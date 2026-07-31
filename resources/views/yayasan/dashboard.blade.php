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
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Dashboard Overview Yayasan</h1>
                <p class="text-violet-100 text-sm md:text-base max-w-2xl">
                    Monitoring data terintegrasi seluruh unit sekolah di bawah naungan <strong>Yayasan Perguruan Pembangunan Daerah (PEMBDA) Nias</strong>.
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
                <span class="font-medium">Jenjang Terdaftar:</span>
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
                <span class="text-slate-500">Demografi Gender:</span>
                <span class="font-bold text-slate-800">👦 {{ number_format($chartData['total_male']) }} | 👧 {{ number_format($chartData['total_female']) }}</span>
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
                <span class="text-slate-500">Pendidik & Tendik:</span>
                <span class="font-bold text-emerald-700">👨‍🏫 {{ number_format(array_sum($chartData['teachers'])) }} Guru | 💼 {{ number_format(array_sum($chartData['staff'])) }} Staf</span>
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
                <span class="text-slate-500">Terbayar vs Tunggakan:</span>
                <span class="font-bold text-amber-700">Rp {{ number_format($stats['total_paid'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- ════════════════ GRAFIK-GRAFIK STRATEGIS YAYASAN ════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Chart 1: Distribusi Siswa & SDM Per Unit Sekolah (Column 8) --}}
        <div class="lg:col-span-8 bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-violet-600"></span>
                        Distribusi Siswa & SDM Per Unit Sekolah
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Perbandingan statistik jumlah Siswa, Guru, dan Staf di SMP, SMA, dan SMK</p>
                </div>
                <div class="flex items-center gap-3 text-xs font-semibold">
                    <span class="flex items-center gap-1 text-slate-600"><span class="w-3 h-3 rounded-md bg-indigo-600"></span> Siswa</span>
                    <span class="flex items-center gap-1 text-slate-600"><span class="w-3 h-3 rounded-md bg-emerald-500"></span> Guru</span>
                    <span class="flex items-center gap-1 text-slate-600"><span class="w-3 h-3 rounded-md bg-amber-500"></span> Staf</span>
                </div>
            </div>

            <div class="relative w-full h-[320px]">
                <canvas id="schoolPopulationChart"></canvas>
            </div>
        </div>

        {{-- Chart 2: Demografi Gender Siswa (Column 4) --}}
        <div class="lg:col-span-4 bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    Demografi Gender Siswa
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Proporsi Laki-laki vs Perempuan seluruh unit</p>
            </div>

            <div class="relative w-full h-[220px] my-4 flex items-center justify-center">
                <canvas id="genderDemographicChart"></canvas>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-100 text-center">
                <div class="bg-blue-50/60 p-2.5 rounded-2xl border border-blue-100">
                    <span class="text-[11px] font-bold text-blue-600 uppercase">Laki-laki</span>
                    <p class="text-lg font-extrabold text-slate-900 mt-0.5">{{ number_format($chartData['total_male']) }}</p>
                    <span class="text-[10px] text-slate-500">{{ $stats['total_students'] > 0 ? round(($chartData['total_male'] / $stats['total_students']) * 100, 1) : 0 }}%</span>
                </div>
                <div class="bg-rose-50/60 p-2.5 rounded-2xl border border-rose-100">
                    <span class="text-[11px] font-bold text-rose-600 uppercase">Perempuan</span>
                    <p class="text-lg font-extrabold text-slate-900 mt-0.5">{{ number_format($chartData['total_female']) }}</p>
                    <span class="text-[10px] text-slate-500">{{ $stats['total_students'] > 0 ? round(($chartData['total_female'] / $stats['total_students']) * 100, 1) : 0 }}%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════ GRAFIK KEUANGAN & KOMPOSISI SDM ════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Chart 3: Realisasi vs Tunggakan Tagihan (Column 8) --}}
        <div class="lg:col-span-8 bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        Realisasi Pembayaran vs Tunggakan Per Unit Sekolah
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Status penerimaan dana tagihan siswa di SMP, SMA, dan SMK</p>
                </div>
                <div class="flex items-center gap-3 text-xs font-semibold">
                    <span class="flex items-center gap-1 text-slate-600"><span class="w-3 h-3 rounded-md bg-emerald-500"></span> Terbayar</span>
                    <span class="flex items-center gap-1 text-slate-600"><span class="w-3 h-3 rounded-md bg-rose-400"></span> Tunggakan</span>
                </div>
            </div>

            <div class="relative w-full h-[300px]">
                <canvas id="financialRealizationChart"></canvas>
            </div>
        </div>

        {{-- Chart 4: Rasio SDM Pendidik vs Tendik (Column 4) --}}
        <div class="lg:col-span-4 bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    Komposisi SDM Yayasan
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Rasio Tenaga Pendidik (Guru) vs Kependidikan (Staf)</p>
            </div>

            <div class="relative w-full h-[220px] my-4 flex items-center justify-center">
                <canvas id="sdmCompositionChart"></canvas>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-100 text-center">
                <div class="bg-emerald-50/60 p-2.5 rounded-2xl border border-emerald-100">
                    <span class="text-[11px] font-bold text-emerald-700 uppercase">Tenaga Pendidik</span>
                    <p class="text-lg font-extrabold text-slate-900 mt-0.5">{{ number_format(array_sum($chartData['teachers'])) }}</p>
                    <span class="text-[10px] text-slate-500">Guru Aktif</span>
                </div>
                <div class="bg-amber-50/60 p-2.5 rounded-2xl border border-amber-100">
                    <span class="text-[11px] font-bold text-amber-700 uppercase">Kependidikan</span>
                    <p class="text-lg font-extrabold text-slate-900 mt-0.5">{{ number_format(array_sum($chartData['staff'])) }}</p>
                    <span class="text-[10px] text-slate-500">Staf Administrasi</span>
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
                    Matriks Detail Kinerja Unit Sekolah
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Rincian data per unit sekolah untuk bahan evaluasi dan keputusan kebijakan strategis</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-600 text-xs uppercase font-extrabold border-b border-slate-100">
                        <th class="py-3.5 px-4 rounded-l-2xl">Unit Sekolah</th>
                        <th class="py-3.5 px-4 text-center">Jenjang</th>
                        <th class="py-3.5 px-4 text-center">Siswa (L / P)</th>
                        <th class="py-3.5 px-4 text-center">SDM (Guru / Staf)</th>
                        <th class="py-3.5 px-4 text-right">Total Tagihan</th>
                        <th class="py-3.5 px-4 text-right">Terbayar</th>
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
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-extrabold @if($school['type'] === 'SMP') bg-blue-100 text-blue-700 @elseif($school['type'] === 'SMA') bg-emerald-100 text-emerald-700 @elseif($school['type'] === 'SMK') bg-amber-100 text-amber-700 @else bg-slate-100 text-slate-700 @endif">
                                {{ $school['type'] }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="font-bold text-slate-900">{{ number_format($school['student_count']) }}</span>
                            <span class="block text-[11px] text-slate-400">({{ $school['male_students'] }} L | {{ $school['female_students'] }} P)</span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="font-bold text-slate-900">{{ number_format($school['employee_count']) }}</span>
                            <span class="block text-[11px] text-slate-400">({{ $school['teacher_count'] }} Guru | {{ $school['staff_count'] }} Staf)</span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-slate-700">
                            Rp {{ number_format($school['billed'], 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <span class="font-extrabold text-emerald-600">Rp {{ number_format($school['paid'], 0, ',', '.') }}</span>
                            @if($school['billed'] > 0)
                            <span class="block text-[11px] text-slate-400">({{ round(($school['paid'] / $school['billed']) * 100, 1) }}%)</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-700 px-3 py-1 rounded-xl text-xs font-bold">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ $school['active_days'] }} Hari
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-violet-50/70 font-extrabold text-violet-900">
                        <td class="py-3.5 px-4 rounded-l-2xl">TOTAL PEMBDA NIAS</td>
                        <td class="py-3.5 px-4 text-center">{{ $schoolSummaries->count() }} Unit</td>
                        <td class="py-3.5 px-4 text-center">{{ number_format($stats['total_students']) }} Siswa</td>
                        <td class="py-3.5 px-4 text-center">{{ number_format($stats['total_employees']) }} Pegawai</td>
                        <td class="py-3.5 px-4 text-right">Rp {{ number_format($stats['total_billed'], 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-right text-emerald-700">Rp {{ number_format($stats['total_paid'], 0, ',', '.') }}</td>
                        <td class="py-3.5 px-4 text-center rounded-r-2xl">—</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ════════════════ INFORMASI YAYASAN PROFIL ════════════════ --}}
    @if($yayasan)
    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
        <h3 class="text-base font-extrabold text-slate-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Profil Yayasan Perguruan Pembangunan Daerah Nias
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                <span class="text-xs font-bold text-slate-400 uppercase">Nama Resmi</span>
                <p class="font-extrabold text-slate-900 mt-1">{{ $yayasan->name }}</p>
            </div>
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                <span class="text-xs font-bold text-slate-400 uppercase">Alamat</span>
                <p class="font-extrabold text-slate-900 mt-1">{{ $yayasan->address ?? 'Kota Gunungsitoli' }}</p>
            </div>
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                <span class="text-xs font-bold text-slate-400 uppercase">Kabupaten / Kota</span>
                <p class="font-extrabold text-slate-900 mt-1">{{ $yayasan->city ?? 'Gunungsitoli' }}</p>
            </div>
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                <span class="text-xs font-bold text-slate-400 uppercase">Provinsi</span>
                <p class="font-extrabold text-slate-900 mt-1">{{ $yayasan->province ?? 'Sumatera Utara' }}</p>
            </div>
        </div>
    </div>
    @endif
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
    const totalMale = {{ $chartData['total_male'] }};
    const totalFemale = {{ $chartData['total_female'] }};

    // Chart Font Family & Default Options
    Chart.defaults.font.family = "'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, sans-serif";
    Chart.defaults.color = '#64748b';

    // 1. Chart Populasi Per Sekolah (Grouped Bar Chart)
    const ctxPop = document.getElementById('schoolPopulationChart').getContext('2d');
    new Chart(ctxPop, {
        type: 'bar',
        data: {
            labels: schoolsData,
            datasets: [
                {
                    label: 'Siswa',
                    data: studentsData,
                    backgroundColor: '#4f46e5', // Indigo 600
                    borderRadius: 8,
                    barPercentage: 0.6,
                },
                {
                    label: 'Guru',
                    data: teachersData,
                    backgroundColor: '#10b981', // Emerald 500
                    borderRadius: 8,
                    barPercentage: 0.6,
                },
                {
                    label: 'Staf',
                    data: staffData,
                    backgroundColor: '#f59e0b', // Amber 500
                    borderRadius: 8,
                    barPercentage: 0.6,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 12,
                    cornerRadius: 12,
                    bodyFont: { weight: 'bold' }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { weight: '600', size: 11 } }
                },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { size: 11 } },
                    beginAtZero: true
                }
            }
        }
    });

    // 2. Chart Demografi Gender Siswa (Doughnut Chart)
    const ctxGender = document.getElementById('genderDemographicChart').getContext('2d');
    new Chart(ctxGender, {
        type: 'doughnut',
        data: {
            labels: ['Laki-laki', 'Perempuan'],
            datasets: [{
                data: [totalMale, totalFemale],
                backgroundColor: ['#3b82f6', '#f43f5e'], // Blue & Rose
                borderWidth: 4,
                borderColor: '#ffffff',
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 12,
                    cornerRadius: 12,
                    callbacks: {
                        label: function(context) {
                            const value = context.raw || 0;
                            const total = totalMale + totalFemale;
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return ` ${context.label}: ${value.toLocaleString()} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // 3. Chart Realisasi Keuangan (Stacked Bar Chart)
    const ctxFinance = document.getElementById('financialRealizationChart').getContext('2d');
    new Chart(ctxFinance, {
        type: 'bar',
        data: {
            labels: schoolsData,
            datasets: [
                {
                    label: 'Terbayar (Lunas)',
                    data: paidData,
                    backgroundColor: '#10b981', // Emerald
                    borderRadius: 8,
                    stack: 'Stack 0',
                },
                {
                    label: 'Sisa Tunggakan',
                    data: unpaidData,
                    backgroundColor: '#fb7185', // Rose 400
                    borderRadius: 8,
                    stack: 'Stack 0',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 12,
                    cornerRadius: 12,
                    callbacks: {
                        label: function(context) {
                            const val = context.raw || 0;
                            return ` ${context.dataset.label}: Rp ${val.toLocaleString('id-ID')}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { weight: '600', size: 11 } }
                },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        font: { size: 10 },
                        callback: function(value) {
                            if (value >= 1000000000) return 'Rp ' + (value / 1000000000).toFixed(1) + 'B';
                            if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(0) + 'M';
                            if (value >= 1000) return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                            return 'Rp ' + value;
                        }
                    },
                    beginAtZero: true
                }
            }
        }
    });

    // 4. Chart Komposisi SDM (Doughnut Chart)
    const totalTeachers = teachersData.reduce((a, b) => a + b, 0);
    const totalStaff = staffData.reduce((a, b) => a + b, 0);

    const ctxSdm = document.getElementById('sdmCompositionChart').getContext('2d');
    new Chart(ctxSdm, {
        type: 'doughnut',
        data: {
            labels: ['Tenaga Pendidik (Guru)', 'Tenaga Kependidikan (Staf)'],
            datasets: [{
                data: [totalTeachers, totalStaff],
                backgroundColor: ['#10b981', '#f59e0b'], // Emerald & Amber
                borderWidth: 4,
                borderColor: '#ffffff',
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 12,
                    cornerRadius: 12,
                    callbacks: {
                        label: function(context) {
                            const value = context.raw || 0;
                            const total = totalTeachers + totalStaff;
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return ` ${context.label}: ${value.toLocaleString()} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection
