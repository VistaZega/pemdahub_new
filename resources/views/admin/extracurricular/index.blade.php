@extends('layouts.admin')

@section('title', 'Manajemen Ekstrakurikuler & Kegiatan Non-Akademik')

@section('content')
<div class="space-y-6">
    {{-- Header Banner (UI/UX PRO MAX: CLEAN LIGHT THEME WITH 21:1 CONTRAST) --}}
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm relative overflow-hidden space-y-4">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
            <div class="space-y-2">
                <div class="flex items-center gap-2 text-xs font-black tracking-wider uppercase text-indigo-700">
                    <span>🌟 Pembinaan Bakat & Karakter Siswa</span>
                    <span>&bull;</span>
                    <span class="bg-indigo-50 border border-indigo-200 px-2.5 py-0.5 rounded-full text-indigo-900">Terintegrasi DNA & Space</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 flex items-center gap-3">
                    <span>🎨 Ekstrakurikuler & Unit Kegiatan</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 font-medium max-w-3xl leading-relaxed">
                    Kelola unit kegiatan non-akademik, struktur pengurus (Manager Yayasan, Pembina PKS, Ketua Siswa, Sekretaris, Bendahara), persetujuan anggota, dan ruang interaksi Pembda Space.
                </p>
            </div>
            <button onclick="document.getElementById('modalAddEkskul').classList.remove('hidden')" class="px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-sm transition flex items-center gap-2 flex-shrink-0 active:scale-95">
                <i class="fas fa-plus"></i>
                <span>Tambah Unit Kegiatan</span>
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs flex items-center gap-4 hover:shadow-xs transition">
            <div class="w-13 h-13 rounded-2xl bg-indigo-50 text-indigo-700 border border-indigo-100 flex items-center justify-center text-2xl font-black shadow-inner flex-shrink-0">
                🏛️
            </div>
            <div>
                <p class="text-xs text-indigo-700 font-bold uppercase tracking-wider">Total Unit Kegiatan</p>
                <p class="text-2xl font-black text-slate-900 mt-0.5">{{ $stats['total_units'] }} <span class="text-xs font-semibold text-slate-400">Unit</span></p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs flex items-center gap-4 hover:shadow-xs transition">
            <div class="w-13 h-13 rounded-2xl bg-purple-50 text-purple-700 border border-purple-100 flex items-center justify-center text-2xl font-black shadow-inner flex-shrink-0">
                👥
            </div>
            <div>
                <p class="text-xs text-purple-700 font-bold uppercase tracking-wider">Total Anggota Aktif</p>
                <p class="text-2xl font-black text-slate-900 mt-0.5">{{ $stats['total_members'] }} <span class="text-xs font-semibold text-slate-400">Siswa</span></p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs flex items-center gap-4 hover:shadow-xs transition">
            <div class="w-13 h-13 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center text-2xl font-black shadow-inner flex-shrink-0">
                📅
            </div>
            <div>
                <p class="text-xs text-emerald-700 font-bold uppercase tracking-wider">Log Kegiatan Latihan</p>
                <p class="text-2xl font-black text-slate-900 mt-0.5">{{ $stats['total_activities'] }} <span class="text-xs font-semibold text-slate-400">Sesi</span></p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            @if(in_array(auth()->user()->role, ['superadmin', 'admin_yayasan', 'yayasan']))
            <div class="sm:col-span-4">
                <select name="school_id" onchange="this.form.submit()" class="w-full text-xs font-semibold rounded-xl border-slate-300 bg-slate-50 py-2.5 px-3 focus:bg-white text-slate-900">
                    <option value="">-- Seluruh Unit Sekolah (3 Sekolah Aktif) --</option>
                    @foreach($schools as $sch)
                    <option value="{{ $sch->id }}" {{ request('school_id') == $sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="sm:col-span-3">
                <select name="category" onchange="this.form.submit()" class="w-full text-xs font-semibold rounded-xl border-slate-300 bg-slate-50 py-2.5 px-3 focus:bg-white text-slate-900">
                    <option value="">-- Semua Kategori --</option>
                    <option value="marching_band" {{ request('category') == 'marching_band' ? 'selected' : '' }}>🥁 Marching Band / Korsik</option>
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
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama unit / pembina..." class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 py-2.5 pl-9 pr-3 focus:bg-white text-slate-900">
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
        <div class="bg-white rounded-2xl border {{ $ekskul->isFoundationLevel() ? 'border-amber-300 ring-2 ring-amber-50' : 'border-slate-200' }} shadow-2xs overflow-hidden flex flex-col hover:shadow-md hover:border-indigo-300 transition duration-200 group">
            <div class="p-5 flex-1 space-y-3.5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-700 flex items-center justify-center text-2xl shadow-2xs group-hover:scale-105 transition flex-shrink-0">
                            {{ $ekskul->display_icon }}
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700">
                                    {{ $ekskul->category_label }}
                                </span>
                                @if($ekskul->isFoundationLevel())
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300">
                                    🏛️ Yayasan
                                </span>
                                @endif
                            </div>
                            <h3 class="font-black text-slate-900 text-sm mt-1 leading-snug group-hover:text-indigo-600 transition">
                                {{ $ekskul->name }}
                            </h3>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-slate-600 font-medium line-clamp-2 leading-relaxed">
                    {{ $ekskul->description ?: 'Unit kegiatan pembinaan bakat dan karakter siswa Perguruan Pembda.' }}
                </p>

                {{-- Info Pill --}}
                <div class="space-y-1.5 text-xs bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium flex items-center gap-1.5"><i class="fas fa-school text-[11px] text-indigo-500"></i> Unit:</span>
                        <span class="font-bold text-slate-800">{{ $ekskul->school->short_name ?? ($ekskul->school->name ?? 'Lintas Yayasan') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium flex items-center gap-1.5"><i class="fas fa-user-tie text-[11px] text-indigo-500"></i> Pembina/Mgr:</span>
                        <span class="font-bold text-slate-800 truncate max-w-[160px]">{{ $ekskul->manager_name ?: ($ekskul->advisor_name ?: ($ekskul->advisor->full_name ?? 'PKS Kesiswaan')) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium flex items-center gap-1.5"><i class="fas fa-crown text-[11px] text-amber-500"></i> Ketua:</span>
                        <span class="font-bold text-slate-800 truncate max-w-[160px]">{{ $ekskul->leader->full_name ?? '(Belum Ditetapkan)' }}</span>
                    </div>
                    @if($ekskul->schedule_day_time)
                    <div class="flex items-center justify-between border-t border-slate-200/60 pt-1.5">
                        <span class="text-slate-500 font-medium flex items-center gap-1.5"><i class="fas fa-clock text-[11px] text-indigo-500"></i> Jadwal:</span>
                        <span class="font-bold text-slate-700">{{ $ekskul->schedule_day_time }}</span>
                    </div>
                    @endif
                </div>

                {{-- Badges Count --}}
                <div class="flex items-center justify-between text-xs pt-1">
                    <span class="font-bold text-indigo-600 flex items-center gap-1.5">
                        <i class="fas fa-users"></i> {{ $ekskul->active_members_count }} Anggota Aktif
                    </span>
                    <span class="font-bold text-slate-500 flex items-center gap-1.5">
                        <i class="fas fa-calendar-check"></i> {{ $ekskul->activities_count }} Kegiatan
                    </span>
                </div>
            </div>

            <div class="bg-slate-50 px-5 py-3 border-t border-slate-100 flex items-center justify-between gap-2">
                @if($ekskul->forum_group_id)
                <a href="{{ route('forum.index', ['group' => $ekskul->forum_group_id]) }}" class="text-xs font-bold text-purple-700 hover:text-purple-900 flex items-center gap-1.5" title="Kanal Diskusi Pembda Space">
                    <i class="fas fa-comments text-purple-600"></i> Space Group
                </a>
                @else
                <span></span>
                @endif

                <div class="flex items-center gap-1.5">
                    <form action="{{ route('admin.extracurricular.destroy', $ekskul) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus unit {{ addslashes($ekskul->name) }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Hapus Unit Ekstrakurikuler">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </form>

                    <a href="{{ route('admin.extracurricular.show', $ekskul) }}" class="px-3.5 py-1.5 bg-slate-900 hover:bg-indigo-600 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-2xs active:scale-95">
                        <span>Kelola</span>
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
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

{{-- Modal Tambah Unit Ekskul --}}
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

            @if($isGlobal)
            <div>
                <label class="block font-bold text-slate-700 mb-1">Unit Naungan Sekolah / Yayasan *</label>
                <select name="school_id" class="w-full rounded-xl border-slate-300 font-semibold text-slate-900">
                    <option value="">🏛️ Tingkat Yayasan (Marching Band / Lintas Unit)</option>
                    @foreach($schools as $sch)
                    <option value="{{ $sch->id }}">{{ $sch->name }}</option>
                    @endforeach
                </select>
            </div>
            @else
            <input type="hidden" name="school_id" value="{{ auth()->user()->school_id }}">
            @endif

            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Unit Kegiatan *</label>
                <input type="text" name="name" required placeholder="Contoh: Sanggar Seni Budaya Nias" class="w-full rounded-xl border-slate-300 font-semibold text-slate-900">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kategori *</label>
                    <select name="category" required class="w-full rounded-xl border-slate-300 font-semibold text-slate-900">
                        <option value="marching_band">🥁 Marching Band / Korsik</option>
                        <option value="pramuka">⚜️ Gerakan Pramuka</option>
                        <option value="paskibraka">🇮🇩 Paskibraka</option>
                        <option value="seni_budaya">🎭 Seni & Budaya / Sanggar</option>
                        <option value="olahraga">⚽ Olahraga & Atletik</option>
                        <option value="sains_it">💻 Sains & IT Club</option>
                        <option value="keagamaan">✝️ Keagamaan</option>
                        <option value="jurnalistik">📰 Jurnalistik</option>
                        <option value="umum">🎨 Umum</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Emoji / Icon</label>
                    <input type="text" name="icon" placeholder="Contoh: 🎭 atau 🥁" class="w-full rounded-xl border-slate-300 font-semibold text-slate-900">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Deskripsi Kegiatan</label>
                <textarea name="description" rows="3" placeholder="Uraian visi, misi, dan aktivitas unit ekskul..." class="w-full rounded-xl border-slate-300 text-slate-900"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jadwal Latihan</label>
                    <input type="text" name="schedule_day_time" placeholder="Contoh: Sabtu, 15:00 - 17:00" class="w-full rounded-xl border-slate-300 text-slate-900">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Lokasi Latihan</label>
                    <input type="text" name="location" placeholder="Contoh: Lapangan Utama Pembda" class="w-full rounded-xl border-slate-300 text-slate-900">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="document.getElementById('modalAddEkskul').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition shadow-sm">Simpan Unit</button>
            </div>
        </form>
    </div>
</div>
@endsection
