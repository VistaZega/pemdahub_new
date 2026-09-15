// ============================================================
//  FIRMWARE ESP32 FULL - PEMBDAHUB ATTENDANCE STATION (20x4)
//  ESP32 + RC522 (RFID) + GM65 (QR) + LCD 20x4 + DFPlayer + Buzzer + LED
//
//  Fitur Lengkap & UI Selaras dengan Station NodeMCU 20x4:
//  1. Smart Screensaver Running Text Slogan Resmi Yayasan Perguruan Pembda Nias
//  2. UI Animasi Siaga (Standby Wave Animation & Blinking Heartbeat)
//  3. Custom Pixel Characters LCD (Ikon WiFi, Ikon Kartu, Ikon Heartbeat)
//  4. Multi-AP WiFi Failover (4 Profil Jaringan Otomatis)
//  5. DFPlayer Mini Audio (10-byte packet dengan standard checksum)
//  6. Hardware UART Full-Duplex: GM65 RX pada Pin RX2 (GPIO 16), DFPlayer TX pada Pin TX2 (GPIO 17)
//
//  WIRING DIAGRAM ESP32 DevKit V1:
//  ┌─────────────┬──────────┬──────────────────────────────────────────┐
//  │ Komponen    │ GPIO     │ Catatan                                  │
//  ├─────────────┼──────────┼──────────────────────────────────────────┤
//  │ RFID SDA/SS │ GPIO 5   │ SPI CS (VSPI)                            │
//  │ RFID SCK    │ GPIO 18  │ SPI CLK (VSPI default)                   │
//  │ RFID MOSI   │ GPIO 23  │ SPI MOSI (VSPI default)                  │
//  │ RFID MISO   │ GPIO 19  │ SPI MISO (VSPI default)                  │
//  │ RFID RST    │ GPIO 4   │ RFID Reset                               │
//  │ LCD SDA     │ GPIO 21  │ I2C SDA default ESP32                    │
//  │ LCD SCL     │ GPIO 22  │ I2C SCL default ESP32                    │
//  │ QR RX       │ GPIO 16  │ Serial1 RX ← TX GM65 Scanner (Pin RX2)   │
//  │ MP3 TX      │ GPIO 17  │ Serial2 TX → RX DFPlayer via 1KΩ (TX2)   │
//  │ MP3 RX      │ GPIO 25  │ Serial2 RX (Pin bebas / dummy)           │
//  │ Buzzer (+)  │ GPIO 2   │ Buzzer aktif HIGH                        │
//  │ LED Green   │ GPIO 13  │ LED Hijau (Berhasil)                     │
//  │ LED Red     │ GPIO 15  │ LED Merah (Gagal/Peringatan)             │
//  │ RFID 3.3V   │ 3V3      │ JANGAN pakai 5V untuk RC522!             │
//  │ LCD VCC     │ 5V       │ LCD 20x4 butuh 5V                        │
//  │ MP3 VCC     │ 5V       │ DFPlayer Mini butuh 5V                   │
//  │ GND         │ GND      │ Common ground semua modul                │
//  └─────────────┴──────────┴──────────────────────────────────────────┘
// ============================================================

#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <WiFi.h>
#include <WiFiClient.h>
#include <WiFiMulti.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <WiFiClientSecure.h>
#include <HardwareSerial.h>

// ============================================================
// ============================================================
//  KONFIGURASI JARINGAN & SERVER PEMBDAHUB
// ============================================================

// 1. Jaringan Utama (Satu-satunya LAN Lokal Sekolah -> Server Ubuntu)
const char* WIFI_LOCAL_SSID     = "PembdaLINK";
const char* WIFI_LOCAL_PASS     = "PEMBDA2026";
const char* SERVER_LOCAL_URL    = "http://50.35.89.10/api/attendance/rfid-scan";
const char* SCAN_BUFFER_LOCAL   = "http://50.35.89.10/api/rfid/scan-buffer";

// 2. Jaringan Alternatif (Internet Failover -> Server Production Cloud)
// Digunakan secara otomatis apabila LAN PembdaLINK tidak ditemukan / padam
const char* WIFI_ALT_SSID       = "TEFA";
const char* WIFI_ALT_PASSWORD   = "PEMBDA2026";

const char* WIFI_ALT1_SSID      = "PembdaNET";
const char* WIFI_ALT1_PASSWORD  = "pelita31";

const char* WIFI_ALT2_SSID      = "Xspace";
const char* WIFI_ALT2_PASSWORD  = "12345678starlink";

const char* SERVER_CLOUD_URL    = "https://perguruanpembda.com/api/attendance/rfid-scan";
const char* SCAN_BUFFER_CLOUD   = "https://perguruanpembda.com/api/rfid/scan-buffer";

// API Kiosk Key & Device ID
const char* KIOSK_API_KEY       = "RAHASIA-PEMBDAHUB-12345";

