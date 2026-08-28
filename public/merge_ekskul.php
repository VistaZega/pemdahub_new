<?php
require __DIR__."/../vendor/autoload.php";
$app = require_once __DIR__."/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;

echo "<pre>";
echo "=== Script Penggabungan Ekskul (Pramuka SMKS) ===\n";

// Mencari langsung dari nama Ekskul (Gugus Depan)
$source = Extracurricular::where("name", "like", "%Gugus Depan Gerakan Pramuka%")->where("name", "like", "%SMK Swasta Pembda Nias%")->first();
$target = Extracurricular::where("name", "like", "Gugus Depan SMK Swasta Pembda Nias%")->first();

if (!$source) {
    echo "Info: Ekskul sumber (3 Orang) tidak ditemukan. Mencari alternatif...\n";
    $source = Extracurricular::where("name", "like", "%Gerakan Pramuka%")->where("name", "like", "%SMK%")->first();
}

if (!$target) {
    echo "Info: Ekskul target (10 Orang) tidak ditemukan. Mencari alternatif...\n";
    $target = Extracurricular::where("name", "like", "%Gugus Depan%")->where("name", "like", "%SMK%")->where("id", "!=", $source?->id ?? 0)->first();
}

if (!$source) {
    die("Error: Ekskul sumber gagal ditemukan.\n");
}
if (!$target) {
    die("Error: Ekskul target gagal ditemukan.\n");
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

    if (!str_contains(strtolower($target->name), "pramuka")) {
        $target->name = "Gugus Depan Pramuka SMKS Pembda Nias";
        $target->save();
        echo "- Nama ekskul target diperbarui menjadi: {$target->name}\n";
    }

    echo "\nRingkasan:\n";
    echo "- $countMoved anggota berhasil dipindahkan.\n";
    echo "- $countDuplicate anggota dihapus karena duplikat.\n";
    echo "- Ekskul $deletedName telah dihapus dari sistem.\n";
}

echo "\nPenggabungan selesai. Anda bisa menutup halaman ini.";
