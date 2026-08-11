<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Ticket;
use Illuminate\Http\Request;

/**
 * FR-12 - Survei Kepuasan Pengguna
 * Hanya dapat diisi setelah tiket berstatus Resolved/Closed.
 */
class FeedbackController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        if (! in_array($ticket->status, ['Resolved', 'Closed'])) {
            return response()->json([
                'message' => 'Umpan balik hanya dapat diberikan setelah tiket diselesaikan.',
            ], 422);
        }

        if ($ticket->feedback()->exists()) {
            return response()->json([
                'message' => 'Umpan balik untuk tiket ini sudah pernah diberikan.',
            ], 409);
        }

        $data = $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $feedback = Feedback::create([
            'ticket_id' => $ticket->ticket_id,
            ...$data,
        ]);

        return response()->json($feedback, 201);
    }

    public function show(Ticket $ticket)
    {
        return response()->json($ticket->feedback);
    }

    /**
     * Ringkasan rata-rata rating, dapat difilter per rentang tanggal - mendukung FR-13.
     */
    public function summary(Request $request)
    {
        $query = Feedback::query();

        if ($request->filled('date_from')) {
            $query->whereDate('submitted_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('submitted_at', '<=', $request->date_to);
        }

        return response()->json([
            'average_rating'    => round($query->avg('rating'), 2),
            'total_feedback'    => $query->count(),
            'rating_breakdown'  => $query->selectRaw('rating, count(*) as total')
                ->groupBy('rating')->orderBy('rating')->get(),
        ]);
    }
}
