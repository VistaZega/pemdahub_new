// ============================================================
//  FIRMWARE ESP32 FULL - PEMBDAHUB ATTENDANCE STATION
//  ESP32 + RC522 (RFID) + QR Scanner + LCD 20x4 + MP3 + Buzzer + LED
//
//  Port dari firmware NodeMCU V3 ke ESP32.
//  Server endpoint, format JSON, dan logika bisnis 100% identik.
//
//  KEUNGGULAN ESP32 DIBANDING NODEMCU V3:
//  1. HardwareSerial   : QR Scanner pakai Serial1 (lebih stabil)
//  2. HardwareSerial   : MP3 Player pakai Serial2 (lebih stabil)
//  3. Tidak perlu SoftwareSerial (rawan noise/timing issue)
//  4. RAM 520KB vs 80KB : Lebih leluasa untuk HTTPS + JSON
//  5. Dual-core 240MHz : WiFi di core 0, loop di core 1
//  6. LED Hijau + Merah: Pin GPIO cukup banyak
//
//  WIRING DIAGRAM ESP32 DevKit V1:
//  ┌─────────────┬──────────┬───────────────────────────────┐
//  │ Komponen    │ GPIO     │ Catatan                       │
//  ├─────────────┼──────────┼───────────────────────────────┤
//  │ RFID SDA/SS │ GPIO 5   │ SPI CS (VSPI)                 │
//  │ RFID SCK    │ GPIO 18  │ SPI CLK (VSPI default)        │
//  │ RFID MOSI   │ GPIO 23  │ SPI MOSI (VSPI default)       │
//  │ RFID MISO   │ GPIO 19  │ SPI MISO (VSPI default)       │
//  │ RFID RST    │ GPIO 4   │ RFID Reset                    │
//  │ LCD SDA     │ GPIO 21  │ I2C SDA default ESP32         │
//  │ LCD SCL     │ GPIO 22  │ I2C SCL default ESP32         │
//  │ QR RX       │ GPIO 25  │ Serial1 RX ← TX QR Scanner   │
//  │ MP3 TX      │ GPIO 17  │ Serial2 TX → RX DFPlayer      │
//  │ MP3 RX      │ GPIO 16  │ Serial2 RX ← TX DFPlayer (opt)│
//  │ Buzzer (+)  │ GPIO 2   │ Buzzer aktif HIGH             │
//  │ LED Green   │ GPIO 15  │ LED Hijau (berhasil)          │
//  │ LED Red     │ GPIO 13  │ LED Merah (gagal)             │
//  │ RFID 3.3V   │ 3V3      │ JANGAN pakai 5V untuk RC522! │
//  │ LCD VCC     │ 5V       │ LCD 20x4 butuh 5V!            │
//  │ MP3 VCC     │ 5V       │ DFPlayer Mini butuh 5V!       │
//  │ GND         │ GND      │ Common ground semua komponen  │
//  └─────────────┴──────────┴───────────────────────────────┘
//
//  WIRING QR SCANNER (GM65 / GM50):
//  ┌──────────────────────────────────────────────────────┐
//  │  QR Scanner TX ───────────────→ ESP32 GPIO25 (RX1)  │
//  │  QR Scanner VCC ──────────────→ 5V / 3.3V           │
//  │  QR Scanner GND ──────────────→ GND                  │
//  └──────────────────────────────────────────────────────┘
//  CATATAN: Hanya butuh 1 kabel data (TX scanner → RX ESP32).
//           Kita hanya MENERIMA data dari scanner, tidak mengirim.
//
//  WIRING MP3 PLAYER (DFPlayer Mini):
//  ┌──────────────────────────────────────────────────────┐
//  │  ESP32 GPIO17 (TX2) ──[1KΩ]──→ DFPlayer RX          │
//  │  ESP32 GPIO16 (RX2) ←──────── DFPlayer TX (opsional)│
//  │  5V Eksternal ────────────────→ DFPlayer VCC         │
//  │  GND ─────────────────────────→ DFPlayer GND         │
//  │  DFPlayer SPK_1 ──────────────→ Speaker (+)          │
//  │  DFPlayer SPK_2 ──────────────→ Speaker (-)          │
//  └──────────────────────────────────────────────────────┘
//  CATATAN: Resistor 1KΩ antara TX2 dan DFPlayer RX
//           untuk proteksi level sinyal.
//
//  FILE MP3 DI SD CARD:
//  SD Card/
//  └── 01/              ← Folder 01
//      ├── 001.mp3      → "Selamat Pagi Silahkan Absen"      (Boot)
//      ├── 002.mp3      → "Akses Diterima Selamat Belajar"   (CHECK_IN Siswa)
//      ├── 003.mp3      → "Absen Pulang Sampai Jumpa"        (CHECK_OUT)
//      ├── 004.mp3      → "Absen Sudah Tercatat Terima Kasih"(COOLDOWN)
//      ├── 005.mp3      → "Selamat Pagi Selamat Bekerja"     (CHECK_IN Guru)
//      ├── 006.mp3      → "Kartu Tidak Dikenali Hubungi Admin"(NEW_CARD)
//      └── 007.mp3      → "Sistem Ada Gangguan Hubungi Admin" (ERROR)
//
//  BOARD SETTING DI ARDUINO IDE:
//  - Board         : "ESP32 Dev Module"
//  - Flash Size    : "4MB (Sketch 1.2MB / OTA ~1.5MB)"
//  - CPU Frequency : "240MHz"
//  - Upload Speed  : 115200
//  - Board Manager URL:
//    https://raw.githubusercontent.com/espressif/arduino-esp32/
//    gh-pages/package_esp32_index.json
//
//  LIBRARY YANG DIBUTUHKAN (Install via Library Manager):
//  - MFRC522 by GithubCommunity
//  - LiquidCrystal I2C by Frank de Brabander
//  - ArduinoJson by Benoit Blanchon (v6.x)
// ============================================================

