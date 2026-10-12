# PANDUAN FIRMWARE UPDATE HARDWARE STATION PEMBDAHUB (2026)

Folder ini berisi pembaruan resmi firmware untuk seluruh hardware station presensi RFID & Barcode/QR di lingkungan Perguruan Pembda Nias (SMK, SMA, SMP, dan TEFA).

Setiap station memiliki **1 folder dan 1 file firmware `.ino` tersendiri** dengan nama folder yang identik dengan nama filenya agar langsung kompatibel dibuka di Arduino IDE tanpa error penamaan.

---

## 📋 DAFTAR STATION & PEMETAAN HARDWARE

| Nama Folder & File | Device ID | Unit Sekolah | Mikrokontroler | Layar LCD | Audio & Indikator | Pemindai Tambahan |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **`Station_SMK_01`** | `STATION-SMK-01` | SMK Swasta Pembda | NodeMCU V3 (ESP8266) | **LCD 16x2** | DFPlayer MP3 (D4) + Buzzer (D8) + LED (D3) | RFID RC522 |
| **`Station_SMK_02`** | `STATION-SMK-02` | SMK Swasta Pembda | NodeMCU V3 (ESP8266) | **LCD 16x2** | DFPlayer MP3 (D4) + Buzzer (D8) + LED (D3) | RFID RC522 |
| **`Station_SMK_03`** | `STATION-SMK-03` | SMK Swasta Pembda | NodeMCU V3 (ESP8266) | **LCD 16x2** | Buzzer (D8) + 2 LED (Hijau D3, Merah D4) *(No MP3)* | RFID RC522 |
| **`Station_SMK_04`** | `STATION-SMK-04` | SMK Swasta Pembda | ESP32 DevKit V1 | **LCD 16x2** | DFPlayer MP3 (TX2=17) + Buzzer (2) + 2 LED (15, 13) | RFID RC522 |
| **`Station_SMK_05`** | `STATION-SMK-05` | SMK Swasta Pembda | ESP32-C3 SuperMini | **LCD 16x2** | DFPlayer MP3 (TX=21) + Buzzer (2) + 2 LED (0, 1) | RFID RC522 |
| **`Station_SMA_01`** | `STATION-SMA-01` | SMAS Pembda 1 | NodeMCU V3 (ESP8266) | **LCD 20x4** | DFPlayer MP3 (D4) + Buzzer (D8) | RFID RC522 + QR GM65 (D3) |
| **`Station_SMA_02`** | `STATION-SMA-02` | SMAS Pembda 1 | NodeMCU V3 (ESP8266) | **LCD 20x4** | DFPlayer MP3 (D4) + Buzzer (D8) | RFID RC522 + QR GM65 (D3) |
| **`Station_SMA_03`** | `STATION-SMA-03` | SMAS Pembda 1 | NodeMCU V3 (ESP8266) | **LCD 20x4** | Buzzer (D8) *(Pin D4 untuk Hardware Reset RC522, No MP3)* | RFID RC522 + QR GM65 (D3) |
| **`Station_SMA_04`** | `STATION-SMA-04` | SMAS Pembda 1 | NodeMCU V3 (ESP8266) | **LCD 20x4** | DFPlayer MP3 (D4) + Buzzer (D8) | RFID RC522 + QR GM65 (D3) |
| **`Station_SMP_01`** | `STATION-SMP-01` | SMPS Pembda 2 | NodeMCU V3 (ESP8266) | **LCD 20x4** | DFPlayer MP3 (D4) + Buzzer (D8) | RFID RC522 + QR GM65 (D3) |
| **`Station_SMP_02`** | `STATION-SMP-02` | SMPS Pembda 2 | ESP32 DevKit V1 | **LCD 20x4** | DFPlayer MP3 (TX2=17) + Buzzer (2) + 2 LED (13, 15) | RFID RC522 + QR GM65 (RX2=16) |
| **`Station_SMP_03`** | `STATION-SMP-03` | SMPS Pembda 2 | NodeMCU V3 (ESP8266) | **LCD 20x4** | DFPlayer MP3 (D4) + Buzzer (D8) | RFID RC522 + QR GM65 (D3) |
| **`Station_Kiosk_TEFA`** | `KIOSK-TEFA` | TEFA SMK Pembda | NodeMCU V3 (ESP8266) | **LCD 16x2** | DFPlayer MP3 (D4) + Buzzer (D8) + LED (D3) | RFID RC522 |

---

## 🛠️ PENINGKATAN & SOLUSI ATAS KENDALA LAPANGAN

### 1. Masalah Koneksi WiFi & Jaringan LAN
* **WiFi Anti-Sleep**: 
  - Pada NodeMCU ESP8266 ditambahkan `WiFi.setSleepMode(WIFI_NONE_SLEEP)`.
  - Pada ESP32 / ESP32-C3 ditambahkan `WiFi.setSleep(false)`.
  - Menghentikan radio chip dari mode sleep/hemat daya yang menyebabkan station menjadi tidak responsif setelah menyala berjam-jam.