// ── DEVICE ID ──
const char* DEVICE_ID           = "STATION-SMP-02";

// ============================================================
//  PIN DEFINITIONS - ESP32 Dev Module
// ============================================================

// SPI Pins untuk RFID RC522 (VSPI default ESP32)
#define RFID_SS_PIN    5    // GPIO 5  - SPI CS
#define RFID_RST_PIN   4    // GPIO 4  - RFID Hardware Reset

// I2C LCD 20x4
#define LCD_ADDRESS    0x27
#define LCD_COLS       20
#define LCD_ROWS       4

// Serial Hardware:
// GM65 QR Scanner TX -> Pin RX2 (GPIO 16)
// DFPlayer Mini RX   <- Pin TX2 (GPIO 17) via R 1kΩ
#define QR_RX_PIN      16   // GPIO 16 (Pin RX2) - Jalur Data GM65 Scanner
#define QR_TX_PIN      -1   // Tidak dipakai (hanya menerima data)
#define MP3_TX_PIN     17   // GPIO 17 (Pin TX2) - Jalur Perintah DFPlayer Mini RX
#define MP3_RX_PIN     25   // GPIO 25 (Pin Bebas) - Dummy RX Serial2
#define MP3_VOLUME     30   // Tingkat volume MP3 Maksimal (0 s.d 30)

// Indikator
#define BUZZER_PIN     2    // GPIO 2  - Buzzer
#define LED_GREEN      13   // GPIO 13 - LED Hijau (Berhasil)
#define LED_RED        15   // GPIO 15 - LED Merah (Gagal/Peringatan)

// ============================================================
//  TIMEOUTS & COOLDOWNS
// ============================================================
#define HTTP_TIMEOUT        8000   // 8 detik timeout HTTP
#define DISPLAY_RESULT_MS   3500   // Durasi tampil hasil di LCD
#define SCAN_COOLDOWN_MS    3000   // Anti double-tap (3 detik)
#define IDLE_TIMEOUT_MS     20000  // 20 detik tanpa scan -> Aktifkan Screensaver
#define QR_MIN_LENGTH       3      // Minimum panjang QR valid

// ============================================================
//  CUSTOM PIXEL CHARACTERS UNTUK LCD (5x8 Dots)
// ============================================================
byte iconWifi[8] = {
  B00000,
  B01110,
  B10001,
  B00100,
  B01010,
  B00000,
  B00100,
  B00000
};

byte iconCard[8] = {
  B11111,
  B10001,
  B10101,
  B10001,
  B11111,
  B00000,
  B00000,
  B00000
};

byte iconHeart[8] = {
  B00000,
  B01010,
  B11111,
  B11111,
  B01110,
  B00100,
  B00000,
  B00000
};

// ============================================================
//  GLOBAL STATE
// ============================================================
String        lastUID           = "";
unsigned long lastTapTime       = 0;
String        qrBuffer          = "";
unsigned long lastWiFiCheck     = 0;
bool          isOnline          = false;

// Manajemen Failover Jaringan (LAN vs Internet Cloud)
unsigned long lastLanProbeTime  = 0;
bool          isScanningLan     = false;

// Animasi & Smart Screensaver State
unsigned long lastActivityTime  = 0;
unsigned long lastAnimTime      = 0;
int           animFrame         = 0;
bool          isShowingResult   = false;
bool          isScreensaver     = false;

// Teks Berjalan Slogan Resmi Yayasan Perguruan Pembda Nias
const String marqueeText = "   *** YAYASAN PERGURUAN PEMBDA NIAS *** Keep Moving Forward - Maju Terus Pantang Mundur! *** SMPS Pembda 2 - SMAS Pembda 1 - SMK PEMBDA Nias *** Silakan Tempel Kartu RFID / Scan QR Code ***   ";
int          marqueePos        = 0;
unsigned long lastMarqueeTime  = 0;

// ============================================================
//  OBJEK HARDWARE
// ============================================================
MFRC522           rfid(RFID_SS_PIN, RFID_RST_PIN);
LiquidCrystal_I2C lcd(LCD_ADDRESS, LCD_COLS, LCD_ROWS);
WiFiMulti         wifiMulti;
HardwareSerial    qrSerial(1);   // UART1 ESP32 untuk QR Scanner
HardwareSerial    mp3Serial(2);  // UART2 ESP32 untuk MP3 Player

// Prototipe Fungsi
void playAudio(uint8_t folder, uint8_t track);
void setMp3Volume(uint8_t vol);
void sendMp3Command(uint8_t cmd, uint8_t para1, uint8_t para2);
void showReady();
void showScreensaverBase();
void updateLcdAnimation();
void updateMarquee();
void showError(String msg);
void beep(int count, int duration);
void handleRfidScan();
void handleQrScan();
void sendToServer(String uid, String type);
void sendScanBuffer(String uid);
void parseAndDisplay(String json, String uid);
void connectWiFi();
bool isLanMode();
String getActiveServerUrl();
String getActiveScanBufferUrl();
void checkLanAvailability(unsigned long now);
void indicatorCheckIn();
void indicatorCheckOut();
void indicatorCooldown();
void indicatorNewCard();
void indicatorFail();
String getRfidUID();

