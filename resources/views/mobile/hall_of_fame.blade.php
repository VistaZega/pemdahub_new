@extends('mobile.layouts.app')

@section('title', 'Hall of Fame - Papan Peringkat Prestasi & Teladan')

@section('content')
<div class="space-y-4 pb-14" x-data="{ 
    activeTab: 'siswa',
    searchQuery: ''
}">
    <!-- Header Navigation -->
    <div class="flex items-center justify-between gap-2 px-1 pt-1">
        <div class="flex items-center gap-2.5 min-w-0">
            <a href="{{ route('mobile.dashboard') }}" class="w-9 h-9 rounded-2xl bg-white border-2 border-slate-200 text-slate-700 flex items-center justify-center shadow-xs active:scale-95 transition shrink-0">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            <div class="min-w-0">
                <h2 class="text-base font-black text-slate-900 leading-tight truncate">Hall of Fame 🏆</h2>
                <p class="text-[10px] text-slate-500 font-bold truncate">Papan Peringkat Prestasi & Guru Teladan</p>
            </div>
        </div>

        <div class="w-9 h-9 rounded-2xl bg-gradient-to-tr from-amber-400 to-yellow-300 text-slate-950 flex items-center justify-center text-base shadow-md font-black shrink-0">
            🌟
        </div>
    </div>

    <!-- Category Tabs Segmented Control -->
    <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-200/80 rounded-2xl border border-slate-300 shadow-inner">
        <button @click="activeTab = 'siswa'"
                :class="activeTab === 'siswa' ? 'bg-amber-400 text-slate-950 font-black shadow-sm' : 'text-slate-600 font-bold hover:text-slate-900'"
                class="py-2.5 rounded-xl text-xs transition flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-graduation-cap text-xs"></i>
            <span>🎓 Top Siswa ({{ $topStudents->count() }})</span>
        </button>
        <button @click="activeTab = 'guru'"
                :class="activeTab === 'guru' ? 'bg-purple-600 text-white font-black shadow-sm' : 'text-slate-600 font-bold hover:text-slate-900'"
                class="py-2.5 rounded-xl text-xs transition flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-chalkboard-user text-xs"></i>
            <span>👨‍🏫 Guru Teladan ({{ $topTeachers->count() }})</span>
        </button>
    </div>

    <!-- TAB 1: TOP SISWA (HALL OF FAME) -->
    <div x-show="activeTab === 'siswa'" x-transition class="space-y-4">
        @php
            $podium1 = $topStudents->get(0);
            $podium2 = $topStudents->get(1);
            $podium3 = $topStudents->get(2);
            $remainingStudents = $topStudents->slice(3);
        @endphp

        <!-- 3D Podium for Top 3 Students -->
        @if($podium1)
        <div class="clay-card p-4 sm:p-5 bg-gradient-to-b from-amber-100/70 via-white to-slate-50 border-2 border-amber-300/80 rounded-3xl shadow-md">
            <div class="text-center mb-3">
                <span class="text-[9px] font-black uppercase tracking-wider bg-amber-200 text-amber-900 px-3 py-1 rounded-full border border-amber-300 shadow-xs">
                    👑 Podium Juara Umum Siswa
                </span>
            </div>

            <div class="flex items-end justify-center gap-2 pt-2 pb-1">
                <!-- RANK 2 (Silver) -->
                @if($podium2)
                @php
                    $photo2 = $podium2->photo_url ?? null;
                    if (!$photo2 || str_contains($photo2, 'default-student.jpg') || str_contains($photo2, 'default-avatar')) {
                        $photo2 = isset($podium2->user->avatar_url) && $podium2->user->avatar_url ? $podium2->user->avatar_url : 'https://ui-avatars.com/api/?name=' . urlencode($podium2->full_name) . '&background=94a3b8&color=fff&bold=true';
                    }
                    $pts2 = $podium2->user->reputation->total_points ?? $podium2->reputation_points ?? 0;
                @endphp
                <div class="flex-1 max-w-[105px] text-center flex flex-col items-center">
                    <div class="relative mb-1.5">
                        <img src="{{ $photo2 }}" alt="{{ $podium2->full_name }}" 
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($podium2->full_name) }}&background=94a3b8&color=fff&bold=true';"
                             class="w-13 h-13 rounded-2xl object-cover border-2 border-slate-300 shadow-md ring-2 ring-slate-200">
                        <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-slate-400 text-white font-black text-[10px] flex items-center justify-center border border-white shadow-xs">
                            🥈
                        </span>
                    </div>
                    <h4 class="text-[11px] font-black text-slate-900 truncate w-full leading-tight">{{ $podium2->full_name }}</h4>
                    <p class="text-[9px] font-bold text-slate-500 truncate w-full">{{ $podium2->school->name ?? 'Pembda' }}</p>
                    <div class="w-full mt-2 py-2 px-1 rounded-t-2xl bg-gradient-to-b from-slate-200 to-slate-300 border-t border-slate-300 shadow-inner">
                        <span class="text-[10px] font-black text-slate-800 block leading-tight">{{ number_format($pts2) }}</span>
                        <span class="text-[8px] font-extrabold text-slate-500 uppercase">Pts</span>
                    </div>
                </div>
                @endif

                <!-- RANK 1 (Gold - Highest) -->
                @php
                    $photo1 = $podium1->photo_url ?? null;
                    if (!$photo1 || str_contains($photo1, 'default-student.jpg') || str_contains($photo1, 'default-avatar')) {
                        $photo1 = isset($podium1->user->avatar_url) && $podium1->user->avatar_url ? $podium1->user->avatar_url : 'https://ui-avatars.com/api/?name=' . urlencode($podium1->full_name) . '&background=f59e0b&color=fff&bold=true';
                    }
                    $pts1 = $podium1->user->reputation->total_points ?? $podium1->reputation_points ?? 0;
                @endphp
                <div class="flex-1 max-w-[125px] text-center flex flex-col items-center z-10">
                    <div class="relative mb-2">
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 text-sm animate-bounce">👑</div>
                        <img src="{{ $photo1 }}" alt="{{ $podium1->full_name }}" 
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($podium1->full_name) }}&background=f59e0b&color=fff&bold=true';"
                             class="w-16 h-16 rounded-3xl object-cover border-3 border-amber-400 shadow-xl ring-4 ring-amber-200">
                        <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-amber-500 text-white font-black text-xs flex items-center justify-center border-2 border-white shadow-md">
                            🥇
                        </span>
                    </div>
                    <h4 class="text-xs font-black text-slate-900 truncate w-full leading-tight">{{ $podium1->full_name }}</h4>
                    <p class="text-[9px] font-extrabold text-amber-800 truncate w-full">{{ $podium1->school->name ?? 'Pembda' }}</p>
                    <div class="w-full mt-2 py-3.5 px-1 rounded-t-2xl bg-gradient-to-b from-amber-300 via-amber-400 to-amber-500 border-t border-amber-200 shadow-md">
                        <span class="text-xs font-black text-slate-950 block leading-tight">{{ number_format($pts1) }}</span>
                        <span class="text-[8px] font-black text-amber-950 uppercase tracking-wide">Poin Reputasi</span>
                    </div>
                </div>

                <!-- RANK 3 (Bronze) -->
                @if($podium3)
                @php
                    $photo3 = $podium3->photo_url ?? null;
                    if (!$photo3 || str_contains($photo3, 'default-student.jpg') || str_contains($photo3, 'default-avatar')) {
                        $photo3 = isset($podium3->user->avatar_url) && $podium3->user->avatar_url ? $podium3->user->avatar_url : 'https://ui-avatars.com/api/?name=' . urlencode($podium3->full_name) . '&background=b45309&color=fff&bold=true';
                    }
                    $pts3 = $podium3->user->reputation->total_points ?? $podium3->reputation_points ?? 0;
                @endphp
                <div class="flex-1 max-w-[105px] text-center flex flex-col items-center">
                    <div class="relative mb-1.5">
                        <img src="{{ $photo3 }}" alt="{{ $podium3->full_name }}" 
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($podium3->full_name) }}&background=b45309&color=fff&bold=true';"
                             class="w-13 h-13 rounded-2xl object-cover border-2 border-amber-700 shadow-md ring-2 ring-amber-200">
                        <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-amber-700 text-white font-black text-[10px] flex items-center justify-center border border-white shadow-xs">
                            🥉
                        </span>
                    </div>
                    <h4 class="text-[11px] font-black text-slate-900 truncate w-full leading-tight">{{ $podium3->full_name }}</h4>
                    <p class="text-[9px] font-bold text-slate-500 truncate w-full">{{ $podium3->school->name ?? 'Pembda' }}</p>
                    <div class="w-full mt-2 py-1.5 px-1 rounded-t-2xl bg-gradient-to-b from-amber-600/30 to-amber-700/40 border-t border-amber-400 shadow-inner">
                        <span class="text-[10px] font-black text-slate-800 block leading-tight">{{ number_format($pts3) }}</span>
                        <span class="text-[8px] font-extrabold text-slate-500 uppercase">Pts</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Recent Star Achievements (Sorotan Prestasi) -->
        @if($recentAchievements->isNotEmpty())
        <div class="space-y-2">
            <div class="flex items-center justify-between px-1">
                <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fa-solid fa-star text-amber-500"></i> Sorotan Prestasi Terkini
                </h3>
            </div>

            <div class="flex items-center gap-2.5 overflow-x-auto pb-1 no-scrollbar">
                @foreach($recentAchievements as $ach)
                    <div class="clay-card p-3 bg-white border-2 border-amber-200 rounded-2xl shadow-xs min-w-[200px] max-w-[230px] shrink-0 space-y-1.5">
                        <div class="flex items-center justify-between gap-1">
                            <span class="px-2 py-0.5 rounded-full text-[8px] font-black uppercase bg-amber-100 text-amber-900 border border-amber-300">
                                {{ $ach->level_label }}
                            </span>
                            <span class="text-[11px]">
                                {{ $ach->rank === 'winner' ? '🥇' : ($ach->rank === 'runner_up' ? '🥈' : ($ach->rank === 'third_place' ? '🥉' : '🎖️')) }}
                            </span>
                        </div>
                        <h5 class="text-[11px] font-black text-slate-900 line-clamp-1 leading-snug">{{ $ach->title }}</h5>
                        <p class="text-[9px] font-bold text-slate-500 truncate">🎓 {{ $ach->student->full_name ?? 'Siswa' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Remaining Students Leaderboard List -->
        <div class="space-y-2.5">
            <div class="flex items-center justify-between px-1">
                <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fa-solid fa-ranking-star text-blue-600"></i> Peringkat Reputasi Siswa
                </h3>
                <span class="text-[10px] font-bold text-slate-400">Top 20</span>
            </div>

            @forelse($remainingStudents as $index => $std)
                @php
                    $rankNumber = $index + 4;
                    $stdPhoto = $std->photo_url ?? null;
                    if (!$stdPhoto || str_contains($stdPhoto, 'default-student.jpg') || str_contains($stdPhoto, 'default-avatar')) {
                        $stdPhoto = isset($std->user->avatar_url) && $std->user->avatar_url ? $std->user->avatar_url : 'https://ui-avatars.com/api/?name=' . urlencode($std->full_name) . '&background=6366f1&color=fff&bold=true';
                    }
                    $pts = $std->user->reputation->total_points ?? $std->reputation_points ?? 0;
                @endphp
                <div class="clay-card p-3.5 flex items-center justify-between gap-3 bg-white border-2 border-slate-200/90 rounded-2xl shadow-xs hover:border-purple-300 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-7 text-center font-black text-xs text-slate-500 shrink-0">
                            #{{ $rankNumber }}
                        </div>
                        <img src="{{ $stdPhoto }}" alt="{{ $std->full_name }}" 
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($std->full_name) }}&background=6366f1&color=fff&bold=true';"
                             class="w-10 h-10 rounded-2xl object-cover border border-slate-200 shadow-2xs shrink-0">
                        <div class="min-w-0">
                            <h4 class="text-xs font-black text-slate-900 truncate leading-tight">{{ $std->full_name }}</h4>
                            <p class="text-[9px] font-bold text-slate-400 truncate">
                                {{ $std->school->name ?? 'Pembda' }} {{ isset($std->classroom) ? '• ' . $std->classroom->class_name : '' }}
                            </p>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <span class="text-xs font-black text-amber-600 block leading-none">+{{ number_format($pts) }}</span>
                        <span class="text-[8px] font-bold text-slate-400 uppercase">Pts</span>
                    </div>
                </div>
            @empty
                @if(!$podium1)
                    <div class="clay-card p-8 text-center text-slate-500 space-y-2 bg-white rounded-3xl">
                        <div class="text-3xl">🎓</div>
                        <p class="text-xs font-bold">Belum ada data papan peringkat reputasi siswa.</p>
                    </div>
                @endif
            @endforelse
        </div>
    </div>

    <!-- TAB 2: TOP GURU TELADAN -->
    <div x-show="activeTab === 'guru'" x-transition class="space-y-4">
        @php
            $podiumT1 = $topTeachers->get(0);
            $podiumT2 = $topTeachers->get(1);
            $podiumT3 = $topTeachers->get(2);
            $remainingTeachers = $topTeachers->slice(3);
        @endphp

        <!-- 3D Podium for Top 3 Teachers -->
        @if($podiumT1)
        <div class="clay-card p-4 sm:p-5 bg-gradient-to-b from-purple-100/70 via-white to-slate-50 border-2 border-purple-300/80 rounded-3xl shadow-md">
            <div class="text-center mb-3">
                <span class="text-[9px] font-black uppercase tracking-wider bg-purple-200 text-purple-950 px-3 py-1 rounded-full border border-purple-300 shadow-xs">
                    🌟 Guru Inspiratif & Teladan
                </span>
            </div>

            <div class="flex items-end justify-center gap-2 pt-2 pb-1">
                <!-- RANK 2 GURU (Silver) -->
                @if($podiumT2)
                @php
                    $photoT2 = $podiumT2->photo_url ?? null;
                    if (!$photoT2 || str_contains($photoT2, 'default-teacher.jpg') || str_contains($photoT2, 'default-avatar')) {
                        $photoT2 = isset($podiumT2->user->avatar_url) && $podiumT2->user->avatar_url ? $podiumT2->user->avatar_url : 'https://ui-avatars.com/api/?name=' . urlencode($podiumT2->full_name) . '&background=94a3b8&color=fff&bold=true';
                    }
                    $ptsT2 = $podiumT2->user->reputation->total_points ?? $podiumT2->reputation_points ?? 0;
                @endphp
                <div class="flex-1 max-w-[105px] text-center flex flex-col items-center">
                    <div class="relative mb-1.5">
                        <img src="{{ $photoT2 }}" alt="{{ $podiumT2->full_name }}" 
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($podiumT2->full_name) }}&background=94a3b8&color=fff&bold=true';"
                             class="w-13 h-13 rounded-2xl object-cover border-2 border-slate-300 shadow-md ring-2 ring-slate-200">
                        <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-slate-400 text-white font-black text-[10px] flex items-center justify-center border border-white shadow-xs">
                            🥈
                        </span>
                    </div>
                    <h4 class="text-[11px] font-black text-slate-900 truncate w-full leading-tight">{{ $podiumT2->full_name }}</h4>
                    <p class="text-[9px] font-bold text-slate-500 truncate w-full">{{ $podiumT2->school->name ?? 'Pembda' }}</p>
                    <div class="w-full mt-2 py-2 px-1 rounded-t-2xl bg-gradient-to-b from-slate-200 to-slate-300 border-t border-slate-300 shadow-inner">
                        <span class="text-[10px] font-black text-slate-800 block leading-tight">{{ number_format($ptsT2) }}</span>
                        <span class="text-[8px] font-extrabold text-slate-500 uppercase">Pts</span>
                    </div>
                </div>
                @endif

                <!-- RANK 1 GURU (Gold) -->
                @php
                    $photoT1 = $podiumT1->photo_url ?? null;
                    if (!$photoT1 || str_contains($photoT1, 'default-teacher.jpg') || str_contains($photoT1, 'default-avatar')) {
                        $photoT1 = isset($podiumT1->user->avatar_url) && $podiumT1->user->avatar_url ? $podiumT1->user->avatar_url : 'https://ui-avatars.com/api/?name=' . urlencode($podiumT1->full_name) . '&background=7c3aed&color=fff&bold=true';
                    }
                    $ptsT1 = $podiumT1->user->reputation->total_points ?? $podiumT1->reputation_points ?? 0;
                @endphp
                <div class="flex-1 max-w-[125px] text-center flex flex-col items-center z-10">
                    <div class="relative mb-2">
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 text-sm animate-bounce">👑</div>
                        <img src="{{ $photoT1 }}" alt="{{ $podiumT1->full_name }}" 
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($podiumT1->full_name) }}&background=7c3aed&color=fff&bold=true';"
                             class="w-16 h-16 rounded-3xl object-cover border-3 border-purple-500 shadow-xl ring-4 ring-purple-200">
                        <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-purple-600 text-white font-black text-xs flex items-center justify-center border-2 border-white shadow-md">
                            🥇
                        </span>
                    </div>
                    <h4 class="text-xs font-black text-slate-900 truncate w-full leading-tight">{{ $podiumT1->full_name }}</h4>
                    <p class="text-[9px] font-extrabold text-purple-800 truncate w-full">{{ $podiumT1->school->name ?? 'Pembda' }}</p>
                    <div class="w-full mt-2 py-3.5 px-1 rounded-t-2xl bg-gradient-to-b from-purple-500 to-indigo-600 border-t border-purple-300 text-white shadow-md">
                        <span class="text-xs font-black block leading-tight">{{ number_format($ptsT1) }}</span>
                        <span class="text-[8px] font-bold text-purple-100 uppercase tracking-wide">Poin Dedikasi</span>
                    </div>
                </div>

                <!-- RANK 3 GURU (Bronze) -->
                @if($podiumT3)
                @php
                    $photoT3 = $podiumT3->photo_url ?? null;
                    if (!$photoT3 || str_contains($photoT3, 'default-teacher.jpg') || str_contains($photoT3, 'default-avatar')) {
                        $photoT3 = isset($podiumT3->user->avatar_url) && $podiumT3->user->avatar_url ? $podiumT3->user->avatar_url : 'https://ui-avatars.com/api/?name=' . urlencode($podiumT3->full_name) . '&background=b45309&color=fff&bold=true';
                    }
                    $ptsT3 = $podiumT3->user->reputation->total_points ?? $podiumT3->reputation_points ?? 0;
                @endphp
                <div class="flex-1 max-w-[105px] text-center flex flex-col items-center">
                    <div class="relative mb-1.5">
                        <img src="{{ $photoT3 }}" alt="{{ $podiumT3->full_name }}" 
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($podiumT3->full_name) }}&background=b45309&color=fff&bold=true';"
                             class="w-13 h-13 rounded-2xl object-cover border-2 border-amber-700 shadow-md ring-2 ring-amber-200">
                        <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-amber-700 text-white font-black text-[10px] flex items-center justify-center border border-white shadow-xs">
                            🥉
                        </span>
                    </div>
                    <h4 class="text-[11px] font-black text-slate-900 truncate w-full leading-tight">{{ $podiumT3->full_name }}</h4>
                    <p class="text-[9px] font-bold text-slate-500 truncate w-full">{{ $podiumT3->school->name ?? 'Pembda' }}</p>
                    <div class="w-full mt-2 py-1.5 px-1 rounded-t-2xl bg-gradient-to-b from-amber-600/30 to-amber-700/40 border-t border-amber-400 shadow-inner">
                        <span class="text-[10px] font-black text-slate-800 block leading-tight">{{ number_format($ptsT3) }}</span>
                        <span class="text-[8px] font-extrabold text-slate-500 uppercase">Pts</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Remaining Teachers Leaderboard List -->
        <div class="space-y-2.5">
            <div class="flex items-center justify-between px-1">
                <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fa-solid fa-chalkboard-user text-purple-600"></i> Papan Peringkat Dedikasi Guru
                </h3>
                <span class="text-[10px] font-bold text-slate-400">Top 15</span>
            </div>

            @forelse($remainingTeachers as $index => $tcher)
                @php
                    $rankNum = $index + 4;
                    $tPhoto = $tcher->photo_url ?? null;
                    if (!$tPhoto || str_contains($tPhoto, 'default-teacher.jpg') || str_contains($tPhoto, 'default-avatar')) {
                        $tPhoto = isset($tcher->user->avatar_url) && $tcher->user->avatar_url ? $tcher->user->avatar_url : 'https://ui-avatars.com/api/?name=' . urlencode($tcher->full_name) . '&background=9333ea&color=fff&bold=true';
                    }
                    $pts = $tcher->user->reputation->total_points ?? $tcher->reputation_points ?? 0;
                @endphp
                <div class="clay-card p-3.5 flex items-center justify-between gap-3 bg-white border-2 border-slate-200/90 rounded-2xl shadow-xs hover:border-purple-300 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-7 text-center font-black text-xs text-slate-500 shrink-0">
                            #{{ $rankNum }}
                        </div>
                        <img src="{{ $tPhoto }}" alt="{{ $tcher->full_name }}" 
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($tcher->full_name) }}&background=9333ea&color=fff&bold=true';"
                             class="w-10 h-10 rounded-2xl object-cover border border-slate-200 shadow-2xs shrink-0">
                        <div class="min-w-0">
                            <h4 class="text-xs font-black text-slate-900 truncate leading-tight">{{ $tcher->full_name }}</h4>
                            <p class="text-[9px] font-bold text-slate-400 truncate">
                                {{ $tcher->school->name ?? 'Pembda' }}
                            </p>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <span class="text-xs font-black text-purple-700 block leading-none">+{{ number_format($pts) }}</span>
                        <span class="text-[8px] font-bold text-slate-400 uppercase">Pts</span>
                    </div>
                </div>
            @empty
                @if(!$podiumT1)
                    <div class="clay-card p-8 text-center text-slate-500 space-y-2 bg-white rounded-3xl">
                        <div class="text-3xl">👨‍🏫</div>
                        <p class="text-xs font-bold">Belum ada data papan peringkat guru.</p>
                    </div>
                @endif
            @endforelse
        </div>
    </div>
</div>
@endsection
