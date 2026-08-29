{{-- HALL OF FAME CIVITAS TERPILIH — Collegiate Varsity Poster Style (Ole Miss Athletic Tribute Aesthetic) --}}
@php
    $academicYearName = isset($activeAcademicYear) && $activeAcademicYear ? $activeAcademicYear->name : '2026/2027';
    $yearDigits = explode('/', $academicYearName)[0] ?? '2026';
@endphp

<section id="fame" class="py-16 px-4 sm:px-8 border-t border-[#e7e3d8] bg-[#faf8f5]">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
            <div>
                <div class="text-[11px] font-mono-code font-bold text-[#ff3823] uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <span>✱</span>
                    <span>HONOR ROLL & REKOGNISI CIVITAS</span>
                </div>
                <h2 class="text-3xl sm:text-4xl font-black text-[#121316] tracking-tight">
                    Hall of Fame <span class="highlight-marker">Pembda Elite.</span>
                </h2>
                <p class="text-xs sm:text-sm text-[#555] font-medium mt-1 max-w-xl">
                    Apresiasi tertinggi bagi insan berprestasi, teladan kedisiplinan, dan kontributor terdepan di lingkungan Yayasan Perguruan Pembda.
                </p>
            </div>

            <!-- Tab Switcher (Siswa vs Guru) -->
            <div class="flex items-center gap-2 bg-[#ede9df] p-1.5 rounded-full border border-[#121316]">
                <button type="button" onclick="switchFameTab('siswa')" id="btn-fame-siswa" class="px-5 py-2 rounded-full font-mono-code text-xs font-black transition-all bg-[#121316] text-[#fde047] shadow-sm">
                    🎓 SISWA TELADAN ({{ $topStudentsElite->count() }})
                </button>
                <button type="button" onclick="switchFameTab('guru')" id="btn-fame-guru" class="px-5 py-2 rounded-full font-mono-code text-xs font-black transition-all text-[#555] hover:text-[#121316]">
                    📚 GURU INSPIRATIF ({{ $topTeachersElite->count() }})
                </button>
            </div>
        </div>

        {{-- ===== THE COLLEGIATE BANNER POSTER WRAPPER ===== --}}
        <div class="bg-white border-2 border-[#121316] rounded-3xl p-6 sm:p-10 shadow-[8px_8px_0px_#121316] relative overflow-hidden">
            
            <!-- 1. TOP CREST BADGE (Like 'Ole Miss' script badge) -->
            <div class="flex justify-center mb-6 relative z-10">
                <div class="inline-flex items-center gap-2 px-6 py-2 rounded-full bg-[#ff3823] text-white border-2 border-[#121316] shadow-[3px_3px_0px_#121316]">
                    <span class="text-base">★</span>
                    <span class="font-black italic tracking-wider text-sm font-serif">Perguruan Pembda</span>
                    <span class="text-base">★</span>
                </div>
            </div>

            <!-- 2. HORIZONTAL RED RIBBON STRIPE (Behind the portraits) -->
            <div class="absolute left-0 right-0 top-[42%] -translate-y-1/2 h-24 sm:h-28 bg-[#ff3823] border-y-2 border-[#121316] z-0 hidden sm:block">
                <!-- Diagonal Pattern overlay -->
                <div class="w-full h-full opacity-15" style="background-image: repeating-linear-gradient(45deg, #000 0, #000 2px, transparent 0, transparent 8px);"></div>
            </div>

            {{-- 3A. STRIP OF HONOR: TOP SISWA (6 HONOREES) --}}
            <div id="fame-content-siswa" class="relative z-10">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 sm:gap-5 my-4">
                    @forelse($topStudentsElite as $index => $rep)
                        @php
                            $rank = $index + 1;
                            $student = $rep->user?->student;
                            $school = $student?->school ?? $rep->user?->school;
                            $studentName = $rep->user?->name ?? 'Siswa Teladan';
                            $studentPhoto = $student?->photo_url 
                                ?? $rep->user?->avatar_url 
                                ?? 'https://ui-avatars.com/api/?name=' . urlencode($studentName) . '&background=121316&color=ffffff&bold=true';
                            $rankBadges = [
                                1 => ['badge' => '🥇 #1', 'bg' => 'bg-[#fbc02d] text-[#121316]'],
                                2 => ['badge' => '🥈 #2', 'bg' => 'bg-slate-200 text-[#121316]'],
                                3 => ['badge' => '🥉 #3', 'bg' => 'bg-amber-600 text-white'],
                            ];
                            $badgeInfo = $rankBadges[$rank] ?? ['badge' => "#{$rank}", 'bg' => 'bg-[#121316] text-white'];
                        @endphp
                        <div class="flex flex-col items-center group">
                            <!-- Portrait Card Frame -->
                            <div class="w-full aspect-[3/4] bg-[#faf8f5] border-2 border-[#121316] rounded-2xl overflow-hidden shadow-[4px_4px_0px_#121316] relative transition-transform duration-300 group-hover:-translate-y-1.5 group-hover:shadow-[6px_6px_0px_#ff3823]">
                                <!-- Rank Pill Overlay -->
                                <div class="absolute top-2 left-2 {{ $badgeInfo['bg'] }} px-2 py-0.5 rounded-md border border-[#121316] font-mono-code font-black text-[9px] z-10 shadow-xs">
                                    {{ $badgeInfo['badge'] }}
                                </div>

                                <!-- Photo with Grayscale to Color Effect -->
                                <img src="{{ $studentPhoto }}" 
                                     alt="{{ $studentName }}" 
                                     class="w-full h-full object-cover grayscale contrast-110 group-hover:grayscale-0 group-hover:scale-105 transition-all duration-300" 
                                     loading="lazy" 
                                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($studentName) }}&background=121316&color=ffffff&bold=true';">
                                
                                <!-- Star Points overlay at bottom of photo -->
                                <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-2 pt-6 text-center">
                                    <span class="text-[10px] font-mono-code font-extrabold text-[#fde047]">
                                        ⭐ {{ number_format($rep->total_points) }} Pts
                                    </span>
                                </div>
                            </div>

                            <!-- Text Beneath Card Frame -->
                            <div class="text-center mt-3 w-full px-1">
                                <h4 class="text-xs sm:text-sm font-black uppercase text-[#121316] tracking-tight truncate leading-snug group-hover:text-[#ff3823] transition-colors" title="{{ $studentName }}">
                                    {{ $studentName }}
                                </h4>
                                <div class="text-[10px] font-mono-code font-bold uppercase text-[#ff3823] truncate mt-0.5">
                                    {{ $school?->name ?? 'SMK/SMA PEMBDA' }}
                                </div>
                                <div class="text-[9px] font-mono-code text-[#777] truncate font-medium">
                                    {{ $student?->classroom?->name ?? 'Pelajar Teladan' }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-10 text-xs font-bold text-[#777]">
                            Data peringkat siswa sedang diproses sistem.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- 3B. STRIP OF HONOR: TOP GURU (6 HONOREES) --}}
            <div id="fame-content-guru" class="relative z-10 hidden">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 sm:gap-5 my-4">
                    @forelse($topTeachersElite as $index => $rep)
                        @php
                            $rank = $index + 1;
                            $teacherName = $rep->user?->name ?? 'Guru Inspiratif';
                            $teacherPhoto = $rep->user?->teacher?->photo_url 
                                ?? $rep->user?->avatar_url 
                                ?? 'https://ui-avatars.com/api/?name=' . urlencode($teacherName) . '&background=ff3823&color=ffffff&bold=true';
                            $school = $rep->user?->teacher?->school ?? $rep->user?->school;
                            $rankBadges = [
                                1 => ['badge' => '🥇 #1', 'bg' => 'bg-[#fbc02d] text-[#121316]'],
                                2 => ['badge' => '🥈 #2', 'bg' => 'bg-slate-200 text-[#121316]'],
                                3 => ['badge' => '🥉 #3', 'bg' => 'bg-amber-600 text-white'],
                            ];
                            $badgeInfo = $rankBadges[$rank] ?? ['badge' => "#{$rank}", 'bg' => 'bg-[#121316] text-white'];
                        @endphp
                        <div class="flex flex-col items-center group">
                            <!-- Portrait Card Frame -->
                            <div class="w-full aspect-[3/4] bg-[#faf8f5] border-2 border-[#121316] rounded-2xl overflow-hidden shadow-[4px_4px_0px_#121316] relative transition-transform duration-300 group-hover:-translate-y-1.5 group-hover:shadow-[6px_6px_0px_#ff3823]">
                                <!-- Rank Pill Overlay -->
                                <div class="absolute top-2 left-2 {{ $badgeInfo['bg'] }} px-2 py-0.5 rounded-md border border-[#121316] font-mono-code font-black text-[9px] z-10 shadow-xs">
                                    {{ $badgeInfo['badge'] }}
                                </div>

                                <!-- Photo with Grayscale to Color Effect -->
                                <img src="{{ $teacherPhoto }}" 
                                     alt="{{ $teacherName }}" 
                                     class="w-full h-full object-cover grayscale contrast-110 group-hover:grayscale-0 group-hover:scale-105 transition-all duration-300" 
                                     loading="lazy" 
                                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($teacherName) }}&background=ff3823&color=ffffff&bold=true';">
                                
                                <!-- Star Points overlay at bottom of photo -->
                                <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-2 pt-6 text-center">
                                    <span class="text-[10px] font-mono-code font-extrabold text-[#fde047]">
                                        ⭐ {{ number_format($rep->total_points) }} Pts
                                    </span>
                                </div>
                            </div>

                            <!-- Text Beneath Card Frame -->
                            <div class="text-center mt-3 w-full px-1">
                                <h4 class="text-xs sm:text-sm font-black uppercase text-[#121316] tracking-tight truncate leading-snug group-hover:text-[#ff3823] transition-colors" title="{{ $teacherName }}">
                                    {{ $teacherName }}
                                </h4>
                                <div class="text-[10px] font-mono-code font-bold uppercase text-[#ff3823] truncate mt-0.5">
                                    {{ $school?->name ?? 'GURU TELADAN PEMBDA' }}
                                </div>
                                <div class="text-[9px] font-mono-code text-[#777] truncate font-medium">
                                    Pendidik Berdedikasi
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-10 text-xs font-bold text-[#777]">
                            Data peringkat guru sedang diproses sistem.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 4. BOTTOM COLLEGIATE TYPOGRAPHY & LOGO BANNER -->
            <div class="pt-8 mt-6 border-t-2 border-[#121316] flex flex-col sm:flex-row items-center justify-between gap-6 relative z-10">
                
                <!-- Left Emblem: [P] PEMBDA CIVITAS HALL OF FAME -->
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-[#121316] text-[#fde047] border-2 border-[#121316] rounded-xl flex items-center justify-center font-black text-2xl shadow-[3px_3px_0px_#ff3823] flex-shrink-0">
                        P
                    </div>
                    <div>
                        <div class="text-lg sm:text-xl font-black tracking-tight text-[#121316] leading-none uppercase">
                            PEMBDA <span class="text-[#ff3823]">CIVITAS</span>
                        </div>
                        <div class="text-[11px] font-mono-code font-extrabold text-[#777] tracking-widest uppercase mt-0.5">
                            ★ HALL OF FAME ★
                        </div>
                    </div>
                </div>

                <!-- Right Big Varsity Typography: CLASS OF 2026 -->
                <div class="text-center sm:text-right">
                    <div class="text-[11px] font-mono-code font-black tracking-widest text-[#ff3823] uppercase">
                        CLASS OF
                    </div>
                    <div class="text-4xl sm:text-6xl font-black text-[#121316] tracking-tighter leading-none font-sans">
                        {{ $yearDigits }}
                    </div>
                </div>

            </div>

        </div>

    </div>
</section>

<script>
    function switchFameTab(tab) {
        const btnSiswa = document.getElementById('btn-fame-siswa');
        const btnGuru = document.getElementById('btn-fame-guru');
        const contentSiswa = document.getElementById('fame-content-siswa');
        const contentGuru = document.getElementById('fame-content-guru');

        if (tab === 'siswa') {
            btnSiswa.className = 'px-5 py-2 rounded-full font-mono-code text-xs font-black transition-all bg-[#121316] text-[#fde047] shadow-sm';
            btnGuru.className = 'px-5 py-2 rounded-full font-mono-code text-xs font-black transition-all text-[#555] hover:text-[#121316]';
            contentSiswa.classList.remove('hidden');
            contentGuru.classList.add('hidden');
        } else {
            btnGuru.className = 'px-5 py-2 rounded-full font-mono-code text-xs font-black transition-all bg-[#121316] text-[#fde047] shadow-sm';
            btnSiswa.className = 'px-5 py-2 rounded-full font-mono-code text-xs font-black transition-all text-[#555] hover:text-[#121316]';
            contentGuru.classList.remove('hidden');
            contentSiswa.classList.add('hidden');
        }
    }
</script>
