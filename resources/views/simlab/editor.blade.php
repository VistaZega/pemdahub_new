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
        .canvas-grid {
            background-size: 20px 20px;
            background-image: 
                radial-gradient(circle, rgba(255, 255, 255, 0.07) 1px, transparent 1px);
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
            <div id="simStatusBadge" class="hidden sm:flex items-center space-x-1.5 px-2.5 py-1 rounded-full bg-gray-800 border border-gray-700 text-xs text-gray-400">
                <span id="statusIndicatorPin" class="w-2 h-2 rounded-full bg-gray-500"></span>
                <span id="statusText" class="font-mono">SIAP</span>
            </div>

            <button id="btnSaveProject" class="px-4 py-1.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-black text-xs font-bold transition-all flex items-center space-x-1.5 shadow-md">
                <i class="fas fa-save"></i>
                <span>Simpan</span>
            </button>
        </div>
    </header>

    <!-- Main Workspace Container -->
    <div class="flex-grow flex overflow-hidden relative">
        
        <!-- LEFT PANEL: Component Library Palette -->
        <aside class="w-64 bg-gray-900 border-r border-gray-800 flex flex-col shrink-0 z-20">
            <div class="p-3 border-b border-gray-800 flex items-center justify-between bg-gray-950/60">
                <span class="text-xs font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fas fa-boxes text-emerald-400"></i> Komponen SimLab
                </span>
                <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-950 text-emerald-400 font-mono border border-emerald-800">16+ Kit</span>
            </div>

            <!-- Wire Color Palette Bar -->
            <div class="p-2 border-b border-gray-800 bg-gray-900/80 flex items-center justify-between text-xs">
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
                        <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-left transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="hc_sr04">
                            <i class="fas fa-broadcast-tower text-teal-400"></i>
                            <span>HC-SR04 Ultrasonik</span>
                        </button>
                        <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-left transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="dht11">
                            <i class="fas fa-temperature-high text-rose-400"></i>
                            <span>DHT11 Suhu</span>
                        </button>
                        <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-left transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="rc522">
                            <i class="fas fa-id-card text-purple-400"></i>
                            <span>RC522 RFID</span>
                        </button>
                        <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-left transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="tcrt5000">
                            <i class="fas fa-road text-amber-400"></i>
                            <span>TCRT5000 Line</span>
                        </button>
                        <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-left transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="pir">
                            <i class="fas fa-running text-green-400"></i>
                            <span>PIR Gerak</span>
                        </button>
                        <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-left transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="ldr">
                            <i class="fas fa-sun text-yellow-400"></i>
                            <span>LDR Cahaya</span>
                        </button>
                    </div>
                </div>

                <!-- Category: Aktuator & Audio -->
                <div>
                    <h4 class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1">
                        <i class="fas fa-cogs text-amber-400"></i> Motor & Aktuator
                    </h4>
                    <div class="grid grid-cols-2 gap-2">
                        <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-left transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="servo">
                            <i class="fas fa-sync text-indigo-400"></i>
                            <span>Servo SG90</span>
                        </button>
                        <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-left transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="motor_dc">
                            <i class="fas fa-fan text-blue-400"></i>
                            <span>Motor DC</span>
                        </button>
                        <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-left transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="l298n">
                            <i class="fas fa-memory text-emerald-400"></i>
                            <span>Driver L298N</span>
                        </button>
                        <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-left transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="relay">
                            <i class="fas fa-toggle-on text-orange-400"></i>
                            <span>Relay 5V</span>
                        </button>
                        <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-left transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="dfplayer">
                            <i class="fas fa-music text-pink-400"></i>
                            <span>Mini DFPlayer</span>
                        </button>
                        <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-left transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="speaker">
                            <i class="fas fa-volume-up text-cyan-400"></i>
                            <span>Speaker/Buzzer</span>
                        </button>
                    </div>
                </div>

                <!-- Category: Display & LED -->
                <div>
                    <h4 class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1">
                        <i class="fas fa-tv text-purple-400"></i> Display & Indikator
                    </h4>
                    <div class="grid grid-cols-2 gap-2">
                        <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-left transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="lcd1602">
                            <i class="fas fa-desktop text-emerald-400"></i>
                            <span>LCD 16x2 I2C</span>
                        </button>
                        <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-left transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="lcd2004">
                            <i class="fas fa-desktop text-cyan-400"></i>
                            <span>LCD 20x4 I2C</span>
                        </button>
                        <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-left transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="led_red">
                            <i class="fas fa-lightbulb text-red-500"></i>
                            <span>LED Merah</span>
                        </button>
                        <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-left transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="led_green">
                            <i class="fas fa-lightbulb text-emerald-400"></i>
                            <span>LED Hijau</span>
                        </button>
                        <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-left transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="led_yellow">
                            <i class="fas fa-lightbulb text-yellow-400"></i>
                            <span>LED Kuning</span>
                        </button>
                        <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-left transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="led_white">
                            <i class="fas fa-lightbulb text-gray-100"></i>
                            <span>LED Putih</span>
                        </button>
                    </div>
                </div>

                <!-- Category: Komponen Pasif & Catu Daya -->
                <div>
                    <h4 class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1">
                        <i class="fas fa-[#e5e7eb] text-yellow-400"></i> Pasif & Daya
                    </h4>
                    <div class="grid grid-cols-2 gap-2">
                        <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-left transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="resistor">
                            <i class="fas fa-wave-square text-amber-500"></i>
                            <span>Resistor 300Ω</span>
                        </button>
                        <button class="add-comp-btn p-2 rounded-xl bg-gray-800 hover:bg-gray-700 border border-gray-700 text-left transition-all text-xs font-medium text-gray-200 flex flex-col items-center gap-1 text-center" data-type="psu">
                            <i class="fas fa-plug text-red-400"></i>
                            <span>Power Supply 5V/2A</span>
                        </button>
                    </div>
                </div>

            </div>
        </aside>

        <!-- CENTER PANEL: Interactive Circuit SVG Canvas -->
        <main class="flex-grow bg-gray-950 relative overflow-hidden flex flex-col select-none">
            <!-- Canvas Toolbar Controls (Clear Wires, Reset Canvas, Zoom) -->
            <div class="absolute top-3 left-3 z-10 flex items-center space-x-2 bg-gray-900/90 border border-gray-800 backdrop-blur rounded-xl p-1.5 shadow-lg">
                <button id="btnClearWires" class="px-2.5 py-1 rounded-lg bg-gray-800 hover:bg-gray-700 text-xs font-semibold text-red-400 transition-colors flex items-center gap-1" title="Hapus Semua Kabel">
                    <i class="fas fa-trash-alt"></i>
                    <span>Reset Kabel</span>
                </button>
                <button id="btnClearCanvas" class="px-2.5 py-1 rounded-lg bg-gray-800 hover:bg-gray-700 text-xs font-semibold text-gray-300 transition-colors flex items-center gap-1" title="Hapus Semua Komponen">
                    <i class="fas fa-eraser"></i>
                    <span>Kosongkan</span>
                </button>
                <div class="h-4 w-px bg-gray-800"></div>
                <button id="btnZoomIn" class="w-7 h-7 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-300 flex items-center justify-center text-xs" title="Zoom In">
                    <i class="fas fa-search-plus"></i>
                </button>
                <button id="btnZoomOut" class="w-7 h-7 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-300 flex items-center justify-center text-xs" title="Zoom Out">
                    <i class="fas fa-search-minus"></i>
                </button>
            </div>

            <!-- Canvas Viewport -->
            <div id="circuitCanvasContainer" class="w-full h-full canvas-grid overflow-auto relative cursor-crosshair">
                <svg id="circuitSvg" class="w-[3000px] h-[2000px] absolute top-0 left-0">
                    <!-- Dynamic Bezier Wires rendered here -->
                    <g id="wiresGroup"></g>
                    <!-- Temporary wire drawing preview -->
                    <path id="tempWire" d="" stroke="#10b981" stroke-width="3" fill="none" stroke-dasharray="6,6" class="hidden"></path>
                </svg>
                
                <!-- HTML Layer for Draggable Components -->
                <div id="componentsLayer" class="w-[3000px] h-[2000px] absolute top-0 left-0 pointer-events-none"></div>
            </div>

            <!-- Canvas Bottom Helper Info -->
            <div class="absolute bottom-3 left-3 z-10 bg-gray-900/90 border border-gray-800 backdrop-blur rounded-xl px-3 py-1.5 text-[11px] text-gray-400 flex items-center space-x-3 shadow-lg pointer-events-none">
                <span><i class="fas fa-mouse-pointer text-emerald-400 mr-1"></i> Klik Pin & Tarik untuk Sambung Kabel</span>
                <span>•</span>
                <span><i class="fas fa-arrows-alt text-cyan-400 mr-1"></i> Geser Komponen</span>
                <span>•</span>
                <span><i class="fas fa-trash text-red-400 mr-1"></i> Klik Kabel untuk Menghapus</span>
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

    <!-- Pass Project JSON data to Frontend JS -->
    <script>
        window.SimLabConfig = {
            projectId: {{ $project ? $project->id : 'null' }},
            saveUrl: "{{ route('simlab.save') }}",
            compileUrl: "{{ route('simlab.compile') }}",
            initialProject: {!! json_encode($project) !!}
        };
    </script>

    <!-- Load SimLab Component Libraries & Engines -->
    <script src="{{ asset('js/simlab/components.js') }}"></script>
    <script src="{{ asset('js/simlab/circuit.js') }}"></script>
    <script src="{{ asset('js/simlab/engine.js') }}"></script>

</body>
</html>
