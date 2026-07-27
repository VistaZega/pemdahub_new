@extends('layouts.yayasan')

@section('title', 'Rekapitulasi Keuangan Yayasan')

@section('content')
<div class="space-y-6">

    <!-- Header & Filter Bar -->
    <div class="bg-gradient-to-r from-violet-900 via-purple-900 to-indigo-900 rounded-2xl p-6 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black flex items-center gap-2.5">
                <i class="fas fa-chart-line text-amber-400"></i> Rekapitulasi Pendapatan & Belanja Yayasan
            </h1>
            <p class="text-xs text-violet-200 mt-1">
                Laporan konsolidasi eksekutif seluruh pendapatan SPP unit sekolah, belanja pegawai, dan rencana belanja operasional terpusat.
            </p>
        </div>

        <form method="GET" action="{{ route('yayasan.financial_recap.index') }}" class="flex flex-wrap items-center gap-3">
            <div>
                <select name="academic_year_id" onchange="this.form.submit()" class="bg-white/10 text-white border border-white/20 rounded-xl text-xs px-3 py-2 font-bold backdrop-blur-md focus:ring-2 focus:ring-amber-400">
                    @foreach($allYears as $y)
                        <option value="{{ $y->id }}" {{ $currentYear->id == $y->id ? 'selected' : '' }} class="text-gray-900">
                            TP {{ $y->year }} {{ $y->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="bg-white/10 p-1 rounded-xl border border-white/20 backdrop-blur-md flex items-center gap-1">
                <a href="{{ route('yayasan.financial_recap.index', ['academic_year_id' => $currentYear->id, 'period_mode' => 'annual']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $periodMode === 'annual' ? 'bg-amber-400 text-amber-950 shadow' : 'text-white hover:bg-white/10' }}">
                    12 Bulan
                </a>
                <a href="{{ route('yayasan.financial_recap.index', ['academic_year_id' => $currentYear->id, 'period_mode' => 'monthly']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $periodMode === 'monthly' ? 'bg-amber-400 text-amber-950 shadow' : 'text-white hover:bg-white/10' }}">
                    1 Bulan
                </a>
            </div>

            <a href="{{ route('yayasan.financial_recap.export_pdf', ['academic_year_id' => $currentYear->id, 'period_mode' => $periodMode]) }}" 
               target="_blank"
               class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                <i class="fas fa-file-pdf"></i> Export PDF Laporan
            </a>
        </form>
    </div>

    <!-- 4 Executive KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Total SPP -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-extrabold text-emerald-700 uppercase tracking-wider">Total Pendapatan SPP</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <i class="fas fa-hand-holding-dollar text-sm"></i>
                </div>
            </div>
            <span class="text-2xl font-black text-emerald-900 block">Rp {{ number_format($grandTotalIncome, 0, ',', '.') }}</span>
            <p class="text-[11px] text-gray-500 mt-1">Akumulasi SPP Seluruh Unit Sekolah</p>
        </div>

        <!-- Card 2: Total Belanja Pegawai -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-extrabold text-blue-700 uppercase tracking-wider">Total Belanja Pegawai</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    <i class="fas fa-users-gear text-sm"></i>
                </div>
            </div>
            <span class="text-2xl font-black text-blue-900 block">Rp {{ number_format($grandTotalGajiLembaga, 0, ',', '.') }}</span>
            <p class="text-[11px] text-gray-500 mt-1">Gaji Guru Sekolah + Staf Yayasan</p>
        </div>

        <!-- Card 3: Total Belanja Ops Yayasan -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-extrabold text-amber-700 uppercase tracking-wider">Belanja Ops Yayasan</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                    <i class="fas fa-list-check text-sm"></i>
                </div>
            </div>
            <span class="text-2xl font-black text-amber-800 block">Rp {{ number_format($totalBelanjaOpsYayasan, 0, ',', '.') }}</span>
            <p class="text-[11px] text-gray-500 mt-1">Ditarik dari Rencana Belanja (Halaman 2)</p>
        </div>

        <!-- Card 4: Saldo Akhir Bersih -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-extrabold text-violet-700 uppercase tracking-wider">Saldo Bersih Akhir</span>
                <div class="w-9 h-9 rounded-xl {{ $grandTotalSaldoAkhir >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }} flex items-center justify-center font-bold">
                    <i class="fas {{ $grandTotalSaldoAkhir >= 0 ? 'fa-chart-line-up' : 'fa-chart-line-down' }} text-sm"></i>
                </div>
            </div>
            <span class="text-2xl font-black {{ $grandTotalSaldoAkhir >= 0 ? 'text-emerald-700' : 'text-red-700' }} block">
                {{ $grandTotalSaldoAkhir >= 0 ? '+' : '' }}Rp {{ number_format($grandTotalSaldoAkhir, 0, ',', '.') }}
            </span>
            <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $grandTotalSaldoAkhir >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                {{ $grandTotalSaldoAkhir >= 0 ? 'SURPLUS PERGURUAN' : 'DEFISIT PERGURUAN' }}
            </span>
        </div>
    </div>

    <!-- SEKSI 1: RINGKASAN KONTRIBUSI UNIT SEKOLAH -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-md overflow-hidden">
        <div class="p-5 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-extrabold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-school text-emerald-600"></i> I. Kontribusi Pendapatan SPP & Belanja Gaji Unit Sekolah
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Ringkasan pendapatan SPP dan belanja pegawai tiap unit pendidikan.</p>
            </div>
            <a href="{{ route('yayasan.contribution_balance.index') }}" class="text-xs font-bold text-violet-700 hover:text-violet-900 flex items-center gap-1">
                Buka Halaman Kontribusi Unit <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-100 text-gray-700 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">No</th>
                        <th class="px-4 py-3">Nama Unit Sekolah</th>
                        <th class="px-4 py-3 text-center">Jumlah Siswa</th>
                        <th class="px-4 py-3 text-right">Pendapatan SPP</th>
                        <th class="px-4 py-3 text-right">Gaji Guru & Pegawai</th>
                        <th class="px-4 py-3 text-right">Surplus Kontribusi Sekolah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($schoolData as $idx => $row)
                        <tr class="hover:bg-violet-50/30 transition">
                            <td class="px-4 py-3 text-center font-bold text-gray-400">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3 font-bold text-gray-900 flex items-center gap-2">
                                {{ $row['school']->name }}
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600">
                                    {{ $row['school']->type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-gray-700">{{ $row['total_students'] }} siswa</td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-700">Rp {{ number_format($row['income_total'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-bold text-blue-700">Rp {{ number_format($row['salary_total'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-extrabold {{ $row['surplus_kontribusi'] >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                                {{ $row['surplus_kontribusi'] >= 0 ? '+' : '' }}Rp {{ number_format($row['surplus_kontribusi'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-100 font-bold border-t border-gray-200 text-gray-900">
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-right font-extrabold">SUBTOTAL UNIT SEKOLAH:</td>
                        <td class="px-4 py-3 text-right text-emerald-700">Rp {{ number_format($grandTotalIncome, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-blue-700">Rp {{ number_format($grandTotalGajiSekolah, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-black text-emerald-800">
                            Rp {{ number_format($grandTotalIncome - $grandTotalGajiSekolah, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- SEKSI 2: PENGELUARAN TERPUSAT YAYASAN -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-md overflow-hidden">
        <div class="p-5 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-extrabold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-building text-violet-600"></i> II. Pengeluaran Terpusat Yayasan (Gaji Staf & Belanja Operasional)
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Komponen beban gaji staf yayasan dan belanja operasional non-gaji terpusat.</p>
            </div>
            <a href="{{ route('yayasan.operational_expenses.index') }}" class="text-xs font-bold text-violet-700 hover:text-violet-900 flex items-center gap-1">
                Kelola Rencana Belanja Operasional <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Komponen 1: Gaji Staf Yayasan -->
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-lg">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900">Gaji Staf & Pengurus Yayasan</h4>
                        <p class="text-xs text-gray-500">{{ $yayasanEmployeeCount }} pegawai aktif (Payroll Yayasan)</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-sm font-black text-blue-900 block">Rp {{ number_format($totalYayasanSalary, 0, ',', '.') }}</span>
                    <span class="text-[11px] text-gray-400">({{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }})</span>
                </div>
            </div>

            <!-- Komponen 2: Belanja Operasional Terpusat -->
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-lg">
                        <i class="fas fa-list-check"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900">Rencana Belanja Operasional Yayasan</h4>
                        <p class="text-xs text-gray-500">Internet, Listrik, Air, Sarpras, ATK, Dinas, dll</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-sm font-black text-amber-800 block">Rp {{ number_format($totalBelanjaOpsYayasan, 0, ',', '.') }}</span>
                    <span class="text-[11px] text-gray-400">Ditarik dari Halaman 2</span>
                </div>
            </div>
        </div>

        <!-- Breakdown Kode Rekening Terpusat -->
        @if(!empty($savedExpenseDetails) && count($savedExpenseDetails) > 0)
            <div class="px-6 pb-6">
                <p class="text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-2">Rincian Per Kode Rekening Belanja Operasional Yayasan:</p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 text-xs">
                    @foreach($savedExpenseDetails as $accCode => $accAmount)
                        @php
                            $accInfo = $expenseAccounts[$accCode] ?? null;
                            $accPeriod = ((float)$accAmount) * $multiplier;
                        @endphp
                        @if((float)$accAmount > 0)
                            <div class="p-2 bg-gray-50 rounded-lg border border-gray-200 flex items-center justify-between">
                                <span class="truncate max-w-[200px]" title="{{ $accInfo['name'] ?? $accCode }}">
                                    <strong class="font-mono text-violet-700">{{ $accCode }}</strong> — {{ $accInfo['name'] ?? 'Belanja' }}
                                </span>
                                <span class="font-bold text-gray-900">Rp {{ number_format($accPeriod, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- SEKSI 3: REKAPITULASI KONSOLIDASI SELURUH LEMBAGA -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-md overflow-hidden">
        <div class="p-6 bg-gradient-to-r from-violet-900 to-indigo-900 text-white flex items-center justify-between">
            <div>
                <h3 class="text-base font-extrabold flex items-center gap-2">
                    <i class="fas fa-calculator text-amber-400"></i> III. Rekapitulasi Akhir Pendapatan & Belanja Yayasan
                </h3>
                <p class="text-xs text-violet-200 mt-0.5">Perhitungan saldo bersih konsolidasi akhir seluruh perguruan.</p>
            </div>
            <span class="px-3 py-1.5 rounded-full text-xs font-black uppercase tracking-wider {{ $grandTotalSaldoAkhir >= 0 ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white' }}">
                {{ $grandTotalSaldoAkhir >= 0 ? 'SURPLUS' : 'DEFISIT' }}
            </span>
        </div>

        <div class="p-6 overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-100 text-gray-700 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Komponen Keuangan Konsolidasi</th>
                        <th class="px-4 py-3 text-right">Nominal Periode ({{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }})</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        <td class="px-4 py-3 font-bold text-emerald-800">1. Total Pendapatan SPP (Seluruh Sekolah)</td>
                        <td class="px-4 py-3 text-right font-extrabold text-emerald-700 text-sm">Rp {{ number_format($grandTotalIncome, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 font-bold text-blue-800">2. Total Belanja Pegawai (Sekolah + Yayasan)</td>
                        <td class="px-4 py-3 text-right font-extrabold text-blue-700 text-sm">(Rp {{ number_format($grandTotalGajiLembaga, 0, ',', '.') }})</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 font-bold text-amber-800">3. Total Belanja Operasional Terpusat Yayasan</td>
                        <td class="px-4 py-3 text-right font-extrabold text-amber-700 text-sm">(Rp {{ number_format($totalBelanjaOpsYayasan, 0, ',', '.') }})</td>
                    </tr>
                    <tr class="bg-red-50/60 font-bold">
                        <td class="px-4 py-3 font-extrabold text-red-900">4. TOTAL PENGELUARAN LEMBAGA (2 + 3)</td>
                        <td class="px-4 py-3 text-right font-black text-red-700 text-sm">(Rp {{ number_format($grandTotalPengeluaran, 0, ',', '.') }})</td>
                    </tr>
                </tbody>
                <tfoot class="bg-violet-900 text-white font-extrabold border-t-2 border-violet-900 text-sm">
                    <tr>
                        <td class="px-4 py-4 uppercase tracking-wider">SALDO BERSIH AKHIR YAYASAN (1 - 4):</td>
                        <td class="px-4 py-4 text-right font-black text-base {{ $grandTotalSaldoAkhir >= 0 ? 'text-emerald-300' : 'text-red-300' }}">
                            {{ $grandTotalSaldoAkhir >= 0 ? '+' : '' }}Rp {{ number_format($grandTotalSaldoAkhir, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection
