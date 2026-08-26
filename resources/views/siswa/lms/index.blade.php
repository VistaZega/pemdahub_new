@extends('layouts.siswa')

@section('title', 'LMS - Portal Siswa')

@push('styles')
<style>
    .course-card { animation: fadeUp 0.4s ease both; }
    .course-card:nth-child(1) { animation-delay: 0s; }
    .course-card:nth-child(2) { animation-delay: 0.06s; }
    .course-card:nth-child(3) { animation-delay: 0.12s; }
    .course-card:nth-child(4) { animation-delay: 0.18s; }
    .course-card:nth-child(5) { animation-delay: 0.24s; }
    .course-card:nth-child(6) { animation-delay: 0.30s; }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .progress-ring { transform: rotate(-90deg); }
    .hero-pattern {
        background-image: radial-gradient(circle at 25% 60%, rgba(255,255,255,0.15) 0%, transparent 50%),
                          radial-gradient(circle at 75% 20%, rgba(255,255,255,0.12) 0%, transparent 40%);
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    {{-- ═══════════════════════════════════════════════ --}}
    {{-- HERO BANNER (CERAH, ENERGIK & MODERN) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="relative bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 rounded-3xl p-6 md:p-8 overflow-hidden shadow-xl shadow-indigo-200/50 text-white">
        {{-- Background decorative shapes --}}
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute right-1/3 -top-10 w-48 h-48 bg-pink-400/20 rounded-full blur-xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="flex items-center gap-3.5 mb-3">
                    <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 flex items-center justify-center shadow-lg shadow-black/5 shrink-0">
                        <i class="fas fa-graduation-cap text-2xl text-white"></i>
                    </div>
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-white/20 backdrop-blur-md text-[11px] font-bold tracking-wider uppercase border border-white/20 mb-1">
                            <i class="fas fa-sparkles text-amber-300 text-xs"></i> Learning Space Siswa
                        </span>
                        <h2 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight drop-shadow-sm">
                            Halo, {{ explode(' ', $student->user->name ?? 'Siswa')[0] }}! 👋
                        </h2>
                    </div>
                </div>
                <p class="text-white/90 font-medium text-sm max-w-lg leading-relaxed">
                    Eksplorasi materi pelajaran interaktif, kerjakan tugas seru, dan ikuti kuis evaluasi untuk mengumpulkan skor terbaikmu!
                </p>
            </div>

            {{-- Quick Stats --}}
            <div class="flex items-center gap-3">
                @php
                    $totalCourses = $courses->count();
                    $avgProgress = $totalCourses > 0 ? round(collect($courseProgress)->avg()) : 0;
                    $completedCourses = collect($courseProgress)->filter(fn($p) => $p >= 100)->count();
                    
                    // Gamification Data
                    $reputation = null;
                    if ($student->user_id) {
                        $reputation = \App\Models\Reputation::firstOrCreate(
                            ['user_id' => $student->user_id],
                            ['total_points' => 0, 'level_name' => 'Newbie']
                        );
                    }
                @endphp
                <div class="bg-white/15 backdrop-blur-md border border-white/25 rounded-2xl px-5 py-3 text-center min-w-[95px] shadow-sm">
                    <div class="text-2xl font-black leading-none text-white drop-shadow-sm">{{ $totalCourses }}</div>
                    <div class="text-[10px] font-bold uppercase tracking-widest text-white/80 mt-1">Course</div>
                </div>
                <div class="bg-white/15 backdrop-blur-md border border-white/25 rounded-2xl px-5 py-3 text-center min-w-[95px] shadow-sm">
                    <div class="text-2xl font-black leading-none text-white drop-shadow-sm">{{ $avgProgress }}%</div>
                    <div class="text-[10px] font-bold uppercase tracking-widest text-white/80 mt-1">Progress</div>
                </div>
                
                @if($reputation)
                <div class="bg-amber-400 text-slate-900 border border-amber-300 rounded-2xl px-5 py-3 text-center min-w-[110px] shadow-md shadow-amber-500/20">
                    <div class="text-2xl font-black leading-none text-slate-900 flex items-center justify-center gap-1">
                        <i class="fas fa-star text-sm text-amber-700"></i> {{ number_format($reputation->total_points) }}
                    </div>
                    <div class="text-[10px] font-extrabold uppercase tracking-widest text-slate-800 mt-1">{{ $reputation->level_name }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>


    {{-- ═══════════════════════════════════════════════ --}}
    {{-- UPCOMING DEADLINES WIDGET --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @if((isset($upcomingAssignments) && $upcomingAssignments->count() > 0) || (isset($upcomingQuizzes) && $upcomingQuizzes->count() > 0))
    <div class="bg-gradient-to-br from-amber-50/90 via-orange-50/40 to-rose-50/70 rounded-3xl p-6 shadow-sm border border-amber-200/80">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white flex items-center justify-center font-bold shadow-md shadow-amber-200 shrink-0">
                    <i class="fas fa-bell text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Tenggat Waktu Minggu Ini</h3>
                    <p class="text-xs text-slate-600 font-medium">Tugas & Kuis yang perlu Anda selesaikan segera</p>
                </div>
            </div>
            <span class="px-3.5 py-1.5 bg-amber-200/80 text-amber-900 text-xs font-extrabold rounded-xl border border-amber-300/80 flex items-center gap-1.5">
                <i class="fas fa-clock text-amber-700 text-xs"></i>
                {{ ($upcomingAssignments->count() ?? 0) + ($upcomingQuizzes->count() ?? 0) }} Item Jatuh Tempo
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($upcomingAssignments as $asgn)
            @php
                $diffHours = \Carbon\Carbon::now()->diffInHours(\Carbon\Carbon::parse($asgn->deadline), false);
                $diffDays = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($asgn->deadline), false);
                $isUrgent = $diffHours <= 24;
            @endphp
            <div class="flex items-start justify-between p-4 rounded-2xl border shadow-sm transition-all hover:shadow-md {{ $isUrgent ? 'bg-white border-rose-200' : 'bg-white border-amber-100' }}">
                <div class="flex items-start gap-3 min-w-0 flex-1">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $isUrgent ? 'from-rose-500 to-pink-600' : 'from-blue-500 to-indigo-600' }} text-white flex items-center justify-center flex-shrink-0 font-bold text-sm shadow-sm">
                        <i class="fas fa-tasks text-white"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-600 block truncate">{{ $asgn->course->subject->subject_name ?? 'Tugas' }}</span>
                        <h4 class="font-bold text-slate-900 text-sm leading-snug line-clamp-1">{{ $asgn->title }}</h4>
                        <p class="text-xs text-slate-500 font-medium mt-0.5 flex items-center gap-1">
                            <i class="far fa-clock text-slate-400"></i> Deadline: {{ \Carbon\Carbon::parse($asgn->deadline)->translatedFormat('d M Y, H:i') }}
                        </p>
                    </div>
                </div>
                <div class="ml-3 shrink-0">
                    <span class="px-3 py-1 text-[10px] font-extrabold rounded-xl uppercase tracking-wider shadow-xs {{ $isUrgent ? 'bg-rose-500 text-white' : 'bg-amber-100 text-amber-800 border border-amber-200' }}">
                        {{ $diffHours <= 0 ? 'Hari Ini' : ($diffHours < 24 ? $diffHours.' Jam lagi' : $diffDays.' Hari lagi') }}
                    </span>
                </div>
            </div>
            @endforeach

            @foreach($upcomingQuizzes as $qz)
            <div class="flex items-start justify-between p-4 rounded-2xl border border-purple-100 bg-white shadow-sm transition-all hover:shadow-md">
                <div class="flex items-start gap-3 min-w-0 flex-1">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 text-white flex items-center justify-center flex-shrink-0 font-bold text-sm shadow-sm">
                        <i class="fas fa-question-circle text-white"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-600 block truncate">{{ $qz->course->subject->subject_name ?? 'Kuis' }}</span>
                        <h4 class="font-bold text-slate-900 text-sm leading-snug line-clamp-1">{{ $qz->title }}</h4>
                        <p class="text-xs text-slate-500 font-medium mt-0.5 flex items-center gap-1">
                            <i class="fas fa-list-ol text-slate-400"></i> {{ $qz->questions_count ?? 0 }} Soal Evaluasi
                        </p>
                    </div>
                </div>
                <div class="ml-3 shrink-0">
                    <span class="px-3 py-1 text-[10px] font-extrabold rounded-xl uppercase tracking-wider bg-purple-100 text-purple-800 border border-purple-200">
                        Kuis Aktif
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- GAMIFICATION LEADERBOARD (CERIA & MERIAH) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @if(isset($leaderboard) && $leaderboard->count() > 0)
    <div class="bg-gradient-to-r from-violet-600 via-indigo-600 to-purple-700 rounded-3xl p-6 shadow-xl shadow-indigo-200/50 text-white relative overflow-hidden">
        {{-- Background decorative glows --}}
        <div class="absolute -right-8 -top-8 w-44 h-44 bg-amber-400/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute left-1/3 -bottom-8 w-56 h-56 bg-pink-500/20 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex items-center justify-between mb-5">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-amber-300 to-yellow-500 text-slate-950 flex items-center justify-center font-black shadow-lg shrink-0">
                    <i class="fas fa-trophy text-slate-900 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-white tracking-wide">Papan Peringkat Pembelajar Teraktif</h3>
                    <p class="text-xs text-amber-200 font-medium">Siswa dengan perolehan Poin EXP terbanyak minggu ini</p>
                </div>
            </div>
            <span class="px-3.5 py-1.5 bg-amber-400 text-slate-950 text-xs font-black rounded-xl border border-amber-300 shadow-md uppercase tracking-wider">
                Top 5 Siswa 🏆
            </span>
        </div>

        <div class="relative z-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            @foreach($leaderboard as $lb)
            @php
                $rankBadge = match($loop->iteration) {
                    1 => ['bg' => 'bg-gradient-to-br from-yellow-300 to-amber-500 text-slate-950', 'icon' => '🥇'],
                    2 => ['bg' => 'bg-gradient-to-br from-slate-100 to-slate-300 text-slate-900', 'icon' => '🥈'],
                    3 => ['bg' => 'bg-gradient-to-br from-amber-600 to-orange-700 text-white', 'icon' => '🥉'],
                    default => ['bg' => 'bg-white/20 text-white', 'icon' => '#' . $loop->iteration],
                };
            @endphp
            <div class="bg-white/15 backdrop-blur-md border border-white/20 p-3.5 rounded-2xl flex items-center gap-3 hover:bg-white/25 transition-all shadow-sm">
                <div class="w-10 h-10 rounded-xl {{ $rankBadge['bg'] }} flex items-center justify-center font-black text-xs shadow-md flex-shrink-0">
                    {{ $rankBadge['icon'] }}
                </div>
                <div class="min-w-0 flex-1">
                    <h4 class="font-bold text-white text-xs truncate drop-shadow-xs">{{ $lb->user->name ?? 'Siswa' }}</h4>
                    <p class="text-[11px] text-amber-300 font-extrabold flex items-center gap-1 mt-0.5">
                        <i class="fas fa-star text-[9px] text-amber-300"></i> {{ number_format($lb->total_points) }} EXP
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- COURSE GRID (WARNA-WARNI & VIBRANT) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @if($courses->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($courses as $course)
        @php
            $colorConfig = \App\Models\LmsCourse::getColorClasses($course->color);
            $scientist = $course->getScientistConfig();
            $consolidatedSchedules = $course->getConsolidatedSchedule();
            $progress = $courseProgress[$course->id] ?? 0;
            $design = $course->design ?? null;
            $gradientTheme = $design['gradient'] ?? $colorConfig['gradient'] ?? 'from-indigo-600 via-purple-600 to-pink-600';
        @endphp
        <div class="course-card group">
            <div class="bg-white rounded-3xl border border-slate-100 overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 h-full flex flex-col">
                {{-- Card Header (Vibrant Dynamic Gradient) --}}
                <div class="relative p-5 overflow-hidden bg-gradient-to-br {{ $gradientTheme }} text-white">
                    {{-- Decorative pattern --}}
                    <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-white/10 rounded-full blur-xl pointer-events-none group-hover:scale-125 transition-transform"></div>

                    @if($course->meeting_active)
                    <a href="{{ route('siswa.lms.meeting.join', $course->id) }}" id="live-badge-{{ $course->id }}" class="absolute top-4 right-4 z-20 flex items-center gap-1.5 bg-rose-500 text-white px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider shadow-md shadow-rose-900/30">
                        <span class="w-2 h-2 bg-white rounded-full inline-block animate-ping"></span>
                        LIVE
                    </a>
                    @else
                    <a href="#" id="live-badge-{{ $course->id }}" class="absolute top-4 right-4 z-20 hidden items-center gap-1.5 bg-rose-500 text-white px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider shadow-md shadow-rose-900/30">
                        <span class="w-2 h-2 bg-white rounded-full inline-block animate-ping"></span>
                        LIVE
                    </a>
                    @endif

                    <div class="relative z-10">
                        <div class="flex items-start gap-3.5 mb-3">
                            <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 flex items-center justify-center flex-shrink-0 shadow-md group-hover:scale-110 group-hover:rotate-2 transition-transform">
                                @if($scientist)
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $scientist['icon'] !!}</svg>
                                @elseif(isset($design['icon']))
                                <i class="{{ $design['icon'] }} text-white text-xl"></i>
                                @else
                                <i class="fas fa-graduation-cap text-white text-xl"></i>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-extrabold text-white text-base leading-snug line-clamp-2 drop-shadow-xs">{{ $course->course_name ?? $course->name }}</h3>
                                <div class="flex items-center gap-2 mt-1.5">
                                    <span class="bg-white/20 backdrop-blur-md text-white px-2.5 py-0.5 rounded-lg text-[9px] font-extrabold uppercase tracking-widest border border-white/25">
                                        {{ $course->getShortCode() }}
                                    </span>
                                    @if(isset($design['category']))
                                    <span class="text-[10px] text-white/80 font-semibold truncate">{{ $design['category'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Teacher --}}
                        <div class="flex items-center gap-2 text-white/90 text-xs font-semibold">
                            <i class="fas fa-chalkboard-teacher text-xs text-white/80"></i>
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
                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-semibold bg-slate-50 text-slate-700 border border-slate-200">
                            <i class="far fa-clock mr-1 text-slate-400 text-[10px]"></i> {{ $schLabel }}
                        </span>
                        @endforeach
                    </div>
                    @endif

                    {{-- Course Description --}}
                    <p class="text-slate-600 font-medium text-xs mb-4 line-clamp-2 leading-relaxed">
                        {{ $course->description ?: 'Belajar ' . ($course->subject->subject_name ?? '') . ' secara interaktif dan seru.' }}
                    </p>

                    {{-- Mini Stats --}}
                    <div class="flex items-center justify-between text-xs text-slate-700 font-bold mb-4 bg-slate-50 p-3 rounded-2xl border border-slate-100">
                        <span class="flex items-center gap-1.5 text-indigo-600"><i class="fas fa-folder text-indigo-500"></i> {{ $course->modules_count ?? 0 }} Modul</span>
                        <span class="text-slate-300">·</span>
                        <span class="flex items-center gap-1.5 text-purple-600"><i class="fas fa-file-alt text-purple-500"></i> {{ $course->materials_count ?? 0 }} Materi</span>
                        <span class="text-slate-300">·</span>
                        <span class="flex items-center gap-1.5 text-emerald-600"><i class="fas fa-tasks text-emerald-500"></i> {{ $course->assignments_count ?? 0 }} Tugas</span>
                    </div>

                    {{-- Progress --}}
                    <div class="mb-4">
                        <div class="flex justify-between text-xs mb-1.5 font-bold uppercase tracking-wider text-slate-600">
                            <span class="text-slate-800">
                                {{ $progress >= 80 ? '🔥 Hampir Selesai!' : ($progress >= 40 ? '📖 Sedang Belajar' : ($progress > 0 ? '🚀 Baru Mulai' : '📚 Belum Dimulai')) }}
                            </span>
                            <span class="text-indigo-600 font-extrabold">{{ number_format($progress) }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-1000 ease-out bg-gradient-to-r from-emerald-400 to-teal-500" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>

                    {{-- CTA --}}
                    <div class="mt-auto">
                        <a href="{{ route('siswa.lms.show', $course->id) }}"
                           class="flex items-center justify-center gap-2 w-full bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-700 hover:via-purple-700 hover:to-pink-700 text-white px-4 py-3.5 rounded-2xl transition-all shadow-md shadow-indigo-100 hover:shadow-lg hover:shadow-indigo-200 text-xs font-bold uppercase tracking-wider transform hover:scale-[1.02] active:scale-[0.98]">
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
    <div class="bg-gradient-to-br from-amber-50/50 via-white to-indigo-50/50 rounded-3xl shadow-sm border border-indigo-100 p-16 text-center">
        <div class="w-20 h-20 bg-indigo-50 text-indigo-600 rounded-3xl flex items-center justify-center mx-auto mb-4 shadow-inner">
            <i class="fas fa-book-open text-3xl"></i>
        </div>
        <h3 class="text-lg font-bold text-slate-900 mb-1">Belum Ada Course Terdaftar</h3>
        <p class="text-slate-500 font-medium text-xs max-w-sm mx-auto">Anda belum terdaftar di kelas manapun. Hubungi guru Anda untuk didaftarkan ke ruang belajar.</p>
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
                    // Hide all live badges first
                    document.querySelectorAll('[id^="live-badge-"]').forEach(badge => {
                        badge.classList.add('hidden');
                        badge.classList.remove('flex');
                        badge.href = '#';
                    });
                    
                    // Show for active courses
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

        // Poll every 30 seconds
        setInterval(checkLiveMeetings, 30000);
        
        // Run immediately
        checkLiveMeetings();
    });
</script>
@endpush

