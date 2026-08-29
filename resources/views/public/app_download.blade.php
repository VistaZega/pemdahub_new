<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950 text-slate-100 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Instalasi & Download Aplikasi Mobile - PembdaHUB</title>
    
    <!-- PWA Manifest & App Icons -->
    <link rel="manifest" href="{{ asset('manifest.json?v=6') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icons/icon-192x192.png?v=6') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/icons/icon-192x192.png?v=6') }}">
    <meta name="theme-color" content="#4f46e5">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @keyframes pulse-glow {
            0%, 100% { transform: scale(1); box-shadow: 0 0 20px rgba(99, 102, 241, 0.6); }
            50% { transform: scale(1.02); box-shadow: 0 0 35px rgba(249, 115, 22, 0.8); }
        }
        .glow-button {
            animation: pulse-glow 2s infinite ease-in-out;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 flex flex-col items-center justify-center p-4 py-8"
      x-data="pwaInstaller()">

    <!-- Top Navigation Bar (Back to Home) -->
    <div class="max-w-md w-full mb-4 flex items-center justify-between px-2">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-300 hover:text-white bg-white/10 hover:bg-white/20 px-3.5 py-2 rounded-xl transition-all backdrop-blur-sm border border-white/10">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Beranda</span>
        </a>
        <span class="text-[11px] font-mono font-bold text-orange-400">PEMBDA SMART HUB</span>
    </div>

    <div class="max-w-md w-full bg-slate-900/90 backdrop-blur-xl border-2 border-white/10 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6 text-center relative overflow-hidden">
        
        <!-- App Logo & Badge -->
        <div class="space-y-3">
            <div class="flex items-center justify-center gap-2">
                <div class="w-16 h-16 rounded-2xl bg-white p-2 shadow-xl border-2 border-white/80">
                    <img src="{{ asset('images/logo-yayasan.png') }}" alt="Logo Yayasan" class="w-full h-full object-contain">
                </div>
                <div class="w-20 h-20 rounded-2xl bg-white p-2 shadow-2xl border-4 border-orange-400/80">
                    <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo PembdaHUB" class="w-full h-full object-contain">
                </div>
            </div>

            <div>
                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-orange-500/20 text-orange-300 border border-orange-400/30">
                    📱 Aplikasi Mobile Web & PWA
                </span>
                <h1 class="text-2xl font-black text-white mt-2 tracking-tight">PembdaHUB Mobile</h1>
                <p class="text-xs text-slate-300 font-bold mt-1">Perguruan Pembangunan Daerah Nias</p>
            </div>
        </div>

        <!-- Android 1-Click Install Button (Active when PWA install prompt is ready) -->
        <template x-if="deferredPrompt">
            <div class="p-5 rounded-2xl bg-gradient-to-b from-orange-950/60 to-slate-900 border-2 border-orange-500/40 space-y-4 shadow-xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-black">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    Perangkat Siap Di-install
                </div>

                <h3 class="text-sm font-black text-white">Pasang Aplikasi ke Layar Utama HP</h3>
                <p class="text-[11px] text-slate-300 font-medium">Klik tombol di bawah untuk memasang PembdaHUB langsung ke HP Anda tanpa perlu unduh file manual!</p>

                <button @click="installPwa()" 
                        class="glow-button w-full py-4 px-6 bg-gradient-to-r from-orange-500 via-red-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-black text-sm rounded-2xl shadow-xl transition transform active:scale-95 flex items-center justify-center gap-3">
                    <i class="fa-solid fa-download text-lg"></i>
                    <span>PASANG APLIKASI SEKARANG</span>
                </button>
            </div>
        </template>

        <!-- Direct Link / Fallback Action -->
        <div class="p-5 rounded-2xl bg-white/5 border border-white/10 space-y-4">
            <h3 class="text-xs font-black text-white uppercase tracking-wider">Akses Langsung via HP atau Scan QR</h3>

            <a href="{{ url('/m/login') }}" 
               class="w-full py-3.5 px-4 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 hover:from-blue-500 hover:to-indigo-500 text-white font-black text-xs rounded-2xl shadow-lg transition transform active:scale-95 flex items-center justify-center gap-2">
                <i class="fa-solid fa-mobile-screen-button text-base"></i>
                <span>BUKA APLIKASI MOBILE DI BROWSER HP</span>
            </a>

            <!-- QR Code Section -->
            <div class="pt-3 border-t border-white/10 space-y-2">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Scan QR Code ini dari Kamera HP:</span>
                <div class="p-3 bg-white rounded-2xl inline-block shadow-lg mx-auto">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode(url('/m/login')) }}" 
                         alt="Scan QR Code PembdaHUB Mobile" 
                         class="w-36 h-36 mx-auto rounded-xl">
                </div>
            </div>
        </div>

        <!-- Instructions Accordion / Steps -->
        <div class="text-left space-y-3 pt-2 border-t border-white/10">
            <h4 class="text-xs font-black text-slate-300 uppercase tracking-wider text-center flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-circle-info text-orange-400"></i> Panduan Pasang Mandiri di HP:
            </h4>
            
            <div class="p-4 rounded-2xl bg-slate-800/90 border border-slate-700/80 text-xs text-slate-300 space-y-2">
                <div class="font-black text-emerald-400 flex items-center gap-2 text-xs">
                    <i class="fa-brands fa-android text-base"></i> <span>1. HP Android (Google Chrome):</span>
                </div>
                <ol class="text-[11px] text-slate-300 font-medium space-y-1 pl-6 list-decimal">
                    <li>Buka <strong class="text-white">perguruanpembda.com</strong> di browser Google Chrome.</li>
                    <li>Tekan menu <strong>titik tiga (⋮)</strong> di pojok kanan atas Chrome.</li>
                    <li>Pilih <strong class="text-emerald-300">"Instal aplikasi"</strong> atau <strong class="text-emerald-300">"Tambahkan ke Layar Utama"</strong>.</li>
                    <li>Klik <strong>"Instal"</strong> — Aplikasi PembdaHUB langsung siap digunakan dari homescreen HP Anda!</li>
                </ol>
            </div>

            <div class="p-4 rounded-2xl bg-slate-800/90 border border-slate-700/80 text-xs text-slate-300 space-y-2">
                <div class="font-black text-blue-400 flex items-center gap-2 text-xs">
                    <i class="fa-brands fa-apple text-base"></i> <span>2. iPhone / iPad (Safari):</span>
                </div>
                <ol class="text-[11px] text-slate-300 font-medium space-y-1 pl-6 list-decimal">
                    <li>Buka <strong class="text-white">perguruanpembda.com</strong> di browser Safari.</li>
                    <li>Tekan tombol <strong>Bagikan / Share (□↑)</strong> di bilah bawah.</li>
                    <li>Gulir ke bawah dan pilih <strong class="text-blue-300">"Tambahkan ke Layar Utama (Add to Home Screen)"</strong>.</li>
                    <li>Klik <strong>"Tambah"</strong> di kanan atas.</li>
                </ol>
            </div>
        </div>

        <div class="text-center pt-2 text-[10px] text-slate-500 font-bold">
            © {{ date('Y') }} Yayasan Perguruan PEMBDA Nias — PembdaHUB Pro
        </div>
    </div>

    <script>
        function pwaInstaller() {
            return {
                deferredPrompt: null,
                init() {
                    window.addEventListener('beforeinstallprompt', (e) => {
                        e.preventDefault();
                        this.deferredPrompt = e;
                    });
                },
                installPwa() {
                    if (this.deferredPrompt) {
                        this.deferredPrompt.prompt();
                        this.deferredPrompt.userChoice.then((choiceResult) => {
                            if (choiceResult.outcome === 'accepted') {
                                this.deferredPrompt = null;
                            }
                        });
                    }
                }
            }
        }
    </script>
</body>
</html>
