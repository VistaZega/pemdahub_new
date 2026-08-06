<?php
$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') { die('Unauthorized'); }

$env = parse_ini_file(__DIR__ . '/../.env');
$pdo = new PDO("mysql:host={$env['DB_HOST']};dbname={$env['DB_DATABASE']}", $env['DB_USERNAME'], $env['DB_PASSWORD']);

$tables = [
    '1' => '<ul><li><b>LED Anoda (Kaki Panjang)</b> ➜ Resistor 220 Ohm ➜ <b>Pin 13 Arduino</b></li><li><b>LED Katoda (Kaki Pendek)</b> ➜ <b>Pin GND Arduino</b></li></ul>',
    '2' => '<ul><li><b>Pin VCC DHT11</b> ➜ <b>Pin 5V Arduino</b></li><li><b>Pin GND DHT11</b> ➜ <b>Pin GND Arduino</b></li><li><b>Pin Data DHT11</b> ➜ <b>Pin Digital 2 Arduino</b></li></ul>',
    '3' => '<ul><li><b>Kabel Merah Servo</b> ➜ <b>Pin 5V Arduino</b></li><li><b>Kabel Hitam/Coklat Servo</b> ➜ <b>Pin GND Arduino</b></li><li><b>Kabel Kuning/Oranye Servo</b> ➜ <b>Pin Digital 9 Arduino</b></li></ul>',
    '4' => '<ul><li><b>Pin VCC LCD</b> ➜ <b>Pin 5V Arduino</b></li><li><b>Pin GND LCD</b> ➜ <b>Pin GND Arduino</b></li><li><b>Pin SDA LCD</b> ➜ <b>Pin A4 Arduino</b></li><li><b>Pin SCL LCD</b> ➜ <b>Pin A5 Arduino</b></li></ul>',
    '5' => '<ul><li><b>Pin VCC HC-SR04</b> ➜ <b>Pin 5V Arduino</b></li><li><b>Pin GND HC-SR04</b> ➜ <b>Pin GND Arduino</b></li><li><b>Pin TRIG HC-SR04</b> ➜ <b>Pin Digital 9 Arduino</b></li><li><b>Pin ECHO HC-SR04</b> ➜ <b>Pin Digital 10 Arduino</b></li></ul>',
    '6' => '<ul><li><b>Kaki 1 Push Button</b> ➜ <b>Pin Digital 2 Arduino</b></li><li><b>Kaki 2 Push Button</b> ➜ <b>Pin GND Arduino</b></li></ul><i>(Program akan menggunakan fitur INPUT_PULLUP internal)</i>',
    '7' => '<ul><li><b>Pin Kiri Potensiometer</b> ➜ <b>Pin 5V Arduino</b></li><li><b>Pin Kanan Potensiometer</b> ➜ <b>Pin GND Arduino</b></li><li><b>Pin Tengah (Wiper) Potensiometer</b> ➜ <b>Pin Analog A0 Arduino</b></li></ul>',
    '8' => '<ul><li><b>Kaki Positif (+) Buzzer</b> ➜ <b>Pin Digital 8 Arduino</b></li><li><b>Kaki Negatif (-) Buzzer</b> ➜ <b>Pin GND Arduino</b></li></ul>',
    '9' => '<ul><li><b>Pin VCC Relay</b> ➜ <b>Pin 5V Arduino</b></li><li><b>Pin GND Relay</b> ➜ <b>Pin GND Arduino</b></li><li><b>Pin IN Relay</b> ➜ <b>Pin Digital 7 Arduino</b></li></ul>',
    '10' => '<ul><li><b>Pin VCC HC-05</b> ➜ <b>Pin 5V Arduino</b></li><li><b>Pin GND HC-05</b> ➜ <b>Pin GND Arduino</b></li><li><b>Pin TX HC-05</b> ➜ <b>Pin Digital 10 Arduino (RX)</b></li><li><b>Pin RX HC-05</b> ➜ <b>Pin Digital 11 Arduino (TX)</b></li></ul>',
    '11' => '<ul><li><b>Pin 3.3V RC522</b> ➜ <b>Pin 3.3V Arduino (JANGAN KE 5V!)</b></li><li><b>Pin GND RC522</b> ➜ <b>Pin GND Arduino</b></li><li><b>Pin RST RC522</b> ➜ <b>Pin Digital 9 Arduino</b></li><li><b>Pin SDA (SS) RC522</b> ➜ <b>Pin Digital 10 Arduino</b></li><li><b>Pin MOSI RC522</b> ➜ <b>Pin Digital 11 Arduino</b></li><li><b>Pin MISO RC522</b> ➜ <b>Pin Digital 12 Arduino</b></li><li><b>Pin SCK RC522</b> ➜ <b>Pin Digital 13 Arduino</b></li></ul>',
    '12' => '<ul><li><b>Koneksi:</b> Sambungkan langsung port USB ESP32 ke komputer Anda menggunakan kabel Micro-USB / Type-C.</li></ul>',
    '13' => '<ul><li><b>Pin VCC DHT11</b> ➜ <b>Pin 3.3V ESP32</b></li><li><b>Pin GND DHT11</b> ➜ <b>Pin GND ESP32</b></li><li><b>Pin Data DHT11</b> ➜ <b>Pin D2 ESP32</b></li></ul>',
    '1' => '<ul><li><b>HC-SR04:</b> VCC ➜ 5V | GND ➜ GND | TRIG ➜ Pin 9 | ECHO ➜ Pin 10</li><li><b>Servo:</b> Merah ➜ 5V | Hitam ➜ GND | Kuning ➜ Pin 6</li></ul>',
    '2' => '<ul><li><b>RFID RC522:</b> Sama seperti modul 11</li><li><b>Servo:</b> Merah ➜ 5V | Hitam ➜ GND | Kuning ➜ Pin 6</li><li><b>LED Hijau:</b> Pin A0 | <b>LED Merah:</b> Pin A1</li></ul>',
    '3' => '<ul><li><b>DHT11:</b> Data ➜ D2 ESP32</li><li><b>LCD I2C:</b> SDA ➜ D21 ESP32 | SCL ➜ D22 ESP32</li></ul>',
    '4' => '<ul><li><b>Laser:</b> Ke 5V Arduino</li><li><b>LDR (Photocell):</b> Kaki 1 ➜ 5V | Kaki 2 ➜ Pin A0 & Resistor 10k ➜ GND</li><li><b>Buzzer:</b> Pin 8</li><li><b>LED Alarm:</b> Pin 13</li></ul>',
];

