@extends('layouts.treasurer')

@section('title', 'Pengeluaran Operasional Sekolah (Non-SDM)')

@section('content')
<div class="space-y-6">
    {{-- Header Banner --}}
    <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-indigo-700 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10">
            <i class="fas fa-wallet text-9xl"></i>
        </div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="bg-white/20 text-white text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider inline-block mb-2">
                    Modul Keuangan Bendahara
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Pengeluaran Operasional (Non-SDM)</h1>
                <p class="text-emerald-100 text-sm sm:text-base mt-1 max-w-2xl font-medium">
                    Pencatatan pengeluaran operasional sekolah di luar honorarium perorangan (Subsidi Keuangan, Otorisasi, Operasional, Tunjangan Bendahara, Operator PembdaHUB).
                </p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <button onclick="document.getElementById('modalAddExpense').classList.remove('hidden')" class="bg-white text-emerald-800 hover:bg-emerald-50 px-5 py-2.5 rounded-2xl font-black text-sm shadow-lg transition-all flex items-center gap-2">
                    <i class="fas fa-plus-circle text-emerald-600"></i> Catat Pengeluaran
                </button>
            </div>
        </div>
    </div>

    {{-- Alert Status --}}
    @if(session('success'))
        <div class="bg-emerald-100 border-2 border-emerald-400 text-emerald-950 p-4 rounded-2xl flex items-center gap-3 font-bold shadow-sm">
            <i class="fas fa-check-circle text-emerald-600 text-xl"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-rose-100 border-2 border-rose-400 text-rose-950 p-4 rounded-2xl flex items-center gap-3 font-bold shadow-sm">
            <i class="fas fa-exclamation-circle text-rose-600 text-xl"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-3xl border-2 border-slate-200 shadow-md">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-black text-slate-500 uppercase tracking-wider">Total Accumulasi Pengeluaran</span>
                <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-black">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
            <h3 class="text-2xl sm:text-3xl font-black text-slate-900">Rp {{ number_format($totalAmount, 0, ',', '.') }}</h3>
            <span class="text-xs font-bold text-slate-500 mt-1 block">Seluruh Catatan Pos Non-SDM</span>
        </div>

        <div class="bg-white p-5 rounded-3xl border-2 border-slate-200 shadow-md">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-black text-slate-500 uppercase tracking-wider">Pengeluaran Bulan Ini</span>
                <div class="w-10 h-10 rounded-2xl bg-teal-100 text-teal-700 flex items-center justify-center font-black">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
            <h3 class="text-2xl sm:text-3xl font-black text-slate-900">Rp {{ number_format($thisMonthAmount, 0, ',', '.') }}</h3>
            <span class="text-xs font-bold text-teal-600 mt-1 block">{{ date('F Y') }}</span>
        </div>

        <div class="bg-white p-5 rounded-3xl border-2 border-slate-200 shadow-md">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-black text-slate-500 uppercase tracking-wider">Rekening Default</span>
                <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-black">
                    <i class="fas fa-list-ol"></i>
                </div>
            </div>
            <h3 class="text-2xl sm:text-3xl font-black text-slate-900">{{ $categories->count() }} Rekening</h3>
            <span class="text-xs font-bold text-slate-500 mt-1 block">Subsidi, Otorisasi, Operasional, dll</span>
        </div>
    </div>

    {{-- Filter & Table Ledger --}}
    <div class="bg-white rounded-3xl border-2 border-slate-200 shadow-lg overflow-hidden">
        {{-- Filter Bar --}}
        <div class="p-5 bg-slate-50 border-b-2 border-slate-200">
            <form method="GET" action="{{ route('treasurer.operational-expenses.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase mb-1">Rekening Pengeluaran</label>
                    <select name="expense_category_id" onchange="this.form.submit()" class="w-full rounded-xl border-2 border-slate-300 px-3 py-2 text-sm font-bold text-slate-900 bg-white shadow-xs focus:ring-2 focus:ring-emerald-500">
                        <option value="">Semua Rekening</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('expense_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase mb-1">Bulan</label>
                    <select name="month" onchange="this.form.submit()" class="w-full rounded-xl border-2 border-slate-300 px-3 py-2 text-sm font-bold text-slate-900 bg-white shadow-xs focus:ring-2 focus:ring-emerald-500">
                        <option value="">Semua Bulan</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase mb-1">Tahun</label>
                    <select name="year" onchange="this.form.submit()" class="w-full rounded-xl border-2 border-slate-300 px-3 py-2 text-sm font-bold text-slate-900 bg-white shadow-xs focus:ring-2 focus:ring-emerald-500">
                        <option value="">Semua Tahun</option>
                        @for($y = date('Y'); $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full bg-slate-900 hover:bg-black text-white py-2 px-4 rounded-xl font-bold text-sm shadow-md transition-all">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <a href="{{ route('treasurer.operational-expenses.index') }}" class="bg-slate-200 hover:bg-slate-300 text-slate-800 py-2 px-3 rounded-xl font-bold text-sm shadow-xs transition-all">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 uppercase text-xs font-black border-b-2 border-slate-200">
                        <th class="py-3.5 px-4">Tanggal</th>
                        <th class="py-3.5 px-4">Rekening / Pos</th>
                        <th class="py-3.5 px-4">Judul & Keterangan</th>
                        <th class="py-3.5 px-4">Nominal (Rp)</th>
                        <th class="py-3.5 px-4">Penerima / Pos Tujuan</th>
                        <th class="py-3.5 px-4">Metode</th>
                        <th class="py-3.5 px-4 text-center">Bukti</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-medium text-sm text-slate-900">
                    @forelse($expenses as $exp)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-800 whitespace-nowrap">
                                {{ $exp->expense_date ? $exp->expense_date->format('d M Y') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="bg-emerald-100 text-emerald-950 border border-emerald-400 text-xs font-black px-3 py-1 rounded-lg inline-block">
                                    {{ $exp->expenseCategory->name ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-black text-slate-900 text-base">{{ $exp->title }}</div>
                                @if($exp->notes)
                                    <div class="text-xs text-slate-600 mt-0.5">{{ Str::limit($exp->notes, 80) }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-black text-emerald-800 text-base whitespace-nowrap">
                                Rp {{ number_format($exp->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-800 whitespace-nowrap">
                                {{ $exp->recipient_name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="uppercase text-xs font-black px-2.5 py-1 rounded-md border {{ $exp->payment_method == 'transfer' ? 'bg-blue-50 text-blue-800 border-blue-300' : 'bg-amber-50 text-amber-900 border-amber-300' }}">
                                    {{ $exp->payment_method }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($exp->proof_file)
                                    <a href="{{ asset('storage/' . $exp->proof_file) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-lg border border-indigo-300">
                                        <i class="fas fa-file-invoice"></i> Lihat
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400 font-bold">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="editExpense({{ json_encode($exp) }})" class="p-2 rounded-xl bg-amber-100 text-amber-900 hover:bg-amber-200 font-bold transition-all" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('treasurer.operational-expenses.destroy', $exp->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi pengeluaran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-xl bg-rose-100 text-rose-900 hover:bg-rose-200 font-bold transition-all" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-500 font-bold">
                                <i class="fas fa-folder-open text-4xl mb-3 block text-slate-300"></i>
                                Belum ada catatan transaksi pengeluaran operasional.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div class="p-4 bg-slate-50 border-t-2 border-slate-200">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
</div>

{{-- MODAL TAMBAH PENGELUARAN --}}
<div id="modalAddExpense" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-xl w-full border-2 border-slate-300 shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-emerald-600 to-teal-700 p-5 text-white flex items-center justify-between">
            <h3 class="font-black text-lg flex items-center gap-2">
                <i class="fas fa-plus-circle"></i> Catat Transaksi Pengeluaran Operasional
            </h3>
            <button onclick="document.getElementById('modalAddExpense').classList.add('hidden')" class="text-white hover:text-slate-200 text-xl font-bold">&times;</button>
        </div>
        <form action="{{ route('treasurer.operational-expenses.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase mb-1">Rekening / Pos Pengeluaran <span class="text-rose-500">*</span></label>
                <select name="expense_category_id" required class="w-full rounded-xl border-2 border-slate-300 p-2.5 text-sm font-bold text-slate-900 bg-white shadow-xs focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Pilih Rekening Pengeluaran --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->code ?? 'Kategori' }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-black text-slate-700 uppercase mb-1">Judul Transaksi <span class="text-rose-500">*</span></label>
                <input type="text" name="title" required placeholder="Contoh: Pencairan Subsidi Keuangan Bulan Agustus" class="w-full rounded-xl border-2 border-slate-300 p-2.5 text-sm font-bold text-slate-900 bg-white shadow-xs focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase mb-1">Tanggal Transaksi <span class="text-rose-500">*</span></label>
                    <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border-2 border-slate-300 p-2.5 text-sm font-bold text-slate-900 bg-white shadow-xs focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase mb-1">Nominal (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="amount" min="0" step="1000" required placeholder="0" class="w-full rounded-xl border-2 border-slate-300 p-2.5 text-sm font-bold text-slate-900 bg-white shadow-xs focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase mb-1">Penerima / Pos Tujuan</label>
                    <input type="text" name="recipient_name" placeholder="Nama Penerima / Vendor" class="w-full rounded-xl border-2 border-slate-300 p-2.5 text-sm font-bold text-slate-900 bg-white shadow-xs focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase mb-1">Metode Pembayaran</label>
                    <select name="payment_method" required class="w-full rounded-xl border-2 border-slate-300 p-2.5 text-sm font-bold text-slate-900 bg-white shadow-xs focus:ring-2 focus:ring-emerald-500">
                        <option value="cash">Tunai (Cash)</option>
                        <option value="transfer">Transfer Bank</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-black text-slate-700 uppercase mb-1">Bukti Transfer / Kwitansi (PDF / Gambar)</label>
                <input type="file" name="proof_file" accept="image/*,.pdf" class="w-full text-xs text-slate-600 bg-slate-50 border-2 border-slate-300 rounded-xl p-2 font-bold">
            </div>

            <div>
                <label class="block text-xs font-black text-slate-700 uppercase mb-1">Catatan Tambahan</label>
                <textarea name="notes" rows="2" placeholder="Keterangan opsional..." class="w-full rounded-xl border-2 border-slate-300 p-2.5 text-sm font-bold text-slate-900 bg-white shadow-xs focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t-2 border-slate-200">
                <button type="button" onclick="document.getElementById('modalAddExpense').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 font-bold text-slate-800 text-sm">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 font-black text-white text-sm shadow-md">Simpan Transaksi</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT PENGELUARAN --}}
<div id="modalEditExpense" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-xl w-full border-2 border-slate-300 shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-amber-600 to-orange-700 p-5 text-white flex items-center justify-between">
            <h3 class="font-black text-lg flex items-center gap-2">
                <i class="fas fa-edit"></i> Edit Transaksi Pengeluaran
            </h3>
            <button onclick="document.getElementById('modalEditExpense').classList.add('hidden')" class="text-white hover:text-slate-200 text-xl font-bold">&times;</button>
        </div>
        <form id="formEditExpense" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase mb-1">Rekening / Pos Pengeluaran <span class="text-rose-500">*</span></label>
                <select id="edit_expense_category_id" name="expense_category_id" required class="w-full rounded-xl border-2 border-slate-300 p-2.5 text-sm font-bold text-slate-900 bg-white shadow-xs focus:ring-2 focus:ring-amber-500">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-black text-slate-700 uppercase mb-1">Judul Transaksi <span class="text-rose-500">*</span></label>
                <input type="text" id="edit_title" name="title" required class="w-full rounded-xl border-2 border-slate-300 p-2.5 text-sm font-bold text-slate-900 bg-white shadow-xs focus:ring-2 focus:ring-amber-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase mb-1">Tanggal Transaksi <span class="text-rose-500">*</span></label>
                    <input type="date" id="edit_expense_date" name="expense_date" required class="w-full rounded-xl border-2 border-slate-300 p-2.5 text-sm font-bold text-slate-900 bg-white shadow-xs focus:ring-2 focus:ring-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase mb-1">Nominal (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" id="edit_amount" name="amount" min="0" step="1000" required class="w-full rounded-xl border-2 border-slate-300 p-2.5 text-sm font-bold text-slate-900 bg-white shadow-xs focus:ring-2 focus:ring-amber-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase mb-1">Penerima / Pos Tujuan</label>
                    <input type="text" id="edit_recipient_name" name="recipient_name" class="w-full rounded-xl border-2 border-slate-300 p-2.5 text-sm font-bold text-slate-900 bg-white shadow-xs focus:ring-2 focus:ring-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase mb-1">Metode Pembayaran</label>
                    <select id="edit_payment_method" name="payment_method" required class="w-full rounded-xl border-2 border-slate-300 p-2.5 text-sm font-bold text-slate-900 bg-white shadow-xs focus:ring-2 focus:ring-amber-500">
                        <option value="cash">Tunai (Cash)</option>
                        <option value="transfer">Transfer Bank</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-black text-slate-700 uppercase mb-1">Ganti Bukti Transfer / Kwitansi (Opsional)</label>
                <input type="file" name="proof_file" accept="image/*,.pdf" class="w-full text-xs text-slate-600 bg-slate-50 border-2 border-slate-300 rounded-xl p-2 font-bold">
            </div>

            <div>
                <label class="block text-xs font-black text-slate-700 uppercase mb-1">Catatan Tambahan</label>
                <textarea id="edit_notes" name="notes" rows="2" class="w-full rounded-xl border-2 border-slate-300 p-2.5 text-sm font-bold text-slate-900 bg-white shadow-xs focus:ring-2 focus:ring-amber-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t-2 border-slate-200">
                <button type="button" onclick="document.getElementById('modalEditExpense').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 font-bold text-slate-800 text-sm">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 font-black text-white text-sm shadow-md">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function editExpense(exp) {
    document.getElementById('formEditExpense').action = '/bendahara/operational-expenses/' + exp.id;
    document.getElementById('edit_expense_category_id').value = exp.expense_category_id;
    document.getElementById('edit_title').value = exp.title;
    document.getElementById('edit_expense_date').value = exp.expense_date ? exp.expense_date.substring(0, 10) : '';
    document.getElementById('edit_amount').value = exp.amount;
    document.getElementById('edit_recipient_name').value = exp.recipient_name || '';
    document.getElementById('edit_payment_method').value = exp.payment_method || 'cash';
    document.getElementById('edit_notes').value = exp.notes || '';
    document.getElementById('modalEditExpense').classList.remove('hidden');
}
</script>
@endsection
