# DOKUMENTASI TEKNIS 03: MODULE PSB (PENERIMAAN SISWA BARU)

**Sistem Manajemen Sekolah Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)**  
**Versi:** 3.0.0  
**Tanggal:** 25 Agustus 2026

---

## DAFTAR ISI

1. [Overview Module PSB](#1-overview-module-psb)
2. [Arsitektur Module](#2-arsitektur-module)
3. [Database Tables](#3-database-tables)
4. [Models & Relationships](#4-models--relationships)
5. [Controllers](#5-controllers)
6. [Routes](#6-routes)
7. [Views](#7-views)
8. [Business Logic](#8-business-logic)
9. [Validation Rules](#9-validation-rules)
10. [Integration](#10-integration)
11. [User Flow](#11-user-flow)
12. [Testing](#12-testing)

---

## 1. OVERVIEW MODULE PSB

### Deskripsi

Module PSB (Penerimaan Siswa Baru) adalah sistem pendaftaran online untuk calon siswa baru di 3 sekolah:

- SMPS Pembda 2 (ID: 1)
- SMA Pembda 1 (ID: 2)
- SMKS Pembda Nias (ID: 3)

### Fitur Utama

1. **Pendaftaran Online** - Form pendaftaran dengan validasi lengkap
2. **Sistem Gelombang** - Pendaftaran dibagi dalam beberapa gelombang dengan kuota
3. **Multi-School Support** - Mendukung 3 sekolah dengan konfigurasi berbeda
4. **Program & Konsentrasi** - Khusus SMK dengan pilihan program dan konsentrasi keahlian
5. **Upload Dokumen** - Upload foto dan dokumen pendukung
6. **Admin Dashboard** - Manajemen pendaftar dengan filter dan export
7. **WhatsApp Notification** - Notifikasi otomatis ke orang tua via WhatsApp

### Technology Stack

- **Backend:** Laravel 12.49.0 (PHP 8.2.12)
- **Frontend:** Blade Templates + Tailwind CSS 3.x + JavaScript
- **Database:** MySQL (pembda_hub)
- **File Storage:** Laravel Storage (local disk)
- **Notification:** Fonnte WhatsApp API

---

## 2. ARSITEKTUR MODULE

### Directory Structure

```
app/
├── Http/
│   └── Controllers/
│       ├── PublicRegistrationController.php    # Public-facing registration
│       └── Admin/
│           └── ApplicantController.php         # Admin management
├── Models/
│   ├── Applicant.php                           # Applicant model
│   ├── Gelombang.php                           # Wave/period model
│   ├── ProgramKeahlian.php                     # Major program model (SMK)
│   ├── KonsentrasiKeahlian.php                 # Major specialization model (SMK)
│   ├── School.php                              # School model
│   └── AcademicYear.php                        # Academic year model
└── Services/
    └── NotificationService.php                 # WhatsApp notification service

resources/
└── views/
    ├── public/
    │   ├── homepage.blade.php                  # Landing page with wave info
    │   └── registration.blade.php              # Registration form
    └── admin/
        └── psb/
            ├── index.blade.php                 # List applicants with filters
            ├── show.blade.php                  # View applicant detail
            ├── edit.blade.php                  # Edit applicant
            └── create.blade.php                # Manual create applicant

routes/
└── web.php                                      # Route definitions

database/
└── migrations/
    ├── xxxx_create_applicants_table.php
    ├── xxxx_create_gelombangs_table.php
    ├── xxxx_create_program_keahlians_table.php
    ├── xxxx_create_konsentrasi_keahlians_table.php
    └── 2026_02_08_081050_add_program_konsentrasi_to_applicants_table.php
```

---

## 3. DATABASE TABLES

### 3.1 `applicants`

**Primary table untuk data pendaftar**

```sql
CREATE TABLE `applicants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) unsigned NOT NULL,
  `academic_year_id` bigint(20) unsigned NOT NULL,
  `gelombang_id` bigint(20) unsigned DEFAULT NULL,
  `registration_number` varchar(50) NOT NULL,
  `registration_type` enum('Baru','Pindahan') NOT NULL DEFAULT 'Baru',
  `nisn` varchar(20) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `gender` enum('L','P') NOT NULL,
  `birth_place` varchar(100) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `previous_school` varchar(255) DEFAULT NULL,
  `parent_name` varchar(255) DEFAULT NULL,
  `parent_phone` varchar(20) DEFAULT NULL,
  `parent_email` varchar(100) DEFAULT NULL,
  `program_keahlian_id` bigint(20) unsigned DEFAULT NULL,
  `konsentrasi_keahlian_id` bigint(20) unsigned DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `documents` json DEFAULT NULL,
  `status` enum('Pending','Diterima','Ditolak','Cadangan') NOT NULL DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `registration_number` (`registration_number`),
  KEY `school_id` (`school_id`),
  KEY `academic_year_id` (`academic_year_id`),
  KEY `gelombang_id` (`gelombang_id`),
  KEY `program_keahlian_id` (`program_keahlian_id`),
  KEY `konsentrasi_keahlian_id` (`konsentrasi_keahlian_id`),
  CONSTRAINT FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT FOREIGN KEY (`gelombang_id`) REFERENCES `gelombangs` (`id`) ON DELETE SET NULL,
  CONSTRAINT FOREIGN KEY (`program_keahlian_id`) REFERENCES `program_keahlians` (`id`) ON DELETE SET NULL,
  CONSTRAINT FOREIGN KEY (`konsentrasi_keahlian_id`) REFERENCES `konsentrasi_keahlians` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Business Rules:**

- `registration_number` format: `PSB-{SCHOOL_CODE}-{YEAR}-{SEQUENCE}`
    - Contoh: PSB-SMPS-2026-0001, PSB-SMA-2026-0001, PSB-SMKS-2026-0001
- `program_keahlian_id` dan `konsentrasi_keahlian_id` WAJIB jika `school_id = 3` (SMKS)
- `program_keahlian_id` dan `konsentrasi_keahlian_id` NULL untuk SMP dan SMA

### 3.2 `gelombangs`

**Table untuk periode/gelombang pendaftaran**

```sql
CREATE TABLE `gelombangs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) unsigned NOT NULL,
  `academic_year_id` bigint(20) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `quota` int(11) NOT NULL,
  `registration_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Business Rules:**

- Hanya 1 gelombang yang boleh `is_active = 1` per sekolah per tahun ajaran
- `registration_fee`:
    - SMP & SMA: Rp 50.000
    - SMK: Rp 300.000 (Rp 50.000 pendaftaran + Rp 250.000 peralatan praktik)

### 3.3 `program_keahlians`

**Table untuk program keahlian SMK (level 1)**

```sql
CREATE TABLE `program_keahlians` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) unsigned NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `school_id` (`school_id`),
  CONSTRAINT FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Data Example:**

```
1. Teknik Komputer dan Informatika (TKI)
2. Teknik Otomotif (TO)
3. Tata Busana (TB)
```

### 3.4 `konsentrasi_keahlians`

**Table untuk konsentrasi keahlian SMK (level 2)**

```sql
CREATE TABLE `konsentrasi_keahlians` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `program_keahlian_id` bigint(20) unsigned NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `program_keahlian_id` (`program_keahlian_id`),
  CONSTRAINT FOREIGN KEY (`program_keahlian_id`) REFERENCES `program_keahlians` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Data Example (untuk Teknik Komputer dan Informatika):**

```
1. Teknik Komputer dan Jaringan (TKJ)
2. Multimedia (MM)
3. Rekayasa Perangkat Lunak (RPL)
```

---

## 4. MODELS & RELATIONSHIPS

### 4.1 `Applicant` Model

**File:** `app/Models/Applicant.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Applicant extends Model
{
    protected $fillable = [
        'school_id',
        'academic_year_id',
        'gelombang_id',
        'registration_number',
        'registration_type',
        'nisn',
        'name',
        'gender',
        'birth_place',
        'birth_date',
        'religion',
        'address',
        'phone',
        'previous_school',
        'parent_name',
        'parent_phone',
        'parent_email',
        'program_keahlian_id',      // Khusus SMK
        'konsentrasi_keahlian_id',  // Khusus SMK
        'photo',
        'documents',
        'status',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'documents' => 'array',
    ];

    // Relationships
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function gelombang(): BelongsTo
    {
        return $this->belongsTo(Gelombang::class);
    }

    public function programKeahlian(): BelongsTo
    {
        return $this->belongsTo(ProgramKeahlian::class);
    }

    public function konsentrasiKeahlian(): BelongsTo
    {
        return $this->belongsTo(KonsentrasiKeahlian::class);
    }

    // Accessor for display
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        return substr($initials, 0, 2);
    }
}
```

### 4.2 `Gelombang` Model

**File:** `app/Models/Gelombang.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gelombang extends Model
{
    protected $fillable = [
        'school_id',
        'academic_year_id',
        'name',
        'start_date',
        'end_date',
        'quota',
        'registration_fee',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function applicants(): HasMany
    {
        return $this->hasMany(Applicant::class);
    }

    // Check if registration is open
    public function isOpen(): bool
    {
        $now = now()->format('Y-m-d');
        return $this->is_active
            && $now >= $this->start_date->format('Y-m-d')
            && $now <= $this->end_date->format('Y-m-d');
    }

    // Get remaining quota
    public function getRemainingQuotaAttribute(): int
    {
        $registered = $this->applicants()->count();
        return max(0, $this->quota - $registered);
    }

    // Get progress percentage
    public function getProgressAttribute(): int
    {
        if ($this->quota == 0) return 0;
        $registered = $this->applicants()->count();
        return min(100, round(($registered / $this->quota) * 100));
    }
}
```

### 4.3 `ProgramKeahlian` Model

**File:** `app/Models/ProgramKeahlian.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramKeahlian extends Model
{
    protected $fillable = [
        'school_id',
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function konsentrasiKeahlians(): HasMany
    {
        return $this->hasMany(KonsentrasiKeahlian::class);
    }

    public function applicants(): HasMany
    {
        return $this->hasMany(Applicant::class);
    }
}
```

### 4.4 `KonsentrasiKeahlian` Model

**File:** `app/Models/KonsentrasiKeahlian.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KonsentrasiKeahlian extends Model
{
    protected $fillable = [
        'program_keahlian_id',
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function programKeahlian(): BelongsTo
    {
        return $this->belongsTo(ProgramKeahlian::class);
    }

    public function applicants(): HasMany
    {
        return $this->hasMany(Applicant::class);
    }
}
```

---

## 5. CONTROLLERS

### 5.1 `PublicRegistrationController`

**File:** `app/Http/Controllers/PublicRegistrationController.php`

**Responsibility:** Handle public-facing registration form and submission

**Methods:**

#### `index()`

**Purpose:** Display registration form

```php
public function index()
{
    // Get active academic year
    $activeAcademicYear = AcademicYear::where('is_active', true)->first();

    if (!$activeAcademicYear) {
        return redirect()->back()->with('error', 'Tidak ada tahun ajaran aktif.');
    }

    // Get all schools
    $schools = School::all();

    // Get active gelombangs for each school
    $gelombangs = Gelombang::with('school')
        ->where('academic_year_id', $activeAcademicYear->id)
        ->where('is_active', true)
        ->get();

    // Get all program keahlian (for SMK)
    $programKeahlians = ProgramKeahlian::where('school_id', 3)
        ->where('is_active', true)
        ->get();

    return view('public.registration', compact(
        'schools',
        'gelombangs',
        'programKeahlians',
        'activeAcademicYear'
    ));
}
```

#### `store(Request $request)`

**Purpose:** Process registration submission

```php
public function store(Request $request)
{
    // Validation (see section 9)
    $validated = $request->validate([
        'school_id' => 'required|exists:schools,id',
        'registration_type' => 'required|in:Baru,Pindahan',
        'name' => 'required|string|max:255',
        'gender' => 'required|in:L,P',
        'nisn' => 'nullable|string|max:20',
        'birth_place' => 'nullable|string|max:100',
        'birth_date' => 'nullable|date',
        'religion' => 'nullable|string|max:50',
        'address' => 'nullable|string',
        'phone' => 'nullable|string|max:20',
        'previous_school' => 'nullable|string|max:255',
        'parent_name' => 'required|string|max:255',
        'parent_phone' => 'required|string|max:20',
        'parent_email' => 'nullable|email|max:100',
        'program_keahlian_id' => 'required_if:school_id,3|nullable|exists:program_keahlians,id',
        'konsentrasi_keahlian_id' => 'required_if:school_id,3|nullable|exists:konsentrasi_keahlians,id',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    // Get active academic year
    $academicYear = AcademicYear::where('is_active', true)->first();

    // Get active gelombang for this school
    $gelombang = Gelombang::where('school_id', $validated['school_id'])
        ->where('academic_year_id', $academicYear->id)
        ->where('is_active', true)
        ->first();

    // Generate registration number
    $registrationNumber = $this->generateRegistrationNumber($validated['school_id']);

    // Handle photo upload
    $photoPath = null;
    if ($request->hasFile('photo')) {
        $photoPath = $request->file('photo')->store('applicants/photos', 'public');
    }

    // Create applicant
    $applicant = Applicant::create([
        'school_id' => $validated['school_id'],
        'academic_year_id' => $academicYear->id,
        'gelombang_id' => $gelombang ? $gelombang->id : null,
        'registration_number' => $registrationNumber,
        'registration_type' => $validated['registration_type'],
        'name' => $validated['name'],
        'gender' => $validated['gender'],
        'nisn' => $validated['nisn'],
        'birth_place' => $validated['birth_place'],
        'birth_date' => $validated['birth_date'],
        'religion' => $validated['religion'],
        'address' => $validated['address'],
        'phone' => $validated['phone'],
        'previous_school' => $validated['previous_school'],
        'parent_name' => $validated['parent_name'],
        'parent_phone' => $validated['parent_phone'],
        'parent_email' => $validated['parent_email'],
        'program_keahlian_id' => $validated['program_keahlian_id'] ?? null,
        'konsentrasi_keahlian_id' => $validated['konsentrasi_keahlian_id'] ?? null,
        'photo' => $photoPath,
        'status' => 'Pending',
    ]);

    // Send WhatsApp notification to parent
    $this->sendRegistrationNotification($applicant);

    return redirect()->back()->with('success',
        'Pendaftaran berhasil! Nomor pendaftaran Anda: ' . $registrationNumber
    );
}
```

#### `getProgramKeahlian($schoolId)`

**Purpose:** API endpoint for AJAX - get program keahlian by school

```php
public function getProgramKeahlian($schoolId)
{
    $programs = ProgramKeahlian::where('school_id', $schoolId)
        ->where('is_active', true)
        ->select('id', 'name', 'code')
        ->get();

    return response()->json($programs);
}
```

#### `getKonsentrasiKeahlian($programId)`

**Purpose:** API endpoint for AJAX - get konsentrasi keahlian by program

```php
public function getKonsentrasiKeahlian($programId)
{
    $konsentrasi = KonsentrasiKeahlian::where('program_keahlian_id', $programId)
        ->where('is_active', true)
        ->select('id', 'name', 'code')
        ->get();

    return response()->json($konsentrasi);
}
```

#### `generateRegistrationNumber($schoolId)`

**Purpose:** Generate unique registration number

```php
private function generateRegistrationNumber($schoolId): string
{
    $school = School::find($schoolId);
    $year = date('Y');

    // Get school code
    $schoolCode = match($schoolId) {
        1 => 'SMPS',
        2 => 'SMA',
        3 => 'SMKS',
        default => 'PSB',
    };

    // Get last sequence number for this school and year
    $lastApplicant = Applicant::where('school_id', $schoolId)
        ->whereYear('created_at', $year)
        ->orderBy('id', 'desc')
        ->first();

    $sequence = $lastApplicant ? (int)substr($lastApplicant->registration_number, -4) + 1 : 1;

    return sprintf('PSB-%s-%s-%04d', $schoolCode, $year, $sequence);
}
```

#### `sendRegistrationNotification($applicant)`

**Purpose:** Send WhatsApp notification to parent

```php
private function sendRegistrationNotification($applicant): void
{
    $notificationService = app(NotificationService::class);

    $message = "Yth. {$applicant->parent_name},\n\n";
    $message .= "Pendaftaran atas nama *{$applicant->name}* telah berhasil dengan nomor pendaftaran: *{$applicant->registration_number}*\n\n";
    $message .= "Sekolah: {$applicant->school->name}\n";
    $message .= "Status: Menunggu Verifikasi\n\n";
    $message .= "Terima kasih telah mendaftar di Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA).";

    $notificationService->sendWhatsApp($applicant->parent_phone, $message);
}
```

---

### 5.2 `ApplicantController` (Admin)

**File:** `app/Http/Controllers/Admin/ApplicantController.php`

**Responsibility:** Admin CRUD and management of applicants

**Methods:**

#### `index(Request $request)`

**Purpose:** Display list of applicants with filters

```php
public function index(Request $request)
{
    $query = Applicant::with([
        'school',
        'academicYear',
        'gelombang',
        'programKeahlian',
        'konsentrasiKeahlian'
    ]);

    // Filter by academic year
    if ($request->filled('academic_year_id')) {
        $query->where('academic_year_id', $request->academic_year_id);
    }

    // Filter by school
    if ($request->filled('school_id')) {
        $query->where('school_id', $request->school_id);
    }

    // Filter by program keahlian
    if ($request->filled('program_keahlian_id')) {
        $query->where('program_keahlian_id', $request->program_keahlian_id);
    }

    // Filter by status
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // Search by name or registration number
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('registration_number', 'like', "%{$search}%");
        });
    }

    $applicants = $query->latest()->paginate(20);

    // Statistics for cards
    $smpCount = Applicant::where('school_id', 1)->count();
    $smaCount = Applicant::where('school_id', 2)->count();
    $smkCount = Applicant::where('school_id', 3)->count();
    $totalApplicants = Applicant::count();

    // Data for filter dropdowns
    $schools = School::all();
    $academicYears = AcademicYear::orderBy('name', 'desc')->get();
    $programKeahlians = ProgramKeahlian::where('is_active', true)->get();

    return view('admin.psb.index', compact(
        'applicants',
        'schools',
        'academicYears',
        'programKeahlians',
        'smpCount',
        'smaCount',
        'smkCount',
        'totalApplicants'
    ));
}
```

#### `show($id)`

**Purpose:** Display applicant detail

```php
public function show($id)
{
    $applicant = Applicant::with([
        'school',
        'academicYear',
        'gelombang',
        'programKeahlian',
        'konsentrasiKeahlian'
    ])->findOrFail($id);

    return view('admin.psb.show', compact('applicant'));
}
```

#### `export(Request $request)`

**Purpose:** Export applicants to Excel (CSV)

```php
public function export(Request $request)
{
    $query = Applicant::with([
        'school',
        'academicYear',
        'gelombang',
        'programKeahlian',
        'konsentrasiKeahlian'
    ]);

    // Apply same filters as index
    if ($request->filled('academic_year_id')) {
        $query->where('academic_year_id', $request->academic_year_id);
    }
    if ($request->filled('school_id')) {
        $query->where('school_id', $request->school_id);
    }
    if ($request->filled('program_keahlian_id')) {
        $query->where('program_keahlian_id', $request->program_keahlian_id);
    }
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('registration_number', 'like', "%{$search}%");
        });
    }

    $applicants = $query->latest()->get();

    // Generate filename
    $filename = 'data-pendaftar-' . date('Y-m-d-His') . '.csv';

    // Create CSV with UTF-8 BOM
    $handle = fopen('php://output', 'w');
    fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

    // Headers
    fputcsv($handle, [
        'No. Pendaftaran',
        'Tahun Ajaran',
        'Sekolah',
        'Gelombang',
        'Jenis',
        'NISN',
        'Nama',
        'JK',
        'TTL',
        'Agama',
        'Alamat',
        'HP Siswa',
        'Asal Sekolah',
        'Nama Ortu',
        'HP Ortu',
        'Email Ortu',
        'Program Keahlian',
        'Konsentrasi Keahlian',
        'Status',
        'Tgl Daftar'
    ]);

    // Data rows
    foreach ($applicants as $applicant) {
        fputcsv($handle, [
            $applicant->registration_number,
            $applicant->academicYear->name ?? '-',
            $applicant->school->name ?? '-',
            $applicant->gelombang->name ?? '-',
            $applicant->registration_type,
            $applicant->nisn ?? '-',
            $applicant->name,
            $applicant->gender,
            ($applicant->birth_place ?? '-') . ', ' . ($applicant->birth_date ? $applicant->birth_date->format('d/m/Y') : '-'),
            $applicant->religion ?? '-',
            $applicant->address ?? '-',
            $applicant->phone ?? '-',
            $applicant->previous_school ?? '-',
            $applicant->parent_name ?? '-',
            $applicant->parent_phone ?? '-',
            $applicant->parent_email ?? '-',
            $applicant->programKeahlian->name ?? '-',
            $applicant->konsentrasiKeahlian->name ?? '-',
            $applicant->status,
            $applicant->created_at->format('d/m/Y H:i')
        ]);
    }

    fclose($handle);

    // Return response
    return response()->streamDownload(function() use ($handle) {
        // Stream is already closed
    }, $filename, [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ]);
}
```

#### `update(Request $request, $id)`

**Purpose:** Update applicant status and notes

```php
public function update(Request $request, $id)
{
    $applicant = Applicant::findOrFail($id);

    $validated = $request->validate([
        'status' => 'required|in:Pending,Diterima,Ditolak,Cadangan',
        'notes' => 'nullable|string',
    ]);

    $applicant->update($validated);

    // Send notification if status changed
    if ($applicant->isDirty('status')) {
        $this->sendStatusChangeNotification($applicant);
    }

    return redirect()->route('admin.psb.show', $applicant->id)
        ->with('success', 'Data pendaftar berhasil diupdate.');
}
```

#### `sendStatusChangeNotification($applicant)`

**Purpose:** Send notification when status changes

```php
private function sendStatusChangeNotification($applicant): void
{
    $notificationService = app(NotificationService::class);

    $statusText = match($applicant->status) {
        'Diterima' => 'DITERIMA',
        'Ditolak' => 'DITOLAK',
        'Cadangan' => 'MASUK DAFTAR CADANGAN',
        default => 'MENUNGGU VERIFIKASI',
    };

    $message = "Yth. {$applicant->parent_name},\n\n";
    $message .= "Status pendaftaran atas nama *{$applicant->name}* dengan nomor pendaftaran *{$applicant->registration_number}* telah diupdate menjadi: *{$statusText}*\n\n";

    if ($applicant->status === 'Diterima') {
        $message .= "Selamat! Silakan segera menghubungi sekolah untuk proses selanjutnya.\n\n";
    } elseif ($applicant->status === 'Ditolak') {
        $message .= "Mohon maaf, untuk informasi lebih lanjut silakan menghubungi sekolah.\n\n";
    }

    $message .= "Terima kasih.";

    $notificationService->sendWhatsApp($applicant->parent_phone, $message);
}
```

---

## 6. ROUTES

**File:** `routes/web.php`

```php
use App\Http\Controllers\PublicRegistrationController;
use App\Http\Controllers\Admin\ApplicantController;

// Public Routes
Route::get('/', function () {
    return view('public.homepage');
})->name('homepage');

Route::prefix('pendaftaran')->name('pendaftaran.')->group(function () {
    Route::get('/', [PublicRegistrationController::class, 'index'])->name('index');
    Route::post('/', [PublicRegistrationController::class, 'store'])->name('store');
});

// API Routes (outside pendaftaran prefix for global access)
Route::get('/api/program-keahlian/{schoolId}', [PublicRegistrationController::class, 'getProgramKeahlian']);
Route::get('/api/konsentrasi-keahlian/{programId}', [PublicRegistrationController::class, 'getKonsentrasiKeahlian']);

// Admin Routes (requires authentication)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('psb')->name('psb.')->group(function () {
        Route::get('/', [ApplicantController::class, 'index'])->name('index');
        Route::get('/export', [ApplicantController::class, 'export'])->name('export');
        Route::get('/{id}', [ApplicantController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ApplicantController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ApplicantController::class, 'update'])->name('update');
        Route::delete('/{id}', [ApplicantController::class, 'destroy'])->name('destroy');
    });
});
```

---

## 7. VIEWS

### 7.1 Public Registration Form

**File:** `resources/views/public/registration.blade.php`

**Key Features:**

- School selection dropdown
- Dynamic form fields based on school selection
- SMK-specific fields (program & konsentrasi) shown only when SMK is selected
- AJAX loading for program and konsentrasi dropdowns
- Photo upload preview
- Form validation with error messages

**JavaScript for Dynamic Loading:**

```javascript
// School change event
document.getElementById("school_id").addEventListener("change", function () {
    const schoolId = this.value;
    const smkSection = document.getElementById("smk-program-section");
    const programSelect = document.getElementById("program_keahlian_id");
    const konsentrasiSelect = document.getElementById(
        "konsentrasi_keahlian_id",
    );

    if (schoolId == 3) {
        // Show SMK fields
        smkSection.style.display = "block";
        programSelect.disabled = false;
        konsentrasiSelect.disabled = false;

        // Load program keahlian
        loadProgramKeahlian(schoolId);
    } else {
        // Hide SMK fields
        smkSection.style.display = "none";
        programSelect.disabled = true;
        konsentrasiSelect.disabled = true;
        programSelect.value = "";
        konsentrasiSelect.value = "";
    }
});

// Program keahlian change event
document
    .getElementById("program_keahlian_id")
    .addEventListener("change", function () {
        const programId = this.value;
        if (programId) {
            loadKonsentrasiKeahlian(programId);
        }
    });

// Load program keahlian via AJAX
function loadProgramKeahlian(schoolId) {
    fetch(`/api/program-keahlian/${schoolId}`)
        .then((response) => response.json())
        .then((data) => {
            const select = document.getElementById("program_keahlian_id");
            select.innerHTML =
                '<option value="">-- Pilih Program Keahlian --</option>';
            data.forEach((program) => {
                select.innerHTML += `<option value="${program.id}">${program.name}</option>`;
            });
        });
}

// Load konsentrasi keahlian via AJAX
function loadKonsentrasiKeahlian(programId) {
    fetch(`/api/konsentrasi-keahlian/${programId}`)
        .then((response) => response.json())
        .then((data) => {
            const select = document.getElementById("konsentrasi_keahlian_id");
            select.innerHTML =
                '<option value="">-- Pilih Konsentrasi Keahlian --</option>';
            data.forEach((konsentrasi) => {
                select.innerHTML += `<option value="${konsentrasi.id}">${konsentrasi.name}</option>`;
            });
        });
}
```

---

### 7.2 Admin Index Page

**File:** `resources/views/admin/psb/index.blade.php`

**Key Features:**

- Statistics cards (SMP, SMA, SMK, Total)
- Collapsible filter section with gradient header
- 4-column filter grid (Academic Year, School, Program, Status)
- Search input
- Export Excel button
- Paginated table with program/konsentrasi badges
- Responsive design with Tailwind CSS

**Filter Section:**

```html
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <!-- SMPS Card -->
    <div class="bg-blue-500 text-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-100 text-sm">SMPS Pembda 2</p>
                <p class="text-3xl font-bold">{{ $smpCount }}</p>
            </div>
            <i class="fas fa-graduation-cap text-5xl opacity-20"></i>
        </div>
    </div>

    <!-- SMA Card -->
    <div class="bg-green-500 text-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-green-100 text-sm">SMA Pembda 1</p>
                <p class="text-3xl font-bold">{{ $smaCount }}</p>
            </div>
            <i class="fas fa-book text-5xl opacity-20"></i>
        </div>
    </div>

    <!-- SMKS Card -->
    <div class="bg-purple-500 text-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-purple-100 text-sm">SMKS Pembda Nias</p>
                <p class="text-3xl font-bold">{{ $smkCount }}</p>
            </div>
            <i class="fas fa-tools text-5xl opacity-20"></i>
        </div>
    </div>

    <!-- Total Card -->
    <div class="bg-gray-800 text-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-300 text-sm">Total Pendaftar</p>
                <p class="text-3xl font-bold">{{ $totalApplicants }}</p>
            </div>
            <i class="fas fa-users text-5xl opacity-20"></i>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-4 flex items-center justify-between cursor-pointer"
         onclick="toggleFilter()">
        <div class="flex items-center space-x-3">
            <i class="fas fa-filter text-white text-xl"></i>
            <h3 class="text-lg font-semibold text-white">Filter & Pencarian</h3>
        </div>
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.psb.export', request()->all()) }}"
               class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-50 transition font-medium flex items-center space-x-2">
                <i class="fas fa-file-excel"></i>
                <span>Export Excel</span>
            </a>
            <i class="fas fa-chevron-down text-white transition-transform" id="filter-icon"></i>
        </div>
    </div>

    <!-- Filter Content (Collapsible) -->
    <div id="filter-content" class="p-6">
        <form method="GET" action="{{ route('admin.psb.index') }}" class="space-y-6">
            <!-- 4 Column Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Academic Year Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-2 text-blue-500"></i>Tahun Ajaran
                    </label>
                    <select name="academic_year_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- School Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-school mr-2 text-green-500"></i>Sekolah
                    </label>
                    <select name="school_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Program Keahlian Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-graduation-cap mr-2 text-purple-500"></i>Program Keahlian
                    </label>
                    <select name="program_keahlian_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua</option>
                        @foreach($programKeahlians as $program)
                            <option value="{{ $program->id }}" {{ request('program_keahlian_id') == $program->id ? 'selected' : '' }}>
                                {{ $program->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-flag mr-2 text-orange-500"></i>Status
                    </label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua</option>
                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Diterima" {{ request('status') == 'Diterima' ? 'selected' : '' }}>Diterima</option>
                        <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                        <option value="Cadangan" {{ request('status') == 'Cadangan' ? 'selected' : '' }}>Cadangan</option>
                    </select>
                </div>
            </div>

            <!-- Search Bar -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-search mr-2 text-gray-500"></i>Pencarian (Nama/No. Pendaftaran)
                </label>
                <div class="flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari nama atau nomor pendaftaran..."
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-search mr-2"></i>Cari
                    </button>
                    <a href="{{ route('admin.psb.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">
                        <i class="fas fa-redo mr-2"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function toggleFilter() {
    const content = document.getElementById('filter-content');
    const icon = document.getElementById('filter-icon');

    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        content.style.display = 'none';
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}
</script>
```

---

### 7.3 Admin Detail Page

**File:** `resources/views/admin/psb/show.blade.php`

**Key Features:**

- Photo display (132x132px) on right side
- Data fields on left side (2/3 width)
- Program & Konsentrasi section with gradient background
- Badges for program (indigo) and konsentrasi (purple)
- Status update form
- Action buttons

**Layout:**

```html
<div class="flex gap-6">
    <!-- Left: Data (2/3) -->
    <div class="w-2/3">
        <!-- Basic Info -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium text-gray-600">NISN</label>
                <p class="text-lg font-semibold">
                    {{ $applicant->nisn ?? '-' }}
                </p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600"
                    >Jenis Pendaftaran</label
                >
                <p class="text-lg font-semibold">
                    {{ $applicant->registration_type }}
                </p>
            </div>
            <!-- More fields... -->
        </div>

        <!-- Program & Konsentrasi (if SMK) -->
        @if($applicant->school_id == 3 && ($applicant->programKeahlian ||
        $applicant->konsentrasiKeahlian))
        <div
            class="mt-6 bg-gradient-to-r from-purple-50 to-pink-50 p-4 rounded-lg border border-purple-200"
        >
            <h4 class="font-semibold text-purple-900 mb-3 flex items-center">
                <i class="fas fa-graduation-cap mr-2"></i>Program & Konsentrasi
                Keahlian
            </h4>
            <div class="space-y-2">
                @if($applicant->programKeahlian)
                <div>
                    <span class="text-sm text-gray-600">Program:</span>
                    <span
                        class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800"
                    >
                        {{ $applicant->programKeahlian->name }}
                    </span>
                </div>
                @endif @if($applicant->konsentrasiKeahlian)
                <div>
                    <span class="text-sm text-gray-600">Konsentrasi:</span>
                    <span
                        class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800"
                    >
                        {{ $applicant->konsentrasiKeahlian->name }}
                    </span>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Right: Photo (1/3) -->
    <div class="w-1/3">
        <div class="bg-gray-50 rounded-lg p-4 text-center">
            @if($applicant->photo)
            <img
                src="{{ asset('storage/' . $applicant->photo) }}"
                alt="Foto {{ $applicant->name }}"
                class="w-32 h-32 rounded-lg object-cover mx-auto border-4 border-white shadow-md"
            />
            @else
            <div
                class="w-32 h-32 rounded-lg bg-blue-500 flex items-center justify-center mx-auto border-4 border-white shadow-md"
            >
                <span class="text-white text-4xl font-bold"
                    >{{ $applicant->initials }}</span
                >
            </div>
            @endif
            <p class="mt-2 text-sm font-medium text-gray-700">
                {{ $applicant->name }}
            </p>
        </div>
    </div>
</div>
```

---

## 8. BUSINESS LOGIC

### 8.1 Registration Number Generation

**Format:** `PSB-{SCHOOL_CODE}-{YEAR}-{SEQUENCE}`

**School Codes:**

- SMPS: SMP Pembda 2
- SMA: SMA Pembda 1
- SMKS: SMK Pembda Nias

**Example:**

- PSB-SMPS-2026-0001
- PSB-SMA-2026-0012
- PSB-SMKS-2026-0045

**Logic:**

```php
private function generateRegistrationNumber($schoolId): string
{
    $schoolCode = match($schoolId) {
        1 => 'SMPS',
        2 => 'SMA',
        3 => 'SMKS',
        default => 'PSB',
    };

    $year = date('Y');

    $lastApplicant = Applicant::where('school_id', $schoolId)
        ->whereYear('created_at', $year)
        ->orderBy('id', 'desc')
        ->first();

    $sequence = $lastApplicant ? (int)substr($lastApplicant->registration_number, -4) + 1 : 1;

    return sprintf('PSB-%s-%s-%04d', $schoolCode, $year, $sequence);
}
```

---

### 8.2 Gelombang/Wave System

**Rules:**

1. Hanya 1 gelombang yang boleh aktif per sekolah per tahun ajaran
2. Pendaftaran hanya bisa dilakukan jika ada gelombang aktif
3. Gelombang memiliki kuota dan tanggal mulai/selesai
4. Jika kuota penuh, gelombang otomatis tidak menerima pendaftar baru

**isOpen() Method:**

```php
public function isOpen(): bool
{
    $now = now()->format('Y-m-d');
    return $this->is_active
        && $now >= $this->start_date->format('Y-m-d')
        && $now <= $this->end_date->format('Y-m-d')
        && $this->remaining_quota > 0;
}
```

---

### 8.3 SMK Program & Konsentrasi Selection

**Rules:**

1. Hanya muncul jika school_id = 3 (SMKS Pembda Nias)
2. Program Keahlian dipilih dulu, baru Konsentrasi Keahlian
3. Konsentrasi Keahlian tergantung pada Program Keahlian yang dipilih
4. Keduanya WAJIB diisi untuk SMK

**Validation:**

```php
'program_keahlian_id' => 'required_if:school_id,3|nullable|exists:program_keahlians,id',
'konsentrasi_keahlian_id' => 'required_if:school_id,3|nullable|exists:konsentrasi_keahlians,id',
```

**AJAX Flow:**

1. User pilih sekolah → jika SMK, tampilkan section program/konsentrasi
2. User pilih program keahlian → fetch konsentrasi keahlian via API
3. User pilih konsentrasi keahlian → ready to submit

---

### 8.4 Registration Fee

**Biaya Pendaftaran:**

- **SMP & SMA:** Rp 50.000
- **SMK:** Rp 300.000
    - Rp 50.000 (biaya pendaftaran)
    - Rp 250.000 (biaya peralatan praktik)

**Note:** Biaya ini hanya biaya pendaftaran, belum termasuk:

- SPP bulanan
- Biaya seragam
- Biaya buku
- Biaya kegiatan

---

## 9. VALIDATION RULES

### Registration Form Validation

```php
[
    'school_id' => 'required|exists:schools,id',
    'registration_type' => 'required|in:Baru,Pindahan',
    'name' => 'required|string|max:255',
    'gender' => 'required|in:L,P',
    'nisn' => 'nullable|string|max:20',
    'birth_place' => 'nullable|string|max:100',
    'birth_date' => 'nullable|date|before:today',
    'religion' => 'nullable|string|max:50',
    'address' => 'nullable|string',
    'phone' => 'nullable|string|max:20|regex:/^([0-9\s\-\+\(\)]*)$/min:10',
    'previous_school' => 'nullable|string|max:255',
    'parent_name' => 'required|string|max:255',
    'parent_phone' => 'required|string|max:20|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
    'parent_email' => 'nullable|email|max:100',
    'program_keahlian_id' => 'required_if:school_id,3|nullable|exists:program_keahlians,id',
    'konsentrasi_keahlian_id' => 'required_if:school_id,3|nullable|exists:konsentrasi_keahlians,id',
    'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
]
```

### Admin Update Validation

```php
[
    'status' => 'required|in:Pending,Diterima,Ditolak,Cadangan',
    'notes' => 'nullable|string|max:1000',
]
```

---

## 10. INTEGRATION

### 10.1 WhatsApp Notification (Fonnte)

**Service:** `app/Services/NotificationService.php`

**Purpose:** Send WhatsApp notifications to parents

**Methods:**

- `sendWhatsApp($phone, $message)` - Send text message
- `sendWhatsAppWithImage($phone, $message, $imageUrl)` - Send message with image

**Integration Points:**

1. **After Registration:** Send confirmation with registration number
2. **Status Change:** Send notification when admin changes status (Diterima/Ditolak/Cadangan)

**Example Message:**

```
Yth. Bpk/Ibu [Nama Orang Tua],

Pendaftaran atas nama *[Nama Siswa]* telah berhasil dengan nomor pendaftaran: *[No. Pendaftaran]*

Sekolah: [Nama Sekolah]
Status: Menunggu Verifikasi

Terima kasih telah mendaftar di Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA).
```

---

## 11. USER FLOW

### 11.1 Public Registration Flow

```
1. User mengakses homepage → klik "Daftar Sekarang"
2. User memilih sekolah tujuan (SMP/SMA/SMK)
3. Jika SMK → tampilkan pilihan Program & Konsentrasi Keahlian
4. User mengisi data diri (nama, NISN, TTL, dll)
5. User mengisi data orang tua (nama, HP, email)
6. User upload foto (opsional)
7. User submit form
8. Sistem validasi data
9. Sistem generate nomor pendaftaran
10. Sistem save data ke database
11. Sistem kirim WhatsApp notification ke orang tua
12. Sistem tampilkan pesan sukses dengan nomor pendaftaran
```

### 11.2 Admin Management Flow

```
1. Admin login ke sistem
2. Admin akses menu PSB → Daftar Pendaftar
3. Admin lihat statistik per sekolah (cards)
4. Admin gunakan filter untuk mencari pendaftar tertentu
5. Admin klik "Lihat Detail" pada pendaftar
6. Admin review data pendaftar lengkap
7. Admin update status (Diterima/Ditolak/Cadangan)
8. Sistem kirim notification ke orang tua
9. Admin export data ke Excel jika diperlukan
```

---

## 12. TESTING

### 12.1 Test Cases

#### Registration Form

- [x] Form tampil dengan lengkap
- [x] Dropdown sekolah terisi
- [x] Section SMK muncul hanya jika pilih SMKS
- [x] API program keahlian berfungsi
- [x] API konsentrasi keahlian berfungsi
- [x] Validation berfungsi dengan benar
- [x] Photo upload berfungsi
- [x] Registration number generated correctly
- [x] Data tersimpan ke database
- [x] WhatsApp notification terkirim

#### Admin Management

- [x] Index page tampil dengan benar
- [x] Statistics cards menampilkan data real-time
- [x] Filter berfungsi untuk semua field
- [x] Search berfungsi (nama & no. pendaftaran)
- [x] Detail page tampil lengkap dengan photo
- [x] Program & konsentrasi tampil dengan badge
- [x] Status update berfungsi
- [x] Export Excel menghasilkan file CSV UTF-8 BOM
- [x] Pagination berfungsi

---

## CHANGELOG

| Tanggal    | Versi | Perubahan                        |
| ---------- | ----- | -------------------------------- |
| 08/02/2026 | 1.0   | Initial PSB module documentation |

---

**Dokumen dibuat oleh:** Tim Development Pembda Hub  
**Terakhir diupdate:** 8 Februari 2026
