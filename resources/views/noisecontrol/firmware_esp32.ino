/* =========================================================================================
 * PEMBDA NOISE CONTROL & CLASSROOM MONITORING SYSTEM
 * Microcontroller : ESP32 (ESP-WROOM-32 / NodeMCU-32S)
 * Sensor Audio    : INMP441 (Digital I2S MEMS Microphone)
 * Display         : OLED 1.3" I2C (SH1106 / SSD1306, Address 0x3C)
 * Indikator LED   : Hijau (Aman), Kuning (Sedikit Ribut), Merah (Terlalu Ribut)
 * Cloud Server    : https://perguruanpembda.com/noicecontrol
 * Fitur Otomatis  : Kirim Peringatan WhatsApp ke Wali Kelas saat Lampu Merah Menyala
 * =========================================================================================
 * 
 * PANDUAN PIN WIRING (ESP32 DevKit):
 * -----------------------------------------------------------------------------------------
 * 1. INMP441 Microphone (I2S):
 *    - VDD  -> 3.3V ESP32
 *    - GND  -> GND ESP32
 *    - SD   -> GPIO 32  (Serial Data Out)
 *    - WS   -> GPIO 25  (Word Select / LRCK)
 *    - SCK  -> GPIO 33  (Serial Clock / BCLK)
 *    - L/R  -> GND      (Left Channel)
 * 
 * 2. OLED 1.3" (I2C):
 *    - VCC  -> 3.3V ESP32
 *    - GND  -> GND ESP32
 *    - SDA  -> GPIO 21  (I2C SDA)
 *    - SCL  -> GPIO 22  (I2C SCL)
 * 
 * 3. 3x Indikator LED (dengan Resistor 220 Ohm - 330 Ohm ke GND):
 *    - LED Hijau  (Aman)          -> GPIO 18
 *    - LED Kuning (Sedikit Ribut) -> GPIO 19
 *    - LED Merah  (Terlalu Ribut) -> GPIO 23
 * 
 * LIBRARY YANG DIBUTUHKAN (Install via Arduino Library Manager):
 * 1. "U8g2" by oliver (Disarankan untuk OLED 1.3" SH1106 / SSD1306)
 *    - ATAU "Adafruit SH110X" & "Adafruit GFX"
 * 2. "ArduinoJson" by Benoit Blanchon (Versi 6.x atau 7.x)
 * 3. WiFi & HTTPClient (Bawaan ESP32 Core)
 * =========================================================================================
 */

#include <Arduino.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <driver/i2s.h>
#include <Wire.h>
#include <U8g2lib.h>

// ================= PENGATURAN KONEKSI & CLOUD =================
const char* WIFI_SSID     = "NAMA_WIFI_SEKOLAH";      // Ganti dengan SSID WiFi Sekolah
const char* WIFI_PASSWORD = "PASSWORD_WIFI";          // Ganti dengan Password WiFi
const char* SERVER_URL    = "https://perguruanpembda.com/noicecontrol/api/telemetry";

// TOKEN ALAT (Dapat disalin dari dashboard perguruanpembda.com/noicecontrol)
const char* DEVICE_TOKEN  = "nd_GANTI_DENGAN_TOKEN_ALAT_ANDA";
const char* FIRMWARE_VER  = "1.0.0-PRO";

// ================= KONFIGURASI PIN HARDWARE =================
// Pin I2S INMP441
#define I2S_WS_PIN    25
#define I2S_SCK_PIN   33
#define I2S_SD_PIN    32
#define I2S_PORT      I2S_NUM_0

// Pin Indikator LED
#define PIN_LED_HIJAU   18
#define PIN_LED_KUNING  19
#define PIN_LED_MERAH   23

// Inisialisasi Layar OLED 1.3" (SH1106 I2C 128x64, Noname/Chinese clones)
// Catatan: Jika menggunakan SSD1306, ganti dengan U8G2_SSD1306_128X64_NONAME_F_HW_I2C
U8G2_SH1106_128X64_NONAME_F_HW_I2C u8g2(U8G2_R0, /* reset=*/ U8X8_PIN_NONE, /* scl=*/ 22, /* sda=*/ 21);

