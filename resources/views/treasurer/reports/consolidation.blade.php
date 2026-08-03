@extends('layouts.treasurer')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Laporan Konsolidasi Yayasan</h1>
            <p class="text-gray-500 text-sm mt-1">Laporan Keuangan Bulanan dari Bendahara ke Yayasan</p>
        </div>
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-medium shadow-sm transition-colors flex items-center gap-2">
            <i class="fas fa-print"></i> Cetak Laporan
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 print:hidden">
        <form action="{{ route('treasurer.consolidation.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
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

    <!-- Report Area -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 print:p-0 print:border-none print:shadow-none" id="printableArea">
        <!-- Report Header -->
        <div class="text-center mb-8 border-b-2 border-gray-800 pb-4">
            <h2 class="text-2xl font-bold uppercase tracking-wide text-gray-900">{{ $school->name }}</h2>
            <p class="text-gray-600 mt-1">LAPORAN KONSOLIDASI KEUANGAN YAYASAN</p>
            <p class="text-gray-500 text-sm mt-1">
                Periode: <strong>{{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}</strong>
            </p>
        </div>

        <div class="space-y-6">
            
            <!-- PENDAPATAN -->
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-3 border-b border-gray-200 pb-2">1. PENDAPATAN (PENERIMAAN KAS)</h3>
                <table class="w-full text-sm text-left">
                    <tbody class="divide-y divide-gray-100">
                        @forelse($incomeDetails as $type => $amount)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 text-gray-700">Penerimaan {{ $type }}</td>
                            <td class="py-3 px-4 text-right font-medium text-gray-900">Rp {{ number_format($amount, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td class="py-3 px-4 text-gray-500 italic text-center" colspan="2">Belum ada data penerimaan bulan ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-emerald-50 font-bold border-t border-emerald-200">
                            <td class="py-3 px-4 text-emerald-900">Total Pendapatan Kotor</td>
                            <td class="py-3 px-4 text-right text-emerald-700 text-base">Rp {{ number_format($grossIncome, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- PENGELUARAN -->
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-3 border-b border-gray-200 pb-2 mt-6">2. PENGELUARAN (BEBAN)</h3>
                <table class="w-full text-sm text-left">
                    <tbody class="divide-y divide-gray-100">
                        <!-- Rincian Gaji -->
                        <tr class="bg-gray-50">
                            <td class="py-2 px-4 font-semibold text-gray-700" colspan="2">A. Pengeluaran Gaji & Honor</td>
                        </tr>
                        @foreach($salaryDetails as $category => $amount)
                        @if($amount > 0)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 pl-8 text-gray-600"><i class="fas fa-caret-right text-gray-400 mr-2 text-[10px]"></i> {{ $category }}</td>
                            <td class="py-3 px-4 text-right font-medium text-gray-900">Rp {{ number_format($amount, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @endforeach
                        
                        <!-- Bagi Hasil Sekolah -->
                        <tr class="bg-gray-50">
                            <td class="py-2 px-4 font-semibold text-gray-700" colspan="2">B. Bagi Hasil / Kas Sekolah</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 pl-8 text-gray-600">
                                <i class="fas fa-caret-right text-gray-400 mr-2 text-[10px]"></i> Bagian Pendapatan Internal Unit Sekolah
                            </td>
                            <td class="py-3 px-4 text-right font-medium text-gray-900">Rp {{ number_format($schoolShareTotal, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-rose-50 font-bold border-t border-rose-200">
                            <td class="py-3 px-4 text-rose-900">Total Pengeluaran & Potongan</td>
                            <td class="py-3 px-4 text-right text-rose-700 text-base">Rp {{ number_format($salaryTotal + $schoolShareTotal, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- SALDO BERSIH -->
            <div class="mt-8">
                <div class="bg-gradient-to-r from-blue-900 to-indigo-800 rounded-xl p-6 shadow-lg text-white flex items-center justify-between print:bg-white print:text-black print:border-2 print:border-black print:shadow-none print:p-4">
                    <div>
                        <h3 class="text-lg font-medium text-blue-100 print:text-gray-600">3. SALDO NETTO YAYASAN</h3>
                        <p class="text-sm text-blue-200 mt-1 print:text-gray-500">Dana bersih yang disetorkan/dilaporkan kepada Yayasan.</p>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold tracking-tight">Rp {{ number_format($netBalance, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Tanda Tangan -->
            <div class="mt-16 grid grid-cols-2 gap-8 text-center text-sm print:grid-cols-2">
                <div>
                    <p class="mb-20 text-gray-600">Mengetahui,</p>
                    <p class="font-bold text-gray-900 border-b border-gray-400 inline-block px-4 pb-1">Kepala Sekolah</p>
                </div>
                <div>
                    <p class="mb-20 text-gray-600">Dibuat Oleh,</p>
                    <p class="font-bold text-gray-900 border-b border-gray-400 inline-block px-4 pb-1">Bendahara Sekolah</p>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
@media print {
    body { background: white !important; }
    #sidebar, header, nav { display: none !important; }
    main { padding: 0 !important; margin: 0 !important; width: 100% !important; max-width: 100% !important; }
    @page { margin: 1.5cm; }
}
</style>
@endsection
