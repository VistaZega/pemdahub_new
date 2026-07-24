<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Template Jurnal Civitas Akademika Perguruan Pembda Nias</title>
    <style>
        @page {
            size: A4 portrait;
            margin-top: 2.5cm;
            margin-bottom: 2.5cm;
            margin-left: 3.0cm;
            margin-right: 2.5cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10.5pt;
            line-height: 1.35;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }

        /* Header Identitas Jurnal */
        .journal-header {
            border-bottom: 2px solid #0f766e;
            padding-bottom: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .journal-title-top {
            font-size: 11pt;
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .journal-subhead {
            font-size: 9.5pt;
            font-weight: bold;
            color: #334155;
            margin: 2px 0 0 0;
        }
        .journal-meta {
            font-size: 8.5pt;
            color: #64748b;
            margin-top: 3px;
            font-style: italic;
        }

        /* Info Box Template */
        .info-box {
            background-color: #f0fdf4;
            border: 1px solid #a7f3d0;
            border-left: 4px solid #059669;
            padding: 10px 12px;
            margin-bottom: 20px;
            font-size: 8.5pt;
            color: #065f46;
            border-radius: 4px;
        }
        .info-box-title {
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        /* Layout Specs Box */
        .specs-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 20px;
        }
        .specs-table th, .specs-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
        }
        .specs-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            text-align: left;
            font-weight: bold;
        }

        /* Article Header */
        .article-title-id {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            margin: 15px 0 5px 0;
            color: #0f172a;
            line-height: 1.2;
        }
        .article-title-en {
            font-size: 11pt;
            font-style: italic;
            text-align: center;
            color: #475569;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        .authors {
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 4px;
        }
        .affiliation {
            text-align: center;
            font-size: 8.5pt;
            color: #475569;
            margin-bottom: 15px;
        }

        /* Abstrak Box */
        .abstract-container {
            background-color: #fafafa;
            border: 1px solid #e2e8f0;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .abstract-title {
            font-size: 9.5pt;
            font-weight: bold;
            margin-bottom: 4px;
            color: #0f172a;
        }
        .abstract-text {
            font-size: 9pt;
            text-align: justify;
            margin-bottom: 6px;
            color: #334155;
            font-style: italic;
        }
        .keywords {
            font-size: 8.5pt;
            font-weight: bold;
            color: #0f172a;
        }

        /* Section Headings */
        .section-heading {
            font-size: 11pt;
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
            border-bottom: 1.5px solid #0f766e;
            padding-bottom: 3px;
            margin-top: 18px;
            margin-bottom: 8px;
        }

        p {
            text-align: justify;
            text-justify: inter-word;
            margin-top: 0;
            margin-bottom: 8px;
            text-indent: 0.7cm;
        }

        .no-indent {
            text-indent: 0 !important;
        }

        /* References / Daftar Pustaka */
        .ref-item {
            font-size: 9pt;
            text-align: justify;
            padding-left: 0.8cm;
            text-indent: -0.8cm;
            margin-bottom: 6px;
            line-height: 1.3;
        }

        /* Footer */
        .footer-note {
            margin-top: 30px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 10px;
            font-size: 8pt;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- Official Journal Header --}}
    <div class="journal-header">
        <div class="journal-title-top">JURNAL EDUSAINS & TEKNOLOGI PEMBDA (JET-PEMBDA)</div>
        <div class="journal-subhead">YAYASAN PERGURUAN PEMBDA NIAS (PEMBDA) | Dipublikasikan di PembdaHUB</div>
        <div class="journal-meta">ISSN (Online): 2988-7123 | Volume 1, Nomor 1, Tahun 2026 | URL: https://perguruanpembda.com/knowledge</div>
    </div>

    {{-- Info Status Template --}}
    <div class="info-box">
        <div class="info-box-title">📌 INFORMASI PENGGUNAAN TEMPLATE JURNAL RESMI PEMBDA</div>
        Template ini merupakan standar panduan penulisan jurnal ilmiah Civitas Akademika Perguruan Pembda Nias. 
        Penggunaan template ini sifatnya <strong>OPSIONAL (Bebas / Tidak Wajib)</strong>. Guru & Civitas Akademika diperbolehkan mengunggah karya tulis mandiri atau menggunakan format ini jika ingin mengikuti standar penulisan publikasi resmi.
    </div>

    {{-- Panduan Spesifikasi Layout & Formatting --}}
    <table class="specs-table">
        <tr>
            <th colspan="2">PANDUAN SPESIFIKASI LAYOUT & FORMATTING ARTIKEL</th>
        </tr>
        <tr>
            <td width="30%"><strong>Ukuran Kertas</strong></td>
            <td>A4 (210 x 297 mm), Orientasi Portrait</td>
        </tr>
        <tr>
            <td><strong>Margin Halaman</strong></td>
            <td>Atas: 2.5 cm | Bawah: 2.5 cm | Kiri: 3.0 cm | Kanan: 2.5 cm</td>
        </tr>
        <tr>
            <td><strong>Jenis & Ukuran Font</strong></td>
            <td>Times New Roman (Judul: 14pt Bold | Bab: 11pt Bold | Isi: 10.5pt Justify)</td>
        </tr>
        <tr>
            <td><strong>Spasi Paragraf</strong></td>
            <td>Line Spacing: 1.15 | Indentasi Awal Paragraf: 0.7 cm | Text Align: Justify</td>
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

        <div class="abstract-title" style="margin-top: 10px;">ABSTRACT (English)</div>
        <div class="abstract-text">
            Write the English abstract here (150 - 250 words). The abstract must provide a complete summary of the article, including: (1) Background and primary objectives, (2) Methods or pedagogical approaches applied, (3) Key findings and results, and (4) Main conclusion and significance for the academic community of Yayasan Perguruan Pembda Nias. Use clear and concise language.
        </div>
        <div class="keywords">
            Keywords: PembdaHUB; Perguruan Pembda Nias; Educational Innovation; Microcontroller; Digital Literacy.
        </div>
    </div>

    {{-- Bab I: Pendahuluan --}}
    <div class="section-heading">I. PENDAHULUAN (INTRODUCTION)</div>
    <p>
        Pendahuluan menguraikan latar belakang masalah, konteks pembelajaran di lingkungan Yayasan Perguruan Pembda Nias, urgensi topik yang dibahas, serta kebaruan (<em>novelty</em>) atau gagasan inovatif yang ditawarkan. Sebutkan studi atau literatur terdahulu yang relevan untuk mendukung argumen pentingnya karya tulis ini. Bagian pendahuluan diakhiri dengan rumusan tujuan penulisan atau hipotesis karya secara jelas.
    </p>

    {{-- Bab II: Metode --}}
    <div class="section-heading">II. METODE PENELITIAN & IMPLEMENTASI PEMBELAJARAN (METHODS & IMPLEMENTATION)</div>
    <p>
        Jelaskan metode, pendekatan, instrumen, subjek atau siswa sasaran, serta prosedur pelaksanaan penelitian atau implementasi modul pembelajaran secara terstruktur. Apabila karya ini berbasis pemanfaatan portal digital PembdaHUB (seperti penggunaan modul PDF, video tutorial, atau materi hobi), paparkan tahapan integrasi media tersebut dalam Kegiatan Belajar Mengajar (KBM) atau pengayaan siswa.
    </p>

    {{-- Bab III: Hasil dan Pembahasan --}}
    <div class="section-heading">III. HASIL DAN PEMBAHASAN (RESULTS AND DISCUSSION)</div>
    <p>
        Sajikan data hasil penerapan, analisis pencapaian siswa atau civitas akademika, data statistik interaksi (seperti jumlah pembaca, unduhan, atau evaluasi KBM), tabel hasil pengukuran, serta pembahasan mendalam. Bandingkan hasil karya Anda dengan teori atau penelitian terdahulu untuk menunjukkan efektivitas dan dampak keberhasilannya di lingkungan Perguruan Pembda Nias.
    </p>

    {{-- Bab IV: Kesimpulan dan Saran --}}
    <div class="section-heading">IV. KESIMPULAN DAN SARAN (CONCLUSION AND RECOMMENDATIONS)</div>
    <p>
        Kemukakan kesimpulan utama yang menjawab tujuan penulisan karya tulis. Berikan saran praktis atau rekomendasi tindak lanjut bagi guru, sekolah, dan pengembangan repositori PembdaHUB ke depan. Kesimpulan disajikan secara eksplisit tanpa menggunakan penomoran item berlebihan.
    </p>

    {{-- Ucapan Terima Kasih --}}
    <div class="section-heading">UCAPAN TERIMA KASIH (ACKNOWLEDGMENT)</div>
    <p class="no-indent">
        Penulis mengucapkan terima kasih kepada Pengurus Yayasan Perguruan Pembda Nias, Kepala Sekolah, rekan Guru Civitas Akademika, serta Pengembang Sistem PembdaHUB atas dukungan fasilitas, sarana, dan ruang publikasi karya ilmiah ini.
    </p>

    {{-- Daftar Pustaka --}}
    <div class="section-heading">DAFTAR PUSTAKA / REFERENCES (Standar APA 7th Edition)</div>
    
    <div style="font-weight: bold; font-size: 9pt; margin-top: 6px; margin-bottom: 4px; color: #0f766e;">
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

    <div style="font-weight: bold; font-size: 9pt; margin-top: 10px; margin-bottom: 4px; color: #0f766e;">
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

    {{-- Footer --}}
    <div class="footer-note">
        Hak Cipta © 2026 Yayasan Perguruan Pembda Nias. Dipublikasikan secara resmi melalui Portal PembdaHUB (https://perguruanpembda.com).
    </div>

</body>
</html>
