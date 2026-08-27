@extends('layouts.treasurer')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Pratinjau & Pembersihan Pembayaran Ganda</h1>
            <p class="text-gray-500 text-sm mt-1">Inspeksi & Pilih Transaksi Pembayaran Ganda yang Ingin Dihapus</p>
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

    @php
        $totalDuplicateBills = $groupedDuplicates->count();
        $totalRedundantPayments = 0;
        $totalExcessAmount = 0;

        foreach($groupedDuplicates as $payments) {
            $totalRedundantPayments += ($payments->count() - 1);
            $totalExcessAmount += $payments->slice(1)->sum('amount_paid');
        }
    @endphp

    <!-- Executive Summary Card -->
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="text-xs font-semibold text-rose-600 uppercase tracking-wider mb-1">Hasil Inspeksi Transaksi Ganda</div>
            <div class="flex items-center gap-6">
                <div>
                    <span class="text-2xl font-bold text-gray-900">{{ $totalDuplicateBills }}</span>
                    <span class="text-xs text-gray-500 block">Tagihan Berganda</span>
                </div>
                <div class="border-r border-gray-200 h-8"></div>
                <div>
                    <span class="text-2xl font-bold text-rose-600">{{ $totalRedundantPayments }}</span>
                    <span class="text-xs text-gray-500 block">Record Duplikat</span>
                </div>
                <div class="border-r border-gray-200 h-8"></div>
                <div>
                    <span class="text-2xl font-bold text-rose-700">Rp {{ number_format($totalExcessAmount, 0, ',', '.') }}</span>
                    <span class="text-xs text-gray-500 block">Total Nominal Berlebih</span>
                </div>
            </div>
        </div>

        @if($totalDuplicateBills > 0)
        <div class="flex items-center gap-3">
            <button type="button" onclick="selectAllDuplicates(true)" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-xl text-xs font-semibold transition-colors">
                <i class="fas fa-check-square me-1"></i> Pilih Semua Duplikat
            </button>
            <form action="{{ route('treasurer.payments.fix_duplicates') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membersihkan SELURUH pembayaran ganda secara otomatis? Pembayaran pertama yang sah akan tetap dipertahankan.');">
                @csrf
                <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition-colors flex items-center gap-2">
                    <i class="fas fa-broom"></i> Bersihkan Semua Otomatis
                </button>
            </form>
        </div>
        @else
        <div class="bg-emerald-50 text-emerald-700 px-4 py-3 rounded-xl text-sm font-semibold flex items-center gap-2">
            <i class="fas fa-shield-check text-emerald-600 text-base"></i> Tidak ada transaksi pembayaran ganda terdeteksi. Data kas bersih!
        </div>
        @endif
    </div>

    <!-- Interactive Selection Form -->
    @if($totalDuplicateBills > 0)
    <form action="{{ route('treasurer.payments.fix_duplicates') }}" method="POST" id="selectiveFixForm" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi pembayaran ganda yang dipilih?');">
        @csrf
        
        <div class="flex items-center justify-between bg-gray-50 p-4 rounded-xl border border-gray-200 mb-4">
            <div class="flex items-center gap-2">
                <input type="checkbox" id="checkAllMaster" class="w-4 h-4 text-rose-600 rounded border-gray-300 focus:ring-rose-500" onchange="toggleAllCheckboxes(this)">
                <label for="checkAllMaster" class="text-xs font-bold text-gray-700 uppercase tracking-wider cursor-pointer">Pilih Semua Record Duplikat Untuk Dihapus</label>
            </div>
            <button type="submit" class="bg-rose-700 hover:bg-rose-800 text-white px-5 py-2 rounded-xl text-xs font-bold shadow-sm transition-colors flex items-center gap-2">
                <i class="fas fa-trash-can"></i> Hapus Pembayaran Ganda yang Dipilih
            </button>
        </div>

        <div class="space-y-6">
            @foreach($groupedDuplicates as $billId => $payments)
            @php
                $firstPay = $payments->first();
                $bill = $firstPay->bill;
                $student = $firstPay->student;
                $duplicateCount = $payments->count() - 1;
                $excessAmount = $payments->slice(1)->sum('amount_paid');
            @endphp
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <!-- Group Header -->
                <div class="bg-slate-50 p-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-rose-600 bg-rose-100 px-2.5 py-1 rounded-lg me-2">Ganda {{ $payments->count() }}x</span>
                        <strong class="text-gray-900 font-bold text-base">{{ $student->full_name ?? 'Siswa' }}</strong>
                        <span class="text-gray-500 text-xs ms-2">(NISN: {{ $student->nisn ?? '-' }})</span>
                        <div class="text-xs text-gray-600 mt-1">
                            Jenis Tagihan: <strong class="text-gray-800">{{ $bill->paymentType->type_name ?? 'Tagihan' }}</strong> 
                            @if($bill->month) - Periode Bulan {{ $bill->month }}/{{ $bill->year }} @endif
                            | Total Nominal Tagihan: <strong>Rp {{ number_format($bill->amount, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-500">Nominal Ganda Berlebih:</div>
                        <div class="text-sm font-bold text-rose-700">Rp {{ number_format($excessAmount, 0, ',', '.') }}</div>
                    </div>
                </div>

                <!-- Transaction List -->
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 text-gray-500 border-b border-gray-200 uppercase tracking-wider text-[10px] font-bold">
                            <tr>
                                <th class="py-2.5 px-4 text-center w-12">Pilih</th>
                                <th class="py-2.5 px-4">Status Transaksi</th>
                                <th class="py-2.5 px-4">Waktu Input Transaksi</th>
                                <th class="py-2.5 px-4">No. Kwitansi / Ref</th>
                                <th class="py-2.5 px-4">Metode Bayar</th>
                                <th class="py-2.5 px-4">Diproses Oleh</th>
                                <th class="py-2.5 px-4 text-right">Jumlah Dibayar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($payments as $idx => $p)
                            <tr class="{{ $idx == 0 ? 'bg-emerald-50/40' : 'bg-rose-50/20' }}">
                                <td class="py-3 px-4 text-center">
                                    @if($idx == 0)
                                        <span class="text-emerald-600 font-bold" title="Pembayaran Pertama Sah"><i class="fas fa-lock"></i></span>
                                    @else
                                        <input type="checkbox" name="payment_ids[]" value="{{ $p->id }}" class="dup-checkbox w-4 h-4 text-rose-600 rounded border-gray-300 focus:ring-rose-500">
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-semibold">
                                    @if($idx == 0)
                                        <span class="text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded text-[11px]"><i class="fas fa-check me-1"></i> Utama (Sah)</span>
                                    @else
                                        <span class="text-rose-700 bg-rose-100 px-2 py-0.5 rounded text-[11px]"><i class="fas fa-copy me-1"></i> Duplikat Ke-{{ $idx }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-gray-700 font-medium whitespace-nowrap">
                                    {{ $p->created_at ? $p->created_at->format('d/m/Y H:i:s') : '-' }}
                                    <span class="text-gray-400 text-[10px] block">({{ $p->created_at ? $p->created_at->diffForHumans() : '' }})</span>
                                </td>
                                <td class="py-3 px-4 font-mono text-gray-700">{{ $p->receipt_number ?? $p->reference_number ?? '-' }}</td>
                                <td class="py-3 px-4 text-gray-700 uppercase font-medium">{{ $p->payment_method }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ $p->processedBy->full_name ?? 'Sistem' }}</td>
                                <td class="py-3 px-4 text-right font-bold {{ $idx == 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                                    Rp {{ number_format($p->amount_paid, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="bg-rose-700 hover:bg-rose-800 text-white px-6 py-3 rounded-xl text-sm font-bold shadow-md transition-colors flex items-center gap-2">
                <i class="fas fa-trash-can"></i> Hapus Pembayaran Ganda yang Dipilih
            </button>
        </div>
    </form>
    @endif
</div>

<script>
function toggleAllCheckboxes(master) {
    const checkboxes = document.querySelectorAll('.dup-checkbox');
    checkboxes.forEach(cb => cb.checked = master.checked);
}

function selectAllDuplicates(state) {
    const master = document.getElementById('checkAllMaster');
    if (master) master.checked = state;
    toggleAllCheckboxes({ checked: state });
}
</script>
@endsection
