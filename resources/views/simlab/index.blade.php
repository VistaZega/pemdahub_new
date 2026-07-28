<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PembdaHUB SimLab - Laboratorium Mikrokontroler & IoT Virtual</title>
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
        .glow-emerald {
            box-shadow: 0 0 25px -5px rgba(16, 185, 129, 0.3);
        }
        .glow-amber {
            box-shadow: 0 0 25px -5px rgba(245, 158, 11, 0.3);
        }
        .glass-card {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-card:hover {
            border-color: rgba(16, 185, 129, 0.4);
            transform: translateY(-2px);
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
                        <i class="fas fa-microchip"></i>
                    </div>
                    <div>
                        <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-400 via-teal-300 to-cyan-400">PembdaHUB</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-950 text-emerald-400 border border-emerald-800 font-semibold ml-1">SimLab</span>
                    </div>
                </a>
            </div>

            <div class="flex items-center space-x-3">
                <a href="{{ route('simlab.editor', 'new') }}" class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-black font-bold text-sm shadow-lg shadow-emerald-500/25 transition-all flex items-center space-x-2">
                    <i class="fas fa-plus"></i>
                    <span>Buat Project Baru</span>
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
                    <span>Virtual Electronics & Microcontroller Lab 100% Mandiri</span>
                </div>
                <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-white mb-4">
                    Laboratorium Mikrokontroler & <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-400 to-cyan-400">IoT Virtual</span>
                </h1>
                <p class="text-gray-400 text-base sm:text-lg mb-8 leading-relaxed">
                    Rancang rangkaian elektronika, tulis kode Arduino C++, dan jalankan simulasi mikrokontroler secara instan di browser tanpa ketergantungan perangkat keras fisik maupun platform luar.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('simlab.editor', 'new') }}" class="px-6 py-3.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-black font-bold text-base transition-all shadow-xl shadow-emerald-500/20 flex items-center space-x-2">
                        <i class="fas fa-play"></i>
                        <span>Buka Workspace Simulator</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Supported Hardware Badges -->
        <div class="mb-12">
            <h2 class="text-sm uppercase tracking-wider text-gray-500 font-bold mb-4 flex items-center gap-2">
                <i class="fas fa-microchip text-emerald-400"></i> Papan Mikrokontroler & Sensor Didukung
            </h2>
            <div class="flex flex-wrap gap-3">
                <div class="px-3.5 py-2 rounded-xl bg-gray-900 border border-gray-800 text-xs font-bold text-cyan-400 flex items-center gap-2">
                    <i class="fas fa-microchip text-base"></i> Arduino Uno (ATmega328P)
                </div>
                <div class="px-3.5 py-2 rounded-xl bg-gray-900 border border-gray-800 text-xs font-bold text-emerald-400 flex items-center gap-2">
                    <i class="fas fa-microchip text-base"></i> Arduino Nano
                </div>
                <div class="px-3.5 py-2 rounded-xl bg-gray-900 border border-gray-800 text-xs font-bold text-amber-400 flex items-center gap-2">
                    <i class="fas fa-wifi text-base"></i> ESP32 DevKit
                </div>
                <div class="px-3.5 py-2 rounded-xl bg-gray-800/50 border border-gray-700/50 text-xs font-medium text-gray-300">
                    HC-SR04 • DHT11 • Servo SG90 • L298N Motor Driver • RC522 RFID • LCD 16x2/20x4 • Mini DFPlayer • Relay 5V • TCRT5000 • PIR Sensor • LDR
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
                            <span>Mulai Praktik</span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- Template 2: Ultrasonic Distance & LCD -->
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
                            <span>Mulai Praktik</span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- Template 3: Servo Motor Sweep -->
                <div class="glass-card rounded-2xl p-6 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-2.5 py-1 rounded-lg bg-emerald-950 text-emerald-400 border border-emerald-800 text-xs font-bold">Arduino Nano</span>
                            <span class="text-xs text-gray-500 font-mono">Starter #3</span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2 group-hover:text-emerald-400 transition-colors">Motor Servo SG90 Sweep</h3>
                        <p class="text-gray-400 text-xs mb-4">Kontrol sudut pergerakan Motor Servo SG90 dari 0 derajat hingga 180 derajat secara kontinu.</p>
                    </div>
                    <div class="pt-4 border-t border-gray-800 flex items-center justify-between">
                        <span class="text-xs text-gray-500"><i class="fas fa-bolt text-amber-400 mr-1"></i>Pemula</span>
                        <a href="{{ route('simlab.editor', 'servo-sweep') }}" class="px-3.5 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 text-xs font-bold transition-colors flex items-center gap-1">
                            <span>Mulai Praktik</span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- Template 4: DHT11 Sensor Suhu -->
                <div class="glass-card rounded-2xl p-6 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-2.5 py-1 rounded-lg bg-amber-950 text-amber-400 border border-amber-800 text-xs font-bold">ESP32 DevKit</span>
                            <span class="text-xs text-gray-500 font-mono">Starter #4</span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2 group-hover:text-emerald-400 transition-colors">DHT11 Suhu & Serial Monitor</h3>
                        <p class="text-gray-400 text-xs mb-4">Membaca kelembaban & suhu lingkungan dari sensor DHT11 lalu dikirim ke Serial Monitor ESP32.</p>
                    </div>
                    <div class="pt-4 border-t border-gray-800 flex items-center justify-between">
                        <span class="text-xs text-gray-500"><i class="fas fa-bolt text-amber-400 mr-1"></i>IoT Basic</span>
                        <a href="{{ route('simlab.editor', 'dht11-esp32') }}" class="px-3.5 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 text-xs font-bold transition-colors flex items-center gap-1">
                            <span>Mulai Praktik</span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- Template 5: Motor DC & Driver L298N -->
                <div class="glass-card rounded-2xl p-6 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-2.5 py-1 rounded-lg bg-cyan-950 text-cyan-400 border border-cyan-800 text-xs font-bold">Arduino Uno</span>
                            <span class="text-xs text-gray-500 font-mono">Starter #5</span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2 group-hover:text-emerald-400 transition-colors">Motor DC & Driver L298N</h3>
                        <p class="text-gray-400 text-xs mb-4">Mengontrol arah putaran (CW/CCW) dan kecepatan PWM Motor DC menggunakan modul driver L298N.</p>
                    </div>
                    <div class="pt-4 border-t border-gray-800 flex items-center justify-between">
                        <span class="text-xs text-gray-500"><i class="fas fa-bolt text-amber-400 mr-1"></i>Robotika</span>
                        <a href="{{ route('simlab.editor', 'l298n-motor') }}" class="px-3.5 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 text-xs font-bold transition-colors flex items-center gap-1">
                            <span>Mulai Praktik</span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- Template 6: Smart Home Relay & PIR -->
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
                            <span>Mulai Praktik</span>
                            <i class="fas fa-arrow-right text-xs"></i>
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
                        <i class="fas fa-folder-open text-emerald-400"></i> Proyek SimLab Saya
                    </h2>
                    <p class="text-xs text-gray-400">Rangkaian dan kode yang pernah Anda simpan di akun PembdaHUB</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($myProjects as $project)
                <div class="glass-card rounded-2xl p-6 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-2.5 py-1 rounded-lg bg-emerald-950 text-emerald-400 border border-emerald-800 text-xs font-bold uppercase">
                                {{ $project->board_type }}
                            </span>
                            <span class="text-xs text-gray-500">{{ $project->updated_at->diffForHumans() }}</span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2 group-hover:text-emerald-400 transition-colors">{{ $project->title }}</h3>
                        <p class="text-gray-400 text-xs mb-4 line-clamp-2">{{ $project->description ?: 'Tidak ada deskripsi' }}</p>
                    </div>
                    <div class="pt-4 border-t border-gray-800 flex items-center justify-between">
                        <span class="text-xs text-gray-500"><i class="fas fa-code text-cyan-400 mr-1"></i>Tersimpan</span>
                        <a href="{{ route('simlab.editor', $project->id) }}" class="px-3.5 py-1.5 rounded-lg bg-emerald-500 text-black text-xs font-bold hover:bg-emerald-400 transition-colors flex items-center gap-1">
                            <i class="fas fa-edit text-xs"></i>
                            <span>Buka Project</span>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="border-t border-gray-800 bg-gray-950 py-6 text-center text-xs text-gray-500">
        <p>&copy; 2026 PembdaHUB Virtual Electronics & Microcontroller Lab (SimLab). Hak Cipta Dilindungi.</p>
    </footer>

</body>
</html>
