<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PembdaTEMU Guru: {{ $course->course_name ?? $course->name }} - PembdaHUB</title>
    
    <!-- Tailwind CSS CDN (Memastikan seluruh utility & arbitrary values ter-generate real-time) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- FontAwesome 6 CDN (Icon Lengkap & Tajam) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono (Khas PembdaHUB Homepage) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            font-family: 'Plus Jakarta Sans', sans-serif;
            --bg-warm: #faf8f5;
            --text-dark: #121316;
            --brand-red: #ff3823;
            --brand-yellow: #fbc02d;
            --brand-blue: #2563eb;
            --border-ink: #121316;
        }

        body {
            background-color: #faf8f5 !important;
            color: #121316 !important;
            letter-spacing: -0.02em;
        }

        .font-mono-code {
            font-family: 'JetBrains Mono', monospace !important;
        }

        /* Yellow Highlighter Stroke Effect (Khas PembdaHUB Homepage) */
        .highlight-marker {
            background: linear-gradient(180deg, transparent 62%, #fde047 62%, #fde047 92%, transparent 92%);
            display: inline;
        }

        /* Dotted Graph Paper Background */
        .graph-paper-box {
            background-color: #faf8f5 !important;
            background-image: radial-gradient(#cbd5e1 1.5px, transparent 1.5px) !important;
            background-size: 16px 16px !important;
        }

        /* Tactile Solid Drop-Shadow Buttons */
        .btn-tactile-red {
            background-color: #ff3823 !important;
            color: #ffffff !important;
            border: 2px solid #121316 !important;
            box-shadow: 3px 3px 0px #121316 !important;
            transition: all 0.15s ease;
        }
        .btn-tactile-red:hover {
            transform: translate(1.5px, 1.5px);
            box-shadow: 1.5px 1.5px 0px #121316 !important;
        }

        .btn-tactile-yellow {
            background-color: #fbc02d !important;
            color: #121316 !important;
            border: 2px solid #121316 !important;
            box-shadow: 3px 3px 0px #121316 !important;
            transition: all 0.15s ease;
        }
        .btn-tactile-yellow:hover {
            transform: translate(1.5px, 1.5px);
            box-shadow: 1.5px 1.5px 0px #121316 !important;
        }

        .btn-tactile-white {
            background-color: #ffffff !important;
            color: #121316 !important;
            border: 2px solid #121316 !important;
            box-shadow: 3px 3px 0px #121316 !important;
            transition: all 0.15s ease;
        }
        .btn-tactile-white:hover {
            transform: translate(1.5px, 1.5px);
            box-shadow: 1.5px 1.5px 0px #121316 !important;
        }

        /* Poster Card Tactile */
        .poster-card {
            border: 2px solid #121316 !important;
            border-radius: 1.25rem !important;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 3px 3px 0px #121316 !important;
        }
        .poster-card:hover {
            transform: translateY(-2px);
            box-shadow: 5px 5px 0px #121316 !important;
        }

        /* Scrollbar Tactile */
        ::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }
        ::-webkit-scrollbar-track {
            background: #f1ede4;
        }
        ::-webkit-scrollbar-thumb {
            background: #121316;
            border-radius: 9999px;
        }

        /* Video Tile Responsive Styles (Zoom style - Tactile Poster Frame) */
        .video-tile {
            position: relative;
            background: #0f172a !important;
            border-radius: 1.5rem !important;
            overflow: hidden;
            border: 2.5px solid #121316 !important;
            box-shadow: 4px 4px 0px #121316 !important;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .video-tile:hover {
            box-shadow: 6px 6px 0px #121316 !important;
        }
        .video-tile.speaking {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 4px #10b981, 5px 5px 0px #121316 !important;
        }
        .video-tile.pinned-speaker {
            border-color: #ff3823 !important;
            box-shadow: 0 0 0 4px #ff3823, 6px 6px 0px #121316 !important;
        }
        .video-tile video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            border-radius: inherit;
        }
        .filmstrip-tile {
            height: 100px !important;
            width: 175px !important;
            flex-shrink: 0 !important;
            cursor: pointer;
            border-radius: 1.25rem !important;
            border: 2px solid #121316 !important;
            box-shadow: 3px 3px 0px #121316 !important;
        }
        .filmstrip-tile:hover {
            transform: scale(1.04);
            box-shadow: 4px 4px 0px #121316 !important;
        }

        /* Canvas Whiteboard */
        .canvas-whiteboard {
            background-color: #ffffff;
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 16px 16px;
        }
        .canvas-grid {
            background-color: #ffffff;
            background-image: linear-gradient(#e2e8f0 1px, transparent 1px), linear-gradient(90deg, #e2e8f0 1px, transparent 1px);
            background-size: 20px 20px;
        }
        .canvas-chalkboard {
            background-color: #064e3b;
            background-image: radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px);
            background-size: 16px 16px;
        }
    </style>
