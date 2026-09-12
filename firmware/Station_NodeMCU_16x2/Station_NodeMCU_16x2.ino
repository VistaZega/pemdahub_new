// ============================================================
//  FIRMWARE NODEMCU V3 (ESP-12F) - PEMBDAHUB ATTENDANCE STATION
//  Versi: RFID + LCD 16x2 + Buzzer + LED + MP3 Player
//  
//  Diporting dari versi 20x4 dan QR Scanner, disesuaikan 
//  untuk layar LCD 16x2 dan penambahan indikator LED fisik.
//  Server endpoint, format JSON, dan logika audio 100% identik.
//
//  PERUBAHAN DARI VERSI SEBELUMNYA:
//  1. Layar LCD disesuaikan ke resolusi 16x2 (teks lebih padat & rapi).
//  2. QR Scanner dihapus karena keterbatasan pin.
//  3. Pin D3 (sebelumnya QR RX) dialihfungsikan untuk Indikator LED.
//  4. LED akan menyala bersamaan dengan pola Buzzer.
//
//  WIRING DIAGRAM NODEMCU V3:
//  ┌─────────────┬──────────┬───────────────────────────────┐
//  │ Komponen    │ Pin MCU  │ Catatan                       │
//  ├─────────────┼──────────┼───────────────────────────────┤
//  │ RFID SDA/SS │ D0 (16)  │ SPI CS (D8 pull-down ganggu!) │
//  │ RFID SCK    │ D5 (14)  │ SPI CLK default               │
//  │ RFID MOSI   │ D7 (13)  │ SPI MOSI default              │
//  │ RFID MISO   │ D6 (12)  │ SPI MISO default              │
//  │ RFID RST    │ -        │ Hubungkan langsung ke 3.3V    │
//  │ LCD SDA     │ D2 (4)   │ I2C SDA default               │
//  │ LCD SCL     │ D1 (5)   │ I2C SCL default               │
//  │ MP3 TX      │ D4 (2)   │ SoftwareSerial TX → RX DFP    │
//  │ Buzzer (+)  │ D8 (15)  │ Pull-down = buzzer OFF @boot  │
//  │ LED (+)     │ D3 (0)   │ Indikator Visual (LED)        │
//  │ RFID 3.3V   │ 3V3      │ JANGAN pakai 5V untuk RC522!  │
//  │ LCD VCC     │ 5V       │ LCD 16x2 butuh 5V!            │
//  │ MP3 VCC     │ 5V       │ DFPlayer Mini butuh 5V!       │
//  │ GND         │ GND      │ Common ground semua komponen  │
//  └─────────────┴──────────┴───────────────────────────────┘
//
//  CATATAN WIRING LED DI D3 (GPIO0):
//  - GPIO0 harus bernilai HIGH sesaat ketika ESP8266 baru dinyalakan (boot).
//  - Pastikan menggunakan resistor minimal 1K Ohm agar LED tidak "menarik" 
//    pin ini menjadi LOW yang mengakibatkan NodeMCU gagal boot.
//
//  WIRING MP3 PLAYER (DFPlayer Mini):
//  ┌──────────────────────────────────────────────────────┐
//  │  NodeMCU D4 (GPIO2) ──[1KΩ]──→ DFPlayer RX          │
//  │  5V Eksternal ────────────────→ DFPlayer VCC         │
//  │  GND ─────────────────────────→ DFPlayer GND         │
//  │  DFPlayer SPK_1 ──────────────→ Speaker (+)          │
//  │  DFPlayer SPK_2 ──────────────→ Speaker (-)          │
//  └──────────────────────────────────────────────────────┘
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
// ============================================================

#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>
#include <WiFiClientSecure.h>
#include <ESP8266WiFiMulti.h>
#include <SoftwareSerial.h>

// ============================================================
//  KONFIGURASI - Sesuaikan untuk setiap station!
// ============================================================

// WiFi Utama
const char* WIFI_SSID         = "PembdaLINK";
const char* WIFI_PASSWORD     = "PEMBDA2026";

// WiFi Alternatif 1 (otomatis fallback jika utama gagal)
const char* WIFI_ALT_SSID     = "Xspace";
const char* WIFI_ALT_PASSWORD = "12345678starlink";

