<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PembdaHUB Virtual Lab & Simulator KBM — Multi-Mapel, Vokasi & IoT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body {
            background: radial-gradient(circle at top right, #1e1b4b 0%, #0f172a 45%, #020617 100%);
            min-height: 100vh;
            color: #f8fafc;
        }
        .glass-card {
            background: rgba(15, 23, 42, 0.72);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(148, 163, 184, 0.14);
        }
        .glass-card:hover {
            border-color: rgba(99, 102, 241, 0.45);
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="antialiased selection:bg-indigo-500 selection:text-white" x-data="simLabCatalog()">

    <!-- Top Navbar -->
    <nav class="sticky top-0 z-40 border-b border-slate-800/80 bg-slate-950/85 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 via-purple-500 to-cyan-500 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                        <i class="fas fa-flask-vial text-white text-base"></i>
                    </div>
                    <div>
                        <span class="font-bold text-white tracking-tight text-base block leading-none">PembdaHUB <span class="text-cyan-400">SimLab</span></span>
                        <span class="text-[11px] text-slate-400">Laboratorium Virtual & Praktikum Interaktif</span>
                    </div>
                </a>
            </div>

            <div class="flex items-center gap-2.5">
                @if(auth()->check() && (auth()->user()->teacher || auth()->user()->isAdmin()))
                <button @click="openAiModal = true" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs sm:text-sm font-semibold shadow-lg shadow-indigo-600/25 transition">
                    <i class="fas fa-wand-magic-sparkles"></i>
                    <span>Buat Simulator AI</span>
                </button>
                @endif

                @auth
                <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-800/90 hover:bg-slate-700 text-slate-200 text-xs sm:text-sm font-medium border border-slate-700 transition">
                    <i class="fas fa-arrow-left"></i>
                    <span class="hidden sm:inline">Kembali ke Portal</span>
                </a>
                @else
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs sm:text-sm font-medium border border-slate-700 transition">
                    <i class="fas fa-right-to-bracket"></i>
                    <span>Login</span>
                </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Banner -->
    <header class="relative overflow-hidden border-b border-slate-800/70">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/15 border border-indigo-400/30 text-indigo-300 text-xs font-semibold mb-4">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Laboratorium Praktikum Virtual • SMP, SMA, SMK Vokasi & LMS</span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white mb-3">
                        Eksperimen Visual, Animasi Real-Time & <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-indigo-400 to-purple-400">SOP Lab Standar</span>
                    </h1>
                    <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
                        Masuk ke ruang lab virtual lintas mata pelajaran — mulai dari <strong>Mesin Bensin 4-Tak (SMK)</strong>, <strong>Persamaan Reaksi Kimia (SMA)</strong>, <strong>Grafik Kuadrat (SMP)</strong>, hingga <strong>Studio IoT Arduino/ESP32</strong>. Ubah variabel, amati efeknya secara langsung, dan ikuti SOP praktikum langkah demi langkah.
                    </p>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-2 gap-3 shrink-0">
                    <div class="glass-card rounded-2xl p-3.5 text-center">
                        <div class="text-2xl font-extrabold text-cyan-400">{{ $virtualLabs->count() }}</div>
                        <div class="text-[11px] text-slate-400 font-medium">Ruang Lab Virtual</div>
                    </div>
                    <div class="glass-card rounded-2xl p-3.5 text-center">
                        <div class="text-2xl font-extrabold text-purple-400">{{ $templates->count() }}</div>
                        <div class="text-[11px] text-slate-400 font-medium">Starter Kit IoT</div>
                    </div>
                    <div class="glass-card rounded-2xl p-3.5 text-center">
                        <div class="text-2xl font-extrabold text-emerald-400">4</div>
                        <div class="text-[11px] text-slate-400 font-medium">Kategori Jenjang</div>
                    </div>
                    <div class="glass-card rounded-2xl p-3.5 text-center">
                        <div class="text-2xl font-extrabold text-amber-400"><i class="fas fa-robot"></i> AI</div>
                        <div class="text-[11px] text-slate-400 font-medium">Generator Guru</div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">

        <!-- Section 1: Katalog Ruang Lab Virtual Multi-Mapel -->
        <section>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-white flex items-center gap-2.5">
                        <i class="fas fa-microscope text-cyan-400"></i>
                        <span>Ruang Laboratorium Virtual & Eksperimen</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">Pilih kategori atau cari mata pelajaran untuk masuk ke ruang praktikum beranimasi lengkap dengan SOP.</p>
                </div>

                <!-- Search Input -->
                <div class="relative w-full md:w-72">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" x-model="searchQuery" placeholder="Cari mapel atau simulator..."
                        class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700/80 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition">
                </div>
            </div>

            <!-- 4 Category Tabs + Semua -->
            <div class="flex flex-wrap items-center gap-2 mb-6">
                <button @click="activeCategory = 'all'"
                    :class="activeCategory === 'all' ? 'bg-indigo-600 text-white border-indigo-500 shadow-lg shadow-indigo-600/25' : 'bg-slate-900/80 text-slate-300 border-slate-800 hover:border-slate-700'"
                    class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold border transition flex items-center gap-2">
                    <i class="fas fa-layer-group"></i>
                    <span>Semua Lab</span>
                    <span class="px-1.5 py-0.5 rounded-md bg-black/25 text-[11px]">{{ $virtualLabs->count() }}</span>
                </button>

                <button @click="activeCategory = 'umum'"
                    :class="activeCategory === 'umum' ? 'bg-cyan-600 text-white border-cyan-500 shadow-lg shadow-cyan-600/25' : 'bg-slate-900/80 text-slate-300 border-slate-800 hover:border-slate-700'"
                    class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold border transition flex items-center gap-2">
                    <i class="fas fa-globe"></i>
                    <span>Umum</span>
                    <span class="px-1.5 py-0.5 rounded-md bg-black/25 text-[11px]">{{ $virtualLabs->where('category', 'umum')->count() }}</span>
                </button>

                <button @click="activeCategory = 'smp'"
                    :class="activeCategory === 'smp' ? 'bg-emerald-600 text-white border-emerald-500 shadow-lg shadow-emerald-600/25' : 'bg-slate-900/80 text-slate-300 border-slate-800 hover:border-slate-700'"
                    class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold border transition flex items-center gap-2">
                    <i class="fas fa-shapes"></i>
                    <span>Level SMP</span>
                    <span class="px-1.5 py-0.5 rounded-md bg-black/25 text-[11px]">{{ $virtualLabs->where('category', 'smp')->count() }}</span>
                </button>

                <button @click="activeCategory = 'sma'"
                    :class="activeCategory === 'sma' ? 'bg-purple-600 text-white border-purple-500 shadow-lg shadow-purple-600/25' : 'bg-slate-900/80 text-slate-300 border-slate-800 hover:border-slate-700'"
                    class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold border transition flex items-center gap-2">
                    <i class="fas fa-atom"></i>
                    <span>Level SMA</span>
                    <span class="px-1.5 py-0.5 rounded-md bg-black/25 text-[11px]">{{ $virtualLabs->where('category', 'sma')->count() }}</span>
                </button>

                <button @click="activeCategory = 'vokasi'"
                    :class="activeCategory === 'vokasi' ? 'bg-amber-600 text-white border-amber-500 shadow-lg shadow-amber-600/25' : 'bg-slate-900/80 text-slate-300 border-slate-800 hover:border-slate-700'"
                    class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold border transition flex items-center gap-2">
                    <i class="fas fa-gears"></i>
                    <span>Vokasi (SMK)</span>
                    <span class="px-1.5 py-0.5 rounded-md bg-black/25 text-[11px]">{{ $virtualLabs->where('category', 'vokasi')->count() }}</span>
                </button>
            </div>

            <!-- Grid Cards Virtual Lab -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($virtualLabs as $lab)
                @php
                    $catMap = [
                        'umum' => ['label' => 'Umum', 'cls' => 'bg-cyan-500/15 text-cyan-300 border-cyan-500/30'],
                        'smp' => ['label' => 'Level SMP', 'cls' => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30'],
                        'sma' => ['label' => 'Level SMA', 'cls' => 'bg-purple-500/15 text-purple-300 border-purple-500/30'],
                        'vokasi' => ['label' => 'Vokasi (SMK)', 'cls' => 'bg-amber-500/15 text-amber-300 border-amber-500/30'],
                    ];
                    $catInfo = $catMap[$lab->category] ?? $catMap['umum'];
                    $canDelete = auth()->check() && ($lab->created_by === auth()->id() || auth()->user()->isAdmin());
                    $searchBlob = strtolower($lab->title . ' ' . $lab->subject_name . ' ' . $lab->description . ' ' . ($lab->course->name ?? $lab->course->course_name ?? ''));
                @endphp
                <div x-show="(activeCategory === 'all' || activeCategory === '{{ $lab->category }}') && ('{{ addslashes($searchBlob) }}'.includes(searchQuery.toLowerCase()))"
                     x-transition
                     class="glass-card rounded-2xl p-5 flex flex-col justify-between transition duration-200 relative group">
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white text-lg shadow-md"
                                     style="background: {{ $lab->accent_color ?? '#6366f1' }};">
                                    <i class="fas {{ $lab->icon ?? 'fa-flask' }}"></i>
                                </div>
                                <div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider border {{ $catInfo['cls'] }}">
                                        {{ $catInfo['label'] }}
                                    </span>
                                    <div class="text-xs text-slate-400 font-medium mt-0.5">{{ $lab->subject_name }}</div>
                                </div>
                            </div>

                            @if($canDelete)
                            <button @click="deleteLab({{ $lab->id }}, '{{ addslashes($lab->title) }}')"
                                    title="Hapus Simulator"
                                    class="opacity-0 group-hover:opacity-100 text-slate-500 hover:text-rose-400 p-1.5 rounded-lg hover:bg-rose-500/10 transition">
                                <i class="fas fa-trash-can text-xs"></i>
                            </button>
                            @endif
                        </div>

                        <h3 class="text-base sm:text-lg font-bold text-white mb-2 leading-snug group-hover:text-cyan-300 transition">
                            {{ $lab->title }}
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-400 line-clamp-3 leading-relaxed mb-4">
                            {{ $lab->description }}
                        </p>

                        @if($lab->course)
                        <div class="mb-4 px-3 py-1.5 rounded-lg bg-indigo-500/10 border border-indigo-500/25 flex items-center gap-2 text-xs text-indigo-300">
                            <i class="fas fa-book-open shrink-0"></i>
                            <span class="truncate">LMS: {{ $lab->course->name ?? $lab->course->course_name }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-slate-400 flex items-center gap-1.5">
                            <i class="fas fa-clipboard-check text-emerald-400"></i>
                            <span>{{ is_array($lab->sop_steps) ? count($lab->sop_steps) : 0 }} Langkah SOP</span>
                        </span>

                        <a href="{{ route('simlab.lab', $lab->slug) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-indigo-600 text-white text-xs font-semibold transition">
                            <span>Masuk Ruang Lab</span>
                            <i class="fas fa-arrow-right text-[11px]"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        <!-- Section 2: Studio Mikrokontroler & IoT (Arduino / ESP32) -->
        <section class="pt-4 border-t border-slate-800/80">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-white flex items-center gap-2.5">
                        <i class="fas fa-microchip text-amber-400"></i>
                        <span>Studio Rangkaian Mikrokontroler & IoT</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">Rakit rangkaian Arduino Uno, Nano, dan ESP32 lengkap dengan kode C++/Sketch secara interaktif.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('simlab.editor', 'new-arduino_uno') }}" class="px-3 py-2 rounded-xl bg-cyan-600/20 hover:bg-cyan-600/30 border border-cyan-500/40 text-cyan-300 text-xs font-semibold transition">
                        + Kanvas Arduino Uno
                    </a>
                    <a href="{{ route('simlab.editor', 'new-esp32') }}" class="px-3 py-2 rounded-xl bg-purple-600/20 hover:bg-purple-600/30 border border-purple-500/40 text-purple-300 text-xs font-semibold transition">
                        + Kanvas ESP32 WiFi
                    </a>
                    <a href="{{ route('simlab.editor', 'new-arduino_nano') }}" class="px-3 py-2 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 border border-emerald-500/40 text-emerald-300 text-xs font-semibold transition">
                        + Kanvas Arduino Nano
                    </a>
                </div>
            </div>

            @if($myProjects->count() > 0)
            <div class="mb-8">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-3">Proyek IoT Tersimpan Saya</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($myProjects as $proj)
                    <a href="{{ route('simlab.editor', $proj->id) }}" class="glass-card rounded-xl p-4 block transition">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300">{{ $proj->board_type }}</span>
                            <span class="text-[11px] text-slate-500">{{ $proj->updated_at->diffForHumans() }}</span>
                        </div>
                        <div class="font-bold text-white text-sm truncate">{{ $proj->title }}</div>
                        <div class="text-xs text-slate-400 mt-1 truncate">{{ $proj->description ?? 'Proyek rangkaian mikrokontroler' }}</div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            @if($templates->count() > 0)
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-3">Starter Kit Praktikum IoT</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($templates as $tpl)
                    <a href="{{ route('simlab.editor', $tpl->slug ?? $tpl->id) }}" class="glass-card rounded-xl p-4 flex items-start gap-3.5 transition group">
                        <div class="w-10 h-10 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-amber-400 shrink-0 group-hover:bg-amber-500 group-hover:text-slate-950 transition">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-slate-800 text-cyan-300">{{ $tpl->board_type }}</span>
                            </div>
                            <h4 class="font-bold text-white text-sm truncate group-hover:text-cyan-300 transition">{{ $tpl->title }}</h4>
                            <p class="text-xs text-slate-400 line-clamp-2 mt-1">{{ $tpl->description }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </section>

    </main>

    <!-- Modal: Buat Simulator Baru dengan AI (Guru/Admin) -->
    @if(auth()->check() && (auth()->user()->teacher || auth()->user()->isAdmin()))
    <div x-show="openAiModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.away="if(!isGenerating) openAiModal = false"
             class="w-full max-w-xl rounded-2xl bg-slate-900 border border-slate-700 shadow-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between bg-slate-900/90">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white">
                        <i class="fas fa-wand-magic-sparkles text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-base">Buat Simulator Lab Baru (AI Generator)</h3>
                        <p class="text-[11px] text-slate-400">Hasilkan lab interaktif beranimasi + SOP praktikum otomatis untuk siswa</p>
                    </div>
                </div>
                <button @click="if(!isGenerating) openAiModal = false" class="text-slate-400 hover:text-white p-1">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form @submit.prevent="submitAiLab" class="p-6 space-y-4">
                <div x-show="aiError" class="p-3 rounded-xl bg-rose-500/15 border border-rose-500/40 text-rose-300 text-xs" x-text="aiError"></div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Judul Simulator Lab *</label>
                    <input type="text" x-model="form.title" required placeholder="Contoh: Simulator Mesin Pendingin AC Mobil / Titik Didih Larutan"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Kategori Jenjang *</label>
                        <select x-model="form.category" required
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-sm text-white focus:outline-none focus:border-indigo-500">
                            <option value="umum">Umum</option>
                            <option value="smp">Level SMP</option>
                            <option value="sma">Level SMA</option>
                            <option value="vokasi">Vokasi (SMK)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Mata Pelajaran / Kejuruan *</label>
                        <input type="text" x-model="form.subject_name" required placeholder="Contoh: Fisika / TKR / Kimia / IPA"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                @if($teacherCourses->count() > 0)
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Hubungkan ke Course / Modul LMS (Opsional)</label>
                    <select x-model="form.course_id"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-sm text-white focus:outline-none focus:border-indigo-500">
                        <option value="">-- Tidak dihubungkan ke course tertentu --</option>
                        @foreach($teacherCourses as $c)
                        <option value="{{ $c->id }}">{{ $c->name ?? $c->course_name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Variabel & Konsep Eksperimen yang Diinginkan</label>
                    <textarea x-model="form.description" rows="3" placeholder="Jelaskan variabel yang ingin di-tuning siswa dan animasi yang ditampilkan (misal: slider tekanan, suhu, kecepatan putaran, serta pembacaan output real-time)..."
                              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-sm text-white focus:outline-none focus:border-indigo-500"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="button" @click="openAiModal = false" :disabled="isGenerating"
                            class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit" :disabled="isGenerating"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 disabled:opacity-60 text-white text-xs font-semibold shadow-lg transition">
                        <i class="fas" :class="isGenerating ? 'fa-spinner fa-spin' : 'fa-wand-magic-sparkles'"></i>
                        <span x-text="isGenerating ? 'Merakit Simulator & SOP...' : 'Generate Simulator'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <script>
        function simLabCatalog() {
            return {
                activeCategory: 'all',
                searchQuery: '',
                openAiModal: false,
                isGenerating: false,
                aiError: '',
                form: {
                    title: '',
                    category: 'vokasi',
                    subject_name: '',
                    course_id: '',
                    description: ''
                },
                async submitAiLab() {
                    this.isGenerating = true;
                    this.aiError = '';
                    try {
                        const res = await fetch('{{ route("simlab.ai-generate") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(this.form)
                        });
                        const data = await res.json();
                        if (!res.ok || !data.success) {
                            this.aiError = data.message || 'Gagal membangkitkan simulator.';
                            return;
                        }
                        window.location.href = data.redirect_url;
                    } catch (e) {
                        this.aiError = 'Terjadi gangguan koneksi saat memproses permintaan.';
                    } finally {
                        this.isGenerating = false;
                    }
                },
                async deleteLab(id, title) {
                    if (!confirm(`Hapus simulator "${title}"?`)) return;
                    try {
                        const res = await fetch(`{{ url('/simlab/lab') }}/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        if (res.ok) {
                            window.location.reload();
                        } else {
                            alert('Gagal menghapus simulator.');
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan saat menghapus simulator.');
                    }
                }
            };
        }
    </script>
</body>
</html>
