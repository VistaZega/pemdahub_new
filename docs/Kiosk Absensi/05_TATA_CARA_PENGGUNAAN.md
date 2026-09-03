# BAB 5: TATA CARA PENGGUNAAN & PEMELIHARAAN (SOP)

---

## 5.1 Prosedur Menghidupkan Stasiun (Initial Power On)

1. **Pemeriksaan Fisik:**
   * Pastikan MicroSD card sudah terpasang dengan benar pada slot DFPlayer Mini.
   * Pastikan tidak ada kabel yang terjepit atau terlepas di dalam casing.
2. **Sambungkan Catu Daya:**
   * Hubungkan kabel Micro-USB dari Adaptor 5V 2A ke port daya stasiun absensi.
3. **Proses Booting Otomatis:**
   * Layar LCD 20x4 akan menyala dan menampilkan teks `PEMBDA HUB v2`.
   * Stasiun akan mencari sinyal WiFi dan terhubung ke jaringan.
   * Setelah terkoneksi, speaker akan memutar audio sapaan `001.mp3` (*"Selamat Pagi, Silakan Lakukan Absensi"*).
   * Baris ke-4 LCD akan menunjukkan `Status: READY`.

---

## 5.2 Konfigurasi Scanner GM65 (Hanya Dilakukan Sekali Saat Pertama Kali Dirakit)

Agar GM65 bekerja pada mode serial otomatis, lakukan langkah berikut:
1. Nyalakan perangkat stasiun absensi.
2. Buka gambar Barcode Konfigurasi di layar HP atau kertas manual.
3. Dekatkan kamera GM65 ke barcode:
   * **Barcode 1:** **`Series Output`** (Aktifkan mode Serial TTL). Scanner akan berbunyi *bip*.
   * **Barcode 2:** **`9600bps (Default)`** (Kunci kecepatan baudrate 9600). Scanner akan berbunyi *bip*.
   * **Barcode 3 (Opsional):** **`Sense Mode`** (Deteksi otomatis saat ada objek mendekat).
4. Cabut kabel power stasiun selama 2 detik lalu colokkan kembali untuk menyimpan konfigurasi permanen ke chip internal GM65.

---

## 5.3 Panduan Operasional Pengguna (Siswa, Guru, & Pegawai)

### 1. Absensi Menggunakan Kartu RFID:
1. Dekatkan kartu RFID siswa/guru ke area bertanda **RFID SCAN** (jarak 1 – 3 cm).
2. Tahan kartu selama **0.5 detik** sampai terdengar bunyi *bip-bip* dua kali.
3. Lihat layar LCD:
   * **Nama dan Kelas/Jabatan** Anda akan muncul di layar.
   * Jam kehadiran (*Check-In* atau *Check-Out*) akan tertera.
   * Speaker akan menyapa ramah sesuai status (*"Selamat Belajar"* untuk siswa atau *"Selamat Bertugas"* untuk guru).

### 2. Absensi Menggunakan QR Code / Barcode HP:
1. Buka kartu pelajar digital atau QR NISN pada layar smartphone Anda (atur kecerahan layar HP minimal 50%).
2. Arahkan layar HP ke depan lensa **GM65 Scanner** dengan jarak sekitar **8 – 15 cm**.
3. Scanner akan mendeteksi dan berbunyi *bip*. Layar LCD akan langsung menampilkan nama dan memproses kehadiran.

---

## 5.4 Registrasi Kartu Siswa/Guru Baru (Fitur Instant Scan-Buffer)

Tidak perlu mengetik 8 hingga 10 digit kode heksadesimal kartu secara manual! PembdaHUB memiliki fitur **Instant Scan-Buffer**:

```
[1. Tap Kartu Baru di Stasiun] ──► [2. Server Simpan UID di Buffer] ──► [3. Admin Klik 'Daftarkan RFID' di Web] ──► [4. Kolom UID Terisi Otomatis!]
```

