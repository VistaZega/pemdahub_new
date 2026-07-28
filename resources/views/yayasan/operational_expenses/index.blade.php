@extends('layouts.yayasan')

@section('title', 'Rencana Belanja Yayasan & Perguruan (RAPBY)')

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

    .rapby-hero {
        background-color: #090d16;
        border: 2px solid #000000;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
    }

    .rapby-card-pro {
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
    .stat-card-pro.blue::before { background-color: #1d4ed8; }
    .stat-card-pro.amber::before { background-color: #d97706; }
    .stat-card-pro.violet::before { background-color: #6b21a8; }

    .rapby-input-pro {
        border: 2px solid #000000 !important;
        color: #000000 !important;
        font-weight: 900 !important;
        background-color: #ffffff !important;
        opacity: 1 !important;
    }
    .rapby-input-pro::placeholder {
        color: #334155 !important;
        opacity: 1 !important;
        font-weight: 800 !important;
    }
    .rapby-input-pro:focus {
        border-color: #1d4ed8 !important;
        box-shadow: 0 0 0 4px #93c5fd !important;
        outline: none !important;
    }

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
</style>
@endpush

@section('content')
<div class="ui-ux-promax space-y-6">

    {{-- HERO HEADER (100% SOLID CONTRAST) --}}
    <div class="rapby-hero rounded-3xl p-6 md:p-8 text-white relative overflow-hidden">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-amber-400 text-black flex items-center justify-center font-black shadow-lg border-2 border-black">
                        <i class="fas fa-file-invoice-dollar text-2xl"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Rencana Anggaran Belanja</h1>
                            <span class="pro-badge bg-amber-400 text-black border-2 border-black">RAPBY v2.0</span>
                        </div>
                        <p class="text-amber-400 text-sm font-black tracking-wide">Yayasan & Perguruan Pembda</p>
                    </div>
                </div>
                <p class="text-white text-xs font-black max-w-xl leading-relaxed">
                    Sistem Penyusunan Rencana Anggaran Belanja Pegawai (Otomatis Penugasan SDM) & Belanja Operasional Terpusat dengan Kontras 100% Solid & Presisi Tinggi.
                </p>
            </div>

            {{-- Controls & Period Selector --}}
            <form method="GET" action="{{ route('yayasan.operational_expenses.index') }}" class="flex flex-wrap items-center gap-3">
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
                        <a href="{{ route('yayasan.operational_expenses.index', ['academic_year_id' => $currentYear->id, 'period_mode' => 'annual']) }}"
                           class="px-4 py-2 rounded-lg text-xs font-black transition-all {{ $periodMode === 'annual' ? 'bg-amber-400 text-black border-2 border-black' : 'text-white hover:bg-slate-900' }}">
                            <i class="fas fa-calendar-days mr-1.5"></i> 12 Bulan
                        </a>
                        <a href="{{ route('yayasan.operational_expenses.index', ['academic_year_id' => $currentYear->id, 'period_mode' => 'monthly']) }}"
                           class="px-4 py-2 rounded-lg text-xs font-black transition-all {{ $periodMode === 'monthly' ? 'bg-amber-400 text-black border-2 border-black' : 'text-white hover:bg-slate-900' }}">
                            <i class="fas fa-calendar-day mr-1.5"></i> 1 Bulan
                        </a>
                    </div>
                </div>

                <div class="flex flex-col justify-end pt-5">
                    <a href="{{ route('yayasan.operational_expenses.export_pdf', ['academic_year_id' => $currentYear->id, 'period_mode' => $periodMode]) }}" target="_blank"
                       class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs rounded-xl shadow-lg transition flex items-center gap-2 border-2 border-black">
                        <i class="fas fa-file-pdf text-sm"></i> Export PDF
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-300 border-2 border-black text-black rounded-2xl text-xs font-black flex items-center gap-3 shadow-md">
            <i class="fas fa-check-circle text-emerald-950 text-xl"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- STATISTICAL SUMMARY CARDS (100% SOLID CONTRAST ON WHITE CARD) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="stat-card-pro blue rapby-card-pro rounded-2xl p-5 shadow-md">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-blue-900 text-white flex items-center justify-center font-black border-2 border-black">
                    <i class="fas fa-users-gear text-2xl"></i>
                </div>
                <div>
                    <p class="text-xs text-black font-black uppercase tracking-wider">5.1.00 — Belanja Pegawai</p>
                    <p class="text-2xl font-black text-black mt-0.5 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}</p>
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="pro-badge bg-blue-950 text-white border-2 border-black">
                            <i class="fas fa-building text-[10px]"></i> {{ count($hierarchicalSalaryData) }} Unit
                        </span>
                        <span class="pro-badge bg-indigo-950 text-white border-2 border-black">
                            <i class="fas fa-user-check text-[10px]"></i> {{ $totalPegawaiCount }} Pegawai
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="stat-card-pro amber rapby-card-pro rounded-2xl p-5 shadow-md">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-amber-600 text-white flex items-center justify-center font-black border-2 border-black">
                    <i class="fas fa-list-check text-2xl"></i>
                </div>
                <div>
                    <p class="text-xs text-black font-black uppercase tracking-wider">5.1.01–14 — Belanja Operasional</p>
                    <p class="text-2xl font-black text-black mt-0.5 num-col whitespace-nowrap" id="cardOpsTotal">Rp&nbsp;{{ number_format($totalOpsPeriod, 0, ',', '.') }}</p>
                    <span class="pro-badge bg-amber-400 text-black border-2 border-black mt-1.5">
                        <i class="fas fa-pen-to-square text-[10px]"></i> Dapat Diedit
                    </span>
                </div>
            </div>
        </div>

        <div class="stat-card-pro violet rapby-card-pro rounded-2xl p-5 shadow-md bg-purple-100 border-2 border-black">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-950 text-white flex items-center justify-center font-black border-2 border-black">
                    <i class="fas fa-calculator text-2xl"></i>
                </div>
                <div>
                    <p class="text-xs text-black font-black uppercase tracking-wider">Grand Total RAPBY</p>
                    <p class="text-2xl font-black text-black mt-0.5 num-col whitespace-nowrap" id="cardGrandTotal">Rp&nbsp;{{ number_format($grandTotalBelanjaPeriod, 0, ',', '.') }}</p>
                    <span class="pro-badge bg-purple-950 text-white border-2 border-black mt-1.5">
                        <i class="fas fa-layer-group text-[10px]"></i> Total Keseluruhan
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN TABLE SYSTEM --}}
    <form method="POST" action="{{ route('yayasan.operational_expenses.store') }}">
        @csrf
        <input type="hidden" name="academic_year_id" value="{{ $currentYear->id }}">

        <div class="rapby-card-pro rounded-3xl shadow-2xl overflow-hidden border-2 border-black">
            {{-- Table Header Toolbar --}}
            <div class="rapby-hero p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b-2 border-black">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-amber-400 text-black font-black flex items-center justify-center shadow-md border-2 border-black">
                        <i class="fas fa-table-list text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-white">Rincian Anggaran Belanja RAPBY</h2>
                        <p class="text-amber-400 text-xs font-black">Tahun Pelajaran {{ $currentYear->year ?? '-' }} • Periode {{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }}</p>
                    </div>
                </div>
                <button type="submit" class="px-6 py-3 bg-amber-400 hover:bg-amber-300 text-black font-black text-xs rounded-xl shadow-xl transition-all flex items-center gap-2 border-2 border-black">
                    <i class="fas fa-save text-sm"></i> Simpan Rencana Belanja
                </button>
            </div>

            {{-- Table View --}}
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse">
                    <thead>
                        <tr class="bg-black text-white border-b-2 border-black">
                            <th class="px-3 py-4 text-center w-14 text-xs uppercase font-black">No</th>
                            <th class="px-3 py-4 w-28 text-xs uppercase font-black">Kode Rek.</th>
                            <th class="px-4 py-4 text-xs uppercase font-black">Nama Rekening Belanja</th>
                            <th class="px-3 py-4 w-24 text-center text-xs uppercase font-black">Jumlah</th>
                            <th class="px-3 py-4 w-24 text-center text-xs uppercase font-black">Satuan</th>
                            <th class="px-4 py-4 text-right w-44 text-xs uppercase font-black whitespace-nowrap">Tarif Satuan (Rp)</th>
                            <th class="px-4 py-4 text-right w-44 text-xs uppercase font-black whitespace-nowrap">Total / Bulan</th>
                            <th class="px-4 py-4 text-right w-48 text-xs uppercase font-black whitespace-nowrap">Total Periode</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-slate-400 bg-white">

                        {{-- KELOMPOK 5.1.00: BELANJA PEGAWAI --}}
                        <tr class="bg-blue-950 text-white border-b-2 border-black">
                            <td class="px-3 py-3.5 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-blue-900 text-white text-xs font-black border-2 border-white">5.1</span>
                            </td>
                            <td class="px-3 py-3.5 font-mono font-black text-amber-400 text-sm">5.1.00</td>
                            <td colspan="4" class="px-4 py-3.5">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-users-gear text-amber-400 text-base"></i>
                                    <span class="font-black uppercase tracking-wide text-xs text-white">Kelompok: Belanja Pegawai Perguruan</span>
                                    <span class="pro-badge bg-blue-900 text-white border border-white ml-2">
                                        <i class="fas fa-lock text-[8px]"></i> Otomatis Penugasan
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-right font-black text-amber-400 num-col text-xs whitespace-nowrap">Rp&nbsp;{{ number_format($totalGajiPerguruanMonthly, 0, ',', '.') }}</td>
                            <td class="px-4 py-3.5 text-right font-black text-white text-sm num-col whitespace-nowrap">Rp&nbsp;{{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}</td>
                        </tr>

                        @foreach($hierarchicalSalaryData as $uIdx => $uData)
                            @php $item = $uData['items'][0] ?? null; @endphp
                            @if($item)
                            <tr class="hover:bg-slate-200 transition-all {{ $uData['school_type'] === 'yayasan' ? 'bg-purple-100' : 'bg-white' }} border-b border-slate-400">
                                <td class="px-3 py-4 text-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full {{ $uData['school_type'] === 'yayasan' ? 'bg-purple-950 text-white' : 'bg-blue-950 text-white' }} text-xs font-black border border-black">
                                        {{ $uIdx + 1 }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 font-mono font-black text-black text-xs">
                                    {{ $item['code'] }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-black text-amber-400 font-black flex items-center justify-center border border-black">
                                            <i class="fas {{ $item['icon'] }} text-sm"></i>
                                        </div>
                                        <span class="font-black text-black text-xs">{{ $item['name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-4 text-center font-black text-black num-col text-sm">{{ $item['volume'] }}</td>
                                <td class="px-3 py-4 text-center font-black text-black">{{ $item['unit'] }}</td>
                                <td class="px-4 py-4 text-right font-black text-black num-col whitespace-nowrap">Rp&nbsp;{{ number_format($item['tariff'], 0, ',', '.') }}</td>
                                <td class="px-4 py-4 text-right font-black text-black num-col whitespace-nowrap">Rp&nbsp;{{ number_format($item['amount'], 0, ',', '.') }}</td>
                                <td class="px-4 py-4 text-right font-black text-black text-xs num-col whitespace-nowrap">
                                    Rp&nbsp;{{ number_format($uData['total_period'], 0, ',', '.') }}
                                </td>
                            </tr>
                            @endif
                        @endforeach

                        {{-- Subtotal Pegawai --}}
                        <tr class="bg-blue-200 border-y-2 border-black">
                            <td colspan="6" class="px-4 py-4 text-right font-black text-black uppercase text-xs tracking-wider">
                                <i class="fas fa-sigma mr-1.5 text-black text-sm"></i> Subtotal Belanja Pegawai (5.1.00):
                            </td>
                            <td class="px-4 py-4 text-right font-black text-black text-xs num-col whitespace-nowrap">Rp&nbsp;{{ number_format($totalGajiPerguruanMonthly, 0, ',', '.') }}</td>
                            <td class="px-4 py-4 text-right font-black text-black text-sm num-col whitespace-nowrap">Rp&nbsp;{{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}</td>
                        </tr>

                        {{-- KELOMPOK 5.1.01+: BELANJA OPERASIONAL --}}
                        <tr class="bg-amber-950 text-white border-b-2 border-black">
                            <td class="px-3 py-3.5 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-amber-900 text-white text-xs font-black border-2 border-amber-400">5.1</span>
                            </td>
                            <td class="px-3 py-3.5 font-mono font-black text-amber-400 text-sm">5.1.01+</td>
                            <td colspan="4" class="px-4 py-3.5">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-list-check text-amber-400 text-base"></i>
                                    <span class="font-black uppercase tracking-wide text-xs text-white">Kelompok: Belanja Operasional Non-Gaji</span>
                                    <span class="pro-badge bg-amber-400 text-black border border-black ml-2">
                                        <i class="fas fa-pen-to-square text-[8px]"></i> Editable
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-right font-black text-amber-400 num-col text-xs whitespace-nowrap" id="groupOpsMonthly">Rp&nbsp;{{ number_format($totalOpsMonthly, 0, ',', '.') }}</td>
                            <td class="px-4 py-3.5 text-right font-black text-white text-sm num-col whitespace-nowrap" id="groupOpsPeriod">Rp&nbsp;{{ number_format($totalOpsPeriod, 0, ',', '.') }}</td>
                        </tr>

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
                                <tr class="hover:bg-amber-200 transition-all {{ $opsNo % 2 === 0 ? 'bg-amber-100' : 'bg-white' }} border-b border-slate-400">
                                    <td class="px-3 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-black text-white text-xs font-black">{{ $opsNo++ }}</span>
                                    </td>
                                    <td class="px-3 py-4">
                                        <span class="font-mono font-black text-black bg-amber-400 px-2.5 py-1 rounded-md border-2 border-black text-xs">{{ $code }}</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-black text-amber-400 font-black flex items-center justify-center border border-black">
                                                <i class="fas {{ $detail['icon'] }} text-sm"></i>
                                            </div>
                                            <span class="font-black text-black text-xs">{{ $detail['name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-2 py-4 text-center">
                                        <input type="number" name="expense_details[{{ $code }}][volume]" value="{{ $vol }}" min="1" step="1"
                                               oninput="updateRowCalc('{{ $safeCode }}')" id="vol_{{ $safeCode }}"
                                               class="rapby-input-pro w-full text-xs font-black text-center py-2 rounded-xl bg-white" placeholder="1">
                                    </td>
                                    <td class="px-2 py-4 text-center">
                                        <input type="text" name="expense_details[{{ $code }}][unit]" value="{{ $unit }}" id="unit_{{ $safeCode }}"
                                               class="rapby-input-pro w-full text-xs font-black text-center py-2 rounded-xl bg-white" placeholder="Bulan">
                                    </td>
                                    <td class="px-3 py-4 text-right">
                                        <div class="relative">
                                            <span class="absolute left-3 top-2.5 text-xs font-black text-black whitespace-nowrap">Rp</span>
                                            <input type="number" name="expense_details[{{ $code }}][tariff]" value="{{ $tariff > 0 ? $tariff : '' }}"
                                                   step="5000" min="0" oninput="updateRowCalc('{{ $safeCode }}')" id="tariff_{{ $safeCode }}"
                                                   class="rapby-input-pro w-full text-xs font-black text-right pl-9 pr-3 py-2 rounded-xl bg-white num-col" placeholder="0">
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-right font-black text-black num-col text-xs whitespace-nowrap" id="monthly_{{ $safeCode }}">Rp&nbsp;{{ number_format($amtMonthly, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-right font-black text-black num-col text-xs whitespace-nowrap" id="period_{{ $safeCode }}">Rp&nbsp;{{ number_format($amtPeriod, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                        @endforeach

                        {{-- Subtotal Ops --}}
                        <tr class="bg-amber-300 border-y-2 border-black">
                            <td colspan="6" class="px-4 py-4 text-right font-black text-black uppercase text-xs tracking-wider">
                                <i class="fas fa-sigma mr-1.5 text-black text-sm"></i> Subtotal Belanja Operasional (5.1.01–14):
                            </td>
                            <td class="px-4 py-4 text-right font-black text-black text-xs num-col whitespace-nowrap" id="subtotalOpsMonthly">Rp&nbsp;{{ number_format($totalOpsMonthly, 0, ',', '.') }}</td>
                            <td class="px-4 py-4 text-right font-black text-black text-sm num-col whitespace-nowrap" id="subtotalOpsPeriod">Rp&nbsp;{{ number_format($totalOpsPeriod, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-black text-white border-t-4 border-black">
                            <td colspan="6" class="px-6 py-5 text-right uppercase tracking-widest font-black text-xs text-amber-400">
                                Grand Total Rencana Belanja RAPBY:
                            </td>
                            <td class="px-4 py-5 text-right num-col whitespace-nowrap">
                                <span class="text-amber-300 font-black text-sm whitespace-nowrap" id="footTotalMonthly">Rp&nbsp;{{ number_format($grandTotalBelanjaMonthly, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-4 py-5 text-right num-col whitespace-nowrap">
                                <span class="text-emerald-400 font-black text-2xl whitespace-nowrap" id="footTotalPeriod">Rp&nbsp;{{ number_format($grandTotalBelanjaPeriod, 0, ',', '.') }}</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Footer Notes & Action --}}
            <div class="p-6 bg-slate-300 border-t-2 border-black flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="w-full md:w-2/3">
                    <label class="block text-xs font-black text-black mb-1.5 uppercase tracking-wider">
                        <i class="fas fa-comment-dots mr-1 text-black"></i> Catatan Rencana Anggaran Belanja (Opsional)
                    </label>
                    <input type="text" name="notes" value="{{ $contribution->notes ?? '' }}"
                           class="rapby-input-pro w-full text-xs p-3.5 rounded-2xl bg-white text-black font-black border-2 border-black" placeholder="Tambahkan catatan persetujuan rencana anggaran belanja...">
                </div>
                <button type="submit" class="px-8 py-4 bg-amber-400 hover:bg-amber-300 text-black font-black text-xs rounded-2xl shadow-2xl transition-all flex items-center gap-2.5 border-2 border-black">
                    <i class="fas fa-save text-base"></i> Simpan Rencana Belanja
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const totalGajiMonthly = {{ $totalGajiPerguruanMonthly }};
    const multiplier = {{ $multiplier }};

    function updateRowCalc(safeCode) {
        const vol = parseFloat(document.getElementById('vol_' + safeCode)?.value) || 0;
        const tariff = parseFloat(document.getElementById('tariff_' + safeCode)?.value) || 0;
        const monthly = vol * tariff;
        const period = monthly * multiplier;
        const fmt = (n) => 'Rp\u00A0' + n.toLocaleString('id-ID');

        document.getElementById('monthly_' + safeCode).innerText = fmt(monthly);
        document.getElementById('period_' + safeCode).innerText = fmt(period);

        let sumOps = 0;
        document.querySelectorAll('input[id^="tariff_"]').forEach(t => {
            const k = t.id.replace('tariff_', '');
            const v = parseFloat(document.getElementById('vol_' + k)?.value) || 0;
            sumOps += v * (parseFloat(t.value) || 0);
        });

        const grand = totalGajiMonthly + sumOps;
        document.getElementById('groupOpsMonthly').innerText = fmt(sumOps);
        document.getElementById('groupOpsPeriod').innerText = fmt(sumOps * multiplier);
        document.getElementById('subtotalOpsMonthly').innerText = fmt(sumOps);
        document.getElementById('subtotalOpsPeriod').innerText = fmt(sumOps * multiplier);
        document.getElementById('cardOpsTotal').innerText = fmt(sumOps * multiplier);
        document.getElementById('cardGrandTotal').innerText = fmt(grand * multiplier);
        document.getElementById('footTotalMonthly').innerText = fmt(grand);
        document.getElementById('footTotalPeriod').innerText = fmt(grand * multiplier);
    }
</script>
@endpush
@endsection
