<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tatap Muka Virtual: {{ $course->course_name ?? $course->name }} - PembdaHUB</title>
    
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
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-500/15 border border-rose-500/30 text-rose-400 uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span> Live Kelas
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
        
        {{-- Conference Stage (Video Call Jitsi) --}}
        <main id="conference-panel" class="flex-1 relative bg-slate-950 flex flex-col h-full overflow-hidden transition-all duration-150">
            
            {{-- Video Container --}}
            <div id="meet" class="w-full flex-1 relative bg-slate-950"></div>

            {{-- Floating Teacher Action Bar (Indonesian Language & Friendly) --}}
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2 bg-slate-900/90 backdrop-blur-md border border-slate-700/80 px-3 py-2 rounded-2xl shadow-2xl">
                
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
                <h3 class="text-base font-bold text-white">Akhiri Sesi Tatap Muka?</h3>
                <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                    Sesi kelas live untuk mata pelajaran <strong>{{ $course->course_name ?? $course->name }}</strong> akan ditutup. Seluruh siswa akan diarahkan kembali ke beranda modul dan total durasi kehadiran siswa otomatis tersimpan di rekap presensi.
                </p>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2">
                <button onclick="closeEndMeetingModal()" type="button" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 transition-colors">
                    Batal
                </button>
                <form action="{{ route('guru.lms.meeting.stop', $course->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 transition-colors shadow-lg shadow-rose-900/30 flex items-center gap-1.5">
                        <i class="fas fa-check"></i> Ya, Akhiri Kelas
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- 5. TOAST NOTIFICATION --}}
    <div id="copy-toast" class="fixed bottom-6 right-6 z-50 bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow-xl border border-indigo-400 flex items-center gap-2 transform translate-y-12 opacity-0 transition-all duration-300 pointer-events-none">
        <i class="fas fa-circle-check text-emerald-300 text-sm"></i>
        <span>Tautan kelas berhasil disalin! Bagikan ke siswa via WhatsApp.</span>
    </div>

    {{-- JITSI MEET EXTERNAL API SDK --}}
    <script src="https://{{ config('services.jitsi.domain', 'meet.jit.si') }}/external_api.js"></script>

    <script>
        let jitsiApi = null;
        let meetingStartTime = new Date("{{ $course->meeting_started_at ? $course->meeting_started_at->toISOString() : now()->toISOString() }}");

        // 1. LIVE TIMER KALKULASI
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

        // 2. TOGGLE & SWITCH TAB CLASSROOM HUB
        function toggleClassroomHub() {
            const hub = document.getElementById('classroom-hub');
            const handler = document.getElementById('resize-handler');
            if (hub.classList.contains('hidden')) {
                hub.classList.remove('hidden');
                hub.classList.add('flex');
                if (handler) handler.classList.remove('hidden');
            } else {
                hub.classList.add('hidden');
                hub.classList.remove('flex');
                if (handler) handler.classList.add('hidden');
            }
        }

        function switchTab(tabName) {
            // Pastikan panel terbuka jika sedang tersembunyi
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

        // 3. MATERIAL PREVIEW VIEWER
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

        // 4. DIGITAL WHITEBOARD LOGIC (HTML5 CANVAS)
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
            
            // Simpan gambar sebelum resize
            let tempImage = null;
            if (canvas.width > 0 && canvas.height > 0) {
                tempImage = ctx.getImageData(0, 0, canvas.width, canvas.height);
            }

            canvas.width = container.offsetWidth;
            canvas.height = container.offsetHeight;

            // Pulihkan gambar jika ada
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
                    penColor = '#0f172a'; // Ganti ke hitam otomatis jika background putih
                }
            }
        }

        function downloadWhiteboard() {
            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = canvas.width;
            tempCanvas.height = canvas.height;
            const tempCtx = tempCanvas.getContext('2d');
            
            // Background fill
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

        // Pointer event listeners for Whiteboard drawing
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

        // 5. JITSI MEET INITIALIZATION
        document.addEventListener("DOMContentLoaded", function () {
            const domain = "{{ config('services.jitsi.domain', 'meet.jit.si') }}";
            const options = {
                roomName: "{{ $roomName }}",
                width: "100%",
                height: "100%",
                parentNode: document.querySelector('#meet'),
                userInfo: {
                    displayName: "{{ $displayName }}"
                },
                configOverwrite: {
                    startWithAudioMuted: false,
                    startWithVideoMuted: false,
                    enableWelcomePage: false,
                    prejoinPageEnabled: false,
                    disableDeepLinking: true
                },
                interfaceConfigOverwrite: {
                    TOOLBAR_BUTTONS: [
                        'microphone', 'camera', 'desktop', 'fullscreen',
                        'chat', 'recording', 'livestreaming', 'sharedvideo',
                        'raisehand', 'videoquality', 'tileview', 'mute-everyone',
                        'settings', 'hangup'
                    ],
                    SETTINGS_SECTIONS: ['devices', 'language', 'profile']
                }
            };

            jitsiApi = new JitsiMeetExternalAPI(domain, options);

            const iframe = jitsiApi.getIFrame();
            if (iframe) {
                iframe.setAttribute('allow', 'camera *; microphone *; display-capture *; autoplay *; clipboard-write *');
            }

            // Tangkap event saat guru menutup meeting dari tombol bawaan Jitsi
            jitsiApi.addEventListener('videoConferenceLeft', function() {
                openEndMeetingModal();
            });

            // Polling Peserta Hadir (Attendance)
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
                    })
                    .catch(err => console.error("Error fetching attendees:", err));
            }

            setInterval(fetchAttendees, 12000);
            fetchAttendees();
        });

        // 6. KONTROL PEDAGOGIS GURU
        function teacherMuteEveryone() {
            if (jitsiApi) {
                jitsiApi.executeCommand('muteEveryone');
                alert('Seluruh mikrofon siswa berhasil dinonaktifkan (Mute All).');
            }
        }

        // 7. COPY MEETING LINK & TOAST
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

        // 8. MODAL AKHIRI KELAS
        function openEndMeetingModal() {
            document.getElementById('end-meeting-modal').classList.remove('hidden');
        }
        function closeEndMeetingModal() {
            document.getElementById('end-meeting-modal').classList.add('hidden');
        }

        // 9. RESIZE DRAG HANDLER LOGIC
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
    </script>
</body>
</html>
