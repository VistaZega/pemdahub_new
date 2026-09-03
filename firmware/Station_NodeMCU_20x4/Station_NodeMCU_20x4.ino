// ============================================================
//  FIRMWARE NODEMCU V3 (ESP-12F) - PEMBDAHUB ATTENDANCE STATION
//  Station Absen Terpadu: RFID RC522 + GM65 QR Scanner + LCD 20x4 I2C + DFPlayer MP3 + Buzzer
//  Versi UI: Professional Animated Standby Screen (Non-Blocking)
//  
//  ============================================================
//  TABEL LENGKAP KONEKSI KABEL & PIN HARDWARE (WIRING DIAGRAM)
//  ============================================================
//
//  1. GM65 BARCODE / QR SCANNER (Konektor 4-Pin):
//  ┌──────────────────────┬────────────────┬──────────────────────────┐
//  │ Pin / Tulisan GM65   │ Pin NodeMCU    │ Catatan Penting          │
//  ├──────────────────────┼────────────────┼──────────────────────────┤
//  │ 5V   (Kabel Merah)   │ VU (5V)        │ Tegangan 5V dari port USB│
//  │ GND  (Kabel Hijau)   │ GND            │ Ground bersama           │
//  │ TX   (Kabel Hitam)   │ D3 (GPIO0)     │ Sinyal Data TX GM65 -> D3│
//  │ RX   (Kabel Kuning)  │ BEBAS / KOSONG │ TIDAK BOLEH DICOLOK!     │
//  └──────────────────────┴────────────────┴──────────────────────────┘
//
//  2. RFID RC522 13.56 MHz (SPI Interface):
//  ┌──────────────────────┬────────────────┬──────────────────────────┐
//  │ Pin Modul RC522      │ Pin NodeMCU    │ Catatan Penting          │
//  ├──────────────────────┼────────────────┼──────────────────────────┤
//  │ 3.3V (VCC)           │ 3V3 (3.3V)     │ WAJIB 3.3V (JANGAN 5V!)  │
//  │ RST (Reset)          │ 3V3 (3.3V)     │ Langsung hubungkan 3.3V  │
//  │ GND                  │ GND            │ Ground bersama           │
//  │ MISO                 │ D6 (GPIO12)    │ SPI MISO bawaan ESP8266  │
//  │ MOSI                 │ D7 (GPIO13)    │ SPI MOSI bawaan ESP8266  │
//  │ SCK                  │ D5 (GPIO14)    │ SPI Clock bawaan ESP8266 │
//  │ SDA / SS             │ D0 (GPIO16)    │ SPI Chip Select (CS)     │
//  └──────────────────────┴────────────────┴──────────────────────────┘
//
//  3. LCD 20x4 I2C (Alamat Default 0x27 / 0x3F):
//  ┌──────────────────────┬────────────────┬──────────────────────────┐
//  │ Pin Modul I2C LCD    │ Pin NodeMCU    │ Catatan Penting          │
//  ├──────────────────────┼────────────────┼──────────────────────────┤
//  │ VCC                  │ VU (5V)        │ LCD 20x4 butuh 5V (terang│
//  │ GND                  │ GND            │ Ground bersama           │
//  │ SDA                  │ D2 (GPIO4)     │ I2C Data default         │
//  │ SCL                  │ D1 (GPIO5)     │ I2C Clock default        │
//  └──────────────────────┴────────────────┴──────────────────────────┘
//
//  4. DFPLAYER MINI MP3 PLAYER (Audio Suara Sapaan):
//  ┌──────────────────────┬────────────────┬──────────────────────────┐
//  │ Pin DFPlayer Mini    │ Pin NodeMCU    │ Catatan Penting          │
//  ├──────────────────────┼────────────────┼──────────────────────────┤
//  │ VCC                  │ VU (5V)        │ Daya 5V                  │
//  │ GND                  │ GND            │ Ground bersama           │
//  │ RX                   │ D4 (GPIO2)     │ Melalui Resistor 1K Ohm! │
//  │ SPK_1                │ Speaker (+)    │ Speaker 3W / 8 Ohm       │
//  │ SPK_2                │ Speaker (-)    │ Speaker 3W / 8 Ohm       │
//  └──────────────────────┴────────────────┴──────────────────────────┘
//
//  5. BUZZER AKTIF 5V:
//  ┌──────────────────────┬────────────────┬──────────────────────────┐
//  │ Pin Buzzer           │ Pin NodeMCU    │ Catatan Penting          │
//  ├──────────────────────┼────────────────┼──────────────────────────┤
//  │ Positif (+)          │ D8 (GPIO15)    │ Pin pull-down (aman boot)│
//  │ Negatif (-)          │ GND            │ Ground bersama           │
//  └──────────────────────┴────────────────┴──────────────────────────┘
//
//  ============================================================
//  CATATAN SUMBER DAYA & KESTABILAN:
//  - Gunakan Adaptor Charger minimal 5V 2A berkualitas baik.
//  - Disarankan pasang 1 Elco 470uF/1000uF 16V antara VU (5V) dan GND.
//  - Jangan pasang kabel Kuning (RX GM65) ke pin RX NodeMCU agar
//    tidak bentrok dengan komunikasi USB Serial ke Komputer.
//  ============================================================
//
//  BOARD SETTING DI ARDUINO IDE:
//  - Board      : "NodeMCU 1.0 (ESP-12E Module)"
//  - Flash Size : "4MB (FS:2MB OTA:~1019KB)"  
//  - CPU Freq   : 80 MHz (atau 160 MHz untuk performa)
//  - Upload     : 115200
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
//  KONFIGURASI - Sesuaikan untuk setiap station!
// ============================================================

