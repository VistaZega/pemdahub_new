@extends('layouts.yayasan')

@section('title', 'Rencana Belanja Operasional Yayasan')

@section('content')
<div class="space-y-6">

    <!-- Header & Filter Bar -->
    <div class="bg-gradient-to-r from-violet-900 via-purple-900 to-indigo-900 rounded-2xl p-6 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black flex items-center gap-2.5">
                <i class="fas fa-list-check text-amber-400"></i> Rencana Belanja Operasional Yayasan (RAPBY)
            </h1>
            <p class="text-xs text-violet-200 mt-1">
                Penyusunan anggaran rencana belanja operasional terpusat per kode rekening untuk Tahun Pelajaran aktif.
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
                <i class="fas fa-file-pdf"></i> Export PDF
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
        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-violet-100 text-violet-700 flex items-center justify-center font-bold text-xl">
                <i class="fas fa-wallet"></i>
            </div>
            <div>
                <span class="text-xs text-gray-500 font-medium block">Total Rencana Belanja (Bulanan)</span>
                <span class="text-xl font-black text-violet-900">Rp {{ number_format($totalMonthly, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xl">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div>
                <span class="text-xs text-gray-500 font-medium block">Total Rencana Belanja (Tahunan - 12 Bln)</span>
                <span class="text-xl font-black text-amber-700">Rp {{ number_format($totalMonthly * 12, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xl">
                <i class="fas fa-layer-group"></i>
            </div>
            <div>
                <span class="text-xs text-gray-500 font-medium block">Jumlah Kode Rekening Belanja</span>
                <span class="text-xl font-black text-blue-900">{{ count($expenseAccounts) }} Akun Rekening</span>
            </div>
        </div>
    </div>

    <!-- Form & Table Entry Rencana Belanja Operasional -->
    <form method="POST" action="{{ route('yayasan.operational_expenses.store') }}">
        @csrf
        <input type="hidden" name="academic_year_id" value="{{ $currentYear->id }}">

        <div class="bg-white rounded-2xl border border-gray-200 shadow-md overflow-hidden">
            <div class="p-6 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-extrabold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-edit text-violet-600"></i> Rincian Anggaran Belanja Operasional Yayasan (TP {{ $currentYear->year }})
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Input nominal anggaran operasional per bulan untuk masing-masing kode rekening belanja.</p>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-violet-800 bg-violet-50 px-3 py-1.5 rounded-xl border border-violet-200">
                        Subtotal Rencana: <strong id="displayHeaderTotal" class="text-violet-900 font-black">Rp {{ number_format($totalMonthly, 0, ',', '.') }}</strong>/bln
                    </span>

                    <button type="submit" class="px-5 py-2 bg-violet-600 hover:bg-violet-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                        <i class="fas fa-save"></i> Simpan Rencana Belanja
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
                            <tr class="hover:bg-violet-50/30 transition">
                                <td class="px-4 py-3 text-center font-bold text-gray-400">{{ $no++ }}</td>
                                <td class="px-4 py-3 font-mono font-bold text-violet-700 bg-violet-50/50 rounded-lg text-center">
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
                                               class="w-full text-xs font-bold text-right pl-9 pr-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 tabular-nums" 
                                               placeholder="0">
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-gray-800 tabular-nums" id="annual_{{ str_replace('.', '_', $code) }}">
                                    Rp {{ number_format($valAnnual, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-violet-900 text-white font-bold border-t-2 border-violet-900">
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-right uppercase tracking-wider font-extrabold">TOTAL RENCANA BELANJA OPERASIONAL YAYASAN:</td>
                            <td class="px-4 py-4 text-right text-amber-300 text-sm font-black" id="footTotalMonthly">
                                Rp {{ number_format($totalMonthly, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-right text-emerald-300 text-sm font-black" id="footTotalAnnual">
                                Rp {{ number_format($totalMonthly * 12, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Notes -->
            <div class="p-6 bg-gray-50 border-t border-gray-200 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="w-full md:w-2/3">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Catatan Tambahan Rencana Anggaran (Opsional)</label>
                    <input type="text" name="notes" value="{{ $contribution->notes ?? '' }}" 
                           class="w-full text-xs p-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-violet-500" 
                           placeholder="Catatan tambahan mengenai persetujuan atau alokasi anggaran operasional...">
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
    function updateRowCalc(codeSafe, val) {
        const num = parseFloat(val) || 0;
        const annual = num * 12;
        document.getElementById('annual_' + codeSafe).innerText = 'Rp ' + annual.toLocaleString('id-ID');

        // Recalculate Grand Totals
        let sumMonthly = 0;
        const inputs = document.querySelectorAll('input[name^="expense_details"]');
        inputs.forEach(function(inp) {
            sumMonthly += (parseFloat(inp.value) || 0);
        });

        const sumAnnual = sumMonthly * 12;
        document.getElementById('footTotalMonthly').innerText = 'Rp ' + sumMonthly.toLocaleString('id-ID');
        document.getElementById('footTotalAnnual').innerText = 'Rp ' + sumAnnual.toLocaleString('id-ID');
        document.getElementById('displayHeaderTotal').innerText = 'Rp ' + sumMonthly.toLocaleString('id-ID');
    }
</script>
@endpush
@endsection
