<?php

/**
 * WhatsApp Message Templates
 * 
 * Gunakan {variable} untuk placeholder yang akan diganti
 */

return [
    
    /**
     * PSB - Registration Confirmation
     */
    'psb.registration' => 
"✅ *PENDAFTARAN BERHASIL!*

Halo *{nama}*, pendaftaran Anda berhasil!

📋 No. Registrasi: *{nomor_registrasi}*
🏫 Sekolah: {sekolah}
📅 Tahun Ajaran: {tahun_ajaran}

🎯 *LANGKAH BERIKUTNYA:*
1. Transfer biaya pendaftaran Rp {biaya}
   Bank: {bank_name}
   No. Rek: {bank_account}
   a.n. {bank_holder}

2. Kirim bukti transfer ke WA ini dengan format:
   *BAYAR#{nomor_registrasi}#{nama}*

3. Tunggu konfirmasi dalam 1x24 jam

📧 Email detail sudah dikirim ke {email}
💬 Simpan nomor ini untuk komunikasi

Info: {contact_email}
Website: {website}",

    /**
     * PSB - Registration Confirmation for PRESTASI Path
     */
    'psb.registration.prestasi' => 
"🏆 *PENDAFTARAN JALUR PRESTASI BERHASIL!*

Halo *{nama}*, pendaftaran Anda melalui Jalur Prestasi berhasil!

📋 No. Registrasi: *{nomor_registrasi}*
🏫 Sekolah: {sekolah}
📅 Tahun Ajaran: {tahun_ajaran}
🎖️ Jalur: *PRESTASI (Juara Kelas)*

💰 *BEBAS BIAYA PENDAFTARAN!*
Karena Anda mendaftar melalui jalur prestasi, Anda TIDAK PERLU membayar biaya pendaftaran.

🎯 *PROSES VERIFIKASI:*
1. ⏳ Tim panitia akan memverifikasi dokumen prestasi Anda (Raport/Piagam Juara Kelas)
2. ⏱️ Estimasi: 2-3 hari kerja
3. 📲 Anda akan menerima notifikasi hasil verifikasi melalui WA & Email

✅ *Jika Disetujui:*
- Anda akan mendapat link untuk upload dokumen lengkap (KK, Akta, Ijazah, Raport)
- Kemudian akan dijadwalkan untuk tes masuk

❌ *Jika Ditolak:*
- Anda akan diberi tahu alasan penolakan
- Anda bisa melanjutkan ke jalur reguler dengan membayar biaya pendaftaran

📧 Email detail sudah dikirim ke {email}
💬 Simpan nomor ini untuk komunikasi

Info: {contact_email}
Website: {website}

_Terima kasih atas prestasi yang telah Anda raih! 🌟_",

    /**
     * PSB - Achievement Document Verification Result (APPROVED)
     */
    'psb.prestasi.approved' => 
"✅ *DOKUMEN PRESTASI DISETUJUI!*

Selamat *{nama}*! 🎉

Dokumen prestasi Anda telah diverifikasi dan *DISETUJUI* ✅

📋 No. Registrasi: *{nomor_registrasi}*
🏆 Prestasi: {prestasi_detail}

🎯 *LANGKAH SELANJUTNYA:*

1. 📤 Upload dokumen lengkap di:
   {upload_url}

2. 📄 Dokumen yang diperlukan:
   • Kartu Keluarga (KK)
   • Akta Kelahiran
   • Ijazah SMP/Sederajat
   • Raport Semester Terakhir
   • Pas Foto 3x4

3. ⏰ Deadline upload: {deadline}

4. 📅 Setelah dokumen lengkap, jadwal tes akan dikirimkan

💡 *Catatan:* Anda tetap BEBAS biaya pendaftaran

Terima kasih! 🙏",

    /**
     * PSB - Achievement Document Verification Result (REJECTED)
     */
    'psb.prestasi.rejected' => 
"❌ *DOKUMEN PRESTASI TIDAK DISETUJUI*

Halo *{nama}*,

Mohon maaf, dokumen prestasi Anda *TIDAK DISETUJUI* ❌

📋 No. Registrasi: *{nomor_registrasi}*
📝 Alasan: {alasan_penolakan}

🔄 *OPSI LANJUTAN:*

Anda dapat melanjutkan pendaftaran melalui *Jalur Reguler* dengan:

1. 💳 Transfer biaya pendaftaran Rp {biaya}
   Bank: {bank_name}
   No. Rek: {bank_account}
   a.n. {bank_holder}

2. 📲 Kirim bukti transfer ke WA ini dengan format:
   *BAYAR#{nomor_registrasi}#{nama}*

3. ⏱️ Tunggu konfirmasi dalam 1x24 jam

Jika ada pertanyaan, hubungi:
📞 {contact_phone}
📧 {contact_email}

Semangat! 💪",

    /**
     * PSB - Payment Received
     */
    'psb.payment' => 
"✅ *PEMBAYARAN DITERIMA!*

*{nama}*, pembayaran Anda sudah terverifikasi ✅

📋 Registrasi: *{nomor_registrasi}*
💰 Jumlah: Rp {jumlah}
📅 Tanggal: {tanggal}

🎯 *UPLOAD DOKUMEN:*
Silakan upload dokumen pendaftaran di:
{upload_url}

📄 Dokumen yang diperlukan:
1. Kartu Keluarga (KK)
2. Akta Kelahiran
3. Ijazah SMP/Sederajat
4. Pas Foto 3x4

⏰ Deadline upload: {deadline}

💡 Setelah dokumen lengkap, jadwal tes akan dikirim!

Terima kasih 🙏",

    /**
     * PSB - Document Verified
     */
    'psb.document' => 
"✅ *DOKUMEN LENGKAP!*

*{nama}*, dokumen Anda sudah diverifikasi ✅

📋 Status: *Siap Tes*

📅 *INFO JADWAL TES:*
Tanggal: {tanggal_tes}
Waktu: {waktu_tes}
Tempat: {tempat_tes}

📝 Yang perlu dibawa:
• Kartu peserta (download di website)
• Alat tulis
• Masker

🔗 Download kartu peserta:
{download_url}

⚠️ Harap hadir 30 menit sebelum tes dimulai

Semangat! 💪",

    /**
     * PSB - Test Schedule
     */
    'psb.test_schedule' => 
"📅 *PENGUMUMAN JADWAL TES*

Halo *{nama}*,

Berikut jadwal tes masuk:

📋 No. Registrasi: *{nomor_registrasi}*
📅 Tanggal: *{tanggal}*
🕐 Waktu: *{waktu}*
📍 Tempat: *{tempat}*

📝 *KETENTUAN:*
• Hadir 30 menit lebih awal
• Bawa kartu peserta & alat tulis
• Berpakaian rapi
• Menggunakan masker

🔗 Download kartu peserta:
{kartu_url}

Jika ada pertanyaan, hubungi:
📞 {contact_phone}
📧 {contact_email}

Sukses untuk tesnya! 🎓",

    /**
     * PSB - Test Result (Accepted)
     */
    'psb.accepted' => 
"🎉 *SELAMAT!*

*{nama}*, Anda DITERIMA di:

🏫 {sekolah}
📚 {program_keahlian}

📋 No. Registrasi: *{nomor_registrasi}*

🎯 *LANGKAH SELANJUTNYA:*

1. Daftar ulang:
   📅 {tanggal_daftar_ulang}
   📍 {tempat_daftar_ulang}

2. Dokumen yang dibawa:
   • Ijazah asli + fotokopi
   • KK asli + fotokopi
   • Akta asli + fotokopi
   • Pas foto 3x4 (4 lembar)
   • Materai 10.000 (2 lembar)

3. Biaya daftar ulang:
   💰 Rp {biaya_daftar_ulang}

⚠️ Jika tidak daftar ulang sebelum {deadline}, dianggap mengundurkan diri.

Selamat bergabung! 🎓",

    /**
     * PSB - Reminder
     */
    'psb.reminder' => 
"⏰ *REMINDER*

Halo *{nama}*,

{pesan_reminder}

📋 No. Registrasi: *{nomor_registrasi}*

Untuk informasi lebih lanjut:
📞 {contact_phone}
📧 {contact_email}

Terima kasih 🙏",

    /**
     * General Notification
     */
    'general.notification' => 
"📢 *PEMBERITAHUAN*

Halo *{nama}*,

{pesan}

Terima kasih 🙏

---
{footer}",

    /**
     * Payment Reminder
     */
    'payment.reminder' => 
"💰 *PENGINGAT PEMBAYARAN*

Halo *{nama}*,

Anda memiliki tagihan yang belum dibayar:

📋 Tagihan: {jenis_tagihan}
💵 Jumlah: Rp {jumlah}
⏰ Jatuh tempo: {jatuh_tempo}

Silakan lakukan pembayaran ke:
Bank: {bank_name}
No. Rek: {bank_account}
a.n. {bank_holder}

Setelah transfer, konfirmasi ke:
📞 {contact_phone}

Terima kasih 🙏",

    /**
     * LMS - New Material Published
     */
    'lms.material.published' => 
"📚 *MATERI BARU TERSEDIA!*

Halo *{nama}*,

Guru Anda *{teacher_name}* baru saja menerbitkan materi baru di kelas online.

🏫 Course: *{course_name}*
📖 Judul Materi: *{title}*

Mari baca dan pelajari materinya sekarang untuk meningkatkan pemahaman Anda.

🎯 Akses Course:
{link}

Semangat belajar! 🎓",

    /**
     * LMS - New Assignment Published
     */
    'lms.assignment.published' => 
"📝 *TUGAS BARU DIBUAT!*

Halo *{nama}*,

Ada tugas baru yang harus Anda selesaikan di kelas online.

🏫 Course: *{course_name}*
✍️ Tugas: *{title}*
⏰ Batas Pengumpulan: *{due_date}*

Pastikan Anda membaca petunjuk pengerjaan dan mengumpulkan sebelum batas waktu.

🎯 Akses & Kumpulkan Tugas:
{link}

Jangan menunda pekerjaan Anda! 💪",

    /**
     * LMS - New Quiz Published
     */
    'lms.quiz.published' => 
"✏️ *KUIS BARU DIBUAT!*

Halo *{nama}*,

Guru Anda telah mempublikasikan kuis baru di kelas online.

🏫 Course: *{course_name}*
🏆 Kuis: *{title}*

Persiapkan diri Anda dengan baik sebelum memulai kuis ini.

🎯 Kerjakan Kuis:
{link}

Semoga sukses! 🌟",

    /**
     * LMS - Virtual Meeting Started
     */
    'lms.meeting.started' => 
"📹 *KELAS TATAP MUKA VIRTUAL DIMULAI!*

Halo *{nama}*,

Guru Anda *{teacher_name}* telah memulai kelas tatap muka virtual (Video Conference) sekarang!

🏫 Course: *{course_name}*
⏰ Mulai: Baru saja

Silakan segera bergabung ke ruang meeting kelas untuk mengikuti penjelasan materi secara langsung.

🎯 Gabung Meeting Kelas:
{link}

Diharapkan hadir tepat waktu! 🤝",

    /**
     * Counseling - New counseling record (pembinaan)
     */
    'student.counseling' => 
"⚠️ *CATATAN PEMBINAAN SISWA*

Halo Orang Tua/Wali dari *{nama}*,

Kami menginformasikan bahwa siswa yang bersangkutan hari ini mendapatkan catatan pembinaan/konseling:

📋 Kejadian: *{title}*
📝 Keterangan: {reason}
💡 Tindak Lanjut: {action}

Mari bersama-sama membimbing putra/putri kita agar menjadi lebih baik. Jika ada hal yang ingin didiskusikan, silakan hubungi wali kelas atau guru BK.

Terima kasih 🙏",

    /**
     * Reputation - New award (penghargaan)
     */
    'student.award' => 
"🎉 *APRESIASI PENGHARGAAN SISWA*

Selamat kepada *{nama}*! 🏆

Siswa telah mendapatkan penghargaan prestasi/keaktifan hari ini:

📋 Kategori: *{title}*
🎖️ Poin Tambahan: +{points} Poin
📝 Deskripsi: {reason}

Terima kasih atas dedikasi dan kerja kerasnya. Teruslah berprestasi dan menginspirasi teman-teman lainnya! 🌟",

    /**
     * Payment - Receipt/Confirmation
     */
    'payment.receipt' => 
"✅ *PEMBAYARAN SPP / UANG SEKOLAH BERHASIL!*

Halo *{nama}*, pembayaran Anda telah diterima dan diverifikasi:

📋 No. Transaksi: *{transaction_id}*
💵 Jumlah: Rp {jumlah}
📅 Tanggal: {tanggal}
🏷️ Keterangan: SPP/Iuran Bulan *{bulan}*

Status Tagihan: *LUNAS* ✅

Terima kasih atas pembayaran tepat waktu. Bukti kuitansi resmi dapat diunduh di portal siswa. 🙏",

    /**
     * Teacher - Teaching Schedule Reminder
     */
    'teacher.teaching_reminder' => 
"⏰ *PENGINGAT JADWAL MENGAJAR*

Halo Bapak/Ibu *{teacher_name}*,

Mengingatkan jadwal mengajar Anda hari ini:

🏫 Kelas: *{classroom_name}*
📚 Mata Pelajaran: *{subject_name}*
🕐 Jam/Sesi: *{time_slot}*

Harap hadir tepat waktu di ruang kelas. Terima kasih atas dedikasi Bapak/Ibu guru! 👨‍🏫👩‍🏫",

    /**
     * Grades - Grade Published
     */
    'student.grade_published' => 
"📊 *PENGUMUMAN NILAI BARU*

Halo *{nama}*,

Nilai baru untuk evaluasi belajar Anda telah diterbitkan:

🏫 Kelas: {classroom_name}
📚 Mata Pelajaran: {subject_name}
📝 Jenis Nilai: *{grade_type}* (Kuis/Tugas)
Halo *{nama}*,

{pesan}

Terima kasih 🙏

---
{footer}",

    /**
     * Payment Reminder
     */
    'payment.reminder' => 
"💰 *PENGINGAT PEMBAYARAN*

Halo *{nama}*,

Anda memiliki tagihan yang belum dibayar:

📋 Tagihan: {jenis_tagihan}
💵 Jumlah: Rp {jumlah}
⏰ Jatuh tempo: {jatuh_tempo}

Silakan lakukan pembayaran ke:
Bank: {bank_name}
No. Rek: {bank_account}
a.n. {bank_holder}

Setelah transfer, konfirmasi ke:
📞 {contact_phone}

Terima kasih 🙏",

    /**
     * LMS - New Material Published
     */
    'lms.material.published' => 
"📚 *MATERI BARU TERSEDIA!*

Halo *{nama}*,

Guru Anda *{teacher_name}* baru saja menerbitkan materi baru di kelas online.

🏫 Course: *{course_name}*
📖 Judul Materi: *{title}*

Mari baca dan pelajari materinya sekarang untuk meningkatkan pemahaman Anda.

🎯 Akses Course:
{link}

Semangat belajar! 🎓",

    /**
     * LMS - New Assignment Published
     */
    'lms.assignment.published' => 
"📝 *TUGAS BARU DIBUAT!*

Halo *{nama}*,

Ada tugas baru yang harus Anda selesaikan di kelas online.

🏫 Course: *{course_name}*
✍️ Tugas: *{title}*
⏰ Batas Pengumpulan: *{due_date}*

Pastikan Anda membaca petunjuk pengerjaan dan mengumpulkan sebelum batas waktu.

🎯 Akses & Kumpulkan Tugas:
{link}

Jangan menunda pekerjaan Anda! 💪",

    /**
     * LMS - New Quiz Published
     */
    'lms.quiz.published' => 
"✏️ *KUIS BARU DIBUAT!*

Halo *{nama}*,

Guru Anda telah mempublikasikan kuis baru di kelas online.

🏫 Course: *{course_name}*
🏆 Kuis: *{title}*

Persiapkan diri Anda dengan baik sebelum memulai kuis ini.

🎯 Kerjakan Kuis:
{link}

Semoga sukses! 🌟",

    /**
     * LMS - Virtual Meeting Started
     */
    'lms.meeting.started' => 
"📹 *KELAS TATAP MUKA VIRTUAL DIMULAI!*

Halo *{nama}*,

Guru Anda *{teacher_name}* telah memulai kelas tatap muka virtual (Video Conference) sekarang!

🏫 Course: *{course_name}*
⏰ Mulai: Baru saja

Silakan segera bergabung ke ruang meeting kelas untuk mengikuti penjelasan materi secara langsung.

🎯 Gabung Meeting Kelas:
{link}

Diharapkan hadir tepat waktu! 🤝",

    /**
     * Counseling - New counseling record (pembinaan)
     */
    'student.counseling' => 
"⚠️ *CATATAN PEMBINAAN SISWA*

Halo Orang Tua/Wali dari *{nama}*,

Kami menginformasikan bahwa siswa yang bersangkutan hari ini mendapatkan catatan pembinaan/konseling:

📋 Kejadian: *{title}*
📝 Keterangan: {reason}
💡 Tindak Lanjut: {action}

Mari bersama-sama membimbing putra/putri kita agar menjadi lebih baik. Jika ada hal yang ingin didiskusikan, silakan hubungi wali kelas atau guru BK.

Terima kasih 🙏",

    /**
     * Reputation - New award (penghargaan)
     */
    'student.award' => 
"🎉 *APRESIASI PENGHARGAAN SISWA*

Selamat kepada *{nama}*! 🏆

Siswa telah mendapatkan penghargaan prestasi/keaktifan hari ini:

📋 Kategori: *{title}*
🎖️ Poin Tambahan: +{points} Poin
📝 Deskripsi: {reason}

Terima kasih atas dedikasi dan kerja kerasnya. Teruslah berprestasi dan menginspirasi teman-teman lainnya! 🌟",

    /**
     * Payment - Receipt/Confirmation
     */
    'payment.receipt' => 
"✅ *PEMBAYARAN SPP / UANG SEKOLAH BERHASIL!*

Halo *{nama}*, pembayaran Anda telah diterima dan diverifikasi:

📋 No. Transaksi: *{transaction_id}*
💵 Jumlah: Rp {jumlah}
📅 Tanggal: {tanggal}
🏷️ Keterangan: SPP/Iuran Bulan *{bulan}*

Status Tagihan: *LUNAS* ✅

Terima kasih atas pembayaran tepat waktu. Bukti kuitansi resmi dapat diunduh di portal siswa. 🙏",

    /**
     * Teacher - Teaching Schedule Reminder
     */
    'teacher.teaching_reminder' => 
"⏰ *PENGINGAT JADWAL MENGAJAR*

Halo Bapak/Ibu *{teacher_name}*,

Mengingatkan jadwal mengajar Anda hari ini:

🏫 Kelas: *{classroom_name}*
📚 Mata Pelajaran: *{subject_name}*
🕐 Jam/Sesi: *{time_slot}*

Harap hadir tepat waktu di ruang kelas. Terima kasih atas dedikasi Bapak/Ibu guru! 👨‍🏫👩‍🏫",

    /**
     * Grades - Grade Published
     */
    'student.grade_published' => 
"📊 *PENGUMUMAN NILAI BARU*

Halo *{nama}*,

Nilai baru untuk evaluasi belajar Anda telah diterbitkan:

🏫 Kelas: {classroom_name}
📚 Mata Pelajaran: {subject_name}
📝 Jenis Nilai: *{grade_type}* (Kuis/Tugas)
💯 Nilai Anda: *{score}*
💡 Catatan: {notes}

Silakan cek detail nilai lengkap di portal siswa. Tingkatkan terus belajarnya! 🚀",

    /**
     * Attendance - Student Attendance Alert
     */
    'student.attendance' => 
"📢 *NOTIFIKASI KEHADIRAN SISWA*

Halo Orang Tua/Wali dari *{nama}*,

Menginfokan status kehadiran putra/putri Anda hari ini:

📅 Tanggal: {tanggal}
🏫 Kelas: {classroom_name}
🏷️ Status Absensi: *{status}*

_Catatan: Jika status Izin/Sakit, mohon kirimkan surat keterangan resmi ke wali kelas._

Terima kasih atas kerja samanya. 🙏",

    /**
     * Attendance - Teacher Attendance Alert
     */
    'teacher.attendance' => 
"👨‍🏫 *NOTIFIKASI PRESENSI KEHADIRAN GURU*

Halo Bapak/Ibu *{nama}*,

Presensi kehadiran Anda telah berhasil dicatat oleh sistem:

📅 Tanggal: *{tanggal}*
⏰ Waktu Scan: *{waktu} WIB*
🏷️ Status: *{status}* ({tipe_absen})
💼 Jabatan: {jabatan}

Terima kasih atas dedikasi dan kehadiran Bapak/Ibu hari ini! 🙏",

    /**
     * Attendance - Staff / Employee Attendance Alert
     */
    'employee.attendance' => 
"💼 *NOTIFIKASI PRESENSI KEHADIRAN PEGAWAI*

Halo Bapak/Ibu *{nama}*,

Presensi kehadiran Pegawai/Staf telah berhasil dicatat oleh sistem:

📅 Tanggal: *{tanggal}*
⏰ Waktu Scan: *{waktu} WIB*
🏷️ Status: *{status}* ({tipe_absen})
💼 Jabatan/Unit: {jabatan}

Terima kasih atas disiplin kerja dan pengabdian Bapak/Ibu hari ini! 🙏",

    /**
     * Executive - Principal Daily Attendance Digest (Kepala Sekolah)
     */
    'executive.principal_daily_attendance' =>
"{🏫|🏛️|🏢} *{LAPORAN REKAPITULASI|REKAP EKSEKUTIF|RINGKASAN KEHADIRAN|LAPORAN KEHADIRAN HARIAN} {sekolah}*
{📌|📋|✉️} *{Kepada Yth.|Yth. Bapak/Ibu|Yth.} {nama_kepsek}*

{salam_pembuka}

{📅|🗓️} Tanggal: *{tanggal}*
{⏰|🕒} Waktu Rekap: *{15 Menit Pasca Batas Toleransi ({waktu_rekap})|Pukul {waktu_rekap} (Sesi Masuk Pagi)|Rekapitulasi Harian ({waktu_rekap})}*

{👨‍🎓|🧑‍🎓} *1. {KEHADIRAN SISWA|PRESENSI SISWA} (Total: {total_siswa} Siswa):*
• {✅|🟢} Hadir Tepat Waktu: *{siswa_hadir}* | {🕒|🟡} Terlambat: *{siswa_terlambat}*
• {🤒|🏥} Sakit: *{siswa_sakit}* | {📩|📝} Izin: *{siswa_izin}* | {❌|🚫} Alpha: *{siswa_alpha}*
• {⏳|⌛} Belum Presensi: *{siswa_belum_presensi}*

{👨‍🏫|👩‍🏫} *2. {KEHADIRAN GURU & TENAGA PENDIDIK|PRESENSI GURU & TENDIK}:*
• {✅|🟢} Hadir: *{guru_hadir}* | {🚗|💼} Dinas Luar: *{guru_dinas}*
• {🤒|🏥} Sakit: *{guru_sakit}* | {📩|📝} Izin: *{guru_izin}* | {❌|🚫} Alpha: *{guru_alpha}*

{💼|🏢} *3. {KEHADIRAN PEGAWAI & STAF TATA USAHA|PRESENSI STAF TATA USAHA & KARYAWAN}:*
• {✅|🟢} Hadir: *{pegawai_hadir}* | {🏖️|🌴} Cuti: *{pegawai_cuti}*
• {🤒|🏥} Sakit: *{pegawai_sakit}* | {📩|📝} Izin: *{pegawai_izin}* | {❌|🚫} Alpha: *{pegawai_alpha}*

{💡|📌} *Catatan:* {Rincian lengkap per kelas dapat dipantau di Portal Admin PembdaHUB.|Data real-time selengkapnya dapat diakses melalui Dashboard SIM PembdaHUB.|Pantau monitoring kehadiran kelas langsung via portal sistem PembdaHUB.}

{catatan_penutup}

{motto}

---
{_Dikirim otomatis oleh PembdaHUB Executive System_|_Layanan Otomatis SIM PembdaHUB_|_Executive Report System Perguruan Pembda Nias_}",

    /**
     * Executive - Homeroom Daily Attendance Digest (Wali Kelas)
     */
    'executive.homeroom_daily_attendance' =>
"{👩‍🏫|👨‍🏫|📊} *{REKAP KEHADIRAN HARIAN KELAS|LAPORAN PRESENSI HARIAN KELAS|RINGKASAN KEHADIRAN SISWA KELAS} {kelas}*
{📌|📋|✉️} *{Yth. Wali Kelas:|Kepada Yth. Wali Kelas:|Wali Kelas Binaan:} {nama_wali_kelas}*

{salam_pembuka}

{📅|🗓️} Tanggal: *{tanggal}*
{⏰|🕒} Waktu Rekap: *{15 Menit Pasca Batas Toleransi ({waktu_rekap})|Pukul {waktu_rekap}|Sesi Masuk Pagi ({waktu_rekap})}*

{📊|📈} *{RINGKASAN KEHADIRAN SISWA KELAS|REKAPITULASI PRESENSI KELAS} {kelas}:*
• {👥|🧑‍🤝‍🧑} Total Siswa: *{total_siswa} Siswa*
• {🏫|🏢} Total Fisik Hadir: *{total_fisik_hadir} Siswa ({persen_hadir}%)*
  - {🟢|✅} Hadir Tepat Waktu: *{hadir} Siswa*
  - {🟡|🕒} Hadir Terlambat  : *{terlambat} Siswa*
• {🚫|❌} Tidak Hadir Fisik  : *{total_tidak_hadir} Siswa*
  - {🤒|🏥} Sakit: *{sakit}* | {📩|📝} Izin: *{izin}* | {❌|⛔} Alpha: *{alpha}*
• {⏳|⌛} Belum Presensi: *{belum_presensi} Siswa*

{📋|📝} *{CATATAN KETERLAMBATAN & KETIDAKHADIRAN:|RINCIAN SISWA TIDAK HADIR / TERLAMBAT:|DAFTAR REKAP SISWA BERKENDALA HARI INI:}*
{daftar_tidak_hadir}

{catatan_penutup}

{motto}

---
{_Dikirim otomatis oleh PembdaHUB Executive System_|_Layanan Notifikasi Presensi SIM PembdaHUB_|_Sistem Rekapitulasi Terpadu PembdaHUB_}",

    /**
     * Teacher & Employee - Individual Tap Attendance Notification
     */
    'teacher.attendance' =>
"{✅|🟢|📲} *{PRESENSI BERHASIL DICATAT|KONFIRMASI KEHADIRAN MASUK|NOTIFIKASI TAP KEHADIRAN}*

{Halo|Selamat pagi|Salam hangat}, *{nama}*! {👋|🙏|✨}
Presensi {kehadiran|masuk|KBM} Anda hari ini telah {terekam di sistem|berhasil diverifikasi|tercatat resmi di server SIM}:

{📋|📌} Jabatan: *{jabatan}*
{📅|🗓️} Tanggal: *{tanggal}*
{⏰|🕒} Waktu Tap: *{waktu} WIB*
{📊|🎯} Status: *{status}*

{Selamat menjalankan tugas dan selamat beraktivitas! 🌟|Terima kasih telah hadir tepat waktu. Selamat bertugas! 👨‍🏫|Semoga aktivitas hari ini penuh kelancaran dan berkah. ✨|Terima kasih atas dedikasi dan kedisiplinan Anda. 🙏}

{motto}

---
{_Notifikasi Otomatis Gate/Kiosk PembdaHUB_|_SIM Terpadu Perguruan Pembda Nias_|_Presensi Digital PembdaHUB_}",

    'employee.attendance' =>
"{✅|🟢|📲} *{PRESENSI BERHASIL DICATAT|KONFIRMASI KEHADIRAN PEGAWAI|NOTIFIKASI TAP MASUK}*

{Halo|Selamat pagi|Salam hangat}, *{nama}*! {👋|🙏|✨}
Presensi {kehadiran|masuk kerja} Anda hari ini telah {terekam di sistem|berhasil diverifikasi|tercatat resmi di server SIM}:

{💼|📌} Posisi/Jabatan: *{jabatan}*
{📅|🗓️} Tanggal: *{tanggal}*
{⏰|🕒} Waktu Tap: *{waktu} WIB*
{📊|🎯} Status: *{status}*

{Selamat menjalankan tugas dan pelayanan hari ini! 🌟|Terima kasih telah hadir tepat waktu di kantor. ✨|Semoga aktivitas dinas hari ini berjalan lancar dan berkah. 🙏}

{motto}

---
{_Notifikasi Otomatis Gate/Kiosk PembdaHUB_|_SIM Terpadu Perguruan Pembda Nias_|_Presensi Digital PembdaHUB_}",

    /**
     * Executive - Principal Monthly SPP Digest
     */
    'executive.principal_monthly_spp' =>
"💰 *BERITA REKAPITULASI KEUANGAN SPP BULANAN*
📌 *Kepada Yth. Kepala Sekolah Perguruan Pembda*

📅 Periode Bulan: *{periode_bulan}*

📊 *RINGKASAN KEUANGAN SPP SEKOLAH:*
• 💵 Total SPP Terbayar (LUNAS): *Rp {total_lunas}*
• ⚠️ Jumlah Siswa Menunggak SPP: *{total_penunggak} Siswa*
• 🔻 Total Nilai SPP Belum Terbayar: *Rp {total_tunggakan}*

💡 *Rekomendasi Action:* Laporan rincian penunggak per kelas dapat diunduh di menu Keuangan PembdaHUB untuk ditindaklanjuti Wali Kelas.

---
_Dikirim otomatis oleh PembdaHUB Executive System_",

    /**
     * Executive - Homeroom Monthly SPP Digest
     */
    'executive.homeroom_monthly_spp' =>
"💳 *REKAP SPP BULANAN KELAS {kelas}*
📌 *Yth. Wali Kelas: {nama_wali_kelas}*

📅 Periode Bulan: *{periode_bulan}*

📊 *RINGKASAN SPP KELAS {kelas}:*
• ⚠️ Jumlah Siswa Belum Bayar: *{jumlah_penunggak} Siswa*
• 💰 Total Value Belum Terbayar: *Rp {total_tunggakan}*

📋 *DAFTAR SISWA BELUM BAYAR SPP:*
{daftar_penunggak}

---
_Dikirim otomatis oleh PembdaHUB Executive System_",
];
