<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Progress Input Data TP. {{ $currentYear->year ?? '2026/2027' }}</title>
    <style>
        @page {
            margin: 1cm 1.2cm 1cm 1.2cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1f2937;
            line-height: 1.35;
            font-size: 10px;
        }
        .header-container {
            border-bottom: 3px solid #6d28d9;
            padding-bottom: 10px;
            margin-bottom: 15px;
            width: 100%;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .logo-title {
            font-size: 16px;
            font-weight: bold;
            color: #4c1d95;
            margin: 0;
            text-transform: uppercase;
        }
        .logo-subtitle {
            font-size: 11px;
            color: #4b5563;
            margin: 3px 0 0 0;
            font-weight: bold;
        }
        .header-meta {
            text-align: right;
            font-size: 10px;
            color: #4b5563;
        }
        .header-meta strong {
            color: #111827;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table thead {
            display: table-header-group;
        }
        table.data-table tr {
            page-break-inside: avoid;
        }
        table.data-table th {
            background-color: #4c1d95;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #3b0764;
            font-size: 9.5px;
            text-transform: uppercase;
        }
        table.data-table td {
            padding: 6px 8px;
            border: 1px solid #d1d5db;
            vertical-align: top;
            font-size: 9.5px;
        }
        .bg-group {
            background-color: #f8fafc;
        }
        .item-number {
            display: inline-block;
            background-color: #6d28d9;
            color: white;
            font-weight: bold;
            border-radius: 3px;
            padding: 1px 5px;
            font-size: 9px;
            margin-right: 4px;
        }
        .item-title {
            font-weight: bold;
            color: #111827;
            font-size: 10.5px;
            margin-bottom: 2px;
        }
        .item-desc {
            color: #6b7280;
            font-size: 8.5px;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9px;
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
        .unit-badge {
            font-weight: bold;
            color: #111827;
        }
        .satuan-box {
            text-align: center;
            font-weight: bold;
            color: #374151;
        }
        .detail-box {
            margin-top: 4px;
            padding-top: 4px;
            border-top: 1px dashed #e5e7eb;
        }
        .detail-title {
            font-size: 8px;
            font-weight: bold;
            color: #6d28d9;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .detail-item {
            font-size: 8.5px;
            color: #374151;
            margin-bottom: 1px;
        }
        .footer-signature {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .signature-box {
            float: right;
            width: 240px;
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

<table class="data-table">
    <thead>
        <tr>
            <th style="width: 25%;">Indikator Item</th>
            <th style="width: 15%;">Unit Sekolah</th>
            <th style="width: 16%; text-align: center;">Perkembangan</th>
            <th style="width: 9%; text-align: center;">Satuan</th>
            <th style="width: 35%;">Rekomendasi & Rincian Detail Terinput</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            @php
                $schoolsCount = count($item['schools_data']);
            @endphp
            @foreach($item['schools_data'] as $idx => $s)
                <tr>
                    @if($idx === 0)
                        <td rowspan="{{ $schoolsCount }}" class="bg-group">
                            <div class="item-title">
                                <span class="item-number">{{ $item['number'] }}</span>
                                {{ $item['title'] }}
                            </div>
                            <div class="item-desc">{{ $item['description'] }}</div>
                        </td>
                    @endif

                    <td class="unit-badge">
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
                        <div style="font-weight: bold; color: #111827;">{{ $s['rekomendasi'] }}</div>
                        @if(!empty($s['details']))
                            <div class="detail-box">
                                <div class="detail-title">Rincian Data Terinput:</div>
                                @foreach($s['details'] as $dt)
                                    <div class="detail-item">• {{ $dt }}</div>
                                @endforeach
                            </div>
                        @endif
                        @if(!empty($s['action_items']))
                            <div class="detail-box" style="border-top: 1px dashed #fca5a5; margin-top: 4px; padding-top: 4px;">
                                <div class="detail-title" style="color: #dc2626;">Action Items Perlu Dilengkapi:</div>
                                @foreach($s['action_items'] as $act)
                                    <div class="detail-item" style="color: #991b1b; font-weight: bold;">⚠️ {{ $act }}</div>
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
        <p style="margin: 4px 0 50px 0; font-weight: bold;">Ketua Yayasan PEMBDA,</p>
        <p style="margin: 0; font-weight: bold; text-decoration: underline;">Yulianus Zega</p>
    </div>
    <div class="clear"></div>
</div>

</body>
</html>
