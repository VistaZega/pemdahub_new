<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PembdaTEMU Guru: {{ $course->course_name ?? $course->name }} - PembdaHUB</title>
    
    <!-- Tailwind CSS & App JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- FontAwesome 6 -->
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
            background-color: var(--bg-warm);
            color: var(--text-dark);
            letter-spacing: -0.02em;
        }

        .font-mono-code {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Yellow Highlighter Stroke Effect (Khas PembdaHUB Homepage) */
        .highlight-marker {
            background: linear-gradient(180deg, transparent 62%, #fde047 62%, #fde047 92%, transparent 92%);
            display: inline;
        }

        /* Dotted Graph Paper Background */
        .graph-paper-box {
            background-color: #faf8f5;
            background-image: radial-gradient(#cbd5e1 1.2px, transparent 1.2px);
            background-size: 16px 16px;
        }

        /* Tactile Solid Drop-Shadow Buttons */
        .btn-tactile-red {
            background: #ff3823;
            color: #ffffff;
            border: 2px solid #121316;
            box-shadow: 3px 3px 0px #121316;
            transition: all 0.15s ease;
        }
        .btn-tactile-red:hover {
            transform: translate(1.5px, 1.5px);
            box-shadow: 1.5px 1.5px 0px #121316;
        }

        .btn-tactile-yellow {
            background: #fbc02d;
            color: #121316;
            border: 2px solid #121316;
            box-shadow: 3px 3px 0px #121316;
            transition: all 0.15s ease;
        }
        .btn-tactile-yellow:hover {
            transform: translate(1.5px, 1.5px);
            box-shadow: 1.5px 1.5px 0px #121316;
        }

        .btn-tactile-white {
            background: #ffffff;
            color: #121316;
            border: 2px solid #121316;
            box-shadow: 3px 3px 0px #121316;
            transition: all 0.15s ease;
        }
        .btn-tactile-white:hover {
            transform: translate(1.5px, 1.5px);
            box-shadow: 1.5px 1.5px 0px #121316;
        }

        /* Poster Card Tactile */
        .poster-card {
            border: 2px solid #121316;
            border-radius: 1.25rem;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 3px 3px 0px #121316;
        }
        .poster-card:hover {
            transform: translateY(-2px);
            box-shadow: 5px 5px 0px #121316;
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
            background: #0f172a;
            border-radius: 1.5rem;
            overflow: hidden;
            border: 2.5px solid #121316;
            box-shadow: 4px 4px 0px #121316;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .video-tile:hover {
            box-shadow: 6px 6px 0px #121316;
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

    {{-- 1. SMART CLASSROOM HEADER (PEMBDAHUB HOMEPAGE TACTILE STYLE) --}}
    <header class="bg-[#faf8f5] border-b-2 border-[#121316] px-4 sm:px-6 py-2.5 flex items-center justify-between gap-3 z-30 flex-shrink-0 shadow-xs">
        
        {{-- Brand, Unit & Info Kelas --}}
        <div class="flex items-center gap-3 min-w-0">
            <div class="flex items-center gap-1.5 flex-shrink-0">
                <a href="{{ route('guru.dashboard') }}" title="Kembali ke Dashboard Guru" class="hover:scale-105 transition-transform flex-shrink-0">
                    <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo PembdaHUB" class="w-9 h-9 object-contain rounded-xl border-2 border-[#121316] shadow-[2px_2px_0px_#ff3823] bg-white p-0.5">
                </a>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-[#ff3823] text-white border border-[#121316] uppercase tracking-wider shadow-[2px_2px_0px_#121316]">
                        <span class="w-2 h-2 rounded-full bg-[#fde047] animate-ping"></span> ✱ PembdaTEMU LIVE
                    </span>
                    <span class="px-2 py-0.5 rounded-md bg-[#fde047] text-[#121316] text-[10px] font-mono-code font-extrabold border border-[#121316] hidden md:inline truncate">
                        {{ $course->school?->name ?? 'SMKS Swasta Pembda Nias' }}
                    </span>
                </div>
                <h1 class="text-sm sm:text-base font-black text-[#121316] truncate leading-tight mt-1 flex items-center gap-1.5">
                    <span class="highlight-marker">{{ $course->course_name ?? $course->name }}</span>
                    @if($course->subject)
                        <span class="text-xs font-bold text-[#555] hidden sm:inline">• {{ $course->subject->name }}</span>
                    @endif
                </h1>
            </div>
        </div>

        {{-- Center Status Widget: Durasi Live & Peserta Hadir (JetBrains Mono Tactile) --}}
        <div class="hidden lg:flex items-center gap-3 bg-[#121316] text-white border-2 border-[#121316] rounded-2xl px-4 py-1.5 shadow-[3px_3px_0px_#ff3823]">
            <div class="flex items-center gap-2 text-xs font-mono-code font-bold text-[#fde047]" title="Durasi Live Kelas">
                <i class="far fa-clock text-[#fbc02d] animate-spin" style="animation-duration: 8s;"></i>
                <span id="class-timer" class="font-mono-code tracking-widest text-sm text-white font-black">00:00:00</span>
            </div>
            <div class="w-px h-4 bg-white/20"></div>
            <button onclick="switchTab('peserta')" class="flex items-center gap-1.5 text-xs font-mono-code font-black text-[#10b981] hover:text-emerald-300 transition-colors" title="Lihat daftar siswa hadir">
                <span class="w-2 h-2 rounded-full bg-[#10b981] animate-ping"></span>
                <span id="header-attendee-count">0</span> Siswa Hadir
            </button>
        </div>

        {{-- Right Controls (Tactile Buttons with Icon Boxes) --}}
        <div class="flex items-center gap-2 flex-shrink-0">
            
            {{-- Tombol Salin Tautan (Sky Tactile) --}}
            <button onclick="copyMeetingLink()" type="button" class="hidden sm:flex items-center gap-1.5 btn-tactile-white px-3.5 py-1.5 rounded-xl text-xs font-mono-code font-black active:scale-95" title="Salin link kelas untuk dibagikan ke siswa via WhatsApp">
                <div class="w-5 h-5 rounded-md bg-[#0284c7] text-white flex items-center justify-center text-[10px] border border-[#121316]">
                    <i class="fas fa-link"></i>
                </div>
                <span>Salin Tautan</span>
            </button>

            {{-- Tombol Papan Tulis Cepat (Purple Tactile) --}}
            <button onclick="openTabDirect('whiteboard')" type="button" class="flex items-center gap-1.5 btn-tactile-white px-3.5 py-1.5 rounded-xl text-xs font-mono-code font-black active:scale-95" title="Buka Papan Tulis Digital KBM">
                <div class="w-5 h-5 rounded-md bg-[#7c3aed] text-white flex items-center justify-center text-[10px] border border-[#121316]">
                    <i class="fas fa-pen-ruler"></i>
                </div>
                <span class="hidden md:inline">Papan Tulis</span>
            </button>

            {{-- Tombol Pilihan Tampilan (Zoom View Selector - Amber Tactile) --}}
            <div class="relative inline-block text-left" id="header-view-wrapper">
                <button onclick="toggleViewMenu(event)" type="button" class="flex items-center gap-1.5 btn-tactile-white px-3 py-1.5 rounded-xl text-xs font-mono-code font-black active:scale-95" title="Pilihan Tata Letak Tampilan (Zoom)">
                    <div class="w-5 h-5 rounded-md bg-[#fbc02d] text-[#121316] flex items-center justify-center text-[10px] border border-[#121316]">
                        <i id="header-view-icon" class="fas fa-table-cells"></i>
                    </div>
                    <span class="hidden md:inline">Tata Letak: <span id="header-view-name" class="text-[#ff3823] font-black">Galeri</span></span>
                    <i class="fas fa-chevron-down text-[9px] text-[#121316] ml-0.5"></i>
                </button>
            </div>

            {{-- Tombol Toggle Panel Materi / Peserta (Indigo Tactile) --}}
            <button onclick="toggleClassroomHub()" type="button" id="btn-toggle-hub" class="flex items-center gap-1.5 btn-tactile-white px-3.5 py-1.5 rounded-xl text-xs font-mono-code font-black active:scale-95" title="Buka atau sembunyikan panel modul dan siswa">
                <div class="w-5 h-5 rounded-md bg-[#4f46e5] text-white flex items-center justify-center text-[10px] border border-[#121316]">
                    <i class="fas fa-columns"></i>
                </div>
                <span class="hidden sm:inline">Panel KBM</span>
            </button>

            {{-- Profile Chip Guru --}}
            <div class="hidden xl:flex items-center gap-2 bg-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316] px-2.5 py-1 rounded-xl text-xs text-[#121316]" title="{{ $displayName }}">
                <div class="w-6 h-6 rounded-lg bg-[#ff3823] border border-black flex items-center justify-center text-[11px] font-black text-white uppercase shadow-xs">
                    {{ substr($displayName, 0, 1) }}
                </div>
                <span class="font-mono-code font-black max-w-[100px] truncate text-[#121316]">{{ explode(' ', trim($displayName))[0] }}</span>
            </div>

            {{-- Tombol Akhiri Kelas (Crimson Tactile) --}}
            <button onclick="openEndMeetingModal()" type="button" class="btn-tactile-red px-3.5 sm:px-4 py-1.5 rounded-xl text-xs font-mono-code font-black flex items-center gap-1.5 active:scale-95">
                <i class="fas fa-power-off text-xs"></i>
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
                        <div class="w-5 h-5 rounded-md bg-[#2563eb] text-white flex items-center justify-center text-[10px] border border-[#121316]">
                            <i id="current-view-icon" class="fas fa-table-cells"></i>
                        </div>
                        <span>Tata Letak: <span id="current-view-name" class="text-[#ff3823] font-black">Galeri</span></span>
                        <i class="fas fa-chevron-down text-[9px] text-[#121316]"></i>
                    </button>

                    <div id="view-dropdown-menu" class="hidden absolute right-0 mt-2 w-60 rounded-2xl bg-white border-2 border-[#121316] shadow-[5px_5px_0px_#121316] p-2.5 z-50 text-[#121316] transition-all">
                        <div class="px-3 py-1 text-[10px] font-mono-code font-extrabold uppercase tracking-wider text-[#777] border-b border-[#e2ded5] mb-1.5 flex items-center justify-between">
                            <span>TATA LETAK ZOOM</span>
                            <span>⌜ ⌟</span>
                        </div>
                        
                        <button onclick="changeLayout('gallery')" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-mono-code font-bold hover:bg-[#faf3e0] text-[#121316] flex items-center justify-between transition-colors border border-transparent hover:border-[#121316]">
                            <span class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-md bg-[#2563eb] text-white flex items-center justify-center text-[10px] border border-black"><i class="fas fa-table-cells"></i></span>
                                <span>Tampilan Galeri</span>
                            </span>
                            <i id="check-gallery" class="fas fa-check text-[#2563eb] text-xs font-black"></i>
                        </button>

                        <button onclick="changeLayout('speaker')" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-mono-code font-bold hover:bg-[#faf3e0] text-[#121316] flex items-center justify-between transition-colors border border-transparent hover:border-[#121316]">
                            <span class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-md bg-[#ff3823] text-white flex items-center justify-center text-[10px] border border-black"><i class="fas fa-user-tie"></i></span>
                                <span>Tampilan Pembicara</span>
                            </span>
                            <i id="check-speaker" class="fas fa-check text-[#ff3823] text-xs hidden font-black"></i>
                        </button>

                        <button onclick="changeLayout('sidebyside')" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-mono-code font-bold hover:bg-[#faf3e0] text-[#121316] flex items-center justify-between transition-colors border border-transparent hover:border-[#121316]">
                            <span class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-md bg-[#10b981] text-white flex items-center justify-center text-[10px] border border-black"><i class="fas fa-columns"></i></span>
                                <span>Berdampingan (KBM)</span>
                            </span>
                            <i id="check-sidebyside" class="fas fa-check text-[#10b981] text-xs hidden font-black"></i>
                        </button>

                        <div class="h-px bg-[#e2ded5] my-1.5"></div>

                        <button onclick="toggleAppFullscreen()" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-mono-code font-bold hover:bg-[#faf3e0] text-[#121316] flex items-center gap-2.5 transition-colors border border-transparent hover:border-[#121316]">
                            <span class="w-5 h-5 rounded-md bg-[#fbc02d] text-black flex items-center justify-center text-[10px] border border-black"><i id="menu-fs-icon" class="fas fa-expand"></i></span>
                            <span id="menu-fs-text">Layar Penuh (F11)</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- STATUS INDIKATOR GURU (TENGAH ATAS - TACTILE PILL AGAR TIDAK MENABRAK BADGE GURU) --}}
            <div id="solo-teacher-banner" class="absolute top-4 left-1/2 -translate-x-1/2 z-20 pointer-events-auto transition-all duration-300">
                <div class="flex items-center gap-2 bg-[#fbc02d] border-2 border-[#121316] shadow-[3px_3px_0px_#121316] px-4 py-1.5 rounded-full text-xs font-mono-code font-black text-[#121316]">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#10b981] animate-ping"></span>
                    <i class="fas fa-video text-xs"></i>
                    <span>KAMERA AKTIF • MENUNGGU SISWA MASUK</span>
                </div>
            </div>

            {{-- 1. SPEAKER VIEW STAGE (Active when currentLayout === 'speaker') --}}
            <div id="stage-speaker-wrapper" class="hidden flex-1 flex-col h-full overflow-hidden p-3 pb-28 gap-2.5 relative min-h-0">
                {{-- Top Filmstrip Bar: Thumbnail peserta lain --}}
                <div id="speaker-filmstrip" class="flex items-center gap-2.5 overflow-x-auto p-2 h-28 shrink-0 bg-white/80 rounded-2xl border-2 border-[#121316] shadow-[3px_3px_0px_#121316] backdrop-blur-sm scroll-smooth">
                    {{-- Non-pinned tiles live here --}}
                </div>
                {{-- Main Spotlight Screen: 1 Peserta Terfokus (Guru/Siswa) --}}
                <div id="speaker-main-stage" class="flex-1 flex items-center justify-center overflow-hidden min-h-0 relative p-1">
                    {{-- Pinned tile lives here --}}
                </div>
            </div>

            {{-- 2. GALLERY VIEW STAGE (Default: Active when currentLayout === 'gallery' or 'sidebyside') --}}
            {{-- Catatan: pb-28 memastikan video tile TIDAK PERNAH tertutup oleh dock mengambang di bawah --}}
            <div id="stage-gallery-wrapper" class="flex-1 flex items-center justify-center overflow-hidden h-full p-2.5 sm:p-4 pb-28 min-h-0 relative">
                <div id="gallery-grid" class="w-full h-full flex items-center justify-center gap-3.5 transition-all duration-300">
                    {{-- Local video tile (Guru) --}}
                    <div id="video-tile-local" class="video-tile group relative rounded-3xl overflow-hidden bg-slate-900 border-2.5 border-[#121316] shadow-[4px_4px_0px_#121316] flex items-center justify-center w-full h-full max-h-[82vh] transition-all">
                        <div id="agora-local-video" class="w-full h-full relative">
                            {{-- Agora video injected here --}}
                        </div>
                        <div id="agora-local-placeholder" class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 bg-slate-900 pointer-events-none">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-rose-500 to-amber-500 border-2 border-white flex items-center justify-center text-2xl font-black text-white mb-2 shadow-lg">
                                {{ substr($displayName, 0, 1) }}
                            </div>
                            <p class="text-xs font-mono-code font-bold text-[#fde047]">Mengaktifkan kamera...</p>
                        </div>
                        
                        {{-- 🎯 PERBAIKAN: BADGE NAMA GURU DIPINDAH KE POJOK KIRI ATAS --}}
                        {{-- Dijamin 100% TIDAK AKAN PERNAH TERTUTUP oleh menu bar di bawah! --}}
                        <div class="absolute top-4 left-4 z-20 flex items-center gap-2 bg-[#121316]/95 backdrop-blur-md px-3 py-1.5 rounded-xl text-xs font-black text-white border-2 border-[#121316] shadow-[3px_3px_0px_#ff3823]" title="{{ $displayName }}">
                            <span class="w-2 h-2 rounded-full bg-[#10b981] animate-pulse"></span>
                            <i id="tile-mic-local" class="fas fa-microphone text-[#10b981] text-xs"></i>
                            <span class="truncate max-w-[150px] text-[#fde047] font-mono-code font-bold">Pak {{ explode(' ', trim($displayName))[0] }}</span>
                            <span class="px-2 py-0.5 rounded-md bg-[#ff3823] text-white text-[9px] font-mono-code font-black uppercase tracking-wider">HOST GURU</span>
                        </div>
                        
                        <div class="absolute top-4 right-4 z-20 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="pinSpeaker('local')" type="button" class="p-2 rounded-xl bg-white hover:bg-[#fde047] text-[#121316] border-2 border-[#121316] text-xs shadow-[2px_2px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 transition" title="Fokuskan / Spotlight">
                                <i class="fas fa-thumbtack text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Floating Teacher Action Dock (Neo-Tactile Island Khas PembdaHUB) --}}
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2 sm:gap-2.5 bg-white border-2 border-[#121316] shadow-[5px_5px_0px_#121316] px-3.5 sm:px-5 py-2.5 rounded-2xl sm:rounded-full">
                
                {{-- Toggle Mic --}}
                <button id="btn-toggle-mic" onclick="toggleMic()" type="button" class="group relative p-3 rounded-xl bg-[#10b981] text-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316] transition active:translate-x-0.5 active:translate-y-0.5" title="Nyalakan/Matikan Mikrofon">
                    <i id="mic-icon" class="fas fa-microphone text-base text-white"></i>
                    <span class="absolute -top-9 left-1/2 -translate-x-1/2 bg-[#121316] text-[#fde047] text-[10px] font-mono-code font-black px-2.5 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap border border-black shadow-md">Mic</span>
                </button>

                {{-- Toggle Camera --}}
                <button id="btn-toggle-cam" onclick="toggleCam()" type="button" class="group relative p-3 rounded-xl bg-[#0891b2] text-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316] transition active:translate-x-0.5 active:translate-y-0.5" title="Nyalakan/Matikan Kamera">
                    <i id="cam-icon" class="fas fa-video text-base text-white"></i>
                    <span class="absolute -top-9 left-1/2 -translate-x-1/2 bg-[#121316] text-[#fde047] text-[10px] font-mono-code font-black px-2.5 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap border border-black shadow-md">Kamera</span>
                </button>

                {{-- Screen Share --}}
                <button onclick="toggleScreenShare()" id="btn-screenshare" type="button" class="group relative p-3 rounded-xl bg-[#2563eb] text-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316] transition active:translate-x-0.5 active:translate-y-0.5" title="Bagikan Layar Anda">
                    <i class="fas fa-desktop text-base text-white"></i>
                    <span class="absolute -top-9 left-1/2 -translate-x-1/2 bg-[#121316] text-[#fde047] text-[10px] font-mono-code font-black px-2.5 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap border border-black shadow-md">Bagi Layar</span>
                </button>

                <div class="w-px h-6 bg-[#121316] hidden sm:block"></div>

                {{-- Mute All Button (Amber Tactile) --}}
                <button onclick="teacherMuteEveryone()" type="button" class="flex items-center gap-1.5 btn-tactile-yellow px-3.5 sm:px-4 py-2.5 rounded-xl text-xs font-mono-code font-black active:scale-95" title="Kondusifkan Kelas: Minta seluruh siswa mematikan mic">
                    <i class="fas fa-volume-xmark text-sm"></i>
                    <span class="hidden sm:inline">Mute Semua</span>
                </button>

                <div class="w-px h-6 bg-[#121316] hidden sm:block"></div>

                {{-- Quick Open Material (Indigo Tactile) --}}
                <button onclick="openTabDirect('materi')" type="button" class="flex items-center gap-1.5 bg-[#4f46e5] hover:bg-indigo-700 text-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316] px-3.5 sm:px-4 py-2.5 rounded-xl text-xs font-mono-code font-black transition-all active:translate-x-0.5 active:translate-y-0.5" title="Buka silabus materi & modul kelas">
                    <i class="fas fa-book-open text-sm text-[#fde047]"></i>
                    <span class="hidden sm:inline">Modul</span>
                </button>

                {{-- Quick Open Whiteboard (Purple Tactile) --}}
                <button onclick="openTabDirect('whiteboard')" type="button" class="flex items-center gap-1.5 bg-[#7c3aed] hover:bg-purple-700 text-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316] px-3.5 sm:px-4 py-2.5 rounded-xl text-xs font-mono-code font-black transition-all active:translate-x-0.5 active:translate-y-0.5" title="Tulis rumus atau coretan di papan tulis">
                    <i class="fas fa-pen-ruler text-sm text-[#fde047]"></i>
                    <span class="hidden sm:inline">Papan Tulis</span>
                </button>

                {{-- Quick Open Attendees (White Tactile with Live Emerald Badge) --}}
                <button onclick="openTabDirect('peserta')" type="button" class="flex items-center gap-1.5 bg-white hover:bg-[#faf3e0] text-[#121316] border-2 border-[#121316] shadow-[2px_2px_0px_#121316] px-3.5 sm:px-4 py-2.5 rounded-xl text-xs font-mono-code font-black transition-all active:translate-x-0.5 active:translate-y-0.5" title="Lihat kehadiran siswa secara realtime">
                    <i class="fas fa-users-viewfinder text-sm text-[#2563eb]"></i>
                    <span class="hidden sm:inline">Presensi</span>
                    <span id="quick-attendee-badge" class="bg-[#10b981] text-white px-2 py-0.5 rounded-full text-[10px] font-mono-code font-black border border-[#121316]">0</span>
                </button>
            </div>
        </main>

        {{-- Resize Handle Divider --}}
        <div id="resize-handler" class="w-[10px] bg-[#ede9df] hover:bg-[#ff3823] cursor-col-resize select-none z-20 flex items-center justify-center relative transition-colors duration-150 group border-x border-[#121316]" title="Geser untuk mengatur lebar panel materi">
            <div class="w-4 h-12 bg-white border-2 border-[#121316] rounded-md flex items-center justify-center shadow-[2px_2px_0px_#121316] pointer-events-none">
                <i class="fas fa-grip-lines-vertical text-[10px] text-[#121316]"></i>
            </div>
        </div>

        {{-- Resize Overlay (Mencegah iframe menyerap pointer saat drag) --}}
        <div id="resize-overlay" class="fixed inset-0 z-40 hidden cursor-col-resize"></div>

        {{-- 3. CLASSROOM MULTI-HUB PANEL (TACTILE BINDER STYLE KHAS PEMBDAHUB) --}}
        <aside id="classroom-hub" class="w-[520px] max-w-[90vw] border-l-2 border-[#121316] bg-[#faf8f5] flex flex-col h-full z-15 shadow-2xl relative">
            
            {{-- Tabs Header Bar (Tactile Binder Style) --}}
            <div class="border-b-2 border-[#121316] flex items-center bg-[#ede9df] p-2 gap-2 flex-shrink-0">
                <button id="tab-btn-materi" onclick="switchTab('materi')" type="button" class="flex-1 py-2 px-3 text-xs font-mono-code font-black transition-all flex items-center justify-center gap-2 rounded-xl bg-[#121316] text-[#fde047] border-2 border-[#121316] shadow-[2px_2px_0px_#ff3823]">
                    <i class="fas fa-book text-xs text-[#ff3823]"></i>
                    <span>Materi & Modul</span>
                </button>
                <button id="tab-btn-whiteboard" onclick="switchTab('whiteboard')" type="button" class="flex-1 py-2 px-3 text-xs font-mono-code font-bold transition-all flex items-center justify-center gap-2 rounded-xl bg-white text-[#121316] border-2 border-[#121316] hover:bg-[#faf3e0]">
                    <i class="fas fa-pen-ruler text-xs text-[#7c3aed]"></i>
                    <span>Papan Tulis</span>
                </button>
                <button id="tab-btn-peserta" onclick="switchTab('peserta')" type="button" class="flex-1 py-2 px-3 text-xs font-mono-code font-bold transition-all flex items-center justify-center gap-2 rounded-xl bg-white text-[#121316] border-2 border-[#121316] hover:bg-[#faf3e0]">
                    <i class="fas fa-users text-xs text-[#10b981]"></i>
                    <span>Siswa (<span id="attendee-tab-count">0</span>)</span>
                </button>
                <button onclick="toggleClassroomHub()" type="button" class="p-2 text-[#121316] hover:bg-[#ff3823] hover:text-white rounded-xl border-2 border-[#121316] bg-white transition-colors shadow-xs" title="Tutup panel">
                    <i class="fas fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- TAB 1: MATERI & MODUL PEMBELAJARAN --}}
            <div id="tab-content-materi" class="flex-1 flex flex-col overflow-hidden">
                
                {{-- Viewer Preview Box (Ketika Guru Membuka Materi) --}}
                <div id="material-preview-box" class="h-1/2 min-h-[220px] border-b-2 border-[#121316] bg-white flex flex-col hidden">
                    <div class="px-4 py-2 border-b-2 border-[#121316] bg-[#faf3e0] flex items-center justify-between">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-6 h-6 rounded-md bg-[#ff3823] text-white flex items-center justify-center text-xs border border-black"><i id="preview-icon" class="far fa-file-pdf"></i></span>
                            <h3 id="preview-title" class="text-xs font-black text-[#121316] truncate">Judul Materi</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <a id="preview-external-link" href="#" target="_blank" class="btn-tactile-white text-[10px] font-mono-code font-bold px-2.5 py-1 rounded-lg flex items-center gap-1">
                                Tab Baru <i class="fas fa-external-link-alt text-[8px]"></i>
                            </a>
                            <button onclick="closeMaterialPreview()" type="button" class="text-[#121316] hover:bg-[#ff3823] hover:text-white p-1 rounded-md border border-black" title="Tutup preview materi">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    </div>
                    <div class="flex-1 relative bg-white overflow-hidden">
                        <iframe id="preview-iframe" class="w-full h-full border-none hidden" allow="autoplay; clipboard-write; clipboard-read"></iframe>
                        <div id="preview-text" class="p-4 text-xs text-[#121316] overflow-y-auto h-full prose max-w-none hidden font-mono-code"></div>
                    </div>
                </div>

                {{-- Daftar Modul & Materi Silabus (Graph Texture with Poster Cards) --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-3.5 graph-paper-box">
                    <div class="flex items-center justify-between pb-1">
                        <span class="text-[11px] font-mono-code font-extrabold uppercase tracking-wider text-[#ff3823] flex items-center gap-1">
                            <span>✱</span> SILABUS & BAHAN AJAR
                        </span>
                        <span class="text-[10px] font-mono-code font-bold text-[#777]">Klik "Buka" untuk tayang</span>
                    </div>

                    @php
                        $modules = $course->modules()->ordered()->active()->get();
                    @endphp

                    @if($modules->isEmpty())
                        <div class="text-center text-xs text-[#777] py-12 poster-card bg-white p-6">
                            <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-[#ede9df] border-2 border-[#121316] flex items-center justify-center text-[#ff3823] text-2xl shadow-[3px_3px_0px_#121316]">
                                <i class="far fa-folder-open"></i>
                            </div>
                            <h4 class="font-black text-[#121316] text-sm">Belum Ada Materi</h4>
                            <p class="font-medium mt-1">Belum ada modul atau materi di kelas ini.</p>
                            <a href="{{ route('guru.lms.materials.create', $course->id) }}" target="_blank" class="mt-3 inline-flex items-center gap-1.5 btn-tactile-red text-xs font-mono-code font-bold px-3.5 py-1.5 rounded-xl">
                                <i class="fas fa-plus"></i> Tambah Materi Baru
                            </a>
                        </div>
                    @else
                        @foreach($modules as $module)
                            <div class="poster-card bg-white overflow-hidden">
                                <div class="p-3 bg-[#121316] text-white border-b-2 border-[#121316] flex items-center justify-between">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#fde047]"></span>
                                        <h4 class="text-xs font-black text-white truncate font-mono-code">{{ $module->title }}</h4>
                                    </div>
                                    <span class="text-[10px] font-mono-code font-bold bg-[#fbc02d] text-[#121316] px-2 py-0.5 rounded-md border border-black">{{ $module->materials()->active()->count() }} Materi</span>
                                </div>
                                <div class="p-2.5 space-y-2 bg-[#faf8f5]">
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
                                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-white hover:bg-[#faf3e0] border-1.5 border-[#121316] text-xs transition-all shadow-[2px_2px_0px_#121316]">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                @if($material->material_type == 'pdf')
                                                    <span class="w-7 h-7 rounded-lg bg-[#ff3823] text-white flex items-center justify-center text-xs border border-black flex-shrink-0"><i class="far fa-file-pdf"></i></span>
                                                @elseif($material->material_type == 'video')
                                                    <span class="w-7 h-7 rounded-lg bg-[#fbc02d] text-[#121316] flex items-center justify-center text-xs border border-black flex-shrink-0"><i class="far fa-play-circle"></i></span>
                                                @elseif($material->material_type == 'link')
                                                    <span class="w-7 h-7 rounded-lg bg-[#0891b2] text-white flex items-center justify-center text-xs border border-black flex-shrink-0"><i class="fas fa-link"></i></span>
                                                @elseif($material->material_type == 'canva' || $material->material_type == 'googledocs')
                                                    <span class="w-7 h-7 rounded-lg bg-[#10b981] text-white flex items-center justify-center text-xs border border-black flex-shrink-0"><i class="fas fa-file-lines"></i></span>
                                                @else
                                                    <span class="w-7 h-7 rounded-lg bg-[#4f46e5] text-white flex items-center justify-center text-xs border border-black flex-shrink-0"><i class="far fa-file-lines"></i></span>
                                                @endif
                                                <span class="text-[11px] font-bold text-[#121316] truncate">{{ $material->title }}</span>
                                            </div>
                                            <button onclick="displayMaterial('{{ $safeTitle }}', '{{ $material->material_type }}', '{{ $url }}', '{{ $safeContent }}')" type="button" class="btn-tactile-white text-[10px] font-mono-code font-black px-2.5 py-1 rounded-lg flex items-center gap-1 flex-shrink-0">
                                                Buka <i class="fas fa-arrow-up-right-from-square text-[8px]"></i>
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
            <div id="tab-content-whiteboard" class="flex-1 flex flex-col overflow-hidden hidden bg-[#faf8f5]">
                
                {{-- Toolbar Papan Tulis --}}
                <div class="p-2.5 bg-white border-b-2 border-[#121316] flex flex-wrap items-center justify-between gap-2 flex-shrink-0 shadow-xs">
                    
                    {{-- Warna & Mode --}}
                    <div class="flex items-center gap-1.5">
                        <button onclick="setWhiteboardTool('pen')" id="tool-pen" type="button" class="p-2 rounded-xl bg-[#4f46e5] text-white text-xs font-bold border-2 border-black shadow-[2px_2px_0px_#121316]" title="Spidol / Pena">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button onclick="setWhiteboardTool('eraser')" id="tool-eraser" type="button" class="p-2 rounded-xl bg-white hover:bg-slate-100 text-[#121316] border-2 border-[#121316] text-xs font-bold shadow-[2px_2px_0px_#121316]" title="Penghapus Coretan">
                            <i class="fas fa-eraser"></i>
                        </button>

                        <div class="w-px h-6 bg-[#121316] mx-1"></div>

                        {{-- Palet Warna Coretan --}}
                        <div class="flex items-center gap-1.5">
                            <button onclick="setPenColor('#121316')" class="w-6 h-6 rounded-lg bg-[#121316] border-2 border-black hover:scale-110 transition-transform shadow-xs" title="Hitam"></button>
                            <button onclick="setPenColor('#2563eb')" class="w-6 h-6 rounded-lg bg-[#2563eb] border-2 border-black hover:scale-110 transition-transform shadow-xs" title="Biru"></button>
                            <button onclick="setPenColor('#ff3823')" class="w-6 h-6 rounded-lg bg-[#ff3823] border-2 border-black hover:scale-110 transition-transform shadow-xs" title="Merah"></button>
                            <button onclick="setPenColor('#10b981')" class="w-6 h-6 rounded-lg bg-[#10b981] border-2 border-black hover:scale-110 transition-transform shadow-xs" title="Hijau"></button>
                            <button onclick="setPenColor('#fbc02d')" class="w-6 h-6 rounded-lg bg-[#fbc02d] border-2 border-black hover:scale-110 transition-transform shadow-xs" title="Kuning"></button>
                        </div>
                    </div>

                    {{-- Tebal Garis & Latar Papan --}}
                    <div class="flex items-center gap-1.5">
                        <select id="pen-size-select" onchange="setPenSize(this.value)" class="bg-white border-2 border-[#121316] text-[#121316] text-xs font-mono-code font-bold rounded-xl px-2.5 py-1.5 outline-none shadow-[2px_2px_0px_#121316]">
                            <option value="2">Halus (2px)</option>
                            <option value="4" selected>Sedang (4px)</option>
                            <option value="8">Tebal (8px)</option>
                            <option value="16">Stabilo (16px)</option>
                        </select>

                        <select id="canvas-bg-select" onchange="setCanvasBackground(this.value)" class="bg-white border-2 border-[#121316] text-[#121316] text-xs font-mono-code font-bold rounded-xl px-2.5 py-1.5 outline-none shadow-[2px_2px_0px_#121316]">
                            <option value="whiteboard" selected>Whiteboard Putih</option>
                            <option value="grid">Grid Matematika</option>
                            <option value="chalkboard">Papan Tulis Hijau</option>
                        </select>
                    </div>

                    {{-- Actions: Undo, Clear, Save --}}
                    <div class="flex items-center gap-1.5 ml-auto">
                        <button onclick="undoWhiteboard()" type="button" class="btn-tactile-white p-2 rounded-xl text-xs font-bold" title="Batalkan coretan terakhir">
                            <i class="fas fa-rotate-left"></i>
                        </button>
                        <button onclick="clearWhiteboard()" type="button" class="btn-tactile-red p-2 rounded-xl text-xs font-bold" title="Hapus semua coretan papan tulis">
                            <i class="fas fa-trash-can"></i>
                        </button>
                        <button onclick="downloadWhiteboard()" type="button" class="btn-tactile-yellow px-3 py-1.5 rounded-xl font-mono-code font-black text-xs flex items-center gap-1" title="Unduh hasil coretan sebagai gambar PNG">
                            <i class="fas fa-download"></i> <span class="hidden xl:inline">Simpan</span>
                        </button>
                    </div>
                </div>

                {{-- Drawing Canvas Area with Tactile Border --}}
                <div id="whiteboard-container" class="flex-1 relative overflow-hidden canvas-whiteboard cursor-crosshair m-2.5 rounded-2xl border-2 border-[#121316] shadow-[3px_3px_0px_#121316]">
                    <canvas id="whiteboard-canvas" class="w-full h-full block"></canvas>
                </div>
            </div>

            {{-- TAB 3: DAFTAR SISWA & PRESENSI REALTIME --}}
            <div id="tab-content-peserta" class="flex-1 flex flex-col overflow-hidden hidden bg-[#faf8f5]">
                
                {{-- Header Sub-bar --}}
                <div class="p-3 bg-white border-b-2 border-[#121316] flex items-center justify-between flex-shrink-0 shadow-xs">
                    <div>
                        <div class="text-[10px] font-mono-code font-black text-[#ff3823] uppercase">✱ REALTIME KBM</div>
                        <h3 class="text-xs font-black text-[#121316]">Presensi Tatap Muka</h3>
                    </div>
                    <a href="{{ route('guru.lms.meeting.attendance', $course->id) }}" target="_blank" class="btn-tactile-yellow text-[10px] font-mono-code font-bold px-3 py-1.5 rounded-xl flex items-center gap-1.5">
                        <i class="fas fa-file-invoice"></i> Rekap Sesi
                    </a>
                </div>

                {{-- List Peserta (Vibrant Poster Card Style) --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-2.5 graph-paper-box" id="attendees-list">
                    <div class="text-center text-xs text-[#777] py-12 poster-card bg-white p-6">
                        <i class="fas fa-spinner animate-spin text-2xl mb-2 text-[#4f46e5] block"></i>
                        <p class="font-mono-code font-bold">Memuat daftar siswa hadir...</p>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    {{-- 4. MODAL KONFIRMASI AKHIRI KELAS (TACTILE NEO-BRUTALIST KHAS PEMBDAHUB) --}}
    <div id="end-meeting-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-[#121316]/75 backdrop-blur-xs p-4 hidden">
        <div class="bg-white border-2 border-[#121316] rounded-3xl max-w-md w-full p-6 shadow-[8px_8px_0px_#ff3823] space-y-4 relative">
            <span class="absolute top-2.5 left-3 text-[#999] font-mono-code text-xs select-none">⌜</span>
            <span class="absolute top-2.5 right-3 text-[#999] font-mono-code text-xs select-none">⌝</span>
            <span class="absolute bottom-2.5 left-3 text-[#999] font-mono-code text-xs select-none">⌞</span>
            <span class="absolute bottom-2.5 right-3 text-[#999] font-mono-code text-xs select-none">⌟</span>

            <div class="w-12 h-12 rounded-2xl bg-[#ff3823] border-2 border-[#121316] text-white flex items-center justify-center text-xl shadow-[3px_3px_0px_#121316]">
                <i class="fas fa-power-off"></i>
            </div>
            <div>
                <div class="text-[10px] font-mono-code font-black text-[#ff3823] uppercase">✱ KONFIRMASI SELESAI KBM</div>
                <h3 class="text-lg font-black text-[#121316]">Akhiri Sesi PembdaTEMU?</h3>
                <p class="text-xs text-[#555] mt-1.5 leading-relaxed font-medium">
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
                        <i class="fas fa-check"></i> Ya, Akhiri PembdaTEMU
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- 5. TOAST NOTIFICATION --}}
    <div id="copy-toast" class="fixed bottom-6 right-6 z-50 bg-[#121316] text-[#fde047] px-4 py-2.5 rounded-xl text-xs font-mono-code font-bold shadow-[4px_4px_0px_#ff3823] border-2 border-[#121316] flex items-center gap-2 transform translate-y-12 opacity-0 transition-all duration-300 pointer-events-none">
        <i class="fas fa-circle-check text-[#10b981] text-base"></i>
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

        // Zoom Layout State Variables
        let currentLayout    = 'gallery'; // 'gallery' | 'speaker' | 'sidebyside'
        let currentPinnedUid = 'local';   // 'local' | remote uid
        let attendeesMap     = {};        // user.uid => student name

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
                    if (micIcon) micIcon.className = 'fas fa-microphone text-[#10b981] text-xs';
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
                    if (micIcon) micIcon.className = 'fas fa-microphone-slash text-[#ff3823] text-xs';
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
            tile.className = 'video-tile group relative rounded-3xl overflow-hidden bg-slate-900 border-2.5 border-[#121316] shadow-[4px_4px_0px_#121316] flex items-center justify-center transition-all duration-300';
            
            const rawName = attendeesMap[user.uid] || `Siswa #${user.uid}`;
            const shortName = rawName.split(' ')[0];

            tile.innerHTML = `
                <div id="remote-${user.uid}" class="w-full h-full relative"></div>
                <div id="remote-placeholder-${user.uid}" class="absolute inset-0 flex flex-col items-center justify-center bg-[#121316] text-slate-300 hidden pointer-events-none">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-cyan-500 to-indigo-600 flex items-center justify-center text-2xl font-black text-white mb-2 shadow-lg border-2 border-white/20">
                        ${shortName.charAt(0)}
                    </div>
                    <p class="text-xs font-mono-code font-bold text-[#fde047]">${shortName}</p>
                </div>
                {{-- BADGE NAMA SISWA: TEPAT DI POJOK KIRI ATAS - 100% AMAN DARI MENU BAR BAWAH --}}
                <div class="absolute top-4 left-4 z-20 flex items-center gap-2 bg-[#121316]/95 backdrop-blur-md px-3 py-1.5 rounded-xl text-xs font-black text-white border-2 border-[#121316] shadow-[3px_3px_0px_#2563eb]" title="${rawName}">
                    <span class="w-2 h-2 rounded-full bg-[#10b981] animate-pulse"></span>
                    <i id="tile-mic-${user.uid}" class="fas fa-microphone text-[#10b981] text-xs"></i>
                    <span id="tile-name-${user.uid}" class="truncate max-w-[140px] text-[#fde047] font-mono-code font-bold">${shortName}</span>
                    <span class="px-2 py-0.5 rounded-md bg-[#2563eb] text-white text-[9px] font-mono-code font-black uppercase tracking-wider">SISWA</span>
                </div>
                <div class="absolute top-4 right-4 z-20 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button onclick="pinSpeaker('${user.uid}')" type="button" class="p-2 rounded-xl bg-white hover:bg-[#fde047] text-[#121316] border-2 border-[#121316] text-xs shadow-[2px_2px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 transition" title="Fokuskan / Spotlight">
                        <i class="fas fa-thumbtack text-xs"></i>
                    </button>
                </div>
            `;
            return tile;
        }

        // Susun ulang posisi tile video berdasarkan mode tata letak Zoom
        function rearrangeTiles() {
            const isSpeaker = (currentLayout === 'speaker');
            const galleryStage = document.getElementById('stage-gallery-wrapper');
            const speakerStage = document.getElementById('stage-speaker-wrapper');
            const galleryGrid = document.getElementById('gallery-grid');
            const filmstrip = document.getElementById('speaker-filmstrip');
            const mainStage = document.getElementById('speaker-main-stage');
            const soloBanner = document.getElementById('solo-teacher-banner');

            const allTiles = Array.from(document.querySelectorAll('.video-tile'));
            const total = allTiles.length;

            // Tampilkan status solo jika guru masih sendirian
            if (soloBanner) {
                if (total <= 1) {
                    soloBanner.classList.remove('hidden');
                } else {
                    soloBanner.classList.add('hidden');
                }
            }

            if (isSpeaker) {
                if (galleryStage) galleryStage.classList.add('hidden');
                if (speakerStage) {
                    speakerStage.classList.remove('hidden');
                    speakerStage.classList.add('flex');
                }

                const pinnedId = (currentPinnedUid === 'local') ? 'video-tile-local' : `video-tile-${currentPinnedUid}`;
                let pinnedTile = document.getElementById(pinnedId);
                if (!pinnedTile && allTiles.length > 0) {
                    pinnedTile = allTiles[0];
                    currentPinnedUid = (pinnedTile.id === 'video-tile-local') ? 'local' : pinnedTile.id.replace('video-tile-', '');
                }

                if (pinnedTile && mainStage) {
                    mainStage.appendChild(pinnedTile);
                    pinnedTile.className = 'video-tile relative rounded-3xl overflow-hidden bg-slate-900 border-3 border-[#ff3823] shadow-[6px_6px_0px_#121316] flex items-center justify-center w-full h-full max-h-[82vh] transition-all pinned-speaker';
                    pinnedTile.onclick = null;
                }

                allTiles.forEach(tile => {
                    if (tile !== pinnedTile && filmstrip) {
                        filmstrip.appendChild(tile);
                        tile.className = 'video-tile filmstrip-tile relative rounded-2xl overflow-hidden bg-slate-900 border-2 border-[#121316] shadow-[2px_2px_0px_#121316] flex items-center justify-center aspect-video cursor-pointer transition-all hover:scale-105';
                        tile.classList.remove('pinned-speaker');
                        tile.onclick = () => pinSpeaker(tile.id === 'video-tile-local' ? 'local' : tile.id.replace('video-tile-', ''));
                    }
                });
            } else {
                // Mode Galeri atau Mode Berdampingan (KBM)
                if (speakerStage) {
                    speakerStage.classList.add('hidden');
                    speakerStage.classList.remove('flex');
                }
                if (galleryStage) galleryStage.classList.remove('hidden');

                allTiles.forEach(tile => {
                    if (galleryGrid) galleryGrid.appendChild(tile);
                    tile.classList.remove('filmstrip-tile', 'pinned-speaker');
                    tile.onclick = null;
                });

                if (galleryGrid) {
                    if (total <= 1) {
                        galleryGrid.className = 'w-full h-full flex items-center justify-center p-1 sm:p-2';
                        if (allTiles[0]) {
                            allTiles[0].className = 'video-tile relative rounded-3xl overflow-hidden bg-slate-900 border-2.5 border-[#121316] shadow-[5px_5px_0px_#121316] flex items-center justify-center w-full h-full max-h-[82vh] transition-all';
                        }
                    } else if (total === 2) {
                        galleryGrid.className = 'w-full h-full max-h-[82vh] grid grid-cols-1 md:grid-cols-2 gap-3.5 items-center justify-center p-2';
                        allTiles.forEach(t => t.className = 'video-tile relative rounded-3xl overflow-hidden bg-slate-900 border-2 border-[#121316] shadow-[4px_4px_0px_#121316] flex items-center justify-center w-full h-full max-h-[75vh] aspect-video transition-all');
                    } else if (total <= 4) {
                        galleryGrid.className = 'w-full h-full max-h-[82vh] grid grid-cols-2 grid-rows-2 gap-3 items-center justify-center p-2';
                        allTiles.forEach(t => t.className = 'video-tile relative rounded-2xl overflow-hidden bg-slate-900 border-2 border-[#121316] shadow-[3px_3px_0px_#121316] flex items-center justify-center w-full h-full aspect-video transition-all');
                    } else if (total <= 6) {
                        galleryGrid.className = 'w-full h-full max-h-[82vh] grid grid-cols-3 grid-rows-2 gap-2.5 items-center justify-center p-2';
                        allTiles.forEach(t => t.className = 'video-tile relative rounded-2xl overflow-hidden bg-slate-900 border-2 border-[#121316] shadow-[3px_3px_0px_#121316] flex items-center justify-center w-full h-full aspect-video transition-all');
                    } else {
                        galleryGrid.className = 'w-full h-full max-h-[82vh] grid grid-cols-3 md:grid-cols-4 gap-2.5 overflow-y-auto p-2';
                        allTiles.forEach(t => t.className = 'video-tile relative rounded-2xl overflow-hidden bg-slate-900 border-2 border-[#121316] shadow-[3px_3px_0px_#121316] flex items-center justify-center w-full aspect-video transition-all');
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
            document.getElementById('mic-icon').className = isMuted
                ? 'fas fa-microphone-slash text-base text-white'
                : 'fas fa-microphone text-base text-white';
            const btn = document.getElementById('btn-toggle-mic');
            btn.className = isMuted
                ? 'group relative p-3 rounded-xl bg-[#ff3823] hover:bg-red-600 text-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316] transition active:translate-x-0.5 active:translate-y-0.5'
                : 'group relative p-3 rounded-xl bg-[#10b981] hover:bg-emerald-600 text-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316] transition active:translate-x-0.5 active:translate-y-0.5';
        }

        async function toggleCam() {
            if (!localVideoTrack) return;
            isCamOff = !isCamOff;
            await localVideoTrack.setMuted(isCamOff);
            document.getElementById('cam-icon').className = isCamOff
                ? 'fas fa-video-slash text-base text-white'
                : 'fas fa-video text-base text-white';
            const btn = document.getElementById('btn-toggle-cam');
            btn.className = isCamOff
                ? 'group relative p-3 rounded-xl bg-[#ff3823] hover:bg-red-600 text-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316] transition active:translate-x-0.5 active:translate-y-0.5'
                : 'group relative p-3 rounded-xl bg-[#0891b2] hover:bg-cyan-600 text-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316] transition active:translate-x-0.5 active:translate-y-0.5';
        }

        async function toggleScreenShare() {
            if (!isScreenSharing) {
                try {
                    localScreenTrack = await AgoraRTC.createScreenVideoTrack();
                    await agoraClient.unpublish(localVideoTrack);
                    await agoraClient.publish(localScreenTrack);
                    localScreenTrack.play('agora-local-video');
                    isScreenSharing = true;
                    document.getElementById('btn-screenshare').classList.add('bg-[#10b981]', 'shadow-[3px_3px_0px_#121316]');
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
            const btn = document.getElementById('btn-screenshare');
            if (btn) btn.classList.remove('bg-[#10b981]', 'shadow-[3px_3px_0px_#121316]');
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
                    <div class="flex flex-col items-center justify-center h-full text-slate-300 p-6 text-center">
                        <div class="w-16 h-16 rounded-2xl bg-[#ff3823] border-2 border-white flex items-center justify-center text-white text-3xl mb-3 shadow-lg">
                            <i class="fas fa-video-slash"></i>
                        </div>
                        <p class="text-sm font-black text-white font-mono-code">Kamera tidak dapat diakses</p>
                        <p class="text-xs mt-1 text-slate-400 font-mono-code">${msg}</p>
                        <p class="text-xs mt-3 text-[#fde047] font-bold">Pastikan AGORA_APP_ID sudah diisi di .env dan browser diizinkan mengakses kamera/mikrofon.</p>
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
            const checkGal   = document.getElementById('check-gallery');
            const checkSpk   = document.getElementById('check-speaker');
            const checkSbs   = document.getElementById('check-sidebyside');

            if (checkGal) checkGal.classList.add('hidden');
            if (checkSpk) checkSpk.classList.add('hidden');
            if (checkSbs) checkSbs.classList.add('hidden');

            if (mode === 'speaker') {
                if (iconEl) iconEl.className = 'fas fa-user-tie text-xs';
                if (nameEl) nameEl.textContent = 'Pembicara';
                if (headerIcon) headerIcon.className = 'fas fa-user-tie';
                if (headerName) headerName.textContent = 'Pembicara';
                if (checkSpk) checkSpk.classList.remove('hidden');
            } else if (mode === 'sidebyside') {
                if (iconEl) iconEl.className = 'fas fa-columns text-xs';
                if (nameEl) nameEl.textContent = 'Berdampingan';
                if (headerIcon) headerIcon.className = 'fas fa-columns';
                if (headerName) headerName.textContent = 'Berdampingan';
                if (checkSbs) checkSbs.classList.remove('hidden');
            } else {
                if (iconEl) iconEl.className = 'fas fa-table-cells text-xs';
                if (nameEl) nameEl.textContent = 'Galeri';
                if (headerIcon) headerIcon.className = 'fas fa-table-cells';
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
                if (fsIcon) fsIcon.className = 'fas fa-compress';
                if (fsText) fsText.textContent = 'Keluar Layar Penuh';
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
                if (fsIcon) fsIcon.className = 'fas fa-expand';
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
                    btn.className = 'flex-1 py-2 px-3 text-xs font-mono-code font-black transition-all flex items-center justify-center gap-2 rounded-xl bg-[#121316] text-[#fde047] border-2 border-[#121316] shadow-[2px_2px_0px_#ff3823]';
                    content.classList.remove('hidden');
                } else {
                    btn.className = 'flex-1 py-2 px-3 text-xs font-mono-code font-bold transition-all flex items-center justify-center gap-2 rounded-xl bg-white text-[#121316] border-2 border-[#121316] hover:bg-[#faf3e0]';
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
            
            // Simpan gambar saat ini
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

            // Touch support untuk tablet / layar sentuh
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
                btnPen.className = 'p-2 rounded-xl bg-[#4f46e5] text-white text-xs font-bold border-2 border-black shadow-[2px_2px_0px_#121316]';
                btnEraser.className = 'p-2 rounded-xl bg-white hover:bg-slate-100 text-[#121316] border-2 border-[#121316] text-xs font-bold shadow-[2px_2px_0px_#121316]';
            } else {
                btnEraser.className = 'p-2 rounded-xl bg-[#ff3823] text-white text-xs font-bold border-2 border-black shadow-[2px_2px_0px_#121316]';
                btnPen.className = 'p-2 rounded-xl bg-white hover:bg-slate-100 text-[#121316] border-2 border-[#121316] text-xs font-bold shadow-[2px_2px_0px_#121316]';
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
            container.className = `flex-1 relative overflow-hidden cursor-crosshair m-2.5 rounded-2xl border-2 border-[#121316] shadow-[3px_3px_0px_#121316] canvas-${bg}`;
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
                iconEl.className = 'far fa-file-pdf';
            } else if (type === 'video') {
                iconEl.className = 'far fa-play-circle';
            } else {
                iconEl.className = 'far fa-file-lines';
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
                            <div class="text-center py-10 px-4 poster-card bg-white p-6">
                                <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-[#ede9df] border-2 border-[#121316] flex items-center justify-center text-[#ff3823] text-2xl shadow-[3px_3px_0px_#121316]">
                                    <i class="fas fa-user-clock"></i>
                                </div>
                                <h4 class="text-xs font-mono-code font-black text-[#121316] uppercase">Belum Ada Siswa Hadir</h4>
                                <p class="text-[11px] text-[#555] mt-1.5 leading-relaxed font-medium">Bagikan tautan sesi ini kepada siswa agar mereka segera masuk ke ruang PembdaTEMU.</p>
                                <div class="mt-4">
                                    <button onclick="copyMeetingLink()" type="button" class="btn-tactile-yellow text-xs font-mono-code font-black px-4 py-2 rounded-xl inline-flex items-center gap-2">
                                        <i class="fas fa-copy"></i>
                                        <span>Salin Tautan WhatsApp</span>
                                    </button>
                                </div>
                            </div>
                        `;
                        return;
                    }

                    let html = '';
                    const avatarGradients = [
                        'from-pink-500 to-rose-500',
                        'from-cyan-500 to-blue-500',
                        'from-emerald-500 to-teal-500',
                        'from-violet-500 to-purple-500',
                        'from-amber-500 to-orange-500'
                    ];

                    data.attendees.forEach((student, idx) => {
                        const grad = avatarGradients[idx % avatarGradients.length];
                        html += `
                            <div class="flex items-center justify-between p-3 rounded-2xl bg-white border-2 border-[#121316] shadow-[3px_3px_0px_#121316] hover:translate-x-0.5 transition-all text-xs">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr ${grad} flex items-center justify-center text-xs font-black text-white border-2 border-[#121316] shadow-xs flex-shrink-0">
                                        ${student.initials}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-black text-[#121316] truncate leading-snug">${student.name}</p>
                                        <span class="text-[10px] text-[#555] font-mono-code font-bold flex items-center gap-1">
                                            <i class="far fa-clock text-[#2563eb]"></i> Masuk: ${student.joined_at}
                                        </span>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-mono-code font-black bg-[#10b981] border-2 border-[#121316] text-white shadow-xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span> HADIR
                                </span>
                            </div>
                        `;
                    });
                    listEl.innerHTML = html;

                    // Update nama siswa pada badge video tile jika cocok dengan user_id
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