#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <WiFiClientSecure.h>
#include <HardwareSerial.h>

// ============================================================
//  KONFIGURASI - Sesuaikan untuk setiap station!
// ============================================================

// WiFi Utama
const char* WIFI_SSID          = "PembdaLINK";
const char* WIFI_PASSWORD      = "PEMBDA2026";

// WiFi Alternatif 1 (otomatis fallback jika utama gagal)
const char* WIFI_ALT_SSID      = "Xspace";
const char* WIFI_ALT_PASSWORD  = "12345678starlink";

// WiFi Alternatif 2
const char* WIFI_ALT2_SSID     = "TEFA";
const char* WIFI_ALT2_PASSWORD = "PEMBDA2026";

// WiFi Alternatif 3
const char* WIFI_ALT3_SSID     = "VISTAFAMILY";
const char* WIFI_ALT3_PASSWORD = "pelita31";

// Server API - JANGAN DIUBAH kecuali domain berubah
const char* SERVER_URL        = "https://perguruanpembda.com/api/attendance/rfid-scan";
const char* SCAN_BUFFER_URL   = "https://perguruanpembda.com/api/rfid/scan-buffer";
const char* KIOSK_API_KEY     = "RAHASIA-PEMBDAHUB-12345";

// ── GANTI DEVICE_ID UNTUK SETIAP STATION! ──
// Contoh: "KIOSK-ESP32F-01", "KIOSK-ESP32F-02", dst.
const char* DEVICE_ID         = "KIOSK-NEW-04";

// ============================================================
//  PIN DEFINITIONS - ESP32 Dev Module
// ============================================================

// SPI Pins untuk RFID RC522 (menggunakan VSPI default ESP32)
// SCK  = GPIO 18 - otomatis
// MISO = GPIO 19 - otomatis
// MOSI = GPIO 23 - otomatis
#define RFID_SS_PIN    5    // GPIO 5  - SPI CS
#define RFID_RST_PIN   4    // GPIO 4  - RFID Reset

// I2C LCD 20x4
// SDA = GPIO 21 - otomatis
// SCL = GPIO 22 - otomatis
#define LCD_ADDRESS    0x27
#define LCD_COLS       20
#define LCD_ROWS       4

// QR Scanner via Hardware Serial1 (RX only)
#define QR_RX_PIN      25   // GPIO 25 - Serial1 RX ← TX QR Scanner
#define QR_TX_PIN      -1   // Tidak dipakai (kita hanya terima data)

// MP3 Player (DFPlayer Mini) via Hardware Serial2
#define MP3_TX_PIN     17   // GPIO 17 - Serial2 TX → RX DFPlayer via R 1KΩ
#define MP3_RX_PIN     16   // GPIO 16 - Serial2 RX ← TX DFPlayer (opsional)
#define MP3_VOLUME     22   // Tingkat volume MP3 (0 s.d 30)

// Indikator
#define BUZZER_PIN     2    // GPIO 2  - Buzzer
#define LED_GREEN      13   // GPIO 13 - LED Hijau (Telah ditukar via software)
#define LED_RED        15   // GPIO 15 - LED Merah (Telah ditukar via software)

// ============================================================
//  TIMEOUTS & COOLDOWNS
// ============================================================
#define HTTP_TIMEOUT        8000   // 8 detik timeout HTTP
#define DISPLAY_RESULT_MS   3500   // Durasi tampil hasil di LCD
#define SCAN_COOLDOWN_MS    3000   // Anti double-tap (3 detik)
#define QR_MIN_LENGTH       3      // Minimum panjang QR valid

