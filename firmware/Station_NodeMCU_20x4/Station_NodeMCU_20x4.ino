// ============================================================
//  FIRMWARE NODEMCU V3 (ESP-12F) - PEMBDAHUB ATTENDANCE STATION
//  Station Absen Terpadu: RFID RC522 + GM65 QR Scanner + LCD 20x4 I2C + DFPlayer MP3 + Buzzer
//  
//  WIRING DIAGRAM NODEMCU V3:
//  ┌─────────────┬──────────┬───────────────────────────────────────┐
//  │ Komponen    │ Pin MCU  │ Catatan                               │
//  ├─────────────┼──────────┼───────────────────────────────────────┤
//  │ RFID SDA/SS │ D0 (16)  │ SPI CS                                │
//  │ RFID SCK    │ D5 (14)  │ SPI CLK default                       │
//  │ RFID MOSI   │ D7 (13)  │ SPI MOSI default                      │
//  │ RFID MISO   │ D6 (12)  │ SPI MISO default                      │
//  │ RFID RST    │ 3V3      │ DIHUBUNGKAN LANGSUNG KE 3.3V          │
//  │ LCD SDA     │ D2 (4)   │ I2C SDA default                       │
//  │ LCD SCL     │ D1 (5)   │ I2C SCL default                       │
//  │ QR RX       │ D3 (0)   │ SoftwareSerial RX (GM65 TX -> D3)     │
//  │ MP3 RX      │ D4 (2)   │ SoftwareSerial TX -> resistor 1K -> RX│
//  │ Buzzer (+)  │ D8 (15)  │ Pull-down = buzzer OFF saat boot      │
//  │ LCD/MP3 VCC │ VU (5V)  │ Dihubungkan ke pin VU (5V)            │
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
const char* SERVER_URL         = "https://perguruanpembda.com/api/attendance/rfid-scan";
const char* SCAN_BUFFER_URL    = "https://perguruanpembda.com/api/rfid/scan-buffer";
const char* KIOSK_API_KEY      = "RAHASIA-PEMBDAHUB-12345";

// ── GANTI DEVICE_ID UNTUK SETIAP STATION! ──
const char* DEVICE_ID          = "STATION-SMA-01";

// ============================================================
//  PIN DEFINITIONS - NodeMCU V3 (ESP-12F)
// ============================================================

// SPI Pins untuk RFID RC522 (menggunakan HSPI default ESP8266)
#define RFID_SS_PIN    16   // D0 (GPIO16) - SPI CS
#define RFID_RST_PIN  255   // UNUSED - Hubungkan pin RST RFID langsung ke 3.3V NodeMCU

// MP3 Player TX Pin (menggunakan SoftwareSerial bersama QR Scanner RX)
#define MP3_TX_PIN      2   // D4 (GPIO2) - Hubungkan ke RX MP3 Player via resistor 1K Ohm
#define MP3_VOLUME     30   // Tingkat volume MP3 (0 s.d 30)

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
String        lastUID          = "";
unsigned long lastTapTime      = 0;
String        qrBuffer         = "";
unsigned long lastWiFiCheck    = 0;
bool          isOnline         = false;

// Animasi & Smart Screensaver State
unsigned long lastActivityTime = 0;
unsigned long lastAnimTime     = 0;
int           animFrame        = 0;
bool          isShowingResult  = false;
bool          isScreensaver    = false;

// Teks Berjalan Slogan Resmi Yayasan Perguruan Pembda Nias
const String marqueeText = "   *** YAYASAN PERGURUAN PEMBDA NIAS *** Keep Moving Forward - Maju Terus Pantang Mundur! *** SMP - SMA - SMK Swasta Pembda *** Silakan Tempel Kartu RFID / Scan QR Code ***   ";
int           marqueePos        = 0;
unsigned long lastMarqueeTime  = 0;

// ============================================================
//  OBJEK HARDWARE
// ============================================================
MFRC522           rfid(RFID_SS_PIN, RFID_RST_PIN);
LiquidCrystal_I2C lcd(LCD_ADDRESS, LCD_COLS, LCD_ROWS);
ESP8266WiFiMulti  wifiMulti;
SoftwareSerial    kioskSerial(QR_RX_PIN, MP3_TX_PIN); // RX D3, TX D4

