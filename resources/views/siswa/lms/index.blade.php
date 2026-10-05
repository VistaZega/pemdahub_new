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
        background-image: radial-gradient(circle at 25% 60%, rgba(255,255,255,0.08) 0%, transparent 50%),
                          radial-gradient(circle at 75% 20%, rgba(255,255,255,0.06) 0%, transparent 40%);
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    {{-- ═══════════════════════════════════════════════ --}}
    {{-- HERO BANNER (100% SOLID UI UX PRO MAX) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="relative bg-white rounded-3xl p-6 md:p-8 overflow-hidden shadow-xl border-2 border-black">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="flex items-center mb-3" style="display: flex; align-items: center; gap: 1.25rem;">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-md border-2 border-black shrink-0" style="background-color: #1e3a8a !important; color: #ffffff !important; margin-right: 1.25rem; flex-shrink: 0;">
                        <i class="fas fa-graduation-cap text-2xl text-white"></i>
                    </div>
                    <div style="min-width: 0; flex: 1;">
                        <p class="text-black text-xs font-black uppercase tracking-[0.2em]">Learning Management System Siswa</p>
                        <h2 class="text-2xl md:text-3xl font-black text-black tracking-tight mt-0.5">Halo, {{ explode(' ', $student->user->name ?? 'Siswa')[0] }}! 👋</h2>
                    </div>
                </div>
                <p class="text-black font-bold text-sm max-w-md leading-relaxed">Akses materi modul ajar, selesaikan tugas sekolah, dan jawab quiz evaluasi interaktif.</p>
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
                <div class="bg-white border-2 border-black rounded-2xl px-5 py-3 text-center min-w-[90px] shadow-md">
                    <div class="text-2xl font-black leading-none text-black">{{ $totalCourses }}</div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-black mt-1">Course</div>
                </div>
                <div class="bg-white border-2 border-black rounded-2xl px-5 py-3 text-center min-w-[90px] shadow-md" style="background-color: #e0f2fe !important;">
                    <div class="text-2xl font-black leading-none text-black">{{ $avgProgress }}%</div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-black mt-1">Progress</div>
                </div>
                
                @if($reputation)
                <div class="border-2 border-black rounded-2xl px-5 py-3 text-center min-w-[110px] shadow-md" style="background-color: #fef08a !important; color: #000000 !important;">
                    <div class="text-2xl font-black leading-none text-black flex items-center justify-center gap-1">
                        <i class="fas fa-star text-sm text-black"></i> {{ number_format($reputation->total_points) }}
                    </div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-black mt-1">{{ $reputation->level_name }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>


    {{-- ═══════════════════════════════════════════════ --}}
    {{-- UPCOMING DEADLINES WIDGET --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @if((isset($upcomingAssignments) && $upcomingAssignments->count() > 0) || (isset($upcomingQuizzes) && $upcomingQuizzes->count() > 0))
    <div class="bg-white rounded-3xl p-6 shadow-md border-2 border-black">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center" style="display: flex; align-items: center; gap: 1.25rem;">
                <div class="w-12 h-12 rounded-2xl bg-black text-amber-400 flex items-center justify-center font-black shadow-md border-2 border-black shrink-0" style="margin-right: 1.25rem; flex-shrink: 0;">
                    <i class="fas fa-bell text-xl text-amber-400"></i>
                </div>
                <div style="min-width: 0; flex: 1;">
                    <h3 class="text-base font-black text-black leading-tight">Tenggat Waktu Minggu Ini</h3>
                    <p class="text-xs font-bold text-black mt-0.5">Tugas & Kuis yang harus Anda selesaikan segera</p>
                </div>
            </div>
            <span class="px-3 py-1 bg-amber-300 text-black text-xs font-black rounded-xl border-2 border-black">
                {{ ($upcomingAssignments->count() ?? 0) + ($upcomingQuizzes->count() ?? 0) }} Item Jatuh Tempo
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($upcomingAssignments as $asgn)
            @php
                $deadlineDate = \Carbon\Carbon::parse($asgn->deadline);
                $now = \Carbon\Carbon::now();
                $diffHours = (int) $now->diffInHours($deadlineDate, false);
                $diffDays = (int) ceil($now->floatDiffInDays($deadlineDate, false));
                $isOverdue = $now->greaterThan($deadlineDate);
                $isUrgent = !$isOverdue && ($diffHours <= 24);
                
                if ($isOverdue) {
                    $badgeText = 'Terlewat';
                    $badgeClass = 'bg-rose-600 text-white';
                } elseif ($diffHours <= 0 || $diffHours < 1) {
                    $diffMinutes = (int) $now->diffInMinutes($deadlineDate, false);
                    $badgeText = $diffMinutes > 0 ? $diffMinutes . ' Mnt lagi' : 'Hari Ini';
                    $badgeClass = 'bg-rose-600 text-white';
                } elseif ($diffHours < 24) {
                    $badgeText = $diffHours . ' Jam lagi';
                    $badgeClass = 'bg-rose-600 text-white';
                } else {
                    $badgeText = $diffDays . ' Hari lagi';
                    $badgeClass = 'bg-amber-300 text-black';
                }
            @endphp
            <div class="flex items-start justify-between p-4 rounded-2xl border-2 border-black shadow-sm {{ $isUrgent ? 'bg-rose-100' : 'bg-slate-50' }}">
                <div class="flex items-start" style="display: flex; align-items: flex-start; gap: 1rem; flex: 1; min-width: 0;">
                    <div class="w-10 h-10 rounded-xl bg-black text-white flex items-center justify-center flex-shrink-0 font-black text-sm border-2 border-black" style="margin-right: 1rem; flex-shrink: 0;">
                        <i class="fas fa-tasks text-white"></i>
                    </div>
                    <div style="min-width: 0; flex: 1;">
                        <span class="text-[10px] font-black uppercase tracking-wider text-black">{{ $asgn->course->subject->subject_name ?? 'Tugas' }}</span>
                        <h4 class="font-black text-black text-sm leading-snug line-clamp-1">{{ $asgn->title }}</h4>
                        <p class="text-xs font-bold text-black mt-0.5">
                            <i class="far fa-clock mr-1 text-black"></i> Deadline: {{ $deadlineDate->translatedFormat('d M Y, H:i') }}
                        </p>
                    </div>
                </div>
                <div style="margin-left: 0.75rem; flex-shrink: 0;">
                    <span class="px-3 py-1 text-[10px] font-black rounded-xl uppercase tracking-wider border border-black {{ $badgeClass }}">
                        {{ $badgeText }}
                    </span>
                </div>
            </div>
            @endforeach

            @foreach($upcomingQuizzes as $qz)
            <div class="flex items-start justify-between p-4 rounded-2xl border-2 border-black shadow-sm" style="background-color: #f3e8ff !important;">
                <div class="flex items-start" style="display: flex; align-items: flex-start; gap: 1rem; flex: 1; min-width: 0;">
                    <div class="w-10 h-10 rounded-xl bg-black text-white flex items-center justify-center flex-shrink-0 font-black text-sm border-2 border-black" style="margin-right: 1rem; flex-shrink: 0;">
                        <i class="fas fa-question-circle text-white"></i>
                    </div>
                    <div style="min-width: 0; flex: 1;">
                        <span class="text-[10px] font-black uppercase tracking-wider text-black">{{ $qz->course->subject->subject_name ?? 'Kuis' }}</span>
                        <h4 class="font-black text-black text-sm leading-snug line-clamp-1">{{ $qz->title }}</h4>
                        <p class="text-xs font-bold text-black mt-0.5">
                            <i class="fas fa-list-ol mr-1 text-black"></i> {{ $qz->questions_count ?? 0 }} Soal Evaluasi
                        </p>
                    </div>
                </div>
                <div style="margin-left: 0.75rem; flex-shrink: 0;">
                    <span class="px-3 py-1 text-[10px] font-black rounded-xl uppercase tracking-wider bg-black text-white border border-black">
                        Kuis Aktif
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- GAMIFICATION LEADERBOARD WIDGET (CERAH & BERKONTRAS TINGGI) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @if(isset($leaderboard) && $leaderboard->count() > 0)
    <div class="bg-gradient-to-r from-amber-50/90 via-orange-50/80 to-amber-100/70 rounded-3xl p-6 shadow-xl border-2 border-black">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
            <div class="flex items-center" style="display: flex; align-items: center; gap: 1.25rem;">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black shadow-md border-2 border-black shrink-0" style="background-color: #fbbf24 !important; color: #000000 !important; margin-right: 1.25rem; flex-shrink: 0;">
                    <i class="fas fa-trophy text-black text-2xl animate-bounce" style="animation-duration: 2s;"></i>
                </div>
                <div style="min-width: 0; flex: 1;">
                    <h3 class="text-lg font-black text-black tracking-tight">Papan Peringkat Pembelajar Teraktif</h3>
                    <p class="text-xs text-amber-900 font-extrabold mt-0.5">Siswa dengan perolehan Poin EXP terbanyak minggu ini</p>
                </div>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-black text-amber-300 text-xs font-black rounded-xl border-2 border-black uppercase tracking-wider shadow-sm">
                    <span>👑 Top 5 Siswa</span>
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            @foreach($leaderboard as $lb)
            @php
                $isCurrentUser = ($lb->user_id === auth()->id()) || (isset($student) && $lb->user_id === $student->user_id);
                
                $cardBg = match($loop->iteration) {
                    1 => 'background: linear-gradient(135deg, #fef08a 0%, #fde047 50%, #f59e0b 100%) !important; color: #000000 !important;',
                    2 => 'background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 50%, #cbd5e1 100%) !important; color: #000000 !important;',
                    3 => 'background: linear-gradient(135deg, #ffedd5 0%, #fed7aa 50%, #fdba74 100%) !important; color: #000000 !important;',
                    default => 'background: #ffffff !important; color: #000000 !important;',
                };
                
                $badgeBg = match($loop->iteration) {
                    1 => 'background-color: #000000 !important; color: #fde047 !important;',
                    2 => 'background-color: #000000 !important; color: #ffffff !important;',
                    3 => 'background-color: #000000 !important; color: #fed7aa !important;',
                    default => 'background-color: #f1f5f9 !important; color: #000000 !important;',
                };
                
                $rankIcon = match($loop->iteration) {
                    1 => '🥇 #1',
                    2 => '🥈 #2',
                    3 => '🥉 #3',
                    default => '#' . $loop->iteration,
                };
            @endphp
            <div class="border-2 border-black p-3 rounded-2xl flex items-center shadow-md hover:scale-105 transition-all duration-200 relative overflow-hidden" style="{{ $cardBg }} display: flex; align-items: center; gap: 0.85rem;">
                @if($isCurrentUser)
                <span class="absolute top-1 right-2 text-[8px] font-black uppercase bg-black text-amber-300 px-1.5 py-0.5 rounded-md border border-black z-10">Kamu</span>
                @endif
                <div class="relative flex-shrink-0" style="margin-right: 0.65rem;">
                    <div class="w-12 h-12 rounded-2xl overflow-hidden border-2 border-black shadow-xs bg-white flex-shrink-0">
                        <img src="{{ $lb->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($lb->user->name ?? 'Siswa') . '&background=0f172a&color=ffffff&bold=true' }}"
                             alt="{{ $lb->user->name ?? 'Siswa' }}"
                             class="w-full h-full object-cover object-center"
                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($lb->user->name ?? 'Siswa') }}&background=0f172a&color=ffffff&bold=true'">
                    </div>
                    <span class="absolute -bottom-1 -right-1 text-[9px] font-black px-1.5 py-0.2 rounded-md border border-black shadow-xs" style="{{ $badgeBg }} line-height: 1.2;">
                        {{ $rankIcon }}
                    </span>
                </div>
                <div class="min-w-0 flex-1" style="min-width: 0; flex: 1;">
                    <h4 class="font-black text-xs truncate leading-snug" style="color: #000000 !important;" title="{{ $lb->user->name ?? 'Siswa' }}">
                        {{ $lb->user->name ?? 'Siswa' }}
                    </h4>
                    <div class="mt-1">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-black border border-black shadow-2xs" style="background-color: #000000 !important; color: #fde047 !important;">
                            <i class="fas fa-bolt text-[9px] text-amber-400"></i> {{ number_format($lb->total_points) }} EXP
                        </span>
                    </div>
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
            $themeColor = $course->getActiveThemeHexColor();
        @endphp
        <div class="course-card group">
            <div class="bg-white rounded-3xl border-2 border-black overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 h-full flex flex-col">
                {{-- Card Header (Dynamic Header Color based on Module, default Abu Muda) --}}
                <div class="relative p-5 overflow-hidden border-b-2 border-black text-white" style="background-color: {{ $themeColor }} !important;">
                    @if($course->meeting_active)
                    <a href="{{ route('siswa.lms.meeting.join', $course->id) }}" id="live-badge-{{ $course->id }}" class="absolute top-4 right-4 z-20 flex items-center gap-1.5 bg-rose-600 text-white px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider border border-black shadow-md">
                        <span class="w-2 h-2 bg-white rounded-full inline-block animate-ping"></span>
                        LIVE
                    </a>
                    @else
                    <a href="#" id="live-badge-{{ $course->id }}" class="absolute top-4 right-4 z-20 hidden items-center gap-1.5 bg-rose-600 text-white px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider border border-black shadow-md">
                        <span class="w-2 h-2 bg-white rounded-full inline-block animate-ping"></span>
                        LIVE
                    </a>
                    @endif

                    <div class="relative z-10">
                        <div class="flex items-start mb-3" style="display: flex; align-items: flex-start; gap: 1rem;">
                            <div class="w-12 h-12 rounded-2xl overflow-hidden border-2 border-black flex-shrink-0 shadow-md bg-white" style="margin-right: 1rem; flex-shrink: 0;">
                                <img src="{{ $course->getTeacherPhotoUrl() }}" alt="{{ $course->teacher->user->name ?? 'Guru Pengajar' }}" class="w-full h-full object-cover object-center" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($course->teacher->user->name ?? 'Guru') }}&background=0f172a&color=ffffff&bold=true'">
                            </div>
                            <div class="min-w-0 flex-1" style="min-width: 0; flex: 1;">
                                <h3 class="font-black text-white text-base leading-snug line-clamp-2">{{ $course->course_name ?? $course->name }}</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="bg-slate-900 text-amber-300 px-2.5 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest border border-black">{{ $course->getShortCode() }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Teacher --}}
                        <div class="flex items-center text-white text-xs font-black" style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem;">
                            <i class="fas fa-chalkboard-teacher text-xs" style="margin-right: 0.35rem;"></i>
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
                        <span class="inline-flex items-center px-3 py-1 rounded-xl text-[10px] font-black bg-slate-100 text-black border border-black">
                            <i class="far fa-clock mr-1 text-black text-[10px]"></i> {{ $schLabel }}
                        </span>
                        @endforeach
                    </div>
                    @endif

                    {{-- Course Description --}}
                    <p class="text-black font-bold text-xs mb-4 line-clamp-2 leading-relaxed">{{ $course->description ?: 'Belajar ' . ($course->subject->subject_name ?? '') . ' secara interaktif.' }}</p>

                    {{-- Mini Stats --}}
                    <div class="flex items-center gap-3 text-xs text-black font-black mb-4 bg-slate-100 p-3 rounded-2xl border-2 border-black">
                        <span class="flex items-center gap-1"><i class="fas fa-folder text-black"></i> {{ $course->modules_count ?? 0 }} Modul</span>
                        <span>·</span>
                        <span class="flex items-center gap-1"><i class="fas fa-file-alt text-black"></i> {{ $course->materials_count ?? 0 }} Materi</span>
                        <span>·</span>
                        <span class="flex items-center gap-1"><i class="fas fa-tasks text-black"></i> {{ $course->assignments_count ?? 0 }} Tugas</span>
                    </div>

                    @php
                        $cDetail = $courseProgressDetails[$course->id] ?? null;
                    @endphp
                    {{-- Progress --}}
                    <div class="mb-4">
                        <div class="flex justify-between text-xs mb-1.5 font-black uppercase tracking-wider text-black">
                            <span>
                                {{ $progress >= 80 ? '🔥 Hampir Selesai!' : ($progress >= 40 ? '📖 Sedang Belajar' : ($progress > 0 ? '🚀 Baru Mulai' : '📚 Belum Dimulai')) }}
                            </span>
                            <span class="text-black font-black">{{ number_format($progress) }}%</span>
                        </div>
                        <div class="w-full bg-slate-200 border-2 border-black rounded-full h-3 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-1000 ease-out border-r border-black" style="background-color: #059669 !important; width: {{ $progress }}%"></div>
                        </div>
                        @if($cDetail)
                        <div class="flex items-center justify-between text-[10px] text-slate-700 font-bold mt-2 px-1">
                            <span title="Materi Selesai Dibaca"><i class="fas fa-file-alt text-blue-600 mr-1"></i> Materi {{ $cDetail['materials']['completed'] }}/{{ $cDetail['materials']['total'] }}</span>
                            <span title="Tugas Dikumpulkan"><i class="fas fa-tasks text-amber-600 mr-1"></i> Tugas {{ $cDetail['assignments']['completed'] }}/{{ $cDetail['assignments']['total'] }}</span>
                            <span title="Kuis Diselesaikan"><i class="fas fa-question-circle text-purple-600 mr-1"></i> Kuis {{ $cDetail['quizzes']['completed'] }}/{{ $cDetail['quizzes']['total'] }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- CTA --}}
                    <div class="mt-auto">
                        <a href="{{ route('siswa.lms.show', $course->id) }}"
                           class="flex items-center justify-center gap-2 w-full text-white px-4 py-3.5 rounded-2xl transition-all shadow-md text-xs font-black uppercase tracking-wider border-2 border-black hover:opacity-90"
                           style="background-color: {{ $themeColor }} !important;">
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
    <div class="bg-white rounded-3xl shadow-md border-2 border-black p-16 text-center">
        <div class="w-20 h-20 bg-amber-100 border-2 border-black rounded-3xl flex items-center justify-center mx-auto mb-4"><i class="fas fa-book-open text-3xl text-black"></i></div>
        <h3 class="text-lg font-black text-black mb-1">Belum Ada Course Terdaftar</h3>
        <p class="text-black font-bold text-xs max-w-sm mx-auto">Anda belum terdaftar di course manapun. Hubungi guru Anda untuk didaftarkan ke kelas.</p>
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

