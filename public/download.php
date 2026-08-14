<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950 text-slate-100 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Instalasi Aplikasi Mobile - PembdaHUB Pro</title>
    
    <!-- PWA Manifest & App Icons -->
    <link rel="manifest" href="/manifest.json?v=6">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png?v=6">
    <link rel="icon" type="image/png" sizes="192x192" href="/images/icons/icon-192x192.png?v=6">
    <meta name="theme-color" content="#4f46e5">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .clay-btn {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.5), inset 0 2px 4px rgba(255,255,255,0.4);
            border: 2px solid rgba(255,255,255,0.3);
        }
        @keyframes pulse-glow {
            0%, 100% { transform: scale(1); box-shadow: 0 0 20px rgba(99, 102, 241, 0.6); }
            50% { transform: scale(1.03); box-shadow: 0 0 35px rgba(168, 85, 247, 0.9); }
        }
        .glow-button {
            animation: pulse-glow 2s infinite ease-in-out;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-purple-950 to-slate-900 flex items-center justify-center p-4"
      x-data="pwaInstaller()">

    <div class="max-w-md w-full bg-slate-900/90 backdrop-blur-xl border-2 border-purple-500/30 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6 text-center relative overflow-hidden">
        
        <!-- App Logo & Title -->
        <div class="space-y-3">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-white p-2 shadow-2xl shadow-purple-500/40 border-4 border-white/80 mx-auto">
                <img src="/images/app-logo.png?v=6" alt="PembdaHUB Logo" class="w-full h-full object-contain rounded-2xl">
            </div>
            <div>
                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-500/20 text-purple-300 border border-purple-400/30">
                    📱 Aplikasi Mobile Web & PWA
                </span>
                <h1 class="text-2xl font-black text-white mt-2 tracking-tight">PembdaHUB Mobile</h1>
                <p class="text-xs text-purple-200/80 font-bold mt-1">Perguruan Pembangunan Daerah Nias</p>
            </div>
        </div>

        <!-- Android 1-Click Install Button (Shown if PWA install prompt is ready) -->
        <template x-if="deferredPrompt">
            <div class="p-5 rounded-2xl bg-gradient-to-b from-purple-900/40 to-slate-900 border-2 border-purple-500/40 space-y-4 shadow-xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-black">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    Siap Di-install Sekarang
                </div>

                <h3 class="text-sm font-black text-white">Pasang Aplikasi Langsung ke Layar Utama HP</h3>
                <p class="text-[11px] text-slate-300 font-medium">Klik tombol di bawah untuk memasang PembdaHUB ke HP Anda tanpa melalui Play Store!</p>

                <button @click="installPwa()" 
                        class="glow-button w-full py-4 px-6 bg-gradient-to-r from-emerald-500 via-teal-500 to-indigo-600 text-white font-black text-sm rounded-2xl shadow-xl transition transform active:scale-95 flex items-center justify-center gap-3">
                    <i class="fa-solid fa-download text-lg"></i>
                    <span>INSTALL APLIKASI SEKARANG</span>
                </button>
            </div>
        </template>

        <!-- Direct Link / Fallback Action -->
        <div class="p-5 rounded-2xl bg-white/5 border border-white/10 space-y-4" x-show="!deferredPrompt">
            <h3 class="text-xs font-black text-white uppercase tracking-wider">Akses Langsung via HP</h3>

            <a href="/m/login" 
               class="w-full py-4 px-4 bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-black text-xs rounded-2xl shadow-lg transition transform active:scale-95 flex items-center justify-center gap-2">
                <i class="fa-solid fa-mobile-screen-button text-base"></i>
                <span>MASUK KE APLIKASI MOBILE</span>
            </a>

            <!-- QR Code Section -->
            <div class="pt-3 border-t border-white/10 space-y-2">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Scan QR Code ini jika dari Komputer:</span>
                <div class="p-2.5 bg-white rounded-2xl inline-block shadow-lg mx-auto">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https%3A%2F%2Fperguruanpembda.com%2Fm%2Flogin" 
                         alt="Scan QR Code PembdaHUB Mobile" 
                         class="w-32 h-32 mx-auto rounded-xl">
                </div>
            </div>
        </div>

        <!-- Instructions Accordion / Steps -->
        <div class="text-left space-y-3 pt-2 border-t border-white/10">
            <h4 class="text-xs font-black text-slate-300 uppercase tracking-wider text-center">Panduan Pasang Manual di HP:</h4>
            
            <div class="p-3.5 rounded-2xl bg-slate-800/80 border border-slate-700/80 text-xs text-slate-300 space-y-1">
                <div class="font-black text-emerald-400 flex items-center gap-1.5">
                    <i class="fa-brands fa-android text-base"></i> Untuk HP Android (Chrome):
                </div>
                <p class="text-[11px] text-slate-400 font-medium pl-5">
                    Buka link → Tekan titik tiga (⋮) di kanan atas → Pilih <strong>"Tambahkan ke Layar Utama" / "Install Aplikasi"</strong>.
                </p>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-800/80 border border-slate-700/80 text-xs text-slate-300 space-y-1">
                <div class="font-black text-blue-400 flex items-center gap-1.5">
                    <i class="fa-brands fa-apple text-base"></i> Untuk iPhone / iOS (Safari):
                </div>
                <p class="text-[11px] text-slate-400 font-medium pl-5">
                    Buka link → Tekan ikon Bagikan / Share (□↑) → Pilih <strong>"Add to Home Screen" (Tambahkan ke Utama)</strong>.
                </p>
            </div>
        </div>

        <div class="text-center pt-2 text-[10px] text-slate-500 font-bold">
            © <?= date('Y') ?> YP PEMBDA Nias — PembdaHUB Pro Mobile App
        </div>
    </div>

    <!-- AUTO POPUP MODAL DI HP (Bila Pop-up Prompt Siap) -->
    <div x-show="showModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-10 scale-90"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-10 scale-90"
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
         style="display: none;">
        
        <div class="bg-gradient-to-b from-slate-900 to-purple-950 rounded-3xl p-6 w-full max-w-sm border-2 border-purple-500/50 shadow-2xl text-center space-y-4">
            
            <div class="w-16 h-16 rounded-2xl bg-white p-1.5 mx-auto shadow-lg border-2 border-purple-300">
                <img src="/images/app-logo.png?v=6" alt="PembdaHUB" class="w-full h-full object-contain rounded-xl">
            </div>

            <div class="space-y-1">
                <h3 class="text-lg font-black text-white">Install PembdaHUB Mobile! 🚀</h3>
                <p class="text-xs text-purple-200/90 font-bold">Pasang ke Layar Utama HP Anda untuk akses cepat 1-klik tanpa lewat browser.</p>
            </div>

            <div class="pt-2 space-y-2">
                <button @click="installPwa()" 
                        class="w-full py-3.5 px-4 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-black text-xs rounded-2xl shadow-xl transition active:scale-95 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-download text-sm"></i>
                    <span>INSTALL APLIKASI SEKARANG</span>
                </button>

                <button @click="showModal = false" 
                        class="w-full py-2.5 px-4 text-xs font-bold text-slate-400 hover:text-white transition">
                    Nanti Saja
                </button>
            </div>
        </div>
    </div>

    <script>
        function pwaInstaller() {
            return {
                deferredPrompt: null,
                showModal: false,
                init() {
                    window.addEventListener('beforeinstallprompt', (e) => {
                        e.preventDefault();
                        this.deferredPrompt = e;
                        this.showModal = true;
                    });
                    
                    // Register Service Worker
                    if ('serviceWorker' in navigator) {
                        navigator.serviceWorker.register('/sw.js').catch(err => console.log('SW Reg error:', err));
                    }
                },
                installPwa() {
                    if (this.deferredPrompt) {
                        this.deferredPrompt.prompt();
                        this.deferredPrompt.userChoice.then((choiceResult) => {
                            if (choiceResult.outcome === 'accepted') {
                                this.showModal = false;
                                this.deferredPrompt = null;
                            }
                        });
                    } else {
                        window.location.href = '/m/login';
                    }
                }
            }
        }
    </script>
</body>
</html>
