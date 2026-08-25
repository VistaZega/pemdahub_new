<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>RAPOR PROJEK PENGUATAN PROFIL PELAJAR PANCASILA (P5)</title>
    <style>
        @page {
            margin: 20mm 15mm 20mm 15mm;
            size: a4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            color: #1e293b;
            line-height: 1.4;
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
            font-size: 9pt;
            color: #475569;
            margin: 0;
        }
        .report-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 12px 0 16px 0;
            color: #0f172a;
        }
        .info-table {
            width: 100%;
            margin-bottom: 14px;
            font-size: 9pt;
        }
        .info-table td {
            padding: 2px 4px;
        }
        .project-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            margin-bottom: 14px;
            border-radius: 4px;
            font-size: 9pt;
        }
        .assessment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 8.5pt;
        }
        .assessment-table th, .assessment-table td {
            border: 1px solid #475569;
            padding: 5px 6px;
        }
        .assessment-table th {
            background-color: #e2e8f0;
            font-weight: bold;
            text-align: center;
        }
        .check-mark {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            color: #0f172a;
        }
        .notes-box {
            border: 1px solid #94a3b8;
            padding: 8px 10px;
            min-height: 50px;
            margin-bottom: 16px;
            font-size: 8.5pt;
            background-color: #ffffff;
        }
        .legend-table {
            width: 100%;
            font-size: 7.5pt;
            color: #475569;
            margin-bottom: 16px;
            border: 1px dashed #cbd5e1;
            padding: 4px;
        }
        .signature-table {
            width: 100%;
            margin-top: 20px;
            font-size: 9pt;
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
        RAPOR PROJEK PENGUATAN PROFIL PELAJAR PANCASILA (P5)
    </div>

    {{-- Biodata Siswa --}}
    <table class="info-table">
        <tr>
            <td style="width: 18%; font-weight: bold;">Nama Peserta Didik</td>
            <td style="width: 2%;">:</td>
            <td style="width: 40%; font-weight: bold; text-transform: uppercase;">{{ $student->full_name }}</td>
            <td style="width: 18%; font-weight: bold;">Kelas / Rombel</td>
            <td style="width: 2%;">:</td>
            <td style="width: 20%;">{{ $project->classroom->name ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">NISN / NIS</td>
            <td>:</td>
            <td>{{ $student->nisn ?? '-' }} / {{ $student->nis ?? '-' }}</td>
            <td style="font-weight: bold;">Tahun Pelajaran</td>
            <td>:</td>
            <td>{{ $project->academicYear->name ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Nama Sekolah</td>
            <td>:</td>
            <td>{{ $student->school->name ?? '-' }}</td>
            <td style="font-weight: bold;">Fase Kurikulum</td>
            <td>:</td>
            <td>Fase {{ in_array(substr($project->classroom->name ?? '', 0, 2), ['10', 'X']) ? 'E' : 'F' }}</td>
        </tr>
    </table>

    {{-- Ringkasan Projek --}}
    <div class="project-box">
        <table style="width: 100%;">
            <tr>
                <td style="width: 15%; font-weight: bold;">Judul Projek</td>
                <td style="width: 2%;">:</td>
                <td style="width: 83%; font-weight: bold;">{{ $project->title }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Tema P5</td>
                <td>:</td>
                <td>{{ $project->theme }}</td>
            </tr>
            @if($project->description)
            <tr>
                <td style="font-weight: bold; vertical-align: top;">Deskripsi</td>
                <td style="vertical-align: top;">:</td>
                <td>{{ $project->description }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- Tabel Capaian Dimensi & Sub-Elemen --}}
    <table class="assessment-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 55%;">Dimensi dan Rumusan Sub-Elemen Capaian</th>
                <th style="width: 10%;">MB</th>
                <th style="width: 10%;">SB</th>
                <th style="width: 10%;">BSH</th>
                <th style="width: 10%;">SAB</th>
            </tr>
        </thead>
        <tbody>
            @foreach($project->targets as $idx => $target)
            @php
                $score = $assessments->get($target->id)?->score;
            @endphp
            <tr>
                <td style="text-align: center; font-weight: bold;">{{ $idx + 1 }}</td>
                <td>
                    <div style="font-weight: bold; color: #0f172a; margin-bottom: 2px;">
                        {{ $target->dimension }}
                    </div>
                    <div style="color: #334155; font-size: 8pt;">
                        {{ $target->sub_element }}
                    </div>
                </td>
                <td class="check-mark">{{ $score === 'MB' ? '✓' : '' }}</td>
                <td class="check-mark">{{ $score === 'SB' ? '✓' : '' }}</td>
                <td class="check-mark">{{ $score === 'BSH' ? '✓' : '' }}</td>
                <td class="check-mark">{{ $score === 'SAB' ? '✓' : '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Catatan Proses Guru --}}
    <div style="font-weight: bold; font-size: 8.5pt; margin-bottom: 4px;">Catatan Proses & Capaian Projek:</div>
    <div class="notes-box">
        {{ $note->notes ?? 'Ananda menunjukkan keaktifan dan partisipasi yang baik selama pelaksanaan projek penguatan profil pelajar pancasila.' }}
    </div>

    {{-- Keterangan Skala --}}
    <table class="legend-table">
        <tr>
            <td><b>MB:</b> Mulai Berkembang</td>
            <td><b>SB:</b> Sedang Berkembang</td>
            <td><b>BSH:</b> Berkembang Sesuai Harapan</td>
            <td><b>SAB:</b> Sangat Berkembang</td>
        </tr>
    </table>

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
                Fasilitator / Wali Kelas
                <br><br><br><br>
                <b>{{ $project->classroom->homeroomTeacher->full_name ?? '( .................................................. )' }}</b>
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
