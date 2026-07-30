// ============================================================
//  SOFTWARE SPI (BIT-BANGING) DIAGNOSTIC - Arduino Nano + RC522
//  Menggunakan pin digital bebas untuk memverifikasi modul RC522
//  jika hardware SPI (D11/D12/D13) pada Nano bermasalah.
// ============================================================

// Pinout Software SPI:
#define SOFT_SDA   10  // Pin D10 -> RC522 SDA
#define SOFT_SCK    6  // Pin D6  -> RC522 SCK  (Bebas dari D13!)
#define SOFT_MOSI   5  // Pin D5  -> RC522 MOSI (Bebas dari D11!)
#define SOFT_MISO   4  // Pin D4  -> RC522 MISO (Bebas dari D12!)
#define SOFT_RST    3  // Pin D3  -> RC522 RST  (Bebas dari D9!)

void setup() {
  Serial.begin(115200);
  delay(1000);

  Serial.println(F("\n========================================"));
  Serial.println(F("  SOFTWARE SPI DIAGNOSTIC (BIT-BANGING) "));
  Serial.println(F("========================================"));
  Serial.println(F("Pindah kabel ke Pin berikut untuk Uji Software SPI:"));
  Serial.println(F(" -> RC522 SDA  ---> Pin D10"));
  Serial.println(F(" -> RC522 SCK  ---> Pin D6"));
  Serial.println(F(" -> RC522 MOSI ---> Pin D5"));
  Serial.println(F(" -> RC522 MISO ---> Pin D4"));
  Serial.println(F(" -> RC522 RST  ---> Pin D3"));
  Serial.println(F("========================================"));

  pinMode(SOFT_SDA, OUTPUT);
  pinMode(SOFT_SCK, OUTPUT);
  pinMode(SOFT_MOSI, OUTPUT);
  pinMode(SOFT_MISO, INPUT);
  pinMode(SOFT_RST, OUTPUT);

  digitalWrite(SOFT_SDA, HIGH);
  digitalWrite(SOFT_SCK, LOW);

  // Hard Reset Manual
  digitalWrite(SOFT_RST, LOW);
  delay(50);
  digitalWrite(SOFT_RST, HIGH);
  delay(100);
}

byte softSpiTransfer(byte data) {
  byte result = 0;
  for (int i = 7; i >= 0; i--) {
    digitalWrite(SOFT_MOSI, (data >> i) & 0x01);
    digitalWrite(SOFT_SCK, HIGH);
    delayMicroseconds(5);
    if (digitalRead(SOFT_MISO)) {
      result |= (1 << i);
    }
    digitalWrite(SOFT_SCK, LOW);
    delayMicroseconds(5);
  }
  return result;
}

byte readRegister(byte reg) {
  digitalWrite(SOFT_SDA, LOW);
  softSpiTransfer((reg << 1) | 0x80); // Read command
  byte val = softSpiTransfer(0x00);
  digitalWrite(SOFT_SDA, HIGH);
  return val;
}

void loop() {
  byte ver = readRegister(0x37); // VersionReg

  Serial.print(F("Hasil Soft-SPI Register Version (0x37): 0x"));
  if (ver < 16) Serial.print("0");
  Serial.print(ver, HEX);

  if (ver == 0x91 || ver == 0x92) {
    Serial.println(F(" ✅ BERHASIL! (RC522 Original Berfungsi!)"));
  } else if (ver == 0xB2) {
    Serial.println(F(" ✅ BERHASIL! (RC522 Clone Berfungsi!)"));
  } else if (ver == 0x00) {
    Serial.println(F(" ❌ (Masih 0x00 - Modul RC522 Rusak / Tegangan 3.3V Mati)"));
  } else if (ver == 0xFF) {
    Serial.println(F(" ❌ (0xFF - MISO D4 Floating)"));
  } else {
    Serial.println(F(" ❓ (Respon Lain)"));
  }

  delay(2000);
}
