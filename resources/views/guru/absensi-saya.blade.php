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

    <!-- Presensi GPS Guru Mandiri Trigger Card (Modern Gradient & High-Contrast Neo-Brutalism) -->
    <div class="bg-white rounded-3xl p-6 shadow-xl border-2 border-black space-y-5 relative overflow-hidden">
        {{-- Background glow accent --}}
        <div class="absolute -right-12 -top-12 w-48 h-48 bg-emerald-400/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative z-10">
            <div class="flex items-center gap-4">
                <div class="w-13 h-13 rounded-2xl bg-amber-400 border-2 border-black flex items-center justify-center text-black text-2xl font-black shadow-md flex-shrink-0">
                    <i class="fas fa-location-dot text-black"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        <span class="px-3 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider border border-black bg-teal-100 text-teal-900 shadow-xs">
                            📍 Presensi Mandiri GPS Guru & Pegawai
                        </span>
                        <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-300">
                            <i class="fas fa-shield-halved text-emerald-600 mr-1"></i> Geofencing Radius Aktif
                        </span>
                    </div>
                    <h3 class="text-xl font-black text-slate-900 leading-tight">
                        {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                    </h3>
                </div>
            </div>
            
            <div class="bg-slate-900 text-white px-5 py-2.5 rounded-2xl border-2 border-black font-black text-sm flex items-center gap-2 shadow-md self-start sm:self-auto">
                <i class="far fa-clock text-amber-400 text-base"></i>
                <span id="desktopLiveClock" class="text-base text-amber-300 tracking-wider font-mono">{{ date('H:i:s') }}</span>
                <span class="text-xs text-slate-400 uppercase">WIB</span>
            </div>
        </div>

        <!-- Presensi Status & Action Buttons -->
        <div class="pt-4 border-t-2 border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-5 relative z-10">
            @if(!empty($todayAttendance))
                @php
                    $isNotCheckedOut = !$todayAttendance->time_out || $todayAttendance->time_out === '00:00:00' || $todayAttendance->time_out === '00:00';
                @endphp
                <div class="flex items-center gap-4 min-w-0">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 border-2 border-emerald-400 shrink-0 flex items-center justify-center text-emerald-700 text-2xl font-black shadow-sm">
                        <i class="fas fa-circle-check"></i>
                    </div>
                    <div class="space-y-0.5 min-w-0">
                        <span class="text-[11px] text-slate-500 font-extrabold uppercase tracking-wider block">Status Presensi Hari Ini</span>
                        <div class="text-base font-black text-slate-900 flex flex-wrap items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-lg bg-emerald-100 text-emerald-900 border border-emerald-300 text-xs font-black">Hadir Masuk</span>
                            <span class="text-xs font-bold text-slate-600">Jam: <span class="text-emerald-700 font-black">{{ substr($todayAttendance->time_in ?? $todayAttendance->check_in_time ?? date('H:i'), 0, 5) }} WIB</span></span>
                            @if(!$isNotCheckedOut)
                                <span class="text-slate-300">·</span>
                                <span class="px-2.5 py-0.5 rounded-lg bg-blue-100 text-blue-900 border border-blue-300 text-xs font-black">Pulang</span>
                                <span class="text-xs font-bold text-slate-600">Jam: <span class="text-blue-700 font-black">{{ substr($todayAttendance->time_out, 0, 5) }} WIB</span></span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($isNotCheckedOut)
                    <button type="button" onclick="performGuruGpsScan(this)" class="w-full sm:w-auto px-8 py-3.5 bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs sm:text-sm rounded-2xl shadow-lg transition-all transform hover:scale-105 active:scale-95 flex items-center justify-center gap-3 border-2 border-black">
                        <i class="fas fa-right-from-bracket text-base text-slate-900"></i>
                        <span class="tracking-wide">PRESENSI PULANG SEKARANG (GPS)</span>
                    </button>
                @else
                    <div class="px-5 py-2.5 rounded-2xl bg-emerald-50 border-2 border-emerald-300 text-emerald-900 text-xs font-black flex items-center gap-2">
                        <i class="fas fa-circle-check text-emerald-600 text-base"></i>
                        <span>Presensi Lengkap Hari Ini (Masuk & Pulang Selesai)</span>
                    </div>
                @endif
            @else
                <div class="flex items-center gap-4 min-w-0">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 border-2 border-amber-300 shrink-0 flex items-center justify-center text-amber-800 text-2xl font-black shadow-sm">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="space-y-0.5 min-w-0">
                        <span class="text-[11px] text-slate-500 font-extrabold uppercase tracking-wider block">Status Presensi Hari Ini</span>
                        <div class="text-base font-black text-amber-800 leading-tight">
                            Belum Melakukan Presensi Masuk
                        </div>
                    </div>
                </div>

                <button type="button" onclick="performGuruGpsScan(this)" class="w-full sm:w-auto px-8 py-3.5 bg-teal-600 hover:bg-teal-700 text-white font-black text-xs sm:text-sm rounded-2xl shadow-lg transition-all transform hover:scale-105 active:scale-95 flex items-center justify-center gap-3 border-2 border-black">
                    <i class="fas fa-fingerprint text-amber-300 text-lg animate-pulse"></i>
                    <span class="tracking-wide">PRESENSI MASUK SEKARANG (GPS)</span>
                </button>
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

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    setInterval(() => {
        const now = new Date();
        const clockEl = document.getElementById('desktopLiveClock');
        if (clockEl) {
            clockEl.innerText = now.toTimeString().split(' ')[0];
        }
    }, 1000);

    function performGuruGpsScan(btnElement) {
        if (!navigator.geolocation) {
            Swal.fire({
                icon: 'error',
                title: 'GPS Tidak Didukung',
                text: 'Browser Anda tidak mendukung fitur lokasi (GPS). Gunakan Google Chrome atau browser modern lainnya.',
                confirmButtonColor: '#059669',
            });
            return;
        }

        const origHtml = btnElement ? btnElement.innerHTML : '';
        if (btnElement) {
            btnElement.disabled = true;
            btnElement.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i> Mengambil Lokasi GPS...';
        }

        navigator.geolocation.getCurrentPosition(
            function (pos) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                let deviceId = localStorage.getItem('pembdahub_device_id');
                if (!deviceId) {
                    deviceId = 'dev_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                    localStorage.setItem('pembdahub_device_id', deviceId);
                }

                if (btnElement) {
                    btnElement.innerHTML = '<i class="fas fa-spinner fa-spin mr-1.5"></i> Memverifikasi Presensi...';
                }

                fetch('{{ route('mobile.absensi.scan') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        latitude: lat,
                        longitude: lng,
                        device_id: deviceId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (btnElement) {
                        btnElement.disabled = false;
                        btnElement.innerHTML = origHtml;
                    }

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Presensi Berhasil! 🎉',
                            html: `<p class="font-bold text-slate-800">${data.message}</p><p class="text-xs text-slate-500 mt-2">Terima kasih atas dedikasi dan pengabdian Bapak/Ibu Guru!</p>`,
                            timer: 3500,
                            timerProgressBar: true,
                            showConfirmButton: true,
                            confirmButtonText: 'Tutup',
                            confirmButtonColor: '#059669',
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Presensi Belum Berhasil ⚠️',
                            text: data.message || 'Terjadi kesalahan saat memverifikasi lokasi.',
                            confirmButtonColor: '#e11d48',
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    if (btnElement) {
                        btnElement.disabled = false;
                        btnElement.innerHTML = origHtml;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gangguan Jaringan',
                        text: 'Gagal terhubung ke server. Pastikan koneksi internet aktif dan stabil.',
                        confirmButtonColor: '#e11d48',
                    });
                });
            },
            function (err) {
                if (btnElement) {
                    btnElement.disabled = false;
                    btnElement.innerHTML = origHtml;
                }
                let msg = 'Gagal mengakses GPS pada perangkat Anda.';
                if (err.code === err.PERMISSION_DENIED) {
                    msg = 'Akses lokasi ditolak. Harap izinkan akses lokasi (GPS) pada browser/perangkat Anda.';
                } else if (err.code === err.POSITION_UNAVAILABLE) {
                    msg = 'Sinyal GPS tidak terdeteksi. Pastikan GPS aktif dan berada di area terbuka.';
                }
                Swal.fire({
                    icon: 'warning',
                    title: 'Akses Lokasi Diperlukan 📍',
                    text: msg,
                    confirmButtonColor: '#f59e0b',
                });
            },
            { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
        );
    }
</script>
@endpush
