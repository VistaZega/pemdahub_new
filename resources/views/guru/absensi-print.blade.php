<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rekap Absensi - {{ $selectedClassroom->class_name }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            color: black;
            background: white;
            margin: 0;
            padding: 20px;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mt-4 { margin-top: 1rem; }
        .mt-8 { margin-top: 2rem; }
        .w-full { width: 100%; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid black;
            padding: 4px;
        }
        th {
            background-color: #f3f3f3;
            text-align: center;
        }
        
        .header-kop {
            border-bottom: 3px solid black;
            padding-bottom: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .header-text {
            flex: 1;
            text-align: center;
            line-height: 1.2;
        }
        .header-text h1, .header-text h2, .header-text h3 {
            margin: 0;
            padding: 0;
        }
        .header-text h1 { font-size: 16px; }
        .header-text h2 { font-size: 18px; }
        .header-text p { font-size: 11px; margin: 2px 0 0 0; }
        
        .info-table {
            width: 50%;
            border: none;
            margin-bottom: 15px;
        }
        .info-table th, .info-table td {
            border: none;
            padding: 2px 5px 2px 0;
            text-align: left;
            background: none;
        }
        
        .ttd-container {
            width: 100%;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .ttd-table {
            width: 100%;
            border: none;
        }
        .ttd-table th, .ttd-table td {
            border: none;
            background: none;
            text-align: center;
            padding: 0;
        }
        .ttd-space { height: 80px; }

        .page-break { page-break-before: always; }
        
        @media print {
            body { padding: 0; }
            @page { margin: 1cm; size: landscape; }
            button { display: none !important; }
        }
        
        .btn-print {
            display: inline-block;
            padding: 10px 20px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-family: sans-serif;
            margin-bottom: 20px;
            cursor: pointer;
            border: none;
        }
        
        .badge {
            font-size: 9px;
            font-family: sans-serif;
            color: #666;
            margin-left: 2px;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="btn-print">🖨️ Cetak Laporan</button>

    @php
        $sekolah = $teacher->school;
    @endphp

    <!-- HALAMAN 1: KEHADIRAN HARIAN SEKOLAH -->
    <div class="header-kop">
        <div class="header-text">
            <h1>YAYASAN PERGURUAN PEMBDA NIAS</h1>
            <h2>{{ strtoupper($sekolah->name ?? 'SEKOLAH') }}</h2>
            <p>{{ $sekolah->address ?? 'Alamat Sekolah' }}</p>
        </div>
    </div>

    <h3 class="text-center mb-4">REKAPITULASI KEHADIRAN HARIAN SISWA (SEKOLAH)</h3>

    <table class="info-table">
        <tr>
            <td width="100"><strong>Kelas</strong></td>
            <td width="10">:</td>
            <td>{{ $selectedClassroom->class_name }}</td>
        </tr>
        <tr>
            <td><strong>Wali Kelas</strong></td>
            <td>:</td>
            <td>{{ $selectedClassroom->homeroomTeacher->name ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Bulan</strong></td>
            <td>:</td>
            <td>{{ $monthsList[$selectedMonth] ?? '' }} {{ $selectedYear }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th rowspan="2" width="30">No</th>
                <th rowspan="2" width="200">Nama Siswa</th>
                <th rowspan="2" width="80">NISN</th>
                <th colspan="{{ $daysInMonth }}">Tanggal</th>
                <th colspan="4">Total</th>
            </tr>
            <tr>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    <th width="15" style="font-size:10px">{{ $d }}</th>
                @endfor
                <th width="20">H</th>
                <th width="20">S</th>
                <th width="20">I</th>
                <th width="20">A</th>
            </tr>
        </thead>
        <tbody>
            @forelse($classroomStudents as $idx => $st)
                @php
                    $stStat = $studentStats[$st->id] ?? ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0];
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $st->full_name }}</td>
                    <td class="text-center">{{ $st->nisn ?? '-' }}</td>
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $stStatus = $matrixMap[$st->id][$d] ?? null;
                            $stChar = match($stStatus) {
                                'hadir' => 'H',
                                'sakit' => 'S',
                                'izin' => 'I',
                                'alpha' => 'A',
                                default => ''
                            };
                        @endphp
                        <td class="text-center" style="font-size:10px">{{ $stChar }}</td>
                    @endfor
                    <td class="text-center font-bold">{{ $stStat['hadir'] ?: '-' }}</td>
                    <td class="text-center font-bold">{{ $stStat['sakit'] ?: '-' }}</td>
                    <td class="text-center font-bold">{{ $stStat['izin'] ?: '-' }}</td>
                    <td class="text-center font-bold">{{ $stStat['alpha'] ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $daysInMonth + 7 }}" class="text-center" style="padding: 20px;">
                        Tidak ada data siswa.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>



    <!-- HALAMAN 2: KEHADIRAN PELAJARAN SAYA -->
    <div class="page-break"></div>

    <div class="header-kop">
        <div class="header-text">
            <h1>YAYASAN PERGURUAN PEMBDA NIAS</h1>
            <h2>{{ strtoupper($sekolah->name ?? 'SEKOLAH') }}</h2>
            <p>{{ $sekolah->address ?? 'Alamat Sekolah' }}</p>
        </div>
    </div>

    <h3 class="text-center mb-4">REKAPITULASI KEHADIRAN PELAJARAN GURU</h3>

    <table class="info-table">
        <tr>
            <td width="100"><strong>Nama Guru</strong></td>
            <td width="10">:</td>
            <td>{{ $teacher->name }}</td>
        </tr>
        <tr>
            <td><strong>Kelas</strong></td>
            <td>:</td>
            <td>{{ $selectedClassroom->class_name }}</td>
        </tr>
        <tr>
            <td><strong>Bulan</strong></td>
            <td>:</td>
            <td>{{ $monthsList[$selectedMonth] ?? '' }} {{ $selectedYear }}</td>
        </tr>
        @if(isset($assignmentInfo))
        <tr>
            <td><strong>Tipe Kelas</strong></td>
            <td>:</td>
            <td>{{ $assignmentInfo }}</td>
        </tr>
        @endif
    </table>

    <table>
        <thead>
            <tr>
                <th rowspan="2" width="30">No</th>
                <th rowspan="2" width="200">Nama Siswa</th>
                <th rowspan="2" width="80">NISN</th>
                <th colspan="{{ $daysInMonth }}">Tanggal</th>
                <th colspan="4">Total</th>
            </tr>
            <tr>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    <th width="15" style="font-size:10px">{{ $d }}</th>
                @endfor
                <th width="20">H</th>
                <th width="20">S</th>
                <th width="20">I</th>
                <th width="20">A</th>
            </tr>
        </thead>
        <tbody>
            @forelse($classroomStudents as $idx => $st)
                @php
                    $stStat = $lessonStudentStats[$st->id] ?? ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0];
                    $isWajib = in_array($st->id, $wajibStudentIds);
                @endphp
                <tr>
                    <td class="text-center {{ !$isWajib ? 'badge' : '' }}">{{ $idx + 1 }}</td>
                    <td class="{{ !$isWajib ? 'badge' : '' }}">
                        {{ $st->full_name }}
                        @if(!$isWajib) <span class="badge">[TDK WAJIB]</span> @endif
                    </td>
                    <td class="text-center {{ !$isWajib ? 'badge' : '' }}">{{ $st->nisn ?? '-' }}</td>
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $stStatus = $lessonMatrixMap[$st->id][$d] ?? null;
                            if ($stStatus) {
                                $stChar = match($stStatus) {
                                    'hadir' => 'H',
                                    'sakit' => 'S',
                                    'izin' => 'I',
                                    'alpha' => 'A',
                                    default => ''
                                };
                            }
                        @endphp
                        <td class="text-center" style="font-size:10px; {{ !$isWajib && !$stStatus ? 'background-color: #f5f5f5;' : '' }}">
                            @if($stStatus)
                                {{ $stChar }}
                            @elseif(!$isWajib)
                                -
                            @endif
                        </td>
                    @endfor
                    <td class="text-center font-bold {{ !$isWajib ? 'badge' : '' }}">{{ $stStat['hadir'] > 0 ? $stStat['hadir'] : '-' }}</td>
                    <td class="text-center font-bold {{ !$isWajib ? 'badge' : '' }}">{{ $stStat['sakit'] > 0 ? $stStat['sakit'] : '-' }}</td>
                    <td class="text-center font-bold {{ !$isWajib ? 'badge' : '' }}">{{ $stStat['izin'] > 0 ? $stStat['izin'] : '-' }}</td>
                    <td class="text-center font-bold {{ !$isWajib ? 'badge' : '' }}">{{ $stStat['alpha'] > 0 ? $stStat['alpha'] : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $daysInMonth + 7 }}" class="text-center" style="padding: 20px;">
                        Tidak ada data siswa.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>



    <script>
        // Otomatis muncul dialog print saat halaman dibuka (opsional)
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
