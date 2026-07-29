<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PembdaHUB SimLab Workspace - {{ $project ? $project->title : 'Proyek Baru' }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Fira+Code:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CodeMirror Editor -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/theme/dracula.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/clike/clike.min.js"></script>

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0d1117;
            color: #c9d1d9;
            overflow: hidden;
        }
        .CodeMirror {
            font-family: 'Fira Code', monospace;
            height: 100%;
            font-size: 13px;
            background: #0d1117 !important;
        }
        .CodeMirror-gutters {
            background: #161b22 !important;
            border-right: 1px solid #30363d !important;
        }
        .canvas-workspace {
            background-color: #f8fafc;
        }
        .pin-hover:hover {
            filter: drop-shadow(0 0 6px rgba(16, 185, 129, 0.9));
            cursor: pointer;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #161b22;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #30363d;
            border-radius: 3px;
        }
    </style>
</head>
<body class="h-screen w-screen flex flex-col antialiased selection:bg-emerald-500 selection:text-black">

    <!-- Top Workspace Toolbar -->
    <header class="h-14 border-b border-gray-800 bg-gray-900/90 backdrop-blur px-4 flex items-center justify-between z-30 shrink-0">
        <!-- Left: Branding & Project Name -->
        <div class="flex items-center space-x-3">
            <a href="{{ route('simlab.index') }}" class="p-2 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-300 transition-colors" title="Kembali ke Galeri SimLab">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div class="h-6 w-px bg-gray-800"></div>
            
            <div class="flex items-center space-x-2">
                <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-xs font-bold">
                    <i class="fas fa-microchip"></i>
                </div>
                <input type="text" id="projectTitle" value="{{ $project ? $project->title : 'Proyek SimLab Tanpa Judul' }}" class="bg-transparent text-white font-bold text-sm focus:outline-none focus:bg-gray-800/60 px-2 py-1 rounded border border-transparent focus:border-emerald-500/40 w-48 sm:w-64 transition-all" placeholder="Judul Proyek...">
            </div>
        </div>

        <!-- Middle: Board Selector & Controls -->
        <div class="flex items-center space-x-2">
            <div class="flex items-center bg-gray-800/80 rounded-xl p-1 border border-gray-700/60">
                <span class="text-xs text-gray-400 font-semibold px-2">Board:</span>
                <select id="boardTypeSelect" class="bg-gray-900 text-emerald-400 text-xs font-bold rounded-lg px-2 py-1 focus:outline-none border border-gray-700">
                    <option value="uno" {{ ($project->board_type ?? 'uno') == 'uno' ? 'selected' : '' }}>Arduino Uno (ATmega328P)</option>
                    <option value="nano" {{ ($project->board_type ?? '') == 'nano' ? 'selected' : '' }}>Arduino Nano</option>
                    <option value="esp32" {{ ($project->board_type ?? '') == 'esp32' ? 'selected' : '' }}>ESP32 DevKit V1</option>
                </select>
            </div>

            <button id="btnCompile" class="px-3.5 py-1.5 rounded-xl bg-gray-800 hover:bg-gray-700 text-amber-400 text-xs font-bold transition-all border border-amber-500/30 flex items-center space-x-1.5 shadow-sm">
                <i class="fas fa-cogs"></i>
                <span>Cek Sintaks</span>
            </button>

            <button id="btnRunSim" class="px-4 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-black text-xs font-black transition-all flex items-center space-x-1.5 shadow-lg shadow-emerald-500/20">
                <i class="fas fa-play" id="simIcon"></i>
                <span id="simText">Jalankan Simulasi</span>
            </button>
        </div>

        <!-- Right: Actions & Save -->
        <div class="flex items-center space-x-2">
            <button onclick="SimLabEngine.newProject()" class="px-3 py-1.5 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-bold transition-all border border-gray-700 flex items-center space-x-1.5 shadow-sm cursor-pointer" title="Buat Proyek Baru">
                <i class="fas fa-file-alt text-emerald-400"></i>
                <span class="hidden md:inline">Proyek Baru</span>
            </button>

            <button onclick="SimLabEngine.openMyProjectsModal()" class="px-3 py-1.5 rounded-xl bg-gray-800 hover:bg-gray-700 text-amber-300 text-xs font-bold transition-all border border-amber-500/30 flex items-center space-x-1.5 shadow-sm cursor-pointer" title="Daftar Proyek Saya (Load / Edit / Hapus)">
                <i class="fas fa-folder-open text-amber-400"></i>
                <span>Proyek Saya</span>
            </button>

            <div id="simStatusBadge" class="hidden lg:flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-gray-800 border border-gray-700 text-xs text-gray-400">
                <span id="statusIndicatorPin" class="w-2 h-2 rounded-full bg-gray-500"></span>
                <span id="statusText" class="font-mono">SIAP</span>
            </div>

            <button id="btnSaveProject" onclick="SimLabEngine.saveProject()" class="px-4 py-1.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-black text-xs font-black transition-all flex items-center space-x-1.5 shadow-lg shadow-emerald-500/25 ring-1 ring-emerald-400/30 cursor-pointer">
                <i class="fas fa-save"></i>
                <span>Simpan</span>
            </button>
        </div>
    </header>

    <!-- Main Workspace Container -->
    <div class="flex-grow flex overflow-hidden relative">
        
        <!-- LEFT PANEL WRAPPER: Sidebar + Toggle Button -->
        <div class="relative flex shrink-0 z-20">
            <!-- Toggle Button (OUTSIDE sidebar so it stays visible when collapsed) -->
            <button id="btnToggleSidebar" class="absolute -right-5 top-1/2 -translate-y-1/2 z-40 w-5 h-14 bg-gray-800 hover:bg-emerald-700 border border-gray-700 rounded-r-lg flex items-center justify-center text-gray-400 hover:text-white cursor-pointer text-[10px] transition-colors shadow-lg" title="Buka/Tutup Panel">
                <i class="fas fa-chevron-left"></i>
            </button>

            <aside id="leftSidebar" class="w-64 bg-gray-900 border-r border-gray-800 flex flex-col transition-all duration-300 overflow-hidden">

            <!-- Tabs: Komponen / Koneksi -->
            <div class="flex border-b border-gray-800 bg-gray-950/80 shrink-0">
                <button id="tabBtnKomponen" onclick="document.getElementById('tabKomponen').classList.remove('hidden');document.getElementById('tabKoneksi').classList.add('hidden');this.classList.add('text-emerald-400','border-emerald-400');document.getElementById('tabBtnKoneksi').classList.remove('text-emerald-400','border-emerald-400')" class="flex-1 py-2 text-[11px] font-bold text-emerald-400 border-b-2 border-emerald-400 transition-colors flex items-center justify-center gap-1">
                    <i class="fas fa-boxes"></i> Komponen
                </button>
                <button id="tabBtnKoneksi" onclick="document.getElementById('tabKoneksi').classList.remove('hidden');document.getElementById('tabKomponen').classList.add('hidden');this.classList.add('text-emerald-400','border-emerald-400');document.getElementById('tabBtnKomponen').classList.remove('text-emerald-400','border-emerald-400')" class="flex-1 py-2 text-[11px] font-bold text-gray-500 border-b-2 border-transparent transition-colors flex items-center justify-center gap-1">
                    <i class="fas fa-project-diagram"></i> Koneksi
                </button>
            </div>

            <!-- TAB: Komponen -->
            <div id="tabKomponen" class="flex-grow flex flex-col overflow-hidden">
                <!-- Wire Color Palette Bar -->
                <div class="p-2 border-b border-gray-800 bg-gray-900/80 flex items-center justify-between text-xs shrink-0">
                    <span class="text-[11px] text-gray-400 font-medium">Warna Kabel:</span>
                    <div class="flex items-center space-x-1.5">
                        <button class="wire-color-btn w-5 h-5 rounded-full bg-red-500 border-2 border-white ring-2 ring-emerald-400" data-color="#ef4444" title="Merah (VCC/5V)"></button>
                        <button class="wire-color-btn w-5 h-5 rounded-full bg-black border border-gray-600" data-color="#000000" title="Hitam (GND)"></button>
                        <button class="wire-color-btn w-5 h-5 rounded-full bg-emerald-500 border border-gray-600" data-color="#10b981" title="Hijau (Signal)"></button>
                        <button class="wire-color-btn w-5 h-5 rounded-full bg-cyan-400 border border-gray-600" data-color="#22d3ee" title="Biru (SDA/Tx)"></button>
                        <button class="wire-color-btn w-5 h-5 rounded-full bg-amber-400 border border-gray-600" data-color="#fbbf24" title="Kuning (SCL/Rx)"></button>
                    </div>
                </div>

                <!-- Component List Scrollable -->
                <div class="flex-grow overflow-y-auto p-3 space-y-4 custom-scrollbar">
                    
                    <!-- Category: Board Mikrokontroler -->
                    <div>
                        <h4 class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1">
                            <i class="fas fa-microchip text-cyan-400"></i> Board Utama
                        </h4>
                        <div class="grid grid-cols-2 gap-2">
                            <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-left transition-all text-xs font-semibold text-white flex flex-col items-center justify-center gap-1 group" data-type="uno">
                                <i class="fas fa-microchip text-lg text-cyan-400 group-hover:scale-110 transition-transform"></i>
                                <span>Arduino Uno</span>
                            </button>
                            <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-left transition-all text-xs font-semibold text-white flex flex-col items-center justify-center gap-1 group" data-type="nano">
                                <i class="fas fa-microchip text-lg text-emerald-400 group-hover:scale-110 transition-transform"></i>
                                <span>Arduino Nano</span>
                            </button>
                            <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-left transition-all text-xs font-semibold text-white flex flex-col items-center justify-center gap-1 col-span-2 group" data-type="esp32">
                                <i class="fas fa-wifi text-lg text-amber-400 group-hover:scale-110 transition-transform"></i>
                                <span>ESP32 DevKit V1</span>
                            </button>
                        </div>
                    </div>

                    <!-- Category: Sensor & Input -->
                    <div>
                        <h4 class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1">
                            <i class="fas fa-eye text-emerald-400"></i> Sensor & Input
                        </h4>
                        <div class="grid grid-cols-2 gap-2">
                            <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="hc_sr04">
                                <i class="fas fa-broadcast-tower text-teal-400"></i> <span>HC-SR04</span>
                            </button>
                            <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="dht11">
                                <i class="fas fa-temperature-high text-rose-400"></i> <span>DHT11 Suhu</span>
                            </button>
                            <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="rc522">
                                <i class="fas fa-id-card text-purple-400"></i> <span>RC522 RFID</span>
                            </button>
                            <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="tcrt5000">
                                <i class="fas fa-road text-amber-400"></i> <span>TCRT5000</span>
                            </button>
                            <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="pir">
                                <i class="fas fa-running text-green-400"></i> <span>PIR Gerak</span>
                            </button>
                            <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="ldr">
                                <i class="fas fa-sun text-yellow-400"></i> <span>LDR Cahaya</span>
                            </button>
                        </div>
                    </div>

                    <!-- Category: Motor & Aktuator -->
                    <div>
                        <h4 class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1">
                            <i class="fas fa-cogs text-amber-400"></i> Motor & Aktuator
                        </h4>
                        <div class="grid grid-cols-2 gap-2">
                            <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="servo">
                                <i class="fas fa-sync text-indigo-400"></i> <span>Servo SG90</span>
                            </button>
                            <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="motor_dc">
                                <i class="fas fa-fan text-blue-400"></i> <span>Motor DC</span>
                            </button>
                            <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="l298n">
                                <i class="fas fa-memory text-emerald-400"></i> <span>L298N</span>
                            </button>
                            <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="relay">
                                <i class="fas fa-toggle-on text-orange-400"></i> <span>Relay 5V</span>
                            </button>
                            <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="dfplayer">
                                <i class="fas fa-music text-pink-400"></i> <span>DFPlayer</span>
                            </button>
                            <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="speaker">
                                <i class="fas fa-volume-up text-cyan-400"></i> <span>Buzzer</span>
                            </button>
                        </div>
                    </div>

                    <!-- Category: Display & LED -->
                    <div>
                        <h4 class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1">
                            <i class="fas fa-tv text-purple-400"></i> Display & LED
                        </h4>
                        <div class="grid grid-cols-2 gap-2">
                            <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="lcd1602">
                                <i class="fas fa-desktop text-emerald-400"></i> <span>LCD 16x2</span>
                            </button>
                            <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="lcd2004">
                                <i class="fas fa-desktop text-cyan-400"></i> <span>LCD 20x4</span>
                            </button>
                            <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="led_red">
                                <i class="fas fa-lightbulb text-red-500"></i> <span>LED Merah</span>
                            </button>
                            <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="led_green">
                                <i class="fas fa-lightbulb text-emerald-400"></i> <span>LED Hijau</span>
                            </button>
                            <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="led_yellow">
                                <i class="fas fa-lightbulb text-yellow-400"></i> <span>LED Kuning</span>
                            </button>
                            <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="led_white">
                                <i class="fas fa-lightbulb text-gray-100"></i> <span>LED Putih</span>
                            </button>
                        </div>
                    </div>

                    <!-- Category: Pasif & Daya -->
                    <div>
                        <h4 class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1">
                            <i class="fas fa-bolt text-yellow-400"></i> Pasif & Daya
                        </h4>
                        <div class="grid grid-cols-2 gap-2">
                            <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="resistor">
                                <i class="fas fa-wave-square text-amber-500"></i> <span>Resistor</span>
                            </button>
                            <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="psu">
                                <i class="fas fa-plug text-red-400"></i> <span>PSU 5V/2A</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <!-- TAB: Koneksi (Hidden by default) -->
            <div id="tabKoneksi" class="hidden flex-grow flex flex-col overflow-hidden">
                <div class="p-2 border-b border-gray-800 bg-gray-900/80 flex items-center justify-between text-xs shrink-0">
                    <span class="text-[11px] text-gray-400 font-medium flex items-center gap-1">
                        <i class="fas fa-project-diagram text-blue-400"></i> Daftar Sambungan Kabel
                    </span>
                    <span id="connPanelCount" class="text-[10px] px-1.5 py-0.5 rounded bg-blue-900/50 text-blue-300 font-mono border border-blue-800">0 kabel</span>
                </div>
                <div class="flex-grow overflow-y-auto custom-scrollbar">
                    <table class="w-full text-[10px]">
                        <thead class="text-gray-500 uppercase sticky top-0 bg-gray-900">
                            <tr>
                                <th class="px-2 py-1.5 text-left">#</th>
                                <th class="px-2 py-1.5 text-left">Dari</th>
                                <th class="px-2 py-1.5 text-left">Pin</th>
                                <th class="px-2 py-1.5 text-left">Ke</th>
                                <th class="px-2 py-1.5 text-left">Pin</th>
                            </tr>
                        </thead>
                        <tbody id="connPanelBody">
                            <tr><td colspan="5" class="text-center text-gray-500 py-4 text-[10px]">Belum ada koneksi kabel</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </aside>
        </div><!-- end LEFT PANEL WRAPPER -->

        <!-- CENTER PANEL: Interactive Circuit SVG Canvas -->
        <main class="flex-grow bg-gray-950 relative overflow-hidden flex flex-col select-none">
            <!-- Canvas Toolbar Controls (Clear Wires, Reset Canvas, Zoom, Cable Mode) -->
            <div class="absolute top-3 left-3 z-30 flex items-center space-x-1.5 bg-white/95 border border-gray-300 backdrop-blur rounded-xl p-1.5 shadow-lg">
                <button id="btnToggleWireStyle" class="px-2 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-xs font-semibold text-emerald-600 transition-colors flex items-center gap-1" title="Ganti Gaya Kabel">
                    <i class="fas fa-ruler-combined"></i>
                    <span id="wireStyleLabel">Kabel: Lurus 90°</span>
                </button>
                <div class="h-4 w-px bg-gray-300"></div>
                <button id="btnClearWires" class="px-2 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-xs font-semibold text-red-500 transition-colors flex items-center gap-1" title="Hapus Kabel">
                    <i class="fas fa-trash-alt"></i> <span>Reset</span>
                </button>
                <button id="btnClearCanvas" class="px-2 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-xs font-semibold text-gray-600 transition-colors flex items-center gap-1" title="Kosongkan">
                    <i class="fas fa-eraser"></i>
                </button>
                <div class="h-4 w-px bg-gray-300"></div>
                <button id="btnZoomIn" class="w-7 h-7 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center text-xs" title="Zoom In"><i class="fas fa-search-plus"></i></button>
                <button id="btnZoomOut" class="w-7 h-7 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center text-xs" title="Zoom Out"><i class="fas fa-search-minus"></i></button>
                <div class="h-4 w-px bg-gray-300"></div>
                <!-- Grid Controls -->
                <select id="gridSizeSelect" class="bg-gray-100 text-gray-700 text-[10px] font-mono rounded border border-gray-300 px-1.5 py-1 focus:outline-none" title="Ukuran Grid">
                    <option value="10">Grid 10px</option>
                    <option value="20" selected>Grid 20px</option>
                    <option value="40">Grid 40px</option>
                    <option value="60">Grid 60px</option>
                </select>
                <button id="btnToggleGrid" class="px-2 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-xs font-semibold text-emerald-600 transition-colors flex items-center gap-1" title="Tampilkan/Sembunyikan Grid">
                    <i class="fas fa-th"></i>
                </button>
                <button id="btnToggleSnap" class="px-2 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-xs font-semibold text-emerald-600 transition-colors flex items-center gap-1" title="Snap ke Grid">
                    <i class="fas fa-magnet"></i> <span>Snap: ON</span>
                </button>

            </div>

            <!-- Canvas Viewport -->
            <div id="circuitCanvasContainer" class="w-full h-full canvas-workspace overflow-auto relative cursor-crosshair">
                <!-- SVG Layer for Grid + Wires -->
                <svg id="circuitSvg" class="w-[3000px] h-[2000px] absolute top-0 left-0 pointer-events-none z-20">
                    <!-- Grid Lines (rendered by circuit.js) -->
                    <g id="gridGroup"></g>
                    <!-- Dynamic Wires -->
                    <g id="wiresGroup"></g>
                    <!-- Temporary wire preview -->
                    <path id="tempWire" d="" stroke="#10b981" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="6,6" class="hidden"></path>
                </svg>

                <!-- HTML Layer for Draggable Components -->
                <div id="componentsLayer" class="w-[3000px] h-[2000px] absolute top-0 left-0 pointer-events-auto z-10"></div>
            </div>



            <!-- Canvas Bottom Helper Info -->
            <div class="absolute bottom-3 left-3 z-10 bg-white/90 border border-gray-300 backdrop-blur rounded-xl px-3 py-1.5 text-[11px] text-gray-500 flex items-center space-x-3 shadow-lg pointer-events-none">
                <span><i class="fas fa-mouse-pointer text-emerald-500 mr-1"></i> Klik Pin → Sambung Kabel</span>
                <span>•</span>
                <span><i class="fas fa-arrows-alt text-cyan-500 mr-1"></i> Geser Komponen</span>
                <span>•</span>
                <span><i class="fas fa-hand-pointer text-amber-500 mr-1"></i> Klik Kanan Kabel → Menu</span>
                <span>•</span>
                <span><i class="fas fa-edit text-blue-500 mr-1"></i> Double-Klik Nama → Edit</span>
            </div>
        </main>

        <!-- RIGHT PANEL: Code Editor & Serial Monitor -->
        <aside class="w-96 bg-gray-900 border-l border-gray-800 flex flex-col shrink-0 z-20">
            <!-- Tabs Bar -->
            <div class="flex border-b border-gray-800 bg-gray-950/80">
                <button id="tabBtnCode" class="flex-1 py-2.5 text-xs font-bold text-emerald-400 border-b-2 border-emerald-400 bg-gray-900 transition-colors flex items-center justify-center gap-1.5">
                    <i class="fas fa-code"></i> Kode Arduino (.ino)
                </button>
                <button id="tabBtnSerial" class="flex-1 py-2.5 text-xs font-bold text-gray-400 hover:text-white border-b-2 border-transparent transition-colors flex items-center justify-center gap-1.5">
                    <i class="fas fa-terminal"></i> Serial Monitor
                </button>
            </div>

            <!-- Code Editor Container -->
            <div id="codeTabContent" class="flex-grow flex flex-col overflow-hidden">
                <!-- Code Template Quick Loader & Auto-Code Toggle -->
                <div class="p-2 border-b border-gray-800 bg-gray-950 flex items-center justify-between text-xs shrink-0 gap-2">
                    <button id="btnToggleAutoCode" onclick="SimLabEngine.toggleAutoCode()" class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold hover:bg-emerald-500/30 transition-colors cursor-pointer" title="Auto-Code Pintar: Generasi/Hapus kode otomatis sesuai komponen aktif">
                        <i class="fas fa-magic text-amber-400 mr-1"></i> Auto-Code: ON
                    </button>
                    <select id="codeTemplateSelect" onchange="SimLabEngine.loadProjectTemplate(this.value)" class="bg-gray-900 text-emerald-400 text-xs font-bold rounded-lg border border-gray-700 px-2 py-1 focus:outline-none focus:border-emerald-500 cursor-pointer transition-colors max-w-[240px] truncate" title="Pilih 10 Template Proyek Edukatif Siap Pakai">
                        <option value="">-- 10 Template Proyek Edukatif --</option>
                        <option value="blink">01. Lampu LED Blink</option>
                        <option value="traffic_light">02. Lampu Lalu Lintas 3 Warna</option>
                        <option value="dc_motor">03. Kipas & Kecepatan Motor DC</option>
                        <option value="rfid_door">04. Smart Door Access RFID & Motor DC</option>
                        <option value="pir_alarm">05. Alarm Deteksi Gerakan PIR & Buzzer</option>
                        <option value="ultrasonic_gate">06. Palang Otomatis HC-SR04 & Servo</option>
                        <option value="smart_lamp_ldr">07. Lampu Jalan Otomatis LDR & Relay</option>
                        <option value="cooling_fan_dht">08. Pendingin Suhu Ruangan DHT11</option>
                        <option value="line_follower">09. Robot Line Follower TCRT5000</option>
                        <option value="smart_home_iot">10. Smart Home Automation Terpadu IoT</option>
                    </select>
                </div>

                <div class="flex-grow relative">
                    <textarea id="codeEditorArea" class="hidden">
