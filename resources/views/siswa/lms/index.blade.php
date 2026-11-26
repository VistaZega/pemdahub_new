@extends('layouts.siswa')

@section('title', 'LMS - Ruang Belajar Digital Siswa')

@section('content')
<style>
    .lms-wrap {
        max-width: 1320px;
        margin: 0 auto;
        padding-bottom: 2.5rem;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    .lms-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 55%, #312e81 100%);
        border: 1px solid #1e293b;
        border-radius: 1.35rem;
        padding: 1.5rem 1.75rem;
        color: #ffffff;
        box-shadow: 0 12px 28px -6px rgba(15, 23, 42, 0.28);
        position: relative;
        overflow: hidden;
    }
    .lms-hero::after {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 220px;
        height: 220px;
        border-radius: 9999px;
        background: radial-gradient(circle, rgba(56, 189, 248, 0.22) 0%, rgba(56, 189, 248, 0) 70%);
        pointer-events: none;
    }
    .lms-stat-pill {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 1rem;
        padding: 0.75rem 1.1rem;
        min-width: 96px;
        text-align: center;
    }
    .lms-toolbar {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 1.1rem;
        padding: 0.85rem 1.1rem;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.85rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }
    .lms-course-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1.25rem;
    }
    @media (max-width: 1180px) {
        .lms-course-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 700px) {
        .lms-course-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }
    .lms-course-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 1.25rem;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .lms-course-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.1);
    }
    .lms-filter-btn {
        padding: 0.45rem 0.85rem;
        border-radius: 9999px;
        font-size: 1.08rem;
        font-weight: 700;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .lms-filter-btn.is-active {
        background: #0f172a;
        color: #ffffff;
    }
    .lms-filter-btn:not(.is-active) {
        background: #f1f5f9;
        color: #475569;
    }
    .lms-filter-btn:not(.is-active):hover {
        background: #e2e8f0;
        color: #0f172a;
    }
</style>

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

<div class="lms-wrap" x-data="{ search: '', filter: 'all' }">

    {{-- 1. HERO HEADER (DEEP INDIGO-SLATE + INTEGRATED AI, SIMLAB & CERTIFICATES) --}}
    <div class="lms-hero">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.25rem; position: relative; z-index: 1;">
            <div style="min-width: 240px; flex: 1;">
                <div style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.25rem 0.7rem; border-radius: 9999px; background: rgba(245, 158, 11, 0.18); border: 1px solid rgba(251, 191, 36, 0.4); color: #fde68a; font-size: 0.80rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.5rem;">
                    <i class="fas fa-microchip"></i> Smart Learning Ecosystem &bull; PembdaHUB
                </div>
                <h1 style="font-size: 1.70rem; font-weight: 900; color: #ffffff; margin: 0; line-height: 1.25;">
                    Ruang Belajar Digital, {{ explode(' ', $student->user->name ?? 'Siswa')[0] }}
                </h1>
                <p style="font-size: 0.90rem; color: #cbd5e1; margin-top: 0.35rem; max-width: 560px; line-height: 1.5;">
                    Pelajari modul interaktif, kerjakan tugas &amp; kuis evaluasi, praktik di Simulator Lab, dan berdiskusi bersama Pembda AI Tutor.
                </p>

                <div style="display: flex; flex-wrap: wrap; gap: 0.55rem; margin-top: 0.9rem;">
                    <a href="{{ route('siswa.ai.index', ['mode' => 'lms_assistant']) }}" style="padding: 0.5rem 0.95rem; border-radius: 0.75rem; background: #fbbf24; color: #0f172a; font-size: 1.08rem; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                        <i class="fas fa-robot"></i> Pembda AI Tutor
                    </a>
                    <a href="{{ route('simlab.index') }}" style="padding: 0.5rem 0.95rem; border-radius: 0.75rem; background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(52, 211, 153, 0.4); color: #6ee7b7; font-size: 1.08rem; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                        <i class="fas fa-flask"></i> Simulator Lab
                    </a>
                    <a href="{{ route('siswa.lms.certificates.index') }}" style="padding: 0.5rem 0.95rem; border-radius: 0.75rem; background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.22); color: #ffffff; font-size: 1.08rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                        <i class="fas fa-trophy" style="color: #fbbf24;"></i> Sertifikat Saya
                    </a>
                </div>
            </div>

            {{-- Quick Stats --}}
            <div style="display: flex; flex-wrap: wrap; gap: 0.65rem; flex-shrink: 0;">
                <div class="lms-stat-pill">
                    <div style="font-size: 1.45rem; font-weight: 900; color: #ffffff; line-height: 1.1;">{{ $totalCourses }}</div>
                    <div style="font-size: 0.98rem; font-weight: 700; color: #cbd5e1; text-transform: uppercase; margin-top: 0.2rem;">Kursus</div>
                </div>
                <div class="lms-stat-pill" style="background: rgba(56, 189, 248, 0.16); border-color: rgba(56, 189, 248, 0.35);">
                    <div style="font-size: 1.45rem; font-weight: 900; color: #7dd3fc; line-height: 1.1;">{{ $avgProgress }}%</div>
                    <div style="font-size: 0.98rem; font-weight: 700; color: #bae6fd; text-transform: uppercase; margin-top: 0.2rem;">Rata Progress</div>
                </div>
                @if($reputation)
                <div class="lms-stat-pill" style="background: rgba(251, 191, 36, 0.18); border-color: rgba(251, 191, 36, 0.4);">
                    <div style="font-size: 1.45rem; font-weight: 900; color: #fde047; line-height: 1.1;">{{ number_format($reputation->total_points) }}</div>
                    <div style="font-size: 0.98rem; font-weight: 700; color: #fef08a; text-transform: uppercase; margin-top: 0.2rem;">{{ $reputation->level_name }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- 2. TENGGAT WAKTU MINGGU INI (COMPACT & TERARAH) --}}
    @if((isset($upcomingAssignments) && $upcomingAssignments->count() > 0) || (isset($upcomingQuizzes) && $upcomingQuizzes->count() > 0))
    <div style="background: linear-gradient(90deg, #fffbeb 0%, #ffffff 65%); border: 1px solid #fcd34d; border-left: 4px solid #d97706; border-radius: 1.15rem; padding: 1.1rem 1.25rem; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 0.85rem;">
            <div style="display: flex; align-items: center; gap: 0.7rem;">
                <div style="width: 2.35rem; height: 2.35rem; border-radius: 0.75rem; background: #d97706; color: #ffffff; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-bell"></i>
                </div>
                <div>
                    <h3 style="font-size: 1.04rem; font-weight: 800; color: #0f172a; margin: 0;">Tenggat Waktu Minggu Ini</h3>
                    <p style="font-size: 1.08rem; color: #64748b; margin: 0;">Tugas &amp; kuis yang perlu segera diselesaikan</p>
                </div>
            </div>
            <span style="padding: 0.25rem 0.7rem; border-radius: 9999px; background: #fef3c7; color: #92400e; font-size: 0.84rem; font-weight: 800;">
                {{ ($upcomingAssignments->count() ?? 0) + ($upcomingQuizzes->count() ?? 0) }} Item
            </span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 0.75rem;">
            @foreach($upcomingAssignments->take(4) as $asgn)
            @php
                $deadlineDate = \Carbon\Carbon::parse($asgn->deadline);
                $isOverdue = now()->greaterThan($deadlineDate);
            @endphp
            <a href="{{ route('siswa.lms.show', $asgn->course_id) }}" style="padding: 0.75rem 0.95rem; border-radius: 0.85rem; background: #ffffff; border: 1px solid {{ $isOverdue ? '#fecdd3' : '#e2e8f0' }}; text-decoration: none; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                <div style="min-width: 0;">
                    <div style="font-size: 0.90rem; font-weight: 800; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $asgn->title }}</div>
                    <div style="font-size: 1.04rem; color: #64748b;">{{ $asgn->course->subject->subject_name ?? ($asgn->course->course_name ?? 'LMS') }} &bull; {{ $deadlineDate->format('d M H:i') }}</div>
                </div>
                <span style="padding: 0.2rem 0.55rem; border-radius: 9999px; font-size: 0.98rem; font-weight: 800; background: {{ $isOverdue ? '#fee2e2' : '#fef3c7' }}; color: {{ $isOverdue ? '#b91c1c' : '#92400e' }}; white-space: nowrap;">
                    {{ $isOverdue ? 'Terlewat' : 'Kerjakan' }}
                </span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 3. FILTER & PENCARIAN KURSUS --}}
    @if($courses->count() > 0)
    <div class="lms-toolbar">
        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.4rem;">
            <button type="button" @click="filter = 'all'" class="lms-filter-btn" :class="{ 'is-active': filter === 'all' }">Semua ({{ $totalCourses }})</button>
            <button type="button" @click="filter = 'in_progress'" class="lms-filter-btn" :class="{ 'is-active': filter === 'in_progress' }">Sedang Berjalan</button>
            <button type="button" @click="filter = 'completed'" class="lms-filter-btn" :class="{ 'is-active': filter === 'completed' }">Selesai ({{ $completedCourses }})</button>
            <button type="button" @click="filter = 'live'" class="lms-filter-btn" :class="{ 'is-active': filter === 'live' }">🔴 Live</button>
        </div>

        <div style="position: relative; min-width: 230px; flex: 1; max-width: 320px;">
            <i class="fas fa-search" style="position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.98rem;"></i>
            <input type="text"
                   x-model="search"
                   placeholder="Cari mata pelajaran atau guru..."
                   style="width: 100%; padding: 0.5rem 0.85rem 0.5rem 2.2rem; border-radius: 0.75rem; border: 1px solid #cbd5e1; background: #f8fafc; font-size: 0.98rem; color: #0f172a; outline: none;">
        </div>
    </div>

    {{-- 4. COURSE GRID --}}
    <div class="lms-course-grid">
        @foreach($courses as $course)
        @php
            $consolidatedSchedules = $course->getConsolidatedSchedule();
            $progress = $courseProgress[$course->id] ?? 0;
            $themeColor = $course->getActiveThemeHexColor() ?: '#1e3a8a';
            $courseTitle = $course->course_name ?? $course->name;
            $teacherName = $course->teacher->user->name ?? '-';
            $cDetail = $courseProgressDetails[$course->id] ?? null;
            $searchKey = strtolower($courseTitle . ' ' . $teacherName . ' ' . $course->getShortCode());
        @endphp
        <div class="lms-course-card"
             x-show="(!search.trim() || @js($searchKey).includes(search.toLowerCase().trim()))
                     && (filter === 'all'
                         || (filter === 'completed' && {{ $progress >= 100 ? 'true' : 'false' }})
                         || (filter === 'in_progress' && {{ $progress < 100 ? 'true' : 'false' }})
                         || (filter === 'live' && {{ $course->meeting_active ? 'true' : 'false' }}))">

            {{-- Card Header --}}
            <div style="position: relative; padding: 1.15rem 1.25rem; background: linear-gradient(135deg, {{ $themeColor }} 0%, #0f172a 135%); color: #ffffff;">
                @if($course->meeting_active)
                <a href="{{ route('siswa.lms.meeting.join', $course->id) }}" id="live-badge-{{ $course->id }}" style="position: absolute; top: 0.85rem; right: 0.85rem; display: inline-flex; align-items: center; gap: 0.35rem; background: #e11d48; color: #ffffff; padding: 0.25rem 0.6rem; border-radius: 9999px; font-size: 0.98rem; font-weight: 800; text-decoration: none; box-shadow: 0 2px 8px rgba(225, 29, 72, 0.45);">
                    <span class="w-1.5 h-1.5 bg-white rounded-full inline-block animate-ping"></span> LIVE
                </a>
                @else
                <a href="#" id="live-badge-{{ $course->id }}" class="hidden" style="position: absolute; top: 0.85rem; right: 0.85rem; align-items: center; gap: 0.35rem; background: #e11d48; color: #ffffff; padding: 0.25rem 0.6rem; border-radius: 9999px; font-size: 0.98rem; font-weight: 800; text-decoration: none;">
                    <span class="w-1.5 h-1.5 bg-white rounded-full inline-block animate-ping"></span> LIVE
                </a>
                @endif

                <div style="display: flex; align-items: center; gap: 0.85rem;">
                    <div style="width: 3rem; height: 3rem; border-radius: 0.85rem; overflow: hidden; border: 2px solid rgba(255,255,255,0.45); flex-shrink: 0; background: #ffffff;">
                        <img src="{{ $course->getTeacherPhotoUrl() }}" alt="{{ $teacherName }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($teacherName) }}&background=0f172a&color=ffffff&bold=true'">
                    </div>
                    <div style="min-width: 0; flex: 1;">
                        <span style="display: inline-block; background: rgba(15, 23, 42, 0.55); color: #fde047; padding: 0.15rem 0.5rem; border-radius: 0.45rem; font-size: 1.08rem; font-weight: 800; letter-spacing: 0.05em;">{{ $course->getShortCode() }}</span>
                        <h3 style="font-size: 1.06rem; font-weight: 800; color: #ffffff; margin: 0.25rem 0 0; line-height: 1.3;">{{ $courseTitle }}</h3>
                        <div style="font-size: 0.84rem; color: rgba(255,255,255,0.85); margin-top: 0.15rem;">{{ $teacherName }}</div>
                    </div>
                </div>
            </div>

            {{-- Card Body --}}
            <div style="padding: 1.15rem 1.25rem; flex: 1; display: flex; flex-direction: column; gap: 0.85rem;">
                @if($consolidatedSchedules->isNotEmpty())
                <div style="display: flex; flex-wrap: wrap; gap: 0.35rem;">
                    @foreach($consolidatedSchedules as $schLabel)
                    <span style="padding: 0.2rem 0.55rem; border-radius: 9999px; font-size: 0.98rem; font-weight: 700; background: #f1f5f9; color: #334155;">
                        <i class="far fa-clock" style="margin-right: 0.2rem;"></i> {{ $schLabel }}
                    </span>
                    @endforeach
                </div>
                @endif

                <p style="font-size: 0.98rem; color: #475569; line-height: 1.5; margin: 0;">
                    {{ \Illuminate\Support\Str::limit($course->description ?: 'Pelajari materi dan aktivitas ' . ($course->subject->subject_name ?? '') . ' secara interaktif.', 90) }}
                </p>

                {{-- Mini Stats --}}
                <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.45rem; background: #f8fafc; padding: 0.6rem; border-radius: 0.85rem; border: 1px solid #e2e8f0; text-align: center;">
                    <div>
                        <div style="font-size: 1.00rem; font-weight: 800; color: #0f172a;">{{ $course->modules_count ?? 0 }}</div>
                        <div style="font-size: 1.08rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Modul</div>
                    </div>
                    <div style="border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;">
                        <div style="font-size: 1.00rem; font-weight: 800; color: #0f172a;">{{ $course->materials_count ?? 0 }}</div>
                        <div style="font-size: 1.08rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Materi</div>
                    </div>
                    <div>
                        <div style="font-size: 1.00rem; font-weight: 800; color: #0f172a;">{{ $course->assignments_count ?? 0 }}</div>
                        <div style="font-size: 1.08rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Tugas</div>
                    </div>
                </div>

                {{-- Progress --}}
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 1.04rem; font-weight: 800; color: #334155; margin-bottom: 0.35rem;">
                        <span>{{ $progress >= 80 ? '🔥 Hampir Tuntas' : ($progress >= 40 ? '📖 Sedang Belajar' : ($progress > 0 ? '🚀 Baru Mulai' : '📚 Belum Dimulai')) }}</span>
                        <span style="color: #059669;">{{ number_format($progress) }}%</span>
                    </div>
                    <div style="width: 100%; height: 7px; background: #e2e8f0; border-radius: 9999px; overflow: hidden;">
                        <div style="width: {{ min(100, $progress) }}%; height: 100%; background: #059669; border-radius: 9999px;"></div>
                    </div>
                    @if($cDetail)
                    <div style="display: flex; justify-content: space-between; font-size: 0.98rem; color: #64748b; font-weight: 600; margin-top: 0.35rem;">
                        <span>Materi {{ $cDetail['materials']['completed'] }}/{{ $cDetail['materials']['total'] }}</span>
                        <span>Tugas {{ $cDetail['assignments']['completed'] }}/{{ $cDetail['assignments']['total'] }}</span>
                        <span>Kuis {{ $cDetail['quizzes']['completed'] }}/{{ $cDetail['quizzes']['total'] }}</span>
                    </div>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div style="margin-top: auto; display: flex; flex-direction: column; gap: 0.45rem;">
                    @if($course->meeting_active)
                    <a href="{{ route('siswa.lms.meeting.join', $course->id) }}" style="display: flex; align-items: center; justify-content: center; gap: 0.4rem; width: 100%; padding: 0.6rem; border-radius: 0.8rem; background: #e11d48; color: #ffffff; font-size: 1.08rem; font-weight: 800; text-decoration: none;">
                        <i class="fas fa-video"></i> Masuk PembdaTEMU Live
                    </a>
                    @endif

                    <div style="display: flex; gap: 0.45rem;">
                        <a href="{{ route('siswa.lms.show', $course->id) }}" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.65rem 0.9rem; border-radius: 0.8rem; background: {{ $themeColor }}; color: #ffffff; font-size: 1.08rem; font-weight: 800; text-decoration: none;">
                            <i class="fas fa-door-open"></i> Ruang Belajar
                        </a>
                        <a href="{{ route('siswa.ai.index', ['mode' => 'lms_assistant', 'prompt' => 'Bantu saya memahami materi dan cek tugas pada mata pelajaran "' . $courseTitle . '".']) }}"
                           title="Tanya Pembda AI tentang mata pelajaran ini"
                           style="padding: 0.65rem 0.85rem; border-radius: 0.8rem; background: #eef2ff; border: 1px solid #c7d2fe; color: #4338ca; font-size: 1.08rem; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem;">
                            <i class="fas fa-robot"></i> AI
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 1.25rem; padding: 3.5rem 1.5rem; text-align: center;">
        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📚</div>
        <h3 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0;">Belum Ada Course Terdaftar</h3>
        <p style="font-size: 0.90rem; color: #64748b; margin-top: 0.3rem;">Anda belum terdaftar di course manapun. Hubungi guru Anda untuk didaftarkan ke kelas.</p>
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
                        badge.style.display = 'none';
                        badge.href = '#';
                    });
                    if (data.live && data.live.length > 0) {
                        data.live.forEach(course => {
                            const badge = document.getElementById('live-badge-' + course.id);
                            if (badge) {
                                badge.classList.remove('hidden');
                                badge.style.display = 'inline-flex';
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
