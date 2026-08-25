# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [3.0.0] - 2026-08-25

### 🛡️ Enterprise Stability & Security Hardening
- **AcademicYear 3-Layer Protection**: SoftDeletes trait + AcademicYearObserver interceptor + Controller rejection to eliminate risk of cascade deletion disasters.
- **Teacher-Employee Unified Architecture**: Delegated accessors in Teacher model reading directly from Employee as Single Source of Truth, paired with auto-sync observer on Employee updates to eliminate data drift.
- **Asynchronous Background Processing**: Scheduled database queue worker configured for instant non-blocking user experience on PDF bulk generation & WhatsApp notifications.
- **Pristine Project Structure**: Cleaned up and organized 160+ ad-hoc debug/test/backup files from root into isolated `tools/` and `archive/` directories.

### 🌟 Features & Ecosystem Integration (v3.0.0 Milestone)
- **AI Integration (Google Gemini)**: AI RPP/Lesson Plan Generator & AI CBT Question Generator.
- **Virtual Video Classrooms**: Direct embedded Jitsi Meet integration for live distance learning.
- **P5 Assessment Module**: Projek Penguatan Profil Pelajar Pancasila (Kurikulum Merdeka) digital assessment & report cards.
- **PKL Industrial Mentorship Portal**: Secure Signed URL access for DUDI mentors without registration.
- **Pembda Colabs & Reputation Gamification**: Points reward & penalty system, Leaderboards, Charity fundraising, and Menara Prestasi (Pembda Tower).
- **SimLab Virtual Microcontroller**: Interactive IoT simulator supporting Arduino Uno, Nano, and ESP32 with 23+ sensors/actuators.

---

## [2.5.0] - 2026-02-21

### 🎨 UI Enhancement: Classroom Management

#### Added

- **Scientist Avatar System** — Unique CSS-based avatars for SMPS Pembda 2 classes named after scientists
  - 12 scientists mapped: Alessandro Volta, Isaac Newton, Nikola Tesla, Marie Curie, Albert Einstein, Thomas Alva Edison, Pythagoras, Archimedes, Blaise Pascal, Gregor Mendel, Alexsander Graham Bell, Aristoteles
  - Each scientist has unique gradient color scheme + domain-relevant SVG icon
  - Tooltip shows full scientist name on hover
  - Scientist name displayed as italic subtitle below class name
  - Non-scientist classes retain original square letter icon
- **SVG Circular Progress Ring** — Replaced linear capacity bar with animated circular progress ring
  - 64×64px SVG ring with animated stroke arc
  - Color-coded: Green (<75%), Yellow (75-89%), Orange (90-99%), Red (≥100%)
  - Status labels: Tersedia, Cukup, Hampir Penuh, Penuh
  - Displays percentage in center + student count below

#### Changed

- `resources/views/admin/classrooms/index.blade.php` — Complete avatar and capacity section redesign

### 📚 Data Enhancement: SMPS Pembda 2 Subjects

#### Added

- **15 subjects** replaced/added for SMPS Pembda 2 (school_id=1) from Excel survey data
  - Core subjects: Matematika, Bahasa Indonesia, Bahasa Inggris, IPA, IPS, PKN, PAK, Pendidikan Agama Islam
  - Skills subjects: PJOK, Seni Budaya, Prakarya, TIK, BK, Mulok
  - Special: Agama Kristen
- All subjects include 80-word Indonesian descriptions with contextual detail

### 🏫 School List Page Enhancement

#### Changed

- Updated `resources/views/admin/schools/index.blade.php` — Improved school list columns and layout

---

## [2.4.0] - 2026-02-15

### 🎓 Phase 12: LMS Enhancement (Complete)

#### Added

- **LMS Course Management** — Full CRUD for courses, modules, and materials with file upload
- **Assignment System** — Create/edit assignments with deadlines, student submission tracking, teacher grading
- **Online Quiz** — Multiple-choice quiz builder with auto-grading, attempt tracking, score analytics
- **Discussion Forums** — Course discussions with threaded replies for student-teacher interaction
- **Siswa LMS Portal** — Student-facing LMS views (courses, materials, assignments, quizzes, discussions)
- **Grade Integration** — CBT exam scores sync to grading system
- **57 total LMS routes** registered (38 guru + 11 siswa + 8 admin)

#### Changed

- Guru sidebar: added LMS menu group (Courses, Assignments, Quizzes, Discussions)
- Siswa sidebar: added LMS menu with course browsing and submission views

### 📊 Phase 13: Report Generation & Attendance

#### Added

- **Guru Absensi Module** — `Guru\AttendanceController` with bulk attendance input per classroom
  - Quick-set buttons (Hadir/Sakit/Izin/Alpha), real-time summary counter
  - Ownership verification (teacher can only access their classes)
