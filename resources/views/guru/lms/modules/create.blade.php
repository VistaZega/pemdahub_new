@extends('layouts.guru')

@section('title', 'Tambah Modul - ' . $course->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-4">
        <a href="{{ route('guru.lms.show', $course->id) }}" class="w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-800 hover:bg-amber-100 text-amber-800 transition-all shadow-sm">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-2xl font-semibold text-gray-800">Tambah Modul Ajar Baru</h2>
            <p class="text-xs text-gray-800 font-bold">{{ $course->name }}</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        <div class="px-8 py-6 border-b-2 border-gray-200" >
            <h3 class="text-white font-semibold tracking-wide flex items-center gap-2 text-base">
                <i class="fas fa-layer-group text-amber-500"></i> Detail Informasi Modul Ajar
            </h3>
            <p class="text-amber-300 text-xs font-bold mt-1">Gunakan modul untuk mengelompokkan materi pembelajaran secara sistematis.</p>
        </div>

        <form action="{{ route('guru.lms.modules.store', $course->id) }}" method="POST" class="p-8 space-y-6">
            @csrf
            
            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-800 tracking-wide mb-2">Judul Modul <span class="text-red-600">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: Modul 1 - Pengenalan Dasar" 
                           class="w-full border border-gray-200 rounded-xl px-5 py-3.5 text-sm text-gray-800 font-semibold focus:ring-4 focus:ring-black/20 outline-none">
                    @error('title') <p class="text-red-600 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-800 tracking-wide mb-2">Deskripsi Modul (Opsional)</label>
                    <textarea name="description" rows="4" placeholder="Jelaskan apa yang akan dipelajari di modul ini..." 
                              class="w-full border border-gray-200 rounded-xl p-4 text-sm text-gray-800 font-semibold focus:ring-4 focus:ring-black/20 outline-none"></textarea>
                    @error('description') <p class="text-red-600 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-800 tracking-wide mb-2">Warna Identitas Modul</label>
                    <div class="grid grid-cols-4 sm:grid-cols-8 gap-3 mt-2">
                        @foreach(['indigo', 'emerald', 'rose', 'amber', 'blue', 'purple', 'cyan', 'orange'] as $color)
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="color" value="{{ $color }}" class="peer sr-only" {{ $loop->first ? 'checked' : '' }}>
                            <div class="w-full aspect-square rounded-xl border border-gray-200 peer-checked:ring-4 peer-checked:ring-black/30 transition-all flex items-center justify-center shadow-sm group-hover:scale-105" >
                                <i class="fas fa-check text-white font-semibold opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                            </div>
                            <span class="block text-[10px] text-center mt-1 font-semibold text-gray-800 tracking-wide">{{ $color }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t-2 border-gray-200 flex gap-3">
                <a href="{{ route('guru.lms.show', $course->id) }}" 
                   class="flex-1 px-6 py-3.5 rounded-xl font-semibold bg-slate-200 text-gray-800 border border-gray-200 hover:bg-slate-300 transition-all tracking-wide text-xs text-center">
                    Batal
                </a>
                <button type="submit" 
                        class="flex-[2] px-6 py-3.5 rounded-xl font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition-all border border-gray-200 shadow-md tracking-wide text-xs">
                    <i class="fas fa-save mr-1 text-amber-500"></i> Simpan Modul Baru
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
