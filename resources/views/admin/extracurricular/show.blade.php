@extends('layouts.admin')

@section('title', $extracurricular->name . ' - Detail Unit Ekstrakurikuler')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'members', showDeleteModal: false }">

    {{-- Breadcrumb & Back --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
        <a href="{{ route('admin.extracurricular.index') }}" class="font-black text-indigo-700 hover:text-indigo-900 flex items-center gap-2 transition text-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali ke Katalog Ekstrakurikuler</span>
        </a>
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white border-2 border-indigo-100 shadow-xs text-slate-800 font-bold">
            <span class="text-slate-500">🏫 Unit Sekolah:</span>
            <span class="text-indigo-800 font-black text-xs">{{ $extracurricular->school->name ?? 'Yayasan Perguruan Pembda (Lintas Unit Sekolah)' }}</span>
        </div>
    </div>

    {{-- Hero Banner (VIBRANT ROYAL GRADIENT - ULTRA HIGH CONTRAST) --}}
    <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-purple-800 rounded-3xl p-6 sm:p-8 text-white relative overflow-hidden shadow-2xl border-2 border-indigo-400/40">
        <div class="absolute -top-12 -right-12 w-80 h-80 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 right-1/4 w-60 h-60 bg-purple-500/20 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            <div class="flex items-start gap-4 sm:gap-6">
                <div class="w-18 h-18 sm:w-22 sm:h-22 rounded-3xl bg-white text-slate-900 border-4 border-white/80 flex items-center justify-center text-4xl sm:text-5xl shadow-2xl flex-shrink-0">
                    {{ $extracurricular->display_icon }}
                </div>
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-white text-indigo-900 shadow-md">
                            {{ $extracurricular->category_label }}
                        </span>
                        @if($extracurricular->isFoundationLevel())
                        <span class="px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-amber-300 text-amber-950 shadow-md border border-amber-400">
                            🏛️ LINTAS UNIT YAYASAN
                        </span>
                        @else
                        <span class="px-3.5 py-1 rounded-full text-xs font-black bg-indigo-950/80 text-white border border-white/30 shadow-sm">
                            {{ $extracurricular->school->short_name ?? ($extracurricular->school->name ?? 'Unit Sekolah') }}
                        </span>
                        @endif
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight drop-shadow-md">{{ $extracurricular->name }}</h1>
                    <p class="text-sm sm:text-base text-white font-medium max-w-3xl leading-relaxed drop-shadow-xs">
                        {{ $extracurricular->description ?: 'Unit kegiatan pembinaan bakat, minat, dan kepemimpinan siswa.' }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 flex-shrink-0">
                @if($extracurricular->forum_group_id)
                <a href="{{ route('forum.index', ['group' => $extracurricular->forum_group_id]) }}" class="px-4 py-2.5 bg-purple-500 hover:bg-purple-600 text-white rounded-2xl font-black text-xs shadow-lg transition flex items-center gap-2 active:scale-95 border border-purple-300/40">
                    <i class="fas fa-comments text-sm"></i>
                    <span>Kanal Space Ekskul</span>
                </a>
                @endif

                <button onclick="document.getElementById('modalAssignLeadership').classList.remove('hidden')" class="px-4 py-2.5 bg-white text-slate-900 hover:bg-slate-100 rounded-2xl font-black text-xs shadow-lg transition flex items-center gap-2 active:scale-95 border border-slate-200">
                    <i class="fas fa-user-gear text-indigo-600 text-sm"></i>
                    <span>Struktur Pengurus</span>
                </button>

                {{-- Tombol Hapus Unit --}}
                <button @click="showDeleteModal = true" class="px-4 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-2xl font-black text-xs transition flex items-center gap-2 shadow-lg active:scale-95 border border-rose-300/40" title="Hapus Unit Ekstrakurikuler">
                    <i class="fas fa-trash-alt text-sm"></i>
                    <span>Hapus Unit</span>
                </button>
            </div>
        </div>

        {{-- Meta Badges Bar (CERAH, JELAS, ULTRA KONTRAS) --}}
        <div class="mt-6 pt-6 border-t border-white/30 grid grid-cols-2 sm:grid-cols-4 gap-3.5 text-xs">
            <div class="bg-white/20 backdrop-blur-md p-4 rounded-2xl border border-white/40 shadow-inner">
                <p class="text-[11px] text-amber-200 font-black uppercase tracking-wider flex items-center gap-1.5">
                    <span>📅</span> JADWAL LATIHAN
                </p>
                <p class="font-black text-white mt-1 text-sm truncate">{{ $extracurricular->schedule_day_time ?: 'Fleksibel / Sesuai Jadwal' }}</p>
            </div>
            <div class="bg-white/20 backdrop-blur-md p-4 rounded-2xl border border-white/40 shadow-inner">
                <p class="text-[11px] text-cyan-200 font-black uppercase tracking-wider flex items-center gap-1.5">
                    <span>📍</span> LOKASI / TEMPAT
                </p>
                <p class="font-black text-white mt-1 text-sm truncate">{{ $extracurricular->location ?: 'Kampus Pembda' }}</p>
            </div>
            <div class="bg-white/20 backdrop-blur-md p-4 rounded-2xl border border-white/40 shadow-inner">
                <p class="text-[11px] text-emerald-200 font-black uppercase tracking-wider flex items-center gap-1.5">
                    <span>👥</span> TOTAL ANGGOTA
                </p>
                <p class="font-black text-amber-300 mt-1 text-sm">{{ $extracurricular->members->where('status', 'approved')->count() }} Siswa Aktif</p>
            </div>
            <div class="bg-white/20 backdrop-blur-md p-4 rounded-2xl border border-white/40 shadow-inner">
                <p class="text-[11px] text-pink-200 font-black uppercase tracking-wider flex items-center gap-1.5">
                    <span>🚩</span> SESI LATIHAN
                </p>
                <p class="font-black text-purple-200 mt-1 text-sm">{{ $extracurricular->activities->count() }} Kegiatan Tercatat</p>
            </div>
        </div>
    </div>

    {{-- 4-Card Leadership Structure (VIBRANT & BOLD) --}}
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-sitemap text-indigo-600"></i> Struktur Pengurus Unit Kegiatan
            </h2>
            <button onclick="document.getElementById('modalAssignLeadership').classList.remove('hidden')" class="text-xs font-black text-indigo-700 hover:text-indigo-900 flex items-center gap-1">
                <i class="fas fa-edit"></i> Ubah Struktur Pengurus
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Pembina / Manager --}}
            <div class="bg-white p-5 rounded-3xl border-2 border-indigo-200/80 shadow-sm flex items-center gap-4 hover:shadow-md transition">
                <div class="w-13 h-13 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-2xl shadow-md flex-shrink-0">
                    👨‍🏫
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-black text-indigo-700 uppercase tracking-wider">{{ $extracurricular->isFoundationLevel() ? 'Manager Marching Band' : 'Pembina Guru / PKS' }}</p>
                    <p class="text-sm font-black text-slate-900 truncate mt-0.5">{{ $extracurricular->manager_name ?: ($extracurricular->advisor_name ?: ($extracurricular->advisor->full_name ?? 'PKS Kesiswaan')) }}</p>
                </div>
            </div>

            {{-- Ketua --}}
            <div class="bg-white p-5 rounded-3xl border-2 border-amber-200/80 shadow-sm flex items-center gap-4 hover:shadow-md transition">
                <div class="w-13 h-13 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-2xl shadow-md flex-shrink-0">
                    👑
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-black text-amber-700 uppercase tracking-wider">{{ $extracurricular->isFoundationLevel() ? 'Field Commander / Gitapati' : 'Ketua Siswa' }}</p>
                    <p class="text-sm font-black text-slate-900 truncate mt-0.5">{{ $extracurricular->leader->full_name ?? 'Belum Ditentukan' }}</p>
                </div>
            </div>

            {{-- Sekretaris --}}
            <div class="bg-white p-5 rounded-3xl border-2 border-purple-200/80 shadow-sm flex items-center gap-4 hover:shadow-md transition">
                <div class="w-13 h-13 rounded-2xl bg-purple-600 text-white flex items-center justify-center text-2xl shadow-md flex-shrink-0">
                    📝
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-black text-purple-700 uppercase tracking-wider">Sekretaris Siswa</p>
                    <p class="text-sm font-black text-slate-900 truncate mt-0.5">{{ $extracurricular->secretary->full_name ?? 'Belum Ditentukan' }}</p>
                </div>
            </div>

            {{-- Bendahara --}}
            <div class="bg-white p-5 rounded-3xl border-2 border-emerald-200/80 shadow-sm flex items-center gap-4 hover:shadow-md transition">
                <div class="w-13 h-13 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-2xl shadow-md flex-shrink-0">
                    💰
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-black text-emerald-700 uppercase tracking-wider">Bendahara Siswa</p>
                    <p class="text-sm font-black text-slate-900 truncate mt-0.5">{{ $extracurricular->treasurer->full_name ?? 'Belum Ditentukan' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation (Members, Activities, Settings) --}}
    <div class="bg-white rounded-3xl border-2 border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-100 p-2 border-b border-slate-200 flex flex-wrap items-center gap-2">
            <button @click="activeTab = 'members'" :class="activeTab === 'members' ? 'bg-indigo-600 text-white shadow-md font-black' : 'text-slate-700 hover:text-slate-900 font-bold'" class="px-5 py-3 rounded-2xl transition flex items-center gap-2 text-xs">
                <i class="fas fa-users"></i>
                <span>Anggota Siswa ({{ $extracurricular->members->count() }})</span>
            </button>
            <button @click="activeTab = 'activities'" :class="activeTab === 'activities' ? 'bg-indigo-600 text-white shadow-md font-black' : 'text-slate-700 hover:text-slate-900 font-bold'" class="px-5 py-3 rounded-2xl transition flex items-center gap-2 text-xs">
                <i class="fas fa-calendar-check"></i>
                <span>Log Latihan & Kegiatan ({{ $extracurricular->activities->count() }})</span>
            </button>
            <button @click="activeTab = 'settings'" :class="activeTab === 'settings' ? 'bg-indigo-600 text-white shadow-md font-black' : 'text-slate-700 hover:text-slate-900 font-bold'" class="px-5 py-3 rounded-2xl transition flex items-center gap-2 text-xs">
                <i class="fas fa-cog"></i>
                <span>Pengaturan & Hapus Unit</span>
            </button>
        </div>

        {{-- TAB 1: MEMBERS --}}
        <div x-show="activeTab === 'members'" class="p-6 sm:p-8 space-y-5">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-black text-slate-900">Daftar Anggota & Klaim Keanggotaan</h3>
                    <p class="text-xs text-slate-600 font-medium mt-0.5">Siswa yang disetujui otomatis mendapatkan +15 Poin Reputasi dan terkoneksi ke grup Pembda Space.</p>
                </div>
                <button onclick="document.getElementById('modalAddMember').classList.remove('hidden')" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black text-xs shadow-md transition flex items-center gap-2 active:scale-95">
                    <i class="fas fa-user-plus"></i>
                    <span>Tambah Siswa Manual</span>
                </button>
            </div>

            <div class="overflow-x-auto rounded-2xl border-2 border-slate-200 shadow-2xs">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-900 text-white font-black uppercase text-[11px] tracking-wider">
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
                        <tr class="hover:bg-indigo-50/40 transition">
                            <td class="p-4 text-center font-black text-slate-500">{{ $idx + 1 }}</td>
                            <td class="p-4">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-11 h-11 rounded-2xl overflow-hidden bg-slate-100 border-2 border-slate-200 flex-shrink-0 shadow-xs">
                                        <img src="{{ $member->student->photo_url }}" class="w-full h-full object-cover" alt="{{ $member->student->full_name }}" onerror="this.onerror=null; this.src='{{ asset('images/default-student.jpg') }}';">
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-black text-slate-900 text-sm">{{ $member->student->full_name }}</p>
                                        <p class="text-xs text-slate-600 font-mono mt-0.5 font-bold">NISN: {{ $member->student->nisn ?: '-' }} &bull; NIS: {{ $member->student->nis ?: '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-slate-800">
                                <div class="font-black text-slate-900 text-xs">{{ $member->student->school->short_name ?: $member->student->school->name }}</div>
                                <div class="text-xs text-indigo-700 font-bold mt-0.5">{{ $member->student->currentClassroom->first()->class_name ?? ($member->student->classroom->class_name ?? '-') }}</div>
                            </td>
                            <td class="p-4 text-center">
                                @if($member->section)
                                <span class="px-3 py-1 rounded-full text-xs font-black bg-purple-100 text-purple-950 border border-purple-300 shadow-2xs">
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
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-black text-xs transition shadow-md" title="Setujui Klaim">
                                            Setujui
                                        </button>
                                    </form>
                                    @endif

                                    <form action="{{ route('admin.extracurricular.members.remove', $member) }}" method="POST" onsubmit="return confirm('Hapus siswa ini dari unit ekskul?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-rose-600 hover:bg-rose-100 rounded-xl transition" title="Hapus Anggota">
                                            <i class="fas fa-trash-alt text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="p-10 text-center text-slate-500 text-sm font-bold bg-slate-50">
                                Belum ada siswa yang mendaftar di unit ekstrakurikuler ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TAB 2: ACTIVITIES --}}
        <div x-show="activeTab === 'activities'" class="p-6 sm:p-8 space-y-5" style="display: none;">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-black text-slate-900">Catatan Kegiatan, Sesi Latihan & Prestasi</h3>
                    <p class="text-xs text-slate-600 font-medium mt-0.5">Dokumentasi resmi aktivitas ekskul yang merefleksikan portofolio siswa Pembda.</p>
                </div>
                <button onclick="document.getElementById('modalAddActivity').classList.remove('hidden')" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black text-xs shadow-md transition flex items-center gap-2 active:scale-95">
                    <i class="fas fa-plus"></i>
                    <span>Catat Kegiatan Baru</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @forelse($extracurricular->activities as $act)
                <div class="p-5 bg-slate-50 rounded-3xl border-2 border-slate-200 space-y-3 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-indigo-100 text-indigo-950 border border-indigo-200">
                            {{ $act->activity_date->translatedFormat('l, d F Y') }}
                        </span>
                        <span class="text-xs text-slate-700 font-bold flex items-center gap-1.5">
                            <i class="fas fa-location-dot text-rose-500"></i> {{ $act->location ?: 'Kampus Pembda' }}
                        </span>
                    </div>
                    <h4 class="font-black text-slate-900 text-sm leading-snug">{{ $act->title }}</h4>
                    <p class="text-xs text-slate-700 leading-relaxed font-normal">{{ $act->description ?: 'Kegiatan latihan rutin unit ekstrakurikuler.' }}</p>
                </div>
                @empty
                <div class="col-span-full py-12 text-center text-slate-500 text-sm bg-slate-50 rounded-3xl border-2 border-dashed border-slate-300 font-bold">
                    Belum ada log kegiatan latihan yang dicatat. Klik 'Catat Kegiatan Baru' di atas.
                </div>
                @endforelse
            </div>
        </div>

        {{-- TAB 3: SETTINGS & DANGER ZONE --}}
        <div x-show="activeTab === 'settings'" class="p-6 sm:p-8 max-w-3xl space-y-8" style="display: none;">
            <div>
                <h3 class="text-base font-black text-slate-900 border-b border-slate-200 pb-3">Edit Informasi Unit Ekstrakurikuler</h3>

                <form action="{{ route('admin.extracurricular.update', $extracurricular) }}" method="POST" class="space-y-4 text-xs mt-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block font-black text-slate-800 text-xs mb-1">Nama Unit Kegiatan *</label>
                        <input type="text" name="name" value="{{ old('name', $extracurricular->name) }}" required class="w-full rounded-2xl border-2 border-slate-300 font-black text-slate-900 text-sm p-3 focus:border-indigo-600">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-black text-slate-800 text-xs mb-1">Kategori *</label>
                            <select name="category" required class="w-full rounded-2xl border-2 border-slate-300 font-black text-slate-900 text-xs p-3 focus:border-indigo-600">
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
                            <input type="text" name="icon" value="{{ old('icon', $extracurricular->icon) }}" placeholder="Contoh: 🥁 atau 🎭" class="w-full rounded-2xl border-2 border-slate-300 font-black text-slate-900 text-xs p-3 focus:border-indigo-600">
                        </div>
                    </div>

                    <div>
                        <label class="block font-black text-slate-800 text-xs mb-1">Deskripsi Kegiatan</label>
                        <textarea name="description" rows="3" class="w-full rounded-2xl border-2 border-slate-300 text-slate-900 text-xs font-semibold p-3 leading-relaxed focus:border-indigo-600">{{ old('description', $extracurricular->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-black text-slate-800 text-xs mb-1">Jadwal Latihan</label>
                            <input type="text" name="schedule_day_time" value="{{ old('schedule_day_time', $extracurricular->schedule_day_time) }}" class="w-full rounded-2xl border-2 border-slate-300 text-slate-900 text-xs font-bold p-3 focus:border-indigo-600">
                        </div>

                        <div>
                            <label class="block font-black text-slate-800 text-xs mb-1">Lokasi Latihan</label>
                            <input type="text" name="location" value="{{ old('location', $extracurricular->location) }}" class="w-full rounded-2xl border-2 border-slate-300 text-slate-900 text-xs font-bold p-3 focus:border-indigo-600">
                        </div>
                    </div>

                    <div class="pt-3 flex justify-end">
                        <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black text-xs transition shadow-md active:scale-95">
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
                        <button @click="showDeleteModal = true" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl font-black text-xs transition shadow-md flex items-center gap-2 active:scale-95">
                            <i class="fas fa-trash-alt"></i>
                            <span>Hapus Unit Ekstrakurikuler Ini</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL HAPUS UNIT (CONFIRMATION) --}}
    <div x-show="showDeleteModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="showDeleteModal = false" class="bg-white w-full max-w-md rounded-3xl shadow-2xl border-2 border-slate-200 overflow-hidden flex flex-col p-6 space-y-4">
            <div class="w-16 h-16 rounded-3xl bg-rose-100 text-rose-600 flex items-center justify-center text-2xl mx-auto shadow-inner">
                <i class="fas fa-trash-alt"></i>
            </div>

            <div class="text-center space-y-2">
                <h3 class="font-black text-slate-900 text-lg">Konfirmasi Hapus Unit</h3>
                <p class="text-xs text-slate-600 leading-relaxed font-medium">
                    Apakah Anda yakin ingin menghapus unit <b>"{{ $extracurricular->name }}"</b>? Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <div class="flex items-center gap-3 pt-3">
                <button type="button" @click="showDeleteModal = false" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-2xl font-black text-xs transition">
                    Batal
                </button>
                <form action="{{ route('admin.extracurricular.destroy', $extracurricular) }}" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl font-black text-xs transition shadow-md active:scale-95">
                        Ya, Hapus Unit
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

{{-- Modal Tetapkan Pengurus --}}
<div id="modalAssignLeadership" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border-2 border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
        <div class="bg-gradient-to-r from-blue-700 to-indigo-900 p-6 text-white flex items-center justify-between">
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
                <input type="text" name="manager_name" value="{{ old('manager_name', $extracurricular->manager_name) }}" placeholder="Contoh: Manager Marching Band Yayasan Pembda" class="w-full rounded-2xl border-2 border-slate-300 font-black text-slate-900 text-xs p-3">
            </div>
            @endif

            <div>
                <label class="block font-black text-slate-800 mb-1 text-xs">Guru Pembina / Pelatih</label>
                <select name="advisor_teacher_id" class="w-full rounded-2xl border-2 border-slate-300 font-black text-slate-900 text-xs p-3">
                    <option value="">-- Manual / Pembina Luar --</option>
                    @foreach($teachers as $t)
                    <option value="{{ $t->id }}" {{ $extracurricular->advisor_teacher_id == $t->id ? 'selected' : '' }}>{{ $t->full_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-black text-slate-800 mb-1 text-xs">{{ $extracurricular->isFoundationLevel() ? 'Field Commander / Gitapati Siswa' : 'Ketua / Koordinator Siswa' }}</label>
                <select name="leader_student_id" class="w-full rounded-2xl border-2 border-slate-300 font-black text-slate-900 text-xs p-3">
                    <option value="">-- Pilih Siswa (Boleh Kosong) --</option>
                    @foreach($students as $s)
                    <option value="{{ $s->id }}" {{ $extracurricular->leader_student_id == $s->id ? 'selected' : '' }}>{{ $s->full_name }} ({{ $s->school->short_name ?? '' }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-black text-slate-800 mb-1 text-xs">Sekretaris Siswa</label>
                <select name="secretary_student_id" class="w-full rounded-2xl border-2 border-slate-300 font-black text-slate-900 text-xs p-3">
                    <option value="">-- Pilih Siswa (Boleh Kosong) --</option>
                    @foreach($students as $s)
                    <option value="{{ $s->id }}" {{ $extracurricular->secretary_student_id == $s->id ? 'selected' : '' }}>{{ $s->full_name }} ({{ $s->school->short_name ?? '' }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-black text-slate-800 mb-1 text-xs">Bendahara Siswa</label>
                <select name="treasurer_student_id" class="w-full rounded-2xl border-2 border-slate-300 font-black text-slate-900 text-xs p-3">
                    <option value="">-- Pilih Siswa (Boleh Kosong) --</option>
                    @foreach($students as $s)
                    <option value="{{ $s->id }}" {{ $extracurricular->treasurer_student_id == $s->id ? 'selected' : '' }}>{{ $s->full_name }} ({{ $s->school->short_name ?? '' }})</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="document.getElementById('modalAssignLeadership').classList.add('hidden')" class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-2xl font-bold transition">Batal</button>
                <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black transition shadow-md active:scale-95">Simpan Struktur</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Tambah Member Manual --}}
<div id="modalAddMember" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl border-2 border-slate-200 overflow-hidden flex flex-col">
        <div class="bg-gradient-to-r from-blue-700 to-indigo-900 p-6 text-white flex items-center justify-between">
            <h3 class="font-black text-base text-white">Tambah Anggota Siswa Manual</h3>
            <button onclick="document.getElementById('modalAddMember').classList.add('hidden')" class="text-white hover:text-slate-200 text-2xl font-bold">&times;</button>
        </div>

        <form action="{{ route('admin.extracurricular.members.add', $extracurricular) }}" method="POST" class="p-6 space-y-4 text-xs">
            @csrf

            <div>
                <label class="block font-black text-slate-800 mb-1 text-xs">Pilih Siswa *</label>
                <select name="student_id" required class="w-full rounded-2xl border-2 border-slate-300 font-black text-slate-900 text-xs p-3">
                    @foreach($students as $s)
                    <option value="{{ $s->id }}">{{ $s->full_name }} ({{ $s->school->short_name ?? '' }} - NISN: {{ $s->nisn ?: '-' }})</option>
                    @endforeach
                </select>
            </div>

            @if(!empty($extracurricular->section_list))
            <div>
                <label class="block font-black text-slate-800 mb-1 text-xs">Section / Alat Musik</label>
                <select name="section" class="w-full rounded-2xl border-2 border-slate-300 font-black text-slate-900 text-xs p-3">
                    <option value="">-- Pilih Section (Opsional) --</option>
                    @foreach($extracurricular->section_list as $secKey => $secVal)
                    <option value="{{ is_numeric($secKey) ? $secVal : $secKey }}">{{ $secVal }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block font-black text-slate-800 mb-1 text-xs">Peran / Jabatan *</label>
                <select name="role" required class="w-full rounded-2xl border-2 border-slate-300 font-black text-slate-900 text-xs p-3">
                    <option value="anggota">Anggota Aktif</option>
                    <option value="sie_kegiatan">Seksi Kegiatan / Section Leader</option>
                    <option value="ketua">Ketua / Field Commander</option>
                    <option value="sekretaris">Sekretaris</option>
                    <option value="bendahara">Bendahara</option>
                </select>
            </div>

            <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="document.getElementById('modalAddMember').classList.add('hidden')" class="px-5 py-3 bg-slate-100 text-slate-800 rounded-2xl font-bold">Batal</button>
                <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black shadow-md active:scale-95">Tambahkan Siswa</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Catat Kegiatan --}}
<div id="modalAddActivity" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl border-2 border-slate-200 overflow-hidden flex flex-col">
        <div class="bg-gradient-to-r from-blue-700 to-indigo-900 p-6 text-white flex items-center justify-between">
            <h3 class="font-black text-base text-white">Catat Log Kegiatan / Latihan</h3>
            <button onclick="document.getElementById('modalAddActivity').classList.add('hidden')" class="text-white hover:text-slate-200 text-2xl font-bold">&times;</button>
        </div>

        <form action="{{ route('admin.extracurricular.activities.add', $extracurricular) }}" method="POST" class="p-6 space-y-4 text-xs">
            @csrf

            <div>
                <label class="block font-black text-slate-800 mb-1 text-xs">Nama / Topik Kegiatan *</label>
                <input type="text" name="title" required placeholder="Contoh: Latihan Baris-Berbaris Persiapan Upacara" class="w-full rounded-2xl border-2 border-slate-300 font-black text-slate-900 text-xs p-3">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-black text-slate-800 mb-1 text-xs">Tanggal Kegiatan *</label>
                    <input type="date" name="activity_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-2xl border-2 border-slate-300 font-black text-slate-900 text-xs p-3">
                </div>

                <div>
                    <label class="block font-black text-slate-800 mb-1 text-xs">Lokasi Latihan</label>
                    <input type="text" name="location" value="{{ $extracurricular->location ?: 'Kampus Pembda' }}" class="w-full rounded-2xl border-2 border-slate-300 font-black text-slate-900 text-xs p-3">
                </div>
            </div>

            <div>
                <label class="block font-black text-slate-800 mb-1 text-xs">Deskripsi / Hasil Latihan</label>
                <textarea name="description" rows="3" placeholder="Uraian ringkas materi yang dilatih dan catatan kehadiran anggota..." class="w-full rounded-2xl border-2 border-slate-300 text-slate-900 text-xs font-semibold p-3"></textarea>
            </div>

            <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                <button type="button" onclick="document.getElementById('modalAddActivity').classList.add('hidden')" class="px-5 py-3 bg-slate-100 text-slate-800 rounded-2xl font-bold">Batal</button>
                <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black shadow-md active:scale-95">Simpan Kegiatan</button>
            </div>
        </form>
    </div>
</div>
@endsection
