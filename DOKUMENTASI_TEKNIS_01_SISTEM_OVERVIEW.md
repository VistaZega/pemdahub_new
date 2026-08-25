# DOKUMENTASI TEKNIS 01 - SISTEM OVERVIEW

## PEMBDA HUB - Sistem Manajemen Sekolah Terintegrasi

**Versi:** 3.0.0  
**Tanggal:** 25 Agustus 2026  
**Status:** Production Ready

---

## 1. RINGKASAN EKSEKUTIF

PEMBDA Hub adalah sistem manajemen sekolah terintegrasi berbasis web yang mengelola 3 unit sekolah:

- SMPS Pembda 2 (SMP)
- SMA Pembda 1 (SMA)
- SMKS Pembda Nias (SMK)

### 1.1 Tujuan Sistem

- Mengelola data akademik siswa dan guru
- Mengelola jadwal pelajaran otomatis
- Mengelola keuangan sekolah (tagihan, pembayaran)
- Mengelola penerimaan siswa baru (PSB)
- Mengelola rapor dan penilaian
- Notifikasi otomatis via WhatsApp

### 1.2 Ruang Lingkup

- **Akademik**: Siswa, Guru, Jadwal, Nilai, Rapor
- **Keuangan**: Tagihan, Pembayaran, Diskon, Tunjangan
- **PSB**: Pendaftaran Online, Verifikasi, Tes, Penerimaan
- **Manajemen**: User, Role, Jabatan, Mata Pelajaran

---

## 2. ARSITEKTUR SISTEM

### 2.1 Technology Stack

#### Backend

- **Framework**: Laravel 12.49.0
- **PHP**: 8.2.12
- **Database**: MySQL (MariaDB)
- **Server**: Apache 2.4 (XAMPP)

#### Frontend

- **CSS Framework**: Tailwind CSS 3.x (via CDN)
- **JavaScript**: Vanilla JS + Alpine.js
- **Icons**: Font Awesome 6.x

#### Integrasi

- **WhatsApp API**: Fonnte (untuk notifikasi)
- **Export**: CSV/Excel native PHP

### 2.2 Design Pattern

- **MVC**: Model-View-Controller (Laravel default)
- **Repository Pattern**: Untuk data access layer
- **Service Pattern**: Untuk business logic
- **Policy Pattern**: Untuk authorization

---

## 3. STRUKTUR DATABASE

### 3.1 Database Utama

**Nama Database**: `pembda_hub`

### 3.2 Kategori Tabel (61 tabel)

#### A. Manajemen User & Auth (5 tabel)

- `users` - Data user sistem
- `password_reset_tokens` - Token reset password
- `sessions` - Session management
- `personal_access_tokens` - API tokens
- `cache`, `cache_locks` - Cache system

#### B. Master Data (15 tabel)

- `schools` - Data 3 unit sekolah
- `academic_years` - Tahun ajaran
- `subjects` - Mata pelajaran
- `majors` - Jurusan (IPA/IPS untuk SMA, Teknik untuk SMK)
- `program_keahlians` - Program keahlian SMK
- `konsentrasi_keahlians` - Konsentrasi keahlian SMK
- `classrooms` - Data kelas
- `employees` - Data pegawai/guru
- `positions` - Jabatan/posisi
- `teacher_subjects` - Link guru-mata pelajaran
- `teacher_competencies` - Kompetensi mengajar guru
- `timeslots` - Slot waktu pelajaran
- `days` - Hari dalam seminggu
- `rooms` - Ruang kelas
- `settings` - Pengaturan sistem

#### C. Data Siswa (3 tabel)

- `students` - Data siswa aktif
- `student_classroom` - Assignment siswa ke kelas
- `student_photos` - Foto siswa

#### D. Jadwal Pelajaran (5 tabel)

- `schedules` - Jadwal master
- `schedule_details` - Detail jadwal per slot
- `teaching_assignments` - Assignment guru mengajar
- `schedule_conflicts` - Log konflik jadwal
- `schedule_validations` - Validasi jadwal

