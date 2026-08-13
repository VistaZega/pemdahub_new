@extends('mobile.layouts.app')

@section('title', 'Tagihan SPP - Mobile')

@section('content')
<div class="space-y-4">
    <!-- Financial Overview Card -->
    <div class="glass-card rounded-3xl p-5 bg-gradient-to-br from-purple-950/80 via-slate-900 to-slate-950 border border-purple-500/30">
        <span class="text-[10px] font-bold text-purple-400 uppercase">Sisa Tagihan SPP & Keuangan</span>
        <h2 class="text-2xl font-black text-white mt-1">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</h2>

        <div class="grid grid-cols-2 gap-2 mt-4 pt-3 border-t border-slate-800 text-xs">
            <div>
                <span class="text-slate-400 block text-[10px]">Total Tagihan</span>
                <span class="font-bold text-slate-200">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px]">Sudah Dibayar</span>
                <span class="font-bold text-emerald-400">Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Bills List -->
    <div class="space-y-3">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider px-1">Rincian Item Tagihan</h3>

        @forelse($bills as $bill)
            <div class="glass-card rounded-2xl p-4 space-y-2">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-extrabold text-white">{{ $bill->title ?? 'Tagihan Sekolah' }}</h4>
                        <span class="text-[10px] text-slate-400">{{ $bill->academicYear->name ?? '' }}</span>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase
                        {{ strtolower($bill->status) === 'lunas' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-300 border border-rose-500/30' }}">
                        {{ $bill->status ?? 'Belum Lunas' }}
                    </span>
                </div>

                <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-800/80">
                    <span class="text-slate-400">Nominal Tagihan:</span>
                    <span class="font-extrabold text-white">Rp {{ number_format($bill->amount, 0, ',', '.') }}</span>
                </div>
            </div>
        @empty
            <div class="glass-card rounded-2xl p-6 text-center text-slate-500 text-xs">
                Tidak ada tagihan pembayaran.
            </div>
        @endforelse
    </div>
</div>
@endsection
