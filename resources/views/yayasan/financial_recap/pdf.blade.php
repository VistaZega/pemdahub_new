<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Keuangan Yayasan</title>
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
        .badge-surplus {
            color: #065f46;
            font-weight: bold;
        }
        .badge-defisit {
            color: #991b1b;
            font-weight: bold;
        }
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
        <h3>LAPORAN REKAPITULASI KONSOLIDASI KEUANGAN PERGURUAN</h3>
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

    <h4 style="margin: 0 0 8px 0; color:#4c1d95; font-size:12px;">I. REKAPITULASI KONSOLIDASI AKHIR PERGURUAN</h4>
    <table class="table">
        <thead>
            <tr>
                <th width="70%">Komponen Konsolidasi Keuangan</th>
                <th width="30%" class="text-right">Nominal Periode</th>
            </tr>
        </thead>
        <tbody>
            <tr style="background-color: #ecfdf5;">
                <td class="font-bold text-emerald-800">1. TOTAL PENDAPATAN SPP (SELURUH UNIT SEKOLAH)</td>
                <td class="text-right font-bold text-emerald-700">Rp {{ number_format($grandTotalIncome, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">a. Belanja Pegawai (Gaji Guru & Staf Sekolah + Yayasan)</td>
                <td class="text-right font-bold text-blue-700">Rp {{ number_format($totalGajiLembagaPeriod, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">b. Belanja Operasional Non-Gaji (Kode Rekening 5.1.01 - 5.1.14)</td>
                <td class="text-right font-bold text-amber-700">Rp {{ number_format($totalBelanjaOpsPeriod, 0, ',', '.') }}</td>
            </tr>
            <tr style="background-color: #fef2f2; font-weight: bold;">
                <td class="font-bold text-red-900">2. TOTAL RENCANA BELANJA PERGURUAN (a + b)</td>
                <td class="text-right font-bold text-red-700">(Rp {{ number_format($grandTotalBelanjaPeriod, 0, ',', '.') }})</td>
            </tr>
        </tbody>
        <tfoot>
            <tr style="background-color: #f3e8ff; font-weight: bold;">
                <td class="text-right">SALDO BERSIH AKHIR PERGURUAN (1 - 2):</td>
                <td class="text-right {{ $grandTotalSaldoAkhir >= 0 ? 'badge-surplus' : 'badge-defisit' }}" style="font-size:11px;">
                    {{ $grandTotalSaldoAkhir >= 0 ? 'SURPLUS (+): ' : 'DEFISIT (-): ' }} Rp {{ number_format(abs($grandTotalSaldoAkhir), 0, ',', '.') }}
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
