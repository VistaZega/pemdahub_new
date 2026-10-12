// ==============================================================================
//  PEMBDAHUB ATTENDANCE STATION - FIRMWARE RESMI TERSTANDARISASI 2026
//  STATION ID      : STATION-SMK-02
//  UNIT SEKOLAH    : SMKS Pembda Nias
//  MIKROKONTROLER  : NodeMCU V3 (ESP8266 ESP-12F)
//  LAYAR           : LCD 16x2 I2C (Alamat 0x27 / 0x3F)
//  AUDIO / SUARA   : DFPlayer Mini MP3 Player + Buzzer Aktif
//  PEMINDAI        : RFID RC522 (13.56 MHz)
// ==============================================================================
//  STANDAR FITUR PEMBDAHUB:
//  1. Prioritas Mutlak LAN: Konek ke PembdaLINK dahulu + Auto-Switchback 90s.
//  2. Dual-Heartbeat (45s): Lapor SSID, IP, RSSI, RAM, Uptime ke /stations.
//  3. Tampilan Simetris Penuh: Menggunakan seluruh 2 baris x 16 kolom.
//  4. Animasi In-Place: Teks berganti di tempat (tanpa running text buram).
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
const char* DEVICE_ID           = "STATION-SMK-02";
const char* UNIT_NAME           = "SMKS Pembda Nias";
const char* KIOSK_API_KEY       = "RAHASIA-PEMBDAHUB-12345";

// Prioritas 1: Server Lokal LAN Sekolah (Respon kilat < 200ms)
const char* SERVER_LOCAL_URL    = "http://50.35.89.10/api/attendance/rfid-scan";
const char* SCAN_BUFFER_LOCAL   = "http://50.35.89.10/api/rfid/scan-buffer";
const char* HEARTBEAT_LOCAL     = "http://50.35.89.10/api/attendance/heartbeat";

// Prioritas 2: Server Cloud Production (Failover Internet / Hosting)
const char* SERVER_CLOUD_URL    = "https://perguruanpembda.com/api/attendance/rfid-scan";
const char* SCAN_BUFFER_CLOUD   = "https://perguruanpembda.com/api/rfid/scan-buffer";
const char* HEARTBEAT_CLOUD     = "https://perguruanpembda.com/api/attendance/heartbeat";

// Profil WiFi Multi-AP Resmi (Prioritas 1: LAN PembdaLINK)
const char* WIFI_SSID           = "PembdaLINK";
const char* WIFI_PASSWORD       = "PEMBDA2026";

const char* WIFI_ALT1_SSID      = "PembdaNET";
const char* WIFI_ALT1_PASSWORD  = "pelita31";

const char* WIFI_ALT2_SSID      = "PembdaSTARGATE";
const char* WIFI_ALT2_PASSWORD  = "";

const char* WIFI_ALT3_SSID      = "VISTAFAMILY";
const char* WIFI_ALT3_PASSWORD  = "pelita31";

const char* WIFI_ALT4_SSID      = "Black_Hole";
const char* WIFI_ALT4_PASSWORD  = "pelita31";

// ==============================================================================
//  PIN DEFINITIONS & HARDWARE MAPPING (NodeMCU V3 (ESP8266 ESP-12F))
// ==============================================================================
#define RFID_SS_PIN     16      // D0 (GPIO16) - Chip Select RC522
#define RFID_RST_PIN   255      // Reset terhubung ke 3.3V
#define MP3_TX_PIN       2      // D4 (GPIO2)  - TX NodeMCU -> RX DFPlayer via 1k Ohm
#define MP3_VOLUME      30      // Volume Speaker DFPlayer (0 - 30)
#define BUZZER_PIN      15      // D8 (GPIO15) - Buzzer Aktif
#define LED_PIN          0      // D3 (GPIO0)  - LED Indikator

#define LCD_ADDRESS   0x27
#define LCD_COLS        16
#define LCD_ROWS         2

