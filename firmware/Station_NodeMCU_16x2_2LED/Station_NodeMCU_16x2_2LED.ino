// ============================================================
//  FIRMWARE NODEMCU V3 (ESP-12F) - PEMBDAHUB ATTENDANCE STATION
//  Versi: RFID RC522 + LCD 16x2 + 2 LED (Hijau & Merah) + Buzzer
//  (TANPA MP3 PLAYER)
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
//  KONFIGURASI - Sesuaikan untuk setiap station!
// ============================================================

// WiFi Utama
const char* WIFI_SSID         = "Xspace";
const char* WIFI_PASSWORD     = "12345678starlink";

// WiFi Alternatif (otomatis fallback jika utama gagal)
const char* WIFI_ALT_SSID     = "TEFA";
const char* WIFI_ALT_PASSWORD = "PEMBDA2026";

// WiFi Alternatif 2
const char* WIFI_ALT2_SSID    = "VistaHotLine";
const char* WIFI_ALT2_PASSWORD= "pelita31";

// Server API PembdaHUB
const char* SERVER_URL        = "https://perguruanpembda.com/api/attendance/rfid-scan";
const char* KIOSK_API_KEY     = "RAHASIA-PEMBDAHUB-12345";

// Device ID Unik Per Station
const char* DEVICE_ID         = "STATION-SMK-01";

// ============================================================
//  PIN DEFINITIONS - NodeMCU V3 (ESP-12F)
// ============================================================

// SPI Pins untuk RFID RC522
#define RFID_SS_PIN    16   // D0 (GPIO16)
#define RFID_RST_PIN  255   // UNUSED - Hubungkan pin RST RFID langsung ke 3.3V NodeMCU

// Indikator LED & Buzzer
#define LED_GREEN_PIN   0   // D3 (GPIO0)  - LED Hijau (Berhasil / Sukses)
#define LED_RED_PIN     2   // D4 (GPIO2)  - LED Merah (Gagal / Kartu Salah)
#define BUZZER_PIN     15   // D8 (GPIO15) - Buzzer (Pull-down bawaan)

// I2C LCD 16x2
#define LCD_ADDRESS    0x27
#define LCD_COLS       16
#define LCD_ROWS       2

// ============================================================
//  TIMEOUTS & COOLDOWNS
// ============================================================
#define HTTP_TIMEOUT        10000   // 10 detik (ESP8266 TLS)
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

// ============================================================
//  DEKLARASI PROTOTIPE FUNGSI (Agar tidak error 'not declared')
// ============================================================
void connectWiFi();
void showReady();
void showError(String msg);
void indicatorSuccess();
void indicatorFail();
void indicatorCooldown();
void indicatorNewCard();
void handleRfidScan();
void sendToServer(String uid, String type);
void parseAndDisplay(String json);
String getRfidUID();

// ============================================================
//  INDIKATOR BUZZER & 2 LED (HIJAU & MERAH)
// ============================================================

// Sukses (Check-In / Check-Out): LED Hijau Nyala + 2 Beep Pendek
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

// Cooldown / Sudah Absen: LED Hijau Nyala 1x Panjang (400ms) + 1 Beep
void indicatorCooldown() {
  digitalWrite(LED_RED_PIN, LOW);
  digitalWrite(LED_GREEN_PIN, HIGH);
  digitalWrite(BUZZER_PIN, HIGH);
  delay(400);
  digitalWrite(LED_GREEN_PIN, LOW);
  digitalWrite(BUZZER_PIN, LOW);
}

// Kartu Baru / Belum Terdaftar: LED Merah Kedip 3x Cepat + Beep
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

