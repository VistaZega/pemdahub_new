// ==============================================================================
//  PEMBDAHUB ATTENDANCE STATION - FIRMWARE UPDATE 2026
//  STATION ID      : STATION-SMP-03
//  UNIT SEKOLAH    : SMPS Pembda 2
//  MIKROKONTROLER  : NodeMCU V3 (ESP8266 ESP-12F)
//  LAYAR           : LCD 20x4 I2C (Alamat 0x27 / 0x3F)
//  AUDIO / SUARA   : DFPlayer Mini MP3 Player (SoftwareSerial TX=D4/GPIO2)
//  PEMINDAI QR     : GM65 Barcode/QR Scanner (SoftwareSerial RX=D3/GPIO0)
//  PEMINDAI RFID   : RFID RC522 (HSPI NodeMCU: D0=CS, D5=SCK, D7=MOSI, D6=MISO)
//  INDIKATOR       : Buzzer Aktif (D8/GPIO15)
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

const char* DEVICE_ID           = "STATION-SMP-03";
const char* UNIT_NAME           = "SMPS Pembda 2";
const char* KIOSK_API_KEY       = "RAHASIA-PEMBDAHUB-12345";

const char* SERVER_LOCAL_URL    = "http://50.35.89.10/api/attendance/rfid-scan";
const char* SCAN_BUFFER_LOCAL   = "http://50.35.89.10/api/rfid/scan-buffer";
const char* HEARTBEAT_LOCAL     = "http://50.35.89.10/api/attendance/heartbeat";

const char* SERVER_CLOUD_URL    = "https://perguruanpembda.com/api/attendance/rfid-scan";
const char* SCAN_BUFFER_CLOUD   = "https://perguruanpembda.com/api/rfid/scan-buffer";
const char* HEARTBEAT_CLOUD     = "https://perguruanpembda.com/api/attendance/heartbeat";

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

#define RFID_SS_PIN     16      // D0 (GPIO16)
#define RFID_RST_PIN   255      // 3.3V
#define MP3_TX_PIN       2      // D4 (GPIO2)
#define MP3_VOLUME      30
#define BUZZER_PIN      15      // D8 (GPIO15)
#define QR_RX_PIN        0      // D3 (GPIO0) - RX SoftwareSerial dari GM65

#define LCD_ADDRESS   0x27
#define LCD_COLS        20
#define LCD_ROWS         4

#define HTTP_TIMEOUT          8000
#define DISPLAY_RESULT_MS     3500
#define SCAN_COOLDOWN_MS      3000
#define WATCHDOG_TIMEOUT_MS 180000
#define HEARTBEAT_INTERVAL   45000
#define SLIDE_INTERVAL        5000

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

void setup() {
  Serial.begin(115200);
  delay(500);

  Serial.println(F("\n========================================================"));
  Serial.print  (F("  STATION ID   : ")); Serial.println(DEVICE_ID);
  Serial.print  (F("  UNIT         : ")); Serial.println(UNIT_NAME);
  Serial.println(F("========================================================"));

  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);

  Wire.begin(4, 5); // D2=SDA, D1=SCL
  Wire.setClock(400000);
#ifdef FDB_LIQUID_CRYSTAL_I2C_H
  lcd.begin();
#else
  lcd.init();
#endif
  lcd.backlight();

  printCenter(0, "* PERGURUAN PEMBDA *");
  printCenter(1, "--------------------");
  printCenter(2, UNIT_NAME);
  printCenter(3, DEVICE_ID);
  beep(1, 100);
  delay(1500);

  SPI.begin();
  SPI.setFrequency(1000000);
  delay(50);
  rfid.PCD_Init();
  delay(50);
  rfid.PCD_SetAntennaGain(rfid.RxGain_max);
  rfid.PCD_AntennaOn();

  qrSerial.begin(9600);

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

  lastActivityTime    = millis();
  lastSlideChangeTime = millis();
  showSlide(0);
}

void loop() {
  unsigned long now = millis();

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
        printCenter(1, "REBOOT WATCHDOG");
        printCenter(2, "Restart Sistem..");
        delay(1500);
        ESP.restart();
      }
    }
  }

  if (isOnline && (now - lastHeartbeatTime >= HEARTBEAT_INTERVAL || lastHeartbeatTime == 0)) {
    lastHeartbeatTime = now;
    sendHeartbeat();
  }

  if (!isShowingResult) {
    if (now - lastSlideChangeTime >= SLIDE_INTERVAL) {
      lastSlideChangeTime = now;
      currentSlide = (currentSlide + 1) % 3;
      showSlide(currentSlide);
    }
  }

  handleRfidScan();
  handleQrScan();
  delay(20);
}

