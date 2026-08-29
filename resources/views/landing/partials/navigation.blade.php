{{-- NAVIGATION — DesainPake AI Tactile Style --}}
<header class="sticky top-0 z-50 bg-[#faf8f5]/95 backdrop-blur-md border-b border-[#e7e3d8]">
    <div class="max-w-7xl mx-auto px-4 sm:px-8 h-20 flex items-center justify-between">
        
        <!-- Brand Logo -->
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
            <span class="text-[#ff3823] font-black text-2xl leading-none group-hover:rotate-45 transition-transform">✱</span>
            <div class="flex items-center gap-2">
                <span class="text-xl font-black tracking-tight text-[#121316]">Pembda<span class="text-[#ff3823]">HUB</span></span>
                <span class="px-1.5 py-0.5 rounded bg-[#e7e3d8] text-[#555] text-[10px] font-mono-code font-bold uppercase hidden sm:inline-block">SMART SCHOOL</span>
            </div>
        </a>

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
            <a href="#alumni" onclick="document.getElementById('mobile-nav-drawer').classList.add('hidden')" class="py-1">Jejaring Alumni</a>
            <a href="#sekolah" onclick="document.getElementById('mobile-nav-drawer').classList.add('hidden')" class="py-1">3 Unit Sekolah</a>
            <div class="pt-4 border-t border-[#e7e3d8] flex flex-col gap-3">
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
