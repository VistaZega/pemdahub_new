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
            
            {{-- Video Arena Layout --}}
            <div class="flex-1 flex flex-col overflow-hidden p-3 gap-3 relative">
                
                {{-- Layar Utama: Video Guru / Screen Share Guru --}}
                <div class="flex-1 bg-slate-900 rounded-2xl overflow-hidden border border-slate-800 relative flex items-center justify-center shadow-inner">
                    <div id="agora-teacher-video" class="w-full h-full relative">
                        {{-- Placeholder sebelum guru connect / jika guru belum publish video --}}
                        <div id="teacher-waiting-placeholder" class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 p-6 text-center">
                            <div class="w-16 h-16 rounded-2xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 mb-3 animate-pulse">
                                <i class="fas fa-chalkboard-user text-2xl"></i>
                            </div>
                            <h3 class="text-base font-bold text-white">Menghubungkan ke Ruang PembdaTEMU...</h3>
                            <p class="text-xs text-slate-400 mt-1 max-w-sm">Guru sedang menyiapkan siaran. Suara dan video guru akan otomatis muncul saat guru mulai mengajar.</p>
                            <span class="mt-3 inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span> Channel: {{ $agoraChannel }}
                            </span>
                        </div>
                    </div>

                    {{-- Badge Status Siaran Guru --}}
                    <div class="absolute top-3 left-3 z-10 flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-bold bg-slate-900/90 border border-slate-700 text-slate-200 backdrop-blur-md">
                            <i class="fas fa-chalkboard-user text-indigo-400"></i>
                            <span>{{ $course->teacher->user->name ?? 'Guru Pengajar' }}</span>
                        </span>
                        <span id="teacher-audio-indicator" class="inline-flex items-center gap-1 px-2 py-1 rounded-xl text-[10px] font-bold bg-slate-900/90 border border-slate-700 text-slate-400 backdrop-blur-md">
                            <i class="fas fa-volume-high text-emerald-400"></i> Audio Aktif
                        </span>
                    </div>

                    {{-- PiP Video Siswa (Kamera Sendiri) di Pojok Bawah Kanan --}}
                    <div id="student-pip-container" class="absolute bottom-20 sm:bottom-4 right-4 w-36 sm:w-48 aspect-video bg-slate-900/95 rounded-xl overflow-hidden border border-slate-700 shadow-2xl z-20">
                        <div id="agora-student-video" class="w-full h-full relative">
                            <div id="student-cam-off-placeholder" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-900 text-slate-500 text-[10px]">
                                <i class="fas fa-video-slash text-base mb-1"></i>
                                <span>Kamera Nonaktif</span>
                            </div>
                        </div>
                        <div class="absolute bottom-1 left-1.5 z-10 text-[9px] font-bold text-white bg-slate-900/80 px-1.5 py-0.5 rounded border border-slate-700">
                            Saya ({{ explode(' ', $displayName)[0] }})
                        </div>
                    </div>
                </div>

                {{-- Baris Thumbnail Siswa Lain --}}
                <div id="agora-peers-container" class="h-20 bg-slate-900/60 rounded-xl border border-slate-800/80 p-2 flex items-center gap-2 overflow-x-auto flex-shrink-0">
                    <div id="no-peers-hint" class="text-[11px] text-slate-500 px-3 flex items-center gap-2">
                        <i class="fas fa-users text-slate-600"></i>
                        <span>Siswa lain yang mengaktifkan kamera akan muncul di sini</span>
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

        // 1. INISIALISASI AGORA CLIENT SISWA
        async function initAgoraStudent() {
            if (!AGORA_APP_ID) {
                console.error("Agora App ID kosong.");
                return;
            }

            // Inisialisasi RTC Client Agora
            agoraClient = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' });

            // Remote User Published (Guru atau Teman publish stream)
            agoraClient.on('user-published', async (user, mediaType) => {
                await agoraClient.subscribe(user, mediaType);

                if (mediaType === 'video') {
                    // Cek apakah yang publish adalah Guru
                    if (user.uid === TEACHER_USER_ID || isTeacherTrack(user)) {
                        const teacherContainer = document.getElementById('agora-teacher-video');
                        const placeholder = document.getElementById('teacher-waiting-placeholder');
                        if (placeholder) placeholder.style.display = 'none';
                        user.videoTrack.play(teacherContainer);
                    } else {
                        // Siswa lain (Classmate)
                        const peerContainer = document.getElementById(`peer-${user.uid}`) || createPeerContainer(user.uid);
                        user.videoTrack.play(peerContainer);
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
                if (user.uid === TEACHER_USER_ID && mediaType === 'video') {
                    const placeholder = document.getElementById('teacher-waiting-placeholder');
                    if (placeholder) placeholder.style.display = 'flex';
                }
                if (mediaType === 'video') {
                    const el = document.getElementById(`peer-${user.uid}`);
                    if (el) el.remove();
                    checkPeersEmpty();
                }
            });

            // Remote User Left
            agoraClient.on('user-left', (user) => {
                const el = document.getElementById(`peer-${user.uid}`);
                if (el) el.remove();
                checkPeersEmpty();
            });

            // Join Agora Channel
            await agoraClient.join(AGORA_APP_ID, AGORA_CHANNEL, AGORA_TOKEN || null, AGORA_UID);
            console.log("Siswa berhasil join Agora room:", AGORA_CHANNEL);
        }

        function isTeacherTrack(user) {
            // Jika TEACHER_USER_ID cocok atau tidak ada ID lain
            return TEACHER_USER_ID > 0 && user.uid === TEACHER_USER_ID;
        }

        function createPeerContainer(uid) {
            const hint = document.getElementById('no-peers-hint');
            if (hint) hint.style.display = 'none';

            const container = document.getElementById('agora-peers-container');
            const div = document.createElement('div');
            div.id = `peer-${uid}`;
            div.className = 'h-full aspect-video bg-slate-800 rounded-lg overflow-hidden border border-slate-700 flex-shrink-0 relative';
            div.innerHTML = `<span class="absolute bottom-0.5 left-1 z-10 text-[8px] font-bold text-white bg-slate-900/80 px-1 rounded">Siswa #${uid}</span>`;
            container.appendChild(div);
            return div;
        }

        function checkPeersEmpty() {
            const container = document.getElementById('agora-peers-container');
            const peers = container.querySelectorAll('[id^="peer-"]');
            const hint = document.getElementById('no-peers-hint');
            if (peers.length === 0 && hint) {
                hint.style.display = 'flex';
            }
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
            } else {
                hub.classList.add('hidden');
                hub.classList.remove('flex');
                if (handler) handler.classList.add('hidden');
            }
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
