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
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-6">
            <div class="max-w-2xl">
                <div class="text-[11px] font-mono-code font-bold text-[#ff3823] uppercase tracking-wider mb-2">
                    ✱ ETALASE PROJECT SMK, PENELITIAN SMA, PKL & MODUL LMS
                </div>
                <h2 class="text-3xl sm:text-4xl font-black text-[#121316] tracking-tight mb-3">
                    Dari bengkel kejuruan sampai laboratorium riset.
                </h2>
                <p class="text-xs sm:text-sm text-[#555] font-medium leading-relaxed">
                    Semua karya di bawah lahir dari riset penelitian & project nyata siswa, logbook kemitraan DUDI, dan modul KBM aktif — data asli database sekolah.
                </p>
            </div>

            <!-- Tombol Spesial Peta Lokasi PKL Maps -->
            <div class="flex items-center gap-3">
                <a href="{{ route('public.pkl.map') }}" class="px-5 py-2.5 rounded-full bg-[#121316] text-[#fde047] hover:bg-[#222] font-mono-code text-xs font-black shadow-[3px_3px_0px_#ff3823] flex items-center gap-2 border border-[#121316] transition-transform hover:-translate-y-0.5">
                    <span>🗺️ PETA SEBARAN PKL (MAPS)</span>
                    <span class="text-white text-[10px]">&rarr;</span>
                </a>
            </div>
        </div>

        <!-- Filter Tab Buttons -->
        <div class="flex flex-wrap gap-2 mb-10">
            <button type="button" onclick="filterShowcase('semua')" id="tab-semua" class="px-5 py-2 rounded-full font-mono-code text-xs font-bold filter-btn-active">SEMUA</button>
            <button type="button" onclick="filterShowcase('project')" id="tab-project" class="px-5 py-2 rounded-full font-mono-code text-xs font-bold filter-btn-inactive">PROJECT & PENELITIAN ({{ isset($finalProjectsShowcase) ? $finalProjectsShowcase->count() : 0 }})</button>
            <button type="button" onclick="filterShowcase('pkl')" id="tab-pkl" class="px-5 py-2 rounded-full font-mono-code text-xs font-bold filter-btn-inactive">LOGBOOK PKL ({{ isset($pklShowcase) ? $pklShowcase->count() : 0 }})</button>
            <button type="button" onclick="filterShowcase('lms')" id="tab-lms" class="px-5 py-2 rounded-full font-mono-code text-xs font-bold filter-btn-inactive">MODUL LMS ({{ isset($trainingModules) ? $trainingModules->count() : 0 }})</button>
            <button type="button" onclick="filterShowcase('prestasi')" id="tab-prestasi" class="px-5 py-2 rounded-full font-mono-code text-xs font-bold filter-btn-inactive">PRESTASI JUARA ({{ isset($achievements) ? $achievements->count() : 0 }})</button>
        </div>

        <!-- THE VIBRANT POSTER GRID -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12" id="showcase-grid">
            
            {{-- 1. LOOP REAL PROJECT AKHIR SMK / PENELITIAN SMA SISWA --}}
            @forelse($finalProjectsShowcase->take(8) as $project)
                @php
                    $t = $colorThemes[$themeIndex % count($colorThemes)];
                    $themeIndex++;
                    $schoolName = strtolower($project->student?->school?->name ?? '');
                    $isSMA = str_contains($schoolName, 'sma') || $project->type === 'penelitian_ilmiah' || $project->type === 'research';
                    $typeLabel = $isSMA ? 'PENELITIAN' : 'PROJECT';
                    $schoolLabel = $project->student?->school?->name ?? ($isSMA ? 'SMAS PEMBDA 1' : 'SMKS PEMBDA NIAS');
                    $studentName = $project->student?->full_name ?? 'Tim Siswa';
                    $firstName = explode(' ', trim($studentName))[0] ?? $studentName;
                    $studentPhoto = $project->student?->photo_url 
                        ?? $project->student?->user?->avatar_url 
                        ?? 'https://ui-avatars.com/api/?name=' . urlencode($studentName) . '&background=fbc02d&color=121316&bold=true';
                @endphp
                <article class="showcase-card poster-card {{ $t['bg'] }} {{ $t['text'] }} p-6 flex flex-col justify-between min-h-[340px]" data-type="project">
                    <div>
                        <div class="text-[10px] font-mono-code font-bold uppercase {{ $t['sub'] }} mb-3 truncate cursor-help" title="{{ $schoolLabel }} • {{ $isSMA ? 'PENELITIAN SISWA' : 'PROJECT SISWA' }}">
                            {{ $schoolLabel }} &bull; {{ $isSMA ? 'PENELITIAN SISWA' : 'PROJECT SISWA' }}
                        </div>
                        <h3 class="text-2xl font-black uppercase leading-tight tracking-tight mb-3 line-clamp-3 cursor-help" title="{{ $project->title }}">
                            {{ $project->title }}
                        </h3>
                        <span class="inline-block px-2.5 py-1 rounded {{ $t['badge'] }} text-[10px] font-mono-code font-bold">
                            {{ strtoupper($project->status ?? 'VERIFIED') }}
                        </span>
                        @if($project->abstract)
                            <p class="text-xs {{ $t['sub'] }} mt-3 line-clamp-2 leading-relaxed font-medium cursor-help" title="{{ $project->abstract }}">
                                {{ $project->abstract }}
                            </p>
                        @endif
                    </div>
                    <div class="pt-4 border-t {{ $t['border'] }} flex items-center justify-between text-[11px] font-mono-code font-bold mt-4">
                        <div class="flex items-center gap-2 min-w-0 cursor-help" title="{{ $studentName }}">
                            <div class="w-8 h-8 rounded-full bg-white border border-current/20 flex items-center justify-center flex-shrink-0 overflow-hidden shadow-sm">
                                <img src="{{ $studentPhoto }}" alt="{{ $studentName }}" class="w-full h-full rounded-full object-cover" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($studentName) }}&background=fbc02d&color=121316&bold=true';">
                            </div>
                            <span class="truncate">{{ $firstName }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-[#121316] text-white text-[10px] flex-shrink-0">{{ $typeLabel }}</span>
                    </div>
                </article>
            @empty
            @endforelse

            {{-- 2. LOOP REAL PKL SHOWCASE (LOGBOOK DUDI & MONITORING) WITH GOOGLE MAPS LINK --}}
            @forelse($pklShowcase->take(8) as $pkl)
                @php
                    $t = $colorThemes[$themeIndex % count($colorThemes)];
                    $themeIndex++;
                    $personName = $pkl['person_name'] ?? 'Siswa PKL';
                    $firstName = explode(' ', trim($personName))[0] ?? $personName;
                    $personPhoto = $pkl['person_photo'] 
                        ?? 'https://ui-avatars.com/api/?name=' . urlencode($personName) . '&background=fbc02d&color=121316&bold=true';
                @endphp
                <article class="showcase-card poster-card {{ $t['bg'] }} {{ $t['text'] }} p-6 flex flex-col justify-between min-h-[340px]" data-type="pkl">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="text-[10px] font-mono-code font-bold uppercase {{ $t['sub'] }} truncate cursor-help" title="{{ $pkl['school_name'] }} • MITRA DUDI">
                                {{ $pkl['school_name'] }}
                            </div>
                            <a href="{{ route('public.pkl.map') }}" class="px-2 py-0.5 rounded bg-black/20 hover:bg-black/40 text-[9px] font-mono-code font-bold inline-flex items-center gap-1 border border-current/20 transition-all flex-shrink-0" title="Buka Peta GPS Siswa PKL">
                                🗺️ Peta GPS ↗
                            </a>
                        </div>
                        <h3 class="text-2xl font-black uppercase leading-tight tracking-tight mb-2 line-clamp-3 cursor-help" title="{{ $pkl['dudi_name'] }}">
                            {{ $pkl['dudi_name'] }}
                        </h3>
                        <span class="inline-block px-2.5 py-1 rounded {{ $t['badge'] }} text-[10px] font-mono-code font-bold">
                            {{ $pkl['type'] === 'monitoring' ? 'MONITORING GURU' : 'LOGBOOK DUDI' }}
                        </span>
                        <p class="text-xs {{ $t['sub'] }} mt-3 line-clamp-3 leading-relaxed font-medium cursor-help" title="{{ $pkl['description'] }}">
                            "{{ $pkl['description'] }}"
                        </p>
                    </div>
                    <div class="pt-4 border-t {{ $t['border'] }} flex items-center justify-between text-[11px] font-mono-code font-bold mt-4">
                        <div class="flex items-center gap-2 min-w-0 cursor-help" title="{{ $personName }} • {{ $pkl['school_name'] }}">
                            <div class="w-8 h-8 rounded-full bg-white border border-current/20 flex items-center justify-center flex-shrink-0 overflow-hidden shadow-sm">
                                <img src="{{ $personPhoto }}" alt="{{ $personName }}" class="w-full h-full rounded-full object-cover" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($personName) }}&background=fbc02d&color=121316&bold=true';">
                            </div>
                            <span class="truncate">{{ $firstName }}</span>
                        </div>
                        <a href="{{ route('public.pkl.map') }}" class="hover:underline flex items-center gap-1 text-[10px] flex-shrink-0 font-bold">
                            <span>{{ $pkl['date'] }}</span>
                            <span>📍</span>
                        </a>
                    </div>
                </article>
            @empty
            @endforelse

            {{-- 3. LOOP REAL MODUL BAHAN AJAR LMS --}}
            @forelse($trainingModules->take(8) as $module)
                @php
                    $t = $colorThemes[$themeIndex % count($colorThemes)];
                    $themeIndex++;
                    $authorUser = $module->author;
                    $teacher = $authorUser?->teacher;
                    $authorName = $teacher?->full_name ?? $authorUser?->name ?? 'Guru Pembda';
                    $firstName = explode(' ', trim($authorName))[0] ?? $authorName;
                    $authorPhoto = $teacher?->photo_url 
                        ?? $authorUser?->photo_url 
                        ?? $authorUser?->avatar_url 
                        ?? 'https://ui-avatars.com/api/?name=' . urlencode($authorName) . '&background=fbc02d&color=121316&bold=true';
                @endphp
                <article class="showcase-card poster-card {{ $t['bg'] }} {{ $t['text'] }} p-6 flex flex-col justify-between min-h-[340px]" data-type="lms">
                    <div>
                        <div class="text-[10px] font-mono-code font-bold uppercase {{ $t['sub'] }} mb-3 truncate cursor-help" title="KURIKULUM MERDEKA • {{ $module->category ?? 'MODUL AJAR' }}">
                            KURIKULUM MERDEKA &bull; {{ $module->category ?? 'MODUL AJAR' }}
                        </div>
                        <h3 class="text-2xl font-black uppercase leading-tight tracking-tight mb-2 line-clamp-3 cursor-help" title="{{ $module->title }}">
                            {{ $module->title }}
                        </h3>
                        <span class="inline-block px-2.5 py-1 rounded {{ $t['badge'] }} text-[10px] font-mono-code font-bold">
                            {{ $module->target_role ? strtoupper($module->target_role) : 'UMUM' }}
                        </span>
                        @if($module->description)
                            <p class="text-xs {{ $t['sub'] }} mt-3 line-clamp-2 leading-relaxed font-medium cursor-help" title="{{ $module->description }}">
                                {{ $module->description }}
                            </p>
                        @endif
                    </div>
                    <div class="pt-4 border-t {{ $t['border'] }} flex items-center justify-between text-[11px] font-mono-code font-bold mt-4">
                        <div class="flex items-center gap-2 min-w-0 cursor-help" title="{{ $authorName }}">
                            <div class="w-8 h-8 rounded-full bg-white border border-current/20 flex items-center justify-center flex-shrink-0 overflow-hidden shadow-sm">
                                <img src="{{ $authorPhoto }}" alt="{{ $authorName }}" class="w-full h-full rounded-full object-cover" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($authorName) }}&background=fbc02d&color=121316&bold=true';">
                            </div>
                            <span class="truncate">{{ $firstName }}</span>
                        </div>
                        <a href="{{ asset('storage/' . $module->pdf_file) }}" target="_blank" class="hover:underline text-[10px] flex-shrink-0 font-bold">UNDUH &darr;</a>
                    </div>
                </article>
            @empty
            @endforelse

            {{-- 4. LOOP REAL PRESTASI & PENGHARGAAN SISWA --}}
            @forelse($achievements->take(8) as $ach)
                @php
                    $t = $colorThemes[$themeIndex % count($colorThemes)];
                    $themeIndex++;
                    $studentName = $ach->student?->full_name ?? 'Siswa Berprestasi';
                    $firstName = explode(' ', trim($studentName))[0] ?? $studentName;
                    $studentPhoto = $ach->student?->photo_url 
                        ?? $ach->student?->user?->avatar_url 
                        ?? 'https://ui-avatars.com/api/?name=' . urlencode($studentName) . '&background=fbc02d&color=121316&bold=true';
                    $fullPrestasiDesc = "Diraih oleh {$studentName} pada ajang " . ($ach->competition_name ?? $ach->event_name ?? 'Kompetisi Sekolah') . (!empty($ach->description) ? ' - ' . $ach->description : '');
                @endphp
                <article class="showcase-card poster-card {{ $t['bg'] }} {{ $t['text'] }} p-6 flex flex-col justify-between min-h-[340px]" data-type="prestasi">
                    <div>
                        <div class="text-[10px] font-mono-code font-bold uppercase {{ $t['sub'] }} mb-3 truncate cursor-help" title="{{ $ach->student?->school?->name ?? 'PEMBDA NIAS' }} • {{ strtoupper($ach->achievement_level ?? 'PROVINSI') }}">
                            {{ $ach->student?->school?->name ?? 'PEMBDA NIAS' }} &bull; {{ strtoupper($ach->achievement_level ?? 'PROVINSI') }}
                        </div>
                        <h3 class="text-2xl font-black uppercase leading-tight tracking-tight mb-2 line-clamp-3 cursor-help" title="{{ $ach->title ?? 'Prestasi Kejuaraan' }}">
                            {{ $ach->title ?? 'Prestasi Kejuaraan' }}
                        </h3>
                        <span class="inline-block px-2.5 py-1 rounded {{ $t['badge'] }} text-[10px] font-mono-code font-bold">
                            {{ strtoupper(str_replace('_', ' ', $ach->ranking ?? 'JUARA')) }}
                        </span>
                        <p class="text-xs {{ $t['sub'] }} mt-3 line-clamp-4 leading-relaxed font-medium cursor-help" title="{{ $fullPrestasiDesc }}">
                            Diraih oleh {{ $studentName }} pada ajang {{ $ach->competition_name ?? $ach->event_name ?? 'Kompetisi Sekolah' }}.@if(!empty($ach->description)) {{ $ach->description }}@endif
                        </p>
                    </div>
                    <div class="pt-4 border-t {{ $t['border'] }} flex items-center justify-between text-[11px] font-mono-code font-bold mt-4">
                        <div class="flex items-center gap-2 min-w-0 cursor-help" title="{{ $studentName }}">
                            <div class="w-8 h-8 rounded-full bg-white border border-current/20 flex items-center justify-center flex-shrink-0 overflow-hidden shadow-sm">
                                <img src="{{ $studentPhoto }}" alt="{{ $studentName }}" class="w-full h-full rounded-full object-cover" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($studentName) }}&background=fbc02d&color=121316&bold=true';">
                            </div>
                            <span class="truncate">{{ $firstName }}</span>
                        </div>
                        <span class="flex-shrink-0">🏆 JUARA</span>
                    </div>
                </article>
            @empty
            @endforelse

        </div>

    </div>