- **OrangTua Raport Download** — Parents can download published report cards as PDF
  - Ownership verification (parent can only download their children's rapor)
- **Wali Kelas Raport Management** — `Guru\ReportCardController` with 9 routes
  - Generate, view, edit notes, finalize & publish workflow, individual PDF print
  - Homeroom teacher authorization on all actions
  - Conditional "Raport" menu (only for wali kelas)
- **Bulk Raport Download (ZIP)** — Admin and Guru can bulk download class report cards as ZIP
- **Sidebar Enhancement** — Absensi submenu (Rekap + Input), conditional Raport menu

#### Fixed

- `siswa.lms.discussions` → `siswa.lms.discussions.index` route name fix
- `alpa` → `alpha` in AttendanceController validation (matching model constant)
- `description/descriptions` → `notes` in AttendanceController (matching model fillable)
- `alpa` → `alpha` in admin bulk attendance view dropdown and ReportCardController

### 👥 SDM & Penggajian Module (Complete)

#### Added

- **PayrollController** — Salary slip search, payroll settings management
- **WorkloadSummaryController** — Workload index, salary reports with CSV export
- **EmployeeAssignmentService** — Dynamic salary calculation formulas
- **Settings Page** — Payroll settings with validation and confirmation dialog
- Role-based sidebar access (superadmin, admin_sekolah)
- 4 workload views with controller-view consistency

---

## [2.3.0] - 2026-02-12

### 🔒 Phase A: Security & Stability

#### Added

- Rate limiting middleware on authentication routes (5 attempts/minute)
- Security headers middleware (X-Content-Type-Options, X-Frame-Options, X-XSS-Protection, Referrer-Policy, Strict-Transport-Security)
- CSRF token verification hardened across all forms
- Input sanitization on all user-facing controllers
- Password policy enforcement: min 8 chars, mixed case, numbers (via `Password::defaults()`)

#### Fixed

- Cookie security flags (httpOnly, secure, sameSite)
- Session configuration hardened (encrypt, httpOnly)

### ⚡ Phase B: Performance & Caching

#### Added

- 18 database indexes on frequently queried columns (students, payments, student_bills, applicants, etc.)
- Query caching on dashboard statistics and academic year lookups
- Eager loading optimization to eliminate N+1 queries across 12 controllers

#### Changed

- Optimized dashboard queries with selective column loading
- Replaced raw queries with Eloquent relationships where applicable

### 🧹 Phase C: Code Quality & Maintainability

#### Added

- 13 Form Request classes for input validation (StoreStudentRequest, StorePaymentRequest, StoreTeacherRequest, etc.)
- Database transactions on all multi-step write operations (payments, bulk bill creation, grade updates)
- Consistent error handling pattern: log internally, show generic message to users

#### Removed

- Dead code and unused methods across controllers
- Redundant validation logic (moved to Form Requests)

#### Fixed

- Model `$fillable` arrays updated to match actual database columns
- Consistent use of `back()->with()` flash message pattern

### 🧪 Phase D: Testing & Monitoring

#### Added

- Queue infrastructure: `failed_jobs` and `job_batches` database tables
- 4 dedicated logging channels: `whatsapp`, `payments`, `security`, `performance` (daily rotation)
- 8 model factories (PaymentType, StudentBill, Payment, ParentModel, Attendance, StudentClass, TeachingAssignment, Position)
- `bendahara()` state on UserFactory
- 6 scheduled tasks in `routes/console.php` (queue pruning, restarts, auth cleanup, cache tags, health check every 15min)
- `WhatsAppServiceInterface` contract for dependency injection and testing
- 3 custom exception classes: `WhatsAppException`, `TemplateNotFoundException`, `PaymentException`
- `LogsActivity` trait expanded to Payment, StudentBill, User models
- TreasurerModuleTest (13 tests) — dashboard, CRUD, authorization, cross-school blocking, bulk payments
- WhatsAppServiceTest (15 tests) — service lifecycle, API calls, error handling, job dispatching
- **Total: 384 tests passing (990 assertions)**

#### Changed

- WhatsApp service fully rewritten: HTTP timeout, non-blocking bulk send via queued jobs
- PerformanceMonitor middleware: production-ready (slow request logging >1s, high query count detection >50)
- Logging default changed from `single` to `daily` with 30-day retention
- DashboardController `DATE_FORMAT` query made database-agnostic (MySQL + SQLite compatible)

### 🎨 Phase E: UX & Frontend Improvements

#### Added

- Indonesian locale files (`lang/id/`): auth, pagination, passwords, validation — all messages in Bahasa Indonesia
- Shared flash message partial (`partials/flash-messages.blade.php`) with support for `success`, `error`, `warning`, `info`
- Shared UX scripts partial (`partials/ux-scripts.blade.php`): auto-dismiss flash (6s), form submit loading state, `showFlashMessage()` JS helper
- Dismissible flash messages with close button and `role="alert"` for accessibility
- Form submit buttons show spinner "Memproses..." and disable during submission

#### Changed

- `APP_LOCALE` default set to `id` (Indonesian), `APP_FAKER_LOCALE` to `id_ID`
- All 5 layouts (admin, guru, siswa, orangtua, treasurer) now use unified flash message partial
- AJAX error handlers use `showFlashMessage()` instead of `alert()` or `console.error()`

#### Removed

- 9 `console.log` statements from PSB registration form

### 📦 Phase F: Documentation & Deployment Readiness

#### Changed

- `.env.example` updated: APP_NAME, APP_LOCALE=id, DB_CONNECTION=mysql, LOG_STACK=daily
- `.gitignore` updated: added patterns for `*.sql`, `*.docx`, temp files, credential files
- CHANGELOG.md updated with Phase A-F entries
- README.md updated with complete installation guide, testing instructions, queue/scheduler setup
- PROJECT_STATUS.md updated: technical debt cleared, test counts updated, Phase A-F entries
- Deployment documentation updated: queue workers, scheduled tasks, security headers, npm build

#### Removed

- Temporary files from project root (temp_response.html, test_results\*.txt)
- Credential files from project root (security risk)
- Phase report markdown files moved to `docs/archive/`
- SQL dump moved to `database/`
- Laravel boilerplate text from README

---

## [2.2.0] - 2026-02-08

### 🎉 NEW: Comprehensive Documentation & Production Readiness

#### Added - Documentation

- **DOKUMENTASI_TEKNIS_01_SISTEM_OVERVIEW.md** (500+ lines)
    - Complete system architecture
    - Technology stack details
    - Database structure (62 tables)
    - Module descriptions (Academic, Financial, PSB, Assessment, Notifications)
    - User roles and permissions
    - Security and performance guidelines
    - Deployment and maintenance procedures

- **DOKUMENTASI_TEKNIS_02_DATABASE_SCHEMA.md** (1000+ lines)
    - Detailed schema for all 62 tables
    - Column definitions with data types
    - Relationships and foreign keys
    - Business rules for each table
    - Indexes and performance optimization
    - Sample data structures
    - Complete ERD documentation

- **DOKUMENTASI_TEKNIS_03_MODULE_PSB.md** (800+ lines)
    - PSB module architecture
    - Database tables (applicants, gelombangs, program_keahlians, konsentrasi_keahlians)
    - Models with relationships
    - Controllers (PublicRegistrationController, ApplicantController)
    - Routes and API endpoints
    - Views (registration form, admin index, detail)
    - Business logic (registration number generation, gelombang system, SMK major selection)
    - Validation rules
    - WhatsApp notification integration
    - User flow diagrams
    - Testing checklist

- **DOKUMENTASI_TEKNIS_04_API_REFERENCE.md** (300+ lines)
    - Public APIs (program keahlian, konsentrasi keahlian)
    - Admin APIs (applicants CRUD, export)
    - Request/response examples
    - Authentication methods
    - Error handling
    - Status codes
    - Rate limiting guidelines

- **DOKUMENTASI_TEKNIS_05_DEPLOYMENT.md** (1000+ lines)
    - System requirements
    - Development setup (Windows XAMPP, Linux Ubuntu)
    - Production deployment checklist
    - Database migration guide
    - Environment configuration
    - Security best practices
    - Performance optimization (caching, Redis, Opcache)
    - Backup & recovery procedures
    - Monitoring setup
    - Troubleshooting guide

#### Modified

- **README.md** - Complete overhaul
    - Modern structure with table of contents
    - Updated feature descriptions
    - Technology stack table
    - System requirements
    - Quick start installation guide
    - Module status table
    - Project structure tree
    - Configuration examples
    - Default users table
    - Contributing guidelines

- **FILE_CLEANUP_REPORT.md**
    - Analysis of 200+ files
    - Categorized safe-to-delete files
    - Cleanup recommendations
    - Disk space savings estimation

#### Removed - Cleanup

- **Testing Files** (40+ files)
    - check-\*.php (all testing scripts)
    - test-_.php, test-_.html, test-\*.sh
    - analyze-_.php, verify-_.php, debug-\*.php
- **One-time Setup Files** (16+ files)
    - add-_.php, assign-_.php, auto-\*.php
    - create-\*.php (except Laravel migrations)
    - seed-_.php, setup-_.php
- **Migration Utilities** (20+ files)
    - fix-_.php, update-_.php, clean-\*.php
    - delete-_.php, import-_.php, link-\*.php
- **Development Utilities** (15+ files)
    - show-_.php, simulate-_.php, generate-\*.php
    - _.py (Python scripts), report-_.php
    - quick-_.php, comprehensive-_.php

- **Data Files** (11+ files)
    - _.csv (Data Siswa SMK_.csv, guru_smk.csv)
    - \*.sql backups (Data SMP.sql)
    - \*.txt logs and data files

- **Documentation Archive** (30+ files moved to `docs/archive/`)
    - PHASE\_\*.md (all phase reports)
    - COMPLETION_REPORT\*.md
    - DELETE\_\*.md
    - AUDIT_REPORT.md
    - BENDAHARA_IMPLEMENTATION.md
    - BUSINESS_REQUIREMENTS\*.md
    - COMPETENCY_VALIDATION_SYSTEM.md
    - DEV-E2E.md
    - DEVELOPMENT_STATUS.md

#### Changed

- Codebase cleaned up for production readiness
- Total files reduced from 200+ to ~50 core files
- Estimated disk space saved: 10-15 MB
- Documentation consolidated and improved

#### Technical Improvements

- Better organized project structure
- Comprehensive technical documentation
- Production-ready configuration examples
- Clear deployment procedures
- Improved maintainability

---

## [2.1.0] - 2026-02-08

### 🎉 NEW: WhatsApp Notification System for PSB

#### Added

- **WhatsApp Integration Service** (`app/Services/WhatsAppService.php`)
    - Support multiple providers: Fonnte, Wablas, Twilio
    - Auto phone number normalization
    - Bulk sending with rate limiting
    - Account info & connection testing
- **Notification Service** (`app/Services/NotificationService.php`)
    - Auto-send on PSB registration
    - Payment confirmation notifications
    - Test schedule notifications
    - Acceptance notifications
    - Custom reminder system
    - Bulk notification support

- **Admin PSB Notification Controller**
    - Dashboard with statistics
    - Individual & bulk notification sending
    - Preview & test connection features
    - Resend capability

- **WhatsApp Message Templates** (8 templates in Bahasa Indonesia)
    - PSB Registration, Payment, Documents, Test Schedule, Acceptance, Reminders

- **Admin UI** - Full-featured notification dashboard
- **Documentation** - 5 comprehensive guides (3000+ words total)
- **Code Examples** - 15 usage examples for developers
- **Routes** - 8 new admin routes for notifications

#### Modified

- **PublicRegistrationController** - Auto-send WhatsApp on registration
- **routes/web.php** - Added PSB notification routes
- **config/services.php** - Added WhatsApp configuration
- **.env.example** - Added WhatsApp variables

#### Features

- ✅ Automatic sending on student registration
- ✅ Manual & bulk sending from admin
- ✅ Professional message templates
- ✅ Multi-provider support
- ✅ Error handling & comprehensive logging

---

## [2026-02-06] - Phase 9.2: SMP Data Correction & UI Enhancement ✅

### Fixed

**SMP Teacher Competency Data Correction:**

- ✅ Fixed incorrect teacher-subject mapping from round-robin to actual legacy data
- ✅ Extracted correct mapping from Data SMP.sql penugasan table
- ✅ Script: `fix-smp-teacher-competencies.php` with exact subject matching logic
- ✅ Verified 11 core teachers match original system (YONATA: MTK+IPA+PJOK, DEDI PUTRA: B.Inggris+Seni, etc.)
- ✅ Removed `is_primary` column references that caused SQL errors
- ✅ Total: 23 competencies correctly assigned based on legacy system

**Database Schema Fixes:**

- ❌ Removed all `is_primary` column references from `subject_teacher` table
- ✅ Updated TeacherCompetencyController: removed primary_subject_id validation
- ✅ Updated Teacher model: removed getPrimarySubject() method
- ✅ Updated 5 view files: removed ⭐ Utama badges and dropdowns
- ✅ Fixed SQLSTATE[42S22] Column not found error

**Teacher Management UI Enhancement:**

- ✨ Enlarged teacher photos: 12x12 → 20x20 with hover scale effect
- ✨ Merged teacher code with name column (badge format below name)
- ✨ Changed competency display from bubbles to formal card format with left border
- ✨ Enhanced hover effects: row gradients, button animations (scale-110, rotate-3)
- ✨ Added animated pulse for active status indicator
- ✨ Improved empty state with gradient background and CTA button
- ✅ File: `resources/views/admin/teachers/index.blade.php`

### Technical Details

**Competency Mapping Fix:**

```php
// OLD (WRONG): Round-robin arbitrary assignment
$originalMapping = [
    11 => [5, 16, 2], // CLARA: IPS, Prakarya, Bahasa Indonesia ❌
];

// NEW (CORRECT): From Data SMP.sql penugasan table
$originalMapping = [
    11 => [5], // CLARA: IPS only ✅
];
```

**Subject Matching Algorithm:**

- Switch-case mapping by old mata_pelajaran_id (1-16)
- Exact code matching: MTK, IPA, IPS, PKN, TIK, BK, etc.
- String matching for subject names with special cases
- Fixed Matematika vs TIK collision (MTK code contains 'TK')

**Removed is_primary References:**

- Controllers: `TeacherCompetencyController@update/store/getTeachersBySubject`
- Models: `Teacher->getPrimarySubject()`
- Views: `competencies.blade.php`, `show.blade.php`, `grid.blade.php`, `create.blade.php`
- Total: 20+ references removed across 6 files

### Scripts Created

1. **fix-smp-teacher-competencies.php** - Main correction script with:
    - Teacher name fuzzy matching (16 legacy → 17 current)
    - Exact subject ID mapping (switch-case by old ID)
    - DELETE old wrong data + INSERT correct data
    - Safety: Only affects school_id=1 (SMP)

2. **show-smp-teacher-competencies.php** - Verification display script
3. **list-smp-teachers.php** - List all SMP teachers
4. **list-smp-subjects.php** - List all SMP subjects
5. **check-tik.php**, **check-dedi.php**, **debug-mtk.php** - Debugging utilities

### Verified Results

**Guru → Mata Pelajaran (Correct):**

| Guru                   | Seharusnya              | Hasil                      | Status |
| ---------------------- | ----------------------- | -------------------------- | ------ |
| YONATA TELAUMBANUA     | MTK, IPA, PJOK          | ✅ MTK, IPA, PJOK          | BENAR  |
| DEDI PUTRA TELAUMBANUA | B.Inggris, Seni         | ✅ B.Inggris, Seni         | BENAR  |
| MARSELINA NDRURU       | Agama Katolik           | ✅ Agama Katolik           | BENAR  |
| KRISTIANI ZEBUA        | MTK, BK                 | ✅ MTK, BK                 | BENAR  |
| BEATUS NDRURU          | IPS, Mulok              | ✅ IPS, Mulok              | BENAR  |
| ELIAMAN ZAI            | PKN, Prakarya           | ✅ PKN, Prakarya           | BENAR  |
| NURIATI ZEGA           | PKN                     | ✅ PKN                     | BENAR  |
| DEWI JULI ZEGA         | TIK                     | ✅ TIK                     | BENAR  |
| YARNIWATI SARUMAHA     | Agama Kristen, Prakarya | ✅ Agama Kristen, Prakarya | BENAR  |
| SOLIDARMAN MENDROFA    | PJOK, Mulok             | ✅ PJOK, Mulok             | BENAR  |
| CLARA SABRINA          | IPS                     | ✅ IPS                     | BENAR  |

**Statistics:**

- 15/17 teachers with competencies (2 without: Herni Yanti [new teacher], SRI RAHAYU [Bahasa Daerah not in system])
- 23 total competencies assigned
- 1.4 average competencies per teacher
- All 14 SMP subjects covered except Bahasa Daerah

## [2026-02-03] - Phase 9.1: Schedule Grid Management System ✅

### Added

**Schedule Grid System:**

- ✨ Database table: `time_slots` for flexible time configuration per school
- ✨ Excel-like grid view with Days (rows) × Time Slots × Classrooms (columns)
- ✨ Visual grid layout matching traditional school wall schedule
- ✨ Modal CRUD interface for add/edit/delete schedules without page reload
- ✨ Real-time conflict detection (teacher and classroom availability)
- ✨ Time slot system with 4 types: lesson, break, ceremony, other
- ✨ SMK time slots configuration (15 slots: Apel, 5S, Les 1-11, Istirahat 1-2)
- ✨ Sample data seeder for testing (7 classrooms, 10 subjects, 28 schedules)
- ✨ Admin menu "Jadwal Pelajaran" in sidebar (purple-pink calendar icon)

**Grid Features:**

- 📅 Days as row headers with rowspan for time slots
- 🕐 Time slot column showing name and time range
- 🏫 Classroom columns (dynamically loaded per school)
- 📚 Filled cells: Subject name + Teacher name in emerald gradient boxes
- ➕ Empty cells: Plus icon button to add schedule
- ✏️ Click filled cell to edit schedule via modal
- 🗑️ Delete button in edit modal
- 🚫 Conflict validation: "Guru sudah mengajar di waktu yang sama"
- 🎯 Auto-fill context in modal (day, time, classroom read-only)

**Technical Implementation:**

- **Model:** TimeSlot with school relationship
- **Controller:** ScheduleGridController with 5 actions (index, store, edit, update, destroy)
- **View:** grid.blade.php (283 lines) with modal form and JavaScript functions
- **Routes:** 5 grid routes with proper precedence (before resource route)
- **Migration:** time_slots table + time_slot_id column in schedules table
- **Seeders:** TimeSlotsSeeder, SMKDataSeeder, SampleSchedulesSeeder (326 lines total)

**Sample Data:**

- 7 SMK Classrooms: X TKJ 1/2, X AKL 1, XI TKJ/AKL, XII TKJ/AKL
- 10 Subjects: Matematika, B. Indonesia, B. Inggris, Pemrograman Web, Jaringan Komputer, Basis Data, Sistem Operasi, Akuntansi Dasar, Praktikum Akuntansi, Ekonomi Bisnis
- 28 Schedules: Distributed across 3 teachers (8-12 schedules each)

### Fixed

**Critical Laravel Route Conflict:**

- ❌ Issue: 404 error on `/admin/schedules/grid` despite route existing
- 🔍 Root cause: `Route::resource('schedules')` defined BEFORE specific grid routes
- 📋 Diagnosis: Resource route `{schedule}` parameter catching `/schedules/grid` as show action
- ✅ Solution: Moved grid routes (lines 78-84) BEFORE resource route (line 86)
- 🧹 Cleanup: Removed duplicate grid route definitions (lines 117-121)
- 💨 Cache: Cleared all 6 cache types using `php artisan optimize:clear` (432ms)

**Key Lesson Learned:**

> In Laravel, specific routes MUST be defined BEFORE resource routes to prevent catch-all parameter matching. Always clear route cache after route file changes.

### Technical Details

- **Routes:** 5 grid routes under `/admin/schedules` (grid, store-grid, edit-grid, update-grid, delete-grid)
- **Authorization:** School-based access control (user can only manage their school's schedules)
- **Validation:** Required fields (teacher, subject, classroom, time_slot, day)
- **Conflict Detection:** Checks for same teacher OR classroom at same time
- **Data Loading:** Eager loading with relationships (teacher, subject, classroom, timeSlot)
- **Grid Algorithm:** Nested loops (days × time slots × classrooms) with helper closure for schedule lookup

### Documentation

- 📄 `PHASE_9_1_SCHEDULE_GRID_SYSTEM.md` - Complete technical documentation (933 lines)
- 📊 Includes: Overview, Features, Code Implementation, Testing Guide, Future Enhancements
- 🎓 Key Learnings: Route precedence, Grid layout algorithm, Modal CRUD pattern, Conflict detection

---

## [2026-02-02] - Phase 10A: Academic System - Rapor Digital ✅

### Added

**Rapor Digital (Report Cards):**

- ✨ Database tables: `report_cards` (rapor) & `student_achievements` (prestasi)
- ✨ Auto-generate rapor from grades & attendance data
- ✨ Status workflow: draft → finalized → published
- ✨ Comprehensive rapor display with nilai per mata pelajaran
- ✨ Attendance tracking (Hadir/Sakit/Izin/Alpa) with percentage
- ✨ Ranking system per classroom
- ✨ Teacher & Principal notes fields
- ✨ Achievements & recommendations section
- ✨ Professional PDF export with proper formatting
- ✨ Filter by semester, classroom, status
- ✨ Edit notes (only for draft status)
- ✨ Finalize & publish workflow with audit trail
- ✨ Admin menu "Rapor Digital" in Academic section

**Report Card Features:**

- 📊 Weighted grading: Tugas (20%), UTS (30%), UAS (40%), Sikap (10%)
- 📈 Automatic predicate calculation (A/B/C/D)
- 🏆 Student achievements integration
- 📄 PDF template matching official report card format
- 🔒 Status-based access control (draft editable, finalized/published read-only)

**Student Achievements:**

- 🏆 Track prestasi: Academic, Sport, Art, Competition, Other
- 🌍 Level tracking: School, District, City, Province, National, International
- 🥇 Rank tracking: Winner, Runner-up, Third Place, Participant
- 📸 Certificate file upload support

### Technical Details

- **Models:** ReportCard, StudentAchievement with relationships
- **Controller:** ReportCardController with 8 actions (index, generate, show, edit, update, finalize, publish, print)
- **Views:** index (list with filters), show (detail), edit (notes), pdf (print template)
- **Routes:** 8 routes under `/admin/report-cards`
- **PDF:** DomPDF integration for professional report card generation

---

## [2026-02-02] - Phase 9: Treasurer Module - Complete Financial System ✅

### Added

**Real-time Dashboard:**

- ✨ 4 new cards replacing old statistics: Tunggakan Bulan Ini, Terbayar Bulan Ini, Tunggakan YTD, Terbayar YTD
- ✨ Period-specific calculations (current month vs year-to-date)
- ✨ Color-coded cards (orange-red, green-emerald, purple-indigo, blue-cyan)
- ✨ Complex date filtering logic for YTD calculations
- ✨ Quick action button "Laporan" added to dashboard

**Matrix Report System (NEW):**

- ✨ Complete report controller with comprehensive filtering
- ✨ Matrix view: Student (rows) x Month (columns) format
- ✨ Visual indicators: ✓ (paid), X (unpaid), - (no bill)
- ✨ Filters: Payment Type, Academic Year, Classroom, Period Type (yearly/ytd/month)
- ✨ Toggle "Tampilkan semua" untuk include non-active students
- ✨ Student status badges (Aktif/Lulus/Pindah/Keluar) with color coding
- ✨ Summary columns: Total ✓ and Total X per student
- ✨ Filter info display box (Tahun Ajaran, Kelas, Jenis Tagihan)
- ✨ Legend box for mark meanings

**Professional Excel Export:**

- ✨ Header section with title and filter info (Rows 1-5)
- ✨ Matrix format matching screen layout
- ✨ Columns: No, Nama, NISN, Kelas, Jul-Jun (12 months), Total Bayar, Total Belum
- ✨ Data mapping: ✓/X marks per month
- ✨ Emerald header styling (#10B981), white text, bold
- ✨ All borders, center alignment for months, auto-width columns
- ✨ Professional layout ready for management presentation

**Student Status Handling:**

- ✨ Auto-filter siswa aktif di dashboard (exclude lulus/pindah/keluar)
- ✨ Auto-filter siswa aktif di bulk bill creation
- ✨ Toggle option in report untuk include all statuses
- ✨ Status badge indicators (green/blue/orange/red)
- ✨ Info box in bulk create: "Hanya siswa AKTIF yang akan diproses"
- ✨ Gray background for non-active student rows
- ✨ Audit trail maintained for all historical data

**Menu & Navigation:**

- ✨ New "Laporan" menu item in treasurer sidebar (indigo-purple icon)
- ✨ 5th quick action button in dashboard for reports
- ✨ Routes: /bendahara/reports, /bendahara/reports/export

### Fixed

**Critical Bugs:**

- 🐛 Fixed duplicate academic years in dropdown (3x entries) → Added school_id filter
- 🐛 Fixed duplicate payment types in dropdown → Added school_id filter
- 🐛 Fixed year calculation bug (bills created for 2026 instead of 2025) → Base year logic from academic_year
- 🐛 Fixed "View not found" errors for treasurer.payments.create and bulk-create → Created missing views
- 🐛 Fixed "Undefined variable $schoolId" in Admin\StudentBillController → Added at method start
- 🐛 Fixed variable conflict in report view ($year override by loop) → Renamed to $ay in foreach

**Data Issues:**

- 🐛 Prevented cross-school data leakage → All queries filtered by school_id
- 🐛 Fixed N+1 query problem in reports → Eager loading with ->with()
- 🐛 Optimized dropdown queries → DISTINCT + whereIn for active data only

### Changed

**Dashboard Redesign:**

- 📝 Changed from 3 cards (annual stats) to 4 cards (monthly + YTD)
- 📝 Updated statistics calculation from yearly to period-specific
- 📝 Quick actions grid from 4 columns to 5 columns

**Report Format:**

- 📝 Changed from traditional table (1 student = 12 rows) to matrix (1 student = 1 row)
- 📝 Removed tab interface (Per Bulan / Per Kelas) → Single matrix view
- 📝 Excel export format completely redesigned for efficiency

**Student Filtering:**

- 📝 Default behavior now excludes non-active students
- 📝 Explicit toggle required to view all students including non-active

### Documentation

- 📚 Created PHASE_9_FINANCIAL_SYSTEM_COMPLETION.md (comprehensive technical report)
- 📚 Created FINANCIAL_SYSTEM_QUICK_REF.md (developer quick reference)
- 📚 Updated DOCUMENTATION_INDEX.md with Phase 9 links
- 📚 Updated CHANGELOG.md with all changes

### Performance

- ⚡ Report row reduction: 12x fewer rows (1200 → 100 for 100 students)
- ⚡ Excel file size: 90% reduction (~500 KB → ~50 KB)
- ⚡ Query optimization: 68% faster report generation (~2.5s → ~0.8s)
- ⚡ Database queries: 60% reduction via eager loading

---

## [2026-02-01] - Phase 7: Billing System Enhancements 🚀

### Added

**Financial System - Late Fee Automation:**

- ✨ Automatic late fee calculation system with configurable grace period
- ✨ New `settings` table for system-wide configuration management
- ✨ `Setting` model with type-safe getValue/setValue methods
- ✨ Late fee accessor in StudentBill model (late_fee, total_with_late_fee, outstanding_with_late_fee)
- ✨ Orange alert sections in Quick Pay and Batch Payment modals for late fee display
- ✨ Late fee breakdown in payment tooltips and summaries

**Payment History & Tracking:**

- ✨ Payment history timeline page per student at `/admin/students/{student}/payments`
- ✨ 4 summary cards: Total Tagihan (purple), Sudah Dibayar (green), Sisa Tunggakan (red), Denda Keterlambatan (yellow)
- ✨ Timeline view with date badges, payment details, verification status
- ✨ All bills table with 8 columns including late fee highlighting
- ✨ Pagination support (20 payments per page)
- ✨ "Riwayat Pembayaran" button in student detail page

**Data Export:**

- ✨ Excel export functionality for bills at `/admin/bills/export`
- ✨ Excel export functionality for payments at `/admin/payments/export`
- ✨ BillsExport class with 8 columns (Student, NISN, Type, Year, Month, Amount, Status)
- ✨ PaymentsExport class with comprehensive payment data
- ✨ Export respects all active filters (academic year, payment type, classroom, search)
- ✨ Auto-generated filename with timestamp

**Dashboard Analytics:**

- ✨ 6 Chart.js visualizations on admin dashboard
- ✨ Monthly payment trends (line chart, last 6 months)
- ✨ Payment status distribution (doughnut chart)
- ✨ Payment type breakdown (bar chart)
- ✨ Top 10 students by outstanding (horizontal bar)
- ✨ Payment methods distribution (pie chart)
- ✨ Financial KPIs: total bills, paid, outstanding, payment rate

**Enhanced Filtering:**

- ✨ Classroom filter dropdown in bills index page
- ✨ Classroom filter uses many-to-many relationship (student_classes pivot)
- ✨ Export button includes classroom_id parameter

**UX Improvements:**

- ✨ Redesigned create bill form with logical field ordering
- ✨ Helper text and emoji icons for better field recognition
- ✨ Required field indicators with red asterisks
- ✨ Grid layout for related fields (Payment Type + Academic Year, Month + Year)

### Changed

**Form Optimization:**

- 🔧 Made month and year fields optional (nullable validation)
- 🔧 Removed school selection field from create bill form (auto-determined from student)
- 🔧 Consolidated duplicate "Jumlah" fields into single "Jumlah Tagihan" field
- 🔧 Consolidated duplicate "Catatan" fields into single textarea
- 🔧 Reordered fields to start with Student selection (most logical entry point)
- 🔧 Added due_date field to validation (nullable|date)

**Duplicate Prevention:**

- 🔧 Payment types dropdown now uses `unique('type_code')` to eliminate duplicates
- 🔧 Academic years dropdown uses `unique('year')` for single entry per year
- 🔧 Classrooms dropdown uses `unique('id')` after distinct pluck
- 🔧 Applied to index(), create(), and bulkCreate() methods

**Query Optimization:**

- 🔧 Bills index uses DB::table with distinct for dropdown data
- 🔧 Collection-based deduplication instead of SQL GROUP BY (avoids MySQL strict mode issues)
- 🔧 Eager loading with with() for payment history queries

### Fixed

- 🐛 Duplicate payment type entries in create bill dropdown (3-4x → 1x)
- 🐛 Duplicate academic year entries (3-4x → 1x)
- 🐛 Triple "Biaya Bulanan (SPP)" entries from multiple schools
- 🐛 Duplicate classroom entries in filter dropdown
- 🐛 Required month validation blocking non-monthly bills (ujian, pendaftaran)
- 🐛 Classroom filter SQL error (changed to proper many-to-many query)
- 🐛 Undefined $schools variable in create view
- 🐛 MySQL GROUP BY error with strict mode (switched to collection unique)

### Database

**New Tables:**

- `settings` - System configuration with key-value pairs and type casting
    - Columns: id, key (unique), value, type (enum), group, description, timestamps
    - Initial data: late_fee_enabled, late_fee_grace_period, late_fee_amount, late_fee_type

**New Routes:**

- `GET /admin/students/{student}/payments` → StudentController@paymentHistory
- `GET /admin/bills/export` → StudentBillController@export
- `GET /admin/payments/export` → PaymentController@export

**New Files:**

- `database/migrations/2026_02_01_100000_create_settings_table.php`
- `app/Models/Setting.php`
- `app/Exports/BillsExport.php`
- `app/Exports/PaymentsExport.php`
- `resources/views/admin/students/payment-history.blade.php`

**Modified Files:**

- `app/Models/StudentBill.php` (added late_fee accessors)
- `app/Http/Controllers/Admin/StudentBillController.php` (export, filters, duplicates)
- `app/Http/Controllers/Admin/StudentController.php` (paymentHistory method)
- `app/Http/Controllers/Admin/DashboardController.php` (analytics queries)
- `resources/views/admin/bills/index.blade.php` (643 lines - late fee UI, classroom filter)
- `resources/views/admin/bills/create.blade.php` (130 lines - redesigned)
- `resources/views/admin/students/show.blade.php` (added button)
- `resources/views/admin/dashboard.blade.php` (added charts)
- `routes/web.php` (3 new routes)

**Dependencies:**

- 📦 Added: `maatwebsite/excel` v3.1 for Excel export functionality

**Documentation:**

- 📖 Created: `PHASE_7_BILLING_ENHANCEMENTS.md` (37,000+ words technical documentation)
- 📖 Updated: `README_DEVELOPMENT.md` with Phase 7 progress
- 📖 Updated: `README.md` with latest features

### Performance

- ⚡ Collection-based deduplication for small-medium datasets
- ⚡ Indexed queries for payment history (recommend: idx_payments_student_date)
- ⚡ Eager loading reduces N+1 queries in payment history
- ⚡ Pagination limits memory usage (20 items/page)

### Security

- 🔒 Authorization check in paymentHistory method
- 🔒 CSRF protection on all forms
- 🔒 Type-safe setting retrieval with match expression
- 🔒 Validated input for all form submissions

---

## [2026-02-01] - UI & Akses Akademik

### Changed

- Menu "Absensi" (biasa) dihapus, hanya "Absensi Kelas (Bulk)" yang tampil dan dipindah ke grup Akademik.
- Hak akses admin: hanya bisa melihat data nilai, tidak bisa tambah/edit/hapus nilai siswa.
- Tampilan nilai: satu baris per siswa, kolom nilai per jenis (Tugas, UTS, UAS, Sikap), ada kolom NISN dan rata-rata nilai.

## [Unreleased] - 2026-01-30

### Added

- Collapsible, grouped admin sidebar with icons and keyboard shortcuts. (Toggle via `#sidebar-toggle`, mobile via `#mobile-sidebar-toggle`)
- CSS transitions for sidebar collapse/expand and mobile slide-in.
- Quick Actions layout polish and icons for admin dashboard.
- `pa11y` accessibility check added to CI and `/a11y/admin-dashboard` route for automated scans (enabled in testing/CI only).
- Tests: `AdminNavigationTest` extended to check sidebar groups, toggle and aria attributes.

### Changed

- Admin views now use `layouts.admin` for consistent sidebar across admin pages.
