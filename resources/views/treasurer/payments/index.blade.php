@extends('layouts.treasurer')

@section('title', 'Daftar Pembayaran')

@section('content')
<div class="space-y-8 pb-12">
    <!-- Header Section -->
    <div class="bg-white border border-slate-100 rounded-2xl p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 via-teal-600 to-cyan-600 text-white shadow-lg shadow-emerald-500/20 shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Daftar Pembayaran</h1>
                    <p class="text-xs font-semibold text-slate-500 mt-1">Monitoring dan pencatatan transaksi pembayaran siswa oleh Bendahara</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('treasurer.payments.bulk-create') }}" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl font-bold text-xs hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Pembayaran Massal
                </a>

                <a href="{{ route('treasurer.payments.create') }}" 
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-bold text-xs shadow-md shadow-emerald-600/20 hover:from-emerald-700 hover:to-teal-700 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Input Pembayaran
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-xl text-xs font-bold text-emerald-800 flex items-center gap-2 shadow-sm">
        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-start justify-between">
                <div class="space-y-2">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Transaksi</p>
                    <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ number_format($payments->total(), 0, ',', '.') }}</h3>
                    <p class="text-[11px] text-slate-400 font-medium">Transaksi terdaftar</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-start justify-between">
                <div class="space-y-2">
                    <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Nominal Diterima</p>
                    <h3 class="text-2xl font-extrabold text-emerald-600 tracking-tight">
                        Rp {{ number_format($payments->sum('amount_paid'), 0, ',', '.') }}
                    </h3>
                    <p class="text-[11px] text-slate-400 font-medium">Total dana terkumpul halaman ini</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-start justify-between">
                <div class="space-y-2">
                    <p class="text-xs font-bold text-teal-600 uppercase tracking-wider">Terverifikasi</p>
                    <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                        {{ number_format($payments->where('is_verified', true)->count(), 0, ',', '.') }}
                    </h3>
                    <p class="text-[11px] text-slate-400 font-medium">Pembayaran tervalidasi</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-teal-50 text-teal-600 rounded-2xl shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8 space-y-6">
        <form method="GET" action="{{ route('treasurer.payments.index') }}" class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Metode Pembayaran</label>
                    <div class="relative">
                        <select name="payment_method" class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 font-semibold text-xs rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all appearance-none cursor-pointer">
                            <option value="">Semua Metode</option>
                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Tunai</option>
                            <option value="transfer" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                            <option value="qris" {{ request('payment_method') == 'qris' ? 'selected' : '' }}>QRIS</option>
                            <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Kartu</option>
                        </select>
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Kelas</label>
                    <div class="relative">
                        <select name="classroom_id" class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 font-semibold text-xs rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all appearance-none cursor-pointer">
                            <option value="">Semua Kelas</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" {{ request('classroom_id') == $classroom->id ? 'selected' : '' }}>{{ $classroom->class_name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Jenis Pembayaran</label>
                    <div class="relative">
                        <select name="payment_type_id" class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 text-slate-800 font-semibold text-xs rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all appearance-none cursor-pointer">
                            <option value="">Semua Jenis</option>
                            @foreach($paymentTypes as $type)
                                <option value="{{ $type->id }}" {{ request('payment_type_id') == $type->id ? 'selected' : '' }}>{{ $type->type_name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Tanggal Dari</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" 
                        class="w-full px-4 py-2 bg-slate-50 border border-slate-200 text-slate-800 font-semibold text-xs rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Tanggal Sampai</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" 
                        class="w-full px-4 py-2 bg-slate-50 border border-slate-200 text-slate-800 font-semibold text-xs rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                        placeholder="No Kwitansi, Siswa..."
                        class="w-full px-4 py-2 bg-slate-50 border border-slate-200 text-slate-800 font-semibold text-xs rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
                <a href="{{ route('treasurer.payments.index') }}" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-all">
                    Reset
                </a>
                <a href="{{ route('treasurer.payments.export', ['payment_method' => request('payment_method'), 'search' => request('search'), 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'classroom_id' => request('classroom_id'), 'payment_type_id' => request('payment_type_id')]) }}" 
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 border border-blue-200 text-blue-700 rounded-xl font-bold text-xs hover:bg-blue-100 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export Excel
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2 bg-emerald-600 text-white rounded-xl font-bold text-xs shadow-md shadow-emerald-600/20 hover:bg-emerald-700 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-separate border-spacing-0">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">No Kwitansi</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Siswa</th>
                        <th class="px-4 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Tagihan & Periode</th>
                        <th class="px-4 py-4 text-right text-xs font-bold text-slate-600 uppercase tracking-wider">Jumlah Bayar</th>
                        <th class="px-4 py-4 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Metode</th>
                        <th class="px-4 py-4 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Status Validasi</th>
                        <th class="px-4 py-4 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $payment)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-4 py-4 text-xs font-semibold text-slate-700 whitespace-nowrap">{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : '-' }}</td>
                        <td class="px-4 py-4 font-mono text-xs font-bold text-slate-800">{{ $payment->receipt_number }}</td>
                        <td class="px-4 py-4">
                            <p class="font-bold text-slate-900 text-xs">{{ $payment->student->full_name }}</p>
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mt-0.5">{{ $payment->student->nisn ?? 'NISN -' }}</p>
                        </td>
                        <td class="px-4 py-4">
                            @if($payment->bill)
                                <p class="font-bold text-slate-900 text-xs">{{ $payment->bill->paymentType->type_name }} ({{ $payment->bill->academicYear->year }})</p>
                                @if($payment->bill->month)
                                    <p class="text-[11px] text-slate-500 font-medium mt-0.5">Periode: {{ \Carbon\Carbon::create(null, $payment->bill->month, 1)->translatedFormat('F') }} {{ $payment->bill->year }}</p>
                                @else
                                    <p class="text-[11px] text-slate-500 font-medium mt-0.5">1 Kali Bayar ({{ $payment->bill->year }})</p>
                                @endif
                            @else
                                <span class="text-xs text-slate-400 italic">Pembayaran Massal / Umum</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right font-extrabold text-emerald-600 text-xs">Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</td>
                        <td class="px-4 py-4 text-center">
                            @if($payment->payment_method === 'cash')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-[11px] font-bold">Tunai</span>
                            @elseif($payment->payment_method === 'transfer')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg text-[11px] font-bold">Transfer</span>
                            @elseif($payment->payment_method === 'qris')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-purple-50 text-purple-700 border border-purple-200 rounded-lg text-[11px] font-bold">QRIS</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 bg-slate-100 text-slate-700 border border-slate-200 rounded-lg text-[11px] font-bold">{{ strtoupper($payment->payment_method) }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($payment->is_verified)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-bold">Terverifikasi</span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-xs font-bold">Belum Verifikasi</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            <a href="{{ route('treasurer.payments.show', $payment) }}" 
                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-emerald-600 hover:border-emerald-300 transition-all shadow-sm" title="Lihat Detail Transaksi">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-16 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-sm font-bold text-slate-700">Tidak ada transaksi pembayaran ditemukan</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-white">
            {{ $payments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
