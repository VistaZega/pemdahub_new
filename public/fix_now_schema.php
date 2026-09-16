<?php
/**
 * SELF-CONTAINED AUTO-FIX - creates schema directly without migration files
 * Akses: https://perguruanpembda.com/fix_now_schema.php?secret=pembda99
 * Setelah selesai, HAPUS file ini!
 */
if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403); die('Forbidden');
}

@set_time_limit(120);
ini_set('display_errors', 1);
header('Content-Type: text/plain; charset=utf-8');

echo "=== PEMBDAHUB AUTO-FIX START ===\n\n";

// Bootstrap Laravel
$root = realpath(__DIR__ . '/..');
if (!file_exists("$root/vendor/autoload.php")) {
    $root = '/var/www/pembdahub';
}
if (!file_exists("$root/artisan")) {
    die("ERROR: Cannot find Laravel root\n");
}

require_once "$root/vendor/autoload.php";
$app = require_once "$root/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

$fixes = 0;

// FIX 1: Create devices table
if (!Schema::hasTable('devices')) {
    echo "FIX 1: Creating devices table...\n";
    Schema::create('devices', function (Blueprint $table) {
        $table->id();
        $table->string('device_code', 50)->unique();
        $table->string('name', 100)->nullable();
        $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('set null');
        $table->string('type', 50)->nullable();
        $table->string('status', 20)->default('active');
        $table->timestamp('last_seen_at')->nullable();
        $table->timestamps();
    });
    echo "✅ devices table created\n";
    $fixes++;
} else {
    echo "⏭️ devices table already exists\n";
}

// FIX 2: Add status column to academic_years
if (Schema::hasTable('academic_years') && !Schema::hasColumn('academic_years', 'status')) {
    echo "FIX 2: Adding status column to academic_years...\n";
    Schema::table('academic_years', function (Blueprint $table) {
        $table->string('status', 20)->nullable()->after('is_active');
    });
    DB::table('academic_years')->where('is_active', true)->whereNull('status')->update(['status' => 'aktif']);
    DB::table('academic_years')->where('is_active', false)->whereNull('status')->update(['status' => 'nonaktif']);
    echo "✅ status column added to academic_years\n";
    $fixes++;
} else {
    echo "⏭️ academic_years.status already exists\n";
}

// FIX 3: Update AcademicYear model fillable
$modelFile = "$root/app/Models/AcademicYear.php";
if (file_exists($modelFile)) {
    $content = file_get_contents($modelFile);
    if (strpos($content, "'status'") === false) {
        $content = str_replace("'is_active',", "'is_active',\n        'status',", $content);
        file_put_contents($modelFile, $content);
        echo "✅ AcademicYear.php updated (+status fillable)\n";
        $fixes++;
    } else {
        echo "⏭️ AcademicYear.php already has status\n";
    }
}

// FIX 4: Create Device model
$deviceModel = "$root/app/Models/Device.php";
if (!file_exists($deviceModel)) {
    $code = '<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        \'device_code\', \'name\', \'school_id\', \'type\', \'status\', \'last_seen_at\',
    ];

    protected $casts = [
        \'last_seen_at\' => \'datetime\',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
';
    file_put_contents($deviceModel, $code);
    echo "✅ Device model created\n";
    $fixes++;
} else {
    echo "⏭️ Device model already exists\n";
}

// FIX 5: Fix difficulty key in results.blade.php
$viewFile = "$root/resources/views/guru/cbt/exams/results.blade.php";
if (file_exists($viewFile)) {
    $viewContent = file_get_contents($viewFile);
    $original = $viewContent;
    $viewContent = str_replace("\$item['difficulty']", "\$item['difficulty'] ?? '-'", $viewContent);
    $viewContent = str_replace("\$item['difficulty_index']", "\$item['difficulty_index'] ?? '-'", $viewContent);
    if ($viewContent !== $original) {
        file_put_contents($viewFile, $viewContent);
        echo "✅ results.blade.php difficulty key fixed\n";
        $fixes++;
    } else {
        echo "⏭️ results.blade.php already fixed\n";
    }
} else {
    echo "⏭️ results.blade.php not found (might use compiled cache)\n";
}

// Clear view cache
try {
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    echo "✅ View cache cleared\n";
} catch (\Throwable $e) {
    echo "⚠️ view:clear: " . $e->getMessage() . "\n";
}

echo "\n=== COMPLETE: $fixes fixes applied ===\n";
echo "⚠️ DELETE THIS FILE AFTER VERIFICATION!\n";
