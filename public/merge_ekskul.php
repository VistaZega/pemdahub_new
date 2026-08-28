<?php
require __DIR__."/../vendor/autoload.php";
$app = require_once __DIR__."/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;
use App\Models\School;

echo "<pre>";
echo "=== Script Auto-Merge Ekskul (SMKS Pembda Nias) ===\n\n";

$smks = School::where("name", "like", "%SMKS Pembda Nias%")->first();
if (!$smks) {
    die("Error: SMKS Pembda Nias tidak ditemukan.\n");
}

// Get all ekskul for SMKS
$ekskuls = Extracurricular::where("school_id", $smks->id)->withCount("members")->get();

$keywords = [
    "Futsal",
    "Pramuka",
    "Paskibraka",
    "Paskas",
    "Voli",
    "Volleyball",
    "Basket",
    "Tari",
    "Pencak Silat",
    "Karate",
    "Rohis",
    "Rohkris",
    "Musik",
    "Osil"
];

$groups = [];

foreach ($ekskuls as $ek) {
    $matched = false;
    foreach ($keywords as $kw) {
        if (stripos($ek->name, $kw) !== false) {
            // Group by this keyword
            // Note: Paskibraka and Paskas can be grouped together
            $groupKey = ($kw == "Paskas") ? "Paskibraka" : $kw;
            $groupKey = ($kw == "Volleyball") ? "Voli" : $groupKey;
            
            $groups[$groupKey][] = $ek;
            $matched = true;
            break;
        }
    }
    if (!$matched) {
        $groups["Lainnya"][] = $ek;
    }
}

foreach ($groups as $key => $items) {
    if ($key == "Lainnya") continue;
    
    if (count($items) > 1) {
        echo "Ditemukan duplikasi untuk kategori: <b>$key</b>\n";
        
        // Sort by member count descending
        usort($items, function($a, $b) {
            return $b->members_count <=> $a->members_count;
        });
        
        $target = $items[0];
        echo "  -> Dipertahankan: {$target->name} ({$target->members_count} anggota)\n";
        
        for ($i = 1; $i < count($items); $i++) {
            $source = $items[$i];
            echo "  -> Akan digabung & dihapus: {$source->name} ({$source->members_count} anggota)\n";
            
            // Do the merge
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
                } else {
                    $member->delete();
                    $countDuplicate++;
                }
            }

            DB::table("extracurricular_activities")
                ->where("extracurricular_id", $source->id)
                ->update(["extracurricular_id" => $target->id]);

            $source->delete();
            echo "     * Selesai: $countMoved dipindah, $countDuplicate duplikat dihapus.\n";
        }
        echo "\n";
    }
}

echo "Pengecekan dan penggabungan otomatis selesai. Anda bisa menutup halaman ini.";
