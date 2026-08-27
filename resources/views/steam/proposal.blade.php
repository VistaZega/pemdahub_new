<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PROPOSAL INOVASI STEAMPRENEUR SMK 2026 - SMKS PEMBDA NIAS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        body {
            background-color: #E2E8F0;
            color: #0F172A;
            line-height: 1.65;
        }

        /* Top Sticky Action Bar */
        .top-action-bar {
            background: #0F172A;
            color: #FFFFFF;
            padding: 14px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 9999;
            box-shadow: 0 4px 15px rgba(0,0,0,0.25);
        }

        .action-title {
            font-weight: 800;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #FFFFFF;
        }

        .action-title span {
            color: #94A3B8;
            font-size: 12px;
            font-weight: 500;
        }

        .btn-group {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 9px 18px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #2563EB;
            color: #FFFFFF !important;
            box-shadow: 0 2px 8px rgba(37,99,235,0.4);
        }
        .btn-primary:hover {
            background: #1D4ED8;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #334155;
            color: #F8FAFC !important;
        }
        .btn-secondary:hover {
            background: #475569;
        }

        /* Page Container (A4 Setup) */
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 2.4cm 2.2cm;
            margin: 25px auto;
            background: #FFFFFF;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            position: relative;
            box-sizing: border-box;
        }

        @media print {
            body {
                background: none;
            }
            .top-action-bar {
                display: none !important;
            }
            .page {
                margin: 0;
                box-shadow: none;
                width: 100%;
                min-height: auto;
                padding: 1.8cm 1.8cm;
            }
            .page-break {
                page-break-before: always;
            }
        }

        /* Official Document Header */
        .doc-header {
            border-bottom: 3px double #1E3A8A;
            padding-bottom: 14px;
            margin-bottom: 24px;
            text-align: center;
        }
        .doc-header h4 {
            font-size: 11px;
            font-weight: 800;
            color: #475569;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .doc-header h2 {
            font-size: 17px;
            font-weight: 900;
            color: #1E3A8A;
            text-transform: uppercase;
            margin: 3px 0;
        }
        .doc-header p {
            font-size: 12px;
            color: #64748B;
            font-weight: 600;
        }

        /* Proposal Title Box */
        .title-box {
            background: linear-gradient(135deg, #1E3A8A 0%, #1E40AF 100%);
            color: #FFFFFF;
            padding: 24px 20px;
            border-radius: 10px;
            margin: 20px 0 28px 0;
            text-align: center;
        }
        .badge-cat {
            display: inline-block;
            background: #F59E0B;
            color: #111827;
            font-weight: 800;
            font-size: 10px;
            padding: 3px 10px;
            border-radius: 20px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .title-box h1 {
            font-size: 18px;
            line-height: 1.35;
            font-weight: 900;
            letter-spacing: -0.2px;
            color: #FFFFFF;
        }
        .title-box .sub-meta {
            font-size: 11px;
            color: #E0E7FF;
            margin-top: 8px;
            font-weight: 500;
        }

        /* Section Headings */
        h2.section-heading {
            color: #1E3A8A;
            font-size: 15px;
            font-weight: 900;
            border-bottom: 2px solid #E2E8F0;
            padding-bottom: 6px;
            margin-top: 30px;
            margin-bottom: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        h3 {
            color: #0F172A;
            font-size: 13.5px;
            font-weight: 800;
            margin-top: 18px;
            margin-bottom: 6px;
        }
        p {
            font-size: 12.5px;
            line-height: 1.65;
            margin-bottom: 12px;
            text-align: justify;
            color: #1E293B;
        }
        ul, ol {
            font-size: 12.5px;
            line-height: 1.65;
            padding-left: 20px;
            margin-bottom: 14px;
            color: #1E293B;
        }
        li {
            margin-bottom: 5px;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 14px 0;
            font-size: 11.5px;
        }
        table th {
            background: #F1F5F9;
            color: #1E3A8A;
            font-weight: 800;
            text-align: left;
            padding: 8px 10px;
            border: 1px solid #CBD5E1;
        }
        table td {
            padding: 7px 10px;
            border: 1px solid #CBD5E1;
            vertical-align: top;
            color: #1E293B;
        }
        table tr:nth-child(even) {
            background: #F8FAFC;
        }

        /* Highlight Boxes */
        .box-info {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-left: 4px solid #1E3A8A;
            border-radius: 6px;
            padding: 12px 14px;
            margin: 14px 0;
            font-size: 12px;
        }

        .box-blue {
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            border-radius: 6px;
            padding: 12px 14px;
            margin: 14px 0;
            font-size: 12px;
        }

        .box-amber {
            background: #FFFBEB;
            border: 1px solid #FDE68A;
            border-left: 4px solid #D97706;
            border-radius: 6px;
            padding: 12px 14px;
            margin: 14px 0;
            font-size: 12px;
        }

        .steam-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 6px;
            margin: 16px 0;
        }
        .steam-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-top: 3px solid #1E3A8A;
            border-radius: 6px;
            padding: 8px 6px;
            text-align: center;
            font-size: 10.5px;
        }
        .steam-card strong {
            display: block;
            color: #1E3A8A;
            font-size: 11px;
            margin-bottom: 3px;
            font-weight: 800;
        }

        /* Signature Section */
        .sig-container {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .sig-box {
            width: 230px;
            text-align: center;
            font-size: 11.5px;
        }
        .sig-meterai {
            width: 90px;
            height: 48px;
            border: 1px dashed #94A3B8;
            margin: 12px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9.5px;
            color: #64748B;
            border-radius: 4px;
            background: #F8FAFC;
        }
        .sig-name {
            font-weight: 800;
            text-decoration: underline;
            margin-top: 6px;
            color: #0F172A;
        }
    </style>
</head>
<body>

    <!-- Sticky Top Download & Print Bar -->
    <div class="top-action-bar">
        <div class="action-title">
            📄 PROPOSAL STEAMPRENEUR SMK 2026 <span>(SMK Swasta Pembda Nias)</span>
        </div>
        <div class="btn-group">
            <a href="{{ route('steam.index') }}" class="btn btn-secondary">
                ⬅️ Kembali ke Dashboard Lomba
            </a>
            <button class="btn btn-primary" onclick="window.print()">
                🖨️ Cetak ke PDF / Print (A4)
            </button>
        </div>
    </div>

    <!-- HALAMAN 1 -->
    <div class="page">
        <div class="doc-header">
            <h4>DIREKTORAT SEKOLAH MENENGAH KEJURUAN — KEMENDIKDASMEN</h4>
            <h2>PROPOSAL INOVASI STEAMPRENEUR SMK TAHUN 2026</h2>
            <p>Tema: "Solusi Nyata Untuk Indonesia" — Kategori Bidang Teknologi Informasi (SMK IT)</p>
        </div>

        <div class="title-box">
            <div class="badge-cat">High-Fidelity IT Prototype</div>
            <h1>PEMBDA-HUB: Ekosistem Smart School dan Kiosk IoT Berbasis Resilient-Edge untuk Transformasi Digital Vokasi Kepulauan Nias</h1>
            <div class="sub-meta">SMK Swasta Pembda Nias — Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)</div>
        </div>

        <h2 class="section-heading">I. Identitas Pengusul &amp; Orisinalitas Karya</h2>
        <table>
            <tr>
                <td width="30%"><strong>Satuan Pendidikan</strong></td>
                <td width="70%">SMK Swasta Pembda Nias (NPSN: 20220003) — <em>Unit Pengusul Inovasi</em></td>
            </tr>
            <tr>
                <td><strong>Yayasan Pengelola</strong></td>
                <td>Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)</td>
            </tr>
            <tr>
                <td><strong>Prinsip Pengembangan</strong></td>
                <td><strong style="color: #1E3A8A;">100% Karya Mandiri (In-House) Guru Teknis &amp; Siswa SMK — Tanpa Konsultan/Vendor Luar</strong></td>
            </tr>
            <tr>
                <td><strong>Status Siklus Hidup</strong></td>
                <td><strong style="color: #059669;">Telah Dimulai, Sedang Berjalan Nyata (In-Production), &amp; Terus Dikembangkan</strong></td>
            </tr>
            <tr>
                <td><strong>Cakupan Sistem Multi-Unit</strong></td>
                <td>
                    <strong>1. SMPS Pembda 2</strong> (Jenjang SMP)<br>
                    <strong>2. SMA Pembda 1</strong> (Jenjang SMA)<br>
                    <strong>3. SMKS Pembda Nias</strong> (Jenjang SMK)<br>
                    <strong>4. Unit TeFa Bengkelin</strong> (<code>www.bengkelin.cloud</code>)
                </td>
            </tr>
            <tr>
                <td><strong>Alamat Kampus</strong></td>
                <td>{{ $competitionData['school_address'] }}</td>
            </tr>
            <tr>
                <td><strong>Guru Pendamping</strong></td>
                <td><strong>{{ $competitionData['mentor_teacher']['name'] }}</strong> ({{ $competitionData['mentor_teacher']['nip'] }}) — <em>{{ $competitionData['mentor_teacher']['subject'] }}</em></td>
            </tr>
            <tr>
                <td><strong>Susunan Tim Siswa</strong></td>
                <td>
                    1. <strong>{{ $competitionData['team_leader']['name'] }}</strong> (NISN: {{ $competitionData['team_leader']['nisn'] }} &bull; {{ $competitionData['team_leader']['class'] }}) — <em>{{ $competitionData['team_leader']['role'] }}</em><br>
                    2. <strong>{{ $competitionData['team_member_1']['name'] }}</strong> (NISN: {{ $competitionData['team_member_1']['nisn'] }} &bull; {{ $competitionData['team_member_1']['class'] }}) — <em>{{ $competitionData['team_member_1']['role'] }}</em><br>
                    3. <strong>{{ $competitionData['team_member_2']['name'] }}</strong> (NISN: {{ $competitionData['team_member_2']['nisn'] }} &bull; {{ $competitionData['team_member_2']['class'] }}) — <em>{{ $competitionData['team_member_2']['role'] }}</em>
                </td>
            </tr>
        </table>

        <h2 class="section-heading">II. Ringkasan Karya (Executive Summary)</h2>
        <p>
            Transformasi digital pada institusi pendidikan di wilayah kepulauan dan 3T menghadapi tantangan kompleks: keterbatasan anggaran teknologi, ketidakstabilan infrastruktur listrik dan internet di Pulau Nias, serta fragmentasi sistem antar unit sekolah. Menjawab tantangan tersebut, tim SMK Swasta Pembda Nias mengembangkan <strong>PEMBDA-HUB</strong>, sebuah ekosistem <em>smart school multi-tenant</em> dan <em>Kiosk IoT</em> berbasis <em>Resilient-Edge Architecture</em>.
        </p>
        
        <div class="box-amber">
            <strong>Nilai Orisinalitas &amp; Kemandirian Vokasi:</strong><br>
            Karena keterbatasan biaya dan ketiadaan anggaran menyewa konsultan IT atau <em>software house</em> luar, seluruh sistem—mulai dari skema database, logika backend Laravel, antarmuka web, perakitan sirkuit elektronik, koding firmware ESP32 C++, hingga platform publik <code>www.bengkelin.cloud</code>—<strong>murni dirancang, dikoding, dan dirakit sendiri oleh kolaborasi guru teknis dan siswa-siswi SMK Swasta Pembda Nias</strong>. Sistem ini bukan prototipe statis, melainkan <strong>telah dimulai, sedang beroperasi secara nyata melayani ribuan pengguna, dan terus dikembangkan secara berkelanjutan</strong>.
        </div>

        <p>
            Inovasi ini mengintegrasikan lima pilar STEAM melalui empat modul utama:
        </p>
        <ol>
            <li><strong>Smart IoT Kiosk Attendance &amp; Multi-Entity Recognition:</strong> Perangkat keras presensi cerdas berbasis ESP32 240MHz dengan <em>dual scanning</em> (RFID &amp; QR Code) dan audio suara interaktif (DFPlayer Mini). Sistem secara cerdas mendeteksi entitas (Siswa SMP/SMA/SMK, Guru Lintas Unit, Pegawai Yayasan, dan Teknisi TeFa) serta dilengkapi <em>Fail-Safe Offline Buffer</em> yang menjamin pencatatan tetap beroperasi saat listrik padam atau internet terputus di Pulau Nias.</li>
            <li><strong>Teaching Factory (TeFa) 4.0 Ecosystem:</strong> Sinergi operasional antara platform komersial publik <code>www.bengkelin.cloud</code> dengan sistem induk sekolah untuk pencatatan presensi kerja teknisi dan jam terbang riil siswa.</li>
            <li><strong>Smart E-PKL &amp; DUDI Magic-Link Portal:</strong> Digitalisasi jurnal harian magang dengan validasi koordinat GPS dan foto kamera, serta persetujuan mentor industri tanpa login melalui <em>Signed Token Link</em> aman yang terhubung ke WhatsApp Gateway.</li>
            <li><strong>360° Student DNA &amp; Competency Analytics:</strong> Pemodelan matematika multi-variabel untuk memetakan bakat dan kesiapan kerja siswa ke dalam radar grafik 6 dimensi (<em>Logic, Technical, Creative, Social, Communication, Discipline</em>).</li>
        </ol>
        <p>
            Analisis numerasi membuktikan bahwa 1 unit Kiosk IoT PembdaHUB memiliki HPP sebesar Rp 320.000 (hemat 87,2% dibanding mesin komersial Rp 2.500.000) dengan margin laba 62,35% dan Titik Impas (BEP) tercapai pada 5 unit—yang mana tepat terserap 100% oleh 5 titik unit internal Yayasan PEMBDA Nias sendiri.
        </p>
    </div>

    <!-- HALAMAN 2 -->
    <div class="page page-break">
        <h2 class="section-heading">BAB I. Pendahuluan</h2>
        
        <h3>1.1. Latar Belakang Masalah Faktual &amp; Kemandirian Riset</h3>
        <p>
            Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA) menaungi 3 satuan pendidikan lintas jenjang (SMPS Pembda 2, SMA Pembda 1, dan SMKS Pembda Nias) serta unit usaha kejuruan <em>Teaching Factory</em>. Dalam perjalanannya, sekolah menghadapi dilema mendasar:
        </p>
        <ul>
            <li><strong>Ketiadaan Anggaran untuk Konsultan IT:</strong> Biaya pengadaan sistem informasi terpadu dan mesin absensi komersial dari vendor luar mencapai puluhan hingga ratusan juta rupiah yang tidak terjangkau bagi sekolah swasta di daerah.</li>
            <li><strong>Kerapuhan Jaringan &amp; Listrik di Wilayah 3T:</strong> Pemadaman listrik bergilir dan fluktuasi sinyal internet di Pulau Nias menyebabkan sistem komersial biasa lumpuh total.</li>
            <li><strong>Tekad Mandiri Guru &amp; Siswa:</strong> Menolak pasrah pada keadaan, guru-guru teknis kejuruan bersama siswa PPLG dan TJKT memutuskan merekayasa seluruh sistem secara mandiri dari nol (<em>from scratch</em>).</li>
        </ul>

        <h3>1.2. Status Siklus Hidup Inovasi</h3>
        <div class="box-info">
            <strong>Siklus Implementasi Proyek:</strong>
            <ul style="margin-bottom: 0;">
                <li><strong>Telah Dimulai:</strong> Pengembangan telah berjalan dan melalui 384 uji otomatis.</li>
                <li><strong>Sedang Berjalan Nyata (In-Production):</strong> Digunakan setiap hari untuk presensi Kiosk, modul CBT online, E-PKL, dan operasional TeFa.</li>
                <li><strong>Sedang &amp; Terus Dikembangkan:</strong> Penambahan modul analitik cerdas Student DNA dan integrasi AI masa depan.</li>
            </ul>
        </div>

        <h2 class="section-heading">BAB II. Tinjauan Pustaka &amp; Integrasi STEAM</h2>

        <div class="steam-grid">
            <div class="steam-card">
                <strong>SCIENCE</strong>
                Induksi magnetik RFID 13.56 MHz &amp; Efisiensi daya 0.8W.
            </div>
            <div class="steam-card">
                <strong>TECHNOLOGY</strong>
                Multi-Tenant Laravel 12, FreeRTOS C++, &amp; WA Gateway.
            </div>
            <div class="steam-card">
                <strong>ENGINEERING</strong>
                Sirkuit Dual-Scan Kiosk, Fail-Safe Buffer, &amp; Box Akrilik.
            </div>
            <div class="steam-card">
                <strong>ARTS</strong>
                Human-Centered UI Tailwind, Sound Assistant, &amp; Badges.
            </div>
            <div class="steam-card">
                <strong>MATHEMATICS</strong>
                Model Radar 6D DNA, Matrix Jadwal Lintas Unit, &amp; BEP.
            </div>
        </div>

        <h2 class="section-heading">BAB III. Metode Perancangan &amp; Pengembangan</h2>
        <p>
            Sistem dirancang melayani ekosistem yayasan terpusat yang menghubungkan seluruh satuan pendidikan dan unit usaha:
        </p>
        
        <div class="box-blue">
            <strong>Alur Ekosistem Yayasan PEMBDA-HUB:</strong><br>
            <code>[ 1 Kiosk IoT Cerdas ] ──&gt; Mendeteksi: Siswa SMPS 2 / SMA 1 / SMKS / Guru Lintas Unit / Teknisi TeFa</code><br>
            <code>[ Pelanggan Umum ] ──&gt; [ www.bengkelin.cloud ] ──&gt; [ Sinkronisasi Jam Kerja Siswa &amp; Student DNA ]</code><br>
            <code>[ Notifikasi Terpadu ] ──&gt; WhatsApp Gateway otomatis ke Orang Tua Siswa &amp; Pengurus Yayasan</code>
        </div>
    </div>

    <!-- HALAMAN 3 -->
    <div class="page page-break">
        <h2 class="section-heading">BAB IV. Hasil dan Pembahasan (Analisis Numerasi STEAMpreneur)</h2>

        <h3>4.1. Model Matematika: 360° Student DNA Scoring Algorithm</h3>
        <p>
            Skor dimensi kompetensi siswa dihitung menggunakan formula vektor multi-kriteria berikut:
        </p>
        <div class="box-blue" style="text-align: center; font-weight: 700; font-family: monospace;">
            S_dimensi = Σ [ w_i × ( (x_i - x_min) / (x_max - x_min) ) × 100 ]
        </div>
        <p>
            Di mana <em>w_i</em> adalah bobot instrumen (Presensi IoT, Nilai Praktik, Portofolio PKL, Jam Kerja TeFa), dan <em>x_i</em> adalah nilai riil yang dinormalisasi ke skala 0 - 100 untuk menghasilkan grafik radar 6 dimensi bakat siswa.
        </p>

        <h3>4.2. Perhitungan Harga Pokok Produksi (HPP) Stasiun Kiosk IoT</h3>
        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="35%">Komponen / Material</th>
                    <th width="30%">Spesifikasi</th>
                    <th width="15%">Qty</th>
                    <th width="15%">Subtotal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>1</td><td>Main Controller</td><td>ESP32 DevKit V1 30-Pin</td><td>1 Unit</td><td>Rp 55.000</td></tr>
                <tr><td>2</td><td>RFID Module</td><td>RC522 13.56MHz + Kartu</td><td>1 Set</td><td>Rp 18.000</td></tr>
                <tr><td>3</td><td>Audio Module</td><td>DFPlayer Mini + MicroSD 8GB</td><td>1 Set</td><td>Rp 38.000</td></tr>
                <tr><td>4</td><td>Output Suara</td><td>Mini Speaker 8Ω 3W Enclosed</td><td>1 Unit</td><td>Rp 12.000</td></tr>
                <tr><td>5</td><td>Output Tampilan</td><td>LCD 20x4 I2C Blue Backlight</td><td>1 Unit</td><td>Rp 42.000</td></tr>
                <tr><td>6</td><td>Indikator Visual</td><td>Dual LED + Active Buzzer</td><td>1 Set</td><td>Rp 15.000</td></tr>
                <tr><td>7</td><td>Casing Enclosure</td><td>Akrilik 3mm Custom Cutting TeFa</td><td>1 Unit</td><td>Rp 65.000</td></tr>
                <tr><td>8</td><td>Catu Daya</td><td>Adaptor 5V 2A Regulated</td><td>1 Unit</td><td>Rp 25.000</td></tr>
                <tr><td>9</td><td>Jasa Rakit Siswa</td><td>Insentif Perakitan TeFa Siswa</td><td>1 Paket</td><td>Rp 50.000</td></tr>
                <tr style="font-weight: 800; background: #EEF2FF;">
                    <td colspan="4" align="right">TOTAL HPP PER UNIT KIOSK:</td>
                    <td>Rp 320.000</td>
                </tr>
            </tbody>
        </table>

        <div class="box-info">
            <strong>Analisis Efisiensi Biaya Produksi:</strong><br>
            Biaya mesin absensi komersial di pasaran: <strong>Rp 2.500.000</strong>.<br>
            HPP Kiosk IoT PembdaHUB buatan siswa: <strong>Rp 320.000</strong>.<br>
            <strong>Efisiensi Biaya yang Dicapai = 87,2%</strong>.
        </div>

        <h3>4.3. Analisis Margin Keuntungan &amp; Titik Impas (BEP)</h3>
        <ul>
            <li><strong>Harga Jual Pasar Unit Kiosk:</strong> Rp 850.000 / unit.</li>
            <li><strong>HPP Produksi:</strong> Rp 320.000 / unit.</li>
            <li><strong>Margin Laba Kotor:</strong> Rp 530.000 / unit (<strong>62,35%</strong>).</li>
            <li><strong>Biaya Tetap Awal (Fixed Cost):</strong> Rp 2.650.000.</li>
            <li><strong>Break Even Point (BEP):</strong>
                <code>BEP = Rp 2.650.000 / Rp 530.000 = <strong>5 Unit Kiosk</strong></code>.
            </li>
            <li><strong>Validasi Implementasi Riil di Yayasan PEMBDA:</strong> Kebutuhan internal (SMPS 2: 1 unit, SMA 1: 1 unit, SMKS: 2 unit, TeFa Bengkelin: 1 unit = Total 5 unit). Titik impas langsung tercapai 100% dari serapan ekosistem internal yayasan.</li>
        </ul>

        <h2 class="section-heading">BAB V. Kesimpulan</h2>
        <p>
            Inovasi PEMBDA-HUB membuktikan bahwa ketiadaan biaya dan keterbatasan infrastruktur daerah 3T dapat dijawab dengan kemandirian riset guru dan siswa SMK. Dengan sistem yang telah berjalan nyata dan terus dikembangkan, karya ini mencerminkan esensi sejati dari program STEAMpreneur SMK.
        </p>

        <!-- Signature Section -->
        <div class="sig-container" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; text-align: center; margin-top: 40px; page-break-inside: avoid;">
            <div class="sig-box">
                Mengetahui,<br>
                <strong>Kepala SMK Swasta Pembda Nias</strong>
                <div style="height: 50px;"></div>
                <div class="sig-name" style="font-weight: bold; text-decoration: underline;">{{ $competitionData['principal']['name'] ?? 'Kepala SMK Swasta Pembda Nias' }}</div>
                <div style="font-size: 11px; color: #64748B;">{{ $competitionData['principal']['nip'] ?? 'NPY. 2022000301' }}</div>
            </div>

            <div class="sig-box">
                Menyetujui,<br>
                <strong>Guru Pendamping</strong>
                <div style="height: 50px;"></div>
                <div class="sig-name" style="font-weight: bold; text-decoration: underline;">{{ $competitionData['mentor_teacher']['name'] }}</div>
                <div style="font-size: 11px; color: #64748B;">{{ $competitionData['mentor_teacher']['nip'] }}</div>
            </div>

            <div class="sig-box">
                Gunungsitoli, 27 Agustus 2026<br>
                <strong>Ketua Tim Pengusul</strong>
                <div class="sig-meterai" style="border: 1px dashed #CBD5E1; padding: 4px; font-size: 10px; color: #94A3B8; margin: 6px auto; width: 90px;">Meterai 10.000</div>
                <div class="sig-name" style="font-weight: bold; text-decoration: underline;">{{ $competitionData['team_leader']['name'] }}</div>
                <div style="font-size: 11px; color: #64748B;">NISN: {{ $competitionData['team_leader']['nisn'] }}</div>
            </div>
        </div>
    </div>

</body>
</html>
