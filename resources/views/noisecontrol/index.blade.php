<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pusat Kontrol Kebisingan Kelas IoT (Noise Control) - Perguruan PEMBDA</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0b1120;
            color: #f1f5f9;
        }

        .font-mono-code {
            font-family: 'JetBrains Mono', monospace;
        }

        .font-tech {
            font-family: 'Chakra Petch', sans-serif;
        }

        /* LED Lamp Glow Effects */
        .led-indicator {
            transition: all 0.25s ease-in-out;
        }

        .led-green-off {
            background-color: #064e3b;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.6);
            border: 2px solid #047857;
            opacity: 0.35;
        }
        .led-green-on {
            background-color: #10b981;
            box-shadow: 0 0 20px #10b981, 0 0 40px #10b981, inset 0 0 8px #d1fae5;
            border: 2px solid #a7f3d0;
            opacity: 1;
        }

        .led-yellow-off {
            background-color: #713f12;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.6);
            border: 2px solid #b45309;
            opacity: 0.35;
        }
        .led-yellow-on {
            background-color: #f59e0b;
            box-shadow: 0 0 20px #f59e0b, 0 0 40px #f59e0b, inset 0 0 8px #fef3c7;
            border: 2px solid #fde68a;
            opacity: 1;
        }

        .led-red-off {
            background-color: #7f1d1d;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.6);
            border: 2px solid #b91c1c;
            opacity: 0.35;
        }
        .led-red-on {
            background-color: #ef4444;
            box-shadow: 0 0 25px #ef4444, 0 0 50px #ef4444, inset 0 0 10px #fee2e2;
            border: 2px solid #fca5a5;
            opacity: 1;
            animation: pulse-red-alert 0.8s infinite alternate;
        }

        @keyframes pulse-red-alert {
            from { box-shadow: 0 0 15px #ef4444, 0 0 30px #ef4444; }
            to { box-shadow: 0 0 30px #ef4444, 0 0 60px #f87171, 0 0 80px rgba(239,68,68,0.8); }
        }

        /* Glassmorphism Panels */
        .glass-panel {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(51, 65, 85, 0.6);
        }

        .glass-card {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(71, 85, 105, 0.4);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0f172a;
        }
        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-[#0b1120] text-slate-100">

    <!-- Top Navigation Bar -->
    <header class="glass-panel border-b border-slate-800 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-rose-600 via-amber-500 to-emerald-500 p-0.5 shadow-lg shadow-emerald-500/20">
                    <div class="w-full h-full bg-[#0f172a] rounded-[10px] flex items-center justify-center">
                        <i class="fa-solid fa-waveform-lines text-emerald-400 text-lg"></i>
                    </div>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-tech font-bold text-lg tracking-wider text-white">NOISECONTROL</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono-code font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">ESP32 + INMP441</span>
                    </div>
                    <p class="text-xs text-slate-400">Monitoring & Pengendali Kebisingan Kelas Otomatis Perguruan PEMBDA</p>
                </div>
            </div>

            <!-- Quick Info & Actions -->
            <div class="flex items-center gap-3">
                <!-- Device Picker -->
                @if($devices->count() > 0)
                <div class="relative hidden sm:block">
                    <select id="deviceSelector" onchange="window.location.href = '?device_id=' + this.value"
                        class="bg-slate-900 text-slate-200 text-xs rounded-lg px-3 py-2 border border-slate-700 focus:outline-none focus:border-emerald-500 font-mono-code">
                        @foreach($devices as $dev)
                            <option value="{{ $dev->id }}" {{ $activeDevice && $activeDevice->id === $dev->id ? 'selected' : '' }}>
                                {{ $dev->device_uid }} - {{ $dev->classroom ? $dev->classroom->class_name : $dev->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Server Live Pulse -->
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-xs">
                    <span class="relative flex h-2 w-2">
                        <span id="livePulsePing" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span id="livePulseDot" class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span id="livePulseText" class="text-slate-300 font-mono-code">LIVE SYNC</span>
                </div>

                @if(!$isUnlocked)
                <button onclick="openUnlockModal()" class="px-3 py-1.5 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 text-amber-300 text-xs font-semibold flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-lock"></i> Buka Kunci Kontrol
                </button>
                @endif

                <a href="#codeSection" class="px-3 py-1.5 rounded-lg bg-indigo-600/20 hover:bg-indigo-600/30 border border-indigo-500/30 text-indigo-300 text-xs font-semibold flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-microchip"></i> Firmware ESP32
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 w-full">

        @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-lg"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">&times;</button>
        </div>
        @endif

        @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-white">&times;</button>
        </div>
        @endif

        <!-- TOP STATS COUNTERS -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="glass-card p-4 rounded-2xl flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 text-xl">
                    <i class="fa-solid fa-microchip"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold font-tech text-white">{{ $stats['total_devices'] }}</div>
                    <div class="text-xs text-slate-400">Total Alat Sensor</div>
                </div>
            </div>

            <div class="glass-card p-4 rounded-2xl flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 text-xl">
                    <i class="fa-solid fa-wifi"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold font-tech text-emerald-400">{{ $stats['online_devices'] }}</div>
                    <div class="text-xs text-slate-400">Perangkat Online</div>
                </div>
            </div>

            <div class="glass-card p-4 rounded-2xl flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400 text-xl">
                    <i class="fa-brands fa-whatsapp"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold font-tech text-rose-400">{{ $stats['alerts_today'] }}</div>
                    <div class="text-xs text-slate-400">Notifikasi WA Hari Ini</div>
                </div>
            </div>

            <div class="glass-card p-4 rounded-2xl flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 text-xl">
                    <i class="fa-solid fa-volume-high"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold font-tech text-amber-300">
                        <span id="statAvgDb">{{ $stats['avg_db_today'] ?: '--' }}</span> <span class="text-xs font-normal text-slate-400">dB</span>
                    </div>
                    <div class="text-xs text-slate-400">Rata-rata Suara Kelas</div>
                </div>
            </div>
        </div>

        @if($activeDevice)
        <!-- MAIN DASHBOARD CONSOLE -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- LEFT & CENTER: VU METER, 3-COLOR LED INDICATOR, & WAVEFORM (8 Cols) -->
            <div class="lg:col-span-8 space-y-6">

                <!-- 1. LIVE HARDWARE CONSOLE PANEL -->
                <div class="glass-panel p-6 rounded-3xl border border-slate-700/80 shadow-2xl relative overflow-hidden">
                    
                    <!-- Decorative Background Glow -->
                    <div id="ambientGlow" class="absolute -top-24 -left-24 w-80 h-80 rounded-full blur-[100px] pointer-events-none opacity-20 bg-emerald-500 transition-all duration-700"></div>

                    <!-- Panel Header -->
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-4 mb-6">
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 rounded-full text-xs font-mono-code font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                UID: {{ $activeDevice->device_uid }}
                            </span>
                            <span id="deviceOnlineBadge" class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $activeDevice->isOnline() ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-slate-700 text-slate-400' }}">
                                <i class="fa-solid fa-circle text-[8px] mr-1"></i> {{ $activeDevice->isOnline() ? 'ONLINE' : 'STANDBY' }}
                            </span>
                        </div>
                        <div class="text-xs text-slate-400">
                            Lokasi: <span class="text-slate-200 font-medium">{{ $activeDevice->location_note ?: 'Ruang Kelas' }}</span>
                        </div>
                    </div>

                    <!-- 3x PHYSICAL LED SIMULATION + REAL-TIME DIGITAL READOUT -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                        
                        <!-- 3-Color Physical LED Hardware Representation -->
                        <div class="md:col-span-5 flex flex-col items-center justify-center p-6 rounded-2xl bg-black/50 border border-slate-800/80 shadow-inner">
                            <div class="text-[11px] font-mono-code uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-lightbulb"></i> STATUS LAMPU KELAS
                            </div>

                            <!-- 3 Lamp Housing -->
                            <div class="flex items-center justify-center gap-6 py-2">
                                <!-- Green LED -->
                                <div class="flex flex-col items-center gap-2">
                                    <div id="ledGreen" class="w-14 h-14 rounded-full led-indicator {{ ($activeDevice->last_status ?? 'safe') === 'safe' ? 'led-green-on' : 'led-green-off' }}"></div>
                                    <span class="text-[10px] font-bold font-mono-code uppercase text-emerald-400">HIJAU</span>
                                    <span class="text-[9px] text-slate-400">&lt; {{ $activeDevice->threshold_green }} dB</span>
                                </div>

                                <!-- Yellow LED -->
                                <div class="flex flex-col items-center gap-2">
                                    <div id="ledYellow" class="w-14 h-14 rounded-full led-indicator {{ ($activeDevice->last_status ?? '') === 'warning' ? 'led-yellow-on' : 'led-yellow-off' }}"></div>
                                    <span class="text-[10px] font-bold font-mono-code uppercase text-amber-400">KUNING</span>
                                    <span class="text-[9px] text-slate-400">{{ $activeDevice->threshold_green }}-{{ $activeDevice->threshold_red }} dB</span>
                                </div>

                                <!-- Red LED -->
                                <div class="flex flex-col items-center gap-2">
                                    <div id="ledRed" class="w-14 h-14 rounded-full led-indicator {{ ($activeDevice->last_status ?? '') === 'danger' ? 'led-red-on' : 'led-red-off' }}"></div>
                                    <span class="text-[10px] font-bold font-mono-code uppercase text-rose-400">MERAH</span>
                                    <span class="text-[9px] text-slate-400">&ge; {{ $activeDevice->threshold_red }} dB</span>
                                </div>
                            </div>

                            <!-- OLED 1.3" Mini Hardware Preview Tag -->
                            <div class="mt-4 pt-3 border-t border-slate-800/80 w-full text-center">
                                <span class="text-[10px] text-slate-400 font-mono-code">
                                    <i class="fa-solid fa-tv mr-1"></i> OLED 1.3" I2C Sync Aktif (SH1106)
                                </span>
                            </div>
                        </div>

                        <!-- Real-time dB Sound Meter Display -->
                        <div class="md:col-span-7 flex flex-col justify-center space-y-4">
                            <div>
                                <div class="flex items-baseline justify-between mb-1">
                                    <span class="text-xs font-mono-code text-slate-400 tracking-wider">TINGKAT KEBISINGAN KELAS:</span>
                                    <span id="liveStatusBadge" class="px-2.5 py-0.5 rounded text-xs font-bold font-mono-code uppercase tracking-wider
                                        {{ ($activeDevice->last_status ?? 'safe') === 'danger' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/40' : (($activeDevice->last_status ?? '') === 'warning' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40') }}">
                                        {{ ($activeDevice->last_status ?? 'safe') === 'danger' ? '⚠️ TERLALU RIBUT (BAHAYA)' : (($activeDevice->last_status ?? '') === 'warning' ? '⚡ SEDIKIT RIBUT' : '✅ KONDISI AMAN / TERTIB') }}
                                    </span>
                                </div>
                                <div class="flex items-baseline gap-3">
                                    <span id="liveDbValue" class="text-6xl sm:text-7xl font-bold font-tech tracking-tight text-white transition-all">
                                        {{ number_format($activeDevice->last_db ?? 45.0, 1) }}
                                    </span>
                                    <span class="text-2xl font-tech text-slate-400">dB</span>
                                    <span class="text-xs font-mono-code text-slate-500 ml-auto">
                                        Peak: <span id="livePeakDb" class="text-slate-300">{{ number_format($activeDevice->peak_db ?? 45.0, 1) }}</span> dB
                                    </span>
                                </div>
                            </div>

                            <!-- Animated Segmented VU Meter Bar -->
                            <div class="space-y-1">
                                <div class="h-6 rounded-lg bg-black/60 p-1 border border-slate-700/80 relative overflow-hidden flex items-center">
                                    <div id="vuMeterBar" class="h-full rounded transition-all duration-150 bg-gradient-to-r from-emerald-500 via-amber-400 to-rose-600"
                                         style="width: {{ min(100, max(0, (($activeDevice->last_db ?? 45) - 30) / 70 * 100)) }}%;"></div>
                                    <!-- Marker Line for Red Threshold -->
                                    <div class="absolute top-0 bottom-0 w-0.5 bg-rose-500 z-10 shadow-lg shadow-rose-500/50" 
                                         style="left: {{ (($activeDevice->threshold_red - 30) / 70 * 100) }}%;"
                                         title="Batas Merah ({{ $activeDevice->threshold_red }} dB)"></div>
                                </div>
                                <div class="flex justify-between text-[10px] font-mono-code text-slate-500 px-1">
                                    <span>30 dB</span>
                                    <span class="text-emerald-400">{{ $activeDevice->threshold_green }} dB</span>
                                    <span class="text-amber-400">{{ $activeDevice->threshold_yellow }} dB</span>
                                    <span class="text-rose-400 font-bold">&ge; {{ $activeDevice->threshold_red }} dB (ALARM)</span>
                                    <span>100 dB</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- REAL-TIME NOISE WAVEFORM CHART -->
                    <div class="mt-6 pt-4 border-t border-slate-800">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-mono-code text-slate-400 flex items-center gap-1.5">
                                <i class="fa-solid fa-chart-line text-emerald-400"></i> FLUKTUASI SUARA KELAS (REAL-TIME SPARKLINE)
                            </span>
                            <span class="text-[11px] text-slate-500">Update setiap 2 detik</span>
                        </div>
                        <div class="h-28 w-full">
                            <canvas id="noiseWaveformCanvas"></canvas>
                        </div>
                    </div>

                </div>

                <!-- 2. WALI KELAS & RECIPIENT CARD -->
                @php
                    $wali = $activeDevice->getEffectiveWaliKelas();
                @endphp
                <div class="glass-panel p-5 rounded-3xl border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4 w-full sm:w-auto">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 p-0.5 shadow-lg shadow-emerald-500/20 shrink-0">
                            <div class="w-full h-full bg-slate-900 rounded-[14px] flex items-center justify-center text-emerald-400 text-2xl">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold text-emerald-400 uppercase tracking-wider">Wali Kelas Penerima Peringatan</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono-code bg-slate-800 text-slate-300 border border-slate-700">
                                    {{ $activeDevice->classroom ? $activeDevice->classroom->class_name : 'Belum Ditentukan' }}
                                </span>
                            </div>
                            <div class="text-base font-bold text-white mt-0.5" id="waliNameDisplay">
                                {{ $wali['name'] }}
                            </div>
                            <div class="text-xs text-slate-400 flex items-center gap-2 mt-1">
                                <i class="fa-brands fa-whatsapp text-emerald-400"></i>
                                <span id="waliPhoneDisplay" class="font-mono-code text-slate-300">{{ $wali['phone'] ?: 'Nomor WhatsApp belum terdaftar' }}</span>
                                @if(empty($wali['phone']))
                                <span class="text-[10px] text-rose-400 italic">(Wajib dilengkapi)</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                        <button onclick="testWhatsAppAlert({{ $activeDevice->id }})" id="btnTestWa"
                            class="px-4 py-2 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 border border-emerald-500/40 text-emerald-300 text-xs font-semibold flex items-center gap-2 transition active:scale-95">
                            <i class="fa-brands fa-whatsapp text-base"></i> Test WA ke Wali Kelas
                        </button>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: THRESHOLD CONTROLS & SOUND SIMULATOR (4 Cols) -->
            <div class="lg:col-span-4 space-y-6">

                <!-- 1. THRESHOLD SETTINGS QUICK CONTROLS -->
                <div class="glass-panel p-5 rounded-3xl border border-slate-800 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-sliders text-emerald-400"></i>
                            <span class="font-tech font-bold text-white">KONTROL AMBANG BATAS</span>
                        </div>
                        <span class="text-[10px] font-mono-code text-slate-400">SYNC OTA</span>
                    </div>

                    <form id="formThresholds" onsubmit="saveDeviceSettings(event, {{ $activeDevice->id }})" class="space-y-4">
                        @csrf
                        <input type="hidden" name="name" value="{{ $activeDevice->name }}">
                        <input type="hidden" name="school_id" value="{{ $activeDevice->school_id }}">
                        <input type="hidden" name="classroom_id" value="{{ $activeDevice->classroom_id }}">
                        <input type="hidden" name="location_note" value="{{ $activeDevice->location_note }}">
                        <input type="hidden" name="custom_recipient_name" value="{{ $activeDevice->custom_recipient_name }}">
                        <input type="hidden" name="custom_phone" value="{{ $activeDevice->custom_phone }}">

                        <!-- Batas Hijau (Aman) -->
                        <div class="space-y-1">
                            <div class="flex justify-between text-xs">
                                <span class="text-emerald-400 font-semibold flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle text-[8px]"></i> Batas Aman (Hijau)
                                </span>
                                <span id="labelThreshGreen" class="font-mono-code text-white">&lt; {{ $activeDevice->threshold_green }} dB</span>
                            </div>
                            <input type="range" name="threshold_green" min="40" max="85" step="1" 
                                value="{{ (int)$activeDevice->threshold_green }}"
                                oninput="document.getElementById('labelThreshGreen').innerText = '< ' + this.value + ' dB'"
                                class="w-full accent-emerald-500 bg-slate-800 h-2 rounded-lg cursor-pointer">
                        </div>

                        <!-- Batas Kuning (Sedang / Sedikit Ribut) -->
                        <div class="space-y-1">
                            <div class="flex justify-between text-xs">
                                <span class="text-amber-400 font-semibold flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle text-[8px]"></i> Batas Sedang (Kuning)
                                </span>
                                <span id="labelThreshYellow" class="font-mono-code text-white">{{ $activeDevice->threshold_yellow }} dB</span>
                            </div>
                            <input type="range" name="threshold_yellow" min="50" max="95" step="1" 
                                value="{{ (int)$activeDevice->threshold_yellow }}"
                                oninput="document.getElementById('labelThreshYellow').innerText = this.value + ' dB'"
                                class="w-full accent-amber-500 bg-slate-800 h-2 rounded-lg cursor-pointer">
                        </div>

                        <!-- Batas Merah (Terlalu Ribut / Bahaya) -->
                        <div class="space-y-1">
                            <div class="flex justify-between text-xs">
                                <span class="text-rose-400 font-semibold flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle text-[8px]"></i> Batas Bahaya (Merah & WA)
                                </span>
                                <span id="labelThreshRed" class="font-mono-code text-white">&ge; {{ $activeDevice->threshold_red }} dB</span>
                            </div>
                            <input type="range" name="threshold_red" min="60" max="105" step="1" 
                                value="{{ (int)$activeDevice->threshold_red }}"
                                oninput="document.getElementById('labelThreshRed').innerText = '&ge; ' + this.value + ' dB'"
                                class="w-full accent-rose-500 bg-slate-800 h-2 rounded-lg cursor-pointer">
                        </div>

                        <!-- Cooldown Notifikasi -->
                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <div>
                                <label class="text-[11px] text-slate-400 block mb-1">Cooldown WA (Menit):</label>
                                <input type="number" name="cooldown_minutes" value="{{ $activeDevice->cooldown_minutes }}" min="1" max="120"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-white font-mono-code focus:outline-none focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="text-[11px] text-slate-400 block mb-1">Status Kirim WA:</label>
                                <label class="inline-flex items-center gap-2 cursor-pointer mt-1">
                                    <input type="checkbox" name="wa_enabled" value="1" {{ $activeDevice->wa_enabled ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                                    <span class="text-xs text-slate-300 font-medium">Aktif</span>
                                </label>
                            </div>
                        </div>

                        <button type="submit" id="btnSaveThresholds"
                            class="w-full py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-semibold text-xs shadow-lg shadow-emerald-500/20 transition active:scale-95">
                            <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Ambang Batas ke Alat
                        </button>
                    </form>
                </div>

                <!-- 2. NOISE SIMULATOR (WEB TESTER) -->
                <div class="glass-panel p-5 rounded-3xl border border-slate-800 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-flask-vial text-amber-400"></i>
                            <span class="font-tech font-bold text-white">SIMULATOR KEBISINGAN KELAS</span>
                        </div>
                        <span class="text-[10px] font-mono-code text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">TEST LAB</span>
                    </div>

                    <p class="text-xs text-slate-400">
                        Uji coba respons lampu LED dan pengiriman WhatsApp otomatis secara langsung melalui browser tanpa memerlukan hardware:
                    </p>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-300">Geser Nilai Desibel Suara:</span>
                            <span id="simDbLabel" class="text-sm font-bold font-mono-code text-emerald-400">45.0 dB</span>
                        </div>
                        <input type="range" id="simDbSlider" min="30" max="110" step="0.5" value="45"
                            oninput="updateSimulatorSlider(this.value)"
                            class="w-full accent-amber-500 bg-slate-800 h-2.5 rounded-lg cursor-pointer">

                        <div class="grid grid-cols-3 gap-2 pt-1">
                            <button onclick="setSimLevel(42)" class="px-2 py-2 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-[11px] font-semibold text-center transition">
                                🤫 Hening<br><span class="font-mono-code text-[10px]">42 dB</span>
                            </button>
                            <button onclick="setSimLevel(72)" class="px-2 py-2 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 text-amber-300 text-[11px] font-semibold text-center transition">
                                💬 Diskusi<br><span class="font-mono-code text-[10px]">72 dB</span>
                            </button>
                            <button onclick="setSimLevel(86)" class="px-2 py-2 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 text-rose-300 text-[11px] font-semibold text-center transition">
                                🚨 Ribut WA<br><span class="font-mono-code text-[10px]">86 dB</span>
                            </button>
                        </div>

                        <button onclick="sendSimulatedTelemetry()" id="btnSendSim"
                            class="w-full py-2.5 rounded-xl bg-amber-600/20 hover:bg-amber-600/30 border border-amber-500/40 text-amber-300 text-xs font-semibold flex items-center justify-center gap-2 transition active:scale-95">
                            <i class="fa-solid fa-play"></i> Eksekusi Sinyal Suara ke Sistem
                        </button>
                    </div>
                </div>

            </div>

        </div>
        @else
        <!-- EMPTY STATE: JIKA BELUM ADA DEVICE -->
        <div class="glass-panel p-12 rounded-3xl border border-slate-800 text-center space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 text-3xl mx-auto">
                <i class="fa-solid fa-microchip"></i>
            </div>
            <h2 class="text-xl font-bold font-tech text-white">Belum Ada Perangkat Sensor Kebisingan</h2>
            <p class="text-sm text-slate-400 max-w-md mx-auto">
                Tambahkan perangkat monitor kebisingan ESP32 pertama Anda untuk mengaitkannya ke kelas dan mengaktifkan deteksi suara otomatis.
            </p>
            <button onclick="openModalCreateDevice()" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm shadow-lg shadow-emerald-500/20 transition">
                <i class="fa-solid fa-plus mr-1"></i> Tambah Perangkat Sekarang
            </button>
        </div>
        @endif

        <!-- SECTION: MANAJEMEN PERANGKAT KELAS -->
        <div class="glass-panel p-6 rounded-3xl border border-slate-800 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-4">
                <div>
                    <h3 class="font-tech font-bold text-lg text-white flex items-center gap-2">
                        <i class="fa-solid fa-server text-emerald-400"></i> DAFTAR PERANGKAT IOT TERPASANG
                    </h3>
                    <p class="text-xs text-slate-400">Perangkat sensor ESP32 yang terhubung ke sistem pemantauan ruang kelas</p>
                </div>

                <div class="flex items-center gap-2">
                    <button onclick="openModalCreateDevice()" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold flex items-center gap-1.5 transition shadow-lg shadow-emerald-500/20">
                        <i class="fa-solid fa-plus"></i> Tambah Alat
                    </button>
                </div>
            </div>

            <!-- Table of Devices -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-900/60 uppercase font-mono-code text-[11px] text-slate-400 border-b border-slate-800">
                        <tr>
                            <th class="p-3">UID & Nama Alat</th>
                            <th class="p-3">Unit & Kelas</th>
                            <th class="p-3">Wali Kelas (WA)</th>
                            <th class="p-3">Ambang Batas (dB)</th>
                            <th class="p-3">Token Telemetri</th>
                            <th class="p-3">Status Live</th>
                            <th class="p-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80">
                        @forelse($devices as $dev)
                        @php $w = $dev->getEffectiveWaliKelas(); @endphp
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="p-3">
                                <div class="font-bold text-white font-tech">{{ $dev->name }}</div>
                                <div class="font-mono-code text-[10px] text-slate-400">{{ $dev->device_uid }}</div>
                            </td>
                            <td class="p-3">
                                <div class="font-semibold text-slate-200">{{ $dev->classroom ? $dev->classroom->class_name : 'Belum Ditentukan' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $dev->school ? $dev->school->name : 'Semua Unit' }}</div>
                            </td>
                            <td class="p-3">
                                <div class="font-medium text-slate-200">{{ $w['name'] }}</div>
                                <div class="font-mono-code text-[10px] text-emerald-400">{{ $w['phone'] ?: '-' }}</div>
                            </td>
                            <td class="p-3 font-mono-code text-[11px]">
                                <span class="text-emerald-400">&lt;{{ (int)$dev->threshold_green }}</span> / 
                                <span class="text-amber-400">{{ (int)$dev->threshold_yellow }}</span> / 
                                <span class="text-rose-400">&ge;{{ (int)$dev->threshold_red }}</span>
                            </td>
                            <td class="p-3">
                                <div class="flex items-center gap-1.5 font-mono-code text-[11px]">
                                    <span class="bg-slate-900 px-2 py-1 rounded text-slate-400 max-w-[120px] truncate" title="{{ $dev->device_token }}">{{ substr($dev->device_token, 0, 10) }}...</span>
                                    <button onclick="copyToClipboard('{{ $dev->device_token }}', 'Token disalin!')" class="text-slate-400 hover:text-white" title="Salin Token">
                                        <i class="fa-regular fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $dev->isOnline() ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-800 text-slate-400' }}">
                                    {{ $dev->isOnline() ? 'ONLINE' : 'STANDBY' }}
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="?device_id={{ $dev->id }}" class="px-2.5 py-1 rounded-lg bg-blue-500/20 hover:bg-blue-500/30 text-blue-300 text-xs transition" title="Lihat Console">
                                        <i class="fa-solid fa-gauge-high"></i>
                                    </a>
                                    <button onclick="openModalEditDevice({{ $dev->toJson() }})" class="px-2.5 py-1 rounded-lg bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 text-xs transition" title="Edit Alat">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <form action="{{ route('noisecontrol.devices.delete', $dev->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus alat ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 text-xs transition" title="Hapus">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-slate-500 italic">Belum ada perangkat yang terdaftar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECTION: RIWAYAT ALARM & LOG TELEMETRI -->
        <div class="glass-panel p-6 rounded-3xl border border-slate-800 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-rose-400"></i>
                    <h3 class="font-tech font-bold text-lg text-white">RIWAYAT PERINGATAN KEBISINGAN & PESAN WA</h3>
                </div>
                <span class="text-xs font-mono-code text-slate-400">Sinkronisasi Database Riil</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-900/60 uppercase font-mono-code text-[11px] text-slate-400 border-b border-slate-800">
                        <tr>
                            <th class="p-3">Waktu</th>
                            <th class="p-3">Kelas & Ruang</th>
                            <th class="p-3">Tingkat dB</th>
                            <th class="p-3">Status Indikator</th>
                            <th class="p-3">Notifikasi WhatsApp</th>
                            <th class="p-3">Penerima</th>
                        </tr>
                    </thead>
                    <tbody id="logsTableBody" class="divide-y divide-slate-800/80">
                        @forelse($recentLogs as $log)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="p-3 font-mono-code text-slate-400 whitespace-nowrap">{{ $log->created_at->format('H:i:s d/m/Y') }}</td>
                            <td class="p-3 font-semibold text-white">{{ $log->classroom ? $log->classroom->class_name : ($log->device?->name ?? 'Ruang Kelas') }}</td>
                            <td class="p-3 font-mono-code font-bold {{ $log->status === 'danger' ? 'text-rose-400' : ($log->status === 'warning' ? 'text-amber-400' : 'text-emerald-400') }}">
                                {{ number_format($log->db_level, 1) }} dB
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono-code uppercase font-bold
                                    {{ $log->status === 'danger' ? 'bg-rose-500/20 text-rose-300' : ($log->status === 'warning' ? 'bg-amber-500/20 text-amber-300' : 'bg-emerald-500/20 text-emerald-300') }}">
                                    {{ $log->status === 'danger' ? 'MERAH (BAHAYA)' : ($log->status === 'warning' ? 'KUNING (SEDANG)' : 'HIJAU (AMAN)') }}
                                </span>
                            </td>
                            <td class="p-3">
                                @if($log->is_alert_triggered)
                                    @if($log->alert_status === 'sent' || $log->alert_status === 'test_sent')
                                        <span class="text-emerald-400 font-semibold flex items-center gap-1"><i class="fa-solid fa-check-double"></i> Terkirim ke WA</span>
                                    @elseif($log->alert_status === 'cooldown')
                                        <span class="text-amber-400 flex items-center gap-1"><i class="fa-solid fa-hourglass-half"></i> Dilewati (Cooldown)</span>
                                    @elseif($log->alert_status === 'no_phone')
                                        <span class="text-rose-400 flex items-center gap-1"><i class="fa-solid fa-triangle-exclamation"></i> Gagal (No HP Kosong)</span>
                                    @else
                                        <span class="text-rose-400 flex items-center gap-1"><i class="fa-solid fa-xmark"></i> {{ $log->alert_status }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="p-3 text-slate-400 text-[11px] truncate max-w-[200px]" title="{{ $log->alert_recipient }}">{{ $log->alert_recipient ?: '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-slate-500 italic">Belum ada riwayat rekaman suara.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECTION: SKEMA HARDWARE & SOURCE CODE ARDUINO ESP32 -->
        <div id="codeSection" class="glass-panel p-6 rounded-3xl border border-slate-800 space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-800 pb-4">
                <div>
                    <h3 class="font-tech font-bold text-lg text-white flex items-center gap-2">
                        <i class="fa-solid fa-microchip text-indigo-400"></i> PANDUAN PERAKITAN ALAT & FIRMWARE ESP32
                    </h3>
                    <p class="text-xs text-slate-400">Wiring Pinout Lengkap INMP441, OLED 1.3", LED 3 Warna, dan Source Code Siap Flash</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="copyFirmwareCode()" class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold flex items-center gap-1.5 transition shadow-lg shadow-indigo-500/20">
                        <i class="fa-regular fa-copy"></i> Salin Seluruh Kode Arduino
                    </button>
                </div>
            </div>

            <!-- HARDWARE PINOUT TABLE -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- INMP441 -->
                <div class="glass-card p-4 rounded-2xl border border-slate-700/60 space-y-2">
                    <div class="text-xs font-bold font-mono-code text-emerald-400 flex items-center gap-2">
                        <i class="fa-solid fa-microphone-lines"></i> 1. SENSOR INMP441 (I2S)
                    </div>
                    <ul class="text-xs space-y-1 font-mono-code text-slate-300">
                        <li><span class="text-slate-400">VDD :</span> 3.3V ESP32</li>
                        <li><span class="text-slate-400">GND :</span> GND ESP32</li>
                        <li><span class="text-slate-400">SD  :</span> <span class="text-emerald-400 font-bold">GPIO 32</span> (Serial Data)</li>
                        <li><span class="text-slate-400">WS  :</span> <span class="text-emerald-400 font-bold">GPIO 25</span> (Word Select)</li>
                        <li><span class="text-slate-400">SCK :</span> <span class="text-emerald-400 font-bold">GPIO 33</span> (Clock BCLK)</li>
                        <li><span class="text-slate-400">L/R :</span> GND (Left Channel)</li>
                    </ul>
                </div>

                <!-- OLED 1.3" -->
                <div class="glass-card p-4 rounded-2xl border border-slate-700/60 space-y-2">
                    <div class="text-xs font-bold font-mono-code text-blue-400 flex items-center gap-2">
                        <i class="fa-solid fa-tv"></i> 2. DISPLAY OLED 1.3" (I2C)
                    </div>
                    <ul class="text-xs space-y-1 font-mono-code text-slate-300">
                        <li><span class="text-slate-400">VCC :</span> 3.3V ESP32</li>
                        <li><span class="text-slate-400">GND :</span> GND ESP32</li>
                        <li><span class="text-slate-400">SDA :</span> <span class="text-blue-400 font-bold">GPIO 21</span> (I2C Data)</li>
                        <li><span class="text-slate-400">SCL :</span> <span class="text-blue-400 font-bold">GPIO 22</span> (I2C Clock)</li>
                        <li><span class="text-slate-400">Chip:</span> SH1106 / SSD1306 (0x3C)</li>
                        <li><span class="text-slate-400">Fitur:</span> Bar VU Graph + Teks dB</li>
                    </ul>
                </div>

                <!-- LED 3 WARNA -->
                <div class="glass-card p-4 rounded-2xl border border-slate-700/60 space-y-2">
                    <div class="text-xs font-bold font-mono-code text-amber-400 flex items-center gap-2">
                        <i class="fa-solid fa-traffic-light"></i> 3. INDIKATOR 3 LED
                    </div>
                    <ul class="text-xs space-y-1 font-mono-code text-slate-300">
                        <li><span class="text-emerald-400">LED Hijau  (Aman)    :</span> <span class="font-bold">GPIO 18</span></li>
                        <li><span class="text-amber-400">LED Kuning (Sedang)  :</span> <span class="font-bold">GPIO 19</span></li>
                        <li><span class="text-rose-400">LED Merah  (Ribut)   :</span> <span class="font-bold">GPIO 23</span></li>
                        <li class="text-[10px] text-slate-400 pt-1">Gunakan resistor 220&Omega; - 330&Omega; di setiap kaki katoda ke GND.</li>
                    </ul>
                </div>
            </div>

            <!-- CODE PREVIEW BLOCK -->
            <div class="space-y-2">
                <div class="flex items-center justify-between text-xs text-slate-400">
                    <span class="font-mono-code">Firmware: PembdaNoiseMonitor_ESP32.ino (Otomatis Terisi Token Alat Aktif)</span>
                    <span class="text-emerald-400 font-mono-code text-[11px]">✓ U8g2 & ArduinoJson Ready</span>
                </div>
                <div class="relative rounded-2xl overflow-hidden border border-slate-800 bg-[#060a12]">
                    <pre class="p-4 text-xs font-mono-code text-slate-300 overflow-x-auto max-h-[380px] leading-relaxed"><code id="firmwareCodeBlock">// Cuplikan Konfigurasi Otomatis untuk Perangkat Ini:
const char* SERVER_URL   = "https://perguruanpembda.com/noicecontrol/api/telemetry";
const char* DEVICE_TOKEN = "{{ $activeDevice->device_token ?? 'nd_TOKEN_ALAT_ANDA' }}";

// Pin Hardware:
#define I2S_WS_PIN    25
#define I2S_SCK_PIN   33
#define I2S_SD_PIN    32
#define PIN_LED_HIJAU   18
#define PIN_LED_KUNING  19
#define PIN_LED_MERAH   23

// Ambang Batas Default dari Server:
float thresholdGreen   = {{ $activeDevice->threshold_green ?? 65.0 }};
float thresholdYellow  = {{ $activeDevice->threshold_yellow ?? 75.0 }};
float thresholdRed     = {{ $activeDevice->threshold_red ?? 80.0 }};

// (Klik tombol 'Salin Seluruh Kode Arduino' di atas untuk mengunduh kode lengkap 250+ baris)</code></pre>
                </div>
            </div>
        </div>

    </main>

    <!-- FOOTER -->
    <footer class="glass-panel border-t border-slate-800 py-6 mt-12 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4">
            <p>Sistem Pemantau Kebisingan Kelas Berbasis IoT (ESP32, INMP441, OLED 1.3", 3-LED Indicator & WhatsApp Gateway)</p>
            <p class="mt-1 text-slate-400">SIM Terpadu PembdaHUB &copy; {{ date('Y') }} Perguruan PEMBDA Nias. All rights reserved.</p>
        </div>
    </footer>

    <!-- MODAL 1: TAMBAH PERANGKAT BARU -->
    <div id="modalCreateDevice" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="glass-panel w-full max-w-lg p-6 rounded-3xl border border-slate-700 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-tech font-bold text-lg text-white flex items-center gap-2">
                    <i class="fa-solid fa-plus text-emerald-400"></i> Pendaftaran Perangkat Sensor Baru
                </h3>
                <button onclick="closeModalCreateDevice()" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form action="{{ route('noisecontrol.devices.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs text-slate-300 block mb-1">Nama Perangkat / Label:</label>
                    <input type="text" name="name" required placeholder="Contoh: Sensor Suara Lab X TE"
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-slate-300 block mb-1">ID Unik (UID):</label>
                        <input type="text" name="device_uid" placeholder="ESP32-NOISE-02"
                            class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white font-mono-code focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="text-xs text-slate-300 block mb-1">Unit Sekolah:</label>
                        <select name="school_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                            <option value="">-- Pilih Unit Sekolah --</option>
                            @foreach($schools as $sch)
                                <option value="{{ $sch->id }}">{{ $sch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-xs text-slate-300 block mb-1">Kelas Terhubung (Otomatis Ambil Wali Kelas):</label>
                    <select name="classroom_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                        <option value="">-- Pilih Ruang Kelas --</option>
                        @foreach($classrooms as $cls)
                            <option value="{{ $cls->id }}">
                                {{ $cls->class_name }} ({{ $cls->school ? $cls->school->name : 'Unit' }}) - Wali: {{ $cls->homeroomTeacher ? $cls->homeroomTeacher->full_name : 'Belum Ada' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="text-[11px] text-emerald-400 block mb-1">Batas Aman (dB):</label>
                        <input type="number" name="threshold_green" value="65" min="30" max="100" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2 text-xs text-white font-mono-code">
                    </div>
                    <div>
                        <label class="text-[11px] text-amber-400 block mb-1">Batas Sedang (dB):</label>
                        <input type="number" name="threshold_yellow" value="75" min="30" max="100" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2 text-xs text-white font-mono-code">
                    </div>
                    <div>
                        <label class="text-[11px] text-rose-400 block mb-1">Batas Merah (dB):</label>
                        <input type="number" name="threshold_red" value="80" min="30" max="110" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2 text-xs text-white font-mono-code">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-slate-300 block mb-1">Wali Pengganti (Opsional):</label>
                        <input type="text" name="custom_recipient_name" placeholder="Nama Guru / Piket" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                    </div>
                    <div>
                        <label class="text-xs text-slate-300 block mb-1">No. WA Pengganti (Opsional):</label>
                        <input type="text" name="custom_phone" placeholder="08123456789" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white font-mono-code">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-800">
                    <button type="button" onclick="closeModalCreateDevice()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-lg shadow-emerald-500/20">Simpan Perangkat</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: EDIT PERANGKAT -->
    <div id="modalEditDevice" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="glass-panel w-full max-w-lg p-6 rounded-3xl border border-slate-700 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-tech font-bold text-lg text-white flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-amber-400"></i> Edit Pengaturan Perangkat
                </h3>
                <button onclick="closeModalEditDevice()" class="text-slate-400 hover:text-white">&times;</button>
            </div>

            <form id="formEditDevice" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs text-slate-300 block mb-1">Nama Perangkat / Label:</label>
                    <input type="text" name="name" id="editName" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-slate-300 block mb-1">Unit Sekolah:</label>
                        <select name="school_id" id="editSchoolId" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                            <option value="">-- Pilih Unit Sekolah --</option>
                            @foreach($schools as $sch)
                                <option value="{{ $sch->id }}">{{ $sch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-slate-300 block mb-1">Kelas Terhubung:</label>
                        <select name="classroom_id" id="editClassroomId" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                            <option value="">-- Pilih Ruang Kelas --</option>
                            @foreach($classrooms as $cls)
                                <option value="{{ $cls->id }}">
                                    {{ $cls->class_name }} ({{ $cls->school ? $cls->school->name : 'Unit' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="text-[11px] text-emerald-400 block mb-1">Aman (dB):</label>
                        <input type="number" name="threshold_green" id="editThreshGreen" min="30" max="100" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2 text-xs text-white font-mono-code">
                    </div>
                    <div>
                        <label class="text-[11px] text-amber-400 block mb-1">Sedang (dB):</label>
                        <input type="number" name="threshold_yellow" id="editThreshYellow" min="30" max="100" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2 text-xs text-white font-mono-code">
                    </div>
                    <div>
                        <label class="text-[11px] text-rose-400 block mb-1">Merah (dB):</label>
                        <input type="number" name="threshold_red" id="editThreshRed" min="30" max="110" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2 text-xs text-white font-mono-code">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-slate-300 block mb-1">Wali Pengganti (Manual):</label>
                        <input type="text" name="custom_recipient_name" id="editCustomName" placeholder="Kosongkan jika otomatis" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                    </div>
                    <div>
                        <label class="text-xs text-slate-300 block mb-1">No. WA Pengganti (Manual):</label>
                        <input type="text" name="custom_phone" id="editCustomPhone" placeholder="08xxxxxxxx" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white font-mono-code">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-slate-300 block mb-1">Jeda Cooldown WA (Menit):</label>
                        <input type="number" name="cooldown_minutes" id="editCooldown" min="1" max="120" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white font-mono-code">
                    </div>
                    <div>
                        <label class="text-xs text-slate-300 block mb-1">Kalibrasi Offset dB:</label>
                        <input type="number" step="0.5" name="calibration_offset" id="editCalibOffset" min="-20" max="20" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white font-mono-code">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-800">
                    <button type="button" onclick="closeModalEditDevice()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-semibold shadow-lg shadow-amber-500/20">Perbarui Pengaturan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: BUKA KUNCI PIN -->
    <div id="modalUnlock" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="glass-panel w-full max-w-sm p-6 rounded-3xl border border-slate-700 text-center space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 text-xl mx-auto">
                <i class="fa-solid fa-lock"></i>
            </div>
            <div>
                <h3 class="font-tech font-bold text-lg text-white">Masukkan PIN Keamanan</h3>
                <p class="text-xs text-slate-400 mt-1">Masukkan PIN kontrol SIM PembdaHUB untuk mengakses tombol pengaturan alat</p>
            </div>

            <form onsubmit="submitUnlockPin(event)" class="space-y-4">
                <input type="password" id="inputPin" placeholder="Masukkan PIN..." autofocus
                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-center text-base font-mono-code text-white tracking-widest focus:outline-none focus:border-amber-500">
                
                <div class="flex gap-2">
                    <button type="button" onclick="closeUnlockModal()" class="w-1/2 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold">Batal</button>
                    <button type="submit" class="w-1/2 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-semibold">Buka Kunci</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT LOGIC & CHART.JS -->
    <script>
        const activeDeviceId = {{ $activeDevice ? $activeDevice->id : 'null' }};
        const activeDeviceToken = "{{ $activeDevice->device_token ?? '' }}";
        let threshGreen = {{ $activeDevice->threshold_green ?? 65.0 }};
        let threshYellow = {{ $activeDevice->threshold_yellow ?? 75.0 }};
        let threshRed = {{ $activeDevice->threshold_red ?? 80.0 }};

        // Initialize Waveform Chart
        let waveformChart = null;
        const chartCtx = document.getElementById('noiseWaveformCanvas');
        if (chartCtx) {
            waveformChart = new Chart(chartCtx, {
                type: 'line',
                data: {
                    labels: Array(15).fill(''),
                    datasets: [{
                        label: 'Desibel Suara (dB)',
                        data: Array(15).fill(45),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.08)',
                        borderWidth: 2,
                        tension: 0.35,
                        fill: true,
                        pointRadius: 2,
                        pointHoverRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { display: false },
                        y: {
                            min: 30,
                            max: 105,
                            grid: { color: 'rgba(51, 65, 85, 0.3)' },
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 9, family: 'JetBrains Mono' },
                                stepSize: 20
                            }
                        }
                    },
                    animation: { duration: 300 }
                }
            });
        }

        // Live Polling Function (every 2.5 seconds)
        function pollLiveData() {
            if (!activeDeviceId) return;

            fetch(`{{ route('noisecontrol.live-data') }}?device_id=${activeDeviceId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success' && data.active_device) {
                        const dev = data.active_device;
                        updateLiveDashboard(dev.last_db, dev.peak_db, dev.last_status, dev.threshold_green, dev.threshold_yellow, dev.threshold_red, dev.is_online);

                        // Update sparkline chart
                        if (waveformChart && data.sparkline && data.sparkline.length > 0) {
                            const labels = data.sparkline.map(s => s.time);
                            const values = data.sparkline.map(s => s.db);
                            waveformChart.data.labels = labels;
                            waveformChart.data.datasets[0].data = values;

                            // Adjust line color based on last status
                            const lastStatus = dev.last_status;
                            if (lastStatus === 'danger') {
                                waveformChart.data.datasets[0].borderColor = '#ef4444';
                                waveformChart.data.datasets[0].backgroundColor = 'rgba(239, 68, 68, 0.15)';
                            } else if (lastStatus === 'warning') {
                                waveformChart.data.datasets[0].borderColor = '#f59e0b';
                                waveformChart.data.datasets[0].backgroundColor = 'rgba(245, 158, 11, 0.1)';
                            } else {
                                waveformChart.data.datasets[0].borderColor = '#10b981';
                                waveformChart.data.datasets[0].backgroundColor = 'rgba(16, 185, 129, 0.08)';
                            }
                            waveformChart.update();
                        }
                    }
                })
                .catch(err => console.log('Polling error:', err));
        }

        // Update Live Elements
        function updateLiveDashboard(db, peak, status, greenTh, yellowTh, redTh, isOnline) {
            threshGreen = greenTh;
            threshYellow = yellowTh;
            threshRed = redTh;

            const dbValEl = document.getElementById('liveDbValue');
            const peakEl = document.getElementById('livePeakDb');
            const badgeEl = document.getElementById('liveStatusBadge');
            const vuBar = document.getElementById('vuMeterBar');
            const ambientGlow = document.getElementById('ambientGlow');

            if (dbValEl) dbValEl.innerText = parseFloat(db).toFixed(1);
            if (peakEl) peakEl.innerText = parseFloat(peak).toFixed(1);

            // Update VU Bar width (30 dB - 100 dB map)
            if (vuBar) {
                const pct = Math.min(100, Math.max(0, ((db - 30) / 70) * 100));
                vuBar.style.width = pct + '%';
            }

            // Update 3 Physical LED Lamp elements
            const ledG = document.getElementById('ledGreen');
            const ledY = document.getElementById('ledYellow');
            const ledR = document.getElementById('ledRed');

            if (ledG && ledY && ledR) {
                ledG.className = 'w-14 h-14 rounded-full led-indicator ' + (status === 'safe' ? 'led-green-on' : 'led-green-off');
                ledY.className = 'w-14 h-14 rounded-full led-indicator ' + (status === 'warning' ? 'led-yellow-on' : 'led-yellow-off');
                ledR.className = 'w-14 h-14 rounded-full led-indicator ' + (status === 'danger' ? 'led-red-on' : 'led-red-off');
            }

            // Update Badge Text & Ambient Color
            if (badgeEl) {
                if (status === 'danger') {
                    badgeEl.className = 'px-2.5 py-0.5 rounded text-xs font-bold font-mono-code uppercase tracking-wider bg-rose-500/20 text-rose-400 border border-rose-500/40';
                    badgeEl.innerText = '⚠️ TERLALU RIBUT (BAHAYA)';
                    if (ambientGlow) ambientGlow.className = 'absolute -top-24 -left-24 w-80 h-80 rounded-full blur-[100px] pointer-events-none opacity-30 bg-rose-600 transition-all duration-700';
                } else if (status === 'warning') {
                    badgeEl.className = 'px-2.5 py-0.5 rounded text-xs font-bold font-mono-code uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/40';
                    badgeEl.innerText = '⚡ SEDIKIT RIBUT';
                    if (ambientGlow) ambientGlow.className = 'absolute -top-24 -left-24 w-80 h-80 rounded-full blur-[100px] pointer-events-none opacity-20 bg-amber-500 transition-all duration-700';
                } else {
                    badgeEl.className = 'px-2.5 py-0.5 rounded text-xs font-bold font-mono-code uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/40';
                    badgeEl.innerText = '✅ KONDISI AMAN / TERTIB';
                    if (ambientGlow) ambientGlow.className = 'absolute -top-24 -left-24 w-80 h-80 rounded-full blur-[100px] pointer-events-none opacity-20 bg-emerald-500 transition-all duration-700';
                }
            }
        }

        // Start live polling loop
        setInterval(pollLiveData, 2500);

        // Simulator Functions
        function updateSimulatorSlider(val) {
            document.getElementById('simDbLabel').innerText = parseFloat(val).toFixed(1) + ' dB';
        }

        function setSimLevel(val) {
            const slider = document.getElementById('simDbSlider');
            if (slider) {
                slider.value = val;
                updateSimulatorSlider(val);
            }
        }

        function sendSimulatedTelemetry() {
            if (!activeDeviceToken) {
                alert('Pilih atau buat perangkat terlebih dahulu sebelum simulasi.');
                return;
            }

            const dbVal = parseFloat(document.getElementById('simDbSlider').value);
            const btn = document.getElementById('btnSendSim');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim Sinyal...';

            fetch(`{{ route('noisecontrol.telemetry') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    token: activeDeviceToken,
                    db: dbVal,
                    peak_db: dbVal + 3.0,
                    firmware: 'SIMULATOR-WEB'
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-play"></i> Eksekusi Sinyal Suara ke Sistem';

                if (data.status === 'success') {
                    // Update tampilan seketika
                    updateLiveDashboard(data.current_db, data.current_db + 3.0, data.status_level, data.threshold_green, data.threshold_yellow, data.threshold_red, true);

                    if (data.alert_triggered && data.alert_sent) {
                        alert(`🚨 NOTIFIKASI SUKSES: Suara ${data.current_db} dB melampaui batas bahaya! Pesan WhatsApp otomatis telah dikirim ke Wali Kelas: ${data.wali_name}`);
                    } else if (data.alert_triggered && data.alert_status === 'cooldown') {
                        alert(`⚠️ AMBANG BATAS MERAH TERPICU: Suara ${data.current_db} dB, namun pesan WA dilewati karena masih masa Cooldown (${data.cooldown_remaining} menit lagi).`);
                    }
                    pollLiveData();
                } else {
                    alert('Gagal mengirim simulasi: ' + (data.message || 'Error'));
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-play"></i> Eksekusi Sinyal Suara ke Sistem';
                alert('Kesalahan koneksi simulasi.');
            });
        }

        // Test WhatsApp Direct Button
        function testWhatsAppAlert(deviceId) {
            const btn = document.getElementById('btnTestWa');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim WA...';

            fetch(`{{ url('/noicecontrol/devices') }}/${deviceId}/test-wa`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-brands fa-whatsapp text-base"></i> Test WA ke Wali Kelas';

                if (data.status === 'success') {
                    alert('✔ ' + data.message);
                } else {
                    alert('⚠️ ' + data.message);
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-brands fa-whatsapp text-base"></i> Test WA ke Wali Kelas';
                alert('Terjadi kesalahan saat memicu pengiriman pesan WhatsApp.');
            });
        }

        // Save Threshold Settings AJAX
        function saveDeviceSettings(e, deviceId) {
            e.preventDefault();
            const form = e.target;
            const btn = document.getElementById('btnSaveThresholds');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

            const formData = new FormData(form);

            fetch(`{{ url('/noicecontrol/devices') }}/${deviceId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Ambang Batas ke Alat';

                if (data.status === 'success') {
                    alert('✔ Pengaturan ambang batas kebisingan berhasil disimpan dan akan disinkronkan ke alat ESP32 secara instan!');
                    pollLiveData();
                } else {
                    alert('Gagal menyimpan: ' + (data.message || 'Error'));
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Ambang Batas ke Alat';
                alert('Gagal menghubungi server.');
            });
        }

        // Modal Handlers
        function openModalCreateDevice() {
            document.getElementById('modalCreateDevice').classList.remove('hidden');
        }
        function closeModalCreateDevice() {
            document.getElementById('modalCreateDevice').classList.add('hidden');
        }

        function openModalEditDevice(dev) {
            document.getElementById('formEditDevice').action = `{{ url('/noicecontrol/devices') }}/${dev.id}`;
            document.getElementById('editName').value = dev.name;
            document.getElementById('editSchoolId').value = dev.school_id || '';
            document.getElementById('editClassroomId').value = dev.classroom_id || '';
            document.getElementById('editThreshGreen').value = dev.threshold_green;
            document.getElementById('editThreshYellow').value = dev.threshold_yellow;
            document.getElementById('editThreshRed').value = dev.threshold_red;
            document.getElementById('editCustomName').value = dev.custom_recipient_name || '';
            document.getElementById('editCustomPhone').value = dev.custom_phone || '';
            document.getElementById('editCooldown').value = dev.cooldown_minutes;
            document.getElementById('editCalibOffset').value = dev.calibration_offset || 0;

            document.getElementById('modalEditDevice').classList.remove('hidden');
        }
        function closeModalEditDevice() {
            document.getElementById('modalEditDevice').classList.add('hidden');
        }

        function openUnlockModal() {
            document.getElementById('modalUnlock').classList.remove('hidden');
        }
        function closeUnlockModal() {
            document.getElementById('modalUnlock').classList.add('hidden');
        }

        function submitUnlockPin(e) {
            e.preventDefault();
            const pin = document.getElementById('inputPin').value;
            fetch(`{{ route('noisecontrol.unlock') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ pin: pin })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            });
        }

        function copyToClipboard(text, msg) {
            navigator.clipboard.writeText(text).then(() => {
                alert(msg || 'Berhasil disalin ke clipboard!');
            });
        }

        function copyFirmwareCode() {
            const code = document.getElementById('firmwareCodeBlock').innerText;
            // Fetch full source code from firmware file
            fetch('{{ asset("storage/firmware_esp32.ino") }}')
                .catch(() => null);

            // Copy populated firmware template
            const fullFirmware = `/* =========================================================================================
 * PEMBDA NOISE CONTROL & CLASSROOM MONITORING SYSTEM
 * Microcontroller : ESP32 (ESP-WROOM-32 / NodeMCU-32S)
 * Sensor Audio    : INMP441 (Digital I2S MEMS Microphone)
 * Display         : OLED 1.3" I2C (SH1106 / SSD1306, Address 0x3C)
 * Indikator LED   : Hijau (Aman), Kuning (Sedikit Ribut), Merah (Terlalu Ribut)
 * Cloud Server    : https://perguruanpembda.com/noicecontrol
 * =========================================================================================
 */

#include <Arduino.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <driver/i2s.h>
#include <Wire.h>
#include <U8g2lib.h>

const char* WIFI_SSID     = "NAMA_WIFI_SEKOLAH";
const char* WIFI_PASSWORD = "PASSWORD_WIFI";
const char* SERVER_URL    = "https://perguruanpembda.com/noicecontrol/api/telemetry";
const char* DEVICE_TOKEN  = "${activeDeviceToken}";
const char* FIRMWARE_VER  = "1.0.0-PRO";

#define I2S_WS_PIN    25
#define I2S_SCK_PIN   33
#define I2S_SD_PIN    32
#define I2S_PORT      I2S_NUM_0

#define PIN_LED_HIJAU   18
#define PIN_LED_KUNING  19
#define PIN_LED_MERAH   23

U8G2_SH1106_128X64_NONAME_F_HW_I2C u8g2(U8G2_R0, U8X8_PIN_NONE, 22, 21);

float thresholdGreen   = ${threshGreen};
float thresholdYellow  = ${threshYellow};
float thresholdRed     = ${threshRed};
float calibOffset      = 12.0;

#define I2S_SAMPLE_RATE 16000
#define I2S_BUFFER_SIZE 512
int32_t i2s_raw_samples[I2S_BUFFER_SIZE];

#define SPARKLINE_POINTS 32
uint8_t sparkline[SPARKLINE_POINTS];

unsigned long lastTelemetryTime = 0;
const unsigned long TELEMETRY_INTERVAL = 3000;
float currentDb = 45.0;
float peakDb    = 45.0;
String currentStatus = "safe";
String className     = "Ruang Kelas";

void setupI2S() {
    i2s_config_t i2s_config = {
        .mode = (i2s_mode_t)(I2S_MODE_MASTER | I2S_MODE_RX),
        .sample_rate = I2S_SAMPLE_RATE,
        .bits_per_sample = I2S_BITS_PER_SAMPLE_32BIT,
        .channel_format = I2S_CHANNEL_FMT_ONLY_LEFT,
        .communication_format = i2s_comm_format_t(I2S_COMM_FORMAT_STAND_I2S),
        .intr_alloc_flags = ESP_INTR_FLAG_LEVEL1,
        .dma_buf_count = 4,
        .dma_buf_len = I2S_BUFFER_SIZE,
        .use_apll = false,
        .tx_desc_auto_clear = false,
        .fixed_mclk = 0
    };
    i2s_pin_config_t pin_config = {
        .bck_io_num = I2S_SCK_PIN,
        .ws_io_num = I2S_WS_PIN,
        .data_out_num = I2S_PIN_NO_CHANGE,
        .data_in_io_num = I2S_SD_PIN
    };
    i2s_driver_install(I2S_PORT, &i2s_config, 0, NULL);
    i2s_set_pin(I2S_PORT, &pin_config);
    i2s_set_clk(I2S_PORT, I2S_SAMPLE_RATE, I2S_BITS_PER_SAMPLE_32BIT, I2S_CHANNEL_MONO);
}

float readNoiseLevel() {
    size_t bytesRead = 0;
    i2s_read(I2S_PORT, (char*)i2s_raw_samples, sizeof(i2s_raw_samples), &bytesRead, portMAX_DELAY);
    int samplesCount = bytesRead / sizeof(int32_t);
    if (samplesCount <= 0) return 30.0;

    double sumSquares = 0.0;
    for (int i = 0; i < samplesCount; i++) {
        int32_t sample = i2s_raw_samples[i] >> 8;
        sumSquares += ((double)sample * (double)sample);
    }
    double rms = sqrt(sumSquares / samplesCount);
    float db = (rms > 10.0) ? (20.0 * log10(rms) - 20.0 + calibOffset) : 30.0;
    return constrain(db, 30.0, 120.0);
}

void updateLedStatus(float db) {
    if (db >= thresholdRed) {
        digitalWrite(PIN_LED_MERAH, HIGH);
        digitalWrite(PIN_LED_KUNING, LOW);
        digitalWrite(PIN_LED_HIJAU, LOW);
        currentStatus = "danger";
    } else if (db >= thresholdGreen) {
        digitalWrite(PIN_LED_MERAH, LOW);
        digitalWrite(PIN_LED_KUNING, HIGH);
        digitalWrite(PIN_LED_HIJAU, LOW);
        currentStatus = "warning";
    } else {
        digitalWrite(PIN_LED_MERAH, LOW);
        digitalWrite(PIN_LED_KUNING, LOW);
        digitalWrite(PIN_LED_HIJAU, HIGH);
        currentStatus = "safe";
    }
}

void drawOledDisplay(float db, float peak) {
    u8g2.clearBuffer();
    u8g2.setFont(u8g2_font_6x10_tf);
    u8g2.drawStr(0, 9, "PEMBDA NOISE MONITOR");
    u8g2.drawStr(108, 9, WiFi.status() == WL_CONNECTED ? "WIFI" : "!OFF");
    u8g2.drawHLine(0, 11, 128);

    u8g2.setFont(u8g2_font_logisoso16_tf);
    char dbStr[16];
    dtostrf(db, 4, 1, dbStr);
    strcat(dbStr, " dB");
    u8g2.drawStr(0, 31, dbStr);

    u8g2.setFont(u8g2_font_6x12_tf);
    if (currentStatus == "danger") {
        u8g2.drawRBox(74, 16, 54, 17, 3);
        u8g2.setDrawColor(0);
        u8g2.drawStr(78, 29, "!RIBAT!");
        u8g2.setDrawColor(1);
    } else if (currentStatus == "warning") {
        u8g2.drawRFrame(74, 16, 54, 17, 3);
        u8g2.drawStr(77, 29, "SEDANG");
    } else {
        u8g2.drawRFrame(74, 16, 54, 17, 3);
        u8g2.drawStr(82, 29, "AMAN");
    }

    u8g2.drawFrame(0, 36, 128, 9);
    int barWidth = map(constrain((int)db, 35, 100), 35, 100, 0, 124);
    if (barWidth > 0) u8g2.drawBox(2, 38, barWidth, 5);
    int redMarkerX = map((int)thresholdRed, 35, 100, 0, 124) + 2;
    u8g2.drawVLine(redMarkerX, 35, 11);

    u8g2.drawHLine(0, 48, 128);
    for (int i = 0; i < SPARKLINE_POINTS - 1; i++) {
        u8g2.drawLine(i * 4, 63 - sparkline[i], (i + 1) * 4, 63 - sparkline[i + 1]);
    }
    u8g2.setFont(u8g2_font_4x6_tf);
    u8g2.drawStr(86, 58, className.c_str());
    u8g2.sendBuffer();
}

void sendTelemetry(float db, float peak) {
    if (WiFi.status() != WL_CONNECTED) return;
    HTTPClient http;
    http.begin(SERVER_URL);
    http.addHeader("Content-Type", "application/json");
    http.addHeader("X-Device-Token", DEVICE_TOKEN);
    http.setTimeout(4000);

    StaticJsonDocument<256> doc;
    doc["token"] = DEVICE_TOKEN;
    doc["db"] = db;
    doc["peak_db"] = peak;
    doc["firmware"] = FIRMWARE_VER;
    String payload;
    serializeJson(doc, payload);

    int code = http.POST(payload);
    if (code == 200) {
        StaticJsonDocument<512> resDoc;
        if (!deserializeJson(resDoc, http.getString())) {
            if (resDoc.containsKey("threshold_green")) thresholdGreen = resDoc["threshold_green"];
            if (resDoc.containsKey("threshold_yellow")) thresholdYellow = resDoc["threshold_yellow"];
            if (resDoc.containsKey("threshold_red")) thresholdRed = resDoc["threshold_red"];
            if (resDoc.containsKey("classroom")) className = resDoc["classroom"].as<String>();
        }
    }
    http.end();
}

void setup() {
    Serial.begin(115200);
    pinMode(PIN_LED_HIJAU, OUTPUT);
    pinMode(PIN_LED_KUNING, OUTPUT);
    pinMode(PIN_LED_MERAH, OUTPUT);

    u8g2.begin();
    u8g2.clearBuffer();
    u8g2.setFont(u8g2_font_6x12_tf);
    u8g2.drawStr(12, 20, "PEMBDA NOISE MONITOR");
    u8g2.drawStr(16, 36, "Menginisialisasi...");
    u8g2.sendBuffer();

    setupI2S();
    for (int i = 0; i < SPARKLINE_POINTS; i++) sparkline[i] = 2;

    WiFi.mode(WIFI_STA);
    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
}

void loop() {
    float db = readNoiseLevel();
    currentDb = (currentDb * 0.70) + (db * 0.30);
    peakDb = max(db, (float)(peakDb * 0.98));

    updateLedStatus(currentDb);

    static unsigned long lastSpark = 0;
    if (millis() - lastSpark >= 100) {
        lastSpark = millis();
        uint8_t h = map(constrain((int)currentDb, 35, 95), 35, 95, 1, 14);
        for (int i = 0; i < SPARKLINE_POINTS - 1; i++) sparkline[i] = sparkline[i + 1];
        sparkline[SPARKLINE_POINTS - 1] = h;
    }

    static unsigned long lastDisp = 0;
    if (millis() - lastDisp >= 80) {
        lastDisp = millis();
        drawOledDisplay(currentDb, peakDb);
    }

    static bool prevDanger = false;
    bool isDanger = (currentStatus == "danger");
    if (millis() - lastTelemetryTime >= TELEMETRY_INTERVAL || (isDanger && !prevDanger)) {
        lastTelemetryTime = millis();
        sendTelemetry(currentDb, peakDb);
    }
    prevDanger = isDanger;
    delay(20);
}`;

            navigator.clipboard.writeText(fullFirmware).then(() => {
                alert('✔ Seluruh Source Code Arduino ESP32 berhasil disalin ke clipboard! Siap di-paste ke Arduino IDE.');
            });
        }
    </script>
</body>
</html>
