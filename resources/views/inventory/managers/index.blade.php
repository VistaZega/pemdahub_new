@extends($layout)

@section('title', 'Akun Yang Ditunjuk - PembdaINVENTORY')

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

    {{-- BREADCRUMB & HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-400 mb-1">
                <a href="{{ route('inventory.index') }}" class="hover:text-indigo-600 transition">PembdaINVENTORY</a>
                <i class="fas fa-chevron-right text-[9px]"></i>
                <span class="text-slate-700">Wewenang & Akun Ditunjuk</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <i class="fas fa-user-shield text-purple-600"></i>
                <span>Kelola Akun Pengelola Aset (Akun yang Ditunjuk)</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1">
                Tentukan akun guru, pegawai, atau staf dari unit mana pun yang diberi wewenang resmi oleh Yayasan untuk mengelola PembdaINVENTORY.
            </p>
        </div>

        <div>
            <a href="{{ route('inventory.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border-2 border-slate-200 hover:border-slate-300 text-slate-700 font-bold text-xs shadow-sm transition">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali ke Inventaris</span>
            </a>
        </div>
    </div>

    {{-- BANNER INFORMASI KEBIJAKAN YAYASAN --}}
    <div class="bg-gradient-to-r from-purple-900 via-indigo-900 to-slate-900 text-white rounded-3xl p-6 border-2 border-slate-900 shadow-lg flex flex-col sm:flex-row sm:items-center gap-5">
        <div class="w-12 h-12 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-2xl text-amber-300 flex-shrink-0">
            <i class="fas fa-landmark"></i>
        </div>
        <div class="space-y-1">
            <h3 class="text-sm font-black uppercase tracking-wider text-purple-200">Hak Prerogatif Pengurus Yayasan</h3>
            <p class="text-xs text-slate-200 font-medium leading-relaxed">
                Sesuai kebijakan: Menu PembdaINVENTORY hanya dapat diakses & dikelola oleh <b>Yayasan</b> dan <b>Akun yang Ditunjuk</b> secara sah. Akun yang Anda tambahkan di bawah ini akan otomatis mendapatkan menu PembdaINVENTORY di sidebar portal akun masing-masing.
            </p>
        </div>
    </div>

    {{-- FORM PENUNJUKAN AKUN BARU --}}
    <div class="bg-white rounded-3xl border-2 border-slate-900 p-6 sm:p-8 shadow-sm space-y-5">
        <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3">
            <div class="w-7 h-7 rounded-lg bg-purple-600 text-white flex items-center justify-center text-xs font-black">
                <i class="fas fa-user-plus"></i>
            </div>
            <h2 class="text-base font-black text-slate-900">Form Penunjukan Pengelola Aset Baru</h2>
        </div>

        <form action="{{ route('inventory.managers.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                {{-- Pilih Pengguna --}}
                <div class="space-y-1.5 sm:col-span-2">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Pilih Guru / Pegawai / Staf yang Ditunjuk <span class="text-rose-500">*</span>
                    </label>
                    <select name="user_id" required class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-purple-600 focus:bg-white text-xs font-bold text-slate-900 transition">
                        <option value="">-- Cari & Pilih Akun Pengguna --</option>
                        @foreach($availableUsers as $u)
                        <option value="{{ $u->id }}">
                            {{ $u->name }} ({{ $u->username }}) — Role: {{ ucfirst($u->role) }} | Unit: {{ $u->school ? $u->school->name : 'Yayasan' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Jabatan Penunjukan --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Jabatan / Peran Pengelola <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="role_title" value="Petugas Pengelola Aset" placeholder="Contoh: Koordinator Sarpras SMK / Staf Inventaris" required class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-purple-600 focus:bg-white text-xs font-bold text-slate-900 transition">
                </div>

                {{-- Cakupan Unit Sekolah --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Cakupan Wewenang Unit <span class="text-rose-500">*</span>
                    </label>
                    <select name="school_id" class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-purple-600 focus:bg-white text-xs font-bold text-slate-900 transition">
                        <option value="all">🏢 Mengelola Seluruh Unit & Yayasan (Semua Aset)</option>
                        @foreach($schools as $sch)
                        @if(strtolower($sch->type) !== 'yayasan')
                        <option value="{{ $sch->id }}">🏫 Khusus {{ $sch->name }}</option>
                        @endif
                        @endforeach
                    </select>
                </div>

                {{-- Catatan Penunjukan --}}
                <div class="sm:col-span-2 space-y-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Dasar Surat Keputusan / Catatan Tambahan (Opsional)
                    </label>
                    <input type="text" name="notes" placeholder="Contoh: Berdasarkan SK Yayasan No. 04/SK-YYS/2026 tentang Tim Sarpras" class="w-full py-2.5 px-3.5 rounded-xl bg-slate-50 border-2 border-slate-200 focus:border-purple-600 focus:bg-white text-xs font-medium text-slate-900 transition">
                </div>
            </div>

            {{-- CHECKLIST HAK AKSES --}}
            <div class="p-4 rounded-2xl bg-purple-50/60 border border-purple-200 space-y-2">
                <span class="text-xs font-black uppercase tracking-wider text-purple-900 block">Rincian Hak Akses Modul:</span>
                <div class="flex flex-wrap items-center gap-4 text-xs font-bold text-slate-800">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="can_create" value="1" checked class="w-4 h-4 text-purple-600 rounded">
                        <span>Tambah Aset Baru</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="can_edit" value="1" checked class="w-4 h-4 text-purple-600 rounded">
                        <span>Edit & Perbarui Aset</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="can_delete" value="1" checked class="w-4 h-4 text-purple-600 rounded">
                        <span>Hapus Data Aset</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="can_export" value="1" checked class="w-4 h-4 text-purple-600 rounded">
                        <span>Cetak Label & Export Laporan</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-6 py-3 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-black text-xs shadow-lg shadow-purple-600/30 border-2 border-purple-700 transition transform active:scale-95 flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    <span>Tunjuk & Berikan Wewenang Kelola</span>
                </button>
            </div>
        </form>
    </div>

    {{-- TABEL DAFTAR AKUN YANG DITUNJUK --}}
    <div class="bg-white rounded-3xl border-2 border-slate-900 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                <i class="fas fa-users-gear text-indigo-600"></i>
                <span>Daftar Akun Pengelola Aset Aktif</span>
                <span class="px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800 text-xs font-extrabold">{{ $managers->count() }} Akun</span>
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-900 text-white uppercase text-[10px] tracking-wider font-extrabold">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Pengguna Ditunjuk</th>
                        <th class="py-3 px-4">Jabatan Inventaris</th>
                        <th class="py-3 px-4">Cakupan Unit</th>
                        <th class="py-3 px-4">Hak Wewenang</th>
                        <th class="py-3 px-4">Ditunjuk Oleh</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($managers as $idx => $m)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3.5 px-4 text-center font-bold text-slate-900">{{ $idx + 1 }}</td>
                        
                        {{-- Pengguna --}}
                        <td class="py-3.5 px-4">
                            <div class="font-extrabold text-slate-900 text-sm">{{ $m->user ? $m->user->name : 'Akun Terhapus' }}</div>
                            <div class="text-[11px] text-slate-500 font-medium mt-0.5">
                                <span class="inv-mono text-slate-600 font-semibold">{{ $m->user?->username }}</span> • 
                                <span class="text-slate-500">{{ $m->user?->school ? $m->user->school->name : 'Yayasan' }}</span>
                            </div>
                        </td>

                        {{-- Jabatan --}}
                        <td class="py-3.5 px-4 font-bold text-slate-800">
                            {{ $m->role_title }}
                        </td>

                        {{-- Cakupan --}}
                        <td class="py-3.5 px-4">
                            @if($m->school)
                            <span class="px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-800 font-bold text-[10px] border border-indigo-200">
                                {{ $m->school->name }}
                            </span>
                            @else
                            <span class="px-2.5 py-1 rounded-md bg-purple-50 text-purple-900 font-bold text-[10px] border border-purple-200">
                                🏢 Semua Unit & Yayasan
                            </span>
                            @endif
                        </td>

                        {{-- Hak Wewenang --}}
                        <td class="py-3.5 px-4">
                            <div class="flex flex-wrap gap-1 text-[9px] font-extrabold">
                                @if($m->can_create) <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">+Tambah</span> @endif
                                @if($m->can_edit) <span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-800">✎Edit</span> @endif
                                @if($m->can_delete) <span class="px-1.5 py-0.5 rounded bg-rose-100 text-rose-800">✕Hapus</span> @endif
                                @if($m->can_export) <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800">⎙Cetak</span> @endif
                            </div>
                        </td>

                        {{-- Ditunjuk Oleh --}}
                        <td class="py-3.5 px-4 text-[11px]">
                            <div class="font-bold text-slate-800">{{ $m->appointedBy ? $m->appointedBy->name : 'Ketua Yayasan' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $m->appointed_at ? $m->appointed_at->translatedFormat('d M Y') : '-' }}</div>
                        </td>

                        {{-- Status Aktif --}}
                        <td class="py-3.5 px-4 text-center">
                            @if($m->is_active)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                <i class="fas fa-check-circle"></i> Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-slate-100 text-slate-600 border border-slate-300">
                                <i class="fas fa-pause-circle"></i> Nonaktif
                            </span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- Toggle Active --}}
                                <form action="{{ route('inventory.managers.toggle', $m->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-7 h-7 rounded-lg {{ $m->is_active ? 'bg-amber-50 hover:bg-amber-600 text-amber-700 hover:text-white' : 'bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white' }} flex items-center justify-center text-xs transition shadow-xs" title="{{ $m->is_active ? 'Nonaktifkan Akses' : 'Aktifkan Akses' }}">
                                        <i class="fas {{ $m->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                    </button>
                                </form>

                                {{-- Cabut / Hapus --}}
                                <form action="{{ route('inventory.managers.destroy', $m->id) }}" method="POST" onsubmit="return confirm('Cabut wewenang pengelola aset untuk {{ $m->user?->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white flex items-center justify-center text-xs transition shadow-xs" title="Cabut Wewenang">
                                        <i class="fas fa-user-xmark"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400">
                            <i class="fas fa-user-shield text-2xl text-slate-300 mb-2"></i>
                            <p class="text-xs font-bold text-slate-600">Belum ada akun yang ditunjuk oleh Yayasan.</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Gunakan form di atas untuk menunjuk pengelola aset.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
