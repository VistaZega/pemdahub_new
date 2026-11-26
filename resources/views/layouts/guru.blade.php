@php
    $userRole = session('active_role', auth()->user()?->role);
    $isPegawaiOnly = ($userRole === 'pegawai');
@endphp

{{--
    Guru Layout — extends unified master layout
    Theme: Emerald/Teal (Modern Clean Slate — Selaras dengan Portal Siswa)
--}}
@extends('layouts.app', [
    'theme'       => 'emerald',
    'sidebarId'   => 'guru-sidebar',
    'storageKey'  => 'guru_sidebar_collapsed',
    'portalName'  => $isPegawaiOnly ? 'Portal Kepegawaian' : 'Portal Guru',
    'portalSub'   => 'PembdaHUB Education System',
    'portalIcon'  => $isPegawaiOnly ? 'fas fa-id-card-clip' : 'fas fa-chalkboard-teacher',
])

@push('styles')
<style>
    /* Modern Clean Design System untuk Portal Guru (selaras dengan Portal Siswa) */
    .gd-wrap {
        max-width: 1320px;
        margin: 0 auto;
        padding-bottom: 2.5rem;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    .gd-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 1.25rem;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
    }
    .gd-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #064e3b 100%);
        border: 1px solid #1e293b;
        border-radius: 1.35rem;
        padding: 1.5rem 1.75rem;
        color: #ffffff;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.25);
        position: relative;
        overflow: hidden;
    }
    .gd-hero::after {
        content: '';
        position: absolute;
        top: -45px;
        right: -45px;
        width: 190px;
        height: 190px;
        border-radius: 9999px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.24) 0%, rgba(16, 185, 129, 0) 70%);
        pointer-events: none;
    }
    /* Harmonisasi lembut untuk sub-halaman guru agar tidak kaku dengan border-black neo-brutalism */
    main .border-black {
        border-color: #cbd5e1 !important;
    }
    main .border-2.border-black {
        border-width: 1px !important;
        border-color: #cbd5e1 !important;
    }
</style>
@endpush

