@extends('layouts.yayasan')

@section('title', 'Rencana Pendapatan SPP Unit Sekolah')

@section('content')
<div class="space-y-6">

    <!-- Flash Message -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm animate-fade-in">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-500 text-white flex items-center justify-center font-bold">
                    <i class="fas fa-check"></i>
                </div>
                <div>
                    <h4 class="font-bold text-sm">Berhasil!</h4>
                    <p class="text-xs text-emerald-700">{{ session('success') }}</p>
                </div>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- Header Banner & Filter -->
    <div class="bg-gradient-to-r from-violet-700 via-purple-700 to-indigo-800 rounded-2xl p-6 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-white/90 text-xs font-medium mb-2 backdrop-blur-md">
                    <i class="fas fa-school"></i> Unit Pendidikan Yayasan
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Rencana Pendapatan SPP Unit Sekolah</h1>
                <p class="text-xs md:text-sm text-violet-100/90 mt-1 max-w-2xl">
                    Perhitungan real-time potensi dan rencana pendapatan SPP siswa per unit sekolah berdasarkan tarif tingkat kelas.
                </p>
            </div>
            
            <!-- Actions -->
            <div class="flex items-center gap-2">
                <a href="{{ route('yayasan.contribution_balance.export_pdf', ['academic_year_id' => $currentYear->id ?? null, 'period_mode' => $periodMode]) }}" 
                   class="px-4 py-2.5 rounded-xl bg-white text-violet-700 hover:bg-violet-50 font-bold text-xs shadow-md transition flex items-center gap-2">
                    <i class="fas fa-file-pdf text-red-500 text-sm"></i> Export PDF Laporan
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <form method="GET" action="{{ route('yayasan.contribution_balance.index') }}" class="mt-6 pt-4 border-t border-white/15 grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-semibold text-violet-200 mb-1">Tahun Pelajaran</label>
                <select name="academic_year_id" onchange="this.form.submit()" class="w-full text-xs font-semibold bg-white/10 border border-white/20 text-white rounded-xl px-3 py-2 focus:bg-violet-900/80 focus:outline-none">
                    @foreach($allYears as $y)
                        <option value="{{ $y->id }}" class="bg-gray-800 text-white" {{ ($currentYear->id ?? null) == $y->id ? 'selected' : '' }}>
                            TP {{ $y->year }} {{ $y->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-violet-200 mb-1">Mode Perhitungan Periode</label>
                <select name="period_mode" onchange="this.form.submit()" class="w-full text-xs font-semibold bg-white/10 border border-white/20 text-white rounded-xl px-3 py-2 focus:bg-violet-900/80 focus:outline-none">
                    <option value="annual" class="bg-gray-800 text-white" {{ $periodMode === 'annual' ? 'selected' : '' }}>Tahunan (12 Bulan / Full Year)</option>
                    <option value="monthly" class="bg-gray-800 text-white" {{ $periodMode === 'monthly' ? 'selected' : '' }}>Bulanan (1 Bulan)</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-white/20 hover:bg-white/30 text-white font-bold text-xs rounded-xl transition flex items-center justify-center gap-2 border border-white/20">
                    <i class="fas fa-filter"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <!-- Card 1: Total Pendapatan SPP -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Pendapatan SPP (Seluruh Sekolah)</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <i class="fas fa-wallet text-base"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-black text-emerald-900">Rp {{ number_format($grandTotalIncome, 0, ',', '.') }}</h3>
                <p class="text-[11px] text-gray-500 mt-0.5">Pendapatan SPP Siswa ({{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }})</p>
            </div>
        </div>

        <!-- Card 2: Total Siswa -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Siswa Terdaftar</span>
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                    <i class="fas fa-user-graduate text-base"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-black text-blue-900">
                    {{ array_sum(array_column($schoolData, 'total_students')) }} Siswa
                </h3>
                <p class="text-[11px] text-gray-500 mt-0.5">Akumulasi Seluruh Unit Sekolah</p>
            </div>
        </div>

        <!-- Card 3: Jumlah Unit Sekolah -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Jumlah Unit Sekolah</span>
                <div class="w-10 h-10 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center">
                    <i class="fas fa-school text-base"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-black text-violet-900">{{ count($schoolData) }} Unit Sekolah</h3>
                <p class="text-[11px] text-gray-500 mt-0.5">Unit Pendidikan Aktif Perguruan</p>
            </div>
        </div>
    </div>

    <!-- Details Per School Unit -->
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-extrabold text-gray-800 flex items-center gap-2">
                <i class="fas fa-building-columns text-violet-600"></i> Rincian Pendapatan SPP per Unit Sekolah
            </h2>
            <span class="text-xs text-gray-500">TP {{ $currentYear->year ?? '-' }} ({{ $periodMode === 'annual' ? 'Mode 12 Bulan' : 'Mode 1 Bulan' }})</span>
        </div>

        @foreach($schoolData as $item)
            @php
                $s = $item['school'];
                $c = $item['contribution'];
            @endphp
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm hover:shadow-md transition overflow-hidden">
                <!-- Unit Header Bar -->
                <div class="bg-gray-50/80 px-6 py-4 border-b border-gray-200/70 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-600 to-purple-800 text-white flex items-center justify-center font-black text-sm shadow">
                            {{ strtoupper(substr($s->type, 0, 3)) }}
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                {{ $s->name }}
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-violet-100 text-violet-700">
                                    {{ $s->type }}
                                </span>
                            </h3>
                            <p class="text-xs text-gray-500">
                                Total Siswa: <strong class="text-gray-700">{{ $item['total_students'] }}</strong> orang
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <span class="text-[10px] text-gray-500 font-semibold uppercase block">Total Pendapatan Unit</span>
                            <span class="text-base font-black text-emerald-700">
                                Rp {{ number_format($item['income_total'], 0, ',', '.') }}
                            </span>
                        </div>

                        <button type="button" 
                                onclick="openEditModal({{ $s->id }}, '{{ addslashes($s->name) }}', {{ json_encode($item['levels']) }}, '{{ addslashes($c->notes ?? '') }}')"
                                class="px-3.5 py-2 rounded-xl bg-violet-50 hover:bg-violet-100 text-violet-700 border border-violet-200 font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                            <i class="fas fa-edit text-xs"></i> Edit Tarif SPP Unit
                        </button>
                    </div>
                </div>

                <!-- Unit Content Grid -->
                <div class="p-6">
                    <div class="overflow-x-auto border border-gray-200 rounded-xl">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-100 text-gray-700 font-bold uppercase">
                                <tr>
                                    <th class="px-4 py-3">Tingkat Kelas</th>
                                    <th class="px-4 py-3 text-center">Jumlah Siswa</th>
                                    <th class="px-4 py-3 text-right">Tarif SPP / Siswa (Bln)</th>
                                    <th class="px-4 py-3 text-right">Sumber Tarif</th>
                                    <th class="px-4 py-3 text-right">Pendapatan Per Bulan</th>
                                    <th class="px-4 py-3 text-right font-bold">Subtotal ({{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }})</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($item['levels'] as $lvl)
                                    <tr class="hover:bg-violet-50/20 transition">
                                        <td class="px-4 py-3 font-bold text-gray-900">Kelas {{ $lvl['level'] }}</td>
                                        <td class="px-4 py-3 text-center font-semibold text-gray-700">{{ $lvl['student_count'] }} siswa</td>
                                        <td class="px-4 py-3 text-right text-gray-700 font-semibold">Rp {{ number_format($lvl['spp_monthly'], 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600">
                                                {{ $lvl['spp_source'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold text-gray-800">Rp {{ number_format($lvl['income_monthly'], 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-right font-bold text-emerald-700">Rp {{ number_format($lvl['income_total'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-4 text-center text-gray-400 italic">Belum ada tingkat kelas/siswa terdaftar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="border-t border-gray-200 font-bold bg-emerald-50/60">
                                <tr>
                                    <td class="px-4 py-3 text-gray-900 uppercase">TOTAL PENDAPATAN {{ strtoupper($s->name) }}</td>
                                    <td class="px-4 py-3 text-center text-emerald-800">{{ $item['total_students'] }} siswa</td>
                                    <td colspan="2" class="px-4 py-3 text-right text-gray-500">-</td>
                                    <td class="px-4 py-3 text-right text-emerald-800">Rp {{ number_format($item['income_monthly'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right text-emerald-900 font-black text-sm">Rp {{ number_format($item['income_total'], 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- TABLE REKAPITULASI PENDAPATAN SPP SELURUH UNIT SEKOLAH -->
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-md overflow-hidden mt-8">
        <div class="p-6 bg-gradient-to-r from-gray-50 to-gray-100/60 border-b border-gray-200">
            <h2 class="text-lg font-extrabold text-gray-900 flex items-center gap-2">
                <i class="fas fa-table-list text-violet-600"></i> Rekapitulasi Pendapatan SPP Seluruh Unit Sekolah
            </h2>
            <p class="text-xs text-gray-500 mt-1">
                Matriks perbandingan pendapatan SPP antar unit sekolah untuk periode {{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }}.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-100 text-gray-700 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">No</th>
                        <th class="px-4 py-3">Nama Unit Sekolah</th>
                        <th class="px-4 py-3 text-center">Jumlah Siswa</th>
                        <th class="px-4 py-3 text-right">Pendapatan SPP / Bulan</th>
                        <th class="px-4 py-3 text-right">Total Pendapatan SPP ({{ $periodMode === 'annual' ? '12 Bln' : '1 Bln' }})</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($schoolData as $idx => $row)
                        <tr class="hover:bg-violet-50/40 transition">
                            <td class="px-4 py-3.5 text-center font-bold text-gray-400">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3.5 font-bold text-gray-900 flex items-center gap-2">
                                {{ $row['school']->name }}
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600">
                                    {{ $row['school']->type }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center font-semibold text-gray-700">{{ $row['total_students'] }} siswa</td>
                            <td class="px-4 py-3.5 text-right font-bold text-gray-800">Rp {{ number_format($row['income_monthly'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3.5 text-right font-extrabold text-sm text-emerald-700">Rp {{ number_format($row['income_total'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-violet-900 text-white font-bold border-t-2 border-violet-900">
                    <tr>
                        <td colspan="3" class="px-4 py-4 text-right uppercase tracking-wider font-extrabold">GRAND TOTAL PENDAPATAN SPP PERGURUAN:</td>
                        <td class="px-4 py-4 text-right text-emerald-300 text-sm">Rp {{ number_format(array_sum(array_column($schoolData, 'income_monthly')), 0, ',', '.') }}</td>
                        <td class="px-4 py-4 text-right text-base font-black text-emerald-300">Rp {{ number_format($grandTotalIncome, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>

<!-- Modal Input / Edit Tarif SPP -->
<div id="editModal" class="fixed inset-0 z-50 bg-gray-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 transform transition-all scale-95 opacity-0 modal-card flex flex-col">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
            <div>
                <h3 class="text-base font-bold text-gray-900" id="modalSchoolName">Edit Tarif SPP</h3>
                <p class="text-xs text-gray-500 mt-0.5">Penetapan Tarif SPP per Tingkat Kelas Unit Sekolah</p>
            </div>
            <button onclick="closeEditModal()" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('yayasan.contribution_balance.store') }}" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="school_id" id="modalSchoolId">
            <input type="hidden" name="academic_year_id" value="{{ $currentYear->id ?? '' }}">

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-2">Tarif SPP Siswa (Per Bulan)</label>
                <div id="modalSppInputs" class="space-y-2.5 max-h-60 overflow-y-auto pr-1">
                    <!-- Dynamic inputs injected via Javascript -->
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Catatan / Keterangan (Opsional)</label>
                <textarea name="notes" id="modalNotes" rows="2" 
                          class="w-full text-xs p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-violet-500" 
                          placeholder="Catatan tambahan penetapan SPP..."></textarea>
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-xl">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-violet-600 hover:bg-violet-700 rounded-xl shadow-md transition flex items-center gap-1.5">
                    <i class="fas fa-save"></i> Simpan Tarif SPP
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
                div.className = 'flex items-center justify-between gap-3 bg-gray-50 p-2.5 rounded-xl border border-gray-200';
                div.innerHTML = `
                    <span class="text-xs font-bold text-gray-700">Kelas ${lvl.level} (${lvl.student_count} siswa):</span>
                    <div class="relative w-48">
                        <span class="absolute left-3 top-2 text-xs font-bold text-gray-400">Rp</span>
                        <input type="number" name="spp_rates[${lvl.level}]" value="${lvl.spp_monthly}" step="1000" min="0"
                               class="w-full text-xs font-bold pl-9 pr-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500">
                    </div>
                `;
                sppContainer.appendChild(div);
            });
        } else {
            sppContainer.innerHTML = '<p class="text-xs text-gray-400 italic p-2 bg-gray-50 rounded-lg">Belum ada kelas terdaftar pada unit ini.</p>';
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
