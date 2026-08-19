@extends('layouts.app')

@section('title', 'Daftar Tiket - Helpdesk IT')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-semibold">Tiket</h1>
        <p class="text-sm text-gray-500 mt-1">Semua tiket layanan IT yang tercatat.</p>
    </div>
    <a href="{{ route('tickets.create') }}"
       class="inline-flex items-center gap-1.5 bg-brand-700 hover:bg-brand-800 text-white text-sm font-medium px-3.5 py-2 rounded-md transition-colors">
        + Tiket Baru
    </a>
</div>

<form method="GET" action="{{ route('tickets.index') }}" class="flex flex-wrap gap-3 mb-5">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari subjek atau ID tiket..."
           class="flex-1 min-w-[200px] rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-700 focus:ring-1 focus:ring-brand-700">

    <select name="status" onchange="this.form.submit()"
            class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-700 focus:ring-1 focus:ring-brand-700">
        <option value="">Semua Status</option>
        @foreach (['Open', 'In Progress', 'Pending', 'Resolved', 'Closed'] as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
        @endforeach
    </select>

    <button type="submit" class="text-sm text-gray-600 border border-gray-300 rounded-md px-3.5 py-2 hover:bg-gray-50">
        Cari
    </button>
</form>

<div class="bg-white border border-[#E2E8F0] rounded-lg overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
            <tr>
                <th class="text-left px-5 py-3 font-medium">Tiket</th>
                <th class="text-left px-5 py-3 font-medium">Kategori</th>
                <th class="text-left px-5 py-3 font-medium">Prioritas</th>
                <th class="text-left px-5 py-3 font-medium">Status</th>
                <th class="text-left px-5 py-3 font-medium">Dibuat</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[#E2E8F0]">
            @forelse ($tickets as $ticket)
                <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('tickets.show', $ticket) }}'">
                    <td class="px-5 py-3.5">
                        <p class="font-medium text-[#1F2933]">#{{ $ticket->ticket_id }} &middot; {{ $ticket->subject }}</p>
                        <p class="text-xs text-gray-400">{{ $ticket->requester->full_name }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-gray-600">{{ $ticket->category->category_name }}</td>
                    <td class="px-5 py-3.5">@include('partials.priority-badge', ['priority' => $ticket->priority->priority_name])</td>
                    <td class="px-5 py-3.5">@include('partials.status-badge', ['status' => $ticket->status])</td>
                    <td class="px-5 py-3.5 text-gray-400 text-xs">{{ $ticket->created_at->format('d M Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-gray-400">
                        Tidak ada tiket yang cocok dengan pencarian.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-5">
    {{ $tickets->links() }}
</div>
@endsection