// Gagal / Error: LED Merah Nyala 1x Panjang (700ms) + 1 Beep Panjang
void indicatorFail() {
  digitalWrite(LED_GREEN_PIN, LOW);
  digitalWrite(LED_RED_PIN, HIGH);
  digitalWrite(BUZZER_PIN, HIGH);
  delay(700);
  digitalWrite(LED_RED_PIN, LOW);
  digitalWrite(BUZZER_PIN, LOW);
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
    
    // Kedip LED Hijau 2x sebagai tanda siap
    for(int i=0; i<2; i++){
      digitalWrite(LED_GREEN_PIN, HIGH); digitalWrite(BUZZER_PIN, HIGH); delay(100);
      digitalWrite(LED_GREEN_PIN, LOW);  digitalWrite(BUZZER_PIN, LOW);  delay(100);
    }
    delay(1000);
  } else {
    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(F("WIFI GAGAL!     "));
    lcd.setCursor(0, 1); lcd.print(F("Auto-retry bg..."));
    indicatorFail();
    delay(1500);
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
//  SETUP
// ============================================================
void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println(F("\n=== NODEMCU STATION BOOTING (2 LED + NO MP3) ==="));
  Serial.println(F("Board: NodeMCU V3 (ESP-12F) - 16x2 + 2 LED + Buzzer"));
  Serial.print(F("Device ID: ")); Serial.println(DEVICE_ID);

  // Inisialisasi LED & Buzzer
  pinMode(LED_GREEN_PIN, OUTPUT);
  pinMode(LED_RED_PIN, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT);

  digitalWrite(LED_GREEN_PIN, LOW);
  digitalWrite(LED_RED_PIN, LOW);
  digitalWrite(BUZZER_PIN, LOW);

  // Inisialisasi I2C LCD (SDA=GPIO4/D2, SCL=GPIO5/D1)
  Wire.begin(4, 5);
  lcd.init();
  lcd.backlight();
  lcd.setCursor(0, 0); lcd.print(F("   PEMBDA HUB   "));
  lcd.setCursor(0, 1); lcd.print(F("  Silakan Scan  "));
  delay(1500);

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
    indicatorFail();
    delay(2000);
  } else {
    rfid.PCD_SetAntennaGain(rfid.RxGain_max);
    delay(10);
    rfid.PCD_AntennaOn();
  }

  // Koneksi WiFi dengan multi-AP
  wifiMulti.addAP(WIFI_SSID, WIFI_PASSWORD);
  wifiMulti.addAP(WIFI_ALT_SSID, WIFI_ALT_PASSWORD);
  wifiMulti.addAP(WIFI_ALT2_SSID, WIFI_ALT2_PASSWORD);
  connectWiFi();
  isOnline = (WiFi.status() == WL_CONNECTED);
  showReady();

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
  if (!rfid.PICC_IsNewCardPresent()) return;

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
  
  // Beep pendek saat scan kartu
  digitalWrite(BUZZER_PIN, HIGH);
  delay(80);
  digitalWrite(BUZZER_PIN, LOW);

  // Halt kartu SEBELUM kirim HTTP
  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();

  // Kirim ke server
  sendToServer(uid, "rfid");

  // Reset lastUID
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

  if (code == 200 || code == 201) {
    String payload = http.getString();
    parseAndDisplay(payload);
  } else if (code == 401) {
    showError("API Key Invalid!");
  } else if (code == 404) {
    showError("Kartu Tdk Kenal ");
  } else if (code > 0) {
    showError("Server Err " + String(code));
  } else {
    String payload = http.getString();
    if (payload.length() > 10 && payload.indexOf("status") > 0) {
      parseAndDisplay(payload);
    } else {
      showError("Gagal Terhubung ");
    }
  }

  http.end();
  delay(DISPLAY_RESULT_MS);
  showReady();
}

// ============================================================
//  PARSE RESPONSE JSON (LCD 16x2)
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
    String namaDisplay  = nama.substring(0, min((int)nama.length(), 16));
    String waktuShort   = waktu.substring(0, min((int)waktu.length(), 11));

    lcd.clear();
    
    if (action_code == "CHECK_IN") {
      lcd.setCursor(0, 0); lcd.print(namaDisplay);
      lcd.setCursor(0, 1); lcd.print("Masuk: " + waktuShort);
      indicatorSuccess();
    }
    else if (action_code == "CHECK_OUT") {
      lcd.setCursor(0, 0); lcd.print(namaDisplay);
      lcd.setCursor(0, 1); lcd.print("Pulang:" + waktuShort);
      indicatorSuccess();
    }
    else if (action_code == "COOLDOWN") {
      lcd.setCursor(0, 0); lcd.print(F("Sudah Absen!    "));
      if (waktu != "") {
        lcd.setCursor(0, 1); lcd.print("Jam: " + waktuShort);
      } else {
        lcd.setCursor(0, 1); lcd.print(message.substring(0, 16));
      }
      indicatorCooldown();
    }
    else if (action_code == "NEW_CARD") {
      lcd.setCursor(0, 0); lcd.print(F("KARTU BARU      "));
      lcd.setCursor(0, 1); lcd.print(F("Belum Terdaftar "));
      indicatorNewCard();
    }
    else {
      lcd.setCursor(0, 0); lcd.print(message.substring(0, 16));
      lcd.setCursor(0, 1); lcd.print("Waktu: " + waktuShort);
      indicatorSuccess();  
    }
  } else {
    String errMsg = (action_code == "ALREADY_ATTENDED") ? "Sudah Absen     " : message;
    if (action_code == "ALREADY_ATTENDED") {
      indicatorCooldown();
    } else {
      indicatorFail();
    }
    showError(errMsg);
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