// ============================================================
//  SETUP
// ============================================================
void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println(F("\n=== ESP32 FULL STATION BOOTING ==="));
  Serial.println(F("Board: ESP32 Dev Module (Full Version)"));
  Serial.println(F("Fitur: RFID + QR + LCD 20x4 + MP3 + Buzzer + Dual LED"));
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

  // Daftarkan Custom Characters ke memori LCD
  lcd.createChar(0, iconWifi);
  lcd.createChar(1, iconCard);
  lcd.createChar(2, iconHeart);

  lcd.setCursor(0, 0); lcd.print(F("===================="));
  lcd.setCursor(0, 1); lcd.print(F(" * PEMBDA HUB v2 *  "));
  lcd.setCursor(0, 2); lcd.print(F(" Memulai Sistem...  "));
  lcd.setCursor(0, 3); lcd.print(F("===================="));
  Serial.println(F("LCD 20x4 OK."));
  delay(1500);

  // ── INISIALISASI SPI & RFID RC522 DENGAN HARDWARE RESET PULSE ──
  pinMode(RFID_RST_PIN, OUTPUT);
  digitalWrite(RFID_RST_PIN, LOW);
  delay(50);
  digitalWrite(RFID_RST_PIN, HIGH);
  delay(100);

  SPI.begin();
  rfid.PCD_Init();
  delay(100);

  Serial.println(F("Mengecek modul RFID RC522..."));
  rfid.PCD_DumpVersionToSerial();

  byte version = rfid.PCD_ReadRegister(rfid.VersionReg);
  if (version == 0x00 || version == 0xFF) {
    Serial.println(F("WARNING: RFID tidak terdeteksi! Cek wiring SPI."));
    Serial.println(F("  SS=GPIO5, SCK=GPIO18, MOSI=GPIO23, MISO=GPIO19, RST=GPIO4"));
    lcd.setCursor(0, 2); lcd.print(F("RFID ERROR! Cek SPI "));
    beep(5, 100);
    delay(2000);
  } else {
    rfid.PCD_SetAntennaGain(rfid.RxGain_max);
    delay(10);
    rfid.PCD_AntennaOn();
    Serial.println(F("RFID Antenna Gain: MAX 48dB"));
  }

  // ── KONEKSI WIFI MULTI-AP (PROFIL ALTERNATIF INTERNET) ──
  wifiMulti.addAP(WIFI_ALT_SSID,  WIFI_ALT_PASSWORD);
  wifiMulti.addAP(WIFI_ALT1_SSID, WIFI_ALT1_PASSWORD);
  wifiMulti.addAP(WIFI_ALT2_SSID, WIFI_ALT2_PASSWORD);
  connectWiFi();
  isOnline = (WiFi.status() == WL_CONNECTED);

  lastActivityTime = millis();
  showReady();

  // ── INISIALISASI QR SCANNER (Serial1) ──
  qrSerial.begin(9600, SERIAL_8N1, QR_RX_PIN, QR_TX_PIN);
  delay(100);
  while (qrSerial.available()) qrSerial.read();
  qrBuffer = "";
  Serial.print(F("QR Scanner (Serial1 RX=GPIO")); Serial.print(QR_RX_PIN); Serial.println(F(") OK."));

  // ── INISIALISASI MP3 PLAYER (Serial2) ──
  mp3Serial.begin(9600, SERIAL_8N1, MP3_RX_PIN, MP3_TX_PIN);
  delay(1500);  // DFPlayer butuh ~1.5 detik untuk inisialisasi chip & MicroSD
  while (mp3Serial.available()) mp3Serial.read();

  // Set volume MP3 ke level maksimal (30)
  setMp3Volume(MP3_VOLUME);
  delay(200);

  // Putar lagu pembuka 001.mp3 ("Selamat Pagi Silahkan Absen")
  playAudio(1, 1);
  Serial.println(F("MP3 Player (Serial2 TX=GPIO17) OK. Volume: 30/30"));

  Serial.print(F("Free Heap setelah init: ")); Serial.println(ESP.getFreeHeap());
  Serial.println(F("System Ready. Silakan scan kartu RFID atau QR Code."));
}

