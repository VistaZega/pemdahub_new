# BAB 4: KODE FIRMWARE & PENJELASAN TEKNIS

---

## 4.1 Persiapan Lingkungan Pengembangan (Arduino IDE Setup)

### 1. Board Manager URL (ESP8266 Core)
Pastikan URL Board Manager ESP8266 telah ditambahkan di **Arduino IDE ➜ File ➜ Preferences ➜ Additional Boards Manager URLs**:
```
http://arduino.esp8266.com/stable/package_esp8266com_index.json
```

### 2. Pengaturan Kompilator (Tools Menu):
* **Board:** `NodeMCU 1.0 (ESP-12E Module)`
* **CPU Frequency:** `80 MHz` (atau `160 MHz` untuk performa enkripsi TLS lebih cepat)
* **Flash Size:** `4MB (FS:2MB OTA:~1019KB)`
* **Upload Speed:** `115200`
* **Port:** Pilih COM Port yang sesuai dengan kabel NodeMCU Anda.

### 3. Library yang Wajib Diinstal (via Library Manager):
1. **`MFRC522`** by GithubCommunity (v1.4.10 atau terbaru)
2. **`LiquidCrystal_I2C`** by Frank de Brabander / Marcoschwartz
3. **`ArduinoJson`** by Benoit Blanchon (Wajib versi **6.x**, misal `v6.21.3`)

---

## 4.2 Struktur Audio MicroSD Card (Folder `/01/`)
Format kartu MicroSD sebagai **FAT32**. Buat folder bernama `01` di direktori utama (*root*), lalu isi dengan 7 file MP3 berikut:

```
MicroSD Card/
└── 01/
    ├── 001.mp3  -> "Selamat Pagi, Silakan Lakukan Absensi" (Saat stasiun booting)
    ├── 002.mp3  -> "Akses Diterima, Selamat Belajar" (Siswa Absen Masuk)
    ├── 003.mp3  -> "Absen Pulang Berhasil, Sampai Jumpa" (Absen Pulang)
    ├── 004.mp3  -> "Absensi Anda Sudah Tercatat Hari Ini" (Cooldown / Sudah Hadir)
    ├── 005.mp3  -> "Selamat Pagi, Selamat Bertugas" (Guru / Staf Absen Masuk)
    ├── 006.mp3  -> "Kartu Tidak Dikenali, Hubungi Admin" (Kartu Baru Belum Terdaftar)
    └── 007.mp3  -> "Sistem Ada Gangguan, Hubungi Admin" (Koneksi WiFi / Server Error)
```

---

## 4.3 Source Code Lengkap Firmware (`Station_NodeMCU_20x4.ino`)

