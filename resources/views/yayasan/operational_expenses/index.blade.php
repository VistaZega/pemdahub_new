@extends('layouts.yayasan')

@section('title', 'Rencana Belanja Yayasan & Perguruan')

@push('styles')
<style>
    .rapby-gradient { background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4c1d95 100%); }
    .rapby-card { backdrop-filter: blur(16px); background: rgba(255,255,255,0.95); border: 1px solid rgba(99,102,241,0.12); }
    .num-col { font-variant-numeric: tabular-nums; }
    .stat-card { position: relative; overflow: hidden; transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
    .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; }
    .stat-card.blue::before { background: linear-gradient(90deg, #3b82f6, #6366f1); }
    .stat-card.amber::before { background: linear-gradient(90deg, #f59e0b, #d97706); }
    .stat-card.violet::before { background: linear-gradient(90deg, #7c3aed, #a855f7); }
    .rapby-input { transition: all 0.2s ease; border: 1.5px solid #e5e7eb; }
    .rapby-input:focus { border-color: #8b5cf6; box-shadow: 0 0 0 3px rgba(139,92,246,0.15); outline: none; }
    .auto-badge { display: inline-flex; align-items: center; gap: 3px; padding: 1px 6px; border-radius: 4px; font-size: 9px; font-weight: 800; background: linear-gradient(135deg, #dbeafe, #e0e7ff); color: #1e40af; border: 1px solid #bfdbfe; text-transform: uppercase; letter-spacing: 0.05em; }
</style>
@endpush

@section('content')
<div class="space-y-6">

    {{-- HERO HEADER --}}
    <div class="rapby-gradient rounded-2xl p-6 md:p-8 text-white shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 right-0 w-72 h-72 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3"></div>
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
                    Penyusunan anggaran belanja pegawai per unit pendidikan & yayasan beserta belanja operasional terpusat.
                </p>
            </div>
            <form method="GET" action="{{ route('yayasan.operational_expenses.index') }}" class="flex flex-wrap items-center gap-3">
                <select name="academic_year_id" onchange="this.form.submit()" class="bg-white/10 text-white border border-white/20 rounded-xl text-xs px-3 py-2.5 font-bold backdrop-blur-md focus:ring-2 focus:ring-amber-400 min-w-[160px]">
                    @foreach($allYears as $y)
                        <option value="{{ $y->id }}" {{ ($currentYear->id ?? '') == $y->id ? 'selected' : '' }} class="text-gray-900">
                            TP {{ $y->year }} {{ $y->is_active ? '✦ Aktif' : '' }}
                        </option>
                    @endforeach
                </select>
                <div class="bg-white/10 p-1 rounded-xl border border-white/20 backdrop-blur-md flex items-center gap-1">
                    <a href="{{ route('yayasan.operational_expenses.index', ['academic_year_id' => $currentYear->id, 'period_mode' => 'annual']) }}"
                       class="px-3.5 py-2 rounded-lg text-xs font-bold transition {{ $periodMode === 'annual' ? 'bg-amber-400 text-amber-950 shadow-lg' : 'text-white/80 hover:bg-white/10' }}">
                        <i class="fas fa-calendar-days mr-1"></i> 12 Bulan
                    </a>
                    <a href="{{ route('yayasan.operational_expenses.index', ['academic_year_id' => $currentYear->id, 'period_mode' => 'monthly']) }}"
                       class="px-3.5 py-2 rounded-lg text-xs font-bold transition {{ $periodMode === 'monthly' ? 'bg-amber-400 text-amber-950 shadow-lg' : 'text-white/80 hover:bg-white/10' }}">
                        <i class="fas fa-calendar-day mr-1"></i> 1 Bulan
                    </a>
                </div>
                <a href="{{ route('yayasan.operational_expenses.export_pdf', ['academic_year_id' => $currentYear->id, 'period_mode' => $periodMode]) }}" target="_blank"
                   class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-white font-bold text-xs rounded-xl shadow-lg transition flex items-center gap-1.5">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-bold flex items-center gap-2 shadow-sm">
            <i class="fas fa-check-circle text-emerald-600 text-base"></i> {{ session('success') }}
        </div>
    @endif

    {{-- SUMMARY CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="stat-card blue rapby-card rounded-2xl p-5 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/25">
                    <i class="fas fa-users-gear text-lg"></i>
                </div>
                <div>
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">5.1.00 — Belanja Pegawai</p>
                    <p class="text-xl font-black text-gray-900 mt-0.5 num-col">Rp {{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-blue-600 font-semibold mt-1">{{ count($hierarchicalSalaryData) }} Unit • {{ $totalPegawaiCount }} Pegawai</p>
                </div>
            </div>
        </div>
        <div class="stat-card amber rapby-card rounded-2xl p-5 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center shadow-lg shadow-amber-500/25">
                    <i class="fas fa-list-check text-lg"></i>
                </div>
                <div>
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">5.1.01–14 — Belanja Operasional</p>
                    <p class="text-xl font-black text-gray-900 mt-0.5 num-col" id="cardOpsTotal">Rp {{ number_format($totalOpsPeriod, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-amber-600 font-semibold mt-1">Dapat Diedit</p>
                </div>
            </div>
        </div>
        <div class="stat-card violet rapby-card rounded-2xl p-5 shadow-sm bg-gradient-to-br from-violet-50/80 to-purple-50/80">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-600 to-purple-700 text-white flex items-center justify-center shadow-lg shadow-violet-600/30">
                    <i class="fas fa-calculator text-lg"></i>
                </div>
                <div>
                    <p class="text-[10px] text-violet-700 font-extrabold uppercase tracking-wider">Grand Total RAPBY</p>
                    <p class="text-2xl font-black text-violet-950 mt-0.5 num-col" id="cardGrandTotal">Rp {{ number_format($grandTotalBelanjaPeriod, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-violet-600 font-semibold mt-1">Pegawai + Operasional</p>
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN TABLE --}}
    <form method="POST" action="{{ route('yayasan.operational_expenses.store') }}">
        @csrf
        <input type="hidden" name="academic_year_id" value="{{ $currentYear->id }}">

        <div class="rapby-card rounded-2xl shadow-lg overflow-hidden">
            <div class="rapby-gradient p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center">
                        <i class="fas fa-table-list text-amber-400"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-extrabold text-white">Rincian Anggaran Belanja RAPBY</h2>
                        <p class="text-indigo-300 text-[10px]">TP {{ $currentYear->year ?? '-' }} • {{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }}</p>
                    </div>
                </div>
                <button type="submit" class="px-6 py-2.5 bg-amber-400 hover:bg-amber-300 text-amber-950 font-black text-xs rounded-xl shadow-lg transition flex items-center gap-2">
                    <i class="fas fa-save"></i> Simpan Rencana Belanja
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="bg-gradient-to-r from-slate-800 to-slate-700 text-white">
                            <th class="px-3 py-3 text-center w-12 text-[10px] uppercase font-bold">No</th>
                            <th class="px-3 py-3 w-28 text-[10px] uppercase font-bold">Kode Rek.</th>
                            <th class="px-4 py-3 text-[10px] uppercase font-bold">Nama Rekening Belanja</th>
                            <th class="px-3 py-3 w-24 text-center text-[10px] uppercase font-bold">Jumlah</th>
                            <th class="px-3 py-3 w-24 text-center text-[10px] uppercase font-bold">Satuan</th>
                            <th class="px-4 py-3 text-right w-40 text-[10px] uppercase font-bold">Tarif Satuan (Rp)</th>
                            <th class="px-4 py-3 text-right w-40 text-[10px] uppercase font-bold">Total / Bulan</th>
                            <th class="px-4 py-3 text-right w-44 text-[10px] uppercase font-bold">Total Periode</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">

                        {{-- KELOMPOK 5.1.00: BELANJA PEGAWAI --}}
                        <tr class="bg-gradient-to-r from-blue-900 via-blue-800 to-indigo-900 text-white">
                            <td class="px-3 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-white/15 text-[10px] font-black">5.1</span>
                            </td>
                            <td class="px-3 py-3 font-mono font-black text-blue-200">5.1.00</td>
                            <td colspan="4" class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-users-gear text-blue-300"></i>
                                    <span class="font-extrabold uppercase tracking-wide text-[11px]">Kelompok: Belanja Pegawai Perguruan</span>
                                    <span class="auto-badge bg-blue-200/20 text-blue-200 border-blue-400/30">
                                        <i class="fas fa-lock text-[7px]"></i> Otomatis
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-blue-200 num-col">Rp {{ number_format($totalGajiPerguruanMonthly, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-black text-white text-sm num-col">Rp {{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}</td>
                        </tr>

                        @foreach($hierarchicalSalaryData as $uIdx => $uData)
                            @php $item = $uData['items'][0] ?? null; @endphp
                            @if($item)
                            <tr class="hover:bg-blue-50/50 transition {{ $uData['school_type'] === 'yayasan' ? 'bg-violet-50/30' : '' }}">
                                <td class="px-3 py-3 text-center">
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full {{ $uData['school_type'] === 'yayasan' ? 'bg-violet-100 text-violet-700' : 'bg-blue-100 text-blue-700' }} text-[9px] font-bold">
                                        {{ $uIdx + 1 }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 font-mono font-bold {{ $uData['school_type'] === 'yayasan' ? 'text-violet-700' : 'text-blue-700' }} text-[11px]">
                                    {{ $item['code'] }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <i class="fas {{ $item['icon'] }} {{ $uData['school_type'] === 'yayasan' ? 'text-violet-500' : 'text-blue-500' }}"></i>
                                        <span class="font-bold text-gray-900">{{ $item['name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center font-bold text-gray-800 num-col">{{ $item['volume'] }}</td>
                                <td class="px-3 py-3 text-center font-semibold text-gray-600">{{ $item['unit'] }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-800 num-col">Rp {{ number_format($item['tariff'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-bold text-gray-800 num-col">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-black {{ $uData['school_type'] === 'yayasan' ? 'text-violet-900' : 'text-blue-900' }} num-col">
                                    Rp {{ number_format($uData['total_period'], 0, ',', '.') }}
                                </td>
                            </tr>
                            @endif
                        @endforeach

                        {{-- Subtotal Pegawai --}}
                        <tr class="bg-blue-50 border-y-2 border-blue-200">
                            <td colspan="6" class="px-4 py-3 text-right font-extrabold text-blue-900 uppercase text-[10px] tracking-wider">
                                <i class="fas fa-sigma mr-1.5 text-blue-600"></i> Subtotal Belanja Pegawai (5.1.00):
                            </td>
                            <td class="px-4 py-3 text-right font-black text-blue-900 num-col">Rp {{ number_format($totalGajiPerguruanMonthly, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-black text-blue-950 text-sm num-col">Rp {{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}</td>
                        </tr>

                        {{-- KELOMPOK 5.1.01+: BELANJA OPERASIONAL --}}
                        <tr class="bg-gradient-to-r from-amber-900 via-amber-800 to-orange-900 text-white">
                            <td class="px-3 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-white/15 text-[10px] font-black">5.1</span>
                            </td>
                            <td class="px-3 py-3 font-mono font-black text-amber-200">5.1.01+</td>
                            <td colspan="4" class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-list-check text-amber-300"></i>
                                    <span class="font-extrabold uppercase tracking-wide text-[11px]">Kelompok: Belanja Operasional Non-Gaji</span>
                                    <span class="auto-badge bg-amber-200/20 text-amber-200 border-amber-400/30">
                                        <i class="fas fa-pen text-[7px]"></i> Editable
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-amber-200 num-col" id="groupOpsMonthly">Rp {{ number_format($totalOpsMonthly, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-black text-white text-sm num-col" id="groupOpsPeriod">Rp {{ number_format($totalOpsPeriod, 0, ',', '.') }}</td>
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
                                <tr class="hover:bg-amber-50/40 transition {{ $opsNo % 2 === 0 ? 'bg-gray-50/50' : '' }}">
                                    <td class="px-3 py-3 text-center">
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-100 text-amber-700 text-[9px] font-bold">{{ $opsNo++ }}</span>
                                    </td>
                                    <td class="px-3 py-3">
                                        <span class="font-mono font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded text-[11px]">{{ $code }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-md bg-gray-100 text-gray-500 flex items-center justify-center">
                                                <i class="fas {{ $detail['icon'] }} text-[10px]"></i>
                                            </div>
                                            <span class="font-bold text-gray-900 text-[11px]">{{ $detail['name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-2 py-3 text-center">
                                        <input type="number" name="expense_details[{{ $code }}][volume]" value="{{ $vol }}" min="1" step="1"
                                               oninput="updateRowCalc('{{ $safeCode }}')" id="vol_{{ $safeCode }}"
                                               class="rapby-input w-full text-xs font-bold text-center py-1.5 rounded-lg bg-white" placeholder="1">
                                    </td>
                                    <td class="px-2 py-3 text-center">
                                        <input type="text" name="expense_details[{{ $code }}][unit]" value="{{ $unit }}" id="unit_{{ $safeCode }}"
                                               class="rapby-input w-full text-xs font-semibold text-center py-1.5 rounded-lg bg-white" placeholder="Bulan">
                                    </td>
                                    <td class="px-3 py-3 text-right">
                                        <div class="relative">
                                            <span class="absolute left-2.5 top-2 text-[10px] font-bold text-gray-400">Rp</span>
                                            <input type="number" name="expense_details[{{ $code }}][tariff]" value="{{ $tariff > 0 ? $tariff : '' }}"
                                                   step="5000" min="0" oninput="updateRowCalc('{{ $safeCode }}')" id="tariff_{{ $safeCode }}"
                                                   class="rapby-input w-full text-xs font-bold text-right pl-8 pr-2 py-1.5 rounded-lg bg-white num-col" placeholder="0">
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-700 num-col" id="monthly_{{ $safeCode }}">Rp {{ number_format($amtMonthly, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-black text-amber-900 num-col" id="period_{{ $safeCode }}">Rp {{ number_format($amtPeriod, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                        @endforeach

                        {{-- Subtotal Ops --}}
                        <tr class="bg-amber-50 border-y-2 border-amber-200">
                            <td colspan="6" class="px-4 py-3 text-right font-extrabold text-amber-900 uppercase text-[10px] tracking-wider">
                                <i class="fas fa-sigma mr-1.5 text-amber-600"></i> Subtotal Belanja Operasional (5.1.01–14):
                            </td>
                            <td class="px-4 py-3 text-right font-black text-amber-900 num-col" id="subtotalOpsMonthly">Rp {{ number_format($totalOpsMonthly, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-black text-amber-950 text-sm num-col" id="subtotalOpsPeriod">Rp {{ number_format($totalOpsPeriod, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gradient-to-r from-violet-900 via-indigo-900 to-purple-900 text-white">
                            <td colspan="6" class="px-6 py-4 text-right uppercase tracking-widest font-extrabold text-[11px] text-indigo-200">
                                Grand Total Rencana Belanja RAPBY:
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

            <div class="p-6 bg-gray-50/80 border-t border-gray-200 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="w-full md:w-2/3">
                    <label class="block text-[10px] font-bold text-gray-500 mb-1.5 uppercase tracking-wider">Catatan (Opsional)</label>
                    <input type="text" name="notes" value="{{ $contribution->notes ?? '' }}"
                           class="rapby-input w-full text-xs p-3 rounded-xl bg-white" placeholder="Catatan persetujuan rencana anggaran belanja...">
                </div>
                <button type="submit" class="px-8 py-3 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-black text-xs rounded-xl shadow-lg transition flex items-center gap-2">
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

    function updateRowCalc(safeCode) {
        const vol = parseFloat(document.getElementById('vol_' + safeCode)?.value) || 0;
        const tariff = parseFloat(document.getElementById('tariff_' + safeCode)?.value) || 0;
        const monthly = vol * tariff;
        const period = monthly * multiplier;
        const fmt = (n) => 'Rp ' + n.toLocaleString('id-ID');

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
