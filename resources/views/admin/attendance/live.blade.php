@extends('layouts.admin')

@section('title', 'Live Presensi Real-Time - Pusat Absensi')

@section('content')
<div class="space-y-8">
    {{-- Unified Header --}}
    @include('admin.attendance.header')

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- STATISTIC METRIC CARDS (POP NEO-BRUTALISM GRID) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-5">
        {{-- 1. Total Target --}}
        <div class="bg-white rounded-3xl border-2 border-black p-5 shadow-md hover:shadow-xl transition-all flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center border-2 border-black shadow-sm text-xl font-black" style="background-color: #4f46e5 !important; color: #ffffff !important;">
                    <i class="fas fa-users text-white"></i>
                </div>
                <span class="text-3xl font-black text-black leading-none font-mono" id="stat_total_target">{{ number_format($stats['total_target']) }}</span>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-black">Total Terdaftar</p>
                <p class="text-[11px] font-bold text-gray-500 mt-0.5">{{ ucfirst($group) }} aktif</p>
            </div>
        </div>

        {{-- 2. Hadir --}}
        <div class="bg-white rounded-3xl border-2 border-black p-5 shadow-md hover:shadow-xl transition-all flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center border-2 border-black shadow-sm text-xl font-black" style="background-color: #059669 !important; color: #ffffff !important;">
                    <i class="fas fa-circle-check text-white"></i>
                </div>
                <span class="text-3xl font-black text-black leading-none font-mono" id="stat_hadir">{{ number_format($stats['hadir']) }}</span>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-black">Hadir Tepat</p>
                <p class="text-[11px] font-bold text-gray-500 mt-0.5">Sebelum batas waktu</p>
            </div>
        </div>

        {{-- 3. Terlambat --}}
        <div class="bg-white rounded-3xl border-2 border-black p-5 shadow-md hover:shadow-xl transition-all flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center border-2 border-black shadow-sm text-xl font-black" style="background-color: #d97706 !important; color: #ffffff !important;">
                    <i class="fas fa-clock text-white"></i>
                </div>
                <span class="text-3xl font-black text-black leading-none font-mono" id="stat_terlambat">{{ number_format($stats['terlambat']) }}</span>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-black">Terlambat</p>
                <p class="text-[11px] font-bold text-gray-500 mt-0.5">Lewat batas toleransi</p>
            </div>
        </div>

        {{-- 4. Izin & Sakit --}}
        <div class="bg-white rounded-3xl border-2 border-black p-5 shadow-md hover:shadow-xl transition-all flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center border-2 border-black shadow-sm text-xl font-black" style="background-color: #0284c7 !important; color: #ffffff !important;">
                    <i class="fas fa-envelope-open-text text-white"></i>
                </div>
                <span class="text-3xl font-black text-black leading-none font-mono" id="stat_izin_sakit">{{ number_format($stats['izin'] + $stats['sakit'] + $stats['dinas_luar'] + $stats['cuti']) }}</span>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-black">Izin & Sakit</p>
                <p class="text-[11px] font-bold text-gray-500 mt-0.5">Surat resmi terlampir</p>
            </div>
        </div>

        {{-- 5. Alpha --}}
        <div class="bg-white rounded-3xl border-2 border-black p-5 shadow-md hover:shadow-xl transition-all flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center border-2 border-black shadow-sm text-xl font-black" style="background-color: #e11d48 !important; color: #ffffff !important;">
                    <i class="fas fa-circle-xmark text-white"></i>
                </div>
                <span class="text-3xl font-black text-black leading-none font-mono" id="stat_alpha">{{ number_format($stats['alpha']) }}</span>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-black">Alpha</p>
                <p class="text-[11px] font-bold text-gray-500 mt-0.5">Tanpa keterangan</p>
            </div>
        </div>

        {{-- 6. Belum Hadir --}}
        <div class="bg-white rounded-3xl border-2 border-black p-5 shadow-md hover:shadow-xl transition-all flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center border-2 border-black shadow-sm text-xl font-black" style="background-color: #334155 !important; color: #ffffff !important;">
                    <i class="fas fa-hourglass-half text-white"></i>
                </div>
                <span class="text-3xl font-black text-black leading-none font-mono" id="stat_belum">{{ number_format($stats['belum']) }}</span>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-black">Belum Presensi</p>
                <p class="text-[11px] font-bold text-gray-500 mt-0.5">Menunggu tap/GPS</p>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- LIVE FEED STREAM CONTAINER (LEGA & RAPI)       --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="bg-white rounded-3xl border-2 border-black p-6 md:p-8 shadow-xl">
        <div class="flex items-center justify-between pb-6 border-b-2 border-black mb-6 flex-wrap gap-4">
            <div class="flex items-center gap-3.5">
                <span class="relative flex h-4 w-4">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-4 w-4 bg-rose-600"></span>
                </span>
                <div>
                    <h3 class="text-xl font-black text-black leading-tight tracking-tight">Live Activity Feed (Real-Time Stream)</h3>
                    <p class="text-xs text-gray-500 font-bold mt-0.5">Data diperbarui otomatis setiap 4 detik saat ada presensi masuk</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="px-5 py-3 bg-black text-amber-400 rounded-2xl text-xs font-mono font-black border-2 border-black shadow-md" id="live_clock">
                    {{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i:s') }} WIB
                </span>
                <button type="button" onclick="pollLiveData()" class="px-6 py-3 bg-amber-400 hover:bg-amber-300 text-black rounded-2xl text-xs font-black uppercase tracking-wider border-2 border-black transition active:scale-95 shadow-md flex items-center gap-2" title="Refresh Sekarang">
                    <i class="fas fa-rotate text-black"></i>
                    <span>Refresh</span>
                </button>
            </div>
        </div>

        {{-- Live Events Grid --}}
        <div id="live_events_container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($liveEvents as $ev)
                <div class="p-5 rounded-3xl border-2 border-black hover:bg-amber-50 transition-all shadow-md flex items-center justify-between gap-4 bg-white">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-12 h-12 rounded-2xl bg-amber-400 border-2 border-black flex items-center justify-center font-black text-xs shrink-0 shadow-sm overflow-hidden">
                            @if(!empty($ev['photo_url']))
                                <img src="{{ $ev['photo_url'] }}" class="w-full h-full object-cover" alt="{{ $ev['name'] }}">
                            @else
                                <span class="text-black font-black text-xs">{{ strtoupper(substr($ev['name'], 0, 2)) }}</span>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="font-black text-black text-sm truncate">{{ $ev['name'] }}</div>
                            <div class="text-xs text-gray-500 font-bold truncate mt-0.5">{{ $ev['subtitle'] }} · {{ $ev['code'] }}</div>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <div class="flex items-center justify-end gap-2 mb-2">
                            @if($ev['recorded_via'] === 'gps')
                                <span class="px-3 py-1 rounded-xl text-[10px] font-black bg-blue-100 text-blue-900 border border-blue-900">
                                    <i class="fas fa-location-dot mr-1"></i> GPS
                                </span>
                            @elseif($ev['recorded_via'] === 'rfid')
                                <span class="px-3 py-1 rounded-xl text-[10px] font-black bg-amber-200 text-black border border-black">
                                    <i class="fas fa-id-card mr-1"></i> RFID
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-xl text-[10px] font-black bg-gray-100 text-black border border-black">
                                    <i class="fas fa-pen mr-1"></i> Manual
                                </span>
                            @endif

                            <span class="px-3 py-1 rounded-xl text-[10px] font-black border border-black
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
                        viaBadge = '<span class="px-3 py-1 rounded-xl text-[10px] font-black bg-blue-100 text-blue-900 border border-blue-900"><i class="fas fa-location-dot mr-1"></i> GPS</span>';
                    } else if (ev.recorded_via === 'rfid') {
                        viaBadge = '<span class="px-3 py-1 rounded-xl text-[10px] font-black bg-amber-200 text-black border border-black"><i class="fas fa-id-card mr-1"></i> RFID</span>';
                    } else {
                        viaBadge = '<span class="px-3 py-1 rounded-xl text-[10px] font-black bg-gray-100 text-black border border-black"><i class="fas fa-pen mr-1"></i> Manual</span>';
                    }

                    let statusClass = 'bg-rose-400 text-black';
                    if (ev.status === 'hadir') statusClass = 'bg-emerald-300 text-black';
                    else if (ev.status === 'terlambat') statusClass = 'bg-amber-300 text-black';

                    html += `
                        <div class="p-5 rounded-3xl border-2 border-black hover:bg-amber-50 transition-all shadow-md flex items-center justify-between gap-4 bg-white">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-12 h-12 rounded-2xl bg-amber-400 border-2 border-black flex items-center justify-center font-black text-xs shrink-0 shadow-sm overflow-hidden">
                                    ${photoHtml}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-black text-black text-sm truncate">${ev.name}</div>
                                    <div class="text-xs text-gray-500 font-bold truncate mt-0.5">${ev.subtitle} · ${ev.code}</div>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <div class="flex items-center justify-end gap-2 mb-2">
                                    ${viaBadge}
                                    <span class="px-3 py-1 rounded-xl text-[10px] font-black border border-black ${statusClass}">
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
