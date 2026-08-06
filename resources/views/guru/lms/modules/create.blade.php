@extends('layouts.guru')

@section('title', 'Tambah Modul - ' . $course->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('guru.lms.show', $course->id) }}" class="w-11 h-11 bg-white border-2 border-black rounded-2xl flex items-center justify-center text-black hover:bg-amber-300 transition-all shadow-sm">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h2 class="text-2xl font-black text-black tracking-tight">Tambah Modul Ajar Baru</h2>
            <p class="text-xs text-black font-bold">{{ $course->name }}</p>
        </div>
    </div>

    <!-- Form Card Neo-Brutalism -->
    <div class="bg-white rounded-3xl shadow-xl border-2 border-black overflow-hidden">
        <div class="px-8 py-6 border-b-2 border-black" style="background-color: #090d16 !important; color: #ffffff !important;">
            <h3 class="text-white font-black tracking-wide flex items-center gap-2 text-base uppercase">
                <i class="fas fa-layer-group text-amber-400"></i> Detail Informasi Modul Ajar
            </h3>
            <p class="text-amber-300 text-xs font-bold mt-1">Gunakan modul untuk mengelompokkan materi pembelajaran secara sistematis.</p>
        </div>

        <form action="{{ route('guru.lms.modules.store', $course->id) }}" method="POST" class="p-8 space-y-6">
            @csrf
            
            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Judul Modul <span class="text-rose-600">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: Modul 1 - Pengenalan Dasar" 
                           class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none">
                    @error('title') <p class="text-rose-600 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Deskripsi Modul (Opsional)</label>
                    <textarea name="description" rows="4" placeholder="Jelaskan apa yang akan dipelajari di modul ini..." 
                              class="w-full border-2 border-black rounded-2xl p-4 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none"></textarea>
                    @error('description') <p class="text-rose-600 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Warna Identitas Modul</label>
                    <div class="grid grid-cols-4 sm:grid-cols-8 gap-3 mt-2">
                        @foreach(['indigo', 'emerald', 'rose', 'amber', 'blue', 'purple', 'cyan', 'orange'] as $color)
                        @php
                            $hexColor = match($color) {
                                'indigo' => '#4f46e5',
                                'emerald' => '#059669',
                                'rose' => '#e11d48',
                                'amber' => '#d97706',
                                'blue' => '#2563eb',
                                'purple' => '#9333ea',
                                'cyan' => '#0891b2',
                                'orange' => '#ea580c',
                                default => '#2563eb'
                            };
                        @endphp
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="color" value="{{ $color }}" class="peer sr-only" {{ $loop->first ? 'checked' : '' }}>
                            <div class="w-full aspect-square rounded-2xl border-2 border-black peer-checked:ring-4 peer-checked:ring-black/40 transition-all flex items-center justify-center shadow-xs group-hover:scale-105" style="background-color: {{ $hexColor }} !important;">
                                <i class="fas fa-check text-white font-black opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                            </div>
                            <span class="block text-[10px] text-center mt-1 font-black text-black uppercase tracking-wider">{{ $color }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t-2 border-black flex gap-3">
                <a href="{{ route('guru.lms.show', $course->id) }}" 
                   class="flex-1 px-6 py-3.5 rounded-2xl font-black bg-slate-200 text-black border-2 border-black hover:bg-slate-300 transition-all tracking-wider uppercase text-xs text-center">
                    Batal
                </a>
                <button type="submit" 
                        class="flex-[2] px-6 py-3.5 rounded-2xl font-black bg-emerald-600 text-white hover:bg-emerald-700 transition-all border-2 border-black shadow-md tracking-wider uppercase text-xs">
                    <i class="fas fa-save mr-1 text-amber-400"></i> Simpan Modul Baru
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
