// ============================================================
//  RFID UID SCANNER - Arduino Nano (Enhanced Diagnostics)
//  Alat khusus untuk membaca UID kartu RFID/NFC (13.56MHz)
//  dan mengirimkan ke PC via USB Serial (115200 baud)
// ============================================================
//
//  WIRING DIAGRAM (Arduino Nano + RC522):
//  ┌─────────────┬────────────┬───────────────────────────┐
//  │ RC522       │ Arduino    │ Catatan                   │
//  ├─────────────┼────────────┼───────────────────────────┤
//  │ SDA (SS)    │ Pin 10     │ SPI Chip Select           │
//  │ SCK         │ Pin 13     │ SPI Clock                 │
//  │ MOSI        │ Pin 11     │ SPI MOSI                  │
//  │ MISO        │ Pin 12     │ SPI MISO                  │
//  │ RST         │ Pin 9      │ Reset                     │
//  │ 3.3V        │ 3.3V       │ ⚠️ Wajib 3.3V (Jangan 5V!)│
//  │ GND         │ GND        │ Ground                    │
//  └─────────────┴────────────┴───────────────────────────┘
//
//  BUZZER & LED (Opsional):
//  - Buzzer (+)   ---> Pin 7
//  - LED Hijau    ---> Pin 5
//  - LED Merah    ---> Pin 6
// ============================================================

#include <SPI.h>
#include <MFRC522.h>

#define RFID_SS_PIN   10   // Pin 10 - SPI Chip Select
#define RFID_RST_PIN   9   // Pin 9  - Reset
#define BUZZER_PIN     7   // Pin 7  - Buzzer (Opsional)
#define LED_GREEN      5   // Pin 5  - LED Hijau (Opsional)
#define LED_RED        6   // Pin 6  - LED Merah (Opsional)

#define SCAN_COOLDOWN_MS 2000  // Anti double-tap 2 detik

MFRC522 rfid(RFID_SS_PIN, RFID_RST_PIN);

String        lastUID     = "";
unsigned long lastTap     = 0;
unsigned long scanCount   = 0;
unsigned long lastHeartbeat = 0;

void setup() {
  Serial.begin(115200);
  delay(1000);
  
  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(LED_GREEN,  OUTPUT);
  pinMode(LED_RED,    OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);
  digitalWrite(LED_GREEN,  LOW);
  digitalWrite(LED_RED,    LOW);

  Serial.println(F("\n========================================"));
  Serial.println(F("   RFID UID SCANNER - Arduino Nano      "));
  Serial.println(F("========================================"));

  // Inisialisasi SPI & RFID RC522
  SPI.begin();
  SPI.setFrequency(1000000); // 1MHz untuk kestabilan chip clone (0xB2)
  delay(50);

  rfid.PCD_Init();
  delay(100);

  // Cek apakah modul RFID RC522 terhubung dengan benar
  byte version = rfid.PCD_ReadRegister(rfid.VersionReg);
  
  if (version == 0x00 || version == 0xFF) {
    Serial.println(F("❌ ERROR: Modul RFID RC522 TIDAK Terdeteksi!"));
    Serial.println(F("  -> Periksa kabel wiring SPI (Pin 9, 10, 11, 12, 13)"));
    Serial.println(F("  -> Pastikan pin 3.3V dan GND terhubung rapat"));
    
    // Kedipkan LED Merah cepat sebagai tanda error hardware
    for (int i = 0; i < 10; i++) {
      digitalWrite(LED_RED, HIGH); delay(100);
      digitalWrite(LED_RED, LOW);  delay(100);
    }
  } else {
    // Maximalkan antenna gain
    rfid.PCD_SetAntennaGain(rfid.RxGain_max);
    rfid.PCD_AntennaOn();

    Serial.print(F("✅ SENSOR RFID OK! Version Chip: 0x"));
    Serial.print(version, HEX);
    if (version == 0x91 || version == 0x92) Serial.println(F(" (Original MFRC522)"));
    else if (version == 0xB2) Serial.println(F(" (Clone Chip MFRC522 - OK)"));
    else Serial.println();

    Serial.println(F("========================================"));
    Serial.println(F(" Status: SIAP. Tempelkan kartu RFID...  "));
    Serial.println(F("========================================"));

    // Beep 1x + LED Hijau tanda siap
    digitalWrite(BUZZER_PIN, HIGH);
    digitalWrite(LED_GREEN, HIGH);
    delay(150);
    digitalWrite(BUZZER_PIN, LOW);
    digitalWrite(LED_GREEN, LOW);
  }
}

void loop() {
  unsigned long now = millis();

  // Heartbeat setiap 5 detik agar user tahu alat tidak hang/stuck
  if (now - lastHeartbeat >= 5000) {
    lastHeartbeat = now;
    // Cek ulang register chip RFID untuk memastikan kabel tidak lepas saat operasi
    byte v = rfid.PCD_ReadRegister(rfid.VersionReg);
    if (v == 0x00 || v == 0xFF) {
      Serial.println(F("⚠️ WARNING: Sambungan kabel RFID terputus!"));
    }
  }

  // Cek apakah ada kartu di dekat antena
  if (!rfid.PICC_IsNewCardPresent()) return;

  // Coba baca serial kartu (retry 1x untuk chip clone)
  if (!rfid.PICC_ReadCardSerial()) {
    delay(10);
    if (!rfid.PICC_ReadCardSerial()) return;
  }

  // Baca UID
  String uid = getUID();

  // Anti double-tap
  if (uid == lastUID && (now - lastTap) < SCAN_COOLDOWN_MS) {
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
    return;
  }

  lastUID = uid;
  lastTap = now;
  scanCount++;

  // Kirim data ke Serial USB
  Serial.println("UID:" + uid);
  Serial.println("COUNT:" + String(scanCount));

  // Indikator Suara & LED
  digitalWrite(BUZZER_PIN, HIGH);
  digitalWrite(LED_GREEN, HIGH);
  delay(120);
  digitalWrite(BUZZER_PIN, LOW);
  digitalWrite(LED_GREEN, LOW);

  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();
}

String getUID() {
  if (rfid.uid.size < 4) return "";
  String hex = "";
  for (int i = 3; i >= 0; i--) {
    if (rfid.uid.uidByte[i] < 0x10) hex += "0";
    hex += String(rfid.uid.uidByte[i], HEX);
  }
  hex.toUpperCase();
  return hex;
}
