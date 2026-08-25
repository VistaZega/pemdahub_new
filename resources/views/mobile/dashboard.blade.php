@extends('mobile.layouts.app')

@section('title', 'Beranda Playful - PembdaHUB Mobile Pro')

@section('content')
<div class="space-y-5 pt-2">
    <!-- Hero Banner Card (Playful 3D Clay Banner) -->
    <div class="clay-blue p-4 relative overflow-hidden shadow-lg">
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

        <!-- 🚀 Inline Compact Progress Belajar & Kehadiran (Di Dalam Hero Banner) -->
        @if($activeRole === 'siswa')
        @php
            $progOverall = $studentProgress['overall'] ?? 0;
            $progAttendance = $studentProgress['attendance_rate'] ?? 0;
        @endphp
        <div class="mt-3 pt-2.5 border-t border-white/20 flex items-center justify-between gap-3 text-white relative z-10">
            <div class="flex items-center gap-1.5 shrink-0 text-[10px] font-black">
                <span class="text-amber-300">🚀 Progres:</span>
                <span class="bg-white/20 px-2 py-0.5 rounded-lg border border-white/30 text-[10px] font-black">{{ $progOverall }}%</span>
            </div>
            <div class="flex-1 bg-black/20 rounded-full h-2.5 p-0.5 overflow-hidden border border-white/30">
                <div class="bg-gradient-to-r from-amber-300 via-yellow-300 to-emerald-400 h-full rounded-full transition-all duration-500 shadow-xs" style="width: {{ max(6, $progOverall) }}%"></div>
            </div>
            <div class="text-[10px] font-extrabold text-blue-100 shrink-0">
                Hadir: <span class="text-emerald-300 font-black">{{ $progAttendance }}%</span>
            </div>
        </div>
        @elseif(in_array($activeRole, ['guru', 'pegawai', 'superadmin', 'admin_sekolah', 'kepala_sekolah', 'ketua_yayasan']))
        @php
            $tOverall = $teacherProgress['overall'] ?? 100;
            $tAttendance = $teacherProgress['attendance_rate'] ?? 100;
        @endphp
        <div class="mt-3 pt-2.5 border-t border-white/20 flex items-center justify-between gap-3 text-white relative z-10">
            <div class="flex items-center gap-1.5 shrink-0 text-[10px] font-black">
                <span class="text-amber-300">👨‍🏫 Kinerja:</span>
                <span class="bg-white/20 px-2 py-0.5 rounded-lg border border-white/30 text-[10px] font-black">{{ $tOverall }}%</span>
            </div>
            <div class="flex-1 bg-black/20 rounded-full h-2.5 p-0.5 overflow-hidden border border-white/30">
                <div class="bg-gradient-to-r from-purple-300 via-pink-300 to-amber-300 h-full rounded-full transition-all duration-500 shadow-xs" style="width: {{ max(6, $tOverall) }}%"></div>
            </div>
            <div class="text-[10px] font-extrabold text-blue-100 shrink-0">
                Presensi: <span class="text-emerald-300 font-black">{{ $tAttendance }}%</span>
            </div>
        </div>
        @endif
    </div>

    <!-- 🕒 Compact Quick Presensi Bar (Guru / Pegawai / Siswa) -->
    @php
        $isTeacherOrStaff = in_array($activeRole, ['guru', 'pegawai', 'superadmin', 'admin_sekolah', 'kepala_sekolah', 'ketua_yayasan']);
        $isStudentRole = ($activeRole === 'siswa');
        $isNotCheckedOut = empty($todayAttendance) || !$todayAttendance->time_out || $todayAttendance->time_out === '00:00:00' || $todayAttendance->time_out === '00:00';
    @endphp

    @if(($isTeacherOrStaff || $isStudentRole) && (empty($todayAttendance) || $isNotCheckedOut))
    <div class="bg-white rounded-2xl p-2.5 sm:p-3 border-2 border-slate-200/90 shadow-xs flex items-center justify-between gap-2.5 transition-all">
        <!-- Left: Status Icon & Quick Info -->
        <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center text-sm sm:text-base shrink-0 shadow-2xs border
                        {{ empty($todayAttendance) ? 'bg-amber-100 text-amber-700 border-amber-300' : 'bg-emerald-100 text-emerald-700 border-emerald-300' }}">
                <i class="fa-solid {{ empty($todayAttendance) ? 'fa-fingerprint animate-pulse' : 'fa-check-double' }}"></i>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-black text-slate-900 truncate">
                        {{ empty($todayAttendance) ? 'Waktunya Presensi!' : 'Presensi Masuk Tercatat' }}
                    </span>
                    @if(empty($todayAttendance))
                        <span class="px-1.5 py-0.5 rounded-md text-[9px] font-black bg-rose-500 text-white shrink-0 leading-none">Belum</span>
                    @else
                        <span class="px-1.5 py-0.5 rounded-md text-[9px] font-black bg-emerald-600 text-white shrink-0 leading-none">{{ substr($todayAttendance->time_in, 0, 5) }}</span>
                    @endif
                </div>
                <p class="text-[10px] text-slate-500 font-bold truncate">
                    {{ empty($todayAttendance) ? 'Konfirmasi kehadiran GPS sekarang' : 'Jangan lupa presensi pulang nanti' }}
                </p>
            </div>
        </div>

        <!-- Right: Action Button -->
        <div class="shrink-0 flex items-center gap-1.5">
            <button type="button" 
                    onclick="performMobileGpsScan(this)" 
                    class="px-3 py-1.5 rounded-xl text-xs font-black transition flex items-center gap-1.5 shadow-2xs border-2 active:scale-95
                           {{ empty($todayAttendance) ? 'bg-amber-400 hover:bg-amber-300 text-slate-950 border-amber-500' : 'bg-blue-600 hover:bg-blue-500 text-white border-blue-700' }}">
                <i class="fa-solid {{ empty($todayAttendance) ? 'fa-location-dot' : 'fa-right-from-bracket' }} text-[10px]"></i>
                <span>{{ empty($todayAttendance) ? 'Presensi' : 'Pulang' }}</span>
            </button>
            <a href="{{ $isStudentRole ? route('mobile.absensi.index') : (Route::has('mobile.guru.absensi.saya') ? route('mobile.guru.absensi.saya') : route('mobile.absensi.index')) }}" 
               class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-xs border border-slate-200 shrink-0 active:scale-95"
               title="Rekap Absensi">
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </a>
        </div>
    </div>
    @endif

    <!-- 🌟 Top Stories Bar: Kanal & Squad Pembda Space -->
    <div class="bg-white/80 backdrop-blur-md p-3 rounded-2xl border-2 border-slate-200/80 shadow-xs space-y-2">
        <div class="flex items-center justify-between px-0.5">
            <h3 class="text-[11px] font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                <span class="relative flex h-2.5 w-2.5">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                <span>Space Channel & Squad</span>
            </h3>
            <a href="{{ route('mobile.space.index') }}" class="text-[10px] font-black text-purple-600 hover:text-purple-700 flex items-center gap-1">
                <span>Buka Space</span> <i class="fa-solid fa-chevron-right text-[8px]"></i>
            </a>
        </div>

        <div class="flex items-center gap-3.5 overflow-x-auto pb-1 pt-1 px-0.5 no-scrollbar scroll-smooth">
            <!-- Lobi Utama Bubble -->
            <a href="{{ route('mobile.space.index') }}" class="flex flex-col items-center gap-1.5 shrink-0 group">
                <div class="w-14 h-14 rounded-2xl p-0.5 bg-gradient-to-tr from-purple-600 via-pink-500 to-amber-400 shadow-md group-hover:scale-110 transition-all duration-300 relative">
                    <div class="w-full h-full rounded-[14px] bg-gradient-to-tr from-slate-900 to-indigo-900 flex items-center justify-center text-2xl shadow-inner border border-white/20">
                        🏛️
                    </div>
                    <span class="absolute -top-1 -right-1 flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500 border border-white"></span>
                    </span>
                </div>
                <span class="text-[10px] font-black text-slate-800 tracking-tight max-w-[68px] truncate text-center">Lobi Utama</span>
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
                        $ringColor = match($grp->type ?? '') {
                            'classroom' => 'from-emerald-400 via-teal-500 to-blue-500',
                            'broadcast' => 'from-rose-500 via-pink-500 to-amber-400',
                            'extracurricular' => 'from-amber-400 via-orange-500 to-rose-500',
                            'subject' => 'from-blue-500 via-indigo-500 to-purple-500',
                            default => 'from-purple-500 via-indigo-500 to-blue-400'
                        };
                    @endphp
                    <a href="{{ route('mobile.space.group.show', $grp->id) }}" class="flex flex-col items-center gap-1.5 shrink-0 group relative">
                        <div class="w-14 h-14 rounded-2xl p-0.5 bg-gradient-to-tr {{ $ringColor }} shadow-md group-hover:scale-110 transition-all duration-300">
                            <div class="w-full h-full rounded-[14px] bg-white flex items-center justify-center text-2xl text-slate-800 font-black border border-slate-100 shadow-inner">
                                {{ $grpIcon }}
                            </div>
                        </div>
                        @if($isNew)
                            <span class="absolute top-0 right-0 w-3.5 h-3.5 bg-rose-500 border-2 border-white rounded-full flex items-center justify-center text-[7px] text-white font-black">!</span>
                        @endif
                        <span class="text-[10px] font-black text-slate-800 tracking-tight max-w-[68px] truncate text-center">{{ $grp->name }}</span>
                    </a>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Quick Access Grid (Compact Circular Icons) -->
    <div>
        <div class="flex items-center justify-between mb-2.5 px-1">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">Modul PembdaHUB</h3>
            <span class="text-[10px] font-black text-blue-600">Pintasan Ringkas</span>
        </div>
        
        @php 
            $activeRole = session('active_role', $user->role); 
            $siswaSchoolType = strtoupper($student?->school?->type ?? '');
            $siswaGradeLevel = $student?->currentClassroom()?->first()?->grade_level ?? $student?->grade_level;
            $isKelasXII = ($siswaGradeLevel == 12);
            $showPkl = ($siswaSchoolType === 'SMK' && $isKelasXII);
        @endphp

        @if($activeRole === 'siswa')
        <div class="grid grid-cols-4 sm:grid-cols-5 gap-y-3.5 gap-x-2">
            <!-- Space -->
            <a href="{{ route('mobile.space.index') }}" class="flex flex-col items-center gap-1 group transition active:scale-95">
                <div class="w-12 h-12 rounded-full clay-purple flex items-center justify-center text-xl text-white shadow-md border-2 border-white/60 relative group-hover:scale-110 transition">
                    <span class="absolute -top-0.5 -right-0.5 px-1 rounded-full bg-rose-500 text-white text-[7px] font-black animate-pulse shadow-sm">LIVE</span>
                    💬
                </div>
                <span class="text-[10px] font-black text-slate-800 text-center leading-none">Space</span>
            </a>

            <!-- Jadwal -->
            <a href="{{ route('mobile.jadwal') }}" class="flex flex-col items-center gap-1 group transition active:scale-95">
                <div class="w-12 h-12 rounded-full clay-blue flex items-center justify-center text-xl text-white shadow-md border-2 border-white/60 group-hover:scale-110 transition">
                    📅
                </div>
                <span class="text-[10px] font-black text-slate-800 text-center leading-none">Jadwal</span>
            </a>

            <!-- Nilai -->
            <a href="{{ route('mobile.nilai') }}" class="flex flex-col items-center gap-1 group transition active:scale-95">
                <div class="w-12 h-12 rounded-full clay-green flex items-center justify-center text-xl text-white shadow-md border-2 border-white/60 group-hover:scale-110 transition">
                    📈
                </div>
                <span class="text-[10px] font-black text-slate-800 text-center leading-none">Nilai</span>
            </a>

            <!-- SPP -->
            <a href="{{ route('mobile.tagihan') }}" class="flex flex-col items-center gap-1 group transition active:scale-95">
                <div class="w-12 h-12 rounded-full clay-yellow flex items-center justify-center text-xl text-white shadow-md border-2 border-white/60 group-hover:scale-110 transition">
                    💳
                </div>
                <span class="text-[10px] font-black text-slate-800 text-center leading-none">SPP</span>
            </a>

            <!-- CBT -->
            <a href="{{ route('mobile.cbt') }}" class="flex flex-col items-center gap-1 group transition active:scale-95">
                <div class="w-12 h-12 rounded-full clay-pink flex items-center justify-center text-xl text-white shadow-md border-2 border-white/60 group-hover:scale-110 transition">
                    💻
                </div>
                <span class="text-[10px] font-black text-slate-800 text-center leading-none">CBT</span>
            </a>

            <!-- LMS -->
            <a href="{{ route('mobile.lms.index') }}" class="flex flex-col items-center gap-1 group transition active:scale-95">
                <div class="w-12 h-12 rounded-full clay-blue flex items-center justify-center text-xl text-white shadow-md border-2 border-white/60 group-hover:scale-110 transition">
                    📚
                </div>
                <span class="text-[10px] font-black text-slate-800 text-center leading-none">LMS</span>
            </a>

            <!-- Absensi -->
            <a href="{{ route('mobile.absensi.scan') }}" class="flex flex-col items-center gap-1 group transition active:scale-95">
                <div class="w-12 h-12 rounded-full clay-cyan flex items-center justify-center text-xl text-white shadow-md border-2 border-white/60 group-hover:scale-110 transition">
                    📷
                </div>
                <span class="text-[10px] font-black text-slate-800 text-center leading-none">Absensi</span>
            </a>

            {{-- PKL (Khusus Siswa SMK Kelas XII) --}}
            @if($showPkl)
            <a href="{{ route('mobile.pkl') }}" class="flex flex-col items-center gap-1 group transition active:scale-95">
                <div class="w-12 h-12 rounded-full clay-orange flex items-center justify-center text-xl text-white shadow-md border-2 border-white/60 group-hover:scale-110 transition">
                    💼
                </div>
                <span class="text-[10px] font-black text-slate-800 text-center leading-none">PKL</span>
            </a>
            @endif

            <!-- DNA 360° -->
            <a href="{{ route('mobile.dna') }}" class="flex flex-col items-center gap-1 group transition active:scale-95">
                <div class="w-12 h-12 rounded-full clay-purple flex items-center justify-center text-xl text-white shadow-md border-2 border-white/60 group-hover:scale-110 transition relative">
                    <span class="absolute -top-1 -right-1 px-1.5 py-0.2 rounded-full bg-gradient-to-r from-fuchsia-600 to-indigo-600 text-white text-[7px] font-black shadow-xs">360°</span>
                    🧬
                </div>
                <span class="text-[10px] font-black text-slate-800 text-center leading-none">DNA 360°</span>
            </a>

            <!-- Ekskul -->
            <a href="{{ route('mobile.ekskul') }}" class="flex flex-col items-center gap-1 group transition active:scale-95">
                <div class="w-12 h-12 rounded-full clay-orange flex items-center justify-center text-xl text-white shadow-md border-2 border-white/60 group-hover:scale-110 transition">
                    🎨
                </div>
                <span class="text-[10px] font-black text-slate-800 text-center leading-none">Ekskul</span>
            </a>

            <!-- Fame -->
            <a href="{{ route('mobile.hall-of-fame') }}" class="flex flex-col items-center gap-1 group transition active:scale-95">
                <div class="w-12 h-12 rounded-full clay-pink flex items-center justify-center text-xl text-white shadow-md border-2 border-white/60 group-hover:scale-110 transition">
                    👑
                </div>
                <span class="text-[10px] font-black text-slate-800 text-center leading-none">Fame</span>
            </a>
        </div>
        @else
        <!-- Menu Guru (Compact Circular Icons with 3D Pastel Clay) -->
        <div class="grid grid-cols-4 sm:grid-cols-5 gap-y-3.5 gap-x-2">
            <!-- Space -->
            <a href="{{ route('mobile.space.index') }}" class="flex flex-col items-center gap-1 group transition active:scale-95">
                <div class="w-12 h-12 rounded-full clay-purple flex items-center justify-center text-xl text-white shadow-md border-2 border-white/60 relative group-hover:scale-110 transition">
                    <span class="absolute -top-0.5 -right-0.5 px-1 rounded-full bg-rose-500 text-white text-[7px] font-black animate-pulse shadow-sm">LIVE</span>
                    💬
                </div>
                <span class="text-[10px] font-black text-slate-800 text-center leading-none">Space</span>
            </a>

            <!-- Jadwal -->
            <a href="{{ route('mobile.guru.jadwal') }}" class="flex flex-col items-center gap-1 group transition active:scale-95">
                <div class="w-12 h-12 rounded-full clay-purple flex items-center justify-center text-xl text-white shadow-md border-2 border-white/60 group-hover:scale-110 transition">
                    👨‍🏫
                </div>
                <span class="text-[10px] font-black text-slate-800 text-center leading-none">Jadwal</span>
            </a>

            <!-- Absensi -->
            <a href="{{ route('mobile.guru.absensi.input') }}" class="flex flex-col items-center gap-1 group transition active:scale-95">
                <div class="w-12 h-12 rounded-full clay-green flex items-center justify-center text-xl text-white shadow-md border-2 border-white/60 group-hover:scale-110 transition">
                    📋
                </div>
                <span class="text-[10px] font-black text-slate-800 text-center leading-none">Absensi</span>
            </a>

            <!-- Presensi -->
            <a href="{{ route('mobile.guru.absensi.saya') }}" class="flex flex-col items-center gap-1 group transition active:scale-95">
                <div class="w-12 h-12 rounded-full clay-cyan flex items-center justify-center text-xl text-white shadow-md border-2 border-white/60 group-hover:scale-110 transition">
                    📌
                </div>
                <span class="text-[10px] font-black text-slate-800 text-center leading-none">Presensi</span>
            </a>

            <!-- Nilai -->
            <a href="{{ route('mobile.guru.tugas') }}" class="flex flex-col items-center gap-1 group transition active:scale-95">
                <div class="w-12 h-12 rounded-full clay-yellow flex items-center justify-center text-xl text-white shadow-md border-2 border-white/60 group-hover:scale-110 transition">
                    📝
                </div>
                <span class="text-[10px] font-black text-slate-800 text-center leading-none">Nilai</span>
            </a>

            <!-- LMS -->
            <a href="{{ route('mobile.lms.index') }}" class="flex flex-col items-center gap-1 group transition active:scale-95">
                <div class="w-12 h-12 rounded-full clay-blue flex items-center justify-center text-xl text-white shadow-md border-2 border-white/60 group-hover:scale-110 transition">
                    📚
                </div>
                <span class="text-[10px] font-black text-slate-800 text-center leading-none">LMS</span>
            </a>

            <!-- Kelas -->
            <a href="{{ route('mobile.guru.kelas') }}" class="flex flex-col items-center gap-1 group transition active:scale-95">
                <div class="w-12 h-12 rounded-full clay-purple flex items-center justify-center text-xl text-white shadow-md border-2 border-white/60 group-hover:scale-110 transition">
                    🏫
                </div>
                <span class="text-[10px] font-black text-slate-800 text-center leading-none">Kelas</span>
            </a>

            <!-- CBT -->
            <a href="{{ route('mobile.guru.cbt') }}" class="flex flex-col items-center gap-1 group transition active:scale-95">
                <div class="w-12 h-12 rounded-full clay-pink flex items-center justify-center text-xl text-white shadow-md border-2 border-white/60 group-hover:scale-110 transition">
                    💻
                </div>
                <span class="text-[10px] font-black text-slate-800 text-center leading-none">CBT</span>
            </a>

            <!-- Ekskul -->
            <a href="{{ route('mobile.guru.ekskul') }}" class="flex flex-col items-center gap-1 group transition active:scale-95">
                <div class="w-12 h-12 rounded-full clay-orange flex items-center justify-center text-xl text-white shadow-md border-2 border-white/60 group-hover:scale-110 transition">
                    🎨
                </div>
                <span class="text-[10px] font-black text-slate-800 text-center leading-none">Ekskul</span>
            </a>

            <!-- DNA 360° -->
            <a href="{{ route('mobile.guru.dna') }}" class="flex flex-col items-center gap-1 group transition active:scale-95">
                <div class="w-12 h-12 rounded-full clay-purple flex items-center justify-center text-xl text-white shadow-md border-2 border-white/60 group-hover:scale-110 transition relative">
                    <span class="absolute -top-1 -right-1 px-1.5 py-0.2 rounded-full bg-gradient-to-r from-fuchsia-600 to-indigo-600 text-white text-[7px] font-black shadow-xs">360°</span>
                    🧬
                </div>
                <span class="text-[10px] font-black text-slate-800 text-center leading-none">DNA 360°</span>
            </a>
        </div>
        @endif
    </div>

    <!-- 🧬 DNA Potensi Belajar & Minat Karier 360° Siswa (Mobile Widget) -->
    @if($activeRole === 'siswa' && isset($dnaAnalysis) && $dnaAnalysis)
    <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-purple-950 p-4.5 rounded-2xl shadow-lg border-2 border-slate-700 text-white relative overflow-hidden space-y-3">
        <div class="absolute -right-8 -top-8 w-36 h-36 bg-fuchsia-500/20 rounded-full blur-xl pointer-events-none"></div>
        <div class="relative z-10 flex items-start justify-between gap-2">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl overflow-hidden border border-white/30 shadow-md bg-slate-800 shrink-0">
                    <img src="{{ $student->photo_url }}" alt="{{ $student->full_name }}" class="w-full h-full object-cover">
                </div>
                <div>
                    <div class="flex items-center gap-1.5 mb-0.5">
                        <span class="px-2 py-0.5 rounded-full bg-gradient-to-r {{ $dnaAnalysis['archetype']['color'] }} text-white text-[8px] font-black uppercase tracking-wider shadow-xs">
                            <i class="fas {{ $dnaAnalysis['archetype']['badge_icon'] }}"></i> {{ $dnaAnalysis['archetype']['title'] }}
                        </span>
                        <span class="text-[9px] font-bold text-slate-300 bg-white/10 px-1.5 py-0.5 rounded-full">
                            {{ $dnaAnalysis['confidence_score'] }}%
                        </span>
                    </div>
                    <h3 class="text-xs font-black text-white leading-tight">DNA Akademik 360°</h3>
                </div>
            </div>
            <a href="{{ route('mobile.dna') }}" class="px-3 py-1.5 bg-gradient-to-r from-fuchsia-500 to-indigo-500 hover:from-fuchsia-600 hover:to-indigo-600 text-white text-[10px] font-black rounded-xl shadow-sm transition active:scale-95 flex items-center gap-1">
                <span>Detail</span> <i class="fa-solid fa-chevron-right text-[8px]"></i>
            </a>
        </div>

        <p class="text-[11px] text-slate-200 leading-snug font-medium italic">
            "{{ $dnaAnalysis['archetype']['tagline'] }}"
        </p>

        <!-- 6 Dimensi Grid Ringkas -->
        <div class="grid grid-cols-3 gap-1.5 text-[9px] pt-1">
            <div class="bg-white/10 rounded-lg p-1.5 text-center border border-white/10">
                <span class="text-slate-300">Logika:</span> <b class="text-blue-300">{{ $dnaAnalysis['scores']['logic'] }}</b>
            </div>
            <div class="bg-white/10 rounded-lg p-1.5 text-center border border-white/10">
                <span class="text-slate-300">Bahasa:</span> <b class="text-emerald-300">{{ $dnaAnalysis['scores']['communication'] }}</b>
            </div>
            <div class="bg-white/10 rounded-lg p-1.5 text-center border border-white/10">
                <span class="text-slate-300">Vokasi:</span> <b class="text-indigo-300">{{ $dnaAnalysis['scores']['technical'] }}</b>
            </div>
            <div class="bg-white/10 rounded-lg p-1.5 text-center border border-white/10">
                <span class="text-slate-300">Sosial:</span> <b class="text-amber-300">{{ $dnaAnalysis['scores']['social'] }}</b>
            </div>
            <div class="bg-white/10 rounded-lg p-1.5 text-center border border-white/10">
                <span class="text-slate-300">Kreatif:</span> <b class="text-purple-300">{{ $dnaAnalysis['scores']['creative'] }}</b>
            </div>
            <div class="bg-white/10 rounded-lg p-1.5 text-center border border-white/10">
                <span class="text-slate-300">Disiplin:</span> <b class="text-rose-300">{{ $dnaAnalysis['scores']['discipline'] }}</b>
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
            <p class="text-[10px] text-purple-100/90 font-bold mt-0.5 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-300"></span>
                <span>Oleh: {{ $activePollThread->user->name ?? 'Pembda HUB' }}</span>
            </p>
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

        <!-- 📲 Download App Promo Bar (Agar Warga Lain Bisa Install PembdaHUB Mobile) -->
        <div class="pt-2.5 mt-1 border-t border-white/20 flex items-center justify-between gap-2">
            <div class="text-[10px] font-bold text-purple-100 flex items-center gap-1.5 min-w-0">
                <span class="text-sm">📱</span>
                <span class="truncate">Ajak Warga PEMBDA Lain Install PembdaHUB di HP!</span>
            </div>
            <a href="{{ route('app.download') }}" target="_blank" class="px-3 py-1.5 bg-white text-purple-900 font-black text-[10px] rounded-xl shadow-sm hover:bg-purple-50 transition shrink-0 flex items-center gap-1 active:scale-95">
                <i class="fa-solid fa-download text-purple-600"></i>
                <span>Download App</span>
            </a>
        </div>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function performMobileGpsScan(btnElement) {
        if (!navigator.geolocation) {
            Swal.fire({
                icon: 'error',
                title: 'GPS Tidak Didukung',
                text: 'Perangkat atau browser Anda tidak mendukung fitur lokasi (GPS). Gunakan browser Chrome atau izinkan akses lokasi.',
                confirmButtonColor: '#2563eb',
            });
            return;
        }

        const origHtml = btnElement ? btnElement.innerHTML : '';
        if (btnElement) {
            btnElement.disabled = true;
            btnElement.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i> Mengambil Lokasi GPS...';
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
                    btnElement.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i> Memverifikasi Presensi...';
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
                            html: `<p class="font-bold text-slate-800">${data.message}</p><p class="text-xs text-slate-500 mt-2">Data kehadiran Anda telah tercatat.</p>`,
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
                            text: data.message || 'Terjadi kendala saat memverifikasi lokasi.',
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
                let errText = 'Gagal mengakses lokasi GPS. Pastikan GPS aktif dan izin lokasi diizinkan pada browser/HP Anda.';
                if (err.code === 1) {
                    errText = 'Izin lokasi ditolak. Harap izinkan akses lokasi (GPS) pada browser/aplikasi Anda.';
                } else if (err.code === 2) {
                    errText = 'Posisi GPS tidak dapat ditentukan. Pastikan Anda berada di area dengan sinyal GPS baik.';
                } else if (err.code === 3) {
                    errText = 'Waktu permintaan lokasi habis (timeout). Silakan coba lagi.';
                }
                Swal.fire({
                    icon: 'warning',
                    title: 'Akses Lokasi Diperlukan',
                    text: errText,
                    confirmButtonColor: '#e11d48',
                });
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }

    function toggleDashboardLike(threadId, btn) {
        fetch(`/mobile/space/threads/${threadId}/like`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const countSpan = btn.querySelector('.like-count');
                const icon = btn.querySelector('i');
                if (countSpan) countSpan.textContent = data.likes_count;
                if (data.liked) {
                    btn.classList.remove('bg-slate-100', 'text-slate-600');
                    btn.classList.add('bg-rose-50', 'text-rose-600', 'border-rose-200', 'font-black');
                    icon.classList.remove('fa-regular');
                    icon.classList.add('fa-solid', 'text-rose-500');
                } else {
                    btn.classList.add('bg-slate-100', 'text-slate-600');
                    btn.classList.remove('bg-rose-50', 'text-rose-600', 'border-rose-200', 'font-black');
                    icon.classList.remove('fa-solid', 'text-rose-500');
                    icon.classList.add('fa-regular');
                }
            }
        })
        .catch(err => console.error(err));
    }
</script>
@endpush
