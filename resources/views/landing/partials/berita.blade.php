{{-- BERITA & INFORMASI TERKINI — ARSIP LENGKAP (100% Real Database $news) --}}
@if(isset($news) && $news->count() > 0)
<section id="berita" class="py-20 px-4 sm:px-8 border-t border-[#e7e3d8]">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
            <div>
                <div class="text-[11px] font-mono-code font-bold text-[#ff3823] uppercase tracking-wider mb-2">
                    ✱ ARSIP PUBLIKASI & WARTA SEKOLAH
                </div>
                <h2 class="text-3xl sm:text-4xl font-black text-[#121316] tracking-tight">
                    Warta & Berita <span class="highlight-marker">Terkini.</span>
                </h2>
                <p class="text-xs sm:text-sm text-[#555] font-medium mt-2 max-w-xl">
                    Informasi resmi agenda akademik, pengumuman yayasan, liputan prestasi siswa, dan rilis kegiatan sekolah.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <span class="px-3.5 py-1.5 bg-white border-2 border-[#121316] rounded-full text-xs font-mono-code font-bold shadow-[2px_2px_0px_#121316]">
                    📢 Berita Terverifikasi
                </span>
                <a href="#warta-pembda" class="px-3.5 py-1.5 bg-[#f6f4ee] border-2 border-[#121316] rounded-full text-xs font-mono-code font-bold hover:bg-[#ede9df] transition-colors shadow-[2px_2px_0px_#121316] flex items-center gap-1.5">
                    <i class="fa-solid fa-newspaper text-[#ff3823]"></i>
                    <span>Edisi Koran Atas ↑</span>
                </a>
            </div>
        </div>

        <!-- News Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($news as $item)
                <article class="bg-white border-2 border-[#121316] rounded-2xl p-6 shadow-[4px_4px_0px_#121316] flex flex-col justify-between hover:-translate-y-1 transition-all group cursor-pointer" onclick="openNewsDetailModal({{ $item->id }})">
                    <div>
                        @if($item->thumbnail_url || $item->image)
                            <div class="h-44 rounded-xl bg-slate-100 overflow-hidden mb-4 border border-[#121316] relative">
                                <img src="{{ $item->thumbnail_url ?? asset('storage/' . $item->image) }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <div class="absolute bottom-2 left-2 px-2 py-0.5 bg-[#121316]/80 text-white rounded text-[9px] font-mono-code font-bold uppercase">
                                    {{ $item->category_label ?? ucfirst($item->category ?? 'Warta') }}
                                </div>
                            </div>
                        @endif
                        <div class="text-[10px] font-mono-code font-bold text-[#2563eb] uppercase mb-2">
                            {{ $item->published_at ? \Carbon\Carbon::parse($item->published_at)->translatedFormat('d M Y') : '2026' }}
                        </div>
                        <h3 class="font-newspaper text-lg font-black text-[#121316] mb-2 leading-snug line-clamp-2 group-hover:text-[#ff3823] transition-colors">
                            {{ $item->title }}
                        </h3>
                        <p class="text-xs text-[#555] font-newspaper leading-relaxed line-clamp-3 font-normal mb-4">
                            {{ $item->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($item->content), 120) }}
                        </p>
                    </div>
                    <div class="pt-4 border-t border-[#e2ded5] flex items-center justify-between text-[11px] font-mono-code font-bold">
                        <span class="text-[#777] truncate max-w-[150px]">Oleh: {{ $item->author_name ?? ($item->author->name ?? 'Humas PEMBDA') }}</span>
                        <span class="text-[#ff3823] group-hover:translate-x-1 transition-transform flex items-center gap-1">
                            <span>Baca</span> &rarr;
                        </span>
                    </div>
                </article>
            @endforeach
        </div>

    </div>
</section>
@endif
