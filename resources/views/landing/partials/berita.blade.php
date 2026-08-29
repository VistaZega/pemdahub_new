{{-- BERITA & INFORMASI TERKINI — 100% Real Database ($news) --}}
@if(isset($news) && $news->count() > 0)
<section id="berita" class="py-20 px-4 sm:px-8 border-t border-[#e7e3d8]">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
            <div>
                <div class="text-[11px] font-mono-code font-bold text-[#ff3823] uppercase tracking-wider mb-2">
                    ✱ KABAR CIVITAS & INFORMASI
                </div>
                <h2 class="text-3xl sm:text-4xl font-black text-[#121316] tracking-tight">
                    Warta & Berita <span class="highlight-marker">Terkini.</span>
                </h2>
                <p class="text-xs sm:text-sm text-[#555] font-medium mt-2 max-w-xl">
                    Informasi resmi agenda akademik, pengumuman yayasan, liputan prestasi siswa, dan rilis kegiatan sekolah.
                </p>
            </div>

            <div>
                <span class="px-3.5 py-1.5 bg-white border-2 border-[#121316] rounded-full text-xs font-mono-code font-bold shadow-[2px_2px_0px_#121316]">
                    📢 Berita Terverifikasi
                </span>
            </div>
        </div>

        <!-- News Grid (3 Columns) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($news as $item)
                <article class="bg-white border-2 border-[#121316] rounded-2xl p-6 shadow-[4px_4px_0px_#121316] flex flex-col justify-between hover:-translate-y-1 transition-transform">
                    <div>
                        @if($item->thumbnail_url || $item->image)
                            <div class="h-44 rounded-xl bg-slate-100 overflow-hidden mb-4 border border-[#121316]">
                                <img src="{{ $item->thumbnail_url ?? asset('storage/' . $item->image) }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                            </div>
                        @endif
                        <div class="text-[10px] font-mono-code font-bold text-[#2563eb] uppercase mb-2">
                            {{ $item->category ?? 'BERITA SEKOLAH' }} &bull; {{ $item->published_at ? \Carbon\Carbon::parse($item->published_at)->translatedFormat('d M Y') : '2026' }}
                        </div>
                        <h3 class="text-base font-black text-[#121316] mb-2 leading-snug line-clamp-2">
                            {{ $item->title }}
                        </h3>
                        <p class="text-xs text-[#555] leading-relaxed line-clamp-3 font-medium mb-4">
                            {{ $item->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($item->content), 120) }}
                        </p>
                    </div>
                    <div class="pt-4 border-t border-[#e2ded5] flex items-center justify-between text-[11px] font-mono-code font-bold">
                        <span class="text-[#777]">Oleh: {{ $item->author_name ?? 'Humas PEMBDA' }}</span>
                        <a href="{{ url('/news/' . ($item->slug ?? $item->id)) }}" class="text-[#ff3823] hover:underline">Baca &rarr;</a>
                    </div>
                </article>
            @endforeach
        </div>

    </div>
</section>
@endif
