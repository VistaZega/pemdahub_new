// ============================================================
//  FIRMWARE NODEMCU V3 (ESP-12F) - PEMBDAHUB ATTENDANCE STATION
//  Versi: RFID RC522 + LCD 16x2 + 2 LED (Hijau & Merah) + Buzzer
//  (TANPA MP3 PLAYER - WITH MULTI WIFI DISPLAY & CONTINUOUS LOOP)
//
//  Tampilan (UI), Custom Icon, Layar Siaga, Smart Screensaver Marquee,
//  dan Sinkronisasi Buffer Kartu Baru 100% IDENTIK dengan ESP32 16x2.
// ============================================================
//
//  WIRING DIAGRAM NODEMCU V3:
//  ┌──────────────┬──────────┬───────────────────────────────┐
//  │ Komponen     │ Pin MCU  │ Catatan                       │
//  ├──────────────┼──────────┼───────────────────────────────┤
//  │ RFID SDA/SS  │ D0 (16)  │ SPI CS                        │
//  │ RFID SCK     │ D5 (14)  │ SPI CLK default               │
//  │ RFID MOSI    │ D7 (13)  │ SPI MOSI default              │
//  │ RFID MISO    │ D6 (12)  │ SPI MISO default              │
//  │ RFID RST     │ -        │ Hubungkan langsung ke 3.3V    │
//  │ LCD SDA      │ D2 (4)   │ I2C SDA default               │
//  │ LCD SCL      │ D1 (5)   │ I2C SCL default               │
//  │ LED Hijau (+)│ D3 (0)   │ Resistor 1K → Anoda LED Hijau │
//  │ LED Merah (+)│ D4 (2)   │ Resistor 1K → Anoda LED Merah │
//  │ Buzzer (+)   │ D8 (15)  │ Pull-down = buzzer OFF @boot  │
//  │ RFID 3.3V    │ 3V3      │ JANGAN pakai 5V untuk RC522!  │
//  │ LCD VCC      │ 5V       │ LCD 16x2 butuh 5V!            │
//  │ GND          │ GND      │ Common ground semua komponen  │
//  └──────────────┴──────────┴───────────────────────────────┘

#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>
#include <WiFiClientSecure.h>
#include <ESP8266WiFiMulti.h>

// ============================================================
//  KONFIGURASI MULTI-WIFI & SERVER
// ============================================================

// WiFi Utama & Alternatif
const char* WIFI_SSID          = "PembdaLINK";
const char* WIFI_PASSWORD      = "PEMBDA2026";

const char* WIFI_ALT_SSID      = "Xspace";
const char* WIFI_ALT_PASSWORD  = "12345678starlink";

const char* WIFI_ALT2_SSID     = "TEFA";
const char* WIFI_ALT2_PASSWORD = "PEMBDA2026";

const char* WIFI_ALT3_SSID     = "VISTAFAMILY";
const char* WIFI_ALT3_PASSWORD = "pelita31";

// Server API PembdaHUB
// - Server Lokal Sekolah   : "http://50.35.89.10/api/attendance/rfid-scan"
// - Server Production Cloud : "https://perguruanpembda.com/api/attendance/rfid-scan"
const char* SERVER_URL         = "http://50.35.89.10/api/attendance/rfid-scan";
const char* SCAN_BUFFER_URL    = "http://50.35.89.10/api/rfid/scan-buffer";
const char* KIOSK_API_KEY      = "RAHASIA-PEMBDAHUB-12345";

// Device ID Unik Per Station
const char* DEVICE_ID          = "STATION-SMK-03";

// ============================================================
//  PIN DEFINITIONS - NodeMCU V3 (ESP-12F)
// ============================================================

#define RFID_SS_PIN    16   // D0 (GPIO16)
#define RFID_RST_PIN  255   // UNUSED - Hubungkan pin RST RFID langsung ke 3.3V

#define LED_GREEN_PIN   0   // D3 (GPIO0)  - LED Hijau (Berhasil / Sukses)
#define LED_RED_PIN     2   // D4 (GPIO2)  - LED Merah (Gagal / Kartu Salah)
#define BUZZER_PIN     15   // D8 (GPIO15) - Buzzer

#define LCD_ADDRESS    0x27
#define LCD_COLS       16
#define LCD_ROWS       2

