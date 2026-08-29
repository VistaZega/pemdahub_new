{{-- FOOTER — DesainPake AI Tactile Dark Ink --}}
<footer id="kontak" class="border-t-2 border-[#121316] bg-[#121316] text-white pt-16 pb-12 text-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-8">
        
        <div class="grid grid-cols-1 md:grid-cols-12 gap-10 pb-12 border-b border-white/10">
            
            <!-- Col 1: Brand (Span 5) -->
            <div class="md:col-span-5">
                <div class="flex items-center gap-3 mb-4">
                    <a href="{{ route('app.download') }}" class="flex items-center gap-2 group/flogo" title="Download & Pasang PembdaHUB Mobile">
                        <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo PembdaHUB" class="w-9 h-9 object-contain rounded-lg shadow-sm border border-white/20 group-hover/flogo:border-[#ff3823] group-hover/flogo:scale-105 transition-all">
                        <span class="text-xl font-black tracking-tight text-white group-hover/flogo:text-[#ff3823] transition-colors">Pembda<span class="text-[#ff3823]">HUB</span></span>
                    </a>
                </div>
                
                <p class="text-xs text-slate-400 leading-relaxed max-w-sm mb-6 font-medium">
                    Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA) — berdiri sejak 1970, konsisten membina generasi unggul melalui pendidikan bermutu di Kota Gunungsitoli.
                </p>

                <div class="flex items-center gap-3">
                    <a href="#" class="w-9 h-9 rounded-xl bg-white/5 hover:bg-white/15 border border-white/10 flex items-center justify-center text-white transition-colors" aria-label="Facebook">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-xl bg-white/5 hover:bg-white/15 border border-white/10 flex items-center justify-center text-white transition-colors" aria-label="Instagram">
                        <i class="fa-brands fa-instagram"></i>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-xl bg-white/5 hover:bg-white/15 border border-white/10 flex items-center justify-center text-white transition-colors" aria-label="YouTube">
                        <i class="fa-brands fa-youtube"></i>
                    </a>
                </div>
            </div>

            <!-- Col 2: Navigation Links (Span 2) -->
            <div class="md:col-span-2">
                <h4 class="text-[11px] font-mono-code font-bold text-slate-300 uppercase tracking-wider mb-4">NAVIGASI</h4>
                <div class="flex flex-col gap-2.5 font-medium text-slate-400">
                    <a href="#beranda" class="hover:text-white transition-colors">Beranda</a>
                    <a href="#showcase" class="hover:text-white transition-colors">Karya Siswa & PKL</a>
                    <a href="#ekskul" class="hover:text-white transition-colors">Ekstrakurikuler</a>
                    <a href="#galeri" class="hover:text-white transition-colors">Galeri Foto Momen</a>
                    <a href="#pembda-space" class="hover:text-white transition-colors">Pembda Space (STEAM)</a>
                    <a href="#fame" class="hover:text-white transition-colors">Hall of Fame</a>
                    <a href="#mitra-industri" class="text-[#ff3823] hover:underline font-bold">Mitra Industri DUDI</a>
                </div>
            </div>

            <!-- Col 3: Layanan Digital (Span 2) -->
            <div class="md:col-span-2">
                <h4 class="text-[11px] font-mono-code font-bold text-slate-300 uppercase tracking-wider mb-4">LAYANAN DIGITAL</h4>
                <div class="flex flex-col gap-2.5 font-medium text-slate-400">
                    <a href="{{ route('login') }}" class="hover:text-white transition-colors">LMS & Ujian CBT</a>
                    <a href="{{ route('public.pkl.map') }}" class="text-[#fde047] hover:underline font-bold">🗺️ Peta GPS Siswa PKL</a>
                    <a href="{{ route('ika.directory') }}" class="hover:text-white transition-colors">Direktori Alumni</a>
                    <a href="{{ route('ika.register') }}" class="hover:text-white transition-colors">Pendaftaran IKA</a>
                    <a href="{{ route('login') }}" class="hover:text-white transition-colors">Logbook PKL</a>
                    <a href="{{ asset('MANUAL_BOOK_PEMBDAHUB.pdf') }}" target="_blank" class="text-slate-300 hover:underline font-bold">📄 Manual Book PDF</a>
                </div>
            </div>

            <!-- Col 4: Alamat & Kontak (Span 3) -->
            <div class="md:col-span-3">
                <h4 class="text-[11px] font-mono-code font-bold text-slate-300 uppercase tracking-wider mb-4">KAMPUS PUSAT</h4>
                <div class="flex flex-col gap-2 text-slate-400 text-xs font-medium leading-relaxed">
                    <p class="flex items-start gap-2">
                        <i class="fa-solid fa-location-dot text-[#ff3823] mt-0.5"></i>
                        <span>Jl. Pelita No.09, Kelurahan Ilir, Kec. Gunungsitoli, Kota Gunungsitoli, Sumatera Utara (22815)</span>
                    </p>
                    <p class="flex items-center gap-2 mt-2">
                        <i class="fa-solid fa-envelope text-[#fbc02d]"></i>
                        <a href="mailto:perguruanpembdanias@gmail.com" class="hover:underline text-slate-300">perguruanpembdanias@gmail.com</a>
                    </p>
                </div>
            </div>

        </div>

        <!-- Footer Bottom Bar -->
        <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-slate-500 font-mono-code text-[11px]">
            <p>&copy; {{ date('Y') }} Yayasan Perguruan PEMBDA Nias &bull; 54 Tahun Mengabdi</p>
            <p class="text-slate-400">
                Crafted for <strong class="text-white">Pembda<span class="text-[#ff3823]">HUB</span></strong> &bull; Living Smart School
            </p>
        </div>

    </div>
</footer>
