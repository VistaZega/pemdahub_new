@extends('mobile.layouts.app')

@section('title', 'Beranda Playful - PembdaHUB Mobile Pro')

@section('content')
<div class="space-y-5 pt-2">
    <!-- Hero Banner Card (Playful 3D Clay Banner) -->
    <div class="clay-blue p-6 relative overflow-hidden">
        <div class="flex items-center space-x-4 relative z-10">
            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-2xl object-cover border-4 border-white/60 shadow-md bg-white">

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
        <!-- Menu Guru (Clay Cards Grid - 12 Modules) -->
        <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
            <!-- 1. Jadwal Mengajar -->
            <a href="{{ route('mobile.guru.jadwal') }}" class="clay-purple p-2.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-10 h-10 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-xl mb-1 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    👨‍🏫
                </div>
                <span class="text-[10px] font-black">Jadwal</span>
            </a>

            <!-- 2. Input Absensi Siswa -->
            <a href="{{ route('mobile.guru.absensi.input') }}" class="clay-green p-2.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-10 h-10 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-xl mb-1 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📋
                </div>
                <span class="text-[10px] font-black">Absen Siswa</span>
            </a>

            <!-- 3. Presensi Guru Mandiri (Absen Saya) -->
            <a href="{{ route('mobile.guru.absensi.saya') }}" class="clay-cyan p-2.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-10 h-10 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-xl mb-1 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📌
                </div>
                <span class="text-[10px] font-black">Absen Saya</span>
            </a>

            <!-- 4. Periksa Tugas & Nilai -->
            <a href="{{ route('mobile.guru.tugas') }}" class="clay-yellow p-2.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-10 h-10 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-xl mb-1 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📝
                </div>
                <span class="text-[10px] font-black">Nilai</span>
            </a>

            <!-- 5. LMS Modul -->
            <a href="{{ route('mobile.lms.index') }}" class="clay-blue p-2.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-10 h-10 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-xl mb-1 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📚
                </div>
                <span class="text-[10px] font-black">LMS</span>
            </a>

            <!-- 6. My Class (Kelas Saya) -->
            <a href="{{ route('mobile.guru.kelas') }}" class="clay-purple p-2.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-10 h-10 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-xl mb-1 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    🏫
                </div>
                <span class="text-[10px] font-black">My Class</span>
            </a>

            <!-- 7. Surat Edaran -->
            <a href="{{ route('mobile.guru.edaran') }}" class="clay-orange p-2.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-10 h-10 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-xl mb-1 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📜
                </div>
                <span class="text-[10px] font-black">Edaran</span>
            </a>

            <!-- 8. CBT Ujian -->
            <a href="{{ route('mobile.guru.cbt') }}" class="clay-pink p-2.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-10 h-10 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-xl mb-1 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    💻
                </div>
                <span class="text-[10px] font-black">CBT</span>
            </a>

            <!-- 9. Raport Digital -->
            <a href="{{ route('mobile.guru.raport') }}" class="clay-green p-2.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-10 h-10 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-xl mb-1 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    📊
                </div>
                <span class="text-[10px] font-black">Raport</span>
            </a>

            <!-- 10. Hall Of Fame -->
            <a href="{{ route('mobile.guru.hall-of-fame') }}" class="clay-yellow p-2.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-10 h-10 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-xl mb-1 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    🏆
                </div>
                <span class="text-[10px] font-black">Hall of Fame</span>
            </a>

            <!-- 11. Pembda Space -->
            <a href="{{ route('mobile.space.index') }}" class="clay-purple p-2.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-10 h-10 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-xl mb-1 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    💬
                </div>
                <span class="text-[10px] font-black">Space</span>
            </a>

            <!-- 12. Profile Saya -->
            <a href="{{ route('mobile.profile') }}" class="clay-cyan p-2.5 text-center flex flex-col items-center group transition active:scale-95">
                <div class="w-10 h-10 rounded-2xl bg-white/30 backdrop-blur-md flex items-center justify-center text-xl mb-1 border border-white/40 shadow-sm group-hover:scale-110 transition">
                    👤
                </div>
                <span class="text-[10px] font-black">Profile</span>
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
                <a href="{{ route('mobile.space.show', $thread->id) }}" class="clay-card p-5 block hover:border-blue-300 transition space-y-2.5">
                    <!-- Top Category Badge -->
                    <div>
                        <span class="inline-flex items-center gap-1 px-3 py-1 text-[10px] font-black rounded-full bg-blue-100 text-blue-700 border border-blue-200">
                            #💬 {{ $thread->category_label ?? $thread->category ?? 'Lobi Utama' }}
                        </span>
                    </div>

                    <!-- Post Title & Content -->
                    <div class="space-y-1">
                        <h4 class="text-xs font-black text-slate-900 leading-snug">{{ $thread->title }}</h4>
                        <p class="text-[11px] text-slate-600 line-clamp-2 leading-relaxed font-semibold">{{ Str::limit(strip_tags($thread->content), 80) }}</p>
                    </div>

                    <!-- Footer: Author & Stats -->
                    <div class="flex items-center justify-between text-[10px] text-slate-500 pt-3 border-t border-slate-100 font-extrabold">
                        <span class="text-slate-700 flex items-center gap-1.5 min-w-0 truncate">
                            <i class="fa-regular fa-user text-blue-600 text-xs"></i> 
                            <span class="truncate font-black text-slate-800 uppercase tracking-tight text-[10px]">{{ $thread->user->name ?? 'Anonim' }}</span>
                        </span>
                        <div class="flex items-center space-x-3.5 text-slate-500 shrink-0 text-[11px]">
                            <span class="flex items-center gap-1"><i class="fa-regular fa-comment text-blue-500"></i>{{ $thread->replies_count ?? 0 }}</span>
                            <span class="flex items-center gap-1"><i class="fa-regular fa-heart text-rose-500"></i>{{ $thread->likes_count ?? 0 }}</span>
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
