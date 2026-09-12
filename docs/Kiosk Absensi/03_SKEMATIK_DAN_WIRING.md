# BAB 3: GAMBAR SKEMATIK & DIAGRAM WIRING LENGKAP

---

## 3.1 Tabel Pemetaan Pin Lengkap (Master Pinout Table)

Tabel berikut adalah acuan resmi pemetaan pin antara mikrokontroler **NodeMCU V3 (ESP8266 ESP-12F)** dan seluruh modul periferal pendukung:

| Pin NodeMCU | Nama GPIO | Terhubung Ke Komponen | Pin Modul | Fungsi Sinyal / Arus | Catatan Khusus |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **VU** | V-USB (5V) | GM65, LCD 20x4, DFPlayer | VCC / 5V | Jalur Catu Daya Utama (+5V) | Bersumber langsung dari Micro-USB |
| **3V3** | 3.3V Out | RFID RC522 | VCC (3.3V) | Jalur Catu Daya Sensor RFID (+3.3V)| **Wajib 3.3V! Dilarang ke 5V!** |
| **GND** | Ground | Semua Modul | GND | Common Ground (Jalur Negatif Bersama)| Hubungkan semua ground jadi satu |
| **D0** | GPIO 16 | RFID RC522 | SDA / SS | SPI Chip Select (CS) | Mengontrol jalur aktif komunikasi RFID |
| **D1** | GPIO 5 | LCD 20x4 I2C | SCL | I2C Serial Clock | Clock default bus I2C |
| **D2** | GPIO 4 | LCD 20x4 I2C | SDA | I2C Serial Data | Data default bus I2C |
| **D3** | GPIO 0 | GM65 Scanner | TX (Kabel Hitam)| SoftwareSerial RX (Menerima data QR)| Idle HIGH, pull-up aman |
| **D4** | GPIO 2 | RFID RC522 | RST (Reset) | Hardware Reset Pulse (LOW -> HIGH) | **Membangunkan kristal osilator RC522** |
| **D5** | GPIO 14 | RFID RC522 | SCK | SPI Serial Clock | Clock bus SPI bawaan ESP8266 |
| **D6** | GPIO 12 | RFID RC522 | MISO | SPI Master In Slave Out | Jalur data dari RFID ke NodeMCU |
| **D7** | GPIO 13 | RFID RC522 | MOSI | SPI Master Out Slave In | Jalur data dari NodeMCU ke RFID |
| **D8** | GPIO 15 | Active Buzzer 5V | Positif (+) | Digital Output Buzzer (Aktif HIGH) | Pulldown internal aman saat boot |
| **-** | - | GM65 Scanner | RX (Kabel Kuning)| **TIDAK DICOLOK (BEBAS)** | **Dilarang colok ke pin RX NodeMCU!**|

---

## 3.2 Diagram Skematik Blok Rangkaian (Block Schematic)

```
                              ┌───────────────────────────┐
                              │  ADAPTOR 5V 2A MICRO-USB  │
                              └─────────────┬─────────────┘
                                            │
                                    [+5V]   │   [GND]
                                ┌───────────┴───────────┐
                                │                       │
 ┌──────────────────────────────▼───────────────────────▼─────────────────────────────┐
 │                                NODEMCU V3 (ESP-12F)                               │
 │                                                                                   │
 │   [3.3V]    [GND]   [D0]    [D5]    [D7]    [D6]   [3.3V]                         │
 └───┬─────────┬───────┬───────┬───────┬───────┬──────┬──────────────────────────────┘
     │         │       │       │       │       │      │
     │         │       │       │       │       │      │  (Jalur Bus SPI)
 ┌───▼─────────▼───────▼───────▼───────▼───────▼──────▼────────┐
 │ 3.3V       GND     SDA     SCK     MOSI    MISO    RST      │
 │                   MODUL RFID RC522 (13.56 MHz)              │
 └─────────────────────────────────────────────────────────────┘

 ┌───────────────────────────────────────────────────────────────────────────────────┐
 │                                NODEMCU V3 (ESP-12F)                               │
 │                                                                                   │
 │   [VU/5V]   [GND]   [D2]    [D1]    [D3]    [D4]              [D8]                │
 └───┬─────────┬───────┬───────┬───────┬───────┬─────────────────┬───────────────────┘
     │         │       │       │       │       │                 │
     │         │       │       │       │       │ [Resistor 1kΩ]  │
     │         │       │       │       │       └───────┐         │
     │         │       │       │       │               │         │
     ├─────────┼───────┼───────┼───────┘               │         │
     │         │       │       │                       │         │
 ┌───▼─────────▼───────▼───────▼─────┐                 │         │
 │  VCC       GND     SDA     SCL    │                 │         │
 │         LCD 20x4 I2C PCF8574      │                 │         │
 └───────────────────────────────────┘                 │         │
     │         │                                       │         │
     ├─────────┼───────────────────────┐               │         │
     │         │                       │               │         │
 ┌───▼─────────▼───────┐           ┌───▼───────────────▼──┐  ┌───▼─────────▼──┐
 │  5V        GND      │           │  VCC             RX  │  │  (+)       (-) │
 │ (Merah)  (Hijau)    │           │ (Pin 1)       (Pin 2)│  │                │
 │ TX (Hitam) ───► [D3]│           │     DFPLAYER MINI    │  │  ACTIVE BUZZER │
 │ RX (Kuning) ──► (NC)│           │  SPK1          SPK2  │  │       5V       │
 │   GM65 QR SCANNER   │           └───┬──────────────┬───┘  └────────────────┘
 └─────────────────────┘               │              │
                                   ┌───▼──────────────▼───┐
                                   │  SPEAKER 3 WATT 8Ω   │
                                   └──────────────────────┘
```

