{{-- 3 UNIT SEKOLAH MANDIRI — 100% Real Database ($schools) --}}
@php
    $unitColors = [
        'sma' => ['color' => 'text-[#2563eb]', 'badge' => 'bg-emerald-100 text-emerald-800', 'badge_text' => 'Akreditasi A', 'desc' => 'Fokus pembinaan sains intensif, persiapan masuk Perguruan Tinggi Negeri (PTN) favorit, sekolah kedinasan, dan olimpiade.'],
        'smp' => ['color' => 'text-[#fbc02d]', 'badge' => 'bg-emerald-100 text-emerald-800', 'badge_text' => 'Akreditasi A', 'desc' => 'Pondasi karakter mandiri, budaya literasi & numerasi, penguatan sains dasar, serta minat seni dan olahraga kepanduan.'],
        'smk' => ['color' => 'text-[#ff3823]', 'badge' => 'bg-indigo-100 text-indigo-800', 'badge_text' => 'Pusat Vokasi DUDI', 'desc' => '5 Konsentrasi Keahlian: TKJ, Rekayasa Perangkat Lunak (RPL), Teknik Sepeda Motor (TBSM), TKRO, dan Akuntansi (AKL) siap kerja.'],
    ];
@endphp

<section id="sekolah" class="py-20 bg-[#f4f1ea] border-t border-[#e7e3d8]">
    <div class="max-w-7xl mx-auto px-4 sm:px-8">
        
        <!-- Header -->
        <div class="text-center max-w-xl mx-auto mb-12">
            <div class="text-[11px] font-mono-code font-bold text-[#ff3823] uppercase tracking-wider mb-1">
                ✱ TIGA PILAR INSTITUSI
            </div>
            <h2 class="text-3xl sm:text-4xl font-black text-[#121316] tracking-tight">
                Satu Kompleks Perguruan di Nias.
            </h2>
            <p class="text-xs sm:text-sm text-[#555] font-medium mt-2">
                Yayasan Perguruan PEMBDA Nias menaungi 3 jenjang pendidikan formal yang saling bersinergi dan terakreditasi unggul.
            </p>
        </div>

        <!-- 3 Unit Sekolah Cards Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @forelse($schools as $school)
                @php
                    $typeKey = strtolower($school->type ?? 'sma');
                    $cfg = $unitColors[$typeKey] ?? [
                        'color' => 'text-[#2563eb]',
                        'badge' => 'bg-emerald-100 text-emerald-800',
                        'badge_text' => 'Terakreditasi',
                        'desc' => $school->description ?? 'Unit pendidikan unggul di bawah naungan Yayasan Perguruan PEMBDA Nias.'
                    ];
                @endphp
                <div class="bg-white border-2 border-[#121316] rounded-2xl p-6 shadow-[4px_4px_0px_#121316] flex flex-col justify-between hover:-translate-y-1 transition-transform">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="font-mono-code text-xs font-black {{ $cfg['color'] }} uppercase tracking-wider">
                                {{ strtoupper($school->type) }} PEMBDA
                            </span>
                            <span class="px-2.5 py-0.5 rounded {{ $cfg['badge'] }} text-[10px] font-mono-code font-bold">
                                {{ $school->accreditation ? 'Akreditasi ' . $school->accreditation : $cfg['badge_text'] }}
                            </span>
                        </div>
                        
                        <h3 class="text-xl font-black text-[#121316] mb-2 leading-tight">
                            {{ $school->name }}
                        </h3>
                        
                        <p class="text-xs text-[#555] leading-relaxed mb-6 font-medium">
                            {{ $school->description ?? $cfg['desc'] }}
                        </p>
                    </div>

                    <!-- Metrics Footer -->
                    <div class="pt-4 border-t border-[#e2ded5] grid grid-cols-3 gap-2 text-center text-[11px] font-mono-code">
                        <div class="p-2 bg-[#faf8f5] rounded-lg border border-[#e7e3d8]">
                            <div class="font-black text-[#121316] text-sm">{{ $school->students_count ?? 0 }}</div>
                            <div class="text-[9px] text-[#777] uppercase font-bold">Siswa</div>
                        </div>
                        <div class="p-2 bg-[#faf8f5] rounded-lg border border-[#e7e3d8]">
                            <div class="font-black text-[#121316] text-sm">{{ $school->teachers_count ?? 0 }}</div>
                            <div class="text-[9px] text-[#777] uppercase font-bold">Guru</div>
                        </div>
                        <div class="p-2 bg-[#faf8f5] rounded-lg border border-[#e7e3d8]">
                            <div class="font-black text-[#121316] text-sm">{{ $school->classrooms_count ?? 0 }}</div>
                            <div class="text-[9px] text-[#777] uppercase font-bold">Rombel</div>
                        </div>
                    </div>

                    @if($typeKey === 'smk')
                        <div class="mt-3 pt-3 border-t border-[#e7e3d8]">
                            <button type="button" onclick="openDudiMapModal()" class="w-full text-center py-2 px-3 rounded-xl bg-red-50 hover:bg-red-100 text-[#ff3823] font-mono-code font-bold text-xs flex items-center justify-center gap-1.5 transition-colors border border-[#ff3823]/30">
                                <span>📍 Lihat Peta Lokasi PKL (Google Maps)</span>
                                <span>&rarr;</span>
                            </button>
                        </div>
                    @endif
                </div>
            @empty
                <div class="col-span-3 text-center py-12 bg-white rounded-2xl border-2 border-dashed border-[#121316]">
                    <p class="text-sm font-bold text-[#777]">3 Unit Sekolah: SMAS Pembda 1, SMPS Pembda 2, dan SMKS Pembda Nias.</p>
                </div>
            @endforelse
        </div>

    </div>
</section>
