@extends('layouts.mobile')

@section('title', 'Ekstrakurikuler Siswa')

@section('content')
<div class="space-y-4 pb-20" x-data="{ activeTab: 'my' }">

    {{-- Top Bar --}}
    <div class="flex items-center justify-between pt-1">
        <a href="{{ route('mobile.dashboard') }}" class="w-10 h-10 rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center justify-center text-slate-700 active:scale-95 transition">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div class="text-center">
            <h1 class="text-sm font-black text-slate-900 leading-tight">Ekstrakurikuler</h1>
            <p class="text-[10px] text-purple-600 font-bold uppercase tracking-wider">{{ $student->school->short_name ?: $student->school->name }}</p>
        </div>
        <a href="{{ route('mobile.space.index') }}" class="w-10 h-10 rounded-2xl bg-purple-50 border border-purple-200 text-purple-700 flex items-center justify-center text-sm active:scale-95 transition" title="Pembda Space">
            <i class="fa-solid fa-comments"></i>
        </a>
    </div>

    {{-- Hero 3D Card --}}
    <div class="bg-gradient-to-br from-indigo-900 via-purple-900 to-slate-950 p-5 rounded-3xl text-white relative overflow-hidden shadow-lg border border-indigo-800/40">
        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-purple-500/20 rounded-full blur-2xl"></div>
        <div class="relative z-10 space-y-3">
            <div class="flex items-center justify-between">
                <span class="px-2.5 py-1 bg-white/20 backdrop-blur-xs rounded-full text-[10px] font-black tracking-wider uppercase flex items-center gap-1">
                    <span>🏆</span> Bakat & Karakter
                </span>
                <span class="text-[11px] font-black text-amber-300">
                    +15 s/d +30 Poin Reputasi
                </span>
            </div>

            <div class="space-y-1">
                <h2 class="text-lg font-black text-white leading-snug">Unit Kegiatan Siswa</h2>
                <p class="text-[11px] text-slate-200 leading-relaxed">
                    Setiap keaktifan ekstrakurikuler otomatis mendongkrak profil <b>DNA 360°</b> dan membuka kanal diskusi ekskul di <b>Pembda Space</b>!
                </p>
            </div>

            {{-- Counter Pill --}}
            <div class="grid grid-cols-2 gap-2 pt-1">
                <div class="bg-white/10 rounded-2xl p-2.5 border border-white/10 text-center">
                    <p class="text-[10px] text-slate-300 font-bold uppercase">Diikuti</p>
                    <p class="text-base font-black text-white">{{ $myMemberships->count() }} <span class="text-[10px] font-normal text-purple-200">Unit</span></p>
                </div>
                <div class="bg-white/10 rounded-2xl p-2.5 border border-white/10 text-center">
                    <p class="text-[10px] text-slate-300 font-bold uppercase">Tersedia</p>
                    <p class="text-base font-black text-amber-300">{{ $availableEkskuls->count() }} <span class="text-[10px] font-normal text-purple-200">Pilihan</span></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Segmented Control --}}
    <div class="bg-slate-200/80 p-1 rounded-2xl flex text-xs font-bold shadow-inner">
        <button @click="activeTab = 'my'" :class="activeTab === 'my' ? 'bg-white text-indigo-900 shadow-xs font-black' : 'text-slate-600'" class="flex-1 py-2 rounded-xl transition flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-id-badge"></i>
            <span>Ekskul Saya ({{ $myMemberships->count() }})</span>
        </button>
        <button @click="activeTab = 'explore'" :class="activeTab === 'explore' ? 'bg-white text-indigo-900 shadow-xs font-black' : 'text-slate-600'" class="flex-1 py-2 rounded-xl transition flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-compass"></i>
            <span>Jelajahi ({{ $availableEkskuls->count() }})</span>
        </button>
    </div>

    {{-- TAB 1: EKSKUL SAYA --}}
    <div x-show="activeTab === 'my'" class="space-y-3" x-transition.duration.200ms>
        @forelse($myMemberships as $membership)
        @php $ekskul = $membership->extracurricular; @endphp
        <div class="bg-white p-4.5 rounded-3xl border-2 border-indigo-100 shadow-xs space-y-3">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-2xl shadow-2xs flex-shrink-0">
                        {{ $ekskul->display_icon }}
                    </div>
                    <div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase text-white bg-gradient-to-r {{ $membership->role_badge_color }} shadow-2xs">
                            {{ $membership->role_label }}
                        </span>
                        <h3 class="font-black text-slate-900 text-sm mt-1 leading-snug">{{ $ekskul->name }}</h3>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 space-y-1.5 text-xs text-slate-600">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">📅 Jadwal:</span>
                    <span class="font-bold text-slate-800">{{ $ekskul->schedule_day_time ?: 'Fleksibel' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">📍 Lokasi:</span>
                    <span class="font-bold text-slate-800">{{ $ekskul->location ?: 'Kampus Pembda' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">👨‍🏫 Pembina:</span>
                    <span class="font-bold text-slate-800 truncate max-w-[160px]">{{ $ekskul->advisor_name ?: 'PKS Kesiswaan' }}</span>
                </div>
            </div>

            <div class="flex items-center justify-between pt-1">
                <span class="text-xs font-bold text-indigo-600 flex items-center gap-1">
                    <i class="fa-solid fa-award text-amber-500"></i> +{{ $membership->points_awarded }} Poin
                </span>

                @if($ekskul->forum_group_id)
                <a href="{{ route('mobile.space.index') }}" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl text-xs font-bold shadow-xs active:scale-95 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-comments"></i>
                    <span>Buka Space</span>
                </a>
                @endif
            </div>
        </div>
        @empty
        <div class="p-8 bg-white rounded-3xl border border-dashed border-slate-300 text-center space-y-2">
            <p class="text-3xl">🌱</p>
            <p class="font-bold text-slate-800 text-xs">Belum Bergabung di Ekskul</p>
            <p class="text-[11px] text-slate-500 leading-relaxed">Buka tab <b>'Jelajahi'</b> di atas untuk memilih dan mengklaim unit kegiatan ekstrakurikuler favoritmu!</p>
        </div>
        @endforelse
    </div>

    {{-- TAB 2: JELAJAHI --}}
    <div x-show="activeTab === 'explore'" class="space-y-3" x-transition.duration.200ms style="display: none;">
        @foreach($availableEkskuls as $ekskul)
        @php $isJoined = in_array($ekskul->id, $joinedEkskulIds); @endphp
        <div class="bg-white p-4.5 rounded-3xl border border-slate-200/80 shadow-xs space-y-3">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-50 to-purple-50 border border-slate-200 flex items-center justify-center text-2xl shadow-2xs flex-shrink-0">
                    {{ $ekskul->display_icon }}
                </div>
                <div class="min-w-0 flex-1">
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-slate-100 text-slate-700">
                        {{ $ekskul->category_label }}
                    </span>
                    <h3 class="font-black text-slate-900 text-xs mt-1 leading-snug truncate">{{ $ekskul->name }}</h3>
                </div>
            </div>

            <p class="text-[11px] text-slate-500 leading-relaxed">
                {{ $ekskul->description ?: 'Kegiatan pengembangan bakat dan potensi siswa.' }}
            </p>

            <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-100 space-y-1 text-[11px] text-slate-600">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">📅 Jadwal:</span>
                    <span class="font-semibold text-slate-800">{{ $ekskul->schedule_day_time ?: 'Fleksibel' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">👥 Anggota:</span>
                    <span class="font-bold text-indigo-600">{{ $ekskul->active_members_count }} Siswa Aktif</span>
                </div>
            </div>

            <div class="pt-1 flex items-center justify-between">
                @if($isJoined)
                <span class="px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl text-[11px] font-bold flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check"></i> Sudah Terdaftar
                </span>
                @else
                <form action="{{ route('mobile.ekskul.claim', $ekskul) }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit" onclick="return confirm('Daftar ke unit {{ $ekskul->name }}?')" class="w-full py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-xs font-bold shadow-xs active:scale-95 transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-plus"></i>
                        <span>Gabung / Klaim Ekskul Ini</span>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection
