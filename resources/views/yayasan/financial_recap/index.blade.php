@extends('layouts.yayasan')

@section('title', 'Rekapitulasi Keuangan Yayasan')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700;800;900&display=swap" rel="stylesheet">

<style>
    /* 100% SOLID COLORS - NO OPACITY TRANSPARENCY */
    .ui-ux-promax {
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #000000;
    }

    .recap-hero {
        background-color: #090d16;
        border: 2px solid #000000;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
    }

    .recap-card-pro {
        background-color: #ffffff;
        border: 2px solid #000000;
        border-radius: 1.25rem;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
    }

    /* MENCEGAH TEKS NOMINAL TERPISAH DENGAN Rp */
    .num-col { 
        font-variant-numeric: tabular-nums; 
        white-space: nowrap !important;
    }

    .stat-card-pro {
        position: relative;
        overflow: hidden;
        border: 2px solid #000000;
        background-color: #ffffff;
    }
    .stat-card-pro::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
    }
    .stat-card-pro.green::before { background-color: #059669; }
    .stat-card-pro.amber::before { background-color: #d97706; }
    .stat-card-pro.red::before { background-color: #dc2626; }
    .stat-card-pro.violet::before { background-color: #7c3aed; }

    .pro-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        opacity: 1 !important;
    }

    /* Section header rows */
    .section-header-green { background-color: #a7f3d0 !important; }
    .section-header-amber { background-color: #fde68a !important; }
    .section-header-red { background-color: #fca5a5 !important; }
    .section-total-row { background-color: #f1f5f9 !important; }

    /* Collapsible section toggle */
    .section-toggle {
        cursor: pointer;
        user-select: none;
        transition: background-color 0.15s ease;
    }
    .section-toggle:hover {
        filter: brightness(0.95);
    }
    .section-toggle .toggle-icon {
        transition: transform 0.2s ease;
    }
    .section-toggle.collapsed .toggle-icon {
        transform: rotate(-90deg);
    }
</style>
@endpush

@section('content')
<div class="ui-ux-promax space-y-6">

    {{-- HERO HEADER --}}
    <div class="recap-hero rounded-3xl p-6 md:p-8 text-white relative overflow-hidden">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black shadow-lg border-2 border-black shrink-0" style="background-color: #fbbf24 !important; color: #000000 !important;">
                        <i class="fas fa-chart-line text-2xl text-black"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Rekapitulasi Keuangan</h1>
                            <span class="pro-badge border-2 border-black" style="background-color: #fbbf24 !important; color: #000000 !important;">Halaman 3</span>
                        </div>
                        <p class="text-amber-400 text-sm font-black tracking-wide">Konsolidasi Rencana Pendapatan & Belanja Yayasan</p>
                    </div>
                </div>
                <p class="text-white text-xs font-black max-w-xl leading-relaxed">
                    Laporan Konsolidasi Eksekutif Perbandingan Total Rencana Pendapatan SPP (Halaman 1) dan Total Rencana Belanja Perguruan (Halaman 2).
                </p>
            </div>

            {{-- Controls & Period Selector --}}
            <form method="GET" action="{{ route('yayasan.financial_recap.index') }}" class="flex flex-wrap items-center gap-3">
                <div class="flex flex-col">
                    <span class="text-[11px] uppercase tracking-wider font-black text-amber-400 mb-1">Tahun Pelajaran</span>
                    <select name="academic_year_id" onchange="this.form.submit()" class="bg-black text-white border-2 border-amber-400 rounded-xl text-xs px-3.5 py-2.5 font-black focus:ring-2 focus:ring-amber-400 min-w-[170px]">
                        @foreach($allYears as $y)
                            <option value="{{ $y->id }}" {{ ($currentYear->id ?? '') == $y->id ? 'selected' : '' }} class="bg-black text-white font-black">
                                TP {{ $y->year }} {{ $y->is_active ? '✦ Aktif' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col">
                    <span class="text-[11px] uppercase tracking-wider font-black text-amber-400 mb-1">Mode Periode</span>
                    <div class="bg-black p-1 rounded-xl border-2 border-slate-700 flex items-center gap-1">
                        <a href="{{ route('yayasan.financial_recap.index', ['academic_year_id' => $currentYear->id, 'period_mode' => 'annual']) }}"
                           class="px-4 py-2 rounded-lg text-xs font-black transition-all" style="{{ $periodMode === 'annual' ? 'background-color: #fbbf24 !important; color: #000000 !important; border: 2px solid #000000;' : 'color: #ffffff;' }}">
                            <i class="fas fa-calendar-days mr-1.5"></i> 12 Bulan
                        </a>
                        <a href="{{ route('yayasan.financial_recap.index', ['academic_year_id' => $currentYear->id, 'period_mode' => 'monthly']) }}"
                           class="px-4 py-2 rounded-lg text-xs font-black transition-all" style="{{ $periodMode === 'monthly' ? 'background-color: #fbbf24 !important; color: #000000 !important; border: 2px solid #000000;' : 'color: #ffffff;' }}">
                            <i class="fas fa-calendar-day mr-1.5"></i> 1 Bulan
                        </a>
                    </div>
                </div>

                <div class="flex flex-col justify-end pt-5">
                    <a href="{{ route('yayasan.financial_recap.export_pdf', ['academic_year_id' => $currentYear->id, 'period_mode' => $periodMode]) }}" target="_blank"
                       class="px-4 py-2.5 text-white font-black text-xs rounded-xl shadow-lg transition flex items-center gap-2 border-2 border-black" style="background-color: #059669 !important;">
                        <i class="fas fa-file-pdf text-sm text-white"></i> Export PDF Laporan
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- EXECUTIVE KPI CARDS (4 CARDS) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        {{-- Card 1: Total Pendapatan SPP --}}
        <div class="stat-card-pro green recap-card-pro rounded-2xl p-5 shadow-md">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black border-2 border-black shrink-0" style="background-color: #059669 !important; color: #ffffff !important;">
                    <i class="fas fa-hand-holding-dollar text-xl text-white"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] text-black font-black uppercase tracking-wider">A. Total Pendapatan SPP</p>
                    <p class="text-xl font-black text-black mt-0.5 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($grandTotalIncome, 0, ',', '.') }}</p>
                    <p class="text-[10px] font-black text-black mt-0.5 num-col whitespace-nowrap">
                        1 Bln: <span class="text-emerald-800 font-black">Rp&nbsp;{{ number_format($grandTotalIncomeMonthly, 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Card 2: Total Belanja Pegawai --}}
        <div class="stat-card-pro amber recap-card-pro rounded-2xl p-5 shadow-md">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black border-2 border-black shrink-0" style="background-color: #d97706 !important; color: #ffffff !important;">
                    <i class="fas fa-users-gear text-xl text-white"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] text-black font-black uppercase tracking-wider">B. Belanja Pegawai</p>
                    <p class="text-xl font-black text-black mt-0.5 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($totalGajiLembagaPeriod, 0, ',', '.') }}</p>
                    <p class="text-[10px] font-black text-black mt-0.5 num-col whitespace-nowrap">
                        1 Bln: <span class="text-amber-900 font-black">Rp&nbsp;{{ number_format($totalGajiLembagaMonthly, 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Card 3: Total Belanja Operasional --}}
        <div class="stat-card-pro red recap-card-pro rounded-2xl p-5 shadow-md">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black border-2 border-black shrink-0" style="background-color: #dc2626 !important; color: #ffffff !important;">
                    <i class="fas fa-list-check text-xl text-white"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] text-black font-black uppercase tracking-wider">C. Belanja Operasional</p>
                    <p class="text-xl font-black text-black mt-0.5 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($totalBelanjaOpsPeriod, 0, ',', '.') }}</p>
                    <p class="text-[10px] font-black text-black mt-0.5 num-col whitespace-nowrap">
                        1 Bln: <span class="text-red-900 font-black">Rp&nbsp;{{ number_format($totalBelanjaOpsMonthly, 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Card 4: Saldo Bersih Akhir --}}
        <div class="stat-card-pro violet recap-card-pro rounded-2xl p-5 shadow-md border-2 border-black" style="background-color: {{ $grandTotalSaldoAkhir >= 0 ? '#dcfce7' : '#fee2e2' }} !important;">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black border-2 border-black shrink-0" style="background-color: {{ $grandTotalSaldoAkhir >= 0 ? '#059669' : '#dc2626' }} !important; color: #ffffff !important;">
                    <i class="fas {{ $grandTotalSaldoAkhir >= 0 ? 'fa-chart-line' : 'fa-triangle-exclamation' }} text-xl text-white"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] text-black font-black uppercase tracking-wider">D. Saldo Bersih</p>
                    <p class="text-xl font-black mt-0.5 num-col whitespace-nowrap text-black">
                        {{ $grandTotalSaldoAkhir >= 0 ? '+' : '' }}Rp&nbsp;{{ number_format($grandTotalSaldoAkhir, 0, ',', '.') }}
                    </p>
                    <p class="text-[10px] font-black text-black mt-0.5 num-col whitespace-nowrap">
                        1 Bln: <span class="font-black" style="color: {{ $grandTotalSaldoAkhirMonthly >= 0 ? '#059669' : '#dc2626' }}">{{ $grandTotalSaldoAkhirMonthly >= 0 ? '+' : '' }}Rp&nbsp;{{ number_format($grandTotalSaldoAkhirMonthly, 0, ',', '.') }}</span>
                    </p>
                    <span class="pro-badge border-2 border-black mt-1" style="background-color: {{ $grandTotalSaldoAkhir >= 0 ? '#059669' : '#dc2626' }} !important; color: #ffffff !important; font-size: 9px; padding: 2px 8px;">
                        <i class="fas {{ $grandTotalSaldoAkhir >= 0 ? 'fa-check' : 'fa-exclamation-triangle' }} text-[8px] text-white"></i>
                        {{ $grandTotalSaldoAkhir >= 0 ? 'SURPLUS' : 'DEFISIT' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- MATRIKS KONSOLIDASI FINANSIAL TABLE --}}
    <div class="recap-card-pro rounded-3xl shadow-2xl overflow-hidden border-2 border-black">
        {{-- Table Toolbar Header --}}
        <div class="recap-hero p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b-2 border-black">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shadow-md border-2 border-black shrink-0" style="background-color: #fbbf24 !important; color: #000000 !important;">
                    <i class="fas fa-file-contract text-xl text-black"></i>
                </div>
                <div>
                    <h2 class="text-lg font-black text-white">Matriks Konsolidasi Pendapatan & Belanja Perguruan</h2>
                    <p class="text-amber-400 text-xs font-black">Ringkasan Konsolidasi 1 Bulan & Periode Total (TP {{ $currentYear->year ?? '-' }})</p>
                </div>
            </div>
            <span class="pro-badge border-2 border-black" style="background-color: {{ $grandTotalSaldoAkhir >= 0 ? '#059669' : '#dc2626' }} !important; color: #ffffff !important;">
                <i class="fas {{ $grandTotalSaldoAkhir >= 0 ? 'fa-shield-check' : 'fa-triangle-exclamation' }} text-[10px] text-white"></i>
                {{ $grandTotalSaldoAkhir >= 0 ? 'SURPLUS' : 'DEFISIT' }}
            </span>
        </div>

        {{-- Table Content --}}
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-black text-white border-b-2 border-black">
                        <th class="px-4 py-4 text-xs uppercase font-black text-white">Komponen Konsolidasi Keuangan</th>
                        <th class="px-4 py-4 text-right w-56 text-xs uppercase font-black text-white whitespace-nowrap">Nominal 1 Bulan (Rp)</th>
                        <th class="px-4 py-4 text-right w-64 text-xs uppercase font-black text-white whitespace-nowrap">Nominal Total Periode ({{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }}) (Rp)</th>
                    </tr>
                </thead>
                <tbody class="bg-white">

                    {{-- ============================================= --}}
                    {{-- SEKSI A: PENDAPATAN SPP MASING-MASING UNIT --}}
                    {{-- ============================================= --}}
                    <tr class="section-toggle section-header-green font-black border-b-2 border-black" onclick="toggleSection('income')">
                        <td class="px-4 py-4 font-black text-black">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-chevron-down toggle-icon text-xs text-emerald-800"></i>
                                <i class="fas fa-hand-holding-dollar text-emerald-800 text-base"></i>
                                <span>A. PENDAPATAN MASING-MASING UNIT SEKOLAH</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-right font-black text-black num-col whitespace-nowrap">Rp&nbsp;{{ number_format($grandTotalIncomeMonthly, 0, ',', '.') }}</td>
                        <td class="px-4 py-4 text-right font-black text-black text-sm num-col whitespace-nowrap">Rp&nbsp;{{ number_format($grandTotalIncome, 0, ',', '.') }}</td>
                    </tr>

                    {{-- Rincian Pendapatan SPP Per Unit Sekolah --}}
                    @foreach($schoolSppData as $schData)
                    <tr class="section-row-income bg-white border-b border-slate-200 hover:bg-emerald-50 transition-colors">
                        <td class="px-4 py-3 pl-12 font-bold text-slate-700">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-school text-emerald-600 text-xs"></i>
                                <span>Pendapatan SPP {{ $schData['school']->name }}</span>
                                <span class="pro-badge border border-emerald-300" style="background-color: #ecfdf5 !important; color: #065f46 !important; font-size: 9px; padding: 1px 6px;">
                                    {{ $schData['total_students'] }} Siswa
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-slate-700 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($schData['income_monthly'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-bold text-slate-700 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($schData['income_total'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach

                    {{-- ============================================= --}}
                    {{-- SEKSI B: BELANJA PEGAWAI MASING-MASING UNIT --}}
                    {{-- ============================================= --}}
                    <tr class="section-toggle section-header-amber font-black border-b-2 border-black border-t-2" onclick="toggleSection('salary')">
                        <td class="px-4 py-4 font-black text-black">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-chevron-down toggle-icon text-xs text-amber-800"></i>
                                <i class="fas fa-users-gear text-amber-800 text-base"></i>
                                <span>B. BELANJA PEGAWAI MASING-MASING UNIT SEKOLAH</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-right font-black text-black num-col whitespace-nowrap">Rp&nbsp;{{ number_format($totalGajiLembagaMonthly, 0, ',', '.') }}</td>
                        <td class="px-4 py-4 text-right font-black text-black text-sm num-col whitespace-nowrap">Rp&nbsp;{{ number_format($totalGajiLembagaPeriod, 0, ',', '.') }}</td>
                    </tr>

                    {{-- Rincian Belanja Pegawai Per Unit Sekolah --}}
                    @foreach($schoolSalaryData as $salData)
                    <tr class="section-row-salary bg-white border-b border-slate-200 hover:bg-amber-50 transition-colors">
                        <td class="px-4 py-3 pl-12 font-bold text-slate-700">
                            <div class="flex items-center gap-2">
                                <i class="fas {{ $salData['school']->type === 'yayasan' ? 'fa-building' : 'fa-school' }} text-amber-600 text-xs"></i>
                                <span>Gaji & Tunjangan Pegawai {{ $salData['school']->name }}</span>
                                <span class="pro-badge border border-amber-300" style="background-color: #fffbeb !important; color: #92400e !important; font-size: 9px; padding: 1px 6px;">
                                    {{ $salData['employee_count'] }} Orang
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-slate-700 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($salData['salary_monthly'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-bold text-slate-700 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($salData['salary_total'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach

                    {{-- ============================================= --}}
                    {{-- SEKSI C: BELANJA OPERASIONAL YAYASAN --}}
                    {{-- ============================================= --}}
                    <tr class="section-toggle section-header-red font-black border-b-2 border-black border-t-2" onclick="toggleSection('opex')">
                        <td class="px-4 py-4 font-black text-black">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-chevron-down toggle-icon text-xs text-red-800"></i>
                                <i class="fas fa-list-check text-red-800 text-base"></i>
                                <span>C. BELANJA OPERASIONAL YAYASAN (NON-GAJI)</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-right font-black text-black num-col whitespace-nowrap">Rp&nbsp;{{ number_format($totalBelanjaOpsMonthly, 0, ',', '.') }}</td>
                        <td class="px-4 py-4 text-right font-black text-black text-sm num-col whitespace-nowrap">Rp&nbsp;{{ number_format($totalBelanjaOpsPeriod, 0, ',', '.') }}</td>
                    </tr>

                    {{-- Rincian Belanja Operasional Per Kode Rekening --}}
                    @foreach($operationalExpenseItems as $opItem)
                        @if($opItem['amount_monthly'] > 0)
                        <tr class="section-row-opex bg-white border-b border-slate-200 hover:bg-red-50 transition-colors">
                            <td class="px-4 py-3 pl-12 font-bold text-slate-700">
                                <div class="flex items-center gap-2">
                                    <i class="fas {{ $opItem['icon'] }} text-red-500 text-xs"></i>
                                    <span class="text-slate-500 font-black">{{ $opItem['code'] }}</span>
                                    <span>{{ $opItem['name'] }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-slate-700 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($opItem['amount_monthly'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-bold text-slate-700 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($opItem['amount_total'], 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    @endforeach

                    {{-- Jika tidak ada belanja operasional yang diinput --}}
                    @if(collect($operationalExpenseItems)->sum('amount_monthly') == 0)
                    <tr class="section-row-opex bg-white border-b border-slate-200">
                        <td colspan="3" class="px-4 py-4 pl-12 text-center text-slate-400 italic font-bold">
                            <i class="fas fa-info-circle mr-1"></i> Belum ada rincian belanja operasional yang diinput pada Halaman 2
                        </td>
                    </tr>
                    @endif

                    {{-- ============================================= --}}
                    {{-- TOTAL BELANJA PERGURUAN (B + C) --}}
                    {{-- ============================================= --}}
                    <tr class="font-black border-t-2 border-b-2 border-black" style="background-color: #e2e8f0 !important;">
                        <td class="px-4 py-4 font-black text-black">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-calculator text-slate-800 text-base"></i>
                                <span>TOTAL BELANJA PERGURUAN (B + C)</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-right font-black text-black num-col whitespace-nowrap">(Rp&nbsp;{{ number_format($grandTotalBelanjaMonthly, 0, ',', '.') }})</td>
                        <td class="px-4 py-4 text-right font-black text-black text-sm num-col whitespace-nowrap">(Rp&nbsp;{{ number_format($grandTotalBelanjaPeriod, 0, ',', '.') }})</td>
                    </tr>

                </tbody>
                <tfoot>
                    {{-- ============================================= --}}
                    {{-- SEKSI D: SALDO BERSIH AKHIR --}}
                    {{-- ============================================= --}}
                    <tr class="bg-black text-white font-black border-t-4 border-black text-sm">
                        <td class="px-6 py-5 font-black">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center border-2 border-amber-400 shrink-0" style="background-color: {{ $grandTotalSaldoAkhir >= 0 ? '#059669' : '#dc2626' }} !important;">
                                    <i class="fas {{ $grandTotalSaldoAkhir >= 0 ? 'fa-chart-line' : 'fa-triangle-exclamation' }} text-lg text-white"></i>
                                </div>
                                <div>
                                    <span class="text-amber-400 uppercase tracking-widest text-xs">D. SALDO BERSIH AKHIR PERGURUAN</span>
                                    <p class="text-slate-400 text-[10px] font-bold mt-0.5">Pendapatan (A) − Belanja Pegawai (B) − Belanja Operasional (C)</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-5 text-right num-col whitespace-nowrap">
                            <span class="font-black text-lg whitespace-nowrap" style="color: {{ $grandTotalSaldoAkhirMonthly >= 0 ? '#34d399' : '#f87171' }} !important;">
                                {{ $grandTotalSaldoAkhirMonthly >= 0 ? '+' : '' }}Rp&nbsp;{{ number_format($grandTotalSaldoAkhirMonthly, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-4 py-5 text-right num-col whitespace-nowrap">
                            <span class="font-black text-2xl whitespace-nowrap" style="color: {{ $grandTotalSaldoAkhir >= 0 ? '#34d399' : '#f87171' }} !important;">
                                {{ $grandTotalSaldoAkhir >= 0 ? '+' : '' }}Rp&nbsp;{{ number_format($grandTotalSaldoAkhir, 0, ',', '.') }}
                            </span>
                            <div class="mt-1">
                                <span class="pro-badge border border-white" style="background-color: {{ $grandTotalSaldoAkhir >= 0 ? '#059669' : '#dc2626' }} !important; color: #ffffff !important; font-size: 9px;">
                                    <i class="fas {{ $grandTotalSaldoAkhir >= 0 ? 'fa-check-circle' : 'fa-exclamation-circle' }} text-[8px]"></i>
                                    {{ $grandTotalSaldoAkhir >= 0 ? 'SURPLUS PERGURUAN' : 'DEFISIT PERGURUAN' }}
                                </span>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function toggleSection(sectionName) {
        const rows = document.querySelectorAll('.section-row-' + sectionName);
        const header = event.currentTarget;
        const isCollapsed = header.classList.toggle('collapsed');

        rows.forEach(row => {
            row.style.display = isCollapsed ? 'none' : '';
        });
    }
</script>
@endpush
