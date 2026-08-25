@php
    $userRole = session('active_role', auth()->user()?->role);
    $isPegawaiOnly = ($userRole === 'pegawai');
@endphp

{{--
    Guru Layout — extends unified master layout
    Theme: Emerald/Teal
--}}
@extends('layouts.app', [
    'theme'       => 'emerald',
    'sidebarId'   => 'guru-sidebar',
    'storageKey'  => 'guru_sidebar_collapsed',
    'portalName'  => $isPegawaiOnly ? 'Portal Kepegawaian' : 'Portal Guru',
    'portalSub'   => 'PembdaHUB Education System',
    'portalIcon'  => $isPegawaiOnly ? 'fas fa-id-card-clip' : 'fas fa-chalkboard-teacher',
])

@section('sidebar-menu')
    @php
        $ac = 'bg-emerald-100 text-emerald-950 font-black border-2 border-black shadow-xs active';
        $nc = 'text-slate-900 font-bold hover:bg-slate-100 hover:text-black';
        
        $isWaliKelas = false;
        $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
        $teacherId = \App\Models\Teacher::where('user_id', auth()->id())->value('id');
        if (!$isPegawaiOnly && $activeYearId && $teacherId) {
            $isWaliKelas = \App\Models\Classroom::where('homeroom_teacher_id', $teacherId)
                ->where('academic_year_id', $activeYearId)
                ->exists();
        }
    @endphp

    <!-- Dashboard -->
    <a href="{{ route('guru.dashboard') }}" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('guru.dashboard') ? $ac : 'text-slate-900 font-bold hover:bg-slate-100' }}">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #059669 !important; color: #ffffff !important;">
            <i class="fas fa-home text-xs text-white"></i>
        </div>
        <span class="text-sm flex-1 font-black">Dashboard</span>
    </a>

    @if(!$isPegawaiOnly)
    <!-- Jadwal Mengajar -->
    <a href="{{ route('guru.jadwal') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.jadwal') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #2563eb !important; color: #ffffff !important;"><i class="fas fa-calendar-alt text-[10px] text-white"></i></div>
        <span>Jadwal Mengajar</span>
    </a>
    @endif

    <!-- Kalender Pendidikan -->
    <a href="{{ route('guru.calendar.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.calendar.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #0284c7 !important; color: #ffffff !important;"><i class="fas fa-calendar-alt text-[10px] text-white"></i></div>
        <span>Kalender Pendidikan</span>
    </a>

    <!-- Surat Edaran Yayasan -->
    <a href="{{ route('admin.letters.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.letters.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #7c3aed !important; color: #ffffff !important;"><i class="fas fa-file-signature text-[10px] text-white"></i></div>
        <span>Surat Edaran Yayasan</span>
    </a>

    @if(!$isPegawaiOnly)
    <!-- Kelas Saya -->
    <a href="{{ route('guru.kelas') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.kelas', 'guru.siswa-kelas') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #4f46e5 !important; color: #ffffff !important;"><i class="fas fa-users text-[10px] text-white"></i></div>
        <span>Kelas Saya</span>
    </a>

    @if($isWaliKelas)
    <!-- Biaya Pendidikan -->
    <a href="{{ route('guru.tagihan-siswa') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.tagihan-siswa') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #0891b2 !important; color: #ffffff !important;"><i class="fas fa-file-invoice-dollar text-[10px] text-white"></i></div>
        <span>Biaya Pendidikan</span>
    </a>
    @endif

    @if(\App\Models\Setting::getValue('guru_access_lms', true))
    <!-- LMS -->
    <a href="{{ route('guru.lms.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.lms.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #ea580c !important; color: #ffffff !important;"><i class="fas fa-laptop text-[10px] text-white"></i></div>
        <span>LMS</span>
    </a>
    @endif
    @endif

    <!-- Pembda Knowledge & Media -->
    <a href="{{ route('guru.knowledge.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.knowledge.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #0d9488 !important; color: #ffffff !important;"><i class="fas fa-cube text-[10px] text-white"></i></div>
        <span>Knowledge & Media</span>
    </a>

    <!-- PembdaHUB Simulator Lab -->
    <a href="{{ route('simlab.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('simlab.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #059669 !important; color: #ffffff !important;"><i class="fas fa-microchip text-[10px] text-white"></i></div>
        <span>Simulator Lab</span>
    </a>

    @if(!$isPegawaiOnly)
    <!-- Nilai Siswa -->
    <a href="{{ route('guru.nilai') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.nilai*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #16a34a !important; color: #ffffff !important;"><i class="fas fa-chart-bar text-[10px] text-white"></i></div>
        <span>Nilai Siswa</span>
    </a>
    @if(request()->routeIs('guru.nilai*'))
    <div class="ml-11 -mt-0.5 mb-1 space-y-0.5">
        <a href="{{ route('guru.nilai') }}" class="block text-xs px-3 py-1.5 rounded-lg transition {{ request()->routeIs('guru.nilai') && !request()->routeIs('guru.nilai.*') ? 'bg-emerald-100 text-emerald-950 font-black border border-black' : 'text-slate-700 font-bold hover:text-black hover:bg-slate-100' }}">
            <i class="fas fa-eye mr-1.5 text-[10px]"></i>Lihat Nilai
        </a>
        <a href="{{ route('guru.nilai.input') }}" class="block text-xs px-3 py-1.5 rounded-lg transition {{ request()->routeIs('guru.nilai.input') ? 'bg-emerald-100 text-emerald-950 font-black border border-black' : 'text-slate-700 font-bold hover:text-black hover:bg-slate-100' }}">
            <i class="fas fa-edit mr-1.5 text-[10px]"></i>Input Nilai
        </a>
        <a href="{{ route('guru.nilai.summary') }}" class="block text-xs px-3 py-1.5 rounded-lg transition {{ request()->routeIs('guru.nilai.summary') ? 'bg-emerald-100 text-emerald-950 font-black border border-black' : 'text-slate-700 font-bold hover:text-black hover:bg-slate-100' }}">
            <i class="fas fa-table mr-1.5 text-[10px]"></i>Rekap Nilai
        </a>
    </div>
    @endif

    @if(\App\Models\Setting::getValue('guru_access_cbt', true))
    <!-- CBT / Ujian -->
    <a href="{{ route('guru.cbt.banks.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.cbt.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #0f766e !important; color: #ffffff !important;"><i class="fas fa-desktop text-[10px] text-white"></i></div>
        <span>CBT / Ujian</span>
    </a>
    @if(request()->routeIs('guru.cbt.*'))
    <div class="ml-11 -mt-0.5 mb-1 space-y-0.5">
        <a href="{{ route('guru.cbt.banks.index') }}" class="block text-xs px-3 py-1.5 rounded-lg transition {{ request()->routeIs('guru.cbt.banks.*') ? 'bg-emerald-100 text-emerald-950 font-black border border-black' : 'text-slate-700 font-bold hover:text-black hover:bg-slate-100' }}">
            <i class="fas fa-database mr-1.5 text-[10px]"></i>Bank Soal
        </a>
        <a href="{{ route('guru.cbt.exams.index') }}" class="block text-xs px-3 py-1.5 rounded-lg transition {{ request()->routeIs('guru.cbt.exams.*') ? 'bg-emerald-100 text-emerald-950 font-black border border-black' : 'text-slate-700 font-bold hover:text-black hover:bg-slate-100' }}">
            <i class="fas fa-file-circle-check mr-1.5 text-[10px]"></i>Kelola Ujian
        </a>
    </div>
    @endif
    @endif
    @endif

    <!-- Perjanjian Kinerja -->
    <a href="{{ route('guru.performance_contracts.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.performance_contracts.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #dc2626 !important; color: #ffffff !important;"><i class="fas fa-file-signature text-[10px] text-white"></i></div>
        <span>Perjanjian Kinerja</span>
    </a>

    @if(!$isPegawaiOnly)
    <!-- Asisten AI -->
    <div data-menu-group="asisten-ai" class="menu-group">
        <button onclick="toggleGroup(this)" class="menu-group-toggle w-full menu-item flex items-center justify-between px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.ai.*') ? $ac : $nc }}">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #0f766e !important; color: #ffffff !important;">
                    <i class="fas fa-magic text-[10px] text-white"></i>
                </div>
                <span class="text-sm">Asisten AI</span>
            </div>
            <i class="fas fa-chevron-right text-[10px] chevron transition-transform {{ request()->routeIs('guru.ai.*') ? 'rotate-90' : '' }}"></i>
        </button>
        <div class="menu-group-body ml-11 mt-1 space-y-0.5 {{ request()->routeIs('guru.ai.*') ? '' : 'closed' }}">
            <a href="{{ route('guru.ai.lesson-plan') }}" class="block text-xs px-3 py-1.5 rounded-lg transition {{ request()->routeIs('guru.ai.lesson-plan') ? 'bg-emerald-100 text-emerald-950 font-black border border-black' : 'text-slate-700 font-bold hover:text-black hover:bg-slate-100' }}">
                <i class="fas fa-wand-magic-sparkles mr-1.5 text-[10px]"></i>RPP Generator
            </a>
            <a href="{{ route('guru.ai.question-generator') }}" class="block text-xs px-3 py-1.5 rounded-lg transition {{ request()->routeIs('guru.ai.question-generator') ? 'bg-emerald-100 text-emerald-950 font-black border border-black' : 'text-slate-700 font-bold hover:text-black hover:bg-slate-100' }}">
                <i class="fas fa-brain mr-1.5 text-[10px]"></i>Pembuat Soal CBT
            </a>
        </div>
    </div>
    @endif

    <!-- Absensi Siswa -->
    <a href="{{ route('guru.absensi') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.absensi*') && !request()->routeIs('guru.absensi.saya') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #9333ea !important; color: #ffffff !important;"><i class="fas fa-user-check text-[10px] text-white"></i></div>
        <span>Absensi Siswa</span>
    </a>


    @if(\App\Models\Setting::getValue('pegawai_view_attendance_recap', true))
    <!-- Absensi Saya -->
    <a href="{{ route('guru.absensi.saya') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.absensi.saya') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #0d9488 !important; color: #ffffff !important;"><i class="fas fa-clipboard-user text-[10px] text-white"></i></div>
        <span>Absensi Saya</span>
    </a>
    @endif

    @if(\App\Models\Setting::getValue('pegawai_can_request_leave', true))
    <!-- Pengajuan Cuti -->
    <a href="{{ route('guru.leaves.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.leaves.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #d97706 !important; color: #ffffff !important;"><i class="fas fa-calendar-times text-[10px] text-white"></i></div>
        <span>Pengajuan Cuti</span>
    </a>
    @endif

    @if(!$isPegawaiOnly)
    <!-- Raport -->
    <a href="{{ route('guru.raport.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.raport.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #e11d48 !important; color: #ffffff !important;"><i class="fas fa-file-alt text-[10px] text-white"></i></div>
        <span>Raport Akademik</span>
    </a>
    <a href="{{ route('guru.dna.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.dna.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #9333ea !important; color: #ffffff !important;"><i class="fas fa-dna text-[10px] text-white"></i></div>
        <span>DNA Akademik 360°</span>
    </a>
    <a href="{{ route('guru.extracurricular.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.extracurricular.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #6366f1 !important; color: #ffffff !important;"><i class="fas fa-palette text-[10px] text-white"></i></div>
        <span>Ekstrakurikuler</span>
    </a>

    <!-- Monitoring PKL -->
    @php
        $teacherId = \App\Models\Teacher::where('user_id', auth()->id())->value('id');
        $hasPkl = \App\Models\PklPlacement::where('teacher_id', $teacherId)->exists();
    @endphp
    @if($hasPkl)
    <a href="{{ route('guru.pkl.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.pkl.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #4338ca !important; color: #ffffff !important;"><i class="fas fa-briefcase text-[10px] text-white"></i></div>
        <span>Bimbingan PKL (Siswa)</span>
    </a>
    <a href="{{ route('guru.pkl_monitorings.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.pkl_monitorings.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #4338ca !important; color: #ffffff !important;"><i class="fas fa-file-invoice text-[10px] text-white"></i></div>
        <span>Laporan Monitoring PKL</span>
    </a>
    @endif

    <!-- Bimbingan & Ujian Tugas Akhir -->
    @php
        $teacherModel = \App\Models\Teacher::with('school')->where('user_id', auth()->id())->first();
        $schoolType = $teacherModel?->school?->type;
        $isSmaOrSmkTeacher = $teacherModel && in_array($schoolType, ['SMA', 'SMK']);
        
        $bimbinganLabel = 'Bimbingan Tugas Akhir';
        $ujianLabel = 'Ujian Tugas Akhir';
        
        if ($isSmaOrSmkTeacher) {
            if ($schoolType === 'SMK') {
                $bimbinganLabel = 'Bimbingan Project Akhir';
                $ujianLabel = 'Ujian Project Akhir';
            }
        }
    @endphp
    @if($isSmaOrSmkTeacher)
    <a href="{{ route('guru.final-projects.bimbingan.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.final-projects.bimbingan.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #6d28d9 !important; color: #ffffff !important;"><i class="fas fa-file-signature text-[10px] text-white"></i></div>
        <span>{{ $bimbinganLabel }}</span>
    </a>
    <a href="{{ route('guru.final-projects.ujian.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.final-projects.ujian.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #7e22ce !important; color: #ffffff !important;"><i class="fas fa-graduation-cap text-[10px] text-white"></i></div>
        <span>{{ $ujianLabel }}</span>
    </a>
    @endif
    @endif

    <!-- Pembda Space -->
    <a href="{{ route('forum.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('forum.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #4338ca !important; color: #ffffff !important;"><i class="fas fa-comments text-[10px] text-white"></i></div>
        <span>Pembda Space</span>
    </a>

    @if(\App\Models\Setting::getValue('guru_view_reputation_leaderboard', true))
    <!-- Leaderboard / Hall of Fame -->
    <a href="{{ route('reputation.leaderboard') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('reputation.leaderboard') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #d97706 !important; color: #ffffff !important;"><i class="fas fa-ranking-star text-[10px] text-white"></i></div>
        <span>Hall of Fame</span>
    </a>
    @endif

    <!-- Pelatihan PembdaHUB -->
    <a href="{{ route('training.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('training.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #0284c7 !important; color: #ffffff !important;"><i class="fas fa-book-reader text-[10px] text-white"></i></div>
        <span>Pelatihan PembdaHUB</span>
    </a>

    <!-- Survey Kepuasan -->
    <a href="{{ route('guru.surveys.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.surveys.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #0d9488 !important; color: #ffffff !important;"><i class="fas fa-poll-h text-[10px] text-white"></i></div>
        <span>Survey Kepuasan</span>
    </a>

    {{-- Menu Khusus Panitia & Tugas Tambahan --}}
    @php
        $user = auth()->user();
        $isPanitiaCbt = $user->isPanitiaCbt();
        $isPanitiaPkl = $user->isPanitiaPkl();
        $isPanitiaProyek = $user->isPanitiaProyek();
        $isPksOrPiket = $user->isPksOrPiket();
    @endphp
    @if($isPanitiaCbt || $isPanitiaPkl || $isPanitiaProyek || $isPksOrPiket)
    <div class="px-3 pt-4 pb-1 text-[11px] font-black uppercase tracking-wider text-slate-900 border-t border-slate-200 mt-2">
        Tugas Tambahan & Panitia
    </div>
    @endif
    
    @if($isPanitiaCbt)
    <a href="{{ route('admin.cbt.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.cbt.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #e11d48 !important; color: #ffffff !important;"><i class="fas fa-desktop text-[10px] text-white"></i></div>
        <span>Admin Panitia CBT</span>
    </a>
    @endif
    @if($isPanitiaPkl)
    <a href="{{ route('admin.pkl-alumni.placements.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.pkl-alumni.*', 'admin.dudis.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #2563eb !important; color: #ffffff !important;"><i class="fas fa-user-tie text-[10px] text-white"></i></div>
        <span>Admin Panitia PKL</span>
    </a>
    @endif
    @if($isPanitiaProyek)
    <a href="{{ route('admin.final-projects.proposals.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.final-projects.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #7c3aed !important; color: #ffffff !important;"><i class="fas fa-tasks text-[10px] text-white"></i></div>
        <span>Admin Panitia Tugas/Project Akhir</span>
    </a>
    @endif
    @if($isPksOrPiket)
    <a href="{{ route('admin.counseling.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.counseling.*', 'admin.students.development.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #ea580c !important; color: #ffffff !important;"><i class="fas fa-shield-alt text-[10px] text-white"></i></div>
        <span>Catatan Perkembangan Siswa</span>
    </a>
    @endif

    <!-- Profil -->
    <a href="{{ route('guru.profil') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.profil') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg flex items-center justify-center border border-black shadow-xs" style="background-color: #475569 !important; color: #ffffff !important;"><i class="fas fa-user text-[10px] text-white"></i></div>
        <span>Profil Saya</span>
    </a>
@endsection
