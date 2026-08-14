@extends('mobile.layouts.app')

@section('title', 'Absensi Mobile Pro - PembdaHUB')

@section('content')
<div class="space-y-4">
    <!-- Header Title -->
    <div>
        <h2 class="text-lg font-black text-slate-900">Absensi & Presensi</h2>
        <p class="text-[11px] text-slate-500 font-medium">Presensi Mandiri GPS & Catatan Kehadiran</p>
    </div>

    <!-- Special 17 August Independence Day Card (HUT RI Merah-Putih) -->
    @if(date('m-d') === '08-17')
        <div class="p-4 rounded-3xl bg-gradient-to-r from-red-600 via-rose-600 to-white border-2 border-red-500 shadow-lg text-white space-y-2 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="px-3 py-0.5 rounded-full text-[10px] font-black uppercase bg-white text-red-700 shadow border border-red-200">
                    🇮🇩 DIRGAHAYU REPUBLIK INDONESIA
                </span>
                <span class="text-xs font-black text-slate-800 bg-white/90 px-2 py-0.5 rounded-lg border border-slate-200">17 AGUSTUS</span>
            </div>
            <h3 class="text-base font-black text-white leading-tight drop-shadow-md">HUT RI Ke-81 — MERDEKA! ✊</h3>
            <p class="text-[11px] text-red-50 font-bold leading-relaxed drop-shadow-sm">
                Selamat Hari Kemerdekaan Republik Indonesia! Mari isi kemerdekaan dengan semangat belajar, berkarya, dan menjadi kebanggaan Bangsa! 🇮🇩✨
            </p>
        </div>
    @endif

    <!-- GPS Presensi Card (UI/UX Pro Max Emerald Gradient Card) -->
    <div class="bg-gradient-to-br from-emerald-600 via-teal-600 to-emerald-700 rounded-3xl p-5 text-white shadow-lg shadow-emerald-500/25 text-center relative overflow-hidden"
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
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white/20 text-white text-2xl mb-3 shadow-md border border-white/30 backdrop-blur-md">
            <i class="fa-solid fa-location-dot"></i>
        </div>

        <h3 class="text-base font-black text-white mb-1">Presensi Mandiri GPS</h3>
        <p class="text-xs text-emerald-100 mb-4 max-w-xs mx-auto font-medium">Pastikan Anda berada di area lokasi sekolah sebelum menekan tombol di bawah.</p>

        @if($todayAttendance)
            <div class="p-3.5 rounded-2xl bg-white/20 border border-white/30 text-white text-xs font-black inline-flex items-center gap-2 mb-1 backdrop-blur-md shadow">
                <i class="fa-solid fa-circle-check text-emerald-300 text-sm"></i>
                <span>Sudah Absen Masuk: {{ $todayAttendance->time_in }} ({{ strtoupper($todayAttendance->status) }})</span>
            </div>
        @else
            <button @click="doGpsScan()" :disabled="loading"
                    class="w-full py-3.5 px-6 bg-white text-emerald-800 font-black text-xs rounded-2xl shadow-xl hover:bg-emerald-50 transition transform active:scale-95 flex items-center justify-center gap-2">
                <template x-if="loading">
                    <i class="fa-solid fa-spinner animate-spin text-sm"></i>
                </template>
                <template x-if="!loading">
                    <i class="fa-solid fa-fingerprint text-base text-emerald-600"></i>
                </template>
                <span x-text="loading ? statusMsg : 'KIRIM PRESENSI GPS SEKARANG'"></span>
            </button>
        @endif
    </div>

    <!-- Attendance History List -->
    <div class="space-y-3">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-wider px-1">Riwayat Kehadiran (30 Hari Terakhir)</h3>

        @forelse($attendances as $att)
            <div class="pro-card rounded-2xl p-3.5 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-black
                        {{ strtolower($att->status) === 'hadir' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : '' }}
                        {{ strtolower($att->status) === 'terlambat' ? 'bg-amber-100 text-amber-700 border border-amber-200' : '' }}
                        {{ in_array(strtolower($att->status), ['sakit', 'izin', 'alpha']) ? 'bg-rose-100 text-rose-700 border border-rose-200' : '' }}">
                        <i class="fa-solid 
                            {{ strtolower($att->status) === 'hadir' ? 'fa-check' : '' }}
                            {{ strtolower($att->status) === 'terlambat' ? 'fa-clock' : '' }}
                            {{ in_array(strtolower($att->status), ['sakit', 'izin', 'alpha']) ? 'fa-user-xmark' : '' }}"></i>
                    </div>

                    <div>
                        <h4 class="text-xs font-black text-slate-900">{{ \Carbon\Carbon::parse($att->date)->translatedFormat('l, d M Y') }}</h4>
                        <span class="text-[10px] text-slate-500 font-medium">Masuk: {{ $att->time_in ?? '-' }} | Keluar: {{ $att->time_out ?? '-' }}</span>
                    </div>
                </div>

                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase
                    {{ strtolower($att->status) === 'hadir' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : '' }}
                    {{ strtolower($att->status) === 'terlambat' ? 'bg-amber-100 text-amber-800 border border-amber-200' : '' }}
                    {{ in_array(strtolower($att->status), ['sakit', 'izin', 'alpha']) ? 'bg-rose-100 text-rose-800 border border-rose-200' : '' }}">
                    {{ $att->status }}
                </span>
            </div>
        @empty
            <div class="pro-card rounded-2xl p-6 text-center text-slate-500 text-xs font-medium">
                Belum ada catatan riwayat kehadiran.
            </div>
        @endforelse
    </div>
</div>
@endsection
