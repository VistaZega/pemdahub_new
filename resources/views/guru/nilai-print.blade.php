<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Nilai Siswa - {{ $classroom->class_name ?? 'Kelas' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-pembda.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #111;
            margin: 0;
            padding: 15px;
            background-color: #f8fafc;
            line-height: 1.35;
        }

        /* Screen Toolbar */
        .toolbar-container {
            max-width: 1100px;
            margin: 0 auto 20px auto;
            background: #ffffff;
            border: 2px solid #0f172a;
            border-radius: 16px;
            padding: 14px 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
        .toolbar-flex {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .toolbar-left, .toolbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 10px;
            border: 1.5px solid #000;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .btn-primary {
            background-color: #2563eb;
            color: #ffffff !important;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
        .btn-secondary {
            background-color: #f1f5f9;
            color: #0f172a !important;
        }
        .btn-secondary:hover {
            background-color: #e2e8f0;
        }
        .btn-outline {
            background-color: #ffffff;
            color: #334155 !important;
        }
        .btn-outline:hover {
            background-color: #f8fafc;
        }
        .select-filter {
            padding: 7px 12px;
            border: 1.5px solid #000;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
            background-color: #ffffff;
            color: #0f172a;
            outline: none;
            cursor: pointer;
        }

        /* Paper Sheet */
        .sheet {
            max-width: 1100px;
            margin: 0 auto 30px auto;
            background: #ffffff;
            padding: 25px 30px;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            page-break-after: always;
        }
        .sheet:last-child {
            page-break-after: auto;
            margin-bottom: 0;
        }

        /* Kop Surat */
        .kop-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            padding-bottom: 12px;
            border-bottom: 3px double #000000;
            margin-bottom: 16px;
        }
        .kop-logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .kop-text {
            text-align: center;
            flex: 1;
        }
        .kop-text h2 {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .kop-text h1 {
            margin: 2px 0;
            font-size: 17px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .kop-text p {
            margin: 2px 0 0 0;
            font-size: 10.5px;
            color: #222;
        }

        /* Document Header */
        .doc-title-container {
            text-align: center;
            margin-bottom: 16px;
        }
        .doc-title {
            margin: 0;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-decoration: underline;
        }
        .doc-subtitle {
            margin: 3px 0 0 0;
            font-size: 11px;
            font-weight: 600;
            color: #333;
        }

        /* Info Grid */
        .info-table {
            width: 100%;
            border: none;
            margin-bottom: 14px;
            font-size: 11px;
        }
        .info-table td {
            border: none;
            padding: 2.5px 4px;
            vertical-align: top;
        }
        .info-label {
            font-weight: 700;
            color: #334155;
            width: 140px;
        }

        /* Data Table */
        .grade-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
            margin-bottom: 14px;
        }
        .grade-table th, .grade-table td {
            border: 1px solid #1e293b;
            padding: 5px 6px;
        }
        .grade-table th {
            background-color: #f1f5f9;
            font-weight: 800;
            text-align: center;
            vertical-align: middle;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.3px;
        }
        .grade-table td {
            vertical-align: middle;
        }
        .text-center { text-align: center !important; }
        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: 700; }
        .font-semibold { font-weight: 600; }

        /* Badge status */
        .badge-status {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 9.5px;
            text-align: center;
        }
        .badge-tuntas {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }
        .badge-belum {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        /* Stats Box */
        .stats-summary-box {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            background-color: #f8fafc;
            border: 1px solid #94a3b8;
            padding: 8px 14px;
            border-radius: 6px;
            margin-bottom: 25px;
            font-size: 10.5px;
            font-weight: 600;
            gap: 10px;
        }

        /* Signatures */
        .signature-table {
            width: 100%;
            border: none;
            margin-top: 25px;
            page-break-inside: avoid;
            font-size: 11px;
        }
        .signature-table td {
            border: none;
            padding: 0;
            text-align: center;
            vertical-align: top;
            width: 50%;
        }
        .signature-space {
            height: 65px;
        }
        .signature-name {
            font-weight: 800;
            text-decoration: underline;
        }

        /* Blank Sheet Specific */
        .blank-cell {
            height: 26px;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                font-size: 10.5px;
            }
            .no-print {
                display: none !important;
            }
            .sheet {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 0 25px 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            .grade-table th {
                background-color: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .badge-status {
                border: 1px solid #000000 !important;
                background-color: transparent !important;
                color: #000000 !important;
            }
            @page {
                size: landscape;
                margin: 10mm 12mm;
            }
            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    @php
        $school = $classroom?->school ?? $teacher->school;
        $activeYearName = $activeYear->year ?? date('Y');
        $semesterLabel = $selectedSemester->semester_name ?? ('Semester ' . ($selectedSemester->semester_number ?? '1'));
        $showMode = request('mode', ($dataPerSubject->contains('has_grades', true) ? 'rekap' : 'blank'));
    @endphp

    {{-- INTERACTIVE TOP TOOLBAR (SCREEN ONLY) --}}
    <div class="toolbar-container no-print">
        <div class="toolbar-flex">
            <div class="toolbar-left">
                <button type="button" onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Cetak Dokumen / Simpan PDF
                </button>

                <form method="GET" action="{{ route('guru.nilai.print') }}" class="inline-flex items-center gap-2" id="toolbarForm">
                    <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
                    <input type="hidden" name="semester_id" value="{{ $selectedSemesterId }}">

                    {{-- Switcher Mode Cetak --}}
                    <select name="mode" onchange="this.form.submit()" class="select-filter">
                        <option value="rekap" {{ $showMode === 'rekap' ? 'selected' : '' }}>📋 Format Rekap Nilai Siswa</option>
                        <option value="blank" {{ $showMode === 'blank' ? 'selected' : '' }}>📝 Lembar Format Nilai Kosong (KBM)</option>
                    </select>

                    {{-- Filter Mapel jika ada lebih dari 1 --}}
                    @if($subjects->count() > 1)
                        <select name="subject_id" onchange="this.form.submit()" class="select-filter">
                            <option value="">Semua Mapel Diajar ({{ $subjects->count() }})</option>
                            @foreach($subjects as $sb)
                                <option value="{{ $sb->id }}" {{ $selectedSubjectId == $sb->id ? 'selected' : '' }}>
                                    {{ $sb->subject_name ?? $sb->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </form>
            </div>

            <div class="toolbar-right">
                <span style="font-size: 11px; font-weight: 600; color: #475569;">
                    <i class="fas fa-info-circle text-blue-500"></i> Rekomendasi: Kertas A4/F4 (Landscape)
                </span>
                <button type="button" onclick="window.close()" class="btn btn-secondary" title="Tutup Tab">
                    <i class="fas fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>

    @if(!$classroom)
        <div class="sheet" style="text-align: center; padding: 50px 20px;">
            <i class="fas fa-exclamation-triangle" style="font-size: 36px; color: #f59e0b; margin-bottom: 12px;"></i>
            <h2 style="margin: 0 0 8px 0; color: #1e293b;">Data Kelas Tidak Ditemukan</h2>
            <p style="margin: 0; color: #64748b;">Silakan pilih kelas melalui menu Nilai Siswa terlebih dahulu.</p>
        </div>
    @elseif($dataPerSubject->isEmpty())
        <div class="sheet" style="text-align: center; padding: 50px 20px;">
            <i class="fas fa-info-circle" style="font-size: 36px; color: #3b82f6; margin-bottom: 12px;"></i>
            <h2 style="margin: 0 0 8px 0; color: #1e293b;">Tidak Ada Mata Pelajaran Terdaftar</h2>
            <p style="margin: 0; color: #64748b;">Belum ada penugasan mengajar aktif untuk kelas {{ $classroom->class_name }}.</p>
        </div>
    @else
        {{-- RENDER SHEET PER MATA PELAJARAN --}}
        @foreach($dataPerSubject as $subjectIdx => $item)
            @php
                $subj = $item['subject'];
                $students = $item['students'];
                $stats = $item['stats'];
                $subjName = $subj->subject_name ?? $subj->name ?? 'Mata Pelajaran';
                $kkm = $subj->kkm ?? 75;
                $hasRealGrades = $item['has_grades'];
                $isEffectiveBlank = ($showMode === 'blank' || !$hasRealGrades);
            @endphp

            <div class="sheet">
                {{-- KOP SURAT RESMI --}}
                <div class="kop-wrapper">
                    <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo Pembda" class="kop-logo" onerror="this.style.display='none'">
                    <div class="kop-text">
                        <h2>YAYASAN PERGURUAN PEMBDA NIAS</h2>
                        <h1>{{ strtoupper($school->name ?? 'SMK SWASTA PEMBDA NIAS') }}</h1>
                        <p>
                            {{ $school->address ?? 'Jalan K.H. Dewantara No. 1, Gunungsitoli' }} |
                            NPSN: {{ $school->npsn ?? '-' }} |
                            Telp: {{ $school->phone ?? '-' }} |
                            Email: {{ $school->email ?? 'info@perguruanpembda.com' }}
                        </p>
                    </div>
                </div>

                {{-- JUDUL LAPORAN --}}
                <div class="doc-title-container">
                    <h3 class="doc-title">
                        @if($isEffectiveBlank)
                            FORMAT LEMBAR PENILAIAN SISWA (KBM TATAP MUKA)
                        @else
                            DAFTAR REKAPITULASI NILAI CAPAIAN HASIL BELAJAR SISWA
                        @endif
                    </h3>
                    <p class="doc-subtitle">
                        Semester {{ $semesterLabel }} &middot; Tahun Pelajaran {{ $activeYearName }}
                    </p>
                </div>

                {{-- INFORMASI KELAS & MAPEL --}}
                <table class="info-table">
                    <tr>
                        <td class="info-label">Satuan Pendidikan</td>
                        <td width="10">:</td>
                        <td><strong>{{ $school->name ?? 'SMK Swasta Pembda Nias' }}</strong></td>

                        <td class="info-label">Guru Mata Pelajaran</td>
                        <td width="10">:</td>
                        <td><strong>{{ $teacher->full_name ?? $teacher->name ?? '-' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="info-label">Kelas / Rombel</td>
                        <td>:</td>
                        <td><strong>{{ $classroom->class_name }}</strong></td>

                        <td class="info-label">Wali Kelas</td>
                        <td>:</td>
                        <td>{{ $classroom->homeroomTeacher?->full_name ?? $classroom->homeroomTeacher?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Mata Pelajaran</td>
                        <td>:</td>
                        <td><strong>{{ $subjName }}</strong></td>

                        <td class="info-label">Kriteria Ketuntasan (KKM)</td>
                        <td>:</td>
                        <td><span style="font-weight: 800; color: #0284c7;">{{ $kkm }}</span></td>
                    </tr>
                    <tr>
                        <td class="info-label">Jumlah Siswa</td>
                        <td>:</td>
                        <td>{{ $students->count() }} Siswa Terdaftar</td>

                        <td class="info-label">Tanggal Cetak</td>
                        <td>:</td>
                        <td>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td>
                    </tr>
                </table>

                {{-- TABEL DATA NILAI --}}
                @if(!$isEffectiveBlank)
                    {{-- 1. TABEL DENGAN NILAI REAL --}}
                    <table class="grade-table">
                        <thead>
                            <tr>
                                <th rowspan="2" width="4%">No</th>
                                <th rowspan="2" width="12%">NISN</th>
                                <th rowspan="2" width="9%">NIS</th>
                                <th rowspan="2" width="27%" class="text-left" style="padding-left: 8px;">Nama Lengkap Siswa</th>
                                <th rowspan="2" width="5%">L/P</th>
                                <th colspan="4">Komponen Penilaian</th>
                                <th rowspan="2" width="8%">Nilai Akhir</th>
                                <th rowspan="2" width="7%">Predikat</th>
                                <th rowspan="2" width="9%">Keterangan</th>
                            </tr>
                            <tr>
                                <th width="7%">Tugas<br><span style="font-size: 8.5px; font-weight: normal;">({{ number_format($gradeWeight->tugas_weight ?? 20, 0) }}%)</span></th>
                                <th width="7%">PTS<br><span style="font-size: 8.5px; font-weight: normal;">({{ number_format($gradeWeight->pts_weight ?? 30, 0) }}%)</span></th>
                                <th width="7%">PAS<br><span style="font-size: 8.5px; font-weight: normal;">({{ number_format($gradeWeight->pas_weight ?? 40, 0) }}%)</span></th>
                                <th width="7%">Sikap<br><span style="font-size: 8.5px; font-weight: normal;">({{ number_format($gradeWeight->sikap_weight ?? 10, 0) }}%)</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $idx => $st)
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td class="text-center" style="font-family: monospace;">{{ $st['student']->nisn ?? '-' }}</td>
                                    <td class="text-center" style="font-family: monospace;">{{ $st['student']->nis ?? '-' }}</td>
                                    <td class="text-left font-semibold" style="padding-left: 8px;">{{ $st['student']->full_name }}</td>
                                    <td class="text-center">{{ $st['student']->gender ?? '-' }}</td>

                                    {{-- Tugas --}}
                                    <td class="text-center">
                                        {{ $st['tugas_avg'] !== null ? number_format($st['tugas_avg'], 1) : '-' }}
                                    </td>

                                    {{-- PTS --}}
                                    <td class="text-center">
                                        {{ $st['pts_score'] !== null ? number_format($st['pts_score'], 1) : '-' }}
                                    </td>

                                    {{-- PAS --}}
                                    <td class="text-center">
                                        {{ $st['pas_score'] !== null ? number_format($st['pas_score'], 1) : '-' }}
                                    </td>

                                    {{-- Sikap --}}
                                    <td class="text-center">
                                        {{ $st['sikap_score'] !== null ? number_format($st['sikap_score'], 1) : '-' }}
                                    </td>

                                    {{-- Nilai Akhir --}}
                                    <td class="text-center font-bold" style="background-color: #f8fafc; font-size: 11px;">
                                        {{ $st['final_score'] !== null ? number_format($st['final_score'], 1) : '-' }}
                                    </td>

                                    {{-- Predikat --}}
                                    <td class="text-center font-bold">
                                        {{ $st['predicate'] ?? '-' }}
                                    </td>

                                    {{-- Status --}}
                                    <td class="text-center">
                                        @if($st['is_passed'] === true)
                                            <span class="badge-status badge-tuntas">Tuntas</span>
                                        @elseif($st['is_passed'] === false)
                                            <span class="badge-status badge-belum">Belum Tuntas</span>
                                        @else
                                            <span style="color: #94a3b8;">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center" style="padding: 20px; color: #64748b;">
                                        Tidak ada data siswa pada rombel ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- STATS SUMMARY FOOTER --}}
                    <div class="stats-summary-box">
                        <span>Rata-rata Kelas: <strong style="color: #0284c7;">{{ $stats['average'] }}</strong></span>
                        <span>Nilai Tertinggi: <strong style="color: #16a34a;">{{ $stats['max'] }}</strong></span>
                        <span>Nilai Terendah: <strong style="color: #dc2626;">{{ $stats['min'] }}</strong></span>
                        <span>Siswa Tuntas (>= {{ $kkm }}): <strong style="color: #16a34a;">{{ $stats['passed_count'] }} Orang</strong> ({{ $stats['total_students'] > 0 ? round(($stats['passed_count'] / $stats['total_students']) * 100, 1) : 0 }}%)</span>
                        <span>Belum Tuntas: <strong style="color: #dc2626;">{{ $stats['not_passed_count'] }} Orang</strong></span>
                    </div>

                @else
                    {{-- 2. TABEL LEMBAR PENILAIAN FORMAT KBM (BLANK / KOSONG UNTUK ISIAN FISIK GURU) --}}
                    <table class="grade-table">
                        <thead>
                            <tr>
                                <th rowspan="2" width="4%">No</th>
                                <th rowspan="2" width="12%">NISN</th>
                                <th rowspan="2" width="9%">NIS</th>
                                <th rowspan="2" width="24%" class="text-left" style="padding-left: 8px;">Nama Lengkap Siswa</th>
                                <th rowspan="2" width="4%">L/P</th>
                                <th colspan="4">Tugas / Harian</th>
                                <th rowspan="2" width="7%">Rerata Tugas</th>
                                <th rowspan="2" width="7%">PTS / UTS</th>
                                <th rowspan="2" width="7%">PAS / UAS</th>
                                <th rowspan="2" width="6%">Sikap</th>
                                <th rowspan="2" width="7%">Nilai Akhir</th>
                                <th rowspan="2" width="9%">Keterangan</th>
                            </tr>
                            <tr>
                                <th width="4%">T1</th>
                                <th width="4%">T2</th>
                                <th width="4%">T3</th>
                                <th width="4%">T4</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $idx => $st)
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td class="text-center" style="font-family: monospace;">{{ $st['student']->nisn ?? '-' }}</td>
                                    <td class="text-center" style="font-family: monospace;">{{ $st['student']->nis ?? '-' }}</td>
                                    <td class="text-left font-semibold" style="padding-left: 8px;">{{ $st['student']->full_name }}</td>
                                    <td class="text-center">{{ $st['student']->gender ?? '-' }}</td>
                                    <td class="blank-cell"></td>
                                    <td class="blank-cell"></td>
                                    <td class="blank-cell"></td>
                                    <td class="blank-cell"></td>
                                    <td class="blank-cell"></td>
                                    <td class="blank-cell"></td>
                                    <td class="blank-cell"></td>
                                    <td class="blank-cell"></td>
                                    <td class="blank-cell"></td>
                                    <td class="blank-cell"></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="15" class="text-center" style="padding: 20px; color: #64748b;">
                                        Tidak ada data siswa pada rombel ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div style="font-size: 10px; color: #64748b; margin-top: 4px; font-style: italic;">
                        * Catatan: Lembar format penilaian ini dicetak untuk pencatatan nilai manual/fisik guru pada KBM tatap muka.
                    </div>
                @endif

                {{-- TANDA TANGAN RESMI --}}
                <table class="signature-table">
                    <tr>
                        <td>
                            Mengetahui,<br>
                            Kepala Sekolah {{ $school->name ?? '' }}
                            <div class="signature-space"></div>
                            <span class="signature-name">
                                {{ $school->principal_name ?? $school->principal?->full_name ?? $school->principal?->name ?? '..................................................' }}
                            </span><br>
                            NIP / NUPTK: {{ $school->principal?->nip ?? '-' }}
                        </td>
                        <td>
                            Gunungsitoli, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                            Guru Mata Pelajaran,
                            <div class="signature-space"></div>
                            <span class="signature-name">
                                {{ $teacher->full_name ?? $teacher->name ?? $teacher->user->name ?? '..................................................' }}
                            </span><br>
                            NIP / NUPTK: {{ $teacher->nip ?? '-' }}
                        </td>
                    </tr>
                </table>
            </div>
        @endforeach
    @endif

</body>
</html>
