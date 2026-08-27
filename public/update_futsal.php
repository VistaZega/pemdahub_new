<?php
@ini_set('display_errors', '1');
@error_reporting(E_ALL);

$secret = $_REQUEST['secret'] ?? 'pembda99';
if ($secret !== 'pembda99') die('Unauthorized');

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\School;

$school = School::where('name', 'LIKE', '%SMAS Pembda 1%')->first();
if (!$school) die('Sekolah tidak ditemukan');

$affected = DB::table('extracurriculars')
    ->where('name', 'LIKE', '%Futsal%')
    ->update([
        'school_id' => $school->id,
        'scope' => 'sekolah'
    ]);

if ($affected > 0) {
    echo "Berhasil memindahkan Ekskul Futsal menjadi milik {$school->name} (scope: sekolah).";
} else {
    echo "Ekskul Futsal tidak ditemukan atau sudah dipindahkan.";
}
