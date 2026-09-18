<?php
/**
 * Standalone Routing & Directory Collision Diagnostic
 * 
 * Akses: https://perguruanpembda.com/diag_404.php?secret=pembda99
 */

if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    echo 'Access denied.';
    exit;
}

echo "<html><head><title>PembdaHUB Routing Diagnostic</title>";
echo "<style>body{font-family:monospace;background:#0f172a;color:#cbd5e1;padding:20px;line-height:1.6}";
echo ".ok{color:#4ade80}.warn{color:#facc15}.err{color:#f87171}.info{color:#38bdf8}";
echo "h1{color:#c084fc;margin-bottom:5px}h2{color:#2dd4bf;border-bottom:1px solid #334155;padding-bottom:5px;margin-top:30px}pre{background:#1e293b;padding:15px;border-radius:8px;overflow-x:auto;border:1px solid #334155}";
echo "table{width:100%;border-collapse:collapse;margin:15px 0}th,td{border:1px solid #334155;padding:10px;text-align:left}th{background:#1e293b;color:#38bdf8}</style></head><body>";

echo "<h1>🔍 PembdaHUB Routing & Directory Diagnostic</h1>";
echo "<p class='info'>Waktu Server: " . date('Y-m-d H:i:s T') . "</p>";

// 1. PHP & Server Variables
echo "<h2>1. Server & Execution Environment</h2><pre>";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Current File Path: " . __FILE__ . "\n";
echo "Current Directory (__DIR__): " . __DIR__ . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'Not set') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'Not set') . "\n";
echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'Not set') . "\n";
echo "base_path() equivalent: " . realpath(__DIR__ . '/../') . "\n";
echo "</pre>";

// 2. Directory Listing: public_html/
echo "<h2>2. Directory Listing of public_html/ (web root)</h2><pre>";
$webRoot = __DIR__;
if (is_dir($webRoot)) {
    $items = scandir($webRoot);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $fullPath = $webRoot . '/' . $item;
        $isDir = is_dir($fullPath);
        $isLink = is_link($fullPath);
        
        $type = $isDir ? '[DIR] ' : '[FILE]';
        if ($isLink) {
            $type = '[LINK]';
            $target = readlink($fullPath);
            echo "<span class='warn'>$type $item -> $target</span>\n";
        } else if ($isDir) {
            // Highlight directories named guru, siswa, admin
            if (in_array(strtolower($item), ['guru', 'siswa', 'admin'])) {
                echo "<span class='err'>$type $item  <-- ⚠️ CONFLICT: Directory named '$item' exists in web root!</span>\n";
            } else {
                echo "<span class='warn'>$type $item</span>\n";
            }
        } else {
            echo "<span class='ok'>$type $item</span>\n";
        }
    }
} else {
    echo "<span class='err'>Cannot access public_html directory.</span>\n";
}
echo "</pre>";

// 3. Directory Listing: public_html/pembdahub/
echo "<h2>3. Directory Listing of pembdahub/ (Laravel Root)</h2><pre>";
$laravelRoot = __DIR__ . '/pembdahub';
if (is_dir($laravelRoot)) {
    $items = scandir($laravelRoot);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $fullPath = $laravelRoot . '/' . $item;
        $isDir = is_dir($fullPath);
        $type = $isDir ? '[DIR] ' : '[FILE]';
        echo $isDir ? "<span class='warn'>$type $item</span>\n" : "<span class='ok'>$type $item</span>\n";
    }
} else {
    echo "<span class='warn'>ℹ️ Directory '/pembdahub' not found inside public_html. Checking if Laravel is placed at parent level...</span>\n";
    $parentDir = realpath(__DIR__ . '/../');
    echo "Parent directory path: $parentDir\n";
    if (is_dir($parentDir)) {
        $items = scandir($parentDir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $fullPath = $parentDir . '/' . $item;
            $isDir = is_dir($fullPath);
            $type = $isDir ? '[DIR] ' : '[FILE]';
            echo $isDir ? "<span class='warn'>$type $item</span>\n" : "<span class='ok'>$type $item</span>\n";
        }
    }
}
echo "</pre>";

