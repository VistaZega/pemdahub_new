@extends(auth()->user()->layout)

@section('title', 'Hall of Fame - Pembda Elite')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto space-y-8" x-data="leaderboardTooltip()">

    {{-- ═══════════════════ HERO HEADER BANNER (VIBRANT NEO-BRUTALISM) ═══════════════════ --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-amber-500 via-purple-600 to-indigo-700 rounded-[2.5rem] p-8 md:p-10 shadow-2xl border-2 border-black">
        <div class="absolute -top-12 -right-12 w-64 h-64 bg-amber-300/30 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -left-12 w-64 h-64 bg-purple-400/30 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6 text-white">
            <div class="space-y-3 text-center md:text-left">
                <div class="inline-flex items-center gap-2 bg-black px-4 py-1.5 rounded-full border border-black text-xs font-black uppercase tracking-wider text-amber-300 shadow-md">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span> Live Leaderboard Ranking
                </div>
                <h1 class="text-3xl md:text-5xl font-black tracking-tight text-white flex items-center justify-center md:justify-start gap-3 drop-shadow-md">
                    <span>🏆</span> Hall of Fame <span class="text-amber-300">Pembda Elite</span>
                </h1>
                <p class="text-indigo-100 font-medium text-xs md:text-sm max-w-xl">
                    Panggung kehormatan tertinggi bagi Siswa dan Guru PembdaHub paling berprestasi, aktif, dan inspiratif.
                </p>
            </div>

            @if(auth()->check() && $userRanking)
            <div class="bg-black border-2 border-black rounded-3xl p-5 shadow-2xl flex items-center gap-6 shrink-0 text-white">
                <div class="text-center px-3">
                    <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Peringkat Anda</div>
                    <div class="text-3xl font-black text-amber-400">#{{ $userRanking }}</div>
                </div>
                <div class="w-0.5 h-10 bg-slate-700"></div>
                <div class="text-center px-3">
                    <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Poin Elite Anda</div>
                    <div class="text-3xl font-black text-emerald-400">{{ number_format(auth()->user()->reputation->total_points ?? 0) }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ═══════════════════ DUAL PARALLEL COLUMNS (SIDE-BY-SIDE) ═══════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">

        {{-- ╔══════════════════════════════════════════════════════════════════╗
           ║ LEFT COLUMN: TOP ELITE STUDENTS (Siswa)                          ║
           ╚══════════════════════════════════════════════════════════════════╝ --}}
        <div class="bg-white rounded-3xl border-2 border-black p-6 shadow-xl space-y-6">
            <div class="flex items-center justify-between border-b-2 border-black pb-4">
                <h2 class="text-lg md:text-xl font-black text-black flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-amber-400 border-2 border-black flex items-center justify-center text-black text-sm shadow-xs">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    Top Elite Students (Siswa)
                </h2>
                <span class="bg-purple-100 text-black border-2 border-black text-[10px] font-black px-3 py-1 rounded-xl uppercase">Prestasi Siswa</span>
            </div>

            @php
                $std1 = $topStudents->get(0);
                $std2 = $topStudents->get(1);
                $std3 = $topStudents->get(2);
                $stdRest = $topStudents->slice(3);
            @endphp

            {{-- Top 3 Student Podiums (Enlarged Photos) --}}
            <div class="grid grid-cols-3 gap-3 items-end pt-2 pb-2">
                {{-- Rank 2 --}}
                @if($std2)
                <div class="flex flex-col items-center cursor-pointer transition hover:-translate-y-1"
                     @mouseenter="showTooltip($event, {{ json_encode([
                         'name' => $std2->user->name,
                         'role' => 'Siswa - ' . ($std2->user->student->classroom->class_name ?? 'Siswa'),
                         'photo' => $std2->user->photo_url,
                         'points' => $std2->total_points,
                         'level' => $std2->level_name,
                         'logs' => $std2->user->reputationLogs
                     ]) }})"
                     @mouseleave="hideTooltip()">
                    <div class="mb-3">
                        <img src="{{ $std2->user->photo_url }}" class="w-18 h-18 md:w-22 md:h-22 rounded-full border-4 border-slate-300 object-cover shadow-lg bg-slate-100" />
                    </div>
                    <div class="w-full bg-slate-100 border-2 border-black rounded-2xl p-3 text-center space-y-1 shadow-sm">
                        <span class="inline-block bg-black text-white text-[10px] font-black px-2 py-0.5 rounded-md border border-black">#2</span>
                        <div class="text-xs font-black text-black truncate w-full" title="{{ $std2->user->name }}">{{ Str::words($std2->user->name, 2, '') }}</div>
                        <div class="text-[10px] font-extrabold text-slate-700 truncate">{{ $std2->user->student->classroom->class_name ?? 'Siswa' }}</div>
                        <div class="inline-block bg-black text-amber-300 text-[11px] font-black px-2.5 py-0.5 rounded-lg border border-black shadow-xs">{{ number_format($std2->total_points) }} pts</div>
                    </div>
                </div>
                @endif

                {{-- Rank 1 (Champion) --}}
                @if($std1)
                <div class="flex flex-col items-center cursor-pointer transition hover:-translate-y-1"
                     @mouseenter="showTooltip($event, {{ json_encode([
                         'name' => $std1->user->name,
                         'role' => 'Siswa - ' . ($std1->user->student->classroom->class_name ?? 'Siswa'),
                         'photo' => $std1->user->photo_url,
                         'points' => $std1->total_points,
                         'level' => $std1->level_name,
                         'logs' => $std1->user->reputationLogs
                     ]) }})"
                     @mouseleave="hideTooltip()">
                    <div class="mb-3 relative">
                        <div class="text-amber-500 text-2xl font-black drop-shadow-md text-center mb-1"><i class="fas fa-crown"></i></div>
                        <img src="{{ $std1->user->photo_url }}" class="w-24 h-24 md:w-28 md:h-28 rounded-full border-4 border-amber-400 object-cover shadow-2xl bg-amber-50" />
                    </div>
                    <div class="w-full bg-amber-300 border-2 border-black rounded-2xl p-4 text-center space-y-1 shadow-md">
                        <span class="inline-block bg-black text-amber-300 text-[10px] font-black px-2.5 py-0.5 rounded-md border border-black">#1 JUARA</span>
                        <div class="text-xs md:text-sm font-black text-black truncate w-full" title="{{ $std1->user->name }}">{{ Str::words($std1->user->name, 2, '') }}</div>
                        <div class="text-[10px] font-extrabold text-black truncate">{{ $std1->user->student->classroom->class_name ?? 'Siswa' }}</div>
                        <div class="inline-block bg-black text-emerald-400 text-xs font-black px-3 py-1 rounded-lg border border-black shadow-xs">{{ number_format($std1->total_points) }} pts</div>
                    </div>
                </div>
                @endif

                {{-- Rank 3 --}}
                @if($std3)
                <div class="flex flex-col items-center cursor-pointer transition hover:-translate-y-1"
                     @mouseenter="showTooltip($event, {{ json_encode([
                         'name' => $std3->user->name,
                         'role' => 'Siswa - ' . ($std3->user->student->classroom->class_name ?? 'Siswa'),
                         'photo' => $std3->user->photo_url,
                         'points' => $std3->total_points,
                         'level' => $std3->level_name,
                         'logs' => $std3->user->reputationLogs
                     ]) }})"
                     @mouseleave="hideTooltip()">
                    <div class="mb-3">
                        <img src="{{ $std3->user->photo_url }}" class="w-18 h-18 md:w-22 md:h-22 rounded-full border-4 border-amber-700 object-cover shadow-lg bg-amber-50" />
                    </div>
                    <div class="w-full bg-amber-100 border-2 border-black rounded-2xl p-3 text-center space-y-1 shadow-sm">
                        <span class="inline-block bg-black text-amber-400 text-[10px] font-black px-2 py-0.5 rounded-md border border-black">#3</span>
                        <div class="text-xs font-black text-black truncate w-full" title="{{ $std3->user->name }}">{{ Str::words($std3->user->name, 2, '') }}</div>
                        <div class="text-[10px] font-extrabold text-amber-950 truncate">{{ $std3->user->student->classroom->class_name ?? 'Siswa' }}</div>
                        <div class="inline-block bg-black text-amber-300 text-[11px] font-black px-2.5 py-0.5 rounded-lg border border-black shadow-xs">{{ number_format($std3->total_points) }} pts</div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Student Ranks 4-10 List --}}
            <div class="space-y-2.5 pt-2">
                <div class="text-xs font-black text-black uppercase tracking-wider px-1">Peringkat Selanjutnya:</div>
                @foreach($stdRest as $index => $std)
                <div class="flex items-center justify-between p-3 bg-slate-50 hover:bg-amber-100 rounded-2xl border-2 border-black transition cursor-pointer shadow-xs"
                     @mouseenter="showTooltip($event, {{ json_encode([
                         'name' => $std->user->name,
                         'role' => 'Siswa - ' . ($std->user->student->classroom->class_name ?? 'Siswa'),
                         'photo' => $std->user->photo_url,
                         'points' => $std->total_points,
                         'level' => $std->level_name,
                         'logs' => $std->user->reputationLogs
                     ]) }})"
                     @mouseleave="hideTooltip()">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-7 h-7 rounded-xl bg-black text-white font-black text-xs flex items-center justify-center shrink-0 border border-black">#{{ $index + 4 }}</span>
                        <img src="{{ $std->user->photo_url }}" class="w-11 h-11 rounded-full border-2 border-black object-cover shrink-0" />
                        <div class="min-w-0">
                            <div class="text-xs font-black text-black truncate">{{ $std->user->name }}</div>
                            <div class="text-[11px] font-bold text-slate-600 truncate">{{ $std->user->student->classroom->class_name ?? 'Siswa' }}</div>
                        </div>
                    </div>
                    <div class="text-right shrink-0 ml-2">
                        <span class="bg-black text-amber-300 font-black text-xs px-2.5 py-1 rounded-xl border border-black shadow-xs">{{ number_format($std->total_points) }} pts</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>


        {{-- ╔══════════════════════════════════════════════════════════════════╗
           ║ RIGHT COLUMN: INSPIRATIONAL GURU (Guru)                          ║
           ╚══════════════════════════════════════════════════════════════════╝ --}}
        <div class="bg-white rounded-3xl border-2 border-black p-6 shadow-xl space-y-6">
            <div class="flex items-center justify-between border-b-2 border-black pb-4">
                <h2 class="text-lg md:text-xl font-black text-black flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600 border-2 border-black flex items-center justify-center text-white text-sm shadow-xs">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    Inspirational Teachers (Pendidik)
                </h2>
                <span class="bg-indigo-100 text-black border-2 border-black text-[10px] font-black px-3 py-1 rounded-xl uppercase">Kinerja Guru</span>
            </div>

            @php
                $tch1 = $topTeachers->get(0);
                $tch2 = $topTeachers->get(1);
                $tch3 = $topTeachers->get(2);
                $tchRest = $topTeachers->slice(3);
            @endphp

            {{-- Top 3 Teacher Podiums (Enlarged Photos) --}}
            <div class="grid grid-cols-3 gap-3 items-end pt-2 pb-2">
                {{-- Rank 2 --}}
                @if($tch2)
                <div class="flex flex-col items-center cursor-pointer transition hover:-translate-y-1"
                     @mouseenter="showTooltip($event, {{ json_encode([
                         'name' => $tch2->user->name,
                         'role' => 'Guru - ' . ($tch2->user->teacher->school->name ?? 'Pembda'),
                         'photo' => $tch2->user->photo_url,
                         'points' => $tch2->total_points,
                         'level' => $tch2->level_name,
                         'logs' => $tch2->user->reputationLogs
                     ]) }})"
                     @mouseleave="hideTooltip()">
                    <div class="mb-3">
                        <img src="{{ $tch2->user->photo_url }}" class="w-18 h-18 md:w-22 md:h-22 rounded-full border-4 border-slate-300 object-cover shadow-lg bg-slate-100" />
                    </div>
                    <div class="w-full bg-slate-100 border-2 border-black rounded-2xl p-3 text-center space-y-1 shadow-sm">
                        <span class="inline-block bg-black text-white text-[10px] font-black px-2 py-0.5 rounded-md border border-black">#2</span>
                        <div class="text-xs font-black text-black truncate w-full" title="{{ $tch2->user->name }}">{{ Str::words($tch2->user->name, 2, '') }}</div>
                        <div class="text-[10px] font-extrabold text-slate-700 truncate">{{ $tch2->user->teacher->school->name ?? 'Guru' }}</div>
                        <div class="inline-block bg-black text-amber-300 text-[11px] font-black px-2.5 py-0.5 rounded-lg border border-black shadow-xs">{{ number_format($tch2->total_points) }} pts</div>
                    </div>
                </div>
                @endif

                {{-- Rank 1 (Master Guru) --}}
                @if($tch1)
                <div class="flex flex-col items-center cursor-pointer transition hover:-translate-y-1"
                     @mouseenter="showTooltip($event, {{ json_encode([
                         'name' => $tch1->user->name,
                         'role' => 'Guru - ' . ($tch1->user->teacher->school->name ?? 'Pembda'),
                         'photo' => $tch1->user->photo_url,
                         'points' => $tch1->total_points,
                         'level' => $tch1->level_name,
                         'logs' => $tch1->user->reputationLogs
                     ]) }})"
                     @mouseleave="hideTooltip()">
                    <div class="mb-3 relative">
                        <div class="text-indigo-600 text-2xl font-black drop-shadow-md text-center mb-1"><i class="fas fa-medal"></i></div>
                        <img src="{{ $tch1->user->photo_url }}" class="w-24 h-24 md:w-28 md:h-28 rounded-full border-4 border-indigo-600 object-cover shadow-2xl bg-indigo-50" />
                    </div>
                    <div class="w-full bg-indigo-200 border-2 border-black rounded-2xl p-4 text-center space-y-1 shadow-md">
                        <span class="inline-block bg-black text-amber-300 text-[10px] font-black px-2.5 py-0.5 rounded-md border border-black">#1 MASTER</span>
                        <div class="text-xs md:text-sm font-black text-black truncate w-full" title="{{ $tch1->user->name }}">{{ Str::words($tch1->user->name, 2, '') }}</div>
                        <div class="text-[10px] font-extrabold text-indigo-950 truncate">{{ $tch1->user->teacher->school->name ?? 'Guru' }}</div>
                        <div class="inline-block bg-black text-emerald-400 text-xs font-black px-3 py-1 rounded-lg border border-black shadow-xs">{{ number_format($tch1->total_points) }} pts</div>
                    </div>
                </div>
                @endif

                {{-- Rank 3 --}}
                @if($tch3)
                <div class="flex flex-col items-center cursor-pointer transition hover:-translate-y-1"
                     @mouseenter="showTooltip($event, {{ json_encode([
                         'name' => $tch3->user->name,
                         'role' => 'Guru - ' . ($tch3->user->teacher->school->name ?? 'Pembda'),
                         'photo' => $tch3->user->photo_url,
                         'points' => $tch3->total_points,
                         'level' => $tch3->level_name,
                         'logs' => $tch3->user->reputationLogs
                     ]) }})"
                     @mouseleave="hideTooltip()">
                    <div class="mb-3">
                        <img src="{{ $tch3->user->photo_url }}" class="w-18 h-18 md:w-22 md:h-22 rounded-full border-4 border-amber-700 object-cover shadow-lg bg-amber-50" />
                    </div>
                    <div class="w-full bg-indigo-50 border-2 border-black rounded-2xl p-3 text-center space-y-1 shadow-sm">
                        <span class="inline-block bg-black text-amber-400 text-[10px] font-black px-2 py-0.5 rounded-md border border-black">#3</span>
                        <div class="text-xs font-black text-black truncate w-full" title="{{ $tch3->user->name }}">{{ Str::words($tch3->user->name, 2, '') }}</div>
                        <div class="text-[10px] font-extrabold text-slate-700 truncate">{{ $tch3->user->teacher->school->name ?? 'Guru' }}</div>
                        <div class="inline-block bg-black text-amber-300 text-[11px] font-black px-2.5 py-0.5 rounded-lg border border-black shadow-xs">{{ number_format($tch3->total_points) }} pts</div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Teacher Ranks 4-10 List --}}
            <div class="space-y-2.5 pt-2">
                <div class="text-xs font-black text-black uppercase tracking-wider px-1">Pendidik Selanjutnya:</div>
                @foreach($tchRest as $index => $tch)
                <div class="flex items-center justify-between p-3 bg-slate-50 hover:bg-indigo-100 rounded-2xl border-2 border-black transition cursor-pointer shadow-xs"
                     @mouseenter="showTooltip($event, {{ json_encode([
                         'name' => $tch->user->name,
                         'role' => 'Guru - ' . ($tch->user->teacher->school->name ?? 'Pembda'),
                         'photo' => $tch->user->photo_url,
                         'points' => $tch->total_points,
                         'level' => $tch->level_name,
                         'logs' => $tch->user->reputationLogs
                     ]) }})"
                     @mouseleave="hideTooltip()">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-7 h-7 rounded-xl bg-black text-white font-black text-xs flex items-center justify-center shrink-0 border border-black">#{{ $index + 4 }}</span>
                        <img src="{{ $tch->user->photo_url }}" class="w-11 h-11 rounded-full border-2 border-black object-cover shrink-0" />
                        <div class="min-w-0">
                            <div class="text-xs font-black text-black truncate">{{ $tch->user->name }}</div>
                            <div class="text-[11px] font-bold text-slate-600 truncate">{{ $tch->user->teacher->school->name ?? 'Guru' }}</div>
                        </div>
                    </div>
                    <div class="text-right shrink-0 ml-2">
                        <span class="bg-black text-amber-300 font-black text-xs px-2.5 py-1 rounded-xl border border-black shadow-xs">{{ number_format($tch->total_points) }} pts</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- ═══════════════════ SMART COMPACT POPOVER (ZERO FACE OVERLAP) ═══════════════════ --}}
    <div x-show="visible" 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         :style="`top: ${position.y}px; left: ${position.x}px;`"
         :class="tooltipClass"
         style="display: none;">
        
        <div class="flex items-center gap-2.5 border-b-2 border-black pb-2">
            <img :src="activeUser.photo" class="w-8 h-8 rounded-full border border-black object-cover shrink-0 bg-slate-100" />
            <div class="min-w-0">
                <h4 class="text-xs font-black text-black truncate" x-text="activeUser.name"></h4>
                <div class="flex items-center gap-1">
                    <span class="bg-black text-amber-300 text-[8px] font-black px-1.5 py-0.2 rounded uppercase" x-text="activeUser.level"></span>
                    <span class="bg-amber-300 text-black text-[8px] font-black px-1.5 py-0.2 rounded" x-text="activeUser.points + ' pts'"></span>
                </div>
            </div>
        </div>

        <div>
            <div class="text-[9px] font-black text-black uppercase tracking-wider mb-1 flex items-center gap-1">
                <i class="fas fa-history text-indigo-600"></i> Riwayat Asal Poin:
            </div>
            
            <template x-if="activeUser.logs && activeUser.logs.length > 0">
                <div class="space-y-1 max-h-36 overflow-y-auto pr-1">
                    <template x-for="log in activeUser.logs" :key="log.id">
                        <div class="p-1.5 bg-slate-100 rounded-lg border border-black text-[10px]">
                            <div class="flex items-center justify-between gap-1">
                                <span class="font-black text-emerald-800" x-text="'+' + log.points + ' Pts'"></span>
                                <span class="text-[8px] font-extrabold text-slate-600 uppercase" x-text="log.category"></span>
                            </div>
                            <div class="font-black text-black text-[9px] leading-tight truncate" x-text="log.description || 'Aktivitas LMS / PembdaHub'"></div>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="!activeUser.logs || activeUser.logs.length === 0">
                <div class="p-2 bg-slate-100 rounded-lg border border-black text-center text-[9px] font-black text-slate-600">
                    Belum ada riwayat transaksi poin.
                </div>
            </template>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
