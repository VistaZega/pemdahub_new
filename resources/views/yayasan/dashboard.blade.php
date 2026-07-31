@extends('layouts.yayasan')

@section('title', 'Dashboard Kebijakan Strategis - Ketua Yayasan')

@section('content')
<div class="space-y-6">
    {{-- ════════════════ HERO BANNER STRATEGIS YAYASAN ════════════════ --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-violet-700 via-purple-700 to-indigo-800 rounded-3xl p-6 md:p-8 text-white shadow-xl">
        {{-- Background decorative shapes --}}
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute right-1/3 -top-10 w-48 h-48 bg-violet-400/20 rounded-full blur-xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 5h4M7 3h10M7 7h10"></path>
                        </svg>
                    </div>
                    <span class="bg-white/20 backdrop-blur-md px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider text-violet-100 border border-white/10">
                        Eksekutif & Kebijakan Strategis
                    </span>
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Dashboard Strategic Analytics Yayasan</h1>
                <p class="text-violet-100 text-sm md:text-base max-w-2xl">
                    Executive Decision Support System terpadu membaca Kehadiran, Keuangan, LMS, CBT, dan SDM <strong>Yayasan PEMBDA Nias</strong>.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                @if($currentAcademicYear)
                <div class="flex items-center gap-2.5 bg-white/15 backdrop-blur-md border border-white/20 px-4 py-2.5 rounded-2xl text-xs font-bold">
                    <svg class="w-4 h-4 text-violet-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>TP {{ $currentAcademicYear->year }}</span>
                </div>
                @endif

                <a href="{{ route('yayasan.progress-input') }}" class="inline-flex items-center justify-center gap-2 bg-amber-400 hover:bg-amber-300 text-slate-900 px-4 py-2.5 rounded-2xl text-xs font-extrabold shadow-lg shadow-amber-400/20 transition-all hover:scale-105 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span>Progress Input Data</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ════════════════ EXECUTIVE TOP KPI SUMMARY CARDS ════════════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        {{-- Card 1: Unit Pendidikan --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Unit Pendidikan</span>
                    <h3 class="text-2xl md:text-3xl font-extrabold text-slate-900 mt-1">{{ $stats['total_schools'] }} <span class="text-xs font-normal text-slate-500">Sekolah</span></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-violet-100 text-violet-700 flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 5h4M7 3h10M7 7h10"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span class="font-medium">Jenjang Aktif:</span>
                <span class="font-bold text-violet-700">SMP • SMA • SMK</span>
            </div>
        </div>

        {{-- Card 2: Total Siswa Aktif --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Siswa Aktif</span>
                    <h3 class="text-2xl md:text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($stats['total_students']) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Rata-rata Presensi Siswa:</span>
                <span class="font-bold text-blue-700">📈 {{ round(array_sum($chartData['student_attendance_rates'])/max(1, count($chartData['student_attendance_rates'])), 1) }}%</span>
            </div>
        </div>

        {{-- Card 3: Total SDM Pegawai --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total SDM Pegawai</span>
                    <h3 class="text-2xl md:text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($stats['total_employees']) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Rata-rata Presensi SDM:</span>
                <span class="font-bold text-emerald-700">⏱️ {{ round(array_sum($chartData['employee_attendance_rates'])/max(1, count($chartData['employee_attendance_rates'])), 1) }}%</span>
            </div>
        </div>

        {{-- Card 4: Realisasi Keuangan --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Realisasi Keuangan</span>
                    <h3 class="text-2xl md:text-3xl font-extrabold text-slate-900 mt-1">{{ $stats['realization_rate'] }}%</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Terbayar Lunas:</span>
                <span class="font-bold text-amber-700">Rp {{ number_format($stats['total_paid'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- ════════════════ AI STRATEGIC POLICY ENGINE (REKOMENDASI AI YAYASAN) ════════════════ --}}
    <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 text-white shadow-xl border border-indigo-900/50">
        <div class="flex items-center justify-between mb-5 border-b border-white/10 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white shadow-lg">
                    🤖
                </div>
                <div>
                    <h3 class="text-lg font-extrabold text-white flex items-center gap-2">
                        AI Strategic Policy Insights
                        <span class="bg-violet-500/30 text-violet-300 border border-violet-400/30 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">Real-Time Evaluation</span>
                    </h3>
                    <p class="text-xs text-slate-300">Rekomendasi kebijakan strategis berbasis pembacaan data otomatis untuk Yayasan PEMBDA Nias</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            {{-- Insight Keuangan --}}
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10 hover:bg-white/15 transition-all">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-2.5 h-2.5 rounded-full @if($aiInsights['keuangan']['status']==='optimal') bg-emerald-400 @else bg-amber-400 @endif"></span>
                    <h4 class="text-sm font-bold text-amber-300">💡 {{ $aiInsights['keuangan']['title'] }}</h4>
                </div>
                <p class="text-xs text-slate-200 leading-relaxed">{{ $aiInsights['keuangan']['summary'] }}</p>
                <div class="mt-3 pt-2.5 border-t border-white/10 text-[11px] text-indigo-200 font-medium">
                    📌 <strong>Aksi Strategis:</strong> {{ $aiInsights['keuangan']['action'] }}
                </div>
            </div>

            {{-- Insight Presensi & SDM --}}
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10 hover:bg-white/15 transition-all">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                    <h4 class="text-sm font-bold text-emerald-300">🎯 {{ $aiInsights['sdm_presensi']['title'] }}</h4>
                </div>
                <p class="text-xs text-slate-200 leading-relaxed">{{ $aiInsights['sdm_presensi']['summary'] }}</p>
                <div class="mt-3 pt-2.5 border-t border-white/10 text-[11px] text-indigo-200 font-medium">
                    📌 <strong>Aksi Strategis:</strong> {{ $aiInsights['sdm_presensi']['action'] }}
                </div>
            </div>

            {{-- Insight LMS & CBT --}}
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10 hover:bg-white/15 transition-all">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-sky-400"></span>
                    <h4 class="text-sm font-bold text-sky-300">🚀 {{ $aiInsights['digital_lms_cbt']['title'] }}</h4>
                </div>
                <p class="text-xs text-slate-200 leading-relaxed">{{ $aiInsights['digital_lms_cbt']['summary'] }}</p>
                <div class="mt-3 pt-2.5 border-t border-white/10 text-[11px] text-indigo-200 font-medium">
                    📌 <strong>Aksi Strategis:</strong> {{ $aiInsights['digital_lms_cbt']['action'] }}
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════ GRAFIK ANALYTICS PINTAR (GRID 2 KOLOM PER BARIS) ════════════════ --}}
    {{-- BARIS 1: Presensi & Keuangan --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- GRAFIK 1: Presensi Kehadiran Siswa & Pegawai --}}
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-indigo-600"></span>
                        1. Presensi Kehadiran (Siswa vs Pegawai)
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Tingkat kehadiran presensi di SMP, SMA, dan SMK</p>
                </div>
                <div class="flex items-center gap-3 text-xs font-bold">
                    <span class="flex items-center gap-1 text-slate-700"><span class="w-3 h-3 rounded-md bg-indigo-600"></span> Siswa (%)</span>
                    <span class="flex items-center gap-1 text-slate-700"><span class="w-3 h-3 rounded-md bg-emerald-500"></span> Pegawai (%)</span>
                </div>
            </div>

            <div class="relative w-full h-[280px]">
                <canvas id="attendanceRateChart"></canvas>
            </div>
        </div>

        {{-- GRAFIK 2: Realisasi Pembayaran vs Tunggakan Keuangan --}}
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        2. Realisasi Keuangan vs Tunggakan Tagihan
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Status penerimaan dana tagihan siswa (dalam Rupiah)</p>
                </div>
                <div class="flex items-center gap-3 text-xs font-bold">
                    <span class="flex items-center gap-1 text-slate-700"><span class="w-3 h-3 rounded-md bg-emerald-500"></span> Terbayar</span>
                    <span class="flex items-center gap-1 text-slate-700"><span class="w-3 h-3 rounded-md bg-rose-400"></span> Tunggakan</span>
                </div>
            </div>

            <div class="relative w-full h-[280px]">
                <canvas id="financialRealizationChart"></canvas>
            </div>
        </div>
    </div>

    {{-- BARIS 2: LMS & CBT --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- GRAFIK 3: Adopsi LMS Digital Learning --}}
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-sky-500"></span>
                        3. Adopsi LMS & Pembelajaran Digital
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Jumlah kursus & mata pelajaran digital aktif di LMS per sekolah</p>
                </div>
                <span class="bg-sky-100 text-sky-800 text-[11px] font-extrabold px-3 py-1 rounded-full">
                    Total {{ array_sum($chartData['lms_engagement']) }} Kursus
                </span>
            </div>

            <div class="relative w-full h-[280px]">
                <canvas id="lmsAdoptionChart"></canvas>
            </div>
        </div>

        {{-- GRAFIK 4: Evaluasi Ujian Digital CBT --}}
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-violet-600"></span>
                        4. Rata-Rata Nilai Evaluasi Ujian CBT
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Capaian skor rata-rata ujian Computer Based Test per sekolah</p>
                </div>
                <span class="bg-violet-100 text-violet-800 text-[11px] font-extrabold px-3 py-1 rounded-full">
                    Rata-rata: {{ round(array_sum($chartData['cbt_scores'])/max(1, count($chartData['cbt_scores'])), 1) }} / 100
                </span>
            </div>

            <div class="relative w-full h-[280px]">
                <canvas id="cbtPerformanceChart"></canvas>
            </div>
        </div>
    </div>

    {{-- BARIS 3: Komposisi Siswa & SDM --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- GRAFIK 5: Populasi Siswa & Kecukupan SDM --}}
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                        5. Populasi Siswa & Distribusi SDM
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Jumlah Siswa, Guru, dan Staf di setiap unit sekolah</p>
                </div>
                <div class="flex items-center gap-2 text-xs font-bold">
                    <span class="flex items-center gap-1 text-slate-700"><span class="w-3 h-3 rounded-md bg-indigo-600"></span> Siswa</span>
                    <span class="flex items-center gap-1 text-slate-700"><span class="w-3 h-3 rounded-md bg-emerald-500"></span> Guru</span>
                    <span class="flex items-center gap-1 text-slate-700"><span class="w-3 h-3 rounded-md bg-amber-500"></span> Staf</span>
                </div>
            </div>

            <div class="relative w-full h-[280px]">
                <canvas id="schoolPopulationChart"></canvas>
            </div>
        </div>

        {{-- GRAFIK 6: Demografi Gender & Rasio SDM --}}
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                        6. Demografi Gender Siswa & Rasio SDM
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Proporsi Siswa Laki-laki vs Perempuan dan Pendidik vs Tendik</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 my-2">
                <div class="relative w-full h-[180px] flex items-center justify-center">
                    <canvas id="genderDemographicChart"></canvas>
                </div>
                <div class="relative w-full h-[180px] flex items-center justify-center">
                    <canvas id="sdmCompositionChart"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-100 text-center text-xs">
                <div class="bg-blue-50/60 p-2 rounded-xl border border-blue-100 font-bold text-blue-800">
                    👦 Laki-laki: {{ number_format($chartData['total_male']) }} | 👧 Perempuan: {{ number_format($chartData['total_female']) }}
                </div>
                <div class="bg-emerald-50/60 p-2 rounded-xl border border-emerald-100 font-bold text-emerald-800">
                    👨‍🏫 Guru: {{ number_format(array_sum($chartData['teachers'])) }} | 💼 Staf: {{ number_format(array_sum($chartData['staff'])) }}
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════ TABEL RINGKASAN STRATEGIS UNIT SEKOLAH ════════════════ --}}
    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 5h4M7 3h10M7 7h10"></path>
                    </svg>
                    Matriks Evaluasi Kinerja Unit Sekolah Terpadu
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Rincian data per unit sekolah untuk evaluasi presensi, CBT, LMS, keuangan, dan SDM</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-600 text-xs uppercase font-extrabold border-b border-slate-100">
                        <th class="py-3.5 px-4 rounded-l-2xl">Unit Sekolah</th>
                        <th class="py-3.5 px-4 text-center">Presensi Siswa / SDM</th>
                        <th class="py-3.5 px-4 text-center">LMS / Skor CBT</th>
                        <th class="py-3.5 px-4 text-center">Siswa (L / P)</th>
                        <th class="py-3.5 px-4 text-center">SDM (Guru / Staf)</th>
                        <th class="py-3.5 px-4 text-right">Realisasi Keuangan</th>
                        <th class="py-3.5 px-4 text-center rounded-r-2xl">Hari Aktif</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($schoolSummaries as $school)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-violet-100 text-violet-700 flex items-center justify-center font-bold text-xs">
                                    {{ $school['type'] }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900">{{ $school['name'] }}</p>
                                    <span class="text-[11px] text-slate-400">ID Unit: #{{ $school['id'] }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="font-bold text-indigo-700">Siswa: {{ $school['student_att_rate'] }}%</span>
                            <span class="block text-[11px] text-emerald-600 font-bold">SDM: {{ $school['employee_att_rate'] }}%</span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="font-bold text-sky-700">LMS: {{ $school['lms_courses'] }} Kursus</span>
                            <span class="block text-[11px] text-violet-600 font-bold">CBT Avg: {{ $school['cbt_avg_score'] }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="font-bold text-slate-900">{{ number_format($school['student_count']) }}</span>
                            <span class="block text-[11px] text-slate-400">({{ $school['male_students'] }} L | {{ $school['female_students'] }} P)</span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="font-bold text-slate-900">{{ number_format($school['employee_count']) }}</span>
                            <span class="block text-[11px] text-slate-400">({{ $school['teacher_count'] }} Guru | {{ $school['staff_count'] }} Staf)</span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <span class="font-extrabold text-emerald-600">Rp {{ number_format($school['paid'], 0, ',', '.') }}</span>
                            <span class="block text-[11px] text-slate-400">Target: Rp {{ number_format($school['billed'], 0, ',', '.') }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-700 px-3 py-1 rounded-xl text-xs font-bold">
                                {{ $school['active_days'] }} Hari
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-violet-50/70 font-extrabold text-violet-900">
                        <td class="py-3.5 px-4 rounded-l-2xl">TOTAL PEMBDA NIAS</td>
                        <td class="py-3.5 px-4 text-center">Presensi Sehat</td>
                        <td class="py-3.5 px-4 text-center">{{ array_sum($chartData['lms_engagement']) }} Kursus LMS</td>
                        <td class="py-3.5 px-4 text-center">{{ number_format($stats['total_students']) }} Siswa</td>
                        <td class="py-3.5 px-4 text-center">{{ number_format($stats['total_employees']) }} Pegawai</td>
                        <td class="py-3.5 px-4 text-right text-emerald-700">Rp {{ number_format($stats['total_paid'], 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-center rounded-r-2xl">—</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- Chart.js Script CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const schoolsData = @json($chartData['schools']);
    const studentsData = @json($chartData['students']);
    const teachersData = @json($chartData['teachers']);
    const staffData = @json($chartData['staff']);
    const billedData = @json($chartData['billed']);
    const paidData = @json($chartData['paid']);
    const unpaidData = @json($chartData['unpaid']);
    const studentAttRates = @json($chartData['student_attendance_rates']);
    const employeeAttRates = @json($chartData['employee_attendance_rates']);
    const lmsEngagement = @json($chartData['lms_engagement']);
    const cbtScores = @json($chartData['cbt_scores']);
    const totalMale = {{ $chartData['total_male'] }};
    const totalFemale = {{ $chartData['total_female'] }};

    // Chart Font Family & Default Options
    Chart.defaults.font.family = "'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, sans-serif";
    Chart.defaults.color = '#64748b';

    // 1. Chart Presensi (Line / Bar)
    new Chart(document.getElementById('attendanceRateChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: schoolsData,
            datasets: [
                {
                    label: 'Siswa (%)',
                    data: studentAttRates,
                    backgroundColor: '#4f46e5',
                    borderRadius: 8,
                    barPercentage: 0.5,
                },
                {
                    label: 'Pegawai (%)',
                    data: employeeAttRates,
                    backgroundColor: '#10b981',
                    borderRadius: 8,
                    barPercentage: 0.5,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { min: 60, max: 100, ticks: { callback: v => v + '%' } }
            }
        }
    });

    // 2. Chart Keuangan (Stacked Bar)
    new Chart(document.getElementById('financialRealizationChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: schoolsData,
            datasets: [
                {
                    label: 'Terbayar',
                    data: paidData,
                    backgroundColor: '#10b981',
                    borderRadius: 8,
                    stack: 'Stack 0',
                },
                {
                    label: 'Tunggakan',
                    data: unpaidData,
                    backgroundColor: '#fb7185',
                    borderRadius: 8,
                    stack: 'Stack 0',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: {
                    ticks: {
                        callback: function(v) {
                            if (v >= 1000000000) return 'Rp ' + (v / 1000000000).toFixed(1) + 'B';
                            if (v >= 1000000) return 'Rp ' + (v / 1000000).toFixed(0) + 'M';
                            return 'Rp ' + v;
                        }
                    }
                }
            }
        }
    });

    // 3. Chart LMS (Bar Chart)
    new Chart(document.getElementById('lmsAdoptionChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: schoolsData,
            datasets: [{
                label: 'Mata Pelajaran LMS',
                data: lmsEngagement,
                backgroundColor: '#0284c7', // Sky 600
                borderRadius: 10,
                barPercentage: 0.5,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true }
            }
        }
    });

    // 4. Chart CBT Average Score (Line / Bar)
    new Chart(document.getElementById('cbtPerformanceChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: schoolsData,
            datasets: [{
                label: 'Skor Ujian CBT',
                data: cbtScores,
                borderColor: '#7c3aed', // Violet 600
                backgroundColor: 'rgba(124, 58, 237, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 6,
                pointBackgroundColor: '#7c3aed',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { min: 50, max: 100 }
            }
        }
    });

    // 5. Chart Populasi Per Sekolah (Grouped Bar)
    new Chart(document.getElementById('schoolPopulationChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: schoolsData,
            datasets: [
                { label: 'Siswa', data: studentsData, backgroundColor: '#4f46e5', borderRadius: 6, barPercentage: 0.6 },
                { label: 'Guru', data: teachersData, backgroundColor: '#10b981', borderRadius: 6, barPercentage: 0.6 },
                { label: 'Staf', data: staffData, backgroundColor: '#f59e0b', borderRadius: 6, barPercentage: 0.6 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { grid: { display: false } }, y: { beginAtZero: true } }
        }
    });

    // 6. Chart Demografi Gender Siswa (Doughnut)
    new Chart(document.getElementById('genderDemographicChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Laki-laki', 'Perempuan'],
            datasets: [{
                data: [totalMale, totalFemale],
                backgroundColor: ['#3b82f6', '#f43f5e'],
                borderWidth: 3,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: { legend: { display: false } }
        }
    });

    // 7. Chart Komposisi SDM (Doughnut)
    const totalTeachers = teachersData.reduce((a, b) => a + b, 0);
    const totalStaff = staffData.reduce((a, b) => a + b, 0);

    new Chart(document.getElementById('sdmCompositionChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Guru', 'Staf'],
            datasets: [{
                data: [totalTeachers, totalStaff],
                backgroundColor: ['#10b981', '#f59e0b'],
                borderWidth: 3,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: { legend: { display: false } }
        }
    });
});
</script>
@endsection
