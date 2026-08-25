@extends('layouts.admin')

@section('title', 'Manajemen Ekstrakurikuler & Kegiatan Non-Akademik')

@section('content')
<div class="space-y-6">
    {{-- Header Banner --}}
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-purple-950 rounded-2xl p-6 sm:p-7 text-white relative overflow-hidden shadow-lg border border-slate-800">
        <div class="absolute -top-12 -right-12 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2 text-xs font-semibold text-indigo-300">
                    <span>Pengembangan Karakter & Non-Akademik</span>
                    <span>&bull;</span>
                    <span class="bg-indigo-500/30 px-2 py-0.5 rounded-full text-white">Terintegrasi DNA & Space</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-3">
                    <span>🎨 Ekstrakurikuler & Unit Kegiatan</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 max-w-2xl leading-relaxed">
                    Kelola unit kegiatan non-akademik, struktur pengurus (Pembina/PKS, Ketua, Sekretaris, Bendahara), persetujuan anggota siswa, dan kanal Pembda Space.
                </p>
            </div>
            <button onclick="document.getElementById('modalAddEkskul').classList.remove('hidden')" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl font-bold text-xs shadow-md transition flex items-center gap-2 flex-shrink-0 active:scale-95">
                <i class="fas fa-plus"></i>
                <span>Tambah Unit Kegiatan</span>
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-black flex-shrink-0">
                🏛️
            </div>
            <div>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Total Unit Kegiatan</p>
                <p class="text-2xl font-black text-slate-900">{{ $stats['total_units'] }} <span class="text-xs font-normal text-slate-400">Unit Ekskul</span></p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-black flex-shrink-0">
                👥
            </div>
            <div>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Total Anggota Aktif</p>
                <p class="text-2xl font-black text-slate-900">{{ $stats['total_members'] }} <span class="text-xs font-normal text-slate-400">Siswa Terdaftar</span></p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-black flex-shrink-0">
                📅
            </div>
            <div>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">Log Kegiatan & Latihan</p>
                <p class="text-2xl font-black text-slate-900">{{ $stats['total_activities'] }} <span class="text-xs font-normal text-slate-400">Sesi Tercatat</span></p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            @if(in_array(auth()->user()->role, ['superadmin', 'admin_yayasan', 'yayasan']))
            <div class="sm:col-span-4">
                <select name="school_id" onchange="this.form.submit()" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-slate-50 py-2.5 px-3 focus:bg-white">
                    <option value="">-- Seluruh Unit Sekolah (3 Sekolah Aktif) --</option>
                    @foreach($schools as $sch)
                    <option value="{{ $sch->id }}" {{ request('school_id') == $sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="sm:col-span-3">
                <select name="category" onchange="this.form.submit()" class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-slate-50 py-2.5 px-3 focus:bg-white">
                    <option value="">-- Semua Kategori --</option>
                    <option value="pramuka" {{ request('category') == 'pramuka' ? 'selected' : '' }}>⚜️ Gerakan Pramuka</option>
                    <option value="paskibraka" {{ request('category') == 'paskibraka' ? 'selected' : '' }}>🇮🇩 Paskibraka</option>
                    <option value="seni_budaya" {{ request('category') == 'seni_budaya' ? 'selected' : '' }}>🎭 Seni & Budaya</option>
                    <option value="olahraga" {{ request('category') == 'olahraga' ? 'selected' : '' }}>⚽ Olahraga & Atletik</option>
                    <option value="sains_it" {{ request('category') == 'sains_it' ? 'selected' : '' }}>💻 Sains & IT</option>
                    <option value="keagamaan" {{ request('category') == 'keagamaan' ? 'selected' : '' }}>✝️ Keagamaan</option>
                </select>
            </div>

            <div class="sm:col-span-4">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama ekskul / pembina..." class="w-full text-xs rounded-xl border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 focus:bg-white">
                    <i class="fas fa-search absolute left-3 top-3 text-slate-400 text-xs"></i>
                </div>
            </div>

            <div class="sm:col-span-1 flex gap-2">
                <button type="submit" class="w-full py-2.5 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">Cari</button>
            </div>
        </form>
    </div>

    {{-- Grid Daftar Ekskul --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($extracurriculars as $ekskul)
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col hover:shadow-md hover:border-indigo-300 transition duration-200 group">
            <div class="p-5 flex-1 space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-50 to-purple-100 border border-indigo-100 flex items-center justify-center text-2xl shadow-2xs group-hover:scale-105 transition">
                            {{ $ekskul->display_icon }}
                        </div>
                        <div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-700">
                                {{ $ekskul->category_label }}
                            </span>
                            <h3 class="font-black text-slate-900 text-sm mt-1 leading-snug group-hover:text-indigo-600 transition">
                                {{ $ekskul->name }}
                            </h3>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                    {{ $ekskul->description ?: 'Unit kegiatan pembinaan bakat dan karakter siswa Perguruan Pembda.' }}
                </p>

                {{-- Info Pill --}}
                <div class="space-y-2 text-xs bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <div class="flex items-center justify-between text-slate-600">
                        <span class="flex items-center gap-1.5 text-slate-500"><i class="fas fa-school text-[11px]"></i> Unit:</span>
                        <span class="font-bold text-slate-800">{{ $ekskul->school->short_name ?: $ekskul->school->name }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <span class="flex items-center gap-1.5 text-slate-500"><i class="fas fa-user-tie text-[11px]"></i> Pembina:</span>
                        <span class="font-bold text-slate-800 truncate max-w-[160px]">{{ $ekskul->advisor_name ?: ($ekskul->advisor->full_name ?? 'PKS Kesiswaan') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <span class="flex items-center gap-1.5 text-slate-500"><i class="fas fa-crown text-[11px] text-amber-500"></i> Ketua:</span>
                        <span class="font-bold text-amber-900 truncate max-w-[160px]">{{ $ekskul->leader->full_name ?? '(Belum Ditetapkan)' }}</span>
                    </div>
                    @if($ekskul->schedule_day_time)
                    <div class="flex items-center justify-between text-slate-600 border-t border-slate-200/60 pt-1.5">
                        <span class="flex items-center gap-1.5 text-slate-500"><i class="fas fa-clock text-[11px] text-indigo-500"></i> Jadwal:</span>
                        <span class="font-semibold text-slate-700">{{ $ekskul->schedule_day_time }}</span>
                    </div>
                    @endif
                </div>

                {{-- Badges Count --}}
                <div class="flex items-center justify-between text-xs text-slate-500 pt-1">
                    <span class="font-bold text-indigo-600 flex items-center gap-1">
                        <i class="fas fa-users"></i> {{ $ekskul->active_members_count }} Anggota Aktif
                    </span>
                    <span class="font-semibold text-slate-400 flex items-center gap-1">
                        <i class="fas fa-calendar-check"></i> {{ $ekskul->activities_count }} Kegiatan
                    </span>
                </div>
            </div>

            <div class="bg-slate-50/80 px-5 py-3 border-t border-slate-100 flex items-center justify-between gap-2">
                @if($ekskul->forum_group_id)
                <a href="{{ route('space.index', ['group' => $ekskul->forum_group_id]) }}" class="text-xs font-bold text-purple-700 hover:text-purple-900 flex items-center gap-1.5" title="Kanal Diskusi Pembda Space">
                    <i class="fas fa-comments text-purple-600"></i> Space Group
                </a>
                @else
                <span></span>
                @endif

                <a href="{{ route('admin.extracurricular.show', $ekskul) }}" class="px-4 py-2 bg-slate-900 hover:bg-indigo-600 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-2xs">
                    <span>Kelola Unit</span>
                    <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-slate-200">
            <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                🎨
            </div>
            <h3 class="font-bold text-slate-800 text-base">Belum Ada Unit Ekstrakurikuler</h3>
            <p class="text-xs text-slate-500 mt-1">Klik tombol 'Tambah Unit Kegiatan' di atas untuk membuat unit ekskul pertama.</p>
        </div>
        @endforelse
    </div>

    <div class="pt-4">
        {{ $extracurriculars->links() }}
    </div>
</div>

{{-- Modal Tambah Ekskul --}}
<div id="modalAddEkskul" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
        <div class="bg-gradient-to-r from-slate-900 to-indigo-950 p-5 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="text-2xl">🎨</span>
                <h3 class="font-black text-sm text-white">Tambah Unit Ekstrakurikuler Baru</h3>
            </div>
            <button onclick="document.getElementById('modalAddEkskul').classList.add('hidden')" class="text-slate-400 hover:text-white text-lg">&times;</button>
        </div>

        <form action="{{ route('admin.extracurricular.store') }}" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1 text-xs">
            @csrf

            <div>
                <label class="block font-bold text-slate-700 mb-1">Unit Sekolah *</label>
                <select name="school_id" required class="w-full rounded-xl border-slate-300 font-semibold text-slate-800">
                    @foreach($schools as $sch)
                    <option value="{{ $sch->id }}" {{ (request('school_id') == $sch->id || auth()->user()->school_id == $sch->id) ? 'selected' : '' }}>{{ $sch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Unit Kegiatan *</label>
                <input type="text" name="name" required placeholder="Contoh: Gugus Depan Gerakan Pramuka Pembda" class="w-full rounded-xl border-slate-300 font-semibold">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kategori *</label>
                    <select name="category" required class="w-full rounded-xl border-slate-300 font-semibold">
                        <option value="pramuka">⚜️ Gerakan Pramuka</option>
                        <option value="paskibraka">🇮🇩 Paskibraka</option>
                        <option value="seni_budaya">🎭 Seni & Budaya / Sanggar</option>
                        <option value="olahraga">⚽ Olahraga & Atletik</option>
                        <option value="sains_it">💻 Sains & IT Club</option>
                        <option value="keagamaan">✝️ Keagamaan (Rohkris/Rohis)</option>
                        <option value="jurnalistik">📰 Jurnalistik & Literasi</option>
                        <option value="umum">🎨 Pengembangan Diri Umum</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Emoji / Icon Simbol</label>
                    <input type="text" name="icon" placeholder="Contoh: ⚜️ atau 🎭 atau 💻" class="w-full rounded-xl border-slate-300 font-semibold">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Deskripsi & Visi Kegiatan</label>
                <textarea name="description" rows="2.5" placeholder="Penjelasan singkat tujuan, kegiatan latihan, dan manfaat bagi siswa..." class="w-full rounded-xl border-slate-300"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jadwal Latihan</label>
                    <input type="text" name="schedule_day_time" placeholder="Contoh: Jumat, 15:00 - 17:00" class="w-full rounded-xl border-slate-300">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Lokasi Latihan</label>
                    <input type="text" name="location" placeholder="Contoh: Lapangan Utama Pembda" class="w-full rounded-xl border-slate-300">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Guru Pembina / PKS Kesiswaan</label>
                <input type="text" name="advisor_name" placeholder="Nama Guru Pembina atau Tim PKS Kesiswaan" class="w-full rounded-xl border-slate-300 font-semibold">
                <p class="text-[10px] text-slate-400 mt-1">Boleh dikosongkan (otomatis dipegang oleh PKS Kesiswaan unit terkait).</p>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="document.getElementById('modalAddEkskul').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition shadow-sm">Simpan Unit Ekskul</button>
            </div>
        </form>
    </div>
</div>
@endsection
