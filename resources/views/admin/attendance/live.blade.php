@extends('layouts.admin')

@section('title', 'Live Presensi Real-Time - Edu Attendance Hub')

@push('styles')
<style>
    .clay-stat-card {
        background: #ffffff;
        border-radius: 28px;
        box-shadow: 8px 12px 24px rgba(30, 41, 59, 0.06), -6px -6px 16px rgba(255, 255, 255, 0.9), inset 2px 2px 4px rgba(255, 255, 255, 0.8), inset -2px -2px 4px rgba(0, 0, 0, 0.03);
        border: 2px solid #f1f5f9;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .clay-stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 12px 18px 30px rgba(30, 41, 59, 0.12), -8px -8px 20px rgba(255, 255, 255, 1);
    }
    .clay-event-card {
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 6px 10px 20px rgba(30, 41, 59, 0.05), -4px -4px 12px rgba(255, 255, 255, 0.9), inset 2px 2px 3px rgba(255, 255, 255, 0.8);
        border: 2px solid #f1f5f9;
        transition: all 0.3s ease;
    }
    .clay-event-card:hover {
        transform: translateY(-4px);
        box-shadow: 10px 16px 26px rgba(30, 41, 59, 0.1);
        border-color: #e2e8f0;
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    {{-- Unified Playful Clay Header --}}
    @include('admin.attendance.header')

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- STATISTIC METRIC CARDS (PLAYFUL CLAY CHIPS)    --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-5">
        {{-- 1. Total Target --}}
        <div class="clay-stat-card rounded-3xl p-5 flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 text-white flex items-center justify-center text-xl font-bold shadow-md"
                     style="box-shadow: 3px 5px 12px rgba(67, 97, 238, 0.35);">
                    <i class="fas fa-users"></i>
                </div>
                <span class="text-3xl font-extrabold text-slate-800 leading-none font-mono" style="font-family: var(--clay-font-title);" id="stat_total_target">{{ number_format($stats['total_target']) }}</span>
            </div>
            <div>
                <p class="text-xs font-extrabold uppercase tracking-wider text-slate-700" style="font-family: var(--clay-font-title);">Total Terdaftar</p>
                <p class="text-[11px] font-semibold text-slate-400 mt-0.5">{{ ucfirst($group) }} aktif</p>
            </div>
        </div>

        {{-- 2. Hadir --}}
        <div class="clay-stat-card rounded-3xl p-5 flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-600 text-white flex items-center justify-center text-xl font-bold shadow-md"
                     style="box-shadow: 3px 5px 12px rgba(6, 214, 160, 0.35);">
                    <i class="fas fa-circle-check"></i>
                </div>
                <span class="text-3xl font-extrabold text-emerald-600 leading-none font-mono" style="font-family: var(--clay-font-title);" id="stat_hadir">{{ number_format($stats['hadir']) }}</span>
            </div>
            <div>
                <p class="text-xs font-extrabold uppercase tracking-wider text-slate-700" style="font-family: var(--clay-font-title);">Hadir Tepat</p>
                <p class="text-[11px] font-semibold text-slate-400 mt-0.5">Sebelum batas waktu</p>
            </div>
        </div>

        {{-- 3. Terlambat --}}
        <div class="clay-stat-card rounded-3xl p-5 flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white flex items-center justify-center text-xl font-bold shadow-md"
                     style="box-shadow: 3px 5px 12px rgba(245, 158, 11, 0.35);">
                    <i class="fas fa-clock"></i>
                </div>
                <span class="text-3xl font-extrabold text-amber-600 leading-none font-mono" style="font-family: var(--clay-font-title);" id="stat_terlambat">{{ number_format($stats['terlambat']) }}</span>
            </div>
            <div>
                <p class="text-xs font-extrabold uppercase tracking-wider text-slate-700" style="font-family: var(--clay-font-title);">Terlambat</p>
                <p class="text-[11px] font-semibold text-slate-400 mt-0.5">Lewat toleransi</p>
            </div>
        </div>

        {{-- 4. Izin & Sakit --}}
        <div class="clay-stat-card rounded-3xl p-5 flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-sky-400 to-blue-600 text-white flex items-center justify-center text-xl font-bold shadow-md"
                     style="box-shadow: 3px 5px 12px rgba(56, 189, 248, 0.35);">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <span class="text-3xl font-extrabold text-sky-600 leading-none font-mono" style="font-family: var(--clay-font-title);" id="stat_izin_sakit">{{ number_format($stats['izin'] + $stats['sakit'] + $stats['dinas_luar'] + $stats['cuti']) }}</span>
            </div>
            <div>
                <p class="text-xs font-extrabold uppercase tracking-wider text-slate-700" style="font-family: var(--clay-font-title);">Izin & Sakit</p>
                <p class="text-[11px] font-semibold text-slate-400 mt-0.5">Surat resmi terlampir</p>
            </div>
        </div>

        {{-- 5. Alpha --}}
        <div class="clay-stat-card rounded-3xl p-5 flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-rose-400 to-pink-600 text-white flex items-center justify-center text-xl font-bold shadow-md"
                     style="box-shadow: 3px 5px 12px rgba(244, 63, 94, 0.35);">
                    <i class="fas fa-circle-xmark"></i>
                </div>
                <span class="text-3xl font-extrabold text-rose-600 leading-none font-mono" style="font-family: var(--clay-font-title);" id="stat_alpha">{{ number_format($stats['alpha']) }}</span>
            </div>
            <div>
                <p class="text-xs font-extrabold uppercase tracking-wider text-slate-700" style="font-family: var(--clay-font-title);">Alpha</p>
                <p class="text-[11px] font-semibold text-slate-400 mt-0.5">Tanpa keterangan</p>
            </div>
        </div>

        {{-- 6. Belum Presensi --}}
        <div class="clay-stat-card rounded-3xl p-5 flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-slate-400 to-slate-600 text-white flex items-center justify-center text-xl font-bold shadow-md"
                     style="box-shadow: 3px 5px 12px rgba(100, 116, 139, 0.35);">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <span class="text-3xl font-extrabold text-slate-600 leading-none font-mono" style="font-family: var(--clay-font-title);" id="stat_belum">{{ number_format($stats['belum']) }}</span>
            </div>
            <div>
                <p class="text-xs font-extrabold uppercase tracking-wider text-slate-700" style="font-family: var(--clay-font-title);">Belum Hadir</p>
                <p class="text-[11px] font-semibold text-slate-400 mt-0.5">Menunggu tap/GPS</p>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- LIVE FEED STREAM CONTAINER (PLAYFUL CLAY)       --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="clay-card rounded-[2.5rem] p-7 md:p-9 bg-white">
        <div class="flex items-center justify-between pb-6 border-b border-slate-100 mb-8 flex-wrap gap-4">
            <div class="flex items-center gap-3.5">
                <span class="relative flex h-4 w-4">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-4 w-4 bg-rose-600"></span>
                </span>
                <div>
                    <h3 class="text-xl font-extrabold text-slate-800 leading-tight" style="font-family: var(--clay-font-title);">Live Activity Feed (Real-Time Stream)</h3>
                    <p class="text-xs text-slate-500 font-semibold mt-0.5">Data diperbarui otomatis setiap 4 detik saat ada presensi masuk</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="px-5 py-2.5 bg-slate-900 text-yellow-300 rounded-2xl text-xs font-mono font-extrabold shadow-md" id="live_clock">
                    {{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i:s') }} WIB
                </span>
                <button type="button" onclick="pollLiveData()" class="clay-btn-amber px-6 py-2.5 rounded-2xl text-xs font-extrabold uppercase tracking-wider transition active:scale-95 shadow-md flex items-center gap-2" title="Refresh Sekarang">
                    <i class="fas fa-rotate text-slate-900"></i>
                    <span>Refresh</span>
                </button>
            </div>
        </div>

        {{-- Live Events Grid --}}
        <div id="live_events_container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($liveEvents as $ev)
                <div class="clay-event-card p-5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-yellow-300 to-amber-500 text-slate-900 border-2 border-white flex items-center justify-center font-extrabold text-xs shrink-0 shadow-sm overflow-hidden">
                            @if(!empty($ev['photo_url']))
                                <img src="{{ $ev['photo_url'] }}" class="w-full h-full object-cover" alt="{{ $ev['name'] }}">
                            @else
                                <span class="text-slate-900 font-extrabold text-xs">{{ strtoupper(substr($ev['name'], 0, 2)) }}</span>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="font-extrabold text-slate-800 text-sm truncate">{{ $ev['name'] }}</div>
                            <div class="text-xs text-slate-400 font-semibold truncate mt-0.5">{{ $ev['subtitle'] }} · {{ $ev['code'] }}</div>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <div class="flex items-center justify-end gap-2 mb-2">
                            @if($ev['recorded_via'] === 'gps')
                                <span class="px-3 py-1 rounded-xl text-[10px] font-extrabold bg-blue-100 text-blue-700 border border-blue-200">
                                    <i class="fas fa-location-dot mr-1"></i> GPS
                                </span>
                            @elseif($ev['recorded_via'] === 'rfid')
                                <span class="px-3 py-1 rounded-xl text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                                    <i class="fas fa-id-card mr-1"></i> RFID
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-xl text-[10px] font-extrabold bg-slate-100 text-slate-700 border border-slate-200">
                                    <i class="fas fa-pen mr-1"></i> Manual
                                </span>
                            @endif

                            @php
                                $badgeCls = match($ev['status']) {
                                    'hadir'     => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                    'terlambat' => 'bg-amber-100 text-amber-800 border-amber-300',
                                    default     => 'bg-rose-100 text-rose-800 border-rose-300'
                                };
                            @endphp
                            <span class="px-3 py-1 rounded-xl text-[10px] font-extrabold border {{ $badgeCls }}">
                                {{ strtoupper($ev['status']) }}
                            </span>
                        </div>
                        <div class="text-xs font-mono font-extrabold text-slate-800">
                            {{ $ev['time_in'] ?? '-' }} WIB
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 text-center text-slate-400">
                    <div class="w-16 h-16 bg-amber-100 rounded-3xl border-2 border-amber-200 flex items-center justify-center mx-auto mb-4 text-amber-600 text-3xl shadow-md">
                        <i class="fas fa-satellite-dish"></i>
                    </div>
                    <p class="text-lg text-slate-800 font-extrabold" style="font-family: var(--clay-font-title);">Belum ada aktivitas presensi masuk pada tanggal ini.</p>
                    <p class="text-xs text-slate-500 font-semibold mt-1">Aktivitas presensi GPS atau RFID akan muncul di sini secara langsung.</p>
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
                        : `<span class="text-slate-900 font-extrabold text-xs">${initials}</span>`;

                    let viaBadge = '';
                    if (ev.recorded_via === 'gps') {
                        viaBadge = '<span class="px-3 py-1 rounded-xl text-[10px] font-extrabold bg-blue-100 text-blue-700 border border-blue-200"><i class="fas fa-location-dot mr-1"></i> GPS</span>';
                    } else if (ev.recorded_via === 'rfid') {
                        viaBadge = '<span class="px-3 py-1 rounded-xl text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200"><i class="fas fa-id-card mr-1"></i> RFID</span>';
                    } else {
                        viaBadge = '<span class="px-3 py-1 rounded-xl text-[10px] font-extrabold bg-slate-100 text-slate-700 border border-slate-200"><i class="fas fa-pen mr-1"></i> Manual</span>';
                    }

                    let statusClass = 'bg-rose-100 text-rose-800 border-rose-300';
                    if (ev.status === 'hadir') statusClass = 'bg-emerald-100 text-emerald-800 border-emerald-300';
                    else if (ev.status === 'terlambat') statusClass = 'bg-amber-100 text-amber-800 border-amber-300';

                    html += `
                        <div class="clay-event-card p-5 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-yellow-300 to-amber-500 text-slate-900 border-2 border-white flex items-center justify-center font-extrabold text-xs shrink-0 shadow-sm overflow-hidden">
                                    ${photoHtml}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-extrabold text-slate-800 text-sm truncate">${ev.name}</div>
                                    <div class="text-xs text-slate-400 font-semibold truncate mt-0.5">${ev.subtitle} · ${ev.code}</div>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <div class="flex items-center justify-end gap-2 mb-2">
                                    ${viaBadge}
                                    <span class="px-3 py-1 rounded-xl text-[10px] font-extrabold border ${statusClass}">
                                        ${ev.status.toUpperCase()}
                                    </span>
                                </div>
                                <div class="text-xs font-mono font-extrabold text-slate-800">
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
