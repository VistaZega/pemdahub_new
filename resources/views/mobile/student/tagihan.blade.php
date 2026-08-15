@extends('mobile.layouts.app')

@section('title', 'Tagihan & Keuangan SPP - PembdaHUB Mobile')

@section('content')
<div class="space-y-4" x-data="{ tab: 'due' }">
    <!-- Header Title -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-900">Tagihan & SPP Saya 💳</h2>
            <p class="text-[11px] text-slate-500 font-bold">Periode Berkenaan: <span class="text-blue-700 font-extrabold">{{ $currentPeriodLabel }}</span></p>
        </div>
        <div class="w-10 h-10 rounded-2xl clay-yellow flex items-center justify-center text-xl font-black shadow-md">
            💰
        </div>
    </div>

    <!-- Financial Overview Clay Hero Card (Berdasarkan Bulan Berkenaan, Bukan 12 Bulan) -->
    <div class="clay-card p-5 {{ $financialSummary['card_gradient'] }} text-white space-y-3.5 shadow-lg relative overflow-hidden">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-black uppercase tracking-wider bg-white/25 px-3 py-1 rounded-full border border-white/35 backdrop-blur-sm">
                {{ $financialSummary['badge_text'] }}
            </span>
            <span class="text-[10px] font-bold text-white/80">
                s.d. {{ $currentPeriodLabel }}
            </span>
        </div>

        <div>
            <span class="text-white/80 text-[11px] font-bold block uppercase tracking-wide">
                Total Tunggakan s.d. Bulan Berkenaan
            </span>
            <h2 class="text-3xl font-black text-white leading-none mt-1">
                Rp {{ number_format($totalDueOutstanding, 0, ',', '.') }}
            </h2>
            @if($totalDueOutstanding <= 0)
                <p class="text-[11px] text-emerald-100 font-bold mt-1.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check text-xs"></i> Seluruh kewajiban pembayaran telah lunas!
                </p>
            @endif
        </div>

        <!-- Metric Grid Kewajiban Jatuh Tempo -->
        <div class="grid grid-cols-3 gap-2 pt-2.5 border-t border-white/20 text-xs">
            <div class="bg-white/10 p-2 rounded-xl text-center">
                <span class="text-white/80 block text-[9px] font-bold uppercase">Wajib Bayar</span>
                <span class="font-black text-white text-xs block truncate">Rp {{ number_format($totalDueAmount, 0, ',', '.') }}</span>
            </div>
            <div class="bg-white/10 p-2 rounded-xl text-center">
                <span class="text-white/80 block text-[9px] font-bold uppercase">Telah Dibayar</span>
                <span class="font-black text-white text-xs block truncate">Rp {{ number_format($totalDuePaid, 0, ',', '.') }}</span>
            </div>
            <div class="bg-white/10 p-2 rounded-xl text-center">
                <span class="text-white/80 block text-[9px] font-bold uppercase">Sisa Tunggakan</span>
                <span class="font-black text-white text-xs block truncate">Rp {{ number_format($totalDueOutstanding, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- LAPORAN DETAIL STATUS PEMBAYARAN SISWA (Bulan Berkenaan & Rincian Tunggakan) -->
    <div class="clay-card p-4.5 space-y-3 bg-white border-2 border-slate-200">
        <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2">
            <i class="fa-solid fa-clipboard-check text-blue-600"></i> Laporan Pembayaran Uang Sekolah
        </h3>

        <!-- 1. Status Uang Sekolah Bulan Berkenaan -->
        <div class="p-3 rounded-2xl border 
            {{ $currentMonthStatus === 'lunas' ? 'bg-emerald-50 border-emerald-200' : ($currentMonthStatus === 'cicilan' ? 'bg-amber-50 border-amber-200' : 'bg-rose-50 border-rose-200') }}">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-wide 
                    {{ $currentMonthStatus === 'lunas' ? 'text-emerald-800' : ($currentMonthStatus === 'cicilan' ? 'text-amber-800' : 'text-rose-800') }}">
                    Bulan Berkenaan ({{ $currentPeriodLabel }})
                </span>
                <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase
                    {{ $currentMonthStatus === 'lunas' ? 'bg-emerald-600 text-white' : ($currentMonthStatus === 'cicilan' ? 'bg-amber-500 text-white' : 'bg-rose-600 text-white') }}">
                    {{ $currentMonthStatus === 'lunas' ? 'LUNAS' : ($currentMonthStatus === 'cicilan' ? 'CICILAN' : 'BELUM BAYAR') }}
                </span>
            </div>
            <p class="text-xs font-extrabold mt-1
                {{ $currentMonthStatus === 'lunas' ? 'text-emerald-950' : ($currentMonthStatus === 'cicilan' ? 'text-amber-950' : 'text-rose-950') }}">
                @if($currentMonthStatus === 'lunas')
                    ✅ Siswa telah membayar uang sekolah bulan berkenaan ({{ $currentPeriodLabel }}).
                @elseif($currentMonthStatus === 'cicilan')
                    🟡 Siswa telah membayar sebagian uang sekolah bulan berkenaan (Sisa: Rp {{ number_format($currentMonthBill->sisa_tunggakan ?? 0, 0, ',', '.') }}).
                @else
                    🔴 Siswa belum membayar uang sekolah bulan berkenaan ({{ $currentPeriodLabel }}).
                @endif
            </p>
        </div>

        <!-- 2. Rincian Bulan-Bulan yang Menunggak (Jika Ada) -->
        @if(!empty($unpaidCurrentAndPast))
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase text-slate-700">
                        Daftar Bulan Menunggak ({{ count($unpaidCurrentAndPast) }} Bulan)
                    </span>
                    <span class="text-[9px] font-extrabold text-rose-600 bg-rose-100 px-2 py-0.5 rounded-full">
                        Wajib Dilunasi
                    </span>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($unpaidCurrentAndPast as $unpaidMonth)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[10px] font-black bg-rose-100 text-rose-900 border border-rose-200">
                            <i class="fa-solid fa-triangle-exclamation text-[9px] text-rose-600"></i>
                            {{ $unpaidMonth }}
                        </span>
                    @endforeach
                </div>
                <p class="text-[10px] text-slate-500 font-bold pt-1">
                    *Tunggakan dihitung s.d. bulan berkenaan ({{ $currentPeriodLabel }}). Tagihan bulan selanjutnya tidak dihitung sebagai tunggakan.
                </p>
            </div>
        @else
            <div class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-2xl flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-shield-check text-base"></i>
                </div>
                <div>
                    <h4 class="text-xs font-black text-emerald-950">Tertib Finansial & Bebas Tunggakan</h4>
                    <p class="text-[10px] text-emerald-800 font-bold">Tidak ada catatan tunggakan uang sekolah s.d. periode {{ $currentPeriodLabel }}.</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Filter Segmentasi Tab (Clay Segmented Control) -->
    <div class="flex items-center space-x-1.5 overflow-x-auto pb-1 no-scrollbar">
        <button @click="tab = 'due'" 
                :class="tab === 'due' ? 'clay-orange text-white shadow-md scale-105 font-black' : 'bg-white text-slate-600 border-2 border-slate-200 font-bold'"
                class="px-3.5 py-2 rounded-2xl text-xs whitespace-nowrap transition flex items-center gap-1.5 shrink-0">
            <span>🔴 Wajib Bayar</span>
            <span class="w-4 h-4 rounded-full text-[9px] font-black flex items-center justify-center"
                  :class="tab === 'due' ? 'bg-white/30 text-white' : 'bg-orange-100 text-orange-800'">
                {{ $dueBills->count() }}
            </span>
        </button>

        <button @click="tab = 'paid'" 
                :class="tab === 'paid' ? 'clay-green text-white shadow-md scale-105 font-black' : 'bg-white text-slate-600 border-2 border-slate-200 font-bold'"
                class="px-3.5 py-2 rounded-2xl text-xs whitespace-nowrap transition flex items-center gap-1.5 shrink-0">
            <span>🟢 Riwayat Lunas</span>
            <span class="w-4 h-4 rounded-full text-[9px] font-black flex items-center justify-center"
                  :class="tab === 'paid' ? 'bg-white/30 text-white' : 'bg-emerald-100 text-emerald-800'">
                {{ $paidBills->count() }}
            </span>
        </button>

        <button @click="tab = 'future'" 
                :class="tab === 'future' ? 'clay-blue text-white shadow-md scale-105 font-black' : 'bg-white text-slate-600 border-2 border-slate-200 font-bold'"
                class="px-3.5 py-2 rounded-2xl text-xs whitespace-nowrap transition flex items-center gap-1.5 shrink-0">
            <span>📅 Periode Mendatang</span>
            <span class="w-4 h-4 rounded-full text-[9px] font-black flex items-center justify-center"
                  :class="tab === 'future' ? 'bg-white/30 text-white' : 'bg-blue-100 text-blue-800'">
                {{ $futureBills->count() }}
            </span>
        </button>

        <button @click="tab = 'all'" 
                :class="tab === 'all' ? 'clay-purple text-white shadow-md scale-105 font-black' : 'bg-white text-slate-600 border-2 border-slate-200 font-bold'"
                class="px-3.5 py-2 rounded-2xl text-xs whitespace-nowrap transition flex items-center gap-1.5 shrink-0">
            <span>📋 Semua</span>
            <span class="w-4 h-4 rounded-full text-[9px] font-black flex items-center justify-center"
                  :class="tab === 'all' ? 'bg-white/30 text-white' : 'bg-purple-100 text-purple-800'">
                {{ $allBills->count() }}
            </span>
        </button>
    </div>

    <!-- ==================== 1. TAB: WAJIB BAYAR (Jatuh Tempo s.d. Bulan Berkenaan) ==================== -->
    <div x-show="tab === 'due'" class="space-y-3">
        @forelse($dueBills as $bill)
            @include('mobile.student.partials.tagihan_card', ['bill' => $bill, 'highlightDue' => true])
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold space-y-1.5">
                <div class="text-3xl">🎉</div>
                <h4 class="text-slate-800 font-black text-sm">Tidak Ada Tagihan Wajib Bayar!</h4>
                <p class="text-[11px] text-slate-500">Seluruh tagihan s.d. bulan berkenaan ({{ $currentPeriodLabel }}) telah lunas.</p>
            </div>
        @endforelse
    </div>

    <!-- ==================== 2. TAB: RIWAYAT LUNAS ==================== -->
    <div x-show="tab === 'paid'" class="space-y-3" style="display: none;">
        @forelse($paidBills as $bill)
            @include('mobile.student.partials.tagihan_card', ['bill' => $bill])
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold space-y-1.5">
                <i class="fa-solid fa-receipt text-3xl text-slate-300"></i>
                <p>Belum ada riwayat tagihan yang lunas.</p>
            </div>
        @endforelse
    </div>

    <!-- ==================== 3. TAB: PERIODE MENDATANG (Belum Jatuh Tempo) ==================== -->
    <div x-show="tab === 'future'" class="space-y-3" style="display: none;">
        <div class="p-3 bg-blue-50 border border-blue-200 rounded-2xl text-[11px] text-blue-900 font-bold flex items-center gap-2">
            <i class="fa-solid fa-info-circle text-blue-600 text-sm"></i>
            <span>Daftar di bawah ini adalah tagihan bulan mendatang dan <strong>bukan merupakan tunggakan</strong> saat ini.</span>
        </div>

        @forelse($futureBills as $bill)
            @include('mobile.student.partials.tagihan_card', ['bill' => $bill])
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold space-y-1.5">
                <i class="fa-solid fa-calendar-check text-3xl text-slate-300"></i>
                <p>Tidak ada tagihan periode mendatang yang terdaftar.</p>
            </div>
        @endforelse
    </div>

    <!-- ==================== 4. TAB: SEMUA TAGIHAN ==================== -->
    <div x-show="tab === 'all'" class="space-y-3" style="display: none;">
        @forelse($allBills as $bill)
            @include('mobile.student.partials.tagihan_card', ['bill' => $bill])
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold space-y-1.5">
                <p>Belum ada rincian tagihan pembayaran sekolah.</p>
            </div>
        @endforelse
    </div>

    <!-- Info Petunjuk Pembayaran Card -->
    <div class="p-4 bg-blue-50/80 border-2 border-blue-200 rounded-2xl space-y-2 text-xs text-blue-900 font-extrabold">
        <div class="flex items-center gap-2 text-blue-800 font-black uppercase text-[11px]">
            <i class="fa-solid fa-building-columns text-sm"></i> Petunjuk Pembayaran Uang Sekolah
        </div>
        <p class="text-[11px] text-slate-700 font-bold leading-relaxed">
            Pembayaran SPP dan Tagihan Sekolah dapat dilakukan secara tunai melalui <strong>Kasir / Bendahara Sekolah</strong> atau melalui Transfer Rekening Resmi Perguruan Pembda. Bukti pembayaran akan otomatis terverifikasi dan tercatat di aplikasi.
        </p>
    </div>
</div>
@endsection