// ============================================================
//  TIMING & COOLDOWNS
// ============================================================
#define HTTP_TIMEOUT        8000   // 8 detik timeout HTTP
#define DISPLAY_RESULT_MS   3500   // Durasi tampil hasil di LCD
#define SCAN_COOLDOWN_MS    3000   // Anti double-tap (3 detik)
#define IDLE_TIMEOUT_MS     20000  // 20 detik tanpa scan -> Aktifkan Screensaver

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
//  GLOBAL STATE & OBJEK
// ============================================================
String        lastUID          = "";
unsigned long lastTapTime      = 0;
unsigned long lastWiFiCheck    = 0;
unsigned long lastActivityTime = 0;
unsigned long lastAnimTime     = 0;
unsigned long lastMarqueeTime  = 0;

bool          isOnline         = false;
bool          isShowingResult  = false;
bool          isScreensaver    = false;

int           animFrame        = 0;
int           marqueePos       = 0;

// Slogan Resmi Perguruan Pembda Nias untuk Running Text
const String  marqueeText      = "Maju Terus Pantang Mundur - Keep Moving Forward - Perguruan Pembda Nias       ";

MFRC522           rfid(RFID_SS_PIN, RFID_RST_PIN);
LiquidCrystal_I2C lcd(LCD_ADDRESS, LCD_COLS, LCD_ROWS);
ESP8266WiFiMulti  wifiMulti;

// ============================================================
//  PROTOTIPE FUNGSI
// ============================================================
void connectWiFi();
void handleRfidScan();
void sendToServer(String uid, String type);
void sendScanBuffer(String uid);
void parseAndDisplay(String json, String uid);
void showReady();
void showScreensaverBase();
void updateLcdAnimation();
void updateMarquee();
void showError(String msg);
void indicatorSuccess();
void indicatorFail();
void indicatorCooldown();
void indicatorNewCard();
void beep(int count, int duration);
String getRfidUID();

// ============================================================
//  SETUP
// ============================================================
void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println(F("\n=============================================="));
  Serial.println(F("  PEMBDAHUB STATION (NodeMCU 16x2 2-LED)      "));
  Serial.println(F("=============================================="));
  Serial.print(F("Device ID : ")); Serial.println(DEVICE_ID);
  Serial.print(F("Free Heap : ")); Serial.print(ESP.getFreeHeap() / 1024); Serial.println(F(" KB"));

  // 1. Inisialisasi Pin Indikator
  pinMode(LED_GREEN_PIN, OUTPUT);
  pinMode(LED_RED_PIN,   OUTPUT);
  pinMode(BUZZER_PIN,    OUTPUT);

  digitalWrite(LED_GREEN_PIN, LOW);
  digitalWrite(LED_RED_PIN,   LOW);
  digitalWrite(BUZZER_PIN,    LOW);

  // 2. Inisialisasi I2C LCD (SDA=GPIO4/D2, SCL=GPIO5/D1)
  Wire.begin(4, 5);
  Wire.setClock(400000); // 400kHz Fast I2C transfer tanpa jeda
#ifdef FDB_LIQUID_CRYSTAL_I2C_H
  lcd.begin();
#else
  lcd.init();
#endif
  lcd.backlight();
  lcd.createChar(0, iconWifi);
  lcd.createChar(1, iconCard);
  lcd.createChar(2, iconHeart);

  lcd.setCursor(0, 0); lcd.print(F("* PERGURUAN    *"));
  lcd.setCursor(0, 1); lcd.print(F("* PEMBDA NIAS  *"));
  beep(1, 100);
  delay(1500);

  // 3. Inisialisasi SPI & RFID RC522 (HSPI NodeMCU)
  SPI.begin();
  SPI.setFrequency(1000000); // 1MHz agar chip stabil
  delay(50);
  
  rfid.PCD_Init();
  delay(100);

  Serial.println(F("Mengecek modul RFID RC522..."));
  byte version = rfid.PCD_ReadRegister(rfid.VersionReg);
  if (version == 0x00 || version == 0xFF) {
    Serial.println(F("WARNING: RFID tidak terdeteksi! Cek wiring SPI."));
    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(F("! RFID RC522 ER !"));
    lcd.setCursor(0, 1); lcd.print(F("Cek Jalur SPI   "));
    indicatorFail();
    delay(2000);
  } else {
    Serial.print(F("RFID Chip Version: 0x"));
    Serial.print(version, HEX);
    if (version == 0x91 || version == 0x92) Serial.println(F(" (MFRC522 Original)"));
    else if (version == 0x88)               Serial.println(F(" (FM17522 Clone)"));
    else if (version == 0xB2)               Serial.println(F(" (MFRC522 Clone - OK)"));
    else                                    Serial.println(F(" (Compatible)"));

    rfid.PCD_SetAntennaGain(rfid.RxGain_max);
    delay(10);
    rfid.PCD_AntennaOn();
  }

  // 4. Tambahkan semua 4 WiFi AP ke wifiMulti
  wifiMulti.addAP(WIFI_SSID,      WIFI_PASSWORD);
  wifiMulti.addAP(WIFI_ALT_SSID,  WIFI_ALT_PASSWORD);
  wifiMulti.addAP(WIFI_ALT2_SSID, WIFI_ALT2_PASSWORD);
  wifiMulti.addAP(WIFI_ALT3_SSID, WIFI_ALT3_PASSWORD);

  connectWiFi();
  isOnline = (WiFi.status() == WL_CONNECTED);

  lastActivityTime = millis();
  showReady();
  Serial.println(F("System Ready. Silakan scan kartu RFID."));
}