// PembdaHUB SimLab - Kode Utama Arduino
// Papan: Arduino Uno (ATmega328P)

void setup() {
  pinMode(13, OUTPUT);
  Serial.begin(9600);
  Serial.println("PembdaHUB SimLab Berhasil Dimulai!");
}

void loop() {
  digitalWrite(13, HIGH);
  delay(1000);
  digitalWrite(13, LOW);
  delay(1000);
}
                    </textarea>
                </div>
                
                <!-- Compilation Output Log Footer -->
                <div id="compilerLogContainer" class="h-28 border-t border-gray-800 bg-gray-950 p-2.5 font-mono text-[11px] overflow-y-auto custom-scrollbar">
                    <div class="text-gray-500 font-bold mb-1 flex items-center justify-between">
                        <span><i class="fas fa-terminal mr-1 text-amber-400"></i> Compiler & Log Output:</span>
                        <span id="compilerStatusLabel" class="text-emerald-400">Ready</span>
                    </div>
                    <pre id="compilerLogText" class="text-emerald-400 whitespace-pre-wrap">Sistem Siap. Klik 'Jalankan Simulasi' untuk memulai.</pre>
                </div>
            </div>

            <!-- Serial Monitor Container (Hidden by Default) -->
            <div id="serialTabContent" class="hidden flex-grow flex flex-col overflow-hidden bg-gray-950">
                <div class="p-2 border-b border-gray-800 flex items-center justify-between bg-gray-900 text-xs">
                    <div class="flex items-center space-x-2">
                        <span class="text-gray-400 font-medium">Baud:</span>
                        <select id="serialBaudRate" class="bg-gray-950 text-emerald-400 font-mono text-xs rounded border border-gray-700 px-2 py-0.5 focus:outline-none">
                            <option value="9600" selected>9600 baud</option>
                            <option value="115200">115200 baud</option>
                        </select>
                    </div>
                    <button id="btnClearSerial" class="px-2 py-0.5 rounded bg-gray-800 hover:bg-gray-700 text-gray-300 text-xs transition-colors">
                        Clear
                    </button>
                </div>
                
                <!-- Serial Output Area -->
                <div id="serialOutputText" class="flex-grow p-3 font-mono text-xs text-emerald-400 overflow-y-auto custom-scrollbar whitespace-pre-wrap bg-black/50">
