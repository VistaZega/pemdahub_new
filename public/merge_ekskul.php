<?php
require __DIR__."/../vendor/autoload.php";
$app = require_once __DIR__."/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;

echo "<pre>";
echo "=== Script Penggabungan Ekskul (Paskibraka SMAS Pembda 1) ===\n";

$smas = \App\Models\School::where("name", "like", "%SMAS Pembda 1%")->first();
if (!$smas) {
    die("Error: SMAS Pembda 1 tidak ditemukan.\n");
}

$sourceName = "Korps Paskibraka Satria";
$targetName = "Paskas";

$source = Extracurricular::where("school_id", $smas->id)->where("name", "like", "%{$sourceName}%")->first();
$target = Extracurricular::where("school_id", $smas->id)->where("name", "like", "%{$targetName}%")->first();

if (!$source) {
    echo "Info: Ekskul {$sourceName} tidak ditemukan (mungkin sudah dihapus/digabung).\n";
}
if (!$target) {
    $target = Extracurricular::where("name", "like", "%{$targetName}%")->first();
    if (!$target) {
        die("Error: Ekskul {$targetName} tidak ditemukan.\n");
    }
}

if ($source && $target) {
    echo "Memindahkan anggota dari '{$source->name}' ke '{$target->name}'...\n";

    $membersToMove = ExtracurricularMember::where("extracurricular_id", $source->id)->get();
    $countMoved = 0;
    $countDuplicate = 0;

    foreach ($membersToMove as $member) {
        $exists = ExtracurricularMember::where("extracurricular_id", $target->id)
                    ->where("student_id", $member->student_id)
                    ->exists();
                    
        if (!$exists) {
            $member->extracurricular_id = $target->id;
            $member->save();
            $countMoved++;
            echo "- Siswa ID {$member->student_id} dipindahkan.\n";
        } else {
            $member->delete();
            $countDuplicate++;
            echo "- Siswa ID {$member->student_id} sudah ada di {$target->name} (duplikat dihapus).\n";
        }
    }

    DB::table("extracurricular_activities")
        ->where("extracurricular_id", $source->id)
        ->update(["extracurricular_id" => $target->id]);

    $deletedName = $source->name;
    $source->delete();

    echo "\nRingkasan:\n";
    echo "- $countMoved anggota berhasil dipindahkan.\n";
    echo "- $countDuplicate anggota dihapus karena duplikat.\n";
    echo "- Ekskul $deletedName telah dihapus dari sistem.\n";
}

echo "\nPenggabungan selesai. Anda bisa menutup halaman ini.";
