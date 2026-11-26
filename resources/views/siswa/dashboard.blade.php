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
        border: 1px solid #e2e8f0;
        border-radius: 1.25rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }
    .sd-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
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
        padding: 0.55rem 0.95rem;
        border-radius: 0.75rem;
        font-size: 0.78rem;
        font-weight: 700;
        transition: all 0.15s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        border: 1px solid transparent;
    }
    .sd-tab-btn.is-active {
        background: #eef2ff;
        color: #4338ca;
        border-color: #c7d2fe;
    }
    .sd-tab-btn:not(.is-active) {
        color: #64748b;
        background: transparent;
    }
    .sd-tab-btn:not(.is-active):hover {
        background: #f8fafc;
        color: #0f172a;
    }
</style>

<div class="sd-wrap" x-data="{ mainTab: '{{ $groupedTodaySchedules->count() > 0 ? 'jadwal' : 'tugas' }}', taskFilter: 'all', rightTab: 'ekskul' }">

    {{-- 1. HERO WELCOME & STATUS PRESENSI TERPADU (RINGKAS & BERSIH) --}}
    <div class="sd-card" style="padding: 1.35rem 1.5rem;">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.25rem;">
            <div style="display: flex; align-items: center; gap: 1rem; min-width: 0; flex: 1;">
                <div style="position: relative; flex-shrink: 0;">
                    <div style="width: 3.75rem; height: 3.75rem; border-radius: 1rem; overflow: hidden; border: 2px solid #e2e8f0; background: #f8fafc;">
                        <img src="{{ $student->photo_url }}" style="width: 100%; height: 100%; object-fit: cover;" alt="{{ $student->full_name }}" onerror="this.onerror=null; this.src='{{ asset('images/default-student.jpg') }}';">
                    </div>
                    <span style="position: absolute; bottom: -2px; right: -2px; width: 1rem; height: 1rem; border-radius: 9999px; background: #10b981; border: 2px solid #fff;" title="Aktif"></span>
                </div>

                <div style="min-width: 0;">
                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.45rem; margin-bottom: 0.25rem;">
                        @if($classroom)
                        <span style="padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.68rem; font-weight: 800; background: #eef2ff; color: #4338ca;">
                            {{ $classroom->class_name }}
                        </span>
                        @endif
                        @if($activeYear)
                        <span style="padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.68rem; font-weight: 700; background: #f1f5f9; color: #475569;">
                            TP {{ $activeYear->year }}
                        </span>
                        @endif
                        <span style="font-size: 0.72rem; color: #64748b; font-weight: 600;">
                            {{ now()->translatedFormat('l, d F Y') }}
                        </span>
                    </div>
                    <h1 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; line-height: 1.25; margin: 0;">
                        Hai, {{ explode(' ', trim($student->full_name))[0] }}!
                    </h1>
                    <p style="font-size: 0.78rem; color: #64748b; font-weight: 500; margin-top: 0.15rem;">
                        {{ $student->school->name ?? 'Perguruan Pembda' }} &bull; NISN {{ $student->nisn ?: '-' }}
                    </p>
                </div>
            </div>

            {{-- Quick Action Buttons --}}
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.6rem; flex-shrink: 0;">
                @if(!$todayAttendance || !$todayAttendance->time_out)
                <a href="{{ route('siswa.absensi') }}"
                   style="padding: 0.6rem 1rem; border-radius: 0.85rem; background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; font-size: 0.76rem; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 0.45rem;">
                    <i class="fas fa-fingerprint"></i>
                    <span>{{ !$todayAttendance ? 'Absen Masuk' : 'Absen Pulang (Masuk ' . date('H:i', strtotime($todayAttendance->time_in)) . ')' }}</span>
                </a>
                @else
                <span style="padding: 0.6rem 0.9rem; border-radius: 0.85rem; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 0.74rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <i class="fas fa-check-circle"></i> Presensi Lengkap
                </span>
                @endif

                <a href="{{ route('siswa.ai.index') }}"
                   style="padding: 0.6rem 1rem; border-radius: 0.85rem; background: #4f46e5; color: #ffffff; font-size: 0.76rem; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 0.45rem; box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);">
                    <i class="fas fa-robot"></i>
                    <span>Pembda AI</span>
                </a>

                @php
                    $__circleProfile = \App\Models\CircleProfile::where('user_id', Auth::id())->first();
                    $__circleUsername = $__circleProfile?->username ?? Auth::user()->username;
                @endphp
                <a href="{{ route('circle.profile', ['username' => $__circleUsername]) }}"
                   style="padding: 0.6rem 0.95rem; border-radius: 0.85rem; background: #f8fafc; color: #0f172a; border: 1px solid #cbd5e1; font-size: 0.76rem; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <i class="fas fa-bolt" style="color: #f59e0b;"></i>
                    <span>Circle</span>
                </a>
            </div>
        </div>
    </div>

    {{-- 2. ALERT LIVE PEMBDATEMU (HANYA JIKA ADA KELAS LIVE) --}}
    @if(isset($activeMeetings) && $activeMeetings->isNotEmpty())
        @foreach($activeMeetings as $meetingCourse)
        <div class="sd-card" style="padding: 1rem 1.35rem; background: linear-gradient(90deg, #fff1f2 0%, #ffffff 100%); border-color: #fda4af; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.9rem;">
                <div style="width: 2.75rem; height: 2.75rem; border-radius: 0.85rem; background: #e11d48; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                    <i class="fas fa-video"></i>
                </div>
                <div>
                    <span style="font-size: 0.65rem; font-weight: 900; color: #be123c; text-transform: uppercase; letter-spacing: 0.05em;">● KELAS LIVE PEMBDATEMU AKTIF</span>
                    <div style="font-size: 0.95rem; font-weight: 800; color: #0f172a;">{{ $meetingCourse->course_name ?? $meetingCourse->name }}</div>
                    <div style="font-size: 0.74rem; color: #64748b;">Guru: {{ $meetingCourse->teacher?->user?->name ?? 'Guru Pengajar' }}</div>
                </div>
            </div>
            <a href="{{ route('siswa.lms.meeting.join', $meetingCourse->id) }}" style="padding: 0.6rem 1.15rem; border-radius: 0.85rem; background: #e11d48; color: #fff; font-size: 0.76rem; font-weight: 800; text-decoration: none;">
                Masuk Kelas Live &rarr;
            </a>
        </div>
        @endforeach
    @endif

    {{-- 3. 4 KPI RINGKAS MINIMALIS --}}
    <div class="sd-kpi-grid">
        <a href="{{ route('siswa.absensi') }}" class="sd-card" style="padding: 1.1rem 1.25rem; text-decoration: none; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
            <div>
                <div style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Presensi</div>
                <div style="font-size: 1.5rem; font-weight: 900; color: #0f172a; line-height: 1.2; margin-top: 0.15rem;">{{ $attendanceData['percentage'] }}%</div>
                <div style="font-size: 0.72rem; color: #64748b; margin-top: 0.15rem;">{{ $attendanceData['present'] }}/{{ $attendanceData['total'] }} hari</div>
            </div>
            <div style="width: 2.75rem; height: 2.75rem; border-radius: 0.85rem; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                <i class="fas fa-user-check"></i>
            </div>
        </a>

        <a href="{{ route('siswa.nilai') }}" class="sd-card" style="padding: 1.1rem 1.25rem; text-decoration: none; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
            <div>
                <div style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Rata-Rata Nilai</div>
                <div style="font-size: 1.5rem; font-weight: 900; color: #0f172a; line-height: 1.2; margin-top: 0.15rem;">{{ number_format($avgScore, 1) }}</div>
                <div style="font-size: 0.72rem; color: #64748b; margin-top: 0.15rem;">Semester aktif</div>
            </div>
            <div style="width: 2.75rem; height: 2.75rem; border-radius: 0.85rem; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                <i class="fas fa-chart-line"></i>
            </div>
        </a>

        <div class="sd-card" style="padding: 1.1rem 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
            <div>
                <div style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Poin Reputasi</div>
                <div style="font-size: 1.5rem; font-weight: 900; color: #0f172a; line-height: 1.2; margin-top: 0.15rem;">{{ number_format($reputation->total_points ?? 0) }}</div>
                <div style="font-size: 0.72rem; color: #d97706; font-weight: 700; margin-top: 0.15rem;">Peringkat #{{ $rank ?? 1 }} &bull; {{ $reputation->level_name ?? 'Pemula' }}</div>
            </div>
            <div style="width: 2.75rem; height: 2.75rem; border-radius: 0.85rem; background: #fffbeb; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                <i class="fas fa-crown"></i>
            </div>
        </div>

        <a href="{{ route('siswa.tagihan') }}" class="sd-card" style="padding: 1.1rem 1.25rem; text-decoration: none; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
            <div>
                <div style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Biaya Pendidikan</div>
                <div style="font-size: 1.2rem; font-weight: 900; color: {{ $totalOutstanding > 0 ? '#be123c' : '#059669' }}; line-height: 1.35; margin-top: 0.2rem;">
                    {{ $totalOutstanding > 0 ? 'Belum Lunas' : 'Lunas' }}
                </div>
                <div style="font-size: 0.72rem; color: #64748b; margin-top: 0.15rem;">
                    {{ $totalOutstanding > 0 ? 'Cek rincian tagihan' : 'Bebas tunggakan' }}
                </div>
            </div>
            <div style="width: 2.75rem; height: 2.75rem; border-radius: 0.85rem; background: {{ $totalOutstanding > 0 ? '#fff1f2' : '#ecfdf5' }}; color: {{ $totalOutstanding > 0 ? '#e11d48' : '#059669' }}; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                <i class="fas fa-wallet"></i>
            </div>
        </a>
    </div>

    {{-- 4. MAIN BENTO WORKSPACE (2 KOLOM ASIMETRIS) --}}
    <div class="sd-bento-grid">

        {{-- KOLOM KIRI: FOKUS BELAJAR HARI INI (1 CARD DENGAN 3 TAB) --}}
        <div class="sd-card" style="overflow: hidden;">
            {{-- Tab Header --}}
            <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem;">
                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.35rem;">
                    <button type="button" @click="mainTab = 'jadwal'" class="sd-tab-btn" :class="{ 'is-active': mainTab === 'jadwal' }">
                        <i class="fas fa-calendar-day"></i>
                        <span>Jadwal Hari Ini</span>
                        <span style="font-size: 0.68rem; padding: 0.1rem 0.45rem; border-radius: 9999px; background: #e2e8f0; color: #0f172a;">{{ $groupedTodaySchedules->count() }}</span>
                    </button>
                    <button type="button" @click="mainTab = 'tugas'" class="sd-tab-btn" :class="{ 'is-active': mainTab === 'tugas' }">
                        <i class="fas fa-tasks"></i>
                        <span>Tugas &amp; Ujian</span>
                        @if($unifiedDeadlines->isNotEmpty())
                        <span style="font-size: 0.68rem; padding: 0.1rem 0.45rem; border-radius: 9999px; background: #fee2e2; color: #b91c1c;">{{ $unifiedDeadlines->count() }}</span>
                        @endif
                    </button>
                    <button type="button" @click="mainTab = 'lms'" class="sd-tab-btn" :class="{ 'is-active': mainTab === 'lms' }">
                        <i class="fas fa-book-open"></i>
                        <span>Kursus LMS</span>
                        <span style="font-size: 0.68rem; padding: 0.1rem 0.45rem; border-radius: 9999px; background: #e2e8f0; color: #0f172a;">{{ $courses->count() }}</span>
                    </button>
                </div>

                <a x-show="mainTab === 'jadwal'" href="{{ route('siswa.jadwal') }}" style="font-size: 0.74rem; font-weight: 700; color: #4f46e5; text-decoration: none;">Jadwal Lengkap &rarr;</a>
                <a x-show="mainTab === 'tugas'" href="{{ route('siswa.lms.index') }}" style="font-size: 0.74rem; font-weight: 700; color: #4f46e5; text-decoration: none;">Semua Tugas &rarr;</a>
                <a x-show="mainTab === 'lms'" href="{{ route('siswa.lms.index') }}" style="font-size: 0.74rem; font-weight: 700; color: #4f46e5; text-decoration: none;">Buka LMS &rarr;</a>
            </div>

            {{-- TAB 1: JADWAL HARI INI (TIMELINE LIST RAPI) --}}
            <div x-show="mainTab === 'jadwal'" style="padding: 1rem 1.25rem;">
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
                            <div style="padding: 0.85rem 1rem; border-radius: 0.95rem; border: 1px solid {{ $isCurrent ? '#6ee7b7' : ($isNext ? '#fcd34d' : '#e2e8f0') }}; background: {{ $isCurrent ? '#ecfdf5' : ($isNext ? '#fffbeb' : '#ffffff') }}; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem;">
                                <div style="display: flex; align-items: center; gap: 0.85rem; min-width: 0; flex: 1;">
                                    <div style="padding: 0.35rem 0.65rem; border-radius: 0.65rem; background: {{ $isCurrent ? '#059669' : '#0f172a' }}; color: #fff; font-size: 0.72rem; font-weight: 800; font-family: monospace; white-space: nowrap;">
                                        {{ $sStart }} - {{ $sEnd }}
                                    </div>
                                    <div style="min-width: 0;">
                                        @foreach($schedulesAtTime as $sch)
                                            <div style="font-size: 0.88rem; font-weight: 800; color: #0f172a; line-height: 1.3;">
                                                {{ $sch->teachingAssignment?->subject?->subject_name ?? $sch->teachingAssignment?->subject?->name ?? 'Mata Pelajaran' }}
                                            </div>
                                            <div style="font-size: 0.74rem; color: #64748b; font-weight: 500;">
                                                {{ $sch->teachingAssignment?->teacher?->user?->name ?? $sch->teachingAssignment?->teacher?->full_name ?? '-' }}
                                                @if($sch->classroom) &bull; {{ $sch->classroom->class_name }} @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    @if($isCurrent)
                                        <span style="padding: 0.25rem 0.6rem; border-radius: 9999px; font-size: 0.68rem; font-weight: 800; background: #10b981; color: #fff;">Berlangsung</span>
                                    @elseif($isNext)
                                        <span style="padding: 0.25rem 0.6rem; border-radius: 9999px; font-size: 0.68rem; font-weight: 800; background: #f59e0b; color: #fff;">Berikutnya</span>
                                    @elseif($isPast)
                                        <span style="padding: 0.25rem 0.6rem; border-radius: 9999px; font-size: 0.68rem; font-weight: 700; background: #f1f5f9; color: #64748b;">Selesai</span>
                                    @else
                                        <span style="padding: 0.25rem 0.6rem; border-radius: 9999px; font-size: 0.68rem; font-weight: 700; background: #eef2ff; color: #4338ca;">Terjadwal</span>
                                    @endif

                                    @php
                                        $firstSch = $schedulesAtTime->first();
                                        $subId = $firstSch?->teachingAssignment?->subject_id;
                                        $matchedCourse = $courses->firstWhere('subject_id', $subId);
                                    @endphp
                                    @if($matchedCourse)
                                        <a href="{{ route('siswa.lms.show', $matchedCourse->id) }}" style="padding: 0.35rem 0.7rem; border-radius: 0.65rem; background: #f8fafc; border: 1px solid #cbd5e1; color: #0f172a; font-size: 0.72rem; font-weight: 700; text-decoration: none;">
                                            Modul
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="padding: 2.5rem 1rem; text-align: center; color: #64748b;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📅</div>
                        <div style="font-weight: 700; color: #0f172a; font-size: 0.9rem;">Tidak ada jadwal pelajaran hari ini</div>
                        <div style="font-size: 0.78rem; margin-top: 0.2rem;">Gunakan waktu luangmu untuk mengulang materi di LMS atau berlatih bersama Pembda AI.</div>
                    </div>
                @endif
            </div>

            {{-- TAB 2: TUGAS & UJIAN (MAKSIMAL 6 PRIORITAS AGAR TIDAK SCROLL PANJANG) --}}
            <div x-show="mainTab === 'tugas'" style="display: none; padding: 1rem 1.25rem;">
                @if($unifiedDeadlines->isNotEmpty())
                    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.85rem;">
                        <div style="display: flex; gap: 0.4rem;">
                            <button type="button" @click="taskFilter = 'all'" :style="taskFilter === 'all' ? 'background:#0f172a;color:#fff;' : 'background:#f1f5f9;color:#475569;'" style="padding:0.3rem 0.7rem;border-radius:9999px;font-size:0.7rem;font-weight:700;border:none;cursor:pointer;">Semua ({{ $unifiedDeadlines->count() }})</button>
                            <button type="button" @click="taskFilter = 'urgent'" :style="taskFilter === 'urgent' ? 'background:#d97706;color:#fff;' : 'background:#fef3c7;color:#92400e;'" style="padding:0.3rem 0.7rem;border-radius:9999px;font-size:0.7rem;font-weight:700;border:none;cursor:pointer;">Segera</button>
                            <button type="button" @click="taskFilter = 'late'" :style="taskFilter === 'late' ? 'background:#dc2626;color:#fff;' : 'background:#fee2e2;color:#991b1b;'" style="padding:0.3rem 0.7rem;border-radius:9999px;font-size:0.7rem;font-weight:700;border:none;cursor:pointer;">Terlambat</button>
                        </div>
                        <span style="font-size: 0.72rem; color: #64748b;">Menampilkan prioritas teratas</span>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                        @foreach($unifiedDeadlines->take(8) as $item)
                            <div x-show="taskFilter === 'all' || (taskFilter === 'late' && {{ $item['is_late'] ? 'true' : 'false' }}) || (taskFilter === 'urgent' && {{ $item['is_urgent'] ? 'true' : 'false' }})"
                                 style="padding: 0.85rem 1rem; border-radius: 0.95rem; border: 1px solid {{ $item['is_late'] ? '#fecdd3' : '#e2e8f0' }}; background: {{ $item['is_late'] ? '#fff1f2' : '#ffffff' }}; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem;">
                                <div style="min-width: 0; flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 0.45rem; margin-bottom: 0.2rem;">
                                        <span style="font-size: 0.66rem; font-weight: 800; padding: 0.15rem 0.5rem; border-radius: 9999px; background: {{ $item['type_badge_bg'] }}; color: {{ $item['type_badge_color'] }};">
                                            {{ $item['type_label'] }}
                                        </span>
                                        <span style="font-size: 0.68rem; font-weight: 700; color: {{ $item['status_color'] }};">
                                            {{ $item['status_label'] }}
                                        </span>
                                    </div>
                                    <div style="font-size: 0.86rem; font-weight: 800; color: #0f172a;">{{ $item['title'] }}</div>
                                    <div style="font-size: 0.73rem; color: #64748b;">
                                        {{ $item['subject_name'] }} &bull; {{ $item['deadline_label'] }}
                                    </div>
                                </div>
                                <a href="{{ $item['action_url'] }}" style="padding: 0.45rem 0.85rem; border-radius: 0.7rem; background: {{ $item['type'] === 'cbt' ? '#ea580c' : ($item['type'] === 'quiz' ? '#7c3aed' : '#2563eb') }}; color: #fff; font-size: 0.72rem; font-weight: 800; text-decoration: none; white-space: nowrap;">
                                    {{ $item['action_label'] }} &rsaquo;
                                </a>
                            </div>
                        @endforeach
                    </div>

                    @if($unifiedDeadlines->count() > 8)
                    <div style="margin-top: 0.9rem; text-align: center;">
                        <a href="{{ route('siswa.lms.index') }}" style="display: inline-block; padding: 0.55rem 1.2rem; border-radius: 0.8rem; background: #f8fafc; border: 1px solid #cbd5e1; color: #0f172a; font-size: 0.76rem; font-weight: 800; text-decoration: none;">
                            Lihat {{ $unifiedDeadlines->count() - 8 }} Tugas Lainnya di LMS &rarr;
                        </a>
                    </div>
                    @endif
                @else
                    <div style="padding: 2.5rem 1rem; text-align: center; color: #15803d; background: #f0fdf4; border-radius: 1rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.4rem;">🎉</div>
                        <div style="font-weight: 800; font-size: 0.95rem;">Semua Tugas &amp; Ujian Tuntas!</div>
                        <div style="font-size: 0.78rem; margin-top: 0.2rem;">Tidak ada tugas LMS atau jadwal CBT mendesak saat ini.</div>
                    </div>
                @endif
            </div>

            {{-- TAB 3: KURSUS LMS --}}
            <div x-show="mainTab === 'lms'" style="display: none; padding: 1rem 1.25rem;">
                @if($courses->isNotEmpty())
                    <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                        @foreach($courses->take(6) as $course)
                            @php $prog = $courseProgress[$course->id] ?? 0; @endphp
                            <a href="{{ route('siswa.lms.show', $course->id) }}" style="padding: 0.85rem 1rem; border-radius: 0.95rem; border: 1px solid #e2e8f0; background: #ffffff; text-decoration: none; display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                                <div style="min-width: 0; flex: 1;">
                                    <div style="font-size: 0.86rem; font-weight: 800; color: #0f172a;">{{ $course->course_name ?? $course->name }}</div>
                                    <div style="font-size: 0.72rem; color: #64748b; margin-top: 0.1rem;">{{ $course->teacher?->user?->name ?? 'Guru Mapel' }}</div>
                                    <div style="width: 100%; height: 5px; background: #f1f5f9; border-radius: 9999px; margin-top: 0.45rem; overflow: hidden;">
                                        <div style="width: {{ min(100, $prog) }}%; height: 100%; background: #4f46e5; border-radius: 9999px;"></div>
                                    </div>
                                </div>
                                <span style="font-size: 0.74rem; font-weight: 800; color: #4f46e5;">{{ $prog }}%</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div style="padding: 2rem 1rem; text-align: center; color: #64748b; font-size: 0.82rem;">
                        Belum ada kursus LMS yang terdaftar.
                    </div>
                @endif
            </div>
        </div>

        {{-- KOLOM KANAN: OSIS, PEMBDA AI, DNA & MENU CEPAT --}}
        <div style="display: flex; flex-direction: column; gap: 1.1rem;">

            {{-- Widget Pemilihan OSIS (Otomatis hilang jika tidak ada event aktif) --}}
            @include('components.osis-campaign-widget', ['variant' => 'student'])

            {{-- Kartu Pembda AI Tutor --}}
            <div class="sd-card" style="padding: 1.25rem; background: linear-gradient(135deg, #eef2ff 0%, #ffffff 100%); border-color: #c7d2fe;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <div style="width: 2.35rem; height: 2.35rem; border-radius: 0.75rem; background: #4f46e5; color: #fff; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div>
                            <div style="font-size: 0.88rem; font-weight: 800; color: #0f172a;">Pembda AI Tutor</div>
                            <div style="font-size: 0.72rem; color: #4338ca; font-weight: 600;">Asisten belajar &amp; foto soal</div>
                        </div>
                    </div>
                    <a href="{{ route('siswa.ai.index') }}" style="font-size: 0.74rem; font-weight: 800; color: #4f46e5; text-decoration: none;">Buka &rarr;</a>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    <a href="{{ route('siswa.ai.index', ['mode' => 'lms_assistant', 'prompt' => 'Tugas LMS atau kuis apa saja yang belum saya kerjakan saat ini?']) }}"
                       style="padding: 0.45rem 0.75rem; border-radius: 0.75rem; background: #ffffff; border: 1px solid #c7d2fe; color: #312e81; font-size: 0.72rem; font-weight: 700; text-decoration: none;">
                        📋 Cek Tugas Belum Selesai
                    </a>
                    <a href="{{ route('siswa.ai.index', ['mode' => 'tutor']) }}"
                       style="padding: 0.45rem 0.75rem; border-radius: 0.75rem; background: #4f46e5; color: #ffffff; font-size: 0.72rem; font-weight: 700; text-decoration: none;">
                        📸 Tanya / Foto Soal
                    </a>
                </div>
            </div>

            {{-- Ringkasan DNA Akademik 360° --}}
            @if(isset($dnaAnalysis))
            <div class="sd-card" style="padding: 1.15rem 1.25rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                <div>
                    <div style="font-size: 0.68rem; font-weight: 800; color: #7c3aed; text-transform: uppercase;">DNA Akademik 360°</div>
                    <div style="font-size: 0.9rem; font-weight: 800; color: #0f172a; margin-top: 0.15rem;">
                        {{ $dnaAnalysis['dominant_archetype']['title'] ?? ($dnaAnalysis['profile_type'] ?? 'Profil Potensi Siswa') }}
                    </div>
                    <div style="font-size: 0.72rem; color: #64748b;">Lihat pemetaan minat, bakat &amp; gaya belajar</div>
                </div>
                <a href="{{ route('siswa.dna.index') }}" style="padding: 0.45rem 0.85rem; border-radius: 0.75rem; background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; font-size: 0.72rem; font-weight: 800; text-decoration: none; white-space: nowrap;">
                    Detail &rarr;
                </a>
            </div>
            @endif

            {{-- Ekskul & Presensi Terakhir (Tabbed Compact) --}}
            @php
                $myEkskuls = $student->extracurricularMembers()->where('status', 'approved')->with('extracurricular')->get();
            @endphp
            <div class="sd-card" style="overflow: hidden;">
                <div style="padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; display: flex; gap: 0.35rem;">
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
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0.7rem; border-radius: 0.75rem; background: #f8fafc; border: 1px solid #e2e8f0;">
                                    <span style="font-size: 0.78rem; font-weight: 700; color: #0f172a;">{{ $e->display_icon }} {{ $e->name }}</span>
                                    <span style="font-size: 0.68rem; font-weight: 700; color: #059669;">Aktif</span>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div style="font-size: 0.76rem; color: #64748b; text-align: center; padding: 0.75rem 0;">
                            Belum bergabung ekstrakurikuler. <a href="{{ route('siswa.ekskul.index') }}" style="color: #4f46e5; font-weight: 700;">Lihat Katalog &rarr;</a>
                        </div>
                    @endif
                </div>

                <div x-show="rightTab === 'presensi'" style="display: none; padding: 0.9rem 1rem;">
                    @if(isset($attendanceHistory) && $attendanceHistory->isNotEmpty())
                        <div style="display: flex; flex-direction: column; gap: 0.45rem;">
                            @foreach($attendanceHistory->take(4) as $att)
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.45rem 0.7rem; border-radius: 0.75rem; background: #f8fafc; border: 1px solid #e2e8f0; font-size: 0.74rem;">
                                    <span style="font-weight: 700; color: #0f172a;">{{ \Carbon\Carbon::parse($att->date)->translatedFormat('d M Y') }}</span>
                                    <span style="font-weight: 700; color: {{ $att->status === 'hadir' ? '#059669' : '#d97706' }}; text-transform: uppercase;">{{ $att->status }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="font-size: 0.76rem; color: #64748b; text-align: center; padding: 0.75rem 0;">Belum ada riwayat presensi.</div>
                    @endif
                </div>

                <div x-show="rightTab === 'cepat'" style="display: none; padding: 0.9rem 1rem;">
                    <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.5rem;">
                        <a href="{{ route('siswa.cbt.index') }}" style="padding: 0.6rem; border-radius: 0.75rem; background: #f8fafc; border: 1px solid #e2e8f0; text-decoration: none; font-size: 0.74rem; font-weight: 700; color: #0f172a; text-align: center;">🖥️ Portal CBT</a>
                        <a href="{{ route('siswa.nilai') }}" style="padding: 0.6rem; border-radius: 0.75rem; background: #f8fafc; border: 1px solid #e2e8f0; text-decoration: none; font-size: 0.74rem; font-weight: 700; color: #0f172a; text-align: center;">📊 Nilai &amp; Rapor</a>
                        <a href="{{ route('simlab.index') }}" style="padding: 0.6rem; border-radius: 0.75rem; background: #f8fafc; border: 1px solid #e2e8f0; text-decoration: none; font-size: 0.74rem; font-weight: 700; color: #0f172a; text-align: center;">🧪 Simulator Lab</a>
                        <a href="{{ route('siswa.konseling') }}" style="padding: 0.6rem; border-radius: 0.75rem; background: #f8fafc; border: 1px solid #e2e8f0; text-decoration: none; font-size: 0.74rem; font-weight: 700; color: #0f172a; text-align: center;">💬 BK &amp; Prestasi</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