// WiFi Alternatif 2
const char* WIFI_ALT2_SSID    = "TEFA";
const char* WIFI_ALT2_PASSWORD= "PEMBDA2026";

// WiFi Alternatif 3
const char* WIFI_ALT3_SSID    = "VISTAFAMILY";
const char* WIFI_ALT3_PASSWORD= "pelita31";

// Server API - JANGAN DIUBAH kecuali domain berubah
const char* SERVER_URL        = "https://perguruanpembda.com/api/attendance/rfid-scan";
const char* KIOSK_API_KEY     = "RAHASIA-PEMBDAHUB-12345";

// ── GANTI DEVICE_ID UNTUK SETIAP STATION! ──
const char* DEVICE_ID         = "STATION-SMK-01";

// ============================================================
//  PIN DEFINITIONS - NodeMCU V3 (ESP-12F)
// ============================================================

// SPI Pins untuk RFID RC522 (menggunakan HSPI default ESP8266)
#define RFID_SS_PIN    16   // D0 (GPIO16) - SPI CS (D8 pull-down ganggu SPI!)
#define RFID_RST_PIN  255   // UNUSED - Hubungkan pin RST RFID langsung ke 3.3V NodeMCU

// MP3 Player TX Pin
#define MP3_TX_PIN      2   // D4 (GPIO2) - Hubungkan ke RX MP3 Player via resistor 1K Ohm
#define MP3_VOLUME     22   // Tingkat volume MP3 (0 s.d 30)

// Indikator Suara dan Cahaya
#define BUZZER_PIN     15   // D8 (GPIO15) - pull-down bawaan = buzzer OFF saat boot
#define LED_PIN         0   // D3 (GPIO0)  - LED Indikator (Gunakan Resistor 1K)

// I2C LCD 16x2
#define LCD_ADDRESS    0x27
#define LCD_COLS       16
#define LCD_ROWS       2

// ============================================================
//  TIMEOUTS & COOLDOWNS
// ============================================================
#define HTTP_TIMEOUT        10000   // 10 detik (ESP8266 TLS lebih lambat)
#define DISPLAY_RESULT_MS   3500    // Durasi tampil hasil di LCD
#define SCAN_COOLDOWN_MS    3000    // Anti double-tap (3 detik)

// ============================================================
//  GLOBAL STATE
// ============================================================
String        lastUID          = "";
unsigned long lastTapTime      = 0;
unsigned long lastWiFiCheck    = 0;
bool          isOnline         = false;

// ============================================================
//  OBJEK HARDWARE
// ============================================================
MFRC522           rfid(RFID_SS_PIN, RFID_RST_PIN);
LiquidCrystal_I2C lcd(LCD_ADDRESS, LCD_COLS, LCD_ROWS);
ESP8266WiFiMulti  wifiMulti;
SoftwareSerial    mp3Serial(-1, MP3_TX_PIN); // RX=-1 (tidak dipakai), TX untuk MP3 Player

// ============================================================
//  SETUP
// ============================================================
void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println(F("\n=== NODEMCU STATION BOOTING ==="));
  Serial.println(F("Board: NodeMCU V3 (ESP-12F) - 16x2 + LED + MP3"));
  Serial.print(F("Device ID: ")); Serial.println(DEVICE_ID);

  // Inisialisasi Buzzer & LED
  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(LED_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);
  digitalWrite(LED_PIN, LOW);

  // Inisialisasi I2C LCD (SDA=GPIO4/D2, SCL=GPIO5/D1)
  Wire.begin(4, 5);
#ifdef FDB_LIQUID_CRYSTAL_I2C_H
  lcd.begin();
#else
  lcd.init();
