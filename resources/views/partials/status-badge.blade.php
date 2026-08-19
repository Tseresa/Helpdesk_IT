@php
    $statusStyles = [
        'Open'        => 'bg-slate-100 text-slate-700 border-slate-200',
        'In Progress' => 'bg-blue-50 text-blue-700 border-blue-200',
        'Pending'     => 'bg-amber-50 text-amber-700 border-amber-200',
        'Resolved'    => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'Closed'      => 'bg-gray-100 text-gray-500 border-gray-200',
    ];
    $style = $statusStyles[$status] ?? 'bg-gray-100 text-gray-600 border-gray-200';
@endphp
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $style }}">
    {{ $status }}
</span>