// WiFi Utama
const char* WIFI_SSID          = "Xspace";
const char* WIFI_PASSWORD      = "12345678starlink";

// WiFi Alternatif 1 (otomatis fallback jika utama gagal)
const char* WIFI_ALT_SSID      = "TEFA";
const char* WIFI_ALT_PASSWORD  = "PEMBDA2026";

// WiFi Alternatif 2
const char* WIFI_ALT2_SSID     = "VISTAFAMILY";
const char* WIFI_ALT2_PASSWORD = "pelita31";

// Server API - JANGAN DIUBAH kecuali domain berubah
const char* SERVER_URL         = "https://perguruanpembda.com/api/attendance/rfid-scan";
const char* KIOSK_API_KEY      = "RAHASIA-PEMBDAHUB-12345";

// ── GANTI DEVICE_ID UNTUK SETIAP STATION! ──
const char* DEVICE_ID          = "STATION-SMA-01";

// ============================================================
//  PIN DEFINITIONS - NodeMCU V3 (ESP-12F)
// ============================================================

// SPI Pins untuk RFID RC522 (menggunakan HSPI default ESP8266)
#define RFID_SS_PIN    16   // D0 (GPIO16) - SPI CS
#define RFID_RST_PIN  255   // UNUSED - Hubungkan pin RST RFID langsung ke 3.3V NodeMCU

// MP3 Player TX Pin (menggunakan SoftwareSerial bersama QR Scanner RX)
#define MP3_TX_PIN      2   // D4 (GPIO2) - Hubungkan ke RX MP3 Player via resistor 1K Ohm
#define MP3_VOLUME     30   // Tingkat volume MP3 (0 s.d 30)

// Buzzer (satu-satunya indikator - Opsi B tanpa LED)
#define BUZZER_PIN     15   // D8 (GPIO15) - pull-down bawaan = buzzer OFF saat boot

// QR Scanner GM65/GM50 via SoftwareSerial (RX only)
#define QR_RX_PIN       0   // D3 (GPIO0)  - UART idle HIGH = boot-safe

// I2C LCD 20x4 (menggunakan pin I2C default ESP8266)
#define LCD_ADDRESS    0x27
#define LCD_COLS       20
#define LCD_ROWS       4

// ============================================================
//  TIMEOUTS & COOLDOWNS
// ============================================================
#define HTTP_TIMEOUT        10000   // 10 detik (ESP8266 TLS)
#define DISPLAY_RESULT_MS   3500    // Durasi tampil hasil di LCD
#define SCAN_COOLDOWN_MS    3000    // Anti double-tap (3 detik)
#define QR_MIN_LENGTH       3       // Minimum panjang QR valid

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

// ============================================================
//  GLOBAL STATE
// ============================================================
String        lastUID          = "";
unsigned long lastTapTime      = 0;
String        qrBuffer         = "";
unsigned long qrPauseUntil     = 0;
unsigned long lastWiFiCheck    = 0;
bool          isOnline         = false;

// Animasi LCD State
unsigned long lastAnimTime     = 0;
int           animFrame        = 0;
bool          isShowingResult  = false;

// ============================================================
//  OBJEK HARDWARE
// ============================================================
MFRC522           rfid(RFID_SS_PIN, RFID_RST_PIN);
LiquidCrystal_I2C lcd(LCD_ADDRESS, LCD_COLS, LCD_ROWS);
ESP8266WiFiMulti  wifiMulti;
SoftwareSerial    kioskSerial(QR_RX_PIN, MP3_TX_PIN); // RX untuk QR Scanner, TX untuk MP3 Player