#endif
  lcd.backlight();
  lcd.setCursor(0, 0); lcd.print(F("   PEMBDA HUB   "));
  lcd.setCursor(0, 1); lcd.print(F("  Silakan Scan  "));
  delay(2000);

  // ── INISIALISASI SPI & RFID RC522 ──
  SPI.begin();
  SPI.setFrequency(1000000); // 1MHz agar Clone chip 0xB2 bekerja stabil
  delay(50);
  
  rfid.PCD_Init();
  delay(150);

  Serial.println(F("Mengecek modul RFID RC522..."));
  byte version = rfid.PCD_ReadRegister(rfid.VersionReg);
  if (version == 0x00 || version == 0xFF) {
    Serial.println(F("WARNING: RFID tidak terdeteksi! Cek wiring SPI."));
    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(F("RFID ERROR!     "));
    lcd.setCursor(0, 1); lcd.print(F("Cek Jalur SPI   "));
    beep(5, 100);
    delay(2000);
  } else {
    // Naikkan antenna gain ke MAXIMUM untuk sensitivitas terbaik
    rfid.PCD_SetAntennaGain(rfid.RxGain_max);
    delay(10);
    rfid.PCD_AntennaOn();
  }

  // Koneksi WiFi dengan multi-AP
  wifiMulti.addAP(WIFI_SSID, WIFI_PASSWORD);
  wifiMulti.addAP(WIFI_ALT_SSID, WIFI_ALT_PASSWORD);
  wifiMulti.addAP(WIFI_ALT2_SSID, WIFI_ALT2_PASSWORD);
  wifiMulti.addAP(WIFI_ALT3_SSID, WIFI_ALT3_PASSWORD);
  connectWiFi();
  isOnline = (WiFi.status() == WL_CONNECTED);
  showReady();

  // Inisialisasi SoftwareSerial untuk MP3 Player
  mp3Serial.begin(9600);
  delay(100);

  // Inisialisasi Volume MP3 Player
  setMp3Volume(MP3_VOLUME);

  // Putar lagu pembuka 001.mp3 ("Selamat Pagi Silahkan Absen")
  playAudio(1, 1);

  Serial.println(F("System Ready. Silakan scan kartu RFID."));
}

// ============================================================
//  LOOP UTAMA
// ============================================================
void loop() {
  unsigned long now = millis();

  // Periksa status WiFi setiap 10 detik secara non-blocking
  if (now - lastWiFiCheck >= 10000 || lastWiFiCheck == 0) {
    lastWiFiCheck = now;
    if (wifiMulti.run() == WL_CONNECTED) {
      if (!isOnline) {
        isOnline = true;
        Serial.println(F("WiFi Terhubung Kembali."));
        showReady();
      }
    } else {
      if (isOnline) {
        isOnline = false;
        Serial.println(F("WiFi Terputus! Mencoba mencari jaringan..."));
        showReady();
      }
    }
  }

  // Cek RFID
  handleRfidScan();

  // Delay kecil agar WDT ESP8266 tidak trigger
  delay(20);
}

// ============================================================
//  HANDLER RFID SCAN
// ============================================================
void handleRfidScan() {
  // Cek ada kartu baru di field
  if (!rfid.PICC_IsNewCardPresent()) return;

  // Coba baca serial, jika gagal coba sekali lagi (untuk clone chip)
  if (!rfid.PICC_ReadCardSerial()) {
    delay(10);
    if (!rfid.PICC_ReadCardSerial()) return;
  }

  String uid = getRfidUID();
  Serial.println("RFID UID: " + uid);

  // Anti double-tap: abaikan UID sama dalam cooldown window
  unsigned long now = millis();
  if (uid == lastUID && (now - lastTapTime) < SCAN_COOLDOWN_MS) {
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
    return;
  }
  lastUID     = uid;
  lastTapTime = now;

  // Tampilkan di LCD
  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("MEMPROSES...    "));
  lcd.setCursor(0, 1); lcd.print("ID:" + uid);
  beep(1, 100);

  // Halt kartu SEBELUM kirim HTTP (agar tidak re-trigger selama HTTP berlangsung)
  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();

  // Kirim ke server
  sendToServer(uid, "rfid");

  // Reset lastUID setelah selesai agar kartu yang sama bisa scan lagi
  lastUID = "";
}

