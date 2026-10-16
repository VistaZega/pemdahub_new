@extends('layouts.admin')

@section('title', 'Otorisasi & Kontrol Fitur - PembdaHUB')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg">
                    <i class="fas fa-shield-halved text-white text-3xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Pusat Otorisasi Fitur</h1>
                    <p class="text-gray-600 mt-1">Konfigurasi hak akses, visibilitas modul, dan kontrol fitur sistem sekolah secara terpadu</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-xl flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 flex-shrink-0">
            <i class="fas fa-check-circle"></i>
        </div>
        <p class="text-green-700 font-medium text-sm">{{ session('success') }}</p>
    </div>
    @endif

    <form action="{{ route('admin.settings.features.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            {{-- KATEGORI: SISWA & ORANG TUA --}}
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 flex flex-col justify-between">
                <div class="space-y-6">
                    <h2 class="text-lg font-bold text-gray-900 border-b pb-3 flex items-center gap-2">
                        <i class="fas fa-user-graduate text-indigo-500"></i>
                        Siswa & Orang Tua
                    </h2>
                    
                    <div class="space-y-4">
                        <!-- show_report_card -->
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="show_report_card" name="show_report_card" value="1" {{ $settings['show_report_card'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                            <div>
                                <label for="show_report_card" class="text-sm font-semibold text-gray-800">Tampilkan Rapor Digital</label>
                                <p class="text-xs text-gray-500 mt-0.5">Izinkan siswa dan orang tua mengunduh dan mencetak Rapor PDF.</p>
                            </div>
                        </div>

                        <!-- siswa_view_attendance_recap -->
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="siswa_view_attendance_recap" name="siswa_view_attendance_recap" value="1" {{ $settings['siswa_view_attendance_recap'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                            <div>
                                <label for="siswa_view_attendance_recap" class="text-sm font-semibold text-gray-800">Rekap Absensi</label>
                                <p class="text-xs text-gray-500 mt-0.5">Menampilkan menu riwayat absensi bulanan di portal siswa.</p>
                            </div>
                        </div>

                        <!-- siswa_view_reputation_leaderboard -->
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="siswa_view_reputation_leaderboard" name="siswa_view_reputation_leaderboard" value="1" {{ $settings['siswa_view_reputation_leaderboard'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                            <div>
                                <label for="siswa_view_reputation_leaderboard" class="text-sm font-semibold text-gray-800">Hall of Fame (Leaderboard)</label>
                                <p class="text-xs text-gray-500 mt-0.5">Izinkan akses melihat papan peringkat poin keaktifan siswa.</p>
                            </div>
                        </div>

                        <!-- siswa_access_cbt -->
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="siswa_access_cbt" name="siswa_access_cbt" value="1" {{ $settings['siswa_access_cbt'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                            <div>
                                <label for="siswa_access_cbt" class="text-sm font-semibold text-gray-800">Akses Ujian Online (CBT)</label>
                                <p class="text-xs text-gray-500 mt-0.5">Buka akses pengerjaan ujian dan latihan soal bagi siswa.</p>
                            </div>
                        </div>

                        <!-- siswa_access_lms -->
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="siswa_access_lms" name="siswa_access_lms" value="1" {{ $settings['siswa_access_lms'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                            <div>
                                <label for="siswa_access_lms" class="text-sm font-semibold text-gray-800">Akses Portal LMS</label>
                                <p class="text-xs text-gray-500 mt-0.5">Siswa dapat mengakses materi kelas, tugas, dan diskusi forum.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KATEGORI: GURU --}}
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 flex flex-col justify-between">
                <div class="space-y-6">
                    <h2 class="text-lg font-bold text-gray-900 border-b pb-3 flex items-center gap-2">
                        <i class="fas fa-chalkboard-teacher text-emerald-500"></i>
                        Guru Pengajar
                    </h2>
                    
                    <div class="space-y-4">
                        <!-- guru_can_edit_grades -->
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="guru_can_edit_grades" name="guru_can_edit_grades" value="1" {{ $settings['guru_can_edit_grades'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                            <div>
                                <label for="guru_can_edit_grades" class="text-sm font-semibold text-gray-800">Edit Nilai Terkunci</label>
                                <p class="text-xs text-gray-500 mt-0.5">Izinkan guru mengubah nilai mata pelajaran yang rapornya sudah berstatus final.</p>
                            </div>
                        </div>

                        <!-- guru_view_reputation_leaderboard -->
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="guru_view_reputation_leaderboard" name="guru_view_reputation_leaderboard" value="1" {{ $settings['guru_view_reputation_leaderboard'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                            <div>
                                <label for="guru_view_reputation_leaderboard" class="text-sm font-semibold text-gray-800">Hall of Fame (Leaderboard)</label>
                                <p class="text-xs text-gray-500 mt-0.5">Guru dapat melihat papan skor keaktifan guru dan siswa.</p>
                            </div>
                        </div>

                        <!-- guru_can_see_payroll_details -->
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="guru_can_see_payroll_details" name="guru_can_see_payroll_details" value="1" {{ $settings['guru_can_see_payroll_details'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                            <div>
                                <label for="guru_can_see_payroll_details" class="text-sm font-semibold text-gray-800">Akses Detail Slip Gaji</label>
                                <p class="text-xs text-gray-500 mt-0.5">Menampilkan informasi nominal gaji pokok di halaman profil guru.</p>
                            </div>
                        </div>

                        <!-- guru_access_cbt -->
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="guru_access_cbt" name="guru_access_cbt" value="1" {{ $settings['guru_access_cbt'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                            <div>
                                <label for="guru_access_cbt" class="text-sm font-semibold text-gray-800">Akses Kelola CBT</label>
                                <p class="text-xs text-gray-500 mt-0.5">Buka akses bank soal, paket ujian, dan penilaian esai bagi guru.</p>
                            </div>
                        </div>

                        <!-- guru_access_lms -->
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="guru_access_lms" name="guru_access_lms" value="1" {{ $settings['guru_access_lms'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                            <div>
                                <label for="guru_access_lms" class="text-sm font-semibold text-gray-800">Akses Kelola LMS</label>
                                <p class="text-xs text-gray-500 mt-0.5">Guru dapat mengelola kelas online, membuat tugas, dan berdiskusi.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KATEGORI: PEGAWAI / STAF --}}
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 flex flex-col justify-between">
                <div class="space-y-6">
                    <h2 class="text-lg font-bold text-gray-900 border-b pb-3 flex items-center gap-2">
                        <i class="fas fa-briefcase text-amber-500"></i>
                        Pegawai / Staf Non-Guru
                    </h2>
                    
                    <div class="space-y-4">
                        <!-- pegawai_can_request_leave -->
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="pegawai_can_request_leave" name="pegawai_can_request_leave" value="1" {{ $settings['pegawai_can_request_leave'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                            <div>
                                <label for="pegawai_can_request_leave" class="text-sm font-semibold text-gray-800">Pengajuan Cuti Mandiri</label>
                                <p class="text-xs text-gray-500 mt-0.5">Buka menu pengajuan cuti secara mandiri dari portal pribadi staf/pegawai.</p>
                            </div>
                        </div>

                        <!-- pegawai_can_see_payroll_details -->
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="pegawai_can_see_payroll_details" name="pegawai_can_see_payroll_details" value="1" {{ $settings['pegawai_can_see_payroll_details'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                            <div>
                                <label for="pegawai_can_see_payroll_details" class="text-sm font-semibold text-gray-800">Akses Gaji Pokok</label>
                                <p class="text-xs text-gray-500 mt-0.5">Menampilkan informasi nominal gaji pokok di halaman profil pegawai.</p>
                            </div>
                        </div>

                        <!-- pegawai_view_attendance_recap -->
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="pegawai_view_attendance_recap" name="pegawai_view_attendance_recap" value="1" {{ $settings['pegawai_view_attendance_recap'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                            <div>
                                <label for="pegawai_view_attendance_recap" class="text-sm font-semibold text-gray-800">Rekap Absensi Saya</label>
                                <p class="text-xs text-gray-500 mt-0.5">Staf dapat mengakses menu Rekap Absensi Pribadi mereka sendiri.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- KATEGORI: MODE TANGGAP DARURAT & PEMBELAJARAN JARAK JAUH (PJJ) -->
        <div class="mt-8 bg-gradient-to-br from-amber-500/10 via-orange-500/5 to-white rounded-2xl shadow-md border-2 border-amber-300 p-6 relative overflow-hidden">
            <div class="absolute -right-8 -top-8 w-36 h-36 bg-amber-200/40 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-amber-200 pb-4 mb-6">
                <div>
                    <h2 class="text-xl font-black text-amber-950 flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg shadow-sm">
                            <i class="fas fa-head-side-mask"></i>
                        </span>
                        Mode Tanggap Darurat & Pembelajaran Jarak Jauh (PJJ)
                    </h2>
                    <p class="text-xs text-amber-800 font-medium mt-1">
                        Sistem fleksibel untuk mengalihkan pembelajaran ke Belajar Dari Rumah (BDR) & membebaskan radius presensi GPS siswa per unit sekolah tanpa merusak pengaturan normal sebelumnya.
                    </p>
                </div>
                <div>
                    <label class="inline-flex items-center gap-3 cursor-pointer bg-white px-4 py-2 rounded-xl border border-amber-300 shadow-sm hover:border-amber-400 transition">
                        <input type="checkbox" id="pjj_enabled" name="pjj_enabled" value="1" {{ ($settings['pjj_enabled'] ?? false) ? 'checked' : '' }} class="w-5 h-5 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                        <span class="text-sm font-black text-slate-800">
                            {{ ($settings['pjj_enabled'] ?? false) ? '🚨 MODE PJJ SEDANG AKTIF' : '⚪ MODE PJJ NON-AKTIF' }}
                        </span>
                    </label>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Unit Sekolah yang Diberlakukan PJJ -->
                <div class="bg-white/80 backdrop-blur-sm p-4 rounded-xl border border-amber-200 space-y-3">
                    <label class="block text-xs font-black uppercase text-slate-700 tracking-wider">
                        Unit Sekolah yang Diberlakukan PJJ (Bebas Radius Geofence)
                    </label>
                    <p class="text-[11px] text-slate-500 font-medium leading-relaxed">
                        Centang unit yang melaksanakan Belajar Dari Rumah (BDR). Unit yang <strong>TIDAK dicentang</strong> tetap menjalankan aturan KBM Tatap Muka normal dan validasi Geofence (radius 175 meter) tetap aktif.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                        @php
                            $selectedIds = (array) ($settings['pjj_school_ids'] ?? [2, 7]);
                        @endphp
                        @foreach($activeSchools as $school)
                            @php
                                $isChecked = in_array((int)$school->id, array_map('intval', $selectedIds), true);
                            @endphp
                            <label class="flex items-start gap-3 p-3.5 rounded-xl border-2 transition cursor-pointer {{ $isChecked ? 'bg-amber-50/80 border-amber-400 text-amber-950 font-bold' : 'bg-white border-slate-200 text-slate-700 hover:border-slate-300' }}">
                                <input type="checkbox" name="pjj_school_ids[]" value="{{ $school->id }}" {{ $isChecked ? 'checked' : '' }} class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500 mt-0.5">
                                <div class="text-xs">
                                    <div class="font-bold text-slate-900">{{ $school->name }}</div>
                                    <div class="text-[10px] text-slate-500 mt-0.5">
                                        Jenjang: <span class="font-black text-indigo-600 uppercase">{{ $school->type }}</span>
                                    </div>
                                    <div class="mt-1">
                                        @if($isChecked)
                                            <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase px-2 py-0.5 rounded-md bg-amber-200 text-amber-900">
                                                <i class="fas fa-house-laptop"></i> PJJ Aktif (Rumah)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[9px] font-bold uppercase px-2 py-0.5 rounded-md bg-slate-100 text-slate-600">
                                                <i class="fas fa-school"></i> Normal Tatap Muka
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Rentang Tanggal & Alasan Darurat -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Tanggal Mulai -->
                    <div class="bg-white/80 p-4 rounded-xl border border-amber-200 space-y-1.5">
                        <label for="pjj_start_date" class="block text-xs font-black uppercase text-slate-700">
                            Tanggal Mulai PJJ
                        </label>
                        <input type="date" id="pjj_start_date" name="pjj_start_date" 
                               value="{{ $settings['pjj_start_date'] ?? '2026-10-16' }}"
                               class="w-full text-sm font-bold px-3 py-2 rounded-lg border-2 border-slate-300 focus:border-amber-500 outline-none bg-white">
                        <p class="text-[10px] text-slate-500">Contoh: <code>16 Oktober 2026</code> (Sesuai SE Yayasan)</p>
                    </div>

                    <!-- Tanggal Selesai -->
                    <div class="bg-white/80 p-4 rounded-xl border border-amber-200 space-y-1.5">
                        <label for="pjj_end_date" class="block text-xs font-black uppercase text-slate-700">
                            Tanggal Selesai PJJ (Auto-Expiry)
                        </label>
                        <input type="date" id="pjj_end_date" name="pjj_end_date" 
                               value="{{ $settings['pjj_end_date'] ?? '2026-10-21' }}"
                               class="w-full text-sm font-bold px-3 py-2 rounded-lg border-2 border-slate-300 focus:border-amber-500 outline-none bg-white">
                        <p class="text-[10px] text-slate-500">Setelah tanggal ini, sistem otomatis kembali ke mode tatap muka.</p>
                    </div>

                    <!-- Alasan / Rujukan Surat Edaran -->
                    <div class="bg-white/80 p-4 rounded-xl border border-amber-200 space-y-1.5">
                        <label for="pjj_reason" class="block text-xs font-black uppercase text-slate-700">
                            Rujukan / Keterangan Darurat
                        </label>
                        <input type="text" id="pjj_reason" name="pjj_reason" 
                               value="{{ $settings['pjj_reason'] ?? 'Antisipasi Dampak Bencana Kabut Asap (Surat Edaran No. 07/SE/YP-PEMBDA/X/2026)' }}"
                               class="w-full text-xs font-medium px-3 py-2 rounded-lg border-2 border-slate-300 focus:border-amber-500 outline-none bg-white">
                        <p class="text-[10px] text-slate-500">Tercatat di log presensi dan rekap kehadiran.</p>
                    </div>
                </div>

                <!-- Opsi Guru & Auto-LMS -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex items-start gap-3 p-4 bg-white/90 rounded-xl border border-amber-200">
                        <input type="checkbox" id="pjj_allow_teachers" name="pjj_allow_teachers" value="1" {{ ($settings['pjj_allow_teachers'] ?? true) ? 'checked' : '' }} class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500 mt-1">
                        <div>
                            <label for="pjj_allow_teachers" class="text-xs font-bold text-slate-900">Izinkan Guru & Pegawai Presensi Daring (WFH)</label>
                            <p class="text-[11px] text-slate-500 mt-0.5">Bagi unit sekolah yang PJJ, Bapak/Ibu Guru dapat melakukan presensi mandiri dari rumah saat mengajar daring.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-4 bg-white/90 rounded-xl border border-amber-200">
                        <input type="checkbox" id="pjj_auto_lms_attendance" name="pjj_auto_lms_attendance" value="1" {{ ($settings['pjj_auto_lms_attendance'] ?? true) ? 'checked' : '' }} class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500 mt-1">
                        <div>
                            <label for="pjj_auto_lms_attendance" class="text-xs font-bold text-slate-900">Auto-Presensi Interaksi LMS Siswa</label>
                            <p class="text-[11px] text-slate-500 mt-0.5">Kehadiran siswa tercatat otomatis saat membuka modul, mengerjakan tugas, atau join Live Class di LMS (Sesuai SE Yayasan Butir 3).</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KATEGORI: GEOFENCING & RADIUS PRESENSI GPS -->
        <div class="mt-8 bg-white rounded-2xl shadow-md border border-gray-100 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-location-dot text-rose-500 text-2xl"></i>
                        Radius Geofencing & Koordinat Presensi GPS
                    </h2>
                    <p class="text-xs text-gray-500 mt-1">Atur jangkauan radius toleransi GPS agar Bapak/Ibu Guru dan Pegawai dapat melakukan presensi mandiri di area sekolah dengan lancar.</p>
                </div>
                <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-black rounded-full uppercase self-start sm:self-auto">
                    Geofencing Multi-Kampus Aktif
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Radius Maksimal (Meter) -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                    <label for="attendance_max_radius" class="block text-xs font-black uppercase text-slate-700">
                        Batas Radius Presensi (Meter)
                    </label>
                    <div class="relative">
                        <input type="number" id="attendance_max_radius" name="attendance_max_radius" 
                               value="{{ $settings['attendance_max_radius'] ?? 175 }}" min="20" max="2000" step="5"
                               class="w-full text-base font-black px-3.5 py-2.5 rounded-xl border-2 border-slate-300 focus:border-indigo-500 outline-none bg-white text-slate-800">
                        <span class="absolute right-3.5 top-2.5 text-xs font-bold text-slate-400">meter</span>
                    </div>
                    <p class="text-[11px] text-slate-500 font-medium leading-relaxed">
                        Standar Kompleks: <b>175 meter</b> untuk mencakup seluruh area gedung sekolah, ruang guru, serta toleransi deviasi GPS.
                    </p>
                </div>

                <!-- Latitude Kampus -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                    <label for="school_latitude" class="block text-xs font-black uppercase text-slate-700">
                        Latitude Titik Pusat Sekolah
                    </label>
                    <input type="text" id="school_latitude" name="school_latitude" 
                           value="{{ $settings['school_latitude'] ?? '1.28127778' }}"
                           class="w-full text-sm font-mono font-bold px-3.5 py-2.5 rounded-xl border-2 border-slate-300 focus:border-indigo-500 outline-none bg-white text-slate-800">
                    <p class="text-[11px] text-slate-500 font-medium">
                        Default: <code>1.28127778</code> (Kompleks Perguruan Pembda, Jl. Pelita No. 09 Gunungsitoli).
                    </p>
                </div>

                <!-- Longitude Kampus -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                    <label for="school_longitude" class="block text-xs font-black uppercase text-slate-700">
                        Longitude Titik Pusat Sekolah
                    </label>
                    <input type="text" id="school_longitude" name="school_longitude" 
                           value="{{ $settings['school_longitude'] ?? '97.62566667' }}"
                           class="w-full text-sm font-mono font-bold px-3.5 py-2.5 rounded-xl border-2 border-slate-300 focus:border-indigo-500 outline-none bg-white text-slate-800">
                    <p class="text-[11px] text-slate-500 font-medium">
                        Default: <code>97.62566667</code> (Kompleks Perguruan Pembda, Jl. Pelita No. 09 Gunungsitoli).
                    </p>
                </div>
            </div>
        </div>

        <!-- KATEGORI: WHATSAPP OTOMATIS -->
        <div class="mt-8 bg-white rounded-2xl shadow-md border border-gray-100 p-6">
            <h2 class="text-xl font-bold text-gray-900 border-b pb-3 mb-6 flex items-center gap-2">
                <i class="fab fa-whatsapp text-emerald-500 text-2xl"></i>
                Kelompok Pengiriman WhatsApp Otomatis
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- wa_send_psb_registration -->
                <div class="flex items-start gap-3 p-4 bg-slate-50 rounded-xl border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/10 transition">
                    <input type="checkbox" id="wa_send_psb_registration" name="wa_send_psb_registration" value="1" {{ $settings['wa_send_psb_registration'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                    <div>
                        <label for="wa_send_psb_registration" class="text-sm font-semibold text-gray-800">Pendaftaran & Verifikasi Prestasi PSB</label>
                        <p class="text-xs text-gray-500 mt-0.5 font-normal">Notifikasi saat pendaftaran berhasil dan persetujuan/penolakan berkas jalur prestasi.</p>
                    </div>
                </div>

                <!-- wa_send_psb_payment -->
                <div class="flex items-start gap-3 p-4 bg-slate-50 rounded-xl border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/10 transition">
                    <input type="checkbox" id="wa_send_psb_payment" name="wa_send_psb_payment" value="1" {{ $settings['wa_send_psb_payment'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                    <div>
                        <label for="wa_send_psb_payment" class="text-sm font-semibold text-gray-800">Konfirmasi Pembayaran PSB</label>
                        <p class="text-xs text-gray-500 mt-0.5 font-normal">Notifikasi tanda terima pendaftaran berbayar saat bukti transfer diverifikasi.</p>
                    </div>
                </div>

                <!-- wa_send_psb_test_schedule -->
                <div class="flex items-start gap-3 p-4 bg-slate-50 rounded-xl border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/10 transition">
                    <input type="checkbox" id="wa_send_psb_test_schedule" name="wa_send_psb_test_schedule" value="1" {{ $settings['wa_send_psb_test_schedule'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                    <div>
                        <label for="wa_send_psb_test_schedule" class="text-sm font-semibold text-gray-800">Verifikasi Dokumen & Jadwal Seleksi</label>
                        <p class="text-xs text-gray-500 mt-0.5 font-normal">Notifikasi kelengkapan dokumen persyaratan dan rincian waktu/tempat ujian masuk.</p>
                    </div>
                </div>

                <!-- wa_send_psb_acceptance -->
                <div class="flex items-start gap-3 p-4 bg-slate-50 rounded-xl border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/10 transition">
                    <input type="checkbox" id="wa_send_psb_acceptance" name="wa_send_psb_acceptance" value="1" {{ $settings['wa_send_psb_acceptance'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                    <div>
                        <label for="wa_send_psb_acceptance" class="text-sm font-semibold text-gray-800">Hasil Kelulusan & Kelulusan PSB</label>
                        <p class="text-xs text-gray-500 mt-0.5 font-normal">Notifikasi kelulusan seleksi calon siswa baru beserta tata cara daftar ulang.</p>
                    </div>
                </div>

                <!-- wa_send_payment_reminder -->
                <div class="flex items-start gap-3 p-4 bg-slate-50 rounded-xl border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/10 transition">
                    <input type="checkbox" id="wa_send_payment_reminder" name="wa_send_payment_reminder" value="1" {{ $settings['wa_send_payment_reminder'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                    <div>
                        <label for="wa_send_payment_reminder" class="text-sm font-semibold text-gray-800">Pemberitahuan Tagihan & SPP Siswa</label>
                        <p class="text-xs text-gray-500 mt-0.5 font-normal">Notifikasi pengingat jatuh tempo pembayaran SPP bulanan atau tunggakan siswa.</p>
                    </div>
                </div>

                <!-- wa_send_lms_notification -->
                <div class="flex items-start gap-3 p-4 bg-slate-50 rounded-xl border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/10 transition">
                    <input type="checkbox" id="wa_send_lms_notification" name="wa_send_lms_notification" value="1" {{ $settings['wa_send_lms_notification'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                    <div>
                        <label for="wa_send_lms_notification" class="text-sm font-semibold text-gray-800">Pengumuman Pembelajaran & LMS</label>
                        <p class="text-xs text-gray-500 mt-0.5 font-normal">Notifikasi publikasi materi, tugas, kuis, atau tatap muka kelas online yang dimulai.</p>
                    </div>
                </div>

                <!-- wa_send_counseling_record -->
                <div class="flex items-start gap-3 p-4 bg-slate-50 rounded-xl border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/10 transition">
                    <input type="checkbox" id="wa_send_counseling_record" name="wa_send_counseling_record" value="1" {{ $settings['wa_send_counseling_record'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                    <div>
                        <label for="wa_send_counseling_record" class="text-sm font-semibold text-gray-800">Pembinaan Siswa (Bimbingan Konseling)</label>
                        <p class="text-xs text-gray-500 mt-0.5 font-normal">Notifikasi kepada orang tua saat siswa mendapatkan catatan pelanggaran/pembinaan BK.</p>
                    </div>
                </div>

                <!-- wa_send_reputation_award -->
                <div class="flex items-start gap-3 p-4 bg-slate-50 rounded-xl border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/10 transition">
                    <input type="checkbox" id="wa_send_reputation_award" name="wa_send_reputation_award" value="1" {{ $settings['wa_send_reputation_award'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                    <div>
                        <label for="wa_send_reputation_award" class="text-sm font-semibold text-gray-800">Apresiasi Penghargaan & Poin Reputasi</label>
                        <p class="text-xs text-gray-500 mt-0.5 font-normal">Notifikasi saat siswa mendapatkan penghargaan, prestasi keaktifan, atau medali.</p>
                    </div>
                </div>

                <!-- wa_send_payment_receipt -->
                <div class="flex items-start gap-3 p-4 bg-slate-50 rounded-xl border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/10 transition">
                    <input type="checkbox" id="wa_send_payment_receipt" name="wa_send_payment_receipt" value="1" {{ $settings['wa_send_payment_receipt'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                    <div>
                        <label for="wa_send_payment_receipt" class="text-sm font-semibold text-gray-800">Bukti Penerimaan SPP / Uang Sekolah</label>
                        <p class="text-xs text-gray-500 mt-0.5 font-normal">Notifikasi bukti bayar iuran SPP lunas/berhasil ke WhatsApp siswa & orang tua.</p>
                    </div>
                </div>

                <!-- wa_send_teaching_reminder -->
                <div class="flex items-start gap-3 p-4 bg-slate-50 rounded-xl border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/10 transition">
                    <input type="checkbox" id="wa_send_teaching_reminder" name="wa_send_teaching_reminder" value="1" {{ $settings['wa_send_teaching_reminder'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                    <div>
                        <label for="wa_send_teaching_reminder" class="text-sm font-semibold text-gray-800">Pengingat Jadwal Mengajar Guru</label>
                        <p class="text-xs text-gray-500 mt-0.5 font-normal">Notifikasi otomatis harian untuk mengingatkan jadwal jam mengajar kepada masing-masing guru.</p>
                    </div>
                </div>

                <!-- wa_send_grade_published -->
                <div class="flex items-start gap-3 p-4 bg-slate-50 rounded-xl border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/10 transition">
                    <input type="checkbox" id="wa_send_grade_published" name="wa_send_grade_published" value="1" {{ $settings['wa_send_grade_published'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                    <div>
                        <label for="wa_send_grade_published" class="text-sm font-semibold text-gray-800">Penerbitan & Rilis Nilai Baru</label>
                        <p class="text-xs text-gray-500 mt-0.5 font-normal">Notifikasi rekap perolehan nilai kuis/tugas baru yang dipublikasikan oleh guru.</p>
                    </div>
                </div>

                <!-- wa_send_attendance_alert -->
                <div class="flex items-start gap-3 p-4 bg-slate-50 rounded-xl border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/10 transition">
                    <input type="checkbox" id="wa_send_attendance_alert" name="wa_send_attendance_alert" value="1" {{ $settings['wa_send_attendance_alert'] ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mt-1">
                    <div>
                        <label for="wa_send_attendance_alert" class="text-sm font-semibold text-gray-800">Notifikasi Presensi & Absensi Harian</label>
                        <p class="text-xs text-gray-500 mt-0.5 font-normal">Notifikasi otomatis status kehadiran (Sakit, Izin, Alpha, Terlambat) siswa kepada orang tua.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" class="bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-bold px-6 py-3 rounded-xl transition shadow flex items-center gap-2 hover:-translate-y-0.5 duration-200">
                <i class="fas fa-save"></i>
                Simpan Konfigurasi Otorisasi
            </button>
        </div>
    </form>
</div>
@endsection
