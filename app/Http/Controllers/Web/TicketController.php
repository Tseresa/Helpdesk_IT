<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Priority;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketHistory;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Ticket::with(['requester', 'assignee', 'category', 'priority']);

        if ($user->role->role_name === 'End User') {
            $query->where('requester_id', $user->user_id);
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
                if ($request->user()->role->role_name === 'End User') {
                    $q->where('is_internal', false);
                }
                $q->with('user')->orderBy('created_at');
            },
            'attachments.uploader',
            'history.changedBy',
            'feedback',
        ]);

        return view('tickets.show', compact('ticket'));
    }

    /**
     * FR-06 - Update status tiket dari halaman detail (dropdown status).
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'status' => ['required', 'in:Open,In Progress,Pending,Resolved,Closed'],
        ]);

        $oldStatus = $ticket->status;

        $update = ['status' => $data['status']];
        if ($data['status'] === 'Resolved' && ! $ticket->resolved_at) {
            $update['resolved_at'] = now();
        }
        if ($data['status'] === 'Closed' && ! $ticket->closed_at) {
            $update['closed_at'] = now();
        }

        $ticket->update($update);

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
     * Menambah komentar dari halaman detail tiket.
     */
    public function addComment(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'comment_text' => ['required', 'string'],
            'is_internal'  => ['sometimes', 'boolean'],
        ]);

        $isInternal = $request->user()->role->role_name === 'End User'
            ? false
            : $request->boolean('is_internal');

        $ticket->comments()->create([
            'user_id'      => $request->user()->user_id,
            'comment_text' => $data['comment_text'],
            'is_internal'  => $isInternal,
        ]);

        return back()->with('status', 'Komentar berhasil ditambahkan.');
    }

    /**
     * Memastikan End User hanya bisa melihat tiketnya sendiri.
     */
    private function authorizeView(Request $request, Ticket $ticket): void
    {
        $user = $request->user();

        if ($user->role->role_name === 'End User' && $ticket->requester_id !== $user->user_id) {
            abort(403, 'Anda tidak memiliki akses ke tiket ini.');
        }
    }
}
