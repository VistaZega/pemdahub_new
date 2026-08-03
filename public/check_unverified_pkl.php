<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PklLog;
use App\Models\School;
use Illuminate\Support\Facades\DB;

// Only allow with secret for basic security
if (($_GET['secret'] ?? '') !== 'pembda99') {
    die('Unauthorized');
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Unverified PKL Logs</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Daftar Logbook PKL yang Belum Diverifikasi</h2>
";

try {
    $school = School::where('name', 'like', '%SMKS Pembda Nias%')->first();
    if (!$school) {
        echo "<p>School 'SMKS Pembda Nias' not found in database.</p>";
    } else {
        $logs = PklLog::with(['placement.student', 'placement.teacher'])
            ->whereHas('placement.student', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })
            ->where(function($q) {
                $q->where('status', '!=', 'approved')
                  ->where('status', '!=', 'verified')
                  ->orWhereNull('status');
            })
            ->get();
            
        echo "<p>Total Logbook Belum Verifikasi: " . $logs->count() . "</p>";
        
        if ($logs->count() > 0) {
            echo "<table>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Nama Siswa</th>
                    <th>Guru Pendamping</th>
                    <th>Tempat PKL (DUDI)</th>
                    <th>Status</th>
                </tr>";
                
            $no = 1;
            foreach ($logs as $log) {
                $studentName = $log->placement && $log->placement->student ? $log->placement->student->full_name : 'N/A';
                $teacherName = $log->placement && $log->placement->teacher ? $log->placement->teacher->full_name : 'N/A';
                $dudiName = $log->placement && $log->placement->dudi ? $log->placement->dudi->name : 'N/A';
                $logDate = $log->log_date ? $log->log_date->format('d M Y') : 'N/A';
                
                echo "<tr>
                    <td>{$no}</td>
                    <td>{$logDate}</td>
                    <td>{$studentName}</td>
                    <td>{$teacherName}</td>
                    <td>{$dudiName}</td>
                    <td>{$log->status}</td>
                </tr>";
                $no++;
            }
            echo "</table>";
        } else {
            echo "<p>Semua logbook sudah diverifikasi atau tidak ada data logbook.</p>";
        }
    }
} catch (\Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

echo "</body></html>";
