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
                ['name' => 'PT Astra International Tbk', 'file' => 'astra-international.png', 'class' => ''],
                ['name' => 'Auto2000 (Toyota Astra)', 'file' => 'auto-2000.png', 'class' => ''],
                ['name' => 'Axioo Class Program', 'file' => 'axioo-class-program.png', 'class' => ''],
                ['name' => 'Astra Daihatsu Motor', 'file' => 'daihatsu.png', 'class' => ''],
                ['name' => 'Pintar Bersama Daihatsu (PBD)', 'file' => 'pintar-bersama-daihatsu.png', 'class' => 'scale-[2.0] group-hover:scale-[2.15]'],
                ['name' => 'PT Tera Data Indonusa Tbk (Axioo)', 'file' => 'tera-data-internusa.png', 'class' => ''],
                ['name' => 'Polytron (PT Hartono Istana Teknologi)', 'file' => 'polytron.png', 'class' => 'scale-[1.5] group-hover:scale-[1.65]'],
                ['name' => 'PT Asaba Computer Centre', 'file' => 'pt-asaba.png', 'class' => 'scale-[1.3] group-hover:scale-[1.45]'],
                ['name' => 'Phytaverse', 'file' => 'phytaverse.png', 'class' => 'scale-[2.0] group-hover:scale-[2.15]'],
            ];
        @endphp

        {{-- Direct Logos with Clean Border Lines (Tanpa Card) --}}
        <div class="border-t-2 border-l-2 border-[#121316] bg-white shadow-sm">
            <div class="grid grid-cols-3 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-9">
                @foreach($mitraLogos as $logo)
                    <div class="border-r-2 border-b-2 border-[#121316] p-2 sm:p-2.5 md:p-3 h-20 sm:h-24 md:h-28 flex items-center justify-center hover:bg-[#faf8f5] transition-colors group cursor-pointer overflow-hidden"
                         title="{{ $logo['name'] }}">
                        <img src="{{ asset('images/mitra/' . $logo['file']) }}" 
                             alt="{{ $logo['name'] }}" 
                             loading="lazy"
                             class="h-full w-full max-h-[85%] max-w-[92%] object-contain transition-transform duration-200 {{ !empty($logo['class']) ? $logo['class'] : 'group-hover:scale-110' }}">
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</section>