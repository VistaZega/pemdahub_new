@extends('mobile.layouts.app')

@section('title', 'Beranda Playful - PembdaHUB Mobile Pro')

@section('content')
<div class="space-y-5 pt-2">
    <!-- Hero Banner Card (Playful 3D Clay Banner) -->
    <div class="clay-blue p-6 relative overflow-hidden">
        <div class="flex items-center space-x-4 relative z-10">
            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-2xl object-cover border-4 border-white/60 shadow-md bg-white">

            <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-2 mb-1">
                    @php $activeRole = session('active_role', $user->role); @endphp
                    <span class="px-3 py-0.5 rounded-full bg-white/30 text-white text-[10px] font-black tracking-wide uppercase border border-white/40 shadow-sm backdrop-blur-sm">
                        {{ strtoupper($activeRole) }}
                    </span>
                    @if($student && $student->school)
                        <span class="text-[10px] text-blue-100 font-extrabold truncate">{{ $student->school->name }}</span>
                    @elseif($teacher && $teacher->school)
                        <span class="text-[10px] text-blue-100 font-extrabold truncate">{{ $teacher->school->name }}</span>
                    @endif
                </div>
                <h2 class="text-xl font-black text-white truncate leading-tight tracking-tight">Halo, {{ $user->name }}! 🚀</h2>
                @if($student)
                    @php
                        $studentClassroom = $classroom ?? $student->currentClassroom()->first();
                        $className = $studentClassroom->name ?? ($studentClassroom->class_name ?? '-');
                        $studentPoints = $user->reputation->total_points ?? ($student->reputation_points ?? 0);
                    @endphp
                    <div class="flex flex-wrap items-center gap-1.5 mt-1.5 text-[11px] font-extrabold text-blue-100">
                        <span class="bg-white/20 px-2 py-0.5 rounded-lg border border-white/30"><i class="fa-solid fa-id-card text-blue-200 mr-1"></i>NISN: {{ $student->nisn ?? $student->nis ?? '-' }}</span>
                        <span class="bg-white/20 px-2 py-0.5 rounded-lg border border-white/30"><i class="fa-solid fa-graduation-cap text-blue-200 mr-1"></i>Kelas: {{ $className }}</span>
                        <span class="bg-amber-400 text-slate-900 px-2 py-0.5 rounded-lg font-black shadow-xs"><i class="fa-solid fa-star text-amber-900 mr-1"></i>{{ number_format($studentPoints) }} Poin</span>
                    </div>
                @elseif($teacher)
                    <p class="text-xs text-blue-100/90 mt-1 font-bold truncate">
                        NIP: {{ $teacher->nip ?? '-' }} • Guru Pengampu
                    </p>
                @else
                    <p class="text-xs text-blue-100/90 mt-1 font-bold truncate">
                        {{ $user->email }}
                    </p>
                @endif
            </div>
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
        @endphp

        @if($activeRole === 'siswa')
        <div class="grid grid-cols-4 gap-3">
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
                <span class="text-[11px] font-black">CBT</span>
            </a>

            @if($siswaSchoolType === 'SMK' && $isKelasXII)
            <!-- PKL (Clay Orange) - Khusus Siswa SMK Kelas XII -->
            <a href="{{ route('mobile.pkl') }}" class="clay-orange p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    💼
                </div>
                <span class="text-[11px] font-black">PKL</span>
            </a>

            <!-- Project Akhir (Clay Purple) - Khusus Siswa SMK Kelas XII -->
            <a href="{{ route('mobile.final-project') }}" class="clay-purple p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    🚀
                </div>
                <span class="text-[11px] font-black">Project Akhir</span>
            </a>
            @elseif($siswaSchoolType === 'SMA' && $isKelasXII)
            <!-- Penelitian Akhir (Clay Purple) - Khusus Siswa SMA Kelas XII -->
            <a href="{{ route('mobile.final-project') }}" class="clay-purple p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    🔬
                </div>
                <span class="text-[11px] font-black">Penelitian</span>
            </a>
            @endif

            <!-- Pembda Space (Clay Purple) -->
            <a href="{{ route('mobile.space.index') }}" class="clay-purple p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    💬
                </div>
                <span class="text-[11px] font-black">Space</span>
            </a>

            <!-- LMS (Clay Blue) -->
            <a href="{{ route('mobile.lms.index') }}" class="clay-blue p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📚
                </div>
                <span class="text-[11px] font-black">LMS</span>
            </a>

            <!-- Absensi (Clay Cyan) -->
            <a href="{{ route('mobile.absensi.index') }}" class="clay-cyan p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📌
                </div>
                <span class="text-[11px] font-black">Absensi</span>
            </a>

            <!-- Catatan Perkembangan & Prestasi (Clay Yellow) -->
            <a href="{{ route('mobile.catatan') }}" class="clay-yellow p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    🏆
                </div>
                <span class="text-[11px] font-black">Catatan</span>
            </a>

            <!-- Hall Of Fame Siswa (Clay Orange) -->
            <a href="{{ route('mobile.hall-of-fame') }}" class="clay-orange p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    👑
                </div>
                <span class="text-[11px] font-black">Hall of Fame</span>
            </a>
        </div>
        @else
        <!-- Menu Guru (Synchronized Playful 3D Clay Cards - Grid cols 4 identik Siswa) -->
        <div class="grid grid-cols-4 gap-3">
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

            <!-- 7. Catatan Siswa (Clay Yellow) -->
            <a href="{{ route('mobile.guru.catatan-siswa') }}" class="clay-yellow p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    🏆
                </div>
                <span class="text-[11px] font-black">Catatan</span>
            </a>

            <!-- 8. CBT Ujian (Clay Pink) -->
            <a href="{{ route('mobile.guru.cbt') }}" class="clay-pink p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    💻
                </div>
                <span class="text-[11px] font-black">CBT</span>
            </a>

            <!-- 9. Raport Digital (Clay Green) -->
            <a href="{{ route('mobile.guru.raport') }}" class="clay-green p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📊
                </div>
                <span class="text-[11px] font-black">Raport</span>
            </a>

            <!-- 10. Tagihan Kelas Wali Kelas (Clay Green) -->
            <a href="{{ route('mobile.guru.tagihan') }}" class="clay-green p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    💳
                </div>
                <span class="text-[11px] font-black">Tagihan</span>
            </a>

            <!-- 11. Hall Of Fame (Clay Orange) -->
            <a href="{{ route('mobile.guru.hall-of-fame') }}" class="clay-orange p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    👑
                </div>
                <span class="text-[11px] font-black">Hall of Fame</span>
            </a>

            <!-- 12. Surat Edaran (Clay Orange) -->
            <a href="{{ route('mobile.guru.edaran') }}" class="clay-orange p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📜
                </div>
                <span class="text-[11px] font-black">Edaran</span>
            </a>

            {{-- 13. MODUL BIMBINGAN PKL GURU (Jika ditugaskan) --}}
            @if($hasPklBimbingan ?? false)
            <a href="{{ route('mobile.guru.pkl') }}" class="clay-orange p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    💼
                </div>
                <span class="text-[11px] font-black">Bimbing PKL</span>
            </a>

            <a href="{{ route('mobile.guru.pkl.monitoring') }}" class="clay-blue p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    🏢
                </div>
                <span class="text-[11px] font-black">Monitor DUDI</span>
            </a>
            @endif

            {{-- 14. MODUL BIMBINGAN PROJECT / PENELITIAN AKHIR GURU (Jika ditugaskan) --}}
            @if($hasProjectBimbingan ?? false)
            <a href="{{ route('mobile.guru.final-projects.bimbingan') }}" class="clay-purple p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    🔬
                </div>
                <span class="text-[11px] font-black">{{ ($teacher?->school?->type ?? '') === 'SMK' ? 'Bimbing Proyek' : 'Bimbing TA' }}</span>
            </a>
            @endif

            {{-- 15. MODUL PENGUJI UJIAN PROJECT / PENELITIAN AKHIR (Jika ditugaskan) --}}
            @if($hasProjectUjian ?? false)
            <a href="{{ route('mobile.guru.final-projects.ujian') }}" class="clay-pink p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    🎓
                </div>
                <span class="text-[11px] font-black">{{ ($teacher?->school?->type ?? '') === 'SMK' ? 'Uji Proyek' : 'Uji TA' }}</span>
            </a>
            @endif

            {{-- 16. MODUL KELOLA PANITIA PKL (Jika memiliki jabatan Panitia PKL / Admin) --}}
            @if($isPanitiaPkl ?? false)
            <a href="{{ route('mobile.panitia.pkl') }}" class="clay-yellow p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    ⚙️
                </div>
                <span class="text-[11px] font-black">Panitia PKL</span>
            </a>
            @endif

            {{-- 17. MODUL KELOLA PANITIA PROJECT/PENELITIAN AKHIR (Jika memiliki jabatan Panitia TA / Admin) --}}
            @if($isPanitiaProyek ?? false)
            <a href="{{ route('mobile.panitia.final-project') }}" class="clay-purple p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    🛠️
                </div>
                <span class="text-[11px] font-black">Panitia TA</span>
            </a>
            @endif

            <!-- 18. Pembda Space (Clay Purple) -->
            <a href="{{ route('mobile.space.index') }}" class="clay-purple p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    💬
                </div>
                <span class="text-[11px] font-black">Space</span>
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

    <!-- Pembda Space Widget (Terbaru & Paling Rame) -->
    <div x-data="{ spaceTab: 'terbaru' }">
        <div class="flex items-center justify-between mb-2.5 px-1">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                💬 Pembda Space
            </h3>
            <a href="{{ route('mobile.space.index') }}" class="text-[11px] font-black text-blue-600 hover:text-blue-700">
                Lihat Semua <i class="fa-solid fa-arrow-right text-[9px]"></i>
            </a>
        </div>

        <!-- Tab Pills (Terbaru vs Paling Rame) -->
        <div class="grid grid-cols-2 gap-2 p-1.5 bg-slate-200/70 rounded-2xl border-2 border-slate-300 mb-3">
            <button @click="spaceTab = 'terbaru'"
                    :class="spaceTab === 'terbaru' ? 'bg-blue-600 text-white border-2 border-black shadow-md font-black' : 'text-slate-600 font-extrabold hover:text-slate-900'"
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
                @endphp
                <a href="{{ route('mobile.space.show', $thread->id) }}" class="clay-card p-4.5 block hover:border-blue-300 transition space-y-2 bg-white border-2 border-slate-200">
                    <!-- Top Category & Time Badge -->
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[10px] font-black rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                            #💬 {{ $thread->category_label ?? $thread->category ?? 'Lobi Utama' }}
                        </span>
                        <span class="text-[10px] font-bold text-slate-500 flex items-center gap-1">
                            <i class="fa-regular fa-clock text-blue-500"></i>
                            {{ $thread->created_at ? $thread->created_at->diffForHumans() : '-' }}
                        </span>
                    </div>

                    <!-- Post Title & Content Preview -->
                    <div class="space-y-1">
                        <h4 class="text-xs font-black text-slate-900 leading-snug">{{ $thread->title }}</h4>
                        <p class="text-[11px] text-slate-600 line-clamp-2 leading-relaxed font-semibold">{{ Str::limit(strip_tags($thread->content), 80) }}</p>
                    </div>

                    <!-- Footer: Author & Stats -->
                    <div class="flex items-center justify-between text-[10px] text-slate-500 pt-2.5 border-t border-slate-100 font-extrabold">
                        <span class="text-slate-700 flex items-center gap-1.5 min-w-0 truncate">
                            <img src="{{ $authorPhoto }}" alt="{{ $authorUser?->name }}"
                                 onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($authorUser?->name ?? 'User') }}&background=7c3aed&color=fff&bold=true';"
                                 class="w-5 h-5 rounded-full object-cover border border-purple-200 shrink-0">
                            <span class="truncate font-black text-slate-800 uppercase tracking-tight text-[10px]">{{ $thread->user->name ?? 'Anonim' }}</span>
                        </span>
                        <div class="flex items-center space-x-3 text-slate-500 shrink-0 text-[10px] font-black">
                            <span class="flex items-center gap-1 text-blue-600"><i class="fa-regular fa-comment"></i>{{ $thread->replies_count ?? count($thread->replies ?? []) }}</span>
                            <span class="flex items-center gap-1 text-rose-600"><i class="fa-regular fa-heart"></i>{{ $thread->likes_count ?? count($thread->likes ?? []) }}</span>
                        </div>
                    </div>
                </a>
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
                @endphp
                <a href="{{ route('mobile.space.show', $thread->id) }}" class="clay-card p-4.5 block hover:border-rose-300 transition space-y-2 bg-white border-2 border-rose-100">
                    <!-- Top Category, Hot Badge & Time -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[10px] font-black rounded-full bg-rose-100 text-rose-800 border border-rose-200">
                                🔥 Paling Rame
                            </span>
                            <span class="text-[9px] font-extrabold text-slate-500">
                                #{{ $thread->category_label ?? $thread->category ?? 'Diskusi' }}
                            </span>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500 flex items-center gap-1">
                            <i class="fa-regular fa-clock text-rose-500"></i>
                            {{ $thread->created_at ? $thread->created_at->diffForHumans() : '-' }}
                        </span>
                    </div>

                    <!-- Post Title & Content Preview -->
                    <div class="space-y-1">
                        <h4 class="text-xs font-black text-slate-900 leading-snug">{{ $thread->title }}</h4>
                        <p class="text-[11px] text-slate-600 line-clamp-2 leading-relaxed font-semibold">{{ Str::limit(strip_tags($thread->content), 80) }}</p>
                    </div>

                    <!-- Footer: Author & Stats -->
                    <div class="flex items-center justify-between text-[10px] text-slate-500 pt-2.5 border-t border-slate-100 font-extrabold">
                        <span class="text-slate-700 flex items-center gap-1.5 min-w-0 truncate">
                            <img src="{{ $authorPhoto }}" alt="{{ $authorUser?->name }}"
                                 onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($authorUser?->name ?? 'User') }}&background=e11d48&color=fff&bold=true';"
                                 class="w-5 h-5 rounded-full object-cover border border-rose-200 shrink-0">
                            <span class="truncate font-black text-slate-800 uppercase tracking-tight text-[10px]">{{ $thread->user->name ?? 'Anonim' }}</span>
                        </span>
                        <div class="flex items-center space-x-3 text-slate-500 shrink-0 text-[10px] font-black">
                            <span class="flex items-center gap-1 text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200"><i class="fa-regular fa-comment"></i> {{ $thread->replies_count ?? count($thread->replies ?? []) }} Komentar</span>
                            <span class="flex items-center gap-1 text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-200"><i class="fa-regular fa-heart"></i> {{ $thread->likes_count ?? count($thread->likes ?? []) }} Suka</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                    Belum ada postingan ramai di Pembda Space.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
