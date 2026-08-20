@extends('layouts.app')

@section('title', 'Dashboard - Helpdesk IT')

@section('content')
<div class="mb-8">
    <h1 class="text-xl font-semibold">Halo, {{ auth()->user()->full_name }} 👋</h1>
    <p class="text-sm text-gray-500 mt-1">Berikut ringkasan tiket {{ $isEndUser ? 'Anda' : 'yang tercatat di sistem' }}.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">
    <div class="bg-white border border-[#E2E8F0] rounded-lg p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wide">Total Tiket</p>
        <p class="text-3xl font-semibold mt-2 font-mono">{{ $stats['total'] }}</p>
    </div>
    <div class="bg-white border border-[#E2E8F0] rounded-lg p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wide">Sedang Berjalan</p>
        <p class="text-3xl font-semibold mt-2 font-mono text-blue-700">{{ $stats['open'] }}</p>
    </div>
    <div class="bg-white border border-[#E2E8F0] rounded-lg p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wide">Selesai</p>
        <p class="text-3xl font-semibold mt-2 font-mono text-emerald-700">{{ $stats['resolved'] }}</p>
    </div>
</div>

<div class="flex items-center justify-between mb-4">
    <h2 class="font-semibold text-sm text-gray-700">Tiket Terbaru</h2>
    <a href="{{ route('tickets.index') }}" class="text-sm text-brand-700 hover:underline">Lihat semua &rarr;</a>
</div>

<div class="bg-white border border-[#E2E8F0] rounded-lg divide-y divide-[#E2E8F0]">
    @forelse ($recentTickets as $ticket)
        <a href="{{ route('tickets.show', $ticket) }}" class="flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition-colors">
            <div>
                <p class="text-sm font-medium text-[#1F2933]">#{{ $ticket->ticket_id }} &middot; {{ $ticket->subject }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $ticket->category->category_name }} &middot; {{ $ticket->created_at->diffForHumans() }}</p>
            </div>
            <div class="flex items-center gap-2">
                @include('partials.priority-badge', ['priority' => $ticket->priority->priority_name])
                @include('partials.status-badge', ['status' => $ticket->status])
            </div>
        </a>
    @empty
        <div class="px-5 py-10 text-center text-sm text-gray-400">
            Belum ada tiket. <a href="{{ route('tickets.create') }}" class="text-brand-700 hover:underline">Buat tiket pertama Anda</a>.
        </div>
    @endforelse
</div>
@endsection
