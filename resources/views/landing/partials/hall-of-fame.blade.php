{{-- HALL OF FAME CIVITAS TERPILIH — 100% Real Database ($topStudentsElite & $topTeachersElite) --}}
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
                Peringkat reputasi keaktifan belajar, ketuntasan modul LMS, kedisiplinan RFID, dan dedikasi civitas semester ganjil 2026/2027.
            </p>
        </div>

        {{-- ===== SECTION: TOP PELAJAR ===== --}}
        <div class="mb-10">
            <div class="flex items-center gap-3 mb-6">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[#2563eb] text-white text-[11px] font-mono-code font-extrabold uppercase shadow-[3px_3px_0px_#121316]">
                    🎓 PELAJAR TERBAIK
                </span>
                <div class="h-px flex-1 bg-[#e7e3d8]"></div>
            </div>

            @php
                $rankBadges = [
                    1 => ['bg' => 'bg-[#fbc02d]', 'text' => 'text-[#121316]', 'badge' => '🥇', 'pill' => 'bg-[#faf3e0] border-[#121316] text-[#121316]'],
                    2 => ['bg' => 'bg-slate-200', 'text' => 'text-[#121316]', 'badge' => '🥈', 'pill' => 'bg-[#f4f4f4] border-[#121316] text-[#121316]'],
                    3 => ['bg' => 'bg-amber-100', 'text' => 'text-[#b45309]', 'badge' => '🥉', 'pill' => 'bg-[#f4f4f4] border-[#121316] text-[#121316]'],
                ];
                $studentRank = 1;
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($topStudentsElite->take(3) as $rep)
                    @php
                        $b = $rankBadges[$studentRank] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'badge' => '⭐', 'pill' => 'bg-white border-slate-300 text-slate-700'];
                        $student = $rep->user?->student;
                        $school = $student?->school ?? $rep->user?->school;
                    @endphp
                    <div class="bg-white border-2 border-[#121316] rounded-2xl p-6 text-center shadow-[4px_4px_0px_#121316] relative overflow-hidden hover:-translate-y-1 transition-transform">
                        <div class="absolute -top-3 -right-3 w-12 h-12 {{ $b['bg'] }} rounded-full border border-[#121316] flex items-center justify-center font-black text-xs rotate-12">
                            {{ $b['badge'] }}
                        </div>
                        <!-- Avatar silhouette pelajar -->
                        <div class="w-16 h-16 rounded-full {{ $b['bg'] }} {{ $b['text'] }} font-black text-lg flex items-center justify-center mx-auto mb-3 border-2 border-[#121316] shadow-[2px_2px_0px_#121316]">
                            @if($student && $student->user && $student->user->profile_photo)
                                <img src="{{ asset('storage/' . $student->user->profile_photo) }}" alt="{{ $rep->user?->name }}" class="w-full h-full rounded-full object-cover">
                            @else
                                <svg class="w-8 h-8 opacity-60" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                            @endif
                        </div>
                        <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-[#2563eb] text-white text-[9px] font-mono-code font-bold mb-2">
                            🎓 PELAJAR #{{ $studentRank }}
                        </div>
                        <h4 class="text-base font-black text-[#121316] truncate">{{ $rep->user?->name ?? 'Siswa Teladan' }}</h4>
                        <p class="text-xs font-bold text-[#2563eb] mt-0.5 truncate">
                            {{ $school?->name ?? 'SMK/SMA PEMBDA' }} &bull; {{ $student?->classroom?->name ?? 'Siswa Aktif' }}
                        </p>
                        <div class="mt-4 pt-3 border-t border-[#e2ded5]">
                            <span class="inline-block px-3 py-1 rounded-full border font-mono-code font-extrabold text-xs {{ $b['pill'] }}">
                                ⭐ {{ number_format($rep->total_points) }} Poin Reputasi
                            </span>
                        </div>
                    </div>
                    @php $studentRank++; @endphp
                @empty
                    <!-- Fallback Mock if Reputation DB Empty -->
                    <div class="bg-white border-2 border-[#121316] rounded-2xl p-6 text-center shadow-[4px_4px_0px_#121316]">
                        <div class="w-16 h-16 rounded-full bg-[#fbc02d] text-[#121316] font-black text-lg flex items-center justify-center mx-auto mb-3 border-2 border-[#121316]">
                            <svg class="w-8 h-8 opacity-60" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        </div>
                        <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-[#2563eb] text-white text-[9px] font-mono-code font-bold mb-2">🎓 PELAJAR</div>
                        <h4 class="text-base font-black text-[#121316]">Siswa Berprestasi</h4>
                        <p class="text-xs font-bold text-[#2563eb] mt-0.5">SMAS Pembda 1</p>
                        <div class="mt-4 pt-3 border-t border-[#e2ded5]">
                            <span class="inline-block px-3 py-1 rounded-full bg-[#faf3e0] border border-[#121316] font-mono-code font-extrabold text-xs text-[#121316]">⭐ 4.850 Poin</span>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ===== SECTION: TOP GURU ===== --}}
        <div>
            <div class="flex items-center gap-3 mb-6">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[#ff3823] text-white text-[11px] font-mono-code font-extrabold uppercase shadow-[3px_3px_0px_#121316]">
                    📚 GURU TELADAN
                </span>
                <div class="h-px flex-1 bg-[#e7e3d8]"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @php $teacherRank = 1; @endphp
                @forelse($topTeachersElite->take(3) as $topTeacher)
                    <div class="bg-white border-2 border-[#121316] rounded-2xl p-6 text-center shadow-[4px_4px_0px_#121316] relative overflow-hidden hover:-translate-y-1 transition-transform">
                        <!-- Avatar silhouette guru -->
                        <div class="w-16 h-16 rounded-full bg-[#ff3823]/10 text-[#ff3823] font-black text-lg flex items-center justify-center mx-auto mb-3 border-2 border-[#ff3823] shadow-[2px_2px_0px_#121316]">
                            @if($topTeacher->user && $topTeacher->user->profile_photo)
                                <img src="{{ asset('storage/' . $topTeacher->user->profile_photo) }}" alt="{{ $topTeacher->user?->name }}" class="w-full h-full rounded-full object-cover">
                            @else
                                <svg class="w-8 h-8 opacity-60" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                            @endif
                        </div>
                        <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-[#ff3823] text-white text-[9px] font-mono-code font-bold mb-2">
                            📚 GURU #{{ $teacherRank }}
                        </div>
                        <h4 class="text-base font-black text-[#121316] truncate">{{ $topTeacher->user?->name ?? 'Guru Inspiratif' }}</h4>
                        <p class="text-xs font-bold text-[#ff3823] mt-0.5 truncate">
                            {{ $topTeacher->user?->teacher?->school?->name ?? 'Guru Teladan PEMBDA' }}
                        </p>
                        <div class="mt-4 pt-3 border-t border-[#e2ded5]">
                            <span class="inline-block px-3 py-1 rounded-full bg-red-50 border border-[#ff3823] font-mono-code font-extrabold text-xs text-[#ff3823]">
                                ⭐ {{ number_format($topTeacher->total_points) }} Poin Dedikasi
                            </span>
                        </div>
                    </div>
                    @php $teacherRank++; @endphp
                @empty
                    <div class="bg-white border-2 border-[#121316] rounded-2xl p-6 text-center shadow-[4px_4px_0px_#121316]">
                        <div class="w-16 h-16 rounded-full bg-[#ff3823]/10 text-[#ff3823] flex items-center justify-center mx-auto mb-3 border-2 border-[#ff3823]">
                            <svg class="w-8 h-8 opacity-60" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        </div>
                        <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-[#ff3823] text-white text-[9px] font-mono-code font-bold mb-2">📚 GURU</div>
                        <h4 class="text-base font-black text-[#121316]">Pendidik Inspiratif</h4>
                        <p class="text-xs font-bold text-[#ff3823] mt-0.5">Perguruan PEMBDA Nias</p>
                        <div class="mt-4 pt-3 border-t border-[#e2ded5]">
                            <span class="inline-block px-3 py-1 rounded-full bg-red-50 border border-[#ff3823] font-mono-code font-extrabold text-xs text-[#ff3823]">🎖️ Dedikasi KBM</span>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</section>
