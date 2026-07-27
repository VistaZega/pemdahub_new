@extends('layouts.yayasan')

@section('title', 'Rencana Belanja Yayasan & Perguruan')

@push('styles')
<style>
    .rapby-gradient { background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%); }
    .rapby-card { background: #ffffff; border: 2px solid #cbd5e1; }
    .num-col { font-variant-numeric: tabular-nums; }
    .stat-card { position: relative; overflow: hidden; }
    .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; }
    .stat-card.blue::before { background: #2563eb; }
    .stat-card.amber::before { background: #d97706; }
    .stat-card.violet::before { background: #7c3aed; }
    .rapby-input { transition: all 0.2s ease; border: 2px solid #94a3b8; color: #0f172a; font-weight: 700; }
    .rapby-input:focus { border-color: #4338ca; box-shadow: 0 0 0 3px rgba(67,56,202,0.25); outline: none; }
    .auto-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 900; background: #dbeafe; color: #1e3a8a; border: 1.5px solid #1d4ed8; text-transform: uppercase; }
</style>
@endpush

@section('content')
<div class="space-y-6">

    {{-- HERO HEADER --}}
    <div class="rapby-gradient rounded-2xl p-6 md:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-12 h-12 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center font-black shadow-lg">
                        <i class="fas fa-file-invoice-dollar text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl md:text-2xl font-black text-white tracking-tight">Rencana Anggaran Belanja</h1>
                        <p class="text-amber-300 text-sm font-black tracking-wide">Yayasan & Perguruan (RAPBY)</p>
                    </div>
                </div>
                <p class="text-white text-xs font-bold max-w-lg leading-relaxed mt-1">
                    Penyusunan anggaran belanja pegawai per unit pendidikan & yayasan beserta belanja operasional terpusat.
                </p>
            </div>
            <form method="GET" action="{{ route('yayasan.operational_expenses.index') }}" class="flex flex-wrap items-center gap-3">
                <select name="academic_year_id" onchange="this.form.submit()" class="bg-slate-900 text-white border-2 border-amber-400 rounded-xl text-xs px-3 py-2.5 font-black focus:ring-2 focus:ring-amber-400 min-w-[160px]">
                    @foreach($allYears as $y)
                        <option value="{{ $y->id }}" {{ ($currentYear->id ?? '') == $y->id ? 'selected' : '' }} class="bg-slate-900 text-white font-bold">
                            TP {{ $y->year }} {{ $y->is_active ? '✦ Aktif' : '' }}
                        </option>
                    @endforeach
                </select>
                <div class="bg-slate-900 p-1 rounded-xl border-2 border-slate-700 flex items-center gap-1">
                    <a href="{{ route('yayasan.operational_expenses.index', ['academic_year_id' => $currentYear->id, 'period_mode' => 'annual']) }}"
                       class="px-4 py-2 rounded-lg text-xs font-black transition {{ $periodMode === 'annual' ? 'bg-amber-400 text-slate-950 shadow-md' : 'text-white hover:bg-slate-800' }}">
                        <i class="fas fa-calendar-days mr-1"></i> 12 Bulan
                    </a>
                    <a href="{{ route('yayasan.operational_expenses.index', ['academic_year_id' => $currentYear->id, 'period_mode' => 'monthly']) }}"
                       class="px-4 py-2 rounded-lg text-xs font-black transition {{ $periodMode === 'monthly' ? 'bg-amber-400 text-slate-950 shadow-md' : 'text-white hover:bg-slate-800' }}">
                        <i class="fas fa-calendar-day mr-1"></i> 1 Bulan
                    </a>
                </div>
                <a href="{{ route('yayasan.operational_expenses.export_pdf', ['academic_year_id' => $currentYear->id, 'period_mode' => $periodMode]) }}" target="_blank"
                   class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs rounded-xl shadow-lg transition flex items-center gap-1.5 border border-emerald-400">
                    <i class="fas fa-file-pdf text-sm"></i> Export PDF
                </a>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-100 border-2 border-emerald-500 text-emerald-950 rounded-xl text-xs font-black flex items-center gap-2 shadow-sm">
            <i class="fas fa-check-circle text-emerald-700 text-lg"></i> {{ session('success') }}
        </div>
    @endif

    {{-- SUMMARY CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="stat-card blue rapby-card rounded-2xl p-5 shadow-md">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-700 text-white flex items-center justify-center shadow-lg font-black">
                    <i class="fas fa-users-gear text-xl"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-900 font-black uppercase tracking-wider">5.1.00 — Belanja Pegawai</p>
                    <p class="text-2xl font-black text-blue-950 mt-0.5 num-col">Rp {{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}</p>
                    <p class="text-xs text-blue-800 font-black mt-1">{{ count($hierarchicalSalaryData) }} Unit • {{ $totalPegawaiCount }} Pegawai</p>
                </div>
            </div>
        </div>
        <div class="stat-card amber rapby-card rounded-2xl p-5 shadow-md">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-600 text-white flex items-center justify-center shadow-lg font-black">
                    <i class="fas fa-list-check text-xl"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-900 font-black uppercase tracking-wider">5.1.01–14 — Belanja Operasional</p>
                    <p class="text-2xl font-black text-amber-950 mt-0.5 num-col" id="cardOpsTotal">Rp {{ number_format($totalOpsPeriod, 0, ',', '.') }}</p>
                    <p class="text-xs text-amber-900 font-black mt-1">Dapat Diedit</p>
                </div>
            </div>
        </div>
        <div class="stat-card violet rapby-card rounded-2xl p-5 shadow-md bg-violet-100 border-2 border-violet-300">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-violet-800 text-white flex items-center justify-center shadow-lg font-black">
                    <i class="fas fa-calculator text-xl"></i>
                </div>
                <div>
                    <p class="text-xs text-violet-950 font-black uppercase tracking-wider">Grand Total RAPBY</p>
                    <p class="text-2xl font-black text-violet-950 mt-0.5 num-col" id="cardGrandTotal">Rp {{ number_format($grandTotalBelanjaPeriod, 0, ',', '.') }}</p>
                    <p class="text-xs text-violet-900 font-black mt-1">Pegawai + Operasional</p>
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN TABLE --}}
    <form method="POST" action="{{ route('yayasan.operational_expenses.store') }}">
        @csrf
        <input type="hidden" name="academic_year_id" value="{{ $currentYear->id }}">

        <div class="rapby-card rounded-2xl shadow-xl overflow-hidden">
            <div class="rapby-gradient p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-400 text-slate-950 font-black flex items-center justify-center">
                        <i class="fas fa-table-list text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-white">Rincian Anggaran Belanja RAPBY</h2>
                        <p class="text-amber-300 text-xs font-black">TP {{ $currentYear->year ?? '-' }} • {{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }}</p>
                    </div>
                </div>
                <button type="submit" class="px-6 py-2.5 bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs rounded-xl shadow-lg transition flex items-center gap-2 border-2 border-amber-300">
                    <i class="fas fa-save text-sm"></i> Simpan Rencana Belanja
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="bg-slate-900 text-white border-b-2 border-slate-700">
                            <th class="px-3 py-3.5 text-center w-12 text-xs uppercase font-black">No</th>
                            <th class="px-3 py-3.5 w-28 text-xs uppercase font-black">Kode Rek.</th>
                            <th class="px-4 py-3.5 text-xs uppercase font-black">Nama Rekening Belanja</th>
                            <th class="px-3 py-3.5 w-24 text-center text-xs uppercase font-black">Jumlah</th>
                            <th class="px-3 py-3.5 w-24 text-center text-xs uppercase font-black">Satuan</th>
                            <th class="px-4 py-3.5 text-right w-40 text-xs uppercase font-black">Tarif Satuan (Rp)</th>
                            <th class="px-4 py-3.5 text-right w-40 text-xs uppercase font-black">Total / Bulan</th>
                            <th class="px-4 py-3.5 text-right w-44 text-xs uppercase font-black">Total Periode</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-slate-200 bg-white">

                        {{-- KELOMPOK 5.1.00: BELANJA PEGAWAI --}}
                        <tr class="bg-blue-900 text-white">
                            <td class="px-3 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-800 text-white text-xs font-black border border-blue-400">5.1</span>
                            </td>
                            <td class="px-3 py-3 font-mono font-black text-amber-300 text-sm">5.1.00</td>
                            <td colspan="4" class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-users-gear text-amber-400 text-sm"></i>
                                    <span class="font-black uppercase tracking-wide text-xs text-white">Kelompok: Belanja Pegawai Perguruan</span>
                                    <span class="auto-badge">
                                        <i class="fas fa-lock text-[8px]"></i> Otomatis
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-black text-amber-300 num-col text-xs">Rp {{ number_format($totalGajiPerguruanMonthly, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-black text-white text-sm num-col">Rp {{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}</td>
                        </tr>

                        @foreach($hierarchicalSalaryData as $uIdx => $uData)
                            @php $item = $uData['items'][0] ?? null; @endphp
                            @if($item)
                            <tr class="hover:bg-slate-100 transition {{ $uData['school_type'] === 'yayasan' ? 'bg-purple-50' : 'bg-white' }}">
                                <td class="px-3 py-3.5 text-center">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full {{ $uData['school_type'] === 'yayasan' ? 'bg-purple-900 text-white' : 'bg-blue-900 text-white' }} text-xs font-black">
                                        {{ $uIdx + 1 }}
                                    </span>
                                </td>
                                <td class="px-3 py-3.5 font-mono font-black {{ $uData['school_type'] === 'yayasan' ? 'text-purple-950' : 'text-blue-950' }} text-xs">
                                    {{ $item['code'] }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <i class="fas {{ $item['icon'] }} {{ $uData['school_type'] === 'yayasan' ? 'text-purple-700' : 'text-blue-700' }} text-sm"></i>
                                        <span class="font-black text-slate-950 text-xs">{{ $item['name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3.5 text-center font-black text-slate-950 num-col">{{ $item['volume'] }}</td>
                                <td class="px-3 py-3.5 text-center font-black text-slate-900">{{ $item['unit'] }}</td>
                                <td class="px-4 py-3.5 text-right font-black text-slate-950 num-col">Rp {{ number_format($item['tariff'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3.5 text-right font-black text-slate-950 num-col">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3.5 text-right font-black {{ $uData['school_type'] === 'yayasan' ? 'text-purple-950' : 'text-blue-950' }} text-xs num-col">
                                    Rp {{ number_format($uData['total_period'], 0, ',', '.') }}
                                </td>
                            </tr>
                            @endif
                        @endforeach

                        {{-- Subtotal Pegawai --}}
                        <tr class="bg-blue-100 border-y-2 border-blue-400">
                            <td colspan="6" class="px-4 py-3.5 text-right font-black text-blue-950 uppercase text-xs tracking-wider">
                                <i class="fas fa-sigma mr-1.5 text-blue-800"></i> Subtotal Belanja Pegawai (5.1.00):
                            </td>
                            <td class="px-4 py-3.5 text-right font-black text-blue-950 text-xs num-col">Rp {{ number_format($totalGajiPerguruanMonthly, 0, ',', '.') }}</td>
                            <td class="px-4 py-3.5 text-right font-black text-blue-950 text-sm num-col">Rp {{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}</td>
                        </tr>

                        {{-- KELOMPOK 5.1.01+: BELANJA OPERASIONAL --}}
                        <tr class="bg-amber-900 text-white">
                            <td class="px-3 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-800 text-white text-xs font-black border border-amber-400">5.1</span>
                            </td>
                            <td class="px-3 py-3 font-mono font-black text-amber-300 text-sm">5.1.01+</td>
                            <td colspan="4" class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-list-check text-amber-300 text-sm"></i>
                                    <span class="font-black uppercase tracking-wide text-xs text-white">Kelompok: Belanja Operasional Non-Gaji</span>
                                    <span class="auto-badge bg-amber-300 text-slate-950 border-amber-500">
                                        <i class="fas fa-pen text-[8px]"></i> Editable
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-black text-amber-300 num-col text-xs" id="groupOpsMonthly">Rp {{ number_format($totalOpsMonthly, 0, ',', '.') }}</td>
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
                                <tr class="hover:bg-amber-100 transition {{ $opsNo % 2 === 0 ? 'bg-amber-50/60' : 'bg-white' }}">
                                    <td class="px-3 py-3.5 text-center">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-800 text-white text-xs font-black">{{ $opsNo++ }}</span>
                                    </td>
                                    <td class="px-3 py-3.5">
                                        <span class="font-mono font-black text-amber-950 bg-amber-200 px-2 py-1 rounded border border-amber-400 text-xs">{{ $code }}</span>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-md bg-slate-200 text-slate-900 font-bold flex items-center justify-center">
                                                <i class="fas {{ $detail['icon'] }} text-xs"></i>
                                            </div>
                                            <span class="font-black text-slate-950 text-xs">{{ $detail['name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-2 py-3.5 text-center">
                                        <input type="number" name="expense_details[{{ $code }}][volume]" value="{{ $vol }}" min="1" step="1"
                                               oninput="updateRowCalc('{{ $safeCode }}')" id="vol_{{ $safeCode }}"
                                               class="rapby-input w-full text-xs font-black text-center py-1.5 rounded-lg bg-white" placeholder="1">
                                    </td>
                                    <td class="px-2 py-3.5 text-center">
                                        <input type="text" name="expense_details[{{ $code }}][unit]" value="{{ $unit }}" id="unit_{{ $safeCode }}"
                                               class="rapby-input w-full text-xs font-black text-center py-1.5 rounded-lg bg-white" placeholder="Bulan">
                                    </td>
                                    <td class="px-3 py-3.5 text-right">
                                        <div class="relative">
                                            <span class="absolute left-2.5 top-2 text-xs font-black text-slate-900">Rp</span>
                                            <input type="number" name="expense_details[{{ $code }}][tariff]" value="{{ $tariff > 0 ? $tariff : '' }}"
                                                   step="5000" min="0" oninput="updateRowCalc('{{ $safeCode }}')" id="tariff_{{ $safeCode }}"
                                                   class="rapby-input w-full text-xs font-black text-right pl-8 pr-2 py-1.5 rounded-lg bg-white num-col" placeholder="0">
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-black text-slate-950 num-col text-xs" id="monthly_{{ $safeCode }}">Rp {{ number_format($amtMonthly, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3.5 text-right font-black text-amber-950 num-col text-xs" id="period_{{ $safeCode }}">Rp {{ number_format($amtPeriod, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                        @endforeach

                        {{-- Subtotal Ops --}}
                        <tr class="bg-amber-100 border-y-2 border-amber-400">
                            <td colspan="6" class="px-4 py-3.5 text-right font-black text-amber-950 uppercase text-xs tracking-wider">
                                <i class="fas fa-sigma mr-1.5 text-amber-800"></i> Subtotal Belanja Operasional (5.1.01–14):
                            </td>
                            <td class="px-4 py-3.5 text-right font-black text-amber-950 text-xs num-col" id="subtotalOpsMonthly">Rp {{ number_format($totalOpsMonthly, 0, ',', '.') }}</td>
                            <td class="px-4 py-3.5 text-right font-black text-amber-950 text-sm num-col" id="subtotalOpsPeriod">Rp {{ number_format($totalOpsPeriod, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-950 text-white border-t-4 border-slate-700">
                            <td colspan="6" class="px-6 py-4 text-right uppercase tracking-widest font-black text-xs text-amber-400">
                                Grand Total Rencana Belanja RAPBY:
                            </td>
                            <td class="px-4 py-4 text-right num-col">
                                <span class="text-amber-300 font-black text-sm" id="footTotalMonthly">Rp {{ number_format($grandTotalBelanjaMonthly, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-4 py-4 text-right num-col">
                                <span class="text-emerald-400 font-black text-xl" id="footTotalPeriod">Rp {{ number_format($grandTotalBelanjaPeriod, 0, ',', '.') }}</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="p-6 bg-slate-100 border-t-2 border-slate-300 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="w-full md:w-2/3">
                    <label class="block text-xs font-black text-slate-900 mb-1.5 uppercase tracking-wider">Catatan (Opsional)</label>
                    <input type="text" name="notes" value="{{ $contribution->notes ?? '' }}"
                           class="rapby-input w-full text-xs p-3 rounded-xl bg-white text-slate-950 font-bold border-2 border-slate-400" placeholder="Catatan persetujuan rencana anggaran belanja...">
                </div>
                <button type="submit" class="px-8 py-3.5 bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs rounded-xl shadow-xl transition flex items-center gap-2 border-2 border-amber-300">
                    <i class="fas fa-save text-sm"></i> Simpan Rencana Belanja
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
