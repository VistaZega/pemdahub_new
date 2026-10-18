/* =========================================================================================
 * PEMBDA NOISE CONTROL & CLASSROOM MONITORING SYSTEM
 * Microcontroller : ESP32 (ESP-WROOM-32 / NodeMCU-32S)
 * Sensor Audio    : INMP441 (Digital I2S MEMS Microphone)
 * Display         : OLED 1.3" I2C (SH1106, Address 0x3C via Adafruit_SH110X)
 * Indikator LED   : Hijau (18), Kuning (19), Merah (23)
 * Cloud Server    : https://perguruanpembda.com/noicecontrol
 * =========================================================================================
 * PIN HARDWARE:
 * 1. INMP441 Microphone (I2S):
 *    - SCK  -> GPIO 26  (Serial Clock / BCLK)  <-- SESUAI RANGKAIAN FISIK ANDA!
 *    - WS   -> GPIO 25  (Word Select / LRCK)
 *    - SD   -> GPIO 32  (Serial Data Out)
 *    - VDD  -> 3.3V
 *    - GND  -> GND
 *    - L/R  -> GND (Left Channel)
 * 
 * 2. OLED 1.3" (I2C):
 *    - SDA  -> GPIO 21
 *    - SCL  -> GPIO 22
 *    - VCC  -> 3.3V
 *    - GND  -> GND
 * 
 * 3. 3x Indikator LED:
 *    - LED Hijau  (Aman)          -> GPIO 18 (Resistor 220-330 Ohm ke GND)
 *    - LED Kuning (Sedikit Ribut) -> GPIO 19 (Resistor 220-330 Ohm ke GND)
 *    - LED Merah  (Terlalu Ribut) -> GPIO 23 (Resistor 220-330 Ohm ke GND)
 * 
 * 4. Buzzer Alarm:
 *    - Positif (+) / VCC          -> GPIO 4  (Alarm Bunyi Saat Ribut)
 *    - Negatif (-) / GND          -> GND
 * =========================================================================================
 */

#include <Arduino.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SH110X.h>
#include <driver/i2s.h>
#include <math.h>
#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// ================= PENGATURAN WIFI & SERVER =================
const char* WIFI_SSID     = "VST:BlackHole";      // Ganti dengan SSID WiFi/Hotspot Anda
const char* WIFI_PASSWORD = "pelita31";          // Ganti dengan Password WiFi Anda
const char* SERVER_URL    = "https://perguruanpembda.com/noicecontrol/api/telemetry";

// Token Alat (Salin dari website perguruanpembda.com/noicecontrol)
const char* DEVICE_TOKEN  = "nd_MBKIYTv6GR79bf05lUJEiHZtKcbzDZNp";
const char* FIRMWARE_VER  = "1.2.0-PRO";

// ================= PIN MAPPING =================
#define MIC_SCK  26   // Pin SCK INMP441 (GPIO 26)
#define MIC_WS   25   // Pin WS INMP441  (GPIO 25)
#define MIC_SD   32   // Pin SD INMP441  (GPIO 32)
#define I2S_PORT I2S_NUM_0

#define PIN_LED_HIJAU   18
#define PIN_LED_KUNING  19
#define PIN_LED_MERAH   23
#define PIN_BUZZER      4    // Pin Buzzer Alarm (GPIO 4)

// Inisialisasi Layar OLED SH1106
Adafruit_SH1106G oled(128, 64, &Wire, -1);

// Ambang Batas Desibel Suara
float thresholdGreen   = 65.0; // < 65 dB  : AMAN (LED Hijau)
float thresholdYellow  = 75.0; // 65-75 dB : SEDANG (LED Kuning)
float thresholdRed     = 80.0; // >= 80 dB : BAHAYA / TERLALU RIBUT (LED Merah & Kirim WA)

// Buffer Audio I2S
#define SAMPLES_COUNT 512
int32_t samples[SAMPLES_COUNT];

// Variabel Suara & Telemetri
float currentDb = 45.0;
float peakDb    = 45.0;
String currentStatus = "safe";
String className     = "Ruang Kelas";
unsigned long lastTelemetryTime = 0;
const unsigned long TELEMETRY_INTERVAL = 3000;
bool isAlertSent = false;