// ============================================================
//  LOOP UTAMA
// ============================================================
void loop() {
  unsigned long now = millis();

  // 1. Periksa status WiFi & Smart Failover secara non-blocking
  if (WiFi.status() != WL_CONNECTED) {
    if (now - lastWiFiCheck >= 5000 || lastWiFiCheck == 0) {
      lastWiFiCheck = now;
      if (isOnline) {
        isOnline = false;
        Serial.println(F("[NET] WiFi Terputus! Memulai auto-reconnect..."));
        if (isScreensaver) showScreensaverBase();
        else showReady();
      }
      // Prioritaskan koneksi kembali ke LAN Utama (PembdaLINK)
      WiFi.begin(WIFI_LOCAL_SSID, WIFI_LOCAL_PASS);
      delay(200);
      if (WiFi.status() != WL_CONNECTED) {
        // Jika LAN belum aktif, cari jaringan alternatif internet via wifiMulti
        wifiMulti.run();
      }
      if (WiFi.status() == WL_CONNECTED) {
        isOnline = true;
        Serial.println("[NET] WiFi Terhubung ke: " + WiFi.SSID() + (isLanMode() ? " [LAN Ubuntu Lokal]" : " [Internet Cloud]"));
        if (isScreensaver) showScreensaverBase();
        else showReady();
      }
    }
  } else {
    // WiFi status saat ini terhubung (WL_CONNECTED)
    if (!isOnline) {
      isOnline = true;
      Serial.println("[NET] Status Pulih Online: " + WiFi.SSID() + (isLanMode() ? " [LAN Ubuntu]" : " [Internet Cloud]"));
      if (isScreensaver) showScreensaverBase();
      else showReady();
    }

    // Jika sedang terhubung ke Internet Alternatif (Bukan LAN PembdaLINK):
    // Cek berkala di background apakah LAN PembdaLINK sudah aktif kembali
    checkLanAvailability(now);
  }

  // 2. Transisi Otomatis ke Screensaver / Running Text jika tidak ada scan selama 20 detik
  if (!isShowingResult) {
    if (!isScreensaver && (now - lastActivityTime >= IDLE_TIMEOUT_MS)) {
      isScreensaver = true;
      marqueePos = 0;
      showScreensaverBase();
    }
    
    // Update Tampilan Animasi
    if (isScreensaver) {
      updateMarquee();
    } else {
      updateLcdAnimation();
    }
  }

  // 3. Cek Scanner RFID
  handleRfidScan();

  // 4. Cek Scanner Barcode / QR
  handleQrScan();

  delay(10);
}

// ============================================================
//  HANDLER RFID SCAN
// ============================================================
void handleRfidScan() {
  if (!rfid.PICC_IsNewCardPresent()) return;
  if (!rfid.PICC_ReadCardSerial()) {
    delay(10);
    if (!rfid.PICC_ReadCardSerial()) return;
  }

  String uid = getRfidUID();
  unsigned long now = millis();
  if (uid == lastUID && (now - lastTapTime) < SCAN_COOLDOWN_MS) {
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
    return;
  }
  lastUID          = uid;
  lastTapTime      = now;
  lastActivityTime = now;
  isScreensaver    = false;

  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();

  isShowingResult = true; // Kunci layar agar animasi/running text tidak menimpa
  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("=== MEMPROSES ======"));
  lcd.setCursor(0, 1); lcd.print(F("Membaca kartu RFID  "));
  lcd.setCursor(0, 2); lcd.print("UID: " + uid);
  lcd.setCursor(0, 3); lcd.print(F("Mohon tunggu...     "));
  beep(1, 100);

  sendToServer(uid, "rfid");
  lastUID = "";
}

// ============================================================
//  HANDLER QR CODE (via Hardware Serial1)
// ============================================================
void handleQrScan() {
  int maxRead = 10;

  for (int i = 0; i < maxRead && qrSerial.available() > 0; i++) {
    char c = qrSerial.read();

    if (c == '\r' || c == '\n') {
      if (qrBuffer.length() >= QR_MIN_LENGTH) {
        String qrData = qrBuffer;
        qrData.trim();
        qrBuffer = "";

        unsigned long now = millis();
        if (qrData == lastUID && (now - lastTapTime) < SCAN_COOLDOWN_MS) {
          return;
        }
        lastUID          = qrData;
        lastTapTime      = now;
        lastActivityTime = now;
        isScreensaver    = false;

        isShowingResult = true; // Kunci layar
        lcd.clear();
        lcd.setCursor(0, 0); lcd.print(F("=== MEMPROSES ======"));
        lcd.setCursor(0, 1); lcd.print(F("Kode QR Terbaca     "));
        lcd.setCursor(0, 2); lcd.print("ID: " + qrData.substring(0, min((int)qrData.length(), 16)));
        lcd.setCursor(0, 3); lcd.print(F("Menghubungi server.."));
        beep(1, 150);

        sendToServer(qrData, "qr");
        lastUID = "";
        return;
      }
      qrBuffer = "";
    }
    else if (c >= 32 && c <= 126) {
      if (qrBuffer.length() < 128) {
        qrBuffer += c;
      } else {
        qrBuffer = "";
      }
    }
  }
}