#### E. PSB - Penerimaan Siswa Baru (8 tabel)

- `applicants` - Data pendaftar
- `applicant_documents` - Dokumen pendaftar
- `applicant_test_scores` - Nilai tes masuk
- `applicant_achievements` - Prestasi pendaftar
- `applicant_payments` - Pembayaran pendaftar
- `applicant_discounts` - Diskon untuk pendaftar
- `registration_waves` - Gelombang pendaftaran
- `interview_schedules` - Jadwal interview (SMA)

#### F. Keuangan (12 tabel)

- `bills` - Tagihan siswa
- `bill_items` - Item tagihan detail
- `payments` - Pembayaran
- `payment_items` - Detail item pembayaran
- `discounts` - Master diskon
- `student_discounts` - Diskon per siswa
- `position_allowances` - Tunjangan jabatan
- `employee_position_allowances` - Assignment tunjangan
- `payrolls` - Gaji pegawai
- `payroll_details` - Detail gaji
- `bank_accounts` - Rekening bank
- `transactions` - Transaksi keuangan

#### G. Penilaian & Rapor (7 tabel)

- `assessment_types` - Jenis penilaian
- `assessments` - Penilaian master
- `assessment_scores` - Nilai siswa
- `rapor_templates` - Template rapor
- `rapor_releases` - Rilis rapor
- `student_rapors` - Rapor siswa
- `rapor_comments` - Komentar wali kelas

#### H. Notifikasi (3 tabel)

- `notifications` - Notifikasi sistem
- `notification_logs` - Log notifikasi terkirim
- `whatsapp_logs` - Log WhatsApp khusus

#### I. Migrasi & Jobs (3 tabel)

- `migrations` - Laravel migrations
- `jobs` - Queue jobs
- `job_batches` - Batch jobs
- `failed_jobs` - Failed queue jobs

---

## 4. STRUKTUR FOLDER

### 4.1 Folder Utama

```
pembdahub/
├── app/                    # Kode aplikasi
├── bootstrap/              # Bootstrap Laravel
├── config/                 # Konfigurasi
├── database/               # Migrations, Seeders
├── public/                 # Public assets
├── resources/              # Views, CSS, JS
├── routes/                 # Route definitions
├── storage/                # Storage (logs, uploads)
├── tests/                  # Unit & Feature tests
└── vendor/                 # Dependencies
```

### 4.2 Folder App (Detail)

```
app/
├── Console/                # Artisan commands
├── Exceptions/             # Exception handlers
├── Http/
│   ├── Controllers/        # Controllers
│   │   ├── Admin/          # Admin controllers
│   │   ├── Auth/           # Auth controllers
│   │   └── ...
│   ├── Middleware/         # Middleware
│   └── Requests/           # Form requests
├── Models/                 # Eloquent models
├── Policies/               # Authorization policies
├── Providers/              # Service providers
└── Services/               # Business logic services
```

### 4.3 Folder Resources

```
resources/
├── views/
│   ├── admin/              # Admin views
│   │   ├── dashboard.blade.php
│   │   ├── psb/            # PSB views
│   │   ├── academic/       # Academic views
│   │   ├── financial/      # Financial views
│   │   └── ...
│   ├── public/             # Public views
│   │   └── registration.blade.php
│   └── layouts/            # Layout templates
├── css/                    # CSS files
└── js/                     # JavaScript files
```

---

## 5. MODUL UTAMA

### 5.1 Modul Akademik

- Manajemen Siswa
- Manajemen Guru
- Manajemen Jadwal
- Manajemen Kelas
- Manajemen Mata Pelajaran

### 5.2 Modul Keuangan

- Tagihan Bulanan
- Pembayaran
- Diskon & Beasiswa
- Tunjangan Pegawai
- Laporan Keuangan

### 5.3 Modul PSB

- Pendaftaran Online
- Verifikasi Dokumen
- Tes Masuk
- Interview (SMA)
- Pengumuman Kelulusan
- Daftar Ulang

