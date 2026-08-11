<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Http\Request;

/**
 * FR-10 - Komentar & Riwayat Komunikasi Tiket
 */
class TicketCommentController extends Controller
{
    /**
     * Daftar komentar pada sebuah tiket.
     * Catatan internal (is_internal) disembunyikan dari end-user.
     */
    public function index(Request $request, Ticket $ticket)
    {
        $query = $ticket->comments()->with('user')->orderBy('created_at');

        if ($request->user()->role->role_name === 'End User') {
            $query->where('is_internal', false);
        }

        return response()->json($query->get());
    }

    public function store(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'comment_text' => ['required', 'string'],
            'is_internal'  => ['sometimes', 'boolean'],
        ]);

        // End-user tidak boleh membuat catatan internal
        $isInternal = $request->user()->role->role_name === 'End User'
            ? false
            : ($data['is_internal'] ?? false);

        $comment = TicketComment::create([
            'ticket_id'    => $ticket->ticket_id,
            'user_id'      => $request->user()->user_id,
            'comment_text' => $data['comment_text'],
            'is_internal'  => $isInternal,
        ]);

        return response()->json($comment->load('user'), 201);
    }

    public function destroy(TicketComment $comment)
    {
        $comment->delete();

        return response()->json(['message' => 'Komentar berhasil dihapus']);
    }
}
