@extends('layouts.app')

@section('title', 'Daftar - Helpdesk IT')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center -mt-8">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <span class="inline-flex w-11 h-11 rounded-lg bg-brand-700 text-white items-center justify-center font-mono text-sm mb-4">HD</span>
            <h1 class="text-xl font-semibold text-[#1F2933]">Buat Akun Baru</h1>
            <p class="text-sm text-gray-500 mt-1">Untuk karyawan yang ingin mengajukan tiket layanan IT</p>
        </div>

        <div class="bg-white border border-[#E2E8F0] rounded-lg p-6 shadow-sm">
            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" required autofocus
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-700 focus:ring-1 focus:ring-brand-700">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-700 focus:ring-1 focus:ring-brand-700">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input id="password" type="password" name="password" required
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-700 focus:ring-1 focus:ring-brand-700">
                    <p class="text-xs text-gray-400 mt-1">Minimal 8 karakter</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-700 focus:ring-1 focus:ring-brand-700">
                </div>

                <button type="submit"
                        class="w-full bg-brand-700 hover:bg-brand-800 text-white text-sm font-medium py-2.5 rounded-md transition-colors">
                    Daftar
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-gray-500 mt-6">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-brand-700 font-medium hover:underline">Masuk di sini</a>
        </p>
    </div>
</div>
@endsection
