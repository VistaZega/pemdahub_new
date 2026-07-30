// ============================================================
//  RFID UID SCANNER - Arduino Nano (Pin Diagnostik Spesifik)
//  Alat khusus untuk membaca UID kartu RFID/NFC (13.56MHz)
// ============================================================

#include <SPI.h>
#include <MFRC522.h>

#define RFID_SS_PIN   10   // Pin 10 - SPI Chip Select (SDA)
#define RFID_RST_PIN   9   // Pin 9  - Reset (RST)
#define BUZZER_PIN     7   // Pin 7  - Buzzer (Opsional)
#define LED_GREEN      5   // Pin 5  - LED Hijau (Opsional)
#define LED_RED        6   // Pin 6  - LED Merah (Opsional)

#define SCAN_COOLDOWN_MS 2000

MFRC522 rfid(RFID_SS_PIN, RFID_RST_PIN);

String        lastUID       = "";
unsigned long lastTap       = 0;
unsigned long scanCount     = 0;
unsigned long lastHeartbeat = 0;

void diagnoseRfidWiring();

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

  SPI.begin();
  SPI.setClockDivider(SPI_CLOCK_DIV16); // 1MHz AVR SPI
  delay(50);

  rfid.PCD_Init();
  delay(100);

  // Jalankan diagnosa jalur pin SPI
  diagnoseRfidWiring();
}

void loop() {
  unsigned long now = millis();

  // Diagnostik berkala setiap 5 detik jika sambungan terputus
  if (now - lastHeartbeat >= 5000) {
    lastHeartbeat = now;
    byte v = rfid.PCD_ReadRegister(rfid.VersionReg);
    if (v == 0x00 || v == 0xFF) {
      Serial.println(F("\n⚠️ SAMBUNGAN RFID TERPUTUS!"));
      diagnoseRfidWiring();
    }
  }

  if (!rfid.PICC_IsNewCardPresent()) return;
  if (!rfid.PICC_ReadCardSerial()) {
    delay(10);
    if (!rfid.PICC_ReadCardSerial()) return;
  }

  String uid = getUID();

  if (uid == lastUID && (now - lastTap) < SCAN_COOLDOWN_MS) {
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
    return;
  }

  lastUID = uid;
  lastTap = now;
  scanCount++;

  Serial.println("UID:" + uid);
  Serial.println("COUNT:" + String(scanCount));

  digitalWrite(BUZZER_PIN, HIGH);
  digitalWrite(LED_GREEN, HIGH);
  delay(120);
  digitalWrite(BUZZER_PIN, LOW);
  digitalWrite(LED_GREEN, LOW);

  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();
}

// ============================================================
//  FUNGSI ANALISA SPESIFIK JALUR KABEL SPI
// ============================================================
void diagnoseRfidWiring() {
  byte v = rfid.PCD_ReadRegister(rfid.VersionReg);
  
  Serial.print(F("📊 Nilai Pembacaan Version Register: 0x"));
  if (v < 16) Serial.print("0");
  Serial.println(v, HEX);

  if (v == 0x00) {
    Serial.println(F("❌ HASIL ANALISA (0x00 - Semuanya LOW):"));
    Serial.println(F("   Kabel yang KEMUNGKINAN BESAR LEPAS / PUTUS:"));
    Serial.println(F("   1. 🔴 Kabel 3.3V atau GND (RC522 Mati / Tanpa Daya)"));
    Serial.println(F("   2. 🟡 Kabel SCK  -> Pin 13 Arduino Nano"));
    Serial.println(F("   3. 🟡 Kabel SDA  -> Pin 10 Arduino Nano"));
  }
  else if (v == 0xFF) {
    Serial.println(F("❌ HASIL ANALISA (0xFF - Semuanya HIGH / Floating):"));
    Serial.println(F("   Kabel yang KEMUNGKINAN BESAR LEPAS / PUTUS:"));
    Serial.println(F("   1. 🔵 Kabel MISO -> Pin 12 Arduino Nano"));
    Serial.println(F("   2. 🟢 Kabel RST  -> Pin 9 Arduino Nano"));
    Serial.println(F("   3. 🔴 Pin 3.3V terlepas atau tidak ada arus"));
  }
  else {
    // Uji komunikasi Tulis -> Baca (MOSI - Pin 11)
    byte testVal = 0x25;
    rfid.PCD_WriteRegister(rfid.TModeReg, testVal);
    byte readVal = rfid.PCD_ReadRegister(rfid.TModeReg);
    
    if ((readVal & 0x0F) != (testVal & 0x0F)) {
      Serial.println(F("❌ HASIL ANALISA (Gagal Tulis-Baca Register):"));
      Serial.println(F("   1. 🟣 Kabel MOSI -> Pin 11 Arduino Nano (Perintah dari Arduino tidak sampai ke RC522)"));
    } else {
      rfid.PCD_SetAntennaGain(rfid.RxGain_max);
      rfid.PCD_AntennaOn();
      Serial.println(F("✅ SENSOR RFID NORMAL! Siap membaca kartu."));
    }
  }
  Serial.println(F("----------------------------------------"));
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