// ============================================================
//  SMART ROUTING & HELPER JARINGAN
// ============================================================
bool isLanMode() {
  return (WiFi.status() == WL_CONNECTED && WiFi.SSID() == WIFI_LOCAL_SSID);
}

String getActiveServerUrl() {
  return isLanMode() ? String(SERVER_LOCAL_URL) : String(SERVER_CLOUD_URL);
}

String getActiveScanBufferUrl() {
  return isLanMode() ? String(SCAN_BUFFER_LOCAL) : String(SCAN_BUFFER_CLOUD);
}

// ============================================================
//  PROBE BACKGROUND KETERSEDIAAN LAN PEMBDA-LINK
// ============================================================
void checkLanAvailability(unsigned long now) {
  // Hanya jalankan jika sedang tidak di LAN, tidak sedang memproses hasil scan, dan buffer serial kosong
  if (WiFi.SSID() == WIFI_LOCAL_SSID || isShowingResult || qrSerial.available() > 0) {
    if (isScanningLan) {
      WiFi.scanDelete();
      isScanningLan = false;
    }
    return;
  }

  // Mulai scan background setiap 45 detik
  if (!isScanningLan && (now - lastLanProbeTime >= 45000 || lastLanProbeTime == 0)) {
    lastLanProbeTime = now;
    isScanningLan = true;
    WiFi.scanNetworks(true); // Non-blocking async scan di ESP32
    Serial.println(F("[PROBE] Mengecek sinyal LAN PembdaLINK di background..."));
  }

  // Cek apakah hasil scan async sudah selesai
  if (isScanningLan) {
    int scanResult = WiFi.scanComplete();
    if (scanResult >= 0) {
      bool lanFound = false;
      for (int i = 0; i < scanResult; i++) {
        if (WiFi.SSID(i) == WIFI_LOCAL_SSID) {
          lanFound = true;
          break;
        }
      }
      WiFi.scanDelete();
      isScanningLan = false;

      if (lanFound) {
        Serial.println(F("[FAILOVER] LAN PembdaLINK terdeteksi aktif! Beralih dari Internet ke LAN Lokal..."));
        WiFi.disconnect();
        WiFi.begin(WIFI_LOCAL_SSID, WIFI_LOCAL_PASS);
        delay(200);
      }
    } else if (scanResult == -2) {
      isScanningLan = false;
    }
  }
}

// ============================================================
//  KIRIM DATA KE SERVER (SMART HYBRID: HTTP LAN / HTTPS CLOUD)
// ============================================================
void sendToServer(String uid, String type) {
  isShowingResult = true;
  String targetUrl = getActiveServerUrl();
  bool lan = isLanMode();

  Serial.println(F("================================="));
  Serial.println("Mengirim Scan ke Server : " + uid + " (" + type + ")");
  Serial.println("Jalur Jaringan          : " + String(lan ? "[LAN LOKAL UBUNTU]" : "[INTERNET CLOUD]"));
  Serial.println("URL Target              : " + targetUrl);
  Serial.println(F("================================="));

  if (WiFi.status() != WL_CONNECTED) {
    playAudio(1, 7); // 007.MP3 - Sistem ada gangguan hubungi admin
    showError("Koneksi WiFi Off");
    return;
  }

  HTTPClient http;
  WiFiClient client;
  WiFiClientSecure secureClient;

  if (targetUrl.startsWith("https://")) {
    secureClient.setInsecure(); // Bypass verifikasi sertifikat SSL jika HTTPS
    http.begin(secureClient, targetUrl);
  } else {
    http.begin(client, targetUrl); // HTTP biasa untuk IP server lokal
  }
  http.addHeader("Content-Type",    "application/json");
  http.addHeader("X-Kiosk-API-Key", KIOSK_API_KEY);
  http.addHeader("Accept",          "application/json");
  http.setTimeout(lan ? 6000 : HTTP_TIMEOUT); // LAN 6s (ultra-cepat), Cloud 10s

  StaticJsonDocument<128> doc;
  doc["uid"]       = uid;
  doc["type"]      = type;
  doc["device_id"] = DEVICE_ID;
  String body;
  serializeJson(doc, body);

  int code = http.POST(body);
  Serial.print("HTTP Response Code: "); Serial.println(code);

  if (code == 200 || code == 201) {
    String payload = http.getString();
    Serial.println("Server Response: " + payload);
    parseAndDisplay(payload, uid);
  } else if (code == 401) {
    playAudio(1, 7);
    showError("API Key Tidak Valid!");
  } else if (code == 404) {
    playAudio(1, 6);
    showError("Kartu/QR Tdk Terdaftar");
  } else if (code > 0) {
    playAudio(1, 7);
    showError("Gagal Server: " + String(code));
  } else {
    String errMsg = http.errorToString(code);
    String payload = http.getString();
    if (payload.length() > 10 && payload.indexOf("status") > 0) {
      parseAndDisplay(payload, uid);
    } else {
      playAudio(1, 7); // 007.MP3 - Sistem Ada Gangguan Hubungi Admin
      showError(lan ? "Err Server LAN" : "Err Server Cloud");
    }
  }

  http.end();
  while (qrSerial.available()) qrSerial.read();
  qrBuffer = "";

  delay(DISPLAY_RESULT_MS);
  lastActivityTime = millis();
  isScreensaver    = false;
  showReady();
}

