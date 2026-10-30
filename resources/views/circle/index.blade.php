@extends('circle.layout')

@section('title', 'Beranda PembdaCIRCLE — Wadah Kreasi, Nongki & Portofolio')

@section('og_meta')
    <meta property="og:title" content="PembdaCIRCLE — Wadah Kreasi, Nongki & Portofolio Siswa Pembda">
    <meta property="og:description" content="Panggung sosial dan talenta digital pertama untuk siswa dan guru Perguruan Pembda.">
    <meta property="og:image" content="{{ asset('images/icons/icon-512x512.png') }}">
    <meta property="og:url" content="{{ route('circle.feed') }}">
    <meta property="og:type" content="website">
@endsection

@section('content')
@php
    $storiesPayload = $activeStories->map(function ($st) {
        $firstMedia = $st->media->first();
        return [
            'id' => $st->id,
            'name' => $st->user->name,
            'nickname' => $st->user->circleProfile->nickname ?? explode(' ', $st->user->name)[0],
            'username' => $st->user->circleProfile->username ?? $st->user->username,
            'avatar' => $st->user->avatar_url ?? asset('images/default-avatar.svg'),
            'school' => $st->school?->name ?? ($st->user->school?->name ?? 'Perguruan Pembda'),
            'content' => $st->content,
            'mood_emoji' => $st->mood_emoji,
            'mood_text' => $st->mood_text,
            'time_ago' => $st->created_at->diffForHumans(),
            'media_type' => $firstMedia?->media_type,
            'media_url' => $firstMedia?->url,
            'likes_count' => $st->likes_count,
        ];
    })->values();
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-7"
     x-data="{
        stories: {{ Js::from($storiesPayload) }},
        storyOpen: false,
        activeIdx: 0,
        progress: 0,
        timer: null,
        openStory(idx) {
            this.activeIdx = idx;
            this.storyOpen = true;
            this.startTimer();
        },
        closeStory() {
            this.storyOpen = false;
            clearInterval(this.timer);
        },
        nextStory() {
            if (this.activeIdx < this.stories.length - 1) {
                this.activeIdx++;
                this.startTimer();
            } else {
                this.closeStory();
            }
        },
        prevStory() {
            if (this.activeIdx > 0) {
                this.activeIdx--;
                this.startTimer();
            }
        },
        startTimer() {
            clearInterval(this.timer);
            this.progress = 0;
            const step = 2;
            this.timer = setInterval(() => {
                this.progress += step;
                if (this.progress >= 100) {
                    this.nextStory();
                }
            }, 100);
        },
        currStory() {
            return this.stories[this.activeIdx] || {};
        }
     }">

    {{-- ═══════════════════════════════════════════
         KOLOM KIRI & TENGAH: FEED UTAMA (PASTEL SEGAR)
    ═══════════════════════════════════════════ --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- ── 1. Story Bar: ⚡ KILAS (STORY 24 JAM) ── --}}
        @if($activeStories->isNotEmpty() || Auth::check())
            <div class="bg-white rounded-3xl border-2 border-[#f1e8dc] p-4 shadow-xs">
                <div class="flex items-center justify-between mb-3 px-1">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-pink-100 text-pink-600 flex items-center justify-center text-xs">
                            <i class="fas fa-bolt"></i>
                        </span>
                        <span>Kilas</span>
                    </span>
                    <span class="text-[11px] font-bold text-pink-600 bg-pink-50 border border-pink-200 px-2.5 py-0.5 rounded-full">
                        {{ $activeStories->count() }} kilas aktif ✨
                    </span>
                </div>
                <div class="flex items-center gap-4 overflow-x-auto pb-2 custom-scrollbar">
                    @auth
                        {{-- Bulatan Heboh: Lepas Kilas Baru --}}
                        <button type="button" 
                                @click="document.getElementById('quick-post-box')?.scrollIntoView({behavior: 'smooth'}); setTimeout(() => { const btn = document.getElementById('btn-select-story'); if(btn) btn.click(); }, 300)"
                                class="flex flex-col items-center gap-1.5 shrink-0 group focus:outline-none cursor-pointer"
                                title="Unggah Kilas Baru">
                            <div class="relative w-16 h-16 rounded-full p-[2.5px] border-2 border-dashed border-pink-400 group-hover:scale-105 transition-transform duration-200">
                                <div class="w-full h-full rounded-full p-0.5 bg-white overflow-hidden">
                                    <img src="{{ Auth::user()->avatar_url ?? asset('images/default-avatar.svg') }}" class="w-full h-full rounded-full object-cover">
                                </div>
                                <span class="absolute bottom-0 right-0 w-5 h-5 rounded-full bg-pink-600 text-white border-2 border-white flex items-center justify-center text-[10px] font-black shadow-xs">
                                    +
                                </span>
                            </div>
                            <span class="text-[11px] font-black text-slate-950 group-hover:text-pink-600 max-w-[70px] truncate text-center">
                                + Kilas
                            </span>
                        </button>
                    @endauth

                    @foreach($activeStories as $story)
                        <button type="button" 
                                @click="openStory({{ $loop->index }})" 
                                class="flex flex-col items-center gap-1.5 shrink-0 group focus:outline-none cursor-pointer">
                            <div class="w-16 h-16 rounded-full p-[3px] pastel-story-ring group-hover:scale-105 transition-transform duration-200 shadow-xs">
                                <div class="w-full h-full rounded-full p-0.5 bg-white overflow-hidden">
                                    <img src="{{ $story->user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                         alt="{{ $story->user->name }}"
                                         class="w-full h-full rounded-full object-cover bg-pink-50">
                                </div>
                            </div>
                            <span class="text-[11px] font-extrabold text-slate-900 group-hover:text-pink-600 max-w-[70px] truncate text-center transition-colors">
                                {{ $story->user->circleProfile->nickname ?? explode(' ', $story->user->name)[0] }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── 2. RUANG EKSPRESI & UNJUK KARYA (KOTAK POSTING CEPAT LANGSUNG DI FEED) ── --}}
        @auth
            <div id="quick-post-box" 
                 class="bg-white rounded-3xl border-2 border-pink-200 p-5 sm:p-6 shadow-xs relative overflow-hidden transition-all duration-300"
                 x-data="{
                    postType: 'status',
                    tabCat: 'wall',
                    showExtras: false,
                    selectedMood: '✨',
                    moodText: 'Ceria',
                    mediaName: '',
                    mediaPreviewUrl: null,
                    isVideo: false,
                    postContent: '',
                    hasPoll: false,
                    pollOptions: ['', ''],
                    addPollOption() {
                        if (this.pollOptions.length < 4) {
                            this.pollOptions.push('');
                        }
                    },
                    removePollOption(idx) {
                        if (this.pollOptions.length > 2) {
                            this.pollOptions.splice(idx, 1);
                        }
                    },
                    setPrompt(text, emoji, label) {
                        this.postContent = text;
                        this.selectedMood = emoji;
                        this.moodText = label;
                        this.$nextTick(() => {
                            if (this.$refs.contentInput) {
                                this.$refs.contentInput.focus();
                            }
                        });
                    },
                    handleFileSelect(e) {
                        const file = e.target.files[0];
                        if (!file) return;
                        this.mediaName = file.name;
                        this.isVideo = file.type.startsWith('video');
                        const reader = new FileReader();
                        reader.onload = (ev) => {
                            this.mediaPreviewUrl = ev.target.result;
                        };
                        reader.readAsDataURL(file);
                    },
                    clearMedia() {
                        this.mediaName = '';
                        this.mediaPreviewUrl = null;
                        this.isVideo = false;
                        if (this.$refs.mediaInput) this.$refs.mediaInput.value = '';
                    }
                 }">
                
                <div class="absolute -right-8 -top-8 w-32 h-32 bg-pink-100/60 rounded-full blur-2xl pointer-events-none"></div>

                {{-- Header Pengguna --}}
                <div class="flex items-center justify-between gap-3 mb-4 relative z-10">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="relative shrink-0">
                            <img src="{{ Auth::user()->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                 alt="{{ Auth::user()->name }}"
                                 class="w-12 h-12 rounded-2xl object-cover ring-2 ring-pink-300 shadow-xs">
                            <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white shadow-2xs"></span>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h4 class="font-display font-black text-sm sm:text-base text-slate-950 truncate">
                                    Curhat, {{ explode(' ', Auth::user()->name)[0] }}! ✨
                                </h4>
                                @if(Auth::user()->school)
                                    <span class="hidden sm:inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-pink-100 text-pink-900 border border-pink-200">
                                        {{ Auth::user()->school->name }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs font-bold text-slate-700 truncate">
                                Cerita nongki, karya bengkel/lab, atau ide seru hari ini!
                            </p>
                        </div>
                    </div>
                    <span class="hidden md:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-black bg-amber-50 text-amber-900 border border-amber-200 shadow-2xs">
                        <span>Panggung</span> 🚀
                    </span>
                </div>

                {{-- Form Pengiriman --}}
                <form action="{{ route('circle.post.store') }}" method="POST" enctype="multipart/form-data" class="relative z-10">
                    @csrf
                    <input type="hidden" name="tab_category" :value="tabCat">
                    <input type="hidden" name="post_type" :value="postType">

                    {{-- 4 Pilihan Gelora Konten Pastel (1 Kata: Nongki, Kilas, Karya, Reels) --}}
                    <div class="flex items-center gap-2 mb-3.5 overflow-x-auto pb-1 custom-scrollbar">
                        {{-- 1. Nongki --}}
                        <button type="button" 
                                @click="tabCat = 'wall'; postType = 'status'"
                                :class="tabCat === 'wall' && postType === 'status' ? 'bg-[#faedcd] text-slate-950 border-[#d4a373] shadow-xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-[#faedcd]/40'"
                                class="px-3.5 py-2 rounded-2xl text-xs font-black border-2 transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                            <span class="w-5 h-5 rounded-lg bg-[#faedcd] text-[#7f5539] flex items-center justify-center text-[10px]">
                                <i class="fas fa-mug-hot"></i>
                            </span>
                            <span>Nongki</span>
                        </button>

                        {{-- 2. Kilas --}}
                        <button type="button" id="btn-select-story"
                                @click="tabCat = 'wall'; postType = 'story'"
                                :class="postType === 'story' ? 'bg-pink-100 text-slate-950 border-pink-300 shadow-xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-pink-50'"
                                class="px-3.5 py-2 rounded-2xl text-xs font-black border-2 transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                            <span class="w-5 h-5 rounded-lg bg-pink-200 text-pink-700 flex items-center justify-center text-[10px]">
                                <i class="fas fa-bolt"></i>
                            </span>
                            <span>Kilas</span>
                        </button>

                        {{-- 3. Karya --}}
                        <button type="button" 
                                @click="tabCat = 'portfolio'; postType = 'project'"
                                :class="tabCat === 'portfolio' && postType === 'project' ? 'bg-amber-100 text-slate-950 border-amber-300 shadow-xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-amber-50'"
                                class="px-3.5 py-2 rounded-2xl text-xs font-black border-2 transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                            <span class="w-5 h-5 rounded-lg bg-amber-200 text-amber-900 flex items-center justify-center text-[10px]">
                                <i class="fas fa-trophy"></i>
                            </span>
                            <span>Karya</span>
                        </button>

                        {{-- 4. Reels --}}
                        <button type="button" 
                                @click="tabCat = 'portfolio'; postType = 'reel'"
                                :class="postType === 'reel' ? 'bg-sky-100 text-slate-950 border-sky-300 shadow-xs' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-sky-50'"
                                class="px-3.5 py-2 rounded-2xl text-xs font-black border-2 transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                            <span class="w-5 h-5 rounded-lg bg-sky-200 text-sky-800 flex items-center justify-center text-[10px]">
                                <i class="fas fa-play"></i>
                            </span>
                            <span>Reels</span>
                        </button>
                    </div>

                    {{-- Judul Karya (Tampil Saat Memilih Karya atau Reels) --}}
                    <div x-show="tabCat === 'portfolio'" x-transition class="mb-3">
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-900 mb-1">
                            Judul Karya / Proyek <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="title" 
                               placeholder="Contoh: Rancang Bangun IoT Pengendali Suhu Mesin, atau Riset Biologi Alga Nias..." 
                               class="w-full px-4 py-2.5 rounded-2xl bg-amber-50/60 border-2 border-amber-200 text-xs sm:text-sm font-black text-slate-950 placeholder:text-slate-400 focus:outline-none focus:border-amber-400 shadow-2xs">
                    </div>

                    {{-- 💡 Pancingan Ide Cepat --}}
                    <div class="mb-3">
                        <div class="flex items-center justify-between mb-1.5 px-0.5">
                            <span class="text-[11px] font-black uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-[10px]">💡</span>
                                <span>Pancingan Ide:</span>
                            </span>
                        </div>
                        <div class="flex items-center gap-2 overflow-x-auto pb-1.5 custom-scrollbar">
                            <button type="button" 
                                    @click="setPrompt('Lagi nongki santai di kantin nunggu jam pelajaran bareng bestie ☕ Spill jajanan favorit kalian hari ini!', '☕', 'Nongki Santai')"
                                    class="px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-200 text-xs font-black text-slate-950 shrink-0 transition hover:scale-105 cursor-pointer">
                                ☕ Nongki Kantin
                            </button>
                            <button type="button" 
                                    @click="setPrompt('Keringat anak bengkel/lab hari ini 🛠️ Baut kencang, rangkaian aman, karya siap dipamerkan!', '🛠️', 'Keringat Bengkel')"
                                    class="px-3 py-1.5 rounded-xl bg-orange-50 hover:bg-orange-100 border border-orange-200 text-xs font-black text-slate-950 shrink-0 transition hover:scale-105 cursor-pointer">
                                🛠️ Suasana Bengkel/Lab
                            </button>
                            <button type="button" 
                                    @click="setPrompt('Lagu yang lagi nemenin ngerjain tugas malam ini 🎧🎶 Ada rekomendasi lagu asyik buat fokus?', '🎧', 'Nge-vibes Musik')"
                                    class="px-3 py-1.5 rounded-xl bg-pink-50 hover:bg-pink-100 border border-pink-200 text-xs font-black text-slate-950 shrink-0 transition hover:scale-105 cursor-pointer">
                                🎧 Rekomendasi Lagu
                            </button>
                            <button type="button" 
                                    @click="setPrompt('Pusing dikit nggak ngaruh 📚 Yang penting besok pas masuk kelas semua tugas sudah tuntas!', '📚', 'Semangat Belajar')"
                                    class="px-3 py-1.5 rounded-xl bg-sky-50 hover:bg-sky-100 border border-sky-200 text-xs font-black text-slate-950 shrink-0 transition hover:scale-105 cursor-pointer">
                                📚 Pusing Dikit Nggak Ngaruh
                            </button>
                            <button type="button" 
                                    @click="setPrompt('Tebak-tebakan logika buat bapak/ibu guru dan kawan-kawan 🥊 Siapa yang bisa jawab duluan di komentar?', '🥊', 'Tebak-Tebakan')"
                                    class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-xs font-black text-slate-950 shrink-0 transition hover:scale-105 cursor-pointer">
                                🥊 Tebak-Tebakan Logika
                            </button>
                        </div>
                    </div>

                    {{-- Textarea Isi Goresan --}}
                    <div class="mb-3">
                        <textarea name="content" rows="3" required
                                  x-ref="contentInput"
                                  x-model="postContent"
                                  :placeholder="postType === 'story' ? 'Tuliskan kilas cerita cepat yang bertahan 24 jam...' : (tabCat === 'portfolio' ? 'Uraikan latar belakang karya, alat praktikum yang digunakan, dan hasil uji kompetensimu...' : 'Tuliskan obrolan santai, curhat seru di sekolah, atau pesan inspiratif...')"
                                  class="w-full p-4 rounded-2xl bg-slate-50 border-2 border-slate-200 text-xs sm:text-sm font-bold text-slate-950 placeholder:text-slate-400 focus:outline-none focus:border-pink-300 resize-none shadow-2xs"></textarea>
                    </div>

                    {{-- Kotak Input Polling Interaktif (Muncul jika tombol Polling ditekan) --}}
                    <div x-show="hasPoll" x-collapse x-transition class="mb-3.5 p-4 rounded-2xl bg-purple-50/70 border-2 border-purple-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wider text-purple-950 flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded-lg bg-purple-200 text-purple-800 flex items-center justify-center text-[10px]">📊</span>
                                <span>Bikin Polling / Jajak Pendapat Warga Pembda</span>
                            </span>
                            <button type="button" @click="hasPoll = false" class="text-xs font-black text-rose-600 hover:text-rose-800 cursor-pointer">
                                ✕ Batal Polling
                            </button>
                        </div>

                        <div>
                            <label class="block text-[11px] font-black text-slate-900 mb-1">Pertanyaan Polling <span class="text-rose-500">*</span></label>
                            <input type="text" name="poll_question" placeholder="Contoh: Menu kantin terfavorit hari ini, atau Tim Praktikum vs Teori?" 
                                   class="w-full px-3.5 py-2 rounded-xl bg-white border border-purple-200 text-xs font-bold text-slate-950 focus:outline-none focus:border-purple-400">
                        </div>

                        <div>
                            <label class="block text-[11px] font-black text-slate-900 mb-1">Pilihan Jawaban (Minimal 2, Maksimal 4):</label>
                            <div class="space-y-2">
                                <template x-for="(opt, idx) in pollOptions" :key="idx">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-lg bg-purple-200 text-purple-900 flex items-center justify-center text-xs font-black shrink-0" x-text="idx + 1"></span>
                                        <input type="text" name="poll_options[]" x-model="pollOptions[idx]" :placeholder="'Pilihan ' + (idx + 1) + '...'"
                                               class="flex-1 px-3 py-1.5 rounded-xl bg-white border border-purple-200 text-xs font-bold text-slate-950 focus:outline-none focus:border-purple-400">
                                        <button type="button" x-show="pollOptions.length > 2" @click="removePollOption(idx)"
                                                class="w-7 h-7 rounded-xl bg-white hover:bg-rose-100 text-slate-400 hover:text-rose-600 flex items-center justify-center text-xs font-black transition cursor-pointer border border-slate-200">
                                            ✕
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <div class="mt-2.5" x-show="pollOptions.length < 4">
                                <button type="button" @click="addPollOption()" class="text-xs font-black text-purple-700 hover:text-purple-900 flex items-center gap-1.5 cursor-pointer">
                                    <i class="fas fa-plus-circle"></i>
                                    <span>Tambah Pilihan Lain</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Media Preview (Live jika ada file yang dipilih) --}}
                    <template x-if="mediaPreviewUrl">
                        <div class="mb-3 relative rounded-2xl overflow-hidden border-2 border-slate-200 max-h-56 bg-slate-950 flex items-center justify-center">
                            <template x-if="!isVideo">
                                <img :src="mediaPreviewUrl" class="max-h-56 w-full object-contain">
                            </template>
                            <template x-if="isVideo">
                                <video :src="mediaPreviewUrl" controls class="max-h-56 w-full object-contain"></video>
                            </template>
                            <button type="button" @click="clearMedia()" 
                                    class="absolute top-2 right-2 w-7 h-7 rounded-full bg-black/70 hover:bg-rose-600 text-white flex items-center justify-center text-xs font-black transition cursor-pointer"
                                    title="Hapus Lampiran Media">
                                ✕
                            </button>
                        </div>
                    </template>

                    {{-- Extra Options Toggle (Mood & Lokasi Bengkel/Lab) --}}
                    <div x-show="showExtras" x-collapse x-transition class="mb-3.5 p-3.5 rounded-2xl bg-slate-50 border-2 border-slate-200 space-y-3">
                        <div>
                            <span class="block text-[11px] font-black text-slate-900 mb-1.5">Getaran Suasana Hati:</span>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                @foreach(['🔥' => 'Semangat', '🚀' => 'Produktif', '💡' => 'Ide Baru', '🛠️' => 'Ngoprek', '☕' => 'Santai', '🎨' => 'Kreatif', '📚' => 'Belajar', '😎' => 'Keren', '✨' => 'Ceria', '🌸' => 'Damai'] as $emoji => $mLabel)
                                    <button type="button" 
                                            @click="selectedMood = '{{ $emoji }}'; moodText = '{{ $mLabel }}'"
                                            class="px-2.5 py-1 rounded-xl text-xs font-bold border transition cursor-pointer hover:scale-105"
                                            :class="selectedMood === '{{ $emoji }}' ? 'bg-amber-100 border-amber-300 text-slate-950 font-black' : 'bg-white border-slate-200 text-slate-800'">
                                        <span>{{ $emoji }}</span> <span>{{ $mLabel }}</span>
                                    </button>
                                @endforeach
                            </div>
                            <input type="hidden" name="mood_emoji" :value="selectedMood">
                            <input type="hidden" name="mood_text" :value="moodText">
                        </div>

                        <div>
                            <span class="block text-[11px] font-black text-slate-900 mb-1">Lokasi / Ruang Praktik:</span>
                            <input type="text" name="location_tag" 
                                   placeholder="Contoh: Bengkel TKR, Lab Komputer TKJ, Perpustakaan, Ruang OSIS..." 
                                   class="w-full px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-950 focus:outline-none focus:border-pink-300">
                        </div>
                    </div>

                    {{-- Bottom Action Bar: Upload File + Polling + Ekstra Mood + Tombol Morak Terbang --}}
                    <div class="flex items-center justify-between gap-3 pt-2 border-t border-slate-100 flex-wrap">
                        <div class="flex items-center gap-2 flex-wrap">
                            <label class="px-3.5 py-2 rounded-2xl bg-pink-50 hover:bg-pink-100 border-2 border-pink-200 text-slate-950 text-xs font-black cursor-pointer transition flex items-center gap-2 hover:scale-105">
                                <i class="fas fa-photo-film text-pink-600 text-xs"></i>
                                <span class="hidden sm:inline">Foto / Video</span>
                                <span class="sm:hidden">Media</span>
                                <input type="file" name="media" x-ref="mediaInput" @change="handleFileSelect($event)" accept="image/*,video/*" class="hidden">
                            </label>

                            <button type="button" @click="hasPoll = !hasPoll"
                                    :class="hasPoll ? 'bg-purple-100 border-purple-400 text-purple-950 font-black shadow-xs' : 'bg-purple-50 hover:bg-purple-100 border-purple-200 text-slate-950 font-bold'"
                                    class="px-3.5 py-2 rounded-2xl border-2 text-xs cursor-pointer transition flex items-center gap-2 hover:scale-105">
                                <span class="text-xs">📊</span>
                                <span class="hidden sm:inline">Polling Seru</span>
                                <span class="sm:hidden">Polling</span>
                            </button>

                            <button type="button" @click="showExtras = !showExtras"
                                    class="px-3.5 py-2 rounded-2xl bg-amber-50 hover:bg-amber-100 border-2 border-amber-200 text-slate-950 text-xs font-black cursor-pointer transition flex items-center gap-2 hover:scale-105">
                                <span x-text="selectedMood || '✨'"></span>
                                <span class="hidden sm:inline">Suasana & Lokasi</span>
                            </button>
                        </div>

                        <button type="submit" 
                                class="px-6 py-2.5 rounded-2xl border-2 border-black transition-all transform hover:scale-105 active:scale-95 cursor-pointer shadow-md flex items-center gap-2"
                                style="background: linear-gradient(135deg, #ff007a 0%, #ff6b00 50%, #ffd600 100%) !important; color: #000000 !important; border: 2.5px solid #000000 !important; box-shadow: 0 4px 14px rgba(255, 0, 122, 0.45) !important;">
                            <i class="fas fa-paper-plane text-xs text-black" style="color: #000000 !important;"></i>
                            <span style="color: #000000 !important; font-weight: 900 !important; font-size: 13px !important; letter-spacing: 0.05em !important; text-transform: uppercase !important;">
                                GASKEUN! 🚀
                            </span>
                        </button>
                    </div>

                </form>
            </div>
        @else
            <div class="bg-gradient-to-r from-pink-50 via-amber-50 to-sky-50 rounded-3xl border-2 border-pink-200 p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3.5 text-center sm:text-left">
                    <div class="w-12 h-12 rounded-2xl bg-pink-200 text-pink-700 flex items-center justify-center text-xl shrink-0">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div>
                        <h4 class="font-display font-black text-sm text-slate-950">Ingin Ikutan Curhat & Pamer Karya? ✨</h4>
                        <p class="text-xs font-semibold text-slate-700">Masuk dengan akun siswa atau guru Anda untuk mulai mengunggah portofolio digital dan momen seru.</p>
                    </div>
                </div>
                <a href="{{ route('login') }}" 
                   class="px-5 py-2.5 rounded-2xl text-xs font-black text-black border-2 border-black transition-all hover:scale-105 shrink-0 shadow-md"
                   style="background: linear-gradient(135deg, #ffd600 0%, #34d399 100%) !important; color: #000000 !important; border: 2px solid #000000 !important;">
                    <i class="fas fa-sign-in-alt mr-1"></i> Masuk Sekarang
                </a>
            </div>
        @endauth

        {{-- ── 2B. TANTANGAN HARI INI (DAILY VIRAL PROMPT) ── --}}
        @if(isset($todayChallenge))
            <div class="bg-gradient-to-r from-amber-100 via-pink-100 to-sky-100 rounded-3xl border-2 border-black p-4 sm:p-5 shadow-sm relative overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 relative z-10">
                    <div class="flex items-start sm:items-center gap-3">
                        <span class="w-12 h-12 rounded-2xl bg-white border-2 border-black flex items-center justify-center text-2xl shadow-xs shrink-0">
                            {{ $todayChallenge['emoji'] }}
                        </span>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-black text-yellow-300">
                                    🎯 TANTANGAN!
                                </span>
                                <span class="text-xs font-black text-slate-950">{{ $todayChallenge['title'] }}</span>
                            </div>
                            <p class="text-xs font-black text-slate-900 mt-1 leading-relaxed">
                                "{{ $todayChallenge['desc'] }}"
                            </p>
                        </div>
                    </div>
                    @auth
                        <button type="button" 
                                onclick="document.getElementById('quick-post-box')?.scrollIntoView({ behavior: 'smooth' }); const ta = document.querySelector('textarea[name=content]'); if(ta) { ta.value = '#TantanganPembda ({{ $todayChallenge['title'] }}): '; ta.focus(); ta.dispatchEvent(new Event('input')); }"
                                class="px-4 py-2 rounded-2xl border-2 border-black bg-white hover:bg-yellow-300 text-slate-950 font-black text-xs transition transform hover:scale-105 active:scale-95 shadow-xs shrink-0 flex items-center gap-1.5 cursor-pointer self-start sm:self-auto">
                            <span>Ikutan!</span> 🚀
                        </button>
                    @else
                        <a href="{{ route('login') }}" 
                            class="px-4 py-2 rounded-2xl border-2 border-black bg-white hover:bg-yellow-300 text-slate-950 font-black text-xs transition transform hover:scale-105 active:scale-95 shadow-xs shrink-0 flex items-center gap-1.5 cursor-pointer self-start sm:self-auto">
                            <span>Ikutan!</span> 🚀
                        </a>
                    @endauth
                </div>
            </div>
        @endif

        {{-- ── 3. Filter Tab Bar: Gelora Pastel (1 Kata: Semua, Karya, Nongki, Reels) ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white rounded-3xl border-2 border-[#f1e8dc] p-2.5 shadow-xs">
            {{-- Tabs Pastel Heboh --}}
            <div class="flex items-center gap-2 overflow-x-auto custom-scrollbar pb-1 sm:pb-0">
                {{-- 1. Semua --}}
                <a href="{{ route('circle.feed', array_merge(request()->query(), ['tab' => 'all'])) }}" 
                   class="px-4 py-2 rounded-2xl text-xs font-black transition-base shrink-0 flex items-center gap-2 {{ $tabFilter === 'all' ? 'bg-pink-100 text-slate-950 border-2 border-pink-300 shadow-xs' : 'text-slate-800 hover:text-slate-950 hover:bg-slate-50' }}">
                    <span class="w-5 h-5 rounded-lg bg-pink-200 text-pink-800 flex items-center justify-center text-[10px]">
                        <i class="fas fa-layer-group"></i>
                    </span>
                    <span>Semua</span>
                </a>
                {{-- 2. Karya --}}
                <a href="{{ route('circle.feed', array_merge(request()->query(), ['tab' => 'portfolio'])) }}" 
                   class="px-4 py-2 rounded-2xl text-xs font-black transition-base shrink-0 flex items-center gap-2 {{ $tabFilter === 'portfolio' ? 'bg-amber-100 text-slate-950 border-2 border-amber-300 shadow-xs' : 'text-slate-800 hover:text-slate-950 hover:bg-slate-50' }}">
                    <span class="w-5 h-5 rounded-lg bg-amber-200 text-amber-900 flex items-center justify-center text-[10px]">
                        <i class="fas fa-trophy"></i>
                    </span>
                    <span>Karya</span>
                </a>
                {{-- 3. Nongki --}}
                <a href="{{ route('circle.feed', array_merge(request()->query(), ['tab' => 'wall'])) }}" 
                   class="px-4 py-2 rounded-2xl text-xs font-black transition-base shrink-0 flex items-center gap-2 {{ $tabFilter === 'wall' ? 'bg-[#faedcd] text-slate-950 border-2 border-[#d4a373] shadow-xs' : 'text-slate-800 hover:text-slate-950 hover:bg-slate-50' }}">
                    <span class="w-5 h-5 rounded-lg bg-[#e6ccb2] text-[#7f5539] flex items-center justify-center text-[10px]">
                        <i class="fas fa-mug-hot"></i>
                    </span>
                    <span>Nongki</span>
                </a>
                {{-- 4. Reels --}}
                <a href="{{ route('circle.feed', array_merge(request()->query(), ['tab' => 'reels'])) }}" 
                   class="px-4 py-2 rounded-2xl text-xs font-black transition-base shrink-0 flex items-center gap-2 {{ $tabFilter === 'reels' ? 'bg-sky-100 text-slate-950 border-2 border-sky-300 shadow-xs' : 'text-slate-800 hover:text-slate-950 hover:bg-slate-50' }}">
                    <span class="w-5 h-5 rounded-lg bg-sky-200 text-sky-800 flex items-center justify-center text-[10px]">
                        <i class="fas fa-play"></i>
                    </span>
                    <span>Reels</span>
                </a>
            </div>

            {{-- Mobile Search & School Switcher --}}
            <div class="flex flex-col sm:hidden gap-2 border-t border-[#f1e8dc] pt-2 w-full">
                <form action="{{ route('circle.feed') }}" method="GET" class="relative w-full">
                    @if(request('school')) <input type="hidden" name="school" value="{{ request('school') }}"> @endif
                    @if(request('tab')) <input type="hidden" name="tab" value="{{ request('tab') }}"> @endif
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="Cari karya, siswa, keahlian..."
                           class="w-full pl-8 pr-7 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-950 placeholder:text-slate-400 focus:outline-none focus:border-pink-300">
                    <span class="absolute left-2.5 top-2 text-xs text-slate-400">🔍</span>
                </form>
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
                    <a href="{{ route('circle.feed') }}" class="px-2.5 py-1 rounded-xl text-[11px] font-black shrink-0 {{ !request('school') ? 'bg-pink-100 text-slate-950 border border-pink-200' : 'text-slate-700' }}">Semua</a>
                    <a href="{{ route('circle.feed', ['school' => 'SMK']) }}" class="px-2.5 py-1 rounded-xl text-[11px] font-black shrink-0 {{ request('school') === 'SMK' ? 'bg-[#faedcd] text-slate-950 border border-[#d4a373]' : 'text-slate-700' }}">SMK</a>
                    <a href="{{ route('circle.feed', ['school' => 'SMA']) }}" class="px-2.5 py-1 rounded-xl text-[11px] font-black shrink-0 {{ request('school') === 'SMA' ? 'bg-sky-100 text-slate-950 border border-sky-200' : 'text-slate-700' }}">SMA</a>
                    <a href="{{ route('circle.feed', ['school' => 'SMP']) }}" class="px-2.5 py-1 rounded-xl text-[11px] font-black shrink-0 {{ request('school') === 'SMP' ? 'bg-emerald-100 text-slate-950 border border-emerald-200' : 'text-slate-700' }}">SMP</a>
                </div>
            </div>
        </div>

        {{-- ── Banner Hasil Pencarian Gelora ── --}}
        @if(!empty($search))
            <div class="bg-gradient-to-r from-amber-50 via-pink-50 to-sky-50 rounded-3xl border-2 border-amber-300 p-4 sm:p-5 shadow-xs">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div class="flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-2xl bg-amber-200 text-amber-900 flex items-center justify-center text-sm font-black shadow-2xs">
                            🔍
                        </span>
                        <div>
                            <h4 class="font-display font-black text-sm text-slate-950">
                                Hasil Pencarian Gelora: "<span class="text-pink-600 font-extrabold">{{ $search }}</span>"
                            </h4>
                            <p class="text-xs font-bold text-slate-700">
                                Ditemukan {{ $posts->total() }} karya & cerita yang relevan.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('circle.feed', array_filter(['school' => request('school'), 'tab' => request('tab')])) }}" 
                       class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-rose-50 border-2 border-rose-200 text-rose-700 text-xs font-black transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                        <span>✕ Bersihkan</span>
                    </a>
                </div>

                {{-- Warga Pembda Terkait yang Cocok --}}
                @if(isset($matchedProfiles) && $matchedProfiles->isNotEmpty())
                    <div class="mt-3.5 pt-3 border-t border-amber-200/80">
                        <span class="text-[11px] font-black uppercase tracking-wider text-slate-900 block mb-2">Warga Pembda Terkait:</span>
                        <div class="flex items-center gap-2.5 overflow-x-auto pb-1 custom-scrollbar">
                            @foreach($matchedProfiles as $mp)
                                <a href="{{ route('circle.profile', ['username' => $mp->username]) }}" 
                                   class="px-3 py-1.5 rounded-2xl bg-white border border-amber-200 hover:border-pink-300 text-xs flex items-center gap-2 shrink-0 shadow-2xs hover:scale-105 transition">
                                    <img src="{{ $mp->user->avatar_url ?? asset('images/default-avatar.svg') }}" class="w-6 h-6 rounded-full object-cover">
                                    <div class="text-left">
                                        <div class="font-black text-slate-950 leading-tight">{{ $mp->nickname ?? $mp->user->name }}</div>
                                        <div class="text-[10px] text-slate-600">{{ $mp->user->school?->name ?? 'Pembda' }}</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- ── 3. Post Cards Feed (Teks Hitam Terbaca Jelas & Kartu Lucu) ── --}}
        @if($posts->isEmpty())
            <div class="bg-white rounded-3xl border-2 border-[#f1e8dc] p-12 text-center shadow-xs">
                <div class="w-20 h-20 rounded-3xl bg-pink-100 text-pink-600 border-2 border-pink-200 flex items-center justify-center mx-auto mb-4 text-3xl shadow-xs">
                    <i class="fas fa-palette"></i>
                </div>
                <h3 class="font-display font-black text-xl text-slate-950 mb-2 tracking-tight">Panggung Masih Menanti Ceritamu</h3>
                <p class="text-sm font-medium text-slate-800 max-w-md mx-auto mb-6 leading-relaxed">
                    Jadilah pelopor pertama yang membagikan momen nongki santai, unjuk keahlian bengkel/lab, atau karya terbaikmu hari ini!
                </p>
                @auth
                    @php
                        $myProfile = Auth::user()->circleProfile ?? \App\Models\CircleProfile::getOrCreateForUser(Auth::user());
                    @endphp
                    <a href="{{ route('circle.profile', ['username' => $myProfile->username]) }}" 
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-black text-slate-950 bg-pink-200 hover:bg-pink-300 border-2 border-pink-300 shadow-xs transition-base hover:scale-105">
                        <i class="fas fa-pen-nib text-pink-700"></i>
                        <span>Buka Profil Saya & Mulai Posting</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-black text-slate-950 bg-pink-200 hover:bg-pink-300 border-2 border-pink-300 shadow-xs transition-base hover:scale-105">
                        <i class="fas fa-heart text-pink-600"></i>
                        <span>Masuk untuk Berpartisipasi</span>
                    </a>
                @endauth
            </div>
        @else
            @if($tabFilter === 'reels')
                {{-- ═══════════════════════════════════════════
                     REELS EXPLORE GRID (TIKTOK / INSTAGRAM 9:16)
                ═══════════════════════════════════════════ --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
                    @foreach($posts as $post)
                        @php $m = $post->media->first(); @endphp
                        <div id="post-{{ $post->id }}" class="group relative rounded-3xl overflow-hidden aspect-[9/16] bg-slate-950 border-2 {{ request('post') == $post->id ? 'border-amber-400 ring-4 ring-amber-300 shadow-2xl' : 'border-[#f1e8dc]' }} shadow-md hover:shadow-xl hover:border-sky-300 transition-all duration-300 flex flex-col justify-between p-3.5">
                            
                            {{-- Background Video / Image --}}
                            <div class="absolute inset-0 z-0">
                                @if($m && $m->media_type === 'video')
                                    <video src="{{ $m->url }}" muted loop playsinline class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"></video>
                                @elseif($m && $m->media_type === 'image')
                                    <img src="{{ $m->url }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-indigo-900 to-sky-900 flex items-center justify-center p-4 text-center">
                                        <span class="text-white text-xs font-black">{{ $post->title ?? 'Reels Karya' }}</span>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/25 to-black/40"></div>
                            </div>

                            {{-- Top Pill: School & Reel badge & Share & Delete --}}
                            <div class="relative z-10 flex items-center justify-between gap-1.5">
                                <span class="px-2.5 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider bg-sky-500 text-white shadow-2xs flex items-center gap-1">
                                    <i class="fas fa-play text-[8px]"></i>
                                    <span>Lensa Gerak</span>
                                </span>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" 
                                            onclick="event.stopPropagation(); shareCirclePost('{{ addslashes($post->title ?? 'Lensa Gerak Pembda') }}', '{{ route('circle.feed', ['tab' => 'reels', 'post' => $post->id]) }}#post-{{ $post->id }}')"
                                            class="w-6 h-6 rounded-lg bg-black/60 hover:bg-amber-500 text-white flex items-center justify-center text-[10px] transition cursor-pointer" 
                                            title="Siarkan Lensa Gerak ini">
                                        <i class="fas fa-share-nodes"></i>
                                    </button>
                                    @if(Auth::check() && (Auth::id() === $post->user_id || Auth::user()->isSuperAdmin()))
                                        <form action="{{ route('circle.post.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Hapus reels ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-6 h-6 rounded-lg bg-black/60 hover:bg-rose-600 text-white flex items-center justify-center text-[10px] transition cursor-pointer" title="Hapus Reels">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            {{-- Center Play Icon --}}
                            <div class="relative z-10 flex items-center justify-center my-auto opacity-0 group-hover:opacity-100 transition-opacity">
                                <div class="w-12 h-12 rounded-full bg-white/30 backdrop-blur-sm text-white flex items-center justify-center text-lg shadow-lg">
                                    <i class="fas fa-play ml-1"></i>
                                </div>
                            </div>

                            {{-- Bottom Info & Stats --}}
                            <div class="relative z-10 text-white">
                                <a href="{{ route('circle.profile', ['username' => $post->user->circle_username ?? $post->user->username ?? 'user-' . $post->user_id]) }}" 
                                   class="flex items-center gap-1.5 mb-1.5 group-hover:underline">
                                    <img src="{{ $post->user->avatar_url ?? asset('images/default-avatar.svg') }}" class="w-5 h-5 rounded-full object-cover border border-white/50">
                                    <span class="text-[11px] font-black truncate max-w-[110px]">{{ $post->user->circleProfile->nickname ?? $post->user->name }}</span>
                                </a>
                                <h4 class="text-xs font-black line-clamp-2 leading-tight text-white mb-2">{{ $post->title ?? $post->content }}</h4>
                                <div class="flex items-center justify-between text-[10px] text-slate-300 pt-1.5 border-t border-white/20">
                                    <span class="flex items-center gap-1 font-bold">
                                        <span>🔥</span>
                                        <span>{{ $post->likes_count }}</span>
                                    </span>
                                    <span class="flex items-center gap-1 font-bold">
                                        <i class="far fa-comment-dots"></i>
                                        <span>{{ $post->comments_count }}</span>
                                    </span>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
                {{-- Pagination Links --}}
                <div class="pt-4 flex justify-center">
                    {{ $posts->links() }}
                </div>
            @else
                <div class="space-y-6">
                    @foreach($posts as $post)
                        <article id="post-{{ $post->id }}" x-data="{ showComments: false }" class="bg-white rounded-3xl border-2 {{ request('post') == $post->id ? 'border-pink-500 ring-4 ring-pink-300 shadow-xl' : 'border-[#f1e8dc]' }} p-5 sm:p-6 shadow-xs hover:shadow-md hover:border-pink-200 transition-all duration-200">
                            {{-- Post Header (Author Info & Badges) --}}
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <a href="{{ route('circle.profile', ['username' => $post->user->circle_username ?? $post->user->username ?? 'user-' . $post->user_id]) }}" 
                                       class="shrink-0 focus:outline-none group">
                                        <img src="{{ $post->user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                             alt="{{ $post->user->name }}"
                                             class="w-12 h-12 rounded-2xl object-cover ring-2 ring-pink-100 group-hover:ring-pink-400 transition-all">
                                    </a>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <a href="{{ route('circle.profile', ['username' => $post->user->circle_username ?? $post->user->username ?? 'user-' . $post->user_id]) }}" 
                                               class="font-display font-black text-base text-slate-950 hover:text-pink-600 transition-colors truncate">
                                                {{ $post->user->name }}
                                            </a>
                                            @if($post->user->role === 'guru')
                                                <span class="w-5 h-5 rounded-full bg-sky-100 text-sky-700 flex items-center justify-center text-[10px]" title="Guru / Mentor Resmi">
                                                    <i class="fas fa-certificate"></i>
                                                </span>
                                            @else
                                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px]" title="Siswa Aktif Pembda">
                                                    <i class="fas fa-check"></i>
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] font-bold text-slate-700 flex items-center gap-1.5 flex-wrap mt-0.5">
                                            <span>{{ $post->created_at->diffForHumans() }}</span>
                                            @if($post->school)
                                                <span>•</span>
                                                <span class="text-slate-900 bg-slate-100 px-2 py-0.5 rounded-md">{{ $post->school->name }}</span>
                                            @endif
                                            @if($post->location_tag)
                                                <span>•</span>
                                                <span class="text-pink-700 font-bold"><i class="fas fa-location-dot text-[9px] mr-0.5"></i>{{ $post->location_tag }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Category Pill Pastel & Post Deletion --}}
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider shrink-0 {{ $post->tab_category === 'portfolio' ? 'bg-amber-100 text-amber-950 border border-amber-300' : 'bg-[#faedcd] text-[#7f5539] border border-[#d4a373]' }}">
                                        @if($post->tab_category === 'portfolio')
                                            <i class="fas fa-trophy mr-1 text-amber-700"></i>Reka Cipta
                                        @else
                                            <i class="fas fa-mug-hot mr-1 text-[#7f5539]"></i>Cengkrama
                                        @endif
                                    </span>

                                    @if(Auth::check() && (Auth::id() === $post->user_id || Auth::user()->isSuperAdmin()))
                                        <form action="{{ route('circle.post.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Hapus postingan ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-7 h-7 rounded-xl bg-slate-100 hover:bg-rose-100 text-slate-500 hover:text-rose-600 flex items-center justify-center text-xs transition cursor-pointer border border-slate-200" title="Hapus Postingan">
                                                <i class="fas fa-trash-alt text-[10px]"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                        {{-- Mood Indicator Pastel Kuning --}}
                        @if($post->mood_text)
                            <div class="mb-3.5 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-50 border border-amber-200 text-slate-950 text-xs font-black shadow-2xs">
                                <span class="text-base">{{ $post->mood_emoji ?? '✨' }}</span>
                                <span>{{ $post->mood_text }}</span>
                            </div>
                        @endif

                        {{-- Post Title --}}
                        @if($post->title)
                            <h3 class="font-display font-black text-lg text-slate-950 mb-2 leading-snug">
                                {{ $post->title }}
                            </h3>
                        @endif

                        {{-- Post Content Text (Hitam Pekat & Sangat Mudah Dibaca) --}}
                        @if($post->content)
                            <div class="text-sm font-semibold text-slate-950 leading-relaxed whitespace-pre-line mb-4">
                                {{ $post->content }}
                            </div>
                        @endif

                        {{-- Polling Interaktif Warga Pembda --}}
                        @if($post->poll)
                            @php
                                $poll = $post->poll;
                                $userVotedOptionId = $poll->hasVoted(Auth::id(), request()->ip());
                                $totalVotes = $poll->totalVotes();
                            @endphp
                            <div x-data="{
                                    votedOptionId: {{ $userVotedOptionId ?? 'null' }},
                                    totalVotes: {{ $totalVotes }},
                                    options: {{ json_encode($poll->options->map(function($o) use ($poll, $userVotedOptionId) {
                                        return [
                                            'id' => $o->id,
                                            'option_text' => $o->option_text,
                                            'votes_count' => $o->votes_count,
                                            'percentage' => $o->percentage(),
                                            'is_selected' => $userVotedOptionId == $o->id,
                                        ];
                                    })) }},
                                    isVoting: false,
                                    async vote(optId) {
                                        if (this.votedOptionId || this.isVoting) return;
                                        this.isVoting = true;
                                        try {
                                            const csrf = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
                                            const res = await fetch('{{ route('circle.poll.vote') }}', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json',
                                                    'X-CSRF-TOKEN': csrf,
                                                },
                                                body: JSON.stringify({
                                                    circle_poll_id: {{ $poll->id }},
                                                    circle_poll_option_id: optId
                                                })
                                            });
                                            const data = await res.json();
                                            if (res.ok && data.status === 'success') {
                                                this.votedOptionId = data.voted_option_id;
                                                this.totalVotes = data.total_votes;
                                                this.options = data.options;
                                            } else {
                                                alert(data.message || 'Terjadi kesalahan');
                                            }
                                        } catch(e) {
                                            console.error(e);
                                        } finally {
                                            this.isVoting = false;
                                        }
                                    }
                                 }" 
                                 class="mb-4 p-4 rounded-3xl bg-purple-50/70 border-2 border-purple-200">
                                
                                <div class="flex items-center justify-between mb-3 px-1">
                                    <span class="text-xs font-black uppercase tracking-wider text-purple-950 flex items-center gap-1.5">
                                        <span class="w-5 h-5 rounded-lg bg-purple-200 text-purple-800 flex items-center justify-center text-[10px]">📊</span>
                                        <span>Jajak Pendapat Warga Pembda</span>
                                    </span>
                                    <span class="text-[11px] font-bold text-purple-900 bg-purple-100 border border-purple-300 px-2.5 py-0.5 rounded-full"
                                          x-text="totalVotes + ' Suara'">
                                        {{ $totalVotes }} Suara
                                    </span>
                                </div>

                                <h4 class="font-display font-black text-sm text-slate-950 mb-3 leading-snug">
                                    {{ $poll->question }}
                                </h4>

                                <div class="space-y-2">
                                    <template x-for="(opt, oIdx) in options" :key="opt.id">
                                        <div class="relative">
                                            {{-- Tombol jika belum vote --}}
                                            <template x-if="!votedOptionId">
                                                <button type="button" @click="vote(opt.id)" :disabled="isVoting"
                                                        class="w-full text-left px-4 py-2.5 rounded-2xl bg-white hover:bg-purple-100 border-2 border-purple-200 hover:border-purple-300 text-xs font-black text-slate-950 transition-all flex items-center justify-between group cursor-pointer hover:scale-[1.01] shadow-2xs">
                                                    <span x-text="opt.option_text"></span>
                                                    <i class="fas fa-chevron-right text-[10px] text-purple-400 group-hover:text-purple-700"></i>
                                                </button>
                                            </template>

                                            {{-- Tampilan persentase jika sudah vote --}}
                                            <template x-if="votedOptionId">
                                                <div class="relative overflow-hidden rounded-2xl bg-white border-2 p-2.5 text-xs font-black text-slate-950 shadow-2xs"
                                                     :class="opt.is_selected ? 'border-purple-400 ring-2 ring-purple-200' : 'border-slate-200'">
                                                    {{-- Progress Bar Pastel --}}
                                                    <div class="absolute inset-y-0 left-0 transition-all duration-700 rounded-xl"
                                                         :class="oIdx === 0 ? 'bg-pink-200/80' : (oIdx === 1 ? 'bg-amber-200/80' : (oIdx === 2 ? 'bg-emerald-200/80' : 'bg-sky-200/80'))"
                                                         :style="'width: ' + opt.percentage + '%'"></div>
                                                    
                                                    <div class="relative z-10 flex items-center justify-between px-1.5">
                                                        <div class="flex items-center gap-2">
                                                            <template x-if="opt.is_selected">
                                                                <span class="w-4 h-4 rounded-full bg-purple-600 text-white flex items-center justify-center text-[8px]">✓</span>
                                                            </template>
                                                            <span x-text="opt.option_text"></span>
                                                        </div>
                                                        <span class="text-xs font-black text-slate-950" x-text="opt.percentage + '%'"></span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        @endif

                        {{-- Post Media --}}
                        @if($post->media->isNotEmpty())
                            <div class="rounded-3xl overflow-hidden mb-4 bg-slate-950 border-2 border-[#f1e8dc] max-h-[500px] flex items-center justify-center shadow-xs">
                                @php $m = $post->media->first(); @endphp
                                @if($m->media_type === 'image')
                                    <img src="{{ $m->url }}" 
                                         alt="Media karya" 
                                         class="w-full h-auto object-cover max-h-[500px]" 
                                         loading="lazy">
                                @elseif($m->media_type === 'video')
                                    <video src="{{ $m->url }}" controls class="w-full max-h-[500px] bg-black"></video>
                                @endif
                            </div>
                        @endif

                        {{-- Action & Reaction Bar (Interactive AJAX & Lucu) --}}
                        <div class="flex items-center justify-between pt-3.5 border-t-2 border-[#f8f4ed] text-xs">
                            {{-- Chunky Pastel Reaction Buttons --}}
                            <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap">
                                {{-- Menyala! 🔥 (Oranye Pastel) --}}
                                <button type="button" 
                                        onclick="toggleCircleReaction(this, {{ $post->id }}, 'lit')"
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-orange-50 hover:bg-orange-100 border border-orange-200 text-slate-950 font-black transition-base cursor-pointer hover:scale-105">
                                    <span class="text-base">🔥</span>
                                    <span class="reaction-count-{{ $post->id }}">{{ $post->likes_count > 0 ? $post->likes_count : 'Menyala!' }}</span>
                                </button>
                                {{-- Keren Pol! 👏 (Hijau Pastel) --}}
                                <button type="button" 
                                        onclick="toggleCircleReaction(this, {{ $post->id }}, 'clap')"
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-slate-950 font-black transition-base cursor-pointer hover:scale-105">
                                    <span class="text-base">👏</span>
                                    <span class="hidden sm:inline">Keren Pol!</span>
                                </button>
                                {{-- Memicu Ide! 💡 (Kuning Pastel) --}}
                                <button type="button" 
                                        onclick="toggleCircleReaction(this, {{ $post->id }}, 'nongki')"
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-amber-50 hover:bg-amber-100 border border-amber-200 text-slate-950 font-black transition-base cursor-pointer hover:scale-105">
                                    <span class="text-base">💡</span>
                                    <span class="hidden sm:inline">Memicu Ide!</span>
                                </button>
                                {{-- Bangga! ❤️ (Pink Pastel) --}}
                                <button type="button" 
                                        onclick="toggleCircleReaction(this, {{ $post->id }}, 'love')"
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-pink-50 hover:bg-pink-100 border border-pink-200 text-slate-950 font-black transition-base cursor-pointer hover:scale-105">
                                    <span class="text-base">❤️</span>
                                    <span class="hidden sm:inline">Bangga!</span>
                                </button>
                            </div>

                            {{-- Comments, Share & Profile Link --}}
                            <div class="flex items-center gap-2 sm:gap-2.5 shrink-0 flex-wrap">
                                <button type="button" 
                                        @click="showComments = !showComments"
                                        class="text-xs font-bold text-slate-800 hover:text-pink-600 flex items-center gap-1.5 cursor-pointer px-2.5 py-1.5 rounded-xl bg-slate-50 hover:bg-pink-50 transition border border-slate-200/80">
                                    <i class="far fa-comment-dots text-pink-500"></i>
                                    <span>{{ $post->comments_count }} <span class="hidden sm:inline">Komentar</span></span>
                                </button>
                                <button type="button" 
                                        onclick="shareCirclePost('{{ addslashes($post->title ?? ($post->content ? \Illuminate\Support\Str::limit($post->content, 60) : 'Momen Gelora Pembda')) }}', '{{ route('circle.feed', ['post' => $post->id]) }}#post-{{ $post->id }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-100 hover:bg-amber-200 text-slate-950 border border-amber-300 font-black transition-all text-xs cursor-pointer hover:scale-105 shadow-2xs"
                                        title="Siarkan karya/postingan ini ke WhatsApp atau media sosial">
                                    <i class="fas fa-share-nodes text-amber-800 text-[11px]"></i>
                                    <span>Siarkan</span>
                                </button>
                                <a href="{{ route('circle.profile', ['username' => $post->user->circle_username ?? $post->user->username ?? 'user-' . $post->user_id]) }}" 
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-pink-100 hover:bg-pink-200 text-pink-900 font-black transition-colors text-xs border border-pink-200">
                                    <span>Lihat</span>
                                    <i class="fas fa-arrow-right text-[10px]"></i>
                                </a>
                            </div>
                        </div>

                        {{-- Collapsible Comment Section (Pastel Card) --}}
                        <div x-show="showComments" x-collapse x-transition class="mt-4 pt-4 border-t border-slate-100">
                            @if($post->comments->isNotEmpty())
                                <div class="space-y-2 mb-3 max-h-48 overflow-y-auto pr-1 custom-scrollbar">
                                    @foreach($post->comments as $cm)
                                        <div class="p-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs flex items-start gap-2">
                                            <div class="w-6 h-6 rounded-full bg-pink-200 text-pink-800 font-black flex items-center justify-center text-[10px] shrink-0 mt-0.5">
                                                {{ substr($cm->author_name, 0, 1) }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center justify-between gap-2">
                                                    <span class="font-black text-slate-950 truncate">{{ $cm->author_name }}</span>
                                                    <span class="text-[10px] text-slate-500 shrink-0">{{ $cm->created_at->diffForHumans() }}</span>
                                                </div>
                                                <p class="text-slate-800 font-medium mt-0.5 whitespace-pre-line">{{ $cm->comment_text }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Form Komentar Cepat --}}
                            <form action="{{ route('circle.comment.store') }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                <input type="hidden" name="circle_post_id" value="{{ $post->id }}">
                                <input type="text" name="comment_text" placeholder="Beri apresiasi atau komentar manis..." required
                                       class="flex-1 px-3.5 py-2 rounded-2xl bg-white border-2 border-pink-200 text-xs font-bold text-slate-950 placeholder:text-slate-400 focus:outline-none focus:border-pink-400 shadow-2xs">
                                <button type="submit" class="px-4 py-2 rounded-2xl bg-pink-200 hover:bg-pink-300 text-slate-950 text-xs font-black border-2 border-pink-300 transition-all hover:scale-105 shrink-0 flex items-center gap-1.5 cursor-pointer shadow-2xs">
                                    <i class="fas fa-paper-plane text-pink-700"></i>
                                    <span>Kirim</span>
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach

                {{-- Pagination Links --}}
                <div class="pt-4 flex justify-center">
                    {{ $posts->links() }}
                </div>
            </div>
            @endif
        @endif

    </div>

    {{-- ═══════════════════════════════════════════
         KOLOM KANAN: SIDEBAR WIDGETS (PASTEL CANTIK)
    ═══════════════════════════════════════════ --}}
    <div class="hidden lg:block space-y-6">

        {{-- ── 1. Hero Widget: Identity & Mission (Pink & Coklat Pastel) ── --}}
        <div class="bg-gradient-to-br from-[#fce7f3] via-[#fffbeb] to-[#faedcd] rounded-3xl p-6 border-2 border-pink-200/90 shadow-sm relative overflow-hidden">
            <div class="relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[10px] font-black tracking-wider uppercase bg-white/80 border border-pink-300 text-pink-800 mb-4 shadow-2xs">
                    <i class="fas fa-star text-amber-500 text-[10px]"></i>
                    <span>The 1st Edu-Social Network</span>
                </div>
                <h3 class="font-display font-black text-xl text-slate-950 mb-2 tracking-tight leading-snug">
                    Panggung Bebas Berekspresi & Berkarya
                </h3>
                <p class="text-xs font-bold text-slate-900 leading-relaxed mb-5">
                    Tunjukkan pesona, gaya nyentrik, suasana hati, dan mahakarya terbaikmu kepada keluarga besar Pembda dan dunia industri!
                </p>
                <div class="p-3.5 rounded-2xl bg-white/90 border border-pink-200 text-xs font-black text-slate-950 flex items-center gap-3 shadow-2xs">
                    <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                        <i class="fas fa-shield-heart text-sm"></i>
                    </span>
                    <span>Nilai akademik, rapor & BK tetap 100% aman & privat.</span>
                </div>
            </div>
            {{-- Bulatan Hiasan Pastel --}}
            <div class="absolute -right-8 -bottom-8 w-44 h-44 bg-pink-300/30 rounded-full blur-2xl pointer-events-none"></div>
        </div>

        {{-- ── 1B. 👑 JAWARA! (LEADERBOARD MENYALA) ── --}}
        @if(isset($topStudents) && $topStudents->isNotEmpty())
            <div class="bg-gradient-to-br from-amber-50 via-white to-pink-50 rounded-3xl border-2 border-amber-300 p-5 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between mb-3.5 px-0.5">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-950 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-amber-200 text-amber-900 flex items-center justify-center text-xs font-black">
                            👑
                        </span>
                        <span>Jawara!</span>
                    </h3>
                    <span class="text-[9px] font-black text-amber-900 bg-amber-200/80 px-2 py-0.5 rounded-full uppercase">
                        Menyala 🔥
                    </span>
                </div>

                <div class="space-y-2.5">
                    @foreach($topStudents as $idx => $st)
                        <a href="{{ route('circle.profile', ['username' => $st->username ?? $st->user?->circle_username ?? 'user']) }}" 
                           class="flex items-center justify-between p-2.5 rounded-2xl bg-white hover:bg-amber-100/50 border border-amber-200 transition-all hover:scale-102 group shadow-2xs">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="relative shrink-0">
                                    <img src="{{ $st->user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                         class="w-9 h-9 rounded-xl object-cover ring-2 {{ $idx === 0 ? 'ring-amber-400' : ($idx === 1 ? 'ring-slate-400' : 'ring-orange-300') }}">
                                    <span class="absolute -top-1.5 -left-1.5 text-xs">
                                        {{ $idx === 0 ? '🥇' : ($idx === 1 ? '🥈' : '🥉') }}
                                    </span>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs font-black text-slate-950 truncate group-hover:text-pink-600 transition-colors">
                                        {{ $st->nickname ?? $st->user->name }}
                                    </div>
                                    <div class="text-[10px] font-bold text-slate-600 truncate">
                                        {{ $st->user->school?->name ?? 'Siswa Pembda' }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-[11px] font-black text-orange-600 shrink-0 flex items-center gap-1 bg-orange-50 px-2 py-0.5 rounded-lg border border-orange-200">
                                <span>🔥</span>
                                <span>{{ $st->total_likes ?? 0 }}</span>
                            </div>
                        </a>
                    @endforeach

                    {{-- Guru Pembina Teraktif --}}
                    @if(isset($topTeacher))
                        <div class="pt-2 border-t border-amber-200/80">
                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-700 block mb-1.5">Guru Pembina Teraktif:</span>
                            <a href="{{ route('circle.profile', ['username' => $topTeacher->username ?? $topTeacher->user?->circle_username ?? 'user']) }}" 
                               class="flex items-center justify-between p-2 rounded-2xl bg-sky-50 hover:bg-sky-100 border border-sky-200 transition group shadow-2xs">
                                <div class="flex items-center gap-2 min-w-0">
                                    <img src="{{ $topTeacher->user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                         class="w-7 h-7 rounded-lg object-cover ring-2 ring-sky-300 shrink-0">
                                    <div class="min-w-0">
                                        <div class="text-[11px] font-black text-slate-950 truncate group-hover:text-sky-700">
                                            {{ $topTeacher->nickname ?? $topTeacher->user->name }}
                                        </div>
                                        <div class="text-[9px] font-bold text-slate-600">Guru / Mentor</div>
                                    </div>
                                </div>
                                <span class="text-[10px] font-black text-sky-800 bg-sky-100 px-2 py-0.5 rounded-md border border-sky-300">
                                    ⭐ Mentor
                                </span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ── 2. Trending Profiles Widget (Biru & Mint Pastel) ── --}}
        @if($trendingProfiles->isNotEmpty())
            <div class="bg-white rounded-3xl border-2 border-[#f1e8dc] p-5 shadow-xs">
                <div class="flex items-center justify-between mb-4 px-1">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-950 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center text-xs">
                            <i class="fas fa-fire"></i>
                        </span>
                        <span>Profil Populer</span>
                    </h3>
                    <span class="text-[10px] font-black text-slate-900 bg-amber-100 border border-amber-200 px-2 py-0.5 rounded-full">
                        Paling Ramai 🔥
                    </span>
                </div>
                <div class="space-y-3">
                    @foreach($trendingProfiles as $tp)
                        <a href="{{ route('circle.profile', ['username' => $tp->username]) }}" 
                           class="flex items-center justify-between p-2.5 rounded-2xl hover:bg-pink-50/70 border border-transparent hover:border-pink-200 transition-base group">
                            <div class="flex items-center gap-3 min-w-0">
                                <img src="{{ $tp->user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                     alt="{{ $tp->user->name }}"
                                     class="w-10 h-10 rounded-2xl object-cover ring-2 ring-pink-100 group-hover:ring-pink-400 transition-all shrink-0">
                                <div class="min-w-0">
                                    <h4 class="text-xs font-black text-slate-950 group-hover:text-pink-600 transition-colors truncate flex items-center gap-1.5">
                                        <span>{{ $tp->nickname ?? $tp->user->name }}</span>
                                        @if($tp->mood_emoji)
                                            <span class="text-[12px]">{{ $tp->mood_emoji }}</span>
                                        @endif
                                    </h4>
                                    <span class="text-[10px] font-bold text-slate-700 block truncate">
                                        {{ $tp->user->school?->name ?? 'Perguruan Pembda' }}
                                    </span>
                                </div>
                            </div>
                            <span class="text-[11px] font-black text-slate-950 bg-sky-100 border border-sky-200 px-2.5 py-1 rounded-xl shrink-0 flex items-center gap-1">
                                <i class="fas fa-eye text-[9px] text-sky-700"></i>{{ number_format($tp->views_count) }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── 3. Footer Mini ── --}}
        <div class="px-2 text-center text-xs font-bold text-slate-800 leading-relaxed">
            <p>© {{ date('Y') }} Perguruan Pembda Gunungsitoli.</p>
            <p class="text-slate-600 text-[11px] mt-0.5">Warna-Warni Kreativitas di PembdaHUB 🌸</p>
        </div>

    </div>

    {{-- ═══════════════════════════════════════════
         24H STORY VIEWER MODAL (INSTAGRAM / WHATSAPP STYLE)
    ═══════════════════════════════════════════ --}}
    <div x-show="storyOpen" x-cloak 
         class="fixed inset-0 z-50 bg-slate-950/90 backdrop-blur-md flex items-center justify-center p-2 sm:p-4"
         @keydown.escape.window="closeStory()"
         x-transition>
        
        <div class="relative w-full max-w-sm h-[85vh] max-h-[680px] bg-slate-900 rounded-[2.5rem] overflow-hidden border-2 border-white/20 shadow-2xl flex flex-col justify-between"
             @click.outside="closeStory()">
            
            {{-- Top Bars: Stories Progress Bar & Header --}}
            <div class="relative z-20 p-4 bg-gradient-to-b from-black/85 via-black/40 to-transparent">
                {{-- Multi-story Progress Indicators --}}
                <div class="flex items-center gap-1.5 mb-3">
                    <template x-for="(st, idx) in stories" :key="st.id">
                        <div class="h-1 flex-1 rounded-full bg-white/30 overflow-hidden">
                            <div class="h-full bg-white transition-all duration-100 ease-linear"
                                 :style="idx === activeIdx ? 'width: ' + progress + '%' : (idx < activeIdx ? 'width: 100%' : 'width: 0%')">
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Story Author Info & Close Button --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <img :src="currStory().avatar" class="w-9 h-9 rounded-full object-cover ring-2 ring-pink-400">
                        <div class="min-w-0 text-white">
                            <div class="flex items-center gap-1.5">
                                <span class="font-black text-xs truncate" x-text="currStory().name"></span>
                                <span class="text-[9px] bg-pink-500/80 px-1.5 py-0.2 rounded-md font-bold" x-text="currStory().school"></span>
                            </div>
                            <span class="text-[10px] text-slate-300 font-medium" x-text="currStory().time_ago"></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a :href="'/@' + currStory().username" class="px-2.5 py-1 rounded-xl bg-white/20 hover:bg-white/30 text-white text-[10px] font-black transition">
                            Profil
                        </a>
                        <button type="button" @click="closeStory()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/40 text-white flex items-center justify-center text-sm transition cursor-pointer">
                            ✕
                        </button>
                    </div>
                </div>
            </div>

            {{-- Center Tap Zones (Left: Prev, Right: Next) --}}
            <div class="absolute inset-0 z-10 flex">
                <div class="w-1/3 h-full cursor-pointer" @click="prevStory()"></div>
                <div class="w-2/3 h-full cursor-pointer" @click="nextStory()"></div>
            </div>

            {{-- Story Media & Content --}}
            <div class="absolute inset-0 z-0 flex items-center justify-center bg-black">
                <template x-if="currStory().media_type === 'image'">
                    <img :src="currStory().media_url" class="w-full h-full object-contain">
                </template>
                <template x-if="currStory().media_type === 'video'">
                    <video :src="currStory().media_url" autoplay playsinline class="w-full h-full object-contain"></video>
                </template>
                <template x-if="!currStory().media_url">
                    <div class="p-8 text-center text-white flex flex-col items-center justify-center h-full w-full bg-gradient-to-br from-pink-600 via-purple-600 to-indigo-700">
                        <span class="text-4xl mb-4" x-text="currStory().mood_emoji || '✨'"></span>
                        <p class="font-display font-black text-lg sm:text-xl leading-relaxed" x-text="currStory().content"></p>
                    </div>
                </template>
            </div>

            {{-- Bottom Overlay: Story Caption & Reactions --}}
            <div class="relative z-20 p-4 bg-gradient-to-t from-black/90 via-black/50 to-transparent text-white">
                <template x-if="currStory().media_url && currStory().content">
                    <p class="text-xs font-semibold text-slate-100 line-clamp-3 mb-3" x-text="currStory().content"></p>
                </template>

                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5">
                        <button type="button" @click.stop="toggleCircleReaction($el, currStory().id, 'lit')" class="px-2.5 py-1 rounded-xl bg-white/20 hover:bg-white/30 text-xs font-black transition">
                            🔥 Lit
                        </button>
                        <button type="button" @click.stop="toggleCircleReaction($el, currStory().id, 'clap')" class="px-2.5 py-1 rounded-xl bg-white/20 hover:bg-white/30 text-xs font-black transition">
                            👏 Apresiasi
                        </button>
                        <button type="button" @click.stop="toggleCircleReaction($el, currStory().id, 'love')" class="px-2.5 py-1 rounded-xl bg-white/20 hover:bg-white/30 text-xs font-black transition">
                            ❤️ Suka
                        </button>
                    </div>
                    <span class="text-[10px] text-slate-300 font-bold" x-text="currStory().mood_text || 'Story 24 Jam'"></span>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
