{{-- SHOWCASE POSTER WARNA-WARNI — 100% Real Database Data (Project, PKL, Modul LMS, Prestasi) --}}
@php
    $colorThemes = [
        ['bg' => 'bg-[#f4ece1]', 'text' => 'text-[#121316]', 'sub' => 'text-[#777]', 'badge' => 'bg-[#10b981] text-white', 'border' => 'border-[#121316]/20'],
        ['bg' => 'bg-[#fbc02d]', 'text' => 'text-[#121316]', 'sub' => 'text-[#444]', 'badge' => 'bg-[#121316] text-[#fbc02d]', 'border' => 'border-[#121316]/20'],
        ['bg' => 'bg-[#2563eb]', 'text' => 'text-white', 'sub' => 'text-blue-200', 'badge' => 'bg-white text-[#2563eb]', 'border' => 'border-white/20'],
        ['bg' => 'bg-[#ff3823]', 'text' => 'text-white', 'sub' => 'text-red-200', 'badge' => 'bg-[#fbc02d] text-[#121316]', 'border' => 'border-white/20'],
        ['bg' => 'bg-[#121316]', 'text' => 'text-white', 'sub' => 'text-slate-400', 'badge' => 'bg-[#ff3823] text-white', 'border' => 'border-white/20'],
        ['bg' => 'bg-[#10b981]', 'text' => 'text-white', 'sub' => 'text-emerald-100', 'badge' => 'bg-[#121316] text-[#10b981]', 'border' => 'border-white/20'],
        ['bg' => 'bg-[#4f46e5]', 'text' => 'text-white', 'sub' => 'text-indigo-200', 'badge' => 'bg-white text-[#4f46e5]', 'border' => 'border-white/20'],
        ['bg' => 'bg-[#ea580c]', 'text' => 'text-white', 'sub' => 'text-orange-100', 'badge' => 'bg-[#121316] text-amber-300', 'border' => 'border-white/20'],
    ];
    $themeIndex = 0;
@endphp

