@php
    $priorityStyles = [
        'Low'      => 'bg-slate-100 text-slate-600 border-slate-200',
        'Medium'   => 'bg-blue-50 text-blue-700 border-blue-200',
        'High'     => 'bg-orange-50 text-orange-700 border-orange-200',
        'Critical' => 'bg-red-50 text-red-700 border-red-200',
    ];
    $style = $priorityStyles[$priority] ?? 'bg-gray-100 text-gray-600 border-gray-200';
@endphp
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $style }}">
    {{ $priority }}
</span>
