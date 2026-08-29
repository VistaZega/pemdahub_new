{{-- MITRA INDUSTRI BESAR — DesainPake AI Tactile Style --}}
<section id="mitra-industri" class="py-20 px-4 sm:px-8 bg-[#faf8f5] border-t-2 border-[#121316] relative overflow-hidden">
    {{-- Subtle decorative background watermark --}}
    <div class="absolute -right-20 -bottom-20 w-96 h-96 bg-[#ff3823]/5 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -left-20 -top-20 w-96 h-96 bg-[#fbc02d]/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto relative z-10">

        {{-- Section Header --}}
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-14">
            <div>
                <div class="text-[11px] font-mono-code font-extrabold text-[#ff3823] uppercase tracking-wider mb-3 flex items-center gap-2">
                    <span class="text-base">✱</span>
                    <span>LINK & MATCH DUNIA USAHA & DUNIA INDUSTRI (DUDI)</span>
                </div>
                <h2 class="text-3xl sm:text-5xl font-black text-[#121316] tracking-tight leading-[1.1]">
                    Industri Besar Mitra Resmi <br>
                    <span class="highlight-marker">SMK Swasta Pembda Nias</span>
                </h2>
                <p class="text-sm sm:text-base text-[#555] font-medium mt-4 max-w-2xl leading-relaxed">
                    Menghubungkan ruang belajar dengan standar dunia kerja nyata melalui kelas industri resmi, kurikulum tersertifikasi, laboratorium modern, serta penempatan magang terpadu.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('public.pkl.map') }}" class="px-5 py-3 rounded-full btn-tactile-white text-xs font-black flex items-center gap-2">
                    <i class="fa-solid fa-map-location-dot text-[#ff3823]"></i>
                    <span>Peta GPS Sebaran Siswa PKL</span>
                </a>
                <a href="{{ route('app.download') }}" class="px-5 py-3 rounded-full btn-tactile-red text-xs font-black flex items-center gap-2">
                    <i class="fa-solid fa-mobile-screen-button"></i>
                    <span>Aplikasi PembdaHUB</span>
                </a>
            </div>
        </div>

        @php
            $industrialPartners = [
                [
                    'name' => 'PT Astra International Tbk',
                    'category' => 'Konglomerasi Otomotif & Industri',
                    'logo' => 'astra-international.png',
                    'badge' => 'Otomotif & Industri',
                    'badge_color' => 'bg-amber-100 text-amber-900 border-amber-300',
                    'desc' => 'Grup industri otomotif dan manufaktur terkemuka di Indonesia, mendukung standarisasi vokasi dan transfer teknologi rekayasa.',
                ],
                [
                    'name' => 'Axioo Class Program',
                    'category' => 'Kelas Industri Resmi IT',
                    'logo' => 'axioo-class-program.png',
                    'badge' => 'Kelas Industri IT',
                    'badge_color' => 'bg-blue-100 text-blue-900 border-blue-300',
                    'desc' => 'Program pendidikan industri resmi berbasis sertifikasi internasional, perakitan hardware laptop/PC, dan kurikulum IT terkini.',
                ],
                [
                    'name' => 'Astra Daihatsu Motor',
                    'category' => 'Pabrikan Otomotif Terpadu',
                    'logo' => 'daihatsu.png',
                    'badge' => 'Otomotif & Mesin',
                    'badge_color' => 'bg-red-100 text-red-900 border-red-300',
                    'desc' => 'Penyelarasan kurikulum teknik kendaraan ringan (TKRO), budaya kerja industri (5R), dan pelatihan mekanik modern.',
                ],
                [
                    'name' => 'Pintar Bersama Daihatsu (PBD)',
                    'category' => 'Sertifikasi Vokasi Nasional',
                    'logo' => 'pintar-bersama-daihatsu.png',
                    'badge' => 'Sertifikasi Vokasi',
                    'badge_color' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                    'desc' => 'Inisiatif nasional PT Astra Daihatsu Motor dalam meningkatkan daya saing lulusan SMK melalui sertifikasi keahlian teknis otomotif.',
                ],
                [
                    'name' => 'Auto2000 (Toyota Astra)',
                    'category' => 'Jaringan Dealer & Servis Otomotif',
                    'logo' => 'auto-2000.png',
                    'badge' => 'Bengkel Resmi',
                    'badge_color' => 'bg-rose-100 text-rose-900 border-rose-300',
                    'desc' => 'Mitra layanan purna jual dan servis kendaraan, memfasilitasi Praktik Kerja Lapangan (PKL) standar profesional.',
                ],
                [
                    'name' => 'PT Tera Data Indonusa Tbk (Axioo)',
                    'category' => 'Principal Hardware & Komputer AI',
                    'logo' => 'tera-data-internusa.png',
                    'badge' => 'Principal Teknologi',
                    'badge_color' => 'bg-indigo-100 text-indigo-900 border-indigo-300',
                    'desc' => 'Produsen dan pemegang merek Axioo di Indonesia, penyedia perangkat laboratorium komputer & platform edukasi AI.',
                ],
                [
                    'name' => 'Polytron (PT Hartono Istana Teknologi)',
                    'category' => 'Elektronika & Manufaktur Audio Video',
                    'logo' => 'polytron.png',
                    'badge' => 'Elektronika & Audio',
                    'badge_color' => 'bg-sky-100 text-sky-900 border-sky-300',
                    'desc' => 'Pelopor industri manufaktur elektronik nasional, mendukung keahlian Teknik Audio Video (TAV) dan rangkaian elektronika.',
                ],
                [
                    'name' => 'PT Asaba Computer Centre',
                    'category' => 'Solusi IT & Otomasi Perkantoran',
                    'logo' => 'pt-asaba.png',
                    'badge' => 'IT & Perangkat',
                    'badge_color' => 'bg-purple-100 text-purple-900 border-purple-300',
                    'desc' => 'Distributor teknologi informasi dan sistem otomatisasi kantor terkemuka, memperluas wawasan infrastruktur digital siswa.',
                ],
                [
                    'name' => 'Phytaverse',
                    'category' => 'Inovasi Digital, IoT & EduTech',
                    'logo' => 'phytaverse.png',
                    'badge' => 'IoT & Digital Tech',
                    'badge_color' => 'bg-teal-100 text-teal-900 border-teal-300',
                    'desc' => 'Platform teknologi interaktif, mendukung eksplorasi riset Internet of Things (IoT), kecerdasan buatan, dan simulasi sains modern.',
                ],
            ];
        @endphp

        {{-- 9 Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            @foreach($industrialPartners as $partner)
                <div class="bg-white border-2 border-[#121316] rounded-3xl p-6 shadow-[5px_5px_0px_#121316] hover:shadow-[7px_7px_0px_#ff3823] hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group">
                    
                    <div>
                        {{-- Top Header / Badge --}}
                        <div class="flex items-center justify-between gap-2 mb-5">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-mono-code font-bold uppercase border {{ $partner['badge_color'] }}">
                                {{ $partner['badge'] }}
                            </span>
                            <span class="text-[10px] font-mono-code text-slate-400 font-bold">SMKS PEMBDA</span>
                        </div>

                        {{-- Logo Showcase Box (Clean White Canvas) --}}
                        <div class="w-full h-28 bg-[#faf8f5] border border-slate-200 rounded-2xl p-4 flex items-center justify-center mb-5 group-hover:border-[#121316] group-hover:bg-white transition-colors">
                            <img src="{{ asset('images/mitra/' . $partner['logo']) }}" 
                                 alt="Logo {{ $partner['name'] }}" 
                                 loading="lazy"
                                 class="max-h-20 max-w-[85%] object-contain filter transition-transform duration-300 group-hover:scale-105">
                        </div>

                        {{-- Partner Name & Info --}}
                        <h3 class="text-base font-black text-[#121316] tracking-tight group-hover:text-[#ff3823] transition-colors leading-snug">
                            {{ $partner['name'] }}
                        </h3>
                        <p class="text-[11px] font-mono-code font-bold text-slate-500 mt-1 uppercase">
                            {{ $partner['category'] }}
                        </p>

                        <p class="text-xs text-slate-600 font-medium leading-relaxed mt-3 pt-3 border-t border-slate-100">
                            {{ $partner['desc'] }}
                        </p>
                    </div>

                    {{-- Card Bottom Meta Tag --}}
                    <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-[10px] font-mono-code font-bold text-slate-500">
                        <span class="flex items-center gap-1.5 text-emerald-600">
                            <i class="fa-solid fa-circle-check text-[10px]"></i> Mitra Terverifikasi
                        </span>
                        <span class="text-[#ff3823] group-hover:translate-x-1 transition-transform">
                            Vokasi Unggul &rarr;
                        </span>
                    </div>

                </div>
            @endforeach
        </div>

        {{-- Bottom Summary Banner --}}
        <div class="bg-[#121316] text-white border-2 border-[#121316] rounded-3xl p-6 sm:p-8 shadow-[6px_6px_0px_#ff3823] flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-1 text-center md:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-amber-300 border border-white/20 text-[10px] font-mono-code font-bold uppercase mb-1">
                    <i class="fa-solid fa-handshake"></i> Sinergi Sekolah & Industri
                </div>
                <h4 class="text-xl sm:text-2xl font-black tracking-tight text-white">
                    Siap Membuka Peluang Kerjasama Industri Baru?
                </h4>
                <p class="text-xs sm:text-sm text-slate-300 font-medium max-w-xl">
                    SMK Swasta Pembda Nias senantiasa membuka pintu kemitraan penyelarasan kurikulum, magang industri, rekrutmen lulusan, serta program CSR pendidikan.
                </p>
            </div>

            <div class="flex items-center gap-3 flex-shrink-0">
                <a href="https://wa.me/6282168532567?text=Halo%20Yayasan%20Perguruan%20PEMBDA%20Nias,%20kami%20tertarik%20untuk%20menjalin%20kemitraan%20industri%20dengan%20SMK%20Swasta%20Pembda%20Nias." 
                   target="_blank" rel="noopener noreferrer" 
                   class="px-6 py-3.5 rounded-full bg-[#25d366] hover:bg-[#20bd5a] text-slate-950 font-black text-xs shadow-lg transition-transform hover:scale-105 flex items-center gap-2">
                    <i class="fa-brands fa-whatsapp text-base"></i>
                    <span>Hubungi Hubungan Industri (Hubin)</span>
                </a>
            </div>
        </div>

    </div>
</section>