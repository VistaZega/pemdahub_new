@php
    $currentRoute = request()->route()->getName();
    $currentGroup = $group ?? 'siswa';
    $currentSchoolId = $schoolId ?? null;
    $currentDate = $date ?? \Carbon\Carbon::now('Asia/Jakarta')->toDateString();
@endphp

<div class="space-y-6 mb-8">
    {{-- ═══════════════════════════════════════════════ --}}
    {{-- HERO BANNER LMS STYLE --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="relative bg-white rounded-3xl p-6 md:p-8 overflow-hidden shadow-xl border-2 border-black">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <div class="flex items-center gap-3.5 mb-3">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-md border-2 border-black shrink-0" style="background-color: #090d16 !important; color: #ffffff !important;">
                        <i class="fas fa-clipboard-check text-2xl text-amber-400"></i>
                    </div>
                    <div>
                        <p class="text-black text-xs font-black uppercase tracking-[0.2em]">Pusat Absensi & Presensi Terpadu</p>
                        <h1 class="text-2xl md:text-3xl font-black text-black tracking-tight">Presensi {{ ucfirst($currentGroup) }} — Perguruan Pembda</h1>
                    </div>
                </div>
                <p class="text-black font-bold text-sm max-w-xl leading-relaxed">
                    Sistem pemantauan presensi real-time, input massal cerdas, dan rekapitulasi data kehadiran terintegrasi untuk Siswa, Guru, dan Pegawai.
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <div class="inline-flex items-center gap-2 border-2 border-black rounded-xl px-4 py-2 shadow-sm font-black text-xs bg-amber-300 text-black">
                        <i class="fas fa-school text-black text-sm"></i>
                        <span>{{ $selectedSchool->name ?? 'Perguruan Pembda' }}</span>
                    </div>
                    <div class="inline-flex items-center gap-2 border-2 border-black rounded-xl px-4 py-2 shadow-sm font-black text-xs bg-black text-amber-400">
                        <i class="fas fa-calendar-day text-amber-400 text-sm"></i>
                        <span>{{ \Carbon\Carbon::parse($currentDate)->translatedFormat('l, d F Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Controls: Unit Selector & Date --}}
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0">
                @if($isSuperAdmin && isset($schools) && $schools->count() > 1)
                <div class="relative min-w-[220px]">
                    <select onchange="changeAttendanceSchool(this.value)"
                            class="w-full bg-white hover:bg-amber-50 border-2 border-black rounded-2xl px-4 py-3 text-xs font-black text-black focus:ring-2 focus:ring-amber-400 transition cursor-pointer appearance-none pr-9 shadow-md">
                        @foreach($schools as $sch)
                            <option value="{{ $sch->id }}" {{ $currentSchoolId == $sch->id ? 'selected' : '' }}>
                                🏫 {{ $sch->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-black text-xs">
                        <i class="fas fa-chevron-down font-black"></i>
                    </div>
                </div>
                @endif

                <div class="flex items-center bg-white border-2 border-black rounded-2xl px-4 py-2.5 shadow-md">
                    <i class="fas fa-calendar-alt text-black text-sm mr-2.5"></i>
                    <input type="date" value="{{ $currentDate }}" onchange="changeAttendanceDate(this.value)"
                           class="bg-transparent border-none text-xs font-black text-black focus:ring-0 p-0 cursor-pointer">
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- NAVIGATION CONTROLS: KELOMPOK & SUB-MENU TABS --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="bg-white rounded-3xl border-2 border-black p-4 shadow-md flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        {{-- Kelompok Switcher (Siswa, Guru, Pegawai) --}}
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="text-[11px] font-black text-black uppercase tracking-wider">Pilih Kelompok:</span>
            </div>
            <div class="flex flex-wrap gap-2">
                @if(!$isYayasan)
                <a href="{{ request()->fullUrlWithQuery(['group' => 'siswa']) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-black border-2 border-black transition-all shadow-sm
                          {{ $currentGroup === 'siswa' ? 'bg-black text-amber-400' : 'bg-white text-black hover:bg-amber-300' }}">
                    <i class="fas fa-user-graduate text-xs"></i>
                    <span>Siswa</span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['group' => 'guru']) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-black border-2 border-black transition-all shadow-sm
                          {{ $currentGroup === 'guru' ? 'bg-black text-amber-400' : 'bg-white text-black hover:bg-amber-300' }}">
                    <i class="fas fa-chalkboard-teacher text-xs"></i>
                    <span>Guru</span>
                </a>
                @endif
                <a href="{{ request()->fullUrlWithQuery(['group' => 'pegawai']) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-black border-2 border-black transition-all shadow-sm
                          {{ $currentGroup === 'pegawai' ? 'bg-black text-amber-400' : 'bg-white text-black hover:bg-amber-300' }}">
                    <i class="fas fa-user-tie text-xs"></i>
                    <span>Pegawai / Staf</span>
                </a>
            </div>
        </div>

        {{-- 4 Sub-Menu Navigation Tabs --}}
        <div>
            <div class="flex items-center gap-2 mb-2 lg:justify-end">
                <span class="text-[11px] font-black text-black uppercase tracking-wider">Modul Absensi:</span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                {{-- 1. Live Feed --}}
                <a href="{{ route('admin.attendance.live', ['group' => $currentGroup, 'school_id' => $currentSchoolId, 'date' => $currentDate]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-black border-2 border-black transition-all shadow-sm
                          {{ str_contains($currentRoute, 'live') ? 'bg-rose-500 text-white shadow-md' : 'bg-white text-black hover:bg-amber-300' }}">
                    <span class="w-2.5 h-2.5 rounded-full {{ str_contains($currentRoute, 'live') ? 'bg-white animate-ping' : 'bg-rose-500' }}"></span>
                    <span>Live Feed</span>
                </a>

                {{-- 2. Monitoring Harian --}}
                <a href="{{ route('admin.attendance.monitoring', ['group' => $currentGroup, 'school_id' => $currentSchoolId, 'date' => $currentDate]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-black border-2 border-black transition-all shadow-sm
                          {{ str_contains($currentRoute, 'monitoring') || $currentRoute === 'admin.attendance.index' ? 'bg-black text-amber-400 shadow-md' : 'bg-white text-black hover:bg-amber-300' }}">
                    <i class="fas fa-chart-line text-xs"></i>
                    <span>Monitoring Harian</span>
                </a>

                {{-- 3. Input Massal --}}
                <a href="{{ route('admin.attendance.bulk', ['group' => $currentGroup, 'school_id' => $currentSchoolId, 'date' => $currentDate]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-black border-2 border-black transition-all shadow-sm
                          {{ str_contains($currentRoute, 'bulk') ? 'bg-black text-amber-400 shadow-md' : 'bg-white text-black hover:bg-amber-300' }}">
                    <i class="fas fa-table text-xs"></i>
                    <span>Input Massal</span>
                </a>

                {{-- 4. Rekap & Laporan --}}
                <a href="{{ route('admin.attendance.rekap', ['group' => $currentGroup, 'school_id' => $currentSchoolId]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-black border-2 border-black transition-all shadow-sm
                          {{ str_contains($currentRoute, 'rekap') ? 'bg-black text-amber-400 shadow-md' : 'bg-white text-black hover:bg-amber-300' }}">
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
