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
            <div class="w-px h-4" style="background-color: rgba(255,255,255,0.3);"></div>
            <div class="flex items-center gap-1.5 text-xs font-mono-code font-black text-amber-300" title="Reward Reputasi: +50 Poin Guru Host KBM (min. 5 menit & ada siswa hadir)">
                <i class="fa-solid fa-award text-amber-400"></i>
                <span>+50 Pts</span>
            </div>
        </div>

        {{-- Right Controls (Compact Icon-Only Tactile Buttons with Hover Tooltips) --}}
        <div class="flex items-center gap-1.5 sm:gap-2 flex-shrink-0">
            
            {{-- 1. Tombol Rekam Video KBM (Record Session) --}}
            <button id="header-btn-record" onclick="toggleMeetingRecording()" type="button" class="group relative w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center transition active:scale-95 border-2 border-black" style="background-color: #ffffff; color: #ff3823; box-shadow: 2px 2px 0px #121316;" title="Rekam Pembelajaran KBM (Record)">
                <span id="header-record-dot" class="w-3.5 h-3.5 rounded-full bg-red-600 border border-black flex items-center justify-center">
                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                </span>
                <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">
                    <span id="header-record-tooltip">Rekam KBM</span>
                </span>
            </button>

            {{-- 2. Tombol Salin Tautan --}}
            <button onclick="copyMeetingLink()" type="button" class="group relative w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center transition active:scale-95 border-2 border-black" style="background-color: #0284c7; color: #ffffff; box-shadow: 2px 2px 0px #121316;" title="Salin tautan kelas untuk siswa">
                <i class="fa-solid fa-link text-sm"></i>
                <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">
                    Salin Tautan
                </span>
            </button>

            {{-- 3. Tombol Pilihan Tampilan Layout (Tata Letak) --}}
            <div class="relative inline-block text-left" id="header-view-wrapper">
                <button onclick="toggleViewMenu(event)" type="button" class="group relative w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center transition active:scale-95 border-2 border-black" style="background-color: #f59e0b; color: #121316; box-shadow: 2px 2px 0px #121316;" title="Pilihan Tata Letak Tampilan Video">
                    <i id="header-view-icon" class="fa-solid fa-table-cells-large text-sm text-black"></i>
                    <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">
                        Tata Letak: <span id="header-view-name">Ruang Kelas (5x8)</span>
                    </span>
                </button>
            </div>

            {{-- 4. Tombol Toggle Panel Materi / Peserta --}}
            <button onclick="toggleClassroomHub()" type="button" id="btn-toggle-hub" class="group relative w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center transition active:scale-95 border-2 border-black" style="background-color: #4f46e5; color: #ffffff; box-shadow: 2px 2px 0px #121316;" title="Buka / Sembunyikan Panel KBM">
                <i class="fa-solid fa-table-columns text-sm"></i>
                <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">
                    Panel KBM
                </span>
            </button>

            {{-- 5. Profile Chip Guru --}}
            <div class="group relative w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center border-2 border-black bg-white select-none" style="box-shadow: 2px 2px 0px #121316;" title="{{ $displayName }}">
                <div class="w-6 h-6 rounded-lg flex items-center justify-center text-[11px] font-black uppercase text-white shadow-xs" style="background-color: #ff3823; border: 1px solid #121316;">
                    {{ substr($displayName, 0, 1) }}
                </div>
                <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">
                    {{ $displayName }}
                </span>
            </div>

            {{-- 6. Tombol Akhiri Kelas --}}
            <button onclick="openEndMeetingModal()" type="button" class="group relative w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center transition active:scale-95 border-2 border-black" style="background-color: #ff3823; color: #ffffff; box-shadow: 2px 2px 0px #121316;" title="Akhiri Sesi PembdaTEMU">
                <i class="fa-solid fa-power-off text-sm"></i>
                <span class="absolute -bottom-8 right-0 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #ff3823; border: 1.5px solid #000000;">
                    Akhiri Kelas
                </span>
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
                
                {{-- AREA MIMBAR DEPAN KELAS: DUAL PODIUM (GURU + SISWA BICARA / BERTANYA) --}}
                <div class="w-full flex flex-col items-center justify-center flex-shrink-0 px-2 sm:px-4">
                    <div class="flex items-center gap-2 mb-2 flex-wrap justify-center">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-mono-code font-black uppercase tracking-wider" style="background-color: #ff3823; color: #ffffff; border: 1.5px solid #121316; box-shadow: 2px 2px 0px #121316;">
                            <span class="w-2 h-2 rounded-full animate-ping" style="background-color: #fde047;"></span> ✱ MIMBAR DEPAN KELAS
                        </span>
                        <span id="classroom-occupancy-pill" class="px-2.5 py-1 rounded-full text-[10px] font-mono-code font-extrabold" style="background-color: #fbc02d; color: #121316; border: 1.5px solid #121316; box-shadow: 2px 2px 0px #121316;">
                            0 / 40 Kursi Terisi
                        </span>
                        <span id="hand-raise-alert-pill" class="hidden items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-mono-code font-black animate-bounce" style="background-color: #ef4444; color: #ffffff; border: 1.5px solid #121316; box-shadow: 2px 2px 0px #121316;">
                            <i class="fa-solid fa-hand"></i> <span id="hand-raise-count-text">0 Ingin Bertanya</span>
                        </span>
                    </div>

                    {{-- DYNAMIC PODIUM: DEFAULT GURU DI TENGAH DEPAN; JIKA ADA SISWA BICARA/TANYA BERUBAH MENJADI 2 KOLOM --}}
                    <div id="podium-container" class="w-full max-w-xl mx-auto transition-all duration-300">
                        <div id="podium-grid" class="w-full flex items-center justify-center transition-all duration-300">
                            
                            {{-- PODIUM 1: GURU PENGAJAR --}}
                            <div id="podium-teacher-wrapper" class="flex flex-col items-center w-full transition-all duration-300">
                                <div class="w-full flex items-center justify-between px-3 py-1 mb-1 rounded-xl bg-white text-[10px] font-mono-code font-black" style="border: 1.5px solid #121316; box-shadow: 2px 2px 0px #ff3823;">
                                    <span class="flex items-center gap-1.5 text-[#ff3823]">
                                        <i class="fa-solid fa-chalkboard-user"></i> PODIUM GURU
                                    </span>
                                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-[#ff3823] text-white">PENGAJAR</span>
                                </div>
                                <div id="podium-teacher-slot" class="w-full h-44 sm:h-52 md:h-56 flex items-center justify-center">
                                    {{-- Video tile guru masuk ke sini secara otomatis --}}
                                </div>
                            </div>

                            {{-- PODIUM 2: SISWA BICARA / BERTANYA (ACTIVE SPEAKER & TANYA) - MUNCUL HANYA SAAT ADA SISWA BICARA/BERTANYA --}}
                            <div id="podium-speaker-wrapper" class="hidden flex-col items-center w-full transition-all duration-300">
                                <div class="w-full flex items-center justify-between px-3 py-1 mb-1 rounded-xl bg-white text-[10px] font-mono-code font-black" style="border: 1.5px solid #121316; box-shadow: 2px 2px 0px #2563eb;">
                                    <span class="flex items-center gap-1.5 text-[#2563eb]">
                                        <i class="fa-solid fa-microphone-lines"></i> <span id="podium-speaker-label">MIMBAR SISWA BICARA & TANYA</span>
                                    </span>
                                    <span id="podium-speaker-badge" class="text-[9px] px-1.5 py-0.2 rounded bg-[#2563eb] text-white">BICARA</span>
                                </div>
                                <div id="podium-speaker-slot" class="w-full h-44 sm:h-52 md:h-56 flex items-center justify-center relative">
                                    {{-- Video tile siswa masuk ke sini saat berbicara --}}
                                </div>
                            </div>

                        </div>
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
                        <div id="classroom-grid" class="grid grid-cols-4 sm:grid-cols-6 lg:grid-cols-8 gap-2 sm:gap-2.5 pb-24">
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

            {{-- Floating Teacher Action Dock (Compact Icon-Only Tactile Dock with Hover Tooltips) --}}
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 rounded-2xl sm:rounded-full bg-white border-2 border-black" style="box-shadow: 4px 4px 0px #121316;">
                
                {{-- 1. Toggle Mic --}}
                <button id="btn-toggle-mic" onclick="toggleMic()" type="button" class="group relative w-10 h-10 rounded-xl sm:rounded-full flex items-center justify-center transition active:scale-95 border-2 border-black" style="background-color: #10b981; color: #ffffff; box-shadow: 2px 2px 0px #121316;" title="Nyalakan/Matikan Mikrofon (M)">
                    <i id="mic-icon" class="fa-solid fa-microphone text-sm text-white"></i>
                    <span class="absolute -top-9 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">Mikrofon (M)</span>
                </button>

                {{-- 2. Toggle Camera --}}
                <button id="btn-toggle-cam" onclick="toggleCam()" type="button" class="group relative w-10 h-10 rounded-xl sm:rounded-full flex items-center justify-center transition active:scale-95 border-2 border-black" style="background-color: #0891b2; color: #ffffff; box-shadow: 2px 2px 0px #121316;" title="Nyalakan/Matikan Kamera (V)">
                    <i id="cam-icon" class="fa-solid fa-video text-sm text-white"></i>
                    <span class="absolute -top-9 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">Kamera (V)</span>
                </button>

                {{-- 3. Screen Share --}}
                <button onclick="toggleScreenShare()" id="btn-screenshare" type="button" class="group relative w-10 h-10 rounded-xl sm:rounded-full flex items-center justify-center transition active:scale-95 border-2 border-black" style="background-color: #2563eb; color: #ffffff; box-shadow: 2px 2px 0px #121316;" title="Bagikan Layar Anda">
                    <i class="fa-solid fa-desktop text-sm text-white"></i>
                    <span class="absolute -top-9 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">Bagi Layar</span>
                </button>

                {{-- 4. Rekam Video KBM (Record) --}}
                <button id="btn-record-session" onclick="toggleMeetingRecording()" type="button" class="group relative w-10 h-10 rounded-xl sm:rounded-full flex items-center justify-center transition active:scale-95 border-2 border-black" style="background-color: #ffffff; color: #ff3823; box-shadow: 2px 2px 0px #121316;" title="Rekam Video KBM ke Materi LMS">
                    <span id="bottom-record-indicator" class="w-3.5 h-3.5 rounded-full bg-red-600 border border-black flex items-center justify-center">
                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                    </span>
                    <span class="absolute -top-9 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;"><span id="bottom-record-tooltip">Rekam KBM</span></span>
                </button>

                <div class="w-px h-6 bg-slate-300"></div>

                {{-- 5. Mute Semua Siswa --}}
                <button onclick="teacherMuteEveryone()" type="button" class="group relative w-10 h-10 rounded-xl sm:rounded-full flex items-center justify-center transition active:scale-95 border-2 border-black" style="background-color: #fde047; color: #121316; box-shadow: 2px 2px 0px #121316;" title="Kondisikan Kelas: Minta seluruh siswa mematikan mic">
                    <i class="fa-solid fa-volume-xmark text-sm"></i>
                    <span class="absolute -top-9 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">Mute Semua Siswa</span>
                </button>

                {{-- 6. Modul / Materi --}}
                <button onclick="openTabDirect('materi')" type="button" class="group relative w-10 h-10 rounded-xl sm:rounded-full flex items-center justify-center transition active:scale-95 border-2 border-black" style="background-color: #4f46e5; color: #ffffff; box-shadow: 2px 2px 0px #121316;" title="Buka Silabus Materi &amp; Modul">
                    <i class="fa-solid fa-book-open text-sm" style="color: #fde047;"></i>
                    <span class="absolute -top-9 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">Silabus &amp; Modul</span>
                </button>

                {{-- 7. Papan Tulis (Whiteboard) --}}
                <button onclick="openTabDirect('whiteboard')" type="button" class="group relative w-10 h-10 rounded-xl sm:rounded-full flex items-center justify-center transition active:scale-95 border-2 border-black" style="background-color: #7c3aed; color: #ffffff; box-shadow: 2px 2px 0px #121316;" title="Buka Papan Tulis Digital (Whiteboard)">
                    <i class="fa-solid fa-chalkboard text-sm" style="color: #fde047;"></i>
                    <span class="absolute -top-9 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">Papan Tulis Digital</span>
                </button>

                {{-- 8. Presensi Siswa --}}
                <button onclick="openTabDirect('peserta')" type="button" class="group relative w-10 h-10 rounded-xl sm:rounded-full flex items-center justify-center transition active:scale-95 border-2 border-black" style="background-color: #ffffff; color: #121316; box-shadow: 2px 2px 0px #121316;" title="Lihat Presensi Siswa Realtime">
                    <i class="fa-solid fa-users text-sm text-emerald-600"></i>
                    <span id="quick-attendee-badge" class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full text-[9px] font-mono-code font-black" style="background-color: #10b981; color: #ffffff; border: 1px solid #121316;">0</span>
                    <span class="absolute -top-9 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">Presensi Siswa</span>
                </button>

                {{-- 9. Chat Kelas --}}
                <button onclick="openTabDirect('chat')" type="button" class="group relative w-10 h-10 rounded-xl sm:rounded-full flex items-center justify-center transition active:scale-95 border-2 border-black" style="background-color: #2563eb; color: #ffffff; box-shadow: 2px 2px 0px #121316;" title="Buka Obrolan Kelas &amp; Tanya Jawab">
                    <i class="fa-solid fa-comments text-sm" style="color: #fde047;"></i>
                    <span id="quick-chat-badge" class="hidden absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full text-[9px] font-mono-code font-black" style="background-color: #ff3823; color: #ffffff; border: 1px solid #121316;">0</span>
                    <span class="absolute -top-9 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">Obrolan Kelas</span>
                </button>

                {{-- 10. Polling Kilat --}}
                <button onclick="openTabDirect('polling')" type="button" class="group relative w-10 h-10 rounded-xl sm:rounded-full flex items-center justify-center transition active:scale-95 border-2 border-black" style="background-color: #f59e0b; color: #121316; box-shadow: 2px 2px 0px #121316;" title="Buka Polling Kilat KBM">
                    <i class="fa-solid fa-square-poll-vertical text-sm text-black"></i>
                    <span class="absolute -top-9 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">Polling Kilat</span>
                </button>

                {{-- 11. Q&A Antrean --}}
                <button onclick="openTabDirect('qa')" type="button" class="group relative w-10 h-10 rounded-xl sm:rounded-full flex items-center justify-center transition active:scale-95 border-2 border-black" style="background-color: #ea580c; color: #ffffff; box-shadow: 2px 2px 0px #121316;" title="Buka Antrean Q&amp;A Siswa">
                    <i class="fa-solid fa-circle-question text-sm text-white"></i>
                    <span class="absolute -top-9 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">Antrean Q&amp;A</span>
                </button>

                {{-- 12. Kuis Live --}}
                <button onclick="openTabDirect('kuis')" type="button" class="group relative w-10 h-10 rounded-xl sm:rounded-full flex items-center justify-center transition active:scale-95 border-2 border-black" style="background-color: #ec4899; color: #ffffff; box-shadow: 2px 2px 0px #121316;" title="Luncurkan Kuis Live Interaktif (Kahoot-style)">
                    <i class="fa-solid fa-gamepad text-sm" style="color: #fde047;"></i>
                    <span class="absolute -top-9 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">Kuis Interaktif Live</span>
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
            
            {{-- Tabs Header Bar (Tactile Binder Style - Compact Icons with Tooltips) --}}
            <div class="flex items-center p-2 gap-1 flex-shrink-0" style="background-color: #ede9df; border-bottom: 2px solid #121316;">
                {{-- Tab Modul --}}
                <button id="tab-btn-materi" onclick="switchTab('materi')" type="button" class="group relative flex-1 py-2 px-1 text-xs font-mono-code font-black transition-all flex items-center justify-center rounded-xl" style="background-color: #121316; color: #fde047; border: 2px solid #121316; box-shadow: 2px 2px 0px #ff3823;" title="Silabus Materi &amp; Modul">
                    <i class="fa-solid fa-book-open text-xs" style="color: #ff3823;"></i>
                    <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">Modul Materi</span>
                </button>

                {{-- Tab Papan Tulis --}}
                <button id="tab-btn-whiteboard" onclick="switchTab('whiteboard')" type="button" class="group relative flex-1 py-2 px-1 text-xs font-mono-code font-bold transition-all flex items-center justify-center rounded-xl" style="background-color: #ffffff; color: #121316; border: 2px solid #121316;" title="Papan Tulis Digital (Whiteboard)">
                    <i class="fa-solid fa-chalkboard text-xs" style="color: #7c3aed;"></i>
                    <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">Papan Tulis</span>
                </button>

                {{-- Tab Siswa --}}
                <button id="tab-btn-peserta" onclick="switchTab('peserta')" type="button" class="group relative flex-1 py-2 px-1 text-xs font-mono-code font-bold transition-all flex items-center justify-center gap-1 rounded-xl" style="background-color: #ffffff; color: #121316; border: 2px solid #121316;" title="Daftar Siswa Hadir">
                    <i class="fa-solid fa-users text-xs" style="color: #10b981;"></i>
                    <span id="attendee-tab-count" class="text-[10px] font-black text-emerald-700">0</span>
                    <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">Presensi Siswa</span>
                </button>

                {{-- Tab Chat --}}
                <button id="tab-btn-chat" onclick="switchTab('chat')" type="button" class="group relative flex-1 py-2 px-1 text-xs font-mono-code font-bold transition-all flex items-center justify-center rounded-xl" style="background-color: #ffffff; color: #121316; border: 2px solid #121316;" title="Obrolan Kelas &amp; DM">
                    <i class="fa-solid fa-comments text-xs" style="color: #2563eb;"></i>
                    <span id="chat-tab-badge" class="hidden absolute -top-1 -right-1 px-1.5 py-0.2 rounded-full text-[9px] font-black" style="background-color: #ff3823; color: #ffffff;">0</span>
                    <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">Obrolan Chat</span>
                </button>

                {{-- Tab Polling --}}
                <button id="tab-btn-polling" onclick="switchTab('polling')" type="button" class="group relative flex-1 py-2 px-1 text-xs font-mono-code font-bold transition-all flex items-center justify-center rounded-xl" style="background-color: #ffffff; color: #121316; border: 2px solid #121316;" title="Polling Kilat KBM">
                    <i class="fa-solid fa-square-poll-vertical text-xs" style="color: #d97706;"></i>
                    <span id="poll-tab-badge" class="hidden absolute -top-1 -right-1 w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                    <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">Polling Kilat</span>
                </button>

                {{-- Tab Q&A --}}
                <button id="tab-btn-qa" onclick="switchTab('qa')" type="button" class="group relative flex-1 py-2 px-1 text-xs font-mono-code font-bold transition-all flex items-center justify-center rounded-xl" style="background-color: #ffffff; color: #121316; border: 2px solid #121316;" title="Antrean Tanya Jawab (Q&amp;A)">
                    <i class="fa-solid fa-circle-question text-xs" style="color: #ea580c;"></i>
                    <span id="qa-tab-badge" class="hidden absolute -top-1 -right-1 px-1.5 py-0.2 rounded-full text-[9px] font-black" style="background-color: #ea580c; color: #ffffff;">0</span>
                    <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">Tanya Jawab (Q&amp;A)</span>
                </button>

                {{-- Tab Kuis --}}
                <button id="tab-btn-kuis" onclick="switchTab('kuis')" type="button" class="group relative flex-1 py-2 px-1 text-xs font-mono-code font-bold transition-all flex items-center justify-center rounded-xl" style="background-color: #ffffff; color: #121316; border: 2px solid #121316;" title="Kuis Interaktif Live (Kahoot)">
                    <i class="fa-solid fa-gamepad text-xs" style="color: #ec4899;"></i>
                    <span id="quiz-tab-badge" class="hidden absolute -top-1 -right-1 px-1 py-0.2 rounded text-[8px] font-black" style="background-color: #ec4899; color: #ffffff;">LIVE</span>
                    <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #fde047; border: 1.5px solid #000000;">Kuis Live</span>
                </button>

                {{-- Tutup Panel --}}
                <button onclick="toggleClassroomHub()" type="button" class="group relative p-2 rounded-xl transition-colors shadow-xs" style="background-color: #ffffff; color: #121316; border: 2px solid #121316;" title="Tutup panel">
                    <i class="fa-solid fa-xmark text-sm"></i>
                    <span class="absolute -bottom-8 right-0 text-[10px] font-mono-code font-black px-2 py-0.5 rounded-md opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md z-30" style="background-color: #121316; color: #ff3823; border: 1.5px solid #000000;">Tutup</span>
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
                        <video id="preview-video" class="w-full h-full object-contain bg-black hidden" controls></video>
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
                            @php
                                $moduleHeaderBg = match($module->color) {
                                    'indigo'  => '#4f46e5',
                                    'emerald' => '#059669',
                                    'rose'    => '#e11d48',
                                    'amber'   => '#d97706',
                                    'blue'    => '#2563eb',
                                    'purple'  => '#9333ea',
                                    'cyan'    => '#0891b2',
                                    'orange'  => '#ea580c',
                                    default   => '#2563eb'
                                };
                            @endphp
                            <div class="poster-card bg-white overflow-hidden rounded-2xl" style="border: 2px solid #121316; box-shadow: 3px 3px 0px #121316;">
                                {{-- Header Modul: Sesuai aturan warna tema modul LMS --}}
                                <div class="p-3 flex items-center justify-between" style="background-color: {{ $moduleHeaderBg }} !important; color: #ffffff !important; border-bottom: 2px solid #121316 !important;">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="w-6 h-6 rounded-lg flex items-center justify-center text-[11px] font-black border border-black shadow-xs flex-shrink-0" style="background-color: #fbbf24; color: #121316;">{{ $module->sequence }}</span>
                                        <h4 class="text-xs font-black truncate font-mono-code text-white">{{ $module->title }}</h4>
                                    </div>
                                    <span class="text-[10px] font-mono-code font-bold px-2 py-0.5 rounded-md flex-shrink-0" style="background-color: #fbc02d; color: #121316; border: 1.5px solid #000000;">{{ $module->materials()->active()->count() }} Materi</span>
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

            {{-- TAB 3: DAFTAR SISWA, ANGKAT TANGAN & SUASANA HATI REALTIME --}}
            <div id="tab-content-peserta" class="flex-1 flex flex-col overflow-hidden hidden" style="background-color: #faf8f5;">
                
                {{-- Header Sub-bar --}}
                <div class="p-3 flex items-center justify-between flex-shrink-0 shadow-xs" style="background-color: #ffffff; border-bottom: 2px solid #121316;">
                    <div>
                        <div class="text-[10px] font-mono-code font-black uppercase" style="color: #ff3823;">✱ KONTROL KELAS</div>
                        <h3 class="text-xs font-black" style="color: #121316;">Presensi & Suasana Hati</h3>
                    </div>
                    <a href="{{ route('guru.lms.meeting.attendance', $course->id) }}" target="_blank" class="btn-tactile-yellow text-[10px] font-mono-code font-bold px-3 py-1.5 rounded-xl flex items-center gap-1.5">
                        <i class="fa-solid fa-file-invoice"></i> Rekap Sesi
                    </a>
                </div>

                {{-- Siswa Angkat Tangan (Alert Box Guru) --}}
                <div id="hands-raised-section" class="hidden p-3 bg-[#fef2f2] border-b-2 border-[#121316] flex-shrink-0">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] font-mono-code font-black text-[#dc2626] flex items-center gap-1.5">
                            <i class="fa-solid fa-hand animate-bounce"></i> SISWA ANGKAT TANGAN (<span id="hands-raised-count">0</span>)
                        </span>
                        <span class="text-[9px] font-mono-code text-[#7f1d1d] font-bold">Klik untuk panggil</span>
                    </div>
                    <div id="hands-raised-list" class="space-y-1.5">
                        {{-- Items angkat tangan di-render dinamis --}}
                    </div>
                </div>

                {{-- Rekap Suasana Hati Kelas (Mood Meter) --}}
                <div class="p-3 bg-white border-b-2 border-[#121316] flex-shrink-0">
                    <div class="text-[10px] font-mono-code font-black uppercase text-[#475569] mb-1.5 flex items-center justify-between">
                        <span>✱ REKAP SUASANA HATI KELAS</span>
                        <span class="text-[9px] text-[#94a3b8]">Live Feedback</span>
                    </div>
                    <div id="mood-meter-badges" class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-[10px] font-mono-code font-bold text-slate-400">Belum ada respon suasana hati siswa.</span>
                    </div>
                </div>

                {{-- List Peserta (Vibrant Poster Card Style) --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-2.5 graph-paper-box" id="attendees-list">
                    <div class="text-center text-xs py-12 poster-card bg-white p-6" style="color: #777;">
                        <i class="fa-solid fa-spinner animate-spin text-2xl mb-2 block" style="color: #4f46e5;"></i>
                        <p class="font-mono-code font-bold">Memuat daftar siswa hadir...</p>
                    </div>
                </div>
            </div>

            {{-- TAB 4: CHAT KELAS REALTIME (PUBLIK & DIRECT MESSAGE) --}}
            <div id="tab-content-chat" class="flex-1 flex flex-col overflow-hidden hidden" style="background-color: #faf8f5;">
                {{-- Header Chat --}}
                <div class="p-3 flex items-center justify-between flex-shrink-0 shadow-xs" style="background-color: #ffffff; border-bottom: 2px solid #121316;">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs text-white" style="background-color: #2563eb; border: 1.5px solid #121316;">
                            <i class="fa-solid fa-comments"></i>
                        </span>
                        <div>
                            <div class="text-[10px] font-mono-code font-black uppercase" style="color: #2563eb;">✱ OBROLAN KELAS</div>
                            <h3 class="text-xs font-black" style="color: #121316;">Chat Publik & Pribadi</h3>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-mono-code font-bold text-slate-500">Tujuan:</span>
                        <select id="chat-recipient-select" class="text-[11px] font-mono-code font-bold px-2 py-1 rounded-xl bg-white border-2 border-black focus:outline-none" style="box-shadow: 2px 2px 0px #121316;">
                            <option value="all">🌐 Semua Orang (Publik)</option>
                        </select>
                    </div>
                </div>

                {{-- Chat Messages List --}}
                <div id="chat-messages-container" class="flex-1 overflow-y-auto p-3 space-y-2.5 graph-paper-box">
                    <div id="chat-empty-state" class="text-center text-xs py-12 poster-card bg-white p-6" style="color: #777;">
                        <i class="fa-regular fa-comment-dots text-3xl mb-2 block" style="color: #2563eb;"></i>
                        <p class="font-mono-code font-bold text-[#121316]">Belum ada pesan obrolan.</p>
                        <p class="text-[10px] font-medium text-slate-500 mt-1">Gunakan chat untuk berdiskusi, menyapa kelas, atau mengirim pesan pribadi.</p>
                    </div>
                </div>

                {{-- Quick Reactions Bar --}}
                <div class="px-3 py-1.5 flex items-center gap-1.5 overflow-x-auto flex-shrink-0 bg-white" style="border-top: 1.5px solid #e2ded5;">
                    <span class="text-[10px] font-mono-code font-bold text-slate-400 mr-1 flex-shrink-0">Reaksi:</span>
                    <button onclick="sendQuickReaction('👍')" type="button" class="px-2 py-0.5 rounded-lg text-xs hover:scale-110 active:scale-95 transition bg-slate-100 border border-slate-300">👍</button>
                    <button onclick="sendQuickReaction('👏')" type="button" class="px-2 py-0.5 rounded-lg text-xs hover:scale-110 active:scale-95 transition bg-slate-100 border border-slate-300">👏</button>
                    <button onclick="sendQuickReaction('❤️')" type="button" class="px-2 py-0.5 rounded-lg text-xs hover:scale-110 active:scale-95 transition bg-slate-100 border border-slate-300">❤️</button>
                    <button onclick="sendQuickReaction('💡')" type="button" class="px-2 py-0.5 rounded-lg text-xs hover:scale-110 active:scale-95 transition bg-slate-100 border border-slate-300">💡</button>
                    <button onclick="sendQuickReaction('🙋')" type="button" class="px-2 py-0.5 rounded-lg text-xs hover:scale-110 active:scale-95 transition bg-slate-100 border border-slate-300">🙋</button>
                    <button onclick="sendQuickReaction('❓')" type="button" class="px-2 py-0.5 rounded-lg text-xs hover:scale-110 active:scale-95 transition bg-slate-100 border border-slate-300">❓</button>
                </div>

                {{-- Chat Input Form --}}
                <div class="p-2.5 bg-white flex items-center gap-2 flex-shrink-0" style="border-top: 2px solid #121316;">
                    <input type="text" id="chat-input-text" placeholder="Tulis pesan ke semua atau siswa..." class="flex-1 px-3 py-2 text-xs font-mono-code rounded-xl border-2 border-black focus:outline-none" style="box-shadow: 2px 2px 0px #121316;" onkeydown="if(event.key==='Enter') sendChatMessage()">
                    <button onclick="sendChatMessage()" type="button" class="btn-tactile-red px-3.5 py-2 rounded-xl text-xs font-mono-code font-black flex items-center gap-1 active:scale-95">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                        <span class="hidden sm:inline">Kirim</span>
                    </button>
                </div>
            </div>

            {{-- TAB 5: POLLING KILAT (QUICK POLL) --}}
            <div id="tab-content-polling" class="flex-1 flex flex-col overflow-hidden hidden" style="background-color: #faf8f5;">
                {{-- Header Polling --}}
                <div class="p-3 flex items-center justify-between flex-shrink-0 shadow-xs" style="background-color: #ffffff; border-bottom: 2px solid #121316;">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs border border-black shadow-xs" style="background-color: #d97706; color: #ffffff;">
                            <i class="fa-solid fa-square-poll-vertical"></i>
                        </div>
                        <div>
                            <div class="text-xs font-black" style="color: #121316;">Polling Kilat KBM</div>
                            <div class="text-[9px] font-mono-code font-bold text-slate-500">Uji pemahaman kelas secara real-time</div>
                        </div>
                    </div>
                    <div id="poll-header-status-badge">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-slate-100 text-slate-600 border border-slate-300">Siap Buat</span>
                    </div>
                </div>

                {{-- Konten Utama Polling --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-4">
                    
                    {{-- Section 1: Active Poll View (Jika ada polling aktif/selesai) --}}
                    <div id="guru-active-poll-card" class="hidden p-4 rounded-2xl bg-white space-y-3 relative overflow-hidden" style="border: 2px solid #121316; box-shadow: 4px 4px 0px #d97706;">
                        <div class="flex items-center justify-between">
                            <span id="guru-poll-live-indicator" class="px-2 py-0.5 rounded-md text-[10px] font-mono-code font-black flex items-center gap-1.5" style="background-color: #fef3c7; color: #92400e; border: 1px solid #f59e0b;">
                                <span class="w-2 h-2 rounded-full animate-ping bg-amber-500"></span> POLLING BERLANGSUNG
                            </span>
                            <span id="guru-poll-total-badge" class="text-xs font-mono-code font-black text-slate-700">0 Suara Masuk</span>
                        </div>

                        <h4 id="guru-poll-question-text" class="text-sm font-black leading-snug" style="color: #121316;">
                            Pertanyaan Polling
                        </h4>

                        {{-- Options & Progress Bars Container --}}
                        <div id="guru-poll-options-container" class="space-y-2 pt-1">
                            {{-- Diisi secara dinamis oleh JavaScript --}}
                        </div>

                        {{-- Action Buttons Polling --}}
                        <div class="pt-2 border-t border-slate-200 flex items-center justify-between gap-2">
                            <button id="btn-end-poll" onclick="endCurrentPoll()" type="button" class="btn-tactile-white px-3 py-1.5 rounded-xl text-xs font-mono-code font-bold flex items-center gap-1 text-rose-600">
                                <i class="fa-solid fa-lock"></i> Kunci / Tutup
                            </button>
                            <button onclick="clearCurrentPoll()" type="button" class="btn-tactile-red px-3 py-1.5 rounded-xl text-xs font-mono-code font-black flex items-center gap-1">
                                <i class="fa-solid fa-plus"></i> Buat Baru
                            </button>
                        </div>
                    </div>

                    {{-- Section 2: Form Buat Polling Baru --}}
                    <div id="guru-create-poll-card" class="p-4 rounded-2xl bg-white space-y-3.5" style="border: 2px solid #121316; box-shadow: 4px 4px 0px #121316;">
                        <div>
                            <div class="text-[10px] font-mono-code font-black uppercase text-amber-700">✱ TEMPLATE CEPAT (1-KLIK)</div>
                            <div class="grid grid-cols-3 gap-2 mt-1.5">
                                <button onclick="setPollPreset('paham')" type="button" class="p-2 rounded-xl text-left bg-amber-50 border border-amber-300 hover:bg-amber-100 transition active:scale-95">
                                    <div class="text-[11px] font-black text-amber-900 leading-tight">Paham / Belum</div>
                                    <div class="text-[9px] font-mono-code text-amber-700">3 Pilihan</div>
                                </button>
                                <button onclick="setPollPreset('setuju')" type="button" class="p-2 rounded-xl text-left bg-sky-50 border border-sky-300 hover:bg-sky-100 transition active:scale-95">
                                    <div class="text-[11px] font-black text-sky-900 leading-tight">Ya / Tidak</div>
                                    <div class="text-[9px] font-mono-code text-sky-700">2 Pilihan</div>
                                </button>
                                <button onclick="setPollPreset('pilgan')" type="button" class="p-2 rounded-xl text-left bg-purple-50 border border-purple-300 hover:bg-purple-100 transition active:scale-95">
                                    <div class="text-[11px] font-black text-purple-900 leading-tight">Pilgan A/B/C/D</div>
                                    <div class="text-[9px] font-mono-code text-purple-700">4 Pilihan</div>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-mono-code font-black text-slate-800 mb-1">Pertanyaan Polling:</label>
                            <textarea id="poll-input-question" rows="2" class="w-full px-3 py-2 text-xs font-mono-code rounded-xl border-2 border-black focus:outline-none" style="box-shadow: 2px 2px 0px #121316;" placeholder="Contoh: Apakah konsep rangkaian paralel ini sudah jelas?"></textarea>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[11px] font-mono-code font-black text-slate-800">Pilihan Jawaban:</label>
                                <button onclick="addPollOptionField()" type="button" class="text-[10px] font-mono-code font-bold text-indigo-600 hover:underline flex items-center gap-1">
                                    <i class="fa-solid fa-plus-circle"></i> Tambah Opsi
                                </button>
                            </div>
                            <div id="poll-options-inputs" class="space-y-2">
                                <div class="flex items-center gap-2 option-row">
                                    <span class="w-6 h-6 rounded-md bg-slate-100 border border-black flex items-center justify-center text-xs font-mono-code font-black flex-shrink-0">1</span>
                                    <input type="text" class="poll-opt-input flex-1 px-3 py-1.5 text-xs font-mono-code rounded-xl border border-black focus:outline-none" value="Paham dan Jelas">
                                </div>
                                <div class="flex items-center gap-2 option-row">
                                    <span class="w-6 h-6 rounded-md bg-slate-100 border border-black flex items-center justify-center text-xs font-mono-code font-black flex-shrink-0">2</span>
                                    <input type="text" class="poll-opt-input flex-1 px-3 py-1.5 text-xs font-mono-code rounded-xl border border-black focus:outline-none" value="Masih Bingung">
                                </div>
                                <div class="flex items-center gap-2 option-row">
                                    <span class="w-6 h-6 rounded-md bg-slate-100 border border-black flex items-center justify-center text-xs font-mono-code font-black flex-shrink-0">3</span>
                                    <input type="text" class="poll-opt-input flex-1 px-3 py-1.5 text-xs font-mono-code rounded-xl border border-black focus:outline-none" value="Perlu Contoh Soal">
                                </div>
                            </div>
                        </div>

                        <button onclick="submitCreatePoll()" type="button" class="w-full btn-tactile-red py-2.5 rounded-xl text-xs font-mono-code font-black flex items-center justify-center gap-2">
                            <i class="fa-solid fa-bullhorn text-xs"></i>
                            <span>Luncurkan Polling ke Layar Siswa</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- TAB 6: TANYA JAWAB TERSTRUKTUR (Q&A QUEUE) --}}
            <div id="tab-content-qa" class="flex-1 flex flex-col overflow-hidden hidden" style="background-color: #faf8f5;">
                {{-- Header Q&A --}}
                <div class="p-3 flex items-center justify-between flex-shrink-0 shadow-xs" style="background-color: #ffffff; border-bottom: 2px solid #121316;">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs border border-black shadow-xs" style="background-color: #ea580c; color: #ffffff;">
                            <i class="fa-solid fa-clipboard-question"></i>
                        </div>
                        <div>
                            <div class="text-xs font-black" style="color: #121316;">Antrean Pertanyaan (Q&A)</div>
                            <div class="text-[9px] font-mono-code font-bold text-slate-500">Terurut dari upvote terbanyak siswa</div>
                        </div>
                    </div>
                    <div>
                        <span id="guru-qa-count-badge" class="px-2 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-orange-100 text-orange-800 border border-orange-300">0 Pertanyaan</span>
                    </div>
                </div>

                {{-- Daftar Pertanyaan Q&A --}}
                <div id="guru-qa-list-container" class="flex-1 overflow-y-auto p-4 space-y-3">
                    <div id="guru-qa-empty" class="text-center py-10 px-4">
                        <div class="w-12 h-12 mx-auto rounded-2xl bg-orange-100 text-orange-600 border border-orange-300 flex items-center justify-center text-xl mb-2">
                            <i class="fa-solid fa-comment-dots"></i>
                        </div>
                        <p class="text-xs font-mono-code font-bold text-slate-700">Belum ada pertanyaan materi</p>
                        <p class="text-[10px] text-slate-500 mt-1">Siswa dapat menuliskan pertanyaan materi melalui tab Q&A mereka dan memberikan upvote.</p>
                    </div>
                </div>
            </div>

            {{-- TAB 7: KUIS INTERAKTIF LIVE (KAHOOT-STYLE SUPER CLASSROOM) --}}
            <div id="tab-content-kuis" class="flex-1 flex flex-col overflow-hidden hidden" style="background-color: #faf8f5;">
                {{-- Header Kuis --}}
                <div class="p-3 flex items-center justify-between flex-shrink-0 shadow-xs" style="background-color: #ffffff; border-bottom: 2px solid #121316;">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs border border-black shadow-xs" style="background-color: #ec4899; color: #ffffff;">
                            <i class="fa-solid fa-gamepad"></i>
                        </div>
                        <div>
                            <div class="text-xs font-black" style="color: #121316;">Kuis Interaktif Live</div>
                            <div class="text-[9px] font-mono-code font-bold text-slate-500">Kahoot-style • Realtime Skor &amp; Podium</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span id="guru-quiz-status-badge" class="px-2 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-slate-100 text-slate-700 border border-slate-300">Standby</span>
                    </div>
                </div>

                {{-- Scrollable Container --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-4">
                    
                    {{-- 1. VIEW LAUNCHER --}}
                    <div id="quiz-view-launcher" class="space-y-4">
                        <div class="p-4 rounded-2xl bg-white border-2 border-black" style="box-shadow: 4px 4px 0px #121316;">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-mono-code font-black bg-pink-100 text-pink-700 border border-pink-300">FASE 4</span>
                                <h4 class="text-xs font-black uppercase text-slate-900">Luncurkan Kuis Kelas</h4>
                            </div>
                            
                            {{-- Mode Toggle --}}
                            <div class="grid grid-cols-2 gap-2 mb-4 p-1 rounded-xl bg-slate-100 border border-slate-300">
                                <button type="button" onclick="setQuizLauncherMode('lms')" id="btn-mode-lms-quiz" class="py-1.5 text-xs font-mono-code font-black rounded-lg bg-black text-amber-300 transition">
                                    Dari Kuis LMS
                                </button>
                                <button type="button" onclick="setQuizLauncherMode('custom')" id="btn-mode-custom-quiz" class="py-1.5 text-xs font-mono-code font-bold rounded-lg text-slate-700 transition">
                                    Soal Cepat KBM
                                </button>
                            </div>

                            {{-- Mode 1: Pilih dari Kuis LMS --}}
                            <div id="quiz-mode-lms" class="space-y-3">
                                <div>
                                    <label class="block text-[10px] font-mono-code font-bold text-slate-600 mb-1">PILIH KUIS MATERI KURSUS INI:</label>
                                    <select id="guru-quiz-select" class="w-full text-xs font-bold p-2.5 rounded-xl border-2 border-black bg-white focus:ring-0">
                                        @if(isset($courseQuizzes) && $courseQuizzes->count() > 0)
                                            <option value="">-- Pilih Kuis LMS yang Siap Diuji --</option>
                                            @foreach($courseQuizzes as $q)
                                                <option value="{{ $q->id }}" data-questions="{{ $q->questions->count() }}">
                                                    {{ $q->title }} ({{ $q->questions->count() }} Soal)
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="">(Belum ada Kuis dibuat di kursus ini - gunakan tab 'Soal Cepat KBM')</option>
                                        @endif
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-mono-code font-bold text-slate-600 mb-1">DURASI WAKTU PER SOAL:</label>
                                    <select id="guru-quiz-lms-time" class="w-full text-xs font-bold p-2.5 rounded-xl border-2 border-black bg-white focus:ring-0">
                                        <option value="15">15 Detik (Cepat &amp; Menantang)</option>
                                        <option value="20" selected>20 Detik (Standar Rekomendasi)</option>
                                        <option value="30">30 Detik (Santai)</option>
                                        <option value="45">45 Detik (Analisis Soal Hitungan)</option>
                                        <option value="60">60 Detik (1 Menit)</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Mode 2: Soal Cepat KBM --}}
                            <div id="quiz-mode-custom" class="space-y-3 hidden">
                                <div>
                                    <label class="block text-[10px] font-mono-code font-bold text-slate-600 mb-1">JUDUL KUIS KILAT:</label>
                                    <input type="text" id="guru-custom-quiz-title" value="Kuis Kilat Pemahaman Materi" class="w-full text-xs font-bold p-2.5 rounded-xl border-2 border-black bg-white focus:ring-0">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-mono-code font-bold text-slate-600 mb-1">PERTANYAAN SOAL:</label>
                                    <textarea id="guru-custom-question" rows="2" placeholder="Tuliskan pertanyaan materi di sini..." class="w-full text-xs font-bold p-2.5 rounded-xl border-2 border-black bg-white focus:ring-0">Berapakah nilai tegangan jika arus 2A dan hambatan 10 Ohm?</textarea>
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-mono-code font-bold text-slate-600">PILIHAN JAWABAN &amp; KUNCI BENAR:</label>
                                    
                                    {{-- Opsi A Merah --}}
                                    <div class="flex items-center gap-2">
                                        <input type="radio" name="custom_correct_ans" value="0" id="ans_rad_0" class="w-4 h-4 text-red-600 focus:ring-0">
                                        <span class="w-6 h-6 rounded-md flex items-center justify-center text-white font-mono-code font-black text-xs" style="background-color: #ef4444;">▲</span>
                                        <input type="text" id="ans_opt_0" value="5 Volt" placeholder="Pilihan A" class="flex-1 text-xs font-bold p-2 rounded-xl border-2 border-black bg-white">
                                    </div>
                                    {{-- Opsi B Biru --}}
                                    <div class="flex items-center gap-2">
                                        <input type="radio" name="custom_correct_ans" value="1" id="ans_rad_1" checked class="w-4 h-4 text-blue-600 focus:ring-0">
                                        <span class="w-6 h-6 rounded-md flex items-center justify-center text-white font-mono-code font-black text-xs" style="background-color: #3b82f6;">◆</span>
                                        <input type="text" id="ans_opt_1" value="20 Volt" placeholder="Pilihan B" class="flex-1 text-xs font-bold p-2 rounded-xl border-2 border-black bg-white">
                                    </div>
                                    {{-- Opsi C Kuning --}}
                                    <div class="flex items-center gap-2">
                                        <input type="radio" name="custom_correct_ans" value="2" id="ans_rad_2" class="w-4 h-4 text-amber-500 focus:ring-0">
                                        <span class="w-6 h-6 rounded-md flex items-center justify-center text-white font-mono-code font-black text-xs" style="background-color: #f59e0b;">●</span>
                                        <input type="text" id="ans_opt_2" value="12 Volt" placeholder="Pilihan C" class="flex-1 text-xs font-bold p-2 rounded-xl border-2 border-black bg-white">
                                    </div>
                                    {{-- Opsi D Hijau --}}
                                    <div class="flex items-center gap-2">
                                        <input type="radio" name="custom_correct_ans" value="3" id="ans_rad_3" class="w-4 h-4 text-emerald-600 focus:ring-0">
                                        <span class="w-6 h-6 rounded-md flex items-center justify-center text-white font-mono-code font-black text-xs" style="background-color: #10b981;">■</span>
                                        <input type="text" id="ans_opt_3" value="8 Volt" placeholder="Pilihan D" class="flex-1 text-xs font-bold p-2 rounded-xl border-2 border-black bg-white">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-mono-code font-bold text-slate-600 mb-1">DURASI WAKTU SOAL:</label>
                                    <select id="guru-custom-quiz-time" class="w-full text-xs font-bold p-2.5 rounded-xl border-2 border-black bg-white focus:ring-0">
                                        <option value="15">15 Detik</option>
                                        <option value="20" selected>20 Detik (Standar)</option>
                                        <option value="30">30 Detik</option>
                                        <option value="60">60 Detik</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Tombol Launch --}}
                            <div class="pt-4">
                                <button type="button" onclick="startGuruLiveQuiz()" class="w-full py-3 px-4 rounded-2xl text-xs font-mono-code font-black uppercase tracking-wider flex items-center justify-center gap-2 transition active:scale-95 text-white border-2 border-black" style="background-color: #ec4899; box-shadow: 4px 4px 0px #121316;">
                                    <i class="fa-solid fa-rocket"></i> Luncurkan Kuis Live Sekarang
                                </button>
                            </div>
                        </div>

                        {{-- Info Kotak Hadiah Reputasi --}}
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-300 text-xs font-mono-code text-amber-950 flex items-start gap-2.5">
                            <span class="text-xl">🏆</span>
                            <div>
                                <p class="font-black text-amber-900">Bonus Poin Reputasi Otomatis:</p>
                                <p class="text-[11px] text-amber-800 mt-0.5">Top 3 siswa tercepat dan tertepat akan langsung mendapat bonus reputasi di profil mereka: 🥇 +15 Poin, 🥈 +10 Poin, 🥉 +5 Poin.</p>
                            </div>
                        </div>
                    </div>

                    {{-- 2. VIEW RUNNING QUESTION --}}
                    <div id="quiz-view-question" class="hidden space-y-4">
                        <div class="p-4 rounded-2xl bg-white border-2 border-black" style="box-shadow: 4px 4px 0px #121316;">
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span id="guru-q-badge" class="px-2.5 py-1 rounded-lg text-xs font-mono-code font-black bg-pink-100 text-pink-700 border border-pink-300">
                                    Soal 1 / 1
                                </span>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-mono-code font-bold text-slate-500">Sisa Waktu:</span>
                                    <span id="guru-q-timer" class="px-2.5 py-0.5 rounded-md text-xs font-mono-code font-black bg-rose-500 text-white animate-pulse">
                                        20s
                                    </span>
                                </div>
                            </div>

                            {{-- Question Text --}}
                            <div class="p-3 rounded-xl bg-slate-50 border-2 border-slate-200 mb-4">
                                <h3 id="guru-q-text" class="text-sm font-extrabold text-slate-900 leading-snug">
                                    Pertanyaan sedang dimuat...
                                </h3>
                            </div>

                            {{-- Live Answers Progress Bar --}}
                            <div class="mb-4">
                                <div class="flex items-center justify-between text-xs font-mono-code font-bold text-slate-600 mb-1">
                                    <span>Respon Siswa:</span>
                                    <span id="guru-q-answers-count" class="font-black text-pink-600">0 / 0 Siswa Menjawab</span>
                                </div>
                                <div class="w-full h-3 rounded-full bg-slate-100 border border-slate-300 overflow-hidden">
                                    <div id="guru-q-progress-bar" class="h-full bg-pink-500 transition-all duration-300" style="width: 0%;"></div>
                                </div>
                            </div>

                            {{-- 4 Pilihan Jawaban Cards --}}
                            <div id="guru-q-options-grid" class="grid grid-cols-2 gap-2 mb-4">
                                {{-- Diisi dynamically via JS --}}
                            </div>

                            {{-- Tombol Scoreboard & Batal --}}
                            <div class="flex items-center gap-2 pt-2 border-t border-slate-200">
                                <button type="button" onclick="showGuruQuizScoreboard()" class="flex-1 py-2.5 px-3 rounded-xl text-xs font-mono-code font-black text-white bg-indigo-600 hover:bg-indigo-700 active:scale-95 transition border-2 border-black" style="box-shadow: 3px 3px 0px #121316;">
                                    <i class="fa-solid fa-chart-column mr-1"></i> Tampilkan Skor &amp; Jawaban
                                </button>
                                <button type="button" onclick="cancelGuruQuiz()" class="py-2.5 px-3 rounded-xl text-xs font-mono-code font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-300 transition" title="Batalkan kuis">
                                    <i class="fa-solid fa-stop"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- 3. VIEW SCOREBOARD & ANALYSIS --}}
                    <div id="quiz-view-scoreboard" class="hidden space-y-4">
                        <div class="p-4 rounded-2xl bg-white border-2 border-black" style="box-shadow: 4px 4px 0px #121316;">
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-mono-code font-black bg-indigo-100 text-indigo-700 border border-indigo-300">
                                    PAPAN SKOR SEMENTARA
                                </span>
                                <span id="guru-score-q-badge" class="text-xs font-mono-code font-bold text-slate-500">Soal 1 / 1</span>
                            </div>

                            {{-- Bar Chart Distribusi Jawaban --}}
                            <div class="mb-4 p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <div class="text-[10px] font-mono-code font-bold text-slate-600 uppercase mb-2">Distribusi Pilihan Siswa:</div>
                                <div id="guru-score-distribution" class="space-y-1.5">
                                    {{-- Diisi secara dinamis --}}
                                </div>
                            </div>

                            {{-- Leaderboard Top 5 Siswa --}}
                            <div class="mb-4">
                                <div class="flex items-center justify-between text-xs font-mono-code font-bold text-slate-700 mb-2">
                                    <span>🏅 TOP 5 SKOR TERTINGGI:</span>
                                    <span class="text-[10px] text-slate-400">Poin Realtime</span>
                                </div>
                                <div id="guru-score-leaderboard" class="space-y-1.5">
                                    {{-- Diisi secara dinamis --}}
                                </div>
                            </div>

                            {{-- Navigation Buttons --}}
                            <div class="flex items-center gap-2 pt-2 border-t border-slate-200">
                                <button type="button" id="btn-guru-next-q" onclick="guruNextQuizQuestion()" class="flex-1 py-2.5 px-3 rounded-xl text-xs font-mono-code font-black text-white bg-emerald-600 hover:bg-emerald-700 active:scale-95 transition border-2 border-black" style="box-shadow: 3px 3px 0px #121316;">
                                    <span>Soal Berikutnya</span> <i class="fa-solid fa-arrow-right ml-1"></i>
                                </button>
                                <button type="button" onclick="endGuruQuizToPodium()" class="py-2.5 px-3 rounded-xl text-xs font-mono-code font-black text-amber-900 bg-amber-300 hover:bg-amber-400 border-2 border-black transition" style="box-shadow: 2px 2px 0px #121316;" title="Selesaikan kuis & buka podium">
                                    <i class="fa-solid fa-trophy mr-1"></i> Buka Podium
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- 4. VIEW PODIUM FINAL --}}
                    <div id="quiz-view-podium" class="hidden space-y-4">
                        <div class="p-5 rounded-2xl bg-white border-2 border-black text-center" style="box-shadow: 5px 5px 0px #121316;">
                            <div class="w-14 h-14 mx-auto rounded-2xl flex items-center justify-center text-3xl mb-2 border-2 border-black animate-bounce" style="background-color: #fde047; box-shadow: 3px 3px 0px #121316;">
                                🏆
                            </div>
                            <h3 class="text-base font-black text-slate-900">PODIUM JUARA KUIS LIVE</h3>
                            <p id="guru-podium-title" class="text-xs font-mono-code font-bold text-slate-500 mb-4">Kuis Interaktif KBM Selesai</p>

                            {{-- Podium 3 Besar Cards --}}
                            <div id="guru-podium-cards" class="grid grid-cols-3 gap-2 items-end mb-4 pt-4">
                                {{-- Diisi via JS: Juara 2 (kiri), Juara 1 (tengah), Juara 3 (kanan) --}}
                            </div>

                            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-300 text-xs font-mono-code text-emerald-950 mb-4">
                                <span class="font-black">✓ Poin Reputasi Diberikan:</span>
                                <span class="text-[11px] block text-emerald-800 mt-0.5">Juara 1 (+15 Poin), Juara 2 (+10 Poin), Juara 3 (+5 Poin) otomatis ditambahkan ke database.</span>
                            </div>

                            <button type="button" onclick="clearGuruLiveQuiz()" class="w-full py-2.5 px-4 rounded-xl text-xs font-mono-code font-black text-white bg-slate-900 hover:bg-slate-800 border-2 border-black transition active:scale-95" style="box-shadow: 3px 3px 0px #121316;">
                                Mulai Kuis Baru
                            </button>
                        </div>
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
                <div class="mt-3 p-2.5 rounded-xl flex items-center gap-2.5 text-xs font-mono-code" style="background-color: #fef3c7; color: #92400e; border: 1.5px solid #f59e0b;">
                    <div class="w-7 h-7 rounded-lg bg-amber-400 text-black flex items-center justify-center font-bold text-sm flex-shrink-0 border border-black shadow-xs">
                        <i class="fa-solid fa-award"></i>
                    </div>
                    <div>
                        <span class="font-black text-amber-950">+50 Poin Reputasi Guru</span> otomatis diraih jika durasi KBM &ge; 5 menit dan ada siswa hadir. Siswa yang hadir juga meraih <span class="font-black text-amber-950">+25 Poin</span>.
                    </div>
                </div>
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

    {{-- MODAL SIMPAN REKAMAN PEMBELAJARAN KE MATERI LMS (FASE 4+ FEATURE) --}}
    <div id="modal-save-recording" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden" style="background-color: rgba(18, 19, 22, 0.8); backdrop-filter: blur(4px);">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 relative border-2 border-black" style="box-shadow: 8px 8px 0px #ff3823;">
            <div class="flex items-center justify-between border-b-2 border-black pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-red-500 text-white flex items-center justify-center border border-black shadow-xs">
                        <i class="fa-solid fa-video"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900">Publikasikan Rekaman KBM</h3>
                        <p class="text-[10px] font-mono-code font-bold text-slate-500">Rekaman akan otomatis tampil di Modul Materi Siswa</p>
                    </div>
                </div>
                <button onclick="closeSaveRecordingModal()" type="button" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center border border-black transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Video Preview Player --}}
            <div class="rounded-2xl overflow-hidden bg-black border-2 border-black aspect-video flex items-center justify-center relative shadow-inner">
                <video id="recording-preview-video" controls class="w-full h-full object-contain"></video>
            </div>

            {{-- Recording Info & Stats --}}
            <div class="flex items-center justify-between text-[11px] font-mono-code px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-300">
                <span class="text-slate-600">Durasi: <strong id="recording-preview-duration" class="text-black font-black">00:00</strong></span>
                <span class="text-slate-600">Ukuran: <strong id="recording-preview-size" class="text-black font-black">0 MB</strong></span>
            </div>

            {{-- Form Fields --}}
            <div class="space-y-3">
                <div>
                    <label class="block text-[10px] font-mono-code font-black text-slate-700 uppercase mb-1">Judul Materi Rekaman:</label>
                    <input type="text" id="rec-material-title" class="w-full text-xs font-bold p-2.5 rounded-xl border-2 border-black bg-white focus:ring-0" value="Rekaman KBM: {{ $course->course_name ?? $course->name }} ({{ now()->format('d M Y') }})">
                </div>

                <div>
                    <label class="block text-[10px] font-mono-code font-black text-slate-700 uppercase mb-1">Pilih Modul Pembelajaran:</label>
                    <select id="rec-material-module-id" class="w-full text-xs font-bold p-2.5 rounded-xl border-2 border-black bg-white focus:ring-0">
                        @if($course->modules && $course->modules->count() > 0)
                            @foreach($course->modules as $mod)
                                <option value="{{ $mod->id }}">{{ $mod->title }}</option>
                            @endforeach
                        @else
                            <option value="">Buat Modul Baru Otomatis: Rekaman PembdaTEMU</option>
                        @endif
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-mono-code font-black text-slate-700 uppercase mb-1">Deskripsi Singkat:</label>
                    <textarea id="rec-material-desc" rows="2" class="w-full text-xs font-bold p-2.5 rounded-xl border-2 border-black bg-white focus:ring-0" placeholder="Catatan materi atau pembahasan pada sesi ini...">Video rekaman pembelajaran tatap muka PembdaTEMU mata pelajaran {{ $course->subject->name ?? 'ini' }} untuk dipelajari kembali secara mandiri.</textarea>
                </div>
            </div>

            {{-- Upload Progress Bar (Hidden by default) --}}
            <div id="recording-upload-progress-box" class="hidden space-y-1">
                <div class="flex items-center justify-between text-[10px] font-mono-code font-bold">
                    <span class="text-indigo-600 animate-pulse">Mengunggah rekaman ke server...</span>
                    <span id="recording-upload-pct" class="font-black">0%</span>
                </div>
                <div class="w-full h-2.5 rounded-full bg-slate-200 overflow-hidden border border-slate-300">
                    <div id="recording-upload-bar" class="h-full bg-indigo-600 transition-all duration-200" style="width: 0%;"></div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2 pt-2 border-t border-slate-200">
                <button type="button" onclick="downloadRecordingLocally()" class="px-3.5 py-2.5 rounded-xl text-xs font-mono-code font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-300 transition" title="Download salinan video ke laptop/komputer Anda">
                    <i class="fa-solid fa-download mr-1"></i> Download
                </button>
                <button type="button" id="btn-save-recording-submit" onclick="uploadRecordingToLms()" class="flex-1 py-2.5 px-4 rounded-xl text-xs font-mono-code font-black text-white bg-emerald-600 hover:bg-emerald-700 border-2 border-black transition active:scale-95 flex items-center justify-center gap-1.5 shadow-[2px_2px_0px_#121316]">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span>Simpan &amp; Terbitkan ke Siswa</span>
                </button>
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
        let activeStudentSpeakerUid = null; // UID siswa yang sedang bicara di mimbar kanan
        let activeSpeakerTimer = null;      // Timer hold speaker di mimbar
        let currentRaisedHands = [];        // Daftar siswa angkat tangan
        let currentMoods = {};              // user.uid => { mood, icon, label, color, bg }
        let currentChatList = [];           // Daftar pesan chat
        let unreadChatCount = 0;            // Jumlah pesan chat belum dibaca
        let isChatActiveTab = false;        // Status tab chat sedang aktif

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
                    // Ambang batas suara level >= 28 untuk mencegah suara bocor / background noise menaikkan siswa ke mimbar depan
                    if (vol.level >= 28) {
                        const targetTileId = (vol.uid === 0 || vol.uid === AGORA_UID) ? 'video-tile-local' : `video-tile-${vol.uid}`;
                        const tileEl = document.getElementById(targetTileId);
                        if (tileEl) tileEl.classList.add('speaking');

                        // Jika siswa yang berbicara (bukan guru lokal)
                        if (vol.uid !== 0 && vol.uid !== AGORA_UID) {
                            promoteStudentToSpeakerPodium(vol.uid);
                        }
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
                if (activeStudentSpeakerUid === String(user.uid)) {
                    activeStudentSpeakerUid = null;
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

        // 🎯 FITUR UNGGULAN: PROMOTE SISWA KE MIMBAR BICARA / BERTANYA (KANAN ATAS)
        function promoteStudentToSpeakerPodium(uid) {
            activeStudentSpeakerUid = String(uid);
            if (activeSpeakerTimer) clearTimeout(activeSpeakerTimer);
            if (currentLayout === 'classroom') {
                rearrangeTiles();
            }
            // Tetap di mimbar selama 7 detik setelah hening bicara
            activeSpeakerTimer = setTimeout(() => {
                activeStudentSpeakerUid = null;
                if (currentLayout === 'classroom') {
                    rearrangeTiles();
                }
            }, 7000);
        }

        // 🎯 BUAT DOM TILE UNTUK VIDEO REMOTE SISWA (LENGKAP DENGAN MOOD & HAND RAISE)
        function createRemoteTile(user) {
            const tile = document.createElement('div');
            tile.id = `video-tile-${user.uid}`;
            tile.className = 'video-tile group relative rounded-3xl overflow-hidden flex items-center justify-center transition-all duration-300';
            tile.style.backgroundColor = '#0f172a';
            tile.style.border = '2.5px solid #121316';
            tile.style.boxShadow = '4px 4px 0px #121316';
            
            const rawName = attendeesMap[user.uid] || `Siswa #${user.uid}`;
            const shortName = rawName.split(' ')[0];
            const mood = currentMoods[user.uid] || { icon: '🔥', label: 'Semangat', bg: '#ffedd5', color: '#ea580c' };
            const isHand = currentRaisedHands.some(h => String(h.user_id) === String(user.uid));

            tile.innerHTML = `
                <div id="remote-${user.uid}" class="w-full h-full relative"></div>
                <div id="remote-placeholder-${user.uid}" class="absolute inset-0 flex flex-col items-center justify-center hidden pointer-events-none" style="background-color: #121316; color: #cbd5e1;">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl font-black text-white mb-2 shadow-lg" style="background: linear-gradient(135deg, #0891b2, #4f46e5); border: 2px solid #ffffff;">
                        ${shortName.charAt(0)}
                    </div>
                    <p class="text-xs font-mono-code font-bold" style="color: #fde047;">${shortName}</p>
                </div>
                {{-- BADGE NAMA SISWA + SUASANA HATI + ANGKAT TANGAN (POJOK KIRI ATAS) --}}
                <div class="absolute top-3 left-3 z-20 flex items-center gap-1.5 flex-wrap max-w-[90%]">
                    <span class="seat-desk-tag px-1.5 py-0.5 rounded text-[8px] font-mono-code font-black" style="background-color: #fbc02d; color: #121316; border: 1px solid #121316;">Meja</span>
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-black shadow-md" style="background-color: #121316; color: #ffffff; border: 2px solid #121316; box-shadow: 2px 2px 0px #2563eb;" title="${rawName}">
                        <span class="w-2 h-2 rounded-full animate-pulse" style="background-color: #10b981;"></span>
                        <i id="tile-mic-${user.uid}" class="fa-solid fa-microphone text-xs" style="color: #10b981;"></i>
                        <span id="tile-name-${user.uid}" class="truncate max-w-[120px] font-mono-code font-bold" style="color: #fde047;">${shortName}</span>
                    </div>
                    <span id="tile-mood-${user.uid}" class="tile-mood-badge px-2 py-0.5 rounded-xl text-[10px] font-mono-code font-black border border-black shadow-xs flex items-center gap-1" style="background-color: ${mood.bg}; color: ${mood.color};" title="Suasana Hati: ${mood.label}">
                        <span>${mood.icon}</span> <span class="hidden sm:inline">${mood.label}</span>
                    </span>
                    <span id="tile-hand-${user.uid}" class="tile-hand-badge ${isHand ? '' : 'hidden'} px-2 py-0.5 rounded-xl text-[10px] font-mono-code font-black bg-red-600 text-white animate-bounce border border-black shadow-xs flex items-center gap-1">
                        <i class="fa-solid fa-hand"></i> Tanya
                    </span>
                </div>
                <div class="absolute top-3 right-3 z-20 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button onclick="promoteStudentToSpeakerPodium('${user.uid}')" type="button" class="p-1.5 rounded-xl text-xs transition active:scale-95" style="background-color: #fbc02d; color: #121316; border: 1.5px solid #121316; box-shadow: 2px 2px 0px #121316;" title="Panggil ke Mimbar Depan">
                        <i class="fa-solid fa-microphone-lines text-xs"></i>
                    </button>
                    <button onclick="pinSpeaker('${user.uid}')" type="button" class="p-1.5 rounded-xl text-xs active:scale-95 transition" style="background-color: #ffffff; color: #121316; border: 1.5px solid #121316; box-shadow: 2px 2px 0px #121316;" title="Fokuskan / Spotlight">
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
            const speakerSlot    = document.getElementById('podium-speaker-slot');
            const speakerIdle    = document.getElementById('podium-speaker-idle');
            const speakerLabel   = document.getElementById('podium-speaker-label');
            const speakerBadge   = document.getElementById('podium-speaker-badge');
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

                // 1. Tempatkan Video Guru di Mimbar Kiri
                if (teacherTile && teacherSlot) {
                    teacherSlot.appendChild(teacherTile);
                    teacherTile.className = 'video-tile relative rounded-3xl overflow-hidden flex items-center justify-center w-full h-full max-h-[82vh] transition-all';
                    teacherTile.style.backgroundColor = '#0f172a';
                    teacherTile.style.border = '2.5px solid #121316';
                    teacherTile.style.boxShadow = '4px 4px 0px #ff3823';
                    teacherTile.classList.remove('filmstrip-tile', 'pinned-speaker');
                    teacherTile.onclick = null;
                }

                // 2. Tempatkan Siswa yang Sedang Bicara / Bertanya di Mimbar Kanan
                let activeOnPodiumTile = null;
                let activeOnPodiumUid = null;

                if (activeStudentSpeakerUid) {
                    const candidate = document.getElementById(`video-tile-${activeStudentSpeakerUid}`);
                    if (candidate) {
                        activeOnPodiumTile = candidate;
                        activeOnPodiumUid = activeStudentSpeakerUid;
                    }
                } else if (currentRaisedHands.length > 0) {
                    const firstHandUid = currentRaisedHands[0].user_id;
                    const candidate = document.getElementById(`video-tile-${firstHandUid}`);
                    if (candidate) {
                        activeOnPodiumTile = candidate;
                        activeOnPodiumUid = String(firstHandUid);
                    }
                }

                const podiumContainer = document.getElementById('podium-container');
                const podiumGrid = document.getElementById('podium-grid');
                const speakerWrapper = document.getElementById('podium-speaker-wrapper');

                if (activeOnPodiumTile && speakerSlot) {
                    // ADA SISWA BICARA / BERTANYA: Buka mimbar kanan, lebarkan wadah, guru bergeser ke kiri
                    if (podiumContainer) {
                        podiumContainer.className = 'w-full max-w-5xl mx-auto transition-all duration-300';
                    }
                    if (podiumGrid) {
                        podiumGrid.className = 'w-full grid grid-cols-1 md:grid-cols-2 gap-3 items-center justify-center transition-all duration-300';
                    }
                    if (speakerWrapper) {
                        speakerWrapper.classList.remove('hidden');
                        speakerWrapper.classList.add('flex');
                    }

                    speakerSlot.appendChild(activeOnPodiumTile);
                    activeOnPodiumTile.className = 'video-tile relative rounded-3xl overflow-hidden flex items-center justify-center w-full h-full max-h-[82vh] transition-all';
                    activeOnPodiumTile.style.backgroundColor = '#0f172a';
                    activeOnPodiumTile.style.border = '3px solid #2563eb';
                    activeOnPodiumTile.style.boxShadow = '5px 5px 0px #121316';
                    activeOnPodiumTile.classList.remove('filmstrip-tile', 'pinned-speaker');
                    activeOnPodiumTile.onclick = null;

                    const pName = attendeesMap[activeOnPodiumUid] || `Siswa #${activeOnPodiumUid}`;
                    const isHand = (currentRaisedHands.find(h => String(h.user_id) === String(activeOnPodiumUid)));
                    if (speakerLabel) {
                        speakerLabel.textContent = isHand ? `✋ INGIN BERTANYA: ${pName}` : `🎙️ SEDANG BICARA: ${pName}`;
                    }
                    if (speakerBadge) {
                        speakerBadge.textContent = isHand ? 'ANGKAT TANGAN' : 'BICARA';
                        speakerBadge.style.backgroundColor = isHand ? '#ef4444' : '#10b981';
                    }
                } else {
                    // TIDAK ADA SISWA BICARA / BERTANYA: Sembunyikan mimbar kanan, video guru kembali ke tengah depan
                    if (speakerWrapper) {
                        speakerWrapper.classList.add('hidden');
                        speakerWrapper.classList.remove('flex');
                    }
                    if (podiumGrid) {
                        podiumGrid.className = 'w-full flex items-center justify-center transition-all duration-300';
                    }
                    if (podiumContainer) {
                        podiumContainer.className = 'w-full max-w-xl mx-auto transition-all duration-300';
                    }
                }

                // 3. Update occupancy pill
                if (occupancyPill) {
                    occupancyPill.textContent = `${studentTiles.length} / 40 Kursi Terisi`;
                }

                // 4. Render 40 Bangku Siswa (5 Baris x 8 Kolom)
                if (classroomGrid) {
                    classroomGrid.innerHTML = '';
                    const totalSlots = Math.max(40, studentTiles.length);
                    for (let i = 0; i < totalSlots; i++) {
                        const seatNum = String(i + 1).padStart(2, '0');
                        if (i < studentTiles.length) {
                            const sTile = studentTiles[i];
                            if (sTile === activeOnPodiumTile) {
                                // Tampilkan penanda bahwa siswa ini sedang berada di mimbar depan!
                                const reservedDesk = document.createElement('div');
                                reservedDesk.className = 'h-24 sm:h-28 rounded-2xl flex flex-col items-center justify-center p-2 text-center transition-all select-none';
                                reservedDesk.style.backgroundColor = '#eff6ff';
                                reservedDesk.style.border = '2px solid #2563eb';
                                reservedDesk.style.boxShadow = '3px 3px 0px #121316';
                                reservedDesk.innerHTML = `
                                    <span class="text-[9px] font-mono-code font-black mb-1" style="color: #1e40af;">Meja #${seatNum}</span>
                                    <i class="fa-solid fa-microphone-lines text-lg mb-1 animate-pulse" style="color: #2563eb;"></i>
                                    <span class="text-[8px] font-mono-code font-black uppercase px-2 py-0.5 rounded text-white" style="background-color: #2563eb;">Di Mimbar Depan 🎤</span>
                                `;
                                classroomGrid.appendChild(reservedDesk);
                            } else {
                                sTile.className = 'video-tile relative rounded-2xl overflow-hidden flex items-center justify-center w-full h-24 sm:h-28 aspect-video transition-all';
                                sTile.style.backgroundColor = '#0f172a';
                                sTile.style.border = '2px solid #121316';
                                sTile.style.boxShadow = '3px 3px 0px #121316';
                                sTile.classList.remove('filmstrip-tile', 'pinned-speaker');
                                sTile.onclick = null;

                                let deskTag = sTile.querySelector('.seat-desk-tag');
                                if (deskTag) {
                                    deskTag.textContent = `#${seatNum}`;
                                }
                                classroomGrid.appendChild(sTile);
                            }
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

            const tabs = ['materi', 'whiteboard', 'peserta', 'chat', 'polling', 'qa', 'kuis'];
            tabs.forEach(t => {
                const btn = document.getElementById('tab-btn-' + t);
                const content = document.getElementById('tab-content-' + t);
                if (t === tabName) {
                    if (btn) {
                        btn.style.backgroundColor = '#121316';
                        btn.style.color = '#fde047';
                        btn.style.border = '2px solid #121316';
                        btn.style.boxShadow = '2px 2px 0px #ff3823';
                    }
                    if (content) content.classList.remove('hidden');
                } else {
                    if (btn) {
                        btn.style.backgroundColor = '#ffffff';
                        btn.style.color = '#121316';
                        btn.style.border = '2px solid #121316';
                        btn.style.boxShadow = 'none';
                    }
                    if (content) content.classList.add('hidden');
                }
            });

            if (tabName === 'polling') {
                const pBadge = document.getElementById('poll-tab-badge');
                if (pBadge) pBadge.classList.add('hidden');
            }
            if (tabName === 'qa') {
                const qBadge = document.getElementById('qa-tab-badge');
                if (qBadge) qBadge.classList.add('hidden');
            }
            if (tabName === 'kuis') {
                const quizBadge = document.getElementById('quiz-tab-badge');
                if (quizBadge) quizBadge.classList.add('hidden');
            }

            if (tabName === 'chat') {
                isChatActiveTab = true;
                unreadChatCount = 0;
                const badge1 = document.getElementById('chat-tab-badge');
                const badge2 = document.getElementById('quick-chat-badge');
                if (badge1) badge1.classList.add('hidden');
                if (badge2) badge2.classList.add('hidden');
                const c = document.getElementById('chat-messages-container');
                if (c) c.scrollTop = c.scrollHeight;
            } else {
                isChatActiveTab = false;
            }

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
            const videoEl = document.getElementById('preview-video');
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
            if (videoEl) {
                videoEl.pause();
                videoEl.classList.add('hidden');
                videoEl.src = '';
            }

            if (type === 'video' && (url.endsWith('.webm') || url.endsWith('.mp4') || url.includes('/storage/lms/recordings/'))) {
                if (videoEl) {
                    videoEl.src = url;
                    videoEl.classList.remove('hidden');
                }
                extLink.href = url;
                extLink.classList.remove('hidden');
            } else if (type === 'text') {
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
            const videoEl = document.getElementById('preview-video');
            if (box) box.classList.add('hidden');
            if (iframe) iframe.src = 'about:blank';
            if (videoEl) {
                videoEl.pause();
                videoEl.src = '';
            }
        }

        // ============================================================
        // 11. REALTIME SIGNALING & SYNC (PRESENSI, MOOD, ANGKAT TANGAN, CHAT)
        // ============================================================
        let isSyncing = false;
        let knownHandIds = new Set();
        let lastRenderedChatIds = new Set();
        let audioCtx = null;

        function playNotificationChime() {
            try {
                if (!audioCtx) {
                    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sine';
                osc.connect(gain);
                gain.connect(audioCtx.destination);

                const now = audioCtx.currentTime;
                osc.frequency.setValueAtTime(587.33, now); // D5
                osc.frequency.setValueAtTime(880.00, now + 0.12); // A5

                gain.gain.setValueAtTime(0.001, now);
                gain.gain.exponentialRampToValueAtTime(0.25, now + 0.05);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.38);

                osc.start(now);
                osc.stop(now + 0.38);
            } catch (e) {
                console.warn("Chime error:", e);
            }
        }

        async function syncMeetingState(extraPayload = {}) {
            if (isSyncing) return;
            isSyncing = true;
            try {
                const payload = Object.assign({ action: 'sync' }, extraPayload);
                const res = await fetch("{{ route('guru.lms.meeting.sync', $course->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                if (!res.ok) throw new Error("Sync HTTP " + res.status);
                const data = await res.json();
                handleMeetingSyncData(data);
            } catch (err) {
                console.warn("Sync warning:", err);
            } finally {
                isSyncing = false;
            }
        }

        function handleMeetingSyncData(data) {
            if (!data) return;

            // 1. Peserta & Count
            const attendees = data.attendees || [];
            const studentAttendees = attendees.filter(a => a.role === 'siswa');
            const total = studentAttendees.length;

            const hCount = document.getElementById('header-attendee-count');
            if (hCount) hCount.textContent = total;
            const tabCount = document.getElementById('attendee-tab-count');
            if (tabCount) tabCount.textContent = total;
            const qCount = document.getElementById('quick-attendee-badge');
            if (qCount) qCount.textContent = total;

            // 2. Update Attendees Map (UID -> Nama & Data)
            attendees.forEach(student => {
                const sid = String(student.user_id);
                attendeesMap[sid] = student.name;

                // Update name tag pada tile remote video & meja kelas
                const nameTag = document.getElementById(`tile-name-${sid}`);
                if (nameTag) {
                    nameTag.textContent = student.name.split(' ')[0];
                }
                const seatNameTag = document.getElementById(`seat-name-${sid}`);
                if (seatNameTag) {
                    seatNameTag.textContent = student.name.split(' ')[0];
                }

                // Update Suasana Hati (Mood) badge pada tile & meja
                const moodTileTag = document.getElementById(`tile-mood-${sid}`);
                if (moodTileTag && student.mood_icon) {
                    moodTileTag.innerHTML = `<span>${student.mood_icon}</span> <span>${student.mood_label}</span>`;
                    moodTileTag.style.backgroundColor = student.mood_bg || '#ffffff';
                    moodTileTag.style.color = student.mood_color || '#121316';
                }
                const seatMoodTag = document.getElementById(`seat-mood-${sid}`);
                if (seatMoodTag && student.mood_icon) {
                    seatMoodTag.innerHTML = `<span>${student.mood_icon}</span> <span>${student.mood_label}</span>`;
                    seatMoodTag.style.backgroundColor = student.mood_bg || '#ffffff';
                    seatMoodTag.style.color = student.mood_color || '#121316';
                }

                // Update Angkat Tangan badge pada tile & meja
                const handTileTag = document.getElementById(`tile-hand-${sid}`);
                if (handTileTag) {
                    if (student.raise_hand) {
                        handTileTag.classList.remove('hidden');
                    } else {
                        handTileTag.classList.add('hidden');
                    }
                }
                const seatHandTag = document.getElementById(`seat-hand-${sid}`);
                if (seatHandTag) {
                    if (student.raise_hand) {
                        seatHandTag.classList.remove('hidden');
                    } else {
                        seatHandTag.classList.add('hidden');
                    }
                }
            });

            // 3. Render Daftar Peserta di Tab Siswa
            const listEl = document.getElementById('attendees-list');
            if (listEl) {
                if (studentAttendees.length === 0) {
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
                } else {
                    const avatarGradients = [
                        'linear-gradient(135deg, #ec4899, #f43f5e)',
                        'linear-gradient(135deg, #06b6d4, #3b82f6)',
                        'linear-gradient(135deg, #10b981, #14b8a6)',
                        'linear-gradient(135deg, #8b5cf6, #a855f7)',
                        'linear-gradient(135deg, #f59e0b, #f97316)'
                    ];
                    let html = '';
                    studentAttendees.forEach((student, idx) => {
                        const grad = avatarGradients[idx % avatarGradients.length];
                        const initials = student.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
                        const isHand = student.raise_hand;
                        html += `
                            <div class="p-3 rounded-2xl bg-white text-xs transition-all space-y-2" style="border: 2px solid #121316; box-shadow: 3px 3px 0px ${isHand ? '#ef4444' : '#121316'};">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center text-[11px] font-black text-white flex-shrink-0" style="background: ${grad}; border: 1.5px solid #121316;">
                                            ${initials}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-black truncate leading-snug" style="color: #121316;">${student.name}</p>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded-md text-[9px] font-bold" style="background-color: ${student.mood_bg || '#f1f5f9'}; color: ${student.mood_color || '#121316'}; border: 1px solid #121316;">
                                                    ${student.mood_icon || '🔥'} ${student.mood_label || 'Semangat'}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        ${isHand ? `
                                            <button onclick="promoteStudentToSpeakerPodium('${student.user_id}'); dismissHand(${student.user_id});" type="button" class="btn-tactile-yellow px-2 py-1 rounded-lg text-[10px] font-mono-code font-black flex items-center gap-1" title="Panggil ke mimbar bicara">
                                                <i class="fa-solid fa-microphone-lines text-xs"></i> Panggil
                                            </button>
                                        ` : `
                                            <button onclick="promoteStudentToSpeakerPodium('${student.user_id}')" type="button" class="p-1.5 rounded-lg text-xs bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-700" title="Pindahkan ke mimbar depan">
                                                <i class="fa-solid fa-microphone text-[10px]"></i>
                                            </button>
                                        `}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    listEl.innerHTML = html;
                }
            }

            // 4. Update Section Siswa Angkat Tangan (Hands Raised Alert)
            const hands = data.raised_hands || [];
            const handsSection = document.getElementById('hands-raised-section');
            const handsCountEl = document.getElementById('hands-raised-count');
            const handsListEl = document.getElementById('hands-raised-list');

            let newHandDetected = false;
            hands.forEach(h => {
                if (!knownHandIds.has(h.user_id)) {
                    newHandDetected = true;
                }
            });
            if (newHandDetected && hands.length > 0) {
                playNotificationChime();
            }
            knownHandIds = new Set(hands.map(h => h.user_id));

            if (handsSection) {
                if (hands.length > 0) {
                    handsSection.classList.remove('hidden');
                    if (handsCountEl) handsCountEl.textContent = hands.length;
                    if (handsListEl) {
                        handsListEl.innerHTML = hands.map(h => `
                            <div class="flex items-center justify-between p-2 rounded-xl bg-white border border-red-300 shadow-xs">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="text-base animate-bounce">✋</span>
                                    <div class="min-w-0">
                                        <div class="text-xs font-black truncate text-red-900">${h.name}</div>
                                        <div class="text-[9px] font-mono-code text-red-600 font-bold">Ingin bertanya / berbicara</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    <button onclick="promoteStudentToSpeakerPodium('${h.user_id}'); dismissHand(${h.user_id});" type="button" class="btn-tactile-yellow px-2 py-0.5 rounded-lg text-[10px] font-mono-code font-black flex items-center gap-1">
                                        <i class="fa-solid fa-microphone-lines text-xs"></i> Panggil
                                    </button>
                                    <button onclick="dismissHand(${h.user_id})" type="button" class="px-2 py-0.5 rounded-lg text-[10px] font-mono-code font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300">
                                        Turunkan
                                    </button>
                                </div>
                            </div>
                        `).join('');
                    }
                } else {
                    handsSection.classList.add('hidden');
                }
            }

            // 5. Update Mood Meter Badges (Rekap Emosi / Suasana Hati Kelas)
            const moodMeterEl = document.getElementById('mood-meter-badges');
            if (moodMeterEl) {
                const summary = data.mood_summary || {};
                const keys = Object.keys(summary);
                if (keys.length === 0) {
                    moodMeterEl.innerHTML = `<span class="text-[10px] font-mono-code font-bold text-slate-400">Belum ada respon suasana hati siswa.</span>`;
                } else {
                    const availableMoods = data.available_moods || {};
                    let moodHtml = '';
                    for (const [label, count] of Object.entries(summary)) {
                        let foundMeta = null;
                        for (const k in availableMoods) {
                            if (availableMoods[k].label === label) {
                                foundMeta = availableMoods[k];
                                break;
                            }
                        }
                        const icon = foundMeta ? foundMeta.icon : '✨';
                        const bg = foundMeta ? foundMeta.bg : '#f1f5f9';
                        const color = foundMeta ? foundMeta.color : '#121316';
                        moodHtml += `
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-xl text-[10px] font-mono-code font-bold shadow-xs" style="background-color: ${bg}; color: ${color}; border: 1.5px solid #121316;">
                                <span>${icon}</span>
                                <span>${label}</span>
                                <span class="ml-0.5 px-1 rounded-full bg-white text-black font-black text-[9px] border border-black">${count}</span>
                            </span>
                        `;
                    }
                    moodMeterEl.innerHTML = moodHtml;
                }
            }

            // 6. Update Pilihan Tujuan Chat (Dropdown Recipient)
            const chatSelect = document.getElementById('chat-recipient-select');
            if (chatSelect) {
                const currentVal = chatSelect.value;
                let selectOptions = `<option value="all">📢 Semua Orang (Publik)</option>`;
                studentAttendees.forEach(s => {
                    selectOptions += `<option value="${s.user_id}">👤 ${s.name} (Siswa)</option>`;
                });
                chatSelect.innerHTML = selectOptions;
                if (currentVal && chatSelect.querySelector(`option[value="${currentVal}"]`)) {
                    chatSelect.value = currentVal;
                }
            }

            // 7. Render Chat Messages
            const chats = data.chats || [];
            const chatContainer = document.getElementById('chat-messages-container');
            const chatEmpty = document.getElementById('chat-empty-state');

            if (chatContainer) {
                if (chats.length === 0) {
                    if (chatEmpty) chatEmpty.classList.remove('hidden');
                } else {
                    if (chatEmpty) chatEmpty.classList.add('hidden');
                    
                    let newIncomingMsg = false;
                    chats.forEach(c => {
                        if (!lastRenderedChatIds.has(c.id)) {
                            newIncomingMsg = true;
                            lastRenderedChatIds.add(c.id);
                        }
                    });

                    if (newIncomingMsg) {
                        let chatHtml = '';
                        chats.forEach(c => {
                            const isMe = (c.sender_id === AGORA_UID);
                            const isPrivate = c.is_private;
                            const bubbleBg = isMe ? '#fef08a' : (isPrivate ? '#f3e8ff' : '#ffffff');
                            const borderCol = isPrivate ? '#9333ea' : '#121316';
                            const badgePrivate = isPrivate ? `<span class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded text-[8px] font-mono-code font-black bg-purple-600 text-white"><i class="fa-solid fa-lock text-[7px]"></i> PRIBADI: ${c.recipient_name}</span>` : '';

                            chatHtml += `
                                <div class="flex flex-col ${isMe ? 'items-end' : 'items-start'}">
                                    <div class="flex items-center gap-1.5 mb-0.5 text-[9px] font-mono-code font-bold text-slate-500">
                                        <span class="${isMe ? 'text-[#ff3823] font-black' : 'text-[#121316]'}">${isMe ? 'Saya' : c.sender_name}</span>
                                        ${badgePrivate}
                                        <span>${c.time_formatted || ''}</span>
                                    </div>
                                    <div class="px-3 py-2 rounded-2xl max-w-[85%] text-xs font-mono-code break-words" style="background-color: ${bubbleBg}; color: #121316; border: 1.5px solid ${borderCol}; box-shadow: 2px 2px 0px ${borderCol};">
                                        ${c.text}
                                    </div>
                                </div>
                            `;
                        });
                        chatContainer.innerHTML = chatHtml;
                        chatContainer.scrollTop = chatContainer.scrollHeight;

                        if (!isChatActiveTab) {
                            unreadChatCount++;
                            const badge1 = document.getElementById('chat-tab-badge');
                            const badge2 = document.getElementById('quick-chat-badge');
                            if (badge1) {
                                badge1.textContent = unreadChatCount;
                                badge1.classList.remove('hidden');
                            }
                            if (badge2) {
                                badge2.textContent = unreadChatCount;
                                badge2.classList.remove('hidden');
                            }
                        }
                    }
                }
            }

            // 8. Render Polling Kilat
            renderGuruPoll(data.poll);

            // 9. Render Q&A Queue
            renderGuruQa(data.qa_list);

            // 10. Render Kuis Live Kahoot-style (Fase 4)
            renderGuruQuiz(data.quiz);
        }

        async function sendChatMessage(customText = null) {
            const input = document.getElementById('chat-input-text');
            const select = document.getElementById('chat-recipient-select');
            const text = (customText !== null ? customText : (input ? input.value : '')).trim();
            if (!text) return;

            if (customText === null && input) {
                input.value = '';
            }

            const recipientId = select ? select.value : 'all';
            let recipientName = 'Semua Orang';
            if (select && select.options[select.selectedIndex]) {
                recipientName = select.options[select.selectedIndex].text.replace(/^[^\w]+/, '').trim();
            }

            await syncMeetingState({
                action: 'send_chat',
                text: text,
                recipient_id: recipientId,
                recipient_name: recipientName
            });
        }

        function sendQuickReaction(emoji) {
            sendChatMessage(emoji);
        }

        async function dismissHand(targetUserId) {
            await syncMeetingState({
                action: 'dismiss_hand',
                target_user_id: targetUserId
            });
        }

        // ============================================================
        // 11B. FITUR FASE 2: POLLING KILAT & TANYA JAWAB (Q&A)
        // ============================================================
        function setPollPreset(type) {
            const qInput = document.getElementById('poll-input-question');
            if (!qInput) return;

            if (type === 'paham') {
                qInput.value = 'Apakah konsep materi ini sudah dipahami dengan jelas?';
                renderPollInputs(['Paham dan Jelas', 'Masih Bingung', 'Perlu Contoh Soal']);
            } else if (type === 'setuju') {
                qInput.value = 'Apakah Anda setuju dengan kesimpulan materi ini?';
                renderPollInputs(['Ya, Sangat Setuju', 'Tidak Setuju']);
            } else if (type === 'pilgan') {
                qInput.value = 'Pilihlah jawaban yang paling tepat dari soal berikut:';
                renderPollInputs(['Pilihan A', 'Pilihan B', 'Pilihan C', 'Pilihan D']);
            }
        }

        function renderPollInputs(options) {
            const c = document.getElementById('poll-options-inputs');
            if (!c) return;
            c.innerHTML = '';
            options.forEach((opt, idx) => {
                c.appendChild(createPollInputRow(idx + 1, opt));
            });
        }

        function createPollInputRow(num, val = '') {
            const div = document.createElement('div');
            div.className = 'flex items-center gap-2 option-row';
            div.innerHTML = `
                <span class="w-6 h-6 rounded-md bg-slate-100 border border-black flex items-center justify-center text-xs font-mono-code font-black flex-shrink-0">${num}</span>
                <input type="text" class="poll-opt-input flex-1 px-3 py-1.5 text-xs font-mono-code rounded-xl border border-black focus:outline-none" value="${val}" placeholder="Pilihan jawaban...">
                <button type="button" onclick="this.closest('.option-row').remove(); renumberPollOptions();" class="p-1 text-slate-400 hover:text-red-500 text-xs">
                    <i class="fa-solid fa-times"></i>
                </button>
            `;
            return div;
        }

        function addPollOptionField() {
            const c = document.getElementById('poll-options-inputs');
            if (!c) return;
            const count = c.querySelectorAll('.option-row').length + 1;
            c.appendChild(createPollInputRow(count, ''));
        }

        function renumberPollOptions() {
            const c = document.getElementById('poll-options-inputs');
            if (!c) return;
            c.querySelectorAll('.option-row').forEach((row, i) => {
                const s = row.querySelector('span');
                if (s) s.textContent = i + 1;
            });
        }

        async function submitCreatePoll() {
            const qInput = document.getElementById('poll-input-question');
            const question = qInput ? qInput.value.trim() : '';
            if (!question) {
                alert('Silakan tulis pertanyaan polling terlebih dahulu.');
                return;
            }

            const optInputs = document.querySelectorAll('.poll-opt-input');
            const options = [];
            optInputs.forEach(inp => {
                const v = inp.value.trim();
                if (v) options.push(v);
            });

            if (options.length < 2) {
                alert('Polling minimal membutuhkan 2 pilihan jawaban.');
                return;
            }

            await syncMeetingState({
                action: 'create_poll',
                question: question,
                options: options
            });

            switchTab('polling');
        }

        async function endCurrentPoll() {
            if (!confirm('Apakah Anda yakin ingin menutup dan mengunci polling ini?')) return;
            await syncMeetingState({ action: 'end_poll' });
        }

        async function clearCurrentPoll() {
            if (!confirm('Hapus hasil polling dan buat baru?')) return;
            await syncMeetingState({ action: 'clear_poll' });
        }

        function renderGuruPoll(poll) {
            const activeCard = document.getElementById('guru-active-poll-card');
            const createCard = document.getElementById('guru-create-poll-card');
            const headerBadge = document.getElementById('poll-header-status-badge');
            if (!activeCard || !createCard) return;

            if (!poll) {
                activeCard.classList.add('hidden');
                createCard.classList.remove('hidden');
                if (headerBadge) {
                    headerBadge.innerHTML = '<span class="px-2 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-slate-100 text-slate-600 border border-slate-300">Siap Buat</span>';
                }
                return;
            }

            // Ada polling aktif atau selesai
            activeCard.classList.remove('hidden');
            createCard.classList.add('hidden');

            const qText = document.getElementById('guru-poll-question-text');
            if (qText) qText.textContent = poll.question;

            const liveInd = document.getElementById('guru-poll-live-indicator');
            const endBtn = document.getElementById('btn-end-poll');
            if (poll.is_active) {
                if (liveInd) {
                    liveInd.className = 'px-2 py-0.5 rounded-md text-[10px] font-mono-code font-black flex items-center gap-1.5 bg-amber-100 text-amber-900 border border-amber-300';
                    liveInd.innerHTML = '<span class="w-2 h-2 rounded-full animate-ping bg-amber-500"></span> POLLING BERLANGSUNG';
                }
                if (headerBadge) {
                    headerBadge.innerHTML = '<span class="px-2 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-amber-100 text-amber-900 border border-amber-300 animate-pulse">● Aktif</span>';
                }
                if (endBtn) endBtn.classList.remove('hidden');
            } else {
                if (liveInd) {
                    liveInd.className = 'px-2 py-0.5 rounded-md text-[10px] font-mono-code font-black bg-slate-200 text-slate-700 border border-slate-300';
                    liveInd.innerHTML = '<i class="fa-solid fa-lock text-[9px]"></i> POLLING SELESAI';
                }
                if (headerBadge) {
                    headerBadge.innerHTML = '<span class="px-2 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-slate-100 text-slate-600 border border-slate-300">Selesai</span>';
                }
                if (endBtn) endBtn.classList.add('hidden');
            }

            const totalBadge = document.getElementById('guru-poll-total-badge');
            if (totalBadge) totalBadge.textContent = `${poll.total_votes} Suara Masuk`;

            // Render Options
            const container = document.getElementById('guru-poll-options-container');
            if (container) {
                let html = '';
                const colors = ['#ea580c', '#0284c7', '#16a34a', '#9333ea', '#ca8a04', '#dc2626'];
                poll.options.forEach((opt, idx) => {
                    const count = poll.counts[idx] || 0;
                    const pct = poll.percentages[idx] || 0;
                    const color = colors[idx % colors.length];

                    html += `
                        <div class="p-2.5 rounded-xl border border-slate-300 bg-slate-50 space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-mono-code">
                                <div class="flex items-center gap-2 font-black text-slate-900">
                                    <span class="w-5 h-5 rounded flex items-center justify-center text-[10px] text-white font-bold" style="background-color: ${color};">${idx + 1}</span>
                                    <span>${opt}</span>
                                </div>
                                <div class="font-extrabold text-slate-800">
                                    <span>${count}</span> <span class="text-[10px] text-slate-500">(${pct}%)</span>
                                </div>
                            </div>
                            <div class="w-full bg-slate-200 h-2.5 rounded-full overflow-hidden border border-slate-300">
                                <div class="h-full rounded-full transition-all duration-500" style="width: ${pct}%; background-color: ${color};"></div>
                            </div>
                        </div>
                    `;
                });
                container.innerHTML = html;
            }
        }

        // Q&A Functions for Guru
        async function updateQaStatus(questionId, status) {
            await syncMeetingState({
                action: 'update_qa_status',
                question_id: questionId,
                status: status
            });
        }

        async function upvoteQa(questionId) {
            await syncMeetingState({
                action: 'upvote_qa',
                question_id: questionId
            });
        }

        function renderGuruQa(qaList) {
            const container = document.getElementById('guru-qa-list-container');
            const emptyEl = document.getElementById('guru-qa-empty');
            const countBadge = document.getElementById('guru-qa-count-badge');
            if (!container) return;

            const list = qaList || [];
            if (countBadge) countBadge.textContent = `${list.length} Pertanyaan`;

            if (list.length === 0) {
                if (emptyEl) emptyEl.classList.remove('hidden');
                container.querySelectorAll('.qa-item-card').forEach(el => el.remove());
                return;
            }

            if (emptyEl) emptyEl.classList.add('hidden');

            let html = '';
            list.forEach(item => {
                const isAnswering = (item.status === 'answering');
                const isAnswered = (item.status === 'answered');

                let borderStyle = 'border: 2px solid #121316; box-shadow: 2px 2px 0px #121316;';
                let cardBg = '#ffffff';
                let statusBadge = '';

                if (isAnswering) {
                    borderStyle = 'border: 2.5px solid #d97706; box-shadow: 4px 4px 0px #f59e0b;';
                    cardBg = '#fffbeb';
                    statusBadge = '<span class="px-2 py-0.5 rounded-full text-[9px] font-mono-code font-black bg-amber-400 text-black border border-black animate-pulse flex items-center gap-1"><i class="fa-solid fa-microphone-lines text-xs"></i> SEDANG DIBAHAS DI MIMBAR</span>';
                } else if (isAnswered) {
                    cardBg = '#f0fdf4';
                    statusBadge = '<span class="px-2 py-0.5 rounded-full text-[9px] font-mono-code font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">✅ Sudah Terjawab</span>';
                } else {
                    statusBadge = '<span class="px-2 py-0.5 rounded-full text-[9px] font-mono-code font-bold bg-slate-100 text-slate-600 border border-slate-300">⏳ Menunggu</span>';
                }

                html += `
                    <div class="qa-item-card p-3 rounded-2xl transition-all space-y-2 relative" style="background-color: ${cardBg}; ${borderStyle}">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-md bg-orange-500 text-white flex items-center justify-center text-[10px] font-black border border-black shadow-xs">
                                    ${item.user_name.charAt(0).toUpperCase()}
                                </span>
                                <div>
                                    <span class="font-black text-slate-900">${item.user_name}</span>
                                    <span class="text-[9px] font-mono-code text-slate-400 ml-1">${item.time_formatted}</span>
                                </div>
                            </div>
                            <div>${statusBadge}</div>
                        </div>

                        <p class="text-xs font-mono-code font-bold text-slate-800 leading-snug pl-1">
                            "${item.question}"
                        </p>

                        <div class="flex items-center justify-between pt-1 border-t border-slate-200">
                            <button onclick="upvoteQa('${item.id}')" type="button" class="btn-tactile-white px-2 py-1 rounded-lg text-[10px] font-mono-code font-black flex items-center gap-1 ${item.has_upvoted ? 'text-orange-600 border-orange-500' : 'text-slate-600'}" title="Upvote pertanyaan ini">
                                <i class="fa-solid fa-thumbs-up"></i>
                                <span>${item.upvote_count} Upvote</span>
                            </button>

                            <div class="flex items-center gap-1.5">
                                ${!isAnswering && !isAnswered ? `
                                    <button onclick="updateQaStatus('${item.id}', 'answering')" type="button" class="btn-tactile-white px-2 py-1 rounded-lg text-[10px] font-mono-code font-black text-amber-700 border-amber-400 flex items-center gap-1" title="Bahas pertanyaan ini di mimbar video">
                                        <i class="fa-solid fa-microphone"></i> Bahas
                                    </button>
                                ` : ''}
                                ${isAnswering ? `
                                    <button onclick="updateQaStatus('${item.id}', 'answered')" type="button" class="btn-tactile-white px-2 py-1 rounded-lg text-[10px] font-mono-code font-black text-emerald-700 border-emerald-400 flex items-center gap-1" title="Tandai sudah selesai dijawab">
                                        <i class="fa-solid fa-check"></i> Selesai
                                    </button>
                                ` : ''}
                                ${isAnswered ? `
                                    <button onclick="updateQaStatus('${item.id}', 'pending')" type="button" class="btn-tactile-white px-2 py-1 rounded-lg text-[9px] font-mono-code text-slate-500">
                                        Reset
                                    </button>
                                ` : ''}
                                <button onclick="updateQaStatus('${item.id}', 'delete')" type="button" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 text-xs" title="Hapus pertanyaan">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // ============================================================
        // 11C. FITUR FASE 4: KUIS LIVE KAHOOT-STYLE (SUPER CLASSROOM)
        // ============================================================
        let currentGuruQuizLauncherMode = 'lms';
        let currentGuruQuizData = null;
        let guruQuizTimerInterval = null;
        let guruQuizRemaining = 0;

        const KAHOOT_SHAPES = ['▲', '◆', '●', '■'];
        const KAHOOT_COLORS = ['#ef4444', '#3b82f6', '#f59e0b', '#10b981'];
        const KAHOOT_LETTERS = ['A', 'B', 'C', 'D'];

        function setQuizLauncherMode(mode) {
            currentGuruQuizLauncherMode = mode;
            const btnLms = document.getElementById('btn-mode-lms-quiz');
            const btnCustom = document.getElementById('btn-mode-custom-quiz');
            const modeLms = document.getElementById('quiz-mode-lms');
            const modeCustom = document.getElementById('quiz-mode-custom');

            if (mode === 'lms') {
                if (btnLms) { btnLms.className = 'py-1.5 text-xs font-mono-code font-black rounded-lg bg-black text-amber-300 transition'; }
                if (btnCustom) { btnCustom.className = 'py-1.5 text-xs font-mono-code font-bold rounded-lg text-slate-700 transition'; }
                if (modeLms) modeLms.classList.remove('hidden');
                if (modeCustom) modeCustom.classList.add('hidden');
            } else {
                if (btnCustom) { btnCustom.className = 'py-1.5 text-xs font-mono-code font-black rounded-lg bg-black text-amber-300 transition'; }
                if (btnLms) { btnLms.className = 'py-1.5 text-xs font-mono-code font-bold rounded-lg text-slate-700 transition'; }
                if (modeCustom) modeCustom.classList.remove('hidden');
                if (modeLms) modeLms.classList.add('hidden');
            }
        }

        async function startGuruLiveQuiz() {
            if (currentGuruQuizLauncherMode === 'lms') {
                const quizSelect = document.getElementById('guru-quiz-select');
                const quizId = quizSelect ? quizSelect.value : '';
                if (!quizId) {
                    alert('Silakan pilih salah satu kuis yang ada di LMS atau gunakan tab "Soal Cepat KBM"!');
                    return;
                }
                const timeLimit = parseInt(document.getElementById('guru-quiz-lms-time')?.value || '20');

                await syncMeetingState({
                    action: 'start_live_quiz',
                    quiz_id: quizId,
                    time_limit: timeLimit
                });
            } else {
                const title = (document.getElementById('guru-custom-quiz-title')?.value || 'Kuis Kilat KBM').trim();
                const questionText = (document.getElementById('guru-custom-question')?.value || '').trim();
                if (!questionText) {
                    alert('Pertanyaan kuis kilat tidak boleh kosong!');
                    return;
                }
                const opts = [
                    (document.getElementById('ans_opt_0')?.value || '').trim(),
                    (document.getElementById('ans_opt_1')?.value || '').trim(),
                    (document.getElementById('ans_opt_2')?.value || '').trim(),
                    (document.getElementById('ans_opt_3')?.value || '').trim()
                ].filter(o => o !== '');

                if (opts.length < 2) {
                    alert('Minimal masukkan 2 pilihan jawaban!');
                    return;
                }

                let correctAns = 0;
                const checkedRadio = document.querySelector('input[name="custom_correct_ans"]:checked');
                if (checkedRadio) {
                    correctAns = parseInt(checkedRadio.value) || 0;
                }
                const timeLimit = parseInt(document.getElementById('guru-custom-quiz-time')?.value || '20');

                await syncMeetingState({
                    action: 'start_live_quiz',
                    title: title,
                    time_limit: timeLimit,
                    questions: [{
                        question: questionText,
                        options: opts,
                        correct_answer: correctAns,
                        time_limit: timeLimit
                    }]
                });
            }

            switchTab('kuis');
        }

        async function showGuruQuizScoreboard() {
            await syncMeetingState({
                action: 'show_quiz_scoreboard'
            });
        }

        async function guruNextQuizQuestion() {
            if (!currentGuruQuizData) return;
            const nextIdx = (currentGuruQuizData.current_question_index || 0) + 1;
            if (nextIdx < (currentGuruQuizData.total_questions || 1)) {
                await syncMeetingState({
                    action: 'next_quiz_question',
                    question_index: nextIdx
                });
            } else {
                await endGuruQuizToPodium();
            }
        }

        async function endGuruQuizToPodium() {
            await syncMeetingState({
                action: 'end_live_quiz'
            });
        }

        async function cancelGuruQuiz() {
            if (confirm('Hentikan dan bersihkan kuis live ini?')) {
                await syncMeetingState({
                    action: 'clear_live_quiz'
                });
            }
        }

        async function clearGuruLiveQuiz() {
            await syncMeetingState({
                action: 'clear_live_quiz'
            });
        }

        function renderGuruQuiz(quiz) {
            currentGuruQuizData = quiz;

            const vLauncher = document.getElementById('quiz-view-launcher');
            const vQuestion = document.getElementById('quiz-view-question');
            const vScoreboard = document.getElementById('quiz-view-scoreboard');
            const vPodium = document.getElementById('quiz-view-podium');
            const statusBadge = document.getElementById('guru-quiz-status-badge');
            const tabBadge = document.getElementById('quiz-tab-badge');

            if (!quiz || !quiz.status || quiz.status === 'standby') {
                if (statusBadge) {
                    statusBadge.textContent = 'Standby';
                    statusBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-slate-100 text-slate-700 border border-slate-300';
                }
                if (tabBadge) tabBadge.classList.add('hidden');
                if (vLauncher) vLauncher.classList.remove('hidden');
                if (vQuestion) vQuestion.classList.add('hidden');
                if (vScoreboard) vScoreboard.classList.add('hidden');
                if (vPodium) vPodium.classList.add('hidden');
                if (guruQuizTimerInterval) { clearInterval(guruQuizTimerInterval); guruQuizTimerInterval = null; }
                return;
            }

            // Kuis Sedang Berjalan (Question)
            if (quiz.status === 'question') {
                if (statusBadge) {
                    statusBadge.textContent = 'LIVE // SOAL AKTIF';
                    statusBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-rose-500 text-white animate-pulse border border-rose-700';
                }
                if (tabBadge) tabBadge.classList.remove('hidden');
                if (vLauncher) vLauncher.classList.add('hidden');
                if (vQuestion) vQuestion.classList.remove('hidden');
                if (vScoreboard) vScoreboard.classList.add('hidden');
                if (vPodium) vPodium.classList.add('hidden');

                const curQ = quiz.current_question || {};
                const qIdx = (quiz.current_question_index || 0) + 1;
                const totalQ = quiz.total_questions || 1;

                const qBadge = document.getElementById('guru-q-badge');
                if (qBadge) qBadge.textContent = `Soal ${qIdx} dari ${totalQ}`;

                const qText = document.getElementById('guru-q-text');
                if (qText) qText.textContent = curQ.question || 'Pertanyaan Kuis';

                // Timer Countdown
                const timeLimit = curQ.time_limit || 20;
                const elapsed = quiz.time_elapsed || 0;
                guruQuizRemaining = Math.max(0, timeLimit - elapsed);
                const timerEl = document.getElementById('guru-q-timer');
                if (timerEl) timerEl.textContent = `${guruQuizRemaining}s`;

                if (!guruQuizTimerInterval) {
                    guruQuizTimerInterval = setInterval(() => {
                        if (guruQuizRemaining > 0) {
                            guruQuizRemaining--;
                            const tEl = document.getElementById('guru-q-timer');
                            if (tEl) tEl.textContent = `${guruQuizRemaining}s`;
                        } else {
                            clearInterval(guruQuizTimerInterval);
                            guruQuizTimerInterval = null;
                        }
                    }, 1000);
                }

                // Answers counter
                const ansCount = quiz.answers_count || 0;
                const totPlayers = quiz.total_players || 0;
                const countEl = document.getElementById('guru-q-answers-count');
                if (countEl) countEl.textContent = `${ansCount} / ${totPlayers} Siswa Menjawab`;

                const pct = totPlayers > 0 ? Math.min(100, Math.round((ansCount / totPlayers) * 100)) : 0;
                const progBar = document.getElementById('guru-q-progress-bar');
                if (progBar) progBar.style.width = `${pct}%`;

                // Options Grid (4 Kahoot Buttons)
                const optsGrid = document.getElementById('guru-q-options-grid');
                if (optsGrid && curQ.options) {
                    let optsHtml = '';
                    curQ.options.forEach((optText, oIdx) => {
                        const shape = KAHOOT_SHAPES[oIdx % 4];
                        const color = KAHOOT_COLORS[oIdx % 4];
                        optsHtml += `
                            <div class="p-2.5 rounded-xl text-white font-mono-code font-bold text-xs flex items-center gap-2 border-2 border-black" style="background-color: ${color}; box-shadow: 2px 2px 0px #121316;">
                                <span class="w-6 h-6 rounded-md bg-white/25 flex items-center justify-center font-black text-xs">${shape}</span>
                                <span class="truncate">${optText}</span>
                            </div>
                        `;
                    });
                    optsGrid.innerHTML = optsHtml;
                }
                return;
            }

            // Kuis Status Scoreboard
            if (quiz.status === 'scoreboard') {
                if (guruQuizTimerInterval) { clearInterval(guruQuizTimerInterval); guruQuizTimerInterval = null; }
                if (statusBadge) {
                    statusBadge.textContent = 'PAPAN SKOR';
                    statusBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-indigo-500 text-white border border-indigo-700';
                }
                if (tabBadge) tabBadge.classList.add('hidden');
                if (vLauncher) vLauncher.classList.add('hidden');
                if (vQuestion) vQuestion.classList.add('hidden');
                if (vScoreboard) vScoreboard.classList.remove('hidden');
                if (vPodium) vPodium.classList.add('hidden');

                const curQ = quiz.current_question || {};
                const qIdx = (quiz.current_question_index || 0) + 1;
                const totalQ = quiz.total_questions || 1;

                const scoreBadge = document.getElementById('guru-score-q-badge');
                if (scoreBadge) scoreBadge.textContent = `Soal ${qIdx} dari ${totalQ}`;

                // Distribusi Pilihan Siswa
                const distContainer = document.getElementById('guru-score-distribution');
                if (distContainer && curQ.options) {
                    let distHtml = '';
                    const dist = quiz.distribution || {};
                    const totalAns = Math.max(1, quiz.answers_count || 1);

                    curQ.options.forEach((optText, oIdx) => {
                        const count = dist[oIdx] || 0;
                        const pct = Math.round((count / totalAns) * 100);
                        const shape = KAHOOT_SHAPES[oIdx % 4];
                        const color = KAHOOT_COLORS[oIdx % 4];
                        const isCorrect = (curQ.correct_answer === oIdx);

                        distHtml += `
                            <div class="p-2 rounded-xl border ${isCorrect ? 'bg-emerald-50 border-emerald-500 shadow-xs ring-2 ring-emerald-400' : 'bg-white border-slate-200'}">
                                <div class="flex items-center justify-between text-xs font-mono-code mb-1">
                                    <div class="flex items-center gap-1.5 truncate">
                                        <span class="w-5 h-5 rounded flex items-center justify-center text-white text-[10px] font-black" style="background-color: ${color};">${shape}</span>
                                        <span class="font-bold truncate text-slate-900">${optText}</span>
                                        ${isCorrect ? '<span class="text-emerald-700 font-black text-[10px] ml-1">✓ BENAR</span>' : ''}
                                    </div>
                                    <span class="font-black text-slate-700 ml-2">${count} siswa (${pct}%)</span>
                                </div>
                                <div class="w-full h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full ${isCorrect ? 'bg-emerald-500' : 'bg-slate-400'}" style="width: ${pct}%;"></div>
                                </div>
                            </div>
                        `;
                    });
                    distContainer.innerHTML = distHtml;
                }

                // Leaderboard Top 5 Siswa
                const lbContainer = document.getElementById('guru-score-leaderboard');
                if (lbContainer) {
                    const topList = quiz.top_players || [];
                    if (topList.length === 0) {
                        lbContainer.innerHTML = `<p class="text-[10px] font-mono-code text-slate-400 text-center py-2">Belum ada skor yang dicatat.</p>`;
                    } else {
                        let lbHtml = '';
                        topList.forEach((pl, rIdx) => {
                            const medal = rIdx === 0 ? '🥇' : (rIdx === 1 ? '🥈' : (rIdx === 2 ? '🥉' : `#${rIdx + 1}`));
                            lbHtml += `
                                <div class="flex items-center justify-between p-2 rounded-xl bg-white border border-slate-200 shadow-xs font-mono-code text-xs">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="text-sm font-black w-6 text-center">${medal}</span>
                                        <span class="font-extrabold truncate text-slate-900">${pl.name}</span>
                                    </div>
                                    <span class="font-black text-pink-600">${(pl.score || 0).toLocaleString()} pts</span>
                                </div>
                            `;
                        });
                        lbContainer.innerHTML = lbHtml;
                    }
                }

                // Tombol Next Question vs Buka Podium
                const nextBtn = document.getElementById('btn-guru-next-q');
                if (nextBtn) {
                    if (qIdx < totalQ) {
                        nextBtn.innerHTML = `<span>Soal Berikutnya (${qIdx + 1}/${totalQ})</span> <i class="fa-solid fa-arrow-right ml-1"></i>`;
                    } else {
                        nextBtn.innerHTML = `<span>Selesai &amp; Buka Podium</span> <i class="fa-solid fa-trophy ml-1"></i>`;
                    }
                }
                return;
            }

            // Kuis Status Selesai (Podium)
            if (quiz.status === 'finished') {
                if (guruQuizTimerInterval) { clearInterval(guruQuizTimerInterval); guruQuizTimerInterval = null; }
                if (statusBadge) {
                    statusBadge.textContent = 'SELESAI (PODIUM)';
                    statusBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-amber-400 text-amber-950 border border-amber-600';
                }
                if (tabBadge) tabBadge.classList.add('hidden');
                if (vLauncher) vLauncher.classList.add('hidden');
                if (vQuestion) vQuestion.classList.add('hidden');
                if (vScoreboard) vScoreboard.classList.add('hidden');
                if (vPodium) vPodium.classList.remove('hidden');

                const pTitle = document.getElementById('guru-podium-title');
                if (pTitle) pTitle.textContent = quiz.title || 'Kuis KBM';

                const podCards = document.getElementById('guru-podium-cards');
                if (podCards) {
                    const podList = quiz.podium || [];
                    const p1 = podList[0] || null;
                    const p2 = podList[1] || null;
                    const p3 = podList[2] || null;

                    podCards.innerHTML = `
                        {{-- Juara 2 (Perak) --}}
                        <div class="flex flex-col items-center">
                            <span class="text-2xl mb-1">🥈</span>
                            <div class="w-full p-2.5 rounded-t-2xl border-2 border-black bg-slate-200 text-center font-mono-code" style="box-shadow: 2px 2px 0px #121316; height: 110px;">
                                <div class="text-[9px] font-black text-slate-600">JUARA 2</div>
                                <div class="text-xs font-black truncate text-slate-900 mt-1">${p2 ? p2.name : '-'}</div>
                                <div class="text-[10px] font-black text-pink-600 mt-0.5">${p2 ? (p2.score || 0).toLocaleString() : '0'} pts</div>
                                <span class="inline-block mt-1 px-1.5 py-0.2 rounded bg-amber-200 text-amber-900 text-[8px] font-black">+10 Rep</span>
                            </div>
                        </div>

                        {{-- Juara 1 (Emas - Paling Tinggi) --}}
                        <div class="flex flex-col items-center -mt-4">
                            <span class="text-3xl mb-1 animate-bounce">🥇</span>
                            <div class="w-full p-3 rounded-t-2xl border-2 border-black bg-amber-300 text-center font-mono-code" style="box-shadow: 3px 3px 0px #121316; height: 135px;">
                                <div class="text-[10px] font-black text-amber-900">JUARA 1 👑</div>
                                <div class="text-xs font-black truncate text-slate-900 mt-1">${p1 ? p1.name : '-'}</div>
                                <div class="text-[11px] font-black text-pink-700 mt-0.5">${p1 ? (p1.score || 0).toLocaleString() : '0'} pts</div>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded bg-black text-amber-300 text-[9px] font-black">+15 Rep</span>
                            </div>
                        </div>

                        {{-- Juara 3 (Perunggu) --}}
                        <div class="flex flex-col items-center">
                            <span class="text-2xl mb-1">🥉</span>
                            <div class="w-full p-2.5 rounded-t-2xl border-2 border-black bg-amber-700/20 text-center font-mono-code" style="box-shadow: 2px 2px 0px #121316; height: 95px;">
                                <div class="text-[9px] font-black text-amber-900">JUARA 3</div>
                                <div class="text-xs font-black truncate text-slate-900 mt-1">${p3 ? p3.name : '-'}</div>
                                <div class="text-[10px] font-black text-pink-600 mt-0.5">${p3 ? (p3.score || 0).toLocaleString() : '0'} pts</div>
                                <span class="inline-block mt-1 px-1.5 py-0.2 rounded bg-amber-200 text-amber-900 text-[8px] font-black">+5 Rep</span>
                            </div>
                        </div>
                    `;
                }
            }
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
            if (isRecordingActive) {
                if (confirm("Perekaman KBM sedang aktif! Apakah Anda ingin menghentikan dan menyimpan rekaman terlebih dahulu?")) {
                    stopMeetingRecording();
                    return;
                }
            }
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
        // 15. SISTEM RECORD VIDEO CONFERENCE & PUBLISH KE LMS (FASE 4+)
        // ============================================================
        let mediaRecorder = null;
        let recordedChunks = [];
        let recordingStream = null;
        let recordingStartTime = null;
        let recordingTimerInterval = null;
        let recordedBlob = null;
        let recordedDurationSeconds = 0;
        let isRecordingActive = false;

        async function toggleMeetingRecording() {
            if (isRecordingActive) {
                stopMeetingRecording();
            } else {
                await startMeetingRecording();
            }
        }

        async function startMeetingRecording() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getDisplayMedia) {
                alert("Browser Anda tidak mendukung perekaman layar WebRTC. Disarankan memakai Google Chrome atau Microsoft Edge versi terbaru.");
                return;
            }

            try {
                // Minta izin merekam layar / tab browser (termasuk audio sistem)
                const displayStream = await navigator.mediaDevices.getDisplayMedia({
                    video: {
                        cursor: "always",
                        displaySurface: "browser"
                    },
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        sampleRate: 44100
                    }
                });

                // Gabungkan audio: System / Tab Audio + Mic Guru dari Agora
                let combinedAudioStream = null;
                try {
                    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                    if (AudioContextClass) {
                        const audioCtx = new AudioContextClass();
                        const dest = audioCtx.createMediaStreamDestination();

                        let hasAudio = false;

                        // Audio dari screen/tab (suara siswa & audio tab)
                        if (displayStream.getAudioTracks().length > 0) {
                            const screenAudioSource = audioCtx.createMediaStreamSource(new MediaStream([displayStream.getAudioTracks()[0]]));
                            screenAudioSource.connect(dest);
                            hasAudio = true;
                        }

                        // Audio mic guru dari localAudioTrack Agora
                        if (localAudioTrack && typeof localAudioTrack.getMediaStreamTrack === 'function') {
                            const micTrack = localAudioTrack.getMediaStreamTrack();
                            if (micTrack) {
                                const micAudioSource = audioCtx.createMediaStreamSource(new MediaStream([micTrack]));
                                micAudioSource.connect(dest);
                                hasAudio = true;
                            }
                        }

                        if (hasAudio) {
                            combinedAudioStream = dest.stream;
                        }
                    }
                } catch (audioErr) {
                    console.warn("Audio mixing warning:", audioErr);
                }

                // Susun track untuk MediaStream final
                const finalTracks = [...displayStream.getVideoTracks()];
                if (combinedAudioStream && combinedAudioStream.getAudioTracks().length > 0) {
                    finalTracks.push(combinedAudioStream.getAudioTracks()[0]);
                } else if (displayStream.getAudioTracks().length > 0) {
                    finalTracks.push(displayStream.getAudioTracks()[0]);
                } else if (localAudioTrack && typeof localAudioTrack.getMediaStreamTrack === 'function') {
                    const micTrack = localAudioTrack.getMediaStreamTrack();
                    if (micTrack) finalTracks.push(micTrack);
                }

                recordingStream = new MediaStream(finalTracks);

                // Pilih format webm yang didukung browser
                let mimeType = 'video/webm;codecs=vp9,opus';
                if (!MediaRecorder.isTypeSupported(mimeType)) {
                    mimeType = 'video/webm;codecs=vp8,opus';
                }
                if (!MediaRecorder.isTypeSupported(mimeType)) {
                    mimeType = 'video/webm';
                }

                mediaRecorder = new MediaRecorder(recordingStream, {
                    mimeType: mimeType,
                    videoBitsPerSecond: 1500000 // 1.5 Mbps bitrate stabil
                });

                recordedChunks = [];

                mediaRecorder.ondataavailable = function(e) {
                    if (e.data && e.data.size > 0) {
                        recordedChunks.push(e.data);
                    }
                };

                mediaRecorder.onstop = function() {
                    finalizeRecording();
                };

                // Jika guru menekan tombol stop sharing di banner floating browser
                displayStream.getVideoTracks()[0].onended = function() {
                    if (isRecordingActive) {
                        stopMeetingRecording();
                    }
                };

                mediaRecorder.start(1000); // chunk slice setiap 1 detik
                isRecordingActive = true;
                recordingStartTime = Date.now();

                // UI Update: Mengubah tombol & indikator jadi merekam
                updateRecordingUI(true);

                recordingTimerInterval = setInterval(() => {
                    const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
                    recordedDurationSeconds = elapsed;
                    const mins = String(Math.floor(elapsed / 60)).padStart(2, '0');
                    const secs = String(elapsed % 60).padStart(2, '0');
                    const timeStr = `${mins}:${secs}`;
                    
                    const headerTooltip = document.getElementById('header-record-tooltip');
                    if (headerTooltip) headerTooltip.textContent = `Merekam [${timeStr}] (Klik Selesai)`;

                    const bottomTooltip = document.getElementById('bottom-record-tooltip');
                    if (bottomTooltip) bottomTooltip.textContent = `Merekam [${timeStr}] (Klik Selesai)`;
                }, 1000);

            } catch (err) {
                console.error("Gagal memulai perekaman:", err);
                if (err.name !== 'NotAllowedError' && err.name !== 'AbortError') {
                    alert("Gagal memulai perekaman: " + (err.message || err.name));
                }
            }
        }

        function stopMeetingRecording() {
            if (!mediaRecorder || mediaRecorder.state === 'inactive') return;

            if (recordingTimerInterval) {
                clearInterval(recordingTimerInterval);
                recordingTimerInterval = null;
            }

            mediaRecorder.stop();
            isRecordingActive = false;

            // Hentikan screen capture track
            if (recordingStream) {
                recordingStream.getTracks().forEach(track => {
                    try { track.stop(); } catch (e) {}
                });
                recordingStream = null;
            }

            updateRecordingUI(false);
        }

        function updateRecordingUI(recording) {
            const headerBtn = document.getElementById('header-btn-record');
            const headerDot = document.getElementById('header-record-dot');
            const headerTooltip = document.getElementById('header-record-tooltip');

            const bottomBtn = document.getElementById('btn-record-session');
            const bottomDot = document.getElementById('bottom-record-indicator');
            const bottomTooltip = document.getElementById('bottom-record-tooltip');

            if (recording) {
                if (headerBtn) {
                    headerBtn.style.backgroundColor = '#ff3823';
                    headerBtn.style.color = '#ffffff';
                    headerBtn.classList.add('animate-pulse');
                }
                if (headerDot) headerDot.className = 'w-3.5 h-3.5 rounded-full bg-white border border-black flex items-center justify-center';
                if (headerTooltip) headerTooltip.textContent = 'Merekam... (Klik Selesai)';

                if (bottomBtn) {
                    bottomBtn.style.backgroundColor = '#ff3823';
                    bottomBtn.style.color = '#ffffff';
                    bottomBtn.classList.add('animate-pulse');
                }
                if (bottomDot) bottomDot.className = 'w-3.5 h-3.5 rounded-full bg-white border border-black flex items-center justify-center';
                if (bottomTooltip) bottomTooltip.textContent = 'Merekam... (Klik Selesai)';
            } else {
                if (headerBtn) {
                    headerBtn.style.backgroundColor = '#ffffff';
                    headerBtn.style.color = '#ff3823';
                    headerBtn.classList.remove('animate-pulse');
                }
                if (headerDot) headerDot.className = 'w-3.5 h-3.5 rounded-full bg-red-600 border border-black flex items-center justify-center';
                if (headerTooltip) headerTooltip.textContent = 'Rekam KBM';

                if (bottomBtn) {
                    bottomBtn.style.backgroundColor = '#ffffff';
                    bottomBtn.style.color = '#ff3823';
                    bottomBtn.classList.remove('animate-pulse');
                }
                if (bottomDot) bottomDot.className = 'w-3.5 h-3.5 rounded-full bg-red-600 border border-black flex items-center justify-center';
                if (bottomTooltip) bottomTooltip.textContent = 'Rekam KBM';
            }
        }

        function finalizeRecording() {
            if (recordedChunks.length === 0) {
                alert("Perekaman tidak menghasilkan data video.");
                return;
            }

            recordedBlob = new Blob(recordedChunks, { type: 'video/webm' });
            const sizeInMb = (recordedBlob.size / (1024 * 1024)).toFixed(1);

            const mins = String(Math.floor(recordedDurationSeconds / 60)).padStart(2, '0');
            const secs = String(recordedDurationSeconds % 60).padStart(2, '0');
            const durStr = `${mins}:${secs}`;

            const durEl = document.getElementById('recording-preview-duration');
            const sizeEl = document.getElementById('recording-preview-size');
            const videoPreview = document.getElementById('recording-preview-video');

            if (durEl) durEl.textContent = durStr;
            if (sizeEl) sizeEl.textContent = `${sizeInMb} MB`;

            if (videoPreview) {
                videoPreview.src = URL.createObjectURL(recordedBlob);
                videoPreview.load();
            }

            openSaveRecordingModal();
        }

        function openSaveRecordingModal() {
            const modal = document.getElementById('modal-save-recording');
            if (modal) modal.classList.remove('hidden');
        }

        function closeSaveRecordingModal() {
            const modal = document.getElementById('modal-save-recording');
            if (modal) modal.classList.add('hidden');
            const videoPreview = document.getElementById('recording-preview-video');
            if (videoPreview) {
                videoPreview.pause();
            }
        }

        function downloadRecordingLocally() {
            if (!recordedBlob) {
                alert("Belum ada video rekaman yang siap didownload.");
                return;
            }
            const url = URL.createObjectURL(recordedBlob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            const courseTitle = "{{ Str::slug($course->course_name ?? $course->name) }}";
            a.download = `rekaman-pembdatemu-${courseTitle}-${Date.now()}.webm`;
            document.body.appendChild(a);
            a.click();
            setTimeout(() => {
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }, 100);
        }

        function uploadRecordingToLms() {
            if (!recordedBlob) {
                alert("Tidak ada file rekaman untuk diunggah.");
                return;
            }

            const titleInput = document.getElementById('rec-material-title');
            const moduleSelect = document.getElementById('rec-material-module-id');
            const descInput = document.getElementById('rec-material-desc');

            const title = titleInput ? titleInput.value.trim() : '';
            if (!title) {
                alert("Harap masukkan judul materi rekaman!");
                if (titleInput) titleInput.focus();
                return;
            }

            const moduleId = moduleSelect ? moduleSelect.value : '';
            const desc = descInput ? descInput.value.trim() : '';

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('title', title);
            if (moduleId) formData.append('module_id', moduleId);
            if (desc) formData.append('description', desc);
            formData.append('duration_seconds', recordedDurationSeconds);
            formData.append('video_file', recordedBlob, `recording-${Date.now()}.webm`);

            const progressBox = document.getElementById('recording-upload-progress-box');
            const progressBar = document.getElementById('recording-upload-bar');
            const progressPct = document.getElementById('recording-upload-pct');
            const submitBtn = document.getElementById('btn-save-recording-submit');

            if (progressBox) progressBox.classList.remove('hidden');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>Sedang Mengunggah...</span>';
            }

            const xhr = new XMLHttpRequest();
            xhr.open('POST', "{{ route('guru.lms.meeting.save-recording', $course->id) }}", true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    const percentComplete = Math.round((e.loaded / e.total) * 100);
                    if (progressBar) progressBar.style.width = percentComplete + '%';
                    if (progressPct) progressPct.textContent = percentComplete + '%';
                }
            };

            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const res = JSON.parse(xhr.responseText);
                        if (res.success) {
                            alert("Sukses! Video rekaman KBM berhasil disimpan dan otomatis diterbitkan di materi modul siswa.");
                            closeSaveRecordingModal();
                        } else {
                            alert("Gagal menyimpan rekaman: " + (res.message || 'Terjadi kesalahan'));
                        }
                    } catch (e) {
                        alert("Sukses mengunggah rekaman video!");
                        closeSaveRecordingModal();
                    }
                } else {
                    alert("Terjadi kesalahan saat mengunggah (HTTP " + xhr.status + "). Anda tetap dapat mengklik tombol 'Download' untuk menyimpan video ke komputer.");
                }
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    submitBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i><span>Simpan &amp; Terbitkan ke Siswa</span>';
                }
                if (progressBox) progressBox.classList.add('hidden');
            };

            xhr.onerror = function() {
                alert("Gagal menghubungi server. Silakan klik tombol 'Download' untuk mengamankan rekaman di laptop Anda.");
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    submitBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i><span>Simpan &amp; Terbitkan ke Siswa</span>';
                }
                if (progressBox) progressBox.classList.add('hidden');
            };

            xhr.send(formData);
        }

        // ============================================================
        // 16. CLEANUP & INIT SAAT LOAD
        // ============================================================
        document.addEventListener('DOMContentLoaded', () => {
            initAgora().catch(err => {
                console.error('Agora init error:', err);
                showAgoraError(err.message || 'Kamera atau mikrofon tidak dapat dibuka');
            });

            setInterval(syncMeetingState, 3500);
            syncMeetingState();
        });

        window.addEventListener('beforeunload', async () => {
            if (isRecordingActive) {
                stopMeetingRecording();
            }
            if (localAudioTrack) { localAudioTrack.stop(); localAudioTrack.close(); }
            if (localVideoTrack) { localVideoTrack.stop(); localVideoTrack.close(); }
            if (localScreenTrack) { localScreenTrack.stop(); localScreenTrack.close(); }
            if (agoraClient) await agoraClient.leave();
        });
    </script>
</body>
</html>
