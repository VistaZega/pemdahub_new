<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900 text-slate-100 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalasi Aplikasi Mobile - PembdaHUB Pro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-purple-950 to-slate-900 flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-slate-900/90 backdrop-blur-xl border-2 border-purple-500/30 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6 text-center">
        
        <!-- App Logo & Badge -->
        <div class="space-y-3">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-white p-2 shadow-2xl shadow-purple-500/40 border-4 border-white/80 mx-auto">
                <img src="{{ asset('images/app-logo.png?v=6') }}" alt="PembdaHUB Logo" class="w-full h-full object-contain rounded-2xl">
            </div>
            <div>
                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-500/20 text-purple-300 border border-purple-400/30">
                    📱 Aplikasi Mobile Web & PWA
                </span>
                <h1 class="text-2xl font-black text-white mt-2 tracking-tight">PembdaHUB Mobile</h1>
                <p class="text-xs text-purple-200/80 font-bold mt-1">Perguruan Pembangunan Daerah Nias</p>
            </div>
        </div>

        <!-- QR Code & Direct Mobile Button -->
        <div class="p-5 rounded-2xl bg-white/5 border border-white/10 space-y-4">
            <h3 class="text-xs font-black text-white uppercase tracking-wider">Scan QR Code dengan Kamera HP Anda</h3>
            
            <div class="p-3 bg-white rounded-2xl inline-block shadow-lg mx-auto">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode(url('/m/login')) }}" 
                     alt="Scan QR Code PembdaHUB Mobile" 
                     class="w-40 h-40 mx-auto rounded-xl">
            </div>

            <p class="text-[11px] text-slate-400 font-medium">Arahkan kamera HP Anda ke QR Code di atas untuk membuka aplikasi secara instan.</p>

            <a href="{{ url('/m/login') }}" 
               class="w-full py-3.5 px-4 bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-black text-xs rounded-2xl shadow-lg transition transform active:scale-95 flex items-center justify-center gap-2">
                <i class="fa-solid fa-mobile-screen-button text-base"></i>
                <span>BUKA APLIKASI MOBILE DI HP</span>
            </a>
        </div>

        <!-- Instructions Accordion / Steps -->
        <div class="text-left space-y-3 pt-2 border-t border-white/10">
            <h4 class="text-xs font-black text-slate-300 uppercase tracking-wider text-center flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-circle-info text-purple-400"></i> Cara Pasang (Install) di Layar Utama HP:
            </h4>
            
            <div class="p-4 rounded-2xl bg-slate-800/90 border border-slate-700/80 text-xs text-slate-300 space-y-2">
                <div class="font-black text-emerald-400 flex items-center gap-2 text-xs">
                    <i class="fa-brands fa-android text-base"></i> <span>1. HP Android (Google Chrome):</span>
                </div>
                <ol class="text-[11px] text-slate-300 font-medium space-y-1 pl-6 list-decimal">
                    <li>Buka link <strong class="text-white">perguruanpembda.com</strong> di Google Chrome.</li>
                    <li>Tekan ikon <strong>titik tiga (⋮)</strong> di kanan atas Chrome.</li>
                    <li>Pilih menu <strong class="text-emerald-300">"Instal aplikasi"</strong> atau <strong class="text-emerald-300">"Tambahkan ke Layar Utama"</strong>.</li>
                    <li>Klik <strong>"Instal"</strong> — Aplikasi langsung terpasang di Layar Utama HP Anda!</li>
                </ol>
            </div>

            <div class="p-4 rounded-2xl bg-slate-800/90 border border-slate-700/80 text-xs text-slate-300 space-y-2">
                <div class="font-black text-blue-400 flex items-center gap-2 text-xs">
                    <i class="fa-brands fa-apple text-base"></i> <span>2. iPhone / iPad (Safari):</span>
                </div>
                <ol class="text-[11px] text-slate-300 font-medium space-y-1 pl-6 list-decimal">
                    <li>Buka link <strong class="text-white">perguruanpembda.com</strong> di Safari.</li>
                    <li>Tekan ikon <strong>Bagikan / Share (□↑)</strong> di bagian bawah layar.</li>
                    <li>Gulir ke bawah dan pilih <strong class="text-blue-300">"Tambahkan ke Layar Utama"</strong>.</li>
                    <li>Klik <strong>"Tambah"</strong> di sudut kanan atas.</li>
                </ol>
            </div>
        </div>

        <div class="text-center pt-2 text-[10px] text-slate-500 font-bold">
            © {{ date('Y') }} YP PEMBDA Nias — PembdaHUB Pro Mobile App
        </div>
    </div>

</body>
</html>