---

## 3.3 Detail Pengkabelan Khusus Per Modul

### 1. Pengkabelan Modul GM65 (QR & Barcode Scanner)
Modul GM65 memiliki 4 kabel warna:
* **Kabel Merah (5V):** Hubungkan ke pin **VU** NodeMCU.
* **Kabel Hijau (GND):** Hubungkan ke pin **GND** NodeMCU.
* **Kabel Hitam (TXD):** Hubungkan ke pin **D3 (GPIO0)** NodeMCU.
* **Kabel Kuning (RXD):** **BIARKAN LEPAS / BEBAS (Tidak Dicolok).**
  > ⚠️ **Peringatan Kritis:** Jangan menghubungkan kabel kuning ini ke pin berlabel `RX` pada NodeMCU. Pin `RX` bawaan NodeMCU terhubung ke chip USB-UART (CH340/CP2102) yang berkomunikasi dengan komputer. Jika kabel kuning dicolok ke sana, sinyal serial akan bertabrakan dan sistem tidak akan bisa menerima data.

### 2. Pengkabelan Modul RFID RC522 (13.56 MHz)
* **VCC (3.3V):** Hubungkan ke pin **3V3** NodeMCU.
* **RST (Reset):** Hubungkan langsung ke pin **3V3** NodeMCU.
* **GND:** Hubungkan ke pin **GND** NodeMCU.
* **MISO:** Hubungkan ke pin **D6** NodeMCU.
* **MOSI:** Hubungkan ke pin **D7** NodeMCU.
* **SCK:** Hubungkan ke pin **D5** NodeMCU.
* **SDA / SS:** Hubungkan ke pin **D0** NodeMCU.

### 3. Pengkabelan DFPlayer Mini & Resistor 1kΩ
Tegangan logika pin NodeMCU adalah 3.3 Volt, sedangkan modul DFPlayer Mini beroperasi pada tegangan 5 Volt.
* Hubungkan pin **D4 (GPIO2)** NodeMCU ke salah satu kaki **Resistor 1kΩ**.
* Hubungkan kaki Resistor 1kΩ lainnya ke pin **RX (Pin 2)** pada DFPlayer Mini.
* Resistor 1kΩ ini berfungsi untuk meredam arus balik (*noise suppression*) dan menjaga transmisi sinyal audio tetap jernih tanpa distorsi (*humming/buzz*).
* Hubungkan pin **SPK_1** dan **SPK_2** langsung ke kedua kutub mini speaker 3 Watt 8 Ohm.

### 4. Pemasangan Kapasitor Elco Penstabil Tegangan (Power Decoupling)
* Pasang 1 buah **Kapasitor Elektrolit (Elco) 470 µF atau 1000 µF (16V)**:
  * **Kaki Positif (+) Elco:** Hubungkan ke jalur **VU (5V)**.
  * **Kaki Negatif (-) Elco (Garis Putih/Minus):** Hubungkan ke jalur **GND**.
* **Fungsi:** Menyimpan cadangan energi instan saat modul GM65 menyalakan lampu flash/laser dan saat DFPlayer memutar suara pada volume tinggi, sehingga tegangan 5V tidak turun (*voltage drop*) yang dapat menyebabkan NodeMCU restart mendadak (*brownout reset*).