========================================
PembdaHUB SimLab Serial Console Connected
========================================
                </div>

                <!-- Serial Input Field -->
                <div class="p-2 border-t border-gray-800 bg-gray-900 flex space-x-2">
                    <input type="text" id="serialInputText" class="flex-grow bg-gray-950 border border-gray-700 text-white font-mono text-xs rounded-lg px-2.5 py-1.5 focus:outline-none focus:border-emerald-500" placeholder="Kirim perintah Serial..." />
                    <button id="btnSendSerial" class="px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-black text-xs font-bold">
                        Kirim
                    </button>
                </div>
            </div>

        </aside>
    </div>

    <!-- MODAL: DAFTAR PROYEK SAYA (LOAD, EDIT, HAPUS) -->
    <div id="modalMyProjects" class="fixed inset-0 z-50 hidden bg-black/75 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[85vh]">
            <!-- Modal Header -->
            <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between bg-gray-950">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center font-bold text-lg">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Daftar Proyek Saya</h3>
                        <p class="text-xs text-gray-400">Pilih proyek tersimpan untuk dimuat ke workspace atau dikelola</p>
                    </div>
                </div>
                <button onclick="SimLabEngine.closeMyProjectsModal()" class="text-gray-400 hover:text-white p-1.5 rounded-lg hover:bg-gray-800 text-lg transition-colors">✕</button>
            </div>

            <!-- Modal Content (Project List) -->
            <div id="myProjectsListContainer" class="p-5 overflow-y-auto space-y-3 custom-scrollbar flex-grow min-h-[260px]">
                <div class="text-center text-gray-500 py-8">
                    <i class="fas fa-spinner fa-spin text-2xl text-emerald-400 mb-2"></i>
                    <p class="text-xs">Memuat daftar proyek...</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-5 py-3 border-t border-gray-800 bg-gray-950 flex items-center justify-between text-xs">
                <span class="text-gray-500">Bisa memuat rangkaian SVG & kode C++ secara otomatis</span>
                <button onclick="SimLabEngine.closeMyProjectsModal()" class="px-4 py-1.5 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-300 font-bold transition-colors">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Pass Project JSON data to Frontend JS -->
    <script>
        window.SimLabConfig = {
            projectId: {{ $project ? $project->id : 'null' }},
            saveUrl: "{{ route('simlab.save') }}",
            compileUrl: "{{ route('simlab.compile') }}",
            initialProject: {!! json_encode($project) !!}
        };
    </script>

    <!-- Load SimLab Component Libraries & Engines (Cache-Busted) -->
    @php $jsVer = config('app.asset_version', time()); @endphp
    <script src="{{ asset('js/simlab/components.js') }}?v={{ $jsVer }}"></script>
    <script src="{{ asset('js/simlab/circuit.js') }}?v={{ $jsVer }}"></script>
    <script src="{{ asset('js/simlab/engine.js') }}?v={{ $jsVer }}"></script>

</body>
</html>
