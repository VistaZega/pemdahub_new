// ============================================================
//  FIRMWARE NODEMCU V3 (ESP-12F) - PEMBDAHUB ATTENDANCE STATION
//  Versi: RFID RC522 + GM65 QR Scanner + LCD 20x4 I2C + Buzzer
//  (TANPA MP3 PLAYER - PIN D4 SEBAGAI HARDWARE RESET RFID RC522)
//  
//  WIRING DIAGRAM NODEMCU V3:
//  ┌─────────────┬──────────┬───────────────────────────────────────┐
//  │ Komponen    │ Pin MCU  │ Catatan                               │
//  ├─────────────┼──────────┼───────────────────────────────────────┤
//  │ RFID SDA/SS │ D0 (16)  │ SPI CS                                │
//  │ RFID SCK    │ D5 (14)  │ SPI CLK default                       │
//  │ RFID MOSI   │ D7 (13)  │ SPI MOSI default                      │
//  │ RFID MISO   │ D6 (12)  │ SPI MISO default                      │
//  │ RFID RST    │ D4 (2)   │ HARDWARE RESET PIN RFID RC522         │
//  │ LCD SDA     │ D2 (4)   │ I2C SDA default                       │
//  │ LCD SCL     │ D1 (5)   │ I2C SCL default                       │
//  │ QR RX       │ D3 (0)   │ SoftwareSerial RX (GM65 TX -> D3)     │
//  │ Buzzer (+)  │ D8 (15)  │ Pull-down = buzzer OFF saat boot      │
//  │ LCD VCC     │ VU (5V)  │ Dihubungkan ke pin VU (5V)            │
//  │ RFID 3.3V   │ 3V3      │ JANGAN pakai 5V untuk RC522!          │
//  │ GND         │ GND      │ Common ground semua komponen          │
//  └─────────────┴──────────┴───────────────────────────────────────┘
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
//  KONFIGURASI JARINGAN & SERVER PEMBDAHUB
// ============================================================

// 1. Jaringan Utama (Satu-satunya LAN Lokal Sekolah -> Server Ubuntu)
const char* WIFI_LOCAL_SSID     = "PembdaLINK";
const char* WIFI_LOCAL_PASS     = "PEMBDA2026";
const char* SERVER_LOCAL_URL    = "http://50.35.89.10/api/attendance/rfid-scan";
const char* SCAN_BUFFER_LOCAL   = "http://50.35.89.10/api/rfid/scan-buffer";

// 2. Jaringan Alternatif (Internet Failover -> Server Production Cloud)
// Digunakan secara otomatis apabila LAN PembdaLINK tidak ditemukan / padam
const char* WIFI_ALT_SSID       = "Black_Hole";
const char* WIFI_ALT_PASSWORD   = "pelita31";

const char* WIFI_ALT1_SSID      = "PembdaNET";
const char* WIFI_ALT1_PASSWORD  = "pelita31";

const char* SERVER_CLOUD_URL    = "https://perguruanpembda.com/api/attendance/rfid-scan";
const char* SCAN_BUFFER_CLOUD   = "https://perguruanpembda.com/api/rfid/scan-buffer";

// API Kiosk Key & Device ID
const char* KIOSK_API_KEY       = "RAHASIA-PEMBDAHUB-12345";

// ── GANTI DEVICE_ID UNTUK SETIAP STATION! ──
const char* DEVICE_ID           = "STATION-SMA-03";

// ============================================================
//  PIN DEFINITIONS - NodeMCU V3 (ESP-12F)
// ============================================================

// SPI Pins untuk RFID RC522 (menggunakan HSPI default ESP8266)
#define RFID_SS_PIN    16   // D0 (GPIO16) - SPI CS
#define RFID_RST_PIN    2   // D4 (GPIO2)  - Hardware Reset RFID RC522

// Buzzer
#define BUZZER_PIN     15   // D8 (GPIO15) - pull-down bawaan = buzzer OFF saat boot

// QR Scanner GM65/GM50 via SoftwareSerial (RX only)
#define QR_RX_PIN       0   // D3 (GPIO0) - TX GM65 terhubung ke D3

// I2C LCD 20x4
#define LCD_ADDRESS    0x27
#define LCD_COLS       20
#define LCD_ROWS       4

