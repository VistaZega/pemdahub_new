@php
    $campaign = $campaign ?? \App\Models\OsisElection::getActiveCampaignForUser();
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
                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($campaign->winnerCandidate->leader_name) }}&background=0f172a&color=fbbf24&bold=true&size=256';"
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
    <div style="background: linear-gradient(135deg, #ecfdf5 0%, #ffffff 55%); border: 1px solid #a7f3d0; border-top: 3px solid #059669; border-radius: 1.25rem; padding: 1.25rem; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);">

        @if($campaign->is_simulation)
            <div style="margin-bottom: 0.9rem; padding: 0.65rem 0.9rem; border-radius: 0.85rem; background: #fef3c7; border: 1px solid #fcd34d; color: #78350f; font-size: 0.74rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                <span>🧪</span>
                <span><strong>Mode Simulasi:</strong> Hak suara saat ini bersifat uji coba.</span>
            </div>
        @endif

        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 0.9rem;">
            <div style="display: flex; align-items: center; gap: 0.7rem; min-width: 0;">
                <div style="width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; background: #059669; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; box-shadow: 0 4px 10px rgba(5, 150, 105, 0.25);">
                    <i class="fas fa-vote-yea"></i>
                </div>
                <div style="min-width: 0;">
                    <div style="display: flex; align-items: center; gap: 0.45rem; flex-wrap: wrap;">
                        <span style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #047857; background: #d1fae5; padding: 0.15rem 0.5rem; border-radius: 9999px;">
                            E-Voting OSIS
                        </span>
                        <span style="font-size: 0.7rem; font-weight: 600; color: #64748b;">Periode {{ $campaign->period }}</span>
                    </div>
                    <h3 style="font-size: 0.92rem; font-weight: 800; color: #0f172a; margin-top: 0.15rem; line-height: 1.25;">{{ $campaign->title }}</h3>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                @if($campaign->status === 'voting_closed')
                    <a href="{{ route('osis.real-count', $campaign->slug) }}" style="padding: 0.5rem 0.9rem; border-radius: 0.75rem; background: #0f172a; color: #ffffff; font-size: 0.74rem; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                        <i class="fas fa-chart-pie" style="color: #34d399;"></i>
                        <span>Hasil Real Count</span>
                    </a>
                @elseif($hasVoted)
                    <span style="padding: 0.45rem 0.8rem; border-radius: 0.75rem; background: #d1fae5; color: #065f46; font-size: 0.72rem; font-weight: 800; display: inline-flex; align-items: center; gap: 0.35rem;">
                        <i class="fas fa-check-circle"></i> Sudah Memilih
                    </span>
                    <a href="{{ route('osis.booth') }}" style="padding: 0.45rem 0.8rem; border-radius: 0.75rem; background: #0f172a; color: #ffffff; font-size: 0.72rem; font-weight: 700; text-decoration: none;">
                        Bilik Suara
                    </a>
                @else
                    <a href="{{ route('osis.booth') }}" style="padding: 0.55rem 1rem; border-radius: 0.8rem; background: #059669; color: #ffffff; font-size: 0.75rem; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 0.45rem; box-shadow: 0 4px 10px rgba(5, 150, 105, 0.3);">
                        <i class="fas fa-check-to-slot"></i>
                        <span>Salurkan Hak Suara</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- Candidate Grid (2 Kolom Rapi dengan Avatar Fallback) --}}
        <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.65rem;">
            @foreach($campaign->candidates as $c)
                @php
                    $badgeHex = [
                        1 => '#e11d48',
                        2 => '#2563eb',
                        3 => '#d97706',
                        4 => '#059669',
                    ][$c->candidate_number] ?? '#0f172a';
                    $avatarFallback = 'https://ui-avatars.com/api/?name=' . urlencode($c->leader_name) . '&background=' . ltrim($badgeHex, '#') . '&color=ffffff&bold=true&size=128';
                    $photoSrc = !empty($c->photo_url) ? $c->photo_url : $avatarFallback;
                @endphp
                <a href="{{ route('osis.booth') }}" style="display: flex; align-items: center; gap: 0.65rem; padding: 0.6rem; border-radius: 0.9rem; background: #ffffff; border: 1px solid #e2e8f0; text-decoration: none; transition: border-color 0.15s ease;">
                    <div style="position: relative; width: 2.85rem; height: 2.85rem; border-radius: 0.7rem; overflow: hidden; flex-shrink: 0; background: #f1f5f9;">
                        <img src="{{ $photoSrc }}"
                             alt="{{ $c->leader_name }}"
                             onerror="this.onerror=null; this.src='{{ $avatarFallback }}';"
                             style="width: 100%; height: 100%; object-fit: cover;">
                        <span style="position: absolute; top: 2px; left: 2px; width: 1.15rem; height: 1.15rem; border-radius: 0.35rem; background: {{ $badgeHex }}; color: #ffffff; font-size: 0.62rem; font-weight: 900; display: flex; align-items: center; justify-content: center;">
                            {{ $c->candidate_number }}
                        </span>
                    </div>
                    <div style="min-width: 0; flex: 1;">
                        <div style="font-size: 0.62rem; font-weight: 800; color: {{ $badgeHex }}; text-transform: uppercase;">Paslon 0{{ $c->candidate_number }}</div>
                        <div style="font-size: 0.76rem; font-weight: 800; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $c->leader_name }}">
                            {{ $c->leader_name }}
                        </div>
                        <div style="font-size: 0.66rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            Sek: {{ $c->secretary_name ?? '-' }}
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

