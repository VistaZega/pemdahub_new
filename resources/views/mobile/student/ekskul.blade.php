@extends('mobile.layouts.app')

@section('title', 'Ekstrakurikuler Siswa')

@section('content')
<div class="space-y-4 pb-20" x-data="{ activeTab: 'my' }">

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

    {{-- TAB 1: EKSKUL SAYA --}}
    <div x-show="activeTab === 'my'" class="space-y-3" x-transition.duration.200ms>
        @forelse($myMemberships as $membership)
        @php $ekskul = $membership->extracurricular; @endphp
        <div class="bg-white p-4.5 rounded-3xl border-2 border-indigo-100 shadow-xs space-y-3">
            <div class="flex items-start gap-3">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-2xl shadow-2xs flex-shrink-0">
                    {{ $ekskul->display_icon }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-1 mb-1.5">
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold text-white bg-gradient-to-r {{ $membership->role_badge_color }} shadow-2xs">
                            {{ $membership->role_label }}
                        </span>
                        @if($membership->section)
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-purple-100 text-purple-900 border border-purple-200">
                            🎺 {{ $membership->section }}
                        </span>
                        @endif
                    </div>
                    <h3 class="font-black text-slate-900 text-xs leading-snug">{{ $ekskul->name }}</h3>
                </div>
            </div>

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
                    <span class="text-slate-500 font-bold">👨‍🏫 Pembina:</span>
                    <span class="font-black text-slate-900 truncate max-w-[150px]">{{ $ekskul->manager_name ?: ($ekskul->advisor_name ?: 'PKS Kesiswaan') }}</span>
                </div>
            </div>

            <div class="pt-1 flex items-center justify-between border-t border-slate-100">
                <span class="text-xs font-black text-indigo-700 flex items-center gap-1">
                    <i class="fa-solid fa-star text-amber-500"></i> +{{ $membership->points_awarded }} Poin Reputasi
                </span>
                @if($ekskul->forum_group_id)
                <a href="{{ route('mobile.space.index') }}" class="px-3.5 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-black shadow-xs active:scale-95 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-comments"></i>
                    <span>Space</span>
                </a>
                @endif
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

            <div class="pt-1 border-t border-slate-100">
                @if($isJoined)
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl text-xs font-black flex items-center gap-1.5">
                        <i class="fa-solid fa-check-circle"></i> Sudah Terdaftar
                    </span>
                    @if($ekskul->forum_group_id)
                    <a href="{{ route('mobile.space.index') }}" class="text-xs font-black text-purple-700 hover:text-purple-900 flex items-center gap-1">
                        <span>Buka Space</span> &rarr;
                    </a>
                    @endif
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

</div>
@endsection