#define HTTP_TIMEOUT          8000
#define DISPLAY_RESULT_MS     3500
#define SCAN_COOLDOWN_MS      3000
#define WATCHDOG_TIMEOUT_MS 180000  // 3 Menit WiFi putus -> Auto-Reboot
#define HEARTBEAT_INTERVAL   45000  // 45 Detik kirim telemetri ke /stations
#define SLIDE_INTERVAL        5000  // 5 Detik per halaman carousel

// ==============================================================================
//  GLOBAL OBJECTS & STATE
// ==============================================================================
MFRC522           rfid(RFID_SS_PIN, RFID_RST_PIN);
LiquidCrystal_I2C lcd(LCD_ADDRESS, LCD_COLS, LCD_ROWS);
ESP8266WiFiMulti  wifiMulti;
SoftwareSerial    mp3Serial(-1, MP3_TX_PIN);

String        lastUID               = "";
unsigned long lastTapTime           = 0;
unsigned long lastWiFiCheck         = 0;
unsigned long lastHeartbeatTime     = 0;
unsigned long lastSlideChangeTime   = 0;
unsigned long lastInPlaceAnimTime   = 0;
unsigned long wifiDisconnectedSince = 0;
unsigned long lastPrimaryLanCheck   = 0;

bool isOnline        = false;
bool isShowingResult = false;
int  currentSlide    = 0;
int  inPlaceFrame    = 0;

// Prototype Fungsi
void printCenter(uint8_t row, const String& text);
void printRow(uint8_t row, const String& text);
void showSlide(int slideIndex);
void updateInPlaceAnim();
void connectWiFiInitial();
void handleRfidScan();
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
  delay(300);

  Serial.println(F("\n========================================================"));
  Serial.print  (F("  STATION ID   : ")); Serial.println(DEVICE_ID);
  Serial.print  (F("  UNIT SEKOLAH : ")); Serial.println(UNIT_NAME);
  Serial.println(F("========================================================"));

  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);
  pinMode(LED_PIN, OUTPUT);
  digitalWrite(LED_PIN, LOW);

  Wire.begin(4, 5); // D2=SDA, D1=SCL
  Wire.setClock(400000);
#ifdef FDB_LIQUID_CRYSTAL_I2C_H
  lcd.begin();
#else
  lcd.init();
#endif
  lcd.backlight();

  lcd.clear();
  printCenter(0, "SMKS PEMBDA NIAS");
  printCenter(1, "[STATION-SMK-02]");
  beep(1, 100);
  delay(1200);

  SPI.begin();
  SPI.setFrequency(1000000);
  delay(50);
  rfid.PCD_Init();
  delay(50);
  rfid.PCD_SetAntennaGain(rfid.RxGain_max);
  rfid.PCD_AntennaOn();

  WiFi.mode(WIFI_STA);
  WiFi.setSleepMode(WIFI_NONE_SLEEP);
  WiFi.persistent(false);
  WiFi.setAutoReconnect(true);

  wifiMulti.addAP(WIFI_SSID,      WIFI_PASSWORD);
  wifiMulti.addAP(WIFI_ALT1_SSID, WIFI_ALT1_PASSWORD);
  wifiMulti.addAP(WIFI_ALT2_SSID, WIFI_ALT2_PASSWORD);
  wifiMulti.addAP(WIFI_ALT3_SSID, WIFI_ALT3_PASSWORD);
  wifiMulti.addAP(WIFI_ALT4_SSID, WIFI_ALT4_PASSWORD);

  connectWiFiInitial();

  mp3Serial.begin(9600);
  delay(300);
  while (mp3Serial.available()) mp3Serial.read();
  setMp3Volume(MP3_VOLUME);
  delay(100);
  playAudio(1, 1);

  lastSlideChangeTime = millis();
  showSlide(0);
}

