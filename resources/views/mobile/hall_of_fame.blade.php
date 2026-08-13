@extends('mobile.layouts.app')

@section('title', 'Hall of Fame - Papan Peringkat Reputasi Mobile')

@section('content')
<div class="space-y-4" x-data="{ activeTab: 'siswa' }">
    <!-- Header Title -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-900">Hall of Fame Pembda 🏆</h2>
            <p class="text-[11px] text-slate-500 font-bold">Papan Peringkat Reputasi & Prestasi Warga Sekolah</p>
        </div>
        <div class="w-10 h-10 rounded-2xl clay-yellow flex items-center justify-center text-xl font-black">
            🌟
        </div>
    </div>

    <!-- Category Tabs (Siswa vs Guru) -->
    <div class="grid grid-cols-2 gap-2 p-1.5 bg-slate-200/70 rounded-2xl border-2 border-slate-300">
        <button @click="activeTab = 'siswa'"
                :class="activeTab === 'siswa' ? 'bg-amber-400 text-slate-900 border-2 border-black shadow-md font-black' : 'text-slate-600 font-extrabold hover:text-slate-900'"
                class="py-2.5 px-3 rounded-xl text-xs transition flex items-center justify-center gap-1.5">
            🎓 Siswa Terbaik
        </button>
        <button @click="activeTab = 'guru'"
                :class="activeTab === 'guru' ? 'bg-purple-600 text-white border-2 border-black shadow-md font-black' : 'text-slate-600 font-extrabold hover:text-slate-900'"
                class="py-2.5 px-3 rounded-xl text-xs transition flex items-center justify-center gap-1.5">
            👨‍🏫 Guru Terbaik
        </button>
    </div>

    <!-- Tab 1: Top Siswa -->
    <div x-show="activeTab === 'siswa'" x-transition class="space-y-3">
        <h3 class="text-xs font-black text-slate-600 uppercase tracking-wider px-1 flex items-center gap-1.5">
            <i class="fa-solid fa-graduation-cap text-amber-500"></i> Peringkat Reputasi Siswa
        </h3>

        @forelse($topStudents as $index => $std)
            <div class="clay-card p-4 flex items-center justify-between bg-white border-2 border-slate-200">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-sm text-white shadow-md border border-white/30 shrink-0
                        {{ $index == 0 ? 'bg-gradient-to-br from-amber-400 to-amber-600 text-slate-900' : ($index == 1 ? 'bg-gradient-to-br from-slate-300 to-slate-500 text-slate-900' : ($index == 2 ? 'bg-gradient-to-br from-amber-700 to-amber-900' : 'bg-gradient-to-br from-blue-500 to-indigo-600')) }}">
                        {{ $index == 0 ? '🥇' : ($index == 1 ? '🥈' : ($index == 2 ? '🥉' : '#' . ($index + 1))) }}
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-slate-900 uppercase leading-snug">{{ $std->full_name }}</h4>
                        <span class="text-[10px] font-bold text-slate-500 block">
                            NIS: {{ $std->nis ?? '-' }} {{ isset($std->classroom) ? '&bull; Kelas ' . ($std->classroom->name ?? '-') : '' }}
                        </span>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-xs font-black text-amber-600 block">+{{ number_format($std->user->reputation->total_points ?? $std->reputation_points ?? 0) }} Pts</span>
                    <span class="text-[9px] font-bold text-slate-400">Poin Reputasi</span>
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-3xl">🎓</div>
                <p>Belum ada data papan peringkat reputasi siswa.</p>
            </div>
        @endforelse
    </div>

    <!-- Tab 2: Top Guru -->
    <div x-show="activeTab === 'guru'" x-transition class="space-y-3">
        <h3 class="text-xs font-black text-slate-600 uppercase tracking-wider px-1 flex items-center gap-1.5">
            <i class="fa-solid fa-chalkboard-user text-purple-600"></i> Peringkat Reputasi Guru
        </h3>

        @forelse($topTeachers as $index => $tcher)
            <div class="clay-card p-4 flex items-center justify-between bg-white border-2 border-slate-200">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-sm text-white shadow-md border border-white/30 shrink-0
                        {{ $index == 0 ? 'bg-gradient-to-br from-amber-400 to-amber-600 text-slate-900' : ($index == 1 ? 'bg-gradient-to-br from-purple-400 to-purple-600' : ($index == 2 ? 'bg-gradient-to-br from-indigo-500 to-indigo-700' : 'bg-gradient-to-br from-slate-600 to-slate-800')) }}">
                        {{ $index == 0 ? '🥇' : ($index == 1 ? '🥈' : ($index == 2 ? '🥉' : '#' . ($index + 1))) }}
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-slate-900 uppercase leading-snug">{{ $tcher->full_name }}</h4>
                        <span class="text-[10px] font-bold text-purple-600 block">
                            NIP: {{ $tcher->nip ?? '-' }}
                        </span>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-xs font-black text-purple-600 block">+{{ number_format($tcher->user->reputation->total_points ?? $tcher->reputation_points ?? 0) }} Pts</span>
                    <span class="text-[9px] font-bold text-slate-400">Poin Guru</span>
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-3xl">👨‍🏫</div>
                <p>Belum ada data papan peringkat reputasi guru.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
