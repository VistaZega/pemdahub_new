{{-- EKSTRAKURIKULER & MINAT BAKAT — Windows 10 Metro Tile Design ($extracurriculars) --}}
@php
    // Palet warna tile ala Windows 10 — flat, bold, beragam
    $tileColors = [
        '#0078d4', // Windows Blue
        '#107c10', // Xbox Green
        '#e81123', // Red
        '#ff8c00', // Orange
        '#5c2d91', // Purple
        '#00b7c3', // Teal
        '#b4009e', // Magenta
        '#00cc6a', // Mint Green
        '#e74856', // Coral Red
        '#0099bc', // Cyan
        '#7a7574', // Dark Gray
        '#c30052', // Dark Pink
        '#10893e', // Forest Green
        '#2d7d9a', // Sea Blue
        '#4c4a48', // Charcoal
        '#bf0077', // Deep Magenta
        '#744da9', // Lavender
        '#018574', // Dark Teal
    ];

    // Pattern tile sizes: beberapa tile lebih besar (wide/large) agar mirip Windows 10
    // Format: 'small' = 1x1, 'wide' = 2x1, 'tall' = 1x2, 'large' = 2x2
    $tileSizePattern = ['wide', 'small', 'small', 'large', 'small', 'wide', 'small', 'small', 'wide', 'small', 'small', 'large', 'small', 'wide', 'small', 'small', 'wide', 'small'];

    // Ikon kategori ekskul
    $categoryIcons = [
        'pramuka'       => '⚜️',
        'paskibraka'    => '🇮🇩',
        'seni_budaya'   => '🎭',
        'olahraga'      => '⚽',
        'sains_it'      => '💻',
        'sains_teknologi' => '🤖',
        'keagamaan'     => '✝️',
        'jurnalistik'   => '📰',
        'marching_band' => '🥁',
        'kepanduan'     => '⚜️',
    ];
@endphp

