{{-- MITRA INDUSTRI — Direct Logos with Clean Border Dividers --}}
<section id="mitra-industri" class="py-14 px-4 sm:px-8 bg-[#faf8f5] border-t-2 border-[#121316]">
    <div class="max-w-7xl mx-auto">
        
        {{-- Judul Mitra Industri --}}
        <div class="text-center mb-8">
            <div class="text-[11px] font-mono-code font-bold text-[#ff3823] uppercase tracking-wider">
                ✱ MITRA INDUSTRI
            </div>
        </div>

        @php
            $mitraLogos = [
                ['name' => 'PT Astra International Tbk', 'file' => 'astra-international.png', 'scale' => ''],
                ['name' => 'Auto2000 (Toyota Astra)', 'file' => 'auto-2000.png', 'scale' => ''],
                ['name' => 'Axioo Class Program', 'file' => 'axioo-class-program.png', 'scale' => ''],
                ['name' => 'Astra Daihatsu Motor', 'file' => 'daihatsu.png', 'scale' => ''],
                ['name' => 'Pintar Bersama Daihatsu (PBD)', 'file' => 'pintar-bersama-daihatsu.png', 'scale' => 'scale-110 sm:scale-125'],
                ['name' => 'PT Tera Data Indonusa Tbk (Axioo)', 'file' => 'tera-data-internusa.png', 'scale' => ''],
                ['name' => 'Polytron (PT Hartono Istana Teknologi)', 'file' => 'polytron.png', 'scale' => ''],
                ['name' => 'PT Asaba Computer Centre', 'file' => 'pt-asaba.png', 'scale' => ''],
                ['name' => 'Phytaverse', 'file' => 'phytaverse.png', 'scale' => 'scale-110 sm:scale-120'],
            ];
        @endphp

        {{-- Direct Logos with Clean Border Lines (Tanpa Card) --}}
        <div class="border-t-2 border-l-2 border-[#121316] bg-white shadow-sm">
            <div class="grid grid-cols-3 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-9">
                @foreach($mitraLogos as $logo)
                    <div class="border-r-2 border-b-2 border-[#121316] p-3 sm:p-4 lg:p-5 h-28 sm:h-32 lg:h-36 flex items-center justify-center hover:bg-[#faf8f5] transition-colors group cursor-pointer"
                         title="{{ $logo['name'] }}">
                        <img src="{{ asset('images/mitra/' . $logo['file']) }}" 
                             alt="{{ $logo['name'] }}" 
                             loading="lazy"
                             class="max-h-16 sm:max-h-20 lg:max-h-24 max-w-[92%] object-contain transition-transform duration-200 group-hover:scale-110 {{ $logo['scale'] }}">
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</section>