// ============================================================
//  LOOP UTAMA
// ============================================================
void loop() {
  unsigned long now = millis();

  // 1. Cek Koneksi WiFi Multi-AP setiap 15 detik secara non-blocking
  if (now - lastWiFiCheck >= 15000 || lastWiFiCheck == 0) {
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
        Serial.println(F("WiFi Terputus! Auto-reconnecting..."));
        if (!isShowingResult) {
          if (isScreensaver) showScreensaverBase();
          else showReady();
        }
      }
    }
  }

  // 2. Cek Transisi Screensaver Hemat Layar & Teks Berjalan
  if (!isShowingResult) {
    if (!isScreensaver && (now - lastActivityTime >= IDLE_TIMEOUT_MS)) {
      isScreensaver = true;
      marqueePos    = 0;
      showScreensaverBase();
    }

    if (isScreensaver) {
      updateMarquee();
    } else {
      updateLcdAnimation();
    }
  }

  // 3. Deteksi Kartu RFID
  handleRfidScan();

  delay(20);
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
  Serial.println("RFID Terdeteksi -> UID: " + uid);

  // Anti double-tap
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

  // Bangunkan dari screensaver & tampilkan status proses
  lastActivityTime = millis();
  isScreensaver    = false;
  isShowingResult  = true;

  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("* MEMPROSES... *"));
  lcd.setCursor(0, 1); lcd.print("UID:" + uid.substring(0, 12));
  beep(1, 80);

  // Kirim ke server via HTTP/HTTPS
  sendToServer(uid, "rfid");

  lastUID = "";
}

// ============================================================
//  KIRIM DATA KE SERVER (DUAL-MODE HTTP / HTTPS)
// ============================================================
void sendToServer(String uid, String type) {
  Serial.println("Mengirim presensi: " + uid + " (" + type + ")");

  if (WiFi.status() != WL_CONNECTED) {
    showError("Tidak Ada WiFi!");
    return;
  }

  WiFiClient client;
  BearSSL::WiFiClientSecure secureClient;

  HTTPClient http;
  if (String(SERVER_URL).startsWith("https://")) {
    secureClient.setInsecure(); // Bypass SSL jika HTTPS
    http.begin(secureClient, String(SERVER_URL));
  } else {
    http.begin(client, String(SERVER_URL)); // Ultra-fast HTTP untuk server lokal
  }

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
  Serial.print(F("HTTP Code: ")); Serial.println(code);

  if (code == 200 || code == 201) {
    String payload = http.getString();
    Serial.println("Server Response: " + payload);
    parseAndDisplay(payload, uid);
  } else if (code == 401) {
    showError("API Key Salah!");
  } else if (code == 404) {
    showError("Kartu Tdk Drftr");
  } else if (code > 0) {
    showError("Server HTTP " + String(code));
  } else {
    String errMsg = http.errorToString(code);
    Serial.println("HTTP Error: " + errMsg);
    String payload = http.getString();
    if (payload.length() > 10 && payload.indexOf("status") > 0) {
      parseAndDisplay(payload, uid);
    } else {
      showError("Koneksi Gagal");
    }
  }

  http.end();

  delay(DISPLAY_RESULT_MS);
  lastActivityTime = millis();
  isScreensaver    = false;
  showReady();
}

