@extends('layouts.admin')

@section('title', 'Live Presensi Real-Time - Pusat Absensi')

@section('content')
<div class="space-y-6">
    <!-- Unified Header -->
    @include('admin.attendance.header')

    <!-- Live Status Banner & Counter Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- 1. Total Target -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between text-slate-400 mb-1">
                <span class="text-[11px] font-black uppercase tracking-wider">Total Terdaftar</span>
                <i class="fa-solid fa-users text-xs"></i>
            </div>
            <div class="text-2xl font-black text-slate-900" id="stat_total_target">
                {{ number_format($stats['total_target']) }}
            </div>
            <span class="text-[10px] font-bold text-slate-400 capitalize">{{ $group }} Aktif</span>
        </div>

        <!-- 2. Hadir -->
        <div class="bg-emerald-50/80 rounded-2xl p-4 border border-emerald-200 shadow-2xs">
            <div class="flex items-center justify-between text-emerald-600 mb-1">
                <span class="text-[11px] font-black uppercase tracking-wider">Hadir Tepat Waktu</span>
                <i class="fa-solid fa-circle-check text-xs"></i>
            </div>
            <div class="text-2xl font-black text-emerald-700" id="stat_hadir">
                {{ number_format($stats['hadir']) }}
            </div>
            <span class="text-[10px] font-bold text-emerald-600">Masuk Sebelum Batas</span>
        </div>

        <!-- 3. Terlambat -->
        <div class="bg-amber-50/80 rounded-2xl p-4 border border-amber-200 shadow-2xs">
            <div class="flex items-center justify-between text-amber-600 mb-1">
                <span class="text-[11px] font-black uppercase tracking-wider">Terlambat</span>
                <i class="fa-solid fa-clock text-xs"></i>
            </div>
            <div class="text-2xl font-black text-amber-700" id="stat_terlambat">
                {{ number_format($stats['terlambat']) }}
            </div>
            <span class="text-[10px] font-bold text-amber-600">Masuk Lewat Toleransi</span>
        </div>

        <!-- 4. Izin & Sakit -->
        <div class="bg-blue-50/80 rounded-2xl p-4 border border-blue-200 shadow-2xs">
            <div class="flex items-center justify-between text-blue-600 mb-1">
                <span class="text-[11px] font-black uppercase tracking-wider">Izin / Sakit</span>
                <i class="fa-solid fa-envelope-open-text text-xs"></i>
            </div>
            <div class="text-2xl font-black text-blue-700" id="stat_izin_sakit">
                {{ number_format($stats['izin'] + $stats['sakit'] + $stats['dinas_luar'] + $stats['cuti']) }}
            </div>
            <span class="text-[10px] font-bold text-blue-600">Surat Keterangan Resmi</span>
        </div>

        <!-- 5. Alpha -->
        <div class="bg-rose-50/80 rounded-2xl p-4 border border-rose-200 shadow-2xs">
            <div class="flex items-center justify-between text-rose-600 mb-1">
                <span class="text-[11px] font-black uppercase tracking-wider">Alpha</span>
                <i class="fa-solid fa-circle-xmark text-xs"></i>
            </div>
            <div class="text-2xl font-black text-rose-700" id="stat_alpha">
                {{ number_format($stats['alpha']) }}
            </div>
            <span class="text-[10px] font-bold text-rose-600">Tanpa Keterangan</span>
        </div>

        <!-- 6. Belum Hadir -->
        <div class="bg-slate-100/90 rounded-2xl p-4 border border-slate-300 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 mb-1">
                <span class="text-[11px] font-black uppercase tracking-wider">Belum Presensi</span>
                <i class="fa-solid fa-hourglass-half text-xs"></i>
            </div>
            <div class="text-2xl font-black text-slate-800" id="stat_belum">
                {{ number_format($stats['belum']) }}
            </div>
            <span class="text-[10px] font-bold text-slate-500">Menunggu Tap / GPS</span>
        </div>
    </div>

    <!-- Live Stream Feed Container -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5 flex-wrap gap-3">
            <div class="flex items-center gap-2.5">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                </span>
                <div>
                    <h3 class="text-base font-black text-slate-900 leading-tight">Live Activity Stream (Real-Time Feed)</h3>
                    <p class="text-xs text-slate-400 font-semibold">Memperbarui secara otomatis setiap 4 detik saat ada presensi GPS / RFID masuk</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-xl text-xs font-mono font-bold" id="live_clock">
                    {{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i:s') }} WIB
                </span>
                <button type="button" onclick="pollLiveData()" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs transition active:scale-95" title="Refresh Sekarang">
                    <i class="fa-solid fa-rotate text-xs"></i>
                </button>
            </div>
        </div>

        <!-- Live Events Grid -->
        <div id="live_events_container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
            @forelse($liveEvents as $ev)
                <div class="p-3.5 rounded-2xl border border-slate-200/90 hover:border-slate-300 bg-slate-50/50 hover:bg-white transition-all shadow-2xs flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center text-white font-bold text-xs shrink-0 shadow-xs">
                            {{ strtoupper(substr($ev['name'], 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-slate-900 text-sm truncate">{{ $ev['name'] }}</div>
                            <div class="text-[11px] text-slate-400 truncate">{{ $ev['subtitle'] }} · {{ $ev['code'] }}</div>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <div class="flex items-center justify-end gap-1 mb-1">
                            @if($ev['recorded_via'] === 'gps')
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-blue-100 text-blue-700 border border-blue-200">
                                    <i class="fa-solid fa-location-dot"></i> GPS
                                </span>
                            @elseif($ev['recorded_via'] === 'rfid')
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-amber-100 text-amber-800 border border-amber-200">
                                    <i class="fa-solid fa-id-card"></i> RFID
                                </span>
                            @else
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-slate-100 text-slate-600 border border-slate-200">
                                    <i class="fa-solid fa-pen"></i> Manual
                                </span>
                            @endif

                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black
                                  {{ $ev['status'] === 'hadir' ? 'bg-emerald-100 text-emerald-800' : ($ev['status'] === 'terlambat' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                {{ ucfirst($ev['status']) }}
                            </span>
                        </div>
                        <div class="text-xs font-mono font-bold text-slate-700">
                            {{ $ev['time_in'] ?? '-' }} WIB
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center text-slate-400">
                    <i class="fa-solid fa-satellite-dish text-4xl mb-3 text-slate-300"></i>
                    <p class="font-bold text-sm">Belum ada aktivitas presensi masuk pada tanggal ini.</p>
                    <p class="text-xs text-slate-400 mt-0.5">Aktivitas kehadiran GPS atau RFID akan muncul di sini seketika.</p>
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
                        viaBadge = '<span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-blue-100 text-blue-700 border border-blue-200"><i class="fa-solid fa-location-dot"></i> GPS</span>';
                    } else if (ev.recorded_via === 'rfid') {
                        viaBadge = '<span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-amber-100 text-amber-800 border border-amber-200"><i class="fa-solid fa-id-card"></i> RFID</span>';
                    } else {
                        viaBadge = '<span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-slate-100 text-slate-600 border border-slate-200"><i class="fa-solid fa-pen"></i> Manual</span>';
                    }

                    let statusClass = 'bg-rose-100 text-rose-800';
                    if (ev.status === 'hadir') statusClass = 'bg-emerald-100 text-emerald-800';
                    else if (ev.status === 'terlambat') statusClass = 'bg-amber-100 text-amber-800';

                    html += `
                        <div class="p-3.5 rounded-2xl border border-slate-200/90 hover:border-slate-300 bg-slate-50/50 hover:bg-white transition-all shadow-2xs flex items-center justify-between gap-3 animate-fade-in">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center text-white font-bold text-xs shrink-0 shadow-xs">
                                    ${initials}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-900 text-sm truncate">${ev.name}</div>
                                    <div class="text-[11px] text-slate-400 truncate">${ev.subtitle} · ${ev.code}</div>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <div class="flex items-center justify-end gap-1 mb-1">
                                    ${viaBadge}
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black ${statusClass}">
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

// Start auto polling every 4.5 seconds
document.addEventListener('DOMContentLoaded', () => {
    livePollingInterval = setInterval(pollLiveData, 4500);
});

window.addEventListener('beforeunload', () => {
    if (livePollingInterval) clearInterval(livePollingInterval);
});
</script>
@endsection
