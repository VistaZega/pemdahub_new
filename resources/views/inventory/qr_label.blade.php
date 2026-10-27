<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Aset - {{ $asset->asset_code }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700;800;900&family=JetBrains+Mono:wght@700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .controls {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .btn {
            background-color: #4f46e5;
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 800;
            font-size: 13px;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
            text-decoration: none;
        }

        .btn-outline {
            background-color: white;
            color: #334155;
            border: 2px solid #cbd5e1;
            box-shadow: none;
        }

        /* LABEL STICKER CONTAINER (Ukuran Standar Label 9 x 5 cm) */
        .label-sticker {
            width: 90mm;
            height: 52mm;
            background: white;
            border: 2.5px solid #000000;
            border-radius: 4mm;
            padding: 3mm 4mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .label-header {
            border-bottom: 1.5px solid #000000;
            padding-bottom: 1.5mm;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-text h1 {
            font-size: 7.5pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #000000;
            line-height: 1.1;
        }

        .header-text h2 {
            font-size: 6pt;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            margin-top: 1px;
        }

        .tag-badge {
            background: #000000;
            color: white;
            font-size: 5.5pt;
            font-weight: 900;
            padding: 1px 4px;
            border-radius: 2px;
            text-transform: uppercase;
        }

        .label-body {
            display: flex;
            align-items: center;
            gap: 3mm;
            margin: 1.5mm 0;
            flex: 1;
        }

        .qr-wrapper {
            width: 25mm;
            height: 25mm;
            flex-shrink: 0;
            border: 1px solid #cbd5e1;
            padding: 1mm;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 2mm;
        }

        .qr-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .asset-info {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 1mm;
        }

        .code-box {
            font-family: 'JetBrains Mono', monospace;
            font-size: 9pt;
            font-weight: 900;
            color: #000000;
            background: #f1f5f9;
            padding: 1mm 2mm;
            border-radius: 1.5mm;
            border: 1px solid #000000;
            display: inline-block;
            line-height: 1;
            margin-bottom: 0.5mm;
        }

        .asset-name {
            font-size: 7.5pt;
            font-weight: 900;
            color: #000000;
            line-height: 1.15;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .asset-meta {
            font-size: 5.5pt;
            color: #334155;
            font-weight: 700;
            line-height: 1.2;
        }

        .asset-meta span {
            color: #000000;
            font-weight: 800;
        }

        .label-footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 1mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 4.8pt;
            color: #64748b;
            font-weight: 700;
        }

        .label-footer .warning {
            color: #b91c1c;
            font-weight: 800;
            text-transform: uppercase;
        }

        @media print {
            body {
                background: transparent;
                padding: 0;
                margin: 0;
                min-height: auto;
            }
            .controls {
                display: none;
            }
            .label-sticker {
                box-shadow: none;
                border: 2px solid #000;
                margin: 0;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="controls">
        <button onclick="window.print()" class="btn">
            🖨️ Cetak Stiker Sekarang
        </button>
        <button onclick="window.close()" class="btn btn-outline">
            Tutup
        </button>
    </div>

    <!-- LABEL FISIK STIKER ASET -->
    <div class="label-sticker">
        {{-- HEADER LABEL --}}
        <div class="label-header">
            <div class="header-text">
                <h1>YAYASAN PERGURUAN PEMBDA NIAS</h1>
                <h2>Sistem Informasi Aset Terpadu - PembdaINVENTORY</h2>
            </div>
            <div class="tag-badge">
                {{ $asset->school ? strtoupper($asset->school->type) : 'YAYASAN' }}
            </div>
        </div>

        {{-- BODY: QR CODE + KODE & NAMA ASET --}}
        <div class="label-body">
            <div class="qr-wrapper">
                @php
                    $assetUrl = route('inventory.show', $asset->id);
                    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($assetUrl);
                @endphp
                <img src="{{ $qrCodeUrl }}" alt="QR Code {{ $asset->asset_code }}" onerror="this.onerror=null; this.src='https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl={{ urlencode($assetUrl) }}';">
            </div>

            <div class="asset-info">
                <div>
                    <span class="code-box">{{ $asset->asset_code }}</span>
                </div>
                <div class="asset-name">
                    {{ $asset->name }}
                </div>
                <div class="asset-meta">
                    <div>Unit: <span>{{ $asset->unit_name }}</span></div>
                    <div>Lokasi: <span>{{ $asset->location ? $asset->location->name : 'Gudang Pusat' }}</span></div>
                    <div>Tahun: <span>{{ $asset->acquisition_year }}</span> | No Urut: <span>#{{ $asset->item_number }}</span></div>
                </div>
            </div>
        </div>

        {{-- FOOTER LABEL --}}
        <div class="label-footer">
            <span class="warning">Dilarang Merusak / Melepas Label Aset Ini</span>
            <span>Scan QR untuk verifikasi aset</span>
        </div>
    </div>

</body>
</html>