<section id="showcase" class="py-20 px-4 sm:px-8">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="max-w-2xl mb-8">
            <div class="text-[11px] font-mono-code font-bold text-[#ff3823] uppercase tracking-wider mb-2">
                ✱ ETALASE KARYA SISWA, PKL & MODUL LMS
            </div>
            <h2 class="text-3xl sm:text-4xl font-black text-[#121316] tracking-tight mb-3">
                Dari bengkel kejuruan sampai laboratorium riset.
            </h2>
            <p class="text-xs sm:text-sm text-[#555] font-medium leading-relaxed">
                Semua karya di bawah lahir dari riset nyata siswa, logbook kemitraan DUDI, dan modul KBM aktif semester ganjil 2026/2027 — data asli database sekolah.
            </p>
        </div>

        <!-- Filter Tab Buttons -->
        <div class="flex flex-wrap gap-2 mb-10">
            <button type="button" onclick="filterShowcase('semua')" id="tab-semua" class="px-5 py-2 rounded-full font-mono-code text-xs font-bold filter-btn-active">SEMUA</button>
            <button type="button" onclick="filterShowcase('project')" id="tab-project" class="px-5 py-2 rounded-full font-mono-code text-xs font-bold filter-btn-inactive">PROJECT SISWA ({{ isset($finalProjectsShowcase) ? $finalProjectsShowcase->count() : 0 }})</button>
            <button type="button" onclick="filterShowcase('pkl')" id="tab-pkl" class="px-5 py-2 rounded-full font-mono-code text-xs font-bold filter-btn-inactive">LOGBOOK PKL ({{ isset($pklShowcase) ? $pklShowcase->count() : 0 }})</button>
            <button type="button" onclick="filterShowcase('lms')" id="tab-lms" class="px-5 py-2 rounded-full font-mono-code text-xs font-bold filter-btn-inactive">MODUL LMS ({{ isset($trainingModules) ? $trainingModules->count() : 0 }})</button>
            <button type="button" onclick="filterShowcase('prestasi')" id="tab-prestasi" class="px-5 py-2 rounded-full font-mono-code text-xs font-bold filter-btn-inactive">PRESTASI JUARA ({{ isset($achievements) ? $achievements->count() : 0 }})</button>
        </div>

        <!-- THE VIBRANT POSTER GRID -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6" id="showcase-grid">
            
            {{-- 1. LOOP REAL PROJECT AKHIR / PENELITIAN SISWA --}}
            @forelse($finalProjectsShowcase->take(4) as $project)
                @php
                    $t = $colorThemes[$themeIndex % count($colorThemes)];
                    $themeIndex++;
                @endphp
                <article class="showcase-card poster-card {{ $t['bg'] }} {{ $t['text'] }} p-6 flex flex-col justify-between min-h-[340px]" data-type="project">
                    <div>
                        <div class="text-[10px] font-mono-code font-bold uppercase {{ $t['sub'] }} mb-3 truncate">
                            {{ $project->student?->school?->name ?? 'SMK/SMA PEMBDA' }} &bull; {{ $project->field ?? 'KARYA AKHIR' }}
                        </div>
                        <h3 class="text-2xl font-black uppercase leading-tight tracking-tight mb-3 line-clamp-3">
                            {{ $project->title }}
                        </h3>
                        <span class="inline-block px-2.5 py-1 rounded {{ $t['badge'] }} text-[10px] font-mono-code font-bold">
                            {{ strtoupper($project->status ?? 'VERIFIED') }}
                        </span>
                        @if($project->abstract)
                            <p class="text-xs {{ $t['sub'] }} mt-3 line-clamp-2 leading-relaxed font-medium">
                                {{ $project->abstract }}
                            </p>
                        @endif
                    </div>
                    <div class="pt-4 border-t {{ $t['border'] }} flex items-center justify-between text-[11px] font-mono-code font-bold mt-4">
                        <span class="truncate">{{ $project->student?->full_name ?? 'Tim Siswa' }}</span>
                        <span class="px-2 py-0.5 rounded bg-[#121316] text-white text-[10px]">PROJECT</span>
                    </div>
                </article>
            @empty
            @endforelse

            {{-- 2. LOOP REAL PKL SHOWCASE (LOGBOOK DUDI & MONITORING) --}}
            @forelse($pklShowcase->take(4) as $pkl)
                @php
                    $t = $colorThemes[$themeIndex % count($colorThemes)];
                    $themeIndex++;
                @endphp
                <article class="showcase-card poster-card {{ $t['bg'] }} {{ $t['text'] }} p-6 flex flex-col justify-between min-h-[340px]" data-type="pkl">
                    <div>
                        <div class="text-[10px] font-mono-code font-bold uppercase {{ $t['sub'] }} mb-3 truncate">
                            {{ $pkl['dudi_name'] }} &bull; PKL
                        </div>
                        <h3 class="text-2xl font-black uppercase leading-tight tracking-tight mb-2 line-clamp-3">
                            {{ $pkl['person_name'] }}
                        </h3>
                        <span class="inline-block px-2.5 py-1 rounded {{ $t['badge'] }} text-[10px] font-mono-code font-bold">
                            {{ $pkl['type'] === 'monitoring' ? 'MONITORING GURU' : 'LOGBOOK DUDI' }}
                        </span>
                        <p class="text-xs {{ $t['sub'] }} mt-3 line-clamp-3 leading-relaxed font-medium">
                            "{{ $pkl['description'] }}"
                        </p>
                    </div>
                    <div class="pt-4 border-t {{ $t['border'] }} flex items-center justify-between text-[11px] font-mono-code font-bold mt-4">
                        <span class="truncate">{{ $pkl['school_name'] }}</span>
                        <span>{{ $pkl['date'] }}</span>
                    </div>
                </article>
            @empty
            @endforelse

            {{-- 3. LOOP REAL MODUL BAHAN AJAR LMS --}}
            @forelse($trainingModules->take(4) as $module)
                @php
                    $t = $colorThemes[$themeIndex % count($colorThemes)];
                    $themeIndex++;
                @endphp
                <article class="showcase-card poster-card {{ $t['bg'] }} {{ $t['text'] }} p-6 flex flex-col justify-between min-h-[340px]" data-type="lms">
                    <div>
                        <div class="text-[10px] font-mono-code font-bold uppercase {{ $t['sub'] }} mb-3 truncate">
                            KURIKULUM MERDEKA &bull; {{ $module->category ?? 'MODUL AJAR' }}
                        </div>
                        <h3 class="text-2xl font-black uppercase leading-tight tracking-tight mb-2 line-clamp-3">
                            {{ $module->title }}
                        </h3>
                        <span class="inline-block px-2.5 py-1 rounded {{ $t['badge'] }} text-[10px] font-mono-code font-bold">
                            {{ $module->target_role ? strtoupper($module->target_role) : 'UMUM' }}
                        </span>
                        @if($module->description)
                            <p class="text-xs {{ $t['sub'] }} mt-3 line-clamp-2 leading-relaxed font-medium">
                                {{ $module->description }}
                            </p>
                        @endif
                    </div>
                    <div class="pt-4 border-t {{ $t['border'] }} flex items-center justify-between text-[11px] font-mono-code font-bold mt-4">
                        <span>MODUL PDF</span>
                        <a href="{{ asset('storage/' . $module->pdf_file) }}" target="_blank" class="hover:underline text-[10px]">UNDUH &darr;</a>
                    </div>
                </article>
            @empty
            @endforelse

            {{-- 4. LOOP REAL PRESTASI & PENGHARGAAN SISWA --}}
            @forelse($achievements->take(4) as $ach)
                @php
                    $t = $colorThemes[$themeIndex % count($colorThemes)];
                    $themeIndex++;
                @endphp
                <article class="showcase-card poster-card {{ $t['bg'] }} {{ $t['text'] }} p-6 flex flex-col justify-between min-h-[340px]" data-type="prestasi">
                    <div>
                        <div class="text-[10px] font-mono-code font-bold uppercase {{ $t['sub'] }} mb-3 truncate">
                            {{ $ach->student?->school?->name ?? 'PEMBDA NIAS' }} &bull; {{ strtoupper($ach->achievement_level ?? 'PROVINSI') }}
                        </div>
                        <h3 class="text-2xl font-black uppercase leading-tight tracking-tight mb-2 line-clamp-3">
                            {{ $ach->title ?? 'Prestasi Kejuaraan' }}
                        </h3>
                        <span class="inline-block px-2.5 py-1 rounded {{ $t['badge'] }} text-[10px] font-mono-code font-bold">
                            {{ strtoupper(str_replace('_', ' ', $ach->ranking ?? 'JUARA')) }}
                        </span>
                        <p class="text-xs {{ $t['sub'] }} mt-3 line-clamp-2 leading-relaxed font-medium">
                            Diraih oleh {{ $ach->student?->full_name ?? 'Siswa' }} pada ajang {{ $ach->event_name ?? 'Kompetisi Sekolah' }}.
                        </p>
                    </div>
                    <div class="pt-4 border-t {{ $t['border'] }} flex items-center justify-between text-[11px] font-mono-code font-bold mt-4">
                        <span>{{ $ach->incident_date ? \Carbon\Carbon::parse($ach->incident_date)->translatedFormat('d M Y') : '2026' }}</span>
                        <span>🏆 JUARA</span>
                    </div>
                </article>
            @empty
            @endforelse

        </div>

    </div>
</section>

<script>
    function filterShowcase(type) {
        const tabs = ['semua', 'project', 'pkl', 'lms', 'prestasi'];
        tabs.forEach(t => {
            const btn = document.getElementById(`tab-${t}`);
            if (btn) {
                if (t === type) {
                    btn.className = 'px-5 py-2 rounded-full font-mono-code text-xs font-bold filter-btn-active';
                } else {
                    btn.className = 'px-5 py-2 rounded-full font-mono-code text-xs font-bold filter-btn-inactive';
                }
            }
        });

        const cards = document.querySelectorAll('.showcase-card');
        cards.forEach(card => {
            if (type === 'semua' || card.getAttribute('data-type') === type) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }
</script>