void printCenter(uint8_t row, const String& text) {
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
      printCenter(0, "* PERGURUAN PEMBDA *");
      printCenter(1, "--------------------");
      printCenter(2, "TEMPEL KARTU RFID");
      printCenter(3, "ATAU SCAN QR CODE");
      break;
    case 1:
      printCenter(0, UNIT_NAME);
      printCenter(1, "Inovasi & Prestasi");
      printCenter(2, "Maju Terus - Pantang");
      printCenter(3, "Mundur! Semangat");
      break;
    case 2:
      printCenter(0, "--- STATUS SISTEM --");
      if (isOnline) {
        printRow(1, "WiFi: " + WiFi.SSID().substring(0, 14));
        printRow(2, "IP  : " + WiFi.localIP().toString());
        printRow(3, "Sig : " + String(WiFi.RSSI()) + " dBm [ONLINE]");
      } else {
        printCenter(1, "MENCARI JARINGAN...");
        printCenter(2, "WiFi Tidak Aktif");
        printCenter(3, "[MODE OFFLINE]");
      }
      break;
  }
}

void connectWiFiInitial() {
  lcd.clear();
  printCenter(1, "Koneksi WiFi...");
  printCenter(2, "Mencari Jaringan..");
  int attempt = 0;
  while (wifiMulti.run() != WL_CONNECTED && attempt < 20) {
    delay(300);
    attempt++;
  }
  isOnline = (WiFi.status() == WL_CONNECTED);
}

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
  beep(1, 80);

  lcd.clear();
  printCenter(0, "* MEMPROSES KARTU *");
  printCenter(1, "--------------------");
  printCenter(2, "UID: " + uid);
  printCenter(3, "Menghubungi Server..");

  sendToServer(uid, "rfid");
}

void handleQrScan() {
  if (!qrSerial.available()) return;
  String qrCode = "";
  unsigned long startWait = millis();
  while (millis() - startWait < 80) {
    while (qrSerial.available()) {
      char c = qrSerial.read();
      if (c != '\r' && c != '\n') qrCode += c;
    }
  }
  qrCode.trim();
  if (qrCode.length() < 3) return;

  unsigned long now = millis();
  if (qrCode == lastUID && (now - lastTapTime) < SCAN_COOLDOWN_MS) return;
  lastUID     = qrCode;
  lastTapTime = now;

  isShowingResult = true;
  beep(1, 80);

  lcd.clear();
  printCenter(0, "* MEMPROSES QR *");
  printCenter(1, "--------------------");
  printCenter(2, qrCode.substring(0, 20));
  printCenter(3, "Menghubungi Server..");

  sendToServer(qrCode, "qr");
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

  {
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
    showError("Data Tdk Terdaftar");
  } else {
    playAudio(1, 7);
    showError("Server Error " + String(httpCode));
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
  const char* rawNama = doc["data"]["nama"];
  if (!rawNama) rawNama = doc["data"]["name"];
  String nama        = rawNama ? rawNama : "";

  const char* rawWaktu = doc["data"]["waktu"];
  if (!rawWaktu) rawWaktu = doc["data"]["time"];
  String waktu       = rawWaktu ? rawWaktu : "";
  String kelas       = doc["data"]["kelas"] | "";

  lcd.clear();
  if (status == "success" || status == "info") {
    if (action_code == "CHECK_IN") {
      printCenter(0, "* PRESENSI MASUK *");
      printCenter(1, nama);
      printCenter(2, kelas != "" ? kelas : "Siswa / Guru");
      printCenter(3, "Waktu: " + waktu.substring(0, 8));
      if (kelas.indexOf("Guru") >= 0 || kelas.indexOf("Staf") >= 0) playAudio(1, 5);
      else playAudio(1, 2);
      beep(1, 120);
    } else if (action_code == "CHECK_OUT") {
      printCenter(0, "* PRESENSI PULANG *");
      printCenter(1, nama);
      printCenter(2, "Sampai Jumpa Besok!");
      printCenter(3, "Waktu: " + waktu.substring(0, 8));
      playAudio(1, 3);
      beep(2, 80);
    } else if (action_code == "COOLDOWN") {
      printCenter(0, "* SUDAH TERCATAT *");
      printCenter(1, nama);
      printCenter(2, "Presensi Sebelumnya:");
      printCenter(3, "Jam " + waktu.substring(0, 5) + " WIB");
      playAudio(1, 4);
      beep(1, 200);
    } else if (action_code == "NEW_CARD") {
      sendScanBuffer(uid);
      printCenter(0, "** KARTU BARU **");
      printCenter(1, "UID: " + uid);
      printCenter(2, "Belum Terdaftar!");
      printCenter(3, "Hubungi Admin Web");
      playAudio(1, 6);
      beep(3, 100);
    } else {
      printCenter(0, "* BERHASIL *");
      printCenter(1, nama);
      printCenter(2, message);
      printCenter(3, waktu);
      playAudio(1, 2);
      beep(1, 100);
    }
  } else {
    printCenter(0, "! PERINGATAN !");
    printCenter(1, "--------------------");
    printCenter(2, message);
    printCenter(3, "Periksa Status Kartu");
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
  String body;
  serializeJson(doc, body);

  BearSSL::WiFiClientSecure secureClient;
  secureClient.setInsecure();
  HTTPClient http;
  http.begin(secureClient, HEARTBEAT_CLOUD);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-Kiosk-API-Key", KIOSK_API_KEY);
  http.setTimeout(2500);
  http.POST(body);
  http.end();
}

void showError(String msg) {
  lcd.clear();
  printCenter(0, "! GAGAL !");
  printCenter(1, "--------------------");
  printCenter(2, msg);
  printCenter(3, "Coba Beberapa Saat");
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