// ================= PARAMETER AMBANG BATAS & KALIBRASI =================
float thresholdGreen   = 65.0; // dB < 65  : Aman (LED Hijau)
float thresholdYellow  = 75.0; // 65-75 dB : Sedang / Sedikit Ribut (LED Kuning)
float thresholdRed     = 80.0; // dB >= 80 : Terlalu Ribut (LED Merah & Notif WA)
float calibOffset      = 12.0; // Offset kalibrasi mikrofon (sesuaikan jika perlu)

// Buffer Audio I2S
#define I2S_SAMPLE_RATE 16000
#define I2S_BUFFER_SIZE 512
int32_t i2s_raw_samples[I2S_BUFFER_SIZE];

// Riwayat Sparkline untuk Grafik di Layar OLED
#define SPARKLINE_POINTS 32
uint8_t sparkline[SPARKLINE_POINTS];
uint8_t sparklineIdx = 0;

// Variabel Waktu & Telemetri
unsigned long lastTelemetryTime = 0;
const unsigned long TELEMETRY_INTERVAL = 3000; // Kirim telemetri tiap 3 detik
float currentDb = 45.0;
float peakDb    = 45.0;
String currentStatus = "safe";
String className     = "Ruang Kelas";
String waliName      = "-";
bool isAlertSent     = false;

// ================= SETUP I2S INMP441 =================
void setupI2S() {
    i2s_config_t i2s_config = {
        .mode = (i2s_mode_t)(I2S_MODE_MASTER | I2S_MODE_RX),
        .sample_rate = I2S_SAMPLE_RATE,
        .bits_per_sample = I2S_BITS_PER_SAMPLE_32BIT,
        .channel_format = I2S_CHANNEL_FMT_ONLY_LEFT,
        .communication_format = i2s_comm_format_t(I2S_COMM_FORMAT_STAND_I2S),
        .intr_alloc_flags = ESP_INTR_FLAG_LEVEL1,
        .dma_buf_count = 4,
        .dma_buf_len = I2S_BUFFER_SIZE,
        .use_apll = false,
        .tx_desc_auto_clear = false,
        .fixed_mclk = 0
    };

    i2s_pin_config_t pin_config = {
        .bck_io_num = I2S_SCK_PIN,
        .ws_io_num = I2S_WS_PIN,
        .data_out_num = I2S_PIN_NO_CHANGE,
        .data_in_io_num = I2S_SD_PIN
    };

    i2s_driver_install(I2S_PORT, &i2s_config, 0, NULL);
    i2s_set_pin(I2S_PORT, &pin_config);
    i2s_set_clk(I2S_PORT, I2S_SAMPLE_RATE, I2S_BITS_PER_SAMPLE_32BIT, I2S_CHANNEL_MONO);
}

// ================= BACA SUARA & HITUNG DESIBEL =================
float readNoiseLevel() {
    size_t bytesRead = 0;
    i2s_read(I2S_PORT, (char*)i2s_raw_samples, sizeof(i2s_raw_samples), &bytesRead, portMAX_DELAY);
    
    int samplesCount = bytesRead / sizeof(int32_t);
    if (samplesCount <= 0) return 30.0;

    // Hitung RMS (Root Mean Square) dari sample audio PCM
    double sumSquares = 0.0;
    for (int i = 0; i < samplesCount; i++) {
        // Ambil 24-bit MSB dari 32-bit data I2S
        int32_t sample = i2s_raw_samples[i] >> 8;
        sumSquares += ((double)sample * (double)sample);
    }

    double rms = sqrt(sumSquares / samplesCount);

    // Konversi nilai RMS ke estimasi Decibel (dB SPL) terkalibrasi
    float db = 30.0;
    if (rms > 10.0) {
        db = 20.0 * log10(rms) - 20.0 + calibOffset;
    }

    // Batasi rentang desibel realistis (30 dB - 120 dB)
    if (db < 30.0) db = 30.0;
    if (db > 120.0) db = 120.0;

    return db;
}

// ================= KONTROL LED INDIKATOR TIGA WARNA =================
void updateLedStatus(float db) {
    if (db >= thresholdRed) {
        // ZONA 3: TERLALU RIBUT (Merah ON, Kuning & Hijau OFF)
        digitalWrite(PIN_LED_MERAH, HIGH);
        digitalWrite(PIN_LED_KUNING, LOW);
        digitalWrite(PIN_LED_HIJAU, LOW);
        currentStatus = "danger";
    } 
    else if (db >= thresholdGreen) {
        // ZONA 2: SEDIKIT RIBUT / PERINGATAN (Kuning ON, Merah & Hijau OFF)
        digitalWrite(PIN_LED_MERAH, LOW);
        digitalWrite(PIN_LED_KUNING, HIGH);
        digitalWrite(PIN_LED_HIJAU, LOW);
        currentStatus = "warning";
    } 
    else {
        // ZONA 1: AMAN / KONDUSIF (Hijau ON, Merah & Kuning OFF)
        digitalWrite(PIN_LED_MERAH, LOW);
        digitalWrite(PIN_LED_KUNING, LOW);
        digitalWrite(PIN_LED_HIJAU, HIGH);
        currentStatus = "safe";
    }
}

