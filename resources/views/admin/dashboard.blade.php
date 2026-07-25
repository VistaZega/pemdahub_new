@extends('layouts.admin')

@section('title', $isSuperAdmin ? 'Dashboard Super Admin' : ($isKepsek ? 'Dashboard Kepala Sekolah' : 'Dashboard Admin Sekolah'))

@section('content')
<div class="space-y-6">
    {{-- Hero Header Section --}}
    <div class="relative overflow-hidden rounded-2xl p-8 shadow-xl bg-gradient-to-br {{ $isSuperAdmin ? 'from-indigo-900 via-indigo-700 to-purple-800' : 'from-emerald-900 via-emerald-700 to-teal-800' }} border-0">
        <!-- Background Elements for Glassmorphism -->
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
        <div class="absolute bottom-0 left-0 w-[300px] h-[300px] bg-black/20 rounded-full blur-2xl translate-y-1/3 -translate-x-1/4"></div>
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10 mix-blend-overlay"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-lg text-base font-bold uppercase tracking-widest text-white shadow-inner border border-white/10">
                        <i class="fas fa-crown mr-1 text-yellow-300"></i> {{ $isSuperAdmin ? 'Super Admin' : ($isKepsek ? 'Kepala Sekolah' : 'Admin Sekolah') }}
                    </span>
                    @if($currentSemester)
                        <span class="px-3 py-1 bg-gradient-to-r from-amber-400 to-amber-500 text-amber-950 rounded-lg text-base font-bold uppercase tracking-widest shadow-sm">
                            <i class="fas fa-clock mr-1"></i> {{ $currentSemester->semester_name }}
                        </span>
                    @endif
                </div>
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-md">
                    {{ $isSuperAdmin ? 'Pusat Kendali Ekosistem' : ($school->name ?? 'Dashboard Sekolah') }}
                </h1>
                <p class="text-white text-base md:text-base max-w-2xl font-medium leading-relaxed">
                    {{ $isSuperAdmin ? 'Pantau seluruh operasional Yayasan Perguruan PEMBDA Nias secara real-time melalui panel ini.' : 'Kelola kegiatan akademik dan operasional harian sekolah Anda dengan wawasan data yang akurat.' }}
                </p>
            </div>
            
            <div class="flex items-center gap-4">
                @if($currentAcademicYear)
                    <div class="bg-white/10 backdrop-blur-sm px-5 py-4 rounded-2xl border border-white/20 text-center shadow-lg transform transition hover:scale-105">
                        <p class="text-base uppercase font-bold text-white/90 tracking-wider mb-1">Tahun Pelajaran</p>
                        <p class="text-xl font-black text-white">{{ $currentAcademicYear->year }}</p>
                    </div>
                @endif
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-white/20 to-white/5 backdrop-blur-md flex items-center justify-center border border-white/20 shadow-xl transform transition hover:rotate-12">
                    <i class="fas {{ $isSuperAdmin ? 'fa-globe-asia' : 'fa-school' }} text-3xl text-white"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Stats Grid (KPIs) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        {{-- Total Siswa --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-gradient-to-br from-blue-50 to-blue-100/50 rounded-full group-hover:scale-[2] transition-transform duration-700 ease-in-out"></div>
            <div class="relative flex items-start justify-between z-10">
                <div>
                    <p class="text-base font-bold text-gray-600 uppercase tracking-widest mb-2">Total Siswa</p>
                    <h3 class="text-4xl font-black text-gray-800 tracking-tight">{{ number_format($totalStudents) }}</h3>
                    <div class="flex items-center gap-1.5 mt-2">
                        <span class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        <p class="text-base text-gray-700 font-semibold">{{ number_format($activeStudents) }} Aktif Ber-Rombel (TP {{ $currentAcademicYear->year ?? '' }})</p>
                    </div>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-blue-500/30 group-hover:rotate-6 transition"><i class="fas fa-user-graduate text-2xl"></i></div>
            </div>
        </div>

        {{-- Tenaga Pengajar --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-gradient-to-br from-emerald-50 to-emerald-100/50 rounded-full group-hover:scale-[2] transition-transform duration-700 ease-in-out"></div>
            <div class="relative flex items-start justify-between z-10">
                <div>
                    <p class="text-base font-bold text-gray-600 uppercase tracking-widest mb-2">Tenaga Pengajar</p>
                    <h3 class="text-4xl font-black text-gray-800 tracking-tight">{{ number_format($totalTeachers) }}</h3>
                    <p class="text-base text-gray-700 font-semibold mt-2"><i class="fas fa-users text-emerald-500 mr-1"></i>{{ number_format($totalEmployees) }} Total Pegawai</p>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-emerald-400 to-teal-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-emerald-500/30 group-hover:-rotate-6 transition"><i class="fas fa-chalkboard-teacher text-2xl"></i></div>
            </div>
        </div>

        {{-- Unit / Ruang Kelas --}}
        @if($isSuperAdmin)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-gradient-to-br from-purple-50 to-purple-100/50 rounded-full group-hover:scale-[2] transition-transform duration-700 ease-in-out"></div>
            <div class="relative flex items-start justify-between z-10">
                <div>
                    <p class="text-base font-bold text-gray-600 uppercase tracking-widest mb-2">Unit Sekolah</p>
                    <h3 class="text-4xl font-black text-gray-800 tracking-tight">{{ number_format($totalSchools) }}</h3>
                    <p class="text-base text-gray-700 font-semibold mt-2"><i class="fas fa-sitemap text-purple-500 mr-1"></i>Tingkat SMP, SMA, SMK</p>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-fuchsia-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-purple-500/30 group-hover:rotate-6 transition"><i class="fas fa-building text-2xl"></i></div>
            </div>
        </div>
        @else
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-gradient-to-br from-purple-50 to-purple-100/50 rounded-full group-hover:scale-[2] transition-transform duration-700 ease-in-out"></div>
            <div class="relative flex items-start justify-between z-10">
                <div>
                    <p class="text-base font-bold text-gray-600 uppercase tracking-widest mb-2">Ruang Kelas</p>
                    <h3 class="text-4xl font-black text-gray-800 tracking-tight">{{ number_format($totalClassrooms) }}</h3>
                    <p class="text-base text-gray-700 font-semibold mt-2"><i class="fas fa-door-open text-purple-500 mr-1"></i>Rombel Aktif</p>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-fuchsia-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-purple-500/30 group-hover:rotate-6 transition"><i class="fas fa-door-open text-2xl"></i></div>
            </div>
        </div>
        @endif

        {{-- CBT & LMS --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-gradient-to-br from-amber-50 to-amber-100/50 rounded-full group-hover:scale-[2] transition-transform duration-700 ease-in-out"></div>
            <div class="relative flex items-start justify-between z-10">
                <div>
                    <p class="text-base font-bold text-gray-600 uppercase tracking-widest mb-2">CBT & Digital</p>
                    <h3 class="text-4xl font-black text-gray-800 tracking-tight">{{ number_format($totalCbtExams) }}</h3>
                    <p class="text-base text-gray-700 font-semibold mt-2"><i class="fas fa-book-reader text-amber-500 mr-1"></i>{{ number_format($activeLmsCourses) }} Kursus LMS</p>
                </div>
                <div class="w-14 h-14 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-amber-500/30 group-hover:-rotate-6 transition"><i class="fas fa-laptop-code text-2xl"></i></div>
            </div>
        </div>
    </div>

    {{-- New Section: Operational Indicators --}}
    <h2 class="text-lg font-extrabold text-gray-800 mt-8 mb-4 flex items-center gap-2">
        <i class="fas fa-chart-pie text-indigo-500"></i> Indikator Operasional
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Keuangan (Tagihan) -->
        <div class="bg-gradient-to-r from-gray-900 to-gray-800 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-32 h-32 bg-white/5 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-700"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-base font-bold uppercase tracking-widest text-white">Status Tagihan</h4>
                    <i class="fas fa-wallet text-white text-xl"></i>
                </div>
                <div class="flex items-baseline gap-2 mb-2">
                    <span class="text-3xl font-black text-white">{{ $billPaidPercentage }}%</span>
                    <span class="text-base font-medium text-white">Telah Lunas</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2.5 mb-2 overflow-hidden shadow-inner">
                    <div class="bg-gradient-to-r from-emerald-400 to-emerald-500 h-2.5 rounded-full shadow-lg" style="width: {{ $billPaidPercentage }}%"></div>
                </div>
                <p class="text-base text-white mt-2"><span class="text-emerald-400 font-bold">{{ number_format($paidBillsCount) }}</span> dari {{ number_format($totalBillsCount) }} tagihan tercatat</p>
            </div>
        </div>

        <!-- PSB (Penerimaan Siswa Baru) -->
        <div class="bg-gradient-to-r from-indigo-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-700"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-base font-bold uppercase tracking-widest text-indigo-100">PSB & Admisi</h4>
                    <i class="fas fa-user-plus text-indigo-50 text-xl"></i>
                </div>
                <div class="flex items-baseline gap-2 mb-3">
                    <span class="text-3xl font-black">{{ number_format($totalApplicants) }}</span>
                    <span class="text-base font-medium text-indigo-50">Pendaftar</span>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/20">
                    <div class="flex items-center justify-between text-base">
                        <span class="font-semibold text-white">Sedang Diproses</span>
                        <span class="font-bold bg-white text-indigo-600 px-2 py-0.5 rounded-full">{{ number_format($pendingApplicants) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- BK (Bimbingan Konseling) -->
        <div class="bg-gradient-to-r from-rose-500 to-pink-600 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-700"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-base font-bold uppercase tracking-widest text-rose-100">Bimbingan Konseling</h4>
                    <i class="fas fa-hands-helping text-rose-50 text-xl"></i>
                </div>
                <div class="flex items-baseline gap-2 mb-3">
                    <span class="text-3xl font-black">{{ number_format($activeCounselings) }}</span>
                    <span class="text-base font-medium text-rose-50">Kasus Aktif</span>
                </div>
                <div class="flex items-center gap-3 text-base text-rose-100">
                    <i class="fas fa-info-circle text-white/50 text-2xl"></i>
                    <p class="leading-tight text-base">Catatan BK yang sedang dalam proses penanganan / belum ditutup.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Analytics Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-8">
        
        {{-- Left Column (Charts & Progress) --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Academic & Operational Progress --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                 <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                    <i class="fas fa-chart-line text-emerald-500"></i> Metrik Kinerja Utama
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-emerald-50/50 rounded-2xl p-5 border border-emerald-100/50 flex flex-col justify-center items-center text-center hover:bg-emerald-50 transition group">
                        <div class="w-16 h-16 rounded-full bg-white text-emerald-500 flex items-center justify-center text-2xl mb-3 shadow-sm border border-emerald-100 group-hover:scale-110 transition-transform">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <p class="text-3xl font-black text-gray-800 mb-1">{{ $cumulativeRate }}%</p>
                        <p class="text-base font-bold text-gray-700 uppercase tracking-widest">Kehadiran Siswa</p>
                    </div>
                    <div class="bg-blue-50/50 rounded-2xl p-5 border border-blue-100/50 flex flex-col justify-center items-center text-center hover:bg-blue-50 transition group">
                        <div class="w-16 h-16 rounded-full bg-white text-blue-500 flex items-center justify-center text-2xl mb-3 shadow-sm border border-blue-100 group-hover:scale-110 transition-transform">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        <p class="text-3xl font-black text-gray-800 mb-1">{{ number_format($activeLmsCourses) }}</p>
                        <p class="text-base font-bold text-gray-700 uppercase tracking-widest">Kursus Digital</p>
                    </div>
                    <div class="bg-amber-50/50 rounded-2xl p-5 border border-amber-100/50 flex flex-col justify-center items-center text-center hover:bg-amber-50 transition group">
                        <div class="w-16 h-16 rounded-full bg-white text-amber-500 flex items-center justify-center text-2xl mb-3 shadow-sm border border-amber-100 group-hover:scale-110 transition-transform">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <p class="text-3xl font-black text-gray-800 mb-1">{{ number_format($totalCbtExams) }}</p>
                        <p class="text-base font-bold text-gray-700 uppercase tracking-widest">Sesi Ujian CBT</p>
                    </div>
                </div>
            </div>

            {{-- Charts Row --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Tren Pendaftaran -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-base font-bold text-gray-700 uppercase tracking-widest mb-4 flex items-center justify-between">
                        <span>Tren Pendaftaran (5 Thn)</span>
                        <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center"><i class="fas fa-chart-area text-gray-600"></i></div>
                    </h3>
                    <div id="enrollmentTrendChart" class="w-full h-64"></div>
                </div>

                <!-- Status Siswa -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-base font-bold text-gray-700 uppercase tracking-widest mb-4 flex items-center justify-between">
                        <span>Status Siswa</span>
                        <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center"><i class="fas fa-chart-pie text-gray-600"></i></div>
                    </h3>
                    <div id="studentStatusChart" class="w-full h-64 flex justify-center mt-2"></div>
                </div>
            </div>

            {{-- Distribution Chart --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-bold text-gray-700 uppercase tracking-widest mb-4 flex items-center justify-between">
                    <span>{{ $isSuperAdmin ? 'Distribusi Siswa per Unit Sekolah' : 'Jumlah Siswa per Kelas' }}</span>
                    <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center"><i class="fas fa-chart-bar text-gray-600"></i></div>
                </h3>
                <div id="distributionChart" class="w-full h-72"></div>
            </div>
        </div>

        {{-- Right Column (Activities & Monitoring) --}}
        <div class="space-y-6">
            {{-- Quick Actions --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                 <h3 class="text-base font-bold text-gray-700 uppercase tracking-widest mb-4">Aksi Cepat</h3>
                 <div class="grid grid-cols-2 gap-4">
                    @if(!$isKepsek)
                    <a href="{{ route('admin.students.create') }}" class="flex flex-col items-center justify-center p-5 bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-indigo-300 transition-all group">
                        <div class="w-12 h-12 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl mb-3 group-hover:bg-indigo-600 group-hover:text-white transition-colors"><i class="fas fa-user-plus"></i></div>
                        <span class="text-base font-bold text-gray-600 text-center uppercase tracking-wider group-hover:text-indigo-700">Tambah Siswa</span>
                    </a>
                    @else
                    <a href="{{ route('admin.employees.dashboard') }}" class="flex flex-col items-center justify-center p-5 bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-indigo-300 transition-all group">
                        <div class="w-12 h-12 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl mb-3 group-hover:bg-indigo-600 group-hover:text-white transition-colors"><i class="fas fa-chart-pie"></i></div>
                        <span class="text-base font-bold text-gray-600 text-center uppercase tracking-wider group-hover:text-indigo-700">Dashboard SDM</span>
                    </a>
                    @endif

                    @if($isSuperAdmin)
                    <a href="{{ route('admin.bills.index') }}" class="flex flex-col items-center justify-center p-5 bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-emerald-300 transition-all group">
                        <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl mb-3 group-hover:bg-emerald-600 group-hover:text-white transition-colors"><i class="fas fa-file-invoice-dollar"></i></div>
                        <span class="text-base font-bold text-gray-600 text-center uppercase tracking-wider group-hover:text-emerald-700">Input Tagihan</span>
                    </a>
                    @else
                    <a href="{{ route('admin.attendances.monitoring') }}" class="flex flex-col items-center justify-center p-5 bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-emerald-300 transition-all group">
                        <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl mb-3 group-hover:bg-emerald-600 group-hover:text-white transition-colors"><i class="fas fa-clipboard-check"></i></div>
                        <span class="text-base font-bold text-gray-600 text-center uppercase tracking-wider group-hover:text-emerald-700">Absensi Siswa</span>
                    </a>
                    @endif
                 </div>
            </div>

            {{-- Presensi Siswa --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-md transition">
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-indigo-50 rounded-full group-hover:scale-[3] transition-transform duration-700 opacity-50"></div>
                <h3 class="text-base font-bold text-gray-700 uppercase tracking-widest mb-4 relative z-10">Kehadiran Siswa Hari Ini</h3>
                @php 
                    $attMap = $todayAttendances->pluck('count', 'status')->toArray();
                    $hadir = $attMap['hadir'] ?? 0;
                    $absen = ($attMap['sakit'] ?? 0) + ($attMap['izin'] ?? 0) + ($attMap['alpha'] ?? 0);
                @endphp
                <div class="space-y-4 relative z-10">
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div class="bg-indigo-50/80 rounded-xl p-4 border border-indigo-100/50">
                            <p class="text-2xl font-black text-indigo-700">{{ number_format($hadir) }}</p>
                            <p class="text-base font-bold uppercase tracking-widest text-indigo-500 mt-1">Hadir</p>
                        </div>
                        <div class="bg-rose-50/80 rounded-xl p-4 border border-rose-100/50">
                            <p class="text-2xl font-black text-rose-700">{{ number_format($absen) }}</p>
                            <p class="text-base font-bold uppercase tracking-widest text-rose-500 mt-1">Absen</p>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 mt-2">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-base font-bold uppercase tracking-wider text-gray-600">Total Kumulatif</span>
                            <span class="text-base font-black text-indigo-600 bg-indigo-100 px-2 py-0.5 rounded-md">{{ $cumulativeRate }}%</span>
                        </div>
                        <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-600 shadow-sm transition-all duration-1000" style="width: {{ $cumulativeRate }}%"></div>
                        </div>
                    </div>
                    <a href="{{ route('admin.attendances.monitoring') }}" class="block text-center text-base font-bold uppercase tracking-widest text-indigo-600 hover:text-indigo-800 transition py-2 bg-indigo-50 rounded-lg hover:bg-indigo-100">Detail Monitoring →</a>
                </div>
            </div>

            {{-- Presensi Guru & Pegawai --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-md transition">
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-teal-50 rounded-full group-hover:scale-[3] transition-transform duration-700 opacity-50"></div>
                <h3 class="text-base font-bold text-gray-700 uppercase tracking-widest mb-4 relative z-10">Kehadiran SDM Hari Ini</h3>
                <div class="space-y-4 relative z-10">
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div class="bg-teal-50/80 rounded-xl p-4 border border-teal-100/50 relative">
                            <p class="text-base font-bold uppercase tracking-widest text-teal-600 mb-1">Guru Hadir</p>
                            <p class="text-xl font-black text-teal-800">{{ $teachersHadir + $teachersTugasKhusus }} <span class="text-base text-teal-500 font-medium">/ {{ $activeTeachersCount }}</span></p>
                            @if($teachersTugasKhusus > 0)
                            <span class="absolute top-0 right-0 bg-teal-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-bl-lg rounded-tr-lg">+{{ $teachersTugasKhusus }} TK</span>
                            @endif
                        </div>
                        <div class="bg-emerald-50/80 rounded-xl p-4 border border-emerald-100/50 relative">
                            <p class="text-base font-bold uppercase tracking-widest text-emerald-600 mb-1">Staf Hadir</p>
                            <p class="text-xl font-black text-emerald-800">{{ $staffHadir + $staffTugasKhusus }} <span class="text-base text-emerald-500 font-medium">/ {{ $activeStaffCount }}</span></p>
                            @if($staffTugasKhusus > 0)
                            <span class="absolute top-0 right-0 bg-emerald-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-bl-lg rounded-tr-lg">+{{ $staffTugasKhusus }} TK</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 space-y-2 text-base">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-700 uppercase tracking-wider">Cuti / Sakit / Izin</span>
                            <span class="font-black text-gray-700 bg-gray-200 px-2 py-0.5 rounded-md">{{ $teachersSakit + $teachersIzin + $staffSakit + $staffIzin }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-700 uppercase tracking-wider">Alpha (Guru/Staf)</span>
                            <span class="font-black text-rose-600 bg-rose-100 px-2 py-0.5 rounded-md">{{ $teachersAlpha }} / {{ $staffAlpha }}</span>
                        </div>
                        @if($staffLate > 0)
                        <div class="flex justify-between items-center pt-1 border-t border-gray-200">
                            <span class="font-bold text-amber-600 uppercase tracking-wider"><i class="fas fa-clock mr-1"></i> Terlambat</span>
                            <span class="font-black text-amber-700 bg-amber-100 px-2 py-0.5 rounded-md">{{ $staffLate }} orang</span>
                        </div>
                        @endif
                    </div>
                    
                    <a href="{{ route('admin.employees.attendance.index') }}" class="block text-center text-base font-bold uppercase tracking-widest text-teal-600 hover:text-teal-800 transition py-2 bg-teal-50 rounded-lg hover:bg-teal-100">Detail Monitoring Pegawai →</a>
                </div>
            </div>

            {{-- Recent Activity / Latest Registrations --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-bold text-gray-700 uppercase tracking-widest mb-4 flex items-center justify-between">
                    <span>Siswa Baru Terdaftar</span>
                    <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center"><i class="fas fa-history text-gray-600"></i></div>
                </h3>
                <div class="space-y-4 mt-2">
                    @forelse($recentStudents as $rs)
                        <div class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg transition">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center text-indigo-600 shadow-inner flex-shrink-0">
                                <i class="fas fa-user-graduate text-base"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-base font-bold text-gray-800 truncate">{{ $rs->full_name }}</p>
                                <p class="text-base font-semibold text-gray-600 uppercase tracking-wider">{{ $rs->currentClassroom->first()->class_name ?? 'Kelas Belum Ditentukan' }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="fas fa-users-slash text-gray-100 text-3xl mb-2"></i>
                            <p class="text-base font-semibold text-gray-600 uppercase tracking-wider">Belum ada siswa baru</p>
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
    // Shared styling variables
    const fontFamily = 'Inter, ui-sans-serif, system-ui, -apple-system, sans-serif';
    const chartConfig = {
        toolbar: { show: false },
        fontFamily: fontFamily,
        background: 'transparent'
    };

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
                opacityFrom: 0.5,
                opacityTo: 0.0,
                stops: [0, 90, 100]
            }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        xaxis: {
            categories: enrollmentData.map(item => item.entry_year),
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { colors: '#9ca3af', fontFamily: fontFamily, fontWeight: 600 } }
        },
        yaxis: {
            labels: { style: { colors: '#9ca3af', fontFamily: fontFamily, fontWeight: 600 } }
        },
        grid: {
            borderColor: '#f3f4f6',
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
        colors: ['#10b981', '#3b82f6', '#f43f5e', '#f59e0b', '#8b5cf6', '#6b7280'],
        plotOptions: {
            pie: {
                donut: { 
                    size: '75%',
                    labels: {
                        show: true,
                        name: { show: true, fontSize: '11px', fontFamily: fontFamily, fontWeight: 700, color: '#9ca3af' },
                        value: { show: true, fontSize: '24px', fontFamily: fontFamily, fontWeight: 900, color: '#1f2937' },
                        total: {
                            show: true,
                            showAlways: true,
                            label: 'TOTAL',
                            fontSize: '11px',
                            fontFamily: fontFamily,
                            fontWeight: 800,
                            color: '#9ca3af'
                        }
                    }
                }
            }
        },
        dataLabels: { enabled: false },
        legend: { position: 'bottom', fontSize: '12px', fontFamily: fontFamily, fontWeight: 600, itemMargin: { horizontal: 10, vertical: 5 } },
        stroke: { width: 0 }
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
        colors: ['#8b5cf6'],
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
            style: { fontSize: '12px', colors: ["#6b7280"], fontFamily: fontFamily, fontWeight: 800 }
        },
        legend: { show: false },
        xaxis: {
            categories: distCategories,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { 
                style: { colors: '#6b7280', fontFamily: fontFamily, fontWeight: 600 },
                rotate: -45,
                trim: true
            }
        },
        yaxis: {
            labels: { style: { colors: '#9ca3af', fontFamily: fontFamily, fontWeight: 600 } }
        },
        grid: {
            borderColor: '#f3f4f6',
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
