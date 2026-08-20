<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isEndUser = $user->role->role_name === 'End User';

        $baseQuery = $isEndUser
            ? Ticket::where('requester_id', $user->user_id)
            : Ticket::query();

        $stats = [
            'total'    => (clone $baseQuery)->count(),
            'open'     => (clone $baseQuery)->whereIn('status', ['Open', 'In Progress', 'Pending'])->count(),
            'resolved' => (clone $baseQuery)->whereIn('status', ['Resolved', 'Closed'])->count(),
        ];

        $recentTickets = (clone $baseQuery)
            ->with(['category', 'priority', 'requester'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'recentTickets', 'isEndUser'));
    }
}
