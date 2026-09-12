// ============================================================
//  FIRMWARE ESP32-C3 - PEMBDAHUB ATTENDANCE STATION
//  Versi: RFID + LCD 16x2 + Buzzer + 2 LED + MP3 Player
//  Koneksi: Anti-Hang, Anti-Crash & High-Stability WiFi
// ============================================================

#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include <ArduinoJson.h>

// ============================================================
//  KONFIGURASI WIFI & SERVER
// ============================================================
const char* WIFI_SSID         = "PembdaLINK";
const char* WIFI_PASSWORD     = "PEMBDA2026";

const char* WIFI_ALT_SSID     = "Xspace";
const char* WIFI_ALT_PASSWORD = "12345678starlink";

const char* WIFI_ALT2_SSID    = "TEFA";
const char* WIFI_ALT2_PASSWORD= "PEMBDA2026";

const char* WIFI_ALT3_SSID    = "VISTAFAMILY";
const char* WIFI_ALT3_PASSWORD= "pelita31";

const char* SERVER_URL        = "https://perguruanpembda.com/api/attendance/rfid-scan";
const char* KIOSK_API_KEY     = "RAHASIA-PEMBDAHUB-12345";

const char* DEVICE_ID         = "STATIIN-SMK-03";

// ============================================================
//  PIN DEFINITIONS - ESP32-C3
// ============================================================
#define RFID_SS_PIN     7
#define RFID_SCK_PIN    4
#define RFID_MOSI_PIN   6
#define RFID_MISO_PIN   5
#define RFID_RST_PIN    10

#define I2C_SDA_PIN     8
#define I2C_SCL_PIN     9
#define LCD_ADDRESS     0x27
#define LCD_COLS        16
#define LCD_ROWS        2

#define MP3_TX_PIN      21
#define MP3_RX_PIN      -1 
#define MP3_VOLUME      22

#define BUZZER_PIN      2
#define LED_GREEN_PIN   0
#define LED_RED_PIN     1

#define HTTP_TIMEOUT        8000
#define DISPLAY_RESULT_MS   3500
#define SCAN_COOLDOWN_MS    3000

// ============================================================
//  GLOBAL STATE
// ============================================================
String        lastUID          = "";
unsigned long lastTapTime      = 0;
unsigned long lastWiFiCheck    = 0;
bool          isOnline         = false;

MFRC522           rfid(RFID_SS_PIN, RFID_RST_PIN);
LiquidCrystal_I2C lcd(LCD_ADDRESS, LCD_COLS, LCD_ROWS);
HardwareSerial    mp3Serial(1);

// ============================================================
//  SETUP
// ============================================================
void setup() {
  Serial.begin(115200);
  delay(1000);
  
  Serial.println(F("\n=================================="));
  Serial.println(F("=== ESP32-C3 STATION BOOTING ==="));
  Serial.println(F("=================================="));
  Serial.print(F("Device ID: ")); Serial.println(DEVICE_ID);

  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(LED_GREEN_PIN, OUTPUT);
  pinMode(LED_RED_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);
  digitalWrite(LED_GREEN_PIN, LOW);
  digitalWrite(LED_RED_PIN, LOW);

  // 1. Inisialisasi I2C & LCD
  Wire.begin(I2C_SDA_PIN, I2C_SCL_PIN);
#ifdef FDB_LIQUID_CRYSTAL_I2C_H
  lcd.begin();
#else
  lcd.init();
#endif
  lcd.backlight();
  lcd.setCursor(0, 0); lcd.print(F("   PEMBDA HUB   "));
  lcd.setCursor(0, 1); lcd.print(F(" Memulai Sistem "));
  delay(1500);

  // 2. KONEKSI WIFI TERLEBIH DAHULU (Sebelum RF RC522 & MP3 menyedot arus/interferensi)
  connectWiFi();

  // 3. Inisialisasi SPI & RFID RC522
  SPI.begin(RFID_SCK_PIN, RFID_MISO_PIN, RFID_MOSI_PIN, RFID_SS_PIN);
  SPI.setFrequency(1000000); 
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
    beep(5, 100, LED_RED_PIN);
    delay(2000);
  } else {
    Serial.println(F("RFID OK!"));
    rfid.PCD_SetAntennaGain(rfid.RxGain_max);
    delay(10);
    rfid.PCD_AntennaOn();
  }

  // 4. Inisialisasi MP3 Player
  mp3Serial.begin(9600, SERIAL_8N1, MP3_RX_PIN, MP3_TX_PIN);
  delay(100);
  setMp3Volume(MP3_VOLUME);
  playAudio(1, 1);

  showReady();
  Serial.println(F("System Ready. Silakan scan kartu RFID."));
}