// ============================================================
//  GLOBAL STATE
// ============================================================
String        lastUID          = "";
unsigned long lastTapTime      = 0;
String        qrBuffer         = "";
unsigned long lastWiFiCheck    = 0;
bool          isOnline         = false;

// ============================================================
//  OBJEK HARDWARE
// ============================================================
MFRC522           rfid(RFID_SS_PIN, RFID_RST_PIN);
LiquidCrystal_I2C lcd(LCD_ADDRESS, LCD_COLS, LCD_ROWS);
HardwareSerial    qrSerial(1);   // UART1 ESP32 untuk QR Scanner
HardwareSerial    mp3Serial(2);  // UART2 ESP32 untuk MP3 Player

// ============================================================
//  SETUP
// ============================================================
void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println(F("\n=== ESP32 FULL STATION BOOTING ==="));
  Serial.println(F("Board: ESP32 Dev Module (Full Version)"));
  Serial.println(F("Fitur: RFID + QR + LCD 20x4 + MP3 + Buzzer + LED"));
  Serial.print(F("Device ID: ")); Serial.println(DEVICE_ID);
  Serial.print(F("Free Heap: ")); Serial.println(ESP.getFreeHeap());

  // Inisialisasi Pin Indikator
  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(LED_GREEN,  OUTPUT);
  pinMode(LED_RED,    OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);
  digitalWrite(LED_GREEN,  LOW);
  digitalWrite(LED_RED,    LOW);

  // Inisialisasi I2C LCD 20x4 (SDA=GPIO21, SCL=GPIO22)
  Wire.begin(21, 22);
#ifdef FDB_LIQUID_CRYSTAL_I2C_H
  lcd.begin();
#else
  lcd.init();
#endif
  lcd.backlight();
  lcd.setCursor(0, 0); lcd.print(F("===================="));
  lcd.setCursor(0, 1); lcd.print(F("   PEMBDA HUB v2    "));
  lcd.setCursor(0, 2); lcd.print(F("  Silakan Scan UID  "));
  lcd.setCursor(0, 3); lcd.print(F("===================="));
  Serial.println(F("LCD 20x4 OK."));
  delay(2000);

  // Inisialisasi SPI & RFID RC522
  SPI.begin();
  rfid.PCD_Init();
  delay(100);

  Serial.println(F("Mengecek modul RFID RC522..."));
  rfid.PCD_DumpVersionToSerial();

  // Cek apakah RFID terbaca
  byte version = rfid.PCD_ReadRegister(rfid.VersionReg);
  if (version == 0x00 || version == 0xFF) {
    Serial.println(F("WARNING: RFID tidak terdeteksi! Cek wiring SPI."));
    Serial.println(F("  SS=GPIO5, SCK=GPIO18, MOSI=GPIO23, MISO=GPIO19, RST=GPIO4"));
    lcd.setCursor(0, 2); lcd.print(F("RFID ERROR! Cek SPI "));
    beep(5, 100);
    delay(3000);
  } else {
    Serial.print(F("RFID Firmware Version: 0x"));
    Serial.print(version, HEX);
    if (version == 0x91 || version == 0x92) Serial.println(F(" (MFRC522 original)"));
    else if (version == 0x88)               Serial.println(F(" (FM17522 clone)"));
    else if (version == 0xB2)               Serial.println(F(" (MFRC522 clone - OK)"));
    else                                    Serial.println(F(" (compatible)"));

    // Naikkan antenna gain ke MAXIMUM untuk clone chip
    rfid.PCD_SetAntennaGain(rfid.RxGain_max);
    delay(10);
    rfid.PCD_AntennaOn();
    Serial.println(F("RFID Antenna Gain: MAX 48dB"));
  }

  // Koneksi WiFi
  connectWiFi();
  isOnline = (WiFi.status() == WL_CONNECTED);

  // ── INISIALISASI QR SCANNER (Serial1) ──
  // ESP32 Serial1 bisa di-remap ke GPIO manapun
  // Kita hanya butuh RX (menerima data dari scanner)
  qrSerial.begin(9600, SERIAL_8N1, QR_RX_PIN, QR_TX_PIN);
  delay(100);
  // Buang data noise awal saat QR module boot
  while (qrSerial.available()) qrSerial.read();
  qrBuffer = "";
  Serial.println(F("QR Scanner (Serial1 RX=GPIO25) OK."));

  // ── INISIALISASI MP3 PLAYER (Serial2) ──
  mp3Serial.begin(9600, SERIAL_8N1, MP3_RX_PIN, MP3_TX_PIN);
  delay(500);  // DFPlayer butuh ~300ms untuk boot
  while (mp3Serial.available()) mp3Serial.read();

  // Set volume MP3
  setMp3Volume(MP3_VOLUME);
  delay(100);

  // Putar lagu pembuka 001.mp3 ("Selamat Pagi Silahkan Absen")
  playAudio(1, 1);
  Serial.println(F("MP3 Player (Serial2 TX=GPIO17) OK. Volume: 22/30"));

  Serial.print(F("Free Heap setelah init: ")); Serial.println(ESP.getFreeHeap());
  Serial.println(F("System Ready. Silakan scan kartu RFID atau QR Code."));

  showReady();
}

