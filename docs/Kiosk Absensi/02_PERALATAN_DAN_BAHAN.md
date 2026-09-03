# BAB 2: PERALATAN DAN BAHAN YANG DIGUNAKAN

---

## 2.1 Daftar Komponen Utama (Bill of Materials / BOM)

Berikut adalah daftar lengkap komponen perangkat keras (*hardware*), sensor, aktuator, dan aksesoris mekanik yang dibutuhkan untuk membangun 1 unit **Pembda Attendance Kiosk Station**:

| No | Nama Komponen / Modul | Tipe / Spesifikasi | Jumlah | Fungsi / Peran |
| :--- | :--- | :--- | :---: | :--- |
| 1 | **Mikrokontroler** | NodeMCU V3 (ESP8266 ESP-12F / CP2102 atau CH340) | 1 unit | Otak pemrosesan, pengendali periferal, dan gateway WiFi HTTPS. |
| 2 | **Sensor RFID** | MFRC522 RFID Reader 13.56 MHz (SPI) | 1 unit | Membaca UID kartu Mifare / e-KTP / gantungan kunci RFID. |
| 3 | **Sensor Barcode / QR** | GROW GM65 (1D/2D Barcode & QR Scanner TTL UART) | 1 unit | Membaca QR Code NISN siswa, kartu pegawai, atau barcode HP. |
| 4 | **Layar Tampilan** | LCD 20x4 Karakter (Biru/Kuning) + Modul I2C PCF8574 | 1 unit | Menampilkan status standby, identitas siswa/guru, jam, dan notifikasi. |
| 5 | **Modul Suara / Audio** | DFPlayer Mini MP3 Player (TF-16P) | 1 unit | Memutar file audio sapaan bahasa Indonesia dari MicroSD. |
| 6 | **Kartu Memori** | MicroSD Card 4GB / 8GB / 16GB (Class 10) | 1 unit | Menyimpan file MP3 suara respons sistem. |
| 7 | **Pengeras Suara** | Mini Speaker 3 Watt 8 Ohm (Diameter 40-50mm) | 1 unit | Output suara sapaan yang jelas dan lantang. |
| 8 | **Aktuator Audio Beep** | Active Buzzer 5V | 1 unit | Memberikan umpan balik instan (*beep*) saat scan berhasil/gagal. |
| 9 | **Resistor Proteksi** | Resistor Karbon / Metal Film 1kΩ (1/4W) | 1 pcs | Proteksi level logika tegangan pin RX DFPlayer Mini (3.3V ke 5V). |
| 10 | **Kapasitor Buffer Daya** | Elco 470 µF / 1000 µF (16V / 25V) | 1 pcs | Penstabil tegangan 5V peredam spike arus GM65 & DFPlayer. |
| 11 | **Power Supply / Catu Daya**| Adaptor DC 5V 2A / 3A (Konektor Micro-USB) | 1 unit | Sumber daya utama seluruh sistem (wajib stabil min. 2 Ampere). |
| 12 | **Kabel Penghubung** | Kabel Jumper Dupont (Female-to-Female & Male-to-Female) | 1 set | Menghubungkan seluruh modul ke pinheader NodeMCU. |
| 13 | **Papan Rangkai / PCB** | PCB Dot Matrix 7x9 cm atau Mini Breadboard | 1 pcs | Tempat merapikan jalur daya (5V & GND) serta resistor/kapasitor. |

---

## 2.2 Spesifikasi Detail Sensor & Aktuator

### 1. Modul RFID RC522 (13.56 MHz)
* **Frekuensi Kerja:** 13.56 MHz (Standar ISO/IEC 14443 Type A).
* **Protokol:** SPI Bus (Clock hingga 10 MHz).
* **Tegangan Operasi:** **3.3 Volt DC** (*Peringatan: Jangan pernah hubungkan pin VCC ke 5V karena dapat merusak IC MFRC522*).
* **Jarak Baca:** 2 cm – 5 cm (tergantung kualitas antena kartu/gantungan kunci).
* **Konsumsi Arus:** 13 – 26 mA (Active), 10 µA (Sleep).

### 2. Modul GM65 Barcode & QR Code Scanner
* **Jenis Optik:** Sensor Kamera CMOS 640 x 480 piksel dengan pencahayaan LED putih internal & Laser Aimer 650nm.
* **Format yang Didukung:**
  * **1D Barcode:** EAN-13, EAN-8, UPC-A, Code 39, Code 128, Codabar, Interleaved 2 of 5.
  * **2D Barcode:** QR Code, Data Matrix, PDF417.
