{{-- EKSTRAKURIKULER & MINAT BAKAT — Windows 10 Metro Live Tiles with Motion Background Imagery ($extracurriculars) --}}
@php
    // Palet warna aksen tile khas Windows 10
    $tileColors = [
        '#0078d4', // Windows Blue
        '#107c10', // Xbox Green
        '#e81123', // Red
        '#d83b01', // Dark Orange
        '#5c2d91', // Purple
        '#008272', // Dark Teal
        '#b4009e', // Magenta
        '#10893e', // Forest Green
        '#e74856', // Coral Red
        '#0099bc', // Cyan
        '#565656', // Slate Dark
        '#c30052', // Dark Pink
        '#004e8c', // Deep Navy
        '#2d7d9a', // Sea Blue
        '#498205', // Olive Green
        '#881798', // Deep Violet
        '#744da9', // Lavender
        '#018574', // Dark Teal
    ];

    // Pattern tile sizes: variasi ukuran Windows 10 (wide, small, large)
    $tileSizePattern = ['wide', 'small', 'small', 'large', 'small', 'wide', 'small', 'small', 'wide', 'small', 'small', 'large', 'small', 'wide', 'small', 'small', 'wide', 'small'];

    // Helper mapping foto background riil yang sangat relevan per unit ekskul
    $getEkskulBg = function($ekskul) {
        if (!empty($ekskul->cover_image) && file_exists(public_path($ekskul->cover_image))) {
            return asset($ekskul->cover_image);
        }

        $name = strtolower($ekskul->name ?? '');
        $cat  = strtolower($ekskul->category ?? '');

        if (str_contains($name, 'marching') || $cat === 'marching_band') {
            return asset('images/ekskul/marching_band_bg.jpg');
        }
        if (str_contains($name, 'pramuka') || str_contains($name, 'gugus') || $cat === 'pramuka' || $cat === 'kepanduan') {
            return asset('images/ekskul/pramuka_bg.jpg');
        }
        if (str_contains($name, 'paskas') || str_contains($name, 'paskibra') || $cat === 'paskibraka') {
            return asset('images/ekskul/paskibraka_bg.jpg');
        }
        if (str_contains($name, 'seni') || str_contains($name, 'sanggar') || str_contains($name, 'hulayo') || str_contains($name, 'tari') || $cat === 'seni_budaya') {
            return asset('images/ekskul/seni_budaya_bg.jpg');
        }
        if (str_contains($name, 'cira') || str_contains($name, 'tech') || str_contains($name, 'robot') || str_contains($name, 'coding') || str_contains($name, 'komputer') || $cat === 'sains_it' || $cat === 'sains_teknologi') {
            return asset('images/ekskul/sains_it_bg.jpg');
        }
        if (str_contains($name, 'tenis') || str_contains($name, 'pingpong')) {
            return asset('images/ekskul/tenis_meja_bg.jpg');
        }
        if (str_contains($name, 'vocal') || str_contains($name, 'vokal') || str_contains($name, 'paduan') || str_contains($name, 'musik') || str_contains($name, 'nyanyi')) {
            return asset('images/ekskul/olah_vocal_bg.jpg');
        }
        if (str_contains($name, 'futsal') || str_contains($name, 'bola kaki') || str_contains($name, 'sepak')) {
            return asset('images/ekskul/futsal_bg.jpg');
        }
        if (str_contains($name, 'renang') || str_contains($name, 'swimming')) {
            return asset('images/ekskul/renang_bg.jpg');
        }
        if (str_contains($name, 'english') || str_contains($name, 'inggris') || str_contains($name, 'bahasa') || str_contains($name, 'debat')) {
            return asset('images/ekskul/english_club_bg.jpg');
        }
        if (str_contains($name, 'cerdas') || str_contains($name, 'olimpiade') || str_contains($name, 'sains')) {
            return asset('images/ekskul/cerdas_cermat_bg.jpg');
        }
        if (str_contains($name, 'volley') || str_contains($name, 'voli') || str_contains($name, 'basket') || $cat === 'olahraga') {
            return asset('images/ekskul/olahraga_bg.jpg');
        }

        return asset('images/ekskul/olahraga_bg.jpg');
    };