// ============================================================
//  LOOP UTAMA
// ============================================================
void loop() {
  unsigned long now = millis();

  // Periksa status WiFi setiap 15 detik secara non-blocking
  if (now - lastWiFiCheck >= 15000 || lastWiFiCheck == 0) {
    lastWiFiCheck = now;
    bool connected = (WiFi.status() == WL_CONNECTED);
    if (connected && !isOnline) {
      isOnline = true;
      Serial.println(F("WiFi Terhubung Kembali."));
      showReady();
    } else if (!connected && isOnline) {
      isOnline = false;
      Serial.println(F("WiFi Terputus! Mencoba reconnect..."));
      WiFi.reconnect();
      showReady();
    }
  }

  // 1. Cek RFID
  handleRfidScan();

  // 2. Cek QR Code
  handleQrScan();

  delay(20);
}

// ============================================================
//  HANDLER RFID SCAN
// ============================================================
void handleRfidScan() {
  // Cek ada kartu baru di field
  if (!rfid.PICC_IsNewCardPresent()) return;

  // Coba baca serial (retry 1x untuk clone chip)
  if (!rfid.PICC_ReadCardSerial()) {
    delay(10);
    if (!rfid.PICC_ReadCardSerial()) return;
  }

  Serial.println(F("Kartu RFID terdeteksi!"));
  String uid = getRfidUID();
  Serial.println("RFID UID: " + uid);

  // Anti double-tap: abaikan UID sama dalam cooldown window
  unsigned long now = millis();
  if (uid == lastUID && (now - lastTapTime) < SCAN_COOLDOWN_MS) {
    Serial.println(F("RFID cooldown, abaikan."));
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
    return;
  }
  lastUID     = uid;
  lastTapTime = now;

  // Halt kartu SEBELUM HTTP (cegah re-trigger selama request berlangsung)
  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();

  // Tampilkan di LCD
  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("=== MEMPROSES ======"));
  lcd.setCursor(0, 1); lcd.print(F("Membaca kartu RFID  "));
  lcd.setCursor(0, 2); lcd.print("UID: " + uid);
  lcd.setCursor(0, 3); lcd.print(F("Mohon tunggu...     "));
  beep(1, 100);

  // Kirim ke server
  sendToServer(uid, "rfid");

  // Reset lastUID agar kartu yang sama bisa scan lagi (check-out)
  lastUID = "";
}

// ============================================================
//  HANDLER QR CODE (via Hardware Serial1)
//  Membaca data dari QR Scanner GM65/GM50.
//  Lebih stabil dari SoftwareSerial karena pakai UART hardware.
// ============================================================
void handleQrScan() {
  int maxRead = 10;  // ESP32 lebih cepat, bisa baca lebih banyak per loop

  for (int i = 0; i < maxRead && qrSerial.available() > 0; i++) {
    char c = qrSerial.read();

    // Karakter akhir baris = data QR selesai
    if (c == '\r' || c == '\n') {
      if (qrBuffer.length() >= QR_MIN_LENGTH) {
        String qrData = qrBuffer;
        qrData.trim();
        qrBuffer = "";

        Serial.println(F("================================="));
        Serial.println("QR Code Terdeteksi: " + qrData);
        Serial.println(F("================================="));

        // Anti double-scan
        unsigned long now = millis();
        if (qrData == lastUID && (now - lastTapTime) < SCAN_COOLDOWN_MS) {
          Serial.println(F("QR Cooldown. Abaikan."));
          return;
        }
        lastUID     = qrData;
        lastTapTime = now;

        // Tampilkan di LCD
        lcd.clear();
        lcd.setCursor(0, 0); lcd.print(F("=== MEMPROSES ======"));
        lcd.setCursor(0, 1); lcd.print(F("Kode QR Terbaca     "));
        lcd.setCursor(0, 2); lcd.print("ID: " + qrData.substring(0, min((int)qrData.length(), 16)));
        lcd.setCursor(0, 3); lcd.print(F("Menghubungi server.."));
        beep(1, 150);

        sendToServer(qrData, "qr");

        // Reset lastUID setelah selesai
        lastUID = "";
        return;
      }
      qrBuffer = "";
    }
    // Karakter printable valid (spasi sampai tilde)
    else if (c >= 32 && c <= 126) {
      if (qrBuffer.length() < 128) {
        qrBuffer += c;
      } else {
        qrBuffer = "";  // Buffer overflow, reset
      }
    }
    // Karakter noise (non-printable) → abaikan saja
  }
}

