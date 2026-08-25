@extends('layouts.siswa')
@section('title', 'Dashboard - Portal Siswa')

@section('content')
<div class="space-y-10">

    {{-- 1. HERO GREETING BAR (ULTRA-SPACIOUS, ELEGANT & AIRY) --}}
    <div class="bg-white rounded-3xl border-2 border-indigo-100 shadow-lg relative overflow-hidden">
        {{-- Rainbow Decorative Stripe --}}
        <div class="h-3.5 w-full bg-gradient-to-r from-rose-500 via-amber-500 via-emerald-500 via-cyan-500 to-purple-600"></div>

        <div class="p-8 sm:p-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center flex-1" style="gap: 2rem;">
                <div class="relative flex-shrink-0" style="margin-right: 1.5rem;">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-3xl overflow-hidden shadow-xl border-4 border-white ring-4 ring-indigo-100 flex-shrink-0 bg-slate-100">
                        <img src="{{ $student->photo_url }}" class="w-full h-full object-cover" alt="{{ $student->full_name }}" onerror="this.onerror=null; this.src='{{ asset('images/default-student.jpg') }}';">
                    </div>
                    <span class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-emerald-500 border-2 border-white flex items-center justify-center text-xs text-white font-black shadow-md" title="Akun Aktif">
                        ✓
                    </span>
                </div>

                <div class="space-y-3 flex-1 min-w-0" style="padding-left: 0.25rem;">
                    <div class="flex flex-wrap items-center gap-2.5" style="margin-bottom: 0.5rem;">
                        <span class="px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-indigo-100 text-indigo-900 border border-indigo-200 shadow-2xs">
                            🎓 Siswa Aktif
                        </span>
                        @if($classroom)
                        <span class="px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-purple-100 text-purple-900 border border-purple-200 shadow-2xs">
                            🏛️ {{ $classroom->class_name }}
                        </span>
                        @endif
                    </div>

                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-900 tracking-normal leading-snug">
                        Hai, {{ $student->full_name }}! 👋
                    </h1>

                    <p class="text-sm sm:text-base text-slate-600 font-semibold flex flex-wrap items-center gap-x-4 gap-y-1.5" style="margin-top: 0.5rem;">
                        <span><i class="fas fa-school text-indigo-500 mr-1.5"></i> {{ $student->school->name ?? 'Perguruan Pembda' }}</span>
                        <span>&bull;</span>
                        <span class="font-mono text-slate-500">NISN: {{ $student->nisn ?: '-' }}</span>
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap sm:flex-col lg:flex-row items-start sm:items-end gap-3.5 flex-shrink-0 pt-2 md:pt-0">
                @if($activeYear)
                <div class="px-5 py-3 rounded-2xl bg-indigo-50 border-2 border-indigo-200/80 text-indigo-900 text-xs font-black flex items-center gap-2.5 shadow-2xs">
                    <i class="fas fa-calendar-alt text-indigo-600 text-sm"></i>
                    <span>TP {{ $activeYear->year }}</span>
                </div>
                @endif
                <div class="px-5 py-3 rounded-2xl bg-emerald-50 border-2 border-emerald-200/80 text-emerald-900 text-xs font-black flex items-center gap-2.5 shadow-2xs">
                    <i class="far fa-clock text-emerald-600 text-sm"></i>
                    <span>{{ now()->translatedFormat('l, d F Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. URGENT ATTENDANCE ALERT (IF NOT CHECKED IN) --}}
    @if(!$todayAttendance || !$todayAttendance->time_out)
    <div class="bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 rounded-3xl p-1.5 shadow-xl text-white">
        <div class="bg-slate-950/20 backdrop-blur-xs p-6 sm:p-8 rounded-[22px] flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="flex items-center gap-6 text-white flex-1" style="gap: 1.75rem;">
                <div class="w-16 h-16 rounded-3xl bg-white/20 border-2 border-white/30 flex items-center justify-center text-3xl shadow-inner flex-shrink-0 animate-bounce" style="margin-right: 1.25rem;">
                    ⏰
                </div>
                <div class="space-y-1.5 flex-1">
                    <h3 class="font-black text-lg text-white leading-tight">Pengingat Presensi Harian Siswa</h3>
                    <p class="text-xs sm:text-sm text-amber-100 font-semibold leading-relaxed">
                        @if(!$todayAttendance)
                            Kamu belum melakukan <b>Absen Masuk</b> hari ini. Yuk catat kehadiranmu agar persentase kehadiran tetap maksimal!
                        @else
                            Kamu sudah absen masuk jam <b>{{ date('H:i', strtotime($todayAttendance->time_in)) }}</b>. Jangan lupa lakukan <b>Absen Pulang</b> saat jam sekolah selesai ya!
                        @endif
                    </p>
                </div>
            </div>
            <a href="{{ route('siswa.absensi') }}" class="px-7 py-3.5 bg-white text-orange-950 hover:bg-amber-100 rounded-2xl font-black text-xs shadow-lg transition transform hover:scale-105 active:scale-95 text-center flex-shrink-0 uppercase tracking-wider flex items-center gap-2.5 border-2 border-white/50">
                <i class="fas fa-fingerprint text-orange-600 text-sm"></i>
                <span>{{ !$todayAttendance ? 'Absen Masuk Sekarang' : 'Absen Pulang' }}</span>
            </a>
        </div>
    </div>
    @endif

    {{-- 3. 4 COLORFUL VIBRANT STAT CARDS (ROOMY & HIGH CONTRAST) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Card 1: Kehadiran (Emerald Theme) --}}
        <div class="bg-gradient-to-br from-emerald-500 to-teal-700 text-white p-7 rounded-3xl shadow-lg border-2 border-emerald-400/50 flex flex-col justify-between hover:scale-[1.02] transition space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black uppercase tracking-wider text-emerald-100">Presensi Kehadiran</span>
                <div class="w-13 h-13 rounded-2xl bg-white text-emerald-600 flex items-center justify-center text-2xl shadow-sm flex-shrink-0" style="margin-left: 1rem;">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
            <div>
                <p class="text-3xl sm:text-4xl font-black text-white tracking-tight">{{ $attendanceData['percentage'] }}%</p>
                <p class="text-xs font-bold text-emerald-100 mt-2 leading-relaxed">
                    {{ $attendanceData['present'] }} Hadir dari {{ $attendanceData['total'] }} Hari Efektif
                </p>
            </div>
        </div>

        {{-- Card 2: Rata-rata Nilai (Indigo Theme) --}}
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 text-white p-7 rounded-3xl shadow-lg border-2 border-blue-400/50 flex flex-col justify-between hover:scale-[1.02] transition space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black uppercase tracking-wider text-blue-100">Rata-Rata Nilai</span>
                <div class="w-13 h-13 rounded-2xl bg-white text-indigo-600 flex items-center justify-center text-2xl shadow-sm flex-shrink-0" style="margin-left: 1rem;">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <div>
                <p class="text-3xl sm:text-4xl font-black text-white tracking-tight">{{ number_format($avgScore, 1) }}</p>
                <p class="text-xs font-bold text-blue-100 mt-2 leading-relaxed">
                    Indeks Prestasi Semester Ini
                </p>
            </div>
        </div>

        {{-- Card 3: Pembda Elite Score (Amber Theme) --}}
        <div class="bg-gradient-to-br from-amber-500 to-orange-600 text-white p-7 rounded-3xl shadow-lg border-2 border-amber-400/50 flex flex-col justify-between hover:scale-[1.02] transition space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black uppercase tracking-wider text-amber-100">Poin Reputasi Elite</span>
                <div class="w-13 h-13 rounded-2xl bg-white text-amber-600 flex items-center justify-center text-2xl shadow-sm flex-shrink-0" style="margin-left: 1rem;">
                    <i class="fas fa-crown"></i>
                </div>
            </div>
            <div>
                <p class="text-3xl sm:text-4xl font-black text-white tracking-tight">{{ number_format($reputation->total_points ?? 0) }} <span class="text-sm font-bold text-amber-200">Poin</span></p>
                <p class="text-xs font-bold text-amber-100 mt-2 leading-relaxed">
                    Peringkat #{{ $rank ?? 1 }} &bull; {{ $reputation->level_name ?? 'Pemula' }}
                </p>
            </div>
        </div>

        {{-- Card 4: Jadwal / Keuangan (Purple/Rose Theme) --}}
        <div class="bg-gradient-to-br from-purple-600 to-pink-600 text-white p-7 rounded-3xl shadow-lg border-2 border-purple-400/50 flex flex-col justify-between hover:scale-[1.02] transition space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black uppercase tracking-wider text-purple-100">Status Pembayaran</span>
                <div class="w-13 h-13 rounded-2xl bg-white text-purple-600 flex items-center justify-center text-2xl shadow-sm flex-shrink-0" style="margin-left: 1rem;">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
            <div>
                @if($totalOutstanding > 0)
                <p class="text-2xl sm:text-3xl font-black text-white tracking-tight">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</p>
                <p class="text-xs font-bold text-pink-200 mt-2 leading-relaxed">Ada tagihan belum lunas</p>
                @else
                <p class="text-2xl sm:text-3xl font-black text-white tracking-tight">Lunas 100%</p>
                <p class="text-xs font-bold text-emerald-200 mt-2 leading-relaxed">Bebas tunggakan SPP</p>
                @endif
            </div>
        </div>
    </div>

    {{-- 4. JADWAL PELAJARAN HARI INI (ULTRA-SPACIOUS, NO-CLIP, ATTRACTIVE & BEAUTIFULLY SPACED) --}}
    <div class="bg-white rounded-3xl border-2 border-indigo-100 shadow-xl overflow-hidden">
        {{-- Header Bar with Generous Padding --}}
        <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-purple-800 p-8 sm:p-10 text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div class="flex items-center gap-6" style="gap: 1.75rem;">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-3xl bg-white text-indigo-700 flex items-center justify-center text-3xl shadow-lg flex-shrink-0 font-black" style="margin-right: 1.5rem;">
                    📅
                </div>
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2.5" style="margin-bottom: 0.35rem;">
                        <span class="px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-amber-400 text-slate-950 shadow-xs">
                            {{ now()->translatedFormat('l, d F Y') }}
                        </span>
                        @if($currentSchedule)
                        <span class="px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-400 text-slate-950 animate-pulse shadow-xs">
                            🟢 Sedang Berlangsung
                        </span>
                        @endif
                    </div>
                    <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                        Jadwal Pelajaran Hari Ini
                    </h2>
                </div>
            </div>

            <div class="flex items-center gap-4 pt-2 sm:pt-0">
                <span class="text-xs font-bold text-indigo-200 hidden sm:inline">Total {{ $todaySchedules->count() }} Sesi Pelajaran</span>
                <a href="{{ route('siswa.jadwal') }}" class="px-5 py-3 bg-white text-indigo-900 hover:bg-slate-100 rounded-2xl font-black text-xs transition shadow-md flex items-center gap-2 active:scale-95">
                    <span>Lihat Jadwal Lengkap</span>
                    <i class="fas fa-arrow-right text-[11px]"></i>
                </a>
            </div>
        </div>

        {{-- Interactive Schedule Cards Grid with Guaranteed Generous Inset --}}
        <div class="p-8 sm:p-10 space-y-8">
            @if($groupedTodaySchedules->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3" style="gap: 2rem;">
                    @php
                        $colorPalettes = [
                            ['border' => 'border-blue-300', 'bg' => 'from-blue-500 to-indigo-600', 'pill' => 'bg-blue-100 text-blue-900 border-blue-200', 'icon' => '📐', 'tag' => 'blue'],
                            ['border' => 'border-purple-300', 'bg' => 'from-purple-500 to-pink-600', 'pill' => 'bg-purple-100 text-purple-900 border-purple-200', 'icon' => '📖', 'tag' => 'purple'],
                            ['border' => 'border-emerald-300', 'bg' => 'from-emerald-500 to-teal-600', 'pill' => 'bg-emerald-100 text-emerald-900 border-emerald-200', 'icon' => '🧪', 'tag' => 'emerald'],
                            ['border' => 'border-amber-300', 'bg' => 'from-amber-500 to-orange-600', 'pill' => 'bg-amber-100 text-amber-900 border-amber-200', 'icon' => '💻', 'tag' => 'amber'],
                            ['border' => 'border-rose-300', 'bg' => 'from-rose-500 to-red-600', 'pill' => 'bg-rose-100 text-rose-900 border-rose-200', 'icon' => '⚽', 'tag' => 'rose'],
                            ['border' => 'border-cyan-300', 'bg' => 'from-cyan-500 to-blue-600', 'pill' => 'bg-cyan-100 text-cyan-900 border-cyan-200', 'icon' => '🎨', 'tag' => 'cyan'],
                        ];
                        $cIdx = 0;
                    @endphp

                    @foreach($groupedTodaySchedules as $timeKey => $schedulesAtTime)
                        @php
                            $first = $schedulesAtTime->first();
                            $timeParts = explode(' - ', $timeKey);
                            $sStart = isset($timeParts[0]) ? \Carbon\Carbon::parse($timeParts[0])->format('H:i') : '--:--';
                            $sEnd = isset($timeParts[1]) ? \Carbon\Carbon::parse($timeParts[1])->format('H:i') : '--:--';
                            
                            $isCurrent = false;
                            $isNext = false;
                            foreach($schedulesAtTime as $s) {
                                if ($currentSchedule && $currentSchedule->id === $s->id) $isCurrent = true;
                                if ($nextSchedule && $nextSchedule->id === $s->id) $isNext = true;
                            }
                            $isPast = $currentTime > $sEnd;
                            $palette = $colorPalettes[$cIdx % count($colorPalettes)];
                            $cIdx++;
                        @endphp

                        <div class="rounded-3xl border-2 {{ $isCurrent ? 'border-emerald-500 ring-4 ring-emerald-100 shadow-xl bg-white scale-[1.02]' : ($isNext ? 'border-amber-400 ring-2 ring-amber-100 shadow-md bg-white' : ($isPast ? 'border-slate-200 bg-slate-50/70 opacity-80' : 'border-indigo-100 shadow-sm bg-white hover:border-indigo-300 hover:shadow-lg')) }} flex flex-col justify-between transition duration-200" style="padding: 1.75rem;">
                            
                            {{-- Top Status Pill & Time Badge (WITH GUARANTEED MARGIN FROM BORDER) --}}
                            <div class="flex items-center justify-between gap-3" style="margin-bottom: 1.25rem; padding-bottom: 0.85rem; border-bottom: 2px solid #f1f5f9;">
                                <div>
                                    <span class="px-3.5 py-1.5 rounded-xl bg-slate-900 text-white font-black text-xs inline-flex items-center gap-2 shadow-sm">
                                        <i class="far fa-clock text-amber-400"></i>
                                        <span>{{ $sStart }} - {{ $sEnd }}</span>
                                    </span>
                                </div>

                                <div>
                                    @if($isCurrent)
                                    <span class="px-3 py-1.5 rounded-full text-[11px] font-black uppercase tracking-wider bg-emerald-500 text-white shadow-sm inline-flex items-center gap-1.5 animate-pulse">
                                        <i class="fas fa-circle text-[6px]"></i> Berlangsung
                                    </span>
                                    @elseif($isNext)
                                    <span class="px-3 py-1.5 rounded-full text-[11px] font-black uppercase tracking-wider bg-amber-500 text-white shadow-sm inline-flex items-center gap-1">
                                        ⏳ Berikutnya
                                    </span>
                                    @elseif($isPast)
                                    <span class="px-3 py-1.5 rounded-full text-[11px] font-black uppercase tracking-wider bg-slate-200 text-slate-700 inline-flex items-center gap-1">
                                        ✓ Selesai
                                    </span>
                                    @else
                                    <span class="px-3 py-1.5 rounded-full text-[11px] font-black uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-200 inline-flex items-center gap-1">
                                        Mendatang
                                    </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Subject Content with Wide Margins & Inset --}}
                            <div class="space-y-4" style="margin-top: 0.5rem; margin-bottom: 0.5rem;">
                                @foreach($schedulesAtTime as $s)
                                <div class="flex items-start" style="gap: 1.25rem;">
                                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br {{ $palette['bg'] }} text-white flex items-center justify-center text-3xl shadow-md flex-shrink-0" style="margin-right: 1.25rem;">
                                        {{ $palette['icon'] }}
                                    </div>
                                    <div class="min-w-0 flex-1" style="padding-top: 0.15rem;">
                                        <h3 class="text-base sm:text-lg font-black text-slate-900 leading-snug group-hover:text-indigo-600 transition truncate" style="margin-bottom: 0.35rem;">
                                            {{ $s->subject->subject_name ?? ($s->subject->name ?? 'Mata Pelajaran') }}
                                        </h3>
                                        <p class="text-xs text-slate-600 font-bold flex items-center gap-1.5 truncate">
                                            <i class="fas fa-chalkboard-teacher text-indigo-500"></i>
                                            <span>{{ $s->teacher->user->name ?? ($s->teacher->full_name ?? 'Guru Pengampu') }}</span>
                                        </p>
                                    </div>
                                </div>

                                {{-- Room & Class info pill (CLEANLY INSET) --}}
                                <div class="flex items-center justify-between text-xs bg-slate-50 rounded-2xl border border-slate-200" style="padding: 0.75rem 1rem; margin-top: 1rem;">
                                    <span class="font-bold text-slate-700 flex items-center gap-2">
                                        <i class="fas fa-door-open text-rose-500"></i>
                                        <span>Ruang: {{ $s->room ?: 'Kelas ' . ($classroom->class_name ?? 'Utama') }}</span>
                                    </span>
                                    <span class="font-black text-indigo-800 bg-indigo-100 px-2.5 py-1 rounded-xl">
                                        {{ $s->subject->code ?? 'MAPEL' }}
                                    </span>
                                </div>
                                @endforeach
                            </div>

                            {{-- Action Footer (GUARANTEED MARGIN FROM BOTTOM & LEFT BORDER) --}}
                            <div class="flex items-center justify-between gap-3" style="margin-top: 1.25rem; padding-top: 0.85rem; border-top: 2px solid #f1f5f9;">
                                <a href="{{ route('siswa.lms.index') }}" class="text-xs font-black text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-2 transition">
                                    <i class="fas fa-book-open"></i>
                                    <span>Materi & Tugas</span>
                                </a>

                                @if($isCurrent)
                                <a href="{{ route('siswa.lms.index') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black shadow-md transition active:scale-95 inline-flex items-center gap-1.5">
                                    <span>Masuk LMS</span> &rarr;
                                </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-16 text-center bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200 space-y-4">
                    <div class="w-20 h-20 bg-white text-slate-400 rounded-full flex items-center justify-center mx-auto text-4xl shadow-sm">
                        ☕
                    </div>
                    <div class="space-y-1">
                        <h3 class="font-black text-slate-800 text-lg">Tidak Ada Jadwal Pelajaran Hari Ini</h3>
                        <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto font-medium leading-relaxed">Hari ini tidak ada sesi belajar mengajar di kelas. Manfaatkan waktu luang untuk belajar mandiri di LMS atau ikuti kegiatan ekstrakurikuler!</p>
                    </div>
                    <div class="pt-3">
                        <a href="{{ route('siswa.lms.index') }}" class="px-6 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-xs font-black shadow-md inline-flex items-center gap-2 transition">
                            <i class="fas fa-laptop-code"></i>
                            <span>Buka Portal E-Learning LMS</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- 5. WIDGET DNA POTENSI & KARIR SISWA 360° (VIBRANT BENTO & SPACIOUS) --}}
    @if(isset($dnaAnalysis))
    <div class="bg-white rounded-3xl border-2 border-indigo-100 shadow-lg relative overflow-hidden space-y-5">
        {{-- Rainbow Header Stripe --}}
        <div class="h-3 w-full bg-gradient-to-r from-purple-600 via-pink-500 to-amber-500"></div>

        <div class="p-8 sm:p-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-8">
            <div class="space-y-4 max-w-2xl">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="px-4 py-1.5 bg-gradient-to-r {{ $dnaAnalysis['archetype']['color'] }} text-white rounded-full text-xs font-black uppercase tracking-wider inline-flex items-center gap-2 shadow-sm">
                        <i class="fas {{ $dnaAnalysis['archetype']['badge_icon'] }}"></i> {{ $dnaAnalysis['archetype']['title'] }}
                    </span>
                    <span class="text-xs font-black text-indigo-900 bg-indigo-50 border border-indigo-200 px-3.5 py-1.5 rounded-full shadow-2xs">
                        ✨ Akurasi Profil: {{ $dnaAnalysis['confidence_score'] }}%
                    </span>
                </div>

                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    DNA Potensi Belajar & Minat Karier 360°
                </h2>

                <p class="text-sm text-slate-600 font-semibold leading-relaxed">
                    "{{ $dnaAnalysis['archetype']['tagline'] }}" &mdash; {{ $dnaAnalysis['archetype']['description'] }}
                </p>

                {{-- 6 Dimension Meters (Vibrant Colorful Pills) --}}
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-3.5 pt-3 text-xs">
                    <div class="bg-blue-50 border-2 border-blue-200 rounded-2xl p-4 text-center shadow-2xs space-y-1">
                        <p class="text-[10px] text-blue-700 font-black uppercase">Logika</p>
                        <p class="text-xl font-black text-blue-950">{{ $dnaAnalysis['scores']['logic'] }}</p>
                    </div>
                    <div class="bg-emerald-50 border-2 border-emerald-200 rounded-2xl p-4 text-center shadow-2xs space-y-1">
                        <p class="text-[10px] text-emerald-700 font-black uppercase">Bahasa</p>
                        <p class="text-xl font-black text-emerald-950">{{ $dnaAnalysis['scores']['communication'] }}</p>
                    </div>
                    <div class="bg-indigo-50 border-2 border-indigo-200 rounded-2xl p-4 text-center shadow-2xs space-y-1">
                        <p class="text-[10px] text-indigo-700 font-black uppercase">Vokasi</p>
                        <p class="text-xl font-black text-indigo-950">{{ $dnaAnalysis['scores']['technical'] }}</p>
                    </div>
                    <div class="bg-amber-50 border-2 border-amber-200 rounded-2xl p-4 text-center shadow-2xs space-y-1">
                        <p class="text-[10px] text-amber-700 font-black uppercase">Sosial</p>
                        <p class="text-xl font-black text-amber-950">{{ $dnaAnalysis['scores']['social'] }}</p>
                    </div>
                    <div class="bg-purple-50 border-2 border-purple-200 rounded-2xl p-4 text-center shadow-2xs space-y-1">
                        <p class="text-[10px] text-purple-700 font-black uppercase">Kreatif</p>
                        <p class="text-xl font-black text-purple-950">{{ $dnaAnalysis['scores']['creative'] }}</p>
                    </div>
                    <div class="bg-rose-50 border-2 border-rose-200 rounded-2xl p-4 text-center shadow-2xs space-y-1">
                        <p class="text-[10px] text-rose-700 font-black uppercase">Disiplin</p>
                        <p class="text-xl font-black text-rose-950">{{ $dnaAnalysis['scores']['discipline'] }}</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row lg:flex-col gap-3.5 w-full lg:w-auto flex-shrink-0 pt-2 lg:pt-0">
                <a href="{{ route('siswa.dna.index') }}" class="px-7 py-4 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-2xl font-black text-xs shadow-md transition text-center flex items-center justify-center gap-2 active:scale-95">
                    <i class="fas fa-radar"></i>
                    <span>Buka Radar DNA 360°</span>
                </a>
                <a href="{{ route('siswa.dna.pdf') }}" class="px-7 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-2xl font-black text-xs transition text-center flex items-center justify-center gap-2">
                    <i class="fas fa-file-pdf text-rose-600"></i>
                    <span>Unduh Sertifikat PDF</span>
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- 6. DUA KOLOM: EKSTRAKURIKULER & LMS + REPUTASI --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- KOLOM KIRI (2 SPAN): EKSTRAKURIKULER & RIWAYAT PRESENSI --}}
        <div class="lg:col-span-2 space-y-8">
            
            {{-- Ekstrakurikuler --}}
            @php
                $myEkskuls = $student->extracurricularMembers()->where('status', 'approved')->with('extracurricular')->get();
            @endphp
            <div class="bg-white rounded-3xl border-2 border-indigo-100 p-8 sm:p-10 shadow-sm space-y-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-5" style="gap: 1.5rem;">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white flex items-center justify-center text-3xl shadow-sm flex-shrink-0" style="margin-right: 1.25rem;">
                            🎨
                        </div>
                        <div class="space-y-1">
                            <h3 class="font-black text-slate-900 text-lg">Ekstrakurikuler & Pembinaan Karakter</h3>
                            <p class="text-xs sm:text-sm text-slate-600 font-semibold">Unit kegiatan non-akademik dan ruang diskusi Pembda Space</p>
                        </div>
                    </div>
                    <a href="{{ route('siswa.ekskul.index') }}" class="text-xs font-black text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5">
                        <span>Katalog Ekskul</span>
                        <i class="fas fa-arrow-right text-[11px]"></i>
                    </a>
                </div>

                @if($myEkskuls->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    @foreach($myEkskuls as $m)
                    @php $e = $m->extracurricular; @endphp
                    <div class="p-5 bg-gradient-to-br from-slate-50 to-indigo-50/40 rounded-3xl border-2 border-indigo-100 flex items-center justify-between gap-4 shadow-2xs">
                        <div class="flex items-center gap-4 min-w-0" style="gap: 1.25rem;">
                            <div class="w-13 h-13 rounded-2xl bg-white border border-indigo-200 flex items-center justify-center text-2xl flex-shrink-0 shadow-xs" style="margin-right: 1rem;">
                                {{ $e->display_icon }}
                            </div>
                            <div class="min-w-0 space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-0.5 rounded-full text-[10px] font-black text-white bg-gradient-to-r {{ $m->role_badge_color }}">
                                        {{ $m->role_label }}
                                    </span>
                                    @if($m->section)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-purple-100 text-purple-900 border border-purple-200">
                                        🎺 {{ $m->section }}
                                    </span>
                                    @endif
                                </div>
                                <p class="font-black text-slate-900 text-xs sm:text-sm truncate">{{ $e->name }}</p>
                            </div>
                        </div>
                        @if($e->forum_group_id)
                        <a href="{{ route('forum.index', ['group' => $e->forum_group_id]) }}" class="px-4 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl text-xs font-black shadow-xs flex items-center gap-1.5 flex-shrink-0 active:scale-95" title="Buka Kanal Space">
                            <i class="fas fa-comments"></i>
                            <span>Space</span>
                        </a>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="p-6 bg-amber-50/80 rounded-3xl border border-amber-200 flex flex-col sm:flex-row items-center justify-between gap-5 text-xs sm:text-sm">
                    <div class="flex items-center gap-4 text-amber-950">
                        <span class="text-3xl">⚜️</span>
                        <span class="font-semibold leading-relaxed">Kamu belum memilih unit ekstrakurikuler. Bergabunglah untuk mendapatkan reward <b>+15 Poin Reputasi</b>!</span>
                    </div>
                    <a href="{{ route('siswa.ekskul.index') }}" class="px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-2xl font-black flex-shrink-0 transition shadow-sm active:scale-95 text-xs">
                        Pilih Ekskul Sekarang
                    </a>
                </div>
                @endif
            </div>

            {{-- Riwayat Presensi Terakhir --}}
            <div class="bg-white rounded-3xl border-2 border-slate-200 shadow-sm overflow-hidden">
                <div class="px-8 py-5 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                    <h3 class="font-black text-slate-900 flex items-center gap-2.5 text-base">
                        <i class="fas fa-history text-emerald-600"></i> Riwayat Presensi Terakhir
                    </h3>
                    <a href="{{ route('siswa.absensi') }}" class="text-xs font-black text-indigo-600 hover:text-indigo-800">Detail Lengkap &rarr;</a>
                </div>
                <div class="p-8">
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="bg-slate-100 text-slate-800 font-black uppercase text-[11px] tracking-wider">
                                    <th class="p-4 text-left">Hari / Tanggal</th>
                                    <th class="p-4 text-center">Status</th>
                                    <th class="p-4 text-center">Jam Masuk</th>
                                    <th class="p-4 text-center">Jam Pulang</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($attendanceHistory as $att)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="p-4 font-black text-slate-900">
                                        {{ $att->date->translatedFormat('l, d M Y') }}
                                    </td>
                                    <td class="p-4 text-center">
                                        @php
                                            $statusBadge = match($att->status) {
                                                'hadir' => 'bg-emerald-100 text-emerald-950 border-emerald-300',
                                                'sakit' => 'bg-amber-100 text-amber-950 border-amber-300',
                                                'izin' => 'bg-blue-100 text-blue-950 border-blue-300',
                                                'alpha' => 'bg-rose-100 text-rose-950 border-rose-300',
                                                default => 'bg-slate-100 text-slate-800'
                                            };
                                        @endphp
                                        <span class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-full text-[11px] font-black uppercase border {{ $statusBadge }}">
                                            {{ $att->status }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-center font-mono font-bold text-slate-700">
                                        {{ $att->time_in ? date('H:i', strtotime($att->time_in)) : '--:--' }}
                                    </td>
                                    <td class="p-4 text-center font-mono font-bold text-slate-700">
                                        {{ $att->time_out ? date('H:i', strtotime($att->time_out)) : '--:--' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-slate-400 font-medium">Belum ada riwayat kehadiran tercatat.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN (1 SPAN): LMS, REPUTASI, & MENU CEPAT --}}
        <div class="space-y-8">
            
            {{-- Kursus LMS --}}
            <div class="bg-white rounded-3xl border-2 border-indigo-100 p-8 shadow-sm space-y-5">
                <div class="flex items-center justify-between">
                    <h3 class="font-black text-slate-900 flex items-center gap-2.5 text-base">
                        <i class="fas fa-book-reader text-indigo-600"></i> Kursus Saya (LMS)
                    </h3>
                    <a href="{{ route('siswa.lms.index') }}" class="text-xs font-black text-indigo-600 hover:text-indigo-800">Semua</a>
                </div>

                <div class="space-y-4">
                    @forelse($courses as $course)
                    @php $progress = $courseProgress[$course->id] ?? 0; @endphp
                    <a href="{{ route('siswa.lms.show', $course->id) }}" class="block p-4 rounded-2xl bg-gradient-to-br from-slate-50 to-indigo-50/30 border border-indigo-100 hover:border-indigo-300 hover:shadow-md transition">
                        <h4 class="text-xs sm:text-sm font-black text-slate-900 truncate mb-2.5">{{ $course->name }}</h4>
                        <div class="w-full bg-slate-200 rounded-full h-2 mb-2 overflow-hidden">
                            <div class="h-full bg-indigo-600 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                        </div>
                        <div class="flex justify-between items-center text-xs font-bold text-slate-500">
                            <span>{{ $course->materials_count }} Materi</span>
                            <span class="text-indigo-700 font-black">{{ $progress }}%</span>
                        </div>
                    </a>
                    @empty
                    <p class="text-center py-6 text-xs text-slate-400 font-medium">Belum ada kursus LMS aktif.</p>
                    @endforelse
                </div>
            </div>

            {{-- Pembda Elite Leaderboard & Activity --}}
            <div class="bg-white rounded-3xl border-2 border-amber-200 p-8 shadow-sm space-y-5">
                <div class="flex items-center justify-between">
                    <h3 class="font-black text-slate-900 flex items-center gap-2 text-sm uppercase tracking-wider">
                        <i class="fas fa-crown text-amber-500"></i> Pembda Elite
                    </h3>
                    <a href="{{ route('reputation.leaderboard') }}" class="text-xs font-black text-amber-700 hover:text-amber-900">Papan Skor &rarr;</a>
                </div>

                <div class="text-center p-7 bg-gradient-to-br from-amber-500 via-orange-500 to-amber-600 rounded-3xl shadow-md text-white space-y-2.5">
                    <span class="bg-white/20 text-white text-[11px] font-black px-3.5 py-1 rounded-full uppercase">Peringkat #{{ $rank ?? 1 }}</span>
                    <div class="text-3xl sm:text-4xl font-black text-white">{{ number_format($reputation->total_points ?? 0) }} Poin</div>
                    <div class="inline-block px-4 py-1.5 bg-white text-slate-950 text-[11px] font-black rounded-full uppercase shadow-xs">
                        {{ $reputation->level_name ?? 'Level Siswa' }}
                    </div>
                </div>

                {{-- Aktivitas Terakhir --}}
                <div class="space-y-2.5">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Aktivitas Poin Terakhir</p>
                    @foreach($reputationLogs as $log)
                    <div class="flex items-center justify-between text-xs p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-slate-800 text-xs truncate">{{ $log->description }}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $log->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="font-black text-xs ml-3 {{ $log->points >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $log->points >= 0 ? '+' : '' }}{{ $log->points }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Menu Cepat --}}
            <div class="bg-white rounded-3xl border-2 border-slate-200 p-8 shadow-sm space-y-4">
                <h3 class="font-black text-slate-900 flex items-center gap-2.5 text-base">
                    <i class="fas fa-bolt text-indigo-600"></i> Pintasan Menu Siswa
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('siswa.nilai') }}" class="p-4 bg-blue-50 hover:bg-blue-100 text-blue-900 rounded-2xl transition text-center font-bold text-xs flex flex-col items-center gap-2 shadow-2xs">
                        <i class="fas fa-chart-bar text-xl text-blue-600"></i>
                        <span>Nilai Saya</span>
                    </a>
                    <a href="{{ route('siswa.absensi') }}" class="p-4 bg-emerald-50 hover:bg-emerald-100 text-emerald-900 rounded-2xl transition text-center font-bold text-xs flex flex-col items-center gap-2 shadow-2xs">
                        <i class="fas fa-clipboard-check text-xl text-emerald-600"></i>
                        <span>Presensi</span>
                    </a>
                    <a href="{{ route('siswa.tagihan') }}" class="p-4 bg-rose-50 hover:bg-rose-100 text-rose-900 rounded-2xl transition text-center font-bold text-xs flex flex-col items-center gap-2 shadow-2xs">
                        <i class="fas fa-wallet text-xl text-rose-600"></i>
                        <span>Tagihan SPP</span>
                    </a>
                    <a href="{{ route('siswa.lms.index') }}" class="p-4 bg-purple-50 hover:bg-purple-100 text-purple-900 rounded-2xl transition text-center font-bold text-xs flex flex-col items-center gap-2 shadow-2xs">
                        <i class="fas fa-laptop-code text-xl text-purple-600"></i>
                        <span>Portal LMS</span>
                    </a>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
