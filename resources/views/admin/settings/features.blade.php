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
                           value="{{ $settings['school_latitude'] ?? '1.282500' }}"
                           class="w-full text-sm font-mono font-bold px-3.5 py-2.5 rounded-xl border-2 border-slate-300 focus:border-indigo-500 outline-none bg-white text-slate-800">
                    <p class="text-[11px] text-slate-500 font-medium">
                        Default: <code>1.282500</code> (Kompleks Jl. Pelita No. 09 Gunungsitoli).
                    </p>
                </div>

                <!-- Longitude Kampus -->
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                    <label for="school_longitude" class="block text-xs font-black uppercase text-slate-700">
                        Longitude Titik Pusat Sekolah
                    </label>
                    <input type="text" id="school_longitude" name="school_longitude" 
                           value="{{ $settings['school_longitude'] ?? '97.619000' }}"
                           class="w-full text-sm font-mono font-bold px-3.5 py-2.5 rounded-xl border-2 border-slate-300 focus:border-indigo-500 outline-none bg-white text-slate-800">
                    <p class="text-[11px] text-slate-500 font-medium">
                        Default: <code>97.619000</code> (Kompleks Jl. Pelita No. 09 Gunungsitoli).
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