// ============================================================
//  KIRIM UID KE SCAN BUFFER (untuk fitur registrasi RFID massal)
//  Dipanggil saat action_code == "NEW_CARD"
// ============================================================
void sendScanBuffer(String uid) {
  if (WiFi.status() != WL_CONNECTED) return;

  String bufferUrl = getActiveScanBufferUrl();
  Serial.println("Mengirim UID ke scan-buffer (" + String(isLanMode() ? "LAN" : "Cloud") + "): " + bufferUrl);

  HTTPClient http;
  WiFiClient client;
  WiFiClientSecure secureClient;

  if (bufferUrl.startsWith("https://")) {
    secureClient.setInsecure();
    http.begin(secureClient, bufferUrl);
  } else {
    http.begin(client, bufferUrl);
  }
  http.addHeader("Content-Type",    "application/json");
  http.addHeader("X-Kiosk-API-Key", KIOSK_API_KEY);
  http.setTimeout(5000);

  StaticJsonDocument<64> doc;
  doc["uid"] = uid;
  String body;
  serializeJson(doc, body);

  int code = http.POST(body);
  Serial.print("Scan-buffer Response: "); Serial.println(code);
  http.end();
}

// ============================================================
//  PARSE RESPONSE JSON & TAMPILKAN DI LCD
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
      // Bedakan audio & ucapan: Guru/Staf → 005, Siswa → 002
      if (kelasDisplay.indexOf("Guru") >= 0 || kelasDisplay.indexOf("Staf") >= 0 || kelasDisplay.indexOf("Staff") >= 0) {
        lcd.setCursor(0, 3); lcd.print(F("Selamat Bertugas!   "));
        playAudio(1, 5); // 005.MP3 - Selamat Pagi Selamat Bekerja (Guru/Staf)
      } else {
        lcd.setCursor(0, 3); lcd.print(F("Selamat Belajar!    "));
        playAudio(1, 2); // 002.MP3 - Akses diterima selamat belajar (Siswa)
      }
      indicatorCheckIn();
    }
    else if (action_code == "CHECK_OUT") {
      lcd.setCursor(0, 2); lcd.print("PULANG PADA: " + waktu);
      lcd.setCursor(0, 3); lcd.print(F("Hati-hati di jalan! "));
      playAudio(1, 3); // 003.MP3 - Absen pulang sampai jumpa
      indicatorCheckOut();
    }
    else if (action_code == "COOLDOWN") {
      lcd.setCursor(0, 2); lcd.print(F("Sudah Absen Masuk!  "));
      if (waktu != "") {
        lcd.setCursor(0, 3); lcd.print("Jam Masuk: " + waktu);
      } else {
        lcd.setCursor(0, 3); lcd.print(message.substring(0, min((int)message.length(), 20)));
      }
      playAudio(1, 4); // 004.MP3 - Absen sudah tercatat terima kasih
      indicatorCooldown();
    }
    else if (action_code == "NEW_CARD") {
      sendScanBuffer(uid);
      lcd.clear();
      lcd.setCursor(0, 0); lcd.print(F("** KARTU BARU **    "));
      lcd.setCursor(0, 1); lcd.print(kelasDisplay);
      lcd.setCursor(0, 2); lcd.print(F("Belum terdaftar!    "));
      lcd.setCursor(0, 3); lcd.print(F("Daftarkan di Admin  "));
      playAudio(1, 6); // 006.MP3 - Kartu tidak dikenali hubungi admin
      indicatorNewCard();
    }
    else {
      lcd.setCursor(0, 2); lcd.print(message.substring(0, min((int)message.length(), 20)));
      lcd.setCursor(0, 3); lcd.print("Waktu: " + waktu);
      playAudio(1, 2); // Default siswa
      indicatorCheckIn();
    }
  } else {
    String errMsg = (action_code == "ALREADY_ATTENDED") ? "Sudah Absen Lengkap" : message;
    if (action_code == "ALREADY_ATTENDED") {
      playAudio(1, 4); // 004.MP3 - Absensi hadir dan pulang sudah tercatat
    } else {
      playAudio(1, 7); // 007.MP3 - Sistem ada gangguan hubungi admin
    }
    showError(errMsg);
  }
}

