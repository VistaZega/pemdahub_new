<?php
require __DIR__ . "/../vendor/autoload.php";
$app = require_once __DIR__ . "/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$base64Image = "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAAAAAAAD/2wBDAAoHBwgHBgoICAgLCgoLDhgQDg0NDh0VFhEYIx8lJCIfIiEmKzcvJik0KSEiMEExNDk7Pj4+JS5ESUM8SDc9Pjv/2wBDAQoLCw4NDhwQEBw7KCIoOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozv/wAARCABQAFADASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAf/xAAaEAACAwEBAAAAAAAAAAAAAAAAAQIDBQQH/8QAFQEBAQAAAAAAAAAAAAAAAAAAAQT/xAAYEQEBAQEBAAAAAAAAAAAAAAAAAQIRE//aAAwDAQACEQMRAD8An4AAgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD/2Q==";

echo "Testing Base64 decoding...\n";
if (preg_match("/^data:image\/(\w+);base64,/", $base64Image, $type)) {
    echo "Regex matched. Type: " . $type[1] . "\n";
    $base64Image = substr($base64Image, strpos($base64Image, ",") + 1);
    $type = strtolower($type[1]);
    if (in_array($type, ["jpg", "jpeg", "png"])) {
        echo "Type allowed.\n";
        $decodedImage = base64_decode($base64Image);
        if ($decodedImage !== false) {
            echo "Decoded successfully. Length: " . strlen($decodedImage) . " bytes\n";
            $fileName = "photos/test_" . uniqid() . "." . $type;
            \Illuminate\Support\Facades\Storage::disk("public")->put($fileName, $decodedImage);
            echo "Saved to: " . $fileName . "\n";
            echo "Exists: " . (\Illuminate\Support\Facades\Storage::disk("public")->exists($fileName) ? "Yes" : "No") . "\n";
        } else {
            echo "Failed to decode base64.\n";
        }
    } else {
        echo "Type not allowed.\n";
    }
} else {
    echo "Regex failed.\n";
}

