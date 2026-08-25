@extends('layouts.siswa')

@section('title', 'Ekstrakurikuler & Unit Kegiatan Siswa')

@section('content')
<div class="space-y-6">

    {{-- Hero Banner --}}
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-purple-950 rounded-3xl p-6 sm:p-8 text-white relative overflow-hidden shadow-lg border border-slate-800">
        <div class="absolute -top-12 -right-12 w-64 h-64 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
            <div class="space-y-2">
                <div class="flex items-center gap-2 text-xs font-semibold text-purple-300">
                    <span>Eksplorasi Minat & Bakat Siswa</span>
                    <span>&bull;</span>
                    <span class="bg-purple-500/30 px-2.5 py-0.5 rounded-full text-white font-bold">+15 s/d +30 Poin Reputasi</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-3">
                    <span>🎨 Ekstrakurikuler & Non-Akademik</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 max-w-2xl leading-relaxed">
                    Kembangkan jiwa kepemimpinan, kreativitas seni, dan kebugaran jasmani. Keikutsertaan ekskul akan langsung memperkuat profil <b>DNA Akademik 360°</b> dan mengaktifkan kanal diskusi squad di <b>Pembda Space</b>!
                </p>
            </div>
            
            <div class="flex items-center gap-3 bg-white/10 p-3.5 rounded-2xl border border-white/15 backdrop-blur-xs flex-shrink-0">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-2xl shadow-sm">
                    🏆
                </div>
                <div>
                    <p class="text-[10px] text-slate-300 font-bold uppercase">Keaktifan Saya</p>
                    <p class="text-base font-black text-white">{{ $myMemberships->count() }} <span class="text-xs font-normal text-purple-200">Unit Diikuti</span></p>
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
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($myMemberships as $membership)
            @php $ekskul = $membership->extracurricular; @endphp
            <div class="bg-white rounded-2xl border-2 border-indigo-100 p-5 shadow-xs flex flex-col justify-between hover:shadow-md transition">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-2xl shadow-2xs">
                                {{ $ekskul->display_icon }}
                            </div>
                            <div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold text-white bg-gradient-to-r {{ $membership->role_badge_color }} shadow-2xs">
                                    {{ $membership->role_label }}
                                </span>
                                <h3 class="font-black text-slate-900 text-sm mt-1 leading-snug">{{ $ekskul->name }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1.5 text-xs bg-slate-50 p-3 rounded-xl border border-slate-100 text-slate-600">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">📅 Jadwal:</span>
                            <span class="font-bold text-slate-800">{{ $ekskul->schedule_day_time ?: 'Sesuai Arahan Pembina' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">📍 Lokasi:</span>
                            <span class="font-bold text-slate-800">{{ $ekskul->location ?: 'Kampus Pembda' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">👨‍🏫 Pembina:</span>
                            <span class="font-bold text-slate-800 truncate max-w-[150px]">{{ $ekskul->advisor_name ?: 'PKS Kesiswaan' }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-indigo-600 flex items-center gap-1">
                        <i class="fas fa-star text-amber-500"></i> +{{ $membership->points_awarded }} Poin Reputasi
                    </span>

                    @if($ekskul->forum_group_id)
                    <a href="{{ route('space.index', ['group' => $ekskul->forum_group_id]) }}" class="px-3.5 py-1.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs active:scale-95">
                        <i class="fas fa-comments"></i>
                        <span>Buka Space</span>
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="p-6 bg-white rounded-2xl border border-dashed border-slate-300 text-center space-y-2">
            <p class="text-2xl">🌱</p>
            <p class="font-bold text-slate-800 text-sm">Kamu belum bergabung di ekstrakurikuler manapun.</p>
            <p class="text-xs text-slate-500">Pilih unit kegiatan favoritmu di bawah dan klik <b>'Gabung / Klaim Ekskul'</b> untuk mulai mengumpulkan poin reputasi!</p>
        </div>
        @endif
    </div>

    {{-- SECTION 2: KATALOG EKSKUL TERSEDIA --}}
    <div class="space-y-3 pt-4">
        <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
            <i class="fas fa-compass text-purple-600"></i> Katalog Ekstrakurikuler Sekolah ({{ $availableEkskuls->count() }} Pilihan)
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($availableEkskuls as $ekskul)
            @php $isJoined = in_array($ekskul->id, $joinedEkskulIds); @endphp
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 flex flex-col justify-between hover:shadow-md transition group">
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-slate-100 to-indigo-50 border border-slate-200 flex items-center justify-center text-2xl group-hover:scale-105 transition">
                            {{ $ekskul->display_icon }}
                        </div>
                        <div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-700">
                                {{ $ekskul->category_label }}
                            </span>
                            <h3 class="font-black text-slate-900 text-sm mt-1 leading-snug group-hover:text-indigo-600 transition">
                                {{ $ekskul->name }}
                            </h3>
                        </div>
                    </div>

                    <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                        {{ $ekskul->description ?: 'Kegiatan pembinaan bakat dan karakter positif siswa.' }}
                    </p>

                    <div class="space-y-1.5 text-xs bg-slate-50 p-3 rounded-xl border border-slate-100 text-slate-600">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">📅 Waktu:</span>
                            <span class="font-semibold text-slate-800">{{ $ekskul->schedule_day_time ?: 'Fleksibel' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">📍 Tempat:</span>
                            <span class="font-semibold text-slate-800">{{ $ekskul->location ?: 'Kampus Pembda' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">👥 Anggota:</span>
                            <span class="font-bold text-indigo-600">{{ $ekskul->active_members_count }} Siswa Aktif</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    @if($isJoined)
                    <span class="px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl text-xs font-bold flex items-center gap-1.5">
                        <i class="fas fa-check-circle"></i> Sudah Terdaftar
                    </span>
                    @if($ekskul->forum_group_id)
                    <a href="{{ route('space.index', ['group' => $ekskul->forum_group_id]) }}" class="text-xs font-bold text-purple-700 hover:text-purple-900">
                        Buka Space &rarr;
                    </a>
                    @endif
                    @else
                    <form action="{{ route('siswa.ekskul.claim', $ekskul) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Daftar ke {{ $ekskul->name }}?')" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-1.5 active:scale-95">
                            <i class="fas fa-plus"></i>
                            <span>Gabung / Klaim Ekskul</span>
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
