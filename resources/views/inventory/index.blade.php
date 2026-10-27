@extends($layout)

@section('title', 'PembdaINVENTORY - Manajemen Aset Terpadu')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">
<style>
    .inv-font { font-family: 'Plus Jakarta Sans', sans-serif; }
    .inv-mono { font-family: 'JetBrains Mono', monospace; }
    .stat-glow {
        position: relative;
        overflow: hidden;
    }
    .stat-glow::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
    }
    .num-col { font-variant-numeric: tabular-nums; white-space: nowrap !important; }
</style>
@endpush

@section('content')
<div class="inv-font min-h-screen bg-slate-50/70 p-4 sm:p-6 lg:p-8 space-y-6">

    {{-- ALERT NOTIFIKASI --}}
    @if(session('success'))
    <div class="flex items-center gap-3 p-4 bg-emerald-50 border-2 border-emerald-500 rounded-2xl text-emerald-900 shadow-sm animate-fade-in">
        <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold text-base shadow-sm">
            <i class="fas fa-check"></i>
        </div>
        <div class="flex-1 font-semibold text-sm">
            {{ session('success') }}
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="flex items-center gap-3 p-4 bg-rose-50 border-2 border-rose-500 rounded-2xl text-rose-900 shadow-sm animate-fade-in">
        <div class="w-9 h-9 rounded-xl bg-rose-500 text-white flex items-center justify-center font-bold text-base shadow-sm">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="flex-1 font-semibold text-sm">
            {{ session('error') }}
        </div>
    </div>
    @endif

    {{-- HEADER & HERO SECTION --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-950 via-indigo-950 to-slate-900 text-white border-2 border-slate-900 p-6 sm:p-8 shadow-xl">
        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
            <i class="fas fa-boxes-stacked text-[220px]"></i>
        </div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 border border-indigo-400/30 text-indigo-300 text-xs font-bold tracking-wider uppercase">
                    <i class="fas fa-shield-alt"></i> Khusus Yayasan & Akun Yang Ditunjuk
                </div>
                <h1 class="text-2xl sm:text-4xl font-black tracking-tight text-white flex items-center gap-3">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-200 via-white to-amber-200">PembdaINVENTORY</span>
                </h1>
                <p class="text-sm sm:text-base text-slate-300 max-w-2xl font-medium leading-relaxed">
                    Sistem Tata Kelola & Pencatatan Aset Terpadu Yayasan Perguruan Pembda Nias, SMAS Pembda 1, SMK Swasta Pembda Nias, dan SMPS Pembda 2 Gunungsitoli.
                </p>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('inventory.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-lg shadow-indigo-600/30 border border-indigo-400/30 transition transform active:scale-95">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Aset Baru</span>
                </a>

                @if(auth()->user()->canDesignateInventoryManagers())
                <a href="{{ route('inventory.managers.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-sm shadow-lg shadow-purple-600/30 border border-purple-400/30 transition transform active:scale-95">
                    <i class="fas fa-user-check"></i>
                    <span>Akun Ditunjuk</span>
                </a>
                @endif

                <a href="{{ route('inventory.settings.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-sm border border-slate-700 transition">
                    <i class="fas fa-sliders-h"></i>
                    <span>Kategori & Lokasi</span>
                </a>

                <div class="flex items-center gap-1.5">
                    <a href="{{ route('inventory.export_pdf', request()->query()) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl bg-rose-600/90 hover:bg-rose-600 text-white font-bold text-xs border border-rose-400/30 transition" title="Cetak Dokumen Rekapitulasi PDF">
                        <i class="fas fa-file-pdf"></i>
                        <span class="hidden sm:inline">PDF</span>
                    </a>
                    <a href="{{ route('inventory.export_csv') }}" class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl bg-emerald-600/90 hover:bg-emerald-600 text-white font-bold text-xs border border-emerald-400/30 transition" title="Unduh Excel / CSV">
                        <i class="fas fa-file-excel"></i>
                        <span class="hidden sm:inline">Excel</span>
                    </a>
                    <form action="{{ route('inventory.recalculate') }}" method="POST" onsubmit="return confirm('Kalkulasi ulang akumulasi penyusutan untuk seluruh aset sesuai tahun berjalan?')">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl bg-amber-600/90 hover:bg-amber-600 text-white font-bold text-xs border border-amber-400/30 transition" title="Sinkronisasi & Hitung Ulang Nilai Buku">
                            <i class="fas fa-calculator"></i>
                            <span class="hidden sm:inline">Hitung</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- KEY METRICS STAT CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        {{-- Card 1: Total Unit Fisik --}}
        <div class="stat-glow bg-white rounded-2xl border-2 border-slate-900 p-4 shadow-sm before:bg-indigo-600">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Total Fisik Barang</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-black">
                    <i class="fas fa-cubes"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 num-col">
                {{ number_format($totalItems, 0, ',', '.') }}
                <span class="text-xs font-bold text-slate-500">Unit / Pcs</span>
            </div>
            <div class="text-[11px] font-semibold text-slate-500 mt-1">
                Dari <b class="text-indigo-600">{{ $totalAssetCount }}</b> jenis/kelompok aset
            </div>
        </div>

        {{-- Card 2: Total Nilai Pengadaan --}}
        <div class="stat-glow bg-white rounded-2xl border-2 border-slate-900 p-4 shadow-sm before:bg-blue-600">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Total Harga Pengadaan</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-black">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-black text-blue-900 num-col">
                Rp {{ number_format($totalAcquisitionValue, 0, ',', '.') }}
            </div>
            <div class="text-[11px] font-semibold text-slate-500 mt-1">
                Nilai perolehan historis
            </div>
        </div>

        {{-- Card 3: Total Akumulasi Penyusutan --}}
        <div class="stat-glow bg-white rounded-2xl border-2 border-slate-900 p-4 shadow-sm before:bg-rose-500">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Akumulasi Penyusutan</span>
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-black">
                    <i class="fas fa-chart-line-down"></i>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-black text-rose-700 num-col">
                Rp {{ number_format($totalAccumulatedDepreciation, 0, ',', '.') }}
            </div>
            <div class="text-[11px] font-semibold text-slate-500 mt-1">
                Beban/Thn: <b class="text-slate-800">Rp {{ number_format($totalAnnualDepreciation, 0, ',', '.') }}</b>
            </div>
        </div>

        {{-- Card 4: Nilai Buku Saat Ini (Net Worth) --}}
        <div class="stat-glow bg-white rounded-2xl border-2 border-slate-900 p-4 shadow-sm before:bg-emerald-600">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Nilai Buku Saat Ini</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-black">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-black text-emerald-700 num-col">
                Rp {{ number_format($totalCurrentBookValue, 0, ',', '.') }}
            </div>
            <div class="text-[11px] font-semibold text-slate-500 mt-1">
                Harga wajar aset bersih saat ini
            </div>
        </div>

        {{-- Card 5: Kondisi Fisik Aset --}}
        <div class="stat-glow bg-white rounded-2xl border-2 border-slate-900 p-4 shadow-sm before:bg-amber-500">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Kondisi Fisik</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-black">
                    <i class="fas fa-heart-pulse"></i>
                </div>
            </div>
            <div class="flex items-center gap-2 mt-1">
                <span class="px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-800 text-xs font-black">
                    <i class="fas fa-check-circle mr-1"></i>{{ $conditionStats['baik'] ?? 0 }} Baik
                </span>
                <span class="px-2 py-0.5 rounded-lg bg-amber-100 text-amber-800 text-xs font-black">
                    <i class="fas fa-exclamation-triangle mr-1"></i>{{ $conditionStats['rusak_ringan'] ?? 0 }} RR
                </span>
                <span class="px-2 py-0.5 rounded-lg bg-rose-100 text-rose-800 text-xs font-black">
                    <i class="fas fa-times-circle mr-1"></i>{{ $conditionStats['rusak_berat'] ?? 0 }} RB
                </span>
            </div>
            <div class="text-[11px] font-semibold text-slate-500 mt-2">
                Monitoring keandalan sarpras
            </div>
        </div>
    </div>

    {{-- UNIT SUMMARY MINI-CARDS (YAYASAN & 3 SEKOLAH AKTIF) --}}
    <div class="bg-white rounded-3xl border-2 border-slate-900 p-5 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-md bg-indigo-600 text-white flex items-center justify-center text-xs font-black">
                    <i class="fas fa-landmark"></i>
                </div>
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Rekapitulasi Aset Per Unit Yayasan & Sekolah</h2>
            </div>
            <span class="text-xs font-bold text-slate-400">Total 4 Entitas Unit Aktif</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            {{-- Yayasan --}}
            <a href="{{ route('inventory.index', array_merge(request()->query(), ['school_id' => 'yayasan'])) }}" class="p-3.5 rounded-2xl border-2 transition {{ $schoolFilter === 'yayasan' ? 'border-purple-600 bg-purple-50/70 shadow-sm' : 'border-slate-200 hover:border-slate-300 bg-slate-50/50' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black text-purple-700 uppercase">Yayasan Pembda</span>
                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-purple-100 text-purple-800">Pusat</span>
                </div>
                <div class="text-lg font-black text-slate-900 mt-2 num-col">
                    Rp {{ number_format($unitSummary['yayasan']['book_value'] ?? 0, 0, ',', '.') }}
                </div>
                <div class="text-xs font-semibold text-slate-500 mt-0.5">
                    {{ $unitSummary['yayasan']['total_items'] ?? 0 }} Unit Fisik Tercatat
                </div>
            </a>

            @foreach($schools as $sch)
            @if(strtolower($sch->type) !== 'yayasan')
            <a href="{{ route('inventory.index', array_merge(request()->query(), ['school_id' => $sch->id])) }}" class="p-3.5 rounded-2xl border-2 transition {{ $schoolFilter == $sch->id ? 'border-indigo-600 bg-indigo-50/70 shadow-sm' : 'border-slate-200 hover:border-slate-300 bg-slate-50/50' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black text-indigo-700 uppercase truncate max-w-[170px]" title="{{ $sch->name }}">{{ $sch->name }}</span>
                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800">{{ strtoupper($sch->type) }}</span>
                </div>
                <div class="text-lg font-black text-slate-900 mt-2 num-col">
                    Rp {{ number_format($unitSummary[$sch->id]['book_value'] ?? 0, 0, ',', '.') }}
                </div>
                <div class="text-xs font-semibold text-slate-500 mt-0.5">
                    {{ $unitSummary[$sch->id]['total_items'] ?? 0 }} Unit Fisik Tercatat
                </div>
            </a>
            @endif
            @endforeach
        </div>
    </div>

    {{-- FILTER & SEARCH TOOLBAR --}}
    <div class="bg-white rounded-3xl border-2 border-slate-900 p-5 shadow-sm space-y-4">
        <form action="{{ route('inventory.index') }}" method="GET" class="space-y-4">
            {{-- UNIT TAB BUTTONS --}}
            <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-3">
                <span class="text-xs font-bold text-slate-400 mr-1 uppercase">Pilih Unit:</span>
                <a href="{{ route('inventory.index', array_merge(request()->except('school_id', 'page'), ['school_id' => 'all'])) }}" class="px-3 py-1.5 rounded-xl text-xs font-black border transition {{ $schoolFilter === 'all' ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border-slate-200' }}">
                    <i class="fas fa-layer-group mr-1.5"></i>Semua Unit (Yayasan & Sekolah)
                </a>
                <a href="{{ route('inventory.index', array_merge(request()->except('school_id', 'page'), ['school_id' => 'yayasan'])) }}" class="px-3 py-1.5 rounded-xl text-xs font-black border transition {{ $schoolFilter === 'yayasan' ? 'bg-purple-700 text-white border-purple-700 shadow-sm' : 'bg-purple-50 text-purple-800 hover:bg-purple-100 border-purple-200' }}">
                    <i class="fas fa-landmark mr-1.5"></i>Yayasan Perguruan Pembda
                </a>
                @foreach($schools as $sch)
                @if(strtolower($sch->type) !== 'yayasan')
                <a href="{{ route('inventory.index', array_merge(request()->except('school_id', 'page'), ['school_id' => $sch->id])) }}" class="px-3 py-1.5 rounded-xl text-xs font-black border transition {{ $schoolFilter == $sch->id ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border-slate-200' }}">
                    <i class="fas fa-school mr-1.5"></i>{{ $sch->name }}
                </a>
                @endif
                @endforeach
            </div>

            {{-- SEARCH & DROPDOWN FILTERS --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                <input type="hidden" name="school_id" value="{{ $schoolFilter }}">

                {{-- Search Bar --}}
                <div class="sm:col-span-2 relative">
                    <i class="fas fa-search absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
                    <input type="text" name="search" value="{{ $searchTerm }}" placeholder="Cari Kode Aset, Nama, Merk, SN, atau PIC..." class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-indigo-600 focus:bg-white text-sm font-semibold text-slate-900 transition">
                </div>

                {{-- Filter Kategori --}}
                <div>
                    <select name="category_id" class="w-full py-2.5 px-3 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-indigo-600 focus:bg-white text-xs font-bold text-slate-800">
                        <option value="all">-- Semua Kategori --</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryFilter == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Kondisi --}}
                <div>
                    <select name="condition" class="w-full py-2.5 px-3 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-indigo-600 focus:bg-white text-xs font-bold text-slate-800">
                        <option value="all">-- Semua Kondisi --</option>
                        <option value="baik" {{ $conditionFilter === 'baik' ? 'selected' : '' }}>Baik (Normal)</option>
                        <option value="rusak_ringan" {{ $conditionFilter === 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                        <option value="rusak_berat" {{ $conditionFilter === 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                        <option value="hilang" {{ $conditionFilter === 'hilang' ? 'selected' : '' }}>Hilang</option>
                        <option value="dihapuskan" {{ $conditionFilter === 'dihapuskan' ? 'selected' : '' }}>Dihapuskan</option>
                    </select>
                </div>

                {{-- Tombol Filter & Reset --}}
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-sm transition">
                        <i class="fas fa-filter mr-1"></i>Filter
                    </button>
                    <a href="{{ route('inventory.index') }}" class="py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs border border-slate-200 transition" title="Reset Semua Filter">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- DATA ASSET TABLE CONTAINER --}}
    <div class="bg-white rounded-3xl border-2 border-slate-900 shadow-md overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
            <div>
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="fas fa-list-check text-indigo-600"></i>
                    <span>Daftar Aset Terdata</span>
                    <span class="px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-800 text-xs font-extrabold">{{ $assets->total() }} Data</span>
                </h3>
                <p class="text-xs text-slate-500 font-medium mt-0.5">
                    Menampilkan rincian No Urut, Kode Aset, Nama, Spesifikasi, Nilai Perolehan, Penyusutan, & Nilai Buku.
                </p>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-500">Urut: No Urut & Tahun Perolehan</span>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-900 text-white uppercase text-[10px] tracking-wider font-extrabold border-b-2 border-slate-900">
                        <th class="py-3 px-3 text-center w-12">No</th>
                        <th class="py-3 px-3">Kode Aset</th>
                        <th class="py-3 px-4 min-w-[220px]">Nama Aset & Deskripsi</th>
                        <th class="py-3 px-3">Unit / Sekolah</th>
                        <th class="py-3 px-3">Kategori & Lokasi</th>
                        <th class="py-3 px-3 text-center">Tahun & Dana</th>
                        <th class="py-3 px-3 text-center">Jumlah</th>
                        <th class="py-3 px-3 text-right">Harga Pengadaan</th>
                        <th class="py-3 px-3 text-right">Penyusutan / Thn</th>
                        <th class="py-3 px-3 text-right">Akumulasi Depr.</th>
                        <th class="py-3 px-3 text-right">Nilai Buku Saat Ini</th>
                        <th class="py-3 px-3 text-center">Kondisi</th>
                        <th class="py-3 px-3 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($assets as $index => $asset)
                    <tr class="hover:bg-indigo-50/40 transition">
                        {{-- No Urut --}}
                        <td class="py-3.5 px-3 text-center font-black text-slate-900 bg-slate-50/30">
                            {{ $asset->item_number ?: ($assets->firstItem() + $index) }}
                        </td>

                        {{-- Kode Aset --}}
                        <td class="py-3.5 px-3 font-bold">
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('inventory.qr_label', $asset->id) }}" target="_blank" class="w-6 h-6 rounded bg-slate-100 hover:bg-indigo-100 text-slate-600 hover:text-indigo-600 flex items-center justify-center text-xs transition" title="Cetak Stiker Label QR Code">
                                    <i class="fas fa-qrcode"></i>
                                </a>
                                <a href="{{ route('inventory.show', $asset->id) }}" class="inv-mono font-bold text-indigo-700 hover:text-indigo-900 hover:underline">
                                    {{ $asset->asset_code }}
                                </a>
                            </div>
                        </td>

                        {{-- Nama Aset & Deskripsi --}}
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900 text-xs sm:text-sm">
                                <a href="{{ route('inventory.show', $asset->id) }}" class="hover:text-indigo-600 transition">
                                    {{ $asset->name }}
                                </a>
                            </div>
                            @if($asset->brand || $asset->serial_number)
                            <div class="text-[11px] text-slate-500 font-medium mt-0.5">
                                @if($asset->brand) <span class="font-semibold text-slate-700">{{ $asset->brand }}</span> @endif
                                @if($asset->serial_number) <span class="inv-mono text-[10px] text-slate-400">SN: {{ $asset->serial_number }}</span> @endif
                            </div>
                            @endif
                            @if($asset->description)
                            <div class="text-[11px] text-slate-500 line-clamp-1 mt-0.5" title="{{ $asset->description }}">
                                {{ $asset->description }}
                            </div>
                            @endif
                        </td>

                        {{-- Unit --}}
                        <td class="py-3.5 px-3">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-black {{ str_contains(strtoupper($asset->unit_name), 'YAYASAN') ? 'bg-purple-100 text-purple-900 border border-purple-200' : 'bg-slate-100 text-slate-800' }}">
                                <i class="fas fa-building text-[9px]"></i>
                                {{ $asset->unit_name }}
                            </span>
                        </td>

                        {{-- Kategori & Lokasi --}}
                        <td class="py-3.5 px-3">
                            <div class="font-bold text-slate-800 text-[11px]">
                                {{ $asset->category ? $asset->category->name : '-' }}
                            </div>
                            <div class="text-[10px] text-slate-500 flex items-center gap-1 mt-0.5">
                                <i class="fas fa-map-marker-alt text-[9px] text-slate-400"></i>
                                <span>{{ $asset->location ? $asset->location->name : 'Gudang Pusat' }}</span>
                            </div>
                        </td>

                        {{-- Tahun & Dana --}}
                        <td class="py-3.5 px-3 text-center">
                            <span class="inv-mono font-bold text-slate-800 text-xs">{{ $asset->acquisition_year }}</span>
                            <div class="text-[10px] text-slate-400 font-semibold">{{ $asset->funding_source }}</div>
                        </td>

                        {{-- Jumlah --}}
                        <td class="py-3.5 px-3 text-center font-bold text-slate-900 num-col">
                            {{ $asset->quantity }}
                            <span class="text-[10px] font-semibold text-slate-500">{{ $asset->unit }}</span>
                        </td>

                        {{-- Harga Pengadaan --}}
                        <td class="py-3.5 px-3 text-right font-black text-slate-900 num-col">
                            {{ $asset->formatted_acquisition_price }}
                            @if($asset->quantity > 1)
                            <div class="text-[9px] text-slate-400 font-semibold">@ Rp {{ number_format($asset->unit_price, 0, ',', '.') }}</div>
                            @endif
                        </td>

                        {{-- Penyusutan per Tahun --}}
                        <td class="py-3.5 px-3 text-right font-semibold text-rose-600 num-col">
                            @if($asset->depreciation_method === 'none')
                            <span class="text-[10px] font-bold text-slate-400 italic">Bebas Depresiasi</span>
                            @else
                            {{ $asset->formatted_annual_depreciation }}
                            <div class="text-[9px] text-slate-400 font-semibold">{{ $asset->useful_life_years }} Thn</div>
                            @endif
                        </td>

                        {{-- Akumulasi Penyusutan --}}
                        <td class="py-3.5 px-3 text-right font-bold text-rose-700 num-col">
                            {{ $asset->formatted_accumulated_depreciation }}
                        </td>

                        {{-- Nilai Buku Saat Ini --}}
                        <td class="py-3.5 px-3 text-right font-black text-emerald-700 bg-emerald-50/30 num-col text-xs sm:text-sm">
                            {{ $asset->formatted_current_book_value }}
                        </td>

                        {{-- Kondisi --}}
                        <td class="py-3.5 px-3 text-center">
                            @php $cb = $asset->condition_badge; @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black border {{ $cb['class'] }}">
                                <i class="fas {{ $cb['icon'] }} text-[9px]"></i>
                                {{ $cb['label'] }}
                            </span>
                        </td>

                        {{-- Aksi --}}
                        <td class="py-3.5 px-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('inventory.show', $asset->id) }}" class="w-7 h-7 rounded-lg bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white flex items-center justify-center text-xs transition shadow-xs" title="Lihat Profil Aset">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('inventory.edit', $asset->id) }}" class="w-7 h-7 rounded-lg bg-amber-50 hover:bg-amber-600 text-amber-700 hover:text-white flex items-center justify-center text-xs transition shadow-xs" title="Edit Aset">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('inventory.destroy', $asset->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus aset {{ $asset->asset_code }} - {{ $asset->name }}? Tindakan ini tidak dapat dibatalkan.')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white flex items-center justify-center text-xs transition shadow-xs" title="Hapus Aset">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="py-12 text-center text-slate-400">
                            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mx-auto mb-3">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <div class="font-bold text-slate-700 text-sm">Belum Ada Data Aset Sesuai Filter</div>
                            <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1">
                                Silakan ubah filter pencarian atau klik tombol <b>Tambah Aset Baru</b> untuk mulai mendata inventaris.
                            </p>
                            <div class="mt-4">
                                <a href="{{ route('inventory.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold text-xs shadow-md">
                                    <i class="fas fa-plus"></i> Tambah Aset Sekarang
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($assets->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50/50">
            {{ $assets->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
