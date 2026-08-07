@extends('layouts.guru')

@section('title', 'Edit Topik Diskusi')

@section('content')
<div class="space-y-6">
    {{-- Header Banner --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 rounded-3xl p-8 text-white shadow-xl">
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <a href="{{ route('guru.lms.discussions.show', [$course->id, $discussion->id]) }}" class="inline-flex items-center gap-2 text-white/80 hover:text-white mb-3 text-sm transition">
                    <i class="fas fa-arrow-left"></i> Kembali ke Detail Diskusi
                </a>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Edit Topik Diskusi</h1>
                <p class="text-emerald-100 text-sm mt-1">{{ $course->title }}</p>
            </div>
        </div>
    </div>

    {{-- Form Edit --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
        <form action="{{ route('guru.lms.discussions.update', [$course->id, $discussion->id]) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Judul Topik <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $discussion->title) }}" required
                       class="w-full rounded-xl border-gray-200 focus:border-teal-500 focus:ring-teal-500 text-sm p-3 border">
                @error('title')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tipe Topik <span class="text-red-500">*</span></label>
                    <select name="type" required class="w-full rounded-xl border-gray-200 focus:border-teal-500 focus:ring-teal-500 text-sm p-3 border">
                        <option value="discussion" {{ old('type', $discussion->type) === 'discussion' ? 'selected' : '' }}>💬 Diskusi Umum</option>
                        <option value="question" {{ old('type', $discussion->type) === 'question' ? 'selected' : '' }}>❓ Pertanyaan</option>
                        <option value="announcement" {{ old('type', $discussion->type) === 'announcement' ? 'selected' : '' }}>📢 Pengumuman</option>
                    </select>
                    @error('type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center pt-6">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_pinned" value="1" {{ old('is_pinned', $discussion->is_pinned) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600"></div>
                        <span class="ml-3 text-sm font-medium text-gray-700">Sematkan Topik Ini (Pinned)</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Isi Topik / Deskripsi <span class="text-red-500">*</span></label>
                <textarea name="content" rows="8" required
                          class="w-full rounded-xl border-gray-200 focus:border-teal-500 focus:ring-teal-500 text-sm p-3 border font-sans">{{ old('content', $discussion->content) }}</textarea>
                @error('content')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('guru.lms.discussions.show', [$course->id, $discussion->id]) }}"
                   class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-medium transition">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-teal-600 to-cyan-600 text-white font-medium text-sm hover:from-teal-700 hover:to-cyan-700 transition shadow-md">
                    <i class="fas fa-save mr-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
