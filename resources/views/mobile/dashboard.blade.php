@extends('mobile.layouts.app')

@section('title', 'Beranda - PembdaHUB Mobile')

@section('content')
<div class="space-y-5 pt-2">
    <!-- User Greeting Banner (Vibrant Card) -->
    <div class="bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-700 rounded-3xl p-5 text-white shadow-xl shadow-indigo-500/20 relative overflow-hidden">
        <div class="absolute -right-6 -top-6 w-28 h-28 bg-white/10 rounded-full blur-xl"></div>

        <div class="flex items-center space-x-3.5 relative z-10">
            @if($student && $student->photo_url)
                <img src="{{ $student->photo_url }}" alt="{{ $user->name }}" class="w-14 h-14 rounded-2xl object-cover border-2 border-white/40 shadow-md">
            @else
                <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white font-extrabold text-xl border border-white/30 shadow-md">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
            @endif

            <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-2">
                    @php $activeRole = session('active_role', $user->role); @endphp
                    <span class="px-2.5 py-0.5 rounded-full bg-white/20 text-white text-[10px] font-extrabold tracking-wide uppercase border border-white/30">
                        {{ strtoupper($activeRole) }}
                    </span>
                    @if($student && $student->school)
                        <span class="text-[10px] text-indigo-200 truncate font-medium">{{ $student->school->name }}</span>
                    @endif
                </div>
                <h2 class="text-lg font-extrabold text-white truncate mt-1 leading-tight">{{ $user->name }}</h2>
                <p class="text-xs text-indigo-100/90 mt-0.5 truncate font-medium">
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

    <!-- Quick Access Grid (Vibrant Colorful Icons) -->
    <div>
        <h3 class="text-xs font-extrabold text-slate-500 uppercase tracking-wider mb-2.5 px-1">Menu Utama</h3>
        
        @php $activeRole = session('active_role', $user->role); @endphp
        @if($activeRole === 'siswa')
        <div class="grid grid-cols-4 gap-2.5">
            <!-- Jadwal -->
            <a href="{{ route('mobile.jadwal') }}" class="glass-card rounded-2xl p-3 text-center hover:bg-slate-50 transition flex flex-col items-center group">
                <div class="w-12 h-12 rounded-2xl bg-blue-500 text-white flex items-center justify-center text-xl mb-1.5 shadow-md shadow-blue-500/20 group-hover:scale-110 transition">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">Jadwal</span>
            </a>

            <!-- Nilai -->
            <a href="{{ route('mobile.nilai') }}" class="glass-card rounded-2xl p-3 text-center hover:bg-slate-50 transition flex flex-col items-center group">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center text-xl mb-1.5 shadow-md shadow-emerald-500/20 group-hover:scale-110 transition">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">Nilai</span>
            </a>

            <!-- Tagihan SPP -->
            <a href="{{ route('mobile.tagihan') }}" class="glass-card rounded-2xl p-3 text-center hover:bg-slate-50 transition flex flex-col items-center group">
                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-xl mb-1.5 shadow-md shadow-amber-500/20 group-hover:scale-110 transition">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">Tagihan</span>
            </a>

            <!-- CBT Ujian -->
            <a href="{{ route('mobile.cbt') }}" class="glass-card rounded-2xl p-3 text-center hover:bg-slate-50 transition flex flex-col items-center group">
                <div class="w-12 h-12 rounded-2xl bg-rose-500 text-white flex items-center justify-center text-xl mb-1.5 shadow-md shadow-rose-500/20 group-hover:scale-110 transition">
                    <i class="fa-solid fa-laptop-code"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">CBT Ujian</span>
            </a>

            <!-- PKL / Tugas Akhir -->
            <a href="{{ route('mobile.pkl') }}" class="glass-card rounded-2xl p-3 text-center hover:bg-slate-50 transition flex flex-col items-center group">
                <div class="w-12 h-12 rounded-2xl bg-teal-500 text-white flex items-center justify-center text-xl mb-1.5 shadow-md shadow-teal-500/20 group-hover:scale-110 transition">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">PKL</span>
            </a>

            <!-- Pembda Space -->
            <a href="{{ route('mobile.space.index') }}" class="glass-card rounded-2xl p-3 text-center hover:bg-slate-50 transition flex flex-col items-center group">
                <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-xl mb-1.5 shadow-md shadow-indigo-600/20 group-hover:scale-110 transition">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">Space</span>
            </a>

            <!-- LMS -->
            <a href="{{ route('mobile.lms.index') }}" class="glass-card rounded-2xl p-3 text-center hover:bg-slate-50 transition flex flex-col items-center group">
                <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center text-xl mb-1.5 shadow-md shadow-purple-600/20 group-hover:scale-110 transition">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">LMS</span>
            </a>

            <!-- Absensi -->
            <a href="{{ route('mobile.absensi.index') }}" class="glass-card rounded-2xl p-3 text-center hover:bg-slate-50 transition flex flex-col items-center group">
                <div class="w-12 h-12 rounded-2xl bg-cyan-600 text-white flex items-center justify-center text-xl mb-1.5 shadow-md shadow-cyan-600/20 group-hover:scale-110 transition">
                    <i class="fa-solid fa-fingerprint"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">Absensi</span>
            </a>
        </div>
        @else
        <!-- Menu Guru / Pegawai -->
        <div class="grid grid-cols-4 gap-2.5">
            <!-- Jadwal Mengajar -->
            <a href="{{ route('mobile.guru.jadwal') }}" class="glass-card rounded-2xl p-3 text-center hover:bg-slate-50 transition flex flex-col items-center group">
                <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center text-xl mb-1.5 shadow-md shadow-purple-600/20 group-hover:scale-110 transition">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">Jadwal</span>
            </a>

            <!-- Input Absensi Kelas -->
            <a href="{{ route('mobile.guru.absensi.input') }}" class="glass-card rounded-2xl p-3 text-center hover:bg-slate-50 transition flex flex-col items-center group">
                <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-xl mb-1.5 shadow-md shadow-emerald-600/20 group-hover:scale-110 transition">
                    <i class="fa-solid fa-clipboard-user"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">Absen Kelas</span>
            </a>

            <!-- Periksa Tugas -->
            <a href="{{ route('mobile.guru.tugas') }}" class="glass-card rounded-2xl p-3 text-center hover:bg-slate-50 transition flex flex-col items-center group">
                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-xl mb-1.5 shadow-md shadow-amber-500/20 group-hover:scale-110 transition">
                    <i class="fa-solid fa-file-signature"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">Nilai Tugas</span>
            </a>

            <!-- Space -->
            <a href="{{ route('mobile.space.index') }}" class="glass-card rounded-2xl p-3 text-center hover:bg-slate-50 transition flex flex-col items-center group">
                <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-xl mb-1.5 shadow-md shadow-indigo-600/20 group-hover:scale-110 transition">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">Space</span>
            </a>
        </div>
        @endif
    </div>

    <!-- Attendance Summary Widget (If student) -->
    @if($activeRole === 'siswa')
    <div class="glass-card rounded-3xl p-4">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center space-x-2">
                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></div>
                <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Kehadiran Bulan Ini</h3>
            </div>
            <a href="{{ route('mobile.absensi.index') }}" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-700">Detail <i class="fa-solid fa-chevron-right text-[9px]"></i></a>
        </div>

        <div class="grid grid-cols-3 gap-2">
            <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-3 text-center">
                <span class="text-xl font-black text-emerald-600">{{ $attendanceStats['hadir'] }}</span>
                <span class="block text-[10px] text-emerald-700 font-bold">Hadir</span>
            </div>
            <div class="bg-amber-50 border border-amber-100 rounded-2xl p-3 text-center">
                <span class="text-xl font-black text-amber-600">{{ $attendanceStats['terlambat'] }}</span>
                <span class="block text-[10px] text-amber-700 font-bold">Terlambat</span>
            </div>
            <div class="bg-rose-50 border border-rose-100 rounded-2xl p-3 text-center">
                <span class="text-xl font-black text-rose-600">{{ $attendanceStats['sakit'] + $attendanceStats['izin'] + $attendanceStats['alpha'] }}</span>
                <span class="block text-[10px] text-rose-700 font-bold">Izin/Alpha</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Pembda Space Recent Feed Widget -->
    <div>
        <div class="flex items-center justify-between mb-2.5 px-1">
            <h3 class="text-xs font-extrabold text-slate-500 uppercase tracking-wider">Pembda Space Terbaru</h3>
            <a href="{{ route('mobile.space.index') }}" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-700">Lihat Semua <i class="fa-solid fa-arrow-right text-[9px]"></i></a>
        </div>

        <div class="space-y-2.5">
            @forelse($recentDiscussions as $thread)
                <a href="{{ route('mobile.space.show', $thread->id) }}" class="glass-card rounded-2xl p-4 block hover:border-indigo-300 transition">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <span class="inline-block px-2.5 py-0.5 text-[9px] font-extrabold rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200 mb-1.5">
                                #{{ $thread->category_label ?? $thread->category ?? 'diskusi' }}
                            </span>
                            <h4 class="text-xs font-extrabold text-slate-900 truncate leading-snug">{{ $thread->title }}</h4>
                            <p class="text-[11px] text-slate-600 line-clamp-1 mt-0.5 font-medium">{{ Str::limit(strip_tags($thread->content), 70) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-slate-500 mt-3 pt-2.5 border-t border-slate-100 font-semibold">
                        <span class="text-slate-700"><i class="fa-regular fa-user mr-1 text-indigo-600"></i>{{ $thread->user->name ?? 'Anonim' }}</span>
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
