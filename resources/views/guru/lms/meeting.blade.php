<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PembdaTEMU: {{ $course->course_name ?? $course->name }} - PembdaHUB</title>
    
    <!-- Tailwind CSS & App JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Instrument Sans & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Instrument Sans', sans-serif;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.6);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(71, 85, 105, 0.6);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.8);
        }
        .canvas-grid-math {
            background-size: 24px 24px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.07) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.07) 1px, transparent 1px);
        }
        .canvas-chalkboard {
            background-color: #14281d;
            background-image: radial-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 0);
            background-size: 20px 20px;
        }
        .canvas-whiteboard {
            background-color: #ffffff;
            background-image: radial-gradient(rgba(0, 0, 0, 0.06) 1px, transparent 0);
            background-size: 20px 20px;
        }
        /* Video Tile Responsive Styles (Zoom style) */
        .video-tile {
            position: relative;
            background: #090d16;
            border-radius: 1rem;
            overflow: hidden;
            border: 1.5px solid rgba(51, 65, 85, 0.7);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            aspect-ratio: 16 / 9;
        }
        .video-tile:hover {
            border-color: rgba(99, 102, 241, 0.6);
        }
        .video-tile.speaking {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.4);
        }
        .video-tile.pinned-speaker {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.4);
        }
        .video-tile video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            border-radius: inherit;
        }
        .filmstrip-tile {
            height: 96px !important;
            width: 170px !important;
            flex-shrink: 0 !important;
            cursor: pointer;
            border-radius: 0.75rem !important;
        }
        .filmstrip-tile:hover {
            transform: scale(1.03);
            border-color: #6366f1 !important;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 h-screen flex flex-col overflow-hidden select-none">

    {{-- 1. SMART CLASSROOM HEADER --}}
    <header class="bg-slate-900/95 backdrop-blur-md border-b border-slate-800 px-4 sm:px-6 py-2.5 flex items-center justify-between gap-3 z-30 flex-shrink-0 shadow-lg">
        
        {{-- Info Kelas & Guru --}}
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-rose-600 to-amber-500 p-0.5 shadow-lg shadow-rose-900/30 flex-shrink-0">
                <div class="w-full h-full bg-slate-900 rounded-[10px] flex items-center justify-center text-rose-500 font-bold">
                    <i class="fas fa-chalkboard-user text-base animate-pulse"></i>
                </div>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-500/15 border border-rose-500/30 text-rose-400 uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span> PembdaTEMU Live
                    </span>
                    <span class="text-[11px] font-semibold text-slate-400 hidden md:inline truncate">
                        {{ $course->school?->name ?? 'Perguruan Pembda' }}
                    </span>
                </div>
                <h1 class="text-sm sm:text-base font-extrabold text-white truncate leading-tight mt-0.5">
                    {{ $course->course_name ?? $course->name }}
                    @if($course->subject)
                        <span class="text-xs font-semibold text-slate-400 font-normal hidden sm:inline"> • {{ $course->subject->name }}</span>
                    @endif
                </h1>
            </div>
        </div>

        {{-- Center Status Widget: Durasi & Peserta Hadir --}}
        <div class="hidden lg:flex items-center gap-2 bg-slate-950/70 border border-slate-800/90 rounded-2xl px-3.5 py-1.5 shadow-inner">
            <div class="flex items-center gap-2 text-xs font-bold text-amber-400">
                <i class="far fa-clock text-amber-500 animate-spin" style="animation-duration: 8s;"></i>
                <span id="class-timer" class="font-mono tracking-wider">00:00:00</span>
            </div>
            <div class="w-px h-3.5 bg-slate-800"></div>
            <button onclick="switchTab('peserta')" class="flex items-center gap-1.5 text-xs font-semibold text-emerald-400 hover:text-emerald-300 transition-colors" title="Lihat siswa yang hadir">
                <i class="fas fa-users text-[11px]"></i>
                <span id="header-attendee-count">0</span> Siswa Hadir
            </button>
        </div>

        {{-- Right Controls --}}
        <div class="flex items-center gap-2 flex-shrink-0">
            
            {{-- Tombol Salin Tautan --}}
            <button onclick="copyMeetingLink()" type="button" class="hidden sm:flex items-center gap-1.5 bg-slate-800/90 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700/80 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all shadow-sm active:scale-95" title="Salin link kelas untuk dibagikan ke siswa">
                <i class="far fa-copy text-indigo-400"></i>
                <span>Salin Tautan</span>
            </button>

            {{-- Tombol Papan Tulis Cepat --}}
            <button onclick="openTabDirect('whiteboard')" type="button" class="flex items-center gap-1.5 bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 border border-emerald-500/30 px-3 py-1.5 rounded-xl text-xs font-bold transition-all active:scale-95" title="Buka Papan Tulis Digital KBM">
                <i class="fas fa-pen-fancy text-emerald-400"></i>
                <span class="hidden md:inline">Papan Tulis</span>
            </button>

            {{-- Tombol Pilihan Tampilan (Zoom View Selector) --}}
            <div class="relative inline-block text-left" id="header-view-wrapper">
                <button onclick="toggleViewMenu(event)" type="button" class="flex items-center gap-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 px-3 py-1.5 rounded-xl text-xs font-bold transition-all active:scale-95 shadow-sm" title="Pilihan Tata Letak Tampilan (Zoom)">
                    <i id="header-view-icon" class="fas fa-table-cells text-indigo-400"></i>
                    <span class="hidden md:inline">Tampilan: <span id="header-view-name" class="text-white">Galeri</span></span>
                    <i class="fas fa-chevron-down text-[9px] text-slate-400"></i>
                </button>
            </div>

            {{-- Tombol Toggle Panel Materi / Peserta --}}
            <button onclick="toggleClassroomHub()" type="button" id="btn-toggle-hub" class="flex items-center gap-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 px-3 py-1.5 rounded-xl text-xs font-bold transition-all active:scale-95" title="Buka atau sembunyikan panel modul dan siswa">
                <i class="fas fa-columns text-indigo-400"></i>
                <span class="hidden sm:inline">Panel KBM</span>
            </button>

            {{-- Profile Chip Guru --}}
            <div class="hidden xl:flex items-center gap-2 bg-slate-800/80 border border-slate-700 px-2.5 py-1 rounded-xl text-xs text-slate-300">
                <div class="w-6 h-6 rounded-full bg-indigo-600/80 flex items-center justify-center text-[10px] font-bold text-white uppercase">
                    {{ substr($displayName, 0, 1) }}
                </div>
                <span class="font-medium max-w-[110px] truncate">{{ $displayName }}</span>
            </div>

            {{-- Tombol Akhiri Kelas --}}
            <button onclick="openEndMeetingModal()" type="button" class="bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-bold px-3.5 py-1.5 rounded-xl text-xs transition-all shadow-md shadow-rose-900/40 flex items-center gap-1.5 hover:-translate-y-0.5 active:translate-y-0">
                <i class="fas fa-phone-slash"></i>
                <span>Akhiri Kelas</span>
            </button>
        </div>
    </header>

    {{-- 2. MAIN WORKSPACE CONTAINER (SPLIT SCREEN) --}}
    <div class="flex-1 flex flex-row overflow-hidden relative" style="height: calc(100vh - 61px);">
        
        {{-- Conference Stage (Video Call Agora) --}}
        <main id="conference-panel" class="flex-1 relative bg-slate-950 flex flex-col h-full overflow-hidden transition-all duration-150">
            
            {{-- FLOATING ZOOM-STYLE VIEW SELECTOR (POJOK KANAN ATAS VIDEO) --}}
            <div class="absolute top-3.5 right-3.5 z-30 flex items-center gap-2">
                <div class="relative" id="video-view-dropdown-wrapper">
                    <button id="btn-view-toggle" onclick="toggleViewMenu(event)" type="button" class="flex items-center gap-2 bg-slate-900/90 hover:bg-slate-800 text-slate-200 hover:text-white px-3.5 py-1.5 rounded-xl border border-slate-700/80 text-xs font-bold transition shadow-xl backdrop-blur-md active:scale-95" title="Ganti Tata Letak Video (Zoom)">
                        <i id="current-view-icon" class="fas fa-table-cells text-indigo-400 text-xs"></i>
                        <span>Tampilan: <span id="current-view-name" class="text-white">Galeri</span></span>
                        <i class="fas fa-chevron-down text-[9px] text-slate-400"></i>
                    </button>

                    <div id="view-dropdown-menu" class="hidden absolute right-0 mt-2 w-56 rounded-2xl bg-slate-900/95 border border-slate-700/80 shadow-2xl p-2 z-50 backdrop-blur-xl transition-all">
                        <div class="px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-800 mb-1">
                            Pilihan Tata Letak (Zoom)
                        </div>
                        
                        <button onclick="changeLayout('gallery')" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold hover:bg-slate-800 text-slate-200 hover:text-white flex items-center justify-between transition-colors">
                            <span class="flex items-center gap-2.5">
                                <i class="fas fa-table-cells text-indigo-400 w-4"></i>
                                <span>Tampilan Galeri</span>
                            </span>
                            <i id="check-gallery" class="fas fa-check text-indigo-400 text-xs"></i>
                        </button>

                        <button onclick="changeLayout('speaker')" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold hover:bg-slate-800 text-slate-200 hover:text-white flex items-center justify-between transition-colors">
                            <span class="flex items-center gap-2.5">
                                <i class="fas fa-user-tie text-rose-400 w-4"></i>
                                <span>Tampilan Pembicara</span>
                            </span>
                            <i id="check-speaker" class="fas fa-check text-rose-400 text-xs hidden"></i>
                        </button>

                        <button onclick="changeLayout('sidebyside')" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold hover:bg-slate-800 text-slate-200 hover:text-white flex items-center justify-between transition-colors">
                            <span class="flex items-center gap-2.5">
                                <i class="fas fa-columns text-emerald-400 w-4"></i>
                                <span>Berdampingan (KBM)</span>
                            </span>
                            <i id="check-sidebyside" class="fas fa-check text-emerald-400 text-xs hidden"></i>
                        </button>

                        <div class="h-px bg-slate-800 my-1.5"></div>

                        <button onclick="toggleAppFullscreen()" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold hover:bg-slate-800 text-slate-300 hover:text-white flex items-center gap-2.5 transition-colors">
                            <i id="menu-fs-icon" class="fas fa-expand text-amber-400 w-4"></i>
                            <span id="menu-fs-text">Layar Penuh (F11)</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- 1. SPEAKER VIEW STAGE (Active when currentLayout === 'speaker') --}}
            <div id="stage-speaker-wrapper" class="hidden flex-1 flex-col h-full overflow-hidden p-3 gap-2.5 relative min-h-0">
                {{-- Top Filmstrip Bar: Thumbnail peserta lain --}}
                <div id="speaker-filmstrip" class="flex items-center gap-2.5 overflow-x-auto p-1.5 h-28 shrink-0 bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-sm scroll-smooth">
                    {{-- Non-pinned tiles live here --}}
                </div>
                {{-- Main Spotlight Screen: 1 Peserta Terfokus (Guru/Siswa) --}}
                <div id="speaker-main-stage" class="flex-1 flex items-center justify-center overflow-hidden min-h-0 relative p-1">
                    {{-- Pinned tile lives here --}}
                </div>
            </div>

            {{-- 2. GALLERY VIEW STAGE (Default: Active when currentLayout === 'gallery' or 'sidebyside') --}}
            <div id="stage-gallery-wrapper" class="flex-1 flex items-center justify-center overflow-hidden h-full p-3 min-h-0 relative">
                <div id="gallery-grid" class="w-full h-full flex items-center justify-center gap-3 transition-all duration-300">
                    {{-- Local video tile (Guru) --}}
                    <div id="video-tile-local" class="video-tile group relative rounded-2xl overflow-hidden bg-slate-900 border border-slate-700/80 shadow-2xl flex items-center justify-center w-full max-w-5xl h-full max-h-[78vh] aspect-video mx-auto my-auto transition-all">
                        <div id="agora-local-video" class="w-full h-full relative">
                            {{-- Agora video injected here --}}
                        </div>
                        <div id="agora-local-placeholder" class="absolute inset-0 flex flex-col items-center justify-center text-slate-500 bg-slate-900 pointer-events-none">
                            <div class="w-16 h-16 rounded-full bg-slate-800 border-2 border-slate-700 flex items-center justify-center text-2xl font-bold text-rose-400 mb-2">
                                {{ substr($displayName, 0, 1) }}
                            </div>
                            <p class="text-xs font-semibold text-slate-300">Mengaktifkan kamera...</p>
                        </div>
                        <div class="absolute bottom-2.5 left-2.5 z-10 flex items-center gap-1.5 bg-slate-950/80 backdrop-blur-md px-2.5 py-1 rounded-xl text-[11px] font-bold text-white border border-slate-800 shadow-md">
                            <i id="tile-mic-local" class="fas fa-microphone text-emerald-400 text-[10px]"></i>
                            <span class="truncate max-w-[170px]"><i class="fas fa-chalkboard-user text-rose-400 mr-1"></i>{{ $displayName }} (Host)</span>
                        </div>
                        <div class="absolute top-2.5 right-2.5 z-10 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="pinSpeaker('local')" type="button" class="p-1.5 rounded-lg bg-slate-950/80 hover:bg-indigo-600 text-slate-300 hover:text-white border border-slate-700 text-xs shadow-md transition" title="Fokuskan / Spotlight">
                                <i class="fas fa-thumbtack text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SOLO TEACHER FLOATING BANNER (Hanya muncul jika belum ada siswa yang bergabung) --}}
            <div id="solo-teacher-banner" class="absolute bottom-20 left-1/2 -translate-x-1/2 z-20 pointer-events-auto transition-all duration-300">
                <div class="flex items-center gap-3 bg-slate-900/95 backdrop-blur-md border border-slate-700/80 px-4 py-2 rounded-2xl shadow-2xl text-xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                    <span class="text-slate-300 font-medium">Kamera Anda Aktif • Menunggu siswa bergabung</span>
                    <button onclick="copyMeetingLink()" type="button" class="ml-1 bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-xl font-bold text-xs flex items-center gap-1.5 transition active:scale-95 shadow-md shadow-indigo-900/30">
                        <i class="far fa-copy text-xs"></i>
                        <span>Bagikan Tautan</span>
                    </button>
                </div>
            </div>

            {{-- Floating Teacher Action Bar --}}
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2 bg-slate-900/90 backdrop-blur-md border border-slate-700/80 px-3 py-2 rounded-2xl shadow-2xl">
                
                {{-- Toggle Mic --}}
                <button id="btn-toggle-mic" onclick="toggleMic()" type="button" class="group relative p-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 transition" title="Nyalakan/Matikan Mikrofon">
                    <i id="mic-icon" class="fas fa-microphone text-emerald-400"></i>
                </button>

                {{-- Toggle Camera --}}
                <button id="btn-toggle-cam" onclick="toggleCam()" type="button" class="group relative p-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 transition" title="Nyalakan/Matikan Kamera">
                    <i id="cam-icon" class="fas fa-video text-emerald-400"></i>
                </button>

                {{-- Screen Share --}}
                <button onclick="toggleScreenShare()" id="btn-screenshare" type="button" class="p-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 transition" title="Bagikan Layar Anda">
                    <i class="fas fa-desktop text-blue-400"></i>
                </button>

                <div class="w-px h-4 bg-slate-700/80 hidden sm:block"></div>

                {{-- Mute All Button (Pedagogical Control) --}}
                <button onclick="teacherMuteEveryone()" type="button" class="group relative flex items-center gap-1.5 bg-slate-800 hover:bg-amber-600/30 text-slate-200 hover:text-amber-300 border border-slate-700 hover:border-amber-500/50 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all" title="Kondusifkan Kelas: Matikan mic seluruh siswa">
                    <i class="fas fa-volume-xmark text-amber-400"></i>
                    <span class="hidden sm:inline">Mute Semua</span>
                </button>

                <div class="w-px h-4 bg-slate-700/80 hidden sm:block"></div>

                {{-- Quick Open Material --}}
                <button onclick="openTabDirect('materi')" type="button" class="flex items-center gap-1.5 bg-slate-800 hover:bg-indigo-600/30 text-slate-200 hover:text-indigo-300 border border-slate-700 hover:border-indigo-500/50 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all" title="Buka silabus materi & modul kelas">
                    <i class="fas fa-book-open text-indigo-400"></i>
                    <span class="hidden sm:inline">Modul</span>
                </button>

                {{-- Quick Open Whiteboard --}}
                <button onclick="openTabDirect('whiteboard')" type="button" class="flex items-center gap-1.5 bg-slate-800 hover:bg-emerald-600/30 text-slate-200 hover:text-emerald-300 border border-slate-700 hover:border-emerald-500/50 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all" title="Tulis rumus atau coretan di papan tulis">
                    <i class="fas fa-pen text-emerald-400"></i>
                    <span class="hidden sm:inline">Papan Tulis</span>
                </button>

                {{-- Quick Open Attendees --}}
                <button onclick="openTabDirect('peserta')" type="button" class="flex items-center gap-1.5 bg-slate-800 hover:bg-cyan-600/30 text-slate-200 hover:text-cyan-300 border border-slate-700 hover:border-cyan-500/50 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all" title="Lihat kehadiran siswa secara realtime">
                    <i class="fas fa-user-check text-cyan-400"></i>
                    <span class="hidden sm:inline">Presensi</span>
                    <span id="quick-attendee-badge" class="bg-cyan-500/20 text-cyan-300 px-1.5 py-0.2 rounded-md text-[10px] font-bold">0</span>
                </button>
            </div>
        </main>

        {{-- Resize Handle Divider --}}
        <div id="resize-handler" class="w-[8px] bg-slate-850 hover:bg-indigo-500 cursor-col-resize select-none z-20 flex items-center justify-center relative transition-colors duration-150 group" title="Geser untuk mengatur lebar panel materi">
            <div class="w-4 h-12 bg-slate-900 border border-slate-700 group-hover:border-indigo-400 rounded-md flex items-center justify-center shadow-lg pointer-events-none">
                <i class="fas fa-grip-lines-vertical text-[10px] text-slate-400 group-hover:text-indigo-300"></i>
            </div>
        </div>

        {{-- Resize Overlay (Mencegah iframe menyerap pointer saat drag) --}}
        <div id="resize-overlay" class="fixed inset-0 z-40 hidden cursor-col-resize"></div>

        {{-- 3. CLASSROOM MULTI-HUB PANEL (Materi, Papan Tulis & Peserta) --}}
        <aside id="classroom-hub" class="w-[520px] max-w-[90vw] border-l border-slate-800 bg-slate-900/95 flex flex-col h-full z-15 shadow-2xl relative">
            
            {{-- Tabs Header Bar --}}
            <div class="border-b border-slate-800 flex items-center bg-slate-950/60 px-2 pt-1 flex-shrink-0">
                <button id="tab-btn-materi" onclick="switchTab('materi')" type="button" class="flex-1 py-2.5 px-3 text-xs font-bold transition-all flex items-center justify-center gap-1.5 border-b-2 border-indigo-500 text-indigo-400">
                    <i class="fas fa-book text-xs"></i>
                    <span>Materi & Modul</span>
                </button>
                <button id="tab-btn-whiteboard" onclick="switchTab('whiteboard')" type="button" class="flex-1 py-2.5 px-3 text-xs font-semibold text-slate-400 hover:text-slate-200 transition-all flex items-center justify-center gap-1.5 border-b-2 border-transparent">
                    <i class="fas fa-pen-ruler text-xs"></i>
                    <span>Papan Tulis</span>
                </button>
                <button id="tab-btn-peserta" onclick="switchTab('peserta')" type="button" class="flex-1 py-2.5 px-3 text-xs font-semibold text-slate-400 hover:text-slate-200 transition-all flex items-center justify-center gap-1.5 border-b-2 border-transparent">
                    <i class="fas fa-users text-xs"></i>
                    <span>Siswa (<span id="attendee-tab-count">0</span>)</span>
                </button>
                <button onclick="toggleClassroomHub()" type="button" class="p-2 text-slate-400 hover:text-white rounded-lg ml-1 hover:bg-slate-800 transition-colors" title="Tutup panel">
                    <i class="fas fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- TAB 1: MATERI & MODUL PEMBELAJARAN --}}
            <div id="tab-content-materi" class="flex-1 flex flex-col overflow-hidden">
                
                {{-- Viewer Preview Box (Ketika Guru Membuka Materi) --}}
                <div id="material-preview-box" class="h-1/2 min-h-[220px] border-b border-slate-800 bg-slate-950 flex flex-col hidden">
                    <div class="px-4 py-2 border-b border-slate-800/80 bg-slate-900/60 flex items-center justify-between">
                        <div class="flex items-center gap-2 min-w-0">
                            <i id="preview-icon" class="far fa-file-pdf text-rose-500 text-xs"></i>
                            <h3 id="preview-title" class="text-xs font-bold text-slate-200 truncate">Judul Materi</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <a id="preview-external-link" href="#" target="_blank" class="text-[10px] bg-slate-800 hover:bg-slate-700 text-indigo-300 font-bold px-2 py-1 rounded-lg border border-slate-700 transition-all flex items-center gap-1">
                                Tab Baru <i class="fas fa-external-link-alt text-[8px]"></i>
                            </a>
                            <button onclick="closeMaterialPreview()" type="button" class="text-slate-400 hover:text-white p-1" title="Tutup preview materi">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    </div>
                    <div class="flex-1 relative bg-slate-950 overflow-hidden">
                        <iframe id="preview-iframe" class="w-full h-full border-none hidden" allow="autoplay; clipboard-write; clipboard-read"></iframe>
                        <div id="preview-text" class="p-4 text-xs text-slate-300 overflow-y-auto h-full prose prose-invert max-w-none hidden"></div>
                    </div>
                </div>

                {{-- Daftar Modul & Materi Silabus --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-3">
                    <div class="flex items-center justify-between pb-1">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Silabus & Bahan Ajar</span>
                        <span class="text-[10px] text-slate-500">Klik "Buka" untuk menampilkan materi</span>
                    </div>

                    @php
                        $modules = $course->modules()->ordered()->active()->get();
                    @endphp

                    @if($modules->isEmpty())
                        <div class="text-center text-xs text-slate-500 py-12">
                            <i class="far fa-folder-open text-3xl mb-2 text-slate-600 block"></i>
                            <p class="font-medium">Belum ada modul atau materi di kelas ini.</p>
                            <a href="{{ route('guru.lms.materials.create', $course->id) }}" target="_blank" class="mt-2 inline-flex items-center gap-1 text-[11px] text-indigo-400 hover:underline">
                                <i class="fas fa-plus"></i> Tambah Materi Baru
                            </a>
                        </div>
                    @else
                        @foreach($modules as $module)
                            <div class="bg-slate-800/50 rounded-xl border border-slate-800 overflow-hidden">
                                <div class="p-3 bg-slate-800/80 border-b border-slate-800/80 flex items-center justify-between">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                        <h4 class="text-xs font-bold text-slate-200 truncate">{{ $module->title }}</h4>
                                    </div>
                                    <span class="text-[10px] text-slate-400">{{ $module->materials()->active()->count() }} Materi</span>
                                </div>
                                <div class="p-2 space-y-1.5">
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
                                        <div class="flex items-center justify-between p-2 rounded-lg bg-slate-900/60 hover:bg-slate-900 border border-slate-800/80 text-xs transition-all">
                                            <div class="flex items-center gap-2 min-w-0">
                                                @if($material->material_type == 'pdf')
                                                    <i class="far fa-file-pdf text-rose-500 flex-shrink-0"></i>
                                                @elseif($material->material_type == 'video')
                                                    <i class="far fa-play-circle text-amber-400 flex-shrink-0"></i>
                                                @elseif($material->material_type == 'link')
                                                    <i class="fas fa-link text-cyan-400 flex-shrink-0"></i>
                                                @elseif($material->material_type == 'canva' || $material->material_type == 'googledocs')
                                                    <i class="fas fa-file-lines text-teal-400 flex-shrink-0"></i>
                                                @else
                                                    <i class="far fa-file-lines text-indigo-400 flex-shrink-0"></i>
                                                @endif
                                                <span class="text-[11px] font-medium text-slate-300 truncate">{{ $material->title }}</span>
                                            </div>
                                            <button onclick="displayMaterial('{{ $safeTitle }}', '{{ $material->material_type }}', '{{ $url }}', '{{ $safeContent }}')" type="button" class="text-[10px] bg-slate-800 hover:bg-indigo-600 text-indigo-300 hover:text-white font-bold px-2 py-1 rounded-md border border-slate-700 transition-all flex items-center gap-1 flex-shrink-0">
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

            {{-- TAB 2: PAPAN TULIS DIGITAL KBM (WHITEBOARD CANVAS) --}}
            <div id="tab-content-whiteboard" class="flex-1 flex flex-col overflow-hidden hidden">
                
                {{-- Toolbar Papan Tulis --}}
                <div class="p-3 bg-slate-950/80 border-b border-slate-800 flex flex-wrap items-center justify-between gap-2 flex-shrink-0">
                    
                    {{-- Warna & Mode --}}
                    <div class="flex items-center gap-1.5">
                        <button onclick="setWhiteboardTool('pen')" id="tool-pen" type="button" class="p-2 rounded-lg bg-indigo-600 text-white text-xs shadow-sm" title="Spidol / Pena">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button onclick="setWhiteboardTool('eraser')" id="tool-eraser" type="button" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs" title="Penghapus Coretan">
                            <i class="fas fa-eraser"></i>
                        </button>

                        <div class="w-px h-5 bg-slate-800 mx-1"></div>

                        {{-- Palet Warna Coretan --}}
                        <div class="flex items-center gap-1">
                            <button onclick="setPenColor('#ffffff')" class="w-5 h-5 rounded-full bg-white border border-slate-400 hover:scale-110 transition-transform" title="Putih"></button>
                            <button onclick="setPenColor('#facc15')" class="w-5 h-5 rounded-full bg-yellow-400 hover:scale-110 transition-transform" title="Kuning"></button>
                            <button onclick="setPenColor('#38bdf8')" class="w-5 h-5 rounded-full bg-sky-400 hover:scale-110 transition-transform" title="Biru"></button>
                            <button onclick="setPenColor('#4ade80')" class="w-5 h-5 rounded-full bg-green-400 hover:scale-110 transition-transform" title="Hijau"></button>
                            <button onclick="setPenColor('#f43f5e')" class="w-5 h-5 rounded-full bg-rose-500 hover:scale-110 transition-transform" title="Merah"></button>
                        </div>
                    </div>

                    {{-- Tebal Garis & Latar Papan --}}
                    <div class="flex items-center gap-1.5">
                        <select id="pen-size-select" onchange="setPenSize(this.value)" class="bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-lg px-2 py-1 outline-none">
                            <option value="2">Halus (2px)</option>
                            <option value="4" selected>Sedang (4px)</option>
                            <option value="8">Tebal (8px)</option>
                            <option value="16">Stabilo (16px)</option>
                        </select>

                        <select id="canvas-bg-select" onchange="setCanvasBackground(this.value)" class="bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-lg px-2 py-1 outline-none">
                            <option value="chalkboard">Papan Tulis Hijau</option>
                            <option value="grid">Grid Matematika</option>
                            <option value="whiteboard">Whiteboard Putih</option>
                        </select>
                    </div>

                    {{-- Actions: Undo, Clear, Save --}}
                    <div class="flex items-center gap-1 ml-auto">
                        <button onclick="undoWhiteboard()" type="button" class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs flex items-center gap-1" title="Batalkan coretan terakhir">
                            <i class="fas fa-rotate-left"></i>
                        </button>
                        <button onclick="clearWhiteboard()" type="button" class="px-2 py-1 rounded-lg bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 border border-rose-500/30 text-xs flex items-center gap-1" title="Hapus semua coretan papan tulis">
                            <i class="fas fa-trash-can"></i>
                        </button>
                        <button onclick="downloadWhiteboard()" type="button" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-1" title="Unduh hasil coretan sebagai gambar PNG">
                            <i class="fas fa-download"></i> <span class="hidden xl:inline">Simpan</span>
                        </button>
                    </div>
                </div>

                {{-- Drawing Canvas Area --}}
                <div id="whiteboard-container" class="flex-1 relative overflow-hidden canvas-chalkboard cursor-crosshair">
                    <canvas id="whiteboard-canvas" class="w-full h-full block"></canvas>
                </div>
            </div>

            {{-- TAB 3: DAFTAR SISWA & PRESENSI REALTIME --}}
            <div id="tab-content-peserta" class="flex-1 flex flex-col overflow-hidden hidden">
                
                {{-- Header Sub-bar --}}
                <div class="p-3 bg-slate-950/60 border-b border-slate-800 flex items-center justify-between flex-shrink-0">
                    <div>
                        <h3 class="text-xs font-bold text-slate-200">Presensi Tatap Muka Realtime</h3>
                        <p class="text-[10px] text-slate-400">Kehadiran dicatat otomatis saat siswa masuk & keluar.</p>
                    </div>
                    <a href="{{ route('guru.lms.meeting.attendance', $course->id) }}" target="_blank" class="text-[10px] bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 px-2.5 py-1 rounded-lg font-bold flex items-center gap-1 transition-all">
                        <i class="fas fa-file-invoice"></i> Rekap Sesi
                    </a>
                </div>

                {{-- List Peserta --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-2.5" id="attendees-list">
                    <div class="text-center text-xs text-slate-500 py-12">
                        <i class="fas fa-spinner animate-spin text-xl mb-2 text-indigo-400 block"></i>
                        <p>Memuat daftar siswa hadir...</p>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    {{-- 4. MODAL KONFIRMASI AKHIRI KELAS --}}
    <div id="end-meeting-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm p-4 hidden">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-500 flex items-center justify-center text-xl">
                <i class="fas fa-power-off"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-white">Akhiri Sesi PembdaTEMU?</h3>
                <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                    Sesi PembdaTEMU untuk mata pelajaran <strong>{{ $course->course_name ?? $course->name }}</strong> akan ditutup. Seluruh siswa akan diarahkan kembali ke beranda modul dan total durasi kehadiran siswa otomatis tersimpan di rekap presensi.
                </p>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2">
                <button onclick="closeEndMeetingModal()" type="button" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 transition-colors">
                    Batal
                </button>
                <form action="{{ route('guru.lms.meeting.stop', $course->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 transition-colors shadow-lg shadow-rose-900/30 flex items-center gap-1.5">
                        <i class="fas fa-check"></i> Ya, Akhiri PembdaTEMU
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- 5. TOAST NOTIFICATION --}}
    <div id="copy-toast" class="fixed bottom-6 right-6 z-50 bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow-xl border border-indigo-400 flex items-center gap-2 transform translate-y-12 opacity-0 transition-all duration-300 pointer-events-none">
        <i class="fas fa-circle-check text-emerald-300 text-sm"></i>
        <span>Tautan PembdaTEMU berhasil disalin! Bagikan ke siswa via WhatsApp.</span>
    </div>

    {{-- AGORA WEB SDK CDN --}}
    <script src="https://download.agora.io/sdk/release/AgoraRTC_N.js"></script>

    <script>
        // ============================================================
        // AGORA CONFIGURATION (dari Laravel controller)
        // ============================================================
        const AGORA_APP_ID  = "{{ $agoraAppId }}";
        const AGORA_TOKEN   = "{{ $agoraToken }}" || null;
        const AGORA_CHANNEL = "{{ $agoraChannel }}";
        const AGORA_UID     = {{ $agoraUid }};
        const COURSE_ID     = {{ $course->id }};
        const IS_HOST       = true;

        let agoraClient;
        let localVideoTrack, localAudioTrack, localScreenTrack;
        let isScreenSharing = false;
        let isMuted         = false;
        let isCamOff        = false;

        // ============================================================
        // LAYOUT & VIEW MANAGEMENT (ZOOM STYLE)
        // ============================================================
        let currentLayout = 'gallery'; // 'gallery', 'speaker', 'sidebyside'
        let currentPinnedUid = 'local'; // 'local' or remote uid
        let attendeesMap = {}; // mapping user_id -> student name

        function toggleViewMenu(e) {
            if (e) e.stopPropagation();
            const menu = document.getElementById('view-dropdown-menu');
            if (menu) menu.classList.toggle('hidden');
        }

        function closeViewMenu() {
            const menu = document.getElementById('view-dropdown-menu');
            if (menu) menu.classList.add('hidden');
        }

        document.addEventListener('click', (e) => {
            const wrapper1 = document.getElementById('video-view-dropdown-wrapper');
            const wrapper2 = document.getElementById('header-view-wrapper');
            if ((!wrapper1 || !wrapper1.contains(e.target)) && (!wrapper2 || !wrapper2.contains(e.target))) {
                closeViewMenu();
            }
        });

        function changeLayout(mode) {
            currentLayout = mode;
            closeViewMenu();

            const hub = document.getElementById('classroom-hub');
            const handler = document.getElementById('resize-handler');

            // Update icons and labels
            const iconEl = document.getElementById('current-view-icon');
            const nameEl = document.getElementById('current-view-name');
            const headerIcon = document.getElementById('header-view-icon');
            const headerName = document.getElementById('header-view-name');
            const checkGal = document.getElementById('check-gallery');
            const checkSpk = document.getElementById('check-speaker');
            const checkSbs = document.getElementById('check-sidebyside');

            if (checkGal) checkGal.classList.add('hidden');
            if (checkSpk) checkSpk.classList.add('hidden');
            if (checkSbs) checkSbs.classList.add('hidden');

            if (mode === 'speaker') {
                if (iconEl) iconEl.className = 'fas fa-user-tie text-rose-400 text-xs';
                if (nameEl) nameEl.textContent = 'Pembicara';
                if (headerIcon) headerIcon.className = 'fas fa-user-tie text-rose-400';
                if (headerName) headerName.textContent = 'Pembicara';
                if (checkSpk) checkSpk.classList.remove('hidden');

                if (hub) { hub.classList.add('hidden'); hub.classList.remove('flex'); }
                if (handler) handler.classList.add('hidden');
            } else if (mode === 'sidebyside') {
                if (iconEl) iconEl.className = 'fas fa-columns text-emerald-400 text-xs';
                if (nameEl) nameEl.textContent = 'Berdampingan';
                if (headerIcon) headerIcon.className = 'fas fa-columns text-emerald-400';
                if (headerName) headerName.textContent = 'Berdampingan';
                if (checkSbs) checkSbs.classList.remove('hidden');

                if (hub) { hub.classList.remove('hidden'); hub.classList.add('flex'); }
                if (handler) handler.classList.remove('hidden');
            } else {
                // gallery mode
                if (iconEl) iconEl.className = 'fas fa-table-cells text-indigo-400 text-xs';
                if (nameEl) nameEl.textContent = 'Galeri';
                if (headerIcon) headerIcon.className = 'fas fa-table-cells text-indigo-400';
                if (headerName) headerName.textContent = 'Galeri';
                if (checkGal) checkGal.classList.remove('hidden');

                if (hub) { hub.classList.add('hidden'); hub.classList.remove('flex'); }
                if (handler) handler.classList.add('hidden');
            }

            rearrangeTiles();
        }

        function pinSpeaker(uid) {
            currentPinnedUid = uid;
            if (currentLayout !== 'speaker') {
                changeLayout('speaker');
            } else {
                rearrangeTiles();
            }
        }

        function toggleAppFullscreen() {
            closeViewMenu();
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
            } else {
                if (document.exitFullscreen) document.exitFullscreen();
            }
        }

        document.addEventListener('fullscreenchange', () => {
            const icon = document.getElementById('menu-fs-icon');
            const text = document.getElementById('menu-fs-text');
            if (!document.fullscreenElement) {
                if (icon) icon.className = 'fas fa-expand text-amber-400 w-4';
                if (text) text.textContent = 'Layar Penuh (F11)';
            } else {
                if (icon) icon.className = 'fas fa-compress text-amber-400 w-4';
                if (text) text.textContent = 'Keluar Layar Penuh (Esc)';
            }
        });

        // ============================================================
        // 1. INISIALISASI AGORA RTC
        // ============================================================
        async function initAgora() {
            if (!AGORA_APP_ID) {
                showAgoraError('AGORA_APP_ID belum dikonfigurasi. Isi di file .env server production.');
                return;
            }

            agoraClient = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' });

            // Active Speaker Volume Indicator (Zoom-style glowing green border)
            agoraClient.enableAudioVolumeIndicator();
            agoraClient.on('volume-indicator', volumes => {
                volumes.forEach(volume => {
                    const isSpeaking = volume.level > 10;
                    const tileId = (volume.uid === AGORA_UID || volume.uid === 0) 
                        ? 'video-tile-local' 
                        : `video-tile-${volume.uid}`;
                    const tile = document.getElementById(tileId);
                    if (tile) {
                        if (isSpeaking) {
                            tile.classList.add('speaking');
                        } else {
                            tile.classList.remove('speaking');
                        }
                    }
                });
            });

            // Event: user remote baru publish stream
            agoraClient.on('user-published', async (user, mediaType) => {
                await agoraClient.subscribe(user, mediaType);
                if (mediaType === 'video') {
                    let tile = document.getElementById(`video-tile-${user.uid}`);
                    if (!tile) {
                        tile = createRemoteTile(user);
                    }
                    user.videoTrack.play(`remote-${user.uid}`);
                    const ph = document.getElementById(`remote-placeholder-${user.uid}`);
                    if (ph) ph.style.display = 'none';
                }
                if (mediaType === 'audio') {
                    user.audioTrack.play();
                    const micIcon = document.getElementById(`tile-mic-${user.uid}`);
                    if (micIcon) micIcon.className = 'fas fa-microphone text-emerald-400 text-[10px]';
                }
                updateParticipantCount();
                rearrangeTiles();
            });

            // Event: user remote berhenti publish
            agoraClient.on('user-unpublished', (user, mediaType) => {
                if (mediaType === 'video') {
                    const ph = document.getElementById(`remote-placeholder-${user.uid}`);
                    if (ph) ph.style.display = 'flex';
                }
                if (mediaType === 'audio') {
                    const micIcon = document.getElementById(`tile-mic-${user.uid}`);
                    if (micIcon) micIcon.className = 'fas fa-microphone-slash text-rose-400 text-[10px]';
                }
            });

            // Event: user remote keluar dari channel
            agoraClient.on('user-left', (user) => {
                const tile = document.getElementById(`video-tile-${user.uid}`);
                if (tile) tile.remove();
                if (currentPinnedUid == user.uid) {
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

        // Buat DOM tile untuk video remote siswa (Zoom style)
        function createRemoteTile(user) {
            const tile = document.createElement('div');
            tile.id = `video-tile-${user.uid}`;
            tile.className = 'video-tile group relative rounded-2xl overflow-hidden bg-slate-900 border border-slate-700/80 shadow-lg flex items-center justify-center transition-all duration-300';
            
            const studentName = attendeesMap[user.uid] || `Siswa #${user.uid}`;

            tile.innerHTML = `
                <div id="remote-${user.uid}" class="w-full h-full relative"></div>
                <div id="remote-placeholder-${user.uid}" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-900 text-slate-400 hidden">
                    <div class="w-16 h-16 rounded-full bg-slate-800 border-2 border-slate-700 flex items-center justify-center text-2xl font-bold text-indigo-400 mb-1.5 shadow-inner">
                        <i class="fas fa-user-graduate text-xl"></i>
                    </div>
                    <p class="text-xs font-semibold text-slate-300">${studentName}</p>
                </div>
                <div class="absolute bottom-2.5 left-2.5 z-10 flex items-center gap-1.5 bg-slate-950/80 backdrop-blur-md px-2.5 py-1 rounded-xl text-[11px] font-bold text-white border border-slate-800 shadow-md">
                    <i id="tile-mic-${user.uid}" class="fas fa-microphone text-emerald-400 text-[10px]"></i>
                    <span id="tile-name-${user.uid}" class="truncate max-w-[160px]"><i class="fas fa-user-graduate text-indigo-400 mr-1"></i>${studentName}</span>
                </div>
                <div class="absolute top-2.5 right-2.5 z-10 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button onclick="pinSpeaker('${user.uid}')" type="button" class="p-1.5 rounded-lg bg-slate-950/80 hover:bg-indigo-600 text-slate-300 hover:text-white border border-slate-700 text-xs shadow-md transition" title="Fokuskan / Spotlight">
                        <i class="fas fa-thumbtack text-[10px]"></i>
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

            // Tampilkan/sembunyikan notifikasi jika guru masih sendirian
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
                    pinnedTile.className = 'video-tile relative rounded-2xl overflow-hidden bg-slate-900 border-2 border-indigo-500/60 shadow-2xl flex items-center justify-center w-full h-full max-h-[75vh] aspect-video transition-all pinned-speaker';
                    pinnedTile.onclick = null;
                }

                allTiles.forEach(tile => {
                    if (tile !== pinnedTile && filmstrip) {
                        filmstrip.appendChild(tile);
                        tile.className = 'video-tile filmstrip-tile relative rounded-xl overflow-hidden bg-slate-900 border border-slate-700 shadow-md flex items-center justify-center aspect-video cursor-pointer transition-all hover:scale-105';
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
                        galleryGrid.className = 'w-full h-full flex items-center justify-center p-2';
                        if (allTiles[0]) {
                            allTiles[0].className = 'video-tile relative rounded-2xl overflow-hidden bg-slate-900 border border-slate-700/80 shadow-2xl flex items-center justify-center w-full max-w-5xl h-full max-h-[78vh] aspect-video mx-auto my-auto transition-all';
                        }
                    } else if (total === 2) {
                        galleryGrid.className = 'w-full h-full max-h-[78vh] grid grid-cols-1 md:grid-cols-2 gap-3.5 items-center justify-center p-2';
                        allTiles.forEach(t => t.className = 'video-tile relative rounded-2xl overflow-hidden bg-slate-900 border border-slate-700 shadow-xl flex items-center justify-center w-full h-full max-h-[70vh] aspect-video transition-all');
                    } else if (total <= 4) {
                        galleryGrid.className = 'w-full h-full max-h-[78vh] grid grid-cols-2 grid-rows-2 gap-3 items-center justify-center p-2';
                        allTiles.forEach(t => t.className = 'video-tile relative rounded-2xl overflow-hidden bg-slate-900 border border-slate-700 shadow-xl flex items-center justify-center w-full h-full aspect-video transition-all');
                    } else if (total <= 6) {
                        galleryGrid.className = 'w-full h-full max-h-[78vh] grid grid-cols-3 grid-rows-2 gap-2.5 items-center justify-center p-2';
                        allTiles.forEach(t => t.className = 'video-tile relative rounded-xl overflow-hidden bg-slate-900 border border-slate-700 shadow-lg flex items-center justify-center w-full h-full aspect-video transition-all');
                    } else {
                        galleryGrid.className = 'w-full h-full max-h-[78vh] grid grid-cols-3 md:grid-cols-4 gap-2.5 overflow-y-auto p-2';
                        allTiles.forEach(t => t.className = 'video-tile relative rounded-xl overflow-hidden bg-slate-900 border border-slate-700 shadow-md flex items-center justify-center w-full aspect-video transition-all');
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
                ? 'fas fa-microphone-slash text-red-400'
                : 'fas fa-microphone text-emerald-400';
            const btn = document.getElementById('btn-toggle-mic');
            btn.className = isMuted
                ? 'group relative p-3 rounded-xl bg-red-500/20 text-red-400 border border-red-500/30 transition'
                : 'group relative p-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 transition';
        }

        async function toggleCam() {
            if (!localVideoTrack) return;
            isCamOff = !isCamOff;
            await localVideoTrack.setMuted(isCamOff);
            document.getElementById('cam-icon').className = isCamOff
                ? 'fas fa-video-slash text-red-400'
                : 'fas fa-video text-emerald-400';
            const btn = document.getElementById('btn-toggle-cam');
            btn.className = isCamOff
                ? 'group relative p-3 rounded-xl bg-red-500/20 text-red-400 border border-red-500/30 transition'
                : 'group relative p-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 transition';
        }

        async function toggleScreenShare() {
            if (!isScreenSharing) {
                try {
                    localScreenTrack = await AgoraRTC.createScreenVideoTrack();
                    await agoraClient.unpublish(localVideoTrack);
                    await agoraClient.publish(localScreenTrack);
                    localScreenTrack.play('agora-local-video');
                    isScreenSharing = true;
                    document.getElementById('btn-screenshare').classList.add('bg-blue-600/30', 'text-blue-300', 'border-blue-500/30');
                    // Otomatis matikan screen share ketika pengguna menghentikan dari browser
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
            if (btn) btn.classList.remove('bg-blue-600/30', 'text-blue-300', 'border-blue-500/30');
        }

        // ============================================================
        // 3. UPDATE COUNTER PESERTA
        // ============================================================
        function updateParticipantCount() {
            if (!agoraClient) return;
            const count = agoraClient.remoteUsers.length;
            const headerEl = document.getElementById('header-attendee-count');
            const badgeEl  = document.getElementById('quick-attendee-badge');
            if (headerEl) headerEl.textContent = count;
            if (badgeEl)  badgeEl.textContent  = count;
        }

        // ============================================================
        // 4. KONTROL PEDAGOGIS GURU (MUTE ALL)
        // ============================================================
        function teacherMuteEveryone() {
            // Agora free tier tidak support force-mute remote — informasikan ke guru
            alert('Fitur Kondusifkan Kelas: Minta siswa untuk mematikan mikrofon masing-masing, atau gunakan chat/pesan untuk instruksi. (Fitur force-mute memerlukan Agora Real-Time Messaging).');
        }

        // ============================================================
        // 5. ERROR HANDLER
        // ============================================================
        function showAgoraError(msg) {
            const el = document.getElementById('agora-local-video');
            if (el) {
                el.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full text-slate-400 p-6 text-center">
                        <i class="fas fa-video-slash text-4xl mb-3 text-rose-500"></i>
                        <p class="text-sm font-bold text-slate-300">Kamera tidak dapat diakses</p>
                        <p class="text-xs mt-1 text-slate-500">${msg}</p>
                        <p class="text-xs mt-3 text-amber-400">Pastikan AGORA_APP_ID sudah diisi di .env dan browser diizinkan mengakses kamera/mikrofon.</p>
                    </div>
                `;
            }
        }

        // ============================================================
        // 6. LIVE TIMER KALKULASI
        // ============================================================
        let meetingStartTime = new Date("{{ $course->meeting_started_at ? $course->meeting_started_at->toISOString() : now()->toISOString() }}");

        function updateTimer() {
            const now = new Date();
            const diffMs = Math.max(0, now - meetingStartTime);
            const totalSec = Math.floor(diffMs / 1000);
            const h = String(Math.floor(totalSec / 3600)).padStart(2, '0');
            const m = String(Math.floor((totalSec % 3600) / 60)).padStart(2, '0');
            const s = String(totalSec % 60).padStart(2, '0');
            const timerEl = document.getElementById('class-timer');
            if (timerEl) timerEl.textContent = `${h}:${m}:${s}`;
        }
        setInterval(updateTimer, 1000);
        updateTimer();

        // ============================================================
        // 7. TOGGLE & SWITCH TAB CLASSROOM HUB
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

        function updateViewLabel(mode) {
            const iconEl = document.getElementById('current-view-icon');
            const nameEl = document.getElementById('current-view-name');
            const headerIcon = document.getElementById('header-view-icon');
            const headerName = document.getElementById('header-view-name');
            const checkGal = document.getElementById('check-gallery');
            const checkSpk = document.getElementById('check-speaker');
            const checkSbs = document.getElementById('check-sidebyside');

            if (checkGal) checkGal.classList.add('hidden');
            if (checkSpk) checkSpk.classList.add('hidden');
            if (checkSbs) checkSbs.classList.add('hidden');

            if (mode === 'sidebyside') {
                if (iconEl) iconEl.className = 'fas fa-columns text-emerald-400 text-xs';
                if (nameEl) nameEl.textContent = 'Berdampingan';
                if (headerIcon) headerIcon.className = 'fas fa-columns text-emerald-400';
                if (headerName) headerName.textContent = 'Berdampingan';
                if (checkSbs) checkSbs.classList.remove('hidden');
            } else if (mode === 'speaker') {
                if (iconEl) iconEl.className = 'fas fa-user-tie text-rose-400 text-xs';
                if (nameEl) nameEl.textContent = 'Pembicara';
                if (headerIcon) headerIcon.className = 'fas fa-user-tie text-rose-400';
                if (headerName) headerName.textContent = 'Pembicara';
                if (checkSpk) checkSpk.classList.remove('hidden');
            } else {
                if (iconEl) iconEl.className = 'fas fa-table-cells text-indigo-400 text-xs';
                if (nameEl) nameEl.textContent = 'Galeri';
                if (headerIcon) headerIcon.className = 'fas fa-table-cells text-indigo-400';
                if (headerName) headerName.textContent = 'Galeri';
                if (checkGal) checkGal.classList.remove('hidden');
            }
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
                    btn.classList.add('border-b-2', 'border-indigo-500', 'text-indigo-400');
                    btn.classList.remove('border-transparent', 'text-slate-400');
                    content.classList.remove('hidden');
                    if (t === 'whiteboard') {
                        setTimeout(resizeWhiteboardCanvas, 50);
                    }
                } else {
                    btn.classList.remove('border-b-2', 'border-indigo-500', 'text-indigo-400');
                    btn.classList.add('border-transparent', 'text-slate-400');
                    content.classList.add('hidden');
                }
            });
        }

        function openTabDirect(tabName) {
            switchTab(tabName);
        }

        // ============================================================
        // 8. MATERIAL PREVIEW VIEWER
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
                iconEl.className = 'far fa-file-pdf text-rose-500 text-xs';
            } else if (type === 'video') {
                iconEl.className = 'far fa-play-circle text-amber-400 text-xs';
            } else {
                iconEl.className = 'far fa-file-lines text-indigo-400 text-xs';
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
        // 9. DIGITAL WHITEBOARD LOGIC (HTML5 CANVAS)
        // ============================================================
        const canvas = document.getElementById('whiteboard-canvas');
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let currentTool = 'pen';
        let penColor = '#ffffff';
        let penSize = 4;
        let whiteboardHistory = [];

        function resizeWhiteboardCanvas() {
            const container = document.getElementById('whiteboard-container');
            if (!container) return;
            
            let tempImage = null;
            if (canvas.width > 0 && canvas.height > 0) {
                tempImage = ctx.getImageData(0, 0, canvas.width, canvas.height);
            }

            canvas.width = container.offsetWidth;
            canvas.height = container.offsetHeight;

            if (tempImage) {
                ctx.putImageData(tempImage, 0, 0);
            }
        }

        function saveCanvasState() {
            whiteboardHistory.push(ctx.getImageData(0, 0, canvas.width, canvas.height));
            if (whiteboardHistory.length > 25) whiteboardHistory.shift();
        }

        function undoWhiteboard() {
            if (whiteboardHistory.length > 0) {
                const prev = whiteboardHistory.pop();
                ctx.putImageData(prev, 0, 0);
            } else {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
        }

        function clearWhiteboard() {
            if (confirm('Bersihkan semua tulisan di papan tulis?')) {
                saveCanvasState();
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
        }

        function setWhiteboardTool(tool) {
            currentTool = tool;
            const btnPen = document.getElementById('tool-pen');
            const btnEraser = document.getElementById('tool-eraser');
            if (tool === 'pen') {
                btnPen.classList.add('bg-indigo-600', 'text-white');
                btnPen.classList.remove('bg-slate-800', 'text-slate-300');
                btnEraser.classList.remove('bg-indigo-600', 'text-white');
                btnEraser.classList.add('bg-slate-800', 'text-slate-300');
            } else {
                btnEraser.classList.add('bg-indigo-600', 'text-white');
                btnEraser.classList.remove('bg-slate-800', 'text-slate-300');
                btnPen.classList.remove('bg-indigo-600', 'text-white');
                btnPen.classList.add('bg-slate-800', 'text-slate-300');
            }
        }

        function setPenColor(color) {
            penColor = color;
            setWhiteboardTool('pen');
        }

        function setPenSize(size) {
            penSize = parseInt(size, 10);
        }

        function setCanvasBackground(bgType) {
            const container = document.getElementById('whiteboard-container');
            container.className = 'flex-1 relative overflow-hidden cursor-crosshair';
            if (bgType === 'chalkboard') {
                container.classList.add('canvas-chalkboard');
            } else if (bgType === 'grid') {
                container.classList.add('bg-slate-950', 'canvas-grid-math');
            } else {
                container.classList.add('canvas-whiteboard');
                if (penColor === '#ffffff') {
                    penColor = '#0f172a';
                }
            }
        }

        function downloadWhiteboard() {
            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = canvas.width;
            tempCanvas.height = canvas.height;
            const tempCtx = tempCanvas.getContext('2d');
            
            const container = document.getElementById('whiteboard-container');
            if (container.classList.contains('canvas-chalkboard')) {
                tempCtx.fillStyle = '#14281d';
            } else if (container.classList.contains('canvas-whiteboard')) {
                tempCtx.fillStyle = '#ffffff';
            } else {
                tempCtx.fillStyle = '#090d16';
            }
            tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
            tempCtx.drawImage(canvas, 0, 0);

            const link = document.createElement('a');
            link.download = 'PapanTulis-{{ Str::slug($course->course_name ?? $course->name) }}-' + Date.now() + '.png';
            link.href = tempCanvas.toDataURL('image/png');
            link.click();
        }

        function getCanvasCoords(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        }

        function startDrawing(e) {
            isDrawing = true;
            saveCanvasState();
            const { x, y } = getCanvasCoords(e);
            ctx.beginPath();
            ctx.moveTo(x, y);
        }

        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault();
            const { x, y } = getCanvasCoords(e);
            
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';

            if (currentTool === 'eraser') {
                ctx.globalCompositeOperation = 'destination-out';
                ctx.lineWidth = penSize * 3;
            } else {
                ctx.globalCompositeOperation = 'source-over';
                ctx.strokeStyle = penColor;
                ctx.lineWidth = penSize;
            }

            ctx.lineTo(x, y);
            ctx.stroke();
        }

        function stopDrawing() {
            if (isDrawing) {
                isDrawing = false;
                ctx.closePath();
            }
        }

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        window.addEventListener('mouseup', stopDrawing);

        canvas.addEventListener('touchstart', startDrawing, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        window.addEventListener('touchend', stopDrawing);

        window.addEventListener('resize', resizeWhiteboardCanvas);

        // ============================================================
        // 10. POLLING PRESENSI SISWA (ATTENDANCE)
        // ============================================================
        function fetchAttendees() {
            fetch("{{ route('guru.lms.meeting.attendees', $course->id) }}")
                .then(response => response.json())
                .then(data => {
                    const total = data.total || 0;
                    document.getElementById('header-attendee-count').textContent = total;
                    document.getElementById('attendee-tab-count').textContent = total;
                    document.getElementById('quick-attendee-badge').textContent = total;

                    const listEl = document.getElementById('attendees-list');
                    if (!listEl) return;

                    if (total === 0) {
                        listEl.innerHTML = `
                            <div class="text-center text-xs text-slate-500 py-12">
                                <div class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center mx-auto mb-2 text-slate-400">
                                    <i class="far fa-user text-xl"></i>
                                </div>
                                <p class="font-medium text-slate-300">Belum ada siswa yang masuk.</p>
                                <p class="text-[10px] text-slate-500 mt-1">Salin tautan kelas dan kirim ke grup WhatsApp rombel.</p>
                            </div>
                        `;
                        return;
                    }

                    let html = '';
                    data.attendees.forEach(student => {
                        html += `
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/60 transition-all text-xs">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center text-[11px] font-extrabold text-white shadow-md flex-shrink-0">
                                        ${student.initials}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-200 truncate leading-snug">${student.name}</p>
                                        <span class="text-[10px] text-slate-400 flex items-center gap-1">
                                            <i class="far fa-clock text-indigo-400"></i> ${student.joined_at}
                                        </span>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/15 border border-emerald-500/30 text-emerald-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Hadir
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
                                    nameBadge.innerHTML = `<i class="fas fa-user-graduate text-indigo-400 mr-1"></i>${student.name}`;
                                }
                            }
                        });
                    }
                })
                .catch(err => console.error("Error fetching attendees:", err));
        }

        // ============================================================
        // 11. COPY MEETING LINK & TOAST
        // ============================================================
        function copyMeetingLink() {
            const joinUrl = "{{ route('siswa.lms.meeting.join', $course->id) }}";
            navigator.clipboard.writeText(joinUrl).then(() => {
                const toast = document.getElementById('copy-toast');
                toast.classList.remove('translate-y-12', 'opacity-0');
                toast.classList.add('translate-y-0', 'opacity-100');
                setTimeout(() => {
                    toast.classList.remove('translate-y-0', 'opacity-100');
                    toast.classList.add('translate-y-12', 'opacity-0');
                }, 3500);
            });
        }

        // ============================================================
        // 12. MODAL AKHIRI KELAS
        // ============================================================
        function openEndMeetingModal() {
            document.getElementById('end-meeting-modal').classList.remove('hidden');
        }
        function closeEndMeetingModal() {
            document.getElementById('end-meeting-modal').classList.add('hidden');
        }

        // ============================================================
        // 13. RESIZE DRAG HANDLER LOGIC
        // ============================================================
        const hub = document.getElementById('classroom-hub');
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
            if (newWidth >= 320 && newWidth <= window.innerWidth * 0.65) {
                hub.style.width = newWidth + 'px';
                resizeWhiteboardCanvas();
            }
        });

        document.addEventListener('mouseup', function() {
            if (isResizing) {
                isResizing = false;
                overlay.classList.add('hidden');
                document.body.style.cursor = 'default';
                resizeWhiteboardCanvas();
            }
        });

        // ============================================================
        // 14. INIT SAAT HALAMAN LOAD
        // ============================================================
        document.addEventListener('DOMContentLoaded', () => {
            // Start Agora
            initAgora().catch(err => {
                console.error('Agora init error:', err);
                showAgoraError(err.message || 'Gagal terhubung ke Agora');
            });

            // Start attendance polling
            setInterval(fetchAttendees, 12000);
            fetchAttendees();
        });

        // ============================================================
        // 15. CLEANUP SAAT HALAMAN DITUTUP
        // ============================================================
        window.addEventListener('beforeunload', async () => {
            try {
                if (localAudioTrack) { localAudioTrack.stop(); localAudioTrack.close(); }
                if (localVideoTrack) { localVideoTrack.stop(); localVideoTrack.close(); }
                if (localScreenTrack) { localScreenTrack.stop(); localScreenTrack.close(); }
                if (agoraClient) await agoraClient.leave();
            } catch(e) { /* ignore cleanup errors */ }
        });
    </script>
</body>
</html>
