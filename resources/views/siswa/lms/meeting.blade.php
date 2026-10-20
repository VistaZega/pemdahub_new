<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PembdaTEMU Siswa: {{ $course->course_name ?? $course->name }} - PembdaHUB</title>
    
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
            border-color: #2563eb !important;
            box-shadow: 0 0 0 4px #2563eb, 6px 6px 0px #121316 !important;
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
    <header class="bg-[#faf8f5] border-b-2 border-[#121316] px-4 sm:px-6 py-2.5 flex items-center justify-between gap-3 z-30 flex-shrink-0 shadow-xs" style="background-color: #faf8f5; border-bottom: 2.5px solid #121316;">
        
        {{-- Brand, Unit & Info Kelas --}}
        <div class="flex items-center gap-3 min-w-0">
            <div class="flex items-center gap-1.5 flex-shrink-0">
                <a href="{{ route('siswa.dashboard') }}" title="Kembali ke Dashboard Siswa" class="hover:scale-105 transition-transform flex-shrink-0">
                    <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo PembdaHUB" class="w-9 h-9 object-contain rounded-xl bg-white p-0.5" style="border: 2px solid #121316; box-shadow: 2px 2px 0px #2563eb;">
                </a>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-mono-code font-black uppercase tracking-wider" style="background-color: #2563eb; color: #ffffff; border: 1.5px solid #121316; box-shadow: 2px 2px 0px #121316;">
                        <span class="w-2 h-2 rounded-full animate-ping" style="background-color: #fde047;"></span> ✱ PembdaTEMU SISWA
                    </span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-mono-code font-extrabold hidden md:inline truncate" style="background-color: #fde047; color: #121316; border: 1.5px solid #121316;">
                        {{ $course->school?->name ?? 'Perguruan Pembda' }}
                    </span>
                </div>
                <h1 class="text-sm sm:text-base font-black truncate leading-tight mt-1 flex items-center gap-1.5" style="color: #121316;">
                    <span class="highlight-marker">{{ $course->course_name ?? $course->name }}</span>
                    @if($course->teacher && $course->teacher->user)
                        <span class="text-xs font-bold hidden sm:inline" style="color: #555555;">• Guru: {{ $course->teacher->user->name }}</span>
                    @endif
                </h1>
            </div>
        </div>

        {{-- Center Status Widget: Durasi Hadir & Sinyal (JetBrains Mono Tactile) --}}
        <div class="hidden lg:flex items-center gap-3 rounded-2xl px-4 py-1.5" style="background-color: #121316; color: #ffffff; border: 2px solid #121316; box-shadow: 3px 3px 0px #2563eb;">
            <div class="flex items-center gap-2 text-xs font-mono-code font-bold" style="color: #fde047;" title="Durasi kehadiran Anda di kelas ini">
                <i class="fa-regular fa-clock text-sm animate-spin" style="color: #fbc02d; animation-duration: 8s;"></i>
                <span id="student-timer" class="font-mono-code tracking-widest text-sm font-black" style="color: #ffffff;">00:00:00</span>
            </div>
            <div class="w-px h-4" style="background-color: rgba(255,255,255,0.3);"></div>
            <div id="connection-status-badge" class="flex items-center gap-1.5 text-xs font-mono-code font-black" style="color: #10b981;">
                <span class="w-2 h-2 rounded-full animate-ping" style="background-color: #10b981;"></span>
                <span id="mode-status-text">Koneksi Aktif</span>
            </div>
            <div class="w-px h-4" style="background-color: rgba(255,255,255,0.3);"></div>
            <div class="flex items-center gap-1.5 text-xs font-mono-code font-black text-amber-300" title="Reward Reputasi: +25 Poin Siswa (min. 5 menit hadir)">
                <i class="fa-solid fa-award text-amber-400"></i>
                <span>+25 Pts</span>
            </div>
        </div>

        {{-- Right Controls (Tactile Buttons with Icon Boxes) --}}
        <div class="flex items-center gap-2 flex-shrink-0">
            
            {{-- Tombol Mode Hemat Kuota (Amber Tactile) --}}
            <button onclick="toggleDataSaverMode()" type="button" id="btn-data-saver" class="hidden sm:flex items-center gap-1.5 btn-tactile-white px-3 py-1.5 rounded-xl text-xs font-mono-code font-black active:scale-95" title="Turunkan kualitas video untuk menghemat kuota internet dan memperlancar audio">
                <div class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] border border-black" style="background-color: #fbc02d; color: #121316;">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <span id="data-saver-label">Hemat Kuota</span>
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

            {{-- Tombol Buka Materi & Catatan (Indigo Tactile) --}}
            <button onclick="toggleStudentHub()" type="button" id="btn-student-hub" class="flex items-center gap-1.5 btn-tactile-white px-3.5 py-1.5 rounded-xl text-xs font-mono-code font-black active:scale-95" title="Buka modul dan catatan pelajaran">
                <div class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] border border-black" style="background-color: #4f46e5; color: #ffffff;">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <span class="hidden md:inline">Materi & Catatan</span>
            </button>

            {{-- Student Profile Chip --}}
            <div class="hidden xl:flex items-center gap-2 bg-white px-2.5 py-1 rounded-xl text-xs" style="border: 2px solid #121316; box-shadow: 2px 2px 0px #121316; color: #121316;" title="{{ $displayName }}">
                <div class="w-6 h-6 rounded-lg border border-black flex items-center justify-center text-[11px] font-black uppercase shadow-xs" style="background-color: #2563eb; color: #ffffff;">
                    {{ substr($displayName, 0, 1) }}
                </div>
                <span class="font-mono-code font-black max-w-[100px] truncate" style="color: #121316;">{{ explode(' ', trim($displayName))[0] }}</span>
            </div>

            {{-- Tombol Keluar Kelas (Crimson Tactile) --}}
            <button onclick="openLeaveModal()" type="button" class="btn-tactile-red px-3.5 sm:px-4 py-1.5 rounded-xl text-xs font-mono-code font-black flex items-center gap-1.5 active:scale-95">
                <i class="fa-solid fa-door-open text-xs"></i>
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

                        <button onclick="changeStudentLayout('classroom')" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-mono-code font-bold hover:bg-[#faf3e0] flex items-center justify-between transition-colors border border-transparent hover:border-[#121316]" style="color: #121316;">
                            <span class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] border border-black" style="background-color: #ff3823; color: #ffffff;"><i class="fa-solid fa-chalkboard-user"></i></span>
                                <span>Model Ruang Kelas (5×8)</span>
                            </span>
                            <i id="check-classroom" class="fa-solid fa-check text-xs font-black" style="color: #ff3823;"></i>
                        </button>
                        
                        <button onclick="changeStudentLayout('gallery')" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-mono-code font-bold hover:bg-[#faf3e0] flex items-center justify-between transition-colors border border-transparent hover:border-[#121316]" style="color: #121316;">
                            <span class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] border border-black" style="background-color: #2563eb; color: #ffffff;"><i class="fa-solid fa-table-cells"></i></span>
                                <span>Tampilan Galeri</span>
                            </span>
                            <i id="check-gallery" class="fa-solid fa-check text-xs hidden font-black" style="color: #2563eb;"></i>
                        </button>

                        <button onclick="changeStudentLayout('speaker')" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-mono-code font-bold hover:bg-[#faf3e0] flex items-center justify-between transition-colors border border-transparent hover:border-[#121316]" style="color: #121316;">
                            <span class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] border border-black" style="background-color: #fbc02d; color: #121316;"><i class="fa-solid fa-user-tie"></i></span>
                                <span>Tampilan Pembicara</span>
                            </span>
                            <i id="check-speaker" class="fa-solid fa-check text-xs hidden font-black" style="color: #fbc02d;"></i>
                        </button>

                        <button onclick="changeStudentLayout('sidebyside')" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-mono-code font-bold hover:bg-[#faf3e0] flex items-center justify-between transition-colors border border-transparent hover:border-[#121316]" style="color: #121316;">
                            <span class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] border border-black" style="background-color: #10b981; color: #ffffff;"><i class="fa-solid fa-columns"></i></span>
                                <span>Berdampingan (Materi)</span>
                            </span>
                            <i id="check-sidebyside" class="fa-solid fa-check text-xs hidden font-black" style="color: #10b981;"></i>
                        </button>

                        <div class="h-px my-1.5" style="background-color: #e2ded5;"></div>

                        <button onclick="toggleStudentFullscreen()" type="button" class="w-full text-left px-3 py-2 rounded-xl text-xs font-mono-code font-bold hover:bg-[#faf3e0] flex items-center gap-2.5 transition-colors border border-transparent hover:border-[#121316]" style="color: #121316;">
                            <span class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] border border-black" style="background-color: #fbc02d; color: #121316;"><i id="menu-fs-icon" class="fa-solid fa-expand"></i></span>
                            <span id="menu-fs-text">Layar Penuh (F11)</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- 1. CLASSROOM VIEW STAGE (DEFAULT: Model Ruang Kelas 5x8 Kursi Siswa + Meja Guru Depan) --}}
            <div id="stage-classroom-wrapper" class="flex flex-1 flex-col h-full overflow-hidden p-2 sm:p-3 pb-28 gap-2 relative min-h-0">
                
                {{-- AREA DYNAMIC MIMBAR / PODIUM DEPAN KELAS (DEFAULT: GURU DI TENGAH; JIKA ADA SISWA BICARA: DUAL PODIUM) --}}
                <div class="w-full flex flex-col items-center justify-center flex-shrink-0 mb-1">
                    <div class="flex items-center gap-2 mb-1.5 flex-wrap justify-center">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-mono-code font-black uppercase tracking-wider" style="background-color: #ff3823; color: #ffffff; border: 1.5px solid #121316; box-shadow: 2px 2px 0px #121316;">
                            <span class="w-2 h-2 rounded-full animate-ping" style="background-color: #fde047;"></span> ✱ PODIUM UTAMA KELAS
                        </span>
                        <span id="classroom-occupancy-pill" class="px-2.5 py-1 rounded-full text-[10px] font-mono-code font-extrabold" style="background-color: #fbc02d; color: #121316; border: 1.5px solid #121316; box-shadow: 2px 2px 0px #121316;">
                            1 / 40 Kursi Terisi
                        </span>
                    </div>

                    {{-- FLOATING PROMPT KUIS LIVE DARI GURU (KAHOOT-STYLE) --}}
                    <div id="student-floating-quiz-banner" class="hidden w-full max-w-xl mx-auto mb-2 p-3 rounded-2xl flex items-center justify-between gap-3 transition-all animate-pulse" style="background-color: #ec4899; color: #ffffff; border: 2.5px solid #121316; box-shadow: 4px 4px 0px #121316;">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="text-xl flex-shrink-0">🎮</span>
                            <div class="min-w-0">
                                <div class="text-[9px] font-mono-code font-black uppercase text-pink-200 tracking-wider">KUIS LIVE BERLANGSUNG!</div>
                                <div id="student-floating-quiz-title" class="text-xs font-black truncate text-white">Soal aktif sedang dibuka guru...</div>
                            </div>
                        </div>
                        <button onclick="switchStudentTab('kuis')" type="button" class="btn-tactile-white px-3 py-1.5 rounded-xl text-xs font-mono-code font-black flex items-center gap-1 flex-shrink-0" style="background-color: #fde047; color: #121316; border: 2px solid #121316;">
                            <span>Ikuti Kuis</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </button>
                    </div>

                    {{-- FLOATING PROMPT POLLING KBM DARI GURU --}}
                    <div id="student-floating-poll-banner" class="hidden w-full max-w-xl mx-auto mb-2 p-3 rounded-2xl flex items-center justify-between gap-3 transition-all" style="background-color: #fde047; color: #121316; border: 2.5px solid #121316; box-shadow: 4px 4px 0px #121316;">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="text-xl flex-shrink-0 animate-bounce">📢</span>
                            <div class="min-w-0">
                                <div class="text-[9px] font-mono-code font-black uppercase text-amber-900 tracking-wider">POLLING KBM DILUNCURKAN GURU</div>
                                <div id="student-floating-poll-question" class="text-xs font-black truncate text-black">Pertanyaan polling...</div>
                            </div>
                        </div>
                        <button onclick="switchStudentTab('polling')" type="button" class="btn-tactile-white px-3 py-1.5 rounded-xl text-xs font-mono-code font-black flex items-center gap-1 flex-shrink-0">
                            <span>Jawab</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </button>
                    </div>

                    <div id="podium-container" class="w-full max-w-xl mx-auto transition-all duration-300">
                        <div id="podium-grid" class="w-full flex items-center justify-center transition-all duration-300">
                            {{-- Slot Kiri: Mimbar Guru Pengajar --}}
                            <div id="podium-teacher-wrapper" class="flex flex-col w-full transition-all duration-300">
                                <div class="flex items-center justify-between px-2 py-0.5 mb-1 text-[10px] font-mono-code font-black text-[#ff3823]">
                                    <span>👨‍🏫 MIMBAR GURU / PENGAJAR</span>
                                    <span class="text-slate-500 font-bold">Host Kelas</span>
                                </div>
                                <div id="podium-teacher-slot" class="w-full h-44 sm:h-52 md:h-56 flex items-center justify-center rounded-2xl overflow-hidden bg-slate-900 border-2 border-black" style="box-shadow: 3px 3px 0px #ff3823;">
                                    {{-- Video tile guru masuk ke sini secara otomatis --}}
                                </div>
                            </div>

                            {{-- Slot Kanan: Mimbar Siswa Bicara / Bertanya (Active Speaker Podium) - MUNCUL HANYA SAAT ADA SISWA BICARA/TANYA --}}
                            <div id="podium-speaker-wrapper" class="hidden flex-col w-full transition-all duration-300">
                                <div class="flex items-center justify-between px-2 py-0.5 mb-1 text-[10px] font-mono-code font-black text-[#2563eb]">
                                    <span class="flex items-center gap-1">
                                        <i class="fa-solid fa-microphone-lines text-[#2563eb]"></i> MIMBAR SISWA BICARA / BERTANYA
                                    </span>
                                    <span id="podium-speaker-badge" class="px-1.5 py-0.2 rounded text-[9px] font-mono-code font-black bg-blue-600 text-white animate-pulse">Sedang Berbicara 🎤</span>
                                </div>
                                <div id="podium-speaker-slot" class="w-full h-44 sm:h-52 md:h-56 flex items-center justify-center rounded-2xl overflow-hidden bg-slate-900 border-2 border-black relative" style="box-shadow: 3px 3px 0px #2563eb;">
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
                        <div id="classroom-grid" class="grid grid-cols-4 sm:grid-cols-6 lg:grid-cols-8 gap-2 sm:gap-2.5">
                            {{-- 40 slot kursi siswa (terisi / kosong) dirender dinamis di sini --}}
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. SPEAKER VIEW STAGE (Active when currentLayout === 'speaker') --}}
            {{-- Catatan: pb-28 memastikan video tile TIDAK PERNAH tertutup oleh dock mengambang di bawah --}}
            <div id="stage-speaker-wrapper" class="hidden flex-1 flex-col h-full overflow-hidden p-3 pb-28 gap-2.5 relative min-h-0">
                {{-- Top Filmstrip Bar: Thumbnail peserta lain --}}
                <div id="speaker-filmstrip" class="flex items-center gap-2.5 overflow-x-auto p-2 h-28 shrink-0 rounded-2xl backdrop-blur-sm scroll-smooth" style="background-color: rgba(255, 255, 255, 0.85); border: 2px solid #121316; box-shadow: 3px 3px 0px #121316;">
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
                <div id="video-tile-teacher" class="video-tile group relative rounded-3xl overflow-hidden flex items-center justify-center w-full h-full max-h-[82vh] transition-all" style="background-color: #0f172a; border: 2.5px solid #121316; box-shadow: 4px 4px 0px #121316;">
                    <div id="agora-teacher-video" class="w-full h-full relative"></div>
                    <div id="teacher-waiting-placeholder" class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center pointer-events-none" style="background-color: #121316; color: #cbd5e1;">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl mb-3 shadow-lg animate-pulse" style="background-color: #ff3823; border: 2px solid #ffffff; color: #ffffff;">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <h3 class="text-base font-black font-mono-code" style="color: #ffffff;">Menghubungkan ke Siaran Guru...</h3>
                        <p class="text-xs mt-1 max-w-sm font-medium" style="color: #94a3b8;">Guru sedang menyiapkan siaran. Video & audio guru akan otomatis tampil saat guru mulai mengajar.</p>
                        <span class="mt-3 inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-mono-code font-bold shadow-xs" style="background-color: #fbc02d; color: #121316; border: 1.5px solid #121316;">
                            <span class="w-2 h-2 rounded-full animate-ping" style="background-color: #ff3823;"></span> Live Class
                        </span>
                    </div>
                    
                    {{-- 🎯 PERBAIKAN: BADGE NAMA GURU DIPINDAH KE POJOK KIRI ATAS --}}
                    {{-- Dijamin 100% TIDAK AKAN PERNAH TERTUTUP oleh menu bar di bawah! --}}
                    <div class="absolute top-4 left-4 z-20 flex items-center gap-2 backdrop-blur-md px-3 py-1.5 rounded-xl text-xs font-black shadow-md" style="background-color: rgba(18, 19, 22, 0.95); color: #ffffff; border: 2px solid #121316; box-shadow: 3px 3px 0px #ff3823;" title="{{ $course->teacher?->user?->name ?? 'Guru Pengajar' }}">
                        <span class="w-2 h-2 rounded-full animate-pulse" style="background-color: #10b981;"></span>
                        <i id="teacher-audio-indicator" class="fa-solid fa-volume-high text-xs" style="color: #10b981;"></i>
                        <span class="truncate max-w-[150px] font-mono-code font-bold" style="color: #fde047;">{{ explode(' ', trim($course->teacher?->user?->name ?? 'Guru'))[0] }}</span>
                        <span class="px-2 py-0.5 rounded-md text-[9px] font-mono-code font-black uppercase tracking-wider" style="background-color: #ff3823; color: #ffffff;">GURU</span>
                    </div>

                    <div class="absolute top-4 right-4 z-20 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button onclick="pinStudentSpeaker('teacher')" type="button" class="p-2 rounded-xl text-xs transition active:translate-x-0.5 active:translate-y-0.5" style="background-color: #ffffff; color: #121316; border: 2px solid #121316; box-shadow: 2px 2px 0px #121316;" title="Fokuskan / Spotlight">
                            <i class="fa-solid fa-thumbtack text-xs"></i>
                        </button>
                    </div>
                </div>

                {{-- 2. Video Tile SAYA (Siswa Sendiri) --}}
                <div id="video-tile-local" class="video-tile group relative rounded-3xl overflow-hidden flex items-center justify-center aspect-video transition-all" style="background-color: #0f172a; border: 2.5px solid #121316; box-shadow: 4px 4px 0px #121316;">
                    <div id="agora-student-video" class="w-full h-full relative"></div>
                    <div id="student-cam-off-placeholder" class="absolute inset-0 flex flex-col items-center justify-center text-xs pointer-events-none" style="background-color: #121316; color: #cbd5e1;">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl font-black text-white mb-2 shadow-lg" style="background: linear-gradient(135deg, #06b6d4, #4f46e5); border: 2px solid #ffffff;">
                            {{ substr($displayName, 0, 1) }}
                        </div>
                        <span class="text-[11px] font-mono-code font-bold" style="color: #fde047;">Kamera Nonaktif</span>
                    </div>

                    {{-- 🎯 BADGE NAMA SAYA + MOOD + HAND INDICATOR --}}
                    <div class="absolute top-4 left-4 z-20 flex items-center gap-1.5 backdrop-blur-md px-2.5 py-1.5 rounded-xl text-xs font-black shadow-md flex-wrap" style="background-color: rgba(18, 19, 22, 0.95); color: #ffffff; border: 2px solid #121316; box-shadow: 3px 3px 0px #2563eb;" title="{{ $displayName }}">
                        <i id="tile-mic-local" class="fa-solid fa-microphone-slash text-xs" style="color: #ff3823;"></i>
                        <span class="truncate max-w-[120px] font-mono-code font-bold" style="color: #fde047;">Saya ({{ explode(' ', trim($displayName))[0] }})</span>
                        <span class="px-1.5 py-0.5 rounded text-[8px] font-mono-code font-black uppercase tracking-wider" style="background-color: #2563eb; color: #ffffff;">SISWA</span>
                        <span id="tile-mood-local" class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[9px] font-mono-code font-black" style="background-color: #ffedd5; color: #ea580c; border: 1px solid #121316;">
                            <span id="tile-mood-local-icon">🔥</span> <span id="tile-mood-local-text">Semangat</span>
                        </span>
                        <span id="tile-hand-local" class="hidden px-1.5 py-0.2 rounded text-[9px] font-mono-code font-black animate-bounce" style="background-color: #ef4444; color: #ffffff; border: 1px solid #121316;">
                            ✋
                        </span>
                    </div>

                    <div class="absolute top-4 right-4 z-20 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button onclick="pinStudentSpeaker('local')" type="button" class="p-2 rounded-xl text-xs transition active:translate-x-0.5 active:translate-y-0.5" style="background-color: #ffffff; color: #121316; border: 2px solid #121316; box-shadow: 2px 2px 0px #121316;" title="Fokuskan / Spotlight">
                            <i class="fa-solid fa-thumbtack text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Floating Student Action Dock (Neo-Tactile Island Khas PembdaHUB) --}}
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2 sm:gap-2.5 px-3.5 sm:px-5 py-2.5 rounded-2xl sm:rounded-full" style="background-color: #ffffff; border: 2.5px solid #121316; box-shadow: 5px 5px 0px #121316;">
                
                {{-- Toggle Mic Siswa --}}
                <button onclick="toggleStudentMic()" id="btn-toggle-mic" type="button" class="group relative p-3 rounded-xl transition active:translate-x-0.5 active:translate-y-0.5" style="background-color: #ff3823; color: #ffffff; border: 2px solid #121316; box-shadow: 2px 2px 0px #121316;" title="Aktifkan / Matikan Mikrofon">
                    <i id="mic-icon" class="fa-solid fa-microphone-slash text-base text-white"></i>
                    <span class="absolute -top-9 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2.5 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md" style="background-color: #121316; color: #fde047; border: 1px solid #121316;">Mic</span>
                </button>

                {{-- Toggle Kamera Siswa --}}
                <button onclick="toggleStudentCam()" id="btn-toggle-cam" type="button" class="group relative p-3 rounded-xl transition active:translate-x-0.5 active:translate-y-0.5" style="background-color: #ff3823; color: #ffffff; border: 2px solid #121316; box-shadow: 2px 2px 0px #121316;" title="Aktifkan / Matikan Kamera">
                    <i id="cam-icon" class="fa-solid fa-video-slash text-base text-white"></i>
                    <span class="absolute -top-9 left-1/2 -translate-x-1/2 text-[10px] font-mono-code font-black px-2.5 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap shadow-md" style="background-color: #121316; color: #fde047; border: 1px solid #121316;">Kamera</span>
                </button>

                <div class="w-px h-6" style="background-color: #121316;"></div>

                {{-- Angkat Tangan (Amber Tactile) --}}
                <button onclick="studentToggleRaiseHand()" id="btn-raise-hand" type="button" class="flex items-center gap-1.5 px-3.5 sm:px-4 py-2.5 rounded-xl text-xs font-mono-code font-black active:scale-95 transition-all" style="background-color: #fbc02d; color: #121316; border: 2px solid #121316; box-shadow: 3px 3px 0px #121316;" title="Beri tanda ke guru bahwa Anda ingin bertanya">
                    <i class="fa-solid fa-hand text-sm"></i>
                    <span id="raise-hand-label" class="hidden sm:inline">Angkat Tangan</span>
                </button>

                {{-- Suasana Hati / Mood Picker Siswa --}}
                <button onclick="openMoodModal()" id="btn-mood-picker" type="button" class="flex items-center gap-1.5 px-3.5 sm:px-4 py-2.5 rounded-xl text-xs font-mono-code font-black active:scale-95 transition-all" style="background-color: #ffffff; color: #121316; border: 2px solid #121316; box-shadow: 3px 3px 0px #121316;" title="Pilih suasana hati Anda saat mengikuti kelas">
                    <span id="current-mood-icon" class="text-sm">🔥</span>
                    <span id="current-mood-label" class="hidden sm:inline">Semangat</span>
                </button>

                {{-- Buka Chat Realtime (Blue Tactile) --}}
                <button onclick="openStudentTab('chat')" type="button" class="relative flex items-center gap-1.5 px-3.5 sm:px-4 py-2.5 rounded-xl text-xs font-mono-code font-black transition-all active:translate-x-0.5 active:translate-y-0.5" style="background-color: #2563eb; color: #ffffff; border: 2px solid #121316; box-shadow: 2px 2px 0px #121316;" title="Buka ruang obrolan kelas">
                    <i class="fa-solid fa-comments text-sm" style="color: #fde047;"></i>
                    <span class="hidden sm:inline">Chat</span>
                    <span id="quick-chat-badge" class="hidden absolute -top-2 -right-1 px-1.5 py-0.2 rounded-full text-[9px] font-mono-code font-black" style="background-color: #ff3823; color: #ffffff; border: 1px solid #121316;">0</span>
                </button>

                {{-- Buka Materi Cepat (Indigo Tactile) --}}
                <button onclick="openStudentTab('materi')" type="button" class="flex items-center gap-1.5 px-3.5 sm:px-4 py-2.5 rounded-xl text-xs font-mono-code font-black transition-all active:translate-x-0.5 active:translate-y-0.5" style="background-color: #4f46e5; color: #ffffff; border: 2px solid #121316; box-shadow: 2px 2px 0px #121316;" title="Buka materi modul pelajaran">
                    <i class="fa-solid fa-book-open text-sm" style="color: #fde047;"></i>
                    <span class="hidden sm:inline">Modul</span>
                </button>

                {{-- Buka Catatan (Emerald Tactile) --}}
                <button onclick="openStudentTab('notes')" type="button" class="flex items-center gap-1.5 px-3.5 sm:px-4 py-2.5 rounded-xl text-xs font-mono-code font-black transition-all active:translate-x-0.5 active:translate-y-0.5" style="background-color: #10b981; color: #ffffff; border: 2px solid #121316; box-shadow: 2px 2px 0px #121316;" title="Buka papan catatan pribadi">
                    <i class="fa-solid fa-pen-nib text-sm" style="color: #fde047;"></i>
                    <span class="hidden sm:inline">Catatan</span>
                </button>

                {{-- Buka Kuis Live (Pink Tactile) --}}
                <button onclick="openStudentTab('kuis')" type="button" class="relative flex items-center gap-1.5 px-3.5 sm:px-4 py-2.5 rounded-xl text-xs font-mono-code font-black transition-all active:translate-x-0.5 active:translate-y-0.5" style="background-color: #ec4899; color: #ffffff; border: 2px solid #121316; box-shadow: 2px 2px 0px #121316;" title="Buka Kuis Interaktif Live">
                    <i class="fa-solid fa-gamepad text-sm" style="color: #fde047;"></i>
                    <span class="hidden sm:inline">Kuis</span>
                    <span id="student-quick-quiz-badge" class="hidden absolute -top-2 -right-1 px-1.5 py-0.2 rounded-full text-[9px] font-mono-code font-black" style="background-color: #fde047; color: #121316; border: 1px solid #121316;">!</span>
                </button>
            </div>
        </main>

        {{-- Resize Handle Divider --}}
        <div id="resize-handler" class="w-[10px] bg-[#ede9df] hover:bg-[#ff3823] cursor-col-resize select-none z-20 flex items-center justify-center relative transition-colors duration-150 group border-x border-[#121316]" title="Geser untuk mengatur lebar panel materi">
            <div class="w-4 h-12 bg-white border-2 border-[#121316] rounded-md flex items-center justify-center shadow-[2px_2px_0px_#121316] pointer-events-none">
                <i class="fa-solid fa-grip-lines-vertical text-[10px] text-[#121316]"></i>
            </div>
        </div>

        {{-- Resize Overlay --}}
        <div id="resize-overlay" class="fixed inset-0 z-40 hidden cursor-col-resize"></div>

        {{-- 3. STUDENT LEARNING COMPANION HUB (TACTILE BINDER STYLE KHAS PEMBDAHUB) --}}
        <aside id="student-hub" class="w-[480px] max-w-[90vw] flex flex-col h-full z-15 shadow-2xl relative" style="background-color: #faf8f5; border-left: 2.5px solid #121316;">
            
            {{-- Tabs Header Bar --}}
            <div class="flex items-center p-2 gap-1.5 flex-shrink-0" style="background-color: #ede9df; border-bottom: 2px solid #121316;">
                <button id="tab-btn-materi" onclick="switchStudentTab('materi')" type="button" class="flex-1 py-2 px-2 text-xs font-mono-code font-black transition-all flex items-center justify-center gap-1 rounded-xl" style="background-color: #121316; color: #fde047; border: 2px solid #121316; box-shadow: 2px 2px 0px #ff3823;">
                    <i class="fa-solid fa-book text-xs" style="color: #ff3823;"></i>
                    <span class="hidden sm:inline">Modul</span>
                </button>
                <button id="tab-btn-notes" onclick="switchStudentTab('notes')" type="button" class="flex-1 py-2 px-2 text-xs font-mono-code font-bold transition-all flex items-center justify-center gap-1 rounded-xl hover:bg-[#faf3e0]" style="background-color: #ffffff; color: #121316; border: 2px solid #121316;">
                    <i class="fa-solid fa-pen-to-square text-xs" style="color: #10b981;"></i>
                    <span class="hidden sm:inline">Catatan</span>
                </button>
                <button id="tab-btn-friends" onclick="switchStudentTab('friends')" type="button" class="flex-1 py-2 px-2 text-xs font-mono-code font-bold transition-all flex items-center justify-center gap-1 rounded-xl hover:bg-[#faf3e0]" style="background-color: #ffffff; color: #121316; border: 2px solid #121316;">
                    <i class="fa-solid fa-users text-xs" style="color: #2563eb;"></i>
                    <span>Teman</span>
                </button>
                <button id="tab-btn-chat" onclick="switchStudentTab('chat')" type="button" class="flex-1 py-2 px-1 text-xs font-mono-code font-bold transition-all flex items-center justify-center gap-1 rounded-xl hover:bg-[#faf3e0] relative" style="background-color: #ffffff; color: #121316; border: 2px solid #121316;">
                    <i class="fa-solid fa-comments text-xs" style="color: #2563eb;"></i>
                    <span>Chat</span>
                    <span id="chat-tab-badge" class="hidden px-1.5 py-0.2 rounded-full text-[9px] font-black" style="background-color: #ff3823; color: #ffffff;">0</span>
                </button>
                <button id="tab-btn-polling" onclick="switchStudentTab('polling')" type="button" class="flex-1 py-2 px-1 text-xs font-mono-code font-bold transition-all flex items-center justify-center gap-1 rounded-xl hover:bg-[#faf3e0] relative" style="background-color: #ffffff; color: #121316; border: 2px solid #121316;" title="Polling Kilat">
                    <i class="fa-solid fa-square-poll-vertical text-xs" style="color: #d97706;"></i>
                    <span>Poll</span>
                    <span id="student-poll-tab-badge" class="hidden w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                </button>
                <button id="tab-btn-qa" onclick="switchStudentTab('qa')" type="button" class="flex-1 py-2 px-1 text-xs font-mono-code font-bold transition-all flex items-center justify-center gap-1 rounded-xl hover:bg-[#faf3e0] relative" style="background-color: #ffffff; color: #121316; border: 2px solid #121316;" title="Tanya Jawab Q&A">
                    <i class="fa-solid fa-clipboard-question text-xs" style="color: #ea580c;"></i>
                    <span>Q&A</span>
                    <span id="student-qa-tab-badge" class="hidden px-1.5 py-0.2 rounded-full text-[9px] font-black" style="background-color: #ea580c; color: #ffffff;">0</span>
                </button>
                <button id="tab-btn-kuis" onclick="switchStudentTab('kuis')" type="button" class="flex-1 py-2 px-1 text-xs font-mono-code font-bold transition-all flex items-center justify-center gap-1 rounded-xl hover:bg-[#faf3e0] relative" style="background-color: #ffffff; color: #121316; border: 2px solid #121316;" title="Kuis Interaktif Live">
                    <i class="fa-solid fa-gamepad text-xs" style="color: #ec4899;"></i>
                    <span>Kuis</span>
                    <span id="student-quiz-tab-badge" class="hidden w-2 h-2 rounded-full bg-pink-500 animate-ping"></span>
                </button>
                <button onclick="toggleStudentHub()" type="button" class="p-2 rounded-xl transition-colors shadow-xs" style="background-color: #ffffff; color: #121316; border: 2px solid #121316;" title="Tutup panel">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- TAB 1: MATERI & MODUL --}}
            <div id="tab-content-materi" class="flex-1 flex flex-col overflow-hidden">
                
                {{-- Viewer Preview Box --}}
                <div id="material-preview-box" class="h-1/2 min-h-[220px] bg-white flex flex-col hidden" style="border-bottom: 2px solid #121316;">
                    <div class="px-4 py-2 flex items-center justify-between" style="background-color: #faf3e0; border-bottom: 2px solid #121316;">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-6 h-6 rounded-md flex items-center justify-center text-xs border border-black" style="background-color: #ff3823; color: #ffffff;"><i id="preview-icon" class="fa-regular fa-file-pdf"></i></span>
                            <h3 id="preview-title" class="text-xs font-black truncate font-mono-code" style="color: #121316;">Materi Pelajaran</h3>
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
                        <span class="text-[10px] font-mono-code font-bold" style="color: #777;">Klik "Buka" untuk menyimak</span>
                    </div>

                    @php
                        $modules = $course->modules()->ordered()->active()->get();
                    @endphp

                    @if($modules->isEmpty())
                        <div class="text-center text-xs py-12 poster-card bg-white p-6" style="color: #777;">
                            <div class="w-14 h-14 mx-auto mb-3 rounded-2xl flex items-center justify-center text-2xl shadow-sm" style="background-color: #ede9df; border: 2px solid #121316; color: #2563eb;">
                                <i class="fa-regular fa-folder-open"></i>
                            </div>
                            <h4 class="font-black text-sm" style="color: #121316;">Belum Ada Materi</h4>
                            <p class="font-medium mt-1">Belum ada modul atau materi di kelas ini.</p>
                        </div>
                    @else
                        @foreach($modules as $module)
                            @php
                                $moduleHeaderBg = match($module->color) {
                                    'indigo' => '#4f46e5',
                                    'emerald' => '#059669',
                                    'rose' => '#e11d48',
                                    'amber' => '#d97706',
                                    'blue' => '#2563eb',
                                    'purple' => '#7c3aed',
                                    'cyan' => '#0891b2',
                                    'orange' => '#ea580c',
                                    default => '#121316'
                                };
                            @endphp
                            <div class="poster-card bg-white overflow-hidden">
                                <div class="p-3 flex items-center justify-between" style="background-color: {{ $moduleHeaderBg }}; color: #ffffff; border-bottom: 2px solid #121316;">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-6 h-6 rounded-lg flex items-center justify-center text-[10px] font-mono-code font-black text-white bg-black/30 border border-white/40">
                                            {{ $module->sequence ?? ($loop->iteration) }}
                                        </span>
                                        <h4 class="text-xs font-black truncate font-mono-code text-white">{{ $module->title }}</h4>
                                    </div>
                                    <span class="text-[10px] font-mono-code font-bold px-2 py-0.5 rounded-md border border-black bg-white text-black shadow-xs">{{ $module->materials()->active()->count() }} Materi</span>
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
                                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-white hover:bg-[#faf3e0] text-xs transition-all" style="border: 1.5px solid #121316; box-shadow: 2px 2px 0px #121316;">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                @if($material->material_type == 'pdf')
                                                    <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs border border-black flex-shrink-0" style="background-color: #ff3823; color: #ffffff;"><i class="fa-regular fa-file-pdf"></i></span>
                                                @elseif($material->material_type == 'video')
                                                    <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs border border-black flex-shrink-0" style="background-color: #fbc02d; color: #121316;"><i class="fa-regular fa-play-circle"></i></span>
                                                @elseif($material->material_type == 'link')
                                                    <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs border border-black flex-shrink-0" style="background-color: #0891b2; color: #ffffff;"><i class="fa-solid fa-link"></i></span>
                                                @elseif($material->material_type == 'canva' || $material->material_type == 'googledocs')
                                                    <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs border border-black flex-shrink-0" style="background-color: #10b981; color: #ffffff;"><i class="fa-solid fa-file-lines"></i></span>
                                                @else
                                                    <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs border border-black flex-shrink-0" style="background-color: #4f46e5; color: #ffffff;"><i class="fa-regular fa-file-lines"></i></span>
                                                @endif
                                                <span class="text-[11px] font-bold truncate" style="color: #121316;">{{ $material->title }}</span>
                                            </div>
                                            <button onclick="displayStudentMaterial('{{ $safeTitle }}', '{{ $material->material_type }}', '{{ $url }}', '{{ $safeContent }}')" type="button" class="btn-tactile-white text-[10px] font-mono-code font-black px-2.5 py-1 rounded-lg flex items-center gap-1 flex-shrink-0">
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

            {{-- TAB 2: CATATAN SISWA --}}
            <div id="tab-content-notes" class="flex-1 flex flex-col overflow-hidden hidden" style="background-color: #faf8f5;">
                <div class="p-3 bg-white flex items-center justify-between flex-shrink-0 shadow-xs" style="border-bottom: 2px solid #121316;">
                    <div>
                        <div class="text-[10px] font-mono-code font-black uppercase" style="color: #10b981;">✱ CATATAN PRIBADI</div>
                        <h3 class="text-xs font-black" style="color: #121316;">Buku Coretan Pelajaran</h3>
                    </div>
                    <span id="notes-saved-hint" class="text-[10px] font-mono-code font-bold" style="color: #777;">Otomatis tersimpan</span>
                </div>
                <div class="flex-1 p-3 flex flex-col graph-paper-box">
                    <textarea id="student-notes-area" class="w-full flex-1 bg-white p-4 text-xs resize-none outline-none focus:border-[#2563eb] transition-all font-mono-code leading-relaxed" style="color: #121316; border: 2px solid #121316; border-radius: 1rem; box-shadow: 3px 3px 0px #121316;" placeholder="Tuliskan poin-poin penjelasan penting dari guru di sini... (Catatan otomatis tersimpan di peramban browser Anda)"></textarea>
                </div>
            </div>

            {{-- TAB 3: TEMAN SEKELAS --}}
            <div id="tab-content-friends" class="flex-1 flex flex-col overflow-hidden hidden" style="background-color: #faf8f5;">
                <div class="p-3 bg-white flex items-center justify-between flex-shrink-0 shadow-xs" style="border-bottom: 2px solid #121316;">
                    <div>
                        <div class="text-[10px] font-mono-code font-black uppercase" style="color: #2563eb;">✱ CIVITAS KELAS</div>
                        <h3 class="text-xs font-black" style="color: #121316;">Teman Sekelas Hadir</h3>
                    </div>
                    <span id="friends-badge-count" class="btn-tactile-yellow text-[10px] font-mono-code font-black px-2.5 py-0.5 rounded-full">0 Online</span>
                </div>
                <div class="flex-1 overflow-y-auto p-4 space-y-2.5 graph-paper-box" id="friends-list">
                    <div class="text-center text-xs py-12 poster-card bg-white p-6" style="color: #777;">
                        <i class="fa-solid fa-spinner animate-spin text-2xl mb-2 block" style="color: #2563eb;"></i>
                        <p class="font-mono-code font-bold">Memuat teman sekelas...</p>
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
                            <option value="all">📢 Semua Orang (Publik)</option>
                            @if($course->teacher && $course->teacher->user)
                                <option value="{{ $course->teacher->user->id }}">👨‍🏫 Guru: {{ $course->teacher->user->name }}</option>
                            @endif
                        </select>
                    </div>
                </div>

                {{-- Chat Messages List --}}
                <div id="chat-messages-container" class="flex-1 overflow-y-auto p-3 space-y-2.5 graph-paper-box">
                    <div id="chat-empty-state" class="text-center text-xs py-12 poster-card bg-white p-6" style="color: #777;">
                        <i class="fa-regular fa-comment-dots text-3xl mb-2 block" style="color: #2563eb;"></i>
                        <p class="font-mono-code font-bold text-[#121316]">Belum ada pesan obrolan.</p>
                        <p class="text-[10px] font-medium text-slate-500 mt-1">Gunakan chat untuk bertanya kepada guru atau berdiskusi dengan teman.</p>
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
                    <input type="text" id="chat-input-text" placeholder="Tulis pesan ke semua, guru, atau teman..." class="flex-1 px-3 py-2 text-xs font-mono-code rounded-xl border-2 border-black focus:outline-none" style="box-shadow: 2px 2px 0px #121316;" onkeydown="if(event.key==='Enter') sendChatMessage()">
                    <button onclick="sendChatMessage()" type="button" class="btn-tactile-red px-3.5 py-2 rounded-xl text-xs font-mono-code font-black flex items-center gap-1 active:scale-95">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                        <span class="hidden sm:inline">Kirim</span>
                    </button>
                </div>
            </div>

            {{-- TAB 5: POLLING KILAT SISWA --}}
            <div id="tab-content-polling" class="flex-1 flex flex-col overflow-hidden hidden" style="background-color: #faf8f5;">
                {{-- Header Polling --}}
                <div class="p-3 flex items-center justify-between flex-shrink-0 shadow-xs" style="background-color: #ffffff; border-bottom: 2px solid #121316;">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs border border-black shadow-xs" style="background-color: #d97706; color: #ffffff;">
                            <i class="fa-solid fa-square-poll-vertical"></i>
                        </div>
                        <div>
                            <div class="text-xs font-black" style="color: #121316;">Polling Kilat KBM</div>
                            <div class="text-[9px] font-mono-code font-bold text-slate-500">Pertanyaan interaktif dari guru</div>
                        </div>
                    </div>
                    <div id="student-poll-header-badge">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-slate-100 text-slate-600 border border-slate-300">Menunggu</span>
                    </div>
                </div>

                {{-- Konten Utama Polling --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-4">
                    {{-- Belum Ada Polling --}}
                    <div id="student-poll-empty" class="text-center py-12 px-4">
                        <div class="w-12 h-12 mx-auto rounded-2xl bg-amber-100 text-amber-700 border border-amber-300 flex items-center justify-center text-xl mb-2">
                            <i class="fa-solid fa-hourglass-half"></i>
                        </div>
                        <p class="text-xs font-mono-code font-bold text-slate-700">Belum Ada Polling Aktif</p>
                        <p class="text-[10px] text-slate-500 mt-1">Saat Guru meluncurkan pertanyaan polling kilat, pilihan jawaban akan langsung muncul di sini.</p>
                    </div>

                    {{-- Ada Polling Aktif / Selesai --}}
                    <div id="student-poll-card" class="hidden p-4 rounded-2xl bg-white space-y-3 relative overflow-hidden" style="border: 2px solid #121316; box-shadow: 4px 4px 0px #d97706;">
                        <div class="flex items-center justify-between">
                            <span id="student-poll-status-indicator" class="px-2 py-0.5 rounded-md text-[10px] font-mono-code font-black flex items-center gap-1.5" style="background-color: #fef3c7; color: #92400e; border: 1px solid #f59e0b;">
                                <span class="w-2 h-2 rounded-full animate-ping bg-amber-500"></span> POLLING BERLANGSUNG
                            </span>
                            <span id="student-poll-total-badge" class="text-xs font-mono-code font-black text-slate-700">0 Suara</span>
                        </div>

                        <h4 id="student-poll-question-text" class="text-sm font-black leading-snug" style="color: #121316;">
                            Pertanyaan Polling
                        </h4>

                        {{-- Options Container (Buttons or Progress Bars) --}}
                        <div id="student-poll-options-container" class="space-y-2 pt-1">
                            {{-- Diisi secara dinamis oleh JavaScript --}}
                        </div>

                        <div id="student-poll-notice" class="text-[10px] font-mono-code text-center text-slate-500 pt-1 border-t border-slate-200">
                            Pilih salah satu jawaban di atas untuk memberikan suara Anda.
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 6: TANYA JAWAB TERSTRUKTUR (Q&A) SISWA --}}
            <div id="tab-content-qa" class="flex-1 flex flex-col overflow-hidden hidden" style="background-color: #faf8f5;">
                {{-- Header Q&A --}}
                <div class="p-3 flex items-center justify-between flex-shrink-0 shadow-xs" style="background-color: #ffffff; border-bottom: 2px solid #121316;">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs border border-black shadow-xs" style="background-color: #ea580c; color: #ffffff;">
                            <i class="fa-solid fa-clipboard-question"></i>
                        </div>
                        <div>
                            <div class="text-xs font-black" style="color: #121316;">Tanya Jawab (Q&A)</div>
                            <div class="text-[9px] font-mono-code font-bold text-slate-500">Tanyakan materi & upvote pertanyaan teman</div>
                        </div>
                    </div>
                    <div>
                        <span id="student-qa-count-badge" class="px-2 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-orange-100 text-orange-800 border border-orange-300">0 Pertanyaan</span>
                    </div>
                </div>

                {{-- Form Ajukan Pertanyaan --}}
                <div class="p-3 bg-white border-b-2 border-black flex flex-col gap-2 flex-shrink-0">
                    <label class="text-[10px] font-mono-code font-black text-slate-700 uppercase">Ajukan Pertanyaan ke Guru:</label>
                    <div class="flex items-center gap-2">
                        <input type="text" id="student-qa-input" placeholder="Tuliskan pertanyaan materi..." class="flex-1 px-3 py-2 text-xs font-mono-code rounded-xl border-2 border-black focus:outline-none" style="box-shadow: 2px 2px 0px #121316;" onkeydown="if(event.key==='Enter') submitStudentQuestion()">
                        <button onclick="submitStudentQuestion()" type="button" class="btn-tactile-red px-3.5 py-2 rounded-xl text-xs font-mono-code font-black flex items-center gap-1 active:scale-95">
                            <i class="fa-solid fa-paper-plane text-xs"></i>
                            <span>Tanya</span>
                        </button>
                    </div>
                </div>

                {{-- Daftar Pertanyaan Q&A --}}
                <div id="student-qa-list-container" class="flex-1 overflow-y-auto p-4 space-y-3">
                    <div id="student-qa-empty" class="text-center py-10 px-4">
                        <div class="w-12 h-12 mx-auto rounded-2xl bg-orange-100 text-orange-600 border border-orange-300 flex items-center justify-center text-xl mb-2">
                            <i class="fa-solid fa-comment-dots"></i>
                        </div>
                        <p class="text-xs font-mono-code font-bold text-slate-700">Belum ada pertanyaan</p>
                        <p class="text-[10px] text-slate-500 mt-1">Gunakan form di atas untuk menanyakan materi yang belum Anda pahami.</p>
                    </div>
                </div>
            </div>

            {{-- TAB 7: KUIS INTERAKTIF LIVE (KAHOOT-STYLE PLAYER SUPER CLASSROOM) --}}
            <div id="tab-content-kuis" class="flex-1 flex flex-col overflow-hidden hidden" style="background-color: #faf8f5;">
                {{-- Header Kuis Siswa --}}
                <div class="p-3 flex items-center justify-between flex-shrink-0 shadow-xs" style="background-color: #ffffff; border-bottom: 2px solid #121316;">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs border border-black shadow-xs" style="background-color: #ec4899; color: #ffffff;">
                            <i class="fa-solid fa-gamepad"></i>
                        </div>
                        <div>
                            <div class="text-xs font-black" style="color: #121316;">Kuis Interaktif Live</div>
                            <div class="text-[9px] font-mono-code font-bold text-slate-500">Jawab cepat &amp; raih skor tertinggi!</div>
                        </div>
                    </div>
                    <div>
                        <span id="student-quiz-status-pill" class="px-2 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-slate-100 text-slate-700 border border-slate-300">Menunggu</span>
                    </div>
                </div>

                {{-- Content Area --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-4">
                    
                    {{-- VIEW 1: STANDBY / WAITING STATE --}}
                    <div id="student-quiz-standby" class="p-6 rounded-2xl bg-white border-2 border-black text-center" style="box-shadow: 4px 4px 0px #121316;">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-pink-100 text-pink-600 border border-pink-300 flex items-center justify-center text-2xl mb-3">
                            🎮
                        </div>
                        <h4 class="text-sm font-black text-slate-900">Belum Ada Kuis Aktif</h4>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Saat Guru meluncurkan kuis interaktif, pertanyaan dan tombol jawaban Kahoot akan otomatis muncul di sini!</p>
                        <div class="mt-4 p-2.5 rounded-xl bg-amber-50 border border-amber-300 text-xs font-mono-code text-amber-900 font-bold">
                            🏆 Top 3 Siswa tercepat meraih bonus reputasi: 🥇 +15 Poin, 🥈 +10 Poin, 🥉 +5 Poin!
                        </div>
                    </div>

                    {{-- VIEW 2: QUESTION ACTIVE (KAHOOT-STYLE 4 BUTTONS) --}}
                    <div id="student-quiz-question-box" class="hidden space-y-3">
                        <div class="p-4 rounded-2xl bg-white border-2 border-black" style="box-shadow: 4px 4px 0px #121316;">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span id="student-q-badge" class="px-2.5 py-0.5 rounded-lg text-xs font-mono-code font-black bg-pink-100 text-pink-700 border border-pink-300">
                                    Soal 1 / 1
                                </span>
                                <div class="flex items-center gap-1.5 font-mono-code text-xs font-black text-rose-600">
                                    <i class="fa-solid fa-clock animate-spin"></i>
                                    <span id="student-q-timer" class="px-2 py-0.5 rounded bg-rose-500 text-white font-mono-code text-xs">20s</span>
                                </div>
                            </div>

                            <div class="p-3 rounded-xl bg-slate-50 border-2 border-slate-200 mb-3">
                                <h3 id="student-q-text" class="text-sm font-black text-slate-900 leading-snug">
                                    Pertanyaan kuis sedang dimuat...
                                </h3>
                            </div>

                            {{-- Status Jika Sudah Menjawab --}}
                            <div id="student-answered-indicator" class="hidden mb-3 p-2.5 rounded-xl bg-emerald-100 border border-emerald-400 text-center font-mono-code text-xs text-emerald-900 font-bold animate-pulse">
                                ✓ Jawaban Anda Telah Terkirim! Menunggu guru membuka papan skor...
                            </div>

                            {{-- 4 Big Kahoot-Style Tactile Buttons --}}
                            <div id="student-kahoot-buttons" class="grid grid-cols-2 gap-2.5">
                                {{-- Diisi secara dinamis dengan 4 tombol Kahoot --}}
                            </div>
                        </div>
                    </div>

                    {{-- VIEW 3: SCOREBOARD / RESULT FEEDBACK --}}
                    <div id="student-quiz-result-box" class="hidden space-y-3">
                        <div class="p-4 rounded-2xl bg-white border-2 border-black" style="box-shadow: 4px 4px 0px #121316;">
                            <div id="student-result-banner" class="p-3 rounded-xl text-center mb-3 border-2 border-black">
                                {{-- Dinamis: Benar / Salah --}}
                            </div>

                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 mb-3 font-mono-code text-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500 font-bold">Peringkat Anda:</span>
                                    <span id="student-my-rank" class="font-black text-slate-900">#1 di Kelas</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500 font-bold">Total Skor Anda:</span>
                                    <span id="student-my-score" class="font-black text-pink-600">0 pts</span>
                                </div>
                                <div id="student-correct-answer-info" class="pt-2 border-t border-slate-200 text-[11px] text-slate-700">
                                    {{-- Kunci jawaban benar & penjelasan --}}
                                </div>
                            </div>

                            {{-- Top 5 Leaderboard --}}
                            <div>
                                <div class="text-[10px] font-mono-code font-bold text-slate-500 uppercase mb-1.5">Top 5 Skor Kelas:</div>
                                <div id="student-top-leaderboard" class="space-y-1">
                                    {{-- List top 5 --}}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- VIEW 4: FINAL PODIUM --}}
                    <div id="student-quiz-podium-box" class="hidden space-y-3">
                        <div class="p-5 rounded-2xl bg-white border-2 border-black text-center" style="box-shadow: 4px 4px 0px #121316;">
                            <div class="text-3xl mb-1 animate-bounce">🏆</div>
                            <h3 class="text-sm font-black text-slate-900">KUIS LIVE SELESAI</h3>
                            <p id="student-podium-title" class="text-xs font-mono-code font-bold text-slate-500 mb-3">Hasil Akhir Kuis KBM</p>

                            <div id="student-podium-cards" class="grid grid-cols-3 gap-1.5 items-end mb-3 pt-2">
                                {{-- Podium --}}
                            </div>

                            <div id="student-podium-my-result" class="p-2.5 rounded-xl bg-amber-50 border border-amber-300 font-mono-code text-xs text-amber-950 font-bold">
                                {{-- Posisi siswa sendiri dan bonus reputasi --}}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </aside>
    </div>

    {{-- MODAL KONFIRMASI KELUAR KELAS (TACTILE NEO-BRUTALIST KHAS PEMBDAHUB) --}}
    <div id="leave-meeting-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden" style="background-color: rgba(18, 19, 22, 0.75); backdrop-filter: blur(4px);">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 relative" style="border: 2.5px solid #121316; box-shadow: 8px 8px 0px #ff3823;">
            <span class="absolute top-2.5 left-3 font-mono-code text-xs select-none" style="color: #999;">⌜</span>
            <span class="absolute top-2.5 right-3 font-mono-code text-xs select-none" style="color: #999;">⌝</span>
            <span class="absolute bottom-2.5 left-3 font-mono-code text-xs select-none" style="color: #999;">⌞</span>
            <span class="absolute bottom-2.5 right-3 font-mono-code text-xs select-none" style="color: #999;">⌟</span>

            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl shadow-sm" style="background-color: #ff3823; border: 2px solid #121316; color: #ffffff;">
                <i class="fa-solid fa-door-open"></i>
            </div>
            <div>
                <div class="text-[10px] font-mono-code font-black uppercase" style="color: #ff3823;">✱ KELUAR SESI</div>
                <h3 class="text-lg font-black" style="color: #121316;">Tinggalkan PembdaTEMU?</h3>
                <p class="text-xs mt-1.5 leading-relaxed font-medium" style="color: #555555;">
                    Durasi kehadiran Anda sampai detik ini akan tersimpan di rekap absensi guru. Anda dapat bergabung kembali selama guru masih membuka kelas.
                </p>
                <div class="mt-3 p-2.5 rounded-xl flex items-center gap-2.5 text-xs font-mono-code" style="background-color: #ecfdf5; color: #065f46; border: 1.5px solid #10b981;">
                    <div class="w-7 h-7 rounded-lg bg-emerald-500 text-white flex items-center justify-center font-bold text-sm flex-shrink-0 border border-black shadow-xs">
                        <i class="fa-solid fa-award"></i>
                    </div>
                    <div>
                        <span class="font-black text-emerald-950">+25 Poin Reputasi Siswa</span> otomatis diraih jika Anda hadir aktif minimal 5 menit di sesi PembdaTEMU ini!
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button onclick="closeLeaveModal()" type="button" class="btn-tactile-white px-4 py-2 rounded-xl text-xs font-mono-code font-bold">
                    Batal
                </button>
                <button onclick="executeLeaveMeeting()" type="button" class="btn-tactile-red px-4 py-2 rounded-xl text-xs font-mono-code font-black flex items-center gap-1.5">
                    <i class="fa-solid fa-check"></i> Ya, Tinggalkan Sesi
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL PILIH SUASANA HATI / MOOD SISWA (TACTILE NEO-BRUTALIST) --}}
    <div id="mood-picker-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden" style="background-color: rgba(18, 19, 22, 0.75); backdrop-filter: blur(4px);">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 relative" style="border: 2.5px solid #121316; box-shadow: 8px 8px 0px #fbc02d;">
            <span class="absolute top-2.5 left-3 font-mono-code text-xs select-none" style="color: #999;">⌜</span>
            <span class="absolute top-2.5 right-3 font-mono-code text-xs select-none" style="color: #999;">⌝</span>
            <span class="absolute bottom-2.5 left-3 font-mono-code text-xs select-none" style="color: #999;">⌞</span>
            <span class="absolute bottom-2.5 right-3 font-mono-code text-xs select-none" style="color: #999;">⌟</span>

            <div class="flex items-center justify-between border-b-2 border-black pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-xl" style="background-color: #fbc02d; border: 2px solid #121316; color: #121316; box-shadow: 2px 2px 0px #121316;">
                        <i class="fa-regular fa-face-smile"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-mono-code font-black uppercase text-[#ea580c]">✱ SUASANA HATI KELAS</div>
                        <h3 class="text-base font-black text-[#121316]">Bagaimana Suasana Hatimu Saat Ini?</h3>
                    </div>
                </div>
                <button onclick="closeMoodModal()" type="button" class="p-2 rounded-xl border border-black hover:bg-slate-100">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <p class="text-xs text-slate-600 font-medium leading-relaxed">
                Pilih ikon yang paling menggambarkan perasaan atau kondisimu sekarang. Guru dapat melihat rekap suasana hati untuk menyesuaikan tempo dan suasana pembelajaran:
            </p>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 max-h-[50vh] overflow-y-auto p-1">
                @foreach(\App\Services\LmsMeetingSyncService::getAvailableMoods() as $mKey => $m)
                    <button type="button" onclick="selectStudentMood('{{ $mKey }}', '{{ $m['icon'] }}', '{{ $m['label'] }}', '{{ $m['bg'] }}', '{{ $m['color'] }}')" 
                        class="mood-option-btn flex items-center gap-2.5 p-2.5 rounded-2xl text-left transition-all active:scale-95" 
                        id="mood-btn-{{ $mKey }}"
                        style="background-color: {{ $m['bg'] }}; color: {{ $m['color'] }}; border: 2px solid #121316; box-shadow: 3px 3px 0px #121316;">
                        <span class="text-2xl flex-shrink-0">{{ $m['icon'] }}</span>
                        <div class="min-w-0">
                            <div class="text-xs font-black truncate">{{ $m['label'] }}</div>
                            <div class="text-[9px] font-mono-code opacity-75 font-bold">Pilih Status</div>
                        </div>
                    </button>
                @endforeach
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200">
                <button onclick="closeMoodModal()" type="button" class="btn-tactile-white px-4 py-2 rounded-xl text-xs font-mono-code font-bold">
                    Tutup
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

        // Zoom Layout State Variables (Siswa - Default: Model Ruang Kelas 5x8 Kursi)
        let currentLayout    = 'classroom'; // 'classroom' | 'gallery' | 'speaker' | 'sidebyside'
        let currentPinnedUid = 'teacher';   // 'teacher' | 'local' | remote uid
        let attendeesMap     = {};          // user.uid => student name

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
            } else if (mode === 'gallery') {
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

        // 🎯 FITUR UNGGULAN: PROMOTE SISWA KE MIMBAR BICARA / BERTANYA (KANAN ATAS SEJAJAR GURU)
        let activeStudentSpeakerUid = null;
        let activeSpeakerTimer = null;

        function promoteStudentToSpeakerPodium(uid) {
            activeStudentSpeakerUid = String(uid);
            if (activeSpeakerTimer) clearTimeout(activeSpeakerTimer);
            if (currentLayout === 'classroom') {
                rearrangeStudentTiles();
            }
            // Tetap di mimbar selama 7 detik setelah hening bicara
            activeSpeakerTimer = setTimeout(() => {
                activeStudentSpeakerUid = null;
                if (currentLayout === 'classroom') {
                    rearrangeStudentTiles();
                }
            }, 7000);
        }

        // 🎯 BUAT DOM TILE UNTUK VIDEO REMOTE TEMAN SEKELAS
        // BADGE NAMA SISWA DITETAPKAN DI POJOK KIRI ATAS AGAR TIDAK TERTUTUP BAR MENU!
        function createRemoteStudentTile(user) {
            const tile = document.createElement('div');
            tile.id = `video-tile-${user.uid}`;
            tile.className = 'video-tile group relative rounded-3xl overflow-hidden flex items-center justify-center transition-all duration-300';
            tile.style.backgroundColor = '#0f172a';
            tile.style.border = '2.5px solid #121316';
            tile.style.boxShadow = '4px 4px 0px #121316';
            
            const rawName = attendeesMap[user.uid] || `Siswa #${user.uid}`;
            const shortName = rawName.split(' ')[0] || rawName;

            tile.innerHTML = `
                <div id="remote-${user.uid}" class="w-full h-full relative"></div>
                <div id="remote-placeholder-${user.uid}" class="absolute inset-0 flex flex-col items-center justify-center hidden pointer-events-none" style="background-color: #121316; color: #cbd5e1;">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl font-black text-white mb-2 shadow-lg" style="background: linear-gradient(135deg, #06b6d4, #4f46e5); border: 2px solid #ffffff;">
                        ${shortName.charAt(0)}
                    </div>
                    <p class="text-xs font-mono-code font-bold" style="color: #fde047;">${shortName}</p>
                </div>
                {{-- BADGE NAMA SISWA LAIN: TEPAT DI POJOK KIRI ATAS - 100% AMAN DARI MENU BAR BAWAH --}}
                <div class="absolute top-4 left-4 z-20 flex items-center gap-1.5 backdrop-blur-md px-2.5 py-1.5 rounded-xl text-xs font-black shadow-md flex-wrap" style="background-color: rgba(18, 19, 22, 0.95); color: #ffffff; border: 2px solid #121316; box-shadow: 3px 3px 0px #2563eb;" title="${rawName}">
                    <span class="w-2 h-2 rounded-full animate-pulse" style="background-color: #10b981;"></span>
                    <i id="tile-mic-${user.uid}" class="fa-solid fa-microphone text-xs" style="color: #10b981;"></i>
                    <span id="tile-name-${user.uid}" class="truncate max-w-[120px] font-mono-code font-bold" style="color: #fde047;">${shortName}</span>
                    <span class="px-1.5 py-0.5 rounded text-[8px] font-mono-code font-black uppercase tracking-wider" style="background-color: #2563eb; color: #ffffff;">SISWA</span>
                    <span id="tile-mood-${user.uid}" class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[9px] font-mono-code font-black" style="background-color: #f1f5f9; color: #121316; border: 1px solid #121316;">
                        🔥 Semangat
                    </span>
                    <span id="tile-hand-${user.uid}" class="hidden px-1.5 py-0.2 rounded text-[9px] font-mono-code font-black animate-bounce" style="background-color: #ef4444; color: #ffffff; border: 1px solid #121316;">
                        ✋
                    </span>
                </div>
                <div class="absolute top-4 right-4 z-20 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button onclick="pinStudentSpeaker('${user.uid}')" type="button" class="p-2 rounded-xl text-xs transition active:translate-x-0.5 active:translate-y-0.5" style="background-color: #ffffff; color: #121316; border: 2px solid #121316; box-shadow: 2px 2px 0px #121316;" title="Fokuskan / Spotlight">
                        <i class="fa-solid fa-thumbtack text-xs"></i>
                    </button>
                </div>
            `;
            return tile;
        }

        // Susun ulang posisi tile video berdasarkan mode tata letak Zoom (Siswa)
        function rearrangeStudentTiles() {
            const isClassroom = (currentLayout === 'classroom');
            const isSpeaker   = (currentLayout === 'speaker');
            const classroomStage = document.getElementById('stage-classroom-wrapper');
            const galleryStage   = document.getElementById('stage-gallery-wrapper');
            const speakerStage   = document.getElementById('stage-speaker-wrapper');
            const classroomGrid  = document.getElementById('classroom-grid');
            const teacherSlot    = document.getElementById('podium-teacher-slot');
            const speakerSlot    = document.getElementById('podium-speaker-slot');
            const speakerIdle    = document.getElementById('podium-speaker-idle');
            const speakerBadge   = document.getElementById('podium-speaker-badge');
            const galleryGrid    = document.getElementById('gallery-grid');
            const filmstrip      = document.getElementById('speaker-filmstrip');
            const mainStage      = document.getElementById('speaker-main-stage');
            const occupancyPill  = document.getElementById('classroom-occupancy-pill');

            // Ambil seluruh tile video yang ada
            const allTiles = Array.from(document.querySelectorAll('.video-tile'));
            const teacherTile = document.getElementById('video-tile-teacher');
            const localTile = document.getElementById('video-tile-local');
            // Kumpulkan tile murid: local tile di depan (#01), diikuti teman sekelas
            const classmateTiles = allTiles.filter(t => t.id !== 'video-tile-teacher' && t.id !== 'video-tile-local');
            const studentTiles = [];
            if (localTile) studentTiles.push(localTile);
            studentTiles.push(...classmateTiles);
            const total = allTiles.length;

            if (isClassroom) {
                if (galleryStage) galleryStage.classList.add('hidden');
                if (speakerStage) speakerStage.classList.add('hidden');
                if (classroomStage) {
                    classroomStage.classList.remove('hidden');
                    classroomStage.classList.add('flex');
                }

                // 1. Tempatkan Video Guru di Mimbar Kiri Depan
                if (teacherTile && teacherSlot) {
                    teacherSlot.appendChild(teacherTile);
                    teacherTile.className = 'video-tile relative rounded-2xl overflow-hidden flex items-center justify-center w-full h-full transition-all';
                    teacherTile.style.backgroundColor = '#0f172a';
                    teacherTile.style.border = '2.5px solid #121316';
                    teacherTile.style.boxShadow = 'none';
                    teacherTile.classList.remove('filmstrip-tile', 'pinned-speaker');
                    teacherTile.onclick = null;
                }

                // 2. Tempatkan Active Speaker Siswa di Mimbar Kanan Depan (Sejajar Guru)
                let speakerTile = null;
                if (activeStudentSpeakerUid) {
                    if (activeStudentSpeakerUid === String(AGORA_UID) || activeStudentSpeakerUid === 'local') {
                        speakerTile = localTile;
                    } else {
                        speakerTile = document.getElementById(`video-tile-${activeStudentSpeakerUid}`);
                    }
                }

                const podiumContainer = document.getElementById('podium-container');
                const podiumGrid = document.getElementById('podium-grid');
                const speakerWrapper = document.getElementById('podium-speaker-wrapper');

                if (speakerTile && speakerSlot) {
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

                    speakerSlot.appendChild(speakerTile);
                    speakerTile.className = 'video-tile relative rounded-2xl overflow-hidden flex items-center justify-center w-full h-full transition-all';
                    speakerTile.style.backgroundColor = '#0f172a';
                    speakerTile.style.border = '2.5px solid #2563eb';
                    speakerTile.style.boxShadow = 'none';
                    speakerTile.classList.remove('filmstrip-tile', 'pinned-speaker');
                    speakerTile.onclick = null;
                    if (speakerBadge) {
                        const isMe = (speakerTile === localTile);
                        speakerBadge.textContent = isMe ? 'Anda Sedang Berbicara 🎤' : 'Sedang Berbicara 🎤';
                        speakerBadge.className = 'px-1.5 py-0.2 rounded text-[9px] font-mono-code font-black bg-blue-600 text-white animate-pulse';
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

                // 3. Update occupancy pill (termasuk saya)
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
                            // Cek jika siswa ini sedang berada di mimbar kanan atas
                            const isAtSpeakerPodium = (speakerTile && sTile === speakerTile);

                            if (isAtSpeakerPodium) {
                                // Tampilkan penanda bahwa siswa sedang berada di mimbar depan
                                const speakingSeat = document.createElement('div');
                                speakingSeat.className = 'h-24 sm:h-28 rounded-2xl flex flex-col items-center justify-center p-2 text-center transition-all bg-blue-50 border-2 border-blue-400 select-none shadow-sm';
                                speakingSeat.innerHTML = `
                                    <span class="text-[9px] font-mono-code font-bold mb-1 text-blue-900">Meja #${seatNum}</span>
                                    <i class="fa-solid fa-microphone-lines text-lg text-blue-600 mb-1 animate-bounce"></i>
                                    <span class="text-[8px] font-mono-code font-black text-blue-700 uppercase">[ Di Mimbar Depan 🎤 ]</span>
                                `;
                                classroomGrid.appendChild(speakingSeat);
                            } else {
                                // Kursi Terisi: Pasang student video tile
                                sTile.className = 'video-tile relative rounded-2xl overflow-hidden flex items-center justify-center w-full h-24 sm:h-28 aspect-video transition-all';
                                sTile.style.backgroundColor = '#0f172a';
                                sTile.style.border = (sTile.id === 'video-tile-local') ? '2.5px solid #2563eb' : '2px solid #121316';
                                sTile.style.boxShadow = (sTile.id === 'video-tile-local') ? '3px 3px 0px #2563eb' : '3px 3px 0px #121316';
                                sTile.classList.remove('filmstrip-tile', 'pinned-speaker');
                                sTile.onclick = null;

                                // Tag nomor meja
                                let deskTag = sTile.querySelector('.seat-desk-tag');
                                const badge = sTile.querySelector('.absolute.top-4.left-4') || sTile.querySelector('.rounded-xl');
                                if (!deskTag && badge) {
                                    deskTag = document.createElement('span');
                                    deskTag.className = 'seat-desk-tag px-1.5 py-0.5 rounded text-[8px] font-mono-code font-black';
                                    deskTag.style.backgroundColor = (sTile.id === 'video-tile-local') ? '#2563eb' : '#fbc02d';
                                    deskTag.style.color = (sTile.id === 'video-tile-local') ? '#ffffff' : '#121316';
                                    deskTag.style.border = '1px solid #121316';
                                    badge.insertBefore(deskTag, badge.firstChild);
                                }
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
                    pinnedTile.className = 'video-tile relative rounded-3xl overflow-hidden flex items-center justify-center w-full h-full max-h-[82vh] transition-all pinned-speaker';
                    pinnedTile.style.backgroundColor = '#0f172a';
                    pinnedTile.style.border = '3px solid #2563eb';
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
                        tile.onclick = () => {
                            if (tile.id === 'video-tile-teacher') pinStudentSpeaker('teacher');
                            else if (tile.id === 'video-tile-local') pinStudentSpeaker('local');
                            else pinStudentSpeaker(tile.id.replace('video-tile-', ''));
                        };
                    }
                });
            } else {
                // Mode Galeri atau Mode Berdampingan
                if (classroomStage) classroomStage.classList.add('hidden');
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
                            allTiles[0].className = 'video-tile relative rounded-3xl overflow-hidden flex items-center justify-center w-full h-full max-h-[82vh] transition-all';
                            allTiles[0].style.border = '2.5px solid #121316';
                            allTiles[0].style.boxShadow = '5px 5px 0px #121316';
                        }
                    } else if (total === 2) {
                        galleryGrid.className = 'w-full h-full max-h-[82vh] grid grid-cols-1 md:grid-cols-2 gap-3.5 items-center justify-center p-2';
                        allTiles.forEach(t => {
                            t.className = 'video-tile relative rounded-3xl overflow-hidden flex items-center justify-center w-full h-full max-h-[75vh] aspect-video transition-all';
                            t.style.border = '2px solid #121316';
                            t.style.boxShadow = '4px 4px 0px #121316';
                        });
                    } else if (total <= 4) {
                        galleryGrid.className = 'w-full h-full max-h-[82vh] grid grid-cols-2 grid-rows-2 gap-3 items-center justify-center p-2';
                        allTiles.forEach(t => {
                            t.className = 'video-tile relative rounded-2xl overflow-hidden flex items-center justify-center w-full h-full aspect-video transition-all';
                            t.style.border = '2px solid #121316';
                            t.style.boxShadow = '3px 3px 0px #121316';
                        });
                    } else if (total <= 6) {
                        galleryGrid.className = 'w-full h-full max-h-[82vh] grid grid-cols-3 grid-rows-2 gap-2.5 items-center justify-center p-2';
                        allTiles.forEach(t => {
                            t.className = 'video-tile relative rounded-2xl overflow-hidden flex items-center justify-center w-full h-full aspect-video transition-all';
                            t.style.border = '2px solid #121316';
                            t.style.boxShadow = '3px 3px 0px #121316';
                        });
                    } else {
                        galleryGrid.className = 'w-full h-full max-h-[82vh] grid grid-cols-3 md:grid-cols-4 gap-2.5 overflow-y-auto p-2';
                        allTiles.forEach(t => {
                            t.className = 'video-tile relative rounded-2xl overflow-hidden flex items-center justify-center w-full aspect-video transition-all';
                            t.style.border = '2px solid #121316';
                            t.style.boxShadow = '3px 3px 0px #121316';
                        });
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

            // Deteksi Active Speaker (Border hijau + Auto-Promote Siswa Bicara ke Mimbar)
            agoraClient.enableAudioVolumeIndicator();
            agoraClient.on('volume-indicator', (volumes) => {
                document.querySelectorAll('.video-tile').forEach(t => t.classList.remove('speaking'));
                volumes.forEach(vol => {
                    // Ambang batas suara level >= 28 untuk mencegah suara bocor/noise menaikkan siswa ke mimbar
                    if (vol.level >= 28) {
                        let targetTileId = null;
                        if (vol.uid === 0 || vol.uid === AGORA_UID) {
                            // Siswa lokal hanya dipromosikan ke mimbar jika mic menyala (tidak muted)
                            if (isMicOn) {
                                targetTileId = 'video-tile-local';
                                promoteStudentToSpeakerPodium(AGORA_UID);
                            }
                        } else if (vol.uid === TEACHER_USER_ID) {
                            targetTileId = 'video-tile-teacher';
                        } else {
                            targetTileId = `video-tile-${vol.uid}`;
                            // Siswa lain berbicara -> Naikkan ke mimbar depan
                            promoteStudentToSpeakerPodium(vol.uid);
                        }
                        if (targetTileId) {
                            const tileEl = document.getElementById(targetTileId);
                            if (tileEl) tileEl.classList.add('speaking');
                        }
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
                const tileMic = document.getElementById('tile-mic-local');
                if (isMicOn) {
                    icon.className = 'fa-solid fa-microphone text-base text-white';
                    btn.style.backgroundColor = '#10b981';
                    btn.style.color = '#ffffff';
                    if (tileMic) {
                        tileMic.className = 'fa-solid fa-microphone text-xs';
                        tileMic.style.color = '#10b981';
                    }
                } else {
                    icon.className = 'fa-solid fa-microphone-slash text-base text-white';
                    btn.style.backgroundColor = '#ff3823';
                    btn.style.color = '#ffffff';
                    if (tileMic) {
                        tileMic.className = 'fa-solid fa-microphone-slash text-xs';
                        tileMic.style.color = '#ff3823';
                    }
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
                    icon.className = 'fa-solid fa-video text-base text-white';
                    btn.style.backgroundColor = '#0891b2';
                    btn.style.color = '#ffffff';
                } else {
                    icon.className = 'fa-solid fa-video-slash text-base text-white';
                    btn.style.backgroundColor = '#ff3823';
                    btn.style.color = '#ffffff';
                }
            } catch (err) {
                console.warn('Gagal mengakses kamera siswa:', err);
                alert('Tidak dapat mengakses kamera. Pastikan Anda telah memberikan izin kamera pada peramban (browser).');
            }
        }

        // 3. ANGKAT TANGAN (RAISE HAND) SISWA
        async function studentToggleRaiseHand() {
            isRaiseHand = !isRaiseHand;
            const btn = document.getElementById('btn-raise-hand');
            const label = document.getElementById('raise-hand-label');
            const handLocal = document.getElementById('tile-hand-local');
            if (isRaiseHand) {
                btn.style.backgroundColor = '#ff3823';
                btn.style.color = '#ffffff';
                if (label) label.textContent = 'Tangan Terangkat!';
                if (handLocal) handLocal.classList.remove('hidden');
                // Naikkan diri sendiri ke mimbar pembicara depan
                promoteStudentToSpeakerPodium(AGORA_UID);
            } else {
                btn.style.backgroundColor = '#fbc02d';
                btn.style.color = '#121316';
                if (label) label.textContent = 'Angkat Tangan';
                if (handLocal) handLocal.classList.add('hidden');
            }
            await syncMeetingStudent({ raise_hand: isRaiseHand });
        }

        // 4. SUASANA HATI / MOOD PICKER SISWA
        let currentMood = 'semangat';

        function openMoodModal() {
            const modal = document.getElementById('mood-picker-modal');
            if (modal) modal.classList.remove('hidden');
        }

        function closeMoodModal() {
            const modal = document.getElementById('mood-picker-modal');
            if (modal) modal.classList.add('hidden');
        }

        async function selectStudentMood(key, icon, label, bg, color) {
            currentMood = key;
            const iconEl = document.getElementById('current-mood-icon');
            const labelEl = document.getElementById('current-mood-label');
            if (iconEl) iconEl.textContent = icon;
            if (labelEl) labelEl.textContent = label;

            const localMoodTag = document.getElementById('tile-mood-local');
            if (localMoodTag) {
                localMoodTag.style.backgroundColor = bg;
                localMoodTag.style.color = color;
                const iSpan = document.getElementById('tile-mood-local-icon');
                const tSpan = document.getElementById('tile-mood-local-text');
                if (iSpan) iSpan.textContent = icon;
                if (tSpan) tSpan.textContent = label;
            }

            closeMoodModal();
            await syncMeetingStudent({ mood: key });
        }

        // 5. MODE HEMAT KUOTA
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
                    btn.style.backgroundColor = '#fbc02d';
                    btn.style.color = '#121316';
                }
                if (label) label.textContent = 'Hemat Aktif';
                if (statusText) statusText.textContent = '⚡ Hemat Kuota';
                alert('Mode Hemat Kuota Aktif: Mengutamakan kelancaran suara guru dan mematikan pengiriman video dari perangkat Anda.');
            } else {
                if (btn) {
                    btn.style.backgroundColor = '#ffffff';
                    btn.style.color = '#121316';
                }
                if (label) label.textContent = 'Hemat Kuota';
                if (statusText) statusText.textContent = 'Koneksi Normal';
            }
        }

        // 6. LIVE TIMER KEHADIRAN SISWA
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

        // 7. TOGGLE & SWITCH TAB LEARNING COMPANION HUB
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

        let isStudentChatActiveTab = false;
        let unreadStudentChatCount = 0;

        function switchStudentTab(tabName) {
            const hub = document.getElementById('student-hub');
            const handler = document.getElementById('resize-handler');
            if (hub.classList.contains('hidden')) {
                hub.classList.remove('hidden');
                hub.classList.add('flex');
                if (handler) handler.classList.remove('hidden');
            }

            const tabs = ['materi', 'notes', 'friends', 'chat', 'polling', 'qa', 'kuis'];
            tabs.forEach(t => {
                const btn = document.getElementById('tab-btn-' + t);
                const content = document.getElementById('tab-content-' + t);
                if (t === tabName) {
                    if (btn) {
                        btn.style.backgroundColor = '#121316';
                        btn.style.color = '#fde047';
                        btn.style.boxShadow = '2px 2px 0px #ff3823';
                        btn.classList.add('font-black');
                        btn.classList.remove('font-bold');
                    }
                    if (content) content.classList.remove('hidden');
                } else {
                    if (btn) {
                        btn.style.backgroundColor = '#ffffff';
                        btn.style.color = '#121316';
                        btn.style.boxShadow = 'none';
                        btn.classList.add('font-bold');
                        btn.classList.remove('font-black');
                    }
                    if (content) content.classList.add('hidden');
                }
            });

            if (tabName === 'polling') {
                const pBadge = document.getElementById('student-poll-tab-badge');
                if (pBadge) pBadge.classList.add('hidden');
            }
            if (tabName === 'qa') {
                const qBadge = document.getElementById('student-qa-tab-badge');
                if (qBadge) qBadge.classList.add('hidden');
            }
            if (tabName === 'kuis') {
                const qBadge = document.getElementById('student-quiz-tab-badge');
                const quickQBadge = document.getElementById('student-quick-quiz-badge');
                if (qBadge) qBadge.classList.add('hidden');
                if (quickQBadge) quickQBadge.classList.add('hidden');
            }

            if (tabName === 'chat') {
                isStudentChatActiveTab = true;
                unreadStudentChatCount = 0;
                const badge1 = document.getElementById('chat-tab-badge');
                const badge2 = document.getElementById('quick-chat-badge');
                if (badge1) badge1.classList.add('hidden');
                if (badge2) badge2.classList.add('hidden');
                const c = document.getElementById('chat-messages-container');
                if (c) c.scrollTop = c.scrollHeight;
            } else {
                isStudentChatActiveTab = false;
            }
        }

        function openStudentTab(tabName) {
            switchStudentTab(tabName);
        }

        // 8. DISPLAY MATERI PEMBELAJARAN
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

        // 9. BUKU CATATAN SISWA (PERSISTED LOCALSTORAGE)
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

        // ============================================================
        // 10. REALTIME SIGNALING & SYNC SISWA (PRESENSI, MOOD, HANDS, CHAT)
        // ============================================================
        let isSyncingStudent = false;
        let lastRenderedStudentChatIds = new Set();

        async function syncMeetingStudent(extraPayload = {}) {
            if (isSyncingStudent) return;
            isSyncingStudent = true;
            try {
                const payload = Object.assign({
                    action: 'sync',
                    raise_hand: isRaiseHand,
                    mood: currentMood
                }, extraPayload);

                const res = await fetch("{{ route('siswa.lms.meeting.sync', $course->id) }}", {
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
                handleStudentMeetingSyncData(data);
            } catch(err) {
                console.warn("Sync siswa warning:", err);
            } finally {
                isSyncingStudent = false;
            }
        }

        function handleStudentMeetingSyncData(data) {
            if (!data) return;

            // 0. Cek jika Guru sudah mengakhiri meeting
            if (data.is_active === false && !window.isMeetingEndedByTeacher) {
                window.isMeetingEndedByTeacher = true;
                showToast('Guru telah mengakhiri sesi PembdaTEMU. Mengalihkan ke LMS...', 'info');
                setTimeout(() => {
                    window.location.href = "{{ route('siswa.lms.show', $course->id) }}";
                }, 2000);
                return;
            }

            // 1. Attendees Map & Friends List
            const attendees = data.attendees || [];
            const friendsOnly = attendees.filter(a => a.user_id !== AGORA_UID && a.role === 'siswa');
            const badgeEl = document.getElementById('friends-badge-count');
            if (badgeEl) badgeEl.textContent = (friendsOnly.length + 1) + ' Online';

            // Update attendeesMap
            attendees.forEach(stu => {
                const sid = String(stu.user_id);
                attendeesMap[sid] = stu.name;

                // Tile nama
                const tileNameSpan = document.getElementById(`tile-name-${sid}`);
                if (tileNameSpan) {
                    tileNameSpan.textContent = stu.name.split(' ')[0];
                }

                // Remote mood tag
                const moodTag = document.getElementById(`tile-mood-${sid}`);
                if (moodTag && stu.mood_icon) {
                    moodTag.innerHTML = `<span>${stu.mood_icon}</span> <span>${stu.mood_label}</span>`;
                    moodTag.style.backgroundColor = stu.mood_bg || '#f1f5f9';
                    moodTag.style.color = stu.mood_color || '#121316';
                }

                // Remote hand tag
                const handTag = document.getElementById(`tile-hand-${sid}`);
                if (handTag) {
                    if (stu.raise_hand) {
                        handTag.classList.remove('hidden');
                    } else {
                        handTag.classList.add('hidden');
                    }
                }
            });

            // Cek apakah guru menurunkan tangan kita
            const myRecord = attendees.find(a => a.user_id === AGORA_UID);
            if (myRecord) {
                if (!myRecord.raise_hand && isRaiseHand) {
                    isRaiseHand = false;
                    const btn = document.getElementById('btn-raise-hand');
                    const label = document.getElementById('raise-hand-label');
                    const handLocal = document.getElementById('tile-hand-local');
                    if (btn) {
                        btn.style.backgroundColor = '#fbc02d';
                        btn.style.color = '#121316';
                    }
                    if (label) label.textContent = 'Angkat Tangan';
                    if (handLocal) handLocal.classList.add('hidden');
                }
            }

            // Render Friends List
            const friendsList = document.getElementById('friends-list');
            if (friendsList) {
                if (friendsOnly.length === 0) {
                    friendsList.innerHTML = `
                        <div class="text-center py-10 px-4 poster-card bg-white p-6" style="color: #777;">
                            <div class="w-14 h-14 mx-auto mb-3 rounded-2xl flex items-center justify-center text-2xl shadow-sm" style="background-color: #ede9df; border: 2px solid #121316; color: #2563eb;">
                                <i class="fa-solid fa-users-rays"></i>
                            </div>
                            <h4 class="text-xs font-mono-code font-black uppercase" style="color: #121316;">Belum Ada Teman Lain</h4>
                            <p class="text-[11px] mt-1.5 leading-relaxed font-medium" style="color: #555;">Anda adalah siswa pertama yang bergabung di PembdaTEMU ini. Teman-teman sekelas akan muncul di sini saat mereka masuk.</p>
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
                    friendsOnly.forEach((stu, idx) => {
                        const grad = avatarGradients[idx % avatarGradients.length];
                        const initials = stu.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
                        html += `
                            <div class="flex items-center justify-between p-3 rounded-2xl bg-white text-xs transition-all" style="border: 2px solid #121316; box-shadow: 3px 3px 0px #121316;">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-black text-white flex-shrink-0" style="background: ${grad}; border: 2px solid #121316;">
                                        ${initials}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-black truncate" style="color: #121316;">${stu.name}</p>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded-md text-[9px] font-bold" style="background-color: ${stu.mood_bg || '#f1f5f9'}; color: ${stu.mood_color || '#121316'}; border: 1px solid #121316;">
                                                ${stu.mood_icon || '🔥'} ${stu.mood_label || 'Semangat'}
                                            </span>
                                            ${stu.raise_hand ? '<span class="px-1.5 py-0.2 rounded text-[9px] bg-red-500 text-white font-bold animate-pulse">✋ Bertanya</span>' : ''}
                                        </div>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-mono-code font-black" style="background-color: #10b981; color: #ffffff; border: 1.5px solid #121316;">
                                    <span class="w-1.5 h-1.5 rounded-full animate-ping" style="background-color: #ffffff;"></span> HADIR
                                </span>
                            </div>
                        `;
                    });
                    friendsList.innerHTML = html;
                }
            }

            // 2. Chat Recipient Select
            const chatSelect = document.getElementById('chat-recipient-select');
            if (chatSelect) {
                const currentVal = chatSelect.value;
                let selectOptions = `<option value="all">📢 Semua Orang (Publik)</option>`;
                
                // Add teacher
                const teacherUser = attendees.find(a => a.role === 'guru');
                if (teacherUser) {
                    selectOptions += `<option value="${teacherUser.user_id}">👨‍🏫 Guru: ${teacherUser.name}</option>`;
                }
                // Add classmates
                friendsOnly.forEach(f => {
                    selectOptions += `<option value="${f.user_id}">👤 ${f.name} (Teman)</option>`;
                });
                chatSelect.innerHTML = selectOptions;
                if (currentVal && chatSelect.querySelector(`option[value="${currentVal}"]`)) {
                    chatSelect.value = currentVal;
                }
            }

            // 3. Render Chats
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
                        if (!lastRenderedStudentChatIds.has(c.id)) {
                            newIncomingMsg = true;
                            lastRenderedStudentChatIds.add(c.id);
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

                        if (!isStudentChatActiveTab) {
                            unreadStudentChatCount++;
                            const badge1 = document.getElementById('chat-tab-badge');
                            const badge2 = document.getElementById('quick-chat-badge');
                            if (badge1) {
                                badge1.textContent = unreadStudentChatCount;
                                badge1.classList.remove('hidden');
                            }
                            if (badge2) {
                                badge2.textContent = unreadStudentChatCount;
                                badge2.classList.remove('hidden');
                            }
                        }
                    }
                }
            }

            // 8. Render Polling Kilat untuk Siswa
            renderStudentPoll(data.poll);

            // 9. Render Q&A Queue untuk Siswa
            renderStudentQa(data.qa_list);

            // 10. Render Kuis Live Kahoot-style untuk Siswa (Fase 4)
            renderStudentQuiz(data.quiz);
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

            await syncMeetingStudent({
                action: 'send_chat',
                text: text,
                recipient_id: recipientId,
                recipient_name: recipientName
            });
        }

        function sendQuickReaction(emoji) {
            sendChatMessage(emoji);
        }

        // ============================================================
        // 10B. FITUR FASE 2 SISWA: POLLING KILAT & TANYA JAWAB (Q&A)
        // ============================================================
        let studentCurrentPoll = null;

        async function voteStudentPoll(optionIndex) {
            await syncMeetingStudent({
                action: 'vote_poll',
                option_index: optionIndex
            });
            showToast('Suara Anda telah dicatat!', 'success');
        }

        function renderStudentPoll(poll) {
            studentCurrentPoll = poll;
            const banner = document.getElementById('student-floating-poll-banner');
            const qBanner = document.getElementById('student-floating-poll-question');
            const emptyEl = document.getElementById('student-poll-empty');
            const cardEl = document.getElementById('student-poll-card');
            const headerBadge = document.getElementById('student-poll-header-badge');
            const tabPing = document.getElementById('student-poll-tab-badge');

            if (!poll) {
                if (banner) banner.classList.add('hidden');
                if (emptyEl) emptyEl.classList.remove('hidden');
                if (cardEl) cardEl.classList.add('hidden');
                if (headerBadge) {
                    headerBadge.innerHTML = '<span class="px-2 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-slate-100 text-slate-600 border border-slate-300">Menunggu</span>';
                }
                if (tabPing) tabPing.classList.add('hidden');
                return;
            }

            // Ada Polling
            if (emptyEl) emptyEl.classList.add('hidden');
            if (cardEl) cardEl.classList.remove('hidden');

            const qText = document.getElementById('student-poll-question-text');
            if (qText) qText.textContent = poll.question;

            const totalBadge = document.getElementById('student-poll-total-badge');
            if (totalBadge) totalBadge.textContent = `${poll.total_votes} Suara`;

            const statusInd = document.getElementById('student-poll-status-indicator');
            if (poll.is_active) {
                if (statusInd) {
                    statusInd.className = 'px-2 py-0.5 rounded-md text-[10px] font-mono-code font-black flex items-center gap-1.5 bg-amber-100 text-amber-900 border border-amber-300';
                    statusInd.innerHTML = '<span class="w-2 h-2 rounded-full animate-ping bg-amber-500"></span> POLLING BERLANGSUNG';
                }
                if (headerBadge) {
                    headerBadge.innerHTML = '<span class="px-2 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-amber-100 text-amber-900 border border-amber-300 animate-pulse">● Aktif</span>';
                }
                // Tampilkan banner melayang jika belum vote
                if (!poll.has_voted) {
                    if (banner) {
                        banner.classList.remove('hidden');
                        if (qBanner) qBanner.textContent = poll.question;
                    }
                    if (tabPing) tabPing.classList.remove('hidden');
                } else {
                    if (banner) banner.classList.add('hidden');
                    if (tabPing) tabPing.classList.add('hidden');
                }
            } else {
                if (statusInd) {
                    statusInd.className = 'px-2 py-0.5 rounded-md text-[10px] font-mono-code font-black bg-slate-200 text-slate-700 border border-slate-300';
                    statusInd.innerHTML = '<i class="fa-solid fa-lock text-[9px]"></i> POLLING SELESAI';
                }
                if (headerBadge) {
                    headerBadge.innerHTML = '<span class="px-2 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-slate-100 text-slate-600 border border-slate-300">Selesai</span>';
                }
                if (banner) banner.classList.add('hidden');
                if (tabPing) tabPing.classList.add('hidden');
            }

            // Options Container
            const container = document.getElementById('student-poll-options-container');
            const noticeEl = document.getElementById('student-poll-notice');
            if (!container) return;

            const colors = ['#ea580c', '#0284c7', '#16a34a', '#9333ea', '#ca8a04', '#dc2626'];

            if (poll.is_active && !poll.has_voted) {
                // Tampilkan tombol interaktif untuk memilih
                if (noticeEl) noticeEl.textContent = 'Pilih salah satu jawaban di bawah untuk memberikan suara Anda:';
                let html = '';
                poll.options.forEach((opt, idx) => {
                    const color = colors[idx % colors.length];
                    html += `
                        <button onclick="voteStudentPoll(${idx})" type="button" class="w-full p-3 rounded-2xl flex items-center justify-between font-mono-code font-bold text-xs bg-white hover:bg-amber-50 active:scale-95 transition-all text-slate-900 border-2 border-black" style="box-shadow: 3px 3px 0px #121316;">
                            <div class="flex items-center gap-2.5">
                                <span class="w-6 h-6 rounded-lg flex items-center justify-center text-white font-black text-xs" style="background-color: ${color};">${idx + 1}</span>
                                <span class="text-left font-black">${opt}</span>
                            </div>
                            <i class="fa-solid fa-check text-xs text-slate-300"></i>
                        </button>
                    `;
                });
                container.innerHTML = html;
            } else {
                // Tampilkan hasil & progress bar
                if (noticeEl) {
                    noticeEl.innerHTML = poll.has_voted
                        ? '<span class="text-emerald-700 font-bold">🎉 Suara Anda telah dicatat. Berikut perolehan suara kelas:</span>'
                        : '<span class="text-slate-500">🔒 Polling telah ditutup oleh Guru.</span>';
                }

                let html = '';
                poll.options.forEach((opt, idx) => {
                    const count = poll.counts[idx] || 0;
                    const pct = poll.percentages[idx] || 0;
                    const color = colors[idx % colors.length];
                    const isMyChoice = (idx === poll.user_voted_option);

                    html += `
                        <div class="p-2.5 rounded-xl border ${isMyChoice ? 'border-2 border-amber-500 bg-amber-50/50' : 'border-slate-300 bg-slate-50'} space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-mono-code">
                                <div class="flex items-center gap-2 font-black text-slate-900">
                                    <span class="w-5 h-5 rounded flex items-center justify-center text-[10px] text-white font-bold" style="background-color: ${color};">${idx + 1}</span>
                                    <span>${opt}</span>
                                    ${isMyChoice ? '<span class="px-1.5 py-0.2 rounded text-[9px] font-black bg-amber-400 text-black border border-black">Pilihan Anda</span>' : ''}
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

                if (poll.is_active && poll.has_voted) {
                    html += `
                        <div class="text-center pt-2">
                            <button onclick="changeStudentPollVote()" type="button" class="text-[11px] font-mono-code font-bold text-amber-800 hover:underline">
                                ↺ Ubah Pilihan Jawaban
                            </button>
                        </div>
                    `;
                }

                container.innerHTML = html;
            }
        }

        function changeStudentPollVote() {
            if (!studentCurrentPoll || !studentCurrentPoll.is_active) return;
            studentCurrentPoll.has_voted = false;
            renderStudentPoll(studentCurrentPoll);
        }

        // Q&A Functions for Siswa
        async function submitStudentQuestion() {
            const input = document.getElementById('student-qa-input');
            const text = input ? input.value.trim() : '';
            if (!text) {
                alert('Silakan tuliskan pertanyaan materi Anda.');
                return;
            }

            input.value = '';
            await syncMeetingStudent({
                action: 'ask_question',
                text: text
            });

            showToast('Pertanyaan Anda telah dikirim ke guru!', 'success');
            switchStudentTab('qa');
        }

        async function upvoteStudentQa(questionId) {
            await syncMeetingStudent({
                action: 'upvote_qa',
                question_id: questionId
            });
        }

        function renderStudentQa(qaList) {
            const container = document.getElementById('student-qa-list-container');
            const emptyEl = document.getElementById('student-qa-empty');
            const countBadge = document.getElementById('student-qa-count-badge');
            if (!container) return;

            const list = qaList || [];
            if (countBadge) countBadge.textContent = `${list.length} Pertanyaan`;

            if (list.length === 0) {
                if (emptyEl) emptyEl.classList.remove('hidden');
                container.querySelectorAll('.student-qa-card').forEach(el => el.remove());
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
                    statusBadge = '<span class="px-2 py-0.5 rounded-full text-[9px] font-mono-code font-black bg-amber-400 text-black border border-black animate-pulse flex items-center gap-1"><i class="fa-solid fa-microphone-lines text-xs"></i> SEDANG DIBAHAS GURU</span>';
                } else if (isAnswered) {
                    cardBg = '#f0fdf4';
                    statusBadge = '<span class="px-2 py-0.5 rounded-full text-[9px] font-mono-code font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">✅ Sudah Dijawab</span>';
                } else {
                    statusBadge = '<span class="px-2 py-0.5 rounded-full text-[9px] font-mono-code font-bold bg-slate-100 text-slate-600 border border-slate-300">⏳ Menunggu</span>';
                }

                html += `
                    <div class="student-qa-card p-3 rounded-2xl transition-all space-y-2 relative" style="background-color: ${cardBg}; ${borderStyle}">
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
                            <button onclick="upvoteStudentQa('${item.id}')" type="button" class="btn-tactile-white px-2.5 py-1 rounded-lg text-[10px] font-mono-code font-black flex items-center gap-1.5 ${item.has_upvoted ? 'text-orange-600 border-orange-500' : 'text-slate-600'}" title="Upvote jika Anda juga ingin pertanyaan ini dijawab">
                                <i class="fa-solid fa-thumbs-up"></i>
                                <span>${item.upvote_count} Upvote</span>
                            </button>

                            ${isAnswering ? '<span class="text-[10px] font-mono-code font-black text-amber-700 animate-bounce">Perhatikan Guru di Mimbar 🎤</span>' : ''}
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // ============================================================
        // 10B. FITUR FASE 4: KUIS LIVE KAHOOT-STYLE PLAYER (SISWA)
        // ============================================================
        let currentStudentQuiz = null;
        let studentQuizTimerInterval = null;
        let studentQuizRemaining = 0;
        let hasSubmittedCurrentQuestion = false;
        let lastAnsweredQuestionIndex = -1;

        const KAHOOT_SHAPES_SISWA = ['▲', '◆', '●', '■'];
        const KAHOOT_COLORS_SISWA = ['#ef4444', '#3b82f6', '#f59e0b', '#10b981'];

        async function submitStudentQuizAnswer(answerIndex) {
            if (!currentStudentQuiz || currentStudentQuiz.status !== 'question') return;
            if (hasSubmittedCurrentQuestion) return;

            hasSubmittedCurrentQuestion = true;
            lastAnsweredQuestionIndex = currentStudentQuiz.current_question_index;

            // Visual feedback pada tombol yang dipilih
            for (let i = 0; i < 4; i++) {
                const b = document.getElementById(`kahoot-btn-${i}`);
                if (b) {
                    b.disabled = true;
                    if (i === answerIndex) {
                        b.classList.add('ring-4', 'ring-white', 'scale-95');
                        b.style.boxShadow = 'none';
                    } else {
                        b.classList.add('opacity-40', 'cursor-not-allowed');
                    }
                }
            }

            const ind = document.getElementById('student-answered-indicator');
            if (ind) ind.classList.remove('hidden');

            await syncMeetingStudent({
                action: 'submit_quiz_answer',
                answer_index: answerIndex
            });

            showToast('Jawaban Anda berhasil dikirim! Menunggu papan skor...', 'success');
        }

        function renderStudentQuiz(quiz) {
            currentStudentQuiz = quiz;

            const banner = document.getElementById('student-floating-quiz-banner');
            const qBannerTitle = document.getElementById('student-floating-quiz-title');
            const tabBadge = document.getElementById('student-quiz-tab-badge');
            const quickBadge = document.getElementById('student-quick-quiz-badge');

            const vStandby = document.getElementById('student-quiz-standby');
            const vQuestion = document.getElementById('student-quiz-question-box');
            const vResult = document.getElementById('student-quiz-result-box');
            const vPodium = document.getElementById('student-quiz-podium-box');
            const statusPill = document.getElementById('student-quiz-status-pill');

            if (!quiz || !quiz.status || quiz.status === 'standby') {
                if (banner) banner.classList.add('hidden');
                if (tabBadge) tabBadge.classList.add('hidden');
                if (quickBadge) quickBadge.classList.add('hidden');
                if (statusPill) {
                    statusPill.textContent = 'Menunggu';
                    statusPill.className = 'px-2 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-slate-100 text-slate-700 border border-slate-300';
                }
                if (vStandby) vStandby.classList.remove('hidden');
                if (vQuestion) vQuestion.classList.add('hidden');
                if (vResult) vResult.classList.add('hidden');
                if (vPodium) vPodium.classList.add('hidden');
                if (studentQuizTimerInterval) { clearInterval(studentQuizTimerInterval); studentQuizTimerInterval = null; }
                return;
            }

            // Kuis Mode Soal Aktif
            if (quiz.status === 'question') {
                const curQ = quiz.current_question || {};
                const qIdx = (quiz.current_question_index || 0);
                const totalQ = quiz.total_questions || 1;

                // Reset status jawaban jika soal berganti
                if (lastAnsweredQuestionIndex !== qIdx) {
                    hasSubmittedCurrentQuestion = false;
                }

                const myPlayer = quiz.my_player || null;
                const hasAnsweredServer = (myPlayer && myPlayer.answers && myPlayer.answers[qIdx] !== undefined);
                if (hasAnsweredServer) {
                    hasSubmittedCurrentQuestion = true;
                    lastAnsweredQuestionIndex = qIdx;
                }

                // Tampilkan Floating Banner jika belum menjawab
                if (banner) {
                    if (!hasSubmittedCurrentQuestion) {
                        banner.classList.remove('hidden');
                        if (qBannerTitle) {
                            qBannerTitle.textContent = `Soal #${qIdx + 1}: ${curQ.question || ''}`;
                        }
                    } else {
                        banner.classList.add('hidden');
                    }
                }

                if (tabBadge && !hasSubmittedCurrentQuestion) tabBadge.classList.remove('hidden');
                if (quickBadge && !hasSubmittedCurrentQuestion) quickBadge.classList.remove('hidden');

                if (statusPill) {
                    statusPill.textContent = 'LIVE // SOAL AKTIF';
                    statusPill.className = 'px-2 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-rose-500 text-white animate-pulse border border-rose-700';
                }

                if (vStandby) vStandby.classList.add('hidden');
                if (vQuestion) vQuestion.classList.remove('hidden');
                if (vResult) vResult.classList.add('hidden');
                if (vPodium) vPodium.classList.add('hidden');

                const qBadge = document.getElementById('student-q-badge');
                if (qBadge) qBadge.textContent = `Soal ${qIdx + 1} dari ${totalQ}`;

                const qText = document.getElementById('student-q-text');
                if (qText) qText.textContent = curQ.question || 'Pertanyaan Kuis';

                // Timer Countdown
                const timeLimit = curQ.time_limit || 20;
                const elapsed = quiz.time_elapsed || 0;
                studentQuizRemaining = Math.max(0, timeLimit - elapsed);
                const timerEl = document.getElementById('student-q-timer');
                if (timerEl) timerEl.textContent = `${studentQuizRemaining}s`;

                if (!studentQuizTimerInterval) {
                    studentQuizTimerInterval = setInterval(() => {
                        if (studentQuizRemaining > 0) {
                            studentQuizRemaining--;
                            const tEl = document.getElementById('student-q-timer');
                            if (tEl) tEl.textContent = `${studentQuizRemaining}s`;
                        } else {
                            clearInterval(studentQuizTimerInterval);
                            studentQuizTimerInterval = null;
                        }
                    }, 1000);
                }

                // Status Indicator
                const answeredInd = document.getElementById('student-answered-indicator');
                if (answeredInd) {
                    if (hasSubmittedCurrentQuestion) {
                        answeredInd.classList.remove('hidden');
                    } else {
                        answeredInd.classList.add('hidden');
                    }
                }

                // Render 4 Kahoot-style buttons
                const btnContainer = document.getElementById('student-kahoot-buttons');
                if (btnContainer && curQ.options) {
                    let btnsHtml = '';
                    const myChosenAnswer = myPlayer && myPlayer.answers ? myPlayer.answers[qIdx] : null;

                    curQ.options.forEach((optText, oIdx) => {
                        const shape = KAHOOT_SHAPES_SISWA[oIdx % 4];
                        const color = KAHOOT_COLORS_SISWA[oIdx % 4];
                        const isMyPick = (myChosenAnswer !== null && myChosenAnswer === oIdx);
                        const disabledAttr = hasSubmittedCurrentQuestion ? 'disabled' : '';
                        const opacityClass = hasSubmittedCurrentQuestion && !isMyPick ? 'opacity-40 cursor-not-allowed' : '';
                        const ringClass = isMyPick ? 'ring-4 ring-white scale-95' : '';

                        btnsHtml += `
                            <button id="kahoot-btn-${oIdx}" onclick="submitStudentQuizAnswer(${oIdx})" type="button" ${disabledAttr} class="p-3.5 rounded-2xl text-white font-mono-code font-bold text-xs flex flex-col items-start justify-between min-h-[85px] border-2 border-black transition active:scale-95 ${opacityClass} ${ringClass}" style="background-color: ${color}; box-shadow: ${isMyPick ? 'none' : '3px 3px 0px #121316'};">
                                <div class="flex items-center justify-between w-full">
                                    <span class="w-7 h-7 rounded-lg bg-white/25 flex items-center justify-center font-black text-sm">${shape}</span>
                                    ${isMyPick ? '<span class="px-2 py-0.5 rounded bg-white text-black font-black text-[10px]">Pilihan Anda ✓</span>' : ''}
                                </div>
                                <span class="text-xs font-black mt-2 text-left leading-snug line-clamp-2">${optText}</span>
                            </button>
                        `;
                    });
                    btnContainer.innerHTML = btnsHtml;
                }
                return;
            }

            // Kuis Mode Scoreboard / Result
            if (quiz.status === 'scoreboard') {
                if (studentQuizTimerInterval) { clearInterval(studentQuizTimerInterval); studentQuizTimerInterval = null; }
                if (banner) banner.classList.add('hidden');
                if (tabBadge) tabBadge.classList.add('hidden');
                if (quickBadge) quickBadge.classList.add('hidden');

                if (statusPill) {
                    statusPill.textContent = 'PAPAN SKOR';
                    statusPill.className = 'px-2 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-indigo-500 text-white border border-indigo-700';
                }

                if (vStandby) vStandby.classList.add('hidden');
                if (vQuestion) vQuestion.classList.add('hidden');
                if (vResult) vResult.classList.remove('hidden');
                if (vPodium) vPodium.classList.add('hidden');

                const curQ = quiz.current_question || {};
                const myPlayer = quiz.my_player || null;

                // Result Banner
                const resBanner = document.getElementById('student-result-banner');
                if (resBanner) {
                    if (myPlayer && myPlayer.last_answer_correct) {
                        resBanner.className = 'p-3 rounded-xl text-center mb-3 border-2 border-black bg-emerald-400 text-emerald-950 font-mono-code';
                        resBanner.style.boxShadow = '3px 3px 0px #121316';
                        resBanner.innerHTML = `
                            <div class="text-xl">🎉 LUAR BIASA!</div>
                            <div class="text-xs font-black mt-0.5">Jawaban Anda Benar (+${myPlayer.last_answer_points || 0} Poin)</div>
                        `;
                    } else if (myPlayer && myPlayer.last_answer !== undefined) {
                        resBanner.className = 'p-3 rounded-xl text-center mb-3 border-2 border-black bg-rose-400 text-rose-950 font-mono-code';
                        resBanner.style.boxShadow = '3px 3px 0px #121316';
                        resBanner.innerHTML = `
                            <div class="text-xl">❌ YAH, BELUM TEPAT</div>
                            <div class="text-xs font-black mt-0.5">Jangan menyerah! Masih ada kesempatan di soal berikutnya.</div>
                        `;
                    } else {
                        resBanner.className = 'p-3 rounded-xl text-center mb-3 border-2 border-black bg-slate-200 text-slate-800 font-mono-code';
                        resBanner.style.boxShadow = '3px 3px 0px #121316';
                        resBanner.innerHTML = `
                            <div class="text-sm font-bold">Waktu Habis / Tidak Menjawab</div>
                        `;
                    }
                }

                // Rank & Score
                const myRankEl = document.getElementById('student-my-rank');
                if (myRankEl) {
                    myRankEl.textContent = myPlayer && myPlayer.rank ? `#${myPlayer.rank} dari ${quiz.total_players || 1} Siswa` : '-';
                }
                const myScoreEl = document.getElementById('student-my-score');
                if (myScoreEl) {
                    myScoreEl.textContent = `${(myPlayer ? myPlayer.score : 0).toLocaleString()} pts`;
                }

                // Correct Answer Info
                const correctInfoEl = document.getElementById('student-correct-answer-info');
                if (correctInfoEl && curQ.options && curQ.correct_answer !== undefined) {
                    const corrText = curQ.options[curQ.correct_answer] || '';
                    correctInfoEl.innerHTML = `
                        <div class="font-bold text-emerald-800">
                            <strong>Kunci Jawaban Benar:</strong> ${corrText}
                        </div>
                        ${curQ.explanation ? `<div class="mt-1 text-slate-600"><em>Penjelasan:</em> ${curQ.explanation}</div>` : ''}
                    `;
                }

                // Top 5 Leaderboard
                const topContainer = document.getElementById('student-top-leaderboard');
                if (topContainer) {
                    const topList = quiz.top_players || [];
                    let topHtml = '';
                    topList.forEach((pl, rIdx) => {
                        const medal = rIdx === 0 ? '🥇' : (rIdx === 1 ? '🥈' : (rIdx === 2 ? '🥉' : `#${rIdx + 1}`));
                        const isMe = (myPlayer && pl.user_id === myPlayer.user_id);
                        topHtml += `
                            <div class="flex items-center justify-between p-2 rounded-xl ${isMe ? 'bg-pink-100 border-2 border-pink-500 font-black' : 'bg-white border border-slate-200'} font-mono-code text-xs">
                                <div class="flex items-center gap-2 truncate">
                                    <span class="w-5 text-center font-bold">${medal}</span>
                                    <span class="truncate">${pl.name} ${isMe ? '(Anda)' : ''}</span>
                                </div>
                                <span class="font-bold text-pink-600 ml-2">${(pl.score || 0).toLocaleString()} pts</span>
                            </div>
                        `;
                    });
                    topContainer.innerHTML = topHtml;
                }
                return;
            }

            // Kuis Mode Selesai (Podium Final)
            if (quiz.status === 'finished') {
                if (studentQuizTimerInterval) { clearInterval(studentQuizTimerInterval); studentQuizTimerInterval = null; }
                if (banner) banner.classList.add('hidden');
                if (tabBadge) tabBadge.classList.add('hidden');
                if (quickBadge) quickBadge.classList.add('hidden');

                if (statusPill) {
                    statusPill.textContent = 'PODIUM FINAL';
                    statusPill.className = 'px-2 py-0.5 rounded-full text-[10px] font-mono-code font-black bg-amber-400 text-amber-950 border border-amber-600';
                }

                if (vStandby) vStandby.classList.add('hidden');
                if (vQuestion) vQuestion.classList.add('hidden');
                if (vResult) vResult.classList.add('hidden');
                if (vPodium) vPodium.classList.remove('hidden');

                const pTitle = document.getElementById('student-podium-title');
                if (pTitle) pTitle.textContent = quiz.title || 'Kuis KBM Selesai';

                const podCards = document.getElementById('student-podium-cards');
                if (podCards) {
                    const podList = quiz.podium || [];
                    const p1 = podList[0] || null;
                    const p2 = podList[1] || null;
                    const p3 = podList[2] || null;

                    podCards.innerHTML = `
                        {{-- Juara 2 --}}
                        <div class="flex flex-col items-center">
                            <span class="text-xl mb-1">🥈</span>
                            <div class="w-full p-2 rounded-t-xl border-2 border-black bg-slate-200 text-center font-mono-code" style="box-shadow: 2px 2px 0px #121316; height: 95px;">
                                <div class="text-[8px] font-black text-slate-600">JUARA 2</div>
                                <div class="text-[11px] font-black truncate text-slate-900 mt-0.5">${p2 ? p2.name : '-'}</div>
                                <div class="text-[9px] font-bold text-pink-600 mt-0.5">${p2 ? (p2.score || 0).toLocaleString() : '0'} pts</div>
                                <span class="inline-block mt-1 px-1 rounded bg-amber-200 text-amber-900 text-[7px] font-black">+10 Rep</span>
                            </div>
                        </div>

                        {{-- Juara 1 --}}
                        <div class="flex flex-col items-center -mt-3">
                            <span class="text-2xl mb-1 animate-bounce">🥇</span>
                            <div class="w-full p-2.5 rounded-t-xl border-2 border-black bg-amber-300 text-center font-mono-code" style="box-shadow: 2px 2px 0px #121316; height: 115px;">
                                <div class="text-[9px] font-black text-amber-900">JUARA 1 👑</div>
                                <div class="text-[11px] font-black truncate text-slate-900 mt-0.5">${p1 ? p1.name : '-'}</div>
                                <div class="text-[10px] font-bold text-pink-700 mt-0.5">${p1 ? (p1.score || 0).toLocaleString() : '0'} pts</div>
                                <span class="inline-block mt-1 px-1.5 py-0.2 rounded bg-black text-amber-300 text-[8px] font-black">+15 Rep</span>
                            </div>
                        </div>

                        {{-- Juara 3 --}}
                        <div class="flex flex-col items-center">
                            <span class="text-xl mb-1">🥉</span>
                            <div class="w-full p-2 rounded-t-xl border-2 border-black bg-amber-700/20 text-center font-mono-code" style="box-shadow: 2px 2px 0px #121316; height: 85px;">
                                <div class="text-[8px] font-black text-amber-900">JUARA 3</div>
                                <div class="text-[11px] font-black truncate text-slate-900 mt-0.5">${p3 ? p3.name : '-'}</div>
                                <div class="text-[9px] font-bold text-pink-600 mt-0.5">${p3 ? (p3.score || 0).toLocaleString() : '0'} pts</div>
                                <span class="inline-block mt-1 px-1 rounded bg-amber-200 text-amber-900 text-[7px] font-black">+5 Rep</span>
                            </div>
                        </div>
                    `;
                }

                // My Result Card
                const myResultEl = document.getElementById('student-podium-my-result');
                if (myResultEl) {
                    const myPlayer = quiz.my_player || null;
                    if (myPlayer) {
                        const rank = myPlayer.rank || 0;
                        let bonusText = '';
                        if (rank === 1) bonusText = '🎉 Selamat! Anda Juara 1 dan meraih bonus +15 Poin Reputasi!';
                        else if (rank === 2) bonusText = '🎉 Hebat! Anda Juara 2 dan meraih bonus +10 Poin Reputasi!';
                        else if (rank === 3) bonusText = '🎉 Keren! Anda Juara 3 dan meraih bonus +5 Poin Reputasi!';
                        else bonusText = `Hasil Akhir Anda: Peringkat #${rank} (${(myPlayer.score || 0).toLocaleString()} pts). Terus semangat!`;

                        myResultEl.innerHTML = `
                            <p>${bonusText}</p>
                            <p class="text-[10px] text-slate-500 font-normal mt-1">Poin reputasi pemenang otomatis tersimpan ke profil siswa.</p>
                        `;
                    } else {
                        myResultEl.innerHTML = `<p class="text-slate-500">Kuis selesai. Terima kasih atas partisipasi Anda!</p>`;
                    }
                }
            }
        }

        // 11. PRESENSI KELUAR & LEAVE HANDLER
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
            try {
                const fd = new FormData();
                fd.append('_token', "{{ csrf_token() }}");
                await fetch("{{ route('siswa.lms.meeting.leave', $course->id) }}", {
                    method: 'POST',
                    body: fd
                });
            } catch(e) {
                reportLeave();
            }
            try {
                if (localAudioTrack) { localAudioTrack.stop(); localAudioTrack.close(); }
                if (localVideoTrack) { localVideoTrack.stop(); localVideoTrack.close(); }
                if (agoraClient) await agoraClient.leave();
            } catch(e) {}
            window.location.href = "{{ route('siswa.lms.show', $course->id) }}";
        }

        // 12. RESIZE DRAG HANDLER
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

        // 13. INIT ON PAGE LOAD
        document.addEventListener("DOMContentLoaded", function () {
            initAgoraStudent().catch(err => {
                console.error("Agora student init error:", err);
            });

            setInterval(syncMeetingStudent, 3500);
            syncMeetingStudent();
        });
    </script>
</body>
</html>