// ================= SETUP I2S INMP441 =================
void setupI2S() {
    i2s_config_t config = {};
    config.mode = (i2s_mode_t)(I2S_MODE_MASTER | I2S_MODE_RX);
    config.sample_rate = 16000;
    config.bits_per_sample = I2S_BITS_PER_SAMPLE_32BIT;
    config.channel_format = I2S_CHANNEL_FMT_ONLY_LEFT;
    config.communication_format = I2S_COMM_FORMAT_STAND_I2S;
    config.intr_alloc_flags = 0;
    config.dma_buf_count = 4;
    config.dma_buf_len = 256;
    config.use_apll = false;
    config.tx_desc_auto_clear = false;
    config.fixed_mclk = 0;

    esp_err_t hasil = i2s_driver_install(I2S_PORT, &config, 0, nullptr);
    if (hasil != ESP_OK) {
        Serial.println("I2S driver install gagal!");
    }

    i2s_pin_config_t pins = {};
    pins.mck_io_num = I2S_PIN_NO_CHANGE;
    pins.bck_io_num = MIC_SCK;
    pins.ws_io_num = MIC_WS;
    pins.data_out_num = I2S_PIN_NO_CHANGE;
    pins.data_in_num = MIC_SD;

    hasil = i2s_set_pin(I2S_PORT, &pins);
    if (hasil != ESP_OK) {
        Serial.println("I2S set pin gagal!");
    }

    // Buang data awal mikrofon
    for (int i = 0; i < 4; i++) {
        size_t dibaca = 0;
        i2s_read(I2S_PORT, samples, sizeof(samples), &dibaca, pdMS_TO_TICKS(100));
    }
}

// ================= BACA SUARA & HITUNG DESIBEL (RESPONSIF) =================
float readNoiseLevel() {
    size_t bytesRead = 0;
    esp_err_t hasil = i2s_read(I2S_PORT, samples, sizeof(samples), &bytesRead, pdMS_TO_TICKS(150));
    int jumlah = bytesRead / sizeof(int32_t);

    if (hasil != ESP_OK || jumlah == 0) {
        return 35.0;
    }

    // Normalisasi audio 24-bit ke rentang -1.0 s.d +1.0
    double total = 0;
    double totalKuadrat = 0;

    for (int i = 0; i < jumlah; i++) {
        double x = (double)(samples[i] >> 8) / 8388608.0;
        total += x;
        totalKuadrat += x * x;
    }

    // Hilangkan komponen DC Offset untuk mendapatkan RMS murni
    double rata = total / jumlah;
    double variansi = totalKuadrat / jumlah - (rata * rata);
    if (variansi < 0) variansi = 0;

    double rms = sqrt(variansi);
    
    // Hitung dBFS (-75 s.d -20 dBFS)
    float dbfs = 20.0 * log10(rms > 0.000000001 ? rms : 0.000000001);

    // Konversi dBFS ke estimasi dB SPL (Sound Pressure Level) yang realistis:
    // Suara hening kamar: ~40-48 dB
    // Percakapan sedang : ~60-70 dB
    // Suara keras/tepuk : ~80-95 dB
    float dbSPL = dbfs + 115.0f;
    dbSPL = constrain(dbSPL, 35.0f, 110.0f);

    return dbSPL;
}

