<?php

namespace App\Services;

use Throwable;

class ErrorDiagnosticService
{
    /**
     * Diagnose an exception or error message into human-friendly Indonesian explanation.
     */
    public static function diagnose(Throwable|string $error): array
    {
        $message = is_string($error) ? $error : $error->getMessage();
        $class = is_string($error) ? 'RawError' : get_class($error);

        // 1. Column Not Found in SQL (SQLSTATE 42S22 / Error 1054)
        if (str_contains($message, '42S22') || (str_contains($message, 'Unknown column') && str_contains($message, '1054'))) {
            preg_match("/Unknown column '([^']+)'/i", $message, $m);
            $column = $m[1] ?? 'tertentu';
            return [
                'type' => 'Database - Kolom Tidak Ditemukan',
                'badge' => 'DATABASE SCHEMA',
                'danger_level' => 'low',
                'danger_label' => '🟢 Aman (Data Tidak Rusak)',
                'problem' => "Sistem mencoba membaca atau mengisi kolom <code>{$column}</code>, namun kolom tersebut belum ada di tabel database.",
                'impact' => "Operasi simpan/baca pada fitur yang bersangkutan tertahan sementara. Seluruh data lain di database tetap aman 100% dan tidak ada yang hilang/rusak.",
                'solution' => "Jika ini adalah kolom <code>updated_at</code>/<code>created_at</code> pada model tanpa timestamps, tambahkan <code>public \$timestamps = false;</code> pada model Eloquent-nya. Jika kolom baru, buat dan jalankan migrasi database.",
                'raw' => $message,
            ];
        }

        // 2. Table Already Exists (SQLSTATE 42S01 / Error 1050)
        if (str_contains($message, '42S01') || (str_contains($message, 'already exists') && str_contains($message, '1050'))) {
            preg_match("/Table '([^']+)' already exists/i", $message, $m);
            $table = $m[1] ?? 'tabel';
            return [
                'type' => 'Database - Tabel Sudah Ada Sebelumnya',
                'badge' => 'MIGRATION NOTICE',
                'danger_level' => 'low',
                'danger_label' => '🟢 Sangat Aman (Tidak Ada Data Hilang)',
                'problem' => "Sistem migrasi otomatis mencoba membuat tabel <code>{$table}</code>, padahal tabel tersebut sudah ada dan sedang aktif di database.",
                'impact' => "Sama sekali tidak merusak data. Tabel dan seluruh isi data Anda tetap aman dan utuh.",
                'solution' => "Bungkus instruksi pembuatan tabel di file migration dengan proteksi <code>if (!Schema::hasTable('{$table}'))</code> agar Laravel melewatinya secara otomatis.",
                'raw' => $message,
            ];
        }

        // 3. Blade View Section Error (Cannot end a section without first starting one)
        if (str_contains($message, 'Cannot end a section without first starting one')) {
            preg_match("/View: ([^\\)]+)/i", $message, $m);
            $viewPath = !empty($m[1]) ? basename($m[1]) : 'file Blade';
            return [
                'type' => 'Tampilan - Kesalahan Tag Blade Template',
                'badge' => 'BLADE VIEW ERROR',
                'danger_level' => 'low',
                'danger_label' => '🟢 Aman (Hanya Masalah Tampilan)',
                'problem' => "Pada file template tampilan (<code>{$viewPath}</code>), terdapat tag penutup <code>@endsection</code> di baris bawah, namun tag pembuka <code>@extends(...)</code> atau <code>@section('content')</code> di baris paling atas lupa ditulis.",
                'impact' => "Halaman web tersebut gagal dimuat di browser pengguna, namun data akun, nilai, dan database tidak terpengaruh sama sekali.",
                'solution' => "Buka file <code>{$viewPath}</code> dan tambahkan tag <code>@extends('layouts.app')</code> dan <code>@section('content')</code> pada baris pertama file.",
                'raw' => $message,
            ];
        }

        // 4. View Not Found (InvalidArgumentException)
        if (str_contains($message, 'View [') && str_contains($message, '] not found')) {
            preg_match("/View \[([^\]]+)\] not found/i", $message, $m);
            $viewName = $m[1] ?? 'tertentu';
            return [
                'type' => 'Tampilan - File View Tidak Ditemukan',
                'badge' => 'MISSING VIEW',
                'danger_level' => 'low',
                'danger_label' => '🟢 Aman',
                'problem' => "Controller memanggil file template tampilan <code>{$viewName}</code>, namun file <code>.blade.php</code> tersebut belum dibuat di folder <code>resources/views/</code>.",
                'impact' => "Pengguna mendapat pesan error 500 saat mengakses URL terkait.",
                'solution' => "Buat file blade baru di path <code>resources/views/" . str_replace('.', '/', $viewName) . ".blade.php</code>.",
                'raw' => $message,
            ];
        }

        // 5. Duplicate Entry in SQL (SQLSTATE 23000 / Error 1062)
        if (str_contains($message, '1062') || str_contains($message, 'Duplicate entry')) {
            preg_match("/Duplicate entry '([^']+)'/i", $message, $m);
            $entry = $m[1] ?? 'data';
            return [
                'type' => 'Database - Duplikasi Data / Race Condition',
                'badge' => 'DUPLICATE ENTRY (1062)',
                'danger_level' => 'low',
                'danger_label' => '🟢 Aman (Data Terlindungi dari Duplikasi)',
                'problem' => "Sistem mencoba memasukkan data ganda (<code>{$entry}</code>) pada tabel yang mengharuskan nilai unik (misal: dua request bersamaan saat membuka materi LMS).",
                'impact' => "Database secara otomatis menolak pencatatan ganda. Data yang sudah ada tetap aman dan tidak rusak/berantakan.",
                'solution' => "Gunakan metode <code>firstOrCreate()</code> atau <code>updateOrCreate()</code> dengan blok pengaman <code>try-catch</code> pada controller fitur terkait.",
                'raw' => $message,
            ];
        }

        // 6. Foreign Key / Integrity Constraint Violation (SQLSTATE 23000)
        if (str_contains($message, '23000') || str_contains($message, 'foreign key constraint fails')) {
            return [
                'type' => 'Database - Proteksi Integritas Relasi Data',
                'badge' => 'DATA INTEGRITY SHIELD',
                'danger_level' => 'medium',
                'danger_label' => '🟡 Proteksi Aktif (Data Terlindungi)',
                'problem' => "Sistem menolak penghapusan data karena data induk ini masih terhubung dengan data penting lainnya (misal: menghapus Kelas yang masih memiliki siswa/jadwal, atau Tahun Pelajaran aktif).",
                'impact' => "Proteksi database bekerja sempurna! Data Anda berhasil diselamatkan dari potensi hilang/terhapus tanpa sengaja.",
                'solution' => "Jangan menghapus data induk yang masih memiliki relasi. Jika memang harus dihapus, pindahkan atau hapus relasi data anaknya terlebih dahulu.",
                'raw' => $message,
            ];
        }

        // 6. Git Memory / Thread Limit on Shared Hosting
        if (str_contains($message, 'unable to create thread') || str_contains($message, 'gc.log') || str_contains($message, 'failed to run repack')) {
            return [
                'type' => 'Server - Batasan Thread Hosting (Git GC)',
                'badge' => 'HOSTING RESOURCE NOTICE',
                'danger_level' => 'low',
                'danger_label' => '🟢 Sangat Aman (Deploy Tetap Sukses)',
                'problem' => "Fitur kompresi riwayat latar belakang Git (Garbage Collection) dibatasi oleh server hosting karena limitasi jumlah thread/CPU.",
                'impact' => "Kode terbaru dari GitHub tetap berhasil diunduh dan dipasang 100% sempurna ke server.",
                'solution' => "Nonaktifkan fitur auto-gc dengan menjalankan <code>git config gc.auto 0</code> dan hapus file <code>.git/gc.log</code>.",
                'raw' => $message,
            ];
        }

        // 7. CSRF Token Mismatch (419 Session Expired)
        if (str_contains($message, 'CSRF token mismatch') || str_contains($class, 'TokenMismatchException')) {
            return [
                'type' => 'Keamanan - Sesi Formulir Kedaluwarsa',
                'badge' => 'SECURITY TOKEN (419)',
                'danger_level' => 'low',
                'danger_label' => '🟢 Aman (Proteksi Keamanan)',
                'problem' => "Sesi halaman browser Anda telah habis karena terlalu lama terbuka tanpa aktivitas sebelum formulir dikirim.",
                'impact' => "Data formulir tidak diproses untuk mencegah manipulasi request.",
                'solution' => "Cukup muat ulang (refresh) halaman browser, login kembali jika diminta, lalu kirim ulang formulir Anda.",
                'raw' => $message,
            ];
        }

        // 8. General Database Connection / Host Error
        if (str_contains($message, 'Connection refused') || str_contains($message, 'Access denied for user')) {
            return [
                'type' => 'Database - Koneksi Terputus / Kredensial Salah',
                'badge' => 'DB CONNECTION',
                'danger_level' => 'high',
                'danger_label' => '🔴 Perlu Penanganan Segera',
                'problem' => "Aplikasi tidak dapat terhubung ke server database MySQL (karena username/password salah atau server database sedang restart).",
                'impact' => "Aplikasi tidak dapat membaca atau menulis data apapun ke database.",
                'solution' => "Periksa file <code>.env</code> di server, pastikan <code>DB_HOST</code>, <code>DB_DATABASE</code>, <code>DB_USERNAME</code>, dan <code>DB_PASSWORD</code> sudah sesuai dengan konfigurasi hPanel hosting.",
                'raw' => $message,
            ];
        }

        // 9. Generic Fallback Error
        return [
            'type' => 'Sistem - Kesalahan Operasi Internal',
            'badge' => 'SYSTEM EXCEPTION',
            'danger_level' => 'medium',
            'danger_label' => '🟡 Perlu Pemeriksaan',
            'problem' => "Terjadi kendala pada eksekusi kode: <code>" . htmlspecialchars(mb_substr($message, 0, 180)) . "...</code>",
            'impact' => "Fungsi yang sedang dijalankan terhenti sebelum selesai.",
            'solution' => "Periksa catatan file log di <code>storage/logs/laravel.log</code> atau hubungi tim pengembang dengan menyertakan pesan error di atas.",
            'raw' => $message,
        ];
    }
}
