@extends('layouts.yayasan')

@section('title', 'Rencana Anggaran Belanja (RAB) Yayasan')

@push('styles')
<style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: #f3f4f6;
    }
    .dark-hero {
        background-color: #090d16;
        border: 2px solid #000000;
    }
    .pro-card {
        background-color: #ffffff;
        border: 2px solid #000000;
        box-shadow: 4px 4px 0px #000000;
        border-radius: 8px;
    }
    .pro-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        border: 2px solid #000000;
        border-radius: 8px;
        overflow: hidden;
    }
    .pro-table th {
        background-color: #000000;
        color: #ffffff;
        font-weight: 900;
        text-transform: uppercase;
        padding: 12px 16px;
        border-bottom: 2px solid #000000;
        border-right: 2px solid #333333;
    }
    .pro-table th:last-child {
        border-right: none;
    }
    .pro-table td {
        background-color: #ffffff;
        color: #000000;
        font-weight: 900;
        padding: 12px 16px;
        border-bottom: 2px solid #000000;
        border-right: 2px solid #000000;
    }
    .pro-table td:last-child {
        border-right: none;
    }
    .pro-table tbody tr:last-child td {
        border-bottom: none;
    }
    .pro-table tbody tr:hover td {
        background-color: #fef3c7; /* hover:bg-amber-100 */
    }
    /* Footer & inline-styled rows must override default td colors */
    .pro-table tfoot tr td {
        background-color: #000000 !important;
        color: #ffffff !important;
    }
    .pro-table .subtotal-row td {
        background-color: inherit !important;
        color: inherit !important;
    }
    .pro-table .subtotal-green td {
        background-color: #d1fae5 !important;
        color: #000000 !important;
    }
    .pro-table .subtotal-red td {
        background-color: #fee2e2 !important;
        color: #991b1b !important;
    }
    .pro-badge {
        display: inline-block;
        padding: 4px 8px;
        border: 2px solid #000000;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 900;
        color: #000000;
        box-shadow: 2px 2px 0px #000000;
    }
    .badge-yellow { background-color: #fbbf24; }
    .badge-green { background-color: #34d399; }
    .badge-blue { background-color: #60a5fa; }
    .badge-red { background-color: #f87171; }
    .badge-purple { background-color: #c084fc; }

    .num-col {
        text-align: right;
        font-family: 'Plus Jakarta Sans', monospace;
    }
    .tab-btn {
        border: 2px solid #000000;
        background-color: #ffffff;
        color: #000000;
        font-weight: 900;
        padding: 8px 16px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .tab-btn.active {
        background-color: #fbbf24;
        box-shadow: 3px 3px 0px #000000;
        transform: translate(-3px, -3px);
    }
    .pro-input {
        border: 2px solid #000000;
        background-color: #ffffff;
        color: #000000;
        font-weight: 900;
        border-radius: 4px;
        padding: 8px 12px;
        width: 100%;
        box-shadow: inset 2px 2px 0px rgba(0,0,0,0.05);
    }
    .pro-input:focus {
        outline: none;
        background-color: #fef3c7;
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    <!-- Hero Banner -->
    <div class="dark-hero rounded-xl p-8 text-white relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black uppercase mb-2">Rencana Anggaran Belanja (RAB) Yayasan</h1>
                <p class="text-gray-300 font-bold text-sm">
                    Rekapitulasi Pendapatan, Belanja Gaji, dan Belanja Operasional Unit.
                </p>
            </div>
            
            <div class="flex flex-col sm:flex-row items-end sm:items-center gap-4 bg-white/10 p-4 border-2 border-white rounded-lg">
                <form method="GET" action="{{ route('yayasan.rab.index') }}" class="flex items-center gap-3">
                    <div>
                        <label class="block text-xs font-black mb-1 uppercase" style="color: #fbbf24 !important;">Tahun Pelajaran</label>
                        <select name="academic_year_id" onchange="this.form.submit()" class="border-2 border-black rounded px-3 py-1.5 text-xs font-black cursor-pointer shadow-sm" style="background-color: #ffffff !important; color: #000000 !important;">
                            @foreach($allYears as $year)
                                <option value="{{ $year->id }}" style="background-color: #ffffff !important; color: #000000 !important;" {{ ($currentYear->id ?? null) == $year->id ? 'selected' : '' }}>
                                    TP {{ $year->year }} {{ $year->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black mb-1 uppercase" style="color: #fbbf24 !important;">Periode</label>
                        <select name="period_mode" onchange="this.form.submit()" class="border-2 border-black rounded px-3 py-1.5 text-xs font-black cursor-pointer shadow-sm" style="background-color: #ffffff !important; color: #000000 !important;">
                            <option value="annual" style="background-color: #ffffff !important; color: #000000 !important;" {{ $periodMode == 'annual' ? 'selected' : '' }}>12 Bulan (Tahunan)</option>
                            <option value="monthly" style="background-color: #ffffff !important; color: #000000 !important;" {{ $periodMode == 'monthly' ? 'selected' : '' }}>1 Bulan (Bulanan)</option>
                        </select>
                    </div>
                </form>
                
                <a href="{{ route('yayasan.rab.export_pdf', ['academic_year_id' => $currentYear->id ?? '', 'period_mode' => $periodMode]) }}" 
                   target="_blank"
                   class="bg-[#ff0000] text-white font-black px-4 py-2 border-2 border-white hover:bg-white hover:text-[#ff0000] hover:border-[#ff0000] transition-colors rounded text-sm uppercase">
                    <i class="fas fa-file-pdf mr-1"></i> Export PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="pro-card p-6 border-b-8 border-b-green-500">
            <div class="text-xs font-black text-gray-500 uppercase mb-2">Total Pendapatan</div>
            <div class="text-3xl font-black text-black">Rp {{ number_format($summary['total_income'], 0, ',', '.') }}</div>
        </div>
        <div class="pro-card p-6 border-b-8 border-b-red-500">
            <div class="text-xs font-black text-gray-500 uppercase mb-2">Total Belanja (Gaji + Ops)</div>
            <div class="text-3xl font-black text-black">Rp {{ number_format($summary['total_expense'], 0, ',', '.') }}</div>
        </div>
        <div class="pro-card p-6 border-b-8 {{ $summary['total_balance'] >= 0 ? 'border-b-blue-500' : 'border-b-red-500' }}">
            <div class="text-xs font-black text-gray-500 uppercase mb-2">Saldo / Balance</div>
            <div class="text-3xl font-black text-black">Rp {{ number_format($summary['total_balance'], 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- 1. Per-school SPP income table -->
    <div class="pro-card p-6">
        <h2 class="text-xl font-black uppercase mb-4 flex items-center justify-between">
            <span><i class="fas fa-coins text-yellow-500 mr-2"></i> Rencana Pendapatan SPP</span>
            <span class="pro-badge badge-green text-sm">TOTAL: Rp {{ number_format($summary['total_income'], 0, ',', '.') }}</span>
        </h2>
        
        <div class="overflow-x-auto">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th>Unit Sekolah</th>
                        <th>Jenjang/Tingkat</th>
                        <th class="text-center">Siswa Aktif</th>
                        <th class="text-right">Tarif SPP/Bulan</th>
                        <th class="text-right">Total ({{ $periodMode == 'annual' ? '12 Bln' : '1 Bln' }})</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($incomeData as $schoolId => $data)
                        @foreach($data['levels'] as $index => $lvl)
                            <tr>
                                @if($index == 0)
                                    <td rowspan="{{ count($data['levels']) + 1 }}" class="align-top">
                                        <div class="font-black text-lg">{{ $data['school']->name }}</div>
                                        <div class="text-xs text-gray-500 mt-1 uppercase">{{ $data['school']->type }}</div>
                                    </td>
                                @endif
                                <td>Kelas {{ $lvl['level'] }}</td>
                                <td class="text-center">{{ $lvl['student_count'] }}</td>
                                <td class="num-col">Rp&nbsp;{{ number_format($lvl['spp_monthly_rate'], 0, ',', '.') }}</td>
                                <td class="num-col text-green-700 bg-green-50">Rp&nbsp;{{ number_format($lvl['income_period'], 0, ',', '.') }}</td>
                                @if($index == 0)
                                    <td rowspan="{{ count($data['levels']) + 1 }}" class="text-center align-middle">
                                        <button type="button" 
                                                onclick="openSppModal({{ $schoolId }}, '{{ addslashes($data['school']->name) }}', {{ json_encode($data['levels']) }})"
                                                class="pro-badge badge-yellow cursor-pointer hover:bg-yellow-300">
                                            <i class="fas fa-edit mr-1"></i> Edit SPP
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        <tr class="subtotal-green" style="border-top: 2px solid #000;">
                            <td class="uppercase font-black text-right" colspan="2">TOTAL {{ $data['school']->name }}</td>
                            <td class="num-col font-black" style="color: #166534 !important;">Rp&nbsp;{{ number_format($data['total_income_period'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. Per-school expense table -->
    <div class="pro-card p-6">
        <h2 class="text-xl font-black uppercase mb-4 flex items-center justify-between">
            <span><i class="fas fa-money-check-dollar text-red-500 mr-2"></i> Rencana Belanja (Gaji & Operasional)</span>
            <span class="pro-badge badge-red text-sm">TOTAL: Rp {{ number_format($summary['total_expense'], 0, ',', '.') }}</span>
        </h2>

        <div class="flex flex-wrap gap-2 mb-4 border-b-2 border-black pb-2" id="expense-tabs">
            @foreach($expenseData as $schoolId => $data)
                <button class="tab-btn {{ $loop->first ? 'active' : '' }}" onclick="switchExpenseTab({{ $schoolId }})" id="tab-btn-{{ $schoolId }}">
                    {{ $data['school']->name }}
                </button>
            @endforeach
        </div>

        @foreach($expenseData as $schoolId => $data)
            <div id="tab-content-{{ $schoolId }}" class="expense-tab-content {{ $loop->first ? 'block' : 'hidden' }}">
                <div class="flex justify-between items-center mb-4">
                    <div class="font-black text-lg uppercase bg-black text-white inline-block px-3 py-1">
                        {{ $data['school']->name }}
                    </div>
                    <button type="button" 
                            onclick="openOpsModal({{ $schoolId }}, '{{ addslashes($data['school']->name) }}', {{ json_encode($data['ops_details']) }})"
                            class="pro-badge badge-purple cursor-pointer hover:bg-purple-300 text-sm">
                        <i class="fas fa-edit mr-1"></i> Edit Operasional
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="pro-table">
                        <thead>
                            <tr>
                                <th class="w-16 text-center">KODE</th>
                                <th>URAIAN BELANJA</th>
                                <th class="text-center">VOL</th>
                                <th class="text-center">SATUAN</th>
                                <th class="text-right">TARIF</th>
                                <th class="text-right">JUMLAH ({{ $periodMode == 'annual' ? '12 Bln' : '1 Bln' }})</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Gaji -->
                            <tr class="bg-gray-100">
                                <td colspan="6" class="uppercase font-black text-sm">A. BELANJA PEGAWAI (AUTO)</td>
                            </tr>
                            <tr>
                                <td class="text-center font-bold text-gray-500">{{ $data['salary_item']['code'] }}</td>
                                <td>
                                    <i class="fas {{ $data['salary_item']['icon'] }} mr-2 text-gray-500"></i>
                                    {{ $data['salary_item']['name'] }}
                                </td>
                                <td class="text-center">{{ $data['salary_item']['volume'] }}</td>
                                <td class="text-center">{{ $data['salary_item']['unit'] }}</td>
                                <td class="num-col">Rp&nbsp;{{ number_format($data['salary_item']['tariff'], 0, ',', '.') }}</td>
                                <td class="num-col text-red-700 bg-red-50">Rp&nbsp;{{ number_format($data['total_salary_period'], 0, ',', '.') }}</td>
                            </tr>

                            <!-- Operasional -->
                            <tr class="bg-gray-100">
                                <td colspan="6" class="uppercase font-black text-sm">B. BELANJA OPERASIONAL</td>
                            </tr>
                            @foreach($data['ops_details'] as $code => $detail)
                                <tr>
                                    <td class="text-center font-bold text-gray-500">{{ $code }}</td>
                                    <td>
                                        <i class="fas {{ $detail['icon'] }} mr-2 text-gray-500"></i>
                                        {{ $detail['name'] }}
                                    </td>
                                    <td class="text-center">{{ $detail['volume'] }}</td>
                                    <td class="text-center">{{ $detail['unit'] }}</td>
                                    <td class="num-col">Rp&nbsp;{{ number_format($detail['tariff'], 0, ',', '.') }}</td>
                                    <td class="num-col text-red-700 bg-red-50">Rp&nbsp;{{ number_format($detail['amount'] * $multiplier, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            
                            <!-- Subtotal -->
                            <tr class="subtotal-red" style="border-top: 2px solid #000;">
                                <td colspan="5" class="uppercase font-black text-right">TOTAL BELANJA {{ $data['school']->name }}</td>
                                <td class="num-col font-black">Rp&nbsp;{{ number_format($data['grand_total_period'], 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    <!-- 3. Grand total recap table -->
    <div class="pro-card p-6">
        <h2 class="text-xl font-black uppercase mb-4 flex items-center justify-between">
            <span><i class="fas fa-scale-balanced text-blue-500 mr-2"></i> Rekapitulasi Akhir (Surplus/Defisit)</span>
        </h2>
        
        <div class="overflow-x-auto">
            <table class="pro-table">
                <thead>
                    <tr>
                        <th>UNIT SEKOLAH</th>
                        <th class="text-right">TOTAL PENDAPATAN</th>
                        <th class="text-right">TOTAL BELANJA</th>
                        <th class="text-right">SALDO / BALANCE</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allSchools as $sch)
                        @php
                            $inc = isset($incomeData[$sch->id]) ? $incomeData[$sch->id]['total_income_period'] : 0;
                            $exp = isset($expenseData[$sch->id]) ? $expenseData[$sch->id]['grand_total_period'] : 0;
                            $bal = $inc - $exp;
                        @endphp
                        <tr>
                            <td class="font-black uppercase">{{ $sch->name }}</td>
                            <td class="num-col text-green-700">Rp&nbsp;{{ number_format($inc, 0, ',', '.') }}</td>
                            <td class="num-col text-red-700">Rp&nbsp;{{ number_format($exp, 0, ',', '.') }}</td>
                            <td class="num-col font-black {{ $bal >= 0 ? 'text-blue-700 bg-blue-50' : 'text-red-700 bg-red-50' }}">
                                Rp&nbsp;{{ number_format($bal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td class="font-black uppercase" style="background-color: #000 !important; color: #fbbf24 !important;">GRAND TOTAL YAYASAN</td>
                        <td class="num-col font-black" style="background-color: #000 !important; color: #34d399 !important;">Rp&nbsp;{{ number_format($summary['total_income'], 0, ',', '.') }}</td>
                        <td class="num-col font-black" style="background-color: #000 !important; color: #f87171 !important;">Rp&nbsp;{{ number_format($summary['total_expense'], 0, ',', '.') }}</td>
                        <td class="num-col font-black text-lg" style="background-color: #000 !important; color: #fbbf24 !important;">Rp&nbsp;{{ number_format($summary['total_balance'], 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Modal SPP -->
<div id="sppModal" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center hidden">
    <div class="pro-card max-w-lg w-full m-4">
        <div class="bg-black text-white px-6 py-4 flex justify-between items-center border-b-2 border-black">
            <h3 class="font-black uppercase">Setting SPP: <span id="sppSchoolName" class="text-yellow-400"></span></h3>
            <button onclick="closeSppModal()" class="text-white hover:text-red-500 font-black text-xl">&times;</button>
        </div>
        <form action="{{ route('yayasan.rab.store') }}" method="POST" class="p-6">
            @csrf
            <input type="hidden" name="school_id" id="sppSchoolId">
            <input type="hidden" name="academic_year_id" value="{{ $currentYear->id ?? '' }}">
            
            <div id="sppLevelsContainer" class="space-y-4 mb-6">
                <!-- Injected via JS -->
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t-2 border-black">
                <button type="button" onclick="closeSppModal()" class="px-4 py-2 border-2 border-black font-black uppercase hover:bg-gray-100">Batal</button>
                <button type="submit" class="px-4 py-2 bg-yellow-400 border-2 border-black font-black uppercase hover:bg-yellow-500">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Operasional -->
<div id="opsModal" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center hidden overflow-y-auto py-10">
    <div class="pro-card max-w-3xl w-full m-4">
        <div class="bg-black text-white px-6 py-4 flex justify-between items-center border-b-2 border-black">
            <h3 class="font-black uppercase">Setting Operasional: <span id="opsSchoolName" class="text-purple-400"></span></h3>
            <button onclick="closeOpsModal()" class="text-white hover:text-red-500 font-black text-xl">&times;</button>
        </div>
        <form action="{{ route('yayasan.rab.store') }}" method="POST" class="p-6">
            @csrf
            <input type="hidden" name="school_id" id="opsSchoolId">
            <input type="hidden" name="academic_year_id" value="{{ $currentYear->id ?? '' }}">
            
            <div class="overflow-x-auto mb-6 border-2 border-black">
                <table class="w-full text-left" style="border-collapse: collapse;">
                    <thead>
                        <tr class="bg-gray-100 border-b-2 border-black">
                            <th class="p-2 border-r-2 border-black font-black uppercase text-xs">Uraian</th>
                            <th class="p-2 border-r-2 border-black font-black uppercase text-xs w-20">Volume</th>
                            <th class="p-2 border-r-2 border-black font-black uppercase text-xs w-24">Satuan</th>
                            <th class="p-2 font-black uppercase text-xs w-40">Tarif (Rp)</th>
                        </tr>
                    </thead>
                    <tbody id="opsItemsContainer">
                        <!-- Injected via JS -->
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t-2 border-black">
                <button type="button" onclick="closeOpsModal()" class="px-4 py-2 border-2 border-black font-black uppercase hover:bg-gray-100">Batal</button>
                <button type="submit" class="px-4 py-2 bg-purple-400 border-2 border-black font-black uppercase hover:bg-purple-500">Simpan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Tab Switching for Expenses
    function switchExpenseTab(schoolId) {
        document.querySelectorAll('.expense-tab-content').forEach(el => {
            el.classList.remove('block');
            el.classList.add('hidden');
        });
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('active');
        });
        
        document.getElementById('tab-content-' + schoolId).classList.remove('hidden');
        document.getElementById('tab-content-' + schoolId).classList.add('block');
        document.getElementById('tab-btn-' + schoolId).classList.add('active');
    }

    // SPP Modal
    function openSppModal(schoolId, schoolName, levelsData) {
        document.getElementById('sppSchoolId').value = schoolId;
        document.getElementById('sppSchoolName').innerText = schoolName;
        
        let html = '';
        levelsData.forEach(lvl => {
            html += `
                <div>
                    <label class="block text-sm font-black uppercase mb-1">Tarif SPP Kelas ${lvl.level}</label>
                    <div class="flex items-center">
                        <span class="border-y-2 border-l-2 border-black bg-gray-100 px-3 py-2 font-black">Rp</span>
                        <input type="number" name="spp_rates[${lvl.level}]" value="${lvl.spp_monthly_rate}" 
                               class="pro-input flex-1 !border-l-0" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                    </div>
                </div>
            `;
        });
        
        document.getElementById('sppLevelsContainer').innerHTML = html;
        document.getElementById('sppModal').classList.remove('hidden');
    }

    function closeSppModal() {
        document.getElementById('sppModal').classList.add('hidden');
    }

    // Ops Modal
    function openOpsModal(schoolId, schoolName, opsDetails) {
        document.getElementById('opsSchoolId').value = schoolId;
        document.getElementById('opsSchoolName').innerText = schoolName;
        
        let html = '';
        Object.entries(opsDetails).forEach(([code, detail]) => {
            html += `
                <tr class="border-b-2 border-black">
                    <td class="p-2 border-r-2 border-black">
                        <div class="font-black text-sm">${detail.name}</div>
                        <div class="text-xs text-gray-500">${code}</div>
                    </td>
                    <td class="p-2 border-r-2 border-black">
                        <input type="number" step="0.01" name="expense_details[${code}][volume]" value="${detail.volume}" class="pro-input !p-1 text-center text-sm">
                    </td>
                    <td class="p-2 border-r-2 border-black">
                        <input type="text" name="expense_details[${code}][unit]" value="${detail.unit}" class="pro-input !p-1 text-center text-sm">
                    </td>
                    <td class="p-2">
                        <input type="number" name="expense_details[${code}][tariff]" value="${detail.tariff}" class="pro-input !p-1 text-right text-sm">
                    </td>
                </tr>
            `;
        });
        
        document.getElementById('opsItemsContainer').innerHTML = html;
        document.getElementById('opsModal').classList.remove('hidden');
    }

    function closeOpsModal() {
        document.getElementById('opsModal').classList.add('hidden');
    }
</script>
@endpush
