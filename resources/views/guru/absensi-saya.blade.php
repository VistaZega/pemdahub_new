@extends('layouts.guru')
@section('title', 'Absensi Saya - Portal Guru')

@section('content')
<div class="space-y-6">
    {{-- Header Banner (Neo-Brutalism) --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl p-6 border-2 border-black" style="background: linear-gradient(135deg, #090d16 0%, #0f766e 50%, #115e59 100%) !important; color: #ffffff !important;">
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
                <select name="month" onchange="this.form.submit()" class="text-xs font-black border-2 border-black bg-white rounded-2xl px-4 py-2.5 shadow-sm text-black outline-none">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
                <select name="year" onchange="this.form.submit()" class="text-xs font-black border-2 border-black bg-white rounded-2xl px-4 py-2.5 shadow-sm text-black outline-none">
                    @for($y = now()->year; $y >= now()->year - 2; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </form>
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
@endsection
