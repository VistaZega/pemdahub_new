<!DOCTYPE html>
<html lang="id" class="h-full antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login — PembdaHUB Mobile</title>

    <!-- PWA Manifest & App Icons -->
    <link rel="manifest" href="{{ asset('manifest.json?v=6') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icons/icon-192x192.png?v=6') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/icons/icon-192x192.png?v=6') }}">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS CDN + Alpine JS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        /* Animated gradient background */
        .login-bg {
            background: linear-gradient(135deg, #38bdf8 0%, #818cf8 35%, #c084fc 60%, #f472b6 100%);
            background-size: 300% 300%;
            animation: gradientShift 12s ease infinite;
        }
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        /* Floating blobs */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(1px);
            opacity: 0.18;
            animation: floatBlob 8s ease-in-out infinite;
        }
        .blob-1 { width: 160px; height: 160px; background: #ffffff; top: -30px; left: -40px; animation-duration: 10s; opacity: 0.22; }
        .blob-2 { width: 100px; height: 100px; background: #fbbf24; bottom: 15%; right: -20px; animation-duration: 9s; animation-delay: 1s; }
        .blob-3 { width: 70px; height: 70px; background: #34d399; top: 30%; right: 10%; animation-duration: 7s; animation-delay: 2s; }
        .blob-4 { width: 50px; height: 50px; background: #ffffff; bottom: 25%; left: 5%; animation-duration: 11s; animation-delay: 0.5s; opacity: 0.25; }
        .blob-5 { width: 120px; height: 120px; background: #f472b6; bottom: -30px; right: 30%; animation-duration: 13s; animation-delay: 3s; opacity: 0.12; }
        .blob-6 { width: 30px; height: 30px; background: #fbbf24; top: 18%; right: 25%; animation-duration: 6s; animation-delay: 1.5s; opacity: 0.35; }
        .blob-7 { width: 20px; height: 20px; background: #38bdf8; top: 45%; left: 8%; animation-duration: 5s; animation-delay: 2.5s; opacity: 0.4; }

        @keyframes floatBlob {
            0%, 100% { transform: translateY(0px) scale(1); }
            33% { transform: translateY(-18px) scale(1.04); }
            66% { transform: translateY(10px) scale(0.97); }
        }

        /* Glassmorphism card */
        .glass-card {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 2px solid rgba(255, 255, 255, 0.7);
            border-radius: 1.75rem;
            box-shadow:
                0 20px 50px rgba(99, 102, 241, 0.15),
                0 8px 20px rgba(0, 0, 0, 0.06),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
        }

        /* Gradient button with shine effect */
        .btn-gradient {
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 50%, #8b5cf6 100%);
            background-size: 200% 200%;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4);
        }
        .btn-gradient:active {
            transform: scale(0.96);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);
        }
        .btn-gradient::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -60%;
            width: 40%;
            height: 200%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent);
            transform: skewX(-20deg);
            animation: btnShine 4s ease-in-out infinite;
        }
        @keyframes btnShine {
            0%, 100% { left: -60%; }
            50% { left: 120%; }
        }

        /* Input focus glow */
        .input-field {
            transition: all 0.25s ease;
        }
        .input-field:focus {
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
        }

        /* Logo pulse on load */
        .logo-container {
            animation: logoPop 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.2s both;
        }
        @keyframes logoPop {
            0% { transform: scale(0.5); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        /* Card slide up */
        .card-enter {
            animation: slideUp 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) 0.4s both;
        }
        @keyframes slideUp {
            0% { transform: translateY(40px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        /* Dots pattern */
        .dots-pattern {
            background-image: radial-gradient(circle, rgba(255,255,255,0.3) 1.5px, transparent 1.5px);
            background-size: 20px 20px;
        }

        /* Role pills hover */
        .role-pill {
            transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .role-pill:hover {
            transform: translateY(-2px) scale(1.05);
        }
    </style>
</head>
<body class="h-full overflow-x-hidden">

    <div class="login-bg min-h-screen flex flex-col items-center justify-center relative px-5 py-8" x-data="{ showPass: false, loading: false }">

        <!-- Floating Decorative Blobs -->
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
        <div class="blob blob-4"></div>
        <div class="blob blob-5"></div>
        <div class="blob blob-6"></div>
        <div class="blob blob-7"></div>

        <!-- Dots Pattern Overlay -->
        <div class="dots-pattern absolute inset-0 pointer-events-none"></div>

        <!-- Content Wrapper -->
        <div class="w-full max-w-sm relative z-10">

            <!-- Hero Branding -->
            <div class="text-center mb-7">
                <div class="logo-container inline-flex items-center justify-center w-[88px] h-[88px] rounded-[1.4rem] bg-white p-2 shadow-xl shadow-indigo-500/25 border-[3px] border-white/80 mb-4">
                    <img src="{{ asset('images/app-logo.png?v=6') }}" alt="PembdaHUB Logo" class="w-full h-full object-contain rounded-xl">
                </div>
                <h1 class="text-[2rem] font-black text-white tracking-tight leading-none drop-shadow-md">PembdaHUB</h1>
                <p class="text-[11px] text-white/80 font-bold mt-1.5 tracking-widest uppercase">Perguruan Pembangunan Daerah Nias</p>
            </div>

            <!-- Login Card -->
            <div class="glass-card p-7 card-enter">
                <h2 class="text-xl font-black text-slate-900 mb-0.5">Selamat Datang! 👋</h2>
                <p class="text-[11px] text-slate-500 mb-6 font-semibold">Masuk dengan akun Siswa, Guru, atau Orang Tua</p>

                <!-- Error Message -->
                @if($errors->any())
                    <div class="mb-5 p-3.5 rounded-2xl bg-gradient-to-r from-rose-500 to-pink-500 text-white text-xs font-bold shadow-lg shadow-rose-500/25 flex items-center gap-2.5 border border-white/30">
                        <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-circle-exclamation text-sm"></i>
                        </div>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form action="{{ route('mobile.login.post') }}" method="POST" class="space-y-4" @submit="loading = true">
                    @csrf

                    <!-- Login Input -->
                    <div>
                        <label for="login" class="block text-[11px] font-extrabold text-slate-700 mb-1.5 ml-1">EMAIL / USERNAME / NIS</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center">
                                    <i class="fa-regular fa-user text-indigo-500 text-xs"></i>
                                </div>
                            </div>
                            <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                                   placeholder="Masukkan Email / NIS..."
                                   class="input-field w-full pl-[3.2rem] pr-4 py-3.5 bg-slate-50/80 border-2 border-slate-200/80 rounded-2xl text-slate-900 text-sm placeholder-slate-400 focus:outline-none focus:border-indigo-400 font-semibold">
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label for="password" class="block text-[11px] font-extrabold text-slate-700 mb-1.5 ml-1">PASSWORD</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center">
                                    <i class="fa-solid fa-lock text-indigo-500 text-[11px]"></i>
                                </div>
                            </div>
                            <input :type="showPass ? 'text' : 'password'" id="password" name="password" required
                                   placeholder="••••••••"
                                   class="input-field w-full pl-[3.2rem] pr-12 py-3.5 bg-slate-50/80 border-2 border-slate-200/80 rounded-2xl text-slate-900 text-sm placeholder-slate-400 focus:outline-none focus:border-indigo-400 font-semibold">
                            <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-indigo-500 transition">
                                <i :class="showPass ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between pt-0.5">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" name="remember" class="w-[18px] h-[18px] rounded-md border-2 border-slate-300 text-indigo-600 focus:ring-indigo-400 focus:ring-offset-0">
                            <span class="text-xs text-slate-600 font-semibold group-hover:text-slate-800 transition">Ingat Saya</span>
                        </label>
                        <a href="{{ url('/forgot-password') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 transition">Lupa Password?</a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                            class="btn-gradient w-full py-4 px-4 text-white font-black text-sm rounded-2xl uppercase tracking-widest flex items-center justify-center gap-2 mt-2"
                            :disabled="loading">
                        <template x-if="!loading">
                            <span class="flex items-center gap-2">
                                <span>Masuk ke Aplikasi</span>
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </span>
                        </template>
                        <template x-if="loading">
                            <span class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span>Memproses...</span>
                            </span>
                        </template>
                    </button>
                </form>
            </div>

            <!-- Role Indicators -->
            <div class="flex items-center justify-center gap-2.5 mt-6" style="animation: slideUp 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) 0.7s both;">
                <div class="role-pill px-3.5 py-1.5 rounded-full bg-white/30 backdrop-blur-sm border border-white/40 flex items-center gap-1.5 shadow-sm">
                    <span class="text-[13px]">🎓</span>
                    <span class="text-[10px] font-bold text-white drop-shadow-sm">Siswa</span>
                </div>
                <div class="role-pill px-3.5 py-1.5 rounded-full bg-white/30 backdrop-blur-sm border border-white/40 flex items-center gap-1.5 shadow-sm">
                    <span class="text-[13px]">👨‍🏫</span>
                    <span class="text-[10px] font-bold text-white drop-shadow-sm">Guru</span>
                </div>
                <div class="role-pill px-3.5 py-1.5 rounded-full bg-white/30 backdrop-blur-sm border border-white/40 flex items-center gap-1.5 shadow-sm">
                    <span class="text-[13px]">👨‍👩‍👧</span>
                    <span class="text-[10px] font-bold text-white drop-shadow-sm">Orang Tua</span>
                </div>
            </div>

            <!-- PWA Install Banner -->
            <div x-data="{ deferredPrompt: null, canInstall: false }"
                 x-init="window.addEventListener('beforeinstallprompt', (e) => { e.preventDefault(); deferredPrompt = e; canInstall = true; });"
                 class="mt-4">
                <button x-show="canInstall" x-cloak
                        @click="deferredPrompt.prompt(); deferredPrompt.userChoice.then(() => { canInstall = false; });"
                        class="w-full py-2.5 rounded-2xl bg-white/25 backdrop-blur-sm border border-white/40 text-white text-xs font-bold flex items-center justify-center gap-2 transition active:scale-95 hover:bg-white/35"
                        style="animation: slideUp 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) 0.9s both;">
                    <i class="fa-solid fa-download text-xs"></i>
                    <span>Install PembdaHUB ke HP Anda</span>
                </button>
            </div>

            <!-- Copyright -->
            <div class="text-center mt-6" style="animation: slideUp 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) 0.8s both;">
                <p class="text-[10px] text-white/60 font-semibold">© {{ date('Y') }} Yayasan Perguruan PEMBDA Nias</p>
                <p class="text-[9px] text-white/40 font-medium mt-0.5">PembdaHUB Education Portal v2.0</p>
            </div>
        </div>
    </div>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('SW registered!'))
                    .catch(err => console.log('SW registration failed: ', err));
            });
        }
    </script>
</body>
</html>