// Prototipe Fungsi Audio & Display
void playAudio(uint8_t folder, uint8_t track);
void setMp3Volume(uint8_t vol);
void showReady();
void updateLcdAnimation();
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
//  SETUP
// ============================================================
void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println(F("\n=== NODEMCU STATION BOOTING ==="));
  Serial.println(F("Board: NodeMCU V3 (ESP-12F)"));
  Serial.print(F("Device ID: ")); Serial.println(DEVICE_ID);
  Serial.print(F("Free Heap: ")); Serial.println(ESP.getFreeHeap());

  // Inisialisasi Buzzer
  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);

  // Inisialisasi I2C LCD (SDA=GPIO4/D2, SCL=GPIO5/D1)
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
  Serial.println(F("LCD OK."));
  delay(1500);

  // ── INISIALISASI SPI & RFID RC522 ──
  SPI.begin();
  SPI.setFrequency(1000000); // 1MHz timing longgar untuk chip clone
  delay(50);
  
  rfid.PCD_Init();
  delay(150);

  Serial.println(F("Mengecek modul RFID RC522..."));
  byte version = rfid.PCD_ReadRegister(rfid.VersionReg);
  if (version == 0x00 || version == 0xFF) {
    Serial.println(F("WARNING: RFID tidak terdeteksi! Cek wiring SPI."));
    lcd.setCursor(0, 2); lcd.print(F("RFID ERROR! Cek SPI "));
    beep(5, 100);
    delay(2000);
  } else {
    rfid.PCD_SetAntennaGain(rfid.RxGain_max); // Gain antenna MAX 48dB
    rfid.PCD_AntennaOn();
    Serial.println(F("RFID RC522 Siap."));
  }

  // Koneksi WiFi dengan multi-AP
  wifiMulti.addAP(WIFI_SSID, WIFI_PASSWORD);
  wifiMulti.addAP(WIFI_ALT_SSID, WIFI_ALT_PASSWORD);
  wifiMulti.addAP(WIFI_ALT2_SSID, WIFI_ALT2_PASSWORD);
  connectWiFi();
  isOnline = (WiFi.status() == WL_CONNECTED);
  showReady();

  // Inisialisasi SoftwareSerial untuk Kiosk (RX=QR Scanner, TX=MP3 Player)
  kioskSerial.begin(9600);
  delay(100);
  while (kioskSerial.available()) kioskSerial.read();
  qrBuffer = "";

  // Inisialisasi Volume MP3 Player
  setMp3Volume(MP3_VOLUME);

  // Putar lagu pembuka 001.mp3 ("Selamat Pagi Silahkan Absen")
  playAudio(1, 1);

  Serial.print(F("Free Heap setelah init: ")); Serial.println(ESP.getFreeHeap());
  Serial.println(F("System Ready. Silakan scan kartu RFID atau QR Code."));
}