// ============================================================
//  KIRIM DATA KE SERVER (HTTPS)
// ============================================================
void sendToServer(String uid, String type) {
  Serial.println("Mengirim ke server: " + uid + " (" + type + ")");
  Serial.print(F("Free Heap sebelum HTTPS: ")); Serial.println(ESP.getFreeHeap());

  if (WiFi.status() != WL_CONNECTED) {
    playAudio(1, 7); // 007.MP3 : "Sistem Ada Gangguan Hubungi Admin"
    showError("Koneksi Internet Off");
    return;
  }

  WiFiClientSecure client;
  client.setInsecure();  // Skip verifikasi SSL cert

  HTTPClient http;
  http.begin(client, SERVER_URL);
  http.addHeader("Content-Type",    "application/json");
  http.addHeader("X-Kiosk-API-Key", KIOSK_API_KEY);
  http.addHeader("Accept",          "application/json");
  http.setTimeout(HTTP_TIMEOUT);

  // Buat JSON request body
  StaticJsonDocument<128> doc;
  doc["uid"]       = uid;
  doc["type"]      = type;
  doc["device_id"] = DEVICE_ID;
  String body;
  serializeJson(doc, body);

  Serial.println("POST body: " + body);
  int code = http.POST(body);
  Serial.print("HTTP Response Code: "); Serial.println(code);
  Serial.print(F("Free Heap setelah POST: ")); Serial.println(ESP.getFreeHeap());

  if (code == 200 || code == 201) {
    String payload = http.getString();
    Serial.println("Server Response: " + payload);
    parseAndDisplay(payload, uid);
  } else if (code == 401) {
    playAudio(1, 7); // 007.MP3 : "Sistem Ada Gangguan Hubungi Admin"
    showError("API Key Tidak Valid!");
  } else if (code == 404) {
    playAudio(1, 6); // 006.MP3 : "Kartu Tidak Dikenali Hubungi Admin"
    showError("Kartu/QR Tdk Terdaftar");
  } else if (code > 0) {
    playAudio(1, 7); // 007.MP3 : "Sistem Ada Gangguan Hubungi Admin"
    showError("Gagal Server: " + String(code));
  } else {
    String errMsg = http.errorToString(code);
    Serial.println("HTTP Error: " + errMsg);
    // Coba baca body meski error code
    String payload = http.getString();
    if (payload.length() > 10 && payload.indexOf("status") > 0) {
      Serial.println("Response walau error: " + payload);
      parseAndDisplay(payload, uid);
    } else {
      playAudio(1, 7); // 007.MP3 : "Sistem Ada Gangguan Hubungi Admin"
      showError("Server Error: " + errMsg.substring(0, 14));
    }
  }

  http.end();

  // Buang noise QR yang masuk selama proses HTTP
  while (qrSerial.available()) qrSerial.read();
  qrBuffer = "";

  Serial.print(F("Free Heap setelah http.end: ")); Serial.println(ESP.getFreeHeap());
  delay(DISPLAY_RESULT_MS);
  showReady();
}

// ============================================================
//  KIRIM UID KE SCAN BUFFER (untuk fitur daftar RFID di web)
//  Dipanggil saat action_code == "NEW_CARD"
//  Admin buka halaman daftar → UID sudah muncul otomatis di modal
// ============================================================
void sendScanBuffer(String uid) {
  if (WiFi.status() != WL_CONNECTED) return;

  Serial.println(F("Mengirim UID ke scan-buffer..."));

  WiFiClientSecure client;
  client.setInsecure();

  HTTPClient http;
  http.begin(client, SCAN_BUFFER_URL);
  http.addHeader("Content-Type",    "application/json");
  http.addHeader("X-Kiosk-API-Key", KIOSK_API_KEY);
  http.setTimeout(5000);

  StaticJsonDocument<64> doc;
  doc["uid"] = uid;
  String body;
  serializeJson(doc, body);

  int code = http.POST(body);
  Serial.print(F("Scan-buffer response: ")); Serial.println(code);

  http.end();
}

