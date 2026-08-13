@extends('mobile.layouts.app')

@section('title', 'Absen Saya - Presensi Guru Mobile')

@section('content')
<div class="space-y-4">
    <!-- Header Title & Role Dual Tabs -->
    <div>
        <h2 class="text-xl font-black text-slate-900">Modul Absensi Guru 📌</h2>
        <p class="text-[11px] text-slate-500 font-bold">Kelola Absen Siswa & Presensi Mandiri Saya</p>
    </div>

    <!-- Dual Tab Navigation Bar (Absen Siswa vs Absen Saya) -->
    <div class="flex items-center space-x-2 border-b-2 border-slate-200/80 pb-2">
        <a href="{{ route('mobile.guru.absensi.input') }}" 
           class="flex-1 py-2.5 px-3 rounded-2xl text-xs font-black text-center transition bg-white text-slate-600 border-2 border-slate-200 hover:text-slate-900">
            📋 Absen Siswa (Kelas)
        </a>
        <a href="{{ route('mobile.guru.absensi.saya') }}" 
           class="flex-1 py-2.5 px-3 rounded-2xl text-xs font-black text-center transition clay-purple text-white shadow-md scale-105">
            📌 Absen Saya (Guru)
        </a>
    </div>

    <!-- Today Presensi Card (Clay Purple) -->
    <div class="clay-purple p-6 space-y-4 relative overflow-hidden">
        <div class="flex items-center justify-between">
            <div>
                <span class="px-3 py-0.5 rounded-full text-[9px] font-black uppercase bg-white/30 text-white border border-white/40">
                    Presensi Guru Mandiri
                </span>
                <h3 class="text-base font-black text-white mt-1 leading-tight">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</h3>
            </div>
            <div class="text-right bg-white/20 backdrop-blur-md px-3 py-1.5 rounded-2xl border border-white/30 text-white font-black text-xs">
                <i class="fa-regular fa-clock mr-1"></i> <span id="liveClock">{{ date('H:i:s') }}</span>
            </div>
        </div>

        <!-- Attendance Action Button -->
        <div class="pt-2 border-t border-white/20">
            @if($todayAttendance)
                <div class="p-3.5 bg-white/20 backdrop-blur-md rounded-2xl border border-white/40 text-white text-xs flex items-center justify-between font-black">
                    <span><i class="fa-solid fa-circle-check text-emerald-300 text-base mr-2"></i>Sudah Presensi Hari Ini</span>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/80 text-[10px] uppercase">{{ $todayAttendance->check_in_time ?? date('H:i') }}</span>
                </div>
            @else
                <form action="{{ route('mobile.absensi.scan') }}" method="POST" id="gpsForm" class="space-y-2">
                    @csrf
                    <input type="hidden" name="latitude" id="latInput">
                    <input type="hidden" name="longitude" id="lngInput">
                    <button type="button" onclick="handleGpsScan()" class="w-full py-3.5 bg-white text-purple-900 font-black text-xs rounded-2xl shadow-lg hover:bg-purple-50 transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-location-dot text-rose-500 text-sm"></i>
                        <span>📍 Presensi GPS Guru Sekarang</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Monthly Summary Widgets -->
    <div class="grid grid-cols-4 gap-2 text-center">
        <div class="clay-green p-3">
            <span class="text-xl font-black block leading-none">{{ $stats['hadir'] }}</span>
            <span class="text-[9px] font-black uppercase mt-1 block text-emerald-100">Hadir</span>
        </div>
        <div class="clay-yellow p-3">
            <span class="text-xl font-black block leading-none">{{ $stats['terlambat'] }}</span>
            <span class="text-[9px] font-black uppercase mt-1 block text-amber-100">Terlambat</span>
        </div>
        <div class="clay-pink p-3">
            <span class="text-xl font-black block leading-none">{{ $stats['izin'] }}</span>
            <span class="text-[9px] font-black uppercase mt-1 block text-rose-100">Izin/Sakit</span>
        </div>
        <div class="clay-card p-3 bg-slate-100 text-slate-700">
            <span class="text-xl font-black block leading-none text-slate-800">{{ $stats['alpha'] }}</span>
            <span class="text-[9px] font-black uppercase mt-1 block text-slate-500">Alpha</span>
        </div>
    </div>

    <!-- Monthly Attendance History -->
    <div class="space-y-3 pt-2">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-wider px-1">Riwayat Presensi Guru (Bulan Ini)</h3>

        @forelse($attendances as $att)
            <div class="clay-card p-4 flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-black text-slate-900">{{ \Carbon\Carbon::parse($att->date)->translatedFormat('l, d M Y') }}</h4>
                    <p class="text-[10px] text-slate-500 font-bold mt-0.5">
                        <i class="fa-regular fa-clock text-purple-600 mr-1"></i>Masuk: {{ $att->check_in_time ?? '-' }} | Pulang: {{ $att->check_out_time ?? '-' }}
                    </p>
                </div>
                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase 
                    {{ str_contains(strtolower($att->status ?? ''), 'hadir') ? 'clay-green' : (str_contains(strtolower($att->status ?? ''), 'lambat') ? 'clay-yellow' : 'clay-pink') }}">
                    {{ $att->status ?? 'Hadir' }}
                </span>
            </div>
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                Belum ada data presensi guru bulan ini.
            </div>
        @endforelse
    </div>
</div>

<script>
    setInterval(() => {
        const now = new Date();
        document.getElementById('liveClock').innerText = now.toTimeString().split(' ')[0];
    }, 1000);

    function handleGpsScan() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    document.getElementById('latInput').value = pos.coords.latitude;
                    document.getElementById('lngInput').value = pos.coords.longitude;
                    document.getElementById('gpsForm').submit();
                },
                (err) => {
                    alert('Gagal mendapatkan lokasi GPS: ' + err.message + '. Mengirim presensi dengan lokasi default...');
                    document.getElementById('gpsForm').submit();
                }
            );
        } else {
            alert('Browser tidak mendukung Geolocation.');
            document.getElementById('gpsForm').submit();
        }
    }
</script>
@endsection