// ==============================================================================
//  LOOP UTAMA
// ==============================================================================
void loop() {
  unsigned long now = millis();

  // 1. Pemeriksaan Koneksi WiFi setiap 10 detik
  if (now - lastWiFiCheck >= 10000 || lastWiFiCheck == 0) {
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
        lcd.clear();
        printCenter(0, "WATCHDOG REBOOT!");
        printCenter(1, "Restart Sistem..");
        delay(1500);
        ESP.restart();
      }
    }
  }

  // 2. Auto-Switchback ke Prioritas Utama LAN (PembdaLINK) setiap 90 detik jika sedang di WiFi Cadangan
  if (isOnline && !isShowingResult && WiFi.SSID() != WIFI_SSID && (now - lastPrimaryLanCheck >= 90000)) {
    lastPrimaryLanCheck = now;
    int n = WiFi.scanNetworks(false, false);
    for (int i = 0; i < n; i++) {
      if (WiFi.SSID(i) == WIFI_SSID && WiFi.RSSI(i) > -85) {
        WiFi.disconnect();
        delay(50);
        WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
        int waitLan = 0;
        while (WiFi.status() != WL_CONNECTED && waitLan < 20) {
          delay(250);
          waitLan++;
        }
        if (!isShowingResult) showSlide(currentSlide);
        break;
      }
    }
    WiFi.scanDelete();
  }

  // 3. Heartbeat Telemetri IoT setiap 45 detik (Lokal + Cloud /stations)
  if (isOnline && (now - lastHeartbeatTime >= HEARTBEAT_INTERVAL || lastHeartbeatTime == 0)) {
    lastHeartbeatTime = now;
    sendHeartbeat();
  }

  // 4. Carousel Layar Siaga (5 detik) & Animasi Huruf Berganti In-Place (1.5 detik)
  if (!isShowingResult) {
    if (now - lastSlideChangeTime >= SLIDE_INTERVAL) {
      lastSlideChangeTime = now;
      currentSlide = (currentSlide + 1) % 3;
      showSlide(currentSlide);
    } else {
      updateInPlaceAnim();
    }
  }

  // 5. Deteksi Pemindaian RFID & QR Code
  handleRfidScan();
  delay(20);
}

// ==============================================================================
//  HELPER DISPLAY SIMETRIS & ANIMASI IN-PLACE (LCD 16x2)
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

void updateInPlaceAnim() {
  if (isShowingResult) return;
  unsigned long now = millis();
  if (now - lastInPlaceAnimTime < 1500) return;
  lastInPlaceAnimTime = now;
  inPlaceFrame = (inPlaceFrame + 1) % 2;

  if (currentSlide == 0) {
    if (isOnline) {
      if (inPlaceFrame == 0) printCenter(1, "> TEMPEL KARTU <");
      else printCenter(1, "* SILAKAN SCAN *");
    }
  } else if (currentSlide == 1) {
    if (inPlaceFrame == 0) printCenter(1, "[STATION-SMK-02]");
    else printCenter(1, "*PANTANG MUNDUR*");
  }
}

void showSlide(int slideIndex) {
  if (isShowingResult) return;
  switch (slideIndex) {
    case 0:
      // SIMETRIS TENGAH 16 KOLOM x 2 BARIS PENUH (Kiri = Kanan)
      printCenter(0, "PERGURUAN PEMBDA");
      if (isOnline) {
        if (inPlaceFrame == 0) printCenter(1, "> TEMPEL KARTU <");
        else printCenter(1, "* SILAKAN SCAN *");
      } else {
        printCenter(1, "[ MODE OFFLINE ]");
      }
      break;

    case 1:
      // SIMETRIS TENGAH 16 KOLOM x 2 BARIS PENUH (Identitas Unit & Station)
      printCenter(0, "SMKS PEMBDA NIAS");
      if (inPlaceFrame == 0) printCenter(1, "[STATION-SMK-02]");
      else printCenter(1, "*PANTANG MUNDUR*");
      break;

    case 2:
      // RAPAT KIRI SEMUA 16 KOLOM x 2 BARIS (Titik dua sejajar lurus di kolom 2)
      if (isOnline) {
        String ssid = WiFi.SSID();
        if (ssid.length() > 13) ssid = ssid.substring(0, 13);
        printRow(0, "AP:" + ssid);
        printRow(1, "IP:" + WiFi.localIP().toString().substring(0, 13));
      } else {
        printRow(0, "AP:TERPUTUS");
        printRow(1, "ST:MENCARI WIFI");
      }
      break;
  }
}

