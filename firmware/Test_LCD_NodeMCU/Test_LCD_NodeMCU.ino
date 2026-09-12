// ============================================================
//  TEST LCD 16x2 I2C & I2C SCANNER - NODEMCU V3 (ESP8266)
// ============================================================
//  Wiring LCD 16x2 I2C ke NodeMCU V3:
//  - VCC  ---> 5V (VIN / 5V eksternal)  (Penting: 16x2 butuh 5V agar kontras jelas!)
//  - GND  ---> GND
//  - SDA  ---> D2 (GPIO 4)
//  - SCL  ---> D1 (GPIO 5)
// ============================================================

#include <Wire.h>
#include <LiquidCrystal_I2C.h>

// Definisikan alamat I2C utama (0x27 atau 0x3F)
byte i2cAddress = 0x27;

// Objek LCD
LiquidCrystal_I2C lcd(0x27, 16, 2);

void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println(F("\n======================================"));
  Serial.println(F("    TEST LCD 16x2 I2C & SCANNER       "));
  Serial.println(F("======================================"));

  // Inisialisasi I2C pin: SDA = GPIO4 (D2), SCL = GPIO5 (D1)
  Wire.begin(4, 5);

  // 1. JALANKAN I2C SCANNER DAHULU VIA SERIAL MONITOR
  Serial.println(F("Scanning I2C bus..."));
  byte error, address;
  int nDevices = 0;

  for (address = 1; address < 127; address++) {
    Wire.beginTransmission(address);
    error = Wire.endTransmission();

    if (error == 0) {
      Serial.print(F("Device I2C DITEMUKAN pada alamat 0x"));
      if (address < 16) Serial.print("0");
      Serial.print(address, HEX);
      Serial.println(F(" !"));
      i2cAddress = address;
      nDevices++;
    }
  }

  if (nDevices == 0) {
    Serial.println(F("TIDAK ADA Perangkat I2C Terdeteksi!"));
    Serial.println(F("-> Cek kabel SDA (D2) dan SCL (D1)"));
    Serial.println(F("-> Cek VCC (5V) dan GND LCD"));
  } else {
    Serial.print(F("Selesai! Menggunakan alamat I2C: 0x"));
    Serial.println(i2cAddress, HEX);
  }

  // 2. INISIALISASI LCD DENGAN ALAMAT TERDETEKSI
  lcd = LiquidCrystal_I2C(i2cAddress, 16, 2);
#ifdef FDB_LIQUID_CRYSTAL_I2C_H
  lcd.begin();
#else
  lcd.init();
#endif
  lcd.backlight();

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print(" PEMBDA HUB OK ");
  lcd.setCursor(0, 1);
  lcd.print(" LCD 16x2 READY ");
  
  Serial.println(F("LCD berhasil diinisialisasi. Cek layar!"));
  Serial.println(F("JIKA LAYAR HANYA HYALU KUNING/BIRU TANPA TEKS:"));
  Serial.println(F("-> Putar Potensio/Trimpot Biru di belakang modul I2C LCD!"));
}

int counter = 0;

void loop() {
  // Animasi hitungan agar terlihat layar hidup dan merespon
  lcd.setCursor(0, 1);
  lcd.print("Count: ");
  lcd.print(counter);
  lcd.print("      ");

  counter++;
  delay(1000);
}
