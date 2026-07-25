@extends('layouts.yayasan')

@section('title', 'Progress Input Data - Ketua Yayasan')

@section('content')
<div class="space-y-6">
    {{-- Header Banner --}}
    <div class="bg-gradient-to-r from-violet-600 via-purple-600 to-indigo-700 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10 text-9xl">
            <i class="fas fa-tasks"></i>
        </div>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 relative z-10">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-2xl">📊</span>
                    <span class="bg-white/20 px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider">Monitoring Yayasan</span>
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight">Progress Input Data & Kesiapan Akademik</h1>
                <p class="text-white/80 text-sm mt-1">Rekapitulasi rinci 12 indikator perkembangan penginputan data seluruh unit sekolah TP. {{ $currentYear->year ?? '2026/2027' }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                {{-- Form Filter Tahun Pelajaran --}}
                <form action="{{ route('yayasan.progress-input') }}" method="GET" class="flex items-center gap-2 m-0 p-0">
                    <select name="academic_year_id" onchange="this.form.submit()" class="bg-white/15 text-white border border-white/30 rounded-xl px-3 py-2 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-white/50 backdrop-blur-sm">
                        @foreach($allYears as $year)
                            <option value="{{ $year->id }}" class="text-gray-800" {{ ($currentYear && $currentYear->id == $year->id) ? 'selected' : '' }}>
                                TP. {{ $year->year }} {{ $year->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </form>

                {{-- Tombol Export PDF --}}
                <a href="{{ route('yayasan.progress-input.export-pdf', ['academic_year_id' => request('academic_year_id')]) }}" 
                   class="inline-flex items-center gap-2 bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5">
                    <i class="fas fa-file-pdf text-lg"></i>
                    <span>Eksport PDF Rinci</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Keterangan Indikator Status & Summary Stats --}}
    @php
        $totalItems = count($items);
        $totalSchoolData = 0;
        $greenCount = 0;
        $amberCount = 0;
        $redCount = 0;
        $schoolStats = [];

        foreach($items as $it) {
            foreach($it['schools_data'] as $sc) {
                $totalSchoolData++;
                $sName = $sc['school_name'];
                if (!isset($schoolStats[$sName])) {
                    $schoolStats[$sName] = ['green' => 0, 'amber' => 0, 'red' => 0, 'total' => 0];
                }
                $schoolStats[$sName]['total']++;

                if($sc['status_color'] === 'green') {
                    $greenCount++;
                    $schoolStats[$sName]['green']++;
                } elseif($sc['status_color'] === 'amber') {
                    $amberCount++;
                    $schoolStats[$sName]['amber']++;
                } else {
                    $redCount++;
                    $schoolStats[$sName]['red']++;
                }
            }
        }
        $readinessPct = $totalSchoolData > 0 ? round(($greenCount / $totalSchoolData) * 100, 1) : 0;
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Unit Sekolah</div>
                <div class="text-xl font-extrabold text-gray-900 mt-1">{{ count($schools) }} Unit</div>
                <div class="text-[11px] text-gray-500 mt-0.5">
                    @foreach($schools as $sch)
                        <span class="inline-block bg-gray-100 px-1.5 py-0.5 rounded text-gray-700 font-semibold mr-1 mb-0.5">{{ $sch->name }}</span>
                    @endforeach
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-lg font-bold flex-shrink-0">
                <i class="fas fa-school"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Indikator Dipantau</div>
                <div class="text-xl font-extrabold text-gray-900 mt-1">{{ $totalItems }} Item Kesiapan</div>
                <div class="text-[11px] text-gray-500 mt-0.5">12 Indikator per unit sekolah</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold flex-shrink-0">
                <i class="fas fa-list-check"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tingkat Kesiapan Input</div>
                <div class="text-xl font-extrabold text-emerald-600 mt-1">{{ $readinessPct }}% <span class="text-xs text-gray-400 font-semibold">({{ $greenCount }}/{{ $totalSchoolData }})</span></div>
                <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2">
                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $readinessPct }}%"></div>
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold flex-shrink-0">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Perlu Perhatian / Follow-up</div>
                <div class="text-xl font-extrabold text-rose-600 mt-1">{{ $redCount + $amberCount }} Indikator</div>
                <div class="text-[11px] text-gray-500 mt-0.5 font-medium">
                    <span class="text-rose-600 font-bold">{{ $redCount }} Kosong</span> • 
                    <span class="text-amber-600 font-bold">{{ $amberCount }} Sebagian</span>
                </div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg font-bold flex-shrink-0">
                <i class="fas fa-exclamation-circle"></i>
            </div>
        </div>
    </div>

    {{-- Breakdown Ringkas Per Unit Sekolah --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($schools as $sch)
            @php
                $sData = $schoolStats[$sch->name] ?? ['green' => 0, 'amber' => 0, 'red' => 0, 'total' => 12];
                $sPct = $sData['total'] > 0 ? round(($sData['green'] / $sData['total']) * 100, 1) : 0;
            @endphp
            <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-violet-600"></span>
                        <h4 class="font-extrabold text-gray-900 text-sm">{{ $sch->name }}</h4>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-lg {{ $sPct >= 80 ? 'bg-emerald-100 text-emerald-700' : ($sPct >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                        {{ $sPct }}% Lengkap
                    </span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2 mb-3">
                    <div class="bg-violet-600 h-2 rounded-full transition-all duration-500" style="width: {{ $sPct }}%"></div>
                </div>
                <div class="flex items-center justify-between text-xs font-medium text-gray-600">
                    <span class="text-emerald-700 font-bold"><i class="fas fa-check-circle text-emerald-500 mr-1"></i> {{ $sData['green'] }} Selesai</span>
                    <span class="text-amber-700 font-bold"><i class="fas fa-exclamation-triangle text-amber-500 mr-1"></i> {{ $sData['amber'] }} Proses</span>
                    <span class="text-rose-700 font-bold"><i class="fas fa-times-circle text-rose-500 mr-1"></i> {{ $sData['red'] }} Belum</span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Control Panel: Filter Unit Sekolah & Filter Status --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-extrabold text-gray-500 uppercase tracking-wider mr-2">Filter Unit:</span>
            <button type="button" onclick="filterSchool('all')" id="btn-school-all" class="btn-school-filter active bg-violet-600 text-white px-3 py-1.5 rounded-xl text-xs font-bold transition shadow-sm">
                Semua Unit
            </button>
            @foreach($schools as $sch)
                <button type="button" onclick="filterSchool('{{ Str::slug($sch->name) }}')" id="btn-school-{{ Str::slug($sch->name) }}" class="btn-school-filter bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-xl text-xs font-bold transition">
                    {{ $sch->name }}
                </button>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-extrabold text-gray-500 uppercase tracking-wider mr-2">Filter Status:</span>
            <button type="button" onclick="filterStatus('all')" id="btn-status-all" class="btn-status-filter active bg-gray-800 text-white px-3 py-1.5 rounded-xl text-xs font-bold transition shadow-sm">
                Semua Status
            </button>
            <button type="button" onclick="filterStatus('attention')" id="btn-status-attention" class="btn-status-filter bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 px-3 py-1.5 rounded-xl text-xs font-bold transition">
                <i class="fas fa-exclamation-circle text-rose-500 mr-1"></i> Perlu Perhatian
            </button>
            <button type="button" onclick="filterStatus('green')" id="btn-status-green" class="btn-status-filter bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 px-3 py-1.5 rounded-xl text-xs font-bold transition">
                <i class="fas fa-check-circle text-emerald-500 mr-1"></i> Lengkap
            </button>
        </div>
    </div>

    {{-- Tabel Rekapitulasi Progress Input Rinci --}}
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse" id="progress-table">
                <thead>
                    <tr class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 text-white text-left text-xs uppercase tracking-wider font-extrabold border-b border-gray-700">
                        <th class="py-4 px-5 w-1/4 border-r border-gray-700">Indikator Item</th>
                        <th class="py-4 px-4 w-40 border-r border-gray-700">Unit Sekolah</th>
                        <th class="py-4 px-4 w-48 text-center border-r border-gray-700">Perkembangan</th>
                        <th class="py-4 px-4 w-28 text-center border-r border-gray-700">Satuan</th>
                        <th class="py-4 px-5">Rekomendasi & Rincian Detail Data Terinput</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm">
                    @forelse($items as $item)
                        @php
                            $schoolsCount = count($item['schools_data']);
                        @endphp
                        @foreach($item['schools_data'] as $idx => $s)
                            @php
                                $slugSchool = Str::slug($s['school_name']);
                                $statusColor = $s['status_color'];
                            @endphp
                            <tr class="progress-row school-row-{{ $slugSchool }} status-row-{{ $statusColor }} hover:bg-violet-50/40 transition-colors {{ $idx === $schoolsCount - 1 ? 'border-b-4 border-gray-200' : '' }}"
                                data-school="{{ $slugSchool }}"
                                data-status="{{ $statusColor }}">
                                
                                {{-- Kolom Item (Merged rowspan untuk semua sekolah) --}}
                                @if($idx === 0)
                                    <td rowspan="{{ $schoolsCount }}" class="py-4 px-5 align-top bg-gray-50/60 border-r border-gray-200 item-cell">
                                        <div class="flex items-start gap-3 sticky top-4">
                                            <span class="flex-shrink-0 w-8 h-8 rounded-xl bg-violet-600 text-white font-extrabold text-xs flex items-center justify-center shadow-sm mt-0.5">
                                                {{ $item['number'] }}
                                            </span>
                                            <div>
                                                <h4 class="font-extrabold text-gray-900 text-base leading-snug">{{ $item['title'] }}</h4>
                                                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">{{ $item['description'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                @endif

                                {{-- Kolom Unit Sekolah --}}
                                <td class="py-4 px-4 align-middle font-bold text-gray-800 border-r border-gray-200">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2.5 h-2.5 rounded-full bg-violet-500"></div>
                                        <span class="font-bold text-gray-900">{{ $s['school_name'] }}</span>
                                    </div>
                                </td>

                                {{-- Kolom Perkembangan --}}
                                <td class="py-4 px-4 align-middle text-center border-r border-gray-200">
                                    @if($s['status_color'] === 'green')
                                        <span class="inline-block px-3 py-1 rounded-xl bg-emerald-100 text-emerald-800 font-extrabold text-xs shadow-sm border border-emerald-300">
                                            <i class="fas fa-check-circle mr-1"></i> {{ $s['perkembangan'] }}
                                        </span>
                                    @elseif($s['status_color'] === 'amber')
                                        <span class="inline-block px-3 py-1 rounded-xl bg-amber-100 text-amber-800 font-extrabold text-xs shadow-sm border border-amber-300">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> {{ $s['perkembangan'] }}
                                        </span>
                                    @else
                                        <span class="inline-block px-3 py-1 rounded-xl bg-rose-100 text-rose-800 font-extrabold text-xs shadow-sm border border-rose-300">
                                            <i class="fas fa-times-circle mr-1"></i> {{ $s['perkembangan'] }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Kolom Satuan --}}
                                <td class="py-4 px-4 align-middle text-center font-bold text-gray-600 border-r border-gray-200">
                                    <span class="bg-gray-100 text-gray-700 px-2.5 py-1 rounded-lg text-xs font-semibold border border-gray-200">
                                        {{ $s['satuan'] }}
                                    </span>
                                </td>

                                {{-- Kolom Rekomendasi & Rincian Detail Data --}}
                                <td class="py-4 px-5 align-middle text-gray-700 text-xs leading-relaxed space-y-2">
                                    <div class="flex items-start gap-2">
                                        @if($s['status_color'] === 'green')
                                            <i class="fas fa-check text-emerald-600 mt-0.5 flex-shrink-0"></i>
                                        @elseif($s['status_color'] === 'amber')
                                            <i class="fas fa-lightbulb text-amber-500 mt-0.5 flex-shrink-0"></i>
                                        @else
                                            <i class="fas fa-arrow-right text-rose-500 mt-0.5 flex-shrink-0"></i>
                                        @endif
                                        <span class="font-bold text-gray-800">{{ $s['rekomendasi'] }}</span>
                                    </div>

                                    {{-- List Rincian Detail Data --}}
                                    @if(!empty($s['details']))
                                        <div class="pt-2 border-t border-gray-100">
                                            <div class="text-[10px] font-extrabold text-violet-700 uppercase tracking-wider mb-1 flex items-center gap-1">
                                                <i class="fas fa-list-ul text-[9px]"></i> Rincian Detail Terinput:
                                            </div>
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach($s['details'] as $detail)
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-800 border border-slate-200 text-[11px] font-semibold">
                                                        <i class="fas fa-check-double text-[9px] text-violet-600"></i> {{ $detail }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- List Action Items Diagnostik jika ada --}}
                                    @if(!empty($s['action_items']))
                                        <div class="pt-1.5">
                                            <div class="text-[10px] font-extrabold text-rose-600 uppercase tracking-wider mb-1 flex items-center gap-1">
                                                <i class="fas fa-tools text-[9px]"></i> Action Items Perlu Dilengkapi:
                                            </div>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($s['action_items'] as $act)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-bold">
                                                        <i class="fas fa-exclamation-triangle text-[8px]"></i> {{ $act }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3 text-gray-300 block"></i>
                                <span class="font-semibold">Belum ada data indikator yang tersedia.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    let activeSchoolFilter = 'all';
    let activeStatusFilter = 'all';

    function filterSchool(schoolSlug) {
        activeSchoolFilter = schoolSlug;
        
        // Update active class on school buttons
        document.querySelectorAll('.btn-school-filter').forEach(btn => {
            btn.classList.remove('bg-violet-600', 'text-white', 'shadow-sm', 'active');
            btn.classList.add('bg-gray-100', 'text-gray-700');
        });
        
        const activeBtn = document.getElementById(`btn-school-${schoolSlug}`);
        if(activeBtn) {
            activeBtn.classList.remove('bg-gray-100', 'text-gray-700');
            activeBtn.classList.add('bg-violet-600', 'text-white', 'shadow-sm', 'active');
        }
        
        applyFilters();
    }

    function filterStatus(statusKey) {
        activeStatusFilter = statusKey;

        // Update active class on status buttons
        document.querySelectorAll('.btn-status-filter').forEach(btn => {
            btn.classList.remove('bg-gray-800', 'text-white', 'shadow-sm', 'active');
        });

        const activeBtn = document.getElementById(`btn-status-${statusKey}`);
        if(activeBtn) {
            activeBtn.classList.add('bg-gray-800', 'text-white', 'shadow-sm', 'active');
        }

        applyFilters();
    }

    function applyFilters() {
        const rows = document.querySelectorAll('.progress-row');
        
        rows.forEach(row => {
            const rowSchool = row.getAttribute('data-school');
            const rowStatus = row.getAttribute('data-status');

            let matchSchool = (activeSchoolFilter === 'all' || rowSchool === activeSchoolFilter);
            let matchStatus = true;

            if (activeStatusFilter === 'green') {
                matchStatus = (rowStatus === 'green');
            } else if (activeStatusFilter === 'attention') {
                matchStatus = (rowStatus === 'amber' || rowStatus === 'red');
            }

            if (matchSchool && matchStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Adjust rowspans on item cells dynamically
        document.querySelectorAll('.item-cell').forEach(cell => {
            const parentRow = cell.parentElement;
            const itemNumber = parentRow.querySelector('span').innerText.trim();
            
            // Count visible rows for this item
            const itemRows = Array.from(rows).filter(r => {
                const numSpan = r.querySelector('.item-cell span');
                if (numSpan && r.style.display !== 'none') return true;
                if (!numSpan && r.style.display !== 'none') {
                    // Check if part of same group
                    let prev = r.previousElementSibling;
                    while (prev) {
                        const prevSpan = prev.querySelector('.item-cell span');
                        if (prevSpan) {
                            return prevSpan.innerText.trim() === itemNumber;
                        }
                        prev = prev.previousElementSibling;
                    }
                }
                return false;
            });

            if (itemRows.length > 0) {
                cell.setAttribute('rowspan', itemRows.length);
                cell.style.display = '';
            } else {
                cell.style.display = 'none';
            }
        });
    }
</script>
@endsection

