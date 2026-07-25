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
    {{-- HERO BANNER --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="relative bg-gradient-to-br from-blue-50 via-indigo-50 to-violet-50 rounded-2xl p-8 md:p-10 overflow-hidden shadow-lg border border-blue-200">
        <div class="absolute -right-12 -top-12 w-64 h-64 bg-blue-200/30 rounded-full blur-2xl"></div>
        <div class="absolute -left-8 -bottom-8 w-48 h-48 bg-indigo-200/20 rounded-full blur-xl"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center shadow-md">
                        <i class="fas fa-graduation-cap text-xl text-white"></i>
                    </div>
                    <div>
                        <p class="text-blue-700 text-xs font-bold uppercase tracking-[0.2em]">Learning Management System</p>
                        <h2 class="text-2xl md:text-2xl font-bold text-gray-900 tracking-tight text-gray-900">Halo, {{ explode(' ', $student->user->name ?? 'Siswa')[0] }}! 👋</h2>
                    </div>
                </div>
                <p class="text-gray-600 text-sm max-w-md leading-relaxed">Akses materi pelajaran, kerjakan tugas, dan selesaikan quiz dari kelas Anda.</p>
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
                        $repColor = $reputation->level_color ?? 'slate';
                    }
                @endphp
                <div class="bg-white border border-blue-200 rounded-xl px-5 py-3 text-center min-w-[80px] shadow-sm">
                    <div class="text-2xl font-bold leading-none text-blue-700">{{ $totalCourses }}</div>
                    <div class="text-[9px] font-bold uppercase tracking-widest text-gray-500 mt-1.5">Course</div>
                </div>
                <div class="bg-white border border-indigo-200 rounded-xl px-5 py-3 text-center min-w-[80px] shadow-sm">
                    <div class="text-2xl font-bold leading-none text-indigo-700">{{ $avgProgress }}%</div>
                    <div class="text-[9px] font-bold uppercase tracking-widest text-gray-500 mt-1.5">Progress</div>
                </div>
                
                @if($reputation)
                <div class="bg-gradient-to-br from-{{ $repColor }}-50 to-white border border-{{ $repColor }}-200 rounded-xl px-5 py-3 text-center min-w-[100px] shadow-sm relative overflow-hidden group">
                    <div class="text-2xl font-black leading-none text-{{ $repColor }}-600 flex items-center justify-center gap-1">
                        <i class="fas fa-star text-sm text-amber-400"></i> {{ number_format($reputation->total_points) }}
                    </div>
                    <div class="text-[9px] font-bold uppercase tracking-widest text-{{ $repColor }}-500 mt-1.5">{{ $reputation->level_name }}</div>
                    
                    <!-- Progress Bar -->
                    <div class="w-full bg-{{ $repColor }}-100 h-1.5 rounded-full overflow-hidden mt-2 border border-{{ $repColor }}-200">
                        <div class="bg-{{ $repColor }}-500 h-full rounded-full" style="width: {{ $reputation->progress_percentage }}%"></div>
                    </div>
                    
                    <!-- Hover Info -->
                    <div class="absolute inset-0 bg-white/95 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center p-2 text-center">
                        <span class="text-[10px] font-bold text-gray-700 leading-tight">EXP/Poin</span>
                        <span class="text-[9px] text-gray-500 leading-tight">Dapatkan Poin dengan membaca materi & quiz!</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>


    {{-- ═══════════════════════════════════════════════ --}}
    {{-- UPCOMING DEADLINES WIDGET (TENGGAT WAKTU) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @if((isset($upcomingAssignments) && $upcomingAssignments->count() > 0) || (isset($upcomingQuizzes) && $upcomingQuizzes->count() > 0))
    <div class="bg-white rounded-2xl p-6 shadow-md border-2 border-amber-200">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold shadow-sm">
                    <i class="fas fa-bell text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Tenggat Waktu Minggu Ini</h3>
                    <p class="text-xs font-semibold text-gray-500">Tugas & Kuis yang harus Anda selesaikan segera</p>
                </div>
            </div>
            <span class="px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full border border-amber-300">
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
            <div class="flex items-start justify-between p-4 rounded-xl {{ $isUrgent ? 'bg-red-50 border border-red-200' : 'bg-gray-50 border border-gray-200' }} hover:shadow-md transition">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-lg {{ $isUrgent ? 'bg-red-500' : 'bg-blue-600' }} text-white flex items-center justify-center flex-shrink-0 font-bold text-sm">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-600">{{ $asgn->course->subject->subject_name ?? 'Tugas' }}</span>
                        <h4 class="font-bold text-gray-900 text-sm leading-snug line-clamp-1">{{ $asgn->title }}</h4>
                        <p class="text-xs font-semibold text-gray-600 mt-0.5">
                            <i class="far fa-clock mr-1"></i> Deadline: {{ \Carbon\Carbon::parse($asgn->deadline)->translatedFormat('d M Y, H:i') }}
                        </p>
                    </div>
                </div>
                <div>
                    <span class="px-2.5 py-1 text-[10px] font-extrabold rounded-lg uppercase tracking-wider {{ $isUrgent ? 'bg-red-600 text-white' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                        {{ $diffHours <= 0 ? 'Hari Ini' : ($diffHours < 24 ? $diffHours.' Jam lagi' : $diffDays.' Hari lagi') }}
                    </span>
                </div>
            </div>
            @endforeach

            @foreach($upcomingQuizzes as $qz)
            <div class="flex items-start justify-between p-4 rounded-xl bg-purple-50 border border-purple-200 hover:shadow-md transition">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-lg bg-purple-600 text-white flex items-center justify-center flex-shrink-0 font-bold text-sm">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700">{{ $qz->course->subject->subject_name ?? 'Kuis' }}</span>
                        <h4 class="font-bold text-gray-900 text-sm leading-snug line-clamp-1">{{ $qz->title }}</h4>
                        <p class="text-xs font-semibold text-gray-600 mt-0.5">
                            <i class="fas fa-list-ol mr-1"></i> {{ $qz->questions_count ?? 0 }} Soal
                        </p>
                    </div>
                </div>
                <div>
                    <span class="px-2.5 py-1 text-[10px] font-extrabold rounded-lg uppercase tracking-wider bg-purple-600 text-white">
                        Kuis Aktif
                    </span>
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
            $colorConfig = \App\Models\LmsCourse::getColorClasses($course->color);
            $scientist = $course->getScientistConfig();
            $consolidatedSchedules = $course->getConsolidatedSchedule();
            $progress = $courseProgress[$course->id] ?? 0;
        @endphp
        <div class="course-card group">
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 h-full flex flex-col">
                {{-- Card Header --}}
                <div class="relative bg-gradient-to-br {{ $colorConfig['gradient'] }} p-5 overflow-hidden">
                    @if($course->meeting_active)
                    <a href="{{ route('siswa.lms.meeting.join', $course->id) }}" id="live-badge-{{ $course->id }}" class="absolute top-4 right-4 z-20 flex items-center gap-1.5 bg-rose-500 text-white px-2.5 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider animate-pulse shadow-md hover:scale-105 transition-all">
                        <span class="w-2 h-2 bg-white rounded-full inline-block animate-ping"></span>
                        LIVE
                    </a>
                    @else
                    <a href="#" id="live-badge-{{ $course->id }}" class="absolute top-4 right-4 z-20 hidden items-center gap-1.5 bg-rose-500 text-white px-2.5 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider animate-pulse shadow-md hover:scale-105 transition-all">
                        <span class="w-2 h-2 bg-white rounded-full inline-block animate-ping"></span>
                        LIVE
                    </a>
                    @endif
                    <div class="absolute inset-0 opacity-[0.07]" style="background-image: radial-gradient(circle at 70% 30%, white 1px, transparent 1px); background-size: 20px 20px;"></div>
                    <div class="absolute -right-6 -bottom-6 opacity-10 group-hover:opacity-20 transition-opacity">
                        @if($scientist)
                        <svg class="w-28 h-28 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $scientist['icon'] !!}</svg>
                        @else
                        <i class="fas fa-graduation-cap text-7xl text-white"></i>
                        @endif
                    </div>

                    <div class="relative z-10">
                        <div class="flex items-start gap-3 mb-3">
                            <div class="w-11 h-11 bg-white/20 rounded-xl flex items-center justify-center border border-gray-100 flex-shrink-0 shadow-sm group-hover:scale-110 group-hover:rotate-3 transition-transform">
                                @if($scientist)
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $scientist['icon'] !!}</svg>
                                @else
                                <i class="fas fa-graduation-cap text-white"></i>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-bold text-white text-base leading-snug line-clamp-2">{{ $course->course_name ?? $course->name }}</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="bg-white/20 px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-widest">{{ $course->getShortCode() }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Teacher --}}
                        <div class="flex items-center gap-2 text-white/80 text-xs">
                            <i class="fas fa-chalkboard-teacher text-[10px]"></i>
                            <span class="font-medium truncate">{{ $course->teacher->user->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="p-5 flex-1 flex flex-col">
                    {{-- Schedule Badges --}}
                    @if($consolidatedSchedules->isNotEmpty())
                    <div class="mb-3 flex flex-wrap gap-1.5">
                        @foreach($consolidatedSchedules as $schLabel)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-gray-50 text-gray-600 border border-gray-100">
                            <i class="far fa-clock mr-1 text-gray-400 text-[9px]"></i> {{ $schLabel }}
                        </span>
                        @endforeach
                    </div>
                    @endif

                    {{-- Course Description --}}
                    <p class="text-gray-500 text-sm mb-4 line-clamp-2 leading-relaxed">{{ $course->description ?: 'Belajar ' . ($course->subject->subject_name ?? '') . ' secara interaktif.' }}</p>

                    {{-- Mini Stats --}}
                    <div class="flex items-center gap-3 text-[11px] text-gray-400 font-medium mb-4 bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                        <span class="flex items-center gap-1"><i class="fas fa-folder text-amber-400"></i> {{ $course->modules_count ?? 0 }} Modul</span>
                        <span class="text-gray-200">·</span>
                        <span class="flex items-center gap-1"><i class="fas fa-file-alt text-blue-400"></i> {{ $course->materials_count ?? 0 }} Materi</span>
                        <span class="text-gray-200">·</span>
                        <span class="flex items-center gap-1"><i class="fas fa-tasks text-emerald-400"></i> {{ $course->assignments_count ?? 0 }} Tugas</span>
                    </div>

                    {{-- Progress --}}
                    <div class="mb-4">
                        <div class="flex justify-between text-[10px] mb-1.5 font-bold uppercase tracking-wider">
                            <span class="{{ $progress >= 80 ? 'text-emerald-600' : ($progress >= 40 ? 'text-blue-600' : 'text-amber-600') }}">
                                {{ $progress >= 80 ? '🔥 Hampir Selesai!' : ($progress >= 40 ? '📖 Sedang Belajar' : ($progress > 0 ? '🚀 Baru Mulai' : '📚 Belum Dimulai')) }}
                            </span>
                            <span class="text-gray-500">{{ number_format($progress) }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full transition-all duration-1000 ease-out {{ $progress >= 80 ? 'bg-gradient-to-r from-emerald-500 to-emerald-400' : ($progress >= 40 ? 'bg-gradient-to-r from-blue-500 to-blue-400' : 'bg-gradient-to-r from-amber-500 to-amber-400') }}"
                                 style="width: {{ $progress }}%"></div>
                        </div>
                    </div>

                    {{-- CTA --}}
                    <div class="mt-auto">
                        <a href="{{ route('siswa.lms.show', $course->id) }}"
                           class="flex items-center justify-center gap-2 w-full {{ $colorConfig['bg'] }} hover:brightness-110 text-white px-4 py-3 rounded-xl transition-all shadow-sm hover:shadow-lg text-sm font-bold group-hover:-translate-y-0.5 active:translate-y-0">
                            <i class="fas fa-door-open"></i> Masuk Kelas
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @else
    {{-- Empty State --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
        <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-inner">
            <i class="fas fa-book-open text-4xl text-blue-400"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">Belum Ada Course</h3>
        <p class="text-gray-500 max-w-sm mx-auto leading-relaxed">Anda belum terdaftar di course manapun. Hubungi guru Anda untuk didaftarkan ke kelas.</p>
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

