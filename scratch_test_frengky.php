<?php
if (!defined('LARAVEL_BOOTSTRAPPED')) {
    define('LARAVEL_BOOTSTRAPPED', true);
    require __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
}

if (!function_exists('calculateDistanceTest')) {
    function calculateDistanceTest($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * asin(sqrt($a));
        return $earthRadius * $c;
    }
}

$student = \App\Models\Student::with(['school', 'user'])->where('full_name', 'like', '%Frengky%')->first();
echo "Student: {$student->full_name} (ID: {$student->id}, School ID: {$student->school_id})\n";
echo "Primary School: " . ($student->school->name ?? 'None') . " (Lat: {$student->school->latitude}, Lng: {$student->school->longitude})\n\n";

$testLats = [
    'Kompleks Perguruan Pembda Real (Depan/Tengah Kampus)' => [1.281342, 97.625766],
    'Kompleks Perguruan Pembda Real (Ruang Kelas/Belakang)' => [1.281433, 97.626503],
    'Lokasi Lama Yang Salah (1.2825, 97.6190)' => [1.282500, 97.619000],
];

$maxRadiusMeters = (int) \App\Models\Setting::getValue('attendance_max_radius', 175);
echo "Setting attendance_max_radius: {$maxRadiusMeters} meters\n";
echo "Setting school_latitude: " . \App\Models\Setting::getValue('school_latitude', 1.28127778) . "\n";
echo "Setting school_longitude: " . \App\Models\Setting::getValue('school_longitude', 97.62566667) . "\n\n";

$targetLocations = [];
$primarySchool = $student->school;
if ($primarySchool && (float)$primarySchool->latitude != 0.0 && (float)$primarySchool->longitude != 0.0) {
    $targetLocations[] = [
        'name' => $primarySchool->name,
        'lat' => (float)$primarySchool->latitude,
        'lng' => (float)$primarySchool->longitude,
    ];
}

$allSchools = \App\Models\School::where('is_active', true)
    ->whereNotNull('latitude')
    ->whereNotNull('longitude')
    ->where('latitude', '!=', 0)
    ->where('longitude', '!=', 0)
    ->get();

foreach ($allSchools as $sch) {
    $targetLocations[] = [
        'name' => $sch->name,
        'lat' => (float)$sch->latitude,
        'lng' => (float)$sch->longitude,
    ];
}

$globalLat = (float) \App\Models\Setting::getValue('school_latitude', 1.28127778);
$globalLng = (float) \App\Models\Setting::getValue('school_longitude', 97.62566667);
$targetLocations[] = [
    'name' => 'Kampus Perguruan Pembda',
    'lat' => $globalLat,
    'lng' => $globalLng,
];

echo "All Target Locations checked by AttendanceController:\n";
foreach ($targetLocations as $idx => $loc) {
    echo "  [{$idx}] {$loc['name']} -> Lat: {$loc['lat']}, Lng: {$loc['lng']}\n";
}
echo "\n";

foreach ($testLats as $label => $coords) {
    $lat = $coords[0];
    $lng = $coords[1];
    $minDistance = null;
    $closestTarget = null;
    foreach ($targetLocations as $loc) {
        $dist = calculateDistanceTest($lat, $lng, $loc['lat'], $loc['lng']);
        if ($minDistance === null || $dist < $minDistance) {
            $minDistance = $dist;
            $closestTarget = $loc;
        }
    }
    $passed = $minDistance <= $maxRadiusMeters ? "ALLOWED (BERHASIL ABSEN)" : "REJECTED (DITOLAK)";
    echo "Test [{$label}] ({$lat}, {$lng}):\n";
    echo "  Closest target: {$closestTarget['name']} at " . round($minDistance) . " meters away -> Result: {$passed}\n\n";
}
