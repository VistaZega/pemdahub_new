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
        .video-tile {
            position: relative;
            overflow: hidden;
            background-color: #0f172a;
            border-radius: 1rem;
            border: 1px solid rgba(51, 65, 85, 0.8);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .video-tile.speaking {
            box-shadow: 0 0 0 3px #10b981, 0 0 20px rgba(16, 185, 129, 0.5) !important;
            border-color: #10b981 !important;
        }
        .video-tile.pinned-speaker {
            border-color: #6366f1 !important;
            box-shadow: 0 0 25px rgba(99, 102, 241, 0.35) !important;
        }
        .filmstrip-tile {
            height: 96px !important;
            width: 170px !important;
            flex-shrink: 0 !important;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 h-screen flex flex-col overflow-hidden select-none">

    {{-- 1. SMART CLASSROOM HEADER (SISWA) --}}
    <header class="bg-slate-900/95 backdrop-blur-md border-b border-slate-800 px-4 sm:px-6 py-2.5 flex items-center justify-between gap-3 z-30 flex-shrink-0 shadow-lg">
        
        {{-- Info Kelas & Guru Pengampu --}}
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-cyan-500 p-0.5 shadow-lg shadow-indigo-900/30 flex-shrink-0">
                <div class="w-full h-full bg-slate-900 rounded-[10px] flex items-center justify-center text-indigo-400 font-bold">
                    <i class="fas fa-video text-base"></i>
                </div>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-500/15 border border-indigo-500/30 text-indigo-400 uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-ping"></span> Mengikuti PembdaTEMU
                    </span>
                    <span class="text-[11px] font-semibold text-slate-400 hidden md:inline truncate">
                        {{ $course->school?->name ?? 'Perguruan Pembda' }}
                    </span>
                </div>
                <h1 class="text-sm sm:text-base font-extrabold text-white truncate leading-tight mt-0.5">
                    {{ $course->course_name ?? $course->name }}
                    @if($course->teacher && $course->teacher->user)
                        <span class="text-xs font-semibold text-slate-400 font-normal hidden sm:inline"> • Guru: {{ $course->teacher->user->name }}</span>
                    @endif
                </h1>
            </div>
        </div>

        {{-- Center Status Widget: Durasi Hadir & Sinyal --}}
        <div class="hidden lg:flex items-center gap-2.5 bg-slate-950/70 border border-slate-800/90 rounded-2xl px-3.5 py-1.5 shadow-inner">
            <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-400" title="Durasi kehadiran Anda di kelas ini">
                <i class="far fa-clock text-emerald-500 animate-pulse"></i>
                <span id="student-timer" class="font-mono tracking-wider">00:00:00</span>
            </div>
            <div class="w-px h-3.5 bg-slate-800"></div>
            <div id="connection-status-badge" class="flex items-center gap-1.5 text-xs font-semibold text-slate-300">
                <i class="fas fa-signal text-emerald-400 text-[11px]"></i>
                <span id="mode-status-text">Koneksi Normal</span>
            </div>
        </div>

        {{-- Right Controls --}}
        <div class="flex items-center gap-2 flex-shrink-0">
            
            {{-- Tombol Mode Hemat Kuota --}}
            <button onclick="toggleDataSaverMode()" type="button" id="btn-data-saver" class="hidden sm:flex items-center gap-1.5 bg-slate-800/90 hover:bg-slate-700 text-slate-200 border border-slate-700 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all active:scale-95" title="Turunkan kualitas video untuk menghemat kuota internet dan memperlancar audio">
                <i class="fas fa-bolt text-amber-400"></i>
                <span id="data-saver-label">Hemat Kuota</span>
            </button>

            {{-- Tombol Pilihan Tampilan (Zoom View Selector) --}}
            <div class="relative inline-block text-left" id="header-view-wrapper">
                <button onclick="toggleViewMenu(event)" type="button" class="flex items-center gap-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 px-3 py-1.5 rounded-xl text-xs font-bold transition-all active:scale-95 shadow-sm" title="Pilihan Tata Letak Tampilan (Zoom)">
                    <i id="header-view-icon" class="fas fa-user-tie text-rose-400"></i>
                    <span class="hidden md:inline">Tampilan: <span id="header-view-name" class="text-white">Pembicara</span></span>
                    <i class="fas fa-chevron-down text-[9px] text-slate-400"></i>
                </button>
            </div>

            {{-- Tombol Buka Materi --}}
            <button onclick="toggleStudentHub()" type="button" id="btn-student-hub" class="flex items-center gap-1.5 bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 px-3 py-1.5 rounded-xl text-xs font-bold transition-all active:scale-95" title="Buka modul dan materi pelajaran">
                <i class="fas fa-book-open text-indigo-400"></i>
                <span class="hidden md:inline">Materi & Catatan</span>
            </button>

            {{-- Student Profile Chip --}}
            <div class="hidden xl:flex items-center gap-2 bg-slate-800/80 border border-slate-700 px-2.5 py-1 rounded-xl text-xs text-slate-300">
                <div class="w-6 h-6 rounded-full bg-cyan-600/80 flex items-center justify-center text-[10px] font-bold text-white uppercase">
                    {{ substr($displayName, 0, 1) }}
                </div>
                <span class="font-medium max-w-[110px] truncate">{{ $displayName }}</span>
            </div>

            {{-- Tombol Keluar Kelas --}}
            <button onclick="openLeaveModal()" type="button" class="bg-slate-800 hover:bg-rose-600/20 text-slate-300 hover:text-rose-400 border border-slate-700 hover:border-rose-500/40 font-bold px-3.5 py-1.5 rounded-xl text-xs transition-all flex items-center gap-1.5 active:scale-95">
                <i class="fas fa-door-open text-xs"></i>
                <span>Keluar</span>
            </button>
        </div>
    </header>

    {{-- 2. MAIN WORKSPACE CONTAINER --}}
    <div class="flex-1 flex flex-row overflow-hidden relative" style="height: calc(100vh - 61px);">
        
        {{-- Conference Arena (Agora Video Stage) --}}
        <main id="conference-panel" class="flex-1 relative bg-slate-950 flex flex-col h-full overflow-hidden transition-all duration-150">
            
            {{-- FLOATING ZOOM-STYLE VIEW SELECTOR (POJOK KANAN ATAS VIDEO) --}}
            <div class="absolute top-3.5 right-3.5 z-30 flex items-center gap-2">
                <div class="relative" id="video-view-dropdown-wrapper">
                    <button id="btn-view-toggle" onclick="toggleViewMenu(event)" type="button" class="flex items-center gap-2 bg-slate-900/90 hover:bg-slate-800 text-slate-200 hover:text-white px-3.5 py-1.5 rounded-xl border border-slate-700/80 text-xs font-bold transition shadow-xl backdrop-blur-md active:scale-95" title="Ganti Tata Letak Video (Zoom)">
                        <i id="current-view-icon" class="fas fa-user-tie text-rose-400 text-xs"></i>
                        <span>Tampilan: <span id="current-view-name" class="text-white">Pembicara</span></span>
                        <i class="fas fa-chevron-down text-[9px] text-slate-400"></i>
                    </button>

                    <div id="view-dropdown-menu" class="hidden absolute right-0 mt-2 w-56 rounded-2xl bg-slate-900/95 border border-slate-700/80 shadow-2xl p-2 z-50 backdrop-blur-xl transition-all">
                        <div class="px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-800 mb-1">
                            Pilihan Tata Letak (Zoom)
                        </div>
                        
                        <button onclick="changeStudentLayout('speaker')" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold hover:bg-slate-800 text-slate-200 hover:text-white flex items-center justify-between transition-colors">
                            <span class="flex items-center gap-2.5">
                                <i class="fas fa-user-tie text-rose-400 w-4"></i>
                                <span>Tampilan Pembicara</span>
                            </span>
                            <i id="check-speaker" class="fas fa-check text-rose-400 text-xs"></i>
                        </button>

                        <button onclick="changeStudentLayout('gallery')" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold hover:bg-slate-800 text-slate-200 hover:text-white flex items-center justify-between transition-colors">
                            <span class="flex items-center gap-2.5">
                                <i class="fas fa-table-cells text-indigo-400 w-4"></i>
                                <span>Tampilan Galeri</span>
                            </span>
                            <i id="check-gallery" class="fas fa-check text-indigo-400 text-xs hidden"></i>
                        </button>

                        <button onclick="changeStudentLayout('sidebyside')" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold hover:bg-slate-800 text-slate-200 hover:text-white flex items-center justify-between transition-colors">
                            <span class="flex items-center gap-2.5">
                                <i class="fas fa-columns text-emerald-400 w-4"></i>
                                <span>Berdampingan (Materi)</span>
                            </span>
                            <i id="check-sidebyside" class="fas fa-check text-emerald-400 text-xs hidden"></i>
                        </button>

                        <div class="h-px bg-slate-800 my-1.5"></div>

                        <button onclick="toggleStudentFullscreen()" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold hover:bg-slate-800 text-slate-300 hover:text-white flex items-center gap-2.5 transition-colors">
                            <i id="menu-fs-icon" class="fas fa-expand text-amber-400 w-4"></i>
                            <span id="menu-fs-text">Layar Penuh (F11)</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- 1. SPEAKER VIEW STAGE (Default Siswa: Focus ke Guru/Host) --}}
            <div id="stage-speaker-wrapper" class="flex-1 flex flex-col h-full overflow-hidden p-3 gap-2.5 relative min-h-0">
                {{-- Top Filmstrip Bar: Thumbnail peserta lain --}}
                <div id="speaker-filmstrip" class="flex items-center gap-2.5 overflow-x-auto p-1.5 h-28 shrink-0 bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-sm scroll-smooth">
                    {{-- Non-pinned tiles live here --}}
                </div>
                {{-- Main Spotlight Screen: 1 Peserta Terfokus (Guru/Siswa) --}}
                <div id="speaker-main-stage" class="flex-1 flex items-center justify-center overflow-hidden min-h-0 relative p-1">
                    {{-- Pinned tile lives here --}}
                </div>
            </div>

            {{-- 2. GALLERY VIEW STAGE (Active when currentLayout === 'gallery' or 'sidebyside') --}}
            <div id="stage-gallery-wrapper" class="hidden flex-1 items-center justify-center overflow-hidden h-full p-3 min-h-0 relative">
                <div id="gallery-grid" class="w-full h-full flex items-center justify-center gap-3 transition-all duration-300">
                    {{-- All tiles reside here in gallery mode --}}
                </div>
            </div>

            {{-- MASTER INITIAL TILES CONTAINER (Akan dipindahkan oleh rearrangeTiles) --}}
            <div id="master-tiles-pool" class="hidden">
                {{-- 1. Video Tile GURU (Host) --}}
                <div id="video-tile-teacher" class="video-tile group relative rounded-2xl overflow-hidden bg-slate-900 border border-slate-700/80 shadow-2xl flex items-center justify-center w-full max-w-5xl h-full max-h-[78vh] aspect-video mx-auto my-auto transition-all">
                    <div id="agora-teacher-video" class="w-full h-full relative"></div>
                    <div id="teacher-waiting-placeholder" class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 p-6 text-center bg-slate-900 pointer-events-none">
                        <div class="w-16 h-16 rounded-2xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 mb-3 animate-pulse">
                            <i class="fas fa-chalkboard-user text-2xl"></i>
                        </div>
                        <h3 class="text-base font-bold text-white">Menghubungkan ke Ruang PembdaTEMU...</h3>
                        <p class="text-xs text-slate-400 mt-1 max-w-sm">Guru sedang menyiapkan siaran. Video & audio guru akan otomatis tampil di sini saat mulai mengajar.</p>
                        <span class="mt-3 inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span> Channel: {{ $agoraChannel }}
                        </span>
                    </div>
                    <div class="absolute bottom-2.5 left-2.5 z-10 flex items-center gap-1.5 bg-slate-950/80 backdrop-blur-md px-2.5 py-1 rounded-xl text-[11px] font-bold text-white border border-slate-800 shadow-md">
                        <i id="teacher-audio-indicator" class="fas fa-volume-high text-emerald-400 text-[10px]"></i>
                        <span class="truncate max-w-[170px]"><i class="fas fa-chalkboard-user text-rose-400 mr-1"></i>{{ $course->teacher->user->name ?? 'Guru Pengajar' }} (Guru)</span>
                    </div>
                    <div class="absolute top-2.5 right-2.5 z-10 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button onclick="pinStudentSpeaker('teacher')" type="button" class="p-1.5 rounded-lg bg-slate-950/80 hover:bg-indigo-600 text-slate-300 hover:text-white border border-slate-700 text-xs shadow-md transition" title="Fokuskan / Spotlight">
                            <i class="fas fa-thumbtack text-[10px]"></i>
                        </button>
                    </div>
                </div>

                {{-- 2. Video Tile SAYA (Siswa Sendiri) --}}
                <div id="video-tile-local" class="video-tile group relative rounded-2xl overflow-hidden bg-slate-900 border border-slate-700/80 shadow-2xl flex items-center justify-center aspect-video transition-all">
                    <div id="agora-student-video" class="w-full h-full relative"></div>
                    <div id="student-cam-off-placeholder" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-900 text-slate-500 text-xs pointer-events-none">
                        <div class="w-14 h-14 rounded-full bg-slate-800 border-2 border-slate-700 flex items-center justify-center text-xl font-bold text-cyan-400 mb-1.5 shadow-inner">
                            {{ substr($displayName, 0, 1) }}
                        </div>
                        <span class="text-[11px] font-semibold text-slate-400">Kamera Saya Nonaktif</span>
                    </div>
                    <div class="absolute bottom-2.5 left-2.5 z-10 flex items-center gap-1.5 bg-slate-950/80 backdrop-blur-md px-2.5 py-1 rounded-xl text-[11px] font-bold text-white border border-slate-800 shadow-md">
                        <i id="tile-mic-local" class="fas fa-microphone-slash text-rose-400 text-[10px]"></i>
                        <span class="truncate max-w-[150px]">Saya ({{ explode(' ', $displayName)[0] }})</span>
                    </div>
                    <div class="absolute top-2.5 right-2.5 z-10 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button onclick="pinStudentSpeaker('local')" type="button" class="p-1.5 rounded-lg bg-slate-950/80 hover:bg-indigo-600 text-slate-300 hover:text-white border border-slate-700 text-xs shadow-md transition" title="Fokuskan / Spotlight">
                            <i class="fas fa-thumbtack text-[10px]"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Floating Student Action Bar --}}
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2 bg-slate-900/90 backdrop-blur-md border border-slate-700/80 px-3 py-2 rounded-2xl shadow-2xl">
                
                {{-- Toggle Mic Siswa --}}
                <button onclick="toggleStudentMic()" id="btn-toggle-mic" type="button" class="group relative p-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 transition active:scale-95" title="Aktifkan / Matikan Mikrofon">
                    <i id="mic-icon" class="fas fa-microphone-slash text-rose-400 text-sm"></i>
                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap border border-slate-700">Mic</span>
                </button>

                {{-- Toggle Kamera Siswa --}}
                <button onclick="toggleStudentCam()" id="btn-toggle-cam" type="button" class="group relative p-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 transition active:scale-95" title="Aktifkan / Matikan Kamera">
                    <i id="cam-icon" class="fas fa-video-slash text-rose-400 text-sm"></i>
                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[10px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap border border-slate-700">Kamera</span>
                </button>

                <div class="w-px h-5 bg-slate-700/80"></div>

                {{-- Angkat Tangan (Raise Hand) --}}
                <button onclick="studentToggleRaiseHand()" id="btn-raise-hand" type="button" class="flex items-center gap-1.5 bg-slate-800 hover:bg-amber-600/30 text-slate-200 hover:text-amber-300 border border-slate-700 hover:border-amber-500/50 px-3 py-2 rounded-xl text-xs font-bold transition-all active:scale-95" title="Beri tanda ke guru bahwa Anda ingin bertanya">
                    <i class="fas fa-hand text-amber-400"></i>
                    <span id="raise-hand-label" class="hidden sm:inline">Angkat Tangan</span>
                </button>

                {{-- Buka Materi Cepat --}}
                <button onclick="openStudentTab('materi')" type="button" class="flex items-center gap-1.5 bg-slate-800 hover:bg-indigo-600/30 text-slate-200 hover:text-indigo-300 border border-slate-700 hover:border-indigo-500/50 px-3 py-2 rounded-xl text-xs font-semibold transition-all active:scale-95" title="Buka materi modul pelajaran">
                    <i class="fas fa-book text-indigo-400"></i>
                    <span class="hidden sm:inline">Modul</span>
                </button>

                {{-- Buka Catatan / Coretan --}}
                <button onclick="openStudentTab('notes')" type="button" class="flex items-center gap-1.5 bg-slate-800 hover:bg-emerald-600/30 text-slate-200 hover:text-emerald-300 border border-slate-700 hover:border-emerald-500/50 px-3 py-2 rounded-xl text-xs font-semibold transition-all active:scale-95" title="Buka papan catatan pribadi">
                    <i class="fas fa-pen-to-square text-emerald-400"></i>
                    <span class="hidden sm:inline">Catatan</span>
                </button>
            </div>
        </main>

        {{-- Resize Handle Divider --}}
        <div id="resize-handler" class="w-[8px] bg-slate-850 hover:bg-indigo-500 cursor-col-resize select-none z-20 flex items-center justify-center relative transition-colors duration-150 group" title="Geser untuk mengatur lebar panel materi">
            <div class="w-4 h-12 bg-slate-900 border border-slate-700 group-hover:border-indigo-400 rounded-md flex items-center justify-center shadow-lg pointer-events-none">
                <i class="fas fa-grip-lines-vertical text-[10px] text-slate-400 group-hover:text-indigo-300"></i>
            </div>
        </div>

        {{-- Resize Overlay --}}
        <div id="resize-overlay" class="fixed inset-0 z-40 hidden cursor-col-resize"></div>

        {{-- 3. STUDENT LEARNING COMPANION HUB --}}
        <aside id="student-hub" class="w-[480px] max-w-[90vw] border-l border-slate-800 bg-slate-900/95 flex flex-col h-full z-15 shadow-2xl relative">
            
            {{-- Tabs Header Bar --}}
            <div class="border-b border-slate-800 flex items-center bg-slate-950/60 px-2 pt-1 flex-shrink-0">
                <button id="tab-btn-materi" onclick="switchStudentTab('materi')" type="button" class="flex-1 py-2.5 px-3 text-xs font-bold transition-all flex items-center justify-center gap-1.5 border-b-2 border-indigo-500 text-indigo-400">
                    <i class="fas fa-book text-xs"></i>
                    <span>Materi & Modul</span>
                </button>
                <button id="tab-btn-notes" onclick="switchStudentTab('notes')" type="button" class="flex-1 py-2.5 px-3 text-xs font-semibold text-slate-400 hover:text-slate-200 transition-all flex items-center justify-center gap-1.5 border-b-2 border-transparent">
                    <i class="fas fa-pen-to-square text-xs"></i>
                    <span>Catatan Siswa</span>
                </button>
                <button id="tab-btn-friends" onclick="switchStudentTab('friends')" type="button" class="flex-1 py-2.5 px-3 text-xs font-semibold text-slate-400 hover:text-slate-200 transition-all flex items-center justify-center gap-1.5 border-b-2 border-transparent">
                    <i class="fas fa-users text-xs"></i>
                    <span>Teman Sekelas</span>
                </button>
                <button onclick="toggleStudentHub()" type="button" class="p-2 text-slate-400 hover:text-white rounded-lg ml-1 hover:bg-slate-800 transition-colors" title="Tutup panel">
                    <i class="fas fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- TAB 1: MATERI & MODUL --}}
            <div id="tab-content-materi" class="flex-1 flex flex-col overflow-hidden">
                
                {{-- Viewer Preview Box --}}
                <div id="material-preview-box" class="h-1/2 min-h-[220px] border-b border-slate-800 bg-slate-950 flex flex-col hidden">
                    <div class="px-4 py-2 border-b border-slate-800/80 bg-slate-900/60 flex items-center justify-between">
                        <div class="flex items-center gap-2 min-w-0">
                            <i id="preview-icon" class="far fa-file-pdf text-rose-500 text-xs"></i>
                            <h3 id="preview-title" class="text-xs font-bold text-slate-200 truncate">Materi Pelajaran</h3>
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

                {{-- Daftar Modul & Materi --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-3">
                    <div class="flex items-center justify-between pb-1">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Silabus & Bahan Ajar</span>
                        <span class="text-[10px] text-slate-500">Klik "Buka" untuk menyimak</span>
                    </div>

                    @php
                        $modules = $course->modules()->ordered()->active()->get();
                    @endphp

                    @if($modules->isEmpty())
                        <div class="text-center text-xs text-slate-500 py-12">
                            <i class="far fa-folder-open text-3xl mb-2 text-slate-600 block"></i>
                            <p class="font-medium">Belum ada modul atau materi di kelas ini.</p>
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
                                            <button onclick="displayStudentMaterial('{{ $safeTitle }}', '{{ $material->material_type }}', '{{ $url }}', '{{ $safeContent }}')" type="button" class="text-[10px] bg-slate-800 hover:bg-indigo-600 text-indigo-300 hover:text-white font-bold px-2 py-1 rounded-md border border-slate-700 transition-all flex items-center gap-1 flex-shrink-0">
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
            <div id="tab-content-notes" class="flex-1 flex flex-col overflow-hidden hidden">
                <div class="p-3 bg-slate-950/60 border-b border-slate-800 flex items-center justify-between flex-shrink-0">
                    <div>
                        <h3 class="text-xs font-bold text-slate-200">Catatan Belajar Mandiri</h3>
                        <p class="text-[10px] text-slate-400">Catatan otomatis tersimpan di peramban (browser) Anda.</p>
                    </div>
                    <span id="notes-saved-hint" class="text-[10px] text-slate-500">Tersimpan otomatis</span>
                </div>
                <div class="flex-1 p-4 flex flex-col">
                    <textarea id="student-notes-area" class="w-full flex-1 bg-slate-950/80 border border-slate-800 rounded-xl p-3 text-xs text-slate-200 placeholder-slate-500 resize-none outline-none focus:border-indigo-500 transition-colors font-mono leading-relaxed" placeholder="Tuliskan poin-poin penjelasan penting dari guru di sini..."></textarea>
                </div>
            </div>

            {{-- TAB 3: TEMAN SEKELAS --}}
            <div id="tab-content-friends" class="flex-1 flex flex-col overflow-hidden hidden">
                <div class="p-3 bg-slate-950/60 border-b border-slate-800 flex items-center justify-between flex-shrink-0">
                    <div>
                        <h3 class="text-xs font-bold text-slate-200">Teman Sekelas Hadir</h3>
                        <p class="text-[10px] text-slate-400">Daftar siswa yang sedang aktif mengikuti tatap muka.</p>
                    </div>
                    <span id="friends-badge-count" class="text-[10px] bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-2 py-0.5 rounded-full font-bold">0 Online</span>
                </div>
                <div class="flex-1 overflow-y-auto p-4 space-y-2" id="friends-list">
                    <div class="text-center text-xs text-slate-500 py-12">
                        <i class="fas fa-spinner animate-spin text-xl mb-2 text-indigo-400 block"></i>
                        <p>Memuat teman sekelas...</p>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    {{-- MODAL KONFIRMASI KELUAR KELAS --}}
    <div id="leave-meeting-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm p-4 hidden">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-500 flex items-center justify-center text-xl">
                <i class="fas fa-door-open"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-white">Tinggalkan Sesi PembdaTEMU?</h3>
                <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                    Durasi kehadiran Anda sampai detik ini akan tersimpan di rekap absensi guru. Anda dapat bergabung kembali selama guru masih membuka kelas.
                </p>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2">
                <button onclick="closeLeaveModal()" type="button" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 transition-colors">
                    Batal
                </button>
                <button onclick="executeLeaveMeeting()" type="button" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 transition-colors shadow-lg shadow-rose-900/30 flex items-center gap-1.5">
                    <i class="fas fa-check"></i> Ya, Tinggalkan PembdaTEMU
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
                if (iconEl) iconEl.className = 'fas fa-table-cells text-indigo-400 text-xs';
                if (nameEl) nameEl.textContent = 'Galeri';
                if (headerIcon) headerIcon.className = 'fas fa-table-cells text-indigo-400';
                if (headerName) headerName.textContent = 'Galeri';
                if (checkGal) checkGal.classList.remove('hidden');
            } else if (mode === 'sidebyside') {
                if (iconEl) iconEl.className = 'fas fa-columns text-emerald-400 text-xs';
                if (nameEl) nameEl.textContent = 'Berdampingan';
                if (headerIcon) headerIcon.className = 'fas fa-columns text-emerald-400';
                if (headerName) headerName.textContent = 'Berdampingan';
                if (checkSbs) checkSbs.classList.remove('hidden');
            } else {
                if (iconEl) iconEl.className = 'fas fa-user-tie text-rose-400 text-xs';
                if (nameEl) nameEl.textContent = 'Pembicara';
                if (headerIcon) headerIcon.className = 'fas fa-user-tie text-rose-400';
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
                if (fsIcon) fsIcon.className = 'fas fa-compress text-amber-400 w-4';
                if (fsText) fsText.textContent = 'Keluar Layar Penuh';
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
                if (fsIcon) fsIcon.className = 'fas fa-expand text-amber-400 w-4';
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

        function createRemoteStudentTile(user) {
            const tile = document.createElement('div');
            tile.id = `video-tile-${user.uid}`;
            tile.className = 'video-tile group relative rounded-2xl overflow-hidden bg-slate-900 border border-slate-700/80 shadow-lg flex items-center justify-center transition-all duration-300';
            
            const studentName = attendeesMap[user.uid] || `Siswa #${user.uid}`;

            tile.innerHTML = `
                <div id="remote-${user.uid}" class="w-full h-full relative"></div>
                <div id="remote-placeholder-${user.uid}" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-900 text-slate-400 hidden pointer-events-none">
                    <div class="w-14 h-14 rounded-full bg-slate-800 border-2 border-slate-700 flex items-center justify-center text-xl font-bold text-indigo-400 mb-1.5 shadow-inner">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <p class="text-xs font-semibold text-slate-300">${studentName}</p>
                </div>
                <div class="absolute bottom-2.5 left-2.5 z-10 flex items-center gap-1.5 bg-slate-950/80 backdrop-blur-md px-2.5 py-1 rounded-xl text-[11px] font-bold text-white border border-slate-800 shadow-md">
                    <i id="tile-mic-${user.uid}" class="fas fa-microphone text-emerald-400 text-[10px]"></i>
                    <span id="tile-name-${user.uid}" class="truncate max-w-[150px]"><i class="fas fa-user-graduate text-indigo-400 mr-1"></i>${studentName}</span>
                </div>
                <div class="absolute top-2.5 right-2.5 z-10 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button onclick="pinStudentSpeaker('${user.uid}')" type="button" class="p-1.5 rounded-lg bg-slate-950/80 hover:bg-indigo-600 text-slate-300 hover:text-white border border-slate-700 text-xs shadow-md transition" title="Fokuskan / Spotlight">
                        <i class="fas fa-thumbtack text-[10px]"></i>
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
                    pinnedTile.className = 'video-tile relative rounded-2xl overflow-hidden bg-slate-900 border-2 border-indigo-500/60 shadow-2xl flex items-center justify-center w-full h-full max-h-[75vh] aspect-video transition-all pinned-speaker';
                    pinnedTile.onclick = null;
                }

                allTiles.forEach(tile => {
                    if (tile !== pinnedTile && filmstrip) {
                        filmstrip.appendChild(tile);
                        tile.className = 'video-tile filmstrip-tile relative rounded-xl overflow-hidden bg-slate-900 border border-slate-700 shadow-md flex items-center justify-center aspect-video cursor-pointer transition-all hover:scale-105';
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
                    icon.className = 'fas fa-microphone text-emerald-400 text-sm';
                    btn.classList.add('border-emerald-500/40', 'bg-emerald-500/20');
                    btn.classList.remove('border-slate-700', 'bg-slate-800');
                } else {
                    icon.className = 'fas fa-microphone-slash text-rose-400 text-sm';
                    btn.classList.remove('border-emerald-500/40', 'bg-emerald-500/20');
                    btn.classList.add('border-slate-700', 'bg-slate-800');
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
                    icon.className = 'fas fa-video text-emerald-400 text-sm';
                    btn.classList.add('border-emerald-500/40', 'bg-emerald-500/20');
                    btn.classList.remove('border-slate-700', 'bg-slate-800');
                } else {
                    icon.className = 'fas fa-video-slash text-rose-400 text-sm';
                    btn.classList.remove('border-emerald-500/40', 'bg-emerald-500/20');
                    btn.classList.add('border-slate-700', 'bg-slate-800');
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
                btn.classList.add('bg-amber-600', 'text-white', 'border-amber-400');
                btn.classList.remove('bg-slate-800', 'text-slate-200');
                if (label) label.textContent = 'Tangan Terangkat';
            } else {
                btn.classList.remove('bg-amber-600', 'text-white', 'border-amber-400');
                btn.classList.add('bg-slate-800', 'text-slate-200');
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
                // Matikan kamera sendiri jika aktif untuk hemat kuota
                if (localVideoTrack && isCamOn) {
                    await toggleStudentCam();
                }
                if (btn) {
                    btn.classList.add('bg-amber-600', 'text-white', 'border-amber-400');
                    btn.classList.remove('bg-slate-800', 'text-slate-200');
                }
                if (label) label.textContent = 'Hemat Aktif';
                if (statusText) statusText.textContent = '⚡ Hemat Kuota';
                alert('Mode Hemat Kuota Aktif: Mengutamakan kelancaran suara guru dan mematikan pengiriman video dari perangkat Anda.');
            } else {
                if (btn) {
                    btn.classList.remove('bg-amber-600', 'text-white', 'border-amber-400');
                    btn.classList.add('bg-slate-800', 'text-slate-200');
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
                    btn.classList.add('border-b-2', 'border-indigo-500', 'text-indigo-400');
                    btn.classList.remove('border-transparent', 'text-slate-400');
                    content.classList.remove('hidden');
                } else {
                    btn.classList.remove('border-b-2', 'border-indigo-500', 'text-indigo-400');
                    btn.classList.add('border-transparent', 'text-slate-400');
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
                    hint.textContent = 'Tersimpan pada ' + new Date().toLocaleTimeString('id-ID');
                    hint.classList.add('text-emerald-400');
                    setTimeout(() => hint.classList.remove('text-emerald-400'), 2000);
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
                        listEl.innerHTML = '<p class="text-center text-xs text-slate-500 py-8">Belum ada siswa lain bergabung.</p>';
                        return;
                    }

                    let html = '';
                    data.attendees.forEach(stu => {
                        attendeesMap[stu.id] = stu.name;

                        // Perbarui nama di tile video jika sudah ada
                        const tileNameSpan = document.getElementById(`tile-name-${stu.id}`);
                        if (tileNameSpan) {
                            tileNameSpan.innerHTML = `<i class="fas fa-user-graduate text-indigo-400 mr-1"></i>${stu.name}`;
                        }
                        const tilePlaceholder = document.querySelector(`#remote-placeholder-${stu.id} p`);
                        if (tilePlaceholder) {
                            tilePlaceholder.textContent = stu.name;
                        }

                        html += `
                            <div class="flex items-center gap-2.5 p-2 rounded-xl bg-slate-800/60 border border-slate-700/60 text-xs">
                                <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-cyan-600 to-indigo-600 flex items-center justify-center text-[10px] font-bold text-white shadow-sm">
                                    ${stu.initials}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-slate-200 truncate">${stu.name}</p>
                                    <span class="text-[9px] text-slate-400 flex items-center gap-1">
                                        <i class="far fa-clock text-cyan-400"></i> ${stu.joined_at}
                                    </span>
                                </div>
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