// ============================================================
//  KIRIM DATA KE SERVER (HTTPS via BearSSL)
// ============================================================
void sendToServer(String uid, String type) {
  if (WiFi.status() != WL_CONNECTED) {
    showError("Internet Offline");
    return;
  }

  // BearSSL WiFiClientSecure - skip validasi sertifikat
  BearSSL::WiFiClientSecure client;
  client.setInsecure();

  HTTPClient http;
  http.begin(client, String(SERVER_URL));
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

  int code = http.POST(body);

  if (code == 200 || code == 201) {
    String payload = http.getString();
    parseAndDisplay(payload);
  } else if (code == 401) {
    playAudio(1, 7); // 007.MP3
    showError("API Key Invalid!");
  } else if (code == 404) {
    playAudio(1, 6); // 006.MP3
    showError("Kartu Tdk Kenal ");
  } else if (code > 0) {
    playAudio(1, 7); // 007.MP3
    showError("Server Err " + String(code));
  } else {
    String errMsg = http.errorToString(code);
    String payload = http.getString();
    
    // Fallback jika body tetap ada meskipun code negatif (kadang terjadi di BearSSL)
    if (payload.length() > 10 && payload.indexOf("status") > 0) {
      parseAndDisplay(payload);
    } else {
      playAudio(1, 7); // 007.MP3
      showError("Gagal Terhubung ");
    }
  }

  http.end();
  delay(DISPLAY_RESULT_MS);
  showReady();
}

// ============================================================
//  PARSE RESPONSE JSON (Disesuaikan untuk LCD 16x2)
// ============================================================
void parseAndDisplay(String json) {
  StaticJsonDocument<256> doc;
  if (deserializeJson(doc, json)) {
    showError("Data Rusak");
    return;
  }

  String status      = doc["status"]      | "error";
  String nama        = doc["nama"]        | "Tidak dikenal";
  String kelas       = doc["kelas"]       | "";
  String message     = doc["message"]     | "";
  String waktu       = doc["waktu"]       | "";
  String action_code = doc["action_code"] | "";

  if (status == "success" || status == "info") {
    // Potong string agar muat di LCD 16 karakter
    String namaDisplay  = nama.substring(0, min((int)nama.length(), 16));
    String kelasDisplay = kelas.substring(0, min((int)kelas.length(), 16));
    String waktuShort   = waktu.substring(0, min((int)waktu.length(), 11)); // e.g. 07:15:00

    lcd.clear();
    
    if (action_code == "CHECK_IN") {
      lcd.setCursor(0, 0); lcd.print(namaDisplay);
      lcd.setCursor(0, 1); lcd.print("Masuk: " + waktuShort);
      
      if (kelasDisplay.indexOf("Guru") >= 0 || kelasDisplay.indexOf("Staf") >= 0 || kelasDisplay.indexOf("Staff") >= 0) {
        playAudio(1, 5); // 005.MP3 : "Selamat Pagi Selamat Bekerja"
      } else {
        playAudio(1, 2); // 002.MP3 : "Akses Diterima Selamat Belajar"
      }
      indicatorCheckIn();
    }
    else if (action_code == "CHECK_OUT") {
      lcd.setCursor(0, 0); lcd.print(namaDisplay);
      lcd.setCursor(0, 1); lcd.print("Pulang:" + waktuShort);
      playAudio(1, 3); // 003.MP3 : "Absen Pulang Sampai Jumpa"
      indicatorCheckOut();
    }
    else if (action_code == "COOLDOWN") {
      lcd.setCursor(0, 0); lcd.print(F("Sudah Absen!    "));
      if (waktu != "") {
        lcd.setCursor(0, 1); lcd.print("Jam: " + waktuShort);
      } else {
        lcd.setCursor(0, 1); lcd.print(message.substring(0, 16));
      }
      playAudio(1, 4); // 004.MP3 : "Absen Sudah Tercatat"
      indicatorCooldown();
    }
    else if (action_code == "NEW_CARD") {
      lcd.setCursor(0, 0); lcd.print(F("KARTU BARU      "));
      lcd.setCursor(0, 1); lcd.print(F("Belum Terdaftar "));
      playAudio(1, 6); // 006.MP3 : "Kartu Tidak Dikenali"
      indicatorNewCard();
    }
    else {
      lcd.setCursor(0, 0); lcd.print(message.substring(0, 16));
      lcd.setCursor(0, 1); lcd.print("Waktu: " + waktuShort);
      playAudio(1, 2); 
      indicatorCheckIn();  
    }
  } else {
    String errMsg = (action_code == "ALREADY_ATTENDED") ? "Sudah Absen     " : message;
    if (action_code == "ALREADY_ATTENDED") {
      playAudio(1, 4); 
    } else {
      playAudio(1, 7); 
    }
    showError(errMsg);
  }
}

