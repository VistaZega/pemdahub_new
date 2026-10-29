@php
    $campaign = \App\Models\OsisElection::getActiveCampaignForUser();
    if (!$campaign) {
        return;
    }

    $currentUser = auth()->user();
    $hasVoted = false;
    if ($currentUser) {
        $hasVoted = \App\Models\OsisVoter::where('election_id', $campaign->id)
            ->where('user_id', $currentUser->id)
            ->where('has_voted', true)
            ->exists();
    }
    $variant = $variant ?? 'student'; // 'student', 'teacher', 'space'
@endphp

{{-- ========================================================================= --}}
{{-- KONDISI 1: SUDAH SELESAI & ADA PEMENANG TERPILIH (GRACE PERIOD 3 HARI)    --}}
{{-- ========================================================================= --}}
@if($campaign->isCompleted() && $campaign->winnerCandidate)
    <div class="rounded-3xl p-5 md:p-6 border-2 border-slate-900 shadow-xl relative overflow-hidden transition hover:shadow-2xl mb-6 text-slate-950"
         style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 50%, #d97706 100%);">
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-5">
            <div class="flex items-center gap-4 text-center md:text-left flex-col sm:flex-row">
                <div class="relative flex-shrink-0">
                    <img src="{{ $campaign->winnerCandidate->photo_url }}" 
                         alt="{{ $campaign->winnerCandidate->leader_name }}" 
                         class="w-20 h-20 md:w-24 md:h-24 rounded-2xl object-cover border-3 border-slate-900 shadow-md">
                    <span class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-slate-900 text-amber-300 text-xs flex items-center justify-center border border-white shadow">👑</span>
                </div>
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-slate-900 text-amber-300 text-[10px] font-black uppercase tracking-wider">
                        👑 PENGURUS OSIS TERPILIH PERIODE {{ $campaign->period }}
                    </div>
                    <h3 class="text-lg md:text-xl font-black uppercase text-slate-950 tracking-tight">
                        PASLON 0{{ $campaign->winnerCandidate->candidate_number }} &mdash; {{ $campaign->winnerCandidate->leader_name }}
                    </h3>
                    <div class="text-xs font-bold text-slate-900 flex flex-wrap items-center justify-center md:justify-start gap-2">
                        <span>Sek: <strong>{{ $campaign->winnerCandidate->secretary_name ?? '-' }}</strong></span>
                        &bull;
                        <span>Ben: <strong>{{ $campaign->winnerCandidate->treasurer_name ?? '-' }}</strong></span>
                        &bull;
                        <span class="bg-slate-900 text-amber-300 px-2 py-0.5 rounded-md text-[10px] font-black">
                            ⭐ Peraih +150 Poin Prestasi
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('osis.real-count', $campaign->slug) }}" 
                   class="px-4 py-2.5 rounded-xl bg-slate-900 text-white font-black text-xs hover:bg-slate-800 transition shadow border border-black/20 flex items-center gap-1.5">
                    <i class="fas fa-chart-pie text-emerald-400"></i>
                    <span>Hasil Lengkap</span>
                </a>
            </div>
        </div>
    </div>

