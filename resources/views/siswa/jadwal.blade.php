@extends('layouts.siswa')
@section('title', 'Jadwal Pelajaran - Portal Siswa')

@section('content')
<div class="space-y-5">
    {{-- Header + Stats --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 rounded-2xl border border-gray-100 shadow-sm mb-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white text-xl shadow-lg flex-shrink-0">
                <i class="fas fa-layer-group"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-800">Jadwal Pelajaran</h1>
                <p class="text-xs text-gray-500 mt-0.5">Roster mingguan kelas <span class="font-bold text-blue-600">{{ $classroom->class_name ?? '-' }}</span></p>
            </div>
        </div>
        {{-- Stats Badges --}}
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 px-3 py-1.5 rounded-lg text-xs font-semibold border border-blue-100">
                <i class="fas fa-clock text-xs"></i> {{ $totalSessions }} Sesi
            </span>
            <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-lg text-xs font-semibold border border-emerald-100">
                <i class="fas fa-book text-xs"></i> {{ $totalJP }} JP
            </span>
            <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 px-3 py-1.5 rounded-lg text-xs font-semibold border border-amber-100">
                <i class="fas fa-book-open text-xs"></i> {{ $uniqueSubjects }} Mapel
            </span>
        </div>
    </div>

    @if(empty($timetable))
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-calendar-times text-4xl text-gray-300"></i>
            </div>
            <p class="text-gray-500 font-medium">Belum ada jadwal pelajaran yang terdaftar.</p>
        </div>
    @else
        {{-- Weekly Grid --}}
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse min-w-[800px]">
                    <thead class="bg-gray-100 border-b-2 border-gray-200">
                        <tr class="bg-gray-100">
                            <th class="px-4 py-4 text-xs font-bold text-gray-600 uppercase tracking-wider text-center border-b border-r border-gray-200 w-28 sticky left-0 bg-gray-100 z-20 shadow-[2px_0_5px_rgba(0,0,0,0.03)]">
                                Jam / Waktu
                            </th>
                            @foreach($activeDays as $day)
                                @php $isToday = strtolower(now()->format('l')) === $day; @endphp
                                <th class="px-2 py-4 text-center border-b border-r border-gray-200 last:border-r-0 {{ $isToday ? 'bg-blue-100/50' : '' }}" style="min-width: 150px;">
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="text-xs font-bold {{ $isToday ? 'text-blue-700' : 'text-gray-500' }} uppercase tracking-wider">{{ $dayLabels[$day] }}</span>
                                        @if($isToday)
                                            <span class="inline-flex items-center gap-1 text-[10px] bg-blue-600 text-white px-2 py-0.5 rounded-full font-bold shadow-sm animate-pulse">
                                                HARI INI
                                            </span>
                                        @endif
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="border-t border-gray-200">
                        @php $renderedOccupied = []; @endphp
                        @foreach($timeSlots as $slot)
                            @php
                                $order = $slot->slot_order;
                                $startFormatted = \Carbon\Carbon::parse($slot->start_time)->format('H:i');
                                $endFormatted = \Carbon\Carbon::parse($slot->end_time)->format('H:i');
                            @endphp
                            <tr class="group border-b border-gray-200 hover:bg-gray-50 transition-colors">
                                <td class="px-2 py-4 border-r border-gray-200 text-center sticky left-0 bg-gray-50 group-hover:bg-gray-100 z-10 w-28 shadow-[2px_0_5px_rgba(0,0,0,0.03)] transition-colors">
                                    <div class="flex flex-col items-center justify-center h-full">
                                        <span class="text-[13px] font-extrabold text-gray-700 bg-white px-2 py-1 rounded-md shadow-sm border border-gray-200 w-full mb-1">{{ $startFormatted }}</span>
                                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest my-0.5">s/d</span>
                                        <span class="text-[13px] font-bold text-gray-500 bg-white px-2 py-1 rounded-md shadow-sm border border-gray-100 w-full">{{ $endFormatted }}</span>
                                    </div>
                                </td>
                                @foreach($activeDays as $day)
                                    @php
                                        if (isset($renderedOccupied[$day][$order])) continue;

                                        $schedule = $timetable[$order][$day] ?? null;
                                        $isToday = strtolower(now()->format('l')) === $day;
                                        $duration = $schedule->duration_slots ?? 1;
                                        
                                        if ($duration > 1) {
                                            for ($i = 1; $i < $duration; $i++) {
                                                $renderedOccupied[$day][$order + $i] = true;
                                            }
                                        }
                                        
                                        $colors = $schedule ? ($subjectColors[$schedule->subject_id] ?? ['bg' => 'bg-gray-100', 'border' => 'border-gray-300', 'text' => 'text-gray-800', 'sub' => 'text-gray-600']) : null;
                                    @endphp
                                    <td class="px-2 py-2 border-r border-gray-200 last:border-r-0 {{ $isToday ? 'bg-blue-50/30' : '' }}" 
                                        @if($duration > 1) rowspan="{{ $duration }}" @endif>
                                        @if($schedule)
                                            <a href="{{ route('siswa.lms.schedule', $schedule->id) }}" title="Buka Modul Pembelajaran LMS: {{ $schedule->subject->subject_name ?? $schedule->subject->name ?? '' }}" class="rounded-xl p-3 {{ $colors['bg'] }} border {{ str_replace('border-', 'border-opacity-50 border-', $colors['border']) }} hover:shadow-lg hover:scale-[1.02] transition-all duration-300 h-full flex flex-col min-h-[110px] relative overflow-hidden group/card shadow-sm text-left no-underline block">
                                                {{-- Teacher & Info --}}
                                                <div class="flex items-center gap-2 mb-2 relative z-10">
                                                    <div class="w-8 h-8 rounded-full overflow-hidden border-2 border-white shadow-sm bg-white flex-shrink-0">
                                                        <img src="{{ $schedule->teacher->photo_url }}" class="w-full h-full object-cover" alt="{{ $schedule->teacher->full_name }}">
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="text-xs font-bold {{ $colors['text'] }} truncate leading-tight group-hover/card:text-blue-700 transition-colors">
                                                            {{ explode(' ', $schedule->teacher->full_name ?? $schedule->teacher->user->name)[0] }}
                                                        </p>
                                                        <p class="text-[10px] font-bold {{ $colors['sub'] }} opacity-80 uppercase tracking-wider mt-0.5">Guru Mapel</p>
                                                    </div>
                                                </div>

                                                <div class="mb-3 relative z-10">
                                                    <p class="text-[13px] font-extrabold {{ $colors['text'] }} leading-snug group-hover/card:tracking-tight transition-all">
                                                        {{ $schedule->subject->subject_name ?? $schedule->subject->name ?? '-' }}
                                                    </p>
                                                </div>

                                                <div class="flex items-center justify-between mt-auto relative z-10">
                                                    <span class="text-[10px] font-bold text-blue-600 opacity-0 group-hover/card:opacity-100 transition-opacity flex items-center gap-1">
                                                        <i class="fas fa-external-link-alt text-[9px]"></i> LMS
                                                    </span>
                                                    @if($duration > 1)
                                                        <span class="text-xs font-bold {{ $colors['sub'] }} opacity-50">{{ $duration }} JP</span>
                                                    @endif
                                                </div>

                                                {{-- Decorative background icon --}}
                                                <i class="fas fa-book-open absolute -right-2 -bottom-2 text-4xl opacity-[0.03] group-hover/card:opacity-[0.08] transition-all transform group-hover/card:rotate-12"></i>
                                            </a>
                                        @else
                                            <div class="h-full flex items-center justify-center min-h-[100px]">
                                                <div class="w-1.5 h-1.5 bg-gray-100 rounded-full group-hover:bg-blue-100 transition-colors"></div>
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
                <i class="fas fa-palette mr-1"></i> Daftar Mata Pelajaran
            </h3>
            <div class="flex flex-wrap gap-2">
                @php
                    $legendSubjects = collect();
                    foreach($timetable as $row) {
                        foreach($row as $schedule) {
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

