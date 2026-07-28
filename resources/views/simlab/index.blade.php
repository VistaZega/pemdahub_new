<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PembdaHUB Simulator Lab - Laboratorium Virtual Interaktif</title>
    <meta name="description" content="Simulator Lab PembdaHUB — kumpulan simulator interaktif untuk pembelajaran sains dan teknologi: Mikrokontroler, Gerbang Logika, dan lainnya.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Fira+Code:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0b0f19;
            color: #f3f4f6;
        }
        .bg-grid-pattern {
            background-size: 30px 30px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }
        .glow-emerald { box-shadow: 0 0 25px -5px rgba(16, 185, 129, 0.3); }
        .glass-card {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }
        .glass-card:hover {
            border-color: rgba(16, 185, 129, 0.4);
            transform: translateY(-2px);
        }
        .sim-card {
            background: rgba(17, 24, 39, 0.85);
            backdrop-filter: blur(16px);
            border: 2px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }
        .sim-card:hover {
            border-color: rgba(16, 185, 129, 0.5);
            transform: translateY(-4px);
            box-shadow: 0 12px 40px -10px rgba(16, 185, 129, 0.2);
        }
        .sim-card.disabled {
            opacity: 0.5;
            pointer-events: none;
        }
    </style>
</head>
<body class="bg-grid-pattern min-h-screen flex flex-col justify-between antialiased selection:bg-emerald-500 selection:text-black">

    <!-- Header Navigation -->
    <header class="border-b border-gray-800 bg-gray-900/80 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="{{ url('/') }}" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-black font-black text-xl shadow-lg shadow-emerald-500/20 group-hover:scale-105 transition-transform">
                        <i class="fas fa-flask"></i>
                    </div>
                    <div>
                        <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-400 via-teal-300 to-cyan-400">PembdaHUB</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-950 text-emerald-400 border border-emerald-800 font-semibold ml-1">Simulator Lab</span>
                    </div>
                </a>
            </div>

            <div class="flex items-center space-x-3">
                <a href="{{ route('simlab.editor', 'new') }}" class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-black font-bold text-sm shadow-lg shadow-emerald-500/25 transition-all flex items-center space-x-2">
                    <i class="fas fa-plus"></i>
                    <span>Buat Proyek Baru</span>
                </a>
                <a href="{{ url('/') }}" class="px-3 py-2 rounded-xl bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition-colors text-sm font-medium">
                    <i class="fas fa-arrow-left mr-1"></i> Beranda
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full flex-grow">
        <!-- Hero Section -->
        <div class="relative rounded-3xl p-8 sm:p-12 overflow-hidden mb-12 border border-gray-800 bg-gradient-to-br from-gray-900 via-emerald-950/20 to-gray-900 glow-emerald">
            <div class="absolute -right-20 -bottom-20 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 max-w-3xl">
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold mb-4">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>Laboratorium Virtual Interaktif 100% Mandiri</span>
                </div>
                <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-white mb-4">
                    Simulator <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-400 to-cyan-400">Lab</span>
                </h1>
                <p class="text-gray-400 text-base sm:text-lg mb-6 leading-relaxed">
                    Kumpulan simulator interaktif untuk pembelajaran sains dan teknologi. Pilih simulator di bawah untuk mulai bereksperimen langsung di browser — tanpa perangkat keras fisik.
                </p>
            </div>
        </div>

        <!-- Simulator Cards Grid -->
        <div class="mb-12">
            <h2 class="text-sm uppercase tracking-wider text-gray-500 font-bold mb-6 flex items-center gap-2">
                <i class="fas fa-cubes text-emerald-400"></i> Pilih Simulator
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Mikrokontroler Simulator (ACTIVE) -->
                <a href="{{ route('simlab.editor', 'new') }}" class="sim-card rounded-2xl p-6 flex flex-col justify-between group cursor-pointer">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white text-2xl shadow-lg shadow-cyan-500/20">
                                <i class="fas fa-microchip"></i>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-emerald-950 text-emerald-400 border border-emerald-700 text-[10px] font-bold uppercase tracking-wider">Aktif</span>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2 group-hover:text-emerald-400 transition-colors">Mikrokontroler Simulator</h3>
                        <p class="text-gray-400 text-sm mb-4">Rancang rangkaian elektronika, tulis kode Arduino C++, dan jalankan simulasi mikrokontroler secara real-time di browser.</p>
                        <div class="flex flex-wrap gap-1.5 mb-3">
                            <span class="px-2 py-0.5 rounded bg-gray-800 text-[10px] text-cyan-400 font-bold border border-gray-700">Arduino Uno</span>
                            <span class="px-2 py-0.5 rounded bg-gray-800 text-[10px] text-emerald-400 font-bold border border-gray-700">Arduino Nano</span>
                            <span class="px-2 py-0.5 rounded bg-gray-800 text-[10px] text-amber-400 font-bold border border-gray-700">ESP32</span>
                            <span class="px-2 py-0.5 rounded bg-gray-800 text-[10px] text-gray-400 font-mono border border-gray-700">23+ Komponen</span>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-gray-700/50 flex items-center justify-between">
                        <span class="text-xs text-gray-500"><i class="fas fa-tools text-emerald-400 mr-1"></i> Rangkaian + Kode + Simulasi</span>
                        <span class="px-3 py-1 rounded-lg bg-emerald-500 text-black text-xs font-bold flex items-center gap-1 group-hover:bg-emerald-400">
                            Mulai <i class="fas fa-arrow-right text-[10px]"></i>
                        </span>
                    </div>
                </a>

                <!-- Logic Gate Simulator (COMING SOON) -->
                <div class="sim-card disabled rounded-2xl p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-purple-500 to-violet-600 flex items-center justify-center text-white text-2xl shadow-lg shadow-purple-500/20">
                                <i class="fas fa-project-diagram"></i>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-gray-800 text-gray-500 border border-gray-700 text-[10px] font-bold uppercase tracking-wider">Segera Hadir</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-400 mb-2">Gerbang Logika Simulator</h3>
                        <p class="text-gray-500 text-sm mb-4">Bangun dan simulasikan rangkaian gerbang logika digital (AND, OR, NOT, NAND, NOR, XOR, XNOR) secara visual.</p>
                    </div>
                    <div class="pt-4 border-t border-gray-800 flex items-center justify-between">
                        <span class="text-xs text-gray-600"><i class="fas fa-clock mr-1"></i> Dalam Pengembangan</span>
                    </div>
                </div>

                <!-- Sensor Playground (COMING SOON) -->
                <div class="sim-card disabled rounded-2xl p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-600 flex items-center justify-center text-white text-2xl shadow-lg shadow-amber-500/20">
                                <i class="fas fa-temperature-high"></i>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-gray-800 text-gray-500 border border-gray-700 text-[10px] font-bold uppercase tracking-wider">Segera Hadir</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-400 mb-2">Sensor Playground</h3>
                        <p class="text-gray-500 text-sm mb-4">Eksplorasi dan pelajari karakteristik berbagai sensor (suhu, jarak, cahaya, gerak) secara interaktif tanpa kode.</p>
                    </div>
                    <div class="pt-4 border-t border-gray-800 flex items-center justify-between">
                        <span class="text-xs text-gray-600"><i class="fas fa-clock mr-1"></i> Dalam Pengembangan</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hardware Badges -->
        <div class="mb-12">
            <h2 class="text-sm uppercase tracking-wider text-gray-500 font-bold mb-4 flex items-center gap-2">
                <i class="fas fa-microchip text-emerald-400"></i> Komponen & Sensor Didukung
            </h2>
            <div class="flex flex-wrap gap-3">
                <div class="px-3.5 py-2 rounded-xl bg-gray-900 border border-gray-800 text-xs font-bold text-cyan-400 flex items-center gap-2">
                    <i class="fas fa-microchip text-base"></i> Arduino Uno
                </div>
                <div class="px-3.5 py-2 rounded-xl bg-gray-900 border border-gray-800 text-xs font-bold text-emerald-400 flex items-center gap-2">
                    <i class="fas fa-microchip text-base"></i> Arduino Nano
                </div>
                <div class="px-3.5 py-2 rounded-xl bg-gray-900 border border-gray-800 text-xs font-bold text-amber-400 flex items-center gap-2">
                    <i class="fas fa-wifi text-base"></i> ESP32 DevKit
                </div>
                <div class="px-3.5 py-2 rounded-xl bg-gray-800/50 border border-gray-700/50 text-xs font-medium text-gray-300">
                    HC-SR04 • DHT11 • Servo SG90 • L298N • RC522 RFID • LCD 16x2/20x4 • DFPlayer • Relay 5V • TCRT5000 • PIR • LDR • Buzzer • Motor DC • LED RGB
                </div>
            </div>
        </div>

        <!-- Template Projects Section -->
        <div class="mb-12">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <i class="fas fa-shapes text-amber-400"></i> Template & Starter Kits
                    </h2>
                    <p class="text-xs text-gray-400">Pilih template berikut untuk langsung mulai mencoba rangkaian & kode dasar</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Template 1: LED Blink -->
                <div class="glass-card rounded-2xl p-6 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-2.5 py-1 rounded-lg bg-cyan-950 text-cyan-400 border border-cyan-800 text-xs font-bold">Arduino Uno</span>
                            <span class="text-xs text-gray-500 font-mono">Starter #1</span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2 group-hover:text-emerald-400 transition-colors">LED Blink Basic</h3>
                        <p class="text-gray-400 text-xs mb-4">Simulasi dasar kedip LED pada Pin D13 dengan resistor 300 ohm dan fungsi delay().</p>
                    </div>
                    <div class="pt-4 border-t border-gray-800 flex items-center justify-between">
                        <span class="text-xs text-gray-500"><i class="fas fa-bolt text-amber-400 mr-1"></i>Pemula</span>
                        <a href="{{ route('simlab.editor', 'blink') }}" class="px-3.5 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 text-xs font-bold transition-colors flex items-center gap-1">
                            <span>Mulai Praktik</span> <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- Template 2: Ultrasonic + LCD -->
                <div class="glass-card rounded-2xl p-6 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-2.5 py-1 rounded-lg bg-cyan-950 text-cyan-400 border border-cyan-800 text-xs font-bold">Arduino Uno</span>
                            <span class="text-xs text-gray-500 font-mono">Starter #2</span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2 group-hover:text-emerald-400 transition-colors">Sensor HC-SR04 & LCD 16x2</h3>
                        <p class="text-gray-400 text-xs mb-4">Pengukuran jarak dengan sensor ultrasonic HC-SR04 dan ditampilkan ke layar LCD 16x2 I2C.</p>
                    </div>
                    <div class="pt-4 border-t border-gray-800 flex items-center justify-between">
                        <span class="text-xs text-gray-500"><i class="fas fa-bolt text-amber-400 mr-1"></i>Menengah</span>
                        <a href="{{ route('simlab.editor', 'ultrasonic-lcd') }}" class="px-3.5 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 text-xs font-bold transition-colors flex items-center gap-1">
                            <span>Mulai Praktik</span> <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- Template 3: Servo Sweep -->
                <div class="glass-card rounded-2xl p-6 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-2.5 py-1 rounded-lg bg-emerald-950 text-emerald-400 border border-emerald-800 text-xs font-bold">Arduino Nano</span>
                            <span class="text-xs text-gray-500 font-mono">Starter #3</span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2 group-hover:text-emerald-400 transition-colors">Motor Servo SG90 Sweep</h3>
                        <p class="text-gray-400 text-xs mb-4">Kontrol sudut pergerakan Motor Servo SG90 dari 0° hingga 180° secara kontinu.</p>
                    </div>
                    <div class="pt-4 border-t border-gray-800 flex items-center justify-between">
                        <span class="text-xs text-gray-500"><i class="fas fa-bolt text-amber-400 mr-1"></i>Pemula</span>
                        <a href="{{ route('simlab.editor', 'servo-sweep') }}" class="px-3.5 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 text-xs font-bold transition-colors flex items-center gap-1">
                            <span>Mulai Praktik</span> <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- Template 4: DHT11 ESP32 -->
                <div class="glass-card rounded-2xl p-6 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-2.5 py-1 rounded-lg bg-amber-950 text-amber-400 border border-amber-800 text-xs font-bold">ESP32 DevKit</span>
                            <span class="text-xs text-gray-500 font-mono">Starter #4</span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2 group-hover:text-emerald-400 transition-colors">DHT11 Suhu & Serial Monitor</h3>
                        <p class="text-gray-400 text-xs mb-4">Membaca kelembaban & suhu lingkungan dari sensor DHT11 lalu dikirim ke Serial Monitor.</p>
                    </div>
                    <div class="pt-4 border-t border-gray-800 flex items-center justify-between">
                        <span class="text-xs text-gray-500"><i class="fas fa-bolt text-amber-400 mr-1"></i>IoT Basic</span>
                        <a href="{{ route('simlab.editor', 'dht11-esp32') }}" class="px-3.5 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 text-xs font-bold transition-colors flex items-center gap-1">
                            <span>Mulai Praktik</span> <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- Template 5: Motor DC & L298N -->
                <div class="glass-card rounded-2xl p-6 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-2.5 py-1 rounded-lg bg-cyan-950 text-cyan-400 border border-cyan-800 text-xs font-bold">Arduino Uno</span>
                            <span class="text-xs text-gray-500 font-mono">Starter #5</span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2 group-hover:text-emerald-400 transition-colors">Motor DC & Driver L298N</h3>
                        <p class="text-gray-400 text-xs mb-4">Mengontrol arah putaran (CW/CCW) dan kecepatan PWM Motor DC menggunakan modul L298N.</p>
                    </div>
                    <div class="pt-4 border-t border-gray-800 flex items-center justify-between">
                        <span class="text-xs text-gray-500"><i class="fas fa-bolt text-amber-400 mr-1"></i>Robotika</span>
                        <a href="{{ route('simlab.editor', 'l298n-motor') }}" class="px-3.5 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 text-xs font-bold transition-colors flex items-center gap-1">
                            <span>Mulai Praktik</span> <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- Template 6: Relay & PIR -->
                <div class="glass-card rounded-2xl p-6 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-2.5 py-1 rounded-lg bg-amber-950 text-amber-400 border border-amber-800 text-xs font-bold">ESP32 DevKit</span>
                            <span class="text-xs text-gray-500 font-mono">Starter #6</span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2 group-hover:text-emerald-400 transition-colors">Smart Home Relay & PIR Gerak</h3>
                        <p class="text-gray-400 text-xs mb-4">Otomatisasi saklar Relay 5V berdasarkan deteksi pergerakan dari sensor PIR Motion detector.</p>
                    </div>
                    <div class="pt-4 border-t border-gray-800 flex items-center justify-between">
                        <span class="text-xs text-gray-500"><i class="fas fa-bolt text-amber-400 mr-1"></i>Smart System</span>
                        <a href="{{ route('simlab.editor', 'relay-pir') }}" class="px-3.5 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 text-xs font-bold transition-colors flex items-center gap-1">
                            <span>Mulai Praktik</span> <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->check() && count($myProjects) > 0)
        <!-- My Saved Projects Section -->
        <div class="mb-12">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <i class="fas fa-folder-open text-emerald-400"></i> Proyek Saya
                    </h2>
                    <p class="text-xs text-gray-400">Rangkaian dan kode yang pernah Anda simpan — klik untuk melanjutkan</p>
                </div>
                <a href="{{ route('simlab.editor', 'new') }}" class="px-3 py-1.5 rounded-lg bg-emerald-500/10 text-emerald-400 text-xs font-bold hover:bg-emerald-500/20 transition-colors flex items-center gap-1">
                    <i class="fas fa-plus text-[10px]"></i> Proyek Baru
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($myProjects as $project)
                <div class="glass-card rounded-2xl p-6 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2.5 py-1 rounded-lg bg-emerald-950 text-emerald-400 border border-emerald-800 text-xs font-bold uppercase">
                                {{ $project->board_type }}
                            </span>
                            <span class="text-[10px] text-gray-500 font-mono">{{ $project->updated_at->format('d M Y H:i') }}</span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2 group-hover:text-emerald-400 transition-colors">{{ $project->title }}</h3>
                        <p class="text-gray-400 text-xs mb-3 line-clamp-2">{{ $project->description ?: 'Tidak ada deskripsi' }}</p>
                        <div class="flex items-center gap-2 text-[10px] text-gray-500">
                            <span><i class="fas fa-code text-cyan-400 mr-0.5"></i> Tersimpan</span>
                            <span>•</span>
                            <span>{{ $project->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-gray-800 flex items-center justify-between">
                        <a href="{{ route('simlab.editor', $project->id) }}" class="px-3.5 py-1.5 rounded-lg bg-emerald-500 text-black text-xs font-bold hover:bg-emerald-400 transition-colors flex items-center gap-1">
                            <i class="fas fa-edit text-xs"></i>
                            <span>Buka</span>
                        </a>
                        <form method="POST" action="{{ route('simlab.destroy', $project->id) }}" onsubmit="return confirm('Yakin ingin menghapus proyek ini?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-red-500/10 text-red-400 text-xs font-bold hover:bg-red-500/20 transition-colors">
                                <i class="fas fa-trash text-[10px]"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="border-t border-gray-800 bg-gray-950 py-6 text-center text-xs text-gray-500">
        <p>&copy; 2026 PembdaHUB Simulator Lab. Hak Cipta Dilindungi.</p>
    </footer>

</body>
</html>
