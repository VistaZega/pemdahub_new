<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Keuangan Yayasan</title>
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
        <h3>LAPORAN REKAPITULASI KONSOLIDASI KEUANGAN PERGURUAN</h3>
        <p>Tahun Pelajaran: {{ $currentYear->year ?? '-' }} | Periode: {{ $periodMode === 'annual' ? 'Tahunan (12 Bulan)' : 'Bulanan (1 Bulan)' }} | Metode: {{ ($viewMode ?? 'cash') === 'cash' ? 'Realisasi Kas (Cash Basis)' : 'Proyeksi Potensi (Accrual Basis)' }}</p>
    </div>

    <table class="meta-table">
        <tr>
            <td width="15%"><strong>Dicetak Pada:</strong></td>
            <td width="35%">{{ date('d F Y, H:i') }} WIB</td>
            <td width="15%"><strong>Dibuat Oleh:</strong></td>
            <td width="35%">Sistem Informasi PembdaHUB (Yayasan)</td>
        </tr>
    </table>

    <h4 style="margin: 0 0 8px 0; color:#000; font-size:11px; text-transform:uppercase;">I. MATRIKS KONSOLIDASI AKHIR PERGURUAN</h4>
    <table class="table">
        <thead>
            <tr>
                <th width="50%">Komponen Konsolidasi Keuangan</th>
                <th width="25%" class="text-right nowrap">Nominal 1 Bulan</th>
                <th width="25%" class="text-right nowrap">Nominal Total Periode</th>
            </tr>
        </thead>
        <tbody>
            <tr style="background-color: #a7f3d0;">
                <td class="font-bold">1. TOTAL PENDAPATAN SPP (SELURUH UNIT SEKOLAH)</td>
                <td class="text-right font-bold nowrap">Rp&nbsp;{{ number_format($grandTotalIncomeMonthly, 0, ',', '.') }}</td>
                <td class="text-right font-bold nowrap">Rp&nbsp;{{ number_format($grandTotalIncome, 0, ',', '.') }}</td>
            </tr>

            @foreach($schoolSppData as $schData)
            <tr style="background-color: #ffffff;">
                <td style="padding-left: 20px;">&bull; Rencana Pendapatan SPP {{ $schData['school']->name }} ({{ $schData['total_students'] }} Siswa)</td>
                <td class="text-right nowrap">Rp&nbsp;{{ number_format($schData['income_monthly'], 0, ',', '.') }}</td>
                <td class="text-right nowrap">Rp&nbsp;{{ number_format($schData['income_total'], 0, ',', '.') }}</td>
            </tr>
            @endforeach

            <tr style="background-color: #ffffff;">
                <td style="padding-left: 20px;">a. Belanja Pegawai Perguruan (Gaji Guru & Staf Sekolah + Yayasan)</td>
                <td class="text-right font-bold nowrap">Rp&nbsp;{{ number_format($totalGajiLembagaMonthly, 0, ',', '.') }}</td>
                <td class="text-right font-bold nowrap">Rp&nbsp;{{ number_format($totalGajiLembagaPeriod, 0, ',', '.') }}</td>
            </tr>
            <tr style="background-color: #ffffff;">
                <td style="padding-left: 20px;">b. Belanja Operasional Non-Gaji (Kode Rekening 5.1.01 - 5.1.15)</td>
                <td class="text-right font-bold nowrap">Rp&nbsp;{{ number_format($totalBelanjaOpsMonthly, 0, ',', '.') }}</td>
                <td class="text-right font-bold nowrap">Rp&nbsp;{{ number_format($totalBelanjaOpsPeriod, 0, ',', '.') }}</td>
            </tr>
            <tr style="background-color: #fca5a5; font-weight: bold;">
                <td class="font-bold">2. TOTAL RENCANA BELANJA PERGURUAN (a + b)</td>
                <td class="text-right font-bold nowrap">(Rp&nbsp;{{ number_format($grandTotalBelanjaMonthly, 0, ',', '.') }})</td>
                <td class="text-right font-bold nowrap">(Rp&nbsp;{{ number_format($grandTotalBelanjaPeriod, 0, ',', '.') }})</td>
            </tr>
        </tbody>
        <tfoot>
            <tr style="background-color: #000; color: #fff; font-weight: bold;">
                <td class="text-right font-bold" style="color:#fff;">SALDO BERSIH AKHIR PERGURUAN (1 - 2):</td>
                <td class="text-right font-bold nowrap" style="font-size:10px; color: {{ $grandTotalSaldoAkhirMonthly >= 0 ? '#34d399' : '#f87171' }};">
                    {{ $grandTotalSaldoAkhirMonthly >= 0 ? 'SURPLUS: ' : 'DEFISIT: ' }} Rp&nbsp;{{ number_format(abs($grandTotalSaldoAkhirMonthly), 0, ',', '.') }}
                </td>
                <td class="text-right font-bold nowrap" style="font-size:11px; color: {{ $grandTotalSaldoAkhir >= 0 ? '#34d399' : '#f87171' }};">
                    {{ $grandTotalSaldoAkhir >= 0 ? 'SURPLUS: ' : 'DEFISIT: ' }} Rp&nbsp;{{ number_format(abs($grandTotalSaldoAkhir), 0, ',', '.') }}
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
                <p><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></p>
            </td>
        </tr>
    </table>

</body>
</html>
