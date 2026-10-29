<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $newsItem->title }} — Warta Yayasan Perguruan PEMBDA Nias</title>
    <meta name="description" content="{{ $newsItem->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($newsItem->content), 150) }}">
    
    <!-- Open Graph Metadata untuk WhatsApp & Media Sosial -->
    <meta property="og:title" content="{{ $newsItem->title }}">
    <meta property="og:description" content="{{ $newsItem->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($newsItem->content), 150) }}">
    @if($newsItem->image_url || $newsItem->image)
        <meta property="og:image" content="{{ $newsItem->image_url ?? asset('storage/' . $newsItem->image) }}">
    @endif
    <meta property="og:type" content="article">

    <link rel="icon" href="{{ asset('images/logo-pembda.png') }}" type="image/png">

    <!-- Google Fonts: Plus Jakarta Sans, JetBrains Mono & Newsreader -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700;800&family=Newsreader:ital,opsz,wght@0,6..72,400..800;1,6..72,400..800&display=swap" rel="stylesheet">

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            font-family: 'Plus Jakarta Sans', sans-serif;
            --bg-warm: #faf8f5;
            --text-dark: #121316;
            --brand-red: #ff3823;
        }

        body {
            background-color: var(--bg-warm);
            color: var(--text-dark);
            letter-spacing: -0.02em;
        }

        .font-mono-code {
            font-family: 'JetBrains Mono', monospace;
        }

        .font-newspaper {
            font-family: 'Newsreader', Georgia, 'Times New Roman', serif;
        }

        .btn-tactile-red {
            background: #ff3823;
            color: #ffffff;
            border: 1.5px solid #121316;
            box-shadow: 3px 3px 0px #121316;
            transition: all 0.15s ease;
        }
        .btn-tactile-red:hover {
            transform: translate(1.5px, 1.5px);
            box-shadow: 1.5px 1.5px 0px #121316;
        }

        .btn-tactile-white {
            background: #ffffff;
            color: #121316;
            border: 1.5px solid #121316;
            box-shadow: 3px 3px 0px #121316;
            transition: all 0.15s ease;
        }
        .btn-tactile-white:hover {
            transform: translate(1.5px, 1.5px);
            box-shadow: 1.5px 1.5px 0px #121316;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between">

    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-50 bg-[#faf8f5]/95 backdrop-blur-md border-b border-[#e7e3d8]">
        <div class="max-w-6xl mx-auto px-4 sm:px-8 h-16 sm:h-20 flex items-center justify-between gap-4">
            
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="hover:scale-105 transition-transform flex-shrink-0">
                    <img src="{{ asset('images/logo-yayasan.png') }}" alt="Logo Yayasan PEMBDA" class="w-8 h-8 sm:w-10 sm:h-10 object-contain rounded-lg border border-[#e7e3d8]">
                </a>
                <a href="{{ route('home') }}" class="text-base sm:text-lg font-black tracking-tight text-[#121316] hover:text-[#ff3823] transition-colors">
                    Pembda<span class="text-[#ff3823]">HUB</span>
                    <span class="text-[10px] sm:text-xs font-mono-code font-bold text-[#71717a] ml-1 hidden sm:inline">&bull; Warta Yayasan</span>
                </a>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="px-4 py-2 rounded-full btn-tactile-white text-xs font-mono-code font-bold flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali ke Beranda</span>
                </a>
            </div>

        </div>
    </header>

    <!-- Main Article Container -->
    <main class="max-w-4xl mx-auto px-4 sm:px-8 py-8 sm:py-12 w-full flex-1">
        
        <!-- Newspaper Gazette Article Box -->
        <article class="bg-[#fffefb] border-2 border-[#121316] rounded-2xl p-6 sm:p-10 shadow-[8px_8px_0px_#121316]">
            
            <!-- Breadcrumbs & Meta Top -->
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#121316]/20 pb-3 mb-5 text-xs font-mono-code font-bold text-[#666]">
                <div class="flex items-center gap-2">
                    <a href="{{ route('home') }}" class="hover:text-[#ff3823]">Beranda</a>
                    <span>/</span>
                    <span class="px-2 py-0.5 bg-[#121316] text-[#fde047] rounded text-[10px] uppercase">
                        {{ $newsItem->category_label ?? ucfirst($newsItem->category ?? 'Warta') }}
                    </span>
                    <span>&bull;</span>
                    <span>{{ $newsItem->published_at ? \Carbon\Carbon::parse($newsItem->published_at)->translatedFormat('l, d F Y') : 'Terbaru' }}</span>
                </div>

                <div class="text-[#ff3823]">
                    Oleh: {{ $newsItem->author_name ?? ($newsItem->author->name ?? 'Redaksi Humas PEMBDA') }}
                </div>
            </div>

            <!-- Newspaper Headline -->
            <h1 class="font-newspaper text-3xl sm:text-5xl font-black text-[#121316] leading-[1.12] tracking-tight mb-6">
                {{ $newsItem->title }}
            </h1>

            <!-- Image with Newspaper Caption Frame -->
            @php
                $newsImg = $newsItem->image_url ?? ($newsItem->thumbnail_url ?? ($newsItem->image ? asset('storage/' . $newsItem->image) : null));
            @endphp
            @if($newsImg)
                <div class="mb-8">
                    <div class="border-2 border-[#121316] rounded-xl overflow-hidden bg-slate-100 shadow-sm">
                        <img src="{{ $newsImg }}" alt="{{ $newsItem->title }}" class="w-full max-h-[460px] object-cover">
                    </div>
                    <div class="mt-2 text-center text-xs font-mono-code text-[#71717a] italic">
                        Dokumentasi: Humas Yayasan Perguruan PEMBDA Nias &bull; Liputan Resmi Civitas
                    </div>
                </div>
            @endif

            <!-- Excerpt Lead Box -->
            @if($newsItem->excerpt)
                <div class="p-5 bg-[#faf8f5] border-l-4 border-[#ff3823] rounded-r-xl mb-8 font-newspaper text-base sm:text-lg italic text-[#374151] leading-relaxed">
                    {{ $newsItem->excerpt }}
                </div>
            @endif

            <!-- Article Body Content -->
            <div class="font-newspaper text-base sm:text-lg text-[#1f2937] leading-relaxed space-y-5 font-normal">
                @if($newsItem->content && str_contains($newsItem->content, '<p>'))
                    {!! $newsItem->content !!}
                @else
                    @foreach(explode("\n", $newsItem->content ?? $newsItem->excerpt) as $p)
                        @if(trim($p) !== '')
                            <p>{{ trim($p) }}</p>
                        @endif
                    @endforeach
                @endif
            </div>

            <!-- Article Footer & Share Toolbar -->
            <div class="mt-10 pt-6 border-t-2 border-[#121316] flex flex-wrap items-center justify-between gap-4">
                <div class="text-xs font-mono-code text-[#71717a]">
                    <strong>Publikasi Resmi:</strong> Yayasan Perguruan PEMBDA Nias &bull; Edisi Terverifikasi
                </div>

                <div class="flex items-center gap-2">
                    <a href="https://api.whatsapp.com/send?text={{ urlencode('*' . $newsItem->title . "*\n\n" . url()->current()) }}" target="_blank" class="px-4 py-2 rounded-full bg-emerald-600 text-white text-xs font-mono-code font-bold hover:bg-emerald-700 flex items-center gap-1.5 transition-colors shadow-sm">
                        <i class="fa-brands fa-whatsapp text-sm"></i>
                        <span>Bagikan WA</span>
                    </a>
                    <button type="button" onclick="navigator.clipboard.writeText(window.location.href); alert('Tautan berita berhasil disalin!');" class="px-4 py-2 rounded-full bg-white border border-[#121316] text-[#121316] text-xs font-mono-code font-bold hover:bg-[#ede9df] flex items-center gap-1.5 transition-colors shadow-sm">
                        <i class="fa-solid fa-link text-xs"></i>
                        <span>Salin Link</span>
                    </button>
                    <button type="button" onclick="window.print()" class="px-3.5 py-2 rounded-full bg-white border border-[#121316] text-[#121316] text-xs font-mono-code font-bold hover:bg-[#ede9df] transition-colors shadow-sm" title="Cetak Lembar Koran">
                        <i class="fa-solid fa-print"></i>
                    </button>
                </div>
            </div>

        </article>

        <!-- Warta Lainnya -->
        @if(isset($otherNews) && $otherNews->isNotEmpty())
            <div class="mt-12">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-newspaper text-2xl font-black text-[#121316] uppercase">
                        Warta Yayasan Terkait Lainnya
                    </h3>
                    <a href="{{ route('home') }}#berita" class="text-xs font-mono-code font-bold text-[#ff3823] hover:underline">
                        Lihat Semua &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($otherNews as $other)
                        <a href="{{ route('public.news.show', $other->slug ?? $other->id) }}" class="p-4 bg-white border-2 border-[#121316] rounded-xl hover:-translate-y-1 transition-transform shadow-[3px_3px_0px_#121316] flex flex-col justify-between group">
                            <div>
                                <div class="text-[10px] font-mono-code font-bold text-[#2563eb] uppercase mb-1">
                                    {{ $other->category_label ?? 'WARTA' }} &bull; {{ $other->published_at ? \Carbon\Carbon::parse($other->published_at)->translatedFormat('d M Y') : 'Baru' }}
                                </div>
                                <h4 class="font-newspaper text-sm sm:text-base font-bold text-[#121316] leading-snug line-clamp-2 group-hover:text-[#ff3823] transition-colors mb-2">
                                    {{ $other->title }}
                                </h4>
                                <p class="font-newspaper text-xs text-[#555] line-clamp-2">
                                    {{ $other->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($other->content), 80) }}
                                </p>
                            </div>
                            <div class="mt-3 pt-2 border-t border-[#f0ede6] text-[11px] font-mono-code font-bold text-[#ff3823] flex items-center justify-end">
                                <span>Baca Artikel &rarr;</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

    </main>

    <!-- Footer -->
    <footer class="bg-[#121316] text-[#999] py-8 border-t border-[#333] text-center text-xs font-mono-code mt-12">
        <p class="text-white font-bold mb-1">Yayasan Perguruan PEMBDA Nias</p>
        <p>&copy; {{ date('Y') }} PembdaHUB. Maju Terus Pantang Mundur.</p>
    </footer>

</body>
</html>
