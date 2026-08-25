<?php
/**
 * Emergency Cache Clearer & Diagnostics
 * 
 * File PHP standalone yang tidak melewati routing Laravel.
 * Akses: https://perguruanpembda.com/clear-cache.php?secret=pembda99
 * 
 * HAPUS FILE INI SETELAH BERHASIL DIGUNAKAN!
 */

// Security check
if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    echo 'Access denied.';
    exit;
}

echo "<html><head><title>PembdaHUB Emergency Tool</title>";
echo "<style>body{font-family:monospace;background:#1a1a2e;color:#e0e0e0;padding:20px;line-height:1.8}";
echo ".ok{color:#00e676}.warn{color:#ffc107}.err{color:#ff5252}.info{color:#40c4ff}";
echo "h1{color:#bb86fc}h2{color:#03dac6}pre{background:#16213e;padding:15px;border-radius:8px;overflow-x:auto}";
echo "a.btn{display:inline-block;background:#bb86fc;color:#000;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:bold;margin:5px}</style></head><body>";

echo "<h1>🔧 PembdaHUB Emergency Tool</h1>";

// Reset OPcache if enabled
if (function_exists('opcache_reset')) {
    if (@opcache_reset()) {
        echo "<p class='ok'>✅ OPcache has been reset successfully (Memory cache cleared).</p>";
    } else {
        echo "<p class='warn'>⚠️ Failed to reset OPcache.</p>";
    }
} else {
    echo "<p class='info'>ℹ️ OPcache is not enabled on this server.</p>";
}

// Step 1: Delete cached files manually
echo "<h2>1. Hapus Cache Files</h2><pre>";
$cacheDir = __DIR__.'/../bootstrap/cache/';
$cacheFiles = ['config.php', 'routes-v7.php', 'packages.php', 'services.php', 'events.php'];
$deleted = 0;
foreach ($cacheFiles as $file) {
    $path = $cacheDir . $file;
    if (file_exists($path)) {
        if (@unlink($path)) {
            echo "<span class='ok'>✅ Deleted: bootstrap/cache/{$file}</span>\n";
            $deleted++;
        } else {
            echo "<span class='err'>❌ Failed to delete: bootstrap/cache/{$file}</span>\n";
        }
    } else {
        echo "<span class='info'>ℹ️  Not found (OK): bootstrap/cache/{$file}</span>\n";
    }
}
echo "</pre>";

$viewDir = __DIR__.'/../storage/framework/views/';
if (is_dir($viewDir)) {
    $viewFiles = glob($viewDir . '*.php');
    $vDeleted = 0;
    foreach ($viewFiles as $vFile) {
        if (@unlink($vFile)) $vDeleted++;
    }
    echo "<span class='ok'>✅ Cleared {$vDeleted} compiled Blade view files in storage/framework/views/</span>\n";
}

