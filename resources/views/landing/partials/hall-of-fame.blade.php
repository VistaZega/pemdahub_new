{{-- HALL OF FAME CIVITAS TERPILIH — Compact Tactile Cards with Large Avatars --}}
<section id="fame" class="py-14 px-4 sm:px-8 border-t border-[#e7e3d8]">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header Ringkas -->
        <div class="text-center max-w-2xl mx-auto mb-10">
            <div class="text-[10px] font-mono-code font-bold text-[#ff3823] uppercase tracking-wider mb-1.5">
                ✱ REKOGNISI & PRESTASI CIVITAS
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-[#121316] tracking-tight">
                Hall of Fame <span class="highlight-marker">Civitas Terpilih.</span>
            </h2>
            <p class="text-xs text-[#555] font-medium mt-1">
                Peringkat reputasi keaktifan belajar, ketuntasan modul LMS, kedisiplinan RFID, dan dedikasi civitas.
            </p>
        </div>

        {{-- ===== SECTION: TOP PELAJAR KONTRIBUTIF ===== --}}
        <div class="mb-10">
            <div class="flex items-center gap-3 mb-6">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-[#2563eb] text-white text-[10px] font-mono-code font-extrabold uppercase shadow-[2px_2px_0px_#121316]">
                    🎓 PELAJAR PALING KONTRIBUTIF
                </span>
                <div class="h-px flex-1 bg-[#e7e3d8]"></div>
            </div>

            @php
                $studentCardColors = [
                    1 => ['ring' => 'ring-[#fbc02d]', 'badge' => '🥇', 'rank_bg' => 'bg-[#fbc02d]', 'rank_text' => 'text-[#121316]', 'pill' => 'bg-[#faf3e0] text-[#121316] border-[#121316]'],
                    2 => ['ring' => 'ring-slate-300', 'badge' => '🥈', 'rank_bg' => 'bg-slate-300', 'rank_text' => 'text-[#121316]', 'pill' => 'bg-slate-50 text-[#121316] border-slate-300'],
                    3 => ['ring' => 'ring-amber-400', 'badge' => '🥉', 'rank_bg' => 'bg-amber-300', 'rank_text' => 'text-[#92400e]', 'pill' => 'bg-amber-50 text-[#92400e] border-amber-300'],
                ];
                $studentRank = 1;
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse($topStudentsElite->take(3) as $rep)
                    @php
                        $c = $studentCardColors[$studentRank] ?? $studentCardColors[3];
                        $student = $rep->user?->student;
                        $school = $student?->school ?? $rep->user?->school;
                        $studentName = $rep->user?->name ?? 'Siswa Teladan';
                        $studentPhoto = $student?->photo_url 
                            ?? $rep->user?->avatar_url 
                            ?? 'https://ui-avatars.com/api/?name=' . urlencode($studentName) . '&background=2563eb&color=ffffff&bold=true';
                        $badges = $rep->user?->badges ?? collect();
                    @endphp
                    <div class="bg-white border-2 border-[#121316] rounded-2xl p-5 shadow-[4px_4px_0px_#121316] flex flex-col items-center text-center relative hover:-translate-y-1 transition-transform group">
                        
                        <!-- Rank Pin Overlay -->
                        <div class="absolute top-3 left-3 {{ $c['rank_bg'] }} {{ $c['rank_text'] }} px-2 py-0.5 rounded-md border border-[#121316] font-mono-code font-black text-[10px] shadow-[1px_1px_0px_#121316]">
                            #{{ $studentRank }}
                        </div>
                        <div class="absolute top-3 right-3 text-lg select-none">
                            {{ $c['badge'] }}
                        </div>

                        <!-- Large Photo Avatar (Ukuran Diperbesar 24x24 / 96px) -->
                        <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl border-2 border-[#121316] ring-4 {{ $c['ring'] }} overflow-hidden shadow-md bg-[#faf8f5] mb-3 mt-1 flex-shrink-0">
                            <img src="{{ $studentPhoto }}" alt="{{ $studentName }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($studentName) }}&background=2563eb&color=ffffff&bold=true';">
                        </div>

                        <!-- Info Siswa -->
                        <div class="w-full">
                            <span class="inline-block px-2 py-0.5 rounded bg-blue-50 text-[#2563eb] text-[9px] font-mono-code font-bold mb-1 border border-blue-200">
                                PELAJAR KONTRIBUTIF
                            </span>
                            <h4 class="text-base font-black text-[#121316] leading-snug mb-0.5 truncate" title="{{ $studentName }}">
                                {{ $studentName }}
                            </h4>
                            <p class="text-xs font-bold text-[#2563eb] truncate">
                                {{ $school?->name ?? 'SMK/SMA PEMBDA' }}
                            </p>
                            <p class="text-[11px] text-[#777] font-medium truncate mb-2">
                                {{ $student?->classroom?->name ?? 'Siswa Aktif' }}
                            </p>

                            <!-- Badges -->
                            @if($badges->count() > 0)
                                <div class="flex items-center justify-center gap-1 mb-3 flex-wrap">
                                    @foreach($badges->take(3) as $badge)
                                        <span class="px-1.5 py-0.2 rounded bg-[#f4f1ea] text-[9px] font-mono-code font-bold text-[#555]" title="{{ $badge->name }}">
                                            {{ $badge->icon ?? '🏅' }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Points Strip -->
                            <div class="pt-2.5 border-t border-[#e7e3d8]">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full border font-mono-code font-extrabold text-xs {{ $c['pill'] }} shadow-[1px_1px_0px_#121316]">
                                    ⭐ {{ number_format($rep->total_points) }} <span class="text-[10px] font-bold opacity-80">Poin</span>
                                </span>
                            </div>
                        </div>

                    </div>
                    @php $studentRank++; @endphp
                @empty
                    <div class="col-span-3 text-center py-8 bg-white rounded-2xl border-2 border-dashed border-[#121316]">
                        <p class="text-xs font-bold text-[#777]">Data reputasi pelajar sedang dihitung.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ===== SECTION: TOP GURU BERDEDIKASI ===== --}}
        <div>
            <div class="flex items-center gap-3 mb-6">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-[#ff3823] text-white text-[10px] font-mono-code font-extrabold uppercase shadow-[2px_2px_0px_#121316]">
                    📚 GURU BERDEDIKASI TINGGI
                </span>
                <div class="h-px flex-1 bg-[#e7e3d8]"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @php $teacherRank = 1; @endphp
                @forelse($topTeachersElite->take(3) as $topTeacher)
                    @php
                        $teacherName = $topTeacher->user?->name ?? 'Guru Inspiratif';
                        $teacherPhoto = $topTeacher->user?->teacher?->photo_url 
                            ?? $topTeacher->user?->avatar_url 
                            ?? 'https://ui-avatars.com/api/?name=' . urlencode($teacherName) . '&background=ff3823&color=ffffff&bold=true';
                        $badges = $topTeacher->user?->badges ?? collect();
                    @endphp
                    <div class="bg-white border-2 border-[#121316] rounded-2xl p-5 shadow-[4px_4px_0px_#121316] flex flex-col items-center text-center relative hover:-translate-y-1 transition-transform group">
                        
                        <!-- Rank Pin Overlay -->
                        <div class="absolute top-3 left-3 bg-[#ff3823] text-white px-2 py-0.5 rounded-md border border-[#121316] font-mono-code font-black text-[10px] shadow-[1px_1px_0px_#121316]">
                            #{{ $teacherRank }}
                        </div>
                        <div class="absolute top-3 right-3 text-lg select-none">
                            📚
                        </div>

                        <!-- Large Photo Avatar (Ukuran Diperbesar 24x24 / 96px) -->
                        <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl border-2 border-[#121316] ring-4 ring-[#ff3823]/30 overflow-hidden shadow-md bg-[#faf8f5] mb-3 mt-1 flex-shrink-0">
                            <img src="{{ $teacherPhoto }}" alt="{{ $teacherName }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($teacherName) }}&background=ff3823&color=ffffff&bold=true';">
                        </div>

                        <!-- Info Guru -->
                        <div class="w-full">
                            <span class="inline-block px-2 py-0.5 rounded bg-red-50 text-[#ff3823] text-[9px] font-mono-code font-bold mb-1 border border-red-200">
                                GURU TELADAN
                            </span>
                            <h4 class="text-base font-black text-[#121316] leading-snug mb-0.5 truncate" title="{{ $teacherName }}">
                                {{ $teacherName }}
                            </h4>
                            <p class="text-xs font-bold text-[#ff3823] truncate mb-2">
                                {{ $topTeacher->user?->teacher?->school?->name ?? 'Guru Teladan PEMBDA' }}
                            </p>

                            <!-- Badges -->
                            @if($badges->count() > 0)
                                <div class="flex items-center justify-center gap-1 mb-3 flex-wrap">
                                    @foreach($badges->take(3) as $badge)
                                        <span class="px-1.5 py-0.2 rounded bg-red-50 text-[9px] font-mono-code font-bold text-[#ff3823]" title="{{ $badge->name }}">
                                            {{ $badge->icon ?? '🏅' }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Points Strip -->
                            <div class="pt-2.5 border-t border-[#e7e3d8]">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full border border-[#ff3823] font-mono-code font-extrabold text-xs text-[#ff3823] bg-red-50 shadow-[1px_1px_0px_#ff3823]">
                                    ⭐ {{ number_format($topTeacher->total_points) }} <span class="text-[10px] font-bold opacity-80">Poin</span>
                                </span>
                            </div>
                        </div>

                    </div>
                    @php $teacherRank++; @endphp
                @empty
                    <div class="col-span-3 text-center py-8 bg-white rounded-2xl border-2 border-dashed border-[#121316]">
                        <p class="text-xs font-bold text-[#777]">Data reputasi guru sedang dihitung.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</section>
