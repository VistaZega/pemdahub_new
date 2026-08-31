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
                        'description' => $ekskul->description ?? 'Wadah pembinaan karakter, kreativitas, kepemimpinan dan prestasi siswa.',
                        'schedule' => $ekskul->schedule_day_time ?? 'Setiap Sabtu / Terjadwal',
                        'location' => $ekskul->location ?? 'Lingkungan Kampus Perguruan Pembda',
                        'advisor' => $ekskul->advisor_name ?? ($ekskul->manager_name ?? 'Pelatih / Pembina Sekolah'),
                        'icon' => $icon,
                        'color' => $bgColor,
                    ]) }})"
                    title="Klik untuk info detail {{ $ekskul->name }}"
                >
                    {{-- Badge anggota (pojok kanan atas) --}}
                    @if($memberCount > 0)
                        <div class="absolute top-2 right-2 sm:top-2.5 sm:right-2.5 text-white/80 bg-black/20 px-1.5 py-0.5 rounded text-[9px] sm:text-[10px] font-mono-code font-bold backdrop-blur-sm">
                            👥 {{ $memberCount }}
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
                    <div class="absolute inset-0 bg-white/0 group-hover:bg-white/10 transition-colors duration-150 pointer-events-none"></div>
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
        <div class="bg-[#262626] border border-[#3a3a3a] rounded-xl p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-2xl">💡</span>
                <p class="text-xs text-[#bbb] font-medium leading-normal">
                    <strong class="text-white">Pendaftaran Ekskul:</strong> Siswa aktif dapat memilih maksimal 2 kegiatan ekstrakurikuler per semester melalui portal siswa PembdaHUB.
                </p>
            </div>
            @auth
                <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded bg-[#0078d4] text-white text-xs font-bold whitespace-nowrap hover:bg-[#106ebe] transition-colors shadow-[0_2px_8px_rgba(0,120,212,0.4)]">
                    Pilih Ekskul di Portal &rarr;
                </a>
            @else
                <a href="{{ route('login') }}" class="px-5 py-2.5 rounded bg-[#0078d4] text-white text-xs font-bold whitespace-nowrap hover:bg-[#106ebe] transition-colors shadow-[0_2px_8px_rgba(0,120,212,0.4)]">
                    Masuk Portal Siswa &rarr;
                </a>
            @endauth
        </div>

        <!-- Interactive Modal Info Ekskul Windows 10 Style -->
        <div id="win10-ekskul-modal" class="fixed inset-0 z-[99999] hidden items-center justify-center bg-black/70 backdrop-blur-sm p-4 animate-fade-in" onclick="closeWin10TileModal(event)">
            <div class="bg-[#1f1f1f] border-2 border-[#444] rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl text-white transform transition-all" onclick="event.stopPropagation()">
                <!-- Modal Header Tile Style -->
                <div id="modal-tile-header" class="p-6 text-white relative transition-colors duration-200" style="background-color: #0078d4;">
                    <button type="button" onclick="closeWin10TileModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-black/30 hover:bg-black/50 text-white flex items-center justify-center font-bold text-sm transition-colors">
                        ✕
                    </button>
                    <div class="flex items-center gap-4">
                        <div id="modal-tile-icon" class="text-4xl sm:text-5xl p-2 bg-black/20 rounded-xl">🎭</div>
                        <div>
                            <span id="modal-tile-category" class="text-[10px] font-mono-code uppercase font-bold tracking-wider px-2 py-0.5 bg-black/30 rounded inline-block mb-1">Pramuka</span>
                            <h3 id="modal-tile-title" class="text-lg sm:text-xl font-black leading-tight">Nama Ekskul</h3>
                            <p id="modal-tile-school" class="text-xs text-white/80 font-medium mt-0.5">SMAS Pembda 1</p>
                        </div>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4 text-xs text-[#ccc]">
                    <div>
                        <div class="text-[10px] font-mono-code font-bold uppercase text-[#888] mb-1">Deskripsi Kegiatan</div>
                        <p id="modal-tile-desc" class="text-white/90 leading-relaxed font-normal">Deskripsi lengkap kegiatan ekstrakurikuler.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2 border-t border-[#333]">
                        <div class="bg-[#2a2a2a] p-3 rounded-lg border border-[#383838]">
                            <div class="text-[9px] font-mono-code uppercase font-bold text-[#888] mb-0.5">👥 Anggota Aktif</div>
                            <div id="modal-tile-members" class="text-white font-bold text-sm">36 Siswa</div>
                        </div>
                        <div class="bg-[#2a2a2a] p-3 rounded-lg border border-[#383838]">
                            <div class="text-[9px] font-mono-code uppercase font-bold text-[#888] mb-0.5">📅 Jadwal</div>
                            <div id="modal-tile-schedule" class="text-white font-bold text-xs truncate">Setiap Sabtu</div>
                        </div>
                        <div class="bg-[#2a2a2a] p-3 rounded-lg border border-[#383838]">
                            <div class="text-[9px] font-mono-code uppercase font-bold text-[#888] mb-0.5">📍 Lokasi</div>
                            <div id="modal-tile-location" class="text-white font-bold text-xs truncate">Lapangan Utama</div>
                        </div>
                        <div class="bg-[#2a2a2a] p-3 rounded-lg border border-[#383838]">
                            <div class="text-[9px] font-mono-code uppercase font-bold text-[#888] mb-0.5">👨‍🏫 Pembina</div>
                            <div id="modal-tile-advisor" class="text-white font-bold text-xs truncate">Guru Pembina</div>
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="pt-3 flex justify-end gap-2">
                        <button type="button" onclick="closeWin10TileModal()" class="px-4 py-2 rounded bg-[#333] hover:bg-[#444] text-white font-bold text-xs transition-colors">
                            Tutup
                        </button>
                        @auth
                            <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded bg-[#0078d4] hover:bg-[#106ebe] text-white font-bold text-xs transition-colors">
                                Daftar di Portal &rarr;
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-4 py-2 rounded bg-[#0078d4] hover:bg-[#106ebe] text-white font-bold text-xs transition-colors">
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
