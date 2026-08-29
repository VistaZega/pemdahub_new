{{-- FITUR PINTAR GRAPH PAPER — DesainPake AI Feature Grid --}}
<section id="fitur" class="py-20 px-4 sm:px-8 border-t border-[#e7e3d8]">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="max-w-2xl mb-12">
            <div class="text-[11px] font-mono-code font-bold text-[#ff3823] uppercase tracking-wider mb-2">
                ✱ TEKNOLOGI PENDIDIKAN TERPADU
            </div>
            <h2 class="text-3xl sm:text-4xl font-black text-[#121316] tracking-tight mb-2">
                Bukan sekadar website sekolah biasa.
            </h2>
            <p class="text-xs sm:text-sm text-[#666] font-medium leading-relaxed">
                PembdaHUB memahami kebutuhan civitas — dari presensi gerbang kilat, ujian anti-contek, modul mandiri, sampai penilaian otomatis rapor merdeka.
            </p>
        </div>

        <!-- Graph Paper Feature Cards (3 Columns) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Feature Card 1 -->
            <div class="bg-white border-2 border-[#121316] rounded-2xl p-6 shadow-[3px_3px_0px_#121316]">
                <div class="graph-paper-box h-40 rounded-xl border border-[#e2ded5] p-4 flex items-center justify-center gap-2 mb-6">
                    <div class="w-10 h-14 rounded-lg bg-[#121316] text-white flex flex-col items-center justify-center font-mono-code text-[10px] shadow-sm">
                        <span>💳</span>
                        <span class="font-bold">RFID</span>
                    </div>
                    <div class="w-10 h-14 rounded-lg bg-[#ff3823] text-white flex flex-col items-center justify-center font-mono-code text-[10px] shadow-sm">
                        <span>📝</span>
                        <span class="font-bold">CBT</span>
                    </div>
                    <div class="w-10 h-14 rounded-lg bg-[#fbc02d] text-black flex flex-col items-center justify-center font-mono-code text-[10px] shadow-sm">
                        <span>📚</span>
                        <span class="font-bold">LMS</span>
                    </div>
                </div>
                <h3 class="text-base font-black text-[#121316] mb-2">3 Sistem Terpadu Dalam 1 Portal</h3>
                <p class="text-xs text-[#555] leading-relaxed font-medium">
                    Presensi RFID otomatis gerbang, ujian CBT daring terenkripsi, dan bahan ajar Kurikulum Merdeka saling terhubung tanpa perlu banyak aplikasi terpisah.
                </p>
            </div>

            <!-- Feature Card 2 -->
            <div class="bg-white border-2 border-[#121316] rounded-2xl p-6 shadow-[3px_3px_0px_#121316]">
                <div class="graph-paper-box h-40 rounded-xl border border-[#e2ded5] p-4 flex flex-col justify-center gap-2 text-xs font-mono-code mb-6">
                    <div class="p-2 bg-white rounded-lg border border-[#121316] text-[11px] font-bold shadow-xs">
                        &bull; {{ $trainingModules->first()?->title ?? 'Modul Praktikum Jaringan TKJ' }}
                    </div>
                    <div class="p-2 bg-white rounded-lg border border-[#121316] text-[11px] font-bold text-[#ff3823] shadow-xs">
                        &bull; {{ $trainingModules->skip(1)->first()?->title ?? 'Riset Hayati & Sains Terapan' }}
                    </div>
                </div>
                <h3 class="text-base font-black text-[#121316] mb-2">Bahan Ajar Mandiri & Modul PDF</h3>
                <p class="text-xs text-[#555] leading-relaxed font-medium">
                    Modul PDF terverifikasi dan video praktikum disusun langsung oleh bapak/ibu guru untuk diakses belajar kapan saja dari rumah.
                </p>
            </div>

            <!-- Feature Card 3 -->
            <div class="bg-white border-2 border-[#121316] rounded-2xl p-6 shadow-[3px_3px_0px_#121316]">
                <div class="graph-paper-box h-40 rounded-xl border border-[#e2ded5] p-4 flex flex-col items-center justify-center gap-2 mb-6">
                    <div class="px-4 py-1.5 rounded-full bg-[#121316] text-[#fde047] font-mono-code text-[11px] font-bold">
                        "Koreksi Nilai Otomatis"
                    </div>
                    <div class="px-5 py-2 rounded-lg bg-[#ff3823] text-white font-black text-xs uppercase shadow-sm">
                        E-RAPOR MERDEKA
                    </div>
                </div>
                <h3 class="text-base font-black text-[#121316] mb-2">Pengolahan Nilai & Rapor Kilat</h3>
                <p class="text-xs text-[#555] leading-relaxed font-medium">
                    Kalkulasi nilai harian, PTS, dan PAS langsung masuk ke format rapor resmi siap cetak tanpa beban rekap manual berulang.
                </p>
            </div>

        </div>

    </div>
</section>
