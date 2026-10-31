{{-- ═══════════════════════════════════════════════════════════════════════════
     PARTIAL: UNIFIED POST, ACTIVITY REPORT, MULTI-PHOTO COLLAGE & LIVE COMPOSER
     Digunakan di Beranda (circle/index.blade.php) & Dinding Profil (circle/profile.blade.php)
═══════════════════════════════════════════════════════════════════════════ --}}
@php
    $composerUser = Auth::user();
@endphp

@if($composerUser)
<div id="quick-post-box"
     class="bg-white rounded-3xl border-2 border-pink-200 p-5 sm:p-6 shadow-xs relative overflow-hidden transition-all duration-300 mb-6"
     x-data="circlePostComposer()">

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
        <input type="hidden" name="media_layout" :value="mediaLayout">
        <input type="hidden" name="is_live" :value="postType === 'live' ? 1 : 0">
        <input type="hidden" name="mood_emoji" :value="selectedMood">
        <input type="hidden" name="mood_text" :value="moodText">
        <input type="hidden" name="latitude" :value="latitude">
        <input type="hidden" name="longitude" :value="longitude">

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

        {{-- ── 4. INPUT JUDUL UTAMA (Untuk Laporan, LIVE, Karya, atau Galeri) ── --}}
        <div x-show="tabCat === 'portfolio' || tabCat === 'gallery' || postType === 'report' || postType === 'live' || showTitleInput"
             x-collapse x-transition class="mb-2.5">
            <input type="text" name="title" x-model="postTitle"
                   :placeholder="postType === 'report' ? 'Judul Berita / Laporan Kegiatan (Contoh: Tim SMK Pembda Raih Juara 1 LKS)...' : (postType === 'live' ? 'Judul Siaran Langsung (Contoh: LIVE Babak Final Futsal Antar Kelas)...' : (tabCat === 'gallery' ? 'Judul Media / Foto / Video / Lagu...' : 'Judul Karya / Berita Utama...'))"
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

        {{-- ── 10. PANDUAN PROSES UPLOAD REAL-TIME (PROGRESS BAR 0% - 100% SAMPAI TAMPIL) ── --}}
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
    window.circlePostComposer = function () {
        const nowLocal = new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000).toISOString().slice(0, 16);
        return {
            isSubmitting: false,
            uploadProgress: 0,
            uploadStageText: '',
            uploadedBytesText: '',
            uploadError: '',
            uploadSuccess: false,

            tabCat: 'wall',
            postType: 'status',
            mediaLayout: 'bottom',
            showTitleInput: false,
            showEventPanel: false,
            showMoodPanel: false,

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
                if (this.postType === 'live') return 'Siarkan langsung kegiatan dari lokasi beserta GPS & foto real-time!';
                if (this.postType === 'report') return 'Laporkan kegiatan lomba/acara lengkap dengan waktu, titik GPS & kolase foto!';
                if (this.tabCat === 'gallery') return 'Pajang foto, video, atau lagu di Galeri Pribadi & siarkan ke Beranda!';
                if (this.tabCat === 'portfolio') return 'Pamerkan karya inovasi, tugas akhir, atau proyek terbaikmu!';
                return 'Mau berbagi cerita, laporan kegiatan, atau karya apa hari ini?';
            },

            modeBadgeLabel() {
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
