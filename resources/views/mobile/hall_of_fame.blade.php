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
                class="py-2.5 px-2 text-[11px] sm:text-xs transition flex items-center justify-center gap-1">
            🎓 Top Elite Students
        </button>
        <button @click="activeTab = 'guru'"
                :class="activeTab === 'guru' ? 'bg-purple-600 text-white border-2 border-black shadow-md font-black' : 'text-slate-600 font-extrabold hover:text-slate-900'"
                class="py-2.5 px-2 text-[11px] sm:text-xs transition flex items-center justify-center gap-1">
            ✨ Inspirational Teachers
        </button>
    </div>

    <!-- Tab 1: Top Siswa -->
    <div x-show="activeTab === 'siswa'" x-transition class="space-y-3">
        <h3 class="text-xs font-black text-slate-600 uppercase tracking-wider px-1 flex items-center gap-1.5">
            <i class="fa-solid fa-graduation-cap text-amber-500"></i> Peringkat Top Elite Students
        </h3>

        @forelse($topStudents as $index => $std)
            @php
                $studentPhoto = $std->photo_url ?? null;
                if (!$studentPhoto || str_contains($studentPhoto, 'default-student.jpg') || str_contains($studentPhoto, 'default-avatar')) {
                    if (isset($std->user->avatar_url) && $std->user->avatar_url) {
                        $studentPhoto = $std->user->avatar_url;
                    } else {
                        $studentPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($std->full_name) . '&background=f59e0b&color=fff&bold=true';
                    }
                }
            @endphp
            <div class="clay-card p-4 flex items-center justify-between bg-white border-2 border-slate-200">
                <div class="flex items-center space-x-3.5">
                    <!-- Photo Profile Avatar & Rank Badge -->
                    <div class="relative shrink-0">
                        <img src="{{ $studentPhoto }}" alt="{{ $std->full_name }}"
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($std->full_name) }}&background=f59e0b&color=fff&bold=true';"
                             class="w-12 h-12 rounded-2xl object-cover border-2 {{ $index == 0 ? 'border-amber-400 ring-2 ring-amber-300' : ($index == 1 ? 'border-slate-400' : ($index == 2 ? 'border-amber-700' : 'border-slate-300')) }} shadow-md">
                        
                        <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center font-black text-[10px] text-white shadow-md border border-white
                            {{ $index == 0 ? 'bg-amber-500' : ($index == 1 ? 'bg-slate-400' : ($index == 2 ? 'bg-amber-700' : 'bg-blue-600')) }}">
                            {{ $index == 0 ? '🥇' : ($index == 1 ? '🥈' : ($index == 2 ? '🥉' : '#' . ($index + 1))) }}
                        </span>
                    </div>

                    <div>
                        <h4 class="text-xs font-black text-slate-900 uppercase leading-snug">{{ $std->full_name }}</h4>
                        <span class="text-[10px] font-black text-amber-700 block mt-0.5">
                            🏫 {{ $std->school->name ?? ($std->user->school->name ?? 'Unit Sekolah Pembda') }} {{ isset($std->classroom) ? '&bull; Kelas ' . ($std->classroom->name ?? '-') : '' }}
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
            <i class="fa-solid fa-chalkboard-user text-purple-600"></i> Peringkat Inspirational Teachers
        </h3>

        @forelse($topTeachers as $index => $tcher)
            @php
                $teacherPhoto = $tcher->photo_url ?? null;
                if (!$teacherPhoto || str_contains($teacherPhoto, 'default-teacher.jpg') || str_contains($teacherPhoto, 'default-avatar')) {
                    if (isset($tcher->user->avatar_url) && $tcher->user->avatar_url) {
                        $teacherPhoto = $tcher->user->avatar_url;
                    } else {
                        $teacherPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($tcher->full_name) . '&background=9333ea&color=fff&bold=true';
                    }
                }
            @endphp
            <div class="clay-card p-4 flex items-center justify-between bg-white border-2 border-slate-200">
                <div class="flex items-center space-x-3.5">
                    <!-- Photo Profile Avatar & Rank Badge -->
                    <div class="relative shrink-0">
                        <img src="{{ $teacherPhoto }}" alt="{{ $tcher->full_name }}"
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($tcher->full_name) }}&background=9333ea&color=fff&bold=true';"
                             class="w-12 h-12 rounded-2xl object-cover border-2 {{ $index == 0 ? 'border-amber-400 ring-2 ring-amber-300' : ($index == 1 ? 'border-purple-400' : ($index == 2 ? 'border-indigo-400' : 'border-slate-300')) }} shadow-md">
                        
                        <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center font-black text-[10px] text-white shadow-md border border-white
                            {{ $index == 0 ? 'bg-amber-500' : ($index == 1 ? 'bg-purple-600' : ($index == 2 ? 'bg-indigo-600' : 'bg-slate-700')) }}">
                            {{ $index == 0 ? '🥇' : ($index == 1 ? '🥈' : ($index == 2 ? '🥉' : '#' . ($index + 1))) }}
                        </span>
                    </div>

                    <div>
                        <h4 class="text-xs font-black text-slate-900 uppercase leading-snug">{{ $tcher->full_name }}</h4>
                        <span class="text-[10px] font-black text-purple-700 block mt-0.5">
                            🏫 {{ $tcher->school->name ?? ($tcher->user->school->name ?? 'Unit Sekolah Pembda') }} {{ isset($tcher->position) ? '&bull; ' . $tcher->position : '' }}
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
