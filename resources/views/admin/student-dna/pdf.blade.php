<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>DNA AKADEMIK 360° - {{ $student->full_name }}</title>
    <style>
        @page {
            margin: 12mm 14mm 12mm 14mm;
            size: a4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            color: #1e293b;
            line-height: 1.35;
        }
        .report-title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 6px 0 10px 0;
            color: #0f172a;
        }
        .archetype-box {
            background-color: #f5f3ff;
            border: 1.5px solid #8b5cf6;
            padding: 7px 12px;
            margin-bottom: 10px;
            border-radius: 6px;
            text-align: center;
        }
        .archetype-title {
            font-size: 11.5pt;
            font-weight: bold;
            color: #581c87;
            text-transform: uppercase;
            margin: 0;
        }
        .archetype-tagline {
            font-size: 8pt;
            font-weight: bold;
            color: #6d28d9;
            margin: 2px 0 3px 0;
        }
        .archetype-desc {
            font-size: 7.5pt;
            color: #3b0764;
            margin: 0;
            line-height: 1.25;
        }
        .info-table {
            width: 100%;
            margin-bottom: 10px;
            font-size: 8pt;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 2px 3px;
        }
        .dna-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 7.5pt;
        }
        .dna-table th, .dna-table td {
            border: 1px solid #94a3b8;
            padding: 4px 6px;
        }
        .dna-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
            color: #0f172a;
        }
        .recom-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 7px 11px;
            margin-bottom: 10px;
            border-radius: 4px;
            font-size: 7.5pt;
        }
        .signature-table {
            width: 100%;
            margin-top: 12px;
            font-size: 7.5pt;
        }
        .signature-table td {
            text-align: center;
            vertical-align: top;
            width: 33.33%;
        }
    </style>
