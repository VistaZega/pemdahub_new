@extends('mobile.layouts.app')

@section('title', 'Absensi Mobile - PembdaHUB')

@section('content')
<div class="space-y-4">
    <!-- Header Title -->
    <div>
        <h2 class="text-lg font-extrabold text-white">Absensi & Presensi</h2>
        <p class="text-[11px] text-slate-400">Presensi Mandiri GPS & Catatan Kehadiran</p>
    </div>

    <!-- GPS Presensi Card -->
    <div class="glass-card rounded-3xl p-5 relative overflow-hidden bg-gradient-to-br from-emerald-950/80 via-slate-900 to-slate-950 border border-emerald-500/30 text-center"
         x-data="{ 
            loading: false, 
            statusMsg: '',
            doGpsScan() {
                if (!navigator.geolocation) {
                    alert('Browser Anda tidak mendukung GPS Geolocation.');
                    return;
                }
                this.loading = true;
                this.statusMsg = 'Mengambil koordinat GPS...';
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        this.statusMsg = 'Mengirim data presensi...';
                        fetch('{{ route('mobile.absensi.scan') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                latitude: pos.coords.latitude,
                                longitude: pos.coords.longitude,
                                device_id: navigator.userAgent
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            this.loading = false;
                            alert(data.message || 'Presensi GPS berhasil dikirim!');
                            window.location.reload();
                        })
                        .catch(err => {
                            this.loading = false;
                            alert('Gagal presensi: ' + err.message);
                        });
                    },
                    (err) => {
                        this.loading = false;
                        alert('Gagal mendapatkan lokasi GPS: ' + err.message + '. Harap aktifkan GPS HP Anda.');
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            }
         }">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 text-2xl mb-3 shadow-lg shadow-emerald-500/20">
            <i class="fa-solid fa-location-dot"></i>
        </div>

        <h3 class="text-base font-extrabold text-white mb-1">Presensi Mandiri GPS</h3>
        <p class="text-xs text-slate-400 mb-4 max-w-xs mx-auto">Pastikan Anda berada di area lokasi sekolah sebelum menekan tombol di bawah.</p>

        @if($todayAttendance)
            <div class="p-3 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 text-xs font-bold inline-flex items-center gap-2 mb-2">
                <i class="fa-solid fa-circle-check text-emerald-400"></i>
                <span>Sudah Absen Masuk: {{ $todayAttendance->time_in }} ({{ strtoupper($todayAttendance->status) }})</span>
            </div>
        @else
            <button @click="doGpsScan()" :disabled="loading"
                    class="w-full py-3.5 px-6 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-emerald-600/30 hover:from-emerald-500 hover:to-emerald-400 transition transform active:scale-95 flex items-center justify-center gap-2">
                <template x-if="loading">
                    <i class="fa-solid fa-spinner animate-spin"></i>
                </template>
                <template x-if="!loading">
                    <i class="fa-solid fa-fingerprint text-base"></i>
                </template>
                <span x-text="loading ? statusMsg : 'KIRIM PRESENSI GPS SEKARANG'"></span>
            </button>
        @endif
    </div>

    <!-- Attendance History List -->
    <div class="space-y-3">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider px-1">Riwayat Kehadiran (30 Hari Terakhir)</h3>

        @forelse($attendances as $att)
            <div class="glass-card rounded-2xl p-3.5 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold
                        {{ strtolower($att->status) === 'hadir' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : '' }}
                        {{ strtolower($att->status) === 'terlambat' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : '' }}
                        {{ in_array(strtolower($att->status), ['sakit', 'izin', 'alpha']) ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : '' }}">
                        <i class="fa-solid 
                            {{ strtolower($att->status) === 'hadir' ? 'fa-check' : '' }}
                            {{ strtolower($att->status) === 'terlambat' ? 'fa-clock' : '' }}
                            {{ in_array(strtolower($att->status), ['sakit', 'izin', 'alpha']) ? 'fa-user-xmark' : '' }}"></i>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold text-white">{{ \Carbon\Carbon::parse($att->date)->translatedFormat('l, d M Y') }}</h4>
                        <span class="text-[10px] text-slate-400">Masuk: {{ $att->time_in ?? '-' }} | Keluar: {{ $att->time_out ?? '-' }}</span>
                    </div>
                </div>

                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase
                    {{ strtolower($att->status) === 'hadir' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : '' }}
                    {{ strtolower($att->status) === 'terlambat' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : '' }}
                    {{ in_array(strtolower($att->status), ['sakit', 'izin', 'alpha']) ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : '' }}">
                    {{ $att->status }}
                </span>
            </div>
        @empty
            <div class="glass-card rounded-2xl p-6 text-center text-slate-500 text-xs">
                Belum ada catatan riwayat kehadiran.
            </div>
        @endforelse
    </div>
</div>
@endsection
