@extends('layouts.yayasan')

@section('title', 'Rencana Belanja Operasional & Pegawai Yayasan')

@section('content')
<div class="space-y-6">

    <!-- Header & Filter Bar -->
    <div class="bg-gradient-to-r from-violet-900 via-purple-900 to-indigo-900 rounded-2xl p-6 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black flex items-center gap-2.5">
                <i class="fas fa-list-check text-amber-400"></i> Rencana Belanja Yayasan (RAPBY)
            </h1>
            <p class="text-xs text-violet-200 mt-1">
                Penyusunan anggaran rencana belanja pegawai (gaji) dan belanja operasional non-gaji terpusat per kode rekening.
            </p>
        </div>

        <form method="GET" action="{{ route('yayasan.operational_expenses.index') }}" class="flex flex-wrap items-center gap-3">
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
                <a href="{{ route('yayasan.operational_expenses.index', ['academic_year_id' => $currentYear->id, 'period_mode' => 'annual']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $periodMode === 'annual' ? 'bg-amber-400 text-amber-950 shadow' : 'text-white hover:bg-white/10' }}">
                    12 Bulan
                </a>
                <a href="{{ route('yayasan.operational_expenses.index', ['academic_year_id' => $currentYear->id, 'period_mode' => 'monthly']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $periodMode === 'monthly' ? 'bg-amber-400 text-amber-950 shadow' : 'text-white hover:bg-white/10' }}">
                    1 Bulan
                </a>
            </div>

            <a href="{{ route('yayasan.operational_expenses.export_pdf', ['academic_year_id' => $currentYear->id, 'period_mode' => $periodMode]) }}" 
               target="_blank"
               class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                <i class="fas fa-file-pdf"></i> Export PDF Rencana Belanja
            </a>
        </form>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-bold flex items-center gap-2">
            <i class="fas fa-check-circle text-emerald-600"></i> {{ session('success') }}
        </div>
    @endif

    <!-- Alert / Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card 1: Total Belanja Pegawai -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xl">
                <i class="fas fa-users-gear"></i>
            </div>
            <div>
                <span class="text-xs text-gray-500 font-medium block">A. Total Belanja Pegawai (Gaji)</span>
                <span class="text-xl font-black text-blue-900">Rp {{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}</span>
                <span class="text-[10px] text-gray-400 block">Guru Sekolah + Staf Yayasan</span>
            </div>
        </div>

        <!-- Card 2: Total Belanja Operasional -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xl">
                <i class="fas fa-list-check"></i>
            </div>
            <div>
                <span class="text-xs text-gray-500 font-medium block">B. Belanja Operasional Non-Gaji</span>
                <span class="text-xl font-black text-amber-800">Rp {{ number_format($totalOpsPeriod, 0, ',', '.') }}</span>
                <span class="text-[10px] text-gray-400 block">Akumulasi 14 Kode Rekening</span>
            </div>
        </div>

        <!-- Card 3: Grand Total Belanja Perguruan -->
        <div class="bg-white rounded-2xl p-5 border border-violet-200 shadow-sm bg-gradient-to-br from-violet-50/50 to-purple-50/50 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-violet-700 text-white flex items-center justify-center font-bold text-xl shadow">
                <i class="fas fa-calculator"></i>
            </div>
            <div>
                <span class="text-xs text-violet-800 font-extrabold uppercase block">GRAND TOTAL BELANJA (A + B)</span>
                <span class="text-2xl font-black text-violet-950">Rp {{ number_format($grandTotalBelanjaPeriod, 0, ',', '.') }}</span>
                <span class="text-[10px] text-violet-600 font-semibold block">Total Pengeluaran Perguruan</span>
            </div>
        </div>
    </div>

    <!-- SEKSI A: KELOLA BELANJA PEGAWAI PERGURUAN -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-md overflow-hidden">
        <div class="p-6 bg-gradient-to-r from-blue-900 to-indigo-900 text-white flex items-center justify-between">
            <div>
                <h2 class="text-base font-extrabold flex items-center gap-2">
                    <i class="fas fa-id-card-clip text-blue-300"></i> A. Rencana Belanja Pegawai Perguruan (Gaji & Tunjangan)
                </h2>
                <p class="text-xs text-blue-200 mt-0.5">Ditarik otomatis dari sistem Payroll SDM untuk seluruh unit sekolah & staf yayasan.</p>
            </div>
            <span class="px-3 py-1.5 rounded-full bg-blue-800/80 text-blue-100 text-xs font-bold border border-blue-400/30">
                Total Gaji: Rp {{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-100 text-gray-700 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">No</th>
                        <th class="px-4 py-3">Unit / Lembaga</th>
                        <th class="px-4 py-3 text-center">Jumlah Pegawai</th>
                        <th class="px-4 py-3 text-right">Gaji Per Bulan</th>
                        <th class="px-4 py-3 text-right">Subtotal Gaji ({{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }})</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($salaryBreakdown as $idx => $sRow)
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="px-4 py-3 text-center font-bold text-gray-400">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3 font-bold text-gray-900 flex items-center gap-2">
                                {{ $sRow['school']->name }}
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600">
                                    {{ $sRow['school']->type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-gray-700">{{ $sRow['employee_count'] }} orang</td>
                            <td class="px-4 py-3 text-right font-bold text-gray-800">Rp {{ number_format($sRow['salary_monthly'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-black text-blue-700">Rp {{ number_format($sRow['salary_period'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-blue-900 text-white font-bold border-t-2 border-blue-900">
                    <tr>
                        <td colspan="3" class="px-4 py-3.5 text-right uppercase tracking-wider font-extrabold">SUBTOTAL A (BELANJA PEGAWAI PERGURUAN):</td>
                        <td class="px-4 py-3.5 text-right text-blue-200">Rp {{ number_format($totalGajiPerguruanMonthly, 0, ',', '.') }}</td>
                        <td class="px-4 py-3.5 text-right text-base font-black text-blue-200">Rp {{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- SEKSI B: FORM ENTRY BELANJA OPERASIONAL NON-GAJI -->
    <form method="POST" action="{{ route('yayasan.operational_expenses.store') }}">
        @csrf
        <input type="hidden" name="academic_year_id" value="{{ $currentYear->id }}">

        <div class="bg-white rounded-2xl border border-gray-200 shadow-md overflow-hidden">
            <div class="p-6 bg-gradient-to-r from-amber-900 to-orange-900 text-white flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-extrabold flex items-center gap-2">
                        <i class="fas fa-list-check text-amber-300"></i> B. Rencana Belanja Operasional Non-Gaji per Kode Rekening
                    </h2>
                    <p class="text-xs text-amber-200 mt-0.5">Input anggaran operasional terpusat per bulan (Internet, Listrik, Air, Sarpras, ATK, dll).</p>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-amber-950 font-black text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                        <i class="fas fa-save"></i> Simpan Belanja Operasional
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-gray-100 text-gray-700 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3 w-32">Kode Rekening</th>
                            <th class="px-4 py-3">Nama Rekening Belanja Operasional</th>
                            <th class="px-4 py-3 w-40">Kategori</th>
                            <th class="px-4 py-3 text-right w-56">Anggaran Per Bulan (Rp)</th>
                            <th class="px-4 py-3 text-right w-56">Anggaran Per Tahun (12 Bln)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php $no = 1; @endphp
                        @foreach($expenseAccounts as $code => $acc)
                            @php
                                $valMonthly = isset($savedExpenseDetails[$code]) ? (float)$savedExpenseDetails[$code] : 0;
                                $valAnnual = $valMonthly * 12;
                            @endphp
                            <tr class="hover:bg-amber-50/30 transition">
                                <td class="px-4 py-3 text-center font-bold text-gray-400">{{ $no++ }}</td>
                                <td class="px-4 py-3 font-mono font-bold text-amber-800 bg-amber-50/50 rounded-lg text-center">
                                    {{ $code }}
                                </td>
                                <td class="px-4 py-3 font-bold text-gray-900">
                                    <div class="flex items-center gap-2">
                                        <i class="fas {{ $acc['icon'] }} text-gray-400 w-4"></i>
                                        <span>{{ $acc['name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                                        {{ $acc['category'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-xs font-bold text-gray-400">Rp</span>
                                        <input type="number" name="expense_details[{{ $code }}]" 
                                               value="{{ $valMonthly > 0 ? $valMonthly : '' }}" 
                                               step="5000" min="0" 
                                               oninput="updateRowCalc('{{ str_replace('.', '_', $code) }}', this.value)"
                                               id="input_{{ str_replace('.', '_', $code) }}"
                                               class="w-full text-xs font-bold text-right pl-9 pr-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 tabular-nums" 
                                               placeholder="0">
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-gray-800 tabular-nums" id="annual_{{ str_replace('.', '_', $code) }}">
                                    Rp {{ number_format($valAnnual, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-amber-900 text-white font-bold border-t-2 border-amber-900">
                        <tr>
                            <td colspan="4" class="px-4 py-3.5 text-right uppercase tracking-wider font-extrabold">SUBTOTAL B (BELANJA OPERASIONAL NON-GAJI):</td>
                            <td class="px-4 py-3.5 text-right text-amber-200 font-black" id="footTotalMonthly">
                                Rp {{ number_format($totalOpsMonthly, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right text-amber-200 font-black" id="footTotalAnnual">
                                Rp {{ number_format($totalOpsMonthly * 12, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Notes & Grand Total Footer -->
            <div class="p-6 bg-gray-50 border-t border-gray-200 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="w-full md:w-2/3">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Catatan Rencana Anggaran Belanja (Opsional)</label>
                    <input type="text" name="notes" value="{{ $contribution->notes ?? '' }}" 
                           class="w-full text-xs p-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500" 
                           placeholder="Catatan tambahan mengenai persetujuan anggaran belanja...">
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-amber-950 font-black text-xs rounded-xl shadow-md transition flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Rencana Belanja
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- SEKSI C: REKAPITULASI GRAND TOTAL RENCANA BELANJA -->
    <div class="bg-violet-900 rounded-2xl p-6 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-4 border border-violet-700">
        <div>
            <span class="text-xs font-extrabold text-amber-300 uppercase tracking-wider block">GRAND TOTAL RENCANA BELANJA YAYASAN & PERGURUAN</span>
            <h3 class="text-2xl font-black mt-0.5">Penjumlahan Belanja Pegawai (A) + Belanja Operasional (B)</h3>
        </div>
        <div class="text-right">
            <span class="text-3xl font-black text-amber-400 block" id="grandTotalDisplay">
                Rp {{ number_format($grandTotalBelanjaPeriod, 0, ',', '.') }}
            </span>
            <span class="text-xs text-violet-200">Total Pengeluaran ({{ $periodMode === 'annual' ? '12 Bulan / Full Year' : '1 Bulan' }})</span>
        </div>
    </div>

</div>

@push('scripts')
<script>
    const totalGajiMonthly = {{ $totalGajiPerguruanMonthly }};
    const multiplier = {{ $multiplier }};

    function updateRowCalc(codeSafe, val) {
        const num = parseFloat(val) || 0;
        const annual = num * 12;
        document.getElementById('annual_' + codeSafe).innerText = 'Rp ' + annual.toLocaleString('id-ID');

        // Recalculate Grand Totals
        let sumOpsMonthly = 0;
        const inputs = document.querySelectorAll('input[name^="expense_details"]');
        inputs.forEach(function(inp) {
            sumOpsMonthly += (parseFloat(inp.value) || 0);
        });

        const sumOpsAnnual = sumOpsMonthly * 12;
        document.getElementById('footTotalMonthly').innerText = 'Rp ' + sumOpsMonthly.toLocaleString('id-ID');
        document.getElementById('footTotalAnnual').innerText = 'Rp ' + sumOpsAnnual.toLocaleString('id-ID');

        const grandMonthly = totalGajiMonthly + sumOpsMonthly;
        const grandPeriod = grandMonthly * multiplier;
        document.getElementById('grandTotalDisplay').innerText = 'Rp ' + grandPeriod.toLocaleString('id-ID');
    }
</script>
@endpush
@endsection
