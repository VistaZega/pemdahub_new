<?php
require __DIR__."/../vendor/autoload.php";
$app = require_once __DIR__."/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;

echo "<pre>";
echo "=== Script Penggabungan Ekskul (SMAS Pembda 1) ===\n";

$smas = \App\Models\School::where("name", "like", "%SMAS Pembda 1%")->first();
if (!$smas) {
    die("Error: SMAS Pembda 1 tidak ditemukan.\n");
}

$onoNiha = Extracurricular::where("school_id", $smas->id)->where("name", "like", "%Ono Niha%")->first();
$hulayo = Extracurricular::where("school_id", $smas->id)->where("name", "like", "%Hulayo%")->first();

if (!$onoNiha) {
    echo "Info: Ekskul Ono Niha tidak ditemukan (mungkin sudah dihapus/digabung).\n";
}
if (!$hulayo) {
    die("Error: Ekskul Hulayo tidak ditemukan.\n");
}

if ($onoNiha && $hulayo) {
    echo "Memindahkan anggota dari '{$onoNiha->name}' ke '{$hulayo->name}'...\n";

    $membersToMove = ExtracurricularMember::where("extracurricular_id", $onoNiha->id)->get();
    $countMoved = 0;
    $countDuplicate = 0;

    foreach ($membersToMove as $member) {
        $exists = ExtracurricularMember::where("extracurricular_id", $hulayo->id)
                    ->where("student_id", $member->student_id)
                    ->exists();
                    
        if (!$exists) {
            $member->extracurricular_id = $hulayo->id;
            $member->save();
            $countMoved++;
            echo "- Siswa ID {$member->student_id} dipindahkan.\n";
        } else {
            $member->delete();
            $countDuplicate++;
            echo "- Siswa ID {$member->student_id} sudah ada di Hulayo (duplikat dihapus).\n";
        }
    }

    DB::table("extracurricular_activities")
        ->where("extracurricular_id", $onoNiha->id)
        ->update(["extracurricular_id" => $hulayo->id]);

    $onoNihaName = $onoNiha->name;
    $onoNiha->delete();

    echo "\nRingkasan:\n";
    echo "- $countMoved anggota berhasil dipindahkan.\n";
    echo "- $countDuplicate anggota dihapus karena sudah ada di {$hulayo->name}.\n";
    echo "- Ekskul $onoNihaName telah dihapus dari sistem.\n";
}

echo "\nPenggabungan selesai. Anda bisa menutup halaman ini.";
