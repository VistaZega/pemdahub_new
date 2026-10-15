@extends('layouts.siswa')
@section('title', 'Biaya Pendidikan - Portal Siswa')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-file-invoice-dollar text-amber-500"></i> Biaya Pendidikan
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Rincian kewajiban biaya dan progress pembayaran Anda</p>
        </div>
        
        {{-- Filter Academic Year --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-4 py-2 w-full sm:w-64">
            <form method="GET" action="{{ route('siswa.tagihan') }}" class="flex items-center gap-2">
                <label class="text-xs font-bold text-gray-400 uppercase whitespace-nowrap"><i class="fas fa-calendar-alt mr-1"></i> TA:</label>
                <select name="academic_year_id" onchange="this.form.submit()" class="w-full border border-gray-200 rounded-lg px-2 py-1 text-xs focus:ring-2 focus:ring-amber-500 focus:border-transparent bg-white">
                    <option value="">Semua Tahun</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ $selectedYearId == $year->id ? 'selected' : '' }}>
                            {{ $year->year }} {{ $year->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center"><i class="fas fa-receipt text-blue-600"></i></div>
                <span class="text-xs text-gray-500 font-medium">Total Beban</span>
            </div>
            <p class="text-lg font-bold text-gray-800">{{ $totalBillsCount ?? $bills->count() }} Tagihan</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Tahun Ajaran Terpilih</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 p-5">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center"><i class="fas fa-check-circle text-emerald-600"></i></div>
                <span class="text-xs text-emerald-600 font-medium">Sudah Lunas</span>
            </div>
            <p class="text-lg font-bold text-emerald-600 flex items-center gap-1.5">
                <span>{{ $lunasCount ?? $bills->where('status', 'lunas')->count() }} Bulan</span>
                <span class="text-[11px] bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full font-bold">✓ Lunas</span>
            </p>
            <p class="text-[11px] text-emerald-600/70 mt-0.5">Telah diverifikasi</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-rose-100 p-5">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center"><i class="fas fa-exclamation-circle text-rose-600"></i></div>
                <span class="text-xs text-rose-600 font-medium">Tunggakan</span>
            </div>
            <p class="text-lg font-bold text-rose-600 flex items-center gap-1.5">
                <span>{{ $tunggakanCount ?? 0 }} Bulan</span>
                @if(($tunggakanCount ?? 0) > 0)
                <span class="text-[11px] bg-rose-100 text-rose-800 px-2 py-0.5 rounded-full font-bold">✕ Belum</span>
                @else
                <span class="text-[11px] bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full font-bold">✓ Bebas</span>
                @endif
            </p>
            <p class="text-[11px] text-rose-500/70 mt-0.5">s.d. bulan berkenaan</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-amber-100 p-5">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center"><i class="fas fa-clock text-amber-600"></i></div>
                <span class="text-xs text-amber-600 font-medium">Mendatang</span>
            </div>
            <p class="text-lg font-bold text-amber-600 flex items-center gap-1.5">
                <span>{{ $upcomingCount ?? 0 }} Bulan</span>
                <span class="text-[11px] bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full font-bold">Mendatang</span>
            </p>
            <p class="text-[11px] text-amber-600/70 mt-0.5">Belum jatuh tempo</p>
        </div>
    </div>

    {{-- Legend --}}
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
        <div class="flex flex-wrap items-center gap-5 text-xs font-semibold">
            <span class="text-gray-500 uppercase tracking-wider"><i class="fas fa-info-circle mr-1"></i> Keterangan Status:</span>
            <span class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-md bg-emerald-500 inline-flex items-center justify-center text-white text-[10px]"><i class="fas fa-check"></i></span>
                <span class="text-gray-700">Sudah Lunas (✓)</span>
            </span>
            <span class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-md bg-rose-500 inline-flex items-center justify-center text-white text-[10px]"><i class="fas fa-times"></i></span>
                <span class="text-gray-700">Belum Lunas (✕)</span>
            </span>
            <span class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-md bg-amber-400 inline-flex items-center justify-center text-white text-[10px]"><i class="fas fa-clock"></i></span>
                <span class="text-gray-700">Bulan Mendatang</span>
            </span>
            <span class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-md bg-gray-200 inline-flex items-center justify-center text-gray-400 text-[10px]"><i class="fas fa-minus"></i></span>
                <span class="text-gray-700">Tidak Ada Tagihan</span>
            </span>
        </div>
    </div>

    {{-- Matrix per Recurring Payment Type --}}
    @forelse($recurringTypes as $typeId => $typeName)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-white flex items-center justify-between">
            <h2 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                <i class="fas fa-calendar-alt text-amber-500"></i> {{ $typeName }}
                <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded-full ml-1">Bulanan</span>
            </h2>
        </div>

        {{-- Monthly Grid --}}
        <div class="p-4 overflow-x-auto">
            <div class="flex items-center justify-between min-w-[500px] gap-1.5">
                @foreach($months as $m)
                    @php
                        $key = $typeId . '_' . $m;
                        $bill = $monthMap[$key] ?? null;
                        $monthLabel = substr(\Carbon\Carbon::create()->month($m)->translatedFormat('F'), 0, 3);

                        if (!$bill) {
                            $cellBg = 'bg-gray-100 border-gray-200';
                            $iconHtml = '<i class="fas fa-minus text-gray-300 text-sm"></i>';
                            $tooltip = 'Tidak ada tagihan';
                            $badgeHtml = '';
                        } elseif ($bill->status === 'lunas') {
                            $cellBg = 'bg-emerald-500 border-emerald-600 shadow-sm shadow-emerald-200';
                            $iconHtml = '<i class="fas fa-check text-white text-sm"></i>';
                            $tooltip = 'Lunas (✓)';
                            $badgeHtml = '<span class="text-[9px] font-bold text-emerald-600 tracking-tight whitespace-nowrap mt-0.5">✓ Lunas</span>';
                        } elseif ($bill->isOverdue()) {
                            $cellBg = 'bg-rose-500 border-rose-600 shadow-sm shadow-rose-200';
                            $iconHtml = '<i class="fas fa-times text-white text-sm"></i>';
                            $tooltip = 'Belum Lunas (✕)';
                            $badgeHtml = '<span class="text-[9px] font-bold text-rose-600 tracking-tight whitespace-nowrap mt-0.5">✕ Belum</span>';
                        } else {
                            $cellBg = 'bg-amber-400 border-amber-500';
                            $iconHtml = '<i class="fas fa-clock text-white text-sm"></i>';
                            $tooltip = 'Bulan mendatang';
                            $badgeHtml = '<span class="text-[9px] font-bold text-amber-600 tracking-tight whitespace-nowrap mt-0.5">Mendatang</span>';
                        }
                    @endphp
                    <div class="flex flex-col items-center gap-1 flex-1" title="{{ $tooltip }}">
                        <span class="text-[9px] font-bold text-gray-500 uppercase tracking-wider">{{ $monthLabel }}</span>
                        <div class="w-10 h-10 rounded-lg border {{ $cellBg }} flex items-center justify-center transition-all hover:scale-110 hover:shadow-md cursor-default shrink-0">
                            {!! $iconHtml !!}
                        </div>
                        @if($bill && $bill->paid_amount > 0 && $bill->status !== 'lunas')
                            <span class="text-[8px] font-bold text-amber-600 leading-none">Cicilan</span>
                        @endif
                        {!! $badgeHtml !!}
                    </div>
                @endforeach
            </div>

            {{-- Progress bar for this type --}}
            @php
                $typeTotal = 0; $typeLunas = 0;
                foreach ($months as $m) {
                    $key = $typeId . '_' . $m;
                    if (isset($monthMap[$key])) {
                        $typeTotal++;
                        if ($monthMap[$key]->status === 'lunas') $typeLunas++;
                    }
                }
                $typePct = $typeTotal > 0 ? round(($typeLunas / $typeTotal) * 100) : 0;
            @endphp
            <div class="mt-5 flex items-center gap-3">
                <div class="flex-1 bg-gray-100 rounded-full h-2.5">
                    <div class="h-2.5 rounded-full transition-all {{ $typePct == 100 ? 'bg-emerald-500' : 'bg-amber-400' }}" style="width: {{ $typePct }}%"></div>
                </div>
                <span class="text-sm font-black {{ $typePct == 100 ? 'text-emerald-600' : ($typePct > 0 ? 'text-amber-600' : 'text-gray-400') }}">{{ $typePct }}%</span>
                <span class="text-xs text-gray-400">({{ $typeLunas }}/{{ $typeTotal }} bulan lunas ✓)</span>
            </div>
        </div>
    </div>
    @empty
        {{-- No recurring bills, skip this section silently --}}
    @endforelse

    {{-- Non-Recurring Bills --}}
    @if($nonRecurringBills->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <h2 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                <i class="fas fa-receipt text-amber-500"></i> Biaya Non-Bulanan (Sekali Bayar)
            </h2>
        </div>
        <div class="p-5 space-y-3">
            @foreach($nonRecurringBills as $bill)
                @php
                    if ($bill->status === 'lunas') {
                        $borderColor = 'border-l-emerald-500 bg-emerald-50/30';
                        $statusBadge = '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800"><i class="fas fa-check-circle text-emerald-600"></i> Sudah Lunas (✓)</span>';
                    } elseif ($bill->isOverdue()) {
                        $borderColor = 'border-l-rose-500 bg-rose-50/30';
                        $statusBadge = '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800"><i class="fas fa-exclamation-circle text-rose-600"></i> Belum Lunas (✕)</span>';
                    } else {
                        $borderColor = 'border-l-amber-400 bg-amber-50/30';
                        $statusBadge = '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800"><i class="far fa-clock text-amber-600"></i> Belum Waktunya</span>';
                    }
                @endphp
                <div class="border border-gray-200 border-l-4 {{ $borderColor }} rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <p class="font-bold text-gray-800 text-sm">{{ $bill->paymentType->type_name ?? 'Tagihan' }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $bill->academicYear->year ?? '' }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div>{!! $statusBadge !!}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Payment History --}}
    @php
        $billsWithPayments = $bills->filter(fn($b) => $b->payments->isNotEmpty());
    @endphp
    @if($billsWithPayments->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <h2 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                <i class="fas fa-history text-amber-500"></i> Riwayat Pembayaran Terverifikasi
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Tanggal</th>
                        <th class="px-5 py-3 text-left">Jenis Biaya</th>
                        <th class="px-5 py-3 text-center">Metode</th>
                        <th class="px-5 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($billsWithPayments as $bill)
                        @foreach($bill->payments as $payment)
                        <tr class="hover:bg-amber-50/30 transition">
                            <td class="px-5 py-3 text-gray-700">
                                <i class="fas fa-check-circle text-emerald-500 mr-1"></i>
                                {{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-800">{{ $bill->paymentType->type_name ?? 'Tagihan' }}</p>
                                @if($bill->month)
                                    <p class="text-[10px] text-gray-400">{{ \Carbon\Carbon::create()->month($bill->month)->translatedFormat('F') }} {{ $bill->year }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-[10px] font-bold">{{ $payment->getPaymentMethodLabel() }}</span>
                                @if($payment->receipt_number)
                                    <p class="text-[10px] text-gray-400 mt-0.5">No. {{ $payment->receipt_number }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                    <i class="fas fa-check-circle text-emerald-600"></i> Lunas Terverifikasi (✓)
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Empty State --}}
    @if($bills->isEmpty())
    <div class="bg-white rounded-2xl p-12 shadow-sm border border-gray-100 text-center">
        <i class="fas fa-check-circle text-5xl text-emerald-200 mb-4"></i>
        <p class="text-gray-400 text-lg font-medium">Tidak ada tagihan biaya pendidikan.</p>
        <p class="text-gray-300 text-sm mt-1">Belum ada kewajiban biaya yang dibebankan pada periode ini.</p>
    </div>
    @endif

</div>
@endsection
