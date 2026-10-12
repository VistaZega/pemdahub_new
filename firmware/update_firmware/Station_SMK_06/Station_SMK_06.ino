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
//  2. Auto-Reboot Watchdog: Jika WiFi putus > 3 menit, sistem auto-restart.
//  3. Heartbeat Otomatis (45s): Melaporkan IP, RSSI, status online ke server.
//  4. Tampilan Simetris: Helper printCenter() & printRow() merapikan teks di LCD.
//  5. Animasi Huruf Berganti In-Place: Berganti teks/panah tanpa running text buram.
//  6. Smart Name Formatting: Mencegah nama terpotong di layar 16/20 kolom.
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

// Profil WiFi Multi-AP Default / Utama (Standar Resmi Seluruh Station)
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
//  PIN DEFINITIONS & HARDWARE MAPPING (NodeMCU ESP8266)
// ==============================================================================
#define RFID_SS_PIN     16      // D0 (GPIO16) - Chip Select RC522
#define RFID_RST_PIN   255      // Reset terhubung langsung ke 3.3V
#define MP3_TX_PIN       2      // D4 (GPIO2)  - TX NodeMCU -> RX DFPlayer via 1k Ohm
#define MP3_VOLUME      30      // Volume Speaker DFPlayer (0 - 30)

#define BUZZER_PIN      15      // D8 (GPIO15) - Buzzer Aktif
#define QR_RX_PIN        0      // D3 (GPIO0)  - RX GM65 Barcode/QR Scanner

#define LCD_ADDRESS   0x27      // Alamat I2C LCD (0x27 atau 0x3F)
#define LCD_COLS        20      // LCD 20x4: 20 Kolom Penuh (Kolom 0 s.d 19)
#define LCD_ROWS         4      // LCD 20x4: 4 Baris Penuh (Baris 0 s.d 3)

#define HTTP_TIMEOUT          8000
#define DISPLAY_RESULT_MS     3500
#define SCAN_COOLDOWN_MS      3000
#define WATCHDOG_TIMEOUT_MS 180000  // 3 Menit WiFi putus -> ESP.restart()
#define HEARTBEAT_INTERVAL   45000  // 45 Detik kirim denyut jantung
#define SLIDE_INTERVAL        5000  // 5 Detik per halaman carousel
#define ANIM_INTERVAL         1500  // 1.5 Detik animasi huruf berganti in-place

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
unsigned long lastInPlaceAnimTime   = 0;
unsigned long wifiDisconnectedSince = 0;

bool isOnline        = false;
bool isShowingResult = false;
int  currentSlide    = 0;
int  inPlaceFrame    = 0;

// Prototype Fungsi
void printCenter(uint8_t row, const String& text);
void printRow(uint8_t row, const String& text);
void showSlide(int slideIndex);
void updateInPlaceAnim();
String formatNama(String fullName, int maxLen);
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

  mp3Serial.begin(9600);
  qrSerial.begin(9600);
  delay(200);
  setMp3Volume(MP3_VOLUME);

  Wire.begin(4, 5);
  lcd.init();
  lcd.backlight();

  lcd.clear();
  #if LCD_ROWS == 2
    printCenter(0, "PembdaHUB PRESENSI");
    printCenter(1, "Memulai...");
  #else
    printCenter(0, "PERGURUAN PEMBDA NIAS");
    printCenter(1, "Sistem Presensi RFID");
    printCenter(2, DEVICE_ID);
    printCenter(3, "Memulai Hardware...");
  #endif
  beep(1, 80);

  SPI.begin();
  rfid.PCD_Init();
  delay(50);
  rfid.PCD_Reset();
  delay(50);
  rfid.PCD_Init();
  delay(50);

  WiFi.mode(WIFI_STA);
  WiFi.setSleepMode(WIFI_NONE_SLEEP);
  WiFi.setAutoReconnect(true);

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

  // 1. Pemeriksaan WiFi Non-Blocking (Setiap 5 detik)
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
        lcd.clear();
        printCenter(0, "WATCHDOG REBOOT");
        #if LCD_ROWS == 2
          printCenter(1, "Restart...");
        #else
          printCenter(1, "WiFi Terputus >3 Mnt");
          printCenter(2, "Restart Sistem...");
        #endif
        delay(1500);
        ESP.restart();
      }
    }
  }

  // 2. Heartbeat IoT setiap 45 detik
  if (isOnline && (now - lastHeartbeatTime >= HEARTBEAT_INTERVAL || lastHeartbeatTime == 0)) {
    lastHeartbeatTime = now;
    sendHeartbeat();
  }

  // 3. Carousel Siaga Berganti (Slide Carousel 5s) & Animasi Huruf Berganti In-Place
  if (!isShowingResult) {
    if (now - lastSlideChangeTime >= SLIDE_INTERVAL) {
      lastSlideChangeTime = now;
      currentSlide = (currentSlide + 1) % 3;
      showSlide(currentSlide);
    } else {
      updateInPlaceAnim();
    }
  }

  // 4. Deteksi Pemindaian RFID & QR Code
  handleRfidScan();
  handleQrScan();

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

