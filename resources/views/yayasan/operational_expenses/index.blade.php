@extends('layouts.yayasan')

@section('title', 'Rencana Belanja Yayasan')

@section('content')
<div class="space-y-6">

    <!-- Header & Filter Bar -->
    <div class="bg-gradient-to-r from-violet-900 via-purple-900 to-indigo-900 rounded-2xl p-6 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black flex items-center gap-2.5">
                <i class="fas fa-list-check text-amber-400"></i> Rencana Belanja Yayasan & Perguruan (RAPBY)
            </h1>
            <p class="text-xs text-violet-200 mt-1">
                Penyusunan rincian anggaran belanja pegawai per unit (otomatis dari penugasan) dan belanja operasional terpusat per kode rekening.
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
                <i class="fas fa-file-pdf"></i> Export PDF RAPBY
            </a>
        </form>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-bold flex items-center gap-2">
            <i class="fas fa-check-circle text-emerald-600"></i> {{ session('success') }}
        </div>
    @endif

    <!-- 3 Summary Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card 1: Total Belanja Pegawai -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xl">
                <i class="fas fa-users-gear"></i>
            </div>
            <div>
                <span class="text-xs text-gray-500 font-medium block">5.1.00 Sub-Total Belanja Pegawai</span>
                <span class="text-xl font-black text-blue-900">Rp {{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}</span>
                <span class="text-[10px] text-blue-700 font-semibold block">Rincian {{ $totalPegawaiCount }} Pegawai ({{ count($salarySubAccounts) }} Unit)</span>
            </div>
        </div>

        <!-- Card 2: Total Belanja Operasional -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xl">
                <i class="fas fa-list-check"></i>
            </div>
            <div>
                <span class="text-xs text-gray-500 font-medium block">5.1.01-14 Belanja Operasional Non-Gaji</span>
                <span class="text-xl font-black text-amber-800" id="cardOpsTotal">Rp {{ number_format($totalOpsPeriod, 0, ',', '.') }}</span>
                <span class="text-[10px] text-gray-400 block">Disesuaikan via Rincian Tarif</span>
            </div>
        </div>

        <!-- Card 3: Grand Total Belanja Perguruan -->
        <div class="bg-white rounded-2xl p-5 border border-violet-200 shadow-sm bg-gradient-to-br from-violet-50/50 to-purple-50/50 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-violet-700 text-white flex items-center justify-center font-bold text-xl shadow">
                <i class="fas fa-calculator"></i>
            </div>
            <div>
                <span class="text-xs text-violet-800 font-extrabold uppercase block">GRAND TOTAL BELANJA PERGURUAN</span>
                <span class="text-2xl font-black text-violet-950" id="cardGrandTotal">Rp {{ number_format($grandTotalBelanjaPeriod, 0, ',', '.') }}</span>
                <span class="text-[10px] text-violet-600 font-semibold block">Total Beban Perguruan</span>
            </div>
        </div>
    </div>

    <!-- FORM ENTRY & RINCIAN TABEL ANGGARAN BELANJA YAYASAN -->
    <form method="POST" action="{{ route('yayasan.operational_expenses.store') }}">
        @csrf
        <input type="hidden" name="academic_year_id" value="{{ $currentYear->id }}">

        <div class="bg-white rounded-2xl border border-gray-200 shadow-md overflow-hidden">
            <div class="p-6 bg-gradient-to-r from-violet-900 to-indigo-900 text-white flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-extrabold flex items-center gap-2">
                        <i class="fas fa-table-list text-amber-400"></i> Rincian Anggaran Belanja Yayasan & Perguruan (RAPBY)
                    </h2>
                    <p class="text-xs text-violet-200 mt-0.5">Tabel rincian sub-rekening Belanja Pegawai (otomatis) dan Belanja Operasional Non-Gaji.</p>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="px-5 py-2.5 bg-amber-400 hover:bg-amber-500 text-amber-950 font-black text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                        <i class="fas fa-save"></i> Simpan Rencana Belanja
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-gray-100 text-gray-700 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-3 py-3 text-center w-10">No</th>
                            <th class="px-3 py-3 w-28">Kode Rekening</th>
                            <th class="px-4 py-3">Nama & Rincian Rekening Belanja</th>
                            <th class="px-3 py-3 w-28 text-center">Jumlah (Qty)</th>
                            <th class="px-3 py-3 w-28 text-center">Satuan</th>
                            <th class="px-4 py-3 text-right w-44">Tarif Satuan (Rp)</th>
                            <th class="px-4 py-3 text-right w-44">Total Per Bulan</th>
                            <th class="px-4 py-3 text-right w-48 font-black">Total Periode ({{ $periodMode === 'annual' ? '12 Bln' : '1 Bln' }})</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">

                        <!-- HEADER GRUP 5.1.00: BELANJA PEGAWAI PERGURUAN -->
                        <tr class="bg-blue-900 text-white font-extrabold">
                            <td class="px-3 py-2 text-center text-blue-200">5.1.00</td>
                            <td colspan="5" class="px-4 py-2 uppercase tracking-wider">
                                <i class="fas fa-users-gear mr-1.5 text-blue-300"></i> KELOMPOK REKENING: BELANJA PEGAWAI PERGURUAN (OTOMATIS PENUGASAN)
                            </td>
                            <td class="px-4 py-2 text-right text-blue-200">
                                Rp {{ number_format($totalGajiPerguruanMonthly, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-2 text-right text-blue-200 font-black">
                                Rp {{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}
                            </td>
                        </tr>

                        @php $no = 1; @endphp
                        @foreach($parsedExpenseDetails as $code => $detail)
                            @php
                                $vol = $detail['volume'] ?? 1;
                                $unit = $detail['unit'] ?? 'Paket';
                                $tariff = $detail['tariff'] ?? 0;
                                $amtMonthly = $detail['amount'] ?? 0;
                                $amtPeriod = $amtMonthly * $multiplier;
                                $isAuto = $detail['is_automatic'] ?? false;
                                $safeCode = str_replace('.', '_', $code);
                            @endphp

                            <!-- PEMISAH JIKA MASUK KELOMPOK OPERASIONAL NON-GAJI (5.1.01) -->
                            @if($code === '5.1.01')
                                <tr class="bg-amber-900 text-white font-extrabold">
                                    <td class="px-3 py-2 text-center text-amber-200">5.1.01+</td>
                                    <td colspan="5" class="px-4 py-2 uppercase tracking-wider">
                                        <i class="fas fa-list-check mr-1.5 text-amber-300"></i> KELOMPOK REKENING: BELANJA OPERASIONAL NON-GAJI (DAPAT DI-EDIT)
                                    </td>
                                    <td class="px-4 py-2 text-right text-amber-200" id="groupOpsMonthly">
                                        Rp {{ number_format($totalOpsMonthly, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2 text-right text-amber-200 font-black" id="groupOpsPeriod">
                                        Rp {{ number_format($totalOpsPeriod, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endif

                            <tr class="hover:bg-violet-50/20 transition {{ $isAuto ? 'bg-blue-50/40' : '' }}">
                                <td class="px-3 py-3 text-center font-bold text-gray-400">{{ $no++ }}</td>
                                <td class="px-3 py-3 font-mono font-bold {{ $isAuto ? 'text-blue-800 bg-blue-100/50' : 'text-violet-700 bg-violet-50/50' }} rounded-lg text-center">
                                    {{ $code }}
                                </td>
                                <td class="px-4 py-3 font-bold text-gray-900">
                                    <div class="flex items-center gap-2">
                                        <i class="fas {{ $detail['icon'] }} {{ $isAuto ? 'text-blue-600' : 'text-gray-400' }} w-4"></i>
                                        <span>{{ $detail['name'] }}</span>
                                        @if($isAuto)
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-200">
                                                <i class="fas fa-lock text-[8px] mr-1"></i> Otomatis Penugasan
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                @if($isAuto)
                                    <!-- Sub-rekening Belanja Pegawai (Berasal dari Penugasan - Read Only) -->
                                    <td class="px-3 py-3 text-center font-bold text-blue-900">
                                        {{ $vol }}
                                    </td>
                                    <td class="px-3 py-3 text-center font-bold text-blue-900">
                                        {{ $unit }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-blue-900">
                                        Rp {{ number_format($tariff, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-black text-blue-900">
                                        Rp {{ number_format($amtMonthly, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-black text-blue-950 text-sm">
                                        Rp {{ number_format($amtPeriod, 0, ',', '.') }}
                                    </td>
                                @else
                                    <!-- Kode 5.1.01 s/d 5.1.14 (Bisa Di-edit rinciannya) -->
                                    <td class="px-3 py-3 text-center">
                                        <input type="number" name="expense_details[{{ $code }}][volume]" 
                                               value="{{ $vol }}" min="1" step="1" 
                                               oninput="updateRowCalc('{{ $safeCode }}')"
                                               id="vol_{{ $safeCode }}"
                                               class="w-full text-xs font-bold text-center py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500" 
                                               placeholder="1">
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <input type="text" name="expense_details[{{ $code }}][unit]" 
                                               value="{{ $unit }}"
                                               id="unit_{{ $safeCode }}"
                                               class="w-full text-xs font-semibold text-center py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500" 
                                               placeholder="Bulan/Paket">
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-xs font-bold text-gray-400">Rp</span>
                                            <input type="number" name="expense_details[{{ $code }}][tariff]" 
                                                   value="{{ $tariff > 0 ? $tariff : '' }}" 
                                                   step="5000" min="0" 
                                                   oninput="updateRowCalc('{{ $safeCode }}')"
                                                   id="tariff_{{ $safeCode }}"
                                                   class="w-full text-xs font-bold text-right pl-9 pr-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 tabular-nums" 
                                                   placeholder="0">
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-800 tabular-nums" id="monthly_{{ $safeCode }}">
                                        Rp {{ number_format($amtMonthly, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-black text-violet-900 tabular-nums text-sm" id="period_{{ $safeCode }}">
                                        Rp {{ number_format($amtPeriod, 0, ',', '.') }}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-violet-900 text-white font-bold border-t-2 border-violet-900">
                        <tr>
                            <td colspan="6" class="px-4 py-4 text-right uppercase tracking-wider font-extrabold text-xs">
                                TOTAL RENCANA BELANJA YAYASAN & PERGURUAN (RAPBY):
                            </td>
                            <td class="px-4 py-4 text-right text-amber-300 text-sm font-black" id="footTotalMonthly">
                                Rp {{ number_format($grandTotalBelanjaMonthly, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-right text-emerald-300 text-base font-black" id="footTotalPeriod">
                                Rp {{ number_format($grandTotalBelanjaPeriod, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Notes & Footer Submit Button -->
            <div class="p-6 bg-gray-50 border-t border-gray-200 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="w-full md:w-2/3">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Catatan Rencana Anggaran Belanja (Opsional)</label>
                    <input type="text" name="notes" value="{{ $contribution->notes ?? '' }}" 
                           class="w-full text-xs p-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-violet-500" 
                           placeholder="Catatan persetujuan rincian anggaran belanja...">
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="px-6 py-2.5 bg-violet-600 hover:bg-violet-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Rencana Belanja
                    </button>
                </div>
            </div>
        </div>
    </form>

</div>

@push('scripts')
<script>
    const totalGajiMonthly = {{ $totalGajiPerguruanMonthly }};
    const multiplier = {{ $multiplier }};

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

        // Recalculate Grand Totals
        let sumOpsMonthly = 0;
        const allTariffInputs = document.querySelectorAll('input[id^="tariff_"]');
        allTariffInputs.forEach(function(tInput) {
            const codeKey = tInput.id.replace('tariff_', '');
            const vInput = document.getElementById('vol_' + codeKey);
            const vVal = parseFloat(vInput ? vInput.value : 1) || 0;
            const tVal = parseFloat(tInput.value) || 0;
            sumOpsMonthly += (vVal * tVal);
        });

        const sumOpsPeriod = sumOpsMonthly * multiplier;
        const grandMonthly = totalGajiMonthly + sumOpsMonthly;
        const grandPeriod = grandMonthly * multiplier;

        document.getElementById('groupOpsMonthly').innerText = 'Rp ' + sumOpsMonthly.toLocaleString('id-ID');
        document.getElementById('groupOpsPeriod').innerText = 'Rp ' + sumOpsPeriod.toLocaleString('id-ID');
        document.getElementById('cardOpsTotal').innerText = 'Rp ' + sumOpsPeriod.toLocaleString('id-ID');
        document.getElementById('cardGrandTotal').innerText = 'Rp ' + grandPeriod.toLocaleString('id-ID');
        document.getElementById('footTotalMonthly').innerText = 'Rp ' + grandMonthly.toLocaleString('id-ID');
        document.getElementById('footTotalPeriod').innerText = 'Rp ' + grandPeriod.toLocaleString('id-ID');
    }
</script>
@endpush
@endsection
