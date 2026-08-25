@extends('layouts.treasurer')

@section('title', 'Detail Tagihan Siswa')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white shadow-md">
                <i class="fas fa-file-invoice-dollar text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Detail Tagihan Siswa</h1>
                <p class="text-xs text-gray-500 mt-0.5">{{ $bill->student->full_name }} &bull; {{ $bill->paymentType->name ?? '-' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('treasurer.bills.edit', $bill) }}" class="px-5 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl font-bold text-xs transition flex items-center gap-1.5">
                <i class="fas fa-edit"></i> Edit Nominal
            </a>
            <a href="{{ route('treasurer.bills.index') }}" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-xl font-bold text-xs transition shadow-xs flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    {{-- Detail Card --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center gap-2">
                <i class="fas fa-user-graduate text-indigo-600"></i> Informasi Siswa
            </h3>
            <div class="space-y-2.5 text-xs">
                <div>
                    <p class="text-gray-400 font-medium">Nama Lengkap</p>
                    <p class="font-bold text-gray-900 text-sm">{{ $bill->student->full_name }}</p>
                </div>
                <div>
                    <p class="text-gray-400 font-medium">NISN / NIS</p>
                    <p class="font-mono text-gray-800">{{ $bill->student->nisn ?: '-' }} / {{ $bill->student->nis ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 font-medium">Tahun Pelajaran</p>
                    <p class="font-semibold text-gray-800">{{ $bill->academicYear->name ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center gap-2">
                <i class="fas fa-receipt text-indigo-600"></i> Rincian Tagihan & Status Pembayaran
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-4 bg-gray-50 rounded-xl">
                    <p class="text-xs text-gray-400 font-medium">Total Tagihan</p>
                    <p class="text-lg font-bold text-gray-900 font-mono mt-1">Rp {{ number_format($bill->amount, 0, ',', '.') }}</p>
                </div>
                <div class="p-4 bg-emerald-50 rounded-xl">
                    <p class="text-xs text-emerald-700 font-medium">Sudah Dibayar</p>
                    <p class="text-lg font-bold text-emerald-700 font-mono mt-1">Rp {{ number_format($bill->paid_amount ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="p-4 bg-rose-50 rounded-xl">
                    <p class="text-xs text-rose-700 font-medium">Sisa Tagihan</p>
                    <p class="text-lg font-bold text-rose-700 font-mono mt-1">
                        Rp {{ number_format(max(0, $bill->amount - ($bill->paid_amount ?? 0)), 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="pt-2 flex items-center justify-between">
                <div>
                    <span class="text-xs text-gray-400 font-medium mr-2">Status:</span>
                    @php
                        $isPaid = ($bill->paid_amount ?? 0) >= $bill->amount;
                        $isPartial = ($bill->paid_amount ?? 0) > 0 && !$isPaid;
                    @endphp
                    @if($isPaid)
                    <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold">LUNAS</span>
                    @elseif($isPartial)
                    <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold">SEBAGIAN</span>
                    @else
                    <span class="px-3 py-1 bg-rose-100 text-rose-800 rounded-full text-xs font-bold">BELUM BAYAR</span>
                    @endif
                </div>
                @if(!$isPaid)
                <a href="{{ route('treasurer.payments.create', ['student_id' => $bill->student_id, 'bill_id' => $bill->id]) }}" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs transition shadow-sm inline-flex items-center gap-1.5">
                    <i class="fas fa-money-bill-wave"></i> Bayar Tagihan Sekarang
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Riwayat Pembayaran --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100 font-bold text-sm text-gray-800 flex items-center gap-2">
            <i class="fas fa-history text-indigo-600"></i> Riwayat Transaksi Pembayaran
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 text-gray-600 font-bold border-b border-gray-100">
                    <tr>
                        <th class="p-4 w-12 text-center">No</th>
                        <th class="p-4">No. Transaksi</th>
                        <th class="p-4">Tanggal Bayar</th>
                        <th class="p-4">Metode Pembayaran</th>
                        <th class="p-4 text-right">Jumlah Dibayar</th>
                        <th class="p-4 text-center">Aksi Kwitansi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($bill->payments as $idx => $pmt)
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="p-4 text-center font-bold text-gray-400">{{ $idx + 1 }}</td>
                        <td class="p-4 font-mono font-bold text-gray-800">{{ $pmt->payment_number ?? '-' }}</td>
                        <td class="p-4 font-mono text-gray-600">{{ $pmt->payment_date ? \Carbon\Carbon::parse($pmt->payment_date)->translatedFormat('d M Y') : '-' }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 bg-gray-100 text-gray-700 rounded-lg font-bold text-[11px] uppercase">
                                {{ $pmt->payment_method ?? 'TUNAI' }}
                            </span>
                        </td>
                        <td class="p-4 text-right font-mono font-bold text-emerald-700">
                            Rp {{ number_format($pmt->amount, 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-center">
                            <a href="{{ route('treasurer.payments.show', $pmt) }}" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg font-bold text-xs inline-flex items-center gap-1 transition">
                                <i class="fas fa-receipt"></i> Kwitansi
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-400 italic">Belum ada catatan pembayaran untuk tagihan ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