// ================= GAMBAR TAMPILAN OLED 1.3 INCH =================
void drawOledDisplay(float db, float peak) {
    u8g2.clearBuffer();

    // 1. Header Bar: Nama Sekolah / Logo + Status WiFi
    u8g2.setFont(u8g2_font_6x10_tf);
    u8g2.drawStr(0, 9, "PEMBDA NOISE MONITOR");

    if (WiFi.status() == WL_CONNECTED) {
        u8g2.drawStr(108, 9, "WIFI");
    } else {
        u8g2.drawStr(108, 9, "!OFF");
    }
    u8g2.drawHLine(0, 11, 128);

    // 2. Angka Desibel Besar di Tengah Layar
    u8g2.setFont(u8g2_font_logisoso16_tf);
    char dbStr[16];
    dtostrf(db, 4, 1, dbStr);
    strcat(dbStr, " dB");
    u8g2.drawStr(0, 31, dbStr);

    // 3. Status Badge Box di Samping Kanan
    u8g2.setFont(u8g2_font_6x12_tf);
    if (currentStatus == "danger") {
        u8g2.drawRBox(74, 16, 54, 17, 3);
        u8g2.setDrawColor(0); // Teks invers putih di latar hitam/menyala
        u8g2.drawStr(78, 29, "!RIBAT!");
        u8g2.setDrawColor(1);
    } else if (currentStatus == "warning") {
        u8g2.drawRFrame(74, 16, 54, 17, 3);
        u8g2.drawStr(77, 29, "SEDANG");
    } else {
        u8g2.drawRFrame(74, 16, 54, 17, 3);
        u8g2.drawStr(82, 29, "AMAN");
    }

    // 4. Bar Graph VU-Meter Dinamis (Skala 35 dB - 100 dB)
    u8g2.drawFrame(0, 36, 128, 9);
    int barWidth = map(constrain((int)db, 35, 100), 35, 100, 0, 124);
    if (barWidth > 0) {
        u8g2.drawBox(2, 38, barWidth, 5);
    }
    // Tanda Batas Ambang Merah pada Bar (80 dB)
    int redMarkerX = map((int)thresholdRed, 35, 100, 0, 124) + 2;
    u8g2.drawVLine(redMarkerX, 35, 11);

    // 5. Mini Waveform / Sparkline Riwayat Suara di Bagian Bawah
    u8g2.drawHLine(0, 48, 128);
    for (int i = 0; i < SPARKLINE_POINTS - 1; i++) {
        int x1 = i * 4;
        int y1 = 63 - sparkline[i];
        int x2 = (i + 1) * 4;
        int y2 = 63 - sparkline[i + 1];
        u8g2.drawLine(x1, y1, x2, y2);
    }

    // Info Kelas Mini di Pojok Kanan Bawah
    u8g2.setFont(u8g2_font_4x6_tf);
    u8g2.drawStr(86, 58, className.c_str());

    u8g2.sendBuffer();
}

// ================= KIRIM TELEMETRI KE SERVER PEMBDAHUB =================
void sendTelemetry(float db, float peak) {
    if (WiFi.status() != WL_CONNECTED) {
        return; // Lewati jika belum ada koneksi WiFi
    }

    HTTPClient http;
    http.begin(SERVER_URL);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("X-Device-Token", DEVICE_TOKEN);
    http.setTimeout(4000);

    // Buat JSON payload
    StaticJsonDocument<256> doc;
    doc["token"]    = DEVICE_TOKEN;
    doc["db"]       = db;
    doc["peak_db"]  = peak;
    doc["firmware"] = FIRMWARE_VER;

    String jsonString;
    serializeJson(doc, jsonString);

    int httpCode = http.POST(jsonString);

    if (httpCode == 200) {
        String response = http.getString();
        StaticJsonDocument<512> resDoc;
        DeserializationError error = deserializeJson(resDoc, response);

        if (!error) {
            // Sinkronisasi otomatis ambang batas dari server
            if (resDoc.containsKey("threshold_green")) {
                thresholdGreen = resDoc["threshold_green"];
            }
            if (resDoc.containsKey("threshold_yellow")) {
                thresholdYellow = resDoc["threshold_yellow"];
            }
            if (resDoc.containsKey("threshold_red")) {
                thresholdRed = resDoc["threshold_red"];
            }
            if (resDoc.containsKey("classroom")) {
                className = resDoc["classroom"].as<String>();
            }
            if (resDoc.containsKey("alert_sent")) {
                isAlertSent = resDoc["alert_sent"];
            }
        }
    }

    http.end();
}

