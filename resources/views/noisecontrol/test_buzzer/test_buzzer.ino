/* =========================================================================================
 * PEMBDA NOISE CONTROL - SKETCH KHUSUS PENGUJIAN BUZZER (GPIO 4)
 * =========================================================================================
 * Rangkaian Pin:
 * - Kaki Positif (+) Buzzer -> Hubungkan ke GPIO 4 (ESP32)
 * - Kaki Negatif (-) Buzzer -> Hubungkan ke GND (ESP32)
 *
 * Mendukung 2 Tipe Buzzer:
 * 1. Buzzer Aktif (Punya osilator internal, butuh sinyal DC HIGH)
 * 2. Buzzer Pasif (Butuh sinyal frekuensi/PWM Tone agar membran bergetar)
 * =========================================================================================
 */

#include <Arduino.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SH110X.h>

#define PIN_BUZZER      4     // Pin D4 (GPIO 4)
#define PIN_LED_HIJAU   18
#define PIN_LED_KUNING  19
#define PIN_LED_MERAH   23

Adafruit_SH1106G oled(128, 64, &Wire, -1);
bool oledAda = false;

// Fungsi getar pulsa manual (Kompatibel 100% untuk Buzzer Pasif di semua ESP32)
void mainkanNada(int freqHz, int durasiMs) {
    if (freqHz <= 0) {
        delay(durasiMs);
        return;
    }
    long periodeUs = 1000000L / freqHz;
    long setengahPeriodeUs = periodeUs / 2;
    long totalSiklus = ((long)freqHz * durasiMs) / 1000L;

    for (long i = 0; i < totalSiklus; i++) {
        digitalWrite(PIN_BUZZER, HIGH);
        delayMicroseconds(setengahPeriodeUs);
        digitalWrite(PIN_BUZZER, LOW);
        delayMicroseconds(setengahPeriodeUs);
    }
}

void tampilkanLayar(const char* baris1, const char* baris2, const char* baris3) {
    if (!oledAda) return;
    oled.clearDisplay();
    oled.setTextColor(SH110X_WHITE);
    oled.setTextSize(1);

    oled.setCursor(0, 2);
    oled.println("=== UJI COBA BUZZER ===");
    oled.drawFastHLine(0, 13, 128, SH110X_WHITE);

    oled.setCursor(0, 20);
    oled.println(baris1);

    oled.setCursor(0, 34);
    oled.println(baris2);

    oled.setCursor(0, 48);
    oled.println(baris3);

    oled.display();
}

void setup() {
    Serial.begin(115200);
    delay(500);

    Serial.println("\n==============================================");
    Serial.println("  PEMBDA NOISE MONITOR - TES BUZZER GPIO 4   ");
    Serial.println("==============================================");
    Serial.println("Kabel:");
    Serial.println("  (+) Buzzer -> GPIO 4 (D4)");
    Serial.println("  (-) Buzzer -> GND");
    Serial.println("==============================================\n");

    pinMode(PIN_BUZZER, OUTPUT);
    digitalWrite(PIN_BUZZER, LOW);

    pinMode(PIN_LED_HIJAU, OUTPUT);
    pinMode(PIN_LED_KUNING, OUTPUT);
    pinMode(PIN_LED_MERAH, OUTPUT);

    // Coba nyalakan OLED jika terpasang
    Wire.begin(21, 22);
    if (oled.begin(0x3C, true)) {
        oledAda = true;
        oled.setContrast(255);
        tampilkanLayar("Pin: GPIO 4 (D4)", "Memulai tes...", "Dengarkan suara!");
        delay(1000);
    }
}

void loop() {
    // ---------------------------------------------------------
    // TES 1: UJI BUZZER AKTIF (Sinyal DC HIGH Biasa)
    // ---------------------------------------------------------
    Serial.println("[TES 1] Uji Sinyal DC (Untuk Buzzer Aktif)...");
    tampilkanLayar("TES 1: BUZZER AKTIF", "Sinyal DC HIGH", "Bip-Bip Standar");
    digitalWrite(PIN_LED_HIJAU, HIGH);

    for (int i = 0; i < 3; i++) {
        digitalWrite(PIN_BUZZER, HIGH);
        delay(150);
        digitalWrite(PIN_BUZZER, LOW);
        delay(150);
    }
    digitalWrite(PIN_LED_HIJAU, LOW);
    delay(700);

    // ---------------------------------------------------------
    // TES 2: UJI FREKUENSI NADA 2.4 kHz (Untuk Buzzer Pasif & Aktif)
    // ---------------------------------------------------------
    Serial.println("[TES 2] Uji Nada 2400 Hz (Untuk Buzzer Pasif & Aktif)...");
    tampilkanLayar("TES 2: FREKUENSI NADA", "Frekuensi 2.4 kHz", "Bip Keras & Nyaring");
    digitalWrite(PIN_LED_KUNING, HIGH);

    for (int i = 0; i < 3; i++) {
        mainkanNada(2400, 150);
        delay(150);
    }
    digitalWrite(PIN_LED_KUNING, LOW);
    delay(700);

    // ---------------------------------------------------------
    // TES 3: SIRINE ALARM KEBISINGAN (Naik Turun)
    // ---------------------------------------------------------
    Serial.println("[TES 3] Uji Sirine Alarm Bising...");
    tampilkanLayar("TES 3: SIRINE ALARM", "Sirine Kebisingan", "Status: Merah/Ribut");
    digitalWrite(PIN_LED_MERAH, HIGH);

    for (int j = 0; j < 2; j++) {
        // Frekuensi naik
        for (int f = 1500; f <= 3000; f += 100) {
            mainkanNada(f, 15);
        }
        // Frekuensi turun
        for (int f = 3000; f >= 1500; f -= 100) {
            mainkanNada(f, 15);
        }
    }
    digitalWrite(PIN_LED_MERAH, LOW);
    delay(700);

    // ---------------------------------------------------------
    // TES 4: MELODI NADA PENDEK (Verifikasi Audio)
    // ---------------------------------------------------------
    Serial.println("[TES 4] Uji Melodi Tangga Nada...");
    tampilkanLayar("TES 4: MELODI NADA", "Do - Re - Mi - Sol", "Uji Resonansi");

    int nada[] = { 1046, 1175, 1318, 1568 }; // C6, D6, E6, G6
    for (int i = 0; i < 4; i++) {
        digitalWrite(PIN_LED_HIJAU, (i % 2 == 0) ? HIGH : LOW);
        digitalWrite(PIN_LED_MERAH, (i % 2 != 0) ? HIGH : LOW);
        mainkanNada(nada[i], 120);
        delay(50);
    }
    digitalWrite(PIN_LED_HIJAU, LOW);
    digitalWrite(PIN_LED_MERAH, LOW);

    Serial.println("[SELESAI] Putaran tes selesai. Jeda 2 detik lalu ulangi...\n");
    tampilkanLayar("PENGUJIAN SELESAI", "Apakah terdengar?", "Mengulang tes...");
    delay(2000);
}
