@extends('layouts.yayasan')

@section('title', 'Realisasi Anggaran Belanja Yayasan')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-emerald-800 via-teal-900 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-xs font-medium text-emerald-200 mb-3">
                    <i class="fas fa-[#fa-receipt] text-amber-300"></i> Laporan Realisasi Anggaran Keuangan Aktual
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Realisasi Anggaran Belanja Yayasan</h1>
                <p class="text-emerald-200 text-sm mt-1 max-w-2xl">
                    Pencapaian Realisasi Penerimaan SPP Siswa, Realisasi Pencairan Gaji Pegawai, dan 8 Item Belanja Operasional Aktual.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('yayasan.realisasi.export_pdf', ['academic_year_id' => $activeYear->id ?? '', 'month' => $month, 'year' => $year, 'period_mode' => $periodMode]) }}" 
                   target="_blank"
                   class="inline-flex items-center gap-2 bg-rose-500 hover:bg-rose-600 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-lg transition transform hover:-translate-y-0.5">
                    <i class="fas fa-file-pdf"></i> Export PDF Realisasi
                </a>
            </div>
        </div>
    </div>

    <!-- Filter & Mode Switcher -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('yayasan.realisasi.index') }}" class="flex flex-wrap items-center gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Tahun Pelajaran</label>
                <select name="academic_year_id" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    @foreach($academicYears as $y)
                        <option value="{{ $y->id }}" {{ ($activeYear->id ?? null) == $y->id ? 'selected' : '' }}>
                            TP {{ $y->year }} {{ $y->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Mode Periode</label>
                <div class="inline-flex rounded-xl bg-gray-100 p-1 border border-gray-200">
                    <a href="{{ route('yayasan.realisasi.index', ['academic_year_id' => $activeYear->id ?? '', 'period_mode' => 'monthly', 'month' => $month, 'year' => $year]) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $periodMode === 'monthly' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        Bulanan
                    </a>
                    <a href="{{ route('yayasan.realisasi.index', ['academic_year_id' => $activeYear->id ?? '', 'period_mode' => 'annual', 'month' => $month, 'year' => $year]) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $periodMode === 'annual' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        Tahunan (12 Bulan)
                    </a>
                </div>
            </div>

            @if($periodMode === 'monthly')
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Bulan & Tahun</label>
                    <div class="flex items-center gap-1">
                        <select name="month" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-2 text-xs font-semibold text-gray-700">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                        <select name="year" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-2 text-xs font-semibold text-gray-700">
                            @foreach(range(date('Y') - 2, date('Y') + 1) as $yr)
                                <option value="{{ $yr }}" {{ $year == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif
        </form>

        <div class="text-xs text-gray-500 font-medium flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Status Realisasi: <strong class="text-gray-800">{{ $periodMode === 'monthly' ? "Bulan " . DateTime::createFromFormat('!m', $month)->format('F') . " $year" : "Tahunan (12 Bulan)" }}</strong>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- Total Realisasi Pendapatan -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 relative overflow-hidden group hover:border-emerald-200 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Realisasi Penerimaan SPP</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl sm:text-2xl font-black text-emerald-700 tracking-tight">
                    Rp {{ number_format($summary['total_income'], 0, ',', '.') }}
                </div>
                <div class="text-xs text-gray-400 font-medium mt-1">Penerimaan SPP Lunas</div>
            </div>
        </div>

        <!-- Total Realisasi Belanja Gaji -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 relative overflow-hidden group hover:border-indigo-200 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Realisasi Gaji & Honor</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-money-check-dollar"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight">
                    Rp {{ number_format($summary['total_salary'], 0, ',', '.') }}
                </div>
                <div class="text-xs text-gray-400 font-medium mt-1">Honor & Tunjangan Jabatan</div>
            </div>
        </div>

        <!-- Total Realisasi Operasional -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 relative overflow-hidden group hover:border-amber-200 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Realisasi Operasional (8 Item)</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-boxes-packing"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight">
                    Rp {{ number_format($summary['total_operational'], 0, ',', '.') }}
                </div>
                <div class="text-xs text-amber-600 font-medium mt-1">Pengeluaran Aktual Unit</div>
            </div>
        </div>

        <!-- Saldo Realisasi -->
        <div class="bg-gradient-to-br {{ $summary['total_balance'] >= 0 ? 'from-emerald-900 to-teal-950' : 'from-rose-900 to-red-950' }} text-white rounded-2xl p-5 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-white/70 uppercase tracking-wider">Saldo Realisasi</span>
                <div class="w-9 h-9 rounded-xl bg-white/10 text-white flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-scale-balanced"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl sm:text-2xl font-black tracking-tight">
                    Rp {{ number_format($summary['total_balance'], 0, ',', '.') }}
                </div>
                <div class="text-xs text-white/70 font-medium mt-1">
                    {{ $summary['total_balance'] >= 0 ? 'Surplus Realisasi Aktual' : 'Defisit Realisasi Aktual' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Realisasi Anggaran Belanja Per Unit Sekolah -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50">
            <div>
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-receipt text-emerald-600"></i> Matriks Realisasi Anggaran Per Unit Sekolah & Yayasan
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Realisasi penerimaan SPP aktual, pencairan gaji, dan 8 item belanja operasional aktual</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-gray-100/70 text-gray-700 font-bold uppercase tracking-wider border-b border-gray-200">
                        <th class="py-3.5 px-4 min-w-[180px]">Unit Sekolah / Lembaga</th>
                        <th class="py-3.5 px-4 text-center">Siswa Aktif</th>
                        <th class="py-3.5 px-4 text-right bg-emerald-50/50 text-emerald-900">A. Realisasi SPP</th>
                        <th class="py-3.5 px-4 text-right">1. Honor & Tunjangan</th>
                        <th class="py-3.5 px-4 text-right">2. Operasional Unit</th>
                        <th class="py-3.5 px-4 text-right bg-rose-50/50 text-rose-900">B. Total Realisasi Belanja</th>
                        <th class="py-3.5 px-4 text-right bg-emerald-50/50 text-emerald-900">C. Saldo Realisasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($schoolRealisasiList as $item)
                        <tr class="hover:bg-emerald-50/30 transition">
                            <td class="py-4 px-4 font-bold text-gray-900">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-black text-xs shadow-sm">
                                        {{ strtoupper(substr($item['school']->type ?? $item['school']->name, 0, 3)) }}
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-gray-900">{{ $item['school']->name }}</div>
                                        <div class="text-[10px] text-gray-400 font-normal">Jenjang: {{ strtoupper($item['school']->type ?? '-') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-center font-semibold text-gray-700">
                                {{ number_format($item['student_count'], 0, ',', '.') }} orang
                            </td>
                            <td class="py-4 px-4 text-right font-bold text-emerald-700 bg-emerald-50/30">
                                Rp {{ number_format($item['real_income_period'], 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-right font-semibold text-gray-700">
                                Rp {{ number_format($item['real_salary_period'], 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-right font-semibold text-amber-700">
                                Rp {{ number_format($item['real_operational_period'], 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-right font-bold text-rose-700 bg-rose-50/30">
                                Rp {{ number_format($item['real_total_expense_period'], 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-right font-black {{ $item['real_balance_period'] >= 0 ? 'text-emerald-800 bg-emerald-50/40' : 'text-rose-800 bg-rose-100/50' }}">
                                Rp {{ number_format($item['real_balance_period'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gradient-to-r from-emerald-950 to-teal-900 text-white font-black text-xs border-t-2 border-emerald-950">
                        <td class="py-4 px-4">TOTAL REALISASI KONSOLIDASI YAYASAN</td>
                        <td class="py-4 px-4 text-center">{{ number_format($summary['total_students'], 0, ',', '.') }}</td>
                        <td class="py-4 px-4 text-right text-emerald-300">Rp {{ number_format($summary['total_income'], 0, ',', '.') }}</td>
                        <td class="py-4 px-4 text-right text-indigo-200">Rp {{ number_format($summary['total_salary'], 0, ',', '.') }}</td>
                        <td class="py-4 px-4 text-right text-amber-300">Rp {{ number_format($summary['total_operational'], 0, ',', '.') }}</td>
                        <td class="py-4 px-4 text-right text-rose-300">Rp {{ number_format($summary['total_expense'], 0, ',', '.') }}</td>
                        <td class="py-4 px-4 text-right text-emerald-200 text-sm">Rp {{ number_format($summary['total_balance'], 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Breakdown 8 Item Operasional Aktual -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
        <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fas fa-list-check text-amber-500"></i> Breakdown Realisasi 8 Item Belanja Operasional Per Unit Sekolah
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($operationalItems as $itemKey => $itemInfo)
                <div class="bg-gray-50 rounded-2xl p-4 border border-gray-200 hover:border-amber-300 transition">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">
                            <i class="fas {{ $itemInfo['icon'] }}"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-800">{{ $itemInfo['name'] }}</div>
                            <div class="text-[10px] text-gray-400">{{ $itemInfo['desc'] }}</div>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1.5 text-xs border-t border-gray-200 pt-2">
                        @foreach($schoolRealisasiList as $sItem)
                            <div class="flex items-center justify-between text-gray-600">
                                <span>{{ $sItem['school']->name }}:</span>
                                <span class="font-bold text-gray-800">
                                    Rp {{ number_format(($sItem['itemised_monthly'][$itemKey] ?? 0) * $multiplier, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
