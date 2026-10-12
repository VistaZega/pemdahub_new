// ==============================================================================
//  PEMBDAHUB ATTENDANCE STATION - FIRMWARE UPDATE 2026
//  STATION ID      : STATION-SMA-03
//  UNIT SEKOLAH    : SMAS Pembda 1
//  MIKROKONTROLER  : NodeMCU V3 (ESP8266 ESP-12F)
//  LAYAR           : LCD 20x4 I2C (Alamat 0x27 / 0x3F)
//  AUDIO / SUARA   : Buzzer Aktif (D8/GPIO15) - TANPA MP3 PLAYER
//  FITUR SPESIAL   : Pin D4 (GPIO2) didedikasikan untuk Hardware Reset RFID RC522
//  PEMINDAI QR     : GM65 Barcode/QR Scanner (SoftwareSerial RX=D3/GPIO0)
//  PEMINDAI RFID   : RFID RC522 (HSPI NodeMCU: D0=CS, D5=SCK, D7=MOSI, D6=MISO, D4=RST)
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

const char* DEVICE_ID           = "STATION-SMA-03";
const char* UNIT_NAME           = "SMAS Pembda 1";
const char* KIOSK_API_KEY       = "RAHASIA-PEMBDAHUB-12345";

const char* SERVER_LOCAL_URL    = "http://50.35.89.10/api/attendance/rfid-scan";
const char* SCAN_BUFFER_LOCAL   = "http://50.35.89.10/api/rfid/scan-buffer";
const char* HEARTBEAT_LOCAL     = "http://50.35.89.10/api/attendance/heartbeat";

const char* SERVER_CLOUD_URL    = "https://perguruanpembda.com/api/attendance/rfid-scan";
const char* SCAN_BUFFER_CLOUD   = "https://perguruanpembda.com/api/rfid/scan-buffer";
const char* HEARTBEAT_CLOUD     = "https://perguruanpembda.com/api/attendance/heartbeat";

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

#define RFID_SS_PIN     16      // D0 (GPIO16)
#define RFID_RST_PIN     2      // D4 (GPIO2)  - Dedicated Hardware Reset RC522
#define BUZZER_PIN      15      // D8 (GPIO15)
#define QR_RX_PIN        0      // D3 (GPIO0)  - RX GM65 Scanner

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
unsigned long lastInPlaceAnimTime = 0;
int  inPlaceFrame        = 0;

void printCenter(uint8_t row, const String& text);
void printRow(uint8_t row, const String& text);
void showSlide(int slideIndex);
void updateInPlaceAnim();
void connectWiFiInitial();
void handleRfidScan();
void handleQrScan();
void sendToServer(String uid, String type);
void sendScanBuffer(String uid);
void sendHeartbeat();
void parseAndDisplay(String json, String uid);
void showError(String msg);
void beep(int count, int duration);
String getRfidUID();

