<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Pemilihan OSIS - {{ $election->school->name ?? '' }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 20px;
            font-size: 12pt;
            line-height: 1.4;
        }
        .kop {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .kop h2 { margin: 0; font-size: 15pt; text-transform: uppercase; font-weight: bold; }
        .kop h1 { margin: 2px 0; font-size: 18pt; text-transform: uppercase; font-weight: bold; }
        .kop p { margin: 0; font-size: 10pt; }
        .title { text-align: center; margin-bottom: 25px; }
        .title h3 { margin: 0; font-size: 14pt; text-decoration: underline; text-transform: uppercase; }
        .title span { font-size: 11pt; }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table.data th, table.data td {
            border: 1px solid #000;
            padding: 6px 10px;
            font-size: 11pt;
        }
        table.data th { background-color: #f2f2f2; text-align: center; }
        .ttd-box {
            margin-top: 40px;
            width: 100%;
            display: flex;
            justify-content: space-between;
        }
        .ttd-col {
            width: 45%;
            text-align: center;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 10px 20px; font-weight: bold; background: #047857; color: #fff; border: none; border-radius: 6px; cursor: pointer;">
            🖨️ Cetak Dokumen / Simpan PDF
        </button>
    </div>

    {{-- KOP SURAT --}}
    <div class="kop">
        <h2>YAYASAN PERGURUAN PEMDA NIAS</h2>
        <h1>{{ strtoupper($election->school->name ?? 'SMK SWASTA PEMDA NIAS') }}</h1>
        <p>{{ $election->school->address ?? 'Gunungsitoli, Sumatera Utara' }}</p>
    </div>

    {{-- JUDUL BERITA ACARA --}}
    <div class="title">
        <h3>BERITA ACARA REKAPITULASI HASIL PEMILIHAN PENGURUS OSIS</h3>
        <span>Nomor: BA.OSIS/{{ date('Y') }}/{{ str_pad($election->id, 3, '0', STR_PAD_LEFT) }}</span>
    </div>

    <p>
        Pada hari ini, <strong>{{ now()->translatedFormat('l, d F Y') }}</strong>, telah dilaksanakan rekapitulasi dan pengesahan hasil pemungutan suara Pemilihan Pengurus Organisasi Siswa Intra Sekolah (OSIS) <strong>{{ $election->school->name }}</strong> Periode <strong>{{ $election->period }}</strong> melalui sistem digital E-Voting PembdaHUB.
    </p>

    <h4 style="margin-bottom: 5px;">I. DATA PARTISIPASI PEMILIH (DPT)</h4>
    <table style="width: 100%; margin-bottom: 15px;">
        <tr>
            <td style="width: 35%;">1. Jumlah Pemilih Terdaftar (DPT)</td>
            <td style="width: 5%;">:</td>
            <td><strong>{{ number_format($totalVoters) }}</strong> Orang</td>
        </tr>
        <tr>
            <td>2. Jumlah Suara Sah Masuk</td>
            <td>:</td>
            <td><strong>{{ number_format($totalVotes) }}</strong> Suara</td>
        </tr>
        <tr>
            <td>3. Persentase Partisipasi (Turnout)</td>
            <td>:</td>
            <td><strong>{{ $turnout }}%</strong></td>
        </tr>
    </table>

    <h4 style="margin-bottom: 5px;">II. PEROLEHAN SUARA PASANGAN CALON</h4>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 10%;">No. Urut</th>
                <th style="width: 45%;">Nama Pasangan Calon (Ketua / Sekr / Bend)</th>
                <th style="width: 25%;">Perolehan Suara Sah</th>
                <th style="width: 20%;">Persentase</th>
            </tr>
        </thead>
        <tbody>
            @foreach($election->candidates as $c)
                @php
                    $pct = $totalVotes > 0 ? round(($c->vote_count / $totalVotes) * 100, 1) : 0;
                @endphp
                <tr>
                    <td style="text-align: center; font-weight: bold;">0{{ $c->candidate_number }}</td>
                    <td>
                        <strong>{{ $c->leader_name }}</strong> (Ketua)<br>
                        <small>Sek: {{ $c->secretary_name ?? '-' }} | Ben: {{ $c->treasurer_name ?? '-' }}</small>
                    </td>
                    <td style="text-align: center; font-weight: bold;">{{ number_format($c->vote_count) }} Suara</td>
                    <td style="text-align: center;">{{ $pct }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 15px;">
        Berdasarkan hasil rekapitulasi di atas, Pasangan Calon Nomor Urut <strong>0{{ $winner?->candidate_number }}</strong> atas nama <strong>{{ $winner?->leader_name }}</strong> dinyatakan sebagai <strong>PENGURUS OSIS TERPILIH</strong> Periode {{ $election->period }}.
    </p>

    <p>Demikian Berita Acara ini dibuat dan disahkan secara resmi untuk dipergunakan sebagaimana mestinya.</p>

    {{-- KOLOM TANDA TANGAN --}}
    <div style="margin-top: 40px; width: 100%;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 50%; text-align: center; vertical-align: top;">
                    Mengetahui,<br>
                    <strong>Kepala Sekolah</strong>
                    <br><br><br><br><br>
                    <strong><u>{{ $election->school->principal_name ?? '.........................................' }}</u></strong><br>
                    NIP. {{ $election->school->principal_nip ?? '.................................' }}
                </td>
                <td style="width: 50%; text-align: center; vertical-align: top;">
                    Gunungsitoli, {{ now()->translatedFormat('d F Y') }}<br>
                    <strong>Ketua Panitia / Pembina OSIS</strong>
                    <br><br><br><br><br>
                    <strong><u>.........................................</u></strong><br>
                    NIP/NUPTK. .................................
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
