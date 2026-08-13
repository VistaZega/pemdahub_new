@extends('mobile.layouts.app')

@section('title', 'Beranda Serene - PembdaHUB Mobile')

@section('content')
<div class="space-y-5 pt-2">
    <!-- Serene Hero Greeting Banner (Lavender & Mint Soft Wave Card) -->
    <div class="bg-gradient-to-r from-[#e5deff] via-[#d8e8d8] to-[#fde5d4] rounded-[2rem] p-6 text-slate-800 shadow-[0_10px_30px_rgba(180,170,210,0.25)] border border-white relative overflow-hidden">
        <div class="flex items-center space-x-4 relative z-10">
            @if($student && $student->photo_url)
                <img src="{{ $student->photo_url }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-white shadow-md">
            @else
                <div class="w-16 h-16 rounded-2xl bg-white/80 backdrop-blur-md flex items-center justify-center text-purple-900 font-extrabold text-xl border border-white shadow-md">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
            @endif

            <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-2 mb-1">
                    @php $activeRole = session('active_role', $user->role); @endphp
                    <span class="px-3 py-0.5 rounded-full bg-white/70 text-purple-900 text-[10px] font-extrabold tracking-wide uppercase shadow-sm border border-white">
                        {{ strtoupper($activeRole) }}
                    </span>
                    @if($student && $student->school)
                        <span class="text-[10px] text-slate-700 font-semibold truncate">{{ $student->school->name }}</span>
                    @endif
                </div>
                <h2 class="text-xl font-serif font-bold text-slate-900 truncate leading-tight">Selamat Datang, {{ strtok($user->name, ' ') }}! 🌿</h2>
                <p class="text-xs text-slate-700 mt-1 font-medium truncate">
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

    <!-- Quick Access Grid (Soft Pastel Serene Cards) -->
    <div>
        <div class="flex items-center justify-between mb-3 px-1">
            <h3 class="text-sm font-serif font-bold text-slate-800">Layanan PembdaHUB</h3>
            <span class="text-[11px] font-semibold text-purple-700">Akses Cepat</span>
        </div>
        
        @php $activeRole = session('active_role', $user->role); @endphp
        @if($activeRole === 'siswa')
        <div class="grid grid-cols-4 gap-3">
            <!-- Jadwal (Lavender) -->
            <a href="{{ route('mobile.jadwal') }}" class="bg-[#e5deff]/60 border border-[#d3c7ff] rounded-3xl p-3 text-center transition hover:bg-[#e5deff] flex flex-col items-center group shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-white text-purple-700 flex items-center justify-center text-xl mb-1.5 shadow-sm group-hover:scale-105 transition">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">Jadwal</span>
            </a>

            <!-- Nilai (Mint Sage) -->
            <a href="{{ route('mobile.nilai') }}" class="bg-[#d8e8d8]/60 border border-[#bce0bc] rounded-3xl p-3 text-center transition hover:bg-[#d8e8d8] flex flex-col items-center group shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-white text-emerald-700 flex items-center justify-center text-xl mb-1.5 shadow-sm group-hover:scale-105 transition">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">Nilai</span>
            </a>

            <!-- Tagihan SPP (Soft Peach) -->
            <a href="{{ route('mobile.tagihan') }}" class="bg-[#fde5d4]/60 border border-[#fbd4b6] rounded-3xl p-3 text-center transition hover:bg-[#fde5d4] flex flex-col items-center group shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-white text-amber-700 flex items-center justify-center text-xl mb-1.5 shadow-sm group-hover:scale-105 transition">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">Tagihan</span>
            </a>

            <!-- CBT Ujian (Rose Blush) -->
            <a href="{{ route('mobile.cbt') }}" class="bg-[#ffd6db]/60 border border-[#ffb8c2] rounded-3xl p-3 text-center transition hover:bg-[#ffd6db] flex flex-col items-center group shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-white text-rose-700 flex items-center justify-center text-xl mb-1.5 shadow-sm group-hover:scale-105 transition">
                    <i class="fa-solid fa-laptop-code"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">CBT Ujian</span>
            </a>

            <!-- PKL / Tugas Akhir (Warm Sand) -->
            <a href="{{ route('mobile.pkl') }}" class="bg-[#f5edd6]/60 border border-[#ebdcb1] rounded-3xl p-3 text-center transition hover:bg-[#f5edd6] flex flex-col items-center group shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-white text-amber-800 flex items-center justify-center text-xl mb-1.5 shadow-sm group-hover:scale-105 transition">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">PKL</span>
            </a>

            <!-- Pembda Space (Soft Violet) -->
            <a href="{{ route('mobile.space.index') }}" class="bg-[#ebdcf7]/60 border border-[#dac1f2] rounded-3xl p-3 text-center transition hover:bg-[#ebdcf7] flex flex-col items-center group shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-white text-purple-800 flex items-center justify-center text-xl mb-1.5 shadow-sm group-hover:scale-105 transition">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">Space</span>
            </a>

            <!-- LMS (Cool Sky) -->
            <a href="{{ route('mobile.lms.index') }}" class="bg-[#d9ecf9]/60 border border-[#badbf5] rounded-3xl p-3 text-center transition hover:bg-[#d9ecf9] flex flex-col items-center group shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-white text-blue-700 flex items-center justify-center text-xl mb-1.5 shadow-sm group-hover:scale-105 transition">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">LMS</span>
            </a>

            <!-- Absensi (Soft Cyan) -->
            <a href="{{ route('mobile.absensi.index') }}" class="bg-[#cbe6d5]/60 border border-[#a8d6b7] rounded-3xl p-3 text-center transition hover:bg-[#cbe6d5] flex flex-col items-center group shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-white text-teal-800 flex items-center justify-center text-xl mb-1.5 shadow-sm group-hover:scale-105 transition">
                    <i class="fa-solid fa-fingerprint"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">Absensi</span>
            </a>
        </div>
        @else
        <!-- Menu Guru / Pegawai (Serene Pastels) -->
        <div class="grid grid-cols-4 gap-3">
            <!-- Jadwal Mengajar -->
            <a href="{{ route('mobile.guru.jadwal') }}" class="bg-[#e5deff]/60 border border-[#d3c7ff] rounded-3xl p-3 text-center transition hover:bg-[#e5deff] flex flex-col items-center group shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-white text-purple-700 flex items-center justify-center text-xl mb-1.5 shadow-sm group-hover:scale-105 transition">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">Jadwal</span>
            </a>

            <!-- Input Absensi Kelas -->
            <a href="{{ route('mobile.guru.absensi.input') }}" class="bg-[#d8e8d8]/60 border border-[#bce0bc] rounded-3xl p-3 text-center transition hover:bg-[#d8e8d8] flex flex-col items-center group shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-white text-emerald-700 flex items-center justify-center text-xl mb-1.5 shadow-sm group-hover:scale-105 transition">
                    <i class="fa-solid fa-clipboard-user"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">Absen Kelas</span>
            </a>

            <!-- Periksa Tugas -->
            <a href="{{ route('mobile.guru.tugas') }}" class="bg-[#fde5d4]/60 border border-[#fbd4b6] rounded-3xl p-3 text-center transition hover:bg-[#fde5d4] flex flex-col items-center group shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-white text-amber-700 flex items-center justify-center text-xl mb-1.5 shadow-sm group-hover:scale-105 transition">
                    <i class="fa-solid fa-file-signature"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">Nilai Tugas</span>
            </a>

            <!-- Space -->
            <a href="{{ route('mobile.space.index') }}" class="bg-[#ebdcf7]/60 border border-[#dac1f2] rounded-3xl p-3 text-center transition hover:bg-[#ebdcf7] flex flex-col items-center group shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-white text-purple-800 flex items-center justify-center text-xl mb-1.5 shadow-sm group-hover:scale-105 transition">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-800">Space</span>
            </a>
        </div>
        @endif
    </div>

    <!-- Attendance Summary Widget (Serene Card) -->
    @if($activeRole === 'siswa')
    <div class="serene-card rounded-3xl p-5 space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></div>
                <h3 class="text-sm font-serif font-bold text-slate-900">Kehadiran Bulan Ini</h3>
            </div>
            <a href="{{ route('mobile.absensi.index') }}" class="text-[11px] font-bold text-purple-700 hover:text-purple-900">Detail <i class="fa-solid fa-chevron-right text-[9px]"></i></a>
        </div>

        <div class="grid grid-cols-3 gap-3">
            <div class="bg-[#d8e8d8]/50 border border-[#c3dec3] rounded-2xl p-3 text-center">
                <span class="text-2xl font-black text-emerald-800 leading-none">{{ $attendanceStats['hadir'] }}</span>
                <span class="block text-[10px] text-emerald-900 font-extrabold mt-1">Hadir</span>
            </div>
            <div class="bg-[#fde5d4]/50 border border-[#f9d2b5] rounded-2xl p-3 text-center">
                <span class="text-2xl font-black text-amber-800 leading-none">{{ $attendanceStats['terlambat'] }}</span>
                <span class="block text-[10px] text-amber-900 font-extrabold mt-1">Terlambat</span>
            </div>
            <div class="bg-[#ffd6db]/50 border border-[#fbb8c1] rounded-2xl p-3 text-center">
                <span class="text-2xl font-black text-rose-800 leading-none">{{ $attendanceStats['sakit'] + $attendanceStats['izin'] + $attendanceStats['alpha'] }}</span>
                <span class="block text-[10px] text-rose-900 font-extrabold mt-1">Izin/Alpha</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Pembda Space Recent Feed Widget -->
    <div>
        <div class="flex items-center justify-between mb-3 px-1">
            <h3 class="text-sm font-serif font-bold text-slate-800">Pembda Space Terbaru</h3>
            <a href="{{ route('mobile.space.index') }}" class="text-[11px] font-bold text-purple-700 hover:text-purple-900">Lihat Semua <i class="fa-solid fa-arrow-right text-[9px]"></i></a>
        </div>

        <div class="space-y-3">
            @forelse($recentDiscussions as $thread)
                <a href="{{ route('mobile.space.show', $thread->id) }}" class="serene-card rounded-2xl p-4.5 block hover:border-purple-300 transition">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <span class="inline-block px-3 py-0.5 text-[9px] font-extrabold rounded-full bg-[#e5deff] text-purple-900 border border-[#d3c7ff] mb-2">
                                #{{ $thread->category_label ?? $thread->category ?? 'diskusi' }}
                            </span>
                            <h4 class="text-xs font-black text-slate-900 truncate leading-snug">{{ $thread->title }}</h4>
                            <p class="text-[11px] text-slate-600 line-clamp-1 mt-1 font-medium leading-relaxed">{{ Str::limit(strip_tags($thread->content), 70) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-slate-500 mt-3 pt-2.5 border-t border-stone-100 font-semibold">
                        <span class="text-slate-700 flex items-center gap-1.5">
                            <i class="fa-regular fa-user text-purple-600"></i> {{ $thread->user->name ?? 'Anonim' }}
                        </span>
                        <div class="flex items-center space-x-3 text-slate-400">
                            <span><i class="fa-regular fa-comment mr-1"></i>{{ $thread->replies_count ?? 0 }}</span>
                            <span><i class="fa-regular fa-heart mr-1"></i>{{ $thread->likes_count ?? 0 }}</span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="serene-card rounded-2xl p-6 text-center text-slate-500 text-xs font-medium">
                    Belum ada diskusi di Pembda Space.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
