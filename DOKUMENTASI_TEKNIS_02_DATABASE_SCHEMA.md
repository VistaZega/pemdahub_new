# DOKUMENTASI TEKNIS 02: DATABASE SCHEMA DETAIL

**Sistem Manajemen Sekolah Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)**  
**Versi:** 3.0.0  
**Tanggal:** 25 Agustus 2026  
**Database:** pembda_hub (MySQL)

---

## DAFTAR ISI

1. [Overview Database](#1-overview-database)
2. [User & Authentication Tables](#2-user--authentication-tables)
3. [Master Data Tables](#3-master-data-tables)
4. [Student Management Tables](#4-student-management-tables)
5. [Schedule Management Tables](#5-schedule-management-tables)
6. [PSB (Penerimaan Siswa Baru) Tables](#6-psb-tables)
7. [Financial Management Tables](#7-financial-management-tables)
8. [Assessment Tables](#8-assessment-tables)
9. [Notification Tables](#9-notification-tables)
10. [System Tables](#10-system-tables)
11. [Relationships & Foreign Keys](#11-relationships--foreign-keys)
12. [Indexes & Performance](#12-indexes--performance)

---

## 1. OVERVIEW DATABASE

### Database Information

- **Nama Database:** pembda_hub
- **Character Set:** utf8mb4_unicode_ci
- **Engine:** InnoDB
- **Total Tables:** 62 tables

### Table Categories

- **User & Auth:** 5 tables (users, roles, permissions, dll)
- **Master Data:** 12 tables (schools, subjects, classrooms, dll)
- **Students:** 8 tables (students, student_classes, dll)
- **Schedule:** 6 tables (schedules, timeslots, dll)
- **PSB:** 4 tables (applicants, gelombangs, dll)
- **Financial:** 10 tables (bills, payments, dll)
- **Assessment:** 8 tables (grades, assessments, dll)
- **Notifications:** 3 tables (notifications, notification_logs, dll)
- **System:** 6 tables (migrations, jobs, dll)

---

## 2. USER & AUTHENTICATION TABLES

### 2.1 `users`

**Deskripsi:** Tabel utama untuk semua pengguna sistem (admin, guru, bendahara, wali kelas, orang tua)

| Column            | Type                                                                         | Null | Default        | Description                   |
| ----------------- | ---------------------------------------------------------------------------- | ---- | -------------- | ----------------------------- |
| id                | bigint(20) unsigned                                                          | NO   | AUTO_INCREMENT | Primary key                   |
| name              | varchar(255)                                                                 | NO   | -              | Nama lengkap pengguna         |
| email             | varchar(255)                                                                 | NO   | -              | Email (unique)                |
| email_verified_at | timestamp                                                                    | YES  | NULL           | Waktu verifikasi email        |
| password          | varchar(255)                                                                 | NO   | -              | Password (hashed)             |
| role              | enum('super_admin', 'admin', 'bendahara', 'guru', 'wali_kelas', 'orang_tua') | NO   | 'guru'         | Role pengguna                 |
| school_id         | bigint(20) unsigned                                                          | YES  | NULL           | ID sekolah (FK ke schools)    |
| employee_id       | bigint(20) unsigned                                                          | YES  | NULL           | ID employee (FK ke employees) |
| remember_token    | varchar(100)                                                                 | YES  | NULL           | Token remember me             |
| created_at        | timestamp                                                                    | YES  | NULL           | Waktu dibuat                  |
| updated_at        | timestamp                                                                    | YES  | NULL           | Waktu diupdate                |

**Indexes:**

- PRIMARY KEY: `id`
- UNIQUE KEY: `email`
- KEY: `school_id`, `employee_id`

**Relationships:**

- BelongsTo: `schools`, `employees`

**Business Rules:**

- Email harus unique
- Super Admin tidak terikat sekolah (school_id = NULL)
- Admin/Bendahara terikat dengan 1 sekolah
- Guru/Wali Kelas harus memiliki employee_id
- Orang Tua terikat dengan student (melalui table lain)

---

### 2.2 `password_reset_tokens`

**Deskripsi:** Token untuk reset password

| Column     | Type         | Null | Default | Description                  |
| ---------- | ------------ | ---- | ------- | ---------------------------- |
| email      | varchar(255) | NO   | -       | Email pengguna (primary key) |
| token      | varchar(255) | NO   | -       | Token reset                  |
| created_at | timestamp    | YES  | NULL    | Waktu dibuat                 |

**Indexes:**

- PRIMARY KEY: `email`

---

### 2.3 `sessions`

**Deskripsi:** Session management (Laravel sessions)

| Column        | Type                | Null | Default | Description              |
| ------------- | ------------------- | ---- | ------- | ------------------------ |
| id            | varchar(255)        | NO   | -       | Session ID (primary key) |
| user_id       | bigint(20) unsigned | YES  | NULL    | ID user                  |
| ip_address    | varchar(45)         | YES  | NULL    | IP address               |
| user_agent    | text                | YES  | NULL    | User agent               |
| payload       | longtext            | NO   | -       | Session payload          |
| last_activity | int(11)             | NO   | -       | Last activity timestamp  |

**Indexes:**

- PRIMARY KEY: `id`
- KEY: `user_id`, `last_activity`

---

## 3. MASTER DATA TABLES

### 3.1 `schools`

**Deskripsi:** Data sekolah (3 sekolah dalam yayasan)

| Column     | Type                            | Null | Default        | Description         |
| ---------- | ------------------------------- | ---- | -------------- | ------------------- |
| id         | bigint(20) unsigned             | NO   | AUTO_INCREMENT | Primary key         |
| name       | varchar(255)                    | NO   | -              | Nama sekolah        |
| short_name | varchar(50)                     | YES  | NULL           | Nama singkat        |
| level      | enum('SD', 'SMP', 'SMA', 'SMK') | NO   | -              | Jenjang             |
| npsn       | varchar(20)                     | YES  | NULL           | NPSN                |
| address    | text                            | YES  | NULL           | Alamat lengkap      |
| phone      | varchar(20)                     | YES  | NULL           | Nomor telepon       |
| email      | varchar(100)                    | YES  | NULL           | Email sekolah       |
| principal  | varchar(100)                    | YES  | NULL           | Nama kepala sekolah |
| created_at | timestamp                       | YES  | NULL           | Waktu dibuat        |
| updated_at | timestamp                       | YES  | NULL           | Waktu diupdate      |

**Data:**

- ID 1: SMPS Pembda 2 (SMP)
- ID 2: SMA Pembda 1 (SMA)
- ID 3: SMKS Pembda Nias (SMK)

**Indexes:**

- PRIMARY KEY: `id`
- KEY: `npsn`

---

### 3.2 `academic_years`

**Deskripsi:** Tahun ajaran

| Column     | Type                | Null | Default        | Description                           |
| ---------- | ------------------- | ---- | -------------- | ------------------------------------- |
| id         | bigint(20) unsigned | NO   | AUTO_INCREMENT | Primary key                           |
| name       | varchar(255)        | NO   | -              | Nama tahun ajaran (contoh: 2025/2026) |
| start_date | date                | NO   | -              | Tanggal mulai                         |
| end_date   | date                | NO   | -              | Tanggal berakhir                      |
| is_active  | tinyint(1)          | NO   | 0              | Status aktif (0/1)                    |
| created_at | timestamp           | YES  | NULL           | Waktu dibuat                          |
| updated_at | timestamp           | YES  | NULL           | Waktu diupdate                        |

**Indexes:**

- PRIMARY KEY: `id`
- KEY: `is_active`

**Business Rules:**

- Hanya 1 tahun ajaran yang boleh is_active = 1
- Format name: YYYY/YYYY (contoh: 2025/2026)

---

### 3.3 `classrooms`

**Deskripsi:** Data kelas (ruangan kelas per sekolah)

| Column              | Type                | Null | Default        | Description                                           |
| ------------------- | ------------------- | ---- | -------------- | ----------------------------------------------------- |
| id                  | bigint(20) unsigned | NO   | AUTO_INCREMENT | Primary key                                           |
| school_id           | bigint(20) unsigned | NO   | -              | ID sekolah (FK ke schools)                            |
| academic_year_id    | bigint(20) unsigned | NO   | -              | ID tahun ajaran (FK ke academic_years)                |
| grade               | int(11)             | NO   | -              | Tingkat kelas (7-9 untuk SMP, 10-12 untuk SMA/SMK)    |
| class_name          | varchar(50)         | NO   | -              | Nama kelas (contoh: A, B, C)                          |
| major               | varchar(100)        | YES  | NULL           | Jurusan (untuk SMA: IPA/IPS, untuk SMK: nama program) |
| homeroom_teacher_id | bigint(20) unsigned | YES  | NULL           | ID wali kelas (FK ke employees)                       |
| max_students        | int(11)             | YES  | 40             | Kapasitas maksimal siswa                              |
| created_at          | timestamp           | YES  | NULL           | Waktu dibuat                                          |
| updated_at          | timestamp           | YES  | NULL           | Waktu diupdate                                        |

**Indexes:**

- PRIMARY KEY: `id`
- KEY: `school_id`, `academic_year_id`, `homeroom_teacher_id`

**Business Rules:**

- Kombinasi school_id + academic_year_id + grade + class_name harus unique
- grade untuk SMP: 7, 8, 9
- grade untuk SMA/SMK: 10, 11, 12

---

### 3.4 `subjects`

**Deskripsi:** Mata pelajaran

| Column      | Type                                     | Null | Default        | Description                |
| ----------- | ---------------------------------------- | ---- | -------------- | -------------------------- |
| id          | bigint(20) unsigned                      | NO   | AUTO_INCREMENT | Primary key                |
| school_id   | bigint(20) unsigned                      | NO   | -              | ID sekolah (FK ke schools) |
| code        | varchar(20)                              | NO   | -              | Kode mata pelajaran        |
| name        | varchar(255)                             | NO   | -              | Nama mata pelajaran        |
| category    | enum('Umum', 'Kejuruan', 'Muatan Lokal') | NO   | 'Umum'         | Kategori                   |
| description | text                                     | YES  | NULL           | Deskripsi                  |
| created_at  | timestamp                                | YES  | NULL           | Waktu dibuat               |
| updated_at  | timestamp                                | YES  | NULL           | Waktu diupdate             |

**Indexes:**

- PRIMARY KEY: `id`
- UNIQUE KEY: `school_id + code`

**Business Rules:**

- Kode mata pelajaran harus unique per sekolah
- Kategori 'Kejuruan' hanya untuk SMK

---

### 3.5 `program_keahlians`

**Deskripsi:** Program keahlian untuk SMK (level 1)

| Column      | Type                | Null | Default        | Description                |
| ----------- | ------------------- | ---- | -------------- | -------------------------- |
| id          | bigint(20) unsigned | NO   | AUTO_INCREMENT | Primary key                |
| school_id   | bigint(20) unsigned | NO   | -              | ID sekolah (FK ke schools) |
| code        | varchar(20)         | NO   | -              | Kode program               |
| name        | varchar(255)        | NO   | -              | Nama program keahlian      |
| description | text                | YES  | NULL           | Deskripsi                  |
| is_active   | tinyint(1)          | NO   | 1              | Status aktif (0/1)         |
| created_at  | timestamp           | YES  | NULL           | Waktu dibuat               |
| updated_at  | timestamp           | YES  | NULL           | Waktu diupdate             |

**Data Contoh:**

- Teknik Komputer dan Informatika
- Teknik Otomotif
- Tata Busana

**Indexes:**

- PRIMARY KEY: `id`
- KEY: `school_id`

---

### 3.6 `konsentrasi_keahlians`

**Deskripsi:** Konsentrasi keahlian untuk SMK (level 2, detail dari program keahlian)

| Column              | Type                | Null | Default        | Description                                   |
| ------------------- | ------------------- | ---- | -------------- | --------------------------------------------- |
| id                  | bigint(20) unsigned | NO   | AUTO_INCREMENT | Primary key                                   |
| program_keahlian_id | bigint(20) unsigned | NO   | -              | ID program keahlian (FK ke program_keahlians) |
| code                | varchar(20)         | NO   | -              | Kode konsentrasi                              |
| name                | varchar(255)        | NO   | -              | Nama konsentrasi keahlian                     |
| description         | text                | YES  | NULL           | Deskripsi                                     |
| is_active           | tinyint(1)          | NO   | 1              | Status aktif (0/1)                            |
| created_at          | timestamp           | YES  | NULL           | Waktu dibuat                                  |
| updated_at          | timestamp           | YES  | NULL           | Waktu diupdate                                |

**Data Contoh (untuk Teknik Komputer dan Informatika):**

- Teknik Komputer dan Jaringan (TKJ)
- Multimedia (MM)
- Rekayasa Perangkat Lunak (RPL)

**Indexes:**

- PRIMARY KEY: `id`
- KEY: `program_keahlian_id`

**Relationships:**

- BelongsTo: `program_keahlians`

---

### 3.7 `employees`

**Deskripsi:** Data pegawai/guru

| Column          | Type                                         | Null | Default        | Description                |
| --------------- | -------------------------------------------- | ---- | -------------- | -------------------------- |
| id              | bigint(20) unsigned                          | NO   | AUTO_INCREMENT | Primary key                |
| school_id       | bigint(20) unsigned                          | NO   | -              | ID sekolah (FK ke schools) |
| nip             | varchar(30)                                  | YES  | NULL           | NIP                        |
| nik             | varchar(20)                                  | YES  | NULL           | NIK                        |
| name            | varchar(255)                                 | NO   | -              | Nama lengkap               |
| gender          | enum('L', 'P')                               | NO   | -              | Jenis kelamin              |
| birth_place     | varchar(100)                                 | YES  | NULL           | Tempat lahir               |
| birth_date      | date                                         | YES  | NULL           | Tanggal lahir              |
| phone           | varchar(20)                                  | YES  | NULL           | Nomor HP                   |
| email           | varchar(100)                                 | YES  | NULL           | Email                      |
| address         | text                                         | YES  | NULL           | Alamat                     |
| position        | varchar(100)                                 | YES  | NULL           | Jabatan                    |
| employment_type | enum('PNS', 'PPPK', 'GTY', 'PTY', 'Honorer') | YES  | NULL           | Status kepegawaian         |
| join_date       | date                                         | YES  | NULL           | Tanggal bergabung          |
| photo           | varchar(255)                                 | YES  | NULL           | Path foto                  |
| is_active       | tinyint(1)                                   | NO   | 1              | Status aktif               |
| created_at      | timestamp                                    | YES  | NULL           | Waktu dibuat               |
| updated_at      | timestamp                                    | YES  | NULL           | Waktu diupdate             |

**Indexes:**

- PRIMARY KEY: `id`
- KEY: `school_id`, `nip`

---

### 3.8 `timeslots`

**Deskripsi:** Slot waktu untuk jadwal pelajaran

| Column      | Type                                                       | Null | Default        | Description                |
| ----------- | ---------------------------------------------------------- | ---- | -------------- | -------------------------- |
| id          | bigint(20) unsigned                                        | NO   | AUTO_INCREMENT | Primary key                |
| school_id   | bigint(20) unsigned                                        | NO   | -              | ID sekolah (FK ke schools) |
| day         | enum('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu') | NO   | -              | Hari                       |
| start_time  | time                                                       | NO   | -              | Jam mulai                  |
| end_time    | time                                                       | NO   | -              | Jam selesai                |
| slot_number | int(11)                                                    | NO   | -              | Nomor slot (1-10)          |
| is_break    | tinyint(1)                                                 | NO   | 0              | Apakah waktu istirahat     |
| created_at  | timestamp                                                  | YES  | NULL           | Waktu dibuat               |
| updated_at  | timestamp                                                  | YES  | NULL           | Waktu diupdate             |

**Indexes:**

- PRIMARY KEY: `id`
- KEY: `school_id`, `day`

**Business Rules:**

- Kombinasi school_id + day + slot_number harus unique
- is_break = 1 untuk waktu istirahat/sholat
- Slot non-break digunakan untuk jadwal pelajaran

---

## 4. STUDENT MANAGEMENT TABLES

### 4.1 `students`

**Deskripsi:** Data siswa

| Column          | Type                | Null | Default        | Description                    |
| --------------- | ------------------- | ---- | -------------- | ------------------------------ |
| id              | bigint(20) unsigned | NO   | AUTO_INCREMENT | Primary key                    |
| school_id       | bigint(20) unsigned | NO   | -              | ID sekolah (FK ke schools)     |
| nisn            | varchar(20)         | YES  | NULL           | NISN                           |
| nis             | varchar(20)         | YES  | NULL           | NIS (unique per sekolah)       |
| name            | varchar(255)        | NO   | -              | Nama lengkap                   |
| gender          | enum('L', 'P')      | NO   | -              | Jenis kelamin                  |
| birth_place     | varchar(100)        | YES  | NULL           | Tempat lahir                   |
| birth_date      | date                | YES  | NULL           | Tanggal lahir                  |
| religion        | varchar(50)         | YES  | NULL           | Agama                          |
| address         | text                | YES  | NULL           | Alamat                         |
| phone           | varchar(20)         | YES  | NULL           | Nomor HP siswa                 |
| parent_name     | varchar(255)        | YES  | NULL           | Nama orang tua/wali            |
| parent_phone    | varchar(20)         | YES  | NULL           | Nomor HP orang tua             |
| parent_email    | varchar(100)        | YES  | NULL           | Email orang tua                |
| entry_year      | year(4)             | YES  | NULL           | Tahun masuk                    |
| photo           | varchar(255)        | YES  | NULL           | Path foto                      |
| is_active       | tinyint(1)          | NO   | 1              | Status aktif                   |
| graduation_year | year(4)             | YES  | NULL           | Tahun lulus (jika sudah lulus) |
| created_at      | timestamp           | YES  | NULL           | Waktu dibuat                   |
| updated_at      | timestamp           | YES  | NULL           | Waktu diupdate                 |

**Indexes:**

- PRIMARY KEY: `id`
- KEY: `school_id`, `nisn`, `nis`

**Business Rules:**

- NIS harus unique per sekolah
- NISN harus unique secara nasional

---

### 4.2 `student_classes`

**Deskripsi:** Relasi siswa dengan kelas (siswa bisa pindah kelas tiap tahun ajaran)

| Column           | Type                                                     | Null | Default        | Description                            |
| ---------------- | -------------------------------------------------------- | ---- | -------------- | -------------------------------------- |
| id               | bigint(20) unsigned                                      | NO   | AUTO_INCREMENT | Primary key                            |
| student_id       | bigint(20) unsigned                                      | NO   | -              | ID siswa (FK ke students)              |
| classroom_id     | bigint(20) unsigned                                      | NO   | -              | ID kelas (FK ke classrooms)            |
| academic_year_id | bigint(20) unsigned                                      | NO   | -              | ID tahun ajaran (FK ke academic_years) |
| status           | enum('Aktif', 'Lulus', 'Pindah', 'Keluar', 'Naik Kelas') | NO   | 'Aktif'        | Status                                 |
| created_at       | timestamp                                                | YES  | NULL           | Waktu dibuat                           |
| updated_at       | timestamp                                                | YES  | NULL           | Waktu diupdate                         |

**Indexes:**

- PRIMARY KEY: `id`
- UNIQUE KEY: `student_id + academic_year_id` (siswa hanya boleh 1 kelas per tahun ajaran)
- KEY: `classroom_id`

**Business Rules:**

- Siswa hanya boleh terdaftar di 1 kelas per tahun ajaran
- Status 'Lulus' untuk siswa kelas 9/12
- Status 'Naik Kelas' otomatis saat tahun ajaran baru

---

## 5. SCHEDULE MANAGEMENT TABLES

### 5.1 `schedules`

**Deskripsi:** Jadwal pelajaran

| Column           | Type                | Null | Default        | Description                            |
| ---------------- | ------------------- | ---- | -------------- | -------------------------------------- |
| id               | bigint(20) unsigned | NO   | AUTO_INCREMENT | Primary key                            |
| classroom_id     | bigint(20) unsigned | NO   | -              | ID kelas (FK ke classrooms)            |
| subject_id       | bigint(20) unsigned | NO   | -              | ID mata pelajaran (FK ke subjects)     |
| employee_id      | bigint(20) unsigned | NO   | -              | ID guru (FK ke employees)              |
| timeslot_id      | bigint(20) unsigned | NO   | -              | ID timeslot (FK ke timeslots)          |
| academic_year_id | bigint(20) unsigned | NO   | -              | ID tahun ajaran (FK ke academic_years) |
| created_at       | timestamp           | YES  | NULL           | Waktu dibuat                           |
| updated_at       | timestamp           | YES  | NULL           | Waktu diupdate                         |

**Indexes:**

- PRIMARY KEY: `id`
- KEY: `classroom_id`, `subject_id`, `employee_id`, `timeslot_id`

**Business Rules:**

- Kombinasi classroom_id + timeslot_id harus unique (tidak boleh bentrok)
- Kombinasi employee_id + timeslot_id harus unique (guru tidak bisa mengajar 2 kelas bersamaan)

---

### 5.2 `teacher_competencies`

**Deskripsi:** Kompetensi guru (mata pelajaran apa saja yang bisa diajar)

| Column           | Type                      | Null | Default        | Description                        |
| ---------------- | ------------------------- | ---- | -------------- | ---------------------------------- |
| id               | bigint(20) unsigned       | NO   | AUTO_INCREMENT | Primary key                        |
| employee_id      | bigint(20) unsigned       | NO   | -              | ID guru (FK ke employees)          |
| subject_id       | bigint(20) unsigned       | NO   | -              | ID mata pelajaran (FK ke subjects) |
| competency_level | enum('Utama', 'Tambahan') | NO   | 'Utama'        | Level kompetensi                   |
| created_at       | timestamp                 | YES  | NULL           | Waktu dibuat                       |
| updated_at       | timestamp                 | YES  | NULL           | Waktu diupdate                     |

**Indexes:**

- PRIMARY KEY: `id`
- UNIQUE KEY: `employee_id + subject_id`

**Business Rules:**

- Guru bisa memiliki banyak kompetensi
- 'Utama' adalah mata pelajaran utama yang diajar
- 'Tambahan' adalah mata pelajaran cadangan

---

## 6. PSB TABLES

### 6.1 `applicants`

**Deskripsi:** Data pendaftar PSB

| Column                  | Type                                               | Null | Default        | Description                                                        |
| ----------------------- | -------------------------------------------------- | ---- | -------------- | ------------------------------------------------------------------ |
| id                      | bigint(20) unsigned                                | NO   | AUTO_INCREMENT | Primary key                                                        |
| school_id               | bigint(20) unsigned                                | NO   | -              | ID sekolah tujuan (FK ke schools)                                  |
| academic_year_id        | bigint(20) unsigned                                | NO   | -              | ID tahun ajaran (FK ke academic_years)                             |
| gelombang_id            | bigint(20) unsigned                                | YES  | NULL           | ID gelombang (FK ke gelombangs)                                    |
| registration_number     | varchar(50)                                        | NO   | -              | Nomor pendaftaran (unique)                                         |
| registration_type       | enum('Baru', 'Pindahan')                           | NO   | 'Baru'         | Jenis pendaftaran                                                  |
| nisn                    | varchar(20)                                        | YES  | NULL           | NISN calon siswa                                                   |
| name                    | varchar(255)                                       | NO   | -              | Nama lengkap                                                       |
| gender                  | enum('L', 'P')                                     | NO   | -              | Jenis kelamin                                                      |
| birth_place             | varchar(100)                                       | YES  | NULL           | Tempat lahir                                                       |
| birth_date              | date                                               | YES  | NULL           | Tanggal lahir                                                      |
| religion                | varchar(50)                                        | YES  | NULL           | Agama                                                              |
| address                 | text                                               | YES  | NULL           | Alamat                                                             |
| phone                   | varchar(20)                                        | YES  | NULL           | Nomor HP                                                           |
| previous_school         | varchar(255)                                       | YES  | NULL           | Asal sekolah                                                       |
| parent_name             | varchar(255)                                       | YES  | NULL           | Nama orang tua/wali                                                |
| parent_phone            | varchar(20)                                        | YES  | NULL           | Nomor HP orang tua                                                 |
| parent_email            | varchar(100)                                       | YES  | NULL           | Email orang tua                                                    |
| program_keahlian_id     | bigint(20) unsigned                                | YES  | NULL           | ID program keahlian (FK ke program_keahlians) - Khusus SMK         |
| konsentrasi_keahlian_id | bigint(20) unsigned                                | YES  | NULL           | ID konsentrasi keahlian (FK ke konsentrasi_keahlians) - Khusus SMK |
| photo                   | varchar(255)                                       | YES  | NULL           | Path foto                                                          |
| documents               | json                                               | YES  | NULL           | Dokumen pendukung (JSON)                                           |
| status                  | enum('Pending', 'Diterima', 'Ditolak', 'Cadangan') | NO   | 'Pending'      | Status pendaftaran                                                 |
| notes                   | text                                               | YES  | NULL           | Catatan                                                            |
| created_at              | timestamp                                          | YES  | NULL           | Waktu dibuat                                                       |
| updated_at              | timestamp                                          | YES  | NULL           | Waktu diupdate                                                     |

**Indexes:**

- PRIMARY KEY: `id`
- UNIQUE KEY: `registration_number`
- KEY: `school_id`, `academic_year_id`, `gelombang_id`, `program_keahlian_id`, `konsentrasi_keahlian_id`

**Business Rules:**

- registration_number format: PSB-{SCHOOL_CODE}-{YEAR}-{SEQUENCE}
- program_keahlian_id dan konsentrasi_keahlian_id WAJIB jika school_id = 3 (SMK)
- program_keahlian_id dan konsentrasi_keahlian_id NULL jika school_id != 3

---

### 6.2 `gelombangs`

**Deskripsi:** Gelombang/periode pendaftaran PSB

| Column           | Type                | Null | Default        | Description                            |
| ---------------- | ------------------- | ---- | -------------- | -------------------------------------- |
| id               | bigint(20) unsigned | NO   | AUTO_INCREMENT | Primary key                            |
| school_id        | bigint(20) unsigned | NO   | -              | ID sekolah (FK ke schools)             |
| academic_year_id | bigint(20) unsigned | NO   | -              | ID tahun ajaran (FK ke academic_years) |
| name             | varchar(100)        | NO   | -              | Nama gelombang (contoh: Gelombang 1)   |
| start_date       | date                | NO   | -              | Tanggal mulai pendaftaran              |
| end_date         | date                | NO   | -              | Tanggal akhir pendaftaran              |
| quota            | int(11)             | NO   | -              | Kuota pendaftar                        |
| registration_fee | decimal(15,2)       | NO   | 0.00           | Biaya pendaftaran                      |
| is_active        | tinyint(1)          | NO   | 0              | Status aktif                           |
| created_at       | timestamp           | YES  | NULL           | Waktu dibuat                           |
| updated_at       | timestamp           | YES  | NULL           | Waktu diupdate                         |

**Indexes:**

- PRIMARY KEY: `id`
- KEY: `school_id`, `academic_year_id`

**Business Rules:**

- Hanya 1 gelombang yang boleh is_active = 1 per sekolah per tahun ajaran
- Biaya pendaftaran:
    - SMP & SMA: Rp 50.000
    - SMK: Rp 50.000 + Rp 250.000 (peralatan praktik) = Rp 300.000

---

## 7. FINANCIAL MANAGEMENT TABLES

### 7.1 `bills`

**Deskripsi:** Tagihan pembayaran siswa

| Column           | Type                                                                | Null | Default        | Description                            |
| ---------------- | ------------------------------------------------------------------- | ---- | -------------- | -------------------------------------- |
| id               | bigint(20) unsigned                                                 | NO   | AUTO_INCREMENT | Primary key                            |
| student_id       | bigint(20) unsigned                                                 | NO   | -              | ID siswa (FK ke students)              |
| academic_year_id | bigint(20) unsigned                                                 | NO   | -              | ID tahun ajaran (FK ke academic_years) |
| bill_type        | enum('SPP', 'UPP', 'DPP', 'Seragam', 'Buku', 'Kegiatan', 'Lainnya') | NO   | -              | Jenis tagihan                          |
| bill_number      | varchar(50)                                                         | NO   | -              | Nomor tagihan (unique)                 |
| amount           | decimal(15,2)                                                       | NO   | -              | Jumlah tagihan                         |
| due_date         | date                                                                | NO   | -              | Tanggal jatuh tempo                    |
| status           | enum('Belum Lunas', 'Cicilan', 'Lunas', 'Overdue')                  | NO   | 'Belum Lunas'  | Status pembayaran                      |
| paid_amount      | decimal(15,2)                                                       | NO   | 0.00           | Jumlah yang sudah dibayar              |
| notes            | text                                                                | YES  | NULL           | Catatan                                |
| created_at       | timestamp                                                           | YES  | NULL           | Waktu dibuat                           |
| updated_at       | timestamp                                                           | YES  | NULL           | Waktu diupdate                         |

**Indexes:**

- PRIMARY KEY: `id`
- UNIQUE KEY: `bill_number`
- KEY: `student_id`, `academic_year_id`, `status`

**Business Rules:**

- bill_number format: BILL-{SCHOOL_CODE}-{YEAR}-{SEQUENCE}
- status 'Lunas' jika paid_amount >= amount
- status 'Cicilan' jika paid_amount > 0 dan < amount
- status 'Overdue' jika due_date < today dan status != 'Lunas'

---

### 7.2 `payments`

**Deskripsi:** Riwayat pembayaran

| Column         | Type                                         | Null | Default        | Description                             |
| -------------- | -------------------------------------------- | ---- | -------------- | --------------------------------------- |
| id             | bigint(20) unsigned                          | NO   | AUTO_INCREMENT | Primary key                             |
| bill_id        | bigint(20) unsigned                          | NO   | -              | ID tagihan (FK ke bills)                |
| payment_number | varchar(50)                                  | NO   | -              | Nomor pembayaran (unique)               |
| payment_date   | date                                         | NO   | -              | Tanggal pembayaran                      |
| amount         | decimal(15,2)                                | NO   | -              | Jumlah dibayar                          |
| payment_method | enum('Tunai', 'Transfer', 'QRIS', 'Lainnya') | NO   | 'Tunai'        | Metode pembayaran                       |
| receipt_number | varchar(50)                                  | YES  | NULL           | Nomor kwitansi                          |
| processed_by   | bigint(20) unsigned                          | YES  | NULL           | Diproses oleh (FK ke users - bendahara) |
| notes          | text                                         | YES  | NULL           | Catatan                                 |
| created_at     | timestamp                                    | YES  | NULL           | Waktu dibuat                            |
| updated_at     | timestamp                                    | YES  | NULL           | Waktu diupdate                          |

**Indexes:**

- PRIMARY KEY: `id`
- UNIQUE KEY: `payment_number`
- KEY: `bill_id`, `processed_by`

**Business Rules:**

- payment_number format: PAY-{SCHOOL_CODE}-{YEAR}-{SEQUENCE}
- receipt_number diisi oleh bendahara setelah mencetak kwitansi

---

### 7.3 `position_allowances`

**Deskripsi:** Tunjangan jabatan untuk pegawai

| Column           | Type                | Null | Default        | Description                  |
| ---------------- | ------------------- | ---- | -------------- | ---------------------------- |
| id               | bigint(20) unsigned | NO   | AUTO_INCREMENT | Primary key                  |
| employee_id      | bigint(20) unsigned | NO   | -              | ID pegawai (FK ke employees) |
| position         | varchar(100)        | NO   | -              | Nama jabatan                 |
| allowance_amount | decimal(15,2)       | NO   | -              | Jumlah tunjangan             |
| effective_date   | date                | NO   | -              | Tanggal efektif              |
| end_date         | date                | YES  | NULL           | Tanggal berakhir             |
| notes            | text                | YES  | NULL           | Catatan                      |
| created_at       | timestamp           | YES  | NULL           | Waktu dibuat                 |
| updated_at       | timestamp           | YES  | NULL           | Waktu diupdate               |

**Indexes:**

- PRIMARY KEY: `id`
- KEY: `employee_id`

---

## 8. ASSESSMENT TABLES

### 8.1 `assessments`

**Deskripsi:** Master penilaian (UH, UTS, UAS, dll)

| Column           | Type                                                     | Null | Default        | Description                            |
| ---------------- | -------------------------------------------------------- | ---- | -------------- | -------------------------------------- |
| id               | bigint(20) unsigned                                      | NO   | AUTO_INCREMENT | Primary key                            |
| academic_year_id | bigint(20) unsigned                                      | NO   | -              | ID tahun ajaran (FK ke academic_years) |
| school_id        | bigint(20) unsigned                                      | NO   | -              | ID sekolah (FK ke schools)             |
| name             | varchar(255)                                             | NO   | -              | Nama penilaian                         |
| type             | enum('UH', 'UTS', 'UAS', 'Praktik', 'Projek', 'Lainnya') | NO   | -              | Jenis penilaian                        |
| weight           | decimal(5,2)                                             | NO   | -              | Bobot (%)                              |
| start_date       | date                                                     | YES  | NULL           | Tanggal mulai                          |
| end_date         | date                                                     | YES  | NULL           | Tanggal selesai                        |
| created_at       | timestamp                                                | YES  | NULL           | Waktu dibuat                           |
| updated_at       | timestamp                                                | YES  | NULL           | Waktu diupdate                         |

**Indexes:**

- PRIMARY KEY: `id`
- KEY: `academic_year_id`, `school_id`

---

### 8.2 `grades`

**Deskripsi:** Nilai siswa

| Column           | Type                | Null | Default        | Description                            |
| ---------------- | ------------------- | ---- | -------------- | -------------------------------------- |
| id               | bigint(20) unsigned | NO   | AUTO_INCREMENT | Primary key                            |
| student_id       | bigint(20) unsigned | NO   | -              | ID siswa (FK ke students)              |
| subject_id       | bigint(20) unsigned | NO   | -              | ID mata pelajaran (FK ke subjects)     |
| assessment_id    | bigint(20) unsigned | NO   | -              | ID penilaian (FK ke assessments)       |
| academic_year_id | bigint(20) unsigned | NO   | -              | ID tahun ajaran (FK ke academic_years) |
| score            | decimal(5,2)        | NO   | -              | Nilai (0-100)                          |
| grade            | varchar(2)          | YES  | NULL           | Huruf (A, B, C, D, E)                  |
| notes            | text                | YES  | NULL           | Catatan guru                           |
| created_at       | timestamp           | YES  | NULL           | Waktu dibuat                           |
| updated_at       | timestamp           | YES  | NULL           | Waktu diupdate                         |

**Indexes:**

- PRIMARY KEY: `id`
- UNIQUE KEY: `student_id + subject_id + assessment_id + academic_year_id`

**Business Rules:**

- Nilai 0-100
- Grade otomatis berdasarkan score:
    - A: 90-100
    - B: 80-89
    - C: 70-79
    - D: 60-69
    - E: < 60

---

## 9. NOTIFICATION TABLES

### 9.1 `notifications`

**Deskripsi:** Notifikasi sistem

| Column     | Type                | Null | Default        | Description                    |
| ---------- | ------------------- | ---- | -------------- | ------------------------------ |
| id         | bigint(20) unsigned | NO   | AUTO_INCREMENT | Primary key                    |
| user_id    | bigint(20) unsigned | NO   | -              | ID user penerima (FK ke users) |
| type       | varchar(100)        | NO   | -              | Tipe notifikasi                |
| title      | varchar(255)        | NO   | -              | Judul                          |
| message    | text                | NO   | -              | Pesan                          |
| data       | json                | YES  | NULL           | Data tambahan (JSON)           |
| read_at    | timestamp           | YES  | NULL           | Waktu dibaca                   |
| created_at | timestamp           | YES  | NULL           | Waktu dibuat                   |
| updated_at | timestamp           | YES  | NULL           | Waktu diupdate                 |

**Indexes:**

- PRIMARY KEY: `id`
- KEY: `user_id`, `read_at`

---

### 9.2 `notification_logs`

**Deskripsi:** Log pengiriman notifikasi (WhatsApp, Email, dll)

| Column          | Type                              | Null | Default        | Description                         |
| --------------- | --------------------------------- | ---- | -------------- | ----------------------------------- |
| id              | bigint(20) unsigned               | NO   | AUTO_INCREMENT | Primary key                         |
| notification_id | bigint(20) unsigned               | YES  | NULL           | ID notifikasi (FK ke notifications) |
| channel         | enum('whatsapp', 'email', 'sms')  | NO   | -              | Channel pengiriman                  |
| recipient       | varchar(255)                      | NO   | -              | Nomor/email penerima                |
| message         | text                              | NO   | -              | Pesan yang dikirim                  |
| status          | enum('pending', 'sent', 'failed') | NO   | 'pending'      | Status pengiriman                   |
| response        | text                              | YES  | NULL           | Response dari API                   |
| sent_at         | timestamp                         | YES  | NULL           | Waktu terkirim                      |
| created_at      | timestamp                         | YES  | NULL           | Waktu dibuat                        |
| updated_at      | timestamp                         | YES  | NULL           | Waktu diupdate                      |

**Indexes:**

- PRIMARY KEY: `id`
- KEY: `notification_id`, `channel`, `status`

---

## 10. SYSTEM TABLES

### 10.1 `migrations`

**Deskripsi:** Riwayat migrasi database

| Column    | Type             | Null | Default        | Description       |
| --------- | ---------------- | ---- | -------------- | ----------------- |
| id        | int(10) unsigned | NO   | AUTO_INCREMENT | Primary key       |
| migration | varchar(255)     | NO   | -              | Nama file migrasi |
| batch     | int(11)          | NO   | -              | Batch number      |

**Indexes:**

- PRIMARY KEY: `id`

---

### 10.2 `jobs`

**Deskripsi:** Queue jobs Laravel

| Column       | Type                | Null | Default        | Description      |
| ------------ | ------------------- | ---- | -------------- | ---------------- |
| id           | bigint(20) unsigned | NO   | AUTO_INCREMENT | Primary key      |
| queue        | varchar(255)        | NO   | -              | Nama queue       |
| payload      | longtext            | NO   | -              | Job payload      |
| attempts     | tinyint(3) unsigned | NO   | -              | Jumlah percobaan |
| reserved_at  | int(10) unsigned    | YES  | NULL           | Waktu reserved   |
| available_at | int(10) unsigned    | NO   | -              | Waktu tersedia   |
| created_at   | int(10) unsigned    | NO   | -              | Waktu dibuat     |

**Indexes:**

- PRIMARY KEY: `id`
- KEY: `queue`

---

### 10.3 `failed_jobs`

**Deskripsi:** Queue jobs yang gagal

| Column     | Type                | Null | Default           | Description       |
| ---------- | ------------------- | ---- | ----------------- | ----------------- |
| id         | bigint(20) unsigned | NO   | AUTO_INCREMENT    | Primary key       |
| uuid       | varchar(255)        | NO   | -                 | UUID (unique)     |
| connection | text                | NO   | -                 | Connection        |
| queue      | text                | NO   | -                 | Queue name        |
| payload    | longtext            | NO   | -                 | Job payload       |
| exception  | longtext            | NO   | -                 | Exception message |
| failed_at  | timestamp           | NO   | CURRENT_TIMESTAMP | Waktu gagal       |

**Indexes:**

- PRIMARY KEY: `id`
- UNIQUE KEY: `uuid`

---

### 10.4 `cache`

**Deskripsi:** Cache Laravel

| Column     | Type         | Null | Default | Description             |
| ---------- | ------------ | ---- | ------- | ----------------------- |
| key        | varchar(255) | NO   | -       | Cache key (primary key) |
| value      | mediumtext   | NO   | -       | Cache value             |
| expiration | int(11)      | NO   | -       | Waktu kadaluarsa        |

**Indexes:**

- PRIMARY KEY: `key`

---

### 10.5 `cache_locks`

**Deskripsi:** Cache locks Laravel

| Column     | Type         | Null | Default | Description            |
| ---------- | ------------ | ---- | ------- | ---------------------- |
| key        | varchar(255) | NO   | -       | Lock key (primary key) |
| owner      | varchar(255) | NO   | -       | Owner                  |
| expiration | int(11)      | NO   | -       | Waktu kadaluarsa       |

**Indexes:**

- PRIMARY KEY: `key`

---

## 11. RELATIONSHIPS & FOREIGN KEYS

### User & Authentication

```
users
├── BelongsTo: schools (school_id)
├── BelongsTo: employees (employee_id)
└── HasMany: notifications
```

### Master Data

```
schools
├── HasMany: classrooms
├── HasMany: subjects
├── HasMany: employees
├── HasMany: students
├── HasMany: program_keahlians
├── HasMany: gelombangs
└── HasMany: applicants

academic_years
├── HasMany: classrooms
├── HasMany: student_classes
├── HasMany: schedules
├── HasMany: assessments
├── HasMany: gelombangs
└── HasMany: applicants

program_keahlians
├── BelongsTo: schools (school_id)
├── HasMany: konsentrasi_keahlians
└── HasMany: applicants

konsentrasi_keahlians
├── BelongsTo: program_keahlians (program_keahlian_id)
└── HasMany: applicants
```

### Students

```
students
├── BelongsTo: schools (school_id)
├── HasMany: student_classes
├── HasMany: bills
└── HasMany: grades

student_classes
├── BelongsTo: students (student_id)
├── BelongsTo: classrooms (classroom_id)
└── BelongsTo: academic_years (academic_year_id)
```

### Schedule

```
classrooms
├── BelongsTo: schools (school_id)
├── BelongsTo: academic_years (academic_year_id)
├── BelongsTo: employees as homeroomTeacher (homeroom_teacher_id)
├── HasMany: schedules
└── HasMany: student_classes

schedules
├── BelongsTo: classrooms (classroom_id)
├── BelongsTo: subjects (subject_id)
├── BelongsTo: employees as teacher (employee_id)
├── BelongsTo: timeslots (timeslot_id)
└── BelongsTo: academic_years (academic_year_id)

employees
├── BelongsTo: schools (school_id)
├── HasMany: teacher_competencies
├── HasMany: schedules
└── HasOne: user
```

### PSB

```
applicants
├── BelongsTo: schools (school_id)
├── BelongsTo: academic_years (academic_year_id)
├── BelongsTo: gelombangs (gelombang_id)
├── BelongsTo: program_keahlians (program_keahlian_id)
└── BelongsTo: konsentrasi_keahlians (konsentrasi_keahlian_id)

gelombangs
├── BelongsTo: schools (school_id)
├── BelongsTo: academic_years (academic_year_id)
└── HasMany: applicants
```

### Financial

```
bills
├── BelongsTo: students (student_id)
├── BelongsTo: academic_years (academic_year_id)
└── HasMany: payments

payments
├── BelongsTo: bills (bill_id)
└── BelongsTo: users as processedBy (processed_by)

position_allowances
└── BelongsTo: employees (employee_id)
```

### Assessment

```
assessments
├── BelongsTo: academic_years (academic_year_id)
├── BelongsTo: schools (school_id)
└── HasMany: grades

grades
├── BelongsTo: students (student_id)
├── BelongsTo: subjects (subject_id)
├── BelongsTo: assessments (assessment_id)
└── BelongsTo: academic_years (academic_year_id)
```

---

## 12. INDEXES & PERFORMANCE

### Critical Indexes

1. **Primary Keys**: Semua tabel memiliki AUTO_INCREMENT primary key
2. **Foreign Keys**: Index pada semua foreign key columns untuk join performance
3. **Unique Constraints**:
    - users.email
    - students.nisn
    - bills.bill_number
    - payments.payment_number
    - applicants.registration_number
4. **Composite Indexes**:
    - student_classes: (student_id, academic_year_id)
    - schedules: (classroom_id, timeslot_id), (employee_id, timeslot_id)
    - grades: (student_id, subject_id, assessment_id, academic_year_id)

### Performance Optimization

1. **Eager Loading**: Gunakan with() untuk menghindari N+1 query problem
2. **Query Caching**: Cache query yang sering diakses (master data)
3. **Index Optimization**: Monitor slow queries dan tambahkan index sesuai kebutuhan
4. **Pagination**: Gunakan pagination untuk list yang panjang
5. **Soft Deletes**: Gunakan soft deletes untuk data history

---

## CHANGELOG

| Tanggal    | Versi | Perubahan                             |
| ---------- | ----- | ------------------------------------- |
| 08/02/2026 | 1.0   | Initial database schema documentation |

---

**Dokumen dibuat oleh:** Tim Development Pembda Hub  
**Terakhir diupdate:** 8 Februari 2026