* **Auto-Reboot Watchdog (3 Menit)**:
  - Jika koneksi LAN/WiFi terputus dan tidak dapat terhubung kembali selama 3 menit berturut-turut, mikrokontroler akan otomatis melakukan `ESP.restart()`.
  - Ini menyimulasikan tindakan **cabut kabel power lalu colok kembali secara otomatis** tanpa membutuhkan intervensi teknisi di lokasi.
* **Dual-Mode Server Failover**:
  - Prioritas 1: Server Lokal LAN `http://50.35.89.10/...` (respon ultra cepat < 200ms).
  - Prioritas 2: Server Cloud Production `https://perguruanpembda.com/...` (failover otomatis jika LAN padam).
* **Heartbeat IoT Berkala (45 Detik)**:
  - Setiap 45 detik station mengirimkan sinyal denyut jantung (IP, RSSI dBm, free heap) ke endpoint `/api/attendance/heartbeat`.
  - Dashboard Live Station Monitor (`https://perguruanpembda.com/diag_all_stations.php?secret=pembda99`) akan langsung menampilkan status station **HIJAU / ONLINE** detik itu juga.

### 2. Tampilan Layar LCD
* **Tampilan Simetris & Presisi**:
  - Menggunakan fungsi `printCenter(row, text)` dan `printRow(row, text)` yang menghitung spasi kiri dan kanan secara presisi sesuai lebar kolom LCD (16 kolom untuk SMK, 20 kolom untuk SMA/SMP).
  - Menghilangkan tulisan yang miring atau terpotong tidak rapi.
* **Layar Siaga Berganti (Slide Carousel 5 Detik)**:
  - **Running Text / Marquee geser karakter telah DIHAPUS TOTAL** karena sifat cairan kristal LCD lambat merespons sehingga menyebabkan efek bayang (ghosting/blur).
  - Digantikan dengan **Slide Carousel Statis**: Teks tampil diam dan tegas selama 5 detik, lalu berganti ke slide berikutnya secara mulus:
    - **Slide 1**: Status Presensi & Petunjuk Scan (Tempel Kartu / Scan QR).
    - **Slide 2**: Identitas Unit Sekolah & Slogan Semangat Pembda.
    - **Slide 3**: Informasi Jaringan Teknis (Nama WiFi SSID terhubung, IP Address lokal, dan kuat sinyal).

---

## ⚙️ PENGATURAN BOARD DI ARDUINO IDE

### A. Untuk Station Berbasis NodeMCU V3 (ESP8266)
1. Buka file `.ino` terkait (misal: `Station_SMK_01/Station_SMK_01.ino`).
2. Masuk ke menu **Tools** di Arduino IDE:
   - **Board**: `NodeMCU 1.0 (ESP-12E Module)`
   - **Upload Speed**: `115200` atau `921600`
   - **CPU Frequency**: `80 MHz` (atau `160 MHz` untuk kecepatan maksimal)
   - **Flash Size**: `4MB (FS:2MB OTA:~1019KB)`

### B. Untuk Station Berbasis ESP32 DevKit V1
1. Buka file `.ino` terkait (misal: `Station_SMK_04` atau `Station_SMP_02`).
2. Masuk ke menu **Tools**:
   - **Board**: `ESP32 Dev Module`
   - **Upload Speed**: `921600` (atau `115200` jika kabel USB panjang)
   - **CPU Frequency**: `240MHz (WiFi/BT)`
   - **Flash Frequency**: `80MHz`
   - **Flash Mode**: `QIO`
   - **Partition Scheme**: `Default 4MB with spiffs (1.2MB APP/1.5MB SPIFFS)`

### C. Untuk Station Berbasis ESP32-C3
1. Buka file `.ino` terkait (`Station_SMK_05`).
2. Masuk ke menu **Tools**:
   - **Board**: `ESP32C3 Dev Module`
   - **USB CDC On Boot**: `Enabled`
   - **Flash Size**: `4MB`

---

## 📦 PERSIAPAN FILE AUDIO PADA MICRO SD (DFPLAYER MINI)
Pastikan kartu Micro SD diformat FAT32 dengan folder `01/` berisi file:
- `01/001.mp3` : *"Selamat Pagi, Silakan Absen"* (Saat alat siap/booting)
- `01/002.mp3` : *"Akses Diterima, Selamat Belajar"* (Presensi Masuk Siswa)
- `01/003.mp3` : *"Absen Pulang, Sampai Jumpa Besok"* (Presensi Pulang)
- `01/004.mp3` : *"Absen Sudah Tercatat, Terima Kasih"* (Cooldown / Kartu sudah ditempel)
- `01/005.mp3` : *"Selamat Pagi, Selamat Bekerja"* (Presensi Guru & Pegawai)
- `01/006.mp3` : *"Kartu Tidak Dikenali, Hubungi Admin"* (Kartu baru belum didaftarkan)
- `01/007.mp3` : *"Sistem Ada Gangguan, Hubungi Admin"* (WiFi terputus / kendala jaringan)