@section('sidebar-menu')
    @php
        $ac = 'bg-emerald-50 text-emerald-800 font-bold active';
        $nc = 'text-slate-700 hover:bg-slate-100/80 hover:text-slate-900 font-medium';

        $isWaliKelas = false;
        $pendingPrestasiCount = 0;
        $activeYearId = \App\Models\AcademicYear::where('is_active', true)->value('id');
        $teacherId = \App\Models\Teacher::where('user_id', auth()->id())->value('id');
        if (!$isPegawaiOnly && $activeYearId && $teacherId) {
            $homeroomClassIds = \App\Models\Classroom::where('homeroom_teacher_id', $teacherId)
                ->where('academic_year_id', $activeYearId)
                ->where('is_active', true)
                ->pluck('id');
            $isWaliKelas = $homeroomClassIds->isNotEmpty();
            if ($isWaliKelas) {
                $pendingPrestasiCount = \App\Models\StudentAchievement::whereHas('student.classrooms', function($q) use ($homeroomClassIds) {
                    $q->whereIn('classrooms.id', $homeroomClassIds);
                })->where('status', 'pending')->count();
            }
        }
    @endphp

    <!-- Dashboard -->
    <a href="{{ route('guru.dashboard') }}" class="menu-item flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('guru.dashboard') ? $ac : 'text-slate-700 hover:bg-slate-100/80' }}">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-xs">
            <i class="fas fa-home text-xs"></i>
        </div>
        <span class="text-sm flex-1 font-semibold">Dashboard</span>
    </a>

    <!-- PembdaCIRCLE Guru (Panggung Inspirasi & Komunitas) -->
    <a href="{{ route('circle.feed') }}" class="menu-item flex items-center justify-between px-3 py-2 rounded-xl text-sm {{ request()->routeIs('circle.*') ? 'bg-pink-50 text-pink-900 font-bold border border-pink-200' : 'text-slate-800 hover:bg-pink-50/60' }}" title="PembdaCIRCLE — CONNECT • CREATE • GROW">
        <div class="flex items-center gap-2.5">
            <x-circle-logo size="xs" variant="emblem" />
            <div class="flex flex-col leading-tight">
                <span class="font-extrabold text-slate-900">Pembda<span class="text-pink-600">CIRCLE</span></span>
                <span class="text-[8px] font-bold text-pink-600 tracking-wider">CONNECT • CREATE • GROW</span>
            </div>
        </div>
        <span class="text-[9px] font-bold bg-pink-100 text-pink-800 px-1.5 py-0.5 rounded-full border border-pink-200">Sosial</span>
    </a>

    @if(!$isPegawaiOnly)
    <!-- Jadwal Mengajar -->
    <a href="{{ route('guru.jadwal') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.jadwal') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-calendar-alt text-[10px]"></i></div>
        <span>Jadwal Mengajar</span>
    </a>
    @endif

    <!-- Kalender Pendidikan -->
    <a href="{{ route('guru.calendar.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.calendar.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-calendar-day text-[10px]"></i></div>
        <span>Kalender Pendidikan</span>
    </a>

    <!-- Surat Edaran Yayasan -->
    <a href="{{ route('admin.letters.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.letters.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-file-signature text-[10px]"></i></div>
        <span>Surat Edaran Yayasan</span>
    </a>

    @if(auth()->user()?->canManageInventory())
    <!-- PembdaINVENTORY (Manajemen Aset) -->
    <a href="{{ route('inventory.index') }}" class="menu-item flex items-center justify-between px-3 py-2 rounded-xl text-sm {{ request()->routeIs('inventory.*') ? $ac : $nc }}">
        <div class="flex items-center gap-3">
            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-600 to-slate-800 flex items-center justify-center text-white shadow-xs">
                <i class="fas fa-boxes-stacked text-[10px]"></i>
            </div>
            <span class="font-semibold">PembdaINVENTORY</span>
        </div>
        <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">Aset</span>
    </a>
    @endif

    @if(!$isPegawaiOnly)
    <!-- Kelas Saya -->
    <a href="{{ route('guru.kelas') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.kelas', 'guru.siswa-kelas') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-users text-[10px]"></i></div>
        <span>Kelas Saya</span>
    </a>

    @if($isWaliKelas)
    <!-- Kelola & Edit Data Siswa (Wali Kelas) -->
    <a href="{{ route('admin.students.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.students.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-user-pen text-[10px]"></i></div>
        <span>Kelola & Edit Data Siswa</span>
    </a>

    <!-- Justifikasi Prestasi Siswa (Wali Kelas) -->
    <a href="{{ route('guru.prestasi-siswa.index') }}" class="menu-item flex items-center justify-between px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.prestasi-siswa.*') ? $ac : $nc }}">
        <div class="flex items-center gap-3">
            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-trophy text-[10px]"></i></div>
            <span>Justifikasi Prestasi</span>
        </div>
        @if(isset($pendingPrestasiCount) && $pendingPrestasiCount > 0)
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                {{ $pendingPrestasiCount }}
            </span>
        @endif
    </a>

    <!-- Biaya Pendidikan -->
    <a href="{{ route('guru.tagihan-siswa') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.tagihan-siswa') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-cyan-500 to-teal-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-file-invoice-dollar text-[10px]"></i></div>
        <span>Biaya Pendidikan</span>
    </a>

    <!-- Dispensasi Ujian CBT (Wali Kelas) -->
    <a href="{{ route('guru.walikelas.cbt-dispensasi.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.walikelas.cbt-dispensasi.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-500 to-yellow-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-shield-halved text-[10px]"></i></div>
        <span>Dispensasi Ujian CBT</span>
    </a>

    <!-- Monitoring LMS Kelas (Wali Kelas) -->
    <a href="{{ route('guru.walikelas.lms-monitoring') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.walikelas.lms-monitoring*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-orange-400 to-amber-500 flex items-center justify-center text-white shadow-xs"><i class="fas fa-chart-pie text-[10px]"></i></div>
        <span>Pantauan LMS Rombel</span>
    </a>
    @endif

    @if(\App\Models\Setting::getValue('guru_access_lms', true))
    <!-- LMS -->
    <a href="{{ route('guru.lms.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.lms.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-orange-500 to-rose-500 flex items-center justify-center text-white shadow-xs"><i class="fas fa-laptop text-[10px]"></i></div>
        <span>LMS</span>
    </a>
    @if(request()->routeIs('guru.lms.*'))
    <div class="ml-10 -mt-0.5 mb-1 space-y-0.5">
        <a href="{{ route('guru.lms.index') }}" class="block text-xs px-3 py-1.5 rounded-lg transition {{ request()->routeIs('guru.lms.index') ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-slate-600 font-medium hover:text-slate-900 hover:bg-slate-100' }}">
            <i class="fas fa-book-open mr-1.5 text-[10px]"></i>Kursus Saya
        </a>
        <a href="{{ route('guru.lms.monitoring.index') }}" class="block text-xs px-3 py-1.5 rounded-lg transition {{ request()->routeIs('guru.lms.monitoring.*') ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-slate-600 font-medium hover:text-slate-900 hover:bg-slate-100' }}">
            <i class="fas fa-chart-line mr-1.5 text-[10px]"></i>Pantauan Siswa (Mapel Saya)
        </a>
    </div>
    @endif
    @endif
    @endif

    <!-- Pembda Knowledge & Media -->
    <a href="{{ route('guru.knowledge.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.knowledge.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-cube text-[10px]"></i></div>
        <span>Knowledge & Media</span>
    </a>

    <!-- PembdaHUB Simulator Lab -->
    <a href="{{ route('simlab.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('simlab.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-500 to-cyan-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-microchip text-[10px]"></i></div>
        <span>Simulator Lab</span>
    </a>

    @if(!$isPegawaiOnly)
    <!-- Nilai Siswa -->
    <a href="{{ route('guru.nilai') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.nilai*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-chart-bar text-[10px]"></i></div>
        <span>Nilai Siswa</span>
    </a>
    @if(request()->routeIs('guru.nilai*'))
    <div class="ml-10 -mt-0.5 mb-1 space-y-0.5">
        <a href="{{ route('guru.nilai') }}" class="block text-xs px-3 py-1.5 rounded-lg transition {{ request()->routeIs('guru.nilai') && !request()->routeIs('guru.nilai.*') ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-slate-600 font-medium hover:text-slate-900 hover:bg-slate-100' }}">
            <i class="fas fa-eye mr-1.5 text-[10px]"></i>Lihat Nilai
        </a>
        <a href="{{ route('guru.nilai.input') }}" class="block text-xs px-3 py-1.5 rounded-lg transition {{ request()->routeIs('guru.nilai.input') ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-slate-600 font-medium hover:text-slate-900 hover:bg-slate-100' }}">
            <i class="fas fa-edit mr-1.5 text-[10px]"></i>Input Nilai
        </a>
        <a href="{{ route('guru.nilai.summary') }}" class="block text-xs px-3 py-1.5 rounded-lg transition {{ request()->routeIs('guru.nilai.summary') ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-slate-600 font-medium hover:text-slate-900 hover:bg-slate-100' }}">
            <i class="fas fa-table mr-1.5 text-[10px]"></i>Rekap Nilai
        </a>
    </div>
    @endif

    @if(\App\Models\Setting::getValue('guru_access_cbt', true))
    <!-- CBT / Ujian -->
    <a href="{{ route('guru.cbt.banks.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.cbt.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-600 to-cyan-700 flex items-center justify-center text-white shadow-xs"><i class="fas fa-desktop text-[10px]"></i></div>
        <span>CBT / Ujian</span>
    </a>
    @if(request()->routeIs('guru.cbt.*'))
    <div class="ml-10 -mt-0.5 mb-1 space-y-0.5">
        <a href="{{ route('guru.cbt.banks.index') }}" class="block text-xs px-3 py-1.5 rounded-lg transition {{ request()->routeIs('guru.cbt.banks.*') ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-slate-600 font-medium hover:text-slate-900 hover:bg-slate-100' }}">
            <i class="fas fa-database mr-1.5 text-[10px]"></i>Bank Soal
        </a>
        <a href="{{ route('guru.cbt.exams.index') }}" class="block text-xs px-3 py-1.5 rounded-lg transition {{ request()->routeIs('guru.cbt.exams.*') ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-slate-600 font-medium hover:text-slate-900 hover:bg-slate-100' }}">
            <i class="fas fa-file-circle-check mr-1.5 text-[10px]"></i>Kelola Ujian
        </a>
    </div>
    @endif
    @endif
    @endif

    <!-- Perjanjian Kinerja -->
    <a href="{{ route('guru.performance_contracts.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.performance_contracts.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-rose-500 to-red-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-file-signature text-[10px]"></i></div>
        <span>Perjanjian Kinerja</span>
    </a>

    <!-- Pemilihan OSIS -->
    <a href="{{ route('osis.booth') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('osis.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-700 flex items-center justify-center text-white shadow-xs">
            <i class="fas fa-check-to-slot text-[10px]"></i>
        </div>
        <span>Pemilihan OSIS</span>
    </a>

    @if(!$isPegawaiOnly)
    <!-- Asisten AI -->
    <div data-menu-group="asisten-ai" class="menu-group">
        <button onclick="toggleGroup(this)" class="menu-group-toggle w-full menu-item flex items-center justify-between px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.ai.*') ? $ac : $nc }}">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-500 to-indigo-600 flex items-center justify-center text-white shadow-xs">
                    <i class="fas fa-magic text-[10px]"></i>
                </div>
                <span class="text-sm">Asisten AI</span>
            </div>
            <i class="fas fa-chevron-right text-[10px] chevron transition-transform {{ request()->routeIs('guru.ai.*') ? 'rotate-90' : '' }}"></i>
        </button>
        <div class="menu-group-body ml-10 mt-1 space-y-0.5 {{ request()->routeIs('guru.ai.*') ? '' : 'closed' }}">
            <a href="{{ route('guru.ai.lesson-plan') }}" class="block text-xs px-3 py-1.5 rounded-lg transition {{ request()->routeIs('guru.ai.lesson-plan') ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-slate-600 font-medium hover:text-slate-900 hover:bg-slate-100' }}">
                <i class="fas fa-wand-magic-sparkles mr-1.5 text-[10px]"></i>RPP Generator
            </a>
            <a href="{{ route('guru.ai.question-generator') }}" class="block text-xs px-3 py-1.5 rounded-lg transition {{ request()->routeIs('guru.ai.question-generator') ? 'bg-emerald-50 text-emerald-800 font-bold' : 'text-slate-600 font-medium hover:text-slate-900 hover:bg-slate-100' }}">
                <i class="fas fa-brain mr-1.5 text-[10px]"></i>Pembuat Soal CBT
            </a>
        </div>
    </div>
    @endif

    <!-- Absensi Siswa -->
    <a href="{{ route('guru.absensi') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.absensi*') && !request()->routeIs('guru.absensi.saya') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-purple-500 to-fuchsia-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-user-check text-[10px]"></i></div>
        <span>Absensi Siswa</span>
    </a>

    @if(\App\Models\Setting::getValue('pegawai_view_attendance_recap', true))
    <!-- Absensi Saya -->
    <a href="{{ route('guru.absensi.saya') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.absensi.saya') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-500 to-cyan-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-clipboard-user text-[10px]"></i></div>
        <span>Absensi Saya</span>
    </a>
    @endif

    @if(\App\Models\Setting::getValue('pegawai_can_request_leave', true))
    <!-- Pengajuan Cuti -->
    <a href="{{ route('guru.leaves.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.leaves.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-calendar-times text-[10px]"></i></div>
        <span>Pengajuan Cuti</span>
    </a>
    @endif

    @if(!$isPegawaiOnly)
    <!-- Raport -->
    <a href="{{ route('guru.raport.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.raport.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-file-alt text-[10px]"></i></div>
        <span>Raport Akademik</span>
    </a>
    <a href="{{ route('guru.dna.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.dna.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-fuchsia-500 to-indigo-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-dna text-[10px]"></i></div>
        <span>DNA Akademik 360°</span>
    </a>
    <a href="{{ route('guru.extracurricular.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.extracurricular.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-400 to-blue-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-palette text-[10px]"></i></div>
        <span>Ekstrakurikuler</span>
    </a>

    <!-- Monitoring PKL -->
    @php
        $teacherId = \App\Models\Teacher::where('user_id', auth()->id())->value('id');
        $hasPkl = \App\Models\PklPlacement::where('teacher_id', $teacherId)->exists();
    @endphp
    @if($hasPkl)
    <a href="{{ route('guru.pkl.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.pkl.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-blue-700 flex items-center justify-center text-white shadow-xs"><i class="fas fa-briefcase text-[10px]"></i></div>
        <span>Bimbingan PKL (Siswa)</span>
    </a>
    <a href="{{ route('guru.pkl_monitorings.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.pkl_monitorings.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-700 flex items-center justify-center text-white shadow-xs"><i class="fas fa-file-invoice text-[10px]"></i></div>
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

        if ($isSmaOrSmkTeacher && $schoolType === 'SMK') {
            $bimbinganLabel = 'Bimbingan Project Akhir';
            $ujianLabel = 'Ujian Project Akhir';
        }
    @endphp
    @if($isSmaOrSmkTeacher)
    <a href="{{ route('guru.final-projects.bimbingan.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.final-projects.bimbingan.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-violet-500 to-purple-700 flex items-center justify-center text-white shadow-xs"><i class="fas fa-file-signature text-[10px]"></i></div>
        <span>{{ $bimbinganLabel }}</span>
    </a>
    <a href="{{ route('guru.final-projects.ujian.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.final-projects.ujian.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-purple-500 to-fuchsia-700 flex items-center justify-center text-white shadow-xs"><i class="fas fa-graduation-cap text-[10px]"></i></div>
        <span>{{ $ujianLabel }}</span>
    </a>
    @endif
    @endif

    @if(\App\Services\CirclePointService::isSpaceActive())
    <!-- PembdaSPACE -->
    <a href="{{ route('forum.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('forum.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-600 to-blue-700 flex items-center justify-center text-white shadow-xs"><i class="fas fa-bolt text-[11px] text-amber-300"></i></div>
        <span>PembdaSPACE</span>
    </a>
    @endif

    @if(\App\Models\Setting::getValue('guru_view_reputation_leaderboard', true))
    <!-- Leaderboard / Hall of Fame -->
    <a href="{{ route('reputation.leaderboard') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('reputation.leaderboard') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-ranking-star text-[10px]"></i></div>
        <span>Hall of Fame</span>
    </a>
    @endif

    <!-- Pelatihan PembdaHUB -->
    <a href="{{ route('training.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('training.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-book-reader text-[10px]"></i></div>
        <span>Pelatihan PembdaHUB</span>
    </a>

    <!-- Survey Kepuasan -->
    <a href="{{ route('guru.surveys.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.surveys.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-poll-h text-[10px]"></i></div>
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
    <div class="px-3 pt-4 pb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-t border-slate-200 mt-2">
        Tugas Tambahan & Panitia
    </div>
    @endif

    @if($isPanitiaCbt)
    <a href="{{ route('admin.cbt.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.cbt.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-rose-500 to-red-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-desktop text-[10px]"></i></div>
        <span>Admin Panitia CBT</span>
    </a>
    @endif
    @if($isPanitiaPkl)
    <a href="{{ route('admin.pkl-alumni.placements.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.pkl-alumni.*', 'admin.dudis.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-user-tie text-[10px]"></i></div>
        <span>Admin Panitia PKL</span>
    </a>
    @endif
    @if($isPanitiaProyek)
    <a href="{{ route('admin.final-projects.proposals.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.final-projects.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-purple-500 to-violet-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-tasks text-[10px]"></i></div>
        <span>Admin Panitia Tugas/Project Akhir</span>
    </a>
    @endif
    @if($isPksOrPiket)
    <a href="{{ route('admin.counseling.index') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('admin.counseling.*', 'admin.students.development.*') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-orange-500 to-amber-600 flex items-center justify-center text-white shadow-xs"><i class="fas fa-shield-alt text-[10px]"></i></div>
        <span>Catatan Perkembangan Siswa</span>
    </a>
    @endif

    <!-- Profil -->
    <a href="{{ route('guru.profil') }}" class="menu-item flex items-center gap-3 px-3 py-2 rounded-xl text-sm {{ request()->routeIs('guru.profil') ? $ac : $nc }}">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-slate-500 to-slate-700 flex items-center justify-center text-white shadow-xs"><i class="fas fa-user text-[10px]"></i></div>
        <span>Profil Saya</span>
    </a>
@endsection
