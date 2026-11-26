@extends('layouts.guru')
@section('title', 'Dashboard - Portal Guru')

@section('content')
@php
    $userRole = session('active_role', auth()->user()?->role);
    $isPegawaiOnly = ($userRole === 'pegawai');
    $isNotCheckedOut = empty($todayAttendance) || !$todayAttendance->time_out || $todayAttendance->time_out === '00:00:00' || $todayAttendance->time_out === '00:00';
    $__circleGuruProfile = \App\Models\CircleProfile::where('user_id', Auth::id())->first();
    $__circleGuruUsername = $__circleGuruProfile?->username ?? Auth::user()->username;
    $isYayasanUser = $teacher->user && ($teacher->user->username === 'yulzega' || str_contains(strtolower($teacher->user->name ?? ''), 'yulianus zega'));
@endphp

<style>
    .gd-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }
    .gd-kpi-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
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
    .gd-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
    }
    .gd-bento-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(0, 1fr);
        gap: 1.25rem;
        align-items: start;
    }
    @media (max-width: 1024px) {
        .gd-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .gd-bento-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }
    @media (max-width: 560px) {
        .gd-kpi-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }
    .gd-tab-btn {
        padding: 0.5rem 0.95rem;
        border-radius: 0.75rem;
        font-size: 0.92rem;
        font-weight: 700;
        transition: all 0.15s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        border: 1px solid transparent;
    }
    .gd-tab-btn.is-active {
        background: #0f172a;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.18);
    }
    .gd-tab-btn:not(.is-active) {
        color: #475569;
        background: #f1f5f9;
    }
    .gd-tab-btn:not(.is-active):hover {
        background: #e2e8f0;
        color: #0f172a;
    }
</style>

