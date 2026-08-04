<?php
/**
 * SCRIPT INJEKSI LANGSUNG - LMS Mikrokontroler
 * Menyuntikkan 17 modul langsung ke database production via PDO
 * Tanpa perlu Laravel, Artisan, atau framework apapun
 */

$secret = $_GET['secret'] ?? '';
if ($secret !== 'pembda99') { http_response_code(403); die('Unauthorized'); }

set_time_limit(120);
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre style='background:#0d1117;color:#c9d1d9;padding:20px;font-family:monospace;font-size:12px;line-height:1.6'>";
echo "=== INJEKSI MODUL LMS MIKROKONTROLER ===\n\n";

// 1. Temukan .env
$basePath = realpath(__DIR__ . '/../');
$envFile  = $basePath . '/.env';

if (!file_exists($envFile)) {
    die("ERROR: .env tidak ditemukan di {$basePath}\n");
}

// 2. Parse .env
$env = [];
foreach (file($envFile) as $line) {
    $line = trim($line);
    if (!$line || $line[0] === '#' || strpos($line, '=') === false) continue;
    [$k, $v] = explode('=', $line, 2);
    $env[trim($k)] = trim($v, '"\'');
}

$host = $env['DB_HOST']     ?? '127.0.0.1';
$port = $env['DB_PORT']     ?? '3306';
$db   = $env['DB_DATABASE'] ?? '';
$user = $env['DB_USERNAME'] ?? '';
$pass = $env['DB_PASSWORD'] ?? '';

echo "Database: {$db} @ {$host}:{$port}\n";

// 3. Koneksi PDO
try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Koneksi DB: OK\n\n";
} catch (Exception $e) {
    die("GAGAL koneksi DB: " . $e->getMessage() . "\n");
}

$courseId = 101;

// 4. Cek course ada
$stmt = $pdo->prepare("SELECT id, course_name FROM lms_courses WHERE id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$course) {
    echo "ERROR: Course ID {$courseId} tidak ada di tabel lms_courses!\n";
    $stmt2 = $pdo->query("SELECT id, course_name FROM lms_courses ORDER BY id LIMIT 20");
    echo "Daftar semua course:\n";
    while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) echo "  ID:{$r['id']} | {$r['course_name']}\n";
    die();
}
echo "Course ditemukan: [{$course['id']}] {$course['course_name']}\n\n";