function leaderboardTooltip() {
    return {
        visible: false,
        position: { x: 0, y: 0 },
        tooltipClass: 'fixed z-50 w-64 bg-white border-2 border-black rounded-2xl p-3.5 shadow-2xl space-y-2 pointer-events-none transform -translate-x-1/2',
        activeUser: {
            name: '',
            role: '',
            photo: '',
            points: 0,
            level: '',
            logs: []
        },
        showTooltip(event, userData) {
            this.activeUser = userData;
            let rect = event.currentTarget.getBoundingClientRect();
            
            // Check space below vs space above
            let spaceBelow = window.innerHeight - rect.bottom;
            
            if (spaceBelow >= 180) {
                // Position BELOW the card/avatar so faces are 100% UNCOVERED
                this.position.x = rect.left + (rect.width / 2);
                this.position.y = rect.bottom + 8;
                this.tooltipClass = 'fixed z-50 w-64 bg-white border-2 border-black rounded-2xl p-3.5 shadow-2xl space-y-2 pointer-events-none transform -translate-x-1/2';
            } else {
                // Position ABOVE if at the very bottom of the screen
                this.position.x = rect.left + (rect.width / 2);
                this.position.y = rect.top - 8;
                this.tooltipClass = 'fixed z-50 w-64 bg-white border-2 border-black rounded-2xl p-3.5 shadow-2xl space-y-2 pointer-events-none transform -translate-x-1/2 -translate-y-full';
            }
            this.visible = true;
        },
        hideTooltip() {
            this.visible = false;
        }
    }
}
</script>
@endpush
