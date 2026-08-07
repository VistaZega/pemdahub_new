<?php
$html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Kontrol Manajemen (Contoh Terisi)</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #0a1628; --gold: #f0a500; --blue: #2196F3;
            --emer: #10b981; --red: #ef4444; --bg-body: #f1f5f9;
            --paper: #ffffff; --text-dark: #1e293b; --text-muted: #64748b;
            --border: #cbd5e1;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); color: var(--text-dark); line-height: 1.6; margin: 0; padding: 40px 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .page-header { text-align: center; margin-bottom: 40px; }
        .page-header h1 { color: var(--navy); margin: 0 0 10px 0; font-size: 2.2rem; font-weight: 800; }
        .page-header p { color: var(--text-muted); margin: 0; font-size: 1.1rem; }
        
        .paper { background: var(--paper); padding: 50px; border-radius: 8px; margin-bottom: 50px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); position: relative; }
        @media print { body { background: white; padding: 0; } .paper { box-shadow: none; margin-bottom: 0; page-break-after: always; padding: 20px; } .no-print { display: none; } }

        .kop-surat { text-align: center; border-bottom: 3px solid var(--text-dark); padding-bottom: 20px; margin-bottom: 30px; }
        .kop-surat h2 { margin: 0; font-size: 1.5rem; text-transform: uppercase; font-weight: 800; }
        .kop-surat p { margin: 5px 0 0 0; font-size: 0.95rem; font-weight: 600; color: var(--text-muted); }
        
        .kpi-box { background: #f8fafc; border-left: 5px solid var(--blue); padding: 15px 20px; margin-bottom: 30px; border-radius: 0 6px 6px 0; }
        .kpi-box h4 { margin: 0 0 5px 0; color: var(--blue); font-size: 1.1rem; }
        .kpi-box p { margin: 0; font-size: 0.95rem; font-weight: 600; }

        .form-row { margin-bottom: 15px; display: flex; align-items: flex-end; }
        .form-label { font-weight: 700; width: 220px; flex-shrink: 0; }
        .form-value { border-bottom: 1px dotted var(--text-dark); flex-grow: 1; min-height: 24px; font-weight: 600; color: var(--blue); padding-left: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 0.95rem; }
        th, td { border: 1px solid var(--border); padding: 12px 15px; text-align: left; }
        th { background-color: #f8fafc; font-weight: 700; color: var(--navy); }
        .center { text-align: center; }
        .filled { color: var(--blue); font-weight: 600; }
        .checkbox { font-family: monospace; font-size: 1.1rem; }
        
        .signature-area { margin-top: 50px; display: flex; justify-content: space-between; }
        .sig-box { text-align: center; width: 250px; }
        .sig-name { margin-top: 80px; font-weight: 700; text-decoration: underline; color: var(--navy); }
        .sig-role { font-size: 0.9rem; color: var(--text-muted); }

        .back-btn { display: block; width: max-content; margin: 0 auto 50px auto; padding: 12px 30px; background: var(--navy); color: white; text-decoration: none; font-weight: 700; border-radius: 50px; transition: 0.3s; }
        .back-btn:hover { background: var(--gold); color: var(--navy); }
        
        /* Highlight specific data colors */
        .txt-red { color: var(--red); }
        .txt-green { color: var(--emer); }
    </style>
</head>
<body>

<div class="container">
    <div class="page-header no-print">
        <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--red); color: var(--red); padding: 10px; border-radius: 8px; margin-bottom: 20px; font-weight: 700;">
            [CONTOH TERISI] Formulir ini telah diisi dengan data simulasi sebagai referensi Bapak/Ibu Guru.
        </div>
        <h1>Buku Kontrol Manajemen</h1>
        <p>Instrumen Alat Kontrol Kepala Sekolah untuk 5 Kebijakan Transformasi</p>
        <button onclick="window.print()" class="back-btn" style="margin-top: 20px; background: var(--blue); border: none; cursor: pointer;">🖨️ Cetak Formulir Kosong (Ctrl+P)</button>
    </div>

    <!-- 1. KESIAPAN & SURVEY -->
    <div id="kesiapan" class="paper">
        <div class="kop-surat">
            <h2>Instrumen #1: Evaluasi Kesiapan Guru</h2>
            <p>SMKS PEMBDA NIAS - TAHUN PELAJARAN 2026/2027</p>
        </div>
        <div class="kpi-box" style="border-color: var(--emer);">
            <h4 style="color: var(--emer);">Tujuan Tindak Lanjut</h4>
            <p>Memastikan guru yang hasil surveynya "Belum Siap" mendapatkan pembinaan (Coaching) spesifik.</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="5%" class="center">No</th>
                    <th width="35%">Nama Guru / Mapel</th>
                    <th width="20%" class="center">Status Survey</th>
                    <th width="40%">Tindak Lanjut Pembinaan (Coaching)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="center">1</td>
                    <td class="filled">Herman Telambanua, S.Pd<br><small style="color: var(--text-muted)">Bahasa Inggris</small></td>
                    <td class="filled txt-green"><span class="checkbox">[X]</span> Siap <br><span class="checkbox">[ ]</span> Belum Siap</td>
                    <td class="filled">Diberikan tugas mendampingi presentasi Bahasa Inggris untuk produk TEFA.</td>
                </tr>
                <tr>
                    <td class="center">2</td>
                    <td class="filled">Dra. Mawar Zai<br><small style="color: var(--text-muted)">Matematika</small></td>
                    <td class="filled txt-red"><span class="checkbox">[ ]</span> Siap <br><span class="checkbox">[X]</span> Belum Siap</td>
                    <td class="filled txt-red">Ikut Sesi Coaching bersama Kajur DPIB tentang perhitungan RAB Bangunan (Minggu II Juli).</td>
                </tr>
            </tbody>
        </table>
        
        <div class="signature-area">
            <div class="sig-box">
                <p>Dilaporkan Oleh,</p>
                <div class="sig-name">Yuniwati Zebua, S.Pd</div>
                <div class="sig-role">Wakasek Kurikulum</div>
            </div>
            <div class="sig-box">
                <p>Mengesahkan,</p>
                <div class="sig-name">Yulianus Zega, S.Kom., M.Pd.T</div>
                <div class="sig-role">Kepala Sekolah</div>
            </div>
        </div>
    </div>

    <!-- 2. PENILAIAN GURU -->
    <div id="penilaian" class="paper">
        <div class="kop-surat">
            <h2>Instrumen #2: Penilaian Kinerja Guru (PKG)</h2>
            <p>SMKS PEMBDA NIAS - TAHUN PELAJARAN 2026/2027</p>
        </div>
        
        <div class="kpi-box" style="border-color: var(--gold);">
            <h4 style="color: var(--gold);">Aturan Ketat SK 2026/2027</h4>
            <p>Syarat Guru dapat dipertahankan/diusulkan SK Yayasan adalah meraih SKOR RATA-RATA MINIMAL > 3.5 dari 4 Pilar di bawah ini.</p>
        </div>

        <div class="form-row"><span class="form-label">Nama Guru Dinilai</span><span class="form-value">Agus Mendrofa, S.T.</span></div>
        <div class="form-row"><span class="form-label">Mata Pelajaran / Bidang</span><span class="form-value">Produk Kreatif & Kewirausahaan (TKJ)</span></div>

        <table>
            <thead>
                <tr>
                    <th width="5%" class="center">No</th>
                    <th width="35%">Pilar Penilaian Kinerja</th>
                    <th width="45%">Bukti Fisik Nyata</th>
                    <th width="15%" class="center">Skor (1-5)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="center">1</td>
                    <td><strong>Kompetensi Praktik (30%)</strong></td>
                    <td class="filled">Membimbing siswa membuat prototipe Smart Home berbasis Arduino.</td>
                    <td class="center filled txt-green">4.5</td>
                </tr>
                <tr>
                    <td class="center">2</td>
                    <td><strong>Kontribusi Program (30%)</strong></td>
                    <td class="filled">Siswa berhasil menjual 5 paket instalasi CCTV ke warga sekitar.</td>
                    <td class="center filled txt-green">5.0</td>
                </tr>
                <tr>
                    <td class="center">3</td>
                    <td><strong>Kolaborasi (20%)</strong></td>
                    <td class="filled txt-red">Belum ada proyek gabungan lintas mapel dengan guru umum.</td>
                    <td class="center filled txt-red">2.0</td>
                </tr>
                <tr>
                    <td class="center">4</td>
                    <td><strong>Budaya Industri 5R (20%)</strong></td>
                    <td class="filled">Lab rutin dibersihkan siswa pasca praktik sesuai SOP K3.</td>
                    <td class="center filled txt-green">4.0</td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: right; font-weight: 800;">TOTAL SKOR RATA-RATA (Minimal 3.5):</td>
                    <td class="center filled" style="font-size: 1.2rem; font-weight: 800; color: var(--navy);">3.87</td>
                </tr>
            </tbody>
        </table>
        
        <div style="background: var(--emer); color: white; padding: 10px; text-align: center; font-weight: 800; border-radius: 6px;">
            KESIMPULAN: LULUS (Diusulkan Perpanjangan SK Mengajar)
        </div>

        <div class="signature-area">
            <div class="sig-box">
                <p>Guru yang Dinilai,</p>
                <div class="sig-name">Agus Mendrofa, S.T.</div>
                <div class="sig-role">NIPY. 19880405 2012 1 001</div>
            </div>
            <div class="sig-box">
                <p>Penilai Utama,</p>
                <div class="sig-name">Yulianus Zega, S.Kom., M.Pd.T</div>
                <div class="sig-role">Kepala Sekolah</div>
            </div>
        </div>
    </div>

    <!-- 3. PROGRAM STRATEGIS (TEFA) -->
    <div id="strategis" class="paper">
        <div class="kop-surat">
            <h2>Instrumen #3: Validasi Omzet TEFA</h2>
            <p>SMKS PEMBDA NIAS - TAHUN PELAJARAN 2026/2027</p>
        </div>
        
        <div class="kpi-box" style="border-color: var(--blue);">
            <h4 style="color: var(--blue);">Aturan Pelaporan TEFA</h4>
            <p>Setiap Jurusan WAJIB mencatatkan transaksi layanan/produk ke masyarakat. Jika omzet = 0, maka status TEFA dianggap fiktif.</p>
        </div>

        <div class="form-row"><span class="form-label">Nama Unit / Jurusan</span><span class="form-value">Bintang Motor (Teknik Sepeda Motor)</span></div>
        <div class="form-row"><span class="form-label">Penanggung Jawab</span><span class="form-value">Budiarto Zebua, S.T.</span></div>
        <div class="form-row"><span class="form-label">Bulan Pelaporan</span><span class="form-value">Agustus 2026</span></div>

        <table>
            <thead>
                <tr>
                    <th width="5%" class="center">No</th>
                    <th width="35%">Jenis Layanan / Penjualan</th>
                    <th width="20%" class="center">Lulus QC?</th>
                    <th width="25%" class="center">Omzet / Pemasukan</th>
                    <th width="15%" class="center">Disetor Kas?</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="center">1</td>
                    <td class="filled">Jasa Servis Ringan Honda Vario (Bp. Ahmad)</td>
                    <td class="center filled txt-green"><span class="checkbox">[X]</span> Ya</td>
                    <td class="filled">Rp 150.000</td>
                    <td class="center filled txt-green"><span class="checkbox">[X]</span> Ya</td>
                </tr>
                <tr>
                    <td class="center">2</td>
                    <td class="filled">Ganti Oli & Kampas Rem Yamaha Mio</td>
                    <td class="center filled txt-green"><span class="checkbox">[X]</span> Ya</td>
                    <td class="filled">Rp 85.000</td>
                    <td class="center filled txt-green"><span class="checkbox">[X]</span> Ya</td>
                </tr>
            </tbody>
        </table>
        
        <div class="signature-area">
            <div class="sig-box">
                <p>Disetor Oleh (Kajur),</p>
                <div class="sig-name">Budiarto Zebua, S.T.</div>
            </div>
            <div class="sig-box">
                <p>Diterima Oleh (Bendahara),</p>
                <div class="sig-name">Siti Aminah, S.E.</div>
            </div>
        </div>
    </div>

    <!-- 4. KONTRAK KINERJA JABATAN -->
    <div id="penugasan" class="paper">
        <div class="kop-surat">
            <h2>Instrumen #4: Kontrak Kinerja Jabatan</h2>
            <p>SMKS PEMBDA NIAS - TAHUN PELAJARAN 2026/2027</p>
        </div>
        
        <div class="form-row"><span class="form-label">Nama Pejabat</span><span class="form-value">Rina Melati, M.Kom</span></div>
        <div class="form-row"><span class="form-label">Jabatan Tambahan</span><span class="form-value">Kepala Bursa Kerja Khusus (BKK)</span></div>

        <p style="font-weight: 600; margin-bottom: 10px;">Target Output Riil Semester Ganjil:</p>
        <table>
            <thead>
                <tr>
                    <th width="5%" class="center">No</th>
                    <th width="60%">Deskripsi Target Pekerjaan (Harus Bisa Diukur)</th>
                    <th width="35%" class="center">Status Evaluasi Akhir Semester</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="center">1</td>
                    <td class="filled">Mengadakan 1x Job Fair mini dengan mengundang 3 Perusahaan Lokal.</td>
                    <td class="center filled txt-green">TERCAPAI (Bukti Tersedia)</td>
                </tr>
                <tr>
                    <td class="center">2</td>
                    <td class="filled">Memastikan 15 Alumni TKJ 2026 terserap magang/kerja.</td>
                    <td class="center filled txt-red">TIDAK TERCAPAI (Hanya 5 orang)</td>
                </tr>
            </tbody>
        </table>
        <div style="background: var(--red); color: white; padding: 10px; text-align: center; font-weight: 800; border-radius: 6px;">
            KEPUTUSAN: DIEVALUASI (Target Mayor Mayoritas Tidak Tercapai)
        </div>
        
        <div class="signature-area" style="margin-top:30px;">
            <div class="sig-box">
                <p>Pemegang Jabatan,</p>
                <div class="sig-name">Rina Melati, M.Kom</div>
            </div>
            <div class="sig-box">
                <p>Kepala Sekolah,</p>
                <div class="sig-name">Yulianus Zega, S.Kom., M.Pd.T</div>
            </div>
        </div>
    </div>

</div>
</body>
</html>
HTML;

file_put_contents(__DIR__ . '/public/instrumen-kontrol-kepsek.html', $html);
echo "Instrumen Kontrol rebuilt with completely filled examples.\n";