@endphp

<section id="ekskul" class="py-10 bg-[#faf8f5] border-t border-[#e7e3d8]">
    <div class="max-w-7xl mx-auto px-4 sm:px-8">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-6 gap-3">
            <div>
                <div class="text-[10px] font-mono-code font-bold text-[#2563eb] uppercase tracking-wider mb-1">
                    ✱ PENGEMBANGAN KARAKTER & MINAT BAKAT
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-[#121316] tracking-tight">
                    Asah Potensi di <span class="highlight-marker">{{ isset($extracurriculars) ? $extracurriculars->count() : '18' }}+ Ekstrakurikuler.</span>
                </h2>
                <p class="text-xs text-[#555] font-medium mt-1 max-w-xl">
                    Wadah karakter tangguh, kreativitas seni, robotika, dan olahraga bagi siswa SMP, SMA, dan SMK.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-white border border-[#121316] rounded-full text-[11px] font-mono-code font-bold text-[#121316] shadow-[2px_2px_0px_#121316]">
                    🎖️ {{ isset($extracurriculars) ? $extracurriculars->count() : '18' }}+ Cabang Terpadu
                </span>
            </div>
        </div>

        <!-- Windows 10 Metro Tile Grid with Dynamic Moving Backgrounds -->
        <div class="win10-tile-grid mb-6">
            @forelse($extracurriculars as $index => $ekskul)
                @php
                    $bgColor = $tileColors[$index % count($tileColors)];
                    $sizeClass = $tileSizePattern[$index % count($tileSizePattern)];
                    $bgImg = $getEkskulBg($ekskul);
                    $memberCount = $ekskul->active_members_count ?? 0;
                    $schoolShort = $ekskul->school?->name ?? 'Yayasan (Lintas 3 Unit)';
                    $categoryLabel = $ekskul->category_label ?? ucwords(str_replace('_', ' ', $ekskul->category ?? 'Ekskul'));
                @endphp
                <div
                    class="win10-tile win10-tile-{{ $sizeClass }} group cursor-pointer"
                    style="background-color: {{ $bgColor }};"
                    onclick="openWin10TileModal({{ json_encode([
                        'name' => $ekskul->name,
                        'category' => $categoryLabel,
                        'school' => $schoolShort,
                        'members' => $memberCount,
                        'description' => $ekskul->description ?? 'Wadah pembinaan karakter, kreativitas, kepemimpinan dan prestasi siswa dibimbing pelatih profesional.',
                        'schedule' => $ekskul->schedule_day_time ?? 'Setiap Sabtu / Terjadwal',
                        'location' => $ekskul->location ?? 'Lingkungan Kampus Perguruan Pembda',
                        'advisor' => $ekskul->advisor_name ?? ($ekskul->manager_name ?? 'Pelatih / Pembina Sekolah'),
                        'bgImg' => $bgImg,
                        'color' => $bgColor,
                    ]) }})"
                    title="Klik untuk info detail {{ $ekskul->name }}"
                >
                    {{-- 1. FOTO BACKGROUND BERGERAK (MOTION ON HOVER) --}}
                    <img
                        src="{{ $bgImg }}"
                        alt="{{ $ekskul->name }}"
                        loading="lazy"
                        class="win10-tile-bg-img"
                    >

                    {{-- 2. GRADIENT OVERLAY FOR HIGH CONTRAST & LEGIBILITY --}}
                    <div class="win10-tile-overlay"></div>

                    {{-- 3. TOP ACCENT BAR --}}
                    <div class="win10-tile-top-bar" style="background-color: {{ $bgColor }};"></div>

                    {{-- 4. TOP BAR ELEMENTS (Category Tag + Member Count) --}}
                    <div class="win10-tile-top-info">
                        <span class="win10-cat-badge">
                            {{ $categoryLabel }}
                        </span>
                        @if($memberCount > 0)
                            <span class="win10-member-badge">
                                👥 {{ $memberCount }}
                            </span>
                        @endif
                    </div>

                    {{-- 5. BOTTOM LABELS (Unit Name + School) --}}
                    <div class="win10-tile-label">
                        <div class="win10-tile-name">{{ $ekskul->name }}</div>
                        <div class="win10-tile-school">{{ $schoolShort }}</div>
                    </div>

                    {{-- 6. HOVER BORDER GLOW --}}
                    <div class="absolute inset-0 border border-white/0 group-hover:border-white/40 transition-colors duration-200 pointer-events-none rounded-[2px]"></div>
                </div>
            @empty
                {{-- Empty state --}}
                <div class="col-span-full text-center py-10 bg-white rounded-2xl border-2 border-dashed border-[#121316]/20">
                    <div class="text-3xl mb-2">🎨</div>
                    <p class="text-xs font-bold text-[#777]">Data kegiatan ekstrakurikuler sedang diperbarui.</p>
                </div>
            @endforelse
        </div>

        <!-- Banner Ringkas Panduan Ekskul (Tactile Modern Style) -->
        <div class="bg-white border-2 border-[#121316] rounded-2xl p-4 sm:p-5 shadow-[3px_3px_0px_#121316] flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-2xl">💡</span>
                <p class="text-xs text-[#444] font-medium leading-normal">
                    <strong class="text-[#121316]">Pendaftaran Ekskul:</strong> Siswa aktif dapat memilih maksimal 2 kegiatan ekstrakurikuler per semester melalui portal siswa PembdaHUB.
                </p>
            </div>
            @auth
                <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-full btn-tactile-red text-xs font-black whitespace-nowrap">
                    Pilih Ekskul di Portal &rarr;
                </a>
            @else
                <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-full btn-tactile-red text-xs font-black whitespace-nowrap">
                    Masuk Portal Siswa &rarr;
                </a>
            @endauth
        </div>

        <!-- Interactive Modal Info Ekskul Windows 10 Style -->
        <div id="win10-ekskul-modal" class="fixed inset-0 z-[99999] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4 animate-fade-in" onclick="closeWin10TileModal(event)">
            <div class="bg-white border-2 border-[#121316] rounded-2xl max-w-lg w-full overflow-hidden shadow-[6px_6px_0px_#121316] transform transition-all" onclick="event.stopPropagation()">
                <!-- Modal Header with Photo Background & Acrylic Color Overlay -->
                <div id="modal-tile-header" class="p-6 text-white relative transition-colors duration-200 min-h-[120px] flex flex-col justify-end overflow-hidden">
                    <img id="modal-tile-bg-img" src="" alt="Cover Ekskul" class="absolute inset-0 w-full h-full object-cover filter brightness-[0.45]">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
                    
                    <button type="button" onclick="closeWin10TileModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-black/50 hover:bg-black/80 text-white flex items-center justify-center font-bold text-xs transition-colors z-10" title="Tutup">
                        ✕
                    </button>
                    
                    <div class="relative z-10">
                        <span id="modal-tile-category" class="text-[9px] font-mono-code uppercase font-bold tracking-wider px-2 py-0.5 bg-white/20 backdrop-blur-sm rounded inline-block mb-1 text-white border border-white/30">Pramuka</span>
                        <h3 id="modal-tile-title" class="text-base sm:text-xl font-black leading-tight text-white drop-shadow-[0_2px_4px_rgba(0,0,0,0.8)]">Nama Ekskul</h3>
                        <p id="modal-tile-school" class="text-xs text-white/90 font-medium mt-0.5">SMAS Pembda 1 Gunungsitoli</p>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-5 space-y-4 text-xs text-[#444]">
                    <div>
                        <div class="text-[10px] font-mono-code font-bold uppercase text-[#777] mb-1">Deskripsi Kegiatan</div>
                        <p id="modal-tile-desc" class="text-[#222] leading-relaxed font-medium">Deskripsi lengkap kegiatan ekstrakurikuler.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 pt-2 border-t border-[#eee]">
                        <div class="bg-[#f7f5f0] p-2.5 rounded-xl border border-[#e5e0d4]">
                            <div class="text-[9px] font-mono-code uppercase font-bold text-[#777] mb-0.5">👥 Anggota Aktif</div>
                            <div id="modal-tile-members" class="text-[#121316] font-extrabold text-xs sm:text-sm">36 Siswa</div>
                        </div>
                        <div class="bg-[#f7f5f0] p-2.5 rounded-xl border border-[#e5e0d4]">
                            <div class="text-[9px] font-mono-code uppercase font-bold text-[#777] mb-0.5">📅 Jadwal</div>
                            <div id="modal-tile-schedule" class="text-[#121316] font-extrabold text-xs truncate">Setiap Sabtu</div>
                        </div>
                        <div class="bg-[#f7f5f0] p-2.5 rounded-xl border border-[#e5e0d4]">
                            <div class="text-[9px] font-mono-code uppercase font-bold text-[#777] mb-0.5">📍 Lokasi</div>
                            <div id="modal-tile-location" class="text-[#121316] font-extrabold text-xs truncate">Lapangan Utama</div>
                        </div>
                        <div class="bg-[#f7f5f0] p-2.5 rounded-xl border border-[#e5e0d4]">
                            <div class="text-[9px] font-mono-code uppercase font-bold text-[#777] mb-0.5">👨‍🏫 Pembina</div>
                            <div id="modal-tile-advisor" class="text-[#121316] font-extrabold text-xs truncate">Guru Pembina</div>
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="pt-3 flex items-center justify-end gap-2 border-t border-[#eee]">
                        <button type="button" onclick="closeWin10TileModal()" class="px-4 py-2 rounded-full border border-[#ccc] hover:bg-[#f0f0f0] text-[#444] font-bold text-xs transition-colors">
                            Tutup
                        </button>
                        @auth
                            <a href="{{ route('dashboard') }}" class="px-5 py-2 rounded-full btn-tactile-red text-xs font-black whitespace-nowrap">
                                Daftar di Portal &rarr;
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-5 py-2 rounded-full btn-tactile-red text-xs font-black whitespace-nowrap">
                                Masuk & Daftar &rarr;
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <script>
            function openWin10TileModal(data) {
                document.getElementById('modal-tile-bg-img').src = data.bgImg || '';
                document.getElementById('modal-tile-category').textContent = data.category || 'EKSTRAKURIKULER';
                document.getElementById('modal-tile-title').textContent = data.name || 'Ekstrakurikuler';
                document.getElementById('modal-tile-school').textContent = data.school || 'Perguruan Pembda';
                document.getElementById('modal-tile-desc').textContent = data.description || '-';
                document.getElementById('modal-tile-members').textContent = (data.members || 0) + ' Siswa';
                document.getElementById('modal-tile-schedule').textContent = data.schedule || 'Terjadwal';
                document.getElementById('modal-tile-location').textContent = data.location || 'Kampus Pembda';
                document.getElementById('modal-tile-advisor').textContent = data.advisor || 'Pembina Sekolah';

                const modal = document.getElementById('win10-ekskul-modal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeWin10TileModal(e) {
                const modal = document.getElementById('win10-ekskul-modal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeWin10TileModal();
            });
        </script>

    </div>
</section>

<style>
    /* ═══════════════════════════════════════════════════════
       WINDOWS 10 METRO LIVE TILES — DYNAMIC MOTION ON HOVER
       ═══════════════════════════════════════════════════════ */

    .win10-tile-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 4px;
    }

    @media (min-width: 768px) {
        .win10-tile-grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 5px;
        }
    }
    @media (min-width: 1024px) {
        .win10-tile-grid {
            grid-template-columns: repeat(6, 1fr);
            gap: 5px;
        }
    }

    /* Base tile container */
    .win10-tile {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 8px 10px;
        min-height: 76px;
        cursor: pointer;
        overflow: hidden;
        border-radius: 3px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
        user-select: none;
        transition: box-shadow 0.25s ease, transform 0.25s ease;
    }

    /* Hover elevation & press effect */
    .win10-tile:hover {
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
        transform: translateY(-2px);
        z-index: 5;
    }
    .win10-tile:active {
        transform: scale(0.97);
    }

    /* ─── DYNAMIC BACKGROUND IMAGE MOTION ─── */
    .win10-tile-bg-img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        filter: brightness(0.85) contrast(1.05) saturate(1.1);
        transform: scale(1.0) translateY(0);
        transition: transform 0.65s cubic-bezier(0.16, 1, 0.3, 1), filter 0.45s ease;
        pointer-events: none;
        z-index: 1;
    }

    /* On Mouse Hover: Dynamic gliding zoom motion */
    .win10-tile:hover .win10-tile-bg-img {
        transform: scale(1.16) translateY(-4px);
        filter: brightness(1.02) contrast(1.1) saturate(1.2);
    }

    /* ─── DARK GRADIENT OVERLAY FOR HIGH READABILITY ─── */
    .win10-tile-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(
            180deg,
            rgba(0, 0, 0, 0.35) 0%,
            rgba(0, 0, 0, 0.15) 35%,
            rgba(0, 0, 0, 0.65) 70%,
            rgba(0, 0, 0, 0.95) 100%
        );
        transition: background 0.3s ease;
        pointer-events: none;
        z-index: 2;
    }
    .win10-tile:hover .win10-tile-overlay {
        background: linear-gradient(
            180deg,
            rgba(0, 0, 0, 0.25) 0%,
            rgba(0, 0, 0, 0.10) 30%,
            rgba(0, 0, 0, 0.70) 65%,
            rgba(0, 0, 0, 0.98) 100%
        );
    }

    /* Top Accent Stripe */
    .win10-tile-top-bar {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        z-index: 4;
    }

    /* Top bar info elements */
    .win10-tile-top-info {
        position: relative;
        z-index: 3;
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        gap: 4px;
    }

    .win10-cat-badge {
        background: rgba(0, 0, 0, 0.45);
        color: rgba(255, 255, 255, 0.95);
        padding: 1.5px 5px;
        border-radius: 3px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 7.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.15);
    }

    .win10-member-badge {
        background: rgba(0, 0, 0, 0.45);
        color: rgba(255, 255, 255, 0.95);
        padding: 1.5px 5px;
        border-radius: 3px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 8px;
        font-weight: 700;
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.15);
    }

    /* Bottom Labels */
    .win10-tile-label {
        position: relative;
        z-index: 3;
        width: 100%;
        text-align: left;
        padding-top: 4px;
    }

    .win10-tile-name {
        color: #ffffff;
        font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
        font-weight: 800;
        font-size: 10.5px;
        line-height: 1.25;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.9);
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
        color: rgba(255, 255, 255, 0.85);
        font-family: 'JetBrains Mono', monospace;
        font-weight: 600;
        font-size: 7.5px;
        margin-top: 1.5px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.9);
    }
    .win10-tile-wide .win10-tile-school,
    .win10-tile-large .win10-tile-school {
        font-size: 8.5px;
    }

    /* Tile sizes — Mobile */
    .win10-tile-small {
        grid-column: span 1;
        grid-row: span 1;
        min-height: 76px;
    }
    .win10-tile-wide {
        grid-column: span 2;
        grid-row: span 1;
        min-height: 76px;
    }
    .win10-tile-tall {
        grid-column: span 1;
        grid-row: span 2;
        min-height: 156px;
    }
    .win10-tile-large {
        grid-column: span 2;
        grid-row: span 2;
        min-height: 156px;
    }

    /* Tablet (min-width: 768px) */
    @media (min-width: 768px) {
        .win10-tile { padding: 9px 12px; }
        .win10-tile-small  { min-height: 86px; }
        .win10-tile-wide   { min-height: 86px; }
        .win10-tile-tall   { min-height: 177px; }
        .win10-tile-large  { min-height: 177px; }
        .win10-tile-name   { font-size: 11px; }
        .win10-tile-wide .win10-tile-name,
        .win10-tile-large .win10-tile-name { font-size: 12.5px; }
        .win10-tile-school { font-size: 8px; }
    }

    /* Desktop (min-width: 1024px) */
    @media (min-width: 1024px) {
        .win10-tile { padding: 10px 13px; }
        .win10-tile-small  { min-height: 90px; }
        .win10-tile-wide   { min-height: 90px; }
        .win10-tile-tall   { min-height: 185px; }
        .win10-tile-large  { min-height: 185px; }
        .win10-tile-name   { font-size: 11.5px; }
        .win10-tile-wide .win10-tile-name,
        .win10-tile-large .win10-tile-name { font-size: 13px; }
        .win10-tile-school { font-size: 8.5px; }
    }
</style>