// ================= KONTROL LED TIGA WARNA =================
void updateLedStatus(float db) {
    if (db >= thresholdRed) {
        // ZONA 3: TERLALU RIBUT (Merah ON, Kuning & Hijau OFF)
        digitalWrite(PIN_LED_MERAH, HIGH);
        digitalWrite(PIN_LED_KUNING, LOW);
        digitalWrite(PIN_LED_HIJAU, LOW);
        currentStatus = "danger";
    } 
    else if (db >= thresholdGreen) {
        // ZONA 2: SEDIKIT RIBUT (Kuning ON, Merah & Hijau OFF)
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

// ================= KONTROL ALARM BUZZER INTERMITEN =================
void updateBuzzer(bool isDanger) {
    static unsigned long lastBeep = 0;
    static bool beepState = false;
    if (isDanger) {
        unsigned long now = millis();
        // Pola bip-bip intermiten: 120ms ON, 120ms OFF (alarm berulang tanpa delay)
        if (now - lastBeep >= 120) {
            lastBeep = now;
            beepState = !beepState;
            digitalWrite(PIN_BUZZER, beepState ? HIGH : LOW);
        }
    } else {
        digitalWrite(PIN_BUZZER, LOW);
        beepState = false;
    }
}

// ================= GAMBAR TAMPILAN OLED 1.3 INCH (HERO dB & TEKS SANGAT JELAS) =================
void drawOledDisplay(float db) {
    oled.clearDisplay();
    oled.setTextColor(SH110X_WHITE);

    // 1. Header Bar: Teks Title Case Bersih (Gaya Huruf Normal, Bukan Kapital Kaku)
    oled.setTextSize(1);
    oled.setCursor(0, 0);
    String headerName = className;
    if (headerName == "" || headerName == "Ruang Kelas") headerName = "Pembda Hub";
    if (headerName.length() > 10) headerName = headerName.substring(0, 10);
    oled.print(headerName);

    oled.setCursor(84, 0);
    if (WiFi.status() == WL_CONNECTED) {
        oled.print("Wifi On");
    } else {
        oled.print("Offline");
    }
    oled.drawFastHLine(0, 9, 128, SH110X_WHITE);

    // 2. HERO: Angka Utama Desibel (Besar, Tebal, Center-Stage)
    char dbBuf[8];
    dtostrf(db, 4, 1, dbBuf);
    String sDb = String(dbBuf);
    sDb.trim();

    // Hitung posisi horizontal agar pas di tengah layar (100 px total)
    int startX = (sDb.length() > 4) ? 4 : 14;

    oled.setTextSize(3); // Font besar Size 3 (18x21 px)
    oled.setCursor(startX, 13);
    oled.print(sDb);

    oled.setTextSize(2); // Satuan dB Size 2 (12x16 px)
    oled.setCursor(startX + (sDb.length() * 18) + 4, 18);
    oled.print("dB");

    // 3. Status Badge: Ramping, Elegan & Menggunakan Teks Normal (Title Case)
    if (currentStatus == "danger") {
        // Mode Bahaya / Ribut: Kotak Peringatan Menyala
        oled.fillRoundRect(32, 38, 64, 11, 2, SH110X_WHITE);
        oled.setTextColor(SH110X_BLACK, SH110X_WHITE);
        oled.setTextSize(1);
        oled.setCursor(46, 40);
        oled.print("Ribut!");
        oled.setTextColor(SH110X_WHITE);
    } else if (currentStatus == "warning") {
        // Mode Waspada / Sedang: Garis Tipis Elegan
        oled.drawRoundRect(32, 38, 64, 11, 2, SH110X_WHITE);
        oled.setTextSize(1);
        oled.setCursor(46, 40);
        oled.print("Sedang");
    } else {
        // Mode Aman / Kondusif: Garis Tipis Elegan
        oled.drawRoundRect(32, 38, 64, 11, 2, SH110X_WHITE);
        oled.setTextSize(1);
        oled.setCursor(52, 40);
        oled.print("Aman");
    }

    // 4. Bar Graph VU-Meter Dinamis (Skala 35 dB - 100 dB) di Bagian Bawah
    oled.drawRect(0, 53, 128, 8, SH110X_WHITE);
    int barWidth = map(constrain((int)db, 35, 100), 35, 100, 0, 124);
    if (barWidth > 0) {
        oled.fillRect(2, 55, barWidth, 4, SH110X_WHITE);
    }
    // Garis Batas Ambang Merah (Threshold 80 dB)
    int redMarkerX = map((int)thresholdRed, 35, 100, 0, 124) + 2;
    oled.drawFastVLine(redMarkerX, 52, 10, SH110X_WHITE);

    oled.display();
}

// ================= KIRIM TELEMETRI KE CLOUD PEMBDAHUB =================
void sendTelemetry(float db, float peak) {
    if (WiFi.status() != WL_CONNECTED) {
        return; // Lewati jika offline
    }

    WiFiClientSecure secureClient;
    secureClient.setInsecure(); // Mengizinkan HTTPS tanpa validasi sertifikat CA

    HTTPClient http;
    bool beginOk = false;
    if (String(SERVER_URL).startsWith("https")) {
        beginOk = http.begin(secureClient, SERVER_URL);
    } else {
        beginOk = http.begin(SERVER_URL);
    }

    if (!beginOk) return;

    http.addHeader("Content-Type", "application/json");
    http.addHeader("X-Device-Token", DEVICE_TOKEN);
    http.setTimeout(5000);

    StaticJsonDocument<256> doc;
    doc["token"]    = DEVICE_TOKEN;
    doc["db"]       = db;
    doc["peak_db"]  = peak;
    doc["firmware"] = FIRMWARE_VER;

    String jsonString;
    serializeJson(doc, jsonString);

    int httpCode = http.POST(jsonString);
    Serial.printf("[Telemetri] Kirim %0.1f dB | Response: %d\n", db, httpCode);

    if (httpCode == 200) {
        String response = http.getString();
        StaticJsonDocument<512> resDoc;
        DeserializationError error = deserializeJson(resDoc, response);

        if (!error) {
            if (resDoc.containsKey("threshold_green")) thresholdGreen = resDoc["threshold_green"];
            if (resDoc.containsKey("threshold_yellow")) thresholdYellow = resDoc["threshold_yellow"];
            if (resDoc.containsKey("threshold_red")) thresholdRed = resDoc["threshold_red"];
            if (resDoc.containsKey("classroom")) className = resDoc["classroom"].as<String>();
            if (resDoc.containsKey("alert_sent")) isAlertSent = resDoc["alert_sent"];
        }
    }
    http.end();
}

// ================= SETUP UTAMA =================
void setup() {
    Serial.begin(115200);

    // Setup Pin LED & Buzzer
    pinMode(PIN_LED_HIJAU, OUTPUT);
    pinMode(PIN_LED_KUNING, OUTPUT);
    pinMode(PIN_LED_MERAH, OUTPUT);
    pinMode(PIN_BUZZER, OUTPUT);

    // Self-Test: Ketiga LED dan Buzzer berbunyi sejenak saat startup
    digitalWrite(PIN_LED_HIJAU, HIGH);
    digitalWrite(PIN_LED_KUNING, HIGH);
    digitalWrite(PIN_LED_MERAH, HIGH);
    digitalWrite(PIN_BUZZER, HIGH);
    delay(80);
    digitalWrite(PIN_BUZZER, LOW);

    // Inisialisasi I2C OLED
    Wire.begin(21, 22);
    Wire.setClock(400000);
    delay(200);

    if (!oled.begin(0x3C, true)) {
        Serial.println("Inisialisasi OLED gagal!");
    }

    oled.setContrast(255);
    oled.clearDisplay();
    oled.setTextColor(SH110X_WHITE);
    oled.setTextSize(1);
    oled.setCursor(10, 20);
    oled.println("PEMBDA NOISE MONITOR");
    oled.setCursor(18, 38);
    oled.println("Menginisialisasi...");
    oled.display();

    delay(700);
    digitalWrite(PIN_LED_HIJAU, LOW);
    digitalWrite(PIN_LED_KUNING, LOW);
    digitalWrite(PIN_LED_MERAH, LOW);

    // Inisialisasi Mikrofon INMP441 (SCK=26, WS=25, SD=32)
    setupI2S();

    // Koneksi WiFi ke Hotspot/Router
    WiFi.mode(WIFI_STA);
    WiFi.setAutoReconnect(true);
    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
    Serial.print("\n[WiFi] Menghubungkan ke: ");
    Serial.println(WIFI_SSID);

    int wifiRetry = 0;
    while (WiFi.status() != WL_CONNECTED && wifiRetry < 15) {
        delay(300);
        Serial.print(".");
        wifiRetry++;
    }

    if (WiFi.status() == WL_CONNECTED) {
        Serial.println("\n[WiFi] Berhasil Terhubung!");
        Serial.print("[WiFi] IP ESP32: ");
        Serial.println(WiFi.localIP());
    } else {
        Serial.println("\n[WiFi] Belum tersambung (akan mencoba otomatis di background).");
    }
}

// ================= LOOP UTAMA =================
void loop() {
    // 1. Baca desibel suara dari mikrofon INMP441
    float db = readNoiseLevel();

    // 2. Filter perataan suara (Responsif terhadap lonjakan suara)
    float faktor = (db > currentDb) ? 0.60f : 0.25f;
    currentDb += faktor * (db - currentDb);

    if (db > peakDb) {
        peakDb = db;
    } else {
        peakDb = peakDb * 0.98;
    }

    // 3. Kendalikan Nyala LED Tiga Warna & Buzzer Sesuai Batas Suara
    updateLedStatus(currentDb);
    updateBuzzer(currentStatus == "danger");

    // 4. Render Layar OLED secara halus
    static unsigned long lastDisp = 0;
    if (millis() - lastDisp >= 60) {
        lastDisp = millis();
        drawOledDisplay(currentDb);
    }

    // 5. Coba sambungkan ulang WiFi jika sempat terputus
    if (WiFi.status() != WL_CONNECTED) {
        static unsigned long lastReconnect = 0;
        if (millis() - lastReconnect >= 10000) {
            lastReconnect = millis();
            Serial.println("[WiFi] Reconnecting...");
            WiFi.reconnect();
        }
    }

    // 6. Kirim data telemetri ke server PembdaHUB jika terhubung WiFi
    static bool prevDanger = false;
    bool isDanger = (currentStatus == "danger");

    if (millis() - lastTelemetryTime >= TELEMETRY_INTERVAL || (isDanger && !prevDanger)) {
        lastTelemetryTime = millis();
        sendTelemetry(currentDb, peakDb);
    }
    prevDanger = isDanger;

    delay(10);
}
