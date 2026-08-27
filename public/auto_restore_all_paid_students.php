<?php
/**
 * Standalone Emergency Tool: Otomatisasi Pemulihan Massal Pembayaran Terhapus (Seluruh Siswa & Kelas)
 * Access URL: https://perguruanpembda.com/auto_restore_all_paid_students.php?secret=pembda99
 */

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$secret = $_REQUEST['secret'] ?? '';
$VALID_SECRET = 'pembda99';

if ($secret !== $VALID_SECRET) {
    http_response_code(403);
    die('403 Forbidden - Token Secret Salah');
}

use App\Models\ActivityLog;
use App\Models\Payment;
use App\Models\StudentBill;
use App\Models\Student;
use App\Models\PaymentType;
use App\Models\School;
use App\Models\Classroom;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

$action = $_REQUEST['action'] ?? '';
$schoolId = $_REQUEST['school_id'] ?? 'all';
$classroomId = $_REQUEST['classroom_id'] ?? 'all';
$targetMonth = $_REQUEST['month'] ?? 8; // Default August

$restoredPaymentCount = 0;
$restoredBillCount = 0;
$log = [];

$activeAy = AcademicYear::where('is_active', true)->first() ?? AcademicYear::orderBy('year', 'desc')->first();
$activeAyId = $activeAy ? $activeAy->id : 1;

