<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara KBM Virtual PembdaTEMU - Sesi #{{ $session->id }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            color: #121316;
            background-color: #f1f5f9;
            line-height: 1.4;
            font-size: 11pt;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .screen-toolbar {
            position: sticky;
            top: 0;
            z-index: 50;
            background: #121316;
            color: #ffffff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .screen-toolbar .title {
            font-size: 13px;
            font-weight: 700;
            color: #fde047;
            font-family: 'JetBrains Mono', monospace;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .screen-toolbar .btn-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
            border: 2px solid transparent;
        }
        .btn-print {
            background-color: #fde047;
            color: #121316;
            border-color: #fde047;
        }
        .btn-print:hover {
            background-color: #fef08a;
        }
        .btn-back {
            background-color: #334155;
            color: #f8fafc;
        }
        .btn-back:hover {
            background-color: #475569;
        }
        .sheet-container {
            max-width: 820px;
            margin: 24px auto 40px auto;
            background: #ffffff;
            padding: 36px 44px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        /* KOP SURAT */
        .kop-wrapper {
            display: flex;
            align-items: center;
            gap: 16px;
            border-bottom: 3px double #121316;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }
        .kop-logo {
            width: 76px;
            height: 76px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .kop-text {
            flex: 1;
            text-align: center;
        }
        .kop-text .foundation {
            font-size: 13pt;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #0f172a;
        }
        .kop-text .school-name {
            font-size: 15pt;
            font-weight: 900;
            color: #1e3a8a;
            letter-spacing: 1px;
            margin: 2px 0;
        }
        .kop-text .address {
            font-size: 8.5pt;
            color: #475569;
            line-height: 1.35;
        }
        /* DOKUMEN TITLE */
        .doc-title-box {
            text-align: center;
            margin-bottom: 20px;
        }
        .doc-title {
            font-size: 13pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #121316;
            text-decoration: underline;
        }
        .doc-number {
            font-family: 'JetBrains Mono', monospace;
            font-size: 9pt;
            font-weight: 600;
            color: #475569;
            margin-top: 4px;
        }
        /* META INFO TABLE */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            font-size: 9.5pt;
        }
        .meta-table td {
            padding: 3px 6px;
            vertical-align: top;
        }
        .meta-table td.label {
            width: 150px;
            font-weight: 700;
            color: #334155;
        }
        .meta-table td.colon {
            width: 10px;
            text-align: center;
            font-weight: 700;
        }
        .meta-table td.val {
            font-weight: 600;
            color: #0f172a;
        }
        /* STATS CARD IN PRINT */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        .stat-card {
            border: 1.5px solid #121316;
            border-radius: 8px;
            padding: 8px 12px;
            background: #fafaf9;
            text-align: center;
        }
        .stat-card .num {
            font-size: 16pt;
            font-weight: 900;
            font-family: 'JetBrains Mono', monospace;
            color: #121316;
        }
        .stat-card .lbl {
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-top: 2px;
        }
        /* SECTION HEADING */
        .section-header {
            font-size: 10.5pt;
            font-weight: 800;
            color: #121316;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        /* ATTENDANCE TABLE */
        .table-data {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 24px;
        }
        .table-data th, .table-data td {
            border: 1px solid #334155;
            padding: 6px 8px;
        }
        .table-data th {
            background-color: #f1f5f9;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 0.3px;
        }
        .table-data td.center {
            text-align: center;
        }
        .table-data td.right {
            text-align: right;
        }
        .table-data tr:nth-child(even) {
            background-color: #fafaf9;
        }
        .badge-status {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7.5pt;
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
        }
        .badge-hadir {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }
        .badge-live {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        /* SIGNATURES */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            page-break-inside: avoid;
            font-size: 9.5pt;
        }
        .signature-table td {
            border: none;
            padding: 0;
            vertical-align: top;
            text-align: center;
            width: 50%;
        }
        .signature-space {
            height: 70px;
        }
        .signature-name {
            font-weight: 800;
            text-decoration: underline;
            color: #0f172a;
        }
        .signature-nip {
            font-size: 8.5pt;
            color: #475569;
        }
        /* PRINT SPECIFIC */
        @media print {
            body {
                background: #ffffff;
                font-size: 10pt;
            }
            .screen-toolbar {
                display: none !important;
            }
            .sheet-container {
                max-width: 100%;
                margin: 0;
                padding: 0;
                border: none;
                box-shadow: none;
            }
            @page {
                size: A4 portrait;
                margin: 12mm 15mm;
            }
        }
    </style>
</head>
<body>

    {{-- Screen Toolbar (Hidden on print) --}}
    <div class="screen-toolbar">
        <div class="title">
            <i class="fa-solid fa-file-signature text-amber-300"></i>
            <span>PEMBDATEMU LIVE // BERITA ACARA RESMI</span>
        </div>
        <div class="btn-group">
            <button onclick="window.print()" class="btn-action btn-print">
                <i class="fa-solid fa-print"></i> Cetak / Simpan PDF
            </button>
            <a href="{{ route('guru.lms.meeting.attendance', $course->id) }}" class="btn-action btn-back">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Rekap
            </a>
        </div>
    </div>

    @php
        $schoolName = $school->name ?? 'SMK SWASTA PEMBDA NIAS';
        $schoolAddress = $school->address ?? 'Jl. Pelita No. 1, Ilir, Kec. Gunungsitoli, Kota Gunungsitoli, Sumatera Utara';
        $className = $course->classroom->class_name ?? ($course->lmsClasses->pluck('classroom.class_name')->filter()->join(', ') ?: 'Semua Rombel Terdaftar');
        $subjectName = $course->subject->name ?? 'Mata Pelajaran';
        $teacherName = $teacher->full_name ?? ($teacher->name ?? ($teacher->user->name ?? 'Guru Pengampu'));
        $teacherNip  = $teacher->nip ?? ($teacher->nuptk ?? '-');
        $principalName = $school->principal_name ?? ($school->principal?->full_name ?? ($school->principal?->name ?? '................................................'));
        $principalNip  = $school->principal?->nip ?? ($school->principal?->nuptk ?? '-');
        $sessionDate = \Carbon\Carbon::parse($session->started_at)->translatedFormat('l, d F Y');
        $sessionStart = $session->started_at->format('H:i');
        $sessionEnd   = $session->ended_at ? $session->ended_at->format('H:i') . ' WIB' : 'Masih Berlangsung';
        $docNumber    = 'BA-TEMU/' . $session->started_at->format('Y') . '/' . $session->started_at->format('m') . '/' . str_pad($session->id, 4, '0', STR_PAD_LEFT);
    @endphp

    <div class="sheet-container">
        
        {{-- KOP SURAT RESMI --}}
        <div class="kop-wrapper">
            <img src="{{ asset('assets/images/logo-pembda.png') }}" onerror="this.src='{{ asset('images/logo.png') }}'; this.onerror=null;" alt="Logo Pembda" class="kop-logo">
            <div class="kop-text">
                <div class="foundation">YAYASAN PERGURUAN PEMBDA NIAS</div>
                <div class="school-name">{{ strtoupper($schoolName) }}</div>
                <div class="address">{{ $schoolAddress }}<br>Email: info@perguruanpembda.com | Website: perguruanpembda.com</div>
            </div>
        </div>

        {{-- JUDUL DOKUMEN --}}
        <div class="doc-title-box">
            <div class="doc-title">BERITA ACARA &amp; REKAPITULASI KBM VIRTUAL</div>
            <div class="doc-number">Nomor: {{ $docNumber }}</div>
        </div>

        {{-- META TABLE --}}
        <table class="meta-table">
            <tr>
                <td class="label">Mata Pelajaran</td>
                <td class="colon">:</td>
                <td class="val">{{ $subjectName }}</td>
                <td class="label">Hari / Tanggal</td>
                <td class="colon">:</td>
                <td class="val">{{ $sessionDate }}</td>
            </tr>
            <tr>
                <td class="label">Guru Pengampu</td>
                <td class="colon">:</td>
                <td class="val">{{ $teacherName }}</td>
                <td class="label">Waktu Pertemuan</td>
                <td class="colon">:</td>
                <td class="val">{{ $sessionStart }} - {{ $sessionEnd }} ({{ $session->duration_minutes }} Menit)</td>
            </tr>
            <tr>
                <td class="label">Kelas / Rombel</td>
                <td class="colon">:</td>
                <td class="val">{{ $className }}</td>
                <td class="label">Platform Pembelajaran</td>
                <td class="colon">:</td>
                <td class="val">PembdaTEMU Live (WebRTC HD + Smart Interactive LMS)</td>
            </tr>
        </table>

        {{-- SUMMARY STATS --}}
        <div class="stats-grid">
            <div class="stat-card">
                <div class="num">{{ $enrolledStudentsCount }}</div>
                <div class="lbl">Total Siswa Terdaftar</div>
            </div>
            <div class="stat-card">
                <div class="num">{{ $attendedCount }}</div>
                <div class="lbl">Siswa Hadir di Sesi</div>
            </div>
            <div class="stat-card">
                <div class="num">{{ $attendanceRate }}%</div>
                <div class="lbl">Tingkat Kehadiran Kelas</div>
            </div>
        </div>

        {{-- DAFTAR KEHADIRAN SISWA --}}
        <div class="section-header">
            <span>DAFTAR PRESENSI SISWA TERVERIFIKASI DIGITAL</span>
        </div>

        <table class="table-data">
            <thead>
                <tr>
                    <th width="35">No</th>
                    <th width="100">NISN / NIS</th>
                    <th>Nama Lengkap Siswa</th>
                    <th width="85">Masuk</th>
                    <th width="85">Keluar</th>
                    <th width="80">Durasi</th>
                    <th width="90">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($session->attendances as $idx => $att)
                    @php
                        $st = $att->student;
                        $stName = $st->full_name ?? ($st->user->name ?? 'Siswa');
                        $stNis  = $st->nisn ?: ($st->nis ?: '-');
                        $joinTime = $att->joined_at ? $att->joined_at->format('H:i:s') : '-';
                        $leaveTime = $att->left_at ? $att->left_at->format('H:i:s') : 'Di Kelas';
                        $isLive = is_null($att->left_at);
                    @endphp
                    <tr>
                        <td class="center">{{ $idx + 1 }}</td>
                        <td class="center font-mono">{{ $stNis }}</td>
                        <td><strong>{{ $stName }}</strong></td>
                        <td class="center">{{ $joinTime }}</td>
                        <td class="center">{{ $leaveTime }}</td>
                        <td class="center font-bold">{{ $att->duration_label }}</td>
                        <td class="center">
                            @if($isLive)
                                <span class="badge-status badge-live">LIVE</span>
                            @else
                                <span class="badge-status badge-hadir">HADIR</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="center" style="padding: 16px; color: #64748b;">
                            Tidak ada data presensi yang tercatat pada sesi pembelajaran ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- NOTULA RINGKASAN KBM --}}
        <div style="margin-bottom: 24px; padding: 10px 14px; background: #fafaf9; border: 1px dashed #cbd5e1; border-radius: 6px; font-size: 8.5pt; color: #475569;">
            <p><strong>Catatan Pelaksanaan KBM:</strong> Sesi pembelajaran tatap muka virtual ini dilaksanakan secara interaktif menggunakan modul materi digital, whiteboard digital, polling pemahaman kilat, dan kuis interaktif PembdaTEMU. Presensi dicatat secara otomatis berbasis durasi aktif siswa di ruang kelas digital.</p>
        </div>

        {{-- TANDA TANGAN RESMI --}}
        <table class="signature-table">
            <tr>
                <td>
                    Mengetahui,<br>
                    Kepala Sekolah {{ $schoolName }}
                    <div class="signature-space"></div>
                    <span class="signature-name">{{ $principalName }}</span><br>
                    <span class="signature-nip">NIP / NUPTK: {{ $principalNip }}</span>
                </td>
                <td>
                    Gunungsitoli, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    Guru Mata Pelajaran,
                    <div class="signature-space"></div>
                    <span class="signature-name">{{ $teacherName }}</span><br>
                    <span class="signature-nip">NIP / NUPTK: {{ $teacherNip }}</span>
                </td>
            </tr>
        </table>

    </div>

</body>
</html>
