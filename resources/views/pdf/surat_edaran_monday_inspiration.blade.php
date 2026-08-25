<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Edaran Monday Inspiration - Yayasan Perguruan Pembda Nias</title>
    <style>
        @page {
            margin: 1cm 2cm 1cm 2cm;
            size: A4 portrait;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11.5pt;
            line-height: 1.45;
            color: #000;
            margin: 0;
            padding: 0;
        }
        div, table, tr, td, th, p, ol, ul, li, h1, h2, h3, h4, span, b, i {
            box-sizing: border-box;
        }

        /* === KOP SURAT === */
        .kop-surat {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 14px;
            margin-bottom: 18px;
            position: relative;
        }
        .kop-surat .logo {
            position: absolute;
            left: 0;
            top: 4px;
            width: 78px;
            height: auto;
        }
        .kop-surat .yayasan-name {
            font-size: 12.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .kop-surat .pembda-title {
            font-size: 19pt;
            font-weight: bold;
            letter-spacing: 6px;
            margin: 2px 0 4px 0;
        }
        .kop-surat .address {
            font-size: 10pt;
            margin-top: 3px;
        }
        .kop-surat .website {
            font-size: 10pt;
            font-style: italic;
        }

        /* === ISI SURAT === */
        .surat-info {
            margin-bottom: 15px;
        }
        .surat-info table {
            width: auto;
        }
        .surat-info td {
            padding: 2px 5px 2px 0;
            vertical-align: top;
            font-size: 11.5pt;
        }
        .surat-info td.label { width: 95px; }
        .surat-info td.sep { width: 15px; text-align: center; }

        .kepada-block { margin: 12px 0 10px 0; page-break-inside: avoid; }
        .kepada-block .di-tempat { margin-left: 120px; font-style: italic; }

        .salam-pembuka { margin: 10px 0 6px 0; font-weight: bold; page-break-inside: avoid; }
        .isi-surat { margin: 8px 0; text-align: justify; }
        .isi-surat p { 
            margin-bottom: 8px; 
            text-indent: 35px; 
            page-break-inside: avoid;
            orphans: 3;
            widows: 3;
        }
        .isi-surat .no-indent { text-indent: 0; }

        /* === PROGRAM BOX === */
        .program-box {
            border: 2px solid #1a237e;
            padding: 12px 18px;
            margin: 14px 0;
            text-align: center;
            background: #f5f5ff;
            page-break-inside: avoid;
            border-radius: 4px;
        }
        .program-box .program-label {
            font-size: 10.5pt;
            font-weight: bold;
            color: #1a237e;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .program-box .program-title {
            font-size: 17pt;
            font-weight: bold;
            color: #1a237e;
            margin: 4px 0;
            letter-spacing: 2px;
        }
        .program-box .program-tagline {
            font-size: 13.5pt;
            font-weight: bold;
            font-style: italic;
            color: #303f9f;
        }
        .program-box .program-subtitle {
            font-size: 9.5pt;
            margin-top: 4px;
            color: #555;
        }

        /* === TABEL TEMA === */
        .tema-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 9.5pt;
        }
        .tema-table th {
            background: #1a237e;
            color: #fff;
            padding: 7px 8px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #1a237e;
        }
        .tema-table td {
            padding: 5px 8px;
            border: 1px solid #ccc;
            vertical-align: middle;
        }
        .tema-table tr:nth-child(even) {
            background: #f8f9ff;
        }
        .tema-table .col-mg { width: 35px; text-align: center; font-weight: bold; }
        .tema-table .col-tgl { width: 100px; text-align: center; }
        .tema-table .col-program { width: 125px; text-align: center; font-style: italic; color: #303f9f; }
        .tema-table .col-tema { }

        /* === FASE HEADER === */
        .fase-row td {
            background: #e8eaf6 !important;
            font-weight: bold;
            font-size: 10pt;
            color: #1a237e;
            text-align: center;
            padding: 6px;
        }

        /* === PETUNJUK === */
        .petunjuk-list {
            margin: 6px 0 10px 24px;
        }
        .petunjuk-list li {
            margin-bottom: 5px;
            text-align: justify;
            padding-left: 4px;
            page-break-inside: avoid;
        }

        /* === TTD === */
        .ttd-section {
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .ttd-right {
            float: right;
            text-align: center;
            width: 250px;
        }
        .ttd-right .jabatan { margin-bottom: 60px; }
        .ttd-right .nama {
            font-weight: bold;
            text-decoration: underline;
        }

        /* === TEMBUSAN === */
        .tembusan {
            clear: both;
            margin-top: 70px;
            font-size: 10.5pt;
            page-break-inside: avoid;
        }
        .tembusan ul {
            margin-left: 20px;
            list-style-type: decimal;
        }
        .tembusan ul li { margin-bottom: 2px; }

        /* Page break */
        .page-break { page-break-before: always; }

        /* Clearfix */
        .clearfix::after { content: ""; display: table; clear: both; }
    </style>
</head>
<body>

    {{-- === HALAMAN 1: SURAT EDARAN === --}}

    {{-- Kop Surat --}}
    <div class="kop-surat">
        @php
            $possibleLogoPaths = [
                public_path('images/logo_yayasan.png'),
                public_path('images/logo-yayasan.png'),
                base_path('public/images/logo_yayasan.png'),
                base_path('../public_html/images/logo_yayasan.png'),
            ];
            $logoPath = '';
            foreach ($possibleLogoPaths as $p) {
                if (file_exists($p)) { $logoPath = $p; break; }
            }
        @endphp
        @if(!empty($logoPath) && file_exists($logoPath))
            <img src="{{ $logoPath }}" class="logo" alt="Logo Yayasan Pembda">
        @endif
        <div style="margin-left: 95px; margin-right: 0;">
            <div class="yayasan-name">Yayasan Perguruan Pembangunan Daerah Nias</div>
            <div class="pembda-title">( P E M B D A )</div>
            <div class="address">Jl. Pelita No.9 Kelurahan Ilir, Kota Gunungsitoli, Sumatera Utara (22815)</div>
        </div>
    </div>

    {{-- Tanggal Surat --}}
    <div style="text-align: right; margin-bottom: 8px;">
        Gunungsitoli, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}
    </div>

    {{-- Info Surat --}}
    <div class="surat-info">
        <table>
            <tr>
                <td class="label">Nomor</td>
                <td class="sep">:</td>
                <td>___/SE/YP-PEMBDA/VII/2026</td>
            </tr>
            <tr>
                <td class="label">Lampiran</td>
                <td class="sep">:</td>
                <td>1 (satu) berkas</td>
            </tr>
            <tr>
                <td class="label">Perihal</td>
                <td class="sep">:</td>
                <td><b>Program <i>Monday Inspiration</i> — "Keep Moving Forward"<br>Tahun Pelajaran 2026/2027</b></td>
            </tr>
        </table>
    </div>

    {{-- Kepada --}}
    <div class="kepada-block">
        Kepada Yth.<br>
        <b>Seluruh Kepala Sekolah beserta Guru dan Pegawai</b><br>
        di Lingkungan Yayasan Perguruan Pembangunan Daerah Nias (Pembda)<br>
        <div class="di-tempat">di tempat</div>
    </div>

    {{-- Salam --}}
    <div class="salam-pembuka">Salam Sejahtera,</div>

    {{-- Isi Surat --}}
    <div class="isi-surat">
        <p>
            Puji syukur kita panjatkan ke hadirat Tuhan Yang Maha Esa atas berkat dan rahmat-Nya yang senantiasa menyertai 
            kita semua. Semoga seluruh sivitas akademika Yayasan Perguruan Pembangunan Daerah Nias (Pembda) senantiasa diberikan 
            kesehatan, kekuatan, dan semangat dalam menjalankan pengabdian serta tugas pendidikan.
        </p>

        <p>
            Sebagaimana kita ketahui bersama, upacara bendera hari Senin sering kali dipandang sebatas rutinitas seremonial 
            atau formalitas mingguan belaka. Melalui program pembinaan terpadu, kita ingin bersama-sama menghidupkan kembali esensi upacara 
            agar sungguh-sungguh memberikan muatan nilai yang berbobot, pesan moral yang kuat, serta inspirasi nyata bagi 
            seluruh peserta didik. Kita menyadari bahwa pembinaan karakter dan perubahan pola pikir tidak akan langsung 
            berdampak luas atau instan dalam waktu singkat, namun dengan konsistensi penyampaian pesan yang terarah setiap minggunya, 
            kita sedang membangun fondasi karakter jangka panjang bagi generasi muda Pembda.
        </p>

        <p>
            Atas dasar komitmen kita bersama untuk memperkuat karakter, semangat belajar, serta wawasan kebangsaan anak-anak kita, 
            Yayasan Perguruan Pembangunan Daerah Nias (Pembda) menghadirkan program pembinaan mingguan:
        </p>
    </div>

    {{-- Box Program --}}
    <div class="program-box">
        <div class="program-label">Program Yayasan Perguruan Pembangunan Daerah Nias (Pembda)</div>
        <div class="program-title">MONDAY INSPIRATION</div>
        <div class="program-tagline">"KEEP MOVING FORWARD"</div>
        <div class="program-subtitle">Tema Pembinaan Upacara Bendera Hari Senin — Tahun Pelajaran 2026/2027</div>
    </div>

    <div class="isi-surat">
        <p>
            Program <i>Monday Inspiration</i> dilaksanakan melalui <b>Upacara Bendera Bersama</b>, di mana jadwal pelaksanaan 
            serta penugasan Pembina Upacaranya diatur berdasarkan kesepakatan bersama seluruh Unit Sekolah di lingkungan Yayasan. 
            Setiap minggu, Pembina Upacara yang bertugas akan menyampaikan amanat berdasarkan tema pembinaan yang telah ditetapkan.
        </p>

        <p>
            Perlu kita pahami bersama bahwa tema-tema mingguan ini memiliki muatan esensi yang sangat dalam dan luas, sementara 
            alokasi waktu untuk narasi pidato pada saat upacara bendera sangatlah sempit dan terbatas. Oleh karena itu, Pembina Upacara 
            ditiap minggunya dituntut harus <b>lihai meramu dan mengemas materi</b> ini secara singkat, padat, dan jelas, 
            sehingga esensi pesan dapat tersampaikan dengan kuat, tepat sasaran, dan menggugah hati peserta didik tanpa harus memperpanjang durasi upacara.
        </p>

        <p>
            Lebih daripada itu, pembinaan ini tidak boleh berhenti hanya di lapangan upacara. <b>Selanjutnya menjadi tanggung jawab 
            setiap sekolah untuk menindaklanjuti tema mingguan tersebut</b> di dalam aktivitas keseharian unit sekolah—baik melalui 
            sapaan pagi, refleksi wali kelas, integrasi nilai dalam pembelajaran di kelas, maupun pembiasaan budaya sekolah—agar pesan 
            inspiratif yang disampaikan setiap hari Senin dapat benar-benar dihayati dan diwujudkan dalam perilaku nyata peserta didik sepanjang minggu.
        </p>
    </div>

    <div class="isi-surat">
        <p class="no-indent"><b>Petunjuk Pelaksanaan:</b></p>
        <ol class="petunjuk-list">
            <li>Pelaksanaan upacara hari Senin diselenggarakan dalam bentuk <b>Upacara Bendera Bersama</b>, dengan jadwal dan Pembina Upacara yang diatur berdasarkan kesepakatan bersama seluruh Unit Sekolah;</li>
            <li>Pembina Upacara yang bertugas wajib menyampaikan amanat berdasarkan <b>tema mingguan yang telah ditetapkan</b> dan harus <b>lihai meramu materi secara singkat, padat, dan jelas</b> mengingat terbatasnya waktu upacara;</li>
            <li>Pembina Upacara diharapkan mengontekstualisasikan materi tema dengan dinamika aktual dan kebutuhan pengembangan karakter peserta didik di lingkungan sekolah;</li>
            <li><b>Setiap unit sekolah bertanggung jawab penuh menindaklanjuti dan menguatkan tema mingguan</b> tersebut dalam kegiatan keseharian sekolah sepanjang minggu berjalan;</li>
            <li>Apabila hari Senin bertepatan dengan hari libur nasional atau libur sekolah, maka tema pembinaan dapat disesuaikan pada kesempatan atau forum pembinaan terdekat berikutnya;</li>
            <li>Daftar jadwal dan tema pembinaan selama <b>51 minggu</b> terlampir pada halaman lampiran surat edaran ini.</li>
        </ol>
    </div>

    <div class="isi-surat">
        <p>
            Demikian surat edaran ini disampaikan untuk dijadikan petunjuk serta dilaksanakan dengan penuh komitmen dan kebersamaan. 
            Atas perhatian dan kerja sama seluruh Kepala Sekolah beserta Guru dan Pegawai dalam menyukseskan program ini, 
            kami mengucapkan terima kasih.
        </p>
    </div>

    {{-- Tanda Tangan --}}
    <div class="ttd-section clearfix">
        <div class="ttd-right">
            <div class="jabatan">Ketua Yayasan,</div>
            <div class="nama">Yulianus Zega, S.Kom.,M.Pd.T</div>
            <div>Ketua Yayasan</div>
        </div>
    </div>

    {{-- === HALAMAN 2-3: LAMPIRAN TABEL TEMA === --}}
    <div class="page-break"></div>

    <div class="kop-surat">
        @if(file_exists($logoPath))
            <img src="{{ $logoPath }}" class="logo" alt="Logo Pembda">
        @endif
        <div style="margin-left: 95px; margin-right: 0;">
            <div class="yayasan-name">Yayasan Perguruan Pembangunan Daerah Nias (Pembda)</div>
            <div class="school-name" style="font-size: 14pt;">Lampiran Surat Edaran</div>
            <div class="address">Program Monday Inspiration — "Keep Moving Forward" — TP 2026/2027</div>
        </div>
    </div>

    <div style="text-align: center; margin-bottom: 15px;">
        <b style="font-size: 13pt;">JADWAL TEMA PEMBINAAN UPACARA BENDERA HARI SENIN</b><br>
        <span style="font-size: 11pt;">Tahun Pelajaran 2026/2027 | 51 Minggu (13 Juli 2026 — 28 Juni 2027)</span>
    </div>

    <table class="tema-table">
        <thead>
            <tr>
                <th class="col-mg">Mg</th>
                <th class="col-tgl">Tanggal</th>
                <th class="col-program">Program</th>
                <th class="col-tema">Tema Pembinaan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $themes = [
                    // Fase 1: Fondasi & Karakter
                    ['fase' => 'FASE 1: Fondasi & Karakter', 'start' => 1],
                    1  => 'Keep Moving Forward: Bergerak Maju Tanpa Henti',
                    2  => 'Karakter: Identitas Sejati yang Tidak Bisa Dicuri',
                    3  => 'Integritas: Berani Benar dalam Setiap Keadaan',
                    4  => 'Disiplin: Jembatan Menuju Kesuksesan',
                    5  => 'One Step Higher: Menjadi Versi Terbaik Diri Sendiri',

                    // Fase 2: Pola Pikir & Belajar
                    ['fase' => 'FASE 2: Pola Pikir & Belajar', 'start' => 6],
                    6  => 'Berani Bermimpi, Berani Bertindak',
                    7  => 'Belajar Sepanjang Hayat: Investasi yang Tidak Pernah Rugi',
                    8  => 'Life Skill: Keterampilan Hidup untuk Generasi Tangguh',
                    9  => 'Kreativitas: Menemukan Solusi di Tengah Keterbatasan',
                    10 => 'Inovasi Dimulai dari Keberanian Mencoba',
                    11 => 'Growth Mindset: Gagal Bukan Akhir, Tapi Awal Pembelajaran',

                    // Fase 3: Komunikasi & Sosial
                    ['fase' => 'FASE 3: Komunikasi & Sosial', 'start' => 12],
                    12 => 'Komunikasi yang Membangun, Bukan Menjatuhkan',
                    13 => 'Empati: Memahami Sebelum Ingin Dipahami',
                    14 => 'Kolaborasi: Bersama Kita Lebih Kuat',
                    15 => 'Kepemimpinan Dimulai dari Diri Sendiri',
                    16 => 'Mengelola Waktu: Kunci Produktivitas dan Keseimbangan Hidup',

                    // Fase 4: Teknologi & Digital
                    ['fase' => 'FASE 4: Teknologi & Digital', 'start' => 17],
                    17 => 'STEM: Berpikir Sistematis untuk Menyelesaikan Masalah',
                    18 => 'Menguasai Teknologi untuk Membangun Masa Depan',
                    19 => 'Artificial Intelligence: Peluang dan Tanggung Jawab di Era Baru',
                    20 => 'Literasi Digital: Cerdas, Kritis, dan Beretika di Dunia Maya',
                    21 => 'Berpikir Kritis: Memilah Fakta di Era Informasi',

                    // Fase 5: Kebangsaan & Diversitas
                    ['fase' => 'FASE 5: Kebangsaan & Diversitas', 'start' => 22],
                    22 => 'Unity in Diversity: Bersatu dalam Keberagaman',
                    23 => 'Toleransi: Menghargai Perbedaan sebagai Kekuatan Bangsa',
                    24 => 'Nasionalisme di Era Globalisasi: Bangga Menjadi Indonesia',
                    25 => 'Pemuda sebagai Penggerak Kemajuan Daerah',
                    26 => 'Menjadi Agen Perubahan di Lingkungan Sekitar',

                    // Fase 6: Lingkungan & Kesehatan
                    ['fase' => 'FASE 6: Lingkungan & Kesehatan', 'start' => 27],
                    27 => 'Peduli Lingkungan: Aksi Nyata untuk Bumi yang Lebih Baik',
                    28 => 'Budaya Gotong Royong di Era Modern',
                    29 => 'Kesehatan Mental: Berani Bicara, Berani Minta Tolong',
                    30 => 'Mental Tangguh: Bangkit dari Setiap Keterpurukan',
                    31 => 'Adaptif: Siap Menghadapi Perubahan Zaman',

                    // Fase 7: Kesiapan Masa Depan
                    ['fase' => 'FASE 7: Kesiapan Masa Depan', 'start' => 32],
                    32 => 'Membangun Jiwa Wirausaha: Dari Ide Menjadi Aksi',
                    33 => 'Financial Literacy: Cerdas Mengelola Uang Sejak Dini',
                    34 => 'Siap Memasuki Dunia Kerja dan Dunia Usaha',
                    35 => 'Melayani dengan Hati, Memimpin dengan Teladan',
                    36 => 'Menjadi Pribadi yang Menginspirasi Orang Lain',
                    37 => 'Prestasi Sejati Dibangun oleh Konsistensi',
                    38 => 'Etika dan Profesionalisme: Modal Utama Meraih Kepercayaan',

                    // Fase 8: Penguatan & Evaluasi
                    ['fase' => 'FASE 8: Penguatan & Evaluasi', 'start' => 39],
                    39 => 'Membangun Budaya Sekolah yang Positif dan Inklusif',
                    40 => 'Pendidikan Karakter: Pondasi Generasi Emas Indonesia',
                    41 => 'Bersyukur: Menghargai Setiap Proses dan Pencapaian',
                    42 => 'Digital Wellbeing: Keseimbangan Hidup di Era Digital',
                    43 => 'Tanggung Jawab Sosial: Berbuat Baik Tanpa Diminta',
                    44 => 'Persiapan Ujian: Belajar Cerdas, Bukan Hanya Keras',
                    45 => 'Manajemen Stres: Tetap Tenang di Bawah Tekanan',

                    // Fase 9: Refleksi & Penutup
                    ['fase' => 'FASE 9: Refleksi & Penutup', 'start' => 46],
                    46 => 'Merayakan Proses, Bukan Hanya Hasil',
                    47 => 'Refleksi Diri: Belajar dari Perjalanan Setahun',
                    48 => 'Mempersiapkan Diri untuk Babak Baru Kehidupan',
                    49 => 'Profil Pelajar Pembda: Berkarakter, Berprestasi, Berpengaruh',
                    50 => 'Legacy: Warisan Terbaik adalah Karakter yang Menginspirasi',
                    51 => 'Keep Moving Forward: Menjadikan Kemajuan sebagai Budaya Hidup',
                ];

                $firstMonday = \Carbon\Carbon::parse('2026-07-13');
            @endphp

            @foreach($themes as $key => $value)
                @if(is_array($value) && isset($value['fase']))
                    <tr class="fase-row">
                        <td colspan="4">{{ $value['fase'] }}</td>
                    </tr>
                @elseif(is_string($value))
                    <tr>
                        <td class="col-mg">{{ $key }}</td>
                        <td class="col-tgl">{{ $firstMonday->copy()->addWeeks($key - 1)->locale('id')->isoFormat('D MMM Y') }}</td>
                        <td class="col-program"><i>Keep Moving Forward</i></td>
                        <td class="col-tema">{{ $value }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px; text-align: center; font-size: 10pt; color: #555;">
        <i>"Keep Moving Forward — Bergerak Maju Tanpa Henti"</i><br>
        <b>Yayasan Perguruan Pembda Nias</b> | Tahun Pelajaran 2026/2027
    </div>

</body>
</html>
