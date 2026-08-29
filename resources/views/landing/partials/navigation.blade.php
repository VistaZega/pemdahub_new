{{-- NAVIGATION — DesainPake AI Tactile Style --}}
<header class="sticky top-0 z-50 bg-[#faf8f5]/95 backdrop-blur-md border-b border-[#e7e3d8]">
    <div class="max-w-7xl mx-auto px-4 sm:px-8 h-20 flex items-center justify-between">
        
        <!-- Brand Logo & App Download Links -->
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-1.5">
                <a href="{{ route('home') }}" title="Beranda Yayasan Perguruan PEMBDA" class="hover:scale-105 transition-transform flex-shrink-0">
                    <img src="{{ asset('images/logo-yayasan.png') }}" alt="Logo Yayasan Perguruan PEMBDA" class="w-10 h-10 object-contain rounded-lg shadow-sm border border-[#e7e3d8]">
                </a>
                <a href="{{ route('app.download') }}" title="Download / Install Aplikasi PembdaHUB Mobile" class="relative group/logo hover:scale-110 active:scale-95 transition-all flex-shrink-0 block">
                    <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo PembdaHUB - Download Aplikasi Mobile" class="w-10 h-10 object-contain rounded-lg shadow-sm border-2 border-[#ff3823]/40 group-hover/logo:border-[#ff3823] group-hover/logo:shadow-md">
                    <span class="absolute -top-1.5 -right-1.5 bg-[#ff3823] text-white text-[8px] font-black px-1.5 py-0.2 rounded-full shadow-sm animate-pulse flex items-center gap-0.5 pointer-events-none">
                        <i class="fa-solid fa-download text-[7px]"></i> APP
                    </span>
                </a>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="text-xl font-black tracking-tight text-[#121316] hover:text-[#ff3823] transition-colors">
                    Pembda<span class="text-[#ff3823]">HUB</span>
                </a>
                <a href="{{ route('app.download') }}" title="Download Aplikasi Mobile PembdaHUB" class="px-2 py-0.5 rounded-full bg-gradient-to-r from-red-500 to-orange-500 hover:from-red-600 hover:to-orange-600 text-white text-[10px] font-mono-code font-bold uppercase shadow-sm hidden sm:inline-flex items-center gap-1 transition-transform hover:scale-105">
                    <i class="fa-solid fa-mobile-screen-button text-[9px]"></i>
                    <span>App Mobile</span>
                </a>
            </div>
        </div>

        <!-- Desktop Navigation Links -->
        <nav class="hidden xl:flex items-center gap-6 text-xs font-bold text-[#444]">
            <a href="#showcase" class="hover:text-[#121316] transition-colors">Karya & PKL</a>
            <a href="#ekskul" class="hover:text-[#121316] transition-colors flex items-center gap-1">
                <span>Ekskul</span>
                <span class="px-1.5 py-0.2 rounded bg-[#ff3823] text-white text-[9px] font-mono-code font-bold">{{ isset($extracurriculars) ? $extracurriculars->count() : '16+' }}</span>
            </a>
            <a href="#galeri" class="hover:text-[#121316] transition-colors">Galeri Foto</a>
            <a href="#pembda-space" class="hover:text-[#121316] transition-colors">Pembda Space</a>
            <a href="#fame" class="hover:text-[#121316] transition-colors">Hall of Fame</a>
            <a href="#mitra-industri" class="hover:text-[#121316] transition-colors flex items-center gap-1 text-[#ff3823]">
                <i class="fa-solid fa-handshake text-[10px]"></i>
                <span>Mitra Industri</span>
            </a>
            <a href="#alumni" class="hover:text-[#121316] transition-colors">Alumni</a>
            <a href="#sekolah" class="hover:text-[#121316] transition-colors">3 Sekolah</a>
        </nav>

        <!-- Action CTAs -->
        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-full btn-tactile-red text-xs font-black tracking-wide flex items-center gap-1.5">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Dashboard</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="text-xs font-bold text-[#121316] hover:underline px-3 py-2 hidden sm:inline-block">
                    Masuk Portal
                </a>
                <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-full btn-tactile-red text-xs font-black tracking-wide flex items-center gap-1.5">
                    <span>Ruang Belajar</span>
                    <span>&rarr;</span>
                </a>
            @endauth

            <!-- Mobile Hamburger Button -->
            <button type="button" onclick="document.getElementById('mobile-nav-drawer').classList.toggle('hidden')" class="xl:hidden p-2 text-[#121316] hover:bg-[#ede9df] rounded-lg text-lg border border-[#121316]" aria-label="Toggle Menu">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

    </div>

    <!-- Mobile Drawer -->
    <div id="mobile-nav-drawer" class="hidden xl:hidden bg-[#faf8f5] border-b-2 border-[#121316] px-6 py-6 transition-all">
        <div class="flex flex-col gap-4 text-sm font-bold text-[#121316]">
            <a href="#showcase" onclick="document.getElementById('mobile-nav-drawer').classList.add('hidden')" class="py-1">Karya Siswa & PKL</a>
            <a href="#ekskul" onclick="document.getElementById('mobile-nav-drawer').classList.add('hidden')" class="py-1">Ekstrakurikuler ({{ isset($extracurriculars) ? $extracurriculars->count() : '16+' }})</a>
            <a href="#galeri" onclick="document.getElementById('mobile-nav-drawer').classList.add('hidden')" class="py-1">Galeri Foto Momen</a>
            <a href="#pembda-space" onclick="document.getElementById('mobile-nav-drawer').classList.add('hidden')" class="py-1">Pembda Space (STEAM)</a>
            <a href="#fame" onclick="document.getElementById('mobile-nav-drawer').classList.add('hidden')" class="py-1">Hall of Fame Civitas</a>
            <a href="#mitra-industri" onclick="document.getElementById('mobile-nav-drawer').classList.add('hidden')" class="py-1 text-[#ff3823] flex items-center gap-1.5">
                <i class="fa-solid fa-handshake"></i>
                <span>Mitra Industri Besar</span>
            </a>
            <a href="#alumni" onclick="document.getElementById('mobile-nav-drawer').classList.add('hidden')" class="py-1">Jejaring Alumni</a>
            <a href="#sekolah" onclick="document.getElementById('mobile-nav-drawer').classList.add('hidden')" class="py-1">3 Unit Sekolah</a>
            <div class="pt-4 border-t border-[#e7e3d8] flex flex-col gap-3">
                <a href="{{ route('app.download') }}" class="w-full text-center py-3 rounded-full bg-gradient-to-r from-red-500 via-orange-500 to-amber-500 text-white text-xs font-black shadow-md flex items-center justify-center gap-2">
                    <i class="fa-solid fa-download"></i> Download / Pasang App Mobile
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full text-center py-3 rounded-full btn-tactile-red text-xs font-black">
                        Buka Dashboard Portal
                    </a>
                @else
                    <a href="{{ route('login') }}" class="w-full text-center py-3 rounded-full btn-tactile-white text-xs font-black">
                        Masuk Portal Siswa / Guru
                    </a>
                @endauth
            </div>
        </div>
    </div>
</header>
