# Aturan Pengembangan PembdaHUB

## Alur Kerja Git & Deployment
Setiap kali selesai melakukan pengerjaan fitur, perbaikan bug, atau perubahan kode di lokal:
1. Pastikan semua perubahan sudah stabil dan diverifikasi.
2. Lakukan stage (`git add .`), commit, dan **push perubahan ke GitHub** (branch `main`).
3. Beritahukan pengguna bahwa perubahan sudah berada di GitHub dan siap untuk dideploy oleh pengguna ke hosting.

## Infrastruktur Server Production (Hostinger)

### Struktur Path di Server
- **Base Path (Laravel root):** `/home/u474310197/domains/perguruanpembda.com/public_html/pembdahub`
- **Public Path (web root):** `/home/u474310197/domains/perguruanpembda.com/public_html`
- **APP_URL:** `https://perguruanpembda.com`
- **APP_ENV:** `production`

### Pemetaan URL → Folder
- URL `perguruanpembda.com/...` → diarahkan oleh `public_html/.htaccess` ke folder `pembdahub/public/`
- **Semua route Laravel diakses TANPA prefix `/pembdahub/`**, contoh: `perguruanpembda.com/admin/dashboard`
- File standalone PHP di `public/` bisa diakses langsung, contoh: `perguruanpembda.com/clear-cache.php`
- ⚠️ URL `perguruanpembda.com/pembdahub/admin/...` adalah **SALAH** dan akan menghasilkan 404!
- Penjelasan teknis: Apache merewrite `perguruanpembda.com/admin/...` → `pembdahub/public/admin/...` → `index.php` → Laravel menerima REQUEST_URI `/admin/...` → route cocok

### Keterbatasan Server
- **TIDAK ADA akses SSH** — semua operasi server harus dilakukan via:
  - File Manager di hPanel Hostinger
  - File PHP standalone di folder `public/` yang bisa diakses via browser
  - Git deploy via hPanel
- **Tidak bisa menjalankan `php artisan` langsung** — harus via script PHP standalone atau route khusus

## Checklist Wajib Saat Menambah Fitur Baru

### Jika fitur memerlukan perubahan DATABASE (migrasi/seeder):
1. Buat migration dan seeder seperti biasa
2. Pastikan ada **route `/run-migrations`** di `routes/web.php` yang bisa menjalankan `artisan migrate --force` dan seeder terkait
3. Setelah push & deploy, **INGATKAN pengguna** untuk menjalankan migrasi via browser:
   `https://perguruanpembda.com/run-migrations?secret=pembda99`
4. Jika route migration juga 404 (karena cache), gunakan file standalone `public/clear-cache.php`

### Jika terjadi error 404 setelah deploy:
1. **Cek apakah route cache aktif** — jika ya, hapus file `bootstrap/cache/routes-v7.php` via File Manager atau `clear-cache.php`
2. **Cek apakah tabel database ada** — 404 bisa terjadi karena tabel belum dimigrasi
3. Gunakan script standalone `public/clear-cache.php?secret=pembda99` untuk diagnostik lengkap (cache, routes, tabel, path)
4. File standalone PHP di `public/` TIDAK terpengaruh route cache karena langsung dihandle web server

### Tool Darurat yang Tersedia di `public/`:
- `clear-cache.php` — Hapus cache + diagnostik + jalankan migrasi (akses: `perguruanpembda.com/clear-cache.php?secret=pembda99`)
- `run_cache_clear.php` — Clear cache saja (akses: `perguruanpembda.com/run_cache_clear.php?token=pembda2026clear`)
- `check_deploy.php` — Cek status deployment (akses: `perguruanpembda.com/check_deploy.php?token=pembda2026check`)

## ⛔ Larangan Keamanan Data (KRITIS)

### DILARANG Menghapus Tahun Pelajaran (Academic Years)
- **JANGAN PERNAH** menghapus, drop, atau truncate data di tabel `academic_years` (Tahun Pelajaran/TP)
- **JANGAN PERNAH** membuat kode/script/migration yang menghapus record TP, termasuk TP yang terlihat "duplikat" atau "kosong"
- Penghapusan TP akan **menghancurkan seluruh data relasi** (kelas, jadwal, teaching assignments, nilai, absensi, dll) karena cascade foreign key
- Jika ada masalah dengan data TP (duplikat, salah, dll), **TANYAKAN dulu ke pengguna** sebelum melakukan apapun
- **Latar belakang:** Pada Juli 2026, penghapusan TP 2026/2027 menyebabkan kehilangan data masif dan harus restore dari backup