### Langkah-langkah Registrasi:
1. Ambil kartu RFID baru yang belum terdaftar.
2. Tempelkan kartu tersebut ke **Stasiun Kiosk**.
3. Stasiun akan berbunyi 4 kali dan LCD menampilkan `** KARTU BARU ** Daftarkan di Admin`.
4. Buka Browser di laptop/komputer Admin TU, lalu login ke **Admin Dashboard PembdaHUB**.
5. Buka menu **Data Siswa** atau **Data Guru/Pegawai** ➜ Klik tombol **"Daftarkan RFID"** pada siswa/guru yang bersangkutan.
6. Modal registrasi RFID akan terbuka dan **kolom RFID UID sudah otomatis terisi kode kartu yang baru saja di-tap!**
7. Klik **"Simpan"**. Kartu kini langsung aktif dan dapat langsung digunakan untuk absen.

---

## 5.5 Tabel Pemecahan Masalah (Troubleshooting Matrix)

| Gejala Masalah | Kemungkinan Penyebab | Langkah Solusi Perbaikan |
| :--- | :--- | :--- |
| **LCD menampilkan `Status: OFFLINE`** | Router WiFi mati, SSID/Password salah, atau jangkauan sinyal lemah. | 1. Pastikan WiFi sekolah aktif.<br>2. Cek konfigurasi `WIFI_SSID` & `WIFI_PASSWORD` di kode.<br>3. Dekatkan stasiun ke Access Point. |
| **LCD gelap atau tulisan kotak hitam penuh** | Kontras LCD belum disesuaikan atau kabel daya 5V longgar. | Putar obeng kecil pada potensiometer biru (*trimpot*) di belakang modul I2C LCD sampai tulisan terlihat tajam dan jelas. |
| **Kartu RFID di-tap tetapi tidak ada respon** | Modul RC522 tidak terdeteksi SPI atau kabel RST/3.3V lepas. | 1. Periksa kabel SPI (D0, D5, D6, D7).<br>2. Pastikan pin RST dan VCC RC522 tercolok ke **3.3V** (Bukan 5V!).<br>3. Cek Serial Monitor saat booting apakah muncul *RFID OK*. |
| **GM65 menyala lampu tetapi tidak kirim data** | Kabel RX/TX terbalik atau GM65 belum di-set ke mode *Series Output*. | 1. Pastikan kabel kuning dilepas.<br>2. Pastikan kabel hitam ke pin **D3**.<br>3. Scan kembali barcode **Series Output** dan **9600bps**. |
| **Tidak ada suara dari speaker** | File MP3 salah format/folder, atau resistor 1KΩ belum terpasang. | 1. Pastikan MicroSD diformat FAT32.<br>2. Pastikan file ada di folder `/01/001.mp3` s.d `007.mp3`.<br>3. Cek pin RX DFPlayer terhubung ke D4 via resistor 1kΩ. |
| **Stasiun sering restart mendadak (*reboot loop*)** | Arus adaptor kurang dari 2A (*voltage drop*). | Ganti adaptor dengan charger 5V 2A atau 5V 3A berkualitas baik dan pasang Elco 1000µF pada jalur 5V dan GND. |

---

## 5.6 Pemeliharaan Rutin & Perawatan (Maintenance SOP)

* **Pembersihan Lensa Scanner:** Bersihkan kaca pelindung optik GM65 dengan kain microfiber lembut setiap 1 bulan sekali untuk mencegah debu menumpuk.
* **Pemeriksaan Koneksi Kabel:** Periksa kekencangan baut dan soket jumper setiap semester untuk memastikan tidak ada kabel yang kendor akibat getaran.
* **Pembaruan Firmware (OTA / USB):** Jika ada penambahan fitur suara atau protokol baru, firmware dapat di-flash ulang via port Micro-USB tanpa perlu membongkar modul pendukung.
