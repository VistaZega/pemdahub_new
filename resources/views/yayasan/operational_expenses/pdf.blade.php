<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rencana Belanja Yayasan & Perguruan (RAPBY)</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #5b21b6;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header h2 {
            margin: 0;
            font-size: 15px;
            color: #4c1d95;
            text-transform: uppercase;
        }
        .header h3 {
            margin: 3px 0 0 0;
            font-size: 12px;
            color: #1e1b4b;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 9px;
            color: #6b7280;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 9px;
        }
        .meta-table td {
            padding: 2px 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .table th {
            background-color: #4c1d95;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
            padding: 5px 6px;
            border: 1px solid #4c1d95;
            text-align: left;
        }
        .table td {
            padding: 4px 6px;
            border: 1px solid #e5e7eb;
            font-size: 9px;
        }
        .table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .footer-sig {
            margin-top: 25px;
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
        <h3>LAPORAN RENCANA ANGGARAN BELANJA YAYASAN & PERGURUAN (RAPBY)</h3>
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
                <th width="5%" class="text-center">No</th>
                <th width="12%">Kode Rek.</th>
                <th width="33%">Nama Rekening Belanja</th>
                <th width="8%" class="text-center">Jumlah</th>
                <th width="10%" class="text-center">Satuan</th>
                <th width="16%" class="text-right">Tarif Satuan</th>
                <th width="16%" class="text-right">Total Anggaran ({{ $periodMode === 'annual' ? '12 Bln' : '1 Bln' }})</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($expenseAccounts as $code => $acc)
                @php
                    $detail = $parsedExpenseDetails[$code] ?? [];
                    $vol = $detail['volume'] ?? 1;
                    $unit = $detail['unit'] ?? 'Paket';
                    $tariff = $detail['tariff'] ?? 0;
                    $amtMonthly = $detail['amount'] ?? 0;
                    $amtPeriod = $amtMonthly * $multiplier;
                    $isAuto = $acc['is_automatic'] ?? false;
                @endphp
                <tr style="{{ $isAuto ? 'background-color: #eff6ff;' : '' }}">
                    <td class="text-center">{{ $no++ }}</td>
                    <td class="font-bold text-center" style="font-family: monospace;">{{ $code }}</td>
                    <td class="font-bold">
                        {{ $acc['name'] }}
                        @if($isAuto)
                            <span style="font-size: 8px; color: #1e40af;">(Otomatis Payroll)</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $vol }}</td>
                    <td class="text-center">{{ $unit }}</td>
                    <td class="text-right">Rp {{ number_format($tariff, 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: {{ $isAuto ? '#1e3a8a' : '#111827' }};">
                        Rp {{ number_format($amtPeriod, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f3e8ff; font-weight: bold;">
                <td colspan="6" class="text-right" style="font-size: 9px; text-transform: uppercase;">
                    GRAND TOTAL RENCANA BELANJA PERGURUAN (RAPBY):
                </td>
                <td class="text-right" style="font-size: 10px; color:#4c1d95;">
                    Rp {{ number_format($grandTotalBelanjaPeriod, 0, ',', '.') }}
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