{{-- ========================================================================= --}}
{{-- KONDISI 3: VARIAN DASHBOARD GURU (Modern Clean Slate + Emerald Accent)    --}}
{{-- ========================================================================= --}}
@elseif($variant === 'teacher')
    <div class="rounded-[1.25rem] p-5 border border-emerald-200 border-t-[3px] border-t-emerald-600 shadow-sm relative overflow-hidden"
         style="background: linear-gradient(135deg, #ecfdf5 0%, #ffffff 55%, #f0fdfa 100%);">

        @if($campaign->is_simulation)
            <div class="mb-4 px-3.5 py-2.5 rounded-xl bg-amber-50 border border-amber-300 text-amber-900 text-xs flex items-start sm:items-center gap-2.5">
                <span class="text-base shrink-0">🧪</span>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-extrabold uppercase tracking-wider">Mode Uji Coba / Simulasi E-Voting</span>
                        <span class="px-2 py-0.5 rounded-md bg-amber-200/80 text-amber-950 font-mono text-[10px] uppercase font-bold">Tahap Uji Coba</span>
                    </div>
                    <p class="text-[11px] font-medium text-amber-800 leading-snug mt-0.5">
                        Bapak/Ibu Guru dapat mencoba alur bilik suara digital. Hak suara saat ini bersifat simulasi dan akan di-reset bersih saat Pemilihan OSIS Resmi sekolah dibuka.
                    </p>
                </div>
            </div>
        @endif

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-emerald-100">
            <div class="flex items-start sm:items-center gap-3.5 min-w-0">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-600 text-white flex items-center justify-center text-xl shadow-sm shrink-0">
                    <i class="fas fa-vote-yea"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-100 border border-emerald-200 text-emerald-800 text-[10px] font-extrabold uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                            SUKSESI OSIS {{ $campaign->school->name ?? 'UNIT SEKOLAH' }}
                        </span>
                        <span class="text-xs font-semibold text-slate-500">Periode {{ $campaign->period }}</span>
                    </div>
                    <h3 class="text-base md:text-lg font-extrabold tracking-tight text-slate-900 leading-snug">
                        {{ $campaign->title }}
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-600 mt-0.5">
                        Bapak/Ibu Guru &amp; Tendik memiliki 1 hak suara sah. Mari kenali paslon dan salurkan aspirasi demokrasi Anda.
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                {{-- Turnout Stat Chip --}}
                <div class="bg-white px-3.5 py-2 rounded-xl border border-emerald-200 text-center shadow-2xs">
                    <span class="text-[10px] uppercase font-bold text-slate-500 block leading-none mb-0.5">Partisipasi DPT</span>
                    <span class="text-sm font-extrabold text-emerald-700 font-mono leading-none">{{ $campaign->turnout_percentage }}%</span>
                </div>

                @if($campaign->status === 'voting_closed')
                    <span class="px-3 py-2 rounded-xl bg-amber-50 text-amber-800 text-xs font-bold border border-amber-200 inline-flex items-center gap-1.5">
                        <i class="fas fa-lock text-amber-600"></i>
                        <span>Pemungutan Ditutup</span>
                    </span>
                    <a href="{{ route('osis.real-count', $campaign->slug) }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold shadow-sm transition inline-flex items-center gap-1.5">
                        <i class="fas fa-chart-pie"></i>
                        <span>Pantau Real Count</span>
                    </a>
                @elseif($hasVoted)
                    <span class="px-3.5 py-2 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-bold border border-emerald-200 inline-flex items-center gap-1.5">
                        <i class="fas fa-check-double text-emerald-600"></i>
                        <span>Suara Sah Tercatat</span>
                    </span>
                    <a href="{{ route('osis.booth') }}" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs sm:text-sm font-bold shadow-sm transition inline-flex items-center gap-1.5">
                        <span>Bilik Suara</span>
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                @else
                    <a href="{{ route('osis.booth') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold shadow-sm transition inline-flex items-center gap-2">
                        <i class="fas fa-check-to-slot"></i>
                        <span>Buka Bilik Suara &amp; Nyoblos</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- Mini Candidate Strip untuk Guru --}}
        @if($campaign->candidates->isNotEmpty())
            <div class="pt-3.5">
                <div class="flex items-center justify-between gap-2 mb-2.5">
                    <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <i class="fas fa-users text-emerald-600"></i>
                        <span>Pasangan Calon ({{ $campaign->candidates->count() }} Paslon)</span>
                    </span>
                    <a href="{{ route('osis.booth') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 transition inline-flex items-center gap-1">
                        <span>Lihat Visi Misi Lengkap</span>
                        <i class="fas fa-chevron-right text-[10px]"></i>
                    </a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    @foreach($campaign->candidates as $c)
                        @php
                            $badgeHex = [
                                1 => '#e11d48',
                                2 => '#2563eb',
                                3 => '#d97706',
                                4 => '#059669',
                            ][$c->candidate_number] ?? '#0f172a';
                            $avatarFallback = 'https://ui-avatars.com/api/?name=' . urlencode($c->leader_name) . '&background=' . ltrim($badgeHex, '#') . '&color=ffffff&bold=true&size=128';
                            $photoSrc = !empty($c->photo_url) ? $c->photo_url : $avatarFallback;
                        @endphp
                        <a href="{{ route('osis.booth') }}" class="flex items-center gap-3 p-3 rounded-xl bg-white border border-slate-200 hover:border-emerald-400 hover:shadow-md transition-all group">
                            <div class="relative shrink-0">
                                <img src="{{ $photoSrc }}"
                                     alt="{{ $c->leader_name }}"
                                     onerror="this.onerror=null; this.src='{{ $avatarFallback }}';"
                                     class="w-12 h-12 rounded-xl object-cover border border-slate-200 bg-slate-100">
                                <span class="absolute -top-1.5 -left-1.5 w-5 h-5 rounded-md text-[10px] font-black text-white flex items-center justify-center shadow-xs ring-2 ring-white"
                                      style="background: {{ $badgeHex }};">
                                    {{ $c->candidate_number }}
                                </span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider block leading-none mb-1" style="color: {{ $badgeHex }};">
                                    Paslon 0{{ $c->candidate_number }}
                                </span>
                                <h4 class="text-xs font-extrabold text-slate-900 truncate leading-tight group-hover:text-emerald-700 transition" title="{{ $c->leader_name }}">
                                    {{ $c->leader_name }}
                                </h4>
                                <p class="text-[11px] text-slate-500 truncate mt-0.5" title="Sekretaris: {{ $c->secretary_name ?? '-' }}">
                                    Sek: {{ $c->secretary_name ?? '-' }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

{{-- ========================================================================= --}}
{{-- KONDISI 4: VARIAN PEMBDASPACE (Pinned Spotlight Bar di Feed Komunitas)     --}}
{{-- ========================================================================= --}}
@elseif($variant === 'space')
    <div class="mb-5 block p-4 bg-gradient-to-r from-emerald-50 via-teal-50 to-cyan-50 border-2 border-[#121316] rounded-2xl shadow-[3px_3px_0px_#121316] relative shrink-0 w-full group" style="min-height: fit-content;">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200/80 mb-3">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-black text-xs flex items-center justify-center shadow shrink-0">
                    🗳️
                </span>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($campaign->is_simulation)
                            <span class="text-[10px] font-black uppercase tracking-wider text-amber-900 bg-amber-300 px-2 py-0.5 rounded border border-black shadow-2xs">
                                🧪 Simulasi E-Voting
                            </span>
                        @else
                            <span class="text-[10px] font-black uppercase tracking-wider text-emerald-800 bg-emerald-200/60 px-2 py-0.5 rounded border border-emerald-300">
                                Spotlight Demokrasi Sekolah
                            </span>
                        @endif
                        <span class="text-[11px] font-mono text-slate-600 font-bold">OSIS {{ $campaign->period }}</span>
                    </div>
                    <h3 class="text-sm font-black text-slate-900 leading-tight mt-0.5 truncate">{{ $campaign->title }}</h3>
                </div>
            </div>

            <a href="{{ route('osis.booth') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-[#121316] hover:bg-slate-800 text-white text-xs font-black shadow transition active:scale-95 shrink-0 self-start sm:self-center">
                <i class="fas fa-vote-yea text-emerald-400"></i>
                <span>Bilik Suara & Kenali Paslon</span>
                <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        {{-- Mini Slider Paslon di Feed Space --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
            @foreach($campaign->candidates as $c)
                @php
                    $spaceBg = [
                        1 => 'e11d48',
                        2 => '2563eb',
                        3 => 'd97706',
                        4 => '059669',
                    ][$c->candidate_number] ?? '0f172a';
                @endphp
                <a href="{{ route('osis.public.slug', $campaign->slug) }}" class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-300 hover:border-[#121316] transition shadow-xs group/p">
                    <img src="{{ $c->photo_url }}" alt="{{ $c->leader_name }}" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($c->leader_name) }}&background={{ $spaceBg }}&color=ffffff&bold=true&size=128';" class="w-10 h-10 rounded-lg object-cover flex-shrink-0 border border-slate-200">
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
