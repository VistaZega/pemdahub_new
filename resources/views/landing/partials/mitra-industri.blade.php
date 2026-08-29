{{-- MITRA INDUSTRI — Direct Logos with Clean Border Dividers --}}
<section id="mitra-industri" class="py-14 px-4 sm:px-8 bg-[#faf8f5] border-t-2 border-[#121316]">
    <div class="max-w-7xl mx-auto">
        
        {{-- Judul Mitra Industri --}}
        <div class="text-center mb-8">
            <h2 class="text-2xl sm:text-3xl font-black text-[#121316] tracking-tight uppercase">
                Mitra Industri
            </h2>
        </div>

        @php
            $mitraLogos = [
                ['name' => 'PT Astra International Tbk', 'file' => 'astra-international.png'],
                ['name' => 'Auto2000 (Toyota Astra)', 'file' => 'auto-2000.png'],
                ['name' => 'Axioo Class Program', 'file' => 'axioo-class-program.png'],
                ['name' => 'Astra Daihatsu Motor', 'file' => 'daihatsu.png'],
                ['name' => 'Pintar Bersama Daihatsu (PBD)', 'file' => 'pintar-bersama-daihatsu.png'],
                ['name' => 'PT Tera Data Indonusa Tbk (Axioo)', 'file' => 'tera-data-internusa.png'],
                ['name' => 'Polytron (PT Hartono Istana Teknologi)', 'file' => 'polytron.png'],
                ['name' => 'PT Asaba Computer Centre', 'file' => 'pt-asaba.png'],
                ['name' => 'Phytaverse', 'file' => 'phytaverse.png'],
            ];
        @endphp

        {{-- Direct Logos with Clean Border Lines (Tanpa Card) --}}
        <div class="border-t-2 border-l-2 border-[#121316] bg-white">
            <div class="grid grid-cols-3 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-9">
                @foreach($mitraLogos as $logo)
                    <div class="border-r-2 border-b-2 border-[#121316] p-3 sm:p-4 h-24 sm:h-28 flex items-center justify-center hover:bg-[#faf8f5] transition-colors group cursor-pointer"
                         title="{{ $logo['name'] }}">
                        <img src="{{ asset('images/mitra/' . $logo['file']) }}" 
                             alt="{{ $logo['name'] }}" 
                             loading="lazy"
                             class="max-h-12 sm:max-h-14 max-w-[85%] object-contain transition-transform duration-200 group-hover:scale-110">
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</section>