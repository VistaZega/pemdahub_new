@extends('layouts.yayasan')

@section('title', 'Rencana Belanja Yayasan & Perguruan')

@push('styles')
<style>
    /* === RAPBY PREMIUM STYLES === */
    .rapby-gradient { background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4c1d95 100%); }
    .rapby-card { backdrop-filter: blur(16px); background: rgba(255,255,255,0.95); border: 1px solid rgba(99,102,241,0.12); }
    .rapby-table { border-collapse: separate; border-spacing: 0; }
    .rapby-table th { position: sticky; top: 0; z-index: 10; }
    
    /* Unit school header with gradient left border */
    .unit-header { 
        background: linear-gradient(90deg, #eff6ff 0%, #f8fafc 100%);
        border-left: 4px solid #3b82f6;
        transition: all 0.2s ease;
    }
    .unit-header:hover { background: linear-gradient(90deg, #dbeafe 0%, #f1f5f9 100%); }
    .unit-header.yayasan-unit { border-left-color: #8b5cf6; background: linear-gradient(90deg, #f5f3ff 0%, #faf5ff 100%); }
    .unit-header.yayasan-unit:hover { background: linear-gradient(90deg, #ede9fe 0%, #f3e8ff 100%); }
    
    /* Item rows with indent */
    .item-row { 
        transition: all 0.15s ease;
        border-left: 4px solid transparent;
    }
    .item-row:hover { 
        background: #f8fafc !important; 
        border-left-color: #6366f1;
        transform: translateX(2px);
    }
    .item-row td:first-child { padding-left: 2.5rem; }
    
    /* Category pill */
    .cat-pill {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 8px; border-radius: 9999px; font-size: 10px; font-weight: 700;
        white-space: nowrap;
    }
    
    /* Ops group header */
    .ops-group-header {
        background: linear-gradient(90deg, #451a03 0%, #78350f 50%, #92400e 100%);
    }
    
    /* Number formatting */
    .num-col { font-variant-numeric: tabular-nums; letter-spacing: -0.01em; }
    
    /* Animated badge */
    .auto-badge {
        display: inline-flex; align-items: center; gap: 3px;
        padding: 1px 6px; border-radius: 4px; font-size: 9px; font-weight: 800;
        background: linear-gradient(135deg, #dbeafe, #e0e7ff);
        color: #1e40af; border: 1px solid #bfdbfe;
        text-transform: uppercase; letter-spacing: 0.05em;
    }
    
    /* Smooth input focus */
    .rapby-input {
        transition: all 0.2s ease;
        border: 1.5px solid #e5e7eb;
    }
    .rapby-input:focus {
        border-color: #8b5cf6;
        box-shadow: 0 0 0 3px rgba(139,92,246,0.15);
        outline: none;
    }
    
    /* Summary stat card */
    .stat-card {
        position: relative; overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
    .stat-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    }
    .stat-card.blue::before { background: linear-gradient(90deg, #3b82f6, #6366f1); }
    .stat-card.amber::before { background: linear-gradient(90deg, #f59e0b, #d97706); }
    .stat-card.violet::before { background: linear-gradient(90deg, #7c3aed, #a855f7); }
    
    /* Grand total footer */
    .grand-total-footer {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4c1d95 100%);
    }
    
    /* Collapse animation */
    .unit-items { overflow: hidden; transition: max-height 0.3s ease; }
    
    @media print {
        .no-print { display: none !important; }
        .rapby-table th { background: #4c1d95 !important; color: white !important; -webkit-print-color-adjust: exact; }
    }
</style>
@endpush

@section('content')
<div class="space-y-6">

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- HERO HEADER --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div class="rapby-gradient rounded-2xl p-6 md:p-8 text-white shadow-2xl relative overflow-hidden">
        {{-- Decorative shapes --}}
        <div class="absolute top-0 right-0 w-72 h-72 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/4"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-11 h-11 rounded-xl bg-amber-400/20 flex items-center justify-center">
                        <i class="fas fa-file-invoice-dollar text-amber-400 text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl md:text-2xl font-black tracking-tight">Rencana Anggaran Belanja</h1>
                        <p class="text-indigo-200 text-xs font-medium">Yayasan & Perguruan (RAPBY)</p>
                    </div>
                </div>
                <p class="text-indigo-300 text-[11px] max-w-lg leading-relaxed mt-1">
                    Penyusunan hirarki rincian anggaran belanja pegawai per unit pendidikan & yayasan 
                    beserta belanja operasional terpusat — secara otomatis dari Sistem Penugasan SDM.
                </p>
            </div>

            <form method="GET" action="{{ route('yayasan.operational_expenses.index') }}" class="flex flex-wrap items-center gap-3 no-print">
                <select name="academic_year_id" onchange="this.form.submit()" 
                    class="bg-white/10 text-white border border-white/20 rounded-xl text-xs px-3 py-2.5 font-bold backdrop-blur-md focus:ring-2 focus:ring-amber-400 min-w-[160px]">
                    @foreach($allYears as $y)
                        <option value="{{ $y->id }}" {{ ($currentYear->id ?? '') == $y->id ? 'selected' : '' }} class="text-gray-900">
                            TP {{ $y->year }} {{ $y->is_active ? '✦ Aktif' : '' }}
                        </option>
                    @endforeach
                </select>

                <div class="bg-white/10 p-1 rounded-xl border border-white/20 backdrop-blur-md flex items-center gap-1">
                    <a href="{{ route('yayasan.operational_expenses.index', ['academic_year_id' => $currentYear->id, 'period_mode' => 'annual']) }}"
                       class="px-3.5 py-2 rounded-lg text-xs font-bold transition {{ $periodMode === 'annual' ? 'bg-amber-400 text-amber-950 shadow-lg' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                        <i class="fas fa-calendar-days mr-1"></i> 12 Bulan
                    </a>
                    <a href="{{ route('yayasan.operational_expenses.index', ['academic_year_id' => $currentYear->id, 'period_mode' => 'monthly']) }}"
                       class="px-3.5 py-2 rounded-lg text-xs font-bold transition {{ $periodMode === 'monthly' ? 'bg-amber-400 text-amber-950 shadow-lg' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                        <i class="fas fa-calendar-day mr-1"></i> 1 Bulan
                    </a>
                </div>

                <a href="{{ route('yayasan.operational_expenses.export_pdf', ['academic_year_id' => $currentYear->id, 'period_mode' => $periodMode]) }}" 
                   target="_blank"
                   class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-white font-bold text-xs rounded-xl shadow-lg transition-all hover:shadow-emerald-500/25 flex items-center gap-1.5">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-bold flex items-center gap-2 shadow-sm animate-pulse">
            <i class="fas fa-check-circle text-emerald-600 text-base"></i> {{ session('success') }}
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- SUMMARY STAT CARDS --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        {{-- Card 1: Belanja Pegawai --}}
        <div class="stat-card blue rapby-card rounded-2xl p-5 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/25">
                    <i class="fas fa-users-gear text-lg"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Kel. 5.1.00 — Belanja Pegawai</p>
                    <p class="text-xl font-black text-gray-900 mt-0.5 num-col">Rp {{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}</p>
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="cat-pill bg-blue-100 text-blue-800"><i class="fas fa-school text-[8px]"></i> {{ count($hierarchicalSalaryData) }} Unit</span>
                        <span class="cat-pill bg-indigo-100 text-indigo-800"><i class="fas fa-user text-[8px]"></i> {{ $totalPegawaiCount }} Pegawai</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Belanja Operasional --}}
        <div class="stat-card amber rapby-card rounded-2xl p-5 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center shadow-lg shadow-amber-500/25">
                    <i class="fas fa-list-check text-lg"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Kel. 5.1.01–14 — Belanja Operasional</p>
                    <p class="text-xl font-black text-gray-900 mt-0.5 num-col" id="cardOpsTotal">Rp {{ number_format($totalOpsPeriod, 0, ',', '.') }}</p>
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="cat-pill bg-amber-100 text-amber-800"><i class="fas fa-pen text-[8px]"></i> Dapat Diedit</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Grand Total --}}
        <div class="stat-card violet rapby-card rounded-2xl p-5 shadow-sm bg-gradient-to-br from-violet-50/80 to-purple-50/80">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-600 to-purple-700 text-white flex items-center justify-center shadow-lg shadow-violet-600/30">
                    <i class="fas fa-calculator text-lg"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] text-violet-700 font-extrabold uppercase tracking-wider">Grand Total RAPBY</p>
                    <p class="text-2xl font-black text-violet-950 mt-0.5 num-col" id="cardGrandTotal">Rp {{ number_format($grandTotalBelanjaPeriod, 0, ',', '.') }}</p>
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="cat-pill bg-violet-100 text-violet-800"><i class="fas fa-sigma text-[8px]"></i> Pegawai + Operasional</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- MAIN RAPBY TABLE --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <form method="POST" action="{{ route('yayasan.operational_expenses.store') }}" id="rapbyForm">
        @csrf
        <input type="hidden" name="academic_year_id" value="{{ $currentYear->id }}">

        <div class="rapby-card rounded-2xl shadow-lg overflow-hidden">
            {{-- Table Header --}}
            <div class="rapby-gradient p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center">
                        <i class="fas fa-table-list text-amber-400"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-extrabold text-white tracking-tight">Rincian Hirarki Anggaran Belanja</h2>
                        <p class="text-indigo-300 text-[10px] font-medium">Tahun Pelajaran {{ $currentYear->year ?? '-' }} • Periode {{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }}</p>
                    </div>
                </div>
                <button type="submit" class="no-print px-6 py-2.5 bg-amber-400 hover:bg-amber-300 text-amber-950 font-black text-xs rounded-xl shadow-lg transition-all hover:shadow-amber-400/30 flex items-center gap-2">
                    <i class="fas fa-save"></i> Simpan Rencana Belanja
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="rapby-table w-full text-xs text-left">
                    <thead>
                        <tr class="bg-gradient-to-r from-slate-800 to-slate-700 text-white">
                            <th class="px-3 py-3.5 text-center w-14 font-bold text-[10px] uppercase tracking-wider">No</th>
                            <th class="px-3 py-3.5 w-28 font-bold text-[10px] uppercase tracking-wider">Kode Rek.</th>
                            <th class="px-4 py-3.5 font-bold text-[10px] uppercase tracking-wider">Nama & Rincian Rekening Belanja</th>
                            <th class="px-3 py-3.5 w-24 text-center font-bold text-[10px] uppercase tracking-wider">Jumlah</th>
                            <th class="px-3 py-3.5 w-24 text-center font-bold text-[10px] uppercase tracking-wider">Satuan</th>
                            <th class="px-4 py-3.5 text-right w-40 font-bold text-[10px] uppercase tracking-wider">Tarif Satuan (Rp)</th>
                            <th class="px-4 py-3.5 text-right w-40 font-bold text-[10px] uppercase tracking-wider">Total / Bulan</th>
                            <th class="px-4 py-3.5 text-right w-44 font-bold text-[10px] uppercase tracking-wider">Total Periode</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100/80">

                        {{-- ════════════════════════════════════════════════════════ --}}
                        {{-- KELOMPOK 5.1.00: BELANJA PEGAWAI PERGURUAN --}}
                        {{-- ════════════════════════════════════════════════════════ --}}
                        <tr class="bg-gradient-to-r from-blue-900 via-blue-800 to-indigo-900 text-white">
                            <td class="px-3 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-white/15 text-[10px] font-black">5.1</span>
                            </td>
                            <td class="px-3 py-3 font-mono font-black text-blue-200 text-[11px]">5.1.00</td>
                            <td colspan="4" class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-users-gear text-blue-300"></i>
                                    <span class="font-extrabold uppercase tracking-wide text-[11px]">Kelompok: Belanja Pegawai Perguruan</span>
                                    <span class="auto-badge bg-blue-200/20 text-blue-200 border-blue-400/30 ml-1">
                                        <i class="fas fa-lock text-[7px]"></i> Otomatis Penugasan
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-blue-200 num-col">
                                Rp {{ number_format($totalGajiPerguruanMonthly, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-black text-white text-sm num-col">
                                Rp {{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}
                            </td>
                        </tr>

                        {{-- Per-Unit Hierarchical Rows --}}
                        @foreach($hierarchicalSalaryData as $uIdx => $uData)
                            {{-- UNIT HEADER ROW --}}
                            <tr class="unit-header {{ $uData['school_type'] === 'yayasan' ? 'yayasan-unit' : '' }} cursor-pointer" 
                                onclick="toggleUnit({{ $uIdx }})">
                                <td class="px-3 py-3 text-center">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full {{ $uData['school_type'] === 'yayasan' ? 'bg-violet-100 text-violet-700' : 'bg-blue-100 text-blue-700' }} text-[10px] font-black">
                                        {{ $uIdx + 1 }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 font-mono font-bold {{ $uData['school_type'] === 'yayasan' ? 'text-violet-700' : 'text-blue-700' }} text-[11px]">
                                    {{-- Bukan kode rek spesifik, ini header unit --}}
                                </td>
                                <td colspan="3" class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <i class="fas {{ $uData['school_type'] === 'yayasan' ? 'fa-building text-violet-500' : 'fa-school text-blue-500' }} text-sm"></i>
                                        <div>
                                            <p class="font-extrabold {{ $uData['school_type'] === 'yayasan' ? 'text-violet-900' : 'text-blue-950' }} text-[12px] uppercase tracking-wide">
                                                {{ $uData['school_name'] }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="cat-pill {{ $uData['school_type'] === 'yayasan' ? 'bg-violet-100 text-violet-700' : 'bg-blue-50 text-blue-700' }}">
                                                    <i class="fas fa-user-group text-[7px]"></i> {{ $uData['employee_count'] }} Pegawai
                                                </span>
                                                <span class="cat-pill bg-gray-100 text-gray-600">
                                                    <i class="fas fa-layer-group text-[7px]"></i> {{ count($uData['items']) }} Komponen
                                                </span>
                                            </div>
                                        </div>
                                        <i class="fas fa-chevron-down text-gray-400 text-[10px] ml-auto transition-transform" id="chevron_{{ $uIdx }}"></i>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-[10px] text-gray-500 block">Subtotal/Bln</span>
                                    <span class="font-bold {{ $uData['school_type'] === 'yayasan' ? 'text-violet-900' : 'text-blue-900' }} num-col">
                                        Rp {{ number_format($uData['total_monthly'], 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-bold {{ $uData['school_type'] === 'yayasan' ? 'text-violet-800' : 'text-blue-800' }} num-col">
                                    Rp {{ number_format($uData['total_monthly'], 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="font-black {{ $uData['school_type'] === 'yayasan' ? 'text-violet-950' : 'text-blue-950' }} text-sm num-col">
                                        Rp {{ number_format($uData['total_period'], 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>

                            {{-- ITEM DETAIL ROWS (collapsible) --}}
                            @foreach($uData['items'] as $iIdx => $item)
                                @php
                                    $amtMonthly = $item['amount'];
                                    $amtPeriod = $amtMonthly * $multiplier;
                                @endphp
                                <tr class="item-row unit-items-{{ $uIdx }} bg-white">
                                    <td class="px-3 py-2.5 text-center text-gray-300 text-[10px]">
                                        {{ $uIdx + 1 }}.{{ $iIdx + 1 }}
                                    </td>
                                    <td class="px-3 py-2.5">
                                        <span class="font-mono text-[10px] {{ $uData['school_type'] === 'yayasan' ? 'text-violet-600' : 'text-blue-600' }} font-semibold">
                                            {{ $item['code'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <div class="flex items-center gap-2 pl-4">
                                            <div class="w-5 h-5 rounded flex items-center justify-center {{ $uData['school_type'] === 'yayasan' ? 'bg-violet-100 text-violet-500' : 'bg-blue-50 text-blue-500' }}">
                                                <i class="fas {{ $item['icon'] }} text-[9px]"></i>
                                            </div>
                                            <span class="font-semibold text-gray-800">{{ $item['name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5 text-center font-bold text-gray-800 num-col">
                                        {{ number_format($item['volume'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2.5 text-center font-semibold text-gray-600">
                                        {{ $item['unit'] }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-semibold text-gray-800 num-col">
                                        Rp {{ number_format($item['tariff'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-bold text-gray-800 num-col">
                                        Rp {{ number_format($amtMonthly, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-black {{ $uData['school_type'] === 'yayasan' ? 'text-violet-900' : 'text-blue-900' }} num-col">
                                        Rp {{ number_format($amtPeriod, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach

                        {{-- Subtotal Belanja Pegawai --}}
                        <tr class="bg-blue-50 border-y-2 border-blue-200">
                            <td colspan="6" class="px-4 py-3 text-right font-extrabold text-blue-900 uppercase text-[10px] tracking-wider">
                                <i class="fas fa-sigma mr-1.5 text-blue-600"></i> Subtotal Kelompok Belanja Pegawai (5.1.00):
                            </td>
                            <td class="px-4 py-3 text-right font-black text-blue-900 num-col">
                                Rp {{ number_format($totalGajiPerguruanMonthly, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-black text-blue-950 text-sm num-col">
                                Rp {{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}
                            </td>
                        </tr>

                        {{-- ════════════════════════════════════════════════════════ --}}
                        {{-- KELOMPOK 5.1.01+: BELANJA OPERASIONAL NON-GAJI --}}
                        {{-- ════════════════════════════════════════════════════════ --}}
                        <tr class="ops-group-header text-white">
                            <td class="px-3 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-white/15 text-[10px] font-black">5.1</span>
                            </td>
                            <td class="px-3 py-3 font-mono font-black text-amber-200 text-[11px]">5.1.01+</td>
                            <td colspan="4" class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-list-check text-amber-300"></i>
                                    <span class="font-extrabold uppercase tracking-wide text-[11px]">Kelompok: Belanja Operasional Non-Gaji</span>
                                    <span class="auto-badge bg-amber-200/20 text-amber-200 border-amber-400/30 ml-1">
                                        <i class="fas fa-pen text-[7px]"></i> Dapat Diedit
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-amber-200 num-col" id="groupOpsMonthly">
                                Rp {{ number_format($totalOpsMonthly, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-black text-white text-sm num-col" id="groupOpsPeriod">
                                Rp {{ number_format($totalOpsPeriod, 0, ',', '.') }}
                            </td>
                        </tr>

                        {{-- Operational Expense Rows --}}
                        @php $opsNo = 1; @endphp
                        @foreach($parsedExpenseDetails as $code => $detail)
                            @if(!$detail['is_automatic'])
                                @php
                                    $vol = $detail['volume'] ?? 1;
                                    $unit = $detail['unit'] ?? 'Paket';
                                    $tariff = $detail['tariff'] ?? 0;
                                    $amtMonthly = $detail['amount'] ?? 0;
                                    $amtPeriod = $amtMonthly * $multiplier;
                                    $safeCode = str_replace('.', '_', $code);
                                @endphp
                                <tr class="hover:bg-amber-50/40 transition border-l-4 border-transparent hover:border-amber-500 {{ $opsNo % 2 === 0 ? 'bg-gray-50/50' : 'bg-white' }}">
                                    <td class="px-3 py-3 text-center">
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-100 text-amber-700 text-[9px] font-bold">
                                            {{ $opsNo++ }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3">
                                        <span class="font-mono font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded text-[11px]">
                                            {{ $code }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-md bg-gray-100 text-gray-500 flex items-center justify-center">
                                                <i class="fas {{ $detail['icon'] }} text-[10px]"></i>
                                            </div>
                                            <div>
                                                <span class="font-bold text-gray-900 text-[11px]">{{ $detail['name'] }}</span>
                                                <span class="cat-pill bg-gray-100 text-gray-500 ml-1.5">{{ $detail['category'] }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-2 py-3 text-center">
                                        <input type="number" name="expense_details[{{ $code }}][volume]" 
                                               value="{{ $vol }}" min="1" step="1" 
                                               oninput="updateRowCalc('{{ $safeCode }}')"
                                               id="vol_{{ $safeCode }}"
                                               class="rapby-input w-full text-xs font-bold text-center py-1.5 rounded-lg bg-white" 
                                               placeholder="1">
                                    </td>
                                    <td class="px-2 py-3 text-center">
                                        <input type="text" name="expense_details[{{ $code }}][unit]" 
                                               value="{{ $unit }}"
                                               id="unit_{{ $safeCode }}"
                                               class="rapby-input w-full text-xs font-semibold text-center py-1.5 rounded-lg bg-white" 
                                               placeholder="Bulan">
                                    </td>
                                    <td class="px-3 py-3 text-right">
                                        <div class="relative">
                                            <span class="absolute left-2.5 top-2 text-[10px] font-bold text-gray-400">Rp</span>
                                            <input type="number" name="expense_details[{{ $code }}][tariff]" 
                                                   value="{{ $tariff > 0 ? $tariff : '' }}" 
                                                   step="5000" min="0" 
                                                   oninput="updateRowCalc('{{ $safeCode }}')"
                                                   id="tariff_{{ $safeCode }}"
                                                   class="rapby-input w-full text-xs font-bold text-right pl-8 pr-2 py-1.5 rounded-lg bg-white num-col" 
                                                   placeholder="0">
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-700 num-col" id="monthly_{{ $safeCode }}">
                                        Rp {{ number_format($amtMonthly, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-black text-amber-900 num-col" id="period_{{ $safeCode }}">
                                        Rp {{ number_format($amtPeriod, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach

                        {{-- Subtotal Belanja Operasional --}}
                        <tr class="bg-amber-50 border-y-2 border-amber-200">
                            <td colspan="6" class="px-4 py-3 text-right font-extrabold text-amber-900 uppercase text-[10px] tracking-wider">
                                <i class="fas fa-sigma mr-1.5 text-amber-600"></i> Subtotal Kelompok Belanja Operasional (5.1.01–14):
                            </td>
                            <td class="px-4 py-3 text-right font-black text-amber-900 num-col" id="subtotalOpsMonthly">
                                Rp {{ number_format($totalOpsMonthly, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-black text-amber-950 text-sm num-col" id="subtotalOpsPeriod">
                                Rp {{ number_format($totalOpsPeriod, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>

                    {{-- GRAND TOTAL FOOTER --}}
                    <tfoot>
                        <tr class="grand-total-footer text-white">
                            <td colspan="6" class="px-6 py-4 text-right">
                                <span class="uppercase tracking-widest font-extrabold text-[11px] text-indigo-200">Grand Total Rencana Belanja RAPBY:</span>
                            </td>
                            <td class="px-4 py-4 text-right num-col">
                                <span class="text-amber-300 font-black text-sm" id="footTotalMonthly">Rp {{ number_format($grandTotalBelanjaMonthly, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-4 py-4 text-right num-col">
                                <span class="text-emerald-300 font-black text-lg" id="footTotalPeriod">Rp {{ number_format($grandTotalBelanjaPeriod, 0, ',', '.') }}</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Footer Actions --}}
            <div class="p-6 bg-gray-50/80 border-t border-gray-200 flex flex-col md:flex-row items-center justify-between gap-4 no-print">
                <div class="w-full md:w-2/3">
                    <label class="block text-[10px] font-bold text-gray-500 mb-1.5 uppercase tracking-wider">
                        <i class="fas fa-comment-dots mr-1 text-gray-400"></i> Catatan Rencana Anggaran (Opsional)
                    </label>
                    <input type="text" name="notes" value="{{ $contribution->notes ?? '' }}" 
                           class="rapby-input w-full text-xs p-3 rounded-xl bg-white" 
                           placeholder="Tambahkan catatan atau keterangan persetujuan rencana anggaran belanja...">
                </div>
                <button type="submit" class="px-8 py-3 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-black text-xs rounded-xl shadow-lg transition-all hover:shadow-violet-600/30 flex items-center gap-2">
                    <i class="fas fa-save"></i> Simpan Rencana Belanja
                </button>
            </div>
        </div>
    </form>

</div>

@push('scripts')
<script>
    const totalGajiMonthly = {{ $totalGajiPerguruanMonthly }};
    const multiplier = {{ $multiplier }};

    // Toggle unit collapse
    function toggleUnit(idx) {
        const rows = document.querySelectorAll('.unit-items-' + idx);
        const chevron = document.getElementById('chevron_' + idx);
        rows.forEach(function(row) {
            row.style.display = row.style.display === 'none' ? '' : 'none';
        });
        if (chevron) {
            chevron.style.transform = chevron.style.transform === 'rotate(180deg)' ? '' : 'rotate(180deg)';
        }
    }

    // Live-calculate operational expense row
    function updateRowCalc(safeCode) {
        const volInput = document.getElementById('vol_' + safeCode);
        const tariffInput = document.getElementById('tariff_' + safeCode);
        if (!volInput || !tariffInput) return;

        const vol = parseFloat(volInput.value) || 0;
        const tariff = parseFloat(tariffInput.value) || 0;
        const monthly = vol * tariff;
        const period = monthly * multiplier;

        document.getElementById('monthly_' + safeCode).innerText = 'Rp ' + monthly.toLocaleString('id-ID');
        document.getElementById('period_' + safeCode).innerText = 'Rp ' + period.toLocaleString('id-ID');

        recalcGrandTotals();
    }

    function recalcGrandTotals() {
        let sumOpsMonthly = 0;
        document.querySelectorAll('input[id^="tariff_"]').forEach(function(tInput) {
            const key = tInput.id.replace('tariff_', '');
            const vInput = document.getElementById('vol_' + key);
            const v = parseFloat(vInput ? vInput.value : 1) || 0;
            const t = parseFloat(tInput.value) || 0;
            sumOpsMonthly += (v * t);
        });

        const sumOpsPeriod = sumOpsMonthly * multiplier;
        const grandMonthly = totalGajiMonthly + sumOpsMonthly;
        const grandPeriod = grandMonthly * multiplier;

        const fmt = (n) => 'Rp ' + n.toLocaleString('id-ID');

        document.getElementById('groupOpsMonthly').innerText = fmt(sumOpsMonthly);
        document.getElementById('groupOpsPeriod').innerText = fmt(sumOpsPeriod);
        document.getElementById('subtotalOpsMonthly').innerText = fmt(sumOpsMonthly);
        document.getElementById('subtotalOpsPeriod').innerText = fmt(sumOpsPeriod);
        document.getElementById('cardOpsTotal').innerText = fmt(sumOpsPeriod);
        document.getElementById('cardGrandTotal').innerText = fmt(grandPeriod);
        document.getElementById('footTotalMonthly').innerText = fmt(grandMonthly);
        document.getElementById('footTotalPeriod').innerText = fmt(grandPeriod);
    }
</script>
@endpush
@endsection
