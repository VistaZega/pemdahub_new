<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PembdaHUB Simulator Lab - Laboratorium Virtual Sains, IoT &amp; Kejuruan</title>
    <meta name="description" content="Simulator Lab PembdaHUB — Laboratorium virtual interaktif untuk Elektronika IoT, Gerbang Logika, Hukum Ohm, dan Subnetting Jaringan Komputer.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0b0f19;
            color: #f1f5f9;
        }
        .font-mono-lab {
            font-family: 'JetBrains Mono', monospace;
        }
        .bg-grid-pattern {
            background-size: 32px 32px;
            background-image:
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }
        .lab-card {
            background: rgba(15, 23, 42, 0.88);
            border: 1px solid rgba(148, 163, 184, 0.2);
            transition: all 0.25s ease;
        }
        .lab-card:hover {
            border-color: rgba(52, 211, 153, 0.55);
            transform: translateY(-2px);
            box-shadow: 0 12px 30px -10px rgba(16, 185, 129, 0.22);
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-grid-pattern min-h-screen flex flex-col justify-between antialiased" x-data="simLabApp()">

    @php
        $backUrl = url('/');
        $backLabel = 'Beranda';
        if (auth()->check()) {
            $u = auth()->user();
            if ($u->student) {
                $backUrl = route('siswa.lms.index');
                $backLabel = 'LMS Siswa';
            } elseif ($u->teacher) {
                $backUrl = route('guru.lms.index');
                $backLabel = 'LMS Guru';
            }
        }
    @endphp

    <!-- Header Navigation -->
    <header class="border-b border-slate-800 bg-slate-900/90 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-3">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-slate-950 font-black text-lg shadow-lg">
                    <i class="fas fa-flask"></i>
                </div>
                <div>
                    <span class="text-lg sm:text-xl font-extrabold text-white">PembdaHUB</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-950 text-emerald-300 border border-emerald-700 font-bold ml-1">Simulator Lab</span>
                </div>
            </a>

            <div class="flex items-center gap-2.5">
                @if(auth()->check() && auth()->user()->student)
                <a href="{{ route('siswa.ai.index', ['mode' => 'tutor', 'prompt' => 'Bantu saya memahami eksperimen elektronika, gerbang logika, hukum Ohm, atau subnetting jaringan di Simulator Lab.']) }}"
                   class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold transition">
                    <i class="fas fa-robot text-amber-300"></i>
                    <span>Tanya AI</span>
                </a>
                @endif
                <a href="{{ route('simlab.editor', 'new') }}" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold text-sm transition inline-flex items-center gap-1.5">
                    <i class="fas fa-plus"></i>
                    <span class="hidden sm:inline">Proyek IoT Baru</span>
                </a>
                <a href="{{ $backUrl }}" class="px-3.5 py-2 rounded-xl bg-slate-800 text-slate-200 hover:text-white hover:bg-slate-700 transition text-sm font-semibold">
                    <i class="fas fa-arrow-left mr-1"></i> {{ $backLabel }}
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-grow space-y-8">

        <!-- Hero Section -->
        <div class="relative rounded-3xl p-6 sm:p-9 overflow-hidden border border-slate-800 bg-gradient-to-br from-slate-900 via-emerald-950/30 to-indigo-950/40 shadow-2xl">
            <div class="max-w-3xl space-y-3">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs font-bold uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>Laboratorium Virtual SMP &bull; SMA &bull; 5 Rumpun SMK</span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-extrabold text-white leading-tight">
                    PembdaHUB <span class="text-emerald-400">Simulator Lab</span>
                </h1>
                <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
                    Praktikum interaktif langsung di browser tanpa alat fisik: rancang sirkuit mikrokontroler IoT (Arduino &amp; ESP32), uji Hukum Ohm, simulasikan Gerbang Logika Digital, hingga hitung Subnetting IPv4 untuk TKJ.
                </p>
            </div>
        </div>

        <!-- Lab Category Navigation Tabs -->
        <div class="flex flex-wrap gap-2 bg-slate-900/90 p-2 rounded-2xl border border-slate-800">
            <button type="button" @click="activeTab = 'all'"
                    :class="activeTab === 'all' ? 'bg-emerald-500 text-slate-950' : 'text-slate-300 hover:bg-slate-800'"
                    class="px-4 py-2.5 rounded-xl text-sm font-bold transition cursor-pointer flex items-center gap-2">
                <i class="fas fa-th-large"></i> Semua Lab
            </button>
            <button type="button" @click="activeTab = 'iot'"
                    :class="activeTab === 'iot' ? 'bg-cyan-500 text-slate-950' : 'text-slate-300 hover:bg-slate-800'"
                    class="px-4 py-2.5 rounded-xl text-sm font-bold transition cursor-pointer flex items-center gap-2">
                <i class="fas fa-microchip"></i> Elektronika &amp; IoT (TE/TKR/TSM)
            </button>
            <button type="button" @click="activeTab = 'ohm'"
                    :class="activeTab === 'ohm' ? 'bg-amber-400 text-slate-950' : 'text-slate-300 hover:bg-slate-800'"
                    class="px-4 py-2.5 rounded-xl text-sm font-bold transition cursor-pointer flex items-center gap-2">
                <i class="fas fa-bolt"></i> Lab Listrik &amp; Hukum Ohm (IPA/Fisika)
            </button>
            <button type="button" @click="activeTab = 'logic'"
                    :class="activeTab === 'logic' ? 'bg-violet-400 text-slate-950' : 'text-slate-300 hover:bg-slate-800'"
                    class="px-4 py-2.5 rounded-xl text-sm font-bold transition cursor-pointer flex items-center gap-2">
                <i class="fas fa-project-diagram"></i> Gerbang Logika Digital
            </button>
            <button type="button" @click="activeTab = 'network'"
                    :class="activeTab === 'network' ? 'bg-sky-400 text-slate-950' : 'text-slate-300 hover:bg-slate-800'"
                    class="px-4 py-2.5 rounded-xl text-sm font-bold transition cursor-pointer flex items-center gap-2">
                <i class="fas fa-network-wired"></i> Lab Subnetting IPv4 (TKJ/ACP)
            </button>
        </div>

        <!-- ========================================================= -->
        <!-- SECTION 1: LAB HUKUM OHM & DAYA LISTRIK INTERAKTIF        -->
        <!-- ========================================================= -->
        <section x-show="activeTab === 'all' || activeTab === 'ohm'" x-cloak class="lab-card rounded-3xl p-6 sm:p-8 space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-800 pb-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-amber-400 text-slate-950 flex items-center justify-center text-xl font-black shrink-0">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-400">Lab Fisika / IPA &amp; Kelistrikan Dasar</span>
                        <h2 class="text-xl font-extrabold text-white">Simulator Hukum Ohm &amp; Daya Listrik ($V = I \times R$)</h2>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold"
                      :class="ohmCurrentMa > 500 ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40'"
                      x-text="ohmCurrentMa > 500 ? '⚠️ Arus Tinggi (>500 mA) - Hati-hati Overcurrent!' : '✅ Kondisi Rangkaian Aman'"></span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                <div class="lg:col-span-6 space-y-5">
                    <div>
                        <div class="flex justify-between text-sm font-bold mb-2">
                            <span class="text-slate-200">Tegangan Sumber ($V$)</span>
                            <span class="font-mono-lab text-amber-400" x-text="ohmVoltage + ' Volt'"></span>
                        </div>
                        <input type="range" min="1" max="24" step="0.5" x-model.number="ohmVoltage" class="w-full accent-amber-400 cursor-pointer">
                    </div>
                    <div>
                        <div class="flex justify-between text-sm font-bold mb-2">
                            <span class="text-slate-200">Hambatan Beban ($R$)</span>
                            <span class="font-mono-lab text-cyan-400" x-text="ohmResistance + ' Ohm (Ω)'"></span>
                        </div>
                        <input type="range" min="10" max="1000" step="10" x-model.number="ohmResistance" class="w-full accent-cyan-400 cursor-pointer">
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800 text-sm text-slate-300 leading-relaxed">
                        <strong class="text-white">Rumus:</strong> $I = \frac{V}{R}$ dan $P = V \times I$. Naikkan tegangan atau turunkan hambatan untuk melihat peningkatan arus listrik dan kecerahan lampu virtual.
                    </div>
                </div>

                <div class="lg:col-span-6 grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 text-center">
                        <div class="text-xs font-bold text-slate-400 uppercase">Arus ($I$)</div>
                        <div class="text-2xl font-extrabold font-mono-lab text-cyan-400 mt-1" x-text="ohmCurrentA.toFixed(3) + ' A'"></div>
                        <div class="text-xs text-slate-400 mt-0.5" x-text="ohmCurrentMa.toFixed(1) + ' mA'"></div>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 text-center">
                        <div class="text-xs font-bold text-slate-400 uppercase">Daya ($P$)</div>
                        <div class="text-2xl font-extrabold font-mono-lab text-amber-400 mt-1" x-text="ohmPowerW.toFixed(2) + ' W'"></div>
                        <div class="text-xs text-slate-400 mt-0.5">Watt</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 text-center col-span-2 sm:col-span-1 flex flex-col items-center justify-center">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center transition-all duration-300"
                             :style="'background: rgba(251, 191, 36, ' + Math.min(1, ohmPowerW / 8) + '); box-shadow: 0 0 ' + Math.min(35, ohmPowerW * 5) + 'px rgba(251, 191, 36, 0.8);'">
                            <i class="fas fa-lightbulb text-xl" :class="ohmPowerW > 0.3 ? 'text-slate-950' : 'text-slate-500'"></i>
                        </div>
                        <div class="text-xs font-bold text-slate-300 mt-2">Indikator Beban</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================================= -->
        <!-- SECTION 2: LAB GERBANG LOGIKA DIGITAL                     -->
        <!-- ========================================================= -->
        <section x-show="activeTab === 'all' || activeTab === 'logic'" x-cloak class="lab-card rounded-3xl p-6 sm:p-8 space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-800 pb-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-violet-500 text-white flex items-center justify-center text-xl font-black shrink-0">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-violet-400">Lab Teknik Elektronika &amp; Informatika</span>
                        <h2 class="text-xl font-extrabold text-white">Simulator Gerbang Logika Digital</h2>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <template x-for="g in ['AND', 'OR', 'NAND', 'NOR', 'XOR', 'NOT']" :key="g">
                        <button type="button" @click="logicGate = g"
                                :class="logicGate === g ? 'bg-violet-500 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold font-mono-lab transition cursor-pointer" x-text="g"></button>
                    </template>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                <!-- Inputs -->
                <div class="space-y-3">
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-bold text-white">Input A</div>
                            <div class="text-xs text-slate-400" x-text="logicA ? 'HIGH (1 / 5V)' : 'LOW (0 / 0V)'"></div>
                        </div>
                        <button type="button" @click="logicA = logicA ? 0 : 1"
                                :class="logicA ? 'bg-emerald-500 text-slate-950' : 'bg-slate-800 text-slate-300'"
                                class="w-14 h-9 rounded-xl font-mono-lab font-extrabold text-sm transition cursor-pointer" x-text="logicA"></button>
                    </div>

                    <div x-show="logicGate !== 'NOT'" class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-bold text-white">Input B</div>
                            <div class="text-xs text-slate-400" x-text="logicB ? 'HIGH (1 / 5V)' : 'LOW (0 / 0V)'"></div>
                        </div>
                        <button type="button" @click="logicB = logicB ? 0 : 1"
                                :class="logicB ? 'bg-emerald-500 text-slate-950' : 'bg-slate-800 text-slate-300'"
                                class="w-14 h-9 rounded-xl font-mono-lab font-extrabold text-sm transition cursor-pointer" x-text="logicB"></button>
                    </div>
                </div>

                <!-- Gate Visual & Output -->
                <div class="p-6 rounded-2xl bg-slate-950 border border-slate-800 text-center space-y-3">
                    <div class="text-xs font-bold text-violet-400 uppercase">Gerbang Aktif</div>
                    <div class="text-3xl font-extrabold font-mono-lab text-white" x-text="logicGate"></div>
                    <div class="pt-2 flex flex-col items-center">
                        <div class="w-14 h-14 rounded-full border-4 flex items-center justify-center font-mono-lab text-xl font-black transition-all"
                             :class="logicOutput ? 'bg-emerald-400 border-emerald-200 text-slate-950 shadow-[0_0_25px_rgba(52,211,153,0.7)]' : 'bg-slate-800 border-slate-700 text-slate-400'"
                             x-text="logicOutput"></div>
                        <div class="text-xs font-bold mt-2" :class="logicOutput ? 'text-emerald-400' : 'text-slate-400'"
                             x-text="logicOutput ? 'OUTPUT: HIGH (1 / Menyala)' : 'OUTPUT: LOW (0 / Mati)'"></div>
                    </div>
                </div>

                <!-- Truth Table -->
                <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800">
                    <div class="text-xs font-bold text-slate-400 uppercase mb-2">Tabel Kebenaran (<span x-text="logicGate"></span>)</div>
                    <table class="w-full text-xs font-mono-lab text-center">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-800">
                                <th class="py-1.5">A</th>
                                <th class="py-1.5" x-show="logicGate !== 'NOT'">B</th>
                                <th class="py-1.5 text-violet-400">OUT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, idx) in truthTable" :key="idx">
                                <tr class="border-b border-slate-900"
                                    :class="(row.a === logicA && (logicGate === 'NOT' || row.b === logicB)) ? 'bg-violet-500/20 text-white font-bold' : 'text-slate-400'">
                                    <td class="py-1.5" x-text="row.a"></td>
                                    <td class="py-1.5" x-show="logicGate !== 'NOT'" x-text="row.b"></td>
                                    <td class="py-1.5" :class="row.out ? 'text-emerald-400' : 'text-rose-400'" x-text="row.out"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ========================================================= -->
        <!-- SECTION 3: LAB SUBNETTING IPv4 (TKJ / TJKT / ACP)         -->
        <!-- ========================================================= -->
        <section x-show="activeTab === 'all' || activeTab === 'network'" x-cloak class="lab-card rounded-3xl p-6 sm:p-8 space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-800 pb-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-sky-500 text-slate-950 flex items-center justify-center text-xl font-black shrink-0">
                        <i class="fas fa-network-wired"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-sky-400">Lab TKJ / TJKT / Axioo Class Program</span>
                        <h2 class="text-xl font-extrabold text-white">Simulator Subnetting IPv4 &amp; CIDR</h2>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <div class="lg:col-span-5 space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-200 mb-1.5">Alamat IPv4</label>
                        <input type="text" x-model="ipInput" placeholder="192.168.10.1"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white font-mono-lab text-sm focus:border-sky-400 outline-none">
                    </div>
                    <div>
                        <div class="flex justify-between text-sm font-bold mb-1.5">
                            <span class="text-slate-200">Prefix CIDR</span>
                            <span class="font-mono-lab text-sky-400" x-text="'/' + cidrPrefix"></span>
                        </div>
                        <input type="range" min="16" max="30" step="1" x-model.number="cidrPrefix" class="w-full accent-sky-400 cursor-pointer">
                    </div>
                </div>

                <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800">
                        <div class="text-xs font-bold text-slate-400 uppercase">Network Address</div>
                        <div class="text-base font-extrabold font-mono-lab text-sky-400 mt-1" x-text="subnetInfo.network"></div>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800">
                        <div class="text-xs font-bold text-slate-400 uppercase">Broadcast Address</div>
                        <div class="text-base font-extrabold font-mono-lab text-amber-400 mt-1" x-text="subnetInfo.broadcast"></div>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800">
                        <div class="text-xs font-bold text-slate-400 uppercase">Subnet Mask</div>
                        <div class="text-base font-extrabold font-mono-lab text-white mt-1" x-text="subnetInfo.mask"></div>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800">
                        <div class="text-xs font-bold text-slate-400 uppercase">Host Usable</div>
                        <div class="text-base font-extrabold font-mono-lab text-emerald-400 mt-1" x-text="subnetInfo.hosts + ' Host (' + subnetInfo.range + ')'"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================================= -->
        <!-- SECTION 4: LAB MIKROKONTROLER & IOT (ARDUINO / ESP32)     -->
        <!-- ========================================================= -->
        <section x-show="activeTab === 'all' || activeTab === 'iot'" x-cloak class="space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-extrabold text-white flex items-center gap-2">
                        <i class="fas fa-microchip text-cyan-400"></i> Studio Mikrokontroler &amp; IoT (Arduino / ESP32)
                    </h2>
                    <p class="text-sm text-slate-400">Rancang rangkaian elektronika visual dan tulis kode C++ langsung di editor interaktif</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <a href="{{ route('simlab.editor', 'blink') }}" class="lab-card rounded-2xl p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-cyan-500 text-slate-950 flex items-center justify-center text-xl"><i class="fas fa-microchip"></i></div>
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-950 text-emerald-300 border border-emerald-700 text-xs font-bold">Uno</span>
                        </div>
                        <h3 class="text-lg font-extrabold text-white mb-1.5">Arduino Uno Studio</h3>
                        <p class="text-slate-300 text-sm mb-4">Simulasi ATmega328P dengan 14 Digital I/O, 6 Analog In, dan 23+ modul sensor.</p>
                    </div>
                    <span class="text-sm font-bold text-cyan-400">Buka Studio Uno &rarr;</span>
                </a>

                <a href="{{ route('simlab.editor', 'ultrasonic') }}" class="lab-card rounded-2xl p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-emerald-500 text-slate-950 flex items-center justify-center text-xl"><i class="fas fa-robot"></i></div>
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-950 text-emerald-300 border border-emerald-700 text-xs font-bold">Nano</span>
                        </div>
                        <h3 class="text-lg font-extrabold text-white mb-1.5">Arduino Nano Robotika</h3>
                        <p class="text-slate-300 text-sm mb-4">Workspace ringkas untuk sensor ultrasonik, motor servo, dan kendali PWM.</p>
                    </div>
                    <span class="text-sm font-bold text-emerald-400">Buka Studio Nano &rarr;</span>
                </a>

                <a href="{{ route('simlab.editor', 'dht11') }}" class="lab-card rounded-2xl p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center text-xl"><i class="fas fa-wifi"></i></div>
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-950 text-amber-300 border border-amber-700 text-xs font-bold">ESP32</span>
                        </div>
                        <h3 class="text-lg font-extrabold text-white mb-1.5">ESP32 IoT &amp; Smart Device</h3>
                        <p class="text-slate-300 text-sm mb-4">Simulasi mikrokontroler dual-core Wi-Fi/BLE untuk sistem smart home &amp; telemetri.</p>
                    </div>
                    <span class="text-sm font-bold text-amber-400">Buka Studio ESP32 &rarr;</span>
                </a>
            </div>

            <!-- Starter Kits -->
            <div class="lab-card rounded-3xl p-6 space-y-4">
                <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                    <i class="fas fa-shapes text-amber-400"></i> Template &amp; Starter Kits Praktikum
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach([
                        ['slug' => 'blink', 'board' => 'Uno', 'title' => 'LED Blink Basic', 'desc' => 'Kedip LED pada Pin D13 dengan resistor 300Ω.'],
                        ['slug' => 'ultrasonic-lcd', 'board' => 'Uno', 'title' => 'Sensor HC-SR04 & LCD 16x2', 'desc' => 'Pengukuran jarak ultrasonik tampil di LCD I2C.'],
                        ['slug' => 'servo-sweep', 'board' => 'Nano', 'title' => 'Motor Servo SG90 Sweep', 'desc' => 'Kontrol sudut motor servo dari 0° hingga 180°.'],
                        ['slug' => 'dht11-esp32', 'board' => 'ESP32', 'title' => 'DHT11 Suhu & Serial Monitor', 'desc' => 'Membaca suhu & kelembaban lingkungan real-time.'],
                        ['slug' => 'l298n-motor', 'board' => 'Uno', 'title' => 'Motor DC & Driver L298N', 'desc' => 'Kontrol arah putaran & kecepatan PWM motor DC.'],
                        ['slug' => 'relay-pir', 'board' => 'ESP32', 'title' => 'Smart Home Relay & PIR', 'desc' => 'Otomatisasi saklar Relay 5V dengan sensor gerak.'],
                    ] as $tpl)
                    <a href="{{ route('simlab.editor', $tpl['slug']) }}" class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 hover:border-emerald-500/50 transition flex flex-col justify-between gap-2">
                        <div>
                            <span class="text-xs font-bold text-cyan-400">{{ $tpl['board'] }}</span>
                            <div class="text-base font-bold text-white mt-0.5">{{ $tpl['title'] }}</div>
                            <p class="text-xs sm:text-sm text-slate-400 mt-1">{{ $tpl['desc'] }}</p>
                        </div>
                        <span class="text-xs font-bold text-emerald-400">Mulai Praktik &rarr;</span>
                    </a>
                    @endforeach
                </div>
            </div>

            @if(auth()->check() && count($myProjects) > 0)
            <!-- My Saved Projects -->
            <div class="lab-card rounded-3xl p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-extrabold text-white flex items-center gap-2">
                        <i class="fas fa-folder-open text-emerald-400"></i> Proyek Tersimpan Saya ({{ count($myProjects) }})
                    </h3>
                    <a href="{{ route('simlab.editor', 'new') }}" class="text-sm font-bold text-emerald-400 hover:underline">+ Proyek Baru</a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($myProjects as $project)
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex flex-col justify-between gap-3">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="px-2 py-0.5 rounded bg-emerald-950 text-emerald-300 text-xs font-bold uppercase">{{ $project->board_type }}</span>
                                <span class="text-xs text-slate-400 font-mono-lab">{{ $project->updated_at->format('d M Y') }}</span>
                            </div>
                            <div class="text-base font-bold text-white">{{ $project->title }}</div>
                            <p class="text-xs sm:text-sm text-slate-400 mt-1">{{ $project->description ?: 'Proyek rangkaian mikrokontroler.' }}</p>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-slate-800">
                            <a href="{{ route('simlab.editor', $project->id) }}" class="px-3 py-1.5 rounded-lg bg-emerald-500 text-slate-950 text-xs font-extrabold">Buka Editor</a>
                            <form method="POST" action="{{ route('simlab.destroy', $project->id) }}" onsubmit="return confirm('Yakin ingin menghapus proyek ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-500/15 text-rose-300 text-xs font-bold cursor-pointer">Hapus</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </section>
    </main>

    <footer class="border-t border-slate-800 bg-slate-950 py-5 text-center text-sm text-slate-400">
        &copy; 2026 PembdaHUB Simulator Lab &bull; Yayasan Perguruan PEMBDA Nias
    </footer>

    <script>
        function simLabApp() {
            return {
                activeTab: 'all',

                // Hukum Ohm
                ohmVoltage: 12,
                ohmResistance: 220,
                get ohmCurrentA() {
                    return this.ohmVoltage / Math.max(1, this.ohmResistance);
                },
                get ohmCurrentMa() {
                    return this.ohmCurrentA * 1000;
                },
                get ohmPowerW() {
                    return this.ohmVoltage * this.ohmCurrentA;
                },

                // Gerbang Logika
                logicGate: 'AND',
                logicA: 1,
                logicB: 0,
                evalGate(gate, a, b) {
                    if (gate === 'AND') return (a && b) ? 1 : 0;
                    if (gate === 'OR') return (a || b) ? 1 : 0;
                    if (gate === 'NAND') return !(a && b) ? 1 : 0;
                    if (gate === 'NOR') return !(a || b) ? 1 : 0;
                    if (gate === 'XOR') return (a ^ b) ? 1 : 0;
                    if (gate === 'NOT') return a ? 0 : 1;
                    return 0;
                },
                get logicOutput() {
                    return this.evalGate(this.logicGate, this.logicA, this.logicB);
                },
                get truthTable() {
                    if (this.logicGate === 'NOT') {
                        return [
                            { a: 0, b: 0, out: this.evalGate('NOT', 0, 0) },
                            { a: 1, b: 0, out: this.evalGate('NOT', 1, 0) },
                        ];
                    }
                    return [
                        { a: 0, b: 0, out: this.evalGate(this.logicGate, 0, 0) },
                        { a: 0, b: 1, out: this.evalGate(this.logicGate, 0, 1) },
                        { a: 1, b: 0, out: this.evalGate(this.logicGate, 1, 0) },
                        { a: 1, b: 1, out: this.evalGate(this.logicGate, 1, 1) },
                    ];
                },

                // Subnetting IPv4
                ipInput: '192.168.10.25',
                cidrPrefix: 24,
                get subnetInfo() {
                    const parts = this.ipInput.trim().split('.').map(n => parseInt(n, 10));
                    if (parts.length !== 4 || parts.some(n => isNaN(n) || n < 0 || n > 255)) {
                        return { network: 'IP Tidak Valid', broadcast: '-', mask: '-', hosts: 0, range: '-' };
                    }
                    const ipInt = ((parts[0] << 24) | (parts[1] << 16) | (parts[2] << 8) | parts[3]) >>> 0;
                    const maskInt = this.cidrPrefix === 0 ? 0 : ((~0 << (32 - this.cidrPrefix)) >>> 0);
                    const netInt = (ipInt & maskInt) >>> 0;
                    const bcastInt = (netInt | (~maskInt >>> 0)) >>> 0;
                    const toIp = n => [(n >>> 24) & 255, (n >>> 16) & 255, (n >>> 8) & 255, n & 255].join('.');
                    const hosts = Math.max(0, Math.pow(2, 32 - this.cidrPrefix) - 2);
                    const firstHost = hosts > 0 ? toIp((netInt + 1) >>> 0) : '-';
                    const lastHost = hosts > 0 ? toIp((bcastInt - 1) >>> 0) : '-';
                    return {
                        network: toIp(netInt),
                        broadcast: toIp(bcastInt),
                        mask: toIp(maskInt),
                        hosts: hosts,
                        range: hosts > 0 ? (firstHost + ' – ' + lastHost) : '-'
                    };
                }
            };
        }
    </script>
</body>
</html>
