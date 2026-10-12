// ==============================================================================
//  PEMBDAHUB ATTENDANCE STATION - FIRMWARE UPDATE 2026
//  STATION ID      : STATION-SMK-06
//  UNIT SEKOLAH    : SMK Swasta Pembda
//  MIKROKONTROLER  : NodeMCU V3 (ESP8266 ESP-12F)
//  LAYAR           : LCD 16x2 I2C (Alamat 0x27 / 0x3F) - Bisa diubah ke 20x4
//  AUDIO / SUARA   : DFPlayer Mini MP3 Player (SoftwareSerial TX=D4/GPIO2)
//  PEMINDAI QR     : GM65 Barcode/QR Scanner (SoftwareSerial RX=D3/GPIO0)
//  PEMINDAI RFID   : RFID RC522 (HSPI NodeMCU: D0=CS, D5=SCK, D7=MOSI, D6=MISO)
//  INDIKATOR       : Buzzer Aktif (D8/GPIO15)
// ==============================================================================
//
//  PENINGKATAN FITUR (UPDATE 2026):
//  1. WiFi Anti-Sleep: WiFi.setSleepMode(WIFI_NONE_SLEEP) - Cegah koneksi drop.
//  2. Auto-Reboot Watchdog: Jika WiFi putus > 3 menit, sistem auto-restart (simulasi cabut-colok).
//  3. Heartbeat Otomatis (45s): Melaporkan IP, sinyal RSSI, dan status online ke server.
//  4. Tampilan Simetris: Helper printCenter() & printRow() merapikan teks di LCD.
//  5. Layar Siaga Berganti (Slide Carousel 5s): Menggantikan running text yang buram/ghosting.
//
// ==============================================================================

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

// ==============================================================================
//  KONFIGURASI IDENTITAS & JARINGAN
// ==============================================================================
const char* DEVICE_ID           = "STATION-SMK-06";
const char* UNIT_NAME           = "SMK Swasta Pembda";
const char* KIOSK_API_KEY       = "RAHASIA-PEMBDAHUB-12345";

// Prioritas 1: Server Lokal LAN Sekolah (Respon kilat < 200ms)
const char* SERVER_LOCAL_URL    = "http://50.35.89.10/api/attendance/rfid-scan";
const char* SCAN_BUFFER_LOCAL   = "http://50.35.89.10/api/rfid/scan-buffer";
const char* HEARTBEAT_LOCAL     = "http://50.35.89.10/api/attendance/heartbeat";

// Prioritas 2: Server Cloud Production (Failover Internet / Hosting)
const char* SERVER_CLOUD_URL    = "https://perguruanpembda.com/api/attendance/rfid-scan";
const char* SCAN_BUFFER_CLOUD   = "https://perguruanpembda.com/api/rfid/scan-buffer";
const char* HEARTBEAT_CLOUD     = "https://perguruanpembda.com/api/attendance/heartbeat";

// Profil WiFi Multi-AP (Auto-Failover)
const char* WIFI_SSID           = "PembdaLINK";
const char* WIFI_PASSWORD       = "PEMBDA2026";

const char* WIFI_ALT1_SSID      = "Xspace";
const char* WIFI_ALT1_PASSWORD  = "12345678starlink";

const char* WIFI_ALT2_SSID      = "TEFA";
const char* WIFI_ALT2_PASSWORD  = "PEMBDA2026";

const char* WIFI_ALT3_SSID      = "VISTAFAMILY";
const char* WIFI_ALT3_PASSWORD  = "pelita31";

const char* WIFI_ALT4_SSID      = "PembdaNET";
const char* WIFI_ALT4_PASSWORD  = "pelita31";

// ==============================================================================
//  PIN DEFINITIONS & HARDWARE MAPPING (NodeMCU ESP8266)
// ==============================================================================
#define RFID_SS_PIN     16      // D0 (GPIO16) - Chip Select RC522
#define RFID_RST_PIN   255      // Reset terhubung langsung ke 3.3V
#define MP3_TX_PIN       2      // D4 (GPIO2)  - TX NodeMCU -> RX DFPlayer via 1k Ohm
#define MP3_VOLUME      30      // Volume Speaker DFPlayer (0 - 30)

#define BUZZER_PIN      15      // D8 (GPIO15) - Buzzer Aktif
#define QR_RX_PIN        0      // D3 (GPIO0)  - RX GM65 Barcode/QR Scanner (atau LED jika tdk ada QR)