// ============================================================
//  LOOP UTAMA
// ============================================================
void loop() {
  unsigned long now = millis();

  // 1. Periksa status WiFi setiap 10 detik secara non-blocking
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

  // 2. Update Animasi LCD (Non-Blocking)
  updateLcdAnimation();

  // 3. Cek Scanner RFID
  handleRfidScan();

  // 4. Cek Scanner Barcode / QR
  handleQrScan();

  delay(10);
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

  isShowingResult = true; // Kunci layar agar animasi tidak menimpa
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
//  HANDLER QR CODE - ANTI NOISE
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
        lastUID     = qrData;
        lastTapTime = now;

        isShowingResult = true; // Kunci layar
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
      if (qrBuffer.length() < 128) {
        qrBuffer += c;
      } else {
        qrBuffer = "";
      }
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
//  KIRIM DATA KE SERVER
// ============================================================
void sendToServer(String uid, String type) {
  isShowingResult = true;
  Serial.println("Mengirim ke server: " + uid + " (" + type + ")");
  
  if (WiFi.status() != WL_CONNECTED) {
    playAudio(1, 7); // 007.MP3 - Sistem ada gangguan hubungi admin
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
  Serial.print("HTTP Response Code: "); Serial.println(code);

  if (code == 200 || code == 201) {
    String payload = http.getString();
    Serial.println("Server Response: " + payload);
    parseAndDisplay(payload);
  } else if (code == 401) {
    playAudio(1, 7);
    showError("API Key Tidak Valid!");
  } else if (code == 404) {
    playAudio(1, 6);
    showError("Kartu/QR Tdk Terdaftar");
  } else if (code > 0) {
    playAudio(1, 7);
    showError("Gagal Server: " + String(code));
  } else {
    String errMsg = http.errorToString(code);
    String payload = http.getString();
    if (payload.length() > 10 && payload.indexOf("status") > 0) {
      parseAndDisplay(payload);
    } else {
      playAudio(1, 7);
      showError("Server Error: " + errMsg.substring(0, 14));
    }
  }

  http.end();
  while (kioskSerial.available()) kioskSerial.read();
  qrBuffer = "";
  
  delay(DISPLAY_RESULT_MS);
  showReady();
}

// ============================================================
//  PARSE RESPONSE JSON
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

    if (kelasDisplay != "") {
      lcd.setCursor(0, 1); lcd.print(kelasDisplay);
    } else {
      lcd.setCursor(0, 1); lcd.print("-");
    }

    if (action_code == "CHECK_IN") {
      lcd.setCursor(0, 2); lcd.print("MASUK PADA: " + waktu);
      if (kelasDisplay.indexOf("Guru") >= 0 || kelasDisplay.indexOf("Staf") >= 0 || kelasDisplay.indexOf("Staff") >= 0) {
        lcd.setCursor(0, 3); lcd.print(F("Selamat Bertugas!   "));
        playAudio(1, 5); // 005.MP3 - Selamat Pagi Selamat Bekerja (Guru/Staf)
      } else {
        lcd.setCursor(0, 3); lcd.print(F("Selamat Belajar!    "));
        playAudio(1, 2); // 002.MP3 - Akses diterima selamat belajar (Siswa)
      }
      indicatorCheckIn();
    }
    else if (action_code == "CHECK_OUT") {
      lcd.setCursor(0, 2); lcd.print("PULANG PADA: " + waktu);
      lcd.setCursor(0, 3); lcd.print(F("Hati-hati di jalan! "));
      playAudio(1, 3); // 003.MP3
      indicatorCheckOut();
    }
    else if (action_code == "COOLDOWN") {
      lcd.setCursor(0, 2); lcd.print(F("Sudah Absen Masuk!  "));
      if (waktu != "") {
        lcd.setCursor(0, 3); lcd.print("Jam Masuk: " + waktu);
      } else {
        lcd.setCursor(0, 3); lcd.print(message.substring(0, min((int)message.length(), 20)));
      }
      playAudio(1, 4); // 004.MP3
      indicatorCooldown();
    }
    else if (action_code == "NEW_CARD") {
      lcd.clear();
      lcd.setCursor(0, 0); lcd.print(F("** KARTU BARU **    "));
      lcd.setCursor(0, 1); lcd.print(kelasDisplay);
      lcd.setCursor(0, 2); lcd.print(F("Belum terdaftar!    "));
      lcd.setCursor(0, 3); lcd.print(F("Daftarkan di Admin  "));
      playAudio(1, 6); // 006.MP3 - Kartu tidak dikenali hubungi admin
      indicatorNewCard();
    }
    else {
      lcd.setCursor(0, 2); lcd.print(message.substring(0, min((int)message.length(), 20)));
      lcd.setCursor(0, 3); lcd.print("Waktu: " + waktu);
      playAudio(1, 2); 
      indicatorCheckIn();
    }
  } else {
    String errMsg = (action_code == "ALREADY_ATTENDED") ? "Sudah Absen Lengkap" : message;
    if (action_code == "ALREADY_ATTENDED") {
      playAudio(1, 4); // 004.MP3 - Absensi hadir dan pulang sudah tercatat
    } else {
      playAudio(1, 7); // 007.MP3 - Sistem ada gangguan hubungi admin
    }
    showError(errMsg);
  }
}

// ============================================================
//  FUNGSI DISPLAY STANDBY & ANIMASI (NON-BLOCKING)
// ============================================================

// Menyiapkan Layout Dasar Standby
void showReady() {
  isShowingResult = false;
  lcd.clear();
  
  // Baris 0: Header Elegan
  lcd.setCursor(0, 0);
  lcd.print(F("* PEMBDA PRESENSI * "));
  
  // Baris 1: Petunjuk Scan
  lcd.setCursor(0, 1);
  lcd.write(byte(1)); // Ikon Kartu
  lcd.print(F(" Tempel Kartu / QR "));
  
  // Baris 3: Status Bar & SSID WiFi
  lcd.setCursor(0, 3);
  if (isOnline) {
    lcd.write(byte(0)); // Ikon WiFi
    lcd.print(F(" ON:"));
    String ssid = WiFi.SSID();
    if (ssid.length() > 10) ssid = ssid.substring(0, 10);
    lcd.print(ssid);
  } else {
    lcd.print(F("[OFFLINE] Cari AP..."));
  }
}

