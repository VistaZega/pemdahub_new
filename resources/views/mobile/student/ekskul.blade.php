@extends('mobile.layouts.app')

@section('title', 'Ekstrakurikuler Siswa')

@section('content')
<div class="space-y-4 pb-20" x-data="{ activeTab: 'my', activeModal: null }">

    {{-- Top Bar --}}
    <div class="flex items-center justify-between pt-1">
        <a href="{{ route('mobile.dashboard') }}" class="w-10 h-10 rounded-2xl bg-white border border-slate-200 shadow-2xs flex items-center justify-center text-slate-700 active:scale-95 transition">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div class="text-center">
            <h1 class="text-sm font-black text-slate-900 leading-tight">Ekstrakurikuler</h1>
            <p class="text-[10px] text-purple-700 font-bold uppercase tracking-wider">{{ $student->school->short_name ?: $student->school->name }}</p>
        </div>
        <a href="{{ route('mobile.space.index') }}" class="w-10 h-10 rounded-2xl bg-purple-50 border border-purple-200 text-purple-700 flex items-center justify-center text-sm active:scale-95 transition" title="Pembda Space">
            <i class="fa-solid fa-comments"></i>
        </a>
    </div>

    {{-- Hero Card (Clean Light Theme UI/UX Pro Max) --}}
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-xs space-y-3 relative overflow-hidden">
        <div class="flex items-center justify-between">
            <span class="px-2.5 py-1 bg-purple-100 text-purple-900 border border-purple-200 rounded-full text-[10px] font-black tracking-wider uppercase flex items-center gap-1">
                <span>🏆</span> Bakat & Karakter
            </span>
            <span class="text-[11px] font-black text-indigo-700">
                +15 s/d +30 Poin Reputasi
            </span>
        </div>

        <div class="space-y-1">
            <h2 class="text-base font-black text-slate-900 leading-snug">Unit Kegiatan Siswa</h2>
            <p class="text-xs text-slate-600 leading-relaxed font-medium">
                Setiap keaktifan ekstrakurikuler otomatis mendongkrak profil <b>DNA 360°</b> dan membuka kanal diskusi ekskul di <b>Pembda Space</b>!
            </p>
        </div>

        {{-- Counter Pill --}}
        <div class="grid grid-cols-2 gap-2 pt-1">
            <div class="bg-indigo-50/70 rounded-2xl p-2.5 border border-indigo-100 text-center">
                <p class="text-[10px] text-indigo-700 font-black uppercase">Diikuti</p>
                <p class="text-base font-black text-slate-900">{{ $myMemberships->count() }} <span class="text-[10px] font-bold text-slate-500">Unit</span></p>
            </div>
            <div class="bg-amber-50/70 rounded-2xl p-2.5 border border-amber-200 text-center">
                <p class="text-[10px] text-amber-700 font-black uppercase">Tersedia</p>
                <p class="text-base font-black text-amber-900">{{ $availableEkskuls->count() }} <span class="text-[10px] font-bold text-slate-500">Pilihan</span></p>
            </div>
        </div>
    </div>

    {{-- Tabs Segmented Control --}}
    <div class="bg-slate-100 p-1 rounded-2xl flex text-xs font-bold border border-slate-200 shadow-2xs">
        <button @click="activeTab = 'my'" :class="activeTab === 'my' ? 'bg-white text-indigo-800 shadow-xs font-black border border-slate-200' : 'text-slate-600'" class="flex-1 py-2 rounded-xl transition flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-id-badge"></i>
            <span>Ekskul Saya ({{ $myMemberships->count() }})</span>
        </button>
        <button @click="activeTab = 'explore'" :class="activeTab === 'explore' ? 'bg-white text-indigo-800 shadow-xs font-black border border-slate-200' : 'text-slate-600'" class="flex-1 py-2 rounded-xl transition flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-compass"></i>
            <span>Jelajahi ({{ $availableEkskuls->count() }})</span>
        </button>
    </div>

    {{-- TAB 1: EKSKUL SAYA (DIGITAL SQUAD PASS) --}}
    <div x-show="activeTab === 'my'" class="space-y-3.5" x-transition.duration.200ms>
        @forelse($myMemberships as $membership)
        @php 
            $ekskul = $membership->extracurricular;
            $activeSquad = $ekskul->activeMembers ?? collect();
            $otherSquad = $activeSquad->where('student_id', '!=', $student->id);
            $isLeader = in_array($membership->role, ['ketua', 'wakil_ketua', 'sekretaris', 'bendahara']);
        @endphp
        <div class="relative bg-white rounded-3xl border-2 {{ $isLeader ? 'border-amber-300 ring-2 ring-amber-100 shadow-sm' : 'border-indigo-100 shadow-xs' }} overflow-hidden space-y-3">
            
            {{-- Header Ribbon --}}
            <div class="px-4 py-2 bg-gradient-to-r {{ $isLeader ? 'from-amber-600 via-orange-600 to-purple-700' : 'from-indigo-600 via-purple-600 to-pink-600' }} text-white flex items-center justify-between text-[9px] font-black">
                <span class="flex items-center gap-1 uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    OFFICIAL SQUAD PASS
                </span>
                <span class="font-mono text-indigo-100">
                    #EKS-{{ str_pad($ekskul->id, 2, '0', STR_PAD_LEFT) }}-{{ str_pad($membership->id, 4, '0', STR_PAD_LEFT) }}
                </span>
            </div>

            <div class="p-4 pt-1 space-y-3">
                {{-- Member Profile Header --}}
                <div class="flex items-center gap-3.5 bg-indigo-50/50 p-3 rounded-2xl border border-indigo-100/70">
                    <div class="relative shrink-0 w-10 h-10 mr-1.5">
                        <img src="{{ $student->photo_url }}" alt="{{ $student->full_name }}" class="w-10 h-10 rounded-xl object-cover border {{ $isLeader ? 'border-amber-400' : 'border-indigo-300' }} shadow-2xs bg-white" style="width: 40px; height: 40px; min-width: 40px; max-width: 40px;">
                        <span class="absolute bottom-0 right-0 w-3.5 h-3.5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[6px] border border-white">
                            <i class="fa-solid fa-check"></i>
                        </span>
                    </div>
                    <div class="min-w-0 flex-1 pl-0.5">
                        <div class="flex flex-wrap items-center gap-1.5 mb-1">
                            <span class="px-2 py-0.5 rounded-full text-[8px] font-black text-white bg-gradient-to-r {{ $membership->role_badge_color }} shadow-2xs">
                                {{ $isLeader ? '👑 ' : '' }}{{ $membership->role_label }}
                            </span>
                            @if($membership->section)
                            <span class="px-1.5 py-0.5 rounded-full text-[8px] font-black bg-purple-100 text-purple-900 border border-purple-200">
                                🎺 {{ $membership->section }}
                            </span>
                            @endif
                        </div>
                        <p class="font-black text-slate-900 text-xs truncate">{{ $student->full_name }}</p>
                    </div>
                </div>

                {{-- Ekskul Title --}}
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-2xl shadow-2xs shrink-0 mr-1.5" style="width: 40px; height: 40px; min-width: 40px; max-width: 40px;">
                        {{ $ekskul->display_icon }}
                    </div>
                    <div class="min-w-0 flex-1 pl-0.5">
                        <span class="text-[9px] font-black uppercase tracking-wider text-purple-700 block mb-0.5">
                            {{ $ekskul->category_label }}
                        </span>
                        <h3 class="font-black text-slate-900 text-sm leading-snug">{{ $ekskul->name }}</h3>
                    </div>
                </div>

                {{-- Squad Teammates Avatar Stack (Clickable) --}}
                <div class="bg-slate-50 hover:bg-indigo-50/50 p-2.5 rounded-2xl border border-slate-200/80 space-y-1.5 transition cursor-pointer active:scale-98" @click="activeModal = 'squad-{{ $ekskul->id }}'">
                    <div class="flex items-center justify-between text-[10px]">
                        <span class="font-black text-slate-700 flex items-center gap-1">
                            <i class="fa-solid fa-users text-indigo-600"></i> Rekan Squad
                        </span>
                        <span class="font-extrabold text-indigo-700 bg-indigo-100 px-1.5 py-0.2 rounded-md flex items-center gap-0.5">
                            <span>{{ $activeSquad->count() }} Anggota</span>
                            <i class="fa-solid fa-arrow-right text-[7px]"></i>
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center -space-x-2 py-0.5">
                            <img class="inline-block h-6 w-6 rounded-full ring-1 ring-white object-cover shadow-2xs" src="{{ $student->photo_url }}" title="Kamu" style="width: 24px; height: 24px; min-width: 24px; max-width: 24px;">
                            @foreach($otherSquad->take(3) as $sm)
                                @if($sm->student)
                                <img class="inline-block h-6 w-6 rounded-full ring-1 ring-white object-cover shadow-2xs" src="{{ $sm->student->photo_url }}" title="{{ $sm->student->full_name }}" style="width: 24px; height: 24px; min-width: 24px; max-width: 24px;">
                                @endif
                            @endforeach
                            @if($otherSquad->count() > 3)
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-[8px] font-black text-white ring-1 ring-white" style="width: 24px; height: 24px; min-width: 24px; max-width: 24px;">
                                +{{ $otherSquad->count() - 3 }}
                            </span>
                            @endif
                        </div>
                        <p class="text-[9px] text-slate-500 font-bold truncate">
                            @if($otherSquad->count() > 0)
                                Bersama {{ $otherSquad->first()->student->full_name ?? 'rekan' }} & lainnya
                            @else
                                Squad siap berlatih!
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Schedule & Advisor Info --}}
                <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-200/80 space-y-1 text-[11px] text-slate-700">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-bold">📅 Jadwal:</span>
                        <span class="font-black text-slate-900">{{ $ekskul->schedule_day_time ?: 'Fleksibel' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-bold">📍 Lokasi:</span>
                        <span class="font-black text-slate-900">{{ $ekskul->location ?: 'Kampus Pembda' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-bold">👨‍🏫 Pembina:</span>
                        <span class="font-black text-slate-900 truncate max-w-[140px]">{{ $ekskul->manager_name ?: ($ekskul->advisor_name ?: ($ekskul->advisor->full_name ?? 'PKS Kesiswaan')) }}</span>
                    </div>
                </div>

                {{-- Card Actions --}}
                <div class="pt-1 flex flex-wrap items-center justify-between gap-1.5 border-t border-slate-100">
                    <span class="text-[11px] font-black text-indigo-700 flex items-center gap-1">
                        <i class="fa-solid fa-star text-amber-500"></i> +{{ $membership->points_awarded }} Poin
                    </span>
                    <div class="flex items-center gap-1.5">
                        <button type="button" @click="activeModal = 'ecard-{{ $membership->id }}'" class="px-2.5 py-1.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 text-white rounded-xl text-xs font-black shadow-xs flex items-center gap-1 active:scale-95">
                            <i class="fa-solid fa-id-card"></i>
                            <span>E-Card</span>
                        </button>
                        <button type="button" @click="activeModal = 'squad-{{ $ekskul->id }}'" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-black transition flex items-center gap-1 active:scale-95">
                            <i class="fa-solid fa-sitemap text-indigo-600"></i>
                            <span>Struktur</span>
                        </button>
                        <a href="{{ route('siswa.ekskul.space', $ekskul->id) }}" class="px-3 py-1.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 text-white rounded-xl text-xs font-black shadow-xs active:scale-95 transition flex items-center gap-1" title="Buka Squad Lounge Pembda Space">
                            <i class="fa-solid fa-comments"></i>
                            <span>Space</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="p-8 bg-white rounded-3xl border-2 border-dashed border-slate-300 text-center space-y-2">
            <p class="text-3xl">🌱</p>
            <p class="font-black text-slate-900 text-xs">Belum Ada Ekskul yang Diikuti</p>
            <p class="text-[11px] text-slate-600 font-medium">Buka tab <b>'Jelajahi'</b> di atas untuk mendaftar ekskul favoritmu!</p>
        </div>
        @endforelse
    </div>

    {{-- TAB 2: JELAJAHI KATALOG --}}
    <div x-show="activeTab === 'explore'" class="space-y-3" x-transition.duration.200ms style="display: none;">
        @foreach($availableEkskuls as $ekskul)
        @php $isJoined = in_array($ekskul->id, $joinedEkskulIds); @endphp
        <div class="bg-white p-4.5 rounded-3xl border-2 {{ $ekskul->isFoundationLevel() ? 'border-amber-300 ring-2 ring-amber-50' : 'border-slate-200' }} shadow-xs space-y-3">
            <div class="flex items-start gap-3">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-2xl shadow-2xs flex-shrink-0">
                    {{ $ekskul->display_icon }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-1 mb-1.5">
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-slate-100 text-slate-800">
                            {{ $ekskul->category_label }}
                        </span>
                        @if($ekskul->isFoundationLevel())
                        <span class="px-2 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300">
                            🏛️ Lintas Yayasan
                        </span>
                        @endif
                    </div>
                    <h3 class="font-black text-slate-900 text-xs leading-snug">{{ $ekskul->name }}</h3>
                </div>
            </div>

            <p class="text-xs text-slate-600 font-medium line-clamp-2 leading-relaxed">
                {{ $ekskul->description ?: 'Unit kegiatan pembinaan bakat dan karakter positif siswa.' }}
            </p>

            <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200/80 space-y-1 text-[11px] text-slate-700">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-bold">📅 Jadwal:</span>
                    <span class="font-black text-slate-900">{{ $ekskul->schedule_day_time ?: 'Fleksibel' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-bold">📍 Lokasi:</span>
                    <span class="font-black text-slate-900">{{ $ekskul->location ?: 'Kampus Pembda' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-bold">👥 Anggota:</span>
                    <span class="font-black text-indigo-700">{{ $ekskul->active_members_count }} Siswa Aktif</span>
                </div>
            </div>

            <div class="pt-1 border-t border-slate-100 space-y-2">
                <div class="flex items-center justify-between">
                    <button type="button" @click="activeModal = 'squad-{{ $ekskul->id }}'" class="text-xs font-black text-indigo-700 hover:text-indigo-900 flex items-center gap-1 active:scale-95" title="Lihat Struktur & Roster">
                        <i class="fa-solid fa-sitemap text-indigo-500"></i>
                        <span>Lihat Struktur & Roster</span> &rarr;
                    </button>
                </div>

                @if($isJoined)
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl text-xs font-black flex items-center gap-1.5">
                        <i class="fa-solid fa-check-circle"></i> Sudah Terdaftar
                    </span>
                    <a href="{{ route('siswa.ekskul.space', $ekskul->id) }}" class="text-xs font-black text-purple-700 hover:text-purple-900 flex items-center gap-1" title="Buka Squad Lounge Pembda Space">
                        <span>Buka Space</span> &rarr;
                    </a>
                </div>
                @else
                <form action="{{ route('mobile.ekskul.claim', $ekskul) }}" method="POST" class="space-y-2">
                    @csrf
                    @if(!empty($ekskul->section_list))
                    <div>
                        <label class="block text-[10px] font-black text-slate-800 mb-1">Pilih Section / Alat Musik *</label>
                        <select name="section" required class="w-full text-xs font-bold rounded-xl border-2 border-slate-300 py-2 px-2.5 bg-white text-slate-900 focus:border-indigo-600">
                            <option value="">-- Pilih Alat / Section Musik --</option>
                            @foreach($ekskul->section_list as $secKey => $secVal)
                            <option value="{{ is_numeric($secKey) ? $secVal : $secKey }}">{{ $secVal }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black transition shadow-xs active:scale-95 flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-hand-sparkles"></i>
                        <span>Gabung / Klaim Ekskul (+15 Poin)</span>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- MODAL DETAIL SQUAD ROSTER & STRUKTUR ORGANISASI (MOBILE) --}}
    @php
        $modalEkskuls = $availableEkskuls->merge($myMemberships->pluck('extracurricular'))->unique('id');
    @endphp
    @foreach($modalEkskuls as $modalEkskul)
    @php 
        $squadList = $modalEkskul->activeMembers ?? collect();
    @endphp
    <div x-show="activeModal === 'squad-{{ $modalEkskul->id }}'" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         role="dialog" 
         aria-modal="true"
         @keydown.escape.window="activeModal = null">
        
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
             x-show="activeModal === 'squad-{{ $modalEkskul->id }}'"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="activeModal = null"></div>

        {{-- Modal Panel --}}
        <div class="min-h-full flex items-end sm:items-center justify-center p-0 sm:p-4 text-center">
            <div class="relative w-full max-w-lg bg-white rounded-t-3xl sm:rounded-3xl text-left shadow-2xl overflow-hidden border-t-2 sm:border-2 border-indigo-200 transform transition-all flex flex-col max-h-[85vh]"
                 x-show="activeModal === 'squad-{{ $modalEkskul->id }}'"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-6"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-6"
                 @click.away="activeModal = null">

                {{-- Header --}}
                <div class="bg-gradient-to-r from-indigo-700 via-purple-700 to-pink-600 text-white p-4.5 flex items-start justify-between gap-3 shrink-0">
                    <div class="flex items-start gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-white/20 backdrop-blur-xs border border-white/30 text-white flex items-center justify-center text-2xl shadow-2xs shrink-0" style="width: 44px; height: 44px; min-width: 44px; max-width: 44px;">
                            {{ $modalEkskul->display_icon }}
                        </div>
                        <div class="min-w-0">
                            <span class="text-[8px] font-black uppercase tracking-wider bg-white/20 px-2 py-0.5 rounded-full text-white inline-block mb-1">
                                {{ $modalEkskul->category_label }}
                            </span>
                            <h3 class="text-base font-black text-white leading-tight">
                                {{ $modalEkskul->name }}
                            </h3>
                            <p class="text-[10px] text-indigo-100 font-medium mt-0.5">
                                👥 {{ $squadList->count() }} Anggota &bull; 📍 {{ $modalEkskul->location ?: 'Kampus Pembda' }}
                            </p>
                        </div>
                    </div>

                    <button type="button" @click="activeModal = null" class="w-8 h-8 rounded-xl bg-white/20 text-white flex items-center justify-center text-xs active:scale-95 shrink-0">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                {{-- Content --}}
                <div class="p-4 space-y-4 overflow-y-auto flex-1">
                    
                    {{-- Leadership --}}
                    <div class="space-y-2">
                        <h4 class="text-[11px] font-black uppercase tracking-wider text-purple-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-crown text-amber-500"></i> Dewan Pembina & Ketua
                        </h4>
                        
                        <div class="grid grid-cols-1 gap-2">
                            {{-- Pembina --}}
                            <div class="bg-indigo-50/70 p-3 rounded-2xl border border-indigo-100 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-lg shrink-0" style="width: 40px; height: 40px; min-width: 40px; max-width: 40px;">
                                    👨‍🏫
                                </div>
                                <div class="min-w-0 flex-1 pl-0.5">
                                    <span class="text-[8px] font-black uppercase tracking-wider text-indigo-700 block">Pembina / Manager</span>
                                    <p class="font-black text-slate-900 text-xs truncate">
                                        {{ $modalEkskul->manager_name ?: ($modalEkskul->advisor_name ?: ($modalEkskul->advisor->full_name ?? 'PKS Kesiswaan')) }}
                                    </p>
                                </div>
                            </div>

                            {{-- Ketua --}}
                            <div class="bg-amber-50/70 p-3 rounded-2xl border border-amber-200 flex items-center gap-3">
                                <div class="relative shrink-0 w-10 h-10" style="width: 40px; height: 40px; min-width: 40px; max-width: 40px;">
                                    @if($modalEkskul->leader && $modalEkskul->leader->photo_url)
                                        <img src="{{ $modalEkskul->leader->photo_url }}" alt="{{ $modalEkskul->leader->full_name }}" class="w-10 h-10 rounded-xl object-cover border-2 border-amber-400 bg-white" style="width: 40px; height: 40px; min-width: 40px; max-width: 40px;">
                                    @else
                                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg" style="width: 40px; height: 40px; min-width: 40px; max-width: 40px;">
                                            👑
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1 pl-0.5">
                                    <span class="text-[8px] font-black uppercase tracking-wider text-amber-800 block">Ketua / Koordinator</span>
                                    <p class="font-black text-slate-900 text-xs truncate">
                                        {{ $modalEkskul->leader->full_name ?? 'Akan Ditunjuk Pembina' }}
                                    </p>
                                    <p class="text-[9px] text-slate-500 font-semibold">
                                        {{ $modalEkskul->leader->classroom->class_name ?? ($modalEkskul->leader ? 'Siswa Aktif' : 'Struktur Rintisan') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Sections --}}
                    @if(!empty($modalEkskul->section_list) && count($modalEkskul->section_list) > 0)
                    <div class="space-y-2">
                        <h4 class="text-[11px] font-black uppercase tracking-wider text-purple-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-layer-group text-purple-600"></i> Divisi & Section
                        </h4>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($modalEkskul->section_list as $secKey => $secLabel)
                            @php 
                                $secName = is_numeric($secKey) ? $secLabel : $secKey;
                                $countInSec = $squadList->where('section', $secName)->count();
                            @endphp
                            <div class="p-2.5 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-between gap-1.5">
                                <span class="text-xs font-black text-slate-800 truncate">{{ $secLabel }}</span>
                                <span class="px-1.5 py-0.2 rounded-md text-[9px] font-black bg-purple-100 text-purple-900 shrink-0">
                                    {{ $countInSec }} Siswa
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Roster --}}
                    <div class="space-y-2">
                        <h4 class="text-[11px] font-black uppercase tracking-wider text-purple-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-users text-indigo-600"></i> Daftar Anggota ({{ $squadList->count() }})
                        </h4>

                        @if($squadList->isNotEmpty())
                        <div class="space-y-2 max-h-56 overflow-y-auto pr-0.5">
                            @foreach($squadList as $squadItem)
                            @php 
                                $isMe = ($squadItem->student_id === $student->id);
                            @endphp
                            <div class="p-2.5 rounded-2xl border {{ $isMe ? 'bg-indigo-50/90 border-indigo-300 ring-2 ring-indigo-200' : 'bg-white border-slate-200' }} flex items-center gap-3">
                                <div class="relative shrink-0 w-9 h-9" style="width: 36px; height: 36px; min-width: 36px; max-width: 36px;">
                                    <img src="{{ $squadItem->student->photo_url ?? asset('images/default-avatar.png') }}" alt="{{ $squadItem->student->full_name ?? 'Siswa' }}" class="w-9 h-9 rounded-xl object-cover border {{ $isMe ? 'border-indigo-500' : 'border-slate-300' }} bg-slate-100" style="width: 36px; height: 36px; min-width: 36px; max-width: 36px;">
                                    @if($isMe)
                                    <span class="absolute -bottom-1 -right-1 px-1 py-0.2 rounded-md bg-indigo-600 text-white font-black text-[6px]">Kamu</span>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1 pl-0.5">
                                    <div class="flex items-center gap-1 mb-0.5">
                                        <span class="px-1.5 py-0.2 rounded-full text-[7px] font-black text-white bg-gradient-to-r {{ $squadItem->role_badge_color }}">
                                            {{ $squadItem->role_label }}
                                        </span>
                                        @if($squadItem->section)
                                        <span class="px-1 py-0.2 rounded-full text-[7px] font-black bg-purple-100 text-purple-900">
                                            🎺 {{ $squadItem->section }}
                                        </span>
                                        @endif
                                    </div>
                                    <p class="font-black text-slate-900 text-xs truncate">
                                        {{ $squadItem->student->full_name ?? 'Siswa' }}
                                    </p>
                                    <p class="text-[9px] text-slate-500 font-semibold truncate">
                                        {{ $squadItem->student->school->short_name ?? ($squadItem->student->school->name ?? 'Pembda') }} &bull; {{ $squadItem->student->classroom->class_name ?? 'Siswa' }}
                                    </p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-xs text-slate-500 italic p-3 text-center bg-slate-50 rounded-2xl">Belum ada anggota.</p>
                        @endif
                    </div>

                </div>

                {{-- Footer --}}
                <div class="bg-slate-50 p-3.5 border-t border-slate-200 flex items-center justify-between gap-2 shrink-0">
                    <a href="{{ route('siswa.ekskul.space', $modalEkskul->id) }}" class="px-3 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl text-xs font-black flex items-center gap-1 shadow-xs" title="Buka Squad Lounge Pembda Space">
                        <i class="fa-solid fa-comments"></i>
                        <span>Buka Space</span>
                    </a>
                    <button type="button" @click="activeModal = null" class="ml-auto px-4 py-2 bg-slate-200 text-slate-800 rounded-xl text-xs font-black active:scale-95">
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endforeach

    {{-- MODAL PREVIEW & DOWNLOAD VIP E-CARD DIGITAL (MOBILE) --}}
    @foreach($myMemberships as $membership)
    @php
        $ekskul = $membership->extracurricular;
        $isLeader = in_array($membership->role, ['ketua', 'wakil_ketua', 'sekretaris', 'bendahara']);
    @endphp
    <div x-show="activeModal === 'ecard-{{ $membership->id }}'" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         role="dialog" 
         aria-modal="true"
         @keydown.escape.window="activeModal = null">
        
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" 
             x-show="activeModal === 'ecard-{{ $membership->id }}'"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="activeModal = null"></div>

        {{-- Modal Panel --}}
        <div class="min-h-full flex items-end sm:items-center justify-center p-0 sm:p-4 text-center">
            <div class="relative w-full max-w-lg bg-white rounded-t-3xl sm:rounded-3xl text-left shadow-2xl overflow-hidden border-t-2 sm:border-2 border-indigo-200 transform transition-all flex flex-col"
                 x-show="activeModal === 'ecard-{{ $membership->id }}'"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-6"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-6"
                 @click.away="activeModal = null">

                {{-- Header --}}
                <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-purple-950 text-white p-4.5 flex items-center justify-between border-b border-indigo-900/50">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-2xl bg-amber-400 text-slate-950 flex items-center justify-center text-lg shadow-sm">
                            💳
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-white leading-tight">
                                Digital Member E-Card
                            </h3>
                            <p class="text-[10px] text-indigo-200 font-medium truncate max-w-[200px]">
                                {{ $ekskul->name }}
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="activeModal = null" class="w-8 h-8 rounded-xl bg-white/20 text-white flex items-center justify-center text-xs active:scale-95">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                {{-- Canvas Area (Horizontal Scroll on small screens) --}}
                <div class="p-4 bg-slate-100/90 flex flex-col items-center justify-center overflow-x-auto">
                    
                    {{-- THE OFFICIAL E-CARD COMPONENT --}}
                    <div id="mobile-ecard-card-{{ $membership->id }}" 
                         class="relative w-[340px] max-w-[340px] rounded-2xl p-4 shadow-2xl overflow-hidden select-none border-2 flex flex-col justify-between"
                         style="background: linear-gradient(135deg, #090d16 0%, #1e153a 50%, #2e0854 100%); border-color: rgba(251, 191, 36, 0.5); min-height: 220px; color: #ffffff; font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;">
                        
                        {{-- Background Hologram --}}
                        <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full pointer-events-none" style="background: rgba(168, 85, 247, 0.15); filter: blur(24px);"></div>
                        <div class="absolute right-2 bottom-1 text-7xl pointer-events-none font-black" style="opacity: 0.06; color: #ffffff;">
                            {{ $ekskul->display_icon }}
                        </div>

                        {{-- Card Header --}}
                        <div class="relative z-10 flex items-start justify-between gap-2 pb-2" style="border-bottom: 1px solid rgba(255, 255, 255, 0.15);">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center p-0.5" style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2);">
                                    <img src="{{ asset('images/logo_yayasan.png') }}" crossorigin="anonymous" alt="Logo Yayasan" class="w-6 h-6 object-contain" onerror="this.src='{{ asset('images/logo-pembda.png') }}'">
                                </div>
                                <div>
                                    <h4 class="text-[9px] font-black uppercase tracking-wider leading-tight" style="color: #fbbf24; margin: 0;">
                                        YAYASAN PEMBDA
                                    </h4>
                                    <p class="text-[7px] font-bold uppercase tracking-wide" style="color: #e0e7ff; margin: 0;">
                                        SQUAD PASS &bull; EKSTRAKURIKULER
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-block px-1.5 py-0.2 rounded-full text-[7px] font-mono font-black" style="background-color: #fbbf24; color: #020617;">
                                    #EKS-{{ str_pad($ekskul->id, 2, '0', STR_PAD_LEFT) }}-{{ str_pad($membership->id, 4, '0', STR_PAD_LEFT) }}
                                </span>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="relative z-10 my-2.5 flex items-center gap-3">
                            <div class="relative shrink-0">
                                <img src="{{ $student->photo_url }}" crossorigin="anonymous" alt="{{ $student->full_name }}" class="w-14 h-14 rounded-xl object-cover" style="width: 56px; height: 56px; min-width: 56px; max-width: 56px; border: 2px solid {{ $isLeader ? '#fbbf24' : '#818cf8' }}; background: #ffffff;">
                                <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full flex items-center justify-center text-[6px]" style="background-color: #10b981; color: #ffffff; border: 1px solid #0f172a;">
                                    <i class="fa-solid fa-check"></i>
                                </span>
                            </div>

                            <div class="min-w-0 flex-1 space-y-0.5">
                                <div class="flex items-center gap-1">
                                    <span class="px-1.5 py-0.2 rounded text-[7px] font-black uppercase tracking-wider" style="color: #ffffff; background: {{ $isLeader ? 'linear-gradient(to right, #d97706, #b45309)' : 'linear-gradient(to right, #4f46e5, #7c3aed)' }};">
                                        {{ $isLeader ? '👑 ' : '' }}{{ $membership->role_label }}
                                    </span>
                                    @if($membership->section)
                                    <span class="px-1 py-0.2 rounded text-[7px] font-black uppercase tracking-wider" style="background: rgba(233, 213, 255, 0.9); color: #3b0764;">
                                        🎺 {{ $membership->section }}
                                    </span>
                                    @endif
                                </div>

                                <h3 class="font-black text-xs leading-tight truncate" style="color: #ffffff; margin: 1px 0;">
                                    {{ $student->full_name }}
                                </h3>

                                <p class="text-[9px] font-medium truncate" style="color: #c7d2fe; margin: 0;">
                                    NISN: {{ $student->nisn ?: '-' }} &bull; {{ $student->school->short_name ?? 'Pembda' }}
                                </p>

                                <div class="pt-0.5 flex items-center gap-1">
                                    <span class="text-[10px]">{{ $ekskul->display_icon }}</span>
                                    <span class="text-[10px] font-black truncate" style="color: #fde047;">
                                        {{ $ekskul->name }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Card Footer --}}
                        <div class="relative z-10 pt-2 flex items-center justify-between gap-2 text-[7px]" style="border-top: 1px solid rgba(255, 255, 255, 0.15); color: #cbd5e1;">
                            <div class="flex items-center gap-1.5">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=50x50&data=PEMBDA-EKS-{{ $membership->id }}-{{ $student->nisn }}&color=ffffff&bgcolor=00000000" 
                                     crossorigin="anonymous"
                                     alt="QR" 
                                     class="w-6 h-6 object-contain rounded p-0.5 shrink-0" 
                                     style="width: 24px; height: 24px; min-width: 24px; max-width: 24px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);">
                                <div class="leading-tight">
                                    <p class="font-black" style="color: #ffffff; margin: 0;">VERIFIED</p>
                                    <p class="font-mono" style="color: #94a3b8; margin: 0;">perguruanpembda.com</p>
                                </div>
                            </div>

                            <div class="text-right leading-tight">
                                <p style="color: #94a3b8; margin: 0;">Pembina / Kesiswaan</p>
                                <p class="font-black truncate max-w-[90px]" style="color: #ffffff; margin: 0;">
                                    {{ $ekskul->manager_name ?: ($ekskul->advisor_name ?: ($ekskul->advisor->full_name ?? 'PKS Kesiswaan')) }}
                                </p>
                            </div>
                        </div>

                    </div>

                </div>

                {{-- Modal Action Buttons --}}
                <div class="bg-white p-4 border-t border-slate-200 flex items-center justify-between gap-3">
                    <button type="button" 
                            onclick="downloadEcardCard('mobile-ecard-card-{{ $membership->id }}', 'E-Card_{{ Str::slug($student->full_name) }}_{{ Str::slug($ekskul->name) }}.png')"
                            class="flex-1 py-3 px-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-2xl text-xs font-black transition shadow-sm active:scale-95 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-download"></i>
                        <span>Unduh Gambar (PNG)</span>
                    </button>

                    <button type="button" 
                            onclick="shareEcardCard('mobile-ecard-card-{{ $membership->id }}', 'E-Card {{ $ekskul->name }} - {{ $student->full_name }}')"
                            class="py-3 px-5 bg-emerald-50 text-emerald-800 border-2 border-emerald-300 rounded-2xl text-xs font-black transition flex items-center justify-center gap-1.5 active:scale-95">
                        <i class="fa-solid fa-share-nodes"></i>
                        <span>Share</span>
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endforeach

</div>

{{-- SCRIPT DEDIKASI GENERATE E-CARD HD --}}
<script src="{{ asset('vendor/html-to-image.js') }}"></script>
<script src="{{ asset('vendor/html2canvas.min.js') }}"></script>

<script>
function ensureRenderer() {
    return new Promise((resolve) => {
        if (typeof htmlToImage !== 'undefined' && htmlToImage.toPng) {
            return resolve({ type: 'htmlToImage', fn: htmlToImage });
        }
        if (window.htmlToImage && window.htmlToImage.toPng) {
            return resolve({ type: 'htmlToImage', fn: window.htmlToImage });
        }
        if (typeof html2canvas !== 'undefined') {
            return resolve({ type: 'html2canvas', fn: html2canvas });
        }
        const s = document.createElement('script');
        s.src = '{{ asset('vendor/html-to-image.js') }}';
        s.onload = () => {
            if (typeof htmlToImage !== 'undefined' && htmlToImage.toPng) {
                resolve({ type: 'htmlToImage', fn: htmlToImage });
            } else if (window.htmlToImage && window.htmlToImage.toPng) {
                resolve({ type: 'htmlToImage', fn: window.htmlToImage });
            } else {
                resolve(null);
            }
        };
        s.onerror = () => resolve(null);
        document.head.appendChild(s);
    });
}

async function downloadEcardCard(elementId, filename) {
    const card = document.getElementById(elementId);
    if (!card) {
        alert('Elemen kartu #' + elementId + ' tidak ditemukan.');
        return;
    }

    const btn = window.event ? window.event.currentTarget : null;
    const oldHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> <span>Merender HD...</span>';
    }

    try {
        const renderer = await ensureRenderer();
        let dataUrl;

        if (renderer && renderer.type === 'htmlToImage') {
            dataUrl = await renderer.fn.toPng(card, {
                pixelRatio: 3,
                cacheBust: true,
            });
        } else if (renderer && renderer.type === 'html2canvas') {
            const canvas = await renderer.fn(card, {
                scale: 3,
                useCORS: true,
                allowTaint: true,
                backgroundColor: null,
                logging: false,
            });
            dataUrl = canvas.toDataURL('image/png');
        } else if (typeof html2canvas !== 'undefined') {
            const canvas = await html2canvas(card, {
                scale: 3,
                useCORS: true,
                allowTaint: true,
                backgroundColor: null,
                logging: false,
            });
            dataUrl = canvas.toDataURL('image/png');
        } else {
            throw new Error('Renderer kartu belum siap.');
        }

        const downloadLink = document.createElement('a');
        downloadLink.download = filename || 'E-Card-Ekskul-Pembda.png';
        downloadLink.href = dataUrl;
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);

        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-check mr-1"></i> <span>Tersimpan!</span>';
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = oldHtml;
            }, 3000);
        }
    } catch (err) {
        console.error('Download E-Card Error:', err);
        alert('Gagal mengunduh kartu: ' + err.message);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = oldHtml;
        }
    }
}

async function shareEcardCard(elementId, title) {
    const card = document.getElementById(elementId);
    if (!card) return;

    if (navigator.share) {
        try {
            const renderer = await ensureRenderer();
            if (renderer && renderer.type === 'htmlToImage') {
                const blob = await renderer.fn.toBlob(card, { pixelRatio: 2 });
                if (blob && navigator.canShare && navigator.canShare({ files: [new File([blob], 'ecard.png', { type: 'image/png' })] })) {
                    const file = new File([blob], 'ecard-pembda.png', { type: 'image/png' });
                    await navigator.share({
                        title: title,
                        text: 'Kartu Anggota Resmi Ekskul Perguruan Pembda',
                        files: [file]
                    });
                    return;
                }
            }
            await navigator.share({
                title: title,
                text: 'Kartu Anggota Resmi Ekskul Perguruan Pembda - https://perguruanpembda.com',
                url: window.location.href
            });
        } catch (e) {
            console.log('Share error or dismissed', e);
        }
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert('Tautan halaman ekstrakurikuler telah disalin ke clipboard!');
    }
}
</script>
@endsection