// ============================================================
//  PARSE RESPONSE JSON & TAMPILKAN DI LCD 20x4
// ============================================================
void parseAndDisplay(String json, String uid) {
  StaticJsonDocument<256> doc;
  if (deserializeJson(doc, json)) {
    showError("Format Data Rusak");
    return;
  }

  String status      = doc["status"]      | "error";
  String nama        = doc["nama"]        | "Tidak dikenal";
  String kelas       = doc["kelas"]       | "";
  String message     = doc["message"]     | "";
  String waktu       = doc["waktu"]       | "";
  String action_code = doc["action_code"] | "";

  // Potong agar pas di 20 karakter (LCD 20x4)
  String namaDisplay  = nama.substring(0, min((int)nama.length(), 20));
  String kelasDisplay = kelas.substring(0, min((int)kelas.length(), 20));

  if (status == "success" || status == "info") {

    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(namaDisplay);

    if (kelasDisplay != "") {
      lcd.setCursor(0, 1); lcd.print(kelasDisplay);
    } else {
      lcd.setCursor(0, 1); lcd.print("-");
    }

    if (action_code == "CHECK_IN") {
      lcd.setCursor(0, 2); lcd.print("MASUK PADA: " + waktu);
      // Bedakan audio & teks: Guru/Staf → 005, Siswa → 002
      if (kelasDisplay.indexOf("Guru") >= 0 || kelasDisplay.indexOf("Staf") >= 0 || kelasDisplay.indexOf("Staff") >= 0) {
        lcd.setCursor(0, 3); lcd.print(F("Selamat Bertugas!   "));
        playAudio(1, 5); // 005.MP3 : "Selamat Pagi Selamat Bekerja"
      } else {
        lcd.setCursor(0, 3); lcd.print(F("Selamat Belajar!    "));
        playAudio(1, 2); // 002.MP3 : "Akses Diterima Selamat Belajar"
      }
      indicatorCheckIn();
    }
    else if (action_code == "CHECK_OUT") {
      lcd.setCursor(0, 2); lcd.print("PULANG PADA: " + waktu);
      lcd.setCursor(0, 3); lcd.print(F("Hati-hati di jalan! "));
      playAudio(1, 3); // 003.MP3 : "Absen Pulang Sampai Jumpa Besok Pagi"
      indicatorCheckOut();
    }
    else if (action_code == "COOLDOWN") {
      lcd.setCursor(0, 2); lcd.print(F("Sudah Absen Masuk!  "));
      if (waktu != "") {
        lcd.setCursor(0, 3); lcd.print("Jam Masuk: " + waktu);
      } else {
        lcd.setCursor(0, 3); lcd.print(message.substring(0, min((int)message.length(), 20)));
      }
      playAudio(1, 4); // 004.MP3 : "Absen Sudah Tercatat Terima Kasih"
      indicatorCooldown();
    }
    else if (action_code == "NEW_CARD") {
      // Kartu belum terdaftar → kirim UID ke scan-buffer
      sendScanBuffer(uid);

      lcd.clear();
      lcd.setCursor(0, 0); lcd.print(F("** KARTU BARU **    "));
      lcd.setCursor(0, 1); lcd.print(kelasDisplay);
      lcd.setCursor(0, 2); lcd.print(F("Belum terdaftar!    "));
      lcd.setCursor(0, 3); lcd.print(F("Daftarkan di Admin  "));
      playAudio(1, 6); // 006.MP3 : "Kartu Tidak Dikenali Hubungi Admin"
      indicatorNewCard();
    }
    else {
      // Status lain yang tidak dikenal
      lcd.setCursor(0, 2); lcd.print(message.substring(0, min((int)message.length(), 20)));
      lcd.setCursor(0, 3); lcd.print("Waktu: " + waktu);
      playAudio(1, 2); // Default = pola check-in siswa
      indicatorCheckIn();
    }

  } else {
    // Status error dari server
    String errMsg;
    if (action_code == "ALREADY_ATTENDED") {
      errMsg = "Sudah Absen Lengkap";
      playAudio(1, 4); // 004.MP3 : "Absen Sudah Tercatat Terima Kasih"
    } else {
      errMsg = message;
      playAudio(1, 7); // 007.MP3 : "Sistem Ada Gangguan Hubungi Admin"
    }
    showError(errMsg);
  }
}