void connectWiFiInitial() {
  lcd.clear();
  printCenter(0, "PRIORITAS LAN...");
  printCenter(1, ">> PembdaLINK <<");

  // PRIORITAS 1 MUTLAK: Konek langsung ke PembdaLINK (LAN Lokal) terlebih dahulu (6 detik)
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  int attempt = 0;
  while (WiFi.status() != WL_CONNECTED && attempt < 20) {
    delay(300);
    attempt++;
  }

  // PRIORITAS 2 (FAILOVER): Hanya jika PembdaLINK tidak terjangkau, cari AP Alternatif
  if (WiFi.status() != WL_CONNECTED) {
    printCenter(0, "CARI WIFI ALT...");
    printCenter(1, "[MULTI-AP SIAGA]");
    attempt = 0;
    while (wifiMulti.run() != WL_CONNECTED && attempt < 15) {
      delay(300);
      attempt++;
    }
  }
  isOnline = (WiFi.status() == WL_CONNECTED);
}

// ==============================================================================
//  LOGIKA PEMINDAIAN & KOMUNIKASI SERVER
// ==============================================================================
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
  lastUID     = uid;
  lastTapTime = now;

  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();

  isShowingResult = true;
  digitalWrite(LED_PIN, HIGH);
  beep(1, 80);

  lcd.clear();
  printCenter(0, "*MEMPROSES DATA*");
  printCenter(1, uid);

  sendToServer(uid, "rfid");
  digitalWrite(LED_PIN, LOW);
}


void sendToServer(String uid, String type) {
  if (WiFi.status() != WL_CONNECTED) {
    playAudio(1, 7);
    showError("WiFi Terputus!");
    return;
  }

  StaticJsonDocument<128> doc;
  doc["uid"]       = uid;
  doc["type"]      = type;
  doc["device_id"] = DEVICE_ID;
  String body;
  serializeJson(doc, body);

  bool success = false;
  String payload = "";
  int httpCode = 0;

  // 1. Prioritas Utama: Server Lokal LAN (ketika terhubung ke PembdaLINK)
  if (WiFi.SSID() == WIFI_SSID) {
    WiFiClient client;
    HTTPClient http;
    http.begin(client, SERVER_LOCAL_URL);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("X-Kiosk-API-Key", KIOSK_API_KEY);
    http.addHeader("Accept", "application/json");
    http.setTimeout(2500);
    httpCode = http.POST(body);
    if (httpCode == 200 || httpCode == 201) {
      payload = http.getString();
      success = true;
    }
    http.end();
  }

  // 2. Failover Otomatis: Server Cloud HTTPS (perguruanpembda.com)
  if (!success) {
    BearSSL::WiFiClientSecure secureClient;
    secureClient.setInsecure();
    HTTPClient http;
    http.begin(secureClient, SERVER_CLOUD_URL);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("X-Kiosk-API-Key", KIOSK_API_KEY);
    http.addHeader("Accept", "application/json");
    http.setTimeout(HTTP_TIMEOUT);
    httpCode = http.POST(body);
    if (httpCode == 200 || httpCode == 201) {
      payload = http.getString();
      success = true;
    }
    http.end();
  }

  if (success) {
    parseAndDisplay(payload, uid);
  } else if (httpCode == 404) {
    playAudio(1, 6);
    showError("Kartu Tdk Drftr");
  } else {
    playAudio(1, 7);
    showError("Server Err " + String(httpCode));
  }
}