{{-- ========================================================================= --}}
{{-- KONDISI 2: VARIAN DASHBOARD SISWA (Bento Story & Paslon Mini-Cards)      --}}
{{-- ========================================================================= --}}
@elseif($variant === 'student')
    <div class="bg-white rounded-3xl border-2 border-slate-900 shadow-xl p-5 md:p-6 mb-6 relative overflow-hidden">
        {{-- Rainbow Accent Bar --}}
        <div class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-rose-500 via-amber-500 via-emerald-500 to-blue-600"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-800 border-2 border-emerald-300 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fas fa-vote-yea"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 border border-emerald-300">
                            E-Voting Kesiswaan
                        </span>
                        <span class="text-[11px] font-mono font-bold text-slate-500">Periode {{ $campaign->period }}</span>
                    </div>
                    <h3 class="text-base font-black text-slate-900 mt-0.5">{{ $campaign->title }}</h3>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if($hasVoted)
                    <span class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-800 font-black text-xs border border-emerald-300 flex items-center gap-1.5">
                        <i class="fas fa-check-double text-emerald-600"></i> Suara Anda Sah Tercatat
                    </span>
                    <a href="{{ route('osis.booth') }}" class="px-3.5 py-1.5 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition">
                        Bilik Suara
                    </a>
                @else
                    <a href="{{ route('osis.booth') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow border-2 border-slate-900 transition flex items-center gap-1.5 animate-pulse">
                        <i class="fas fa-check-to-slot"></i> Salurkan Hak Suara Sekarang
                    </a>
                @endif
            </div>
        </div>

        {{-- Mini Candidate Strip (Scrollable/Flex) --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
            @foreach($campaign->candidates as $c)
                @php
                    $colors = [
                        1 => 'bg-rose-50 border-rose-300 text-rose-700',
                        2 => 'bg-blue-50 border-blue-300 text-blue-700',
                        3 => 'bg-amber-50 border-amber-300 text-amber-700',
                        4 => 'bg-emerald-50 border-emerald-300 text-emerald-700',
                    ];
                    $badgeBg = [
                        1 => 'bg-rose-600',
                        2 => 'bg-blue-600',
                        3 => 'bg-amber-500',
                        4 => 'bg-emerald-600',
                    ][$c->candidate_number] ?? 'bg-slate-900';
                @endphp
                <a href="{{ route('osis.booth') }}" class="group block rounded-2xl p-2.5 border-2 border-slate-900 bg-slate-50/50 hover:bg-white transition hover:-translate-y-1 hover:shadow-md">
                    <div class="relative rounded-xl overflow-hidden mb-2 bg-slate-200" style="aspect-ratio: 4/3;">
                        <img src="{{ $c->photo_url }}" alt="{{ $c->leader_name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        <span class="absolute top-1 left-1 w-6 h-6 rounded-lg {{ $badgeBg }} text-white font-black text-[10px] flex items-center justify-center shadow">
                            {{ $c->candidate_number }}
                        </span>
                    </div>
                    <div class="space-y-0.5">
                        <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 block leading-none">Paslon 0{{ $c->candidate_number }}</span>
                        <h4 class="text-xs font-black text-slate-900 truncate leading-tight group-hover:text-emerald-700 transition" title="{{ $c->leader_name }}">
                            {{ $c->leader_name }}
                        </h4>
                        <p class="text-[10px] text-slate-500 truncate">
                            Sek: {{ $c->secretary_name ?? '-' }}
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

{{-- ========================================================================= --}}
{{-- KONDISI 3: VARIAN DASHBOARD GURU (Neo-Brutalism Hijau Emerald)            --}}
{{-- ========================================================================= --}}
@elseif($variant === 'teacher')
    <div class="rounded-3xl p-5 md:p-6 border-2 border-black shadow-xl mb-6 relative overflow-hidden"
         style="background: linear-gradient(135deg, #090d16 0%, #064e3b 50%, #047857 100%); color: #ffffff;">
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-5">
            <div class="flex items-center gap-4 text-center md:text-left flex-col sm:flex-row">
                <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-2xl text-emerald-300 shadow-md flex-shrink-0">
                    <i class="fas fa-box-archive"></i>
                </div>
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-0.5 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-[10px] font-black uppercase tracking-wider mb-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        SUKSESI OSIS {{ $campaign->school->name ?? 'UNIT SEKOLAH' }}
                    </div>
                    <h3 class="text-base md:text-lg font-black uppercase tracking-tight text-white">
                        {{ $campaign->title }} &bull; Periode {{ $campaign->period }}
                    </h3>
                    <p class="text-xs text-slate-200 mt-0.5">
                        Bapak/Ibu Guru & Tendik memiliki 1 hak suara sah. Mari berpartisipasi dan pantau suksesi kepemimpinan siswa.
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-3 flex-shrink-0">
                {{-- Turnout Stat Chip --}}
                <div class="bg-black/40 backdrop-blur-sm px-3.5 py-2 rounded-2xl border border-white/10 text-center">
                    <span class="text-[9px] uppercase font-bold text-slate-300 block">Partisipasi DPT</span>
                    <span class="text-sm font-black text-emerald-400 font-mono">{{ $campaign->turnout_percentage }}%</span>
                </div>

                @if($hasVoted)
                    <a href="{{ route('osis.booth') }}" class="px-4 py-2.5 rounded-2xl bg-white/20 hover:bg-white/30 text-white text-xs font-bold border border-white/30 transition flex items-center gap-1.5">
                        <i class="fas fa-check-double text-emerald-300"></i>
                        <span>Suara Tercatat</span>
                    </a>
                @else
                    <a href="{{ route('osis.booth') }}" class="px-5 py-2.5 rounded-2xl bg-emerald-400 hover:bg-emerald-300 text-slate-950 text-xs font-black shadow-lg border-2 border-black transition hover:scale-105 flex items-center gap-1.5">
                        <i class="fas fa-check-to-slot"></i>
                        <span>Buka Bilik Suara</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

{{-- ========================================================================= --}}
{{-- KONDISI 4: VARIAN PEMBDASPACE (Pinned Spotlight Bar di Feed Komunitas)     --}}
{{-- ========================================================================= --}}
@elseif($variant === 'space')
    <div class="mb-5 block p-4 bg-gradient-to-r from-emerald-50 via-teal-50 to-cyan-50 border-2 border-[#121316] rounded-2xl shadow-[3px_3px_0px_#121316] relative overflow-hidden group">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200/80 mb-3">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-black text-xs flex items-center justify-center shadow">
                    🗳️
                </span>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-emerald-800 bg-emerald-200/60 px-2 py-0.5 rounded border border-emerald-300">
                            Spotlight Demokrasi Sekolah
                        </span>
                        <span class="text-[11px] font-mono text-slate-600 font-bold">OSIS {{ $campaign->period }}</span>
                    </div>
                    <h3 class="text-sm font-black text-slate-900 leading-tight mt-0.5">{{ $campaign->title }}</h3>
                </div>
            </div>

            <a href="{{ route('osis.booth') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-[#121316] hover:bg-slate-800 text-white text-xs font-black shadow transition active:scale-95 flex-shrink-0">
                <i class="fas fa-vote-yea text-emerald-400"></i>
                <span>Bilik Suara & Kenali Paslon</span>
                <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        {{-- Mini Slider Paslon di Feed Space --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
            @foreach($campaign->candidates as $c)
                <a href="{{ route('osis.public.slug', $campaign->slug) }}" class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-300 hover:border-[#121316] transition shadow-xs group/p">
                    <img src="{{ $c->photo_url }}" alt="{{ $c->leader_name }}" class="w-10 h-10 rounded-lg object-cover flex-shrink-0 border border-slate-200">
                    <div class="min-w-0 flex-1">
                        <span class="text-[9px] font-black text-slate-400 block leading-none">NO. 0{{ $c->candidate_number }}</span>
                        <h4 class="text-xs font-black text-slate-900 truncate leading-tight mt-0.5 group-hover/p:text-emerald-700 transition">
                            {{ $c->leader_name }}
                        </h4>
                        <span class="text-[9px] text-slate-500 truncate block">Ketua OSIS</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endif
