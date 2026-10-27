<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Inventaris Aset PembdaHUB</title>
    <style>
        @page {
            margin: 1.2cm 1cm 1.5cm 1cm;
            size: a4 landscape;
        }
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 8pt;
            color: #111827;
            line-height: 1.2;
        }
        .header-table {
            width: 100%;
            border-bottom: 2.5px solid #000000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-title {
            text-align: center;
        }
        .header-title h1 {
            font-size: 13pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-title h2 {
            font-size: 11pt;
            font-weight: bold;
            margin: 2px 0;
            text-transform: uppercase;
            color: #1f2937;
        }
        .header-title p {
            font-size: 7.5pt;
            margin: 0;
            color: #4b5563;
        }
        .doc-title {
            text-align: center;
            margin: 10px 0;
        }
        .doc-title h3 {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 3px 0;
            text-decoration: underline;
        }
        .doc-title .meta {
            font-size: 8pt;
            color: #374151;
        }

        .summary-box {
            width: 100%;
            margin-bottom: 12px;
            border: 1px solid #000;
            background-color: #f9fafb;
            padding: 6px 10px;
        }
        .summary-box table {
            width: 100%;
            font-size: 8pt;
        }
        .summary-box td {
            padding: 2px 5px;
        }
        .summary-box .val {
            font-weight: bold;
            color: #111827;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
        }
        .data-table th, .data-table td {
            border: 0.8px solid #374151;
            padding: 4px 5px;
            vertical-align: middle;
        }
        .data-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            font-size: 6.8pt;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .num-col {
            text-align: right;
            white-space: nowrap;
        }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }

        .footer-sig {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .footer-sig td {
            text-align: center;
            width: 50%;
            font-size: 8.5pt;
        }
        .sig-space {
            height: 55px;
        }
    </style>
</head>
<body>

    {{-- KOP SURAT RESMI YAYASAN --}}
    <table class="header-table">
        <tr>
            <td class="header-title">
                <h1>YAYASAN PERGURUAN PEMBDA NIAS</h1>
                <h2>SISTEM MANAJEMEN ASET TERPADU (PembdaINVENTORY)</h2>
                <p>Menaungi: SMAS Pembda 1 Gunungsitoli • SMK Swasta Pembda Nias • SMPS Pembda 2 Gunungsitoli</p>
                <p>Alamat: Jl. K.H. Dewantara No. 1, Kota Gunungsitoli, Nias - Sumatera Utara</p>
            </td>
        </tr>
    </table>

    {{-- JUDUL LAPORAN --}}
    <div class="doc-title">
        <h3>LAPORAN BUKU INDUK INVENTARIS ASET & NILAI PENYUSUTAN</h3>
        <div class="meta">
            <b>Cakupan:</b> {{ $filterTitle }} &nbsp;|&nbsp; 
            <b>Tanggal Cetak:</b> {{ date('d F Y, H:i') }} WIB
        </div>
    </div>

    {{-- KOTAK RINGKASAN FINANSIAL --}}
    <div class="summary-box">
        <table>
            <tr>
                <td width="25%">Total Fisik Barang: <span class="val">{{ number_format($totalItems, 0, ',', '.') }} Unit/Pcs</span></td>
                <td width="25%">Total Nilai Perolehan: <span class="val">Rp {{ number_format($totalCost, 0, ',', '.') }}</span></td>
                <td width="25%">Akumulasi Penyusutan: <span class="val" style="color: #b91c1c;">Rp {{ number_format($totalDep, 0, ',', '.') }}</span></td>
                <td width="25%">Total Nilai Buku Bersih: <span class="val" style="color: #047857;">Rp {{ number_format($totalBook, 0, ',', '.') }}</span></td>
            </tr>
        </table>
    </div>

    {{-- TABEL DATA ASET LENGKAP --}}
    <table class="data-table">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="9%">Kode Aset</th>
                <th width="18%">Nama Aset & Spesifikasi</th>
                <th width="12%">Unit Pemilik</th>
                <th width="10%">Kategori</th>
                <th width="10%">Lokasi Ruangan</th>
                <th width="4%">Thn</th>
                <th width="5%">Jumlah</th>
                <th width="9%">Harga Pengadaan</th>
                <th width="8%">Akumulasi Depr.</th>
                <th width="8%">Nilai Buku</th>
                <th width="4%">Kondisi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assets as $index => $asset)
            <tr>
                <td class="text-center text-bold">{{ $index + 1 }}</td>
                <td class="text-bold" style="font-family: monospace;">{{ $asset->asset_code }}</td>
                <td>
                    <b>{{ $asset->name }}</b>
                    @if($asset->brand)<br><span style="color:#64748b;">Merk: {{ $asset->brand }}</span>@endif
                    @if($asset->serial_number)<br><span style="color:#64748b;">SN: {{ $asset->serial_number }}</span>@endif
                </td>
                <td>{{ $asset->unit_name }}</td>
                <td>{{ $asset->category ? $asset->category->name : '-' }}</td>
                <td>{{ $asset->location ? $asset->location->name : 'Gudang Pusat' }}</td>
                <td class="text-center">{{ $asset->acquisition_year }}</td>
                <td class="text-center text-bold">{{ $asset->quantity }} {{ $asset->unit }}</td>
                <td class="num-col text-bold">Rp {{ number_format($asset->acquisition_price, 0, ',', '.') }}</td>
                <td class="num-col" style="color: #b91c1c;">Rp {{ number_format($asset->accumulated_depreciation, 0, ',', '.') }}</td>
                <td class="num-col text-bold" style="color: #047857;">Rp {{ number_format($asset->current_book_value, 0, ',', '.') }}</td>
                <td class="text-center text-bold">
                    {{ strtoupper($asset->condition === 'rusak_ringan' ? 'RR' : ($asset->condition === 'rusak_berat' ? 'RB' : $asset->condition)) }}
                </td>
            </tr>
            @endforeach
            {{-- BARIS TOTAL --}}
            <tr style="background-color: #e2e8f0; font-weight: bold;">
                <td colspan="7" class="text-center" style="font-size: 7.5pt;">TOTAL KESELURUHAN ({{ $assets->count() }} Data Aset Tercatat)</td>
                <td class="text-center" style="font-size: 7.5pt;">{{ number_format($totalItems, 0, ',', '.') }}</td>
                <td class="num-col" style="font-size: 7.5pt;">Rp {{ number_format($totalCost, 0, ',', '.') }}</td>
                <td class="num-col" style="font-size: 7.5pt; color: #b91c1c;">Rp {{ number_format($totalDep, 0, ',', '.') }}</td>
                <td class="num-col" style="font-size: 7.5pt; color: #047857;">Rp {{ number_format($totalBook, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    {{-- TANDA TANGAN RESMI --}}
    <table class="footer-sig">
        <tr>
            <td>
                Mengetahui,<br>
                <b>Ketua Yayasan Perguruan Pembda Nias</b>
                <div class="sig-space"></div>
                <b><u>Drs. Yulianus Zega</u></b><br>
                <span>Ketua Pengurus Yayasan</span>
            </td>
            <td>
                Gunungsitoli, {{ date('d F Y') }}<br>
                <b>Pengelola / Petugas Aset Inventaris</b>
                <div class="sig-space"></div>
                <b><u>{{ auth()->user()->name }}</u></b><br>
                <span>NIP / ID: {{ auth()->user()->username }}</span>
            </td>
        </tr>
    </table>

</body>
</html>
