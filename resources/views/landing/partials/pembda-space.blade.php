{{-- PEMBDA SPACE & FORUM STEAM — Real Collaboration Hub --}}
<section id="pembda-space" class="py-20 bg-[#f4f1ea] border-t border-[#e7e3d8]">
    <div class="max-w-7xl mx-auto px-4 sm:px-8">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
            <div>
                <div class="text-[11px] font-mono-code font-bold text-[#2563eb] uppercase tracking-wider mb-2">
                    ✱ RUANG KOLABORASI SISWA & GURU
                </div>
                <h2 class="text-3xl sm:text-4xl font-black text-[#121316] tracking-tight">
                    Pembda Space & <span class="highlight-marker">Forum STEAM.</span>
                </h2>
                <p class="text-xs sm:text-sm text-[#555] font-medium mt-2 max-w-xl">
                    Wadah interaksi aktif siswa dan bapak/ibu guru untuk berdiskusi sains, teknologi, seni, bimbingan masuk PTN, dan persiapan karir vokasi.
                </p>
            </div>

            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-full btn-tactile-red text-xs font-black">
                        + Buat Topik Diskusi
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-full btn-tactile-red text-xs font-black">
                        + Gabung Diskusi Forum
                    </a>
                @endauth
            </div>
        </div>

        <!-- 3 Feature Topic Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Topic 1: Robotika & IoT -->
            <div class="bg-white border-2 border-[#121316] rounded-2xl p-6 shadow-[4px_4px_0px_#121316] flex flex-col justify-between hover:-translate-y-1 transition-transform">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-2.5 py-0.5 rounded bg-blue-100 text-[#2563eb] font-mono-code text-[10px] font-bold uppercase">
                            🤖 Robotika & IoT
                        </span>
                        <span class="text-[11px] font-mono-code text-[#888] font-bold">{{ $totalForumThreads > 0 ? $totalForumThreads . ' Topik' : 'Aktif' }}</span>
                    </div>
                    <h4 class="text-base font-black text-[#121316] mb-2 leading-snug">
                        Persiapan Lomba STEAM: Kalibrasi Sensor Otomasi & Mikrokontroler
                    </h4>
                    <p class="text-xs text-[#555] leading-relaxed mb-4 font-medium">
                        Kolaborasi tim sains dan kejuruan dalam merakit purwarupa alat pengukur kualitas air dan sistem penyiraman tanaman otomatis.
                    </p>
                </div>
                <div class="pt-4 border-t border-[#e2ded5] flex items-center justify-between text-[11px] font-mono-code">
                    <span class="font-bold text-[#121316]">Lintas 3 Unit Sekolah</span>
                    <a href="{{ route('login') }}" class="text-blue-600 font-bold hover:underline">Ikuti &rarr;</a>
                </div>
            </div>

            <!-- Topic 2: Literasi & Riset -->
            <div class="bg-white border-2 border-[#121316] rounded-2xl p-6 shadow-[4px_4px_0px_#121316] flex flex-col justify-between hover:-translate-y-1 transition-transform">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-2.5 py-0.5 rounded bg-amber-100 text-[#b45309] font-mono-code text-[10px] font-bold uppercase">
                            📚 Literasi & KTI
                        </span>
                        <span class="text-[11px] font-mono-code text-[#888] font-bold">Riset Siswa</span>
                    </div>
                    <h4 class="text-base font-black text-[#121316] mb-2 leading-snug">
                        Klub Menulis: Bedah Metodologi Riset & Penulisan Esai Olimpiade
                    </h4>
                    <p class="text-xs text-[#555] leading-relaxed mb-4 font-medium">
                        Ruang konsultasi dan pendampingan intensif bersama guru pembimbing KIR untuk penyusunan karya ilmiah remaja tingkat nasional.
                    </p>
                </div>
                <div class="pt-4 border-t border-[#e2ded5] flex items-center justify-between text-[11px] font-mono-code">
                    <span class="font-bold text-[#121316]">KIR Sains MIPA</span>
                    <a href="{{ route('login') }}" class="text-amber-700 font-bold hover:underline">Ikuti &rarr;</a>
                </div>
            </div>

            <!-- Topic 3: Bursa Karir & PKL -->
            <div class="bg-white border-2 border-[#121316] rounded-2xl p-6 shadow-[4px_4px_0px_#121316] flex flex-col justify-between hover:-translate-y-1 transition-transform">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-2.5 py-0.5 rounded bg-emerald-100 text-[#15803d] font-mono-code text-[10px] font-bold uppercase">
                            💼 Karir & DUDI
                        </span>
                        <span class="text-[11px] font-mono-code text-[#888] font-bold">{{ $totalDudi ?? '45' }}+ Mitra</span>
                    </div>
                    <h4 class="text-base font-black text-[#121316] mb-2 leading-snug">
                        Bursa Kerja Khusus: Wawancara Magang & Portofolio Digital Siswa
                    </h4>
                    <p class="text-xs text-[#555] leading-relaxed mb-4 font-medium">
                        Bimbingan kesiapan kerja dari mitra industri perbankan, telekomunikasi, dan bengkel resmi untuk lulusan siap pakai.
                    </p>
                </div>
                <div class="pt-4 border-t border-[#e2ded5] flex items-center justify-between text-[11px] font-mono-code">
                    <span class="font-bold text-[#121316]">BKK & Hubin SMK</span>
                    <a href="{{ route('login') }}" class="text-emerald-700 font-bold hover:underline">Ikuti &rarr;</a>
                </div>
            </div>

        </div>

    </div>
</section>