// Fungsi Update Animasi Halus (Dipanggil di loop() setiap 350ms)
void updateLcdAnimation() {
  if (isShowingResult) return; // Jangan animasikan jika sedang tampil hasil scan

  unsigned long now = millis();
  if (now - lastAnimTime < 350) return; // Kecepatan frame animasi
  lastAnimTime = now;

  // Frame animasi panah gelombang (Pulse Wave)
  const char* frames[] = {
    "   >> RFID / QR <<  ",
    "  >>> RFID / QR <<< ",
    " >>>> RFID / QR <<<<",
    "  >>> RFID / QR <<< ",
    "   >> RFID / QR <<  ",
    "    > RFID / QR <   "
  };

  // Update Baris 2 (Animasi Gerak)
  lcd.setCursor(0, 2);
  lcd.print(frames[animFrame]);
  animFrame = (animFrame + 1) % 6;

  // Update Heartbeat Blink di pojok kanan bawah (Kolom 19 Baris 3)
  lcd.setCursor(19, 3);
  if (animFrame % 2 == 0) {
    lcd.write(byte(2)); // Ikon Heart / Detak Aktif
  } else {
    lcd.print(F(" "));  // Berkedip mati
  }
}

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

// ============================================================
//  INDIKATOR BUZZER
// ============================================================
void indicatorCheckIn() {
  for (int i = 0; i < 2; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(100);
    digitalWrite(BUZZER_PIN, LOW);
    if (i < 1) delay(100);
  }
}

void indicatorCheckOut() {
  for (int i = 0; i < 3; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(80);
    digitalWrite(BUZZER_PIN, LOW);
    if (i < 2) delay(80);
  }
}

void indicatorCooldown() {
  digitalWrite(BUZZER_PIN, HIGH);
  delay(300);
  digitalWrite(BUZZER_PIN, LOW);
}

void indicatorNewCard() {
  for (int i = 0; i < 4; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(60);
    digitalWrite(BUZZER_PIN, LOW);
    if (i < 3) delay(60);
  }
}

void indicatorFail() {
  digitalWrite(BUZZER_PIN, HIGH);
  delay(600);
  digitalWrite(BUZZER_PIN, LOW);
}

void beep(int count, int duration) {
  for (int i = 0; i < count; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(duration);
    digitalWrite(BUZZER_PIN, LOW);
    if (i < count - 1) delay(100);
  }
}

// ============================================================
//  FUNGSI MP3 PLAYER RAW COMMANDS
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
  if (vol > 30) vol = 30;
  sendMp3Command(0x06, 0x00, vol);
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

// ============================================================
//  KONEKSI WIFI
// ============================================================
void connectWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.persistent(false);
  int attempt = 0;

  lcd.clear();
  lcd.setCursor(0, 0); lcd.print(F("=== MENCARI WIFI ==="));
  lcd.setCursor(0, 1); lcd.print(F("Mencoba koneksi...  "));
  lcd.setCursor(0, 2); lcd.print(F("Hubungkan WiFi...   "));
  lcd.setCursor(0, 3); lcd.print(F("--------------------"));

  while (wifiMulti.run() != WL_CONNECTED && attempt < 20) {
    delay(500);
    Serial.print(".");
    String dots = "";
    for (int i = 0; i < (attempt % 6); i++) dots += ".";
    lcd.setCursor(0, 2); lcd.print("Menghubungkan" + dots + "    ");
    attempt++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(F("=== WIFI CONNECT ==="));
    lcd.setCursor(0, 1); lcd.print("SSID: " + WiFi.SSID().substring(0, 14));
    lcd.setCursor(0, 2); lcd.print("IP: " + WiFi.localIP().toString());
    lcd.setCursor(0, 3); lcd.print(F("--------------------"));
    beep(1, 200);
    delay(1500);
  } else {
    lcd.clear();
    lcd.setCursor(0, 0); lcd.print(F("=== WIFI GAGAL ====="));
    lcd.setCursor(0, 1); lcd.print(F("Koneksi gagal!      "));
    lcd.setCursor(0, 2); lcd.print(F("Auto-retry di bg... "));
    lcd.setCursor(0, 3); lcd.print(F("--------------------"));
    beep(3, 100);
    delay(2000);
  }
}
