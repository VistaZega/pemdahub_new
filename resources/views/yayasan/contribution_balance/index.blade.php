@extends('layouts.yayasan')

@section('title', 'Rencana Pendapatan SPP Unit Sekolah (Saldo Kontribusi)')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700;800;900&display=swap" rel="stylesheet">

<style>
    /* 100% SOLID COLORS - NO OPACITY TRANSPARENCY */
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

    /* MENCEGAH TEKS NOMINAL TERPISAH DENGAN Rp */
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
    .stat-card-pro.blue::before { background-color: #1d4ed8; }
    .stat-card-pro.violet::before { background-color: #7c3aed; }

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

    <!-- Flash Message -->
    @if(session('success'))
        <div class="p-4 rounded-2xl border-2 border-black text-black flex items-center justify-between shadow-md" style="background-color: #6ee7b7 !important;">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black border-2 border-black shrink-0" style="background-color: #000000 !important; color: #ffffff !important;">
                    <i class="fas fa-check text-white"></i>
                </div>
                <div>
                    <h4 class="font-black text-sm text-black">Berhasil!</h4>
                    <p class="text-xs font-black text-black">{{ session('success') }}</p>
                </div>
            </div>
            <button onclick="this.parentElement.remove()" class="text-black hover:text-red-600 font-black">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
    @endif

    <!-- Header Banner & Filter -->
    <div class="contrib-hero rounded-3xl p-6 md:p-8 text-white relative overflow-hidden">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black shadow-lg border-2 border-black shrink-0" style="background-color: #fbbf24 !important; color: #000000 !important;">
                        <i class="fas fa-school text-2xl text-black"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Rencana Pendapatan SPP</h1>
                            <span class="pro-badge border-2 border-black" style="background-color: #fbbf24 !important; color: #000000 !important;">Halaman 1</span>
                        </div>
                        <p class="text-amber-400 text-sm font-black tracking-wide">Kontribusi Pendapatan SPP Unit Sekolah</p>
                    </div>
                </div>
                <p class="text-white text-xs font-black max-w-xl leading-relaxed">
                    Perhitungan Potensi Rencana Pendapatan SPP Siswa Per Unit Sekolah Berdasarkan Tarif Tingkat Kelas dengan Kontras Solid 100%.
                </p>
            </div>
            
            <!-- Actions & Filters -->
            <form method="GET" action="{{ route('yayasan.contribution_balance.index') }}" class="flex flex-wrap items-center gap-3">
                <div class="flex flex-col">
                    <span class="text-[11px] uppercase tracking-wider font-black text-amber-400 mb-1">Tahun Pelajaran</span>
                    <select name="academic_year_id" onchange="this.form.submit()" class="bg-black text-white border-2 border-amber-400 rounded-xl text-xs px-3.5 py-2.5 font-black focus:ring-2 focus:ring-amber-400 min-w-[170px]">
                        @foreach($allYears as $y)
                            <option value="{{ $y->id }}" class="bg-black text-white font-black" {{ ($currentYear->id ?? null) == $y->id ? 'selected' : '' }}>
                                TP {{ $y->year }} {{ $y->is_active ? '✦ Aktif' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col">
                    <span class="text-[11px] uppercase tracking-wider font-black text-amber-400 mb-1">Mode Periode</span>
                    <div class="bg-black p-1 rounded-xl border-2 border-slate-700 flex items-center gap-1">
                        <a href="{{ route('yayasan.contribution_balance.index', ['academic_year_id' => $currentYear->id ?? null, 'period_mode' => 'annual']) }}"
                           class="px-4 py-2 rounded-lg text-xs font-black transition-all" style="{{ $periodMode === 'annual' ? 'background-color: #fbbf24 !important; color: #000000 !important; border: 2px solid #000000;' : 'color: #ffffff;' }}">
                            <i class="fas fa-calendar-days mr-1.5"></i> 12 Bulan
                        </a>
                        <a href="{{ route('yayasan.contribution_balance.index', ['academic_year_id' => $currentYear->id ?? null, 'period_mode' => 'monthly']) }}"
                           class="px-4 py-2 rounded-lg text-xs font-black transition-all" style="{{ $periodMode === 'monthly' ? 'background-color: #fbbf24 !important; color: #000000 !important; border: 2px solid #000000;' : 'color: #ffffff;' }}">
                            <i class="fas fa-calendar-day mr-1.5"></i> 1 Bulan
                        </a>
                    </div>
                </div>

                <div class="flex flex-col justify-end pt-5">
                    <a href="{{ route('yayasan.contribution_balance.export_pdf', ['academic_year_id' => $currentYear->id ?? null, 'period_mode' => $periodMode]) }}" 
                       target="_blank"
                       class="px-4 py-2.5 text-white font-black text-xs rounded-xl shadow-lg transition flex items-center gap-2 border-2 border-black" style="background-color: #059669 !important;">
                        <i class="fas fa-file-pdf text-sm text-white"></i> Export PDF Laporan
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <!-- Card 1: Total Pendapatan SPP -->
        <div class="stat-card-pro green contrib-card-pro rounded-2xl p-5 shadow-md">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-black border-2 border-black shrink-0" style="background-color: #059669 !important; color: #ffffff !important;">
                    <i class="fas fa-wallet text-2xl text-white"></i>
                </div>
                <div>
                    <p class="text-xs text-black font-black uppercase tracking-wider">Total Rencana SPP Perguruan</p>
                    <p class="text-2xl font-black text-black mt-0.5 num-col whitespace-nowrap">Rp&nbsp;{{ number_format($grandTotalIncome, 0, ',', '.') }}</p>
                    <p class="text-xs font-black text-black mt-0.5 num-col whitespace-nowrap">
                        1 Bulan: <span class="text-emerald-950 font-black">Rp&nbsp;{{ number_format(array_sum(array_column($schoolData, 'income_monthly')), 0, ',', '.') }}</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Siswa -->
        <div class="stat-card-pro blue contrib-card-pro rounded-2xl p-5 shadow-md">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-black border-2 border-black shrink-0" style="background-color: #1d4ed8 !important; color: #ffffff !important;">
                    <i class="fas fa-user-graduate text-2xl text-white"></i>
                </div>
                <div>
                    <p class="text-xs text-black font-black uppercase tracking-wider">Total Siswa Terdaftar</p>
                    <p class="text-2xl font-black text-black mt-0.5 num-col whitespace-nowrap">
                        {{ array_sum(array_column($schoolData, 'total_students')) }} Siswa
                    </p>
                    <span class="pro-badge border-2 border-black mt-1" style="background-color: #1d4ed8 !important; color: #ffffff !important;">
                        <i class="fas fa-users text-[10px] text-white"></i> Seluruh Unit Sekolah
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 3: Jumlah Unit Sekolah -->
        <div class="stat-card-pro violet contrib-card-pro rounded-2xl p-5 shadow-md border-2 border-black" style="background-color: #f3e8ff !important;">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-black border-2 border-black shrink-0" style="background-color: #581c87 !important; color: #ffffff !important;">
                    <i class="fas fa-school text-2xl text-white"></i>
                </div>
                <div>
                    <p class="text-xs text-black font-black uppercase tracking-wider">Jumlah Unit Sekolah</p>
                    <p class="text-2xl font-black text-black mt-0.5 num-col whitespace-nowrap">{{ count($schoolData) }} Unit Sekolah</p>
                    <span class="pro-badge border-2 border-black mt-1" style="background-color: #581c87 !important; color: #ffffff !important;">
                        <i class="fas fa-building text-[10px] text-white"></i> Unit Pendidikan Aktif
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Per School Unit -->
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-black text-black flex items-center gap-2">
                <i class="fas fa-building-columns text-black"></i> Rincian Pendapatan SPP per Unit Sekolah
            </h2>
            <span class="pro-badge border-2 border-black" style="background-color: #000000 !important; color: #ffffff !important;">
                TP {{ $currentYear->year ?? '-' }} • Mode {{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }}
            </span>
        </div>

        @foreach($schoolData as $item)
            @php
                $s = $item['school'];
                $c = $item['contribution'];
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

                        <button type="button" 
                                onclick="openEditModal({{ $s->id }}, '{{ addslashes($s->name) }}', {{ json_encode($item['levels']) }}, '{{ addslashes($c->notes ?? '') }}')"
                                class="px-4 py-2.5 rounded-xl text-black border-2 border-black font-black text-xs transition flex items-center gap-2 shadow-md"
                                style="background-color: #fbbf24 !important;">
                            <i class="fas fa-pen-to-square text-xs text-black"></i> Edit Tarif SPP Unit
                        </button>
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
                                <th class="px-4 py-3.5 text-center w-48 text-xs uppercase font-black text-white">Sumber Tarif</th>
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
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="pro-badge border border-black" style="background-color: #e2e8f0 !important; color: #000000 !important;">
                                            {{ $lvl['spp_source'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-black text-black num-col whitespace-nowrap">Rp&nbsp;{{ number_format($lvl['income_monthly'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3.5 text-right font-black text-black num-col whitespace-nowrap">Rp&nbsp;{{ number_format($lvl['income_total'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-4 text-center font-black text-black italic">Belum ada tingkat kelas/siswa terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-black font-black" style="background-color: #a7f3d0 !important;">
                                <td class="px-4 py-4 text-black uppercase font-black">TOTAL PENDAPATAN {{ strtoupper($s->name) }}</td>
                                <td class="px-4 py-4 text-center text-black font-black">{{ $item['total_students'] }} Siswa</td>
                                <td colspan="2" class="px-4 py-4 text-right text-black font-black">-</td>
                                <td class="px-4 py-4 text-right text-black font-black num-col whitespace-nowrap">Rp&nbsp;{{ number_format($item['income_monthly'], 0, ',', '.') }}</td>
                                <td class="px-4 py-4 text-right text-black font-black text-sm num-col whitespace-nowrap">Rp&nbsp;{{ number_format($item['income_total'], 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    <!-- TABLE REKAPITULASI PENDAPATAN SPP SELURUH UNIT SEKOLAH -->
    <div class="contrib-card-pro rounded-3xl shadow-2xl overflow-hidden border-2 border-black mt-8">
        <div class="contrib-hero p-5 flex items-center justify-between border-b-2 border-black">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shadow-md border-2 border-black shrink-0" style="background-color: #fbbf24 !important; color: #000000 !important;">
                    <i class="fas fa-table-list text-xl text-black"></i>
                </div>
                <div>
                    <h2 class="text-lg font-black text-white">Rekapitulasi Pendapatan SPP Seluruh Unit Sekolah</h2>
                    <p class="text-amber-400 text-xs font-black">Matriks Perbandingan Pendapatan SPP Antar Unit Sekolah Periode {{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }}</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-black text-white border-b-2 border-black">
                        <th class="px-4 py-4 text-center w-14 text-xs uppercase font-black text-white">No</th>
                        <th class="px-4 py-4 text-xs uppercase font-black text-white">Nama Unit Sekolah</th>
                        <th class="px-4 py-4 text-center w-40 text-xs uppercase font-black text-white">Jumlah Siswa</th>
                        <th class="px-4 py-4 text-right w-56 text-xs uppercase font-black text-white whitespace-nowrap">Pendapatan SPP / Bulan</th>
                        <th class="px-4 py-4 text-right w-64 text-xs uppercase font-black text-white whitespace-nowrap">Total Pendapatan SPP ({{ $periodMode === 'annual' ? '12 Bln' : '1 Bln' }})</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-slate-300 bg-white">
                    @foreach($schoolData as $idx => $row)
                        <tr class="hover:bg-amber-100 transition-all border-b border-slate-300">
                            <td class="px-4 py-4 text-center font-black text-black">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-black text-white text-xs font-black">{{ $idx + 1 }}</span>
                            </td>
                            <td class="px-4 py-4 font-black text-black">
                                <div class="flex items-center gap-2">
                                    <span>{{ $row['school']->name }}</span>
                                    <span class="pro-badge border border-black" style="background-color: #fbbf24 !important; color: #000000 !important;">
                                        {{ $row['school']->type }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center font-black text-black num-col">{{ $row['total_students'] }} Siswa</td>
                            <td class="px-4 py-4 text-right font-black text-black num-col whitespace-nowrap">Rp&nbsp;{{ number_format($row['income_monthly'], 0, ',', '.') }}</td>
                            <td class="px-4 py-4 text-right font-black text-black text-sm num-col whitespace-nowrap">Rp&nbsp;{{ number_format($row['income_total'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-black text-white font-black border-t-4 border-black text-sm">
                        <td colspan="3" class="px-6 py-5 text-right uppercase tracking-widest font-black text-amber-400">GRAND TOTAL PENDAPATAN SPP PERGURUAN:</td>
                        <td class="px-4 py-5 text-right num-col whitespace-nowrap text-amber-300 font-black">Rp&nbsp;{{ number_format(array_sum(array_column($schoolData, 'income_monthly')), 0, ',', '.') }}</td>
                        <td class="px-4 py-5 text-right num-col whitespace-nowrap text-emerald-400 font-black text-xl">Rp&nbsp;{{ number_format($grandTotalIncome, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>

<!-- Modal Input / Edit Tarif SPP -->
<div id="editModal" class="fixed inset-0 z-50 bg-black/80 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border-2 border-black transform transition-all scale-95 opacity-0 modal-card flex flex-col">
        <div class="flex items-center justify-between pb-4 border-b-2 border-black">
            <div>
                <h3 class="text-base font-black text-black" id="modalSchoolName">Edit Tarif SPP</h3>
                <p class="text-xs font-black text-black mt-0.5">Penetapan Tarif SPP per Tingkat Kelas Unit Sekolah</p>
            </div>
            <button onclick="closeEditModal()" class="w-8 h-8 rounded-xl bg-black text-white hover:bg-amber-400 hover:text-black flex items-center justify-center font-black">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('yayasan.contribution_balance.store') }}" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="school_id" id="modalSchoolId">
            <input type="hidden" name="academic_year_id" value="{{ $currentYear->id ?? '' }}">

            <div>
                <label class="block text-xs font-black text-black mb-2 uppercase tracking-wider">Tarif SPP Siswa (Per Bulan)</label>
                <div id="modalSppInputs" class="space-y-3 max-h-60 overflow-y-auto pr-1">
                    <!-- Dynamic inputs injected via Javascript -->
                </div>
            </div>

            <div>
                <label class="block text-xs font-black text-black mb-1 uppercase tracking-wider">Catatan / Keterangan (Opsional)</label>
                <textarea name="notes" id="modalNotes" rows="2" 
                          class="rapby-input-pro w-full text-xs p-3 rounded-2xl bg-white text-black font-black border-2 border-black" 
                          placeholder="Catatan tambahan penetapan SPP..."></textarea>
            </div>

            <div class="pt-3 border-t-2 border-black flex items-center justify-end gap-3">
                <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 text-xs font-black text-black hover:bg-slate-200 rounded-xl border-2 border-black">
                    Batal
                </button>
                <button type="submit" class="px-6 py-2.5 text-xs font-black text-black rounded-xl shadow-md transition flex items-center gap-2 border-2 border-black" style="background-color: #fbbf24 !important;">
                    <i class="fas fa-save text-black"></i> Simpan Tarif SPP
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openEditModal(schoolId, schoolName, levels, notes) {
        document.getElementById('modalSchoolId').value = schoolId;
        document.getElementById('modalSchoolName').innerText = 'Edit Tarif SPP — ' + schoolName;
        document.getElementById('modalNotes').value = notes || '';

        const sppContainer = document.getElementById('modalSppInputs');
        sppContainer.innerHTML = '';

        if (levels && levels.length > 0) {
            levels.forEach(function(lvl) {
                const div = document.createElement('div');
                div.className = 'flex items-center justify-between gap-3 bg-amber-50 p-3 rounded-2xl border-2 border-black';
                div.innerHTML = `
                    <span class="text-xs font-black text-black">Kelas ${lvl.level} (${lvl.student_count} siswa):</span>
                    <div class="relative w-48">
                        <span class="absolute left-3 top-2.5 text-xs font-black text-black">Rp</span>
                        <input type="number" name="spp_rates[${lvl.level}]" value="${lvl.spp_monthly}" step="1000" min="0"
                               class="rapby-input-pro w-full text-xs font-black pl-9 pr-3 py-2 rounded-xl bg-white text-black num-col">
                    </div>
                `;
                sppContainer.appendChild(div);
            });
        } else {
            sppContainer.innerHTML = '<p class="text-xs text-black font-black italic p-3 bg-slate-100 rounded-xl border-2 border-black">Belum ada kelas terdaftar pada unit ini.</p>';
        }

        const modal = document.getElementById('editModal');
        const card = modal.querySelector('.modal-card');
        modal.classList.remove('hidden');
        setTimeout(() => {
            card.classList.remove('scale-95', 'opacity-0');
            card.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        const card = modal.querySelector('.modal-card');
        card.classList.remove('scale-100', 'opacity-100');
        card.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 150);
    }
</script>
@endpush
@endsection
