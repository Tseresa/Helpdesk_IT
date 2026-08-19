@extends('layouts.app')

@section('title', 'Buat Tiket - Helpdesk IT')

@section('content')
<div class="max-w-xl">
    <h1 class="text-xl font-semibold mb-1">Buat Tiket Baru</h1>
    <p class="text-sm text-gray-500 mb-6">Jelaskan kendala atau permintaan Anda, tim IT akan segera menindaklanjuti.</p>

    <div class="bg-white border border-[#E2E8F0] rounded-lg p-6">
        <form method="POST" action="{{ route('tickets.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <select id="category_id" name="category_id" required
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-700 focus:ring-1 focus:ring-brand-700">
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->category_id }}" @selected(old('category_id') == $category->category_id)>
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="priority_id" class="block text-sm font-medium text-gray-700 mb-1">Prioritas</label>
                    <select id="priority_id" name="priority_id" required
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-700 focus:ring-1 focus:ring-brand-700">
                        <option value="">Pilih prioritas</option>
                        @foreach ($priorities as $priority)
                            <option value="{{ $priority->priority_id }}" @selected(old('priority_id') == $priority->priority_id)>
                                {{ $priority->priority_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subjek</label>
                <input id="subject" type="text" name="subject" value="{{ old('subject') }}" required
                       placeholder="Ringkasan singkat masalah Anda"
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-700 focus:ring-1 focus:ring-brand-700">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea id="description" name="description" rows="5" required
                          placeholder="Jelaskan detail masalah: kapan terjadi, pesan error yang muncul, dan langkah yang sudah dicoba."
                          class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-700 focus:ring-1 focus:ring-brand-700">{{ old('description') }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="bg-brand-700 hover:bg-brand-800 text-white text-sm font-medium px-4 py-2.5 rounded-md transition-colors">
                    Kirim Tiket
                </button>
                <a href="{{ route('tickets.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
