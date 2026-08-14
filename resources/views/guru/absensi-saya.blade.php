@extends('layouts.guru')
@section('title', 'Absensi Saya - Portal Guru')

@section('content')
<div class="space-y-6">
    {{-- Session Flash Alert Notifications (Tanda Konfirmasi Berhasil / Gagal) --}}
    @if(session('success'))
        <div class="bg-emerald-50 border-2 border-emerald-500 text-emerald-950 rounded-2xl p-4.5 shadow-md flex items-center justify-between gap-4 animate-bounce-once">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-emerald-500 text-white shrink-0 flex items-center justify-center text-xl shadow-sm">
                    <i class="fas fa-circle-check"></i>
                </div>
                <div>
                    <h4 class="text-xs font-black uppercase text-emerald-700 tracking-wider">Presensi Berhasil! 🎉</h4>
                    <p class="text-sm font-black text-emerald-950 mt-0.5 leading-snug">{{ session('success') }}</p>
                </div>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-950 p-2">
                <i class="fas fa-xmark text-lg"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border-2 border-rose-500 text-rose-950 rounded-2xl p-4.5 shadow-md flex items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-rose-500 text-white shrink-0 flex items-center justify-center text-xl shadow-sm">
                    <i class="fas fa-circle-exclamation"></i>
                </div>
                <div>
                    <h4 class="text-xs font-black uppercase text-rose-700 tracking-wider">Presensi Gagal! ⚠️</h4>
                    <p class="text-sm font-black text-rose-950 mt-0.5 leading-snug">{{ session('error') }}</p>
                </div>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-700 hover:text-rose-950 p-2">
                <i class="fas fa-xmark text-lg"></i>
            </button>
        </div>
    @endif

    @if(session('info'))
        <div class="bg-blue-50 border-2 border-blue-500 text-blue-950 rounded-2xl p-4.5 shadow-md flex items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-blue-500 text-white shrink-0 flex items-center justify-center text-xl shadow-sm">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <h4 class="text-xs font-black uppercase text-blue-700 tracking-wider">Informasi Presensi ℹ️</h4>
                    <p class="text-sm font-black text-blue-950 mt-0.5 leading-snug">{{ session('info') }}</p>
                </div>
            </div>
            <button onclick="this.parentElement.remove()" class="text-blue-700 hover:text-blue-950 p-2">
                <i class="fas fa-xmark text-lg"></i>
            </button>
        </div>
    @endif

    {{-- Header Banner (Neo-Brutalism) --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl p-6 border-2 border-black" style="background: linear-gradient(135deg, #090d16 0%, #0f766e 50%, #115e59 100%) !important;">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-black text-white flex items-center gap-3" style="color: #ffffff !important;">
                    <div class="w-10 h-10 rounded-xl bg-amber-400 border-2 border-black flex items-center justify-center text-black shadow-sm text-lg">
                        <i class="fas fa-clipboard-user text-black"></i>
                    </div>
                    Absensi Saya
                </h1>
                <p class="text-xs md:text-sm font-bold text-teal-200 mt-1" style="color: #99f6e4 !important;">
                    Rekapitulasi kehadiran mengajar dan tugas khusus
                </p>
            </div>
            
            {{-- Filter Bulan/Tahun --}}
            <form method="GET" action="{{ route('guru.absensi.saya') }}" class="flex items-center gap-2">
                <select name="month" onchange="this.form.submit()" class="text-xs font-black border-2 border-black rounded-2xl px-4 py-2.5 shadow-sm outline-none cursor-pointer" style="color: #000000 !important; background-color: #ffffff !important;">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }} style="color: #000000 !important;">
                            {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
                <select name="year" onchange="this.form.submit()" class="text-xs font-black border-2 border-black rounded-2xl px-4 py-2.5 shadow-sm outline-none cursor-pointer" style="color: #000000 !important; background-color: #ffffff !important;">
                    @for($y = now()->year; $y >= now()->year - 2; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }} style="color: #000000 !important;">
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </form>
        </div>
    </div>

    <!-- Special 17 August Independence Day Card for Guru (HUT RI Merah-Putih) -->
    @if(date('m-d') === '08-17')
        <div class="p-5 rounded-3xl bg-gradient-to-r from-red-600 via-rose-600 to-red-900 border-2 border-black shadow-xl text-white space-y-2 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="px-3 py-1 rounded-full text-xs font-black uppercase bg-white text-red-700 shadow border border-red-200">
                    🇮🇩 HARI KEMERDEKAAN RI
                </span>
                <span class="text-xs font-black text-slate-900 bg-white px-3 py-1 rounded-xl border border-slate-200">17 AGUSTUS</span>
            </div>
            <h3 class="text-lg font-black text-white leading-tight drop-shadow-md">DIRGAHAYU REPUBLIK INDONESIA — MERDEKA! ✊</h3>
            <p class="text-xs text-red-50 font-bold leading-relaxed drop-shadow-sm">
                Hormat setinggi-tingginya kepada para Pahlawan Pendidikan! Terima kasih atas dedikasi dan pengabdian Bapak/Ibu Guru dalam mendidik generasi penerus bangsa Indonesia. 🇮🇩✨
            </p>
        </div>
    @endif

    <!-- Presensi GPS Guru Mandiri Trigger Card (Clean White High-Contrast Desktop Card) -->
    <div class="bg-white rounded-3xl p-6 shadow-md border-2 border-slate-200 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <span class="px-3.5 py-1 rounded-full text-xs font-black uppercase bg-teal-100 text-teal-800 border border-teal-200 shadow-xs">
                    📍 Presensi Mandiri GPS Guru
                </span>
                <h3 class="text-xl font-black text-slate-900 mt-2 leading-tight flex items-center gap-2">
                    <i class="fas fa-calendar-day text-teal-600"></i>
                    {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                </h3>
            </div>
            
            <div class="bg-slate-100 px-4 py-2 rounded-2xl border-2 border-slate-200 text-slate-900 font-black text-sm text-center shadow-inner">
                <i class="far fa-clock mr-1.5 text-teal-600"></i> <span id="desktopLiveClock" class="text-base text-slate-900">{{ date('H:i:s') }}</span> WIB
            </div>
        </div>

        <!-- Presensi Status & Form Button -->
        <div class="pt-4 border-t-2 border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            @if(!empty($todayAttendance))
                @php
                    $isNotCheckedOut = !$todayAttendance->time_out || $todayAttendance->time_out === '00:00:00' || $todayAttendance->time_out === '00:00';
                @endphp
                <div class="flex items-center gap-5 min-w-0">
                    <div class="w-13 h-13 rounded-2xl bg-emerald-100 border-2 border-emerald-300 shrink-0 flex items-center justify-center text-emerald-700 text-2xl font-black shadow-sm p-3">
                        <i class="fas fa-circle-check"></i>
                    </div>
                    <div class="space-y-1 min-w-0">
                        <span class="text-xs text-slate-500 font-extrabold block uppercase tracking-wider">Status Presensi Hari Ini</span>
                        <div class="text-base sm:text-lg font-black text-slate-900 leading-tight">
                            Hadir <span class="text-xs font-bold text-slate-500">(Masuk: <span class="text-emerald-600 font-black text-sm">{{ substr($todayAttendance->time_in ?? $todayAttendance->check_in_time ?? date('H:i'), 0, 5) }}</span>)</span>
                            @if(!$isNotCheckedOut)
                                <span class="text-xs font-bold text-slate-500">| Pulang: <span class="text-blue-600 font-black text-sm">{{ substr($todayAttendance->time_out, 0, 5) }}</span></span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($isNotCheckedOut)
                    <form action="{{ route('mobile.absensi.scan') }}" method="POST" id="desktopGpsForm" class="w-full sm:w-auto shrink-0">
                        @csrf
                        <input type="hidden" name="latitude" id="desktopLatInput">
                        <input type="hidden" name="longitude" id="desktopLngInput">
                        <button type="button" onclick="handleDesktopGpsScan()" class="w-full sm:w-auto px-8 py-3.5 bg-amber-400 text-slate-950 font-black text-xs sm:text-sm rounded-2xl shadow-md hover:bg-amber-300 transition flex items-center justify-center gap-3 border-2 border-slate-900">
                            <i class="fas fa-right-from-bracket text-base text-slate-900"></i>
                            <span class="tracking-wide">PRESENSI GPS PULANG SEKARANG</span>
                        </button>
                    </form>
                @endif
            @else
                <div class="flex items-center gap-5 min-w-0">
                    <div class="w-13 h-13 rounded-2xl bg-amber-100 border-2 border-amber-300 shrink-0 flex items-center justify-center text-amber-800 text-2xl font-black shadow-sm p-3">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="space-y-1 min-w-0">
                        <span class="text-xs text-slate-500 font-extrabold block uppercase tracking-wider">Status Presensi Hari Ini</span>
                        <div class="text-base sm:text-lg font-black text-amber-700 leading-tight">
                            Belum Presensi Masuk Hari Ini
                        </div>
                    </div>
                </div>

                <form action="{{ route('mobile.absensi.scan') }}" method="POST" id="desktopGpsForm" class="w-full sm:w-auto shrink-0">
                    @csrf
                    <input type="hidden" name="latitude" id="desktopLatInput">
                    <input type="hidden" name="longitude" id="desktopLngInput">
                    <button type="button" onclick="handleDesktopGpsScan()" class="w-full sm:w-auto px-8 py-3.5 bg-teal-600 text-white font-black text-xs sm:text-sm rounded-2xl shadow-md hover:bg-teal-700 transition flex items-center justify-center gap-3 border-2 border-teal-800">
                        <i class="fas fa-location-dot text-amber-300 text-base"></i>
                        <span class="tracking-wide">PRESENSI GPS GURU SEKARANG</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <!-- Kehadiran Rate -->
        <div class="bg-white rounded-2xl shadow-sm border border-teal-50 p-5 flex flex-col justify-between relative overflow-hidden group hover:shadow-md transition">
            <div class="absolute -right-4 -bottom-4 text-teal-50 opacity-10 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-percent text-8xl"></i>
            </div>
            <div>
                <div class="w-9 h-9 bg-teal-100 rounded-xl flex items-center justify-center mb-3">
                    <i class="fas fa-percentage text-teal-600 text-sm"></i>
                </div>
                <p class="text-2xl font-black text-gray-800 leading-none">{{ $pct }}%</p>
                <p class="text-xs text-gray-500 font-medium mt-1">Kehadiran Wajib</p>
            </div>
            <div class="text-[10px] text-teal-600 font-semibold mt-4">
                {{ $totals['present_on_scheduled'] }} / {{ $totals['total_scheduled'] }} Hari Terjadwal
            </div>
        </div>

        <!-- Hadir Mengajar -->
        <div class="bg-white rounded-2xl shadow-sm border border-emerald-50 p-5 flex flex-col justify-between relative overflow-hidden group hover:shadow-md transition">
            <div class="absolute -right-4 -bottom-4 text-emerald-50 opacity-15 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-chalkboard-teacher text-8xl"></i>
            </div>
            <div>
                <div class="w-9 h-9 bg-emerald-100 rounded-xl flex items-center justify-center mb-3">
                    <i class="fas fa-check-circle text-emerald-600 text-sm"></i>
                </div>
                <p class="text-2xl font-black text-gray-800 leading-none">{{ $totals['hadir_mengajar'] }} Hari</p>
                <p class="text-xs text-gray-500 font-medium mt-1">Hadir Mengajar (HM)</p>
            </div>
            <div class="text-[10px] text-emerald-600 font-semibold mt-4">
                Sesuai jadwal mengajar
            </div>
        </div>

        <!-- Tugas Khusus -->
        <div class="bg-white rounded-2xl shadow-sm border border-indigo-50 p-5 flex flex-col justify-between relative overflow-hidden group hover:shadow-md transition">
            <div class="absolute -right-4 -bottom-4 text-indigo-50 opacity-15 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-star text-8xl"></i>
            </div>
            <div>
                <div class="w-9 h-9 bg-indigo-100 rounded-xl flex items-center justify-center mb-3">
                    <i class="fas fa-award text-indigo-600 text-sm"></i>
                </div>
                <p class="text-2xl font-black text-gray-800 leading-none">{{ $totals['tugas_khusus'] }} Hari</p>
                <p class="text-xs text-gray-500 font-medium mt-1">Tugas Khusus (TK)</p>
            </div>
            <div class="text-[10px] text-indigo-600 font-semibold mt-4">
                Hadir diluar jadwal mengajar
            </div>
        </div>

        <!-- Reputation Points -->
        <div class="bg-white rounded-2xl shadow-sm border border-yellow-50 p-5 flex flex-col justify-between relative overflow-hidden group hover:shadow-md transition">
            <div class="absolute -right-4 -bottom-4 text-yellow-50 opacity-15 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-coins text-8xl"></i>
            </div>
            <div>
                <div class="w-9 h-9 bg-yellow-100 rounded-xl flex items-center justify-center mb-3">
                    <i class="fas fa-coins text-yellow-600 text-sm"></i>
                </div>
                <p class="text-2xl font-black text-gray-800 leading-none">+{{ $totals['tugas_khusus'] * 15 }} Pts</p>
                <p class="text-xs text-gray-500 font-medium mt-1">Poin Reputasi Didapat</p>
            </div>
            <div class="text-[10px] text-yellow-600 font-semibold mt-4">
                15 Poin tiap Tugas Khusus
            </div>
        </div>

        <!-- Ketidakhadiran -->
        <div class="bg-white rounded-2xl shadow-sm border border-rose-50 p-5 flex flex-col justify-between relative overflow-hidden group hover:shadow-md transition">
            <div class="absolute -right-4 -bottom-4 text-rose-50 opacity-15 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-exclamation-triangle text-8xl"></i>
            </div>
            <div>
                <div class="w-9 h-9 bg-rose-100 rounded-xl flex items-center justify-center mb-3">
                    <i class="fas fa-times-circle text-rose-600 text-sm"></i>
                </div>
                <p class="text-2xl font-black text-gray-800 leading-none">{{ $totals['alpha'] }} Hari</p>
                <p class="text-xs text-gray-500 font-medium mt-1">Absen Wajib (Alpha)</p>
            </div>
            <div class="text-[10px] text-rose-600 font-semibold mt-4">
                Tidak hadir di hari mengajar
            </div>
        </div>
    </div>

    <!-- Kalender Absensi -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-gray-800 text-base flex items-center gap-2">
                    <i class="fas fa-calendar-days text-teal-600"></i> Kalender Kehadiran Bulanan
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Daftar kehadiran harian Anda sepanjang bulan</p>
            </div>
            
            <!-- Legenda Ringkas -->
            <div class="flex flex-wrap gap-2 text-[10px] font-semibold">
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-emerald-50 text-emerald-700 border border-emerald-100">HM = Hadir Mengajar</span>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-indigo-50 text-indigo-700 border border-indigo-100">TK = Tugas Khusus</span>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-rose-50 text-rose-700 border border-rose-100">A = Alpha</span>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-gray-50 text-gray-400 border border-gray-100">- = Bebas Tugas</span>
            </div>
        </div>

        <div class="p-5">
            <!-- Grid 7 Kolom (Hari Mingguan) -->
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-7 gap-3">
                @for($d = 1; $d <= $daysInMonth; $d++)
                    @php
                        $dayData = $calendarData[$d];
                        $date = $dayData['date'];
                        $att = $dayData['attendance'];
                    @endphp
                    <div class="border rounded-2xl p-4 flex flex-col justify-between min-h-[110px] transition-all relative overflow-hidden {{ $dayData['color_class'] }} hover:scale-[1.02] hover:shadow-sm">
                        <!-- Sudut Kanan Atas: Tanggal & Nama Hari -->
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-lg font-black block leading-none">{{ $d }}</span>
                                <span class="text-[9px] uppercase tracking-wider font-semibold opacity-70 mt-1 block">{{ $date->translatedFormat('l') }}</span>
                            </div>
                            
                            <!-- Badge Status Singkat -->
                            <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded shadow-sm border bg-white/60">
                                {{ $dayData['status'] }}
                            </span>
                        </div>

                        <!-- Bagian Bawah: Jam Scan & Keterangan -->
                        <div class="mt-4 pt-2 border-t border-current/10">
                            @if($att)
                                <div class="text-[10px] font-semibold space-y-0.5">
                                    <div class="flex justify-between">
                                        <span>Masuk:</span>
                                        <span class="font-bold">{{ substr($att->time_in, 0, 5) }}</span>
                                    </div>
                                    @if($att->time_out && $att->time_out !== '00:00:00' && $att->time_out !== '00:00')
                                        <div class="flex justify-between">
                                            <span>Pulang:</span>
                                            <span class="font-bold">{{ substr($att->time_out, 0, 5) }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                @if($dayData['status'] === 'TK')
                                    <span class="absolute -right-3 -bottom-3 w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[8px] font-bold shadow-md transform rotate-12" title="Mendapatkan +15 Poin Reputasi">
                                        +15
                                    </span>
                                @endif
                            @else
                                <p class="text-[10px] italic opacity-80 font-medium">
                                    {{ $dayData['status_label'] }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
</div>

<script>
    setInterval(() => {
        const now = new Date();
        const clockEl = document.getElementById('desktopLiveClock');
        if (clockEl) {
            clockEl.innerText = now.toTimeString().split(' ')[0];
        }
    }, 1000);

    function handleDesktopGpsScan() {
        const form = document.getElementById('desktopGpsForm');
        const latInput = document.getElementById('desktopLatInput');
        const lngInput = document.getElementById('desktopLngInput');

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