### Prinsip Umum Keamanan Data
- Selalu gunakan `firstOrCreate` / `updateOrCreate` daripada delete-then-insert
- Jangan buat migration yang men-drop tabel yang sudah berisi data production
- Jika perlu menghapus data, selalu konfirmasi ke pengguna terlebih dahulu
- Untuk operasi berbahaya, tambahkan flag `?dry_run=1` agar bisa preview dulu tanpa eksekusi

### 🏫 Aturan Unit Sekolah & Entitas Yayasan
- **3 Unit Sekolah Aktif**: Di bawah Yayasan hanya terdapat 3 unit sekolah aktif (karena unit sore SMAS Pembda 2 & SMPS Pembda 1 telah digabungkan ke unit pagi SMAS Pembda 1 & SMPS Pembda 2).
- **Pemisahan Fitur Akademik vs Administrative Oversight**:
  - **Fitur Akademik & Operasional Sekolah** (Penugasan Mengajar, Jadwal Pelajaran, Data Siswa, Data Guru, LMS, Rapor, CBT, PSB): **JANGAN PERNAH** menyertakan Yayasan sebagai opsi pilihan sekolah/unit. Selalu filter menggunakan `School::schoolsOnly()` (`type != 'yayasan'` & `is_active = true`).
  - **Fitur Kepegawaian, Keuangan, & Monitoring**: Yayasan dapat dianggap sebagai entitas induk (*parent oversight body*) dengan kewenangan tertinggi, namun rekapitulasi 3 unit sekolah aktif harus tetap dipisahkan secara rapi dari struktur Yayasan.

## 📌 Aturan Etalase Beranda (Showcase Grid & Counters)
Bagian "✱ ETALASE PROJECT SMK, PENELITIAN SMA, PKL & MODUL LMS" di halaman beranda memiliki algoritma data dan penyajian yang baku:

### 1. Counter / Badge Angka pada Tab Filter (Total Real Database)
Badge counter pada tab filter (`showcase.blade.php`) wajib menampilkan **JUMLAH TOTAL SELURUH DATA REAL** yang ada di database:
- **`SEMUA`**: Total seluruh baris data di database (`$totalAllShowcase = $totalFinalProjects + $totalPklAll + $totalCourses + $totalAchievements`)
- **`PROJECT & PENELITIAN`**: Total seluruh tugas akhir/penelitian di database (`$totalFinalProjects`)
- **`LOGBOOK PKL`**: Total seluruh data PKL di database (`$totalPklAll = $totalApprovedLogs + $totalMonitorings`)
- **`MODUL LMS`**: Total seluruh Course & Modul KBM sekolah aktif di database (`$totalCourses` dari `lms_courses`)
- **`PRESTASI JUARA`**: Total seluruh rekam penghargaan siswa di database (`$totalAchievements`)

### 2. Kartu Grid Poster (Diambil Acak dengan Kuota Terbatas - Total 20 Kartu)
Kartu yang dirender di grid di bawah tab filter diambil **secara acak (`inRandomOrder()`)** dari dataset riil di atas dengan batasan:
- **4 Penelitian Kelas XII SMA** (Acak, dari `final_projects` unit SMA / `penelitian_ilmiah`)
- **4 Project Kelas XII SMK** (Acak, dari `final_projects` unit SMK / `project_akhir`)
- **2 Logbook Dudi** (Acak, dari `pkl_logs` status approved dengan foto dokumentasi)
- **2 Monitoring Guru** (Acak, dari `pkl_monitorings` guru ke mitra industri DUDI)
- **4 Modul LMS** (Acak SMP/SMA/SMK, dari `lms_courses` KBM aktif buatan guru)
- **4 Juara Prestasi** (Acak, dari `student_counseling_records` bertipe penghargaan)

### 3. Standar Tampilan Setiap Kartu
- **Photo Profile (Avatar):** Menampilkan foto profil asli siswa/guru, fallback ke UI-Avatars bulat.
- **Nama Pengguna:** Menampilkan **Hanya Kata Pertama Saja** (`explode(' ', trim($name))[0]`), dengan nama lengkap tetap tersedia pada tooltip (`title="..."`).

## Bahasa & Komunikasi
- Komunikasi dengan pengguna menggunakan **Bahasa Indonesia**
- Komentar kode boleh dalam Bahasa Indonesia atau Inggris
