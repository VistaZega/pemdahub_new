@extends('layouts.siswa')
@section('title', 'Dashboard - Portal Siswa')

@section('content')
<div class="space-y-10" style="padding-bottom: 3rem;">

    {{-- 1. HERO GREETING BAR (ULTRA-SPACIOUS, ELEGANT & AIRY) --}}
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
                        @if($classroom)
                        <span style="padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; background-color: #f3e8ff; color: #581c87; border: 1px solid #e9d5ff;">
                            🏛️ {{ $classroom->class_name }}
                        </span>
                        @endif
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
                @if($activeYear)
                <div style="padding: 0.75rem 1.25rem; border-radius: 1rem; background-color: #eef2ff; border: 2px solid #c7d2fe; color: #312e81; font-size: 0.8rem; font-weight: 900; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-calendar-alt" style="color: #4f46e5;"></i>
                    <span>TP {{ $activeYear->year }}</span>
                </div>
                @endif
                <div style="padding: 0.75rem 1.25rem; border-radius: 1rem; background-color: #ecfdf5; border: 2px solid #a7f3d0; color: #064e3b; font-size: 0.8rem; font-weight: 900; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="far fa-clock" style="color: #059669;"></i>
                    <span>{{ now()->translatedFormat('l, d F Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. URGENT ATTENDANCE ALERT (IF NOT CHECKED IN) --}}
    @if(!$todayAttendance || !$todayAttendance->time_out)
    <div class="bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 rounded-3xl shadow-xl text-white" style="border-radius: 1.75rem; padding: 0.35rem; margin-bottom: 2rem;">
        <div style="background-color: rgba(15, 23, 42, 0.2); backdrop-filter: blur(4px); padding: 1.75rem 2rem; border-radius: 1.5rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 1.75rem; flex: 1; min-width: 260px;">
                <div style="width: 4rem; height: 4rem; border-radius: 1.25rem; background-color: rgba(255, 255, 255, 0.25); border: 2px solid rgba(255, 255, 255, 0.35); display: flex; align-items: center; justify-content: center; font-size: 2rem; flex-shrink: 0; margin-right: 0.5rem;">
                    ⏰
                </div>
                <div style="flex: 1;">
                    <h3 style="font-weight: 900; font-size: 1.15rem; color: #ffffff; line-height: 1.3;">Pengingat Presensi Harian Siswa</h3>
                    <p style="font-size: 0.85rem; color: #fef3c7; font-weight: 600; margin-top: 0.35rem; line-height: 1.5;">
                        @if(!$todayAttendance)
                            Kamu belum melakukan <b>Absen Masuk</b> hari ini. Yuk catat kehadiranmu agar persentase kehadiran tetap maksimal!
                        @else
                            Kamu sudah absen masuk jam <b>{{ date('H:i', strtotime($todayAttendance->time_in)) }}</b>. Jangan lupa lakukan <b>Absen Pulang</b> saat jam sekolah selesai ya!
                        @endif
                    </p>
                </div>
            </div>
            <a href="{{ route('siswa.absensi') }}" style="padding: 0.9rem 1.75rem; background-color: #ffffff; color: #431407; border-radius: 1.25rem; font-weight: 900; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 0.6rem; text-decoration: none; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); flex-shrink: 0;">
                <i class="fas fa-fingerprint" style="color: #ea580c; font-size: 1rem;"></i>
                <span>{{ !$todayAttendance ? 'Absen Masuk Sekarang' : 'Absen Pulang' }}</span>
            </a>
        </div>
    </div>
    @endif

    {{-- 3. 4 COLORFUL VIBRANT STAT CARDS (GUARANTEED SPACIOUS INSETS) --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.75rem; margin-bottom: 2.5rem;">
        
        {{-- Card 1: Kehadiran (Emerald Theme) --}}
        <div class="bg-gradient-to-br from-emerald-500 to-teal-700 text-white shadow-lg" style="padding: 1.75rem; border-radius: 1.75rem; border: 2px solid rgba(52, 211, 153, 0.5); display: flex; flex-direction: column; justify-content: space-between; min-height: 160px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                <span style="font-size: 0.75rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; color: #d1fae5;">Presensi Kehadiran</span>
                <div style="width: 3rem; height: 3rem; border-radius: 1rem; background-color: #ffffff; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); flex-shrink: 0;">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
            <div>
                <p style="font-size: 2.25rem; font-weight: 900; color: #ffffff; line-height: 1; letter-spacing: -0.025em;">{{ $attendanceData['percentage'] }}%</p>
                <p style="font-size: 0.75rem; font-weight: 700; color: #d1fae5; margin-top: 0.6rem; line-height: 1.4;">
                    {{ $attendanceData['present'] }} Hadir dari {{ $attendanceData['total'] }} Hari Efektif
                </p>
            </div>
        </div>

        {{-- Card 2: Rata-rata Nilai (Indigo Theme) --}}
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 text-white shadow-lg" style="padding: 1.75rem; border-radius: 1.75rem; border: 2px solid rgba(129, 140, 248, 0.5); display: flex; flex-direction: column; justify-content: space-between; min-height: 160px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                <span style="font-size: 0.75rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; color: #dbeafe;">Rata-Rata Nilai</span>
                <div style="width: 3rem; height: 3rem; border-radius: 1rem; background-color: #ffffff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); flex-shrink: 0;">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <div>
                <p style="font-size: 2.25rem; font-weight: 900; color: #ffffff; line-height: 1; letter-spacing: -0.025em;">{{ number_format($avgScore, 1) }}</p>
                <p style="font-size: 0.75rem; font-weight: 700; color: #dbeafe; margin-top: 0.6rem; line-height: 1.4;">
                    Indeks Prestasi Semester Ini
                </p>
            </div>
        </div>

        {{-- Card 3: Pembda Elite Score (Amber Theme) --}}
        <div class="bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg" style="padding: 1.75rem; border-radius: 1.75rem; border: 2px solid rgba(251, 191, 36, 0.5); display: flex; flex-direction: column; justify-content: space-between; min-height: 160px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                <span style="font-size: 0.75rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; color: #fef3c7;">Poin Reputasi Elite</span>
                <div style="width: 3rem; height: 3rem; border-radius: 1rem; background-color: #ffffff; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); flex-shrink: 0;">
                    <i class="fas fa-crown"></i>
                </div>
            </div>
            <div>
                <p style="font-size: 2.25rem; font-weight: 900; color: #ffffff; line-height: 1; letter-spacing: -0.025em;">{{ number_format($reputation->total_points ?? 0) }} <span style="font-size: 0.875rem; font-weight: 700; color: #fde68a;">Poin</span></p>
                <p style="font-size: 0.75rem; font-weight: 700; color: #fef3c7; margin-top: 0.6rem; line-height: 1.4;">
                    Peringkat #{{ $rank ?? 1 }} &bull; {{ $reputation->level_name ?? 'Pemula' }}
                </p>
            </div>
        </div>

        {{-- Card 4: Jadwal / Keuangan (Purple/Rose Theme) --}}
        <div class="bg-gradient-to-br from-purple-600 to-pink-600 text-white shadow-lg" style="padding: 1.75rem; border-radius: 1.75rem; border: 2px solid rgba(232, 121, 249, 0.5); display: flex; flex-direction: column; justify-content: space-between; min-height: 160px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                <span style="font-size: 0.75rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; color: #fae8ff;">Status Pembayaran</span>
                <div style="width: 3rem; height: 3rem; border-radius: 1rem; background-color: #ffffff; color: #9333ea; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); flex-shrink: 0;">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
            <div>
                @if($totalOutstanding > 0)
                <p style="font-size: 1.75rem; font-weight: 900; color: #ffffff; line-height: 1.1;">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</p>
                <p style="font-size: 0.75rem; font-weight: 700; color: #fbcfe8; margin-top: 0.6rem; line-height: 1.4;">Ada tagihan belum lunas</p>
                @else
                <p style="font-size: 1.75rem; font-weight: 900; color: #ffffff; line-height: 1.1;">Lunas 100%</p>
                <p style="font-size: 0.75rem; font-weight: 700; color: #a7f3d0; margin-top: 0.6rem; line-height: 1.4;">Bebas tunggakan SPP</p>
                @endif
            </div>
        </div>
    </div>

    {{-- 4. JADWAL PELAJARAN HARI INI (ULTRA-SPACIOUS, NO-CLIP, ATTRACTIVE & BEAUTIFULLY SPACED) --}}
    <div class="bg-white shadow-xl" style="border-radius: 1.75rem; border: 2px solid #e0e7ff; overflow: hidden; margin-bottom: 2.5rem;">
        {{-- Header Bar with Generous Padding & Margin --}}
        <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-purple-800 text-white" style="padding: 2rem 2.25rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 1.75rem;">
                <div style="width: 4rem; height: 4rem; border-radius: 1.25rem; background-color: #ffffff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-size: 2rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); flex-shrink: 0; font-weight: 900; margin-right: 0.5rem;">
                    📅
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.4rem;">
                        <span style="padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; background-color: #fbbf24; color: #0f172a;">
                            {{ now()->translatedFormat('l, d F Y') }}
                        </span>
                        @if($currentSchedule)
                        <span style="padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; background-color: #34d399; color: #022c22;" class="animate-pulse">
                            🟢 Sedang Berlangsung
                        </span>
                        @endif
                    </div>
                    <h2 style="font-size: 1.4rem; font-weight: 900; color: #ffffff; line-height: 1.2; margin-top: 0.25rem;">
                        Jadwal Pelajaran Hari Ini
                    </h2>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 1rem;">
                <span style="font-size: 0.8rem; font-weight: 700; color: #c7d2fe;">Total {{ $todaySchedules->count() }} Sesi Pelajaran</span>
                <a href="{{ route('siswa.jadwal') }}" style="padding: 0.75rem 1.25rem; background-color: #ffffff; color: #312e81; border-radius: 1rem; font-weight: 900; font-size: 0.75rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                    <span>Lihat Jadwal Lengkap</span>
                    <i class="fas fa-arrow-right" style="font-size: 0.7rem;"></i>
                </a>
            </div>
        </div>

        {{-- Interactive Schedule Cards Grid with Guaranteed Insets & Spacing --}}
        <div style="padding: 2.25rem;">
            @if($groupedTodaySchedules->count() > 0)
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.75rem;">
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

                    @foreach($groupedTodaySchedules as $timeKey => $schedulesAtTime)
                        @php
                            $first = $schedulesAtTime->first();
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
                            $palette = $colorPalettes[$cIdx % count($colorPalettes)];
                            $cIdx++;
                        @endphp

                        <div class="bg-white shadow-sm hover:shadow-lg transition" style="padding: 1.75rem; border-radius: 1.5rem; border: 2px solid {{ $isCurrent ? '#10b981' : ($isNext ? '#f59e0b' : '#e2e8f0') }}; display: flex; flex-direction: column; justify-content: space-between; {{ $isCurrent ? 'box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.2);' : '' }}">
                            
                            {{-- Top Status Pill & Time Badge (WITH GUARANTEED MARGIN FROM BORDER & NO CLIPPING) --}}
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 1.25rem; padding-bottom: 0.85rem; border-bottom: 2px solid #f1f5f9;">
                                <div>
                                    <span style="padding: 0.45rem 1rem; border-radius: 9999px; background-color: #0f172a; color: #ffffff; font-weight: 900; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                        <i class="far fa-clock" style="color: #fbbf24; margin-right: 0.25rem;"></i>
                                        <span>{{ $sStart }} - {{ $sEnd }}</span>
                                    </span>
                                </div>

                                <div>
                                    @if($isCurrent)
                                    <span style="padding: 0.4rem 0.9rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; background-color: #10b981; color: #ffffff; display: inline-flex; align-items: center; gap: 0.4rem;" class="animate-pulse">
                                        <i class="fas fa-circle" style="font-size: 0.4rem;"></i> Berlangsung
                                    </span>
                                    @elseif($isNext)
                                    <span style="padding: 0.4rem 0.9rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; background-color: #f59e0b; color: #ffffff; display: inline-flex; align-items: center; gap: 0.4rem;">
                                        ⏳ Berikutnya
                                    </span>
                                    @elseif($isPast)
                                    <span style="padding: 0.4rem 0.9rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; background-color: #e2e8f0; color: #334155; display: inline-flex; align-items: center; gap: 0.4rem;">
                                        ✓ Selesai
                                    </span>
                                    @else
                                    <span style="padding: 0.4rem 0.9rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; background-color: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; display: inline-flex; align-items: center; gap: 0.4rem;">
                                        Mendatang
                                    </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Subject Content with Wide Margins & Inset --}}
                            <div style="margin-top: 0.5rem; margin-bottom: 0.5rem;">
                                @foreach($schedulesAtTime as $s)
                                <div style="display: flex; align-items: flex-start; gap: 1.25rem; margin-bottom: 1rem;">
                                    <div class="bg-gradient-to-br {{ $palette['bg'] }} text-white" style="width: 3.5rem; height: 3.5rem; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; flex-shrink: 0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-right: 0.25rem;">
                                        {{ $palette['icon'] }}
                                    </div>
                                    <div style="flex: 1; min-width: 0; padding-top: 0.2rem;">
                                        <h3 style="font-size: 1.1rem; font-weight: 900; color: #0f172a; line-height: 1.35; margin-bottom: 0.35rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            {{ $s->subject->subject_name ?? ($s->subject->name ?? 'Mata Pelajaran') }}
                                        </h3>
                                        <p style="font-size: 0.8rem; color: #475569; font-weight: 700; display: flex; align-items: center; gap: 0.5rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <i class="fas fa-chalkboard-teacher" style="color: #6366f1;"></i>
                                            <span>{{ $s->teacher->user->name ?? ($s->teacher->full_name ?? 'Guru Pengampu') }}</span>
                                        </p>
                                    </div>
                                </div>

                                {{-- Room & Class info pill (CLEANLY INSET & PADDED) --}}
                                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem; background-color: #f8fafc; border-radius: 1rem; border: 1px solid #e2e8f0; padding: 0.75rem 1rem; margin-top: 0.75rem;">
                                    <span style="font-weight: 700; color: #334155; display: flex; align-items: center; gap: 0.5rem;">
                                        <i class="fas fa-door-open" style="color: #f43f5e;"></i>
                                        <span>Ruang: {{ $s->room ?: 'Kelas ' . ($classroom->class_name ?? 'Utama') }}</span>
                                    </span>
                                    <span style="font-weight: 900; color: #3730a3; background-color: #e0e7ff; padding: 0.2rem 0.6rem; border-radius: 0.6rem;">
                                        {{ $s->subject->code ?? 'MAPEL' }}
                                    </span>
                                </div>
                                @endforeach
                            </div>

                            {{-- Action Footer (GUARANTEED MARGIN FROM BOTTOM & LEFT BORDER) --}}
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-top: 1.25rem; padding-top: 0.85rem; border-top: 2px solid #f1f5f9;">
                                <a href="{{ route('siswa.lms.index') }}" style="font-size: 0.75rem; font-weight: 900; color: #4f46e5; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                                    <i class="fas fa-book-open"></i>
                                    <span>Materi & Tugas</span>
                                </a>

                                @if($isCurrent)
                                <a href="{{ route('siswa.lms.index') }}" style="padding: 0.5rem 1rem; background-color: #059669; color: #ffffff; border-radius: 0.75rem; font-size: 0.75rem; font-weight: 900; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    <span>Masuk LMS</span> &rarr;
                                </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="padding: 4rem 2rem; text-align: center; background-color: #f8fafc; border-radius: 1.75rem; border: 2px dashed #cbd5e1;">
                    <div style="width: 5rem; height: 5rem; background-color: #ffffff; color: #94a3b8; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; font-size: 2.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                        ☕
                    </div>
                    <h3 style="font-weight: 900; color: #1e293b; font-size: 1.2rem; margin-bottom: 0.5rem;">Tidak Ada Jadwal Pelajaran Hari Ini</h3>
                    <p style="font-size: 0.85rem; color: #64748b; max-width: 450px; margin: 0 auto 1.5rem auto; line-height: 1.6;">Hari ini tidak ada sesi belajar mengajar di kelas. Manfaatkan waktu luang untuk belajar mandiri di LMS atau ikuti kegiatan ekstrakurikuler!</p>
                    <a href="{{ route('siswa.lms.index') }}" style="padding: 0.85rem 1.75rem; background-color: #4f46e5; color: #ffffff; border-radius: 1.25rem; font-size: 0.8rem; font-weight: 900; text-decoration: none; display: inline-flex; align-items: center; gap: 0.6rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                        <i class="fas fa-laptop-code"></i>
                        <span>Buka Portal E-Learning LMS</span>
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- 5. WIDGET DNA POTENSI & KARIR SISWA 360° (VIBRANT BENTO & SPACIOUS) --}}
    @if(isset($dnaAnalysis))
    <div class="bg-white shadow-lg relative overflow-hidden" style="border-radius: 1.75rem; border: 2px solid #e0e7ff; margin-bottom: 2.5rem;">
        {{-- Rainbow Header Stripe --}}
        <div class="h-3 w-full bg-gradient-to-r from-purple-600 via-pink-500 to-amber-500" style="height: 0.4rem;"></div>

        <div style="padding: 2.25rem; display: flex; flex-direction: column; gap: 2rem;">
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.5rem;">
                <div style="space-y: 0.75rem; max-width: 700px;">
                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; margin-bottom: 0.6rem;">
                        <span class="bg-gradient-to-r {{ $dnaAnalysis['archetype']['color'] }} text-white shadow-sm" style="padding: 0.4rem 1.25rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 0.5rem;">
                            <i class="fas {{ $dnaAnalysis['archetype']['badge_icon'] }}"></i> {{ $dnaAnalysis['archetype']['title'] }}
                        </span>
                        <span style="font-size: 0.75rem; font-weight: 900; color: #312e81; background-color: #eef2ff; border: 1px solid #c7d2fe; padding: 0.4rem 1rem; border-radius: 9999px;">
                            ✨ Akurasi Profil: {{ $dnaAnalysis['confidence_score'] }}%
                        </span>
                    </div>

                    <h2 style="font-size: 1.5rem; font-weight: 900; color: #0f172a; line-height: 1.3; margin-top: 0.4rem; margin-bottom: 0.4rem;">
                        DNA Potensi Belajar & Minat Karier 360°
                    </h2>

                    <p style="font-size: 0.875rem; color: #475569; font-weight: 600; line-height: 1.6;">
                        "{{ $dnaAnalysis['archetype']['tagline'] }}" &mdash; {{ $dnaAnalysis['archetype']['description'] }}
                    </p>
                </div>

                {{-- DNA Action Buttons (GUARANTEED NO OVERLAPPING) --}}
                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.85rem;">
                    <a href="{{ route('siswa.dna.index') }}" class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md hover:opacity-95 transition" style="padding: 0.85rem 1.5rem; border-radius: 1.25rem; font-weight: 900; font-size: 0.8rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.6rem;">
                        <i class="fas fa-radar"></i>
                        <span>Buka Radar DNA 360°</span>
                    </a>
                    <a href="{{ route('siswa.dna.pdf') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-800 transition" style="padding: 0.85rem 1.5rem; border-radius: 1.25rem; font-weight: 900; font-size: 0.8rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.6rem; border: 1px solid #cbd5e1;">
                        <i class="fas fa-file-pdf" style="color: #e11d48;"></i>
                        <span>Unduh Sertifikat PDF</span>
                    </a>
                </div>
            </div>

            {{-- 6 Dimension Meters (GUARANTEED 12px GAPS & NO TOUCHING BOXES) --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 1rem; margin-top: 0.5rem;">
                <div style="background-color: #eff6ff; border: 2px solid #bfdbfe; border-radius: 1.25rem; padding: 1.25rem 0.75rem; text-align: center;">
                    <p style="font-size: 0.7rem; font-weight: 900; color: #1d4ed8; text-transform: uppercase; margin-bottom: 0.35rem;">Logika</p>
                    <p style="font-size: 1.5rem; font-weight: 900; color: #1e3a8a;">{{ $dnaAnalysis['scores']['logic'] }}</p>
                </div>
                <div style="background-color: #ecfdf5; border: 2px solid #a7f3d0; border-radius: 1.25rem; padding: 1.25rem 0.75rem; text-align: center;">
                    <p style="font-size: 0.7rem; font-weight: 900; color: #047857; text-transform: uppercase; margin-bottom: 0.35rem;">Bahasa</p>
                    <p style="font-size: 1.5rem; font-weight: 900; color: #064e3b;">{{ $dnaAnalysis['scores']['communication'] }}</p>
                </div>
                <div style="background-color: #eef2ff; border: 2px solid #c7d2fe; border-radius: 1.25rem; padding: 1.25rem 0.75rem; text-align: center;">
                    <p style="font-size: 0.7rem; font-weight: 900; color: #4338ca; text-transform: uppercase; margin-bottom: 0.35rem;">Vokasi</p>
                    <p style="font-size: 1.5rem; font-weight: 900; color: #312e81;">{{ $dnaAnalysis['scores']['technical'] }}</p>
                </div>
                <div style="background-color: #fffbeb; border: 2px solid #fde68a; border-radius: 1.25rem; padding: 1.25rem 0.75rem; text-align: center;">
                    <p style="font-size: 0.7rem; font-weight: 900; color: #b45309; text-transform: uppercase; margin-bottom: 0.35rem;">Sosial</p>
                    <p style="font-size: 1.5rem; font-weight: 900; color: #78350f;">{{ $dnaAnalysis['scores']['social'] }}</p>
                </div>
                <div style="background-color: #faf5ff; border: 2px solid #e9d5ff; border-radius: 1.25rem; padding: 1.25rem 0.75rem; text-align: center;">
                    <p style="font-size: 0.7rem; font-weight: 900; color: #7e22ce; text-transform: uppercase; margin-bottom: 0.35rem;">Kreatif</p>
                    <p style="font-size: 1.5rem; font-weight: 900; color: #581c87;">{{ $dnaAnalysis['scores']['creative'] }}</p>
                </div>
                <div style="background-color: #fff1f2; border: 2px solid #fecdd3; border-radius: 1.25rem; padding: 1.25rem 0.75rem; text-align: center;">
                    <p style="font-size: 0.7rem; font-weight: 900; color: #be123c; text-transform: uppercase; margin-bottom: 0.35rem;">Disiplin</p>
                    <p style="font-size: 1.5rem; font-weight: 900; color: #881337;">{{ $dnaAnalysis['scores']['discipline'] }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- 6. DUA KOLOM: EKSTRAKURIKULER & LMS + REPUTASI --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
        
        {{-- KOLOM KIRI: EKSTRAKURIKULER & RIWAYAT PRESENSI --}}
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            
            {{-- Ekstrakurikuler --}}
            @php
                $myEkskuls = $student->extracurricularMembers()->where('status', 'approved')->with('extracurricular')->get();
            @endphp
            <div class="bg-white shadow-sm" style="padding: 2rem; border-radius: 1.75rem; border: 2px solid #e0e7ff;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 1.25rem;">
                        <div class="bg-gradient-to-br from-amber-400 to-orange-500 text-white" style="width: 3.5rem; height: 3.5rem; border-radius: 1.25rem; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; flex-shrink: 0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-right: 0.25rem;">
                            🎨
                        </div>
                        <div>
                            <h3 style="font-size: 1.15rem; font-weight: 900; color: #0f172a; line-height: 1.3;">Ekstrakurikuler & Karakter</h3>
                            <p style="font-size: 0.8rem; color: #64748b; font-weight: 600; margin-top: 0.25rem;">Unit kegiatan dan ruang diskusi Pembda Space</p>
                        </div>
                    </div>
                    <a href="{{ route('siswa.ekskul.index') }}" style="font-size: 0.75rem; font-weight: 900; color: #4f46e5; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                        <span>Katalog</span>
                        <i class="fas fa-arrow-right" style="font-size: 0.65rem;"></i>
                    </a>
                </div>

                @if($myEkskuls->isNotEmpty())
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem;">
                    @foreach($myEkskuls as $m)
                    @php $e = $m->extracurricular; @endphp
                    <div class="bg-gradient-to-br from-slate-50 to-indigo-50/40 shadow-2xs" style="padding: 1.25rem; border-radius: 1.5rem; border: 2px solid #e0e7ff; display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                        <div style="display: flex; align-items: center; gap: 1rem; min-width: 0;">
                            <div style="width: 3rem; height: 3rem; border-radius: 1rem; background-color: #ffffff; border: 1px solid #c7d2fe; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-right: 0.25rem;">
                                {{ $e->display_icon }}
                            </div>
                            <div style="min-w-0;">
                                <div style="display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.25rem;">
                                    <span class="bg-gradient-to-r {{ $m->role_badge_color }} text-white" style="padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 900;">
                                        {{ $m->role_label }}
                                    </span>
                                    @if($m->section)
                                    <span style="padding: 0.2rem 0.5rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 900; background-color: #f3e8ff; color: #581c87; border: 1px solid #e9d5ff;">
                                        🎺 {{ $m->section }}
                                    </span>
                                    @endif
                                </div>
                                <p style="font-size: 0.8rem; font-weight: 900; color: #0f172a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $e->name }}</p>
                            </div>
                        </div>
                        @if($e->forum_group_id)
                        <a href="{{ route('forum.index', ['group' => $e->forum_group_id]) }}" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-xs" style="padding: 0.5rem 0.85rem; border-radius: 0.75rem; font-size: 0.75rem; font-weight: 900; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem; flex-shrink: 0;" title="Buka Kanal Space">
                            <i class="fas fa-comments"></i>
                            <span>Space</span>
                        </a>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div style="padding: 1.5rem; background-color: #fef3c7; border-radius: 1.5rem; border: 1px solid #fde68a; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; font-size: 0.85rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; color: #451a03;">
                        <span style="font-size: 2rem;">⚜️</span>
                        <span style="font-weight: 600; line-height: 1.5;">Kamu belum memilih unit ekstrakurikuler. Bergabunglah untuk mendapatkan reward <b>+15 Poin Reputasi</b>!</span>
                    </div>
                    <a href="{{ route('siswa.ekskul.index') }}" style="padding: 0.75rem 1.5rem; background-color: #d97706; color: #ffffff; border-radius: 1rem; font-weight: 900; font-size: 0.75rem; text-decoration: none; flex-shrink: 0;">
                        Pilih Ekskul Sekarang
                    </a>
                </div>
                @endif
            </div>

            {{-- Riwayat Presensi Terakhir --}}
            <div class="bg-white shadow-sm" style="border-radius: 1.75rem; border: 2px solid #e2e8f0; overflow: hidden;">
                <div style="padding: 1.25rem 2rem; border-bottom: 2px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; background-color: #f8fafc;">
                    <h3 style="font-size: 1rem; font-weight: 900; color: #0f172a; display: flex; align-items: center; gap: 0.6rem;">
                        <i class="fas fa-history" style="color: #059669;"></i>
                        <span>Riwayat Presensi Terakhir</span>
                    </h3>
                    <a href="{{ route('siswa.absensi') }}" style="font-size: 0.75rem; font-weight: 900; color: #4f46e5; text-decoration: none;">Detail Lengkap &rarr;</a>
                </div>
                <div style="padding: 1.75rem 2rem;">
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; font-size: 0.8rem; border-collapse: collapse;">
                            <thead>
                                <tr style="background-color: #f1f5f9; color: #334155; font-weight: 900; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.05em;">
                                    <th style="padding: 0.85rem 1rem; text-align: left;">Hari / Tanggal</th>
                                    <th style="padding: 0.85rem 1rem; text-align: center;">Status</th>
                                    <th style="padding: 0.85rem 1rem; text-align: center;">Jam Masuk</th>
                                    <th style="padding: 0.85rem 1rem; text-align: center;">Jam Pulang</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendanceHistory as $att)
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 1rem; font-weight: 900; color: #0f172a;">
                                        {{ $att->date->translatedFormat('l, d M Y') }}
                                    </td>
                                    <td style="padding: 1rem; text-align: center;">
                                        @php
                                            $statusStyle = match($att->status) {
                                                'hadir' => 'background-color: #d1fae5; color: #064e3b; border: 1px solid #a7f3d0;',
                                                'sakit' => 'background-color: #fef3c7; color: #78350f; border: 1px solid #fde68a;',
                                                'izin' => 'background-color: #dbeafe; color: #1e3a8a; border: 1px solid #bfdbfe;',
                                                'alpha' => 'background-color: #ffe4e6; color: #881337; border: 1px solid #fecdd3;',
                                                default => 'background-color: #f1f5f9; color: #334155;'
                                            };
                                        @endphp
                                        <span style="padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; {{ $statusStyle }}">
                                            {{ $att->status }}
                                        </span>
                                    </td>
                                    <td style="padding: 1rem; text-align: center; font-family: monospace; font-weight: 700; color: #334155;">
                                        {{ $att->time_in ? date('H:i', strtotime($att->time_in)) : '--:--' }}
                                    </td>
                                    <td style="padding: 1rem; text-align: center; font-family: monospace; font-weight: 700; color: #334155;">
                                        {{ $att->time_out ? date('H:i', strtotime($att->time_out)) : '--:--' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" style="padding: 2rem; text-align: center; color: #94a3b8; font-weight: 600;">Belum ada riwayat kehadiran tercatat.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: LMS, REPUTASI, & MENU CEPAT --}}
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            
            {{-- Kursus LMS --}}
            <div class="bg-white shadow-sm" style="padding: 2rem; border-radius: 1.75rem; border: 2px solid #e0e7ff;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
                    <h3 style="font-size: 1rem; font-weight: 900; color: #0f172a; display: flex; align-items: center; gap: 0.6rem;">
                        <i class="fas fa-book-reader" style="color: #4f46e5;"></i>
                        <span>Kursus Saya (LMS)</span>
                    </h3>
                    <a href="{{ route('siswa.lms.index') }}" style="font-size: 0.75rem; font-weight: 900; color: #4f46e5; text-decoration: none;">Semua</a>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @forelse($courses as $course)
                    @php $progress = $courseProgress[$course->id] ?? 0; @endphp
                    <a href="{{ route('siswa.lms.show', $course->id) }}" style="display: block; padding: 1rem; border-radius: 1.25rem; background-color: #f8fafc; border: 1px solid #e2e8f0; text-decoration: none;">
                        <h4 style="font-size: 0.85rem; font-weight: 900; color: #0f172a; margin-bottom: 0.6rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $course->name }}</h4>
                        <div style="width: 100%; background-color: #e2e8f0; border-radius: 9999px; height: 0.5rem; overflow: hidden; margin-bottom: 0.5rem;">
                            <div style="height: 100%; background-color: #4f46e5; border-radius: 9999px; width: {{ $progress }}%;"></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; font-weight: 700; color: #64748b;">
                            <span>{{ $course->materials_count }} Materi</span>
                            <span style="color: #4338ca; font-weight: 900;">{{ $progress }}%</span>
                        </div>
                    </a>
                    @empty
                    <p style="text-align: center; padding: 1.5rem 0; font-size: 0.8rem; color: #94a3b8; font-weight: 600;">Belum ada kursus LMS aktif.</p>
                    @endforelse
                </div>
            </div>

            {{-- Pembda Elite Leaderboard & Activity (GUARANTEED NO CLIPPING) --}}
            <div class="bg-white shadow-sm" style="padding: 2rem; border-radius: 1.75rem; border: 2px solid #fde68a;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
                    <h3 style="font-size: 1rem; font-weight: 900; color: #0f172a; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-crown" style="color: #f59e0b;"></i>
                        <span>Pembda Elite</span>
                    </h3>
                    <a href="{{ route('reputation.leaderboard') }}" style="font-size: 0.75rem; font-weight: 900; color: #d97706; text-decoration: none;">Papan Skor &rarr;</a>
                </div>

                <div class="bg-gradient-to-br from-amber-500 via-orange-500 to-amber-600 shadow-md text-white" style="padding: 2rem 1.5rem; text-align: center; border-radius: 1.5rem; margin-bottom: 1.5rem;">
                    <span style="background-color: rgba(255,255,255,0.25); color: #ffffff; font-size: 0.7rem; font-weight: 900; padding: 0.35rem 1rem; border-radius: 9999px; text-transform: uppercase;">
                        PERINGKAT #{{ $rank ?? 1 }}
                    </span>
                    <div style="font-size: 2.5rem; font-weight: 900; color: #ffffff; margin-top: 0.75rem; margin-bottom: 0.75rem; line-height: 1;">
                        {{ number_format($reputation->total_points ?? 0) }} Poin
                    </div>
                    <div>
                        <span style="display: inline-block; padding: 0.4rem 1.25rem; background-color: #ffffff; color: #0f172a; font-size: 0.75rem; font-weight: 900; border-radius: 9999px; text-transform: uppercase; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            {{ $reputation->level_name ?? 'Level Siswa' }}
                        </span>
                    </div>
                </div>

                {{-- Aktivitas Terakhir --}}
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <p style="font-size: 0.7rem; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">Aktivitas Poin Terakhir</p>
                    @foreach($reputationLogs as $log)
                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem; padding: 0.75rem 1rem; border-radius: 1rem; background-color: #f8fafc; border: 1px solid #f1f5f9;">
                        <div style="flex: 1; min-width: 0;">
                            <p style="font-weight: 700; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $log->description }}</p>
                            <p style="font-size: 0.65rem; color: #94a3b8; margin-top: 0.2rem;">{{ $log->created_at->diffForHumans() }}</p>
                        </div>
                        <span style="font-weight: 900; font-size: 0.85rem; margin-left: 0.75rem; color: {{ $log->points >= 0 ? '#059669' : '#e11d48' }};">
                            {{ $log->points >= 0 ? '+' : '' }}{{ $log->points }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Menu Cepat --}}
            <div class="bg-white shadow-sm" style="padding: 2rem; border-radius: 1.75rem; border: 2px solid #e2e8f0;">
                <h3 style="font-size: 1rem; font-weight: 900; color: #0f172a; display: flex; align-items: center; gap: 0.6rem; margin-bottom: 1.25rem;">
                    <i class="fas fa-bolt" style="color: #4f46e5;"></i>
                    <span>Pintasan Menu Siswa</span>
                </h3>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                    <a href="{{ route('siswa.nilai') }}" style="padding: 1.25rem 0.75rem; background-color: #eff6ff; color: #1e3a8a; border-radius: 1.25rem; text-align: center; font-weight: 700; font-size: 0.8rem; text-decoration: none; display: flex; flex-direction: column; align-items: center; gap: 0.6rem;">
                        <i class="fas fa-chart-bar" style="font-size: 1.5rem; color: #2563eb;"></i>
                        <span>Nilai Saya</span>
                    </a>
                    <a href="{{ route('siswa.absensi') }}" style="padding: 1.25rem 0.75rem; background-color: #ecfdf5; color: #064e3b; border-radius: 1.25rem; text-align: center; font-weight: 700; font-size: 0.8rem; text-decoration: none; display: flex; flex-direction: column; align-items: center; gap: 0.6rem;">
                        <i class="fas fa-clipboard-check" style="font-size: 1.5rem; color: #059669;"></i>
                        <span>Presensi</span>
                    </a>
                    <a href="{{ route('siswa.tagihan') }}" style="padding: 1.25rem 0.75rem; background-color: #fff1f2; color: #881337; border-radius: 1.25rem; text-align: center; font-weight: 700; font-size: 0.8rem; text-decoration: none; display: flex; flex-direction: column; align-items: center; gap: 0.6rem;">
                        <i class="fas fa-wallet" style="font-size: 1.5rem; color: #e11d48;"></i>
                        <span>Tagihan SPP</span>
                    </a>
                    <a href="{{ route('siswa.lms.index') }}" style="padding: 1.25rem 0.75rem; background-color: #f3e8ff; color: #581c87; border-radius: 1.25rem; text-align: center; font-weight: 700; font-size: 0.8rem; text-decoration: none; display: flex; flex-direction: column; align-items: center; gap: 0.6rem;">
                        <i class="fas fa-laptop-code" style="font-size: 1.5rem; color: #9333ea;"></i>
                        <span>Portal LMS</span>
                    </a>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