// 5. Hapus data lama (hard delete jika ada deleted_at, kalau tidak ada pakai DELETE biasa)
echo "Membersihkan data lama...\n";
try {
    // Hapus quiz questions dulu
    $pdo->exec("DELETE lqq FROM lms_quiz_questions lqq 
                INNER JOIN lms_quizzes lq ON lqq.quiz_id = lq.id 
                WHERE lq.course_id = {$courseId}");
    $pdo->exec("DELETE FROM lms_quizzes WHERE course_id = {$courseId}");
    $pdo->exec("DELETE FROM lms_assignments WHERE course_id = {$courseId}");
    $pdo->exec("DELETE FROM lms_materials WHERE course_id = {$courseId}");
    $pdo->exec("DELETE FROM lms_modules WHERE course_id = {$courseId}");
    echo "Data lama berhasil dihapus.\n\n";
} catch (Exception $e) {
    echo "Warning saat hapus: " . $e->getMessage() . "\n\n";
}

// 6. Data 17 Modul (langsung embed di sini)
$now = date('Y-m-d H:i:s');
$deadline = date('Y-m-d H:i:s', strtotime('+7 days'));

$modules = [
    [
        'title' => 'Modul 1: Pengenalan Mikrokontroler & Dasar Output (LED)',
        'description' => 'Pelajari apa itu mikrokontroler, struktur dasar program Arduino, dan cara mengontrol output digital menggunakan LED.',
        'quiz_question' => 'Fungsi apa pada pemrograman Arduino yang digunakan untuk memberikan tegangan 5V pada sebuah pin output digital?',
        'quiz_options' => ['pinMode(pin, OUTPUT)', 'digitalWrite(pin, HIGH)', 'analogRead(pin)', 'digitalWrite(pin, LOW)'],
        'quiz_answer' => 'digitalWrite(pin, HIGH)',
        'content' => '<h1>1. Landasan Teori: Pengenalan Mikrokontroler</h1><p>Mikrokontroler adalah sebuah sistem komputer fungsional dalam sebuah chip. Di dalamnya terkandung sebuah inti prosesor, memori (sejumlah kecil RAM, memori program, atau keduanya), dan perlengkapan input output.</p><p><strong>Implementasi di Dunia Nyata:</strong></p><ul><li><strong>Sistem Smart Home:</strong> Menyalakan lampu otomatis saat malam hari.</li><li><strong>Otomotif:</strong> Sistem injeksi bahan bakar dan pengereman ABS.</li><li><strong>Medis:</strong> Alat pengukur detak jantung digital.</li></ul><p>Dalam modul ini, kita akan fokus menggunakan <strong>Arduino Uno</strong>.</p><h1>2. Materi Visual</h1><p><img src="/lms/arduino_led.jpg" alt="Ilustrasi Arduino & LED" style="max-width:100%;border-radius:8px;margin:16px 0"></p><h1>3. Praktek Implementasi</h1><h2>A. Algoritma</h2><ol><li>Inisialisasi pin 13 sebagai pin OUTPUT.</li><li>Nyalakan LED (HIGH / 5V).</li><li>Tunggu 1 detik.</li><li>Matikan LED (LOW / 0V).</li><li>Ulangi.</li></ol><h2>C. Kode Program</h2><pre><code>const int ledPin = 13;\nvoid setup() {\n  pinMode(ledPin, OUTPUT);\n}\nvoid loop() {\n  digitalWrite(ledPin, HIGH);\n  delay(1000);\n  digitalWrite(ledPin, LOW);\n  delay(1000);\n}</code></pre><h1>4. Kesimpulan</h1><ul><li>Mikrokontroler = "otak" yang dapat diprogram.</li><li>Struktur program Arduino: <code>setup()</code> dan <code>loop()</code>.</li><li>Kontrol LED menggunakan <code>pinMode()</code> dan <code>digitalWrite()</code>.</li><li>Resistor penting untuk mencegah LED terbakar.</li></ul>',
    ],
    [
        'title' => 'Modul 2: Membaca Input dari Sensor (Suhu & Kelembaban DHT11)',
        'description' => 'Pelajari cara membaca data lingkungan nyata menggunakan sensor digital DHT11 dan menampilkannya di Serial Monitor.',
        'quiz_question' => 'Fitur apa pada Arduino IDE yang digunakan untuk melihat nilai yang dikirimkan oleh mikrokontroler ke layar komputer?',
        'quiz_options' => ['Board Manager', 'Serial Monitor', 'Verify/Compile', 'Library Manager'],
        'quiz_answer' => 'Serial Monitor',
        'content' => '<h1>1. Landasan Teori: Sensor Lingkungan</h1><p>Sensor adalah perangkat yang mendeteksi perubahan fisik di lingkungan sekitarnya. <strong>Sensor DHT11</strong> dapat mengukur Suhu dan Kelembaban Udara sekaligus.</p><p><strong>Implementasi di Dunia Nyata:</strong> Inkubator penetas telur, Ruang server, Greenhouse pintar.</p><h1>2. Materi Visual</h1><p><img src="/lms/arduino_dht11.jpg" alt="Ilustrasi Arduino & DHT11" style="max-width:100%;border-radius:8px;margin:16px 0"></p><h1>3. Praktek Implementasi</h1><h2>C. Kode Program</h2><pre><code>#include "DHT.h"\n#define DHTPIN 2\n#define DHTTYPE DHT11\nDHT dht(DHTPIN, DHTTYPE);\nvoid setup() {\n  Serial.begin(9600);\n  dht.begin();\n}\nvoid loop() {\n  delay(2000);\n  float h = dht.readHumidity();\n  float t = dht.readTemperature();\n  if (isnan(h) || isnan(t)) {\n    Serial.println("Gagal membaca sensor!");\n    return;\n  }\n  Serial.print("Kelembaban: "); Serial.print(h); Serial.print(" %");\n  Serial.print(" Suhu: "); Serial.print(t); Serial.println(" C");\n}</code></pre><h1>4. Kesimpulan</h1><ul><li>Sensor digital mengirimkan data sudah dalam format digital.</li><li>Library seperti <code>DHT.h</code> mempermudah pembacaan sensor.</li><li>Serial Monitor digunakan untuk debugging dan monitoring nilai sensor.</li></ul>',
    ],
    [
        'title' => 'Modul 3: Mengontrol Aktuator Bergerak (Motor Servo)',
        'description' => 'Pahami konsep sinyal PWM untuk memutar lengan motor servo ke sudut tertentu secara presisi.',
        'quiz_question' => 'Pada Arduino Uno, pin manakah yang TIDAK BISA digunakan untuk mengontrol pergerakan Motor Servo (tidak memiliki fitur PWM)?',
        'quiz_options' => ['Pin 9', 'Pin 10', 'Pin 2', 'Pin 3'],
        'quiz_answer' => 'Pin 2',
        'content' => '<h1>1. Landasan Teori: Aktuator dan Sinyal PWM</h1><p><strong>Motor Servo</strong> berputar menuju posisi (sudut) tertentu (0-180 derajat) menggunakan sinyal <strong>PWM (Pulse Width Modulation)</strong>.</p><p><strong>Implementasi:</strong> Portal parkir, Robotika, Kamera CCTV pintar.</p><h1>2. Materi Visual</h1><p><img src="/lms/arduino_servo.jpg" alt="Ilustrasi Arduino & Motor Servo" style="max-width:100%;border-radius:8px;margin:16px 0"></p><h1>3. Praktek Implementasi</h1><pre><code>#include &lt;Servo.h&gt;\nServo myServo;\nint servoPin = 9;\nvoid setup() {\n  myServo.attach(servoPin);\n}\nvoid loop() {\n  myServo.write(0); delay(1000);\n  myServo.write(90); delay(1000);\n  myServo.write(180); delay(1000);\n}</code></pre><h1>4. Kesimpulan</h1><ul><li>Motor Servo untuk gerakan dengan sudut presisi 0-180 derajat.</li><li>Pin PWM di Arduino Uno ditandai simbol <code>~</code> (pin 3, 5, 6, 9, 10, 11).</li><li>Cukup panggil <code>myServo.write(sudut)</code> untuk memindahkan servo.</li></ul>',
    ],
    [
        'title' => 'Modul 4: Menampilkan Teks pada Layar (LCD 16x2 I2C)',
        'description' => 'Pelajari cara menampilkan informasi berupa teks ke layar LCD 16x2 menggunakan protokol I2C yang hemat kabel.',
        'quiz_question' => 'Pin manakah pada Arduino Uno yang digunakan sebagai jalur SDA dan SCL untuk komunikasi I2C dengan layar LCD?',
        'quiz_options' => ['Pin Digital 0 dan 1', 'Pin Digital 9 dan 10', 'Pin Analog 4 (A4) dan Analog 5 (A5)', 'Pin 5V dan GND'],
        'quiz_answer' => 'Pin Analog 4 (A4) dan Analog 5 (A5)',
        'content' => '<h1>1. Landasan Teori: LCD dan I2C</h1><p><strong>LCD 16x2</strong> mampu menampilkan 2 baris teks, 16 karakter per baris. Dengan modul <strong>I2C</strong>, kabel data berkurang dari 6 menjadi hanya 4 kabel.</p><h1>2. Materi Visual</h1><p><img src="/lms/arduino_lcd.jpg" alt="Ilustrasi Arduino & LCD I2C" style="max-width:100%;border-radius:8px;margin:16px 0"></p><h1>3. Praktek Implementasi</h1><pre><code>#include &lt;Wire.h&gt;\n#include &lt;LiquidCrystal_I2C.h&gt;\nLiquidCrystal_I2C lcd(0x27, 16, 2);\nvoid setup() {\n  lcd.begin();\n  lcd.backlight();\n  lcd.setCursor(0, 0);\n  lcd.print("Halo SMK!");\n  lcd.setCursor(0, 1);\n  lcd.print("TAV Pembda Nias");\n}\nvoid loop() {}</code></pre><h1>4. Kesimpulan</h1><ul><li>I2C menghemat pin Arduino dari 6-8 pin menjadi 2 pin komunikasi.</li><li>Arduino Uno: SDA = A4, SCL = A5.</li></ul>',
    ],
    [
        'title' => 'Modul 5: Mendeteksi Jarak (Sensor Ultrasonik HC-SR04)',
        'description' => 'Pelajari cara mengukur jarak suatu benda dari sensor menggunakan gelombang suara ultrasonik.',
        'quiz_question' => 'Mengapa pada perhitungan jarak menggunakan sensor ultrasonik, durasi pantulan gelombang harus dibagi 2?',
        'quiz_options' => ['Karena sensor hanya bekerja pada tegangan 5V.', 'Untuk mengurangi noise pada pembacaan sensor.', 'Karena waktu yang dihitung adalah waktu tempuh bolak-balik (dari sensor ke benda, dan kembali ke sensor).', 'Karena kecepatan gelombang suara selalu berubah-ubah di udara.'],
        'quiz_answer' => 'Karena waktu yang dihitung adalah waktu tempuh bolak-balik (dari sensor ke benda, dan kembali ke sensor).',
        'content' => '<h1>1. Landasan Teori: Sensor Ultrasonik</h1><p>Sensor <strong>HC-SR04</strong> memancarkan gelombang suara dan mengukur waktu pantulannya untuk menghitung jarak.</p><p><strong>Implementasi:</strong> Sensor parkir mobil, Robot penghindar halangan, Pengukur level air.</p><h1>2. Materi Visual</h1><p><img src="/lms/arduino_ultrasonic.jpg" alt="Ilustrasi HC-SR04" style="max-width:100%;border-radius:8px;margin:16px 0"></p><h1>3. Praktek Implementasi</h1><pre><code>const int trigPin = 9;\nconst int echoPin = 10;\nlong duration; int distance;\nvoid setup() {\n  pinMode(trigPin, OUTPUT);\n  pinMode(echoPin, INPUT);\n  Serial.begin(9600);\n}\nvoid loop() {\n  digitalWrite(trigPin, LOW); delayMicroseconds(2);\n  digitalWrite(trigPin, HIGH); delayMicroseconds(10);\n  digitalWrite(trigPin, LOW);\n  duration = pulseIn(echoPin, HIGH);\n  distance = duration * 0.034 / 2;\n  Serial.print("Jarak: "); Serial.print(distance); Serial.println(" cm");\n  delay(1000);\n}</code></pre><h1>4. Kesimpulan</h1><ul><li>Ultrasonik = Trigger (pemancar) + Echo (penerima).</li><li>Rumus: jarak = waktu × kecepatan suara ÷ 2.</li></ul>',
    ],
    [
        'title' => 'Modul 6: Input Digital (Push Button) & Debouncing',
        'description' => 'Pelajari cara membaca input dari tombol fisik dan mengatasi masalah pantulan sinyal (bouncing).',
        'quiz_question' => 'Apa fungsi utama dari teknik "Debouncing" pada pembacaan tombol Push Button?',
        'quiz_options' => ['Meningkatkan tegangan listrik dari tombol ke Arduino.', 'Mencegah mikrokontroler membaca satu kali tekanan tombol sebagai tekanan berulang kali akibat pantulan mekanis.', 'Mengurangi konsumsi daya pada tombol.', 'Mencegah terjadinya korsleting saat tombol ditekan.'],
        'quiz_answer' => 'Mencegah mikrokontroler membaca satu kali tekanan tombol sebagai tekanan berulang kali akibat pantulan mekanis.',
        'content' => '<h1>1. Landasan Teori: Input Digital & Bouncing</h1><p>Tombol mekanis mengalami <strong>bouncing</strong> — pantulan beberapa kali sebelum stabil. Solusinya adalah teknik <strong>Debouncing</strong> dengan memberikan jeda waktu.</p><h1>2. Materi Visual</h1><p><img src="/lms/arduino_button.jpg" alt="Push Button" style="max-width:100%;border-radius:8px;margin:16px 0"></p><h1>3. Praktek Implementasi</h1><pre><code>const int buttonPin = 2;\nconst int ledPin = 13;\nint ledState = HIGH, buttonState, lastButtonState = LOW;\nunsigned long lastDebounceTime = 0;\nunsigned long debounceDelay = 50;\nvoid setup() {\n  pinMode(buttonPin, INPUT);\n  pinMode(ledPin, OUTPUT);\n  digitalWrite(ledPin, ledState);\n}\nvoid loop() {\n  int reading = digitalRead(buttonPin);\n  if (reading != lastButtonState) lastDebounceTime = millis();\n  if ((millis() - lastDebounceTime) > debounceDelay) {\n    if (reading != buttonState) {\n      buttonState = reading;\n      if (buttonState == HIGH) ledState = !ledState;\n    }\n  }\n  digitalWrite(ledPin, ledState);\n  lastButtonState = reading;\n}</code></pre><h1>4. Kesimpulan</h1><ul><li>Pull-Down resistor menstabilkan pin saat tombol dilepas.</li><li>Debouncing: tunggu 50ms sebelum membaca ulang status tombol.</li></ul>',
    ],
    [
        'title' => 'Modul 7: Input Analog (Potensiometer) & Pemetaan Nilai',
        'description' => 'Pelajari cara membaca rentang nilai analog (0-1023) dan memetakannya menjadi rentang yang berbeda.',
        'quiz_question' => 'Berapakah rentang nilai mentah yang dihasilkan oleh fungsi analogRead() pada Arduino Uno?',
        'quiz_options' => ['0 hingga 100', '0 hingga 255', '0 hingga 1023', '-512 hingga 512'],
        'quiz_answer' => '0 hingga 1023',
        'content' => '<h1>1. Landasan Teori: Sinyal Analog dan ADC</h1><p>Fitur <strong>ADC</strong> pada Arduino mengubah tegangan 0-5V menjadi nilai digital <strong>0 hingga 1023</strong>. <strong>Potensiometer</strong> adalah resistor variabel untuk mensimulasikan sensor analog.</p><h1>2. Materi Visual</h1><p><img src="/lms/arduino_potentiometer.jpg" alt="Potensiometer" style="max-width:100%;border-radius:8px;margin:16px 0"></p><h1>3. Praktek Implementasi</h1><pre><code>const int analogPin = A0;\nvoid setup() { Serial.begin(9600); }\nvoid loop() {\n  int sensorValue = analogRead(analogPin);\n  int mappedValue = map(sensorValue, 0, 1023, 0, 180);\n  Serial.print("ADC: "); Serial.print(sensorValue);\n  Serial.print(" | Sudut: "); Serial.println(mappedValue);\n  delay(100);\n}</code></pre><h1>4. Kesimpulan</h1><ul><li>Pin A0-A5 untuk membaca tegangan analog.</li><li>ADC: 0-5V → 0-1023.</li><li>Fungsi <code>map()</code> untuk mengkonversi skala nilai.</li></ul>',
    ],
    [
        'title' => 'Modul 8: Menghasilkan Suara (Piezo Buzzer & Nada)',
        'description' => 'Pelajari cara menghasilkan suara dan membuat melodi sederhana menggunakan Piezo Buzzer.',
        'quiz_question' => 'Fungsi bawaan Arduino manakah yang dirancang khusus untuk menghasilkan gelombang suara dengan frekuensi tertentu pada Buzzer?',
        'quiz_options' => ['analogWrite()', 'digitalWrite()', 'tone()', 'pulseIn()'],
        'quiz_answer' => 'tone()',
        'content' => '<h1>1. Landasan Teori: Piezo Buzzer</h1><p><strong>Piezo Buzzer</strong> menggunakan material piezoelektrik yang bergetar saat diberi tegangan berfrekuensi tertentu. Fungsi <code>tone()</code> memudahkan pembuatan nada.</p><h1>2. Materi Visual</h1><p><img src="/lms/arduino_buzzer.jpg" alt="Arduino & Buzzer" style="max-width:100%;border-radius:8px;margin:16px 0"></p><h1>3. Praktek Implementasi</h1><pre><code>const int buzzerPin = 8;\nvoid setup() { pinMode(buzzerPin, OUTPUT); }\nvoid loop() {\n  tone(buzzerPin, 262); delay(500); // DO\n  tone(buzzerPin, 294); delay(500); // RE\n  tone(buzzerPin, 330); delay(500); // MI\n  noTone(buzzerPin); delay(2000);\n}</code></pre><h1>4. Kesimpulan</h1><ul><li>Suara = getaran frekuensi, diukur dalam Hz.</li><li><code>tone(pin, frekuensi)</code> untuk membunyikan nada.</li><li><code>noTone(pin)</code> untuk menghentikan suara.</li></ul>',
    ],
    [
        'title' => 'Modul 9: Mengendalikan Beban Besar (Relay Module)',
        'description' => 'Pelajari bagaimana Arduino 5V dapat dengan aman menyalakan alat listrik bertegangan tinggi seperti lampu 220V AC.',
        'quiz_question' => 'Komponen apa yang digunakan sebagai "jembatan" agar Arduino 5V dapat mengontrol perangkat listrik bertegangan 220V AC dengan aman?',
        'quiz_options' => ['Transistor', 'Kapasitor', 'Modul Relay', 'Dioda'],
        'quiz_answer' => 'Modul Relay',
        'content' => '<h1>1. Landasan Teori: Relay</h1><p><strong>Relay</strong> adalah saklar listrik yang digerakkan elektromagnet. Arus kecil dari Arduino (5V) mengaktifkan magnet, yang kemudian menutup saklar untuk arus besar (220V AC).</p><p><strong>Implementasi:</strong> Lampu rumah otomatis, Pompa air, Kipas angin IoT.</p><h1>2. Materi Visual</h1><p><img src="/lms/arduino_relay.jpg" alt="Arduino & Relay" style="max-width:100%;border-radius:8px;margin:16px 0"></p><h1>3. Praktek Implementasi</h1><pre><code>const int relayPin = 7;\nvoid setup() { pinMode(relayPin, OUTPUT); }\nvoid loop() {\n  digitalWrite(relayPin, LOW);  // Nyalakan beban (aktif LOW)\n  delay(5000);\n  digitalWrite(relayPin, HIGH); // Matikan beban\n  delay(5000);\n}</code></pre><h1>4. Kesimpulan</h1><ul><li>Relay = saklar elektromagnetik untuk isolasi tegangan tinggi.</li><li>Relay modul umumnya aktif LOW.</li><li>SELALU gunakan relay untuk alat 220V AC demi keselamatan.</li></ul>',
    ],
    [
        'title' => 'Modul 10: Komunikasi Nirkabel (Bluetooth HC-05)',
        'description' => 'Pelajari cara menghubungkan Arduino dengan smartphone via Bluetooth untuk kontrol nirkabel jarak dekat.',
        'quiz_question' => 'Pada komunikasi Serial antara Arduino dan modul Bluetooth HC-05, pin TX Arduino dihubungkan ke pin apa pada modul HC-05?',
        'quiz_options' => ['TX HC-05', 'RX HC-05', 'VCC HC-05', 'EN HC-05'],
        'quiz_answer' => 'RX HC-05',
        'content' => '<h1>1. Landasan Teori: Bluetooth HC-05</h1><p>Modul <strong>HC-05</strong> memungkinkan komunikasi Bluetooth antara Arduino dan smartphone. Data dikirim via protokol Serial (UART).</p><p><strong>Implementasi:</strong> Kontrol LED dari HP, Robot berbasis Bluetooth, Smart home sederhana.</p><h1>2. Materi Visual</h1><p><img src="/lms/arduino_bluetooth.jpg" alt="Arduino & Bluetooth HC-05" style="max-width:100%;border-radius:8px;margin:16px 0"></p><h1>3. Praktek Implementasi</h1><pre><code>#include &lt;SoftwareSerial.h&gt;\nSoftwareSerial BTSerial(10, 11); // RX, TX\nconst int ledPin = 13;\nvoid setup() {\n  Serial.begin(9600);\n  BTSerial.begin(9600);\n  pinMode(ledPin, OUTPUT);\n}\nvoid loop() {\n  if (BTSerial.available()) {\n    char c = BTSerial.read();\n    if (c == "1") digitalWrite(ledPin, HIGH);\n    if (c == "0") digitalWrite(ledPin, LOW);\n  }\n}</code></pre><h1>4. Kesimpulan</h1><ul><li>TX Arduino → RX HC-05 | RX Arduino → TX HC-05.</li><li>Kecepatan baud HC-05 default: 9600 bps.</li><li>Gunakan <code>SoftwareSerial</code> untuk port serial tambahan.</li></ul>',
    ],
    [
        'title' => 'Modul 11: Sistem Keamanan Kartu (Sensor RFID RC522)',
        'description' => 'Pelajari cara membaca kartu RFID/NFC untuk membuat sistem akses/absensi pintar.',
        'quiz_question' => 'Berapa frekuensi kerja modul RFID RC522 yang umum digunakan dalam sistem keamanan akses berbasis kartu?',
        'quiz_options' => ['433 MHz', '2.4 GHz', '13.56 MHz', '900 MHz'],
        'quiz_answer' => '13.56 MHz',
        'content' => '<h1>1. Landasan Teori: RFID</h1><p><strong>RFID (Radio Frequency Identification)</strong> membaca data dari kartu/tag yang mengandung chip. Modul <strong>RC522</strong> bekerja pada frekuensi 13.56 MHz.</p><p><strong>Implementasi:</strong> Sistem absensi, Kunci pintu elektronik, Kontrol akses laboratorium.</p><h1>2. Materi Visual</h1><p><img src="/lms/arduino_rfid.jpg" alt="Arduino & RFID RC522" style="max-width:100%;border-radius:8px;margin:16px 0"></p><h1>3. Praktek Implementasi</h1><pre><code>#include &lt;SPI.h&gt;\n#include &lt;MFRC522.h&gt;\n#define SS_PIN 10\n#define RST_PIN 9\nMFRC522 rfid(SS_PIN, RST_PIN);\nvoid setup() {\n  Serial.begin(9600);\n  SPI.begin();\n  rfid.PCD_Init();\n  Serial.println("Tempel kartu RFID...");\n}\nvoid loop() {\n  if (!rfid.PICC_IsNewCardPresent()) return;\n  if (!rfid.PICC_ReadCardSerial()) return;\n  Serial.print("UID: ");\n  for (byte i = 0; i &lt; rfid.uid.size; i++) {\n    Serial.print(rfid.uid.uidByte[i], HEX);\n    Serial.print(" ");\n  }\n  Serial.println();\n}</code></pre><h1>4. Kesimpulan</h1><ul><li>RFID menggunakan gelombang radio untuk identifikasi tanpa kontak fisik.</li><li>RC522 terhubung via SPI ke Arduino.</li><li>UID kartu bersifat unik — cocok untuk sistem autentikasi.</li></ul>',
    ],
    [
        'title' => 'Modul 12: Pengenalan ESP32 & Koneksi Jaringan Wi-Fi',
        'description' => 'Berkenalan dengan ESP32 yang lebih canggih dari Arduino dan pelajari cara menghubungkannya ke jaringan Wi-Fi.',
        'quiz_question' => 'Apa keunggulan utama ESP32 dibandingkan Arduino Uno dalam konteks pengembangan IoT?',
        'quiz_options' => ['Lebih murah harganya', 'Memiliki Wi-Fi dan Bluetooth terintegrasi', 'Menggunakan tegangan kerja yang lebih tinggi', 'Memiliki lebih banyak pin analog'],
        'quiz_answer' => 'Memiliki Wi-Fi dan Bluetooth terintegrasi',
        'content' => '<h1>1. Landasan Teori: ESP32</h1><p><strong>ESP32</strong> adalah mikrokontroler dengan Wi-Fi dan Bluetooth terintegrasi. Sangat cocok untuk proyek IoT yang membutuhkan koneksi internet.</p><h1>2. Materi Visual</h1><p><img src="/lms/esp32_wifi.jpg" alt="ESP32 WiFi" style="max-width:100%;border-radius:8px;margin:16px 0"></p><h1>3. Praktek Implementasi</h1><pre><code>#include &lt;WiFi.h&gt;\nconst char* ssid = "NAMA_WIFI_ANDA";\nconst char* password = "PASSWORD_WIFI";\nvoid setup() {\n  Serial.begin(115200);\n  WiFi.begin(ssid, password);\n  while (WiFi.status() != WL_CONNECTED) {\n    delay(500); Serial.print(".");\n  }\n  Serial.println("\\nTerhubung! IP: " + WiFi.localIP().toString());\n}\nvoid loop() {}</code></pre><h1>4. Kesimpulan</h1><ul><li>ESP32 = Arduino + Wi-Fi + Bluetooth dalam satu chip.</li><li>Pemrograman menggunakan Arduino IDE dengan board ESP32.</li><li>IP Address menunjukkan ESP32 sudah terhubung ke jaringan.</li></ul>',
    ],
    [
        'title' => 'Modul 13: Internet of Things (IoT) dengan Blynk / ThingSpeak',
        'description' => 'Wujudkan proyek IoT nyata: kirim data sensor ke cloud dan pantau dari mana saja menggunakan smartphone.',
        'quiz_question' => 'Apa yang dimaksud dengan IoT (Internet of Things)?',
        'quiz_options' => ['Sebuah merek router WiFi terkenal', 'Jaringan perangkat fisik yang terhubung ke internet dan dapat saling bertukar data', 'Bahasa pemrograman khusus untuk mikrokontroler', 'Protokol komunikasi antara Arduino dan sensor'],
        'quiz_answer' => 'Jaringan perangkat fisik yang terhubung ke internet dan dapat saling bertukar data',
        'content' => '<h1>1. Landasan Teori: IoT</h1><p><strong>IoT (Internet of Things)</strong> = jaringan perangkat fisik yang terhubung ke internet. Platform cloud seperti <strong>Blynk</strong> dan <strong>ThingSpeak</strong> memudahkan visualisasi data sensor secara realtime.</p><h1>2. Konsep Arsitektur IoT</h1><p><code>Sensor → ESP32 → Internet → Cloud → Dashboard HP</code></p><h1>3. Praktek: Kirim Suhu ke Blynk</h1><pre><code>#define BLYNK_TEMPLATE_ID "TMPL..."  \n#define BLYNK_AUTH_TOKEN "YourToken"\n#include &lt;BlynkSimpleEsp32.h&gt;\n#include "DHT.h"\nDHT dht(2, DHT11);\nvoid setup() {\n  Blynk.begin(BLYNK_AUTH_TOKEN, "WiFi_SSID", "WiFi_Pass");\n  dht.begin();\n}\nvoid loop() {\n  Blynk.run();\n  float t = dht.readTemperature();\n  Blynk.virtualWrite(V0, t);\n  delay(2000);\n}</code></pre><h1>4. Kesimpulan</h1><ul><li>IoT menghubungkan dunia fisik dengan dunia digital.</li><li>Platform Blynk: mudah digunakan dengan drag-and-drop widget.</li><li>Data dapat dipantau secara realtime dari mana saja.</li></ul>',
    ],
    [
        'title' => 'Project 1: Tempat Sampah Pintar (Smart Trash Bin)',
        'description' => 'Bangun proyek nyata: Tempat sampah yang membuka tutupnya otomatis saat tangan mendekat menggunakan sensor ultrasonik dan motor servo.',
        'quiz_question' => 'Pada proyek Smart Trash Bin, komponen mana yang berfungsi sebagai "otak" yang menentukan kapan tutup harus terbuka?',
        'quiz_options' => ['Motor Servo', 'Sensor Ultrasonik HC-SR04', 'Mikrokontroler Arduino', 'Baterai 9V'],
        'quiz_answer' => 'Mikrokontroler Arduino',
        'content' => '<h1>Deskripsi Proyek</h1><p>Tempat sampah yang tutupnya membuka otomatis ketika tangan atau sampah mendekat, kemudian menutup kembali setelah beberapa detik.</p><h1>Komponen yang Dibutuhkan</h1><ul><li>Arduino Uno (1 unit)</li><li>Sensor Ultrasonik HC-SR04 (1 unit)</li><li>Motor Servo SG90 (1 unit)</li><li>Kabel Jumper secukupnya</li></ul><h1>Cara Kerja</h1><ol><li>Sensor ultrasonik terus-menerus mengukur jarak di depannya.</li><li>Jika jarak kurang dari 15 cm → kirim sinyal ke Arduino.</li><li>Arduino memerintahkan servo membuka tutup (90 derajat).</li><li>Setelah 3 detik, servo menutup kembali (0 derajat).</li></ol><h1>Kode Program</h1><pre><code>#include &lt;Servo.h&gt;\nServo myServo;\nconst int trigPin = 9, echoPin = 10;\nvoid setup() {\n  myServo.attach(6);\n  myServo.write(0);\n  pinMode(trigPin, OUTPUT);\n  pinMode(echoPin, INPUT);\n}\nvoid loop() {\n  digitalWrite(trigPin, LOW); delayMicroseconds(2);\n  digitalWrite(trigPin, HIGH); delayMicroseconds(10);\n  digitalWrite(trigPin, LOW);\n  long d = pulseIn(echoPin, HIGH) * 0.034 / 2;\n  if (d < 15) {\n    myServo.write(90); delay(3000); myServo.write(0);\n  }\n  delay(200);\n}</code></pre>',
    ],
    [
        'title' => 'Project 2: Kunci Pintu RFID Pintar (Smart Door Lock)',
        'description' => 'Bangun sistem kunci pintu yang hanya bisa dibuka dengan kartu RFID yang terdaftar, dilengkapi indikator LED dan buzzer.',
        'quiz_question' => 'Pada proyek Smart Door Lock, apa yang terjadi jika kartu RFID yang ditempelkan TIDAK terdaftar di sistem?',
        'quiz_options' => ['Pintu tetap terbuka', 'LED Hijau menyala', 'LED Merah menyala dan buzzer berbunyi tanda akses ditolak', 'Sistem reset otomatis'],
        'quiz_answer' => 'LED Merah menyala dan buzzer berbunyi tanda akses ditolak',
        'content' => '<h1>Deskripsi Proyek</h1><p>Sistem keamanan pintu berbasis RFID. Hanya kartu dengan UID yang terdaftar yang dapat membuka kunci (mengaktifkan servo/relay).</p><h1>Komponen</h1><ul><li>Arduino Uno</li><li>Modul RFID RC522 + Kartu/Tag RFID</li><li>Motor Servo (simulasi kunci) atau Relay + Solenoid</li><li>LED Merah dan Hijau</li><li>Piezo Buzzer</li></ul><h1>Kode Program</h1><pre><code>#include &lt;SPI.h&gt;\n#include &lt;MFRC522.h&gt;\n#include &lt;Servo.h&gt;\nMFRC522 rfid(10, 9);\nServo servo;\nconst String uidTerdaftar = "AB CD EF 12";\nvoid setup() {\n  SPI.begin(); rfid.PCD_Init();\n  servo.attach(6); servo.write(0);\n  pinMode(A0, OUTPUT); // LED Hijau\n  pinMode(A1, OUTPUT); // LED Merah\n}\nvoid loop() {\n  if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) return;\n  String uid = "";\n  for (byte i=0; i<rfid.uid.size; i++) {\n    uid += String(rfid.uid.uidByte[i], HEX) + " ";\n  }\n  uid.trim();\n  if (uid == uidTerdaftar) {\n    digitalWrite(A0, HIGH); servo.write(90);\n    delay(3000); servo.write(0); digitalWrite(A0, LOW);\n  } else {\n    digitalWrite(A1, HIGH); delay(1000); digitalWrite(A1, LOW);\n  }\n}</code></pre>',
    ],
    [
        'title' => 'Project 3: Stasiun Cuaca IoT (Weather Station Blynk)',
        'description' => 'Bangun stasiun cuaca personal yang mengirim data suhu & kelembaban ke cloud Blynk dan bisa dipantau dari HP.',
        'quiz_question' => 'Pada proyek Weather Station IoT, perangkat apa yang digunakan sebagai penghubung antara sensor fisik dengan internet/cloud?',
        'quiz_options' => ['Arduino Uno', 'Sensor DHT11', 'ESP32', 'LCD I2C'],
        'quiz_answer' => 'ESP32',
        'content' => '<h1>Deskripsi Proyek</h1><p>Stasiun cuaca mini yang membaca suhu dan kelembaban dari sensor DHT11, menampilkannya di LCD, dan secara bersamaan mengirimkan data ke platform Blynk untuk dipantau dari smartphone.</p><h1>Komponen</h1><ul><li>ESP32</li><li>Sensor DHT11</li><li>LCD 16x2 I2C</li><li>Akun Blynk (gratis)</li></ul><h1>Kode Program (Utama)</h1><pre><code>#define BLYNK_AUTH_TOKEN "Token_Anda"\n#include &lt;BlynkSimpleEsp32.h&gt;\n#include &lt;Wire.h&gt;\n#include &lt;LiquidCrystal_I2C.h&gt;\n#include "DHT.h"\nLiquidCrystal_I2C lcd(0x27, 16, 2);\nDHT dht(2, DHT11);\nvoid setup() {\n  Blynk.begin(BLYNK_AUTH_TOKEN, "WiFi", "Pass");\n  dht.begin(); lcd.begin(); lcd.backlight();\n}\nvoid loop() {\n  Blynk.run();\n  float t = dht.readTemperature();\n  float h = dht.readHumidity();\n  lcd.clear();\n  lcd.setCursor(0,0); lcd.print("Suhu: " + String(t) + " C");\n  lcd.setCursor(0,1); lcd.print("Lembab: " + String(h) + "%");\n  Blynk.virtualWrite(V0, t);\n  Blynk.virtualWrite(V1, h);\n  delay(2000);\n}</code></pre>',
    ],
    [
        'title' => 'Project 4: Sistem Alarm Keamanan Laser (Photocell & Buzzer)',
        'description' => 'Bangun sistem alarm keamanan sederhana yang berbunyi ketika sinar laser terputus oleh orang yang lewat.',
        'quiz_question' => 'Pada sistem alarm laser, komponen apa yang berfungsi sebagai "penerima" sinar laser?',
        'quiz_options' => ['LED Infrared', 'Laser Pointer', 'LDR (Light Dependent Resistor) / Photocell', 'Piezo Buzzer'],
        'quiz_answer' => 'LDR (Light Dependent Resistor) / Photocell',
        'content' => '<h1>Deskripsi Proyek</h1><p>Sistem alarm yang menggunakan sinar laser dan LDR (Light Dependent Resistor). Ketika seseorang memotong sinar laser, alarm buzzer berbunyi secara otomatis.</p><h1>Komponen</h1><ul><li>Arduino Uno</li><li>Laser Pointer (Modul) - pemancar</li><li>LDR (Photocell) - penerima</li><li>Resistor 10kΩ</li><li>Piezo Buzzer</li><li>LED Merah (indikator alarm)</li></ul><h1>Cara Kerja</h1><ol><li>Laser diarahkan ke LDR — nilai analog LDR tinggi (terang).</li><li>Ketika laser terputus → nilai LDR turun drastis.</li><li>Arduino mendeteksi perubahan → aktifkan buzzer + LED.</li></ol><h1>Kode Program</h1><pre><code>const int ldrPin = A0;\nconst int buzzerPin = 8;\nconst int ledPin = 13;\nint threshold = 500;\nvoid setup() {\n  pinMode(buzzerPin, OUTPUT);\n  pinMode(ledPin, OUTPUT);\n  Serial.begin(9600);\n}\nvoid loop() {\n  int ldrValue = analogRead(ldrPin);\n  Serial.println(ldrValue);\n  if (ldrValue < threshold) {\n    // Laser terputus - ALARM!\n    digitalWrite(ledPin, HIGH);\n    tone(buzzerPin, 1000);\n  } else {\n    digitalWrite(ledPin, LOW);\n    noTone(buzzerPin);\n  }\n  delay(100);\n}</code></pre>',
    ],
];

// 7. Insert data
echo "Memulai penyuntikan 17 modul...\n\n";
$inserted = 0;

foreach ($modules as $seq => $mod) {
    $sequence = $seq + 1;
    
    // Insert module
    $stmt = $pdo->prepare("INSERT INTO lms_modules (course_id, title, description, sequence, is_active, is_sequential, created_at, updated_at) VALUES (?, ?, ?, ?, 1, 1, ?, ?)");
    $stmt->execute([$courseId, $mod['title'], $mod['description'], $sequence, $now, $now]);
    $moduleId = $pdo->lastInsertId();
    
    // Insert material
    $stmt = $pdo->prepare("INSERT INTO lms_materials (course_id, module_id, title, content, material_type, order_number, is_published, created_at, updated_at) VALUES (?, ?, ?, ?, 'text', 1, 1, ?, ?)");
    $stmt->execute([$courseId, $moduleId, 'Materi: ' . $mod['title'], $mod['content'], $now, $now]);
    
    // Insert assignment
    $stmt = $pdo->prepare("INSERT INTO lms_assignments (course_id, module_id, title, description, assignment_type, deadline, max_score, is_published, allow_resubmit, max_resubmissions, created_at, updated_at) VALUES (?, ?, ?, ?, 'file', ?, 100, 1, 1, 3, ?, ?)");
    $stmt->execute([$courseId, $moduleId, 'Tugas: ' . $mod['title'], '<p>Praktikkan materi dan upload file <b>.ino</b> atau foto/video bukti rangkaian Anda berhasil.</p>', $deadline, $now, $now]);
    
    // Insert quiz
    $stmt = $pdo->prepare("INSERT INTO lms_quizzes (course_id, module_id, title, description, time_limit, total_score, passing_score, max_attempts, shuffle_questions, show_result, is_published, created_at, updated_at) VALUES (?, ?, ?, ?, 15, 100, 70, 3, 1, 1, 1, ?, ?)");
    $stmt->execute([$courseId, $moduleId, 'Kuis: ' . $mod['title'], 'Evaluasi pemahaman Anda terhadap materi modul ini.', $now, $now]);
    $quizId = $pdo->lastInsertId();
    
    // Insert quiz question
    $optionsJson = json_encode($mod['quiz_options']);
    $stmt = $pdo->prepare("INSERT INTO lms_quiz_questions (quiz_id, question, question_type, options, correct_answer, order_number, score, created_at, updated_at) VALUES (?, ?, 'multiple_choice', ?, ?, 1, 100, ?, ?)");
    $stmt->execute([$quizId, $mod['quiz_question'], $optionsJson, $mod['quiz_answer'], $now, $now]);
    
    $inserted++;
    echo "  [{$inserted}/17] ✓ {$mod['title']}\n";
}

echo "\n=== SELESAI! {$inserted} modul berhasil disuntikkan ke database! ===\n";
echo "Silakan buka LMS Anda dan refresh halaman kursus.\n";
echo "</pre>";
