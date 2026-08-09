@extends('layouts.yayasan')

@section('title', 'Rencana Anggaran Belanja (RAB) Yayasan')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700;800;900&display=swap" rel="stylesheet">

<style>
    .ui-ux-promax {
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #000000;
        width: 100%;
        max-width: 100%;
        min-width: 0;
    }

    .contrib-hero {
        background-color: #090d16;
        border: 2px solid #000000;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }

    .contrib-card-pro {
        background-color: #ffffff;
        border: 2px solid #000000;
        border-radius: 1.25rem;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
        width: 100%;
        max-width: 100%;
        min-width: 0;
        box-sizing: border-box;
        overflow: hidden;
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
    .stat-card-pro.blue::before { background-color: #1d4ed8; }
    .stat-card-pro.red::before { background-color: #dc2626; }
    .stat-card-pro.violet::before { background-color: #7c3aed; }

    .rapby-input-pro {
        border: 2px solid #000000 !important;
        color: #000000 !important;
        font-weight: 900 !important;
        background-color: #ffffff !important;
        opacity: 1 !important;
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

    .tab-btn {
        border: 2px solid #000000;
        background-color: #f1f5f9;
        color: #000000;
        font-weight: 900;
        padding: 8px 16px;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .tab-btn.active {
        background-color: #fbbf24 !important;
        color: #000000 !important;
        border: 2px solid #000000;
        box-shadow: 2px 2px 0px #000000;
    }
</style>
@endpush

@section('content')
<div class="ui-ux-promax space-y-6">

    <!-- Flash Message -->
    @if(session('success'))
        <div class="p-4 rounded-2xl border-2 border-black text-black flex items-center justify-between shadow-md" style="background-color: #6ee7b7 !important;">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black border-2 border-black shrink-0" style="background-color: #000000 !important; color: #ffffff !important;">
                    <i class="fas fa-check text-white"></i>
                </div>
                <div>
                    <h4 class="font-black text-sm text-black">Berhasil!</h4>
                    <p class="text-xs font-black text-black">{{ session('success') }}</p>
                </div>
            </div>
            <button onclick="this.parentElement.remove()" class="text-black hover:text-red-600 font-black">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
    @endif

    <!-- 1. Header Banner & Filter -->
    <div class="contrib-hero rounded-3xl p-6 md:p-8 text-white relative overflow-hidden">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black shadow-lg border-2 border-black shrink-0" style="background-color: #fbbf24 !important; color: #000000 !important;">
                        <i class="fas fa-calculator text-2xl text-black"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Rencana Anggaran Belanja (RAB) Yayasan</h1>
                        </div>
                        <p class="text-amber-400 text-sm font-black tracking-wide">Pendapatan SPP, Belanja Gaji, dan Belanja Operasional Per Unit Sekolah</p>
                    </div>
                </div>
                <p class="text-white text-xs font-black max-w-xl leading-relaxed">
                    Perencanaan Rencana Anggaran Belanja (RAB) Berdasarkan Potensi SPP Siswa, Beban Gaji SDM, serta Alokasi Belanja Operasional Sekolah.
                </p>
            </div>
            
            <!-- Actions & Filters -->
            <form method="GET" action="{{ route('yayasan.rab.index') }}" class="flex flex-wrap items-center gap-3">
                <div class="flex flex-col">
                    <span class="text-[11px] uppercase tracking-wider font-black mb-1" style="color: #fbbf24 !important;">Tahun Pelajaran</span>
                    <select name="academic_year_id" onchange="this.form.submit()" class="border-2 border-black rounded-xl text-xs px-3.5 py-2 font-black shadow-sm cursor-pointer" style="background-color: #ffffff !important; color: #000000 !important;">
                        @foreach($allYears as $y)
                            <option value="{{ $y->id }}" style="background-color: #ffffff !important; color: #000000 !important;" {{ ($currentYear->id ?? null) == $y->id ? 'selected' : '' }}>
                                TP {{ $y->year }} {{ $y->is_active ? '✦ Aktif' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col">
                    <span class="text-[11px] uppercase tracking-wider font-black mb-1" style="color: #fbbf24 !important;">Mode Periode</span>
                    <div class="bg-black p-1 rounded-xl border-2 border-slate-700 flex items-center gap-1">
                        <a href="{{ route('yayasan.rab.index', ['academic_year_id' => $currentYear->id ?? null, 'period_mode' => 'annual']) }}"
                           class="px-4 py-2 rounded-lg text-xs font-black transition-all" style="{{ $periodMode === 'annual' ? 'background-color: #fbbf24 !important; color: #000000 !important; border: 2px solid #000000;' : 'color: #ffffff;' }}">
                            <i class="fas fa-calendar-days mr-1.5"></i> 12 Bulan
                        </a>
                        <a href="{{ route('yayasan.rab.index', ['academic_year_id' => $currentYear->id ?? null, 'period_mode' => 'monthly']) }}"
                           class="px-4 py-2 rounded-lg text-xs font-black transition-all" style="{{ $periodMode === 'monthly' ? 'background-color: #fbbf24 !important; color: #000000 !important; border: 2px solid #000000;' : 'color: #ffffff;' }}">
                            <i class="fas fa-calendar-day mr-1.5"></i> 1 Bulan
                        </a>
                    </div>
                </div>

                <div class="flex flex-col justify-end pt-5">
                    <a href="{{ route('yayasan.rab.export_pdf', ['academic_year_id' => $currentYear->id ?? null, 'period_mode' => $periodMode]) }}" 
                       target="_blank"
                       class="px-4 py-2.5 text-white font-black text-xs rounded-xl shadow-lg transition flex items-center gap-2 border-2 border-black" style="background-color: #ef4444 !important;">
                        <i class="fas fa-file-pdf text-sm text-white"></i> Cetak PDF RAB
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Summary Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <!-- Card 1: Total Pendapatan SPP -->
        <div class="stat-card-pro green contrib-card-pro rounded-2xl p-5 shadow-md">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-black border-2 border-black shrink-0" style="background-color: #059669 !important; color: #ffffff !important;">
                    <i class="fas fa-wallet text-2xl text-white"></i>
                </div>
                <div>
                    <p class="text-xs text-black font-black uppercase tracking-wider">Total Pendapatan SPP</p>
                    <p class="text-2xl font-black text-black mt-0.5 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($summary['total_income'], 0, ',', '.') }}</p>
                    <span class="pro-badge border-2 border-black mt-1" style="background-color: #059669 !important; color: #ffffff !important;">
                        <i class="fas fa-coins text-[10px] text-white"></i> Rencana SPP Siswa
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Belanja -->
        <div class="stat-card-pro red contrib-card-pro rounded-2xl p-5 shadow-md">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-black border-2 border-black shrink-0" style="background-color: #dc2626 !important; color: #ffffff !important;">
                    <i class="fas fa-money-bill-transfer text-2xl text-white"></i>
                </div>
                <div>
                    <p class="text-xs text-black font-black uppercase tracking-wider">Total Belanja (SDM + Ops)</p>
                    <p class="text-2xl font-black text-black mt-0.5 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($summary['total_expense'], 0, ',', '.') }}</p>
                    <span class="pro-badge border-2 border-black mt-1" style="background-color: #dc2626 !important; color: #ffffff !important;">
                        <i class="fas fa-arrow-up text-[10px] text-white"></i> Gaji & Operasional
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 3: Saldo Rencana -->
        <div class="stat-card-pro blue contrib-card-pro rounded-2xl p-5 shadow-md">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-black border-2 border-black shrink-0" style="background-color: #1d4ed8 !important; color: #ffffff !important;">
                    <i class="fas fa-scale-balanced text-2xl text-white"></i>
                </div>
                <div>
                    <p class="text-xs text-black font-black uppercase tracking-wider">Saldo Rencana Anggaran</p>
                    <p class="text-2xl font-black text-black mt-0.5 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($summary['total_balance'], 0, ',', '.') }}</p>
                    <span class="pro-badge border-2 border-black mt-1" style="background-color: {{ $summary['total_balance'] >= 0 ? '#1d4ed8' : '#dc2626' }} !important; color: #ffffff !important;">
                        <i class="fas fa-check-double text-[10px] text-white"></i> {{ $summary['total_balance'] >= 0 ? 'Surplus Rencana' : 'Defisit Rencana' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. SECTION 1: Rincian Pendapatan SPP per Unit Sekolah -->
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-black text-black flex items-center gap-2">
                <i class="fas fa-building-columns text-black"></i> Rincian Rencana Pendapatan SPP per Unit Sekolah
            </h2>
            <span class="pro-badge border-2 border-black" style="background-color: #000000 !important; color: #ffffff !important;">
                TP {{ $currentYear->year ?? '-' }} • {{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }}
            </span>
        </div>

        @foreach($incomeData as $schoolId => $item)
            @php
                $s = $item['school'];
                $c = $item['contribution'];
            @endphp
            <div class="contrib-card-pro rounded-3xl shadow-xl overflow-hidden border-2 border-black">
                <!-- Unit Header Bar -->
                <div class="p-5 border-b-2 border-black flex flex-col md:flex-row md:items-center justify-between gap-4" style="background-color: #f1f5f9 !important;">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center font-black text-sm border-2 border-black shrink-0" style="background-color: #000000 !important; color: #fbbf24 !important;">
                            <i class="fas fa-school text-amber-400 text-lg"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-black text-black">{{ $s->name }}</h3>
                                <span class="pro-badge border border-black" style="background-color: #fbbf24 !important; color: #000000 !important;">
                                    {{ strtoupper($s->type) }}
                                </span>
                            </div>
                            <p class="text-xs font-black text-black mt-0.5">
                                Total Siswa: <strong class="text-black">{{ $item['total_students'] }}</strong> Orang Siswa Terdaftar
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <span class="text-[10px] text-black font-black uppercase block tracking-wider">Total Pendapatan Unit</span>
                            <span class="text-lg font-black text-emerald-950 num-col whitespace-nowrap">
                                Rp&nbsp;{{ number_format($item['total_income_period'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Unit Content Table -->
                <div class="p-4 overflow-x-auto">
                    <table class="w-full text-xs text-left border-collapse">
                        <thead>
                            <tr class="bg-black text-white border-b-2 border-black">
                                <th class="px-4 py-3.5 text-xs uppercase font-black text-white">Tingkat Kelas</th>
                                <th class="px-4 py-3.5 text-center w-36 text-xs uppercase font-black text-white">Jumlah Siswa</th>
                                <th class="px-4 py-3.5 text-right w-44 text-xs uppercase font-black text-white whitespace-nowrap">Tarif SPP / Siswa (Bln)</th>
                                <th class="px-4 py-3.5 text-center w-48 text-xs uppercase font-black text-white">Sumber Tarif</th>
                                <th class="px-4 py-3.5 text-right w-48 text-xs uppercase font-black text-white whitespace-nowrap">Pendapatan Per Bulan</th>
                                <th class="px-4 py-3.5 text-right w-52 text-xs uppercase font-black text-white whitespace-nowrap">Subtotal ({{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }})</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-slate-300 bg-white">
                            @forelse($item['levels'] as $lvl)
                                <tr class="hover:bg-amber-100 transition-all border-b border-slate-300">
                                    <td class="px-4 py-3.5 font-black text-black">Kelas {{ $lvl['level'] }}</td>
                                    <td class="px-4 py-3.5 text-center font-black text-black num-col">{{ $lvl['student_count'] }} Siswa</td>
                                    <td class="px-4 py-3.5 text-right font-black text-black num-col whitespace-nowrap">Rp&nbsp;{{ number_format($lvl['spp_monthly_rate'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="pro-badge border border-black" style="background-color: #e2e8f0 !important; color: #000000 !important;">
                                            {{ $lvl['spp_source'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-black text-black num-col whitespace-nowrap">Rp&nbsp;{{ number_format($lvl['income_monthly'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3.5 text-right font-black text-black num-col whitespace-nowrap">Rp&nbsp;{{ number_format($lvl['income_period'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-4 text-center font-black text-black italic">Belum ada tingkat kelas/siswa terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-black font-black" style="background-color: #a7f3d0 !important;">
                                <td class="px-4 py-4 text-black uppercase font-black">TOTAL PENDAPATAN {{ strtoupper($s->name) }}</td>
                                <td class="px-4 py-4 text-center text-black font-black">{{ $item['total_students'] }} Siswa</td>
                                <td colspan="2" class="px-4 py-4 text-right text-black font-black">-</td>
                                <td class="px-4 py-4 text-right text-black font-black num-col whitespace-nowrap">Rp&nbsp;{{ number_format($item['total_income_monthly'], 0, ',', '.') }}</td>
                                <td class="px-4 py-4 text-right text-black font-black text-sm num-col whitespace-nowrap">Rp&nbsp;{{ number_format($item['total_income_period'], 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    <!-- 4. SECTION 2: Rencana Belanja Pegawai & Operasional per Unit Sekolah -->
    <div class="contrib-card-pro rounded-3xl shadow-xl overflow-hidden border-2 border-black mt-8 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b-2 border-black pb-4 mb-6">
            <div>
                <h2 class="text-lg font-black text-black flex items-center gap-2">
                    <i class="fas fa-sliders text-amber-500"></i> Rencana Belanja Operasional & Gaji per Unit Sekolah
                </h2>
                <p class="text-xs text-black font-bold mt-0.5">Alokasi Rencana Belanja SDM dan 10 Pos Rekening Operasional Sekolah</p>
            </div>
        </div>

        <!-- NAV TABS UNIT SEKOLAH -->
        <div class="flex flex-wrap items-center gap-2 border-b-2 border-black pb-3 mb-6">
            @foreach($expenseData as $schoolId => $data)
                <button type="button" 
                        onclick="switchExpenseTab({{ $schoolId }})" 
                        id="tab-btn-{{ $schoolId }}"
                        class="tab-btn {{ $loop->first ? 'active' : '' }}">
                    <i class="fas {{ $data['school']->type === 'yayasan' ? 'fa-building' : 'fa-school' }} mr-1.5"></i>
                    <span>{{ $data['school']->name }}</span>
                </button>
            @endforeach
        </div>

        <!-- TAB CONTENTS -->
        @foreach($expenseData as $schoolId => $data)
            <div id="tab-content-{{ $schoolId }}" class="expense-tab-content {{ $loop->first ? '' : 'hidden' }} space-y-4">
                <div class="p-4 rounded-2xl border-2 border-black flex flex-col md:flex-row md:items-center justify-between gap-4" style="background-color: #f1f5f9 !important;">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-black text-amber-400 flex items-center justify-center font-black border-2 border-black">
                            <i class="fas {{ $data['school']->type === 'yayasan' ? 'fa-building' : 'fa-graduation-cap' }}"></i>
                        </div>
                        <div>
                            <h4 class="font-black text-base text-black">{{ $data['school']->name }}</h4>
                            <p class="text-xs text-black font-bold">Total SDM: {{ $data['employee_count'] }} Orang | Belanja Gaji: Rp&nbsp;{{ number_format($data['total_salary_period'], 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <span class="text-xs font-black text-black uppercase block">Subtotal Belanja Operasional Unit</span>
                            <span class="text-lg font-black text-amber-700 num-col">Rp&nbsp;{{ number_format($data['total_ops_period'], 0, ',', '.') }}</span>
                        </div>

                        <button type="button" 
                                onclick="openOpsModal({{ $schoolId }}, '{{ addslashes($data['school']->name) }}', {{ json_encode($data['ops_details']) }})"
                                class="px-4 py-2.5 rounded-xl text-black border-2 border-black font-black text-xs transition flex items-center gap-2 shadow-md"
                                style="background-color: #c084fc !important;">
                            <i class="fas fa-pen-to-square text-xs text-black"></i> Edit Belanja Operasional
                        </button>
                    </div>
                </div>

                <!-- TABEL POS REKENING ANGGARAN -->
                <div class="overflow-x-auto rounded-2xl border-2 border-black">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-black text-white font-black uppercase border-b-2 border-black">
                                <th class="p-3.5 text-center w-24 text-white">Kode</th>
                                <th class="p-3.5 text-white">Pos Rekening / Rencana Belanja</th>
                                <th class="p-3.5 text-center w-24 text-white">Volume</th>
                                <th class="p-3.5 text-center w-28 text-white">Satuan</th>
                                <th class="p-3.5 text-right w-44 text-white">Tarif Satuan (Rp)</th>
                                <th class="p-3.5 text-right w-52 text-white">Total Periode (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-slate-300 bg-white font-black text-black">
                            <!-- LIST REKENING OPERASIONAL (HANYA DITAMPILKAN YANG ADA NILAI RENCANA > 0) -->
                            @forelse($data['ops_details'] as $code => $item)
                                @if(($item['amount'] ?? 0) > 0 || ($item['tariff'] ?? 0) > 0)
                                    <tr class="hover:bg-amber-100 transition-all border-b border-slate-300">
                                        <td class="p-3.5 text-center font-mono text-black">{{ $item['code'] }}</td>
                                        <td class="p-3.5">
                                            <div class="font-black text-black flex items-center gap-2">
                                                <i class="fas {{ $item['icon'] }} text-amber-600 w-4 text-center"></i>
                                                <span>{{ $item['name'] }}</span>
                                            </div>
                                            <span class="text-[10px] text-gray-600 font-bold">{{ $item['category'] }}</span>
                                        </td>
                                        <td class="p-3.5 text-center">{{ $item['volume'] }}</td>
                                        <td class="p-3.5 text-center">{{ $item['unit'] }}</td>
                                        <td class="p-3.5 text-right num-col">Rp&nbsp;{{ number_format($item['tariff'], 0, ',', '.') }}</td>
                                        <td class="p-3.5 text-right num-col font-black text-black">Rp&nbsp;{{ number_format($item['amount'] * $multiplier, 0, ',', '.') }}</td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="6" class="p-4 text-center font-black italic text-gray-500">Belum ada alokasi belanja operasional yang diinput.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- RINGKASAN REKAPITULASI BELANJA UNIT (SDM + OPERASIONAL) -->
                <div class="p-4 rounded-2xl border-2 border-black space-y-2 mt-4" style="background-color: #f8fafc !important;">
                    <div class="flex items-center justify-between text-xs font-black text-black">
                        <span class="flex items-center gap-2">
                            <i class="fas fa-users text-blue-600"></i>
                            A. Total Belanja Pegawai (Gaji & Tunjangan {{ $data['employee_count'] }} SDM):
                        </span>
                        <span class="num-col text-blue-700">Rp&nbsp;{{ number_format($data['total_salary_period'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs font-black text-black">
                        <span class="flex items-center gap-2">
                            <i class="fas fa-wallet text-amber-600"></i>
                            B. Total Belanja Operasional Unit:
                        </span>
                        <span class="num-col text-amber-700">Rp&nbsp;{{ number_format($data['total_ops_period'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm font-black text-black pt-2 border-t-2 border-black">
                        <span class="uppercase">TOTAL BELANJA {{ strtoupper($data['school']->name) }} (SDM + OPERASIONAL):</span>
                        <span class="num-col text-red-700 text-base font-black">Rp&nbsp;{{ number_format($data['grand_total_period'], 0, ',', '.') }}</span>
                    </div>
                </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- 5. SECTION 3: Matriks Rekapitulasi Konsolidasi Seluruh Unit -->
    <div class="contrib-card-pro rounded-3xl shadow-2xl overflow-hidden border-2 border-black mt-8">
        <div class="contrib-hero p-5 flex items-center justify-between border-b-2 border-black">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shadow-md border-2 border-black shrink-0" style="background-color: #fbbf24 !important; color: #000000 !important;">
                    <i class="fas fa-table-list text-xl text-black"></i>
                </div>
                <div>
                    <h2 class="text-lg font-black text-white">Matriks Rekapitulasi RAB Seluruh Unit Sekolah & Yayasan</h2>
                    <p class="text-amber-400 text-xs font-black">Perbandingan Rencana Pendapatan, Belanja, dan Saldo (Surplus/Defisit) Periode {{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }}</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-black text-white border-b-2 border-black">
                        <th class="px-4 py-4 text-center w-14 text-xs uppercase font-black text-white">No</th>
                        <th class="px-4 py-4 text-xs uppercase font-black text-white">Nama Unit Sekolah / Lembaga</th>
                        <th class="px-4 py-4 text-right text-xs uppercase font-black text-white bg-emerald-950">A. Pendapatan SPP</th>
                        <th class="px-4 py-4 text-right text-xs uppercase font-black text-white">1. Gaji Pegawai</th>
                        <th class="px-4 py-4 text-right text-xs uppercase font-black text-white">2. Operasional Unit</th>
                        <th class="px-4 py-4 text-right text-xs uppercase font-black text-white bg-rose-950">B. Total Belanja</th>
                        <th class="px-4 py-4 text-right text-xs uppercase font-black text-white bg-blue-950">C. Saldo Rencana</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-slate-300 bg-white font-black text-black">
                    @foreach($allSchools as $idx => $sch)
                        @php
                            $inc = isset($incomeData[$sch->id]) ? $incomeData[$sch->id]['total_income_period'] : 0;
                            $exp = isset($expenseData[$sch->id]) ? $expenseData[$sch->id]['grand_total_period'] : 0;
                            $sal = isset($expenseData[$sch->id]) ? $expenseData[$sch->id]['total_salary_period'] : 0;
                            $ops = isset($expenseData[$sch->id]) ? $expenseData[$sch->id]['total_ops_period'] : 0;
                            $bal = $inc - $exp;
                        @endphp
                        <tr class="hover:bg-amber-100 transition-all border-b border-slate-300">
                            <td class="px-4 py-4 text-center font-black text-black">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-black text-white text-xs font-black">{{ $idx + 1 }}</span>
                            </td>
                            <td class="px-4 py-4 font-black text-black">
                                <div class="flex items-center gap-2">
                                    <span>{{ $sch->name }}</span>
                                    <span class="pro-badge border border-black" style="background-color: #fbbf24 !important; color: #000000 !important;">
                                        {{ $sch->type }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-right font-black text-emerald-700 bg-emerald-50 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($inc, 0, ',', '.') }}</td>
                            <td class="px-4 py-4 text-right font-black text-black num-col whitespace-nowrap">Rp&nbsp;{{ number_format($sal, 0, ',', '.') }}</td>
                            <td class="px-4 py-4 text-right font-black text-amber-700 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($ops, 0, ',', '.') }}</td>
                            <td class="px-4 py-4 text-right font-black text-rose-700 bg-rose-50 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($exp, 0, ',', '.') }}</td>
                            <td class="px-4 py-4 text-right font-black {{ $bal >= 0 ? 'text-blue-700 bg-blue-50' : 'text-rose-700 bg-rose-50' }} num-col whitespace-nowrap text-sm">Rp&nbsp;{{ number_format($bal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-black text-white font-black border-t-4 border-black text-sm">
                        <td colspan="2" class="px-6 py-5 text-right uppercase tracking-widest font-black text-amber-400">GRAND TOTAL YAYASAN:</td>
                        <td class="px-4 py-5 text-right num-col whitespace-nowrap text-emerald-400 font-black">Rp&nbsp;{{ number_format($summary['total_income'], 0, ',', '.') }}</td>
                        <td class="px-4 py-5 text-right num-col whitespace-nowrap text-white font-black">Rp&nbsp;{{ number_format($summary['total_salary'], 0, ',', '.') }}</td>
                        <td class="px-4 py-5 text-right num-col whitespace-nowrap text-amber-300 font-black">Rp&nbsp;{{ number_format($summary['total_operational'], 0, ',', '.') }}</td>
                        <td class="px-4 py-5 text-right num-col whitespace-nowrap text-rose-400 font-black">Rp&nbsp;{{ number_format($summary['total_expense'], 0, ',', '.') }}</td>
                        <td class="px-4 py-5 text-right num-col whitespace-nowrap text-blue-400 font-black text-xl">Rp&nbsp;{{ number_format($summary['total_balance'], 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>

<!-- Modal Input / Edit Belanja Operasional -->
<div id="opsModal" class="fixed inset-0 z-50 bg-black/80 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-3xl w-full p-6 shadow-2xl border-2 border-black transform transition-all scale-95 opacity-0 modal-card flex flex-col my-8">
        <div class="flex items-center justify-between pb-4 border-b-2 border-black">
            <div>
                <h3 class="text-base font-black text-black" id="opsSchoolName">Edit Belanja Operasional</h3>
                <p class="text-xs font-black text-black mt-0.5">Penetapan Alokasi 10 Pos Rekening Belanja Operasional Unit</p>
            </div>
            <button onclick="closeOpsModal()" class="w-8 h-8 rounded-xl bg-black text-white hover:bg-amber-400 hover:text-black flex items-center justify-center font-black">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('yayasan.rab.store') }}" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="school_id" id="opsSchoolId">
            <input type="hidden" name="academic_year_id" value="{{ $currentYear->id ?? '' }}">

            <div class="overflow-x-auto rounded-2xl border-2 border-black">
                <table class="w-full text-xs text-left border-collapse">
                    <thead>
                        <tr class="bg-black text-white font-black uppercase border-b-2 border-black">
                            <th class="p-3">Pos Rekening / Uraian</th>
                            <th class="p-3 text-center w-24">Volume</th>
                            <th class="p-3 text-center w-28">Satuan</th>
                            <th class="p-3 text-right w-44">Tarif (Rp)</th>
                        </tr>
                    </thead>
                    <tbody id="opsItemsContainer" class="divide-y-2 divide-slate-300 bg-white font-black text-black">
                        <!-- Dynamic inputs injected via Javascript -->
                    </tbody>
                </table>
            </div>

            <div class="pt-3 border-t-2 border-black flex items-center justify-end gap-3">
                <button type="button" onclick="closeOpsModal()" class="px-5 py-2.5 text-xs font-black text-black hover:bg-slate-200 rounded-xl border-2 border-black">
                    Batal
                </button>
                <button type="submit" class="px-6 py-2.5 text-xs font-black text-black rounded-xl shadow-md transition flex items-center gap-2 border-2 border-black" style="background-color: #c084fc !important;">
                    <i class="fas fa-save text-black"></i> Simpan Belanja Operasional
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Tab Switching for Expenses
    function switchExpenseTab(schoolId) {
        document.querySelectorAll('.expense-tab-content').forEach(el => {
            el.classList.add('hidden');
        });
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('active');
        });
        
        const content = document.getElementById('tab-content-' + schoolId);
        if (content) content.classList.remove('hidden');
        
        const btn = document.getElementById('tab-btn-' + schoolId);
        if (btn) btn.classList.add('active');
    }

    // Ops Modal
    function openOpsModal(schoolId, schoolName, opsDetails) {
        document.getElementById('opsSchoolId').value = schoolId;
        document.getElementById('opsSchoolName').innerText = 'Edit Belanja Operasional — ' + schoolName;
        
        const container = document.getElementById('opsItemsContainer');
        container.innerHTML = '';

        if (opsDetails) {
            Object.entries(opsDetails).forEach(([code, detail]) => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-amber-100 transition-all border-b border-slate-300';
                tr.innerHTML = `
                    <td class="p-3">
                        <div class="font-black text-black">${detail.name}</div>
                        <div class="text-[10px] text-gray-500 font-mono">${code}</div>
                    </td>
                    <td class="p-3 text-center">
                        <input type="number" step="0.01" min="0" name="expense_details[${code}][volume]" value="${detail.volume}" 
                               class="rapby-input-pro w-20 text-center text-xs p-1.5 rounded-xl">
                    </td>
                    <td class="p-3 text-center">
                        <input type="text" name="expense_details[${code}][unit]" value="${detail.unit}" 
                               class="rapby-input-pro w-24 text-center text-xs p-1.5 rounded-xl">
                    </td>
                    <td class="p-3 text-right">
                        <div class="relative">
                            <span class="absolute left-2.5 top-2 text-xs font-black text-black">Rp</span>
                            <input type="number" name="expense_details[${code}][tariff]" value="${detail.tariff}" step="1000" min="0"
                                   class="rapby-input-pro w-36 text-right text-xs pl-8 pr-2 py-1.5 rounded-xl num-col">
                        </div>
                    </td>
                `;
                container.appendChild(tr);
            });
        }

        const modal = document.getElementById('opsModal');
        const card = modal.querySelector('.modal-card');
        modal.classList.remove('hidden');
        setTimeout(() => {
            card.classList.remove('scale-95', 'opacity-0');
            card.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeOpsModal() {
        const modal = document.getElementById('opsModal');
        const card = modal.querySelector('.modal-card');
        card.classList.remove('scale-100', 'opacity-100');
        card.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 150);
    }
</script>
@endpush
@endsection
