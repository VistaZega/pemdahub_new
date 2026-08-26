@extends('layouts.orangtua')
@section('title', 'Biaya Pendidikan '.$student->full_name.' - Portal Orang Tua')

@section('content')
<div class="space-y-6">
    @include('orangtua.partials.child-header', ['student' => $student, 'classroom' => $classroom, 'active' => 'tagihan'])

    {{-- Filter Academic Year --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-file-invoice-dollar text-emerald-600"></i> Rincian Biaya Pendidikan
            </h1>
            <p class="text-xs text-gray-500 mt-0.5">Daftar kewajiban pembayaran siswa yang dikelompokkan secara rapi per bulan</p>
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

    {{-- KPI Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {{-- Tunggakan Card --}}
        <div class="bg-white rounded-2xl shadow-sm border-2 {{ $tunggakanAmount > 0 ? 'border-rose-200 bg-rose-50/20' : 'border-emerald-200 bg-emerald-50/20' }} p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider {{ $tunggakanAmount > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                    <i class="fas {{ $tunggakanAmount > 0 ? 'fa-exclamation-circle' : 'fa-check-circle' }} mr-1"></i> Tunggakan s.d. Bulan Ini
                </span>
                <div class="w-8 h-8 rounded-xl {{ $tunggakanAmount > 0 ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600' }} flex items-center justify-center text-sm">
                    <i class="fas {{ $tunggakanAmount > 0 ? 'fa-hand-holding-usd' : 'fa-check' }}"></i>
                </div>
            </div>
            <p class="text-2xl font-black {{ $tunggakanAmount > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                Rp {{ number_format($tunggakanAmount, 0, ',', '.') }}
            </p>
            <p class="text-xs text-gray-500 mt-1 font-medium">
                {{ $tunggakanAmount > 0 ? 'Kewajiban pembayaran yang harus segera dilunasi' : 'Bebas dari segala tunggakan biaya s.d. bulan ini' }}
            </p>
        </div>

        {{-- Mendatang Card --}}
        <div class="bg-white rounded-2xl shadow-sm border-2 border-amber-100 bg-amber-50/10 p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-amber-600 uppercase tracking-wider">
                    <i class="fas fa-clock mr-1"></i> Tagihan Bulan Mendatang
                </span>
                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-sm">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
            <p class="text-2xl font-black text-amber-600">
                Rp {{ number_format($upcomingAmount, 0, ',', '.') }}
            </p>
            <p class="text-xs text-gray-500 mt-1 font-medium">Tagihan periode berikutnya yang belum jatuh tempo</p>
        </div>

        {{-- Dibayar Card --}}
        <div class="bg-white rounded-2xl shadow-sm border-2 border-emerald-100 bg-emerald-50/10 p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">
                    <i class="fas fa-receipt mr-1"></i> Total Sudah Dibayar
                </span>
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm">
                    <i class="fas fa-shield-alt"></i>
                </div>
            </div>
            <p class="text-2xl font-black text-emerald-600">
                Rp {{ number_format($totalBayar, 0, ',', '.') }}
            </p>
            <p class="text-xs text-gray-500 mt-1 font-medium">Total dana terverifikasi yang telah dibayarkan</p>
        </div>
    </div>

    {{-- SECTION 1: TAGIHAN KELOMPOK PER BULAN (CHRONOLOGICAL MONTH-BY-MONTH) --}}
    <div class="space-y-4">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-calendar-alt text-emerald-600"></i> Rincian Tagihan Bulanan (Per Bulan)
            </h2>
            <span class="text-xs text-gray-400 font-semibold">Tahun Pelajaran Active / Terpilih</span>
        </div>

        @forelse($monthlyGroupedData as $mGroup)
            <div class="bg-white rounded-2xl shadow-sm border-2 {{ $mGroup['is_paid'] ? 'border-emerald-100' : ($mGroup['is_overdue'] ? 'border-rose-200' : 'border-amber-100') }} overflow-hidden transition hover:shadow-md">
                
                {{-- Month Header Bar --}}
                <div class="px-5 py-3.5 {{ $mGroup['is_paid'] ? 'bg-emerald-50/60' : ($mGroup['is_overdue'] ? 'bg-rose-50/70' : 'bg-amber-50/60') }} border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl {{ $mGroup['is_paid'] ? 'bg-emerald-500 text-white' : ($mGroup['is_overdue'] ? 'bg-rose-500 text-white' : 'bg-amber-400 text-white') }} flex items-center justify-center font-black text-sm shadow-sm">
                            <i class="fas {{ $mGroup['is_paid'] ? 'fa-check' : ($mGroup['is_overdue'] ? 'fa-exclamation-triangle' : 'fa-clock') }}"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-gray-900 text-sm flex items-center gap-2">
                                📅 Bulan {{ $mGroup['label'] }}
                            </h3>
                            <p class="text-[11px] font-semibold {{ $mGroup['is_paid'] ? 'text-emerald-700' : ($mGroup['is_overdue'] ? 'text-rose-700' : 'text-amber-700') }}">
                                @if($mGroup['is_paid'])
                                    ✓ Lunas Seluruh Tagihan Bulan Ini
                                @elseif($mGroup['is_overdue'])
                                    ⚠️ Menunggak — Jatuh Tempo: 10 {{ $mGroup['month_name'] }} {{ $mGroup['year'] }}
                                @else
                                    🕒 Belum Jatuh Tempo (Bulan Mendatang)
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="text-right">
                        <div class="text-xs font-bold text-gray-500">Total Tagihan Bulan Ini:</div>
                        <div class="text-sm font-black text-gray-900">
                            Rp {{ number_format($mGroup['total_amount'], 0, ',', '.') }}
                            @if($mGroup['remaining_amount'] > 0)
                                <span class="text-xs font-bold {{ $mGroup['is_overdue'] ? 'text-rose-600' : 'text-amber-600' }} ml-1">
                                    (Sisa: Rp {{ number_format($mGroup['remaining_amount'], 0, ',', '.') }})
                                </span>
                            @else
                                <span class="text-xs font-bold text-emerald-600 ml-1">(Lunas)</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Month Bill Items Table --}}
                <div class="p-4 overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="text-gray-400 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100">
                                <th class="pb-2 text-left pl-2">Komponen / Jenis Tagihan</th>
                                <th class="pb-2 text-right">Nominal Tagihan</th>
                                <th class="pb-2 text-right">Sudah Dibayar</th>
                                <th class="pb-2 text-right">Sisa Pembayaran</th>
                                <th class="pb-2 text-center pr-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($mGroup['items'] as $billItem)
                                @php
                                    $itemSisa = max(0, $billItem->amount - $billItem->paid_amount);
                                    $isItemPaid = $billItem->status === 'lunas' || $itemSisa == 0;
                                    $isItemPartial = $billItem->status === 'cicilan';
                                @endphp
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="py-2.5 pl-2 font-bold text-gray-800 flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full {{ $isItemPaid ? 'bg-emerald-500' : ($mGroup['is_overdue'] ? 'bg-rose-500' : 'bg-amber-400') }}"></span>
                                        {{ $billItem->paymentType->type_name ?? 'Tagihan' }}
                                    </td>
                                    <td class="py-2.5 text-right font-semibold text-gray-700">Rp {{ number_format($billItem->amount, 0, ',', '.') }}</td>
                                    <td class="py-2.5 text-right font-semibold text-emerald-600">Rp {{ number_format($billItem->paid_amount, 0, ',', '.') }}</td>
                                    <td class="py-2.5 text-right font-black {{ $itemSisa > 0 ? ($mGroup['is_overdue'] ? 'text-rose-600' : 'text-amber-600') : 'text-emerald-600' }}">
                                        Rp {{ number_format($itemSisa, 0, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 text-center pr-2">
                                        @if($isItemPaid)
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                ✓ LUNAS
                                            </span>
                                        @elseif($isItemPartial)
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-200">
                                                CICILAN
                                            </span>
                                        @elseif($mGroup['is_overdue'])
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-800 border border-rose-200">
                                                MENUNGGAK
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-slate-100 text-slate-700 border border-slate-200">
                                                BELUM BAYAR
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        @empty
            <div class="bg-white rounded-2xl p-10 text-center text-gray-400 border border-gray-100 shadow-sm">
                <i class="fas fa-check-circle text-4xl mb-3 text-emerald-400"></i>
                <p class="font-bold text-gray-600 text-sm">Tidak ada tagihan bulanan pada periode ini.</p>
            </div>
        @endforelse
    </div>

    {{-- SECTION 2: TAGIHAN NON-BULANAN (UANG PANGKAL, UJIAN, DLL. BILA ADA) --}}
    @if(isset($nonMonthlyBills) && $nonMonthlyBills->count() > 0)
    <div class="space-y-3 pt-4">
        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2 px-1">
            <i class="fas fa-file-invoice text-indigo-600"></i> Tagihan Non-Bulanan / Insidental
        </h2>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr class="text-gray-500 font-bold uppercase tracking-wider text-[10px]">
                            <th class="px-4 py-3 text-left">Jenis Tagihan</th>
                            <th class="px-4 py-3 text-center">Periode / Catatan</th>
                            <th class="px-4 py-3 text-right">Nominal</th>
                            <th class="px-4 py-3 text-right">Dibayar</th>
                            <th class="px-4 py-3 text-right">Sisa</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($nonMonthlyBills as $nb)
                            @php
                                $nbSisa = max(0, $nb->amount - $nb->paid_amount);
                                $isNbPaid = $nb->status === 'lunas' || $nbSisa == 0;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-bold text-gray-800">{{ $nb->paymentType->type_name ?? 'Tagihan' }}</td>
                                <td class="px-4 py-3 text-center text-gray-500 font-medium">1 Kali Pembayaran ({{ $nb->academicYear->year ?? $nb->year }})</td>
                                <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($nb->amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-emerald-600">Rp {{ number_format($nb->paid_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-black {{ $nbSisa > 0 ? 'text-rose-600' : 'text-emerald-600' }}">Rp {{ number_format($nbSisa, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black {{ $isNbPaid ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $isNbPaid ? '✓ LUNAS' : 'BELUM BAYAR' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
