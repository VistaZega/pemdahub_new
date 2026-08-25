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
            
            <div class="flex items-center gap-4 bg-gradient-to-br from-indigo-500 to-purple-600 text-white p-4.5 rounded-3xl shadow-md border-2 border-indigo-300/40 flex-shrink-0">
                <div class="w-13 h-13 rounded-2xl bg-white text-indigo-700 flex items-center justify-center text-3xl shadow-sm">
                    🏆
                </div>
                <div>
                    <p class="text-[10px] text-indigo-100 font-black uppercase tracking-wider">Keaktifan Saya</p>
                    <p class="text-lg font-black text-white">{{ $myMemberships->count() }} <span class="text-xs font-bold text-indigo-200">Unit Diikuti</span></p>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION 1: UNIT EKSKUL YANG DI SAYA IKUTI --}}
    <div class="space-y-3">
        <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
            <i class="fas fa-id-badge text-indigo-600"></i> Ekstrakurikuler yang Saya Ikuti
        </h2>

        @if($myMemberships->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($myMemberships as $membership)
            @php $ekskul = $membership->extracurricular; @endphp
            <div class="bg-white rounded-3xl border-2 border-indigo-200 shadow-sm p-6 flex flex-col justify-between hover:shadow-lg transition">
                <div class="space-y-4">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-3.5">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 via-rose-500 to-purple-600 text-white flex items-center justify-center text-3xl shadow-sm flex-shrink-0">
                                {{ $ekskul->display_icon }}
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold text-white bg-gradient-to-r {{ $membership->role_badge_color }} shadow-2xs">
                                        {{ $membership->role_label }}
                                    </span>
                                    @if($membership->section)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-gradient-to-r from-purple-100 to-pink-100 text-purple-950 border border-purple-300">
                                        🎺 {{ $membership->section }}
                                    </span>
                                    @endif
                                </div>
                                <h3 class="font-black text-slate-900 text-sm mt-1 leading-snug">{{ $ekskul->name }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs bg-gradient-to-br from-slate-50 to-indigo-50/40 p-4 rounded-2xl border border-indigo-100 text-slate-700">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-500">📅 Jadwal:</span>
                            <span class="font-black text-slate-900">{{ $ekskul->schedule_day_time ?: 'Sesuai Arahan Pembina' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-500">📍 Lokasi:</span>
                            <span class="font-black text-slate-900">{{ $ekskul->location ?: 'Kampus Pembda' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-500">👨‍🏫 Pembina / Manager:</span>
                            <span class="font-black text-slate-900 truncate max-w-[150px]">{{ $ekskul->manager_name ?: ($ekskul->advisor_name ?: 'PKS Kesiswaan') }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3.5 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-black text-indigo-700 flex items-center gap-1">
                        <i class="fas fa-star text-amber-500"></i> +{{ $membership->points_awarded }} Poin Reputasi
                    </span>

                    @if($ekskul->forum_group_id)
                    <a href="{{ route('forum.index', ['group' => $ekskul->forum_group_id]) }}" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl text-xs font-black transition flex items-center gap-1.5 shadow-sm active:scale-95">
                        <i class="fas fa-comments"></i>
                        <span>Buka Space</span>
                    </a>
                    @endif
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
                    <div class="flex items-center gap-3.5">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 via-rose-500 to-purple-600 text-white flex items-center justify-center text-3xl group-hover:scale-105 transition flex-shrink-0 shadow-sm">
                            {{ $ekskul->display_icon }}
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-2xs">
                                    {{ $ekskul->category_label }}
                                </span>
                                @if($ekskul->isFoundationLevel())
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-gradient-to-r from-amber-400 to-orange-500 text-white shadow-2xs">
                                    🏛️ Lintas Yayasan
                                </span>
                                @endif
                            </div>
                            <h3 class="font-black text-slate-900 text-base mt-1 leading-snug group-hover:text-indigo-600 transition">
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
                            <span class="font-bold text-slate-500">👥 Anggota:</span>
                            <span class="font-black text-indigo-700">{{ $ekskul->active_members_count }} Siswa Aktif</span>
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