// ============================================================
//  KIRIM KE SCAN BUFFER (Untuk Pendaftaran RFID Baru di Web Admin)
// ============================================================
void sendScanBuffer(String uid) {
  if (WiFi.status() != WL_CONNECTED) return;

  Serial.println(F("Mengirim UID ke buffer pendaftaran web..."));

  WiFiClient client;
  BearSSL::WiFiClientSecure secureClient;

  HTTPClient http;
  if (String(SCAN_BUFFER_URL).startsWith("https://")) {
    secureClient.setInsecure();
    http.begin(secureClient, SCAN_BUFFER_URL);
  } else {
    http.begin(client, SCAN_BUFFER_URL);
  }

  http.addHeader("Content-Type",    "application/json");
  http.addHeader("X-Kiosk-API-Key", KIOSK_API_KEY);
  http.setTimeout(5000);

  StaticJsonDocument<64> doc;
  doc["uid"] = uid;
  String body;
  serializeJson(doc, body);

  int code = http.POST(body);
  Serial.print(F("Buffer response: ")); Serial.println(code);

  http.end();
}

// ============================================================
//  PARSE JSON SERVER & TAMPILAN LCD 16x2
// ============================================================
void parseAndDisplay(String json, String uid) {
  StaticJsonDocument<256> doc;
  if (deserializeJson(doc, json)) {
    showError("Format Data ER");
    return;
  }

  String status      = doc["status"]      | "error";
  String nama        = doc["nama"]        | "Tidak Dikenal";
  String kelas       = doc["kelas"]       | "";
  String message     = doc["message"]     | "";
  String waktu       = doc["waktu"]       | "";
  String action_code = doc["action_code"] | "";

  // Format teks pas di 16 karakter layar
  String namaDisplay  = nama.substring(0, min((int)nama.length(), 16));

  if (status == "success" || status == "info") {

    if (action_code == "CHECK_IN") {
      lcd.clear();
      lcd.setCursor(0, 0); lcd.print(namaDisplay);
      lcd.setCursor(0, 1); lcd.print("MSK: " + waktu.substring(0, 8));
      indicatorSuccess();
    }
    else if (action_code == "CHECK_OUT") {
      lcd.clear();
      lcd.setCursor(0, 0); lcd.print(namaDisplay);
      lcd.setCursor(0, 1); lcd.print("PLG: " + waktu.substring(0, 8));
      indicatorSuccess();
    }
    else if (action_code == "COOLDOWN") {
      lcd.clear();
      lcd.setCursor(0, 0); lcd.print(namaDisplay);
      if (waktu != "") {
        lcd.setCursor(0, 1); lcd.print("Sdh Absen " + waktu.substring(0, 5));
      } else {
        lcd.setCursor(0, 1); lcd.print(message.substring(0, 16));
      }
      indicatorCooldown();
    }
    else if (action_code == "NEW_CARD") {
      sendScanBuffer(uid);

      lcd.clear();
      lcd.setCursor(0, 0); lcd.print(F("** KARTU BARU **"));
      lcd.setCursor(0, 1); lcd.print(F("Daftar di Admin "));
      indicatorNewCard();
    }
    else {
      lcd.clear();
      lcd.setCursor(0, 0); lcd.print(namaDisplay);
      lcd.setCursor(0, 1); lcd.print(message.substring(0, 16));
      indicatorSuccess();
    }

  } else {
    String errMsg;
    if (action_code == "ALREADY_ATTENDED") {
      errMsg = "Sdh Absen Hdr&Plg";
      indicatorCooldown();
    } else {
      errMsg = message.substring(0, 16);
      indicatorFail();
    }
    showError(errMsg);
  }
}

// ============================================================
//  KONEKSI WIFI MULTI-AP
// ============================================================
void connectWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.persistent(false);
  int attempt = 0;

  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("Menghub. WiFi..."));
  lcd.setCursor(0, 1); lcd.print(F("Mencari AP...   "));

  while (wifiMulti.run() != WL_CONNECTED && attempt < 25) {
    delay(500);
    Serial.print(".");
    attempt++;
    String dots = "";
    for (int i = 0; i < (attempt % 5) + 1; i++) dots += ".";
    lcd.setCursor(0, 1); lcd.print("Koneksi" + dots + "       ");
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println(F("\nWiFi Terhubung!"));
    Serial.print(F("SSID : ")); Serial.println(WiFi.SSID());
    Serial.print(F("IP   : ")); Serial.println(WiFi.localIP().toString());
    Serial.print(F("RSSI : ")); Serial.print(WiFi.RSSI()); Serial.println(F(" dBm"));

    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(F("WiFi Terhubung! "));
    String ssid = WiFi.SSID();
    if (ssid.length() > 16) ssid = ssid.substring(0, 16);
    lcd.setCursor(0, 1); lcd.print(ssid);
    beep(1, 200);
    delay(1500);
  } else {
    Serial.println(F("\nWiFi Belum Terhubung. Siaga di mode offline/retry background."));
    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(F("! WiFi Offline !"));
    lcd.setCursor(0, 1); lcd.print(F("Auto-retry bg..."));
    beep(3, 100);
    delay(1500);
  }
}

