@extends('mobile.layouts.app')

@section('title', 'Tagihan SPP 3D - Mobile Pro')

@section('content')
<div class="space-y-4">
    <!-- Financial Overview Clay Card -->
    <div class="clay-yellow p-6">
        <span class="text-[10px] font-black uppercase tracking-wider bg-white/30 px-2.5 py-0.5 rounded-full border border-white/40">Sisa Tagihan SPP & Keuangan</span>
        <h2 class="text-2xl font-black text-white mt-1.5 leading-none">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</h2>

        <div class="grid grid-cols-2 gap-2 mt-4 pt-3 border-t border-white/30 text-xs">
            <div>
                <span class="text-amber-100 block text-[10px] font-bold">Total Tagihan</span>
                <span class="font-black text-white">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
            </div>
            <div>
                <span class="text-amber-100 block text-[10px] font-bold">Sudah Dibayar</span>
                <span class="font-black text-white">Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Bills List (Clay Cards) -->
    <div class="space-y-3">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-wider px-1">Rincian Item Tagihan</h3>

        @forelse($bills as $bill)
            <div class="clay-card p-4.5 space-y-2.5">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-black text-slate-900">{{ $bill->title ?? 'Tagihan Sekolah' }}</h4>
                        <span class="text-[10px] text-slate-500 font-bold">{{ $bill->academicYear->name ?? '' }}</span>
                    </div>
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase
                        {{ strtolower($bill->status) === 'lunas' ? 'clay-green' : 'clay-pink' }}">
                        {{ $bill->status ?? 'Belum Lunas' }}
                    </span>
                </div>

                <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-100 font-black">
                    <span class="text-slate-500 font-bold">Nominal Tagihan:</span>
                    <span class="text-slate-900">Rp {{ number_format($bill->amount, 0, ',', '.') }}</span>
                </div>
            </div>
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                Tidak ada tagihan pembayaran.
            </div>
        @endforelse
    </div>
</div>
@endsection