// ============================================================
//  TIMEOUTS & COOLDOWNS
// ============================================================
#define HTTP_TIMEOUT        10000   // 10 detik timeout HTTPS
#define DISPLAY_RESULT_MS   3500    // Durasi tampil hasil di LCD
#define SCAN_COOLDOWN_MS    3000    // Anti double-tap (3 detik)
#define IDLE_TIMEOUT_MS     20000   // 20 detik tanpa scan -> Aktifkan Screensaver
#define QR_MIN_LENGTH       3       // Minimum panjang QR valid

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
const String marqueeText = "   *** YAYASAN PERGURUAN PEMBDA NIAS *** Keep Moving Forward - Maju Terus Pantang Mundur! *** SMP - SMA - SMK Swasta Pembda *** Silakan Tempel Kartu RFID / Scan QR Code ***   ";
int           marqueePos        = 0;
unsigned long lastMarqueeTime   = 0;

// ============================================================
//  OBJEK HARDWARE
// ============================================================
MFRC522           rfid(RFID_SS_PIN, RFID_RST_PIN);
LiquidCrystal_I2C lcd(LCD_ADDRESS, LCD_COLS, LCD_ROWS);
ESP8266WiFiMulti  wifiMulti;
SoftwareSerial    qrSerial(QR_RX_PIN, -1); // RX D3, TX tidak dipakai (-1)

// Prototipe Fungsi
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
  Serial.println(F("\n=== NODEMCU STATION BOOTING (NO MP3) ==="));
  Serial.println(F("Board: NodeMCU V3 (ESP-12F)"));
  Serial.println(F("Konfigurasi: RFID RST on D4 (GPIO2) - QR Scanner on D3"));
  Serial.print(F("Device ID: ")); Serial.println(DEVICE_ID);
  Serial.print(F("Free Heap: ")); Serial.println(ESP.getFreeHeap());

  // Inisialisasi Buzzer
  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);

  // Inisialisasi I2C LCD (SDA=GPIO4/D2, SCL=GPIO5/D1)
  Wire.begin(4, 5);
  Wire.setClock(400000); // 400kHz Fast I2C agar transfer data LCD super cepat tanpa jeda
#ifdef FDB_LIQUID_CRYSTAL_I2C_H
  lcd.begin();
#else
  lcd.init();
#endif
  lcd.backlight();

  // Daftarkan Karakter Khusus Pixel
  lcd.createChar(0, iconWifi);
  lcd.createChar(1, iconCard);
  lcd.createChar(2, iconHeart);

  // Layar Booting Modern
  lcd.setCursor(0, 0); lcd.print(F("===================="));
  lcd.setCursor(0, 1); lcd.print(F(" * PEMBDA HUB v2 *  "));
  lcd.setCursor(0, 2); lcd.print(F(" Memulai Sistem...  "));
  lcd.setCursor(0, 3); lcd.print(F("===================="));
  Serial.println(F("LCD OK."));
  delay(1200);

  // ── HARDWARE RESET RFID RC522 VIA PIN D4 (GPIO2) ──
  pinMode(RFID_RST_PIN, OUTPUT);
  digitalWrite(RFID_RST_PIN, LOW);
  delay(50);
  digitalWrite(RFID_RST_PIN, HIGH);
  delay(50);

  // ── INISIALISASI SPI & RFID RC522 ──
  SPI.begin();
  SPI.setFrequency(1000000); // 1MHz timing stabil
  delay(50);

  rfid.PCD_Init();
  delay(50);

  byte version = rfid.PCD_ReadRegister(rfid.VersionReg);
  Serial.print(F("RFID Firmware Version: 0x"));
  Serial.println(version, HEX);

  if (version == 0x00 || version == 0xFF) {
    Serial.println(F("WARNING: RFID tidak terdeteksi! Cek wiring SPI & pin RST D4."));
    Serial.println(F("  SS  = D0 (GPIO16), SCK = D5, MOSI = D7, MISO = D6, RST = D4 (GPIO2)"));
    lcd.setCursor(0, 2); lcd.print(F("RFID ERROR! Cek D4  "));
    beep(5, 100);
    delay(2000);
  } else {
    Serial.print(F("RFID Detected: 0x"));
    Serial.print(version, HEX);
    if (version == 0x91 || version == 0x92) Serial.println(F(" (MFRC522 original)"));
    else if (version == 0x88) Serial.println(F(" (FM17522 clone)"));
    else if (version == 0xB2) Serial.println(F(" (MFRC522 clone - OK)"));
    else Serial.println(F(" (compatible)"));

    // Gain antenna MAX 48dB
    rfid.PCD_SetAntennaGain(rfid.RxGain_max);
    delay(10);
    rfid.PCD_AntennaOn();
  }

  // Daftarkan profil WiFi alternatif internet ke wifiMulti
  wifiMulti.addAP(WIFI_ALT_SSID,  WIFI_ALT_PASSWORD);
  wifiMulti.addAP(WIFI_ALT1_SSID, WIFI_ALT1_PASSWORD);
  connectWiFi();
  isOnline = (WiFi.status() == WL_CONNECTED);

  // Inisialisasi SoftwareSerial untuk QR Scanner GM65 (RX=D3)
  qrSerial.begin(9600);
  delay(100);
  while (qrSerial.available()) qrSerial.read();
  qrBuffer = "";

  Serial.print(F("Free Heap setelah init: ")); Serial.println(ESP.getFreeHeap());
  Serial.println(F("System Ready. Silakan scan kartu RFID atau QR Code."));

  lastActivityTime = millis();
  isScreensaver    = false;
  showReady();
}

