@extends($layout)

@section('title', 'Tambah Aset Baru - PembdaINVENTORY')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">
<style>
    .inv-font { font-family: 'Plus Jakarta Sans', sans-serif; }
    .inv-mono { font-family: 'JetBrains Mono', monospace; }
</style>
@endpush

@section('content')
<div class="inv-font min-h-screen bg-slate-50/70 p-4 sm:p-6 lg:p-8 space-y-6 max-w-6xl mx-auto">

    {{-- BREADCRUMB & HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-400 mb-1">
                <a href="{{ route('inventory.index') }}" class="hover:text-indigo-600 transition">PembdaINVENTORY</a>
                <i class="fas fa-chevron-right text-[9px]"></i>
                <span class="text-slate-700">Tambah Aset Baru</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <i class="fas fa-plus-circle text-indigo-600"></i>
                <span>Pencatatan Aset Inventaris Baru</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1">
                Lengkapi rincian fisik, lokasi penempatan, nilai perolehan, dan estimasi penyusutan aset.
            </p>
        </div>

        <div>
            <a href="{{ route('inventory.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border-2 border-slate-200 hover:border-slate-400 text-slate-700 font-bold text-xs shadow-sm transition">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali ke Daftar</span>
            </a>
        </div>
    </div>

    {{-- ERROR VALIDASI --}}
    @if ($errors->any())
    <div class="p-4 bg-rose-50 border-2 border-rose-500 rounded-2xl text-rose-900 shadow-sm space-y-1">
        <div class="font-black text-sm flex items-center gap-2">
            <i class="fas fa-exclamation-circle text-rose-600"></i>
            <span>Terdapat beberapa kesalahan input:</span>
        </div>
        <ul class="list-disc list-inside text-xs font-semibold pl-2 space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- FORM TAMBAH ASET --}}
    <form action="{{ route('inventory.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="assetForm">
        @csrf

        {{-- BAGIAN 1: IDENTITAS & KEPEMILIKAN UNIT --}}
        <div class="bg-white rounded-3xl border-2 border-slate-900 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-sm">
                    1
                </div>
                <div>
                    <h2 class="text-base font-black text-slate-900">Identitas Aset & Unit Pemilik</h2>
                    <p class="text-xs text-slate-500 font-medium">Tentukan unit kepemilikan aset (Yayasan atau Unit Sekolah) dan kode registrasi.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                {{-- Unit Pemilik --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Unit Sekolah / Yayasan <span class="text-rose-500">*</span>
                    </label>
                    <select name="school_id" id="schoolSelect" required class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-indigo-600 focus:bg-white text-xs font-bold text-slate-900 transition">
                        <option value="yayasan" {{ old('school_id') === 'yayasan' ? 'selected' : '' }}>🏛️ Yayasan Perguruan Pembda Nias (Pusat)</option>
                        @foreach($schools as $sch)
                        @if(strtolower($sch->type) !== 'yayasan')
                        <option value="{{ $sch->id }}" {{ old('school_id') == $sch->id ? 'selected' : '' }} data-type="{{ strtoupper($sch->type) }}">
                            🏫 {{ $sch->name }} ({{ strtoupper($sch->type) }})
                        </option>
                        @endif
                        @endforeach
                    </select>
                </div>

                {{-- Kode Aset (100% Otomatis Berdasarkan Unit & Tahun) --}}
                <div class="sm:col-span-2 space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                            Kode Aset Registrasi <span class="text-rose-500">*</span>
                        </label>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200 shadow-2xs">
                            <i class="fas fa-magic text-[10px] text-emerald-600"></i> Dibuat Otomatis Sistem
                        </span>
                    </div>
                    <div class="relative">
                        <input type="text" name="asset_code" id="assetCodeInput" value="{{ old('asset_code', $suggestedCode) }}" required class="w-full py-2.5 px-3.5 pr-28 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-indigo-600 focus:bg-white text-xs font-bold text-indigo-700 inv-mono uppercase transition">
                        <div class="absolute right-1.5 top-1.5 flex items-center gap-1">
                            <button type="button" id="btnGenCode" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white font-bold text-[11px] transition" title="Perbarui Kode Otomatis">
                                <i class="fas fa-rotate text-xs"></i>
                                <span>Perbarui</span>
                            </button>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center justify-between text-[11px] text-slate-500 gap-2">
                        <span>Pola Standar: <code class="inv-mono font-bold text-slate-700">AST-[UNIT]-[TAHUN]-[URUT]</code></span>
                        <span id="lastCodeInfo" class="text-slate-500">Kode terakhir di database: <b id="lastCodeVal" class="text-indigo-700 inv-mono font-bold">Memeriksa...</b></span>
                    </div>
                    {{-- No Urut Registrasi Ditangani Otomatis di Background --}}
                    <input type="hidden" name="item_number" id="itemNumberInput" value="{{ old('item_number', $nextItemNumber) }}">
                </div>

                {{-- Nama Aset --}}
                <div class="sm:col-span-2 space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Nama Aset / Barang <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Laptop Asus ExpertBook B1400 / Mesin Bubut Presisi" required class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-indigo-600 focus:bg-white text-sm font-bold text-slate-900 transition">
                </div>

                {{-- Kategori Aset --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Kategori Aset <span class="text-rose-500">*</span>
                    </label>
                    <select name="category_id" id="categorySelect" required class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-indigo-600 focus:bg-white text-xs font-bold text-slate-900 transition">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" data-useful-life="{{ $cat->useful_life_years }}" data-method="{{ $cat->depreciation_method }}" data-code="{{ $cat->code }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }} ({{ $cat->code }})
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Merk / Brand --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Merk / Brand / Tipe
                    </label>
                    <input type="text" name="brand" value="{{ old('brand') }}" placeholder="Contoh: Asus / Toyota / HP / Brother" class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-indigo-600 focus:bg-white text-xs font-bold text-slate-900 transition">
                </div>

                {{-- Nomor Seri / SN / No Rangka --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Nomor Seri / SN / Plat / Nomor Mesin
                    </label>
                    <input type="text" name="serial_number" value="{{ old('serial_number') }}" placeholder="Contoh: SN-8921-9882 / Plat BB 1024 BB" class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-indigo-600 focus:bg-white text-xs font-bold text-slate-900 inv-mono transition">
                </div>

                {{-- Lokasi / Ruangan Penempatan --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Lokasi / Ruangan Penempatan
                    </label>
                    <select name="location_id" id="locationSelect" class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-indigo-600 focus:bg-white text-xs font-bold text-slate-900 transition">
                        <option value="">-- Pilih Lokasi / Ruangan --</option>
                        @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" data-school="{{ $loc->school_id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>
                            {{ $loc->school ? '[' . strtoupper($loc->school->type) . '] ' : '[YAYASAN] ' }}{{ $loc->full_location }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Deskripsi & Spesifikasi --}}
                <div class="sm:col-span-3 space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Deskripsi Lengkap & Spesifikasi Teknis
                    </label>
                    <textarea name="description" rows="2" placeholder="Tuliskan spesifikasi detail aset, kelengkapan, dimensi, daya listrik, dan informasi penting lainnya..." class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-indigo-600 focus:bg-white text-xs font-medium text-slate-900 transition">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        {{-- BAGIAN 2: PENGADAAN & KALKULATOR PENYUSUTAN REAL-TIME --}}
        <div class="bg-white rounded-3xl border-2 border-slate-900 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-black text-sm">
                    2
                </div>
                <div>
                    <h2 class="text-base font-black text-slate-900">Nilai Pengadaan & Skema Penyusutan</h2>
                    <p class="text-xs text-slate-500 font-medium">Kalkulator otomatis menghitung beban penyusutan per tahun, beban per bulan, dan nilai buku terkini.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                {{-- Tahun Pengadaan --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Tahun Pengadaan <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="acquisition_year" id="acqYearInput" value="{{ old('acquisition_year', date('Y')) }}" min="1970" max="{{ date('Y') + 1 }}" required class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-emerald-600 focus:bg-white text-xs font-bold text-slate-900 inv-mono transition">
                </div>

                {{-- Tanggal Pengadaan --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Tanggal Perolehan (Opsional)
                    </label>
                    <input type="date" name="acquisition_date" id="acqDateInput" value="{{ old('acquisition_date') }}" class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-emerald-600 focus:bg-white text-xs font-bold text-slate-900 transition">
                </div>

                {{-- Sumber Dana --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Sumber Dana <span class="text-rose-500">*</span>
                    </label>
                    <select name="funding_source" required class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-emerald-600 focus:bg-white text-xs font-bold text-slate-900 transition">
                        <option value="Yayasan" {{ old('funding_source') === 'Yayasan' ? 'selected' : '' }}>Yayasan (Mandiri)</option>
                        <option value="BOS Reguler" {{ old('funding_source') === 'BOS Reguler' ? 'selected' : '' }}>BOS Reguler</option>
                        <option value="BOS Kinerja" {{ old('funding_source') === 'BOS Kinerja' ? 'selected' : '' }}>BOS Kinerja</option>
                        <option value="DAK Fisik" {{ old('funding_source') === 'DAK Fisik' ? 'selected' : '' }}>DAK Fisik (Pemerintah)</option>
                        <option value="Bantuan Alumni" {{ old('funding_source') === 'Bantuan Alumni' ? 'selected' : '' }}>Bantuan Alumni (IKASPEN / IKA)</option>
                        <option value="Hibah / CSR" {{ old('funding_source') === 'Hibah / CSR' ? 'selected' : '' }}>Hibah Mitra Industri / CSR</option>
                        <option value="Komite Sekolah" {{ old('funding_source') === 'Komite Sekolah' ? 'selected' : '' }}>Komite Sekolah</option>
                        <option value="Lainnya" {{ old('funding_source') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>

                {{-- Jumlah (Quantity) --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Jumlah (Qty) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="quantity" id="quantityInput" value="{{ old('quantity', 1) }}" min="1" required class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-emerald-600 focus:bg-white text-xs font-bold text-slate-900 inv-mono transition">
                </div>

                {{-- Satuan --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Satuan Barang <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="unit" value="{{ old('unit', 'Unit') }}" placeholder="Contoh: Unit / Set / Buah / Pcs / Paket" required class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-emerald-600 focus:bg-white text-xs font-bold text-slate-900 transition">
                </div>

                {{-- Harga Pengadaan Satuan --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Harga Satuan (Rp) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="any" name="unit_price" id="unitPriceInput" value="{{ old('unit_price', 0) }}" min="0" required class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-emerald-600 focus:bg-white text-xs font-bold text-slate-900 inv-mono transition">
                </div>

                {{-- Masa Manfaat (Useful Life) --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Masa Manfaat (Tahun) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="useful_life_years" id="usefulLifeInput" value="{{ old('useful_life_years', 5) }}" min="0" max="100" required class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-emerald-600 focus:bg-white text-xs font-bold text-slate-900 inv-mono transition">
                    <span class="text-[10px] text-slate-400 font-medium">Isi 0 untuk Tanah (tanpa depresiasi)</span>
                </div>

                {{-- Nilai Residu --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Nilai Residu / Sisa (Rp)
                    </label>
                    <input type="number" step="any" name="salvage_value" id="salvageInput" value="{{ old('salvage_value', 0) }}" min="0" class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-emerald-600 focus:bg-white text-xs font-bold text-slate-900 inv-mono transition">
                </div>

                {{-- Metode Penyusutan --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Metode Penyusutan <span class="text-rose-500">*</span>
                    </label>
                    <select name="depreciation_method" id="methodSelect" required class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-emerald-600 focus:bg-white text-xs font-bold text-slate-900 transition">
                        <option value="straight_line" {{ old('depreciation_method', 'straight_line') === 'straight_line' ? 'selected' : '' }}>Garis Lurus (Straight Line) - Rekomendasi</option>
                        <option value="none" {{ old('depreciation_method') === 'none' ? 'selected' : '' }}>Tanpa Penyusutan (Tanah / Lahan)</option>
                    </select>
                </div>
            </div>

            {{-- LIVE CALCULATION PREVIEW BOX --}}
            <div class="rounded-2xl bg-gradient-to-br from-slate-900 to-indigo-950 text-white p-5 border-2 border-slate-900 space-y-3">
                <div class="flex items-center justify-between text-xs font-bold border-b border-white/10 pb-2">
                    <span class="text-indigo-300 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fas fa-calculator text-amber-400"></i>
                        <span>Simulasi Perhitungan Otomatis PembdaINVENTORY</span>
                    </span>
                    <span class="text-emerald-400 font-black">Live Real-time</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-1">
                    <div>
                        <div class="text-[10px] text-slate-400 font-bold uppercase">Total Nilai Pengadaan</div>
                        <div class="text-base sm:text-lg font-black text-amber-300 inv-mono" id="previewTotalCost">Rp 0</div>
                        <div class="text-[10px] text-slate-400" id="previewCostCalc">1 x Rp 0</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-400 font-bold uppercase">Penyusutan / Tahun</div>
                        <div class="text-base sm:text-lg font-black text-rose-300 inv-mono" id="previewAnnualDep">Rp 0</div>
                        <div class="text-[10px] text-slate-400" id="previewMonthlyDep">Rp 0 / bln</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-400 font-bold uppercase">Estimasi Akumulasi</div>
                        <div class="text-base sm:text-lg font-black text-rose-400 inv-mono" id="previewAccumulatedDep">Rp 0</div>
                        <div class="text-[10px] text-slate-400" id="previewYearsUsed">Umur berjalan: 0 thn</div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-400 font-bold uppercase">Nilai Buku Saat Ini</div>
                        <div class="text-base sm:text-lg font-black text-emerald-400 inv-mono" id="previewBookValue">Rp 0</div>
                        <div class="text-[10px] text-slate-400">Nilai wajar buku aktiva</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BAGIAN 3: KONDISI, DOKUMENTASI & PENANGGUNG JAWAB --}}
        <div class="bg-white rounded-3xl border-2 border-slate-900 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center font-black text-sm">
                    3
                </div>
                <div>
                    <h2 class="text-base font-black text-slate-900">Kondisi Fisik, Penanggung Jawab, & Foto Dokumentasi</h2>
                    <p class="text-xs text-slate-500 font-medium">Status operasional, kondisi fisik barang, dan person in charge (PIC).</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                {{-- Kondisi Fisik --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Kondisi Fisik Aset <span class="text-rose-500">*</span>
                    </label>
                    <select name="condition" required class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-amber-500 focus:bg-white text-xs font-bold text-slate-900 transition">
                        <option value="baik" {{ old('condition', 'baik') === 'baik' ? 'selected' : '' }}>✅ Baik (Siap Digunakan)</option>
                        <option value="rusak_ringan" {{ old('condition') === 'rusak_ringan' ? 'selected' : '' }}>⚠️ Rusak Ringan (Bisa Digunakan Sebagian)</option>
                        <option value="rusak_berat" {{ old('condition') === 'rusak_berat' ? 'selected' : '' }}>❌ Rusak Berat (Perlu Perbaikan Besar)</option>
                        <option value="hilang" {{ old('condition') === 'hilang' ? 'selected' : '' }}>❓ Hilang</option>
                        <option value="dihapuskan" {{ old('condition') === 'dihapuskan' ? 'selected' : '' }}>🚫 Dihapuskan / Afkir</option>
                    </select>
                </div>

                {{-- Status Operasional --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Status Operasional <span class="text-rose-500">*</span>
                    </label>
                    <select name="status" required class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-amber-500 focus:bg-white text-xs font-bold text-slate-900 transition">
                        <option value="aktif" {{ old('status', 'aktif') === 'aktif' ? 'selected' : '' }}>Digunakan / Aktif</option>
                        <option value="disimpan" {{ old('status') === 'disimpan' ? 'selected' : '' }}>Disimpan di Gudang</option>
                        <option value="dipinjam" {{ old('status') === 'dipinjam' ? 'selected' : '' }}>Sedang Dipinjam</option>
                        <option value="perbaikan" {{ old('status') === 'perbaikan' ? 'selected' : '' }}>Dalam Perbaikan / Servis</option>
                    </select>
                </div>

                {{-- Penanggung Jawab Nama PIC --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Nama Penanggung Jawab (PIC)
                    </label>
                    <input type="text" name="pic_name" value="{{ old('pic_name') }}" placeholder="Contoh: Bpk. Ama Erwin Zega / Bu Agustiani" class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-amber-500 focus:bg-white text-xs font-bold text-slate-900 transition">
                </div>

                {{-- Upload Foto Dokumentasi Fisik --}}
                <div class="sm:col-span-2 space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Foto Dokumentasi Fisik Aset (Maksimal 3 MB)
                    </label>
                    <input type="file" name="photo" id="photoInput" accept="image/*" class="w-full py-2 px-3 rounded-xl bg-slate-50 border-2 border-slate-200 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-black file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 text-xs text-slate-600 transition">
                </div>

                {{-- Preview Foto --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">Preview Foto</label>
                    <div id="photoPreviewContainer" class="w-24 h-24 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 flex items-center justify-center text-slate-400 overflow-hidden">
                        <i class="fas fa-image text-2xl" id="photoPlaceholder"></i>
                        <img id="photoPreview" class="w-full h-full object-cover hidden" alt="Preview">
                    </div>
                </div>

                {{-- Catatan Tambahan --}}
                <div class="sm:col-span-3 space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Catatan Khusus / Riwayat Asal-Usul
                    </label>
                    <textarea name="notes" rows="2" placeholder="Catatan khusus, nomor faktur, garansi, atau petunjuk pemeliharaan..." class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-amber-500 focus:bg-white text-xs font-medium text-slate-900 transition">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- BUTTON ACTIONS --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('inventory.index') }}" class="px-5 py-3 rounded-xl bg-white border-2 border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-100 transition">
                Batal
            </a>
            <button type="submit" class="px-7 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-sm shadow-xl shadow-indigo-600/30 border-2 border-indigo-700 transition transform active:scale-95 flex items-center gap-2">
                <i class="fas fa-save"></i>
                <span>Simpan & Terbitkan Aset</span>
            </button>
        </div>
    </form>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const qtyIn = document.getElementById('quantityInput');
        const priceIn = document.getElementById('unitPriceInput');
        const salvageIn = document.getElementById('salvageInput');
        const lifeIn = document.getElementById('usefulLifeInput');
        const methodSel = document.getElementById('methodSelect');
        const acqYearIn = document.getElementById('acqYearInput');
        const catSel = document.getElementById('categorySelect');
        const schoolSel = document.getElementById('schoolSelect');
        const assetCodeIn = document.getElementById('assetCodeInput');
        const btnGenCode = document.getElementById('btnGenCode');
        const photoIn = document.getElementById('photoInput');
        const photoPreview = document.getElementById('photoPreview');
        const photoPlaceholder = document.getElementById('photoPlaceholder');

        function formatRupiah(num) {
            return 'Rp ' + Math.round(num).toLocaleString('id-ID');
        }

        function calculateLive() {
            const qty = Math.max(1, parseInt(qtyIn.value) || 1);
            const unitPrice = parseFloat(priceIn.value) || 0;
            const salvage = parseFloat(salvageIn.value) || 0;
            const life = Math.max(0, parseInt(lifeIn.value) || 0);
            const method = methodSel.value;
            const acqYear = parseInt(acqYearIn.value) || new Date().getFullYear();
            const currentYear = new Date().getFullYear();

            const totalCost = qty * unitPrice;
            document.getElementById('previewTotalCost').textContent = formatRupiah(totalCost);
            document.getElementById('previewCostCalc').textContent = qty + ' x ' + formatRupiah(unitPrice);

            if (method === 'none' || life <= 0) {
                document.getElementById('previewAnnualDep').textContent = 'Rp 0 (Bebas Depresiasi)';
                document.getElementById('previewMonthlyDep').textContent = 'Tidak disusutkan';
                document.getElementById('previewAccumulatedDep').textContent = 'Rp 0';
                document.getElementById('previewYearsUsed').textContent = 'Aset non-depreciable';
                document.getElementById('previewBookValue').textContent = formatRupiah(totalCost);
                return;
            }

            const depreciableBase = Math.max(0, totalCost - salvage);
            const annualDep = depreciableBase / life;
            const monthlyDep = annualDep / 12;

            const yearsUsed = Math.max(0, currentYear - acqYear);
            const accumulatedDep = Math.min(depreciableBase, yearsUsed * annualDep);
            const currentBookValue = Math.max(salvage, totalCost - accumulatedDep);

            document.getElementById('previewAnnualDep').textContent = formatRupiah(annualDep);
            document.getElementById('previewMonthlyDep').textContent = formatRupiah(monthlyDep) + ' / bln';
            document.getElementById('previewAccumulatedDep').textContent = formatRupiah(accumulatedDep);
            document.getElementById('previewYearsUsed').textContent = 'Umur pemakaian: ' + yearsUsed + ' tahun';
            document.getElementById('previewBookValue').textContent = formatRupiah(currentBookValue);
        }

        // Live calculation listeners
        [qtyIn, priceIn, salvageIn, lifeIn, methodSel, acqYearIn].forEach(el => {
            el.addEventListener('input', calculateLive);
            el.addEventListener('change', calculateLive);
        });

        // Auto-set useful life from category
        catSel.addEventListener('change', function () {
            const selectedOpt = catSel.options[catSel.selectedIndex];
            if (selectedOpt && selectedOpt.dataset.usefulLife) {
                lifeIn.value = selectedOpt.dataset.usefulLife;
                if (selectedOpt.dataset.method) {
                    methodSel.value = selectedOpt.dataset.method;
                }
                calculateLive();
            }
        });

        // Photo preview
        if (photoIn) {
            photoIn.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        photoPreview.src = e.target.result;
                        photoPreview.classList.remove('hidden');
                        photoPlaceholder.classList.add('hidden');
                    };
                    reader.readAsDataURL(file);
                } else {
                    photoPreview.classList.add('hidden');
                    photoPlaceholder.classList.remove('hidden');
                }
            });
        }

        // Auto-Generate Kode Aset Real-Time
        function updateAssetCodeAuto() {
            const schoolId = schoolSel ? schoolSel.value : 'yayasan';
            const year = acqYearIn ? (acqYearIn.value || new Date().getFullYear()) : new Date().getFullYear();
            const btnIcon = btnGenCode ? btnGenCode.querySelector('i') : null;
            if (btnIcon) btnIcon.classList.add('fa-spin');

            const url = `{{ route('inventory.generate_code') }}?school_id=${encodeURIComponent(schoolId)}&year=${encodeURIComponent(year)}`;

            fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.json())
                .then(data => {
                    if (data && data.code) {
                        if (assetCodeIn) {
                            assetCodeIn.value = data.code;
                        }
                        const lastCodeVal = document.getElementById('lastCodeVal');
                        if (lastCodeVal) {
                            lastCodeVal.textContent = data.last_code ? data.last_code : '(Belum ada di thn ' + year + ')';
                        }
                    }
                })
                .catch(err => console.error('Gagal generate kode aset otomatis:', err))
                .finally(() => {
                    if (btnIcon) btnIcon.classList.remove('fa-spin');
                });
        }

        if (schoolSel) schoolSel.addEventListener('change', updateAssetCodeAuto);
        if (acqYearIn) acqYearIn.addEventListener('change', updateAssetCodeAuto);
        if (btnGenCode) btnGenCode.addEventListener('click', updateAssetCodeAuto);

        // Panggil saat halaman pertama kali dibuka
        updateAssetCodeAuto();

        // Initial calculation
        calculateLive();
    });
</script>
@endpush
@endsection
