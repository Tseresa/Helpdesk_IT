@extends('layouts.app')

@section('title', 'Tiket #' . $ticket->ticket_id . ' - Helpdesk IT')

@section('content')
@php
    $user = auth()->user();
    $canHandle = $user->canHandleTickets();     // Teknisi, Supervisor, Admin
    $canAssign = $user->canAssignTickets();     // Supervisor, Admin
@endphp

<a href="{{ route('tickets.index') }}" class="text-sm text-gray-500 hover:text-brand-700 mb-4 inline-block">&larr; Kembali ke daftar tiket</a>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Kolom utama --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white border border-[#E2E8F0] rounded-lg p-6">
            <div class="flex items-start justify-between gap-4 mb-3">
                <div>
                    <p class="text-xs text-gray-400 font-mono mb-1">#{{ $ticket->ticket_id }}</p>
                    <h1 class="text-lg font-semibold text-[#1F2933]">{{ $ticket->subject }}</h1>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    @include('partials.priority-badge', ['priority' => $ticket->priority->priority_name])
                    @include('partials.status-badge', ['status' => $ticket->status])
                </div>
            </div>
            <p class="text-sm text-gray-600 whitespace-pre-line leading-relaxed">{{ $ticket->description }}</p>

            <div class="flex flex-wrap gap-x-6 gap-y-1 text-xs text-gray-400 mt-4 pt-4 border-t border-[#E2E8F0]">
                <span>Dibuat oleh <strong class="text-gray-600">{{ $ticket->requester->full_name }}</strong></span>
                <span>Kategori <strong class="text-gray-600">{{ $ticket->category->category_name }}</strong></span>
                <span>{{ $ticket->created_at->format('d M Y, H:i') }}</span>
                @if ($ticket->due_at)
                    <span>Batas SLA <strong class="text-gray-600">{{ $ticket->due_at->format('d M Y, H:i') }}</strong></span>
                @endif
            </div>
        </div>

        {{-- Komentar --}}
        <div class="bg-white border border-[#E2E8F0] rounded-lg p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Komentar & Komunikasi</h2>

            <div class="space-y-4 mb-5">
                @forelse ($ticket->comments as $comment)
                    <div class="flex gap-3 {{ $comment->is_internal ? 'bg-amber-50 border border-amber-100 rounded-md p-3' : '' }}">
                        <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-semibold shrink-0">
                            {{ strtoupper(substr($comment->user->full_name, 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium text-[#1F2933]">{{ $comment->user->full_name }}</p>
                                @if ($comment->is_internal)
                                    <span class="text-[10px] uppercase tracking-wide bg-amber-200 text-amber-800 px-1.5 py-0.5 rounded">Internal</span>
                                @endif
                                <p class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</p>
                            </div>
                            <p class="text-sm text-gray-600 mt-0.5">{{ $comment->comment_text }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Belum ada komentar.</p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('tickets.addComment', $ticket) }}" class="border-t border-[#E2E8F0] pt-4">
                @csrf
                <textarea name="comment_text" rows="3" required placeholder="Tulis komentar..."
                          class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-700 focus:ring-1 focus:ring-brand-700"></textarea>
                <div class="flex items-center justify-between mt-3">
                    @if ($canHandle)
                        <label class="flex items-center gap-2 text-xs text-gray-500">
                            <input type="checkbox" name="is_internal" value="1" class="rounded border-gray-300 text-brand-700 focus:ring-brand-700">
                            Catatan internal (tidak terlihat end-user)
                        </label>
                    @else
                        <span></span>
                    @endif
                    <button type="submit" class="bg-brand-700 hover:bg-brand-800 text-white text-sm font-medium px-4 py-2 rounded-md transition-colors">
                        Kirim
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        @if ($canHandle)
            <div class="bg-white border border-[#E2E8F0] rounded-lg p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Ubah Status</h2>
                <form method="POST" action="{{ route('tickets.updateStatus', $ticket) }}" class="flex gap-2">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-700 focus:ring-1 focus:ring-brand-700">
                        @foreach (['Open', 'In Progress', 'Pending', 'Resolved', 'Closed'] as $status)
                            <option value="{{ $status }}" @selected($ticket->status === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-brand-700 hover:bg-brand-800 text-white text-sm font-medium px-3.5 py-2 rounded-md transition-colors">
                        Simpan
                    </button>
                </form>
            </div>
        @endif

        @if ($canAssign)
            <div class="bg-white border border-[#E2E8F0] rounded-lg p-5">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Tugaskan ke Teknisi</h2>
                <form method="POST" action="{{ route('tickets.assign', $ticket) }}" class="flex gap-2">
                    @csrf
                    @method('PATCH')
                    <select name="assigned_to" required class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-700 focus:ring-1 focus:ring-brand-700">
                        <option value="">Pilih teknisi</option>
                        @foreach ($technicians as $tech)
                            <option value="{{ $tech->user_id }}" @selected($ticket->assigned_to === $tech->user_id)>
                                {{ $tech->full_name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-brand-700 hover:bg-brand-800 text-white text-sm font-medium px-3.5 py-2 rounded-md transition-colors">
                        Tugaskan
                    </button>
                </form>
                @if ($technicians->isEmpty())
                    <p class="text-xs text-amber-600 mt-2">Belum ada akun Teknisi. Tambahkan lewat menu Kelola Pengguna.</p>
                @endif
            </div>
        @endif

        <div class="bg-white border border-[#E2E8F0] rounded-lg p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">Detail</h2>
            <dl class="space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-400">Ditugaskan ke</dt>
                    <dd class="text-gray-700 font-medium">{{ $ticket->assignee->full_name ?? 'Belum ditugaskan' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-400">Diselesaikan</dt>
                    <dd class="text-gray-700">{{ $ticket->resolved_at?->format('d M Y, H:i') ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-400">Ditutup</dt>
                    <dd class="text-gray-700">{{ $ticket->closed_at?->format('d M Y, H:i') ?? '-' }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white border border-[#E2E8F0] rounded-lg p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">Riwayat</h2>
            <ol class="space-y-3 text-xs">
                @forelse ($ticket->history as $entry)
                    <li class="pl-3 border-l-2 border-brand-100">
                        <p class="text-gray-600">
                            <strong>{{ $entry->changedBy->full_name }}</strong>
                            mengubah <span class="font-mono">{{ $entry->field_changed }}</span>
                        </p>
                        <p class="text-gray-400">{{ $entry->old_value ?? '—' }} &rarr; {{ $entry->new_value ?? '—' }}</p>
                        <p class="text-gray-300">{{ $entry->changed_at->diffForHumans() }}</p>
                    </li>
                @empty
                    <li class="text-gray-400">Belum ada riwayat perubahan.</li>
                @endforelse
            </ol>
        </div>
    </div>
</div>
@endsection
