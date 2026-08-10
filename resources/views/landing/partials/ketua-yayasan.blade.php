{{-- KETUA YAYASAN (Dynamic) --}}
<section class="bg-white py-16 md:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <div class="bg-gradient-to-br from-gray-50 to-emerald-50/50 rounded-3xl p-6 sm:p-10 border border-gray-100 shadow-sm">
                <div class="flex flex-col md:flex-row items-center gap-8">
                    {{-- Avatar --}}
                    <div class="flex-shrink-0">
                        <div class="w-28 h-28 md:w-32 md:h-32 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-200 overflow-hidden">
                            @php
                                $photoPath = base_path('public/images/photo-profile.jpeg');
                                $photoExists = file_exists($photoPath);
                            @endphp
                            @if($photoExists)
                                <img src="{{ asset('images/photo-profile.jpeg') }}?v={{ filemtime($photoPath) }}" alt="Yulianus Zega, S.Kom" class="w-full h-full object-cover object-center">
                            @else
                                <i class="fa-solid fa-user-tie text-white text-4xl md:text-5xl"></i>
                            @endif
                        </div>
                    </div>
                    {{-- Content --}}
                    <div class="text-center md:text-left flex-1">
                        <div class="inline-flex items-center gap-2 bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-semibold mb-3">
                            <i class="fa-solid fa-star"></i> Sambutan Ketua Yayasan
                        </div>
                        <blockquote class="text-gray-800 text-sm sm:text-base leading-relaxed mb-4 italic">
                            "{{ \App\Models\Setting::getValue('ketua_quote', "Salam sejahtera, Ya'ahowu! Sebagai garda terdepan pendidikan di Kepulauan Nias, Yayasan Perguruan PEMBDA berkomitmen penuh melahirkan generasi emas yang tangguh, berkarakter mulia, dan unggul secara teknologi. Selaras dengan motto abadi kami: 'Keep Moving Forward / Maju Terus Pantang Mundur', kami terus berinovasi tanpa henti melalui PembdaHUB untuk menciptakan ekosistem pembelajaran digital terbaik. Bersama, kita langkah demi langkah melangkah pasti menjawab tantangan zaman demi masa depan Nias yang gemilang!") }}"
                        </blockquote>
                        <div>
                            <div class="font-bold text-gray-900 text-base">{{ \App\Models\Setting::getValue('ketua_nama', 'Yulianus Zega, S.Kom') }}</div>
                            <div class="text-sm text-gray-500">{{ \App\Models\Setting::getValue('ketua_jabatan', 'Ketua Yayasan Perguruan PEMBDA Nias') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
