<?php
require __DIR__ . "/../vendor/autoload.php";
$app = require_once __DIR__ . "/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\Extracurricular;

if (!isset($_GET["secret"]) || $_GET["secret"] !== "pembda99") {
    die("Unauthorized");
}

$updates = [
    ["name" => "Futsal", "icon" => "fa-solid fa-futbol", "category" => "olahraga"],
    ["name" => "Marching Band", "icon" => "fa-solid fa-drum", "category" => "marching_band"],
    ["name" => "Tenis Meja", "icon" => "fa-solid fa-table-tennis-paddle-ball", "category" => "olahraga"],
    ["name" => "Olah Vocal", "icon" => "fa-solid fa-microphone-lines", "category" => "seni_budaya"],
    ["name" => "English Club", "icon" => "fa-solid fa-language", "category" => "sains_it"],
    ["name" => "Sains", "icon" => "fa-solid fa-flask", "category" => "sains_it"],
    ["name" => "Cerdas Cermat", "icon" => "fa-solid fa-brain", "category" => "sains_it"],
];

echo "<h3>Memperbarui Ikon Ekstrakurikuler</h3><ul>";

foreach ($updates as $update) {
    $ekskuls = Extracurricular::where("name", "LIKE", "%" . $update["name"] . "%")->get();
    foreach ($ekskuls as $ekskul) {
        $ekskul->display_icon = $update["icon"];
        $ekskul->category = $update["category"];
        $ekskul->save();
        echo "<li>Diperbarui: " . $ekskul->name . " -> Ikon: " . $update["icon"] . "</li>";
    }
}
echo "</ul><p>Selesai!</p>";
?>
