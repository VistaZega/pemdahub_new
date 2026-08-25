@extends('layouts.mobile')

@section('title', $extracurricular->name . ' - Detail Mobile')

@section('content')
<div class="space-y-4 pb-20" x-data="{ activeTab: 'members', showAddActivityModal: false }">

    {{-- Top Bar --}}
    <div class="flex items-center justify-between pt-1">
        <a href="{{ route('mobile.guru.ekskul') }}" class="w-10 h-10 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center justify-center text-slate-700 active:scale-95 transition">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div class="text-center">
            <h1 class="text-sm font-black text-slate-900 leading-tight truncate max-w-[200px]">{{ $extracurricular->name }}</h1>
            <p class="text-[10px] text-indigo-600 font-bold uppercase tracking-wider">{{ $extracurricular->school->short_name ?? 'Tingkat Yayasan' }}</p>
        </div>
        @if($extracurricular->forum_group_id)
        <a href="{{ route('mobile.space.index') }}" class="w-10 h-10 rounded-2xl bg-purple-50 border border-purple-200 text-purple-700 flex items-center justify-center text-sm active:scale-95 transition" title="Pembda Space">
            <i class="fa-solid fa-comments"></i>
        </a>
        @else
        <div class="w-10"></div>
        @endif
    </div>

    {{-- Hero Card --}}
    <div class="bg-gradient-to-br from-indigo-900 via-purple-900 to-slate-950 p-5 rounded-3xl text-white relative overflow-hidden shadow-lg border border-indigo-800/40 space-y-3">
        <div class="flex items-start gap-3">
            <div class="w-14 h-14 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-3xl shadow-inner flex-shrink-0">
                {{ $extracurricular->display_icon }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-1.5 mb-1">
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-indigo-500 text-white shadow-xs">
                        {{ $extracurricular->category_label }}
                    </span>
                    @if($extracurricular->isFoundationLevel())
                    <span class="px-2 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider bg-amber-400 text-slate-950">
                        🏛️ Yayasan
                    </span>
                    @endif
                </div>
                <h2 class="text-base font-black text-white leading-snug">{{ $extracurricular->name }}</h2>
            </div>
        </div>

        <p class="text-[11px] text-slate-200 leading-relaxed">
            {{ $extracurricular->description ?: 'Unit kegiatan pembinaan bakat, minat, dan kepemimpinan siswa.' }}
        </p>

        {{-- Meta Badges --}}
        <div class="grid grid-cols-2 gap-2 pt-1 text-xs">
            <div class="bg-white/10 rounded-2xl p-2.5 border border-white/10">
                <p class="text-[9px] text-slate-300 font-bold uppercase">📅 Jadwal</p>
                <p class="font-bold text-white text-[11px] truncate mt-0.5">{{ $extracurricular->schedule_day_time ?: 'Fleksibel' }}</p>
            </div>
            <div class="bg-white/10 rounded-2xl p-2.5 border border-white/10">
                <p class="text-[9px] text-slate-300 font-bold uppercase">📍 Lokasi</p>
                <p class="font-bold text-white text-[11px] truncate mt-0.5">{{ $extracurricular->location ?: 'Kampus Pembda' }}</p>
            </div>
        </div>
    </div>

    {{-- Leadership Structure Card --}}
    <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-xs space-y-2.5">
        <h3 class="text-[11px] font-black text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-sitemap text-indigo-600"></i> Struktur Pengurus
        </h3>

        <div class="grid grid-cols-2 gap-2 text-[11px]">
            <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-100">
                <p class="text-[9px] font-bold text-slate-400 uppercase">{{ $extracurricular->isFoundationLevel() ? 'Manager Unit' : 'Pembina PKS' }}</p>
                <p class="font-black text-slate-900 truncate mt-0.5">{{ $extracurricular->manager_name ?: ($extracurricular->advisor_name ?: 'PKS Kesiswaan') }}</p>
            </div>
            <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-100">
                <p class="text-[9px] font-bold text-slate-400 uppercase">{{ $extracurricular->isFoundationLevel() ? 'Field Commander' : 'Ketua Siswa' }}</p>
                <p class="font-black text-slate-900 truncate mt-0.5">{{ $extracurricular->leader->full_name ?? '-' }}</p>
            </div>
            <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-100">
                <p class="text-[9px] font-bold text-slate-400 uppercase">Sekretaris</p>
                <p class="font-black text-slate-900 truncate mt-0.5">{{ $extracurricular->secretary->full_name ?? '-' }}</p>
            </div>
            <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-100">
                <p class="text-[9px] font-bold text-slate-400 uppercase">Bendahara</p>
                <p class="font-black text-slate-900 truncate mt-0.5">{{ $extracurricular->treasurer->full_name ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- Tabs Segmented Control --}}
    <div class="bg-slate-200/80 p-1 rounded-2xl flex text-xs font-bold shadow-inner">
        <button @click="activeTab = 'members'" :class="activeTab === 'members' ? 'bg-white text-indigo-900 shadow-xs font-black' : 'text-slate-600'" class="flex-1 py-2 rounded-xl transition flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-users"></i>
            <span>Anggota ({{ $extracurricular->members->count() }})</span>
        </button>
        <button @click="activeTab = 'activities'" :class="activeTab === 'activities' ? 'bg-white text-indigo-900 shadow-xs font-black' : 'text-slate-600'" class="flex-1 py-2 rounded-xl transition flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-calendar-check"></i>
            <span>Log Latihan ({{ $extracurricular->activities->count() }})</span>
        </button>
    </div>

    {{-- TAB 1: ANGGOTA SISWA --}}
    <div x-show="activeTab === 'members'" class="space-y-3" x-transition.duration.200ms>
        @forelse($extracurricular->members as $member)
        <div class="bg-white p-3.5 rounded-3xl border border-slate-200/80 shadow-xs flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-2xl overflow-hidden bg-slate-100 border border-slate-200 flex-shrink-0">
                    <img src="{{ $member->student->photo_url }}" class="w-full h-full object-cover" alt="{{ $member->student->full_name }}" onerror="this.onerror=null; this.src='{{ asset('images/default-student.jpg') }}';">
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-1">
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase text-white bg-gradient-to-r {{ $member->role_badge_color }}">
                            {{ $member->role_label }}
                        </span>
                        @if($member->section)
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-purple-100 text-purple-800 border border-purple-200">
                            🎺 {{ $member->section }}
                        </span>
                        @endif
                    </div>
                    <p class="font-black text-slate-900 text-xs mt-0.5 truncate">{{ $member->student->full_name }}</p>
                    <p class="text-[10px] text-slate-400 font-medium">
                        {{ $member->student->school->short_name ?? '' }} &bull; {{ $member->student->currentClassroom->first()->class_name ?? ($member->student->classroom->class_name ?? '-') }}
                    </p>
                </div>
            </div>

            <div class="flex-shrink-0 text-right">
                @if($member->status === 'approved')
                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl text-[10px] font-black inline-flex items-center gap-1">
                    <i class="fa-solid fa-check"></i> +{{ $member->points_awarded }}
                </span>
                @else
                <form action="{{ route('mobile.guru.ekskul.member.approve', [$extracurricular, $member]) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white rounded-xl text-[10px] font-black shadow-xs active:scale-95 transition">
                        Setujui (+15)
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="p-8 bg-white rounded-3xl border border-dashed border-slate-300 text-center space-y-2">
            <p class="text-3xl">👥</p>
            <p class="font-bold text-slate-800 text-xs">Belum Ada Anggota Terdaftar</p>
            <p class="text-[11px] text-slate-500">Siswa dapat mengklaim keanggotaan melalui portal siswa.</p>
        </div>
        @endforelse
    </div>

    {{-- TAB 2: LOG LATIHAN & KEGIATAN --}}
    <div x-show="activeTab === 'activities'" class="space-y-3" x-transition.duration.200ms style="display: none;">
        <button @click="showAddActivityModal = true" class="w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-2xl text-xs font-black shadow-xs active:scale-95 transition flex items-center justify-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>Catat Latihan / Kegiatan Baru</span>
        </button>

        @forelse($extracurricular->activities as $act)
        <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-xs space-y-2">
            <div class="flex items-start justify-between gap-2">
                <h4 class="font-black text-slate-900 text-xs leading-snug">{{ $act->title }}</h4>
                <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 text-[9px] font-bold flex-shrink-0">
                    {{ \Carbon\Carbon::parse($act->activity_date)->translatedFormat('d M Y') }}
                </span>
            </div>
            @if($act->description)
            <p class="text-[11px] text-slate-600 leading-relaxed">{{ $act->description }}</p>
            @endif
            @if($act->location)
            <p class="text-[10px] text-slate-400 flex items-center gap-1">
                <i class="fa-solid fa-location-dot"></i>
                <span>{{ $act->location }}</span>
            </p>
            @endif
        </div>
        @empty
        <div class="p-8 bg-white rounded-3xl border border-dashed border-slate-300 text-center space-y-2">
            <p class="text-3xl">📅</p>
            <p class="font-bold text-slate-800 text-xs">Belum Ada Catatan Kegiatan</p>
            <p class="text-[11px] text-slate-500">Klik tombol di atas untuk mencatat sesi latihan pertama.</p>
        </div>
        @endforelse
    </div>

    {{-- Modal Tambah Kegiatan Mobile --}}
    <div x-show="showAddActivityModal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4" style="display: none;">
        <div @click.away="showAddActivityModal = false" class="bg-white w-full max-w-md rounded-t-3xl sm:rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
            <div class="bg-gradient-to-r from-slate-900 to-indigo-950 p-4 text-white flex items-center justify-between">
                <h3 class="font-black text-xs text-white">Catat Log Latihan Baru</h3>
                <button @click="showAddActivityModal = false" class="text-slate-400 hover:text-white text-base">&times;</button>
            </div>

            <form action="{{ route('mobile.guru.ekskul.activity.store', $extracurricular) }}" method="POST" class="p-5 space-y-3 text-xs overflow-y-auto">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama / Topik Kegiatan *</label>
                    <input type="text" name="title" required placeholder="Contoh: Latihan Rutin & Formasi" class="w-full rounded-xl border-slate-300 font-semibold text-xs">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal *</label>
                        <input type="date" name="activity_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border-slate-300 font-semibold text-xs">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Lokasi</label>
                        <input type="text" name="location" value="{{ $extracurricular->location ?: 'Kampus Pembda' }}" class="w-full rounded-xl border-slate-300 font-semibold text-xs">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Uraian / Catatan</label>
                    <textarea name="description" rows="3" placeholder="Ringkasan materi latihan..." class="w-full rounded-xl border-slate-300 text-xs"></textarea>
                </div>

                <div class="pt-2 flex items-center gap-2">
                    <button type="button" @click="showAddActivityModal = false" class="flex-1 py-2.5 bg-slate-100 text-slate-700 rounded-xl font-bold">Batal</button>
                    <button type="submit" class="flex-1 py-2.5 bg-indigo-600 text-white rounded-xl font-bold shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
