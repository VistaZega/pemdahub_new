@extends('layouts.siswa')
@section('title', 'Dashboard - Portal Siswa')

@section('content')
<div class="space-y-6">
    {{-- Compact Greeting Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white rounded-xl shadow-sm border border-gray-100 px-5 py-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl flex-shrink-0 overflow-hidden shadow-md">
                <img src="{{ $student->photo_url }}" class="w-full h-full object-cover" alt="{{ $student->full_name }}">
            </div>
            <div>
                <h1 class="text-lg md:text-xl font-bold text-gray-800">Selamat Datang, {{ explode(' ', $student->full_name)[0] }}!</h1>
                <p class="text-xs text-gray-500 mt-0.5 flex flex-wrap items-center gap-x-2">
                    <span><i class="fas fa-school mr-1 text-blue-500"></i>{{ $student->school->name ?? '' }}</span>
                    @if($classroom) <span class="text-gray-300">·</span> <span>{{ $classroom->class_name }}</span> @endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @if($activeYear)
                <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 px-3 py-1.5 rounded-lg text-xs font-semibold">
                    <i class="fas fa-calendar-alt text-xs"></i> {{ $activeYear->year }}
                </span>
            @endif
            <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-lg text-xs font-semibold">
                <i class="far fa-clock text-xs"></i> {{ now()->translatedFormat('l, d M Y') }}
            </span>
        </div>
    </div>

    {{-- Urgent Attendance Banner --}}
    @if(!$todayAttendance || !$todayAttendance->time_out)
    <div class="bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 rounded-2xl shadow-xl p-0.5 overflow-hidden">
        <div class="bg-white/10 px-5 py-4 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4 text-white">
                <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center text-3xl animate-bounce">
                    <i class="fas fa-fingerprint"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg leading-tight">Waktunya Absensi! 🕒</h3>
                    <p class="text-white/80 text-xs">
                        @if(!$todayAttendance)
                            Kamu belum melakukan Absen Masuk hari ini.
                        @else
                            Kamu sudah masuk jam {{ $todayAttendance->time_in }}. Jangan lupa Absen Pulang nanti!
                        @endif
                    </p>
                </div>
            </div>
            <a href="{{ route('siswa.absensi') }}" class="w-full md:w-auto bg-white text-indigo-600 font-bold px-5 py-2.5 rounded-xl shadow-lg hover:bg-indigo-50 transition transform hover:scale-105 active:scale-95 text-center text-sm uppercase tracking-wider">
                Absen Sekarang
            </a>
        </div>
    </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Kehadiran --}}
        <div class="stat-card group">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-11 h-11 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform flex-shrink-0">
                    <i class="fas fa-user-check text-white text-sm"></i>
                </div>
                <div>
                    <span class="text-3xl font-bold text-gray-800 leading-none">{{ $attendanceData['percentage'] }}%</span>
                </div>
            </div>
            <p class="text-sm font-semibold text-gray-700">Persentase Kehadiran</p>
            <p class="text-xs text-gray-400 mt-0.5 leading-none">
                {{ $attendanceData['present'] }} Hadir / {{ $attendanceData['total'] }} Hari Aktif
            </p>
        </div>
        {{-- Rata-rata Nilai --}}
        <div class="stat-card group">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-11 h-11 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform flex-shrink-0">
                    <i class="fas fa-chart-line text-white text-sm"></i>
                </div>
                <div>
                    <span class="text-3xl font-bold text-gray-800 leading-none">{{ number_format($avgScore, 1) }}</span>
                </div>
            </div>
            <p class="text-sm font-semibold text-gray-700">Rata Nilai</p>
            <p class="text-xs text-gray-400 mt-0.5">IP Semester</p>
        </div>
        {{-- Tagihan --}}
        <div class="stat-card group">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-11 h-11 bg-gradient-to-br from-rose-400 to-rose-600 rounded-xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform flex-shrink-0">
                    <i class="fas fa-wallet text-white text-sm"></i>
                </div>
                <div>
                    <span class="text-xl font-bold text-gray-800 leading-none">Rp {{ number_format($totalOutstanding/1000, 0) }}k</span>
                </div>
            </div>
            <p class="text-sm font-semibold text-gray-700">Tunggakan</p>
            <p class="text-xs text-rose-500 mt-0.5 font-medium">Belum lunas</p>
        </div>
        {{-- Jadwal Hari Ini --}}
        <div class="stat-card group relative overflow-hidden">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-11 h-11 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform flex-shrink-0">
                    <i class="fas fa-calendar-check text-white text-sm"></i>
                </div>
                <div>
                    <span class="text-3xl font-bold text-gray-800 leading-none">{{ $todaySchedules->count() }}</span>
                </div>
            </div>
            <p class="text-sm font-semibold text-gray-700">Jadwal Hari Ini</p>
            @if($nextSchedule)
                <p class="text-xs text-amber-600 mt-0.5 font-medium">
                    <i class="fas fa-arrow-right text-xs mr-0.5"></i> Berikutnya: {{ $nextSchedule->timeSlot->start_time ?? '-' }}
                </p>
            @elseif($currentSchedule)
                <p class="text-xs text-emerald-600 mt-0.5 font-medium">
                    <i class="fas fa-circle text-[6px] mr-0.5 animate-pulse"></i> Sedang Belajar
                </p>
            @else
                <p class="text-xs text-gray-400 mt-0.5">Bebas jadwal hari ini</p>
            @endif
        </div>
    </div>

    {{-- Widget DNA Potensi & Karir Siswa 360° --}}
    @if(isset($dnaAnalysis))
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-purple-950 rounded-2xl p-6 text-white relative overflow-hidden shadow-lg border border-slate-800">
        <div class="absolute -right-8 -top-8 w-48 h-48 bg-purple-500/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
            <div class="space-y-2 max-w-xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3 py-1 bg-gradient-to-r {{ $dnaAnalysis['archetype']['color'] }} text-white rounded-full text-[11px] font-black uppercase tracking-wider inline-flex items-center gap-1.5 shadow-sm">
                        <i class="fas {{ $dnaAnalysis['archetype']['badge_icon'] }}"></i> {{ $dnaAnalysis['archetype']['title'] }}
                    </span>
                    <span class="text-[11px] font-bold text-slate-300 bg-white/10 px-2.5 py-1 rounded-full">
                        Akurasi: {{ $dnaAnalysis['confidence_score'] }}%
                    </span>
                </div>
                <h2 class="text-lg sm:text-xl font-black text-white tracking-tight">
                    DNA Potensi Belajar & Minat Karier 360°
                </h2>
                <p class="text-xs text-slate-200 leading-relaxed font-normal">
                    "{{ $dnaAnalysis['archetype']['tagline'] }}" &mdash; {{ $dnaAnalysis['archetype']['description'] }}
                </p>

                {{-- Mini 6 Dimension Meters --}}
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 pt-2 text-[10px]">
                    <div class="bg-white/10 rounded-lg p-2 text-center border border-white/10">
                        <p class="text-slate-300 font-bold">Logika</p>
                        <p class="text-sm font-black text-blue-300">{{ $dnaAnalysis['scores']['logic'] }}</p>
                    </div>
                    <div class="bg-white/10 rounded-lg p-2 text-center border border-white/10">
                        <p class="text-slate-300 font-bold">Bahasa</p>
                        <p class="text-sm font-black text-emerald-300">{{ $dnaAnalysis['scores']['communication'] }}</p>
                    </div>
                    <div class="bg-white/10 rounded-lg p-2 text-center border border-white/10">
                        <p class="text-slate-300 font-bold">Vokasi</p>
                        <p class="text-sm font-black text-indigo-300">{{ $dnaAnalysis['scores']['technical'] }}</p>
                    </div>
                    <div class="bg-white/10 rounded-lg p-2 text-center border border-white/10">
                        <p class="text-slate-300 font-bold">Sosial</p>
                        <p class="text-sm font-black text-amber-300">{{ $dnaAnalysis['scores']['social'] }}</p>
                    </div>
                    <div class="bg-white/10 rounded-lg p-2 text-center border border-white/10">
                        <p class="text-slate-300 font-bold">Kreatif</p>
                        <p class="text-sm font-black text-purple-300">{{ $dnaAnalysis['scores']['creative'] }}</p>
                    </div>
                    <div class="bg-white/10 rounded-lg p-2 text-center border border-white/10">
                        <p class="text-slate-300 font-bold">Disiplin</p>
                        <p class="text-sm font-black text-rose-300">{{ $dnaAnalysis['scores']['discipline'] }}</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row md:flex-col gap-2.5 w-full md:w-auto flex-shrink-0">
                <a href="{{ route('siswa.dna.index') }}" class="px-5 py-2.5 bg-gradient-to-r from-fuchsia-600 to-indigo-600 hover:from-fuchsia-500 hover:to-indigo-500 text-white rounded-xl text-xs font-bold transition text-center shadow-md flex items-center justify-center gap-2">
                    <i class="fas fa-radar"></i> Lihat Analisis Lengkap 360°
                </a>
                <a href="{{ route('siswa.dna.pdf') }}" class="px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white border border-white/20 rounded-xl text-xs font-bold transition text-center flex items-center justify-center gap-2">
                    <i class="fas fa-file-pdf text-rose-400"></i> Unduh Lembar Rapor DNA (PDF)
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- Main Content: Timeline + Courses --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Jadwal Hari Ini - Timeline style --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-calendar-day text-blue-500"></i> Jadwal Pelajaran Hari Ini
                </h2>
                <a href="{{ route('siswa.jadwal') }}" class="text-xs text-blue-600 hover:underline font-medium">Lihat Semua →</a>
            </div>
            <div class="p-5">
                @if($groupedTodaySchedules->count() > 0)
                    <div class="relative">
                        {{-- Timeline line --}}
                        <div class="absolute left-[18px] top-2 bottom-2 w-0.5 bg-gradient-to-b from-blue-200 via-blue-300 to-blue-100 rounded-full"></div>

                        <div class="space-y-1">
                            @foreach($groupedTodaySchedules as $timeKey => $schedulesAtTime)
                                @php
                                    $first = $schedulesAtTime->first();
                                    $sStart = \Carbon\Carbon::parse(explode(' - ', $timeKey)[0])->format('H:i');
                                    $sEnd = \Carbon\Carbon::parse(explode(' - ', $timeKey)[1])->format('H:i');
                                    
                                    // Status detection
                                    $isCurrent = false;
                                    $isNext = false;
                                    foreach($schedulesAtTime as $s) {
                                        if ($currentSchedule && $currentSchedule->id === $s->id) $isCurrent = true;
                                        if ($nextSchedule && $nextSchedule->id === $s->id) $isNext = true;
                                    }
                                    
                                    $isPast = $currentTime > $sEnd;
                                @endphp
                                <div class="flex items-start gap-4 pl-1 py-2 relative group">
                                    {{-- Timeline dot --}}
                                    <div class="relative z-10 mt-3 flex-shrink-0">
                                        @if($isCurrent)
                                            <div class="w-[14px] h-[14px] bg-blue-500 rounded-full ring-4 ring-blue-100 shadow-sm animate-pulse"></div>
                                        @elseif($isPast)
                                            <div class="w-[10px] h-[10px] bg-gray-300 rounded-full ml-0.5 mt-0.5"></div>
                                        @else
                                            <div class="w-[10px] h-[10px] bg-blue-300 rounded-full ml-0.5 mt-0.5"></div>
                                        @endif
                                    </div>

                                    {{-- Schedule Card --}}
                                    <div class="flex-1 rounded-xl p-3.5 transition-all duration-200 {{ $isCurrent ? 'bg-blue-50 border border-blue-200 shadow-sm ring-1 ring-blue-100' : ($isPast ? 'bg-gray-50/80 border border-gray-100 opacity-60' : 'bg-gray-50 border border-gray-100 hover:bg-gray-100/80') }}">
                                        <div class="flex items-center justify-between mb-1.5">
                                            <div class="flex items-center gap-2">
                                                @if($isCurrent)
                                                    <span class="inline-flex items-center gap-1 text-xs bg-blue-500 text-white px-2 py-0.5 rounded-full font-bold shadow-sm">
                                                        <i class="fas fa-circle text-[5px] animate-pulse"></i> BERLANGSUNG
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs font-bold {{ $isCurrent ? 'text-blue-700' : 'text-gray-600' }}">{{ $sStart }} – {{ $sEnd }}</p>
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-1">
                                            @foreach($schedulesAtTime as $s)
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <p class="font-bold text-gray-800 text-sm mb-0.5">{{ $s->subject->subject_name ?? $s->subject->name ?? '-' }}</p>
                                                        <p class="text-xs text-gray-500 font-medium">
                                                            <i class="fas fa-chalkboard-teacher mr-1 opacity-70"></i>{{ $s->teacher->user->name ?? '-' }}
                                                        </p>
                                                    </div>
                                                    @if($s->room)
                                                        <span class="text-xs bg-white px-2 py-0.5 rounded-full text-gray-500 border border-gray-100">
                                                            <i class="fas fa-door-open mr-1 text-gray-400"></i>{{ $s->room }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-10 text-gray-400">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-mug-hot text-3xl text-gray-300"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Tidak ada jadwal pelajaran hari ini</p>
                        <p class="text-xs text-gray-400 mt-1">Gunakan waktu untuk belajar mandiri! 📚</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Attendance Pulse (Compact History) --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <h2 class="font-bold text-gray-800 flex items-center gap-2 text-sm">
                    <i class="fas fa-history text-emerald-500"></i> Riwayat Kehadiran Terakhir
                </h2>
                <a href="{{ route('siswa.absensi') }}" class="text-xs text-gray-400 hover:text-blue-600 font-semibold uppercase tracking-wider">Detail Selengkapnya →</a>
            </div>
            <div class="p-5">
                <div class="overflow-x-auto overflow-y-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-50">
                                <th class="pb-3 text-left">Hari / Tanggal</th>
                                <th class="pb-3 text-center">Status</th>
                                <th class="pb-3 text-center">Masuk</th>
                                <th class="pb-3 text-center">Keluar</th>
                                <th class="pb-3 text-left hidden md:table-cell pl-4">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($attendanceHistory as $att)
                                <tr class="group hover:bg-gray-50/80 transition-all">
                                    <td class="py-3 pr-4">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-gray-700">{{ $att->date->translatedFormat('d M Y') }}</span>
                                            <span class="text-xs text-gray-400 uppercase tracking-tighter">{{ $att->date->translatedFormat('l') }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 text-center">
                                        @php
                                            $statusBadge = match($att->status) {
                                                'hadir' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                                'sakit' => 'bg-amber-100 text-amber-700 border-amber-200',
                                                'izin' => 'bg-blue-100 text-blue-700 border-blue-200',
                                                'alpha' => 'bg-rose-100 text-rose-700 border-rose-200',
                                                default => 'bg-gray-100 text-gray-600'
                                            };
                                        @endphp
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold uppercase border {{ $statusBadge }}">
                                            {{ $att->status }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="text-xs font-mono text-gray-600 bg-gray-50 px-1.5 py-0.5 rounded border border-gray-100">{{ $att->time_in ? date('H:i', strtotime($att->time_in)) : '--:--' }}</span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="text-xs font-mono text-gray-600 bg-gray-50 px-1.5 py-0.5 rounded border border-gray-100">{{ $att->time_out ? date('H:i', strtotime($att->time_out)) : '--:--' }}</span>
                                    </td>
                                    <td class="py-3 text-left hidden md:table-cell pl-4">
                                        <p class="text-xs text-gray-400 italic max-w-[120px] truncate">{{ $att->notes ?? '-' }}</p>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-gray-400 italic text-xs">Belum ada riwayat kehadiran tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">
            
            {{-- Billing Progress Widget --}}
            @if(isset($studentBillingStats))
            <div class="bg-gradient-to-br from-indigo-50 to-white rounded-2xl shadow-sm border border-indigo-100 p-5 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-100/50 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                
                <h2 class="font-bold text-indigo-700 mb-4 flex items-center gap-2 text-sm relative z-10">
                    <i class="fas fa-file-invoice-dollar text-indigo-500"></i> Progress Pembayaran
                </h2>
                
                <div class="relative z-10 space-y-4">
                    <div class="flex justify-between items-end">
                        <div>
                            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Telah Dibayar</p>
                            <p class="text-base font-black text-indigo-600">Rp {{ number_format($studentBillingStats['paid_amount'], 0, ',', '.') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Tunggakan</p>
                            <p class="text-base font-black text-rose-500">Rp {{ number_format($studentBillingStats['outstanding'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                    
                    {{-- Progress Bar --}}
                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1.5">
                            <span class="text-indigo-700">{{ $studentBillingStats['percentage'] }}% Lunas</span>
                            <span class="text-gray-500">{{ $studentBillingStats['paid_bills'] }}/{{ $studentBillingStats['total_bills'] }} Tagihan</span>
                        </div>
                        <div class="w-full bg-indigo-100/50 rounded-full h-2.5 overflow-hidden border border-indigo-50">
                            <div class="bg-gradient-to-r from-indigo-400 to-indigo-600 h-full rounded-full transition-all duration-1000 shadow-sm" style="width: {{ $studentBillingStats['percentage'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Kursus LMS --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-gray-800 flex items-center gap-2 text-sm">
                        <i class="fas fa-book-reader text-indigo-500"></i> Kursus Saya (LMS)
                    </h2>
                    <a href="{{ route('siswa.lms.index') }}" class="text-xs text-indigo-600 font-bold hover:underline uppercase tracking-wider">Semua</a>
                </div>
                
                <div class="space-y-3">
                    @forelse($courses as $course)
                        @php $progress = $courseProgress[$course->id] ?? 0; @endphp
                        <a href="{{ route('siswa.lms.show', $course->id) }}" class="block group">
                            <div class="p-3 rounded-xl bg-gray-50 border border-gray-100 hover:border-indigo-100 hover:bg-indigo-50/30 transition-all">
                                <h4 class="text-xs font-bold text-gray-800 truncate mb-2 group-hover:text-indigo-700">{{ $course->name }}</h4>
                                <div class="w-full bg-gray-200 rounded-full h-1 mb-1.5 overflow-hidden">
                                    <div class="h-full bg-indigo-500 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                                </div>
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-gray-400">{{ $course->materials_count }} Materi</span>
                                    <span class="font-bold text-indigo-600">{{ $progress }}%</span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <p class="text-center py-4 text-xs text-gray-400 italic">Belum ada kursus aktif</p>
                    @endforelse
                </div>
            </div>

            {{-- Reputation / Pembda Elite System --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5 relative overflow-hidden group">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-emerald-50 rounded-full group-hover:scale-150 transition-transform duration-700 opacity-50"></div>
                
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <h2 class="font-bold text-gray-800 flex items-center gap-2 text-sm uppercase tracking-wider">
                        <i class="fas fa-crown text-amber-500"></i> Pembda Elite
                    </h2>
                    <a href="{{ route('reputation.leaderboard') }}" class="text-xs font-semibold text-indigo-600 hover:underline uppercase tracking-wider">Papan Skor</a>
                </div>

                <div class="text-center p-6 bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl shadow-lg border border-slate-700 mb-4 relative overflow-hidden group/card">
                    <div class="absolute top-0 right-0 p-2">
                        <span class="bg-emerald-500/20 text-emerald-400 text-xs font-bold px-2 py-0.5 rounded-full uppercase border border-emerald-500/30">Rank #{{ $rank }}</span>
                    </div>
                    
                    <div class="relative z-10">
                        <div class="text-4xl font-bold text-white mb-1 drop-shadow-sm">{{ number_format($reputation->total_points) }}</div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Elite Score</div>
                        
                        <div class="inline-block px-4 py-1.5 {{ $reputation->level_color }} text-white text-xs font-bold rounded-full shadow-md uppercase tracking-wider border border-gray-100">
                            {{ $reputation->level_name }}
                        </div>
                    </div>

                    {{-- Progress Bar to Next Level --}}
                    <div class="mt-6 w-full bg-white/10 rounded-full h-1 overflow-hidden">
                        <div class="bg-emerald-500 h-full rounded-full shadow-[0_0_10px_rgba(16,185,129,0.5)]" style="width: {{ $reputation->progress_percentage }}%"></div>
                    </div>
                </div>

                <div class="space-y-3 relative z-10">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Aktivitas Terakhir</h3>
                    @foreach($reputationLogs as $log)
                    <div class="flex items-center justify-between text-xs p-2.5 rounded-xl bg-gray-50 border border-gray-100 hover:bg-white transition">
                        <div class="flex flex-col">
                            <span class="font-bold text-gray-700 leading-tight">{{ $log->description }}</span>
                            <span class="text-xs text-gray-400 mt-0.5">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        <span class="font-bold {{ $log->points >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $log->points >= 0 ? '+' : '' }}{{ $log->points }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            @if($showReportCard && $latestReportCard)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hidden lg:block opacity-60 grayscale hover:grayscale-0 transition duration-500">
                <h2 class="font-bold text-gray-800 mb-3 flex items-center gap-2 text-sm">
                    <i class="fas fa-file-invoice text-gray-400"></i> Rapor Terakhir
                </h2>
                <div class="flex items-center justify-between px-2">
                    <div class="text-center">
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-tighter">Peringkat</p>
                        <p class="text-lg font-bold text-gray-800">#{{ $latestReportCard->rank }}</p>
                    </div>
                    <div class="h-8 w-px bg-gray-100"></div>
                    <div class="text-center">
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-tighter">Rata-rata</p>
                        <p class="text-lg font-bold text-gray-800">{{ $latestReportCard->average_score }}</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Quick Links --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h2 class="font-bold text-gray-800 mb-3 flex items-center gap-2 text-sm">
                    <i class="fas fa-bolt text-blue-500"></i> Menu Cepat
                </h2>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('siswa.nilai') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 transition group">
                        <i class="fas fa-chart-bar text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[11px] font-medium text-center">Nilai Saya</span>
                    </a>
                    <a href="{{ route('siswa.absensi') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition group">
                        <i class="fas fa-clipboard-check text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[11px] font-medium text-center">Presensi</span>
                    </a>
                    <a href="{{ route('siswa.tagihan') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 transition group">
                        <i class="fas fa-wallet text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[11px] font-medium text-center">Tagihan</span>
                    </a>
                    <a href="{{ route('siswa.lms.index') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition group">
                        <i class="fas fa-laptop-code text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[11px] font-medium text-center">LMS</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