#define LCD_ADDRESS   0x27      // Alamat I2C LCD (0x27 atau 0x3F)
#define LCD_COLS        16      // 16 untuk LCD 16x2, ganti 20 jika pakai LCD 20x4
#define LCD_ROWS         2      // 2 untuk LCD 16x2, ganti 4 jika pakai LCD 20x4

#define HTTP_TIMEOUT          8000
#define DISPLAY_RESULT_MS     3500
#define SCAN_COOLDOWN_MS      3000
#define WATCHDOG_TIMEOUT_MS 180000  // 3 Menit WiFi putus -> ESP.restart()
#define HEARTBEAT_INTERVAL   45000  // 45 Detik kirim denyut jantung
#define SLIDE_INTERVAL        5000  // 5 Detik per halaman carousel

// ==============================================================================
//  GLOBAL OBJECTS & STATE
// ==============================================================================
MFRC522           rfid(RFID_SS_PIN, RFID_RST_PIN);
LiquidCrystal_I2C lcd(LCD_ADDRESS, LCD_COLS, LCD_ROWS);
ESP8266WiFiMulti  wifiMulti;
SoftwareSerial    mp3Serial(-1, MP3_TX_PIN);
SoftwareSerial    qrSerial(QR_RX_PIN, -1);

String        lastUID               = "";
unsigned long lastTapTime           = 0;
unsigned long lastWiFiCheck         = 0;
unsigned long lastHeartbeatTime     = 0;
unsigned long lastSlideChangeTime   = 0;
unsigned long lastActivityTime      = 0;
unsigned long wifiDisconnectedSince = 0;

bool isOnline        = false;
bool isShowingResult = false;
int  currentSlide    = 0;

// Karakter Kustom LCD (5x8 Dots)
byte iconWifi[8] = { B00000, B01110, B10001, B00100, B01010, B00000, B00100, B00000 };
byte iconCard[8] = { B11111, B10001, B10101, B10001, B11111, B00000, B00000, B00000 };

// Prototype Fungsi
void printCenter(uint8_t row, const String& text);
void printRow(uint8_t row, const String& text);
void showSlide(int slideIndex);
void connectWiFiInitial();
void handleRfidScan();
void handleQrScan();
void sendToServer(String uid, String type);
void sendScanBuffer(String uid);
void sendHeartbeat();
void parseAndDisplay(String json, String uid);
void showError(String msg);
void beep(int count, int duration);
void playAudio(uint8_t folder, uint8_t track);
void setMp3Volume(uint8_t vol);
String getRfidUID();

// ==============================================================================
//  SETUP
// ==============================================================================
void setup() {
  Serial.begin(115200);
  delay(100);
  Serial.println(F("\n=============================================="));
  Serial.print(F("PEMBDAHUB STATION STARTING: "));
  Serial.println(DEVICE_ID);
  Serial.println(F("=============================================="));

  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);

  // Inisialisasi SoftwareSerial DFPlayer & QR Scanner
  mp3Serial.begin(9600);
  qrSerial.begin(9600);
  delay(200);
  setMp3Volume(MP3_VOLUME);

  // Inisialisasi I2C Wire untuk LCD (D2=SDA, D1=SCL)
  Wire.begin(4, 5);
  lcd.init();
  lcd.backlight();
  lcd.createChar(0, iconWifi);
  lcd.createChar(1, iconCard);

  // Layar Sambutan Awal
  lcd.clear();
  printCenter(0, "PEMBDA PRESENSI");
  printCenter(1, "Memulai...");
  beep(1, 80);

  // Inisialisasi SPI & RFID RC522
  SPI.begin();
  rfid.PCD_Init();
  delay(50);
  rfid.PCD_Reset();
  delay(50);
  rfid.PCD_Init();
  delay(50);

  // Konfigurasi WiFi Anti-Sleep (Krusial agar WiFi tidak drop setelah nyala lama)
  WiFi.mode(WIFI_STA);
  WiFi.setSleepMode(WIFI_NONE_SLEEP);
  WiFi.setAutoReconnect(true);

  // Daftarkan SSID Jaringan ke Multi-AP
  wifiMulti.addAP(WIFI_SSID, WIFI_PASSWORD);
  wifiMulti.addAP(WIFI_ALT1_SSID, WIFI_ALT1_PASSWORD);
  wifiMulti.addAP(WIFI_ALT2_SSID, WIFI_ALT2_PASSWORD);
  wifiMulti.addAP(WIFI_ALT3_SSID, WIFI_ALT3_PASSWORD);
  wifiMulti.addAP(WIFI_ALT4_SSID, WIFI_ALT4_PASSWORD);

  connectWiFiInitial();

  lastSlideChangeTime = millis();
  showSlide(0);
}