// ============================================================
//  KONEKSI WIFI (SMART PRIORITY: LAN PEMBDA-LINK -> INTERNET CLOUD)
// ============================================================
void connectWiFi() {
  WiFi.mode(WIFI_STA);

  // --- TAHAP 1: Coba koneksi ke LAN Utama (PembdaLINK) ---
  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("=== CEK LAN LOKAL ==="));
  lcd.setCursor(0, 1); lcd.print("Cari: " + String(WIFI_LOCAL_SSID).substring(0, 14));
  lcd.setCursor(0, 2); lcd.print(F("Mencoba LAN...      "));
  lcd.setCursor(0, 3); lcd.print(F("Server: Ubuntu Lokal"));

  Serial.println(F("\n[NET] Tahap 1: Mencoba koneksi ke LAN Utama (PembdaLINK)..."));
  WiFi.begin(WIFI_LOCAL_SSID, WIFI_LOCAL_PASS);

  int attempt = 0;
  while (WiFi.status() != WL_CONNECTED && attempt < 14) { // Coba ~7 detik
    delay(500);
    Serial.print(F("."));
    String dots = "";
    for (int i = 0; i < (attempt % 5) + 1; i++) dots += ".";
    lcd.setCursor(0, 2); lcd.print("Menghubungkan" + dots + "    ");
    attempt++;
  }

  // Jika LAN Utama berhasil terhubung:
  if (WiFi.status() == WL_CONNECTED) {
    isOnline = true;
    Serial.println(F("\n[NET] BERHASIL terhubung ke LAN PembdaLINK!"));
    Serial.print(F("[NET] IP Address   : ")); Serial.println(WiFi.localIP());
    Serial.print(F("[NET] Target Server: ")); Serial.println(SERVER_LOCAL_URL);

    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(F("=== [LAN] CONNECT ==="));
    lcd.setCursor(0, 1); lcd.print("SSID  : " + WiFi.SSID().substring(0, 12));
    lcd.setCursor(0, 2); lcd.print(F("Server: Ubuntu Lokal"));
    lcd.setCursor(0, 3); lcd.print("IP: " + WiFi.localIP().toString());
    beep(1, 200);
    delay(1500);
    return;
  }

  // --- TAHAP 2: LAN PembdaLINK Tidak Ditemukan -> Fallback ke Internet Cloud ---
  Serial.println(F("\n[NET] LAN PembdaLINK tidak ditemukan! Mencoba WiFi Internet Alternatif..."));
  WiFi.disconnect();
  delay(100);

  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("=== LAN TDK ADA ===="));
  lcd.setCursor(0, 1); lcd.print(F("Mencari Internet... "));
  lcd.setCursor(0, 2); lcd.print(F("Alt WiFi Scanning.. "));
  lcd.setCursor(0, 3); lcd.print(F("Target: CLOUD PROD  "));
  delay(1000);

  attempt = 0;
  while (wifiMulti.run() != WL_CONNECTED && attempt < 16) { // Coba ~8 detik
    delay(500);
    Serial.print(F("*"));
    String dots = "";
    for (int i = 0; i < (attempt % 5) + 1; i++) dots += "*";
    lcd.setCursor(0, 2); lcd.print("Hubungkan Alt" + dots + "    ");
    attempt++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    isOnline = true;
    Serial.println(F("\n[NET] BERHASIL terhubung ke Internet Alternatif!"));
    Serial.print(F("[NET] SSID         : ")); Serial.println(WiFi.SSID());
    Serial.print(F("[NET] Target Server: ")); Serial.println(SERVER_CLOUD_URL);

    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(F("=== [NET] CONNECT ==="));
    lcd.setCursor(0, 1); lcd.print("SSID  : " + WiFi.SSID().substring(0, 12));
    lcd.setCursor(0, 2); lcd.print(F("Server: CLOUD PROD  "));
    lcd.setCursor(0, 3); lcd.print("IP: " + WiFi.localIP().toString());
    beep(2, 100);
    delay(1500);
  } else {
    isOnline = false;
    Serial.println(F("\n[NET] Semua jaringan WiFi gagal terhubung!"));

    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(F("=== SEMUA OFFLINE =="));
    lcd.setCursor(0, 1); lcd.print(F("LAN & Net Tdk Ada!  "));
    lcd.setCursor(0, 2); lcd.print(F("Auto-retry di bg... "));
    lcd.setCursor(0, 3); lcd.print(F("--------------------"));
    beep(3, 100);
    delay(2000);
  }
}