// ============================================================
//  LOOP UTAMA
// ============================================================
void loop() {
  unsigned long now = millis();

  // Cek koneksi WiFi setiap 15 detik
  if (now - lastWiFiCheck >= 15000 || lastWiFiCheck == 0) {
    lastWiFiCheck = now;
    if (WiFi.status() == WL_CONNECTED) {
      if (!isOnline) {
        isOnline = true;
        Serial.print(F("WiFi Terhubung! IP: "));
        Serial.println(WiFi.localIP());
        showReady();
      }
    } else {
      if (isOnline) {
        isOnline = false;
        Serial.println(F("WiFi Terputus! Mencoba nyambung ulang..."));
        showReady();
      }
      WiFi.reconnect();
    }
  }

  handleRfidScan();
  delay(20);
}

// ============================================================
//  KONEKSI WIFI YANG DIPERBAIKI (ANTI-CRASH & ANTI-TIMEOUT)
// ============================================================
void connectWiFi() {
  WiFi.disconnect(false);
  delay(200);

  WiFi.mode(WIFI_STA);
  WiFi.setSleep(false); 
  WiFi.setTxPower(WIFI_POWER_17dBm);
  
  const char* ssids[] = { WIFI_SSID, WIFI_ALT_SSID, WIFI_ALT2_SSID, WIFI_ALT3_SSID };
  const char* passes[] = { WIFI_PASSWORD, WIFI_ALT_PASSWORD, WIFI_ALT2_PASSWORD, WIFI_ALT3_PASSWORD };
  
  for(int net = 0; net < 4; net++) {
    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(F("MENCARI WIFI... "));
    lcd.setCursor(0, 1); lcd.print("-> "); lcd.print(ssids[net]);
    
    Serial.print("\nMencoba koneksi ke SSID: "); Serial.println(ssids[net]);
    
    WiFi.begin(ssids[net], passes[net]);
    
    int attempt = 0;
    while (WiFi.status() != WL_CONNECTED && attempt < 30) {
      delay(500);
      Serial.print(".");
      attempt++;
    }
    Serial.println();
    
    if (WiFi.status() == WL_CONNECTED) {
      break; 
    } else {
      Serial.println(F("Gagal terhubung, memutuskan sesi sebelum coba berikutnya..."));
      WiFi.disconnect(false);
      delay(500);
    }
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.print(F("Terhubung ke: ")); Serial.println(WiFi.SSID());
    Serial.print(F("IP Address: ")); Serial.println(WiFi.localIP());
    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(F("WIFI CONNECTED! "));
    lcd.setCursor(0, 1); lcd.print(WiFi.localIP().toString());
    isOnline = true;
    beep(1, 200, LED_GREEN_PIN);
    delay(1500);
  } else {
    Serial.println(F("GAGAL TERHUBUNG KE SEMUA JARINGAN!"));
    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(F("WIFI GAGAL!     "));
    lcd.setCursor(0, 1); lcd.print(F("Auto-retry bg..."));
    isOnline = false;
    beep(3, 100, LED_RED_PIN);
    delay(2000);
  }
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
  Serial.print("RFID Tapped: "); Serial.println(uid);

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
  beep(1, 100, LED_GREEN_PIN);

  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();

  sendToServer(uid, "rfid");
  lastUID = "";
}

// ============================================================
//  KIRIM DATA KE SERVER
// ============================================================
void sendToServer(String uid, String type) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("Gagal kirim, WiFi terputus!");
    showError("Internet Offline");
    return;
  }

  Serial.println("Mengirim data ke server...");
  WiFiClientSecure client;
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
  Serial.print("HTTP Code: "); Serial.println(code);

  if (code == 200 || code == 201) {
    String payload = http.getString();
    Serial.println("Respon: " + payload);
    parseAndDisplay(payload);
  } else if (code == 401) {
    playAudio(1, 7); 
    showError("API Key Invalid!");
  } else if (code == 404) {
    playAudio(1, 6); 
    showError("Kartu Tdk Kenal ");
  } else if (code > 0) {
    playAudio(1, 7); 
    showError("Server Err " + String(code));
  } else {
    String errMsg = http.errorToString(code);
    Serial.print("HTTP Error: "); Serial.println(errMsg);
    String payload = http.getString();
    
    if (payload.length() > 10 && payload.indexOf("status") > 0) {
      parseAndDisplay(payload);
    } else {
      playAudio(1, 7); 
      showError("Gagal Terhubung ");
    }
  }

  http.end();
  delay(DISPLAY_RESULT_MS);
  showReady();
}

