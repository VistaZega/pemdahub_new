@extends('mobile.layouts.app')

@section('title', 'Tagihan Saya & Keuangan SPP - PembdaHUB Mobile')

@section('content')
<div class="space-y-4">
    <!-- Header Title -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-900">Tagihan & SPP Saya 💳</h2>
            <p class="text-[11px] text-slate-500 font-bold">Rincian Pembayaran Uang Sekolah Siswa</p>
        </div>
        <div class="w-10 h-10 rounded-2xl clay-yellow flex items-center justify-center text-xl font-black shadow-md">
            💰
        </div>
    </div>

    <!-- Financial Overview Clay Card -->
    <div class="clay-card p-6 {{ $totalOutstanding > 0 ? 'bg-gradient-to-br from-amber-500 to-orange-600' : 'bg-gradient-to-br from-emerald-600 to-teal-700' }} text-white space-y-3 shadow-lg">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-black uppercase tracking-wider bg-white/30 px-3 py-1 rounded-full border border-white/40 backdrop-blur-sm">
                {{ $totalOutstanding > 0 ? '🔴 Sisa Tunggakan SPP' : '🟢 Lunas Bebas Tunggakan' }}
            </span>
            <i class="fa-solid fa-receipt text-white/60 text-lg"></i>
        </div>

        <h2 class="text-3xl font-black text-white leading-none">
            Rp {{ number_format($totalOutstanding, 0, ',', '.') }}
        </h2>

        <div class="grid grid-cols-2 gap-2 pt-3 border-t border-white/30 text-xs">
            <div>
                <span class="text-white/80 block text-[10px] font-bold uppercase">Total Tagihan</span>
                <span class="font-black text-white text-sm">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
            </div>
            <div>
                <span class="text-white/80 block text-[10px] font-bold uppercase">Total Dibayar</span>
                <span class="font-black text-white text-sm">Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Info Cara Pembayaran Card -->
    <div class="p-3.5 bg-blue-50 border-2 border-blue-200 rounded-2xl space-y-1.5 text-xs text-blue-900 font-extrabold">
        <div class="flex items-center gap-2 text-blue-700 font-black uppercase text-[11px]">
            <i class="fa-solid fa-circle-info text-sm"></i> Petunjuk Pembayaran Uang Sekolah
        </div>
        <p class="text-[11px] text-slate-700 font-bold leading-relaxed">
            Pembayaran SPP dan Tagihan Sekolah dapat dilakukan secara tunai melalui <strong>Kasir / Bendahara Sekolah</strong> atau melalui Transfer Rekening Resmi Perguruan Pembda. Bukti pembayaran akan otomatis terupdate di aplikasi.
        </p>
    </div>

    <!-- Bills List (Clay Cards) -->
    <div class="space-y-3">
        <h3 class="text-xs font-black text-slate-600 uppercase tracking-wider px-1">
            Rincian Item Tagihan Siswa ({{ $bills->count() }})
        </h3>

        @forelse($bills as $bill)
            @php
                $status = strtolower($bill->status ?? 'belum_bayar');
                $badgeClass = match($status) {
                    'lunas' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                    'cicilan' => 'bg-amber-100 text-amber-900 border-amber-300',
                    default => 'bg-rose-100 text-rose-900 border-rose-300',
                };
                $statusText = match($status) {
                    'lunas' => '🟢 LUNAS',
                    'cicilan' => '🟡 DIBAYAR SEBAGIAN',
                    default => '🔴 BELUM DIBAYAR',
                };
            @endphp
            <div class="clay-card p-4 space-y-3 bg-white border-2 border-slate-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-black text-slate-900 uppercase leading-snug">
                            {{ $bill->display_title ?? 'Tagihan SPP Sekolah' }}
                        </h4>
                        <span class="text-[10px] text-slate-500 font-bold block mt-0.5">
                            {{ $bill->academicYear->name ?? 'Tahun Ajaran Aktif' }}
                        </span>
                    </div>

                    <span class="px-2.5 py-1 rounded-xl text-[10px] font-black border {{ $badgeClass }}">
                        {{ $statusText }}
                    </span>
                </div>

                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-[11px] font-bold space-y-1">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Nominal Tagihan:</span>
                        <span class="font-black text-slate-900">Rp {{ number_format($bill->amount, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex items-center justify-between text-slate-600">
                        <span>Sudah Dibayar:</span>
                        <span class="font-black text-emerald-700">Rp {{ number_format($bill->paid_amount, 0, ',', '.') }}</span>
                    </div>

                    @if(($bill->sisa_tunggakan ?? 0) > 0)
                    <div class="flex items-center justify-between pt-1 border-t border-slate-200 text-rose-700 font-black">
                        <span>Sisa Tunggakan:</span>
                        <span>Rp {{ number_format($bill->sisa_tunggakan, 0, ',', '.') }}</span>
                    </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-3xl">🎉</div>
                <p>Belum ada rincian tagihan pembayaran sekolah.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
