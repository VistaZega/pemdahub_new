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
                Laporan konsolidasi eksekutif perbandingan Rencana Pendapatan SPP (Halaman 1) dan Rencana Belanja Perguruan (Halaman 2).
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

    <!-- Executive KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card 1: Total Pendapatan SPP (Halaman 1) -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-extrabold text-emerald-700 uppercase tracking-wider">1. Total Pendapatan SPP</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <i class="fas fa-hand-holding-dollar text-sm"></i>
                </div>
            </div>
            <span class="text-2xl font-black text-emerald-900 block">Rp {{ number_format($grandTotalIncome, 0, ',', '.') }}</span>
            <p class="text-[11px] text-gray-500 mt-1">Ditarik dari Halaman 1 (Kontribusi SPP)</p>
        </div>

        <!-- Card 2: Total Belanja Perguruan (Halaman 2) -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-extrabold text-red-700 uppercase tracking-wider">2. Total Rencana Belanja</span>
                <div class="w-9 h-9 rounded-xl bg-red-50 text-red-600 flex items-center justify-center font-bold">
                    <i class="fas fa-calculator text-sm"></i>
                </div>
            </div>
            <span class="text-2xl font-black text-red-900 block">Rp {{ number_format($grandTotalBelanjaPeriod, 0, ',', '.') }}</span>
            <p class="text-[11px] text-gray-500 mt-1">Ditarik dari Halaman 2 (Pegawai + Operasional)</p>
        </div>

        <!-- Card 3: Saldo Bersih Akhir -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-extrabold text-violet-700 uppercase tracking-wider">3. Saldo Bersih Akhir</span>
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

    <!-- SEKSI REKAPITULASI KONSOLIDASI KEUANGAN -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-md overflow-hidden">
        <div class="p-6 bg-gradient-to-r from-violet-900 to-indigo-900 text-white flex items-center justify-between">
            <div>
                <h3 class="text-base font-extrabold flex items-center gap-2">
                    <i class="fas fa-file-contract text-amber-400"></i> Matriks Konsolidasi Pendapatan & Belanja Perguruan
                </h3>
                <p class="text-xs text-violet-200 mt-0.5">Ringkasan perbandingan total sumber penerimaan dan belanja lembaga.</p>
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
                        <th class="px-4 py-3 text-center w-36">Sumber Data</th>
                        <th class="px-4 py-3 text-right w-64">Nominal Periode ({{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }})</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr class="bg-emerald-50/30">
                        <td class="px-4 py-3.5 font-bold text-emerald-900">
                            1. TOTAL PENDAPATAN SPP SISWA (SELURUH UNIT SEKOLAH)
                        </td>
                        <td class="px-4 py-3.5 text-center font-semibold text-emerald-700">Halaman 1</td>
                        <td class="px-4 py-3.5 text-right font-black text-emerald-700 text-sm">Rp {{ number_format($grandTotalIncome, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3.5 pl-8 text-gray-700">
                            a. Belanja Pegawai Perguruan (Gaji Guru & Staf Sekolah + Yayasan)
                        </td>
                        <td class="px-4 py-3.5 text-center text-gray-500">Halaman 2 (A)</td>
                        <td class="px-4 py-3.5 text-right font-bold text-blue-700">Rp {{ number_format($totalGajiLembagaPeriod, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3.5 pl-8 text-gray-700">
                            b. Belanja Operasional Non-Gaji (Kode Rekening 5.1.01 - 5.1.14)
                        </td>
                        <td class="px-4 py-3.5 text-center text-gray-500">Halaman 2 (B)</td>
                        <td class="px-4 py-3.5 text-right font-bold text-amber-700">Rp {{ number_format($totalBelanjaOpsPeriod, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="bg-red-50/60 font-bold">
                        <td class="px-4 py-3.5 font-extrabold text-red-900">
                            2. TOTAL RENCANA BELANJA PERGURUAN (a + b)
                        </td>
                        <td class="px-4 py-3.5 text-center text-red-700">Halaman 2</td>
                        <td class="px-4 py-3.5 text-right font-black text-red-700 text-sm">(Rp {{ number_format($grandTotalBelanjaPeriod, 0, ',', '.') }})</td>
                    </tr>
                </tbody>
                <tfoot class="bg-violet-900 text-white font-extrabold border-t-2 border-violet-900 text-sm">
                    <tr>
                        <td colspan="2" class="px-4 py-4 uppercase tracking-wider">SALDO BERSIH AKHIR PERGURUAN (1 - 2):</td>
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
