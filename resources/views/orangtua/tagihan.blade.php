@extends('layouts.orangtua')
@section('title', 'Checklist Pembayaran SPP '.$student->full_name.' - Portal Orang Tua')

@section('content')
@php
    $monthNames = [
        7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
    ];
    $fullMonthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
@endphp

<div class="space-y-6">
    @include('orangtua.partials.child-header', ['student' => $student, 'classroom' => $classroom, 'active' => 'tagihan'])

    {{-- Filter Academic Year --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-clipboard-check text-emerald-600"></i> Checklist Pembayaran SPP & Biaya Pendidikan
            </h1>
            <p class="text-xs text-gray-500 mt-0.5">Rekapitulasi checklist status pembayaran bulanan siswa (Sudah Lunas / Belum Lunas)</p>
        </div>

        <form method="GET" action="{{ route('orangtua.anak.tagihan', $student->id) }}" class="flex items-center gap-2">
            <label class="text-xs font-bold text-gray-400 uppercase whitespace-nowrap"><i class="fas fa-calendar-alt mr-1"></i> Tahun Pelajaran:</label>
            <select name="academic_year_id" onchange="this.form.submit()" class="w-full sm:w-64 border border-gray-200 rounded-xl px-3 py-1.5 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white shadow-sm">
                <option value="">Semua Tahun Pelajaran</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" {{ $selectedYearId == $year->id ? 'selected' : '' }}>
                        TP {{ $year->year }} {{ $year->is_active ? '(Aktif)' : '' }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Summary Cards (Status Checklist Counts Only - No Nominal Uang) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Periode Tagihan</p>
            <p class="text-2xl font-extrabold text-gray-800 mt-1">{{ $totalBillsCount ?? $bills->count() }} <span class="text-sm font-semibold text-gray-500">Periode</span></p>
            <p class="text-[11px] text-gray-400 mt-1">Akumulasi bulan pada TP terpilih</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 p-5">
            <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Sudah Lunas (✓)</p>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $lunasCount ?? 0 }} <span class="text-sm font-semibold">Bulan</span></p>
            <p class="text-[11px] text-gray-400 mt-1">Status pembayaran terverifikasi lunas</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border-2 {{ ($tunggakanCount ?? 0) > 0 ? 'border-rose-200 bg-rose-50/30' : 'border-emerald-200 bg-emerald-50/30' }} p-5">
            <p class="text-xs font-bold {{ ($tunggakanCount ?? 0) > 0 ? 'text-rose-600' : 'text-emerald-600' }} uppercase tracking-wider">Belum Lunas s.d. Bulan Ini</p>
            <p class="text-2xl font-extrabold {{ ($tunggakanCount ?? 0) > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">
                @if(($tunggakanCount ?? 0) > 0)
                    {{ $tunggakanCount }} <span class="text-sm font-semibold">Bulan (✕)</span>
                @else
                    ✓ <span class="text-base font-extrabold">Nihil (Lunas)</span>
                @endif
            </p>
            <p class="text-[11px] text-gray-500 mt-1">{{ ($tunggakanCount ?? 0) > 0 ? 'Bulan berjalan/sebelumnya yang belum lunas' : 'Seluruh kewajiban s.d. bulan ini sudah lunas' }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-amber-100 p-5">
            <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Periode Mendatang</p>
            <p class="text-2xl font-extrabold text-amber-500 mt-1">{{ $upcomingCount ?? 0 }} <span class="text-sm font-semibold">Bulan</span></p>
            <p class="text-[11px] text-gray-400 mt-1">Bulan berikutnya (belum jatuh tempo)</p>
        </div>
    </div>

    {{-- Monthly Recurring Payment Checklist Matrix --}}
    @if(isset($recurringTypes) && $recurringTypes->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-calendar-check text-emerald-500"></i> Checklist Status Pembayaran Bulanan (SPP)
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">Tanda <strong class="text-emerald-600">✓ (Hijau)</strong> menandakan SPP bulan tersebut sudah dibayar / lunas</p>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-xs">
                <span class="inline-flex items-center gap-1.5 font-semibold text-emerald-700">
                    <span class="w-4 h-4 rounded bg-emerald-500 text-white inline-flex items-center justify-center text-[10px] font-bold">✓</span> Sudah Bayar (Lunas)
                </span>
                <span class="inline-flex items-center gap-1.5 font-semibold text-rose-700">
                    <span class="w-4 h-4 rounded bg-rose-500 text-white inline-flex items-center justify-center text-[10px] font-bold">✕</span> Belum Bayar (Jatuh Tempo)
                </span>
                <span class="inline-flex items-center gap-1.5 font-semibold text-amber-700">
                    <span class="w-4 h-4 rounded bg-amber-400 text-white inline-flex items-center justify-center text-[10px] font-bold">•</span> Bulan Mendatang
                </span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 z-10 min-w-[150px]">Jenis Pembayaran</th>
                        @foreach($months as $m)
                            <th class="text-center px-2 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider min-w-[64px]">{{ $monthNames[$m] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($recurringTypes as $typeId => $typeName)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 font-semibold text-gray-800 sticky left-0 bg-white z-10">{{ $typeName }}</td>
                        @foreach($months as $m)
                            @php
                                $key = $typeId . '_' . $m;
                                $bill = $monthMap[$key] ?? null;
                            @endphp
                            <td class="px-2 py-3 text-center">
                                @if($bill)
                                    @php
                                        $isPaid = $bill->status === 'lunas';
                                        $isPartial = $bill->status === 'cicilan';
                                        $isOverdue = !$isPaid && $bill->isOverdue();
                                    @endphp
                                    @if($isPaid)
                                        <div class="inline-flex flex-col items-center" title="{{ $typeName }} - {{ $fullMonthNames[$m] }} {{ $bill->year }}: SUDAH LUNAS">
                                            <span class="w-8 h-8 rounded-lg bg-emerald-500 text-white flex items-center justify-center text-sm font-bold shadow-sm">✓</span>
                                            <span class="text-[10px] text-emerald-600 font-bold mt-0.5">Lunas</span>
                                        </div>
                                    @elseif($isPartial)
                                        <div class="inline-flex flex-col items-center" title="{{ $typeName }} - {{ $fullMonthNames[$m] }} {{ $bill->year }}: CICILAN">
                                            <span class="w-8 h-8 rounded-lg bg-blue-500 text-white flex items-center justify-center text-xs font-bold shadow-sm">½</span>
                                            <span class="text-[10px] text-blue-600 font-bold mt-0.5">Cicilan</span>
                                        </div>
                                    @elseif($isOverdue)
                                        <div class="inline-flex flex-col items-center" title="{{ $typeName }} - {{ $fullMonthNames[$m] }} {{ $bill->year }}: BELUM LUNAS">
                                            <span class="w-8 h-8 rounded-lg bg-rose-500 text-white flex items-center justify-center text-sm font-bold shadow-sm">✕</span>
                                            <span class="text-[10px] text-rose-600 font-bold mt-0.5">Belum</span>
                                        </div>
                                    @else
                                        <div class="inline-flex flex-col items-center" title="{{ $typeName }} - {{ $fullMonthNames[$m] }} {{ $bill->year }}: PERIODE MENDATANG">
                                            <span class="w-8 h-8 rounded-lg bg-amber-400 text-white flex items-center justify-center text-xs font-bold shadow-sm">
                                                <i class="fas fa-clock text-[10px]"></i>
                                            </span>
                                            <span class="text-[10px] text-amber-600 font-semibold mt-0.5">Mendatang</span>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Detailed Checklist List per Bulan --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h2 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-list-check text-emerald-500"></i> Rincian Checklist Status per Periode
            </h2>
        </div>

        @if($bills->count() > 0)
        <div class="divide-y divide-gray-100">
            @foreach($bills as $bill)
                @php
                    $isPaid = $bill->status === 'lunas';
                    $isPartial = $bill->status === 'cicilan';
                    $isOverdue = !$isPaid && $bill->isOverdue();
                @endphp
                <div class="p-4 sm:px-5 hover:bg-gray-50/50 transition flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 font-bold text-sm
                            {{ $isPaid ? 'bg-emerald-500 text-white' : ($isPartial ? 'bg-blue-500 text-white' : ($isOverdue ? 'bg-rose-500 text-white' : 'bg-amber-400 text-white')) }}">
                            @if($isPaid)
                                ✓
                            @elseif($isPartial)
                                ½
                            @elseif($isOverdue)
                                ✕
                            @else
                                <i class="fas fa-clock text-xs"></i>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-sm">
                                {{ $bill->paymentType->type_name ?? 'Tagihan' }}
                                @if($bill->month)
                                    — {{ $fullMonthNames[$bill->month] ?? '' }} {{ $bill->year }}
                                @endif
                            </h3>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-400 mt-0.5">
                                @if($bill->academicYear)
                                    <span>TP {{ $bill->academicYear->year }}</span>
                                @endif
                                @if($bill->payments && $bill->payments->where('is_verified', true)->count() > 0)
                                    @php $lastPay = $bill->payments->where('is_verified', true)->sortByDesc('payment_date')->first(); @endphp
                                    @if($lastPay && $lastPay->payment_date)
                                        <span class="text-emerald-600 font-medium">
                                            <i class="fas fa-check-circle mr-0.5"></i> Dibayar {{ $lastPay->payment_date->format('d M Y') }}
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="shrink-0">
                        @if($isPaid)
                            <span class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-emerald-100 text-emerald-700 border border-emerald-200 inline-flex items-center gap-1.5">
                                <span>✓</span> Sudah Bayar (Lunas)
                            </span>
                        @elseif($isPartial)
                            <span class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-blue-100 text-blue-700 border border-blue-200 inline-flex items-center gap-1.5">
                                <span>½</span> Cicilan
                            </span>
                        @elseif($isOverdue)
                            <span class="px-3 py-1.5 text-xs font-extrabold rounded-xl bg-rose-100 text-rose-700 border border-rose-200 inline-flex items-center gap-1.5">
                                <span>✕</span> Belum Bayar
                            </span>
                        @else
                            <span class="px-3 py-1.5 text-xs font-bold rounded-xl bg-amber-50 text-amber-700 border border-amber-200 inline-flex items-center gap-1.5">
                                <i class="fas fa-clock text-[10px]"></i> Periode Mendatang
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        @else
        <div class="p-12 text-center text-gray-400">
            <i class="fas fa-check-circle text-4xl mb-3 text-emerald-300"></i>
            <p class="font-medium text-gray-500">Belum ada data periode SPP untuk tahun pelajaran ini</p>
        </div>
        @endif
    </div>
</div>
@endsection
