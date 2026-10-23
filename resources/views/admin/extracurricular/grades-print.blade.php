<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Nilai Extra — {{ $extracurricular->name }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #0f172a;
            margin: 24px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #1e293b;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .header h2 { margin: 0; font-size: 16px; text-transform: uppercase; color: #312e81; }
        .header h1 { margin: 4px 0; font-size: 20px; text-transform: uppercase; }
        .header p { margin: 2px 0; color: #475569; font-size: 11px; }
        .meta-table {
            width: 100%;
            margin-bottom: 14px;
            font-size: 12px;
        }
        .meta-table td { padding: 3px 6px; }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 7px 8px;
            font-size: 11px;
        }
        table.data-table th {
            background-color: #1e1b4b;
            color: #ffffff;
            text-transform: uppercase;
            font-size: 10px;
        }
        table.data-table tr:nth-child(even) { background-color: #f8fafc; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 10px;
        }
        .no-print {
            margin-bottom: 16px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }
        .btn-print {
            background: #4f46e5;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
        }
        @media print {
            .no-print { display: none; }
            body { margin: 10mm; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
    </div>

    <div class="header">
        <h2>YAYASAN PERGURUAN PEMBDA NIAS</h2>
        <h1>{{ $extracurricular->school->name ?? 'UNIT LINTAS YAYASAN PERGURUAN PEMBDA' }}</h1>
        <p>REKAPITULASI NILAI EXTRA (LMS EKSTRAKURIKULER) & KELULUSAN SERTIFIKAT</p>
    </div>

    <table class="meta-table">
        <tr>
            <td width="18%"><b>Nama Ekstrakurikuler</b></td>
            <td width="32%">: {{ $extracurricular->name }}</td>
            <td width="18%"><b>Guru Pembina</b></td>
            <td width="32%">: {{ $extracurricular->manager_name ?: ($extracurricular->advisor_name ?: ($extracurricular->advisor->full_name ?? '-')) }}</td>
        </tr>
        <tr>
            <td><b>Kode Kelas LMS</b></td>
            <td>: {{ $extracurricular->lmsCourse->code ?? 'Belum Diaktifkan' }}</td>
            <td><b>Jadwal & Lokasi</b></td>
            <td>: {{ $extracurricular->schedule_day_time ?: '-' }} ({{ $extracurricular->location ?: 'Kampus Pembda' }})</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="20%">Nama Siswa</th>
                <th width="10%">Kelas</th>
                <th width="9%">Jabatan</th>
                <th width="7%">Rata Tugas</th>
                <th width="7%">Rata Quiz</th>
                <th width="7%">Kehadiran</th>
                <th width="8%">Nilai Akhir (0-100)</th>
                <th width="10%">Predikat</th>
                <th>Deskripsi Capaian Kompetensi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($extracurricular->members as $member)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="font-bold">{{ $member->student->full_name }}<br><span style="font-weight:normal;font-size:10px;color:#64748b;">NISN: {{ $member->student->nisn ?: '-' }}</span></td>
                <td class="text-center">{{ $member->student->currentClassroom->first()?->class_name ?? ($member->student->classroom?->class_name ?? '-') }}</td>
                <td class="text-center">{{ $member->role_label }}</td>
                <td class="text-center">{{ $member->avg_assignment_score !== null ? number_format($member->avg_assignment_score, 1) : '-' }}</td>
                <td class="text-center">{{ $member->avg_quiz_score !== null ? number_format($member->avg_quiz_score, 1) : '-' }}</td>
                <td class="text-center">{{ $member->attendance_score !== null ? number_format($member->attendance_score, 0) : '-' }}</td>
                <td class="text-center font-bold">{{ $member->final_score !== null ? number_format($member->final_score, 1) : '-' }}</td>
                <td class="text-center font-bold">{{ $member->predicate ? $member->predicate . ' (' . $member->predicate_letter . ')' : '-' }}</td>
                <td>{{ $member->grade_description ?: '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">Belum ada anggota aktif yang terdaftar.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table style="width: 100%; margin-top: 32px; font-size: 12px;">
        <tr>
            <td width="65%"></td>
            <td width="35%" class="text-center">
                <div>Gunungsitoli, {{ now()->translatedFormat('d F Y') }}</div>
                <div><b>Guru Pembina {{ $extracurricular->name }}</b></div>
                <div style="margin-top: 56px; font-weight: bold; text-decoration: underline;">
                    {{ $extracurricular->manager_name ?: ($extracurricular->advisor_name ?: ($extracurricular->advisor->full_name ?? '........................................')) }}
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
