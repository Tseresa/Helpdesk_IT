@extends('layouts.app')

@section('title', 'Kelola Pengguna - Helpdesk IT')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-semibold">Kelola Pengguna</h1>
        <p class="text-sm text-gray-500 mt-1">Tambah akun untuk Teknisi, Supervisor, Manajemen, atau Admin lain.</p>
    </div>
    <a href="{{ route('users.create') }}"
       class="inline-flex items-center gap-1.5 bg-brand-700 hover:bg-brand-800 text-white text-sm font-medium px-3.5 py-2 rounded-md transition-colors">
        + Pengguna Baru
    </a>
</div>

<form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap gap-3 mb-5">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..."
           class="flex-1 min-w-[200px] rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-700 focus:ring-1 focus:ring-brand-700">

    <select name="role_id" onchange="this.form.submit()"
            class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-700 focus:ring-1 focus:ring-brand-700">
        <option value="">Semua Peran</option>
        @foreach ($roles as $role)
            <option value="{{ $role->role_id }}" @selected(request('role_id') == $role->role_id)>{{ $role->role_name }}</option>
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
                <th class="text-left px-5 py-3 font-medium">Nama</th>
                <th class="text-left px-5 py-3 font-medium">Peran</th>
                <th class="text-left px-5 py-3 font-medium">Departemen</th>
                <th class="text-left px-5 py-3 font-medium">Status</th>
                <th class="text-right px-5 py-3 font-medium">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-[#E2E8F0]">
            @forelse ($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <p class="font-medium text-[#1F2933]">{{ $user->full_name }}</p>
                        <p class="text-xs text-gray-400">{{ $user->email }}</p>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border bg-brand-50 text-brand-700 border-brand-100">
                            {{ $user->role->role_name }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-gray-600">{{ $user->department->department_name ?? '-' }}</td>
                    <td class="px-5 py-3.5">
                        @if ($user->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border bg-emerald-50 text-emerald-700 border-emerald-200">Aktif</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border bg-gray-100 text-gray-500 border-gray-200">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('users.edit', $user) }}" class="text-brand-700 hover:underline text-xs font-medium">Edit</a>
                            <form method="POST" action="{{ route('users.toggleActive', $user) }}" onsubmit="return confirm('Yakin ingin mengubah status akun ini?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs font-medium {{ $user->is_active ? 'text-red-600 hover:underline' : 'text-emerald-600 hover:underline' }}">
                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-gray-400">Tidak ada pengguna yang cocok.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-5">
    {{ $users->links() }}
</div>
@endsection
