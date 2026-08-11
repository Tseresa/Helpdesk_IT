<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * FR-13 - Pelaporan & Dashboard
 * Menyediakan ringkasan kinerja layanan untuk admin/manajemen.
 */
class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $query = Ticket::query();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $totalTickets = (clone $query)->count();
        $openTickets = (clone $query)->whereIn('status', ['Open', 'In Progress', 'Pending'])->count();
        $resolvedTickets = (clone $query)->whereIn('status', ['Resolved', 'Closed'])->count();

        // Rata-rata waktu penyelesaian dalam jam
        $avgResolutionHours = (clone $query)
            ->whereNotNull('resolved_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours'))
            ->value('avg_hours');

        // Kepatuhan SLA: proporsi tiket yang resolved_at <= due_at
        $slaEligible = (clone $query)->whereNotNull('due_at')->whereNotNull('resolved_at')->count();
        $slaMet = (clone $query)
            ->whereNotNull('due_at')->whereNotNull('resolved_at')
            ->whereColumn('resolved_at', '<=', 'due_at')
            ->count();
        $slaComplianceRate = $slaEligible > 0 ? round(($slaMet / $slaEligible) * 100, 2) : null;

        return response()->json([
            'total_tickets'         => $totalTickets,
            'open_tickets'          => $openTickets,
            'resolved_tickets'      => $resolvedTickets,
            'avg_resolution_hours'  => $avgResolutionHours ? round($avgResolutionHours, 2) : null,
            'sla_compliance_rate'   => $slaComplianceRate,
        ]);
    }

    /**
     * Jumlah tiket per kategori - untuk grafik distribusi.
     */
    public function ticketsByCategory(Request $request)
    {
        return response()->json(
            Ticket::join('categories', 'categories.category_id', '=', 'tickets.category_id')
                ->selectRaw('categories.category_name, count(*) as total')
                ->groupBy('categories.category_name')
                ->orderByDesc('total')
                ->get()
        );
    }

    /**
     * Jumlah tiket per teknisi - untuk memantau beban kerja tim.
     */
    public function ticketsByAgent(Request $request)
    {
        return response()->json(
            Ticket::join('users', 'users.user_id', '=', 'tickets.assigned_to')
                ->selectRaw('users.full_name, count(*) as total_assigned, 
                    SUM(CASE WHEN tickets.status IN ("Resolved","Closed") THEN 1 ELSE 0 END) as total_resolved')
                ->groupBy('users.full_name')
                ->orderByDesc('total_assigned')
                ->get()
        );
    }

    /**
     * Tiket yang mendekati/melewati batas SLA - untuk pemantauan eskalasi (FR-09).
     */
    public function slaAtRisk(Request $request)
    {
        return response()->json(
            Ticket::with(['requester', 'assignee', 'priority'])
                ->whereNotNull('due_at')
                ->whereNotIn('status', ['Resolved', 'Closed'])
                ->where('due_at', '<=', now()->addHours(2))
                ->orderBy('due_at')
                ->get()
        );
    }
}
