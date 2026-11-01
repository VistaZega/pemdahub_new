{{-- ═══════════════════════════════════════════════════════════════════════════
     PARTIAL: POST CARD BODY (LIVE REPORT, GPS EVENT METADATA & SMART MEDIA COLLAGE)
     Digunakan di Beranda (circle/index.blade.php) & Dinding Profil (circle/profile.blade.php)
═══════════════════════════════════════════════════════════════════════════ --}}
@php
    $mediaItems = $post->media ?? collect();
    $mediaCount = $mediaItems->count();
    $layout = $post->media_layout ?? 'bottom';
    $hasEventMeta = !empty($post->event_name) || !empty($post->event_date) || !empty($post->latitude) || $post->post_type === 'report' || $post->is_live;
    $lightboxSlides = $mediaItems->map(function ($m, $idx) use ($post) {
        return [
            'url' => $m->url,
            'type' => $m->media_type,
            'caption' => $m->metadata['caption'] ?? ($post->title ?? ('Dokumentasi #' . ($idx + 1))),
        ];
    })->values()->all();
@endphp

<div x-data="{
    lightboxOpen: false,
    activeSlide: 0,
    slides: {{ json_encode($lightboxSlides) }},
    showLiveUpdateForm: false,
    openLightbox(index) {
        this.activeSlide = index;
        this.lightboxOpen = true;
    },
    nextSlide() {
        this.activeSlide = (this.activeSlide + 1) % this.slides.length;
    },
    prevSlide() {
        this.activeSlide = (this.activeSlide - 1 + this.slides.length) % this.slides.length;
    }
}">

    {{-- ── 1. BANNER STATUS 🔴 LIVE REPORT / LIPUTAN LANGSUNG ── --}}
    @if($post->is_live || $post->post_type === 'live')
        <div class="mb-4 rounded-3xl overflow-hidden border-2 {{ ($post->live_status ?? 'live') === 'live' ? 'border-rose-500 bg-gradient-to-r from-rose-50 via-red-50 to-amber-50 shadow-md' : 'border-slate-300 bg-slate-50' }} p-4">
            <div class="flex items-center justify-between gap-2 flex-wrap mb-2.5">
                <div class="flex items-center gap-2">
                    @if(($post->live_status ?? 'live') === 'live')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-600 text-white text-[11px] font-black uppercase tracking-wider shadow-xs">
                            <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
                            <span>🔴 SEDANG LIVE DARI LOKASI</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-800 text-white text-[11px] font-black uppercase tracking-wider">
                            <span>🏁 LIPUTAN LANGSUNG SELESAI</span>
                        </span>
                    @endif

                    @if($post->event_date)
                        <span class="text-[11px] font-bold text-slate-700 bg-white/90 px-2.5 py-0.5 rounded-full border border-slate-200">
                            ⏰ Mulai {{ $post->event_date->format('H:i') }} WIB
                        </span>
                    @endif
                </div>

                @if(Auth::check() && (Auth::id() === $post->user_id || Auth::user()->isSuperAdmin()) && ($post->live_status ?? 'live') === 'live')
                    <button type="button"
                            @click="showLiveUpdateForm = !showLiveUpdateForm"
                            class="px-3 py-1 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black transition cursor-pointer flex items-center gap-1.5 shadow-2xs">
                        <i class="fas fa-satellite-dish text-[10px]"></i>
                        <span>+ Kirim Kabar / Foto Live</span>
                    </button>
                @endif
            </div>

            {{-- Embed Video Stream (YouTube Live / Link Eksternal) --}}
            @if($post->live_stream_url)
                @if($post->live_embed_url)
                    <div class="rounded-2xl overflow-hidden border-2 border-rose-300 bg-black aspect-video mb-3 shadow-sm">
                        <iframe src="{{ $post->live_embed_url }}" class="w-full h-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                @else
                    <div class="mb-3 p-3 rounded-2xl bg-white border border-rose-200 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-base shrink-0">
                                📺
                            </span>
                            <div class="min-w-0">
                                <div class="text-xs font-black text-slate-950">Tautan Siaran Video Langsung Tersedia</div>
                                <div class="text-[11px] text-slate-500 truncate">{{ $post->live_stream_url }}</div>
                            </div>
                        </div>
                        <a href="{{ $post->live_stream_url }}" target="_blank" rel="noopener noreferrer"
                           class="px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black shrink-0 transition shadow-2xs">
                            Tonton Live ➔
                        </a>
                    </div>
                @endif
            @endif

            {{-- Timeline Perkembangan Menit-demi-Menit (Live Updates) --}}
            @if(!empty($post->live_updates) && is_array($post->live_updates))
                <div class="mt-2 pt-2.5 border-t border-rose-200/70">
                    <div class="text-[10px] font-black uppercase tracking-wider text-rose-900 mb-2 flex items-center gap-1.5">
                        <i class="fas fa-bolt text-rose-600"></i>
                        <span>Linimasa Liputan Langsung ({{ count($post->live_updates) }} Update)</span>
                    </div>
                    <div class="space-y-2 max-h-60 overflow-y-auto pr-1 custom-scrollbar">
                        @foreach(array_reverse($post->live_updates) as $upd)
                            <div class="p-2.5 rounded-2xl bg-white/95 border border-rose-200/80 text-xs shadow-2xs">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <span class="px-2 py-0.5 rounded-md bg-rose-100 text-rose-900 font-black text-[10px]">
                                        {{ $upd['badge'] ?? '⚡ UPDATE' }}
                                    </span>
                                    <span class="text-[10px] font-black text-slate-500">
                                        🕒 {{ $upd['time'] ?? '' }}
                                    </span>
                                </div>
                                <p class="font-bold text-slate-900 leading-relaxed whitespace-pre-line">{{ $upd['text'] ?? '' }}</p>
                                @if(!empty($upd['photo_url']))
                                    <img src="{{ $upd['photo_url'] }}" alt="Live update photo" class="mt-2 rounded-xl max-h-40 object-cover border border-slate-200 cursor-pointer" onclick="window.open('{{ $upd['photo_url'] }}', '_blank')">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Form Tambah Update Live untuk Pemilik Post --}}
            @if(Auth::check() && (Auth::id() === $post->user_id || Auth::user()->isSuperAdmin()) && ($post->live_status ?? 'live') === 'live')
                <div x-show="showLiveUpdateForm" x-collapse x-transition class="mt-3 pt-3 border-t-2 border-rose-200">
                    <form action="{{ route('circle.post.liveUpdate', $post->id) }}" method="POST" enctype="multipart/form-data" class="space-y-2.5">
                        @csrf
                        <div class="text-xs font-black text-slate-900">📡 Siarkan Perkembangan Terbaru dari Lokasi:</div>
                        <textarea name="update_text" rows="2" placeholder="Contoh: Babak ke-2 baru dimulai! Tim Pembda unggul sementara 2-1..."
                                  class="w-full p-3 rounded-2xl bg-white border-2 border-rose-200 text-xs font-bold text-slate-950 focus:outline-none focus:border-rose-400 resize-none"></textarea>
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <label class="px-3 py-1.5 rounded-xl bg-white hover:bg-rose-50 border border-rose-300 text-slate-900 text-xs font-black cursor-pointer flex items-center gap-1.5">
                                <i class="fas fa-camera text-rose-600"></i>
                                <span>+ Lampirkan Foto Lapangan</span>
                                <input type="file" name="update_photo" accept="image/*,video/*" class="hidden">
                            </label>
                            <div class="flex items-center gap-2">
                                <button type="submit" name="end_live" value="1"
                                        onclick="return confirm('Akhiri siaran langsung ini dan simpan sebagai arsip laporan?')"
                                        class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-black cursor-pointer">
                                    ⏹️ Akhiri Live
                                </button>
                                <button type="submit"
                                        class="px-4 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black cursor-pointer shadow-xs">
                                    🚀 Siarkan Update
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    @endif

    {{-- ── 2. KARTU INFORMASI LAPORAN KEGIATAN / LOMBA & KOORDINAT GPS ── --}}
    @if($hasEventMeta && ($post->event_name || $post->event_date || $post->location_tag || $post->latitude))
        <div class="mb-4 p-3.5 sm:p-4 rounded-2xl bg-gradient-to-r from-sky-50 via-indigo-50/70 to-emerald-50 border-2 border-sky-200 shadow-2xs">
            <div class="flex items-center justify-between gap-2 flex-wrap mb-2">
                <span class="px-2.5 py-0.5 rounded-lg bg-sky-600 text-white text-[10px] font-black uppercase tracking-wider flex items-center gap-1">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Laporan Kegiatan & Lokasi Terverifikasi</span>
                </span>
                @if($post->google_maps_url)
                    <a href="{{ $post->google_maps_url }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-white hover:bg-emerald-50 text-emerald-800 border border-emerald-300 text-[11px] font-black transition shadow-2xs hover:scale-105">
                        <i class="fas fa-map-location-dot text-emerald-600"></i>
                        <span>Buka Peta GPS ➔</span>
                    </a>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                @if($post->event_name)
                    <div class="p-2 rounded-xl bg-white/90 border border-sky-100 flex items-start gap-2">
                        <span class="text-base shrink-0">🏆</span>
                        <div class="min-w-0">
                            <div class="text-[10px] font-bold text-slate-500 uppercase">Nama Kegiatan / Lomba</div>
                            <div class="font-black text-slate-950 leading-snug">{{ $post->event_name }}</div>
                        </div>
                    </div>
                @endif

                @if($post->event_date)
                    <div class="p-2 rounded-xl bg-white/90 border border-sky-100 flex items-start gap-2">
                        <span class="text-base shrink-0">🗓️</span>
                        <div class="min-w-0">
                            <div class="text-[10px] font-bold text-slate-500 uppercase">Waktu & Tanggal</div>
                            <div class="font-black text-slate-950 leading-snug">
                                {{ $post->event_date->translatedFormat('d M Y • H:i') }} WIB
                            </div>
                        </div>
                    </div>
                @endif

                @if($post->location_tag || ($post->latitude && $post->longitude))
                    <div class="p-2 rounded-xl bg-white/90 border border-sky-100 flex items-start gap-2">
                        <span class="text-base shrink-0">📍</span>
                        <div class="min-w-0">
                            <div class="text-[10px] font-bold text-slate-500 uppercase">Lokasi & Titik GPS</div>
                            <div class="font-black text-slate-950 leading-snug truncate" title="{{ $post->location_tag }}">
                                {{ $post->location_tag ?: 'Titik Koordinat GPS' }}
                            </div>
                            @if($post->latitude && $post->longitude)
                                <div class="text-[10px] font-bold text-emerald-700 flex items-center gap-1 mt-0.5">
                                    <i class="fas fa-satellite"></i>
                                    <span>{{ number_format($post->latitude, 5) }}, {{ number_format($post->longitude, 5) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ── 3 & 4. PELETAKAN ISI BERITA & KOLASE MEDIA PINTAR (1, 2, 3, 4, 5+ FOTO / VIDEO / AUDIO) ── --}}
    <div class="flex flex-col">
        {{-- BLOK TEKS (JUDUL & ISI CERITA/BERITA) --}}
        <div class="{{ $layout === 'top' ? 'order-2' : 'order-1' }}">

            {{-- 🤝 BANNER KARYA DUET KOLABORASI (COLLABS) --}}
            @if($post->isCollab() && $post->collaborator)
                <div class="mb-3.5 p-3 sm:p-3.5 rounded-2xl bg-gradient-to-r from-purple-50 via-pink-50 to-amber-50 border-2 border-purple-300 shadow-2xs flex items-center justify-between gap-3 flex-wrap">
                    <div class="flex items-center gap-2.5">
                        <div class="flex items-center -space-x-3 shrink-0">
                            <img src="{{ $post->user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                 alt="{{ $post->user->name }}" 
                                 class="w-9 h-9 rounded-full object-cover border-2 border-white shadow-xs" title="Inisiator: {{ $post->user->name }}">
                            <img src="{{ $post->collaborator->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                 alt="{{ $post->collaborator->name }}" 
                                 class="w-9 h-9 rounded-full object-cover border-2 border-purple-500 shadow-xs ring-2 ring-white" title="Kolaborator: {{ $post->collaborator->name }}">
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="px-2 py-0.5 rounded-md bg-purple-600 text-white text-[9px] font-black uppercase tracking-wider flex items-center gap-1">
                                    <span>🤝 KARYA DUET KOLABORASI</span>
                                </span>
                                <span class="text-[9px] font-bold text-purple-900 bg-purple-100 px-2 py-0.5 rounded-md">
                                    {{ $post->interactive_data['collaborator_role'] ?? 'Kreator Duet' }}
                                </span>
                            </div>
                            <div class="text-xs font-black text-slate-900 mt-0.5">
                                Kolaborasi <a href="{{ route('circle.profile', $post->user->circleProfile->username ?? $post->user->username) }}" class="underline hover:text-purple-700">{{ $post->user->name }}</a>
                                ✕
                                <a href="{{ route('circle.profile', $post->collaborator->circleProfile->username ?? $post->collaborator->username) }}" class="underline hover:text-purple-700">{{ $post->collaborator->name }}</a>
                            </div>
                        </div>
                    </div>
                    <span class="text-[9px] font-black text-amber-900 bg-amber-200/80 px-2.5 py-1 rounded-xl border border-amber-300 flex items-center gap-1">
                        <span>💎 Poin apresiasi dibagi adil kedua kreator</span>
                    </span>
                </div>
            @endif

            @if($post->title)
                <h3 class="font-display font-black text-lg text-slate-950 mb-2 leading-snug">
                    {{ $post->title }}
                </h3>
            @endif

            @if($post->content)
                <div class="text-sm font-semibold text-slate-950 leading-relaxed whitespace-pre-line mb-4 {{ $post->isPantun() ? 'p-4 rounded-2xl bg-amber-50/70 border-2 border-amber-200 font-serif italic text-base text-amber-950' : '' }}">
                    {{ $post->content }}
                </div>
            @endif

            {{-- 🪶 BANNER ARENA BERBALAS PANTUN --}}
            @if($post->isPantun())
                <div class="mb-4 rounded-2xl p-3.5 bg-gradient-to-r from-emerald-50 via-teal-50 to-cyan-50 border-2 border-emerald-300 shadow-2xs flex items-center justify-between gap-3 flex-wrap">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-emerald-700 text-white flex items-center justify-center text-sm font-black shrink-0">
                            🪶
                        </span>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-[10px] font-black uppercase tracking-wider text-emerald-800">Arena Berbalas Pantun</span>
                                <span class="text-[9px] font-black bg-white px-2 py-0.5 rounded-full border border-emerald-300 text-emerald-900">
                                    Tema: {{ $post->interactive_data['theme'] ?? 'Jenaka Sekolah' }}
                                </span>
                            </div>
                            <p class="text-xs font-bold text-slate-700 mt-0.5">
                                Balas pantun di atas di kolom komentar! Balasan terbaik dinobatkan sebagai <strong>Raja/Ratu Pantun (+10 Poin)</strong> ✨
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 🧩 KARTU INTERAKTIF TEKA-TEKI & MISTERI (RIDDLE) --}}
            @if($post->isRiddle())
                @php
                    $riddleHint = $post->interactive_data['hint'] ?? null;
                    $guessCount = $post->riddleGuesses->count();
                @endphp
                <div class="mb-4 rounded-3xl p-4 sm:p-5 border-2 border-indigo-300 bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 shadow-xs relative overflow-hidden"
                     x-data="{
                         riddleInput: '',
                         riddleSubmitting: false,
                         riddleResult: null,
                         isSolvedLocal: {{ $post->is_solved ? 'true' : 'false' }},
                         solverNameLocal: '{{ $post->solvedBy?->name ?? '' }}',
                         async sendGuess() {
                             if (!this.riddleInput.trim()) return;
                             this.riddleSubmitting = true;
                             this.riddleResult = null;
                             try {
                                 const res = await fetch('{{ route('circle.riddle.guess', $post->id) }}', {
                                     method: 'POST',
                                     headers: {
                                         'Content-Type': 'application/json',
                                         'Accept': 'application/json',
                                         'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                     },
                                     body: JSON.stringify({ guess: this.riddleInput })
                                 });
                                 const data = await res.json();
                                 this.riddleResult = data;
                                 if (data.is_solved) {
                                     this.isSolvedLocal = true;
                                     this.solverNameLocal = data.solver_name || 'Kamu';
                                 }
                             } catch (e) {
                                 this.riddleResult = { status: 'error', message: 'Terjadi kesalahan koneksi.' };
                             } finally {
                                 this.riddleSubmitting = false;
                             }
                         }
                     }">
                    {{-- Header Teka-Teki --}}
                    <div class="flex items-center justify-between gap-2 mb-3 flex-wrap">
                        <span class="px-3 py-1 rounded-full bg-indigo-600 text-white text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 shadow-2xs">
                            <span>🧩 TEKA-TEKI & MISTERI</span>
                        </span>

                        <template x-if="isSolvedLocal">
                            <span class="px-3 py-1 rounded-full bg-emerald-500 text-white text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 shadow-xs">
                                <span>🏆 TERPECAHKAN!</span>
                            </span>
                        </template>
                        <template x-if="!isSolvedLocal">
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-900 border border-amber-300 text-[10px] font-black">
                                🔥 Hadiah +15 Poin Detektif
                            </span>
                        </template>
                    </div>

                    {{-- Status Terpecahkan --}}
                    <template x-if="isSolvedLocal">
                        <div class="p-3.5 rounded-2xl bg-white border-2 border-emerald-400 text-emerald-950 flex items-center gap-3 shadow-xs mb-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-xl shrink-0">
                                👑
                            </div>
                            <div class="min-w-0">
                                <div class="text-[10px] font-black uppercase tracking-wider text-emerald-700">Misteri Berhasil Dipecahkan!</div>
                                <div class="text-xs font-black text-slate-900 leading-tight">
                                    Dipecahkan oleh <span class="text-emerald-700 font-extrabold" x-text="solverNameLocal"></span> 🏆
                                </div>
                                @if($post->is_solved && !empty($post->interactive_data['raw_answer']))
                                    <div class="text-[11px] font-bold text-slate-600 mt-0.5">
                                        Kunci Jawaban: <span class="px-2 py-0.5 rounded-md bg-emerald-100 font-black text-emerald-900">{{ $post->interactive_data['raw_answer'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </template>

                    {{-- Clue / Petunjuk --}}
                    @if($riddleHint)
                        <div class="mb-3 p-3 rounded-2xl bg-white/80 border border-indigo-200 flex items-start gap-2.5">
                            <span class="text-base shrink-0">💡</span>
                            <div class="text-xs">
                                <strong class="font-black text-indigo-950">Petunjuk Rahasia (Clue):</strong>
                                <span class="text-slate-700 font-semibold">{{ $riddleHint }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Form Tebak Teka-Teki (Jika belum terpecahkan) --}}
                    <template x-if="!isSolvedLocal">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <input type="text"
                                       x-model="riddleInput"
                                       @keydown.enter.prevent="sendGuess()"
                                       placeholder="Ketik tebakanmu di sini..."
                                       :disabled="riddleSubmitting"
                                       class="flex-1 px-3.5 py-2.5 rounded-2xl bg-white border-2 border-indigo-300 text-xs font-black text-slate-950 placeholder-slate-400 focus:outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 shadow-inner">
                                <button type="button"
                                        @click="sendGuess()"
                                        :disabled="riddleSubmitting || !riddleInput.trim()"
                                        class="px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-black transition flex items-center gap-1.5 shadow-xs cursor-pointer active:scale-95">
                                    <span x-show="!riddleSubmitting">🔍 Pecahkan!</span>
                                    <span x-show="riddleSubmitting">Memeriksa...</span>
                                </button>
                            </div>

                            {{-- Alert Hasil Tebakan --}}
                            <template x-if="riddleResult">
                                <div class="p-3 rounded-2xl text-xs font-bold transition flex items-center justify-between gap-2"
                                     :class="riddleResult.is_correct ? 'bg-emerald-100 text-emerald-950 border-2 border-emerald-400' : 'bg-rose-100 text-rose-950 border border-rose-300'">
                                    <span x-text="riddleResult.message"></span>
                                    <button type="button" @click="riddleResult = null" class="text-slate-400 hover:text-slate-600 font-black">✕</button>
                                </div>
                            </template>

                            <div class="flex items-center justify-between text-[10px] font-bold text-slate-500 px-1 pt-0.5">
                                <span>{{ $guessCount }} warga telah mencoba menebak</span>
                                <span>Penebak pertama dapat +15 poin!</span>
                            </div>
                        </div>
                    </template>
                </div>
            @endif

            {{-- ⚔️ KARTU ARENA DUEL BATTLE 1 vs 1 --}}
            @if($post->isBattle())
                @php
                    $battleData = $post->interactive_data ?? [];
                    $battleStats = $post->getBattleStats(Auth::id());
                    $imgA = !empty($battleData['option_a_image']) ? asset('storage/' . $battleData['option_a_image']) : null;
                    $imgB = !empty($battleData['option_b_image']) ? asset('storage/' . $battleData['option_b_image']) : null;
                @endphp
                <div class="mb-4 rounded-3xl p-4 sm:p-5 border-2 border-red-300 bg-gradient-to-br from-amber-50 via-rose-50 to-orange-50 shadow-xs relative overflow-hidden"
                     x-data="{
                         votesA: {{ $battleStats['votes_a'] }},
                         votesB: {{ $battleStats['votes_b'] }},
                         totalVotes: {{ $battleStats['total'] }},
                         pctA: {{ $battleStats['pct_a'] }},
                         pctB: {{ $battleStats['pct_b'] }},
                         myVote: '{{ $battleStats['my_vote'] ?? '' }}',
                         voting: false,
                         async castVote(opt) {
                             if (this.voting) return;
                             this.voting = true;
                             try {
                                 const res = await fetch('{{ route('circle.battle.vote', $post->id) }}', {
                                     method: 'POST',
                                     headers: {
                                         'Content-Type': 'application/json',
                                         'Accept': 'application/json',
                                         'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                     },
                                     body: JSON.stringify({ option: opt })
                                 });
                                 const data = await res.json();
                                 if (data.status === 'success') {
                                     this.myVote = data.chosen_option;
                                     this.votesA = data.stats.votes_a;
                                     this.votesB = data.stats.votes_b;
                                     this.totalVotes = data.stats.total;
                                     this.pctA = data.stats.pct_a;
                                     this.pctB = data.stats.pct_b;
                                 }
                             } catch (e) {
                                 alert('Gagal mengirim suara duel.');
                             } finally {
                                 this.voting = false;
                             }
                         }
                     }">
                    {{-- Header Battle --}}
                    <div class="flex items-center justify-between gap-2 mb-3.5 flex-wrap">
                        <span class="px-3 py-1 rounded-full bg-rose-600 text-white text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 shadow-2xs">
                            <span>⚔️ ARENA DUEL KREATIF 1v1</span>
                        </span>
                        <span class="text-[11px] font-mono font-black text-rose-900 bg-white/80 px-2.5 py-0.5 rounded-full border border-rose-200">
                            <span x-text="totalVotes"></span> Suara Masuk
                        </span>
                    </div>

                    {{-- 2 Kolom Adu Karya A vs B --}}
                    <div class="grid grid-cols-2 gap-3 mb-3.5 relative">
                        {{-- Opsi A --}}
                        <div class="rounded-2xl p-3 border-2 transition flex flex-col justify-between"
                             :class="myVote === 'a' ? 'bg-amber-100/90 border-amber-500 ring-2 ring-amber-300' : 'bg-white border-amber-200'">
                            <div>
                                @if($imgA)
                                    <div class="aspect-[4/3] rounded-xl overflow-hidden mb-2 bg-slate-900 border border-amber-200">
                                        <img src="{{ $imgA }}" alt="Karya A" class="w-full h-full object-cover">
                                    </div>
                                @endif
                                <div class="text-xs font-black text-slate-900 line-clamp-2 leading-tight mb-2">
                                    {{ $battleData['option_a_title'] ?? 'Karya A' }}
                                </div>
                            </div>
                            <button type="button"
                                    @click="castVote('a')"
                                    :disabled="voting"
                                    class="w-full py-2 rounded-xl text-xs font-black transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs active:scale-95"
                                    :class="myVote === 'a' ? 'bg-amber-500 text-slate-950 font-black' : 'bg-amber-50 hover:bg-amber-200 text-amber-950 border border-amber-300'">
                                <span x-show="myVote === 'a'">⭐ Pilihanmu</span>
                                <span x-show="myVote !== 'a'">Pilih Karya A</span>
                            </button>
                        </div>

                        {{-- Lingkaran VS di Tengah --}}
                        <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-rose-600 text-white font-display font-black text-xs flex items-center justify-center border-2 border-white shadow-md z-10 pointer-events-none">
                            VS
                        </div>

                        {{-- Opsi B --}}
                        <div class="rounded-2xl p-3 border-2 transition flex flex-col justify-between"
                             :class="myVote === 'b' ? 'bg-rose-100/90 border-rose-500 ring-2 ring-rose-300' : 'bg-white border-rose-200'">
                            <div>
                                @if($imgB)
                                    <div class="aspect-[4/3] rounded-xl overflow-hidden mb-2 bg-slate-900 border border-rose-200">
                                        <img src="{{ $imgB }}" alt="Karya B" class="w-full h-full object-cover">
                                    </div>
                                @endif
                                <div class="text-xs font-black text-slate-900 line-clamp-2 leading-tight mb-2">
                                    {{ $battleData['option_b_title'] ?? 'Karya B' }}
                                </div>
                            </div>
                            <button type="button"
                                    @click="castVote('b')"
                                    :disabled="voting"
                                    class="w-full py-2 rounded-xl text-xs font-black transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs active:scale-95"
                                    :class="myVote === 'b' ? 'bg-rose-500 text-white font-black' : 'bg-rose-50 hover:bg-rose-200 text-rose-950 border border-rose-300'">
                                <span x-show="myVote === 'b'">⭐ Pilihanmu</span>
                                <span x-show="myVote !== 'b'">Pilih Karya B</span>
                            </button>
                        </div>
                    </div>

                    {{-- Bilah Persentase Pertarungan (Battle Bar) --}}
                    <div class="p-3 rounded-2xl bg-white/90 border border-rose-200 space-y-1.5">
                        <div class="flex items-center justify-between text-[11px] font-black">
                            <span class="text-amber-800"><span x-text="pctA"></span>% Karya A (<span x-text="votesA"></span>)</span>
                            <span class="text-rose-800">(<span x-text="votesB"></span>) Karya B <span x-text="pctB"></span>%</span>
                        </div>
                        <div class="h-3 rounded-full bg-slate-200 overflow-hidden flex">
                            <div class="bg-gradient-to-r from-amber-400 to-amber-500 transition-all duration-500" :style="'width: ' + pctA + '%'"></div>
                            <div class="bg-gradient-to-r from-rose-500 to-red-500 transition-all duration-500" :style="'width: ' + pctB + '%'"></div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ── KARTU REFERENSI KARYA AKADEMIK TERVERIFIKASI (LMS / TUGAS AKHIR / PENELITIAN) ── --}}
            @php
                $verifiedRef = $post->verified_reference_card;
            @endphp
            @if($verifiedRef)
                <div class="mb-4 p-3.5 rounded-2xl bg-gradient-to-r from-amber-50 via-yellow-50/80 to-emerald-50 border-2 border-amber-300 shadow-2xs flex items-center justify-between gap-3 flex-wrap">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-lg shrink-0 shadow-xs">
                            🎓
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap mb-0.5">
                                <span class="px-2 py-0.5 rounded-md bg-amber-200/90 text-amber-950 text-[10px] font-black uppercase tracking-wider">
                                    {{ $verifiedRef['badge'] }}
                                </span>
                                <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-900 border border-emerald-300 text-[10px] font-black flex items-center gap-1">
                                    <i class="fas fa-circle-check text-emerald-600 text-[9px]"></i>
                                    <span>Karya Akademik Terverifikasi</span>
                                </span>
                            </div>
                            <div class="text-xs sm:text-sm font-black text-slate-950 leading-snug">{{ $verifiedRef['title'] }}</div>
                            @if(!empty($verifiedRef['subtitle']))
                                <div class="text-[11px] font-bold text-slate-600 mt-0.5">{{ $verifiedRef['subtitle'] }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        @if(!empty($verifiedRef['score']))
                            <div class="px-3 py-1.5 rounded-xl bg-white border-2 border-emerald-400 text-center shadow-2xs">
                                <div class="text-[9px] font-black text-slate-500 uppercase leading-none">Nilai</div>
                                <div class="text-sm font-black text-emerald-700 leading-tight">{{ round($verifiedRef['score']) }}</div>
                            </div>
                        @endif
                        @if(!empty($verifiedRef['file_url']))
                            <a href="{{ $verifiedRef['file_url'] }}" target="_blank" rel="noopener noreferrer"
                               class="px-3 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-black transition flex items-center gap-1.5 shadow-2xs">
                                <i class="fas fa-file-arrow-down"></i>
                                <span>Buka Karya</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ── KARTU LAMPIRAN BERKAS DOKUMEN / MODUL / KODE SUMBER ── --}}
            @if($post->attachment_url)
                <div class="mb-4 p-3 rounded-2xl bg-slate-50 border-2 border-slate-200 hover:border-indigo-300 transition flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-base shrink-0 font-black">
                            📎
                        </div>
                        <div class="min-w-0">
                            <div class="text-[10px] font-black uppercase tracking-wider text-indigo-700">Lampiran Dokumen / Modul / Berkas</div>
                            <div class="text-xs font-black text-slate-950 truncate" title="{{ $post->attachment_name ?: basename($post->attachment_path) }}">
                                {{ $post->attachment_name ?: basename($post->attachment_path) }}
                            </div>
                        </div>
                    </div>
                    <a href="{{ $post->attachment_url }}" target="_blank" download="{{ $post->attachment_name ?: true }}"
                       class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black shrink-0 transition flex items-center gap-1.5 shadow-2xs">
                        <i class="fas fa-download text-[10px]"></i>
                        <span>Unduh</span>
                    </a>
                </div>
            @endif
        </div>

        {{-- BLOK KOLASE MEDIA --}}
        @if($mediaCount > 0)
            <div class="mb-4 {{ $layout === 'top' ? 'order-1' : 'order-2' }}">
                {{-- KASUS A: 1 MEDIA SAJA (HERO BANNER / VIDEO / AUDIO VINYL) --}}
                @if($mediaCount === 1)
                    @php
                        $m = $mediaItems->first();
                        $caption = $m->metadata['caption'] ?? null;
                    @endphp
                    <div class="rounded-3xl overflow-hidden bg-slate-950 border-2 border-[#f1e8dc] shadow-xs">
                        @if($m->media_type === 'image')
                            <div class="relative group cursor-pointer max-h-[520px] flex items-center justify-center overflow-hidden"
                                 @click="openLightbox(0)">
                                <img src="{{ $m->url }}" alt="{{ $caption ?? $post->title ?? 'Dokumentasi' }}"
                                     class="w-full h-auto max-h-[520px] object-cover group-hover:scale-102 transition-transform duration-300"
                                     loading="lazy">
                                <span class="absolute bottom-3 right-3 px-2.5 py-1 rounded-xl bg-black/60 text-white text-[10px] font-black backdrop-blur-xs opacity-0 group-hover:opacity-100 transition">
                                    🔍 Perbesar Foto
                                </span>
                            </div>
                        @elseif($m->media_type === 'video')
                            <video src="{{ $m->url }}" controls preload="metadata" class="w-full max-h-[520px] bg-black"></video>
                        @elseif($m->media_type === 'audio')
                            <div class="w-full p-6 bg-gradient-to-br from-purple-900 via-slate-900 to-pink-950 flex flex-col items-center text-center text-white"
                                 x-data="{ isPlayingAudio: false }"
                                 x-init="$refs.singleAudio?.addEventListener('play', () => isPlayingAudio = true); $refs.singleAudio?.addEventListener('pause', () => isPlayingAudio = false); $refs.singleAudio?.addEventListener('ended', () => isPlayingAudio = false);">
                                <div class="relative w-20 h-20 rounded-full bg-[#111827] p-2 border-2 border-purple-400 shadow-xl flex items-center justify-center mb-3"
                                     :class="isPlayingAudio ? 'animate-spin' : ''"
                                     style="animation-duration: 4s;">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-r from-pink-500 to-purple-600 flex items-center justify-center text-white text-xs font-black shadow-inner">
                                        🎵
                                    </div>
                                </div>
                                <div class="text-xs font-black text-white mb-2">{{ $caption ?? $post->title ?? 'Rekaman Audio / Lagu' }}</div>
                                <audio x-ref="singleAudio" src="{{ $m->url }}" controls preload="none" class="w-full max-w-md"></audio>
                            </div>
                        @endif

                        @if($caption)
                            <div class="px-4 py-2.5 bg-[#faf8f5] border-t border-[#f1e8dc] text-xs font-bold text-slate-800 flex items-center gap-2">
                                <i class="fas fa-camera text-pink-500 text-[11px]"></i>
                                <span>{{ $caption }}</span>
                            </div>
                        @endif
                    </div>

                {{-- KASUS B: 2 MEDIA (SPLIT 2 KOLOM SIMETRIS) --}}
                @elseif($mediaCount === 2)
                    <div class="grid grid-cols-2 gap-2.5 rounded-3xl overflow-hidden">
                        @foreach($mediaItems as $idx => $m)
                            @php $caption = $m->metadata['caption'] ?? null; @endphp
                            <div class="relative group rounded-2xl overflow-hidden border-2 border-[#f1e8dc] bg-slate-950 aspect-[4/3] flex flex-col justify-end">
                                @if($m->media_type === 'image')
                                    <img src="{{ $m->url }}" alt="Foto {{ $idx + 1 }}"
                                         @click="openLightbox({{ $idx }})"
                                         class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 cursor-pointer"
                                         loading="lazy">
                                @elseif($m->media_type === 'video')
                                    <video src="{{ $m->url }}" controls class="absolute inset-0 w-full h-full object-cover"></video>
                                @endif
                                <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-lg bg-black/60 text-white text-[10px] font-black backdrop-blur-xs pointer-events-none">
                                    #{{ $idx + 1 }}
                                </span>
                                @if($caption)
                                    <div class="relative z-10 p-2 bg-gradient-to-t from-black/85 via-black/50 to-transparent text-white text-[11px] font-bold truncate pointer-events-none">
                                        {{ $caption }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                {{-- KASUS C: 3 MEDIA (BENTO COLLAGE: 1 BESAR UTAMA + 2 PENDAMPING) --}}
                @elseif($mediaCount === 3)
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        {{-- Foto Utama #1 (2 Kolom) --}}
                        @php
                            $first = $mediaItems[0];
                            $cap0 = $first->metadata['caption'] ?? null;
                        @endphp
                        <div class="sm:col-span-2 relative group rounded-2xl overflow-hidden border-2 border-[#f1e8dc] bg-slate-950 aspect-[4/3] sm:aspect-auto sm:min-h-[300px] flex flex-col justify-end">
                            @if($first->media_type === 'image')
                                <img src="{{ $first->url }}" alt="Foto Utama"
                                     @click="openLightbox(0)"
                                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 cursor-pointer"
                                     loading="lazy">
                            @elseif($first->media_type === 'video')
                                <video src="{{ $first->url }}" controls class="absolute inset-0 w-full h-full object-cover"></video>
                            @endif
                            <span class="absolute top-2.5 left-2.5 px-2.5 py-0.5 rounded-lg bg-pink-600 text-white text-[10px] font-black shadow-xs pointer-events-none">
                                ⭐ Foto Utama #1
                            </span>
                            @if($cap0)
                                <div class="relative z-10 p-2.5 bg-gradient-to-t from-black/85 via-black/50 to-transparent text-white text-xs font-bold truncate pointer-events-none">
                                    {{ $cap0 }}
                                </div>
                            @endif
                        </div>

                        {{-- 2 Foto Pendamping di Kanan --}}
                        <div class="grid grid-cols-2 sm:grid-cols-1 gap-2.5">
                            @foreach([$mediaItems[1], $mediaItems[2]] as $subIdx => $m)
                                @php
                                    $realIdx = $subIdx + 1;
                                    $cap = $m->metadata['caption'] ?? null;
                                @endphp
                                <div class="relative group rounded-2xl overflow-hidden border-2 border-[#f1e8dc] bg-slate-950 aspect-[4/3] flex flex-col justify-end">
                                    @if($m->media_type === 'image')
                                        <img src="{{ $m->url }}" alt="Foto {{ $realIdx + 1 }}"
                                             @click="openLightbox({{ $realIdx }})"
                                             class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 cursor-pointer"
                                             loading="lazy">
                                    @elseif($m->media_type === 'video')
                                        <video src="{{ $m->url }}" controls class="absolute inset-0 w-full h-full object-cover"></video>
                                    @endif
                                    <span class="absolute top-2 left-2 px-2 py-0.5 rounded-lg bg-black/60 text-white text-[10px] font-black pointer-events-none">
                                        #{{ $realIdx + 1 }}
                                    </span>
                                    @if($cap)
                                        <div class="relative z-10 p-2 bg-gradient-to-t from-black/85 via-black/50 to-transparent text-white text-[10px] font-bold truncate pointer-events-none">
                                            {{ $cap }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                {{-- KASUS D: 4 ATAU LEBIH MEDIA (GRID 2x2 + OVERLAY +N FOTO LAINNYA) --}}
                @else
                    <div class="grid grid-cols-2 gap-2.5">
                        @foreach($mediaItems->take(4) as $idx => $m)
                            @php
                                $cap = $m->metadata['caption'] ?? null;
                                $extraCount = $mediaCount - 4;
                            @endphp
                            <div class="relative group rounded-2xl overflow-hidden border-2 border-[#f1e8dc] bg-slate-950 aspect-[4/3] flex flex-col justify-end">
                                @if($m->media_type === 'image')
                                    <img src="{{ $m->url }}" alt="Foto {{ $idx + 1 }}"
                                         @click="openLightbox({{ $idx }})"
                                         class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 cursor-pointer"
                                         loading="lazy">
                                @elseif($m->media_type === 'video')
                                    <video src="{{ $m->url }}" controls class="absolute inset-0 w-full h-full object-cover"></video>
                                @endif

                                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-lg bg-black/60 text-white text-[10px] font-black pointer-events-none">
                                    #{{ $idx + 1 }}
                                </span>

                                {{-- Overlay +N jika ini foto ke-4 dan total > 4 --}}
                                @if($idx === 3 && $extraCount > 0)
                                    <button type="button"
                                            @click="openLightbox(3)"
                                            class="absolute inset-0 z-20 bg-black/70 hover:bg-black/60 backdrop-blur-[2px] flex flex-col items-center justify-center text-white transition cursor-pointer">
                                        <span class="font-display font-black text-2xl sm:text-3xl">+{{ $extraCount }}</span>
                                        <span class="text-xs font-bold uppercase tracking-wider mt-1">Lihat Semua ({{ $mediaCount }} Foto)</span>
                                    </button>
                                @elseif($cap)
                                    <div class="relative z-10 p-2 bg-gradient-to-t from-black/85 via-black/50 to-transparent text-white text-[11px] font-bold truncate pointer-events-none">
                                        {{ $cap }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Footer Bar Kolase jika lebih dari 1 foto --}}
                @if($mediaCount > 1)
                    <div class="mt-2 flex items-center justify-between text-[11px] font-bold text-slate-600 px-1">
                        <span>📸 Kolase Dokumentasi ({{ $mediaCount }} Berkas Media)</span>
                        <button type="button" @click="openLightbox(0)" class="text-pink-600 hover:text-pink-800 font-black cursor-pointer flex items-center gap-1">
                            <i class="fas fa-expand text-[10px]"></i>
                            <span>Buka Layar Penuh</span>
                        </button>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- ── 5. LIGHTBOX MODAL FULLSCREEN UNTUK MELIHAT SEMUA FOTO & CAPTION ── --}}
    <div x-show="lightboxOpen" x-cloak
         @keydown.escape.window="lightboxOpen = false"
         class="fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex flex-col items-center justify-center p-4"
         x-transition>
        <div class="w-full max-w-4xl flex items-center justify-between text-white mb-3 px-2">
            <div class="text-xs font-black bg-white/15 px-3 py-1.5 rounded-full">
                Foto <span x-text="activeSlide + 1"></span> dari <span x-text="slides.length"></span>
            </div>
            <button type="button" @click="lightboxOpen = false" class="w-9 h-9 rounded-full bg-white/15 hover:bg-rose-600 text-white flex items-center justify-center font-black cursor-pointer">
                ✕
            </button>
        </div>

        <div class="relative w-full max-w-4xl flex-1 flex items-center justify-center overflow-hidden">
            <template x-if="slides.length > 1">
                <button type="button" @click="prevSlide()" class="absolute left-2 z-20 w-10 h-10 rounded-full bg-black/60 hover:bg-pink-600 text-white flex items-center justify-center text-sm cursor-pointer shadow-lg">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </template>

            <template x-if="slides[activeSlide]">
                <div class="max-h-[75vh] w-full flex flex-col items-center justify-center">
                    <template x-if="slides[activeSlide].type === 'image'">
                        <img :src="slides[activeSlide].url" class="max-h-[68vh] max-w-full object-contain rounded-2xl shadow-2xl">
                    </template>
                    <template x-if="slides[activeSlide].type === 'video'">
                        <video :src="slides[activeSlide].url" controls class="max-h-[68vh] max-w-full rounded-2xl"></video>
                    </template>
                    <div class="mt-3 px-4 py-2 rounded-2xl bg-white/10 text-white text-xs font-bold text-center max-w-xl"
                         x-text="slides[activeSlide].caption"></div>
                </div>
            </template>

            <template x-if="slides.length > 1">
                <button type="button" @click="nextSlide()" class="absolute right-2 z-20 w-10 h-10 rounded-full bg-black/60 hover:bg-pink-600 text-white flex items-center justify-center text-sm cursor-pointer shadow-lg">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </template>
        </div>
    </div>

</div>