// ============================================================
//  LOOP UTAMA
// ============================================================
void loop() {
  unsigned long now = millis();

  // 1. Cek QR Code lebih dulu (prioritas tinggi buffer SoftwareSerial)
  handleQrScan();

  // 2. Cek RFID
  handleRfidScan();

  // 3. Periksa status WiFi & Smart Failover secara non-blocking
  if (WiFi.status() != WL_CONNECTED) {
    if (now - lastWiFiCheck >= 5000 || lastWiFiCheck == 0) {
      lastWiFiCheck = now;
      if (isOnline) {
        isOnline = false;
        Serial.println(F("[NET] WiFi Terputus! Memulai auto-reconnect..."));
        if (!isShowingResult) {
          if (isScreensaver) showScreensaverBase();
          else showReady();
        }
      }
      // Coba koneksi ke Hotspot Alternatif / wifiMulti jika LAN tidak aktif
      WiFi.begin(WIFI_ALT_SSID, WIFI_ALT_PASSWORD);
      delay(300);
      if (WiFi.status() != WL_CONNECTED) {
        wifiMulti.run();
      }
      if (WiFi.status() == WL_CONNECTED) {
        isOnline = true;
        Serial.println("[NET] WiFi Terhubung ke: " + WiFi.SSID() + (isLanMode() ? " [LAN Ubuntu Lokal]" : " [Internet Cloud]"));
        if (!isShowingResult) {
          if (isScreensaver) showScreensaverBase();
          else showReady();
        }
      }
    }
  } else {
    // WiFi status saat ini terhubung (WL_CONNECTED)
    if (!isOnline) {
      isOnline = true;
      Serial.println("[NET] Status Pulih Online: " + WiFi.SSID() + (isLanMode() ? " [LAN Ubuntu]" : " [Internet Cloud]"));
      if (!isShowingResult) {
        if (isScreensaver) showScreensaverBase();
        else showReady();
      }
    }

    // Jika sedang terhubung ke Internet Alternatif (Bukan LAN PembdaLINK):
    // Cek berkala di background apakah LAN PembdaLINK sudah aktif kembali
    checkLanAvailability(now);
  }

  // 4. Jika sedang menampilkan hasil scan atau ada data masuk di serial QR, tunda update LCD
  if (isShowingResult || qrSerial.available() > 0) {
    delay(5);
    return;
  }

  // 5. Transisi ke Smart Screensaver jika idle >= 20 detik
  if (!isScreensaver && (now - lastActivityTime >= IDLE_TIMEOUT_MS)) {
    isScreensaver = true;
    showScreensaverBase();
  }

  // 6. Jalankan animasi standby atau running text marquee
  if (isScreensaver) {
    updateMarquee();
  } else {
    updateLcdAnimation();
  }

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

  Serial.println(F("Kartu RFID terdeteksi!"));
  String uid = getRfidUID();
  Serial.println("RFID UID: " + uid);

  unsigned long now = millis();
  if (uid == lastUID && (now - lastTapTime) < SCAN_COOLDOWN_MS) {
    Serial.println(F("RFID cooldown, abaikan."));
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
    return;
  }
  lastUID     = uid;
  lastTapTime = now;

  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();

  isShowingResult = true;
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
//  HANDLER QR CODE (via SoftwareSerial Pin D3)
// ============================================================
void handleQrScan() {
  int maxRead = 32;

  while (qrSerial.available() > 0 && maxRead-- > 0) {
    char c = qrSerial.read();

    if (c == '\r' || c == '\n') {
      if (qrBuffer.length() >= QR_MIN_LENGTH) {
        String qrData = qrBuffer;
        qrData.trim();
        qrBuffer = "";

        Serial.println(F("================================="));
        Serial.println("QR Code Terdeteksi: " + qrData);
        Serial.println(F("================================="));

        unsigned long now = millis();
        if (qrData == lastUID && (now - lastTapTime) < SCAN_COOLDOWN_MS) {
          Serial.println(F("QR Cooldown. Abaikan."));
          return;
        }
        lastUID     = qrData;
        lastTapTime = now;

        isShowingResult = true;
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
    WiFi.scanNetworks(true); // Non-blocking async scan
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
  String targetUrl = getActiveServerUrl();
  bool lan = isLanMode();

  Serial.println(F("================================="));
  Serial.println("Mengirim Scan ke Server : " + uid + " (" + type + ")");
  Serial.println("Jalur Jaringan          : " + String(lan ? "[LAN LOKAL UBUNTU]" : "[INTERNET CLOUD]"));
  Serial.println("URL Target              : " + targetUrl);
  Serial.println(F("================================="));
  
  if (WiFi.status() != WL_CONNECTED) {
    showError("Koneksi WiFi Off");
    return;
  }

  WiFiClient client;
  BearSSL::WiFiClientSecure secureClient;

  HTTPClient http;
  if (targetUrl.startsWith("https://")) {
    secureClient.setInsecure();
    http.begin(secureClient, targetUrl);
  } else {
    http.begin(client, targetUrl);
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
    showError("API Key Tidak Valid!");
  } else if (code == 404) {
    showError("Kartu/QR Tdk Terdaftar");
  } else if (code > 0) {
    showError("Gagal Server: " + String(code));
  } else {
    String errMsg = http.errorToString(code);
    String payload = http.getString();
    if (payload.length() > 10 && payload.indexOf("status") > 0) {
      parseAndDisplay(payload, uid);
    } else {
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
// ============================================================
void sendScanBuffer(String uid) {
  if (WiFi.status() != WL_CONNECTED) return;

  String bufferUrl = getActiveScanBufferUrl();
  Serial.println("Mengirim UID ke scan-buffer (" + String(isLanMode() ? "LAN" : "Cloud") + "): " + bufferUrl);

  WiFiClient client;
  BearSSL::WiFiClientSecure secureClient;

  HTTPClient http;
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
//  PARSE RESPONSE JSON
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

  if (status == "success" || status == "info") {
    String namaDisplay  = nama.substring(0, min((int)nama.length(), 20));
    String kelasDisplay = kelas.substring(0, min((int)kelas.length(), 20));

    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(namaDisplay);

    if (kelasDisplay != "") {
      lcd.setCursor(0, 1); lcd.print(kelasDisplay);
    } else {
      lcd.setCursor(0, 1); lcd.print("-");
    }

    if (action_code == "CHECK_IN") {
      lcd.setCursor(0, 2); lcd.print("MASUK PADA: " + waktu);
      if (kelasDisplay.indexOf("Guru") >= 0 || kelasDisplay.indexOf("Staf") >= 0 || kelasDisplay.indexOf("Staff") >= 0) {
        lcd.setCursor(0, 3); lcd.print(F("Selamat Bertugas!   "));
      } else {
        lcd.setCursor(0, 3); lcd.print(F("Selamat Belajar!    "));
      }
      indicatorCheckIn();
    }
    else if (action_code == "CHECK_OUT") {
      lcd.setCursor(0, 2); lcd.print("PULANG PADA: " + waktu);
      lcd.setCursor(0, 3); lcd.print(F("Hati-hati di jalan! "));
      indicatorCheckOut();
    }
    else if (action_code == "COOLDOWN") {
      lcd.setCursor(0, 2); lcd.print(F("Sudah Absen Masuk!  "));
      if (waktu != "") {
        lcd.setCursor(0, 3); lcd.print("Jam Masuk: " + waktu);
      } else {
        lcd.setCursor(0, 3); lcd.print(message.substring(0, min((int)message.length(), 20)));
      }
      indicatorCooldown();
    }
    else if (action_code == "NEW_CARD") {
      sendScanBuffer(uid);
      lcd.clear();
      lcd.setCursor(0, 0); lcd.print(F("** KARTU BARU **    "));
      lcd.setCursor(0, 1); lcd.print(kelasDisplay);
      lcd.setCursor(0, 2); lcd.print(F("Belum terdaftar!    "));
      lcd.setCursor(0, 3); lcd.print(F("Daftarkan di Admin  "));
      indicatorNewCard();
    }
    else {
      lcd.setCursor(0, 2); lcd.print(message.substring(0, min((int)message.length(), 20)));
      lcd.setCursor(0, 3); lcd.print("Waktu: " + waktu);
      indicatorCheckIn();
    }
  } else {
    String errMsg = (action_code == "ALREADY_ATTENDED") ? "Sudah Absen Lengkap" : message;
    showError(errMsg);
  }
}

// ============================================================
//  KONEKSI WIFI (SMART PRIORITY: LAN PEMBDA-LINK -> INTERNET CLOUD)
// ============================================================
void connectWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.persistent(false);

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

  // --- TAHAP 2: LAN PembdaLINK Tidak Ditemukan -> Fallback ke Hotspot / Internet Cloud ---
  Serial.println(F("\n[NET] LAN PembdaLINK tidak ditemukan! Mencoba Hotspot / WiFi Alternatif..."));
  WiFi.disconnect();
  delay(100);

  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("=== LAN TDK ADA ===="));
  lcd.setCursor(0, 1); lcd.print("Cari: " + String(WIFI_ALT_SSID).substring(0, 14));
  lcd.setCursor(0, 2); lcd.print(F("Mencoba Hotspot...  "));
  lcd.setCursor(0, 3); lcd.print(F("Target: CLOUD PROD  "));
  delay(500);

  // Coba hubungkan langsung ke SSID Alternatif utama (Black_Hole)
  Serial.print(F("[NET] Menghubungkan langsung ke: "));
  Serial.println(WIFI_ALT_SSID);
  WiFi.begin(WIFI_ALT_SSID, WIFI_ALT_PASSWORD);

  attempt = 0;
  while (WiFi.status() != WL_CONNECTED && attempt < 24) { // Coba ~12 detik
    delay(500);
    Serial.print(F("*"));
    String dots = "";
    for (int i = 0; i < (attempt % 5) + 1; i++) dots += "*";
    lcd.setCursor(0, 2); lcd.print("Hubungkan Alt" + dots + "    ");
    attempt++;
  }

  // Jika belum terhubung, coba via wifiMulti untuk SSID cadangan lainnya
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println(F("\n[NET] Mencoba multi-scan SSID cadangan..."));
    attempt = 0;
    while (wifiMulti.run() != WL_CONNECTED && attempt < 16) {
      delay(500);
      attempt++;
    }
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
  if (now - lastMarqueeTime < 220) return;
  lastMarqueeTime = now;

  // Buat jendela 20 karakter dari teks berjalan
  String windowText = "";
  int textLen = marqueeText.length();
  for (int i = 0; i < 20; i++) {
    int idx = (marqueePos + i) % textLen;
    windowText += marqueeText[idx];
  }

  // Tampilkan di Baris 2
  lcd.setCursor(0, 2);
  lcd.print(windowText);

  marqueePos = (marqueePos + 1) % textLen;

  // Heartbeat blink di pojok kanan bawah
  lcd.setCursor(19, 3);
  if ((marqueePos / 2) % 2 == 0) lcd.write(byte(2));
  else lcd.print(F(" "));
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
//  INDIKATOR BUZZER
// ============================================================
void indicatorCheckIn() {
  for (int i = 0; i < 2; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(100);
    digitalWrite(BUZZER_PIN, LOW);
    if (i < 1) delay(100);
  }
}

void indicatorCheckOut() {
  for (int i = 0; i < 3; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(80);
    digitalWrite(BUZZER_PIN, LOW);
    if (i < 2) delay(80);
  }
}

void indicatorCooldown() {
  digitalWrite(BUZZER_PIN, HIGH);
  delay(300);
  digitalWrite(BUZZER_PIN, LOW);
}

void indicatorNewCard() {
  for (int i = 0; i < 4; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(60);
    digitalWrite(BUZZER_PIN, LOW);
    if (i < 3) delay(60);
  }
}

void indicatorFail() {
  digitalWrite(BUZZER_PIN, HIGH);
  delay(600);
  digitalWrite(BUZZER_PIN, LOW);
}

void beep(int count, int duration) {
  for (int i = 0; i < count; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(duration);
    digitalWrite(BUZZER_PIN, LOW);
    if (i < count - 1) delay(100);
  }
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
