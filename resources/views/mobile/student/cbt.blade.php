@extends('mobile.layouts.app')

@section('title', 'CBT Ujian Online - Mobile')

@section('content')
<div class="space-y-4">
    <div>
        <h2 class="text-lg font-extrabold text-white">CBT & Ujian Online</h2>
        <p class="text-[11px] text-slate-400">Portal Pengerjaan Ujian Komputer & HP</p>
    </div>

    <div class="space-y-3">
        @forelse($exams as $exam)
            <div class="glass-card rounded-2xl p-4 space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                            {{ $exam->subject->name ?? 'Mata Pelajaran' }}
                        </span>
                        <h3 class="text-sm font-extrabold text-white mt-1">{{ $exam->title }}</h3>
                        <p class="text-xs text-slate-400 mt-0.5"><i class="fa-regular fa-clock mr-1"></i>Durasi: {{ $exam->duration_minutes }} menit</p>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-800 flex items-center justify-between">
                    <span class="text-[10px] text-emerald-400 font-semibold"><i class="fa-solid fa-circle text-[8px] mr-1"></i>Ujian Aktif</span>
                    <a href="{{ url('/siswa/cbt/' . $exam->id) }}" 
                       class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-indigo-500 text-white font-bold text-xs rounded-xl shadow-md">
                        Mulai Ujian
                    </a>
                </div>
            </div>
        @empty
            <div class="glass-card rounded-2xl p-8 text-center text-slate-500 text-xs">
                <i class="fa-solid fa-laptop-code text-3xl mb-2 text-indigo-400/40"></i>
                <p class="font-semibold">Tidak ada jadwal ujian CBT aktif saat ini.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
