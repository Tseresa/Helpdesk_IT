<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Priority;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * FR-06, FR-15 - Daftar tiket, disesuaikan per peran:
     * - End User   : hanya tiket miliknya sendiri.
     * - Teknisi    : default hanya tiket yang ditugaskan ke dirinya
     *                (bisa lihat semua via ?filter=all).
     * - Supervisor, Admin, Manajemen : semua tiket.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Ticket::with(['requester', 'assignee', 'category', 'priority']);

        if ($user->isEndUser()) {
            $query->where('requester_id', $user->user_id);
        } elseif ($user->isTeknisi() && $request->get('filter', 'mine') === 'mine') {
            $query->where('assigned_to', $user->user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('ticket_id', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        $categories = Category::orderBy('category_name')->get();
        $priorities = Priority::orderBy('priority_level')->get();

        return view('tickets.create', compact('categories', 'priorities'));
    }

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

        return redirect()->route('tickets.show', $ticket)
            ->with('status', 'Tiket #' . $ticket->ticket_id . ' berhasil dibuat.');
    }

    public function show(Request $request, Ticket $ticket)
    {
        $this->authorizeView($request, $ticket);

        $ticket->load([
            'requester', 'assignee', 'category', 'priority', 'sla',
            'comments' => function ($q) use ($request) {
                if ($request->user()->isEndUser()) {
                    $q->where('is_internal', false);
                }
                $q->with('user')->orderBy('created_at');
            },
            'attachments.uploader',
            'history.changedBy',
            'feedback',
        ]);

        // Daftar teknisi - dipakai untuk dropdown assign (Supervisor/Admin saja).
        $technicians = $request->user()->canAssignTickets()
            ? User::whereHas('role', fn ($q) => $q->where('role_name', 'Teknisi'))
                ->where('is_active', true)->orderBy('full_name')->get()
            : collect();

        return view('tickets.show', compact('ticket', 'technicians'));
    }

    /**
     * FR-06 - Update status tiket dari halaman detail (dropdown status).
     * Hanya Teknisi/Supervisor/Admin yang boleh (dicek di sini, bukan cuma
     * disembunyikan di Blade, supaya tetap aman kalau ada yang submit manual).
     *
     * Kalau status diubah menjadi "In Progress" dan tiket belum ditugaskan
     * ke siapa pun, otomatis di-assign ke pengguna yang mengubah status ini.
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        if (! $request->user()->canHandleTickets()) {
            abort(403, 'Peran Anda tidak diizinkan mengubah status tiket.');
        }

        $data = $request->validate([
            'status' => ['required', 'in:Open,In Progress,Pending,Resolved,Closed'],
        ]);

        $oldStatus = $ticket->status;
        $wasUnassigned = is_null($ticket->assigned_to);

        $update = ['status' => $data['status']];
        if ($data['status'] === 'Resolved' && ! $ticket->resolved_at) {
            $update['resolved_at'] = now();
        }
        if ($data['status'] === 'Closed' && ! $ticket->closed_at) {
            $update['closed_at'] = now();
        }

        // Auto-assign: hanya terjadi kalau status baru "In Progress" DAN
        // tiket sebelumnya belum ada penanggung jawabnya.
        if ($data['status'] === 'In Progress' && $wasUnassigned) {
            $update['assigned_to'] = $request->user()->user_id;
        }

        $ticket->update($update);

        if ($data['status'] === 'In Progress' && $wasUnassigned) {
            TicketHistory::create([
                'ticket_id'     => $ticket->ticket_id,
                'changed_by'    => $request->user()->user_id,
                'field_changed' => 'assigned_to',
                'old_value'     => null,
                'new_value'     => $request->user()->full_name,
            ]);
        }

        TicketHistory::create([
            'ticket_id'     => $ticket->ticket_id,
            'changed_by'    => $request->user()->user_id,
            'field_changed' => 'status',
            'old_value'     => $oldStatus,
            'new_value'     => $data['status'],
        ]);

        return back()->with('status', 'Status tiket diperbarui menjadi "' . $data['status'] . '".');
    }

    /**
     * FR-05 - Menugaskan/reassign tiket ke teknisi tertentu.
     * Hanya Supervisor/Admin yang boleh (FR-09 eskalasi juga lewat sini -
     * reassign ke teknisi/supervisor lain dianggap bentuk eskalasi manual).
     */
    public function assign(Request $request, Ticket $ticket)
    {
        if (! $request->user()->canAssignTickets()) {
            abort(403, 'Hanya Supervisor/Admin yang dapat menugaskan tiket.');
        }

        $data = $request->validate([
            'assigned_to' => ['required', 'exists:users,user_id'],
        ]);

        $newAssignee = User::findOrFail($data['assigned_to']);
        $oldAssigneeName = $ticket->assignee->full_name ?? null;

        $ticket->update([
            'assigned_to' => $data['assigned_to'],
            'status'      => $ticket->status === 'Open' ? 'In Progress' : $ticket->status,
        ]);

        TicketHistory::create([
            'ticket_id'     => $ticket->ticket_id,
            'changed_by'    => $request->user()->user_id,
            'field_changed' => 'assigned_to',
            'old_value'     => $oldAssigneeName,
            'new_value'     => $newAssignee->full_name,
        ]);

        return back()->with('status', 'Tiket berhasil ditugaskan ke ' . $newAssignee->full_name . '.');
    }

    /**
     * Menambah komentar dari halaman detail tiket.
     * Manajemen & End User tidak bisa membuat catatan internal.
     */
    public function addComment(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'comment_text' => ['required', 'string'],
            'is_internal'  => ['sometimes', 'boolean'],
        ]);

        $isInternal = $request->user()->canHandleTickets()
            ? $request->boolean('is_internal')
            : false;

        $ticket->comments()->create([
            'user_id'      => $request->user()->user_id,
            'comment_text' => $data['comment_text'],
            'is_internal'  => $isInternal,
        ]);

        return back()->with('status', 'Komentar berhasil ditambahkan.');
    }

    /**
     * Memastikan End User hanya bisa melihat tiketnya sendiri.
     * Role lain (Teknisi, Supervisor, Admin, Manajemen) boleh melihat semua tiket.
     */
    private function authorizeView(Request $request, Ticket $ticket): void
    {
        $user = $request->user();

        if ($user->isEndUser() && $ticket->requester_id !== $user->user_id) {
            abort(403, 'Anda tidak memiliki akses ke tiket ini.');
        }
    }
}