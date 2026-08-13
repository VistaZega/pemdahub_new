@extends('mobile.layouts.app')

@section('title', 'Raport Digital - Wali Kelas Guru Mobile')

@section('content')
<div class="space-y-4">
    <!-- Header Title -->
    <div>
        <h2 class="text-xl font-black text-slate-900">Raport Digital 📊</h2>
        <p class="text-[11px] text-slate-500 font-bold">Penilaian & Pencetakan Raport Digital Wali Kelas</p>
    </div>

    <!-- Homeroom Classes (3D Clay Cards) -->
    <div class="space-y-3">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-wider px-1">Kelas Bimbingan Wali Kelas</h3>

        @forelse($homeroomClasses as $cls)
            <div class="clay-card p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-2xl clay-green flex items-center justify-center text-white text-xl font-black shadow-md">
                            📊
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 leading-snug">{{ $cls->class_name }}</h3>
                            <span class="text-[10px] font-bold text-emerald-700 block">
                                {{ $cls->students->count() }} Siswa Terdaftar
                            </span>
                        </div>
                    </div>

                    <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 text-[9px] font-black uppercase">
                        Wali Kelas
                    </span>
                </div>

                <!-- Action Button -->
                <div class="pt-2 border-t border-slate-100 flex justify-end">
                    <a href="{{ route('guru.raport.index') }}" 
                       class="py-2.5 px-4 rounded-xl bg-emerald-600 text-white text-[11px] font-black hover:bg-emerald-700 transition flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-file-invoice text-xs"></i> Olah & Cetak Raport Kelas
                    </a>
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-3xl">📊</div>
                <p>Anda tidak terdaftar sebagai Wali Kelas pada semester ini.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
