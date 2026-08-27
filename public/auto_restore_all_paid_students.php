<?php
/**
 * Standalone Emergency Tool: Otomatisasi Pemulihan Massal Pembayaran Terhapus (Seluruh Siswa & Kelas)
 * Access URL: https://perguruanpembda.com/auto_restore_all_paid_students.php?secret=pembda99
 */

@ini_set('display_errors', '1');
@ini_set('display_startup_errors', '1');
@error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
@ini_set('memory_limit', '512M');
@ini_set('max_execution_time', '300');
@set_time_limit(300);

$secret = $_REQUEST['secret'] ?? 'pembda99';
$action = $_REQUEST['action'] ?? '';
$message = '';
$restoredCount = 0;
$log = [];

// Locate .env across all potential server paths on Hostinger
$possibleEnvPaths = [
    __DIR__ . '/.env',
    __DIR__ . '/../.env',
    __DIR__ . '/pembdahub/.env',
    '/home/u474310197/domains/perguruanpembda.com/public_html/pembdahub/.env',
    '/home/u474310197/domains/perguruanpembda.com/public_html/.env',
];

$dbHost = '127.0.0.1'; $dbPort = '3306'; $dbName = 'u474310197_database'; $dbUser = 'u474310197_user'; $dbPass = '';

foreach ($possibleEnvPaths as $envPath) {
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, '#')) continue;
            if (str_contains($line, '=')) {
                list($key, $val) = explode('=', $line, 2);
                if (trim($key) === 'DB_HOST') $dbHost = trim($val, " \"'");
                if (trim($key) === 'DB_PORT') $dbPort = trim($val, " \"'");
                if (trim($key) === 'DB_DATABASE') $dbName = trim($val, " \"'");
                if (trim($key) === 'DB_USERNAME') $dbUser = trim($val, " \"'");
                if (trim($key) === 'DB_PASSWORD') $dbPass = trim($val, " \"'");
            }
        }
        break;
    }
}

