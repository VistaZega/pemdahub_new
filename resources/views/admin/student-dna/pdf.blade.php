<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>DNA AKADEMIK 360° - {{ $student->full_name }}</title>
    <style>
        @page {
            margin: 18mm 15mm 18mm 15mm;
            size: a4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9.5pt;
            color: #1e293b;
            line-height: 1.45;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .school-name {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f172a;
            margin: 0;
        }
        .foundation-name {
            font-size: 8.5pt;
            color: #475569;
            margin: 0;
        }
        .report-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 10px 0 14px 0;
            color: #0f172a;
        }
        .archetype-box {
            background-color: #f3e8ff;
            border: 1.5px solid #a855f7;
            padding: 10px 14px;
            margin-bottom: 14px;
            border-radius: 6px;
            text-align: center;
        }
        .archetype-title {
            font-size: 13pt;
            font-weight: bold;
            color: #581c87;
            text-transform: uppercase;
            margin: 0;
        }
        .archetype-tagline {
            font-size: 9pt;
            font-weight: bold;
            color: #7e22ce;
            margin: 2px 0 4px 0;
        }
        .archetype-desc {
            font-size: 8.5pt;
            color: #3b0764;
            margin: 0;
        }
        .info-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 9pt;
        }
        .info-table td {
            padding: 2px 4px;
        }
        .dna-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 8.5pt;
        }
        .dna-table th, .dna-table td {
            border: 1px solid #94a3b8;
            padding: 5px 8px;
        }
        .dna-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
        }
        .recom-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            margin-bottom: 14px;
            border-radius: 4px;
            font-size: 8.5pt;
        }
        .signature-table {
            width: 100%;
            margin-top: 20px;
            font-size: 8.5pt;
        }
        .signature-table td {
            text-align: center;
            vertical-align: top;
            width: 33.33%;
        }
    </style>
