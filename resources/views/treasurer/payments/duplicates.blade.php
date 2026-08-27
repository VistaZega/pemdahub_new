@extends('layouts.treasurer')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Pembersihan Pembayaran Ganda</h1>
            <p class="text-gray-500 text-sm mt-1">Diagnostik & Pemulihan Transaksi Pembayaran Ganda (Uang Sekolah & Iuran OSIS)</p>
        </div>
        <a href="{{ route('treasurer.payments.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm font-medium transition-colors">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Pembayaran
        </a>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-sm font-medium flex items-center gap-2">
        <i class="fas fa-check-circle text-emerald-600"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('warning'))
    <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-2xl text-sm font-medium flex items-center gap-2">
        <i class="fas fa-exclamation-triangle text-amber-600"></i>
        <span>{{ session('warning') }}</span>
    </div>
    @endif

    <!-- Deduplication Tool Status Card -->
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-gray-900">Hasil Pemindaian Sistem</h2>
            <p class="text-sm text-gray-500 mt-1">
                Ditemukan <strong class="text-rose-600">{{ $groupedDuplicates->count() }} tagihan</strong> dengan transaksi pembayaran ganda.
            </p>
        </div>

        @if($groupedDuplicates->count() > 0)
        <form action="{{ route('treasurer.payments.fix_duplicates') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membersihkan seluruh pembayaran ganda? Sistem akan menyisakan 1 pembayaran pertama yang sah dan memulihkan status tagihan.');">
            @csrf
            <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold shadow-sm transition-colors flex items-center gap-2">
                <i class="fas fa-broom"></i> Bersihkan Semua Pembayaran Ganda
            </button>
        </form>
        @else
        <div class="bg-emerald-50 text-emerald-700 px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2">
            <i class="fas fa-shield-check"></i> Tidak ada transaksi ganda terdeteksi. Sistem Kas Bersih!
        </div>
        @endif
    </div>

    <!-- Duplicate Payment Details Table -->
    @if($groupedDuplicates->count() > 0)
    <div class="space-y-4">
        @foreach($groupedDuplicates as $billId => $payments)
        @php
            $firstPay = $payments->first();
            $bill = $firstPay->bill;
            $student = $firstPay->student;
            $duplicateCount = $payments->count() - 1;
            $excessAmount = $payments->slice(1)->sum('amount_paid');
        @endphp
        <div class="bg-white rounded-2xl border border-rose-100 shadow-sm overflow-hidden">
            <div class="bg-rose-50/50 p-4 border-b border-rose-100 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-rose-600 bg-rose-100 px-2.5 py-1 rounded-lg me-2">Ganda {{ $payments->count() }}x</span>
                    <strong class="text-gray-900 font-bold text-base">{{ $student->full_name ?? 'Siswa' }}</strong>
                    <span class="text-gray-500 text-xs ms-2">(NISN: {{ $student->nisn ?? '-' }})</span>
                    <div class="text-xs text-gray-600 mt-1">
                        Jenis Tagihan: <strong>{{ $bill->paymentType->type_name ?? 'Tagihan' }}</strong> 
                        @if($bill->month) - Periode Bulan {{ $bill->month }}/{{ $bill->year }} @endif
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <div class="text-xs text-gray-500">Nominal Berlebih:</div>
                        <div class="text-sm font-bold text-rose-700">Rp {{ number_format($excessAmount, 0, ',', '.') }}</div>
                    </div>
                    <form action="{{ route('treasurer.payments.fix_duplicates') }}" method="POST">
                        @csrf
                        <input type="hidden" name="bill_id" value="{{ $billId }}">
                        <button type="submit" class="bg-white border border-rose-200 hover:bg-rose-600 hover:text-white text-rose-700 px-3 py-1.5 rounded-lg text-xs font-semibold shadow-xs transition-colors">
                            Bersihkan Tagihan Ini
                        </button>
                    </form>
                </div>
            </div>

            <table class="w-full text-xs text-left">
                <thead class="bg-gray-50 text-gray-500 border-b border-gray-100">
                    <tr>
                        <th class="py-2.5 px-4">Status Record</th>
                        <th class="py-2.5 px-4">Waktu Transaksi</th>
                        <th class="py-2.5 px-4">No. Kwitansi / Ref</th>
                        <th class="py-2.5 px-4">Metode Bayar</th>
                        <th class="py-2.5 px-4 text-right">Jumlah Dibayar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($payments as $idx => $p)
                    <tr class="{{ $idx == 0 ? 'bg-emerald-50/40' : 'bg-rose-50/20' }}">
                        <td class="py-2.5 px-4 font-semibold">
                            @if($idx == 0)
                                <span class="text-emerald-700 flex items-center gap-1"><i class="fas fa-check"></i> Sah (Disimpan)</span>
                            @else
                                <span class="text-rose-600 flex items-center gap-1"><i class="fas fa-times-circle"></i> Duplikat (Akan Dihapus)</span>
                            @endif
                        </td>
                        <td class="py-2.5 px-4 text-gray-700">{{ $p->created_at ? $p->created_at->format('d/m/Y H:i:s') : '-' }}</td>
                        <td class="py-2.5 px-4 font-mono text-gray-600">{{ $p->receipt_number ?? $p->reference_number ?? '-' }}</td>
                        <td class="py-2.5 px-4 text-gray-700 uppercase font-medium">{{ $p->payment_method }}</td>
                        <td class="py-2.5 px-4 text-right font-bold {{ $idx == 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                            Rp {{ number_format($p->amount_paid, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
