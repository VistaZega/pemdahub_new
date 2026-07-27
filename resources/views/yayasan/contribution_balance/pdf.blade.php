<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rencana Pendapatan SPP Unit Sekolah</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #5b21b6;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 0;
            font-size: 16px;
            color: #4c1d95;
            text-transform: uppercase;
        }
        .header h3 {
            margin: 3px 0 0 0;
            font-size: 13px;
            color: #1e1b4b;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 10px;
            color: #6b7280;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 10px;
        }
        .meta-table td {
            padding: 2px 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .table th {
            background-color: #4c1d95;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 6px 8px;
            border: 1px solid #4c1d95;
            text-align: left;
        }
        .table td {
            padding: 5px 8px;
            border: 1px solid #e5e7eb;
            font-size: 10px;
        }
        .table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .footer-sig {
            margin-top: 30px;
            width: 100%;
        }
        .footer-sig td {
            text-align: center;
            vertical-align: top;
        }
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

    <h4 style="margin: 0 0 8px 0; color:#4c1d95; font-size:12px;">I. REKAPITULASI PENDAPATAN SPP SELURUH UNIT SEKOLAH</h4>
    <table class="table">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="35%">Nama Unit Sekolah</th>
                <th width="20%" class="text-center">Jumlah Siswa</th>
                <th width="20%" class="text-right">Pendapatan / Bln</th>
                <th width="20%" class="text-right">Total Pendapatan SPP</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schoolData as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $item['school']->name }}</td>
                    <td class="text-center">{{ $item['total_students'] }}</td>
                    <td class="text-right">Rp {{ number_format($item['income_monthly'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #065f46;">Rp {{ number_format($item['income_total'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f3e8ff; font-weight: bold;">
                <td colspan="3" class="text-right">GRAND TOTAL PENDAPATAN SPP:</td>
                <td class="text-right">Rp {{ number_format(array_sum(array_column($schoolData, 'income_monthly')), 0, ',', '.') }}</td>
                <td class="text-right" style="font-size: 11px; color:#4c1d95;">Rp {{ number_format($grandTotalIncome, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <h4 style="margin: 15px 0 8px 0; color:#4c1d95; font-size:12px;">II. RINCIAN PENDAPATAN PER TINGKAT KELAS</h4>
    @foreach($schoolData as $item)
        <div style="margin-bottom: 12px; page-break-inside: avoid;">
            <strong style="font-size: 11px; color:#1e1b4b;">{{ $item['school']->name }} ({{ $item['school']->type }})</strong>
            <table class="table" style="margin-top:4px;">
                <thead>
                    <tr>
                        <th width="25%">Tingkat Kelas</th>
                        <th width="15%" class="text-center">Jumlah Siswa</th>
                        <th width="20%" class="text-right">Tarif SPP / Siswa</th>
                        <th width="20%" class="text-right">Pendapatan / Bln</th>
                        <th width="20%" class="text-right">Total Pendapatan ({{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }})</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($item['levels'] as $lvl)
                        <tr>
                            <td>Kelas {{ $lvl['level'] }}</td>
                            <td class="text-center">{{ $lvl['student_count'] }}</td>
                            <td class="text-right">Rp {{ number_format($lvl['spp_monthly'], 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($lvl['income_monthly'], 0, ',', '.') }}</td>
                            <td class="text-right font-bold">Rp {{ number_format($lvl['income_total'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center" style="color:#6b7280; font-style:italic;">Belum ada kelas terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background-color:#faf5ff; font-weight:bold;">
                        <td class="text-right">Subtotal:</td>
                        <td class="text-center">{{ $item['total_students'] }}</td>
                        <td class="text-right">-</td>
                        <td class="text-right">Rp {{ number_format($item['income_monthly'], 0, ',', '.') }}</td>
                        <td class="text-right" style="color:#065f46;">Rp {{ number_format($item['income_total'], 0, ',', '.') }}</td>
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
                <p><u>___________________________</u></p>
            </td>
        </tr>
    </table>

</body>
</html>
