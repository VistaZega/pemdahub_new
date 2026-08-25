# 🎓 PEMBDA HUB - Sistem Manajemen Sekolah

**Version 3.0.0** | **384+ Tests Passing** | **Production Ready**

Sistem manajemen terpadu untuk 3 sekolah di Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA):

- SMPS Pembda 2 (SMP)
- SMA Pembda 1 (SMA)
- SMKS Pembda Nias (SMK)

---

## 📋 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Technology Stack](#-technology-stack)
- [System Requirements](#-system-requirements)
- [Installation](#-installation)
- [Documentation](#-documentation)
- [Module Status](#-module-status)
- [Contributing](#-contributing)
- [License](#-license)

---

## ✨ Fitur Utama

### 📝 PSB (Penerimaan Siswa Baru)

- **Online Registration** - Form pendaftaran online dengan validasi lengkap
- **Multi-School Support** - Mendukung 3 sekolah dengan konfigurasi berbeda
- **Gelombang System** - Pendaftaran dibagi dalam gelombang dengan kuota
- **SMK Major Selection** - Pilihan Program Keahlian dan Konsentrasi Keahlian (khusus SMK)
- **Document Upload** - Upload foto dan dokumen pendukung
- **Admin Dashboard** - Manajemen pendaftar dengan filter dan export Excel
- **WhatsApp Notification** - Notifikasi otomatis via WhatsApp (Fonnte API)
- **Statistics Cards** - Real-time count per sekolah
- **Status Management** - Pending, Diterima, Ditolak, Cadangan

### 📚 Manajemen Akademik

- **Data Master** - Siswa, guru, kelas, mata pelajaran
- **Visual Schedule Grid** - Jadwal pelajaran format grid (hari × waktu × kelas)
- **Time Slots System** - Konfigurasi waktu pembelajaran fleksibel per sekolah
- **Conflict Detection** - Validasi otomatis bentrok jadwal
- **Teacher Competencies** - Sistem kompetensi guru per mata pelajaran
- **Homeroom Teachers** - Wali kelas per kelas
- **Academic Year Management** - Multi tahun ajaran

### 💰 Sistem Keuangan

- **Bill Management** - Tagihan (SPP, UPP, DPP, Seragam, Buku, dll)
- **Payment Processing** - Pembayaran dengan berbagai metode
- **Automatic Late Fees** - Denda keterlambatan otomatis
- **Payment History** - Riwayat pembayaran lengkap
- **Excel Export** - Export tagihan dan pembayaran
- **Dashboard Analytics** - Visualisasi data keuangan (Chart.js)
- **Position Allowances** - Tunjangan jabatan pegawai

### 📊 Penilaian

- **Grade Management** - Input nilai berbagai jenis penilaian
- **Assessment Types** - UH, UTS, UAS, Praktik, Projek
- **Weighted Grading** - Sistem pembobotan nilai
- **Report Cards** - Kartu hasil studi

### 🔔 Notifikasi

- **WhatsApp Integration** - Notifikasi via WhatsApp (Fonnte)
- **Email Notifications** - Notifikasi via email
- **In-App Notifications** - Notifikasi dalam aplikasi
- **Notification Logs** - Log pengiriman notifikasi

### 🔐 Keamanan

- **Role-Based Access** - Super Admin, Admin Sekolah, Bendahara, Guru, Wali Kelas, Orang Tua
- **Session Management** - Laravel session dengan Redis support
- **CSRF Protection** - Perlindungan CSRF token
- **Password Hashing** - Bcrypt password hashing
- **Activity Logging** - Audit trail untuk aktivitas penting

### 🎨 User Experience

- **Responsive Design** - Mobile, Tablet, Desktop support
- **Modern UI** - Tailwind CSS 3.x framework
- **Interactive** - JavaScript AJAX untuk dynamic loading
- **Fast** - Optimized queries dan caching

---

## 🛠 Technology Stack

| Component                | Technology   | Version          |
| ------------------------ | ------------ | ---------------- |
| **Backend Framework**    | Laravel      | 12.49.0          |
| **Programming Language** | PHP          | 8.2.12           |
| **Database**             | MySQL        | 8.0+             |
| **Frontend CSS**         | Tailwind CSS | 4.0 (Vite build) |
| **Build Tool**           | Vite         | 7.0              |
| **Icons**                | Font Awesome | 6.x              |
| **JavaScript**           | Vanilla JS   | ES6+             |
| **Web Server**           | Apache       | 2.4+             |
| **WhatsApp API**         | Fonnte       | Latest           |

---

## 💻 System Requirements

### Minimum Requirements

- **PHP:** 8.2.0 or higher
- **MySQL:** 8.0+ or MariaDB 10.6+
- **Web Server:** Apache 2.4+ or Nginx 1.18+
- **RAM:** 4 GB
- **Storage:** 20 GB SSD
- **Composer:** 2.6+

### Recommended Requirements

- **PHP:** 8.2.12
- **MySQL:** 8.0.35
- **Web Server:** Nginx 1.24+
- **RAM:** 8 GB
- **Storage:** 50 GB SSD
- **Redis:** 7.0+ (for caching)

---

## 🚀 Installation

### Quick Start (XAMPP - Windows)

```bash
# 1. Clone repository
cd C:\xampp\htdocs
git clone <repository-url> pembdahub
cd pembdahub

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
copy .env.example .env
php artisan key:generate

# 4. Configure database (edit .env)
# DB_DATABASE=pembda_hub
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Create database
mysql -u root -e "CREATE DATABASE pembda_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# 6. Run migrations
php artisan migrate

# 7. Build frontend assets
npm run build

# 8. Create storage link
php artisan storage:link

# 9. Access application
# http://localhost/pembdahub/public
```

### Queue Worker & Scheduled Tasks

```bash
# Start queue worker (required for WhatsApp bulk send, report generation)
php artisan queue:work --sleep=3 --tries=3 --max-time=3600

# Register scheduler in crontab (Linux) or Task Scheduler (Windows)
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled tasks include: queue pruning, auth token cleanup, cache maintenance, and system health checks (every 15 min).

### Running Tests

```bash
# Run full test suite (384 tests)
php artisan test

# Run specific test class
php artisan test --filter=TreasurerModuleTest
php artisan test --filter=WhatsAppServiceTest
```

For detailed installation guide, see [DOKUMENTASI_TEKNIS_05_DEPLOYMENT.md](DOKUMENTASI_TEKNIS_05_DEPLOYMENT.md)

---

## 📖 Documentation

### Technical Documentation (NEW!)

- **[DOKUMENTASI_TEKNIS_01_SISTEM_OVERVIEW.md](DOKUMENTASI_TEKNIS_01_SISTEM_OVERVIEW.md)** - System Architecture & Overview
- **[DOKUMENTASI_TEKNIS_02_DATABASE_SCHEMA.md](DOKUMENTASI_TEKNIS_02_DATABASE_SCHEMA.md)** - Complete Database Schema (62 tables)
- **[DOKUMENTASI_TEKNIS_03_MODULE_PSB.md](DOKUMENTASI_TEKNIS_03_MODULE_PSB.md)** - PSB Module Documentation
- **[DOKUMENTASI_TEKNIS_04_API_REFERENCE.md](DOKUMENTASI_TEKNIS_04_API_REFERENCE.md)** - API Endpoints Reference
- **[DOKUMENTASI_TEKNIS_05_DEPLOYMENT.md](DOKUMENTASI_TEKNIS_05_DEPLOYMENT.md)** - Deployment Guide
- **[DOKUMENTASI_TEKNIS_06_MANUAL_BOOK.md](DOKUMENTASI_TEKNIS_06_MANUAL_BOOK.md)** - Panduan Pengguna (Manual Book) | **[PDF Version](MANUAL_BOOK_PEMBDAHUB.pdf)** (Premium Book Design)

### Archived Documentation

Phase-specific documentation moved to `docs/archive/`:

- Development phases (PHASE\_\*.md)
- Completion reports (COMPLETION_REPORT\*.md)
- Business requirements (BUSINESS_REQUIREMENTS\*.md)
- And more...

---

## 📊 Module Status

| Module                | Status         | Completion | Notes                                          |
| --------------------- | -------------- | ---------- | ---------------------------------------------- |
| **PSB (Pendaftaran)** | ✅ Complete    | 100%       | Online registration with WhatsApp notification |
| **Master Data**       | ✅ Complete    | 100%       | Schools, students, teachers, subjects          |
| **Academic**          | ✅ Complete    | 100%       | Schedules, time slots, conflict detection      |
| **Financial**         | ✅ Complete    | 100%       | Bills, payments, late fees, analytics          |
| **Assessment**        | ✅ Complete    | 100%       | Grades, assessments, report cards              |
| **Authentication**    | ✅ Complete    | 100%       | Multi-role authentication system               |
| **Notifications**     | ✅ Complete    | 100%       | WhatsApp & email integration                   |
| **Dashboard**         | ✅ Complete    | 100%       | Analytics and statistics                       |
| **LMS**               | ✅ Complete    | 100%       | Course materials, assignments, quizzes         |
| **CBT**               | ✅ Complete    | 100%       | Computer-based testing, question banks         |
| **SDM & Penggajian**  | ✅ Complete    | 100%       | Workload, salary slips, payroll settings       |
| **Report Generation** | ✅ Complete    | 100%       | PDF rapor, bulk ZIP, guru & orangtua access    |
| **Mobile App**        | ⏳ Planned     | 0%         | Flutter mobile application                     |

---

## 📁 Project Structure

```
pembdahub/
├── app/
│   ├── Contracts/              # Service interfaces
│   ├── Events/                  # Model events
│   ├── Exports/                 # Excel exports
│   ├── Helpers/                 # Utility helpers
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/           # Admin controllers (36)
│   │   │   ├── Guru/            # Teacher controllers (9)
│   │   │   ├── Siswa/           # Student controllers (3)
│   │   │   ├── OrangTua/        # Parent controllers (1)
│   │   │   ├── Treasurer/       # Treasurer controllers (4)
│   │   │   └── Auth/            # Auth controllers
│   │   ├── Middleware/          # Custom middleware (7)
│   │   └── Requests/            # Form Request validation (28+)
│   ├── Jobs/                    # Queue jobs (WhatsApp, Reports)
│   ├── Listeners/               # Event listeners
│   ├── Models/                  # Eloquent models (77)
│   ├── Policies/                # Authorization policies
│   ├── Repositories/            # Repository pattern
│   ├── Services/                # Business logic services
│   ├── Traits/                  # Shared traits
│   └── View/                    # View composers
├── config/
│   └── whatsapp-templates.php   # WhatsApp message templates
├── database/
│   ├── factories/               # Model factories (8+)
│   ├── migrations/
│   └── seeders/
├── docs/
│   └── archive/                 # Phase reports & archives
├── lang/
│   └── id/                      # Indonesian translations
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│       ├── admin/               # Admin panel views (128)
│       ├── guru/                # Teacher views (39)
│       ├── siswa/               # Student views (21)
│       ├── orangtua/            # Parent views (7)
│       ├── treasurer/           # Treasurer views (10)
│       ├── public/              # Public-facing pages
│       └── layouts/             # Layout templates
├── routes/
│   ├── web.php                  # Web routes
│   ├── admin.php                # Admin routes
│   ├── guru.php                 # Teacher routes
│   ├── siswa.php                # Student routes
│   ├── orangtua.php             # Parent routes
│   ├── treasurer.php            # Treasurer routes
│   └── console.php              # Scheduled tasks (7 tasks)
├── tests/
│   ├── Feature/                 # Feature tests (384+ total)
│   └── Unit/                    # Unit tests
├── DOKUMENTASI_TEKNIS_*.md      # Technical documentation (5 files)
├── CHANGELOG.md
└── README.md
```

---

## 🔧 Configuration

### Environment Variables

Key `.env` configurations:

```env
# Application
APP_NAME="Pembda Hub"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_LOCALE=id

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pembda_hub
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Queue & Cache
QUEUE_CONNECTION=database
CACHE_STORE=database

# Logging
LOG_CHANNEL=stack
LOG_STACK=daily

# WhatsApp (Fonnte)
WHATSAPP_ENABLED=true
WHATSAPP_PROVIDER=fonnte
WHATSAPP_API_TOKEN=your_fonnte_token_here
WHATSAPP_SENDER=your_whatsapp_number

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
```

---

## 🎓 Default Users

After running seeders:

| Role        | Email                    | Password | School           |
| ----------- | ------------------------ | -------- | ---------------- |
| Super Admin | superadmin@pembdahub.com | password | All schools      |
| Admin SMP   | admin.smp@pembdahub.com  | password | SMPS Pembda 2    |
| Admin SMA   | admin.sma@pembdahub.com  | password | SMA Pembda 1     |
| Admin SMK   | admin.smk@pembdahub.com  | password | SMKS Pembda Nias |
| Bendahara   | bendahara@pembdahub.com  | password | Based on school  |
| Guru        | guru@pembdahub.com       | password | Based on school  |
| Siswa       | siswa@pembdahub.com      | password | Based on school  |
| Orang Tua   | ortu@pembdahub.com       | password | Based on school  |

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📝 License

This project is proprietary software owned by **Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)**.

---

## 📞 Support

For technical support or questions:

- **Email:** support@pembdahub.com
- **WhatsApp:** +62 xxx xxxx xxxx
- **Documentation:** See `DOKUMENTASI_TEKNIS_*.md` files

---

## 🙏 Acknowledgments

- Laravel Framework
- Tailwind CSS
- Font Awesome
- Fonnte WhatsApp API
- All contributors and testers

---

**Built with ❤️ by Tim Development Pembda Hub**  
**Last Updated:** February 21, 2026


