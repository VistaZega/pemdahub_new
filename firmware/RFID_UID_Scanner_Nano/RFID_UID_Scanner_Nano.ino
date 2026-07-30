// ============================================================
//  RAW SPI DIAGNOSTIC SKETCH - Arduino Nano + RC522
//  Uji tingkat rendah tanpa overhead library MFRC522
// ============================================================

#include <SPI.h>

#define SS_PIN  10
#define RST_PIN  9

void setup() {
  Serial.begin(115200);
  delay(1000);

  Serial.println(F("\n========================================"));
  Serial.println(F("    RAW SPI DIAGNOSTIC - ARDUINO NANO   "));
  Serial.println(F("========================================"));

  // Inisialisasi Pin SS dan RST
  pinMode(SS_PIN, OUTPUT);
  pinMode(RST_PIN, OUTPUT);
  
  // Hard reset RC522
  digitalWrite(SS_PIN, HIGH);
  digitalWrite(RST_PIN, LOW);
  delay(50);
  digitalWrite(RST_PIN, HIGH);
  delay(100);

  SPI.begin();

  // Tes 4 Kecepatan SPI Berbeda
  uint16_t dividers[] = { SPI_CLOCK_DIV4, SPI_CLOCK_DIV8, SPI_CLOCK_DIV16, SPI_CLOCK_DIV32, SPI_CLOCK_DIV64 };
  const char* dividerNames[] = { "DIV4 (4MHz)", "DIV8 (2MHz)", "DIV16 (1MHz)", "DIV32 (500kHz)", "DIV64 (250kHz)" };

  for (int i = 0; i < 5; i++) {
    SPI.setClockDivider(dividers[i]);
    delay(10);

    // Baca Register 0x37 (VersionReg) secara manual
    digitalWrite(SS_PIN, LOW);
    SPI.transfer((0x37 << 1) | 0x80); // 0x80 = Read flag
    byte ver = SPI.transfer(0x00);
    digitalWrite(SS_PIN, HIGH);

    Serial.print(F("Kecepatan SPI "));
    Serial.print(dividerNames[i]);
    Serial.print(F(" -> Hasil: 0x"));
    if (ver < 16) Serial.print("0");
    Serial.print(ver, HEX);

    if (ver == 0x91 || ver == 0x92) {
      Serial.println(F(" ✅ (Original RC522 Terdeteksi!)"));
    } else if (ver == 0xB2) {
      Serial.println(F(" ✅ (Clone RC522 Terdeteksi!)"));
    } else if (ver == 0x00) {
      Serial.println(F(" ❌ (0x00 - No Signal / Drop Tegangan 3.3V)"));
    } else if (ver == 0xFF) {
      Serial.println(F(" ❌ (0xFF - Pin MISO Floating / Disconnected)"));
    } else {
      Serial.println(F(" ❓ (Respon Lain)"));
    }
  }

  Serial.println(F("========================================"));
}

void loop() {
  // Pembacaan rutin setiap 2 detik
  digitalWrite(SS_PIN, LOW);
  SPI.transfer((0x37 << 1) | 0x80);
  byte ver = SPI.transfer(0x00);
  digitalWrite(SS_PIN, HIGH);

  Serial.print(F("Status Register Version (0x37): 0x"));
  if (ver < 16) Serial.print("0");
  Serial.print(ver, HEX);

  if (ver == 0x91 || ver == 0x92 || ver == 0xB2) {
    Serial.println(F(" -> SENSOR SIAP!"));
  } else {
    Serial.println(F(" -> SENSOR BELUM MERESPON (Cek 3.3V / Ground)"));
  }

  delay(2000);
}