try {
    $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (\Throwable $e) {
    die("<div style='font-family:sans-serif;padding:30px;background:#fee2e2;color:#991b1b;'>❌ Gagal Koneksi DB: " . htmlspecialchars($e->getMessage()) . "</div>");
}

$schoolId = $_REQUEST['school_id'] ?? 'all';
$classroomId = $_REQUEST['classroom_id'] ?? 'all';
$targetMonth = (int)($_REQUEST['month'] ?? 8);

// BULK RESTORE SELECTED STUDENTS / CLASS FOR TARGET MONTH (AGUSTUS, SEPTEMBER, OKTOBER)
if ($action === 'bulk_restore_month' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentIds = $_POST['student_ids'] ?? [];
    if (!empty($studentIds)) {
        try {
            @$pdo->exec("SET FOREIGN_KEY_CHECKS=0;");

            $ayStmt = $pdo->query("SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1");
            $ayRes = $ayStmt->fetch();
            $activeAyId = $ayRes ? $ayRes['id'] : 1;

            $pTypesStmt = $pdo->query("SELECT id, school_id, type_name, amount, yayasan_share_amount FROM payment_types WHERE is_recurring = 1");
            $pTypes = $pTypesStmt->fetchAll();

            foreach ($studentIds as $stId) {
                $stStmt = $pdo->prepare("SELECT id, school_id, full_name FROM students WHERE id = ?");
                $stStmt->execute([$stId]);
                $st = $stStmt->fetch();
                if (!$st) continue;

                foreach ($pTypes as $pt) {
                    if ($pt['school_id'] == $st['school_id']) {
                        // Check or create bill for target month
                        $chkBill = $pdo->prepare("SELECT id, amount, paid_amount FROM student_bills WHERE student_id = ? AND payment_type_id = ? AND month = ? AND year = 2026");
                        $chkBill->execute([$stId, $pt['id'], $targetMonth]);
                        $bill = $chkBill->fetch();

                        if (!$bill) {
                            $dueDate = sprintf('2026-%02d-10', $targetMonth);
                            $insB = $pdo->prepare("INSERT INTO student_bills (student_id, payment_type_id, academic_year_id, month, year, amount, paid_amount, yayasan_share_amount, status, due_date, created_at, updated_at) VALUES (?, ?, ?, ?, 2026, ?, ?, ?, 'lunas', ?, NOW(), NOW())");
                            $insB->execute([$stId, $pt['id'], $activeAyId, $targetMonth, $pt['amount'], $pt['amount'], $pt['yayasan_share_amount'] ?? $pt['amount'], $dueDate]);
                            $billId = $pdo->lastInsertId();
                        } else {
                            $billId = $bill['id'];
                        }

                        // Ensure payment exists
                        $chkPay = $pdo->prepare("SELECT id FROM payments WHERE bill_id = ?");
                        $chkPay->execute([$billId]);
                        if (!$chkPay->fetch()) {
                            $insP = $pdo->prepare("INSERT INTO payments (bill_id, student_id, amount_paid, payment_method, receipt_number, payment_date, notes, processed_by, is_verified, created_at, updated_at) VALUES (?, ?, ?, 'cash', ?, NOW(), 'Pemulihan Masal Lunas Bulan " . $targetMonth . "', 1, 1, NOW(), NOW())");
                            $insP->execute([$billId, $stId, $pt['amount'], 'KWT-BULK-M' . $targetMonth . '-' . $stId . '-' . $pt['id']]);
                            $restoredCount++;
                        }

                        // Update bill status to lunas
                        $updB = $pdo->prepare("UPDATE student_bills SET paid_amount = amount, status = 'lunas' WHERE id = ?");
                        $updB->execute([$billId]);
                    }
                }
                $log[] = "LUNAS BULAN {$targetMonth}: {$st['full_name']}";
            }

            @$pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
            $message = "BERHASIL MEMULIHKAN KEUANGAN! Memproses LUNAS Bulan {$targetMonth} untuk " . count($studentIds) . " siswa yang dipilih.";
        } catch (\Throwable $e) {
            $message = "Error: " . htmlspecialchars($e->getMessage());
        }
    }
}

// Fetch schools and classrooms safely
$schools = [];
try {
    $schools = $pdo->query("SELECT id, name FROM schools")->fetchAll();
} catch (\Throwable $e) {}

$classrooms = [];
try {
    $classrooms = $pdo->query("SELECT id, school_id, name FROM classrooms ORDER BY school_id ASC, name ASC")->fetchAll();
} catch (\Throwable $e) {}

// Fetch students matching filter
$studentsList = [];
try {
    $queryStr = "SELECT id, full_name, school_id, classroom_id FROM students WHERE 1=1";
    $params = [];

    if ($schoolId !== 'all') {
        $queryStr .= " AND school_id = ?";
        $params[] = $schoolId;
    }
    if ($classroomId !== 'all') {
        $queryStr .= " AND classroom_id = ?";
        $params[] = $classroomId;
    }

    $queryStr .= " ORDER BY full_name ASC LIMIT 500";
    $stPrepared = $pdo->prepare($queryStr);
    $stPrepared->execute($params);
    $studentsList = $stPrepared->fetchAll();
} catch (\Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemulihan Massal Pembayaran Per Bulan - PembdaHUB</title>
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
            <h3 class="fw-bold text-dark mb-1"><i class="fa-solid fa-list-check text-primary me-2"></i> Tool Pemulihan Massal Pembayaran Per Bulan (Bulan 8, 9, 10)</h3>
            <p class="text-muted mb-0">Pilih kelas/siswa dan bulan (Agustus, September, Oktober) untuk memulihkan status Lunas secara masal dalam 1 klik</p>
        </div>
        <a href="clean_duplicate_payments.php?secret=<?= urlencode($secret) ?>" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Kembali</a>
    </div>

    <?php if(!empty($message)): ?>
    <div class="alert alert-success card-custom mb-4">
        <h5 class="alert-heading fw-bold"><i class="fa-solid fa-check-circle me-2"></i> Hasil Pemulihan</h5>
        <p class="mb-2"><?= htmlspecialchars($message) ?></p>
        <?php if(!empty($log)): ?>
        <hr>
        <div class="font-monospace small" style="max-height: 200px; overflow-y: auto;">
            <?php foreach($log as $l): ?>
                <div>&bull; <?= htmlspecialchars($l) ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- FILTER & BULK FORM -->
    <div class="card card-custom p-4 mb-4 bg-white">
        <form method="GET" class="row g-3 mb-4">
            <input type="hidden" name="secret" value="<?= htmlspecialchars($secret) ?>">
            <div class="col-md-4">
                <label class="form-label fw-bold">Pilih Unit Sekolah</label>
                <select name="school_id" class="form-select" onchange="this.form.submit()">
                    <option value="all">-- Semua Unit Sekolah --</option>
                    <?php foreach($schools as $sc): ?>
                        <option value="<?= $sc['id'] ?>" <?= $schoolId == $sc['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sc['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Pilih Kelas</label>
                <select name="classroom_id" class="form-select" onchange="this.form.submit()">
                    <option value="all">-- Semua Kelas --</option>
                    <?php foreach($classrooms as $cr): ?>
                        <option value="<?= $cr['id'] ?>" <?= $classroomId == $cr['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cr['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Bulan Yang Ingin Dipulihkan Lunas</label>
                <select name="month" class="form-select" onchange="this.form.submit()">
                    <option value="7" <?= $targetMonth == 7 ? 'selected' : '' ?>>Juli 2026 (Bulan 7)</option>
                    <option value="8" <?= $targetMonth == 8 ? 'selected' : '' ?>>Agustus 2026 (Bulan 8)</option>
                    <option value="9" <?= $targetMonth == 9 ? 'selected' : '' ?>>September 2026 (Bulan 9)</option>
                    <option value="10" <?= $targetMonth == 10 ? 'selected' : '' ?>>Oktober 2026 (Bulan 10)</option>
                </select>
            </div>
        </form>

        <form method="POST" action="auto_restore_all_paid_students.php?secret=<?= urlencode($secret) ?>">
            <input type="hidden" name="action" value="bulk_restore_month">
            <input type="hidden" name="month" value="<?= $targetMonth ?>">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="selectAll" onclick="toggleAll(this)">
                    <label class="form-check-label fw-bold" for="selectAll">Pilih Semua Siswa (<?= count($studentsList) ?> Siswa)</label>
                </div>
                <button type="submit" class="btn btn-success font-bold px-4" onclick="return confirm('Apakah Anda yakin ingin menandai LUNAS bulan <?= $targetMonth ?> untuk siswa yang dipilih?');">
                    <i class="fa-solid fa-check-double me-1"></i> Pulihkan LUNAS Bulan <?= $targetMonth ?> (1-Klik)
                </button>
            </div>

            <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th width="40">#</th>
                            <th>Nama Siswa</th>
                            <th>ID Siswa</th>
                            <th class="text-center">Pilih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($studentsList as $idx => $st): ?>
                        <tr>
                            <td><?= $idx + 1 ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($st['full_name']) ?></td>
                            <td><span class="badge bg-secondary">ID #<?= $st['id'] ?></span></td>
                            <td class="text-center">
                                <input class="form-check-input st-checkbox" type="checkbox" name="student_ids[]" value="<?= $st['id'] ?>">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAll(master) {
    const checkboxes = document.querySelectorAll('.st-checkbox');
    checkboxes.forEach(cb => cb.checked = master.checked);
}
</script>
</body>
</html>
