{{-- 3 UNIT SEKOLAH MANDIRI — 100% Real Database ($schools) with Distinct Card Colors --}}
@php
    $unitColors = [
        'sma' => [
            'card_bg' => 'bg-[#dbe7d7]', // Card Background Hijau Lumut (Moss Green)
            'color' => 'text-[#2d5a27]',
            'badge' => 'bg-[#2d5a27] text-white',
            'badge_text' => 'Akreditasi A',
            'shadow' => 'shadow-[4px_4px_0px_#121316]',
            'metric_border' => 'border-[#2d5a27]/30',
            'desc' => 'Pusat keunggulan sains terpadu dan riset akademik, mencetak lulusan berprestasi yang siap menembus Perguruan Tinggi Negeri (PTN) favorit, sekolah kedinasan, serta kejuaraan olimpiade tingkat nasional.'
        ],
        'smk' => [
            'card_bg' => 'bg-[#f0e2d3]', // Card Background Coklat Muda (Light Brown / Warm Tan)
            'color' => 'text-[#9a5b2d]',
            'badge' => 'bg-[#9a5b2d] text-white',
            'badge_text' => 'Pusat Vokasi DUDI',
            'shadow' => 'shadow-[4px_4px_0px_#121316]',
            'metric_border' => 'border-[#9a5b2d]/30',
            'desc' => 'Pusat vokasi industri unggul dengan 5 konsentrasi keahlian modern: TKJ, RPL, TBSM, TKRO, dan Akuntansi, melahirkan teknisi terampil siap kerja dengan sertifikasi kompetensi standar DUDI nasional.'
        ],
        'smp' => [
            'card_bg' => 'bg-[#d9effc]', // Card Background Biru Langit (Sky Blue)
            'color' => 'text-[#0284c7]',
            'badge' => 'bg-[#0284c7] text-white',
            'badge_text' => 'Akreditasi A',
            'shadow' => 'shadow-[4px_4px_0px_#121316]',
            'metric_border' => 'border-[#0284c7]/30',
            'desc' => 'Pondasi pembentukan karakter unggul, penguatan literasi digital, sains dasar, dan kepemimpinan, menumbuhkan potensi minat bakat seni, riset dini, serta olahraga kepanduan yang berintegritas tinggi.'
        ],
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

        <!-- 3 Unit Sekolah Cards Grid dengan Warna Card Khusus -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @forelse($schools as $school)
                @php
                    $typeKey = strtolower($school->type ?? 'sma');
                    $cfg = $unitColors[$typeKey] ?? $unitColors['sma'];
                @endphp
                <div class="{{ $cfg['card_bg'] }} border-2 border-[#121316] rounded-2xl p-6 {{ $cfg['shadow'] }} flex flex-col justify-between hover:-translate-y-1 transition-transform">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="font-mono-code text-xs font-black {{ $cfg['color'] }} uppercase tracking-wider">
                                {{ strtoupper($school->type) }} PEMBDA
                            </span>
                            <span class="px-2.5 py-0.5 rounded {{ $cfg['badge'] }} text-[10px] font-mono-code font-bold border border-[#121316]/20">
                                {{ $school->accreditation ? 'Akreditasi ' . $school->accreditation : $cfg['badge_text'] }}
                            </span>
                        </div>
                        
                        <h3 class="text-xl font-black text-[#121316] mb-2 leading-tight">
                            {{ $school->name }}
                        </h3>
                        
                        <p class="text-xs text-[#2b2b2b] leading-relaxed mb-6 font-medium">
                            {{ $cfg['desc'] }}
                        </p>
                    </div>

                    <!-- Metrics Footer -->
                    <div>
                        <div class="pt-4 border-t border-[#121316]/15 grid grid-cols-3 gap-2 text-center text-[11px] font-mono-code">
                            <div class="p-2 bg-white/90 rounded-lg border border-[#121316]/20 shadow-sm">
                                <div class="font-black text-[#121316] text-sm">{{ $school->students_count ?? 0 }}</div>
                                <div class="text-[9px] text-[#555] uppercase font-bold">Siswa</div>
                            </div>
                            <div class="p-2 bg-white/90 rounded-lg border border-[#121316]/20 shadow-sm">
                                <div class="font-black text-[#121316] text-sm">{{ $school->teachers_count ?? 0 }}</div>
                                <div class="text-[9px] text-[#555] uppercase font-bold">Guru</div>
                            </div>
                            <div class="p-2 bg-white/90 rounded-lg border border-[#121316]/20 shadow-sm">
                                <div class="font-black text-[#121316] text-sm">{{ $school->classrooms_count ?? 0 }}</div>
                                <div class="text-[9px] text-[#555] uppercase font-bold">Rombel</div>
                            </div>
                        </div>

                        @if($typeKey === 'smk')
                            <div class="mt-3 pt-3 border-t border-[#121316]/15">
                                <a href="{{ route('public.pkl.map') }}" class="w-full text-center py-2 px-3 rounded-xl bg-[#9a5b2d] hover:bg-[#7e471f] text-white font-mono-code font-bold text-xs flex items-center justify-center gap-1.5 transition-colors border border-[#121316] shadow-[2px_2px_0px_#121316]">
                                    <span>🗺️ Peta GPS Sebaran Siswa PKL (Live Map)</span>
                                    <span>&rarr;</span>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-12 bg-white rounded-2xl border-2 border-dashed border-[#121316]">
                    <p class="text-sm font-bold text-[#777]">3 Unit Sekolah: SMAS Pembda 1, SMPS Pembda 2, dan SMKS Pembda Nias.</p>
                </div>
            @endforelse
        </div>

    </div>
</section>
