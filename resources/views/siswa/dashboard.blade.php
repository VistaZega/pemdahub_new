@extends('layouts.siswa')
@section('title', 'Dashboard - Portal Siswa')

@section('content')
<style>
    .sd-wrap {
        max-width: 1320px;
        margin: 0 auto;
        padding-bottom: 2.5rem;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    .sd-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 1.25rem;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
    }
    .sd-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 58%, #312e81 100%);
        border: 1px solid #1e293b;
        border-radius: 1.35rem;
        padding: 1.5rem 1.75rem;
        color: #ffffff;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.25);
        position: relative;
        overflow: hidden;
    }
    .sd-hero::after {
        content: '';
        position: absolute;
        top: -45px;
        right: -45px;
        width: 190px;
        height: 190px;
        border-radius: 9999px;
        background: radial-gradient(circle, rgba(245, 158, 11, 0.22) 0%, rgba(245, 158, 11, 0) 70%);
        pointer-events: none;
    }
    .sd-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }
    .sd-kpi-card {
        border-radius: 1.15rem;
        padding: 1.15rem 1.25rem;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .sd-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
    }
    .sd-bento-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(0, 1fr);
        gap: 1.25rem;
        align-items: start;
    }
    @media (max-width: 1024px) {
        .sd-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .sd-bento-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }
    @media (max-width: 560px) {
        .sd-kpi-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }
    .sd-tab-btn {
        padding: 0.5rem 0.9rem;
        border-radius: 0.7rem;
        font-size: 0.98rem;
        font-weight: 700;
        transition: all 0.15s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        border: 1px solid transparent;
    }
    .sd-tab-btn.is-active {
        background: #0f172a;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.18);
    }
    .sd-tab-btn:not(.is-active) {
        color: #475569;
        background: #f1f5f9;
    }
    .sd-tab-btn:not(.is-active):hover {
        background: #e2e8f0;
        color: #0f172a;
    }
</style>

