@extends('layouts.admin')

@section('title', 'Live Presensi Real-Time - Pusat Absensi')

@section('content')
<div class="space-y-8">
    {{-- Unified LMS Header --}}
    @include('admin.attendance.header')

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- LIVE STATUS STAT METRICS (LMS STYLE) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        {{-- 1. Total Target --}}
        <div class="bg-black text-amber-400 rounded-3xl p-5 border-2 border-black shadow-md">
            <div class="flex items-center justify-between text-xs font-black uppercase tracking-wider mb-2">
                <span>Total Terdaftar</span>
                <i class="fas fa-users"></i>
            </div>
            <div class="text-3xl font-black font-mono text-white" id="stat_total_target">
                {{ number_format($stats['total_target']) }}
            </div>
            <span class="text-[10px] font-black uppercase tracking-wider text-amber-300">{{ $group }} Aktif</span>
        </div>

        {{-- 2. Hadir --}}
        <div class="bg-emerald-300 text-black rounded-3xl p-5 border-2 border-black shadow-md">
            <div class="flex items-center justify-between text-xs font-black uppercase tracking-wider mb-2">
                <span>Hadir Tepat</span>
                <i class="fas fa-circle-check"></i>
            </div>
            <div class="text-3xl font-black font-mono" id="stat_hadir">
                {{ number_format($stats['hadir']) }}
            </div>
            <span class="text-[10px] font-black uppercase tracking-wider">Tepat Waktu</span>
        </div>

        {{-- 3. Terlambat --}}
        <div class="bg-amber-300 text-black rounded-3xl p-5 border-2 border-black shadow-md">
            <div class="flex items-center justify-between text-xs font-black uppercase tracking-wider mb-2">
                <span>Terlambat</span>
                <i class="fas fa-clock"></i>
            </div>
            <div class="text-3xl font-black font-mono" id="stat_terlambat">
                {{ number_format($stats['terlambat']) }}
            </div>
            <span class="text-[10px] font-black uppercase tracking-wider">Lewat Batas</span>
        </div>

        {{-- 4. Izin & Sakit --}}
        <div class="bg-sky-300 text-black rounded-3xl p-5 border-2 border-black shadow-md">
            <div class="flex items-center justify-between text-xs font-black uppercase tracking-wider mb-2">
                <span>Izin / Sakit</span>
                <i class="fas fa-envelope-open-text"></i>
            </div>
            <div class="text-3xl font-black font-mono" id="stat_izin_sakit">
                {{ number_format($stats['izin'] + $stats['sakit'] + $stats['dinas_luar'] + $stats['cuti']) }}
            </div>
            <span class="text-[10px] font-black uppercase tracking-wider">Keterangan Resmi</span>
        </div>

        {{-- 5. Alpha --}}
        <div class="bg-rose-400 text-black rounded-3xl p-5 border-2 border-black shadow-md">
            <div class="flex items-center justify-between text-xs font-black uppercase tracking-wider mb-2">
                <span>Alpha</span>
                <i class="fas fa-circle-xmark"></i>
            </div>
            <div class="text-3xl font-black font-mono" id="stat_alpha">
                {{ number_format($stats['alpha']) }}
            </div>
            <span class="text-[10px] font-black uppercase tracking-wider">Tanpa Keterangan</span>
        </div>

        {{-- 6. Belum Hadir --}}
        <div class="bg-gray-100 text-black rounded-3xl p-5 border-2 border-black shadow-md">
            <div class="flex items-center justify-between text-xs font-black uppercase tracking-wider mb-2">
                <span>Belum Absen</span>
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="text-3xl font-black font-mono" id="stat_belum">
                {{ number_format($stats['belum']) }}
            </div>
            <span class="text-[10px] font-black uppercase tracking-wider text-gray-600">Menunggu Tap/GPS</span>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- LIVE FEED STREAM CONTAINER --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="bg-white rounded-3xl border-2 border-black p-6 md:p-8 shadow-xl">
        <div class="flex items-center justify-between pb-5 border-b-2 border-black mb-6 flex-wrap gap-4">
            <div class="flex items-center gap-3">
                <span class="relative flex h-4 w-4">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-4 w-4 bg-rose-600"></span>
                </span>
                <div>
                    <h3 class="text-lg font-black text-black leading-tight">Live Activity Feed (Real-Time Stream)</h3>
                    <p class="text-xs text-gray-500 font-bold">Data diperbarui otomatis setiap 4 detik saat ada presensi masuk</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="px-4 py-2 bg-black text-amber-400 rounded-2xl text-xs font-mono font-black border-2 border-black shadow-sm" id="live_clock">
                    {{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i:s') }} WIB
                </span>
                <button type="button" onclick="pollLiveData()" class="px-4 py-2 bg-amber-300 hover:bg-amber-400 text-black rounded-2xl text-xs font-black uppercase tracking-wider border-2 border-black transition active:scale-95 shadow-sm" title="Refresh Sekarang">
                    <i class="fas fa-rotate mr-1"></i> Refresh
                </button>
            </div>
        </div>

        {{-- Live Events Grid --}}
        <div id="live_events_container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($liveEvents as $ev)
                <div class="p-4 rounded-3xl border-2 border-black hover:bg-amber-50 transition-all shadow-sm flex items-center justify-between gap-3 bg-white">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div class="w-11 h-11 rounded-2xl bg-black text-amber-400 border-2 border-black flex items-center justify-center font-black text-xs shrink-0 shadow-xs">
                            {{ strtoupper(substr($ev['name'], 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="font-black text-black text-sm truncate">{{ $ev['name'] }}</div>
                            <div class="text-xs text-gray-500 font-bold truncate">{{ $ev['subtitle'] }} · {{ $ev['code'] }}</div>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <div class="flex items-center justify-end gap-1.5 mb-1.5">
                            @if($ev['recorded_via'] === 'gps')
                                <span class="px-2 py-0.5 rounded-xl text-[9px] font-black bg-blue-100 text-blue-900 border border-blue-900">
                                    <i class="fas fa-location-dot"></i> GPS
                                </span>
                            @elseif($ev['recorded_via'] === 'rfid')
                                <span class="px-2 py-0.5 rounded-xl text-[9px] font-black bg-amber-200 text-black border border-black">
                                    <i class="fas fa-id-card"></i> RFID
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-xl text-[9px] font-black bg-gray-100 text-black border border-black">
                                    <i class="fas fa-pen"></i> Manual
                                </span>
                            @endif

                            <span class="px-2 py-0.5 rounded-xl text-[9px] font-black border border-black
                                  {{ $ev['status'] === 'hadir' ? 'bg-emerald-300 text-black' : ($ev['status'] === 'terlambat' ? 'bg-amber-300 text-black' : 'bg-rose-400 text-black') }}">
                                {{ strtoupper($ev['status']) }}
                            </span>
                        </div>
                        <div class="text-xs font-mono font-black text-black">
                            {{ $ev['time_in'] ?? '-' }} WIB
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 text-center text-gray-400">
                    <div class="w-16 h-16 bg-amber-100 rounded-3xl border-2 border-black flex items-center justify-center mx-auto mb-4 text-black text-3xl shadow-md">
                        <i class="fas fa-satellite-dish"></i>
                    </div>
                    <p class="font-black text-base text-black">Belum ada aktivitas presensi masuk pada tanggal ini.</p>
                    <p class="text-xs text-gray-500 font-bold mt-1">Aktivitas presensi GPS atau RFID akan muncul di sini secara langsung.</p>
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
            // Update stats
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

            // Render live events
            const container = document.getElementById('live_events_container');
            if (data.liveEvents && data.liveEvents.length > 0) {
                let html = '';
                data.liveEvents.forEach(ev => {
                    const initials = ev.name.substring(0, 2).toUpperCase();
                    let viaBadge = '';
                    if (ev.recorded_via === 'gps') {
                        viaBadge = '<span class="px-2 py-0.5 rounded-xl text-[9px] font-black bg-blue-100 text-blue-900 border border-blue-900"><i class="fas fa-location-dot"></i> GPS</span>';
                    } else if (ev.recorded_via === 'rfid') {
                        viaBadge = '<span class="px-2 py-0.5 rounded-xl text-[9px] font-black bg-amber-200 text-black border border-black"><i class="fas fa-id-card"></i> RFID</span>';
                    } else {
                        viaBadge = '<span class="px-2 py-0.5 rounded-xl text-[9px] font-black bg-gray-100 text-black border border-black"><i class="fas fa-pen"></i> Manual</span>';
                    }

                    let statusClass = 'bg-rose-400 text-black';
                    if (ev.status === 'hadir') statusClass = 'bg-emerald-300 text-black';
                    else if (ev.status === 'terlambat') statusClass = 'bg-amber-300 text-black';

                    html += `
                        <div class="p-4 rounded-3xl border-2 border-black hover:bg-amber-50 transition-all shadow-sm flex items-center justify-between gap-3 bg-white">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-11 h-11 rounded-2xl bg-black text-amber-400 border-2 border-black flex items-center justify-center font-black text-xs shrink-0 shadow-xs">
                                    ${initials}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-black text-black text-sm truncate">${ev.name}</div>
                                    <div class="text-xs text-gray-500 font-bold truncate">${ev.subtitle} · ${ev.code}</div>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <div class="flex items-center justify-end gap-1.5 mb-1.5">
                                    ${viaBadge}
                                    <span class="px-2 py-0.5 rounded-xl text-[9px] font-black border border-black ${statusClass}">
                                        ${ev.status.toUpperCase()}
                                    </span>
                                </div>
                                <div class="text-xs font-mono font-black text-black">
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