<div class="gd-wrap" x-data="{ mainTab: '{{ $isPegawaiOnly ? 'aksi' : 'jadwal' }}' }">

    {{-- 1. HERO WELCOME (DEEP SLATE + EMERALD ACCENT — SELARAS PORTAL SISWA) --}}
    <div class="gd-hero">
        <div class="flex flex-wrap items-center justify-between gap-5 relative z-10">
            <div class="flex items-center gap-4 min-w-0 flex-1">
                <div class="relative shrink-0">
                    <div class="w-16 h-16 rounded-2xl overflow-hidden border-2 border-emerald-400 bg-slate-800 shadow-lg">
                        <img src="{{ $teacher->photo_url }}" class="w-full h-full object-cover" alt="{{ $teacher->full_name }}">
                    </div>
                    <span class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full bg-emerald-400 border-2 border-slate-900" title="Aktif"></span>
                </div>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-400 text-slate-950">
                            {{ $teacher->position ?? 'Tenaga Pendidik' }}
                        </span>
                        @if($activeYear)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/10 text-slate-200 border border-white/15">
                            TP {{ preg_replace('/^TP\.?\s*/i', '', trim((string) ($academicYear->year ?? $activeYear->year))) }}
                        </span>
                        @endif
                        <span class="text-xs sm:text-sm text-slate-300 font-medium">
                            {{ now()->translatedFormat('l, d F Y') }}
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-white leading-tight m-0">
                        Selamat Datang, {{ explode(' ', trim($teacher->full_name))[0] }}!
                    </h1>
                    <p class="text-sm text-slate-300 font-medium mt-0.5 truncate">
                        {{ $teacher->school->name ?? 'Perguruan Pembda' }} &bull; {{ $weeklyScheduleCount }} sesi mengajar minggu ini
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('circle.profile', ['username' => $__circleGuruUsername]) }}"
                   title="Buka Panggung & Profil Publik PembdaCIRCLE (@{{ $__circleGuruUsername }})"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-pink-500 via-rose-500 to-amber-500 hover:brightness-110 text-white text-xs sm:text-sm font-bold shadow-lg shadow-pink-500/25 transition">
                    <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                    <i class="fas fa-rocket"></i>
                    <span>PembdaCIRCLE</span>
                </a>
                @if(!$isPegawaiOnly)
                <a href="{{ route('guru.lms.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs sm:text-sm font-semibold border border-white/15 transition">
                    <i class="fas fa-laptop-code text-emerald-300"></i>
                    <span>Buka LMS</span>
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- KAMPANYE PEMILIHAN OSIS (OTOMATIS HILANG SAAT SELESAI) --}}
    @include('components.osis-campaign-widget', ['variant' => 'teacher', 'campaign' => $osisCampaign ?? null])

    {{-- 2. STRIP PRESENSI MANDIRI GPS --}}
    @if(empty($todayAttendance) || $isNotCheckedOut)
    <div class="gd-card p-4 sm:p-5 bg-gradient-to-r from-emerald-50 via-teal-50/60 to-white border-emerald-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center text-xl shadow-md shrink-0">
                    <i class="fas fa-fingerprint"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-0.5">
                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-100 text-emerald-800">
                            Presensi Mandiri GPS
                        </span>
                        @if(empty($todayAttendance))
                            <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-100 text-rose-700">
                                Belum Presensi Masuk
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-teal-100 text-teal-800">
                                Masuk: {{ substr($todayAttendance->time_in, 0, 5) }} WIB
                            </span>
                        @endif
                    </div>
                    <h3 class="font-bold text-slate-900 text-base">
                        {{ empty($todayAttendance) ? 'Waktunya Presensi Kehadiran Guru' : 'Presensi Masuk Tercatat — Jangan Lupa Presensi Pulang' }}
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-600 mt-0.5">
                        {{ empty($todayAttendance)
                            ? 'Konfirmasi kehadiran GPS Anda hari ini langsung dari perangkat.'
                            : 'Anda tercatat masuk pukul ' . substr($todayAttendance->time_in, 0, 5) . ' WIB. Lakukan presensi pulang sebelum meninggalkan sekolah.' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
                <button type="button" onclick="performGuruGpsScan(this)"
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm shadow-sm transition inline-flex items-center gap-2">
                    @if(empty($todayAttendance))
                        <i class="fas fa-location-dot"></i>
                        <span>Presensi Masuk</span>
                    @else
                        <i class="fas fa-right-from-bracket"></i>
                        <span>Presensi Pulang</span>
                    @endif
                </button>
                <a href="{{ route('guru.absensi.saya') }}"
                   class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs sm:text-sm border border-slate-300 transition">
                    Rekap Saya
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- 3. SURAT EDARAN YAYASAN TERBARU --}}
    @if(isset($foundationLetters) && $foundationLetters->isNotEmpty())
    <div class="gd-card p-4 sm:p-5 bg-gradient-to-r from-indigo-50/70 to-white border-indigo-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-lg shrink-0 shadow-sm">
                    <i class="fas fa-file-signature"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-indigo-100 text-indigo-800">Surat & Edaran Yayasan</span>
                        @if($foundationLetters->first()->deadline_date)
                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-100 text-rose-700">
                            <i class="fas fa-clock mr-1"></i>Tenggat: {{ \Carbon\Carbon::parse($foundationLetters->first()->deadline_date)->translatedFormat('d M Y') }}
                        </span>
                        @endif
                    </div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900 leading-snug">{{ $foundationLetters->first()->title }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        No. {{ $foundationLetters->first()->letter_number }} &bull; Terbit {{ \Carbon\Carbon::parse($foundationLetters->first()->effective_date)->translatedFormat('d F Y') }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('admin.letters.show', $foundationLetters->first()->id) }}"
                   class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs sm:text-sm transition inline-flex items-center gap-1.5">
                    <i class="fas fa-book-open"></i> Baca Surat
                </a>
                <a href="{{ route('admin.letters.index') }}"
                   class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs sm:text-sm border border-slate-300 transition">
                    Semua ({{ $foundationLetters->count() }})
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- 4. 4 KPI CARDS --}}
    <div class="gd-kpi-grid">
        <a href="{{ route('guru.kelas') }}" class="gd-kpi-card">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Kelas</p>
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ $classrooms->count() }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Diampu semester ini</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-chalkboard"></i>
            </div>
        </a>

        <a href="{{ route('guru.kelas') }}" class="gd-kpi-card">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Siswa</p>
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ $totalStudents }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Di semua kelas mengajar</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-user-graduate"></i>
            </div>
        </a>

        <a href="{{ route('guru.nilai') }}" class="gd-kpi-card">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Nilai Diinput</p>
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ $gradesCount }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Rekam nilai tersimpan</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-chart-bar"></i>
            </div>
        </a>

        <a href="{{ route('guru.jadwal') }}" class="gd-kpi-card">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Jadwal Hari Ini</p>
                <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ $todaySchedules->count() }}</p>
                @if($nextSchedule)
                    <p class="text-xs font-semibold text-amber-600 mt-0.5 truncate">Berikutnya: {{ $nextSchedule->timeSlot->start_time ?? $nextSchedule->start_time ?? '-' }}</p>
                @elseif($currentSchedule)
                    <p class="text-xs font-semibold text-emerald-600 mt-0.5">Sedang mengajar</p>
                @else
                    <p class="text-xs text-slate-500 mt-0.5">{{ $weeklyScheduleCount }} sesi/minggu</p>
                @endif
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-calendar-check"></i>
            </div>
        </a>
    </div>

    {{-- 5. BENTO GRID 2 KOLOM --}}
    <div class="gd-bento-grid">

        {{-- KOLOM KIRI: TAB JADWAL HARI INI & AKSI CEPAT --}}
        <div class="gd-card p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-200 mb-5">
                <div class="flex flex-wrap items-center gap-2">
                    @if(!$isPegawaiOnly)
                    <button type="button" @click="mainTab = 'jadwal'" :class="mainTab === 'jadwal' ? 'is-active' : ''" class="gd-tab-btn">
                        <i class="fas fa-calendar-day"></i>
                        <span>Jadwal Hari Ini</span>
                        <span class="px-1.5 py-0.5 rounded-md text-xs bg-black/10">{{ $todaySchedules->count() }}</span>
                    </button>
                    @endif
                    <button type="button" @click="mainTab = 'aksi'" :class="mainTab === 'aksi' ? 'is-active' : ''" class="gd-tab-btn">
                        <i class="fas fa-bolt"></i>
                        <span>Aksi Cepat</span>
                    </button>
                </div>
                @if(!$isPegawaiOnly)
                <a href="{{ route('guru.jadwal') }}" class="text-xs sm:text-sm font-bold text-emerald-700 hover:text-emerald-800 inline-flex items-center gap-1">
                    Jadwal Lengkap <i class="fas fa-arrow-right text-xs"></i>
                </a>
                @endif
            </div>

            {{-- TAB 1: TIMELINE JADWAL HARI INI --}}
            @if(!$isPegawaiOnly)
            <div x-show="mainTab === 'jadwal'">
                @if($todaySchedules->count() > 0)
                    <div class="relative pl-6 space-y-4">
                        <div class="absolute left-2.5 top-2 bottom-2 w-0.5 bg-slate-200"></div>
                        @foreach($groupedTodaySchedules as $timeKey => $schedulesAtTime)
                            @php
                                $first = $schedulesAtTime->first();
                                $sStart = $first->timeSlot->start_time ?? $first->start_time ?? null;
                                $sEnd = $first->timeSlot->end_time ?? $first->end_time ?? null;

                                $isCurrent = false;
                                $isNext = false;
                                foreach($schedulesAtTime as $s) {
                                    if ($currentSchedule && $currentSchedule->id === $s->id) $isCurrent = true;
                                    if ($nextSchedule && $nextSchedule->id === $s->id) $isNext = true;
                                }
                                $isPast = $sEnd && $currentTime > $sEnd;
                            @endphp
                            <div class="relative">
                                <span class="absolute -left-[22px] top-4 w-3.5 h-3.5 rounded-full border-2 border-white shadow-xs {{ $isCurrent ? 'bg-emerald-500 ring-4 ring-emerald-100' : ($isNext ? 'bg-amber-400 ring-4 ring-amber-100' : ($isPast ? 'bg-slate-300' : 'bg-indigo-500')) }}"></span>

                                <div class="rounded-2xl p-4 border transition {{ $isCurrent ? 'bg-emerald-50/70 border-emerald-300 shadow-xs' : ($isNext ? 'bg-amber-50/70 border-amber-300' : ($isPast ? 'bg-slate-50 border-slate-200 opacity-75' : 'bg-white border-slate-200 hover:border-slate-300')) }}">
                                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                        <div class="flex items-center gap-2">
                                            @if($isCurrent)
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-600 text-white">Berlangsung</span>
                                            @elseif($isNext)
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-400 text-slate-950">Berikutnya</span>
                                            @elseif($isPast)
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-200 text-slate-600">Selesai</span>
                                            @endif
                                        </div>
                                        <span class="text-xs sm:text-sm font-bold text-slate-700 bg-white px-2.5 py-1 rounded-lg border border-slate-200">
                                            <i class="far fa-clock text-emerald-600 mr-1"></i>{{ \Carbon\Carbon::parse($sStart)->format('H:i') }} – {{ \Carbon\Carbon::parse($sEnd)->format('H:i') }}
                                        </span>
                                    </div>

                                    <div class="space-y-1.5">
                                        @foreach($schedulesAtTime as $s)
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <p class="font-bold text-slate-900 text-sm sm:text-base">{{ $s->subject->subject_name ?? $s->subject->name ?? '-' }}</p>
                                            <span class="text-xs font-bold bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg">
                                                <i class="fas fa-users mr-1 text-indigo-600"></i>{{ $s->classroom->class_name ?? '-' }}
                                            </span>
                                        </div>
                                        @endforeach
                                    </div>

                                    @if($first->room || ($first->duration_slots && $first->duration_slots > 1))
                                    <div class="flex flex-wrap items-center gap-2 mt-3 pt-2.5 border-t border-slate-200/70 text-xs text-slate-600 font-medium">
                                        @if($first->room)
                                        <span class="px-2 py-0.5 rounded-md bg-white border border-slate-200"><i class="fas fa-door-open mr-1 text-emerald-600"></i>Ruang: {{ $first->room }}</span>
                                        @endif
                                        @if($first->duration_slots && $first->duration_slots > 1)
                                        <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 font-bold">{{ $first->duration_slots }} JP</span>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-2xl">
                            <i class="fas fa-mug-hot"></i>
                        </div>
                        <p class="text-base font-bold text-slate-800">Tidak ada jadwal mengajar hari ini</p>
                        <p class="text-sm text-slate-500 mt-1">Gunakan waktu untuk menyiapkan modul LMS atau evaluasi nilai siswa.</p>
                    </div>
                @endif
            </div>
            @endif

            {{-- TAB 2: AKSI CEPAT GURU --}}
            <div x-show="mainTab === 'aksi'" x-cloak>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @if($isPegawaiOnly)
                        <a href="{{ route('guru.absensi.saya') }}" class="p-4 rounded-2xl border border-slate-200 hover:border-emerald-300 hover:bg-emerald-50/40 transition flex flex-col items-center text-center gap-2">
                            <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center"><i class="fas fa-clipboard-user"></i></div>
                            <span class="text-xs sm:text-sm font-bold text-slate-800">Absensi Saya</span>
                        </a>
                        <a href="{{ route('guru.leaves.index') }}" class="p-4 rounded-2xl border border-slate-200 hover:border-amber-300 hover:bg-amber-50/40 transition flex flex-col items-center text-center gap-2">
                            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center"><i class="fas fa-calendar-times"></i></div>
                            <span class="text-xs sm:text-sm font-bold text-slate-800">Cuti Pegawai</span>
                        </a>
                        <a href="{{ route('admin.letters.index') }}" class="p-4 rounded-2xl border border-slate-200 hover:border-purple-300 hover:bg-purple-50/40 transition flex flex-col items-center text-center gap-2">
                            <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center"><i class="fas fa-file-signature"></i></div>
                            <span class="text-xs sm:text-sm font-bold text-slate-800">Surat Edaran</span>
                        </a>
                        <a href="{{ route('guru.profil') }}" class="p-4 rounded-2xl border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition flex flex-col items-center text-center gap-2">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center"><i class="fas fa-user"></i></div>
                            <span class="text-xs sm:text-sm font-bold text-slate-800">Profil Saya</span>
                        </a>
                    @else
                        <a href="{{ route('guru.absensi') }}" class="p-4 rounded-2xl border border-slate-200 hover:border-purple-300 hover:bg-purple-50/40 transition flex flex-col items-center text-center gap-2">
                            <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center"><i class="fas fa-clipboard-check"></i></div>
                            <span class="text-xs sm:text-sm font-bold text-slate-800">Absensi Siswa</span>
                        </a>
                        <a href="{{ route('guru.nilai') }}" class="p-4 rounded-2xl border border-slate-200 hover:border-emerald-300 hover:bg-emerald-50/40 transition flex flex-col items-center text-center gap-2">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center"><i class="fas fa-chart-bar"></i></div>
                            <span class="text-xs sm:text-sm font-bold text-slate-800">Input Nilai</span>
                        </a>
                        <a href="{{ route('guru.lms.index') }}" class="p-4 rounded-2xl border border-slate-200 hover:border-orange-300 hover:bg-orange-50/40 transition flex flex-col items-center text-center gap-2">
                            <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-700 flex items-center justify-center"><i class="fas fa-laptop"></i></div>
                            <span class="text-xs sm:text-sm font-bold text-slate-800">Kelola LMS</span>
                        </a>
                        <a href="{{ route('simlab.index') }}" class="p-4 rounded-2xl border border-slate-200 hover:border-cyan-300 hover:bg-cyan-50/40 transition flex flex-col items-center text-center gap-2">
                            <div class="w-10 h-10 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center"><i class="fas fa-flask-vial"></i></div>
                            <span class="text-xs sm:text-sm font-bold text-slate-800">Simulator Lab</span>
                        </a>
                        <a href="{{ route('guru.jadwal') }}" class="p-4 rounded-2xl border border-slate-200 hover:border-sky-300 hover:bg-sky-50/40 transition flex flex-col items-center text-center gap-2">
                            <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center"><i class="fas fa-calendar-alt"></i></div>
                            <span class="text-xs sm:text-sm font-bold text-slate-800">Jadwal</span>
                        </a>
                        <a href="{{ route('guru.kelas') }}" class="p-4 rounded-2xl border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/40 transition flex flex-col items-center text-center gap-2">
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center"><i class="fas fa-users"></i></div>
                            <span class="text-xs sm:text-sm font-bold text-slate-800">Daftar Kelas</span>
                        </a>
                        @if(auth()->user()->isWaliKelas())
                        <a href="{{ route('admin.students.index') }}" class="p-4 rounded-2xl border border-slate-200 hover:border-emerald-300 hover:bg-emerald-50/40 transition flex flex-col items-center text-center gap-2">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center"><i class="fas fa-user-pen"></i></div>
                            <span class="text-xs sm:text-sm font-bold text-slate-800">Edit Data Siswa</span>
                        </a>
                        @endif
                    @endif

                    @if(auth()->user()->canManageInventory())
                    <a href="{{ route('inventory.index') }}" class="col-span-2 sm:col-span-3 p-4 rounded-2xl border border-indigo-200 bg-indigo-50/50 hover:bg-indigo-50 transition flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-700 text-white flex items-center justify-center shrink-0"><i class="fas fa-boxes-stacked"></i></div>
                        <div>
                            <span class="block text-sm font-bold text-slate-900">PembdaINVENTORY</span>
                            <span class="block text-xs text-slate-600">Akses pengelola aset & sarpras terpadu</span>
                        </div>
                    </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: WALI KELAS / PKL / REPUTATION SCORE --}}
        <div class="space-y-5">
            {{-- Wali Kelas Card --}}
            @if($homeroomClassroom)
            <div class="gd-card p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                        <i class="fas fa-star text-amber-500"></i> Wali Kelas
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                        {{ $homeroomClassroom->students_count ?? $homeroomClassroom->students->count() }} Siswa
                    </span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 mb-4">
                    <p class="font-extrabold text-slate-900 text-lg">{{ $homeroomClassroom->class_name }}</p>
                    <a href="{{ route('guru.siswa-kelas', $homeroomClassroom->id) }}"
                       class="mt-3 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold transition">
                        <i class="fas fa-eye"></i> Lihat Data Siswa
                    </a>
                </div>

                @if(isset($homeroomBillingStats))
                <div class="pt-3 border-t border-slate-200">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Progress SPP & Biaya Kelas</p>
                    <div class="flex justify-between text-xs sm:text-sm mb-1.5">
                        <span class="font-semibold text-emerald-700">Lunas: {{ $homeroomBillingStats->lunas_count }}</span>
                        <span class="font-semibold text-rose-600">Tunggakan: {{ $homeroomBillingStats->belum_bayar_count }}</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $homeroomBillingStats->percentage }}%"></div>
                    </div>
                    <p class="text-xs text-slate-500 mt-1.5">{{ $homeroomBillingStats->percentage }}% terbayar ({{ $homeroomBillingStats->lunas_count }} dari {{ $homeroomBillingStats->due_bills }} tagihan)</p>
                </div>
                @endif
            </div>
            @endif

            {{-- Pembimbing PKL Card --}}
            @if(($pklPlacementsCount ?? 0) > 0 || ($pklSupervisorHours ?? 0) > 0)
            <div class="gd-card p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                        <i class="fas fa-briefcase text-emerald-600"></i> Pembimbing PKL
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                        {{ $pklSupervisorHours ?? 0 }} JP
                    </span>
                </div>
                <p class="text-xl font-extrabold text-slate-900">{{ $pklPlacementsCount ?? 0 }} Siswa Bimbingan</p>
                <a href="{{ route('guru.pkl.index') }}"
                   class="mt-3 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold transition">
                    <i class="fas fa-eye"></i> Monitoring PKL Siswa
                </a>
            </div>
            @endif

            {{-- Reputation Score Card --}}
            <div class="rounded-2xl p-5 bg-slate-900 text-white shadow-lg border border-slate-800">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                        <i class="fas fa-award text-amber-400"></i> Reputation Score
                    </h2>
                    <a href="{{ route('reputation.leaderboard') }}" class="text-xs font-bold text-amber-300 hover:text-amber-200">Papan Skor →</a>
                </div>

                @if($isYayasanUser)
                <div class="text-center p-4 rounded-xl bg-slate-800/70 border border-slate-700 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-400 text-slate-950 mx-auto mb-2 flex items-center justify-center text-xl">
                        <i class="fas fa-crown"></i>
                    </div>
                    <div class="text-base font-bold text-white">Pimpinan Yayasan</div>
                    <div class="text-xs text-amber-300 mt-0.5">Status Non-Kompetitif</div>
                </div>
                @else
                <div class="p-4 rounded-xl bg-slate-800/70 border border-slate-700 mb-4 text-center relative">
                    <span class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-400 text-slate-950">Rank #{{ $rank }}</span>
                    <div class="text-3xl font-extrabold text-white">{{ number_format($reputation->total_points) }}</div>
                    <div class="text-xs font-semibold text-amber-300 mt-0.5">{{ $reputation->level_name }}</div>
                    <div class="mt-3 w-full bg-slate-700 rounded-full h-2 overflow-hidden">
                        <div class="bg-amber-400 h-full rounded-full" style="width: {{ $reputation->progress_percentage }}%"></div>
                    </div>
                </div>
                @endif

                <div class="space-y-2">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kontribusi Terakhir</p>
                    @forelse($reputationLogs as $log)
                    <div class="flex items-center justify-between text-xs p-2.5 rounded-xl bg-slate-800/60 border border-slate-800">
                        <div class="min-w-0 pr-2">
                            <p class="font-semibold text-slate-100 truncate">{{ $log->description }}</p>
                            <p class="text-[11px] text-slate-400">{{ $log->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="font-bold text-amber-400 shrink-0">{{ $log->points >= 0 ? '+' : '' }}{{ $log->points }}</span>
                    </div>
                    @empty
                    <p class="text-xs text-center text-slate-500 py-2">Belum ada aktivitas kontribusi</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function performGuruGpsScan(btnElement) {
        if (!navigator.geolocation) {
            Swal.fire({
                icon: 'error',
                title: 'GPS Tidak Didukung',
                text: 'Browser Anda tidak mendukung fitur lokasi (GPS). Gunakan Google Chrome atau browser modern lainnya.',
                confirmButtonColor: '#059669',
            });
            return;
        }

        const origHtml = btnElement ? btnElement.innerHTML : '';
        if (btnElement) {
            btnElement.disabled = true;
            btnElement.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i> Mengambil Lokasi GPS...';
        }

        navigator.geolocation.getCurrentPosition(
            function (pos) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                let deviceId = localStorage.getItem('pembdahub_device_id');
                if (!deviceId) {
                    deviceId = 'dev_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                    localStorage.setItem('pembdahub_device_id', deviceId);
                }

                if (btnElement) {
                    btnElement.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i> Memverifikasi Presensi...';
                }

                fetch('{{ route('mobile.absensi.scan') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        latitude: lat,
                        longitude: lng,
                        device_id: deviceId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (btnElement) {
                        btnElement.disabled = false;
                        btnElement.innerHTML = origHtml;
                    }

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Presensi Berhasil!',
                            html: `<p class="font-bold text-slate-800">${data.message}</p><p class="text-xs text-slate-500 mt-2">Terima kasih atas pengabdian dan dedikasi Bapak/Ibu Guru!</p>`,
                            timer: 3500,
                            timerProgressBar: true,
                            showConfirmButton: true,
                            confirmButtonText: 'Tutup',
                            confirmButtonColor: '#059669',
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Presensi Belum Berhasil',
                            text: data.message || 'Terjadi kesalahan saat memverifikasi lokasi.',
                            confirmButtonColor: '#e11d48',
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    if (btnElement) {
                        btnElement.disabled = false;
                        btnElement.innerHTML = origHtml;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gangguan Jaringan',
                        text: 'Gagal terhubung ke server. Pastikan koneksi internet aktif dan stabil.',
                        confirmButtonColor: '#e11d48',
                    });
                });
            },
            function (err) {
                if (btnElement) {
                    btnElement.disabled = false;
                    btnElement.innerHTML = origHtml;
                }
                let msg = 'Gagal mengakses GPS pada perangkat Anda.';
                if (err.code === err.PERMISSION_DENIED) {
                    msg = 'Akses lokasi ditolak. Harap izinkan akses lokasi (GPS) pada browser/perangkat Anda.';
                } else if (err.code === err.POSITION_UNAVAILABLE) {
                    msg = 'Sinyal GPS tidak terdeteksi. Pastikan GPS aktif dan berada di area terbuka.';
                }
                Swal.fire({
                    icon: 'warning',
                    title: 'Akses Lokasi Diperlukan',
                    text: msg,
                    confirmButtonColor: '#f59e0b',
                });
            },
            { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
        );
    }
</script>
@endpush
