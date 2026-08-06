@extends('layouts.guru')
@section('title', 'Jadwal Mengajar - Portal Guru')

@section('content')
<div class="space-y-5">
    {{-- Header Banner (Neo-Brutalism) --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl p-6 border-2 border-black" style="background: linear-gradient(135deg, #090d16 0%, #1e1b4b 50%, #1e3a8a 100%) !important; color: #ffffff !important;">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-black text-white flex items-center gap-3" style="color: #ffffff !important;">
                    <div class="w-10 h-10 rounded-xl bg-amber-400 border-2 border-black flex items-center justify-center text-black shadow-sm text-lg">
                        <i class="fas fa-calendar-alt text-black"></i>
                    </div>
                    Jadwal Mengajar
                </h1>
                <p class="text-xs md:text-sm font-bold text-sky-200 mt-1" style="color: #bae6fd !important;">Roster mingguan sesi mengajar & kelas yang diampu</p>
            </div>
            {{-- Weekly Stats Badges --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 text-xs font-black px-3 py-1.5 rounded-xl border border-black shadow-xs uppercase tracking-wider" style="background-color: #38bdf8 !important; color: #000000 !important;">
                    <i class="fas fa-clock text-black"></i> {{ $totalSessions }} Sesi
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs font-black px-3 py-1.5 rounded-xl border border-black shadow-xs uppercase tracking-wider" style="background-color: #34d399 !important; color: #000000 !important;">
                    <i class="fas fa-book text-black"></i> {{ $totalJP }} JP
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs font-black px-3 py-1.5 rounded-xl border border-black shadow-xs uppercase tracking-wider" style="background-color: #c084fc !important; color: #000000 !important;">
                    <i class="fas fa-chalkboard text-black"></i> {{ $uniqueClassrooms }} Kelas
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs font-black px-3 py-1.5 rounded-xl border border-black shadow-xs uppercase tracking-wider" style="background-color: #fbbf24 !important; color: #000000 !important;">
                    <i class="fas fa-book-open text-black"></i> {{ $uniqueSubjects }} Mapel
                </span>
            </div>
        </div>
    </div>

    @if(empty($timetable))
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-calendar-times text-4xl text-gray-300"></i>
            </div>
            <p class="text-gray-500 font-medium">Belum ada jadwal mengajar yang terdaftar.</p>
            <p class="text-sm text-gray-400 mt-1">Hubungi admin untuk menambahkan jadwal Anda.</p>
        </div>
    @else
        {{-- Compact Weekly Timetable Grid --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse min-w-[640px]">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <th class="px-3 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center border-b border-r border-gray-200 w-20 sticky left-0 bg-gray-50 z-10">
                                <i class="fas fa-clock mr-1 text-gray-400"></i>Jam
                            </th>
                            @foreach($activeDays as $day)
                                @php
                                    $isToday = $day === strtolower(now()->format('l'));
                                    $dayColors = [
                                        'monday' => 'from-blue-500 to-blue-600',
                                        'tuesday' => 'from-emerald-500 to-emerald-600',
                                        'wednesday' => 'from-purple-500 to-purple-600',
                                        'thursday' => 'from-amber-500 to-amber-600',
                                        'friday' => 'from-rose-500 to-rose-600',
                                        'saturday' => 'from-cyan-500 to-cyan-600',
                                    ];
                                @endphp
                                <th class="px-2 py-3 text-center border-b border-r border-gray-200 last:border-r-0 {{ $isToday ? 'bg-emerald-50' : '' }}" style="min-width: 120px;">
                                    <div class="flex flex-col items-center gap-1">
                                        @if($isToday)
                                            <span class="inline-flex items-center gap-1 text-xs bg-emerald-500 text-white px-2 py-0.5 rounded-full font-bold">
                                                <i class="fas fa-circle text-[4px] animate-pulse"></i> HARI INI
                                            </span>
                                        @endif
                                        <span class="text-sm font-bold {{ $isToday ? 'text-emerald-700' : 'text-gray-700' }}">{{ $dayLabels[$day] ?? $day }}</span>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php $renderedOccupied = []; @endphp
                        @foreach($timeSlots as $slot)
                            @php
                                $order = $slot->slot_order;
                                $startFormatted = \Carbon\Carbon::parse($slot->start_time)->format('H:i');
                                $endFormatted = \Carbon\Carbon::parse($slot->end_time)->format('H:i');
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                {{-- Time Column --}}
                                <td class="px-2 py-3 border-r border-b border-gray-200 text-center sticky left-0 bg-white z-10 w-20">
                                    <div class="flex flex-col items-center">
                                        <span class="text-xs font-bold text-gray-700">{{ $startFormatted }}</span>
                                        <span class="text-xs text-gray-400">{{ $endFormatted }}</span>
                                    </div>
                                </td>
                                {{-- Day Cells --}}
                                @foreach($activeDays as $day)
                                    @php
                                        // Skip if this cell is already occupied by a rowspan from above
                                        if (isset($renderedOccupied[$day][$order])) continue;

                                        $schedule = $timetable[$order][$day] ?? null;
                                        $isToday = $day === strtolower(now()->format('l'));
                                        $duration = $schedule->duration_slots ?? 1;
                                        
                                        // Mark future slots as occupied for this day
                                        if ($duration > 1) {
                                            for ($i = 1; $i < $duration; $i++) {
                                                $renderedOccupied[$day][$order + $i] = true;
                                            }
                                        }
                                        
                                        $colors = $schedule ? ($subjectColors[$schedule->subject_id] ?? ['bg' => 'bg-gray-100', 'border' => 'border-gray-300', 'text' => 'text-gray-800', 'sub' => 'text-gray-600']) : null;
                                    @endphp
                                    <td class="px-1.5 py-1.5 border-r border-b border-gray-200 last:border-r-0 {{ $isToday ? 'bg-emerald-50/30' : '' }}" 
                                        @if($duration > 1) rowspan="{{ $duration }}" @endif>
                                        @if($schedule)
                                            <div class="rounded-xl p-3 {{ $colors['bg'] }} border-2 {{ $colors['border'] }} hover:shadow-lg transition-all duration-300 cursor-default h-full flex flex-col justify-center min-h-[60px]">
                                                <div class="mb-1">
                                                    <p class="text-xs font-semibold uppercase tracking-wider {{ $colors['sub'] }} opacity-80 mb-0.5">
                                                        {{ $schedule->classroom->class_name ?? '-' }}
                                                    </p>
                                                    <p class="text-sm font-bold {{ $colors['text'] }} leading-tight" title="{{ $schedule->subject->subject_name ?? $schedule->subject->name ?? '-' }}">
                                                        {{ $schedule->subject->subject_name ?? $schedule->subject->name ?? '-' }}
                                                    </p>
                                                </div>
                                                <div class="flex items-center justify-between mt-auto">
                                                    @if($schedule->room)
                                                        <span class="text-xs {{ $colors['sub'] }} font-medium bg-white px-1.5 py-0.5 rounded">
                                                            <i class="fas fa-door-open mr-1"></i>{{ $schedule->room }}
                                                        </span>
                                                    @endif
                                                    @if($duration > 1)
                                                        <span class="text-xs font-bold {{ $colors['sub'] }} ml-auto">{{ $duration }} JP</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div class="rounded-lg h-full flex items-center justify-center min-h-[60px] border border-transparent">
                                                <div class="w-1 h-1 bg-gray-200 rounded-full"></div>
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Subject Legend --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">
                <i class="fas fa-palette mr-1"></i> Legenda Mata Pelajaran
            </h3>
            <div class="flex flex-wrap gap-2">
                @php
                    $legendSubjects = collect();
                    foreach($timetable as $row) {
                        foreach($row as $schedule) { // Fixed: $row is now [day => schedule]
                            if($schedule && $schedule->subject) {
                                $legendSubjects[$schedule->subject_id] = $schedule->subject;
                            }
                        }
                    }
                @endphp
                @foreach($legendSubjects as $subjectId => $subject)
                    @php $colors = $subjectColors[$subjectId] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-700']; @endphp
                    <span class="inline-flex items-center gap-1.5 {{ $colors['bg'] }} {{ $colors['text'] }} px-2.5 py-1 rounded-lg text-[11px] font-medium">
                        <span class="w-2 h-2 rounded-full {{ str_replace('bg-', 'bg-', str_replace('-100', '-400', $colors['bg'])) }}"></span>
                        {{ $subject->subject_name ?? $subject->name ?? '-' }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
