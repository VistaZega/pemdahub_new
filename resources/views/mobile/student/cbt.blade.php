@extends('mobile.layouts.app')

@section('title', 'CBT Ujian 3D - Mobile Pro')

@section('content')
<div class="space-y-4">
    <div>
        <h2 class="text-xl font-black text-slate-900">CBT & Ujian Online 💻</h2>
        <p class="text-[11px] text-slate-500 font-bold">Portal Pengerjaan Ujian Komputer & HP</p>
    </div>

    <div class="space-y-3">
        @forelse($exams as $exam)
            <div class="clay-card p-4.5 space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="px-3 py-0.5 rounded-full text-[9px] font-black bg-rose-100 text-rose-800 border border-rose-200">
                            {{ $exam->subject->name ?? 'Mata Pelajaran' }}
                        </span>
                        <h3 class="text-sm font-black text-slate-900 mt-1.5">{{ $exam->title }}</h3>
                        <p class="text-xs text-slate-500 font-bold mt-0.5"><i class="fa-regular fa-clock mr-1 text-rose-600"></i>Durasi: {{ $exam->duration_minutes }} menit</p>
                    </div>
                </div>

                <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[10px] text-emerald-600 font-black"><i class="fa-solid fa-circle text-[8px] mr-1"></i>Ujian Aktif</span>
                    <a href="{{ url('/siswa/cbt/' . $exam->id) }}" 
                       class="clay-btn px-4 py-2 text-white font-black text-xs">
                        Mulai Ujian
                    </a>
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold">
                <i class="fa-solid fa-laptop-code text-4xl mb-2 text-rose-400"></i>
                <p>Tidak ada jadwal ujian CBT aktif saat ini.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