</head>
<body>

    {{-- Kop Yayasan & Sekolah --}}
    <table class="header-table">
        <tr>
            <td style="width: 15%; text-align: center;">
                <img src="{{ public_path('images/logo_yayasan.png') }}" style="height: 55px;" alt="Logo" onerror="this.style.display='none'">
            </td>
            <td style="width: 85%; text-align: center;">
                <div class="foundation-name">YAYASAN PERGURUAN PEMBANGUNAN DAERAH NIAS (PEMBDA)</div>
                <div class="school-name">{{ $student->school->name ?? 'SMKS / SMAS / SMPS PEMBDA' }}</div>
                <div style="font-size: 8pt; color: #64748b;">
                    {{ $student->school->address ?? 'Jl. K.H. Dewantara No. 1, Kota Gunungsitoli, Sumatera Utara' }}
                </div>
            </td>
        </tr>
    </table>

    <div class="report-title">
        LAPORAN PROFIL DNA AKADEMIK & PEMETAAN POTENSI 360°
    </div>

    {{-- Biodata Siswa --}}
    <table class="info-table">
        <tr>
            <td style="width: 18%; font-weight: bold;">Nama Peserta Didik</td>
            <td style="width: 2%;">:</td>
            <td style="width: 40%; font-weight: bold; text-transform: uppercase;">{{ $student->full_name }}</td>
            <td style="width: 18%; font-weight: bold;">Kelas / Rombel</td>
            <td style="width: 2%;">:</td>
            <td style="width: 20%;">{{ $student->currentClassroom->first()->class_name ?? $student->currentClassroom->first()->name ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">NISN / NIS</td>
            <td>:</td>
            <td>{{ $student->nisn ?: '-' }} / {{ $student->nis ?: '-' }}</td>
            <td style="font-weight: bold;">Tanggal Analisis</td>
            <td>:</td>
            <td>{{ now()->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Nama Sekolah</td>
            <td>:</td>
            <td>{{ $student->school->name ?? '-' }}</td>
            <td style="font-weight: bold;">Tingkat Akurasi</td>
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
                <th style="width: 6%;">No</th>
                <th style="width: 34%;">Sumbu Dimensi Potensi</th>
                <th style="width: 14%;">Skor (0-100)</th>
                <th style="width: 46%;">Indikator & Sumber Data PembdaHUB</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; font-weight: bold;">1</td>
                <td><b>Logika & Analitik</b></td>
                <td style="text-align: center; font-weight: bold; font-size: 10pt; color: #2563eb;">{{ $analysis['scores']['logic'] }}</td>
                <td>Rerata Matematika/IPA & Ketajaman Penalaran Ujian CBT</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">2</td>
                <td><b>Komunikasi & Bahasa</b></td>
                <td style="text-align: center; font-weight: bold; font-size: 10pt; color: #059669;">{{ $analysis['scores']['communication'] }}</td>
                <td>Rerata Bahasa Indonesia, Bahasa Inggris & Partisipasi Diskusi</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">3</td>
                <td><b>Keahlian Vokasi & Terapan</b></td>
                <td style="text-align: center; font-weight: bold; font-size: 10pt; color: #4f46e5;">{{ $analysis['scores']['technical'] }}</td>
                <td>Capaian Mapel Produktif Kejuruan, Praktik Lab & Portofolio</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">4</td>
                <td><b>Sosial & Kepemimpinan</b></td>
                <td style="text-align: center; font-weight: bold; font-size: 10pt; color: #d97706;">{{ $analysis['scores']['social'] }}</td>
                <td>Rerata Mapel Sosial, Kolaborasi Kelompok & Poin Reputasi Positif</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">5</td>
                <td><b>Kreativitas & Daya Inovasi</b></td>
                <td style="text-align: center; font-weight: bold; font-size: 10pt; color: #9333ea;">{{ $analysis['scores']['creative'] }}</td>
                <td>Seni Budaya, Proyek Mandiri SimLab, Inovasi & Prestasi Lomba</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">6</td>
                <td><b>Kedisiplinan & Ketekunan</b></td>
                <td style="text-align: center; font-weight: bold; font-size: 10pt; color: #e11d48;">{{ $analysis['scores']['discipline'] }}</td>
                <td>Presensi RFID ({{ $analysis['metrics']['attendance_rate'] }}%) & Ketepatan Pengumpulan Tugas LMS</td>
            </tr>
        </tbody>
    </table>

    {{-- Rekomendasi Karir / Kuliah --}}
    <div class="recom-box">
        <div style="font-weight: bold; color: #0f172a; margin-bottom: 4px; text-transform: uppercase;">
            Rekomendasi Arah Karier & Masa Depan (AI Prediction):
        </div>
        @if($analysis['school_type'] === 'SMK')
            @if(isset($analysis['recommendations']['career_tracks']))
                @foreach($analysis['recommendations']['career_tracks'] as $t)
                <div style="margin-bottom: 3px;">
                    &bull; <b>{{ $t['title'] }}</b>: {{ $t['description'] }}
                </div>
                @endforeach
            @endif
            @if(isset($analysis['recommendations']['pkl_recommendation']))
            <div style="margin-top: 4px; color: #065f46;">
                <b>Tempat PKL Rekomendasi:</b> {{ $analysis['recommendations']['pkl_recommendation'] }}
            </div>
            @endif
        @else
            @if(isset($analysis['recommendations']['college_majors']))
                <div>Rumpun Program Studi PTN yang Direkomendasikan:</div>
                @foreach($analysis['recommendations']['college_majors'] as $m)
                <div style="margin-left: 10px;">
                    - <b>{{ $m['major'] }}</b> ({{ $m['cluster'] }}) &mdash; Kesiapan: {{ $m['readiness'] }}
                </div>
                @endforeach
            @endif
        @endif
    </div>

    {{-- Tanda Tangan --}}
    <table class="signature-table">
        <tr>
            <td>
                Mengetahui,<br>
                Orang Tua / Wali Murid
                <br><br><br><br>
                <b>( .................................................. )</b>
            </td>
            <td>
                Gunungsitoli, {{ now()->translatedFormat('d F Y') }}<br>
                Guru Bimbingan Konseling / Wali Kelas
                <br><br><br><br>
                <b>{{ $student->currentClassroom->first()->homeroomTeacher->full_name ?? '( .................................................. )' }}</b>
            </td>
            <td>
                Mengetahui,<br>
                Kepala Sekolah
                <br><br><br><br>
                <b>{{ $student->school->principal_name ?? '( .................................................. )' }}</b>
            </td>
        </tr>
    </table>

</body>
</html>
