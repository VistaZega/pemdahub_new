<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Kas Umum - {{ $school->name }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; margin: 0; padding: 10px; }
        .header { text-align: center; border-bottom: 2px solid #222; padding-bottom: 8px; margin-bottom: 15px; }
        .header h2 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 3px 0 0 0; font-size: 12px; }
        .summary-box { margin-bottom: 15px; width: 100%; border-collapse: collapse; }
        .summary-box td { padding: 8px; border: 1px solid #ddd; background: #f9f9f9; text-align: center; }
        .summary-box font-title { display: block; font-size: 10px; font-weight: bold; color: #555; text-transform: uppercase; }
        .summary-box font-val { font-size: 13px; font-weight: bold; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #ccc; padding: 6px 8px; }
        table.data-table th { background: #eee; font-weight: bold; text-align: left; text-transform: uppercase; font-size: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .signatures { margin-top: 30px; width: 100%; border-collapse: collapse; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $school->name }}</h2>
        <p><strong>BUKU KAS UMUM (BKU)</strong></p>
        <p>Periode: {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}</p>
    </div>

    <table class="summary-box">
        <tr>
            <td>
                <span class="font-title">Total Penerimaan (Debet)</span>
                <span class="font-val" style="color: #047857;">Rp {{ number_format($totalDebit, 0, ',', '.') }}</span>
            </td>
            <td>
                <span class="font-title">Total Pengeluaran (Kredit)</span>
                <span class="font-val" style="color: #be123c;">Rp {{ number_format($totalCredit, 0, ',', '.') }}</span>
            </td>
            <td>
                <span class="font-title">Saldo Kas Akhir</span>
                <span class="font-val" style="color: #1d4ed8;">Rp {{ number_format($netEndingBalance, 0, ',', '.') }}</span>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 70px;">Tanggal</th>
                <th style="width: 90px;">No. Ref</th>
                <th>Uraian Transaksi</th>
                <th style="width: 95px;" class="text-right">Debet (Masuk)</th>
                <th style="width: 95px;" class="text-right">Kredit (Keluar)</th>
                <th style="width: 95px;" class="text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $trx)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ date('d/m/Y', strtotime($trx['date'])) }}</td>
                <td>{{ $trx['ref_no'] }}</td>
                <td>{{ $trx['description'] }}</td>
                <td class="text-right">{{ $trx['debit'] > 0 ? 'Rp ' . number_format($trx['debit'], 0, ',', '.') : '-' }}</td>
                <td class="text-right">{{ $trx['credit'] > 0 ? 'Rp ' . number_format($trx['credit'], 0, ',', '.') : '-' }}</td>
                <td class="text-right font-bold">Rp {{ number_format($trx['balance'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="font-style: italic; color: #888;">Tidak ada data transaksi kas pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f0f0f0; font-weight: bold;">
                <td colspan="4" class="text-right">TOTAL MUTASI PERIODE INI:</td>
                <td class="text-right" style="color: #047857;">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #be123c;">Rp {{ number_format($totalCredit, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #1d4ed8;">Rp {{ number_format($netEndingBalance, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="signatures">
        <tr>
            <td>
                Mengetahui,<br>
                <strong>Kepala Sekolah</strong>
                <br><br><br><br>
                ( ________________________ )
            </td>
            <td>
                Gunungsitoli, {{ date('d F Y') }}<br>
                <strong>Bendahara Sekolah</strong>
                <br><br><br><br>
                ( ________________________ )
            </td>
        </tr>
    </table>
</body>
</html>
