@php
    $activeOsis = \App\Models\OsisElection::with(['school', 'candidates'])
        ->where('is_active', true)
        ->whereIn('status', ['voting_open', 'upcoming'])
        ->latest()
        ->first();
@endphp

@if($activeOsis)
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 mb-12 relative z-20">
    <div class="relative overflow-hidden rounded-[2.5rem] p-6 md:p-8 border-3 border-slate-900 shadow-2xl transition hover:shadow-emerald-900/20"
         style="background: linear-gradient(135deg, #090d16 0%, #1e3a8a 50%, #064e3b 100%); color: #ffffff;">
        
        {{-- Background blur circles --}}
        <div class="absolute -top-12 -right-12 w-64 h-64 bg-emerald-400/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -left-12 w-64 h-64 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-6">
            
            {{-- Left Info --}}
            <div class="flex items-center gap-5 text-center lg:text-left flex-col sm:flex-row">
                <div class="w-16 h-16 md:w-20 md:h-20 rounded-2xl bg-white/10 backdrop-blur-md p-3 border border-white/20 shadow-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-vote-yea text-3xl md:text-4xl text-emerald-300 animate-pulse"></i>
                </div>
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-0.5 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-[11px] font-black uppercase tracking-wider mb-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        E-VOTING OSIS PERIODE {{ $activeOsis->period ?? '2026/2027' }}
                    </div>
                    <h2 class="text-xl md:text-2xl lg:text-3xl font-black uppercase tracking-tight leading-tight">
                        {{ $activeOsis->title }}
                    </h2>
                    <p class="text-xs md:text-sm text-slate-200 mt-1 italic font-medium">
                        &ldquo;{{ $activeOsis->theme_tagline ?? 'Bersama Membangun Masa Depan yang Lebih Baik' }}&rdquo;
                    </p>
                </div>
            </div>

            {{-- Right CTA Buttons --}}
            <div class="flex flex-wrap items-center justify-center gap-3 flex-shrink-0 w-full sm:w-auto">
                <a href="{{ route('osis.public.slug', $activeOsis->slug) }}" 
                   class="px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white text-xs font-black border border-white/30 backdrop-blur-sm transition flex items-center gap-2">
                    <i class="fas fa-users text-amber-300"></i>
                    <span>Lihat {{ $activeOsis->candidates->count() }} Paslon</span>
                </a>

                @auth
                    <a href="{{ route('osis.booth') }}" 
                       class="px-6 py-3 rounded-2xl bg-emerald-400 hover:bg-emerald-300 text-slate-950 text-xs font-black shadow-lg border-2 border-black transition hover:scale-105 flex items-center gap-2">
                        <i class="fas fa-box-archive"></i>
                        <span>Buka Bilik Suara</span>
                    </a>
                @else
                    <a href="{{ route('osis.booth') }}" 
                       class="px-6 py-3 rounded-2xl bg-emerald-400 hover:bg-emerald-300 text-slate-950 text-xs font-black shadow-lg border-2 border-black transition hover:scale-105 flex items-center gap-2">
                        <i class="fas fa-lock"></i>
                        <span>Masuk untuk Memilih</span>
                    </a>
                @endauth
            </div>
        </div>
    </div>
</section>
@endif
