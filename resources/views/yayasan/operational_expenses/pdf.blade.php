<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>RAPBY — Rencana Anggaran Belanja Yayasan & Perguruan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
            color: #1a1a2e;
            line-height: 1.5;
            padding: 20px 25px;
        }

        /* Header */
        .doc-header {
            text-align: center;
            border-bottom: 3px solid #4c1d95;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .doc-header h1 {
            font-size: 14px;
            color: #4c1d95;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }
        .doc-header h2 {
            font-size: 11px;
            color: #1e1b4b;
            margin-bottom: 4px;
        }
        .doc-header .meta {
            font-size: 8px;
            color: #6b7280;
        }

        /* Info table */
        .info-row {
            width: 100%;
            margin-bottom: 10px;
            font-size: 8.5px;
        }
        .info-row td { padding: 1.5px 0; }
        .info-row .label { font-weight: bold; color: #374151; width: 15%; }
        .info-row .value { color: #4b5563; width: 35%; }

        /* Main table */
        .rapby-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .rapby-table th {
            background-color: #312e81;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5px;
            padding: 5px 5px;
            border: 1px solid #312e81;
            letter-spacing: 0.3px;
        }
        .rapby-table td {
            padding: 4px 5px;
            border: 1px solid #e5e7eb;
            font-size: 8.5px;
        }

        /* Group headers */
        .group-pegawai {
            background-color: #1e3a8a !important;
            color: #ffffff !important;
            font-weight: bold;
        }
        .group-pegawai td { border-color: #1e3a8a; color: #ffffff; }

        .group-ops {
            background-color: #78350f !important;
            color: #ffffff !important;
            font-weight: bold;
        }
        .group-ops td { border-color: #78350f; color: #ffffff; }

        /* Unit headers */
        .unit-header-row {
            background-color: #eff6ff !important;
            font-weight: bold;
        }
        .unit-header-row td { border-color: #bfdbfe; color: #1e3a8a; }
        .unit-header-yayasan {
            background-color: #f5f3ff !important;
        }
        .unit-header-yayasan td { border-color: #c4b5fd; color: #4c1d95; }

        /* Item rows */
        .item-row td { font-size: 8px; }
        .item-row .indent { padding-left: 15px; }

        /* Subtotals */
        .subtotal-row {
            background-color: #eef2ff !important;
            font-weight: bold;
        }
        .subtotal-row td { border-color: #c7d2fe; }

        .subtotal-ops {
            background-color: #fffbeb !important;
            font-weight: bold;
        }
        .subtotal-ops td { border-color: #fde68a; }

        /* Grand total */
        .grand-total {
            background-color: #f3e8ff !important;
            font-weight: bold;
        }
        .grand-total td { 
            border-color: #c4b5fd;
            font-size: 9.5px;
            color: #4c1d95;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .mono { font-family: 'Courier New', monospace; }

        /* Signature */
        .sig-table {
            width: 100%;
            margin-top: 30px;
        }
        .sig-table td {
            text-align: center;
            vertical-align: top;
            font-size: 8.5px;
            padding: 3px 0;
        }
        .sig-line { margin-top: 50px; }

        @page { margin: 15mm 12mm; }
    </style>
</head>
<body>

    {{-- Document Header --}}
    <div class="doc-header">
        <h1>Yayasan Perguruan Pembda</h1>
        <h2>Rencana Anggaran Belanja Yayasan & Perguruan (RAPBY)</h2>
        <p class="meta">
            Tahun Pelajaran: {{ $currentYear->year ?? '-' }} &bull; 
            Periode: {{ $periodMode === 'annual' ? 'Tahunan (12 Bulan)' : 'Bulanan (1 Bulan)' }} &bull;
            Dicetak: {{ date('d F Y, H:i') }} WIB
        </p>
    </div>

    {{-- Info Row --}}
    <table class="info-row">
        <tr>
            <td class="label">Total Pegawai:</td>
            <td class="value">{{ $totalPegawaiCount }} orang di {{ count($hierarchicalSalaryData) }} unit</td>
            <td class="label">Dibuat Oleh:</td>
            <td class="value">Sistem Informasi PembdaHUB</td>
        </tr>
    </table>

    {{-- Main RAPBY Table --}}
    <table class="rapby-table">
        <thead>
            <tr>
                <th width="4%" class="text-center">No</th>
                <th width="10%">Kode Rek.</th>
                <th width="36%">Nama & Rincian Rekening Belanja</th>
                <th width="7%" class="text-center">Jumlah</th>
                <th width="8%" class="text-center">Satuan</th>
                <th width="15%" class="text-right">Tarif Satuan (Rp)</th>
                <th width="20%" class="text-right">Total Anggaran ({{ $periodMode === 'annual' ? '12 Bln' : '1 Bln' }})</th>
            </tr>
        </thead>
        <tbody>
            {{-- KELOMPOK 5.1.00 BELANJA PEGAWAI --}}
            <tr class="group-pegawai">
                <td class="text-center">5.1.00</td>
                <td colspan="5" style="text-transform: uppercase; font-size: 8px; letter-spacing: 0.5px;">
                    ■ KELOMPOK REKENING: BELANJA PEGAWAI PERGURUAN (OTOMATIS PENUGASAN)
                </td>
                <td class="text-right" style="font-size: 9px;">
                    Rp {{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}
                </td>
            </tr>

            @foreach($hierarchicalSalaryData as $uIdx => $uData)
                {{-- Unit Header --}}
                <tr class="{{ $uData['school_type'] === 'yayasan' ? 'unit-header-yayasan' : 'unit-header-row' }}">
                    <td class="text-center text-bold">{{ $uIdx + 1 }}</td>
                    <td class="mono text-center"></td>
                    <td colspan="4" class="text-bold" style="text-transform: uppercase; font-size: 8.5px;">
                        ▸ {{ $uData['school_name'] }} ({{ $uData['employee_count'] }} Pegawai)
                    </td>
                    <td class="text-right text-bold" style="font-size: 9px;">
                        Rp {{ number_format($uData['total_period'], 0, ',', '.') }}
                    </td>
                </tr>

                {{-- Item rows --}}
                @foreach($uData['items'] as $iIdx => $item)
                    @php
                        $amtPeriod = $item['amount'] * $multiplier;
                    @endphp
                    <tr class="item-row">
                        <td class="text-center" style="color: #9ca3af; font-size: 7.5px;">{{ $uIdx + 1 }}.{{ $iIdx + 1 }}</td>
                        <td class="mono text-center" style="font-size: 7.5px; color: #3b82f6;">{{ $item['code'] }}</td>
                        <td class="indent">{{ $item['name'] }}</td>
                        <td class="text-center text-bold">{{ number_format($item['volume'], 0, ',', '.') }}</td>
                        <td class="text-center">{{ $item['unit'] }}</td>
                        <td class="text-right">Rp {{ number_format($item['tariff'], 0, ',', '.') }}</td>
                        <td class="text-right text-bold">Rp {{ number_format($amtPeriod, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endforeach

            {{-- Subtotal Belanja Pegawai --}}
            <tr class="subtotal-row">
                <td colspan="6" class="text-right" style="font-size: 8px; text-transform: uppercase; letter-spacing: 0.3px;">
                    Subtotal Kelompok Belanja Pegawai (5.1.00):
                </td>
                <td class="text-right" style="font-size: 9.5px; color: #1e3a8a;">
                    Rp {{ number_format($totalGajiPerguruanPeriod, 0, ',', '.') }}
                </td>
            </tr>

            {{-- KELOMPOK 5.1.01+ BELANJA OPERASIONAL --}}
            <tr class="group-ops">
                <td class="text-center">5.1.01+</td>
                <td colspan="5" style="text-transform: uppercase; font-size: 8px; letter-spacing: 0.5px;">
                    ■ KELOMPOK REKENING: BELANJA OPERASIONAL NON-GAJI
                </td>
                <td class="text-right" style="font-size: 9px;">
                    Rp {{ number_format($totalOpsPeriod, 0, ',', '.') }}
                </td>
            </tr>

            @php $opsNo = 1; @endphp
            @foreach($parsedExpenseDetails as $code => $detail)
                @if(!$detail['is_automatic'])
                    @php
                        $vol = $detail['volume'] ?? 1;
                        $unit = $detail['unit'] ?? 'Paket';
                        $tariff = $detail['tariff'] ?? 0;
                        $amtPeriod = ($detail['amount'] ?? 0) * $multiplier;
                    @endphp
                    <tr style="{{ $opsNo % 2 === 0 ? 'background-color: #fafaf9;' : '' }}">
                        <td class="text-center">{{ $opsNo++ }}</td>
                        <td class="mono text-center text-bold" style="color: #92400e;">{{ $code }}</td>
                        <td class="text-bold">{{ $detail['name'] }}</td>
                        <td class="text-center text-bold">{{ $vol }}</td>
                        <td class="text-center">{{ $unit }}</td>
                        <td class="text-right">Rp {{ number_format($tariff, 0, ',', '.') }}</td>
                        <td class="text-right text-bold">Rp {{ number_format($amtPeriod, 0, ',', '.') }}</td>
                    </tr>
                @endif
            @endforeach

            {{-- Subtotal Belanja Operasional --}}
            <tr class="subtotal-ops">
                <td colspan="6" class="text-right" style="font-size: 8px; text-transform: uppercase; letter-spacing: 0.3px;">
                    Subtotal Kelompok Belanja Operasional (5.1.01–14):
                </td>
                <td class="text-right" style="font-size: 9.5px; color: #78350f;">
                    Rp {{ number_format($totalOpsPeriod, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="grand-total">
                <td colspan="6" class="text-right" style="text-transform: uppercase; letter-spacing: 0.5px;">
                    Grand Total Rencana Belanja Yayasan & Perguruan (RAPBY):
                </td>
                <td class="text-right" style="font-size: 11px;">
                    Rp {{ number_format($grandTotalBelanjaPeriod, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- Signature --}}
    <table class="sig-table">
        <tr>
            <td width="50%">
                <p>Mengetahui,</p>
                <p><strong>Bendahara Yayasan</strong></p>
                <p class="sig-line"><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></p>
            </td>
            <td width="50%">
                <p>Gunungsitoli, {{ date('d F Y') }}</p>
                <p><strong>Ketua Yayasan Perguruan Pembda</strong></p>
                <p class="sig-line"><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></p>
            </td>
        </tr>
    </table>

</body>
</html>
