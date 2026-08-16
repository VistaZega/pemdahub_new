@php
    $currentRoute = request()->route()->getName();
    $currentGroup = $group ?? 'siswa';
    $currentSchoolId = $schoolId ?? null;
    $currentDate = $date ?? \Carbon\Carbon::now('Asia/Jakarta')->toDateString();
@endphp

{{-- Google Fonts for Luxury Aesthetic --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700;900&family=Playfair+Display:ital,wght@0,500;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    .luxury-glass-panel {
        background: rgba(11, 17, 29, 0.75) !important;
        backdrop-filter: blur(28px) !important;
        -webkit-backdrop-filter: blur(28px) !important;
        border: 1px solid rgba(212, 175, 55, 0.25) !important;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.75), inset 0 1px 1px rgba(255, 255, 255, 0.12) !important;
        position: relative;
        overflow: hidden;
    }
    .luxury-glass-panel::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), #d4af37, rgba(255, 255, 255, 0.3), transparent);
        opacity: 0.7;
    }
    .gold-gradient-text {
        background: linear-gradient(135deg, #f6e6b4 0%, #d4af37 50%, #aa841e 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .gold-pill-luxury {
        background: rgba(212, 175, 55, 0.1);
        border: 1px solid rgba(212, 175, 55, 0.35);
        color: #f6e6b4;
    }
    .gold-pill-luxury:hover, .gold-pill-luxury.active {
        background: linear-gradient(135deg, #f6e6b4 0%, #d4af37 50%, #997819 100%);
        color: #05070a;
        box-shadow: 0 0 25px rgba(212, 175, 55, 0.4);
    }
</style>

<div class="space-y-6 mb-8">
    {{-- ═══════════════════════════════════════════════ --}}
    {{-- HAUTE ACADEMIC HERO BANNER (LIQUID GLASS SUITE) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="luxury-glass-panel rounded-[2.5rem] p-7 md:p-9 text-white">
        {{-- Liquid Glass Ambient Refraction Blobs --}}
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 rounded-full bg-amber-500/10 blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex items-start md:items-center">
                {{-- Masterpiece Gold Crest --}}
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-200 via-amber-400 to-yellow-600 text-black flex items-center justify-center text-2xl font-black shadow-xl shrink-0 mr-5 border border-white/40">
                    <i class="fas fa-crown text-black"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 mb-2 flex-wrap">
                        <span class="px-3.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-[0.25em] gold-pill-luxury shadow-xs flex items-center gap-1.5">
                            <i class="fas fa-gem text-amber-300"></i> Royal Academic Suite
                        </span>
                        <span class="px-3.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-[0.2em] bg-emerald-500/10 border border-emerald-400/30 text-emerald-300 shadow-xs flex items-center gap-1.5">
                            <i class="fas fa-shield-halved text-emerald-400"></i> Cryptographic Verified
                        </span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-serif font-normal tracking-tight text-white leading-tight">
                        Presensi <span class="gold-gradient-text italic font-semibold">{{ ucfirst($currentGroup) }}</span> &mdash; {{ $selectedSchool->name ?? 'Perguruan Pembda Nias' }}
                    </h1>
                    <p class="text-xs md:text-sm font-light mt-1.5 text-slate-300 leading-relaxed max-w-xl">
                        Haute precision GPS & RFID verification, bespoke attendance governance, and immutable registry.
                    </p>
                </div>
            </div>

            {{-- Controls: Unit & Date Filter --}}
            <div class="flex flex-wrap items-center gap-3.5 shrink-0">
                @if($isSuperAdmin && isset($schools) && $schools->count() > 1)
                <div class="relative min-w-[230px]">
                    <select onchange="changeAttendanceSchool(this.value)"
                            class="w-full bg-[#0d1424]/80 hover:bg-[#131d33] border border-amber-400/40 rounded-2xl px-5 py-3 text-xs font-bold text-amber-200 focus:ring-2 focus:ring-amber-400 transition cursor-pointer appearance-none pr-10 shadow-lg backdrop-blur-md">
                        @foreach($schools as $sch)
                            <option value="{{ $sch->id }}" {{ $currentSchoolId == $sch->id ? 'selected' : '' }} class="bg-[#0b101c] text-white">
                                🏛️ {{ $sch->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-amber-400 text-xs">
                        <i class="fas fa-chevron-down font-bold"></i>
                    </div>
                </div>
                @endif

                <div class="inline-flex items-center gap-2.5 bg-gradient-to-r from-amber-400/20 to-yellow-500/10 text-amber-200 border border-amber-400/40 rounded-2xl px-5 py-2.5 shadow-lg backdrop-blur-md">
                    <i class="fas fa-calendar-alt text-amber-400 text-sm"></i>
                    <input type="date" value="{{ $currentDate }}" onchange="changeAttendanceDate(this.value)"
                           class="bg-transparent border-none text-xs font-bold text-amber-200 focus:ring-0 p-0 cursor-pointer">
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- LIQUID GLASS NAVIGATION BAR: KELOMPOK & SUB-MENU --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="luxury-glass-panel rounded-[2rem] p-5 shadow-2xl flex flex-col lg:flex-row lg:items-center justify-between gap-5">
        {{-- Kelompok Switcher --}}
        <div>
            <span class="block text-[10px] font-bold text-amber-300/70 uppercase tracking-[0.25em] mb-2">
                Pilih Kelompok Eksklusif:
            </span>
            <div class="flex flex-wrap items-center gap-2.5">
                @if(!$isYayasan)
                <a href="{{ request()->fullUrlWithQuery(['group' => 'siswa']) }}" 
                   class="inline-flex items-center gap-2.5 px-5 py-2.5 rounded-full text-xs font-bold transition-all
                          {{ $currentGroup === 'siswa' ? 'bg-gradient-to-r from-amber-200 via-amber-400 to-yellow-600 text-black shadow-lg scale-[1.02] border border-white/60 font-black' : 'bg-white/5 text-slate-300 hover:bg-amber-400/15 border border-white/10 hover:border-amber-400/40' }}">
                    <i class="fas fa-user-graduate text-xs"></i>
                    <span>Siswa</span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['group' => 'guru']) }}" 
                   class="inline-flex items-center gap-2.5 px-5 py-2.5 rounded-full text-xs font-bold transition-all
                          {{ $currentGroup === 'guru' ? 'bg-gradient-to-r from-amber-200 via-amber-400 to-yellow-600 text-black shadow-lg scale-[1.02] border border-white/60 font-black' : 'bg-white/5 text-slate-300 hover:bg-amber-400/15 border border-white/10 hover:border-amber-400/40' }}">
                    <i class="fas fa-chalkboard-teacher text-xs"></i>
                    <span>Guru</span>
                </a>
                @endif
                <a href="{{ request()->fullUrlWithQuery(['group' => 'pegawai']) }}" 
                   class="inline-flex items-center gap-2.5 px-5 py-2.5 rounded-full text-xs font-bold transition-all
                          {{ $currentGroup === 'pegawai' ? 'bg-gradient-to-r from-amber-200 via-amber-400 to-yellow-600 text-black shadow-lg scale-[1.02] border border-white/60 font-black' : 'bg-white/5 text-slate-300 hover:bg-amber-400/15 border border-white/10 hover:border-amber-400/40' }}">
                    <i class="fas fa-user-tie text-xs"></i>
                    <span>Pegawai / Staf</span>
                </a>
            </div>
        </div>

        {{-- 4 Sub-Menu Navigation Tabs --}}
        <div>
            <span class="block text-[10px] font-bold text-amber-300/70 uppercase tracking-[0.25em] mb-2 lg:text-right">
                Pilihan Modul Terpadu:
            </span>
            <div class="flex flex-wrap items-center gap-2.5">
                {{-- 1. Live Feed --}}
                <a href="{{ route('admin.attendance.live', ['group' => $currentGroup, 'school_id' => $currentSchoolId, 'date' => $currentDate]) }}"
                   class="inline-flex items-center gap-2.5 px-5 py-2.5 rounded-full text-xs font-bold transition-all
                          {{ str_contains($currentRoute, 'live') ? 'bg-rose-500/90 text-white shadow-lg shadow-rose-500/30 border border-rose-300/50 scale-[1.02]' : 'bg-white/5 text-slate-300 hover:bg-amber-400/15 border border-white/10 hover:border-amber-400/40' }}">
                    <span class="w-2 h-2 rounded-full {{ str_contains($currentRoute, 'live') ? 'bg-white animate-ping' : 'bg-rose-500' }}"></span>
                    <span>Live Feed</span>
                </a>

                {{-- 2. Monitoring Harian --}}
                <a href="{{ route('admin.attendance.monitoring', ['group' => $currentGroup, 'school_id' => $currentSchoolId, 'date' => $currentDate]) }}"
                   class="inline-flex items-center gap-2.5 px-5 py-2.5 rounded-full text-xs font-bold transition-all
                          {{ str_contains($currentRoute, 'monitoring') || $currentRoute === 'admin.attendance.index' ? 'bg-gradient-to-r from-amber-200 via-amber-400 to-yellow-600 text-black shadow-lg scale-[1.02] border border-white/60 font-black' : 'bg-white/5 text-slate-300 hover:bg-amber-400/15 border border-white/10 hover:border-amber-400/40' }}">
                    <i class="fas fa-chart-line text-xs"></i>
                    <span>Monitoring Harian</span>
                </a>

                {{-- 3. Input Massal --}}
                <a href="{{ route('admin.attendance.bulk', ['group' => $currentGroup, 'school_id' => $currentSchoolId, 'date' => $currentDate]) }}"
                   class="inline-flex items-center gap-2.5 px-5 py-2.5 rounded-full text-xs font-bold transition-all
                          {{ str_contains($currentRoute, 'bulk') ? 'bg-gradient-to-r from-amber-200 via-amber-400 to-yellow-600 text-black shadow-lg scale-[1.02] border border-white/60 font-black' : 'bg-white/5 text-slate-300 hover:bg-amber-400/15 border border-white/10 hover:border-amber-400/40' }}">
                    <i class="fas fa-table text-xs"></i>
                    <span>Input Massal</span>
                </a>

                {{-- 4. Rekap & Laporan --}}
                <a href="{{ route('admin.attendance.rekap', ['group' => $currentGroup, 'school_id' => $currentSchoolId]) }}"
                   class="inline-flex items-center gap-2.5 px-5 py-2.5 rounded-full text-xs font-bold transition-all
                          {{ str_contains($currentRoute, 'rekap') ? 'bg-gradient-to-r from-amber-200 via-amber-400 to-yellow-600 text-black shadow-lg scale-[1.02] border border-white/60 font-black' : 'bg-white/5 text-slate-300 hover:bg-amber-400/15 border border-white/10 hover:border-amber-400/40' }}">
                    <i class="fas fa-file-invoice text-xs"></i>
                    <span>Rekap & Laporan</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function changeAttendanceSchool(schoolId) {
    const url = new URL(window.location.href);
    url.searchParams.set('school_id', schoolId);
    window.location.href = url.toString();
}

function changeAttendanceDate(dateVal) {
    const url = new URL(window.location.href);
    url.searchParams.set('date', dateVal);
    window.location.href = url.toString();
}
</script>