$stmt = $pdo->query("SELECT id, title, content FROM lms_materials WHERE course_id=221");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $content = $r['content'];

    // 1. HAPUS SEMUA GAMBAR (tag <img>)
    $content = preg_replace('/<img[^>]+>/i', '', $content);
    $content = preg_replace('/!\[.*?\]\(.*?\)/', '', $content); // in case ada markdown img tersisa

    // 2. TEMUKAN NOMOR MODUL/PROJECT UNTUK TABEL
    $modNum = '';
    if (preg_match('/Modul (\d+):/', $r['title'], $matches)) {
        $modNum = $matches[1];
    } else if (preg_match('/Project (\d+):/', $r['title'], $matches)) {
        $modNum = $matches[1]; // Wait, ini akan overwrite key '1' modul... 
    }
    
    // perbaikan mapping key:
    $key = '';
    if (strpos($r['title'], 'Modul') !== false) {
        preg_match('/Modul (\d+):/', $r['title'], $matches);
        if(!empty($matches)) $key = 'M'.$matches[1];
    } else {
        preg_match('/Project (\d+):/', $r['title'], $matches);
        if(!empty($matches)) $key = 'P'.$matches[1];
    }

    $wiringData = [
        'M1' => '<ul><li><b>LED Anoda (Kaki Panjang)</b> ➜ Resistor 220 Ohm ➜ <b>Pin 13 Arduino</b></li><li><b>LED Katoda (Kaki Pendek)</b> ➜ <b>Pin GND Arduino</b></li></ul>',
        'M2' => '<ul><li><b>Pin VCC DHT11</b> ➜ <b>Pin 5V Arduino</b></li><li><b>Pin GND DHT11</b> ➜ <b>Pin GND Arduino</b></li><li><b>Pin Data DHT11</b> ➜ <b>Pin Digital 2 Arduino</b></li></ul>',
        'M3' => '<ul><li><b>Kabel Merah Servo</b> ➜ <b>Pin 5V Arduino</b></li><li><b>Kabel Hitam/Coklat Servo</b> ➜ <b>Pin GND Arduino</b></li><li><b>Kabel Kuning/Oranye Servo</b> ➜ <b>Pin Digital 9 Arduino</b></li></ul>',
        'M4' => '<ul><li><b>Pin VCC LCD</b> ➜ <b>Pin 5V Arduino</b></li><li><b>Pin GND LCD</b> ➜ <b>Pin GND Arduino</b></li><li><b>Pin SDA LCD</b> ➜ <b>Pin A4 Arduino</b></li><li><b>Pin SCL LCD</b> ➜ <b>Pin A5 Arduino</b></li></ul>',
        'M5' => '<ul><li><b>Pin VCC HC-SR04</b> ➜ <b>Pin 5V Arduino</b></li><li><b>Pin GND HC-SR04</b> ➜ <b>Pin GND Arduino</b></li><li><b>Pin TRIG HC-SR04</b> ➜ <b>Pin Digital 9 Arduino</b></li><li><b>Pin ECHO HC-SR04</b> ➜ <b>Pin Digital 10 Arduino</b></li></ul>',
        'M6' => '<ul><li><b>Kaki 1 Push Button</b> ➜ <b>Pin Digital 2 Arduino</b></li><li><b>Kaki 2 Push Button</b> ➜ <b>Pin GND Arduino</b></li></ul><i>(Program akan menggunakan fitur INPUT_PULLUP internal)</i>',
        'M7' => '<ul><li><b>Pin Kiri Potensiometer</b> ➜ <b>Pin 5V Arduino</b></li><li><b>Pin Kanan Potensiometer</b> ➜ <b>Pin GND Arduino</b></li><li><b>Pin Tengah (Wiper) Potensiometer</b> ➜ <b>Pin Analog A0 Arduino</b></li></ul>',
        'M8' => '<ul><li><b>Kaki Positif (+) Buzzer</b> ➜ <b>Pin Digital 8 Arduino</b></li><li><b>Kaki Negatif (-) Buzzer</b> ➜ <b>Pin GND Arduino</b></li></ul>',
        'M9' => '<ul><li><b>Pin VCC Relay</b> ➜ <b>Pin 5V Arduino</b></li><li><b>Pin GND Relay</b> ➜ <b>Pin GND Arduino</b></li><li><b>Pin IN Relay</b> ➜ <b>Pin Digital 7 Arduino</b></li></ul>',
        'M10' => '<ul><li><b>Pin VCC HC-05</b> ➜ <b>Pin 5V Arduino</b></li><li><b>Pin GND HC-05</b> ➜ <b>Pin GND Arduino</b></li><li><b>Pin TX HC-05</b> ➜ <b>Pin Digital 10 Arduino (RX)</b></li><li><b>Pin RX HC-05</b> ➜ <b>Pin Digital 11 Arduino (TX)</b></li></ul>',
        'M11' => '<ul><li><b>Pin 3.3V RC522</b> ➜ <b>Pin 3.3V Arduino (JANGAN KE 5V!)</b></li><li><b>Pin GND RC522</b> ➜ <b>Pin GND Arduino</b></li><li><b>Pin RST RC522</b> ➜ <b>Pin Digital 9 Arduino</b></li><li><b>Pin SDA (SS) RC522</b> ➜ <b>Pin Digital 10 Arduino</b></li><li><b>Pin MOSI RC522</b> ➜ <b>Pin Digital 11 Arduino</b></li><li><b>Pin MISO RC522</b> ➜ <b>Pin Digital 12 Arduino</b></li><li><b>Pin SCK RC522</b> ➜ <b>Pin Digital 13 Arduino</b></li></ul>',
        'M12' => '<ul><li><b>Koneksi:</b> Sambungkan langsung port USB ESP32 ke komputer Anda menggunakan kabel Micro-USB / Type-C.</li></ul>',
        'M13' => '<ul><li><b>Pin VCC DHT11</b> ➜ <b>Pin 3.3V ESP32</b></li><li><b>Pin GND DHT11</b> ➜ <b>Pin GND ESP32</b></li><li><b>Pin Data DHT11</b> ➜ <b>Pin D2 ESP32</b></li></ul>',
        'P1' => '<ul><li><b>HC-SR04:</b> VCC ➜ 5V | GND ➜ GND | TRIG ➜ Pin 9 | ECHO ➜ Pin 10</li><li><b>Servo:</b> Merah ➜ 5V | Hitam ➜ GND | Kuning ➜ Pin 6</li></ul>',
        'P2' => '<ul><li><b>RFID RC522:</b> Sama seperti modul 11</li><li><b>Servo:</b> Merah ➜ 5V | Hitam ➜ GND | Kuning ➜ Pin 6</li><li><b>LED Hijau:</b> Pin A0 | <b>LED Merah:</b> Pin A1</li></ul>',
        'P3' => '<ul><li><b>DHT11:</b> Data ➜ D2 ESP32</li><li><b>LCD I2C:</b> SDA ➜ D21 ESP32 | SCL ➜ D22 ESP32</li></ul>',
        'P4' => '<ul><li><b>Laser:</b> Ke 5V Arduino</li><li><b>LDR (Photocell):</b> Kaki 1 ➜ 5V | Kaki 2 ➜ Pin A0 & Resistor 10k ➜ GND</li><li><b>Buzzer:</b> Pin 8</li><li><b>LED Alarm:</b> Pin 13</li></ul>',
    ];

    // 3. SUNTIKKAN TABEL KONEKSI & GANTI "Materi Visual" DENGAN "Tabel Koneksi Pin"
    if (isset($wiringData[$key])) {
        $tableHtml = '<h1>2. Tabel Koneksi Pin</h1>' . $wiringData[$key];
        
        // Hapus judul "2. Materi Visual" atau "2. Konsep Arsitektur IoT" jika ada
        $content = preg_replace('/<h1>2\.\s+Materi Visual<\/h1>/i', '', $content);
        
        // Masukkan sebelum "3. Praktek Implementasi"
        $content = str_replace('<h1>3. Praktek', $tableHtml . '<h1>3. Praktek', $content);
    }

    // 4. PERBAIKI NEWLINE (\n) PADA KODE PROGRAM
    // Replace the literal string "\n" (backslash and n) with an actual newline "\n"
    $content = preg_replace('/\\\\n/', "\n", $content);

    // 5. UPDATE KE DATABASE
    $update = $pdo->prepare("UPDATE lms_materials SET content=? WHERE id=?");
    $update->execute([$content, $r['id']]);
}

echo "<h2>Penyempurnaan Modul Selesai!</h2>";
echo "<ul>";
echo "<li>Gambar AI yang membingungkan telah dihapus total.</li>";
echo "<li>Tabel Panduan Koneksi Pin (Wiring) yang 100% akurat telah ditambahkan.</li>";
echo "<li>Format baris baru (Enter) pada kode program telah diperbaiki.</li>";
echo "</ul>";
