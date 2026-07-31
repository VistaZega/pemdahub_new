@extends('layouts.admin')

@section('title', $isSuperAdmin ? 'Dashboard Super Admin' : ($isKepsek ? 'Dashboard Kepala Sekolah' : 'Dashboard Admin Sekolah'))

@section('content')
<div class="space-y-6">
    {{-- Hero Header Section --}}
    <div class="relative overflow-hidden rounded-2xl p-6 md:p-8 shadow-lg bg-gradient-to-br {{ $isSuperAdmin ? 'from-indigo-900 via-indigo-800 to-slate-900' : 'from-emerald-900 via-teal-800 to-slate-900' }} border border-white/10">
        <!-- Glassmorphism Background Accent Blobs -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-indigo-500/20 rounded-full blur-3xl translate-y-1/3 -translate-x-1/4 pointer-events-none"></div>
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10 mix-blend-overlay pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-3">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="px-3.5 py-1.5 bg-white/15 backdrop-blur-md rounded-xl text-xs font-black uppercase tracking-wider text-white border border-white/20 shadow-inner flex items-center gap-1.5">
                        <i class="fas fa-crown text-amber-300 text-sm"></i> {{ $isSuperAdmin ? 'Super Admin' : ($isKepsek ? 'Kepala Sekolah' : 'Admin Sekolah') }}
                    </span>
                    @if($currentSemester)
                        <span class="px-3.5 py-1.5 bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 rounded-xl text-xs font-black uppercase tracking-wider shadow-md flex items-center gap-1.5">
                            <i class="fas fa-clock text-slate-900 text-xs"></i> {{ $currentSemester->semester_name }}
                        </span>
                    @endif
                </div>
                <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold tracking-tight text-white drop-shadow-sm">
                    {{ $isSuperAdmin ? 'Pusat Kendali Ekosistem' : ($school->name ?? 'Dashboard Sekolah') }}
                </h1>
                <p class="text-slate-100 text-sm md:text-base max-w-2xl font-medium leading-relaxed">
                    {{ $isSuperAdmin ? 'Pantau seluruh operasional Yayasan Perguruan PEMBDA Nias secara real-time melalui panel kendali ini.' : 'Kelola kegiatan akademik dan operasional harian sekolah Anda dengan wawasan data yang akurat.' }}
                </p>
            </div>
            
            <div class="flex items-center gap-3 shrink-0">
                @if($currentAcademicYear)
                    <div class="bg-white/15 backdrop-blur-md px-4 py-3 rounded-xl border border-white/20 text-center shadow-lg transform transition hover:scale-105">
                        <p class="text-[11px] uppercase font-extrabold text-amber-300 tracking-wider mb-0.5">Tahun Pelajaran</p>
                        <p class="text-lg font-black text-white tracking-wide">{{ $currentAcademicYear->year }}</p>
                    </div>
                @endif
                <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center border border-white/20 shadow-xl shrink-0">
                    <i class="fas {{ $isSuperAdmin ? 'fa-globe-asia' : 'fa-school' }} text-2xl text-white"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Banner Surat Edaran Yayasan Terbaru --}}
    @if(isset($foundationLetters) && $foundationLetters->isNotEmpty())
    <div class="bg-gradient-to-r from-purple-900 via-indigo-900 to-slate-900 rounded-2xl p-5 text-white shadow-md border border-purple-500/30 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-xl bg-amber-400/20 border border-amber-400/40 flex items-center justify-center text-amber-300 text-lg shrink-0 shadow-inner">
                <i class="fas fa-file-signature"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                    <span class="bg-amber-400 text-slate-950 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider">
                        <i class="fas fa-bullhorn mr-1"></i> Surat Edaran Resmi Yayasan
                    </span>
                    @if($foundationLetters->first()->deadline_date)
                        <span class="bg-rose-500/30 text-rose-100 border border-rose-400/40 px-2 py-0.5 rounded text-[10px] font-bold">
                            <i class="fas fa-clock mr-1"></i>Tenggat: {{ \Carbon\Carbon::parse($foundationLetters->first()->deadline_date)->translatedFormat('d M Y') }}
                        </span>
                    @endif
                </div>
                <h4 class="font-bold text-base text-white line-clamp-1">
                    {{ $foundationLetters->first()->title }}
                </h4>
                <p class="text-xs text-purple-200 mt-0.5 font-medium">
                    No. Surat: {{ $foundationLetters->first()->letter_number }} • Tanggal Terbit: {{ \Carbon\Carbon::parse($foundationLetters->first()->effective_date)->translatedFormat('d F Y') }}
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0 self-end md:self-center">
            <a href="{{ route('admin.letters.show', $foundationLetters->first()->id) }}" class="bg-amber-400 hover:bg-amber-500 text-slate-950 font-black px-4 py-2 rounded-xl text-xs shadow transition-all flex items-center gap-2">
                <i class="fas fa-eye"></i> Baca Surat
            </a>
            <a href="{{ route('admin.letters.index') }}" class="bg-white/10 hover:bg-white/20 text-white font-bold px-3 py-2 rounded-xl text-xs transition-all border border-white/15">
                Lihat Semua ({{ $foundationLetters->count() }})
            </a>
        </div>
    </div>
    @endif

    {{-- Main Stats Grid (KPI Cards) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        {{-- Total Siswa --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 group relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-indigo-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-in-out opacity-70"></div>
            <div class="relative flex items-start justify-between z-10">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-slate-600 uppercase tracking-wider">Total Siswa</p>
                    <h3 class="text-3xl font-black text-slate-900 tracking-tight">{{ number_format($totalStudents) }}</h3>
                    <div class="flex items-center gap-1.5 pt-1">
                        <span class="flex h-2.5 w-2.5 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </span>
                        <p class="text-xs text-slate-700 font-bold">{{ number_format($activeStudents) }} Aktif Ber-Rombel (TP {{ $currentAcademicYear->year ?? '' }})</p>
                    </div>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white shadow-md shadow-indigo-500/20 group-hover:rotate-6 transition shrink-0">
                    <i class="fas fa-user-graduate text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Tenaga Pengajar --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 group relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-emerald-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-in-out opacity-70"></div>
            <div class="relative flex items-start justify-between z-10">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-slate-600 uppercase tracking-wider">Tenaga Pengajar</p>
                    <h3 class="text-3xl font-black text-slate-900 tracking-tight">{{ number_format($totalTeachers) }}</h3>
                    <p class="text-xs text-slate-700 font-bold pt-1"><i class="fas fa-users text-emerald-600 mr-1"></i>{{ number_format($totalEmployees) }} Total Pegawai</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center text-white shadow-md shadow-emerald-500/20 group-hover:-rotate-6 transition shrink-0">
                    <i class="fas fa-chalkboard-teacher text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Unit / Ruang Kelas --}}
        @if($isSuperAdmin)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 group relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-purple-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-in-out opacity-70"></div>
            <div class="relative flex items-start justify-between z-10">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-slate-600 uppercase tracking-wider">Unit Sekolah</p>
                    <h3 class="text-3xl font-black text-slate-900 tracking-tight">{{ number_format($totalSchools) }}</h3>
                    <p class="text-xs text-slate-700 font-bold pt-1"><i class="fas fa-sitemap text-purple-600 mr-1"></i>Tingkat SMP, SMA, SMK</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-fuchsia-600 rounded-xl flex items-center justify-center text-white shadow-md shadow-purple-500/20 group-hover:rotate-6 transition shrink-0">
                    <i class="fas fa-building text-xl"></i>
                </div>
            </div>
        </div>
        @else
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 group relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-purple-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-in-out opacity-70"></div>
            <div class="relative flex items-start justify-between z-10">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-slate-600 uppercase tracking-wider">Ruang Kelas</p>
                    <h3 class="text-3xl font-black text-slate-900 tracking-tight">{{ number_format($totalClassrooms) }}</h3>
                    <p class="text-xs text-slate-700 font-bold pt-1"><i class="fas fa-door-open text-purple-600 mr-1"></i>Rombel Aktif</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-fuchsia-600 rounded-xl flex items-center justify-center text-white shadow-md shadow-purple-500/20 group-hover:rotate-6 transition shrink-0">
                    <i class="fas fa-door-open text-xl"></i>
                </div>
            </div>
        </div>
        @endif

        {{-- CBT & LMS --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 group relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-amber-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-in-out opacity-70"></div>
            <div class="relative flex items-start justify-between z-10">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-slate-600 uppercase tracking-wider">CBT & Digital</p>
                    <h3 class="text-3xl font-black text-slate-900 tracking-tight">{{ number_format($totalCbtExams) }}</h3>
                    <p class="text-xs text-slate-700 font-bold pt-1"><i class="fas fa-book-reader text-amber-600 mr-1"></i>{{ number_format($activeLmsCourses) }} Kursus LMS</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center text-white shadow-md shadow-amber-500/20 group-hover:-rotate-6 transition shrink-0">
                    <i class="fas fa-laptop-code text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Operational Indicators Section --}}
    <h2 class="text-base font-extrabold text-slate-800 mt-6 mb-3 flex items-center gap-2">
        <i class="fas fa-chart-pie text-indigo-600"></i> Indikator Operasional
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Keuangan (Tagihan) -->
        <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 rounded-2xl p-5 text-white shadow-md border border-slate-700/60 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-32 h-32 bg-white/5 rounded-full -mr-12 -mt-12 group-hover:scale-150 transition-transform duration-500 pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-center mb-3">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-300">Status Tagihan</h4>
                    <i class="fas fa-wallet text-amber-400 text-lg"></i>
                </div>
                <div class="flex items-baseline gap-2 mb-2">
                    <span class="text-3xl font-black text-white">{{ $billPaidPercentage }}%</span>
                    <span class="text-xs font-bold text-slate-300">Telah Lunas</span>
                </div>
                <div class="w-full bg-slate-700/80 rounded-full h-2.5 mb-2 overflow-hidden shadow-inner">
                    <div class="bg-gradient-to-r from-emerald-400 to-teal-400 h-2.5 rounded-full shadow-lg transition-all duration-1000" style="width: {{ $billPaidPercentage }}%"></div>
                </div>
                <p class="text-xs text-slate-300 font-medium mt-2"><span class="text-emerald-400 font-extrabold">{{ number_format($paidBillsCount) }}</span> dari {{ number_format($totalBillsCount) }} tagihan tercatat</p>
            </div>
        </div>

        <!-- PSB (Penerimaan Siswa Baru) -->
        <div class="bg-gradient-to-br from-indigo-600 to-blue-700 rounded-2xl p-5 text-white shadow-md border border-indigo-500/40 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full -mr-12 -mt-12 group-hover:scale-150 transition-transform duration-500 pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-center mb-3">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-indigo-100">PSB & Admisi</h4>
                    <i class="fas fa-user-plus text-indigo-200 text-lg"></i>
                </div>
                <div class="flex items-baseline gap-2 mb-3">
                    <span class="text-3xl font-black text-white">{{ number_format($totalApplicants) }}</span>
                    <span class="text-xs font-bold text-indigo-100">Pendaftar</span>
                </div>
                <div class="bg-white/15 backdrop-blur-md rounded-xl p-3 border border-white/20">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-white">Sedang Diproses</span>
                        <span class="font-black bg-white text-indigo-700 px-2.5 py-0.5 rounded-full shadow-sm">{{ number_format($pendingApplicants) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- BK (Bimbingan Konseling) -->
        <div class="bg-gradient-to-br from-rose-600 to-pink-700 rounded-2xl p-5 text-white shadow-md border border-rose-500/40 relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full -mr-12 -mt-12 group-hover:scale-150 transition-transform duration-500 pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-center mb-3">
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-rose-100">Bimbingan Konseling</h4>
                    <i class="fas fa-hands-helping text-rose-200 text-lg"></i>
                </div>
                <div class="flex items-baseline gap-2 mb-2">
                    <span class="text-3xl font-black text-white">{{ number_format($activeCounselings) }}</span>
                    <span class="text-xs font-bold text-rose-100">Kasus Aktif</span>
                </div>
                <div class="flex items-center gap-2.5 text-xs text-rose-100 font-medium pt-1">
                    <i class="fas fa-info-circle text-amber-300 text-base shrink-0"></i>
                    <p class="leading-snug text-xs">Catatan BK yang sedang dalam proses penanganan / belum ditutup.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Analytics Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        
        {{-- Left Column (Charts & Progress) --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Academic & Operational Progress --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
                 <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider mb-5 flex items-center gap-2">
                    <i class="fas fa-chart-line text-emerald-600"></i> Metrik Kinerja Utama
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-emerald-50/70 rounded-2xl p-4 border border-emerald-200/60 flex flex-col justify-center items-center text-center hover:bg-emerald-50 transition duration-200 group">
                        <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-lg mb-2 shadow-md shadow-emerald-500/20 group-hover:scale-110 transition-transform">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <p class="text-3xl font-black text-slate-900 mb-0.5">{{ $cumulativeRate }}%</p>
                        <p class="text-xs font-bold text-slate-700 uppercase tracking-wider">Kehadiran Siswa</p>
                    </div>
                    <div class="bg-blue-50/70 rounded-2xl p-4 border border-blue-200/60 flex flex-col justify-center items-center text-center hover:bg-blue-50 transition duration-200 group">
                        <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center text-lg mb-2 shadow-md shadow-blue-500/20 group-hover:scale-110 transition-transform">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        <p class="text-3xl font-black text-slate-900 mb-0.5">{{ number_format($activeLmsCourses) }}</p>
                        <p class="text-xs font-bold text-slate-700 uppercase tracking-wider">Kursus Digital</p>
                    </div>
                    <div class="bg-amber-50/70 rounded-2xl p-4 border border-amber-200/60 flex flex-col justify-center items-center text-center hover:bg-amber-50 transition duration-200 group">
                        <div class="w-12 h-12 rounded-xl bg-amber-600 text-white flex items-center justify-center text-lg mb-2 shadow-md shadow-amber-500/20 group-hover:scale-110 transition-transform">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <p class="text-3xl font-black text-slate-900 mb-0.5">{{ number_format($totalCbtExams) }}</p>
                        <p class="text-xs font-bold text-slate-700 uppercase tracking-wider">Sesi Ujian CBT</p>
                    </div>
                </div>
            </div>

            {{-- Charts Row --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Tren Pendaftaran -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5">
                    <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider mb-4 flex items-center justify-between">
                        <span>Tren Pendaftaran (5 Thn)</span>
                        <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600"><i class="fas fa-chart-area text-xs"></i></div>
                    </h3>
                    <div id="enrollmentTrendChart" class="w-full h-64"></div>
                </div>

                <!-- Status Siswa -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5">
                    <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider mb-4 flex items-center justify-between">
                        <span>Status Siswa</span>
                        <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600"><i class="fas fa-chart-pie text-xs"></i></div>
                    </h3>
                    <div id="studentStatusChart" class="w-full h-64 flex justify-center mt-2"></div>
                </div>
            </div>

            {{-- Distribution Chart --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5">
                <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider mb-4 flex items-center justify-between">
                    <span>{{ $isSuperAdmin ? 'Distribusi Siswa per Unit Sekolah' : 'Jumlah Siswa per Kelas' }}</span>
                    <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600"><i class="fas fa-chart-bar text-xs"></i></div>
                </h3>
                <div id="distributionChart" class="w-full h-72"></div>
            </div>
        </div>

        {{-- Right Column (Activities & Monitoring) --}}
        <div class="space-y-6">
            {{-- Quick Actions --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5">
                 <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-bolt text-amber-500"></i> Aksi Cepat
                 </h3>
                 <div class="grid grid-cols-2 gap-3">
                    @if(!$isKepsek)
                    <a href="{{ route('admin.students.create') }}" class="flex flex-col items-center justify-center p-4 bg-slate-50 hover:bg-indigo-50/60 rounded-xl border border-slate-200/80 hover:border-indigo-300 transition-all duration-200 group">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-lg mb-2 shadow-sm group-hover:scale-110 transition-transform"><i class="fas fa-user-plus"></i></div>
                        <span class="text-xs font-bold text-slate-700 text-center uppercase tracking-wider group-hover:text-indigo-700">Tambah Siswa</span>
                    </a>
                    @else
                    <a href="{{ route('admin.employees.dashboard') }}" class="flex flex-col items-center justify-center p-4 bg-slate-50 hover:bg-indigo-50/60 rounded-xl border border-slate-200/80 hover:border-indigo-300 transition-all duration-200 group">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-lg mb-2 shadow-sm group-hover:scale-110 transition-transform"><i class="fas fa-chart-pie"></i></div>
                        <span class="text-xs font-bold text-slate-700 text-center uppercase tracking-wider group-hover:text-indigo-700">Dashboard SDM</span>
                    </a>
                    @endif

                    @if($isSuperAdmin)
                    <a href="{{ route('admin.bills.index') }}" class="flex flex-col items-center justify-center p-4 bg-slate-50 hover:bg-emerald-50/60 rounded-xl border border-slate-200/80 hover:border-emerald-300 transition-all duration-200 group">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-lg mb-2 shadow-sm group-hover:scale-110 transition-transform"><i class="fas fa-file-invoice-dollar"></i></div>
                        <span class="text-xs font-bold text-slate-700 text-center uppercase tracking-wider group-hover:text-emerald-700">Input Tagihan</span>
                    </a>
                    @else
                    <a href="{{ route('admin.attendances.monitoring') }}" class="flex flex-col items-center justify-center p-4 bg-slate-50 hover:bg-emerald-50/60 rounded-xl border border-slate-200/80 hover:border-emerald-300 transition-all duration-200 group">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-lg mb-2 shadow-sm group-hover:scale-110 transition-transform"><i class="fas fa-clipboard-check"></i></div>
                        <span class="text-xs font-bold text-slate-700 text-center uppercase tracking-wider group-hover:text-emerald-700">Absensi Siswa</span>
                    </a>
                    @endif
                 </div>
            </div>

            {{-- Presensi Siswa --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-indigo-50 rounded-full group-hover:scale-[3] transition-transform duration-700 opacity-50 pointer-events-none"></div>
                <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider mb-4 relative z-10 flex items-center gap-2">
                    <i class="fas fa-calendar-check text-indigo-600"></i> Kehadiran Siswa Hari Ini
                </h3>
                @php 
                    $attMap = $todayAttendances->pluck('count', 'status')->toArray();
                    $hadir = $attMap['hadir'] ?? 0;
                    $absen = ($attMap['sakit'] ?? 0) + ($attMap['izin'] ?? 0) + ($attMap['alpha'] ?? 0);
                @endphp
                <div class="space-y-4 relative z-10">
                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div class="bg-indigo-50/90 rounded-xl p-3.5 border border-indigo-200/60">
                            <p class="text-2xl font-black text-indigo-800">{{ number_format($hadir) }}</p>
                            <p class="text-xs font-extrabold uppercase tracking-wider text-indigo-600 mt-0.5">Hadir</p>
                        </div>
                        <div class="bg-rose-50/90 rounded-xl p-3.5 border border-rose-200/60">
                            <p class="text-2xl font-black text-rose-800">{{ number_format($absen) }}</p>
                            <p class="text-xs font-extrabold uppercase tracking-wider text-rose-600 mt-0.5">Absen</p>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200/80 mt-2">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Total Kumulatif</span>
                            <span class="text-xs font-black text-indigo-700 bg-indigo-100 px-2 py-0.5 rounded-md border border-indigo-200">{{ $cumulativeRate }}%</span>
                        </div>
                        <div class="w-full h-2.5 bg-slate-200 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-600 shadow-sm transition-all duration-1000" style="width: {{ $cumulativeRate }}%"></div>
                        </div>
                    </div>
                    <a href="{{ route('admin.attendances.monitoring') }}" class="block text-center text-xs font-black uppercase tracking-wider text-indigo-700 hover:text-indigo-900 transition py-2 bg-indigo-50 hover:bg-indigo-100 rounded-xl border border-indigo-200/50">Detail Monitoring →</a>
                </div>
            </div>

            {{-- Presensi Guru & Pegawai --}}
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-teal-50 rounded-full group-hover:scale-[3] transition-transform duration-700 opacity-50 pointer-events-none"></div>
                <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider mb-4 relative z-10 flex items-center gap-2">
                    <i class="fas fa-id-card text-teal-600"></i> Kehadiran SDM Hari Ini
                </h3>
                <div class="space-y-4 relative z-10">
                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div class="bg-teal-50/90 rounded-xl p-3.5 border border-teal-200/60 relative">
                            <p class="text-xs font-extrabold uppercase tracking-wider text-teal-700 mb-1">Guru Hadir</p>
                            <p class="text-xl font-black text-teal-900">{{ $teachersHadir + $teachersTugasKhusus }} <span class="text-xs text-teal-700 font-bold">/ {{ $activeTeachersCount }}</span></p>
                            @if($teachersTugasKhusus > 0)
                            <span class="absolute top-0 right-0 bg-teal-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-bl-lg rounded-tr-lg">+{{ $teachersTugasKhusus }} TK</span>
                            @endif
                        </div>
                        <div class="bg-emerald-50/90 rounded-xl p-3.5 border border-emerald-200/60 relative">
                            <p class="text-xs font-extrabold uppercase tracking-wider text-emerald-700 mb-1">Staf Hadir</p>
                            <p class="text-xl font-black text-emerald-900">{{ $staffHadir + $staffTugasKhusus }} <span class="text-xs text-emerald-700 font-bold">/ {{ $activeStaffCount }}</span></p>
                            @if($staffTugasKhusus > 0)
                            <span class="absolute top-0 right-0 bg-emerald-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-bl-lg rounded-tr-lg">+{{ $staffTugasKhusus }} TK</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200/80 space-y-2 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-slate-700 uppercase tracking-wider">Cuti / Sakit / Izin</span>
                            <span class="font-black text-slate-900 bg-slate-200 px-2 py-0.5 rounded-md">{{ $teachersSakit + $teachersIzin + $staffSakit + $staffIzin }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-slate-700 uppercase tracking-wider">Alpha (Guru/Staf)</span>
                            <span class="font-black text-rose-700 bg-rose-100 px-2 py-0.5 rounded-md border border-rose-200">{{ $teachersAlpha }} / {{ $staffAlpha }}</span>
                        </div>
                        @if($staffLate > 0)
                        <div class="flex justify-between items-center pt-1.5 border-t border-slate-200">
                            <span class="font-bold text-amber-700 uppercase tracking-wider"><i class="fas fa-clock mr-1"></i> Terlambat</span>
                            <span class="font-black text-amber-800 bg-amber-100 px-2 py-0.5 rounded-md border border-amber-200">{{ $staffLate }} orang</span>
                        </div>
                        @endif
                    </div>
                    
                    <a href="{{ route('admin.employees.attendance.index') }}" class="block text-center text-xs font-black uppercase tracking-wider text-teal-700 hover:text-teal-900 transition py-2 bg-teal-50 hover:bg-teal-100 rounded-xl border border-teal-200/50">Detail Monitoring Pegawai →</a>
                </div>
            </div>

            {{-- Recent Activity / Latest Registrations --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5">
                <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider mb-4 flex items-center justify-between">
                    <span>Siswa Baru Terdaftar</span>
                    <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600"><i class="fas fa-history text-xs"></i></div>
                </h3>
                <div class="space-y-3 mt-2">
                    @forelse($recentStudents as $rs)
                        <div class="flex items-center gap-3 p-2.5 hover:bg-slate-50 rounded-xl transition border border-transparent hover:border-slate-200/60">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white shadow-sm flex-shrink-0">
                                <i class="fas fa-user-graduate text-xs"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-slate-800 truncate leading-tight">{{ $rs->full_name }}</p>
                                <p class="text-[11px] font-semibold text-slate-600 uppercase tracking-wider mt-0.5">{{ $rs->currentClassroom->first()->class_name ?? 'Kelas Belum Ditentukan' }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6">
                            <i class="fas fa-users-slash text-slate-300 text-3xl mb-2"></i>
                            <p class="text-xs font-bold text-slate-600 uppercase tracking-wider">Belum ada siswa baru</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Shared styling variables for UI UX Pro Max standards (high-contrast text colors)
    const fontFamily = "'Plus Jakarta Sans', Inter, ui-sans-serif, system-ui, sans-serif";
    const chartConfig = {
        toolbar: { show: false },
        fontFamily: fontFamily,
        background: 'transparent'
    };

    // High contrast label color for text readability
    const labelColor = '#334155'; // slate-700
    const labelHeaderColor = '#1e293b'; // slate-800

    // 1. Enrollment Trend Chart (Area)
    const enrollmentData = @json($studentsByYear);
    const trendOptions = {
        series: [{
            name: 'Siswa Baru',
            data: enrollmentData.map(item => item.count)
        }],
        chart: {
            type: 'area',
            height: 250,
            ...chartConfig,
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800,
                animateGradually: { enabled: true, delay: 150 },
                dynamicAnimation: { enabled: true, speed: 350 }
            }
        },
        colors: ['#4f46e5'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [0, 90, 100]
            }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        xaxis: {
            categories: enrollmentData.map(item => item.entry_year),
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { colors: labelColor, fontFamily: fontFamily, fontWeight: 700, fontSize: '11px' } }
        },
        yaxis: {
            labels: { style: { colors: labelColor, fontFamily: fontFamily, fontWeight: 700, fontSize: '11px' } }
        },
        grid: {
            borderColor: '#e2e8f0',
            strokeDashArray: 4,
            yaxis: { lines: { show: true } }
        },
        tooltip: { theme: 'light' }
    };
    new ApexCharts(document.querySelector("#enrollmentTrendChart"), trendOptions).render();

    // 2. Student Status Chart (Donut)
    const statusData = @json($studentsByStatus);
    const statusLabels = statusData.map(item => item.status.toUpperCase());
    const statusSeries = statusData.map(item => item.count);
    const statusOptions = {
        series: statusSeries,
        chart: {
            type: 'donut',
            height: 260,
            ...chartConfig
        },
        labels: statusLabels,
        colors: ['#10b981', '#3b82f6', '#f43f5e', '#f59e0b', '#8b5cf6', '#64748b'],
        plotOptions: {
            pie: {
                donut: { 
                    size: '72%',
                    labels: {
                        show: true,
                        name: { show: true, fontSize: '11px', fontFamily: fontFamily, fontWeight: 700, color: labelColor },
                        value: { show: true, fontSize: '22px', fontFamily: fontFamily, fontWeight: 900, color: labelHeaderColor },
                        total: {
                            show: true,
                            showAlways: true,
                            label: 'TOTAL',
                            fontSize: '11px',
                            fontFamily: fontFamily,
                            fontWeight: 800,
                            color: labelColor
                        }
                    }
                }
            }
        },
        dataLabels: { enabled: false },
        legend: { 
            position: 'bottom', 
            fontSize: '12px', 
            fontFamily: fontFamily, 
            fontWeight: 700, 
            labels: { colors: labelHeaderColor },
            itemMargin: { horizontal: 8, vertical: 4 } 
        },
        stroke: { width: 2, colors: ['#ffffff'] }
    };
    new ApexCharts(document.querySelector("#studentStatusChart"), statusOptions).render();

    // 3. Distribution Chart (Bar)
    @if($isSuperAdmin)
        const distData = @json($studentsBySchool);
        const distCategories = distData.map(item => item.school.name);
        const distSeries = distData.map(item => item.count);
    @else
        const distData = @json($classDistribution);
        const distCategories = distData.map(item => item.class_name);
        const distSeries = distData.map(item => item.students_count);
    @endif

    const distOptions = {
        series: [{
            name: 'Jumlah Siswa',
            data: distSeries
        }],
        chart: {
            type: 'bar',
            height: 300,
            ...chartConfig
        },
        colors: ['#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ec4899', '#06b6d4'],
        plotOptions: {
            bar: {
                borderRadius: 6,
                horizontal: false,
                columnWidth: '45%',
                distributed: true
            }
        },
        dataLabels: { 
            enabled: true,
            formatter: function (val) { return val; },
            offsetY: -20,
            style: { fontSize: '11px', colors: [labelHeaderColor], fontFamily: fontFamily, fontWeight: 800 }
        },
        legend: { show: false },
        xaxis: {
            categories: distCategories,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { 
                style: { colors: labelColor, fontFamily: fontFamily, fontWeight: 700, fontSize: '11px' },
                rotate: -35,
                trim: true
            }
        },
        yaxis: {
            labels: { style: { colors: labelColor, fontFamily: fontFamily, fontWeight: 700, fontSize: '11px' } }
        },
        grid: {
            borderColor: '#e2e8f0',
            strokeDashArray: 4,
            yaxis: { lines: { show: true } },
            xaxis: { lines: { show: false } }
        },
        tooltip: { theme: 'light' }
    };
    new ApexCharts(document.querySelector("#distributionChart"), distOptions).render();
});
</script>
@endpush
@endsection