// ============================================================
//  FUNGSI DISPLAY STANDBY (MODE SIAGA AKTIF - 20 DETIK AWAL)
// ============================================================
void showReady() {
  isShowingResult = false;
  lcd.clear();
  
  // Baris 0: Header Elegan
  lcd.setCursor(0, 0);
  lcd.print(F("*PEMBDAHUB PRESENSI*"));
  
  // Baris 1: Petunjuk Scan
  lcd.setCursor(0, 1);
  lcd.write(byte(1)); // Ikon Kartu
  lcd.print(F(" Tempel Kartu / QR "));
  
  // Baris 2: Animasi / Petunjuk
  lcd.setCursor(0, 2);
  lcd.print(F("   >> RFID / QR <<  "));

  // Baris 3: Status Bar & Jalur Jaringan (LAN vs CLOUD)
  lcd.setCursor(0, 3);
  if (isOnline) {
    lcd.write(byte(0)); // Ikon WiFi
    if (isLanMode()) {
      lcd.print(F(" LAN:"));
      String ssid = WiFi.SSID();
      if (ssid.length() > 9) ssid = ssid.substring(0, 9);
      lcd.print(ssid);
      lcd.setCursor(15, 3);
      lcd.print(F("[LAN]"));
    } else {
      lcd.print(F(" NET:"));
      String ssid = WiFi.SSID();
      if (ssid.length() > 9) ssid = ssid.substring(0, 9);
      lcd.print(ssid);
      lcd.setCursor(15, 3);
      lcd.print(F("[NET]"));
    }
  } else {
    lcd.print(F("[OFFLINE] Cari AP..."));
  }
}

// Update Animasi Gelombang Siaga (Dipanggil setiap 350ms)
void updateLcdAnimation() {
  if (isShowingResult || isScreensaver) return;

  unsigned long now = millis();
  if (now - lastAnimTime < 350) return;
  lastAnimTime = now;

  const char* frames[] = {
    "   >> RFID / QR <<  ",
    "  >>> RFID / QR <<< ",
    " >>>> RFID / QR <<<<",
    "  >>> RFID / QR <<< ",
    "   >> RFID / QR <<  ",
    "    > RFID / QR <   "
  };

  lcd.setCursor(0, 2);
  lcd.print(frames[animFrame]);
  animFrame = (animFrame + 1) % 6;

  // Heartbeat indicator di pojok kanan bawah
  lcd.setCursor(19, 3);
  if (animFrame % 2 == 0) lcd.write(byte(2));
  else lcd.print(F(" "));
}

// ============================================================
//  FUNGSI SMART SCREENSAVER (RUNNING TEXT SLOGAN YAYASAN)
// ============================================================
void showScreensaverBase() {
  isShowingResult = false;
  lcd.clear();
  
  // Baris 0: Header Yayasan
  lcd.setCursor(0, 0);
  lcd.print(F("* PERGURUAN PEMBDA *"));
  
  // Baris 1: Subtitle Lembaga
  lcd.setCursor(0, 1);
  lcd.print(F("STATION ABSENSI     "));
  
  // Baris 3: Status Bar Siaga Scan & Mode Jaringan
  lcd.setCursor(0, 3);
  if (isOnline) {
    lcd.write(byte(0));
    lcd.print(isLanMode() ? F(" LAN:") : F(" NET:"));
    String ssid = WiFi.SSID();
    if (ssid.length() > 8) ssid = ssid.substring(0, 8);
    lcd.print(ssid);
    lcd.setCursor(14, 3);
    lcd.print(isLanMode() ? F("LOKAL") : F("CLOUD"));
  } else {
    lcd.print(F("[OFFLINE]   SCAN    "));
  }
}

// Update Teks Berjalan Halus (Dipanggil setiap 220ms)
void updateMarquee() {
  if (isShowingResult || !isScreensaver) return;

  unsigned long now = millis();
  if (now - lastMarqueeTime < 220) return; // Kecepatan scroll teks 220ms per karakter
  lastMarqueeTime = now;

  String windowText = "";
  int textLen = marqueeText.length();
  for (int i = 0; i < 20; i++) {
    int idx = (marqueePos + i) % textLen;
    windowText += marqueeText[idx];
  }

  lcd.setCursor(0, 2);
  lcd.print(windowText);

  marqueePos = (marqueePos + 1) % textLen;

  // Heartbeat blink di pojok kanan bawah
  lcd.setCursor(19, 3);
  if ((marqueePos / 2) % 2 == 0) lcd.write(byte(2));
  else lcd.print(F(" "));
}

void showError(String msg) {
  isShowingResult = true;
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
//  INDIKATOR (Buzzer + Dual LED)
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

// Beep generik
void beep(int count, int duration) {
  for (int i = 0; i < count; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(duration);
    digitalWrite(BUZZER_PIN, LOW);
    if (i < count - 1) delay(100);
  }
}

// ============================================================
//  FUNGSI MP3 PLAYER RAW COMMANDS (Mode Universal 8-Byte)
// ============================================================

void sendMp3Command(uint8_t cmd, uint8_t para1, uint8_t para2) {
  uint8_t cmdBuffer[8] = { 0x7E, 0xFF, 0x06, cmd, 0x00, para1, para2, 0xEF };
  mp3Serial.write(cmdBuffer, 8);
  delay(100);
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
//  BACA UID RFID (format HEX uppercase 8 karakter reversed)
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