```cpp
// ============================================================
//  FIRMWARE NODEMCU V3 (ESP-12F) - PEMBDAHUB ATTENDANCE STATION
//  Station Absen Terpadu: RFID RC522 + GM65 QR Scanner + LCD 20x4 I2C + DFPlayer MP3 + Buzzer
//  Fitur Spesial: Smart Screensaver Marquee (Slogan Yayasan Pembda Nias)
// ============================================================

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

// ============================================================
//  KONFIGURASI JARINGAN & SERVER
// ============================================================

// WiFi Utama
const char* WIFI_SSID          = "Xspace";
const char* WIFI_PASSWORD      = "12345678starlink";

// WiFi Cadangan 1 (Auto-Fallback)
const char* WIFI_ALT_SSID      = "TEFA";
const char* WIFI_ALT_PASSWORD  = "PEMBDA2026";

// WiFi Cadangan 2
const char* WIFI_ALT2_SSID     = "VISTAFAMILY";
const char* WIFI_ALT2_PASSWORD = "pelita31";

// Server API Endpoint
const char* SERVER_URL         = "https://perguruanpembda.com/api/attendance/rfid-scan";
const char* KIOSK_API_KEY      = "RAHASIA-PEMBDAHUB-12345";

// Identitas Perangkat (Ubah untuk setiap stasiun)
const char* DEVICE_ID          = "STATION-SMA-01";

// ============================================================
//  PIN DEFINITIONS & KONFIGURASI PERIFERAL
// ============================================================
#define RFID_SS_PIN    16   // D0 (GPIO16) - SPI CS
#define RFID_RST_PIN  255   // Unused (RST RFID ke 3.3V)
#define MP3_TX_PIN      2   // D4 (GPIO2) - Ke RX DFPlayer Mini via resistor 1K
#define MP3_VOLUME     30   // Tingkat Volume Audio (0 s.d 30)
#define BUZZER_PIN     15   // D8 (GPIO15) - Buzzer Aktif 5V
#define QR_RX_PIN       0   // D3 (GPIO0)  - Menerima Data TX GM65 Scanner
#define LCD_ADDRESS    0x27 // Alamat I2C LCD
#define LCD_COLS       20
#define LCD_ROWS       4

#define HTTP_TIMEOUT        10000   // 10 detik timeout HTTPS
#define DISPLAY_RESULT_MS   3500    // Durasi tayang hasil scan di LCD
#define SCAN_COOLDOWN_MS    3000    // Proteksi anti double-tap
#define QR_MIN_LENGTH       3       // Minimal panjang karakter QR
#define IDLE_TIMEOUT_MS     20000   // 20 detik tanpa aktivitas -> Aktifkan Running Text

// ============================================================
//  CUSTOM PIXEL CHARACTERS UNTUK LCD (5x8 Dots)
// ============================================================
byte iconWifi[8] = {
  B00000,
  B01110,
  B10001,
  B00100,
  B01010,
  B00000,
  B00100,
  B00000
};

byte iconCard[8] = {
  B11111,
  B10001,
  B10101,
  B10001,
  B11111,
  B00000,
  B00000,
  B00000
};

byte iconHeart[8] = {
  B00000,
  B01010,
  B11111,
  B11111,
  B01110,
  B00100,
  B00000,
  B00000
};

// Global State
String        lastUID          = "";
unsigned long lastTapTime      = 0;
String        qrBuffer         = "";
unsigned long qrPauseUntil     = 0;
unsigned long lastWiFiCheck    = 0;
bool          isOnline         = false;

// Animasi & Smart Screensaver State
unsigned long lastActivityTime = 0;
unsigned long lastAnimTime     = 0;
int           animFrame        = 0;
bool          isShowingResult  = false;
bool          isScreensaver    = false;

// Teks Berjalan Slogan Resmi Yayasan Perguruan Pembda Nias
const String marqueeText = "   *** YAYASAN PERGURUAN PEMBDA NIAS *** Keep Moving Forward - Maju Terus Pantang Mundur! *** SMP - SMA - SMK Swasta Pembda *** Silakan Tempel Kartu RFID / Scan QR Code ***   ";
int          marqueePos        = 0;
unsigned long lastMarqueeTime  = 0;

// Hardware Objects
MFRC522           rfid(RFID_SS_PIN, RFID_RST_PIN);
LiquidCrystal_I2C lcd(LCD_ADDRESS, LCD_COLS, LCD_ROWS);
ESP8266WiFiMulti  wifiMulti;
SoftwareSerial    kioskSerial(QR_RX_PIN, MP3_TX_PIN);

// Prototipe Fungsi
void playAudio(uint8_t folder, uint8_t track);
void setMp3Volume(uint8_t vol);
void showReady();
void showScreensaverBase();
void updateLcdAnimation();
void updateMarquee();
void showError(String msg);
void beep(int count, int duration);
void handleRfidScan();
void handleQrScan();
void sendToServer(String uid, String type);
void parseAndDisplay(String json);
void connectWiFi();
void indicatorCheckIn();
void indicatorCheckOut();
void indicatorCooldown();
void indicatorNewCard();
void indicatorFail();
String getRfidUID();

// ============================================================
//  SETUP ROUTINE
// ============================================================
void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println(F("\n=== NODEMCU STATION BOOTING ==="));
  Serial.println(F("Board: NodeMCU V3 (ESP-12F)"));
  Serial.print(F("Device ID: ")); Serial.println(DEVICE_ID);

  // Inisialisasi Buzzer
  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);

  // Inisialisasi LCD 20x4 I2C (SDA=D2, SCL=D1)
  Wire.begin(4, 5);
  lcd.begin();
  lcd.backlight();

  // Daftarkan Custom Characters ke memori LCD
  lcd.createChar(0, iconWifi);
  lcd.createChar(1, iconCard);
  lcd.createChar(2, iconHeart);

  lcd.setCursor(0, 0); lcd.print(F("===================="));
  lcd.setCursor(0, 1); lcd.print(F("  PEMBDAHUB KIOSK   "));
  lcd.setCursor(0, 2); lcd.print(F(" Memulai Sistem...  "));
  lcd.setCursor(0, 3); lcd.print(F("===================="));
  delay(1500);

  // Inisialisasi Bus SPI & RFID RC522
  SPI.begin();
  SPI.setFrequency(1000000); // 1MHz timing stabil untuk chip clone
  delay(50);
  rfid.PCD_Init();
  delay(150);

  byte version = rfid.PCD_ReadRegister(rfid.VersionReg);
  if (version == 0x00 || version == 0xFF) {
    Serial.println(F("WARNING: RFID tidak terdeteksi! Cek wiring SPI."));
    lcd.setCursor(0, 2); lcd.print(F("RFID ERROR! Cek SPI "));
    beep(5, 100);
    delay(2000);
  } else {
    rfid.PCD_SetAntennaGain(rfid.RxGain_max); // Sensitivitas antena maksimal (48dB)
    rfid.PCD_AntennaOn();
  }

  // Koneksi WiFi Multi-AP
  wifiMulti.addAP(WIFI_SSID, WIFI_PASSWORD);
  wifiMulti.addAP(WIFI_ALT_SSID, WIFI_ALT_PASSWORD);
  wifiMulti.addAP(WIFI_ALT2_SSID, WIFI_ALT2_PASSWORD);
  connectWiFi();
  isOnline = (WiFi.status() == WL_CONNECTED);
  
  lastActivityTime = millis();
  showReady();

  // Inisialisasi Serial GM65 & DFPlayer
  kioskSerial.begin(9600);
  delay(100);
  while (kioskSerial.available()) kioskSerial.read();
  qrBuffer = "";

  setMp3Volume(MP3_VOLUME);
  playAudio(1, 1); // Putar sapaan 001.mp3: "Selamat Pagi, Silakan Absen"
}

// ============================================================
//  MAIN LOOP
// ============================================================
void loop() {
  unsigned long now = millis();

  // 1. Pemeriksaan Kesehatan WiFi Setiap 10 Detik
  if (now - lastWiFiCheck >= 10000 || lastWiFiCheck == 0) {
    lastWiFiCheck = now;
    if (wifiMulti.run() == WL_CONNECTED) {
      if (!isOnline) {
        isOnline = true;
        if (isScreensaver) showScreensaverBase();
        else showReady();
      }
    } else {
      if (isOnline) {
        isOnline = false;
        if (isScreensaver) showScreensaverBase();
        else showReady();
      }
    }
  }

  // 2. Transisi Otomatis ke Screensaver Running Text jika 20 detik tidak ada aktivitas
  if (!isShowingResult) {
    if (!isScreensaver && (now - lastActivityTime >= IDLE_TIMEOUT_MS)) {
      isScreensaver = true;
      marqueePos = 0;
      showScreensaverBase();
    }
    
    if (isScreensaver) {
      updateMarquee();
    } else {
      updateLcdAnimation();
    }
  }

  // 3. Cek Scanner RFID
  handleRfidScan();

  // 4. Cek Scanner Barcode / QR
  handleQrScan();

  delay(10);
}

// ============================================================
//  PEMROSESAN KARTU RFID (INSTANT WAKEUP)
// ============================================================
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
  lastUID          = uid;
  lastTapTime      = now;
  lastActivityTime = now;
  isScreensaver    = false; // Bangun dari mode screensaver

  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();

  isShowingResult = true;
  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("=== MEMPROSES ======"));
  lcd.setCursor(0, 1); lcd.print(F("Membaca kartu RFID  "));
  lcd.setCursor(0, 2); lcd.print("UID: " + uid);
  lcd.setCursor(0, 3); lcd.print(F("Mohon tunggu...     "));
  beep(1, 100);

  sendToServer(uid, "rfid");
  lastUID = "";
}

// ============================================================
//  PEMROSESAN BARCODE & QR CODE (INSTANT WAKEUP)
// ============================================================
void handleQrScan() {
  if (millis() < qrPauseUntil) {
    while (kioskSerial.available()) kioskSerial.read();
    return;
  }

  int noiseCount = 0;
  int maxRead = 5;

  for (int i = 0; i < maxRead && kioskSerial.available() > 0; i++) {
    char c = kioskSerial.read();

    if (c == '\r' || c == '\n') {
      if (qrBuffer.length() >= QR_MIN_LENGTH) {
        String qrData = qrBuffer;
        qrData.trim();
        qrBuffer = "";

        unsigned long now = millis();
        if (qrData == lastUID && (now - lastTapTime) < SCAN_COOLDOWN_MS) {
          return;
        }
        lastUID          = qrData;
        lastTapTime      = now;
        lastActivityTime = now;
        isScreensaver    = false; // Bangun dari mode screensaver

        isShowingResult = true;
        lcd.clear();
        lcd.setCursor(0, 0); lcd.print(F("=== MEMPROSES ======"));
        lcd.setCursor(0, 1); lcd.print(F("Kode QR Terbaca     "));
        lcd.setCursor(0, 2); lcd.print("ID: " + qrData.substring(0, min((int)qrData.length(), 16)));
        lcd.setCursor(0, 3); lcd.print(F("Menghubungi server.."));
        beep(1, 150);

        sendToServer(qrData, "qr");
        lastUID = "";
        return;
      }
      qrBuffer = "";
    }
    else if (c >= 32 && c <= 126) {
      noiseCount = 0;
      if (qrBuffer.length() < 128) qrBuffer += c;
      else qrBuffer = "";
    }
    else {
      noiseCount++;
      if (noiseCount >= 3) {
        qrPauseUntil = millis() + 1000;
        qrBuffer = "";
        while (kioskSerial.available()) kioskSerial.read();
        return;
      }
    }
  }
}

// ============================================================
//  PENGIRIMAN DATA KE SERVER PEMBDAHUB (HTTPS POST)
// ============================================================
void sendToServer(String uid, String type) {
  isShowingResult = true;
  if (WiFi.status() != WL_CONNECTED) {
    playAudio(1, 7);
    showError("Koneksi Internet Off");
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
    playAudio(1, 7);
    showError("API Key Tidak Valid!");
  } else if (code == 404) {
    playAudio(1, 6);
    showError("Kartu/QR Tdk Terdaftar");
  } else {
    playAudio(1, 7);
    showError("Gagal Server: " + String(code));
  }

  http.end();
  while (kioskSerial.available()) kioskSerial.read();
  qrBuffer = "";
  
  delay(DISPLAY_RESULT_MS);
  lastActivityTime = millis();
  isScreensaver    = false;
  showReady();
}

// ============================================================
//  PARSER JSON RESPONSE & EKSEKUSI RESPON AUDIO/VISUAL
// ============================================================
void parseAndDisplay(String json) {
  StaticJsonDocument<256> doc;
  if (deserializeJson(doc, json)) {
    showError("Format Data Rusak");
    return;
  }

  String status      = doc["status"]      | "error";
  String nama        = doc["nama"]        | "Tidak dikenal";
  String kelas       = doc["kelas"]       | "";
  String message     = doc["message"]     | "";
  String waktu       = doc["waktu"]       | "";
  String action_code = doc["action_code"] | "";

  if (status == "success" || status == "info") {
    String namaDisplay  = nama.substring(0, min((int)nama.length(), 20));
    String kelasDisplay = kelas.substring(0, min((int)kelas.length(), 20));

    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(namaDisplay);
    lcd.setCursor(0, 1); lcd.print(kelasDisplay != "" ? kelasDisplay : "-");

    if (action_code == "CHECK_IN") {
      lcd.setCursor(0, 2); lcd.print("MASUK PADA: " + waktu);
      if (kelasDisplay.indexOf("Guru") >= 0 || kelasDisplay.indexOf("Staf") >= 0) {
        lcd.setCursor(0, 3); lcd.print(F("Selamat Bertugas!   "));
        playAudio(1, 5); // 005.mp3: Guru/Staf
      } else {
        lcd.setCursor(0, 3); lcd.print(F("Selamat Belajar!    "));
        playAudio(1, 2); // 002.mp3: Siswa
      }
      indicatorCheckIn();
    }
    else if (action_code == "CHECK_OUT") {
      lcd.setCursor(0, 2); lcd.print("PULANG PADA: " + waktu);
      lcd.setCursor(0, 3); lcd.print(F("Hati-hati di jalan! "));
      playAudio(1, 3); // 003.mp3
      indicatorCheckOut();
    }
    else if (action_code == "COOLDOWN") {
      lcd.setCursor(0, 2); lcd.print(F("Sudah Absen Masuk!  "));
      lcd.setCursor(0, 3); lcd.print(waktu != "" ? ("Jam: " + waktu) : message);
      playAudio(1, 4); // 004.mp3
      indicatorCooldown();
    }
    else if (action_code == "NEW_CARD") {
      lcd.clear();
      lcd.setCursor(0, 0); lcd.print(F("** KARTU BARU **    "));
      lcd.setCursor(0, 1); lcd.print(kelasDisplay);
      lcd.setCursor(0, 2); lcd.print(F("Belum terdaftar!    "));
      lcd.setCursor(0, 3); lcd.print(F("Daftarkan di Admin  "));
      playAudio(1, 6); // 006.mp3
      indicatorNewCard();
    }
  } else {
    playAudio(1, 7);
    showError(message != "" ? message : "Gagal Absen");
  }
}

// ============================================================
//  FUNGSI DISPLAY STANDBY & SMART SCREENSAVER (MARQUEE)
// ============================================================

void showReady() {
  isShowingResult = false;
  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("* PEMBDA PRESENSI * "));
  lcd.setCursor(0, 1); lcd.write(byte(1)); lcd.print(F(" Tempel Kartu / QR "));
  lcd.setCursor(0, 3);
  if (isOnline) {
    lcd.write(byte(0)); lcd.print(F(" ON:"));
    String ssid = WiFi.SSID();
    if (ssid.length() > 10) ssid = ssid.substring(0, 10);
    lcd.print(ssid);
  } else {
    lcd.print(F("[OFFLINE] Cari AP..."));
  }
}

void updateLcdAnimation() {
  if (isShowingResult || isScreensaver) return;

  unsigned long now = millis();
  if (now - lastAnimTime < 350) return;
  lastAnimTime = now;

  const char* frames[] = {
    "   >> RFID / QR <<  ",
    "  >>> RFID / QR <<< ",
    " >>>> RFID / QR <<<<",
    "  >>> RFID / QR <<< ",
    "   >> RFID / QR <<  ",
    "    > RFID / QR <   "
  };

  lcd.setCursor(0, 2);
  lcd.print(frames[animFrame]);
  animFrame = (animFrame + 1) % 6;

  lcd.setCursor(19, 3);
  if (animFrame % 2 == 0) lcd.write(byte(2));
  else lcd.print(F(" "));
}

void showScreensaverBase() {
  isShowingResult = false;
  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("* PERGURUAN PEMBDA *"));
  lcd.setCursor(0, 1); lcd.print(F("SMP - SMA - SMK NIAS"));
  lcd.setCursor(0, 3);
  if (isOnline) {
    lcd.write(byte(0)); lcd.print(F(" "));
    String ssid = WiFi.SSID();
    if (ssid.length() > 8) ssid = ssid.substring(0, 8);
    lcd.print(ssid);
    lcd.setCursor(12, 3);
    lcd.print(F("SCAN"));
  } else {
    lcd.print(F("[OFFLINE]   SCAN"));
  }
}

void updateMarquee() {
  if (isShowingResult || !isScreensaver) return;

  unsigned long now = millis();
  if (now - lastMarqueeTime < 220) return;
  lastMarqueeTime = now;

  String windowText = "";
  int textLen = marqueeText.length();
  for (int i = 0; i < 20; i++) {
    int idx = (marqueePos + i) % textLen;
    windowText += marqueeText[idx];
  }

  lcd.setCursor(0, 2);
  lcd.print(windowText);

  marqueePos = (marqueePos + 1) % textLen;

  lcd.setCursor(19, 3);
  if ((marqueePos / 2) % 2 == 0) lcd.write(byte(2));
  else lcd.print(F(" "));
}

// ============================================================
//  HELPER AUDIO MP3 COMMANDS
// ============================================================
void sendMp3Command(uint8_t cmd, uint8_t para1, uint8_t para2) {
  uint8_t cmdBuffer[8] = { 0x7E, 0xFF, 0x06, cmd, 0x00, para1, para2, 0xEF };
  kioskSerial.write(cmdBuffer, 8);
  delay(100);
}

void playAudio(uint8_t folder, uint8_t track) {
  sendMp3Command(0x0F, folder, track);
}

void setMp3Volume(uint8_t vol) {
  sendMp3Command(0x06, 0x00, min(vol, (uint8_t)30));
}

// ============================================================
//  FUNGSI BACA UID RFID (HEX FORMAT)
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

// ============================================================
//  FUNGSI DISPLAY & BUZZER
// ============================================================
void showError(String msg) {
  isShowingResult = true;
  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("=== !!! ERROR !!! =="));
  if (msg.length() > 20) {
    lcd.setCursor(0, 1); lcd.print(msg.substring(0, 20));
    lcd.setCursor(0, 2); lcd.print(msg.substring(20, min((int)msg.length(), 40)));
  } else {
    lcd.setCursor(0, 1); lcd.print(msg);
    lcd.setCursor(0, 2); lcd.print(F("Silakan coba lagi   "));
  }
  lcd.setCursor(0, 3); lcd.print(F("--------------------"));
  indicatorFail();
}

void beep(int count, int duration) {
  for (int i = 0; i < count; i++) {
    digitalWrite(BUZZER_PIN, HIGH); delay(duration);
    digitalWrite(BUZZER_PIN, LOW);
    if (i < count - 1) delay(100);
  }
}

void indicatorCheckIn()  { beep(2, 100); }
void indicatorCheckOut() { beep(3, 80); }
void indicatorCooldown() { beep(1, 300); }
void indicatorNewCard()  { beep(4, 60); }
void indicatorFail()     { beep(1, 600); }

void connectWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.persistent(false);
  int attempt = 0;
  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("=== MENCARI WIFI ==="));
  while (wifiMulti.run() != WL_CONNECTED && attempt < 20) {
    delay(500); attempt++;
    lcd.setCursor(0, 2); lcd.print("Menghubungkan... " + String(attempt));
  }
}
```
