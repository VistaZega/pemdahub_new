@extends('layouts.admin')

@section('title', 'Live Presensi Real-Time - Haute Academic Suite')

@push('styles')
<style>
    .luxury-stat-card {
        background: rgba(13, 20, 35, 0.65) !important;
        backdrop-filter: blur(24px) !important;
        -webkit-backdrop-filter: blur(24px) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.6), inset 0 1px 1px rgba(255, 255, 255, 0.08) !important;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }
    .luxury-stat-card:hover {
        transform: translateY(-5px);
        border-color: rgba(212, 175, 55, 0.5) !important;
        box-shadow: 0 25px 55px rgba(0, 0, 0, 0.75), 0 0 25px rgba(212, 175, 55, 0.15) !important;
    }
    .luxury-table-container {
        background: rgba(10, 15, 26, 0.8) !important;
        backdrop-filter: blur(28px) !important;
        -webkit-backdrop-filter: blur(28px) !important;
        border: 1px solid rgba(212, 175, 55, 0.25) !important;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8), inset 0 1px 1px rgba(255, 255, 255, 0.1) !important;
    }
    .luxury-event-card {
        background: rgba(15, 23, 42, 0.7) !important;
        backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        transition: all 0.3s ease;
    }
    .luxury-event-card:hover {
        background: rgba(25, 35, 60, 0.85) !important;
        border-color: rgba(212, 175, 55, 0.4) !important;
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.6);
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    {{-- Unified Luxury Header --}}
    @include('admin.attendance.header')

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- STATISTIC METRIC CARDS (LIQUID GLASS JEWEL)    --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-5">
        {{-- 1. Total Target --}}
        <div class="luxury-stat-card rounded-3xl p-5 flex flex-col justify-between relative overflow-hidden text-white">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500/20 to-indigo-900/40 border border-indigo-400/40 text-indigo-300 flex items-center justify-center text-lg font-black shadow-lg">
                    <i class="fas fa-users text-indigo-300"></i>
                </div>
                <span class="text-3xl font-serif font-bold text-white leading-none font-mono" id="stat_total_target">{{ number_format($stats['total_target']) }}</span>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-200">Total Terdaftar</p>
                <p class="text-[11px] font-medium text-slate-400 mt-0.5">{{ ucfirst($group) }} aktif</p>
            </div>
        </div>

        {{-- 2. Hadir --}}
        <div class="luxury-stat-card rounded-3xl p-5 flex flex-col justify-between relative overflow-hidden text-white">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500/20 to-emerald-900/40 border border-emerald-400/40 text-emerald-300 flex items-center justify-center text-lg font-black shadow-lg">
                    <i class="fas fa-circle-check text-emerald-300"></i>
                </div>
                <span class="text-3xl font-serif font-bold text-emerald-300 leading-none font-mono" id="stat_hadir">{{ number_format($stats['hadir']) }}</span>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-200">Hadir Tepat</p>
                <p class="text-[11px] font-medium text-slate-400 mt-0.5">Sebelum batas waktu</p>
            </div>
        </div>

        {{-- 3. Terlambat --}}
        <div class="luxury-stat-card rounded-3xl p-5 flex flex-col justify-between relative overflow-hidden text-white">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500/20 to-amber-900/40 border border-amber-400/40 text-amber-300 flex items-center justify-center text-lg font-black shadow-lg">
                    <i class="fas fa-clock text-amber-300"></i>
                </div>
                <span class="text-3xl font-serif font-bold text-amber-300 leading-none font-mono" id="stat_terlambat">{{ number_format($stats['terlambat']) }}</span>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-200">Terlambat</p>
                <p class="text-[11px] font-medium text-slate-400 mt-0.5">Lewat toleransi</p>
            </div>
        </div>

        {{-- 4. Izin & Sakit --}}
        <div class="luxury-stat-card rounded-3xl p-5 flex flex-col justify-between relative overflow-hidden text-white">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500/20 to-blue-900/40 border border-blue-400/40 text-blue-300 flex items-center justify-center text-lg font-black shadow-lg">
                    <i class="fas fa-envelope-open-text text-blue-300"></i>
                </div>
                <span class="text-3xl font-serif font-bold text-blue-300 leading-none font-mono" id="stat_izin_sakit">{{ number_format($stats['izin'] + $stats['sakit'] + $stats['dinas_luar'] + $stats['cuti']) }}</span>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-200">Izin & Sakit</p>
                <p class="text-[11px] font-medium text-slate-400 mt-0.5">Surat resmi terlampir</p>
            </div>
        </div>

        {{-- 5. Alpha --}}
        <div class="luxury-stat-card rounded-3xl p-5 flex flex-col justify-between relative overflow-hidden text-white">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-rose-500/20 to-rose-900/40 border border-rose-400/40 text-rose-300 flex items-center justify-center text-lg font-black shadow-lg">
                    <i class="fas fa-circle-xmark text-rose-300"></i>
                </div>
                <span class="text-3xl font-serif font-bold text-rose-300 leading-none font-mono" id="stat_alpha">{{ number_format($stats['alpha']) }}</span>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-200">Alpha</p>
                <p class="text-[11px] font-medium text-slate-400 mt-0.5">Tanpa keterangan</p>
            </div>
        </div>

        {{-- 6. Belum Presensi --}}
        <div class="luxury-stat-card rounded-3xl p-5 flex flex-col justify-between relative overflow-hidden text-white">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-slate-500/20 to-slate-900/40 border border-slate-400/40 text-slate-300 flex items-center justify-center text-lg font-black shadow-lg">
                    <i class="fas fa-hourglass-half text-slate-300"></i>
                </div>
                <span class="text-3xl font-serif font-bold text-slate-300 leading-none font-mono" id="stat_belum">{{ number_format($stats['belum']) }}</span>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-200">Belum Hadir</p>
                <p class="text-[11px] font-medium text-slate-400 mt-0.5">Menunggu tap/GPS</p>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- LIVE FEED STREAM CONTAINER (LIQUID GLASS SUITE) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="luxury-table-container rounded-[2.5rem] p-7 md:p-9 text-white">
        <div class="flex items-center justify-between pb-6 border-b border-white/10 mb-8 flex-wrap gap-4">
            <div class="flex items-center gap-3.5">
                <span class="relative flex h-4 w-4">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-4 w-4 bg-rose-600"></span>
                </span>
                <div>
                    <h3 class="font-serif text-xl font-bold text-white leading-tight">Live Activity Feed (Real-Time Stream)</h3>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">Data diperbarui otomatis setiap 4 detik saat ada presensi masuk</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="px-5 py-2.5 bg-black/60 text-amber-300 rounded-2xl text-xs font-mono font-bold border border-amber-400/40 shadow-md backdrop-blur-md" id="live_clock">
                    {{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i:s') }} WIB
                </span>
                <button type="button" onclick="pollLiveData()" class="px-6 py-2.5 bg-gradient-to-r from-amber-200 via-amber-400 to-yellow-600 text-black rounded-2xl text-xs font-black uppercase tracking-wider border border-white/40 transition active:scale-95 shadow-lg flex items-center gap-2" title="Refresh Sekarang">
                    <i class="fas fa-rotate text-black"></i>
                    <span>Refresh</span>
                </button>
            </div>
        </div>

        {{-- Live Events Grid --}}
        <div id="live_events_container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($liveEvents as $ev)
                <div class="luxury-event-card p-5 rounded-3xl flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-yellow-700 text-black border border-white/40 flex items-center justify-center font-black text-xs shrink-0 shadow-sm overflow-hidden">
                            @if(!empty($ev['photo_url']))
                                <img src="{{ $ev['photo_url'] }}" class="w-full h-full object-cover" alt="{{ $ev['name'] }}">
                            @else
                                <span class="text-black font-black text-xs">{{ strtoupper(substr($ev['name'], 0, 2)) }}</span>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-white text-sm truncate">{{ $ev['name'] }}</div>
                            <div class="text-xs text-amber-200/60 font-medium truncate mt-0.5">{{ $ev['subtitle'] }} · {{ $ev['code'] }}</div>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <div class="flex items-center justify-end gap-2 mb-2">
                            @if($ev['recorded_via'] === 'gps')
                                <span class="px-3 py-1 rounded-xl text-[10px] font-bold bg-blue-500/15 text-blue-300 border border-blue-400/40">
                                    <i class="fas fa-location-dot mr-1"></i> GPS
                                </span>
                            @elseif($ev['recorded_via'] === 'rfid')
                                <span class="px-3 py-1 rounded-xl text-[10px] font-bold bg-amber-500/15 text-amber-300 border border-amber-400/40">
                                    <i class="fas fa-id-card mr-1"></i> RFID
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-xl text-[10px] font-bold bg-slate-500/15 text-slate-300 border border-slate-400/40">
                                    <i class="fas fa-pen mr-1"></i> Manual
                                </span>
                            @endif

                            @php
                                $badgeCls = match($ev['status']) {
                                    'hadir'     => 'bg-emerald-500/20 text-emerald-300 border-emerald-400/50',
                                    'terlambat' => 'bg-amber-500/20 text-amber-300 border-amber-400/50',
                                    default     => 'bg-rose-500/20 text-rose-300 border-rose-400/50'
                                };
                            @endphp
                            <span class="px-3 py-1 rounded-xl text-[10px] font-bold border {{ $badgeCls }}">
                                {{ strtoupper($ev['status']) }}
                            </span>
                        </div>
                        <div class="text-xs font-mono font-bold text-slate-200">
                            {{ $ev['time_in'] ?? '-' }} WIB
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 text-center text-slate-400">
                    <div class="w-16 h-16 bg-amber-400/10 rounded-3xl border border-amber-400/30 flex items-center justify-center mx-auto mb-4 text-amber-300 text-3xl shadow-lg">
                        <i class="fas fa-satellite-dish"></i>
                    </div>
                    <p class="font-serif text-lg text-white font-medium">Belum ada aktivitas presensi masuk pada tanggal ini.</p>
                    <p class="text-xs text-slate-400 mt-1">Aktivitas presensi GPS atau RFID akan muncul di sini secara langsung.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
let livePollingInterval = null;

function pollLiveData() {
    fetch(window.location.href, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (data.stats) {
                document.getElementById('stat_total_target').innerText = Number(data.stats.total_target).toLocaleString();
                document.getElementById('stat_hadir').innerText = Number(data.stats.hadir).toLocaleString();
                document.getElementById('stat_terlambat').innerText = Number(data.stats.terlambat).toLocaleString();
                document.getElementById('stat_izin_sakit').innerText = Number(data.stats.izin + data.stats.sakit + data.stats.dinas_luar + data.stats.cuti).toLocaleString();
                document.getElementById('stat_alpha').innerText = Number(data.stats.alpha).toLocaleString();
                document.getElementById('stat_belum').innerText = Number(data.stats.belum).toLocaleString();
            }

            if (data.time_now) {
                document.getElementById('live_clock').innerText = data.time_now + ' WIB';
            }

            const container = document.getElementById('live_events_container');
            if (data.liveEvents && data.liveEvents.length > 0) {
                let html = '';
                data.liveEvents.forEach(ev => {
                    const initials = ev.name.substring(0, 2).toUpperCase();
                    const photoHtml = ev.photo_url 
                        ? `<img src="${ev.photo_url}" class="w-full h-full object-cover" alt="${ev.name}">`
                        : `<span class="text-black font-black text-xs">${initials}</span>`;

                    let viaBadge = '';
                    if (ev.recorded_via === 'gps') {
                        viaBadge = '<span class="px-3 py-1 rounded-xl text-[10px] font-bold bg-blue-500/15 text-blue-300 border border-blue-400/40"><i class="fas fa-location-dot mr-1"></i> GPS</span>';
                    } else if (ev.recorded_via === 'rfid') {
                        viaBadge = '<span class="px-3 py-1 rounded-xl text-[10px] font-bold bg-amber-500/15 text-amber-300 border border-amber-400/40"><i class="fas fa-id-card mr-1"></i> RFID</span>';
                    } else {
                        viaBadge = '<span class="px-3 py-1 rounded-xl text-[10px] font-bold bg-slate-500/15 text-slate-300 border border-slate-400/40"><i class="fas fa-pen mr-1"></i> Manual</span>';
                    }

                    let statusClass = 'bg-rose-500/20 text-rose-300 border-rose-400/50';
                    if (ev.status === 'hadir') statusClass = 'bg-emerald-500/20 text-emerald-300 border-emerald-400/50';
                    else if (ev.status === 'terlambat') statusClass = 'bg-amber-500/20 text-amber-300 border-amber-400/50';

                    html += `
                        <div class="luxury-event-card p-5 rounded-3xl flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-yellow-700 text-black border border-white/40 flex items-center justify-center font-black text-xs shrink-0 shadow-sm overflow-hidden">
                                    ${photoHtml}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-white text-sm truncate">${ev.name}</div>
                                    <div class="text-xs text-amber-200/60 font-medium truncate mt-0.5">${ev.subtitle} · ${ev.code}</div>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <div class="flex items-center justify-end gap-2 mb-2">
                                    ${viaBadge}
                                    <span class="px-3 py-1 rounded-xl text-[10px] font-bold border ${statusClass}">
                                        ${ev.status.toUpperCase()}
                                    </span>
                                </div>
                                <div class="text-xs font-mono font-bold text-slate-200">
                                    ${ev.time_in || '-'} WIB
                                </div>
                            </div>
                        </div>
                    `;
                });
                container.innerHTML = html;
            }
        }
    })
    .catch(err => console.error('Live polling error:', err));
}

document.addEventListener('DOMContentLoaded', () => {
    livePollingInterval = setInterval(pollLiveData, 4500);
});

window.addEventListener('beforeunload', () => {
    if (livePollingInterval) clearInterval(livePollingInterval);
});
</script>
@endsection
