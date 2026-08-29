{{-- EKSTRAKURIKULER & MINAT BAKAT — Compact Tactile Design ($extracurriculars) --}}
@php
    $ekskulIcons = [
        'sains_teknologi' => ['icon' => '🤖', 'bg' => 'bg-blue-100', 'color' => 'text-[#2563eb]', 'badge' => 'bg-blue-50 text-[#2563eb] border-blue-200'],
        'seni_budaya' => ['icon' => '🎺', 'bg' => 'bg-amber-100', 'color' => 'text-[#b45309]', 'badge' => 'bg-amber-50 text-[#b45309] border-amber-200'],
        'kepanduan' => ['icon' => '⚜️', 'bg' => 'bg-emerald-100', 'color' => 'text-[#15803d]', 'badge' => 'bg-emerald-50 text-[#15803d] border-emerald-200'],
        'olahraga' => ['icon' => '⚽', 'bg' => 'bg-red-100', 'color' => 'text-[#ff3823]', 'badge' => 'bg-red-50 text-[#ff3823] border-red-200'],
        'keagamaan' => ['icon' => '🕊️', 'bg' => 'bg-indigo-100', 'color' => 'text-[#4338ca]', 'badge' => 'bg-indigo-50 text-[#4338ca] border-indigo-200'],
    ];
@endphp

<section id="ekskul" class="py-14 bg-[#f4f1ea] border-t border-[#e7e3d8]">
    <div class="max-w-7xl mx-auto px-4 sm:px-8">
        
        <!-- Header Ringkas -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
            <div>
                <div class="text-[10px] font-mono-code font-bold text-[#2563eb] uppercase tracking-wider mb-1">
                    ✱ PENGEMBANGAN KARAKTER & MINAT BAKAT
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-[#121316] tracking-tight">
                    Asah Potensi di <span class="highlight-marker">{{ isset($extracurriculars) ? $extracurriculars->count() : '16' }}+ Ekstrakurikuler.</span>
                </h2>
                <p class="text-xs text-[#555] font-medium mt-1 max-w-xl">
                    Wadah karakter tangguh, kreativitas seni, robotika, dan olahraga bagi siswa SMP, SMA, dan SMK.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-white border border-[#121316] rounded-full text-[11px] font-mono-code font-bold shadow-[2px_2px_0px_#121316]">
                    🎖️ 16+ Cabang Terpadu
                </span>
            </div>
        </div>

        <!-- Ekskul Cards Grid Compact (Hemat Ruang) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
            @forelse($extracurriculars->take(6) as $ekskul)
                @php
                    $style = $ekskulIcons[$ekskul->category] ?? [
                        'icon' => $ekskul->icon ? '⭐' : '🎯',
                        'bg' => 'bg-slate-100',
                        'color' => 'text-[#121316]',
                        'badge' => 'bg-slate-50 text-[#121316] border-slate-200'
                    ];
                @endphp
                <div class="bg-white border-2 border-[#121316] rounded-2xl p-4 shadow-[3px_3px_0px_#121316] flex flex-col justify-between hover:-translate-y-1 transition-transform">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-xl {{ $style['bg'] }} {{ $style['color'] }} flex items-center justify-center font-black text-lg border border-[#121316] shadow-[2px_2px_0px_#121316]">
                                <span>{{ $style['icon'] }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded {{ $style['badge'] }} border text-[9px] font-mono-code font-bold uppercase">
                                {{ str_replace('_', ' ', $ekskul->category ?? 'Ekskul') }}
                            </span>
                        </div>
                        <h3 class="text-base font-black text-[#121316] mb-1 truncate">{{ $ekskul->name }}</h3>
                        <p class="text-[11px] text-[#555] leading-relaxed font-medium mb-3 line-clamp-2">
                            {{ $ekskul->description ?? 'Wadah pembinaan bakat, keterampilan, dan disiplin siswa dibimbing pelatih berpengalaman.' }}
                        </p>
                    </div>
                    <div class="pt-3 border-t border-[#e2ded5] flex items-center justify-between text-[10px] font-mono-code font-bold text-[#777]">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            {{ $ekskul->active_members_count ?? 0 }} Anggota
                        </span>
                        <span class="{{ $style['color'] }} font-extrabold">{{ $ekskul->school?->name ?? 'Lintas 3 Unit' }} &rarr;</span>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-8 bg-white rounded-2xl border-2 border-dashed border-[#121316]">
                    <p class="text-xs font-bold text-[#777]">Data kegiatan ekstrakurikuler sedang diperbarui.</p>
                </div>
            @endforelse
        </div>

        <!-- Banner Ringkas Panduan Ekskul -->
        <div class="bg-white border-2 border-[#121316] rounded-2xl p-4 sm:p-5 shadow-[3px_3px_0px_#121316] flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-xl">💡</span>
                <p class="text-xs text-[#444] font-medium leading-normal">
                    <strong>Pendaftaran Ekskul:</strong> Siswa aktif dapat memilih maksimal 2 kegiatan ekstrakurikuler per semester melalui portal siswa PembdaHUB.
                </p>
            </div>
            @auth
                <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-full btn-tactile-red text-xs font-black whitespace-nowrap">
                    Pilih Ekskul di Portal &rarr;
                </a>
            @else
                <a href="{{ route('login') }}" class="px-4 py-2 rounded-full btn-tactile-red text-xs font-black whitespace-nowrap">
                    Masuk Portal Siswa &rarr;
                </a>
            @endauth
        </div>

    </div>
</section>
