@extends('mobile.layouts.app')

@php
    $bimbinganTitle = ($schoolType === 'SMK') ? 'Bimbingan Project Akhir' : 'Bimbingan Penelitian Akhir';
@endphp

@section('title', $bimbinganTitle . ' - PembdaHUB Mobile Pro')

@section('content')
<div class="space-y-4 pt-1">
    <!-- Header Navigation -->
    <div class="flex items-center justify-between px-1">
        <a href="{{ route('mobile.dashboard') }}" class="w-9 h-9 rounded-2xl bg-white border-2 border-slate-200 shadow-sm flex items-center justify-center text-slate-700 hover:bg-slate-50 transition active:scale-95">
            <i class="fa-solid fa-arrow-left text-xs"></i>
        </a>
        <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">{{ $bimbinganTitle }}</h2>
        <div class="w-9"></div>
    </div>

    <!-- Hero Card (Clay Purple) -->
    <div class="clay-purple p-5 space-y-2">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-black uppercase tracking-wider bg-white/30 px-2.5 py-0.5 rounded-full border border-white/40">
                Guru Pembimbing
            </span>
            <span class="text-[10px] font-black bg-white/20 px-2.5 py-0.5 rounded-full">
                {{ $activeYear->name ?? 'Tahun Aktif' }}
            </span>
        </div>
        <h3 class="text-base font-black text-white leading-tight">
            {{ $teacher->full_name }}
        </h3>
        <p class="text-xs text-purple-100 font-bold">
            Membimbing <strong>{{ $projects->count() }}</strong> Kelompok {{ ($schoolType === 'SMK') ? 'Project Akhir SMK' : 'Penelitian Ilmiah SMA' }}
        </p>
    </div>

    <!-- List of Supervised Final Project Groups -->
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider">
                Kelompok Bimbingan ({{ $projects->count() }})
            </h3>
            <span class="text-[10px] font-black text-purple-600">Ketuk untuk review</span>
        </div>

        @forelse($projects as $project)
            @php
                $stages = \App\Models\FinalProject::getStages();
                $currentStageLabel = $stages[$project->current_stage]['label'] ?? ($stages[$project->current_stage]['name'] ?? ucfirst($project->current_stage ?? 'Bab 1'));
            @endphp
            <a href="{{ route('mobile.guru.final-projects.bimbingan.show', $project->id) }}" class="clay-card p-4 block hover:border-purple-300 transition active:scale-98 space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-purple-100 text-purple-800 border border-purple-200">
                        {{ $currentStageLabel }}
                    </span>

                    @if(($project->pending_logs_count ?? 0) > 0)
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-rose-500 text-white animate-pulse">
                            {{ $project->pending_logs_count }} Menunggu ACC
                        </span>
                    @elseif($project->status === 'ready')
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-indigo-100 text-indigo-800 border border-indigo-300">
                            Siap Ujian Sidang
                        </span>
                    @elseif($project->status === 'passed')
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">
                            Lulus ({{ $project->final_score ?? '-' }})
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-slate-100 text-slate-700">
                            {{ $project->total_logs_count ?? 0 }} Jurnal
                        </span>
                    @endif
                </div>

                <div>
                    <h4 class="text-xs font-black text-slate-900 leading-snug line-clamp-2">{{ $project->title }}</h4>
                    <p class="text-[10px] text-slate-500 font-bold mt-1">
                        Ketua: <strong>{{ $project->student->full_name ?? '-' }}</strong> ({{ $project->members->count() }} Anggota)
                    </p>
                </div>
            </a>
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-2xl">🔬</div>
                <p>Belum ada kelompok yang ditugaskan kepada Anda sebagai pembimbing.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