<section id="ekskul" class="py-14 bg-[#1b1b1b] border-t border-[#333]">
    <div class="max-w-7xl mx-auto px-4 sm:px-8">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
            <div>
                <div class="text-[10px] font-mono-code font-bold text-[#00b7c3] uppercase tracking-wider mb-1">
                    ✱ PENGEMBANGAN KARAKTER & MINAT BAKAT
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                    Asah Potensi di <span class="text-[#0078d4]">{{ isset($extracurriculars) ? $extracurriculars->count() : '16' }}+ Ekstrakurikuler.</span>
                </h2>
                <p class="text-xs text-[#aaa] font-medium mt-1 max-w-xl">
                    Wadah karakter tangguh, kreativitas seni, robotika, dan olahraga bagi siswa SMP, SMA, dan SMK.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-[#2d2d2d] border border-[#555] rounded text-[11px] font-mono-code font-bold text-[#0078d4]">
                    🎖️ {{ isset($extracurriculars) ? $extracurriculars->count() : '16' }}+ Cabang Terpadu
                </span>
            </div>
        </div>

        <!-- Windows 10 Metro Tile Grid -->
        <div class="win10-tile-grid mb-8">
            @forelse($extracurriculars as $index => $ekskul)
                @php
                    $bgColor = $tileColors[$index % count($tileColors)];
                    $sizeClass = $tileSizePattern[$index % count($tileSizePattern)];
                    $icon = $ekskul->icon ?? ($categoryIcons[$ekskul->category] ?? '🎯');
                    $memberCount = $ekskul->active_members_count ?? 0;
                    $schoolShort = $ekskul->school?->name ?? 'Lintas 3 Unit';
                    // Ambil kata pertama nama sekolah yang pendek
                    $schoolLabel = $ekskul->school ? str_replace(['SMAS ', 'SMPS ', 'SMKS ', 'SMK ', 'SMA ', 'SMP ', 'Swasta '], '', $ekskul->school->name) : 'Yayasan';
                @endphp
                <div
                    class="win10-tile win10-tile-{{ $sizeClass }} group"
                    style="background-color: {{ $bgColor }};"
                    title="{{ $ekskul->name }} — {{ $ekskul->description ?? 'Kegiatan pengembangan bakat siswa' }}"
                >
                    {{-- Badge anggota (pojok kanan atas) --}}
                    @if($memberCount > 0)
                        <div class="absolute top-2 right-2 sm:top-3 sm:right-3 text-white/60 text-[9px] sm:text-[10px] font-mono-code font-bold">
                            {{ $memberCount }} <span class="hidden sm:inline">siswa</span>
                        </div>
                    @endif

                    {{-- Ikon besar di tengah --}}
                    <div class="win10-tile-icon">
                        <span>{{ $icon }}</span>
                    </div>

                    {{-- Label nama di bawah --}}
                    <div class="win10-tile-label">
                        <div class="win10-tile-name">{{ $ekskul->name }}</div>
                        <div class="win10-tile-school">{{ $schoolShort }}</div>
                    </div>

                    {{-- Hover overlay efek --}}
                    <div class="absolute inset-0 bg-white/0 group-hover:bg-white/10 transition-colors duration-200 pointer-events-none"></div>
                </div>
            @empty
                {{-- Empty state --}}
                <div class="col-span-full text-center py-12">
                    <div class="text-4xl mb-3">🎨</div>
                    <p class="text-sm font-bold text-[#777]">Data kegiatan ekstrakurikuler sedang diperbarui.</p>
                </div>
            @endforelse
        </div>

        <!-- Banner Ringkas Panduan Ekskul -->
        <div class="bg-[#2d2d2d] border border-[#444] rounded-lg p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-xl">💡</span>
                <p class="text-xs text-[#bbb] font-medium leading-normal">
                    <strong class="text-white">Pendaftaran Ekskul:</strong> Siswa aktif dapat memilih maksimal 2 kegiatan ekstrakurikuler per semester melalui portal siswa PembdaHUB.
                </p>
            </div>
            @auth
                <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded bg-[#0078d4] text-white text-xs font-black whitespace-nowrap hover:bg-[#106ebe] transition-colors">
                    Pilih Ekskul di Portal &rarr;
                </a>
            @else
                <a href="{{ route('login') }}" class="px-4 py-2 rounded bg-[#0078d4] text-white text-xs font-black whitespace-nowrap hover:bg-[#106ebe] transition-colors">
                    Masuk Portal Siswa &rarr;
                </a>
            @endauth
        </div>

    </div>
</section>

<style>
    /* ═══════════════════════════════════════════════════════
       WINDOWS 10 METRO TILE GRID — RESPONSIVE CSS GRID
       ═══════════════════════════════════════════════════════ */

    .win10-tile-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 3px;
    }

    /* Desktop: 6 kolom agar tiles bervariasi kecil-besar */
    @media (min-width: 768px) {
        .win10-tile-grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 4px;
        }
    }
    @media (min-width: 1024px) {
        .win10-tile-grid {
            grid-template-columns: repeat(6, 1fr);
            gap: 4px;
        }
    }

    /* Base tile */
    .win10-tile {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 12px;
        min-height: 100px;
        cursor: default;
        overflow: hidden;
        transition: filter 0.15s ease, transform 0.15s ease;
        user-select: none;
    }
    .win10-tile:hover {
        filter: brightness(1.08);
        transform: scale(1.01);
    }
    .win10-tile:active {
        transform: scale(0.97);
        filter: brightness(0.92);
    }

    /* Tile sizes — mobile (2-col grid) */
    .win10-tile-small {
        grid-column: span 1;
        grid-row: span 1;
        min-height: 100px;
    }
    .win10-tile-wide {
        grid-column: span 2;
        grid-row: span 1;
        min-height: 100px;
    }
    .win10-tile-tall {
        grid-column: span 1;
        grid-row: span 2;
        min-height: 200px;
    }
    .win10-tile-large {
        grid-column: span 2;
        grid-row: span 2;
        min-height: 200px;
    }

    /* Desktop sizes */
    @media (min-width: 768px) {
        .win10-tile-small  { min-height: 120px; }
        .win10-tile-wide   { min-height: 120px; }
        .win10-tile-tall   { min-height: 250px; }
        .win10-tile-large  { min-height: 250px; }
    }
    @media (min-width: 1024px) {
        .win10-tile-small  { min-height: 130px; }
        .win10-tile-wide   { min-height: 130px; }
        .win10-tile-tall   { min-height: 270px; }
        .win10-tile-large  { min-height: 270px; }
    }

    /* Icon — centered large emoji/icon */
    .win10-tile-icon {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        line-height: 1;
        filter: drop-shadow(0 1px 2px rgba(0,0,0,0.15));
    }
    .win10-tile-wide .win10-tile-icon,
    .win10-tile-large .win10-tile-icon {
        font-size: 2.8rem;
    }
    .win10-tile-tall .win10-tile-icon {
        font-size: 2.5rem;
    }
    @media (min-width: 768px) {
        .win10-tile-icon { font-size: 2.2rem; }
        .win10-tile-wide .win10-tile-icon { font-size: 3rem; }
        .win10-tile-large .win10-tile-icon { font-size: 3.5rem; }
        .win10-tile-tall .win10-tile-icon { font-size: 3rem; }
    }

    /* Label at bottom-left */
    .win10-tile-label {
        width: 100%;
        text-align: left;
        padding-top: 4px;
    }
    .win10-tile-name {
        color: rgba(255,255,255,0.95);
        font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
        font-weight: 700;
        font-size: 10px;
        line-height: 1.2;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .win10-tile-wide .win10-tile-name,
    .win10-tile-large .win10-tile-name {
        font-size: 12px;
        white-space: normal;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    .win10-tile-school {
        color: rgba(255,255,255,0.5);
        font-family: 'JetBrains Mono', monospace;
        font-weight: 600;
        font-size: 8px;
        margin-top: 1px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .win10-tile-wide .win10-tile-school,
    .win10-tile-large .win10-tile-school {
        font-size: 9px;
    }

    @media (min-width: 768px) {
        .win10-tile-name  { font-size: 11px; }
        .win10-tile-wide .win10-tile-name,
        .win10-tile-large .win10-tile-name { font-size: 13px; }
        .win10-tile-school { font-size: 9px; }
    }
</style>
