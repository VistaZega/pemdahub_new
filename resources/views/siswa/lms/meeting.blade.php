<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PembdaTEMU Siswa: {{ $course->course_name ?? $course->name }} - PembdaHUB</title>
    
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
    </style>
</head>
<body class="bg-[#faf8f5] text-[#121316] h-screen flex flex-col overflow-hidden select-none font-sans antialiased">

    {{-- 1. SMART CLASSROOM HEADER (SISWA - PEMBDAHUB HOMEPAGE TACTILE STYLE) --}}
    <header class="bg-[#faf8f5] border-b-2 border-[#121316] px-4 sm:px-6 py-2.5 flex items-center justify-between gap-3 z-30 flex-shrink-0 shadow-xs">
        
        {{-- Brand, Unit & Info Kelas --}}
        <div class="flex items-center gap-3 min-w-0">
            <div class="flex items-center gap-1.5 flex-shrink-0">
                <a href="{{ route('siswa.dashboard') }}" title="Kembali ke Dashboard Siswa" class="hover:scale-105 transition-transform flex-shrink-0">
                    <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo PembdaHUB" class="w-9 h-9 object-contain rounded-xl border-2 border-[#121316] shadow-[2px_2px_0px_#2563eb] bg-white p-0.5">
                </a>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-[#2563eb] text-white border border-[#121316] uppercase tracking-wider shadow-[2px_2px_0px_#121316]">
                        <span class="w-2 h-2 rounded-full bg-[#fde047] animate-ping"></span> ✱ PembdaTEMU SISWA
                    </span>
                    <span class="px-2 py-0.5 rounded-md bg-[#fde047] text-[#121316] text-[10px] font-mono-code font-extrabold border border-[#121316] hidden md:inline truncate">
                        {{ $course->school?->name ?? 'Perguruan Pembda' }}
                    </span>
                </div>
                <h1 class="text-sm sm:text-base font-black text-[#121316] truncate leading-tight mt-1 flex items-center gap-1.5">
                    <span class="highlight-marker">{{ $course->course_name ?? $course->name }}</span>
                    @if($course->teacher && $course->teacher->user)
                        <span class="text-xs font-bold text-[#555] hidden sm:inline">• Guru: {{ $course->teacher->user->name }}</span>
                    @endif
                </h1>
            </div>
        </div>

        {{-- Center Status Widget: Durasi Hadir & Sinyal (JetBrains Mono Tactile) --}}
        <div class="hidden lg:flex items-center gap-3 bg-[#121316] text-white border-2 border-[#121316] rounded-2xl px-4 py-1.5 shadow-[3px_3px_0px_#2563eb]">
            <div class="flex items-center gap-2 text-xs font-mono-code font-bold text-[#fde047]" title="Durasi kehadiran Anda di kelas ini">
                <i class="far fa-clock text-[#fbc02d] animate-spin" style="animation-duration: 8s;"></i>
                <span id="student-timer" class="font-mono-code tracking-widest text-sm text-white font-black">00:00:00</span>
            </div>
            <div class="w-px h-4 bg-white/20"></div>
            <div id="connection-status-badge" class="flex items-center gap-1.5 text-xs font-mono-code font-black text-[#10b981]">
                <span class="w-2 h-2 rounded-full bg-[#10b981] animate-ping"></span>
                <span id="mode-status-text">Koneksi Aktif</span>
            </div>
        </div>

        {{-- Right Controls (Tactile Buttons with Icon Boxes) --}}
        <div class="flex items-center gap-2 flex-shrink-0">
            
            {{-- Tombol Mode Hemat Kuota (Amber Tactile) --}}
            <button onclick="toggleDataSaverMode()" type="button" id="btn-data-saver" class="hidden sm:flex items-center gap-1.5 btn-tactile-white px-3 py-1.5 rounded-xl text-xs font-mono-code font-black active:scale-95" title="Turunkan kualitas video untuk menghemat kuota internet dan memperlancar audio">
                <div class="w-5 h-5 rounded-md bg-[#fbc02d] text-[#121316] flex items-center justify-center text-[10px] border border-[#121316]">
                    <i class="fas fa-bolt"></i>
                </div>
                <span id="data-saver-label">Hemat Kuota</span>
            </button>

            {{-- Tombol Pilihan Tampilan (Zoom View Selector - Amber Tactile) --}}
            <div class="relative inline-block text-left" id="header-view-wrapper">
                <button onclick="toggleViewMenu(event)" type="button" class="flex items-center gap-1.5 btn-tactile-white px-3 py-1.5 rounded-xl text-xs font-mono-code font-black active:scale-95" title="Pilihan Tata Letak Tampilan (Zoom)">
                    <div class="w-5 h-5 rounded-md bg-[#2563eb] text-white flex items-center justify-center text-[10px] border border-[#121316]">
                        <i id="header-view-icon" class="fas fa-user-tie"></i>
                    </div>
                    <span class="hidden md:inline">Tata Letak: <span id="header-view-name" class="text-[#2563eb] font-black">Pembicara</span></span>
                    <i class="fas fa-chevron-down text-[9px] text-[#121316] ml-0.5"></i>
                </button>
            </div>

            {{-- Tombol Buka Materi & Catatan (Indigo Tactile) --}}
            <button onclick="toggleStudentHub()" type="button" id="btn-student-hub" class="flex items-center gap-1.5 btn-tactile-white px-3.5 py-1.5 rounded-xl text-xs font-mono-code font-black active:scale-95" title="Buka modul dan catatan pelajaran">
                <div class="w-5 h-5 rounded-md bg-[#4f46e5] text-white flex items-center justify-center text-[10px] border border-[#121316]">
                    <i class="fas fa-book-open"></i>
                </div>
                <span class="hidden md:inline">Materi & Catatan</span>
            </button>

            {{-- Student Profile Chip --}}
            <div class="hidden xl:flex items-center gap-2 bg-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316] px-2.5 py-1 rounded-xl text-xs text-[#121316]" title="{{ $displayName }}">
                <div class="w-6 h-6 rounded-lg bg-[#2563eb] border border-black flex items-center justify-center text-[11px] font-black text-white uppercase shadow-xs">
                    {{ substr($displayName, 0, 1) }}
                </div>
                <span class="font-mono-code font-black max-w-[100px] truncate text-[#121316]">{{ explode(' ', trim($displayName))[0] }}</span>
            </div>

            {{-- Tombol Keluar Kelas (Crimson Tactile) --}}
            <button onclick="openLeaveModal()" type="button" class="btn-tactile-red px-3.5 sm:px-4 py-1.5 rounded-xl text-xs font-mono-code font-black flex items-center gap-1.5 active:scale-95">
                <i class="fas fa-door-open text-xs"></i>
                <span>Keluar</span>
            </button>
        </div>
    </header>

    {{-- 2. MAIN WORKSPACE CONTAINER --}}
    <div class="flex-1 flex flex-row overflow-hidden relative" style="height: calc(100vh - 65px);">
        
        {{-- Conference Arena (Agora Video Stage with Dotted Grid Graph Paper Texture) --}}
        <main id="conference-panel" class="flex-1 relative graph-paper-box flex flex-col h-full overflow-hidden transition-all duration-150">
            
            {{-- FLOATING ZOOM-STYLE VIEW SELECTOR (POJOK KANAN ATAS VIDEO) --}}
            <div class="absolute top-4 right-4 z-30 flex items-center gap-2">
                <div class="relative" id="video-view-dropdown-wrapper">
                    <button id="btn-view-toggle" onclick="toggleViewMenu(event)" type="button" class="flex items-center gap-2 btn-tactile-white px-3.5 py-1.5 rounded-xl text-xs font-mono-code font-black transition active:scale-95" title="Ganti Tata Letak Video (Zoom)">
                        <div class="w-5 h-5 rounded-md bg-[#2563eb] text-white flex items-center justify-center text-[10px] border border-[#121316]">
                            <i id="current-view-icon" class="fas fa-user-tie"></i>
                        </div>
                        <span>Tata Letak: <span id="current-view-name" class="text-[#2563eb] font-black">Pembicara</span></span>
                        <i class="fas fa-chevron-down text-[9px] text-[#121316]"></i>
                    </button>

                    <div id="view-dropdown-menu" class="hidden absolute right-0 mt-2 w-60 rounded-2xl bg-white border-2 border-[#121316] shadow-[5px_5px_0px_#121316] p-2.5 z-50 text-[#121316] transition-all">
                        <div class="px-3 py-1 text-[10px] font-mono-code font-extrabold uppercase tracking-wider text-[#777] border-b border-[#e2ded5] mb-1.5 flex items-center justify-between">
                            <span>TATA LETAK ZOOM</span>
                            <span>⌜ ⌟</span>
                        </div>
                        
                        <button onclick="changeStudentLayout('speaker')" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-mono-code font-bold hover:bg-[#faf3e0] text-[#121316] flex items-center justify-between transition-colors border border-transparent hover:border-[#121316]">
                            <span class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-md bg-[#ff3823] text-white flex items-center justify-center text-[10px] border border-black"><i class="fas fa-user-tie"></i></span>
                                <span>Tampilan Pembicara</span>
                            </span>
                            <i id="check-speaker" class="fas fa-check text-[#ff3823] text-xs font-black"></i>
                        </button>

                        <button onclick="changeStudentLayout('gallery')" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-mono-code font-bold hover:bg-[#faf3e0] text-[#121316] flex items-center justify-between transition-colors border border-transparent hover:border-[#121316]">
                            <span class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-md bg-[#2563eb] text-white flex items-center justify-center text-[10px] border border-black"><i class="fas fa-table-cells"></i></span>
                                <span>Tampilan Galeri</span>
                            </span>
                            <i id="check-gallery" class="fas fa-check text-[#2563eb] text-xs hidden font-black"></i>
                        </button>

                        <button onclick="changeStudentLayout('sidebyside')" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-mono-code font-bold hover:bg-[#faf3e0] text-[#121316] flex items-center justify-between transition-colors border border-transparent hover:border-[#121316]">
                            <span class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-md bg-[#10b981] text-white flex items-center justify-center text-[10px] border border-black"><i class="fas fa-columns"></i></span>
                                <span>Berdampingan (Materi)</span>
                            </span>
                            <i id="check-sidebyside" class="fas fa-check text-[#10b981] text-xs hidden font-black"></i>
                        </button>

                        <div class="h-px bg-[#e2ded5] my-1.5"></div>

                        <button onclick="toggleStudentFullscreen()" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-mono-code font-bold hover:bg-[#faf3e0] text-[#121316] flex items-center gap-2.5 transition-colors border border-transparent hover:border-[#121316]">
                            <span class="w-5 h-5 rounded-md bg-[#fbc02d] text-black flex items-center justify-center text-[10px] border border-black"><i id="menu-fs-icon" class="fas fa-expand"></i></span>
                            <span id="menu-fs-text">Layar Penuh (F11)</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- 1. SPEAKER VIEW STAGE (Default Siswa: Focus ke Guru/Host) --}}
            {{-- Catatan: pb-28 memastikan video tile TIDAK PERNAH tertutup oleh dock mengambang di bawah --}}
            <div id="stage-speaker-wrapper" class="flex-1 flex flex-col h-full overflow-hidden p-3 pb-28 gap-2.5 relative min-h-0">
                {{-- Top Filmstrip Bar: Thumbnail peserta lain --}}
                <div id="speaker-filmstrip" class="flex items-center gap-2.5 overflow-x-auto p-2 h-28 shrink-0 bg-white/80 rounded-2xl border-2 border-[#121316] shadow-[3px_3px_0px_#121316] backdrop-blur-sm scroll-smooth">
                    {{-- Non-pinned tiles live here --}}
                </div>
                {{-- Main Spotlight Screen: 1 Peserta Terfokus (Guru/Siswa) --}}
                <div id="speaker-main-stage" class="flex-1 flex items-center justify-center overflow-hidden min-h-0 relative p-1">
                    {{-- Pinned tile lives here --}}
                </div>
            </div>

            {{-- 2. GALLERY VIEW STAGE (Active when currentLayout === 'gallery' or 'sidebyside') --}}
            <div id="stage-gallery-wrapper" class="hidden flex-1 items-center justify-center overflow-hidden h-full p-2.5 sm:p-4 pb-28 min-h-0 relative">
                <div id="gallery-grid" class="w-full h-full flex items-center justify-center gap-3.5 transition-all duration-300">
                    {{-- All tiles reside here in gallery mode --}}
                </div>
            </div>

            {{-- MASTER INITIAL TILES CONTAINER (Akan dipindahkan oleh rearrangeStudentTiles) --}}
            <div id="master-tiles-pool" class="hidden">
                {{-- 1. Video Tile GURU (Host) --}}
                <div id="video-tile-teacher" class="video-tile group relative rounded-3xl overflow-hidden bg-slate-900 border-2.5 border-[#121316] shadow-[4px_4px_0px_#121316] flex items-center justify-center w-full h-full max-h-[82vh] transition-all">
                    <div id="agora-teacher-video" class="w-full h-full relative"></div>
                    <div id="teacher-waiting-placeholder" class="absolute inset-0 flex flex-col items-center justify-center text-slate-300 p-6 text-center bg-[#121316] pointer-events-none">
                        <div class="w-16 h-16 rounded-2xl bg-[#ff3823] border-2 border-white flex items-center justify-center text-white text-3xl mb-3 shadow-lg animate-pulse">
                            <i class="fas fa-chalkboard-user"></i>
                        </div>
                        <h3 class="text-base font-black text-white font-mono-code">Menghubungkan ke Siaran Guru...</h3>
                        <p class="text-xs text-slate-400 mt-1 max-w-sm font-medium">Guru sedang menyiapkan siaran. Video & audio guru akan otomatis tampil saat guru mulai mengajar.</p>
                        <span class="mt-3 inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-mono-code font-bold bg-[#fbc02d] text-[#121316] border border-black shadow-xs">
                            <span class="w-2 h-2 rounded-full bg-[#ff3823] animate-ping"></span> Live Class
                        </span>
                    </div>
                    
                    {{-- 🎯 PERBAIKAN: BADGE NAMA GURU DIPINDAH KE POJOK KIRI ATAS --}}
                    {{-- Dijamin 100% TIDAK AKAN PERNAH TERTUTUP oleh menu bar di bawah! --}}
                    <div class="absolute top-4 left-4 z-20 flex items-center gap-2 bg-[#121316]/95 backdrop-blur-md px-3 py-1.5 rounded-xl text-xs font-black text-white border-2 border-[#121316] shadow-[3px_3px_0px_#ff3823]" title="{{ $course->teacher?->user?->name ?? 'Guru Pengajar' }}">
                        <span class="w-2 h-2 rounded-full bg-[#10b981] animate-pulse"></span>
                        <i id="teacher-audio-indicator" class="fas fa-volume-high text-[#10b981] text-xs"></i>
                        <span class="truncate max-w-[150px] text-[#fde047] font-mono-code font-bold">{{ explode(' ', trim($course->teacher?->user?->name ?? 'Guru'))[0] }}</span>
                        <span class="px-2 py-0.5 rounded-md bg-[#ff3823] text-white text-[9px] font-mono-code font-black uppercase tracking-wider">GURU</span>
                    </div>

                    <div class="absolute top-4 right-4 z-20 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button onclick="pinStudentSpeaker('teacher')" type="button" class="p-2 rounded-xl bg-white hover:bg-[#fde047] text-[#121316] border-2 border-[#121316] text-xs shadow-[2px_2px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 transition" title="Fokuskan / Spotlight">
                            <i class="fas fa-thumbtack text-xs"></i>
                        </button>
                    </div>
                </div>

                {{-- 2. Video Tile SAYA (Siswa Sendiri) --}}
                <div id="video-tile-local" class="video-tile group relative rounded-3xl overflow-hidden bg-slate-900 border-2.5 border-[#121316] shadow-[4px_4px_0px_#121316] flex items-center justify-center aspect-video transition-all">
                    <div id="agora-student-video" class="w-full h-full relative"></div>
                    <div id="student-cam-off-placeholder" class="absolute inset-0 flex flex-col items-center justify-center bg-[#121316] text-slate-300 text-xs pointer-events-none">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-cyan-500 to-indigo-600 border-2 border-white flex items-center justify-center text-2xl font-black text-white mb-2 shadow-lg">
                            {{ substr($displayName, 0, 1) }}
                        </div>
                        <span class="text-[11px] font-mono-code font-bold text-[#fde047]">Kamera Nonaktif</span>
                    </div>

                    {{-- 🎯 PERBAIKAN: BADGE NAMA SAYA DIPINDAH KE POJOK KIRI ATAS --}}
                    {{-- Dijamin 100% TIDAK AKAN PERNAH TERTUTUP oleh menu bar di bawah! --}}
                    <div class="absolute top-4 left-4 z-20 flex items-center gap-2 bg-[#121316]/95 backdrop-blur-md px-3 py-1.5 rounded-xl text-xs font-black text-white border-2 border-[#121316] shadow-[3px_3px_0px_#2563eb]" title="{{ $displayName }}">
                        <i id="tile-mic-local" class="fas fa-microphone-slash text-[#ff3823] text-xs"></i>
                        <span class="truncate max-w-[140px] text-[#fde047] font-mono-code font-bold">Saya ({{ explode(' ', trim($displayName))[0] }})</span>
                        <span class="px-2 py-0.5 rounded-md bg-[#2563eb] text-white text-[9px] font-mono-code font-black uppercase tracking-wider">SISWA</span>
                    </div>

                    <div class="absolute top-4 right-4 z-20 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button onclick="pinStudentSpeaker('local')" type="button" class="p-2 rounded-xl bg-white hover:bg-[#fde047] text-[#121316] border-2 border-[#121316] text-xs shadow-[2px_2px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 transition" title="Fokuskan / Spotlight">
                            <i class="fas fa-thumbtack text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Floating Student Action Dock (Neo-Tactile Island Khas PembdaHUB) --}}
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2 sm:gap-2.5 bg-white border-2 border-[#121316] shadow-[5px_5px_0px_#121316] px-3.5 sm:px-5 py-2.5 rounded-2xl sm:rounded-full">
                
                {{-- Toggle Mic Siswa --}}
                <button onclick="toggleStudentMic()" id="btn-toggle-mic" type="button" class="group relative p-3 rounded-xl bg-[#ff3823] text-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316] transition active:translate-x-0.5 active:translate-y-0.5" title="Aktifkan / Matikan Mikrofon">
                    <i id="mic-icon" class="fas fa-microphone-slash text-base text-white"></i>
                    <span class="absolute -top-9 left-1/2 -translate-x-1/2 bg-[#121316] text-[#fde047] text-[10px] font-mono-code font-black px-2.5 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap border border-black shadow-md">Mic</span>
                </button>

                {{-- Toggle Kamera Siswa --}}
                <button onclick="toggleStudentCam()" id="btn-toggle-cam" type="button" class="group relative p-3 rounded-xl bg-[#ff3823] text-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316] transition active:translate-x-0.5 active:translate-y-0.5" title="Aktifkan / Matikan Kamera">
                    <i id="cam-icon" class="fas fa-video-slash text-base text-white"></i>
                    <span class="absolute -top-9 left-1/2 -translate-x-1/2 bg-[#121316] text-[#fde047] text-[10px] font-mono-code font-black px-2.5 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap border border-black shadow-md">Kamera</span>
                </button>

                <div class="w-px h-6 bg-[#121316]"></div>

                {{-- Angkat Tangan (Amber Tactile) --}}
                <button onclick="studentToggleRaiseHand()" id="btn-raise-hand" type="button" class="flex items-center gap-1.5 btn-tactile-yellow px-3.5 sm:px-4 py-2.5 rounded-xl text-xs font-mono-code font-black active:scale-95" title="Beri tanda ke guru bahwa Anda ingin bertanya">
                    <i class="fas fa-hand text-sm"></i>
                    <span id="raise-hand-label" class="hidden sm:inline">Angkat Tangan</span>
                </button>

                {{-- Buka Materi Cepat (Indigo Tactile) --}}
                <button onclick="openStudentTab('materi')" type="button" class="flex items-center gap-1.5 bg-[#4f46e5] hover:bg-indigo-700 text-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316] px-3.5 sm:px-4 py-2.5 rounded-xl text-xs font-mono-code font-black transition-all active:translate-x-0.5 active:translate-y-0.5" title="Buka materi modul pelajaran">
                    <i class="fas fa-book-open text-sm text-[#fde047]"></i>
                    <span class="hidden sm:inline">Modul</span>
                </button>

                {{-- Buka Catatan (Emerald Tactile) --}}
                <button onclick="openStudentTab('notes')" type="button" class="flex items-center gap-1.5 bg-[#10b981] hover:bg-emerald-600 text-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316] px-3.5 sm:px-4 py-2.5 rounded-xl text-xs font-mono-code font-black transition-all active:translate-x-0.5 active:translate-y-0.5" title="Buka papan catatan pribadi">
                    <i class="fas fa-pen-nib text-sm text-[#fde047]"></i>
                    <span class="hidden sm:inline">Catatan</span>
                </button>
            </div>
        </main>

        {{-- Resize Handle Divider --}}
        <div id="resize-handler" class="w-[10px] bg-[#ede9df] hover:bg-[#ff3823] cursor-col-resize select-none z-20 flex items-center justify-center relative transition-colors duration-150 group border-x border-[#121316]" title="Geser untuk mengatur lebar panel materi">
            <div class="w-4 h-12 bg-white border-2 border-[#121316] rounded-md flex items-center justify-center shadow-[2px_2px_0px_#121316] pointer-events-none">
                <i class="fas fa-grip-lines-vertical text-[10px] text-[#121316]"></i>
            </div>
        </div>

        {{-- Resize Overlay --}}
        <div id="resize-overlay" class="fixed inset-0 z-40 hidden cursor-col-resize"></div>

        {{-- 3. STUDENT LEARNING COMPANION HUB (TACTILE BINDER STYLE KHAS PEMBDAHUB) --}}
        <aside id="student-hub" class="w-[480px] max-w-[90vw] border-l-2 border-[#121316] bg-[#faf8f5] flex flex-col h-full z-15 shadow-2xl relative">
            
            {{-- Tabs Header Bar --}}
            <div class="border-b-2 border-[#121316] flex items-center bg-[#ede9df] p-2 gap-2 flex-shrink-0">
                <button id="tab-btn-materi" onclick="switchStudentTab('materi')" type="button" class="flex-1 py-2 px-3 text-xs font-mono-code font-black transition-all flex items-center justify-center gap-1.5 rounded-xl bg-[#121316] text-[#fde047] border-2 border-[#121316] shadow-[2px_2px_0px_#ff3823]">
                    <i class="fas fa-book text-xs text-[#ff3823]"></i>
                    <span>Materi & Modul</span>
                </button>
                <button id="tab-btn-notes" onclick="switchStudentTab('notes')" type="button" class="flex-1 py-2 px-3 text-xs font-mono-code font-bold transition-all flex items-center justify-center gap-1.5 rounded-xl bg-white text-[#121316] border-2 border-[#121316] hover:bg-[#faf3e0]">
                    <i class="fas fa-pen-to-square text-xs text-[#10b981]"></i>
                    <span>Catatan</span>
                </button>
                <button id="tab-btn-friends" onclick="switchStudentTab('friends')" type="button" class="flex-1 py-2 px-3 text-xs font-mono-code font-bold transition-all flex items-center justify-center gap-1.5 rounded-xl bg-white text-[#121316] border-2 border-[#121316] hover:bg-[#faf3e0]">
                    <i class="fas fa-users text-xs text-[#2563eb]"></i>
                    <span>Teman</span>
                </button>
                <button onclick="toggleStudentHub()" type="button" class="p-2 text-[#121316] hover:bg-[#ff3823] hover:text-white rounded-xl border-2 border-[#121316] bg-white transition-colors shadow-xs" title="Tutup panel">
                    <i class="fas fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- TAB 1: MATERI & MODUL --}}
            <div id="tab-content-materi" class="flex-1 flex flex-col overflow-hidden">
                
                {{-- Viewer Preview Box --}}
                <div id="material-preview-box" class="h-1/2 min-h-[220px] border-b-2 border-[#121316] bg-white flex flex-col hidden">
                    <div class="px-4 py-2 border-b-2 border-[#121316] bg-[#faf3e0] flex items-center justify-between">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-6 h-6 rounded-md bg-[#ff3823] text-white flex items-center justify-center text-xs border border-black"><i id="preview-icon" class="far fa-file-pdf"></i></span>
                            <h3 id="preview-title" class="text-xs font-black text-[#121316] truncate font-mono-code">Materi Pelajaran</h3>
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
                        <span class="text-[10px] font-mono-code font-bold text-[#777]">Klik "Buka" untuk menyimak</span>
                    </div>

                    @php
                        $modules = $course->modules()->ordered()->active()->get();
                    @endphp

                    @if($modules->isEmpty())
                        <div class="text-center text-xs text-[#777] py-12 poster-card bg-white p-6">
                            <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-[#ede9df] border-2 border-[#121316] flex items-center justify-center text-[#2563eb] text-2xl shadow-[3px_3px_0px_#121316]">
                                <i class="far fa-folder-open"></i>
                            </div>
                            <h4 class="font-black text-[#121316] text-sm">Belum Ada Materi</h4>
                            <p class="font-medium mt-1">Belum ada modul atau materi di kelas ini.</p>
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
                                            <button onclick="displayStudentMaterial('{{ $safeTitle }}', '{{ $material->material_type }}', '{{ $url }}', '{{ $safeContent }}')" type="button" class="btn-tactile-white text-[10px] font-mono-code font-black px-2.5 py-1 rounded-lg flex items-center gap-1 flex-shrink-0">
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

            {{-- TAB 2: CATATAN SISWA --}}
            <div id="tab-content-notes" class="flex-1 flex flex-col overflow-hidden hidden bg-[#faf8f5]">
                <div class="p-3 bg-white border-b-2 border-[#121316] flex items-center justify-between flex-shrink-0 shadow-xs">
                    <div>
                        <div class="text-[10px] font-mono-code font-black text-[#10b981] uppercase">✱ CATATAN PRIBADI</div>
                        <h3 class="text-xs font-black text-[#121316]">Buku Coretan Pelajaran</h3>
                    </div>
                    <span id="notes-saved-hint" class="text-[10px] font-mono-code font-bold text-[#777]">Otomatis tersimpan</span>
                </div>
                <div class="flex-1 p-3 flex flex-col graph-paper-box">
                    <textarea id="student-notes-area" class="w-full flex-1 bg-white border-2 border-[#121316] rounded-2xl p-4 text-xs text-[#121316] placeholder-slate-400 resize-none outline-none focus:border-[#2563eb] shadow-[3px_3px_0px_#121316] transition-all font-mono-code leading-relaxed" placeholder="Tuliskan poin-poin penjelasan penting dari guru di sini... (Catatan otomatis tersimpan di peramban browser Anda)"></textarea>
                </div>
            </div>

            {{-- TAB 3: TEMAN SEKELAS --}}
            <div id="tab-content-friends" class="flex-1 flex flex-col overflow-hidden hidden bg-[#faf8f5]">
                <div class="p-3 bg-white border-b-2 border-[#121316] flex items-center justify-between flex-shrink-0 shadow-xs">
                    <div>
                        <div class="text-[10px] font-mono-code font-black text-[#2563eb] uppercase">✱ CIVITAS KELAS</div>
                        <h3 class="text-xs font-black text-[#121316]">Teman Sekelas Hadir</h3>
                    </div>
                    <span id="friends-badge-count" class="btn-tactile-yellow text-[10px] font-mono-code font-black px-2.5 py-0.5 rounded-full">0 Online</span>
                </div>
                <div class="flex-1 overflow-y-auto p-4 space-y-2.5 graph-paper-box" id="friends-list">
                    <div class="text-center text-xs text-[#777] py-12 poster-card bg-white p-6">
                        <i class="fas fa-spinner animate-spin text-2xl mb-2 text-[#2563eb] block"></i>
                        <p class="font-mono-code font-bold">Memuat teman sekelas...</p>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    {{-- MODAL KONFIRMASI KELUAR KELAS (TACTILE NEO-BRUTALIST KHAS PEMBDAHUB) --}}
    <div id="leave-meeting-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-[#121316]/75 backdrop-blur-xs p-4 hidden">
        <div class="bg-white border-2 border-[#121316] rounded-3xl max-w-md w-full p-6 shadow-[8px_8px_0px_#ff3823] space-y-4 relative">
            <span class="absolute top-2.5 left-3 text-[#999] font-mono-code text-xs select-none">⌜</span>
            <span class="absolute top-2.5 right-3 text-[#999] font-mono-code text-xs select-none">⌝</span>
            <span class="absolute bottom-2.5 left-3 text-[#999] font-mono-code text-xs select-none">⌞</span>
            <span class="absolute bottom-2.5 right-3 text-[#999] font-mono-code text-xs select-none">⌟</span>

            <div class="w-12 h-12 rounded-2xl bg-[#ff3823] border-2 border-[#121316] text-white flex items-center justify-center text-xl shadow-[3px_3px_0px_#121316]">
                <i class="fas fa-door-open"></i>
            </div>
            <div>
                <div class="text-[10px] font-mono-code font-black text-[#ff3823] uppercase">✱ KELUAR SESI</div>
                <h3 class="text-lg font-black text-[#121316]">Tinggalkan PembdaTEMU?</h3>
                <p class="text-xs text-[#555] mt-1.5 leading-relaxed font-medium">
                    Durasi kehadiran Anda sampai detik ini akan tersimpan di rekap absensi guru. Anda dapat bergabung kembali selama guru masih membuka kelas.
                </p>
            </div>
            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button onclick="closeLeaveModal()" type="button" class="btn-tactile-white px-4 py-2 rounded-xl text-xs font-mono-code font-bold">
                    Batal
                </button>
                <button onclick="executeLeaveMeeting()" type="button" class="btn-tactile-red px-4 py-2 rounded-xl text-xs font-mono-code font-black flex items-center gap-1.5">
                    <i class="fas fa-check"></i> Ya, Tinggalkan Sesi
                </button>
            </div>
        </div>
    </div>

    {{-- AGORA WEB SDK (WebRTC Standalone Tanpa Embed Jitsi) --}}
    <script src="https://download.agora.io/sdk/release/AgoraRTC_N.js"></script>

    <script>
        // Agora Session Variables
        const AGORA_APP_ID  = "{{ $agoraAppId }}";
        const AGORA_TOKEN   = "{{ $agoraToken }}" || null;
        const AGORA_CHANNEL = "{{ $agoraChannel }}";
        const AGORA_UID     = {{ $agoraUid }};
        const COURSE_ID     = {{ $course->id }};
        const TEACHER_USER_ID = {{ $course->teacher?->user_id ?? 0 }};

        let agoraClient      = null;
        let localAudioTrack  = null;
        let localVideoTrack  = null;
        let isMicOn          = false;
        let isCamOn          = false;
        let isRaiseHand      = false;
        let isDataSaver      = false;

        // Zoom Layout State Variables (Siswa)
        let currentLayout    = 'speaker'; // 'speaker' | 'gallery' | 'sidebyside'
        let currentPinnedUid = 'teacher'; // 'teacher' | 'local' | remote uid
        let attendeesMap     = {};        // user.uid => student name

        // ============================================================
        // 1. ZOOM LAYOUT CONTROLLER (SISWA)
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

        function changeStudentLayout(mode) {
            currentLayout = mode;
            updateStudentViewLabel(mode);

            const hub = document.getElementById('student-hub');
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

            rearrangeStudentTiles();
            const menu = document.getElementById('view-dropdown-menu');
            if (menu) menu.classList.add('hidden');
        }

        function updateStudentViewLabel(mode) {
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

            if (mode === 'gallery') {
                if (iconEl) iconEl.className = 'fas fa-table-cells';
                if (nameEl) nameEl.textContent = 'Galeri';
                if (headerIcon) headerIcon.className = 'fas fa-table-cells';
                if (headerName) headerName.textContent = 'Galeri';
                if (checkGal) checkGal.classList.remove('hidden');
            } else if (mode === 'sidebyside') {
                if (iconEl) iconEl.className = 'fas fa-columns';
                if (nameEl) nameEl.textContent = 'Berdampingan';
                if (headerIcon) headerIcon.className = 'fas fa-columns';
                if (headerName) headerName.textContent = 'Berdampingan';
                if (checkSbs) checkSbs.classList.remove('hidden');
            } else {
                if (iconEl) iconEl.className = 'fas fa-user-tie';
                if (nameEl) nameEl.textContent = 'Pembicara';
                if (headerIcon) headerIcon.className = 'fas fa-user-tie';
                if (headerName) headerName.textContent = 'Pembicara';
                if (checkSpk) checkSpk.classList.remove('hidden');
            }
        }

        function toggleStudentFullscreen() {
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

        function pinStudentSpeaker(target) {
            currentPinnedUid = target;
            if (currentLayout !== 'speaker') {
                changeStudentLayout('speaker');
            } else {
                rearrangeStudentTiles();
            }
        }

        // 🎯 BUAT DOM TILE UNTUK VIDEO REMOTE TEMAN SEKELAS
        // BADGE NAMA SISWA DITETAPKAN DI POJOK KIRI ATAS AGAR TIDAK TERTUTUP BAR MENU!
        function createRemoteStudentTile(user) {
            const tile = document.createElement('div');
            tile.id = `video-tile-${user.uid}`;
            tile.className = 'video-tile group relative rounded-3xl overflow-hidden bg-slate-900 border-2.5 border-[#121316] shadow-[4px_4px_0px_#121316] flex items-center justify-center transition-all duration-300';
            
            const rawName = attendeesMap[user.uid] || `Siswa #${user.uid}`;
            const shortName = rawName.split(' ')[0] || rawName;

            tile.innerHTML = `
                <div id="remote-${user.uid}" class="w-full h-full relative"></div>
                <div id="remote-placeholder-${user.uid}" class="absolute inset-0 flex flex-col items-center justify-center bg-[#121316] text-slate-300 hidden pointer-events-none">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-cyan-500 to-indigo-600 flex items-center justify-center text-2xl font-black text-white mb-2 shadow-lg border-2 border-white/20">
                        ${shortName.charAt(0)}
                    </div>
                    <p class="text-xs font-mono-code font-bold text-[#fde047]">${shortName}</p>
                </div>
                {{-- BADGE NAMA SISWA LAIN: TEPAT DI POJOK KIRI ATAS - 100% AMAN DARI MENU BAR BAWAH --}}
                <div class="absolute top-4 left-4 z-20 flex items-center gap-2 bg-[#121316]/95 backdrop-blur-md px-3 py-1.5 rounded-xl text-xs font-black text-white border-2 border-[#121316] shadow-[3px_3px_0px_#2563eb]" title="${rawName}">
                    <span class="w-2 h-2 rounded-full bg-[#10b981] animate-pulse"></span>
                    <i id="tile-mic-${user.uid}" class="fas fa-microphone text-[#10b981] text-xs"></i>
                    <span id="tile-name-${user.uid}" class="truncate max-w-[140px] text-[#fde047] font-mono-code font-bold">${shortName}</span>
                    <span class="px-2 py-0.5 rounded-md bg-[#2563eb] text-white text-[9px] font-mono-code font-black uppercase tracking-wider">SISWA</span>
                </div>
                <div class="absolute top-4 right-4 z-20 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button onclick="pinStudentSpeaker('${user.uid}')" type="button" class="p-2 rounded-xl bg-white hover:bg-[#fde047] text-[#121316] border-2 border-[#121316] text-xs shadow-[2px_2px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 transition" title="Fokuskan / Spotlight">
                        <i class="fas fa-thumbtack text-xs"></i>
                    </button>
                </div>
            `;
            return tile;
        }

        // Susun ulang posisi tile video berdasarkan mode tata letak Zoom (Siswa)
        function rearrangeStudentTiles() {
            const isSpeaker = (currentLayout === 'speaker');
            const galleryStage = document.getElementById('stage-gallery-wrapper');
            const speakerStage = document.getElementById('stage-speaker-wrapper');
            const galleryGrid  = document.getElementById('gallery-grid');
            const filmstrip    = document.getElementById('speaker-filmstrip');
            const mainStage    = document.getElementById('speaker-main-stage');

            // Ambil seluruh tile video yang ada
            const allTiles = Array.from(document.querySelectorAll('.video-tile'));
            const total = allTiles.length;

            if (isSpeaker) {
                if (galleryStage) galleryStage.classList.add('hidden');
                if (speakerStage) {
                    speakerStage.classList.remove('hidden');
                    speakerStage.classList.add('flex');
                }

                // Tentukan tile yang di-pin
                let pinnedId = 'video-tile-teacher';
                if (currentPinnedUid === 'local') {
                    pinnedId = 'video-tile-local';
                } else if (currentPinnedUid !== 'teacher') {
                    pinnedId = `video-tile-${currentPinnedUid}`;
                }

                let pinnedTile = document.getElementById(pinnedId);
                if (!pinnedTile && allTiles.length > 0) {
                    pinnedTile = document.getElementById('video-tile-teacher') || allTiles[0];
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
                        tile.onclick = () => {
                            if (tile.id === 'video-tile-teacher') pinStudentSpeaker('teacher');
                            else if (tile.id === 'video-tile-local') pinStudentSpeaker('local');
                            else pinStudentSpeaker(tile.id.replace('video-tile-', ''));
                        };
                    }
                });
            } else {
                // Mode Galeri atau Mode Berdampingan
                if (speakerStage) {
                    speakerStage.classList.add('hidden');
                    speakerStage.classList.remove('flex');
                }
                if (galleryStage) {
                    galleryStage.classList.remove('hidden');
                    galleryStage.classList.add('flex');
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

            // Pastikan video element terus berjalan setelah direparenting
            document.querySelectorAll('.video-tile video').forEach(v => {
                if (v && v.paused) v.play().catch(() => {});
            });
        }

        // ============================================================
        // 2. INISIALISASI AGORA CLIENT SISWA
        // ============================================================
        async function initAgoraStudent() {
            if (!AGORA_APP_ID) {
                console.error("Agora App ID kosong.");
                return;
            }

            // Tata letak awal siswa
            rearrangeStudentTiles();

            // Inisialisasi RTC Client Agora
            agoraClient = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' });

            // Deteksi Active Speaker (Border hijau saat berbicara)
            agoraClient.enableAudioVolumeIndicator();
            agoraClient.on('volume-indicator', (volumes) => {
                document.querySelectorAll('.video-tile').forEach(t => t.classList.remove('speaking'));
                volumes.forEach(vol => {
                    if (vol.level > 15) {
                        let targetTileId = null;
                        if (vol.uid === 0 || vol.uid === AGORA_UID) {
                            targetTileId = 'video-tile-local';
                        } else if (vol.uid === TEACHER_USER_ID) {
                            targetTileId = 'video-tile-teacher';
                        } else {
                            targetTileId = `video-tile-${vol.uid}`;
                        }
                        const tileEl = document.getElementById(targetTileId);
                        if (tileEl) tileEl.classList.add('speaking');
                    }
                });
            });

            // Remote User Published (Guru atau Siswa lain publish track)
            agoraClient.on('user-published', async (user, mediaType) => {
                await agoraClient.subscribe(user, mediaType);

                if (mediaType === 'video') {
                    // Cek apakah Guru atau Siswa lain
                    if (user.uid === TEACHER_USER_ID || isTeacherTrack(user)) {
                        const teacherContainer = document.getElementById('agora-teacher-video');
                        const placeholder = document.getElementById('teacher-waiting-placeholder');
                        if (placeholder) placeholder.style.display = 'none';
                        user.videoTrack.play(teacherContainer);
                    } else {
                        // Siswa lain (Classmate)
                        let peerTile = document.getElementById(`video-tile-${user.uid}`);
                        if (!peerTile) {
                            peerTile = createRemoteStudentTile(user);
                            document.getElementById('master-tiles-pool').appendChild(peerTile);
                            rearrangeStudentTiles();
                        }
                        user.videoTrack.play(`remote-${user.uid}`);
                    }
                }

                if (mediaType === 'audio') {
                    user.audioTrack.play();
                    if (user.uid === TEACHER_USER_ID) {
                        const ind = document.getElementById('teacher-audio-indicator');
                        if (ind) ind.classList.remove('hidden');
                    }
                }
            });

            // Remote User Unpublished
            agoraClient.on('user-unpublished', (user, mediaType) => {
                if ((user.uid === TEACHER_USER_ID || isTeacherTrack(user)) && mediaType === 'video') {
                    const placeholder = document.getElementById('teacher-waiting-placeholder');
                    if (placeholder) placeholder.style.display = 'flex';
                }
                if (mediaType === 'video' && user.uid !== TEACHER_USER_ID) {
                    const el = document.getElementById(`video-tile-${user.uid}`);
                    if (el) el.remove();
                    rearrangeStudentTiles();
                }
            });

            // Remote User Left
            agoraClient.on('user-left', (user) => {
                const el = document.getElementById(`video-tile-${user.uid}`);
                if (el) el.remove();
                rearrangeStudentTiles();
            });

            // Join Agora Channel
            await agoraClient.join(AGORA_APP_ID, AGORA_CHANNEL, AGORA_TOKEN || null, AGORA_UID);
            console.log("Siswa berhasil join Agora room:", AGORA_CHANNEL);
        }

        function isTeacherTrack(user) {
            return TEACHER_USER_ID > 0 && user.uid === TEACHER_USER_ID;
        }

        // 2. KONTROL MIC & KAMERA SISWA
        async function toggleStudentMic() {
            try {
                if (!localAudioTrack) {
                    localAudioTrack = await AgoraRTC.createMicrophoneAudioTrack();
                    await agoraClient.publish([localAudioTrack]);
                    isMicOn = true;
                } else {
                    isMicOn = !isMicOn;
                    await localAudioTrack.setMuted(!isMicOn);
                }

                const icon = document.getElementById('mic-icon');
                const btn  = document.getElementById('btn-toggle-mic');
                if (isMicOn) {
                    icon.className = 'fas fa-microphone text-base text-white';
                    btn.className = 'group relative p-3 rounded-xl bg-[#10b981] hover:bg-emerald-600 text-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316] transition active:translate-x-0.5 active:translate-y-0.5';
                } else {
                    icon.className = 'fas fa-microphone-slash text-base text-white';
                    btn.className = 'group relative p-3 rounded-xl bg-[#ff3823] hover:bg-red-600 text-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316] transition active:translate-x-0.5 active:translate-y-0.5';
                }
            } catch (err) {
                console.warn('Gagal mengakses mikrofon siswa:', err);
                alert('Tidak dapat mengakses mikrofon. Pastikan Anda telah memberikan izin mikrofon pada peramban (browser).');
            }
        }

        async function toggleStudentCam() {
            try {
                const placeholder = document.getElementById('student-cam-off-placeholder');

                if (!localVideoTrack) {
                    localVideoTrack = await AgoraRTC.createCameraVideoTrack();
                    await agoraClient.publish([localVideoTrack]);
                    localVideoTrack.play('agora-student-video');
                    if (placeholder) placeholder.style.display = 'none';
                    isCamOn = true;
                } else {
                    isCamOn = !isCamOn;
                    await localVideoTrack.setMuted(!isCamOn);
                    if (placeholder) placeholder.style.display = isCamOn ? 'none' : 'flex';
                }

                const icon = document.getElementById('cam-icon');
                const btn  = document.getElementById('btn-toggle-cam');
                if (isCamOn) {
                    icon.className = 'fas fa-video text-base text-white';
                    btn.className = 'group relative p-3 rounded-xl bg-[#0891b2] hover:bg-cyan-600 text-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316] transition active:translate-x-0.5 active:translate-y-0.5';
                } else {
                    icon.className = 'fas fa-video-slash text-base text-white';
                    btn.className = 'group relative p-3 rounded-xl bg-[#ff3823] hover:bg-red-600 text-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316] transition active:translate-x-0.5 active:translate-y-0.5';
                }
            } catch (err) {
                console.warn('Gagal mengakses kamera siswa:', err);
                alert('Tidak dapat mengakses kamera. Pastikan Anda telah memberikan izin kamera pada peramban (browser).');
            }
        }

        // 3. ANGKAT TANGAN (RAISE HAND)
        function studentToggleRaiseHand() {
            isRaiseHand = !isRaiseHand;
            const btn = document.getElementById('btn-raise-hand');
            const label = document.getElementById('raise-hand-label');
            if (isRaiseHand) {
                btn.className = 'flex items-center gap-1.5 btn-tactile-red px-3.5 sm:px-4 py-2.5 rounded-xl text-xs font-mono-code font-black active:scale-95';
                if (label) label.textContent = 'Tangan Terangkat!';
            } else {
                btn.className = 'flex items-center gap-1.5 btn-tactile-yellow px-3.5 sm:px-4 py-2.5 rounded-xl text-xs font-mono-code font-black active:scale-95';
                if (label) label.textContent = 'Angkat Tangan';
            }
        }

        // 4. MODE HEMAT KUOTA
        async function toggleDataSaverMode() {
            isDataSaver = !isDataSaver;
            const btn = document.getElementById('btn-data-saver');
            const label = document.getElementById('data-saver-label');
            const statusText = document.getElementById('mode-status-text');

            if (isDataSaver) {
                if (localVideoTrack && isCamOn) {
                    await toggleStudentCam();
                }
                if (btn) {
                    btn.className = 'hidden sm:flex items-center gap-1.5 btn-tactile-yellow px-3 py-1.5 rounded-xl text-xs font-mono-code font-black shadow-md';
                }
                if (label) label.textContent = 'Hemat Aktif';
                if (statusText) statusText.textContent = '⚡ Hemat Kuota';
                alert('Mode Hemat Kuota Aktif: Mengutamakan kelancaran suara guru dan mematikan pengiriman video dari perangkat Anda.');
            } else {
                if (btn) {
                    btn.className = 'hidden sm:flex items-center gap-1.5 btn-tactile-white px-3 py-1.5 rounded-xl text-xs font-mono-code font-black transition-all active:scale-95 shadow-xs';
                }
                if (label) label.textContent = 'Hemat Kuota';
                if (statusText) statusText.textContent = 'Koneksi Normal';
            }
        }

        // 5. LIVE TIMER KEHADIRAN SISWA
        let studentJoinTime = new Date();
        function updateStudentTimer() {
            const now = new Date();
            const diffMs = Math.max(0, now - studentJoinTime);
            const totalSec = Math.floor(diffMs / 1000);
            const h = String(Math.floor(totalSec / 3600)).padStart(2, '0');
            const m = String(Math.floor((totalSec % 3600) / 60)).padStart(2, '0');
            const s = String(totalSec % 60).padStart(2, '0');
            const timerEl = document.getElementById('student-timer');
            if (timerEl) timerEl.textContent = `${h}:${m}:${s}`;
        }
        setInterval(updateStudentTimer, 1000);
        updateStudentTimer();

        // 6. TOGGLE & SWITCH TAB LEARNING COMPANION HUB
        function toggleStudentHub() {
            const hub = document.getElementById('student-hub');
            const handler = document.getElementById('resize-handler');
            if (hub.classList.contains('hidden')) {
                hub.classList.remove('hidden');
                hub.classList.add('flex');
                if (handler) handler.classList.remove('hidden');
                if (currentLayout === 'gallery') {
                    currentLayout = 'sidebyside';
                    updateStudentViewLabel('sidebyside');
                }
            } else {
                hub.classList.add('hidden');
                hub.classList.remove('flex');
                if (handler) handler.classList.add('hidden');
                if (currentLayout === 'sidebyside') {
                    currentLayout = 'speaker';
                    updateStudentViewLabel('speaker');
                }
            }
            rearrangeStudentTiles();
        }

        function switchStudentTab(tabName) {
            const hub = document.getElementById('student-hub');
            const handler = document.getElementById('resize-handler');
            if (hub.classList.contains('hidden')) {
                hub.classList.remove('hidden');
                hub.classList.add('flex');
                if (handler) handler.classList.remove('hidden');
            }

            const tabs = ['materi', 'notes', 'friends'];
            tabs.forEach(t => {
                const btn = document.getElementById('tab-btn-' + t);
                const content = document.getElementById('tab-content-' + t);
                if (t === tabName) {
                    btn.className = 'flex-1 py-2 px-3 text-xs font-mono-code font-black transition-all flex items-center justify-center gap-1.5 rounded-xl bg-[#121316] text-[#fde047] border-2 border-[#121316] shadow-[2px_2px_0px_#ff3823]';
                    content.classList.remove('hidden');
                } else {
                    btn.className = 'flex-1 py-2 px-3 text-xs font-mono-code font-bold transition-all flex items-center justify-center gap-1.5 rounded-xl bg-white text-[#121316] border-2 border-[#121316] hover:bg-[#faf3e0]';
                    content.classList.add('hidden');
                }
            });
        }

        function openStudentTab(tabName) {
            switchStudentTab(tabName);
        }

        // 7. DISPLAY MATERI PEMBELAJARAN
        function displayStudentMaterial(title, type, url, content) {
            const box = document.getElementById('material-preview-box');
            const iframe = document.getElementById('preview-iframe');
            const textEl = document.getElementById('preview-text');
            const titleEl = document.getElementById('preview-title');
            const iconEl = document.getElementById('preview-icon');
            const extLink = document.getElementById('preview-external-link');

            titleEl.textContent = title;
            switchStudentTab('materi');

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

        // 8. BUKU CATATAN SISWA (PERSISTED LOCALSTORAGE)
        const notesKey = 'pembdahub_lms_notes_{{ $course->id }}_{{ $student->id }}';
        const notesArea = document.getElementById('student-notes-area');

        function loadStudentNotes() {
            const saved = localStorage.getItem(notesKey);
            if (saved && notesArea) {
                notesArea.value = saved;
            }
        }

        function saveStudentNotesLocally() {
            if (notesArea) {
                localStorage.setItem(notesKey, notesArea.value);
                const hint = document.getElementById('notes-saved-hint');
                if (hint) {
                    hint.textContent = 'Tersimpan ' + new Date().toLocaleTimeString('id-ID');
                    hint.classList.add('text-[#10b981]');
                    setTimeout(() => hint.classList.remove('text-[#10b981]'), 2000);
                }
            }
        }

        if (notesArea) {
            notesArea.addEventListener('input', saveStudentNotesLocally);
        }
        loadStudentNotes();

        // 9. POLLING TEMAN SEKELAS
        function fetchFriends() {
            fetch("{{ route('guru.lms.meeting.attendees', $course->id) }}")
                .then(r => r.json())
                .then(data => {
                    const total = data.total || 0;
                    const badgeEl = document.getElementById('friends-badge-count');
                    if (badgeEl) badgeEl.textContent = total + ' Online';

                    const listEl = document.getElementById('friends-list');
                    if (!listEl) return;

                    if (total === 0) {
                        listEl.innerHTML = `
                            <div class="text-center py-10 px-4 poster-card bg-white p-6">
                                <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-[#ede9df] border-2 border-[#121316] flex items-center justify-center text-[#2563eb] text-2xl shadow-[3px_3px_0px_#121316]">
                                    <i class="fas fa-users-rays"></i>
                                </div>
                                <h4 class="text-xs font-mono-code font-black text-[#121316] uppercase">Belum Ada Teman Lain</h4>
                                <p class="text-[11px] text-[#555] mt-1.5 leading-relaxed font-medium">Anda adalah siswa pertama yang bergabung di PembdaTEMU ini. Teman-teman sekelas akan muncul di sini saat mereka masuk.</p>
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

                    data.attendees.forEach((stu, idx) => {
                        attendeesMap[stu.id] = stu.name;
                        const grad = avatarGradients[idx % avatarGradients.length];
                        const short = (stu.name || '').split(' ')[0] || stu.name;

                        // Perbarui nama di tile video jika sudah ada
                        const tileNameSpan = document.getElementById(`tile-name-${stu.id}`);
                        if (tileNameSpan) {
                            tileNameSpan.textContent = short;
                            if (tileNameSpan.parentElement) {
                                tileNameSpan.parentElement.setAttribute('title', stu.name);
                            }
                        }
                        const tilePlaceholder = document.querySelector(`#remote-placeholder-${stu.id} p`);
                        if (tilePlaceholder) {
                            tilePlaceholder.textContent = short;
                        }

                        html += `
                            <div class="flex items-center justify-between p-3 rounded-2xl bg-white border-2 border-[#121316] shadow-[3px_3px_0px_#121316] hover:translate-x-0.5 transition-all text-xs">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr ${grad} flex items-center justify-center text-xs font-black text-white border-2 border-[#121316] shadow-xs flex-shrink-0">
                                        ${stu.initials}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <p class="font-black text-[#121316] truncate">${stu.name}</p>
                                            ${stu.is_online ? '<span class="w-2 h-2 rounded-full bg-[#10b981] animate-pulse"></span>' : ''}
                                        </div>
                                        <p class="text-[10px] text-[#555] font-mono-code font-bold flex items-center gap-1">
                                            <i class="far fa-clock text-[#2563eb]"></i> Masuk: ${stu.joined_at}
                                        </p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-mono-code font-black bg-[#10b981] border-2 border-[#121316] text-white shadow-xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span> HADIR
                                </span>
                            </div>
                        `;
                    });
                    listEl.innerHTML = html;
                })
                .catch(() => {});
        }

        // 10. PRESENSI KELUAR & LEAVE HANDLER
        function reportLeave() {
            const fd = new FormData();
            fd.append('_token', "{{ csrf_token() }}");
            navigator.sendBeacon("{{ route('siswa.lms.meeting.leave', $course->id) }}", fd);
        }

        window.addEventListener('beforeunload', async () => {
            reportLeave();
            try {
                if (localAudioTrack) { localAudioTrack.stop(); localAudioTrack.close(); }
                if (localVideoTrack) { localVideoTrack.stop(); localVideoTrack.close(); }
                if (agoraClient) await agoraClient.leave();
            } catch(e) {}
        });

        function openLeaveModal() {
            document.getElementById('leave-meeting-modal').classList.remove('hidden');
        }
        function closeLeaveModal() {
            document.getElementById('leave-meeting-modal').classList.add('hidden');
        }
        async function executeLeaveMeeting() {
            reportLeave();
            try {
                if (localAudioTrack) { localAudioTrack.stop(); localAudioTrack.close(); }
                if (localVideoTrack) { localVideoTrack.stop(); localVideoTrack.close(); }
                if (agoraClient) await agoraClient.leave();
            } catch(e) {}
            window.location.href = "{{ route('siswa.lms.show', $course->id) }}";
        }

        // 11. RESIZE DRAG HANDLER
        const studentHub = document.getElementById('student-hub');
        const resizeHandler = document.getElementById('resize-handler');
        const overlay = document.getElementById('resize-overlay');
        let isResizing = false;

        if (resizeHandler) {
            resizeHandler.addEventListener('mousedown', function(e) {
                isResizing = true;
                overlay.classList.remove('hidden');
                document.body.style.cursor = 'col-resize';
            });
        }

        document.addEventListener('mousemove', function(e) {
            if (!isResizing) return;
            const newWidth = window.innerWidth - e.clientX;
            if (newWidth >= 300 && newWidth <= window.innerWidth * 0.65) {
                studentHub.style.width = newWidth + 'px';
            }
        });

        document.addEventListener('mouseup', function() {
            if (isResizing) {
                isResizing = false;
                overlay.classList.add('hidden');
                document.body.style.cursor = 'default';
            }
        });

        // 12. INIT ON PAGE LOAD
        document.addEventListener("DOMContentLoaded", function () {
            initAgoraStudent().catch(err => {
                console.error("Agora student init error:", err);
            });

            setInterval(fetchFriends, 15000);
            fetchFriends();
        });
    </script>
</body>
</html>