void parseAndDisplay(String json, String uid) {
  StaticJsonDocument<512> doc;
  DeserializationError err = deserializeJson(doc, json);
  if (err) {
    showError("Format Data Err");
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
      printCenter(0, nama != "" ? nama : "PRESENSI MASUK");
      printCenter(1, "MASUK: " + waktu.substring(0, 5) + " WIB");
      if (kelas.indexOf("Guru") >= 0 || kelas.indexOf("Staf") >= 0 || kelas.indexOf("Staff") >= 0) {
        playAudio(1, 5);
      } else {
        playAudio(1, 2);
      }
      beep(1, 120);
    } else if (action_code == "CHECK_OUT") {
      printCenter(0, nama != "" ? nama : "*ABSEN PULANG*");
      printCenter(1, "PULANG " + waktu.substring(0, 5) + " WIB");
      playAudio(1, 3);
      beep(2, 80);
    } else if (action_code == "COOLDOWN") {
      printCenter(0, nama != "" ? nama : "*SUDAH ABSEN!*");
      printCenter(1, "ABSEN: " + waktu.substring(0, 5) + " WIB");
      playAudio(1, 4);
      beep(1, 200);
    } else if (action_code == "NEW_CARD") {
      sendScanBuffer(uid);
      printCenter(0, "* KARTU BARU ! *");
      printCenter(1, "DAFTAR DI ADMIN!");
      playAudio(1, 6);
      beep(3, 100);
    } else {
      printCenter(0, nama != "" ? nama : "** BERHASIL **");
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
  StaticJsonDocument<256> doc;
  doc["device_id"] = DEVICE_ID;
  doc["ssid"]      = WiFi.SSID();
  doc["ip"]        = WiFi.localIP().toString();
  doc["rssi"]      = WiFi.RSSI();
  doc["free_heap"] = ESP.getFreeHeap();
  doc["uptime"]    = millis() / 1000;
  String body;
  serializeJson(doc, body);

  if (WiFi.SSID() == WIFI_SSID) {
    WiFiClient client;
    HTTPClient httpLocal;
    httpLocal.begin(client, HEARTBEAT_LOCAL);
    httpLocal.addHeader("Content-Type", "application/json");
    httpLocal.addHeader("X-Kiosk-API-Key", KIOSK_API_KEY);
    httpLocal.setTimeout(1500);
    httpLocal.POST(body);
    httpLocal.end();
  }

  BearSSL::WiFiClientSecure secureClient;
  secureClient.setInsecure();
  HTTPClient http;
  http.begin(secureClient, HEARTBEAT_CLOUD);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-Kiosk-API-Key", KIOSK_API_KEY);
  http.setTimeout(3000);
  http.POST(body);
  http.end();
}

void showError(String msg) {
  lcd.clear();
  printCenter(0, "!! GAGAL SCAN !!");
  printCenter(1, msg);
  beep(3, 120);
  delay(2500);
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

void sendMp3Command(uint8_t cmd, uint8_t para1, uint8_t para2) {
  uint8_t pkt[10] = {0x7E, 0xFF, 0x06, cmd, 0x00, para1, para2, 0x00, 0x00, 0xEF};
  uint16_t sum = 0;
  for (int i = 1; i < 7; i++) sum += pkt[i];
  sum = -sum;
  pkt[7] = (uint8_t)(sum >> 8);
  pkt[8] = (uint8_t)(sum & 0xFF);
  for (int i = 0; i < 10; i++) mp3Serial.write(pkt[i]);
}

void playAudio(uint8_t folder, uint8_t track) {
  sendMp3Command(0x0F, folder, track);
}

void setMp3Volume(uint8_t vol) {
  if (vol > 30) vol = 30;
  sendMp3Command(0x06, 0, vol);
}

String getRfidUID() {
  String uid = "";
  for (int8_t i = rfid.uid.size - 1; i >= 0; i--) {
    if (rfid.uid.uidByte[i] < 0x10) uid += "0";
    uid += String(rfid.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();
  return uid;
}
