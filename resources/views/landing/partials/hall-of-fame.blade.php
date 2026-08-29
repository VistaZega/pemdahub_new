{{-- HALL OF FAME CIVITAS TERPILIH — Premium Living Cards with Real Photos --}}
<section id="fame" class="py-20 px-4 sm:px-8 border-t border-[#e7e3d8]">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="text-center max-w-2xl mx-auto mb-14">
            <div class="text-[11px] font-mono-code font-bold text-[#ff3823] uppercase tracking-wider mb-2">
                ✱ REKOGNISI & PRESTASI CIVITAS
            </div>
            <h2 class="text-3xl sm:text-4xl font-black text-[#121316] tracking-tight">
                Hall of Fame <span class="highlight-marker">Civitas Terpilih.</span>
            </h2>
            <p class="text-xs sm:text-sm text-[#555] font-medium mt-2">
                Peringkat reputasi keaktifan belajar, ketuntasan modul LMS, kedisiplinan RFID, dan dedikasi civitas.
            </p>
        </div>

        {{-- ===== SECTION: TOP PELAJAR KONTRIBUTIF ===== --}}
        <div class="mb-14">
            <div class="flex items-center gap-3 mb-8">
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#2563eb] text-white text-[11px] font-mono-code font-extrabold uppercase shadow-[3px_3px_0px_#121316]">
                    🎓 PELAJAR PALING KONTRIBUTIF
                </span>
                <div class="h-px flex-1 bg-[#e7e3d8]"></div>
            </div>

            @php
                $studentCardColors = [
                    1 => ['gradient' => 'from-[#fbc02d] to-[#f9a825]', 'ring' => 'ring-[#fbc02d]', 'badge' => '🥇', 'rank_bg' => 'bg-[#fbc02d]', 'rank_text' => 'text-[#121316]', 'shadow' => 'shadow-[6px_6px_0px_#b7950b]'],
                    2 => ['gradient' => 'from-slate-300 to-slate-200', 'ring' => 'ring-slate-300', 'badge' => '🥈', 'rank_bg' => 'bg-slate-300', 'rank_text' => 'text-[#121316]', 'shadow' => 'shadow-[6px_6px_0px_#94a3b8]'],
                    3 => ['gradient' => 'from-amber-300 to-amber-200', 'ring' => 'ring-amber-300', 'badge' => '🥉', 'rank_bg' => 'bg-amber-300', 'rank_text' => 'text-[#92400e]', 'shadow' => 'shadow-[6px_6px_0px_#b45309]'],
                ];
                $studentRank = 1;
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($topStudentsElite->take(3) as $rep)
                    @php
                        $c = $studentCardColors[$studentRank] ?? $studentCardColors[3];
                        $student = $rep->user?->student;
                        $school = $student?->school ?? $rep->user?->school;
                        $avatarUrl = $rep->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($rep->user?->name ?? 'S') . '&background=2563eb&color=ffffff&bold=true';
                        $badges = $rep->user?->badges ?? collect();
                    @endphp
                    <div class="group relative bg-white border-2 border-[#121316] rounded-3xl overflow-hidden {{ $c['shadow'] }} hover:-translate-y-2 hover:shadow-[8px_8px_0px_#121316] transition-all duration-300">
                        
                        {{-- Gradient Header Band --}}
                        <div class="bg-gradient-to-r {{ $c['gradient'] }} h-20 relative">
                            {{-- Rank Badge floating --}}
                            <div class="absolute top-3 left-4 {{ $c['rank_bg'] }} {{ $c['rank_text'] }} w-8 h-8 rounded-lg border-2 border-[#121316] flex items-center justify-center font-black text-sm shadow-[2px_2px_0px_#121316]">
                                #{{ $studentRank }}
                            </div>
                            <div class="absolute top-3 right-4 text-2xl">{{ $c['badge'] }}</div>
                            {{-- Decorative pattern --}}
                            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle, #121316 1px, transparent 1px); background-size: 12px 12px;"></div>
                        </div>

                        @php
                            $studentName = $rep->user?->name ?? 'Siswa Teladan';
                            $studentPhoto = $student?->photo_url 
                                ?? $rep->user?->avatar_url 
                                ?? 'https://ui-avatars.com/api/?name=' . urlencode($studentName) . '&background=2563eb&color=ffffff&bold=true';
                        @endphp
                        {{-- Avatar overlapping header --}}
                        <div class="flex justify-center -mt-10 relative z-10">
                            <div class="w-20 h-20 rounded-2xl border-4 border-white ring-2 {{ $c['ring'] }} overflow-hidden shadow-lg bg-white">
                                <img src="{{ $studentPhoto }}" alt="{{ $studentName }}" class="w-full h-full object-cover" loading="lazy" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($studentName) }}&background=2563eb&color=ffffff&bold=true';">
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="px-6 pt-4 pb-6 text-center">
                            <div class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#2563eb] text-white text-[9px] font-mono-code font-bold mb-2">
                                🎓 PELAJAR KONTRIBUTIF #{{ $studentRank }}
                            </div>
                            <h4 class="text-lg font-black text-[#121316] leading-tight mb-1 truncate">{{ $rep->user?->name ?? 'Siswa Teladan' }}</h4>
                            <p class="text-xs font-bold text-[#2563eb] truncate mb-1">
                                {{ $school?->name ?? 'SMK/SMA PEMBDA' }}
                            </p>
                            <p class="text-[11px] text-[#777] font-medium truncate">
                                {{ $student?->classroom?->name ?? 'Siswa Aktif' }}
                            </p>
                            
                            {{-- Badges ribbon --}}
                            @if($badges->count() > 0)
                                <div class="flex items-center justify-center gap-1 mt-3 flex-wrap">
                                    @foreach($badges->take(3) as $badge)
                                        <span class="px-1.5 py-0.5 rounded bg-[#f4f1ea] text-[9px] font-mono-code font-bold text-[#555]" title="{{ $badge->name }}">
                                            {{ $badge->icon ?? '🏅' }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Points --}}
                            <div class="mt-4 pt-4 border-t border-[#e7e3d8]">
                                <div class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full border-2 border-[#121316] font-mono-code font-extrabold text-xs text-[#121316] bg-[#faf8f5] shadow-[2px_2px_0px_#121316]">
                                    ⭐ {{ number_format($rep->total_points) }} <span class="font-bold text-[10px] text-[#777]">Poin</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @php $studentRank++; @endphp
                @empty
                    <div class="col-span-3 text-center py-12 bg-white rounded-2xl border-2 border-dashed border-[#121316]">
                        <p class="text-sm font-bold text-[#777]">Data reputasi pelajar sedang dihitung.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ===== SECTION: TOP GURU BERDEDIKASI ===== --}}
        <div>
            <div class="flex items-center gap-3 mb-8">
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#ff3823] text-white text-[11px] font-mono-code font-extrabold uppercase shadow-[3px_3px_0px_#121316]">
                    📚 GURU BERDEDIKASI TINGGI
                </span>
                <div class="h-px flex-1 bg-[#e7e3d8]"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @php $teacherRank = 1; @endphp
                @forelse($topTeachersElite->take(3) as $topTeacher)
                    @php
                        $avatarUrl = $topTeacher->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($topTeacher->user?->name ?? 'G') . '&background=ff3823&color=ffffff&bold=true';
                        $badges = $topTeacher->user?->badges ?? collect();
                    @endphp
                    <div class="group relative bg-white border-2 border-[#121316] rounded-3xl overflow-hidden shadow-[6px_6px_0px_#991b1b] hover:-translate-y-2 hover:shadow-[8px_8px_0px_#121316] transition-all duration-300">
                        
                        {{-- Gradient Header Band (Red theme) --}}
                        <div class="bg-gradient-to-r from-[#ff3823] to-[#dc2626] h-20 relative">
                            <div class="absolute top-3 left-4 bg-white text-[#ff3823] w-8 h-8 rounded-lg border-2 border-[#121316] flex items-center justify-center font-black text-sm shadow-[2px_2px_0px_#121316]">
                                #{{ $teacherRank }}
                            </div>
                            <div class="absolute top-3 right-4 text-2xl">📚</div>
                            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle, #fff 1px, transparent 1px); background-size: 12px 12px;"></div>
                        </div>

                        @php
                            $teacherName = $topTeacher->user?->name ?? 'Guru Inspiratif';
                            $teacherPhoto = $topTeacher->user?->teacher?->photo_url 
                                ?? $topTeacher->user?->avatar_url 
                                ?? 'https://ui-avatars.com/api/?name=' . urlencode($teacherName) . '&background=ff3823&color=ffffff&bold=true';
                        @endphp
                        {{-- Avatar --}}
                        <div class="flex justify-center -mt-10 relative z-10">
                            <div class="w-20 h-20 rounded-2xl border-4 border-white ring-2 ring-[#ff3823] overflow-hidden shadow-lg bg-white">
                                <img src="{{ $teacherPhoto }}" alt="{{ $teacherName }}" class="w-full h-full object-cover" loading="lazy" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($teacherName) }}&background=ff3823&color=ffffff&bold=true';">
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="px-6 pt-4 pb-6 text-center">
                            <div class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#ff3823] text-white text-[9px] font-mono-code font-bold mb-2">
                                📚 GURU BERDEDIKASI #{{ $teacherRank }}
                            </div>
                            <h4 class="text-lg font-black text-[#121316] leading-tight mb-1 truncate">{{ $topTeacher->user?->name ?? 'Guru Inspiratif' }}</h4>
                            <p class="text-xs font-bold text-[#ff3823] truncate">
                                {{ $topTeacher->user?->teacher?->school?->name ?? 'Guru Teladan PEMBDA' }}
                            </p>
                            
                            {{-- Badges ribbon --}}
                            @if($badges->count() > 0)
                                <div class="flex items-center justify-center gap-1 mt-3 flex-wrap">
                                    @foreach($badges->take(3) as $badge)
                                        <span class="px-1.5 py-0.5 rounded bg-red-50 text-[9px] font-mono-code font-bold text-[#ff3823]" title="{{ $badge->name }}">
                                            {{ $badge->icon ?? '🏅' }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Points --}}
                            <div class="mt-4 pt-4 border-t border-[#e7e3d8]">
                                <div class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full border-2 border-[#ff3823] font-mono-code font-extrabold text-xs text-[#ff3823] bg-red-50 shadow-[2px_2px_0px_#991b1b]">
                                    ⭐ {{ number_format($topTeacher->total_points) }} <span class="font-bold text-[10px] text-[#b91c1c]">Poin</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @php $teacherRank++; @endphp
                @empty
                    <div class="col-span-3 text-center py-12 bg-white rounded-2xl border-2 border-dashed border-[#121316]">
                        <p class="text-sm font-bold text-[#777]">Data reputasi guru sedang dihitung.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</section>
