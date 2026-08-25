# DOKUMENTASI TEKNIS 04: API REFERENCE

**Sistem Manajemen Sekolah Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)**  
**Versi:** 3.0.0  
**Tanggal:** 25 Agustus 2026

---

## DAFTAR ISI

1. [Overview](#1-overview)
2. [Authentication](#2-authentication)
3. [Public APIs](#3-public-apis)
4. [Admin APIs](#4-admin-apis)
5. [Response Format](#5-response-format)
6. [Error Handling](#6-error-handling)
7. [Rate Limiting](#7-rate-limiting)

---

## 1. OVERVIEW

### Base URL

```
http://localhost/pembdahub
```

### API Prefix

- **Public APIs:** `/api/*`
- **Admin APIs:** `/admin/*` (requires authentication)

### Content Type

```
Content-Type: application/json
Accept: application/json
```

---

## 2. AUTHENTICATION

### Admin Routes

Admin routes require session-based authentication via Laravel.

**Login:**

```http
POST /login
Content-Type: application/x-www-form-urlencoded

email=admin@example.com
password=password
```

**Response:**

```json
{
    "redirect": "/admin/dashboard"
}
```

**Logout:**

```http
POST /logout
```

---

## 3. PUBLIC APIs

### 3.1 Get Program Keahlian by School

**Endpoint:** `GET /api/program-keahlian/{schoolId}`

**Description:** Mendapatkan list program keahlian berdasarkan sekolah (khusus SMK)

**Parameters:**

- `schoolId` (path, required): ID sekolah (integer)

**Example Request:**

```http
GET /api/program-keahlian/3 HTTP/1.1
Host: localhost
Accept: application/json
```

**Example Response:**

```json
[
    {
        "id": 1,
        "name": "Teknik Komputer dan Informatika",
        "code": "TKI"
    },
    {
        "id": 2,
        "name": "Teknik Otomotif",
        "code": "TO"
    },
    {
        "id": 3,
        "name": "Tata Busana",
        "code": "TB"
    }
]
```

**Status Codes:**

- `200 OK`: Success
- `404 Not Found`: School not found

---

### 3.2 Get Konsentrasi Keahlian by Program

**Endpoint:** `GET /api/konsentrasi-keahlian/{programId}`

**Description:** Mendapatkan list konsentrasi keahlian berdasarkan program keahlian

**Parameters:**

- `programId` (path, required): ID program keahlian (integer)

**Example Request:**

```http
GET /api/konsentrasi-keahlian/1 HTTP/1.1
Host: localhost
Accept: application/json
```

**Example Response:**

```json
[
    {
        "id": 1,
        "name": "Teknik Komputer dan Jaringan",
        "code": "TKJ"
    },
    {
        "id": 2,
        "name": "Multimedia",
        "code": "MM"
    },
    {
        "id": 3,
        "name": "Rekayasa Perangkat Lunak",
        "code": "RPL"
    }
]
```

**Status Codes:**

- `200 OK`: Success
- `404 Not Found`: Program not found

---

## 4. ADMIN APIs

### 4.1 Get Applicants List

**Endpoint:** `GET /admin/psb`

**Description:** Mendapatkan list pendaftar dengan filter dan pagination

**Authentication:** Required (session-based)

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `academic_year_id` | integer | No | Filter by academic year |
| `school_id` | integer | No | Filter by school (1=SMP, 2=SMA, 3=SMK) |
| `program_keahlian_id` | integer | No | Filter by program keahlian |
| `status` | string | No | Filter by status (Pending/Diterima/Ditolak/Cadangan) |
| `search` | string | No | Search by name or registration number |
| `page` | integer | No | Page number (default: 1) |
| `per_page` | integer | No | Items per page (default: 20) |

**Example Request:**

```http
GET /admin/psb?school_id=3&status=Pending&page=1 HTTP/1.1
Host: localhost
Cookie: laravel_session=...
Accept: text/html
```

**Example Response:** HTML page with paginated applicants list

---

### 4.2 Get Applicant Detail

**Endpoint:** `GET /admin/psb/{id}`

**Description:** Mendapatkan detail pendaftar

**Authentication:** Required

**Parameters:**

- `id` (path, required): Applicant ID

**Example Request:**

```http
GET /admin/psb/123 HTTP/1.1
Host: localhost
Cookie: laravel_session=...
Accept: text/html
```

**Example Response:** HTML page with applicant details

---

### 4.3 Update Applicant Status

**Endpoint:** `PUT /admin/psb/{id}`

**Description:** Update status pendaftar

**Authentication:** Required

**Parameters:**

- `id` (path, required): Applicant ID

**Request Body:**

```json
{
    "status": "Diterima",
    "notes": "Memenuhi persyaratan"
}
```

**Validation Rules:**

- `status`: required, in:Pending,Diterima,Ditolak,Cadangan
- `notes`: nullable, string, max:1000

**Example Request:**

```http
PUT /admin/psb/123 HTTP/1.1
Host: localhost
Cookie: laravel_session=...
Content-Type: application/x-www-form-urlencoded

_token=...&status=Diterima&notes=Memenuhi+persyaratan
```

**Example Response:** Redirect to detail page with success message

**Side Effects:**

- Sends WhatsApp notification to parent if status changed

---

### 4.4 Export Applicants to Excel

**Endpoint:** `GET /admin/psb/export`

**Description:** Export daftar pendaftar ke CSV (Excel-compatible)

**Authentication:** Required

**Query Parameters:**
Same as GET /admin/psb (filters applied to export)

**Example Request:**

```http
GET /admin/psb/export?school_id=3&status=Diterima HTTP/1.1
Host: localhost
Cookie: laravel_session=...
Accept: text/csv
```

**Example Response:**

```csv
No. Pendaftaran,Tahun Ajaran,Sekolah,Gelombang,Jenis,NISN,...
PSB-SMKS-2026-0001,2025/2026,SMKS Pembda Nias,Gelombang 1,Baru,1234567890,...
PSB-SMKS-2026-0002,2025/2026,SMKS Pembda Nias,Gelombang 1,Baru,1234567891,...
```

**Response Headers:**

```
Content-Type: text/csv; charset=UTF-8
Content-Disposition: attachment; filename="data-pendaftar-2026-02-08-143025.csv"
```

**CSV Format:**

- UTF-8 with BOM (for Excel compatibility)
- Comma-separated values
- Quoted strings if contain comma/newline

---

### 4.5 Delete Applicant

**Endpoint:** `DELETE /admin/psb/{id}`

**Description:** Hapus pendaftar

**Authentication:** Required

**Parameters:**

- `id` (path, required): Applicant ID

**Example Request:**

```http
DELETE /admin/psb/123 HTTP/1.1
Host: localhost
Cookie: laravel_session=...
X-CSRF-TOKEN: ...
```

**Example Response:** Redirect to index page with success message

---

## 5. RESPONSE FORMAT

### Success Response (JSON)

```json
{
    "success": true,
    "message": "Operation successful",
    "data": {
        "id": 1,
        "name": "Example"
    }
}
```

### Error Response (JSON)

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "name": ["The name field is required."],
        "email": ["The email must be a valid email address."]
    }
}
```

### Pagination Response

```json
{
  "data": [...],
  "current_page": 1,
  "last_page": 10,
  "per_page": 20,
  "total": 200,
  "from": 1,
  "to": 20,
  "links": {
    "first": "http://localhost/admin/psb?page=1",
    "last": "http://localhost/admin/psb?page=10",
    "prev": null,
    "next": "http://localhost/admin/psb?page=2"
  }
}
```

---

## 6. ERROR HANDLING

### HTTP Status Codes

| Code | Description           | Usage                                    |
| ---- | --------------------- | ---------------------------------------- |
| 200  | OK                    | Request successful                       |
| 201  | Created               | Resource created successfully            |
| 204  | No Content            | Request successful, no content to return |
| 400  | Bad Request           | Invalid request format                   |
| 401  | Unauthorized          | Authentication required                  |
| 403  | Forbidden             | Access denied                            |
| 404  | Not Found             | Resource not found                       |
| 422  | Unprocessable Entity  | Validation failed                        |
| 500  | Internal Server Error | Server error                             |

### Validation Errors

**Example:**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

### Exception Handling

Laravel's exception handler catches all exceptions and returns appropriate responses:

**Development Mode (APP_DEBUG=true):**

- Full stack trace
- Debug information
- SQL queries

**Production Mode (APP_DEBUG=false):**

- Generic error message
- No sensitive information
- Errors logged to `storage/logs/laravel.log`

---

## 7. RATE LIMITING

### Current Configuration

**No rate limiting implemented yet**

### Recommended Configuration

```php
// app/Providers/RouteServiceProvider.php

protected function configureRateLimiting()
{
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
    });

    RateLimiter::for('public', function (Request $request) {
        return Limit::perMinute(30)->by($request->ip());
    });
}
```

**Apply to routes:**

```php
// routes/web.php

Route::prefix('api')->middleware('throttle:api')->group(function () {
    // API routes
});
```

---

## ADDITIONAL APIs (Future Development)

### Student Management APIs

- `GET /api/students` - Get students list
- `GET /api/students/{id}` - Get student detail
- `POST /api/students` - Create student
- `PUT /api/students/{id}` - Update student
- `DELETE /api/students/{id}` - Delete student

### Schedule APIs

- `GET /api/schedules` - Get schedules by classroom
- `GET /api/schedules/{classroomId}` - Get schedule for specific classroom
- `POST /api/schedules` - Create schedule entry
- `PUT /api/schedules/{id}` - Update schedule entry
- `DELETE /api/schedules/{id}` - Delete schedule entry

### Financial APIs

- `GET /api/bills` - Get bills list
- `GET /api/bills/{studentId}` - Get bills for specific student
- `POST /api/payments` - Create payment
- `GET /api/payments/{billId}` - Get payment history for bill

### Assessment APIs

- `GET /api/grades/{studentId}` - Get grades for student
- `POST /api/grades` - Submit grade
- `PUT /api/grades/{id}` - Update grade
- `GET /api/assessments` - Get assessment list

---

## CHANGELOG

| Tanggal    | Versi | Perubahan                 |
| ---------- | ----- | ------------------------- |
| 08/02/2026 | 1.0   | Initial API documentation |

---

**Dokumen dibuat oleh:** Tim Development Pembda Hub  
**Terakhir diupdate:** 8 Februari 2026
