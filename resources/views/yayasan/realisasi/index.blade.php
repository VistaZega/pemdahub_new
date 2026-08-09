@extends('layouts.yayasan')

@section('title', 'Realisasi Anggaran Belanja Yayasan')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700;800;900&display=swap" rel="stylesheet">

<style>
    .ui-ux-promax {
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #000000;
    }

    .contrib-hero {
        background-color: #090d16;
        border: 2px solid #000000;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
    }

    .contrib-card-pro {
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
    .stat-card-pro.red::before { background-color: #dc2626; }
    .stat-card-pro.blue::before { background-color: #1d4ed8; }

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

    <!-- 1. Header Banner & Filter -->
    <div class="contrib-hero rounded-3xl p-6 md:p-8 text-white relative overflow-hidden">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black shadow-lg border-2 border-black shrink-0" style="background-color: #fbbf24 !important; color: #000000 !important;">
                        <i class="fas fa-receipt text-2xl text-black"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Realisasi Anggaran Belanja Yayasan</h1>
                        </div>
                        <p class="text-amber-400 text-sm font-black tracking-wide">Pencapaian Realisasi Penerimaan SPP & Belanja Aktual</p>
                    </div>
                </div>
                <p class="text-white text-xs font-black max-w-xl leading-relaxed">
                    Menampilkan data perbandingan pendapatan SPP estimasi/aktual terhadap belanja operasional dan gaji pegawai aktual.
                </p>
            </div>
            
            <!-- Actions & Filters -->
            <form method="GET" action="{{ route('yayasan.realisasi.index') }}" class="flex flex-wrap items-center gap-3">
                <div class="flex flex-col">
                    <span class="text-[11px] uppercase tracking-wider font-black text-amber-400 mb-1">Tahun Pelajaran</span>
                    <select name="academic_year_id" onchange="this.form.submit()" class="bg-black text-white border-2 border-amber-400 rounded-xl text-xs px-3.5 py-2.5 font-black focus:ring-2 focus:ring-amber-400 min-w-[170px]">
                        @foreach($academicYears as $y)
                            <option value="{{ $y->id }}" class="bg-black text-white font-black" {{ ($activeYear->id ?? null) == $y->id ? 'selected' : '' }}>
                                TP {{ $y->year }} {{ $y->is_active ? '✦ Aktif' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col">
                    <span class="text-[11px] uppercase tracking-wider font-black text-amber-400 mb-1">Mode Periode</span>
                    <div class="bg-black p-1 rounded-xl border-2 border-slate-700 flex items-center gap-1">
                        <a href="{{ route('yayasan.realisasi.index', ['academic_year_id' => $activeYear->id ?? null, 'period_mode' => 'annual']) }}"
                           class="px-4 py-2 rounded-lg text-xs font-black transition-all" style="{{ $periodMode === 'annual' ? 'background-color: #fbbf24 !important; color: #000000 !important; border: 2px solid #000000;' : 'color: #ffffff;' }}">
                            <i class="fas fa-calendar-days mr-1.5"></i> 12 Bulan
                        </a>
                        <a href="{{ route('yayasan.realisasi.index', ['academic_year_id' => $activeYear->id ?? null, 'period_mode' => 'monthly']) }}"
                           class="px-4 py-2 rounded-lg text-xs font-black transition-all" style="{{ $periodMode === 'monthly' ? 'background-color: #fbbf24 !important; color: #000000 !important; border: 2px solid #000000;' : 'color: #ffffff;' }}">
                            <i class="fas fa-calendar-day mr-1.5"></i> 1 Bulan
                        </a>
                    </div>
                </div>

                <div class="flex flex-col justify-end pt-5">
                    <a href="{{ route('yayasan.realisasi.export_pdf', ['academic_year_id' => $activeYear->id ?? null, 'period_mode' => $periodMode]) }}" 
                       target="_blank"
                       class="px-4 py-2.5 text-white font-black text-xs rounded-xl shadow-lg transition flex items-center gap-2 border-2 border-black" style="background-color: #059669 !important;">
                        <i class="fas fa-file-pdf text-sm text-white"></i> Export PDF Laporan
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <!-- Card 1: Total Pendapatan SPP -->
        <div class="stat-card-pro green contrib-card-pro rounded-2xl p-5 shadow-md">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-black border-2 border-black shrink-0" style="background-color: #059669 !important; color: #ffffff !important;">
                    <i class="fas fa-wallet text-2xl text-white"></i>
                </div>
                <div>
                    <p class="text-xs text-black font-black uppercase tracking-wider">Realisasi Pendapatan</p>
                    <p class="text-2xl font-black text-black mt-0.5 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($grandTotalIncome, 0, ',', '.') }}</p>
                    <span class="pro-badge border-2 border-black mt-1" style="background-color: #059669 !important; color: #ffffff !important;">
                        <i class="fas fa-arrow-down text-[10px] text-white"></i> Penerimaan SPP
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
                    <p class="text-xs text-black font-black uppercase tracking-wider">Realisasi Belanja</p>
                    <p class="text-2xl font-black text-black mt-0.5 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($grandTotalExpense, 0, ',', '.') }}</p>
                    <span class="pro-badge border-2 border-black mt-1" style="background-color: #dc2626 !important; color: #ffffff !important;">
                        <i class="fas fa-arrow-up text-[10px] text-white"></i> Gaji & Operasional
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 3: Saldo Realisasi -->
        <div class="stat-card-pro blue contrib-card-pro rounded-2xl p-5 shadow-md">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-black border-2 border-black shrink-0" style="background-color: #1d4ed8 !important; color: #ffffff !important;">
                    <i class="fas fa-scale-balanced text-2xl text-white"></i>
                </div>
                <div>
                    <p class="text-xs text-black font-black uppercase tracking-wider">Saldo Realisasi</p>
                    <p class="text-2xl font-black text-black mt-0.5 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($grandTotalBalance, 0, ',', '.') }}</p>
                    <span class="pro-badge border-2 border-black mt-1" style="background-color: #1d4ed8 !important; color: #ffffff !important;">
                        <i class="fas fa-check-double text-[10px] text-white"></i> Surplus / Defisit
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Per-school income table (grade level breakdown) -->
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-black text-black flex items-center gap-2">
                <i class="fas fa-building-columns text-black"></i> Rincian Realisasi Pendapatan per Unit Sekolah
            </h2>
            <span class="pro-badge border-2 border-black" style="background-color: #000000 !important; color: #ffffff !important;">
                TP {{ $activeYear->year ?? '-' }}
            </span>
        </div>

        @foreach($incomeData as $schoolId => $item)
            @php
                $s = $item['school'];
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
                                Rp&nbsp;{{ number_format($item['income_total'], 0, ',', '.') }}
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
                                <th class="px-4 py-3.5 text-right w-48 text-xs uppercase font-black text-white whitespace-nowrap">Pendapatan Per Bulan</th>
                                <th class="px-4 py-3.5 text-right w-52 text-xs uppercase font-black text-white whitespace-nowrap">Subtotal ({{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }})</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-slate-300 bg-white">
                            @forelse($item['levels'] as $lvl)
                                <tr class="hover:bg-amber-100 transition-all border-b border-slate-300">
                                    <td class="px-4 py-3.5 font-black text-black">Kelas {{ $lvl['level'] }}</td>
                                    <td class="px-4 py-3.5 text-center font-black text-black num-col">{{ $lvl['student_count'] }} Siswa</td>
                                    <td class="px-4 py-3.5 text-right font-black text-black num-col whitespace-nowrap">Rp&nbsp;{{ number_format($lvl['spp_monthly'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3.5 text-right font-black text-black num-col whitespace-nowrap">Rp&nbsp;{{ number_format($lvl['income_monthly'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3.5 text-right font-black text-black num-col whitespace-nowrap">Rp&nbsp;{{ number_format($lvl['income_total'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-4 text-center font-black text-black italic">Belum ada tingkat kelas/siswa terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-black font-black" style="background-color: #a7f3d0 !important;">
                                <td class="px-4 py-4 text-black uppercase font-black">TOTAL PENDAPATAN {{ strtoupper($s->name) }}</td>
                                <td class="px-4 py-4 text-center text-black font-black">{{ $item['total_students'] }} Siswa</td>
                                <td class="px-4 py-4 text-right text-black font-black">-</td>
                                <td class="px-4 py-4 text-right text-black font-black num-col whitespace-nowrap">Rp&nbsp;{{ number_format($item['income_monthly'], 0, ',', '.') }}</td>
                                <td class="px-4 py-4 text-right text-black font-black text-sm num-col whitespace-nowrap">Rp&nbsp;{{ number_format($item['income_total'], 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    <!-- 4. Per-school expense recap table (Operational breakdown) -->
    <div class="contrib-card-pro rounded-3xl shadow-2xl overflow-hidden border-2 border-black mt-8">
        <div class="contrib-hero p-5 flex items-center justify-between border-b-2 border-black">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shadow-md border-2 border-black shrink-0" style="background-color: #ef4444 !important; color: #ffffff !important;">
                    <i class="fas fa-file-invoice-dollar text-xl text-white"></i>
                </div>
                <div>
                    <h2 class="text-lg font-black text-white">Rincian Realisasi Belanja per Unit Sekolah</h2>
                    <p class="text-rose-300 text-xs font-black">Gaji Pegawai & Operasional Aktual</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-black text-white border-b-2 border-black">
                        <th class="px-4 py-4 text-xs uppercase font-black text-white">Unit Sekolah</th>
                        <th class="px-4 py-4 text-right text-xs uppercase font-black text-white">Honor & Tunjangan</th>
                        <th class="px-4 py-4 text-right text-xs uppercase font-black text-white">Belanja Operasional</th>
                        <th class="px-4 py-4 text-right text-xs uppercase font-black text-white bg-rose-950">Total Belanja</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-slate-300 bg-white">
                    @foreach($expenseData as $idx => $row)
                        <tr class="hover:bg-amber-100 transition-all border-b border-slate-300">
                            <td class="px-4 py-4 font-black text-black">
                                <div class="flex flex-col">
                                    <span>{{ $row['school']->name }}</span>
                                    <span class="text-[10px] text-gray-500">{{ $row['school']->type }} | {{ $row['emp_count'] }} Pegawai</span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-right font-black text-black num-col whitespace-nowrap">Rp&nbsp;{{ number_format($row['salary_period'], 0, ',', '.') }}</td>
                            <td class="px-4 py-4 text-right font-black text-black num-col whitespace-nowrap">Rp&nbsp;{{ number_format($row['ops_period'], 0, ',', '.') }}</td>
                            <td class="px-4 py-4 text-right font-black text-rose-700 bg-rose-50 num-col whitespace-nowrap text-sm">Rp&nbsp;{{ number_format($row['total_expense'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- 5. Grand total recap table -->
    <div class="contrib-card-pro rounded-3xl shadow-2xl overflow-hidden border-2 border-black mt-8">
        <div class="contrib-hero p-5 flex items-center justify-between border-b-2 border-black">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shadow-md border-2 border-black shrink-0" style="background-color: #fbbf24 !important; color: #000000 !important;">
                    <i class="fas fa-table-list text-xl text-black"></i>
                </div>
                <div>
                    <h2 class="text-lg font-black text-white">Matriks Rekapitulasi Realisasi Seluruh Unit Sekolah</h2>
                    <p class="text-amber-400 text-xs font-black">Rangkuman Pendapatan, Belanja, dan Saldo Realisasi</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-black text-white border-b-2 border-black">
                        <th class="px-4 py-4 text-xs uppercase font-black text-white">Nama Unit Sekolah</th>
                        <th class="px-4 py-4 text-center w-24 text-xs uppercase font-black text-white">Siswa Aktif</th>
                        <th class="px-4 py-4 text-right text-xs uppercase font-black text-white bg-emerald-950">A. Realisasi SPP</th>
                        <th class="px-4 py-4 text-right text-xs uppercase font-black text-white bg-rose-950">B. Total Belanja</th>
                        <th class="px-4 py-4 text-right text-xs uppercase font-black text-white bg-blue-950">C. Saldo Realisasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-slate-300 bg-white">
                    @foreach($expenseData as $idx => $row)
                        <tr class="hover:bg-amber-100 transition-all border-b border-slate-300">
                            <td class="px-4 py-4 font-black text-black">
                                <div class="flex items-center gap-2">
                                    <span>{{ $row['school']->name }}</span>
                                    <span class="pro-badge border border-black" style="background-color: #fbbf24 !important; color: #000000 !important;">
                                        {{ $row['school']->type }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center font-black text-black num-col">{{ $row['student_count'] }}</td>
                            <td class="px-4 py-4 text-right font-black text-emerald-700 bg-emerald-50 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($row['income'], 0, ',', '.') }}</td>
                            <td class="px-4 py-4 text-right font-black text-rose-700 bg-rose-50 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($row['total_expense'], 0, ',', '.') }}</td>
                            <td class="px-4 py-4 text-right font-black {{ $row['balance'] >= 0 ? 'text-blue-700 bg-blue-50' : 'text-rose-700 bg-rose-50' }} num-col whitespace-nowrap text-sm">Rp&nbsp;{{ number_format($row['balance'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-black text-white font-black border-t-4 border-black text-sm">
                        <td class="px-6 py-5 text-right uppercase tracking-widest font-black text-amber-400">GRAND TOTAL:</td>
                        <td class="px-4 py-5 text-center text-white font-black">{{ $totalStudentsAll }}</td>
                        <td class="px-4 py-5 text-right num-col whitespace-nowrap text-emerald-400 font-black">Rp&nbsp;{{ number_format($grandTotalIncome, 0, ',', '.') }}</td>
                        <td class="px-4 py-5 text-right num-col whitespace-nowrap text-rose-400 font-black">Rp&nbsp;{{ number_format($grandTotalExpense, 0, ',', '.') }}</td>
                        <td class="px-4 py-5 text-right num-col whitespace-nowrap text-blue-400 font-black text-xl">Rp&nbsp;{{ number_format($grandTotalBalance, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection
