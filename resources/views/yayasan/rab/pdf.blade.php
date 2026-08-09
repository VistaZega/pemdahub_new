<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rencana Anggaran Belanja (RAB) Yayasan Pembda</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #1e293b; line-height: 1.4; margin: 0; padding: 0; }
        .header { text-align: center; border-bottom: 2px solid #475569; padding-bottom: 10px; margin-bottom: 15px; }
        .header h2 { font-size: 16px; margin: 0; text-transform: uppercase; color: #0f172a; }
        .header h3 { font-size: 13px; margin: 3px 0 0 0; color: #475569; font-weight: normal; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        .table th { background-color: #f1f5f9; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .bg-total { background-color: #0f172a; color: #ffffff; }
        .summary-card { width: 100%; margin-bottom: 15px; }
        .summary-card td { padding: 8px; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 6px; }
        .footer { margin-top: 30px; width: 100%; }
        .footer td { text-align: center; vertical-align: top; }
    </style>
</head>
<body>
    <div class="header">
        <h2>YAYASAN PERGURUAN PEMBDA NIAS</h2>
        <h3>RENCANA ANGGARAN BELANJA (RAB) UNIT SEKOLAH & YAYASAN</h3>
        <div style="font-size: 9px; color: #64748b; margin-top: 4px;">
            Tahun Pelajaran: {{ $activeYear->year ?? '-' }} | Mode Periode: {{ $periodMode === 'annual' ? '12 Bulan (Tahunan)' : '1 Bulan' }}
        </div>
    </div>

    <table class="summary-card">
        <tr>
            <td width="25%">
                <div style="font-size: 8px; color: #64748b;">TOTAL RENCANA PENDAPATAN SPP</div>
                <div style="font-size: 11px; font-weight: bold; color: #047857;">Rp {{ number_format($summary['total_income'], 0, ',', '.') }}</div>
            </td>
            <td width="25%">
                <div style="font-size: 8px; color: #64748b;">TOTAL RENCANA GAJI & HONOR</div>
                <div style="font-size: 11px; font-weight: bold; color: #4338ca;">Rp {{ number_format($summary['total_salary'], 0, ',', '.') }}</div>
            </td>
            <td width="25%">
                <div style="font-size: 8px; color: #64748b;">TOTAL BELANJA OPERASIONAL</div>
                <div style="font-size: 11px; font-weight: bold; color: #b45309;">Rp {{ number_format($summary['total_operational'], 0, ',', '.') }}</div>
            </td>
            <td width="25%">
                <div style="font-size: 8px; color: #64748b;">SALDO RENCANA (SURPLUS / DEFISIT)</div>
                <div style="font-size: 11px; font-weight: bold; color: {{ $summary['total_balance'] >= 0 ? '#1e1b4b' : '#991b1b' }};">
                    Rp {{ number_format($summary['total_balance'], 0, ',', '.') }}
                </div>
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="22%">Unit Sekolah</th>
                <th width="10%" class="text-center">Siswa Aktif</th>
                <th width="12%" class="text-right">Tarif SPP/Bulan</th>
                <th width="15%" class="text-right">A. Rencana Pendapatan</th>
                <th width="14%" class="text-right">1. Honor & Tunjangan</th>
                <th width="12%" class="text-right">2. Operasional</th>
                <th width="12%" class="text-right">B. Total Belanja</th>
                <th width="15%" class="text-right">C. Saldo Rencana</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schoolRabList as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $item['school']->name }}</td>
                    <td class="text-center">{{ number_format($item['student_count'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['spp_monthly_rate'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #047857;">Rp {{ number_format($item['rab_income_period'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['rab_salary_period'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['rab_operational_period'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #b91c1c;">Rp {{ number_format($item['rab_total_expense_period'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="background-color: #f5f3ff;">Rp {{ number_format($item['rab_balance_period'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-total">
                <td colspan="2" class="font-bold text-center">TOTAL KONSOLIDASI YAYASAN</td>
                <td class="text-center">{{ number_format($summary['total_students'], 0, ',', '.') }}</td>
                <td class="text-right">—</td>
                <td class="text-right font-bold">Rp {{ number_format($summary['total_income'], 0, ',', '.') }}</td>
                <td class="text-right font-bold">Rp {{ number_format($summary['total_salary'], 0, ',', '.') }}</td>
                <td class="text-right font-bold">Rp {{ number_format($summary['total_operational'], 0, ',', '.') }}</td>
                <td class="text-right font-bold">Rp {{ number_format($summary['total_expense'], 0, ',', '.') }}</td>
                <td class="text-right font-bold" style="font-size: 11px;">Rp {{ number_format($summary['total_balance'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 15px;">
        <h4 style="margin-bottom: 5px; font-size: 10px; text-transform: uppercase; color: #475569;">Breakdown 8 Item Belanja Operasional Per Unit (Periode {{ $periodMode === 'annual' ? 'Tahunan' : 'Bulanan' }})</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>Item Belanja Operasional</th>
                    @foreach($schoolRabList as $sItem)
                        <th class="text-right">{{ $sItem['school']->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($operationalItems as $itemKey => $itemInfo)
                    <tr>
                        <td><strong>{{ $itemInfo['name'] }}</strong></td>
                        @foreach($schoolRabList as $sItem)
                            <td class="text-right">
                                Rp {{ number_format(($sItem['itemised_monthly'][$itemKey] ?? 0) * $multiplier, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <table class="footer">
        <tr>
            <td width="50%"></td>
            <td width="50%">
                Gunungsitoli, {{ date('d F Y') }}<br>
                <strong>Ketua Yayasan Perguruan Pembda Nias</strong><br><br><br><br><br>
                <strong><u>YULIANUS ZEGA, S.Kom., M.Pd.T.</u></strong>
            </td>
        </tr>
    </table>
</body>
</html>
