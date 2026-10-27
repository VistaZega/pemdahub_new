@extends($layout)

@section('title', 'Aset ' . $asset->asset_code . ' - ' . $asset->name . ' - PembdaINVENTORY')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">
<style>
    .inv-font { font-family: 'Plus Jakarta Sans', sans-serif; }
    .inv-mono { font-family: 'JetBrains Mono', monospace; }
    .num-col { font-variant-numeric: tabular-nums; white-space: nowrap !important; }
</style>
@endpush

@section('content')
<div class="inv-font min-h-screen bg-slate-50/70 p-4 sm:p-6 lg:p-8 space-y-6 max-w-6xl mx-auto">

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

    {{-- BREADCRUMB & ACTION BAR --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-400 mb-1">
                <a href="{{ route('inventory.index') }}" class="hover:text-indigo-600 transition">PembdaINVENTORY</a>
                <i class="fas fa-chevron-right text-[9px]"></i>
                <span class="text-slate-700">Profil Aset</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="inv-mono text-indigo-600">[{{ $asset->asset_code }}]</span>
                <span class="truncate max-w-xl">{{ $asset->name }}</span>
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('inventory.qr_label', $asset->id) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md transition">
                <i class="fas fa-qrcode"></i>
                <span>Cetak Label QR</span>
            </a>
            <a href="{{ route('inventory.edit', $asset->id) }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shadow-md transition">
                <i class="fas fa-edit"></i>
                <span>Edit Aset</span>
            </a>
            <form action="{{ route('inventory.destroy', $asset->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data aset ini secara permanen?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs border border-rose-200 transition">
                    <i class="fas fa-trash"></i>
                    <span>Hapus</span>
                </button>
            </form>
            <a href="{{ route('inventory.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white border-2 border-slate-200 hover:border-slate-300 text-slate-700 font-bold text-xs transition">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    {{-- HERO ASSET CARD --}}
    <div class="bg-white rounded-3xl border-2 border-slate-900 p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
            {{-- Foto Aset / Thumbnail --}}
            <div class="w-28 h-28 sm:w-36 sm:h-36 rounded-2xl border-2 border-slate-900 bg-slate-100 flex-shrink-0 overflow-hidden flex items-center justify-center shadow-md">
                @if($asset->photo_path)
                <img src="{{ asset('storage/' . $asset->photo_path) }}" alt="{{ $asset->name }}" class="w-full h-full object-cover">
                @else
                <div class="text-center p-3 text-slate-400">
                    <i class="fas fa-boxes-stacked text-3xl sm:text-4xl text-slate-300 mb-1"></i>
                    <div class="text-[10px] font-bold uppercase tracking-wider">No Photo</div>
                </div>
                @endif
            </div>

            {{-- Detail Header --}}
            <div class="flex-1 space-y-2.5 min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black {{ str_contains(strtoupper($asset->unit_name), 'YAYASAN') ? 'bg-purple-100 text-purple-900 border border-purple-300' : 'bg-indigo-100 text-indigo-900 border border-indigo-300' }}">
                        <i class="fas fa-building text-[10px]"></i>
                        {{ $asset->unit_name }}
                    </span>
                    @php $cb = $asset->condition_badge; @endphp
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black border {{ $cb['class'] }}">
                        <i class="fas {{ $cb['icon'] }} text-[10px]"></i>
                        Kondisi: {{ $cb['label'] }}
                    </span>
                    @php $sb = $asset->status_badge; @endphp
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black border {{ $sb['class'] }}">
                        Status: {{ $sb['label'] }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                        <i class="fas fa-tag text-[9px] text-slate-400"></i>
                        No Urut #{{ $asset->item_number }}
                    </span>
                </div>

                <h2 class="text-xl sm:text-2xl font-black text-slate-900 leading-snug">
                    {{ $asset->name }}
                </h2>

                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-600 font-medium">
                    @if($asset->brand)
                    <div><b class="text-slate-900">Merk:</b> {{ $asset->brand }}</div>
                    @endif
                    @if($asset->serial_number)
                    <div class="inv-mono"><b class="text-slate-900">SN / No Seri:</b> {{ $asset->serial_number }}</div>
                    @endif
                    <div><b class="text-slate-900">Jumlah:</b> {{ $asset->quantity }} {{ $asset->unit }}</div>
                    <div><b class="text-slate-900">Tahun Pengadaan:</b> {{ $asset->acquisition_year }}</div>
                    <div><b class="text-slate-900">Sumber Dana:</b> {{ $asset->funding_source }}</div>
                </div>

                @if($asset->description)
                <p class="text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-200/80 leading-relaxed">
                    {{ $asset->description }}
                </p>
                @endif
            </div>
        </div>
    </div>

    {{-- 4 FINANCIAL METRIC STAT CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Nilai Pengadaan --}}
        <div class="bg-white rounded-2xl border-2 border-slate-900 p-5 shadow-sm space-y-1">
            <div class="flex items-center justify-between text-slate-500 text-xs font-black uppercase">
                <span>Nilai Perolehan Awal</span>
                <i class="fas fa-coins text-indigo-600 text-sm"></i>
            </div>
            <div class="text-xl sm:text-2xl font-black text-slate-900 num-col">
                {{ $asset->formatted_acquisition_price }}
            </div>
            <div class="text-xs text-slate-500 font-semibold">
                {{ $asset->quantity }} {{ $asset->unit }} @ Rp {{ number_format($asset->unit_price, 0, ',', '.') }}
            </div>
        </div>

        {{-- Card 2: Penyusutan per Tahun --}}
        <div class="bg-white rounded-2xl border-2 border-slate-900 p-5 shadow-sm space-y-1">
            <div class="flex items-center justify-between text-slate-500 text-xs font-black uppercase">
                <span>Beban Penyusutan / Thn</span>
                <i class="fas fa-chart-line-down text-rose-600 text-sm"></i>
            </div>
            <div class="text-xl sm:text-2xl font-black text-rose-700 num-col">
                {{ $asset->depreciation_method === 'none' ? 'Rp 0' : $asset->formatted_annual_depreciation }}
            </div>
            <div class="text-xs text-slate-500 font-semibold">
                @if($asset->depreciation_method === 'none')
                Bebas penyusutan (Tanah)
                @else
                Masa Manfaat: {{ $asset->useful_life_years }} Tahun
                @endif
            </div>
        </div>

        {{-- Card 3: Akumulasi Penyusutan --}}
        <div class="bg-white rounded-2xl border-2 border-slate-900 p-5 shadow-sm space-y-1">
            <div class="flex items-center justify-between text-slate-500 text-xs font-black uppercase">
                <span>Akumulasi Penyusutan</span>
                <i class="fas fa-history text-amber-600 text-sm"></i>
            </div>
            <div class="text-xl sm:text-2xl font-black text-rose-800 num-col">
                {{ $asset->formatted_accumulated_depreciation }}
            </div>
            <div class="text-xs text-slate-500 font-semibold">
                Penyusutan terhitung s.d. saat ini
            </div>
        </div>

        {{-- Card 4: Nilai Buku Saat Ini --}}
        <div class="bg-white rounded-2xl border-2 border-slate-900 p-5 shadow-sm space-y-1 bg-gradient-to-br from-white to-emerald-50">
            <div class="flex items-center justify-between text-slate-500 text-xs font-black uppercase">
                <span>Nilai Buku Saat Ini</span>
                <i class="fas fa-wallet text-emerald-600 text-sm"></i>
            </div>
            <div class="text-xl sm:text-2xl font-black text-emerald-700 num-col">
                {{ $asset->formatted_current_book_value }}
            </div>
            <div class="text-xs text-emerald-800 font-semibold">
                Nilai wajar aktiva bersih di neraca
            </div>
        </div>
    </div>

    {{-- SPECIFICATION DETAILS & PIC --}}
    <div class="bg-white rounded-3xl border-2 border-slate-900 p-6 sm:p-8 shadow-sm space-y-4">
        <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
            <i class="fas fa-circle-info text-indigo-600"></i>
            <span>Rincian Spesifikasi & Lokasi Penempatan</span>
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-y-4 gap-x-6 text-xs">
            <div>
                <span class="text-slate-400 font-bold block uppercase text-[10px]">Kategori Aset</span>
                <span class="font-extrabold text-slate-800 text-sm">{{ $asset->category ? $asset->category->name : '-' }}</span>
            </div>
            <div>
                <span class="text-slate-400 font-bold block uppercase text-[10px]">Lokasi / Ruangan</span>
                <span class="font-extrabold text-slate-800 text-sm">{{ $asset->location ? $asset->location->full_location : 'Gudang Pusat' }}</span>
            </div>
            <div>
                <span class="text-slate-400 font-bold block uppercase text-[10px]">Penanggung Jawab (PIC)</span>
                <span class="font-extrabold text-indigo-700 text-sm">{{ $asset->pic_name ?: ($asset->picUser ? $asset->picUser->name : 'Pimpinan Unit / Yayasan') }}</span>
            </div>
            <div>
                <span class="text-slate-400 font-bold block uppercase text-[10px]">Tanggal Pengadaan</span>
                <span class="font-bold text-slate-700">{{ $asset->acquisition_date ? $asset->acquisition_date->translatedFormat('d F Y') : '-' }}</span>
            </div>
            <div>
                <span class="text-slate-400 font-bold block uppercase text-[10px]">Nilai Residu / Sisa</span>
                <span class="font-bold text-slate-700">Rp {{ number_format($asset->salvage_value, 0, ',', '.') }}</span>
            </div>
            <div>
                <span class="text-slate-400 font-bold block uppercase text-[10px]">Metode Penyusutan</span>
                <span class="font-bold text-slate-700">{{ $asset->depreciation_method === 'none' ? 'Bebas Penyusutan' : 'Garis Lurus (Straight Line)' }}</span>
            </div>
            @if($asset->notes)
            <div class="sm:col-span-3 pt-2 border-t border-slate-100">
                <span class="text-slate-400 font-bold block uppercase text-[10px] mb-1">Catatan Tambahan</span>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs font-medium">
                    {{ $asset->notes }}
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- TABS: JADWAL PENYUSUTAN TAHUNAN & RIWAYAT MUTASI --}}
    <div class="bg-white rounded-3xl border-2 border-slate-900 shadow-sm overflow-hidden" x-data="{ activeTab: 'schedule' }">
        {{-- TAB HEADERS --}}
        <div class="flex border-b-2 border-slate-900 bg-slate-50/70">
            <button @click="activeTab = 'schedule'" :class="activeTab === 'schedule' ? 'bg-white text-indigo-700 border-b-2 border-indigo-600 font-black' : 'text-slate-600 font-bold hover:bg-slate-100'" class="px-6 py-3.5 text-xs sm:text-sm flex items-center gap-2 transition">
                <i class="fas fa-calendar-alt"></i>
                <span>Simulasi Jadwal Penyusutan Tahunan</span>
            </button>
            <button @click="activeTab = 'mutations'" :class="activeTab === 'mutations' ? 'bg-white text-indigo-700 border-b-2 border-indigo-600 font-black' : 'text-slate-600 font-bold hover:bg-slate-100'" class="px-6 py-3.5 text-xs sm:text-sm flex items-center gap-2 transition">
                <i class="fas fa-history"></i>
                <span>Riwayat Mutasi & Perawatan ({{ $asset->mutations->count() }})</span>
            </button>
        </div>

        {{-- TAB CONTENT 1: DEPRECIATION SCHEDULE TABLE --}}
        <div x-show="activeTab === 'schedule'" class="p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between text-xs text-slate-500">
                <span>Tabel proyeksi nilai buku aset dari tahun perolehan hingga habis masa manfaat ekonomis.</span>
                <span class="font-black text-indigo-600">Metode: Garis Lurus (Straight Line)</span>
            </div>

            <div class="overflow-x-auto border border-slate-200 rounded-2xl">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-900 text-white font-extrabold uppercase text-[10px]">
                            <th class="py-3 px-4">Tahun / Periode</th>
                            <th class="py-3 px-4 text-right">Nilai Buku Awal (Rp)</th>
                            <th class="py-3 px-4 text-right">Beban Penyusutan (Rp)</th>
                            <th class="py-3 px-4 text-right">Akumulasi Penyusutan (Rp)</th>
                            <th class="py-3 px-4 text-right">Nilai Buku Akhir (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($depreciationSchedule as $row)
                        <tr class="hover:bg-indigo-50/30 transition">
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $row['year'] }}</td>
                            <td class="py-3 px-4 text-right num-col">Rp {{ number_format($row['beginning_value'], 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right num-col text-rose-600 font-semibold">Rp {{ number_format($row['depreciation'], 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right num-col text-rose-700 font-bold">Rp {{ number_format($row['accumulated'], 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right num-col text-emerald-700 font-black bg-emerald-50/20">Rp {{ number_format($row['ending_value'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TAB CONTENT 2: MUTATIONS & MAINTENANCE LOGS --}}
        <div x-show="activeTab === 'mutations'" class="p-5 sm:p-6 space-y-5" style="display: none;">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <p class="text-xs text-slate-500 font-medium">
                    Catatan pemindahan lokasi, riwayat servis/perbaikan, dan perubahan kondisi fisik aset.
                </p>
                <button type="button" onclick="document.getElementById('modalMutation').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-sm transition">
                    <i class="fas fa-plus"></i> Catat Mutasi / Servis Baru
                </button>
            </div>

            <div class="space-y-3">
                @forelse($asset->mutations as $m)
                <div class="p-4 rounded-2xl border-2 border-slate-200 bg-slate-50/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-indigo-100 text-indigo-800">
                                {{ str_replace('_', ' ', $m->type) }}
                            </span>
                            <span class="inv-mono text-xs font-bold text-slate-700">
                                {{ $m->mutation_date ? $m->mutation_date->translatedFormat('d F Y') : '-' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-800 font-bold">{{ $m->description }}</p>
                        <div class="flex flex-wrap items-center gap-3 text-[11px] text-slate-500">
                            @if($m->toLocation)
                            <span><i class="fas fa-map-marker-alt mr-1"></i>Lokasi: <b>{{ $m->toLocation->name }}</b></span>
                            @endif
                            @if($m->to_condition)
                            <span><i class="fas fa-heart-pulse mr-1"></i>Kondisi: <b>{{ ucfirst(str_replace('_', ' ', $m->to_condition)) }}</b></span>
                            @endif
                            @if($m->performer)
                            <span><i class="fas fa-user mr-1"></i>Petugas: <b>{{ $m->performer->name }}</b></span>
                            @endif
                        </div>
                    </div>

                    @if($m->cost > 0)
                    <div class="text-right">
                        <span class="text-[10px] font-bold text-slate-400 block uppercase">Biaya Servis</span>
                        <span class="text-sm font-black text-rose-700 inv-mono">Rp {{ number_format($m->cost, 0, ',', '.') }}</span>
                    </div>
                    @endif
                </div>
                @empty
                <div class="py-8 text-center text-slate-400">
                    <i class="fas fa-history text-2xl text-slate-300 mb-2"></i>
                    <p class="text-xs font-bold text-slate-600">Belum ada riwayat mutasi atau servis untuk aset ini.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

{{-- MODAL CATAT MUTASI / SERVIS BARU --}}
<div id="modalMutation" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl border-2 border-slate-900 shadow-2xl max-w-lg w-full p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                <i class="fas fa-tools text-indigo-600"></i>
                <span>Catat Mutasi / Servis Aset</span>
            </h3>
            <button type="button" onclick="document.getElementById('modalMutation').classList.add('hidden')" class="w-7 h-7 rounded-lg bg-slate-100 text-slate-500 hover:text-slate-800 flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="{{ route('inventory.mutations.store', $asset->id) }}" method="POST" class="space-y-4">
            @csrf
            <div class="space-y-1.5">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-700">Tanggal Tindakan</label>
                <input type="date" name="mutation_date" value="{{ date('Y-m-d') }}" required class="w-full py-2 px-3 rounded-xl bg-slate-50 border-2 border-slate-200 text-xs font-bold text-slate-900">
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-700">Jenis Tindakan</label>
                <select name="type" required class="w-full py-2 px-3 rounded-xl bg-slate-50 border-2 border-slate-200 text-xs font-bold text-slate-900">
                    <option value="mutasi_lokasi">Pemindahan Lokasi / Ruangan</option>
                    <option value="perbaikan">Perbaikan / Servis Rutin</option>
                    <option value="pengecekan_rutin">Pengecekan Fisik Berkala</option>
                    <option value="perubahan_kondisi">Perubahan Kondisi Fisik</option>
                    <option value="penghapusan">Penghapusan / Pemusnahan Aset</option>
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-700">Pindah ke Ruangan (Opsional)</label>
                <select name="to_location_id" class="w-full py-2 px-3 rounded-xl bg-slate-50 border-2 border-slate-200 text-xs font-bold text-slate-900">
                    <option value="">-- Tetap di Ruangan Saat Ini --</option>
                    @foreach(\App\Models\InventoryLocation::with('school')->get() as $loc)
                    <option value="{{ $loc->id }}" {{ $asset->location_id == $loc->id ? 'selected' : '' }}>
                        {{ $loc->school ? '[' . strtoupper($loc->school->type) . '] ' : '[YAYASAN] ' }}{{ $loc->full_location }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-700">Kondisi Setelah Tindakan</label>
                <select name="to_condition" class="w-full py-2 px-3 rounded-xl bg-slate-50 border-2 border-slate-200 text-xs font-bold text-slate-900">
                    <option value="baik" {{ $asset->condition === 'baik' ? 'selected' : '' }}>Baik</option>
                    <option value="rusak_ringan" {{ $asset->condition === 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                    <option value="rusak_berat" {{ $asset->condition === 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                    <option value="hilang">Hilang</option>
                    <option value="dihapuskan">Dihapuskan</option>
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-700">Biaya Tindakan / Perbaikan (Rp)</label>
                <input type="number" step="any" name="cost" value="0" min="0" class="w-full py-2 px-3 rounded-xl bg-slate-50 border-2 border-slate-200 text-xs font-bold text-slate-900 inv-mono">
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-700">Keterangan / Rincian Pekerjaan</label>
                <textarea name="description" rows="2" required placeholder="Contoh: Penggantian oli mesin dan filter / Pindah ke Lab Komputer SMA" class="w-full py-2 px-3 rounded-xl bg-slate-50 border-2 border-slate-200 text-xs font-medium text-slate-900"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modalMutation').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs hover:bg-slate-200 transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 text-white font-black text-xs hover:bg-indigo-500 shadow-md transition">Simpan Mutasi</button>
            </div>
        </form>
    </div>
</div>
@endsection
