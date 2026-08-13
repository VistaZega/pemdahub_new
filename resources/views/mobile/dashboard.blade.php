@extends('mobile.layouts.app')

@section('title', 'Beranda - PembdaHUB Mobile')

@section('content')
<div class="space-y-5 pt-2">
    <!-- User Greeting Banner -->
    <div class="glass-card rounded-3xl p-5 relative overflow-hidden bg-gradient-to-br from-indigo-950/80 via-slate-900 to-slate-950 border border-indigo-500/20 shadow-xl">
        <div class="absolute -right-6 -top-6 w-28 h-28 bg-indigo-500/20 rounded-full blur-xl"></div>

        <div class="flex items-center space-x-3.5 relative z-10">
            @if($student && $student->photo_url)
                <img src="{{ $student->photo_url }}" alt="{{ $user->name }}" class="w-14 h-14 rounded-2xl object-cover border-2 border-indigo-400/40 shadow-md">
            @else
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center text-white font-extrabold text-xl shadow-md border border-indigo-400/30">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
            @endif

            <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-0.5 rounded-md bg-indigo-500/20 text-indigo-300 text-[10px] font-bold tracking-wide uppercase border border-indigo-500/30">
                        {{ strtoupper($user->role) }}
                    </span>
                    @if($student && $student->school)
                        <span class="text-[10px] text-slate-400 truncate">{{ $student->school->name }}</span>
                    @endif
                </div>
                <h2 class="text-lg font-extrabold text-white truncate mt-1 leading-tight">{{ $user->name }}</h2>
                <p class="text-xs text-slate-400 mt-0.5 truncate">
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

    <!-- Quick Access Grid (4 Priority Features) -->
    <div>
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2.5 px-1">Menu Utama</h3>
        <div class="grid grid-cols-4 gap-2.5">
            <!-- Space -->
            <a href="{{ route('mobile.space.index') }}" class="glass-card rounded-2xl p-3 text-center hover:bg-slate-800/80 transition flex flex-col items-center group">
                <div class="w-11 h-11 rounded-xl bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center text-lg mb-1.5 group-hover:scale-110 transition">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <span class="text-[11px] font-semibold text-slate-200">Space</span>
            </a>

            <!-- LMS -->
            <a href="{{ route('mobile.lms.index') }}" class="glass-card rounded-2xl p-3 text-center hover:bg-slate-800/80 transition flex flex-col items-center group">
                <div class="w-11 h-11 rounded-xl bg-purple-500/20 text-purple-400 border border-purple-500/30 flex items-center justify-center text-lg mb-1.5 group-hover:scale-110 transition">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <span class="text-[11px] font-semibold text-slate-200">LMS</span>
            </a>

            <!-- Absensi -->
            <a href="{{ route('mobile.absensi.index') }}" class="glass-card rounded-2xl p-3 text-center hover:bg-slate-800/80 transition flex flex-col items-center group">
                <div class="w-11 h-11 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-lg mb-1.5 group-hover:scale-110 transition">
                    <i class="fa-solid fa-fingerprint"></i>
                </div>
                <span class="text-[11px] font-semibold text-slate-200">Absensi</span>
            </a>

            <!-- Profil -->
            <a href="{{ route('mobile.profile') }}" class="glass-card rounded-2xl p-3 text-center hover:bg-slate-800/80 transition flex flex-col items-center group">
                <div class="w-11 h-11 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center text-lg mb-1.5 group-hover:scale-110 transition">
                    <i class="fa-solid fa-id-card"></i>
                </div>
                <span class="text-[11px] font-semibold text-slate-200">Profil</span>
            </a>
        </div>
    </div>

    <!-- Attendance Summary Widget (If student) -->
    @if($user->role === 'siswa')
    <div class="glass-card rounded-2xl p-4">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center space-x-2">
                <div class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></div>
                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Rekap Kehadiran Bulan Ini</h3>
            </div>
            <a href="{{ route('mobile.absensi.index') }}" class="text-[11px] font-medium text-indigo-400 hover:text-indigo-300">Detail <i class="fa-solid fa-chevron-right text-[9px]"></i></a>
        </div>

        <div class="grid grid-cols-3 gap-2">
            <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl p-2.5 text-center">
                <span class="text-lg font-black text-emerald-400">{{ $attendanceStats['hadir'] }}</span>
                <span class="block text-[10px] text-emerald-300/80 font-medium">Hadir</span>
            </div>
            <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-2.5 text-center">
                <span class="text-lg font-black text-amber-400">{{ $attendanceStats['terlambat'] }}</span>
                <span class="block text-[10px] text-amber-300/80 font-medium">Terlambat</span>
            </div>
            <div class="bg-rose-500/10 border border-rose-500/20 rounded-xl p-2.5 text-center">
                <span class="text-lg font-black text-rose-400">{{ $attendanceStats['sakit'] + $attendanceStats['izin'] + $attendanceStats['alpha'] }}</span>
                <span class="block text-[10px] text-rose-300/80 font-medium">Izin/Alpha</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Pembda Space Recent Feed Widget -->
    <div>
        <div class="flex items-center justify-between mb-2.5 px-1">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pembda Space Terbaru</h3>
            <a href="{{ route('mobile.space.index') }}" class="text-[11px] font-medium text-indigo-400 hover:text-indigo-300">Lihat Semua <i class="fa-solid fa-arrow-right text-[9px]"></i></a>
        </div>

        <div class="space-y-2.5">
            @forelse($recentDiscussions as $thread)
                <a href="{{ route('mobile.space.show', $thread->id) }}" class="glass-card rounded-2xl p-3.5 block hover:border-indigo-500/40 transition">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <span class="inline-block px-2 py-0.5 text-[9px] font-bold rounded-md bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 mb-1">
                                #{{ $thread->channel_group ?? 'diskusi' }}
                            </span>
                            <h4 class="text-xs font-bold text-white truncate leading-snug">{{ $thread->title }}</h4>
                            <p class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">{{ Str::limit(strip_tags($thread->content), 70) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-slate-500 mt-2.5 pt-2 border-t border-slate-800/80">
                        <span class="font-medium text-slate-400"><i class="fa-regular fa-user mr-1"></i>{{ $thread->user->name ?? 'Anonim' }}</span>
                        <div class="flex items-center space-x-3">
                            <span><i class="fa-regular fa-comment mr-1"></i>{{ $thread->replies_count ?? 0 }}</span>
                            <span><i class="fa-regular fa-heart mr-1"></i>{{ $thread->likes_count ?? 0 }}</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="glass-card rounded-2xl p-6 text-center text-slate-500 text-xs">
                    Belum ada diskusi di Pembda Space.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