// ============================================================
//  KONEKSI WIFI (dengan fallback ke SSID alternatif)
// ============================================================
void connectWiFi() {
  WiFi.mode(WIFI_STA);

  // Daftar semua WiFi yang akan dicoba berurutan
  struct WifiEntry {
    const char* ssid;
    const char* pass;
  };
  WifiEntry wifiList[] = {
    { WIFI_SSID,       WIFI_PASSWORD       },
    { WIFI_ALT_SSID,   WIFI_ALT_PASSWORD   },
    { WIFI_ALT2_SSID,  WIFI_ALT2_PASSWORD  },
    { WIFI_ALT3_SSID,  WIFI_ALT3_PASSWORD  }
  };
  const int wifiCount = 4;

  for (int w = 0; w < wifiCount; w++) {
    Serial.print(F("Mencoba WiFi: "));
    Serial.println(wifiList[w].ssid);

    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(F("=== MENCARI WIFI ==="));
    lcd.setCursor(0, 1); lcd.print("SSID: " + String(wifiList[w].ssid).substring(0, 14));
    lcd.setCursor(0, 2); lcd.print(F("Menghubungkan...    "));
    lcd.setCursor(0, 3); lcd.print(F("--------------------"));

    WiFi.disconnect(true);
    delay(200);
    WiFi.begin(wifiList[w].ssid, wifiList[w].pass);

    int attempt = 0;
    int maxAttempt = (w == 0) ? 20 : 14;  // Utama 10s, alternatif 7s
    while (WiFi.status() != WL_CONNECTED && attempt < maxAttempt) {
      delay(500);
      Serial.print(".");
      attempt++;
      String dots = "";
      for (int i = 0; i < (attempt % 6); i++) dots += ".";
      lcd.setCursor(0, 2); lcd.print("Menghubungkan" + dots + "    ");
    }

    if (WiFi.status() == WL_CONNECTED) break;  // Berhasil, hentikan loop
    Serial.println(F(" Gagal."));
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nIP: " + WiFi.localIP().toString());
    Serial.println("SSID: " + WiFi.SSID());
    Serial.print("RSSI: "); Serial.print(WiFi.RSSI()); Serial.println(" dBm");

    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(F("=== WIFI CONNECT ==="));
    lcd.setCursor(0, 1); lcd.print("SSID: " + WiFi.SSID().substring(0, 14));
    lcd.setCursor(0, 2); lcd.print("IP: " + WiFi.localIP().toString());
    lcd.setCursor(0, 3); lcd.print(F("--------------------"));
    beep(1, 200);
    delay(2000);
  } else {
    Serial.println(F("\nWiFi Gagal! Mode offline."));
    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(F("=== WIFI GAGAL ====="));
    lcd.setCursor(0, 1); lcd.print(F("Koneksi gagal!      "));
    lcd.setCursor(0, 2); lcd.print(F("Auto-retry 15 dtk   "));
    lcd.setCursor(0, 3); lcd.print(F("--------------------"));
    beep(3, 200);
    delay(2000);
  }
}

// ============================================================
//  FUNGSI DISPLAY LCD 20x4
// ============================================================

void showReady() {
  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("===================="));
  lcd.setCursor(0, 1); lcd.print(F("   PEMBDA HUB v2    "));
  lcd.setCursor(0, 2); lcd.print(F("  Silakan Scan UID  "));
  if (isOnline) {
    lcd.setCursor(0, 3); lcd.print(F("Status: READY       "));
  } else {
    lcd.setCursor(0, 3); lcd.print(F("Status: OFFLINE     "));
  }
}

void showError(String msg) {
  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("=== !!! ERROR !!! =="));
  if (msg.length() > 20) {
    lcd.setCursor(0, 1); lcd.print(msg.substring(0, 20));
    lcd.setCursor(0, 2); lcd.print(msg.substring(20, min((int)msg.length(), 40)));
  } else {
    lcd.setCursor(0, 1); lcd.print(msg);
    lcd.setCursor(0, 2); lcd.print(F("Silakan coba lagi   "));
  }
  lcd.setCursor(0, 3); lcd.print(F("--------------------"));
  indicatorFail();
}

// ============================================================
//  INDIKATOR (Buzzer + LED)
//
//  ┌────────────┬──────────────────────┬──────────────────┐
//  │ Status     │ Pola Buzzer          │ LED              │
//  ├────────────┼──────────────────────┼──────────────────┤
//  │ CHECK_IN   │ ♪♪  (2x 100ms)      │ Hijau 200ms      │
//  │ CHECK_OUT  │ ♪♪♪ (3x 80ms)       │ Hijau 200ms      │
//  │ COOLDOWN   │ ♪── (1x 300ms)      │ Merah 200ms      │
//  │ NEW_CARD   │ ♪♪♪♪(4x 60ms)       │ Merah kedip 4x   │
//  │ ERROR/FAIL │ ♪─── (1x 600ms)     │ Merah 300ms      │
//  └────────────┴──────────────────────┴──────────────────┘
// ============================================================