// Prototipe Fungsi Audio & Display
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
  Serial.println(F("\n=== NODEMCU STATION BOOTING ==="));
  Serial.println(F("Board: NodeMCU V3 (ESP-12F)"));
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
  delay(1500);

  // ── INISIALISASI SPI & RFID RC522 ──
  SPI.begin();
  SPI.setFrequency(1000000); // 1MHz timing stabil
  delay(50);
  
  // Inisialisasi awal + Software Reset via SPI (solusi stabil untuk pin RST di 3.3V)
  byte version = 0x00;
  for (int attempt = 1; attempt <= 3; attempt++) {
    rfid.PCD_Init();
    delay(50);
    rfid.PCD_Reset();   // Kirim instruksi SoftReset ke chip RC522 via SPI
    delay(50);
    rfid.PCD_Init();    // Konfigurasi ulang register setelah soft-reset
    delay(50);

    version = rfid.PCD_ReadRegister(rfid.VersionReg);
    Serial.print(F("Inisialisasi RFID (Percobaan ")); Serial.print(attempt);
    Serial.print(F("): Versi 0x")); Serial.println(version, HEX);

    if (version != 0x00 && version != 0xFF) {
      break; // Modul RC522 siap dan aktif!
    }
    delay(100);
  }

  if (version == 0x00 || version == 0xFF) {
    Serial.println(F("WARNING: RFID tidak terdeteksi! Cek wiring SPI."));
    Serial.println(F("  SS  = D0 (GPIO16), SCK = D5, MOSI = D7, MISO = D6, RST = 3.3V"));
    lcd.setCursor(0, 2); lcd.print(F("RFID ERROR! Cek SPI "));
    beep(5, 100);
    delay(2000);
  } else {
    Serial.print(F("RFID Firmware Version: 0x"));
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

  // Koneksi WiFi dengan multi-AP (4 Profil Jaringan)
  wifiMulti.addAP(WIFI_SSID, WIFI_PASSWORD);
  wifiMulti.addAP(WIFI_ALT_SSID, WIFI_ALT_PASSWORD);
  wifiMulti.addAP(WIFI_ALT2_SSID, WIFI_ALT2_PASSWORD);
  wifiMulti.addAP(WIFI_ALT3_SSID, WIFI_ALT3_PASSWORD);
  connectWiFi();
  isOnline = (WiFi.status() == WL_CONNECTED);

  // Inisialisasi SoftwareSerial untuk Kiosk (RX=D3 Scanner, TX=D4 MP3 Player)
  kioskSerial.begin(9600);
  delay(100);
  while (kioskSerial.available()) kioskSerial.read();
  qrBuffer = "";

  // Inisialisasi Volume MP3 Player
  setMp3Volume(MP3_VOLUME);
  delay(100);

  // Putar lagu pembuka 001.mp3 ("Selamat Pagi Silahkan Absen")
  playAudio(1, 1);

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

  // 3. Periksa status WiFi setiap 10 detik secara non-blocking
  if (now - lastWiFiCheck >= 10000 || lastWiFiCheck == 0) {
    lastWiFiCheck = now;
    if (wifiMulti.run() == WL_CONNECTED) {
      if (!isOnline) {
        isOnline = true;
        Serial.println(F("WiFi Terhubung Kembali."));
        if (!isShowingResult) {
          if (isScreensaver) showScreensaverBase();
          else showReady();
        }
      }
    } else {
      if (isOnline) {
        isOnline = false;
        Serial.println(F("WiFi Terputus! Mencoba mencari jaringan..."));
        if (!isShowingResult) {
          if (isScreensaver) showScreensaverBase();
          else showReady();
        }
      }
    }
  }

  // 4. Jika sedang menampilkan hasil scan atau ada data masuk di serial QR, tunda update LCD
  if (isShowingResult || kioskSerial.available() > 0) {
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

  while (kioskSerial.available() > 0 && maxRead-- > 0) {
    char c = kioskSerial.read();

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
//  KIRIM DATA KE SERVER (HTTPS)
// ============================================================
void sendToServer(String uid, String type) {
  Serial.println("Mengirim ke server: " + uid + " (" + type + ")");
  
  if (WiFi.status() != WL_CONNECTED) {
    playAudio(1, 7); // 007.MP3 - Sistem ada gangguan hubungi admin
    showError("Koneksi Internet Off");
    return;
  }

  BearSSL::WiFiClientSecure client;
  client.setInsecure();

  HTTPClient http;
  http.begin(client, String(SERVER_URL));
  http.addHeader("Content-Type",    "application/json");
  http.addHeader("X-Kiosk-API-Key", KIOSK_API_KEY);
  http.addHeader("Accept",          "application/json");
  http.setTimeout(HTTP_TIMEOUT);

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
    playAudio(1, 7); // 007.MP3 - Sistem ada gangguan hubungi admin
    showError("API Key Tidak Valid!");
  } else if (code == 404) {
    playAudio(1, 6); // 006.MP3 - Kartu tidak dikenali hubungi admin
    showError("Kartu/QR Tdk Terdaftar");
  } else if (code > 0) {
    playAudio(1, 7); // 007.MP3 - Sistem ada gangguan hubungi admin
    showError("Gagal Server: " + String(code));
  } else {
    String errMsg = http.errorToString(code);
    String payload = http.getString();
    if (payload.length() > 10 && payload.indexOf("status") > 0) {
      parseAndDisplay(payload, uid);
    } else {
      playAudio(1, 7); // 007.MP3 - Sistem ada gangguan hubungi admin
      showError("Server Error: " + errMsg.substring(0, 14));
    }
  }

  http.end();
  while (kioskSerial.available()) kioskSerial.read();
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

  Serial.println(F("Mengirim UID ke scan-buffer..."));

  BearSSL::WiFiClientSecure client;
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
      playAudio(1, 2); 
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
//  KONEKSI WIFI
// ============================================================
void connectWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.persistent(false);
  int attempt = 0;

  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("=== MENCARI WIFI ==="));
  lcd.setCursor(0, 1); lcd.print(F("Mencoba koneksi...  "));
  lcd.setCursor(0, 2); lcd.print(F("Hubungkan WiFi...   "));
  lcd.setCursor(0, 3); lcd.print(F("--------------------"));

  while (wifiMulti.run() != WL_CONNECTED && attempt < 20) {
    delay(500);
    Serial.print(".");
    String dots = "";
    for (int i = 0; i < (attempt % 6); i++) dots += ".";
    lcd.setCursor(0, 2); lcd.print("Menghubungkan" + dots + "    ");
    attempt++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(F("=== WIFI CONNECT ==="));
    lcd.setCursor(0, 1); lcd.print("SSID: " + WiFi.SSID().substring(0, 14));
    lcd.setCursor(0, 2); lcd.print("IP: " + WiFi.localIP().toString());
    lcd.setCursor(0, 3); lcd.print(F("--------------------"));
    beep(1, 200);
    delay(1500);
  } else {
    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(F("=== WIFI GAGAL ====="));
    lcd.setCursor(0, 1); lcd.print(F("Koneksi gagal!      "));
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

  // Baris 3: Status Bar & SSID WiFi
  lcd.setCursor(0, 3);
  if (isOnline) {
    lcd.write(byte(0)); // Ikon WiFi
    lcd.print(F(" ON:"));
    String ssid = WiFi.SSID();
    if (ssid.length() > 10) ssid = ssid.substring(0, 10);
    lcd.print(ssid);
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
  
  // Baris 3: Status Bar Siaga Scan
  lcd.setCursor(0, 3);
  if (isOnline) {
    lcd.write(byte(0));
    lcd.print(F(" "));
    String ssid = WiFi.SSID();
    if (ssid.length() > 8) ssid = ssid.substring(0, 8);
    lcd.print(ssid);
    lcd.setCursor(12, 3);
    lcd.print(F("SCAN"));
  } else {
    lcd.print(F("[OFFLINE]   SCAN"));
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
//  FUNGSI MP3 PLAYER RAW COMMANDS (Mode Universal 8-Byte)
// ============================================================
void sendMp3Command(uint8_t cmd, uint8_t para1, uint8_t para2) {
  uint8_t cmdBuffer[8] = { 0x7E, 0xFF, 0x06, cmd, 0x00, para1, para2, 0xEF };
  kioskSerial.write(cmdBuffer, 8);
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