// ==============================================================================
//  HELPER DISPLAY SIMETRIS & ANIMASI HURUF BERGANTI
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

  #if LCD_ROWS == 2
    if (currentSlide == 0) {
      if (isOnline) {
        if (inPlaceFrame == 0) printCenter(1, "> TEMPEL KARTU <");
        else printCenter(1, "* SCAN QR CODE *");
      }
    } else if (currentSlide == 1) {
      if (inPlaceFrame == 0) printCenter(1, "STATION-SMK-06");
      else printCenter(1, "PANTANG MUNDUR");
    }
  #else
    if (currentSlide == 0) {
      if (isOnline) {
        if (inPlaceFrame == 0) printCenter(2, ">> TEMPEL KARTU <<");
        else printCenter(2, ">> SCAN QR CODE <<");
      }
    } else if (currentSlide == 1) {
      if (inPlaceFrame == 0) printCenter(2, "Keep Moving Forward!");
      else printCenter(2, "** PANTANG MUNDUR **");
    }
  #endif
}

void showSlide(int slideIndex) {
  if (isShowingResult) return;

  #if LCD_ROWS == 2
    switch (slideIndex) {
      case 0:
        printCenter(0, "PERGURUAN PEMBDA");
        if (isOnline) {
          if (inPlaceFrame == 0) printCenter(1, "> TEMPEL KARTU <");
          else printCenter(1, "* SCAN QR CODE *");
        } else {
          printCenter(1, "[ MODE OFFLINE ]");
        }
        break;

      case 1:
        printCenter(0, "SMKS PEMBDA NIAS");
        if (inPlaceFrame == 0) printCenter(1, "STATION-SMK-06");
        else printCenter(1, "PANTANG MUNDUR");
        break;

      case 2:
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
  #else
    switch (slideIndex) {
      case 0:
        // SIMETRIS TENGAH 20 KOLOM x 4 BARIS PENUH
        printCenter(0, "* PERGURUAN PEMBDA *");
        printCenter(1, "====================");
        if (isOnline) {
          if (inPlaceFrame == 0) printCenter(2, ">> TEMPEL KARTU <<");
          else printCenter(2, ">> SCAN QR CODE <<");
        } else {
          printCenter(2, "Status: MODE OFFLINE");
        }
        printCenter(3, "SISTEM PRESENSI RFID");
        break;

      case 1:
        // SIMETRIS TENGAH 20 KOLOM x 4 BARIS PENUH
        printCenter(0, "* SMKS PEMBDA NIAS *");
        printCenter(1, "[  STATION-SMK-06  ]");
        if (inPlaceFrame == 0) printCenter(2, "Keep Moving Forward!");
        else printCenter(2, "** PANTANG MUNDUR **");
        printCenter(3, "Maju Terus - Sukses!");
        break;

      case 2:
        // RAPAT KIRI SEMUA 20 KOLOM x 4 BARIS (Titik dua sejajar di kolom 4)
        printRow(0, "ID  : " + String(DEVICE_ID));
        if (isOnline) {
          String ssid = WiFi.SSID();
          if (ssid.length() > 14) ssid = ssid.substring(0, 14);
          printRow(1, "AP  : " + ssid);
          printRow(2, "IP  : " + WiFi.localIP().toString());
          printRow(3, "Sig : " + String(WiFi.RSSI()) + " dBm [ON]");
        } else {
          printRow(1, "AP  : TERPUTUS");
          printRow(2, "STAT: MENCARI WIFI..");
          printRow(3, "MODE: OFFLINE SIAGA");
        }
        break;
    }
  #endif
}

void connectWiFiInitial() {
  lcd.clear();
  #if LCD_ROWS == 2
    printCenter(0, "KONEKSI WIFI....");
    printCenter(1, "MENCARI SINYAL..");
  #else
    printCenter(0, "* PERGURUAN PEMBDA *");
    printCenter(1, "====================");
    printCenter(2, "KONEKSI WIFI........");
    printCenter(3, "MENCARI JARINGAN....");
  #endif
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
  if (uid == lastUID && (now - lastTapTime < SCAN_COOLDOWN_MS)) return;

  lastUID = uid;
  lastTapTime = now;

  isShowingResult = true;
  lcd.clear();
  #if LCD_ROWS == 2
    printCenter(0, "*MEMPROSES DATA*");
    printCenter(1, "ID: " + uid);
  #else
    printCenter(0, "** MEMPROSES DATA **");
    printCenter(1, "--------------------");
    printCenter(2, "UID : " + uid);
    printCenter(3, "Menghubungi Server..");
  #endif
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
  if (qrData == lastUID && (now - lastTapTime < SCAN_COOLDOWN_MS)) return;

  lastUID = qrData;
  lastTapTime = now;

  isShowingResult = true;
  lcd.clear();
  #if LCD_ROWS == 2
    printCenter(0, "*MEMPROSES QR..*");
    printCenter(1, qrData.substring(0, min((int)qrData.length(), 16)));
  #else
    printCenter(0, "** MEMPROSES QR.. **");
    printCenter(1, "--------------------");
    printCenter(2, qrData.substring(0, min((int)qrData.length(), 20)));
    printCenter(3, "Menghubungi Server..");
  #endif
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

  String displayNama = formatNama(nama, LCD_COLS);

  lcd.clear();
  #if LCD_ROWS == 2
    if (status == "success" || status == "info") {
      if (action_code == "CHECK_IN") {
        printCenter(0, displayNama != "" ? displayNama : "PRESENSI MASUK");
        printCenter(1, "MASUK: " + waktu.substring(0, 5) + " WIB");
        if (kelas.indexOf("Guru") >= 0 || kelas.indexOf("Staf") >= 0) playAudio(1, 5);
        else playAudio(1, 2);
        beep(1, 120);
      } else if (action_code == "CHECK_OUT") {
        printCenter(0, displayNama != "" ? displayNama : "*ABSEN PULANG*");
        printCenter(1, "PULANG " + waktu.substring(0, 5) + " WIB");
        playAudio(1, 3);
        beep(2, 80);
      } else if (action_code == "COOLDOWN") {
        printCenter(0, displayNama != "" ? displayNama : "*SUDAH ABSEN!*");
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
        printCenter(0, displayNama != "" ? displayNama : "** BERHASIL **");
        printCenter(1, message.length() <= 16 ? message : message.substring(0, 16));
        playAudio(1, 2);
        beep(1, 100);
      }
    } else {
      printCenter(0, "! PERINGATAN !");
      printCenter(1, message.length() <= 16 ? message : message.substring(0, 16));
      playAudio(1, 7);
      beep(2, 250);
    }
  #else
    if (status == "success" || status == "info") {
      if (action_code == "CHECK_IN") {
        printCenter(0, displayNama != "" ? displayNama : "* PRESENSI MASUK *");
        printCenter(1, kelas != "" ? (kelas.length() <= 20 ? kelas : kelas.substring(0, 20)) : "SISWA / GURU");
        printCenter(2, "JAM MASUK: " + waktu.substring(0, 5) + " WIB");
        if (kelas.indexOf("Guru") >= 0 || kelas.indexOf("Staf") >= 0) {
          printCenter(3, ">>SELAMAT BERTUGAS<<");
          playAudio(1, 5);
        } else {
          printCenter(3, "* SELAMAT BELAJAR! *");
          playAudio(1, 2);
        }
        beep(1, 120);
      } else if (action_code == "CHECK_OUT") {
        printCenter(0, displayNama != "" ? displayNama : "* PRESENSI PULANG *");
        printCenter(1, kelas != "" ? (kelas.length() <= 20 ? kelas : kelas.substring(0, 20)) : "SISWA / GURU");
        printCenter(2, "JAM PULANG " + waktu.substring(0, 5) + " WIB");
        printCenter(3, "*HATI-HATI DI JALAN*");
        playAudio(1, 3);
        beep(2, 80);
      } else if (action_code == "COOLDOWN") {
        printCenter(0, displayNama != "" ? displayNama : "** SUDAH ABSEN **");
        printCenter(1, kelas != "" ? (kelas.length() <= 20 ? kelas : kelas.substring(0, 20)) : "SISWA / GURU");
        printCenter(2, "SUDAH ABSEN MASUK!");
        printCenter(3, "PUKUL: " + waktu.substring(0, 5) + " WIB");
        playAudio(1, 4);
        beep(1, 200);
      } else if (action_code == "NEW_CARD") {
        sendScanBuffer(uid);
        printCenter(0, "** KARTU BARU **");
        printCenter(1, "ID: " + uid);
        printCenter(2, "BELUM TERDAFTAR!");
        printCenter(3, "DAFTARKAN DI ADMIN");
        playAudio(1, 6);
        beep(3, 100);
      } else {
        printCenter(0, displayNama != "" ? displayNama : "** BERHASIL **");
        printCenter(1, kelas != "" ? (kelas.length() <= 20 ? kelas : kelas.substring(0, 20)) : message);
        printCenter(2, message.length() <= 20 ? message : message.substring(0, 20));
        printCenter(3, waktu != "" ? ("PUKUL: " + waktu.substring(0, 5) + " WIB") : "TERIMA KASIH");
        playAudio(1, 2);
        beep(1, 100);
      }
    } else {
      printCenter(0, "! PERINGATAN !");
      printCenter(1, displayNama != "" ? displayNama : "AKSES DITOLAK");
      printCenter(2, message.length() <= 20 ? message : message.substring(0, 20));
      printCenter(3, "SILAKAN COBA LAGI!");
      playAudio(1, 7);
      beep(2, 250);
    }
  #endif

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
  printCenter(1, msg.length() <= LCD_COLS ? msg : msg.substring(0, LCD_COLS));
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