</head>
<body>

    @php
        $possibleLogoPaths = [
            public_path('images/logo-pembda.png'),
            public_path('images/app-logo.png'),
            base_path('public/images/logo-pembda.png'),
            base_path('../public_html/images/logo-pembda.png'),
            base_path('../images/logo-pembda.png'),
            isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/images/logo-pembda.png' : '',
            isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . '/pembdahub/public/images/logo-pembda.png' : '',
        ];
        $logoData = '';
        foreach ($possibleLogoPaths as $path) {
            if (!empty($path) && file_exists($path)) {
                $raw = @file_get_contents($path);
                if ($raw) {
                    $logoData = base64_encode($raw);
                    break;
                }
            }
        }
        $logoSrc = $logoData ? 'data:image/png;base64,' . $logoData : '';
    @endphp

    {{-- Kop Yayasan & Unit Sekolah (Tata Letak Resmi Nasional / Yayasan Pembda) --}}
    <table style="width: 100%; border-bottom: 3px double #0f172a; padding-bottom: 8px; margin-bottom: 10px; border-collapse: collapse;">
        <tr>
            <td style="width: 14%; text-align: center; vertical-align: middle;">
                @if($logoSrc)
                    <img src="{{ $logoSrc }}" style="height: 60px; width: auto; max-width: 65px;" alt="Logo Yayasan PEMBDA">
                @else
                    <div style="width: 50px; height: 50px; border: 1px dashed #94a3b8; line-height: 50px; text-align: center; font-size: 8pt; color: #94a3b8;">LOGO</div>
                @endif
            </td>
            <td style="width: 86%; text-align: center; vertical-align: middle; padding-left: 5px;">
                <div style="font-size: 10pt; font-weight: bold; color: #1e293b; letter-spacing: 0.5px; text-transform: uppercase;">
                    {{ $analysis['database_identity']['foundation_name'] }}
                </div>
                <div style="font-size: 13.5pt; font-weight: 800; color: #0f172a; text-transform: uppercase; margin: 1px 0;">
                    {{ $analysis['database_identity']['school_name'] }}
                </div>
                <div style="font-size: 7.5pt; color: #334155; line-height: 1.3;">
                    {{ $analysis['database_identity']['foundation_address'] }}
                </div>
                <div style="font-size: 7pt; color: #475569; margin-top: 1px;">
                    Email: <span style="color: #2563eb;">{{ $analysis['database_identity']['foundation_email'] }}</span> &bull; Website: <span style="color: #2563eb;">{{ $analysis['database_identity']['foundation_website'] }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="report-title">
        LAPORAN PROFIL DNA AKADEMIK & PEMETAAN POTENSI 360°
    </div>

    {{-- Biodata Siswa & Identitas Resmi (100% Database) --}}
    <table class="info-table">
        <tr>
            <td style="width: 18%; font-weight: bold;">Nama Peserta Didik</td>
            <td style="width: 2%;">:</td>
            <td style="width: 38%; font-weight: bold; text-transform: uppercase;">{{ $student->full_name }}</td>
            <td style="width: 18%; font-weight: bold;">Kelas / Rombel</td>
            <td style="width: 2%;">:</td>
            <td style="width: 22%;"><b>{{ $analysis['database_identity']['classroom_name'] }}</b></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">NISN / NIS</td>
            <td>:</td>
            <td>{{ $student->nisn ?: '-' }} / {{ $analysis['database_identity']['student_nis'] }}</td>
            <td style="font-weight: bold;">Orang Tua / Wali</td>
            <td>:</td>
            <td><b>{{ $analysis['database_identity']['parent_name'] }}</b></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Unit Sekolah</td>
            <td>:</td>
            <td>{{ $analysis['database_identity']['school_name'] }}</td>
            <td style="font-weight: bold;">Alamat Siswa</td>
            <td>:</td>
            <td>{{ $analysis['database_identity']['student_address'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Tanggal Analisis</td>
            <td>:</td>
            <td>{{ now()->translatedFormat('d F Y') }}</td>
            <td style="font-weight: bold;">Akurasi Kalibrasi</td>
            <td>:</td>
            <td><b>{{ $analysis['confidence_score'] }}% ({{ $analysis['confidence_label'] }})</b></td>
        </tr>
    </table>

    {{-- Archetype Box --}}
    <div class="archetype-box">
        <div class="archetype-title">{{ $analysis['archetype']['title'] }}</div>
        <div class="archetype-tagline">"{{ $analysis['archetype']['tagline'] }}"</div>
        <div class="archetype-desc">{{ $analysis['archetype']['description'] }}</div>
    </div>

    {{-- Tabel 6 Dimensi Potensi --}}
    <table class="dna-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 32%;">Sumbu Dimensi Potensi</th>
                <th style="width: 14%;">Skor (0-100)</th>
                <th style="width: 49%;">Indikator & Sumber Data Riil PembdaHUB</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; font-weight: bold;">1</td>
                <td><b>Logika & Analitik</b></td>
                <td style="text-align: center; font-weight: bold; font-size: 9pt; color: #2563eb;">{{ $analysis['scores']['logic'] }}</td>
                <td>Nilai Rerata Eksakta (Matematika/IPA) & Ketajaman Penalaran Ujian CBT</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">2</td>
                <td><b>Komunikasi & Bahasa</b></td>
                <td style="text-align: center; font-weight: bold; font-size: 9pt; color: #059669;">{{ $analysis['scores']['communication'] }}</td>
                <td>Nilai Bahasa Indonesia, Bahasa Inggris & Partisipasi Diskusi LMS</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">3</td>
                <td><b>Keahlian Vokasi & Terapan</b></td>
                <td style="text-align: center; font-weight: bold; font-size: 9pt; color: #4f46e5;">{{ $analysis['scores']['technical'] }}</td>
                <td>Capaian Mapel Produktif Kejuruan, Praktik Laboratorium & SimLab</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">4</td>
                <td><b>Sosial & Kepemimpinan</b></td>
                <td style="text-align: center; font-weight: bold; font-size: 9pt; color: #d97706;">{{ $analysis['scores']['social'] }}</td>
                <td>Nilai Mapel Sosial, Kolaborasi Kelompok & Log Poin Reputasi Positif</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">5</td>
                <td><b>Kreativitas & Daya Inovasi</b></td>
                <td style="text-align: center; font-weight: bold; font-size: 9pt; color: #9333ea;">{{ $analysis['scores']['creative'] }}</td>
                <td>Seni Budaya, Karya Desain, Eksplorasi Mandiri & Portofolio</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">6</td>
                <td><b>Kedisiplinan & Ketekunan</b></td>
                <td style="text-align: center; font-weight: bold; font-size: 9pt; color: #e11d48;">{{ $analysis['scores']['discipline'] }}</td>
                <td>Presensi RFID Mesin ({{ $analysis['metrics']['attendance_rate'] }}%) & Ketepatan Pengumpulan Tugas</td>
            </tr>
        </tbody>
    </table>

    {{-- Rekomendasi Karir / Kuliah --}}
    <div class="recom-box">
        <div style="font-weight: bold; color: #0f172a; margin-bottom: 3px; text-transform: uppercase;">
            Rekomendasi Arah Masa Depan & Jalur Karier (Analisis Algoritmik PembdaHUB):
        </div>
        @if($analysis['school_type'] === 'SMK')
            @if(isset($analysis['recommendations']['career_tracks']))
                @foreach($analysis['recommendations']['career_tracks'] as $t)
                <div style="margin-bottom: 2px;">
                    &bull; <b>{{ $t['title'] }}</b>: {{ $t['description'] }}
                </div>
                @endforeach
            @endif
            @if(isset($analysis['recommendations']['pkl_recommendation']))
            <div style="margin-top: 3px; color: #065f46;">
                <b>Rekomendasi Tempat PKL / Industri Mitra:</b> {{ $analysis['recommendations']['pkl_recommendation'] }}
            </div>
            @endif
        @else
            @if(isset($analysis['recommendations']['college_majors']))
                <div style="margin-bottom: 2px;">Rumpun Program Studi PTN yang Direkomendasikan:</div>
                @foreach($analysis['recommendations']['college_majors'] as $m)
                <div style="margin-left: 8px; margin-bottom: 1px;">
                    - <b>{{ $m['major'] }}</b> ({{ $m['cluster'] }}) &mdash; Tingkat Kesiapan: {{ $m['readiness'] }}
                </div>
                @endforeach
            @endif
        @endif
    </div>

    {{-- Tanda Tangan Resmi (100% Database) --}}
    <table class="signature-table" style="width: 100%; margin-top: 36px; font-size: 8pt; border-collapse: collapse; page-break-inside: avoid;">
        <tr>
            <td style="width: 33.33%; text-align: center; vertical-align: top;">
                Mengetahui,<br>
                <b>Orang Tua / Wali Murid</b>
                <div style="height: 72px;"></div>
                <u><b>( {{ $analysis['database_identity']['parent_name'] !== '-' ? $analysis['database_identity']['parent_name'] : '..................................................' }} )</b></u>
            </td>
            <td style="width: 33.33%; text-align: center; vertical-align: top;">
                Gunungsitoli, {{ now()->translatedFormat('d F Y') }}<br>
                <b>Wali Kelas / Guru Pembimbing</b>
                <div style="height: 72px;"></div>
                <u><b>( {{ $analysis['database_identity']['homeroom_teacher'] !== '-' ? $analysis['database_identity']['homeroom_teacher'] : '..................................................' }} )</b></u>
            </td>
            <td style="width: 33.33%; text-align: center; vertical-align: top;">
                Mengetahui,<br>
                <b>Kepala Sekolah</b>
                <div style="height: 72px;"></div>
                <u><b>( {{ $analysis['database_identity']['principal_name'] !== 'Kepala Sekolah' ? $analysis['database_identity']['principal_name'] : '..................................................' }} )</b></u>
            </td>
        </tr>
    </table>

</body>
</html>
