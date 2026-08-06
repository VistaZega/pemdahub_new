@extends('layouts.guru')

@section('title', 'Rekap Kehadiran Tatap Muka - ' . $course->name)

@push('styles')
<style>
    .session-card { animation: fadeInUp 0.4s ease both; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="{ openSessionId: null }">
    {{-- Header / Breadcrumbs --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('guru.lms.index') }}" class="hover:text-emerald-600 transition-colors">LMS Dashboard</a>
                <i class="fas fa-chevron-right text-[8px] text-gray-400"></i>
                <a href="{{ route('guru.lms.show', $course->id) }}" class="hover:text-emerald-600 transition-colors">{{ $course->name }}</a>
                <i class="fas fa-chevron-right text-[8px] text-gray-400"></i>
                <span class="text-gray-700">Rekap Kehadiran</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Rekap Kehadiran Tatap Muka Virtual</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar riwayat sesi tatap muka dan absensi kehadiran siswa kelas virtual.</p>
        </div>
        <div>
            <a href="{{ route('guru.lms.show', $course->id) }}" class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-xs font-bold hover:bg-gray-50 transition-all shadow-sm">
                <i class="fas fa-arrow-left"></i> Kembali ke Course
            </a>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl p-6 text-white shadow-md relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 opacity-10">
                <i class="fas fa-video text-7xl"></i>
            </div>
            <p class="text-xs font-bold tracking-wide text-indigo-100">Total Sesi Live</p>
            <p class="text-3xl font-extrabold mt-1">{{ $sessions->total() }}</p>
            <p class="text-[10px] text-indigo-100 mt-2 font-medium">Sesi tatap muka virtual yang telah dimulai oleh guru.</p>
        </div>
        
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-6 text-white shadow-md relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 opacity-10">
                <i class="fas fa-users text-7xl"></i>
            </div>
            <p class="text-xs font-bold tracking-wide text-emerald-100">Total Siswa Terdaftar</p>
            <p class="text-3xl font-extrabold mt-1">{{ $course->lmsClasses->flatMap(fn($c) => $c->classroom ? $c->classroom->students : [])->unique('id')->count() }}</p>
            <p class="text-[10px] text-emerald-100 mt-2 font-medium">Siswa terdaftar yang berhak mengikuti kelas virtual.</p>
        </div>

        <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-6 text-white shadow-md relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 opacity-10">
                <i class="fas fa-user-check text-7xl"></i>
            </div>
            <p class="text-xs font-bold tracking-wide text-amber-100">Rata-rata Kehadiran</p>
            @php
                $totalAttendees = $sessions->sum('total_attendees');
                $sessionCount = $sessions->count();
                $avgAttendees = $sessionCount > 0 ? round($totalAttendees / $sessionCount, 1) : 0;
            @endphp
            <p class="text-3xl font-extrabold mt-1">{{ $avgAttendees }} <span class="text-sm font-normal text-amber-100">siswa/sesi</span></p>
            <p class="text-[10px] text-amber-100 mt-2 font-medium">Rata-rata jumlah siswa yang hadir pada setiap sesi live.</p>
        </div>
    </div>

    {{-- Sessions List --}}
    <div class="space-y-4">
        <h2 class="text-sm font-bold text-gray-700 tracking-wide flex items-center gap-2">
            <i class="fas fa-history text-indigo-500"></i> Riwayat Sesi Tatap Muka
        </h2>

        @forelse($sessions as $session)
        <div class="session-card bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-all">
            {{-- Session Summary Header --}}
            <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 cursor-pointer hover:bg-gray-50/50 transition-colors"
                 @click="openSessionId = openSessionId === {{ $session->id }} ? null : {{ $session->id }}">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 {{ $session->isActive() ? 'bg-rose-50 text-rose-600 border border-rose-100 animate-pulse' : 'bg-slate-50 text-slate-600 border border-slate-100' }}">
                        <i class="fas {{ $session->isActive() ? 'fa-video' : 'fa-video-slash' }} text-lg"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-bold text-gray-800 text-sm">Sesi Tatap Muka #{{ $session->id }}</h3>
                            @if($session->isActive())
                            <span class="bg-rose-500 text-white text-[9px] font-bold px-2 py-0.5 rounded-full tracking-wide flex items-center gap-1 animate-pulse">
                                <span class="w-1.5 h-1.5 bg-white rounded-full inline-block animate-ping"></span> Live Sekarang
                            </span>
                            @else
                            <span class="bg-gray-100 text-gray-500 text-[9px] font-bold px-2 py-0.5 rounded-lg tracking-wide">Selesai</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 mt-1.5 text-xs text-gray-500 flex-wrap">
                            <span class="flex items-center gap-1"><i class="far fa-calendar-alt text-gray-400"></i> {{ $session->started_at->format('d M Y') }}</span>
                            <span class="text-gray-300">•</span>
                            <span class="flex items-center gap-1"><i class="far fa-clock text-gray-400"></i> {{ $session->started_at->format('H:i') }} - {{ $session->ended_at ? $session->ended_at->format('H:i') : 'Sekarang' }}</span>
                            <span class="text-gray-300">•</span>
                            <span class="flex items-center gap-1"><i class="fas fa-hourglass-half text-gray-400"></i> Durasi: {{ $session->duration_minutes }} menit</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center gap-4 justify-between md:justify-end">
                    <div class="text-right">
                        <p class="text-xs text-gray-400 font-bold tracking-wide">Kehadiran Siswa</p>
                        <p class="text-base font-extrabold text-indigo-600 mt-0.5">{{ $session->attendances->count() }} Siswa Hadir</p>
                    </div>
                    <button class="w-8 h-8 rounded-lg bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-indigo-50 hover:text-indigo-600 transition-colors border border-gray-100">
                        <i class="fas fa-chevron-down transition-transform duration-200" :class="openSessionId === {{ $session->id }} ? 'rotate-180' : ''"></i>
                    </button>
                </div>
            </div>

            {{-- Attendances Detail Table --}}
            <div x-show="openSessionId === {{ $session->id }}" x-transition x-cloak class="border-t border-gray-100 bg-gray-50/20 p-5">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-150">
                                <th class="pb-3 text-xs font-bold text-gray-400 tracking-wide pl-1">Siswa</th>
                                <th class="pb-3 text-xs font-bold text-gray-400 tracking-wide">NISN</th>
                                <th class="pb-3 text-xs font-bold text-gray-400 tracking-wide">Waktu Bergabung</th>
                                <th class="pb-3 text-xs font-bold text-gray-400 tracking-wide">Waktu Keluar</th>
                                <th class="pb-3 text-xs font-bold text-gray-400 tracking-wide text-right pr-1">Durasi Mengikuti</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($session->attendances as $att)
                            <tr class="hover:bg-white/40 transition-colors group">
                                <td class="py-3.5 pl-1">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-[11px] font-bold text-white shadow-sm flex-shrink-0 group-hover:scale-105 transition-transform">
                                            {{ strtoupper(substr($att->student->full_name ?? $att->student->user->name ?? 'S', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-gray-800">{{ $att->student->full_name }}</p>
                                            <p class="text-[9px] text-gray-400 font-semibold">{{ $att->student->user->name ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 text-xs text-gray-600 font-medium">{{ $att->student->nisn }}</td>
                                <td class="py-3.5 text-xs text-gray-600">{{ $att->joined_at->format('H:i:s') }}</td>
                                <td class="py-3.5 text-xs text-gray-600">
                                    @if($att->left_at)
                                        {{ $att->left_at->format('H:i:s') }}
                                    @else
                                        <span class="text-rose-500 font-semibold flex items-center gap-1.5"><span class="w-1.5 h-1.5 bg-rose-500 rounded-full inline-block animate-ping"></span> Di Dalam Kelas</span>
                                    @endif
                                </td>
                                <td class="py-3.5 text-xs font-bold text-indigo-600 text-right pr-1">
                                    {{ $att->duration_label }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-xs text-gray-400 italic">Belum ada data kehadiran siswa yang tercatat untuk sesi ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-16 text-center">
            <div class="w-20 h-20 bg-indigo-50 rounded-xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-video-slash text-3xl text-indigo-300"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-700 mb-1">Belum Ada Sesi Tatap Muka</h3>
            <p class="text-sm text-gray-400 max-w-xs mx-auto leading-relaxed">Sesi tatap muka virtual yang dimulai oleh guru akan tercatat secara otomatis di halaman ini.</p>
        </div>
        @endforelse

        {{-- Pagination --}}
        <div class="pt-4">
            {{ $sessions->links() }}
        </div>
    </div>
</div>
@endsection
