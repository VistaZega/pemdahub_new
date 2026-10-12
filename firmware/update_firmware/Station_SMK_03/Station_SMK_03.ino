// ==============================================================================
//  PEMBDAHUB ATTENDANCE STATION - FIRMWARE UPDATE 2026
//  STATION ID      : STATION-SMK-03
//  UNIT SEKOLAH    : SMK Swasta Pembda
//  MIKROKONTROLER  : NodeMCU V3 (ESP8266 ESP-12F)
//  LAYAR           : LCD 16x2 I2C (Alamat 0x27 / 0x3F)
//  AUDIO / SUARA   : Buzzer Aktif (D8/GPIO15)
//  INDIKATOR LED   : LED Hijau Sukses (D3/GPIO0), LED Merah Gagal (D4/GPIO2)
//  PEMINDAI        : RFID RC522 (HSPI NodeMCU: D0=CS, D5=SCK, D7=MOSI, D6=MISO)
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

const char* DEVICE_ID           = "STATION-SMK-03";
const char* UNIT_NAME           = "SMK Swasta Pembda";
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
#define RFID_RST_PIN   255      // 3.3V
#define LED_GREEN_PIN    0      // D3 (GPIO0)
#define LED_RED_PIN      2      // D4 (GPIO2)
#define BUZZER_PIN      15      // D8 (GPIO15)

#define LCD_ADDRESS   0x27
#define LCD_COLS        16
#define LCD_ROWS         2

#define HTTP_TIMEOUT          8000
#define DISPLAY_RESULT_MS     3500
#define SCAN_COOLDOWN_MS      3000
#define WATCHDOG_TIMEOUT_MS 180000
#define HEARTBEAT_INTERVAL   45000
#define SLIDE_INTERVAL        5000

MFRC522           rfid(RFID_SS_PIN, RFID_RST_PIN);
LiquidCrystal_I2C lcd(LCD_ADDRESS, LCD_COLS, LCD_ROWS);
ESP8266WiFiMulti  wifiMulti;

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
String formatNama(String fullName, int maxLen);
void connectWiFiInitial();
void handleRfidScan();
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
  Serial.println(F("========================================================"));

  pinMode(BUZZER_PIN,    OUTPUT);
  pinMode(LED_GREEN_PIN, OUTPUT);
  pinMode(LED_RED_PIN,   OUTPUT);
  digitalWrite(BUZZER_PIN,    LOW);
  digitalWrite(LED_GREEN_PIN, LOW);
  digitalWrite(LED_RED_PIN,   LOW);

  Wire.begin(4, 5);
  Wire.setClock(400000);
#ifdef FDB_LIQUID_CRYSTAL_I2C_H
  lcd.begin();
#else
  lcd.init();
#endif
  lcd.backlight();

  printCenter(0, "PEMBDA PRESENSI");
  printCenter(1, DEVICE_ID);
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
        printCenter(0, "REBOOT WATCHDOG");
        printCenter(1, "Restart Sistem..");
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
  delay(20);
}


// ==============================================================================
//  FORMAT NAMA CERDAS (Mencegah Nama Terpotong di Layar)
// ==============================================================================
String formatNama(String fullName, int maxLen) {
  fullName.trim();
  if (fullName.length() <= maxLen) return fullName;

  int firstSpace = fullName.indexOf(' ');
  if (firstSpace == -1) {
    return fullName.substring(0, maxLen);
  }

  String firstName = fullName.substring(0, firstSpace);
  if (firstName.length() >= maxLen) {
    return firstName.substring(0, maxLen);
  }

  String result = firstName;
  int curr = firstSpace;
  while (curr != -1 && curr < fullName.length()) {
    while (curr < fullName.length() && fullName.charAt(curr) == ' ') curr++;
    if (curr < fullName.length()) {
      char initial = fullName.charAt(curr);
      if (result.length() + 3 <= maxLen) {
        result += " ";
        result += initial;
        result += ".";
      } else {
        break;
      }
    }
    curr = fullName.indexOf(' ', curr);
  }
  return result;
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
      if (inPlaceFrame == 0) printCenter(1, "> TEMPEL KARTU <");
      else printCenter(1, "* SILAKAN SCAN *");
    }
  } else if (currentSlide == 1) {
    if (inPlaceFrame == 0) printCenter(1, "STATION-SMK-03");
    else printCenter(1, "PANTANG MUNDUR");
  }
}

