@extends('mobile.layouts.app')

@section('title', $extracurricular->name . ' - Detail Mobile')

@section('content')
<div class="space-y-4 pb-20" x-data="{ activeTab: 'members', showAddActivityModal: false }">

    {{-- Top Bar --}}
    <div class="flex items-center justify-between pt-1">
        <a href="{{ route('mobile.guru.ekskul') }}" class="w-10 h-10 rounded-2xl bg-white border border-slate-200 shadow-2xs flex items-center justify-center text-slate-700 active:scale-95 transition">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div class="text-center">
            <h1 class="text-sm font-black text-slate-900 leading-tight truncate max-w-[200px]">{{ $extracurricular->name }}</h1>
            <p class="text-[10px] text-indigo-700 font-bold uppercase tracking-wider">{{ $extracurricular->school->short_name ?? 'Tingkat Yayasan' }}</p>
        </div>
        @if($extracurricular->forum_group_id)
        <a href="{{ route('mobile.space.index') }}" class="w-10 h-10 rounded-2xl bg-purple-50 border border-purple-200 text-purple-700 flex items-center justify-center text-sm active:scale-95 transition" title="Pembda Space">
            <i class="fa-solid fa-comments"></i>
        </a>
        @else
        <div class="w-10"></div>
        @endif
    </div>

    {{-- Hero Card (Clean Light Theme UI/UX Pro Max) --}}
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs space-y-3">
        <div class="flex items-start gap-3.5">
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 border-2 border-indigo-100 text-indigo-700 flex items-center justify-center text-3xl shadow-2xs flex-shrink-0">
                {{ $extracurricular->display_icon }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-1.5 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-indigo-100 text-indigo-900 border border-indigo-200">
                        {{ $extracurricular->category_label }}
                    </span>
                    @if($extracurricular->isFoundationLevel())
                    <span class="px-2 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300">
                        🏛️ Yayasan
                    </span>
                    @endif
                </div>
                <h2 class="text-base font-black text-slate-900 leading-snug">{{ $extracurricular->name }}</h2>
            </div>
        </div>

        <p class="text-xs text-slate-600 leading-relaxed font-medium">
            {{ $extracurricular->description ?: 'Unit kegiatan pembinaan bakat, minat, dan kepemimpinan siswa.' }}
        </p>

        {{-- Meta Badges --}}
        <div class="grid grid-cols-2 gap-2 pt-1 text-xs">
            <div class="bg-indigo-50/70 rounded-2xl p-3 border border-indigo-100">
                <p class="text-[9px] text-indigo-700 font-black uppercase">📅 Jadwal</p>
                <p class="font-black text-slate-900 text-xs truncate mt-0.5">{{ $extracurricular->schedule_day_time ?: 'Fleksibel' }}</p>
            </div>
            <div class="bg-blue-50/70 rounded-2xl p-3 border border-blue-100">
                <p class="text-[9px] text-blue-700 font-black uppercase">📍 Lokasi</p>
                <p class="font-black text-slate-900 text-xs truncate mt-0.5">{{ $extracurricular->location ?: 'Kampus Pembda' }}</p>
            </div>
        </div>
    </div>

    {{-- Leadership Structure Card --}}
    <div class="bg-white p-4.5 rounded-3xl border border-slate-200 shadow-xs space-y-2.5">
        <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-sitemap text-indigo-600"></i> Struktur Pengurus Unit
        </h3>

        <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-200/80">
                <p class="text-[9px] font-black text-slate-500 uppercase">{{ $extracurricular->isFoundationLevel() ? 'Manager Unit' : 'Pembina PKS' }}</p>
                <p class="font-black text-slate-900 truncate mt-0.5 text-[11px]">{{ $extracurricular->manager_name ?: ($extracurricular->advisor_name ?: 'PKS Kesiswaan') }}</p>
            </div>
            <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-200/80">
                <p class="text-[9px] font-black text-slate-500 uppercase">{{ $extracurricular->isFoundationLevel() ? 'Field Commander' : 'Ketua Siswa' }}</p>
                <p class="font-black text-slate-900 truncate mt-0.5 text-[11px]">{{ $extracurricular->leader->full_name ?? '-' }}</p>
            </div>
            <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-200/80">
                <p class="text-[9px] font-black text-slate-500 uppercase">Sekretaris</p>
                <p class="font-black text-slate-900 truncate mt-0.5 text-[11px]">{{ $extracurricular->secretary->full_name ?? '-' }}</p>
            </div>
            <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-200/80">
                <p class="text-[9px] font-black text-slate-500 uppercase">Bendahara</p>
                <p class="font-black text-slate-900 truncate mt-0.5 text-[11px]">{{ $extracurricular->treasurer->full_name ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- Tabs Segmented Control --}}
    <div class="bg-slate-100 p-1 rounded-2xl flex text-xs font-bold border border-slate-200 shadow-2xs">
        <button @click="activeTab = 'members'" :class="activeTab === 'members' ? 'bg-white text-indigo-900 shadow-xs font-black border border-slate-200' : 'text-slate-600'" class="flex-1 py-2 rounded-xl transition flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-users"></i>
            <span>Anggota ({{ $extracurricular->members->count() }})</span>
        </button>
        <button @click="activeTab = 'activities'" :class="activeTab === 'activities' ? 'bg-white text-indigo-900 shadow-xs font-black border border-slate-200' : 'text-slate-600'" class="flex-1 py-2 rounded-xl transition flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-calendar-check"></i>
            <span>Log Latihan ({{ $extracurricular->activities->count() }})</span>
        </button>
    </div>

    {{-- TAB 1: MEMBERS --}}
    <div x-show="activeTab === 'members'" class="space-y-2.5" x-transition.duration.200ms>
        @forelse($extracurricular->members as $member)
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-2xs flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 flex-shrink-0">
                    <img src="{{ $member->student->photo_url }}" class="w-full h-full object-cover" alt="{{ $member->student->full_name }}" onerror="this.onerror=null; this.src='{{ asset('images/default-student.jpg') }}';">
                </div>
                <div class="min-w-0">
                    <p class="font-black text-slate-900 text-xs truncate">{{ $member->student->full_name }}</p>
                    <div class="flex items-center gap-1.5 mt-0.5 text-[10px]">
                        <span class="font-bold text-slate-500">{{ $member->student->school->short_name ?? '' }}</span>
                        @if($member->section)
                        <span class="text-purple-700 font-bold">&bull; {{ $member->section }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                @if($member->status === 'approved')
                <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                    ✓ Aktif
                </span>
                @else
                <form action="{{ route('mobile.guru.ekskul.approve', $member) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-[10px] shadow-2xs active:scale-95 transition">
                        Setujui
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="p-8 bg-white rounded-3xl border-2 border-dashed border-slate-200 text-center text-xs text-slate-400 font-medium">
            Belum ada anggota siswa yang terdaftar di unit ini.
        </div>
        @endforelse
    </div>

    {{-- TAB 2: ACTIVITIES --}}
    <div x-show="activeTab === 'activities'" class="space-y-3" x-transition.duration.200ms style="display: none;">
        <button @click="showAddActivityModal = true" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black text-xs shadow-xs active:scale-95 transition flex items-center justify-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>Catat Kegiatan / Sesi Latihan Baru</span>
        </button>

        @forelse($extracurricular->activities as $act)
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs space-y-1.5">
            <div class="flex items-center justify-between text-[10px]">
                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 font-bold rounded-md border border-indigo-100">
                    {{ $act->activity_date->translatedFormat('d M Y') }}
                </span>
                <span class="text-slate-500 font-medium">
                    <i class="fa-solid fa-location-dot text-rose-500"></i> {{ $act->location ?: 'Kampus' }}
                </span>
            </div>
            <h4 class="font-black text-slate-900 text-xs">{{ $act->title }}</h4>
            <p class="text-[11px] text-slate-600 leading-relaxed font-normal">{{ $act->description ?: 'Latihan rutin unit ekstrakurikuler.' }}</p>
        </div>
        @empty
        <div class="p-8 bg-white rounded-3xl border-2 border-dashed border-slate-200 text-center text-xs text-slate-400 font-medium">
            Belum ada sesi latihan yang dicatat.
        </div>
        @endforelse
    </div>

    {{-- Modal Tambah Kegiatan --}}
    <div x-show="showAddActivityModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
        <div @click.away="showAddActivityModal = false" class="bg-white w-full max-w-sm rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col p-5 space-y-3.5 text-xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h3 class="font-black text-slate-900 text-sm">Catat Sesi Kegiatan</h3>
                <button @click="showAddActivityModal = false" class="text-slate-400 hover:text-slate-700 text-lg">&times;</button>
            </div>

            <form action="{{ route('mobile.guru.ekskul.activity', $extracurricular) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama / Topik Kegiatan *</label>
                    <input type="text" name="title" required placeholder="Contoh: Latihan Rutin Marching Band" class="w-full rounded-xl border-slate-300 font-semibold text-slate-900">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tanggal *</label>
                    <input type="date" name="activity_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border-slate-300 font-semibold text-slate-900">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Lokasi Latihan</label>
                    <input type="text" name="location" value="{{ $extracurricular->location ?: 'Kampus Pembda' }}" class="w-full rounded-xl border-slate-300 font-semibold text-slate-900">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Deskripsi Ringkas</label>
                    <textarea name="description" rows="2" placeholder="Catatan jalannya latihan..." class="w-full rounded-xl border-slate-300 text-slate-900"></textarea>
                </div>

                <div class="p-2.5 bg-purple-50 border border-purple-200 rounded-xl flex items-start gap-2.5">
                    <input type="checkbox" name="broadcast_to_space" value="1" id="m_chk_broadcast" checked class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500 mt-0.5">
                    <label for="m_chk_broadcast" class="text-[11px] font-bold text-purple-950 cursor-pointer">
                        <span>📢 Siarkan ke Pembda Space</span>
                        <p class="text-[10px] font-normal text-purple-800">Otomatis diposting sebagai showcase ekskul.</p>
                    </label>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" @click="showAddActivityModal = false" class="px-3.5 py-2 bg-slate-100 text-slate-700 rounded-xl font-bold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-xs">Simpan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