// ============================================================
//  KONEKSI WIFI
// ============================================================
void connectWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.persistent(false); 
  int attempt = 0;

  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("MENCARI WIFI... "));

  while (wifiMulti.run() != WL_CONNECTED && attempt < 20) {
    delay(500);
    String dots = "";
    for (int i = 0; i < (attempt % 4) + 1; i++) dots += ".";
    lcd.setCursor(0, 1); lcd.print("Menghubungkan   ");
    lcd.setCursor(13, 1); lcd.print(dots);
    attempt++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(F("WIFI CONNECTED! "));
    lcd.setCursor(0, 1); lcd.print(WiFi.localIP().toString());
    beep(1, 200);
    delay(1500);
  } else {
    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(F("WIFI GAGAL!     "));
    lcd.setCursor(0, 1); lcd.print(F("Auto-retry bg..."));
    beep(3, 100);
    delay(2000);
  }
}

// ============================================================
//  FUNGSI DISPLAY UMUM
// ============================================================

void showReady() {
  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("   PEMBDA HUB   "));
  if (isOnline) {
    lcd.setCursor(0, 1); lcd.print(F("  Silakan Scan  "));
  } else {
    lcd.setCursor(0, 1); lcd.print(F("  [ OFFLINE ]   "));
  }
}

void showError(String msg) {
  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("!!! ERROR !!!   "));
  lcd.setCursor(0, 1); lcd.print(msg.substring(0, 16));
  indicatorFail();
}

// ============================================================
//  INDIKATOR BUZZER & LED
// ============================================================

// Toggle LED dan Buzzer bersamaan
void toggleIndicator(bool state) {
  digitalWrite(BUZZER_PIN, state ? HIGH : LOW);
  digitalWrite(LED_PIN, state ? HIGH : LOW);
}

// CHECK_IN: 2 beep/blink pendek
void indicatorCheckIn() {
  for (int i = 0; i < 2; i++) {
    toggleIndicator(true); delay(100);
    toggleIndicator(false); 
    if (i < 1) delay(100);
  }
}

// CHECK_OUT: 3 beep/blink cepat
void indicatorCheckOut() {
  for (int i = 0; i < 3; i++) {
    toggleIndicator(true); delay(80);
    toggleIndicator(false); 
    if (i < 2) delay(80);
  }
}

// COOLDOWN: 1 beep/blink panjang
void indicatorCooldown() {
  toggleIndicator(true); delay(300);
  toggleIndicator(false);
}

// NEW_CARD: 4 beep/blink sangat cepat
void indicatorNewCard() {
  for (int i = 0; i < 4; i++) {
    toggleIndicator(true); delay(60);
    toggleIndicator(false); 
    if (i < 3) delay(60);
  }
}

// ERROR/FAIL: 1 beep/blink sangat panjang
void indicatorFail() {
  toggleIndicator(true); delay(600);
  toggleIndicator(false);
}

// Beep generik (untuk boot, koneksi WiFi, dll.)
void beep(int count, int duration) {
  for (int i = 0; i < count; i++) {
    toggleIndicator(true); delay(duration);
    toggleIndicator(false);
    if (i < count - 1) delay(100);
  }
}

// ============================================================
//  FUNGSI MP3 PLAYER RAW COMMANDS
// ============================================================

void sendMp3Command(uint8_t cmd, uint8_t para1, uint8_t para2) {
  uint8_t cmdBuffer[8] = { 0x7E, 0xFF, 0x06, cmd, 0x00, para1, para2, 0xEF };
  mp3Serial.write(cmdBuffer, 8);
  delay(100); 
}

void playAudio(uint8_t folder, uint8_t track) {
  sendMp3Command(0x0F, folder, track);
}

void setMp3Volume(uint8_t vol) {
  if (vol > 30) vol = 30;
  sendMp3Command(0x06, 0x00, vol);
}

// ============================================================
//  BACA UID RFID (FORMAT HEXADECIMAL)
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
