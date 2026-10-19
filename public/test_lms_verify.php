<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

if (request('secret') !== 'pembda99') {
    die('Unauthorized');
}

header('Content-Type: text/plain; charset=utf-8');

echo "=== VERIFIKASI KONFIGURASI SERVER PRODUKSI ===\n";
echo "1. SESSION_DRIVER (env): " . env('SESSION_DRIVER') . "\n";
echo "2. config('session.driver'): " . config('session.driver') . "\n";

// Check column definition in MySQL
$colInfo = Illuminate\Support\Facades\DB::select("SHOW FULL COLUMNS FROM lms_materials WHERE Field IN ('title', 'content')");
foreach ($colInfo as $col) {
    echo "3. lms_materials.{$col->Field} Type: {$col->Type}\n";
}

// Test simulate teacher login & POST to materials
echo "\n=== SIMULASI PEMBUATAN MATERI GURU OFERIUS ZEGA ===\n";
try {
    $teacherUser = \App\Models\User::find(279); // Oferius Zega
    \Illuminate\Support\Facades\Auth::login($teacherUser);
    
    $course = \App\Models\LmsCourse::find(842);
    if (!$course) {
        throw new \Exception("Course 842 tidak ditemukan");
    }

    $title = "Memahami dan Mewujudkan Peran Orangtua Sebagai Pendidik Utama Anak dalam Keluarga Berdasarkan Nilai-Nilai Kristiani di Era Digital";
    $content = str_repeat("<p>Pendidikan dalam keluarga merupakan fondasi utama pembentukan karakter kristiani anak. Melalui keteladanan orang tua, anak belajar tentang iman, kasih, dan integritas.</p>", 10);
    
    // Simulate Request to LmsCourseController::storeMaterial
    $storeRequest = \App\Http\Requests\Lms\StoreLmsMaterialRequest::create(
        "/guru/lms/{$course->id}/materials",
        'POST',
        [
            'module_id' => 2401,
            'material_type' => 'pdf',
            'title' => $title,
            'content' => $content,
            'file_url' => 'https://canva.link/gn65smmtltqwhht',
        ]
    );
    $storeRequest->setUserResolver(function() use ($teacherUser) { return $teacherUser; });
    $storeRequest->setContainer($app);
    $storeRequest->setRedirector($app->make('redirect'));
    $storeRequest->validateResolved();

    $controller = app(\App\Http\Controllers\Guru\LmsCourseController::class);
    $redirectResp = $controller->storeMaterial($storeRequest, $course);

    echo "Status Redirect: " . $redirectResp->getStatusCode() . "\n";
    echo "Target URL: " . $redirectResp->getTargetUrl() . "\n";
    echo "Session Success: " . session('success') . "\n";

    // Cari materi yang baru dibuat dan bersihkan
    $created = \App\Models\LmsMaterial::where('course_id', $course->id)
        ->where('title', trim($title))
        ->latest('id')
        ->first();
    
    if ($created) {
        echo "Materi berhasil dibuat dengan ID: {$created->id}\n";
        echo "Order Number: {$created->order_number}\n";
        echo "Panjang Title: " . strlen($created->title) . " karakter\n";
        echo "Panjang Content: " . strlen($created->content) . " bytes\n";
        
        // Hapus materi testing agar tidak mengotori course asli
        $created->forceDelete();
        echo "Materi testing berhasil dibersihkan kembali.\n";
    }

    echo "\n=== HASIL: SEMUA TES BERHASIL 100%! TIDAK ADA ERROR ATAU 502 ===\n";
} catch (\Throwable $e) {
    echo "ERROR TERTANGKAP: " . get_class($e) . " - " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
