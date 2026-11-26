@extends('layouts.guru')
@section('title', 'Jadwal Mengajar - Portal Guru')

@section('content')
<div class="gd-wrap">
    {{-- Header Hero --}}
    <div class="gd-hero">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-emerald-400 text-slate-950 flex items-center justify-center text-xl shadow-md shrink-0">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-white leading-tight">Jadwal Mengajar</h1>
                    <p class="text-xs sm:text-sm text-slate-300 mt-0.5">Roster mingguan sesi mengajar & kelas yang diampu</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-xl bg-white/10 text-sky-200 border border-white/15">
                    <i class="fas fa-clock"></i> {{ $totalSessions }} Sesi
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-xl bg-white/10 text-emerald-200 border border-white/15">
                    <i class="fas fa-book"></i> {{ $totalJP }} JP
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-xl bg-white/10 text-purple-200 border border-white/15">
                    <i class="fas fa-chalkboard"></i> {{ $uniqueClassrooms }} Kelas
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-xl bg-emerald-400 text-slate-950">
                    <i class="fas fa-book-open"></i> {{ $uniqueSubjects }} Mapel
                </span>
            </div>
        </div>
    </div>

    @if(empty($timetable))
        <div class="gd-card p-12 text-center">
            <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fas fa-calendar-times"></i>
            </div>
            <p class="text-base font-bold text-slate-800">Belum ada jadwal mengajar yang terdaftar</p>
            <p class="text-sm text-slate-500 mt-1">Hubungi admin sekolah untuk penjadwalan mengajar Anda.</p>
        </div>
    @else
        {{-- Compact Weekly Timetable Grid --}}
        <div class="gd-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse min-w-[680px]">
                    <thead class="bg-slate-900 text-white">
                        <tr>
                            <th class="px-3 py-3.5 text-xs font-bold uppercase tracking-wider text-center border-r border-slate-700 w-24 sticky left-0 z-10 bg-slate-900 text-emerald-300">
                                <i class="fas fa-clock mr-1"></i> Waktu
                            </th>
                            @foreach($activeDays as $day)
                                @php $isToday = $day === strtolower(now()->format('l')); @endphp
                                <th class="px-2 py-3.5 text-center border-r border-slate-700 last:border-r-0 {{ $isToday ? 'bg-emerald-900/40' : '' }}" style="min-width: 135px;">
                                    <div class="flex flex-col items-center gap-1">
                                        @if($isToday)
                                            <span class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-full font-bold bg-emerald-400 text-slate-950 uppercase">
                                                Hari Ini
                                            </span>
                                        @endif
                                        <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-white">{{ $dayLabels[$day] ?? $day }}</span>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @php $renderedOccupied = []; @endphp
                        @foreach($timeSlots as $slot)
                            @php
                                $order = $slot->slot_order;
                                $startFormatted = \Carbon\Carbon::parse($slot->start_time)->format('H:i');
                                $endFormatted = \Carbon\Carbon::parse($slot->end_time)->format('H:i');
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-2 py-3 border-r border-slate-200 text-center sticky left-0 bg-slate-50 z-10 w-24">
                                    <div class="flex flex-col items-center">
                                        <span class="text-xs font-bold text-slate-900">{{ $startFormatted }}</span>
                                        <span class="text-[11px] font-medium text-slate-500">{{ $endFormatted }}</span>
                                    </div>
                                </td>
                                @foreach($activeDays as $day)
                                    @php
                                        if (isset($renderedOccupied[$day][$order])) continue;

                                        $schedule = $timetable[$order][$day] ?? null;
                                        $isToday = $day === strtolower(now()->format('l'));
                                        $duration = $schedule->duration_slots ?? 1;

                                        if ($duration > 1) {
                                            for ($i = 1; $i < $duration; $i++) {
                                                $renderedOccupied[$day][$order + $i] = true;
                                            }
                                        }
                                    @endphp
                                    <td class="px-1.5 py-1.5 border-r border-slate-200 last:border-r-0 {{ $isToday ? 'bg-emerald-50/25' : '' }}"
                                        @if($duration > 1) rowspan="{{ $duration }}" @endif>
                                        @if($schedule)
                                            <div class="rounded-xl p-3 {{ $isToday ? 'bg-emerald-50 border border-emerald-200' : 'bg-slate-50 border border-slate-200' }} hover:shadow-sm transition h-full flex flex-col justify-between min-h-[65px]">
                                                <div class="mb-1.5">
                                                    <span class="text-[11px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-200 inline-block mb-1">
                                                        {{ $schedule->classroom_name_display ?? $schedule->classroom->class_name ?? '-' }}
                                                    </span>
                                                    <p class="text-xs sm:text-sm font-bold text-slate-900 leading-snug" title="{{ $schedule->subject->subject_name ?? $schedule->subject->name ?? '-' }}">
                                                        {{ $schedule->subject->subject_name ?? $schedule->subject->name ?? '-' }}
                                                    </p>
                                                </div>
                                                <div class="flex items-center justify-between mt-1 text-[11px] font-semibold text-slate-600 border-t border-slate-200/70 pt-1.5">
                                                    @if($schedule->room)
                                                        <span class="inline-flex items-center gap-1">
                                                            <i class="fas fa-door-open text-emerald-600"></i>{{ $schedule->room }}
                                                        </span>
                                                    @endif
                                                    @if($duration > 1)
                                                        <span class="font-bold bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded ml-auto">{{ $duration }} JP</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div class="rounded-xl h-full flex items-center justify-center min-h-[58px] opacity-25">
                                                <div class="w-1.5 h-1.5 bg-slate-400 rounded-full"></div>
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
        <div class="gd-card p-5">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                <i class="fas fa-palette text-emerald-600"></i>
                <span>Legenda Mata Pelajaran</span>
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
                    <span class="inline-flex items-center gap-2 bg-slate-100 text-slate-800 border border-slate-200 px-3 py-1.5 rounded-xl text-xs font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        {{ $subject->subject_name ?? $subject->name ?? '-' }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
