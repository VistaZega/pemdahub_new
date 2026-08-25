@extends('layouts.admin')

@section('title', 'Manajemen Ekstrakurikuler & Kegiatan Non-Akademik')

@section('content')
<div class="space-y-6">
    {{-- Header Banner (VIBRANT RAINBOW ACCENT) --}}
    <div class="bg-white rounded-3xl border-2 border-indigo-100 shadow-md relative overflow-hidden space-y-4">
        {{-- Rainbow Decorative Stripe --}}
        <div class="h-3 w-full bg-gradient-to-r from-rose-500 via-amber-500 via-emerald-500 via-cyan-500 to-purple-600"></div>

        <div class="p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
            <div class="space-y-2">
                <div class="flex items-center gap-2 text-xs font-black tracking-wider uppercase text-purple-700">
                    <span>🌟 Pembinaan Bakat, Seni, Olahraga & Karakter</span>
                    <span>&bull;</span>
                    <span class="bg-purple-100 text-purple-900 border border-purple-200 px-2.5 py-0.5 rounded-full font-black">DNA & Pembda Space</span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-black tracking-tight text-slate-900 flex items-center gap-3">
                    <span>🎨 Ekstrakurikuler & Unit Kegiatan</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 font-semibold max-w-3xl leading-relaxed">
                    Kelola unit kegiatan non-akademik, struktur pengurus (Manager Yayasan, Pembina PKS, Ketua Siswa, Sekretaris, Bendahara), persetujuan anggota, dan ruang interaksi Pembda Space.
                </p>
            </div>
            <button onclick="document.getElementById('modalAddEkskul').classList.remove('hidden')" class="px-6 py-3.5 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-700 hover:to-pink-700 text-white rounded-2xl font-black text-xs shadow-lg transition flex items-center gap-2 flex-shrink-0 active:scale-95 border border-purple-400/40">
                <i class="fas fa-plus text-amber-300"></i>
                <span>Tambah Unit Kegiatan</span>
            </button>
        </div>
    </div>

    {{-- Stats Cards (3 SOLID VIBRANT COLORFUL CARDS) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        {{-- Total Unit --}}
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 text-white p-6 rounded-3xl shadow-md border-2 border-blue-400/40 flex items-center gap-4 hover:scale-[1.02] transition">
            <div class="w-14 h-14 rounded-2xl bg-white text-blue-600 flex items-center justify-center text-3xl font-black shadow-sm flex-shrink-0">
                🏛️
            </div>
            <div>
                <p class="text-xs text-blue-100 font-black uppercase tracking-wider">Total Unit Kegiatan</p>
                <p class="text-3xl font-black text-white mt-0.5">{{ $stats['total_units'] }} <span class="text-xs font-bold text-blue-200">Unit Ekskul</span></p>
            </div>
        </div>

        {{-- Total Anggota --}}
        <div class="bg-gradient-to-br from-purple-600 to-pink-600 text-white p-6 rounded-3xl shadow-md border-2 border-purple-400/40 flex items-center gap-4 hover:scale-[1.02] transition">
            <div class="w-14 h-14 rounded-2xl bg-white text-purple-600 flex items-center justify-center text-3xl font-black shadow-sm flex-shrink-0">
                👥
            </div>
            <div>
                <p class="text-xs text-purple-100 font-black uppercase tracking-wider">Total Anggota Aktif</p>
                <p class="text-3xl font-black text-white mt-0.5">{{ $stats['total_members'] }} <span class="text-xs font-bold text-purple-200">Siswa Terdaftar</span></p>
            </div>
        </div>

        {{-- Log Sesi --}}
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 text-white p-6 rounded-3xl shadow-md border-2 border-emerald-400/40 flex items-center gap-4 hover:scale-[1.02] transition">
            <div class="w-14 h-14 rounded-2xl bg-white text-emerald-600 flex items-center justify-center text-3xl font-black shadow-sm flex-shrink-0">
                📅
            </div>
            <div>
                <p class="text-xs text-emerald-100 font-black uppercase tracking-wider">Log Sesi Latihan</p>
                <p class="text-3xl font-black text-white mt-0.5">{{ $stats['total_activities'] }} <span class="text-xs font-bold text-emerald-200">Kegiatan</span></p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white p-5 rounded-3xl border-2 border-slate-200 shadow-sm">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            @if(in_array(auth()->user()->role, ['superadmin', 'admin_yayasan', 'yayasan']))
            <div class="sm:col-span-4">
                <select name="school_id" onchange="this.form.submit()" class="w-full text-xs font-black rounded-2xl border-2 border-slate-300 bg-slate-50 py-3 px-3.5 focus:bg-white text-slate-900">
                    <option value="">-- Seluruh Unit Sekolah (3 Sekolah Aktif) --</option>
                    @foreach($schools as $sch)
                    <option value="{{ $sch->id }}" {{ request('school_id') == $sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="sm:col-span-3">
                <select name="category" onchange="this.form.submit()" class="w-full text-xs font-black rounded-2xl border-2 border-slate-300 bg-slate-50 py-3 px-3.5 focus:bg-white text-slate-900">
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
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama unit / pembina..." class="w-full text-xs font-bold rounded-2xl border-2 border-slate-300 bg-slate-50 py-3 pl-10 pr-4 focus:bg-white text-slate-900 placeholder:text-slate-400">
                    <i class="fas fa-search absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
                </div>
            </div>

            <div class="sm:col-span-1 flex gap-2">
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-slate-900 to-indigo-950 text-white rounded-2xl text-xs font-black hover:from-indigo-900 hover:to-purple-950 transition shadow-sm">Cari</button>
            </div>
        </form>
    </div>

    {{-- Grid Daftar Ekskul (COLORFUL CARDS) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($extracurriculars as $ekskul)
        <div class="bg-white rounded-3xl border-2 {{ $ekskul->isFoundationLevel() ? 'border-amber-400 ring-4 ring-amber-100' : 'border-indigo-100 hover:border-indigo-400' }} shadow-sm overflow-hidden flex flex-col hover:shadow-xl transition duration-200 group">
            <div class="p-6 flex-1 space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3.5">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 via-rose-500 to-purple-600 text-white flex items-center justify-center text-3xl shadow-md group-hover:scale-105 transition flex-shrink-0">
                            {{ $ekskul->display_icon }}
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="px-3 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-2xs">
                                    {{ $ekskul->category_label }}
                                </span>
                                @if($ekskul->isFoundationLevel())
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-gradient-to-r from-amber-400 to-orange-500 text-white shadow-2xs">
                                    🏛️ Yayasan
                                </span>
                                @endif
                            </div>
                            <h3 class="font-black text-slate-950 text-base mt-1.5 leading-snug group-hover:text-indigo-600 transition">
                                {{ $ekskul->name }}
                            </h3>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-slate-600 font-semibold line-clamp-2 leading-relaxed">
                    {{ $ekskul->description ?: 'Unit kegiatan pembinaan bakat dan karakter siswa Perguruan Pembda.' }}
                </p>

                {{-- Info Pill (Vibrant Tint) --}}
                <div class="space-y-2 text-xs bg-gradient-to-br from-slate-50 to-indigo-50/40 p-4 rounded-2xl border border-indigo-100">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-500 flex items-center gap-1.5"><i class="fas fa-school text-[11px] text-indigo-500"></i> Unit:</span>
                        <span class="font-black text-slate-900">{{ $ekskul->school->short_name ?? ($ekskul->school->name ?? 'Lintas Yayasan') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-500 flex items-center gap-1.5"><i class="fas fa-user-tie text-[11px] text-blue-500"></i> Pembina/Mgr:</span>
                        <span class="font-black text-slate-900 truncate max-w-[160px]">{{ $ekskul->manager_name ?: ($ekskul->advisor_name ?: ($ekskul->advisor->full_name ?? 'PKS Kesiswaan')) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-500 flex items-center gap-1.5"><i class="fas fa-crown text-[11px] text-amber-500"></i> Ketua:</span>
                        <span class="font-black text-amber-900 truncate max-w-[160px]">{{ $ekskul->leader->full_name ?? '(Belum Ditetapkan)' }}</span>
                    </div>
                    @if($ekskul->schedule_day_time)
                    <div class="flex items-center justify-between border-t border-slate-200/80 pt-1.5">
                        <span class="font-bold text-slate-500 flex items-center gap-1.5"><i class="fas fa-clock text-[11px] text-rose-500"></i> Jadwal:</span>
                        <span class="font-black text-slate-800">{{ $ekskul->schedule_day_time }}</span>
                    </div>
                    @endif
                </div>

                {{-- Badges Count --}}
                <div class="flex items-center justify-between text-xs pt-1">
                    <span class="font-black text-indigo-700 flex items-center gap-1.5">
                        <i class="fas fa-users"></i> {{ $ekskul->active_members_count }} Anggota Aktif
                    </span>
                    <span class="font-black text-purple-700 flex items-center gap-1.5">
                        <i class="fas fa-calendar-check"></i> {{ $ekskul->activities_count }} Kegiatan
                    </span>
                </div>
            </div>

            <div class="bg-gradient-to-r from-slate-50 via-indigo-50/30 to-purple-50/30 px-5 py-3.5 border-t-2 border-indigo-100 flex items-center justify-between gap-2">
                @if($ekskul->forum_group_id)
                <a href="{{ route('forum.index', ['group' => $ekskul->forum_group_id]) }}" class="text-xs font-black text-purple-700 hover:text-purple-900 flex items-center gap-1.5" title="Kanal Diskusi Pembda Space">
                    <i class="fas fa-comments text-purple-600"></i> Space Group
                </a>
                @else
                <span></span>
                @endif

                <div class="flex items-center gap-2">
                    <form action="{{ route('admin.extracurricular.destroy', $ekskul) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus unit {{ addslashes($ekskul->name) }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-rose-600 hover:text-rose-800 hover:bg-rose-100 rounded-xl transition" title="Hapus Unit Ekstrakurikuler">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </form>

                    <a href="{{ route('admin.extracurricular.show', $ekskul) }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white rounded-xl text-xs font-black transition flex items-center gap-1.5 shadow-md active:scale-95">
                        <span>Kelola Unit</span>
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-16 text-center bg-white rounded-3xl border-2 border-dashed border-slate-300">
            <div class="w-16 h-16 bg-slate-100 text-slate-500 rounded-full flex items-center justify-center mx-auto mb-3 text-3xl">
                🎨
            </div>
            <h3 class="font-black text-slate-900 text-lg">Belum Ada Unit Ekstrakurikuler</h3>
            <p class="text-xs text-slate-600 mt-1 font-semibold">Klik tombol 'Tambah Unit Kegiatan' di atas untuk membuat unit ekskul pertama.</p>
        </div>
        @endforelse
    </div>

    <div class="pt-4">
        {{ $extracurriculars->links() }}
    </div>
</div>

{{-- Modal Tambah Unit Ekskul --}}
<div id="modalAddEkskul" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border-2 border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 p-6 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-2xl">🎨</span>
                <h3 class="font-black text-base text-white">Tambah Unit Ekstrakurikuler Baru</h3>
            </div>
            <button onclick="document.getElementById('modalAddEkskul').classList.add('hidden')" class="text-white hover:text-slate-200 text-2xl font-bold">&times;</button>
        </div>

        <form action="{{ route('admin.extracurricular.store') }}" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1 text-xs">
            @csrf

            @if($isGlobal)
            <div>
                <label class="block font-black text-slate-800 text-xs mb-1">Unit Naungan Sekolah / Yayasan *</label>
                <select name="school_id" class="w-full rounded-2xl border-2 border-slate-300 font-bold text-slate-900 text-xs p-3">
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
                <label class="block font-black text-slate-800 text-xs mb-1">Nama Unit Kegiatan *</label>
                <input type="text" name="name" required placeholder="Contoh: Sanggar Seni Budaya Nias" class="w-full rounded-2xl border-2 border-slate-300 font-bold text-slate-900 text-xs p-3">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-black text-slate-800 text-xs mb-1">Kategori *</label>
                    <select name="category" required class="w-full rounded-2xl border-2 border-slate-300 font-bold text-slate-900 text-xs p-3">
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
                    <label class="block font-black text-slate-800 text-xs mb-1">Emoji / Icon</label>
                    <input type="text" name="icon" placeholder="Contoh: 🎭 atau 🥁" class="w-full rounded-2xl border-2 border-slate-300 font-bold text-slate-900 text-xs p-3">
                </div>
            </div>

            <div>
                <label class="block font-black text-slate-800 text-xs mb-1">Deskripsi Kegiatan</label>
                <textarea name="description" rows="3" placeholder="Uraian visi, misi, dan aktivitas unit ekskul..." class="w-full rounded-2xl border-2 border-slate-300 text-slate-900 text-xs p-3"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-black text-slate-800 text-xs mb-1">Jadwal Latihan</label>
                    <input type="text" name="schedule_day_time" placeholder="Contoh: Sabtu, 15:00 - 17:00" class="w-full rounded-2xl border-2 border-slate-300 font-bold text-slate-900 text-xs p-3">
                </div>

                <div>
                    <label class="block font-black text-slate-800 text-xs mb-1">Lokasi Latihan</label>
                    <input type="text" name="location" placeholder="Contoh: Lapangan Utama Pembda" class="w-full rounded-2xl border-2 border-slate-300 font-bold text-slate-900 text-xs p-3">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="document.getElementById('modalAddEkskul').classList.add('hidden')" class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-2xl font-bold transition">Batal</button>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-700 hover:to-pink-700 text-white rounded-2xl font-black transition shadow-md active:scale-95">Simpan Unit Baru</button>
            </div>
        </form>
    </div>
</div>
@endsection