// Step 2: Bootstrap Laravel properly
echo "<h2>2. Bootstrap Laravel & Clear Cache</h2><pre>";
try {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    echo "<span class='ok'>✅ Laravel bootstrapped successfully</span>\n";

    $commands = [
        'route:clear' => 'Route Cache',
        'config:clear' => 'Config Cache',
        'cache:clear' => 'Application Cache',
        'view:clear' => 'View Cache',
        'storage:link' => 'Storage Link (Fix Audio/Image Uploads 404)',
    ];
    
    foreach ($commands as $cmd => $label) {
        try {
            $output = new \Symfony\Component\Console\Output\BufferedOutput();
            \Illuminate\Support\Facades\Artisan::call($cmd, [], $output);
            echo "<span class='ok'>✅ {$label}: " . trim($output->fetch()) . "</span>\n";
        } catch (\Exception $e) {
            echo "<span class='warn'>⚠️  {$label}: " . $e->getMessage() . "</span>\n";
        }
    }

    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('lms_materials')) {
            \Illuminate\Support\Facades\DB::table('lms_materials')->where('file_url', 'like', '%k7a9s8u190w%')->orWhere('content', 'like', '%k7a9s8u190w%')->update(['file_url' => 'https://www.youtube.com/watch?v=kYJv8y-f-r0', 'content' => 'Simak video animasi berikut mengenai cara menemukan akar kuadrat.']);
            \Illuminate\Support\Facades\DB::table('lms_materials')->where('file_url', 'like', '%s8a901k_abc%')->orWhere('content', 'like', '%s8a901k_abc%')->update(['file_url' => 'https://www.youtube.com/watch?v=R-PZ6iL1QyU', 'content' => 'Simak penjelasan visual mengenai hubungan diskriminan dengan grafik parabola.']);
            \Illuminate\Support\Facades\DB::table('lms_materials')->where('file_url', 'like', '%p901k_lmn_xyz%')->orWhere('content', 'like', '%p901k_lmn_xyz%')->update(['file_url' => 'https://www.youtube.com/watch?v=cnL6ekiZXEc', 'content' => 'Simak eksperimen gerak parabola di laboratorium fisika berikut ini.']);
            echo "<span class='ok'>✅ LMS Video Links (YouTube ID) Auto-Repaired successfully</span>\n";
        }

        // Auto-sync LMS Enrollments & Materials
        try {
            $lmsService = app(\App\Services\LmsEnrollmentService::class);
            $syncRes = $lmsService->syncAll();
            echo "<span class='ok'>🎓 LMS Sync: {$syncRes['courses_synced']} Kursus & {$syncRes['classrooms_synced']} Rombel diproses, {$syncRes['new_enrollments_created']} pendaftaran siswa baru terhubung.</span>\n";
            if (!empty($syncRes['rogue_cross_school_enrollments_cleaned'])) {
                echo "<span class='ok'>🧹 LMS Clean: {$syncRes['rogue_cross_school_enrollments_cleaned']} pendaftaran silang sekolah dibersihkan.</span>\n";
            }
        } catch (\Exception $e) {
            echo "<span class='warn'>⚠️ LMS Sync Warning: " . htmlspecialchars($e->getMessage()) . "</span>\n";
        }

        // Auto-migrate & seed Pembda Tower (Menara Prestasi)
        if (!\Illuminate\Support\Facades\Schema::hasTable('pembda_tower_bricks')) {
            try {
                $output = new \Symfony\Component\Console\Output\BufferedOutput();
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true], $output);
                echo "<span class='ok'>🏰 Pembda Tower Tables Migrated successfully</span>\n";
            } catch (\Exception $e) {
                echo "<span class='warn'>⚠️ Tower Migration Warning: " . $e->getMessage() . "</span>\n";
            }
        } else {
            // Clean resetting of tower bricks and seed fresh sample bricks
            try {
                \Illuminate\Support\Facades\DB::table('pembda_tower_brick_likes')->delete();
                \Illuminate\Support\Facades\DB::table('pembda_tower_bricks')->delete();

                $sysUser = \App\Models\User::whereIn('role', ['superadmin', 'admin_yayasan', 'admin', 'guru'])->first() ?? \App\Models\User::first();
                if ($sysUser) {
                    $pastDate = \Carbon\Carbon::now()->subDays(2);
                    $samples = [
                        ['msg' => 'Selamat Datang di Menara Prestasi Pembda! Mari bersatu membangun masa depan.', 'color' => 'indigo'],
                        ['msg' => 'Semangat belajar untuk seluruh siswa SD, SMP, SMA, dan SMK Pembda!', 'color' => 'amber'],
                        ['msg' => 'Salam hormat untuk Bapak/Ibu Guru dan Pengurus Yayasan Perguruan Pembda Nias.', 'color' => 'emerald'],
                        ['msg' => 'Inovasi, Integritas, dan Prestasi Tanpa Batas!', 'color' => 'purple'],
                        ['msg' => 'Sukses untuk ujian dan kegiatan belajar mengajar minggu ini!', 'color' => 'rose'],
                        ['msg' => 'Bersama Perguruan Pembda, kita pasti bisa menggapai cita-cita tinggi!', 'color' => 'cyan'],
                    ];
                    foreach ($samples as $idx => $s) {
                        \App\Models\PembdaTowerBrick::create([
                            'user_id' => $sysUser->id,
                            'school_id' => $sysUser->school_id,
                            'message' => $s['msg'],
                            'color' => $s['color'],
                            'brick_number' => $idx + 1,
                            'likes_count' => rand(3, 12),
                            'created_at' => $pastDate,
                            'updated_at' => $pastDate,
                        ]);
                    }
                }
                echo "<span class='ok'>🧱 Pembda Tower database reset & 6 sample bricks seeded successfully</span>\n";
            } catch (\Exception $e) {
                echo "<span class='warn'>⚠️ Tower Reset Error: " . htmlspecialchars($e->getMessage()) . "</span>\n";
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('puzzles') && \Illuminate\Support\Facades\Schema::hasTable('puzzle_pieces')) {
            $latestPuzzle = \App\Models\Puzzle::latest()->first();
            if (!$latestPuzzle) {
                $latestPuzzle = \App\Models\Puzzle::create([
                    'title' => 'Esports Championship (Minggu 1)',
                    'image_path' => 'puzzles/pembda_puzzle_1.png',
                    'grid_x' => 10,
                    'grid_y' => 5,
                    'is_active' => true,
                ]);
            }

            // Non-aktifkan puzzle lain agar hanya 1 puzzle yang aktif
            \App\Models\Puzzle::where('id', '!=', $latestPuzzle->id)->update(['is_active' => false]);
            $latestPuzzle->update(['is_active' => true]);

            // Bersihkan kepingan jika terjadi duplikasi
            $pieceCount = \App\Models\PuzzlePiece::where('puzzle_id', $latestPuzzle->id)->count();
            if ($pieceCount !== 50) {
                \App\Models\PuzzlePiece::where('puzzle_id', $latestPuzzle->id)->delete();
                $piecesData = [];
                for ($i = 0; $i < 50; $i++) {
                    $piecesData[] = [
                        'puzzle_id' => $latestPuzzle->id,
                        'piece_index' => $i,
                        'is_placed' => false,
                        'placed_by_user_id' => null,
                        'placed_at' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                \App\Models\PuzzlePiece::insert($piecesData);
            }

            // Reset kepingan agar 10 keping terpasang (bonus) dan 40 keping belum terpasang
            \App\Models\PuzzlePiece::where('puzzle_id', $latestPuzzle->id)->update([
                'is_placed' => false,
                'placed_by_user_id' => null,
                'placed_at' => null,
            ]);

            $bonusIds = \App\Models\PuzzlePiece::where('puzzle_id', $latestPuzzle->id)
                ->inRandomOrder()
                ->limit(10)
                ->pluck('id');

            \App\Models\PuzzlePiece::whereIn('id', $bonusIds)->update([
                'is_placed' => true,
                'placed_at' => now(),
            ]);

            $placedNow = \App\Models\PuzzlePiece::where('puzzle_id', $latestPuzzle->id)->where('is_placed', true)->count();
            $unplacedNow = \App\Models\PuzzlePiece::where('puzzle_id', $latestPuzzle->id)->where('is_placed', false)->count();
            echo "<span class='ok'>🧩 Puzzle Auto-Repair OK: '{$latestPuzzle->title}' (Terpasang: {$placedNow}, Tersedia di Kiri: {$unplacedNow})</span>\n";
        }
    } catch (\Exception $e) {
        // ignore if table not ready
    }
} catch (\Exception $e) {
    echo "<span class='err'>❌ Bootstrap error: " . $e->getMessage() . "</span>\n";
}
echo "</pre>";

// Step 3: Run migrations if requested
if (isset($_GET['migrate']) && $_GET['migrate'] === 'yes') {
    echo "<h2>3. Menjalankan Migrasi Database</h2><pre>";
    try {
        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true], $output);
        echo "<span class='ok'>" . htmlspecialchars($output->fetch()) . "</span>\n";
        
        echo "\n<span class='info'>--- Running Survey Seeder ---</span>\n";
        $output2 = new \Symfony\Component\Console\Output\BufferedOutput();
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'SurveySeeder', '--force' => true], $output2);
        echo "<span class='ok'>" . htmlspecialchars($output2->fetch()) . "</span>\n";

        echo "\n<span class='info'>--- Running Puzzle Seeder ---</span>\n";
        $outputPuzzle = new \Symfony\Component\Console\Output\BufferedOutput();
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'PuzzleSeeder', '--force' => true], $outputPuzzle);
        echo "<span class='ok'>" . htmlspecialchars($outputPuzzle->fetch()) . "</span>\n";

        echo "\n<span class='info'>--- Running TEFA Employee Seeder ---</span>\n";
        $output3 = new \Symfony\Component\Console\Output\BufferedOutput();
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'TefaEmployeeSeeder', '--force' => true], $output3);
        echo "<span class='ok'>" . htmlspecialchars($output3->fetch()) . "</span>\n";

        echo "\n<span class='info'>--- Step 1: Creating missing User accounts for Employees ---</span>\n";
        $unlinkedEmployees = \App\Models\Employee::whereNull('user_id')->get();
        $createdUsersCount = 0;
        foreach ($unlinkedEmployees as $employee) {
            $firstName = strtolower(explode(' ', trim($employee->full_name))[0]);
            $firstName = preg_replace('/[^a-z0-9]/', '', $firstName);
            if (empty($firstName)) {
                $firstName = 'pegawai' . rand(100, 999);
            }

            $email = $employee->email ?: ($firstName . '@pembdahub.com');
            $username = $firstName;
            $counter = 1;

            while (\App\Models\User::where('email', $email)->orWhere('username', $username)->exists()) {
                $email = $firstName . $counter . ($employee->email ? ('_' . $employee->email) : '@pembdahub.com');
                if (!str_contains($email, '@')) {
                    $email = $firstName . $counter . '@pembdahub.com';
                }
                $username = $firstName . $counter;
                $counter++;
            }

            $role = $employee->employee_type === 'guru' ? 'guru' : 'pegawai';

            $user = \App\Models\User::create([
                'name' => $employee->full_name,
                'email' => $email,
                'username' => $username,
                'password' => \Illuminate\Support\Facades\Hash::make('pembdahub2026'),
                'role' => $role,
                'school_id' => $employee->school_id,
                'is_active' => $employee->is_active,
            ]);

            $employee->user_id = $user->id;
            $employee->save();
            $createdUsersCount++;
            echo "<span class='ok'>👤 Created User account for: " . htmlspecialchars($employee->full_name) . " (Email: {$email}, Username: {$username})</span>\n";
        }
        echo "Total User accounts created: <b>{$createdUsersCount}</b>\n";

        echo "\n<span class='info'>--- Step 2: Creating missing Teacher records for Employees of type 'guru' ---</span>\n";
        $guruEmployees = \App\Models\Employee::where('employee_type', 'guru')->get();
        $createdTeachersCount = 0;
        foreach ($guruEmployees as $employee) {
            $hasTeacherRecord = \App\Models\Teacher::where('employee_id', $employee->id)->exists();
            if (!$hasTeacherRecord) {
                $teacherCode = $employee->employee_code ?: ('GR' . str_pad($employee->id, 3, '0', STR_PAD_LEFT));
                
                $teacher = \App\Models\Teacher::create([
                    'employee_id' => $employee->id,
                    'user_id' => $employee->user_id,
                    'school_id' => $employee->school_id,
                    'teacher_code' => $teacherCode,
                    'full_name' => $employee->full_name,
                    'gender' => $employee->gender,
                    'birth_place' => $employee->birth_place,
                    'birth_date' => $employee->birth_date,
                    'religion' => $employee->religion,
                    'address' => $employee->address,
                    'phone' => $employee->phone,
                    'photo' => $employee->photo,
                    'is_active' => $employee->is_active,
                ]);
                $createdTeachersCount++;
                echo "<span class='ok'>🏫 Created Teacher profile for: " . htmlspecialchars($employee->full_name) . " (Code: {$teacherCode})</span>\n";
            }
        }
        echo "Total Teacher profiles created: <b>{$createdTeachersCount}</b>\n";

        echo "\n<span class='info'>--- Step 3: Syncing user_id values between Teachers, Employees & Users ---</span>\n";
        $teachers = \App\Models\Teacher::all();
        $syncedTeachers = 0;
        foreach ($teachers as $teacher) {
            $updated = false;
            
            if ($teacher->employee_id) {
                $employee = \App\Models\Employee::find($teacher->employee_id);
                if ($employee && $employee->user_id) {
                    if ($teacher->user_id !== $employee->user_id) {
                        $teacher->user_id = $employee->user_id;
                        $teacher->save();
                        $updated = true;
                    }
                }
            }
            
            if (!$teacher->user_id) {
                $employee = \App\Models\Employee::where('full_name', $teacher->full_name)->first();
                if ($employee) {
                    $teacher->employee_id = $employee->id;
                    if ($employee->user_id) {
                        $teacher->user_id = $employee->user_id;
                        $updated = true;
                    }
                    $teacher->save();
                }
            }
            
            if (!$teacher->user_id) {
                $user = \App\Models\User::where('name', $teacher->full_name)
                    ->where('role', 'guru')
                    ->first();
                if ($user) {
                    $teacher->user_id = $user->id;
                    $teacher->save();
                    $updated = true;
                    
                    if ($teacher->employee_id) {
                        $employee = \App\Models\Employee::find($teacher->employee_id);
                        if ($employee && !$employee->user_id) {
                            $employee->user_id = $user->id;
                            $employee->save();
                        }
                    }
                }
            }

            if ($updated) {
                $syncedTeachers++;
                echo "<span class='ok'>🔗 Synced Teacher: " . htmlspecialchars($teacher->full_name) . " to User ID: " . $teacher->user_id . "</span>\n";
            }
        }
        echo "Total Guru yang disinkronkan: <b>{$syncedTeachers}</b>\n";
    } catch (\Exception $e) {
        echo "<span class='err'>❌ Migration error: " . htmlspecialchars($e->getMessage()) . "</span>\n";
    }
    echo "</pre>";
} else if (isset($_GET['reset_puzzle']) && $_GET['reset_puzzle'] === 'yes') {
    echo "<h2>3. Reset Puzzle Pembda Colabs</h2><pre>";
    try {
        // Reset semua keping puzzle ke belum terpasang
        $puzzle = \App\Models\Puzzle::where('is_active', true)->latest()->first();
        if ($puzzle) {
            \App\Models\PuzzlePiece::where('puzzle_id', $puzzle->id)->update([
                'is_placed' => false,
                'placed_by_user_id' => null,
                'placed_at' => null,
            ]);

            // Pasang 10 keping acak sebagai bonus awal dari Sistem
            $bonusIds = \App\Models\PuzzlePiece::where('puzzle_id', $puzzle->id)
                ->inRandomOrder()
                ->limit(10)
                ->pluck('id');

            \App\Models\PuzzlePiece::whereIn('id', $bonusIds)->update([
                'is_placed' => true,
                'placed_at' => now(),
            ]);

            $totalPieces = \App\Models\PuzzlePiece::where('puzzle_id', $puzzle->id)->count();
            $placedNow = \App\Models\PuzzlePiece::where('puzzle_id', $puzzle->id)->where('is_placed', true)->count();
            echo "<span class='ok'>✅ Puzzle '{$puzzle->title}' berhasil direset!</span>\n";
            echo "<span class='ok'>   Total keping: {$totalPieces}</span>\n";
            echo "<span class='ok'>   Terpasang (Bonus Sistem): {$placedNow}</span>\n";
            echo "<span class='ok'>   Tersisa untuk siswa: " . ($totalPieces - $placedNow) . "</span>\n";
        } else {
            echo "<span class='err'>❌ Tidak ada puzzle aktif!</span>\n";
        }
    } catch (\Exception $e) {
        echo "<span class='err'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</span>\n";
    }
    echo "</pre>";
} else {
    echo "<h2>3. Migrasi Database & Restore Data</h2>";
    echo "<a class='btn' href='?secret=pembda99&migrate=yes'>▶️ Jalankan Migrasi & Seeder</a><br><br>";
    echo "<b>🧩 Puzzle Pembda Colabs:</b> ";
    echo "<a class='btn' style='background:#40c4ff;' href='?secret=pembda99&reset_puzzle=yes'>🔄 Reset Puzzle (10 keping awal)</a><br><br>";
    echo "<b>Restore TP 2026/2027:</b> ";
    echo "<a class='btn' style='background:#03dac6;' href='/restore-tp-2026?secret=pembda99&dry_run=1' target='_blank'>🔍 Test Dry Run (Simulasi)</a>";
    echo "<a class='btn' style='background:#00e676;' href='/restore-tp-2026?secret=pembda99' target='_blank'>▶️ Restore Sekarang</a><br><br>";
    echo "<b>Restore TP 2025/2026:</b> ";
    echo "<a class='btn' style='background:#ff9800;' href='/restore-tp-2025?secret=pembda99&dry_run=1' target='_blank'>🔍 Test Dry Run (Simulasi)</a>";
    echo "<a class='btn' style='background:#ff5722;' href='/restore-tp-2025?secret=pembda99' target='_blank'>▶️ Restore Sekarang</a><br><br>";
    echo "<b>⚡ Background Queue Jobs:</b> ";
    try {
        $qPending = \Illuminate\Support\Facades\DB::table('jobs')->count();
        $qFailed = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
        echo "<span class='info'>[Pending: {$qPending} | Failed: {$qFailed}]</span> ";
    } catch (\Throwable $e) {}
    echo "<a class='btn' style='background:#e040fb; color:#fff;' href='/process-queue?secret=pembda99' target='_blank'>▶️ Proses Antrean Jobs Sekarang</a>";
    echo "<p class='info'>Klik tombol di atas untuk menjalankan migrasi, memproses antrean background jobs, atau memulihkan data TP dari backup.</p>";
}

