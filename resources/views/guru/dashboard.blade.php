@extends('layouts.guru')
@section('title', 'Dashboard - Portal Guru')

@section('content')
@php
    $userRole = session('active_role', auth()->user()?->role);
    $isPegawaiOnly = ($userRole === 'pegawai');
@endphp

<div class="space-y-6 max-w-9xl mx-auto">
    {{-- Header Greeting Banner (Vibrant Pop Neo-Brutalism) --}}
    <div class="relative overflow-hidden rounded-[2.5rem] shadow-2xl p-6 md:p-8 border-2 border-black" style="background: linear-gradient(135deg, #090d16 0%, #1e1b4b 50%, #064e3b 100%) !important; color: #ffffff !important;">
        {{-- Ornament blurs --}}
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-emerald-500/20 blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-64 h-64 rounded-full bg-indigo-500/20 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-2xl bg-amber-400 border-2 border-black shadow-md flex items-center justify-center overflow-hidden flex-shrink-0">
                    <img src="{{ $teacher->photo_url }}" class="w-full h-full object-cover" alt="{{ $teacher->full_name }}">
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-3 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider border border-black" style="background-color: #fbbf24 !important; color: #000000 !important;">
                            <i class="fas fa-chalkboard-teacher mr-1 text-black"></i> {{ $teacher->position ?? 'Tenaga Pendidik' }}
                        </span>
                    </div>
                    <h1 class="text-xl md:text-3xl font-black text-white leading-tight" style="color: #ffffff !important;">
                        Selamat Datang, {{ explode(' ', $teacher->full_name)[0] }}! 👋
                    </h1>
                    <p class="text-xs md:text-sm font-bold mt-1 flex flex-wrap items-center gap-x-3 text-emerald-200" style="color: #a7f3d0 !important;">
                        <span><i class="fas fa-school mr-1.5 text-amber-300"></i>{{ $teacher->school->name ?? 'Perguruan Pembda' }}</span>
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                @if($activeYear)
                    <span class="inline-flex items-center gap-2 text-xs font-black px-4 py-2 rounded-2xl border-2 border-black shadow-sm uppercase tracking-wider" style="background-color: #34d399 !important; color: #000000 !important;">
                        <i class="fas fa-calendar-alt text-black"></i> TA {{ $academicYear->year ?? $activeYear->year }}
                    </span>
                @endif
                <span class="inline-flex items-center gap-2 text-xs font-black px-4 py-2 rounded-2xl border-2 border-black shadow-sm uppercase tracking-wider" style="background-color: #fbbf24 !important; color: #000000 !important;">
                    <i class="far fa-clock text-black"></i> {{ now()->translatedFormat('l, d M Y') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Widget Surat Digital & Edaran Yayasan Terbaru --}}
    @if(isset($foundationLetters) && $foundationLetters->isNotEmpty())
    <div class="rounded-3xl p-6 text-white shadow-xl border-2 border-black relative overflow-hidden" style="background: linear-gradient(135deg, #2e1065 0%, #4c1d95 100%) !important;">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 relative z-10">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-400 text-black border-2 border-black flex items-center justify-center font-black text-xl flex-shrink-0 shadow-md">
                    <i class="fas fa-file-signature text-black"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                        <span class="px-3 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider border border-black" style="background-color: #fbbf24 !important; color: #000000 !important;">Surat & Edaran Yayasan</span>
                        @if($foundationLetters->first()->deadline_date)
                        <span class="px-3 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider border border-black" style="background-color: #f43f5e !important; color: #ffffff !important;">
                            <i class="fas fa-clock mr-1 text-white"></i>Tenggat: {{ \Carbon\Carbon::parse($foundationLetters->first()->deadline_date)->translatedFormat('d M Y') }}
                        </span>
                        @endif
                    </div>
                    <h3 class="text-base md:text-lg font-black text-white leading-snug">
                        {{ $foundationLetters->first()->title }}
                    </h3>
                    <p class="text-xs text-purple-200 mt-1 font-bold">
                        No. Surat: {{ $foundationLetters->first()->letter_number }} • Terbit: {{ \Carbon\Carbon::parse($foundationLetters->first()->effective_date)->translatedFormat('d F Y') }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0">
                <a href="{{ route('admin.letters.show', $foundationLetters->first()->id) }}" 
                   class="bg-amber-400 hover:bg-amber-300 text-black font-black px-5 py-3 rounded-2xl text-xs uppercase tracking-wider border-2 border-black shadow-md transition-all flex items-center gap-2">
                    <i class="fas fa-book-open text-black"></i> Baca Surat
                </a>
                <a href="{{ route('admin.letters.index') }}" 
                   class="bg-white/10 hover:bg-white/20 text-white font-black px-4 py-3 rounded-2xl text-xs uppercase tracking-wider border-2 border-white/20 transition-all">
                    Semua ({{ $foundationLetters->count() }})
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- Stats Cards (Neo-Brutalism Grid with Explicit High-Contrast Colors) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
        {{-- Total Kelas --}}
        <div class="bg-white rounded-3xl border-2 border-black p-5 shadow-md hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center border-2 border-black shadow-sm text-xl font-black" style="background-color: #4f46e5 !important; color: #ffffff !important;">
                    <i class="fas fa-chalkboard text-white"></i>
                </div>
                <span class="text-3xl font-black text-black leading-none">{{ $classrooms->count() }}</span>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-black">Total Kelas</p>
                <p class="text-[11px] font-bold text-slate-800 mt-0.5">Kelas diampu semester ini</p>
            </div>
        </div>

        {{-- Total Siswa --}}
        <div class="bg-white rounded-3xl border-2 border-black p-5 shadow-md hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center border-2 border-black shadow-sm text-xl font-black" style="background-color: #0284c7 !important; color: #ffffff !important;">
                    <i class="fas fa-user-graduate text-white"></i>
                </div>
                <span class="text-3xl font-black text-black leading-none">{{ $totalStudents }}</span>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-black">Total Siswa</p>
                <p class="text-[11px] font-bold text-slate-800 mt-0.5">Di semua kelas mengajar</p>
            </div>
        </div>

        {{-- Nilai Diinput --}}
        <div class="bg-white rounded-3xl border-2 border-black p-5 shadow-md hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center border-2 border-black shadow-sm text-xl font-black" style="background-color: #059669 !important; color: #ffffff !important;">
                    <i class="fas fa-chart-bar text-white"></i>
                </div>
                <span class="text-3xl font-black text-black leading-none">{{ $gradesCount }}</span>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-black">Nilai Diinput</p>
                <p class="text-[11px] font-bold text-slate-800 mt-0.5">Telah direkam di sistem</p>
            </div>
        </div>

        {{-- Jadwal Hari Ini --}}
        <div class="bg-white rounded-3xl border-2 border-black p-5 shadow-md hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center border-2 border-black shadow-sm text-xl font-black" style="background-color: #fbbf24 !important; color: #000000 !important;">
                    <i class="fas fa-calendar-check text-black"></i>
                </div>
                <span class="text-3xl font-black text-black leading-none">{{ $todaySchedules->count() }}</span>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-black">Jadwal Hari Ini</p>
                @if($nextSchedule)
                    <p class="text-[11px] font-black text-amber-700 mt-0.5 truncate">
                        <i class="fas fa-arrow-right mr-1 text-black"></i>Berikutnya: {{ $nextSchedule->timeSlot->start_time ?? $nextSchedule->start_time ?? '-' }}
                    </p>
                @elseif($currentSchedule)
                    <p class="text-[11px] font-black text-emerald-700 mt-0.5 flex items-center gap-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 border border-black animate-pulse"></span> Sedang mengajar
                    </p>
                @else
                    <p class="text-[11px] font-bold text-slate-800 mt-0.5">{{ $weeklyScheduleCount }} sesi/minggu</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Main Content: Timeline + Summary --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Jadwal Mengajar Hari Ini - Timeline --}}
        <div class="lg:col-span-2 bg-white rounded-3xl shadow-xl border-2 border-black overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b-2 border-black flex items-center justify-between" style="background-color: #090d16 !important; color: #ffffff !important;">
                <h2 class="font-black text-white text-sm uppercase tracking-wider flex items-center gap-2 leading-none">
                    <i class="fas fa-calendar-day text-amber-400 text-base"></i> Jadwal Mengajar Hari Ini
                    <span class="text-xs font-bold text-slate-300">({{ now()->translatedFormat('d M Y') }})</span>
                </h2>
                <a href="{{ route('guru.jadwal') }}" class="text-xs font-black text-amber-300 hover:text-amber-400 uppercase tracking-wider flex items-center gap-1">
                    Semua <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="p-6 flex-1">
                @if($todaySchedules->count() > 0)
                    <div class="relative">
                        {{-- Timeline line --}}
                        <div class="absolute left-[18px] top-3 bottom-3 w-1 bg-black rounded-full"></div>

                        <div class="space-y-4">
                            @foreach($groupedTodaySchedules as $timeKey => $schedulesAtTime)
                                @php
                                    $first = $schedulesAtTime->first();
                                    $sStart = $first->timeSlot->start_time ?? $first->start_time ?? null;
                                    $sEnd = $first->timeSlot->end_time ?? $first->end_time ?? null;
                                    
                                    $isCurrent = false;
                                    $isNext = false;
                                    foreach($schedulesAtTime as $s) {
                                        if ($currentSchedule && $currentSchedule->id === $s->id) $isCurrent = true;
                                        if ($nextSchedule && $nextSchedule->id === $s->id) $isNext = true;
                                    }
                                    
                                    $isPast = $sEnd && $currentTime > $sEnd;
                                @endphp
                                <div class="flex items-start gap-4 pl-1 relative group">
                                    {{-- Timeline dot --}}
                                    <div class="relative z-10 mt-3 flex-shrink-0">
                                        @if($isCurrent)
                                            <div class="w-5 h-5 border-2 border-black rounded-full shadow-md animate-bounce" style="background-color: #34d399 !important;"></div>
                                        @elseif($isNext)
                                            <div class="w-5 h-5 border-2 border-black rounded-full shadow-md" style="background-color: #fbbf24 !important;"></div>
                                        @elseif($isPast)
                                            <div class="w-4 h-4 border-2 border-black rounded-full ml-0.5" style="background-color: #94a3b8 !important;"></div>
                                        @else
                                            <div class="w-4 h-4 border-2 border-black rounded-full ml-0.5" style="background-color: #818cf8 !important;"></div>
                                        @endif
                                    </div>

                                    {{-- Schedule Card --}}
                                    <div class="flex-1 rounded-2xl p-4 border-2 border-black shadow-xs transition-all duration-200 {{ $isCurrent ? 'bg-emerald-100 border-2 border-black shadow-md' : ($isNext ? 'bg-amber-100 border-2 border-black' : ($isPast ? 'bg-slate-100 opacity-80' : 'bg-slate-50 hover:bg-amber-50')) }}">
                                        <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
                                            <div class="flex items-center gap-2">
                                                @if($isCurrent)
                                                    <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider border border-black" style="background-color: #34d399 !important; color: #000000 !important;">
                                                        <i class="fas fa-circle text-xs animate-pulse mr-1 text-black"></i> BERLANGSUNG
                                                    </span>
                                                @elseif($isNext)
                                                    <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider border border-black" style="background-color: #fbbf24 !important; color: #000000 !important;">
                                                        <i class="fas fa-arrow-right text-xs mr-1 text-black"></i> BERIKUTNYA
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs font-black text-black bg-white px-3 py-1 rounded-xl border border-black inline-block">
                                                    <i class="far fa-clock text-amber-600 mr-1"></i>{{ \Carbon\Carbon::parse($sStart)->format('H:i') }} – {{ \Carbon\Carbon::parse($sEnd)->format('H:i') }}
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-1.5 mt-2">
                                            @foreach($schedulesAtTime as $s)
                                                <div class="flex items-center justify-between">
                                                    <p class="font-black text-black text-sm uppercase tracking-wide">{{ $s->subject->subject_name ?? $s->subject->name ?? '-' }}</p>
                                                    <span class="text-xs font-black bg-white px-2.5 py-1 rounded-xl text-black border border-black shadow-xs">
                                                        <i class="fas fa-users mr-1 text-indigo-700"></i>{{ $s->classroom->class_name ?? '-' }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="flex items-center gap-3 mt-3 text-xs font-black text-black border-t border-black/10 pt-2">
                                            @php $room = $first->room; @endphp
                                            @if($room) <span class="bg-white px-2 py-0.5 rounded-lg border border-black"><i class="fas fa-door-open mr-1 text-emerald-700"></i>Ruang: {{ $room }}</span> @endif
                                            @if($first->duration_slots && $first->duration_slots > 1)
                                                <span class="bg-amber-300 text-black px-2 py-0.5 rounded-lg border border-black font-black">{{ $first->duration_slots }} JP</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-12 text-black">
                        <div class="w-16 h-16 border-2 border-black rounded-2xl flex items-center justify-center mx-auto mb-4 text-2xl shadow-md" style="background-color: #fbbf24 !important; color: #000000 !important;">
                            <i class="fas fa-coffee text-black"></i>
                        </div>
                        <p class="text-base font-black uppercase text-black">Tidak ada jadwal mengajar hari ini</p>
                        <p class="text-xs font-bold text-slate-800 mt-1">Nikmati waktu luang Anda atau persiapkan bahan ajar! ☕</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right Sidebar: Summary & Quick Actions --}}
        <div class="space-y-6">
            {{-- Wali Kelas & Rekap Tagihan --}}
            @if($homeroomClassroom)
            <div class="bg-white rounded-3xl shadow-xl border-2 border-black p-6 relative overflow-hidden">
                <h2 class="font-black text-black mb-4 flex items-center gap-2 text-xs uppercase tracking-wider">
                    <span class="w-8 h-8 rounded-xl border border-black flex items-center justify-center font-black shadow-xs" style="background-color: #fbbf24 !important; color: #000000 !important;"><i class="fas fa-star text-xs text-black"></i></span>
                    Wali Kelas Management
                </h2>
                <div class="bg-emerald-100 border-2 border-black rounded-2xl p-4 relative mb-4 shadow-sm">
                    <p class="font-black text-black text-xl leading-tight">{{ $homeroomClassroom->class_name }}</p>
                    <p class="text-xs font-bold text-black mt-1 flex items-center gap-1.5">
                        <i class="fas fa-users text-emerald-800"></i>
                        {{ $homeroomClassroom->students_count ?? $homeroomClassroom->students->count() }} Siswa Terdaftar
                    </p>
                    <a href="{{ route('guru.siswa-kelas', $homeroomClassroom->id) }}" 
                       class="inline-flex items-center gap-2 mt-4 text-xs font-black bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl border-2 border-black transition shadow-md uppercase tracking-wider">
                        <i class="fas fa-eye text-amber-300"></i> Lihat Data Siswa
                    </a>
                </div>

                @if(isset($homeroomBillingStats))
                <div class="border-t-2 border-black pt-4 mt-3">
                    <h3 class="font-black text-black flex items-center gap-2 text-xs uppercase tracking-wider mb-3">
                        <i class="fas fa-file-invoice-dollar text-indigo-700"></i> Progress SPP & Biaya Kelas
                    </h3>
                    
                    <div class="flex justify-between items-end mb-2">
                        <div>
                            <p class="text-[10px] text-black font-black uppercase">Item Lunas</p>
                            <p class="text-sm font-black text-emerald-700">{{ $homeroomBillingStats->lunas_count }} Biaya</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] text-black font-black uppercase">Tunggakan</p>
                            <p class="text-sm font-black text-rose-600">{{ $homeroomBillingStats->belum_bayar_count }} Biaya</p>
                        </div>
                    </div>
                    
                    {{-- Progress Bar --}}
                    <div class="w-full bg-slate-200 border-2 border-black rounded-full h-4 mb-2 overflow-hidden p-0.5">
                        <div class="bg-emerald-500 h-full rounded-full transition-all duration-1000 border-r border-black" style="width: {{ $homeroomBillingStats->percentage }}%"></div>
                    </div>
                    
                    <div class="flex justify-between items-center text-[10px] font-black text-black">
                        <span>{{ $homeroomBillingStats->percentage }}% Terbayar</span>
                        <span>{{ $homeroomBillingStats->lunas_count }} dari {{ $homeroomBillingStats->due_bills }} Tagihan</span>
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- Weekly Overview --}}
            <div class="bg-white rounded-3xl shadow-xl border-2 border-black p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-black uppercase tracking-widest">Sesi Minggu Ini</p>
                        <p class="text-2xl font-black text-black">{{ $weeklyScheduleCount }} <span class="text-xs font-bold text-slate-800">Jadwal</span></p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-black text-black uppercase tracking-widest">Siswa Diampu</p>
                        <p class="text-2xl font-black text-black">{{ $totalStudents }} <span class="text-xs font-bold text-slate-800">Total</span></p>
                    </div>
                </div>
            </div>

            {{-- Reputation / Pembda Elite System --}}
            <div class="rounded-3xl shadow-xl border-2 border-black p-6 relative overflow-hidden text-white" style="background-color: #090d16 !important;">
                <div class="flex items-center justify-between mb-4 relative z-10 border-b border-slate-800 pb-3">
                    <h2 class="font-black text-white flex items-center gap-2 text-xs uppercase tracking-wider" style="color: #ffffff !important;">
                        <i class="fas fa-award text-amber-400"></i> Reputation Score
                    </h2>
                    <a href="{{ route('reputation.leaderboard') }}" class="text-xs font-black text-amber-300 hover:text-amber-400 uppercase tracking-wider">Papan Skor →</a>
                </div>

                <div class="text-center p-5 bg-slate-900 rounded-2xl shadow-md border-2 border-slate-700 mb-4 relative overflow-hidden text-white">
                    <div class="absolute top-2 right-2">
                        <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase border border-amber-400" style="background-color: #fbbf24 !important; color: #000000 !important;">Rank #{{ $rank }}</span>
                    </div>
                    
                    <div class="relative z-10 pt-2">
                        <div class="text-4xl font-black text-white mb-0.5">{{ number_format($reputation->total_points) }}</div>
                        <div class="text-[10px] font-black text-amber-300 uppercase tracking-widest mb-3">Elite Score</div>
                        
                        <div class="inline-block px-4 py-1.5 {{ $reputation->level_color }} text-white text-xs font-black rounded-xl shadow-sm uppercase tracking-wider border border-black">
                            {{ $reputation->level_name }}
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="mt-4 w-full bg-slate-800 rounded-full h-3 overflow-hidden border border-slate-700 p-0.5">
                        <div class="bg-amber-400 h-full rounded-full" style="width: {{ $reputation->progress_percentage }}%"></div>
                    </div>
                </div>

                <div class="space-y-2.5 relative z-10">
                    <h3 class="text-[10px] font-black text-amber-300 uppercase tracking-wider">Kontribusi Terakhir</h3>
                    @forelse($reputationLogs as $log)
                    <div class="flex items-center justify-between text-xs p-3 rounded-xl bg-slate-900 border border-slate-800 text-white">
                        <div class="flex flex-col max-w-[70%]">
                            <span class="font-bold text-slate-100 leading-tight truncate">{{ $log->description }}</span>
                            <span class="text-[10px] text-amber-300 font-bold mt-0.5">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        <span class="font-black text-amber-400">
                            {{ $log->points >= 0 ? '+' : '' }}{{ $log->points }}
                        </span>
                    </div>
                    @empty
                    <p class="text-xs text-center text-slate-400 italic py-2">Belum ada aktivitas kontribusi</p>
                    @endforelse
                </div>
            </div>

            {{-- Quick Links (Aksi Cepat Pop Neo-Brutalism) --}}
            <div class="bg-white rounded-3xl shadow-xl border-2 border-black p-6">
                <h2 class="font-black text-black mb-4 flex items-center gap-2 text-xs uppercase tracking-wider">
                    <span class="w-8 h-8 rounded-xl border border-black flex items-center justify-center font-black shadow-xs" style="background-color: #fbbf24 !important; color: #000000 !important;"><i class="fas fa-bolt text-xs text-black"></i></span>
                    Aksi Cepat Guru
                </h2>
                <div class="grid grid-cols-2 gap-3">
                    @if($isPegawaiOnly)
                    <a href="{{ route('guru.absensi.saya') }}" class="flex flex-col items-center justify-center gap-2 p-3.5 rounded-2xl hover:bg-amber-300 text-black border-2 border-black shadow-xs transition-all duration-200 group" style="background-color: #99f6e4 !important;">
                        <i class="fas fa-clipboard-user text-xl group-hover:scale-110 transition-transform text-black"></i>
                        <span class="text-xs font-black text-center uppercase tracking-wider text-black">Absensi Saya</span>
                    </a>
                    <a href="{{ route('guru.leaves.index') }}" class="flex flex-col items-center justify-center gap-2 p-3.5 rounded-2xl hover:bg-amber-300 text-black border-2 border-black shadow-xs transition-all duration-200 group" style="background-color: #fef08a !important;">
                        <i class="fas fa-calendar-times text-xl group-hover:scale-110 transition-transform text-black"></i>
                        <span class="text-xs font-black text-center uppercase tracking-wider text-black">Cuti Pegawai</span>
                    </a>
                    <a href="{{ route('admin.letters.index') }}" class="flex flex-col items-center justify-center gap-2 p-3.5 rounded-2xl hover:bg-amber-300 text-black border-2 border-black shadow-xs transition-all duration-200 group" style="background-color: #e9d5ff !important;">
                        <i class="fas fa-file-signature text-xl group-hover:scale-110 transition-transform text-black"></i>
                        <span class="text-xs font-black text-center uppercase tracking-wider text-black">Surat Edaran</span>
                    </a>
                    <a href="{{ route('guru.profil') }}" class="flex flex-col items-center justify-center gap-2 p-3.5 rounded-2xl hover:bg-amber-300 text-black border-2 border-black shadow-xs transition-all duration-200 group" style="background-color: #a7f3d0 !important;">
                        <i class="fas fa-user text-xl group-hover:scale-110 transition-transform text-black"></i>
                        <span class="text-xs font-black text-center uppercase tracking-wider text-black">Profil Saya</span>
                    </a>
                    @else
                    <a href="{{ route('guru.absensi') }}" class="flex flex-col items-center justify-center gap-2 p-3.5 rounded-2xl hover:bg-amber-300 text-black border-2 border-black shadow-xs transition-all duration-200 group" style="background-color: #e9d5ff !important;">
                        <i class="fas fa-clipboard-check text-xl group-hover:scale-110 transition-transform text-black"></i>
                        <span class="text-xs font-black text-center uppercase tracking-wider text-black">Absensi</span>
                    </a>
                    <a href="{{ route('guru.nilai') }}" class="flex flex-col items-center justify-center gap-2 p-3.5 rounded-2xl hover:bg-amber-300 text-black border-2 border-black shadow-xs transition-all duration-200 group" style="background-color: #a7f3d0 !important;">
                        <i class="fas fa-chart-bar text-xl group-hover:scale-110 transition-transform text-black"></i>
                        <span class="text-xs font-black text-center uppercase tracking-wider text-black">Input Nilai</span>
                    </a>
                    <a href="{{ route('guru.jadwal') }}" class="flex flex-col items-center justify-center gap-2 p-3.5 rounded-2xl hover:bg-amber-300 text-black border-2 border-black shadow-xs transition-all duration-200 group" style="background-color: #bae6fd !important;">
                        <i class="fas fa-calendar-alt text-xl group-hover:scale-110 transition-transform text-black"></i>
                        <span class="text-xs font-black text-center uppercase tracking-wider text-black">Jadwal</span>
                    </a>
                    <a href="{{ route('guru.kelas') }}" class="flex flex-col items-center justify-center gap-2 p-3.5 rounded-2xl hover:bg-amber-300 text-black border-2 border-black shadow-xs transition-all duration-200 group" style="background-color: #c7d2fe !important;">
                        <i class="fas fa-users text-xl group-hover:scale-110 transition-transform text-black"></i>
                        <span class="text-xs font-black text-center uppercase tracking-wider text-black">Daftar Kelas</span>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
