{{-- SAMBUTAN KETUA YAYASAN — Clean Seamless Light Edition --}}
<section class="py-16 md:py-24 bg-white" id="sambutan-ketua-section">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        @php
            $photoPath = base_path('public/images/photo-profile.jpeg');
            $photoExists = file_exists($photoPath);
            $ketuaNama = \App\Models\Setting::getValue('ketua_nama', 'Yulianus Zega, S.Kom, M.Pd.T');
            $ketuaJabatan = \App\Models\Setting::getValue('ketua_jabatan', 'Ketua Yayasan Perguruan PEMBDA Nias');
            $ketuaQuote = \App\Models\Setting::getValue('ketua_quote');
            
            $defaultRichQuote = "Selamat datang di PembdaHUB, platform ekosistem pendidikan digital masa depan Yayasan Perguruan PEMBDA Nias. Perguruan PEMBDA berkomitmen penuh melahirkan generasi emas Kepulauan Nias yang tidak hanya tangguh dan cerdas secara akademis, tetapi juga memiliki integritas karakter yang mulia serta menguasai teknologi modern secara profesional.\n\nSelaras dengan motto abadi perjuangan kami: 'Keep Moving Forward / Maju Terus Pantang Mundur', kami terus berinovasi tanpa henti membangun lingkungan belajar berbasis teknologi digital terkini untuk menjawab tantangan era globalisasi.\n\nMari bersama-sama kita bergandengan tangan—pendidik, siswa, orang tua, dan alumni—melangkah pasti mewujudkan masa depan Nias yang gemilang, berdaya saing tinggi, dan berintegritas!";
            
            $finalQuote = ($ketuaQuote && strlen($ketuaQuote) > 50) ? $ketuaQuote : $defaultRichQuote;
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 md:gap-14 items-center">
            
            {{-- Left Column: Photo --}}
            <div class="lg:col-span-5 flex justify-center">
                <div class="relative w-full max-w-sm sm:max-w-md">
                    {{-- Soft ambient background glow --}}
                    <div class="absolute -inset-3 bg-gradient-to-tr from-amber-100/70 to-indigo-100/70 rounded-3xl blur-xl opacity-70"></div>
                    
                    <div class="relative rounded-2xl overflow-hidden bg-slate-50 shadow-md border border-slate-100">
                        @if($photoExists)
                            <img src="{{ asset('images/photo-profile.jpeg') }}?v={{ filemtime($photoPath) }}" 
                                 alt="{{ $ketuaNama }}" 
                                 class="w-full h-[380px] sm:h-[430px] object-cover object-center" />
                        @else
                            <div class="w-full h-[380px] sm:h-[430px] bg-slate-100 flex items-center justify-center text-slate-400">
                                <i class="fa-solid fa-user-tie text-7xl"></i>
                            </div>
                        @endif
                        
                        {{-- Soft Overlay Bottom Caption --}}
                        <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-slate-950/85 via-slate-950/40 to-transparent p-5 text-white">
                            <div class="font-extrabold text-base sm:text-lg leading-tight">{{ $ketuaNama }}</div>
                            <div class="text-xs text-amber-300 font-semibold mt-0.5">{{ $ketuaJabatan }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Speech Text --}}
            <div class="lg:col-span-7 space-y-5">
                
                {{-- Header Tag --}}
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-50 border border-amber-200/60 text-amber-800 text-xs font-bold tracking-wide">
                    <i class="fa-solid fa-quote-left text-amber-500"></i> Sambutan Ketua Yayasan
                </div>

                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 leading-tight tracking-tight">
                    Mewujudkan Pendidikan Berkarakter & Transisi Digital Bagi Masa Depan Nias
                </h2>

                <div class="text-amber-600 font-extrabold text-base sm:text-lg">
                    Ya'ahowu! Salam Sejahtera Bagi Kita Sekalian.
                </div>

                {{-- Clean Speech Paragraphs --}}
                <div class="text-slate-600 text-sm sm:text-base leading-relaxed space-y-3 font-normal">
                    @foreach(explode("\n\n", $finalQuote) as $paragraph)
                        @if(trim($paragraph))
                            <p>{!! nl2br(e(trim($paragraph))) !!}</p>
                        @endif
                    @endforeach
                </div>

                {{-- Motto Callout --}}
                <div class="border-l-4 border-amber-500 bg-amber-50/50 pl-4 py-3 rounded-r-xl my-4">
                    <div class="text-xs font-bold uppercase tracking-wider text-amber-700">Motto Perjuangan</div>
                    <div class="text-sm sm:text-base font-extrabold text-slate-900 mt-0.5">
                        "Keep Moving Forward — Maju Terus Pantang Mundur"
                    </div>
                </div>

                {{-- Signature Info --}}
                <div class="pt-3 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100">
                    <div>
                        <div class="font-extrabold text-slate-900 text-base sm:text-lg">{{ $ketuaNama }}</div>
                        <div class="text-xs text-slate-500 font-medium">{{ $ketuaJabatan }}</div>
                    </div>
                    <div class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-3.5 py-1.5 rounded-lg border border-indigo-100">
                        Yayasan Perguruan PEMBDA Nias
                    </div>
                </div>

            </div>

        </div>
    </div>
</section>
