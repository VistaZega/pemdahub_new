@extends('mobile.layouts.app')

@section('title', 'CBT Ujian - Computer Based Test Guru Mobile')

@section('content')
<div class="space-y-4">
    <!-- Header Title -->
    <div>
        <h2 class="text-xl font-black text-slate-900">CBT & Bank Soal 💻</h2>
        <p class="text-[11px] text-slate-500 font-bold">Manajemen Ujian Berbasis Komputer & Bank Soal</p>
    </div>

    <!-- Question Banks (3D Clay Cards) -->
    <div class="space-y-3">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-wider px-1">Bank Soal Saya</h3>

        @forelse($banks as $bank)
            <div class="clay-card p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-11 h-11 rounded-2xl clay-pink flex items-center justify-center text-white text-xl font-black shadow-md">
                            💻
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 leading-snug">{{ $bank->title ?? $bank->name ?? 'Bank Soal' }}</h3>
                            <span class="text-[10px] font-bold text-purple-600 block">
                                {{ $bank->questions_count ?? 0 }} Butir Soal Terdaftar
                            </span>
                        </div>
                    </div>

                    <span class="px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800 text-[9px] font-black uppercase border border-purple-200">
                        {{ $bank->subject->name ?? $bank->code ?? 'CBT' }}
                    </span>
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] font-bold text-slate-500">
                    <span>Dibuat: {{ \Carbon\Carbon::parse($bank->created_at)->translatedFormat('d M Y') }}</span>
                    @if(Route::has('guru.cbt.banks.show'))
                        <a href="{{ route('guru.cbt.banks.show', $bank->id) }}" class="px-3 py-1 bg-amber-400 text-slate-900 font-black rounded-xl border border-black hover:bg-amber-300 transition flex items-center gap-1 shadow-xs">
                            <i class="fa-solid fa-eye"></i> Lihat & Tautkan ke LMS
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-3xl">💻</div>
                <p>Belum ada Bank Soal CBT yang dibuat.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
