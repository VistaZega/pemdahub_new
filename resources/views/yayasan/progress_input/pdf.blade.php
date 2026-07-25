<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Progress Input Data TP. {{ $currentYear->year ?? '2026/2027' }}</title>
    <style>
        @page {
            margin: 0.8cm 1cm 1cm 1cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1f2937;
            line-height: 1.35;
            font-size: 9.5px;
        }
        .header-container {
            border-bottom: 2.5px solid #6d28d9;
            padding-bottom: 8px;
            margin-bottom: 10px;
            width: 100%;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .logo-title {
            font-size: 15px;
            font-weight: bold;
            color: #4c1d95;
            margin: 0;
            text-transform: uppercase;
        }
        .logo-subtitle {
            font-size: 10.5px;
            color: #4b5563;
            margin: 2px 0 0 0;
            font-weight: bold;
        }
        .header-meta {
            text-align: right;
            font-size: 9.5px;
            color: #4b5563;
        }
        .header-meta strong {
            color: #111827;
        }

        /* Summary Box */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .summary-cell {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 6px;
            text-align: center;
            width: 25%;
        }
        .summary-val {
            font-size: 12px;
            font-weight: bold;
            color: #4c1d95;
        }
        .summary-lbl {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
        }

        /* Main Data Table */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            table-layout: fixed;
        }
        table.data-table thead {
            display: table-header-group;
        }
        table.data-table tr {
            page-break-inside: avoid;
        }
        table.data-table th {
            background-color: #3b0764;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 5px 8px;
            border: 1px solid #2e1065;
            font-size: 9px;
            text-transform: uppercase;
        }
        table.data-table td {
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
            font-size: 9px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* Group Header Row */
        tr.item-group-header td {
            background-color: #f1f5f9;
            border-top: 2px solid #6d28d9;
            border-bottom: 1px solid #cbd5e1;
            padding: 6px 8px;
        }
        .item-number {
            display: inline-block;
            background-color: #6d28d9;
            color: white;
            font-weight: bold;
            border-radius: 3px;
            padding: 1px 6px;
            font-size: 9px;
            margin-right: 5px;
        }
        .item-title {
            font-weight: bold;
            color: #1e1b4b;
            font-size: 10px;
        }
        .item-desc {
            color: #64748b;
            font-size: 8.5px;
            font-weight: normal;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8.5px;
        }
        .badge-green {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .badge-amber {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .badge-red {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .unit-name {
            font-weight: bold;
            color: #0f172a;
        }
        .satuan-box {
            text-align: center;
            font-weight: bold;
            color: #475569;
        }
        .detail-box {
            margin-top: 3px;
            padding-top: 3px;
            border-top: 1px dashed #e2e8f0;
        }
        .detail-title {
            font-size: 7.5px;
            font-weight: bold;
            color: #6d28d9;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .detail-item {
            font-size: 8.5px;
            color: #334155;
            margin-bottom: 1px;
        }
        .action-box {
            margin-top: 3px;
            padding-top: 3px;
            border-top: 1px dashed #fca5a5;
            background-color: #fff5f5;
            padding: 3px 5px;
            border-radius: 3px;
        }
        .action-title {
            font-size: 7.5px;
            font-weight: bold;
            color: #dc2626;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .action-item {
            font-size: 8px;
            color: #991b1b;
            font-weight: bold;
            margin-bottom: 1px;
        }
        .footer-signature {
            width: 100%;
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .signature-box {
            float: right;
            width: 220px;
            text-align: center;
        }
        .clear {
            clear: both;
        }
    </style>
</head>
<body>

<div class="header-container">
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <h1 class="logo-title">Yayasan Perguruan Pembangunan Daerah Nias</h1>
                <p class="logo-subtitle">Laporan Progress Input Data & Kesiapan Akademik (12 Indikator Seluruh Unit)</p>
            </td>
            <td class="header-meta">
                <p style="margin: 0;">Tahun Pelajaran: <strong>{{ $currentYear->year ?? '2026/2027' }}</strong></p>
                <p style="margin: 3px 0 0 0;">Tanggal Cetak: <strong>{{ now()->translatedFormat('d F Y, H:i') }}</strong></p>
            </td>
        </tr>
    </table>
</div>

@php
    $totalItemsCount = count($items);
    $totalSchoolDataCount = 0;
    $greenTotal = 0;
    $amberTotal = 0;
    $redTotal = 0;

    foreach($items as $it) {
        foreach($it['schools_data'] as $sc) {
            $totalSchoolDataCount++;
            if($sc['status_color'] === 'green') $greenTotal++;
            elseif($sc['status_color'] === 'amber') $amberTotal++;
            else $redTotal++;
        }
    }
    $readinessTotalPct = $totalSchoolDataCount > 0 ? round(($greenTotal / $totalSchoolDataCount) * 100, 1) : 0;
@endphp

<table class="summary-table">
    <tr>
        <td class="summary-cell">
            <div class="summary-lbl">Unit Sekolah</div>
            <div class="summary-val">{{ count($schools) }} Unit</div>
        </td>
        <td class="summary-cell">
            <div class="summary-lbl">Indikator Dipantau</div>
            <div class="summary-val">{{ $totalItemsCount }} Item</div>
        </td>
        <td class="summary-cell">
            <div class="summary-lbl">Tingkat Kesiapan</div>
            <div class="summary-val" style="color: #059669;">{{ $readinessTotalPct }}%</div>
        </td>
        <td class="summary-cell">
            <div class="summary-lbl">Perlu Follow-up</div>
            <div class="summary-val" style="color: #dc2626;">{{ $redTotal + $amberTotal }} Item</div>
        </td>
    </tr>
</table>

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 22%;">Unit Sekolah</th>
            <th style="width: 18%; text-align: center;">Perkembangan</th>
            <th style="width: 10%; text-align: center;">Satuan</th>
            <th style="width: 50%;">Rekomendasi & Rincian Detail Data Terinput</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr class="item-group-header">
                <td colspan="4">
                    <span class="item-number">{{ $item['number'] }}</span>
                    <span class="item-title">{{ $item['title'] }}</span>
                    <span class="item-desc"> — {{ $item['description'] }}</span>
                </td>
            </tr>
            @foreach($item['schools_data'] as $s)
                <tr>
                    <td class="unit-name">
                        • {{ $s['school_name'] }}
                    </td>
                    <td style="text-align: center;">
                        @if($s['status_color'] === 'green')
                            <span class="badge badge-green">{{ $s['perkembangan'] }}</span>
                        @elseif($s['status_color'] === 'amber')
                            <span class="badge badge-amber">{{ $s['perkembangan'] }}</span>
                        @else
                            <span class="badge badge-red">{{ $s['perkembangan'] }}</span>
                        @endif
                    </td>
                    <td class="satuan-box">
                        {{ $s['satuan'] }}
                    </td>
                    <td>
                        <div style="font-weight: bold; color: #0f172a;">{{ $s['rekomendasi'] }}</div>
                        @if(!empty($s['details']))
                            <div class="detail-box">
                                <div class="detail-title">Rincian Data Terinput:</div>
                                @foreach($s['details'] as $dt)
                                    <div class="detail-item">• {{ $dt }}</div>
                                @endforeach
                            </div>
                        @endif
                        @if(!empty($s['action_items']))
                            <div class="action-box">
                                <div class="action-title">Action Items Perlu Dilengkapi:</div>
                                @foreach($s['action_items'] as $act)
                                    <div class="action-item">⚠️ {{ $act }}</div>
                                @endforeach
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>

<div class="footer-signature">
    <div class="signature-box">
        <p style="margin: 0;">Gunungsitoli, {{ now()->translatedFormat('d F Y') }}</p>
        <p style="margin: 4px 0 45px 0; font-weight: bold;">Ketua Yayasan PEMBDA,</p>
        <p style="margin: 0; font-weight: bold; text-decoration: underline;">Yulianus Zega</p>
    </div>
    <div class="clear"></div>
</div>

</body>
</html>