// CHECK_IN: 2 beep pendek "tit-tit" + LED Hijau
void indicatorCheckIn() {
  digitalWrite(LED_GREEN, HIGH);
  for (int i = 0; i < 2; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(100);
    digitalWrite(BUZZER_PIN, LOW);
    if (i < 1) delay(100);
  }
  delay(200);
  digitalWrite(LED_GREEN, LOW);
}

// CHECK_OUT: 3 beep cepat "tit-tit-tit" + LED Hijau
void indicatorCheckOut() {
  digitalWrite(LED_GREEN, HIGH);
  for (int i = 0; i < 3; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(80);
    digitalWrite(BUZZER_PIN, LOW);
    if (i < 2) delay(80);
  }
  delay(200);
  digitalWrite(LED_GREEN, LOW);
}

// COOLDOWN: 1 beep panjang "tiiiit" + LED Merah
void indicatorCooldown() {
  digitalWrite(LED_RED, HIGH);
  digitalWrite(BUZZER_PIN, HIGH);
  delay(300);
  digitalWrite(BUZZER_PIN, LOW);
  delay(200);
  digitalWrite(LED_RED, LOW);
}

// NEW_CARD: 4 beep sangat cepat + LED Merah kedip
void indicatorNewCard() {
  for (int i = 0; i < 4; i++) {
    digitalWrite(LED_RED, HIGH);
    digitalWrite(BUZZER_PIN, HIGH);
    delay(60);
    digitalWrite(BUZZER_PIN, LOW);
    digitalWrite(LED_RED, LOW);
    if (i < 3) delay(60);
  }
}

// ERROR/FAIL: 1 beep panjang sekali + LED Merah
void indicatorFail() {
  digitalWrite(LED_RED, HIGH);
  digitalWrite(BUZZER_PIN, HIGH);
  delay(600);
  digitalWrite(BUZZER_PIN, LOW);
  delay(200);
  digitalWrite(LED_RED, LOW);
}

// Beep generik (untuk boot, WiFi, dll.)
void beep(int count, int duration) {
  for (int i = 0; i < count; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(duration);
    digitalWrite(BUZZER_PIN, LOW);
    if (i < count - 1) delay(100);
  }
}

// ============================================================
//  FUNGSI MP3 PLAYER (DFPlayer Mini) via Hardware Serial2
//
//  Menggunakan protokol serial DFPlayer Mini:
//  [0x7E][0xFF][0x06][CMD][0x00][PAR1][PAR2][0xEF]
//
//  Daftar Audio:
//  ┌───────┬──────────────────────────────────────────────┐
//  │ Track │ Isi Audio                                    │
//  ├───────┼──────────────────────────────────────────────┤
//  │ 001   │ "Selamat Pagi Silahkan Absen"        (Boot)  │
//  │ 002   │ "Akses Diterima Selamat Belajar"     (Siswa) │
//  │ 003   │ "Absen Pulang Sampai Jumpa"          (Pulang)│
//  │ 004   │ "Absen Sudah Tercatat Terima Kasih"  (Cool.) │
//  │ 005   │ "Selamat Pagi Selamat Bekerja"       (Guru)  │
//  │ 006   │ "Kartu Tidak Dikenali Hubungi Admin" (New)   │
//  │ 007   │ "Sistem Ada Gangguan Hubungi Admin"  (Error) │
//  └───────┴──────────────────────────────────────────────┘
// ============================================================

// Kirim perintah raw byte ke DFPlayer Mini via Serial2
void sendMp3Command(uint8_t cmd, uint8_t para1, uint8_t para2) {
  uint8_t cmdBuffer[8] = { 0x7E, 0xFF, 0x06, cmd, 0x00, para1, para2, 0xEF };
  mp3Serial.write(cmdBuffer, 8);
  delay(100); // Jeda aman pemrosesan chip DFPlayer (clone butuh lebih lama)
}

// Putar track tertentu di folder tertentu (1-indexed)
// Contoh: playAudio(1, 2) = putar folder 01, file 002.mp3
void playAudio(uint8_t folder, uint8_t track) {
  sendMp3Command(0x0F, folder, track);
}

// Atur volume (0-30)
void setMp3Volume(uint8_t vol) {
  if (vol > 30) vol = 30;
  sendMp3Command(0x06, 0x00, vol);
}

// ============================================================
//  BACA UID RFID (format HEX uppercase, contoh: "A3B2C1D4")
// ============================================================
String getRfidUID() {
  if (rfid.uid.size < 4) return "";
  
  String hexUID = "";
  for (int i = 3; i >= 0; i--) {
    if (rfid.uid.uidByte[i] < 0x10) hexUID += "0";
    hexUID += String(rfid.uid.uidByte[i], HEX);
  }
  
  hexUID.toUpperCase();
  return hexUID;
}

