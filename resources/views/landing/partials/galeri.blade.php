{{-- GALERI MOMEN & DOKUMENTASI SEKOLAH — Polaroid Bento Frames (100% Real Database $galleryItems) --}}
<section id="galeri" class="py-20 px-4 sm:px-8 border-t border-[#e7e3d8]">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
            <div>
                <div class="text-[11px] font-mono-code font-bold text-[#ff3823] uppercase tracking-wider mb-2">
                    ✱ DOKUMENTASI KEHIDUPAN SEKOLAH
                </div>
                <h2 class="text-3xl sm:text-4xl font-black text-[#121316] tracking-tight">
                    Galeri Momen & <span class="highlight-marker">Aktivitas Siswa.</span>
                </h2>
                <p class="text-xs sm:text-sm text-[#555] font-medium mt-2 max-w-xl">
                    Rekam jejak semangat belajar, praktikum laboratorium sains, bengkel otomotif TEFA, apel upacara, dan kompetisi ekstrakurikuler 3 unit sekolah.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="px-3.5 py-1.5 bg-white border-2 border-[#121316] rounded-full text-xs font-mono-code font-bold shadow-[2px_2px_0px_#121316]">
                    📸 {{ $galleryItems->count() > 0 ? $galleryItems->count() . ' Foto Terkini' : 'Dokumentasi Terpadu' }}
                </span>
            </div>
        </div>

        <!-- Bento Photo Gallery Grid (Polaroid Style) -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            
            @php
                $firstItem = $galleryItems->first();
                $otherItems = $galleryItems->slice(1);
            @endphp

            @if($firstItem)
                <!-- Featured Photo (Span 7) -->
                <div class="md:col-span-7 photo-frame flex flex-col justify-between">
                    <div class="h-64 sm:h-80 rounded-xl bg-[#1e293b] flex items-center justify-center text-slate-300 font-bold text-xs relative overflow-hidden group">
                        @if($firstItem->image_url)
                            <img src="{{ $firstItem->image_url }}" alt="{{ $firstItem->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center p-6 text-center text-white bg-gradient-to-tr from-slate-900 to-indigo-900">
                                <span class="text-4xl mb-2">🏛️</span>
                                <h4 class="text-lg font-black uppercase tracking-tight">{{ $firstItem->title }}</h4>
                                <p class="text-xs text-slate-300 mt-1 max-w-md">{{ $firstItem->description ?? 'Dokumentasi kegiatan resmi Perguruan PEMBDA Nias.' }}</p>
                            </div>
                        @endif
                        <span class="absolute top-3 left-3 z-30 px-2.5 py-1 rounded bg-[#ff3823] text-white font-mono-code text-[10px] font-bold">
                            {{ strtoupper($firstItem->category ?? 'UTAMA') }}
                        </span>
                    </div>
                    <div class="pt-3 px-2 flex items-center justify-between text-xs font-bold text-[#555]">
                        <span class="truncate">{{ $firstItem->title }}</span>
                        <span class="text-[#ff3823] font-mono-code">{{ $firstItem->created_at ? $firstItem->created_at->translatedFormat('d M Y') : 'Terbaru' }}</span>
                    </div>
                </div>
            @else
                <!-- Fallback Featured Photo -->
                <div class="md:col-span-7 photo-frame flex flex-col justify-between">
                    <div class="h-64 sm:h-80 rounded-xl bg-[#1e293b] flex items-center justify-center p-6 text-center text-white relative overflow-hidden">
                        <div>
                            <span class="text-4xl mb-2 block">🏛️</span>
                            <h4 class="text-lg font-black uppercase tracking-tight">Apel Kebangsaan & Upacara Bersama 3 Unit Sekolah</h4>
                            <p class="text-xs text-slate-300 mt-1 max-w-md">Menanamkan nilai nasionalisme, disiplin, dan persaudaraan antar siswa SMP, SMA, dan SMK.</p>
                        </div>
                        <span class="absolute top-3 left-3 px-2.5 py-1 rounded bg-[#ff3823] text-white font-mono-code text-[10px] font-bold">
                            KAMPUS UTAMA
                        </span>
                    </div>
                    <div class="pt-3 px-2 flex items-center justify-between text-xs font-bold text-[#555]">
                        <span>Upacara Bendera Senin Pagi</span>
                        <span class="text-[#ff3823] font-mono-code">2026</span>
                    </div>
                </div>
            @endif

            <!-- Right 2 Stacked Cards (Span 5) -->
            <div class="md:col-span-5 flex flex-col gap-6">
                @forelse($otherItems->take(2) as $item)
                    <div class="photo-frame">
                        <div class="h-36 rounded-xl bg-[#0f766e] flex items-center justify-center p-4 text-white text-center relative overflow-hidden">
                            @if($item->image_url)
                                <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="z-10">
                                    <span class="text-2xl mb-1 block">🔬</span>
                                    <h5 class="text-sm font-black uppercase">{{ $item->title }}</h5>
                                    <p class="text-[11px] text-teal-100">{{ $item->description ?? 'Kegiatan Belajar & Praktik' }}</p>
                                </div>
                            @endif
                        </div>
                        <div class="pt-2 px-2 flex items-center justify-between text-[11px] font-bold text-[#555]">
                            <span class="truncate">{{ $item->title }}</span>
                            <span class="text-teal-700 font-mono-code">{{ strtoupper($item->category ?? 'DOKUMENTASI') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="photo-frame">
                        <div class="h-36 rounded-xl bg-[#0f766e] flex items-center justify-center p-4 text-white text-center">
                            <div>
                                <span class="text-2xl mb-1 block">🔬</span>
                                <h5 class="text-sm font-black uppercase">Praktikum Lab Sains & Biologi</h5>
                                <p class="text-[11px] text-teal-100">SMAS Pembda 1 &bull; Riset Bio-Pestisida</p>
                            </div>
                        </div>
                        <div class="pt-2 px-2 flex items-center justify-between text-[11px] font-bold text-[#555]">
                            <span>Laboratorium MIPA Terpadu</span>
                            <span class="text-teal-700 font-mono-code">Sains</span>
                        </div>
                    </div>
                    <div class="photo-frame">
                        <div class="h-36 rounded-xl bg-[#c2410c] flex items-center justify-center p-4 text-white text-center">
                            <div>
                                <span class="text-2xl mb-1 block">🔧</span>
                                <h5 class="text-sm font-black uppercase">Bengkel Otomotif Teaching Factory</h5>
                                <p class="text-[11px] text-orange-100">SMKS Pembda Nias &bull; Uji Injeksi PGM-FI</p>
                            </div>
                        </div>
                        <div class="pt-2 px-2 flex items-center justify-between text-[11px] font-bold text-[#555]">
                            <span>Bengkel Standar Industri</span>
                            <span class="text-orange-700 font-mono-code">Vokasi</span>
                        </div>
                    </div>
                @endforelse
            </div>

        </div>

    </div>
</section>
