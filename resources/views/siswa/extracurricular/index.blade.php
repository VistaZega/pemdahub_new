@extends('layouts.siswa')

@section('title', 'Ekstrakurikuler & Unit Kegiatan Siswa')

@section('content')
<div class="space-y-6">

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

                        {{-- Squad Teammates (Avatar Stack & Social Proof) --}}
                        <div class="bg-indigo-50/50 rounded-2xl p-3.5 border border-indigo-100/70 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-black text-slate-800 flex items-center gap-1.5">
                                    <i class="fas fa-users text-indigo-600"></i> Rekan Squad
                                </span>
                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-900">
                                    {{ $activeSquad->count() }} Anggota Aktif
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
                                <p class="text-[10px] text-slate-600 font-bold text-right leading-tight">
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
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2 mt-3">
                        <span class="text-xs font-black text-indigo-700 flex items-center gap-1.5 bg-amber-50 border border-amber-200/80 px-2.5 py-1 rounded-xl">
                            <i class="fas fa-award text-amber-500"></i> +{{ $membership->points_awarded }} Poin Reputasi
                        </span>

                        @if($ekskul->forum_group_id)
                        <a href="{{ route('forum.index', ['group' => $ekskul->forum_group_id]) }}" class="px-4 py-2 bg-gradient-to-r from-purple-600 via-indigo-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl text-xs font-black transition flex items-center gap-1.5 shadow-sm active:scale-95">
                            <i class="fas fa-comments"></i>
                            <span>Buka Space</span>
                        </a>
                        @endif
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

                <div class="mt-4 pt-3.5 border-t border-slate-100">
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

</div>
@endsection
