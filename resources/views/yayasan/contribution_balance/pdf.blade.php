<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Saldo Kontribusi Unit Sekolah</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #5b21b6;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 0;
            font-size: 16px;
            color: #4c1d95;
            text-transform: uppercase;
        }
        .header h3 {
            margin: 3px 0 0 0;
            font-size: 13px;
            color: #1e1b4b;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 10px;
            color: #6b7280;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 10px;
        }
        .meta-table td {
            padding: 2px 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .table th {
            background-color: #4c1d95;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 6px 8px;
            border: 1px solid #4c1d95;
            text-align: left;
        }
        .table td {
            padding: 5px 8px;
            border: 1px solid #e5e7eb;
            font-size: 10px;
        }
        .table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .badge-surplus {
            color: #065f46;
            font-weight: bold;
        }
        .badge-defisit {
            color: #991b1b;
            font-weight: bold;
        }
        .footer-sig {
            margin-top: 30px;
            width: 100%;
        }
        .footer-sig td {
            text-align: center;
            vertical-align: top;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>YAYASAN PERGURUAN PEMBDA</h2>
        <h3>LAPORAN SALDO KONTRIBUSI UNIT SEKOLAH</h3>
        <p>Tahun Pelajaran: {{ $currentYear->year ?? '-' }} | Periode: {{ $periodMode === 'annual' ? 'Tahunan (12 Bulan)' : 'Bulanan (1 Bulan)' }}</p>
    </div>

    <table class="meta-table">
        <tr>
            <td width="15%"><strong>Dicetak Pada:</strong></td>
            <td width="35%">{{ date('d F Y, H:i') }} WIB</td>
            <td width="15%"><strong>Dibuat Oleh:</strong></td>
            <td width="35%">Sistem Informasi PembdaHUB (Yayasan)</td>
        </tr>
    </table>

    <h4 style="margin: 0 0 8px 0; color:#4c1d95; font-size:12px;">I. REKAPITULASI KONTRIBUSI SELURUH UNIT SEKOLAH</h4>
    <table class="table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="20%">Nama Unit Sekolah</th>
                <th width="10%" class="text-center">Siswa</th>
                <th width="16%" class="text-right">Pendapatan SPP</th>
                <th width="16%" class="text-right">Gaji Guru & Pegawai</th>
                <th width="16%" class="text-right">Belanja Otorisasi</th>
                <th width="18%" class="text-right">Saldo Kontribusi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schoolData as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $item['school']->name }}</td>
                    <td class="text-center">{{ $item['total_students'] }}</td>
                    <td class="text-right">Rp {{ number_format($item['income_total'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['salary_total'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['authorized_expense_total'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold {{ $item['is_surplus'] ? 'badge-surplus' : 'badge-defisit' }}">
                        {{ $item['is_surplus'] ? '+' : '' }}Rp {{ number_format($item['saldo'], 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f3e8ff; font-weight: bold;">
                <td colspan="3" class="text-right">GRAND TOTAL:</td>
                <td class="text-right">Rp {{ number_format($grandTotalIncome, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($grandTotalGaji, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($grandTotalOtorisasi, 0, ',', '.') }}</td>
                <td class="text-right {{ $grandTotalSaldo >= 0 ? 'badge-surplus' : 'badge-defisit' }}" style="font-size:11px;">
                    {{ $grandTotalSaldo >= 0 ? '+' : '' }}Rp {{ number_format($grandTotalSaldo, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    <h4 style="margin: 15px 0 8px 0; color:#4c1d95; font-size:12px;">II. RINCIAN PENDAPATAN PER LEVEL & PENGELUARAN UNIT</h4>
    @foreach($schoolData as $item)
        <div style="margin-bottom: 12px; page-break-inside: avoid;">
            <strong style="font-size: 11px; color:#1e1b4b;">{{ $item['school']->name }} ({{ $item['school']->type }})</strong>
            <table class="table" style="margin-top:4px;">
                <thead>
                    <tr>
                        <th>Rincian Pendapatan Level</th>
                        <th class="text-center">Siswa</th>
                        <th class="text-right">SPP/Bln</th>
                        <th class="text-right">Total Pendapatan</th>
                        <th class="text-right">Pengeluaran & Otorisasi</th>
                        <th class="text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($item['levels']) > 0)
                        @foreach($item['levels'] as $idx => $lvl)
                            <tr>
                                <td>Kelas {{ $lvl['level'] }}</td>
                                <td class="text-center">{{ $lvl['student_count'] }}</td>
                                <td class="text-right">Rp {{ number_format($lvl['spp_monthly'], 0, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format($lvl['income_total'], 0, ',', '.') }}</td>
                                @if($idx == 0)
                                    <td>Gaji Guru & Pegawai ({{ $item['employee_count'] }} org)</td>
                                    <td class="text-right">Rp {{ number_format($item['salary_total'], 0, ',', '.') }}</td>
                                @elseif($idx == 1 && $item['school']->type === 'yayasan')
                                    <td>Belanja Ops Central Yayasan</td>
                                    <td class="text-right">Rp {{ number_format($item['authorized_expense_total'], 0, ',', '.') }}</td>
                                @else
                                    <td>-</td>
                                    <td class="text-right">-</td>
                                @endif
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" class="text-center text-muted" style="color:#6b7280; font-style:italic;">Unit Yayasan (Tidak Ada SPP Siswa)</td>
                            <td>Gaji Staf/Pengurus Yayasan ({{ $item['employee_count'] }} org)</td>
                            <td class="text-right">Rp {{ number_format($item['salary_total'], 0, ',', '.') }}</td>
                        </tr>
                        @if($item['authorized_expense_total'] > 0)
                        <tr>
                            <td colspan="4"></td>
                            <td>Belanja Ops Central Yayasan</td>
                            <td class="text-right">Rp {{ number_format($item['authorized_expense_total'], 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    @endif
                </tbody>
                <tfoot>
                    <tr style="background-color:#faf5ff; font-weight:bold;">
                        <td colspan="3" class="text-right">Subtotal Pendapatan Unit:</td>
                        <td class="text-right">Rp {{ number_format($item['income_total'], 0, ',', '.') }}</td>
                        <td class="text-right">Total Pengeluaran Unit:</td>
                        <td class="text-right">Rp {{ number_format($item['expense_total'], 0, ',', '.') }}</td>
                    </tr>
                    <tr style="background-color:#f3e8ff; font-weight:bold;">
                        <td colspan="4" class="text-right">SALDO KONTRIBUSI AKHIR UNIT:</td>
                        <td colspan="2" class="text-right {{ $item['is_surplus'] ? 'badge-surplus' : 'badge-defisit' }}">
                            {{ $item['is_surplus'] ? 'SURPLUS (+): ' : 'DEFISIT (-): ' }} Rp {{ number_format(abs($item['saldo']), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endforeach

    <h4 style="margin: 15px 0 8px 0; color:#4c1d95; font-size:12px;">III. RINCIAN BELANJA OPERASIONAL PER KODE REKENING</h4>
    <table class="table">
        <thead>
            <tr>
                <th width="12%">Kode Rekening</th>
                <th width="35%">Nama Rekening Belanja Operasional</th>
                <th width="18%">Kategori</th>
                <th width="15%" class="text-right">Nominal / Bln</th>
                <th width="20%" class="text-right">Subtotal ({{ $periodMode === 'annual' ? '12 Bulan' : '1 Bulan' }})</th>
            </tr>
        </thead>
        <tbody>
            @php $hasOpsDetails = false; @endphp
            @foreach($schoolData as $item)
                @if(!empty($item['expense_details']) && count($item['expense_details']) > 0)
                    @php $hasOpsDetails = true; @endphp
                    <tr style="background-color: #f5f3ff; font-weight: bold;">
                        <td colspan="5" style="color: #4c1d95; font-size: 10px;">
                            UNIT / LEMBAGA: {{ strtoupper($item['school']->name) }} ({{ $item['school']->type }})
                        </td>
                    </tr>
                    @foreach($item['expense_details'] as $accCode => $accAmount)
                        @php
                            $accInfo = $expenseAccounts[$accCode] ?? null;
                            $accMonthly = (float) $accAmount;
                            $accPeriod = $accMonthly * $multiplier;
                        @endphp
                        @if($accMonthly > 0)
                            <tr>
                                <td class="font-bold text-center" style="font-family: monospace;">{{ $accCode }}</td>
                                <td>{{ $accInfo['name'] ?? 'Belanja Operasional' }}</td>
                                <td>{{ $accInfo['category'] ?? '-' }}</td>
                                <td class="text-right">Rp {{ number_format($accMonthly, 0, ',', '.') }}</td>
                                <td class="text-right font-bold">Rp {{ number_format($accPeriod, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                    @endforeach
                @endif
            @endforeach
            @if(!$hasOpsDetails)
                <tr>
                    <td colspan="5" class="text-center" style="color:#9ca3af; font-style:italic;">Belum ada rincian belanja operasional per kode rekening yang diinputkan.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="footer-sig">
        <tr>
            <td width="60%"></td>
            <td width="40%">
                <p>Gunungsitoli, {{ date('d F Y') }}</p>
                <p><strong>Ketua Yayasan Perguruan Pembda</strong></p>
                <br><br><br>
                <p><u>___________________________</u></p>
            </td>
        </tr>
    </table>

</body>
</html>
