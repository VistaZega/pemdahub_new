@extends('layouts.guru')
@section('title', 'Jadwal Mengajar - Portal Guru')

@section('content')
<div class="space-y-6">
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
                
                <div class="mt-4 p-3 rounded-2xl border border-white/20 bg-black/20 space-y-2">
                    <p class="text-xs md:text-sm font-bold text-white flex items-center gap-2" style="color: #ffffff !important;">
                        <span class="w-6 h-6 rounded-lg bg-amber-400 border border-black flex items-center justify-center text-black shadow-sm"><i class="fas fa-chalkboard-teacher text-[10px]"></i></span>
                        Jumlah Penugasan Mengajar: <span class="font-black">{{ $totalPenugasanCount }} Penugasan</span> (Total: <span class="font-black">{{ $totalPenugasanHours }} Jam Pelajaran</span>)
                    </p>
                    <p class="text-xs md:text-sm font-bold text-white flex items-center gap-2" style="color: #ffffff !important;">
                        <span class="w-6 h-6 rounded-lg bg-emerald-400 border border-black flex items-center justify-center text-black shadow-sm"><i class="fas fa-user-tie text-[10px]"></i></span>
                        Penugasan Jabatan: <span class="font-black uppercase tracking-wide">{{ $jabatanString }}</span>
                    </p>
                </div>
            </div>
            {{-- Weekly Stats Badges --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 text-xs font-black px-3 py-1.5 rounded-xl border-2 border-black shadow-xs uppercase tracking-wider" style="background-color: #38bdf8 !important; color: #000000 !important;">
                    <i class="fas fa-clock text-black"></i> {{ $totalSessions }} Sesi
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs font-black px-3 py-1.5 rounded-xl border-2 border-black shadow-xs uppercase tracking-wider" style="background-color: #34d399 !important; color: #000000 !important;">
                    <i class="fas fa-book text-black"></i> {{ $totalJP }} JP
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs font-black px-3 py-1.5 rounded-xl border-2 border-black shadow-xs uppercase tracking-wider" style="background-color: #c084fc !important; color: #000000 !important;">
                    <i class="fas fa-chalkboard text-black"></i> {{ $uniqueClassrooms }} Kelas
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs font-black px-3 py-1.5 rounded-xl border-2 border-black shadow-xs uppercase tracking-wider" style="background-color: #fbbf24 !important; color: #000000 !important;">
                    <i class="fas fa-book-open text-black"></i> {{ $uniqueSubjects }} Mapel
                </span>
            </div>
        </div>
    </div>

    @if(empty($timetable))
        <div class="bg-white rounded-3xl shadow-xl border-2 border-black p-12 text-center">
            <div class="w-16 h-16 bg-amber-300 border-2 border-black rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl shadow-md">
                <i class="fas fa-calendar-times text-black"></i>
            </div>
            <p class="text-base font-black uppercase text-black">Belum ada jadwal mengajar yang terdaftar</p>
            <p class="text-xs font-bold text-slate-700 mt-1">Hubungi admin untuk menambahkan jadwal Anda.</p>
        </div>
    @else
        {{-- Compact Weekly Timetable Grid (Neo-Brutalism) --}}
        <div class="bg-white rounded-3xl shadow-xl border-2 border-black overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse min-w-[640px]">
                    <thead class="border-b-2 border-black" style="background-color: #090d16 !important; color: #ffffff !important;">
                        <tr>
                            <th class="px-3 py-3.5 text-xs font-black uppercase tracking-wider text-center border-b-2 border-r-2 border-black w-24 sticky left-0 z-10 text-amber-400" style="background-color: #090d16 !important;">
                                <i class="fas fa-clock mr-1 text-amber-400"></i> WAKTU
                            </th>
                            @foreach($activeDays as $day)
                                @php
                                    $isToday = $day === strtolower(now()->format('l'));
                                @endphp
                                <th class="px-2 py-3.5 text-center border-b-2 border-r-2 border-black last:border-r-0 {{ $isToday ? 'bg-amber-400/20' : '' }}" style="min-width: 130px;">
                                    <div class="flex flex-col items-center gap-1">
                                        @if($isToday)
                                            <span class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-lg font-black border border-black uppercase" style="background-color: #fbbf24 !important; color: #000000 !important;">
                                                <i class="fas fa-circle text-[4px] animate-pulse"></i> HARI INI
                                            </span>
                                        @endif
                                        <span class="text-sm font-black uppercase tracking-wider text-white">{{ $dayLabels[$day] ?? $day }}</span>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-black/10">
                        @php $renderedOccupied = []; @endphp
                        @foreach($timeSlots as $slot)
                            @php
                                $order = $slot->slot_order;
                                $startFormatted = \Carbon\Carbon::parse($slot->start_time)->format('H:i');
                                $endFormatted = \Carbon\Carbon::parse($slot->end_time)->format('H:i');
                            @endphp
                            <tr class="hover:bg-amber-50/50 transition-colors">
                                {{-- Time Column --}}
                                <td class="px-2 py-3 border-r-2 border-b-2 border-black text-center sticky left-0 bg-slate-50 z-10 w-24">
                                    <div class="flex flex-col items-center">
                                        <span class="text-xs font-black text-black">{{ $startFormatted }}</span>
                                        <span class="text-[11px] font-bold text-slate-700">{{ $endFormatted }}</span>
                                    </div>
                                </td>
                                {{-- Day Cells --}}
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
                                    <td class="px-1.5 py-1.5 border-r-2 border-b-2 border-black/20 last:border-r-0 {{ $isToday ? 'bg-amber-50/40' : '' }}" 
                                        @if($duration > 1) rowspan="{{ $duration }}" @endif>
                                        @if($schedule)
                                            <div class="rounded-2xl p-3 bg-amber-100 border-2 border-black shadow-xs hover:shadow-md transition-all duration-200 cursor-default h-full flex flex-col justify-between min-h-[65px]">
                                                <div class="mb-1">
                                                    <p class="text-[10px] font-black uppercase tracking-wider text-black bg-white px-2 py-0.5 rounded-lg border border-black inline-block mb-1">
                                                        {{ $schedule->classroom_name_display ?? $schedule->classroom->class_name ?? '-' }}
                                                    </p>
                                                    <p class="text-xs font-black text-black leading-tight uppercase" title="{{ $schedule->subject->subject_name ?? $schedule->subject->name ?? '-' }}">
                                                        {{ $schedule->subject->subject_name ?? $schedule->subject->name ?? '-' }}
                                                    </p>
                                                </div>
                                                <div class="flex items-center justify-between mt-1 text-[10px] font-bold text-black border-t border-black/10 pt-1">
                                                    @if($schedule->room)
                                                        <span class="bg-white px-1.5 py-0.5 rounded border border-black font-bold">
                                                            <i class="fas fa-door-open mr-1 text-emerald-700"></i>{{ $schedule->room }}
                                                        </span>
                                                    @endif
                                                    @if($duration > 1)
                                                        <span class="font-black bg-amber-300 px-1.5 py-0.5 rounded border border-black ml-auto">{{ $duration }} JP</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div class="rounded-xl h-full flex items-center justify-center min-h-[60px] opacity-20">
                                                <div class="w-2 h-2 bg-black rounded-full"></div>
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
        <div class="bg-white rounded-3xl shadow-xl border-2 border-black p-5">
            <h3 class="text-xs font-black text-black uppercase tracking-wider mb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-amber-400 border border-black flex items-center justify-center text-black text-xs font-black"><i class="fas fa-palette"></i></span>
                Legenda Mata Pelajaran
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
                    <span class="inline-flex items-center gap-1.5 bg-slate-100 text-black border-2 border-black px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wide shadow-xs">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 border border-black"></span>
                        {{ $subject->subject_name ?? $subject->name ?? '-' }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