void setup() {
  Serial.begin(115200);
  delay(500);

  Serial.println(F("\n========================================================"));
  Serial.print  (F("  STATION ID   : ")); Serial.println(DEVICE_ID);
  Serial.print  (F("  UNIT         : ")); Serial.println(UNIT_NAME);
  Serial.println(F("  MODE         : BUZZER ONLY (D4 = RFID HW RESET)"));
  Serial.println(F("========================================================"));

  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);

  Wire.begin(4, 5);
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
    } else {
      updateInPlaceAnim();
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



void updateInPlaceAnim() {
  if (isShowingResult) return;
  unsigned long now = millis();
  if (now - lastInPlaceAnimTime < 1500) return;
  lastInPlaceAnimTime = now;
  inPlaceFrame = (inPlaceFrame + 1) % 2;

  if (currentSlide == 0) {
    if (isOnline) {
      if (inPlaceFrame == 0) printCenter(2, ">> TEMPEL KARTU <<");
      else printCenter(2, ">> SCAN QR CODE <<");
    }
  } else if (currentSlide == 1) {
    if (inPlaceFrame == 0) printCenter(2, "Keep Moving Forward!");
    else printCenter(2, "** PANTANG MUNDUR **");
  }
}

void showSlide(int slideIndex) {
  if (isShowingResult) return;
  switch (slideIndex) {
    case 0:
      // SIMETRIS TENGAH (Semua baris berjumlah karakter GENAP: 20, 20, 18, 20)
      printCenter(0, "* PERGURUAN PEMBDA *");
      printCenter(1, "====================");
      if (isOnline) {
        if (inPlaceFrame == 0) printCenter(2, ">> TEMPEL KARTU <<");
        else printCenter(2, ">> SCAN QR CODE <<");
      } else {
        printCenter(2, "[  MODE OFFLINE  ]");
      }
      printCenter(3, "SISTEM PRESENSI RFID");
      break;

    case 1:
      // SIMETRIS TENGAH (Identitas Unit & Station: 20, 18, 20, 20)
      printCenter(0, "* SMAS PEMBDA NIAS *");
      printCenter(1, "[ STATION-SMA-03 ]");
      if (inPlaceFrame == 0) printCenter(2, "Keep Moving Forward!");
      else printCenter(2, "** PANTANG MUNDUR **");
      printCenter(3, "Maju Terus - Sukses!");
      break;

    case 2:
      // RAPAT KIRI SEMUA (Titik dua sejajar lurus di kolom 4)
      printRow(0, "ID  : " + String(DEVICE_ID));
      if (isOnline) {
        String ssid = WiFi.SSID();
        if (ssid.length() > 14) ssid = ssid.substring(0, 14);
        printRow(1, "WiFi: " + ssid);
        printRow(2, "IP  : " + WiFi.localIP().toString());
        printRow(3, "Sig : " + String(WiFi.RSSI()) + " dBm [ON]");
      } else {
        printRow(1, "WiFi: TERPUTUS");
        printRow(2, "Stat: MENCARI AP...");
        printRow(3, "Mode: OFFLINE SIAGA");
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
    showError("Data Tdk Terdaftar");
  } else {
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
      printCenter(1, kelas != "" ? (kelas.length() <= 20 ? kelas : kelas.substring(0, 20)) : "Siswa / Guru");
      printCenter(2, "MASUK: " + waktu.substring(0, 5) + " WIB");
      if (kelas.indexOf("Guru") >= 0 || kelas.indexOf("Staf") >= 0 || kelas.indexOf("Staff") >= 0) {
        printCenter(3, "Selamat Bertugas!");
      } else {
        printCenter(3, "Selamat Belajar!");
      }
      beep(1, 120);
    } else if (action_code == "CHECK_OUT") {
      printCenter(0, nama != "" ? nama : "* PRESENSI PULANG *");
      printCenter(1, kelas != "" ? (kelas.length() <= 20 ? kelas : kelas.substring(0, 20)) : "Siswa / Guru");
      printCenter(2, "PULANG: " + waktu.substring(0, 5) + " WIB");
      printCenter(3, "Hati-hati di jalan!");
      beep(2, 80);
    } else if (action_code == "COOLDOWN") {
      printCenter(0, nama != "" ? nama : "* SUDAH ABSEN *");
      printCenter(1, kelas != "" ? (kelas.length() <= 20 ? kelas : kelas.substring(0, 20)) : "Siswa / Guru");
      printCenter(2, "Sudah Absen Masuk!");
      printCenter(3, "Jam: " + waktu.substring(0, 5) + " WIB");
      beep(1, 200);
    } else if (action_code == "NEW_CARD") {
      sendScanBuffer(uid);
      printCenter(0, "** KARTU BARU **");
      printCenter(1, "UID: " + uid);
      printCenter(2, "Belum Terdaftar!");
      printCenter(3, "Daftarkan di Admin");
      beep(3, 100);
    } else {
      printCenter(0, nama != "" ? nama : "* BERHASIL *");
      printCenter(1, kelas != "" ? (kelas.length() <= 20 ? kelas : kelas.substring(0, 20)) : message);
      printCenter(2, message.length() <= 20 ? message : message.substring(0, 20));
      printCenter(3, waktu != "" ? ("Waktu: " + waktu.substring(0, 5) + " WIB") : "");
      beep(1, 100);
    }
  } else {
    printCenter(0, "! PERINGATAN !");
    printCenter(1, nama != "" ? nama : message);
    printCenter(2, message.length() <= 20 ? message : message.substring(0, 20));
    printCenter(3, "Silakan Coba Lagi");
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

String getRfidUID() {
  String uid = "";
  for (int8_t i = rfid.uid.size - 1; i >= 0; i--) {
    if (rfid.uid.uidByte[i] < 0x10) uid += "0";
    uid += String(rfid.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();
  return uid;
}