// ============================================================
//  PARSE RESPONSE JSON
// ============================================================
void parseAndDisplay(String json) {
  StaticJsonDocument<256> doc;
  if (deserializeJson(doc, json)) {
    Serial.println("Gagal parse JSON!");
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
    String kelasDisplay = kelas.substring(0, min((int)kelas.length(), 16));
    String waktuShort   = waktu.substring(0, min((int)waktu.length(), 11)); 

    lcd.clear();
    
    if (action_code == "CHECK_IN") {
      lcd.setCursor(0, 0); lcd.print(namaDisplay);
      lcd.setCursor(0, 1); lcd.print("Masuk: " + waktuShort);
      if (kelasDisplay.indexOf("Guru") >= 0 || kelasDisplay.indexOf("Staf") >= 0 || kelasDisplay.indexOf("Staff") >= 0 || kelasDisplay.indexOf("Tefa") >= 0) {
        playAudio(1, 5);
      } else {
        playAudio(1, 2);
      }
      indicatorCheckIn(); 
    }
    else if (action_code == "CHECK_OUT") {
      lcd.setCursor(0, 0); lcd.print(namaDisplay);
      lcd.setCursor(0, 1); lcd.print("Pulang:" + waktuShort);
      playAudio(1, 3);
      indicatorCheckOut(); 
    }
    else if (action_code == "COOLDOWN") {
      lcd.setCursor(0, 0); lcd.print(F("Sudah Absen!    "));
      if (waktu != "") {
        lcd.setCursor(0, 1); lcd.print("Jam: " + waktuShort);
      } else {
        lcd.setCursor(0, 1); lcd.print(message.substring(0, 16));
      }
      playAudio(1, 4);
      indicatorCooldown(); 
    }
    else if (action_code == "NEW_CARD") {
      lcd.setCursor(0, 0); lcd.print(F("KARTU BARU      "));
      lcd.setCursor(0, 1); lcd.print(F("Belum Terdaftar "));
      playAudio(1, 6);
      indicatorNewCard(); 
    }
    else {
      lcd.setCursor(0, 0); lcd.print(message.substring(0, 16));
      lcd.setCursor(0, 1); lcd.print("Waktu: " + waktuShort);
      playAudio(1, 2); 
      indicatorCheckIn();  
    }
  } else {
    String errMsg = (action_code == "ALREADY_ATTENDED") ? "Sudah Absen     " : message;
    if (action_code == "ALREADY_ATTENDED") {
      playAudio(1, 4); 
    } else {
      playAudio(1, 7); 
    }
    showError(errMsg);
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
//  INDIKATOR BUZZER & LED
// ============================================================
void toggleIndicator(bool state, uint8_t pinLed) {
  digitalWrite(BUZZER_PIN, state ? HIGH : LOW);
  digitalWrite(pinLed, state ? HIGH : LOW);
}

void indicatorCheckIn() {
  for (int i = 0; i < 2; i++) {
    toggleIndicator(true, LED_GREEN_PIN); delay(100);
    toggleIndicator(false, LED_GREEN_PIN); 
    if (i < 1) delay(100);
  }
}

void indicatorCheckOut() {
  for (int i = 0; i < 3; i++) {
    toggleIndicator(true, LED_GREEN_PIN); delay(80);
    toggleIndicator(false, LED_GREEN_PIN); 
    if (i < 2) delay(80);
  }
}

void indicatorCooldown() {
  toggleIndicator(true, LED_GREEN_PIN); delay(300);
  toggleIndicator(false, LED_GREEN_PIN);
}

void indicatorNewCard() {
  for (int i = 0; i < 4; i++) {
    toggleIndicator(true, LED_RED_PIN); delay(60);
    toggleIndicator(false, LED_RED_PIN); 
    if (i < 3) delay(60);
  }
}

void indicatorFail() {
  toggleIndicator(true, LED_RED_PIN); delay(600);
  toggleIndicator(false, LED_RED_PIN);
}

void beep(int count, int duration, uint8_t pinLed) {
  for (int i = 0; i < count; i++) {
    toggleIndicator(true, pinLed); delay(duration);
    toggleIndicator(false, pinLed);
    if (i < count - 1) delay(100);
  }
}

// ============================================================
//  FUNGSI MP3 PLAYER RAW COMMANDS
// ============================================================
void sendMp3Command(uint8_t cmd, uint8_t para1, uint8_t para2) {
  uint8_t cmdBuffer[8] = { 0x7E, 0xFF, 0x06, cmd, 0x00, para1, para2, 0xEF };
  mp3Serial.write(cmdBuffer, 8);
  delay(100); 
}

void playAudio(uint8_t folder, uint8_t track) {
  sendMp3Command(0x0F, folder, track);
}

void setMp3Volume(uint8_t vol) {
  if (vol > 30) vol = 30;
  sendMp3Command(0x06, 0x00, vol);
}

// ============================================================
//  BACA UID RFID (FORMAT HEXADECIMAL 12 KARAKTER)
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


