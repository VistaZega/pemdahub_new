<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Progres LMS - Kelas {{ $classroom->class_name }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #111;
            margin: 20px;
            line-height: 1.4;
        }
        .kop {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .kop h2 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
        }
        .kop h3 {
            margin: 2px 0;
            font-size: 14px;
        }
        .kop p {
            margin: 2px 0;
            font-size: 11px;
            color: #444;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 12px;
        }
        .info-table td {
            padding: 3px 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .data-table th, .data-table td {
            border: 1px solid #333;
            padding: 6px 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold; }
        .ttd-table {
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .ttd-table td {
            text-align: center;
            width: 50%;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px; padding: 10px; background: #e0f2fe; border: 1px solid #0284c7; border-radius: 8px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0284c7; color: #fff; font-weight: bold; border: none; border-radius: 4px; cursor: pointer;">
            🖨️ Cetak Dokumen Ini
        </button>
        <span style="margin-left: 10px; font-size: 12px; color: #0369a1;">Gunakan orientasi Portrait / Landscape sesuai kebutuhan kertas.</span>
    </div>

    {{-- KOP SURAT --}}
    <div class="kop">
        <h2>YAYASAN PERGURUAN PEMBDA NIAS</h2>
        <h3>{{ $classroom->school->name ?? 'SMP / SMA / SMK PEMBDA NIAS' }}</h3>
        <p>{{ $classroom->school->address ?? 'Kota Gunungsitoli, Sumatera Utara' }} | NPSN: {{ $classroom->school->npsn ?? '-' }}</p>
    </div>

    {{-- JUDUL LAPORAN --}}
    <div style="text-align: center; margin-bottom: 15px;">
        <h3 style="margin: 0; text-decoration: underline; text-transform: uppercase;">
            REKAPITULASI PROGRES PEMBELAJARAN LMS SISWA
        </h3>
        <p style="margin: 2px 0; font-size: 11px; color: #555;">Tahun Pelajaran {{ $activeYear->year_name ?? date('Y') }}</p>
    </div>

    {{-- INFO KELAS --}}
    <table class="info-table">
        <tr>
            <td width="15%"><strong>Kelas / Rombel</strong></td>
            <td width="35%">: {{ $classroom->class_name }}</td>
            <td width="15%"><strong>Total Mata Pelajaran</strong></td>
            <td width="35%">: {{ $courses->count() }} Mapel Aktif</td>
        </tr>
        <tr>
            <td><strong>Wali Kelas</strong></td>
            <td>: {{ $teacher->user->name ?? $teacher->full_name ?? 'Wali Kelas' }}</td>
            <td><strong>Tanggal Cetak</strong></td>
            <td>: {{ date('d F Y') }}</td>
        </tr>
    </table>

    {{-- TABEL DATA --}}
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">NISN</th>
                <th width="30%">Nama Siswa</th>
                <th width="12%">Materi Selesai</th>
                <th width="12%">Tugas Terkumpul</th>
                <th width="12%">Kuis Dikerjakan</th>
                <th width="14%">Total Capaian (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapData as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $row['student']->nisn ?? '-' }}</td>
                <td class="font-bold">{{ $row['student']->full_name }}</td>
                <td class="text-center">{{ $row['completed_materials'] }} / {{ $row['total_materials'] }}</td>
                <td class="text-center">{{ $row['submitted_tasks'] }} / {{ $row['total_tasks'] }}</td>
                <td class="text-center">{{ $row['completed_quizzes'] }} / {{ $row['total_quizzes'] }}</td>
                <td class="text-center font-bold" style="background-color: {{ $row['overall_pct'] >= 80 ? '#dcfce7' : ($row['overall_pct'] < 40 ? '#ffe4e6' : '#fef3c7') }};">
                    {{ $row['overall_pct'] }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TANDA TANGAN --}}
    <table class="ttd-table">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala Sekolah
                <br><br><br><br><br>
                <strong><u>{{ $classroom->school->principal_name ?? '.........................................' }}</u></strong><br>
                NIP/NUPTK: -
            </td>
            <td>
                Gunungsitoli, {{ date('d F Y') }}<br>
                Wali Kelas {{ $classroom->class_name }}
                <br><br><br><br><br>
                <strong><u>{{ $teacher->user->name ?? $teacher->full_name }}</u></strong><br>
                NIP/NUPTK: {{ $teacher->nip ?? '-' }}
            </td>
        </tr>
    </table>

</body>
</html>
