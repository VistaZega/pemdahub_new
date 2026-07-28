<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rencana Pendapatan SPP Unit Sekolah</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9.5px;
            color: #000;
            line-height: 1.5;
            padding: 20px 25px;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 0;
            font-size: 15px;
            color: #000;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header h3 {
            margin: 3px 0 0 0;
            font-size: 12px;
            color: #000;
            font-weight: bold;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 9px;
            color: #000;
            font-weight: bold;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 9px;
        }
        .meta-table td {
            padding: 2px 0;
            font-weight: bold;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .table th {
            background-color: #000;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5px;
            padding: 6px 8px;
            border: 1px solid #000;
            text-align: left;
            white-space: nowrap;
        }
        .table td {
            padding: 6px 8px;
            border: 1px solid #000;
            font-size: 9px;
            font-weight: bold;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .nowrap { white-space: nowrap !important; }
        .footer-sig {
            margin-top: 30px;
            width: 100%;
        }
        .footer-sig td {
            text-align: center;
            vertical-align: top;
            font-size: 9px;
            font-weight: bold;
        }
        @page { margin: 15mm 12mm; }
    </style>
</head>
<body>

    <div class="header">
        <h2>YAYASAN PERGURUAN PEMBDA</h2>
        <h3>LAPORAN RENCANA PENDAPATAN SPP UNIT SEKOLAH</h3>
        <p>Tahun Pelajaran: {{ $currentYear->year ?? '-' }} | Periode: {{ $periodMode === 'annual' ? 'Tahunan (12 Bulan)' : 'Bulanan (1 Bulan)' }}</p>
    </div>

    <table class="meta-table">
        <tr>
            <td width="15%"><strong>Dicetak Pada:</strong></td>
            <td width="35%">{{ date('d F Y, H:i') }} WIB</td>
            <td width="15%"><strong>Dibuat Oleh:</strong></td>
            <td width="35%">Sistem Informasi PembdaHUB (Yayasan)</td>
        </tr>
    </table>

    <h4 style="margin: 0 0 8px 0; color:#000; font-size:11px; text-transform:uppercase;">I. REKAPITULASI PENDAPATAN SPP SELURUH UNIT SEKOLAH</h4>
    <table class="table">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="35%">Nama Unit Sekolah</th>
                <th width="20%" class="text-center">Jumlah Siswa</th>
                <th width="20%" class="text-right nowrap">Pendapatan / Bln</th>
                <th width="20%" class="text-right nowrap">Total Pendapatan SPP</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schoolData as $index => $item)
                <tr style="{{ $index % 2 === 1 ? 'background-color: #f1f5f9;' : '' }}">
                    <td class="text-center font-bold">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $item['school']->name }}</td>
                    <td class="text-center font-bold">{{ $item['total_students'] }}</td>
                    <td class="text-right font-bold nowrap">Rp&nbsp;{{ number_format($item['income_monthly'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold nowrap" style="color: #000;">Rp&nbsp;{{ number_format($item['income_total'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #000; color: #fff; font-weight: bold;">
                <td colspan="3" class="text-right font-bold" style="color:#fff;">GRAND TOTAL PENDAPATAN SPP:</td>
                <td class="text-right font-bold nowrap" style="color:#fbbf24;">Rp&nbsp;{{ number_format(array_sum(array_column($schoolData, 'income_monthly')), 0, ',', '.') }}</td>
                <td class="text-right font-bold nowrap" style="font-size: 11px; color:#34d399;">Rp&nbsp;{{ number_format($grandTotalIncome, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <h4 style="margin: 15px 0 8px 0; color:#000; font-size:11px; text-transform:uppercase;">II. RINCIAN PENDAPATAN PER TINGKAT KELAS</h4>
    @foreach($schoolData as $item)
        <div style="margin-bottom: 12px; page-break-inside: avoid;">
            <strong style="font-size: 10.5px; color:#000; text-transform:uppercase;">{{ $item['school']->name }} ({{ $item['school']->type }})</strong>
            <table class="table" style="margin-top:4px;">
                <thead>
                    <tr>
                        <th width="25%">Tingkat Kelas</th>
                        <th width="15%" class="text-center">Jumlah Siswa</th>
                        <th width="20%" class="text-right nowrap">Tarif SPP / Siswa</th>
                        <th width="20%" class="text-right nowrap">Pendapatan / Bln</th>
                        <th width="20%" class="text-right nowrap">Total Pendapatan ({{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }})</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($item['levels'] as $lvl)
                        <tr>
                            <td class="font-bold">Kelas {{ $lvl['level'] }}</td>
                            <td class="text-center font-bold">{{ $lvl['student_count'] }}</td>
                            <td class="text-right font-bold nowrap">Rp&nbsp;{{ number_format($lvl['spp_monthly'], 0, ',', '.') }}</td>
                            <td class="text-right font-bold nowrap">Rp&nbsp;{{ number_format($lvl['income_monthly'], 0, ',', '.') }}</td>
                            <td class="text-right font-bold nowrap">Rp&nbsp;{{ number_format($lvl['income_total'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center" style="color:#000; font-style:italic;">Belum ada kelas terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background-color:#a7f3d0; font-weight:bold;">
                        <td class="text-right font-bold">Subtotal:</td>
                        <td class="text-center font-bold">{{ $item['total_students'] }}</td>
                        <td class="text-right font-bold">-</td>
                        <td class="text-right font-bold nowrap">Rp&nbsp;{{ number_format($item['income_monthly'], 0, ',', '.') }}</td>
                        <td class="text-right font-bold nowrap" style="color:#000;">Rp&nbsp;{{ number_format($item['income_total'], 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endforeach

    <table class="footer-sig">
        <tr>
            <td width="60%"></td>
            <td width="40%">
                <p>Gunungsitoli, {{ date('d F Y') }}</p>
                <p><strong>Ketua Yayasan Perguruan Pembda</strong></p>
                <br><br><br>
                <p><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></p>
            </td>
        </tr>
    </table>

</body>
</html>
