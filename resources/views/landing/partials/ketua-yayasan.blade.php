{{-- KETUA YAYASAN (Dynamic & Executive) --}}
<section class="bg-slate-900 py-16 md:py-20 text-white relative overflow-hidden">
    {{-- Glow background effects --}}
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-5xl mx-auto">
            <div class="bg-white/5 backdrop-blur-xl rounded-3xl p-8 sm:p-12 border border-white/10 shadow-2xl relative">
                <div class="flex flex-col md:flex-row items-center gap-10">
                    {{-- Avatar / Photo --}}
                    <div class="flex-shrink-0 relative">
                        <div class="w-36 h-36 md:w-44 md:h-44 rounded-2xl border-2 border-amber-400/50 shadow-xl overflow-hidden bg-slate-800">
                            @php
                                $photoPath = base_path('public/images/photo-profile.jpeg');
                                $photoExists = file_exists($photoPath);
                                $ketuaNama = \App\Models\Setting::getValue('ketua_nama', 'Yulianus Zega, S.Kom, M.Pd.T');
                                $ketuaJabatan = \App\Models\Setting::getValue('ketua_jabatan', 'Ketua Yayasan Perguruan PEMBDA Nias');
                                $ketuaQuote = \App\Models\Setting::getValue('ketua_quote');
                                $defaultQuote = "Salam sejahtera, Ya'ahowu! Sebagai garda terdepan pendidikan di Kepulauan Nias, Yayasan Perguruan PEMBDA berkomitmen penuh melahirkan generasi emas yang tangguh, berkarakter mulia, dan unggul secara teknologi. Selaras dengan motto abadi kami: 'Keep Moving Forward / Maju Terus Pantang Mundur', kami terus berinovasi tanpa henti melalui PembdaHUB untuk menciptakan ekosistem pembelajaran digital terbaik. Bersama, kita langkah demi langkah melangkah pasti menjawab tantangan zaman demi masa depan Nias yang gemilang!";
                                $finalQuote = ($ketuaQuote && strlen($ketuaQuote) > 50) ? $ketuaQuote : $defaultQuote;
                            @endphp
                            @if($photoExists)
                                <img src="{{ asset('images/photo-profile.jpeg') }}?v={{ filemtime($photoPath) }}" alt="{{ $ketuaNama }}" class="w-full h-full object-cover object-center">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-amber-400">
                                    <i class="fa-solid fa-user-tie text-5xl"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                    {{-- Content --}}
                    <div class="text-center md:text-left flex-1 space-y-4">
                        <div class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 text-white px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider shadow-md">
                            <i class="fa-solid fa-star"></i> Sambutan Ketua Yayasan
                        </div>
                        <h3 class="text-xl sm:text-2xl font-extrabold text-white leading-snug">
                            "Mewujudkan Pendidikan Nias yang Berkarakter & Unggul Berbasis Teknologi"
                        </h3>
                        <blockquote class="text-slate-300 text-sm sm:text-base leading-relaxed italic">
                            "{{ $finalQuote }}"
                        </blockquote>
                        <div class="pt-2 border-t border-white/10">
                            <div class="font-black text-amber-400 text-base md:text-lg">{{ $ketuaNama }}</div>
                            <div class="text-xs md:text-sm text-slate-400 font-semibold">{{ $ketuaJabatan }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
