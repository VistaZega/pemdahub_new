<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Perjanjian Kinerja - {{ $contract->employee->full_name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 18mm 15mm 22mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.45;
            color: #000;
            background-color: #ffffff;
            margin: 0;
            padding: 15px;
        }

        .no-print-bar {
            background: #1e1b4b;
            color: #fff;
            padding: 10px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-family: sans-serif;
            font-size: 13px;
        }

        .btn-print {
            background: #10b981;
            color: white;
            border: none;
            padding: 7px 16px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            float: right;
            font-size: 13px;
        }

        .btn-print:hover {
            background: #059669;
        }

        .page-container {
            width: 100%;
            background: #ffffff;
            box-sizing: border-box;
        }

        /* Kop Surat Tabel */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }

        .kop-table td {
            vertical-align: middle;
        }

        .kop-title {
            text-align: center;
        }

        .kop-title h2 {
            margin: 0;
            font-size: 12.5pt;
            font-weight: bold;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .kop-title h1 {
            margin: 2px 0;
            font-size: 17.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .kop-title h3 {
            margin: 2px 0 0 0;
            font-size: 12.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .kop-title p {
            margin: 1px 0 0 0;
            font-size: 9.5pt;
            font-style: italic;
        }

        .garis-kop {
            border-bottom: 3px double #000;
            margin-bottom: 16px;
        }

        /* Judul Dokumen */
        .doc-title {
            text-align: center;
            margin-bottom: 16px;
        }

        .doc-title h3 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
        }

        .doc-title p {
            margin: 3px 0 0 0;
            font-size: 10.5pt;
            font-weight: bold;
        }

        /* Detail Identitas */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 11pt;
        }

        .info-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        /* Tabel Pilar / Target */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 16px;
            font-size: 10.5pt;
        }

        .data-table th {
            background-color: #f3f4f6;
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10pt;
        }

        .data-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
        }

        /* Komitmen & Sanksi */
        .komitmen-box {
            font-size: 10.5pt;
            text-align: justify;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        /* Tanda Tangan 2 Pihak (Hanya Kasek & Yang Bersangkutan) */
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            page-break-inside: avoid;
            font-size: 10.5pt;
        }

        .ttd-table td {
            vertical-align: top;
            text-align: center;
            width: 50%;
        }

        .ttd-box {
            padding: 0 10px;
        }

        .nama-ttd {
            margin-top: 65px;
            font-weight: bold;
            text-decoration: underline;
        }

        @media print {
            .no-print-bar { display: none !important; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body>

    <!-- Bar Tombol Cetak -->
    <div class="no-print-bar">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Dokumen</button>
        <strong>Dokumen Perjanjian Kinerja Resmi</strong> — Yayasan Perguruan Pembda Nias
    </div>

    <div class="page-container">
        
        <!-- Kop Surat Resmi -->
        <table class="kop-table">
            <tr>
                <td style="width: 85px; text-align: left; padding-left: 10px;">
                    <img src="{{ asset('images/logo-pembda.png') }}" style="width: 70px; height: auto;" alt="Logo PEMBDA">
                </td>
                <td class="kop-title">
                    <h2>YAYASAN PERGURUAN PEMBANGUNAN DAERAH NIAS</h2>
                    <h1>(PEMBDA)</h1>
                    <h3>{{ strtoupper($contract->school->name ?? 'UNIT SEKOLAH PEMBDA NIAS') }}</h3>
                    <p>Jl. Pelita No.09 Kel. Ilir Kota Gunungsitoli (22815)</p>
                </td>
                <td style="width: 85px;"></td>
            </tr>
        </table>
        <div class="garis-kop"></div>

        <!-- Judul Dokumen -->
        <div class="doc-title">
            <h3>PERJANJIAN KINERJA & PAKTA INTEGRITAS</h3>
            <p>TAHUN PELAJARAN {{ $contract->academicYear->year ?? date('Y') }}</p>
            <p style="font-size: 9.5pt; color: #374151; font-weight: normal; margin-top: 2px;">
                Tipe Dokumen: 
                @if($contract->contract_type == 'pkg_kejuruan')
                    <strong>Form 2A — Perjanjian Kinerja Guru Produktif/Kejuruan</strong>
                @elseif($contract->contract_type == 'pkg_umum')
                    <strong>Form 2B — Perjanjian Kinerja Guru Mata Pelajaran Umum</strong>
                @else
                    <strong>Form 4 — Perjanjian Kinerja Jabatan ({{ $contract->position->position_name ?? 'Tugas Tambahan' }})</strong>
                @endif
            </p>
        </div>

        <!-- Keterangan Persetujuan Resmi -->
        <div style="border: 1.5px solid #059669; background-color: #f0fdf4; color: #166534; padding: 8px 12px; border-radius: 6px; margin-bottom: 16px; font-size: 9.5pt; text-align: center; line-height: 1.4;">
            Perjanjian Kinerja ini telah <strong>DISETUJUI oleh Kepala Sekolah {{ $contract->school->name ?? '' }}</strong> dan <strong>DISAHKAN oleh Pengurus Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)</strong>.
        </div>

        <!-- Detail Identitas -->
        <div class="content">
            <p style="margin-bottom: 8px;">Yang bertanda tangan di bawah ini:</p>
            <table class="info-table">
                <tr>
                    <td style="width: 170px;"><strong>Nama Lengkap</strong></td>
                    <td style="width: 15px;">:</td>
                    <td><strong>{{ $contract->employee->full_name }}</strong></td>
                </tr>
                <tr>
                    <td><strong>NIP / NUPTK / Kode</strong></td>
                    <td>:</td>
                    <td>{{ $contract->employee->nip ?? $contract->employee->employee_code ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Status Kepegawaian</strong></td>
                    <td>:</td>
                    <td>{{ strtoupper($contract->employee->employment_status ?? '-') }}</td>
                </tr>
                <tr>
                    <td><strong>Unit Kerja</strong></td>
                    <td>:</td>
                    <td>{{ $contract->school->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Jabatan / Tugas Utama</strong></td>
                    <td>:</td>
                    <td>
                        @if($contract->contract_type == 'jabatan_tambahan')
                            <strong>{{ $contract->position->position_name ?? 'Jabatan Tambahan' }}</strong>
                        @else
                            Guru Mata Pelajaran
                        @endif
                    </td>
                </tr>
            </table>

            <p style="margin-bottom: 10px;">Dengan ini menyatakan komitmen dan kesanggupan riil untuk mencapai target sasaran kinerja sebagai berikut selama Tahun Pelajaran {{ $contract->academicYear->year }}:</p>

            <!-- Tabel Target Kinerja -->
            <table class="data-table">
                @if(in_array($contract->contract_type, ['pkg_kejuruan', 'pkg_umum']))
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 35%;">Pilar Perjanjian Kinerja</th>
                            <th style="width: 60%;">Rencana Bukti Fisik Nyata & Target Sasaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="text-align: center;">1</td>
                            <td><strong>{{ $contract->contract_type == 'pkg_kejuruan' ? 'Kompetensi Praktik (30%)' : 'Kompetensi Relevansi Praktik (30%)' }}</strong></td>
                            <td>{{ $contract->target_data['pilar_1'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">2</td>
                            <td><strong>{{ $contract->contract_type == 'pkg_kejuruan' ? 'Kontribusi Program (30%)' : 'Kontribusi Program/TEFA (30%)' }}</strong></td>
                            <td>{{ $contract->target_data['pilar_2'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">3</td>
                            <td><strong>Kolaborasi (20%)</strong></td>
                            <td>{{ $contract->target_data['pilar_3'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">4</td>
                            <td><strong>Budaya Industri 5R (20%)</strong></td>
                            <td>{{ $contract->target_data['pilar_4'] ?? '-' }}</td>
                        </tr>
                    </tbody>
                @elseif($contract->contract_type == 'jabatan_tambahan')
                    <thead>
                        <tr>
                            <th style="width: 8%;">No</th>
                            <th style="width: 92%;">Deskripsi Rencana & Target Sasaran Pekerjaan Riil</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="text-align: center;">1</td>
                            <td>{{ $contract->target_data['target_1'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">2</td>
                            <td>{{ $contract->target_data['target_2'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">3</td>
                            <td>{{ $contract->target_data['target_3'] ?? '-' }}</td>
                        </tr>
                    </tbody>
                @endif
            </table>

            <!-- Komitmen & Sanksi -->
            <div class="komitmen-box">
                Demikian Perjanjian Kinerja ini saya buat dengan sadar dan penuh rasa tanggung jawab sebagai komitmen tugas utama saya. Apabila di akhir semester sasaran kinerja dan bukti fisik ini <strong>TIDAK TERCAPAI / TIDAK TERBUKTI</strong>, saya bersedia menerima sanksi administratif berupa peninjauan ulang hingga pencabutan jam mengajar/penonaktifan tugas tambahan oleh Kepala Sekolah dan Pengurus Yayasan Perguruan Pembangunan Daerah Nias.
            </div>
        </div>

        <!-- Tanda Tangan 2 Pihak (Hanya Kasek & Yang Bersangkutan) -->
        @php
            $kepalaSekolahName = \App\Models\Employee::where('school_id', $contract->school_id)
                ->whereHas('activePositions', function($q) use ($contract) {
                    $q->where('employee_positions.academic_year_id', $contract->academic_year_id)
                      ->where(function($sub) {
                          $sub->where('position_name', 'like', '%Kepala Sekolah%')
                              ->orWhere('position_code', 'KASEK');
                      });
                })->first()?->full_name ?? 'Kepala Sekolah';
        @endphp

        <table class="ttd-table">
            <tr>
                <td style="width: 50%;">
                    <div class="ttd-box">
                        <p>Gunungsitoli, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                        <p>Yang Membuat Pernyataan,</p>
                        <p><strong>Pegawai / Guru</strong></p>
                        <p class="nama-ttd">{{ $contract->employee->full_name }}</p>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div class="ttd-box">
                        <p>Disetujui Oleh,</p>
                        <p><strong>Kepala Sekolah {{ $contract->school->name ?? '' }}</strong></p>
                        <p class="nama-ttd">{{ $kepalaSekolahName }}</p>
                    </div>
                </td>
            </tr>
        </table>

    </div>

</body>
</html>
