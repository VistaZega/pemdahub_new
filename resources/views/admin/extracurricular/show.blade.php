@extends('layouts.admin')

@section('title', $extracurricular->name . ' - Detail Unit Ekstrakurikuler')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'members' }">

    {{-- Breadcrumb & Back --}}
    <div class="flex items-center justify-between text-xs">
        <a href="{{ route('admin.extracurricular.index') }}" class="font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 transition">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali ke Daftar Ekstrakurikuler</span>
        </a>
        <span class="text-slate-400 font-medium">Unit: <b>{{ $extracurricular->school->name ?? 'Yayasan Perguruan Pembda (Lintas Unit Sekolah)' }}</b></span>
    </div>

    {{-- Hero Banner --}}
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-purple-950 rounded-3xl p-6 sm:p-8 text-white relative overflow-hidden shadow-lg border border-slate-800">
        <div class="absolute -top-12 -right-12 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
            <div class="flex items-start gap-4">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-3xl sm:text-4xl shadow-inner flex-shrink-0">
                    {{ $extracurricular->display_icon }}
                </div>
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-indigo-500 text-white shadow-xs">
                            {{ $extracurricular->category_label }}
                        </span>
                        <span class="text-xs text-indigo-300 font-semibold">{{ $extracurricular->school->short_name ?? ($extracurricular->school->name ?? 'Tingkat Yayasan') }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">{{ $extracurricular->name }}</h1>
                    <p class="text-xs sm:text-sm text-slate-300 max-w-2xl leading-relaxed">
                        {{ $extracurricular->description ?: 'Unit kegiatan pembinaan bakat, minat, dan kepemimpinan siswa.' }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 flex-shrink-0">
                @if($extracurricular->forum_group_id)
                <a href="{{ route('space.index', ['group' => $extracurricular->forum_group_id]) }}" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-bold text-xs shadow-md transition flex items-center gap-2 active:scale-95">
                    <i class="fas fa-comments"></i>
                    <span>Kanal Space Ekskul</span>
                </a>
                @endif
                <button onclick="document.getElementById('modalAssignLeadership').classList.remove('hidden')" class="px-4 py-2.5 bg-white text-slate-900 hover:bg-slate-100 rounded-xl font-bold text-xs shadow-md transition flex items-center gap-2 active:scale-95">
                    <i class="fas fa-user-gear text-indigo-600"></i>
                    <span>Struktur Pengurus</span>
                </button>
            </div>
        </div>

        {{-- Meta Badges Bar --}}
        <div class="mt-6 pt-5 border-t border-white/10 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div class="bg-white/5 p-2.5 rounded-xl border border-white/10">
                <p class="text-[10px] text-slate-400 font-bold uppercase">Jadwal Latihan</p>
                <p class="font-bold text-white mt-0.5 truncate">{{ $extracurricular->schedule_day_time ?: 'Fleksibel' }}</p>
            </div>
            <div class="bg-white/5 p-2.5 rounded-xl border border-white/10">
                <p class="text-[10px] text-slate-400 font-bold uppercase">Lokasi / Tempat</p>
                <p class="font-bold text-white mt-0.5 truncate">{{ $extracurricular->location ?: 'Kampus Pembda' }}</p>
            </div>
            <div class="bg-white/5 p-2.5 rounded-xl border border-white/10">
                <p class="text-[10px] text-slate-400 font-bold uppercase">Total Anggota</p>
                <p class="font-bold text-indigo-300 mt-0.5">{{ $extracurricular->members->where('status', 'approved')->count() }} Siswa Aktif</p>
            </div>
            <div class="bg-white/5 p-2.5 rounded-xl border border-white/10">
                <p class="text-[10px] text-slate-400 font-bold uppercase">Sesi Latihan</p>
                <p class="font-bold text-purple-300 mt-0.5">{{ $extracurricular->activities->count() }} Kegiatan</p>
            </div>
        </div>
    </div>

    {{-- 4-Card Leadership Structure --}}
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-sitemap text-indigo-600"></i> Struktur Pengurus Unit Ekskul
            </h2>
            <button onclick="document.getElementById('modalAssignLeadership').classList.remove('hidden')" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-edit"></i> Edit Pengurus
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Pembina / Manager --}}
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg flex-shrink-0">
                    👨‍🏫
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $extracurricular->isFoundationLevel() ? 'Manager Marching Band' : 'Pembina Guru / PKS' }}</p>
                    <p class="text-xs font-black text-slate-900 truncate">{{ $extracurricular->manager_name ?: ($extracurricular->advisor_name ?: ($extracurricular->advisor->full_name ?? 'PKS Kesiswaan')) }}</p>
                </div>
            </div>

            {{-- Ketua --}}
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg flex-shrink-0">
                    👑
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $extracurricular->isFoundationLevel() ? 'Field Commander / Gitapati' : 'Ketua Siswa' }}</p>
                    <p class="text-xs font-black text-slate-900 truncate">{{ $extracurricular->leader->full_name ?? 'Belum Ditentukan' }}</p>
                </div>
            </div>

            {{-- Sekretaris --}}
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg flex-shrink-0">
                    📝
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sekretaris Siswa</p>
                    <p class="text-xs font-black text-slate-900 truncate">{{ $extracurricular->secretary->full_name ?? 'Belum Ditentukan' }}</p>
                </div>
            </div>

            {{-- Bendahara --}}
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg flex-shrink-0">
                    💰
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Bendahara Siswa</p>
                    <p class="text-xs font-black text-slate-900 truncate">{{ $extracurricular->treasurer->full_name ?? 'Belum Ditentukan' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation (Members, Activities, Settings) --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50/50 px-5 flex items-center gap-6 text-xs font-bold">
            <button @click="activeTab = 'members'" :class="activeTab === 'members' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="py-4 border-b-2 transition flex items-center gap-2">
                <i class="fas fa-users"></i>
                <span>Anggota Siswa ({{ $extracurricular->members->count() }})</span>
            </button>
            <button @click="activeTab = 'activities'" :class="activeTab === 'activities' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="py-4 border-b-2 transition flex items-center gap-2">
                <i class="fas fa-calendar-check"></i>
                <span>Log Latihan & Kegiatan ({{ $extracurricular->activities->count() }})</span>
            </button>
            <button @click="activeTab = 'settings'" :class="activeTab === 'settings' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="py-4 border-b-2 transition flex items-center gap-2">
                <i class="fas fa-cog"></i>
                <span>Pengaturan Unit</span>
            </button>
        </div>

        {{-- TAB 1: MEMBERS --}}
        <div x-show="activeTab === 'members'" class="p-5 space-y-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Daftar Anggota & Klaim Keanggotaan</h3>
                    <p class="text-xs text-slate-500">Anggota yang telah disetujui otomatis mendapatkan +15 Poin Reputasi dan terkoneksi ke grup Pembda Space.</p>
                </div>
                <button onclick="document.getElementById('modalAddMember').classList.remove('hidden')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-xs transition flex items-center gap-1.5">
                    <i class="fas fa-user-plus"></i>
                    <span>Tambah Siswa Manual</span>
                </button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                            <th class="p-3.5 w-12 text-center">#</th>
                            <th class="p-3.5">Nama Siswa</th>
                            <th class="p-3.5">Sekolah & Kelas</th>
                            <th class="p-3.5 text-center">Section / Alat</th>
                            <th class="p-3.5 text-center">Jabatan</th>
                            <th class="p-3.5 text-center">Status</th>
                            <th class="p-3.5 text-center">Reward Poin</th>
                            <th class="p-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($extracurricular->members as $idx => $member)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="p-3.5 text-center font-bold text-slate-400">{{ $idx + 1 }}</td>
                            <td class="p-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 flex-shrink-0">
                                        <img src="{{ $member->student->photo_url }}" class="w-full h-full object-cover" alt="{{ $member->student->full_name }}" onerror="this.onerror=null; this.src='{{ asset('images/default-student.jpg') }}';">
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-900 text-xs">{{ $member->student->full_name }}</p>
                                        <p class="text-[10px] text-slate-500 font-mono">NISN: {{ $member->student->nisn ?: '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3.5 text-slate-700 font-semibold">
                                <div>{{ $member->student->school->short_name ?: $member->student->school->name }}</div>
                                <div class="text-[10px] text-slate-400 font-normal">{{ $member->student->currentClassroom->first()->class_name ?? ($member->student->classroom->class_name ?? '-') }}</div>
                            </td>
                            <td class="p-3.5 text-center">
                                @if($member->section)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-purple-100 text-purple-800 border border-purple-200">
                                    🎺 {{ $member->section }}
                                </span>
                                @else
                                <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="p-3.5 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold text-white bg-gradient-to-r {{ $member->role_badge_color }} shadow-2xs">
                                    {{ $member->role_label }}
                                </span>
                            </td>
                            <td class="p-3.5 text-center">
                                @if($member->status === 'approved')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    ✓ Aktif
                                </span>
                                @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                    Menunggu Approval
                                </span>
                                @endif
                            </td>
                            <td class="p-3.5 text-center font-bold text-indigo-600">
                                +{{ $member->points_awarded }} Poin
                            </td>
                            <td class="p-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($member->status !== 'approved')
                                    <form action="{{ route('admin.extracurricular.members.approve', $member) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-[11px] transition shadow-2xs" title="Setujui Klaim">
                                            Setujui
                                        </button>
                                    </form>
                                    @endif

                                    <form action="{{ route('admin.extracurricular.members.remove', $member) }}" method="POST" onsubmit="return confirm('Hapus siswa ini dari unit ekskul?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Hapus Anggota">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400 text-xs">
                                Belum ada siswa yang mendaftar di unit ekstrakurikuler ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TAB 2: ACTIVITIES --}}
        <div x-show="activeTab === 'activities'" class="p-5 space-y-4" style="display: none;">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Catatan Kegiatan, Sesi Latihan & Prestasi</h3>
                    <p class="text-xs text-slate-500">Dokumentasi resmi aktivitas ekskul yang merefleksikan portofolio siswa Pembda.</p>
                </div>
                <button onclick="document.getElementById('modalAddActivity').classList.remove('hidden')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-xs transition flex items-center gap-1.5">
                    <i class="fas fa-plus"></i>
                    <span>Catat Kegiatan Baru</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($extracurricular->activities as $act)
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800">
                            {{ $act->activity_date->translatedFormat('l, d F Y') }}
                        </span>
                        <span class="text-[10px] text-slate-400">{{ $act->location ?: 'Kampus Pembda' }}</span>
                    </div>
                    <h4 class="font-bold text-slate-900 text-xs">{{ $act->title }}</h4>
                    <p class="text-[11px] text-slate-600 leading-relaxed">{{ $act->description ?: 'Kegiatan latihan rutin unit ekstrakurikuler.' }}</p>
                </div>
                @empty
                <div class="col-span-full py-8 text-center text-slate-400 text-xs bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                    Belum ada log kegiatan latihan yang dicatat. Klik 'Catat Kegiatan Baru' di atas.
                </div>
                @endforelse
            </div>
        </div>

        {{-- TAB 3: SETTINGS --}}
        <div x-show="activeTab === 'settings'" class="p-6 max-w-2xl space-y-4" style="display: none;">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Edit Informasi Unit Ekstrakurikuler</h3>

            <form action="{{ route('admin.extracurricular.update', $extracurricular) }}" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Unit Kegiatan *</label>
                    <input type="text" name="name" value="{{ old('name', $extracurricular->name) }}" required class="w-full rounded-xl border-slate-300 font-semibold">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kategori *</label>
                        <select name="category" required class="w-full rounded-xl border-slate-300 font-semibold">
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
                        <label class="block font-bold text-slate-700 mb-1">Emoji / Icon Simbol</label>
                        <input type="text" name="icon" value="{{ old('icon', $extracurricular->icon) }}" placeholder="Contoh: ⚜️ atau 🎭" class="w-full rounded-xl border-slate-300 font-semibold">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Deskripsi Kegiatan</label>
                    <textarea name="description" rows="3" class="w-full rounded-xl border-slate-300">{{ old('description', $extracurricular->description) }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jadwal Latihan</label>
                        <input type="text" name="schedule_day_time" value="{{ old('schedule_day_time', $extracurricular->schedule_day_time) }}" class="w-full rounded-xl border-slate-300">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Lokasi Latihan</label>
                        <input type="text" name="location" value="{{ old('location', $extracurricular->location) }}" class="w-full rounded-xl border-slate-300">
                    </div>
                </div>

                <div class="pt-3 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Tetapkan Pengurus --}}
<div id="modalAssignLeadership" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
        <div class="bg-gradient-to-r from-slate-900 to-indigo-950 p-5 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="text-2xl">👥</span>
                <h3 class="font-black text-sm text-white">Tetapkan Struktur Pengurus Ekskul</h3>
            </div>
            <button onclick="document.getElementById('modalAssignLeadership').classList.add('hidden')" class="text-slate-400 hover:text-white text-lg">&times;</button>
        </div>

        <form action="{{ route('admin.extracurricular.leadership', $extracurricular) }}" method="POST" class="p-6 space-y-4 overflow-y-auto flex-1 text-xs">
            @csrf

            @if($extracurricular->isFoundationLevel() || $extracurricular->category === 'marching_band')
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Manager Unit Yayasan</label>
                <input type="text" name="manager_name" value="{{ old('manager_name', $extracurricular->manager_name) }}" placeholder="Contoh: Manager Marching Band Yayasan Pembda" class="w-full rounded-xl border-slate-300 font-semibold">
            </div>
            @endif

            <div>
                <label class="block font-bold text-slate-700 mb-1">Guru Pembina / Pelatih</label>
                <select name="advisor_teacher_id" class="w-full rounded-xl border-slate-300 font-semibold">
                    <option value="">-- Manual / Pembina Luar --</option>
                    @foreach($teachers as $t)
                    <option value="{{ $t->id }}" {{ $extracurricular->advisor_teacher_id == $t->id ? 'selected' : '' }}>{{ $t->full_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">{{ $extracurricular->isFoundationLevel() ? 'Field Commander / Gitapati Siswa' : 'Ketua / Koordinator Siswa' }}</label>
                <select name="leader_student_id" class="w-full rounded-xl border-slate-300 font-semibold">
                    <option value="">-- Pilih Siswa (Boleh Kosong) --</option>
                    @foreach($students as $s)
                    <option value="{{ $s->id }}" {{ $extracurricular->leader_student_id == $s->id ? 'selected' : '' }}>{{ $s->full_name }} ({{ $s->school->short_name ?? '' }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Sekretaris Siswa</label>
                <select name="secretary_student_id" class="w-full rounded-xl border-slate-300 font-semibold">
                    <option value="">-- Pilih Siswa (Boleh Kosong) --</option>
                    @foreach($students as $s)
                    <option value="{{ $s->id }}" {{ $extracurricular->secretary_student_id == $s->id ? 'selected' : '' }}>{{ $s->full_name }} ({{ $s->school->short_name ?? '' }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Bendahara Siswa</label>
                <select name="treasurer_student_id" class="w-full rounded-xl border-slate-300 font-semibold">
                    <option value="">-- Pilih Siswa (Boleh Kosong) --</option>
                    @foreach($students as $s)
                    <option value="{{ $s->id }}" {{ $extracurricular->treasurer_student_id == $s->id ? 'selected' : '' }}>{{ $s->full_name }} ({{ $s->school->short_name ?? '' }})</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="document.getElementById('modalAssignLeadership').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition shadow-sm">Simpan Struktur</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Tambah Member Manual --}}
<div id="modalAddMember" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col">
        <div class="bg-gradient-to-r from-slate-900 to-indigo-950 p-5 text-white flex items-center justify-between">
            <h3 class="font-black text-sm text-white">Tambah Anggota Siswa Manual</h3>
            <button onclick="document.getElementById('modalAddMember').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form action="{{ route('admin.extracurricular.members.add', $extracurricular) }}" method="POST" class="p-6 space-y-4 text-xs">
            @csrf

            <div>
                <label class="block font-bold text-slate-700 mb-1">Pilih Siswa *</label>
                <select name="student_id" required class="w-full rounded-xl border-slate-300 font-semibold">
                    @foreach($students as $s)
                    <option value="{{ $s->id }}">{{ $s->full_name }} ({{ $s->school->short_name ?? '' }} - NISN: {{ $s->nisn ?: '-' }})</option>
                    @endforeach
                </select>
            </div>

            @if(!empty($extracurricular->section_list))
            <div>
                <label class="block font-bold text-slate-700 mb-1">Section / Alat Musik</label>
                <select name="section" class="w-full rounded-xl border-slate-300 font-semibold">
                    <option value="">-- Pilih Section (Opsional) --</option>
                    @foreach($extracurricular->section_list as $secKey => $secVal)
                    <option value="{{ is_numeric($secKey) ? $secVal : $secKey }}">{{ $secVal }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block font-bold text-slate-700 mb-1">Peran / Jabatan *</label>
                <select name="role" required class="w-full rounded-xl border-slate-300 font-semibold">
                    <option value="anggota">Anggota Aktif</option>
                    <option value="sie_kegiatan">Seksi Kegiatan / Section Leader</option>
                    <option value="ketua">Ketua / Field Commander</option>
                    <option value="sekretaris">Sekretaris</option>
                    <option value="bendahara">Bendahara</option>
                </select>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="document.getElementById('modalAddMember').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl font-bold">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-sm">Tambahkan Siswa</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Catat Kegiatan --}}
<div id="modalAddActivity" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col">
        <div class="bg-gradient-to-r from-slate-900 to-indigo-950 p-5 text-white flex items-center justify-between">
            <h3 class="font-black text-sm text-white">Catat Log Kegiatan / Latihan</h3>
            <button onclick="document.getElementById('modalAddActivity').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form action="{{ route('admin.extracurricular.activities.add', $extracurricular) }}" method="POST" class="p-6 space-y-4 text-xs">
            @csrf

            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama / Topik Kegiatan *</label>
                <input type="text" name="title" required placeholder="Contoh: Latihan Baris-Berbaris Persiapan Upacara" class="w-full rounded-xl border-slate-300 font-semibold">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tanggal Kegiatan *</label>
                    <input type="date" name="activity_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border-slate-300 font-semibold">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Lokasi Latihan</label>
                    <input type="text" name="location" value="{{ $extracurricular->location ?: 'Kampus Pembda' }}" class="w-full rounded-xl border-slate-300">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Deskripsi / Hasil Latihan</label>
                <textarea name="description" rows="3" placeholder="Uraian ringkas materi yang dilatih dan catatan kehadiran anggota..." class="w-full rounded-xl border-slate-300"></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="document.getElementById('modalAddActivity').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl font-bold">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-sm">Simpan Kegiatan</button>
            </div>
        </form>
    </div>
</div>
@endsection