<div class="sd-wrap" x-data="{ mainTab: '{{ $groupedTodaySchedules->count() > 0 ? 'jadwal' : 'tugas' }}', taskFilter: 'all', rightTab: 'ekskul' }">

    {{-- 1. HERO WELCOME (DEEP SLATE + WARM AMBER ACCENT — KONTRAS & BERKARAKTER) --}}
    <div class="sd-hero">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.25rem; position: relative; z-index: 1;">
            <div style="display: flex; align-items: center; gap: 1.1rem; min-width: 0; flex: 1;">
                <div style="position: relative; flex-shrink: 0;">
                    <div style="width: 4.1rem; height: 4.1rem; border-radius: 1.1rem; overflow: hidden; border: 2.5px solid #f59e0b; background: #1e293b; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                        <img src="{{ $student->photo_url }}" style="width: 100%; height: 100%; object-fit: cover;" alt="{{ $student->full_name }}" onerror="this.onerror=null; this.src='{{ asset('images/default-student.jpg') }}';">
                    </div>
                    <span style="position: absolute; bottom: -2px; right: -2px; width: 1rem; height: 1rem; border-radius: 9999px; background: #10b981; border: 2px solid #0f172a;" title="Aktif"></span>
                </div>

                <div style="min-width: 0;">
                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.45rem; margin-bottom: 0.35rem;">
                        @if($classroom)
                        <span style="padding: 0.2rem 0.65rem; border-radius: 9999px; font-size: 0.80rem; font-weight: 800; background: #f59e0b; color: #0f172a;">
                            {{ $classroom->class_name }}
                        </span>
                        @endif
                        @if($activeYear)
                        <span style="padding: 0.2rem 0.65rem; border-radius: 9999px; font-size: 0.80rem; font-weight: 700; background: rgba(255,255,255,0.12); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.18);">
                            TP {{ $activeYear->year }}
                        </span>
                        @endif
                        <span style="font-size: 0.84rem; color: #cbd5e1; font-weight: 600;">
                            {{ now()->translatedFormat('l, d F Y') }}
                        </span>
                    </div>
                    <h1 style="font-size: 1.55rem; font-weight: 800; color: #ffffff; line-height: 1.25; margin: 0;">
                        Hai, {{ explode(' ', trim($student->full_name))[0] }}!
                    </h1>
                    <p style="font-size: 0.98rem; color: #cbd5e1; font-weight: 500; margin-top: 0.2rem;">
                        {{ $student->school->name ?? 'Perguruan Pembda' }} &bull; NISN {{ $student->nisn ?: '-' }}
                    </p>
                    @php
                        $countdownTarget = null;
                        $countdownLabel = '';
                        $countdownMode = '';
                        if ($currentSchedule) {
                            $endStr = $currentSchedule->timeSlot->end_time ?? $currentSchedule->end_time ?? null;
                            $subjName = $currentSchedule->teachingAssignment?->subject?->subject_name ?? $currentSchedule->teachingAssignment?->subject?->name ?? 'Pelajaran';
                            if ($endStr) {
                                $countdownTarget = \Carbon\Carbon::parse(now()->toDateString() . ' ' . $endStr)->timestamp * 1000;
                                $countdownLabel = $subjName;
                                $countdownMode = 'current';
                            }
                        } elseif ($nextSchedule) {
                            $startStr = $nextSchedule->timeSlot->start_time ?? $nextSchedule->start_time ?? null;
                            $subjName = $nextSchedule->teachingAssignment?->subject?->subject_name ?? $nextSchedule->teachingAssignment?->subject?->name ?? 'Pelajaran';
                            if ($startStr) {
                                $countdownTarget = \Carbon\Carbon::parse(now()->toDateString() . ' ' . $startStr)->timestamp * 1000;
                                $countdownLabel = $subjName;
                                $countdownMode = 'next';
                            }
                        }
                    @endphp
                    @if($countdownTarget)
                    <div id="live-class-countdown"
                         data-target="{{ $countdownTarget }}"
                         data-mode="{{ $countdownMode }}"
                         style="margin-top: 0.45rem; display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.25rem 0.65rem; border-radius: 9999px; background: {{ $countdownMode === 'current' ? 'rgba(16, 185, 129, 0.2)' : 'rgba(245, 158, 11, 0.2)' }}; border: 1px solid {{ $countdownMode === 'current' ? 'rgba(52, 211, 153, 0.45)' : 'rgba(251, 191, 36, 0.45)' }}; font-size: 0.84rem; font-weight: 700; color: {{ $countdownMode === 'current' ? '#6ee7b7' : '#fde68a' }};">
                        <span style="width: 6px; height: 6px; border-radius: 9999px; background: {{ $countdownMode === 'current' ? '#34d399' : '#fbbf24' }};"></span>
                        <span>{{ $countdownMode === 'current' ? 'Berlangsung:' : 'Berikutnya:' }} <strong>{{ $countdownLabel }}</strong></span>
                        <span>&bull;</span>
                        <span id="live-countdown-clock" style="font-family: monospace; font-weight: 800;">--:--</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Quick Action Buttons --}}
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.6rem; flex-shrink: 0;">
                @if(!$todayAttendance || !$todayAttendance->time_out)
                <a href="{{ route('siswa.absensi') }}"
                   style="padding: 0.65rem 1.05rem; border-radius: 0.85rem; background: #ea580c; color: #ffffff; border: 1px solid #fb923c; font-size: 0.98rem; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 0.45rem; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.35);">
                    <i class="fas fa-fingerprint"></i>
                    <span>{{ !$todayAttendance ? 'Absen Masuk' : 'Absen Pulang (Masuk ' . date('H:i', strtotime($todayAttendance->time_in)) . ')' }}</span>
                </a>
                @else
                <span style="padding: 0.6rem 0.95rem; border-radius: 0.85rem; background: rgba(16, 185, 129, 0.18); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.4); font-size: 1.08rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <i class="fas fa-check-circle"></i> Presensi Lengkap
                </span>
                @endif

                <a href="{{ route('siswa.ai.index') }}"
                   style="padding: 0.65rem 1.05rem; border-radius: 0.85rem; background: #ffffff; color: #0f172a; font-size: 0.98rem; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 0.45rem; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                    <i class="fas fa-robot" style="color: #4f46e5;"></i>
                    <span>Pembda AI</span>
                </a>

                @php
                    $__circleProfile = \App\Models\CircleProfile::where('user_id', Auth::id())->first();
                    $__circleUsername = $__circleProfile?->username ?? Auth::user()->username;
                @endphp
                <a href="{{ route('circle.profile', ['username' => $__circleUsername]) }}"
                   style="padding: 0.65rem 1rem; border-radius: 0.85rem; background: rgba(255,255,255,0.12); color: #ffffff; border: 1px solid rgba(255,255,255,0.22); font-size: 0.98rem; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <i class="fas fa-bolt" style="color: #fbbf24;"></i>
                    <span>Circle</span>
                </a>
            </div>
        </div>
    </div>

    {{-- 2. ALERT LIVE PEMBDATEMU (HANYA JIKA ADA KELAS LIVE) --}}
    @if(isset($activeMeetings) && $activeMeetings->isNotEmpty())
        @foreach($activeMeetings as $meetingCourse)
        <div class="sd-card" style="padding: 1rem 1.35rem; background: linear-gradient(90deg, #ffe4e6 0%, #fff1f2 100%); border-color: #fb7185; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.9rem;">
                <div style="width: 2.75rem; height: 2.75rem; border-radius: 0.85rem; background: #e11d48; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                    <i class="fas fa-video"></i>
                </div>
                <div>
                    <span style="font-size: 0.98rem; font-weight: 900; color: #be123c; text-transform: uppercase; letter-spacing: 0.05em;">● KELAS LIVE PEMBDATEMU AKTIF</span>
                    <div style="font-size: 1.06rem; font-weight: 800; color: #0f172a;">{{ $meetingCourse->course_name ?? $meetingCourse->name }}</div>
                    <div style="font-size: 1.08rem; color: #475569;">Guru: {{ $meetingCourse->teacher?->user?->name ?? 'Guru Pengajar' }}</div>
                </div>
            </div>
            <a href="{{ route('siswa.lms.meeting.join', $meetingCourse->id) }}" style="padding: 0.6rem 1.15rem; border-radius: 0.85rem; background: #e11d48; color: #fff; font-size: 0.98rem; font-weight: 800; text-decoration: none;">
                Masuk Kelas Live &rarr;
            </a>
        </div>
        @endforeach
    @endif

    {{-- 3. 4 KPI CARDS (TINTED SURFACE + SOLID ICON BADGES — HIDUP & SEIMBANG) --}}
    <div class="sd-kpi-grid">
        <a href="{{ route('siswa.absensi') }}" class="sd-kpi-card" style="background: linear-gradient(135deg, #ecfdf5 0%, #ffffff 70%); border: 1px solid #a7f3d0; border-top: 3px solid #059669;">
            <div>
                <div style="font-size: 1.04rem; font-weight: 800; color: #047857; text-transform: uppercase;">Presensi</div>
                <div style="font-size: 1.55rem; font-weight: 900; color: #064e3b; line-height: 1.2; margin-top: 0.15rem;">{{ $attendanceData['percentage'] }}%</div>
                <div style="font-size: 0.84rem; color: #047857; font-weight: 600; margin-top: 0.15rem;">{{ $attendanceData['present'] }}/{{ $attendanceData['total'] }} hari efektif</div>
            </div>
            <div style="width: 2.85rem; height: 2.85rem; border-radius: 0.9rem; background: #059669; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; box-shadow: 0 4px 10px rgba(5, 150, 105, 0.25);">
                <i class="fas fa-user-check"></i>
            </div>
        </a>

        <a href="{{ route('siswa.nilai') }}" class="sd-kpi-card" style="background: linear-gradient(135deg, #eff6ff 0%, #ffffff 70%); border: 1px solid #bfdbfe; border-top: 3px solid #2563eb;">
            <div>
                <div style="font-size: 1.04rem; font-weight: 800; color: #1d4ed8; text-transform: uppercase;">Rata-Rata Nilai</div>
                <div style="font-size: 1.55rem; font-weight: 900; color: #1e3a8a; line-height: 1.2; margin-top: 0.15rem;">{{ number_format($avgScore, 1) }}</div>
                <div style="font-size: 0.84rem; color: #1d4ed8; font-weight: 600; margin-top: 0.15rem;">Indeks semester aktif</div>
            </div>
            <div style="width: 2.85rem; height: 2.85rem; border-radius: 0.9rem; background: #2563eb; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);">
                <i class="fas fa-chart-line"></i>
            </div>
        </a>

        <div class="sd-kpi-card" style="background: linear-gradient(135deg, #fffbeb 0%, #ffffff 70%); border: 1px solid #fde68a; border-top: 3px solid #d97706;">
            <div>
                <div style="font-size: 1.04rem; font-weight: 800; color: #b45309; text-transform: uppercase;">Poin Reputasi</div>
                <div style="font-size: 1.55rem; font-weight: 900; color: #78350f; line-height: 1.2; margin-top: 0.15rem;">{{ number_format($reputation->total_points ?? 0) }}</div>
                <div style="font-size: 0.84rem; color: #b45309; font-weight: 700; margin-top: 0.15rem;">Peringkat #{{ $rank ?? 1 }} &bull; {{ $reputation->level_name ?? 'Pemula' }}</div>
            </div>
            <div style="width: 2.85rem; height: 2.85rem; border-radius: 0.9rem; background: #d97706; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; box-shadow: 0 4px 10px rgba(217, 119, 6, 0.25);">
                <i class="fas fa-crown"></i>
            </div>
        </div>

        <a href="{{ route('siswa.tagihan') }}" class="sd-kpi-card" style="background: linear-gradient(135deg, {{ $totalOutstanding > 0 ? '#fff1f2' : '#ecfdf5' }} 0%, #ffffff 70%); border: 1px solid {{ $totalOutstanding > 0 ? '#fecdd3' : '#a7f3d0' }}; border-top: 3px solid {{ $totalOutstanding > 0 ? '#e11d48' : '#059669' }};">
            <div>
                <div style="font-size: 1.04rem; font-weight: 800; color: {{ $totalOutstanding > 0 ? '#be123c' : '#047857' }}; text-transform: uppercase;">Biaya Pendidikan</div>
                <div style="font-size: 1.22rem; font-weight: 900; color: {{ $totalOutstanding > 0 ? '#9f1239' : '#064e3b' }}; line-height: 1.35; margin-top: 0.2rem;">
                    {{ $totalOutstanding > 0 ? 'Belum Lunas' : 'Lunas' }}
                </div>
                <div style="font-size: 0.84rem; color: {{ $totalOutstanding > 0 ? '#be123c' : '#047857' }}; font-weight: 600; margin-top: 0.15rem;">
                    {{ $totalOutstanding > 0 ? 'Cek rincian tagihan' : 'Bebas tunggakan' }}
                </div>
            </div>
            <div style="width: 2.85rem; height: 2.85rem; border-radius: 0.9rem; background: {{ $totalOutstanding > 0 ? '#e11d48' : '#059669' }}; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; box-shadow: 0 4px 10px {{ $totalOutstanding > 0 ? 'rgba(225, 29, 72, 0.25)' : 'rgba(5, 150, 105, 0.25)' }};">
                <i class="fas fa-wallet"></i>
            </div>
        </a>
    </div>

    {{-- 4. MAIN BENTO WORKSPACE (2 KOLOM ASIMETRIS) --}}
    <div class="sd-bento-grid">

        {{-- KOLOM KIRI: FOKUS BELAJAR HARI INI (1 CARD DENGAN 3 TAB) --}}
        <div class="sd-card" style="overflow: hidden;">
            {{-- Tab Header --}}
            <div style="padding: 1rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem;">
                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.4rem;">
                    <button type="button" @click="mainTab = 'jadwal'" class="sd-tab-btn" :class="{ 'is-active': mainTab === 'jadwal' }">
                        <i class="fas fa-calendar-day"></i>
                        <span>Jadwal Hari Ini</span>
                        <span style="font-size: 0.80rem; padding: 0.1rem 0.45rem; border-radius: 9999px; background: rgba(255,255,255,0.2); border: 1px solid rgba(148,163,184,0.35);">{{ $groupedTodaySchedules->count() }}</span>
                    </button>
                    <button type="button" @click="mainTab = 'tugas'" class="sd-tab-btn" :class="{ 'is-active': mainTab === 'tugas' }">
                        <i class="fas fa-tasks"></i>
                        <span>Tugas &amp; Ujian</span>
                        @if($unifiedDeadlines->isNotEmpty())
                        <span style="font-size: 0.80rem; padding: 0.1rem 0.45rem; border-radius: 9999px; background: #ef4444; color: #ffffff;">{{ $unifiedDeadlines->count() }}</span>
                        @endif
                    </button>
                    <button type="button" @click="mainTab = 'lms'" class="sd-tab-btn" :class="{ 'is-active': mainTab === 'lms' }">
                        <i class="fas fa-book-open"></i>
                        <span>Kursus LMS</span>
                        <span style="font-size: 0.80rem; padding: 0.1rem 0.45rem; border-radius: 9999px; background: rgba(148,163,184,0.25);">{{ $courses->count() }}</span>
                    </button>
                </div>

                <a x-show="mainTab === 'jadwal'" href="{{ route('siswa.jadwal') }}" style="font-size: 1.08rem; font-weight: 800; color: #4338ca; text-decoration: none;">Jadwal Lengkap &rarr;</a>
                <a x-show="mainTab === 'tugas'" href="{{ route('siswa.lms.index') }}" style="font-size: 1.08rem; font-weight: 800; color: #4338ca; text-decoration: none;">Semua Tugas &rarr;</a>
                <a x-show="mainTab === 'lms'" href="{{ route('siswa.lms.index') }}" style="font-size: 1.08rem; font-weight: 800; color: #4338ca; text-decoration: none;">Buka LMS &rarr;</a>
            </div>

            {{-- TAB 1: JADWAL HARI INI --}}
            <div x-show="mainTab === 'jadwal'" style="padding: 1.1rem 1.25rem; background: #ffffff;">
                @if($groupedTodaySchedules->count() > 0)
                    <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                        @foreach($groupedTodaySchedules as $timeKey => $schedulesAtTime)
                            @php
                                $timeParts = explode(' - ', $timeKey);
                                $sStart = isset($timeParts[0]) ? \Carbon\Carbon::parse($timeParts[0])->format('H:i') : '--:--';
                                $sEnd = isset($timeParts[1]) ? \Carbon\Carbon::parse($timeParts[1])->format('H:i') : '--:--';
                                $isCurrent = false;
                                $isNext = false;
                                foreach($schedulesAtTime as $s) {
                                    if ($currentSchedule && $currentSchedule->id === $s->id) $isCurrent = true;
                                    if ($nextSchedule && $nextSchedule->id === $s->id) $isNext = true;
                                }
                                $isPast = $currentTime > $sEnd;
                            @endphp
                            <div style="padding: 0.85rem 1rem; border-radius: 0.95rem; border: 1px solid {{ $isCurrent ? '#34d399' : ($isNext ? '#fbbf24' : '#e2e8f0') }}; border-left: 4px solid {{ $isCurrent ? '#059669' : ($isNext ? '#d97706' : ($isPast ? '#94a3b8' : '#4f46e5')) }}; background: {{ $isCurrent ? '#ecfdf5' : ($isNext ? '#fffbeb' : '#f8fafc') }}; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem;">
                                <div style="display: flex; align-items: center; gap: 0.85rem; min-width: 0; flex: 1;">
                                    <div style="padding: 0.35rem 0.65rem; border-radius: 0.65rem; background: {{ $isCurrent ? '#059669' : ($isNext ? '#d97706' : '#0f172a') }}; color: #fff; font-size: 0.84rem; font-weight: 800; font-family: monospace; white-space: nowrap;">
                                        {{ $sStart }} - {{ $sEnd }}
                                    </div>
                                    <div style="min-width: 0;">
                                        @foreach($schedulesAtTime as $sch)
                                            <div style="font-size: 0.98rem; font-weight: 800; color: #0f172a; line-height: 1.3;">
                                                {{ $sch->teachingAssignment?->subject?->subject_name ?? $sch->teachingAssignment?->subject?->name ?? 'Mata Pelajaran' }}
                                            </div>
                                            <div style="font-size: 1.08rem; color: #475569; font-weight: 500;">
                                                {{ $sch->teachingAssignment?->teacher?->user?->name ?? $sch->teachingAssignment?->teacher?->full_name ?? '-' }}
                                                @if($sch->classroom) &bull; {{ $sch->classroom->class_name }} @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    @if($isCurrent)
                                        <span style="padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.80rem; font-weight: 800; background: #059669; color: #fff;">Berlangsung</span>
                                    @elseif($isNext)
                                        <span style="padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.80rem; font-weight: 800; background: #d97706; color: #fff;">Berikutnya</span>
                                    @elseif($isPast)
                                        <span style="padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.80rem; font-weight: 700; background: #e2e8f0; color: #475569;">Selesai</span>
                                    @else
                                        <span style="padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.80rem; font-weight: 700; background: #e0e7ff; color: #3730a3;">Terjadwal</span>
                                    @endif

                                    @php
                                        $firstSch = $schedulesAtTime->first();
                                        $subId = $firstSch?->teachingAssignment?->subject_id;
                                        $matchedCourse = $courses->firstWhere('subject_id', $subId);
                                    @endphp
                                    @if($matchedCourse)
                                        <a href="{{ route('siswa.lms.show', $matchedCourse->id) }}" style="padding: 0.35rem 0.75rem; border-radius: 0.65rem; background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; font-size: 0.84rem; font-weight: 700; text-decoration: none;">
                                            Modul
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="padding: 2.5rem 1rem; text-align: center; color: #64748b; background: #f8fafc; border-radius: 1rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📅</div>
                        <div style="font-weight: 800; color: #0f172a; font-size: 1.00rem;">Tidak ada jadwal pelajaran hari ini</div>
                        <div style="font-size: 0.98rem; margin-top: 0.2rem;">Gunakan waktu luangmu untuk mengulang materi di LMS atau berlatih bersama Pembda AI.</div>
                    </div>
                @endif
            </div>

            {{-- TAB 2: TUGAS & UJIAN --}}
            <div x-show="mainTab === 'tugas'" style="display: none; padding: 1.1rem 1.25rem;">
                @if($unifiedDeadlines->isNotEmpty())
                    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.85rem;">
                        <div style="display: flex; gap: 0.4rem;">
                            <button type="button" @click="taskFilter = 'all'" :style="taskFilter === 'all' ? 'background:#0f172a;color:#fff;' : 'background:#f1f5f9;color:#475569;'" style="padding:0.3rem 0.75rem;border-radius:9999px;font-size:0.7rem;font-weight:700;border:none;cursor:pointer;">Semua ({{ $unifiedDeadlines->count() }})</button>
                            <button type="button" @click="taskFilter = 'urgent'" :style="taskFilter === 'urgent' ? 'background:#d97706;color:#fff;' : 'background:#fef3c7;color:#92400e;'" style="padding:0.3rem 0.75rem;border-radius:9999px;font-size:0.7rem;font-weight:700;border:none;cursor:pointer;">Segera</button>
                            <button type="button" @click="taskFilter = 'late'" :style="taskFilter === 'late' ? 'background:#dc2626;color:#fff;' : 'background:#fee2e2;color:#991b1b;'" style="padding:0.3rem 0.75rem;border-radius:9999px;font-size:0.7rem;font-weight:700;border:none;cursor:pointer;">Terlambat</button>
                        </div>
                        <span style="font-size: 0.84rem; color: #64748b; font-weight: 600;">Prioritas teratas</span>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                        @foreach($unifiedDeadlines->take(8) as $item)
                            <div x-show="taskFilter === 'all' || (taskFilter === 'late' && {{ $item['is_late'] ? 'true' : 'false' }}) || (taskFilter === 'urgent' && {{ $item['is_urgent'] ? 'true' : 'false' }})"
                                 style="padding: 0.85rem 1rem; border-radius: 0.95rem; border: 1px solid {{ $item['is_late'] ? '#fecdd3' : '#e2e8f0' }}; border-left: 4px solid {{ $item['is_late'] ? '#e11d48' : ($item['is_urgent'] ? '#d97706' : '#2563eb') }}; background: {{ $item['is_late'] ? '#fff1f2' : '#f8fafc' }}; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem;">
                                <div style="min-width: 0; flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 0.45rem; margin-bottom: 0.2rem;">
                                        <span style="font-size: 0.98rem; font-weight: 800; padding: 0.15rem 0.5rem; border-radius: 9999px; background: {{ $item['type_badge_bg'] }}; color: {{ $item['type_badge_color'] }};">
                                            {{ $item['type_label'] }}
                                        </span>
                                        <span style="font-size: 0.80rem; font-weight: 700; color: {{ $item['status_color'] }};">
                                            {{ $item['status_label'] }}
                                        </span>
                                    </div>
                                    <div style="font-size: 1.08rem; font-weight: 800; color: #0f172a;">{{ $item['title'] }}</div>
                                    <div style="font-size: 0.85rem; color: #475569;">
                                        {{ $item['subject_name'] }} &bull; {{ $item['deadline_label'] }}
                                    </div>
                                </div>
                                <a href="{{ $item['action_url'] }}" style="padding: 0.45rem 0.9rem; border-radius: 0.7rem; background: {{ $item['type'] === 'cbt' ? '#ea580c' : ($item['type'] === 'quiz' ? '#7c3aed' : '#2563eb') }}; color: #fff; font-size: 0.84rem; font-weight: 800; text-decoration: none; white-space: nowrap;">
                                    {{ $item['action_label'] }} &rsaquo;
                                </a>
                            </div>
                        @endforeach
                    </div>

                    @if($unifiedDeadlines->count() > 8)
                    <div style="margin-top: 0.9rem; text-align: center;">
                        <a href="{{ route('siswa.lms.index') }}" style="display: inline-block; padding: 0.55rem 1.2rem; border-radius: 0.8rem; background: #0f172a; color: #ffffff; font-size: 0.98rem; font-weight: 800; text-decoration: none;">
                            Lihat {{ $unifiedDeadlines->count() - 8 }} Tugas Lainnya di LMS &rarr;
                        </a>
                    </div>
                    @endif
                @else
                    <div style="padding: 2.5rem 1rem; text-align: center; color: #15803d; background: #f0fdf4; border-radius: 1rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.4rem;">🎉</div>
                        <div style="font-weight: 800; font-size: 1.06rem;">Semua Tugas &amp; Ujian Tuntas!</div>
                        <div style="font-size: 0.98rem; margin-top: 0.2rem;">Tidak ada tugas LMS atau jadwal CBT mendesak saat ini.</div>
                    </div>
                @endif
            </div>

            {{-- TAB 3: KURSUS LMS --}}
            <div x-show="mainTab === 'lms'" style="display: none; padding: 1.1rem 1.25rem;">
                @if($courses->isNotEmpty())
                    <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                        @foreach($courses->take(6) as $course)
                            @php $prog = $courseProgress[$course->id] ?? 0; @endphp
                            <a href="{{ route('siswa.lms.show', $course->id) }}" style="padding: 0.85rem 1rem; border-radius: 0.95rem; border: 1px solid #e2e8f0; border-left: 4px solid #4f46e5; background: #f8fafc; text-decoration: none; display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                                <div style="min-width: 0; flex: 1;">
                                    <div style="font-size: 1.08rem; font-weight: 800; color: #0f172a;">{{ $course->course_name ?? $course->name }}</div>
                                    <div style="font-size: 0.84rem; color: #475569; margin-top: 0.1rem;">{{ $course->teacher?->user?->name ?? 'Guru Mapel' }}</div>
                                    <div style="width: 100%; height: 6px; background: #e2e8f0; border-radius: 9999px; margin-top: 0.45rem; overflow: hidden;">
                                        <div style="width: {{ min(100, $prog) }}%; height: 100%; background: #4f46e5; border-radius: 9999px;"></div>
                                    </div>
                                </div>
                                <span style="font-size: 1.08rem; font-weight: 800; color: #4338ca; background: #eef2ff; padding: 0.25rem 0.55rem; border-radius: 9999px;">{{ $prog }}%</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div style="padding: 2rem 1rem; text-align: center; color: #64748b; font-size: 1.04rem;">
                        Belum ada kursus LMS yang terdaftar.
                    </div>
                @endif
            </div>
        </div>

        {{-- KOLOM KANAN: OSIS, PEMBDA AI, DNA & MENU CEPAT --}}
        <div style="display: flex; flex-direction: column; gap: 1.1rem;">

            {{-- Widget Pemilihan OSIS --}}
            @include('components.osis-campaign-widget', ['variant' => 'student'])

            {{-- Kartu Pembda AI Tutor (Deep Indigo Card — Focal Point Kanan + AI Daily Briefing) --}}
            <div x-data="{
                    briefing: '',
                    loadingBriefing: true,
                    async loadBriefing(refresh = false) {
                        this.loadingBriefing = true;
                        try {
                            const url = '{{ route('siswa.ai.briefing') }}' + (refresh ? '?refresh=1' : '');
                            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                            const data = await res.json();
                            if (data.success && data.briefing) {
                                this.briefing = data.briefing;
                            }
                        } catch (e) {}
                        this.loadingBriefing = false;
                    }
                 }"
                 x-init="loadBriefing(false)"
                 style="border-radius: 1.25rem; padding: 1.35rem; background: linear-gradient(135deg, #312e81 0%, #4338ca 100%); color: #ffffff; box-shadow: 0 8px 20px -4px rgba(49, 46, 129, 0.35);">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 0.85rem;">
                    <div style="display: flex; align-items: center; gap: 0.7rem;">
                        <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.8rem; background: #fbbf24; color: #0f172a; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div>
                            <div style="font-size: 1.04rem; font-weight: 800; color: #ffffff;">Pembda AI Tutor</div>
                            <div style="font-size: 0.84rem; color: #c7d2fe; font-weight: 600;">Ringkasan cerdas &amp; asisten belajar</div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                        <button type="button" @click="loadBriefing(true)" :disabled="loadingBriefing" title="Perbarui Ringkasan AI" style="background: rgba(255,255,255,0.14); border: none; color: #fde68a; width: 1.85rem; height: 1.85rem; border-radius: 0.55rem; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-sync-alt" :class="{ 'fa-spin': loadingBriefing }" style="font-size: 1.04rem;"></i>
                        </button>
                        <a href="{{ route('siswa.ai.index') }}" style="font-size: 1.08rem; font-weight: 800; color: #fde68a; text-decoration: none;">Buka &rarr;</a>
                    </div>
                </div>

                {{-- AI Daily Study Briefing Box --}}
                <div style="margin-bottom: 0.9rem; padding: 0.75rem 0.9rem; border-radius: 0.85rem; background: rgba(15, 23, 42, 0.32); border: 1px solid rgba(255, 255, 255, 0.14); font-size: 0.98rem; line-height: 1.5; color: #e0e7ff;">
                    <template x-if="loadingBriefing">
                        <span style="color: #c7d2fe;"><i class="fas fa-sparkles" style="color: #fbbf24;"></i> Menyusun ringkasan belajar hari ini...</span>
                    </template>
                    <template x-if="!loadingBriefing && briefing">
                        <div>
                            <span style="color: #fbbf24; font-weight: 800; font-size: 0.80rem; text-transform: uppercase; display: block; margin-bottom: 0.2rem;">✨ Ringkasan Cerdas Hari Ini</span>
                            <span x-text="briefing"></span>
                        </div>
                    </template>
                    <template x-if="!loadingBriefing && !briefing">
                        <span>Siap membantumu memahami materi, mengecek tugas LMS, dan membahas foto soal.</span>
                    </template>
                </div>

                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    <a href="{{ route('siswa.ai.index', ['mode' => 'lms_assistant', 'prompt' => 'Tugas LMS atau kuis apa saja yang belum saya kerjakan saat ini?']) }}"
                       style="padding: 0.5rem 0.8rem; border-radius: 0.75rem; background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.22); color: #ffffff; font-size: 0.84rem; font-weight: 700; text-decoration: none;">
                        📋 Cek Tugas Belum Selesai
                    </a>
                    <a href="{{ route('siswa.ai.index', ['mode' => 'tutor']) }}"
                       style="padding: 0.5rem 0.8rem; border-radius: 0.75rem; background: #fbbf24; color: #0f172a; font-size: 0.84rem; font-weight: 800; text-decoration: none;">
                        📸 Tanya / Foto Soal
                    </a>
                </div>
            </div>

            {{-- Ringkasan DNA Akademik 360° --}}
            @if(isset($dnaAnalysis))
            <div style="border-radius: 1.25rem; padding: 1.15rem 1.25rem; background: linear-gradient(135deg, #f5f3ff 0%, #ffffff 75%); border: 1px solid #ddd6fe; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);">
                <div>
                    <div style="font-size: 0.80rem; font-weight: 800; color: #7c3aed; text-transform: uppercase;">DNA Akademik 360°</div>
                    <div style="font-size: 1.00rem; font-weight: 800; color: #0f172a; margin-top: 0.15rem;">
                        {{ $dnaAnalysis['dominant_archetype']['title'] ?? ($dnaAnalysis['profile_type'] ?? 'Profil Potensi Siswa') }}
                    </div>
                    <div style="font-size: 0.84rem; color: #6d28d9; font-weight: 500;">Pemetaan minat, bakat &amp; gaya belajar</div>
                </div>
                <a href="{{ route('siswa.dna.index') }}" style="padding: 0.5rem 0.9rem; border-radius: 0.75rem; background: #7c3aed; color: #ffffff; font-size: 0.84rem; font-weight: 800; text-decoration: none; white-space: nowrap;">
                    Detail &rarr;
                </a>
            </div>
            @endif

            {{-- Ekskul, Riwayat Presensi & Menu Cepat --}}
            @php
                $myEkskuls = $student->extracurricularMembers()->where('status', 'approved')->with('extracurricular')->get();
            @endphp
            <div class="sd-card" style="overflow: hidden;">
                <div style="padding: 0.75rem 1rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; flex-wrap: wrap; gap: 0.35rem;">
                    <button type="button" @click="rightTab = 'ekskul'" class="sd-tab-btn" :class="{ 'is-active': rightTab === 'ekskul' }">Ekskul ({{ $myEkskuls->count() }})</button>
                    <button type="button" @click="rightTab = 'presensi'" class="sd-tab-btn" :class="{ 'is-active': rightTab === 'presensi' }">Riwayat Presensi</button>
                    <button type="button" @click="rightTab = 'cepat'" class="sd-tab-btn" :class="{ 'is-active': rightTab === 'cepat' }">Menu Cepat</button>
                </div>

                <div x-show="rightTab === 'ekskul'" style="padding: 0.9rem 1rem;">
                    @if($myEkskuls->isNotEmpty())
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            @foreach($myEkskuls->take(4) as $m)
                                @php $e = $m->extracurricular; @endphp
                                @if($e)
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.55rem 0.75rem; border-radius: 0.75rem; background: #f8fafc; border: 1px solid #e2e8f0;">
                                    <span style="font-size: 0.98rem; font-weight: 700; color: #0f172a;">{{ $e->display_icon }} {{ $e->name }}</span>
                                    <span style="font-size: 0.80rem; font-weight: 800; color: #047857; background: #d1fae5; padding: 0.15rem 0.5rem; border-radius: 9999px;">Aktif</span>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div style="font-size: 0.98rem; color: #64748b; text-align: center; padding: 0.75rem 0;">
                            Belum bergabung ekstrakurikuler. <a href="{{ route('siswa.ekskul.index') }}" style="color: #4f46e5; font-weight: 700;">Lihat Katalog &rarr;</a>
                        </div>
                    @endif
                </div>

                <div x-show="rightTab === 'presensi'" style="display: none; padding: 0.9rem 1rem;">
                    @if(isset($attendanceHistory) && $attendanceHistory->isNotEmpty())
                        <div style="display: flex; flex-direction: column; gap: 0.45rem;">
                            @foreach($attendanceHistory->take(4) as $att)
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0.75rem; border-radius: 0.75rem; background: #f8fafc; border: 1px solid #e2e8f0; font-size: 1.08rem;">
                                    <span style="font-weight: 700; color: #0f172a;">{{ \Carbon\Carbon::parse($att->date)->translatedFormat('d M Y') }}</span>
                                    <span style="font-weight: 800; color: {{ $att->status === 'hadir' ? '#047857' : '#b45309' }}; background: {{ $att->status === 'hadir' ? '#d1fae5' : '#fef3c7' }}; padding: 0.15rem 0.5rem; border-radius: 9999px; text-transform: uppercase; font-size: 0.98rem;">{{ $att->status }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="font-size: 0.98rem; color: #64748b; text-align: center; padding: 0.75rem 0;">Belum ada riwayat presensi.</div>
                    @endif
                </div>

                <div x-show="rightTab === 'cepat'" style="display: none; padding: 0.9rem 1rem;">
                    <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.55rem;">
                        <a href="{{ route('siswa.cbt.index') }}" style="padding: 0.65rem; border-radius: 0.8rem; background: #eff6ff; border: 1px solid #bfdbfe; text-decoration: none; font-size: 1.08rem; font-weight: 800; color: #1e40af; text-align: center;">🖥️ Portal CBT</a>
                        <a href="{{ route('siswa.nilai') }}" style="padding: 0.65rem; border-radius: 0.8rem; background: #ecfdf5; border: 1px solid #a7f3d0; text-decoration: none; font-size: 1.08rem; font-weight: 800; color: #065f46; text-align: center;">📊 Nilai &amp; Rapor</a>
                        <a href="{{ route('simlab.index') }}" style="padding: 0.65rem; border-radius: 0.8rem; background: #ecfeff; border: 1px solid #a5f3fc; text-decoration: none; font-size: 1.08rem; font-weight: 800; color: #155e75; text-align: center;">🧪 Simulator Lab</a>
                        <a href="{{ route('siswa.konseling') }}" style="padding: 0.65rem; border-radius: 0.8rem; background: #fff1f2; border: 1px solid #fecdd3; text-decoration: none; font-size: 1.08rem; font-weight: 800; color: #9f1239; text-align: center;">💬 BK &amp; Prestasi</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('live-class-countdown');
    const clock = document.getElementById('live-countdown-clock');
    if (!el || !clock) return;

    const target = parseInt(el.dataset.target, 10);
    const mode = el.dataset.mode;

    function updateCountdown() {
        const diff = Math.floor((target - Date.now()) / 1000);
        if (diff <= 0) {
            clock.textContent = mode === 'current' ? 'Selesai' : 'Dimulai';
            return;
        }
        const hrs = Math.floor(diff / 3600);
        const mins = Math.floor((diff % 3600) / 60);
        const secs = diff % 60;
        const prefix = mode === 'current' ? 'Sisa ' : 'Mulai ';
        if (hrs > 0) {
            clock.textContent = prefix + String(hrs).padStart(2, '0') + ':' + String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        } else {
            clock.textContent = prefix + String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        }
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);
});
</script>
@endsection
