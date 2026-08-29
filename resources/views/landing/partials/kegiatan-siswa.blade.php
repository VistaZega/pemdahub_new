{{-- EKSTRAKURIKULER & MINAT BAKAT — 100% Real Database ($extracurriculars) with Cover Images --}}
@php
    $ekskulIcons = [
        'sains_teknologi' => ['icon' => '🤖', 'bg' => 'bg-blue-100', 'color' => 'text-[#2563eb]', 'badge' => 'bg-blue-50 text-[#2563eb] border-blue-200', 'gradient' => 'from-blue-600 to-blue-400'],
        'seni_budaya' => ['icon' => '🎺', 'bg' => 'bg-amber-100', 'color' => 'text-[#b45309]', 'badge' => 'bg-amber-50 text-[#b45309] border-amber-200', 'gradient' => 'from-amber-600 to-amber-400'],
        'kepanduan' => ['icon' => '⚜️', 'bg' => 'bg-emerald-100', 'color' => 'text-[#15803d]', 'badge' => 'bg-emerald-50 text-[#15803d] border-emerald-200', 'gradient' => 'from-emerald-600 to-emerald-400'],
        'olahraga' => ['icon' => '⚽', 'bg' => 'bg-red-100', 'color' => 'text-[#ff3823]', 'badge' => 'bg-red-50 text-[#ff3823] border-red-200', 'gradient' => 'from-red-600 to-red-400'],
        'keagamaan' => ['icon' => '🕊️', 'bg' => 'bg-indigo-100', 'color' => 'text-[#4338ca]', 'badge' => 'bg-indigo-50 text-[#4338ca] border-indigo-200', 'gradient' => 'from-indigo-600 to-indigo-400'],
    ];
@endphp

<section id="ekskul" class="py-20 bg-[#f4f1ea] border-t border-[#e7e3d8]">
    <div class="max-w-7xl mx-auto px-4 sm:px-8">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
            <div>
                <div class="text-[11px] font-mono-code font-bold text-[#2563eb] uppercase tracking-wider mb-2">
                    ✱ PENGEMBANGAN KARAKTER & MINAT BAKAT
                </div>
                <h2 class="text-3xl sm:text-4xl font-black text-[#121316] tracking-tight">
                    Asah Potensi di <span class="highlight-marker">{{ isset($extracurriculars) ? $extracurriculars->count() : '16' }}+ Ekstrakurikuler.</span>
                </h2>
                <p class="text-xs sm:text-sm text-[#555] font-medium mt-2 max-w-xl">
                    Wadah pembentukan karakter tangguh, kreativitas seni, riset teknologi, dan prestasi olahraga bagi seluruh siswa SMP, SMA, dan SMK.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="px-3.5 py-1.5 bg-white border-2 border-[#121316] rounded-full text-xs font-mono-code font-bold shadow-[2px_2px_0px_#121316]">
                    🎖️ Prestasi Tingkat Kota & Provinsi
                </span>
            </div>
        </div>

        <!-- Ekskul Cards Grid with Cover Images -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
            @forelse($extracurriculars->take(6) as $ekskul)
                @php
                    $style = $ekskulIcons[$ekskul->category] ?? [
                        'icon' => $ekskul->icon ? '⭐' : '🎯',
                        'bg' => 'bg-slate-100',
                        'color' => 'text-[#121316]',
                        'badge' => 'bg-slate-50 text-[#121316] border-slate-200',
                        'gradient' => 'from-slate-600 to-slate-400'
                    ];
                    $hasCover = $ekskul->cover_image && file_exists(public_path('storage/' . $ekskul->cover_image));
                @endphp
                <div class="bg-white border-2 border-[#121316] rounded-2xl overflow-hidden shadow-[4px_4px_0px_#121316] flex flex-col justify-between hover:-translate-y-1 transition-transform group">
                    
                    {{-- Cover Image Area --}}
                    <div class="relative h-36 overflow-hidden">
                        @if($hasCover)
                            <img src="{{ asset('storage/' . $ekskul->cover_image) }}" alt="{{ $ekskul->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                        @else
                            <div class="w-full h-full bg-gradient-to-br {{ $style['gradient'] }} flex items-center justify-center relative">
                                <span class="text-5xl opacity-30 group-hover:scale-110 transition-transform duration-500">{{ $style['icon'] }}</span>
                                <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle, #fff 1px, transparent 1px); background-size: 10px 10px;"></div>
                            </div>
                        @endif
                        
                        {{-- Floating badges on image --}}
                        <div class="absolute top-3 left-3 flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded {{ $style['badge'] }} border text-[10px] font-mono-code font-bold uppercase backdrop-blur-sm">
                                {{ str_replace('_', ' ', $ekskul->category ?? 'Ekskul') }}
                            </span>
                        </div>
                        <div class="absolute bottom-3 right-3">
                            <div class="w-10 h-10 rounded-xl {{ $style['bg'] }} {{ $style['color'] }} flex items-center justify-center font-black text-lg border border-[#121316] shadow-[2px_2px_0px_#121316] backdrop-blur-sm">
                                <span>{{ $style['icon'] }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Text Content --}}
                    <div class="p-5">
                        <h3 class="text-lg font-black text-[#121316] mb-1">{{ $ekskul->name }}</h3>
                        <p class="text-xs text-[#555] leading-relaxed font-medium mb-4 line-clamp-2">
                            {{ $ekskul->description ?? 'Wadah pembinaan bakat, keterampilan, dan disiplin siswa yang aktif dibimbing oleh pelatih profesional.' }}
                        </p>
                    </div>

                    {{-- Footer --}}
                    <div class="px-5 pb-5">
                        <div class="pt-4 border-t border-[#e2ded5] flex items-center justify-between text-[11px] font-mono-code font-bold text-[#777]">
                            <span class="flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                {{ $ekskul->active_members_count ?? 0 }} Anggota Aktif
                            </span>
                            <span class="{{ $style['color'] }}">{{ $ekskul->school?->name ?? 'Lintas 3 Unit' }} &rarr;</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-12 bg-white rounded-2xl border-2 border-dashed border-[#121316]">
                    <p class="text-sm font-bold text-[#777]">Data kegiatan ekstrakurikuler sedang diperbarui.</p>
                </div>
            @endforelse
        </div>

        <!-- Banner Panduan Pemilihan Ekskul -->
        <div class="bg-white border-2 border-[#121316] rounded-2xl p-6 shadow-[3px_3px_0px_#121316] flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-2xl">💡</span>
                <p class="text-xs text-[#444] font-medium">
                    <strong>Pendaftaran Ekskul Bebas Biaya:</strong> Siswa aktif SMP, SMA, dan SMK dapat memilih maksimal 2 kegiatan ekstrakurikuler per semester melalui portal siswa PembdaHUB.
                </p>
            </div>
            @auth
                <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-full btn-tactile-red text-xs font-black whitespace-nowrap">
                    Pilih Ekskul di Portal &rarr;
                </a>
            @else
                <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-full btn-tactile-red text-xs font-black whitespace-nowrap">
                    Masuk Portal Siswa &rarr;
                </a>
            @endauth
        </div>

    </div>
</section>
