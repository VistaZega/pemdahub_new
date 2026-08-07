@extends('layouts.yayasan')

@section('title', 'Rencana Belanja Operasional Per Unit Sekolah (RAPBY)')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700;800;900&display=swap" rel="stylesheet">

<style>
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
    .stat-card-pro.emerald::before { background-color: #059669; }

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

    .tab-btn {
        border: 2px solid #000000;
        transition: all 0.2s ease;
        font-weight: 800;
    }
    .tab-btn.active {
        background-color: #2563eb !important;
        color: #ffffff !important;
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
    }
</style>
@endpush

@section('content')
<div class="ui-ux-promax space-y-6">

    {{-- HERO HEADER --}}
    <div class="rapby-hero rounded-3xl p-6 md:p-8 text-white relative overflow-hidden">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black shadow-lg border-2 border-black" style="background-color: #fbbf24 !important; color: #000000 !important;">
                        <i class="fas fa-school text-2xl text-black"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Rencana Belanja Operasional Per Unit Sekolah</h1>
                            <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider border border-amber-400/40 bg-amber-400/20 text-amber-300">
                                {{ $periodMode === 'annual' ? 'TAHUNAN (12 BULAN)' : 'BULANAN (1 BULAN)' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-300 font-medium">Alokasi Rencana Belanja Rutin (Subsidi Keuangan, Otorisasi, Operasional, Tunjangan Bendahara, Operator) Dikembangkan Per Unit Sekolah</p>
                    </div>
                </div>
            </div>

            {{-- TOOLBAR CONTROL --}}
            <div class="flex flex-wrap items-center gap-3">
                {{-- FILTER TP --}}
                <form action="{{ route('yayasan.operational_expenses.index') }}" method="GET" class="flex items-center gap-2 bg-white/10 p-1.5 rounded-2xl border border-white/20 backdrop-blur-md">
                    <input type="hidden" name="period_mode" value="{{ $periodMode }}">
                    <select name="academic_year_id" onchange="this.form.submit()" class="bg-gray-900 text-white font-bold text-xs rounded-xl px-3 py-2 border border-gray-700 focus:ring-2 focus:ring-amber-400">
                        @foreach($allYears as $y)
                            <option value="{{ $y->id }}" {{ ($currentYear->id ?? null) == $y->id ? 'selected' : '' }}>
                                TP {{ $y->year }} {{ $y->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </form>

                {{-- TOGGLE PERIODE --}}
                <div class="flex items-center bg-gray-900 border border-gray-700 p-1 rounded-2xl">
                    <a href="{{ route('yayasan.operational_expenses.index', ['academic_year_id' => $currentYear->id ?? '', 'period_mode' => 'annual']) }}" 
                       class="px-3 py-1.5 rounded-xl text-xs font-black transition {{ $periodMode === 'annual' ? 'bg-amber-400 text-black shadow-md' : 'text-gray-400 hover:text-white' }}">
                       12 Bulan
                    </a>
                    <a href="{{ route('yayasan.operational_expenses.index', ['academic_year_id' => $currentYear->id ?? '', 'period_mode' => 'monthly']) }}" 
                       class="px-3 py-1.5 rounded-xl text-xs font-black transition {{ $periodMode === 'monthly' ? 'bg-amber-400 text-black shadow-md' : 'text-gray-400 hover:text-white' }}">
                       1 Bulan
                    </a>
                </div>

                {{-- TOMBOL CETAK PDF --}}
                <a href="{{ route('yayasan.operational_expenses.export_pdf', ['academic_year_id' => $currentYear->id ?? '', 'period_mode' => $periodMode]) }}" 
                   target="_blank" 
                   class="px-4 py-2.5 rounded-2xl font-black text-xs uppercase tracking-wider flex items-center gap-2 border-2 border-black transition hover:scale-105 shadow-lg"
                   style="background-color: #ef4444 !important; color: #ffffff !important;">
                    <i class="fas fa-file-pdf text-sm"></i>
                    <span>Cetak PDF RAPBY</span>
                </a>
            </div>
        </div>
    </div>

    {{-- STATS CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="stat-card-pro blue p-5 rounded-2xl shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Belanja Pegawai (SDM)</span>
                <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-black">
                    <i class="fas fa-users-gear text-sm"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-blue-900 num-col">
                    Rp {{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}
                </div>
                <div class="text-xs font-bold text-gray-500 mt-1">
                    {{ $totalPegawaiCount }} Pegawai Aktif ({{ count($allSchools) }} Unit)
                </div>
            </div>
        </div>

        <div class="stat-card-pro amber p-5 rounded-2xl shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Belanja Operasional (Non-SDM)</span>
                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-black">
                    <i class="fas fa-wallet text-sm"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-amber-900 num-col">
                    Rp {{ number_format($totalOpsPerguruanPeriod, 0, ',', '.') }}
                </div>
                <div class="text-xs font-bold text-gray-500 mt-1">
                    Alokasi Rutin Subsidi, Otorisasi, Operasional & Insentif
                </div>
            </div>
        </div>

        <div class="stat-card-pro emerald p-5 rounded-2xl shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Grand Total Belanja Perguruan</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-black">
                    <i class="fas fa-calculator text-sm"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-emerald-900 num-col">
                    Rp {{ number_format($grandTotalBelanjaPeriod, 0, ',', '.') }}
                </div>
                <div class="text-xs font-bold text-gray-500 mt-1">
                    Periode {{ $periodMode === 'annual' ? '12 Bulan (1 Tahun)' : '1 Bulan' }}
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL REKAPITULASI SUMMARY PER UNIT SEKOLAH --}}
    <div class="rapby-card-pro p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                    <i class="fas fa-table-columns text-blue-600"></i>
                    <span>Rekapitulasi Total Rencana Belanja Per Unit Sekolah</span>
                </h3>
                <p class="text-xs text-gray-500 font-bold">Perbandingan Total Belanja Pegawai, Belanja Operasional, dan Grand Total Setiap Unit</p>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border-2 border-black">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-gray-900 text-white font-black uppercase tracking-wider border-b-2 border-black">
                        <th class="p-3 text-center w-12">No</th>
                        <th class="p-3">Nama Unit Sekolah / Lembaga</th>
                        <th class="p-3 text-center">Jumlah SDM</th>
                        <th class="p-3 text-right">Belanja Pegawai (Rp)</th>
                        <th class="p-3 text-right">Belanja Operasional (Rp)</th>
                        <th class="p-3 text-right bg-blue-900 text-white">Grand Total (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y border-black font-bold">
                    @foreach($schoolExpenseData as $sIdx => $sData)
                        <tr class="hover:bg-blue-50/50 transition">
                            <td class="p-3 text-center font-black">{{ $loop->iteration }}</td>
                            <td class="p-3">
                                <div class="font-black text-sm text-gray-900">{{ $sData['school_name'] }}</div>
                                <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded border border-black {{ $sData['school_type'] === 'yayasan' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $sData['school_type'] }}
                                </span>
                            </td>
                            <td class="p-3 text-center font-black text-blue-700">{{ $sData['employee_count'] }} Orang</td>
                            <td class="p-3 text-right num-col text-gray-800">Rp {{ number_format($sData['total_salary_period'], 0, ',', '.') }}</td>
                            <td class="p-3 text-right num-col text-amber-800">Rp {{ number_format($sData['total_ops_period'], 0, ',', '.') }}</td>
                            <td class="p-3 text-right num-col font-black text-blue-900 bg-blue-50/80">Rp {{ number_format($sData['grand_total_period'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-900 text-white font-black text-sm border-t-2 border-black">
                        <td colspan="2" class="p-3 text-right uppercase">TOTAL PERGURAN PEMBDA:</td>
                        <td class="p-3 text-center text-amber-400">{{ $totalPegawaiCount }} Orang</td>
                        <td class="p-3 text-right num-col">Rp {{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}</td>
                        <td class="p-3 text-right num-col text-amber-300">Rp {{ number_format($totalOpsPerguruanPeriod, 0, ',', '.') }}</td>
                        <td class="p-3 text-right num-col text-emerald-400 bg-black">Rp {{ number_format($grandTotalBelanjaPeriod, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- FORM INPUT RENCANA BELANJA OPERASIONAL KELOMPOK PER UNIT SEKOLAH --}}
    <form action="{{ route('yayasan.operational_expenses.store') }}" method="POST">
        @csrf
        <input type="hidden" name="academic_year_id" value="{{ $currentYear->id ?? '' }}">

        <div class="rapby-card-pro p-6 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b-2 border-gray-200 pb-4">
                <div>
                    <h3 class="text-xl font-black text-gray-900 flex items-center gap-2">
                        <i class="fas fa-sliders text-amber-500"></i>
                        <span>Pengaturan Rencana Belanja Operasional Per Unit Sekolah</span>
                    </h3>
                    <p class="text-xs text-gray-500 font-bold">Tentukan alokasi nominal Subsidi Keuangan, Otorisasi, Operasional, Tunjangan Bendahara, dan Operator untuk setiap Unit</p>
                </div>

                <button type="submit" class="px-6 py-3 rounded-2xl font-black text-sm uppercase tracking-wider text-white shadow-xl border-2 border-black transition hover:scale-105 flex items-center justify-center gap-2" style="background-color: #2563eb !important;">
                    <i class="fas fa-save text-base"></i>
                    <span>Simpan Rencana Belanja Per Unit</span>
                </button>
            </div>

            {{-- NAV TABS UNIT SEKOLAH --}}
            <div class="flex flex-wrap items-center gap-2 border-b-2 border-black pb-2" id="schoolTabs">
                @foreach($allSchools as $sIdx => $sch)
                    <button type="button" 
                            onclick="switchSchoolTab('school-tab-{{ $sch->id }}', this)" 
                            class="tab-btn px-4 py-2.5 rounded-xl text-xs uppercase tracking-wider {{ $loop->first ? 'active' : 'bg-gray-100 text-gray-800' }}">
                        <i class="fas {{ $sch->type === 'yayasan' ? 'fa-building' : 'fa-school' }} mr-1.5"></i>
                        <span>{{ $sch->name }}</span>
                    </button>
                @endforeach
            </div>

            {{-- TAB CONTENTS --}}
            @foreach($schoolExpenseData as $sId => $sData)
                <div id="school-tab-{{ $sId }}" class="tab-content space-y-5 {{ $loop->first ? '' : 'hidden' }}">
                    <div class="bg-blue-50/60 p-4 rounded-xl border-2 border-blue-200 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-black">
                                <i class="fas {{ $sData['school_type'] === 'yayasan' ? 'fa-building' : 'fa-graduation-cap' }}"></i>
                            </div>
                            <div>
                                <h4 class="font-black text-base text-gray-900">{{ $sData['school_name'] }}</h4>
                                <p class="text-xs text-gray-600 font-bold">Total SDM: {{ $sData['employee_count'] }} Orang | Belanja Pegawai: Rp {{ number_format($sData['total_salary_period'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-bold text-gray-500 uppercase block">Subtotal Belanja Operasional Unit</span>
                            <span class="text-lg font-black text-amber-700 num-col">Rp {{ number_format($sData['total_ops_period'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- TABEL POS REKENING ANGGARAN --}}
                    <div class="overflow-x-auto rounded-xl border-2 border-black">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-gray-900 text-white font-black uppercase tracking-wider border-b-2 border-black">
                                    <th class="p-3 text-center w-12">Kode</th>
                                    <th class="p-3">Pos Rekening / Rencana Belanja</th>
                                    <th class="p-3 text-center w-28">Volume</th>
                                    <th class="p-3 text-center w-28">Satuan</th>
                                    <th class="p-3 text-right w-44">Tarif Satuan (Rp)</th>
                                    <th class="p-3 text-right w-48 bg-amber-900 text-amber-200">Total Periode (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y border-black font-bold">
                                {{-- 1. GAJI PEGAWAI UNIT (READONLY) --}}
                                <tr class="bg-blue-100/70 border-b-2 border-black">
                                    <td class="p-3 text-center font-black font-mono text-blue-900">{{ $sData['salary_item']['code'] }}</td>
                                    <td class="p-3">
                                        <div class="font-black text-blue-900 flex items-center gap-2">
                                            <i class="fas fa-users text-blue-600"></i>
                                            <span>{{ $sData['salary_item']['name'] }}</span>
                                        </div>
                                        <span class="text-[10px] text-blue-700 font-bold">(Otomatis Terkalkulasi dari Sistem Penugasan & Payroll)</span>
                                    </td>
                                    <td class="p-3 text-center font-black">{{ $sData['salary_item']['volume'] }}</td>
                                    <td class="p-3 text-center">{{ $sData['salary_item']['unit'] }}</td>
                                    <td class="p-3 text-right num-col">Rp {{ number_format($sData['salary_item']['tariff'], 0, ',', '.') }}</td>
                                    <td class="p-3 text-right num-col font-black text-blue-900 bg-blue-200/60">Rp {{ number_format($sData['total_salary_period'], 0, ',', '.') }}</td>
                                </tr>

                                {{-- 2. LIST REKENING OPERASIONAL --}}
                                @foreach($sData['ops_details'] as $code => $item)
                                    <tr class="hover:bg-amber-50/50 transition">
                                        <td class="p-3 text-center font-black font-mono text-gray-700">{{ $item['code'] }}</td>
                                        <td class="p-3">
                                            <div class="font-black text-gray-900 flex items-center gap-2">
                                                <i class="fas {{ $item['icon'] }} text-amber-600 w-4 text-center"></i>
                                                <span>{{ $item['name'] }}</span>
                                            </div>
                                            <span class="text-[10px] text-gray-500 font-bold">{{ $item['category'] }}</span>
                                        </td>
                                        <td class="p-3 text-center">
                                            <input type="number" 
                                                   step="1" 
                                                   min="1" 
                                                   name="expense_details[{{ $sId }}][{{ $code }}][volume]" 
                                                   value="{{ $item['volume'] }}" 
                                                   class="w-full text-center p-1.5 rounded-lg rapby-input-pro text-xs">
                                        </td>
                                        <td class="p-3 text-center">
                                            <input type="text" 
                                                   name="expense_details[{{ $sId }}][{{ $code }}][unit]" 
                                                   value="{{ $item['unit'] }}" 
                                                   class="w-full text-center p-1.5 rounded-lg rapby-input-pro text-xs">
                                        </td>
                                        <td class="p-3 text-right">
                                            <input type="number" 
                                                   step="500" 
                                                   min="0" 
                                                   name="expense_details[{{ $sId }}][{{ $code }}][tariff]" 
                                                   value="{{ $item['tariff'] }}" 
                                                   placeholder="0"
                                                   class="w-full text-right p-1.5 rounded-lg rapby-input-pro text-xs num-col">
                                        </td>
                                        <td class="p-3 text-right num-col font-black text-amber-900 bg-amber-50">
                                            Rp {{ number_format($item['amount'] * $multiplier, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-900 text-white font-black text-sm">
                                    <td colspan="5" class="p-3 text-right uppercase">SUBTOTAL OPERASIONAL UNIT {{ $sData['school_name'] }}:</td>
                                    <td class="p-3 text-right num-col text-amber-400 bg-black">Rp {{ number_format($sData['total_ops_period'], 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endforeach

            <div class="pt-4 border-t-2 border-black flex justify-end">
                <button type="submit" class="px-8 py-3.5 rounded-2xl font-black text-sm uppercase tracking-wider text-white shadow-xl border-2 border-black transition hover:scale-105 flex items-center justify-center gap-2" style="background-color: #2563eb !important;">
                    <i class="fas fa-save text-lg"></i>
                    <span>Simpan Rencana Belanja Per Unit Sekolah</span>
                </button>
            </div>
        </div>
    </form>

</div>

@push('scripts')
<script>
    function switchSchoolTab(tabId, btn) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('active');
            el.classList.add('bg-gray-100', 'text-gray-800');
        });

        document.getElementById(tabId).classList.remove('hidden');
        btn.classList.add('active');
        btn.classList.remove('bg-gray-100', 'text-gray-800');
    }
</script>
@endpush
@endsection