// ACTION 1: AUTO RECOVERY FROM JOBS & ACTIVITY LOGS
if ($action === 'auto_scan_all') {
    DB::transaction(function() use ($activeAyId, &$restoredPaymentCount, &$restoredBillCount, &$log) {
        // A. Scan jobs table
        try {
            $jobs = DB::table('jobs')->get();
            foreach ($jobs as $j) {
                $payload = json_decode($j->payload, true);
                $commandStr = $payload['data']['command'] ?? '';

                if (str_contains($commandStr, 'Payment') || str_contains($commandStr, 'amount_paid')) {
                    // Extract payment parameters via regex from serialized payload
                    preg_match_all('/"(amount_paid|student_id|bill_id|receipt_number|payment_date)";(?:s:\d+:"([^"]+)"|i:(\d+));/', $commandStr, $matches, PREG_SET_ORDER);
                    
                    $params = [];
                    foreach ($matches as $m) {
                        $key = $m[1];
                        $val = $m[2] !== '' ? $m[2] : $m[3];
                        $params[$key] = $val;
                    }

                    if (!empty($params['student_id']) && !empty($params['amount_paid'])) {
                        $stId = (int)$params['student_id'];
                        $amt = (float)$params['amount_paid'];
                        $rec = $params['receipt_number'] ?? null;
                        $pDate = $params['payment_date'] ?? date('Y-m-d');

                        $exists = Payment::where('student_id', $stId)->where('amount_paid', $amt)->exists();
                        if (!$exists) {
                            $bill = StudentBill::where('student_id', $stId)->orderBy('id', 'desc')->first();
                            if ($bill) {
                                $newPay = Payment::create([
                                    'bill_id' => $bill->id,
                                    'student_id' => $stId,
                                    'amount_paid' => $amt,
                                    'payment_method' => 'cash',
                                    'receipt_number' => $rec ?? ('KWT-AUTO-' . time() . '-' . rand(10, 99)),
                                    'payment_date' => $pDate,
                                    'notes' => 'Restored automatically from jobs queue',
                                    'processed_by' => 1,
                                    'is_verified' => true,
                                ]);
                                $restoredPaymentCount++;
                                $stName = Student::find($stId)->full_name ?? "ID #{$stId}";
                                $log[] = "Otomatis Memulihkan Pembayaran Siswa: {$stName} (Nominal: Rp " . number_format($amt, 0, ',', '.') . ")";
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {}

        // B. Scan activity_logs table
        $actLogs = ActivityLog::whereIn('action', ['created', 'deleted'])->get();
        foreach ($actLogs as $al) {
            $data = json_decode($al->changes, true);
            if (is_array($data) && isset($data['student_id'], $data['amount_paid'])) {
                $stId = (int)$data['student_id'];
                $amt = (float)$data['amount_paid'];
                $rec = $data['receipt_number'] ?? null;
                $pDate = $data['payment_date'] ?? $data['created_at'] ?? date('Y-m-d');

                $exists = Payment::where('student_id', $stId)->where('amount_paid', $amt)->exists();
                if (!$exists) {
                    $bill = StudentBill::where('student_id', $stId)->orderBy('id', 'desc')->first();
                    if ($bill) {
                        Payment::create([
                            'bill_id' => $bill->id,
                            'student_id' => $stId,
                            'amount_paid' => $amt,
                            'payment_method' => $data['payment_method'] ?? 'cash',
                            'receipt_number' => $rec ?? ('KWT-LOG-' . time() . '-' . rand(10, 99)),
                            'payment_date' => $pDate,
                            'notes' => 'Restored from activity log record',
                            'processed_by' => 1,
                            'is_verified' => true,
                        ]);
                        $restoredPaymentCount++;
                        $stName = Student::find($stId)->full_name ?? "ID #{$stId}";
                        $log[] = "Otomatis Memulihkan Pembayaran ActivityLog Siswa: {$stName} (Nominal: Rp " . number_format($amt, 0, ',', '.') . ")";
                    }
                }
            }
        }

        // C. Re-sync all bill balances & status
        $bills = StudentBill::all();
        foreach ($bills as $b) {
            $totalPaid = (float) Payment::where('bill_id', $b->id)->where('is_verified', true)->sum('amount_paid');
            if ($totalPaid != $b->paid_amount) {
                $b->paid_amount = $totalPaid;
                if ($totalPaid >= $b->amount) {
                    $b->status = 'lunas';
                } elseif ($totalPaid > 0) {
                    $b->status = 'cicilan';
                } else {
                    $b->status = 'belum_bayar';
                }
                $b->save();
            }
        }
    });
}

// ACTION 2: BULK RESTORE SELECTED CLASS / ALL STUDENTS FOR TARGET MONTH
if ($action === 'bulk_restore_month' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedStudentIds = $_POST['student_ids'] ?? [];

    if (!empty($selectedStudentIds)) {
        DB::transaction(function() use ($selectedStudentIds, $targetMonth, $activeAyId, &$restoredPaymentCount, &$log) {
            $students = Student::whereIn('id', $selectedStudentIds)->get();
            $monthlyTypes = PaymentType::where('is_recurring', true)->get();

            foreach ($students as $st) {
                $pTypes = $monthlyTypes->where('school_id', $st->school_id);
                foreach ($pTypes as $pt) {
                    // Find or create bill for target month
                    $bill = StudentBill::firstOrCreate(
                        [
                            'student_id' => $st->id,
                            'payment_type_id' => $pt->id,
                            'month' => $targetMonth,
                            'year' => 2026,
                        ],
                        [
                            'academic_year_id' => $activeAyId,
                            'amount' => $pt->amount,
                            'paid_amount' => 0,
                            'yayasan_share_amount' => $pt->yayasan_share_amount ?? $pt->amount,
                            'status' => 'belum_bayar',
                            'due_date' => '2026-08-10',
                        ]
                    );

                    if ($bill->status !== 'lunas') {
                        $unpaid = $bill->amount - $bill->paid_amount;
                        if ($unpaid > 0) {
                            Payment::create([
                                'bill_id' => $bill->id,
                                'student_id' => $st->id,
                                'amount_paid' => $unpaid,
                                'payment_method' => 'cash',
                                'receipt_number' => 'KWT-BULK-' . date('Ymd') . '-' . rand(1000, 9999),
                                'payment_date' => date('Y-m-d'),
                                'notes' => 'Bulk restore lunas bulan ' . $targetMonth,
                                'processed_by' => auth()->id() ?? 1,
                                'is_verified' => true,
                            ]);

                            $bill->paid_amount = $bill->amount;
                            $bill->status = 'lunas';
                            $bill->save();

                            $restoredPaymentCount++;
                            $log[] = "BERHASIL PULIHKAN LUNAS (Bulan {$targetMonth}): Siswa {$st->full_name} - Tagihan {$pt->type_name} (Rp " . number_format($pt->amount, 0, ',', '.') . ")";
                        }
                    }
                }
            }
        });
    }
}

// Fetch schools and classrooms for filter
$schools = School::schoolsOnly()->get();
$classrooms = Classroom::when($schoolId !== 'all', fn($q) => $q->where('school_id', $schoolId))->orderBy('class_name')->get();

// Fetch students list for bulk selection
$studentsQuery = Student::with(['school', 'bills' => fn($q) => $q->where('month', $targetMonth)])
    ->where('status', 'active');

if ($schoolId !== 'all') {
    $studentsQuery->where('school_id', $schoolId);
}

if ($classroomId !== 'all') {
    $studentIdsInClass = DB::table('student_classes')->where('classroom_id', $classroomId)->pluck('student_id');
    $studentsQuery->whereIn('id', $studentIdsInClass);
}

$studentsList = $studentsQuery->orderBy('full_name')->get();

$monthNames = [
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tool Pemulihan Massal Pembayaran Siswa - PembdaHUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-size: 13px; font-family: system-ui, -apple-system, sans-serif; }
        .card-custom { border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="py-4">
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1"><i class="fa-solid fa-users-gear text-primary me-2"></i> Tool Emergency: Pemulihan Massal Pembayaran Siswa</h3>
            <p class="text-muted mb-0">Pulihkan pembayaran lunas sekaligus untuk seluruh kelas / seluruh siswa yang pembayarannya sempat terhapus</p>
        </div>
        <a href="clean_duplicate_payments.php?secret=<?= urlencode($secret) ?>" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Tool Pembersih</a>
    </div>

    <!-- OPSI A: OTOMATIS SCAN JOBS & LOGS -->
    <div class="card card-custom p-4 mb-4 bg-white border-primary">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold text-primary mb-1"><i class="fa-solid fa-robot me-2"></i> Opsi A: Otomatis Pindai & Pulihkan Seluruh Record Terhapus di Database</h5>
                <p class="text-muted mb-0">Memindai antrean jobs & activity logs secara otomatis untuk mengembalikan seluruh pembayaran terhapus tanpa perlu memilih siswa satu per satu.</p>
            </div>
            <a href="auto_restore_all_paid_students.php?secret=<?= urlencode($secret) ?>&action=auto_scan_all" class="btn btn-primary font-bold px-4" onclick="return confirm('Apakah Anda yakin ingin menjalankan pemindaian & pemulihan otomatis seluruh transaksi terhapus di database?');">
                <i class="fa-solid fa-bolt me-1"></i> Jalankan Pemulihan Otomatis Database
            </a>
        </div>
    </div>

    <?php if(!empty($log)): ?>
    <div class="alert alert-success alert-dismissible fade show card-custom mb-4" role="alert">
        <h5 class="alert-heading fw-bold"><i class="fa-solid fa-check-circle me-2"></i> Berhasil Memulihkan <?= $restoredPaymentCount ?> Transaksi Pembayaran!</h5>
        <hr>
        <div style="max-height: 250px; overflow-y: auto;" class="font-monospace small">
            <?php foreach($log as $l): ?>
                <div>&bull; <?= htmlspecialchars($l) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- OPSI B: PEMULIHAN MASSAL PER KELAS / SEKOLAH -->
    <div class="card card-custom p-4 mb-4 bg-white">
        <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-list-check me-2"></i> Opsi B: Centang Massal Siswa Per Kelas (Contoh: SMAS Pembda 1 / Kelas XI)</h5>
        
        <form action="auto_restore_all_paid_students.php" method="GET" class="row g-3 align-items-center mb-4">
            <input type="hidden" name="secret" value="<?= htmlspecialchars($secret) ?>">
            
            <div class="col-md-4">
                <label class="form-label fw-bold text-secondary">Unit Sekolah:</label>
                <select name="school_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all" <?= $schoolId === 'all' ? 'selected' : '' ?>>-- Semua Unit Sekolah --</option>
                    <?php foreach($schools as $sch): ?>
                        <option value="<?= $sch->id ?>" <?= (string)$schoolId === (string)$sch->id ? 'selected' : '' ?>><?= htmlspecialchars($sch->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold text-secondary">Kelas:</label>
                <select name="classroom_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all" <?= $classroomId === 'all' ? 'selected' : '' ?>>-- Semua Kelas --</option>
                    <?php foreach($classrooms as $cls): ?>
                        <option value="<?= $cls->id ?>" <?= (string)$classroomId === (string)$cls->id ? 'selected' : '' ?>><?= htmlspecialchars($cls->class_name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold text-secondary">Bulan Yang Dipulihkan:</label>
                <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php foreach($monthNames as $mNum => $mName): ?>
                        <option value="<?= $mNum ?>" <?= (int)$targetMonth === (int)$mNum ? 'selected' : '' ?>>Bulan <?= $mName ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <?php if($studentsList->isNotEmpty()): ?>
        <form action="auto_restore_all_paid_students.php?secret=<?= urlencode($secret) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memulihkan & menandai Lunas Bulan <?= $monthNames[$targetMonth] ?? $targetMonth ?> untuk siswa yang dicentang?');">
            <input type="hidden" name="action" value="bulk_restore_month">
            <input type="hidden" name="school_id" value="<?= htmlspecialchars($schoolId) ?>">
            <input type="hidden" name="classroom_id" value="<?= htmlspecialchars($classroomId) ?>">
            <input type="hidden" name="month" value="<?= htmlspecialchars($targetMonth) ?>">

            <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded border mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="checkAllStudents" onchange="toggleAllStudents(this)">
                    <label class="form-check-label fw-bold text-uppercase small" for="checkAllStudents">Centang Semua Siswa (<?= $studentsList->count() ?> Siswa)</label>
                </div>
                <button type="submit" class="btn btn-success font-bold px-4"><i class="fa-solid fa-rotate-left me-1"></i> Pulihkan & Tandai Lunas Bulan <?= $monthNames[$targetMonth] ?? $targetMonth ?></button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light text-uppercase">
                        <tr>
                            <th class="text-center" style="width: 40px;">Pilih</th>
                            <th>Nama Siswa</th>
                            <th>NISN</th>
                            <th>Unit Sekolah</th>
                            <th>Status Tagihan Bulan <?= $monthNames[$targetMonth] ?? $targetMonth ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($studentsList as $st): ?>
                        <?php
                            $targetBill = $st->bills->first();
                            $isLunas = $targetBill && $targetBill->status === 'lunas';
                        ?>
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" name="student_ids[]" value="<?= $st->id ?>" class="form-check-input student-cb" <?= $isLunas ? '' : 'checked' ?>>
                            </td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($st->full_name) ?></td>
                            <td class="font-monospace text-muted"><?= htmlspecialchars($st->nisn ?? '-') ?></td>
                            <td><?= htmlspecialchars($st->school->name ?? '-') ?></td>
                            <td>
                                <?php if($isLunas): ?>
                                    <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> LUNAS</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> BELUM DIBAYAR / PERLU DIPULIHKAN</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
        <?php else: ?>
            <p class="text-center text-muted py-4">Tidak ada data siswa ditemukan untuk filter ini.</p>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleAllStudents(master) {
    const cbs = document.querySelectorAll('.student-cb');
    cbs.forEach(c => c.checked = master.checked);
}
</script>
</body>
</html>