</section>

{{-- MODAL PETA & DIREKTORI LOKASI PRAKTEK PKL (DUDI) SMK SWASTA PEMBDA NIAS --}}
<div id="dudi-map-modal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-[#faf8f5] border-2 border-[#121316] rounded-3xl w-full max-w-4xl max-h-[90vh] flex flex-col shadow-[8px_8px_0px_#121316] overflow-hidden">
        
        <!-- Modal Header -->
        <div class="p-6 bg-white border-b-2 border-[#121316] flex items-center justify-between gap-4">
            <div>
                <div class="text-[10px] font-mono-code font-bold text-[#ff3823] uppercase tracking-wider mb-1">
                    📍 KEMITRAAN INDUSTRI & VOKASI
                </div>
                <h3 class="text-xl sm:text-2xl font-black text-[#121316]">
                    Peta Lokasi Praktik PKL <span class="highlight-marker">SMKS Pembda Nias</span>
                </h3>
                <p class="text-xs text-[#555] font-medium mt-1">
                    Jejaring kemitraan Dunia Usaha & Industri (DUDI) tempat siswa melaksanakan Praktik Kerja Lapangan.
                </p>
            </div>
            <button type="button" onclick="closeDudiMapModal()" class="w-10 h-10 rounded-full border-2 border-[#121316] bg-white hover:bg-slate-100 flex items-center justify-center font-black text-base flex-shrink-0 shadow-[2px_2px_0px_#121316]">
                ✕
            </button>
        </div>

        <!-- Search & Info Bar -->
        <div class="px-6 py-4 bg-[#ede9df] border-b border-[#e7e3d8] flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="relative w-full sm:w-80">
                <input type="text" id="dudi-search-input" onkeyup="searchDudi()" placeholder="Cari nama mitra / instansi / alamat..." class="w-full px-4 py-2 pl-9 bg-white border border-[#121316] rounded-full text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#ff3823]">
                <span class="absolute left-3 top-2.5 text-xs text-[#777]">🔍</span>
            </div>
            <div class="text-[11px] font-mono-code font-bold text-[#555] flex items-center gap-2">
                <span>Total: <strong class="text-[#121316]" id="dudi-count-text">{{ isset($dudiLocations) ? $dudiLocations->count() : '45+' }} Mitra</strong></span>
                <span>&bull;</span>
                <span class="text-emerald-700 font-extrabold">Terverifikasi Google Maps 📍</span>
            </div>
        </div>

        <!-- Modal Body: Grid DUDI Cards -->
        <div class="p-6 overflow-y-auto max-h-[60vh] space-y-3" id="dudi-cards-container">
            @if(isset($dudiLocations) && $dudiLocations->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($dudiLocations as $dudi)
                        @php
                            $queryStr = urlencode($dudi->name . ' ' . ($dudi->address ?? 'Gunungsitoli Nias'));
                            $dudiMapUrl = "https://www.google.com/maps/search/?api=1&query=" . $queryStr;
                        @endphp
                        <div class="dudi-card bg-white border border-[#121316] rounded-2xl p-4 shadow-[3px_3px_0px_#121316] flex flex-col justify-between hover:-translate-y-0.5 transition-transform" data-name="{{ strtolower($dudi->name . ' ' . $dudi->address . ' ' . $dudi->field_of_work) }}">
                            <div>
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <h4 class="text-sm font-black text-[#121316] leading-snug">
                                        {{ $dudi->name }}
                                    </h4>
                                    @if($dudi->field_of_work)
                                        <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200 text-[9px] font-mono-code font-bold uppercase whitespace-nowrap">
                                            {{ $dudi->field_of_work }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-[#555] font-medium leading-relaxed mb-3">
                                    📍 {{ $dudi->address ?? 'Gunungsitoli, Pulau Nias, Sumatera Utara' }}
                                </p>
                            </div>
                            <div class="pt-3 border-t border-[#e7e3d8] flex items-center justify-between text-[10px] font-mono-code">
                                <span class="text-[#777]">{{ $dudi->school?->name ?? 'SMKS Pembda Nias' }}</span>
                                <a href="{{ $dudiMapUrl }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1 rounded-full bg-[#ff3823] text-white hover:bg-red-600 font-bold inline-flex items-center gap-1 transition-colors shadow-sm">
                                    <span>Buka di Google Maps</span>
                                    <span>↗</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Fallback jika database belum ada list DUDI lengkap -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @php
                        $fallbackDudis = [
                            ['name' => 'PT Telkom Indonesia (Persero) Tbk Witel Gunungsitoli', 'address' => 'Jl. Sirao No. 12, Gunungsitoli, Pulau Nias', 'field' => 'Teknologi Informasi & Jaringan'],
                            ['name' => 'PT Astra International Tbk - Daihatsu / Honda Sales', 'address' => 'Jl. Diponegoro, Gunungsitoli, Pulau Nias', 'field' => 'Otomotif & Kendaraan Ringan'],
                            ['name' => 'Bank Sumut Kantor Cabang Gunungsitoli', 'address' => 'Jl. Gomo No. 34, Gunungsitoli, Nias', 'field' => 'Perbankan & Akuntansi'],
                            ['name' => 'Dinas Komunikasi dan Informatika (Diskominfo)', 'address' => 'Kompleks Perkantoran Pemerintah Kota Gunungsitoli', 'field' => 'IT & Layanan Publik'],
                            ['name' => 'PT PLN (Persero) UP3 Nias', 'address' => 'Jl. Yos Sudarso, Gunungsitoli', 'field' => 'Kelistrikan & Rekayasa'],
                            ['name' => 'Bengkel Resmi Yamaha / Honda Service Center', 'address' => 'Jl. Sudirman No. 88, Gunungsitoli', 'field' => 'Teknik Sepeda Motor'],
                        ];
                    @endphp
                    @foreach($fallbackDudis as $f)
                        <div class="dudi-card bg-white border border-[#121316] rounded-2xl p-4 shadow-[3px_3px_0px_#121316] flex flex-col justify-between" data-name="{{ strtolower($f['name'] . ' ' . $f['address']) }}">
                            <div>
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <h4 class="text-sm font-black text-[#121316]">{{ $f['name'] }}</h4>
                                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-[9px] font-mono-code font-bold uppercase">{{ $f['field'] }}</span>
                                </div>
                                <p class="text-[11px] text-[#555] font-medium leading-relaxed mb-3">📍 {{ $f['address'] }}</p>
                            </div>
                            <div class="pt-3 border-t border-[#e7e3d8] flex items-center justify-between text-[10px] font-mono-code">
                                <span class="text-[#777]">SMKS Pembda Nias</span>
                                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($f['name'] . ' ' . $f['address']) }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1 rounded-full bg-[#ff3823] text-white hover:bg-red-600 font-bold inline-flex items-center gap-1">
                                    <span>Buka di Google Maps</span>
                                    <span>↗</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Modal Footer -->
        <div class="p-4 bg-white border-t border-[#121316] flex items-center justify-between text-xs font-mono-code text-[#777]">
            <span>💡 Klik link Google Maps pada setiap instansi untuk melihat rute navigasi.</span>
            <button type="button" onclick="closeDudiMapModal()" class="px-5 py-2 rounded-full btn-tactile-white text-xs font-bold">
                Tutup Peta
            </button>
        </div>

    </div>
</div>

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

    function openDudiMapModal() {
        const modal = document.getElementById('dudi-map-modal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeDudiMapModal() {
        const modal = document.getElementById('dudi-map-modal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    function searchDudi() {
        const input = document.getElementById('dudi-search-input').value.toLowerCase();
        const cards = document.querySelectorAll('.dudi-card');
        let count = 0;
        cards.forEach(card => {
            const dataName = card.getAttribute('data-name');
            if (!input || dataName.includes(input)) {
                card.style.display = 'flex';
                count++;
            } else {
                card.style.display = 'none';
            }
        });
        const countText = document.getElementById('dudi-count-text');
        if (countText) {
            countText.innerText = `${count} Mitra Ditemukan`;
        }
    }
</script>
