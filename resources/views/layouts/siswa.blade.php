{{--
    Siswa Layout — extends unified master layout
    Theme: Amber/Orange
--}}
@extends('layouts.app', [
    'theme'       => 'amber',
    'sidebarId'   => 'siswa-sidebar',
    'storageKey'  => 'siswa_sidebar_collapsed',
    'portalName'  => 'Portal Siswa',
    'portalSub'   => 'PembdaHUB Education System',
    'portalIcon'  => 'fas fa-user-graduate',
])

@section('sidebar-menu')
    @php
        $ac = 'bg-amber-50 text-amber-700 font-semibold active';
        $nc = 'text-gray-600 hover:bg-gray-50';
    @endphp

    <a href="{{ route('siswa.dashboard') }}" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('siswa.dashboard') ? $ac : 'text-gray-700 hover:bg-gray-50' }}">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-400 to-yellow-600 flex items-center justify-center text-white shadow"><i class="fas fa-home text-xs"></i></div>
        <span class="text-sm flex-1 font-semibold">Dashboard</span>
    </a>

    <a href="{{ route('siswa.jadwal') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('siswa.jadwal') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white"><i class="fas fa-calendar-alt text-[10px]"></i></div>
        <span>Jadwal Pelajaran</span>
    </a>


    <a href="{{ route('siswa.nilai') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('siswa.nilai') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center text-white"><i class="fas fa-chart-bar text-[10px]"></i></div>
        <span>Nilai & Rapor</span>
    </a>

    <a href="{{ route('siswa.dna.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('siswa.dna.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-fuchsia-500 to-indigo-600 flex items-center justify-center text-white"><i class="fas fa-dna text-[10px]"></i></div>
        <span class="font-bold text-indigo-950">DNA Akademik 360°</span>
    </a>

    <a href="{{ route('siswa.ekskul.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('siswa.ekskul.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white"><i class="fas fa-palette text-[10px]"></i></div>
        <span class="font-bold text-slate-900">Ekstrakurikuler</span>
    </a>

    <a href="{{ route('siswa.tagihan') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('siswa.tagihan') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-red-400 to-rose-600 flex items-center justify-center text-white"><i class="fas fa-file-invoice-dollar text-[10px]"></i></div>
        <span>Biaya Pendidikan</span>
    </a>

    @if(\App\Models\Setting::getValue('siswa_view_attendance_recap', true))
    <a href="{{ route('siswa.absensi') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('siswa.absensi') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white"><i class="fas fa-clipboard-check text-[10px]"></i></div>
        <span>Absensi</span>
    </a>
    @endif

    @if(\App\Models\Setting::getValue('siswa_access_lms', true))
    <a href="{{ route('siswa.lms.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('siswa.lms.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-blue-600 flex items-center justify-center text-white"><i class="fas fa-book-open text-[10px]"></i></div>
        <span>LMS</span>
    </a>
    @endif

    <!-- Knowledge & Media -->
    <a href="{{ route('knowledge.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('knowledge.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-400 to-emerald-600 flex items-center justify-center text-white"><i class="fas fa-cube text-[10px]"></i></div>
        <span>Knowledge & Media</span>
    </a>

    <!-- PembdaHUB Simulator Lab -->
    <a href="{{ route('simlab.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('simlab.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-400 to-cyan-600 flex items-center justify-center text-white"><i class="fas fa-flask text-[10px]"></i></div>
        <span>Simulator Lab</span>
    </a>

    @if(\App\Models\Setting::getValue('siswa_access_cbt', true))
    <a href="{{ route('siswa.cbt.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('siswa.cbt.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-cyan-400 to-teal-600 flex items-center justify-center text-white"><i class="fas fa-desktop text-[10px]"></i></div>
        <span>CBT / Ujian</span>
    </a>
    @if(request()->routeIs('siswa.cbt.*'))
    <div class="ml-11 -mt-0.5 mb-1 space-y-0.5">
        <a href="{{ route('siswa.cbt.index') }}" class="block text-xs px-3 py-1.5 rounded-lg transition {{ request()->routeIs('siswa.cbt.index') ? 'bg-amber-50 text-amber-700 font-semibold' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
            <i class="fas fa-list mr-1.5 text-[10px]"></i>Ujian Tersedia
        </a>
        <a href="{{ route('siswa.cbt.history') }}" class="block text-xs px-3 py-1.5 rounded-lg transition {{ request()->routeIs('siswa.cbt.history') ? 'bg-amber-50 text-amber-700 font-semibold' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
            <i class="fas fa-clock-rotate-left mr-1.5 text-[10px]"></i>Riwayat Ujian
        </a>
    </div>
    @endif
    @endif

    <a href="{{ route('siswa.konseling') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('siswa.konseling') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-pink-400 to-rose-600 flex items-center justify-center text-white"><i class="fas fa-clipboard-list text-[10px]"></i></div>
        <span>Catatan Perkembangan</span>
    </a>

    <a href="{{ route('forum.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('forum.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white"><i class="fas fa-comments text-[10px]"></i></div>
        <span>Pembda Space</span>
    </a>

    @if(\App\Models\Setting::getValue('siswa_view_reputation_leaderboard', true))
    <a href="{{ route('reputation.leaderboard') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('reputation.leaderboard') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white"><i class="fas fa-ranking-star text-[10px]"></i></div>
        <span>Hall of Fame</span>
    </a>
    @endif

    @php
        $siswaSchoolType = Auth::user()?->student?->school?->type;
        $siswaGradeLevel = Auth::user()?->student?->currentClassroom()?->first()?->grade_level;
        $isKelasXII = $siswaGradeLevel == 12;
    @endphp

    @if($siswaSchoolType === 'SMK' && $isKelasXII)
    <a href="{{ route('siswa.pkl.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('siswa.pkl.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white"><i class="fas fa-briefcase text-[10px]"></i></div>
        <span>Praktik Kerja (PKL)</span>
    </a>
    @endif

    @if($isKelasXII)
        @if($siswaSchoolType === 'SMK')
            <a href="{{ route('siswa.final-project.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('siswa.final-project.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white"><i class="fas fa-file-alt text-[10px]"></i></div>
                <span>Project Akhir</span>
            </a>
        @elseif($siswaSchoolType === 'SMA')
            <a href="{{ route('siswa.final-project.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('siswa.final-project.*') ? $ac : $nc }}">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white"><i class="fas fa-file-alt text-[10px]"></i></div>
                <span>Tugas Akhir</span>
            </a>
        @endif
    @endif

    @if(!Auth::user()->student || in_array(Auth::user()->student->status, ['lulus', 'alumni']))
    <a href="{{ route('alumni.tracer.form') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('alumni.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-400 to-emerald-600 flex items-center justify-center text-white"><i class="fas fa-graduation-cap text-[10px]"></i></div>
        <span>Portal Alumni (BMW)</span>
    </a>
    @endif

    <a href="{{ route('training.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('training.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-sky-400 to-cyan-600 flex items-center justify-center text-white"><i class="fas fa-book-reader text-[10px]"></i></div>
        <span>Pelatihan PembdaHUB</span>
    </a>

    <!-- Survey Kepuasan -->
    <a href="{{ route('siswa.surveys.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('siswa.surveys.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-400 to-emerald-600 flex items-center justify-center text-white"><i class="fas fa-poll-h text-[10px]"></i></div>
        <span>Survey Kepuasan</span>
    </a>

    <a href="{{ route('siswa.profil') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('siswa.profil') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center text-white"><i class="fas fa-user text-[10px]"></i></div>
        <span>Profil Saya</span>
    </a>
@endsection
