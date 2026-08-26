@extends('layouts.yayasan')

@section('title', 'Rekapitulasi Keuangan Yayasan')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
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

    /* === TABEL PEMBUKUAN STANDAR === */
    .ledger-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }
    .ledger-table th {
        background-color: #090d16;
        color: #ffffff;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 11px;
        padding: 14px 16px;
        text-align: center;
        border: 2px solid #000;
    }
    .ledger-table th:first-child { text-align: center; width: 55px; }
    .ledger-table th:nth-child(2) { text-align: left; }
    .ledger-table td {
        padding: 10px 16px;
        border: 1px solid #cbd5e1;
        vertical-align: middle;
    }
    .ledger-table td:first-child { text-align: center; font-weight: 800; width: 55px; }
    .ledger-table td:nth-child(n+3) { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }

    /* Baris section header */
    .ledger-section {
        background-color: #f8fafc !important;
        border-top: 2px solid #000 !important;
        border-bottom: 2px solid #000 !important;
    }
    .ledger-section td { font-weight: 900; font-size: 12px; color: #000; }

    /* Baris sub-item (detail) */
    .ledger-detail td { font-weight: 600; color: #334155; }
    .ledger-detail:hover { background-color: #f1f5f9; }

    /* Baris subtotal */
    .ledger-subtotal {
        background-color: #f1f5f9 !important;
        border-top: 2px solid #94a3b8 !important;
        border-bottom: 2px solid #94a3b8 !important;
    }
    .ledger-subtotal td { font-weight: 900; color: #000; }

    /* Baris saldo akhir */
    .ledger-grand-total {
        background-color: #090d16 !important;
        border-top: 4px solid #000 !important;
    }
    .ledger-grand-total td { 
        font-weight: 900; 
        color: #fbbf24; 
        font-size: 13px; 
        padding: 16px;
        border-color: #1e293b;
    }

    /* Kolom kosong */
    .cell-empty { color: #e2e8f0; text-align: center !important; }

    /* Collapsible section toggle */
    .section-toggle { cursor: pointer; user-select: none; }
    .section-toggle:hover { filter: brightness(0.97); }
    .section-toggle .toggle-icon { transition: transform 0.2s ease; font-size: 10px; }
    .section-toggle.collapsed .toggle-icon { transform: rotate(-90deg); }
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
                    Laporan Konsolidasi Eksekutif — Format Standar Pembukuan Keuangan Perguruan.
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
                    <span class="text-[11px] uppercase tracking-wider font-black text-amber-400 mb-1">Metode Pembukuan</span>
                    <select name="view_mode" onchange="this.form.submit()" class="bg-black text-white border-2 border-amber-400 rounded-xl text-xs px-3.5 py-2.5 font-black focus:ring-2 focus:ring-amber-400 min-w-[190px]">
                        <option value="cash" {{ ($viewMode ?? 'cash') === 'cash' ? 'selected' : '' }}>💵 Realisasi Kas (Cash Basis)</option>
                        <option value="accrual" {{ ($viewMode ?? '') === 'accrual' ? 'selected' : '' }}>📊 Proyeksi Potensi (Accrual Basis)</option>
                    </select>
                </div>

                <div class="flex flex-col justify-end pt-5">
                    <a href="{{ route('yayasan.financial_recap.export_pdf', ['academic_year_id' => $currentYear->id, 'period_mode' => $periodMode, 'view_mode' => $viewMode ?? 'cash']) }}" target="_blank"
                       class="px-4 py-2.5 text-white font-black text-xs rounded-xl shadow-lg transition flex items-center gap-2 border-2 border-black" style="background-color: #059669 !important;">
                        <i class="fas fa-file-pdf text-sm text-white"></i> Export PDF
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
                    <p class="text-[10px] text-black font-black uppercase tracking-wider">Total Pendapatan</p>
                    <p class="text-xl font-black text-black mt-0.5 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($grandTotalIncomeMonthly, 0, ',', '.') }}</p>
                    <p class="text-[10px] font-bold text-slate-500 mt-0.5 num-col">/ bulan</p>
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
                    <p class="text-[10px] text-black font-black uppercase tracking-wider">Belanja Pegawai</p>
                    <p class="text-xl font-black text-black mt-0.5 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($totalGajiLembagaMonthly, 0, ',', '.') }}</p>
                    <p class="text-[10px] font-bold text-slate-500 mt-0.5 num-col">/ bulan</p>
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
                    <p class="text-[10px] text-black font-black uppercase tracking-wider">Belanja Operasional</p>
                    <p class="text-xl font-black text-black mt-0.5 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($totalBelanjaOpsMonthly, 0, ',', '.') }}</p>
                    <p class="text-[10px] font-bold text-slate-500 mt-0.5 num-col">/ bulan</p>
                </div>
            </div>
        </div>

        {{-- Card 4: Saldo Bersih --}}
        <div class="stat-card-pro violet recap-card-pro rounded-2xl p-5 shadow-md border-2 border-black" style="background-color: {{ $grandTotalSaldoAkhirMonthly >= 0 ? '#dcfce7' : '#fee2e2' }} !important;">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black border-2 border-black shrink-0" style="background-color: {{ $grandTotalSaldoAkhirMonthly >= 0 ? '#059669' : '#dc2626' }} !important; color: #ffffff !important;">
                    <i class="fas {{ $grandTotalSaldoAkhirMonthly >= 0 ? 'fa-chart-line' : 'fa-triangle-exclamation' }} text-xl text-white"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] text-black font-black uppercase tracking-wider">Saldo Bersih</p>
                    <p class="text-xl font-black mt-0.5 num-col whitespace-nowrap" style="color: {{ $grandTotalSaldoAkhirMonthly >= 0 ? '#059669' : '#dc2626' }}">
                        {{ $grandTotalSaldoAkhirMonthly >= 0 ? '+' : '' }}Rp&nbsp;{{ number_format($grandTotalSaldoAkhirMonthly, 0, ',', '.') }}
                    </p>
                    <span class="pro-badge border-2 border-black mt-1" style="background-color: {{ $grandTotalSaldoAkhirMonthly >= 0 ? '#059669' : '#dc2626' }} !important; color: #ffffff !important; font-size: 9px; padding: 2px 8px;">
                        <i class="fas {{ $grandTotalSaldoAkhirMonthly >= 0 ? 'fa-check' : 'fa-exclamation-triangle' }} text-[8px] text-white"></i>
                        {{ $grandTotalSaldoAkhirMonthly >= 0 ? 'SURPLUS' : 'DEFISIT' }} / Bulan
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================== --}}
    {{-- TABEL PEMBUKUAN STANDAR                                        --}}
    {{-- No | Deskripsi | Pendapatan | Belanja | Saldo/Bulan | Saldo/Tahun --}}
    {{-- ============================================================== --}}
    <div class="recap-card-pro rounded-3xl shadow-2xl overflow-hidden border-2 border-black">
        {{-- Table Toolbar Header --}}
        <div class="recap-hero p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b-2 border-black">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shadow-md border-2 border-black shrink-0" style="background-color: #fbbf24 !important; color: #000000 !important;">
                    <i class="fas fa-book-open text-xl text-black"></i>
                </div>
                <div>
                    <h2 class="text-lg font-black text-white">Pembukuan Keuangan Perguruan</h2>
                    <p class="text-amber-400 text-xs font-black">TP {{ $currentYear->year ?? '-' }} — Format Standar Pembukuan</p>
                </div>
            </div>
            <span class="pro-badge border-2 border-black" style="background-color: {{ $grandTotalSaldoAkhir >= 0 ? '#059669' : '#dc2626' }} !important; color: #ffffff !important;">
                <i class="fas {{ $grandTotalSaldoAkhir >= 0 ? 'fa-shield-check' : 'fa-triangle-exclamation' }} text-[10px] text-white"></i>
                {{ $grandTotalSaldoAkhir >= 0 ? 'SURPLUS' : 'DEFISIT' }}
            </span>
        </div>

        {{-- TABEL PEMBUKUAN --}}
        <div class="overflow-x-auto">
            <table class="ledger-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Deskripsi</th>
                        <th style="width: 170px;">Pendapatan (Rp)</th>
                        <th style="width: 170px;">Belanja (Rp)</th>
                        <th style="width: 170px;">Saldo / Bulan (Rp)</th>
                        <th style="width: 170px;">Saldo / Tahun (Rp)</th>
                    </tr>
                </thead>
                <tbody>

                    {{-- ============================== --}}
                    {{-- SEKSI A: PENDAPATAN SPP        --}}
                    {{-- ============================== --}}
                    <tr class="ledger-section section-toggle" onclick="toggleSection('income')" style="background-color: #ecfdf5 !important;">
                        <td style="border-color: #059669;"><i class="fas fa-chevron-down toggle-icon text-emerald-700"></i></td>
                        <td style="border-color: #a7f3d0;">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-hand-holding-dollar text-emerald-700"></i>
                                <span>A. PENDAPATAN MASING-MASING UNIT SEKOLAH</span>
                            </div>
                        </td>
                        <td class="cell-empty" style="border-color: #a7f3d0;">—</td>
                        <td class="cell-empty" style="border-color: #a7f3d0;">—</td>
                        <td class="cell-empty" style="border-color: #a7f3d0;">—</td>
                        <td class="cell-empty" style="border-color: #a7f3d0;">—</td>
                    </tr>

                    @php $incomeNo = 1; @endphp
                    @foreach($schoolSppData as $schData)
                    <tr class="ledger-detail section-row-income">
                        <td>{{ $incomeNo++ }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-school text-emerald-500 text-xs"></i>
                                <span>Pendapatan SPP {{ $schData['school']->name }}</span>
                                <span class="pro-badge border border-emerald-300" style="background-color: #ecfdf5 !important; color: #065f46 !important; font-size: 9px; padding: 1px 6px;">
                                    {{ $schData['total_students'] }} Siswa
                                </span>
                            </div>
                        </td>
                        <td class="num-col" style="color: #059669; font-weight: 700;">{{ number_format($schData['income_monthly'], 0, ',', '.') }}</td>
                        <td class="cell-empty">—</td>
                        <td class="cell-empty">—</td>
                        <td class="cell-empty">—</td>
                    </tr>
                    @endforeach

                    {{-- Subtotal Pendapatan --}}
                    <tr class="ledger-subtotal">
                        <td></td>
                        <td class="font-black">TOTAL PENDAPATAN (A)</td>
                        <td class="num-col" style="color: #059669; font-weight: 900; font-size: 13px;">{{ number_format($grandTotalIncomeMonthly, 0, ',', '.') }}</td>
                        <td class="cell-empty">—</td>
                        <td class="num-col" style="color: #059669; font-weight: 900;">{{ number_format($grandTotalIncomeMonthly, 0, ',', '.') }}</td>
                        <td class="num-col" style="color: #059669; font-weight: 900;">{{ number_format($grandTotalIncome, 0, ',', '.') }}</td>
                    </tr>

                    {{-- ============================== --}}
                    {{-- SEKSI B: BELANJA PEGAWAI       --}}
                    {{-- ============================== --}}
                    <tr class="ledger-section section-toggle" onclick="toggleSection('salary')" style="background-color: #fffbeb !important;">
                        <td style="border-color: #d97706;"><i class="fas fa-chevron-down toggle-icon text-amber-700"></i></td>
                        <td style="border-color: #fde68a;">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-users-gear text-amber-700"></i>
                                <span>B. BELANJA PEGAWAI MASING-MASING UNIT</span>
                            </div>
                        </td>
                        <td class="cell-empty" style="border-color: #fde68a;">—</td>
                        <td class="cell-empty" style="border-color: #fde68a;">—</td>
                        <td class="cell-empty" style="border-color: #fde68a;">—</td>
                        <td class="cell-empty" style="border-color: #fde68a;">—</td>
                    </tr>

                    @php $salaryNo = 1; @endphp
                    @foreach($schoolSalaryData as $salData)
                    <tr class="ledger-detail section-row-salary">
                        <td>{{ $salaryNo++ }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <i class="fas {{ $salData['school']->type === 'yayasan' ? 'fa-building' : 'fa-school' }} text-amber-500 text-xs"></i>
                                <span>Gaji & Tunjangan {{ $salData['school']->name }}</span>
                                <span class="pro-badge border border-amber-300" style="background-color: #fffbeb !important; color: #92400e !important; font-size: 9px; padding: 1px 6px;">
                                    {{ $salData['employee_count'] }} Org
                                </span>
                            </div>
                        </td>
                        <td class="cell-empty">—</td>
                        <td class="num-col" style="color: #dc2626; font-weight: 700;">{{ number_format($salData['salary_monthly'], 0, ',', '.') }}</td>
                        <td class="cell-empty">—</td>
                        <td class="cell-empty">—</td>
                    </tr>
                    @endforeach

                    {{-- Subtotal Belanja Pegawai --}}
                    <tr class="ledger-subtotal">
                        <td></td>
                        <td class="font-black">TOTAL BELANJA PEGAWAI (B)</td>
                        <td class="cell-empty">—</td>
                        <td class="num-col" style="color: #dc2626; font-weight: 900; font-size: 13px;">{{ number_format($totalGajiLembagaMonthly, 0, ',', '.') }}</td>
                        <td class="num-col" style="color: #dc2626; font-weight: 900;">({{ number_format($totalGajiLembagaMonthly, 0, ',', '.') }})</td>
                        <td class="num-col" style="color: #dc2626; font-weight: 900;">({{ number_format($totalGajiLembagaPeriod, 0, ',', '.') }})</td>
                    </tr>

                    {{-- ============================== --}}
                    {{-- SEKSI C: BELANJA OPERASIONAL   --}}
                    {{-- ============================== --}}
                    <tr class="ledger-section section-toggle" onclick="toggleSection('opex')" style="background-color: #fef2f2 !important;">
                        <td style="border-color: #dc2626;"><i class="fas fa-chevron-down toggle-icon text-red-700"></i></td>
                        <td style="border-color: #fca5a5;">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-list-check text-red-700"></i>
                                <span>C. BELANJA OPERASIONAL YAYASAN (NON-GAJI)</span>
                            </div>
                        </td>
                        <td class="cell-empty" style="border-color: #fca5a5;">—</td>
                        <td class="cell-empty" style="border-color: #fca5a5;">—</td>
                        <td class="cell-empty" style="border-color: #fca5a5;">—</td>
                        <td class="cell-empty" style="border-color: #fca5a5;">—</td>
                    </tr>

                    @php $opexNo = 1; @endphp
                    @foreach($operationalExpenseItems as $opItem)
                        @if($opItem['amount_monthly'] > 0)
                        <tr class="ledger-detail section-row-opex">
                            <td>{{ $opexNo++ }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <i class="fas {{ $opItem['icon'] }} text-red-400 text-xs"></i>
                                    <span class="text-slate-400 font-black text-[10px]">{{ $opItem['code'] }}</span>
                                    <span>{{ $opItem['name'] }}</span>
                                </div>
                            </td>
                            <td class="cell-empty">—</td>
                            <td class="num-col" style="color: #dc2626; font-weight: 700;">{{ number_format($opItem['amount_monthly'], 0, ',', '.') }}</td>
                            <td class="cell-empty">—</td>
                            <td class="cell-empty">—</td>
                        </tr>
                        @endif
                    @endforeach

                    {{-- Jika belum ada data belanja operasional --}}
                    @if(collect($operationalExpenseItems)->sum('amount_monthly') == 0)
                    <tr class="ledger-detail section-row-opex">
                        <td colspan="6" class="text-center text-slate-400 italic" style="padding: 16px;">
                            <i class="fas fa-info-circle mr-1"></i> Belum ada rincian belanja operasional yang diinput pada Halaman 2
                        </td>
                    </tr>
                    @endif

                    {{-- Subtotal Belanja Operasional --}}
                    <tr class="ledger-subtotal">
                        <td></td>
                        <td class="font-black">TOTAL BELANJA OPERASIONAL (C)</td>
                        <td class="cell-empty">—</td>
                        <td class="num-col" style="color: #dc2626; font-weight: 900; font-size: 13px;">{{ number_format($totalBelanjaOpsMonthly, 0, ',', '.') }}</td>
                        <td class="num-col" style="color: #dc2626; font-weight: 900;">({{ number_format($totalBelanjaOpsMonthly, 0, ',', '.') }})</td>
                        <td class="num-col" style="color: #dc2626; font-weight: 900;">({{ number_format($totalBelanjaOpsPeriod, 0, ',', '.') }})</td>
                    </tr>

                    {{-- ============================== --}}
                    {{-- TOTAL SELURUH BELANJA (B + C)  --}}
                    {{-- ============================== --}}
                    <tr class="ledger-subtotal" style="background-color: #e2e8f0 !important; border-top: 3px solid #000 !important; border-bottom: 3px solid #000 !important;">
                        <td></td>
                        <td class="font-black" style="font-size: 12px;">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-calculator text-slate-700"></i>
                                <span>TOTAL SELURUH BELANJA (B + C)</span>
                            </div>
                        </td>
                        <td class="cell-empty">—</td>
                        <td class="num-col" style="color: #000; font-weight: 900; font-size: 13px; border-color: #94a3b8;">{{ number_format($grandTotalBelanjaMonthly, 0, ',', '.') }}</td>
                        <td class="num-col" style="color: #dc2626; font-weight: 900; border-color: #94a3b8;">({{ number_format($grandTotalBelanjaMonthly, 0, ',', '.') }})</td>
                        <td class="num-col" style="color: #dc2626; font-weight: 900; border-color: #94a3b8;">({{ number_format($grandTotalBelanjaPeriod, 0, ',', '.') }})</td>
                    </tr>

                </tbody>
                <tfoot>
                    {{-- ============================== --}}
                    {{-- SALDO BERSIH AKHIR (A - B - C) --}}
                    {{-- ============================== --}}
                    <tr class="ledger-grand-total">
                        <td style="border-color: #1e293b;">
                            <i class="fas {{ $grandTotalSaldoAkhir >= 0 ? 'fa-chart-line' : 'fa-triangle-exclamation' }} text-lg" style="color: {{ $grandTotalSaldoAkhir >= 0 ? '#34d399' : '#f87171' }};"></i>
                        </td>
                        <td style="border-color: #1e293b;">
                            <div>
                                <span class="text-amber-400">SALDO BERSIH AKHIR PERGURUAN</span>
                                <p class="text-slate-500 text-[10px] font-bold mt-0.5">Pendapatan (A) − Belanja Pegawai (B) − Belanja Operasional (C)</p>
                            </div>
                        </td>
                        <td class="num-col" style="color: #059669; font-size: 12px; border-color: #1e293b;">{{ number_format($grandTotalIncomeMonthly, 0, ',', '.') }}</td>
                        <td class="num-col" style="color: #f87171; font-size: 12px; border-color: #1e293b;">{{ number_format($grandTotalBelanjaMonthly, 0, ',', '.') }}</td>
                        <td class="num-col" style="border-color: #1e293b;">
                            <span class="text-lg" style="color: {{ $grandTotalSaldoAkhirMonthly >= 0 ? '#34d399' : '#f87171' }};">
                                {{ $grandTotalSaldoAkhirMonthly >= 0 ? '+' : '' }}{{ number_format($grandTotalSaldoAkhirMonthly, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="num-col" style="border-color: #1e293b;">
                            <span class="text-xl" style="color: {{ $grandTotalSaldoAkhir >= 0 ? '#34d399' : '#f87171' }};">
                                {{ $grandTotalSaldoAkhir >= 0 ? '+' : '' }}{{ number_format($grandTotalSaldoAkhir, 0, ',', '.') }}
                            </span>
                            <div class="mt-1">
                                <span class="pro-badge border" style="background-color: {{ $grandTotalSaldoAkhir >= 0 ? '#059669' : '#dc2626' }} !important; color: #fff !important; font-size: 8px; padding: 2px 6px; border-color: {{ $grandTotalSaldoAkhir >= 0 ? '#34d399' : '#f87171' }};">
                                    <i class="fas {{ $grandTotalSaldoAkhir >= 0 ? 'fa-check-circle' : 'fa-exclamation-circle' }} text-[7px]"></i>
                                    {{ $grandTotalSaldoAkhir >= 0 ? 'SURPLUS' : 'DEFISIT' }}
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
