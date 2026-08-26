<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Konsolidasi - {{ $school->name }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #222; margin: 0; padding: 15px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 15px; }
        .header h2 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 3px 0 0 0; font-size: 11px; color: #555; }
        .section-title { font-size: 12px; font-weight: bold; margin-top: 15px; margin-bottom: 6px; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #ddd; padding: 6px 8px; font-size: 10px; }
        table.data-table th { background: #f4f4f4; text-align: left; font-weight: bold; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .total-row { background: #e6f4ea; font-weight: bold; }
        .summary-card { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 10px; margin-top: 15px; }
        .signatures { margin-top: 40px; width: 100%; border-collapse: collapse; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $school->name }}</h2>
        <p><strong>LAPORAN KONSOLIDASI KEUANGAN BULANAN BENDARA SEKOAH KE YAYASAN</strong></p>
        <p>Periode: {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}</p>
    </div>

    <!-- 1. PENDAPATAN -->
    <div class="section-title">1. PENDAPATAN (PENERIMAAN KAS RIIL)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Uraian Penerimaan</th>
                <th class="text-right" style="width: 140px;">Jumlah Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incomeDetails as $type => $amount)
            <tr>
                <td>Penerimaan {{ $type }}</td>
                <td class="text-right">Rp {{ number_format($amount, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2" style="font-style: italic; color: #777;">Belum ada penerimaan kas periode ini.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td>TOTAL PENDAPATAN KOTOR (GROSS INCOME)</td>
                <td class="text-right" style="color: #047857;">Rp {{ number_format($grossIncome, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- 2. PENGELUARAN GAJI -->
    <div class="section-title">2. PENGELUARAN GAJI PEGAWAI</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Kategori Pegawai</th>
                <th class="text-right" style="width: 140px;">Total Pengeluaran Gaji</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salaryDetails as $cat => $amount)
            <tr>
                <td>Belanja Gaji {{ $cat }}</td>
                <td class="text-right">Rp {{ number_format($amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row" style="background: #fef2f2;">
                <td>TOTAL PENGELUARAN GAJI & TUNJANGAN</td>
                <td class="text-right" style="color: #be123c;">Rp {{ number_format($salaryTotal, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- 3. RINGKASAN SALDO -->
    <div class="summary-card">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none;">Total Kas Sekolah Disimpan:</td>
                <td class="text-right font-bold" style="border: none;">Rp {{ number_format($schoolShareTotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="border: none; font-weight: bold; font-size: 11px;">SALDO NETTO YAYASAN:</td>
                <td class="text-right font-bold" style="border: none; font-size: 12px; color: #1d4ed8;">Rp {{ number_format($netBalance, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

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
