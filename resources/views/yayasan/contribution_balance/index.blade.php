@extends('layouts.yayasan')

@section('title', 'Saldo Kontribusi Unit Sekolah')

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
                    <i class="fas fa-landmark"></i> Oversight Keuangan Yayasan
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Saldo Kontribusi Unit Sekolah</h1>
                <p class="text-xs md:text-sm text-violet-100/90 mt-1 max-w-2xl">
                    Perhitungan real-time kontribusi saldo (+/-) per unit sekolah (Pendapatan SPP - Pengeluaran Gaji & Belanja Otorisasi Yayasan).
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
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Pendapatan -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Pendapatan (SPP)</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <i class="fas fa-wallet text-sm"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-xl font-black text-gray-900">Rp {{ number_format($grandTotalIncome, 0, ',', '.') }}</h3>
                <p class="text-[11px] text-gray-500 mt-0.5">Siswa × SPP ({{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }})</p>
            </div>
        </div>

        <!-- Card 2: Total Gaji Unit -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Gaji Guru & Pegawai</span>
                <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                    <i class="fas fa-users-gear text-sm"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-xl font-black text-gray-900">Rp {{ number_format($grandTotalGaji, 0, ',', '.') }}</h3>
                <p class="text-[11px] text-gray-500 mt-0.5">Otomatis dari Sistem Payroll SDM</p>
            </div>
        </div>

        <!-- Card 3: Belanja Otorisasi -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Belanja Otorisasi Yayasan</span>
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                    <i class="fas fa-hand-holding-dollar text-sm"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-xl font-black text-gray-900">Rp {{ number_format($grandTotalOtorisasi, 0, ',', '.') }}</h3>
                <p class="text-[11px] text-gray-500 mt-0.5">Total Otorisasi yang diinput Yayasan</p>
            </div>
        </div>

        <!-- Card 4: Saldo Kontribusi Akhir -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Saldo Kontribusi Akhir</span>
                <div class="w-9 h-9 rounded-xl {{ $grandTotalSaldo >= 0 ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white' }} flex items-center justify-center shadow">
                    <i class="fas {{ $grandTotalSaldo >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }} text-sm"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-xl font-black {{ $grandTotalSaldo >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                    Rp {{ number_format($grandTotalSaldo, 0, ',', '.') }}
                </h3>
                <div class="flex items-center gap-1.5 mt-1">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $grandTotalSaldo >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                        {{ $grandTotalSaldo >= 0 ? 'SURPLUS' : 'DEFISIT' }}
                    </span>
                    <span class="text-[11px] text-gray-500">Pendapatan - Pengeluaran</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Per School Unit -->
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-extrabold text-gray-800 flex items-center gap-2">
                <i class="fas fa-building-columns text-violet-600"></i> Rincian Kontribusi per Unit Sekolah
            </h2>
            <span class="text-xs text-gray-500">TP {{ $currentYear->year ?? '-' }} ({{ $periodMode === 'annual' ? 'Mode 12 Bulan' : 'Mode 1 Bulan' }})</span>
        </div>

        @foreach($schoolData as $item)
            @php
                $s = $item['school'];
                $c = $item['contribution'];
                $isSurplus = $item['is_surplus'];
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
                                Total Siswa: <strong class="text-gray-700">{{ $item['total_students'] }}</strong> orang | 
                                Guru & Pegawai: <strong class="text-gray-700">{{ $item['employee_count'] }}</strong> orang
                            </p>
                        </div>
                    </div>

                    <!-- Saldo Badge & Action -->
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <span class="text-[10px] text-gray-500 font-semibold uppercase block">Saldo Kontribusi Unit</span>
                            <span class="text-base font-black {{ $isSurplus ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $isSurplus ? '+' : '' }}Rp {{ number_format($item['saldo'], 0, ',', '.') }}
                            </span>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $isSurplus ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-red-100 text-red-800 border border-red-200' }}">
                            {{ $isSurplus ? 'SURPLUS' : 'DEFISIT' }}
                        </span>

                        <button type="button" 
                                onclick="openEditModal({{ $s->id }}, '{{ addslashes($s->name) }}', {{ json_encode($item['levels']) }}, {{ $item['authorized_expense_monthly'] }}, '{{ addslashes($c->notes ?? '') }}', {{ json_encode($item['expense_details']) }})"
                                class="px-3.5 py-2 rounded-xl bg-violet-50 hover:bg-violet-100 text-violet-700 border border-violet-200 font-bold text-xs transition flex items-center gap-1.5 shadow-sm">
                            <i class="fas fa-edit text-xs"></i> Edit Belanja Operasional & SPP
                        </button>
                    </div>
                </div>

                <!-- Unit Content Grid -->
                <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- LEFT COLUMN: PENDAPATAN (SISWA * SPP) -->
                    <div class="bg-gray-50/50 rounded-xl p-4 border border-gray-100">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                                <i class="fas fa-circle-arrow-down text-emerald-500"></i> Pendapatan SPP (Siswa × SPP)
                            </h4>
                            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">
                                Total: Rp {{ number_format($item['income_total'], 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-gray-100 text-gray-600 font-semibold uppercase">
                                    <tr>
                                        <th class="px-3 py-2 rounded-l-lg">Tingkat</th>
                                        <th class="px-3 py-2 text-center">Jumlah Siswa</th>
                                        <th class="px-3 py-2 text-right">SPP / Siswa (Bln)</th>
                                        <th class="px-3 py-2 text-right rounded-r-lg">Subtotal ({{ $periodMode === 'annual' ? '12 Bln' : '1 Bln' }})</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($item['levels'] as $lvl)
                                        <tr class="hover:bg-white transition">
                                            <td class="px-3 py-2 font-bold text-gray-800">Kelas {{ $lvl['level'] }}</td>
                                            <td class="px-3 py-2 text-center font-semibold text-gray-700">{{ $lvl['student_count'] }} siswa</td>
                                            <td class="px-3 py-2 text-right text-gray-600">Rp {{ number_format($lvl['spp_monthly'], 0, ',', '.') }}</td>
                                            <td class="px-3 py-2 text-right font-bold text-gray-900">Rp {{ number_format($lvl['income_total'], 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-3 py-3 text-center text-gray-400 italic">Tidak ada tingkat kelas</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="border-t border-gray-200 font-bold bg-emerald-50/60">
                                    <tr>
                                        <td class="px-3 py-2 text-gray-800">TOTAL PENDAPATAN</td>
                                        <td class="px-3 py-2 text-center text-emerald-800">{{ $item['total_students'] }} siswa</td>
                                        <td class="px-3 py-2 text-right text-gray-500">-</td>
                                        <td class="px-3 py-2 text-right text-emerald-700 text-sm">Rp {{ number_format($item['income_total'], 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: PENGELUARAN (GAJI + OTORISASI) -->
                    <div class="bg-gray-50/50 rounded-xl p-4 border border-gray-100">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                                <i class="fas fa-circle-arrow-up text-red-500"></i> Pengeluaran Unit Sekolah
                            </h4>
                            <span class="text-xs font-bold text-red-700 bg-red-50 px-2 py-0.5 rounded-md border border-red-100">
                                Total: Rp {{ number_format($item['expense_total'], 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="space-y-3 text-xs">
                            <!-- Item 1: Gaji -->
                            <div class="p-3 bg-white rounded-lg border border-gray-200/80 flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                                        <i class="fas fa-user-tie text-xs"></i>
                                    </div>
                                    <div>
                                        <h5 class="font-bold text-gray-800">Gaji Guru & Pegawai Unit</h5>
                                        <p class="text-[11px] text-gray-500">{{ $item['employee_count'] }} pegawai aktif (Otomatis dari Payroll SDM)</p>
                                    </div>
                                </div>
                                <div class="text-right font-bold text-gray-900 text-sm">
                                    Rp {{ number_format($item['salary_total'], 0, ',', '.') }}
                                    <span class="text-[10px] text-gray-400 font-normal block">(Rp {{ number_format($item['salary_monthly'], 0, ',', '.') }}/bln)</span>
                                </div>
                            </div>
                            
                            <!-- Item 2: Belanja Otorisasi & Belanja Operasional -->
                            <div class="p-3 bg-white rounded-lg border border-gray-200/80">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                                            <i class="fas fa-hand-holding-dollar text-xs"></i>
                                        </div>
                                        <div>
                                            <h5 class="font-bold text-gray-800">Belanja Otorisasi & Operasional</h5>
                                            <p class="text-[11px] text-gray-500">Breakdown Kode Rekening Belanja</p>
                                        </div>
                                    </div>
                                    <div class="text-right font-bold text-amber-700 text-sm">
                                        Rp {{ number_format($item['authorized_expense_total'], 0, ',', '.') }}
                                        <span class="text-[10px] text-amber-600/70 font-normal block">(Rp {{ number_format($item['authorized_expense_monthly'], 0, ',', '.') }}/bln)</span>
                                    </div>
                                </div>

                                <!-- Sub-Breakdown per Kode Rekening -->
                                @if(!empty($item['expense_details']) && count($item['expense_details']) > 0)
                                    <div class="mt-2.5 pt-2.5 border-t border-gray-100 space-y-1">
                                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Rincian Per Kode Rekening ({{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }}):</p>
                                        @foreach($item['expense_details'] as $accCode => $accAmount)
                                            @php
                                                $accInfo = $expenseAccounts[$accCode] ?? null;
                                                $accMonthly = (float) $accAmount;
                                                $accPeriod = $accMonthly * $multiplier;
                                            @endphp
                                            @if($accMonthly > 0)
                                                <div class="flex items-center justify-between text-[11px] bg-gray-50 px-2.5 py-1 rounded border border-gray-100">
                                                    <span class="text-gray-700 font-medium truncate max-w-[240px]" title="{{ $accInfo['name'] ?? $accCode }}">
                                                        <strong class="font-mono text-violet-700">{{ $accCode }}</strong> — {{ $accInfo['name'] ?? 'Belanja' }}
                                                    </span>
                                                    <span class="font-bold text-gray-800 tabular-nums">Rp {{ number_format($accPeriod, 0, ',', '.') }}</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Total Summary Box -->
                            <div class="p-3 bg-red-50/60 rounded-lg border border-red-100 flex items-center justify-between font-bold">
                                <span class="text-gray-800">TOTAL PENGELUARAN UNIT</span>
                                <span class="text-red-700 text-sm">Rp {{ number_format($item['expense_total'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer Summary Bar per Unit -->
                <div class="bg-gray-100/60 px-6 py-3 border-t border-gray-200/60 flex items-center justify-between text-xs">
                    <div class="text-gray-600">
                        Rumus: <code class="bg-white px-2 py-0.5 rounded border border-gray-200 font-mono text-[11px]">Saldo = Pendapatan (Rp {{ number_format($item['income_total'], 0, ',', '.') }}) - Pengeluaran (Rp {{ number_format($item['expense_total'], 0, ',', '.') }})</code>
                    </div>
                    <div class="font-black text-sm {{ $isSurplus ? 'text-emerald-700' : 'text-red-700' }}">
                        {{ $isSurplus ? 'SURPLUS (+):' : 'DEFISIT (-):' }} Rp {{ number_format(abs($item['saldo']), 0, ',', '.') }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- ════════════════ TABLE REKAPITULASI KONTRIBUSI SELURUH UNIT SEKOLAH ════════════════ -->
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-md overflow-hidden mt-8">
        <div class="p-6 bg-gradient-to-r from-gray-50 to-gray-100/60 border-b border-gray-200">
            <h2 class="text-lg font-extrabold text-gray-900 flex items-center gap-2">
                <i class="fas fa-table-list text-violet-600"></i> Rekapitulasi Saldo Kontribusi Seluruh Unit Sekolah & Yayasan
            </h2>
            <p class="text-xs text-gray-500 mt-1">
                Matriks perbandingan pendapatan, pengeluaran gaji, belanja otorisasi, dan saldo akhir kontribusi per unit.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-100 text-gray-700 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">No</th>
                        <th class="px-4 py-3">Nama Unit / Lembaga</th>
                        <th class="px-4 py-3 text-center">Siswa</th>
                        <th class="px-4 py-3 text-right">Total Pendapatan SPP</th>
                        <th class="px-4 py-3 text-right">Total Gaji Unit</th>
                        <th class="px-4 py-3 text-right">Belanja Otorisasi</th>
                        <th class="px-4 py-3 text-right">Total Pengeluaran</th>
                        <th class="px-4 py-3 text-right">Saldo Kontribusi</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($schoolData as $idx => $row)
                        @php
                            $surplusRow = $row['is_surplus'];
                        @endphp
                        <tr class="hover:bg-violet-50/40 transition">
                            <td class="px-4 py-3.5 text-center font-bold text-gray-400">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3.5 font-bold text-gray-900 flex items-center gap-2">
                                {{ $row['school']->name }}
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600">
                                    {{ $row['school']->type }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center font-semibold text-gray-700">{{ $row['total_students'] }}</td>
                            <td class="px-4 py-3.5 text-right font-bold text-emerald-700">Rp {{ number_format($row['income_total'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3.5 text-right font-bold text-blue-700">Rp {{ number_format($row['salary_total'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3.5 text-right font-bold text-amber-700">Rp {{ number_format($row['authorized_expense_total'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3.5 text-right font-bold text-red-700">Rp {{ number_format($row['expense_total'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3.5 text-right font-extrabold text-sm {{ $surplusRow ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $surplusRow ? '+' : '' }}Rp {{ number_format($row['saldo'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $surplusRow ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $surplusRow ? 'SURPLUS' : 'DEFISIT' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-violet-900 text-white font-bold border-t-2 border-violet-900">
                    <tr>
                        <td colspan="3" class="px-4 py-4 text-right uppercase tracking-wider font-extrabold">GRAND TOTAL KONTRIBUSI:</td>
                        <td class="px-4 py-4 text-right text-emerald-300 text-sm">Rp {{ number_format($grandTotalIncome, 0, ',', '.') }}</td>
                        <td class="px-4 py-4 text-right text-blue-200 text-sm">Rp {{ number_format($grandTotalGaji, 0, ',', '.') }}</td>
                        <td class="px-4 py-4 text-right text-amber-200 text-sm">Rp {{ number_format($grandTotalOtorisasi, 0, ',', '.') }}</td>
                        <td class="px-4 py-4 text-right text-red-300 text-sm">Rp {{ number_format($grandTotalExpense, 0, ',', '.') }}</td>
                        <td class="px-4 py-4 text-right text-base font-black {{ $grandTotalSaldo >= 0 ? 'text-emerald-300' : 'text-red-300' }}">
                            {{ $grandTotalSaldo >= 0 ? '+' : '' }}Rp {{ number_format($grandTotalSaldo, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="px-3 py-1.5 rounded-full text-xs font-black uppercase tracking-wider {{ $grandTotalSaldo >= 0 ? 'bg-emerald-500 text-white shadow-sm' : 'bg-red-500 text-white shadow-sm' }}">
                                {{ $grandTotalSaldo >= 0 ? 'SURPLUS' : 'DEFISIT' }}
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>


<!-- Modal Input / Edit Belanja Otorisasi & SPP -->
<div id="editModal" class="fixed inset-0 z-50 bg-gray-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-3xl w-full p-6 shadow-2xl border border-gray-100 transform transition-all scale-95 opacity-0 modal-card max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 flex-shrink-0">
            <div>
                <h3 class="text-base font-bold text-gray-900" id="modalSchoolName">Edit Otorisasi & SPP</h3>
                <p class="text-xs text-gray-500 mt-0.5">Input Belanja Otorisasi per Kode Rekening & Tarif SPP</p>
            </div>
            <button onclick="closeEditModal()" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('yayasan.contribution_balance.store') }}" class="mt-4 space-y-4 overflow-y-auto flex-1 pr-1">
            @csrf
            <input type="hidden" name="school_id" id="modalSchoolId">
            <input type="hidden" name="academic_year_id" value="{{ $currentYear->id ?? '' }}">

            <!-- RINCIAN KODE REKENING BELANJA OPERASIONAL -->
            <div class="bg-violet-50/60 p-4 rounded-xl border border-violet-100">
                <div class="flex items-center justify-between mb-3">
                    <label class="block text-xs font-extrabold text-violet-900 uppercase tracking-wider">
                        <i class="fas fa-list-check text-violet-600 mr-1"></i> Rincian Belanja Operasional per Kode Rekening (Per Bulan)
                    </label>
                    <span class="text-xs font-bold text-violet-700 bg-white px-2.5 py-1 rounded-lg border border-violet-200">
                        Subtotal Otorisasi: <strong id="modalExpenseSum">Rp 0</strong>/bln
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-72 overflow-y-auto p-1 bg-white rounded-lg border border-gray-200">
                    @foreach($expenseAccounts as $accCode => $acc)
                        <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-200/80 hover:border-violet-300 transition">
                            <label class="block text-[11px] font-bold text-gray-800 mb-1 truncate" title="{{ $acc['name'] }}">
                                <span class="font-mono text-violet-700 bg-violet-100 px-1.5 py-0.5 rounded text-[10px]">{{ $accCode }}</span> {{ $acc['name'] }}
                            </label>
                            <div class="relative">
                                <span class="absolute left-2.5 top-1.5 text-[11px] font-bold text-gray-400">Rp</span>
                                <input type="number" name="expense_details[{{ $accCode }}]" id="acc_input_{{ str_replace('.', '_', $accCode) }}" step="5000" min="0" 
                                       oninput="calculateTotalExpenseModal()"
                                       class="w-full text-xs font-bold pl-8 pr-2 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 text-gray-900 tabular-nums" 
                                       placeholder="0">
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Fallback Total Otorisasi Manual -->
                <div class="mt-3 pt-3 border-t border-violet-200/80 flex items-center justify-between">
                    <span class="text-xs text-gray-600">Total Otorisasi (Otomatis dari Rincian):</span>
                    <input type="hidden" name="authorized_expense" id="modalAuthorizedExpense">
                    <span class="text-sm font-extrabold text-violet-900" id="displayAuthorizedExpense">Rp 0 / bulan</span>
                </div>
            </div>

            <!-- Tarif SPP per Level -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-2">Tarif SPP Siswa (Per Bulan)</label>
                <div id="modalSppInputs" class="space-y-2.5">
                    <!-- Dynamic inputs injected via Javascript -->
                </div>
            </div>

            <!-- Catatan -->
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Catatan / Keterangan (Opsional)</label>
                <textarea name="notes" id="modalNotes" rows="2" 
                          class="w-full text-xs p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-violet-500" 
                          placeholder="Catatan tambahan alokasi belanja operasional..."></textarea>
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2 flex-shrink-0">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-xl">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-violet-600 hover:bg-violet-700 rounded-xl shadow-md transition flex items-center gap-1.5">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function calculateTotalExpenseModal() {
        let total = 0;
        const inputs = document.querySelectorAll('input[name^="expense_details"]');
        inputs.forEach(function(input) {
            const val = parseFloat(input.value) || 0;
            total += val;
        });
        document.getElementById('modalAuthorizedExpense').value = total;
        document.getElementById('modalExpenseSum').innerText = 'Rp ' + total.toLocaleString('id-ID');
        document.getElementById('displayAuthorizedExpense').innerText = 'Rp ' + total.toLocaleString('id-ID') + ' / bulan';
    }

    function openEditModal(schoolId, schoolName, levels, authorizedExpenseMonthly, notes, expenseDetails) {
        document.getElementById('modalSchoolId').value = schoolId;
        document.getElementById('modalSchoolName').innerText = 'Edit Belanja Operasional & SPP — ' + schoolName;
        document.getElementById('modalAuthorizedExpense').value = authorizedExpenseMonthly;
        document.getElementById('modalNotes').value = notes || '';

        // Reset & Populate Kode Rekening inputs
        const details = expenseDetails || {};
        const inputs = document.querySelectorAll('input[name^="expense_details"]');
        inputs.forEach(function(input) {
            const match = input.name.match(/\[(.*?)\]/);
            if (match && match[1]) {
                const code = match[1];
                input.value = details[code] ? details[code] : '';
            }
        });

        calculateTotalExpenseModal();

        // Populate SPP Inputs
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
            sppContainer.innerHTML = '<p class="text-xs text-gray-400 italic p-2 bg-gray-50 rounded-lg">Unit ini tidak memungut SPP siswa.</p>';
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
