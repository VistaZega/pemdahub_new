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
</style>
@endpush

@section('content')
<div class="space-y-10" style="padding-bottom: 3rem;">

    @php
        $totalCourses = $courses->count();
        $avgProgress = $totalCourses > 0 ? round(collect($courseProgress)->avg()) : 0;
        $completedCourses = collect($courseProgress)->filter(fn($p) => $p >= 100)->count();
        
        // Gamification Data
        $reputation = null;
        if ($student->user_id) {
            $reputation = \App\Models\Reputation::firstOrCreate(
                ['user_id' => $student->user_id],
                ['total_points' => 0, 'level_name' => 'Pemula']
            );
        }
    @endphp

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- 1. HERO GREETING BAR (SESUAI DASHBOARD SISWA) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="bg-white rounded-3xl border-2 border-indigo-100 shadow-lg relative overflow-hidden" style="border-radius: 1.75rem; border: 2px solid #e0e7ff; margin-bottom: 2rem;">
        {{-- Rainbow Decorative Stripe --}}
        <div class="h-3.5 w-full bg-gradient-to-r from-rose-500 via-amber-500 via-emerald-500 via-cyan-500 to-purple-600" style="height: 0.5rem;"></div>

        <div style="padding: 2rem 2.25rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 2rem;">
            <div style="display: flex; align-items: center; gap: 2rem; flex: 1; min-width: 280px;">
                <div style="position: relative; flex-shrink: 0;">
                    <div style="width: 5.5rem; height: 5.5rem; border-radius: 1.5rem; overflow: hidden; border: 4px solid #ffffff; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); background-color: #f8fafc;">
                        <img src="{{ $student->photo_url }}" style="width: 100%; height: 100%; object-fit: cover;" alt="{{ $student->full_name }}" onerror="this.onerror=null; this.src='{{ asset('images/default-student.jpg') }}';">
                    </div>
                    <span style="position: absolute; bottom: -0.25rem; right: -0.25rem; width: 1.75rem; height: 1.75rem; border-radius: 9999px; background-color: #10b981; border: 2px solid #ffffff; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; color: #ffffff; font-weight: 900; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);" title="Akun Aktif">
                        ✓
                    </span>
                </div>

                <div style="flex: 1; min-width: 0; padding-left: 0.5rem;">
                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.6rem; margin-bottom: 0.6rem;">
                        <span style="padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; background-color: #e0e7ff; color: #312e81; border: 1px solid #c7d2fe;">
                            🎓 Siswa Aktif
                        </span>
                        @if($student->classroom)
                        <span style="padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; background-color: #f3e8ff; color: #581c87; border: 1px solid #e9d5ff;">
                            🏛️ {{ $student->classroom->class_name }}
                        </span>
                        @endif
                        <span style="padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; background-color: #ecfdf5; color: #064e3b; border: 1px solid #a7f3d0;">
                            ✨ Learning Space LMS
                        </span>
                    </div>

                    <h1 style="font-size: 1.75rem; font-weight: 900; color: #0f172a; line-height: 1.35; margin-top: 0.25rem; margin-bottom: 0.4rem;">
                        Hai, {{ $student->full_name }}! 👋
                    </h1>

                    <p style="font-size: 0.875rem; color: #475569; font-weight: 600; display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; margin-top: 0.4rem;">
                        <span><i class="fas fa-school" style="color: #6366f1; margin-right: 0.4rem;"></i> {{ $student->school->name ?? 'Perguruan Pembda' }}</span>
                        <span>&bull;</span>
                        <span style="font-family: monospace; color: #64748b;">NISN: {{ $student->nisn ?: '-' }}</span>
                    </p>
                </div>
            </div>

            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; flex-shrink: 0;">
                <div style="padding: 0.75rem 1.25rem; border-radius: 1rem; background-color: #eef2ff; border: 2px solid #c7d2fe; color: #312e81; font-size: 0.8rem; font-weight: 900; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-book-reader" style="color: #4f46e5;"></i>
                    <span>{{ $totalCourses }} Mata Pelajaran</span>
                </div>
                <div style="padding: 0.75rem 1.25rem; border-radius: 1rem; background-color: #ecfdf5; border: 2px solid #a7f3d0; color: #064e3b; font-size: 0.8rem; font-weight: 900; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="far fa-clock" style="color: #059669;"></i>
                    <span>{{ now()->translatedFormat('l, d F Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- 2. 4 STAT CARDS (PALET WARNA DASHBOARD SISWA) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.75rem; margin-bottom: 2.5rem;">
        
        {{-- Card 1: Kursus Aktif (Indigo Theme) --}}
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 text-white shadow-lg" style="padding: 1.75rem; border-radius: 1.75rem; border: 2px solid rgba(129, 140, 248, 0.5); display: flex; flex-direction: column; justify-content: space-between; min-height: 160px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                <span style="font-size: 0.75rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; color: #dbeafe;">Total Kursus Aktif</span>
                <div style="width: 3rem; height: 3rem; border-radius: 1rem; background-color: #ffffff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); flex-shrink: 0;">
                    <i class="fas fa-book-open"></i>
                </div>
            </div>
            <div>
                <p style="font-size: 2.25rem; font-weight: 900; color: #ffffff; line-height: 1; letter-spacing: -0.025em;">{{ $totalCourses }}</p>
                <p style="font-size: 0.75rem; font-weight: 700; color: #dbeafe; margin-top: 0.6rem; line-height: 1.4;">
                    Mata Pelajaran Semester Ini
                </p>
            </div>
        </div>

        {{-- Card 2: Progres Belajar (Emerald Theme) --}}
        <div class="bg-gradient-to-br from-emerald-500 to-teal-700 text-white shadow-lg" style="padding: 1.75rem; border-radius: 1.75rem; border: 2px solid rgba(52, 211, 153, 0.5); display: flex; flex-direction: column; justify-content: space-between; min-height: 160px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                <span style="font-size: 0.75rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; color: #d1fae5;">Progres Belajar</span>
                <div style="width: 3rem; height: 3rem; border-radius: 1rem; background-color: #ffffff; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); flex-shrink: 0;">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <div>
                <p style="font-size: 2.25rem; font-weight: 900; color: #ffffff; line-height: 1; letter-spacing: -0.025em;">{{ $avgProgress }}%</p>
                <p style="font-size: 0.75rem; font-weight: 700; color: #d1fae5; margin-top: 0.6rem; line-height: 1.4;">
                    Rata-rata Penyelesaian Modul
                </p>
            </div>
        </div>

        {{-- Card 3: Poin Reputasi EXP (Amber Theme) --}}
        <div class="bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg" style="padding: 1.75rem; border-radius: 1.75rem; border: 2px solid rgba(251, 191, 36, 0.5); display: flex; flex-direction: column; justify-content: space-between; min-height: 160px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                <span style="font-size: 0.75rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; color: #fef3c7;">Poin Reputasi Elite</span>
                <div style="width: 3rem; height: 3rem; border-radius: 1rem; background-color: #ffffff; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); flex-shrink: 0;">
                    <i class="fas fa-crown"></i>
                </div>
            </div>
            <div>
                <p style="font-size: 2.25rem; font-weight: 900; color: #ffffff; line-height: 1; letter-spacing: -0.025em;">{{ number_format($reputation->total_points ?? 0) }} <span style="font-size: 0.875rem; font-weight: 700; color: #fde68a;">EXP</span></p>
                <p style="font-size: 0.75rem; font-weight: 700; color: #fef3c7; margin-top: 0.6rem; line-height: 1.4;">
                    Level: {{ $reputation->level_name ?? 'Pemula' }}
                </p>
            </div>
        </div>

        {{-- Card 4: Tugas & Kuis Menunggu (Purple Theme) --}}
        <div class="bg-gradient-to-br from-purple-600 to-pink-600 text-white shadow-lg" style="padding: 1.75rem; border-radius: 1.75rem; border: 2px solid rgba(232, 121, 249, 0.5); display: flex; flex-direction: column; justify-content: space-between; min-height: 160px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                <span style="font-size: 0.75rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; color: #fae8ff;">Tenggat Waktu</span>
                <div style="width: 3rem; height: 3rem; border-radius: 1rem; background-color: #ffffff; color: #9333ea; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); flex-shrink: 0;">
                    <i class="fas fa-tasks"></i>
                </div>
            </div>
            <div>
                <p style="font-size: 2.25rem; font-weight: 900; color: #ffffff; line-height: 1; letter-spacing: -0.025em;">{{ ($upcomingAssignments->count() ?? 0) + ($upcomingQuizzes->count() ?? 0) }}</p>
                <p style="font-size: 0.75rem; font-weight: 700; color: #fbcfe8; margin-top: 0.6rem; line-height: 1.4;">
                    Tugas & Kuis Minggu Ini
                </p>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- 3. RUANG BELAJAR KURSUS (SESUAI SECTION JADWAL DASHBOARD) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="bg-white shadow-xl" style="border-radius: 1.75rem; border: 2px solid #e0e7ff; overflow: hidden; margin-bottom: 2.5rem;">
        {{-- Section Header Bar --}}
        <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-purple-800 text-white" style="padding: 2rem 2.25rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 1.75rem;">
                <div style="width: 4rem; height: 4rem; border-radius: 1.25rem; background-color: #ffffff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-size: 2rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); flex-shrink: 0; font-weight: 900; margin-right: 0.5rem;">
                    📚
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.4rem;">
                        <span style="padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; background-color: #fbbf24; color: #0f172a;">
                            Semester Aktif
                        </span>
                        <span style="padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; background-color: #e0e7ff; color: #312e81;">
                            {{ $courses->count() }} Kelas Terdaftar
                        </span>
                    </div>
                    <h2 style="font-size: 1.4rem; font-weight: 900; color: #ffffff; line-height: 1.2; margin-top: 0.25rem;">
                        Ruang Belajar & Modul Interaktif
                    </h2>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 1rem;">
                <span style="font-size: 0.8rem; font-weight: 700; color: #c7d2fe;">Pilih mata pelajaran untuk mulai belajar</span>
            </div>
        </div>

        {{-- Course Cards Grid with Guaranteed Insets & Spacing --}}
        <div style="padding: 2.25rem;">
            @if($courses->count() > 0)
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(290px, 1fr)); gap: 1.75rem;">
                    @php
                        $colorPalettes = [
                            ['border' => '#93c5fd', 'bg' => 'from-blue-500 to-indigo-600', 'pill' => '#dbeafe', 'icon' => '📐'],
                            ['border' => '#d8b4fe', 'bg' => 'from-purple-500 to-pink-600', 'pill' => '#f3e8ff', 'icon' => '📖'],
                            ['border' => '#6ee7b7', 'bg' => 'from-emerald-500 to-teal-600', 'pill' => '#d1fae5', 'icon' => '🧪'],
                            ['border' => '#fcd34d', 'bg' => 'from-amber-500 to-orange-600', 'pill' => '#fef3c7', 'icon' => '💻'],
                            ['border' => '#fda4af', 'bg' => 'from-rose-500 to-red-600', 'pill' => '#ffe4e6', 'icon' => '⚽'],
                            ['border' => '#67e8f9', 'bg' => 'from-cyan-500 to-blue-600', 'pill' => '#cffafe', 'icon' => '🎨'],
                        ];
                        $cIdx = 0;
                    @endphp

                    @foreach($courses as $course)
                    @php
                        $progress = $courseProgress[$course->id] ?? 0;
                        $palette = $colorPalettes[$cIdx % count($colorPalettes)];
                        $cIdx++;
                        $consolidatedSchedules = $course->getConsolidatedSchedule();
                    @endphp

                    <div class="bg-white shadow-sm hover:shadow-lg transition" style="padding: 1.75rem; border-radius: 1.5rem; border: 2px solid #e2e8f0; display: flex; flex-direction: column; justify-content: space-between;">
                        
                        {{-- Top Header Pill & Status --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 1.25rem; padding-bottom: 0.85rem; border-bottom: 2px solid #f1f5f9;">
                            <div>
                                <span style="padding: 0.45rem 1rem; border-radius: 9999px; background-color: #0f172a; color: #ffffff; font-weight: 900; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    <i class="fas fa-bookmark" style="color: #fbbf24; margin-right: 0.25rem;"></i>
                                    <span>{{ $course->getShortCode() }}</span>
                                </span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                @if($course->meeting_active)
                                <a href="{{ route('siswa.lms.meeting.join', $course->id) }}" id="live-badge-{{ $course->id }}" style="padding: 0.4rem 0.9rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; background-color: #f43f5e; color: #ffffff; display: inline-flex; align-items: center; gap: 0.4rem;" class="animate-pulse shadow-xs">
                                    <i class="fas fa-circle" style="font-size: 0.4rem;"></i> LIVE
                                </a>
                                @else
                                <a href="#" id="live-badge-{{ $course->id }}" style="padding: 0.4rem 0.9rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; background-color: #f43f5e; color: #ffffff; display: none; align-items: center; gap: 0.4rem;" class="animate-pulse shadow-xs">
                                    <i class="fas fa-circle" style="font-size: 0.4rem;"></i> LIVE
                                </a>
                                @endif
                                
                                <span style="padding: 0.4rem 0.9rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; background-color: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; display: inline-flex; align-items: center; gap: 0.4rem;">
                                    {{ $course->modules_count ?? 0 }} Modul
                                </span>
                            </div>
                        </div>

                        {{-- Subject Content with Wide Margins & Inset --}}
                        <div style="margin-top: 0.5rem; margin-bottom: 0.5rem;">
                            <div style="display: flex; align-items: flex-start; gap: 1.25rem; margin-bottom: 1rem;">
                                <div class="bg-gradient-to-br {{ $palette['bg'] }} text-white" style="width: 3.5rem; height: 3.5rem; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; flex-shrink: 0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-right: 0.25rem;">
                                    {{ $palette['icon'] }}
                                </div>
                                <div style="flex: 1; min-width: 0; padding-top: 0.2rem;">
                                    <h3 style="font-size: 1.1rem; font-weight: 900; color: #0f172a; line-height: 1.35; margin-bottom: 0.35rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        {{ $course->course_name ?? $course->name }}
                                    </h3>
                                    <p style="font-size: 0.8rem; color: #475569; font-weight: 700; display: flex; align-items: center; gap: 0.5rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <i class="fas fa-chalkboard-teacher" style="color: #6366f1;"></i>
                                        <span>{{ $course->teacher->user->name ?? '-' }}</span>
                                    </p>
                                </div>
                            </div>

                            {{-- Schedule and Meta Info pill (CLEANLY INSET & PADDED) --}}
                            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem; background-color: #f8fafc; border-radius: 1rem; border: 1px solid #e2e8f0; padding: 0.75rem 1rem; margin-top: 0.75rem;">
                                <span style="font-weight: 700; color: #334155; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-file-alt" style="color: #6366f1;"></i>
                                    <span>{{ $course->materials_count ?? 0 }} Materi · {{ $course->assignments_count ?? 0 }} Tugas</span>
                                </span>
                                <span style="font-weight: 900; color: #3730a3; background-color: #e0e7ff; padding: 0.2rem 0.6rem; border-radius: 0.6rem;">
                                    {{ number_format($progress) }}% Tuntas
                                </span>
                            </div>

                            {{-- Progress bar --}}
                            <div style="margin-top: 1rem; margin-bottom: 0.25rem;">
                                <div style="width: 100%; background-color: #e2e8f0; border-radius: 9999px; height: 0.5rem; overflow: hidden;">
                                    <div style="height: 100%; background-color: #4f46e5; border-radius: 9999px; width: {{ $progress }}%;"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Footer --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-top: 1.25rem; padding-top: 0.85rem; border-top: 2px solid #f1f5f9;">
                            <span style="font-size: 0.75rem; font-weight: 700; color: #64748b;">
                                {{ $progress >= 100 ? '✅ Selesai' : ($progress > 0 ? '📖 Aktif Belajar' : 'Belum Dimulai') }}
                            </span>

                            <a href="{{ route('siswa.lms.show', $course->id) }}" style="padding: 0.6rem 1.25rem; background-color: #4f46e5; color: #ffffff; border-radius: 0.85rem; font-size: 0.75rem; font-weight: 900; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.25);">
                                <span>Masuk Ruang Belajar</span>
                                <i class="fas fa-arrow-right" style="font-size: 0.7rem;"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div style="padding: 4rem 2rem; text-align: center; background-color: #f8fafc; border-radius: 1.75rem; border: 2px dashed #cbd5e1;">
                    <div style="width: 5rem; height: 5rem; background-color: #ffffff; color: #94a3b8; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; font-size: 2.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                        📚
                    </div>
                    <h3 style="font-weight: 900; color: #1e293b; font-size: 1.2rem; margin-bottom: 0.5rem;">Belum Ada Kursus Terdaftar</h3>
                    <p style="font-size: 0.85rem; color: #64748b; max-width: 450px; margin: 0 auto 1.5rem auto; line-height: 1.6;">Anda belum terdaftar di kelas manapun. Hubungi guru mata pelajaran atau wali kelas Anda untuk didaftarkan ke ruang belajar LMS.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- 4. DUA KOLOM: TENGGAT WAKTU & LEADERBOARD (STYLE DASHBOARD) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
        
        {{-- KOLOM KIRI: TENGGAT WAKTU TUGAS & KUIS --}}
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            <div class="bg-white shadow-sm" style="padding: 2rem; border-radius: 1.75rem; border: 2px solid #e0e7ff;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 1.25rem;">
                        <div class="bg-gradient-to-br from-amber-400 to-orange-500 text-white" style="width: 3.5rem; height: 3.5rem; border-radius: 1.25rem; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; flex-shrink: 0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-right: 0.25rem;">
                            ⏰
                        </div>
                        <div>
                            <h3 style="font-size: 1.15rem; font-weight: 900; color: #0f172a; line-height: 1.3;">Tenggat Waktu Tugas & Kuis</h3>
                            <p style="font-size: 0.8rem; color: #64748b; font-weight: 600; margin-top: 0.25rem;">Item pembelajaran yang perlu diselesaikan segera</p>
                        </div>
                    </div>
                </div>

                @if((isset($upcomingAssignments) && $upcomingAssignments->count() > 0) || (isset($upcomingQuizzes) && $upcomingQuizzes->count() > 0))
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($upcomingAssignments as $asgn)
                    @php
                        $diffHours = \Carbon\Carbon::now()->diffInHours(\Carbon\Carbon::parse($asgn->deadline), false);
                        $isUrgent = $diffHours <= 24;
                    @endphp
                    <div class="bg-gradient-to-br from-slate-50 to-indigo-50/40 shadow-2xs" style="padding: 1.25rem; border-radius: 1.5rem; border: 2px solid #e0e7ff; display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                        <div style="display: flex; align-items: center; gap: 1rem; min-width: 0;">
                            <div style="width: 3rem; height: 3rem; border-radius: 1rem; background-color: #ffffff; border: 1px solid #c7d2fe; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; color: #4f46e5; flex-shrink: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-right: 0.25rem;">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div style="min-width: 0;">
                                <div style="display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.25rem;">
                                    <span style="padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 900; background-color: {{ $isUrgent ? '#ffe4e6' : '#e0e7ff' }}; color: {{ $isUrgent ? '#881337' : '#312e81' }};">
                                        {{ $asgn->course->subject->subject_name ?? 'Tugas' }}
                                    </span>
                                </div>
                                <p style="font-size: 0.85rem; font-weight: 900; color: #0f172a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $asgn->title }}</p>
                                <p style="font-size: 0.75rem; color: #64748b; font-weight: 600; margin-top: 0.15rem;">Deadline: {{ \Carbon\Carbon::parse($asgn->deadline)->translatedFormat('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('siswa.lms.show', $asgn->course_id) }}?tab=assignments" style="padding: 0.5rem 0.85rem; background-color: #4f46e5; color: #ffffff; border-radius: 0.75rem; font-size: 0.75rem; font-weight: 900; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem; flex-shrink: 0;">
                            <span>Kerjakan</span>
                        </a>
                    </div>
                    @endforeach

                    @foreach($upcomingQuizzes as $qz)
                    <div class="bg-gradient-to-br from-slate-50 to-purple-50/40 shadow-2xs" style="padding: 1.25rem; border-radius: 1.5rem; border: 2px solid #e0e7ff; display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                        <div style="display: flex; align-items: center; gap: 1rem; min-width: 0;">
                            <div style="width: 3rem; height: 3rem; border-radius: 1rem; background-color: #ffffff; border: 1px solid #e9d5ff; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; color: #9333ea; flex-shrink: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-right: 0.25rem;">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <div style="min-width: 0;">
                                <div style="display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.25rem;">
                                    <span style="padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 900; background-color: #f3e8ff; color: #581c87;">
                                        {{ $qz->course->subject->subject_name ?? 'Kuis' }}
                                    </span>
                                </div>
                                <p style="font-size: 0.85rem; font-weight: 900; color: #0f172a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $qz->title }}</p>
                                <p style="font-size: 0.75rem; color: #64748b; font-weight: 600; margin-top: 0.15rem;">{{ $qz->questions_count ?? 0 }} Soal Evaluasi</p>
                            </div>
                        </div>
                        <a href="{{ route('siswa.lms.quizzes.start', $qz->id) }}" style="padding: 0.5rem 0.85rem; background-color: #9333ea; color: #ffffff; border-radius: 0.75rem; font-size: 0.75rem; font-weight: 900; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem; flex-shrink: 0;">
                            <span>Mulai Kuis</span>
                        </a>
                    </div>
                    @endforeach
                </div>
                @else
                <div style="padding: 2rem; text-align: center; background-color: #f8fafc; border-radius: 1.5rem; border: 1px solid #e2e8f0;">
                    <p style="font-size: 0.85rem; color: #64748b; font-weight: 700;">🎉 Tidak ada tugas atau kuis yang mendesak saat ini. Terus tingkatkan prestasimu!</p>
                </div>
                @endif
            </div>
        </div>

        {{-- KOLOM KANAN: PEMBDA ELITE LEADERBOARD --}}
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            <div class="bg-white shadow-sm" style="padding: 2rem; border-radius: 1.75rem; border: 2px solid #fde68a;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
                    <h3 style="font-size: 1rem; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-crown" style="color: #f59e0b;"></i>
                        <span>Papan Peringkat EXP</span>
                    </h3>
                    <span style="font-size: 0.75rem; font-weight: 900; color: #d97706;">Top Pembelajar 🏆</span>
                </div>

                <div class="bg-gradient-to-br from-amber-500 via-orange-500 to-amber-600 shadow-md text-white" style="padding: 2rem 1.5rem; text-align: center; border-radius: 1.5rem; margin-bottom: 1.5rem;">
                    <span style="background-color: rgba(255,255,255,0.25); color: #ffffff; font-size: 0.7rem; font-weight: 900; padding: 0.35rem 1rem; border-radius: 9999px; text-transform: uppercase;">
                        POIN REPUTASI ANDA
                    </span>
                    <div style="font-size: 2.5rem; font-weight: 900; color: #ffffff; margin-top: 0.75rem; margin-bottom: 0.75rem; line-height: 1;">
                        {{ number_format($reputation->total_points ?? 0) }} EXP
                    </div>
                    <div>
                        <span style="display: inline-block; padding: 0.4rem 1.25rem; background-color: #ffffff; color: #0f172a; font-size: 0.75rem; font-weight: 900; border-radius: 9999px; text-transform: uppercase; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            {{ $reputation->level_name ?? 'Level Siswa' }}
                        </span>
                    </div>
                </div>

                {{-- Leaderboard Top List --}}
                @if(isset($leaderboard) && $leaderboard->count() > 0)
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <p style="font-size: 0.7rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">Top 5 Siswa Teraktif</p>
                    @foreach($leaderboard as $lb)
                    @php
                        $medalIcon = match($loop->iteration) {
                            1 => '🥇',
                            2 => '🥈',
                            3 => '🥉',
                            default => '#' . $loop->iteration,
                        };
                    @endphp
                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem; padding: 0.75rem 1rem; border-radius: 1rem; background-color: #f8fafc; border: 1px solid #f1f5f9;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1; min-width: 0;">
                            <span style="font-weight: 900; font-size: 1rem;">{{ $medalIcon }}</span>
                            <div style="flex: 1; min-width: 0;">
                                <p style="font-weight: 900; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $lb->user->name ?? 'Siswa' }}</p>
                            </div>
                        </div>
                        <span style="font-weight: 900; font-size: 0.85rem; margin-left: 0.75rem; color: #d97706;">
                            {{ number_format($lb->total_points) }} EXP
                        </span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

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
                        badge.style.display = 'none';
                        badge.href = '#';
                    });

                    if (data.live_courses && data.live_courses.length > 0) {
                        data.live_courses.forEach(item => {
                            const badge = document.getElementById('live-badge-' + item.id);
                            if (badge) {
                                badge.style.display = 'inline-flex';
                                badge.href = item.join_url;
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

