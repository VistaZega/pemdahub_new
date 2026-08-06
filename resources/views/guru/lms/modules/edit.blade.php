@extends('layouts.guru')

@section('title', 'Edit Modul - ' . $module->title)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-4">
        <a href="{{ route('guru.lms.show', $course->id) }}" class="w-10 h-10 bg-white shadow-sm border border-gray-100 rounded-xl flex items-center justify-center text-gray-400 hover:text-emerald-600 transition-all hover:shadow-md">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-xl font-bold text-gray-800">Edit Modul</h2>
            <p class="text-xs text-gray-500 mt-0.5">{{ $course->name }}</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-8 py-6 text-white">
            <h3 class="font-bold tracking-wide flex items-center gap-2">
                <i class="fas fa-edit"></i> Edit Detail Modul
            </h3>
            <p class="text-blue-50 text-xs mt-1">Perbarui informasi modul untuk membantu siswa memahami struktur pembelajaran.</p>
        </div>

        <form action="{{ route('guru.lms.modules.update', $module->id) }}" method="POST" class="p-8 space-y-6">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 ml-1">Judul Modul</label>
                    <input type="text" name="title" value="{{ old('title', $module->title) }}" required 
                           placeholder="Contoh: Modul 1 - Pengenalan Dasar" 
                           class="w-full border border-gray-200 rounded-xl px-5 py-3.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all shadow-sm">
                    @error('title') <p class="text-rose-500 text-[10px] mt-1 ml-1 font-bold">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 ml-1">Deskripsi (Opsional)</label>
                    <textarea name="description" rows="4" placeholder="Jelaskan apa yang akan dipelajari di modul ini..." 
                              class="w-full border border-gray-200 rounded-xl px-5 py-3.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all shadow-sm">{{ old('description', $module->description) }}</textarea>
                    @error('description') <p class="text-rose-500 text-[10px] mt-1 ml-1 font-bold">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 ml-1">Warna Tema Modul</label>
                    <div class="grid grid-cols-4 sm:grid-cols-8 gap-3 mt-2">
                        @foreach(['indigo', 'emerald', 'rose', 'amber', 'blue', 'purple', 'cyan', 'orange'] as $color)
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="color" value="{{ $color }}" class="peer sr-only" {{ old('color', $module->color) === $color ? 'checked' : '' }}>
                            <div class="w-full aspect-square rounded-xl border-2 border-transparent peer-checked:border-blue-500 peer-checked:ring-2 peer-checked:ring-blue-100 transition-all flex items-center justify-center bg-{{ $color }}-500 shadow-sm group-hover:scale-105">
                                <i class="fas fa-check text-white opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                            </div>
                            <span class="block text-[8px] text-center mt-1 font-bold text-gray-400 uppercase tracking-tighter">{{ $color }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-50 flex gap-3">
                <a href="{{ route('guru.lms.show', $course->id) }}" 
                   class="flex-1 px-6 py-3.5 rounded-xl font-bold bg-gray-50 text-gray-400 hover:text-gray-600 transition-all uppercase tracking-widest text-xs text-center">
                    Batal
                </a>
                <button type="submit" 
                        class="flex-[2] px-6 py-3.5 rounded-xl font-bold bg-blue-600 text-white hover:bg-blue-700 transition-all shadow-lg hover:shadow-blue-200 uppercase tracking-widest text-xs">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
