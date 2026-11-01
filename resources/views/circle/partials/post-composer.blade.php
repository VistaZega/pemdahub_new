{{-- ═══════════════════════════════════════════════════════════════════════════
     PARTIAL: UNIFIED POST, ACTIVITY REPORT, MULTI-PHOTO COLLAGE & LIVE COMPOSER
     Digunakan di Beranda (circle/index.blade.php) & Dinding Profil (circle/profile.blade.php)
═══════════════════════════════════════════════════════════════════════════ --}}
@php
    $composerUser = Auth::user();
    $composerCommunities = isset($myCommunities) ? $myCommunities : ($composerUser ? $composerUser->circleCommunities()->get() : collect());
    $defaultCommunityId = isset($activeCommunity) && $activeCommunity ? $activeCommunity->id : '';
    $academicWorksList = isset($userAcademicWorks) ? $userAcademicWorks : collect();
@endphp

@if($composerUser)
<div id="quick-post-box"
     class="bg-white rounded-3xl border-2 border-pink-200 p-5 sm:p-6 shadow-xs relative overflow-hidden transition-all duration-300 mb-6"
     x-data="circlePostComposer('{{ $defaultCommunityId }}')">

    {{-- Header Sapaan Penulis --}}
    <div class="flex items-center justify-between gap-3 mb-3.5 flex-wrap">
        <div class="flex items-center gap-3">
            <img src="{{ $composerUser->avatar_url ?? asset('images/default-avatar.svg') }}"
                 alt="{{ $composerUser->name }}"
                 class="w-11 h-11 rounded-2xl object-cover ring-2 ring-pink-100">
            <div>
                <h4 class="text-sm font-black text-slate-950 flex items-center gap-1.5">
                    <span>Hai {{ explode(' ', $composerUser->name)[0] }}!</span>
                    <span x-text="selectedMood || '✨'"></span>
                </h4>
                <span class="text-xs font-bold text-slate-600" x-text="modeSubtitle()"></span>
            </div>
        </div>

        {{-- Indikator Mode Aktif --}}
        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border shadow-2xs"
              :class="{
                  'bg-rose-600 text-white border-rose-700 animate-pulse': postType === 'live',
                  'bg-sky-100 text-sky-900 border-sky-300': postType === 'report',
                  'bg-amber-100 text-amber-900 border-amber-300': tabCat === 'portfolio',
                  'bg-purple-100 text-purple-900 border-purple-300': tabCat === 'gallery',
                  'bg-[#faedcd] text-[#7f5539] border-[#d4a373]': tabCat === 'wall' && postType === 'status',
                  'bg-pink-100 text-pink-900 border-pink-300': postType === 'story'
              }"
              x-text="modeBadgeLabel()"></span>
    </div>

    <form x-ref="composerForm"
          action="{{ route('circle.post.store') }}"
          method="POST"
          enctype="multipart/form-data"
          @submit.prevent="submitWithProgress($event)">
        @csrf
        <input type="hidden" name="tab_category" :value="tabCat">
        <input type="hidden" name="post_type" :value="postType">
        <input type="hidden" name="interactive_type" :value="interactiveType">
        <input type="hidden" name="media_layout" :value="mediaLayout">
        <input type="hidden" name="is_live" :value="postType === 'live' ? 1 : 0">
        <input type="hidden" name="mood_emoji" :value="selectedMood">
        <input type="hidden" name="mood_text" :value="moodText">
        <input type="hidden" name="latitude" :value="latitude">
        <input type="hidden" name="longitude" :value="longitude">

        {{-- ── 0. PILIHAN TARGET GRUP / KOMUNITAS (Kelas, Sanggar, Ekskul, Rumpun SMK, Angkatan, Alumni) ── --}}
        <div class="mb-3.5 p-2.5 sm:px-3.5 rounded-2xl bg-gradient-to-r from-[#faf5ff] via-[#fff8f1] to-[#f0f9ff] border-2 border-purple-200/80 flex items-center justify-between gap-2 flex-wrap">
            <div class="flex items-center gap-2 text-xs font-black text-slate-900">
                <span class="w-6 h-6 rounded-lg bg-purple-600 text-white flex items-center justify-center text-[11px] shrink-0">🎯</span>
                <span>Tujuan Publikasi:</span>
            </div>
            <div class="flex-1 min-w-[210px]">
                <select name="community_id" x-model="selectedCommunityId"
                        class="w-full px-3 py-1.5 rounded-xl bg-white border-2 border-purple-200 text-xs font-black text-slate-950 focus:outline-none focus:border-purple-500 cursor-pointer">
                    <option value="">🌐 Panggung Utama (Publik Seluruh Warga Pembda)</option>
                    @if(isset($activeCommunity) && $activeCommunity && !$composerCommunities->contains('id', $activeCommunity->id))
                        <option value="{{ $activeCommunity->id }}">
                            {{ $activeCommunity->display_icon }} [{{ $activeCommunity->type_info['label'] }}] {{ $activeCommunity->name }}
                        </option>
                    @endif
                    @foreach($composerCommunities as $comm)
                        <option value="{{ $comm->id }}">
                            {{ $comm->display_icon }} [{{ $comm->type_info['label'] }}] {{ $comm->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- ── 1. PILIHAN MODE POSTINGAN (Nongki, Laporan Kegiatan, LIVE, Kilas, Karya, Galeri) ── --}}
        <div class="flex items-center gap-2 mb-3.5 overflow-x-auto pb-1.5 custom-scrollbar">
            {{-- 1. Nongki --}}
            <button type="button"
                    @click="setMode('wall', 'status')"
                    :class="tabCat === 'wall' && postType === 'status' ? 'bg-[#faedcd] text-slate-950 border-[#d4a373] ring-2 ring-[#d4a373]/40 shadow-xs' : 'bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200/70'"
                    class="px-3.5 py-2 rounded-2xl text-xs font-black border-2 transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                <i class="fas fa-mug-hot text-[#7f5539]"></i>
                <span>Nongki</span>
            </button>

            {{-- 2. Laporan Kegiatan / Lomba (BARU - Terhubung GPS & Waktu) --}}
            <button type="button"
                    @click="setMode('wall', 'report')"
                    :class="postType === 'report' ? 'bg-sky-100 text-slate-950 border-sky-400 ring-2 ring-sky-200 shadow-xs' : 'bg-slate-100 text-slate-700 border-slate-200 hover:bg-sky-50'"
                    class="px-3.5 py-2 rounded-2xl text-xs font-black border-2 transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                <i class="fas fa-clipboard-list text-sky-600"></i>
                <span>Laporan Kegiatan</span>
            </button>

            {{-- 3. Liputan LIVE (BARU - Siaran Langsung Lapangan) --}}
            <button type="button"
                    @click="setMode('wall', 'live')"
                    :class="postType === 'live' ? 'bg-rose-600 text-white border-rose-700 ring-2 ring-rose-300 shadow-sm' : 'bg-rose-50 text-rose-800 border-rose-200 hover:bg-rose-100'"
                    class="px-3.5 py-2 rounded-2xl text-xs font-black border-2 transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                <span class="w-2 h-2 rounded-full bg-current animate-ping"></span>
                <span>🔴 Liputan LIVE</span>
            </button>

            {{-- 4. Karya Portofolio --}}
            <button type="button"
                    @click="setMode('portfolio', 'project')"
                    :class="tabCat === 'portfolio' && postType === 'project' ? 'bg-amber-100 text-slate-950 border-amber-400 ring-2 ring-amber-200 shadow-xs' : 'bg-slate-100 text-slate-700 border-slate-200 hover:bg-amber-50'"
                    class="px-3.5 py-2 rounded-2xl text-xs font-black border-2 transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                <i class="fas fa-trophy text-amber-700"></i>
                <span>Karya</span>
            </button>

            {{-- 5. Galeri Media --}}
            <button type="button"
                    @click="setMode('gallery', 'media')"
                    :class="tabCat === 'gallery' ? 'bg-purple-100 text-slate-950 border-purple-400 ring-2 ring-purple-200 shadow-xs' : 'bg-slate-100 text-slate-700 border-slate-200 hover:bg-purple-50'"
                    class="px-3.5 py-2 rounded-2xl text-xs font-black border-2 transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                <i class="fas fa-photo-film text-purple-600"></i>
                <span>Galeri</span>
            </button>

            {{-- 6. Kilas 24 Jam --}}
            <button type="button"
                    id="btn-select-story"
                    @click="setMode('wall', 'story')"
                    :class="postType === 'story' ? 'bg-pink-100 text-slate-950 border-pink-400 ring-2 ring-pink-200 shadow-xs' : 'bg-slate-100 text-slate-700 border-slate-200 hover:bg-pink-50'"
                    class="px-3.5 py-2 rounded-2xl text-xs font-black border-2 transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                <i class="fas fa-bolt text-pink-600"></i>
                <span>Kilas 24J</span>
            </button>
        </div>

        {{-- ── 1.5 WAHANA INISIATIF KREATIF SISWA (TEKA-TEKI, BATTLE 1v1, DUET COLLABS, BALAS PANTUN) ── --}}
        <div class="mb-3.5 p-3 rounded-2xl bg-gradient-to-r from-violet-100/90 via-fuchsia-50 to-amber-50 border-2 border-violet-300 shadow-2xs">
            <div class="flex items-center justify-between gap-2 mb-2 flex-wrap">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-gradient-to-br from-violet-600 to-pink-600 text-white flex items-center justify-center text-xs font-black shrink-0">✨</span>
                    <span class="text-xs font-black text-slate-900 tracking-tight">Wahana Inisiatif Kreatif Siswa:</span>
                </div>
                <span class="text-[10px] font-black text-violet-900 bg-white/80 px-2.5 py-0.5 rounded-full border border-violet-200">
                    ⚡ Bikin interaksi seru & raih poin apresiasi!
                </span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                {{-- Teka-Teki --}}
                <button type="button"
                        @click="setInteractive('riddle')"
                        :class="interactiveType === 'riddle' ? 'bg-indigo-600 text-white border-indigo-700 ring-2 ring-indigo-300 shadow-xs' : 'bg-white hover:bg-indigo-50 text-slate-900 border-indigo-200'"
                        class="p-2 rounded-xl text-xs font-black border-2 transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <span>🧩</span>
                    <span>Teka-Teki</span>
                </button>

                {{-- Battle 1v1 --}}
                <button type="button"
                        @click="setInteractive('battle')"
                        :class="interactiveType === 'battle' ? 'bg-rose-600 text-white border-rose-700 ring-2 ring-rose-300 shadow-xs' : 'bg-white hover:bg-rose-50 text-slate-900 border-rose-200'"
                        class="p-2 rounded-xl text-xs font-black border-2 transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <span>⚔️</span>
                    <span>Battle 1v1</span>
                </button>

                {{-- Duet Collabs --}}
                <button type="button"
                        @click="setInteractive('collab')"
                        :class="interactiveType === 'collab' ? 'bg-purple-600 text-white border-purple-700 ring-2 ring-purple-300 shadow-xs' : 'bg-white hover:bg-purple-50 text-slate-900 border-purple-200'"
                        class="p-2 rounded-xl text-xs font-black border-2 transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <span>🤝</span>
                    <span>Duet Collabs</span>
                </button>

                {{-- Balas Pantun --}}
                <button type="button"
                        @click="setInteractive('pantun')"
                        :class="interactiveType === 'pantun' ? 'bg-emerald-600 text-white border-emerald-700 ring-2 ring-emerald-300 shadow-xs' : 'bg-white hover:bg-emerald-50 text-slate-900 border-emerald-200'"
                        class="p-2 rounded-xl text-xs font-black border-2 transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <span>🪶</span>
                    <span>Balas Pantun</span>
                </button>
            </div>
        </div>

        {{-- PANEL PENGATURAN INTERAKTIF: 🧩 TEKA-TEKI --}}
        <div x-show="interactiveType === 'riddle'" x-collapse x-transition
             class="mb-3.5 p-4 rounded-2xl bg-indigo-50 border-2 border-indigo-300 space-y-3">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="text-base">🧩</span>
                    <span class="text-xs font-black text-indigo-950 uppercase tracking-wider">Pengaturan Teka-Teki & Misteri</span>
                </div>
                <span class="text-[10px] font-black text-indigo-700 bg-white px-2 py-0.5 rounded-full border border-indigo-200">
                    Hadiah +15 Poin untuk Penebak Pertama
                </span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                <div>
                    <label class="block text-[11px] font-black text-slate-900 mb-1">
                        🔒 Kunci Jawaban Benar <span class="text-rose-600">*</span>:
                    </label>
                    <input type="text" name="riddle_answer"
                           placeholder="Contoh: mikrometer sekrup / hanoi / c++"
                           class="w-full px-3 py-2 rounded-xl bg-white border border-indigo-300 text-xs font-black text-slate-950 focus:outline-none focus:border-indigo-600">
                    <span class="text-[10px] text-slate-500 font-bold mt-0.5 block">
                        Kunci jawaban disimpan terenkripsi dan dirahasiakan sistem sampai terpecahkan!
                    </span>
                </div>
                <div>
                    <label class="block text-[11px] font-black text-slate-900 mb-1">
                        💡 Petunjuk / Clue Rahasia <span class="text-slate-400 font-normal">(Opsional)</span>:
                    </label>
                    <input type="text" name="riddle_hint"
                           placeholder="Contoh: Alat ini ada di lab bengkel mesin..."
                           class="w-full px-3 py-2 rounded-xl bg-white border border-indigo-300 text-xs font-bold text-slate-950 focus:outline-none focus:border-indigo-600">
                    <span class="text-[10px] text-slate-500 font-bold mt-0.5 block">
                        Clue akan terlihat oleh teman-teman untuk membantu mereka berpikir.
                    </span>
                </div>
            </div>
        </div>

        {{-- PANEL PENGATURAN INTERAKTIF: ⚔️ BATTLE 1v1 --}}
        <div x-show="interactiveType === 'battle'" x-collapse x-transition
             class="mb-3.5 p-4 rounded-2xl bg-rose-50 border-2 border-rose-300 space-y-3">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="text-base">⚔️</span>
                    <span class="text-xs font-black text-rose-950 uppercase tracking-wider">Pengaturan Arena Battle 1 vs 1</span>
                </div>
                <span class="text-[10px] font-black text-rose-700 bg-white px-2 py-0.5 rounded-full border border-rose-200">
                    Juara dapat +25 Poin
                </span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                {{-- Opsi A --}}
                <div class="p-3 rounded-xl bg-white border border-amber-300 space-y-2">
                    <label class="block text-[11px] font-black text-amber-900">
                        ⭐ Judul Karya A (Sisi Kiri):
                    </label>
                    <input type="text" name="battle_a_title" placeholder="Contoh: Desain Minimalis A"
                           class="w-full px-3 py-1.5 rounded-lg bg-amber-50/50 border border-amber-200 text-xs font-black text-slate-950">
                    <label class="block text-[10px] font-black text-slate-700">Foto / Gambar Karya A:</label>
                    <input type="file" name="battle_a_image" accept="image/*" class="text-[11px] text-slate-600">
                </div>

                {{-- Opsi B --}}
                <div class="p-3 rounded-xl bg-white border border-rose-300 space-y-2">
                    <label class="block text-[11px] font-black text-rose-900">
                        ⭐ Judul Karya B (Sisi Kanan):
                    </label>
                    <input type="text" name="battle_b_title" placeholder="Contoh: Desain Futuristik B"
                           class="w-full px-3 py-1.5 rounded-lg bg-rose-50/50 border border-rose-200 text-xs font-black text-slate-950">
                    <label class="block text-[10px] font-black text-slate-700">Foto / Gambar Karya B:</label>
                    <input type="file" name="battle_b_image" accept="image/*" class="text-[11px] text-slate-600">
                </div>
            </div>
            <div class="flex items-center justify-between text-xs font-bold text-slate-700 pt-1">
                <span>⏱️ Batas Waktu Duel:</span>
                <select name="battle_duration" class="px-2.5 py-1 rounded-lg bg-white border border-rose-300 text-xs font-black">
                    <option value="24" selected>24 Jam (1 Hari)</option>
                    <option value="48">48 Jam (2 Hari)</option>
                    <option value="72">3 Hari</option>
                </select>
            </div>
        </div>

        {{-- PANEL PENGATURAN INTERAKTIF: 🤝 DUET COLLABS --}}
        <div x-show="interactiveType === 'collab'" x-collapse x-transition
             class="mb-3.5 p-4 rounded-2xl bg-purple-50 border-2 border-purple-300 space-y-3">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="text-base">🤝</span>
                    <span class="text-xs font-black text-purple-950 uppercase tracking-wider">Pengaturan Duet Kolaborasi</span>
                </div>
                <span class="text-[10px] font-black text-purple-700 bg-white px-2 py-0.5 rounded-full border border-purple-200">
                    💎 Poin Dibagi 50:50 Bersama Rekan
                </span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                <div>
                    <label class="block text-[11px] font-black text-slate-900 mb-1">
                        👤 Username Rekan Kolaborator:
                    </label>
                    <input type="text" name="collab_username"
                           placeholder="Contoh: andreas_zebua atau fathan"
                           class="w-full px-3 py-2 rounded-xl bg-white border border-purple-300 text-xs font-black text-slate-950 focus:outline-none focus:border-purple-600">
                </div>
                <div>
                    <label class="block text-[11px] font-black text-slate-900 mb-1">
                        🎯 Peran Rekan dalam Proyek:
                    </label>
                    <input type="text" name="collab_role"
                           placeholder="Contoh: Kreator Musik / Desain Visual / Coding"
                           class="w-full px-3 py-2 rounded-xl bg-white border border-purple-300 text-xs font-bold text-slate-950 focus:outline-none focus:border-purple-600">
                </div>
            </div>
        </div>

        {{-- PANEL PENGATURAN INTERAKTIF: 🪶 BALAS PANTUN --}}
        <div x-show="interactiveType === 'pantun'" x-collapse x-transition
             class="mb-3.5 p-4 rounded-2xl bg-emerald-50 border-2 border-emerald-300 space-y-3">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="text-base">🪶</span>
                    <span class="text-xs font-black text-emerald-950 uppercase tracking-wider">Pengaturan Arena Balas Pantun</span>
                </div>
                <span class="text-[10px] font-black text-emerald-700 bg-white px-2 py-0.5 rounded-full border border-emerald-200">
                    Hadiah +10 Poin Sastrawan
                </span>
            </div>
            <div>
                <label class="block text-[11px] font-black text-slate-900 mb-1">
                    📖 Tema Pantun:
                </label>
                <select name="pantun_theme" class="w-full px-3 py-2 rounded-xl bg-white border border-emerald-300 text-xs font-black text-slate-950 focus:outline-none focus:border-emerald-600">
                    <option value="Jenaka Sekolah">😄 Jenaka & Santai Sekolah</option>
                    <option value="Kejuruan & Teknologi">⚙️ Kejuruan, Sains & Teknologi</option>
                    <option value="Nasehat & Motivasi">🌟 Nasehat, Adab & Semangat Belajar</option>
                    <option value="Persahabatan">🤝 Persahabatan & Kebersamaan</option>
                </select>
                <span class="text-[10px] text-slate-500 font-bold mt-1 block">
                    Tuliskan 2 atau 4 bait pantun awalmu di kolom isi postingan di bawah. Teman-teman akan membalas rima di kolom komentar!
                </span>
            </div>
        </div>

        {{-- ── 2. PANEL KHUSUS 🔴 LIPUTAN LIVE (SIARAN LANGSUNG) ── --}}
        <div x-show="postType === 'live'" x-collapse x-transition
             class="mb-3.5 p-4 rounded-2xl bg-gradient-to-r from-rose-50 via-red-50 to-amber-50 border-2 border-rose-300 space-y-3">
            <div class="flex items-start justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full bg-rose-600 text-white text-[10px] font-black uppercase tracking-wider flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
                        <span>MODE SIARAN LANGSUNG</span>
                    </span>
                    <span class="text-xs font-black text-rose-950">Laporkan langsung dari lokasi acara!</span>
                </div>
            </div>
            <p class="text-[11px] font-bold text-slate-700 leading-relaxed">
                Postingan ini akan tampil paling atas dengan lencana <strong>🔴 SEDANG LIVE</strong>. Setelah terbit, Anda dapat terus menambahkan foto & perkembangan skor/acara menit-demi-menit secara real-time!
            </p>
            <div>
                <label class="block text-[11px] font-black text-slate-900 mb-1">
                    📺 Tautan Video Live Stream <span class="text-slate-500 font-normal">(Opsional — YouTube Live / TikTok Live / IG Live)</span>:
                </label>
                <input type="url" name="live_stream_url" x-model="liveStreamUrl"
                       placeholder="https://www.youtube.com/live/... atau kosongkan jika liputan foto & teks langsung"
                       class="w-full px-3.5 py-2 rounded-xl bg-white border border-rose-200 text-xs font-bold text-slate-950 focus:outline-none focus:border-rose-500">
            </div>
        </div>

        {{-- ── 3. PANEL LAPORAN KEGIATAN, WAKTU & LOKASI GPS TERINTEGRASI ── --}}
        <div x-show="showEventPanel || postType === 'report' || postType === 'live'" x-collapse x-transition
             class="mb-3.5 p-4 rounded-2xl bg-gradient-to-r from-sky-50 via-indigo-50/60 to-emerald-50 border-2 border-sky-200 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black uppercase tracking-wider text-sky-950 flex items-center gap-1.5">
                    <i class="fas fa-map-location-dot text-sky-600"></i>
                    <span>Detail Kegiatan, Waktu & Titik Lokasi GPS</span>
                </span>
                <button type="button"
                        x-show="postType !== 'report' && postType !== 'live'"
                        @click="showEventPanel = false"
                        class="text-[11px] font-black text-slate-500 hover:text-rose-600 cursor-pointer">
                    ✕ Sembunyikan
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                {{-- Nama Kegiatan / Lomba --}}
                <div>
                    <label class="block text-[11px] font-black text-slate-900 mb-1">
                        🏆 Nama Kegiatan / Lomba / Acara:
                    </label>
                    <input type="text" name="event_name" x-model="eventName"
                           placeholder="Contoh: Lomba LKS SMK Tingkat Provinsi / Futsal Cup"
                           class="w-full px-3.5 py-2 rounded-xl bg-white border border-sky-200 text-xs font-bold text-slate-950 focus:outline-none focus:border-sky-500">
                </div>

                {{-- Waktu & Tanggal Kejadian --}}
                <div>
                    <label class="block text-[11px] font-black text-slate-900 mb-1">
                        🗓️ Tanggal & Jam Pelaksanaan:
                    </label>
                    <input type="datetime-local" name="event_date" x-model="eventDate"
                           class="w-full px-3.5 py-2 rounded-xl bg-white border border-sky-200 text-xs font-bold text-slate-950 focus:outline-none focus:border-sky-500">
                </div>
            </div>

            {{-- Lokasi Tempat & Tombol Deteksi GPS Otomatis --}}
            <div>
                <div class="flex items-center justify-between gap-2 mb-1 flex-wrap">
                    <label class="text-[11px] font-black text-slate-900">
                        📍 Tempat / Lokasi Kegiatan (Terhubung GPS):
                    </label>
                    <button type="button"
                            @click="detectGpsLocation()"
                            :disabled="gpsLoading"
                            class="px-3 py-1 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-black transition cursor-pointer flex items-center gap-1.5 shadow-2xs">
                        <i class="fas" :class="gpsLoading ? 'fa-spinner fa-spin' : 'fa-location-crosshairs'"></i>
                        <span x-text="gpsLoading ? 'Mengunci Satelit GPS...' : '📍 Ambil Lokasi GPS Saya Sekarang'"></span>
                    </button>
                </div>

                <input type="text" name="location_tag" x-model="locationTag"
                       placeholder="Ketik nama tempat atau klik tombol 'Ambil Lokasi GPS Saya Sekarang'..."
                       class="w-full px-3.5 py-2 rounded-xl bg-white border border-sky-200 text-xs font-bold text-slate-950 focus:outline-none focus:border-sky-500">

                {{-- Status GPS Terkunci --}}
                <div x-show="latitude && longitude" x-transition
                     class="mt-2 p-2.5 rounded-xl bg-emerald-100/90 border border-emerald-300 flex items-center justify-between gap-2 flex-wrap text-[11px]">
                    <div class="flex items-center gap-2 font-black text-emerald-950">
                        <span>🛰️ Titik GPS Terkunci:</span>
                        <span class="font-mono bg-white px-2 py-0.5 rounded border border-emerald-200" x-text="latitude + ', ' + longitude"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a :href="'https://www.google.com/maps?q=' + latitude + ',' + longitude" target="_blank"
                           class="text-emerald-800 hover:underline font-black">
                            Lihat Peta ➔
                        </a>
                        <button type="button" @click="latitude = ''; longitude = ''; gpsStatus = '';"
                                class="text-rose-600 hover:text-rose-800 font-black cursor-pointer">
                            ✕ Reset GPS
                        </button>
                    </div>
                </div>

                <div x-show="gpsError" x-transition class="mt-1.5 text-[11px] font-bold text-rose-600" x-text="gpsError"></div>
            </div>
        </div>

        {{-- ── 4. INPUT JUDUL UTAMA (Untuk Laporan, LIVE, Karya, Galeri, atau Wahana Inisiatif) ── --}}
        <div x-show="tabCat === 'portfolio' || tabCat === 'gallery' || postType === 'report' || postType === 'live' || interactiveType !== 'none' || showTitleInput"
             x-collapse x-transition class="mb-2.5">
            <input type="text" name="title" x-model="postTitle"
                   :placeholder="interactiveType === 'riddle' ? 'Judul Teka-Teki (Contoh: Benda Apakah Aku? / Misteri Ruang Komputer)...' : (interactiveType === 'battle' ? 'Tema Battle (Contoh: Duel Desain Brosur / Fotografi Kampus)...' : (interactiveType === 'collab' ? 'Judul Karya Kolaborasi Duet...' : (interactiveType === 'pantun' ? 'Tema / Judul Pantun Pembuka...' : (postType === 'report' ? 'Judul Berita / Laporan Kegiatan (Contoh: Tim SMK Pembda Raih Juara 1 LKS)...' : (postType === 'live' ? 'Judul Siaran Langsung (Contoh: LIVE Babak Final Futsal Antar Kelas)...' : (tabCat === 'gallery' ? 'Judul Media / Foto / Video / Lagu...' : 'Judul Karya / Berita Utama...'))))))"
                   class="w-full px-4 py-2.5 rounded-2xl bg-amber-50/60 border-2 border-amber-200 text-sm font-black text-slate-950 placeholder:text-slate-400 focus:outline-none focus:border-amber-400">
        </div>

        {{-- ── 5. IDE SPONTAN CEPAT ── --}}
        <div class="mb-2.5 flex items-center gap-1.5 overflow-x-auto pb-1 custom-scrollbar">
            <button type="button" @click="applyQuickTemplate('report')"
                    class="px-2.5 py-1 rounded-xl bg-sky-50 hover:bg-sky-100 border border-sky-200 text-[11px] font-bold text-slate-900 shrink-0 cursor-pointer">
                🏆 Template Laporan Lomba
            </button>
            <button type="button" @click="applyQuickTemplate('live')"
                    class="px-2.5 py-1 rounded-xl bg-rose-50 hover:bg-rose-100 border border-rose-200 text-[11px] font-bold text-rose-900 shrink-0 cursor-pointer">
                🔴 Template Liputan Live
            </button>
            <button type="button" @click="setPrompt('Keringat anak bengkel/lab hari ini 🛠️ Karya siap dipamerkan!', '🛠️', 'Ngoprek')"
                    class="px-2.5 py-1 rounded-xl bg-orange-50 hover:bg-orange-100 border border-orange-200 text-[11px] font-bold text-slate-900 shrink-0 cursor-pointer">
                🛠️ Praktik Bengkel/Lab
            </button>
            <button type="button" @click="setPrompt('Lagi nongki santai di kantin bareng bestie ☕ Spill jajan kalian!', '☕', 'Santai')"
                    class="px-2.5 py-1 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-200 text-[11px] font-bold text-slate-900 shrink-0 cursor-pointer">
                ☕ Nongki Kantin
            </button>
        </div>

        {{-- ── 6. KOTAK TEKS BERITA / CERITA POSTINGAN ── --}}
        <textarea name="content" rows="3"
                  x-ref="contentInput"
                  x-model="postContent"
                  :placeholder="postType === 'report' ? 'Tuliskan laporan lengkap kegiatan: siapa yang terlibat, jalannya acara, dan hasil yang dicapai...' : (postType === 'live' ? 'Tuliskan pengantar liputan langsung dari lokasi acara saat ini...' : 'Tuliskan cerita, laporan kegiatan, curhat santai, atau penjelasan karyamu...')"
                  class="w-full p-4 rounded-2xl bg-slate-50 border-2 border-slate-200 text-sm font-bold text-slate-950 placeholder:text-slate-400 focus:outline-none focus:border-pink-300 resize-y mb-3"></textarea>

        {{-- ── 7. PANDUAN & MANAJEMEN PELETAKAN MULTI-FOTO / VIDEO / AUDIO ── --}}
        <div x-show="mediaItems.length > 0" x-collapse x-transition
             class="mb-4 p-4 rounded-3xl bg-gradient-to-br from-purple-50/90 via-white to-pink-50/80 border-2 border-purple-300 shadow-xs space-y-3.5">

            {{-- Baris Status Panduan Seleksi Berkas --}}
            <div class="flex items-center justify-between gap-2 flex-wrap pb-2.5 border-b border-purple-200">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-xs font-black shadow-2xs">
                        ✓
                    </span>
                    <div>
                        <div class="text-xs font-black text-slate-950">
                            <span x-text="mediaItems.length"></span> Berkas Media Terseleksi & Siap Diunggah
                            <span class="text-purple-700 font-bold" x-text="'(' + totalSizeFormatted() + ')'"></span>
                        </div>
                        <div class="text-[11px] font-bold text-slate-600" x-text="collageGuideText()"></div>
                    </div>
                </div>

                <div class="flex items-center gap-1.5">
                    <label class="px-3 py-1.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-[11px] font-black cursor-pointer transition flex items-center gap-1 shadow-2xs">
                        <i class="fas fa-plus text-[10px]"></i>
                        <span>Tambah Foto Lagi</span>
                        <input type="file" multiple accept="image/*,video/*,audio/*" @change="appendFiles($event)" class="hidden">
                    </label>
                    <button type="button" @click="clearAllMedia()"
                            class="px-2.5 py-1.5 rounded-xl bg-rose-100 hover:bg-rose-200 text-rose-800 text-[11px] font-black transition cursor-pointer">
                        ✕ Hapus Semua
                    </button>
                </div>
            </div>

            {{-- Pilihan Peletakan Foto terhadap Isi Berita (Media Layout) --}}
            <div class="p-3 rounded-2xl bg-white border border-purple-200">
                <label class="block text-[11px] font-black text-slate-900 mb-2 flex items-center gap-1.5">
                    <i class="fas fa-table-columns text-purple-600"></i>
                    <span>Pilih Peletakan Foto & Isi Berita:</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <button type="button"
                            @click="mediaLayout = 'top'"
                            :class="mediaLayout === 'top' ? 'bg-purple-100 border-purple-500 text-purple-950 font-black ring-2 ring-purple-200' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100 font-bold'"
                            class="p-2.5 rounded-xl border-2 text-left text-xs transition cursor-pointer flex items-center gap-2.5">
                        <span class="text-lg">📰</span>
                        <div>
                            <div class="leading-tight">Foto di Atas, Berita di Bawah</div>
                            <div class="text-[10px] text-slate-500 font-semibold">Gaya Headline Berita / Laporan</div>
                        </div>
                    </button>
                    <button type="button"
                            @click="mediaLayout = 'bottom'"
                            :class="mediaLayout === 'bottom' ? 'bg-purple-100 border-purple-500 text-purple-950 font-black ring-2 ring-purple-200' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100 font-bold'"
                            class="p-2.5 rounded-xl border-2 text-left text-xs transition cursor-pointer flex items-center gap-2.5">
                        <span class="text-lg">📝</span>
                        <div>
                            <div class="leading-tight">Berita di Atas, Kolase Foto di Bawah</div>
                            <div class="text-[10px] text-slate-500 font-semibold">Gaya Cerita & Dokumentasi</div>
                        </div>
                    </button>
                </div>
            </div>

            {{-- Grid Kartu Pratinjau Setiap Berkas (Atur Urutan #1, #2, #3 & Keterangan Foto) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                <template x-for="(item, idx) in mediaItems" :key="item.uid">
                    <div class="bg-white rounded-2xl border-2 overflow-hidden flex flex-col shadow-2xs transition"
                         :class="idx === 0 ? 'border-pink-400 ring-2 ring-pink-100' : 'border-slate-200'">

                        {{-- Visual Thumbnail --}}
                        <div class="relative h-36 bg-slate-950 flex items-center justify-center overflow-hidden">
                            <template x-if="item.type === 'image'">
                                <img :src="item.preview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="item.type === 'video'">
                                <video :src="item.preview" controls class="w-full h-full object-contain"></video>
                            </template>
                            <template x-if="item.type === 'audio'">
                                <div class="p-3 text-center text-white w-full">
                                    <div class="text-2xl mb-1">🎵</div>
                                    <div class="text-[10px] font-black text-amber-300 truncate px-2" x-text="item.name"></div>
                                    <audio :src="item.preview" controls class="mt-1 w-full scale-90"></audio>
                                </div>
                            </template>

                            {{-- Badge Nomor Urut Foto --}}
                            <span class="absolute top-2 left-2 px-2 py-0.5 rounded-lg text-[10px] font-black shadow-xs"
                                  :class="idx === 0 ? 'bg-pink-600 text-white' : 'bg-black/70 text-white'"
                                  x-text="idx === 0 ? '⭐ #1 Utama' : '#' + (idx + 1)"></span>

                            {{-- Tombol Geser Urutan & Hapus --}}
                            <div class="absolute top-2 right-2 flex items-center gap-1">
                                <button type="button" x-show="idx > 0" @click="moveMedia(idx, -1)"
                                        class="w-6 h-6 rounded-lg bg-black/70 hover:bg-purple-600 text-white flex items-center justify-center text-[10px] font-black cursor-pointer"
                                        title="Geser ke Kiri / Jadikan Lebih Awal">
                                    ◀
                                </button>
                                <button type="button" x-show="idx < mediaItems.length - 1" @click="moveMedia(idx, 1)"
                                        class="w-6 h-6 rounded-lg bg-black/70 hover:bg-purple-600 text-white flex items-center justify-center text-[10px] font-black cursor-pointer"
                                        title="Geser ke Kanan">
                                    ▶
                                </button>
                                <button type="button" @click="removeMedia(idx)"
                                        class="w-6 h-6 rounded-lg bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center text-[10px] font-black cursor-pointer"
                                        title="Hapus Berkas Ini">
                                    ✕
                                </button>
                            </div>

                            <span class="absolute bottom-1.5 right-2 px-1.5 py-0.5 rounded bg-black/60 text-white text-[9px] font-bold"
                                  x-text="item.sizeText"></span>
                        </div>

                        {{-- Input Keterangan per Foto --}}
                        <div class="p-2 bg-slate-50 border-t border-slate-100">
                            <input type="text" name="media_captions[]" x-model="item.caption"
                                   :placeholder="'Keterangan foto #' + (idx + 1) + ' (opsional)...'"
                                   class="w-full px-2.5 py-1.5 rounded-xl bg-white border border-slate-200 text-[11px] font-bold text-slate-900 focus:outline-none focus:border-purple-400">
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ── 8. PANEL POLLING INTERAKTIF ── --}}
        <div x-show="hasPoll" x-collapse x-transition class="mb-3.5 p-4 rounded-2xl bg-purple-50/70 border-2 border-purple-200 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black uppercase tracking-wider text-purple-950 flex items-center gap-1.5">
                    <span>📊</span>
                    <span>Polling / Jajak Pendapat</span>
                </span>
                <button type="button" @click="hasPoll = false" class="text-xs font-black text-rose-600 hover:text-rose-800 cursor-pointer">
                    ✕ Batal Polling
                </button>
            </div>
            <input type="text" name="poll_question" placeholder="Pertanyaan Polling..."
                   class="w-full px-3.5 py-2 rounded-xl bg-white border border-purple-200 text-xs font-bold text-slate-950 focus:outline-none focus:border-purple-400">
            <div class="space-y-2">
                <template x-for="(opt, idx) in pollOptions" :key="idx">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-purple-200 text-purple-900 flex items-center justify-center text-xs font-black shrink-0" x-text="idx + 1"></span>
                        <input type="text" name="poll_options[]" x-model="pollOptions[idx]" :placeholder="'Pilihan ' + (idx + 1) + '...'"
                               class="flex-1 px-3 py-1.5 rounded-xl bg-white border border-purple-200 text-xs font-bold text-slate-950 focus:outline-none focus:border-purple-400">
                        <button type="button" x-show="pollOptions.length > 2" @click="removePollOption(idx)"
                                class="w-7 h-7 rounded-xl bg-white hover:bg-rose-100 text-slate-400 hover:text-rose-600 flex items-center justify-center text-xs font-black cursor-pointer border border-slate-200">
                            ✕
                        </button>
                    </div>
                </template>
            </div>
            <button type="button" x-show="pollOptions.length < 4" @click="addPollOption()"
                    class="text-xs font-black text-purple-700 hover:text-purple-900 flex items-center gap-1.5 cursor-pointer">
                <i class="fas fa-plus-circle"></i>
                <span>Tambah Pilihan</span>
            </button>
        </div>

        {{-- ── 9. PANEL SUASANA HATI (MOOD) ── --}}
        <div x-show="showMoodPanel" x-collapse x-transition class="mb-3.5 p-3.5 rounded-2xl bg-amber-50/70 border-2 border-amber-200">
            <span class="block text-[11px] font-black text-slate-900 mb-1.5">Pilih Suasana Hati:</span>
            <div class="flex items-center gap-1.5 flex-wrap">
                @foreach(['🔥' => 'Semangat', '🏆' => 'Juara', '🚀' => 'Produktif', '💡' => 'Ide Baru', '🛠️' => 'Ngoprek', '☕' => 'Santai', '🎨' => 'Kreatif', '📚' => 'Belajar', '😎' => 'Keren', '✨' => 'Ceria'] as $emoji => $mLabel)
                    <button type="button"
                            @click="selectedMood = '{{ $emoji }}'; moodText = '{{ $mLabel }}'"
                            class="px-2.5 py-1 rounded-xl text-xs font-bold border transition cursor-pointer hover:scale-105"
                            :class="selectedMood === '{{ $emoji }}' ? 'bg-amber-200 border-amber-400 text-slate-950 font-black' : 'bg-white border-slate-200 text-slate-800'">
                        <span>{{ $emoji }}</span> <span>{{ $mLabel }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- ── 9B. PANEL REKAM SUARA LANGSUNG (VOICE NOTE BROWSER) ── --}}
        <div x-show="showVoicePanel" x-collapse x-transition class="mb-3.5 p-4 rounded-2xl bg-gradient-to-r from-rose-50 via-pink-50 to-purple-50 border-2 border-pink-300 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black uppercase tracking-wider text-pink-950 flex items-center gap-1.5">
                    <span>🎙️</span>
                    <span>Rekam Suara / Voice Note Langsung</span>
                </span>
                <button type="button" @click="showVoicePanel = false" class="text-xs font-black text-slate-500 hover:text-rose-600 cursor-pointer">
                    ✕ Tutup
                </button>
            </div>

            <div class="flex items-center justify-between gap-3 flex-wrap bg-white p-3 rounded-xl border border-pink-200">
                <div class="flex items-center gap-2.5">
                    <button type="button"
                            @click="toggleVoiceRecording()"
                            :class="isRecordingVoice ? 'bg-rose-600 text-white animate-pulse' : 'bg-pink-600 hover:bg-pink-700 text-white'"
                            class="px-4 py-2 rounded-xl text-xs font-black cursor-pointer transition flex items-center gap-2 shadow-xs">
                        <i class="fas" :class="isRecordingVoice ? 'fa-stop' : 'fa-microphone'"></i>
                        <span x-text="isRecordingVoice ? ('⏹️ Hentikan (' + voiceRecordSeconds + 'd)') : (voicePreviewUrl ? '🎙️ Rekam Ulang' : '🎙️ Mulai Rekam Suara')"></span>
                    </button>
                    <span x-show="isRecordingVoice" class="text-xs font-black text-rose-600">Sedang merekam suara Anda...</span>
                </div>

                <template x-if="voicePreviewUrl">
                    <div class="flex items-center gap-2 flex-1 min-w-[220px]">
                        <audio :src="voicePreviewUrl" controls class="h-9 flex-1"></audio>
                        <button type="button" @click="clearVoiceRecording()"
                                class="px-2.5 py-1.5 rounded-lg bg-rose-100 hover:bg-rose-200 text-rose-700 text-[11px] font-black cursor-pointer">
                            ✕ Hapus
                        </button>
                    </div>
                </template>
            </div>
            <div x-show="voiceError" class="text-[11px] font-bold text-rose-600" x-text="voiceError"></div>
            <input type="file" name="voice_note" x-ref="voiceNoteInput" accept="audio/*" class="hidden">
        </div>

        {{-- ── 9C. PANEL LAMPIRAN DOKUMEN / MODUL & REFERENSI KARYA AKADEMIK TERVERIFIKASI ── --}}
        <div x-show="showAttachmentPanel" x-collapse x-transition class="mb-3.5 p-4 rounded-2xl bg-gradient-to-r from-indigo-50/80 via-amber-50/60 to-emerald-50/70 border-2 border-indigo-200 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black uppercase tracking-wider text-indigo-950 flex items-center gap-1.5">
                    <span>📎</span>
                    <span>Lampiran Modul / Dokumen & Karya Akademik Terverifikasi</span>
                </span>
                <button type="button" @click="showAttachmentPanel = false" class="text-xs font-black text-slate-500 hover:text-rose-600 cursor-pointer">
                    ✕ Tutup
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                {{-- Lampirkan Berkas Dokumen / Modul / Kode --}}
                <div class="p-3 rounded-xl bg-white border border-indigo-200">
                    <label class="block text-[11px] font-black text-slate-900 mb-1.5">
                        📄 Lampirkan Dokumen / Modul (PDF, Word, Excel, PPT, ZIP):
                    </label>
                    <input type="file" name="attachment"
                           @change="attachmentFileName = $event.target.files[0] ? $event.target.files[0].name : ''"
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.txt,.ino,.py"
                           class="w-full text-xs font-bold text-slate-700 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-black file:bg-indigo-100 file:text-indigo-900 hover:file:bg-indigo-200 cursor-pointer">
                    <div x-show="attachmentFileName" class="mt-1.5 text-[11px] font-black text-emerald-700 flex items-center gap-1">
                        <i class="fas fa-circle-check"></i>
                        <span x-text="'Siap dilampirkan: ' + attachmentFileName"></span>
                    </div>
                </div>

                {{-- Kaitkan Karya Akademik Terverifikasi (Tugas LMS / Penelitian SMA / Project SMK) --}}
                <div class="p-3 rounded-xl bg-white border border-amber-200">
                    <label class="block text-[11px] font-black text-slate-900 mb-1.5">
                        🎓 Kaitkan Karya Akademik Terverifikasi (LMS / Tugas Akhir):
                    </label>
                    @if($academicWorksList->isNotEmpty())
                        <select name="reference_choice" x-model="selectedReference"
                                class="w-full px-3 py-1.5 rounded-xl bg-amber-50/70 border border-amber-300 text-xs font-black text-slate-950 focus:outline-none focus:border-amber-500">
                            <option value="">-- Tanpa Referensi Karya Akademik --</option>
                            @foreach($academicWorksList as $work)
                                <option value="{{ $work['ref_value'] }}">
                                    {{ $work['badge'] }}: {{ Str::limit($work['title'], 42) }}{{ $work['score'] ? ' (Nilai: ' . $work['score'] . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <div class="text-[11px] font-semibold text-slate-500 leading-snug">
                            Tugas LMS atau Project Akhir/Penelitian yang sudah kamu kumpulkan akan otomatis muncul di sini untuk dipamerkan dengan lencana Terverifikasi.
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div x-show="isSubmitting || uploadError || uploadSuccess" x-transition
             class="mb-4 p-4 rounded-2xl border-2"
             :class="uploadError ? 'bg-rose-50 border-rose-300' : (uploadSuccess ? 'bg-emerald-50 border-emerald-300' : 'bg-sky-50 border-sky-300')">

            {{-- Stepper Tahapan Proses --}}
            <div class="flex items-center justify-between text-[11px] font-black mb-2">
                <div class="flex items-center gap-2">
                    <template x-if="isSubmitting && !uploadSuccess">
                        <i class="fas fa-spinner fa-spin text-sky-600 text-sm"></i>
                    </template>
                    <template x-if="uploadSuccess">
                        <span class="text-emerald-600 text-base">✅</span>
                    </template>
                    <template x-if="uploadError">
                        <span class="text-rose-600 text-base">⚠️</span>
                    </template>
                    <span class="text-slate-950" x-text="uploadStageText"></span>
                </div>
                <span x-show="isSubmitting" class="text-sky-800 font-mono text-xs" x-text="uploadProgress + '%'"></span>
            </div>

            {{-- Progress Bar Visual --}}
            <div x-show="isSubmitting || uploadSuccess" class="w-full h-3 rounded-full bg-white border border-sky-200 overflow-hidden p-0.5 mb-2">
                <div class="h-full rounded-full transition-all duration-200"
                     :class="uploadSuccess ? 'bg-emerald-500' : 'bg-gradient-to-r from-pink-500 via-purple-500 to-sky-500'"
                     :style="'width: ' + uploadProgress + '%'"></div>
            </div>

            {{-- Detail Ukuran Terunggah --}}
            <div x-show="isSubmitting && uploadedBytesText" class="text-[10px] font-bold text-slate-600 flex items-center justify-between">
                <span x-text="uploadedBytesText"></span>
                <span>Mohon jangan tutup halaman sampai status 100% tampil...</span>
            </div>

            {{-- Pesan Error jika gagal --}}
            <div x-show="uploadError" class="text-xs font-bold text-rose-800 mt-1">
                <p x-text="uploadError"></p>
                <button type="button" @click="uploadError = ''" class="mt-2 px-3 py-1 rounded-lg bg-rose-600 text-white text-[11px] font-black cursor-pointer">
                    Mengerti & Coba Lagi
                </button>
            </div>
        </div>

        {{-- Input File Sebenarnya yang Disinkronkan via DataTransfer --}}
        <input type="file" name="media_files[]" x-ref="realFileInput" multiple accept="image/*,video/*,audio/*" class="hidden">

        {{-- ── 11. BOTTOM ACTION BAR ── --}}
        <div class="flex items-center justify-between gap-2 pt-2.5 border-t border-slate-100 flex-wrap">
            <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                {{-- Pilih Multi Foto / Video / Audio --}}
                <label class="px-3.5 py-2 rounded-2xl bg-pink-50 hover:bg-pink-100 border-2 border-pink-200 text-slate-950 text-xs font-black cursor-pointer transition flex items-center gap-1.5 hover:scale-105">
                    <i class="fas fa-photo-film text-pink-600 text-xs"></i>
                    <span>Foto / Video / Audio</span>
                    <span x-show="mediaItems.length > 0"
                          class="px-1.5 py-0.2 rounded-full bg-pink-600 text-white text-[10px]"
                          x-text="mediaItems.length"></span>
                    <input type="file" multiple accept="image/*,video/*,audio/*" @change="appendFiles($event)" class="hidden">
                </label>

                {{-- Tombol Lokasi GPS & Kegiatan --}}
                <button type="button" @click="showEventPanel = !showEventPanel"
                        :class="(showEventPanel || latitude || eventName) ? 'bg-sky-100 border-sky-400 text-sky-950 font-black' : 'bg-sky-50 hover:bg-sky-100 border-sky-200 text-slate-950 font-bold'"
                        class="px-3 py-2 rounded-2xl border-2 text-xs cursor-pointer transition flex items-center gap-1.5 hover:scale-105">
                    <i class="fas fa-location-dot text-sky-600"></i>
                    <span>Lokasi GPS & Waktu</span>
                </button>

                {{-- Tombol Polling --}}
                <button type="button" @click="hasPoll = !hasPoll"
                        :class="hasPoll ? 'bg-purple-100 border-purple-400 text-purple-950 font-black' : 'bg-purple-50 hover:bg-purple-100 border-purple-200 text-slate-950 font-bold'"
                        class="px-3 py-2 rounded-2xl border-2 text-xs cursor-pointer transition flex items-center gap-1.5 hover:scale-105">
                    <span>📊</span>
                    <span class="hidden sm:inline">Polling</span>
                </button>

                {{-- Tombol Mood --}}
                <button type="button" @click="showMoodPanel = !showMoodPanel"
                        class="px-3 py-2 rounded-2xl bg-amber-50 hover:bg-amber-100 border-2 border-amber-200 text-slate-950 text-xs font-black cursor-pointer transition flex items-center gap-1.5 hover:scale-105">
                    <span x-text="selectedMood || '✨'"></span>
                    <span class="hidden md:inline">Mood</span>
                </button>

                {{-- Tombol Voice Note (Rekam Suara) --}}
                <button type="button" @click="showVoicePanel = !showVoicePanel"
                        :class="(showVoicePanel || voicePreviewUrl || isRecordingVoice) ? 'bg-rose-100 border-rose-400 text-rose-950 font-black' : 'bg-rose-50 hover:bg-rose-100 border-rose-200 text-slate-950 font-bold'"
                        class="px-3 py-2 rounded-2xl border-2 text-xs cursor-pointer transition flex items-center gap-1.5 hover:scale-105">
                    <span>🎙️</span>
                    <span class="hidden sm:inline">Voice Note</span>
                </button>

                {{-- Tombol Modul / Dokumen & Karya LMS --}}
                <button type="button" @click="showAttachmentPanel = !showAttachmentPanel"
                        :class="(showAttachmentPanel || attachmentFileName || selectedReference) ? 'bg-indigo-100 border-indigo-400 text-indigo-950 font-black' : 'bg-indigo-50 hover:bg-indigo-100 border-indigo-200 text-slate-950 font-bold'"
                        class="px-3 py-2 rounded-2xl border-2 text-xs cursor-pointer transition flex items-center gap-1.5 hover:scale-105">
                    <span>📎</span>
                    <span class="hidden sm:inline">Modul / Karya LMS</span>
                </button>
            </div>

            {{-- Tombol Submit Utama --}}
            <button type="submit"
                    :disabled="isSubmitting"
                    class="px-5 py-2.5 rounded-2xl border-2 border-black transition-all transform hover:scale-105 active:scale-95 cursor-pointer shadow-md flex items-center gap-2"
                    :class="isSubmitting ? 'opacity-60 cursor-not-allowed scale-100' : ''"
                    style="background: linear-gradient(135deg, #ff007a 0%, #ff6b00 50%, #ffd600 100%) !important; color: #000000 !important;">
                <i x-show="!isSubmitting" class="fas fa-paper-plane text-xs text-black"></i>
                <i x-show="isSubmitting" class="fas fa-spinner fa-spin text-xs text-black"></i>
                <span class="font-black text-xs uppercase tracking-wider text-black"
                      x-text="isSubmitting ? ('MENGUNGGAH ' + uploadProgress + '%...') : (postType === 'live' ? '🔴 MULAI SIARAN LIVE!' : 'TERBITKAN KE CIRCLE 🚀')"></span>
            </button>
        </div>
    </form>
</div>

<script>
if (typeof window.circlePostComposer !== 'function') {
    window.circlePostComposer = function (initialCommunityId = '') {
        const nowLocal = new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000).toISOString().slice(0, 16);
        return {
            isSubmitting: false,
            uploadProgress: 0,
            uploadStageText: '',
            uploadedBytesText: '',
            uploadError: '',
            uploadSuccess: false,

            selectedCommunityId: initialCommunityId || '',
            tabCat: 'wall',
            postType: 'status',
            mediaLayout: 'bottom',
            showTitleInput: false,
            showEventPanel: false,
            showMoodPanel: false,
            showVoicePanel: false,
            showAttachmentPanel: false,

            attachmentFileName: '',
            selectedReference: '',

            isRecordingVoice: false,
            voiceRecorder: null,
            voiceChunks: [],
            voicePreviewUrl: '',
            voiceRecordSeconds: 0,
            voiceTimer: null,
            voiceError: '',

            postTitle: '',
            postContent: '',
            selectedMood: '✨',
            moodText: 'Ceria',

            eventName: '',
            eventDate: nowLocal,
            locationTag: '',
            latitude: '',
            longitude: '',
            gpsLoading: false,
            gpsError: '',
            liveStreamUrl: '',

            mediaItems: [],
            hasPoll: false,
            pollOptions: ['', ''],
            interactiveType: 'none',

            setInteractive(type) {
                if (this.interactiveType === type) {
                    this.interactiveType = 'none';
                    return;
                }
                this.interactiveType = type;
                if (type === 'riddle') {
                    this.selectedMood = '🧩';
                    this.moodText = 'Misteri';
                    if (!this.postTitle) this.postTitle = 'Teka-Teki: Benda Apakah Aku?';
                    if (!this.postContent) this.postContent = "Aku selalu dicari saat jam praktikum tiba...\nSiapakah aku? Coba pecahkan misterinya di bawah!";
                } else if (type === 'battle') {
                    this.selectedMood = '⚔️';
                    this.moodText = 'Duel';
                    if (!this.postTitle) this.postTitle = 'Adu Karya: Mana Pilihan Terbaikmu?';
                    if (!this.postContent) this.postContent = "Bantu kami menentukan mana yang paling keren! Berikan suaramu pada bilah voting di bawah ini 👇";
                } else if (type === 'collab') {
                    this.selectedMood = '🤝';
                    this.moodText = 'Duet';
                    if (!this.postTitle) this.postTitle = 'Proyek Kolaborasi Duet';
                    if (!this.postContent) this.postContent = "Hasil kerja sama tim kami untuk tugas kejuruan/ekskul! Bagaimana tanggapan teman-teman?";
                } else if (type === 'pantun') {
                    this.selectedMood = '🪶';
                    this.moodText = 'Puitis';
                    if (!this.postTitle) this.postTitle = 'Arena Berbalas Pantun';
                    if (!this.postContent) this.postContent = "Jalan-jalan ke kota Medan,\nSinggah sebentar membeli duku.\nWahai kawan kawan sepadan,\nSiapakah yang berani membalas pantunku?";
                }
            },

            setMode(tab, type) {
                this.tabCat = tab;
                this.postType = type;
                if (type === 'report' || type === 'live') {
                    this.showEventPanel = true;
                    this.mediaLayout = 'top';
                    if (!this.eventDate) {
                        this.eventDate = new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000).toISOString().slice(0, 16);
                    }
                }
            },

            modeSubtitle() {
                if (this.interactiveType === 'riddle') return 'Tantang kawan-kawanmu dengan teka-teki logika atau misteri kampus!';
                if (this.interactiveType === 'battle') return 'Mulai duel 1 lawan 1 dan biarkan warga sekolah memberikan voting!';
                if (this.interactiveType === 'collab') return 'Pamerkan proyek kolaborasi duet & panen poin apresiasi bersama!';
                if (this.interactiveType === 'pantun') return 'Lempar pantun pembuka & tantang kawan-kawan berbalas rima di komentar!';
                if (this.postType === 'live') return 'Siarkan langsung kegiatan dari lokasi beserta GPS & foto real-time!';
                if (this.postType === 'report') return 'Laporkan kegiatan lomba/acara lengkap dengan waktu, titik GPS & kolase foto!';
                if (this.tabCat === 'gallery') return 'Pajang foto, video, atau lagu di Galeri Pribadi & siarkan ke Beranda!';
                if (this.tabCat === 'portfolio') return 'Pamerkan karya inovasi, tugas akhir, atau proyek terbaikmu!';
                return 'Mau berbagi cerita, laporan kegiatan, atau karya apa hari ini?';
            },

            modeBadgeLabel() {
                if (this.interactiveType === 'riddle') return '🧩 Inisiatif Teka-Teki';
                if (this.interactiveType === 'battle') return '⚔️ Inisiatif Battle 1v1';
                if (this.interactiveType === 'collab') return '🤝 Inisiatif Duet Collabs';
                if (this.interactiveType === 'pantun') return '🪶 Inisiatif Balas Pantun';
                if (this.postType === 'live') return '🔴 Mode Liputan LIVE';
                if (this.postType === 'report') return '📋 Mode Laporan Kegiatan';
                if (this.tabCat === 'gallery') return '📸 Mode Galeri Pribadi';
                if (this.tabCat === 'portfolio') return '🏆 Mode Portofolio';
                if (this.postType === 'story') return '⚡ Mode Kilas 24 Jam';
                return '☕ Mode Nongki';
            },

            applyQuickTemplate(kind) {
                if (kind === 'report') {
                    this.setMode('wall', 'report');
                    if (!this.postTitle) this.postTitle = 'Laporan Kegiatan: ';
                    if (!this.postContent) {
                        this.postContent = "📍 Laporan Langsung Kegiatan:\n• Acara: \n• Peserta: \n• Hasil / Jalannya Kegiatan: \n\nDokumentasi terlampir pada kolase foto.";
                    }
                    this.selectedMood = '🏆';
                    this.moodText = 'Juara';
                } else if (kind === 'live') {
                    this.setMode('wall', 'live');
                    if (!this.postTitle) this.postTitle = '🔴 [LIVE] Liputan Langsung: ';
                    if (!this.postContent) {
                        this.postContent = "Sedang berlangsung saat ini dari lokasi kegiatan! Ikuti terus perkembangan terbaru pada linimasa di bawah ini.";
                    }
                    this.selectedMood = '🔥';
                    this.moodText = 'Semangat';
                }
            },

            setPrompt(text, emoji, label) {
                this.postContent = text;
                if (emoji) this.selectedMood = emoji;
                if (label) this.moodText = label;
                this.$nextTick(() => {
                    if (this.$refs.contentInput) this.$refs.contentInput.focus();
                });
            },

            detectGpsLocation() {
                if (!navigator.geolocation) {
                    this.gpsError = 'Browser Anda tidak mendukung fitur GPS Geolocation.';
                    return;
                }
                this.gpsLoading = true;
                this.gpsError = '';
                navigator.geolocation.getCurrentPosition(
                    async (pos) => {
                        this.latitude = pos.coords.latitude.toFixed(6);
                        this.longitude = pos.coords.longitude.toFixed(6);
                        try {
                            const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${this.latitude}&lon=${this.longitude}&zoom=18&addressdetails=1`, {
                                headers: { 'Accept-Language': 'id' }
                            });
                            if (res.ok) {
                                const data = await res.json();
                                if (data && data.display_name) {
                                    const parts = data.display_name.split(',').slice(0, 3).map(s => s.trim());
                                    this.locationTag = parts.join(', ');
                                }
                            }
                        } catch (e) {
                            if (!this.locationTag) {
                                this.locationTag = `Koordinat GPS (${this.latitude}, ${this.longitude})`;
                            }
                        } finally {
                            this.gpsLoading = false;
                        }
                    },
                    (err) => {
                        this.gpsLoading = false;
                        this.gpsError = 'Gagal mengakses GPS. Pastikan izin Lokasi (Location) diaktifkan pada browser Anda.';
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            },

            appendFiles(e) {
                const files = Array.from(e.target.files || []);
                if (!files.length) return;

                files.forEach((file) => {
                    if (file.size > 51200 * 1024) {
                        this.uploadError = `Berkas "${file.name}" melebihi batas maksimal 50MB.`;
                        return;
                    }
                    let type = 'image';
                    if (file.type.startsWith('video')) type = 'video';
                    else if (file.type.startsWith('audio')) type = 'audio';

                    const sizeMB = (file.size / (1024 * 1024));
                    const sizeText = sizeMB >= 1 ? sizeMB.toFixed(2) + ' MB' : (file.size / 1024).toFixed(0) + ' KB';

                    const item = {
                        uid: 'm_' + Date.now() + '_' + Math.random().toString(36).slice(2, 7),
                        file: file,
                        name: file.name,
                        size: file.size,
                        sizeText: sizeText,
                        type: type,
                        preview: URL.createObjectURL(file),
                        caption: ''
                    };
                    this.mediaItems.push(item);
                });

                this.syncFileInput();
                e.target.value = '';
            },

            moveMedia(index, dir) {
                const target = index + dir;
                if (target < 0 || target >= this.mediaItems.length) return;
                const temp = this.mediaItems[index];
                this.mediaItems[index] = this.mediaItems[target];
                this.mediaItems[target] = temp;
                this.mediaItems = [...this.mediaItems];
                this.syncFileInput();
            },

            removeMedia(index) {
                this.mediaItems.splice(index, 1);
                this.syncFileInput();
            },

            clearAllMedia() {
                this.mediaItems = [];
                this.syncFileInput();
            },

            syncFileInput() {
                if (!this.$refs.realFileInput) return;
                try {
                    const dt = new DataTransfer();
                    this.mediaItems.forEach(item => dt.items.add(item.file));
                    this.$refs.realFileInput.files = dt.files;
                } catch (e) {
                    console.warn('DataTransfer sync warning:', e);
                }
            },

            totalSizeFormatted() {
                const total = this.mediaItems.reduce((acc, it) => acc + it.size, 0);
                return (total / (1024 * 1024)).toFixed(2) + ' MB';
            },

            collageGuideText() {
                const n = this.mediaItems.length;
                if (n === 1) return 'Tata Letak Otomatis: 1 Media Hero Lebar Penuh.';
                if (n === 2) return 'Tata Letak Otomatis: Kolase 2 Kolom Sejajar Simetris.';
                if (n === 3) return 'Tata Letak Otomatis: Kolase Bento (Foto #1 Utama Besar + 2 Foto Pendamping).';
                return `Tata Letak Otomatis: Grid Dokumentasi 2x2 (${n} Foto) + Lightbox Layar Penuh.`;
            },

            addPollOption() {
                if (this.pollOptions.length < 4) this.pollOptions.push('');
            },

            removePollOption(idx) {
                if (this.pollOptions.length > 2) this.pollOptions.splice(idx, 1);
            },

            async toggleVoiceRecording() {
                this.voiceError = '';
                if (this.isRecordingVoice && this.voiceRecorder) {
                    this.voiceRecorder.stop();
                    this.isRecordingVoice = false;
                    if (this.voiceTimer) clearInterval(this.voiceTimer);
                    return;
                }

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    this.voiceError = 'Browser Anda tidak mendukung perekaman suara langsung.';
                    return;
                }

                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    this.voiceChunks = [];
                    this.voiceRecorder = new MediaRecorder(stream);

                    this.voiceRecorder.ondataavailable = (ev) => {
                        if (ev.data && ev.data.size > 0) {
                            this.voiceChunks.push(ev.data);
                        }
                    };

                    this.voiceRecorder.onstop = () => {
                        const blob = new Blob(this.voiceChunks, { type: 'audio/webm' });
                        this.voicePreviewUrl = URL.createObjectURL(blob);
                        const file = new File([blob], 'voice-note-' + Date.now() + '.webm', { type: 'audio/webm' });
                        if (this.$refs.voiceNoteInput) {
                            try {
                                const dt = new DataTransfer();
                                dt.items.add(file);
                                this.$refs.voiceNoteInput.files = dt.files;
                            } catch (err) {
                                console.warn('Voice note sync warning:', err);
                            }
                        }
                        stream.getTracks().forEach(t => t.stop());
                    };

                    this.voiceRecorder.start();
                    this.isRecordingVoice = true;
                    this.voiceRecordSeconds = 0;
                    this.voiceTimer = setInterval(() => {
                        this.voiceRecordSeconds++;
                        if (this.voiceRecordSeconds >= 180 && this.isRecordingVoice) {
                            this.toggleVoiceRecording();
                        }
                    }, 1000);
                } catch (err) {
                    this.voiceError = 'Gagal mengakses mikrofon. Pastikan izin Mikrofon diberikan pada browser.';
                }
            },

            clearVoiceRecording() {
                this.voicePreviewUrl = '';
                this.voiceChunks = [];
                this.voiceRecordSeconds = 0;
                if (this.$refs.voiceNoteInput) {
                    this.$refs.voiceNoteInput.value = '';
                }
            },

            submitWithProgress(e) {
                if (this.isSubmitting) return;
                this.syncFileInput();

                const form = this.$refs.composerForm;
                const formData = new FormData(form);

                this.isSubmitting = true;
                this.uploadProgress = 0;
                this.uploadError = '';
                this.uploadSuccess = false;
                this.uploadStageText = this.mediaItems.length > 0
                    ? `Mengunggah ${this.mediaItems.length} berkas media ke server...`
                    : 'Menerbitkan postingan ke PembdaCIRCLE...';

                const xhr = new XMLHttpRequest();
                xhr.open('POST', form.action, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json');

                xhr.upload.addEventListener('progress', (ev) => {
                    if (ev.lengthComputable) {
                        const pct = Math.round((ev.loaded / ev.total) * 100);
                        this.uploadProgress = pct;
                        const loadedMB = (ev.loaded / (1024 * 1024)).toFixed(2);
                        const totalMB = (ev.total / (1024 * 1024)).toFixed(2);
                        this.uploadedBytesText = `Terunggah ${loadedMB} MB dari ${totalMB} MB`;
                        if (pct >= 100) {
                            this.uploadStageText = 'Menyusun kolase & menyimpan postingan...';
                        }
                    }
                });

                xhr.onload = () => {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        this.uploadProgress = 100;
                        this.uploadSuccess = true;
                        this.uploadStageText = '✅ Berhasil diterbitkan! Memuat tampilan terbaru...';
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    } else {
                        this.isSubmitting = false;
                        let errMsg = 'Gagal mengunggah postingan. Pastikan ukuran berkas tidak melebihi batas dan koneksi stabil.';
                        try {
                            const res = JSON.parse(xhr.responseText);
                            if (res.message) errMsg = res.message;
                        } catch (_) {}
                        this.uploadError = errMsg;
                    }
                };

                xhr.onerror = () => {
                    this.isSubmitting = false;
                    this.uploadError = 'Koneksi jaringan terputus saat mengunggah. Silakan klik Terbitkan kembali.';
                };

                xhr.send(formData);
            }
        };
    };
}
</script>
@endif