// 4. Directory Listing: public_html/pembdahub/public/
echo "<h2>4. Directory Listing of pembdahub/public/</h2><pre>";
$laravelPublic = __DIR__ . '/pembdahub/public';
if (is_dir($laravelPublic)) {
    $items = scandir($laravelPublic);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $fullPath = $laravelPublic . '/' . $item;
        $isDir = is_dir($fullPath);
        $type = $isDir ? '[DIR] ' : '[FILE]';
        if ($isDir && in_array(strtolower($item), ['guru', 'siswa', 'admin'])) {
            echo "<span class='err'>$type $item  <-- ⚠️ CONFLICT: Directory named '$item' exists in Laravel public!</span>\n";
        } else {
            echo $isDir ? "<span class='warn'>$type $item</span>\n" : "<span class='ok'>$type $item</span>\n";
        }
    }
} else {
    echo "<span class='info'>Directory '/pembdahub/public' not found or not applicable.</span>\n";
}
echo "</pre>";

// 5. Test Laravel Bootstrap & Route Resolution
echo "<h2>5. Laravel Route & Config Status</h2><pre>";
try {
    // Determine bootstrap path
    $bootstrapPath = null;
    $autoloadPath = null;
    
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        $autoloadPath = __DIR__ . '/../vendor/autoload.php';
        $bootstrapPath = __DIR__ . '/../bootstrap/app.php';
    } elseif (file_exists(__DIR__ . '/pembdahub/vendor/autoload.php')) {
        $autoloadPath = __DIR__ . '/pembdahub/vendor/autoload.php';
        $bootstrapPath = __DIR__ . '/pembdahub/bootstrap/app.php';
    }
    
    if ($autoloadPath && $bootstrapPath) {
        echo "Autoload Path: $autoloadPath\n";
        echo "Bootstrap Path: $bootstrapPath\n";
        
        require $autoloadPath;
        $app = require_once $bootstrapPath;
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        
        echo "<span class='ok'>✅ Laravel bootstrapped successfully.</span>\n";
        echo "App URL: " . config('app.url') . "\n";
        echo "Base Path: " . base_path() . "\n";
        echo "Public Path: " . public_path() . "\n";
        
        // Check router
        $router = app('router');
        $routes = $router->getRoutes();
        echo "Total routes: " . count($routes) . "\n";
        
        // Find debug-menu-check
        $debugRoute = $routes->get('GET');
        $hasDebugRoute = false;
        foreach ($routes as $r) {
            if (str_contains($r->uri(), 'debug-menu-check')) {
                echo "<span class='ok'>✅ Route 'debug-menu-check' exists: URI=" . $r->uri() . "</span>\n";
                $hasDebugRoute = true;
                break;
            }
        }
        if (!$hasDebugRoute) {
            echo "<span class='err'>❌ Route 'debug-menu-check' NOT found in router!</span>\n";
        }

        // Find guru.dashboard
        $guruRoute = $routes->getByName('guru.dashboard');
        if ($guruRoute) {
            echo "<span class='ok'>✅ Route 'guru.dashboard' exists. URI=" . $guruRoute->uri() . "</span>\n";
        } else {
            echo "<span class='err'>❌ Route 'guru.dashboard' NOT found!</span>\n";
        }
        
        // Custom check for Abraham Zega
        echo "\n=== ALL SCHOOLS IN SERVER DATABASE ===\n";
        $allSchools = \App\Models\School::all();
        foreach ($allSchools as $sch) {
            echo "  School ID: {$sch->id} | Name: '{$sch->name}' | Type: '{$sch->type}'\n";
        }

        echo "\n=== DETAILED CHECK FOR ABRAHAM ZEGA ===\n";
        $users = \App\Models\User::where('name', 'like', '%ABRAHAM%')
            ->orWhere('username', 'like', '%abraham%')
            ->get();
            
        if ($users->isEmpty()) {
            echo "❌ User dengan nama/username mengandung 'ABRAHAM' tidak ditemukan di database!\n";
        } else {
            foreach ($users as $u) {
                echo "  User ID: {$u->id} | Name: '{$u->name}' | Username: '{$u->username}' | Role: '{$u->role}'\n";
                $student = $u->student;
                if ($student) {
                    echo "    -> Student ID: {$student->id} | Full Name: '{$student->full_name}' | Status: {$student->status} | School ID: {$student->school_id} (Type: " . ($student->school?->type ?? 'NULL') . ")\n";
                    
                    // Check student classes
                    $scs = \Illuminate\Support\Facades\DB::table('student_classes')
                        ->where('student_id', $student->id)
                        ->get();
                    echo "    -> Classroom relations in student_classes:\n";
                    if ($scs->isEmpty()) {
                        echo "       ❌ Tidak ada record di student_classes!\n";
                    } else {
                        foreach ($scs as $sc) {
                            $cls = \App\Models\Classroom::find($sc->classroom_id);
                            $ay = \App\Models\AcademicYear::find($sc->academic_year_id);
                            echo "       - Class: {$cls?->name} (ID: {$sc->classroom_id}, Grade: {$cls?->grade_level}) | Year: {$ay?->year} (ID: {$sc->academic_year_id}, Active: " . ($ay?->is_active ? 'YES' : 'NO') . ") | Status: {$sc->status}\n";
                        }
                    }
                    
                    // Simulate menu logic
                    $currentClass = $student->currentClassroom()->first();
                    echo "    -> Active Classroom via currentClassroom(): " . ($currentClass ? "{$currentClass->name} (Grade: {$currentClass->grade_level})" : "❌ NULL") . "\n";
                    
                    $schoolType = $student->school?->type;
                    $gradeLevel = $currentClass?->grade_level;
                    $isKelasXII = $gradeLevel == 12;
                    echo "    -> schoolType: [{$schoolType}] | gradeLevel: [{$gradeLevel}] | isKelasXII: " . ($isKelasXII ? 'YES' : 'NO') . "\n";
                    if ($isKelasXII && $schoolType === 'SMA') {
                        echo "      <span class='ok'>✅ MENU TUGAS AKHIR AKAN MUNCUL</span>\n";
                    } elseif ($isKelasXII && $schoolType === 'SMK') {
                        echo "      <span class='ok'>✅ MENU PROJECT AKHIR + PKL AK RAKAN MUNCUL</span>\n";
                    } else {
                        echo "      <span class='err'>❌ KONDISI MENU TIDAK TERPENUHI</span>\n";
                    }
                } else {
                    echo "    ❌ User tidak memiliki record Student!\n";
                }
            }
        }
        
        // Check academic years
        echo "\n=== ACADEMIC YEARS ===\n";
        $years = \App\Models\AcademicYear::all();
        foreach ($years as $y) {
            echo "  ID: {$y->id} | Year: {$y->year} | Active: " . ($y->is_active ? "🟢 ACTIVE" : "🔴 INACTIVE") . "\n";
        }

        // Print Pending Proposals
        echo "\n=== PENDING PROPOSALS ===\n";
        $pendingProposals = \App\Models\FinalProject::with(['student.school', 'student.user'])
            ->where('status', 'pending')
            ->get();
        if ($pendingProposals->isEmpty()) {
            echo "  No pending proposals found.\n";
        } else {
            foreach ($pendingProposals as $p) {
                echo "  Project ID: {$p->id} | Title: '{$p->title}'\n";
                echo "    -> Student: ID={$p->student_id} | Name='{$p->student?->full_name}' | School ID={$p->student?->school_id} (Type: {$p->student?->school?->type})\n";
            }
        }

        // Print Available Teachers
        echo "\n=== TEACHERS LIST ===\n";
        $allTeachers = \App\Models\Teacher::with('school')->get();
        if ($allTeachers->isEmpty()) {
            echo "  No teachers found.\n";
        } else {
            foreach ($allTeachers as $t) {
                echo "  Teacher ID: {$t->id} | Name: '{$t->full_name}' | School ID: {$t->school_id} (Type: {$t->school?->type})\n";
            }
        }

        // Simulate Controller logic
        echo "\n=== SIMULATING CONTROLLER LOGIC FOR SUPERADMIN ===\n";
        $isSA_test = true; // Yulianus is SuperAdmin
        $schoolId_test = 4; // Yayasan
        
        $smaSmkSchoolIds = \App\Models\School::whereIn('type', ['SMA', 'SMK'])->pluck('id')->toArray();
        echo "  SMA/SMK School IDs: " . json_encode($smaSmkSchoolIds) . "\n";
        
        $teachersQuery = \App\Models\Teacher::with('school');
        if (!$isSA_test) {
            $teachersQuery->where('school_id', $schoolId_test);
        } else {
            $teachersQuery->whereIn('school_id', $smaSmkSchoolIds);
        }
        $controllerTeachers = $teachersQuery->get();
        echo "  Total teachers loaded by query: " . $controllerTeachers->count() . "\n";
        foreach ($controllerTeachers->take(5) as $ct) {
            echo "    - Teacher ID: {$ct->id} | Name: '{$ct->full_name}' | School ID: {$ct->school_id} (Type: {$ct->school?->type})\n";
        }

        // Simulate Student School ID check
        echo "\n=== STUDENT SCHOOL CHECK ===\n";
        $project_test = \App\Models\FinalProject::with('student.school')->find(1);
        if ($project_test) {
            echo "  Project ID: {$project_test->id} | Title: '{$project_test->title}'\n";
            echo "  Student ID: {$project_test->student_id} | Name: '{$project_test->student?->full_name}'\n";
            echo "  Student School ID: [{$project_test->student?->school_id}] (Type: " . gettype($project_test->student?->school_id) . ")\n";
            echo "  Student School Type: [{$project_test->student?->school?->type}]\n";
        } else {
            echo "  Project ID 1 not found.\n";
        }

        // Print generated HTML options
        echo "\n=== RENDERED OPTION TAGS (SAMPLE) ===\n";
        foreach ($controllerTeachers->take(10) as $ct) {
            echo "  <option value=\"{$ct->id}\" data-school=\"{$ct->school_id}\">{$ct->full_name} ({$ct->school?->type})</option>\n";
        }

        // Programmatic Request Simulation to proposals page
        echo "\n=== SIMULATING PROPOSALS PAGE GET REQUEST ===\n";
        $yulianusUser = \App\Models\User::where('name', 'like', '%Yulianus%Zega%')->first();
        if ($yulianusUser) {
            echo "Logging in as User ID: {$yulianusUser->id} | Name: {$yulianusUser->name}\n";
            \Illuminate\Support\Facades\Auth::login($yulianusUser);
            
            // Dispatch request and catch exception
            try {
                // Disable all middleware globally to bypass Auth, CORS, and CSRF
                app()->instance('middleware.disable', true);
                view()->share('errors', new \Illuminate\Support\ViewErrorBag());
                
                $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
                $request = \Illuminate\Http\Request::create('/admin/final-projects/proposals', 'GET');
                
                // We resolve the route and run it manually to catch the raw exception
                $route = \Route::getRoutes()->match($request);
                $request->setRouteResolver(fn() => $route);
                
                // Run the controller action directly or dispatch via router
                $response = \Route::dispatch($request);
                echo "Response HTTP Status: " . $response->getStatusCode() . "\n";
                $content = $response->getContent();
                
                // Extract the modal and script parts to print
                if (preg_match('/<div id="assign-modal".*?<\/div>\s*<\/div>\s*<\/div>/s', $content, $matches)) {
                    echo "\n--- MODAL HTML ---\n" . htmlspecialchars($matches[0]) . "\n";
                } else {
                    echo "\n--- COULD NOT FIND ASSIGN-MODAL IN HTML ---\n";
                }
                
                if (preg_match('/<script>.*?<\/script>/s', stristr($content, 'openAssignModal'), $matches)) {
                    echo "\n--- JAVASCRIPT BLOCK ---\n" . htmlspecialchars($matches[0]) . "\n";
                } else {
                    if (preg_match_all('/<script>.*?<\/script>/s', $content, $allScripts)) {
                        echo "\n--- LAST JAVASCRIPT BLOCK ---\n" . htmlspecialchars(end($allScripts[0])) . "\n";
                    } else {
                        echo "\n--- NO JAVASCRIPT BLOCKS FOUND ---\n";
                    }
                }
            } catch (\Throwable $th) {
                echo "Exception caught during simulation:\n";
                echo "  Message: " . $th->getMessage() . "\n";
                echo "  File: " . $th->getFile() . "\n";
                echo "  Line: " . $th->getLine() . "\n";
                echo "  Trace:\n" . substr($th->getTraceAsString(), 0, 1000) . "\n";
            }
        } else {
            echo "User Yulianus Zega not found to login.\n";
        }

        // Print Latest Laravel Logs
        echo "\n=== LATEST LARAVEL LOG LINES ===\n";
        $logFile = base_path('storage/logs/laravel.log');
        if (!file_exists($logFile)) {
            // Find the latest YYYY-MM-DD log
            $logFiles = glob(base_path('storage/logs/laravel-*.log'));
            if (!empty($logFiles)) {
                rsort($logFiles);
                $logFile = $logFiles[0];
            }
        }
        if (file_exists($logFile)) {
            echo "Reading log file: " . basename($logFile) . "\n";
            $lines = file($logFile);
            $lastLines = array_slice($lines, -50);
            foreach ($lastLines as $line) {
                echo htmlspecialchars($line);
            }
        } else {
            echo "No log file found in storage/logs/.\n";
        }
        
    } else {
        echo "<span class='err'>❌ Could not locate vendor/autoload.php or bootstrap/app.php.</span>\n";
    }
} catch (\Exception $e) {
    echo "<span class='err'>❌ Laravel Bootstrap Exception: " . $e->getMessage() . "</span>\n";
    echo $e->getTraceAsString() . "\n";
}
echo "</pre>";

echo "</body></html>";
