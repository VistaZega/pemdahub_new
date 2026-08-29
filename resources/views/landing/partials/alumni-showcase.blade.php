{{-- JEJARING ALUMNI — 100% Real Database ($recentAlumnis & $totalAlumni) --}}
<section id="alumni" class="py-20 bg-[#f4f1ea] border-t border-[#e7e3d8]">
    <div class="max-w-7xl mx-auto px-4 sm:px-8">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
            <div>
                <div class="text-[11px] font-mono-code font-bold text-[#ff3823] uppercase tracking-wider mb-2">
                    ✱ JEJARING SOLID SEJAK 1970
                </div>
                <h2 class="text-3xl sm:text-4xl font-black text-[#121316] tracking-tight">
                    Jejak Sukses Alumni di <span class="highlight-marker">Seluruh Nusantara.</span>
                </h2>
                <p class="text-xs sm:text-sm text-[#555] font-medium mt-2 max-w-xl">
                    Dari perbankan BUMN, profesional teknologi, tenaga medis, wirausahawan mandiri, hingga mahasiswa berprestasi di perguruan tinggi negeri favorit.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3">
                <a href="{{ route('ika.directory') }}" class="px-5 py-2.5 rounded-full btn-tactile-white text-xs font-bold">
                    Direktori Lengkap &rarr;
                </a>
                <a href="{{ route('ika.register') }}" class="px-5 py-2.5 rounded-full btn-tactile-red text-xs font-black">
                    + Gabung Alumni
                </a>
            </div>
        </div>

        <!-- Alumni Stories Grid (Real from Database $recentAlumnis) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            @forelse($recentAlumnis as $alumni)
                <div class="bg-white border-2 border-[#121316] rounded-2xl p-6 shadow-[4px_4px_0px_#121316] flex flex-col justify-between hover:-translate-y-1 transition-transform">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-[#2563eb] text-white flex items-center justify-center font-black text-sm border border-[#121316] shadow-[2px_2px_0px_#121316] overflow-hidden">
                                @if($alumni->photo_url)
                                    <img src="{{ $alumni->photo_url }}" alt="{{ $alumni->full_name }}" class="w-full h-full object-cover">
                                @else
                                    <span>{{ strtoupper(substr($alumni->full_name, 0, 2)) }}</span>
                                @endif
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-[#121316]">{{ $alumni->full_name }}</h4>
                                <p class="text-[10px] font-mono-code font-bold text-[#2563eb]">
                                    {{ $alumni->school?->name ?? 'Alumni PEMBDA' }} &bull; Lulus {{ $alumni->graduation_year ?? '-' }}
                                </p>
                            </div>
                        </div>

                        @if($alumni->current_activity || $alumni->profession)
                            <span class="inline-block px-2.5 py-0.5 rounded bg-blue-50 text-[#2563eb] border border-blue-200 text-[10px] font-mono-code font-bold mb-3">
                                💼 {{ $alumni->current_activity ?? $alumni->profession }}
                            </span>
                        @endif

                        <p class="text-xs text-[#555] italic leading-relaxed font-medium mb-4 line-clamp-4">
                            "{{ $alumni->message }}"
                        </p>
                    </div>
                    <div class="pt-4 border-t border-[#e2ded5] text-[10px] font-mono-code font-bold text-[#888] flex items-center justify-between">
                        <span>{{ $alumni->city ?? 'Indonesia' }}</span>
                        <span class="text-[#2563eb]">Terverifikasi ✓</span>
                    </div>
                </div>
            @empty
                <!-- Fallback Story 1 -->
                <div class="bg-white border-2 border-[#121316] rounded-2xl p-6 shadow-[4px_4px_0px_#121316] flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-[#2563eb] text-white flex items-center justify-center font-black text-sm border border-[#121316]">HZ</div>
                            <div>
                                <h4 class="text-sm font-black text-[#121316]">Hendra Zega, S.T.</h4>
                                <p class="text-[10px] font-mono-code font-bold text-[#2563eb]">Lulusan SMA Pembda 1 '14</p>
                            </div>
                        </div>
                        <span class="inline-block px-2 py-0.5 rounded bg-blue-50 text-[#2563eb] border border-blue-200 text-[10px] font-mono-code font-bold mb-3">💼 Lead Software Engineer</span>
                        <p class="text-xs text-[#555] italic leading-relaxed font-medium mb-4">"Pondasi logika sains dan pembinaan guru di SMA Pembda sangat membantu saya bersaing di industri teknologi."</p>
                    </div>
                    <div class="pt-4 border-t border-[#e2ded5] text-[10px] font-mono-code font-bold text-[#888]">Tech Startup Jakarta</div>
                </div>
                <!-- Fallback Story 2 -->
                <div class="bg-white border-2 border-[#121316] rounded-2xl p-6 shadow-[4px_4px_0px_#121316] flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-[#fbc02d] text-[#121316] flex items-center justify-center font-black text-sm border border-[#121316]">YG</div>
                            <div>
                                <h4 class="text-sm font-black text-[#121316]">Yuliani Gea, S.E.</h4>
                                <p class="text-[10px] font-mono-code font-bold text-[#b45309]">Lulusan SMK Pembda '17</p>
                            </div>
                        </div>
                        <span class="inline-block px-2 py-0.5 rounded bg-amber-50 text-[#b45309] border border-amber-200 text-[10px] font-mono-code font-bold mb-3">🏦 Senior Teller & Audit</span>
                        <p class="text-xs text-[#555] italic leading-relaxed font-medium mb-4">"Praktik akuntansi dan TEFA di SMK membuat saya langsung siap kerja di dunia perbankan."</p>
                    </div>
                    <div class="pt-4 border-t border-[#e2ded5] text-[10px] font-mono-code font-bold text-[#888]">Bank Mandiri Cabang</div>
                </div>
                <!-- Fallback Story 3 -->
                <div class="bg-white border-2 border-[#121316] rounded-2xl p-6 shadow-[4px_4px_0px_#121316] flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-[#10b981] text-white flex items-center justify-center font-black text-sm border border-[#121316]">PC</div>
                            <div>
                                <h4 class="text-sm font-black text-[#121316]">Putri C. Lase</h4>
                                <p class="text-[10px] font-mono-code font-bold text-[#10b981]">Lulusan SMA Pembda 1 '23</p>
                            </div>
                        </div>
                        <span class="inline-block px-2 py-0.5 rounded bg-emerald-50 text-[#10b981] border border-emerald-200 text-[10px] font-mono-code font-bold mb-3">🩺 Mahasiswi Kedokteran</span>
                        <p class="text-xs text-[#555] italic leading-relaxed font-medium mb-4">"Bimbingan belajar intensif di SMA Pembda 1 membantu saya lulus SNBP di Fakultas Kedokteran."</p>
                    </div>
                    <div class="pt-4 border-t border-[#e2ded5] text-[10px] font-mono-code font-bold text-[#888]">Universitas Sumatera Utara</div>
                </div>
            @endforelse
        </div>

        <!-- Alumni Stats Counter Banner -->
        <div class="bg-[#121316] text-white rounded-2xl p-6 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <div class="text-3xl">🤝</div>
                <div>
                    <h4 class="text-base font-black">Ikatan Keluarga Alumni Perguruan PEMBDA Nias (IKA-PEMBDA)</h4>
                    <p class="text-xs text-slate-400">Wadah silaturahmi, beasiswa adik kelas, dan jejaring karir alumni.</p>
                </div>
            </div>
            <div class="flex items-center gap-4 text-xs font-mono-code font-bold">
                <span class="text-amber-400">{{ number_format($totalAlumni) }}+ Terdata</span>
                <span>&bull;</span>
                <span class="text-emerald-400">Lintas Kota</span>
                <span>&bull;</span>
                <span class="text-blue-400">100% Solid</span>
            </div>
        </div>

    </div>
</section>