// ==============================================================================
//  LOOP
// ==============================================================================
void loop() {
  unsigned long now = millis();

  // 1. Pemeriksaan Status WiFi Non-Blocking (Setiap 5 detik)
  if (now - lastWiFiCheck >= 5000) {
    lastWiFiCheck = now;
    if (wifiMulti.run() == WL_CONNECTED) {
      if (!isOnline) {
        isOnline = true;
        wifiDisconnectedSince = 0;
        if (!isShowingResult) showSlide(currentSlide);
      }
    } else {
      if (isOnline) isOnline = false;
      if (wifiDisconnectedSince == 0) {
        wifiDisconnectedSince = now;
      } else if (now - wifiDisconnectedSince >= WATCHDOG_TIMEOUT_MS) {
        // Watchdog Reboot Otomatis jika offline > 3 menit
        lcd.clear();
        printCenter(0, "WATCHDOG REBOOT");
        printCenter(1, "Restart...");
        delay(1500);
        ESP.restart();
      }
    }
  }

  // 2. Heartbeat Denyut Jantung IoT setiap 45 detik
  if (isOnline && (now - lastHeartbeatTime >= HEARTBEAT_INTERVAL || lastHeartbeatTime == 0)) {
    lastHeartbeatTime = now;
    sendHeartbeat();
  }

  // 3. Carousel Layar Siaga Berganti (Slide Carousel 5s)
  if (!isShowingResult) {
    if (now - lastSlideChangeTime >= SLIDE_INTERVAL) {
      lastSlideChangeTime = now;
      currentSlide = (currentSlide + 1) % 3;
      showSlide(currentSlide);
    }
  }

  // 4. Deteksi Pemindaian RFID & QR Code
  handleRfidScan();
  handleQrScan();

  delay(20);
}

// ==============================================================================
//  HELPER DISPLAY SIMETRIS
// ==============================================================================
void printCenter(uint8_t row, const String& text) {
  if (row >= LCD_ROWS) return;
  lcd.setCursor(0, row);
  String trimmed = text;
  if (trimmed.length() > LCD_COLS) trimmed = trimmed.substring(0, LCD_COLS);
  int padLeft = (LCD_COLS - trimmed.length()) / 2;
  int padRight = LCD_COLS - trimmed.length() - padLeft;
  String line = "";
  for (int i = 0; i < padLeft; i++) line += " ";
  line += trimmed;
  for (int i = 0; i < padRight; i++) line += " ";
  lcd.print(line);
}

void printRow(uint8_t row, const String& text) {
  if (row >= LCD_ROWS) return;
  lcd.setCursor(0, row);
  String trimmed = text;
  if (trimmed.length() > LCD_COLS) trimmed = trimmed.substring(0, LCD_COLS);
  int padRight = LCD_COLS - trimmed.length();
  String line = trimmed;
  for (int i = 0; i < padRight; i++) line += " ";
  lcd.print(line);
}

void showSlide(int slideIndex) {
  if (isShowingResult) return;

  switch (slideIndex) {
    case 0:
      // Slide 1: Siap Scan Utama
      printCenter(0, "PEMBDA PRESENSI");
      if (isOnline) {
        printCenter(1, "TEMPEL KARTU");
      } else {
        printCenter(1, "[OFFLINE]");
      }
      break;

    case 1:
      // Slide 2: Identitas Unit Sekolah & Slogan
      printCenter(0, UNIT_NAME);
      printCenter(1, "Maju Terus!");
      break;

    case 2:
      // Slide 3: Status Jaringan WiFi & IP
      if (isOnline) {
        printRow(0, "WiFi:" + WiFi.SSID().substring(0, 11));
        printRow(1, "IP:" + WiFi.localIP().toString());
      } else {
        printCenter(0, "MENCARI WIFI...");
        printCenter(1, "Hubungkan AP");
      }
      break;
  }
}

void connectWiFiInitial() {
  lcd.clear();
  printCenter(0, "Koneksi WiFi...");
  printCenter(1, "Mencari AP...");
  int attempt = 0;
  while (wifiMulti.run() != WL_CONNECTED && attempt < 20) {
    delay(300);
    attempt++;
  }
  isOnline = (WiFi.status() == WL_CONNECTED);
}

// ==============================================================================
//  PEMINDAIAN RFID & QR CODE
// ==============================================================================
void handleRfidScan() {
  if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) return;

  String uid = getRfidUID();
  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();

  if (uid == "") return;

  unsigned long now = millis();
  if (uid == lastUID && (now - lastTapTime < SCAN_COOLDOWN_MS)) {
    Serial.println(F("[RFID] Cooldown double-tap. Abaikan."));
    return;
  }

  lastUID = uid;
  lastTapTime = now;

  isShowingResult = true;
  lcd.clear();
  printCenter(0, "MEMPROSES...");
  printCenter(1, "UID: " + uid);
  beep(1, 100);

  sendToServer(uid, "rfid");
}

