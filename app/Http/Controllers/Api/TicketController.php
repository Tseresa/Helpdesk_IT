<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Controller inti manajemen tiket helpdesk.
 * Mencakup: FR-01 (pembuatan tiket), FR-04 (prioritas & SLA),
 * FR-05 (penugasan), FR-06 (pelacakan status), FR-09 (eskalasi),
 * FR-15 (pencarian tiket).
 */
class TicketController extends Controller
{
    /**
     * FR-06, FR-15 - Daftar tiket dengan filter, pencarian, dan pagination.
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['requester', 'assignee', 'category', 'priority']);

        // End-user hanya melihat tiket miliknya sendiri, kecuali admin/teknisi/supervisor
        $user = $request->user();
        if ($user->role->role_name === 'End User') {
            $query->where('requester_id', $user->user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('priority_id')) {
            $query->where('priority_id', $request->priority_id);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('ticket_id', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return response()->json(
            $query->orderByDesc('created_at')->paginate($request->get('per_page', 15))
        );
    }

    /**
     * FR-01 - Pembuatan tiket baru oleh end-user.
     * SLA due_at dihitung otomatis dari kebijakan SLA kategori+prioritas.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,category_id'],
            'priority_id' => ['required', 'exists:priorities,priority_id'],
            'subject'     => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
        ]);

        $sla = SlaPolicy::where('category_id', $data['category_id'])
            ->where('priority_id', $data['priority_id'])
            ->first();

        $ticket = Ticket::create([
            'requester_id' => $request->user()->user_id,
            'category_id'  => $data['category_id'],
            'priority_id'  => $data['priority_id'],
            'sla_id'       => $sla?->sla_id,
            'subject'      => $data['subject'],
            'description'  => $data['description'],
            'status'       => 'Open',
            'due_at'       => $sla ? now()->addMinutes($sla->resolution_minutes) : null,
        ]);

        return response()->json($ticket->load(['requester', 'category', 'priority']), 201);
    }

    public function show(Ticket $ticket)
    {
        return response()->json($ticket->load([
            'requester', 'assignee', 'category', 'priority', 'sla',
            'comments.user', 'attachments', 'history.changedBy', 'assets',
        ]));
    }

    /**
     * FR-06 - Memperbarui data umum tiket (subjek/deskripsi/kategori/prioritas).
     * Perubahan status/penugasan ditangani method khusus agar tercatat rapi di history.
     */
    public function update(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'subject'     => ['sometimes', 'string', 'max:200'],
            'description' => ['sometimes', 'string'],
            'category_id' => ['sometimes', 'exists:categories,category_id'],
            'priority_id' => ['sometimes', 'exists:priorities,priority_id'],
        ]);

        $ticket->update($data);

        return response()->json($ticket->fresh(['category', 'priority']));
    }

    /**
     * FR-05 - Penugasan tiket ke teknisi (manual oleh admin/supervisor).
     */
    public function assign(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'assigned_to' => ['required', 'exists:users,user_id'],
        ]);

        DB::transaction(function () use ($ticket, $data, $request) {
            $this->logChange($ticket, $request->user()->user_id, 'assigned_to', $ticket->assigned_to, $data['assigned_to']);

            $ticket->update([
                'assigned_to' => $data['assigned_to'],
                'status'      => $ticket->status === 'Open' ? 'In Progress' : $ticket->status,
            ]);
        });

        return response()->json($ticket->fresh(['assignee']));
    }

    /**
     * FR-06 - Mengubah status tiket (In Progress, Pending, Resolved, Closed).
     * Mencatat waktu resolved_at/closed_at serta riwayat perubahan.
     */
    public function changeStatus(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'status' => ['required', 'in:Open,In Progress,Pending,Resolved,Closed'],
        ]);

        DB::transaction(function () use ($ticket, $data, $request) {
            $this->logChange($ticket, $request->user()->user_id, 'status', $ticket->status, $data['status']);

            $update = ['status' => $data['status']];

            if ($data['status'] === 'Resolved' && ! $ticket->resolved_at) {
                $update['resolved_at'] = now();
            }

            if ($data['status'] === 'Closed' && ! $ticket->closed_at) {
                $update['closed_at'] = now();
            }

            $ticket->update($update);
        });

        return response()->json($ticket->fresh());
    }

    /**
     * FR-09 - Eskalasi manual tiket ke supervisor (di luar eskalasi otomatis terjadwal).
     */
    public function escalate(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'escalated_to' => ['required', 'exists:users,user_id'],
            'reason'       => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($ticket, $data, $request) {
            $this->logChange($ticket, $request->user()->user_id, 'assigned_to (escalated)', $ticket->assigned_to, $data['escalated_to']);

            $ticket->update(['assigned_to' => $data['escalated_to']]);

            $ticket->history()->create([
                'changed_by'    => $request->user()->user_id,
                'field_changed' => 'escalation_reason',
                'old_value'     => null,
                'new_value'     => $data['reason'] ?? 'Eskalasi manual',
            ]);
        });

        return response()->json(['message' => 'Tiket berhasil dieskalasi', 'ticket' => $ticket->fresh()]);
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();

        return response()->json(['message' => 'Tiket berhasil dihapus']);
    }

    /**
     * Helper internal: mencatat perubahan field ke tabel ticket_history.
     */
    private function logChange(Ticket $ticket, int $changedBy, string $field, $oldValue, $newValue): void
    {
        TicketHistory::create([
            'ticket_id'     => $ticket->ticket_id,
            'changed_by'    => $changedBy,
            'field_changed' => $field,
            'old_value'     => $oldValue,
            'new_value'     => $newValue,
        ]);
    }
}
