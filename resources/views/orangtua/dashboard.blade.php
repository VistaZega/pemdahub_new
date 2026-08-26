@extends('layouts.orangtua')
@section('title', 'Dashboard - Portal Orang Tua')

@section('content')
<div class="space-y-8" style="padding-bottom: 3rem;" x-data="{ activeTab: '{{ $childrenData->count() > 1 ? 'all' : ($childrenData->first()['student']->id ?? 'all') }}' }">

    @if($childrenData->count() === 0)
        {{-- EMPTY STATE --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-12 text-center" style="border-radius: 1.75rem;">
            <div style="width: 5rem; height: 5rem; background-color: #f1f5f9; color: #94a3b8; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; font-size: 2.5rem;">
                👨‍👩‍👧‍👦
            </div>
            <h2 style="font-size: 1.3rem; font-weight: 900; color: #1e293b; margin-bottom: 0.5rem;">Belum Ada Data Anak Terhubung</h2>
            <p style="font-size: 0.875rem; color: #64748b; max-width: 460px; margin: 0 auto 1.5rem auto; line-height: 1.6;">
                Akun Anda belum terhubung dengan data siswa di Yayasan Perguruan PEMBDA Nias. Silakan hubungi tata usaha atau administrator sekolah untuk melakukan verifikasi relasi orang tua.
            </p>
        </div>
    @else

        {{-- MULTI-CHILD TAB SWITCHER (IF > 1 CHILD) --}}
        @if($childrenData->count() > 1)
        <div class="bg-white p-2 rounded-2xl border border-indigo-100 shadow-sm flex flex-wrap items-center gap-2" style="border-radius: 1.25rem;">
            <button @click="activeTab = 'all'"
                    :class="activeTab === 'all' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md font-extrabold' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 font-bold'"
                    class="px-5 py-2.5 rounded-xl text-xs transition flex items-center gap-2" style="border-radius: 0.85rem;">
                <i class="fas fa-users"></i>
                <span>📊 Ringkasan Semua Anak ({{ $childrenData->count() }})</span>
            </button>

            @foreach($childrenData as $d)
                @php $st = $d['student']; @endphp
                <button @click="activeTab = '{{ $st->id }}'"
                        :class="activeTab === '{{ $st->id }}' ? 'bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow-md font-extrabold' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 font-bold'"
                        class="px-5 py-2.5 rounded-xl text-xs transition flex items-center gap-2" style="border-radius: 0.85rem;">
                    <div style="width: 1.4rem; height: 1.4rem; border-radius: 9999px; overflow: hidden; border: 1px solid #ffffff; flex-shrink: 0;">
                        <img src="{{ $st->photo_url }}" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <span>{{ $st->full_name }}</span>
                    @if($d['classroom'])
                    <span class="opacity-75 font-mono text-[10px]">({{ $d['classroom']->class_name }})</span>
                    @endif
                </button>
            @endforeach
        </div>
        @endif

        {{-- TAB 0: ALL CHILDREN OVERVIEW (IF > 1 CHILD & activeTab === 'all') --}}
        @if($childrenData->count() > 1)
        <div x-show="activeTab === 'all'" class="space-y-6">
            {{-- Overview Hero --}}
            <div class="bg-white rounded-3xl border-2 border-indigo-100 shadow-lg relative overflow-hidden" style="border-radius: 1.75rem; border: 2px solid #e0e7ff;">
                <div class="h-3.5 w-full bg-gradient-to-r from-blue-600 via-teal-500 via-amber-500 via-purple-600 to-rose-500" style="height: 0.5rem;"></div>
                <div style="padding: 1.75rem 2rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.5rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.4rem;">
                            <span style="padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; background-color: #e0e7ff; color: #312e81;">
                                👨‍👩‍👧‍👦 Portal Orang Tua
                            </span>
                            <span style="padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; background-color: #d1fae5; color: #064e3b;">
                                {{ $childrenData->count() }} Anak Terhubung
                            </span>
                        </div>
                        <h1 style="font-size: 1.6rem; font-weight: 900; color: #0f172a; margin-top: 0.2rem;">
                            Selamat Datang, {{ Auth::user()->name }}! 👋
                        </h1>
                        <p style="font-size: 0.85rem; color: #475569; font-weight: 600; margin-top: 0.2rem;">
                            Monitoring pendidikan, absensi real-time, nilai akademik, dan biaya sekolah putra-putri Anda.
                        </p>
                    </div>
                    <div style="padding: 0.75rem 1.25rem; border-radius: 1rem; background-color: #ecfdf5; border: 2px solid #a7f3d0; color: #064e3b; font-size: 0.8rem; font-weight: 900; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="far fa-clock" style="color: #059669;"></i>
                        <span>{{ now()->translatedFormat('l, d F Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Children Overview Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($childrenData as $data)
                    @php $s = $data['student']; @endphp
                    <div class="bg-white rounded-3xl shadow-sm hover:shadow-xl transition border-2 border-slate-100 overflow-hidden flex flex-col justify-between" style="border-radius: 1.75rem;">
                        <div>
                            {{-- Header --}}
                            <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50 p-5 border-b border-indigo-100 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-4 min-w-0">
                                    <div class="w-14 h-14 rounded-2xl overflow-hidden border-2 border-white shadow-md flex-shrink-0 bg-white">
                                        <img src="{{ $s->photo_url }}" class="w-full h-full object-cover" alt="{{ $s->full_name }}">
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="font-extrabold text-slate-900 text-base truncate">{{ $s->full_name }}</h3>
                                        <p class="text-xs text-slate-500 font-semibold truncate mt-0.5">
                                            🏛️ {{ $data['classroom'] ? $data['classroom']->class_name : 'Belum ada kelas' }} · {{ $s->school->name ?? 'Pembda' }}
                                        </p>
                                        <p class="text-[11px] text-slate-400 font-mono mt-0.5">NISN: {{ $s->nisn ?: '-' }}</p>
                                    </div>
                                </div>
                                <button @click="activeTab = '{{ $s->id }}'" class="bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs px-3 py-2 rounded-xl transition flex-shrink-0 flex items-center gap-1 shadow-sm">
                                    <span>Detail</span> &rarr;
                                </button>
                            </div>

                            {{-- Today's Status Banner --}}
                            <div class="px-5 py-3 bg-slate-50 border-b border-slate-100 flex items-center justify-between text-xs font-semibold text-slate-700">
                                <span class="flex items-center gap-1.5">
                                    <i class="fas fa-calendar-day text-indigo-500"></i>
                                    <span>Hari Ini:</span>
                                    @if($data['today_attendance'])
                                        <span class="font-bold text-emerald-600">✓ Presensi ({{ $data['today_attendance']->status_label }})</span>
                                    @else
                                        <span class="font-bold text-amber-600">Belum presensi</span>
                                    @endif
                                </span>
                                @if($data['current_schedule'])
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-black px-2 py-0.5 rounded-full flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> {{ $data['current_schedule']->subject->subject_name ?? 'Belajar' }}
                                </span>
                                @endif
                            </div>

                            {{-- 4 Quick Stats --}}
                            <div class="grid grid-cols-2 gap-3 p-5">
                                <div class="bg-emerald-50/60 border border-emerald-200/60 rounded-2xl p-3.5">
                                    <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider block">Presensi</span>
                                    <p class="text-2xl font-black text-emerald-800 mt-1">{{ $data['attendance_pct'] }}%</p>
                                    <span class="text-[11px] text-emerald-600 font-medium block mt-0.5">{{ $data['attendance_data']['present'] }} Hadir dari {{ $data['attendance_data']['total'] }} Hari</span>
                                </div>
                                <div class="bg-indigo-50/60 border border-indigo-200/60 rounded-2xl p-3.5">
                                    <span class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider block">Rata-Rata Nilai</span>
                                    <p class="text-2xl font-black text-indigo-800 mt-1">{{ number_format($data['avg_score'], 1) }}</p>
                                    <span class="text-[11px] text-indigo-600 font-medium block mt-0.5">Indeks Prestasi Semester</span>
                                </div>
                                <div class="bg-amber-50/60 border border-amber-200/60 rounded-2xl p-3.5">
                                    <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider block">DNA 360°</span>
                                    <p class="text-sm font-black text-amber-900 mt-1 truncate">
                                        {{ $data['dna_analysis']['archetype']['title'] ?? 'Profil Belajar' }}
                                    </p>
                                    <span class="text-[11px] text-amber-700 font-medium block mt-0.5">Rank #{{ $data['rank'] }} Reputasi</span>
                                </div>
                                <div class="bg-purple-50/60 border border-purple-200/60 rounded-2xl p-3.5">
                                    <span class="text-[11px] font-bold text-purple-700 uppercase tracking-wider block">Tunggakan SPP</span>
                                    <p class="text-sm font-black text-purple-900 mt-1 truncate">
                                        {{ $data['outstanding'] > 0 ? 'Rp '.number_format($data['outstanding'], 0, ',', '.') : '✅ Bebas Tunggakan' }}
                                    </p>
                                    <span class="text-[11px] text-purple-600 font-medium block mt-0.5">s.d. Bulan Ini</span>
                                </div>
                            </div>
                        </div>

                        {{-- Quick Links --}}
                        <div class="grid grid-cols-4 border-t border-slate-100 divide-x divide-slate-100 bg-slate-50/50 rounded-b-3xl">
                            <a href="{{ route('orangtua.anak.nilai', $s->id) }}" class="py-3 text-center text-xs font-bold text-emerald-600 hover:bg-emerald-50 transition flex flex-col items-center gap-1">
                                <i class="fas fa-chart-bar text-sm"></i> Nilai
                            </a>
                            <a href="{{ route('orangtua.anak.tagihan', $s->id) }}" class="py-3 text-center text-xs font-bold text-purple-600 hover:bg-purple-50 transition flex flex-col items-center gap-1">
                                <i class="fas fa-file-invoice-dollar text-sm"></i> Tagihan
                            </a>
                            <a href="{{ route('orangtua.anak.absensi', $s->id) }}" class="py-3 text-center text-xs font-bold text-blue-600 hover:bg-blue-50 transition flex flex-col items-center gap-1">
                                <i class="fas fa-clipboard-check text-sm"></i> Absensi
                            </a>
                            <a href="{{ route('orangtua.anak.dna', $s->id) }}" class="py-3 text-center text-xs font-bold text-amber-600 hover:bg-amber-50 transition flex flex-col items-center gap-1">
                                <i class="fas fa-dna text-sm"></i> DNA 360°
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- SINGLE CHILD OR SELECTED CHILD DETAILED DASHBOARD --}}
        @foreach($childrenData as $d)
            @php
                $student = $d['student'];
                $classroom = $d['classroom'];
                $avgScore = $d['avg_score'];
                $attendancePct = $d['attendance_pct'];
                $attendanceData = $d['attendance_data'];
                $todayAttendance = $d['today_attendance'];
                $attendanceHistory = $d['attendance_history'];
                $outstanding = $d['outstanding'];
                $groupedTodaySchedules = $d['grouped_today_schedules'];
                $todaySchedules = $d['today_schedules'];
                $currentSchedule = $d['current_schedule'];
                $nextSchedule = $d['next_schedule'];
                $dnaAnalysis = $d['dna_analysis'];
                $reputation = $d['reputation'];
                $rank = $d['rank'];
                $latestReportCard = $d['latest_report_card'];
                $recentAchievements = $d['recent_achievements'];
                $recentCounseling = $d['recent_counseling'];
            @endphp

            <div x-show="activeTab === '{{ $student->id }}'" class="space-y-8">

                {{-- 1. HERO GREETING BAR (ULTRA-SPACIOUS, ELEGANT & AIRY) --}}
                <div class="bg-white rounded-3xl border-2 border-indigo-100 shadow-lg relative overflow-hidden" style="border-radius: 1.75rem; border: 2px solid #e0e7ff; margin-bottom: 2rem;">
                    {{-- Rainbow Decorative Stripe --}}
                    <div class="h-3.5 w-full bg-gradient-to-r from-teal-500 via-emerald-500 via-cyan-500 via-indigo-500 to-purple-600" style="height: 0.5rem;"></div>

                    <div style="padding: 2rem 2.25rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 2rem;">
                        <div style="display: flex; align-items: center; gap: 2rem; flex: 1; min-width: 280px;">
                            <div style="position: relative; flex-shrink: 0;">
                                <div style="width: 5.5rem; height: 5.5rem; border-radius: 1.5rem; overflow: hidden; border: 4px solid #ffffff; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); background-color: #f8fafc;">
                                    <img src="{{ $student->photo_url }}" style="width: 100%; height: 100%; object-fit: cover;" alt="{{ $student->full_name }}" onerror="this.onerror=null; this.src='{{ asset('images/default-student.jpg') }}';">
                                </div>
                                <span style="position: absolute; bottom: -0.25rem; right: -0.25rem; width: 1.75rem; height: 1.75rem; border-radius: 9999px; background-color: #10b981; border: 2px solid #ffffff; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; color: #ffffff; font-weight: 900; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);" title="Siswa Aktif Terdaftar">
                                    ✓
                                </span>
                            </div>

                            <div style="flex: 1; min-width: 0; padding-left: 0.5rem;">
                                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.6rem; margin-bottom: 0.6rem;">
                                    <span style="padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; background-color: #e0e7ff; color: #312e81; border: 1px solid #c7d2fe;">
                                        👨‍👩‍👧‍👦 Portal Orang Tua
                                    </span>
                                    @if($classroom)
                                    <span style="padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; background-color: #f3e8ff; color: #581c87; border: 1px solid #e9d5ff;">
                                        🏛️ {{ $classroom->class_name }}
                                    </span>
                                    @endif
                                </div>

                                <h1 style="font-size: 1.75rem; font-weight: 900; color: #0f172a; line-height: 1.35; margin-top: 0.25rem; margin-bottom: 0.4rem;">
                                    Monitoring {{ $student->full_name }} 👋
                                </h1>

                                <p style="font-size: 0.875rem; color: #475569; font-weight: 600; display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; margin-top: 0.4rem;">
                                    <span><i class="fas fa-school" style="color: #0d9488; margin-right: 0.4rem;"></i> {{ $student->school->name ?? 'Perguruan Pembda' }}</span>
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

                {{-- 2. ATTENDANCE MONITORING ALERT FOR TODAY --}}
                <div class="bg-gradient-to-r {{ $todayAttendance ? 'from-emerald-600 via-teal-600 to-cyan-700' : 'from-amber-500 via-orange-500 to-rose-500' }} rounded-3xl shadow-xl text-white" style="border-radius: 1.75rem; padding: 0.35rem; margin-bottom: 2rem;">
                    <div style="background-color: rgba(15, 23, 42, 0.2); backdrop-filter: blur(4px); padding: 1.5rem 2rem; border-radius: 1.5rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 1.5rem; flex: 1; min-width: 260px;">
                            <div style="width: 3.75rem; height: 3.75rem; border-radius: 1.25rem; background-color: rgba(255, 255, 255, 0.25); border: 2px solid rgba(255, 255, 255, 0.35); display: flex; align-items: center; justify-content: center; font-size: 1.75rem; flex-shrink: 0;">
                                {{ $todayAttendance ? '✅' : '⏰' }}
                            </div>
                            <div style="flex: 1;">
                                <h3 style="font-weight: 900; font-size: 1.1rem; color: #ffffff; line-height: 1.3;">Status Kehadiran Hari Ini</h3>
                                <p style="font-size: 0.85rem; color: #ffffff; opacity: 0.95; font-weight: 600; margin-top: 0.3rem; line-height: 1.5;">
                                    @if($todayAttendance)
                                        Siswa telah tercatat <b>{{ strtoupper($todayAttendance->status) }}</b> pada jam <b>{{ $todayAttendance->time_in ? date('H:i', strtotime($todayAttendance->time_in)) : '-' }} WIB</b>.
                                        @if($todayAttendance->time_out)
                                            · Absen pulang jam <b>{{ date('H:i', strtotime($todayAttendance->time_out)) }} WIB</b>.
                                        @endif
                                    @else
                                        Belum ada presensi harian tercatat untuk {{ $student->full_name }} hari ini.
                                    @endif
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('orangtua.anak.absensi', $student->id) }}" style="padding: 0.75rem 1.5rem; background-color: #ffffff; color: #0f172a; border-radius: 1.25rem; font-weight: 900; font-size: 0.8rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); flex-shrink: 0;">
                            <i class="fas fa-history" style="color: #0d9488;"></i>
                            <span>Rekap Absensi Lengkap</span>
                        </a>
                    </div>
                </div>

                {{-- 3. 4 COLORFUL VIBRANT STAT CARDS --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.75rem; margin-bottom: 2.5rem;">
                    
                    {{-- Card 1: Presensi Kehadiran (Emerald Theme) --}}
                    <div class="bg-gradient-to-br from-emerald-500 to-teal-700 text-white shadow-lg" style="padding: 1.75rem; border-radius: 1.75rem; border: 2px solid rgba(52, 211, 153, 0.5); display: flex; flex-direction: column; justify-content: space-between; min-height: 160px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                            <span style="font-size: 0.75rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; color: #d1fae5;">Presensi Kehadiran</span>
                            <div style="width: 3rem; height: 3rem; border-radius: 1rem; background-color: #ffffff; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); flex-shrink: 0;">
                                <i class="fas fa-user-check"></i>
                            </div>
                        </div>
                        <div>
                            <p style="font-size: 2.25rem; font-weight: 900; color: #ffffff; line-height: 1; letter-spacing: -0.025em;">{{ $attendancePct }}%</p>
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

                    {{-- Card 3: DNA Potensi / Reputasi (Amber Theme) --}}
                    <div class="bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg" style="padding: 1.75rem; border-radius: 1.75rem; border: 2px solid rgba(251, 191, 36, 0.5); display: flex; flex-direction: column; justify-content: space-between; min-height: 160px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                            <span style="font-size: 0.75rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; color: #fef3c7;">DNA Potensi & Karir</span>
                            <div style="width: 3rem; height: 3rem; border-radius: 1rem; background-color: #ffffff; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); flex-shrink: 0;">
                                <i class="fas fa-dna"></i>
                            </div>
                        </div>
                        <div>
                            <p style="font-size: 1.25rem; font-weight: 900; color: #ffffff; line-height: 1.2; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                {{ $dnaAnalysis['archetype']['title'] ?? 'Profil Gaya Belajar' }}
                            </p>
                            <p style="font-size: 0.75rem; font-weight: 700; color: #fef3c7; margin-top: 0.6rem; line-height: 1.4;">
                                Rank #{{ $rank }} Reputasi &bull; {{ $reputation->level_name ?? 'Siswa Pembda' }}
                            </p>
                        </div>
                    </div>

                    {{-- Card 4: Status Pembayaran Biaya (Purple/Rose Theme) --}}
                    <div class="bg-gradient-to-br from-purple-600 to-pink-600 text-white shadow-lg" style="padding: 1.75rem; border-radius: 1.75rem; border: 2px solid rgba(232, 121, 249, 0.5); display: flex; flex-direction: column; justify-content: space-between; min-height: 160px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                            <span style="font-size: 0.75rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; color: #fae8ff;">Status Biaya Pendidikan</span>
                            <div style="width: 3rem; height: 3rem; border-radius: 1rem; background-color: #ffffff; color: #9333ea; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); flex-shrink: 0;">
                                <i class="fas fa-wallet"></i>
                            </div>
                        </div>
                        <div>
                            @if($outstanding > 0)
                            <p style="font-size: 1.75rem; font-weight: 900; color: #ffffff; line-height: 1.1;">Rp {{ number_format($outstanding, 0, ',', '.') }}</p>
                            <p style="font-size: 0.75rem; font-weight: 700; color: #fbcfe8; margin-top: 0.6rem; line-height: 1.4;">Tunggakan s.d. Bulan Ini</p>
                            @else
                            <p style="font-size: 1.75rem; font-weight: 900; color: #ffffff; line-height: 1.1;">Lunas s.d. Bulan Ini</p>
                            <p style="font-size: 0.75rem; font-weight: 700; color: #a7f3d0; margin-top: 0.6rem; line-height: 1.4;">Bebas tunggakan seluruh biaya (SPP, dll.)</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- 4. JADWAL PELAJARAN HARI INI (LIVE REAL-TIME WIDGET) --}}
                <div class="bg-white shadow-xl" style="border-radius: 1.75rem; border: 2px solid #e0e7ff; overflow: hidden; margin-bottom: 2.5rem;">
                    {{-- Header Bar --}}
                    <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-purple-800 text-white" style="padding: 1.75rem 2.25rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 1.5rem;">
                            <div style="width: 3.75rem; height: 3.75rem; border-radius: 1.25rem; background-color: #ffffff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-size: 1.85rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); flex-shrink: 0; font-weight: 900;">
                                📅
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.35rem;">
                                    <span style="padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 900; text-transform: uppercase; background-color: #fbbf24; color: #0f172a;">
                                        {{ now()->translatedFormat('l, d F Y') }}
                                    </span>
                                    @if($currentSchedule)
                                    <span style="padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 900; text-transform: uppercase; background-color: #34d399; color: #022c22;" class="animate-pulse">
                                        🟢 Sedang Berlangsung
                                    </span>
                                    @endif
                                </div>
                                <h2 style="font-size: 1.35rem; font-weight: 900; color: #ffffff; line-height: 1.2;">
                                    Jadwal Pelajaran {{ $student->full_name }} Hari Ini
                                </h2>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span style="font-size: 0.8rem; font-weight: 700; color: #c7d2fe;">Total {{ $todaySchedules->count() }} Sesi Pelajaran</span>
                            <a href="{{ route('orangtua.anak.jadwal', $student->id) }}" style="padding: 0.75rem 1.25rem; background-color: #ffffff; color: #312e81; border-radius: 1rem; font-weight: 900; font-size: 0.75rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                                <span>Lihat Jadwal Lengkap</span>
                                <i class="fas fa-arrow-right" style="font-size: 0.7rem;"></i>
                            </a>
                        </div>
                    </div>

                    {{-- Schedule Grid --}}
                    <div style="padding: 2rem;">
                        @if($groupedTodaySchedules->count() > 0)
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                                @php
                                    $colorPalettes = [
                                        ['border' => '#93c5fd', 'bg' => 'from-blue-500 to-indigo-600', 'icon' => '📐'],
                                        ['border' => '#d8b4fe', 'bg' => 'from-purple-500 to-pink-600', 'icon' => '📖'],
                                        ['border' => '#6ee7b7', 'bg' => 'from-emerald-500 to-teal-600', 'icon' => '🧪'],
                                        ['border' => '#fcd34d', 'bg' => 'from-amber-500 to-orange-600', 'icon' => '💻'],
                                        ['border' => '#fda4af', 'bg' => 'from-rose-500 to-red-600', 'icon' => '⚽'],
                                        ['border' => '#67e8f9', 'bg' => 'from-cyan-500 to-blue-600', 'icon' => '🎨'],
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

                                    <div class="bg-white shadow-sm hover:shadow-lg transition" style="padding: 1.5rem; border-radius: 1.5rem; border: 2px solid {{ $isCurrent ? '#10b981' : ($isNext ? '#f59e0b' : '#e2e8f0') }}; display: flex; flex-direction: column; justify-content: space-between; {{ $isCurrent ? 'box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.2);' : '' }}">
                                        
                                        {{-- Top Status Pill & Time Badge --}}
                                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 1.25rem; padding-bottom: 0.85rem; border-bottom: 2px solid #f1f5f9;">
                                            <div>
                                                <span style="padding: 0.45rem 1rem; border-radius: 9999px; background-color: #0f172a; color: #ffffff; font-weight: 900; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                                    <i class="far fa-clock" style="color: #fbbf24;"></i>
                                                    <span>{{ $sStart }} - {{ $sEnd }}</span>
                                                </span>
                                            </div>

                                            <div>
                                                @if($isCurrent)
                                                <span style="padding: 0.4rem 0.9rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; background-color: #10b981; color: #ffffff; display: inline-flex; align-items: center; gap: 0.4rem;" class="animate-pulse">
                                                    <i class="fas fa-circle" style="font-size: 0.4rem;"></i> Berlangsung
                                                </span>
                                                @elseif($isNext)
                                                <span style="padding: 0.4rem 0.9rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; background-color: #f59e0b; color: #ffffff; display: inline-flex; align-items: center; gap: 0.4rem;">
                                                    ⏳ Berikutnya
                                                </span>
                                                @elseif($isPast)
                                                <span style="padding: 0.4rem 0.9rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; background-color: #e2e8f0; color: #334155; display: inline-flex; align-items: center; gap: 0.4rem;">
                                                    ✓ Selesai
                                                </span>
                                                @else
                                                <span style="padding: 0.4rem 0.9rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; background-color: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; display: inline-flex; align-items: center; gap: 0.4rem;">
                                                    Mendatang
                                                </span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Subject Content --}}
                                        <div style="margin-top: 0.5rem; margin-bottom: 0.5rem;">
                                            @foreach($schedulesAtTime as $s)
                                            <div style="display: flex; align-items: flex-start; gap: 1.25rem; margin-bottom: 1rem;">
                                                <div class="bg-gradient-to-br {{ $palette['bg'] }} text-white" style="width: 3.25rem; height: 3.25rem; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                                                    {{ $palette['icon'] }}
                                                </div>
                                                <div style="flex: 1; min-width: 0; padding-top: 0.2rem;">
                                                    <h3 style="font-size: 1.05rem; font-weight: 900; color: #0f172a; line-height: 1.35; margin-bottom: 0.35rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                        {{ $s->subject->subject_name ?? ($s->subject->name ?? 'Mata Pelajaran') }}
                                                    </h3>
                                                    <p style="font-size: 0.8rem; color: #475569; font-weight: 700; display: flex; align-items: center; gap: 0.5rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                        <i class="fas fa-chalkboard-teacher" style="color: #6366f1;"></i>
                                                        <span>{{ $s->teacher->user->name ?? ($s->teacher->full_name ?? 'Guru Pengampu') }}</span>
                                                    </p>
                                                </div>
                                            </div>

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
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="padding: 3rem 2rem; text-align: center; background-color: #f8fafc; border-radius: 1.75rem; border: 2px dashed #cbd5e1;">
                                <div style="width: 4.5rem; height: 4.5rem; background-color: #ffffff; color: #94a3b8; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem auto; font-size: 2.25rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                                    ☕
                                </div>
                                <h3 style="font-weight: 900; color: #1e293b; font-size: 1.15rem; margin-bottom: 0.4rem;">Tidak Ada Jadwal Pelajaran Hari Ini</h3>
                                <p style="font-size: 0.85rem; color: #64748b; max-width: 450px; margin: 0 auto; line-height: 1.6;">Hari ini tidak ada sesi belajar mengajar di kelas untuk {{ $student->full_name }}.</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- 5. WIDGET DNA POTENSI & KARIR SISWA 360° --}}
                @if($dnaAnalysis)
                <div class="bg-white shadow-lg relative overflow-hidden" style="border-radius: 1.75rem; border: 2px solid #e0e7ff; margin-bottom: 2.5rem;">
                    <div class="h-3 w-full bg-gradient-to-r from-purple-600 via-pink-500 to-amber-500" style="height: 0.4rem;"></div>

                    <div style="padding: 2.25rem; display: flex; flex-direction: column; gap: 2rem;">
                        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.5rem;">
                            <div style="space-y: 0.75rem; max-width: 700px;">
                                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; margin-bottom: 0.6rem;">
                                    <span class="bg-gradient-to-r {{ $dnaAnalysis['archetype']['color'] ?? 'from-purple-600 to-indigo-600' }} text-white shadow-sm" style="padding: 0.4rem 1.25rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 0.5rem;">
                                        <i class="fas {{ $dnaAnalysis['archetype']['badge_icon'] ?? 'fa-brain' }}"></i> {{ $dnaAnalysis['archetype']['title'] ?? 'Profil DNA' }}
                                    </span>
                                    <span style="font-size: 0.75rem; font-weight: 900; color: #312e81; background-color: #eef2ff; border: 1px solid #c7d2fe; padding: 0.4rem 1rem; border-radius: 9999px;">
                                        ✨ Akurasi Profil: {{ $dnaAnalysis['confidence_score'] ?? 85 }}%
                                    </span>
                                </div>

                                <h2 style="font-size: 1.5rem; font-weight: 900; color: #0f172a; line-height: 1.3; margin-top: 0.4rem; margin-bottom: 0.4rem;">
                                    DNA Potensi Belajar & Minat Karier 360° {{ $student->full_name }}
                                </h2>

                                <p style="font-size: 0.875rem; color: #475569; font-weight: 600; line-height: 1.6;">
                                    "{{ $dnaAnalysis['archetype']['tagline'] ?? '' }}" &mdash; {{ $dnaAnalysis['archetype']['description'] ?? '' }}
                                </p>
                            </div>

                            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.85rem;">
                                <a href="{{ route('orangtua.anak.dna', $student->id) }}" class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md hover:opacity-95 transition" style="padding: 0.85rem 1.5rem; border-radius: 1.25rem; font-weight: 900; font-size: 0.8rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.6rem;">
                                    <i class="fas fa-radar"></i>
                                    <span>Buka Radar DNA 360°</span>
                                </a>
                                <a href="{{ route('orangtua.anak.dna.pdf', $student->id) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-800 transition" style="padding: 0.85rem 1.5rem; border-radius: 1.25rem; font-weight: 900; font-size: 0.8rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.6rem; border: 1px solid #cbd5e1;">
                                    <i class="fas fa-file-pdf" style="color: #e11d48;"></i>
                                    <span>Unduh Sertifikat PDF</span>
                                </a>
                            </div>
                        </div>

                        {{-- 6 Dimension Meters --}}
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 1rem; margin-top: 0.5rem;">
                            <div style="background-color: #eff6ff; border: 2px solid #bfdbfe; border-radius: 1.25rem; padding: 1.25rem 0.75rem; text-align: center;">
                                <p style="font-size: 0.7rem; font-weight: 900; color: #1d4ed8; text-transform: uppercase; margin-bottom: 0.35rem;">Logika</p>
                                <p style="font-size: 1.5rem; font-weight: 900; color: #1e3a8a;">{{ $dnaAnalysis['scores']['logic'] ?? 0 }}</p>
                            </div>
                            <div style="background-color: #ecfdf5; border: 2px solid #a7f3d0; border-radius: 1.25rem; padding: 1.25rem 0.75rem; text-align: center;">
                                <p style="font-size: 0.7rem; font-weight: 900; color: #047857; text-transform: uppercase; margin-bottom: 0.35rem;">Bahasa</p>
                                <p style="font-size: 1.5rem; font-weight: 900; color: #064e3b;">{{ $dnaAnalysis['scores']['communication'] ?? 0 }}</p>
                            </div>
                            <div style="background-color: #eef2ff; border: 2px solid #c7d2fe; border-radius: 1.25rem; padding: 1.25rem 0.75rem; text-align: center;">
                                <p style="font-size: 0.7rem; font-weight: 900; color: #4338ca; text-transform: uppercase; margin-bottom: 0.35rem;">Vokasi</p>
                                <p style="font-size: 1.5rem; font-weight: 900; color: #312e81;">{{ $dnaAnalysis['scores']['technical'] ?? 0 }}</p>
                            </div>
                            <div style="background-color: #fffbeb; border: 2px solid #fde68a; border-radius: 1.25rem; padding: 1.25rem 0.75rem; text-align: center;">
                                <p style="font-size: 0.7rem; font-weight: 900; color: #b45309; text-transform: uppercase; margin-bottom: 0.35rem;">Sosial</p>
                                <p style="font-size: 1.5rem; font-weight: 900; color: #78350f;">{{ $dnaAnalysis['scores']['social'] ?? 0 }}</p>
                            </div>
                            <div style="background-color: #faf5ff; border: 2px solid #e9d5ff; border-radius: 1.25rem; padding: 1.25rem 0.75rem; text-align: center;">
                                <p style="font-size: 0.7rem; font-weight: 900; color: #7e22ce; text-transform: uppercase; margin-bottom: 0.35rem;">Kreatif</p>
                                <p style="font-size: 1.5rem; font-weight: 900; color: #581c87;">{{ $dnaAnalysis['scores']['creative'] ?? 0 }}</p>
                            </div>
                            <div style="background-color: #fff1f2; border: 2px solid #fecdd3; border-radius: 1.25rem; padding: 1.25rem 0.75rem; text-align: center;">
                                <p style="font-size: 0.7rem; font-weight: 900; color: #be123c; text-transform: uppercase; margin-bottom: 0.35rem;">Disiplin</p>
                                <p style="font-size: 1.5rem; font-weight: 900; color: #881337;">{{ $dnaAnalysis['scores']['discipline'] ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- 6. TWO COLUMNS: RIWAYAT PRESENSI & RIWAYAT NILAI / TAGIHAN --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
                    
                    {{-- LEFT COLUMN: PRESENSI & KONSELING/PRESTASI --}}
                    <div style="display: flex; flex-direction: column; gap: 2rem;">
                        
                        {{-- Riwayat Presensi Terakhir --}}
                        <div class="bg-white shadow-sm" style="border-radius: 1.75rem; border: 2px solid #e2e8f0; overflow: hidden;">
                            <div style="padding: 1.25rem 1.75rem; border-bottom: 2px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; background-color: #f8fafc;">
                                <h3 style="font-size: 1rem; font-weight: 900; color: #0f172a; display: flex; align-items: center; gap: 0.6rem;">
                                    <i class="fas fa-history" style="color: #059669;"></i>
                                    <span>Riwayat Presensi Terakhir</span>
                                </h3>
                                <a href="{{ route('orangtua.anak.absensi', $student->id) }}" style="font-size: 0.75rem; font-weight: 900; color: #4f46e5; text-decoration: none;">Detail Lengkap &rarr;</a>
                            </div>
                            <div style="padding: 1.5rem 1.75rem;">
                                <div style="overflow-x: auto;">
                                    <table style="width: 100%; font-size: 0.8rem; border-collapse: collapse;">
                                        <thead>
                                            <tr style="background-color: #f1f5f9; color: #334155; font-weight: 900; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.05em;">
                                                <th style="padding: 0.85rem 1rem; text-align: left;">Hari / Tanggal</th>
                                                <th style="padding: 0.85rem 1rem; text-align: center;">Status</th>
                                                <th style="padding: 0.85rem 1rem; text-align: center;">Jam Masuk</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($attendanceHistory as $att)
                                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                                <td style="padding: 0.85rem 1rem; font-weight: 900; color: #0f172a;">
                                                    {{ $att->date->translatedFormat('l, d M Y') }}
                                                </td>
                                                <td style="padding: 0.85rem 1rem; text-align: center;">
                                                    @php
                                                        $statusStyle = match($att->status) {
                                                            'hadir' => 'background-color: #d1fae5; color: #064e3b; border: 1px solid #a7f3d0;',
                                                            'sakit' => 'background-color: #fef3c7; color: #78350f; border: 1px solid #fde68a;',
                                                            'izin' => 'background-color: #dbeafe; color: #1e3a8a; border: 1px solid #bfdbfe;',
                                                            'alpha' => 'background-color: #ffe4e6; color: #881337; border: 1px solid #fecdd3;',
                                                            default => 'background-color: #f1f5f9; color: #334155;'
                                                        };
                                                    @endphp
                                                    <span style="padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 900; font-size: 0.7rem; text-transform: uppercase; {{ $statusStyle }}">
                                                        {{ strtoupper($att->status) }}
                                                    </span>
                                                </td>
                                                <td style="padding: 0.85rem 1rem; text-align: center; font-weight: 700; color: #475569;">
                                                    {{ $att->time_in ? date('H:i', strtotime($att->time_in)) : '-' }}
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="3" style="padding: 2rem; text-align: center; color: #94a3b8;">Belum ada catatan presensi.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Sorotan Prestasi & Pembinaan --}}
                        <div class="bg-white shadow-sm" style="padding: 1.75rem; border-radius: 1.75rem; border: 2px solid #e0e7ff;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
                                <h3 style="font-size: 1rem; font-weight: 900; color: #0f172a; display: flex; align-items: center; gap: 0.6rem;">
                                    <i class="fas fa-award" style="color: #d97706;"></i>
                                    <span>Prestasi & Catatan Konseling</span>
                                </h3>
                                <a href="{{ route('orangtua.anak.konseling', $student->id) }}" style="font-size: 0.75rem; font-weight: 900; color: #4f46e5; text-decoration: none;">Lihat Semua &rarr;</a>
                            </div>

                            @if($recentAchievements->isNotEmpty() || $recentCounseling->isNotEmpty())
                                <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                                    @foreach($recentAchievements as $ach)
                                    <div class="bg-amber-50/70 border border-amber-200 rounded-2xl p-3.5 flex items-center justify-between">
                                        <div>
                                            <span class="bg-amber-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full uppercase">🏆 Prestasi</span>
                                            <p class="font-black text-slate-900 text-xs mt-1">{{ $ach->title }}</p>
                                            <p class="text-[11px] text-amber-800 font-medium">{{ $ach->achievement_date ? \Carbon\Carbon::parse($ach->achievement_date)->translatedFormat('d M Y') : '' }} · Level {{ ucfirst($ach->level) }}</p>
                                        </div>
                                    </div>
                                    @endforeach

                                    @foreach($recentCounseling as $coun)
                                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3.5 flex items-center justify-between">
                                        <div>
                                            <span class="bg-indigo-600 text-white text-[10px] font-black px-2 py-0.5 rounded-full uppercase">📝 Catatan BK</span>
                                            <p class="font-black text-slate-900 text-xs mt-1">{{ $coun->title }}</p>
                                            <p class="text-[11px] text-slate-600 font-medium">{{ $coun->incident_date ? \Carbon\Carbon::parse($coun->incident_date)->translatedFormat('d M Y') : '' }} · {{ $coun->counselor->name ?? 'Guru BK' }}</p>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-6 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                    <p class="text-xs text-slate-500 font-semibold">Belum ada catatan konseling atau prestasi khusus.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- RIGHT COLUMN: TAGIHAN & RAPOR DIGITAL --}}
                    <div style="display: flex; flex-direction: column; gap: 2rem;">
                        
                        {{-- Widget Tagihan SPP & Biaya --}}
                        <div class="bg-white shadow-sm" style="padding: 1.75rem; border-radius: 1.75rem; border: 2px solid #e0e7ff;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
                                <h3 style="font-size: 1rem; font-weight: 900; color: #0f172a; display: flex; align-items: center; gap: 0.6rem;">
                                    <i class="fas fa-file-invoice-dollar" style="color: #9333ea;"></i>
                                    <span>Ringkasan SPP & Biaya</span>
                                </h3>
                                <a href="{{ route('orangtua.anak.tagihan', $student->id) }}" style="font-size: 0.75rem; font-weight: 900; color: #4f46e5; text-decoration: none;">Detail Tagihan &rarr;</a>
                            </div>

                            <div class="bg-purple-50/60 border border-purple-200 rounded-2xl p-4 mb-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-[11px] font-bold text-purple-700 uppercase">Tunggakan s.d. Bulan Ini</span>
                                        <p class="text-xl font-black text-purple-900 mt-0.5">
                                            {{ $outstanding > 0 ? 'Rp '.number_format($outstanding, 0, ',', '.') : '✅ Lunas (Rp 0)' }}
                                        </p>
                                    </div>
                                    <a href="{{ route('orangtua.anak.tagihan', $student->id) }}" class="bg-purple-600 hover:bg-purple-700 text-white font-black text-xs px-3.5 py-2 rounded-xl transition shadow-sm">
                                        Bayar / Detail
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Widget Rapor Digital --}}
                        @if($showReportCard && $latestReportCard)
                        <div class="bg-gradient-to-br from-indigo-900 to-slate-900 text-white shadow-xl p-6" style="border-radius: 1.75rem;">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-500/30 border border-indigo-400/40 flex items-center justify-center text-xl">
                                    📜
                                </div>
                                <div>
                                    <span class="text-[10px] font-black uppercase text-indigo-300 tracking-wider">Rapor Digital Tersedia</span>
                                    <h4 class="font-extrabold text-sm text-white">Rapor Semester {{ $latestReportCard->semester->semester_name ?? '' }}</h4>
                                </div>
                            </div>
                            <p class="text-xs text-slate-300 leading-relaxed mb-4">
                                Laporan hasil belajar {{ $student->full_name }} resmi dipublikasikan oleh pihak sekolah. Anda dapat mengunduh salinan berkas PDF kapan saja.
                            </p>
                            <a href="{{ route('orangtua.anak.raport.download', ['student' => $student->id, 'reportCard' => $latestReportCard->id]) }}" class="w-full bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black text-xs py-3 px-4 rounded-xl transition flex items-center justify-center gap-2 shadow-md">
                                <i class="fas fa-download text-sm"></i>
                                <span>Unduh Berkas Rapor PDF</span>
                            </a>
                        </div>
                        @endif

                    </div>
                </div>

            </div>
        @endforeach

    @endif
</div>
@endsection