void handleQrScan() {
  if (!qrSerial.available()) return;

  String qrData = "";
  unsigned long startWait = millis();
  while (millis() - startWait < 150) {
    while (qrSerial.available()) {
      char c = qrSerial.read();
      if (c == '\r' || c == '\n') {
        if (qrData.length() >= 3) break;
      } else if (c >= 32 && c <= 126) {
        qrData += c;
      }
      delay(2);
    }
    if (qrData.length() >= 3 && !qrSerial.available()) break;
  }
  qrData.trim();

  if (qrData.length() < 3) return;

  unsigned long now = millis();
  if (qrData == lastUID && (now - lastTapTime < SCAN_COOLDOWN_MS)) {
    Serial.println(F("[QR] Cooldown scan. Abaikan."));
    return;
  }

  lastUID = qrData;
  lastTapTime = now;

  isShowingResult = true;
  lcd.clear();
  printCenter(0, "MEMPROSES QR...");
  printCenter(1, qrData.substring(0, min((int)qrData.length(), 16)));
  beep(1, 100);

  sendToServer(qrData, "qr");
}

String getRfidUID() {
  String uid = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    if (rfid.uid.uidByte[i] < 0x10) uid += "0";
    uid += String(rfid.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();
  return uid;
}

// ==============================================================================
//  KOMUNIKASI SERVER & PARSING HASIL
// ==============================================================================
void sendToServer(String uid, String type) {
  StaticJsonDocument<256> reqDoc;
  reqDoc["uid"]       = uid;
  reqDoc["device_id"] = DEVICE_ID;
  reqDoc["type"]      = type;

  String body;
  serializeJson(reqDoc, body);

  bool success = false;
  String payload = "";
  int httpCode = -1;

  // Percobaan 1: Server Lokal LAN
  if (WiFi.SSID() == WIFI_SSID) {
    WiFiClient client;
    HTTPClient http;
    http.begin(client, SERVER_LOCAL_URL);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("X-Kiosk-API-Key", KIOSK_API_KEY);
    http.setTimeout(HTTP_TIMEOUT);

    httpCode = http.POST(body);
    if (httpCode > 0) {
      payload = http.getString();
      success = true;
    }
    http.end();
  }

  // Percobaan 2: Server Cloud Failover jika lokal gagal
  if (!success) {
    BearSSL::WiFiClientSecure secureClient;
    secureClient.setInsecure();
    HTTPClient http;
    http.begin(secureClient, SERVER_CLOUD_URL);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("X-Kiosk-API-Key", KIOSK_API_KEY);
    http.setTimeout(HTTP_TIMEOUT);

    httpCode = http.POST(body);
    if (httpCode > 0) {
      payload = http.getString();
      success = true;
    }
    http.end();
  }

  if (success) {
    parseAndDisplay(payload, uid);
  } else if (httpCode == 404) {
    playAudio(1, 6);
    showError("Tdk Terdaftar");
  } else {
    playAudio(1, 7);
    showError("Server Err " + String(httpCode));
  }
}

void parseAndDisplay(String json, String uid) {
  StaticJsonDocument<512> doc;
  DeserializationError err = deserializeJson(doc, json);
  if (err) {
    showError("Format Err");
    return;
  }

  String status      = doc["status"] | "";
  String message     = doc["message"] | "";
  String action_code = doc["action_code"] | "";

  const char* rawNama = doc["nama"];
  if (!rawNama) rawNama = doc["data"]["nama"];
  if (!rawNama) rawNama = doc["data"]["name"];
  String nama = rawNama ? rawNama : "";

  const char* rawKelas = doc["kelas"];
  if (!rawKelas) rawKelas = doc["data"]["kelas"];
  String kelas = rawKelas ? rawKelas : "";

  const char* rawWaktu = doc["waktu"];
  if (!rawWaktu) rawWaktu = doc["data"]["waktu"];
  if (!rawWaktu) rawWaktu = doc["data"]["time"];
  String waktu = rawWaktu ? rawWaktu : "";

  lcd.clear();
  if (status == "success" || status == "info") {
    if (action_code == "CHECK_IN") {
      printCenter(0, nama != "" ? nama : "* PRESENSI MASUK *");
      printCenter(1, "MSK " + waktu.substring(0, 5) + " WIB");
      if (kelas.indexOf("Guru") >= 0 || kelas.indexOf("Staf") >= 0) playAudio(1, 5);
      else playAudio(1, 2);
      beep(1, 120);
    } else if (action_code == "CHECK_OUT") {
      printCenter(0, nama != "" ? nama : "* PRESENSI PULANG *");
      printCenter(1, "PLG " + waktu.substring(0, 5) + " WIB");
      playAudio(1, 3);
      beep(2, 80);
    } else if (action_code == "COOLDOWN") {
      printCenter(0, nama != "" ? nama : "SUDAH ABSEN");
      printCenter(1, "Jam: " + waktu.substring(0, 5) + " WIB");
      playAudio(1, 4);
      beep(1, 200);
    } else if (action_code == "NEW_CARD") {
      sendScanBuffer(uid);
      printCenter(0, "* KARTU BARU *");
      printCenter(1, "Daftar di Admin");
      playAudio(1, 6);
      beep(3, 100);
    } else {
      printCenter(0, nama != "" ? nama : "* BERHASIL *");
      printCenter(1, message);
      playAudio(1, 2);
      beep(1, 100);
    }
  } else {
    printCenter(0, "! PERINGATAN !");
    printCenter(1, message);
    playAudio(1, 7);
    beep(2, 250);
  }

  delay(DISPLAY_RESULT_MS);
  isShowingResult = false;
  lastSlideChangeTime = millis();
  showSlide(currentSlide);
}

void sendScanBuffer(String uid) {
  StaticJsonDocument<128> doc;
  doc["uid"]       = uid;
  doc["device_id"] = DEVICE_ID;
  String body;
  serializeJson(doc, body);

  BearSSL::WiFiClientSecure secureClient;
  secureClient.setInsecure();
  HTTPClient http;
  http.begin(secureClient, SCAN_BUFFER_CLOUD);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-Kiosk-API-Key", KIOSK_API_KEY);
  http.setTimeout(3000);
  http.POST(body);
  http.end();
}

void sendHeartbeat() {
  StaticJsonDocument<128> doc;
  doc["device_id"] = DEVICE_ID;
  doc["rssi"]      = WiFi.RSSI();
  doc["free_heap"] = ESP.getFreeHeap();
  doc["ip"]        = WiFi.localIP().toString();
  String body;
  serializeJson(doc, body);

  WiFiClient client;
  HTTPClient http;
  http.begin(client, HEARTBEAT_LOCAL);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-Kiosk-API-Key", KIOSK_API_KEY);
  http.setTimeout(3000);
  int code = http.POST(body);
  http.end();

  if (code <= 0) {
    BearSSL::WiFiClientSecure secureClient;
    secureClient.setInsecure();
    http.begin(secureClient, HEARTBEAT_CLOUD);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("X-Kiosk-API-Key", KIOSK_API_KEY);
    http.setTimeout(3000);
    http.POST(body);
    http.end();
  }
}

void showError(String msg) {
  lcd.clear();
  printCenter(0, "! GAGAL !");
  printCenter(1, msg);
  beep(3, 150);
  delay(DISPLAY_RESULT_MS);
  isShowingResult = false;
  lastSlideChangeTime = millis();
  showSlide(currentSlide);
}

void beep(int count, int duration) {
  for (int i = 0; i < count; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(duration);
    digitalWrite(BUZZER_PIN, LOW);
    if (i < count - 1) delay(80);
  }
}

void playAudio(uint8_t folder, uint8_t track) {
  byte cmd[10] = { 0x7E, 0xFF, 0x06, 0x0F, 0x00, folder, track, 0x00, 0x00, 0xEF };
  uint16_t checksum = 0;
  for (int i = 1; i < 7; i++) checksum += cmd[i];
  checksum = -checksum;
  cmd[7] = (byte)(checksum >> 8);
  cmd[8] = (byte)(checksum & 0xFF);
  for (int i = 0; i < 10; i++) mp3Serial.write(cmd[i]);
}

void setMp3Volume(uint8_t vol) {
  if (vol > 30) vol = 30;
  byte cmd[10] = { 0x7E, 0xFF, 0x06, 0x06, 0x00, 0x00, vol, 0x00, 0x00, 0xEF };
  uint16_t checksum = 0;
  for (int i = 1; i < 7; i++) checksum += cmd[i];
  checksum = -checksum;
  cmd[7] = (byte)(checksum >> 8);
  cmd[8] = (byte)(checksum & 0xFF);
  for (int i = 0; i < 10; i++) mp3Serial.write(cmd[i]);
}
