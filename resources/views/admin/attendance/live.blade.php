@extends('layouts.admin')

@section('title', 'Live Presensi Real-Time - Pusat Absensi')

@push('styles')
<style>
    .edu-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease-in-out;
    }
    .edu-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.04);
    }
</style>
@endpush

@section('content')
<div class="space-y-6 font-sans">
    {{-- Unified Header --}}
    @include('admin.attendance.header')

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- STATISTIC METRIC CARDS (CLEAN CHIPS)            --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 md:gap-5">
        {{-- 1. Total Target --}}
        <div class="edu-card p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center text-lg font-bold">
                    <i class="fas fa-users"></i>
                </div>
                <span class="text-2xl lg:text-3xl font-extrabold text-slate-800 leading-none font-mono" id="stat_total_target">{{ number_format($stats['total_target']) }}</span>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-700 leading-normal">Total Terdaftar</p>
                <p class="text-[11px] font-medium text-slate-400 mt-0.5 leading-normal">{{ ucfirst($group) }} aktif</p>
            </div>
        </div>

        {{-- 2. Hadir --}}
        <div class="edu-card p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center text-lg font-bold">
                    <i class="fas fa-circle-check"></i>
                </div>
                <span class="text-2xl lg:text-3xl font-extrabold text-emerald-600 leading-none font-mono" id="stat_hadir">{{ number_format($stats['hadir']) }}</span>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-700 leading-normal">Hadir Tepat</p>
                <p class="text-[11px] font-medium text-slate-400 mt-0.5 leading-normal">Sebelum batas waktu</p>
            </div>
        </div>

        {{-- 3. Terlambat --}}
        <div class="edu-card p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center text-lg font-bold">
                    <i class="fas fa-clock"></i>
                </div>
                <span class="text-2xl lg:text-3xl font-extrabold text-amber-600 leading-none font-mono" id="stat_terlambat">{{ number_format($stats['terlambat']) }}</span>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-700 leading-normal">Terlambat</p>
                <p class="text-[11px] font-medium text-slate-400 mt-0.5 leading-normal">Lewat toleransi</p>
            </div>
        </div>

        {{-- 4. Izin & Sakit --}}
        <div class="edu-card p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center text-lg font-bold">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <span class="text-2xl lg:text-3xl font-extrabold text-blue-600 leading-none font-mono" id="stat_izin_sakit">{{ number_format($stats['izin'] + $stats['sakit'] + $stats['dinas_luar'] + $stats['cuti']) }}</span>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-700 leading-normal">Izin & Sakit</p>
                <p class="text-[11px] font-medium text-slate-400 mt-0.5 leading-normal">Surat resmi terlampir</p>
            </div>
        </div>

        {{-- 5. Alpha --}}
        <div class="edu-card p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center text-lg font-bold">
                    <i class="fas fa-circle-xmark"></i>
                </div>
                <span class="text-2xl lg:text-3xl font-extrabold text-rose-600 leading-none font-mono" id="stat_alpha">{{ number_format($stats['alpha']) }}</span>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-700 leading-normal">Alpha</p>
                <p class="text-[11px] font-medium text-slate-400 mt-0.5 leading-normal">Tanpa keterangan</p>
            </div>
        </div>

        {{-- 6. Belum Presensi --}}
        <div class="edu-card p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-600 border border-slate-200 flex items-center justify-center text-lg font-bold">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <span class="text-2xl lg:text-3xl font-extrabold text-slate-600 leading-none font-mono" id="stat_belum">{{ number_format($stats['belum']) }}</span>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-700 leading-normal">Belum Hadir</p>
                <p class="text-[11px] font-medium text-slate-400 mt-0.5 leading-normal">Menunggu tap/GPS</p>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- LIVE FEED STREAM CONTAINER                      --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="edu-card p-6 md:p-8 bg-white">
        <div class="flex items-center justify-between pb-5 border-b border-slate-100 mb-6 flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <span class="relative flex h-3.5 w-3.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-rose-600"></span>
                </span>
                <div>
                    <h3 class="text-base md:text-lg font-bold text-slate-800 leading-snug">Live Activity Feed (Real-Time Stream)</h3>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Data diperbarui otomatis setiap 4 detik saat ada presensi masuk</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="px-4 py-2 bg-slate-900 text-amber-300 rounded-xl text-xs font-mono font-bold" id="live_clock">
                    {{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i:s') }} WIB
                </span>
                <button type="button" onclick="pollLiveData()" class="px-4 py-2 bg-amber-400 hover:bg-amber-500 text-slate-900 rounded-xl text-xs font-bold uppercase tracking-wider transition active:scale-95 flex items-center gap-1.5 shadow-2xs" title="Refresh Sekarang">
                    <i class="fas fa-rotate text-xs"></i>
                    <span>Refresh</span>
                </button>
            </div>
        </div>

        {{-- Live Events Grid --}}
        <div id="live_events_container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($liveEvents as $ev)
                <div class="p-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition flex items-center justify-between gap-3 shadow-2xs">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-xs shrink-0 overflow-hidden text-slate-700">
                            @if(!empty($ev['photo_url']))
                                <img src="{{ $ev['photo_url'] }}" class="w-full h-full object-cover" alt="{{ $ev['name'] }}">
                            @else
                                <span>{{ strtoupper(substr($ev['name'], 0, 2)) }}</span>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-slate-800 text-xs md:text-sm leading-snug truncate">{{ $ev['name'] }}</div>
                            <div class="text-[11px] text-slate-400 truncate mt-0.5">{{ $ev['subtitle'] }} · {{ $ev['code'] }}</div>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <div class="flex items-center justify-end gap-1.5 mb-1.5">
                            @if($ev['recorded_via'] === 'gps')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    <i class="fas fa-location-dot mr-1"></i> GPS
                                </span>
                            @elseif($ev['recorded_via'] === 'rfid')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    <i class="fas fa-id-card mr-1"></i> RFID
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    <i class="fas fa-pen mr-1"></i> Manual
                                </span>
                            @endif

                            @php
                                $badgeCls = match($ev['status']) {
                                    'hadir'     => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'terlambat' => 'bg-amber-100 text-amber-800 border-amber-200',
                                    default     => 'bg-rose-100 text-rose-800 border-rose-200'
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $badgeCls }}">
                                {{ strtoupper($ev['status']) }}
                            </span>
                        </div>
                        <div class="text-xs font-mono font-bold text-slate-700">
                            {{ $ev['time_in'] ?? '-' }} WIB
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center text-slate-400">
                    <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-2 text-slate-400 text-xl">
                        <i class="fas fa-satellite-dish"></i>
                    </div>
                    <p class="text-sm font-bold text-slate-700">Belum ada aktivitas presensi masuk pada tanggal ini.</p>
                    <p class="text-xs text-slate-500 mt-0.5">Aktivitas presensi GPS atau RFID akan muncul di sini secara otomatis.</p>
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
                        : `<span>${initials}</span>`;

                    let viaBadge = '';
                    if (ev.recorded_via === 'gps') {
                        viaBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200"><i class="fas fa-location-dot mr-1"></i> GPS</span>';
                    } else if (ev.recorded_via === 'rfid') {
                        viaBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200"><i class="fas fa-id-card mr-1"></i> RFID</span>';
                    } else {
                        viaBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200"><i class="fas fa-pen mr-1"></i> Manual</span>';
                    }

                    let statusClass = 'bg-rose-100 text-rose-800 border-rose-200';
                    if (ev.status === 'hadir') statusClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                    else if (ev.status === 'terlambat') statusClass = 'bg-amber-100 text-amber-800 border-amber-200';

                    html += `
                        <div class="p-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition flex items-center justify-between gap-3 shadow-2xs">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-xs shrink-0 overflow-hidden text-slate-700">
                                    ${photoHtml}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-800 text-xs md:text-sm leading-snug truncate">${ev.name}</div>
                                    <div class="text-[11px] text-slate-400 truncate mt-0.5">${ev.subtitle} · ${ev.code}</div>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <div class="flex items-center justify-end gap-1.5 mb-1.5">
                                    ${viaBadge}
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border ${statusClass}">
                                        ${ev.status.toUpperCase()}
                                    </span>
                                </div>
                                <div class="text-xs font-mono font-bold text-slate-700">
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
