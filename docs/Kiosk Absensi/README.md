# 📖 BUKU PANDUAN LENGKAP (MANUAL BOOK)
# PEMBUATAN & PENGOPERASIAN KIOSK ATTENDANCE STATION PEMBDAHUB
### *Smart Attendance Kiosk Terminal Berbasis NodeMCU ESP8266, RFID 13.56MHz, GM65 QR Scanner, LCD 20x4 I2C & DFPlayer Audio*

---

```
  ██████╗ ███████╗███╗   ███╗██████╗  █████╗ ██╗  ██╗██╗   ██╗██████╗ 
  ██╔══██╗██╔════╝████╗ ████║██╔══██╗██╔══██╗██║  ██║██║   ██║██╔══██╗
  ██████╔╝█████╗  ██╔████╔██║██║  ██║███████║███████║██║   ██║██████╔╝
  ██╔═══╝ ██╔══╝  ██║╚██╔╝██║██║  ██║██╔══██║██╔══██║██║   ██║██╔══██╗
  ██║     ███████╗██║ ╚═╝ ██║██████╔╝██║  ██║██║  ██║╚██████╔╝██████╔╝
  ╚═╝     ╚══════╝╚═╝     ╚═╝╚═════╝ ╚═╝  ╚═╝╚═╝  ╚═╝ ╚═════╝ ╚═════╝ 
                 SMART ATTENDANCE KIOSK STATION v2.0
```

---

## 📌 Daftar Isi Dokumen Manual Book

Manual Book ini terbagi menjadi 5 bab teknis yang terstruktur secara komprehensif:

* ### [📘 BAB 1: PENJELASAN CARA KERJA, FUNGSI & TEKNOLOGI](./01_PENJELASAN_DAN_TEKNOLOGI.md)
  * 1.1 Gambaran Umum (Overview Sistem)
  * 1.2 Fungsi Utama & Fitur Unggulan
  * 1.3 Diagram Alur Cara Kerja (Mermaid Sequence Diagram)
  * 1.4 Arsitektur Hardware & Enkripsi Jaringan TLS

* ### [📦 BAB 2: PERALATAN DAN BAHAN YANG DIGUNAKAN](./02_PERALATAN_DAN_BAHAN.md)
  * 2.1 Tabel Bill of Materials (BOM Lengkap Komponen)
  * 2.2 Spesifikasi Detail Sensor (RFID RC522 & GM65 QR Scanner)
  * 2.3 Spesifikasi Aktuator (LCD 20x4 I2C, DFPlayer Mini MP3, Speaker & Buzzer)
  * 2.4 Rekomendasi Casing (Akrilik, 3D Print, Layout Panel Depan)

* ### [🔌 BAB 3: GAMBAR SKEMATIK & DIAGRAM WIRING LENGKAP](./03_SKEMATIK_DAN_WIRING.md)
  * 3.1 Tabel Pemetaan Master Pinout (NodeMCU ke Seluruh Modul)
  * 3.2 Diagram Skematik Blok Rangkaian (ASCII Schematics)
  * 3.3 Aturan Pengkabelan Khusus (GM65 Wiring, DFPlayer Resistor 1kΩ, RC522 3.3V, dan Elco Buffer)

* ### [💻 BAB 4: KODE FIRMWARE & PENJELASAN TEKNIS](./04_KODE_DAN_PENJELASAN.md)
  * 4.1 Persiapan Lingkungan Arduino IDE (Board Core & Library)
  * 4.2 Struktur Direktori Audio MicroSD Card (`Folder /01/`)
  * 4.3 Source Code Lengkap Firmware NodeMCU (`Station_NodeMCU_20x4.ino`)
  * 4.4 Bedah Fungsi & Logika Komunikasi (Anti Double-Tap, Noise Filter, BearSSL HTTPS)

* ### [🚀 BAB 5: TATA CARA PENGGUNAAN & PEMELIHARAAN (SOP)](./05_TATA_CARA_PENGGUNAAN.md)
  * 5.1 Prosedur Pertama Kali Menghidupkan Stasiun (Initial Power On)
  * 5.2 Konfigurasi Modul GM65 Scanner (Setting UART & 9600 bps)
  * 5.3 Panduan Operasional Pengguna (Tap Kartu RFID & Scan QR HP)
  * 5.4 SOP Registrasi Kartu Siswa/Guru Baru (*Instant Scan-Buffer*)
  * 5.5 Tabel Matriks Pemecahan Masalah (*Troubleshooting Guide*)
  * 5.6 SOP Pemeliharaan Rutin & Perawatan Hardware

---

## 💡 Ringkasan Cepat Spesifikasi Stasiun (Quick Specs)
* **Mikrokontroler:** NodeMCU V3 ESP8266 (ESP-12F) @ 80/160 MHz
* **Input Identifikasi:** RFID Mifare 13.56 MHz SPI & GM65 CMOS Barcode/QR UART
* **Antarmuka Pengguna:** LCD 20x4 Karakter I2C + Audio DFPlayer Mini MP3 + Active Buzzer
* **Protokol Server:** REST API HTTPS over TLS (Port 443) dengan otentikasi `X-Kiosk-API-Key`
* **Fitur Jaringan:** Auto Multi-AP WiFi Failover (3 Profil WiFi Cadangan)
* **Tegangan Operasi:** 5V DC (Minimal 2 Ampere)

---
*Manual Book ini disusun untuk Perguruan PEMBDA / PembdaHUB Ecosystem.*
