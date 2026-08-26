@extends('layouts.treasurer')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Buku Kas Umum (BKU)</h1>
            <p class="text-gray-500 text-sm mt-1">Laporan Arus Kas Masuk & Keluar Riil Unit Sekolah</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('treasurer.reports.bku.pdf', ['month' => $month, 'year' => $year]) }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-xl text-sm font-medium shadow-sm transition-colors flex items-center gap-2">
                <i class="fas fa-file-pdf"></i> Download PDF
            </a>
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-medium shadow-sm transition-colors flex items-center gap-2">
                <i class="fas fa-print"></i> Cetak BKU
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 print:hidden">
        <form action="{{ route('treasurer.reports.bku') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Bulan</label>
                <select name="month" class="bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                        </option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Tahun</label>
                <select name="year" class="bg-gray-50 border border-gray-200 text-gray-800 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    @for ($i = date('Y'); $i >= 2023; $i--)
                        <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2.5 rounded-xl text-sm font-medium shadow-sm transition-colors h-[42px]">
                Tampilkan
            </button>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <div class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-1">Total Penerimaan (Kas Masuk)</div>
            <div class="text-2xl font-bold text-emerald-700">Rp {{ number_format($totalDebit, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <div class="text-xs font-semibold text-rose-600 uppercase tracking-wider mb-1">Total Pengeluaran (Kas Keluar)</div>
            <div class="text-2xl font-bold text-rose-700">Rp {{ number_format($totalCredit, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <div class="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1">Saldo Kas Akhir Periode</div>
            <div class="text-2xl font-bold {{ $netEndingBalance >= 0 ? 'text-blue-700' : 'text-rose-700' }}">
                Rp {{ number_format($netEndingBalance, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <!-- Jurnal BKU -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 overflow-hidden">
        <div class="text-center mb-6">
            <h2 class="text-xl font-bold uppercase text-gray-900">{{ $school->name }}</h2>
            <p class="text-gray-600 font-medium">BUKU KAS UMUM (BKU)</p>
            <p class="text-gray-500 text-xs mt-1">Periode: {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-700 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4 w-28">Tanggal</th>
                        <th class="py-3 px-4 w-32">No. Bukti / Ref</th>
                        <th class="py-3 px-4">Uraian Transaksi</th>
                        <th class="py-3 px-4 text-right w-36 text-emerald-700">Debet (Masuk)</th>
                        <th class="py-3 px-4 text-right w-36 text-rose-700">Kredit (Keluar)</th>
                        <th class="py-3 px-4 text-right w-36 text-blue-700">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transactions as $index => $trx)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-4 text-center font-medium text-gray-500">{{ $index + 1 }}</td>
                        <td class="py-3 px-4 text-gray-700 whitespace-nowrap">{{ date('d/m/Y', strtotime($trx['date'])) }}</td>
                        <td class="py-3 px-4 text-gray-500 font-mono text-xs">{{ $trx['ref_no'] }}</td>
                        <td class="py-3 px-4 text-gray-900 font-medium">{{ $trx['description'] }}</td>
                        <td class="py-3 px-4 text-right text-emerald-700 font-medium">
                            {{ $trx['debit'] > 0 ? 'Rp ' . number_format($trx['debit'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-3 px-4 text-right text-rose-700 font-medium">
                            {{ $trx['credit'] > 0 ? 'Rp ' . number_format($trx['credit'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold {{ $trx['balance'] >= 0 ? 'text-gray-900' : 'text-rose-600' }}">
                            Rp {{ number_format($trx['balance'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-gray-400 italic">Belum ada data transaksi kas pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-100 font-bold text-gray-900 border-t-2 border-gray-300">
                    <tr>
                        <td colspan="4" class="py-3 px-4 text-right">TOTAL MUTASI PERIODE INI:</td>
                        <td class="py-3 px-4 text-right text-emerald-700">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-right text-rose-700">Rp {{ number_format($totalCredit, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-right text-blue-800">Rp {{ number_format($netEndingBalance, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
