// ==============================================================================
//  PEMBDAHUB ATTENDANCE STATION - FIRMWARE UPDATE 2026
//  STATION ID      : STATION-SMK-04
//  UNIT SEKOLAH    : SMK Swasta Pembda
//  MIKROKONTROLER  : ESP32 DevKit V1 (WROOM-32)
//  LAYAR           : LCD 16x2 I2C (SDA=GPIO21, SCL=GPIO22)
//  AUDIO / SUARA   : DFPlayer Mini MP3 (HardwareSerial Serial2: TX2=GPIO17)
//  INDIKATOR LED   : LED Hijau (GPIO15), LED Merah (GPIO13), Buzzer (GPIO2)
//  PEMINDAI        : RFID RC522 (VSPI: SS=5, SCK=18, MOSI=23, MISO=19, RST=4)
// ==============================================================================

#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include <ArduinoJson.h>

const char* DEVICE_ID           = "STATION-SMK-04";
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
const char* WIFI_ALT1_SSID      = "Xspace";
const char* WIFI_ALT1_PASSWORD  = "12345678starlink";
const char* WIFI_ALT2_SSID      = "TEFA";
const char* WIFI_ALT2_PASSWORD  = "PEMBDA2026";
const char* WIFI_ALT3_SSID      = "VISTAFAMILY";
const char* WIFI_ALT3_PASSWORD  = "pelita31";
const char* WIFI_ALT4_SSID      = "PembdaNET";
const char* WIFI_ALT4_PASSWORD  = "pelita31";

#define RFID_SS_PIN      5
#define RFID_SCK_PIN    18
#define RFID_MOSI_PIN   23
#define RFID_MISO_PIN   19
#define RFID_RST_PIN     4

#define I2C_SDA_PIN     21
#define I2C_SCL_PIN     22
#define LCD_ADDRESS   0x27
#define LCD_COLS        16
#define LCD_ROWS         2

#define MP3_TX_PIN      17
#define MP3_RX_PIN      16
#define MP3_VOLUME      30

#define BUZZER_PIN       2
#define LED_GREEN_PIN   15
#define LED_RED_PIN     13

#define HTTP_TIMEOUT          8000
#define DISPLAY_RESULT_MS     3500
#define SCAN_COOLDOWN_MS      3000
#define WATCHDOG_TIMEOUT_MS 180000
#define HEARTBEAT_INTERVAL   45000
#define SLIDE_INTERVAL        5000

MFRC522           rfid(RFID_SS_PIN, RFID_RST_PIN);
LiquidCrystal_I2C lcd(LCD_ADDRESS, LCD_COLS, LCD_ROWS);

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
void connectWiFiMulti();
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

  Wire.begin(I2C_SDA_PIN, I2C_SCL_PIN);
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

  SPI.begin(RFID_SCK_PIN, RFID_MISO_PIN, RFID_MOSI_PIN, RFID_SS_PIN);
  delay(50);
  rfid.PCD_Init();
  delay(50);
  rfid.PCD_SetAntennaGain(rfid.RxGain_max);
  rfid.PCD_AntennaOn();

  WiFi.mode(WIFI_STA);
  WiFi.setSleep(false); // Nonaktifkan power saving WiFi pada ESP32
  WiFi.persistent(false);
  WiFi.setAutoReconnect(true);

  connectWiFiMulti();

  Serial2.begin(9600, SERIAL_8N1, MP3_RX_PIN, MP3_TX_PIN);
  delay(300);
  while (Serial2.available()) Serial2.read();
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
    if (WiFi.status() == WL_CONNECTED) {
      if (!isOnline) {
        isOnline = true;
        wifiDisconnectedSince = 0;
        if (!isShowingResult) showSlide(currentSlide);
      }
    } else {
      if (isOnline) isOnline = false;
      connectWiFiMulti();
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
    }
  }

  handleRfidScan();
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
      printCenter(0, "PEMBDA PRESENSI");
      printCenter(1, isOnline ? "TEMPEL KARTU" : "[OFFLINE]");
      break;
    case 1:
      printCenter(0, UNIT_NAME);
      printCenter(1, "Pantang Mundur!");
      break;
    case 2:
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