// Step 4: Diagnostics
echo "<h2>4. Diagnostik</h2><pre>";
try {
    // Check registered routes for surveys
    $routes = app('router')->getRoutes();
    $surveyRoutes = [];
    foreach ($routes as $route) {
        if (str_contains($route->uri(), 'survey')) {
            $surveyRoutes[] = $route->methods()[0] . ' /' . $route->uri();
        }
    }
    
    if (count($surveyRoutes) > 0) {
        echo "<span class='ok'>✅ Survey routes terdaftar (" . count($surveyRoutes) . " routes):</span>\n";
        foreach ($surveyRoutes as $r) {
            echo "   " . $r . "\n";
        }
    } else {
        echo "<span class='err'>❌ Tidak ada survey routes yang terdaftar!</span>\n";
    }
    
    echo "\n<span class='info'>=== DIRECTORY LISTING OF public_html/ ===</span>\n";
    $dirItems = scandir(__DIR__);
    foreach ($dirItems as $item) {
        if ($item !== '.' && $item !== '..') {
            $isDir = is_dir(__DIR__ . '/' . $item);
            echo ($isDir ? "<span class='warn'>[DIR]  " : "<span class='ok'>[FILE] ") . htmlspecialchars($item) . "</span>\n";
        }
    }
    
    echo "\n<span class='info'>=== GURU DASHBOARD ROUTE DETAIL ===</span>\n";
    $guruRoute = app('router')->getRoutes()->getByName('guru.dashboard');
    if ($guruRoute) {
        echo "<span class='ok'>✅ Route 'guru.dashboard' FOUND!</span>\n";
        echo "   URI: " . $guruRoute->uri() . "\n";
        echo "   Methods: " . implode(', ', $guruRoute->methods()) . "\n";
        echo "   Action: " . $guruRoute->getActionName() . "\n";
        echo "   Middleware: " . implode(', ', $guruRoute->middleware()) . "\n";
    } else {
        echo "<span class='err'>❌ Route 'guru.dashboard' NOT FOUND!</span>\n";
    }
    
    echo "\n<span class='info'>=== RESTORE ROUTES STATUS ===</span>\n";
    $hasRestore2026 = false;
    $hasRestore2025 = false;
    foreach (app('router')->getRoutes() as $r) {
        if ($r->uri() === 'restore-tp-2026') $hasRestore2026 = true;
        if ($r->uri() === 'restore-tp-2025') $hasRestore2025 = true;
    }
    echo ($hasRestore2026 ? "<span class='ok'>✅ Route '/restore-tp-2026' TERDAFTAR DAN SIAP!</span>\n" : "<span class='err'>❌ Route '/restore-tp-2026' BELUM ADA! (Lakukan Git Pull di Hostinger & Clear Cache)</span>\n");
    echo ($hasRestore2025 ? "<span class='ok'>✅ Route '/restore-tp-2025' TERDAFTAR DAN SIAP!</span>\n" : "<span class='err'>❌ Route '/restore-tp-2025' BELUM ADA! (Lakukan Git Pull di Hostinger & Clear Cache)</span>\n");
    echo "\n";
    
    // Check if survey & game tables exist
    $tables = ['surveys', 'survey_questions', 'survey_answers', 'survey_responses', 'lms_games', 'lms_game_attempts'];
    foreach ($tables as $table) {
        try {
            $exists = \Illuminate\Support\Facades\Schema::hasTable($table);
            echo $exists 
                ? "<span class='ok'>✅ Tabel '{$table}' ada</span>\n"
                : "<span class='err'>❌ Tabel '{$table}' TIDAK ADA - perlu migrasi!</span>\n";
        } catch (\Exception $e) {
            echo "<span class='warn'>⚠️  Tidak bisa cek tabel '{$table}': " . $e->getMessage() . "</span>\n";
        }
    }
    
    echo "\n<span class='info'>=== GURU MAPPING INTEGRITY CHECK ===</span>\n";
    try {
        $unlinkedTeachers = \App\Models\Teacher::whereNull('user_id')->count();
        if ($unlinkedTeachers > 0) {
            echo "<span class='err'>❌ Terdapat {$unlinkedTeachers} Guru dengan 'user_id' NULL di tabel 'teachers'! Hal ini menyebabkan Error 404 ketika guru login.</span>\n";
            echo "<span class='info'>Silakan jalankan migrasi database di Step 3 untuk sinkronisasi otomatis.</span>\n";
            
            $list = \App\Models\Teacher::whereNull('user_id')->take(5)->get();
            foreach ($list as $t) {
                echo "   - {$t->full_name} (Code: {$t->teacher_code})\n";
            }
        } else {
            echo "<span class='ok'>✅ Semua Guru di tabel 'teachers' sudah memiliki 'user_id'.</span>\n";
        }
    } catch (\Exception $e) {
        echo "<span class='warn'>⚠️  Gagal mengecek integritas pemetaan guru: " . $e->getMessage() . "</span>\n";
    }
    
    echo "\n";
    
    // Check APP_URL
    echo "<span class='info'>APP_URL: " . config('app.url') . "</span>\n";
    echo "<span class='info'>APP_ENV: " . config('app.env') . "</span>\n";
    echo "<span class='info'>Base Path: " . base_path() . "</span>\n";
    echo "<span class='info'>Public Path: " . public_path() . "</span>\n";
    
    echo "\n<span class='info'>=== LMS ASSIGNMENT 412 / COURSE 313 DIAGNOSTIC ===</span>\n";
    try {
        $ass = \App\Models\LmsAssignment::find(412);
        if ($ass) {
            $c = $ass->course;
            echo "Assignment: {$ass->title} (ID: {$ass->id}, is_group: {$ass->is_group_assignment})\n";
            echo "Course: " . ($c->course_name ?? $c->name) . " (ID: {$c->id}, School: {$c->school_id}, Subject: {$c->subject_id}, Classroom: {$c->classroom_id})\n";
            $lClasses = \App\Models\LmsClass::where('course_id', $c->id)->get();
            echo "LmsClasses count: " . $lClasses->count() . "\n";
            foreach ($lClasses as $lc) {
                $cr = $lc->classroom;
                echo "  - LmsClass {$lc->id}: Classroom ID {$lc->classroom_id} (" . ($cr?->class_name ?? 'NULL') . "), School: {$lc->school_id}\n";
                $scCount = \App\Models\StudentClass::where('classroom_id', $lc->classroom_id)->count();
                echo "    -> StudentClass count in this classroom: {$scCount}\n";
                $enrCount = \App\Models\LmsEnrollment::where('lms_class_id', $lc->id)->count();
                echo "    -> LmsEnrollment count in this lms_class: {$enrCount}\n";
            }
            
            $ctrl = app(\App\Http\Controllers\Guru\LmsAssignmentController::class);
            $refMethod = new \ReflectionMethod($ctrl, 'getEnrolledStudentsForCourse');
            $refMethod->setAccessible(true);
            $stds = $refMethod->invoke($ctrl, $c);
            echo "getEnrolledStudentsForCourse returned: " . $stds->count() . " students.\n";
            foreach ($stds->take(10) as $s) {
                echo "  * Student #{$s->id}: " . ($s->user->name ?? $s->full_name) . " (NISN: {$s->nisn})\n";
            }
        } else {
            echo "Assignment 412 not found.\n";
        }
    } catch (\Throwable $e) {
        echo "<span class='err'>LMS Diag Error: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "</span>\n";
    }

    echo "\n<span class='info'>=== CONTENT OF public_html/.htaccess ===</span>\n";
    $htaccessPath = __DIR__ . '/.htaccess';
    if (file_exists($htaccessPath)) {
        echo htmlspecialchars(file_get_contents($htaccessPath)) . "\n";
    } else {
        echo "public_html/.htaccess not found!\n";
    }
    
} catch (\Exception $e) {
    echo "<span class='err'>❌ Diagnostik error: " . $e->getMessage() . "</span>\n";
}
echo "</pre>";

echo "<hr><p class='warn'>⚠️ <b>PENTING:</b> Hapus file clear-cache.php setelah selesai!</p>";
echo "</body></html>";
