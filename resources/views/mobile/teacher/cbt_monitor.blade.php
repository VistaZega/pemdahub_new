@extends('mobile.layouts.app')

@section('title', 'Live Monitoring Ujian CBT - ' . $exam->exam_title)

@section('content')
<div class="space-y-4">
    <!-- Back Button -->
    <a href="{{ route('mobile.guru.cbt') }}" class="inline-flex items-center gap-1.5 text-xs font-black text-slate-500 hover:text-slate-900 transition">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Manajemen CBT
    </a>

    <!-- Header Card -->
    <div class="clay-card p-5 space-y-3 bg-purple-50 border-2 border-purple-300">
        <div class="flex items-center justify-between">
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase border 
                {{ $exam->status === 'completed' ? 'bg-slate-100 text-slate-700 border-slate-300' : 'bg-emerald-100 text-emerald-800 border-emerald-300' }}">
                {{ $exam->status === 'completed' ? '🏁 Selesai' : '🟢 Ujian Berlangsung / Active' }}
            </span>
            
            <span class="text-xs font-black text-purple-700 bg-white px-3 py-1 rounded-xl border border-purple-200 shadow-xs">
                🔑 Token: <strong>{{ $exam->access_code ?? '-' }}</strong>
            </span>
        </div>

        <div>
            <h2 class="text-base font-black text-slate-900 leading-snug">{{ $exam->exam_title }}</h2>
            <p class="text-xs text-purple-700 font-bold">{{ $exam->subject->name ?? 'Mata Pelajaran' }} &bull; {{ $exam->duration_minutes }} Menit</p>
        </div>

        <div class="pt-2 border-t border-purple-200 flex items-center justify-between text-[10px] font-bold text-slate-500">
            <span>📅 Waktu: {{ $exam->start_time ? $exam->start_time->translatedFormat('d M Y, H:i') : '-' }}</span>
            <span>🎯 Target KKM: <strong>{{ $exam->passing_score ?? 70 }}</strong></span>
        </div>
    </div>

    <!-- Live Statistics (4 Grid Cards) -->
    <div class="grid grid-cols-2 gap-2">
        <div class="clay-card p-4 text-center space-y-1 bg-white border-2 border-slate-200">
            <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider block">Siswa Selesai</span>
            <span class="text-xl font-black text-blue-600 block">{{ count($results) }}</span>
        </div>

        <div class="clay-card p-4 text-center space-y-1 bg-white border-2 border-slate-200">
            <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider block">Lulus KKM</span>
            <span class="text-xl font-black text-emerald-600 block">{{ $passedCount }}</span>
        </div>

        <div class="clay-card p-4 text-center space-y-1 bg-white border-2 border-slate-200">
            <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider block">Rata-Rata Nilai</span>
            <span class="text-xl font-black text-purple-600 block">{{ number_format($avgScore, 1) }}</span>
        </div>

        <div class="clay-card p-4 text-center space-y-1 bg-white border-2 border-slate-200">
            <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider block">Nilai Tertinggi</span>
            <span class="text-xl font-black text-amber-600 block">{{ number_format($maxScore, 1) }}</span>
        </div>
    </div>

    <!-- Candidate Results Roster -->
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider">Hasil & Peserta Live ({{ count($results) }})</h3>
            <button onclick="window.location.reload()" class="text-[10px] font-bold text-purple-600 hover:underline flex items-center gap-1">
                <i class="fa-solid fa-rotate"></i> Refresh Data Live
            </button>
        </div>

        @forelse($results as $res)
            @php
                $std = $res->student;
                $isPassed = ($res->score ?? 0) >= ($exam->passing_score ?? 70);
            @endphp
            <div class="clay-card p-4 flex items-center justify-between bg-white border-2 border-slate-200">
                <div class="flex items-center space-x-3 min-w-0 flex-1">
                    <img src="{{ $std->display_photo ?? 'https://ui-avatars.com/api/?name=' . urlencode($std->full_name ?? 'Siswa') . '&background=7c3aed&color=fff&bold=true' }}" 
                         alt="{{ $std->full_name ?? 'Siswa' }}"
                         onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($std->full_name ?? 'Siswa') }}&background=7c3aed&color=fff&bold=true';"
                         class="w-10 h-10 rounded-2xl object-cover border-2 border-purple-200 shadow-sm shrink-0">
                    
                    <div class="min-w-0 flex-1">
                        <h4 class="text-xs font-black text-slate-900 leading-snug truncate">{{ $std->full_name ?? $std->user->name ?? 'Siswa' }}</h4>
                        <span class="text-[10px] text-slate-400 font-bold block">
                            NIS: {{ $std->nis ?? '-' }} &bull; Selesai: {{ $res->created_at ? $res->created_at->format('H:i') : '-' }}
                        </span>
                    </div>
                </div>

                <div class="text-right shrink-0 pl-2">
                    <span class="text-sm font-black block {{ $isPassed ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ number_format($res->score ?? 0, 1) }}
                    </span>
                    <span class="px-2 py-0.5 rounded-md text-[8px] font-black uppercase border inline-block
                        {{ $isPassed ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-rose-100 text-rose-800 border-rose-200' }}">
                        {{ $isPassed ? 'Lulus' : 'Belum KKM' }}
                    </span>
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-3xl">👥</div>
                <p>Belum ada siswa yang menyelesaikan Ujian CBT ini.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