### 5.4 Modul Penilaian

- Input Nilai
- Rapor Digital
- Ranking Siswa
- Komentar Wali Kelas

### 5.5 Modul Notifikasi

- Notifikasi WhatsApp
- Email Notifications
- SMS (planned)

---

## 6. USER ROLES & PERMISSIONS

### 6.1 Role Hierarchy

1. **Super Admin** - Full access semua modul
2. **Admin Sekolah** - Access per school
3. **Bendahara** - Modul keuangan
4. **Guru** - Modul akademik & nilai
5. **Wali Kelas** - Rapor & data kelas
6. **Orang Tua** - View rapor & tagihan (planned)

### 6.2 Permission Matrix

Lihat: `ROLE_CAPABILITIES_MATRIX.md`

---

## 7. INTEGRASI EKSTERNAL

### 7.1 WhatsApp API (Fonnte)

- **Purpose**: Notifikasi otomatis
- **Use Cases**:
    - Konfirmasi pendaftaran PSB
    - Reminder pembayaran
    - Pengumuman kelulusan
    - Update status dokumen
- **Config**: `.env.whatsapp.example`

### 7.2 Export Excel

- **Library**: Native PHP (fputcsv)
- **Format**: CSV UTF-8 with BOM
- **Use Cases**: Export data pendaftar, siswa, pembayaran

---

## 8. KEAMANAN

### 8.1 Authentication

- Laravel Sanctum for API
- Session-based for web
- Password hashing with bcrypt

### 8.2 Authorization

- Laravel Policies
- Middleware protection
- CSRF protection

### 8.3 Data Protection

- Input validation
- SQL injection prevention (Eloquent ORM)
- XSS protection (Blade escaping)

---

## 9. PERFORMANCE

### 9.1 Optimasi

- Query optimization (eager loading)
- Database indexing
- Cache system (file-based)

### 9.2 Scalability

- Queue system untuk notifikasi
- Lazy loading untuk large datasets
- Pagination pada list views

---

## 10. DEPLOYMENT

### 10.1 Environment

- **Development**: XAMPP (localhost)
- **Production**: TBD (Apache/Nginx + MySQL)

### 10.2 Requirements

- PHP >= 8.2
- MySQL >= 5.7
- Apache/Nginx
- Composer
- Node.js (untuk build assets)

---

## 11. MAINTENANCE

### 11.1 Backup

- Database backup (regular)
- File storage backup
- Configuration backup

### 11.2 Monitoring

- Error logs: `storage/logs/laravel.log`
- WhatsApp logs: `whatsapp_logs` table
- Notification logs: `notification_logs` table

---

## 12. DOKUMENTASI TERKAIT

- **02** - Database Schema Detail [DOKUMENTASI_TEKNIS_02_DATABASE_SCHEMA.md](DOKUMENTASI_TEKNIS_02_DATABASE_SCHEMA.md)
- **03** - Module PSB [DOKUMENTASI_TEKNIS_03_MODULE_PSB.md](DOKUMENTASI_TEKNIS_03_MODULE_PSB.md)
- **04** - API Reference [DOKUMENTASI_TEKNIS_04_API_REFERENCE.md](DOKUMENTASI_TEKNIS_04_API_REFERENCE.md)
- **05** - Deployment Guide [DOKUMENTASI_TEKNIS_05_DEPLOYMENT.md](DOKUMENTASI_TEKNIS_05_DEPLOYMENT.md)
- **06** - Panduan Pengguna (Manual Book) [DOKUMENTASI_TEKNIS_06_MANUAL_BOOK.md](DOKUMENTASI_TEKNIS_06_MANUAL_BOOK.md) | **[PDF Version](MANUAL_BOOK_PEMBDAHUB.pdf)** (Premium Book Design)

---

**Prepared by**: Development Team  
**Last Updated**: 8 Februari 2026  
**Next Review**: Quarterly
