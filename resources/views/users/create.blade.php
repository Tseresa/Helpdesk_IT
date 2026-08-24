@extends('layouts.app')

@section('title', 'Tambah Pengguna - Helpdesk IT')

@section('content')
<div class="max-w-lg">
    <h1 class="text-xl font-semibold mb-1">Tambah Pengguna Baru</h1>
    <p class="text-sm text-gray-500 mb-6">Buat akun untuk anggota tim IT (Teknisi, Supervisor, Manajemen, atau Admin).</p>

    <div class="bg-white border border-[#E2E8F0] rounded-lg p-6">
        <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" required
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-700 focus:ring-1 focus:ring-brand-700">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-700 focus:ring-1 focus:ring-brand-700">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="role_id" class="block text-sm font-medium text-gray-700 mb-1">Peran</label>
                    <select id="role_id" name="role_id" required
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-700 focus:ring-1 focus:ring-brand-700">
                        <option value="">Pilih peran</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->role_id }}" @selected(old('role_id') == $role->role_id)>{{ $role->role_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">Departemen</label>
                    <select id="department_id" name="department_id"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-700 focus:ring-1 focus:ring-brand-700">
                        <option value="">Tidak ada</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->department_id }}" @selected(old('department_id') == $department->department_id)>
                                {{ $department->department_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input id="password" type="password" name="password" required
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-700 focus:ring-1 focus:ring-brand-700">
                <p class="text-xs text-gray-400 mt-1">Minimal 8 karakter. Sampaikan ke pengguna lewat jalur aman.</p>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="bg-brand-700 hover:bg-brand-800 text-white text-sm font-medium px-4 py-2.5 rounded-md transition-colors">
                    Simpan
                </button>
                <a href="{{ route('users.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
