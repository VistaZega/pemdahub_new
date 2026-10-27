@extends($layout)

@section('title', 'Kategori & Ruangan Aset - PembdaINVENTORY')

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
<div class="inv-font min-h-screen bg-slate-50/70 p-4 sm:p-6 lg:p-8 space-y-6 max-w-7xl mx-auto">

    {{-- ALERT NOTIFIKASI --}}
    @if(session('success'))
    <div class="flex items-center gap-3 p-4 bg-emerald-50 border-2 border-emerald-500 rounded-2xl text-emerald-900 shadow-sm animate-fade-in">
        <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold text-base shadow-sm">
            <i class="fas fa-check"></i>
        </div>
        <div class="flex-1 font-semibold text-sm">{{ session('success') }}</div>
    </div>
    @endif

    @if(session('error'))
    <div class="flex items-center gap-3 p-4 bg-rose-50 border-2 border-rose-500 rounded-2xl text-rose-900 shadow-sm animate-fade-in">
        <div class="w-9 h-9 rounded-xl bg-rose-500 text-white flex items-center justify-center font-bold text-base shadow-sm">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="flex-1 font-semibold text-sm">{{ session('error') }}</div>
    </div>
    @endif

    {{-- BREADCRUMB & HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-400 mb-1">
                <a href="{{ route('inventory.index') }}" class="hover:text-indigo-600 transition">PembdaINVENTORY</a>
                <i class="fas fa-chevron-right text-[9px]"></i>
                <span class="text-slate-700">Pengaturan Master</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <i class="fas fa-sliders-h text-indigo-600"></i>
                <span>Master Kategori Aset & Ruangan Penempatan</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1">
                Kelola klasifikasi aktiva tetap dan daftar ruangan pada seluruh unit sekolah dan yayasan.
            </p>
        </div>

        <div>
            <a href="{{ route('inventory.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border-2 border-slate-200 hover:border-slate-300 text-slate-700 font-bold text-xs shadow-sm transition">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali ke Inventaris</span>
            </a>
        </div>
    </div>

    {{-- 2-COLUMN GRID: KATEGORI & RUANGAN --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- KOLOM 1: MASTER KATEGORI ASET --}}
        <div class="space-y-6">
            {{-- Form Tambah Kategori --}}
            <div class="bg-white rounded-3xl border-2 border-slate-900 p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                    <div class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs font-black">
                        <i class="fas fa-tags"></i>
                    </div>
                    <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider">Tambah Kategori Aset</h2>
                </div>

                <form action="{{ route('inventory.settings.categories.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="block text-[11px] font-black uppercase text-slate-700">Nama Kategori *</label>
                            <input type="text" name="name" required placeholder="Contoh: Alat Praktik Musik" class="w-full py-2 px-3 rounded-xl bg-slate-50 border-2 border-slate-200 text-xs font-bold text-slate-900">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[11px] font-black uppercase text-slate-700">Kode Kategori *</label>
                            <input type="text" name="code" required placeholder="Contoh: MSK" class="w-full py-2 px-3 rounded-xl bg-slate-50 border-2 border-slate-200 text-xs font-bold text-slate-900 uppercase inv-mono">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[11px] font-black uppercase text-slate-700">Masa Manfaat (Tahun) *</label>
                            <input type="number" name="useful_life_years" value="5" min="0" required class="w-full py-2 px-3 rounded-xl bg-slate-50 border-2 border-slate-200 text-xs font-bold text-slate-900 inv-mono">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[11px] font-black uppercase text-slate-700">Metode Depresiasi *</label>
                            <select name="depreciation_method" required class="w-full py-2 px-3 rounded-xl bg-slate-50 border-2 border-slate-200 text-xs font-bold text-slate-900">
                                <option value="straight_line">Garis Lurus</option>
                                <option value="none">Tanpa Depresiasi (Tanah)</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2 space-y-1">
                            <label class="block text-[11px] font-black uppercase text-slate-700">Keterangan Singkat</label>
                            <input type="text" name="description" placeholder="Deskripsi jenis barang dalam kategori ini..." class="w-full py-2 px-3 rounded-xl bg-slate-50 border-2 border-slate-200 text-xs font-medium text-slate-900">
                        </div>
                    </div>
                    <div class="flex justify-end pt-1">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs shadow-md transition">
                            <i class="fas fa-plus mr-1"></i> Simpan Kategori
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tabel Kategori Saat Ini --}}
            <div class="bg-white rounded-3xl border-2 border-slate-900 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-200 bg-slate-50/50">
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center justify-between">
                        <span>Daftar Kategori Terdaftar</span>
                        <span class="text-indigo-600">{{ $categories->count() }} Kategori</span>
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-900 text-white uppercase text-[10px]">
                                <th class="py-2.5 px-3">Kode</th>
                                <th class="py-2.5 px-3">Nama Kategori</th>
                                <th class="py-2.5 px-2 text-center">Manfaat</th>
                                <th class="py-2.5 px-2 text-center">Aset</th>
                                <th class="py-2.5 px-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                            @foreach($categories as $cat)
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-3 inv-mono font-bold text-indigo-700">{{ $cat->code }}</td>
                                <td class="py-2.5 px-3 font-bold text-slate-900">{{ $cat->name }}</td>
                                <td class="py-2.5 px-2 text-center font-bold">{{ $cat->useful_life_years }} Thn</td>
                                <td class="py-2.5 px-2 text-center font-extrabold text-indigo-600">{{ $cat->assets_count }}</td>
                                <td class="py-2.5 px-3 text-center">
                                    <form action="{{ route('inventory.settings.categories.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('Hapus kategori {{ $cat->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-6 h-6 rounded bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white inline-flex items-center justify-center text-[10px] transition" title="Hapus Kategori">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- KOLOM 2: MASTER LOKASI & RUANGAN PENEMPATAN --}}
        <div class="space-y-6">
            {{-- Form Tambah Lokasi --}}
            <div class="bg-white rounded-3xl border-2 border-slate-900 p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                    <div class="w-7 h-7 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs font-black">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider">Tambah Lokasi / Ruangan Baru</h2>
                </div>

                <form action="{{ route('inventory.settings.locations.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="block text-[11px] font-black uppercase text-slate-700">Unit Sekolah / Yayasan *</label>
                            <select name="school_id" required class="w-full py-2 px-3 rounded-xl bg-slate-50 border-2 border-slate-200 text-xs font-bold text-slate-900">
                                <option value="yayasan">🏛️ Yayasan Perguruan Pembda</option>
                                @foreach($schools as $sch)
                                @if(strtolower($sch->type) !== 'yayasan')
                                <option value="{{ $sch->id }}">🏫 {{ $sch->name }}</option>
                                @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[11px] font-black uppercase text-slate-700">Nama Ruangan *</label>
                            <input type="text" name="name" required placeholder="Contoh: Lab Komputer 2" class="w-full py-2 px-3 rounded-xl bg-slate-50 border-2 border-slate-200 text-xs font-bold text-slate-900">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[11px] font-black uppercase text-slate-700">Nama Gedung</label>
                            <input type="text" name="building_name" placeholder="Contoh: Gedung Laboratorium Lt. 2" class="w-full py-2 px-3 rounded-xl bg-slate-50 border-2 border-slate-200 text-xs font-bold text-slate-900">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[11px] font-black uppercase text-slate-700">No Ruang / Kode</label>
                            <input type="text" name="room_number" placeholder="Contoh: LAB-02" class="w-full py-2 px-3 rounded-xl bg-slate-50 border-2 border-slate-200 text-xs font-bold text-slate-900 inv-mono uppercase">
                        </div>
                        <div class="sm:col-span-2 space-y-1">
                            <label class="block text-[11px] font-black uppercase text-slate-700">Penanggung Jawab Ruangan</label>
                            <input type="text" name="pic_name" placeholder="Contoh: Kepala Lab / Guru Pembina" class="w-full py-2 px-3 rounded-xl bg-slate-50 border-2 border-slate-200 text-xs font-bold text-slate-900">
                        </div>
                    </div>
                    <div class="flex justify-end pt-1">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs shadow-md transition">
                            <i class="fas fa-plus mr-1"></i> Simpan Ruangan
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tabel Ruangan Saat Ini --}}
            <div class="bg-white rounded-3xl border-2 border-slate-900 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-200 bg-slate-50/50">
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center justify-between">
                        <span>Daftar Ruangan Terdaftar</span>
                        <span class="text-emerald-600">{{ $locations->count() }} Lokasi</span>
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-900 text-white uppercase text-[10px]">
                                <th class="py-2.5 px-3">Unit</th>
                                <th class="py-2.5 px-3">Nama Ruangan</th>
                                <th class="py-2.5 px-3">Gedung</th>
                                <th class="py-2.5 px-2 text-center">Aset</th>
                                <th class="py-2.5 px-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                            @foreach($locations as $loc)
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-3 font-bold text-slate-800">
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black {{ $loc->school ? 'bg-indigo-100 text-indigo-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ $loc->school ? strtoupper($loc->school->type) : 'YAYASAN' }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 font-bold text-slate-900">
                                    {{ $loc->name }}
                                    @if($loc->room_number) <span class="inv-mono text-[10px] text-slate-400 font-normal">({{ $loc->room_number }})</span> @endif
                                </td>
                                <td class="py-2.5 px-3 text-[11px] text-slate-500">{{ $loc->building_name ?: '-' }}</td>
                                <td class="py-2.5 px-2 text-center font-extrabold text-emerald-600">{{ $loc->assets_count }}</td>
                                <td class="py-2.5 px-3 text-center">
                                    <form action="{{ route('inventory.settings.locations.destroy', $loc->id) }}" method="POST" onsubmit="return confirm('Hapus ruangan {{ $loc->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-6 h-6 rounded bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white inline-flex items-center justify-center text-[10px] transition" title="Hapus Ruangan">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
