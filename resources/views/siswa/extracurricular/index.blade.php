@extends('layouts.siswa')

@section('title', 'Ekstrakurikuler & Unit Kegiatan Siswa')

@section('content')
<div class="space-y-6" x-data="{ activeModal: null, searchSquad: '' }">

    {{-- Hero Card (VIBRANT & COLORFUL RAINBOW BENTO HEADER) --}}
    <div class="bg-white rounded-3xl border-2 border-indigo-100 shadow-md relative overflow-hidden space-y-4">
        {{-- Rainbow Stripe --}}
        <div class="h-3 w-full bg-gradient-to-r from-rose-500 via-amber-500 via-emerald-500 via-cyan-500 to-purple-600"></div>

        <div class="p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
            <div class="space-y-2">
                <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-purple-700">
                    <span>🌟 Eksplorasi Minat, Bakat & Kepemimpinan</span>
                    <span>&bull;</span>
                    <span class="bg-gradient-to-r from-purple-500 to-pink-500 text-white px-3 py-0.5 rounded-full font-black shadow-2xs">+15 s/d +30 Poin Reputasi</span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-black tracking-tight text-slate-900 flex items-center gap-3">
                    <span>🎨 Ekstrakurikuler & Non-Akademik</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 font-semibold max-w-3xl leading-relaxed">
                    Kembangkan jiwa kepemimpinan, kreativitas seni, dan kebugaran jasmani. Keikutsertaan ekskul akan langsung memperkuat profil <b>DNA Akademik 360°</b> dan mengaktifkan kanal diskusi squad di <b>Pembda Space</b>!
                </p>
            </div>
            
            <div class="flex items-center gap-4 bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 text-white px-6 py-4 rounded-3xl shadow-md border border-indigo-300/30 shrink-0">
                <div class="w-12 h-12 rounded-2xl bg-white text-indigo-700 flex items-center justify-center text-2xl shadow-sm shrink-0">
                    🏆
                </div>
                <div class="pr-2">
                    <p class="text-[11px] text-indigo-100 font-black uppercase tracking-wider whitespace-nowrap mb-0.5">Keaktifan Saya</p>
                    <p class="text-xl font-black text-white whitespace-nowrap leading-tight">{{ $myMemberships->count() }} <span class="text-xs font-bold text-indigo-200 ml-0.5">Unit Diikuti</span></p>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION 1: UNIT EKSKUL YANG DI SAYA IKUTI (DIGITAL SQUAD PASS & REPUTASI) --}}
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-id-badge text-indigo-600"></i> Ekstrakurikuler yang Saya Ikuti ({{ $myMemberships->count() }})
            </h2>
            <span class="text-xs font-bold text-slate-500">
                <i class="fas fa-shield-alt text-emerald-500"></i> Status Anggota Resmi
            </span>
        </div>

        @if($myMemberships->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($myMemberships as $membership)
            @php 
                $ekskul = $membership->extracurricular;
                $activeSquad = $ekskul->activeMembers ?? collect();
                $otherSquad = $activeSquad->where('student_id', '!=', $student->id);
                $isLeader = in_array($membership->role, ['ketua', 'wakil_ketua', 'sekretaris', 'bendahara']);
            @endphp
            <div class="relative bg-white rounded-3xl border-2 {{ $isLeader ? 'border-amber-300 ring-4 ring-amber-100/50 shadow-md' : 'border-indigo-200/80 shadow-md' }} hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col justify-between group">
                
                {{-- Decorative Watermark Background Icon --}}
                <div class="absolute -right-6 -bottom-8 text-8xl opacity-[0.06] group-hover:opacity-[0.12] transition-opacity duration-300 select-none pointer-events-none">
                    {{ $ekskul->display_icon }}
                </div>

                {{-- VIP Squad Pass Ribbon --}}
                <div class="px-5 py-3 bg-gradient-to-r {{ $isLeader ? 'from-amber-600 via-orange-600 to-purple-700' : 'from-indigo-600 via-purple-600 to-pink-600' }} text-white flex items-center justify-between gap-2 shadow-xs">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-white/20 backdrop-blur-xs border border-white/30 text-white">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            OFFICIAL SQUAD PASS
                        </span>
                        <span class="text-[10px] font-bold text-indigo-100/90 font-mono hidden sm:inline">
                            #EKS-{{ str_pad($ekskul->id, 2, '0', STR_PAD_LEFT) }}-{{ str_pad($membership->id, 4, '0', STR_PAD_LEFT) }}
                        </span>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        @if($ekskul->isFoundationLevel())
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-amber-400 text-slate-950 shadow-2xs">
                            🏛️ Yayasan
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="p-5 sm:p-6 space-y-4 relative z-10 flex-1 flex flex-col justify-between">
                    
                    <div class="space-y-4">
                        {{-- Student Member Profile Header (Pride Factor) --}}
                        <div class="flex items-center gap-4 bg-gradient-to-r from-slate-50 via-indigo-50/40 to-purple-50/30 p-4 rounded-2xl border border-indigo-100/80">
                            <div class="relative shrink-0 w-12 h-12 mr-2">
                                <img src="{{ $student->photo_url }}" alt="{{ $student->full_name }}" class="w-12 h-12 rounded-2xl object-cover border-2 {{ $isLeader ? 'border-amber-400 shadow-amber-200' : 'border-indigo-400 shadow-indigo-100' }} shadow-md bg-white" style="width: 48px; height: 48px; min-width: 48px; max-width: 48px;">
                                <span class="absolute bottom-0 right-0 w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[8px] shadow-sm border border-white" title="Anggota Terverifikasi">
                                    <i class="fas fa-check"></i>
                                </span>
                            </div>
                            <div class="min-w-0 flex-1 pl-1">
                                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black text-white bg-gradient-to-r {{ $membership->role_badge_color }} shadow-2xs">
                                        {{ $isLeader ? '👑 ' : '' }}{{ $membership->role_label }}
                                    </span>
                                    @if($membership->section)
                                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black bg-gradient-to-r from-purple-100 to-pink-100 text-purple-950 border border-purple-300">
                                        🎺 {{ $membership->section }}
                                    </span>
                                    @endif
                                </div>
                                <h4 class="font-black text-slate-900 text-sm leading-tight truncate">{{ $student->full_name }}</h4>
                                <p class="text-[11px] text-slate-500 font-bold mt-1">
                                    NISN: {{ $student->nisn ?: '-' }} &bull; {{ $student->classroom->class_name ?? ($student->school->short_name ?? 'Pembda') }}
                                </p>
                            </div>
                        </div>

                        {{-- Ekskul Title & Category --}}
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 via-rose-500 to-purple-600 text-white flex items-center justify-center text-2xl shadow-sm shrink-0 mr-2 group-hover:scale-105 transition" style="width: 48px; height: 48px; min-width: 48px; max-width: 48px;">
                                {{ $ekskul->display_icon }}
                            </div>
                            <div class="min-w-0 flex-1 pl-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-purple-700 block mb-1">
                                    {{ $ekskul->category_label }}
                                </span>
                                <h3 class="font-black text-slate-900 text-base leading-snug group-hover:text-indigo-600 transition">
                                    {{ $ekskul->name }}
                                </h3>
                            </div>
                        </div>

                        {{-- Squad Teammates (Avatar Stack & Social Proof - CLICKABLE FOR MODAL) --}}
                        <div class="bg-indigo-50/50 hover:bg-indigo-50/80 rounded-2xl p-3.5 border border-indigo-100/70 space-y-2 transition cursor-pointer group/squad" 
                             @click="activeModal = 'squad-{{ $ekskul->id }}'"
                             title="Klik untuk melihat struktur kepengurusan & roster squad lengkap">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-black text-slate-800 flex items-center gap-1.5">
                                    <i class="fas fa-users text-indigo-600"></i> Rekan Squad
                                </span>
                                <span class="text-[10px] font-black px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-900 group-hover/squad:bg-indigo-600 group-hover/squad:text-white transition flex items-center gap-1">
                                    <span>{{ $activeSquad->count() }} Anggota</span>
                                    <i class="fas fa-arrow-right text-[8px]"></i>
                                </span>
                            </div>
                            
                            <div class="flex items-center justify-between gap-2 pt-1">
                                <div class="flex items-center -space-x-2 overflow-hidden py-1">
                                    {{-- Current Student Avatar --}}
                                    <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white object-cover shadow-2xs" src="{{ $student->photo_url }}" alt="{{ $student->full_name }}" title="Kamu ({{ $student->full_name }})" style="width: 32px; height: 32px; min-width: 32px; max-width: 32px;">
                                    
                                    {{-- Other Teammates --}}
                                    @foreach($otherSquad->take(4) as $squadMember)
                                        @if($squadMember->student)
                                        <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white object-cover shadow-2xs" src="{{ $squadMember->student->photo_url }}" alt="{{ $squadMember->student->full_name }}" title="{{ $squadMember->student->full_name }} ({{ $squadMember->role_label }})" style="width: 32px; height: 32px; min-width: 32px; max-width: 32px;">
                                        @endif
                                    @endforeach

                                    @if($otherSquad->count() > 4)
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 text-[10px] font-black text-white ring-2 ring-white shadow-2xs" style="width: 32px; height: 32px; min-width: 32px; max-width: 32px;">
                                        +{{ $otherSquad->count() - 4 }}
                                    </span>
                                    @endif
                                </div>
                                <p class="text-[10px] text-slate-600 font-bold text-right leading-tight group-hover/squad:text-indigo-700 transition">
                                    @if($otherSquad->count() > 0)
                                        Bersama <b>{{ $otherSquad->first()->student->full_name ?? 'teman' }}</b> & {{ $otherSquad->count() }} lainnya
                                    @else
                                        Squad baru dibentuk & siap berkembang!
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Schedule & Advisor Info --}}
                        <div class="space-y-1.5 text-xs bg-slate-50 p-3.5 rounded-2xl border border-slate-200/70 text-slate-700">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-500 flex items-center gap-1.5"><i class="far fa-calendar-alt text-slate-400"></i> Jadwal:</span>
                                <span class="font-black text-slate-900">{{ $ekskul->schedule_day_time ?: 'Sesuai Arahan Pembina' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-500 flex items-center gap-1.5"><i class="fas fa-map-marker-alt text-slate-400"></i> Lokasi:</span>
                                <span class="font-black text-slate-900">{{ $ekskul->location ?: 'Kampus Pembda' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-500 flex items-center gap-1.5"><i class="fas fa-user-tie text-slate-400"></i> Pembina:</span>
                                <span class="font-black text-slate-900 truncate max-w-[160px]">{{ $ekskul->manager_name ?: ($ekskul->advisor_name ?: ($ekskul->advisor->full_name ?? 'PKS Kesiswaan')) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Card Footer --}}
                    <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 mt-3">
                        <span class="text-xs font-black text-indigo-700 flex items-center gap-1.5 bg-amber-50 border border-amber-200/80 px-2.5 py-1 rounded-xl">
                            <i class="fas fa-award text-amber-500"></i> +{{ $membership->points_awarded }} Poin
                        </span>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="activeModal = 'ecard-{{ $membership->id }}'" class="px-3 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white rounded-xl text-xs font-black transition flex items-center gap-1.5 shadow-xs active:scale-95 cursor-pointer" title="Download E-Card Anggota Digital">
                                <i class="fas fa-id-card"></i>
                                <span>E-Card</span>
                            </button>

                            <button type="button" @click="activeModal = 'squad-{{ $ekskul->id }}'" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-black transition flex items-center gap-1.5 active:scale-95 cursor-pointer" title="Lihat Struktur & Roster Lengkap">
                                <i class="fas fa-sitemap text-indigo-600"></i>
                                <span>Struktur</span>
                            </button>

                            @if($ekskul->forum_group_id)
                            <a href="{{ route('forum.index', ['group' => $ekskul->forum_group_id]) }}" class="px-3.5 py-2 bg-gradient-to-r from-purple-600 via-indigo-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl text-xs font-black transition flex items-center gap-1.5 shadow-sm active:scale-95">
                                <i class="fas fa-comments"></i>
                                <span>Space</span>
                            </a>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="p-10 bg-white rounded-3xl border-2 border-dashed border-slate-300 text-center space-y-2">
            <p class="text-4xl">🌱</p>
            <p class="font-black text-slate-900 text-sm">Kamu belum bergabung di ekstrakurikuler manapun.</p>
            <p class="text-xs text-slate-600 font-semibold">Pilih unit kegiatan favoritmu di bawah dan klik <b>'Gabung / Klaim Ekskul'</b> untuk mulai mengumpulkan poin reputasi!</p>
        </div>
        @endif
    </div>

    {{-- SECTION 2: KATALOG EKSKUL TERSEDIA (VIBRANT) --}}
    <div class="space-y-3 pt-4">
        <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
            <i class="fas fa-compass text-purple-600"></i> Katalog Ekstrakurikuler Sekolah & Yayasan ({{ $availableEkskuls->count() }} Pilihan)
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($availableEkskuls as $ekskul)
            @php $isJoined = in_array($ekskul->id, $joinedEkskulIds); @endphp
            <div class="bg-white rounded-3xl border-2 {{ $ekskul->isFoundationLevel() ? 'border-amber-400 ring-4 ring-amber-50' : 'border-indigo-100 hover:border-indigo-400' }} shadow-sm p-6 flex flex-col justify-between hover:shadow-xl transition duration-200 group">
                <div class="space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 via-rose-500 to-purple-600 text-white flex items-center justify-center text-3xl group-hover:scale-105 transition flex-shrink-0 shadow-sm">
                            {{ $ekskul->display_icon }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-1.5 mb-2.5">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-2xs">
                                    {{ $ekskul->category_label }}
                                </span>
                                @if($ekskul->isFoundationLevel())
                                <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-wider bg-gradient-to-r from-amber-400 to-orange-500 text-white shadow-2xs">
                                    🏛️ Lintas Yayasan
                                </span>
                                @endif
                            </div>
                            <h3 class="font-black text-slate-900 text-base leading-snug group-hover:text-indigo-600 transition">
                                {{ $ekskul->name }}
                            </h3>
                        </div>
                    </div>

                    <p class="text-xs text-slate-600 font-semibold line-clamp-2 leading-relaxed">
                        {{ $ekskul->description ?: 'Kegiatan pembinaan bakat dan karakter positif siswa.' }}
                    </p>

                    <div class="space-y-2 text-xs bg-gradient-to-br from-slate-50 to-indigo-50/40 p-4 rounded-2xl border border-indigo-100 text-slate-700">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-500">📅 Waktu:</span>
                            <span class="font-black text-slate-900">{{ $ekskul->schedule_day_time ?: 'Fleksibel' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-500">📍 Tempat:</span>
                            <span class="font-black text-slate-900">{{ $ekskul->location ?: 'Kampus Pembda' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-500">👨‍🏫 Pembina:</span>
                            <span class="font-black text-slate-900 truncate max-w-[150px]">{{ $ekskul->manager_name ?: ($ekskul->advisor_name ?: ($ekskul->advisor->full_name ?? 'PKS Kesiswaan')) }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-1 border-t border-indigo-100/60">
                            <span class="font-bold text-slate-500">👥 Anggota:</span>
                            <div class="flex items-center gap-1.5">
                                <div class="flex items-center -space-x-1.5">
                                    @foreach($ekskul->activeMembers->take(3) as $m)
                                        @if($m->student)
                                        <img class="inline-block h-5 w-5 rounded-full ring-1 ring-white object-cover" src="{{ $m->student->photo_url }}" title="{{ $m->student->full_name }}" style="width: 20px; height: 20px; min-width: 20px; max-width: 20px;">
                                        @endif
                                    @endforeach
                                </div>
                                <span class="font-black text-indigo-700">{{ $ekskul->active_members_count }} Siswa Aktif</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3.5 border-t border-slate-100 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <button type="button" @click="activeModal = 'squad-{{ $ekskul->id }}'" class="text-xs font-black text-indigo-700 hover:text-indigo-900 flex items-center gap-1 cursor-pointer" title="Lihat Struktur & Roster">
                            <i class="fas fa-sitemap text-indigo-500"></i>
                            <span>Lihat Struktur & Roster</span> &rarr;
                        </button>
                    </div>

                    @if($isJoined)
                    <div class="flex items-center justify-between">
                        <span class="px-3.5 py-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl text-xs font-black flex items-center gap-1.5">
                            <i class="fas fa-check-circle"></i> Sudah Terdaftar
                        </span>
                        @if($ekskul->forum_group_id)
                        <a href="{{ route('forum.index', ['group' => $ekskul->forum_group_id]) }}" class="text-xs font-black text-purple-700 hover:text-purple-900 flex items-center gap-1">
                            <span>Buka Space</span> &rarr;
                        </a>
                        @endif
                    </div>
                    @else
                    <form action="{{ route('siswa.ekskul.claim', $ekskul) }}" method="POST" class="space-y-2.5">
                        @csrf
                        @if(!empty($ekskul->section_list))
                        <div>
                            <label class="block text-[10px] font-black text-slate-800 mb-1">Pilih Section / Alat Musik *</label>
                            <select name="section" required class="w-full text-xs font-bold rounded-xl border-2 border-slate-300 py-2.5 px-3 bg-white text-slate-900 focus:border-indigo-600">
                                <option value="">-- Pilih Alat / Section Musik --</option>
                                @foreach($ekskul->section_list as $secKey => $secVal)
                                <option value="{{ is_numeric($secKey) ? $secVal : $secKey }}">{{ $secVal }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <button type="submit" class="w-full py-3 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-700 hover:to-pink-700 text-white rounded-2xl text-xs font-black transition shadow-md active:scale-95 flex items-center justify-center gap-2">
                            <i class="fas fa-hand-sparkles text-amber-300"></i>
                            <span>Gabung / Klaim Ekskul (+15 Poin)</span>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- MODAL DETAIL SQUAD ROSTER & STRUKTUR ORGANISASI UNTUK SETIAP EKSKUL --}}
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
        
        {{-- Backdrop with Blur --}}
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
        <div class="min-h-full flex items-center justify-center p-3 sm:p-6 text-center">
            <div class="relative w-full max-w-3xl bg-white rounded-3xl text-left shadow-2xl overflow-hidden border-2 border-indigo-200 transform transition-all flex flex-col max-h-[90vh]"
                 x-show="activeModal === 'squad-{{ $modalEkskul->id }}'"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                 @click.away="activeModal = null">

                {{-- Modal Header Ribbon --}}
                <div class="bg-gradient-to-r from-indigo-700 via-purple-700 to-pink-600 text-white p-5 sm:p-6 flex items-start justify-between gap-4 shrink-0">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md border border-white/30 text-white flex items-center justify-center text-3xl shadow-sm shrink-0" style="width: 56px; height: 56px; min-width: 56px; max-width: 56px;">
                            {{ $modalEkskul->display_icon }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-1.5 mb-1">
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-white/20 text-white border border-white/30">
                                    {{ $modalEkskul->category_label }}
                                </span>
                                @if($modalEkskul->isFoundationLevel())
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-amber-400 text-slate-950 shadow-2xs">
                                    🏛️ Lintas Yayasan
                                </span>
                                @endif
                            </div>
                            <h3 class="text-xl sm:text-2xl font-black text-white leading-tight">
                                {{ $modalEkskul->name }}
                            </h3>
                            <p class="text-xs text-indigo-100 font-medium mt-1">
                                👥 {{ $squadList->count() }} Anggota Terdaftar &bull; 📍 {{ $modalEkskul->location ?: 'Kampus Pembda' }}
                            </p>
                        </div>
                    </div>

                    {{-- Close Button --}}
                    <button type="button" @click="activeModal = null" class="w-9 h-9 rounded-xl bg-white/15 hover:bg-white/30 text-white flex items-center justify-center transition shrink-0 active:scale-95 cursor-pointer">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                {{-- Scrollable Modal Content --}}
                <div class="p-5 sm:p-7 overflow-y-auto space-y-6 flex-1 text-slate-800">
                    
                    {{-- Seksi 1: Dewan Pembina & Pengurus Inti (Leadership Team) --}}
                    <div class="space-y-3">
                        <h4 class="text-xs font-black uppercase tracking-wider text-purple-700 flex items-center gap-2">
                            <i class="fas fa-crown text-amber-500"></i> Dewan Pembina & Pengurus Inti
                        </h4>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            {{-- Pembina / Pelatih --}}
                            <div class="bg-gradient-to-br from-indigo-50/80 to-purple-50/50 p-4 rounded-2xl border border-indigo-100 flex items-center gap-3.5">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 text-white flex items-center justify-center text-xl shadow-xs shrink-0" style="width: 48px; height: 48px; min-width: 48px; max-width: 48px;">
                                    👨‍🏫
                                </div>
                                <div class="min-w-0 flex-1 pl-1">
                                    <span class="text-[9px] font-black uppercase tracking-wider text-indigo-700 block mb-0.5">Pembina / Manager</span>
                                    <p class="font-black text-slate-900 text-xs sm:text-sm truncate">
                                        {{ $modalEkskul->manager_name ?: ($modalEkskul->advisor_name ?: ($modalEkskul->advisor->full_name ?? 'PKS Kesiswaan')) }}
                                    </p>
                                    <p class="text-[10px] text-slate-500 font-semibold">Dewan Pengampu</p>
                                </div>
                            </div>

                            {{-- Ketua / Koordinator Siswa --}}
                            <div class="bg-gradient-to-br from-amber-50/80 to-orange-50/50 p-4 rounded-2xl border border-amber-200 flex items-center gap-3.5">
                                <div class="relative shrink-0 w-12 h-12" style="width: 48px; height: 48px; min-width: 48px; max-width: 48px;">
                                    @if($modalEkskul->leader && $modalEkskul->leader->photo_url)
                                        <img src="{{ $modalEkskul->leader->photo_url }}" alt="{{ $modalEkskul->leader->full_name }}" class="w-12 h-12 rounded-xl object-cover border-2 border-amber-400 bg-white" style="width: 48px; height: 48px; min-width: 48px; max-width: 48px;">
                                    @else
                                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center text-xl shadow-xs" style="width: 48px; height: 48px; min-width: 48px; max-width: 48px;">
                                            👑
                                        </div>
                                    @endif
                                    <span class="absolute bottom-0 right-0 w-4 h-4 rounded-full bg-amber-500 text-white flex items-center justify-center text-[7px] border border-white">
                                        <i class="fas fa-crown"></i>
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1 pl-1">
                                    <span class="text-[9px] font-black uppercase tracking-wider text-amber-800 block mb-0.5">Ketua / Koordinator</span>
                                    <p class="font-black text-slate-900 text-xs sm:text-sm truncate">
                                        {{ $modalEkskul->leader->full_name ?? 'Akan Ditunjuk Pembina' }}
                                    </p>
                                    <p class="text-[10px] text-slate-500 font-semibold">
                                        {{ $modalEkskul->leader->classroom->class_name ?? ($modalEkskul->leader ? 'Siswa Aktif' : 'Struktur Rintisan') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Seksi 2: Pembagian Section / Alat Musik (Khusus Marching Band / Unit Bersection) --}}
                    @if(!empty($modalEkskul->section_list) && count($modalEkskul->section_list) > 0)
                    <div class="space-y-3">
                        <h4 class="text-xs font-black uppercase tracking-wider text-purple-700 flex items-center gap-2">
                            <i class="fas fa-layer-group text-purple-600"></i> Divisi & Section Alat
                        </h4>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                            @foreach($modalEkskul->section_list as $secKey => $secLabel)
                            @php 
                                $secName = is_numeric($secKey) ? $secLabel : $secKey;
                                $countInSec = $squadList->where('section', $secName)->count();
                            @endphp
                            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 flex items-center justify-between gap-2">
                                <span class="text-xs font-black text-slate-800 truncate">{{ $secLabel }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-purple-100 text-purple-900 shrink-0">
                                    {{ $countInSec }} Siswa
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Seksi 3: Daftar Anggota Squad Terverifikasi --}}
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black uppercase tracking-wider text-purple-700 flex items-center gap-2">
                                <i class="fas fa-users text-indigo-600"></i> Direktori Anggota Squad ({{ $squadList->count() }})
                            </h4>
                        </div>

                        @if($squadList->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-72 overflow-y-auto pr-1">
                            @foreach($squadList as $squadItem)
                            @php 
                                $isMe = ($squadItem->student_id === $student->id);
                            @endphp
                            <div class="p-3 rounded-2xl border {{ $isMe ? 'bg-indigo-50/90 border-indigo-300 ring-2 ring-indigo-200' : 'bg-white border-slate-200 hover:border-indigo-200' }} flex items-center gap-3.5 transition shadow-2xs">
                                <div class="relative shrink-0 w-10 h-10" style="width: 40px; height: 40px; min-width: 40px; max-width: 40px;">
                                    <img src="{{ $squadItem->student->photo_url ?? asset('images/default-avatar.png') }}" alt="{{ $squadItem->student->full_name ?? 'Siswa' }}" class="w-10 h-10 rounded-xl object-cover border {{ $isMe ? 'border-indigo-500' : 'border-slate-300' }} bg-slate-100" style="width: 40px; height: 40px; min-width: 40px; max-width: 40px;">
                                    @if($isMe)
                                    <span class="absolute -bottom-1 -right-1 px-1 py-0.2 rounded-md bg-indigo-600 text-white font-black text-[7px]">Kamu</span>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1 pl-1">
                                    <div class="flex items-center gap-1.5 mb-0.5">
                                        <span class="px-2 py-0.2 rounded-full text-[8px] font-black text-white bg-gradient-to-r {{ $squadItem->role_badge_color }} shadow-2xs">
                                            {{ $squadItem->role_label }}
                                        </span>
                                        @if($squadItem->section)
                                        <span class="px-1.5 py-0.2 rounded-full text-[8px] font-black bg-purple-100 text-purple-900 border border-purple-200">
                                            🎺 {{ $squadItem->section }}
                                        </span>
                                        @endif
                                    </div>
                                    <h5 class="font-black text-slate-900 text-xs truncate">
                                        {{ $squadItem->student->full_name ?? 'Nama Siswa' }}
                                    </h5>
                                    <p class="text-[10px] text-slate-500 font-semibold truncate">
                                        {{ $squadItem->student->school->short_name ?? ($squadItem->student->school->name ?? 'Pembda') }} &bull; {{ $squadItem->student->classroom->class_name ?? 'Siswa' }}
                                    </p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-xs text-slate-500 italic p-4 text-center bg-slate-50 rounded-2xl">Belum ada anggota yang terdaftar di unit ini.</p>
                        @endif
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div class="bg-slate-50 p-4 sm:p-5 border-t border-slate-200 flex items-center justify-between gap-3 shrink-0">
                    <span class="text-xs font-bold text-slate-500">
                        <i class="fas fa-shield-alt text-emerald-600 mr-1"></i> Data Terverifikasi Sistem
                    </span>
                    <div class="flex items-center gap-2">
                        @if($modalEkskul->forum_group_id)
                        <a href="{{ route('forum.index', ['group' => $modalEkskul->forum_group_id]) }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-black transition flex items-center gap-1.5 shadow-sm active:scale-95">
                            <i class="fas fa-comments"></i>
                            <span>Buka Pembda Space</span>
                        </a>
                        @endif
                        <button type="button" @click="activeModal = null" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-xl text-xs font-black transition active:scale-95 cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @endforeach

    {{-- MODAL PREVIEW & DOWNLOAD VIP E-CARD DIGITAL UNTUK SETIAP KEANGGOTAAN SAYA --}}
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
        <div class="min-h-full flex items-center justify-center p-3 sm:p-6 text-center">
            <div class="relative w-full max-w-2xl bg-white rounded-3xl text-left shadow-2xl overflow-hidden border-2 border-indigo-200 transform transition-all flex flex-col"
                 x-show="activeModal === 'ecard-{{ $membership->id }}'"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                 @click.away="activeModal = null">

                {{-- Modal Header --}}
                <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-purple-950 text-white p-5 sm:p-6 flex items-center justify-between border-b border-indigo-900/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-400 text-slate-950 flex items-center justify-center text-xl shadow-sm">
                            💳
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-black text-white leading-tight">
                                Official Digital Member E-Card
                            </h3>
                            <p class="text-xs text-indigo-200 font-medium">
                                Kartu Keanggotaan Resmi {{ $ekskul->name }}
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="activeModal = null" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition active:scale-95 cursor-pointer">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>

                {{-- Modal Body: E-Card Canvas Preview --}}
                <div class="p-4 sm:p-6 bg-slate-100/80 flex flex-col items-center justify-center overflow-x-auto">
                    
                    {{-- THE OFFICIAL E-CARD COMPONENT (To be captured as image) --}}
                    <div id="ecard-card-{{ $membership->id }}" 
                         class="relative w-[520px] max-w-[520px] rounded-3xl p-6 text-white shadow-2xl overflow-hidden select-none border-2 border-amber-400/50 flex flex-col justify-between"
                         style="background: linear-gradient(135deg, #090d16 0%, #1e153a 50%, #2e0854 100%); min-height: 310px;">
                        
                        {{-- Background Holographic Decorative Patterns --}}
                        <div class="absolute -right-12 -top-12 w-48 h-48 rounded-full bg-purple-500/10 blur-2xl pointer-events-none"></div>
                        <div class="absolute -left-12 -bottom-12 w-48 h-48 rounded-full bg-amber-500/10 blur-2xl pointer-events-none"></div>
                        <div class="absolute right-4 bottom-2 text-9xl opacity-[0.05] pointer-events-none font-sans font-black">
                            {{ $ekskul->display_icon }}
                        </div>

                        {{-- Card Header --}}
                        <div class="relative z-10 flex items-start justify-between gap-3 border-b border-white/15 pb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center p-1 shadow-inner">
                                    <img src="{{ asset('images/logo_yayasan.png') }}" crossorigin="anonymous" alt="Logo Yayasan" class="w-8 h-8 object-contain" onerror="this.src='{{ asset('images/logo-pembda.png') }}'">
                                </div>
                                <div>
                                    <h4 class="text-[11px] font-black uppercase tracking-wider text-amber-300">
                                        YAYASAN PERGURUAN PEMBDA NIAS
                                    </h4>
                                    <p class="text-[9px] text-indigo-100 font-bold uppercase tracking-wide">
                                        OFFICIAL SQUAD PASS &bull; EKSTRAKURIKULER
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[8px] font-mono font-black bg-amber-400 text-slate-950 shadow-xs">
                                    #EKS-{{ str_pad($ekskul->id, 2, '0', STR_PAD_LEFT) }}-{{ str_pad($membership->id, 4, '0', STR_PAD_LEFT) }}
                                </span>
                                <p class="text-[7px] text-slate-400 font-mono mt-0.5">STATUS: AKTIF</p>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="relative z-10 my-3 flex items-center gap-4">
                            {{-- Student Photo --}}
                            <div class="relative shrink-0">
                                <img src="{{ $student->photo_url }}" crossorigin="anonymous" alt="{{ $student->full_name }}" class="w-20 h-20 rounded-2xl object-cover border-2 {{ $isLeader ? 'border-amber-400' : 'border-indigo-400' }} shadow-md bg-white" style="width: 80px; height: 80px; min-width: 80px; max-width: 80px;">
                                <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[8px] border-2 border-slate-900 shadow-xs">
                                    <i class="fas fa-check"></i>
                                </span>
                            </div>

                            {{-- Student & Ekskul Details --}}
                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-md text-[8px] font-black uppercase tracking-wider text-white bg-gradient-to-r {{ $membership->role_badge_color }}">
                                        {{ $isLeader ? '👑 ' : '' }}{{ $membership->role_label }}
                                    </span>
                                    @if($membership->section)
                                    <span class="px-2 py-0.5 rounded-md text-[8px] font-black uppercase tracking-wider bg-purple-200/90 text-purple-950">
                                        🎺 {{ $membership->section }}
                                    </span>
                                    @endif
                                </div>

                                <h3 class="font-black text-white text-base leading-tight truncate">
                                    {{ $student->full_name }}
                                </h3>

                                <p class="text-[10px] text-indigo-200 font-medium">
                                    NISN: <b>{{ $student->nisn ?: '-' }}</b> &bull; {{ $student->classroom->class_name ?? 'Siswa' }} ({{ $student->school->short_name ?? 'Pembda' }})
                                </p>

                                <div class="pt-1 flex items-center gap-1.5">
                                    <span class="text-xs">{{ $ekskul->display_icon }}</span>
                                    <span class="text-xs font-black text-amber-300 truncate">
                                        {{ $ekskul->name }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Card Footer --}}
                        <div class="relative z-10 pt-2.5 border-t border-white/15 flex items-center justify-between gap-3 text-[8px] text-slate-300">
                            <div class="flex items-center gap-2">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=PEMBDA-EKS-{{ $membership->id }}-{{ $student->nisn }}&color=ffffff&bgcolor=00000000" 
                                     crossorigin="anonymous"
                                     alt="QR Verification" 
                                     class="w-8 h-8 object-contain rounded bg-white/10 p-0.5 border border-white/20 shrink-0" 
                                     style="width: 32px; height: 32px; min-width: 32px; max-width: 32px;">
                                <div class="leading-tight">
                                    <p class="font-black text-white">TERVERIFIKASI SISTEM</p>
                                    <p class="text-slate-400 font-mono">perguruanpembda.com</p>
                                </div>
                            </div>

                            <div class="text-center font-bold text-slate-400 italic">
                                "Disiplin &bull; Kreatif &bull; Berprestasi"
                            </div>

                            <div class="text-right leading-tight">
                                <p class="text-slate-400 font-medium">Pembina / Kesiswaan</p>
                                <p class="font-black text-white truncate max-w-[130px]">
                                    {{ $ekskul->manager_name ?: ($ekskul->advisor_name ?: ($ekskul->advisor->full_name ?? 'PKS Kesiswaan')) }}
                                </p>
                            </div>
                        </div>

                    </div>

                </div>

                {{-- Modal Action Buttons --}}
                <div class="bg-white p-5 sm:p-6 border-t border-slate-200 flex flex-wrap items-center justify-between gap-4">
                    <div class="text-xs text-slate-500 font-bold flex items-center gap-1.5">
                        <i class="fas fa-sparkles text-amber-500"></i> Render HD 3x Retina
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" 
                                onclick="downloadEcardCard('ecard-card-{{ $membership->id }}', 'E-Card_{{ Str::slug($student->full_name) }}_{{ Str::slug($ekskul->name) }}.png')"
                                class="px-6 py-3 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-700 hover:to-pink-700 text-white rounded-2xl text-xs font-black transition shadow-md active:scale-95 flex items-center gap-2.5 cursor-pointer whitespace-nowrap">
                            <i class="fas fa-download text-sm"></i>
                            <span class="px-1">Unduh Gambar (PNG)</span>
                        </button>

                        <button type="button" 
                                onclick="printEcardCard('ecard-card-{{ $membership->id }}')"
                                class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 rounded-2xl text-xs font-black transition flex items-center gap-2 active:scale-95 cursor-pointer whitespace-nowrap">
                            <i class="fas fa-print text-slate-600 text-sm"></i>
                            <span class="px-1">Cetak</span>
                        </button>

                        <button type="button" 
                                onclick="shareEcardCard('ecard-card-{{ $membership->id }}', 'E-Card {{ $ekskul->name }} - {{ $student->full_name }}')"
                                class="px-6 py-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border-2 border-emerald-300 rounded-2xl text-xs font-black transition flex items-center gap-2 active:scale-95 cursor-pointer whitespace-nowrap">
                            <i class="fas fa-share-alt text-emerald-600 text-sm"></i>
                            <span class="px-1">Share</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @endforeach

</div>

{{-- SCRIPT DEDIKASI GENERATE E-CARD HD --}}
<script src="{{ asset('vendor/html2canvas.min.js') }}"></script>

<script>
function ensureHtml2Canvas() {
    return new Promise((resolve) => {
        if (typeof html2canvas !== 'undefined') {
            return resolve(window.html2canvas);
        }
        const s = document.createElement('script');
        s.src = '{{ asset('vendor/html2canvas.min.js') }}';
        s.onload = () => resolve(window.html2canvas);
        s.onerror = () => {
            const fallback = document.createElement('script');
            fallback.src = 'https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js';
            fallback.onload = () => resolve(window.html2canvas);
            fallback.onerror = () => resolve(null);
            document.head.appendChild(fallback);
        };
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
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> <span class="px-1">Merender HD...</span>';
    }

    try {
        const h2c = await ensureHtml2Canvas();
        if (!h2c) {
            throw new Error('Gagal memuat library pengunduh kartu. Silakan periksa koneksi internet Anda atau gunakan tombol Cetak.');
        }

        const canvas = await h2c(card, {
            scale: 3,
            useCORS: true,
            allowTaint: true,
            backgroundColor: null,
            logging: false,
        });

        const imageUri = canvas.toDataURL('image/png');
        const downloadLink = document.createElement('a');
        downloadLink.download = filename || 'E-Card-Ekskul-Pembda.png';
        downloadLink.href = imageUri;
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);

        if (btn) {
            btn.innerHTML = '<i class="fas fa-check mr-1"></i> <span class="px-1">Berhasil Disimpan!</span>';
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = oldHtml;
            }, 3000);
        }
    } catch (err) {
        console.error('Download E-Card Error:', err);
        alert('Gagal mengunduh kartu: ' + err.message + '\n\nTips: Anda juga dapat menggunakan tombol Cetak untuk menyimpan sebagai PDF.');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = oldHtml;
        }
    }
}

function printEcardCard(elementId) {
    const card = document.getElementById(elementId);
    if (!card) {
        alert('Elemen kartu tidak ditemukan.');
        return;
    }

    const printWin = window.open('', '_blank', 'width=800,height=600');
    if (!printWin) {
        window.print();
        return;
    }

    printWin.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Cetak E-Card Ekskul Pembda</title>
            <style>
                @page { size: auto; margin: 10mm; }
                body {
                    margin: 0;
                    padding: 20px;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 90vh;
                    font-family: system-ui, -apple-system, sans-serif;
                    background: #ffffff;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
            </style>
            <script src="https://cdn.tailwindcss.com"><\/script>
        </head>
        <body>
            <div style="max-width: 540px; width: 100%;">
                ${card.outerHTML}
            </div>
            <script>
                setTimeout(() => {
                    window.print();
                    window.close();
                }, 800);
            <\/script>
        </body>
        </html>
    `);
    printWin.document.close();
}

async function shareEcardCard(elementId, title) {
    const card = document.getElementById(elementId);
    if (!card) return;

    if (navigator.share) {
        try {
            const h2c = await ensureHtml2Canvas();
            if (h2c) {
                const canvas = await h2c(card, { scale: 2, useCORS: true, allowTaint: true });
                canvas.toBlob(async (blob) => {
                    if (blob && navigator.canShare && navigator.canShare({ files: [new File([blob], 'ecard.png', { type: 'image/png' })] })) {
                        const file = new File([blob], 'ecard-pembda.png', { type: 'image/png' });
                        await navigator.share({
                            title: title,
                            text: 'Kartu Anggota Resmi Ekskul Perguruan Pembda',
                            files: [file]
                        });
                        return;
                    }
                    await navigator.share({
                        title: title,
                        text: 'Kartu Anggota Resmi Ekskul Perguruan Pembda - https://perguruanpembda.com',
                        url: window.location.href
                    });
                });
            } else {
                await navigator.share({
                    title: title,
                    text: 'Kartu Anggota Resmi Ekskul Perguruan Pembda',
                    url: window.location.href
                });
            }
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
