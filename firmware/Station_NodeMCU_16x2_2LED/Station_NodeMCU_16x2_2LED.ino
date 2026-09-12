// ============================================================
//  FIRMWARE NODEMCU V3 (ESP-12F) - PEMBDAHUB ATTENDANCE STATION
//  Versi: RFID RC522 + LCD 16x2 + 2 LED (Hijau & Merah) + Buzzer
//  (TANPA MP3 PLAYER - WITH MULTI WIFI DISPLAY & CONTINUOUS LOOP)
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

// WiFi Utama & Alternatif
const char* WIFI_SSID         = "PembdaLINK";
const char* WIFI_PASSWORD     = "PEMBDA2026";

const char* WIFI_ALT_SSID     = "Xspace";
const char* WIFI_ALT_PASSWORD = "12345678starlink";

const char* WIFI_ALT2_SSID    = "TEFA";
const char* WIFI_ALT2_PASSWORD= "PEMBDA2026";

const char* WIFI_ALT3_SSID    = "VISTAFAMILY";
const char* WIFI_ALT3_PASSWORD= "pelita31";

// Struktur daftar WiFi untuk pencarian berulang di LCD
struct WiFiCredential {
  const char* ssid;
  const char* password;
};

const WiFiCredential wifiList[] = {
  { WIFI_SSID,      WIFI_PASSWORD },
  { WIFI_ALT_SSID,  WIFI_ALT_PASSWORD },
  { WIFI_ALT2_SSID, WIFI_ALT2_PASSWORD },
  { WIFI_ALT3_SSID, WIFI_ALT3_PASSWORD }
};
const int NUM_WIFI = sizeof(wifiList) / sizeof(wifiList[0]);

// Server API PembdaHUB
const char* SERVER_URL        = "https://perguruanpembda.com/api/attendance/rfid-scan";
const char* KIOSK_API_KEY     = "RAHASIA-PEMBDAHUB-12345";

// Device ID Unik Per Station
const char* DEVICE_ID         = "STATION-SMK-01";

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

MFRC522           rfid(RFID_SS_PIN, RFID_RST_PIN);
LiquidCrystal_I2C lcd(LCD_ADDRESS, LCD_COLS, LCD_ROWS);
ESP8266WiFiMulti  wifiMulti;

// ============================================================
//  PROTOTIPE FUNGSI
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
//  INDIKATOR BUZZER & 2 LED
// ============================================================

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

void indicatorCooldown() {
  digitalWrite(LED_RED_PIN, LOW);
  digitalWrite(LED_GREEN_PIN, HIGH);
  digitalWrite(BUZZER_PIN, HIGH);
  delay(400);
  digitalWrite(LED_GREEN_PIN, LOW);
  digitalWrite(BUZZER_PIN, LOW);
}

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

void indicatorFail() {
  digitalWrite(LED_GREEN_PIN, LOW);
  digitalWrite(LED_RED_PIN, HIGH);
  digitalWrite(BUZZER_PIN, HIGH);
  delay(700);
  digitalWrite(LED_RED_PIN, LOW);
  digitalWrite(BUZZER_PIN, LOW);
}

// ============================================================
//  KONEKSI WIFI (LOOP PENCARIAN CONTINUOUS + TAMPIL SSID DI LCD)
// ============================================================
void connectWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.persistent(false); 

  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("MENCARI WIFI... "));

  int apIndex = 0;

  // Ulangi terus tanpa batas sampai salah satu WiFi terhubung!
  while (wifiMulti.run() != WL_CONNECTED) {
    const char* currentSSID = wifiList[apIndex].ssid;
    
    // Tampilkan nama SSID yang sedang dicari di baris 1
    String line0 = "Cari:" + String(currentSSID);
    while (line0.length() < 16) line0 += " ";
    if (line0.length() > 16) line0 = line0.substring(0, 16);

    lcd.setCursor(0, 0); lcd.print(line0);

    // Animasi titik-titik (....) di baris 2 sambil mencoba koneksi
    for (int dot = 0; dot < 4; dot++) {
      if (wifiMulti.run() == WL_CONNECTED) break;

      String line1 = "Menghubungkan";
      for (int d = 0; d <= dot; d++) line1 += ".";
      while (line1.length() < 16) line1 += " ";

      lcd.setCursor(0, 1); lcd.print(line1);
      delay(400);
    }

    // Pindah ke SSID berikutnya dalam daftar jika belum terhubung
    apIndex = (apIndex + 1) % NUM_WIFI;
  }

  // Jika WiFi sudah berhasil terhubung
  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("WIFI CONNECTED! "));
  
  String ipStr = WiFi.localIP().toString();
  while (ipStr.length() < 16) ipStr += " ";
  lcd.setCursor(0, 1); lcd.print(ipStr);
  
  // Indikator LED Hijau & Beep 2x
  for (int i = 0; i < 2; i++) {
    digitalWrite(LED_GREEN_PIN, HIGH);
    digitalWrite(BUZZER_PIN, HIGH);
    delay(100);
    digitalWrite(LED_GREEN_PIN, LOW);
    digitalWrite(BUZZER_PIN, LOW);
    if (i < 1) delay(100);
  }
  delay(1200);
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
  Serial.println(F("\n=== NODEMCU STATION BOOTING (4 WIFI + 2 LED + NO MP3) ==="));
  Serial.println(F("Board: NodeMCU V3 (ESP-12F) - 16x2 + 2 LED + Buzzer"));
  Serial.print(F("Device ID: ")); Serial.println(DEVICE_ID);

  pinMode(LED_GREEN_PIN, OUTPUT);
  pinMode(LED_RED_PIN, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT);

  digitalWrite(LED_GREEN_PIN, LOW);
  digitalWrite(LED_RED_PIN, LOW);
  digitalWrite(BUZZER_PIN, LOW);

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

  // Tambahkan semua 4 WiFi AP ke wifiMulti
  for (int i = 0; i < NUM_WIFI; i++) {
    wifiMulti.addAP(wifiList[i].ssid, wifiList[i].password);
  }

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

  // Anti double-tap
  unsigned long now = millis();
  if (uid == lastUID && (now - lastTapTime) < SCAN_COOLDOWN_MS) {
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
    return;
  }
  lastUID     = uid;
  lastTapTime = now;

  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("MEMPROSES...    "));
  lcd.setCursor(0, 1); lcd.print("ID:" + uid);
  
  digitalWrite(BUZZER_PIN, HIGH);
  delay(80);
  digitalWrite(BUZZER_PIN, LOW);

  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();

  sendToServer(uid, "rfid");
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
