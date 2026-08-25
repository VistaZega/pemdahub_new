@extends('layouts.admin')

@section('title', $extracurricular->name . ' - Detail Unit Ekstrakurikuler')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'members', showDeleteModal: false }">

    {{-- Breadcrumb & Back --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <a href="{{ route('admin.extracurricular.index') }}" class="font-black text-indigo-700 hover:text-indigo-900 flex items-center gap-2 transition text-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali ke Katalog Ekstrakurikuler</span>
        </a>
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white shadow-xs font-black text-xs">
            <span>🏫 Unit Naungan:</span>
            <span>{{ $extracurricular->school->name ?? 'Yayasan Perguruan Pembda (Lintas 3 Unit Sekolah)' }}</span>
        </div>
    </div>

    {{-- Hero Card (VIBRANT & COLORFUL BENTO HEADER) --}}
    <div class="bg-white rounded-3xl border-2 border-indigo-200/80 shadow-lg relative overflow-hidden space-y-6">
        {{-- Rainbow Decorative Stripe --}}
        <div class="h-3 w-full bg-gradient-to-r from-rose-500 via-amber-500 via-emerald-500 via-cyan-500 to-purple-600"></div>

        <div class="p-6 sm:p-8 space-y-6">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                <div class="flex items-start gap-4 sm:gap-6">
                    {{-- Big 3D Colorful Icon Container --}}
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-3xl bg-gradient-to-br from-amber-400 via-rose-500 to-purple-600 text-white flex items-center justify-center text-4xl sm:text-5xl shadow-xl border-4 border-white flex-shrink-0">
                        {{ $extracurricular->display_icon }}
                    </div>

                    {{-- Unit Title & Info --}}
                    <div class="space-y-2.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-xs">
                                {{ $extracurricular->category_label }}
                            </span>
                            @if($extracurricular->isFoundationLevel())
                            <span class="px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-gradient-to-r from-amber-400 to-orange-500 text-white shadow-xs border border-amber-300">
                                🏛️ LINTAS UNIT YAYASAN
                            </span>
                            @else
                            <span class="px-3.5 py-1 rounded-full text-xs font-black bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow-xs">
                                {{ $extracurricular->school->short_name ?? ($extracurricular->school->name ?? 'Unit Sekolah') }}
                            </span>
                            @endif
                        </div>

                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-tight">
                            {{ $extracurricular->name }}
                        </h1>

                        <p class="text-sm text-slate-600 font-semibold max-w-3xl leading-relaxed">
                            {{ $extracurricular->description ?: 'Unit kegiatan pembinaan bakat, minat, kreativitas, dan kepemimpinan siswa Perguruan Pembda.' }}
                        </p>
                    </div>
                </div>

                {{-- Action Buttons (Vibrant Multi-Colors) --}}
                <div class="flex flex-wrap items-center gap-3 flex-shrink-0">
                    @if($extracurricular->forum_group_id)
                    <a href="{{ route('forum.index', ['group' => $extracurricular->forum_group_id]) }}" class="px-4 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-2xl font-black text-xs shadow-md transition flex items-center gap-2 active:scale-95 border border-purple-400/40">
                        <i class="fas fa-comments text-sm"></i>
                        <span>Kanal Space Ekskul</span>
                    </a>
                    @endif

                    <button onclick="document.getElementById('modalAssignLeadership').classList.remove('hidden')" class="px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-2xl font-black text-xs shadow-md transition flex items-center gap-2 active:scale-95 border border-blue-400/40">
                        <i class="fas fa-user-gear text-sm"></i>
                        <span>Struktur Pengurus</span>
                    </button>

                    <button @click="showDeleteModal = true" class="px-4 py-2.5 bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white rounded-2xl font-black text-xs transition flex items-center gap-2 shadow-md active:scale-95 border border-rose-400/40" title="Hapus Unit">
                        <i class="fas fa-trash-alt text-sm"></i>
                        <span>Hapus Unit</span>
                    </button>
                </div>
            </div>

            {{-- 4 Solid Vibrant Metric Blocks (RAMAI & PENUH WARNA) --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-2">
                {{-- Jadwal (Sky Blue) --}}
                <div class="bg-gradient-to-br from-sky-500 to-blue-600 text-white p-4.5 rounded-3xl shadow-md border-2 border-sky-400/40 space-y-1">
                    <p class="text-[11px] font-black text-sky-100 uppercase tracking-wider flex items-center gap-1.5">
                        <span>📅</span> JADWAL LATIHAN
                    </p>
                    <p class="font-black text-white text-base truncate">
                        {{ $extracurricular->schedule_day_time ?: 'Fleksibel' }}
                    </p>
                </div>

                {{-- Lokasi (Rose / Pink) --}}
                <div class="bg-gradient-to-br from-rose-500 to-pink-600 text-white p-4.5 rounded-3xl shadow-md border-2 border-rose-400/40 space-y-1">
                    <p class="text-[11px] font-black text-rose-100 uppercase tracking-wider flex items-center gap-1.5">
                        <span>📍</span> LOKASI TEMPAT
                    </p>
                    <p class="font-black text-white text-base truncate">
                        {{ $extracurricular->location ?: 'Kampus Pembda' }}
                    </p>
                </div>

                {{-- Total Anggota (Emerald / Teal) --}}
                <div class="bg-gradient-to-br from-emerald-500 to-teal-600 text-white p-4.5 rounded-3xl shadow-md border-2 border-emerald-400/40 space-y-1">
                    <p class="text-[11px] font-black text-emerald-100 uppercase tracking-wider flex items-center gap-1.5">
                        <span>👥</span> TOTAL ANGGOTA
                    </p>
                    <p class="font-black text-white text-base">
                        {{ $extracurricular->members->where('status', 'approved')->count() }} <span class="text-xs font-bold text-emerald-100">Siswa Aktif</span>
                    </p>
                </div>

                {{-- Sesi Latihan (Amber / Orange) --}}
                <div class="bg-gradient-to-br from-amber-500 to-orange-600 text-white p-4.5 rounded-3xl shadow-md border-2 border-amber-400/40 space-y-1">
                    <p class="text-[11px] font-black text-amber-100 uppercase tracking-wider flex items-center gap-1.5">
                        <span>🚩</span> SESI LATIHAN
                    </p>
                    <p class="font-black text-white text-base">
                        {{ $extracurricular->activities->count() }} <span class="text-xs font-bold text-amber-100">Kegiatan Tercatat</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- 4 Pilar Kepengurusan (COLORFUL GRADIENT CARDS) --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-sitemap text-indigo-600"></i> Struktur Pengurus Unit Kegiatan
            </h2>
            <button onclick="document.getElementById('modalAssignLeadership').classList.remove('hidden')" class="text-xs font-black text-indigo-700 hover:text-indigo-900 flex items-center gap-1">
                <i class="fas fa-edit"></i> Ubah Struktur Pengurus
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Pembina / Manager (Blue/Indigo Card) --}}
            <div class="bg-gradient-to-br from-blue-600 to-indigo-700 text-white p-5 rounded-3xl shadow-md border-2 border-blue-400/50 flex items-center gap-4 hover:scale-[1.02] transition">
                <div class="w-13 h-13 rounded-2xl bg-white text-blue-600 flex items-center justify-center text-2xl shadow-sm flex-shrink-0">
                    👨‍🏫
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-black text-blue-100 uppercase tracking-wider">
                        {{ $extracurricular->isFoundationLevel() ? 'Manager Marching Band' : 'Pembina Guru / PKS' }}
                    </p>
                    <p class="text-sm font-black text-white truncate mt-0.5">
                        {{ $extracurricular->manager_name ?: ($extracurricular->advisor_name ?: ($extracurricular->advisor->full_name ?? 'PKS Kesiswaan')) }}
                    </p>
                </div>
            </div>

            {{-- Ketua (Amber/Orange Card) --}}
            <div class="bg-gradient-to-br from-amber-500 to-orange-600 text-white p-5 rounded-3xl shadow-md border-2 border-amber-400/50 flex items-center gap-4 hover:scale-[1.02] transition">
                <div class="w-13 h-13 rounded-2xl bg-white text-amber-600 flex items-center justify-center text-2xl shadow-sm flex-shrink-0">
                    👑
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-black text-amber-100 uppercase tracking-wider">
                        {{ $extracurricular->isFoundationLevel() ? 'Field Commander / Gitapati' : 'Ketua Siswa' }}
                    </p>
                    <p class="text-sm font-black text-white truncate mt-0.5">
                        {{ $extracurricular->leader->full_name ?? 'Belum Ditentukan' }}
                    </p>
                </div>
            </div>

            {{-- Sekretaris (Purple/Pink Card) --}}
            <div class="bg-gradient-to-br from-purple-600 to-pink-600 text-white p-5 rounded-3xl shadow-md border-2 border-purple-400/50 flex items-center gap-4 hover:scale-[1.02] transition">
                <div class="w-13 h-13 rounded-2xl bg-white text-purple-600 flex items-center justify-center text-2xl shadow-sm flex-shrink-0">
                    📝
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-black text-purple-100 uppercase tracking-wider">Sekretaris Siswa</p>
                    <p class="text-sm font-black text-white truncate mt-0.5">
                        {{ $extracurricular->secretary->full_name ?? 'Belum Ditentukan' }}
                    </p>
                </div>
            </div>

            {{-- Bendahara (Emerald/Teal Card) --}}
            <div class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white p-5 rounded-3xl shadow-md border-2 border-emerald-400/50 flex items-center gap-4 hover:scale-[1.02] transition">
                <div class="w-13 h-13 rounded-2xl bg-white text-emerald-600 flex items-center justify-center text-2xl shadow-sm flex-shrink-0">
                    💰
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-black text-emerald-100 uppercase tracking-wider">Bendahara Siswa</p>
                    <p class="text-sm font-black text-white truncate mt-0.5">
                        {{ $extracurricular->treasurer->full_name ?? 'Belum Ditentukan' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation (Colorful Pills) --}}
    <div class="bg-white rounded-3xl border-2 border-indigo-100 shadow-md overflow-hidden">
        <div class="bg-slate-100 p-2.5 border-b border-slate-200 flex flex-wrap items-center gap-2">
            <button @click="activeTab = 'members'" :class="activeTab === 'members' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md font-black' : 'text-slate-700 hover:text-slate-900 font-bold'" class="px-5 py-2.5 rounded-2xl transition flex items-center gap-2 text-xs">
                <i class="fas fa-users"></i>
                <span>Anggota Siswa ({{ $extracurricular->members->count() }})</span>
            </button>
            <button @click="activeTab = 'activities'" :class="activeTab === 'activities' ? 'bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-md font-black' : 'text-slate-700 hover:text-slate-900 font-bold'" class="px-5 py-2.5 rounded-2xl transition flex items-center gap-2 text-xs">
                <i class="fas fa-calendar-check"></i>
                <span>Log Latihan & Kegiatan ({{ $extracurricular->activities->count() }})</span>
            </button>
            <button @click="activeTab = 'settings'" :class="activeTab === 'settings' ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-md font-black' : 'text-slate-700 hover:text-slate-900 font-bold'" class="px-5 py-2.5 rounded-2xl transition flex items-center gap-2 text-xs">
                <i class="fas fa-cog"></i>
                <span>Pengaturan & Hapus Unit</span>
            </button>
        </div>

        {{-- TAB 1: MEMBERS --}}
        <div x-show="activeTab === 'members'" class="p-6 space-y-5">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-black text-slate-900">Daftar Anggota & Klaim Keanggotaan</h3>
                    <p class="text-xs text-slate-600 font-medium">Siswa yang disetujui otomatis mendapatkan +15 Poin Reputasi dan terkoneksi ke grup Pembda Space.</p>
                </div>
                <button onclick="document.getElementById('modalAddMember').classList.remove('hidden')" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-2xl font-black text-xs shadow-md transition flex items-center gap-2 active:scale-95">
                    <i class="fas fa-user-plus"></i>
                    <span>Tambah Siswa Manual</span>
                </button>
            </div>

            <div class="overflow-x-auto rounded-2xl border-2 border-slate-200">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white font-black uppercase text-[11px] tracking-wider">
                            <th class="p-4 w-12 text-center">#</th>
                            <th class="p-4">Nama Siswa</th>
                            <th class="p-4">Sekolah & Kelas</th>
                            <th class="p-4 text-center">Section / Alat</th>
                            <th class="p-4 text-center">Jabatan</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4 text-center">Reward Poin</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse($extracurricular->members as $idx => $member)
                        <tr class="hover:bg-indigo-50/50 transition">
                            <td class="p-4 text-center font-black text-slate-400">{{ $idx + 1 }}</td>
                            <td class="p-4">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-11 h-11 rounded-2xl overflow-hidden bg-slate-100 border-2 border-indigo-200 flex-shrink-0 shadow-2xs">
                                        <img src="{{ $member->student->photo_url }}" class="w-full h-full object-cover" alt="{{ $member->student->full_name }}" onerror="this.onerror=null; this.src='{{ asset('images/default-student.jpg') }}';">
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-black text-slate-900 text-sm">{{ $member->student->full_name }}</p>
                                        <p class="text-xs text-slate-500 font-mono font-bold mt-0.5">NISN: {{ $member->student->nisn ?: '-' }} &bull; NIS: {{ $member->student->nis ?: '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-slate-800">
                                <div class="font-black text-slate-900 text-xs">{{ $member->student->school->short_name ?: $member->student->school->name }}</div>
                                <div class="text-xs text-indigo-700 font-bold mt-0.5">{{ $member->student->currentClassroom->first()->class_name ?? ($member->student->classroom->class_name ?? '-') }}</div>
                            </td>
                            <td class="p-4 text-center">
                                @if($member->section)
                                <span class="px-3 py-1 rounded-full text-xs font-black bg-gradient-to-r from-purple-100 to-pink-100 text-purple-950 border border-purple-300 shadow-2xs">
                                    🎺 {{ $member->section }}
                                </span>
                                @else
                                <span class="text-slate-400 font-bold">-</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-black text-white bg-gradient-to-r {{ $member->role_badge_color }} shadow-2xs">
                                    {{ $member->role_label }}
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                @if($member->status === 'approved')
                                <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-950 border border-emerald-300">
                                    ✓ Aktif
                                </span>
                                @else
                                <span class="px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-950 border border-amber-300">
                                    Menunggu Approval
                                </span>
                                @endif
                            </td>
                            <td class="p-4 text-center font-black text-indigo-700 text-sm">
                                +{{ $member->points_awarded }} Poin
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($member->status !== 'approved')
                                    <form action="{{ route('admin.extracurricular.members.approve', $member) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-xl font-black text-xs transition shadow-sm" title="Setujui Klaim">
                                            Setujui
                                        </button>
                                    </form>
                                    @endif

                                    <form action="{{ route('admin.extracurricular.members.remove', $member) }}" method="POST" onsubmit="return confirm('Hapus siswa ini dari unit ekskul?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-rose-600 hover:bg-rose-50 rounded-xl transition" title="Hapus Anggota">
                                            <i class="fas fa-trash-alt text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="p-10 text-center text-slate-400 text-sm font-bold bg-slate-50">
                                Belum ada siswa yang mendaftar di unit ekstrakurikuler ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TAB 2: ACTIVITIES --}}
        <div x-show="activeTab === 'activities'" class="p-6 space-y-5" style="display: none;">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-black text-slate-900">Catatan Kegiatan, Sesi Latihan & Prestasi</h3>
                    <p class="text-xs text-slate-600 font-medium">Dokumentasi resmi aktivitas ekskul yang merefleksikan portofolio siswa Pembda.</p>
                </div>
                <button onclick="document.getElementById('modalAddActivity').classList.remove('hidden')" class="px-5 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white rounded-2xl font-black text-xs shadow-md transition flex items-center gap-2 active:scale-95">
                    <i class="fas fa-plus"></i>
                    <span>Catat Kegiatan Baru</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($extracurricular->activities as $act)
                <div class="p-5 bg-gradient-to-br from-amber-50/50 via-white to-orange-50/40 rounded-3xl border-2 border-amber-200/80 space-y-2 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="px-3 py-0.5 rounded-full text-xs font-black bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-2xs">
                            {{ $act->activity_date->translatedFormat('l, d F Y') }}
                        </span>
                        <span class="text-xs text-slate-600 font-bold flex items-center gap-1">
                            <i class="fas fa-location-dot text-rose-500"></i> {{ $act->location ?: 'Kampus Pembda' }}
                        </span>
                    </div>
                    <h4 class="font-black text-slate-900 text-sm leading-snug">{{ $act->title }}</h4>
                    <p class="text-xs text-slate-700 leading-relaxed font-normal">{{ $act->description ?: 'Kegiatan latihan rutin unit ekstrakurikuler.' }}</p>
                </div>
                @empty
                <div class="col-span-full py-10 text-center text-slate-400 text-sm bg-slate-50 rounded-3xl border-2 border-dashed border-slate-300 font-bold">
                    Belum ada log kegiatan latihan yang dicatat. Klik 'Catat Kegiatan Baru' di atas.
                </div>
                @endforelse
            </div>
        </div>

        {{-- TAB 3: SETTINGS & DANGER ZONE --}}
        <div x-show="activeTab === 'settings'" class="p-6 max-w-2xl space-y-6" style="display: none;">
            <div>
                <h3 class="text-base font-black text-slate-900 border-b border-slate-200 pb-3">Edit Informasi Unit Ekstrakurikuler</h3>

                <form action="{{ route('admin.extracurricular.update', $extracurricular) }}" method="POST" class="space-y-4 text-xs mt-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block font-black text-slate-800 text-xs mb-1">Nama Unit Kegiatan *</label>
                        <input type="text" name="name" value="{{ old('name', $extracurricular->name) }}" required class="w-full rounded-2xl border-2 border-slate-300 font-bold text-slate-900 p-3">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-black text-slate-800 text-xs mb-1">Kategori *</label>
                            <select name="category" required class="w-full rounded-2xl border-2 border-slate-300 font-bold text-slate-900 p-3">
                                <option value="marching_band" {{ $extracurricular->category == 'marching_band' ? 'selected' : '' }}>🥁 Marching Band / Korsik Yayasan</option>
                                <option value="pramuka" {{ $extracurricular->category == 'pramuka' ? 'selected' : '' }}>⚜️ Gerakan Pramuka</option>
                                <option value="paskibraka" {{ $extracurricular->category == 'paskibraka' ? 'selected' : '' }}>🇮🇩 Paskibraka</option>
                                <option value="seni_budaya" {{ $extracurricular->category == 'seni_budaya' ? 'selected' : '' }}>🎭 Seni & Budaya / Sanggar</option>
                                <option value="olahraga" {{ $extracurricular->category == 'olahraga' ? 'selected' : '' }}>⚽ Olahraga & Atletik</option>
                                <option value="sains_it" {{ $extracurricular->category == 'sains_it' ? 'selected' : '' }}>💻 Sains & IT Club</option>
                                <option value="keagamaan" {{ $extracurricular->category == 'keagamaan' ? 'selected' : '' }}>✝️ Keagamaan</option>
                                <option value="jurnalistik" {{ $extracurricular->category == 'jurnalistik' ? 'selected' : '' }}>📰 Jurnalistik</option>
                                <option value="umum" {{ $extracurricular->category == 'umum' ? 'selected' : '' }}>🎨 Umum</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-black text-slate-800 text-xs mb-1">Emoji / Icon Simbol</label>
                            <input type="text" name="icon" value="{{ old('icon', $extracurricular->icon) }}" placeholder="Contoh: 🥁 atau 🎭" class="w-full rounded-2xl border-2 border-slate-300 font-bold text-slate-900 p-3">
                        </div>
                    </div>

                    <div>
                        <label class="block font-black text-slate-800 text-xs mb-1">Deskripsi Kegiatan</label>
                        <textarea name="description" rows="3" class="w-full rounded-2xl border-2 border-slate-300 text-slate-900 p-3 leading-relaxed">{{ old('description', $extracurricular->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-black text-slate-800 text-xs mb-1">Jadwal Latihan</label>
                            <input type="text" name="schedule_day_time" value="{{ old('schedule_day_time', $extracurricular->schedule_day_time) }}" class="w-full rounded-2xl border-2 border-slate-300 text-slate-900 font-bold p-3">
                        </div>

                        <div>
                            <label class="block font-black text-slate-800 text-xs mb-1">Lokasi Latihan</label>
                            <input type="text" name="location" value="{{ old('location', $extracurricular->location) }}" class="w-full rounded-2xl border-2 border-slate-300 text-slate-900 font-bold p-3">
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-2xl font-black text-xs transition shadow-md active:scale-95">
                            Simpan Perubahan Unit
                        </button>
                    </div>
                </form>
            </div>

            {{-- ZONA BAHAYA (HAPUS UNIT) --}}
            <div class="pt-6 border-t border-slate-200">
                <div class="bg-rose-50 rounded-3xl border-2 border-rose-200 p-6 space-y-3">
                    <div class="flex items-center gap-2 text-rose-800">
                        <i class="fas fa-exclamation-triangle text-lg"></i>
                        <h4 class="font-black text-sm uppercase tracking-wider">Zona Bahaya: Hapus Unit Kegiatan</h4>
                    </div>
                    <p class="text-xs text-rose-800 leading-relaxed font-semibold">
                        Menghapus unit ini akan menghapus seluruh data sesi latihan dan melepaskan keanggotaan siswa yang terdaftar. Data poin reputasi siswa yang telah diberikan sebelumnya akan tetap tersimpan aman di log reputasi.
                    </p>
                    <div class="pt-2 flex justify-end">
                        <button @click="showDeleteModal = true" class="px-5 py-2.5 bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white rounded-2xl font-black text-xs transition shadow-md flex items-center gap-2 active:scale-95">
                            <i class="fas fa-trash-alt"></i>
                            <span>Hapus Unit Ekstrakurikuler Ini</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL HAPUS UNIT --}}
    <div x-show="showDeleteModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
        <div @click.away="showDeleteModal = false" class="bg-white w-full max-w-md rounded-3xl shadow-2xl border-2 border-slate-200 overflow-hidden flex flex-col p-6 space-y-4">
            <div class="w-14 h-14 rounded-3xl bg-rose-100 text-rose-600 flex items-center justify-center text-2xl mx-auto shadow-inner">
                <i class="fas fa-trash-alt"></i>
            </div>

            <div class="text-center space-y-1.5">
                <h3 class="font-black text-slate-900 text-lg">Konfirmasi Hapus Unit</h3>
                <p class="text-xs text-slate-600 leading-relaxed font-medium">
                    Apakah Anda yakin ingin menghapus unit <b>"{{ $extracurricular->name }}"</b>? Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="button" @click="showDeleteModal = false" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-2xl font-bold text-xs transition">
                    Batal
                </button>
                <form action="{{ route('admin.extracurricular.destroy', $extracurricular) }}" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white rounded-2xl font-black text-xs transition shadow-md active:scale-95">
                        Ya, Hapus Unit
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

{{-- Modal Tetapkan Pengurus --}}
<div id="modalAssignLeadership" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border-2 border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
        <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 p-6 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-2xl">👥</span>
                <h3 class="font-black text-base text-white">Tetapkan Struktur Pengurus Ekskul</h3>
            </div>
            <button onclick="document.getElementById('modalAssignLeadership').classList.add('hidden')" class="text-white hover:text-slate-200 text-2xl font-bold">&times;</button>
        </div>

        <form action="{{ route('admin.extracurricular.leadership', $extracurricular) }}" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1 text-xs">
            @csrf

            @if($extracurricular->isFoundationLevel() || $extracurricular->category === 'marching_band')
            <div>
                <label class="block font-black text-slate-800 mb-1 text-xs">Nama Manager Unit Yayasan</label>
                <input type="text" name="manager_name" value="{{ old('manager_name', $extracurricular->manager_name) }}" placeholder="Contoh: Manager Marching Band Yayasan Pembda" class="w-full rounded-2xl border-2 border-slate-300 font-bold text-slate-900 p-3">
            </div>
            @endif

            <div>
                <label class="block font-black text-slate-800 mb-1 text-xs">Guru Pembina / Pelatih</label>
                <select name="advisor_teacher_id" class="w-full rounded-2xl border-2 border-slate-300 font-bold text-slate-900 p-3">
                    <option value="">-- Manual / Pembina Luar --</option>
                    @foreach($teachers as $t)
                    <option value="{{ $t->id }}" {{ $extracurricular->advisor_teacher_id == $t->id ? 'selected' : '' }}>{{ $t->full_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-black text-slate-800 mb-1 text-xs">{{ $extracurricular->isFoundationLevel() ? 'Field Commander / Gitapati Siswa' : 'Ketua / Koordinator Siswa' }}</label>
                <select name="leader_student_id" class="w-full rounded-2xl border-2 border-slate-300 font-bold text-slate-900 p-3">
                    <option value="">-- Pilih Siswa (Boleh Kosong) --</option>
                    @foreach($students as $s)
                    <option value="{{ $s->id }}" {{ $extracurricular->leader_student_id == $s->id ? 'selected' : '' }}>{{ $s->full_name }} ({{ $s->school->short_name ?? '' }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-black text-slate-800 mb-1 text-xs">Sekretaris Siswa</label>
                <select name="secretary_student_id" class="w-full rounded-2xl border-2 border-slate-300 font-bold text-slate-900 p-3">
                    <option value="">-- Pilih Siswa (Boleh Kosong) --</option>
                    @foreach($students as $s)
                    <option value="{{ $s->id }}" {{ $extracurricular->secretary_student_id == $s->id ? 'selected' : '' }}>{{ $s->full_name }} ({{ $s->school->short_name ?? '' }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-black text-slate-800 mb-1 text-xs">Bendahara Siswa</label>
                <select name="treasurer_student_id" class="w-full rounded-2xl border-2 border-slate-300 font-bold text-slate-900 p-3">
                    <option value="">-- Pilih Siswa (Boleh Kosong) --</option>
                    @foreach($students as $s)
                    <option value="{{ $s->id }}" {{ $extracurricular->treasurer_student_id == $s->id ? 'selected' : '' }}>{{ $s->full_name }} ({{ $s->school->short_name ?? '' }})</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="document.getElementById('modalAssignLeadership').classList.add('hidden')" class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-2xl font-bold transition">Batal</button>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-2xl font-black transition shadow-md active:scale-95">Simpan Struktur</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Tambah Member Manual --}}
<div id="modalAddMember" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl border-2 border-slate-200 overflow-hidden flex flex-col">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white flex items-center justify-between">
            <h3 class="font-black text-base text-white">Tambah Anggota Siswa Manual</h3>
            <button onclick="document.getElementById('modalAddMember').classList.add('hidden')" class="text-white hover:text-slate-200 text-2xl font-bold">&times;</button>
        </div>

        <form action="{{ route('admin.extracurricular.members.add', $extracurricular) }}" method="POST" class="p-6 space-y-4 text-xs">
            @csrf

            <div>
                <label class="block font-black text-slate-800 mb-1 text-xs">Pilih Siswa *</label>
                <select name="student_id" required class="w-full rounded-2xl border-2 border-slate-300 font-bold text-slate-900 p-3">
                    @foreach($students as $s)
                    <option value="{{ $s->id }}">{{ $s->full_name }} ({{ $s->school->short_name ?? '' }} - NISN: {{ $s->nisn ?: '-' }})</option>
                    @endforeach
                </select>
            </div>

            @if(!empty($extracurricular->section_list))
            <div>
                <label class="block font-black text-slate-800 mb-1 text-xs">Section / Alat Musik</label>
                <select name="section" class="w-full rounded-2xl border-2 border-slate-300 font-bold text-slate-900 p-3">
                    <option value="">-- Pilih Section (Opsional) --</option>
                    @foreach($extracurricular->section_list as $secKey => $secVal)
                    <option value="{{ is_numeric($secKey) ? $secVal : $secKey }}">{{ $secVal }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block font-black text-slate-800 mb-1 text-xs">Peran / Jabatan *</label>
                <select name="role" required class="w-full rounded-2xl border-2 border-slate-300 font-bold text-slate-900 p-3">
                    <option value="anggota">Anggota Aktif</option>
                    <option value="sie_kegiatan">Seksi Kegiatan / Section Leader</option>
                    <option value="ketua">Ketua / Field Commander</option>
                    <option value="sekretaris">Sekretaris</option>
                    <option value="bendahara">Bendahara</option>
                </select>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="document.getElementById('modalAddMember').classList.add('hidden')" class="px-5 py-3 bg-slate-100 text-slate-800 rounded-2xl font-bold">Batal</button>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-2xl font-black shadow-md active:scale-95">Tambahkan Siswa</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Catat Kegiatan --}}
<div id="modalAddActivity" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl border-2 border-slate-200 overflow-hidden flex flex-col">
        <div class="bg-gradient-to-r from-amber-500 to-orange-600 p-6 text-white flex items-center justify-between">
            <h3 class="font-black text-base text-white">Catat Log Kegiatan / Latihan</h3>
            <button onclick="document.getElementById('modalAddActivity').classList.add('hidden')" class="text-white hover:text-slate-200 text-2xl font-bold">&times;</button>
        </div>

        <form action="{{ route('admin.extracurricular.activities.add', $extracurricular) }}" method="POST" class="p-6 space-y-4 text-xs">
            @csrf

            <div>
                <label class="block font-black text-slate-800 mb-1 text-xs">Nama / Topik Kegiatan *</label>
                <input type="text" name="title" required placeholder="Contoh: Latihan Baris-Berbaris Persiapan Upacara" class="w-full rounded-2xl border-2 border-slate-300 font-bold text-slate-900 p-3">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-black text-slate-800 mb-1 text-xs">Tanggal Kegiatan *</label>
                    <input type="date" name="activity_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-2xl border-2 border-slate-300 font-bold text-slate-900 p-3">
                </div>

                <div>
                    <label class="block font-black text-slate-800 mb-1 text-xs">Lokasi Latihan</label>
                    <input type="text" name="location" value="{{ $extracurricular->location ?: 'Kampus Pembda' }}" class="w-full rounded-2xl border-2 border-slate-300 font-bold text-slate-900 p-3">
                </div>
            </div>

            <div>
                <label class="block font-black text-slate-800 mb-1 text-xs">Deskripsi / Hasil Latihan</label>
                <textarea name="description" rows="3" placeholder="Uraian ringkas materi yang dilatih dan catatan kehadiran anggota..." class="w-full rounded-2xl border-2 border-slate-300 text-slate-900 p-3"></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" onclick="document.getElementById('modalAddActivity').classList.add('hidden')" class="px-5 py-3 bg-slate-100 text-slate-800 rounded-2xl font-bold">Batal</button>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white rounded-2xl font-black shadow-md active:scale-95">Simpan Kegiatan</button>
            </div>
        </form>
    </div>
</div>
@endsection
