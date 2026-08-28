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

$smks = \App\Models\School::where("name", "like", "%SMKS Pembda Nias%")->first();
if (!$smks) {
    die("Error: SMKS Pembda Nias tidak ditemukan.\n");
}

$sourceName = "Gugus Depan Gerakan Pramuka";
$targetName = "Gugus Depan SMK Swasta Pembda Nias";

// Find them by matching keywords
$source = Extracurricular::where("school_id", $smks->id)->where("name", "like", "%Gerakan Pramuka%")->first();
$target = Extracurricular::where("school_id", $smks->id)->where("name", "like", "Gugus Depan%")->where("id", "!=", $source?->id ?? 0)->orderBy("id")->first();

if (!$source) {
    echo "Info: Ekskul sumber (3 Orang) tidak ditemukan (mungkin sudah dihapus/digabung).\n";
}
if (!$target) {
    die("Error: Ekskul target (10 Orang) tidak ditemukan.\n");
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

    // Pastikan nama targetnya bagus, ubah jika perlu
    if (!str_contains($target->name, "Pramuka")) {
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
