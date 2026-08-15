@extends('mobile.layouts.app')

@section('title', 'Beranda Playful - PembdaHUB Mobile Pro')

@section('content')
<div class="space-y-5 pt-2">
    <!-- Hero Banner Card (Playful 3D Clay Banner) -->
    <div class="clay-blue p-4 relative overflow-hidden">
        <div class="flex items-center space-x-3.5 relative z-10">
            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-14 h-14 rounded-2xl object-cover border-2 border-white/80 shadow-md bg-white shrink-0">

            <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-2 mb-0.5">
                    @php $activeRole = session('active_role', $user->role); @endphp
                    <span class="px-2.5 py-0.5 rounded-full bg-white/30 text-white text-[9px] font-black tracking-wide uppercase border border-white/40 shadow-xs backdrop-blur-sm shrink-0">
                        {{ strtoupper($activeRole) }}
                    </span>
                    @if($student && $student->school)
                        <span class="text-[10px] text-blue-100 font-extrabold truncate">{{ $student->school->name }}</span>
                    @elseif($teacher && $teacher->school)
                        <span class="text-[10px] text-blue-100 font-extrabold truncate">{{ $teacher->school->name }}</span>
                    @endif
                </div>

                <h2 class="text-base font-black text-white truncate leading-tight tracking-tight">Halo, {{ strtok($user->name, ' ') }}! 🚀</h2>

                @if($student)
                    @php
                        $studentClassroom = $classroom ?? $student->currentClassroom()->first();
                        $className = $studentClassroom->name ?? ($studentClassroom->class_name ?? '-');
                        $studentPoints = $user->reputation->total_points ?? ($student->reputation_points ?? 0);
                    @endphp
                    <div class="flex items-center flex-wrap gap-x-2 gap-y-0.5 mt-1 text-[11px] font-extrabold text-blue-100/90 leading-none">
                        <span class="truncate">NISN: {{ $student->nisn ?? $student->nis ?? '-' }}</span>
                        <span class="text-white/60">•</span>
                        <span class="truncate text-white">Kelas: {{ $className }}</span>
                        <span class="text-white/60">•</span>
                        <span class="text-amber-300 font-black"><i class="fa-solid fa-star text-amber-300 text-[10px]"></i> {{ number_format($studentPoints) }} Poin</span>
                    </div>
                @elseif($teacher)
                    <p class="text-xs text-blue-100/90 mt-0.5 font-bold truncate">
                        NIP: {{ $teacher->nip ?? '-' }} • Guru Pengampu
                    </p>
                @else
                    <p class="text-xs text-blue-100/90 mt-0.5 font-bold truncate">
                        {{ $user->email }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- 🌟 Top Stories Bar: Kanal & Squad Pembda Space -->
    <div class="space-y-1.5 pt-1">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-[11px] font-black text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Space Channel & Squad</span>
            </h3>
            <a href="{{ route('mobile.space.index') }}" class="text-[10px] font-extrabold text-purple-600 hover:text-purple-700 flex items-center gap-1">
                <span>Buka Space</span> <i class="fa-solid fa-chevron-right text-[8px]"></i>
            </a>
        </div>

        <div class="flex items-center gap-3 overflow-x-auto pb-2 pt-1 px-1 no-scrollbar scroll-smooth">
            <!-- Lobi Utama Bubble -->
            <a href="{{ route('mobile.space.index') }}" class="flex flex-col items-center gap-1 shrink-0 group">
                <div class="w-13 h-13 rounded-2xl p-0.5 bg-gradient-to-tr from-purple-600 via-indigo-500 to-blue-500 shadow-md group-hover:scale-105 transition">
                    <div class="w-full h-full rounded-[14px] bg-slate-900 flex items-center justify-center text-xl text-white font-black">
                        🏛️
                    </div>
                </div>
                <span class="text-[10px] font-black text-slate-800 tracking-tight max-w-[64px] truncate text-center">Lobi Utama</span>
            </a>

            @if(isset($spaceGroups) && count($spaceGroups) > 0)
                @foreach($spaceGroups as $grp)
                    @php
                        $isNew = $grp->latestThread && $grp->latestThread->created_at && $grp->latestThread->created_at->gt(now()->subDays(2));
                        $grpIcon = match($grp->type ?? '') {
                            'classroom' => '👥',
                            'broadcast' => '📢',
                            'extracurricular' => '🎨',
                            'subject' => '📖',
                            default => '💬'
                        };
                    @endphp
                    <a href="{{ route('mobile.space.group.show', $grp->id) }}" class="flex flex-col items-center gap-1 shrink-0 group relative">
                        <div class="w-13 h-13 rounded-2xl p-0.5 {{ $isNew ? 'bg-gradient-to-tr from-pink-500 via-rose-500 to-amber-400 animate-pulse' : 'bg-slate-300' }} shadow-md group-hover:scale-105 transition">
                            <div class="w-full h-full rounded-[14px] bg-white flex items-center justify-center text-xl text-slate-800 font-black border border-slate-100">
                                {{ $grpIcon }}
                            </div>
                        </div>
                        @if($isNew)
                            <span class="absolute top-0 right-0 w-3 h-3 bg-rose-500 border-2 border-white rounded-full"></span>
                        @endif
                        <span class="text-[10px] font-black text-slate-800 tracking-tight max-w-[64px] truncate text-center">{{ $grp->name }}</span>
                    </a>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Progress Tracking Real Card (Clay Progress Bar) -->
    @if($activeRole === 'siswa')
    @php
        $progOverall = $studentProgress['overall'] ?? 0;
        $progAttendance = $studentProgress['attendance_rate'] ?? 0;
        $progTask = $studentProgress['task_rate'] ?? 0;
        $totalAssign = $studentProgress['total_assignments'] ?? 0;
        $submittedAssign = $studentProgress['submitted_assignments'] ?? 0;
        $progCaption = $studentProgress['caption'] ?? 'Semangat terus dalam belajar dan pertahankan kehadiranmu! 🌟';
    @endphp
    <div class="clay-card p-5 space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-rocket text-orange-500"></i> Progres Belajar & Kehadiran
            </h3>
            <span class="text-xs font-black text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-200">
                {{ $progOverall }}% Selesai
            </span>
        </div>

        <!-- 3D Clay Progress Bar -->
        <div class="w-full bg-slate-100 rounded-full h-5 p-1 shadow-inner relative overflow-hidden border border-slate-200">
            <div class="bg-gradient-to-r from-orange-400 via-amber-400 to-yellow-400 h-full rounded-full transition-all duration-500 shadow-md relative" style="width: {{ max(6, $progOverall) }}%">
                <div class="absolute right-1 top-0 bottom-0 flex items-center">
                    <span class="text-[9px] font-black text-white px-1">🚀</span>
                </div>
            </div>
        </div>

        <!-- Detail Breakdown Badges -->
        <div class="flex items-center justify-between text-[10px] font-bold text-slate-500 pt-0.5 px-0.5">
            <span class="flex items-center gap-1">
                <i class="fa-solid fa-clipboard-user text-emerald-500"></i> Kehadiran: <strong class="text-slate-800">{{ $progAttendance }}%</strong>
            </span>
            @if($totalAssign > 0)
                <span class="flex items-center gap-1">
                    <i class="fa-solid fa-book-bookmark text-purple-500"></i> Tugas: <strong class="text-slate-800">{{ $submittedAssign }}/{{ $totalAssign }} Selesai</strong>
                </span>
            @else
                <span class="text-slate-400 font-semibold">Tugas Belum Ada</span>
            @endif
        </div>

        <p class="text-[11px] font-bold text-slate-600 leading-relaxed">{{ $progCaption }}</p>
    </div>
    @elseif(in_array($activeRole, ['guru', 'pegawai', 'superadmin', 'admin_sekolah', 'kepala_sekolah', 'ketua_yayasan']))
    @php
        $tOverall = $teacherProgress['overall'] ?? 100;
        $tAttendance = $teacherProgress['attendance_rate'] ?? 100;
        $tClassesToday = $teacherProgress['classes_today'] ?? 0;
        $tPendingAssign = $teacherProgress['pending_assignments'] ?? 0;
        $tCaption = $teacherProgress['caption'] ?? 'Dedikasi Anda sangat luar biasa dalam membimbing generasi penerus Pembda! 👨‍🏫🌟';
    @endphp
    <div class="clay-card p-5 space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-chalkboard-user text-purple-600"></i> Aktivitas Mengajar & Presensi Guru
            </h3>
            <span class="text-xs font-black text-purple-700 bg-purple-50 px-2.5 py-0.5 rounded-full border border-purple-200">
                {{ $tOverall }}% Optimal
            </span>
        </div>

        <!-- 3D Clay Progress Bar for Teacher -->
        <div class="w-full bg-slate-100 rounded-full h-5 p-1 shadow-inner relative overflow-hidden border border-slate-200">
            <div class="bg-gradient-to-r from-purple-500 via-indigo-500 to-blue-500 h-full rounded-full transition-all duration-500 shadow-md relative" style="width: {{ max(6, $tOverall) }}%">
                <div class="absolute right-1 top-0 bottom-0 flex items-center">
                    <span class="text-[9px] font-black text-white px-1">✨</span>
                </div>
            </div>
        </div>

        <!-- Detail Breakdown Badges -->
        <div class="flex items-center justify-between text-[10px] font-bold text-slate-500 pt-0.5 px-0.5">
            <span class="flex items-center gap-1">
                <i class="fa-solid fa-calendar-day text-purple-600"></i> Jadwal Hari Ini: <strong class="text-slate-800">{{ $tClassesToday }} Sesi</strong>
            </span>
            <span class="flex items-center gap-1">
                <i class="fa-solid fa-clipboard-check text-amber-500"></i> Periksa Tugas: <strong class="{{ $tPendingAssign > 0 ? 'text-rose-600 font-black' : 'text-slate-800' }}">{{ $tPendingAssign }} Menunggu</strong>
            </span>
        </div>

        <p class="text-[11px] font-bold text-slate-600 leading-relaxed">{{ $tCaption }}</p>
    </div>
    @endif

    <!-- Quick Access Grid (Playful 3D Claymorphism Buttons) -->
    <div>
        <div class="flex items-center justify-between mb-3 px-1">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">Modul PembdaHUB</h3>
            <span class="text-[10px] font-black text-blue-600">Akses Cepat 3D</span>
        </div>
        
        @php 
            $activeRole = session('active_role', $user->role); 
            $siswaSchoolType = strtoupper($student?->school?->type ?? '');
            $siswaGradeLevel = $student?->currentClassroom()?->first()?->grade_level ?? $student?->grade_level;
            $isKelasXII = ($siswaGradeLevel == 12);
            $showPkl = ($siswaSchoolType === 'SMK' && $isKelasXII);
        @endphp

        @if($activeRole === 'siswa')
        <div class="grid grid-cols-4 gap-3">
            <!-- 💬 PEMBDA SPACE (Primary Engine - Highlight Clay Purple) -->
            <a href="{{ route('mobile.space.index') }}" class="clay-purple p-3.5 text-center flex flex-col items-center group transition active:scale-95 relative overflow-hidden border-2 border-purple-400 shadow-lg">
                <div class="absolute top-1 right-1 px-1.5 py-0.2 rounded-full bg-rose-500 text-white text-[7px] font-black animate-pulse">LIVE</div>
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    💬
                </div>
                <span class="text-[11px] font-black">Pembda Space</span>
            </a>

            <!-- Jadwal (Clay Blue) -->
            <a href="{{ route('mobile.jadwal') }}" class="clay-blue p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📅
                </div>
                <span class="text-[11px] font-black">Jadwal</span>
            </a>

            <!-- Nilai (Clay Green) -->
            <a href="{{ route('mobile.nilai') }}" class="clay-green p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📈
                </div>
                <span class="text-[11px] font-black">Nilai</span>
            </a>

            <!-- Tagihan SPP (Clay Yellow) -->
            <a href="{{ route('mobile.tagihan') }}" class="clay-yellow p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    💳
                </div>
                <span class="text-[11px] font-black">Tagihan</span>
            </a>

            <!-- CBT Ujian (Clay Pink) -->
            <a href="{{ route('mobile.cbt') }}" class="clay-pink p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    💻
                </div>
                <span class="text-[11px] font-black">CBT Ujian</span>
            </a>

            <!-- LMS Ruang Belajar (Clay Blue) -->
            <a href="{{ route('mobile.lms.index') }}" class="clay-blue p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📚
                </div>
                <span class="text-[11px] font-black">LMS Belajar</span>
            </a>

            <!-- Presensi Scan QR (Clay Cyan) -->
            <a href="{{ route('mobile.absensi.scan') }}" class="clay-cyan p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📷
                </div>
                <span class="text-[11px] font-black">Scan QR</span>
            </a>

            {{-- MODUL PKL SISWA (Jika SMK & Kelas XII) --}}
            @if($showPkl)
            <a href="{{ route('mobile.pkl') }}" class="clay-orange p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    💼
                </div>
                <span class="text-[11px] font-black">Jurnal PKL</span>
            </a>
            @endif

            <!-- Hall Of Fame Siswa (Clay Orange) -->
            <a href="{{ route('mobile.hall-of-fame') }}" class="clay-orange p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    👑
                </div>
                <span class="text-[11px] font-black">Hall of Fame</span>
            </a>
        </div>
        @else
        <!-- Menu Guru (Synchronized Playful 3D Clay Cards) -->
        <div class="grid grid-cols-4 gap-3">
            <!-- 💬 PEMBDA SPACE (Primary Engine - Highlight Clay Purple) -->
            <a href="{{ route('mobile.space.index') }}" class="clay-purple p-3.5 text-center flex flex-col items-center group transition active:scale-95 relative overflow-hidden border-2 border-purple-400 shadow-lg">
                <div class="absolute top-1 right-1 px-1.5 py-0.2 rounded-full bg-rose-500 text-white text-[7px] font-black animate-pulse">LIVE</div>
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    💬
                </div>
                <span class="text-[11px] font-black">Pembda Space</span>
            </a>

            <!-- 1. Jadwal Mengajar (Clay Purple) -->
            <a href="{{ route('mobile.guru.jadwal') }}" class="clay-purple p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    👨‍🏫
                </div>
                <span class="text-[11px] font-black">Jadwal</span>
            </a>

            <!-- 2. Absen Siswa (Clay Green) -->
            <a href="{{ route('mobile.guru.absensi.input') }}" class="clay-green p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📋
                </div>
                <span class="text-[11px] font-black">Absen Siswa</span>
            </a>

            <!-- 3. Absen Saya / Presensi Guru (Clay Cyan) -->
            <a href="{{ route('mobile.guru.absensi.saya') }}" class="clay-cyan p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📌
                </div>
                <span class="text-[11px] font-black">Absen Saya</span>
            </a>

            <!-- 4. Periksa Tugas & Nilai (Clay Yellow) -->
            <a href="{{ route('mobile.guru.tugas') }}" class="clay-yellow p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📝
                </div>
                <span class="text-[11px] font-black">Nilai</span>
            </a>

            <!-- 5. LMS Modul (Clay Blue) -->
            <a href="{{ route('mobile.lms.index') }}" class="clay-blue p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📚
                </div>
                <span class="text-[11px] font-black">LMS</span>
            </a>

            <!-- 6. My Class / Kelas Saya (Clay Purple) -->
            <a href="{{ route('mobile.guru.kelas') }}" class="clay-purple p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    🏫
                </div>
                <span class="text-[11px] font-black">My Class</span>
            </a>

            <!-- 7. CBT Ujian (Clay Pink) -->
            <a href="{{ route('mobile.guru.cbt') }}" class="clay-pink p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    💻
                </div>
                <span class="text-[11px] font-black">CBT</span>
            </a>
        </div>
        @endif
    </div>

    <!-- Attendance Summary Widget (Clay Cards - Synchronized for Siswa & Guru) -->
    @if($activeRole === 'siswa')
    <div class="clay-card p-5 space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div class="w-3 h-3 rounded-full bg-emerald-500 animate-ping"></div>
                <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Rekap Absensi Bulan Ini</h3>
            </div>
            <a href="{{ route('mobile.absensi.index') }}" class="text-[11px] font-black text-blue-600 hover:text-blue-700">Detail <i class="fa-solid fa-chevron-right text-[9px]"></i></a>
        </div>

        <div class="grid grid-cols-3 gap-2.5">
            <div class="clay-green p-3 text-center">
                <span class="text-2xl font-black leading-none">{{ $attendanceStats['hadir'] }}</span>
                <span class="block text-[10px] font-black uppercase mt-1">Hadir</span>
            </div>
            <div class="clay-yellow p-3 text-center">
                <span class="text-2xl font-black leading-none">{{ $attendanceStats['terlambat'] }}</span>
                <span class="block text-[10px] font-black uppercase mt-1">Terlambat</span>
            </div>
            <div class="clay-pink p-3 text-center">
                <span class="text-2xl font-black leading-none">{{ $attendanceStats['sakit'] + $attendanceStats['izin'] + $attendanceStats['alpha'] }}</span>
                <span class="block text-[10px] font-black uppercase mt-1">Izin/Alpha</span>
            </div>
        </div>
    </div>
    @elseif(in_array($activeRole, ['guru', 'pegawai', 'superadmin', 'admin_sekolah', 'kepala_sekolah', 'ketua_yayasan']))
    <div class="clay-card p-5 space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div class="w-3 h-3 rounded-full bg-emerald-500 animate-ping"></div>
                <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Presensi & Jadwal Mengajar Guru</h3>
            </div>
            <a href="{{ route('mobile.guru.absensi.saya') }}" class="text-[11px] font-black text-blue-600 hover:text-blue-700">Presensi Saya <i class="fa-solid fa-chevron-right text-[9px]"></i></a>
        </div>

        <div class="grid grid-cols-3 gap-2.5">
            <div class="clay-green p-3 text-center">
                <span class="text-2xl font-black leading-none">{{ $teacherAttendanceStats['hadir'] ?? 0 }}</span>
                <span class="block text-[10px] font-black uppercase mt-1">Hadir Kerja</span>
            </div>
            <div class="clay-yellow p-3 text-center">
                <span class="text-2xl font-black leading-none">{{ $teacherAttendanceStats['terlambat'] ?? 0 }}</span>
                <span class="block text-[10px] font-black uppercase mt-1">Terlambat</span>
            </div>
            <div class="clay-purple p-3 text-center">
                <span class="text-2xl font-black leading-none">{{ $teacherAttendanceStats['total_jadwal'] ?? 0 }}</span>
                <span class="block text-[10px] font-black uppercase mt-1">Kelas Hari Ini</span>
            </div>
        </div>
    </div>
    @endif

    <!-- 🗳️ Poling Interaktif Pembda Space -->
    @if(isset($activePollThread) && $activePollThread && $activePollThread->poll)
    @php
        $poll = $activePollThread->poll;
        $totalVotes = $poll->options->sum('votes_count');
        $userVotedOptionId = \App\Models\ForumPollVote::where('forum_poll_id', $poll->id)->where('user_id', Auth::id())->value('forum_poll_option_id');
    @endphp
    <div class="clay-purple p-4.5 space-y-3 relative overflow-hidden shadow-lg border-2 border-purple-300" id="dashboard-poll-card">
        <div class="flex items-center justify-between">
            <span class="px-2.5 py-0.5 rounded-full bg-white/20 text-white text-[9px] font-black uppercase tracking-wider border border-white/30">
                🗳️ Poling Interaktif Space
            </span>
            <span class="text-[10px] font-extrabold text-purple-100">
                <i class="fa-solid fa-users text-amber-300 mr-1"></i><span id="poll-total-votes">{{ $totalVotes }}</span> Suara
            </span>
        </div>

        <div>
            <h4 class="text-xs font-black text-white leading-snug">{{ $activePollThread->title }}</h4>
            <p class="text-[10px] text-purple-100/90 font-bold mt-0.5">Oleh: {{ $activePollThread->user->name ?? 'Pembda HUB' }}</p>
        </div>

        <form onsubmit="submitDashboardPoll(event, {{ $poll->id }})" class="space-y-2 pt-1">
            @csrf
            <div class="space-y-1.5" id="poll-options-container">
                @foreach($poll->options as $opt)
                    @php
                        $pct = $totalVotes > 0 ? round(($opt->votes_count / $totalVotes) * 100) : 0;
                        $isChecked = $userVotedOptionId == $opt->id;
                    @endphp
                    <label class="block p-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 cursor-pointer transition relative overflow-hidden">
                        <!-- Progress Bar Fill -->
                        <div class="absolute left-0 top-0 bottom-0 bg-white/25 transition-all duration-500 rounded-xl" style="width: {{ $pct }}%"></div>
                        
                        <div class="flex items-center justify-between relative z-10 text-[11px] font-bold text-white">
                            <div class="flex items-center gap-2 min-w-0">
                                <input type="radio" name="option_id" value="{{ $opt->id }}" {{ $isChecked ? 'checked' : '' }} class="w-4 h-4 text-purple-600 focus:ring-0 accent-amber-300 cursor-pointer">
                                <span class="truncate">{{ $opt->option_text }}</span>
                            </div>
                            <span class="text-[10px] font-black text-amber-300 ml-2 shrink-0">{{ $pct }}% ({{ $opt->votes_count }})</span>
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="flex items-center justify-between pt-1">
                <button type="submit" class="px-4 py-2 bg-amber-400 hover:bg-amber-300 text-slate-900 font-black text-[11px] rounded-xl shadow-md transition active:scale-95 flex items-center gap-1.5">
                    <i class="fa-solid fa-check-to-slot"></i>
                    <span>Kirim Suara</span>
                </button>
                <a href="{{ route('mobile.space.show', $activePollThread->id) }}" class="text-[10px] font-extrabold text-purple-100 hover:underline">
                    Lihat Diskusi <i class="fa-solid fa-arrow-right text-[8px]"></i>
                </a>
            </div>
        </form>
    </div>
    @endif

    <!-- Pembda Space Widget (Terbaru & Paling Rame) -->
    <div x-data="{ spaceTab: 'terbaru' }">
        <div class="flex items-center justify-between mb-2.5 px-1">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                💬 Pembda Space Live Feed
            </h3>
            <a href="{{ route('mobile.space.index') }}" class="text-[11px] font-black text-purple-600 hover:text-purple-700">
                Lihat Semua <i class="fa-solid fa-arrow-right text-[9px]"></i>
            </a>
        </div>

        <!-- Tab Pills (Terbaru vs Paling Rame) -->
        <div class="grid grid-cols-2 gap-2 p-1.5 bg-slate-200/70 rounded-2xl border-2 border-slate-300 mb-3">
            <button @click="spaceTab = 'terbaru'"
                    :class="spaceTab === 'terbaru' ? 'bg-purple-600 text-white border-2 border-black shadow-md font-black' : 'text-slate-600 font-extrabold hover:text-slate-900'"
                    class="py-2 px-2 text-[11px] transition flex items-center justify-center gap-1">
                ✨ Postingan Terbaru
            </button>
            <button @click="spaceTab = 'rame'"
                    :class="spaceTab === 'rame' ? 'bg-rose-600 text-white border-2 border-black shadow-md font-black' : 'text-slate-600 font-extrabold hover:text-slate-900'"
                    class="py-2 px-2 text-[11px] transition flex items-center justify-center gap-1">
                🔥 Paling Rame
            </button>
        </div>

        <!-- TAB 1: POSTINGAN TERBARU -->
        <div x-show="spaceTab === 'terbaru'" x-transition class="space-y-3">
            @forelse($recentDiscussions as $thread)
                @php
                    $authorUser = $thread->user ?? null;
                    $authorPhoto = $authorUser?->avatar_url ?? null;
                    if (!$authorPhoto || str_contains($authorPhoto, 'default-avatar') || str_contains($authorPhoto, 'default-student.jpg')) {
                        if ($authorUser?->student?->photo_url) {
                            $authorPhoto = $authorUser->student->photo_url;
                        } elseif ($authorUser?->teacher?->photo_url) {
                            $authorPhoto = $authorUser->teacher->photo_url;
                        } else {
                            $authorPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($authorUser?->name ?? 'User') . '&background=7c3aed&color=fff&bold=true';
                        }
                    }
                    $userLiked = $thread->likes ? $thread->likes->contains('user_id', Auth::id()) : false;
                @endphp
                <div class="clay-card p-4.5 space-y-2 bg-white border-2 border-slate-200 hover:border-purple-300 transition">
                    <!-- Top Category & Time Badge -->
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[10px] font-black rounded-full bg-purple-100 text-purple-800 border border-purple-200">
                            #💬 {{ $thread->group->name ?? ($thread->category_label ?? $thread->category ?? 'Lobi Utama') }}
                        </span>
                        <span class="text-[10px] font-bold text-slate-500 flex items-center gap-1">
                            <i class="fa-regular fa-clock text-purple-500"></i>
                            {{ $thread->created_at ? $thread->created_at->diffForHumans() : '-' }}
                        </span>
                    </div>

                    <!-- Post Title & Content Preview -->
                    <a href="{{ route('mobile.space.show', $thread->id) }}" class="block space-y-1 group">
                        <h4 class="text-xs font-black text-slate-900 leading-snug group-hover:text-purple-600 transition">{{ $thread->title }}</h4>
                        <p class="text-[11px] text-slate-600 line-clamp-2 leading-relaxed font-semibold">{{ Str::limit(strip_tags($thread->content), 85) }}</p>
                    </a>

                    <!-- Footer: Author & Interactive Action Buttons -->
                    <div class="flex items-center justify-between text-[10px] text-slate-500 pt-2.5 border-t border-slate-100 font-extrabold">
                        <span class="text-slate-700 flex items-center gap-1.5 min-w-0 truncate">
                            <img src="{{ $authorPhoto }}" alt="{{ $authorUser?->name }}"
                                 onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($authorUser?->name ?? 'User') }}&background=7c3aed&color=fff&bold=true';"
                                 class="w-5 h-5 rounded-full object-cover border border-purple-200 shrink-0">
                            <span class="truncate font-black text-slate-800 uppercase tracking-tight text-[10px]">{{ $thread->user->name ?? 'Anonim' }}</span>
                        </span>
                        <div class="flex items-center space-x-2 shrink-0">
                            <!-- Direct Like Button (AJAX) -->
                            <button type="button" 
                                    onclick="toggleDashboardLike({{ $thread->id }}, this)" 
                                    class="like-btn px-2.5 py-1 rounded-xl transition flex items-center gap-1 text-[10px] {{ $userLiked ? 'bg-rose-50 text-rose-600 border border-rose-200 font-black' : 'bg-slate-100 text-slate-600 hover:bg-rose-50 hover:text-rose-600 font-bold' }}">
                                <i class="{{ $userLiked ? 'fa-solid fa-heart text-rose-500' : 'fa-regular fa-heart' }}"></i>
                                <span class="like-count">{{ $thread->likes_count ?? count($thread->likes ?? []) }}</span>
                            </button>

                            <!-- Direct Comment Button -->
                            <a href="{{ route('mobile.space.show', $thread->id) }}" 
                               class="px-2.5 py-1 rounded-xl bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100 transition flex items-center gap-1 text-[10px] font-black">
                                <i class="fa-regular fa-comment"></i>
                                <span>{{ $thread->replies_count ?? count($thread->replies ?? []) }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                    Belum ada postingan terbaru di Pembda Space.
                </div>
            @endforelse
        </div>

        <!-- TAB 2: POSTINGAN PALING RAME -->
        <div x-show="spaceTab === 'rame'" x-transition class="space-y-3">
            @forelse($popularDiscussions as $thread)
                @php
                    $authorUser = $thread->user ?? null;
                    $authorPhoto = $authorUser?->avatar_url ?? null;
                    if (!$authorPhoto || str_contains($authorPhoto, 'default-avatar') || str_contains($authorPhoto, 'default-student.jpg')) {
                        if ($authorUser?->student?->photo_url) {
                            $authorPhoto = $authorUser->student->photo_url;
                        } elseif ($authorUser?->teacher?->photo_url) {
                            $authorPhoto = $authorUser->teacher->photo_url;
                        } else {
                            $authorPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($authorUser?->name ?? 'User') . '&background=e11d48&color=fff&bold=true';
                        }
                    }
                    $userLiked = $thread->likes ? $thread->likes->contains('user_id', Auth::id()) : false;
                @endphp
                <div class="clay-card p-4.5 space-y-2 bg-white border-2 border-rose-100 hover:border-rose-300 transition">
                    <!-- Top Category, Hot Badge & Time -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[10px] font-black rounded-full bg-rose-100 text-rose-800 border border-rose-200">
                                🔥 Paling Rame
                            </span>
                            <span class="text-[9px] font-extrabold text-slate-500">
                                #{{ $thread->group->name ?? ($thread->category_label ?? $thread->category ?? 'Diskusi') }}
                            </span>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500 flex items-center gap-1">
                            <i class="fa-regular fa-clock text-rose-500"></i>
                            {{ $thread->created_at ? $thread->created_at->diffForHumans() : '-' }}
                        </span>
                    </div>

                    <!-- Post Title & Content Preview -->
                    <a href="{{ route('mobile.space.show', $thread->id) }}" class="block space-y-1 group">
                        <h4 class="text-xs font-black text-slate-900 leading-snug group-hover:text-rose-600 transition">{{ $thread->title }}</h4>
                        <p class="text-[11px] text-slate-600 line-clamp-2 leading-relaxed font-semibold">{{ Str::limit(strip_tags($thread->content), 85) }}</p>
                    </a>

                    <!-- Footer: Author & Interactive Action Buttons -->
                    <div class="flex items-center justify-between text-[10px] text-slate-500 pt-2.5 border-t border-slate-100 font-extrabold">
                        <span class="text-slate-700 flex items-center gap-1.5 min-w-0 truncate">
                            <img src="{{ $authorPhoto }}" alt="{{ $authorUser?->name }}"
                                 onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($authorUser?->name ?? 'User') }}&background=e11d48&color=fff&bold=true';"
                                 class="w-5 h-5 rounded-full object-cover border border-rose-200 shrink-0">
                            <span class="truncate font-black text-slate-800 uppercase tracking-tight text-[10px]">{{ $thread->user->name ?? 'Anonim' }}</span>
                        </span>
                        <div class="flex items-center space-x-2 shrink-0">
                            <!-- Direct Like Button (AJAX) -->
                            <button type="button" 
                                    onclick="toggleDashboardLike({{ $thread->id }}, this)" 
                                    class="like-btn px-2.5 py-1 rounded-xl transition flex items-center gap-1 text-[10px] {{ $userLiked ? 'bg-rose-50 text-rose-600 border border-rose-200 font-black' : 'bg-slate-100 text-slate-600 hover:bg-rose-50 hover:text-rose-600 font-bold' }}">
                                <i class="{{ $userLiked ? 'fa-solid fa-heart text-rose-500' : 'fa-regular fa-heart' }}"></i>
                                <span class="like-count">{{ $thread->likes_count ?? count($thread->likes ?? []) }}</span>
                            </button>

                            <!-- Direct Comment Button -->
                            <a href="{{ route('mobile.space.show', $thread->id) }}" 
                               class="px-2.5 py-1 rounded-xl bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100 transition flex items-center gap-1 text-[10px] font-black">
                                <i class="fa-regular fa-comment"></i>
                                <span>{{ $thread->replies_count ?? count($thread->replies ?? []) }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                    Belum ada postingan ramai di Pembda Space.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
