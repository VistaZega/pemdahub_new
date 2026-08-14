@extends('mobile.layouts.app')

@section('title', 'Absen Saya - Presensi & Poin Guru Mobile')

@section('content')
<div class="space-y-4">
    <!-- Header Title & Role Dual Tabs -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-900">Absensi Saya 📌</h2>
            <p class="text-[11px] text-slate-500 font-bold">Rekap Kehadiran Mengajar & Poin Reputasi Guru</p>
        </div>

        <!-- Filter Month/Year -->
        <form method="GET" action="{{ route('mobile.guru.absensi.saya') }}" class="flex items-center gap-1.5">
            <select name="month" onchange="this.form.submit()" class="text-[11px] font-black border-2 border-slate-200 rounded-xl px-2.5 py-1.5 bg-white text-slate-800 shadow-sm outline-none cursor-pointer">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>
            <select name="year" onchange="this.form.submit()" class="text-[11px] font-black border-2 border-slate-200 rounded-xl px-2 py-1.5 bg-white text-slate-800 shadow-sm outline-none cursor-pointer">
                @for($y = now()->year; $y >= now()->year - 2; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>
        </form>
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

    <!-- Special 17 August Independence Day Card for Guru (HUT RI Merah-Putih) -->
    @if(date('m-d') === '08-17')
        <div class="p-4 rounded-3xl bg-gradient-to-r from-red-600 via-rose-600 to-red-100 border-2 border-red-500 shadow-lg text-white space-y-2 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="px-3 py-0.5 rounded-full text-[10px] font-black uppercase bg-white text-red-700 shadow border border-red-200">
                    🇮🇩 HARI KEMERDEKAAN RI
                </span>
                <span class="text-xs font-black text-slate-800 bg-white/90 px-2 py-0.5 rounded-lg border border-slate-200">17 AGUSTUS</span>
            </div>
            <h3 class="text-base font-black text-white leading-tight drop-shadow-md">DIRGAHAYU REPUBLIK INDONESIA — MERDEKA! ✊</h3>
            <p class="text-[11px] text-red-50 font-bold leading-relaxed drop-shadow-sm">
                Hormat setinggi-tingginya kepada para Pahlawan Pendidikan! Terima kasih atas dedikasi dan pengabdian Bapak/Ibu Guru dalam mendidik generasi penerus bangsa Indonesia. 🇮🇩✨
            </p>
        </div>
    @endif

    <!-- Today Presensi GPS Trigger Card (Clay Purple) -->
    <div class="clay-purple p-5 space-y-3.5 relative overflow-hidden">
        <div class="flex items-center justify-between">
            <div>
                <span class="px-3 py-0.5 rounded-full text-[9px] font-black uppercase bg-white/30 text-white border border-white/40">
                    Presensi GPS Guru Mandiri
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
                <div class="p-3 bg-white/20 backdrop-blur-md rounded-2xl border border-white/40 text-white text-xs flex items-center justify-between font-black">
                    <span><i class="fa-solid fa-circle-check text-emerald-300 text-base mr-2"></i>Sudah Presensi Hari Ini</span>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/80 text-[10px] uppercase">{{ substr($todayAttendance->time_in ?? $todayAttendance->check_in_time ?? date('H:i'), 0, 5) }}</span>
                </div>
            @else
                <form action="{{ route('mobile.absensi.scan') }}" method="POST" id="gpsForm" class="space-y-2">
                    @csrf
                    <input type="hidden" name="latitude" id="latInput">
                    <input type="hidden" name="longitude" id="lngInput">
                    <button type="button" onclick="handleGpsScan()" class="w-full py-3 bg-white text-purple-900 font-black text-xs rounded-2xl shadow-lg hover:bg-purple-50 transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-location-dot text-rose-500 text-sm"></i>
                        <span>📍 Presensi GPS Guru Sekarang</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Stats & Point Summary Grid (5 Cards) -->
    <div class="grid grid-cols-2 gap-2 text-center">
        <!-- Rate Kehadiran Wajib -->
        <div class="clay-card p-3.5 bg-gradient-to-br from-teal-500 to-emerald-600 text-white col-span-2 flex items-center justify-between">
            <div class="text-left">
                <span class="text-[10px] font-black uppercase text-teal-100 block">Tingkat Kehadiran Wajib</span>
                <h4 class="text-2xl font-black leading-none mt-1">{{ $pct }}%</h4>
                <span class="text-[9px] text-teal-100 font-bold block mt-1">{{ $totals['present_on_scheduled'] }} / {{ $totals['total_scheduled'] }} Hari Terjadwal</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 flex items-center justify-center text-2xl">
                📊
            </div>
        </div>

        <!-- Hadir Mengajar (HM) -->
        <div class="clay-green p-3 text-left">
            <div class="flex items-center justify-between">
                <span class="text-[9px] font-black uppercase text-emerald-100">Hadir Mengajar</span>
                <span class="text-xs">👨‍🏫</span>
            </div>
            <h4 class="text-xl font-black text-white mt-1 leading-none">{{ $totals['hadir_mengajar'] }} Hari</h4>
            <span class="text-[8px] font-bold text-emerald-100 mt-1 block">Sesuai Jadwal</span>
        </div>

        <!-- Tugas Khusus (TK) -->
        <div class="clay-purple p-3 text-left">
            <div class="flex items-center justify-between">
                <span class="text-[9px] font-black uppercase text-purple-100">Tugas Khusus</span>
                <span class="text-xs">⭐</span>
            </div>
            <h4 class="text-xl font-black text-white mt-1 leading-none">{{ $totals['tugas_khusus'] }} Hari</h4>
            <span class="text-[8px] font-bold text-purple-100 mt-1 block">Hadir Luar Jadwal</span>
        </div>

        <!-- Poin Reputasi Didapat (+15 Pts/TK) -->
        <div class="clay-yellow p-3 text-left">
            <div class="flex items-center justify-between">
                <span class="text-[9px] font-black uppercase text-amber-100">Poin Reputasi</span>
                <span class="text-xs">🪙</span>
            </div>
            <h4 class="text-xl font-black text-white mt-1 leading-none">+{{ $reputationPoints }} Pts</h4>
            <span class="text-[8px] font-bold text-amber-100 mt-1 block">+15 Pts tiap Tugas Khusus</span>
        </div>

        <!-- Alpha / Absen Wajib -->
        <div class="clay-pink p-3 text-left">
            <div class="flex items-center justify-between">
                <span class="text-[9px] font-black uppercase text-rose-100">Absen Wajib (Alpha)</span>
                <span class="text-xs">⚠️</span>
            </div>
            <h4 class="text-xl font-black text-white mt-1 leading-none">{{ $totals['alpha'] }} Hari</h4>
            <span class="text-[8px] font-bold text-rose-100 mt-1 block">Tidak Hadir Mengajar</span>
        </div>
    </div>

    <!-- Monthly Attendance Matrix Calendar -->
    <div class="clay-card p-4 space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                <i class="fa-solid fa-calendar-days text-purple-600"></i> Kalender Presensi Harian
            </h3>
            <span class="text-[9px] font-black text-slate-500 uppercase">{{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}</span>
        </div>

        <!-- Legend Badges -->
        <div class="flex flex-wrap gap-1.5 text-[9px] font-black">
            <span class="px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-200">HM = Hadir Mengajar</span>
            <span class="px-2 py-0.5 rounded-lg bg-indigo-100 text-indigo-800 border border-indigo-200">TK = Tugas Khusus (+15)</span>
            <span class="px-2 py-0.5 rounded-lg bg-rose-100 text-rose-800 border border-rose-200">A = Alpha</span>
            <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-600 border border-slate-200">- = Bebas Tugas</span>
        </div>

        <!-- Daily Grid (2 Columns on mobile) -->
        <div class="grid grid-cols-2 gap-2">
            @for($d = 1; $d <= $daysInMonth; $d++)
                @php
                    $dayData = $calendarData[$d];
                    $date = $dayData['date'];
                    $att = $dayData['attendance'];
                @endphp
                <div class="p-3 rounded-2xl border-2 flex flex-col justify-between min-h-[85px] {{ $dayData['color_class'] }} transition relative">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-base font-black leading-none block">{{ $d }}</span>
                            <span class="text-[9px] font-extrabold uppercase tracking-tight opacity-75 mt-0.5 block">{{ $date->translatedFormat('D') }}</span>
                        </div>
                        <span class="text-[9px] font-black px-1.5 py-0.5 rounded-md bg-white/70 shadow-xs border border-slate-200">
                            {{ $dayData['status'] }}
                        </span>
                    </div>

                    <div class="mt-2 pt-1 border-t border-current/10 text-[9px] font-bold">
                        @if($att)
                            <div class="flex justify-between items-center">
                                <span class="opacity-75">Masuk:</span>
                                <span class="font-black">{{ substr($att->time_in ?? $att->check_in_time ?? '-', 0, 5) }}</span>
                            </div>
                            @if($att->time_out && $att->time_out !== '00:00:00')
                                <div class="flex justify-between items-center">
                                    <span class="opacity-75">Pulang:</span>
                                    <span class="font-black">{{ substr($att->time_out, 0, 5) }}</span>
                                </div>
                            @endif
                        @else
                            <span class="italic opacity-75 text-[8px]">{{ $dayData['status_label'] }}</span>
                        @endif
                    </div>

                    @if($dayData['status'] === 'TK')
                        <span class="absolute -right-1 -top-1 px-1.5 py-0.5 rounded-full bg-indigo-600 text-white text-[8px] font-black shadow-md">
                            +15 Pts
                        </span>
                    @endif
                </div>
            @endfor
        </div>
    </div>
</div>

<script>
    setInterval(() => {
        const now = new Date();
        document.getElementById('liveClock').innerText = now.toTimeString().split(' ')[0];
    }, 1000);

    function handleGpsScan() {
        const form = document.getElementById('gpsForm');
        const latInput = document.getElementById('latInput');
        const lngInput = document.getElementById('lngInput');

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    latInput.value = pos.coords.latitude;
                    lngInput.value = pos.coords.longitude;
                    form.submit();
                },
                (err) => {
                    latInput.value = 0;
                    lngInput.value = 0;
                    form.submit();
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        } else {
            latInput.value = 0;
            lngInput.value = 0;
            form.submit();
        }
    }
</script>
@endsection
