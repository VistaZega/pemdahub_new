<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>RAPBY — Rencana Anggaran Belanja Yayasan & Perguruan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; color: #000; line-height: 1.5; padding: 20px 25px; }
        .doc-header { text-align: center; border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
        .doc-header h1 { font-size: 14px; color: #000; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px; font-weight: bold; }
        .doc-header h2 { font-size: 11px; color: #000; margin-bottom: 4px; font-weight: bold; }
        .doc-header .meta { font-size: 8px; color: #000; font-weight: bold; }
        .info-row { width: 100%; margin-bottom: 10px; font-size: 8.5px; }
        .info-row td { padding: 1.5px 0; font-weight: bold; }
        .rapby-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .rapby-table th { background-color: #000; color: #fff; font-weight: bold; text-transform: uppercase; font-size: 7.5px; padding: 5px; border: 1px solid #000; white-space: nowrap; }
        .rapby-table td { padding: 4px 5px; border: 1px solid #000; font-size: 8.5px; font-weight: bold; }
        .group-pegawai { background-color: #1e3a8a !important; color: #fff !important; font-weight: bold; }
        .group-pegawai td { border-color: #000; color: #fff; }
        .group-ops { background-color: #78350f !important; color: #fff !important; font-weight: bold; }
        .group-ops td { border-color: #000; color: #fff; }
        .subtotal-row { background-color: #bfdbfe !important; font-weight: bold; color: #000 !important; }
        .subtotal-ops { background-color: #fde68a !important; font-weight: bold; color: #000 !important; }
        .grand-total { background-color: #000 !important; color: #fff !important; font-weight: bold; }
        .grand-total td { border-color: #000; font-size: 9.5px; color: #fff; white-space: nowrap; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .nowrap { white-space: nowrap !important; }
        .mono { font-family: 'Courier New', monospace; }
        .sig-table { width: 100%; margin-top: 30px; }
        .sig-table td { text-align: center; vertical-align: top; font-size: 8.5px; padding: 3px 0; font-weight: bold; }
        .sig-line { margin-top: 50px; }
        @page { margin: 15mm 12mm; }
    </style>
</head>
<body>
    <div class="doc-header">
        <h1>Yayasan Perguruan Pembda</h1>
        <h2>Rencana Anggaran Belanja Yayasan & Perguruan (RAPBY)</h2>
        <p class="meta">Tahun Pelajaran: {{ $currentYear->year ?? '-' }} &bull; Periode: {{ $periodMode === 'annual' ? 'Tahunan (12 Bulan)' : 'Bulanan (1 Bulan)' }} &bull; Dicetak: {{ date('d F Y, H:i') }} WIB</p>
    </div>

    <table class="info-row">
        <tr>
            <td style="width:15%;font-weight:bold;">Total Pegawai:</td>
            <td style="width:35%;">{{ $totalPegawaiCount }} orang di {{ count($hierarchicalSalaryData) }} unit</td>
            <td style="width:15%;font-weight:bold;">Dibuat Oleh:</td>
            <td style="width:35%;">Sistem Informasi PembdaHUB</td>
        </tr>
    </table>

    <table class="rapby-table">
        <thead>
            <tr>
                <th width="4%" class="text-center">No</th>
                <th width="10%">Kode Rek.</th>
                <th width="36%">Nama Rekening Belanja</th>
                <th width="7%" class="text-center">Jumlah</th>
                <th width="8%" class="text-center">Satuan</th>
                <th width="15%" class="text-right nowrap">Tarif Satuan (Rp)</th>
                <th width="20%" class="text-right nowrap">Total ({{ $periodMode === 'annual' ? '12 Bln' : '1 Bln' }})</th>
            </tr>
        </thead>
        <tbody>
            <tr class="group-pegawai">
                <td class="text-center">5.1.00</td>
                <td colspan="5" style="text-transform:uppercase;font-size:8px;">■ KELOMPOK: BELANJA PEGAWAI PERGURUAN (OTOMATIS PENUGASAN)</td>
                <td class="text-right nowrap" style="font-size:9px;">Rp&nbsp;{{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}</td>
            </tr>

            @foreach($hierarchicalSalaryData as $uIdx => $uData)
                @php $item = $uData['items'][0] ?? null; @endphp
                @if($item)
                <tr style="{{ $uData['school_type'] === 'yayasan' ? 'background-color:#e9d5ff;' : ($uIdx % 2 === 1 ? 'background-color:#f1f5f9;' : '') }}">
                    <td class="text-center" style="font-weight:bold;">{{ $uIdx + 1 }}</td>
                    <td class="mono text-center" style="font-weight:bold;color:#000;">{{ $item['code'] }}</td>
                    <td style="font-weight:bold;">{{ $item['name'] }}</td>
                    <td class="text-center" style="font-weight:bold;">{{ $item['volume'] }}</td>
                    <td class="text-center">{{ $item['unit'] }}</td>
                    <td class="text-right nowrap">Rp&nbsp;{{ number_format($item['tariff'], 0, ',', '.') }}</td>
                    <td class="text-right nowrap" style="font-weight:bold;color:#000;">Rp&nbsp;{{ number_format($uData['total_period'], 0, ',', '.') }}</td>
                </tr>
                @endif
            @endforeach

            <tr class="subtotal-row">
                <td colspan="6" class="text-right" style="font-size:8px;text-transform:uppercase;">Subtotal Belanja Pegawai (5.1.00):</td>
                <td class="text-right nowrap" style="font-size:9.5px;color:#000;">Rp&nbsp;{{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}</td>
            </tr>

            <tr class="group-ops">
                <td class="text-center">5.1.01+</td>
                <td colspan="5" style="text-transform:uppercase;font-size:8px;">■ KELOMPOK: BELANJA OPERASIONAL NON-GAJI</td>
                <td class="text-right nowrap" style="font-size:9px;">Rp&nbsp;{{ number_format($totalOpsPeriod, 0, ',', '.') }}</td>
            </tr>

            @php $opsNo = 1; @endphp
            @foreach($parsedExpenseDetails as $code => $detail)
                @if(!$detail['is_automatic'])
                    @php
                        $vol = $detail['volume'] ?? 1;
                        $tariff = $detail['tariff'] ?? 0;
                        $amtPeriod = ($detail['amount'] ?? 0) * $multiplier;
                    @endphp
                    <tr style="{{ $opsNo % 2 === 0 ? 'background-color:#fef3c7;' : '' }}">
                        <td class="text-center">{{ $opsNo++ }}</td>
                        <td class="mono text-center" style="font-weight:bold;color:#000;">{{ $code }}</td>
                        <td style="font-weight:bold;">{{ $detail['name'] }}</td>
                        <td class="text-center" style="font-weight:bold;">{{ $vol }}</td>
                        <td class="text-center">{{ $detail['unit'] ?? 'Paket' }}</td>
                        <td class="text-right nowrap">Rp&nbsp;{{ number_format($tariff, 0, ',', '.') }}</td>
                        <td class="text-right nowrap" style="font-weight:bold;">Rp&nbsp;{{ number_format($amtPeriod, 0, ',', '.') }}</td>
                    </tr>
                @endif
            @endforeach

            <tr class="subtotal-ops">
                <td colspan="6" class="text-right" style="font-size:8px;text-transform:uppercase;">Subtotal Belanja Operasional (5.1.01–15):</td>
                <td class="text-right nowrap" style="font-size:9.5px;color:#000;">Rp&nbsp;{{ number_format($totalOpsPeriod, 0, ',', '.') }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="grand-total">
                <td colspan="6" class="text-right" style="text-transform:uppercase;letter-spacing:0.5px;">Grand Total Rencana Belanja RAPBY:</td>
                <td class="text-right nowrap" style="font-size:11px;color:#fff;">Rp&nbsp;{{ number_format($grandTotalBelanjaPeriod, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="sig-table">
        <tr>
            <td width="50%"><p>Mengetahui,</p><p><strong>Bendahara Yayasan</strong></p><p class="sig-line"><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></p></td>
            <td width="50%"><p>Gunungsitoli, {{ date('d F Y') }}</p><p><strong>Ketua Yayasan Perguruan Pembda</strong></p><p class="sig-line"><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></p></td>
        </tr>
    </table>
</body>
</html>
