@extends('mobile.layouts.app')

@section('title', 'Hall of Fame - Papan Peringkat Reputasi Mobile')

@section('content')
<div class="space-y-4">
    <!-- Header Title -->
    <div>
        <h2 class="text-xl font-black text-slate-900">Hall of Fame Pembda 🏆</h2>
        <p class="text-[11px] text-slate-500 font-bold">Papan Peringkat Reputasi & Prestasi Warga Sekolah</p>
    </div>

    <!-- Leaderboard Cards (3D Clay Yellow) -->
    <div class="space-y-3">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-wider px-1">Poin Reputasi Tertinggi</h3>

        @forelse($topStudents as $index => $std)
            <div class="clay-card p-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-2xl flex items-center justify-center font-black text-sm text-white shadow-sm
                        {{ $index == 0 ? 'bg-amber-500' : ($index == 1 ? 'bg-slate-400' : ($index == 2 ? 'bg-amber-700' : 'bg-blue-500')) }}">
                        #{{ $index + 1 }}
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-slate-900 uppercase">{{ $std->full_name }}</h4>
                        <span class="text-[10px] text-slate-500 font-bold">NIS: {{ $std->nis ?? '-' }}</span>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-xs font-black text-amber-600 block">+{{ $std->user->reputation->total_points ?? $std->reputation_points ?? 0 }} Pts</span>
                    <span class="text-[9px] font-bold text-slate-400">Poin Siswa</span>
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-3xl">🏆</div>
                <p>Belum ada data papan peringkat reputasi.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