</head>
<body class="bg-[#faf8f5] text-[#121316] h-screen flex flex-col overflow-hidden select-none font-sans antialiased">

    {{-- 1. SMART CLASSROOM HEADER (PEMBDAHUB HOMEPAGE TACTILE STYLE - WARNA KONTRAS TINGGI) --}}
    <header class="bg-[#faf8f5] border-b-2 border-[#121316] px-4 sm:px-6 py-2.5 flex items-center justify-between gap-3 z-30 flex-shrink-0 shadow-xs" style="background-color: #faf8f5; border-bottom: 2.5px solid #121316;">
        
        {{-- Brand, Unit & Info Kelas --}}
        <div class="flex items-center gap-3 min-w-0">
            <div class="flex items-center gap-1.5 flex-shrink-0">
                <a href="{{ route('guru.dashboard') }}" title="Kembali ke Dashboard Guru" class="hover:scale-105 transition-transform flex-shrink-0">
                    <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo PembdaHUB" class="w-9 h-9 object-contain rounded-xl border-2 border-[#121316] shadow-[2px_2px_0px_#ff3823] bg-white p-0.5">
                </a>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-black uppercase tracking-wider" style="background-color: #ff3823; color: #ffffff; border: 1.5px solid #121316; box-shadow: 2px 2px 0px #121316;">
                        <span class="w-2 h-2 rounded-full animate-ping" style="background-color: #fde047;"></span> ✱ PembdaTEMU LIVE
                    </span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-mono-code font-extrabold hidden md:inline truncate" style="background-color: #fde047; color: #121316; border: 1.5px solid #121316;">
                        {{ $course->school?->name ?? 'SMKS Swasta Pembda Nias' }}
                    </span>
                </div>
                <h1 class="text-sm sm:text-base font-black truncate leading-tight mt-1 flex items-center gap-1.5" style="color: #121316;">
                    <span class="highlight-marker">{{ $course->course_name ?? $course->name }}</span>
                    @if($course->subject)
                        <span class="text-xs font-bold hidden sm:inline" style="color: #555;">• {{ $course->subject->name }}</span>
                    @endif
                </h1>
            </div>
        </div>

        {{-- Center Status Widget: Durasi Live & Peserta Hadir (Hitam Pekat & Kuning Emas - Sangat Terbaca) --}}
        <div class="hidden lg:flex items-center gap-3 rounded-2xl px-4 py-1.5" style="background-color: #121316; color: #ffffff; border: 2px solid #121316; box-shadow: 3px 3px 0px #ff3823;">
            <div class="flex items-center gap-2 text-xs font-mono-code font-bold" style="color: #fde047;" title="Durasi Live Kelas">
                <i class="fa-regular fa-clock text-sm animate-spin" style="color: #fbc02d; animation-duration: 8s;"></i>
                <span id="class-timer" class="font-mono-code tracking-widest text-sm font-black" style="color: #ffffff;">00:00:00</span>
            </div>
            <div class="w-px h-4" style="background-color: rgba(255,255,255,0.3);"></div>
            <button onclick="switchTab('peserta')" class="flex items-center gap-1.5 text-xs font-mono-code font-black transition-colors" style="color: #10b981;" title="Lihat daftar siswa hadir">
                <span class="w-2 h-2 rounded-full animate-ping" style="background-color: #10b981;"></span>
                <span id="header-attendee-count" style="color: #fde047; font-weight: 900;">0</span> Siswa Hadir
            </button>
        </div>

        {{-- Right Controls (Tactile Buttons with Icon Boxes) --}}
        <div class="flex items-center gap-2 flex-shrink-0">
            
            {{-- Tombol Salin Tautan (Sky Tactile) --}}
            <button onclick="copyMeetingLink()" type="button" class="hidden sm:flex items-center gap-1.5 btn-tactile-white px-3.5 py-1.5 rounded-xl text-xs font-mono-code font-black active:scale-95" title="Salin link kelas untuk dibagikan ke siswa via WhatsApp">
                <div class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] border border-black" style="background-color: #0284c7; color: #ffffff;">
                    <i class="fa-solid fa-link"></i>
                </div>
                <span>Salin Tautan</span>
            </button>

            {{-- Tombol Papan Tulis Cepat (Purple Tactile) --}}
            <button onclick="openTabDirect('whiteboard')" type="button" class="flex items-center gap-1.5 btn-tactile-white px-3.5 py-1.5 rounded-xl text-xs font-mono-code font-black active:scale-95" title="Buka Papan Tulis Digital KBM">
                <div class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] border border-black" style="background-color: #7c3aed; color: #ffffff;">
                    <i class="fa-solid fa-pen-ruler"></i>
                </div>
                <span class="hidden md:inline">Papan Tulis</span>
            </button>

            {{-- Tombol Pilihan Tampilan (Zoom View Selector - Amber Tactile) --}}
            <div class="relative inline-block text-left" id="header-view-wrapper">
                <button onclick="toggleViewMenu(event)" type="button" class="flex items-center gap-1.5 btn-tactile-white px-3 py-1.5 rounded-xl text-xs font-mono-code font-black active:scale-95" title="Pilihan Tata Letak Tampilan (Zoom)">
                    <div class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] border border-black" style="background-color: #ff3823; color: #ffffff;">
                        <i id="header-view-icon" class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <span class="hidden md:inline">Tata Letak: <span id="header-view-name" style="color: #ff3823; font-weight: 900;">Ruang Kelas (5x8)</span></span>
                    <i class="fa-solid fa-chevron-down text-[9px] ml-0.5" style="color: #121316;"></i>
                </button>
            </div>

            {{-- Tombol Toggle Panel Materi / Peserta (Indigo Tactile) --}}
            <button onclick="toggleClassroomHub()" type="button" id="btn-toggle-hub" class="flex items-center gap-1.5 btn-tactile-white px-3.5 py-1.5 rounded-xl text-xs font-mono-code font-black active:scale-95" title="Buka atau sembunyikan panel modul dan siswa">
                <div class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] border border-black" style="background-color: #4f46e5; color: #ffffff;">
                    <i class="fa-solid fa-columns"></i>
                </div>
                <span class="hidden sm:inline">Panel KBM</span>
            </button>

            {{-- Profile Chip Guru --}}
            <div class="hidden xl:flex items-center gap-2 bg-white px-2.5 py-1 rounded-xl text-xs" style="border: 2px solid #121316; box-shadow: 2px 2px 0px #121316; color: #121316;" title="{{ $displayName }}">
                <div class="w-6 h-6 rounded-lg flex items-center justify-center text-[11px] font-black uppercase shadow-xs border border-black" style="background-color: #ff3823; color: #ffffff;">
                    {{ substr($displayName, 0, 1) }}
                </div>
                <span class="font-mono-code font-black max-w-[100px] truncate" style="color: #121316;">{{ explode(' ', trim($displayName))[0] }}</span>
            </div>

            {{-- Tombol Akhiri Kelas (Crimson Tactile) --}}
            <button onclick="openEndMeetingModal()" type="button" class="btn-tactile-red px-3.5 sm:px-4 py-1.5 rounded-xl text-xs font-mono-code font-black flex items-center gap-1.5 active:scale-95">
                <i class="fa-solid fa-power-off text-xs"></i>
                <span>Akhiri Kelas</span>
            </button>
        </div>
    </header>

    {{-- 2. MAIN WORKSPACE CONTAINER (SPLIT SCREEN) --}}
    <div class="flex-1 flex flex-row overflow-hidden relative" style="height: calc(100vh - 65px);">
        
        {{-- Conference Stage (Video Call Agora with Dotted Grid Graph Texture) --}}
        <main id="conference-panel" class="flex-1 relative graph-paper-box flex flex-col h-full overflow-hidden transition-all duration-150">
            
            {{-- FLOATING ZOOM-STYLE VIEW SELECTOR (POJOK KANAN ATAS VIDEO) --}}
            <div class="absolute top-4 right-4 z-30 flex items-center gap-2">
                <div class="relative" id="video-view-dropdown-wrapper">
                    <button id="btn-view-toggle" onclick="toggleViewMenu(event)" type="button" class="flex items-center gap-2 btn-tactile-white px-3.5 py-1.5 rounded-xl text-xs font-mono-code font-black transition active:scale-95" title="Ganti Tata Letak Video (Zoom)">
                        <div class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] border border-black" style="background-color: #ff3823; color: #ffffff;">
                            <i id="current-view-icon" class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <span>Tata Letak: <span id="current-view-name" style="color: #ff3823; font-weight: 900;">Ruang Kelas (5x8)</span></span>
                        <i class="fa-solid fa-chevron-down text-[9px]" style="color: #121316;"></i>
                    </button>

                    <div id="view-dropdown-menu" class="hidden absolute right-0 mt-2 w-64 rounded-2xl bg-white p-2.5 z-50 transition-all" style="border: 2px solid #121316; box-shadow: 5px 5px 0px #121316; color: #121316;">
                        <div class="px-3 py-1 text-[10px] font-mono-code font-extrabold uppercase tracking-wider mb-1.5 flex items-center justify-between" style="color: #777; border-bottom: 1.5px solid #e2ded5;">
                            <span>TATA LETAK ZOOM</span>
                            <span>⌜ ⌟</span>
                        </div>
                        
                        <button onclick="changeLayout('classroom')" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-mono-code font-bold hover:bg-[#faf3e0] flex items-center justify-between transition-colors border border-transparent hover:border-[#121316]" style="color: #121316;">
                            <span class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] border border-black" style="background-color: #ff3823; color: #ffffff;"><i class="fa-solid fa-chalkboard-user"></i></span>
                                <span>Model Ruang Kelas (5×8)</span>
                            </span>
                            <i id="check-classroom" class="fa-solid fa-check text-xs font-black" style="color: #ff3823;"></i>
                        </button>

                        <button onclick="changeLayout('gallery')" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-mono-code font-bold hover:bg-[#faf3e0] flex items-center justify-between transition-colors border border-transparent hover:border-[#121316]" style="color: #121316;">
                            <span class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] border border-black" style="background-color: #2563eb; color: #ffffff;"><i class="fa-solid fa-table-cells"></i></span>
                                <span>Tampilan Galeri</span>
                            </span>
                            <i id="check-gallery" class="fa-solid fa-check text-xs hidden font-black" style="color: #2563eb;"></i>
                        </button>

                        <button onclick="changeLayout('speaker')" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-mono-code font-bold hover:bg-[#faf3e0] flex items-center justify-between transition-colors border border-transparent hover:border-[#121316]" style="color: #121316;">
                            <span class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] border border-black" style="background-color: #fbc02d; color: #121316;"><i class="fa-solid fa-user-tie"></i></span>
                                <span>Tampilan Pembicara</span>
                            </span>
                            <i id="check-speaker" class="fa-solid fa-check text-xs hidden font-black" style="color: #fbc02d;"></i>
                        </button>

                        <button onclick="changeLayout('sidebyside')" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-mono-code font-bold hover:bg-[#faf3e0] flex items-center justify-between transition-colors border border-transparent hover:border-[#121316]" style="color: #121316;">
                            <span class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] border border-black" style="background-color: #10b981; color: #ffffff;"><i class="fa-solid fa-columns"></i></span>
                                <span>Berdampingan (KBM)</span>
                            </span>
                            <i id="check-sidebyside" class="fa-solid fa-check text-xs hidden font-black" style="color: #10b981;"></i>
                        </button>

                        <div class="h-px my-1.5" style="background-color: #e2ded5;"></div>

                        <button onclick="toggleAppFullscreen()" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-mono-code font-bold hover:bg-[#faf3e0] flex items-center gap-2.5 transition-colors border border-transparent hover:border-[#121316]" style="color: #121316;">
                            <span class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] border border-black" style="background-color: #fbc02d; color: #121316;"><i id="menu-fs-icon" class="fa-solid fa-expand"></i></span>
                            <span id="menu-fs-text">Layar Penuh (F11)</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- STATUS INDIKATOR GURU (TENGAH ATAS - TACTILE PILL) --}}
            <div id="solo-teacher-banner" class="absolute top-4 left-1/2 -translate-x-1/2 z-20 pointer-events-auto transition-all duration-300 hidden">
                <div class="flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-mono-code font-black" style="background-color: #fbc02d; color: #121316; border: 2px solid #121316; box-shadow: 3px 3px 0px #121316;">
                    <span class="w-2.5 h-2.5 rounded-full animate-ping" style="background-color: #10b981;"></span>
                    <i class="fa-solid fa-video text-xs"></i>
                    <span>KAMERA AKTIF • MENUNGGU SISWA MASUK</span>
                </div>
            </div>

            {{-- 1. CLASSROOM VIEW STAGE (DEFAULT: Model Ruang Kelas 5x8 Kursi Siswa + Meja Guru Depan) --}}
            <div id="stage-classroom-wrapper" class="flex flex-1 flex-col h-full overflow-hidden p-2 sm:p-3 pb-28 gap-2 relative min-h-0">
                
                {{-- AREA MIMBAR / PODIUM GURU (DEPAN KELAS) --}}
                <div class="w-full flex flex-col items-center justify-center flex-shrink-0">
                    <div class="flex items-center gap-2 mb-1.5 flex-wrap justify-center">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-mono-code font-black uppercase tracking-wider" style="background-color: #ff3823; color: #ffffff; border: 1.5px solid #121316; box-shadow: 2px 2px 0px #121316;">
                            <span class="w-2 h-2 rounded-full animate-ping" style="background-color: #fde047;"></span> ✱ PODIUM GURU / DEPAN KELAS
                        </span>
                        <span id="classroom-occupancy-pill" class="px-2.5 py-1 rounded-full text-[10px] font-mono-code font-extrabold" style="background-color: #fbc02d; color: #121316; border: 1.5px solid #121316; box-shadow: 2px 2px 0px #121316;">
                            0 / 40 Kursi Terisi
                        </span>
                    </div>

                    {{-- Teacher Video Spotlight Slot --}}
                    <div id="podium-teacher-slot" class="w-full max-w-xl h-44 sm:h-52 md:h-60 flex items-center justify-center">
                        {{-- Video tile guru masuk ke sini secara otomatis --}}
                    </div>
                </div>

                {{-- DENAH TEMPAT DUDUK SISWA (5 BARIS X 8 KOLOM = 40 BANGKU) --}}
                <div class="flex-1 overflow-y-auto px-1 sm:px-3 pt-1">
                    <div class="max-w-6xl mx-auto">
                        <div class="flex items-center justify-between px-2 py-1 mb-2 border-b-2 border-[#121316]/20">
                            <span class="text-[10px] font-mono-code font-black uppercase tracking-wider text-[#121316] flex items-center gap-1.5">
                                <i class="fa-solid fa-chalkboard-user text-[#ff3823]"></i>
                                <span>DENAH BANGKU SISWA (5 BARIS × 8 KOLOM = 40 MEJA)</span>
                            </span>
                            <span class="text-[10px] font-mono-code font-bold text-[#555] hidden sm:inline">
                                Pintu Masuk ➜ Baris 1 Depan s/d Baris 5 Belakang
                            </span>
                        </div>
                        <div id="classroom-grid" class="grid grid-cols-4 sm:grid-cols-6 lg:grid-cols-8 gap-2 sm:gap-2.5">
                            {{-- 40 slot kursi siswa (terisi / kosong) dirender dinamis di sini --}}
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. SPEAKER VIEW STAGE (Active when currentLayout === 'speaker') --}}
            <div id="stage-speaker-wrapper" class="hidden flex-1 flex-col h-full overflow-hidden p-3 pb-28 gap-2.5 relative min-h-0">
                {{-- Top Filmstrip Bar: Thumbnail peserta lain --}}
                <div id="speaker-filmstrip" class="flex items-center gap-2.5 overflow-x-auto p-2 h-28 shrink-0 rounded-2xl backdrop-blur-sm scroll-smooth" style="background-color: rgba(255,255,255,0.9); border: 2px solid #121316; box-shadow: 3px 3px 0px #121316;">
                    {{-- Non-pinned tiles live here --}}
                </div>
                {{-- Main Spotlight Screen: 1 Peserta Terfokus (Guru/Siswa) --}}
                <div id="speaker-main-stage" class="flex-1 flex items-center justify-center overflow-hidden min-h-0 relative p-1">
                    {{-- Pinned tile lives here --}}
                </div>
            </div>

            {{-- 3. GALLERY VIEW STAGE (Active when currentLayout === 'gallery' or 'sidebyside') --}}
            {{-- pb-28 memastikan video tile TIDAK PERNAH tertutup oleh dock mengambang di bawah --}}
            <div id="stage-gallery-wrapper" class="hidden flex-1 items-center justify-center overflow-hidden h-full p-2.5 sm:p-4 pb-28 min-h-0 relative">
                <div id="gallery-grid" class="w-full h-full flex items-center justify-center gap-3.5 transition-all duration-300">
                    {{-- Local video tile (Guru) --}}
                    <div id="video-tile-local" class="video-tile group relative rounded-3xl overflow-hidden flex items-center justify-center w-full h-full max-h-[82vh] transition-all" style="background-color: #0f172a; border: 2.5px solid #121316; box-shadow: 4px 4px 0px #121316;">
                        <div id="agora-local-video" class="w-full h-full relative">
                            {{-- Agora video injected here --}}
                        </div>
                        <div id="agora-local-placeholder" class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none" style="background-color: #0f172a; color: #cbd5e1;">
                            <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl font-black text-white mb-2 shadow-lg" style="background: linear-gradient(135deg, #ff3823, #fbc02d); border: 2px solid #ffffff;">
                                {{ substr($displayName, 0, 1) }}
                            </div>
                            <p class="text-xs font-mono-code font-bold" style="color: #fde047;">Mengaktifkan kamera...</p>
                        </div>
                        
                        {{-- 🎯 BADGE NAMA GURU: TEPAT DI POJOK KIRI ATAS - 100% AMAN DARI MENU BAR BAWAH --}}
                        <div class="absolute top-4 left-4 z-20 flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-black shadow-md" style="background-color: #121316; color: #ffffff; border: 2px solid #121316; box-shadow: 3px 3px 0px #ff3823;" title="{{ $displayName }}">
                            <span class="w-2 h-2 rounded-full animate-pulse" style="background-color: #10b981;"></span>
                            <i id="tile-mic-local" class="fa-solid fa-microphone text-xs" style="color: #10b981;"></i>
                            <span class="truncate max-w-[150px] font-mono-code font-bold" style="color: #fde047;">Pak {{ explode(' ', trim($displayName))[0] }}</span>
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-mono-code font-black uppercase tracking-wider" style="background-color: #ff3823; color: #ffffff;">HOST GURU</span>
                        </div>
                        
                        <div class="absolute top-4 right-4 z-20 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="pinSpeaker('local')" type="button" class="p-2 rounded-xl text-xs active:translate-x-0.5 active:translate-y-0.5 transition" style="background-color: #ffffff; color: #121316; border: 2px solid #121316; box-shadow: 2px 2px 0px #121316;" title="Fokuskan / Spotlight">
                                <i class="fa-solid fa-thumbtack text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Floating Teacher Action Dock (Neo-Tactile Island Khas PembdaHUB - Tombol Kontras Tinggi) --}}
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2 sm:gap-2.5 px-3.5 sm:px-5 py-2.5 rounded-2xl sm:rounded-full" style="background-color: #ffffff; border: 2.5px solid #121316; box-shadow: 6px 6px 0px #121316;">
                
                {{-- Toggle Mic --}}
                <button id="btn-toggle-mic" onclick="toggleMic()" type="button" class="group relative p-3 rounded-xl transition active:translate-x-0.5 active:translate-y-0.5" style="background-color: #10b981; color: #ffffff; border: 2px solid #121316; box-shadow: 2px 2px 0px #121316;" title="Nyalakan/Matikan Mikrofon">
                    <i id="mic-icon" class="fa-solid fa-microphone text-base text-white"></i>
                    <span class="absolute -top-9 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2.5 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">Mic</span>
                </button>

                {{-- Toggle Camera --}}
                <button id="btn-toggle-cam" onclick="toggleCam()" type="button" class="group relative p-3 rounded-xl transition active:translate-x-0.5 active:translate-y-0.5" style="background-color: #0891b2; color: #ffffff; border: 2px solid #121316; box-shadow: 2px 2px 0px #121316;" title="Nyalakan/Matikan Kamera">
                    <i id="cam-icon" class="fa-solid fa-video text-base text-white"></i>
                    <span class="absolute -top-9 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2.5 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">Kamera</span>
                </button>

                {{-- Screen Share --}}
                <button onclick="toggleScreenShare()" id="btn-screenshare" type="button" class="group relative p-3 rounded-xl transition active:translate-x-0.5 active:translate-y-0.5" style="background-color: #2563eb; color: #ffffff; border: 2px solid #121316; box-shadow: 2px 2px 0px #121316;" title="Bagikan Layar Anda">
                    <i class="fa-solid fa-desktop text-base text-white"></i>
                    <span class="absolute -top-9 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2.5 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">Bagi Layar</span>
                </button>

                <div class="w-px h-6 hidden sm:block" style="background-color: #121316;"></div>

                {{-- Mute All Button (Amber Tactile) --}}
                <button onclick="teacherMuteEveryone()" type="button" class="flex items-center gap-1.5 btn-tactile-yellow px-3.5 sm:px-4 py-2.5 rounded-xl text-xs font-mono-code font-black active:scale-95" title="Kondusifkan Kelas: Minta seluruh siswa mematikan mic">
                    <i class="fa-solid fa-volume-xmark text-sm"></i>
                    <span class="hidden sm:inline">Mute Semua</span>
                </button>

                <div class="w-px h-6 hidden sm:block" style="background-color: #121316;"></div>

                {{-- Quick Open Material (Indigo Tactile) --}}
                <button onclick="openTabDirect('materi')" type="button" class="flex items-center gap-1.5 px-3.5 sm:px-4 py-2.5 rounded-xl text-xs font-mono-code font-black transition-all active:translate-x-0.5 active:translate-y-0.5" style="background-color: #4f46e5; color: #ffffff; border: 2px solid #121316; box-shadow: 2px 2px 0px #121316;" title="Buka silabus materi & modul kelas">
                    <i class="fa-solid fa-book-open text-sm" style="color: #fde047;"></i>
                    <span class="hidden sm:inline">Modul</span>
                </button>

                {{-- Quick Open Whiteboard (Purple Tactile) --}}
                <button onclick="openTabDirect('whiteboard')" type="button" class="flex items-center gap-1.5 px-3.5 sm:px-4 py-2.5 rounded-xl text-xs font-mono-code font-black transition-all active:translate-x-0.5 active:translate-y-0.5" style="background-color: #7c3aed; color: #ffffff; border: 2px solid #121316; box-shadow: 2px 2px 0px #121316;" title="Tulis rumus atau coretan di papan tulis">
                    <i class="fa-solid fa-pen-ruler text-sm" style="color: #fde047;"></i>
                    <span class="hidden sm:inline">Papan Tulis</span>
                </button>

                {{-- Quick Open Attendees (White Tactile with Live Emerald Badge) --}}
                <button onclick="openTabDirect('peserta')" type="button" class="flex items-center gap-1.5 px-3.5 sm:px-4 py-2.5 rounded-xl text-xs font-mono-code font-black transition-all active:translate-x-0.5 active:translate-y-0.5" style="background-color: #ffffff; color: #121316; border: 2px solid #121316; box-shadow: 2px 2px 0px #121316;" title="Lihat kehadiran siswa secara realtime">
                    <i class="fa-solid fa-users-viewfinder text-sm" style="color: #2563eb;"></i>
                    <span class="hidden sm:inline">Presensi</span>
                    <span id="quick-attendee-badge" class="px-2 py-0.5 rounded-full text-[10px] font-mono-code font-black" style="background-color: #10b981; color: #ffffff; border: 1px solid #121316;">0</span>
                </button>
            </div>
        </main>

        {{-- Resize Handle Divider --}}
        <div id="resize-handler" class="w-[10px] cursor-col-resize select-none z-20 flex items-center justify-center relative transition-colors duration-150 group" style="background-color: #ede9df; border-left: 1.5px solid #121316; border-right: 1.5px solid #121316;" title="Geser untuk mengatur lebar panel materi">
            <div class="w-4 h-12 rounded-md flex items-center justify-center pointer-events-none" style="background-color: #ffffff; border: 2px solid #121316; box-shadow: 2px 2px 0px #121316;">
                <i class="fa-solid fa-grip-lines-vertical text-[10px]" style="color: #121316;"></i>
            </div>
        </div>

        {{-- Resize Overlay (Mencegah iframe menyerap pointer saat drag) --}}
        <div id="resize-overlay" class="fixed inset-0 z-40 hidden cursor-col-resize"></div>

        {{-- 3. CLASSROOM MULTI-HUB PANEL (TACTILE BINDER STYLE KHAS PEMBDAHUB) --}}
        <aside id="classroom-hub" class="w-[520px] max-w-[90vw] flex flex-col h-full z-15 shadow-2xl relative" style="background-color: #faf8f5; border-left: 2.5px solid #121316;">
            
            {{-- Tabs Header Bar (Tactile Binder Style) --}}
            <div class="flex items-center p-2 gap-2 flex-shrink-0" style="background-color: #ede9df; border-bottom: 2px solid #121316;">
                <button id="tab-btn-materi" onclick="switchTab('materi')" type="button" class="flex-1 py-2 px-3 text-xs font-mono-code font-black transition-all flex items-center justify-center gap-2 rounded-xl" style="background-color: #121316; color: #fde047; border: 2px solid #121316; box-shadow: 2px 2px 0px #ff3823;">
                    <i class="fa-solid fa-book text-xs" style="color: #ff3823;"></i>
                    <span>Materi & Modul</span>
                </button>
                <button id="tab-btn-whiteboard" onclick="switchTab('whiteboard')" type="button" class="flex-1 py-2 px-3 text-xs font-mono-code font-bold transition-all flex items-center justify-center gap-2 rounded-xl" style="background-color: #ffffff; color: #121316; border: 2px solid #121316;">
                    <i class="fa-solid fa-pen-ruler text-xs" style="color: #7c3aed;"></i>
                    <span>Papan Tulis</span>
                </button>
                <button id="tab-btn-peserta" onclick="switchTab('peserta')" type="button" class="flex-1 py-2 px-3 text-xs font-mono-code font-bold transition-all flex items-center justify-center gap-2 rounded-xl" style="background-color: #ffffff; color: #121316; border: 2px solid #121316;">
                    <i class="fa-solid fa-users text-xs" style="color: #10b981;"></i>
                    <span>Siswa (<span id="attendee-tab-count">0</span>)</span>
                </button>
                <button onclick="toggleClassroomHub()" type="button" class="p-2 rounded-xl transition-colors shadow-xs" style="background-color: #ffffff; color: #121316; border: 2px solid #121316;" title="Tutup panel">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- TAB 1: MATERI & MODUL PEMBELAJARAN --}}
            <div id="tab-content-materi" class="flex-1 flex flex-col overflow-hidden">
                
                {{-- Viewer Preview Box (Ketika Guru Membuka Materi) --}}
                <div id="material-preview-box" class="h-1/2 min-h-[220px] bg-white flex flex-col hidden" style="border-bottom: 2px solid #121316;">
                    <div class="px-4 py-2 flex items-center justify-between" style="background-color: #faf3e0; border-bottom: 2px solid #121316;">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-6 h-6 rounded-md flex items-center justify-center text-xs border border-black" style="background-color: #ff3823; color: #ffffff;"><i id="preview-icon" class="fa-regular fa-file-pdf"></i></span>
                            <h3 id="preview-title" class="text-xs font-black truncate" style="color: #121316;">Judul Materi</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <a id="preview-external-link" href="#" target="_blank" class="btn-tactile-white text-[10px] font-mono-code font-bold px-2.5 py-1 rounded-lg flex items-center gap-1">
                                Tab Baru <i class="fa-solid fa-arrow-up-right-from-square text-[8px]"></i>
                            </a>
                            <button onclick="closeMaterialPreview()" type="button" class="p-1 rounded-md border border-black hover:bg-[#ff3823] hover:text-white" style="color: #121316;" title="Tutup preview materi">
                                <i class="fa-solid fa-times text-xs"></i>
                            </button>
                        </div>
                    </div>
                    <div class="flex-1 relative bg-white overflow-hidden">
                        <iframe id="preview-iframe" class="w-full h-full border-none hidden" allow="autoplay; clipboard-write; clipboard-read"></iframe>
                        <div id="preview-text" class="p-4 text-xs overflow-y-auto h-full prose max-w-none hidden font-mono-code" style="color: #121316;"></div>
                    </div>
                </div>

                {{-- Daftar Modul & Materi Silabus (Graph Texture with Poster Cards) --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-3.5 graph-paper-box">
                    <div class="flex items-center justify-between pb-1">
                        <span class="text-[11px] font-mono-code font-extrabold uppercase tracking-wider flex items-center gap-1" style="color: #ff3823;">
                            <span>✱</span> SILABUS & BAHAN AJAR
                        </span>
                        <span class="text-[10px] font-mono-code font-bold" style="color: #777;">Klik "Buka" untuk tayang</span>
                    </div>

                    @php
                        $modules = $course->modules()->ordered()->active()->get();
                    @endphp

                    @if($modules->isEmpty())
                        <div class="text-center text-xs py-12 poster-card bg-white p-6" style="color: #777;">
                            <div class="w-14 h-14 mx-auto mb-3 rounded-2xl flex items-center justify-center text-2xl shadow-sm" style="background-color: #ede9df; border: 2px solid #121316; color: #ff3823;">
                                <i class="fa-regular fa-folder-open"></i>
                            </div>
                            <h4 class="font-black text-sm" style="color: #121316;">Belum Ada Materi</h4>
                            <p class="font-medium mt-1">Belum ada modul atau materi di kelas ini.</p>
                            <a href="{{ route('guru.lms.materials.create', $course->id) }}" target="_blank" class="mt-3 inline-flex items-center gap-1.5 btn-tactile-red text-xs font-mono-code font-bold px-3.5 py-1.5 rounded-xl">
                                <i class="fa-solid fa-plus"></i> Tambah Materi Baru
                            </a>
                        </div>
                    @else
                        @foreach($modules as $module)
                            <div class="poster-card bg-white overflow-hidden" style="border: 2px solid #121316; box-shadow: 3px 3px 0px #121316;">
                                {{-- Header Modul: Hitam Pekat dengan Teks Putih & Kuning (100% Terbaca) --}}
                                <div class="p-3 flex items-center justify-between" style="background-color: #121316 !important; color: #ffffff !important; border-bottom: 2px solid #121316 !important;">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: #fde047; display: inline-block;"></span>
                                        <h4 class="text-xs font-black truncate font-mono-code" style="color: #ffffff !important;">{{ $module->title }}</h4>
                                    </div>
                                    <span class="text-[10px] font-mono-code font-bold px-2 py-0.5 rounded-md" style="background-color: #fbc02d; color: #121316; border: 1.5px solid #000000;">{{ $module->materials()->active()->count() }} Materi</span>
                                </div>
                                <div class="p-2.5 space-y-2" style="background-color: #faf8f5;">
                                    @foreach($module->materials()->active()->ordered()->get() as $material)
                                        @php
                                             $url = '';
                                             if ($material->material_type == 'video' && $material->isYouTubeVideo()) {
                                                 $url = $material->getVideoEmbedUrl();
                                             } elseif ($material->file_path) {
                                                 $url = asset('storage/' . $material->file_path);
                                             } else {
                                                 $url = $material->file_url;
                                             }
                                             $safeTitle = addslashes($material->title);
                                             $safeContent = $material->content ? addslashes($material->content) : '';
                                        @endphp
                                        <div class="flex items-center justify-between p-2.5 rounded-xl text-xs transition-all" style="background-color: #ffffff; border: 1.5px solid #121316; box-shadow: 2px 2px 0px #121316;">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                @if($material->material_type == 'pdf')
                                                    <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs flex-shrink-0" style="background-color: #ff3823; color: #ffffff; border: 1.5px solid #121316;"><i class="fa-regular fa-file-pdf"></i></span>
                                                @elseif($material->material_type == 'video')
                                                    <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs flex-shrink-0" style="background-color: #fbc02d; color: #121316; border: 1.5px solid #121316;"><i class="fa-regular fa-play-circle"></i></span>
                                                @elseif($material->material_type == 'link')
                                                    <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs flex-shrink-0" style="background-color: #0891b2; color: #ffffff; border: 1.5px solid #121316;"><i class="fa-solid fa-link"></i></span>
                                                @elseif($material->material_type == 'canva' || $material->material_type == 'googledocs')
                                                    <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs flex-shrink-0" style="background-color: #10b981; color: #ffffff; border: 1.5px solid #121316;"><i class="fa-solid fa-file-lines"></i></span>
                                                @else
                                                    <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs flex-shrink-0" style="background-color: #4f46e5; color: #ffffff; border: 1.5px solid #121316;"><i class="fa-regular fa-file-lines"></i></span>
                                                @endif
                                                <span class="text-[11px] font-bold truncate" style="color: #121316;">{{ $material->title }}</span>
                                            </div>
                                            <button onclick="displayMaterial('{{ $safeTitle }}', '{{ $material->material_type }}', '{{ $url }}', '{{ $safeContent }}')" type="button" class="btn-tactile-white text-[10px] font-mono-code font-black px-2.5 py-1 rounded-lg flex items-center gap-1 flex-shrink-0">
                                                Buka <i class="fa-solid fa-arrow-up-right-from-square text-[8px]"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- TAB 2: PAPAN TULIS DIGITAL KBM (WHITEBOARD CANVAS TACTILE) --}}
            <div id="tab-content-whiteboard" class="flex-1 flex flex-col overflow-hidden hidden" style="background-color: #faf8f5;">
                
                {{-- Toolbar Papan Tulis --}}
                <div class="p-2.5 flex flex-wrap items-center justify-between gap-2 flex-shrink-0 shadow-xs" style="background-color: #ffffff; border-bottom: 2px solid #121316;">
                    
                    {{-- Warna & Mode --}}
                    <div class="flex items-center gap-1.5">
                        <button onclick="setWhiteboardTool('pen')" id="tool-pen" type="button" class="p-2 rounded-xl text-xs font-bold" style="background-color: #4f46e5; color: #ffffff; border: 2px solid #121316; box-shadow: 2px 2px 0px #121316;" title="Spidol / Pena">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button onclick="setWhiteboardTool('eraser')" id="tool-eraser" type="button" class="p-2 rounded-xl text-xs font-bold" style="background-color: #ffffff; color: #121316; border: 2px solid #121316; box-shadow: 2px 2px 0px #121316;" title="Penghapus Coretan">
                            <i class="fa-solid fa-eraser"></i>
                        </button>

                        <div class="w-px h-6 mx-1" style="background-color: #121316;"></div>

                        {{-- Palet Warna Coretan --}}
                        <div class="flex items-center gap-1.5">
                            <button onclick="setPenColor('#121316')" class="w-6 h-6 rounded-lg hover:scale-110 transition-transform shadow-xs" style="background-color: #121316; border: 2px solid #000000;" title="Hitam"></button>
                            <button onclick="setPenColor('#2563eb')" class="w-6 h-6 rounded-lg hover:scale-110 transition-transform shadow-xs" style="background-color: #2563eb; border: 2px solid #000000;" title="Biru"></button>
                            <button onclick="setPenColor('#ff3823')" class="w-6 h-6 rounded-lg hover:scale-110 transition-transform shadow-xs" style="background-color: #ff3823; border: 2px solid #000000;" title="Merah"></button>
                            <button onclick="setPenColor('#10b981')" class="w-6 h-6 rounded-lg hover:scale-110 transition-transform shadow-xs" style="background-color: #10b981; border: 2px solid #000000;" title="Hijau"></button>
                            <button onclick="setPenColor('#fbc02d')" class="w-6 h-6 rounded-lg hover:scale-110 transition-transform shadow-xs" style="background-color: #fbc02d; border: 2px solid #000000;" title="Kuning"></button>
                        </div>
                    </div>

                    {{-- Tebal Garis & Latar Papan --}}
                    <div class="flex items-center gap-1.5">
                        <select id="pen-size-select" onchange="setPenSize(this.value)" class="text-xs font-mono-code font-bold rounded-xl px-2.5 py-1.5 outline-none" style="background-color: #ffffff; color: #121316; border: 2px solid #121316; box-shadow: 2px 2px 0px #121316;">
                            <option value="2">Halus (2px)</option>
                            <option value="4" selected>Sedang (4px)</option>
                            <option value="8">Tebal (8px)</option>
                            <option value="16">Stabilo (16px)</option>
                        </select>

                        <select id="canvas-bg-select" onchange="setCanvasBackground(this.value)" class="text-xs font-mono-code font-bold rounded-xl px-2.5 py-1.5 outline-none" style="background-color: #ffffff; color: #121316; border: 2px solid #121316; box-shadow: 2px 2px 0px #121316;">
                            <option value="whiteboard" selected>Whiteboard Putih</option>
                            <option value="grid">Grid Matematika</option>
                            <option value="chalkboard">Papan Tulis Hijau</option>
                        </select>
                    </div>

                    {{-- Actions: Undo, Clear, Save --}}
                    <div class="flex items-center gap-1.5 ml-auto">
                        <button onclick="undoWhiteboard()" type="button" class="btn-tactile-white p-2 rounded-xl text-xs font-bold" title="Batalkan coretan terakhir">
                            <i class="fa-solid fa-rotate-left"></i>
                        </button>
                        <button onclick="clearWhiteboard()" type="button" class="btn-tactile-red p-2 rounded-xl text-xs font-bold" title="Hapus semua coretan papan tulis">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                        <button onclick="downloadWhiteboard()" type="button" class="btn-tactile-yellow px-3 py-1.5 rounded-xl font-mono-code font-black text-xs flex items-center gap-1" title="Unduh hasil coretan sebagai gambar PNG">
                            <i class="fa-solid fa-download"></i> <span class="hidden xl:inline">Simpan</span>
                        </button>
                    </div>
                </div>

                {{-- Drawing Canvas Area with Tactile Border --}}
                <div id="whiteboard-container" class="flex-1 relative overflow-hidden canvas-whiteboard cursor-crosshair m-2.5 rounded-2xl" style="border: 2px solid #121316; box-shadow: 3px 3px 0px #121316;">
                    <canvas id="whiteboard-canvas" class="w-full h-full block"></canvas>
                </div>
            </div>

            {{-- TAB 3: DAFTAR SISWA & PRESENSI REALTIME --}}
            <div id="tab-content-peserta" class="flex-1 flex flex-col overflow-hidden hidden" style="background-color: #faf8f5;">
                
                {{-- Header Sub-bar --}}
                <div class="p-3 flex items-center justify-between flex-shrink-0 shadow-xs" style="background-color: #ffffff; border-bottom: 2px solid #121316;">
                    <div>
                        <div class="text-[10px] font-mono-code font-black uppercase" style="color: #ff3823;">✱ REALTIME KBM</div>
                        <h3 class="text-xs font-black" style="color: #121316;">Presensi Tatap Muka</h3>
                    </div>
                    <a href="{{ route('guru.lms.meeting.attendance', $course->id) }}" target="_blank" class="btn-tactile-yellow text-[10px] font-mono-code font-bold px-3 py-1.5 rounded-xl flex items-center gap-1.5">
                        <i class="fa-solid fa-file-invoice"></i> Rekap Sesi
                    </a>
                </div>

                {{-- List Peserta (Vibrant Poster Card Style) --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-2.5 graph-paper-box" id="attendees-list">
                    <div class="text-center text-xs py-12 poster-card bg-white p-6" style="color: #777;">
                        <i class="fa-solid fa-spinner animate-spin text-2xl mb-2 block" style="color: #4f46e5;"></i>
                        <p class="font-mono-code font-bold">Memuat daftar siswa hadir...</p>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    {{-- 4. MODAL KONFIRMASI AKHIRI KELAS (TACTILE NEO-BRUTALIST KHAS PEMBDAHUB) --}}
    <div id="end-meeting-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden" style="background-color: rgba(18, 19, 22, 0.75); backdrop-filter: blur(2px);">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 relative" style="border: 2px solid #121316; box-shadow: 8px 8px 0px #ff3823;">
            <span class="absolute top-2.5 left-3 text-xs select-none font-mono-code" style="color: #999;">⌜</span>
            <span class="absolute top-2.5 right-3 text-xs select-none font-mono-code" style="color: #999;">⌝</span>
            <span class="absolute bottom-2.5 left-3 text-xs select-none font-mono-code" style="color: #999;">⌞</span>
            <span class="absolute bottom-2.5 right-3 text-xs select-none font-mono-code" style="color: #999;">⌟</span>

            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl" style="background-color: #ff3823; color: #ffffff; border: 2px solid #121316; box-shadow: 3px 3px 0px #121316;">
                <i class="fa-solid fa-power-off"></i>
            </div>
            <div>
                <div class="text-[10px] font-mono-code font-black uppercase" style="color: #ff3823;">✱ KONFIRMASI SELESAI KBM</div>
                <h3 class="text-lg font-black" style="color: #121316;">Akhiri Sesi PembdaTEMU?</h3>
                <p class="text-xs mt-1.5 leading-relaxed font-medium" style="color: #555;">
                    Sesi PembdaTEMU untuk mata pelajaran <strong>{{ $course->course_name ?? $course->name }}</strong> akan ditutup. Seluruh siswa akan diarahkan kembali ke beranda modul dan total durasi kehadiran siswa otomatis tersimpan di rekap presensi.
                </p>
            </div>
            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button onclick="closeEndMeetingModal()" type="button" class="btn-tactile-white px-4 py-2 rounded-xl text-xs font-mono-code font-bold">
                    Batal
                </button>
                <form action="{{ route('guru.lms.meeting.stop', $course->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-tactile-red px-4 py-2 rounded-xl text-xs font-mono-code font-black flex items-center gap-1.5">
                        <i class="fa-solid fa-check"></i> Ya, Akhiri PembdaTEMU
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- 5. TOAST NOTIFICATION --}}
    <div id="copy-toast" class="fixed bottom-6 right-6 z-50 px-4 py-2.5 rounded-xl text-xs font-mono-code font-bold flex items-center gap-2 transform translate-y-12 opacity-0 transition-all duration-300 pointer-events-none" style="background-color: #121316; color: #fde047; border: 2px solid #121316; box-shadow: 4px 4px 0px #ff3823;">
        <i class="fa-solid fa-circle-check text-base" style="color: #10b981;"></i>
        <span>Tautan PembdaTEMU berhasil disalin! Bagikan ke siswa via WhatsApp.</span>
    </div>

    {{-- AGORA WEB SDK CDN --}}
    <script src="https://download.agora.io/sdk/release/AgoraRTC_N.js"></script>

    <script>
        // Agora Session Variables
        const AGORA_APP_ID  = "{{ $agoraAppId }}";
        const AGORA_TOKEN   = "{{ $agoraToken }}" || null;
        const AGORA_CHANNEL = "{{ $agoraChannel }}";
        const AGORA_UID     = {{ $agoraUid }};
        const COURSE_ID     = {{ $course->id }};
        const IS_HOST       = true;

        let agoraClient      = null;
        let localAudioTrack  = null;
        let localVideoTrack  = null;
        let localScreenTrack = null;
        let isScreenSharing  = false;
        let isMuted          = false;
        let isCamOff         = false;

        // Zoom Layout State Variables (Default: Model Ruang Kelas 5x8 Kursi)
        let currentLayout    = 'classroom'; // 'classroom' | 'gallery' | 'speaker' | 'sidebyside'
        let currentPinnedUid = 'local';     // 'local' | remote uid
        let attendeesMap     = {};          // user.uid => student name

        // ============================================================
        // 1. INISIALISASI AGORA CLIENT & WEBRTC
        // ============================================================
        async function initAgora() {
            if (!AGORA_APP_ID) {
                showAgoraError("AGORA_APP_ID belum dikonfigurasi di file .env server.");
                return;
            }

            // Inisialisasi client Agora RTC
            agoraClient = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' });

            // Deteksi Active Speaker (Voice Activity Detection - Border Hijau saat berbicara)
            agoraClient.enableAudioVolumeIndicator();
            agoraClient.on('volume-indicator', (volumes) => {
                document.querySelectorAll('.video-tile').forEach(t => t.classList.remove('speaking'));
                volumes.forEach(vol => {
                    if (vol.level > 15) {
                        const targetTileId = (vol.uid === 0 || vol.uid === AGORA_UID) ? 'video-tile-local' : `video-tile-${vol.uid}`;
                        const tileEl = document.getElementById(targetTileId);
                        if (tileEl) tileEl.classList.add('speaking');
                    }
                });
            });

            // Remote User Published (Siswa mengaktifkan video atau mikrofon)
            agoraClient.on('user-published', async (user, mediaType) => {
                await agoraClient.subscribe(user, mediaType);

                if (mediaType === 'video') {
                    let tile = document.getElementById(`video-tile-${user.uid}`);
                    if (!tile) {
                        tile = createRemoteTile(user);
                        document.getElementById('gallery-grid').appendChild(tile);
                    }
                    user.videoTrack.play(`remote-${user.uid}`);
                    const placeholder = document.getElementById(`remote-placeholder-${user.uid}`);
                    if (placeholder) placeholder.style.display = 'none';
                }

                if (mediaType === 'audio') {
                    user.audioTrack.play();
                    const micIcon = document.getElementById(`tile-mic-${user.uid}`);
                    if (micIcon) {
                        micIcon.className = 'fa-solid fa-microphone text-xs';
                        micIcon.style.color = '#10b981';
                    }
                }

                updateParticipantCount();
                rearrangeTiles();
            });

            // Remote User Unpublished (Siswa mematikan kamera atau mikrofon)
            agoraClient.on('user-unpublished', (user, mediaType) => {
                if (mediaType === 'video') {
                    const placeholder = document.getElementById(`remote-placeholder-${user.uid}`);
                    if (placeholder) placeholder.style.display = 'flex';
                }
                if (mediaType === 'audio') {
                    const micIcon = document.getElementById(`tile-mic-${user.uid}`);
                    if (micIcon) {
                        micIcon.className = 'fa-solid fa-microphone-slash text-xs';
                        micIcon.style.color = '#ff3823';
                    }
                }
            });

            // Remote User Left (Siswa keluar dari kelas)
            agoraClient.on('user-left', (user) => {
                const tile = document.getElementById(`video-tile-${user.uid}`);
                if (tile) tile.remove();
                if (currentPinnedUid === String(user.uid)) {
                    currentPinnedUid = 'local';
                }
                updateParticipantCount();
                rearrangeTiles();
            });

            // Join channel
            await agoraClient.join(AGORA_APP_ID, AGORA_CHANNEL, AGORA_TOKEN || null, AGORA_UID);

            // Buat track audio & video lokal
            [localAudioTrack, localVideoTrack] = await AgoraRTC.createMicrophoneAndCameraTracks();

            // Tampilkan video lokal
            localVideoTrack.play('agora-local-video');
            const placeholder = document.getElementById('agora-local-placeholder');
            if (placeholder) placeholder.style.display = 'none';

            // Publish stream ke channel
            await agoraClient.publish([localAudioTrack, localVideoTrack]);

            // Tata letak awal
            rearrangeTiles();
        }

        // 🎯 BUAT DOM TILE UNTUK VIDEO REMOTE SISWA (TACTILE POSTER FRAME)
        // BADGE NAMA SISWA DITETAPKAN DI POJOK KIRI ATAS AGAR TIDAK TERTUTUP BAR MENU!
        function createRemoteTile(user) {
            const tile = document.createElement('div');
            tile.id = `video-tile-${user.uid}`;
            tile.className = 'video-tile group relative rounded-3xl overflow-hidden flex items-center justify-center transition-all duration-300';
            tile.style.backgroundColor = '#0f172a';
            tile.style.border = '2.5px solid #121316';
            tile.style.boxShadow = '4px 4px 0px #121316';
            
            const rawName = attendeesMap[user.uid] || `Siswa #${user.uid}`;
            const shortName = rawName.split(' ')[0];

            tile.innerHTML = `
                <div id="remote-${user.uid}" class="w-full h-full relative"></div>
                <div id="remote-placeholder-${user.uid}" class="absolute inset-0 flex flex-col items-center justify-center hidden pointer-events-none" style="background-color: #121316; color: #cbd5e1;">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl font-black text-white mb-2 shadow-lg" style="background: linear-gradient(135deg, #0891b2, #4f46e5); border: 2px solid #ffffff;">
                        ${shortName.charAt(0)}
                    </div>
                    <p class="text-xs font-mono-code font-bold" style="color: #fde047;">${shortName}</p>
                </div>
                {{-- BADGE NAMA SISWA: TEPAT DI POJOK KIRI ATAS - 100% AMAN DARI MENU BAR BAWAH --}}
                <div class="absolute top-4 left-4 z-20 flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-black shadow-md" style="background-color: #121316; color: #ffffff; border: 2px solid #121316; box-shadow: 3px 3px 0px #2563eb;" title="${rawName}">
                    <span class="w-2 h-2 rounded-full animate-pulse" style="background-color: #10b981;"></span>
                    <i id="tile-mic-${user.uid}" class="fa-solid fa-microphone text-xs" style="color: #10b981;"></i>
                    <span id="tile-name-${user.uid}" class="truncate max-w-[140px] font-mono-code font-bold" style="color: #fde047;">${shortName}</span>
                    <span class="px-2 py-0.5 rounded-md text-[9px] font-mono-code font-black uppercase tracking-wider" style="background-color: #2563eb; color: #ffffff;">SISWA</span>
                </div>
                <div class="absolute top-4 right-4 z-20 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button onclick="pinSpeaker('${user.uid}')" type="button" class="p-2 rounded-xl text-xs active:translate-x-0.5 active:translate-y-0.5 transition" style="background-color: #ffffff; color: #121316; border: 2px solid #121316; box-shadow: 2px 2px 0px #121316;" title="Fokuskan / Spotlight">
                        <i class="fa-solid fa-thumbtack text-xs"></i>
                    </button>
                </div>
            `;
            return tile;
        }

        // Susun ulang posisi tile video berdasarkan mode tata letak Zoom
        function rearrangeTiles() {
            const isClassroom = (currentLayout === 'classroom');
            const isSpeaker   = (currentLayout === 'speaker');
            const classroomStage = document.getElementById('stage-classroom-wrapper');
            const galleryStage   = document.getElementById('stage-gallery-wrapper');
            const speakerStage   = document.getElementById('stage-speaker-wrapper');
            const classroomGrid  = document.getElementById('classroom-grid');
            const teacherSlot    = document.getElementById('podium-teacher-slot');
            const galleryGrid    = document.getElementById('gallery-grid');
            const filmstrip      = document.getElementById('speaker-filmstrip');
            const mainStage      = document.getElementById('speaker-main-stage');
            const soloBanner     = document.getElementById('solo-teacher-banner');
            const occupancyPill  = document.getElementById('classroom-occupancy-pill');

            const allTiles = Array.from(document.querySelectorAll('.video-tile'));
            const teacherTile = document.getElementById('video-tile-local');
            const studentTiles = allTiles.filter(t => t.id !== 'video-tile-local');
            const total = allTiles.length;

            if (isClassroom) {
                if (galleryStage) galleryStage.classList.add('hidden');
                if (speakerStage) speakerStage.classList.add('hidden');
                if (classroomStage) {
                    classroomStage.classList.remove('hidden');
                    classroomStage.classList.add('flex');
                }

                if (soloBanner) soloBanner.classList.add('hidden');

                // 1. Tempatkan Video Guru di Mimbar / Podium Depan
                if (teacherTile && teacherSlot) {
                    teacherSlot.appendChild(teacherTile);
                    teacherTile.className = 'video-tile relative rounded-3xl overflow-hidden flex items-center justify-center w-full h-full max-h-[82vh] transition-all';
                    teacherTile.style.backgroundColor = '#0f172a';
                    teacherTile.style.border = '2.5px solid #121316';
                    teacherTile.style.boxShadow = '4px 4px 0px #ff3823';
                    teacherTile.classList.remove('filmstrip-tile', 'pinned-speaker');
                    teacherTile.onclick = null;
                }

                // 2. Update occupancy pill
                if (occupancyPill) {
                    occupancyPill.textContent = `${studentTiles.length} / 40 Kursi Terisi`;
                }

                // 3. Render 40 Bangku Siswa (5 Baris x 8 Kolom)
                if (classroomGrid) {
                    classroomGrid.innerHTML = '';
                    const totalSlots = Math.max(40, studentTiles.length);
                    for (let i = 0; i < totalSlots; i++) {
                        const seatNum = String(i + 1).padStart(2, '0');
                        if (i < studentTiles.length) {
                            // Kursi Terisi: Pasang student video tile
                            const sTile = studentTiles[i];
                            sTile.className = 'video-tile relative rounded-2xl overflow-hidden flex items-center justify-center w-full h-24 sm:h-28 aspect-video transition-all';
                            sTile.style.backgroundColor = '#0f172a';
                            sTile.style.border = '2px solid #121316';
                            sTile.style.boxShadow = '3px 3px 0px #121316';
                            sTile.classList.remove('filmstrip-tile', 'pinned-speaker');
                            sTile.onclick = null;

                            // Tambah atau perbarui tag nomor meja
                            let deskTag = sTile.querySelector('.seat-desk-tag');
                            const badge = sTile.querySelector('.absolute.top-4.left-4') || sTile.querySelector('.rounded-xl');
                            if (!deskTag && badge) {
                                deskTag = document.createElement('span');
                                deskTag.className = 'seat-desk-tag px-1.5 py-0.5 rounded text-[8px] font-mono-code font-black';
                                deskTag.style.backgroundColor = '#fbc02d';
                                deskTag.style.color = '#121316';
                                deskTag.style.border = '1px solid #121316';
                                badge.insertBefore(deskTag, badge.firstChild);
                            }
                            if (deskTag) {
                                deskTag.textContent = `#${seatNum}`;
                            }
                            classroomGrid.appendChild(sTile);
                        } else {
                            // Kursi Kosong (Tactile Blueprint Style)
                            const emptySlot = document.createElement('div');
                            emptySlot.className = 'h-24 sm:h-28 rounded-2xl flex flex-col items-center justify-center p-2 text-center transition-all bg-white/70 hover:bg-[#faf3e0] select-none';
                            emptySlot.style.border = '2px dashed #cbd5e1';
                            emptySlot.style.boxShadow = '2px 2px 0px rgba(0,0,0,0.03)';
                            emptySlot.innerHTML = `
                                <span class="text-[9px] font-mono-code font-bold mb-1" style="color: #64748b;">Meja #${seatNum}</span>
                                <i class="fa-solid fa-chair text-base sm:text-lg mb-1" style="color: #cbd5e1;"></i>
                                <span class="text-[8px] font-mono-code font-bold uppercase tracking-wider" style="color: #94a3b8;">[ Kosong ]</span>
                            `;
                            classroomGrid.appendChild(emptySlot);
                        }
                    }
                }
            } else if (isSpeaker) {
                if (classroomStage) classroomStage.classList.add('hidden');
                if (galleryStage)   galleryStage.classList.add('hidden');
                if (speakerStage) {
                    speakerStage.classList.remove('hidden');
                    speakerStage.classList.add('flex');
                }

                if (soloBanner) {
                    if (total <= 1) soloBanner.classList.remove('hidden');
                    else soloBanner.classList.add('hidden');
                }

                const pinnedId = (currentPinnedUid === 'local') ? 'video-tile-local' : `video-tile-${currentPinnedUid}`;
                let pinnedTile = document.getElementById(pinnedId);
                if (!pinnedTile && allTiles.length > 0) {
                    pinnedTile = allTiles[0];
                    currentPinnedUid = (pinnedTile.id === 'video-tile-local') ? 'local' : pinnedTile.id.replace('video-tile-', '');
                }

                if (pinnedTile && mainStage) {
                    mainStage.appendChild(pinnedTile);
                    pinnedTile.className = 'video-tile relative rounded-3xl overflow-hidden flex items-center justify-center w-full h-full max-h-[82vh] transition-all pinned-speaker';
                    pinnedTile.style.backgroundColor = '#0f172a';
                    pinnedTile.style.border = '3px solid #ff3823';
                    pinnedTile.style.boxShadow = '6px 6px 0px #121316';
                    pinnedTile.onclick = null;
                }

                allTiles.forEach(tile => {
                    if (tile !== pinnedTile && filmstrip) {
                        filmstrip.appendChild(tile);
                        tile.className = 'video-tile filmstrip-tile relative rounded-2xl overflow-hidden flex items-center justify-center aspect-video cursor-pointer transition-all hover:scale-105';
                        tile.style.backgroundColor = '#0f172a';
                        tile.style.border = '2px solid #121316';
                        tile.style.boxShadow = '2px 2px 0px #121316';
                        tile.classList.remove('pinned-speaker');
                        tile.onclick = () => pinSpeaker(tile.id === 'video-tile-local' ? 'local' : tile.id.replace('video-tile-', ''));
                    }
                });
            } else {
                // Mode Galeri atau Mode Berdampingan (KBM)
                if (classroomStage) classroomStage.classList.add('hidden');
                if (speakerStage) {
                    speakerStage.classList.add('hidden');
                    speakerStage.classList.remove('flex');
                }
                if (galleryStage) {
                    galleryStage.classList.remove('hidden');
                    galleryStage.classList.add('flex');
                }

                if (soloBanner) {
                    if (total <= 1) soloBanner.classList.remove('hidden');
                    else soloBanner.classList.add('hidden');
                }

                allTiles.forEach(tile => {
                    if (galleryGrid) galleryGrid.appendChild(tile);
                    tile.classList.remove('filmstrip-tile', 'pinned-speaker');
                    tile.onclick = null;
                });

                if (galleryGrid) {
                    if (total <= 1) {
                        galleryGrid.className = 'w-full h-full flex items-center justify-center p-1 sm:p-2';
                        if (allTiles[0]) {
                            allTiles[0].className = 'video-tile relative rounded-3xl overflow-hidden flex items-center justify-center w-full h-full max-h-[82vh] transition-all';
                            allTiles[0].style.backgroundColor = '#0f172a';
                            allTiles[0].style.border = '2.5px solid #121316';
                            allTiles[0].style.boxShadow = '5px 5px 0px #121316';
                        }
                    } else if (total === 2) {
                        galleryGrid.className = 'w-full h-full max-h-[82vh] grid grid-cols-1 md:grid-cols-2 gap-3.5 items-center justify-center p-2';
                        allTiles.forEach(t => {
                            t.className = 'video-tile relative rounded-3xl overflow-hidden flex items-center justify-center w-full h-full max-h-[75vh] aspect-video transition-all';
                            t.style.backgroundColor = '#0f172a';
                            t.style.border = '2px solid #121316';
                            t.style.boxShadow = '4px 4px 0px #121316';
                        });
                    } else if (total <= 4) {
                        galleryGrid.className = 'w-full h-full max-h-[82vh] grid grid-cols-2 grid-rows-2 gap-3 items-center justify-center p-2';
                        allTiles.forEach(t => {
                            t.className = 'video-tile relative rounded-2xl overflow-hidden flex items-center justify-center w-full h-full aspect-video transition-all';
                            t.style.backgroundColor = '#0f172a';
                            t.style.border = '2px solid #121316';
                            t.style.boxShadow = '3px 3px 0px #121316';
                        });
                    } else if (total <= 6) {
                        galleryGrid.className = 'w-full h-full max-h-[82vh] grid grid-cols-3 grid-rows-2 gap-2.5 items-center justify-center p-2';
                        allTiles.forEach(t => {
                            t.className = 'video-tile relative rounded-2xl overflow-hidden flex items-center justify-center w-full h-full aspect-video transition-all';
                            t.style.backgroundColor = '#0f172a';
                            t.style.border = '2px solid #121316';
                            t.style.boxShadow = '3px 3px 0px #121316';
                        });
                    } else {
                        galleryGrid.className = 'w-full h-full max-h-[82vh] grid grid-cols-3 md:grid-cols-4 gap-2.5 overflow-y-auto p-2';
                        allTiles.forEach(t => {
                            t.className = 'video-tile relative rounded-2xl overflow-hidden flex items-center justify-center w-full aspect-video transition-all';
                            t.style.backgroundColor = '#0f172a';
                            t.style.border = '2px solid #121316';
                            t.style.boxShadow = '3px 3px 0px #121316';
                        });
                    }
                }
            }

            // Pastikan video terus memutar lancar
            document.querySelectorAll('.video-tile video').forEach(v => {
                if (v && v.paused) v.play().catch(() => {});
            });
        }

        // ============================================================
        // 2. KONTROL MIC, KAMERA, SCREEN SHARE
        // ============================================================
        async function toggleMic() {
            if (!localAudioTrack) return;
            isMuted = !isMuted;
            await localAudioTrack.setMuted(isMuted);
            
            const micIcon = document.getElementById('mic-icon');
            const btn = document.getElementById('btn-toggle-mic');
            const tileMic = document.getElementById('tile-mic-local');

            if (isMuted) {
                micIcon.className = 'fa-solid fa-microphone-slash text-base text-white';
                btn.style.backgroundColor = '#ff3823';
                if (tileMic) {
                    tileMic.className = 'fa-solid fa-microphone-slash text-xs';
                    tileMic.style.color = '#ff3823';
                }
            } else {
                micIcon.className = 'fa-solid fa-microphone text-base text-white';
                btn.style.backgroundColor = '#10b981';
                if (tileMic) {
                    tileMic.className = 'fa-solid fa-microphone text-xs';
                    tileMic.style.color = '#10b981';
                }
            }
        }

        async function toggleCam() {
            if (!localVideoTrack) return;
            isCamOff = !isCamOff;
            await localVideoTrack.setMuted(isCamOff);
            
            const camIcon = document.getElementById('cam-icon');
            const btn = document.getElementById('btn-toggle-cam');
            const placeholder = document.getElementById('agora-local-placeholder');

            if (isCamOff) {
                camIcon.className = 'fa-solid fa-video-slash text-base text-white';
                btn.style.backgroundColor = '#ff3823';
                if (placeholder) placeholder.style.display = 'flex';
            } else {
                camIcon.className = 'fa-solid fa-video text-base text-white';
                btn.style.backgroundColor = '#0891b2';
                if (placeholder) placeholder.style.display = 'none';
            }
        }

        async function toggleScreenShare() {
            if (!isScreenSharing) {
                try {
                    localScreenTrack = await AgoraRTC.createScreenVideoTrack();
                    await agoraClient.unpublish(localVideoTrack);
                    await agoraClient.publish(localScreenTrack);
                    localScreenTrack.play('agora-local-video');
                    isScreenSharing = true;
                    document.getElementById('btn-screenshare').style.backgroundColor = '#10b981';
                    localScreenTrack.on('track-ended', async () => {
                        await stopScreenShare();
                    });
                } catch(e) {
                    console.warn('Screen share dibatalkan:', e.message);
                }
            } else {
                await stopScreenShare();
            }
        }

        async function stopScreenShare() {
            if (!localScreenTrack) return;
            await agoraClient.unpublish(localScreenTrack);
            localScreenTrack.stop();
            localScreenTrack.close();
            localScreenTrack = null;
            if (localVideoTrack) {
                await agoraClient.publish(localVideoTrack);
                localVideoTrack.play('agora-local-video');
            }
            isScreenSharing = false;
            document.getElementById('btn-screenshare').style.backgroundColor = '#2563eb';
        }

        // ============================================================
        // 3. UPDATE COUNTER PESERTA
        // ============================================================
        function updateParticipantCount() {
            if (!agoraClient) return;
            const count = agoraClient.remoteUsers.length;
            const headerEl = document.getElementById('header-attendee-count');
            const badgeEl  = document.getElementById('quick-attendee-badge');
            const tabCountEl = document.getElementById('attendee-tab-count');
            if (headerEl) headerEl.textContent = count;
            if (badgeEl)  badgeEl.textContent  = count;
            if (tabCountEl) tabCountEl.textContent = count;
        }

        // ============================================================
        // 4. KONTROL PEDAGOGIS GURU (MUTE ALL)
        // ============================================================
        function teacherMuteEveryone() {
            alert('Fitur Kondusifkan Kelas: Silakan sampaikan arahan kepada siswa untuk mematikan mikrofon masing-masing.');
        }

        // ============================================================
        // 5. ERROR HANDLER
        // ============================================================
        function showAgoraError(msg) {
            const el = document.getElementById('agora-local-video');
            if (el) {
                el.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full p-6 text-center" style="color: #cbd5e1;">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl mb-3 shadow-lg" style="background-color: #ff3823; color: #ffffff; border: 2px solid #ffffff;">
                            <i class="fa-solid fa-video-slash"></i>
                        </div>
                        <p class="text-sm font-black font-mono-code" style="color: #ffffff;">Kamera tidak dapat diakses</p>
                        <p class="text-xs mt-1 font-mono-code" style="color: #94a3b8;">${msg}</p>
                        <p class="text-xs mt-3 font-bold" style="color: #fde047;">Pastikan AGORA_APP_ID sudah diisi di .env dan browser diizinkan mengakses kamera/mikrofon.</p>
                    </div>
                `;
            }
        }

        // ============================================================
        // 6. LIVE TIMER KALKULASI
        // ============================================================
        let meetingStartTime = new Date("{{ $course->meeting_started_at ? $course->meeting_started_at->toISOString() : now()->toISOString() }}");

        function updateMeetingTimer() {
            const now = new Date();
            const diffMs = Math.max(0, now - meetingStartTime);
            const totalSec = Math.floor(diffMs / 1000);
            const h = String(Math.floor(totalSec / 3600)).padStart(2, '0');
            const m = String(Math.floor((totalSec % 3600) / 60)).padStart(2, '0');
            const s = String(totalSec % 60).padStart(2, '0');
            const timerEl = document.getElementById('class-timer');
            if (timerEl) timerEl.textContent = `${h}:${m}:${s}`;
        }
        setInterval(updateMeetingTimer, 1000);
        updateMeetingTimer();

        // ============================================================
        // 7. ZOOM VIEW MENU DROPDOWN & FULLSCREEN
        // ============================================================
        function toggleViewMenu(event) {
            if (event) event.stopPropagation();
            const menu = document.getElementById('view-dropdown-menu');
            if (menu) menu.classList.toggle('hidden');
        }

        document.addEventListener('click', function(e) {
            const dropdownWrapper = document.getElementById('video-view-dropdown-wrapper');
            const headerWrapper   = document.getElementById('header-view-wrapper');
            const menu            = document.getElementById('view-dropdown-menu');
            if (!menu) return;
            if (dropdownWrapper && !dropdownWrapper.contains(e.target) && headerWrapper && !headerWrapper.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });

        function changeLayout(mode) {
            currentLayout = mode;
            updateViewLabel(mode);

            const hub = document.getElementById('classroom-hub');
            const handler = document.getElementById('resize-handler');

            if (mode === 'sidebyside') {
                if (hub && hub.classList.contains('hidden')) {
                    hub.classList.remove('hidden');
                    hub.classList.add('flex');
                    if (handler) handler.classList.remove('hidden');
                }
            } else {
                if (hub && !hub.classList.contains('hidden')) {
                    hub.classList.add('hidden');
                    hub.classList.remove('flex');
                    if (handler) handler.classList.add('hidden');
                }
            }

            rearrangeTiles();
            const menu = document.getElementById('view-dropdown-menu');
            if (menu) menu.classList.add('hidden');
        }

        function updateViewLabel(mode) {
            const iconEl     = document.getElementById('current-view-icon');
            const nameEl     = document.getElementById('current-view-name');
            const headerIcon = document.getElementById('header-view-icon');
            const headerName = document.getElementById('header-view-name');
            const checkCls   = document.getElementById('check-classroom');
            const checkGal   = document.getElementById('check-gallery');
            const checkSpk   = document.getElementById('check-speaker');
            const checkSbs   = document.getElementById('check-sidebyside');

            if (checkCls) checkCls.classList.add('hidden');
            if (checkGal) checkGal.classList.add('hidden');
            if (checkSpk) checkSpk.classList.add('hidden');
            if (checkSbs) checkSbs.classList.add('hidden');

            if (mode === 'classroom') {
                if (iconEl) iconEl.className = 'fa-solid fa-chalkboard-user text-xs';
                if (nameEl) nameEl.textContent = 'Ruang Kelas (5x8)';
                if (headerIcon) headerIcon.className = 'fa-solid fa-chalkboard-user';
                if (headerName) headerName.textContent = 'Ruang Kelas (5x8)';
                if (checkCls) checkCls.classList.remove('hidden');
            } else if (mode === 'speaker') {
                if (iconEl) iconEl.className = 'fa-solid fa-user-tie text-xs';
                if (nameEl) nameEl.textContent = 'Pembicara';
                if (headerIcon) headerIcon.className = 'fa-solid fa-user-tie';
                if (headerName) headerName.textContent = 'Pembicara';
                if (checkSpk) checkSpk.classList.remove('hidden');
            } else if (mode === 'sidebyside') {
                if (iconEl) iconEl.className = 'fa-solid fa-columns text-xs';
                if (nameEl) nameEl.textContent = 'Berdampingan';
                if (headerIcon) headerIcon.className = 'fa-solid fa-columns';
                if (headerName) headerName.textContent = 'Berdampingan';
                if (checkSbs) checkSbs.classList.remove('hidden');
            } else {
                if (iconEl) iconEl.className = 'fa-solid fa-table-cells text-xs';
                if (nameEl) nameEl.textContent = 'Galeri';
                if (headerIcon) headerIcon.className = 'fa-solid fa-table-cells';
                if (headerName) headerName.textContent = 'Galeri';
                if (checkGal) checkGal.classList.remove('hidden');
            }
        }

        function toggleAppFullscreen() {
            const el = document.documentElement;
            const fsIcon = document.getElementById('menu-fs-icon');
            const fsText = document.getElementById('menu-fs-text');
            const isFs = document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement;

            if (!isFs) {
                if (el.requestFullscreen) {
                    el.requestFullscreen();
                } else if (el.webkitRequestFullscreen) {
                    el.webkitRequestFullscreen();
                }
                if (fsIcon) fsIcon.className = 'fa-solid fa-compress';
                if (fsText) fsText.textContent = 'Keluar Layar Penuh';
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
                if (fsIcon) fsIcon.className = 'fa-solid fa-expand';
                if (fsText) fsText.textContent = 'Layar Penuh (F11)';
            }
            const menu = document.getElementById('view-dropdown-menu');
            if (menu) menu.classList.add('hidden');
        }

        function pinSpeaker(targetUid) {
            currentPinnedUid = targetUid;
            if (currentLayout !== 'speaker') {
                changeLayout('speaker');
            } else {
                rearrangeTiles();
            }
        }

        // ============================================================
        // 8. CLASSROOM MULTI-HUB: SWITCH TAB
        // ============================================================
        function toggleClassroomHub() {
            const hub = document.getElementById('classroom-hub');
            const handler = document.getElementById('resize-handler');
            if (hub.classList.contains('hidden')) {
                hub.classList.remove('hidden');
                hub.classList.add('flex');
                if (handler) handler.classList.remove('hidden');
                if (currentLayout === 'gallery') {
                    currentLayout = 'sidebyside';
                    updateViewLabel('sidebyside');
                }
            } else {
                hub.classList.add('hidden');
                hub.classList.remove('flex');
                if (handler) handler.classList.add('hidden');
                if (currentLayout === 'sidebyside') {
                    currentLayout = 'gallery';
                    updateViewLabel('gallery');
                }
            }
            rearrangeTiles();
        }

        function switchTab(tabName) {
            const hub = document.getElementById('classroom-hub');
            const handler = document.getElementById('resize-handler');
            if (hub.classList.contains('hidden')) {
                hub.classList.remove('hidden');
                hub.classList.add('flex');
                if (handler) handler.classList.remove('hidden');
            }

            const tabs = ['materi', 'whiteboard', 'peserta'];
            tabs.forEach(t => {
                const btn = document.getElementById('tab-btn-' + t);
                const content = document.getElementById('tab-content-' + t);
                if (t === tabName) {
                    btn.style.backgroundColor = '#121316';
                    btn.style.color = '#fde047';
                    btn.style.border = '2px solid #121316';
                    btn.style.boxShadow = '2px 2px 0px #ff3823';
                    content.classList.remove('hidden');
                } else {
                    btn.style.backgroundColor = '#ffffff';
                    btn.style.color = '#121316';
                    btn.style.border = '2px solid #121316';
                    btn.style.boxShadow = 'none';
                    content.classList.add('hidden');
                }
            });

            if (tabName === 'whiteboard') {
                setTimeout(resizeCanvas, 100);
            }
        }

        function openTabDirect(tabName) {
            switchTab(tabName);
        }

        // ============================================================
        // 9. PAPAN TULIS DIGITAL (WHITEBOARD CANVAS)
        // ============================================================
        const canvas = document.getElementById('whiteboard-canvas');
        const ctx = canvas ? canvas.getContext('2d') : null;
        let isDrawing = false;
        let currentTool = 'pen';
        let currentColor = '#121316';
        let currentSize = 4;
        let drawHistory = [];

        function resizeCanvas() {
            if (!canvas || !canvas.parentElement) return;
            const rect = canvas.parentElement.getBoundingClientRect();
            if (rect.width === 0 || rect.height === 0) return;
            
            const imgData = ctx ? ctx.getImageData(0, 0, canvas.width, canvas.height) : null;
            canvas.width = rect.width;
            canvas.height = rect.height;
            if (imgData && ctx) ctx.putImageData(imgData, 0, 0);
        }

        if (canvas) {
            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseleave', stopDrawing);

            canvas.addEventListener('touchstart', (e) => {
                const touch = e.touches[0];
                const mouseEvent = new MouseEvent('mousedown', { clientX: touch.clientX, clientY: touch.clientY });
                canvas.dispatchEvent(mouseEvent);
            }, { passive: false });
            canvas.addEventListener('touchmove', (e) => {
                const touch = e.touches[0];
                const mouseEvent = new MouseEvent('mousemove', { clientX: touch.clientX, clientY: touch.clientY });
                canvas.dispatchEvent(mouseEvent);
            }, { passive: false });
            canvas.addEventListener('touchend', () => {
                canvas.dispatchEvent(new MouseEvent('mouseup', {}));
            });
        }

        function startDrawing(e) {
            isDrawing = true;
            saveHistory();
            ctx.beginPath();
            ctx.moveTo(e.offsetX, e.offsetY);
        }

        function draw(e) {
            if (!isDrawing) return;
            ctx.lineWidth = currentSize;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';

            if (currentTool === 'eraser') {
                ctx.globalCompositeOperation = 'destination-out';
                ctx.lineWidth = currentSize * 4;
            } else {
                ctx.globalCompositeOperation = 'source-over';
                ctx.strokeStyle = currentColor;
            }

            ctx.lineTo(e.offsetX, e.offsetY);
            ctx.stroke();
        }

        function stopDrawing() {
            if (!isDrawing) return;
            isDrawing = false;
            ctx.closePath();
        }

        function setWhiteboardTool(tool) {
            currentTool = tool;
            const btnPen = document.getElementById('tool-pen');
            const btnEraser = document.getElementById('tool-eraser');
            if (tool === 'pen') {
                btnPen.style.backgroundColor = '#4f46e5';
                btnPen.style.color = '#ffffff';
                btnEraser.style.backgroundColor = '#ffffff';
                btnEraser.style.color = '#121316';
            } else {
                btnEraser.style.backgroundColor = '#ff3823';
                btnEraser.style.color = '#ffffff';
                btnPen.style.backgroundColor = '#ffffff';
                btnPen.style.color = '#121316';
            }
        }

        function setPenColor(color) {
            currentColor = color;
            setWhiteboardTool('pen');
        }

        function setPenSize(sz) {
            currentSize = parseInt(sz);
        }

        function setCanvasBackground(bg) {
            const container = document.getElementById('whiteboard-container');
            if (!container) return;
            container.className = `flex-1 relative overflow-hidden cursor-crosshair m-2.5 rounded-2xl canvas-${bg}`;
        }

        function saveHistory() {
            if (!ctx) return;
            if (drawHistory.length > 20) drawHistory.shift();
            drawHistory.push(ctx.getImageData(0, 0, canvas.width, canvas.height));
        }

        function undoWhiteboard() {
            if (drawHistory.length > 0 && ctx) {
                const prev = drawHistory.pop();
                ctx.putImageData(prev, 0, 0);
            }
        }

        function clearWhiteboard() {
            if (!ctx) return;
            saveHistory();
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        function downloadWhiteboard() {
            if (!canvas) return;
            const a = document.createElement('a');
            a.download = `whiteboard-${COURSE_ID}-${Date.now()}.png`;
            a.href = canvas.toDataURL('image/png');
            a.click();
        }

        // ============================================================
        // 10. DISPLAY MATERI PEMBELAJARAN
        // ============================================================
        function displayMaterial(title, type, url, content) {
            const box = document.getElementById('material-preview-box');
            const iframe = document.getElementById('preview-iframe');
            const textEl = document.getElementById('preview-text');
            const titleEl = document.getElementById('preview-title');
            const iconEl = document.getElementById('preview-icon');
            const extLink = document.getElementById('preview-external-link');

            titleEl.textContent = title;
            switchTab('materi');

            if (type === 'pdf') {
                iconEl.className = 'fa-regular fa-file-pdf';
            } else if (type === 'video') {
                iconEl.className = 'fa-regular fa-play-circle';
            } else {
                iconEl.className = 'fa-regular fa-file-lines';
            }

            iframe.classList.add('hidden');
            textEl.classList.add('hidden');

            if (type === 'text') {
                textEl.innerHTML = content;
                textEl.classList.remove('hidden');
                extLink.classList.add('hidden');
            } else {
                iframe.src = url;
                iframe.classList.remove('hidden');
                extLink.href = url;
                extLink.classList.remove('hidden');
            }

            box.classList.remove('hidden');
        }

        function closeMaterialPreview() {
            const box = document.getElementById('material-preview-box');
            const iframe = document.getElementById('preview-iframe');
            if (box) box.classList.add('hidden');
            if (iframe) iframe.src = 'about:blank';
        }

        // ============================================================
        // 11. POLLING PRESENSI REALTIME
        // ============================================================
        function fetchAttendees() {
            fetch("{{ route('guru.lms.meeting.attendees', $course->id) }}")
                .then(r => r.json())
                .then(data => {
                    const total = data.total || 0;
                    document.getElementById('header-attendee-count').textContent = total;
                    document.getElementById('attendee-tab-count').textContent = total;
                    document.getElementById('quick-attendee-badge').textContent = total;

                    const listEl = document.getElementById('attendees-list');
                    if (!listEl) return;

                    if (total === 0) {
                        listEl.innerHTML = `
                            <div class="text-center py-10 px-4 poster-card bg-white p-6" style="color: #777;">
                                <div class="w-14 h-14 mx-auto mb-3 rounded-2xl flex items-center justify-center text-2xl shadow-sm" style="background-color: #ede9df; border: 2px solid #121316; color: #ff3823;">
                                    <i class="fa-solid fa-user-clock"></i>
                                </div>
                                <h4 class="text-xs font-mono-code font-black uppercase" style="color: #121316;">Belum Ada Siswa Hadir</h4>
                                <p class="text-[11px] mt-1.5 leading-relaxed font-medium" style="color: #555;">Bagikan tautan sesi ini kepada siswa agar mereka segera masuk ke ruang PembdaTEMU.</p>
                                <div class="mt-4">
                                    <button onclick="copyMeetingLink()" type="button" class="btn-tactile-yellow text-xs font-mono-code font-black px-4 py-2 rounded-xl inline-flex items-center gap-2">
                                        <i class="fa-solid fa-copy"></i>
                                        <span>Salin Tautan WhatsApp</span>
                                    </button>
                                </div>
                            </div>
                        `;
                        return;
                    }

                    let html = '';
                    const avatarGradients = [
                        'linear-gradient(135deg, #ec4899, #f43f5e)',
                        'linear-gradient(135deg, #06b6d4, #3b82f6)',
                        'linear-gradient(135deg, #10b981, #14b8a6)',
                        'linear-gradient(135deg, #8b5cf6, #a855f7)',
                        'linear-gradient(135deg, #f59e0b, #f97316)'
                    ];

                    data.attendees.forEach((student, idx) => {
                        const grad = avatarGradients[idx % avatarGradients.length];
                        html += `
                            <div class="flex items-center justify-between p-3 rounded-2xl bg-white text-xs transition-all" style="border: 2px solid #121316; box-shadow: 3px 3px 0px #121316;">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-black text-white flex-shrink-0" style="background: ${grad}; border: 2px solid #121316;">
                                        ${student.initials}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-black truncate leading-snug" style="color: #121316;">${student.name}</p>
                                        <span class="text-[10px] font-mono-code font-bold flex items-center gap-1" style="color: #555;">
                                            <i class="fa-regular fa-clock" style="color: #2563eb;"></i> Masuk: ${student.joined_at}
                                        </span>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-mono-code font-black" style="background-color: #10b981; color: #ffffff; border: 1.5px solid #121316;">
                                    <span class="w-1.5 h-1.5 rounded-full animate-ping" style="background-color: #ffffff;"></span> HADIR
                                </span>
                            </div>
                        `;
                    });
                    listEl.innerHTML = html;

                    if (data.attendees) {
                        data.attendees.forEach(student => {
                            const sid = student.user_id || student.id;
                            if (sid) {
                                attendeesMap[sid] = student.name;
                                const nameBadge = document.getElementById(`tile-name-${sid}`);
                                if (nameBadge) {
                                    nameBadge.textContent = student.name.split(' ')[0];
                                }
                            }
                        });
                    }
                })
                .catch(err => console.error("Error fetching attendees:", err));
        }

        // ============================================================
        // 12. COPY MEETING LINK & TOAST
        // ============================================================
        function copyMeetingLink() {
            const link = "{{ route('siswa.lms.meeting.join', $course->id) }}";
            navigator.clipboard.writeText(link).then(() => {
                const toast = document.getElementById('copy-toast');
                toast.classList.remove('translate-y-12', 'opacity-0');
                toast.classList.add('translate-y-0', 'opacity-100');
                setTimeout(() => {
                    toast.classList.remove('translate-y-0', 'opacity-100');
                    toast.classList.add('translate-y-12', 'opacity-0');
                }, 3500);
            }).catch(() => {
                alert("Link PembdaTEMU: " + link);
            });
        }

        // ============================================================
        // 13. END MEETING MODAL
        // ============================================================
        function openEndMeetingModal() {
            document.getElementById('end-meeting-modal').classList.remove('hidden');
        }
        function closeEndMeetingModal() {
            document.getElementById('end-meeting-modal').classList.add('hidden');
        }

        // ============================================================
        // 14. DRAG RESIZE PANEL
        // ============================================================
        const hubPanel = document.getElementById('classroom-hub');
        const resizeHandle = document.getElementById('resize-handler');
        const resizeOverlay = document.getElementById('resize-overlay');
        let isResizing = false;

        if (resizeHandle) {
            resizeHandle.addEventListener('mousedown', function(e) {
                isResizing = true;
                resizeOverlay.classList.remove('hidden');
                document.body.style.cursor = 'col-resize';
            });
        }

        document.addEventListener('mousemove', function(e) {
            if (!isResizing) return;
            const newWidth = window.innerWidth - e.clientX;
            if (newWidth >= 320 && newWidth <= window.innerWidth * 0.70) {
                hubPanel.style.width = newWidth + 'px';
            }
        });

        document.addEventListener('mouseup', function() {
            if (isResizing) {
                isResizing = false;
                resizeOverlay.classList.add('hidden');
                document.body.style.cursor = 'default';
                resizeCanvas();
            }
        });

        // ============================================================
        // 15. CLEANUP & INIT SAAT LOAD
        // ============================================================
        document.addEventListener('DOMContentLoaded', () => {
            initAgora().catch(err => {
                console.error('Agora init error:', err);
                showAgoraError(err.message || 'Kamera atau mikrofon tidak dapat dibuka');
            });

            setInterval(fetchAttendees, 15000);
            fetchAttendees();
        });

        window.addEventListener('beforeunload', async () => {
            if (localAudioTrack) { localAudioTrack.stop(); localAudioTrack.close(); }
            if (localVideoTrack) { localVideoTrack.stop(); localVideoTrack.close(); }
            if (localScreenTrack) { localScreenTrack.stop(); localScreenTrack.close(); }
            if (agoraClient) await agoraClient.leave();
        });
    </script>
</body>
</html>
