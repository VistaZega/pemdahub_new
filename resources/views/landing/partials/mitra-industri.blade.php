{{-- MITRA INDUSTRI — Minimalist Pure Logos Showcase --}}
<section id="mitra-industri" class="py-14 px-4 sm:px-8 bg-[#faf8f5] border-t-2 border-[#121316]">
    <div class="max-w-7xl mx-auto">
        
        {{-- Judul Sederhana --}}
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

        {{-- 9 Logo Murni Ditata Rapi --}}
        <div class="grid grid-cols-3 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-9 gap-3 sm:gap-4 items-center justify-center">
            @foreach($mitraLogos as $logo)
                <div class="bg-white border-2 border-[#121316] rounded-2xl p-3 h-24 flex items-center justify-center shadow-[3px_3px_0px_#121316] hover:shadow-[4px_4px_0px_#ff3823] hover:-translate-y-1 transition-all duration-200 cursor-pointer group"
                     title="{{ $logo['name'] }}">
                    <img src="{{ asset('images/mitra/' . $logo['file']) }}" 
                         alt="{{ $logo['name'] }}" 
                         loading="lazy"
                         class="max-h-14 max-w-full object-contain transition-transform duration-200 group-hover:scale-105">
                </div>
            @endforeach
        </div>

    </div>
</section>