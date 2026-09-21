@extends('layouts.guru')
@section('title', 'Absensi Siswa - Portal Guru')

@section('content')

<div class="space-y-6">

    {{-- Header Banner (Neo-Brutalism) --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl p-6 border-2 border-black print-hide" style="background: linear-gradient(135deg, #090d16 0%, #311b92 50%, #4a148c 100%) !important;">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-xl md:text-2xl font-black text-white flex items-center gap-3" style="color: #ffffff !important;">
                        <div class="w-10 h-10 rounded-xl bg-amber-400 border-2 border-black flex items-center justify-center text-black shadow-sm text-lg flex-shrink-0">
                            <i class="fas fa-clipboard-check text-black"></i>
                        </div>
                        <span>Absensi & Rekap Kehadiran Siswa</span>
                    </h1>
                    @if(isset($isHomeroom) && $isHomeroom)
                        <span class="px-3 py-1 rounded-xl bg-amber-400 text-purple-950 font-black text-xs border-2 border-black shadow-sm flex items-center gap-1.5">
                            <i class="fas fa-crown text-amber-900"></i> Wali Kelas
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-xl bg-purple-800 text-purple-200 font-bold text-xs border border-purple-600 shadow-sm flex items-center gap-1.5">
                            <i class="fas fa-chalkboard-teacher"></i> Guru Pengampu
                        </span>
                    @endif
                </div>
                <p class="text-xs md:text-sm font-bold text-purple-200 mt-1" style="color: #e9d5ff !important;">
                    Rekapitulasi dan catatan kehadiran siswa @if($selectedClassroom) · <span class="text-amber-300 font-extrabold">{{ $selectedClassroom->class_name }}</span> @endif @if($activeYear) ({{ $activeYear->year }}) @endif
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <form method="GET" class="flex items-center gap-2 flex-wrap">
                    <select name="classroom_id" onchange="this.form.submit()" class="text-xs font-black border-2 border-black rounded-2xl px-4 py-2.5 shadow-sm outline-none cursor-pointer" style="color: #000000 !important; background-color: #ffffff !important;">
                        <option value="" style="color: #000000 !important;">-- Pilih Kelas --</option>
                        @foreach($classrooms as $cr)
                            <option value="{{ $cr->id }}" {{ $selectedClassroomId == $cr->id ? 'selected' : '' }} style="color: #000000 !important;">
                                {{ $cr->class_name }}
                            </option>
                        @endforeach
                    </select>

                    @if($selectedClassroomId)
                    <select name="month" onchange="this.form.submit()" class="text-xs font-black border-2 border-black rounded-2xl px-3 py-2.5 shadow-sm outline-none cursor-pointer" style="color: #000000 !important; background-color: #ffffff !important;">
                        @foreach($monthsList as $mNum => $mName)
                            <option value="{{ $mNum }}" {{ $selectedMonth == $mNum ? 'selected' : '' }} style="color: #000000 !important;">{{ $mName }}</option>
                        @endforeach
                    </select>

                    <select name="year" onchange="this.form.submit()" class="text-xs font-black border-2 border-black rounded-2xl px-3 py-2.5 shadow-sm outline-none cursor-pointer" style="color: #000000 !important; background-color: #ffffff !important;">
                        @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }} style="color: #000000 !important;">{{ $y }}</option>
                        @endfor
                    </select>
                    @endif
                </form>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-600 text-white font-bold px-5 py-3.5 rounded-2xl shadow-sm border border-emerald-700 flex items-center justify-between text-xs md:text-sm print-hide">
            <span><i class="fas fa-check-circle text-amber-300 mr-2 text-base"></i> {{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-white hover:text-amber-200"><i class="fas fa-times"></i></button>
        </div>
    @endif
    @if($errors->any())
        <div class="bg-rose-600 text-white font-bold px-5 py-3.5 rounded-2xl shadow-sm border border-rose-700 space-y-1 text-xs md:text-sm print-hide">
            @foreach($errors->all() as $err)
                <div><i class="fas fa-exclamation-circle text-amber-300 mr-2 text-base"></i> {{ $err }}</div>
            @endforeach
        </div>
    @endif

    @if(!$selectedClassroomId)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
            <i class="fas fa-hand-pointer text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500 font-bold">Pilih kelas terlebih dahulu untuk melihat rekap absensi.</p>
        </div>
    @elseif(!$selectedClassroom)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
            <i class="fas fa-exclamation-circle text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500 font-bold">Kelas tidak ditemukan atau Anda tidak mengajar di kelas ini.</p>
        </div>
    @else
        <div x-data="{ 
            viewMode: '{{ request('viewMode', 'daily') }}', 
            statusFilter: 'all',
            editModeLesson: {{ ($isTodayScheduled ?? false) ? 'true' : 'false' }}
        }" class="space-y-5">

            {{-- ═════════════════════════════════════════════════════════════════ --}}
            {{-- REKAP KEHADIRAN HARIAN REAL-TIME (5 KARTU SEJAJAR 1 BARIS)        --}}
            {{-- ═════════════════════════════════════════════════════════════════ --}}
            <div class="bg-white rounded-3xl p-5 border-2 border-slate-200 shadow-sm space-y-4 print-hide">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-sm font-black flex-shrink-0">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 flex items-center gap-2 flex-wrap">
                                <span>Rekap Kehadiran Harian Siswa</span>
                                <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-900 border border-purple-200 font-black uppercase">Real-Time Gerbang & Kelas</span>
                            </h3>
                            <p class="text-xs text-slate-500 font-medium">Klik pada salah satu kartu status untuk memfilter daftar siswa di bawah</p>
                        </div>
                    </div>

                    {{-- Pemilih Tanggal Harian --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-bold text-slate-700 flex items-center gap-1">
                            <i class="fas fa-calendar-day text-purple-600"></i> Tanggal:
                        </span>
                        <input type="date" value="{{ $selectedDailyDate }}" 
                            onchange="window.location.href='?classroom_id={{ $selectedClassroomId }}&month={{ $selectedMonth }}&year={{ $selectedYear }}&daily_date=' + this.value + '&viewMode=daily'"
                            class="text-xs font-black border-2 border-purple-300 rounded-xl px-3 py-1.5 bg-white text-slate-900 shadow-sm outline-none focus:border-purple-600 cursor-pointer">
                        @if($selectedDailyDate !== date('Y-m-d'))
                            <a href="?classroom_id={{ $selectedClassroomId }}&month={{ date('n') }}&year={{ date('Y') }}&daily_date={{ date('Y-m-d') }}&viewMode=daily" 
                               class="px-2.5 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold shadow-sm transition" title="Kembali ke Hari Ini">
                                Hari Ini
                            </a>
                        @endif
                    </div>
                </div>

                {{-- 5 Equal Cards in 1 Row (Flex-nowrap) --}}
                <div class="flex flex-wrap lg:flex-nowrap gap-3 items-stretch w-full">
                    {{-- 1. Hadir --}}
                    <button type="button" @click="viewMode = 'daily'; statusFilter = (statusFilter === 'hadir' ? 'all' : 'hadir')" 
                        :class="statusFilter === 'hadir' ? 'border-2 border-emerald-600 bg-emerald-50 shadow-md ring-2 ring-emerald-400' : 'border border-slate-200 bg-slate-50 hover:bg-emerald-50/50'"
                        style="flex: 1 1 0; min-width: 140px;"
                        class="p-4 rounded-2xl text-center transition-all cursor-pointer flex flex-col items-center justify-center">
                        <div class="w-9 h-9 bg-emerald-100 rounded-xl flex items-center justify-center mb-2 text-emerald-600">
                            <i class="fas fa-check-circle text-lg"></i>
                        </div>
                        <p class="text-2xl font-black text-slate-900">{{ $dailySummary['present'] }}</p>
                        <p class="text-xs font-bold text-emerald-700 mt-0.5">Hadir ({{ $dailySummary['percentage'] }}%)</p>
                    </button>

                    {{-- 2. Sakit --}}
                    <button type="button" @click="viewMode = 'daily'; statusFilter = (statusFilter === 'sakit' ? 'all' : 'sakit')" 
                        :class="statusFilter === 'sakit' ? 'border-2 border-amber-500 bg-amber-50 shadow-md ring-2 ring-amber-400' : 'border border-slate-200 bg-slate-50 hover:bg-amber-50/50'"
                        style="flex: 1 1 0; min-width: 140px;"
                        class="p-4 rounded-2xl text-center transition-all cursor-pointer flex flex-col items-center justify-center">
                        <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center mb-2 text-amber-600">
                            <i class="fas fa-briefcase-medical text-lg"></i>
                        </div>
                        <p class="text-2xl font-black text-slate-900">{{ $dailySummary['sick'] }}</p>
                        <p class="text-xs font-bold text-amber-700 mt-0.5">Sakit</p>
                    </button>

                    {{-- 3. Izin --}}
                    <button type="button" @click="viewMode = 'daily'; statusFilter = (statusFilter === 'izin' ? 'all' : 'izin')" 
                        :class="statusFilter === 'izin' ? 'border-2 border-blue-600 bg-blue-50 shadow-md ring-2 ring-blue-400' : 'border border-slate-200 bg-slate-50 hover:bg-blue-50/50'"
                        style="flex: 1 1 0; min-width: 140px;"
                        class="p-4 rounded-2xl text-center transition-all cursor-pointer flex flex-col items-center justify-center">
                        <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center mb-2 text-blue-600">
                            <i class="fas fa-envelope-open-text text-lg"></i>
                        </div>
                        <p class="text-2xl font-black text-slate-900">{{ $dailySummary['permission'] }}</p>
                        <p class="text-xs font-bold text-blue-700 mt-0.5">Izin</p>
                    </button>

                    {{-- 4. Alpha --}}
                    <button type="button" @click="viewMode = 'daily'; statusFilter = (statusFilter === 'alpha' ? 'all' : 'alpha')" 
                        :class="statusFilter === 'alpha' ? 'border-2 border-rose-600 bg-rose-50 shadow-md ring-2 ring-rose-400' : 'border border-slate-200 bg-slate-50 hover:bg-rose-50/50'"
                        style="flex: 1 1 0; min-width: 140px;"
                        class="p-4 rounded-2xl text-center transition-all cursor-pointer flex flex-col items-center justify-center">
                        <div class="w-9 h-9 bg-rose-100 rounded-xl flex items-center justify-center mb-2 text-rose-600">
                            <i class="fas fa-times-circle text-lg"></i>
                        </div>
                        <p class="text-2xl font-black text-slate-900">{{ $dailySummary['absent'] }}</p>
                        <p class="text-xs font-bold text-rose-700 mt-0.5">Alpha</p>
                    </button>

                    {{-- 5. Belum Presensi / Scan --}}
                    <button type="button" @click="viewMode = 'daily'; statusFilter = (statusFilter === 'unscanned' ? 'all' : 'unscanned')" 
                        :class="statusFilter === 'unscanned' ? 'border-2 border-purple-700 bg-purple-50 shadow-md ring-2 ring-purple-400' : 'border-2 border-dashed border-amber-400 bg-amber-50/40 hover:bg-amber-50'"
                        style="flex: 1 1 0; min-width: 140px;"
                        class="p-4 rounded-2xl text-center transition-all cursor-pointer flex flex-col items-center justify-center">
                        <div class="w-9 h-9 bg-amber-200/80 rounded-xl flex items-center justify-center mb-2 text-amber-800">
                            <i class="fas fa-clock text-lg"></i>
                        </div>
                        <p class="text-2xl font-black text-amber-950">{{ $dailySummary['unscanned'] }}</p>
                        <p class="text-xs font-black text-amber-800 mt-0.5">Belum Scan / Presensi</p>
                    </button>
                </div>
            </div>

            {{-- ═════════════════════════════════════════════════════════════════ --}}
            {{-- TAB CONTROLS                                                      --}}
            {{-- ═════════════════════════════════════════════════════════════════ --}}
            <div class="flex items-center justify-between gap-4 flex-wrap bg-white p-2.5 rounded-2xl border-2 border-slate-200 shadow-sm print-hide">
                <div class="flex items-center gap-2 flex-wrap">
                    {{-- Tab 1: Presensi Harian --}}
                    <button type="button" @click="viewMode = 'daily'" 
                        :style="viewMode === 'daily' ? 'background-color: #1e1b4b !important; color: #ffffff !important; border-color: #0f172a !important;' : 'background-color: #f8fafc !important; color: #0f172a !important; border-color: #cbd5e1 !important;'"
                        class="px-4 py-2.5 rounded-xl text-xs font-black transition flex items-center gap-2 border-2 cursor-pointer shadow-sm">
                        <i class="fas fa-clipboard-check text-amber-400 text-sm"></i>
                        @if(isset($isHomeroom) && $isHomeroom)
                            <span>Presensi Harian Siswa (Wali Kelas)</span>
                        @else
                            <span>Pantauan Kehadiran Harian Siswa</span>
                        @endif
                    </button>

                    {{-- Tab 2: Matriks Bulanan --}}
                    <button type="button" @click="viewMode = 'matrix'" 
                        :style="viewMode === 'matrix' ? 'background-color: #1e1b4b !important; color: #ffffff !important; border-color: #0f172a !important;' : 'background-color: #f8fafc !important; color: #0f172a !important; border-color: #cbd5e1 !important;'"
                        class="px-4 py-2.5 rounded-xl text-xs font-black transition flex items-center gap-2 border-2 cursor-pointer shadow-sm">
                        <i class="fas fa-table text-indigo-400 text-sm"></i>
                        <span>Matriks Bulanan (Sekolah)</span>
                    </button>

                    {{-- Tab 3: Pelajaran Saya --}}
                    <button type="button" @click="viewMode = 'log'" 
                        :style="viewMode === 'log' ? 'background-color: #1e1b4b !important; color: #ffffff !important; border-color: #0f172a !important;' : 'background-color: #f8fafc !important; color: #0f172a !important; border-color: #cbd5e1 !important;'"
                        class="px-4 py-2.5 rounded-xl text-xs font-black transition flex items-center gap-2 border-2 cursor-pointer shadow-sm">
                        <i class="fas fa-chalkboard-teacher text-emerald-400 text-sm"></i>
                        <span>Kehadiran Pelajaran Saya</span>
                    </button>
                </div>

                <a href="{{ route('guru.absensi.print', request()->all()) }}" target="_blank" 
                   class="px-4 py-2.5 bg-purple-700 hover:bg-purple-800 text-white rounded-xl text-xs font-black shadow-sm transition flex items-center gap-2 border-2 border-purple-900" style="color: #ffffff !important;">
                    <i class="fas fa-print"></i> Cetak Rekap
                </a>
            </div>

            {{-- ═════════════════════════════════════════════════════════════════ --}}
            {{-- 1. TAB PRESENSI HARIAN KELAS                                      --}}
            {{-- ═════════════════════════════════════════════════════════════════ --}}
            <div x-show="viewMode === 'daily'" class="bg-white rounded-3xl shadow-sm border-2 border-slate-200 overflow-hidden space-y-0">
                @if(isset($isHomeroom) && $isHomeroom)
                    {{-- ── A. FORM EDIT KHUSUS WALI KELAS ── --}}
                    <form action="{{ route('guru.absensi.storeDaily') }}" method="POST" id="formPresensiHarian">
                        @csrf
                        <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
                        <input type="hidden" name="date" value="{{ $selectedDailyDate }}">

                        {{-- Toolbar Presensi Harian --}}
                        <div class="px-6 py-4 bg-purple-50 border-b-2 border-purple-200 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                            <div>
                                <h2 class="text-base font-black text-purple-950 flex items-center gap-2">
                                    <i class="fas fa-calendar-check text-purple-700"></i>
                                    <span>Presensi Harian: {{ \Carbon\Carbon::parse($selectedDailyDate)->translatedFormat('l, d F Y') }}</span>
                                </h2>
                                <p class="text-xs text-purple-900 font-bold mt-0.5">
                                    Kelas: <strong>{{ $selectedClassroom->class_name }}</strong> · Total: <strong>{{ $classroomStudents->count() }} Siswa</strong>
                                    <span class="text-purple-950 bg-amber-300 border border-amber-500 px-2 py-0.5 rounded-md text-[10px] ml-1 font-black">👑 Akses Wali Kelas Aktif</span>
                                </p>
                                @php
                                    $lateThresh = $selectedClassroom ? $selectedClassroom->getLateThreshold() : '07:45:00';
                                    $isPassedLateThresh = ($selectedDailyDate === date('Y-m-d')) && now()->format('H:i:s') > $lateThresh;
                                @endphp
                                @if($isPassedLateThresh)
                                    <div class="mt-2 px-3 py-1.5 bg-amber-100 border border-amber-300 rounded-xl text-amber-900 text-xs font-bold flex items-center gap-2">
                                        <i class="fas fa-clock text-amber-700"></i>
                                        <span>Batas Masuk Kelas ({{ substr($selectedClassroom->entry_time ?? '07:30', 0, 5) }} + {{ $selectedClassroom->late_tolerance ?? 15 }}m = <strong>{{ substr($lateThresh, 0, 5) }} WIB</strong>) telah lewat. Input 'Hadir' otomatis tercatat sebagai <strong>Terlambat</strong>.</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Action Buttons & Filter Chips --}}
                            <div class="flex items-center gap-2 flex-wrap">
                                {{-- Fast Filter Chips --}}
                                <div class="flex items-center gap-1 bg-white p-1 rounded-xl border border-purple-200 shadow-2xs">
                                    <button type="button" @click="statusFilter = 'all'" 
                                        :style="statusFilter === 'all' ? 'background-color: #581c87 !important; color: #ffffff !important;' : 'background-color: transparent !important; color: #334155 !important;'"
                                        class="px-2.5 py-1 rounded-lg text-xs font-black transition cursor-pointer">
                                        Semua ({{ $classroomStudents->count() }})
                                    </button>
                                    <button type="button" @click="statusFilter = 'unscanned'" 
                                        :style="statusFilter === 'unscanned' ? 'background-color: #b45309 !important; color: #ffffff !important;' : 'background-color: transparent !important; color: #b45309 !important;'"
                                        class="px-2.5 py-1 rounded-lg text-xs font-black transition flex items-center gap-1 cursor-pointer">
                                        <i class="fas fa-clock text-[10px]"></i> Belum Scan ({{ $dailySummary['unscanned'] }})
                                    </button>
                                    <button type="button" @click="statusFilter = 'hadir'" 
                                        :style="statusFilter === 'hadir' ? 'background-color: #047857 !important; color: #ffffff !important;' : 'background-color: transparent !important; color: #047857 !important;'"
                                        class="px-2 py-1 rounded-lg text-xs font-black transition cursor-pointer">
                                        Hadir ({{ $dailySchoolAttendances->where('status', 'hadir')->count() }})
                                    </button>
                                    <button type="button" @click="statusFilter = 'terlambat'" 
                                        :style="statusFilter === 'terlambat' ? 'background-color: #d97706 !important; color: #ffffff !important;' : 'background-color: transparent !important; color: #d97706 !important;'"
                                        class="px-2 py-1 rounded-lg text-xs font-black transition cursor-pointer">
                                        Terlambat ({{ $dailySchoolAttendances->where('status', 'terlambat')->count() }})
                                    </button>
                                    <button type="button" @click="statusFilter = 'sakit'" 
                                        :style="statusFilter === 'sakit' ? 'background-color: #d97706 !important; color: #ffffff !important;' : 'background-color: transparent !important; color: #b45309 !important;'"
                                        class="px-2 py-1 rounded-lg text-xs font-black transition cursor-pointer">
                                        Sakit ({{ $dailySummary['sick'] }})
                                    </button>
                                    <button type="button" @click="statusFilter = 'izin'" 
                                        :style="statusFilter === 'izin' ? 'background-color: #1d4ed8 !important; color: #ffffff !important;' : 'background-color: transparent !important; color: #1d4ed8 !important;'"
                                        class="px-2 py-1 rounded-lg text-xs font-black transition cursor-pointer">
                                        Izin ({{ $dailySummary['permission'] }})
                                    </button>
                                    <button type="button" @click="statusFilter = 'alpha'" 
                                        :style="statusFilter === 'alpha' ? 'background-color: #be123c !important; color: #ffffff !important;' : 'background-color: transparent !important; color: #be123c !important;'"
                                        class="px-2 py-1 rounded-lg text-xs font-black transition cursor-pointer">
                                        Alpha ({{ $dailySummary['absent'] }})
                                    </button>
                                </div>

                                {{-- Bulk Helper: Hadirkan Semua yang Belum Scan --}}
                                <button type="button" onclick="markAllUnscannedDailyHadir()" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black shadow-sm transition flex items-center gap-1.5 border border-emerald-800 cursor-pointer" style="color: #ffffff !important;" title="Otomatis isi Hadir bagi semua siswa yang belum scan">
                                    <i class="fas fa-check-double"></i> Hadirkan Belum Scan
                                </button>

                                {{-- Submit Button --}}
                                <button type="submit" class="px-5 py-2 bg-amber-400 hover:bg-amber-300 text-black border-2 border-black rounded-xl text-xs font-black uppercase tracking-wider shadow-md transition flex items-center gap-2 cursor-pointer" style="color: #000000 !important; background-color: #fbbf24 !important;">
                                    <i class="fas fa-save text-sm text-black"></i> Simpan Presensi Harian
                                </button>
                            </div>
                        </div>

                        {{-- Table List of Students (Edit Mode) --}}
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead>
                                    <tr class="bg-slate-900 text-white font-bold text-xs uppercase" style="background-color: #0f172a !important; color: #ffffff !important;">
                                        <th class="px-4 py-3 text-center border-r border-slate-700 w-12 text-white">No</th>
                                        <th class="px-4 py-3 border-r border-slate-700 min-w-[220px] text-white">Nama Siswa</th>
                                        <th class="px-4 py-3 border-r border-slate-700 min-w-[180px] text-white">Status & Asal Catatan</th>
                                        <th class="px-4 py-3 border-r border-slate-700 min-w-[240px] text-center text-white">Tentukan Status (Wali Kelas)</th>
                                        <th class="px-4 py-3 min-w-[220px] text-white">Keterangan / Alasan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($classroomStudents as $idx => $st)
                                        @php
                                            $att = $dailySchoolAttendances->get($st->id);
                                            $currentStatus = $att ? $att->status : '';
                                            $stGroup = $studentBlockGroups[$st->id] ?? null;
                                        @endphp
                                        <tr x-data="{ rowStatus: '{{ $currentStatus }}' }"
                                            x-show="statusFilter === 'all' || (statusFilter === 'unscanned' && !rowStatus) || (statusFilter === rowStatus)"
                                            @mark-unscanned-daily-hadir.window="if (!rowStatus) rowStatus = 'hadir'"
                                            class="hover:bg-purple-50/40 transition">
                                            
                                            <td class="px-4 py-3 text-center font-bold text-slate-500 border-r border-gray-100">
                                                {{ $idx + 1 }}
                                            </td>

                                            <td class="px-4 py-3 border-r border-gray-100 font-bold">
                                                <div class="flex items-center gap-3">
                                                    <img src="{{ $st->photo_url }}" alt="{{ $st->full_name }}" class="w-9 h-9 rounded-full object-cover shrink-0 border border-gray-200 shadow-2xs" onerror="this.src='{{ asset('images/default-student.jpg') }}'">
                                                    <div class="truncate max-w-[200px]" title="{{ $st->full_name }}">
                                                        <div class="text-xs font-bold text-gray-900 truncate">{{ $st->full_name }}</div>
                                                        @if($stGroup)
                                                            @php $isStSched = in_array($st->id, $scheduledStudentIds ?? []); @endphp
                                                            @if($isStSched)
                                                                <span class="inline-block mt-0.5 text-[9px] font-black px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs" title="Grup {{ $stGroup }} (Hadir Fisik)"><i class="fas fa-check-circle text-[8px] mr-0.5"></i> Grup {{ $stGroup }}</span>
                                                            @else
                                                                <span class="inline-block mt-0.5 text-[9px] font-bold px-1.5 py-0.5 rounded bg-rose-100 text-rose-800 border border-rose-300 shadow-2xs" title="Grup {{ $stGroup }} (Tidak Hadir Fisik)"><i class="fas fa-times-circle text-[8px] mr-0.5"></i> Grup {{ $stGroup }}</span>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Status & Asal Catatan --}}
                                            <td class="px-4 py-3 border-r border-gray-100">
                                                @if($att)
                                                    @if($att->recorded_via === 'rfid' || $att->recorded_via === 'barcode' || $att->recorded_via === 'qr')
                                                        <div class="space-y-0.5">
                                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-950 border border-emerald-300 font-black text-xs">
                                                                <i class="fas fa-id-card text-emerald-700"></i> Scan Gerbang
                                                            </span>
                                                            <div class="text-[10px] text-emerald-800 font-bold">
                                                                Pukul {{ $att->time_in ? substr($att->time_in, 0, 5) . ' WIB' : '-' }}
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="space-y-0.5">
                                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-950 border border-blue-300 font-black text-xs">
                                                                <i class="fas fa-user-edit text-blue-700"></i> Manual / Wali Kelas
                                                            </span>
                                                            <div class="text-[10px] text-blue-800 font-bold capitalize">
                                                                Status: {{ $att->status }}
                                                            </div>
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-100 text-amber-950 border border-amber-300 font-black text-xs">
                                                        <i class="fas fa-clock text-amber-700"></i> Belum Scan / Presensi
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Toggle Status Buttons (H, S, I, A, Clear) --}}
                                            <td class="px-4 py-3 border-r border-gray-100 text-center">
                                                <input type="hidden" name="statuses[{{ $st->id }}]" :value="rowStatus">
                                                <div class="inline-flex items-center gap-1 bg-slate-100 p-1 rounded-xl border border-slate-300 shadow-2xs">
                                                    {{-- Hadir --}}
                                                    <button type="button" @click="rowStatus = 'hadir'" 
                                                        :style="rowStatus === 'hadir' ? 'background-color: #059669 !important; color: #ffffff !important;' : 'background-color: transparent !important; color: #334155 !important;'"
                                                        class="px-2 py-1 rounded-lg text-xs font-black transition cursor-pointer" title="Hadir (Tepat Waktu bila sebelum jam toleransi)">
                                                        Hadir
                                                    </button>
                                                    {{-- Terlambat --}}
                                                    <button type="button" @click="rowStatus = 'terlambat'" 
                                                        :style="rowStatus === 'terlambat' ? 'background-color: #d97706 !important; color: #ffffff !important;' : 'background-color: transparent !important; color: #334155 !important;'"
                                                        class="px-2 py-1 rounded-lg text-xs font-black transition cursor-pointer" title="Terlambat">
                                                        Terlambat
                                                    </button>
                                                    {{-- Sakit --}}
                                                    <button type="button" @click="rowStatus = 'sakit'" 
                                                        :style="rowStatus === 'sakit' ? 'background-color: #d97706 !important; color: #ffffff !important;' : 'background-color: transparent !important; color: #334155 !important;'"
                                                        class="px-2.5 py-1 rounded-lg text-xs font-black transition cursor-pointer" title="Sakit">
                                                        Sakit
                                                    </button>
                                                    {{-- Izin --}}
                                                    <button type="button" @click="rowStatus = 'izin'" 
                                                        :style="rowStatus === 'izin' ? 'background-color: #2563eb !important; color: #ffffff !important;' : 'background-color: transparent !important; color: #334155 !important;'"
                                                        class="px-2.5 py-1 rounded-lg text-xs font-black transition cursor-pointer" title="Izin">
                                                        Izin
                                                    </button>
                                                    {{-- Alpha --}}
                                                    <button type="button" @click="rowStatus = 'alpha'" 
                                                        :style="rowStatus === 'alpha' ? 'background-color: #e11d48 !important; color: #ffffff !important;' : 'background-color: transparent !important; color: #334155 !important;'"
                                                        class="px-2.5 py-1 rounded-lg text-xs font-black transition cursor-pointer" title="Alpha (Tanpa Keterangan)">
                                                        Alpha
                                                    </button>
                                                    {{-- Reset --}}
                                                    <button type="button" @click="rowStatus = ''" 
                                                        :style="!rowStatus ? 'background-color: #94a3b8 !important; color: #ffffff !important;' : 'background-color: transparent !important; color: #64748b !important;'"
                                                        class="px-2 py-1 rounded-lg text-xs font-black transition cursor-pointer" title="Kosongkan / Reset Status">
                                                        —
                                                    </button>
                                                </div>
                                            </td>

                                            {{-- Catatan / Keterangan --}}
                                            <td class="px-4 py-3">
                                                <input type="text" name="notes[{{ $st->id }}]" value="{{ $att?->notes ?? '' }}" 
                                                    placeholder="Contoh: Surat dokter, izin keluarga..." 
                                                    class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="p-8 text-center text-gray-400 font-bold">
                                                Tidak ada data siswa aktif pada kelas ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Footer Action Bar --}}
                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="text-xs text-slate-600 font-bold">
                                💡 Data yang disimpan akan langsung terhubung ke rekapitulasi harian sekolah dan matriks bulanan.
                            </div>
                            <button type="submit" class="px-6 py-2.5 bg-amber-400 hover:bg-amber-300 text-black border-2 border-black rounded-xl text-xs font-black uppercase tracking-wider shadow-md transition flex items-center gap-2 cursor-pointer" style="color: #000000 !important; background-color: #fbbf24 !important;">
                                <i class="fas fa-save text-sm text-black"></i> Simpan Presensi Harian Kelas
                            </button>
                        </div>
                    </form>
                @else
                    {{-- ── B. READ-ONLY VIEW UNTUK GURU PENGAMPU (BUKAN WALI KELAS) ── --}}
                    <div class="p-6 space-y-4">
                        {{-- Info Alert: Read-Only --}}
                        <div class="p-4 bg-amber-50 border-2 border-amber-300 rounded-2xl flex items-start gap-3 text-amber-950 text-xs shadow-2xs">
                            <div class="w-8 h-8 rounded-xl bg-amber-400 text-black flex items-center justify-center text-sm font-black flex-shrink-0 mt-0.5">
                                <i class="fas fa-info-circle text-black"></i>
                            </div>
                            <div class="space-y-1">
                                <div class="font-black text-sm text-amber-950">Mode Pantauan Kehadiran Harian (Hanya Lihat)</div>
                                <p class="text-xs text-amber-900 font-medium">
                                    Anda membuka kelas ini sebagai <strong>Guru Pengampu Mata Pelajaran</strong>. Pengisian dan pengeditan Presensi Harian Sekolah dikelola khusus oleh Wali Kelas 
                                    (<strong>{{ $selectedClassroom->homeroomTeacher?->full_name ?? 'Wali Kelas' }}</strong>) atau Petugas Admin Sekolah.
                                </p>
                                <p class="text-[11px] text-amber-800 font-bold">
                                    👉 Untuk menginput absensi jam pelajaran yang Anda ampu di kelas ini, silakan klik tab <strong>"Kehadiran Pelajaran Saya"</strong> di atas.
                                </p>
                            </div>
                        </div>

                        {{-- Fast Filter Chips for Read-Only Mode --}}
                        <div class="flex items-center gap-1.5 flex-wrap bg-slate-50 p-2 rounded-2xl border border-slate-200">
                            <span class="text-xs font-bold text-slate-600 mr-1">Filter:</span>
                            <button type="button" @click="statusFilter = 'all'" 
                                :style="statusFilter === 'all' ? 'background-color: #581c87 !important; color: #ffffff !important;' : 'background-color: #ffffff !important; color: #334155 !important;'"
                                class="px-3 py-1 rounded-xl text-xs font-bold border border-slate-300 transition cursor-pointer">
                                Semua ({{ $classroomStudents->count() }})
                            </button>
                            <button type="button" @click="statusFilter = 'unscanned'" 
                                :style="statusFilter === 'unscanned' ? 'background-color: #b45309 !important; color: #ffffff !important;' : 'background-color: #ffffff !important; color: #b45309 !important;'"
                                class="px-3 py-1 rounded-xl text-xs font-bold border border-slate-300 transition cursor-pointer">
                                Belum Scan ({{ $dailySummary['unscanned'] }})
                            </button>
                            <button type="button" @click="statusFilter = 'hadir'" 
                                :style="statusFilter === 'hadir' ? 'background-color: #047857 !important; color: #ffffff !important;' : 'background-color: #ffffff !important; color: #047857 !important;'"
                                class="px-3 py-1 rounded-xl text-xs font-bold border border-slate-300 transition cursor-pointer">
                                Hadir ({{ $dailySummary['present'] }})
                            </button>
                            <button type="button" @click="statusFilter = 'sakit'" 
                                :style="statusFilter === 'sakit' ? 'background-color: #d97706 !important; color: #ffffff !important;' : 'background-color: #ffffff !important; color: #b45309 !important;'"
                                class="px-3 py-1 rounded-xl text-xs font-bold border border-slate-300 transition cursor-pointer">
                                Sakit ({{ $dailySummary['sick'] }})
                            </button>
                            <button type="button" @click="statusFilter = 'izin'" 
                                :style="statusFilter === 'izin' ? 'background-color: #1d4ed8 !important; color: #ffffff !important;' : 'background-color: #ffffff !important; color: #1d4ed8 !important;'"
                                class="px-3 py-1 rounded-xl text-xs font-bold border border-slate-300 transition cursor-pointer">
                                Izin ({{ $dailySummary['permission'] }})
                            </button>
                            <button type="button" @click="statusFilter = 'alpha'" 
                                :style="statusFilter === 'alpha' ? 'background-color: #be123c !important; color: #ffffff !important;' : 'background-color: #ffffff !important; color: #be123c !important;'"
                                class="px-3 py-1 rounded-xl text-xs font-bold border border-slate-300 transition cursor-pointer">
                                Alpha ({{ $dailySummary['absent'] }})
                            </button>
                        </div>

                        {{-- Read-Only Student Table --}}
                        <div class="overflow-x-auto rounded-2xl border border-slate-200">
                            <table class="w-full text-sm text-left">
                                <thead>
                                    <tr class="bg-slate-900 text-white font-bold text-xs uppercase" style="background-color: #0f172a !important; color: #ffffff !important;">
                                        <th class="px-4 py-3 text-center border-r border-slate-700 w-12 text-white">No</th>
                                        <th class="px-4 py-3 border-r border-slate-700 min-w-[220px] text-white">Nama Siswa</th>
                                        <th class="px-4 py-3 border-r border-slate-700 min-w-[180px] text-white">Status Kehadiran Hari Ini</th>
                                        <th class="px-4 py-3 min-w-[220px] text-white">Keterangan / Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($classroomStudents as $idx => $st)
                                        @php
                                            $att = $dailySchoolAttendances->get($st->id);
                                            $currentStatus = $att ? $att->status : '';
                                            $stGroup = $studentBlockGroups[$st->id] ?? null;
                                        @endphp
                                        <tr x-show="statusFilter === 'all' || (statusFilter === 'unscanned' && '{{ $currentStatus }}' === '') || (statusFilter === '{{ $currentStatus }}')"
                                            class="hover:bg-slate-50 transition">
                                            
                                            <td class="px-4 py-3 text-center font-bold text-slate-500 border-r border-gray-100">
                                                {{ $idx + 1 }}
                                            </td>

                                            <td class="px-4 py-3 border-r border-gray-100 font-bold">
                                                <div class="flex items-center gap-3">
                                                    <img src="{{ $st->photo_url }}" alt="{{ $st->full_name }}" class="w-9 h-9 rounded-full object-cover shrink-0 border border-gray-200 shadow-2xs" onerror="this.src='{{ asset('images/default-student.jpg') }}'">
                                                    <div class="truncate max-w-[220px]" title="{{ $st->full_name }}">
                                                        <div class="text-xs font-bold text-gray-900 truncate">{{ $st->full_name }}</div>
                                                        @if($stGroup)
                                                            @php $isStSched = in_array($st->id, $scheduledStudentIds ?? []); @endphp
                                                            @if($isStSched)
                                                                <span class="inline-block mt-0.5 text-[9px] font-black px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs" title="Grup {{ $stGroup }} (Hadir Fisik)"><i class="fas fa-check-circle text-[8px] mr-0.5"></i> Grup {{ $stGroup }}</span>
                                                            @else
                                                                <span class="inline-block mt-0.5 text-[9px] font-bold px-1.5 py-0.5 rounded bg-rose-100 text-rose-800 border border-rose-300 shadow-2xs" title="Grup {{ $stGroup }} (Tidak Hadir Fisik)"><i class="fas fa-times-circle text-[8px] mr-0.5"></i> Grup {{ $stGroup }}</span>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Status Kehadiran --}}
                                            <td class="px-4 py-3 border-r border-gray-100">
                                                @if($att)
                                                    @if($att->status === 'hadir' || $att->status === 'terlambat')
                                                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-100 text-emerald-950 border border-emerald-300 font-black text-xs">
                                                            <i class="fas fa-check-circle text-emerald-600"></i>
                                                            <span>Hadir {{ $att->time_in ? '('.substr($att->time_in, 0, 5).' WIB)' : '' }}</span>
                                                        </div>
                                                    @elseif($att->status === 'sakit')
                                                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-100 text-amber-950 border border-amber-300 font-black text-xs">
                                                            <i class="fas fa-briefcase-medical text-amber-600"></i>
                                                            <span>Sakit</span>
                                                        </div>
                                                    @elseif($att->status === 'izin')
                                                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-100 text-blue-950 border border-blue-300 font-black text-xs">
                                                            <i class="fas fa-envelope-open-text text-blue-600"></i>
                                                            <span>Izin</span>
                                                        </div>
                                                    @elseif($att->status === 'alpha')
                                                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-100 text-rose-950 border border-rose-300 font-black text-xs">
                                                            <i class="fas fa-times-circle text-rose-600"></i>
                                                            <span>Alpha</span>
                                                        </div>
                                                    @else
                                                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-800 border border-slate-300 font-black text-xs capitalize">
                                                            <span>{{ $att->status }}</span>
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-100 text-amber-950 border border-amber-300 font-black text-xs">
                                                        <i class="fas fa-clock text-amber-600"></i> Belum Presensi / Scan
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Catatan --}}
                                            <td class="px-4 py-3 text-xs text-slate-600">
                                                {{ $att?->notes ?: '—' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="p-8 text-center text-gray-400 font-bold">
                                                Tidak ada data siswa aktif pada kelas ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            {{-- ═════════════════════════════════════════════════════════════════ --}}
            {{-- 2. TAB MATRIKS KEHADIRAN HARIAN SEKOLAH (1 BULAN)                  --}}
            {{-- ═════════════════════════════════════════════════════════════════ --}}
            <div x-show="viewMode === 'matrix'" class="bg-white rounded-2xl shadow-sm border-2 border-slate-200 overflow-hidden print-force-show print-no-bg">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-bold text-gray-800 flex items-center gap-2 text-sm md:text-base">
                        <i class="fas fa-calendar-check text-purple-600"></i> Matriks Kehadiran Harian (Sekolah) — Bulan {{ $monthsList[$selectedMonth] ?? '' }} {{ $selectedYear }} ({{ $selectedClassroom->class_name }})
                    </h2>
                    <span class="text-xs text-gray-500 font-semibold hidden md:inline">H: Hadir | S: Sakit | I: Izin | A: Alpha</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-gray-600 font-bold text-xs uppercase">
                                <th class="px-3 py-3 text-center border-r border-gray-100 w-10">No</th>
                                <th class="px-4 py-3 border-r border-gray-100 min-w-[180px]">Nama Siswa</th>
                                @for($d = 1; $d <= $daysInMonth; $d++)
                                    <th class="px-1 py-2 text-center border-r border-gray-100 min-w-[24px]">{{ $d }}</th>
                                @endfor
                                <th class="px-2 py-2 text-center border-r border-gray-100 bg-green-50 text-green-700">H</th>
                                <th class="px-2 py-2 text-center border-r border-gray-100 bg-yellow-50 text-yellow-700">S</th>
                                <th class="px-2 py-2 text-center border-r border-gray-100 bg-blue-50 text-blue-700">I</th>
                                <th class="px-2 py-2 text-center border-r border-gray-100 bg-red-50 text-red-700">A</th>
                                <th class="px-2 py-2 text-center bg-purple-50 text-purple-700">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($classroomStudents as $idx => $st)
                                @php
                                    $stStat = $studentStats[$st->id] ?? ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0, 'percentage' => 0];
                                @endphp
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="px-3 py-2.5 text-center text-gray-500 font-bold border-r border-gray-100">{{ $idx + 1 }}</td>
                                    <td class="px-3 py-2.5 font-bold text-gray-900 border-r border-gray-100 min-w-[220px]">
                                        <div class="flex items-center gap-2.5">
                                            <img src="{{ $st->photo_url }}" alt="{{ $st->full_name }}" class="w-8 h-8 rounded-full object-cover shrink-0 border border-gray-200 shadow-sm" onerror="this.src='{{ asset('images/default-student.jpg') }}'">
                                            <div class="truncate max-w-[180px]" title="{{ $st->full_name }}">
                                                <div class="text-xs font-bold text-gray-900 truncate">{{ $st->full_name }}</div>
                                                <div class="text-[10px] text-gray-400 font-normal">NISN: {{ $st->nisn ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    @for($d = 1; $d <= $daysInMonth; $d++)
                                        @php
                                            $stStatus = $matrixMap[$st->id][$d] ?? null;
                                            $stBadge = match($stStatus) {
                                                'hadir' => 'bg-green-500 text-white',
                                                'sakit' => 'bg-yellow-400 text-black',
                                                'izin' => 'bg-blue-500 text-white',
                                                'alpha' => 'bg-red-500 text-white',
                                                default => 'text-gray-300'
                                            };
                                            $stChar = match($stStatus) {
                                                'hadir' => 'H',
                                                'sakit' => 'S',
                                                'izin' => 'I',
                                                'alpha' => 'A',
                                                default => '·'
                                            };
                                        @endphp
                                        <td class="px-1 py-1 text-center border-r border-gray-100">
                                            @if($stStatus)
                                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-md text-[10px] font-black {{ $stBadge }}">{{ $stChar }}</span>
                                            @else
                                                <span class="text-gray-300 font-bold text-xs select-none">·</span>
                                            @endif
                                        </td>
                                    @endfor
                                    <td class="px-2 py-2 text-center font-black text-green-700 bg-green-50/50 border-r border-gray-100">{{ $stStat['hadir'] > 0 ? $stStat['hadir'] : '-' }}</td>
                                    <td class="px-2 py-2 text-center font-black text-yellow-700 bg-yellow-50/50 border-r border-gray-100">{{ $stStat['sakit'] > 0 ? $stStat['sakit'] : '-' }}</td>
                                    <td class="px-2 py-2 text-center font-black text-blue-700 bg-blue-50/50 border-r border-gray-100">{{ $stStat['izin'] > 0 ? $stStat['izin'] : '-' }}</td>
                                    <td class="px-2 py-2 text-center font-black text-red-700 bg-red-50/50 border-r border-gray-100">{{ $stStat['alpha'] > 0 ? $stStat['alpha'] : '-' }}</td>
                                    <td class="px-2 py-2 text-center font-black text-purple-700 bg-purple-50/50">{{ ($stStat['hadir'] + $stStat['sakit'] + $stStat['izin'] + $stStat['alpha']) > 0 ? $stStat['percentage'].'%' : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $daysInMonth + 7 }}" class="p-8 text-center text-gray-400 font-bold">
                                        Tidak ada data siswa aktif pada kelas ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ═════════════════════════════════════════════════════════════════ --}}
            {{-- 3. TAB KEHADIRAN PELAJARAN SAYA (INPUT & REKAP)                    --}}
            {{-- ═════════════════════════════════════════════════════════════════ --}}
            <div x-show="viewMode === 'log'" class="bg-white rounded-2xl shadow-sm border-2 border-slate-200 overflow-hidden">
                <form action="{{ route('guru.absensi.store') }}" method="POST" id="formInputAbsensi">
                    @csrf
                    <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">

                    <div class="px-5 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3 bg-purple-50/50">
                        <div>
                            <h2 class="font-bold text-purple-900 flex items-center gap-2">
                                <i class="fas fa-chalkboard-teacher text-purple-500"></i> Rekap & Input Kehadiran Pelajaran Saya
                            </h2>
                            <div class="mt-1">
                                <button type="button" @click="editModeLesson = !editModeLesson" 
                                    :class="editModeLesson ? 'bg-amber-400 hover:bg-amber-300 text-black border-2 border-black' : 'bg-gray-900 hover:bg-gray-800 text-white border-2 border-black'"
                                    class="px-3.5 py-1.5 rounded-xl text-xs font-black transition flex items-center gap-2 shadow-sm print-hide cursor-pointer">
                                    <template x-if="!editModeLesson">
                                        <span class="flex items-center gap-1.5"><i class="fas fa-lock text-amber-400"></i> Mode Terkunci (Klik untuk Buka Mode Edit)</span>
                                    </template>
                                    <template x-if="editModeLesson">
                                        <span class="flex items-center gap-1.5"><i class="fas fa-unlock text-black"></i> Mode Edit Aktif (Klik untuk Kunci Kembali)</span>
                                    </template>
                                </button>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm md:text-base font-black text-purple-900 bg-purple-100/90 border border-purple-200 px-4 py-1.5 rounded-xl mb-1 inline-block shadow-sm">
                                {{ $selectedClassroom->class_name }} ({{ $monthsList[$selectedMonth] ?? '' }} {{ $selectedYear }})
                            </div>
                            @if(isset($assignmentInfo))
                            <div class="text-xs md:text-sm font-black text-purple-900 mt-0.5">
                                {{ $assignmentInfo }}
                            </div>
                            @endif
                            @if(isset($targetGroup))
                            <div class="flex items-center justify-end gap-1.5 mt-1 flex-wrap">
                                <span class="text-[10px] font-black px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                                    <i class="fas fa-check-circle text-emerald-600"></i> Hadir Fisik: Grup {{ $targetGroup }}
                                </span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-rose-100 text-rose-800 border border-rose-300 shadow-2xs">
                                    <i class="fas fa-times-circle text-rose-600"></i> Tidak Hadir Fisik: Grup {{ $targetGroup === 'A' ? 'B' : 'A' }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Toolbar Form Input Pelajaran (Hanya Muncul saat editModeLesson = true) --}}
                    @php
                        $inputDay = (int)\Carbon\Carbon::parse($selectedInputDate)->format('j');
                        $inputMonth = (int)\Carbon\Carbon::parse($selectedInputDate)->format('n');
                        $inputYear = (int)\Carbon\Carbon::parse($selectedInputDate)->format('Y');
                    @endphp
                    <div x-show="editModeLesson" x-transition class="px-5 py-3 bg-purple-100/60 border-b border-purple-200 flex flex-wrap items-center justify-between gap-3 print-hide">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-black text-purple-950 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-edit text-purple-600"></i> Tanggal:
                            </span>
                            <input type="date" name="date" value="{{ $selectedInputDate }}" 
                                onchange="window.location.href='?classroom_id={{ $selectedClassroomId }}&month={{ $selectedMonth }}&year={{ $selectedYear }}&input_date=' + this.value + '&viewMode=log'"
                                class="text-xs font-bold border-2 border-purple-300 rounded-xl px-3 py-1.5 bg-white text-purple-950 shadow-sm outline-none focus:border-purple-600 cursor-pointer">
                            <span class="text-[11px] font-bold text-purple-800 bg-purple-200/80 px-2.5 py-1 rounded-lg">
                                Kolom Tgl {{ $inputDay }} Siap Diisi
                            </span>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            @if($targetGroup && count($scheduledStudentIds) < $classroomStudents->count())
                                <button type="button" onclick="markScheduledHadir()" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black shadow-sm transition flex items-center gap-1.5 border border-emerald-800 cursor-pointer" style="color: #ffffff !important;" title="Hadirkan hanya kelompok siswa yang terjadwal aktif di kelas ini">
                                    <i class="fas fa-check-double"></i> Hadirkan Grup Terjadwal (Grup {{ $targetGroup }}: {{ count($scheduledStudentIds) }})
                                </button>
                                <button type="button" onclick="markAllHadir()" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black shadow-sm transition flex items-center gap-1.5 border border-indigo-800 cursor-pointer" style="color: #ffffff !important;" title="Hadirkan semua siswa di kelas ini jika seluruh kelas masuk">
                                    <i class="fas fa-users"></i> Hadirkan Semua Siswa Kelas ({{ $classroomStudents->count() }})
                                </button>
                            @else
                                <button type="button" onclick="markAllHadir()" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black shadow-sm transition flex items-center gap-1.5 border border-emerald-800 cursor-pointer" style="color: #ffffff !important;">
                                    <i class="fas fa-check-double"></i> Hadirkan Semua Siswa ({{ $classroomStudents->count() }})
                                </button>
                            @endif
                            <button type="submit" class="px-4 py-1.5 bg-amber-400 hover:bg-amber-300 text-black border-2 border-black rounded-xl text-xs font-black uppercase tracking-wider shadow-md transition flex items-center gap-1.5 cursor-pointer" style="color: #000000 !important; background-color: #fbbf24 !important;">
                                <i class="fas fa-save text-black"></i> Simpan Absensi
                            </button>
                            <button type="button" onclick="confirmDeleteSelectedDate()" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black shadow-sm transition flex items-center gap-1.5 border border-rose-800 cursor-pointer" style="color: #ffffff !important;" title="Hapus seluruh absensi pada tanggal terpilih">
                                <i class="fas fa-trash-alt"></i> Hapus Absen Tgl Ini
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="bg-slate-900 text-white font-bold text-xs" style="background-color: #0f172a !important; color: #ffffff !important;">
                                    <th class="px-3 py-3 text-center border-r border-slate-700 w-10 text-white">No</th>
                                    <th class="px-4 py-3 text-left border-r border-slate-700 min-w-[240px] text-white">Nama Siswa</th>
                                    @foreach($lessonDates ?? [] as $d)
                                        <th class="px-1 py-2 text-center border-r border-slate-700 min-w-[24px] text-white {{ $d == $inputDay ? 'bg-amber-400 text-black' : '' }}">{{ $d }}</th>
                                    @endforeach
                                    <th class="px-2 py-2 text-center bg-green-900/60 text-white">H</th>
                                    <th class="px-2 py-2 text-center bg-yellow-900/60 text-white">S</th>
                                    <th class="px-2 py-2 text-center bg-blue-900/60 text-white">I</th>
                                    <th class="px-2 py-2 text-center bg-red-900/60 text-white">A</th>
                                    <th class="px-2 py-2 text-center bg-purple-900/60 text-white">%</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($classroomStudents as $idx => $st)
                                    @php
                                        $stStat = $lessonStudentStats[$st->id] ?? ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0, 'percentage' => 0];
                                        $stGroup = $studentBlockGroups[$st->id] ?? null;
                                        $isScheduled = in_array($st->id, $scheduledStudentIds ?? []);
                                    @endphp
                                    <tr class="transition {{ !$isScheduled ? 'bg-slate-50/70 hover:bg-slate-100/70' : 'hover:bg-purple-50/30' }}">
                                        <td class="px-3 py-2.5 text-center font-bold {{ !$isScheduled ? 'text-slate-500' : 'text-gray-500' }} border-r border-gray-100">{{ $idx + 1 }}</td>
                                        <td class="px-3 py-2.5 font-bold border-r border-gray-100 min-w-[240px]">
                                            <div class="flex items-center gap-2.5">
                                                <img src="{{ $st->photo_url }}" alt="{{ $st->full_name }}" class="w-8 h-8 rounded-full object-cover shrink-0 border-2 {{ !$isScheduled ? 'border-rose-300 shadow-xs' : 'border-emerald-400 shadow-xs' }}" onerror="this.src='{{ asset('images/default-student.jpg') }}'">
                                                <div class="truncate max-w-[190px]" title="{{ $st->full_name }}">
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <span class="{{ !$isScheduled ? 'text-slate-700 font-semibold' : 'text-gray-900 font-bold' }}">
                                                             {{ $st->full_name }}
                                                        </span>
                                                        @if($stGroup)
                                                            @if($isScheduled)
                                                                <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs" title="Grup {{ $stGroup }} (Hadir Fisik di Ruangan)"><i class="fas fa-check-circle text-[8px] mr-0.5"></i> Grup {{ $stGroup }}</span>
                                                            @else
                                                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-rose-100 text-rose-800 border border-rose-300 shadow-2xs" title="Grup {{ $stGroup }} (Tidak Hadir Fisik di Ruangan)"><i class="fas fa-times-circle text-[8px] mr-0.5"></i> Grup {{ $stGroup }}</span>
                                                            @endif
                                                        @endif
                                                    </div>
                                                    <div class="text-[9px] {{ !$isScheduled ? 'text-slate-400 font-normal' : 'text-gray-400 font-normal' }}">
                                                        NISN: {{ $st->nisn ?? '-' }} 
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        @forelse($lessonDates ?? [] as $d)
                                            @php
                                                $isInputCol = ($d === $inputDay && (int)$selectedMonth === $inputMonth && (int)$selectedYear === $inputYear);
                                                $stStatus = $lessonMatrixMap[$st->id][$d] ?? null;
                                                $stBadge = match($stStatus) {
                                                    'hadir' => 'bg-green-500 text-white',
                                                    'sakit' => 'bg-yellow-400 text-black',
                                                    'izin' => 'bg-blue-500 text-white',
                                                    'alpha' => 'bg-red-500 text-white',
                                                    default => 'text-gray-300'
                                                };
                                                $stChar = match($stStatus) {
                                                    'hadir' => 'H',
                                                    'sakit' => 'S',
                                                    'izin' => 'I',
                                                    'alpha' => 'A',
                                                    default => '?'
                                                };
                                            @endphp
                                            <td class="px-1 py-1.5 text-center border-r border-gray-100 {{ $isInputCol ? 'bg-amber-50/80 border-x-2 border-amber-300' : '' }}">
                                                @if($isInputCol)
                                                    {{-- Mode Edit Pelajaran (editModeLesson = true) --}}
                                                    @php
                                                        $currVal = $stStatus ?? ($isScheduled ? 'hadir' : '');
                                                    @endphp
                                                    <div x-show="editModeLesson" x-transition x-data="{ status: '{{ $currVal }}' }" 
                                                        @mark-scheduled-hadir.window="if ({{ $isScheduled ? 'true' : 'false' }}) status = 'hadir'" 
                                                        @mark-all-hadir.window="status = 'hadir'" 
                                                        class="inline-flex items-center gap-0.5 bg-white p-0.5 rounded-lg border {{ $isScheduled ? 'border-purple-200 shadow-sm' : 'border-slate-300 bg-slate-50/40' }} print-hide">
                                                        <input type="hidden" name="statuses[{{ $st->id }}]" :value="status">
                                                        <button type="button" @click="status = 'hadir'" :class="status === 'hadir' ? 'bg-green-500 text-white font-black shadow-sm' : 'text-gray-400 hover:bg-gray-100 font-semibold'" class="w-5 h-5 flex items-center justify-center rounded text-[10px] transition cursor-pointer" title="Hadir">H</button>
                                                        <button type="button" @click="status = 'sakit'" :class="status === 'sakit' ? 'bg-yellow-400 text-black font-black shadow-sm' : 'text-gray-400 hover:bg-gray-100 font-semibold'" class="w-5 h-5 flex items-center justify-center rounded text-[10px] transition cursor-pointer" title="Sakit">S</button>
                                                        <button type="button" @click="status = 'izin'" :class="status === 'izin' ? 'bg-blue-500 text-white font-black shadow-sm' : 'text-gray-400 hover:bg-gray-100 font-semibold'" class="w-5 h-5 flex items-center justify-center rounded text-[10px] transition cursor-pointer" title="Izin">I</button>
                                                        <button type="button" @click="status = 'alpha'" :class="status === 'alpha' ? 'bg-red-500 text-white font-black shadow-sm' : 'text-gray-400 hover:bg-gray-100 font-semibold'" class="w-5 h-5 flex items-center justify-center rounded text-[10px] transition cursor-pointer" title="Alpha">A</button>
                                                        <button type="button" @click="status = ''" :class="status === '' ? 'bg-slate-300 text-slate-700 font-black' : 'text-gray-300 hover:bg-gray-100'" class="w-5 h-5 flex items-center justify-center rounded text-[10px] transition cursor-pointer" title="Tidak Hadir / Kosongkan">—</button>
                                                    </div>
                                                    {{-- Mode Terkunci Pelajaran (editModeLesson = false) --}}
                                                    <div x-show="!editModeLesson">
                                                        @if($stStatus)
                                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-md text-[10px] font-black {{ $stBadge }}">{{ $stChar }}</span>
                                                        @elseif(!$isScheduled)
                                                            <span class="text-rose-900 font-black text-xs select-none" title="Kelompok tidak di kelas ini (sedang di ruang lain)">—</span>
                                                        @else
                                                            <span class="text-gray-300 font-bold text-xs select-none">·</span>
                                                        @endif
                                                    </div>
                                                @elseif($stStatus)
                                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md text-[10px] font-black {{ $stBadge }}">{{ $stChar }}</span>
                                                @elseif(!$isScheduled)
                                                    <span class="text-rose-900 font-black text-xs select-none" title="Kelompok tidak di kelas ini (sedang di ruang lain)">—</span>
                                                @else
                                                    <span class="text-gray-300 font-bold text-xs select-none">·</span>
                                                @endif
                                            </td>
                                        @empty
                                            <td class="px-3 py-1.5 text-center border-r border-gray-100 text-gray-300">-</td>
                                        @endforelse
                                        <td class="px-2 py-2 text-center font-black text-green-700 bg-green-50/50 border-r border-gray-100">{{ $stStat['hadir'] > 0 ? $stStat['hadir'] : '-' }}</td>
                                        <td class="px-2 py-2 text-center font-black text-yellow-700 bg-yellow-50/50 border-r border-gray-100">{{ $stStat['sakit'] > 0 ? $stStat['sakit'] : '-' }}</td>
                                        <td class="px-2 py-2 text-center font-black text-blue-700 bg-blue-50/50 border-r border-gray-100">{{ $stStat['izin'] > 0 ? $stStat['izin'] : '-' }}</td>
                                        <td class="px-2 py-2 text-center font-black text-red-700 bg-red-50/50 border-r border-gray-100">{{ $stStat['alpha'] > 0 ? $stStat['alpha'] : '-' }}</td>
                                        <td class="px-2 py-2 text-center font-black text-purple-700 bg-purple-50/50">{{ ($stStat['hadir'] + $stStat['sakit'] + $stStat['izin'] + $stStat['alpha']) > 0 ? $stStat['percentage'].'%' : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($lessonDates ?? []) + 7 }}" class="p-8 text-center text-gray-400 font-bold">
                                            Tidak ada data siswa aktif pada kelas ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

<form id="formDeleteSelectedDate" action="{{ route('guru.absensi.destroyDate') }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
    <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
    <input type="hidden" name="date" value="{{ $selectedInputDate }}">
</form>

<script>
function markScheduledHadir() {
    window.dispatchEvent(new CustomEvent('mark-scheduled-hadir'));
}

function markAllHadir() {
    window.dispatchEvent(new CustomEvent('mark-all-hadir'));
}

function markAllUnscannedDailyHadir() {
    window.dispatchEvent(new CustomEvent('mark-unscanned-daily-hadir'));
}

function confirmDeleteSelectedDate() {
    let formattedDate = '{{ \Carbon\Carbon::parse($selectedInputDate)->format('d/m/Y') }}';
    if (confirm('Apakah Anda yakin ingin MENGHAPUS SELURUH absensi pada tanggal ' + formattedDate + '?\n\nSemua data absensi pada tanggal ini akan dibersihkan dan kolom tanggal akan otomatis hilang jika tidak ada absensi tersimpan.')) {
        document.getElementById('formDeleteSelectedDate').submit();
    }
}
</script>
@endsection
