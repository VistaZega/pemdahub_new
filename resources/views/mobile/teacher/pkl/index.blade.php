@extends('mobile.layouts.app')

@section('title', 'Bimbingan PKL Siswa - PembdaHUB Mobile Pro')

@section('content')
<div class="space-y-4 pt-1">
    <!-- Header Navigation -->
    <div class="flex items-center justify-between px-1">
        <a href="{{ route('mobile.dashboard') }}" class="w-9 h-9 rounded-2xl bg-white border-2 border-slate-200 shadow-sm flex items-center justify-center text-slate-700 hover:bg-slate-50 transition active:scale-95">
            <i class="fa-solid fa-arrow-left text-xs"></i>
        </a>
        <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">Bimbingan PKL Siswa</h2>
        <div class="w-9"></div>
    </div>

    <!-- Hero Card (Clay Orange) -->
    <div class="clay-orange p-5 space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-black uppercase tracking-wider bg-white/30 px-2.5 py-0.5 rounded-full border border-white/40">
                Guru Pembimbing PKL
            </span>
            <div class="flex items-center gap-1.5">
                <span class="text-[10px] font-black bg-amber-400 text-black px-2.5 py-0.5 rounded-full border border-black shadow-xs">
                    ⚡ {{ $pklHours ?? 0 }} JP
                </span>
                <span class="text-[10px] font-black bg-white/20 px-2.5 py-0.5 rounded-full">
                    {{ $activeYear->name ?? ($activeYear->year ?? 'Tahun Aktif') }}
                </span>
            </div>
        </div>
        <div>
            <h3 class="text-base font-black text-white leading-tight">
                {{ $teacher->full_name }}
            </h3>
            <p class="text-xs text-orange-100 font-bold mt-0.5">
                Membimbing <strong>{{ $placements->count() }}</strong> Siswa • Beban Penugasan: <strong>{{ $pklHours ?? 0 }} JP</strong>
            </p>
        </div>

        <!-- Mini Stats Grid -->
        <div class="grid grid-cols-3 gap-2 pt-1">
            <div class="bg-black/20 backdrop-blur-xs rounded-xl p-2 text-center border border-white/15">
                <span class="block text-sm font-black text-white leading-none">{{ $placements->count() }}</span>
                <span class="text-[9px] font-bold text-orange-200 uppercase tracking-wider">Siswa</span>
            </div>
            <div class="bg-black/20 backdrop-blur-xs rounded-xl p-2 text-center border border-white/15">
                <span class="block text-sm font-black text-amber-300 leading-none">{{ $pklHours ?? 0 }} JP</span>
                <span class="text-[9px] font-bold text-orange-200 uppercase tracking-wider">Beban PKL</span>
            </div>
            <div class="bg-black/20 backdrop-blur-xs rounded-xl p-2 text-center border border-white/15">
                <span class="block text-sm font-black text-white leading-none">{{ $placements->pluck('company_name')->unique()->filter()->count() }}</span>
                <span class="text-[9px] font-bold text-orange-200 uppercase tracking-wider">DUDI</span>
            </div>
        </div>
    </div>

    <!-- List of Supervised Students -->
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider">
                Daftar Siswa Bimbingan ({{ $placements->count() }})
            </h3>
            <span class="text-[10px] font-black text-orange-600">Ketuk untuk review log</span>
        </div>

        @forelse($placements as $placement)
            @php
                $studentUser = $placement->student?->user;
                $studentPhoto = $placement->student?->photo_url ?? ($studentUser?->avatar_url ?? null);
                if (!$studentPhoto || str_contains($studentPhoto, 'default-avatar')) {
                    $studentPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($placement->student->full_name ?? 'Siswa') . '&background=f97316&color=fff&bold=true';
                }
            @endphp
            <a href="{{ route('mobile.guru.pkl.show', $placement->id) }}" class="clay-card p-4 block hover:border-orange-300 transition active:scale-98 space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <img src="{{ $studentPhoto }}" alt="{{ $placement->student->full_name ?? 'Siswa' }}"
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($placement->student->full_name ?? 'Siswa') }}&background=f97316&color=fff&bold=true';"
                             class="w-11 h-11 rounded-2xl object-cover border-2 border-orange-200 shrink-0 bg-white">
                        <div class="min-w-0">
                            <h4 class="text-xs font-black text-slate-900 truncate leading-snug">{{ $placement->student->full_name ?? '-' }}</h4>
                            <p class="text-[10px] text-slate-500 font-bold truncate">NISN: {{ $placement->student->nisn ?? '-' }}</p>
                        </div>
                    </div>

                    @if(($placement->pending_logs_count ?? 0) > 0)
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-rose-500 text-white shadow-xs shrink-0 animate-pulse">
                            {{ $placement->pending_logs_count }} Menunggu ACC
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-300 shrink-0">
                            {{ $placement->total_logs_count ?? 0 }} Logbook
                        </span>
                    @endif
                </div>

                <!-- Placement DUDI Detail -->
                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 text-[11px] font-bold text-slate-700 flex items-center justify-between">
                    <div class="flex items-center gap-1.5 min-w-0">
                        <i class="fa-solid fa-building text-orange-500 text-xs shrink-0"></i>
                        <span class="truncate">{{ $placement->dudi->name ?? ($placement->company_name ?? 'DUDI') }}</span>
                    </div>
                    <span class="text-[10px] font-black text-slate-500 shrink-0">
                        {{ $placement->shift ? 'Shift ' . ucfirst($placement->shift) : 'Reguler' }}
                    </span>
                </div>
            </a>
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-2xl">💼</div>
                <p>Belum ada siswa yang ditugaskan kepada Anda sebagai pembimbing PKL.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
