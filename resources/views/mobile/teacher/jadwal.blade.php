@extends('mobile.layouts.app')

@section('title', 'Jadwal Mengajar Guru - PembdaHUB Mobile Pro')

@section('content')
<div class="space-y-4 pt-1" x-data="{ viewMode: 'harian', day: '{{ $activeDay }}' }">
    <!-- Header -->
    <div class="flex items-center justify-between px-1">
        <a href="{{ route('mobile.dashboard') }}" class="w-9 h-9 rounded-2xl bg-white border-2 border-slate-200 shadow-sm flex items-center justify-center text-slate-700 hover:bg-slate-50 transition active:scale-95">
            <i class="fa-solid fa-arrow-left text-xs"></i>
        </a>
        <div class="text-center">
            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">Jadwal Mengajar Guru</h2>
            <p class="text-[10px] text-slate-500 font-bold truncate max-w-[200px]">{{ $teacher->full_name ?? '-' }}</p>
        </div>
        <div class="w-9"></div>
    </div>

    <!-- Toggle View Switch: Harian vs Roster 1 Minggu -->
    <div class="bg-slate-200/80 p-1 rounded-2xl flex items-center shadow-inner border border-slate-300/60">
        <button @click="viewMode = 'harian'" 
                :class="viewMode === 'harian' ? 'bg-white text-purple-700 font-black shadow-sm' : 'text-slate-600 font-bold hover:text-slate-800'"
                class="flex-1 py-2 rounded-xl text-xs flex items-center justify-center gap-1.5 transition active:scale-95">
            <i class="fa-solid fa-calendar-day text-xs"></i>
            <span>Mode Harian</span>
        </button>
        <button @click="viewMode = 'roster'" 
                :class="viewMode === 'roster' ? 'bg-white text-purple-700 font-black shadow-sm' : 'text-slate-600 font-bold hover:text-slate-800'"
                class="flex-1 py-2 rounded-xl text-xs flex items-center justify-center gap-1.5 transition active:scale-95">
            <i class="fa-solid fa-table-cells text-xs"></i>
            <span>Roster 1 Minggu</span>
        </button>
    </div>

    <!-- KETERANGAN STATUS MENGAJAR HARI INI (Live Teaching Card) -->
    @if($currentSchedule)
        @php
            $currSubj = $currentSchedule->subject->name ?? ($currentSchedule->teachingAssignment->subject->name ?? 'Mata Pelajaran');
            $currClass = $currentSchedule->classroom->name ?? ($currentSchedule->teachingAssignment->classroom->name ?? ($currentSchedule->classroom->class_name ?? '-'));
            $currTime = $currentSchedule->timeSlot 
                ? ($currentSchedule->timeSlot->start_time . ' - ' . $currentSchedule->timeSlot->end_time) 
                : (($currentSchedule->start_time && $currentSchedule->end_time) ? ($currentSchedule->start_time . ' - ' . $currentSchedule->end_time) : ($currentSchedule->start_time ?? '-'));
        @endphp
        <div class="clay-green p-4.5 space-y-2 border-2 border-emerald-300/80 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase bg-emerald-900/40 text-emerald-100 border border-emerald-400/50">
                    <span class="w-2 h-2 rounded-full bg-rose-400 animate-ping"></span>
                    Sedang Mengajar Saat Ini
                </span>
                <span class="text-[10px] font-black text-emerald-950 bg-white/40 px-2 py-0.5 rounded-lg">
                    {{ $currTime }}
                </span>
            </div>

            <div>
                <h3 class="text-sm font-black text-white leading-snug">{{ $currSubj }}</h3>
                <p class="text-[11px] text-emerald-100 font-bold mt-0.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-school text-xs text-white"></i> Kelas: {{ $currClass }}
                </p>
                @if($currentSchedule->room)
                    <p class="text-[10px] text-emerald-200 font-extrabold mt-0.5">
                        <i class="fa-solid fa-door-open mr-1"></i> Ruangan: {{ $currentSchedule->room }}
                    </p>
                @endif
            </div>
        </div>
    @elseif($nextSchedule)
        @php
            $nextSubj = $nextSchedule->subject->name ?? ($nextSchedule->teachingAssignment->subject->name ?? 'Mata Pelajaran');
            $nextClass = $nextSchedule->classroom->name ?? ($nextSchedule->teachingAssignment->classroom->name ?? ($nextSchedule->classroom->class_name ?? '-'));
            $nextTime = $nextSchedule->timeSlot 
                ? ($nextSchedule->timeSlot->start_time . ' - ' . $nextSchedule->timeSlot->end_time) 
                : (($nextSchedule->start_time && $nextSchedule->end_time) ? ($nextSchedule->start_time . ' - ' . $nextSchedule->end_time) : ($nextSchedule->start_time ?? '-'));
        @endphp
        <div class="clay-purple p-4.5 space-y-2 border-2 border-purple-300/80">
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase bg-white/30 text-purple-950 border border-white/40">
                    <i class="fa-solid fa-clock-rotate-left text-[9px]"></i>
                    Mengajar Terdekat / Berikutnya
                </span>
                <span class="text-[10px] font-black text-purple-950 bg-white/40 px-2 py-0.5 rounded-lg">
                    {{ $nextTime }}
                </span>
            </div>

            <div>
                <h3 class="text-sm font-black text-white leading-snug">{{ $nextSubj }}</h3>
                <p class="text-[11px] text-purple-100 font-bold mt-0.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-school text-xs text-white"></i> Kelas: {{ $nextClass }}
                </p>
                @if($nextSchedule->room)
                    <p class="text-[10px] text-purple-200 font-extrabold mt-0.5">
                        <i class="fa-solid fa-door-open mr-1"></i> Ruangan: {{ $nextSchedule->room }}
                    </p>
                @endif
            </div>
        </div>
    @endif

    <!-- ==================== 1. TAMPILAN MODE HARIAN ==================== -->
    <div x-show="viewMode === 'harian'" class="space-y-3">
        <!-- Day Filter Pills (Clay Pills) -->
        <div class="flex items-center space-x-2 overflow-x-auto pb-1 no-scrollbar">
            @foreach($days as $d)
                @php 
                    $count = $schedulesByDay[$d]->count(); 
                    $isHariIni = ($d === $today);
                @endphp
                <button @click="day = '{{ $d }}'" 
                        :class="day === '{{ $d }}' ? 'clay-purple text-white shadow-md scale-105 font-black' : 'bg-white text-slate-600 border-2 border-slate-200 font-bold'"
                        class="px-3.5 py-2 rounded-2xl text-xs whitespace-nowrap transition flex items-center gap-1.5 shrink-0 relative">
                    @if($isHariIni)
                        <span class="w-2 h-2 rounded-full bg-emerald-400 absolute top-1.5 right-1.5"></span>
                    @endif
                    <span>{{ $dayLabels[$d] }}</span>
                    @if($count > 0)
                        <span class="w-4 h-4 rounded-full text-[9px] font-black flex items-center justify-center"
                              :class="day === '{{ $d }}' ? 'bg-white/30 text-white' : 'bg-purple-100 text-purple-800'">
                            {{ $count }}
                        </span>
                    @endif
                </button>
            @endforeach
        </div>

        <!-- Schedules List (Clay Cards) -->
        @foreach($days as $d)
            <div x-show="day === '{{ $d }}'" class="space-y-2.5">
                <div class="flex items-center justify-between px-1">
                    <span class="text-xs font-black text-slate-700 uppercase tracking-wider">
                        Jadwal Hari {{ $dayLabels[$d] }} ({{ $schedulesByDay[$d]->count() }} Sesi)
                    </span>
                    @if($d === $today)
                        <span class="text-[9px] font-black bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full border border-emerald-300">
                            Hari Ini
                        </span>
                    @endif
                </div>

                @forelse($schedulesByDay[$d] as $sch)
                    @php
                        $subjectName = $sch->subject->name ?? ($sch->teachingAssignment->subject->name ?? 'Mata Pelajaran');
                        $className = $sch->classroom->name ?? ($sch->teachingAssignment->classroom->name ?? ($sch->classroom->class_name ?? '-'));
                        $timeDisplay = $sch->timeSlot 
                            ? ($sch->timeSlot->start_time . ' - ' . $sch->timeSlot->end_time) 
                            : (($sch->start_time && $sch->end_time) ? ($sch->start_time . ' - ' . $sch->end_time) : ($sch->start_time ?? '-'));

                        $isOngoing = ($sch->time_status === 'ongoing');
                        $isUpcoming = ($sch->time_status === 'upcoming');
                        $isCompleted = ($sch->time_status === 'completed');
                    @endphp
                    <div class="clay-card p-4 flex items-center justify-between gap-3 border-2 
                        {{ $isOngoing ? 'border-emerald-400 bg-emerald-50/40' : ($isUpcoming ? 'border-purple-200' : ($isCompleted ? 'border-slate-200 opacity-80' : 'border-slate-100')) }}">
                        <div class="flex items-center space-x-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl flex items-center justify-center font-black text-lg shadow-sm shrink-0
                                {{ $isOngoing ? 'clay-green text-white' : ($isUpcoming ? 'clay-purple text-white' : 'clay-card text-slate-600') }}">
                                <i class="fa-solid fa-chalkboard-user"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <h4 class="text-xs font-black text-slate-900 truncate">{{ $subjectName }}</h4>
                                    @if($isOngoing)
                                        <span class="text-[8px] font-black bg-emerald-500 text-white px-1.5 py-0.5 rounded-md uppercase animate-pulse">
                                            Sedang Berlangsung
                                        </span>
                                    @elseif($isUpcoming)
                                        <span class="text-[8px] font-black bg-purple-100 text-purple-800 px-1.5 py-0.5 rounded-md uppercase">
                                            Akan Datang
                                        </span>
                                    @elseif($isCompleted)
                                        <span class="text-[8px] font-black bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded-md uppercase">
                                            Selesai
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[10px] text-slate-500 font-bold truncate flex items-center gap-1 mt-0.5">
                                    <i class="fa-solid fa-school text-purple-600"></i> Kelas: {{ $className }}
                                </p>
                                @if($sch->room)
                                    <p class="text-[9px] text-purple-600 font-extrabold truncate">
                                        <i class="fa-solid fa-door-open mr-0.5"></i> {{ $sch->room }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="text-right shrink-0">
                            <span class="px-2.5 py-1 rounded-xl text-[10px] font-black border block
                                {{ $isOngoing ? 'bg-emerald-100 text-emerald-900 border-emerald-300' : 'bg-purple-50 text-purple-900 border border-purple-200' }}">
                                {{ $timeDisplay }}
                            </span>
                            @if($sch->duration_slots > 1)
                                <span class="text-[9px] text-slate-400 font-bold mt-0.5 block">
                                    {{ $sch->duration_slots }} JP
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold space-y-1">
                        <i class="fa-solid fa-calendar-check text-3xl mb-1 text-slate-300"></i>
                        <p>Tidak ada jadwal mengajar di hari {{ $dayLabels[$d] }}.</p>
                    </div>
                @endforelse
            </div>
        @endforeach
    </div>

    <!-- ==================== 2. TAMPILAN MODE ROSTER 1 MINGGU (TABEL MATRIKS) ==================== -->
    <div x-show="viewMode === 'roster'" class="space-y-4" style="display: none;">
        <!-- Weekly Summary Hero Card -->
        <div class="clay-purple p-5 space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-wider bg-white/30 px-2.5 py-0.5 rounded-full border border-white/40">
                    Roster Mengajar Mingguan
                </span>
                <span class="text-[10px] font-black bg-white/20 px-2.5 py-0.5 rounded-full">
                    Senin s/d {{ in_array('saturday', $activeDays) ? 'Sabtu' : 'Jumat' }}
                </span>
            </div>

            <h3 class="text-base font-black text-white leading-tight">
                Tabel Roster Mengajar {{ $teacher->full_name ?? '-' }}
            </h3>

            <!-- Quick Stats -->
            <div class="grid grid-cols-3 gap-2 pt-1 border-t border-white/20 text-center text-white">
                <div class="p-1.5 bg-white/10 rounded-xl">
                    <span class="block text-xs font-black">{{ $totalWeeklySessions }}</span>
                    <span class="text-[8px] font-bold text-purple-100 uppercase">Total Sesi</span>
                </div>
                <div class="p-1.5 bg-white/10 rounded-xl">
                    <span class="block text-xs font-black">{{ $totalWeeklyJP }}</span>
                    <span class="text-[8px] font-bold text-purple-100 uppercase">Total JP</span>
                </div>
                <div class="p-1.5 bg-white/10 rounded-xl">
                    <span class="block text-xs font-black">{{ $totalUniqueClasses }}</span>
                    <span class="text-[8px] font-bold text-purple-100 uppercase">Kelas Diajar</span>
                </div>
            </div>
        </div>

        <!-- Scroll Hint -->
        <div class="flex items-center justify-between px-1 text-[11px] font-extrabold text-purple-700 bg-purple-50 p-2.5 rounded-2xl border border-purple-200">
            <span class="flex items-center gap-1.5">
                <i class="fa-solid fa-arrows-left-right text-xs"></i> Geser tabel ke kanan untuk melihat seluruh hari
            </span>
            <span class="text-[9px] font-black uppercase bg-purple-200/70 text-purple-900 px-2 py-0.5 rounded-full">Tabel Roster</span>
        </div>

        <!-- Weekly Timetable Matrix Table (Baris: Waktu, Kolom: Hari) -->
        <div class="clay-card p-0 overflow-hidden border-2 border-slate-200 shadow-sm">
            <div class="overflow-x-auto relative">
                <table class="w-full border-collapse min-w-[650px] text-left">
                    <thead>
                        <tr class="bg-slate-100/90 border-b-2 border-slate-200">
                            <!-- Kolom Waktu (Sticky Left) -->
                            <th class="p-3 text-[11px] font-black text-slate-700 uppercase tracking-wider text-center border-r-2 border-slate-200 w-24 sticky left-0 bg-slate-100 z-20 shadow-[2px_0_4px_rgba(0,0,0,0.04)]">
                                Jam / Waktu
                            </th>
                            @foreach($activeDays as $d)
                                @php $isHariIni = ($d === $today); @endphp
                                <th class="p-2.5 text-center border-r border-slate-200 last:border-r-0 min-w-[125px] {{ $isHariIni ? 'bg-purple-100/70' : '' }}">
                                    <span class="text-[11px] font-black uppercase tracking-wide block {{ $isHariIni ? 'text-purple-900' : 'text-slate-700' }}">
                                        {{ $dayLabels[$d] }}
                                    </span>
                                    @if($isHariIni)
                                        <span class="inline-block text-[8px] font-black bg-purple-600 text-white px-1.5 py-0.2 rounded-full uppercase mt-0.5">
                                            Hari Ini
                                        </span>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-xs">
                        @php $renderedOccupied = []; @endphp
                        @forelse($timeSlots as $slot)
                            @php
                                $orderKey = $slot->slot_order ?? $slot->start_time;
                                $startFormatted = \Carbon\Carbon::parse($slot->start_time)->format('H:i');
                                $endFormatted = \Carbon\Carbon::parse($slot->end_time)->format('H:i');
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition">
                                <!-- Sticky Time Column -->
                                <td class="p-2 border-r-2 border-slate-200 text-center sticky left-0 bg-slate-50 z-10 w-24 shadow-[2px_0_4px_rgba(0,0,0,0.04)]">
                                    <span class="text-[11px] font-black text-slate-900 block bg-white px-1.5 py-0.5 rounded-lg border border-slate-200 shadow-2xs">
                                        {{ $startFormatted }}
                                    </span>
                                    <span class="text-[9px] font-bold text-slate-400 block my-0.5">s/d</span>
                                    <span class="text-[10px] font-bold text-slate-600 block bg-white px-1.5 py-0.5 rounded-lg border border-slate-200">
                                        {{ $endFormatted }}
                                    </span>
                                </td>

                                <!-- Day Columns -->
                                @foreach($activeDays as $d)
                                    @php
                                        if (isset($renderedOccupied[$d][$orderKey])) continue;

                                        $sch = $timetable[$orderKey][$d] ?? null;
                                        $isHariIni = ($d === $today);
                                        $duration = (int) ($sch->duration_slots ?? 1);

                                        if ($duration > 1 && is_numeric($orderKey)) {
                                            for ($i = 1; $i < $duration; $i++) {
                                                $renderedOccupied[$d][$orderKey + $i] = true;
                                            }
                                        }

                                        $subjId = $sch ? ($sch->subject_id ?? ($sch->teachingAssignment->subject_id ?? 0)) : 0;
                                        $col = $subjectColors[$subjId] ?? [
                                            'bg' => 'bg-slate-50',
                                            'border' => 'border-slate-200',
                                            'text' => 'text-slate-800',
                                            'sub' => 'text-slate-500',
                                            'badge' => 'bg-slate-100 text-slate-700',
                                            'dot' => 'bg-slate-400'
                                        ];
                                    @endphp
                                    <td class="p-1.5 border-r border-slate-200 last:border-r-0 align-top {{ $isHariIni ? 'bg-purple-50/30' : '' }}"
                                        @if($duration > 1) rowspan="{{ $duration }}" @endif>
                                        @if($sch)
                                            @php
                                                $subjectName = $sch->subject->name ?? ($sch->teachingAssignment->subject->name ?? 'Mata Pelajaran');
                                                $className = $sch->classroom->name ?? ($sch->teachingAssignment->classroom->name ?? ($sch->classroom->class_name ?? '-'));
                                                $isOngoing = ($sch->time_status === 'ongoing');
                                            @endphp
                                            <div class="p-2 rounded-xl {{ $col['bg'] }} border {{ $col['border'] }} h-full flex flex-col justify-between space-y-1 relative shadow-2xs {{ $isOngoing ? 'ring-2 ring-emerald-500' : '' }}">
                                                <div>
                                                    <div class="flex items-center justify-between gap-1">
                                                        <span class="w-2 h-2 rounded-full {{ $col['dot'] }} shrink-0"></span>
                                                        @if($duration > 1)
                                                            <span class="text-[8px] font-black {{ $col['badge'] }} px-1 py-0.2 rounded-md">
                                                                {{ $duration }} JP
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <h5 class="text-[11px] font-black {{ $col['text'] }} leading-tight mt-1 line-clamp-2">
                                                        {{ $subjectName }}
                                                    </h5>
                                                </div>

                                                <div class="pt-1 border-t border-black/5 text-[9px] font-bold {{ $col['sub'] }} truncate">
                                                    <p class="truncate"><i class="fa-solid fa-school text-[8px] mr-0.5"></i>Kelas: {{ $className }}</p>
                                                    @if($sch->room)
                                                        <p class="text-purple-700 font-extrabold truncate"><i class="fa-solid fa-door-open text-[8px] mr-0.5"></i>{{ $sch->room }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div class="h-full min-h-[60px] flex items-center justify-center text-slate-300 text-xs">
                                                -
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($activeDays) + 1 }}" class="p-6 text-center text-slate-500 font-bold text-xs">
                                    Belum ada slot waktu atau jadwal yang ditentukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Subject Color Legend -->
        @if(!empty($subjectColors))
        <div class="clay-card p-4 space-y-2">
            <h4 class="text-[11px] font-black text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                <i class="fa-solid fa-palette text-purple-600"></i> Legenda Mata Pelajaran
            </h4>
            <div class="flex flex-wrap gap-1.5">
                @php
                    $legendSubjects = collect();
                    foreach($timetable as $row) {
                        foreach($row as $schedule) {
                            if($schedule) {
                                $sId = $schedule->subject_id ?? ($schedule->teachingAssignment->subject_id ?? null);
                                if($sId) {
                                    $legendSubjects[$sId] = $schedule->subject->name ?? ($schedule->teachingAssignment->subject->name ?? 'Mata Pelajaran');
                                }
                            }
                        }
                    }
                @endphp
                @foreach($legendSubjects as $sId => $sName)
                    @php $c = $subjectColors[$sId] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-800', 'dot' => 'bg-slate-400']; @endphp
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[10px] font-black {{ $c['bg'] }} {{ $c['text'] }} border border-black/5">
                        <span class="w-2 h-2 rounded-full {{ $c['dot'] }}"></span>
                        <span class="truncate max-w-[140px]">{{ $sName }}</span>
                    </span>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
