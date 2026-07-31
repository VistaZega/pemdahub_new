@extends('layouts.guru')
@section('title', 'Dashboard - Portal Guru')

@section('content')
@php
    $userRole = session('active_role', auth()->user()?->role);
    $isPegawaiOnly = ($userRole === 'pegawai');
@endphp

<div class="space-y-6">
    {{-- Compact Greeting Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white rounded-xl shadow-sm border border-gray-100 px-5 py-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-teal-600 rounded-xl flex items-center justify-center text-2xl shadow-md flex-shrink-0">
                <img src="{{ $teacher->photo_url }}" class="w-full h-full object-cover rounded-xl" alt="{{ $teacher->full_name }}">
            </div>
            <div>
                <h1 class="text-lg md:text-xl font-bold text-gray-800">Selamat Datang, {{ explode(' ', $teacher->full_name)[0] }}!</h1>
                <p class="text-xs text-gray-500 mt-0.5 flex flex-wrap items-center gap-x-2">
                    <span><i class="fas fa-school mr-1 text-emerald-500"></i>{{ $teacher->school->name ?? '' }}</span>
                    @if($teacher->position) <span class="text-gray-300">·</span> <span>{{ $teacher->position }}</span> @endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @if($activeYear)
                <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-lg text-xs font-semibold">
                    <i class="fas fa-calendar-alt text-xs"></i> {{ $activeYear->year }}
                </span>
            @endif
            <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 px-3 py-1.5 rounded-lg text-xs font-semibold">
                <i class="far fa-clock text-xs"></i> {{ now()->translatedFormat('l, d M Y') }}
            </span>
        </div>
    </div>

    {{-- Widget Surat Digital & Edaran Yayasan Terbaru --}}
    @if(isset($foundationLetters) && $foundationLetters->isNotEmpty())
    <div class="bg-gradient-to-r from-violet-900 via-purple-900 to-indigo-950 rounded-2xl p-5 text-white shadow-lg border border-violet-700/50">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-400 text-gray-900 flex items-center justify-center font-black text-lg flex-shrink-0 shadow">
                    <i class="fas fa-file-signature"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="bg-amber-400/20 text-amber-300 border border-amber-400/30 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider">Surat & Edaran Yayasan Terbaru</span>
                        @if($foundationLetters->first()->deadline_date)
                        <span class="bg-rose-500/20 text-rose-300 border border-rose-400/30 px-2 py-0.5 rounded text-[10px] font-semibold">
                            <i class="fas fa-clock mr-1"></i>Tenggat: {{ \Carbon\Carbon::parse($foundationLetters->first()->deadline_date)->translatedFormat('d M Y') }}
                        </span>
                        @endif
                    </div>
                    <h3 class="text-base md:text-lg font-bold text-white leading-snug">
                        {{ $foundationLetters->first()->title }}
                    </h3>
                    <p class="text-xs text-violet-200 mt-1 font-mono">
                        No. Surat: {{ $foundationLetters->first()->letter_number }} • Tanggal Terbit: {{ \Carbon\Carbon::parse($foundationLetters->first()->effective_date)->translatedFormat('d F Y') }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('admin.letters.show', $foundationLetters->first()->id) }}" class="bg-amber-400 hover:bg-amber-500 text-gray-950 font-extrabold px-4 py-2 rounded-xl text-xs shadow-md transition-all flex items-center gap-2">
                    <i class="fas fa-book-open"></i> Baca Surat Digital
                </a>
                <a href="{{ route('admin.letters.index') }}" class="bg-white/10 hover:bg-white/20 text-white font-semibold px-3 py-2 rounded-xl text-xs transition-all">
                    Lihat Semua ({{ $foundationLetters->count() }})
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Kelas --}}
        <div class="stat-card group">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-11 h-11 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform flex-shrink-0">
                    <i class="fas fa-chalkboard text-white text-sm"></i>
                </div>
                <div>
                    <span class="text-3xl font-bold text-gray-800 leading-none">{{ $classrooms->count() }}</span>
                </div>
            </div>
            <p class="text-sm font-semibold text-gray-700">Total Kelas</p>
            <p class="text-xs text-gray-400 mt-0.5">Kelas diampu semester ini</p>
        </div>
        {{-- Total Siswa --}}
        <div class="stat-card group">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-11 h-11 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform flex-shrink-0">
                    <i class="fas fa-user-graduate text-white text-sm"></i>
                </div>
                <div>
                    <span class="text-3xl font-bold text-gray-800 leading-none">{{ $totalStudents }}</span>
                </div>
            </div>
            <p class="text-sm font-semibold text-gray-700">Total Siswa</p>
            <p class="text-xs text-gray-400 mt-0.5">Di semua kelas</p>
        </div>
        {{-- Nilai Diinput --}}
        <div class="stat-card group">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-11 h-11 bg-gradient-to-br from-green-400 to-emerald-600 rounded-xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform flex-shrink-0">
                    <i class="fas fa-chart-bar text-white text-sm"></i>
                </div>
                <div>
                    <span class="text-3xl font-bold text-gray-800 leading-none">{{ $gradesCount }}</span>
                </div>
            </div>
            <p class="text-sm font-semibold text-gray-700">Nilai Diinput</p>
            <p class="text-xs text-gray-400 mt-0.5">Semester ini</p>
        </div>
        {{-- Jadwal Hari Ini --}}
        <div class="stat-card group relative overflow-hidden">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-11 h-11 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform flex-shrink-0">
                    <i class="fas fa-calendar-check text-white text-sm"></i>
                </div>
                <div>
                    <span class="text-3xl font-bold text-gray-800 leading-none">{{ $todaySchedules->count() }}</span>
                </div>
            </div>
            <p class="text-sm font-semibold text-gray-700">Jadwal Hari Ini</p>
            @if($nextSchedule)
                <p class="text-xs text-amber-600 mt-0.5 font-medium">
                    <i class="fas fa-arrow-right text-xs mr-0.5"></i> Berikutnya: {{ $nextSchedule->timeSlot->start_time ?? $nextSchedule->start_time ?? '-' }}
                </p>
            @elseif($currentSchedule)
                <p class="text-xs text-emerald-600 mt-0.5 font-medium">
                    <i class="fas fa-circle text-xs mr-0.5 animate-pulse"></i> Sedang mengajar
                </p>
            @else
                <p class="text-xs text-gray-400 mt-0.5">{{ $weeklyScheduleCount }} sesi/minggu</p>
            @endif
        </div>
    </div>

    {{-- Main Content: Timeline + Summary --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Jadwal Mengajar Hari Ini - Timeline --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-calendar-day text-blue-500"></i> Jadwal Mengajar Hari Ini
                    <span class="text-xs font-normal text-gray-400">({{ now()->translatedFormat('l, d M Y') }})</span>
                </h2>
                <a href="{{ route('guru.jadwal') }}" class="text-xs text-emerald-600 hover:underline font-medium">Lihat Semua →</a>
            </div>
            <div class="p-5">
                @if($todaySchedules->count() > 0)
                    <div class="relative">
                        {{-- Timeline line --}}
                        <div class="absolute left-[18px] top-2 bottom-2 w-0.5 bg-gradient-to-b from-emerald-200 via-emerald-300 to-emerald-100 rounded-full"></div>

                        <div class="space-y-1">
                            @foreach($groupedTodaySchedules as $timeKey => $schedulesAtTime)
                                @php
                                    $first = $schedulesAtTime->first();
                                    $sStart = $first->timeSlot->start_time ?? $first->start_time ?? null;
                                    $sEnd = $first->timeSlot->end_time ?? $first->end_time ?? null;
                                    
                                    // Status detection
                                    $isCurrent = false;
                                    $isNext = false;
                                    foreach($schedulesAtTime as $s) {
                                        if ($currentSchedule && $currentSchedule->id === $s->id) $isCurrent = true;
                                        if ($nextSchedule && $nextSchedule->id === $s->id) $isNext = true;
                                    }
                                    
                                    $isPast = $sEnd && $currentTime > $sEnd;
                                @endphp
                                <div class="flex items-start gap-4 pl-1 py-2 relative group">
                                    {{-- Timeline dot --}}
                                    <div class="relative z-10 mt-3 flex-shrink-0">
                                        @if($isCurrent)
                                            <div class="w-[14px] h-[14px] bg-emerald-500 rounded-full ring-4 ring-emerald-100 shadow-sm animate-pulse"></div>
                                        @elseif($isNext)
                                            <div class="w-[14px] h-[14px] bg-amber-400 rounded-full ring-4 ring-amber-50 shadow-sm"></div>
                                        @elseif($isPast)
                                            <div class="w-[10px] h-[10px] bg-gray-300 rounded-full ml-0.5 mt-0.5"></div>
                                        @else
                                            <div class="w-[10px] h-[10px] bg-emerald-300 rounded-full ml-0.5 mt-0.5"></div>
                                        @endif
                                    </div>

                                    {{-- Schedule Card --}}
                                    <div class="flex-1 rounded-xl p-3.5 transition-all duration-200 {{ $isCurrent ? 'bg-emerald-50 border border-emerald-200 shadow-sm ring-1 ring-emerald-100' : ($isNext ? 'bg-amber-50/60 border border-amber-100' : ($isPast ? 'bg-gray-50/80 border border-gray-100 opacity-60' : 'bg-gray-50 border border-gray-100 hover:bg-gray-100/80')) }}">
                                        <div class="flex items-center justify-between mb-1.5">
                                            <div class="flex items-center gap-2">
                                                @if($isCurrent)
                                                    <span class="inline-flex items-center gap-1 text-xs bg-emerald-500 text-white px-2 py-0.5 rounded-full font-bold shadow-sm">
                                                        <i class="fas fa-circle text-xs animate-pulse"></i> BERLANGSUNG
                                                    </span>
                                                @elseif($isNext)
                                                    <span class="inline-flex items-center gap-1 text-xs bg-amber-400 text-white px-2 py-0.5 rounded-full font-bold">
                                                        <i class="fas fa-arrow-right text-xs"></i> BERIKUTNYA
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs font-bold {{ $isCurrent ? 'text-emerald-700' : ($isNext ? 'text-amber-700' : 'text-gray-600') }}">{{ \Carbon\Carbon::parse($sStart)->format('H:i') }} – {{ \Carbon\Carbon::parse($sEnd)->format('H:i') }}</p>
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-1">
                                            @foreach($schedulesAtTime as $s)
                                                <div class="flex items-center justify-between">
                                                    <p class="font-bold text-gray-800 text-sm italic">{{ $s->subject->subject_name ?? $s->subject->name ?? '-' }}</p>
                                                    <span class="text-xs bg-white px-2 py-0.5 rounded-full text-gray-500 border border-gray-100">
                                                        <i class="fas fa-users mr-1 text-gray-400"></i>{{ $s->classroom->class_name ?? '-' }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                                            @php $room = $first->room; @endphp
                                            @if($room) <span><i class="fas fa-door-open mr-1 text-gray-300"></i>{{ $room }}</span> @endif
                                            @if($first->duration_slots && $first->duration_slots > 1)
                                                <span class="bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded-full font-semibold">{{ $first->duration_slots }} JP</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-10 text-gray-400">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-coffee text-3xl text-gray-300"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Tidak ada jadwal mengajar hari ini</p>
                        <p class="text-xs text-gray-400 mt-1">Nikmati waktu luang Anda! ☕</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right Sidebar: Summary --}}
        <div class="space-y-4">
            {{-- Wali Kelas & Rekap Tagihan --}}
            @if($homeroomClassroom)
            <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 p-5 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-emerald-50 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <h2 class="font-bold text-emerald-700 mb-3 flex items-center gap-2 text-sm relative">
                    <i class="fas fa-star text-amber-400"></i> Wali Kelas
                </h2>
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-xl p-4 relative mb-4">
                    <p class="font-bold text-gray-800 text-lg">{{ $homeroomClassroom->class_name }}</p>
                    <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                        <i class="fas fa-user-friends text-emerald-400"></i>
                        {{ $homeroomClassroom->students_count ?? $homeroomClassroom->students->count() }} siswa
                    </p>
                    <a href="{{ route('guru.siswa-kelas', $homeroomClassroom->id) }}" class="inline-flex items-center gap-1.5 mt-3 text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition shadow-sm">
                        <i class="fas fa-eye"></i> Lihat Siswa
                    </a>
                </div>

                @if(isset($homeroomBillingStats))
                <div class="border-t border-gray-100 pt-4 mt-2">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2 text-xs uppercase tracking-wider mb-3">
                        <i class="fas fa-file-invoice-dollar text-indigo-500"></i> Progress Biaya Pendidikan
                    </h3>
                    
                    <div class="flex justify-between items-end mb-2">
                        <div>
                            <p class="text-[10px] text-gray-500 font-bold uppercase">Item Lunas</p>
                            <p class="text-sm font-bold text-indigo-600">{{ $homeroomBillingStats->lunas_count }} Biaya</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] text-gray-500 font-bold uppercase">Item Tunggakan</p>
                            <p class="text-sm font-bold text-rose-500">{{ $homeroomBillingStats->belum_bayar_count }} Biaya</p>
                        </div>
                    </div>
                    
                    {{-- Progress Bar --}}
                    <div class="w-full bg-gray-100 rounded-full h-2 mb-2 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-400 to-indigo-600 h-2 rounded-full transition-all duration-1000" style="width: {{ $homeroomBillingStats->percentage }}%"></div>
                    </div>
                    
                    <div class="flex justify-between items-center text-[10px] font-bold">
                        <span class="text-gray-500">{{ $homeroomBillingStats->percentage }}% Lunas</span>
                        <span class="text-gray-400">{{ $homeroomBillingStats->lunas_count }} dari {{ $homeroomBillingStats->due_bills }} Biaya Wajib</span>
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- Weekly Overview (Moved and simplified) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Sesi Minggu Ini</p>
                        <p class="text-xl font-bold text-gray-800">{{ $weeklyScheduleCount }} <span class="text-xs font-normal text-gray-400">Jadwal</span></p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Siswa Diampu</p>
                        <p class="text-xl font-bold text-gray-800">{{ $totalStudents }} <span class="text-xs font-normal text-gray-400">Total</span></p>
                    </div>
                </div>
            </div>

            {{-- Reputation / Pembda Elite System --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5 relative overflow-hidden group">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-700 opacity-50"></div>
                
                <div class="flex items-center justify-between mb-4 relative z-10">
                    <h2 class="font-bold text-gray-800 flex items-center gap-2 text-sm uppercase tracking-wider">
                        <i class="fas fa-award text-blue-500"></i> Reputation
                    </h2>
                    <a href="{{ route('reputation.leaderboard') }}" class="text-xs font-semibold text-blue-600 hover:underline uppercase tracking-wider">Papan Skor</a>
                </div>

                <div class="text-center p-6 bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl shadow-lg border border-slate-700 mb-4 relative overflow-hidden group/card text-white">
                    <div class="absolute top-0 right-0 p-2">
                        <span class="bg-blue-500/20 text-blue-400 text-xs font-semibold px-2 py-0.5 rounded-full uppercase border border-blue-500/30">Rank #{{ $rank }}</span>
                    </div>
                    
                    <div class="relative z-10">
                        <div class="text-3xl font-bold mb-1 drop-shadow-sm">{{ number_format($reputation->total_points) }}</div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Elite Score</div>
                        
                        <div class="inline-block px-4 py-1.5 {{ $reputation->level_color }} text-white text-xs font-semibold rounded-full shadow-md uppercase tracking-wider border border-gray-100">
                            {{ $reputation->level_name }}
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="mt-6 w-full bg-white/10 rounded-full h-1 overflow-hidden">
                        <div class="bg-blue-500 h-full rounded-full shadow-[0_0_10px_rgba(59,130,246,0.5)]" style="width: {{ $reputation->progress_percentage }}%"></div>
                    </div>
                </div>

                <div class="space-y-3 relative z-10">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Kontribusi Terakhir</h3>
                    @forelse($reputationLogs as $log)
                    <div class="flex items-center justify-between text-xs p-2.5 rounded-xl bg-gray-50 border border-gray-100 hover:bg-white transition">
                        <div class="flex flex-col max-w-[70%]">
                            <span class="font-bold text-gray-700 leading-tight truncate">{{ $log->description }}</span>
                            <span class="text-xs text-gray-400 mt-0.5">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        <span class="font-bold {{ $log->points >= 0 ? 'text-blue-600' : 'text-rose-600' }}">
                            {{ $log->points >= 0 ? '+' : '' }}{{ $log->points }}
                        </span>
                    </div>
                    @empty
                    <p class="text-xs text-center text-gray-400 italic py-2">Belum ada aktivitas kontribusi</p>
                    @endforelse
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h2 class="font-bold text-gray-800 mb-3 flex items-center gap-2 text-sm">
                    <i class="fas fa-bolt text-amber-500"></i> Aksi Cepat
                </h2>
                <div class="grid grid-cols-2 gap-2">
                    @if($isPegawaiOnly)
                    <a href="{{ route('guru.absensi.saya') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-700 transition group">
                        <i class="fas fa-clipboard-user text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[11px] font-medium text-center">Absensi Saya</span>
                    </a>
                    <a href="{{ route('guru.leaves.index') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 transition group">
                        <i class="fas fa-calendar-times text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[11px] font-medium text-center">Cuti Pegawai</span>
                    </a>
                    <a href="{{ route('admin.letters.index') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 transition group">
                        <i class="fas fa-file-signature text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[11px] font-medium text-center">Surat Edaran</span>
                    </a>
                    <a href="{{ route('guru.profil') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition group">
                        <i class="fas fa-user text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[11px] font-medium text-center">Profil Saya</span>
                    </a>
                    @else
                    <a href="{{ route('guru.absensi') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 transition group">
                        <i class="fas fa-clipboard-check text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[11px] font-medium text-center">Absensi</span>
                    </a>
                    <a href="{{ route('guru.nilai') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-green-50 hover:bg-green-100 text-green-700 transition group">
                        <i class="fas fa-chart-bar text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[11px] font-medium text-center">Nilai</span>
                    </a>
                    <a href="{{ route('guru.jadwal') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 transition group">
                        <i class="fas fa-calendar-alt text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[11px] font-medium text-center">Jadwal</span>
                    </a>
                    <a href="{{ route('guru.kelas') }}" class="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition group">
                        <i class="fas fa-users text-lg group-hover:scale-110 transition-transform"></i>
                        <span class="text-[11px] font-medium text-center">Kelas</span>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
