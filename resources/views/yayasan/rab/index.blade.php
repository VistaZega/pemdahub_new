@extends('layouts.yayasan')

@section('title', 'Rencana Anggaran Belanja (RAB) Yayasan')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-purple-700 via-indigo-800 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-xs font-medium text-purple-200 mb-3">
                    <i class="fas fa-calculator text-amber-300"></i> Perencanaan Keuangan Yayasan & Unit Sekolah
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Rencana Anggaran Belanja (RAB) Yayasan</h1>
                <p class="text-purple-200 text-sm mt-1 max-w-2xl">
                    Rencana Pendapatan SPP Siswa, Rencana Belanja Gaji/Honor Pegawai, dan 8 Item Belanja Operasional Unit Sekolah & Yayasan.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('yayasan.rab.export_pdf', ['academic_year_id' => $activeYear->id ?? '', 'period_mode' => $periodMode]) }}" 
                   target="_blank"
                   class="inline-flex items-center gap-2 bg-rose-500 hover:bg-rose-600 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-lg transition transform hover:-translate-y-0.5">
                    <i class="fas fa-file-pdf"></i> Export PDF RAB
                </a>
            </div>
        </div>
    </div>

    <!-- Filter & Mode Switcher -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('yayasan.rab.index') }}" class="flex flex-wrap items-center gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Tahun Pelajaran</label>
                <select name="academic_year_id" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold text-gray-700 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ ($activeYear->id ?? null) == $year->id ? 'selected' : '' }}>
                            TP {{ $year->year }} {{ $year->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Mode Periode</label>
                <div class="inline-flex rounded-xl bg-gray-100 p-1 border border-gray-200">
                    <a href="{{ route('yayasan.rab.index', ['academic_year_id' => $activeYear->id ?? '', 'period_mode' => 'annual']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $periodMode === 'annual' ? 'bg-purple-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        Tahunan (12 Bulan)
                    </a>
                    <a href="{{ route('yayasan.rab.index', ['academic_year_id' => $activeYear->id ?? '', 'period_mode' => 'monthly']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $periodMode === 'monthly' ? 'bg-purple-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        Bulanan (1 Bulan)
                    </a>
                </div>
            </div>
        </form>

        <div class="text-xs text-gray-500 font-medium flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Mode Tampilan: <strong class="text-gray-800">{{ $periodMode === 'annual' ? 'Rencana 1 Tahun Pelajaran (12 Bulan)' : 'Rencana 1 Bulan' }}</strong>
        </div>
    </div>

    <!-- Ringkasan Konsolidasi Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- Total Rencana Pendapatan -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 relative overflow-hidden group hover:border-emerald-200 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Pendapatan SPP</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight">
                    Rp {{ number_format($summary['total_income'], 0, ',', '.') }}
                </div>
                <div class="text-xs text-emerald-600 font-medium mt-1">
                    Total Siswa Aktif: {{ number_format($summary['total_students'], 0, ',', '.') }} orang
                </div>
            </div>
        </div>

        <!-- Total Rencana Belanja Gaji -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 relative overflow-hidden group hover:border-indigo-200 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Rencana Gaji & Honor</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-user-tie"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight">
                    Rp {{ number_format($summary['total_salary'], 0, ',', '.') }}
                </div>
                <div class="text-xs text-gray-400 font-medium mt-1">Honor Mengajar & Tunjangan Jabatan</div>
            </div>
        </div>

        <!-- Total Rencana Operasional -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 relative overflow-hidden group hover:border-amber-200 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Belanja Operasional (8 Item)</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-boxes-packing"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight">
                    Rp {{ number_format($summary['total_operational'], 0, ',', '.') }}
                </div>
                <div class="text-xs text-amber-600 font-medium mt-1">Otorisasi, BPJS, Admin, dll.</div>
            </div>
        </div>

        <!-- Saldo Rencana -->
        <div class="bg-gradient-to-br {{ $summary['total_balance'] >= 0 ? 'from-purple-900 to-indigo-900' : 'from-rose-900 to-red-900' }} text-white rounded-2xl p-5 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-white/70 uppercase tracking-wider">Saldo Rencana {{ $periodMode === 'annual' ? '(Tahunan)' : '(Bulanan)' }}</span>
                <div class="w-9 h-9 rounded-xl bg-white/10 text-white flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-scale-balanced"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-xl sm:text-2xl font-black tracking-tight">
                    Rp {{ number_format($summary['total_balance'], 0, ',', '.') }}
                </div>
                <div class="text-xs text-white/70 font-medium mt-1">
                    {{ $summary['total_balance'] >= 0 ? 'Surplus Rencana Anggaran' : 'Defisit Rencana Anggaran' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Rencana Anggaran Belanja (RAB) Per Unit Sekolah -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50">
            <div>
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-table text-purple-600"></i> Matriks RAB Masing-Masing Unit Sekolah & Yayasan
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Rincian pendapatan SPP, beban kerja/gaji, dan 8 item belanja operasional per unit sekolah</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-gray-100/70 text-gray-700 font-bold uppercase tracking-wider border-b border-gray-200">
                        <th class="py-3.5 px-4 min-w-[180px]">Unit Sekolah / Lembaga</th>
                        <th class="py-3.5 px-4 text-center">Siswa Aktif</th>
                        <th class="py-3.5 px-4 text-right">Tarif SPP/Bulan</th>
                        <th class="py-3.5 px-4 text-right bg-emerald-50/50 text-emerald-900">A. Pendapatan SPP</th>
                        <th class="py-3.5 px-4 text-right">1. Honor & Tunjangan</th>
                        <th class="py-3.5 px-4 text-right">2. Operasional Unit</th>
                        <th class="py-3.5 px-4 text-right bg-rose-50/50 text-rose-900">B. Total Belanja</th>
                        <th class="py-3.5 px-4 text-right bg-purple-50/50 text-purple-900">C. Saldo Rencana</th>
                        <th class="py-3.5 px-4 text-center">Aksi / Setting</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($schoolRabList as $item)
                        <tr class="hover:bg-purple-50/30 transition">
                            <td class="py-4 px-4 font-bold text-gray-900">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-black text-xs shadow-sm">
                                        {{ strtoupper(substr($item['school']->type ?? $item['school']->name, 0, 3)) }}
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-gray-900">{{ $item['school']->name }}</div>
                                        <div class="text-[10px] text-gray-400 font-normal">Jenjang: {{ strtoupper($item['school']->type ?? '-') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-center font-semibold text-gray-700">
                                {{ number_format($item['student_count'], 0, ',', '.') }} orang
                            </td>
                            <td class="py-4 px-4 text-right font-medium text-gray-600">
                                Rp {{ number_format($item['spp_monthly_rate'], 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-right font-bold text-emerald-700 bg-emerald-50/30">
                                Rp {{ number_format($item['rab_income_period'], 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-right font-semibold text-gray-700">
                                Rp {{ number_format($item['rab_salary_period'], 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-right font-semibold text-amber-700">
                                Rp {{ number_format($item['rab_operational_period'], 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-right font-bold text-rose-700 bg-rose-50/30">
                                Rp {{ number_format($item['rab_total_expense_period'], 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-right font-black {{ $item['rab_balance_period'] >= 0 ? 'text-purple-800 bg-purple-50/40' : 'text-rose-800 bg-rose-100/50' }}">
                                Rp {{ number_format($item['rab_balance_period'], 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-center">
                                <button type="button" 
                                        onclick="openSettingModal({{ $item['school']->id }}, '{{ addslashes($item['school']->name) }}', '{{ strtolower($item['school']->type ?? 'smk') }}', {{ $item['spp_monthly_rate'] }}, {{ json_encode($item['itemised_monthly']) }})"
                                        class="inline-flex items-center gap-1.5 bg-purple-100 hover:bg-purple-200 text-purple-800 text-[11px] font-bold px-3 py-1.5 rounded-lg transition">
                                    <i class="fas fa-sliders text-[10px]"></i> Edit Setting
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gradient-to-r from-purple-900 to-indigo-900 text-white font-black text-xs border-t-2 border-purple-950">
                        <td class="py-4 px-4">TOTAL KONSOLIDASI YAYASAN</td>
                        <td class="py-4 px-4 text-center">{{ number_format($summary['total_students'], 0, ',', '.') }}</td>
                        <td class="py-4 px-4 text-right">—</td>
                        <td class="py-4 px-4 text-right text-emerald-300">Rp {{ number_format($summary['total_income'], 0, ',', '.') }}</td>
                        <td class="py-4 px-4 text-right text-indigo-200">Rp {{ number_format($summary['total_salary'], 0, ',', '.') }}</td>
                        <td class="py-4 px-4 text-right text-amber-300">Rp {{ number_format($summary['total_operational'], 0, ',', '.') }}</td>
                        <td class="py-4 px-4 text-right text-rose-300">Rp {{ number_format($summary['total_expense'], 0, ',', '.') }}</td>
                        <td class="py-4 px-4 text-right text-amber-200 text-sm">Rp {{ number_format($summary['total_balance'], 0, ',', '.') }}</td>
                        <td class="py-4 px-4 text-center">—</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Rincian 8 Item Belanja Operasional Per Unit Sekolah -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
        <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fas fa-list-check text-amber-500"></i> Breakdown 8 Item Belanja Operasional Per Unit Sekolah
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($operationalItems as $itemKey => $itemInfo)
                <div class="bg-gray-50 rounded-2xl p-4 border border-gray-200 hover:border-amber-300 transition">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xs font-bold">
                            <i class="fas {{ $itemInfo['icon'] }}"></i>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-800">{{ $itemInfo['name'] }}</div>
                            <div class="text-[10px] text-gray-400">{{ $itemInfo['desc'] }}</div>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1.5 text-xs border-t border-gray-200 pt-2">
                        @foreach($schoolRabList as $sItem)
                            <div class="flex items-center justify-between text-gray-600">
                                <span>{{ $sItem['school']->name }}:</span>
                                <span class="font-bold text-gray-800">
                                    Rp {{ number_format(($sItem['itemised_monthly'][$itemKey] ?? 0) * $multiplier, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Modal Edit Setting RAB & 8 Item Operasional -->
<div id="settingModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-xl w-full overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="modalContainer">
        <div class="bg-gradient-to-r from-purple-700 to-indigo-800 px-6 py-4 text-white flex items-center justify-between">
            <h3 class="font-bold text-base flex items-center gap-2">
                <i class="fas fa-sliders text-amber-300"></i> Setting RAB Unit: <span id="modalSchoolName" class="text-amber-200"></span>
            </h3>
            <button type="button" onclick="closeSettingModal()" class="text-white/70 hover:text-white text-lg"><i class="fas fa-xmark"></i></button>
        </div>

        <form action="{{ route('yayasan.rab.store') }}" method="POST" class="p-6 space-y-5">
            @csrf
            <input type="hidden" name="school_id" id="modalSchoolId">
            <input type="hidden" name="academic_year_id" value="{{ $activeYear->id ?? '' }}">

            <!-- Tarif SPP Per Siswa -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">
                    <i class="fas fa-money-bill text-emerald-600 mr-1"></i> Tarif SPP (Uang Sekolah) Per Siswa / Bulan
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xs">Rp</span>
                    <input type="number" name="spp_rates[smk]" id="modalSppRate" min="0" step="1000"
                           class="w-full bg-gray-50 border border-gray-300 rounded-xl pl-9 pr-4 py-2 text-xs font-bold text-gray-800 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                <p class="text-[10px] text-gray-400 mt-1">Tarif ini dikalikan jumlah siswa aktif untuk menghitung Rencana Pendapatan SPP.</p>
            </div>

            <!-- 8 Item Belanja Operasional Unit -->
            <div class="border-t border-gray-100 pt-4">
                <h4 class="text-xs font-extrabold text-gray-800 mb-3 flex items-center gap-1.5">
                    <i class="fas fa-boxes-packing text-amber-500"></i> Setting Nominal 8 Item Belanja Operasional / Bulan
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($operationalItems as $itemKey => $itemInfo)
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 mb-1 flex items-center gap-1">
                                <i class="fas {{ $itemInfo['icon'] }} text-amber-500 text-[10px]"></i> {{ $itemInfo['name'] }}
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-[11px]">Rp</span>
                                <input type="number" name="expense_details[{{ $itemKey }}]" id="modalItem_{{ $itemKey }}" min="0" step="5000"
                                       class="w-full bg-gray-50 border border-gray-300 rounded-xl pl-8 pr-3 py-1.5 text-xs font-semibold text-gray-800 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Buttons -->
            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                <button type="button" onclick="closeSettingModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-purple-700 hover:bg-purple-800 shadow-md transition">
                    Simpan RAB Unit
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openSettingModal(schoolId, schoolName, schoolType, sppRate, itemised) {
        document.getElementById('modalSchoolId').value = schoolId;
        document.getElementById('modalSchoolName').innerText = schoolName;

        const sppInput = document.getElementById('modalSppRate');
        sppInput.name = `spp_rates[${schoolType}]`;
        sppInput.value = sppRate;

        // Set 8 items
        for (const [key, val] of Object.entries(itemised || {})) {
            const input = document.getElementById(`modalItem_${key}`);
            if (input) {
                input.value = val;
            }
        }

        const backdrop = document.getElementById('settingModal');
        const container = document.getElementById('modalContainer');

        backdrop.classList.remove('hidden');
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeSettingModal() {
        const backdrop = document.getElementById('settingModal');
        const container = document.getElementById('modalContainer');

        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            backdrop.classList.add('hidden');
        }, 200);
    }
</script>
@endsection
