<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Pendapatan & Belanja Yayasan</title>
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
        <h3>LAPORAN REKAPITULASI PENDAPATAN & BELANJA KONSOLIDASI</h3>
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

    <h4 style="margin: 0 0 8px 0; color:#4c1d95; font-size:12px;">I. REKAPITULASI KONTRIBUSI UNIT SEKOLAH</h4>
    <table class="table">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="30%">Nama Unit Sekolah</th>
                <th width="15%" class="text-center">Siswa</th>
                <th width="25%" class="text-right">Pendapatan SPP</th>
                <th width="25%" class="text-right">Gaji Guru & Pegawai</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schoolData as $idx => $row)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-bold">{{ $row['school']->name }}</td>
                    <td class="text-center">{{ $row['total_students'] }}</td>
                    <td class="text-right">Rp {{ number_format($row['income_total'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($row['salary_total'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f3e8ff; font-weight: bold;">
                <td colspan="3" class="text-right">SUBTOTAL UNIT SEKOLAH:</td>
                <td class="text-right">Rp {{ number_format($grandTotalIncome, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($grandTotalGajiSekolah, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <h4 style="margin: 15px 0 8px 0; color:#4c1d95; font-size:12px;">II. PENGELUARAN TERPUSAT YAYASAN</h4>
    <table class="table">
        <thead>
            <tr>
                <th width="75%">Komponen Pengeluaran Pusat Yayasan</th>
                <th width="25%" class="text-right">Nominal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Gaji Staf & Pengurus Yayasan ({{ $yayasanEmployeeCount }} pegawai)</td>
                <td class="text-right">Rp {{ number_format($totalYayasanSalary, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Rencana Belanja Operasional Terpusat (Internet, Listrik, Air, Sarpras, ATK, dll)</td>
                <td class="text-right">Rp {{ number_format($totalBelanjaOpsYayasan, 0, ',', '.') }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr style="background-color: #f3e8ff; font-weight: bold;">
                <td class="text-right">SUBTOTAL PENGELUARAN YAYASAN:</td>
                <td class="text-right">Rp {{ number_format($totalYayasanSalary + $totalBelanjaOpsYayasan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <h4 style="margin: 15px 0 8px 0; color:#4c1d95; font-size:12px;">III. REKAPITULASI KONSOLIDASI AKHIR PERGURUAN</h4>
    <table class="table">
        <thead>
            <tr>
                <th width="70%">Komponen Konsolidasi Keuangan</th>
                <th width="30%" class="text-right">Nominal Periode</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="font-bold">1. Total Pendapatan SPP (Seluruh Sekolah)</td>
                <td class="text-right font-bold text-emerald-700">Rp {{ number_format($grandTotalIncome, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="font-bold">2. Total Belanja Pegawai (Sekolah + Yayasan)</td>
                <td class="text-right font-bold">(Rp {{ number_format($grandTotalGajiLembaga, 0, ',', '.') }})</td>
            </tr>
            <tr>
                <td class="font-bold">3. Total Belanja Operasional Terpusat Yayasan</td>
                <td class="text-right font-bold">(Rp {{ number_format($totalBelanjaOpsYayasan, 0, ',', '.') }})</td>
            </tr>
            <tr style="background-color: #fef2f2; font-weight: bold;">
                <td>4. TOTAL SELURUH PENGELUARAN LEMBAGA (2 + 3)</td>
                <td class="text-right font-bold text-red-700">(Rp {{ number_format($grandTotalPengeluaran, 0, ',', '.') }})</td>
            </tr>
        </tbody>
        <tfoot>
            <tr style="background-color: #f3e8ff; font-weight: bold;">
                <td class="text-right">SALDO BERSIH AKHIR YAYASAN (1 - 4):</td>
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