// ================= INISIALISASI UTAMA (SETUP) =================
void setup() {
    Serial.begin(115200);
    delay(500);

    // Setup Pin LED
    pinMode(PIN_LED_HIJAU, OUTPUT);
    pinMode(PIN_LED_KUNING, OUTPUT);
    pinMode(PIN_LED_MERAH, OUTPUT);

    // Test Nyala Lampu Saat Startup (Indikasi Hardware OK)
    digitalWrite(PIN_LED_HIJAU, HIGH);
    digitalWrite(PIN_LED_KUNING, HIGH);
    digitalWrite(PIN_LED_MERAH, HIGH);

    // Setup Layar OLED
    u8g2.begin();
    u8g2.clearBuffer();
    u8g2.setFont(u8g2_font_6x12_tf);
    u8g2.drawStr(12, 20, "PEMBDA NOISE MONITOR");
    u8g2.drawStr(16, 36, "Menginisialisasi...");
    u8g2.drawStr(22, 52, "ESP32 + INMP441");
    u8g2.sendBuffer();

    delay(800);
    digitalWrite(PIN_LED_HIJAU, LOW);
    digitalWrite(PIN_LED_KUNING, LOW);
    digitalWrite(PIN_LED_MERAH, LOW);

    // Setup Sensor Suara I2S INMP441
    setupI2S();

    // Reset buffer sparkline
    for (int i = 0; i < SPARKLINE_POINTS; i++) {
        sparkline[i] = 2;
    }

    // Koneksi ke Jaringan WiFi
    WiFi.mode(WIFI_STA);
    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
}

// ================= LOOP UTAMA =================
void loop() {
    // 1. Baca desibel dari mikrofon INMP441
    float db = readNoiseLevel();

    // Filter perataan halus (Smoothing Filter)
    currentDb = (currentDb * 0.70) + (db * 0.30);
    if (db > peakDb) {
        peakDb = db;
    } else {
        peakDb = peakDb * 0.98; // Peluruhan puncak bertahap
    }

    // 2. Kendalikan LED Tiga Warna Sesuai Batas Kebisingan
    updateLedStatus(currentDb);

    // 3. Update data visualizer grafik sparkline
    static unsigned long lastSparklineTime = 0;
    if (millis() - lastSparklineTime >= 100) {
        lastSparklineTime = millis();
        // Petakan dB (35-95) ke tinggi grafik 0-14 pixel
        uint8_t h = map(constrain((int)currentDb, 35, 95), 35, 95, 1, 14);
        
        // Geser buffer sparkline ke kiri
        for (int i = 0; i < SPARKLINE_POINTS - 1; i++) {
            sparkline[i] = sparkline[i + 1];
        }
        sparkline[SPARKLINE_POINTS - 1] = h;
    }

    // 4. Render Layar OLED setiap 80 ms (sekitar 12 FPS, responsif & mulus)
    static unsigned long lastDisplayTime = 0;
    if (millis() - lastDisplayTime >= 80) {
        lastDisplayTime = millis();
        drawOledDisplay(currentDb, peakDb);
    }

    // 5. Kirim Telemetri ke Server PembdaHUB
    // Terkirim setiap 3 detik, ATAU LANGSUNG seketika saat bising berbahaya terdeteksi
    static bool previousDanger = false;
    bool isNowDanger = (currentStatus == "danger");

    if (millis() - lastTelemetryTime >= TELEMETRY_INTERVAL || (isNowDanger && !previousDanger)) {
        lastTelemetryTime = millis();
        sendTelemetry(currentDb, peakDb);
    }
    previousDanger = isNowDanger;

    delay(20);
}
