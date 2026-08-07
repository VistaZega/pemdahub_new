<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>RAPBY — Rencana Anggaran Belanja Yayasan & Perguruan Per Unit Sekolah</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; color: #000; line-height: 1.4; padding: 15px 20px; }
        .doc-header { text-align: center; border-bottom: 3px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
        .doc-header h1 { font-size: 14px; color: #000; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px; font-weight: bold; }
        .doc-header h2 { font-size: 11px; color: #000; margin-bottom: 4px; font-weight: bold; }
        .doc-header .meta { font-size: 8px; color: #000; font-weight: bold; }
        
        .section-title { font-size: 10px; font-weight: bold; text-transform: uppercase; margin-top: 15px; margin-bottom: 5px; background-color: #f1f5f9; padding: 4px 8px; border: 1px solid #000; border-left: 5px solid #000; }
        
        .rapby-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .rapby-table th { background-color: #000; color: #fff; font-weight: bold; text-transform: uppercase; font-size: 7.5px; padding: 5px; border: 1px solid #000; white-space: nowrap; }
        .rapby-table td { padding: 4px 5px; border: 1px solid #000; font-size: 8.5px; font-weight: bold; }
        
        .group-pegawai { background-color: #1e3a8a !important; color: #fff !important; font-weight: bold; }
        .group-pegawai td { border-color: #000; color: #fff; }
        .group-ops { background-color: #78350f !important; color: #fff !important; font-weight: bold; }
        .group-ops td { border-color: #000; color: #fff; }
        .subtotal-row { background-color: #e2e8f0 !important; font-weight: bold; color: #000 !important; }
        .grand-total { background-color: #000 !important; color: #fff !important; font-weight: bold; }
        .grand-total td { border-color: #000; font-size: 9.5px; color: #fff; white-space: nowrap; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .nowrap { white-space: nowrap !important; }
        .mono { font-family: 'Courier New', monospace; }
        
        .sig-table { width: 100%; margin-top: 25px; }
        .sig-table td { text-align: center; vertical-align: top; font-size: 8.5px; padding: 3px 0; font-weight: bold; }
        .sig-line { margin-top: 45px; }
        @page { margin: 12mm 10mm; }
    </style>
</head>
<body>
    <div class="doc-header">
        <h1>Yayasan Perguruan Pembda</h1>
        <h2>Rencana Anggaran Belanja Operasional Per Unit Sekolah (RAPBY)</h2>
        <p class="meta">Tahun Pelajaran: {{ $currentYear->year ?? '-' }} &bull; Periode: {{ $periodMode === 'annual' ? 'Tahunan (12 Bulan)' : 'Bulanan (1 Bulan)' }} &bull; Dicetak: {{ date('d F Y, H:i') }} WIB</p>
    </div>

    {{-- TABEL REKAPITULASI SUMMARY PER UNIT SEKOLAH --}}
    <div class="section-title">A. REKAPITULASI ALOKASI ANGGARAN BELANJA PER UNIT SEKOLAH</div>
    <table class="rapby-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="35%">Unit Sekolah / Lembaga</th>
                <th width="12%" class="text-center">Jumlah SDM</th>
                <th width="24%" class="text-right">Belanja Pegawai (Rp)</th>
                <th width="24%" class="text-right">Belanja Operasional (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schoolExpenseData as $sIdx => $sData)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td style="font-weight:bold;">{{ $sData['school_name'] }}</td>
                    <td class="text-center">{{ $sData['employee_count'] }} Orang</td>
                    <td class="text-right nowrap">Rp&nbsp;{{ number_format($sData['total_salary_period'], 0, ',', '.') }}</td>
                    <td class="text-right nowrap">Rp&nbsp;{{ number_format($sData['total_ops_period'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="grand-total">
                <td colspan="2" class="text-right">TOTAL PERGURAN PEMBDA:</td>
                <td class="text-center">{{ $totalPegawaiCount }} Orang</td>
                <td class="text-right nowrap">Rp&nbsp;{{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}</td>
                <td class="text-right nowrap">Rp&nbsp;{{ number_format($totalOpsPerguruanPeriod, 0, ',', '.') }}</td>
            </tr>
            <tr style="background-color:#000;color:#fff;font-weight:bold;">
                <td colspan="4" class="text-right" style="font-size:10px;padding:6px;">GRAND TOTAL BELANJA PERGURAN (SDM + OPERASIONAL):</td>
                <td class="text-right nowrap" style="font-size:10px;padding:6px;color:#fbbf24;">Rp&nbsp;{{ number_format($grandTotalBelanjaPeriod, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- RINCIAN PER UNIT SEKOLAH --}}
    @foreach($schoolExpenseData as $sId => $sData)
        <div class="section-title">RINCIAN BELANJA UNIT: {{ strtoupper($sData['school_name']) }}</div>
        <table class="rapby-table">
            <thead>
                <tr>
                    <th width="6%" class="text-center">Kode</th>
                    <th width="44%">Pos Rekening Belanja</th>
                    <th width="8%" class="text-center">Volume</th>
                    <th width="10%" class="text-center">Satuan</th>
                    <th width="16%" class="text-right nowrap">Tarif Satuan (Rp)</th>
                    <th width="16%" class="text-right nowrap">Total ({{ $periodMode === 'annual' ? '12 Bln' : '1 Bln' }})</th>
                </tr>
            </thead>
            <tbody>
                {{-- BELANJA PEGAWAI UNIT --}}
                <tr class="subtotal-row">
                    <td class="text-center mono">{{ $sData['salary_item']['code'] }}</td>
                    <td style="font-weight:bold;">{{ $sData['salary_item']['name'] }}</td>
                    <td class="text-center">{{ $sData['salary_item']['volume'] }}</td>
                    <td class="text-center">{{ $sData['salary_item']['unit'] }}</td>
                    <td class="text-right nowrap">Rp&nbsp;{{ number_format($sData['salary_item']['tariff'], 0, ',', '.') }}</td>
                    <td class="text-right nowrap" style="font-weight:bold;">Rp&nbsp;{{ number_format($sData['total_salary_period'], 0, ',', '.') }}</td>
                </tr>

                {{-- REKENING OPERASIONAL UNIT --}}
                @foreach($sData['ops_details'] as $code => $item)
                    @if($item['amount'] > 0 || $item['tariff'] > 0)
                        <tr>
                            <td class="text-center mono">{{ $item['code'] }}</td>
                            <td>{{ $item['name'] }}</td>
                            <td class="text-center">{{ $item['volume'] }}</td>
                            <td class="text-center">{{ $item['unit'] }}</td>
                            <td class="text-right nowrap">Rp&nbsp;{{ number_format($item['tariff'], 0, ',', '.') }}</td>
                            <td class="text-right nowrap">Rp&nbsp;{{ number_format($item['amount'] * $multiplier, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color:#e2e8f0;font-weight:bold;">
                    <td colspan="5" class="text-right">SUBTOTAL BELANJA {{ strtoupper($sData['school_name']) }}:</td>
                    <td class="text-right nowrap">Rp&nbsp;{{ number_format($sData['grand_total_period'], 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    @endforeach

    {{-- TANDA TANGAN --}}
    <table class="sig-table">
        <tr>
            <td width="33%">
                Mengetahui,<br>
                <strong>Ketua Yayasan</strong>
                <div class="sig-line">( ____________________ )</div>
            </td>
            <td width="33%">
                Diverifikasi,<br>
                <strong>Bendahara Yayasan</strong>
                <div class="sig-line">( ____________________ )</div>
            </td>
            <td width="33%">
                Gunungsitoli, {{ date('d F Y') }}<br>
                <strong>Sekretaris / Operator</strong>
                <div class="sig-line">( ____________________ )</div>
            </td>
        </tr>
    </table>
</body>
</html>