void showSlide(int slideIndex) {
  if (isShowingResult) return;

  switch (slideIndex) {
    case 0:
      // SIMETRIS TENGAH (Semua baris 16 karakter GENAP: Kiri 0, Kanan 0)
      printCenter(0, "PERGURUAN PEMBDA");
      if (isOnline) {
        if (inPlaceFrame == 0) printCenter(1, "> TEMPEL KARTU <");
        else printCenter(1, "* SILAKAN SCAN *");
      } else {
        printCenter(1, "[ MODE OFFLINE ]");
      }
      break;

    case 1:
      // SIMETRIS TENGAH (Baris 0 = 16 char, Baris 1 = 14 char [Kiri 1, Kanan 1])
      printCenter(0, "[SMK SW. PEMBDA]");
      if (inPlaceFrame == 0) printCenter(1, "STATION-SMK-03");
      else printCenter(1, "PANTANG MUNDUR");
      break;

    case 2:
      // RAPAT KIRI SEMUA (Titik dua sejajar lurus di kolom 2)
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
  printCenter(0, "Koneksi WiFi...");
  printCenter(1, "Mencari AP...");
  int attempt = 0;
  while (wifiMulti.run() != WL_CONNECTED && attempt < 20) {
    delay(300);
    digitalWrite(LED_GREEN_PIN, !digitalRead(LED_GREEN_PIN));
    attempt++;
  }
  digitalWrite(LED_GREEN_PIN, LOW);
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
  digitalWrite(LED_GREEN_PIN, HIGH);
  beep(1, 80);

  lcd.clear();
  printCenter(0, "MEMPROSES...");
  printCenter(1, uid);

  sendToServer(uid, "rfid");
  digitalWrite(LED_GREEN_PIN, LOW);
}

void sendToServer(String uid, String type) {
  if (WiFi.status() != WL_CONNECTED) {
    digitalWrite(LED_RED_PIN, HIGH);
    showError("WiFi Terputus!");
    digitalWrite(LED_RED_PIN, LOW);
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
    digitalWrite(LED_RED_PIN, HIGH);
    showError("Kartu Tdk Drftr");
    digitalWrite(LED_RED_PIN, LOW);
  } else {
    digitalWrite(LED_RED_PIN, HIGH);
    showError("Server Error " + String(httpCode));
    digitalWrite(LED_RED_PIN, LOW);
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

  String displayNama = formatNama(nama, LCD_COLS);

  lcd.clear();
  if (status == "success" || status == "info") {
    if (action_code == "CHECK_IN") {
      printCenter(0, displayNama != "" ? displayNama : "PRESENSI MASUK");
      printCenter(1, "MASUK: " + waktu.substring(0, 5) + " WIB");
      
      beep(1, 120);
    } else if (action_code == "CHECK_OUT") {
      printCenter(0, displayNama != "" ? displayNama : "*ABSEN PULANG*");
      printCenter(1, "PULANG " + waktu.substring(0, 5) + " WIB");
      beep(2, 80);
    } else if (action_code == "COOLDOWN") {
      printCenter(0, displayNama != "" ? displayNama : "*SUDAH ABSEN!*");
      printCenter(1, "ABSEN: " + waktu.substring(0, 5) + " WIB");
      beep(1, 200);
    } else if (action_code == "NEW_CARD") {
      sendScanBuffer(uid);
      printCenter(0, "* KARTU BARU ! *");
      printCenter(1, "DAFTAR DI ADMIN!");
      beep(3, 100);
    } else {
      printCenter(0, displayNama != "" ? displayNama : "** BERHASIL **");
      printCenter(1, message.length() <= 16 ? message : message.substring(0, 16));
      beep(1, 100);
    }
  } else {
    printCenter(0, "! PERINGATAN !");
    printCenter(1, message.length() <= 16 ? message : message.substring(0, 16));
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

String getRfidUID() {
  String uid = "";
  for (int8_t i = rfid.uid.size - 1; i >= 0; i--) {
    if (rfid.uid.uidByte[i] < 0x10) uid += "0";
    uid += String(rfid.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();
  return uid;
}