void connectWiFiMulti() {
  if (WiFi.status() == WL_CONNECTED) return;
  const char* ssids[] = { WIFI_SSID, WIFI_ALT1_SSID, WIFI_ALT2_SSID, WIFI_ALT3_SSID, WIFI_ALT4_SSID };
  const char* pwds[]  = { WIFI_PASSWORD, WIFI_ALT1_PASSWORD, WIFI_ALT2_PASSWORD, WIFI_ALT3_PASSWORD, WIFI_ALT4_PASSWORD };

  for (int i = 0; i < 5; i++) {
    WiFi.begin(ssids[i], pwds[i]);
    unsigned long st = millis();
    while (WiFi.status() != WL_CONNECTED && millis() - st < 3000) {
      delay(100);
    }
    if (WiFi.status() == WL_CONNECTED) {
      isOnline = true;
      wifiDisconnectedSince = 0;
      return;
    }
  }
  isOnline = false;
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
    playAudio(1, 7);
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
    HTTPClient http;
    http.begin(SERVER_LOCAL_URL);
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
    WiFiClientSecure secureClient;
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
    playAudio(1, 6);
    showError("Kartu Tdk Drftr");
    digitalWrite(LED_RED_PIN, LOW);
  } else {
    digitalWrite(LED_RED_PIN, HIGH);
    playAudio(1, 7);
    showError("Server Error " + String(httpCode));
    digitalWrite(LED_RED_PIN, LOW);
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
    digitalWrite(LED_GREEN_PIN, HIGH);
    if (action_code == "CHECK_IN") {
      printCenter(0, nama);
      printCenter(1, "MSK " + waktu.substring(0, 8));
      if (kelas.indexOf("Guru") >= 0 || kelas.indexOf("Staf") >= 0) playAudio(1, 5);
      else playAudio(1, 2);
      beep(1, 120);
    } else if (action_code == "CHECK_OUT") {
      printCenter(0, nama);
      printCenter(1, "PLG " + waktu.substring(0, 8));
      playAudio(1, 3);
      beep(2, 80);
    } else if (action_code == "COOLDOWN") {
      printCenter(0, nama);
      printCenter(1, "Sudah Absen " + waktu.substring(0, 5));
      playAudio(1, 4);
      beep(1, 200);
    } else if (action_code == "NEW_CARD") {
      digitalWrite(LED_RED_PIN, HIGH);
      sendScanBuffer(uid);
      printCenter(0, "* KARTU BARU *");
      printCenter(1, "Daftar di Admin");
      playAudio(1, 6);
      beep(3, 100);
      digitalWrite(LED_RED_PIN, LOW);
    } else {
      printCenter(0, nama);
      printCenter(1, message);
      playAudio(1, 2);
      beep(1, 100);
    }
    digitalWrite(LED_GREEN_PIN, LOW);
  } else {
    digitalWrite(LED_RED_PIN, HIGH);
    printCenter(0, "! PERINGATAN !");
    printCenter(1, message);
    playAudio(1, 7);
    beep(2, 250);
    digitalWrite(LED_RED_PIN, LOW);
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

  WiFiClientSecure secureClient;
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

  WiFiClientSecure secureClient;
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

void sendMp3Command(uint8_t cmd, uint8_t para1, uint8_t para2) {
  uint8_t pkt[10] = {0x7E, 0xFF, 0x06, cmd, 0x00, para1, para2, 0x00, 0x00, 0xEF};
  uint16_t sum = 0;
  for (int i = 1; i < 7; i++) sum += pkt[i];
  sum = -sum;
  pkt[7] = (uint8_t)(sum >> 8);
  pkt[8] = (uint8_t)(sum & 0xFF);
  for (int i = 0; i < 10; i++) Serial2.write(pkt[i]);
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