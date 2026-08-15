@php
    $status = strtolower($bill->status ?? 'belum_bayar');
    $remaining = (float) ($bill->sisa_tunggakan ?? max(0, $bill->amount - $bill->paid_amount));
    $isLunas = ($status === 'lunas' || $remaining <= 0);
    $isCicilan = ($status === 'cicilan' || ($bill->paid_amount > 0 && !$isLunas));
    
    $badgeClass = $isLunas 
        ? 'bg-emerald-100 text-emerald-900 border-emerald-300' 
        : ($isCicilan ? 'bg-amber-100 text-amber-900 border-amber-300' : 'bg-rose-100 text-rose-900 border-rose-300');
    
    $statusText = $isLunas 
        ? '🟢 LUNAS' 
        : ($isCicilan ? '🟡 DIBAYAR SEBAGIAN' : '🔴 BELUM DIBAYAR');

    $isCurrent = ($bill->is_current_month ?? false);
    $isFuture = ($bill->is_future ?? false);
@endphp

<div class="clay-card p-4 space-y-3 bg-white border-2 {{ $isCurrent && !$isLunas ? 'border-amber-400 ring-2 ring-amber-100' : ($isLunas ? 'border-slate-200' : ($isFuture ? 'border-blue-200' : 'border-rose-300')) }}">
    <div class="flex items-start justify-between gap-2">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-1.5 flex-wrap">
                <h4 class="text-xs font-black text-slate-900 uppercase leading-snug">
                    {{ $bill->display_title ?? 'Tagihan SPP Sekolah' }}
                </h4>
                @if($isCurrent)
                    <span class="px-1.5 py-0.2 rounded-md bg-blue-600 text-white text-[8px] font-black uppercase tracking-wider">
                        Bulan Ini
                    </span>
                @elseif($isFuture)
                    <span class="px-1.5 py-0.2 rounded-md bg-slate-100 text-slate-600 text-[8px] font-black uppercase">
                        Masa Depan
                    </span>
                @endif
            </div>
            <span class="text-[10px] text-slate-500 font-bold block mt-0.5">
                {{ $bill->academicYear->name ?? 'Tahun Ajaran Aktif' }}
            </span>
        </div>

        <span class="px-2.5 py-1 rounded-xl text-[9px] font-black border {{ $badgeClass }} shrink-0">
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

        @if($remaining > 0)
            <div class="flex items-center justify-between pt-1 border-t border-slate-200 text-rose-700 font-black">
                <span>Sisa Kewajiban:</span>
                <span>Rp {{ number_format($remaining, 0, ',', '.') }}</span>
            </div>
        @endif
    </div>
</div>
