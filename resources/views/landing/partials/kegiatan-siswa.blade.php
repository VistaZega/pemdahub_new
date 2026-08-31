{{-- EKSTRAKURIKULER & MINAT BAKAT — Windows 10 Metro Tile Design ($extracurriculars) --}}
@php
    // Palet warna tile ala Windows 10 — flat, bold, beragam
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

        <!-- Windows 10 Metro Tile Grid (Compact ~70% Height) -->
        <div class="win10-tile-grid mb-6">
            @forelse($extracurriculars as $index => $ekskul)
                @php
                    $bgColor = $tileColors[$index % count($tileColors)];
                    $sizeClass = $tileSizePattern[$index % count($tileSizePattern)];
                    $icon = $ekskul->icon ?? ($categoryIcons[$ekskul->category] ?? '🎯');
                    $memberCount = $ekskul->active_members_count ?? 0;
                    $schoolShort = $ekskul->school?->name ?? 'Yayasan (Lintas 3 Unit)';
                @endphp
                <div
                    class="win10-tile win10-tile-{{ $sizeClass }} group cursor-pointer"
                    style="background-color: {{ $bgColor }};"
                    onclick="openWin10TileModal({{ json_encode([
                        'name' => $ekskul->name,
                        'category' => $ekskul->category_label ?? ucwords(str_replace('_', ' ', $ekskul->category ?? 'Ekskul')),
                        'school' => $schoolShort,
                        'members' => $memberCount,
                        'description' => $ekskul->description ?? 'Wadah pembinaan karakter, kreativitas, kepemimpinan dan prestasi siswa dibimbing pelatih profesional.',
                        'schedule' => $ekskul->schedule_day_time ?? 'Setiap Sabtu / Terjadwal',
                        'location' => $ekskul->location ?? 'Lingkungan Kampus Perguruan Pembda',
                        'advisor' => $ekskul->advisor_name ?? ($ekskul->manager_name ?? 'Pelatih / Pembina Sekolah'),
                        'icon' => $icon,
                        'color' => $bgColor,
                    ]) }})"
                    title="Klik untuk detail {{ $ekskul->name }}"
                >
                    {{-- Badge anggota (pojok kanan atas) --}}
                    @if($memberCount > 0)
                        <div class="win10-member-badge">
                            👥 {{ $memberCount }}
                        </div>
                    @endif

                    {{-- Ikon di tengah --}}
                    <div class="win10-tile-icon">
                        <span>{{ $icon }}</span>
                    </div>

                    {{-- Label nama di bawah-kiri --}}
                    <div class="win10-tile-label">
                        <div class="win10-tile-name">{{ $ekskul->name }}</div>
                        <div class="win10-tile-school">{{ $schoolShort }}</div>
                    </div>

                    {{-- Hover overlay efek --}}
                    <div class="absolute inset-0 bg-white/0 group-hover:bg-white/10 transition-colors duration-150 pointer-events-none"></div>
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
                <!-- Modal Header Tile Style -->
                <div id="modal-tile-header" class="p-5 text-white relative transition-colors duration-200" style="background-color: #0078d4;">
                    <button type="button" onclick="closeWin10TileModal()" class="absolute top-4 right-4 w-7 h-7 rounded-full bg-black/30 hover:bg-black/50 text-white flex items-center justify-center font-bold text-xs transition-colors" title="Tutup">
                        ✕
                    </button>
                    <div class="flex items-center gap-3.5 pr-8">
                        <div id="modal-tile-icon" class="text-3xl sm:text-4xl p-2 bg-black/20 rounded-xl flex-shrink-0">🎭</div>
                        <div>
                            <span id="modal-tile-category" class="text-[9px] font-mono-code uppercase font-bold tracking-wider px-2 py-0.5 bg-black/30 rounded inline-block mb-1 text-white">Pramuka</span>
                            <h3 id="modal-tile-title" class="text-base sm:text-lg font-black leading-tight text-white">Nama Ekskul</h3>
                            <p id="modal-tile-school" class="text-[11px] text-white/80 font-medium mt-0.5">SMAS Pembda 1</p>
                        </div>
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
                            <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-full btn-tactile-red text-xs font-black whitespace-nowrap">
                                Daftar di Portal &rarr;
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-4 py-2 rounded-full btn-tactile-red text-xs font-black whitespace-nowrap">
                                Masuk & Daftar &rarr;
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <script>
            function openWin10TileModal(data) {
                document.getElementById('modal-tile-header').style.backgroundColor = data.color || '#0078d4';
                document.getElementById('modal-tile-icon').textContent = data.icon || '🎯';
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
       WINDOWS 10 METRO TILE GRID — COMPACT (70% HEIGHT)
       ═══════════════════════════════════════════════════════ */

    .win10-tile-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 3px;
    }

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
        justify-content: space-between;
        padding: 8px 10px;
        min-height: 72px;
        cursor: pointer;
        overflow: hidden;
        border-radius: 2px;
        transition: filter 0.15s ease, transform 0.15s ease;
        user-select: none;
    }
    .win10-tile:hover {
        filter: brightness(1.1);
        transform: scale(1.015);
        z-index: 2;
    }
    .win10-tile:active {
        transform: scale(0.97);
        filter: brightness(0.92);
    }

    /* Tile sizes — Mobile */
    .win10-tile-small {
        grid-column: span 1;
        grid-row: span 1;
        min-height: 72px;
    }
    .win10-tile-wide {
        grid-column: span 2;
        grid-row: span 1;
        min-height: 72px;
    }
    .win10-tile-tall {
        grid-column: span 1;
        grid-row: span 2;
        min-height: 147px;
    }
    .win10-tile-large {
        grid-column: span 2;
        grid-row: span 2;
        min-height: 147px;
    }

    /* Tablet (min-width: 768px) */
    @media (min-width: 768px) {
        .win10-tile { padding: 9px 11px; }
        .win10-tile-small  { min-height: 82px; }
        .win10-tile-wide   { min-height: 82px; }
        .win10-tile-tall   { min-height: 168px; }
        .win10-tile-large  { min-height: 168px; }
    }

    /* Desktop (min-width: 1024px) */
    @media (min-width: 1024px) {
        .win10-tile { padding: 10px 12px; }
        .win10-tile-small  { min-height: 88px; }
        .win10-tile-wide   { min-height: 88px; }
        .win10-tile-tall   { min-height: 180px; }
        .win10-tile-large  { min-height: 180px; }
    }

    /* Member Badge */
    .win10-member-badge {
        position: absolute;
        top: 6px;
        right: 6px;
        background: rgba(0, 0, 0, 0.28);
        color: rgba(255, 255, 255, 0.95);
        padding: 1px 5px;
        border-radius: 4px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 8px;
        font-weight: 700;
        backdrop-filter: blur(2px);
        z-index: 1;
    }

    /* Icon in center */
    .win10-tile-icon {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        line-height: 1;
        filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.25));
    }
    .win10-tile-wide .win10-tile-icon {
        font-size: 1.8rem;
    }
    .win10-tile-large .win10-tile-icon,
    .win10-tile-tall .win10-tile-icon {
        font-size: 2.3rem;
    }

    @media (min-width: 768px) {
        .win10-tile-icon { font-size: 1.6rem; }
        .win10-tile-wide .win10-tile-icon { font-size: 2.1rem; }
        .win10-tile-large .win10-tile-icon,
        .win10-tile-tall .win10-tile-icon { font-size: 2.6rem; }
    }

    /* Labels at bottom-left */
    .win10-tile-label {
        width: 100%;
        text-align: left;
        padding-top: 2px;
    }
    .win10-tile-name {
        color: #ffffff;
        font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
        font-weight: 800;
        font-size: 10px;
        line-height: 1.2;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
    }
    .win10-tile-wide .win10-tile-name,
    .win10-tile-large .win10-tile-name {
        font-size: 11.5px;
        white-space: normal;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    .win10-tile-school {
        color: rgba(255, 255, 255, 0.75);
        font-family: 'JetBrains Mono', monospace;
        font-weight: 600;
        font-size: 7.5px;
        margin-top: 1px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.3);
    }
    .win10-tile-wide .win10-tile-school,
    .win10-tile-large .win10-tile-school {
        font-size: 8.5px;
    }

    @media (min-width: 768px) {
        .win10-tile-name { font-size: 10.5px; }
        .win10-tile-wide .win10-tile-name,
        .win10-tile-large .win10-tile-name { font-size: 12px; }
        .win10-tile-school { font-size: 8px; }
    }
</style>