* **Antarmuka:** Serial TTL UART (Baudrate 9600 bps default) & USB HID.
* **Tegangan Operasi:** **5.0 Volt DC** (Dihubungkan ke pin `VU` NodeMCU).
* **Konsumsi Arus:** 70 mA (Standby), 150 – 220 mA (Peak Scanning).

### 3. Modul LCD 20x4 Karakter + Backpack I2C PCF8574
* **Kapasitas Layar:** 4 Baris x 20 Karakter (Total 80 karakter teks).
* **Antarmuka:** I2C 2-Wire (`SDA` & `SCL`), Default Address: `0x27` (atau `0x3F`).
* **Fitur:** Dilengkapi potensiometer *trimpot* di bagian belakang modul I2C untuk mengatur kontras ketajaman tulisan.
* **Tegangan Operasi:** **5.0 Volt DC** (Pin `VU`).

### 4. DFPlayer Mini MP3 Player + Speaker 3W
* **IC Utama:** YX5200-24SS.
* **Format Audio:** MP3, WAV, WMA (Sampling rate 8kHz s.d 48kHz).
* **Amplifier Internal:** Chip audio 3 Watt terintegrasi langsung (mampu mendrive speaker 3W 8Ω tanpa amplifier tambahan).
* **Tegangan Operasi:** 3.3V – 5.0V (Rekomendasi: 5.0V di pin `VU` untuk volume maksimal).

---

## 2.3 Casing, Tata Letak Mekanik & Desain Box (Enclosure)

Untuk penempatan di gerbang sekolah, lobi kantor, atau bengkel TEFA, stasiun absensi membutuhkan casing pelindung yang kokoh, tahan debu, dan ergonomis:

### Rekomendasi Bahan Casing:
1. **Akrilik Presisi Laser Cut (Tebal 3mm - 4mm):**
   * Memberikan tampilan modern, profesional, dan futuristik.
   * Bagian depan transparan/hitam glossy dengan lubang presisi untuk LCD, scanner GM65, dan tap area RFID.
2. **3D Printed Enclosure (Filamen PETG / ABS):**
   * Sangat fleksibel untuk custom bracket dinding (*wall-mount*) atau dudukan meja (*desktop stand*).
3. **Box Panel ABS Elektrik (Ukuran 200 x 150 x 80 mm):**
   * Tahan benturan (*heavy-duty*), cocok untuk area luar/semi-outdoor.

### Panduan Tata Letak Komponen Depan (Front Panel Layout):
```
┌────────────────────────────────────────────────────────┐
│           PEMBDAHUB SMART ATTENDANCE KIOSK             │
│                                                        │
│  ┌──────────────────────────────────────────────────┐  │
│  │ [LCD 20x4 I2C DISPLAY]                           │  │
│  │ Baris 1: PEMBDA HUB v2                           │  │
│  │ Baris 2: Silakan Scan Kartu/QR                   │  │
│  │ Baris 3: Status: READY                           │  │
│  │ Baris 4: --------------------                    │  │
│  └──────────────────────────────────────────────────┘  │
│                                                        │
│   ┌───────────────────────┐  ┌──────────────────────┐  │
│   │   [ AREA SCAN RFID ]  │  │ [ GM65 SCANNER EYE ] │  │
│   │     (((( • ))))       │  │      ┌────────┐      │  │
│   │   Tempelkan Kartu     │  │      │ [QR]   │      │  │
│   │   Siswa / Guru        │  │      └────────┘      │  │
│   │    Mifare 13.56MHz    │  │   Arahkan Barcode/QR │  │
│   └───────────────────────┘  └──────────────────────┘  │
│                                                        │
│          ((( ░░░░░░░░░░░░░░░░░░░░░░░░░ )))             │
│              [ LUBANG SPEAKER SUARA ]                  │
│                                                        │
│   [● Lubang Kabel Power 5V Micro-USB di Bagian Bawah]  │
└────────────────────────────────────────────────────────┘
```

### Catatan Penting Instalasi Mekanik:
* **Jarak Modul RFID ke Permukaan:** Modul RC522 harus ditempatkan maksimal 3 mm di balik panel akrilik depan. **Dilarang keras menggunakan plat besi/aluminium di sekitar antena RFID** karena logam akan menyerap gelombang medan elektromagnetik RF.
* **Jendela Optik GM65:** Biarkan lubang lensa GM65 terbuka langsung atau gunakan kaca/akrilik bening berkualitas optik tinggi tanpa goresan agar tidak mendistorsi pembacaan laser scanner.
* **Ventilasi Udara:** Berikan kisi-kisi udara kecil di samping/bawah casing agar panas dari NodeMCU dan adaptor dapat keluar dengan lancar saat menyala 24 jam non-stop.
