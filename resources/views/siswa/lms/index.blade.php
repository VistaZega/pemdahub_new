@extends('layouts.siswa')

@section('title', 'LMS - Portal Siswa PembdaHUB')

@push('styles')
<style>
    .course-card { animation: fadeUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) both; }
    .course-card:nth-child(1) { animation-delay: 0s; }
    .course-card:nth-child(2) { animation-delay: 0.05s; }
    .course-card:nth-child(3) { animation-delay: 0.10s; }
    .course-card:nth-child(4) { animation-delay: 0.15s; }
    .course-card:nth-child(5) { animation-delay: 0.20s; }
    .course-card:nth-child(6) { animation-delay: 0.25s; }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(14px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    {{-- ═══════════════════════════════════════════════ --}}
    {{-- HERO BANNER (UI-UX PRO MAX SISWA UI) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="relative bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 md:p-8 overflow-hidden shadow-xl border border-slate-700/60">
        {{-- Background Glow --}}
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="flex items-center gap-3.5 mb-3">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center shadow-inner backdrop-blur-md">
                        <i class="fas fa-graduation-cap text-xl text-indigo-400"></i>
                    </div>
                    <div>
                        <span class="text-indigo-400 text-[11px] font-bold uppercase tracking-widest block">LMS Ruang Belajar Siswa</span>
                        <h2 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">Halo, {{ explode(' ', $student->user->name ?? 'Siswa')[0] }}! 👋</h2>
                    </div>
                </div>
                <p class="text-slate-300 text-sm max-w-lg leading-relaxed">Akses modul pembelajaran digital, kerjakan tugas sekolah, dan jawab kuis evaluasi interaktif.</p>
            </div>

            {{-- Quick Stats & Gamification --}}
            <div class="flex items-center gap-3 flex-wrap">
                @php
                    $totalCourses = $courses->count();
                    $avgProgress = $totalCourses > 0 ? round(collect($courseProgress)->avg()) : 0;
                    $completedCourses = collect($courseProgress)->filter(fn($p) => $p >= 100)->count();
                    
                    $reputation = null;
                    if ($student->user_id) {
                        $reputation = \App\Models\Reputation::firstOrCreate(
                            ['user_id' => $student->user_id],
                            ['total_points' => 0, 'level_name' => 'Newbie']
                        );
                    }
                @endphp
                <div class="bg-slate-800/60 backdrop-blur-md border border-slate-700/80 rounded-2xl px-5 py-3.5 text-center min-w-[95px] shadow-sm">
                    <div class="text-2xl font-black text-white leading-none">{{ $totalCourses }}</div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-1.5">Course</div>
                </div>
                <div class="bg-slate-800/60 backdrop-blur-md border border-slate-700/80 rounded-2xl px-5 py-3.5 text-center min-w-[95px] shadow-sm">
                    <div class="text-2xl font-black text-emerald-400 leading-none">{{ $avgProgress }}%</div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-1.5">Progress</div>
                </div>
                
                @if($reputation)
                <div class="bg-amber-500/10 backdrop-blur-md border border-amber-500/30 rounded-2xl px-5 py-3.5 text-center min-w-[110px] shadow-sm">
                    <div class="text-xl font-black text-amber-400 flex items-center justify-center gap-1.5 leading-none">
                        <i class="fas fa-star text-sm"></i> {{ number_format($reputation->total_points) }}
                    </div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-amber-300 mt-1.5">{{ $reputation->level_name }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>


    {{-- ═══════════════════════════════════════════════ --}}
    {{-- UPCOMING DEADLINES WIDGET --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @if((isset($upcomingAssignments) && $upcomingAssignments->count() > 0) || (isset($upcomingQuizzes) && $upcomingQuizzes->count() > 0))
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/90 space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 flex items-center justify-center font-bold">
                    <i class="fas fa-bell text-base"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Tenggat Waktu Minggu Ini</h3>
                    <p class="text-xs text-slate-500">Tugas & Kuis yang harus Anda selesaikan segera</p>
                </div>
            </div>
            <span class="px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-xl border border-amber-200">
                {{ ($upcomingAssignments->count() ?? 0) + ($upcomingQuizzes->count() ?? 0) }} Item Jatuh Tempo
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
            @foreach($upcomingAssignments as $asgn)
            @php
                $diffHours = \Carbon\Carbon::now()->diffInHours(\Carbon\Carbon::parse($asgn->deadline), false);
                $diffDays = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($asgn->deadline), false);
                $isUrgent = $diffHours <= 24;
            @endphp
            <div class="flex items-start justify-between p-4 rounded-2xl border transition-all {{ $isUrgent ? 'bg-rose-50/60 border-rose-200' : 'bg-slate-50/70 border-slate-200/80' }}">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center shrink-0 font-bold text-xs">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ $asgn->course->subject->subject_name ?? 'Tugas' }}</span>
                        <h4 class="font-bold text-slate-900 text-xs leading-snug line-clamp-1">{{ $asgn->title }}</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            <i class="far fa-clock mr-1"></i> Deadline: {{ \Carbon\Carbon::parse($asgn->deadline)->translatedFormat('d M Y, H:i') }}
                        </p>
                    </div>
                </div>
                <div>
                    <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-md uppercase tracking-wider border {{ $isUrgent ? 'bg-rose-600 text-white border-rose-700' : 'bg-amber-100 text-amber-800 border-amber-200' }}">
                        {{ $diffHours <= 0 ? 'Hari Ini' : ($diffHours < 24 ? $diffHours.' Jam lagi' : $diffDays.' Hari lagi') }}
                    </span>
                </div>
            </div>
            @endforeach

            @foreach($upcomingQuizzes as $qz)
            <div class="flex items-start justify-between p-4 rounded-2xl border border-indigo-100 bg-indigo-50/40">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 font-bold text-xs">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600">{{ $qz->course->subject->subject_name ?? 'Kuis' }}</span>
                        <h4 class="font-bold text-slate-900 text-xs leading-snug line-clamp-1">{{ $qz->title }}</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            <i class="fas fa-list-ol mr-1"></i> {{ $qz->questions_count ?? 0 }} Soal Evaluasi
                        </p>
                    </div>
                </div>
                <div>
                    <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-md uppercase tracking-wider bg-indigo-600 text-white">
                        Kuis Aktif
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif


    {{-- ═══════════════════════════════════════════════ --}}
    {{-- GAMIFICATION LEADERBOARD WIDGET --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @if(isset($leaderboard) && $leaderboard->count() > 0)
    <div class="rounded-3xl p-6 shadow-xl border border-slate-800 text-white bg-slate-900">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center font-bold shrink-0">
                    <i class="fas fa-trophy text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Papan Peringkat Pembelajar Teraktif</h3>
                    <p class="text-xs text-slate-400 font-medium">Siswa dengan perolehan Poin EXP terbanyak minggu ini</p>
                </div>
            </div>
            <span class="px-3 py-1 bg-amber-400/20 text-amber-300 border border-amber-400/30 text-xs font-bold rounded-xl uppercase tracking-wider">
                Top 5 Siswa 🏆
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            @foreach($leaderboard as $lb)
            @php
                $rankBadge = match($loop->iteration) {
                    1 => '🥇 #1',
                    2 => '🥈 #2',
                    3 => '🥉 #3',
                    default => '#' . $loop->iteration,
                };
            @endphp
            <div class="bg-slate-800/80 border border-slate-700/80 p-3.5 rounded-2xl flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center font-black text-xs shrink-0">
                    {{ $rankBadge }}
                </div>
                <div class="min-w-0 flex-1">
                    <h4 class="font-bold text-white text-xs truncate">{{ $lb->user->name ?? 'Siswa' }}</h4>
                    <p class="text-[10px] text-amber-400 font-semibold flex items-center gap-1 mt-0.5">
                        <i class="fas fa-star text-[9px]"></i> {{ number_format($lb->total_points) }} EXP
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif


    {{-- ═══════════════════════════════════════════════ --}}
    {{-- COURSE GRID --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @if($courses->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($courses as $course)
        @php
            $scientist = $course->getScientistConfig();
            $consolidatedSchedules = $course->getConsolidatedSchedule();
            $progress = $courseProgress[$course->id] ?? 0;
        @endphp
        <div class="course-card group">
            <div class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 h-full flex flex-col hover:-translate-y-1">
                {{-- Card Header --}}
                <div class="relative p-5 bg-slate-900 text-white overflow-hidden border-b border-slate-800">
                    @if($course->meeting_active)
                    <a href="{{ route('siswa.lms.meeting.join', $course->id) }}" id="live-badge-{{ $course->id }}" class="absolute top-4 right-4 z-20 flex items-center gap-1.5 bg-rose-600 text-white px-3 py-1 rounded-xl text-[10px] font-bold uppercase tracking-wider shadow-sm">
                        <span class="w-2 h-2 bg-white rounded-full inline-block animate-ping"></span>
                        LIVE
                    </a>
                    @else
                    <a href="#" id="live-badge-{{ $course->id }}" class="absolute top-4 right-4 z-20 hidden items-center gap-1.5 bg-rose-600 text-white px-3 py-1 rounded-xl text-[10px] font-bold uppercase tracking-wider shadow-sm">
                        <span class="w-2 h-2 bg-white rounded-full inline-block animate-ping"></span>
                        LIVE
                    </a>
                    @endif

                    <div class="relative z-10">
                        <div class="flex items-start gap-3 mb-3">
                            <div class="w-11 h-11 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center shrink-0 shadow-sm text-emerald-400">
                                @if($scientist)
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $scientist['icon'] !!}</svg>
                                @else
                                <i class="fas fa-graduation-cap text-lg"></i>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-bold text-white text-base leading-snug line-clamp-2 group-hover:text-emerald-300 transition-colors">{{ $course->course_name ?? $course->name }}</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="bg-slate-800 text-slate-300 px-2.5 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-wider border border-slate-700">{{ $course->getShortCode() }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Teacher --}}
                        <div class="flex items-center gap-2 text-amber-400 text-xs font-semibold">
                            <i class="fas fa-chalkboard-teacher text-xs"></i>
                            <span class="truncate">Pengajar: {{ $course->teacher->user->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="p-5 flex-1 flex flex-col bg-white">
                    {{-- Schedule Badges --}}
                    @if($consolidatedSchedules->isNotEmpty())
                    <div class="mb-3 flex flex-wrap gap-1.5">
                        @foreach($consolidatedSchedules as $schLabel)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                            <i class="far fa-clock mr-1 text-slate-500"></i> {{ $schLabel }}
                        </span>
                        @endforeach
                    </div>
                    @endif

                    {{-- Course Description --}}
                    <p class="text-slate-600 text-xs mb-4 line-clamp-2 leading-relaxed">{{ $course->description ?: 'Belajar ' . ($course->subject->subject_name ?? '') . ' secara interaktif.' }}</p>

                    {{-- Mini Stats --}}
                    <div class="flex items-center justify-between text-xs text-slate-600 font-semibold mb-4 bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="flex items-center gap-1.5"><i class="fas fa-folder text-emerald-600 text-xs"></i> {{ $course->modules_count ?? 0 }} Modul</span>
                        <span>·</span>
                        <span class="flex items-center gap-1.5"><i class="fas fa-file-alt text-sky-600 text-xs"></i> {{ $course->materials_count ?? 0 }} Materi</span>
                        <span>·</span>
                        <span class="flex items-center gap-1.5"><i class="fas fa-tasks text-amber-600 text-xs"></i> {{ $course->assignments_count ?? 0 }} Tugas</span>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="mb-4">
                        <div class="flex justify-between text-xs mb-1.5 font-bold uppercase tracking-wider text-slate-700">
                            <span>
                                {{ $progress >= 80 ? '🔥 Hampir Selesai!' : ($progress >= 40 ? '📖 Sedang Belajar' : ($progress > 0 ? '🚀 Baru Mulai' : '📚 Belum Dimulai')) }}
                            </span>
                            <span class="text-emerald-600 font-extrabold">{{ number_format($progress) }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden border border-slate-200/80">
                            <div class="h-full rounded-full transition-all duration-1000 ease-out bg-emerald-500" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>

                    {{-- CTA --}}
                    <div class="mt-auto">
                        <a href="{{ route('siswa.lms.show', $course->id) }}"
                           class="flex items-center justify-center gap-2 w-full bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-xl transition-all shadow-sm hover:shadow-md text-xs font-bold tracking-wide">
                            <i class="fas fa-door-open text-xs"></i> Masuk Ruang Belajar
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @else
    {{-- Empty State --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-12 text-center max-w-xl mx-auto">
        <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-indigo-100">
            <i class="fas fa-book-open text-2xl"></i>
        </div>
        <h3 class="text-base font-bold text-slate-800 mb-1">Belum Ada Course Terdaftar</h3>
        <p class="text-slate-500 text-xs max-w-sm mx-auto">Anda belum terdaftar di course manapun. Hubungi guru Anda untuk didaftarkan ke kelas.</p>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function checkLiveMeetings() {
            fetch("{{ route('siswa.lms.live-status') }}")
                .then(response => response.json())
                .then(data => {
                    document.querySelectorAll('[id^="live-badge-"]').forEach(badge => {
                        badge.classList.add('hidden');
                        badge.classList.remove('flex');
                        badge.href = '#';
                    });
                    
                    if (data.live && data.live.length > 0) {
                        data.live.forEach(course => {
                            const badge = document.getElementById('live-badge-' + course.id);
                            if (badge) {
                                badge.classList.remove('hidden');
                                badge.classList.add('flex');
                                badge.href = course.join_url;
                            }
                        });
                    }
                })
                .catch(error => console.error('Error fetching live status:', error));
        }

        setInterval(checkLiveMeetings, 30000);
        checkLiveMeetings();
    });
</script>
@endpush
