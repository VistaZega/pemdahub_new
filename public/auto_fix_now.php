<?php
/**
 * AUTO-FIX: Create missing devices table, add status to academic_years, fix difficulty key
 * Access: https://perguruanpembda.com/auto_fix_now.php?secret=pembda99
 */
if (($_GET['secret'] ?? '') !== 'pembda99') { http_response_code(403); die('Forbidden'); }

@set_time_limit(120);
header('Content-Type: text/html; charset=utf-8');

$root = realpath(__DIR__ . '/../');
if (!$root || !file_exists("$root/artisan")) {
    $root = '/var/www/pembdahub';
    if (!file_exists("$root/artisan")) {
        $root = '/home/u474310197/domains/perguruanpembda.com/public_html/pembdahub';
    }
}

echo "<pre>\n";
echo "Root: $root\n\n";

// 1. Create devices table migration
$migration1 = "$root/database/migrations/2026_09_16_070000_create_devices_table.php";
if (!file_exists($migration1)) {
    $content = '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(\'devices\', function (Blueprint $table) {
            $table->id();
            $table->string(\'device_code\', 50)->unique()->comment(\'Kode unik device/station\');
            $table->string(\'name\', 100)->nullable()->comment(\'Nama device\');
            $table->foreignId(\'school_id\')->nullable()->constrained(\'schools\')->onDelete(\'set null\');
            $table->string(\'type\', 50)->nullable()->comment(\'Jenis device: ESP32, NodeMCU\');
            $table->string(\'status\', 20)->default(\'active\');
            $table->timestamp(\'last_seen_at\')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(\'devices\');
    }
};
';
    file_put_contents($migration1, $content);
    echo "✅ Created: database/migrations/2026_09_16_070000_create_devices_table.php\n";
} else {
    echo "⏭️ Already exists: create_devices_table\n";
}

// 2. Add status to academic_years
$migration2 = "$root/database/migrations/2026_09_16_070001_add_status_to_academic_years_table.php";
if (!file_exists($migration2)) {
    $content = '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(\'academic_years\', function (Blueprint $table) {
            $table->string(\'status\', 20)->nullable()->after(\'is_active\');
        });
        DB::table(\'academic_years\')->where(\'is_active\', true)->whereNull(\'status\')->update([\'status\' => \'aktif\']);
        DB::table(\'academic_years\')->where(\'is_active\', false)->whereNull(\'status\')->update([\'status\' => \'nonaktif\']);
    }

    public function down(): void
    {
        Schema::table(\'academic_years\', function (Blueprint $table) {
            $table->dropColumn(\'status\');
        });
    }
};
';
    file_put_contents($migration2, $content);
    echo "✅ Created: database/migrations/2026_09_16_070001_add_status_to_academic_years_table.php\n";
} else {
    echo "⏭️ Already exists: add_status_to_academic_years\n";
}

// 3. Create Device model
$modelFile = "$root/app/Models/Device.php";
if (!file_exists($modelFile)) {
    $content = '<?php

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
    file_put_contents($modelFile, $content);
    echo "✅ Created: app/Models/Device.php\n";
} else {
    echo "⏭️ Already exists: Device.php\n";
}

// 4. Add 'status' to AcademicYear fillable
$acYearFile = "$root/app/Models/AcademicYear.php";
if (file_exists($acYearFile)) {
    $acContent = file_get_contents($acYearFile);
    if (strpos($acContent, "'status'") === false) {
        $acContent = str_replace(
            "'is_active',",
            "'is_active',\n        'status',",
            $acContent
        );
        file_put_contents($acYearFile, $acContent);
        echo "✅ Updated: app/Models/AcademicYear.php (+status fillable)\n";
    } else {
        echo "⏭️ Already has status: AcademicYear.php\n";
    }
}

// 5. Fix results.blade.php difficulty key
$viewFile = "$root/resources/views/guru/cbt/exams/results.blade.php";
if (file_exists($viewFile)) {
    $viewContent = file_get_contents($viewFile);
    $original = $viewContent;
    $viewContent = str_replace(
        "\$item['difficulty']",
        "\$item['difficulty'] ?? '-'",
        $viewContent
    );
    $viewContent = str_replace(
        "\$item['difficulty_index']",
        "\$item['difficulty_index'] ?? '-'",
        $viewContent
    );
    if ($viewContent !== $original) {
        file_put_contents($viewFile, $viewContent);
        echo "✅ Fixed: results.blade.php (difficulty key null coalescing)\n";
    } else {
        echo "⏭️ Already fixed: results.blade.php\n";
    }
}

// 6. Run migrations
echo "\n--- Running Artisan Migrate ---\n";
$output = shell_exec("cd $root && php artisan migrate --force 2>&1");
echo $output ?: "No output from artisan\n";

// 7. Clear cache
shell_exec("cd $root && php artisan view:clear 2>&1");
shell_exec("cd $root && php artisan cache:clear 2>&1");
echo "✅ Cache cleared\n";

echo "\n🎉 AUTO-FIX COMPLETE!\n";
echo "</pre>";
