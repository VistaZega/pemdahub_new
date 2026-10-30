@extends('circle.layout')

@section('title', ($profile->nickname ?? $user->name) . ' (@' . $profile->username . ') — PembdaCIRCLE')

@section('og_meta')
    <meta property="og:title" content="{{ $user->name }} di PembdaCIRCLE">
    <meta property="og:description" content="{{ $profile->bio ?? 'Lihat portofolio, kreasi, dan ekspresi resmi di Perguruan Pembda.' }}">
    <meta property="og:image" content="{{ $user->avatar_url ?? asset('images/default-avatar.svg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="profile">
@endsection

@section('content')
<div class="max-w-4xl mx-auto" x-data="{ activeTab: '{{ request('tab', 'wall') }}', showEditModal: false, showEndorseModal: false, selectedSkill: '' }">

    {{-- ═══════════════════════════════════════════
         PROFILE HEADER CARD (PASTEL CANTIK & LUCU)
    ═══════════════════════════════════════════ --}}
    <div class="bg-white rounded-3xl border-2 border-[#f1e8dc] shadow-xs overflow-hidden mb-7">

        {{-- ── 1. Cover Banner (Gradien Pastel Lembut & Manis) ── --}}
        <div class="h-48 sm:h-64 w-full relative overflow-hidden bg-gradient-to-r from-[#fce7f3] via-[#fffbeb] to-[#e0f2fe]">
            @if($profile->banner_path)
                <img src="{{ asset('storage/' . $profile->banner_path) }}" 
                     alt="Cover banner"
                     class="w-full h-full object-cover">
            @else
                {{-- Pola Polkadot Estetik Pastel --}}
                <div class="absolute inset-0 opacity-40 bg-[radial-gradient(#ec4899_1.5px,transparent_1.5px)] [background-size:20px_20px]"></div>
                <div class="absolute -right-16 -bottom-16 w-72 h-72 bg-pink-200/50 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute -left-16 -top-16 w-72 h-72 bg-sky-200/50 rounded-full blur-2xl pointer-events-none"></div>
            @endif

            {{-- School Unit Badge on Top Right (Pastel Cantik) --}}
            <div class="absolute top-4 right-4 flex items-center gap-2">
                @if($user->school)
                    <span class="px-4 py-1.5 rounded-2xl text-xs font-black uppercase tracking-wider text-slate-950 bg-white/90 backdrop-blur-md border-2 border-pink-200 shadow-xs flex items-center gap-1.5">
                        <i class="fas fa-school text-pink-600"></i>
                        <span>{{ $user->school->name }}</span>
                    </span>
                @endif
            </div>
        </div>

        {{-- ── 2. Profile Details & Avatar Overlap ── --}}
        <div class="px-6 pb-6 pt-0 relative">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-5 -mt-16 sm:-mt-20 mb-5">
                {{-- Avatar & Identity --}}
                <div class="flex items-end gap-4 min-w-0">
                    <div class="relative shrink-0">
                        <div class="w-28 h-28 sm:w-36 sm:h-36 rounded-3xl p-1 bg-white border-4 border-white shadow-md">
                            <img src="{{ $user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                 alt="{{ $user->name }}" 
                                 class="w-full h-full object-cover rounded-2xl bg-pink-50">
                        </div>
                        @if($activeStories->isNotEmpty())
                            {{-- Story Active Ring Indicator Lucu --}}
                            <div class="absolute -bottom-1 -right-1 w-9 h-9 rounded-full pastel-story-ring text-white flex items-center justify-center text-xs shadow-md border-2 border-white animate-pulse" 
                                 title="Memiliki Story 24 Jam">
                                <i class="fas fa-bolt text-yellow-100"></i>
                            </div>
                        @endif
                    </div>

                    <div class="pb-1 min-w-0">
                        <h1 class="font-display font-black text-xl sm:text-2xl text-slate-950 tracking-tight flex items-center gap-2 truncate">
                            <span>{{ $user->name }}</span>
                            @if($user->role === 'guru')
                                <span class="w-6 h-6 rounded-full bg-sky-100 text-sky-700 flex items-center justify-center text-xs shadow-2xs" title="Pendidik Resmi Terverifikasi">
                                    <i class="fas fa-certificate"></i>
                                </span>
                            @else
                                <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs shadow-2xs" title="Siswa Aktif Pembda">
                                    <i class="fas fa-check"></i>
                                </span>
                            @endif
                        </h1>
                        <div class="flex items-center gap-2 text-xs font-bold text-slate-700 mt-1 flex-wrap">
                            <span class="text-pink-600 bg-pink-50 px-2.5 py-0.5 rounded-lg border border-pink-200">@<span>{{ $profile->username }}</span></span>
                            <span>•</span>
                            <span class="text-slate-900">{{ $user->role === 'siswa' ? 'Siswa Aktif' : ($user->role === 'guru' ? 'Guru / Mentor' : 'Warga Pembda') }}</span>
                            @if($user->student?->classroom)
                                <span>•</span>
                                <span class="bg-[#faedcd] text-[#7f5539] font-black px-2.5 py-0.5 rounded-lg border border-[#d4a373]">
                                    {{ $user->student->classroom->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Action Bar: Check-out to Dashboard, Share, Edit & View Stats --}}
                <div class="flex items-center gap-2 self-start sm:self-end shrink-0 flex-wrap">
                    @auth
                        @php
                            $__currUser = Auth::user();
                            $__dashUrl = route('admin.dashboard');
                            if ($__currUser->role === 'siswa') {
                                $__dashUrl = route('siswa.dashboard');
                            } elseif ($__currUser->role === 'guru' || $__currUser->role === 'pegawai') {
                                $__dashUrl = route('guru.dashboard');
                            } elseif ($__currUser->role === 'orang_tua') {
                                $__dashUrl = route('orangtua.dashboard');
                            } elseif ($__currUser->isKetuaYayasan() || $__currUser->hasRole('ketua_yayasan')) {
                                $__dashUrl = route('yayasan.dashboard');
                            }
                        @endphp
                        {{-- LINK MORAK & CERAH: KEMBALI KE MEJA KERJA DASHBOARD --}}
                        <a href="{{ $__dashUrl }}" 
                           title="Keluar dari CIRCLE dan Kembali ke Meja Kerja Dashboard Utama"
                           class="px-3.5 py-2 rounded-2xl text-xs font-black uppercase tracking-wider text-black border-2 border-black transition-all flex items-center gap-1.5 shadow-md cursor-pointer hover:scale-105"
                           style="background: linear-gradient(135deg, #ffd600 0%, #34d399 100%) !important; color: #000000 !important; border: 2.5px solid #000000 !important; box-shadow: 0 4px 12px rgba(52, 211, 153, 0.45) !important;">
                            <i class="fas fa-compass text-xs text-black"></i>
                            <span>KEMBALI KE MEJA KERJA</span>
                        </a>
                    @endauth

                    @if(Auth::check() && Auth::id() === $user->id)
                        <button @click="showEditModal = true" 
                                class="px-3.5 py-2 rounded-2xl text-xs font-black text-slate-950 bg-amber-100 hover:bg-amber-200 border-2 border-amber-300 transition-base flex items-center gap-1.5 shadow-xs cursor-pointer hover:scale-105">
                            <i class="fas fa-pen text-amber-700 text-xs"></i>
                            <span>Edit Profil</span>
                        </button>
                    @endif

                    @if(Auth::check() && Auth::id() !== $user->id)
                        <button type="button"
                                onclick="window.openCircleChatWith({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->avatar_url }}', '{{ $profile->username ?? $user->username }}')"
                                class="px-3.5 py-2 rounded-2xl text-xs font-black text-white bg-sky-600 hover:bg-sky-500 border-2 border-sky-700 transition-base flex items-center gap-1.5 shadow-xs cursor-pointer hover:scale-105 active:scale-95"
                                title="Kirim Pesan Pribadi Langsung ke {{ $user->name }}">
                            <i class="fas fa-paper-plane text-xs"></i>
                            <span>Bisik</span>
                        </button>
                    @endif

                    <button onclick="shareProfile()" 
                            class="px-3.5 py-2 rounded-2xl text-xs font-black text-slate-950 bg-emerald-100 hover:bg-emerald-200 border-2 border-emerald-300 transition-base flex items-center gap-1.5 shadow-xs cursor-pointer hover:scale-105">
                            <i class="fab fa-whatsapp text-emerald-700 text-sm"></i>
                        <span>Siarkan</span>
                    </button>
                    <div class="px-3.5 py-2 rounded-2xl bg-pink-100 border-2 border-pink-200 text-slate-950 text-xs font-black flex items-center gap-1.5 shadow-xs" 
                         title="Total Kunjungan Profil">
                        <i class="fas fa-eye text-pink-600 text-xs"></i>
                        <span>{{ number_format($profile->views_count) }}</span>
                    </div>
                </div>
            </div>

            {{-- Mood & Vibe Barometer (Kuning Pastel Manis) --}}
            @if($profile->mood_text)
                <div class="mb-4 inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-50 border-2 border-amber-200 text-slate-950 text-xs font-black shadow-2xs">
                    <span class="text-base">{{ $profile->mood_emoji ?? '✨' }}</span>
                    <span>{{ $profile->mood_text }}</span>
                </div>
            @endif

            {{-- Bio Singkat (Hitam Pekat & Jelas Terbaca) --}}
            <p class="text-sm font-semibold text-slate-950 leading-relaxed max-w-2xl mb-4">
                {{ $profile->bio ?? 'Belum ada biodata singkat.' }}
            </p>

            {{-- ── 3. LOKER (KHUSUS PEMILIK SAAT LOGIN) ── --}}
            @if(Auth::check() && Auth::id() === $user->id)
                <div class="mt-4 mb-3 p-4 rounded-3xl bg-gradient-to-r from-pink-50 via-amber-50 to-sky-50 border-2 border-pink-200/90 shadow-xs">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-black uppercase tracking-wider text-slate-950 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-pink-200 text-pink-800 flex items-center justify-center text-xs">
                                <i class="fas fa-lock"></i>
                            </span>
                            <span>Loker</span>
                        </span>
                        <span class="text-[10px] font-black text-slate-900 bg-white px-2.5 py-0.5 rounded-full border border-pink-200 shadow-2xs">
                            🔒 Privat
                        </span>
                    </div>

                    {{-- Horizontal Quick Links ke Seluruh Side Menu Akademik --}}
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 custom-scrollbar">
                        @if($user->role === 'siswa')
                            <a href="{{ route('siswa.nilai') }}" class="px-3.5 py-2 rounded-2xl bg-white hover:bg-emerald-50 border-2 border-emerald-200 text-xs font-black text-slate-950 flex items-center gap-2 shrink-0 shadow-2xs hover:scale-105 transition">
                                <i class="fas fa-chart-bar text-emerald-600"></i>
                                <span>Nilai & Rapor</span>
                            </a>
                            <a href="{{ route('siswa.jadwal') }}" class="px-3.5 py-2 rounded-2xl bg-white hover:bg-sky-50 border-2 border-sky-200 text-xs font-black text-slate-950 flex items-center gap-2 shrink-0 shadow-2xs hover:scale-105 transition">
                                <i class="fas fa-calendar-alt text-sky-600"></i>
                                <span>Jadwal Pelajaran</span>
                            </a>
                            <a href="{{ route('siswa.tagihan') }}" class="px-3.5 py-2 rounded-2xl bg-white hover:bg-rose-50 border-2 border-rose-200 text-xs font-black text-slate-950 flex items-center gap-2 shrink-0 shadow-2xs hover:scale-105 transition">
                                <i class="fas fa-file-invoice-dollar text-rose-600"></i>
                                <span>Biaya SPP</span>
                            </a>
                            @if(\App\Models\Setting::getValue('siswa_view_attendance_recap', true))
                            <a href="{{ route('siswa.absensi') }}" class="px-3.5 py-2 rounded-2xl bg-white hover:bg-amber-50 border-2 border-amber-200 text-xs font-black text-slate-950 flex items-center gap-2 shrink-0 shadow-2xs hover:scale-105 transition">
                                <i class="fas fa-fingerprint text-amber-600"></i>
                                <span>Presensi</span>
                            </a>
                            @endif
                            <a href="{{ route('siswa.dna.index') }}" class="px-3.5 py-2 rounded-2xl bg-white hover:bg-purple-50 border-2 border-purple-200 text-xs font-black text-slate-950 flex items-center gap-2 shrink-0 shadow-2xs hover:scale-105 transition">
                                <i class="fas fa-dna text-purple-600"></i>
                                <span>DNA Akademik</span>
                            </a>
                            <a href="{{ route('siswa.ekskul.index') }}" class="px-3.5 py-2 rounded-2xl bg-white hover:bg-orange-50 border-2 border-orange-200 text-xs font-black text-slate-950 flex items-center gap-2 shrink-0 shadow-2xs hover:scale-105 transition">
                                <i class="fas fa-palette text-orange-600"></i>
                                <span>Ekstrakurikuler</span>
                            </a>
                            <a href="{{ route('siswa.dashboard') }}" class="px-3.5 py-2 rounded-2xl bg-white hover:bg-slate-100 border-2 border-slate-300 text-xs font-black text-slate-950 flex items-center gap-2 shrink-0 shadow-2xs hover:scale-105 transition">
                                <i class="fas fa-columns text-slate-700"></i>
                                <span>Dashboard Menu</span>
                            </a>
                        @elseif($user->role === 'guru')
                            <a href="{{ route('guru.jadwal') }}" class="px-3.5 py-2 rounded-2xl bg-white hover:bg-sky-50 border-2 border-sky-200 text-xs font-black text-slate-950 flex items-center gap-2 shrink-0 shadow-2xs hover:scale-105 transition">
                                <i class="fas fa-calendar-alt text-sky-600"></i>
                                <span>Jadwal Mengajar</span>
                            </a>
                            <a href="{{ route('guru.kelas') }}" class="px-3.5 py-2 rounded-2xl bg-white hover:bg-indigo-50 border-2 border-indigo-200 text-xs font-black text-slate-950 flex items-center gap-2 shrink-0 shadow-2xs hover:scale-105 transition">
                                <i class="fas fa-users text-indigo-600"></i>
                                <span>Kelas Saya</span>
                            </a>
                            <a href="{{ route('guru.dashboard') }}" class="px-3.5 py-2 rounded-2xl bg-white hover:bg-slate-100 border-2 border-slate-300 text-xs font-black text-slate-950 flex items-center gap-2 shrink-0 shadow-2xs hover:scale-105 transition">
                                <i class="fas fa-columns text-slate-700"></i>
                                <span>Dashboard Guru</span>
                            </a>
                        @else
                            <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-2 rounded-2xl bg-white hover:bg-slate-100 border-2 border-slate-300 text-xs font-black text-slate-950 flex items-center gap-2 shrink-0 shadow-2xs hover:scale-105 transition">
                                <i class="fas fa-columns text-slate-700"></i>
                                <span>Dashboard Panel</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Social Media Links --}}
            @if(!empty($profile->social_links))
                <div class="flex items-center gap-3 text-sm text-slate-600 mb-2">
                    @foreach($profile->social_links as $plat => $url)
                        @if($url)
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" 
                               class="hover:text-pink-600 transition-colors p-1.5 rounded-xl bg-slate-50 border border-slate-200" 
                               title="{{ ucfirst($plat) }}">
                                <i class="fab fa-{{ $plat }}"></i>
                            </a>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── 4. Dual-Tab Navigasi: Nongki vs Karya (Pastel Coklat vs Kuning) ── --}}
        <div class="flex border-t-2 border-[#f1e8dc] bg-[#faf8f5] px-6">
            {{-- Tab Nongki (Coklat Pastel) --}}
            <button @click="activeTab = 'wall'" 
                    :class="activeTab === 'wall' ? 'border-[#b08968] text-slate-950 font-black bg-white shadow-xs' : 'border-transparent text-slate-700 hover:text-slate-950 font-bold'"
                    class="flex items-center gap-2 py-4 px-5 border-b-3 text-sm transition-base -mb-[2px] cursor-pointer rounded-t-2xl">
                <span class="w-6 h-6 rounded-lg bg-[#faedcd] text-[#7f5539] flex items-center justify-center text-xs">
                    <i class="fas fa-mug-hot"></i>
                </span>
                <span class="font-display">Nongki</span>
                <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] bg-[#faedcd] text-[#7f5539] font-black border border-[#d4a373]">
                    {{ $wallPosts->count() }}
                </span>
            </button>
            {{-- Tab Karya (Kuning Pastel) --}}
            <button @click="activeTab = 'portfolio'" 
                    :class="activeTab === 'portfolio' ? 'border-amber-400 text-slate-950 font-black bg-white shadow-xs' : 'border-transparent text-slate-700 hover:text-slate-950 font-bold'"
                    class="flex items-center gap-2 py-4 px-5 border-b-3 text-sm transition-base -mb-[2px] cursor-pointer rounded-t-2xl">
                <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center text-xs">
                    <i class="fas fa-trophy"></i>
                </span>
                <span class="font-display">Karya</span>
                <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] bg-amber-100 text-amber-900 border border-amber-300 font-black">
                    {{ $portfolioPosts->count() + $finalProjects->count() + $approvedPklLogs->count() }}
                </span>
            </button>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         KOTAK BUAT STATUS / POSTING KARYA (KHUSUS PEMILIK AKUN)
    ═══════════════════════════════════════════ --}}
    @if(Auth::check() && Auth::id() === $user->id)
        <div class="bg-white rounded-3xl border-2 border-pink-200 p-5 sm:p-6 shadow-xs mb-6" 
             x-data="{ 
                isSubmitting: false,
                postType: 'status', 
                tabCat: 'wall', 
                showAttach: false,
                postContent: '',
                hasPoll: false,
                pollOptions: ['', ''],
                addPollOption() { if (this.pollOptions.length < 4) this.pollOptions.push(''); },
                removePollOption(idx) { if (this.pollOptions.length > 2) this.pollOptions.splice(idx, 1); },
                setPrompt(text) {
                    this.postContent = text;
                    this.$nextTick(() => {
                        if (this.$refs.contentInput) this.$refs.contentInput.focus();
                    });
                }
             }">
            <div class="flex items-center gap-3.5 mb-3">
                <img src="{{ $user->avatar_url ?? asset('images/default-avatar.svg') }}" class="w-11 h-11 rounded-2xl object-cover ring-2 ring-pink-100">
                <div>
                    <h4 class="text-sm font-black text-slate-950">Hai {{ explode(' ', $user->name)[0] }}! ✨</h4>
                    <span class="text-xs font-bold text-slate-600">Mau nulis apa hari ini?</span>
                </div>
            </div>

            <form action="{{ route('circle.post.store') }}" method="POST" enctype="multipart/form-data"
                  @submit="if(isSubmitting) { $event.preventDefault(); return false; } isSubmitting = true;">
                @csrf
                <input type="hidden" name="tab_category" :value="tabCat">
                <input type="hidden" name="post_type" :value="postType">

                {{-- Pilihan Format Postingan (1 Kata: Nongki, Kilas, Karya, Reels) --}}
                <div class="flex items-center gap-2 mb-3 overflow-x-auto pb-1">
                    <button type="button" @click="tabCat = 'wall'; postType = 'status'"
                            :class="tabCat === 'wall' && postType === 'status' ? 'bg-[#faedcd] text-slate-950 border-[#d4a373]' : 'bg-slate-100 text-slate-700 border-slate-200'"
                            class="px-3.5 py-2 rounded-2xl text-xs font-black border-2 transition flex items-center gap-2 shrink-0 cursor-pointer">
                        <i class="fas fa-mug-hot text-[#7f5539]"></i>
                        <span>Nongki</span>
                    </button>
                    <button type="button" @click="tabCat = 'wall'; postType = 'story'"
                            :class="postType === 'story' ? 'bg-pink-100 text-slate-950 border-pink-300' : 'bg-slate-100 text-slate-700 border-slate-200'"
                            class="px-3.5 py-2 rounded-2xl text-xs font-black border-2 transition flex items-center gap-2 shrink-0 cursor-pointer">
                        <i class="fas fa-bolt text-pink-600"></i>
                        <span>Kilas</span>
                    </button>
                    <button type="button" @click="tabCat = 'portfolio'; postType = 'project'"
                            :class="tabCat === 'portfolio' ? 'bg-amber-100 text-slate-950 border-amber-300' : 'bg-slate-100 text-slate-700 border-slate-200'"
                            class="px-3.5 py-2 rounded-2xl text-xs font-black border-2 transition flex items-center gap-2 shrink-0 cursor-pointer">
                        <i class="fas fa-trophy text-amber-700"></i>
                        <span>Karya</span>
                    </button>
                    <button type="button" @click="tabCat = 'portfolio'; postType = 'reel'"
                            :class="postType === 'reel' ? 'bg-sky-100 text-slate-950 border-sky-300' : 'bg-slate-100 text-slate-700 border-slate-200'"
                            class="px-3.5 py-2 rounded-2xl text-xs font-black border-2 transition flex items-center gap-2 shrink-0 cursor-pointer">
                        <i class="fas fa-play text-sky-700"></i>
                        <span>Reels</span>
                    </button>
                </div>

                <div x-show="tabCat === 'portfolio'" class="mb-2.5">
                    <input type="text" name="title" placeholder="Judul Karya / Proyek Utama..." class="w-full px-4 py-2.5 rounded-2xl bg-amber-50/50 border-2 border-amber-200 text-sm font-black text-slate-950 placeholder:text-slate-400 focus:outline-none focus:border-amber-400">
                </div>

                {{-- 💡 Ide Spontan Cepat --}}
                <div class="mb-2.5 flex items-center gap-1.5 overflow-x-auto pb-1 custom-scrollbar">
                    <button type="button" @click="setPrompt('Lagi nongki santai di kantin bareng bestie ☕ Spill jajan kalian!')"
                            class="px-2.5 py-1 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-200 text-[11px] font-bold text-slate-900 shrink-0">
                        ☕ Nongki Kantin
                    </button>
                    <button type="button" @click="setPrompt('Keringat anak bengkel/lab hari ini 🛠️ Karya siap dipamerkan!')"
                            class="px-2.5 py-1 rounded-xl bg-orange-50 hover:bg-orange-100 border border-orange-200 text-[11px] font-bold text-slate-900 shrink-0">
                        🛠️ Bengkel/Lab
                    </button>
                    <button type="button" @click="setPrompt('Lagu yang nemenin belajar malam ini 🎧🎶')"
                            class="px-2.5 py-1 rounded-xl bg-pink-50 hover:bg-pink-100 border border-pink-200 text-[11px] font-bold text-slate-900 shrink-0">
                        🎧 Lagu Favorit
                    </button>
                    <button type="button" @click="setPrompt('Pusing dikit nggak ngaruh 📚 Yang penting tugas beres!')"
                            class="px-2.5 py-1 rounded-xl bg-sky-50 hover:bg-sky-100 border border-sky-200 text-[11px] font-bold text-slate-900 shrink-0">
                        📚 Semangat Belajar
                    </button>
                </div>

                <textarea name="content" rows="3" 
                          x-ref="contentInput"
                          x-model="postContent"
                          placeholder="Tuliskan cerita, curhat santai, atau penjelasan karyamu..." 
                          class="w-full p-4 rounded-2xl bg-slate-50 border-2 border-slate-200 text-sm font-bold text-slate-950 placeholder:text-slate-400 focus:outline-none focus:border-pink-300 resize-none mb-3"></textarea>

                {{-- Kotak Input Polling Interaktif --}}
                <div x-show="hasPoll" x-collapse x-transition class="mb-3.5 p-4 rounded-2xl bg-purple-50/70 border-2 border-purple-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase tracking-wider text-purple-950 flex items-center gap-1.5">
                            <span>📊</span>
                            <span>Bikin Polling / Jajak Pendapat</span>
                        </span>
                        <button type="button" @click="hasPoll = false" class="text-xs font-black text-rose-600 hover:text-rose-800 cursor-pointer">
                            ✕ Batal
                        </button>
                    </div>

                    <div>
                        <input type="text" name="poll_question" placeholder="Pertanyaan Polling..." 
                               class="w-full px-3.5 py-2 rounded-xl bg-white border border-purple-200 text-xs font-bold text-slate-950 focus:outline-none focus:border-purple-400">
                    </div>

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
                    <div class="mt-2" x-show="pollOptions.length < 4">
                        <button type="button" @click="addPollOption()" class="text-xs font-black text-purple-700 hover:text-purple-900 flex items-center gap-1.5 cursor-pointer">
                            <i class="fas fa-plus-circle"></i>
                            <span>Tambah Pilihan</span>
                        </button>
                    </div>
                </div>

                {{-- Action Bar Form Posting --}}
                <div class="flex items-center justify-between flex-wrap gap-2 pt-2 border-t border-slate-100">
                    <div class="flex items-center gap-2 flex-wrap">
                        <label class="px-3.5 py-2 rounded-2xl bg-pink-50 hover:bg-pink-100 border border-pink-200 text-slate-950 text-xs font-black cursor-pointer flex items-center gap-2 transition">
                            <i class="fas fa-image text-pink-600 text-sm"></i>
                            <span>Foto / Video</span>
                            <input type="file" name="media" class="hidden" accept="image/*,video/*" @change="showAttach = true">
                        </label>
                        <button type="button" @click="hasPoll = !hasPoll"
                                :class="hasPoll ? 'bg-purple-100 border-purple-400 text-purple-950 font-black' : 'bg-purple-50 hover:bg-purple-100 border-purple-200 text-slate-950 font-bold'"
                                class="px-3.5 py-2 rounded-2xl border text-xs cursor-pointer transition flex items-center gap-1.5">
                            <span>📊</span>
                            <span>Polling</span>
                        </button>
                        <span x-show="showAttach" class="text-[11px] font-black text-emerald-800 bg-emerald-100 px-2.5 py-1 rounded-xl border border-emerald-300">
                            ✓ Media Terpilih
                        </span>
                    </div>

                    <button type="submit" 
                            :disabled="isSubmitting"
                            class="px-5 py-2.5 rounded-2xl text-xs font-black text-slate-950 bg-pink-200 hover:bg-pink-300 border-2 border-pink-300 shadow-xs transition hover:scale-105 flex items-center gap-2 cursor-pointer"
                            :class="isSubmitting ? 'opacity-60 cursor-not-allowed scale-100 hover:scale-100' : ''">
                        <i x-show="!isSubmitting" class="fas fa-paper-plane text-pink-700"></i>
                        <i x-show="isSubmitting" class="fas fa-spinner fa-spin text-pink-700"></i>
                        <span x-show="!isSubmitting">Terbitkan ke CIRCLE ✨</span>
                        <span x-show="isSubmitting">Menerbitkan... ⏳</span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════
         TAB 1: DINDING NONGKI & MOMEN (THE WALL)
    ═══════════════════════════════════════════ --}}
    <div x-show="activeTab === 'wall'" x-transition class="space-y-6">
        @if($wallPosts->isEmpty())
            <div class="bg-white rounded-3xl border-2 border-[#f1e8dc] p-10 text-center shadow-xs">
                <div class="w-16 h-16 rounded-2xl bg-[#faedcd] text-[#7f5539] border border-[#d4a373] flex items-center justify-center mx-auto mb-3.5 text-2xl shadow-xs">
                    <i class="fas fa-mug-hot"></i>
                </div>
                <h3 class="font-display font-black text-lg text-slate-950 mb-1">Dinding Masih Sepi</h3>
                <p class="text-xs font-bold text-slate-700 max-w-sm mx-auto leading-relaxed">
                    {{ $profile->nickname ?? $user->name }} belum membagikan status nongki atau cerita momen hari ini.
                </p>
            </div>
        @else
            @foreach($wallPosts as $post)
                <article id="post-{{ $post->id }}" x-data="{ showComments: false }" class="bg-white rounded-3xl border-2 {{ request('post') == $post->id ? 'border-pink-500 ring-4 ring-pink-300 shadow-xl' : 'border-[#f1e8dc]' }} p-5 sm:p-6 shadow-xs hover:shadow-md hover:border-pink-200 transition-all duration-200">
                    {{-- Header --}}
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <img src="{{ $user->avatar_url ?? asset('images/default-avatar.svg') }}" 
                                 alt="{{ $user->name }}"
                                 class="w-11 h-11 rounded-2xl object-cover ring-2 ring-pink-100 shrink-0">
                            <div class="min-w-0">
                                <h4 class="font-display font-black text-sm sm:text-base text-slate-950 flex items-center gap-1.5 truncate">
                                    <span>{{ $user->name }}</span>
                                    @if($post->mood_emoji)
                                        <span class="text-xs" title="{{ $post->mood_text }}">{{ $post->mood_emoji }}</span>
                                    @endif
                                </h4>
                                <div class="text-[11px] font-bold text-slate-600 flex items-center gap-1.5 mt-0.5">
                                    <span>{{ $post->created_at->diffForHumans() }}</span>
                                    @if($post->location_tag)
                                        <span>•</span>
                                        <span class="text-pink-700 font-extrabold"><i class="fas fa-location-dot text-[9px] mr-1"></i>{{ $post->location_tag }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Opsi Hapus Postingan (Pemilik / SuperAdmin) --}}
                        @if(Auth::check() && (Auth::id() === $post->user_id || Auth::user()->isSuperAdmin()))
                            <form action="{{ route('circle.post.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Hapus status ini?')" class="shrink-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-xl bg-slate-50 hover:bg-rose-100 text-slate-400 hover:text-rose-600 flex items-center justify-center text-xs transition border border-slate-200 cursor-pointer" title="Hapus Status">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        @endif
                    </div>

                    {{-- Content (Hitam & Sangat Mudah Dibaca) --}}
                    <div class="text-sm font-semibold text-slate-950 leading-relaxed whitespace-pre-line mb-4">
                        {{ $post->content }}
                    </div>

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
                                        <template x-if="!votedOptionId">
                                            <button type="button" @click="vote(opt.id)" :disabled="isVoting"
                                                    class="w-full text-left px-4 py-2.5 rounded-2xl bg-white hover:bg-purple-100 border-2 border-purple-200 hover:border-purple-300 text-xs font-black text-slate-950 transition-all flex items-center justify-between group cursor-pointer hover:scale-[1.01] shadow-2xs">
                                                <span x-text="opt.option_text"></span>
                                                <i class="fas fa-chevron-right text-[10px] text-purple-400 group-hover:text-purple-700"></i>
                                            </button>
                                        </template>

                                        <template x-if="votedOptionId">
                                            <div class="relative overflow-hidden rounded-2xl bg-white border-2 p-2.5 text-xs font-black text-slate-950 shadow-2xs"
                                                 :class="opt.is_selected ? 'border-purple-400 ring-2 ring-purple-200' : 'border-slate-200'">
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

                    {{-- Media --}}
                    @if($post->media->isNotEmpty())
                        <div class="rounded-3xl overflow-hidden mb-4 bg-slate-950 border-2 border-[#f1e8dc] max-h-[460px]">
                            @foreach($post->media as $m)
                                @if($m->media_type === 'image')
                                    <img src="{{ $m->url }}" alt="Media" class="w-full h-auto object-cover max-h-[460px]" loading="lazy">
                                @elseif($m->media_type === 'video')
                                    <video src="{{ $m->url }}" controls class="w-full max-h-[460px] bg-black"></video>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    {{-- Reactions & Comments Bar (Interactive AJAX & Lucu) --}}
                    <div class="flex items-center justify-between pt-3.5 border-t-2 border-[#f8f4ed] text-xs">
                        <div class="flex items-center gap-2 flex-wrap">
                            <button type="button" 
                                    onclick="toggleCircleReaction(this, {{ $post->id }}, 'lit')"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-orange-50 hover:bg-orange-100 border border-orange-200 text-slate-950 font-black transition-base cursor-pointer hover:scale-105">
                                <span class="text-base">🔥</span>
                                <span class="reaction-count-{{ $post->id }}">{{ $post->likes_count > 0 ? $post->likes_count : 'Lit' }}</span>
                            </button>
                            <button type="button" 
                                    onclick="toggleCircleReaction(this, {{ $post->id }}, 'clap')"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-slate-950 font-black transition-base cursor-pointer hover:scale-105">
                                <span class="text-base">👏</span>
                                <span>Apresiasi</span>
                            </button>
                            <button type="button" 
                                    onclick="toggleCircleReaction(this, {{ $post->id }}, 'nongki')"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-amber-50 hover:bg-amber-100 border border-amber-200 text-slate-950 font-black transition-base cursor-pointer hover:scale-105">
                                <span class="text-base">💡</span>
                                <span>Inspirasi</span>
                            </button>
                            <button type="button" 
                                    onclick="toggleCircleReaction(this, {{ $post->id }}, 'love')"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-pink-50 hover:bg-pink-100 border border-pink-200 text-slate-950 font-black transition-base cursor-pointer hover:scale-105">
                                <span class="text-base">❤️</span>
                                <span>Suka</span>
                            </button>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    onclick="shareCirclePost('{{ addslashes($post->content ? \Illuminate\Support\Str::limit($post->content, 60) : 'Goresan Momen PembdaCIRCLE') }}', '{{ route('circle.profile', ['username' => $profile->username, 'post' => $post->id]) }}#post-{{ $post->id }}')"
                                    class="text-xs font-black text-slate-900 hover:text-amber-700 flex items-center gap-1.5 cursor-pointer px-2.5 py-1.5 rounded-xl bg-amber-100 hover:bg-amber-200 border border-amber-300 transition shadow-2xs hover:scale-105"
                                    title="Siarkan postingan ini">
                                <i class="fas fa-share-nodes text-amber-800 text-[11px]"></i>
                                <span>Siarkan</span>
                            </button>
                            <button type="button" 
                                    @click="showComments = !showComments"
                                    class="text-xs font-bold text-slate-700 hover:text-pink-600 flex items-center gap-1.5 cursor-pointer px-2.5 py-1.5 rounded-xl bg-slate-50 hover:bg-pink-50 transition border border-slate-200">
                                <i class="far fa-comment-dots text-pink-500"></i>
                                <span>{{ $post->comments_count }} <span class="hidden sm:inline">komentar</span></span>
                            </button>
                        </div>
                    </div>

                    {{-- Collapsible Comment Drawer --}}
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
                            <input type="text" name="comment_text" placeholder="Tulis komentar atau sapaan manis..." required
                                   class="flex-1 px-3.5 py-2 rounded-2xl bg-white border-2 border-pink-200 text-xs font-bold text-slate-950 placeholder:text-slate-400 focus:outline-none focus:border-pink-400 shadow-2xs">
                            <button type="submit" class="px-4 py-2 rounded-2xl bg-pink-200 hover:bg-pink-300 text-slate-950 text-xs font-black border-2 border-pink-300 transition-all hover:scale-105 shrink-0 flex items-center gap-1.5 cursor-pointer shadow-2xs">
                                <i class="fas fa-paper-plane text-pink-700"></i>
                                <span>Kirim</span>
                            </button>
                        </form>
                    </div>
                </article>
            @endforeach
        @endif
    </div>

    {{-- ═══════════════════════════════════════════
         TAB 2: KARYA & PORTOFOLIO (THE FLEX ZONE)
    ═══════════════════════════════════════════ --}}
    <div x-show="activeTab === 'portfolio'" x-transition class="space-y-6">

        {{-- ── 1. Verified Skill Badges dari Guru (Hijau Pastel Cantik) ── --}}
        @php
            $canEndorseStudent = Auth::check() && $user->role === 'siswa' && (Auth::user()->role === 'guru' || Auth::user()->isSuperAdmin() || Auth::user()->isAdminSekolah());
        @endphp
        @if($skillEndorsements->isNotEmpty() || $canEndorseStudent)
            <div class="bg-emerald-50/90 border-2 border-emerald-200 rounded-3xl p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between mb-3.5 flex-wrap gap-2">
                    <h3 class="text-xs font-black uppercase tracking-wider text-emerald-950 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-emerald-200 text-emerald-800 flex items-center justify-center text-xs">
                            <i class="fas fa-medal text-amber-500"></i>
                        </span>
                        <span>Lencana</span>
                    </h3>
                    @if($canEndorseStudent)
                        <button type="button" @click="showEndorseModal = true" 
                                class="px-3.5 py-1.5 rounded-2xl text-xs font-black text-slate-950 bg-emerald-200 hover:bg-emerald-300 border-2 border-emerald-400 transition-all hover:scale-105 flex items-center gap-1.5 cursor-pointer shadow-2xs">
                            <i class="fas fa-plus-circle text-emerald-800"></i>
                            <span>+ Lencana</span>
                        </button>
                    @endif
                </div>

                @if($skillEndorsements->isNotEmpty())
                    <div class="flex flex-wrap gap-2.5">
                        @foreach($skillEndorsements as $se)
                            <div class="px-3.5 py-2 rounded-2xl bg-white border-2 border-emerald-200 text-xs font-black text-slate-950 shadow-2xs flex items-center gap-2">
                                <i class="fas fa-medal text-amber-500"></i>
                                <span>{{ $se->skill_name }}</span>
                                <span class="text-[10px] text-emerald-900 font-black bg-emerald-100 border border-emerald-300 px-2 py-0.5 rounded-full cursor-help" 
                                      title="Divalidasi oleh: {{ $se->teacher?->name }}{{ $se->endorsement_note ? ' — Catatan: ' . $se->endorsement_note : '' }}">
                                    ✓ {{ explode(' ', trim($se->teacher?->name ?? 'Guru'))[0] }}
                                </span>
                                @if(Auth::check() && ($se->teacher_user_id === Auth::id() || Auth::user()->isSuperAdmin()))
                                    <form action="{{ route('circle.endorse.destroy', $se->id) }}" method="POST" onsubmit="return confirm('Hapus lencana keahlian ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-400 hover:text-rose-600 ml-1 p-0.5 cursor-pointer" title="Hapus Lencana">
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-xs text-emerald-900 font-semibold py-1">
                        <span>Belum ada lencana keahlian yang disematkan untuk siswa ini. Sebagai guru/mentor, Anda dapat memvalidasi keterampilannya sekarang.</span>
                    </div>
                @endif
            </div>
        @endif

        {{-- ── 2. Showcase Grid (Final Projects, PKL, & Circle Projects) ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

            {{-- A. Tugas Akhir / Penelitian Ilmiah (Biru Pastel) --}}
            @foreach($finalProjects as $fp)
                <div class="bg-white rounded-3xl border-2 border-[#f1e8dc] shadow-xs overflow-hidden flex flex-col hover:shadow-md hover:border-sky-300 transition-base">
                    <div class="p-5 flex-1">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-sky-100 border border-sky-300 text-sky-950">
                                {{ $fp->project_type === 'project_akhir' ? 'Project Akhir SMK' : 'Penelitian Ilmiah SMA' }}
                            </span>
                        </div>
                        <h4 class="font-display font-black text-base text-slate-950 mb-2 leading-snug">
                            {{ $fp->title }}
                        </h4>
                        <p class="text-xs font-medium text-slate-800 line-clamp-3 leading-relaxed">
                            {{ $fp->abstract ?? $fp->description ?? 'Dokumentasi hasil karya ilmiah dan praktik terapan terverifikasi.' }}
                        </p>
                    </div>
                    <div class="px-5 py-3 bg-[#faf8f5] border-t-2 border-[#f1e8dc] flex items-center justify-between text-xs">
                        <span class="text-slate-700 font-bold text-[11px]"><i class="fas fa-calendar-alt mr-1 text-slate-500"></i>{{ $fp->created_at->format('M Y') }}</span>
                        <span class="text-emerald-900 font-black text-[10px] bg-emerald-100 border border-emerald-300 px-2.5 py-0.5 rounded-lg">
                            Tersertifikasi
                        </span>
                    </div>
                </div>
            @endforeach

            {{-- B. Praktik Kerja Lapangan (Coklat Pastel) --}}
            @foreach($approvedPklLogs as $pkl)
                <div class="bg-white rounded-3xl border-2 border-[#f1e8dc] shadow-xs overflow-hidden flex flex-col hover:shadow-md hover:border-[#d4a373] transition-base">
                    @if($pkl->photo_url || $pkl->photo)
                        <div class="h-44 w-full bg-slate-100 overflow-hidden">
                            <img src="{{ $pkl->photo_url ?? asset('storage/' . $pkl->photo) }}" 
                                 alt="Dokumentasi PKL"
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                        </div>
                    @endif
                    <div class="p-5 flex-1">
                        <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-[#faedcd] border border-[#d4a373] text-[#7f5539] mb-2.5 inline-block">
                            <i class="fas fa-briefcase mr-1"></i>Praktik Industri DUDI
                        </span>
                        <h4 class="font-display font-black text-sm text-slate-950 mb-1 leading-snug">
                            {{ $pkl->activity }}
                        </h4>
                    </div>
                    <div class="px-5 py-3 bg-[#faf8f5] border-t-2 border-[#f1e8dc] text-[11px] text-slate-700 font-bold flex items-center justify-between">
                        <span>{{ $pkl->log_date ? \Carbon\Carbon::parse($pkl->log_date)->isoFormat('D MMMM Y') : '-' }}</span>
                        <span class="text-emerald-800 font-black"><i class="fas fa-check-circle mr-1 text-emerald-600"></i>Approved</span>
                    </div>
                </div>
            @endforeach

            {{-- C. Postingan Portfolio Circle Langsung (Pink Pastel) --}}
            @foreach($portfolioPosts as $port)
                <div id="post-{{ $port->id }}" class="bg-white rounded-3xl border-2 {{ request('post') == $port->id ? 'border-amber-400 ring-4 ring-amber-300 shadow-xl' : 'border-[#f1e8dc]' }} shadow-xs overflow-hidden flex flex-col hover:shadow-md hover:border-pink-300 transition-base relative group">
                    <div class="absolute top-3 right-3 z-10 flex items-center gap-1.5">
                        <button type="button" 
                                onclick="shareCirclePost('{{ addslashes($port->title ?? 'Karya Portofolio Pembda') }}', '{{ route('circle.profile', ['username' => $profile->username, 'post' => $port->id]) }}#post-{{ $port->id }}')"
                                class="w-7 h-7 rounded-lg bg-black/60 hover:bg-amber-500 text-white flex items-center justify-center text-xs transition cursor-pointer shadow-xs" 
                                title="Siarkan Karya ini">
                            <i class="fas fa-share-nodes text-[10px]"></i>
                        </button>
                        @if(Auth::check() && (Auth::id() === $port->user_id || Auth::user()->isSuperAdmin()))
                            <form action="{{ route('circle.post.destroy', $port->id) }}" method="POST" onsubmit="return confirm('Hapus karya ini?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-7 h-7 rounded-lg bg-black/60 hover:bg-rose-600 text-white flex items-center justify-center text-xs transition cursor-pointer shadow-xs" title="Hapus Karya">
                                    <i class="fas fa-trash-alt text-[10px]"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                    @if($port->media->isNotEmpty())
                        <div class="h-48 w-full bg-slate-900 overflow-hidden">
                            @php $firstMedia = $port->media->first(); @endphp
                            @if($firstMedia->media_type === 'image')
                                <img src="{{ $firstMedia->url }}" alt="Portfolio media" class="w-full h-full object-cover">
                            @elseif($firstMedia->media_type === 'video')
                                <video src="{{ $firstMedia->url }}" controls class="w-full h-full object-cover"></video>
                            @endif
                        </div>
                    @endif
                    <div class="p-5 flex-1">
                        <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-pink-100 border border-pink-300 text-pink-900 mb-2.5 inline-block">
                            {{ $port->post_type }}
                        </span>
                        <h4 class="font-display font-black text-sm text-slate-900 mb-1.5">{{ $port->title ?? 'Karya Unggulan' }}</h4>
                        <p class="text-xs font-semibold text-slate-800 line-clamp-3 leading-relaxed">{{ $port->content }}</p>
                    </div>
                </div>
            @endforeach

        </div>

        {{-- Empty State --}}
        @if($portfolioPosts->isEmpty() && $finalProjects->isEmpty() && $approvedPklLogs->isEmpty())
            <div class="bg-white rounded-3xl border-2 border-[#f1e8dc] p-10 text-center shadow-xs">
                <div class="w-16 h-16 rounded-2xl bg-amber-100 text-amber-800 border border-amber-300 flex items-center justify-center mx-auto mb-3.5 text-2xl shadow-xs">
                    <i class="fas fa-trophy"></i>
                </div>
                <h3 class="font-display font-black text-lg text-slate-950 mb-1">Portofolio Sedang Dirakit</h3>
                <p class="text-xs font-bold text-slate-700 max-w-sm mx-auto leading-relaxed">
                    Karya tugas akhir, dokumentasi magang DUDI, dan inovasi siswa ini akan segera dipamerkan di sini.
                </p>
            </div>
        @endif

    </div>

    {{-- ═══════════════════════════════════════════
         MODAL EDIT PROFIL & MOOD (PASTEL LUCU)
    ═══════════════════════════════════════════ --}}
    @if(Auth::check() && Auth::id() === $user->id)
        <div x-show="showEditModal" x-cloak 
             class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4"
             x-transition>
            <div class="bg-white rounded-3xl border-2 border-pink-200 max-w-md w-full p-6 shadow-2xl relative"
                 @click.outside="showEditModal = false">
                <div class="flex items-center justify-between mb-4 pb-3 border-b-2 border-slate-100">
                    <h3 class="font-display font-black text-lg text-slate-950 flex items-center gap-2">
                        <i class="fas fa-palette text-pink-600"></i>
                        <span>Edit Profil & Suasana Hati</span>
                    </h3>
                    <button type="button" @click="showEditModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center font-bold">
                        ✕
                    </button>
                </div>

                <form action="{{ route('circle.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-black text-slate-900 mb-1">Nama Panggilan Cantik / Julukan</label>
                            <input type="text" name="nickname" value="{{ $profile->nickname }}" class="w-full px-4 py-2 rounded-2xl bg-slate-50 border-2 border-slate-200 text-sm font-bold text-slate-950 focus:outline-none focus:border-pink-300">
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-900 mb-1">Emoji Suasana Hati (Mood)</label>
                            <div class="grid grid-cols-6 gap-2 text-xl mb-2">
                                @foreach(['🔥', '☕', '💡', '✨', '😎', '📚', '💪', '😴', '🎨', '🚀', '🛠️', '🌸'] as $em)
                                    <label class="p-2 rounded-xl border border-slate-200 hover:bg-pink-50 text-center cursor-pointer flex items-center justify-center">
                                        <input type="radio" name="mood_emoji" value="{{ $em }}" {{ ($profile->mood_emoji ?? '✨') === $em ? 'checked' : '' }} class="hidden">
                                        <span>{{ $em }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <input type="text" name="mood_text" value="{{ $profile->mood_text }}" placeholder="Contoh: Semangat ngoprek di bengkel!" class="w-full px-4 py-2 rounded-2xl bg-slate-50 border-2 border-slate-200 text-sm font-bold text-slate-950 focus:outline-none focus:border-pink-300">
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-900 mb-1">Bio Singkat</label>
                            <textarea name="bio" rows="2" class="w-full p-3 rounded-2xl bg-slate-50 border-2 border-slate-200 text-sm font-semibold text-slate-950 focus:outline-none focus:border-pink-300 resize-none">{{ $profile->bio }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-900 mb-1">Ganti Foto Sampul / Banner</label>
                            <input type="file" name="banner" accept="image/*" class="w-full text-xs text-slate-700 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-pink-100 file:text-pink-900 hover:file:bg-pink-200">
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-2xl text-xs font-black text-slate-950 bg-pink-200 hover:bg-pink-300 border-2 border-pink-300 shadow-xs transition hover:scale-105">
                            Simpan Perubahan ✨
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════
         MODAL VALIDASI LENCANA KEAHLIAN OLEH GURU (HIJAU PASTEL CANTIK)
    ═══════════════════════════════════════════ --}}
    @if(Auth::check() && (Auth::user()->role === 'guru' || Auth::user()->isSuperAdmin() || Auth::user()->isAdminSekolah()) && $user->role === 'siswa')
        <div x-show="showEndorseModal" x-cloak 
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4"
             x-transition>
            <div class="bg-white rounded-3xl border-2 border-emerald-300 max-w-lg w-full p-6 shadow-2xl relative max-h-[90vh] overflow-y-auto"
                 @click.outside="showEndorseModal = false">
                <div class="flex items-center justify-between mb-4 pb-3 border-b-2 border-slate-100">
                    <h3 class="font-display font-black text-lg text-slate-950 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-sm">
                            <i class="fas fa-medal text-amber-500"></i>
                        </span>
                        <span>Validasi Keahlian Siswa</span>
                    </h3>
                    <button type="button" @click="showEndorseModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center font-bold cursor-pointer">
                        ✕
                    </button>
                </div>

                <p class="text-xs font-semibold text-slate-700 mb-4">
                    Beri lencana pengesahan resmi atas kompetensi atau karya nyata yang dikuasai oleh <strong>{{ $user->name }}</strong>. Lencana ini akan tampil permanen di portofolio publik siswa.
                </p>

                <form action="{{ route('circle.endorse.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="student_user_id" value="{{ $user->id }}">

                    <div class="space-y-4">
                        {{-- Rekomendasi / Quick Chip Lencana --}}
                        <div>
                            <label class="block text-xs font-black text-slate-950 mb-1.5">Pilih Rekomendasi Lencana Cepat:</label>
                            <div class="flex flex-wrap gap-1.5 max-h-36 overflow-y-auto p-2 bg-slate-50 border-2 border-slate-200 rounded-2xl">
                                @php
                                    $recommendedSkills = [
                                        'AutoCAD & Pemodelan 3D',
                                        'Konfigurasi MikroTik & Jaringan',
                                        'Fiber Optic Splicing',
                                        'Troubleshooting & Servis Mesin',
                                        'Kelistrikan & Injeksi Otomotif',
                                        'Mikrokontroler & Arduino',
                                        'Pengelasan & Fabrikasi',
                                        'Desain Grafis & Komunikasi Visual',
                                        'Web Programming (HTML/CSS/JS)',
                                        'Karya Tulis Ilmiah Terapan',
                                        'Riset Laboratorium Biologi',
                                        'Public Speaking & Presentasi',
                                        'Storytelling & Bahasa Inggris',
                                        'Editing Video Reels & Sinematik',
                                        'Robotika Pemula',
                                        'Kepemimpinan OSIS & Keorganisasian'
                                    ];
                                @endphp
                                @foreach($recommendedSkills as $rSkill)
                                    <button type="button" 
                                            @click="selectedSkill = '{{ $rSkill }}'"
                                            class="px-2.5 py-1 rounded-xl text-[11px] font-bold border transition cursor-pointer hover:scale-105"
                                            :class="selectedSkill === '{{ $rSkill }}' ? 'bg-emerald-200 border-emerald-400 text-emerald-950 font-black' : 'bg-white border-slate-200 text-slate-800 hover:bg-emerald-50'">
                                        + {{ $rSkill }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Nama Keahlian --}}
                        <div>
                            <label class="block text-xs font-black text-slate-950 mb-1">Nama Keahlian / Kompetensi Siswa <span class="text-rose-500">*</span></label>
                            <input type="text" name="skill_name" x-model="selectedSkill" required
                                   placeholder="Contoh: Overhaul Mesin Bensin, Pemrograman Python, dll"
                                   class="w-full px-4 py-2.5 rounded-2xl bg-emerald-50/40 border-2 border-emerald-200 text-xs font-bold text-slate-950 focus:outline-none focus:border-emerald-500">
                        </div>

                        {{-- Catatan Pengesahan Guru --}}
                        <div>
                            <label class="block text-xs font-black text-slate-950 mb-1">Catatan Pengesahan Guru (Opsional)</label>
                            <textarea name="endorsement_note" rows="2" 
                                      placeholder="Contoh: Siswa menunjukkan ketelitian tinggi dalam pengujian rangkaian dan perakitan panel kerja bengkel..."
                                      class="w-full p-3 rounded-2xl bg-slate-50 border-2 border-slate-200 text-xs font-semibold text-slate-950 focus:outline-none focus:border-emerald-400 resize-none"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="showEndorseModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-5 py-2.5 rounded-2xl text-xs font-black text-black border-2 border-black transition-all hover:scale-105 cursor-pointer shadow-md"
                                style="background: linear-gradient(135deg, #a7f3d0 0%, #34d399 100%) !important; color: #000000 !important; border: 2px solid #000000 !important;">
                            <i class="fas fa-check-circle mr-1"></i>
                            <span>Sematkan Lencana 🏅</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>

{{-- ── Toast & Native Share Script ── --}}
<script>
function shareProfile() {
    const url = window.location.href;
    const text = 'Lihat profil dan karya {{ $user->name }} di PembdaCIRCLE:\n' + url;
    if (navigator.share) {
        navigator.share({
            title: '{{ $user->name }} di PembdaCIRCLE',
            text: text,
            url: url
        }).catch(() => {});
    } else {
        const waUrl = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(text);
        window.open(waUrl, '_blank');
    }
}
</script>
@endsection
