{{-- KETUA YAYASAN (Dynamic, Clean Light Edition) --}}
<section class="bg-white py-14 md:py-20 border-t border-slate-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            @php
                $photoPath = base_path('public/images/photo-profile.jpeg');
                $photoExists = file_exists($photoPath);
                $ketuaNama = \App\Models\Setting::getValue('ketua_nama', 'Yulianus Zega, S.Kom, M.Pd.T');
                $ketuaJabatan = \App\Models\Setting::getValue('ketua_jabatan', 'Ketua Yayasan Perguruan PEMBDA Nias');
                $ketuaQuote = \App\Models\Setting::getValue('ketua_quote');
                $defaultQuote = "Selamat datang di PembdaHUB, platform ekosistem pendidikan digital masa depan Yayasan Perguruan PEMBDA Nias. Perguruan PEMBDA berkomitmen penuh melahirkan generasi emas Kepulauan Nias yang tidak hanya tangguh dan cerdas secara akademis, tetapi juga memiliki integritas karakter yang mulia serta menguasai teknologi modern secara profesional.";
                $finalQuote = ($ketuaQuote && strlen($ketuaQuote) > 50) ? $ketuaQuote : $defaultQuote;
            @endphp

            <div class="flex flex-col md:flex-row items-center gap-8 md:gap-10">
                {{-- Avatar / Photo --}}
                <div class="flex-shrink-0">
                    <div class="w-32 h-32 md:w-36 md:h-36 rounded-2xl overflow-hidden bg-slate-100 shadow-sm border border-slate-200">
                        @if($photoExists)
                            <img src="{{ asset('images/photo-profile.jpeg') }}?v={{ filemtime($photoPath) }}" alt="{{ $ketuaNama }}" class="w-full h-full object-cover object-center">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-400">
                                <i class="fa-solid fa-user-tie text-4xl"></i>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Content --}}
                <div class="text-center md:text-left flex-1 space-y-3">
                    <div class="inline-flex items-center gap-2 bg-amber-50 text-amber-800 border border-amber-200/60 px-3 py-1 rounded-full text-xs font-bold">
                        <i class="fa-solid fa-quote-left text-amber-500"></i> Sambutan Ketua Yayasan
                    </div>
                    <blockquote class="text-slate-700 text-sm sm:text-base leading-relaxed font-normal">
                        "{!! nl2br(e($finalQuote)) !!}"
                    </blockquote>
                    <div class="pt-2 border-t border-slate-100">
                        <div class="font-extrabold text-slate-900 text-base">{{ $ketuaNama }}</div>
                        <div class="text-xs text-slate-500 font-medium">{{ $ketuaJabatan }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