// ============================================================
//  FUNGSI DISPLAY LCD 16x2 (STANDBY & SCREENSAVER)
// ============================================================

void showReady() {
  isShowingResult = false;
  lcd.clear();

  // Baris 0: Header Keren
  lcd.setCursor(0, 0);
  lcd.print(F("*PEMBDA PRESENSI"));

  // Baris 1: Petunjuk Scan & Ikon
  lcd.setCursor(0, 1);
  if (isOnline) {
    lcd.write(byte(1)); // Ikon Kartu
    lcd.print(F(" Tempel Kartu  "));
  } else {
    lcd.print(F("[OFFLINE]Scan..."));
  }
}

void updateLcdAnimation() {
  if (isShowingResult || isScreensaver) return;

  unsigned long now = millis();
  if (now - lastAnimTime < 400) return;
  lastAnimTime = now;

  // Heartbeat berkedip di pojok kanan bawah
  lcd.setCursor(15, 1);
  if (animFrame % 2 == 0) {
    lcd.write(byte(2)); // Ikon Heart
  } else {
    lcd.print(F(" "));
  }
  animFrame = (animFrame + 1) % 2;
}

void showScreensaverBase() {
  isShowingResult = false;
  lcd.clear();

  // Baris 0: Header Perguruan Pembda
  lcd.setCursor(0, 0);
  lcd.print(F("*PERGURUAN PEMBDA*"));
}

void updateMarquee() {
  if (isShowingResult || !isScreensaver) return;

  unsigned long now = millis();
  if (now - lastMarqueeTime < 220) return; // Scroll setiap 220ms
  lastMarqueeTime = now;

  int textLen = marqueeText.length();
  String windowText = "";
  for (int i = 0; i < 16; i++) {
    int idx = (marqueePos + i) % textLen;
    windowText += marqueeText[idx];
  }

  lcd.setCursor(0, 1);
  lcd.print(windowText);

  marqueePos = (marqueePos + 1) % textLen;
}

void showError(String msg) {
  isShowingResult = true;
  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("! PERINGATAN !  "));
  lcd.setCursor(0, 1); lcd.print(msg.substring(0, 16));
}

// ============================================================
//  INDIKATOR BUZZER & 2 LED FISIK (HIJAU & MERAH)
// ============================================================

// CHECK_IN / CHECK_OUT SUKSES: 2x Beep + LED Hijau Nyala
void indicatorSuccess() {
  digitalWrite(LED_RED_PIN, LOW);
  for (int i = 0; i < 2; i++) {
    digitalWrite(LED_GREEN_PIN, HIGH);
    digitalWrite(BUZZER_PIN, HIGH);
    delay(100);
    digitalWrite(LED_GREEN_PIN, LOW);
    digitalWrite(BUZZER_PIN, LOW);
    if (i < 1) delay(100);
  }
}

// COOLDOWN: 1x Beep Panjang + LED Hijau
void indicatorCooldown() {
  digitalWrite(LED_RED_PIN, LOW);
  digitalWrite(LED_GREEN_PIN, HIGH);
  digitalWrite(BUZZER_PIN, HIGH);
  delay(350);
  digitalWrite(LED_GREEN_PIN, LOW);
  digitalWrite(BUZZER_PIN, LOW);
}

// NEW_CARD: 3x Beep Cepat + LED Merah Kedip
void indicatorNewCard() {
  digitalWrite(LED_GREEN_PIN, LOW);
  for (int i = 0; i < 3; i++) {
    digitalWrite(LED_RED_PIN, HIGH);
    digitalWrite(BUZZER_PIN, HIGH);
    delay(80);
    digitalWrite(LED_RED_PIN, LOW);
    digitalWrite(BUZZER_PIN, LOW);
    if (i < 2) delay(80);
  }
}

// ERROR/FAIL: 1x Beep Panjang Sekali + LED Merah
void indicatorFail() {
  digitalWrite(LED_GREEN_PIN, LOW);
  digitalWrite(LED_RED_PIN, HIGH);
  digitalWrite(BUZZER_PIN, HIGH);
  delay(600);
  digitalWrite(LED_RED_PIN, LOW);
  digitalWrite(BUZZER_PIN, LOW);
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
//  BACA UID RFID (Format HEX Uppercase, contoh: "8374141B")
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
