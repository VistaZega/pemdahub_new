<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Template Jurnal Civitas Akademika Perguruan Pembda Nias</title>
    <style>
        @page {
            size: A4 portrait;
            margin-top: 2.0cm;
            margin-bottom: 2.0cm;
            margin-left: 2.7cm;
            margin-right: 2.2cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            line-height: 1.32;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }

        /* Header Identitas Jurnal (Diperbesar & Dibuat Lebih Megah) */
        .journal-header {
            border-bottom: 2.5px solid #0f766e;
            padding-bottom: 8px;
            margin-bottom: 12px;
            text-align: center;
        }
        .journal-title-top {
            font-size: 15.5pt;
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin: 0;
            line-height: 1.2;
        }
        .journal-subhead {
            font-size: 11pt;
            font-weight: bold;
            color: #1e293b;
            margin: 3px 0 0 0;
            letter-spacing: 0.3px;
        }
        .journal-meta {
            font-size: 9pt;
            color: #475569;
            margin-top: 3px;
            font-style: italic;
        }

        /* Info Box Template */
        .info-box {
            background-color: #f0fdf4;
            border: 1px solid #a7f3d0;
            border-left: 4px solid #059669;
            padding: 7px 10px;
            margin-bottom: 12px;
            font-size: 8.5pt;
            color: #065f46;
            border-radius: 4px;
        }
        .info-box-title {
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        /* Layout Specs Box */
        .specs-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 12px;
        }
        .specs-table th, .specs-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
        }
        .specs-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            text-align: left;
            font-weight: bold;
        }

        /* Article Header */
        .article-title-id {
            font-size: 13.5pt;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            margin: 8px 0 3px 0;
            color: #0f172a;
            line-height: 1.25;
        }
        .article-title-en {
            font-size: 10.5pt;
            font-style: italic;
            text-align: center;
            color: #475569;
            margin-bottom: 10px;
            line-height: 1.2;
        }
        .authors {
            text-align: center;
            font-size: 9.5pt;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 3px;
        }
        .affiliation {
            text-align: center;
            font-size: 8.5pt;
            color: #475569;
            margin-bottom: 12px;
        }

        /* Abstrak Box */
        .abstract-container {
            background-color: #fafafa;
            border: 1px solid #cbd5e1;
            padding: 9px;
            margin-bottom: 12px;
            border-radius: 4px;
        }
        .abstract-title {
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 2px;
            color: #0f172a;
        }
        .abstract-text {
            font-size: 8.5pt;
            text-align: justify;
            margin-bottom: 4px;
            color: #334155;
            font-style: italic;
            line-height: 1.28;
        }
        .keywords {
            font-size: 8.5pt;
            font-weight: bold;
            color: #0f172a;
        }

        /* Section Headings */
        .section-heading {
            font-size: 10.5pt;
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
            border-bottom: 1.5px solid #0f766e;
            padding-bottom: 2px;
            margin-top: 12px;
            margin-bottom: 5px;
        }

        p {
            text-align: justify;
            text-justify: inter-word;
            margin-top: 0;
            margin-bottom: 5px;
            text-indent: 0.6cm;
            line-height: 1.32;
        }

        .no-indent {
            text-indent: 0 !important;
        }

        .rule-list {
            margin-top: 3px;
            margin-bottom: 6px;
            padding-left: 18px;
            font-size: 8.5pt;
            line-height: 1.3;
        }
        .rule-list li {
            margin-bottom: 3px;
            text-align: justify;
        }

        /* References / Daftar Pustaka */
        .ref-item {
            font-size: 8.5pt;
            text-align: justify;
            padding-left: 0.7cm;
            text-indent: -0.7cm;
            margin-bottom: 4px;
            line-height: 1.25;
        }

        /* Sample Table */
        .sample-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin: 6px 0 8px 0;
        }
        .sample-table th, .sample-table td {
            border: 1px solid #cbd5e1;
            padding: 3px 5px;
            text-align: center;
        }
        .sample-table th {
            background-color: #f1f5f9;
            font-weight: bold;
        }

        /* Footer */
        .footer-note {
            margin-top: 14px;
            border-top: 1px solid #cbd5e1;
            padding-top: 5px;
            font-size: 8pt;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- ==================== HALAMAN 1 ==================== --}}

    {{-- Official Journal Header (Diperbesar sesuai permintaan) --}}
    <div class="journal-header">
        <div class="journal-title-top">JURNAL EDUSAINS & TEKNOLOGI PEMBDA (JET-PEMBDA)</div>
        <div class="journal-subhead">YAYASAN PERGURUAN PEMBDA NIAS (PEMBDA) | Dipublikasikan di PembdaHUB</div>
        <div class="journal-meta">ISSN (Online): 2988-7123 | Volume 1, Nomor 1, Tahun 2026 | URL: https://perguruanpembda.com/knowledge</div>
    </div>

    {{-- Info Status Template --}}
    <div class="info-box">
        <div class="info-box-title">📌 INFORMASI PENGGUNAAN TEMPLATE JURNAL RESMI PEMBDA</div>
        Template ini merupakan dokumen standar publikasi ilmiah Civitas Akademika Perguruan Pembda Nias. 
        Penggunaan template ini sifatnya <strong>OPSIONAL (Bebas / Tidak Wajib)</strong>. Guru & Civitas Akademika diperbolehkan mengunggah karya tulis mandiri atau menggunakan format ini untuk publikasi resmi di portal PembdaHUB.
    </div>

    {{-- Panduan Spesifikasi Layout & Formatting --}}
    <table class="specs-table">
        <tr>
            <th colspan="2">PANDUAN SPESIFIKASI LAYOUT & FORMATTING ARTIKEL JURNAL</th>
        </tr>
        <tr>
            <td width="28%"><strong>Ukuran Kertas</strong></td>
            <td>A4 (210 x 297 mm), Orientasi Portrait</td>
        </tr>
        <tr>
            <td><strong>Margin Halaman</strong></td>
            <td>Atas: 2.0 cm | Bawah: 2.0 cm | Kiri: 2.7 cm | Kanan: 2.2 cm</td>
        </tr>
        <tr>
            <td><strong>Jenis & Ukuran Font</strong></td>
            <td>Times New Roman (Header Jurnal: 15.5pt Bold | Judul: 13.5pt Bold | Isi: 10pt Justify)</td>
        </tr>
        <tr>
            <td><strong>Spasi Paragraf</strong></td>
            <td>Line Spacing: 1.32 | Indentasi Awal Paragraf: 0.6 cm | Text Align: Justify</td>
        </tr>
    </table>

    {{-- Article Title --}}
    <div class="article-title-id">
        [JUDUL ARTIKEL JURNAL INOVASI PEMBELAJARAN / KARYA TULIS ILMIAH - MAKSIMAL 15 KATA]
    </div>
    <div class="article-title-en">
        [ENGLISH ARTICLE TITLE: CLEAR, CONCISE, AND INFORMATIVE - MAXIMUM 15 WORDS]
    </div>

    {{-- Authors & Affiliations --}}
    <div class="authors">
        Nama Penulis Utama<sup>1*</sup>, Nama Penulis Kedua<sup>2</sup>, Nama Penulis Ketiga<sup>3</sup>
    </div>
    <div class="affiliation">
        <sup>1,2,3</sup> Unit Kerja / Mata Pelajaran / Program Studi, Yayasan Perguruan Pembda Nias, Indonesia<br>
        *Email Penulis Korespondensi: <u>penulis.utama@perguruanpembda.com</u>
    </div>

    {{-- Abstrak Indonesia & English --}}
    <div class="abstract-container">
        <div class="abstract-title">ABSTRAK (Bahasa Indonesia)</div>
        <div class="abstract-text">
            Tuliskan abstrak berbahasa Indonesia di sini (150 - 250 kata). Abstrak harus merangkum secara utuh isi artikel jurnal, mencakup: (1) Latar belakang ringkas & tujuan utama inovasi/penelitian, (2) Metode atau pendekatan pembelajaran/pengembangan yang digunakan, (3) Temuan atau hasil utama penerapan karya, serta (4) Kesimpulan dan dampak positifnya bagi Civitas Akademika Perguruan Pembda Nias. Gunakan kalimat yang lugas, jelas, dan tanpa rujukan pustaka.
        </div>
        <div class="keywords">
            Kata Kunci: PembdaHUB; Perguruan Pembda Nias; Inovasi Pembelajaran; Mikrokontroler; Literasi Digital.
        </div>

        <div class="abstract-title" style="margin-top: 6px;">ABSTRACT (English)</div>
        <div class="abstract-text">
            Write the English abstract here (150 - 250 words). The abstract must provide a complete summary of the article, including: (1) Background and primary objectives, (2) Methods or pedagogical approaches applied, (3) Key findings and results, and (4) Main conclusion and significance for the academic community of Yayasan Perguruan Pembda Nias. Use clear and concise language.
        </div>
        <div class="keywords">
            Keywords: PembdaHUB; Perguruan Pembda Nias; Educational Innovation; Microcontroller; Digital Literacy.
        </div>
    </div>

    {{-- ==================== HALAMAN 2 ==================== --}}

    {{-- Bab I: Pendahuluan --}}
    <div class="section-heading">I. PENDAHULUAN (INTRODUCTION)</div>
    <p>
        Pendahuluan menguraikan latar belakang masalah, konteks pembelajaran di lingkungan Yayasan Perguruan Pembda Nias, urgensi topik yang dibahas, serta kebaruan (<em>novelty</em>) atau gagasan inovatif yang ditawarkan. Sebutkan studi atau literatur terdahulu yang relevan untuk mendukung argumen pentingnya karya tulis ini. Bagian pendahuluan diakhiri dengan rumusan tujuan penulisan atau hipotesis karya secara jelas dan terstruktur.
    </p>

    {{-- Bab II: Metode --}}
    <div class="section-heading">II. METODE PENELITIAN & IMPLEMENTASI PEMBELAJARAN (METHODS & IMPLEMENTATION)</div>
    <p>
        Jelaskan metode, pendekatan, instrumen, subjek atau siswa sasaran, serta prosedur pelaksanaan penelitian atau implementasi modul pembelajaran secara terstruktur. Apabila karya ini berbasis pemanfaatan portal digital PembdaHUB (seperti penggunaan modul PDF, video tutorial, atau materi hobi), paparkan tahapan integrasi media tersebut dalam Kegiatan Belajar Mengajar (KBM) atau pengayaan siswa di kelas.
    </p>
    <p>
        Rincian langkah-langkah implementasi disusun sistematis agar pembaca atau pendidik lain di lingkungan Perguruan Pembda Nias dapat mereplikasi inovasi pembelajaran ini secara efektif.
    </p>

    {{-- Bab III: Hasil dan Pembahasan --}}
    <div class="section-heading">III. HASIL DAN PEMBAHASAN (RESULTS AND DISCUSSION)</div>
    <p>
        Sajikan data hasil penerapan, analisis pencapaian siswa atau civitas akademika, data statistik interaksi (seperti jumlah pembaca, unduhan, atau evaluasi KBM), tabel hasil pengukuran, serta pembahasan mendalam. Bandingkan hasil karya Anda dengan teori atau penelitian terdahulu untuk menunjukkan efektivitas dan dampak keberhasilannya di lingkungan Perguruan Pembda Nias.
    </p>

    {{-- Contoh Tabel --}}
    <div style="font-size: 8.5pt; font-weight: bold; margin-top: 4px; text-align: center; color: #0f172a;">
        Tabel 1. Rekapitulasi Statistik Interaksi & Pencapaian Hasil Pembelajaran Siswa
    </div>
    <table class="sample-table">
        <thead>
            <tr>
                <th width="10%">No</th>
                <th width="35%">Indikator Pengukuran</th>
                <th width="25%">Sebelum PembdaHUB</th>
                <th width="30%">Setelah PembdaHUB</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td style="text-align: left;">Tingkat Partisipasi Siswa</td>
                <td>45.0%</td>
                <td><strong>92.5%</strong></td>
            </tr>
            <tr>
                <td>2</td>
                <td style="text-align: left;">Rata-rata Nilai Evaluasi KBM</td>
                <td>68.5</td>
                <td><strong>87.0</strong></td>
            </tr>
            <tr>
                <td>3</td>
                <td style="text-align: left;">Jumlah Akses Modul Digital</td>
                <td>25 Kali/Bulan</td>
                <td><strong>450+ Kali/Bulan</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- Bab IV: Kesimpulan --}}
    <div class="section-heading">IV. KESIMPULAN DAN SARAN (CONCLUSION AND RECOMMENDATIONS)</div>
    <p>
        Kemukakan kesimpulan utama yang menjawab tujuan penulisan karya tulis. Berikan saran praktis atau rekomendasi tindak lanjut bagi guru, sekolah, dan pengembangan repositori PembdaHUB ke depan. Kesimpulan disajikan secara eksplisit tanpa menggunakan penomoran item berlebihan.
    </p>

    {{-- ==================== HALAMAN 3 ==================== --}}

    {{-- Bab V: Aturan Penulisan & Etika Publikasi --}}
    <div class="section-heading">V. ATURAN PENULISAN & ETIKA PUBLIKASI JURNAL (PUBLICATION ETHICS & RULES)</div>
    <p class="no-indent" style="font-weight: bold; color: #0f766e; margin-bottom: 2px;">
        Setiap karya yang dipublikasikan di PembdaHUB wajib mematuhi ketentuan etika publikasi ilmiah berikut:
    </p>
    <ol class="rule-list">
        <li><strong>Orisinalitas & Bebas Plagiarisme:</strong> Karya tulis yang dikirimkan harus merupakan karya asli penulis, belum pernah dipublikasikan di jurnal lain, dan memiliki tingkat kemiripan (similarity index) maksimal 20%.</li>
        <li><strong>Hak Cipta & Lisensi Publikasi:</strong> Hak cipta karya ilmiah tetap berada pada penulis. Dengan mempublikasikan di PembdaHUB, penulis memberikan lisensi terbuka <em>Creative Commons Attribution 4.0 International (CC BY)</em> untuk dapat dibaca dan diunduh oleh publik.</li>
        <li><strong>Ketentuan Gambar & Tabel:</strong> Seluruh tabel dan gambar wajib diberi nomor urut dan judul yang jelas. Judul Tabel diletakkan di bagian atas tabel, sedangkan Judul Gambar diletakkan di bagian bawah gambar dengan resolusi minimal 300 dpi.</li>
        <li><strong>Proses Penelaahan (Review):</strong> Setiap manuskrip akan melalui penelaahan kesesuaian format oleh Tim Redaksi & Dewan Pakar Yayasan Perguruan Pembda Nias sebelum diterbitkan di etalase publik.</li>
        <li><strong>Tanggung Jawab Akademis:</strong> Penulis bertanggung jawab penuh atas kebenaran isi, data, opini, serta sitasi yang dicantumkan dalam manuskrip.</li>
    </ol>

    {{-- Ucapan Terima Kasih --}}
    <div class="section-heading">UCAPAN TERIMA KASIH (ACKNOWLEDGMENT)</div>
    <p class="no-indent">
        Penulis mengucapkan terima kasih yang sebesar-besarnya kepada Pengurus Yayasan Perguruan Pembda Nias, Kepala Sekolah, rekan Guru Civitas Akademika, serta Pengembang Sistem PembdaHUB atas dukungan fasilitas, sarana, dan ruang publikasi karya ilmiah ini.
    </p>

    {{-- Daftar Pustaka --}}
    <div class="section-heading">DAFTAR PUSTAKA / REFERENCES (Standar APA 7th Edition)</div>
    
    <div style="font-weight: bold; font-size: 8.5pt; margin-top: 3px; margin-bottom: 2px; color: #0f766e;">
        [REFERENSI LOKAL / NASIONAL - MINIMAL 5 PUSTAKA]
    </div>

    <div class="ref-item">
        1. Zega, Y., & Hia, T. (2025). Inovasi Pembelajaran Digital Berbasis Mikrokontroler dan IoT pada Sekolah Menengah di Nias. <em>Jurnal Pendidikan Teknologi & Vokasi Pembda</em>, 4(1), 12–25.
    </div>
    <div class="ref-item">
        2. Kemendikbudristek. (2024). <em>Panduan Transformasi Digital dan Pembelajaran Interaktif di Satuan Pendidikan</em>. Kementerian Pendidikan, Kebudayaan, Riset, dan Teknologi Republik Indonesia.
    </div>
    <div class="ref-item">
        3. Harefa, D., & Lase, A. (2024). Penerapan Modul Digital PembdaHUB untuk Meningkatkan Literasi Sains dan Teknologi Siswa. <em>Jurnal Ilmiah Pendidikan Indonesia</em>, 10(2), 88–97.
    </div>
    <div class="ref-item">
        4. Telaumbanua, K. (2023). Pengembangan Media Pembelajaran Interaktif Berbasis Web di Wilayah Kepulauan Nias. <em>Jurnal Teknologi Pendidikan Nasional</em>, 8(3), 145–156.
    </div>
    <div class="ref-item">
        5. Yulianus, Z., & Tim PembdaHUB. (2026). Portal PembdaHUB: Integrasi LMS, CBT, dan Repositori Jurnal Civitas Akademika. <em>Jurnal Sains & Aplikasi Pembda</em>, 2(1), 1–15.
    </div>

    <div style="font-weight: bold; font-size: 8.5pt; margin-top: 5px; margin-bottom: 2px; color: #0f766e;">
        [REFERENSI INTERNASIONAL - MINIMAL 3 PUSTAKA]
    </div>

    <div class="ref-item">
        6. UNESCO. (2024). <em>Global Education Monitoring Report: Technology in Education – A Tool on Whose Terms?</em>. UNESCO Publishing, Paris.
    </div>
    <div class="ref-item">
        7. Siemens, G., & Downes, S. (2023). Connectivism and Digital Pedagogy in Modern K-12 and Higher Education Environments. <em>IEEE Transactions on Learning Technologies</em>, 16(4), 410–422.
    </div>
    <div class="ref-item">
        8. Mayer, R. E. (2022). <em>The Cambridge Handbook of Multimedia Learning</em> (3rd ed.). Cambridge University Press. https://doi.org/10.1017/9781108894333
    </div>

    {{-- Footer Hak Cipta Resmi --}}
    <div class="footer-note">
        Hak Cipta © 2026 Yayasan Perguruan Pembda Nias. Dipublikasikan secara resmi melalui Portal PembdaHUB (https://perguruanpembda.com).
    </div>

</body>
</html>
