<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rencana Belanja Operasional Yayasan</title>
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
        <h3>LAPORAN RENCANA BELANJA OPERASIONAL YAYASAN (RAPBY)</h3>
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

    <table class="table">
        <thead>
            <tr>
                <th width="8%" class="text-center">No</th>
                <th width="15%">Kode Rekening</th>
                <th width="37%">Nama Rekening Belanja Operasional</th>
                <th width="18%">Kategori</th>
                <th width="22%" class="text-right">Rencana Anggaran ({{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }})</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($expenseAccounts as $code => $acc)
                @php
                    $valMonthly = isset($savedExpenseDetails[$code]) ? (float)$savedExpenseDetails[$code] : 0;
                    $valPeriod = $valMonthly * $multiplier;
                @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td class="font-bold text-center" style="font-family: monospace;">{{ $code }}</td>
                    <td class="font-bold">{{ $acc['name'] }}</td>
                    <td>{{ $acc['category'] }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($valPeriod, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f3e8ff; font-weight: bold;">
                <td colspan="4" class="text-right">TOTAL RENCANA BELANJA OPERASIONAL:</td>
                <td class="text-right" style="font-size: 11px; color:#4c1d95;">
                    Rp {{ number_format($totalPeriod, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

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
