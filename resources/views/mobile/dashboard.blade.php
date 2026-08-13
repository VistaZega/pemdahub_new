@extends('mobile.layouts.app')

@section('title', 'Beranda Playful - PembdaHUB Mobile Pro')

@section('content')
<div class="space-y-5 pt-2">
    <!-- Hero Banner Card (Playful 3D Clay Banner) -->
    <div class="clay-blue p-6 relative overflow-hidden">
        <div class="flex items-center space-x-4 relative z-10">
            @if($student && $student->photo_url)
                <img src="{{ $student->photo_url }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-2xl object-cover border-4 border-white/60 shadow-md">
            @else
                <div class="w-16 h-16 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-white font-black text-2xl border-2 border-white shadow-md">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
            @endif

            <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-2 mb-1">
                    @php $activeRole = session('active_role', $user->role); @endphp
                    <span class="px-3 py-0.5 rounded-full bg-white/30 text-white text-[10px] font-black tracking-wide uppercase border border-white/40 shadow-sm backdrop-blur-sm">
                        {{ strtoupper($activeRole) }}
                    </span>
                    @if($student && $student->school)
                        <span class="text-[10px] text-blue-100 font-extrabold truncate">{{ $student->school->name }}</span>
                    @endif
                </div>
                <h2 class="text-xl font-black text-white truncate leading-tight tracking-tight">Halo, {{ strtok($user->name, ' ') }}! 🚀</h2>
                <p class="text-xs text-blue-100/90 mt-1 font-bold truncate">
                    @if($student)
                        NISN: {{ $student->nisn ?? $student->nis ?? '-' }}
                    @elseif($teacher)
                        NIP: {{ $teacher->nip ?? '-' }}
                    @else
                        {{ $user->email }}
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Progress Tracking Demo Card (Clay Progress Bar) -->
    @if($activeRole === 'siswa')
    <div class="clay-card p-5 space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-rocket text-orange-500"></i> Progres Belajar & Kehadiran
            </h3>
            <span class="text-xs font-black text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-200">75% Selesai</span>
        </div>

        <!-- 3D Clay Progress Bar -->
        <div class="w-full bg-slate-100 rounded-full h-5 p-1 shadow-inner relative overflow-hidden border border-slate-200">
            <div class="bg-gradient-to-r from-orange-400 via-amber-400 to-yellow-400 h-full rounded-full transition-all duration-500 shadow-md relative" style="width: 75%">
                <div class="absolute right-1 top-0 bottom-0 flex items-center">
                    <span class="text-[9px] font-black text-white px-1">🚀</span>
                </div>
            </div>
        </div>

        <p class="text-[11px] font-bold text-slate-600">Semangat terus! Kamu sudah menyelesaikan sebagian besar target minggu ini! 🌟</p>
    </div>
    @endif

    <!-- Quick Access Grid (Playful 3D Claymorphism Buttons) -->
    <div>
        <div class="flex items-center justify-between mb-3 px-1">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">Modul PembdaHUB</h3>
            <span class="text-[10px] font-black text-blue-600">Akses Cepat 3D</span>
        </div>
        
        @php $activeRole = session('active_role', $user->role); @endphp
        @if($activeRole === 'siswa')
        <div class="grid grid-cols-4 gap-3">
            <!-- Jadwal (Clay Blue) -->
            <a href="{{ route('mobile.jadwal') }}" class="clay-blue p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📅
                </div>
                <span class="text-[11px] font-black">Jadwal</span>
            </a>

            <!-- Nilai (Clay Green) -->
            <a href="{{ route('mobile.nilai') }}" class="clay-green p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📈
                </div>
                <span class="text-[11px] font-black">Nilai</span>
            </a>

            <!-- Tagihan SPP (Clay Yellow) -->
            <a href="{{ route('mobile.tagihan') }}" class="clay-yellow p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    💳
                </div>
                <span class="text-[11px] font-black">Tagihan</span>
            </a>

            <!-- CBT Ujian (Clay Pink) -->
            <a href="{{ route('mobile.cbt') }}" class="clay-pink p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    💻
                </div>
                <span class="text-[11px] font-black">CBT</span>
            </a>

            <!-- PKL / Tugas Akhir (Clay Orange) -->
            <a href="{{ route('mobile.pkl') }}" class="clay-orange p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    💼
                </div>
                <span class="text-[11px] font-black">PKL</span>
            </a>

            <!-- Pembda Space (Clay Purple) -->
            <a href="{{ route('mobile.space.index') }}" class="clay-purple p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    💬
                </div>
                <span class="text-[11px] font-black">Space</span>
            </a>

            <!-- LMS (Clay Blue) -->
            <a href="{{ route('mobile.lms.index') }}" class="clay-blue p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📚
                </div>
                <span class="text-[11px] font-black">LMS</span>
            </a>

            <!-- Absensi (Clay Cyan) -->
            <a href="{{ route('mobile.absensi.index') }}" class="clay-cyan p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📌
                </div>
                <span class="text-[11px] font-black">Absensi</span>
            </a>
        </div>
        @else
        <!-- Menu Guru (Clay Cards) -->
        <div class="grid grid-cols-4 gap-3">
            <!-- Jadwal Mengajar -->
            <a href="{{ route('mobile.guru.jadwal') }}" class="clay-purple p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    👨‍🏫
                </div>
                <span class="text-[11px] font-black">Jadwal</span>
            </a>

            <!-- Input Absensi Kelas -->
            <a href="{{ route('mobile.guru.absensi.input') }}" class="clay-green p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📋
                </div>
                <span class="text-[11px] font-black">Absen</span>
            </a>

            <!-- Periksa Tugas -->
            <a href="{{ route('mobile.guru.tugas') }}" class="clay-yellow p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📝
                </div>
                <span class="text-[11px] font-black">Nilai</span>
            </a>

            <!-- Space -->
            <a href="{{ route('mobile.space.index') }}" class="clay-purple p-3.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-12 h-12 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-2xl mb-1.5 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    💬
                </div>
                <span class="text-[11px] font-black">Space</span>
            </a>
        </div>
        @endif
    </div>

    <!-- Attendance Summary Widget (Clay Cards) -->
    @if($activeRole === 'siswa')
    <div class="clay-card p-5 space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div class="w-3 h-3 rounded-full bg-emerald-500 animate-ping"></div>
                <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Rekap Absensi Bulan Ini</h3>
            </div>
            <a href="{{ route('mobile.absensi.index') }}" class="text-[11px] font-black text-blue-600 hover:text-blue-700">Detail <i class="fa-solid fa-chevron-right text-[9px]"></i></a>
        </div>

        <div class="grid grid-cols-3 gap-2.5">
            <div class="clay-green p-3 text-center">
                <span class="text-2xl font-black leading-none">{{ $attendanceStats['hadir'] }}</span>
                <span class="block text-[10px] font-black uppercase mt-1">Hadir</span>
            </div>
            <div class="clay-yellow p-3 text-center">
                <span class="text-2xl font-black leading-none">{{ $attendanceStats['terlambat'] }}</span>
                <span class="block text-[10px] font-black uppercase mt-1">Terlambat</span>
            </div>
            <div class="clay-pink p-3 text-center">
                <span class="text-2xl font-black leading-none">{{ $attendanceStats['sakit'] + $attendanceStats['izin'] + $attendanceStats['alpha'] }}</span>
                <span class="block text-[10px] font-black uppercase mt-1">Izin/Alpha</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Pembda Space Recent Feed Widget -->
    <div>
        <div class="flex items-center justify-between mb-3 px-1">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">Pembda Space Terbaru</h3>
            <a href="{{ route('mobile.space.index') }}" class="text-[11px] font-black text-blue-600 hover:text-blue-700">Lihat Semua <i class="fa-solid fa-arrow-right text-[9px]"></i></a>
        </div>

        <div class="space-y-3">
            @forelse($recentDiscussions as $thread)
                <a href="{{ route('mobile.space.show', $thread->id) }}" class="clay-card p-4.5 block hover:border-blue-300 transition">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <span class="inline-block px-3 py-0.5 text-[9px] font-black rounded-full bg-blue-100 text-blue-700 border border-blue-200 mb-1.5">
                                #{{ $thread->category_label ?? $thread->category ?? 'diskusi' }}
                            </span>
                            <h4 class="text-xs font-black text-slate-900 truncate leading-snug">{{ $thread->title }}</h4>
                            <p class="text-[11px] text-slate-600 line-clamp-1 mt-0.5 font-bold">{{ Str::limit(strip_tags($thread->content), 70) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-slate-500 mt-3 pt-2.5 border-t border-slate-100 font-extrabold">
                        <span class="text-slate-700 flex items-center gap-1.5">
                            <i class="fa-regular fa-user text-blue-600"></i> {{ $thread->user->name ?? 'Anonim' }}
                        </span>
                        <div class="flex items-center space-x-3 text-slate-400">
                            <span><i class="fa-regular fa-comment mr-1"></i>{{ $thread->replies_count ?? 0 }}</span>
                            <span><i class="fa-regular fa-heart mr-1"></i>{{ $thread->likes_count ?? 0 }}</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                    Belum ada diskusi di Pembda Space.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
