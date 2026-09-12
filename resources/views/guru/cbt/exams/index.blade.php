@extends('layouts.guru')
@section('title', 'Ujian CBT')
@section('content')
<div class="space-y-6">
    {{-- Hero Header (Neo-Brutalism) --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl p-6 md:p-8 border-2 border-black" style="background: linear-gradient(135deg, #090d16 0%, #065f46 50%, #0f766e 100%) !important;">
        <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div class="flex items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-amber-400 border-2 border-black flex items-center justify-center text-black shadow-md text-2xl">
                    <i class="fas fa-laptop-code text-black"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-white tracking-tight" style="color: #ffffff !important;">Ujian CBT</h1>
                    <p class="text-xs md:text-sm font-bold text-teal-200 mt-1" style="color: #99f6e4 !important;">Kelola ujian kelas dan koreksi hasil ujian sekolah (UTS/UAS/Tryout) yang Anda ampu</p>
                </div>
            </div>
            <a href="{{ route('guru.cbt.exams.create') }}" class="inline-flex items-center px-5 py-3 bg-amber-400 hover:bg-amber-300 text-black border-2 border-black rounded-2xl font-black uppercase text-xs tracking-wider shadow-md transition-all">
                <i class="fas fa-plus-circle text-black mr-2"></i>Buat Ujian Kelas
            </a>
        </div>
    </div>

    {{-- Pending Essays Notification Banner --}}
    @if(($totalPendingEssays ?? 0) > 0)
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-5 bg-gradient-to-r from-amber-500 to-orange-500 rounded-2xl text-white shadow-lg shadow-amber-900/10 border-2 border-black">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-white/20 border border-white/30 flex items-center justify-center text-2xl flex-shrink-0">
                <i class="fas fa-pen-fancy text-white"></i>
            </div>
            <div>
                <h3 class="font-black text-lg text-white">Ada {{ $totalPendingEssays }} Jawaban Esai Menunggu Koreksi!</h3>
                <p class="text-xs sm:text-sm text-amber-100 mt-0.5">Jawaban esai dan isian singkat siswa pada ujian mata pelajaran Anda memerlukan penilaian manual.</p>
            </div>
        </div>
        <span class="inline-flex items-center gap-2 px-4 py-2 bg-white text-amber-900 font-bold rounded-xl text-xs uppercase tracking-wider shadow-sm flex-shrink-0">
            <i class="fas fa-clock text-amber-600"></i> Perlu Penilaian
        </span>
    </div>
    @endif

    @if(session('success'))
    <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl">
        <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0"><i class="fas fa-check text-emerald-600 text-base"></i></div>
        <p class="text-emerald-700 text-base font-medium">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-2xl">
        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation-triangle text-red-600 text-base"></i></div>
        <p class="text-red-700 text-base font-medium">{{ session('error') }}</p>
    </div>
    @endif

    {{-- Filter Scope Tabs --}}
    <div class="flex flex-wrap items-center gap-2 border-b border-gray-200 pb-3">
        <a href="{{ route('guru.cbt.exams.index', ['scope' => 'all']) }}" 
           class="px-4 py-2 rounded-xl text-sm font-bold transition flex items-center gap-2 {{ ($filterScope ?? 'all') === 'all' ? 'bg-emerald-600 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
            <i class="fas fa-th-list"></i>
            <span>Semua Ujian</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-black {{ ($filterScope ?? 'all') === 'all' ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-700' }}">{{ $exams->total() }}</span>
        </a>
        <a href="{{ route('guru.cbt.exams.index', ['scope' => 'school']) }}" 
           class="px-4 py-2 rounded-xl text-sm font-bold transition flex items-center gap-2 {{ ($filterScope ?? '') === 'school' ? 'bg-purple-600 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
            <i class="fas fa-school"></i>
            <span>Ujian Sekolah (UTS/UAS/Tryout)</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-black {{ ($filterScope ?? '') === 'school' ? 'bg-white/20 text-white' : 'bg-purple-100 text-purple-800' }}">{{ $countSchoolExams ?? 0 }}</span>
        </a>
        <a href="{{ route('guru.cbt.exams.index', ['scope' => 'class']) }}" 
           class="px-4 py-2 rounded-xl text-sm font-bold transition flex items-center gap-2 {{ ($filterScope ?? '') === 'class' ? 'bg-teal-600 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
            <i class="fas fa-chalkboard-teacher"></i>
            <span>Ujian Kelas Saya</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-black {{ ($filterScope ?? '') === 'class' ? 'bg-white/20 text-white' : 'bg-teal-100 text-teal-800' }}">{{ $countClassExams ?? 0 }}</span>
        </a>
    </div>

    {{-- Exams Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-base">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-5 py-4 text-left text-base font-semibold text-gray-700 uppercase tracking-wider">Judul Ujian & Mapel</th>
                        <th class="px-5 py-4 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Tipe</th>
                        <th class="px-5 py-4 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Soal</th>
                        <th class="px-5 py-4 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Durasi</th>
                        <th class="px-5 py-4 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Peserta Kelas</th>
                        <th class="px-5 py-4 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-4 text-center text-base font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($exams as $exam)
                    <tr class="hover:bg-emerald-50/30 transition-colors duration-150">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $exam->isSchoolScope() ? 'from-purple-100 to-indigo-100 text-purple-600' : 'from-emerald-100 to-teal-100 text-emerald-600' }} flex items-center justify-center flex-shrink-0 text-base">
                                    <i class="fas {{ $exam->isSchoolScope() ? 'fa-school' : 'fa-file-alt' }}"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-gray-900">{{ $exam->exam_title }}</span>
                                        @if($exam->isSchoolScope())
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-black bg-purple-100 text-purple-800 border border-purple-200">
                                                <i class="fas fa-university text-[10px]"></i> UJIAN SEKOLAH
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                <i class="fas fa-chalkboard text-[10px]"></i> KELAS
                                            </span>
                                        @endif
                                        @if(($exam->pending_essays_count ?? 0) > 0)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-black bg-amber-100 text-amber-900 border border-amber-300 animate-pulse">
                                                <i class="fas fa-pen-fancy text-amber-600"></i> {{ $exam->pending_essays_count }} Esai Belum Dinilai
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1 flex items-center gap-2">
                                        <span class="font-semibold text-gray-800">{{ $exam->subject->subject_name ?? $exam->subject->name ?? '-' }}</span>
                                        @if($exam->isSchoolScope() && $exam->creator)
                                            <span class="text-xs text-gray-500">&bull; Dibuat oleh Admin ({{ $exam->creator->name }})</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold rounded-lg {{ $exam->isSchoolScope() ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-100' }}">
                                {{ strtoupper($exam->exam_type) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center"><span class="font-bold text-gray-700">{{ $exam->total_questions_shown }}</span></td>
                        <td class="px-5 py-4 text-center text-gray-800">{{ $exam->duration_minutes }}′</td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $classNames = $exam->participants->map(fn($p) => $p->classroom?->class_name ?? $p->classroom?->name)->filter()->values();
                            @endphp
                            @if($classNames->count() === 1)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100" title="{{ $classNames[0] }}">
                                    <i class="fas fa-chalkboard mr-1 text-emerald-400"></i>{{ Str::limit($classNames[0], 14) }}
                                </span>
                            @elseif($classNames->count() > 1)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 cursor-help" title="{{ $classNames->implode(', ') }}">
                                    <i class="fas fa-users text-emerald-600"></i>{{ $classNames->count() }} Kelas
                                </span>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            @php $sc = match($exam->status) {
                                'active' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'completed' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'published' => 'bg-amber-100 text-amber-800 border-amber-200',
                                default => 'bg-gray-50 text-gray-800 border-gray-200',
                            }; @endphp
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold rounded-lg border {{ $sc }}">{{ ucfirst($exam->status) }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                {{-- Detail --}}
                                <a href="{{ route('guru.cbt.exams.show', $exam) }}" class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-800 flex items-center justify-center hover:bg-indigo-200 transition" title="Detail Ujian">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>

                                {{-- Koreksi Esai (Tombol langsung) --}}
                                @if(($exam->pending_essays_count ?? 0) > 0)
                                    <a href="{{ route('guru.cbt.exams.grade-essays', $exam) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-md transition" title="Koreksi {{ $exam->pending_essays_count }} jawaban esai">
                                        <i class="fas fa-pen-fancy"></i>
                                        <span>Koreksi ({{ $exam->pending_essays_count }})</span>
                                    </a>
                                @elseif($exam->hasEssayQuestions())
                                    <a href="{{ route('guru.cbt.exams.grade-essays', $exam) }}" class="w-8 h-8 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center hover:bg-amber-200 transition" title="Koreksi Esai">
                                        <i class="fas fa-pen-fancy text-sm"></i>
                                    </a>
                                @endif

                                {{-- Aksi Status Khusus Ujian Kelas --}}
                                @if($exam->isClassScope())
                                    @if($exam->status === 'draft')
                                    <form action="{{ route('guru.cbt.exams.publish', $exam) }}" method="POST" class="inline">@csrf
                                        <button class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-100 transition" title="Terbitkan"><i class="fas fa-paper-plane text-sm"></i></button>
                                    </form>
                                    @elseif($exam->status === 'published')
                                    <form action="{{ route('guru.cbt.exams.activate', $exam) }}" method="POST" class="inline">@csrf
                                        <button class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition" title="Aktifkan"><i class="fas fa-play text-sm"></i></button>
                                    </form>
                                    @elseif($exam->status === 'active')
                                    <form action="{{ route('guru.cbt.exams.complete', $exam) }}" method="POST" class="inline" onsubmit="return confirm('Selesaikan ujian ini?')">@csrf
                                        <button class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition" title="Selesaikan"><i class="fas fa-stop text-sm"></i></button>
                                    </form>
                                    @endif
                                @endif

                                {{-- Hasil Ujian --}}
                                <a href="{{ route('guru.cbt.exams.results', $exam) }}" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition" title="Lihat Hasil & Analisis">
                                    <i class="fas fa-chart-bar text-sm"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 rounded-2xl bg-emerald-50 flex items-center justify-center mb-4"><i class="fas fa-laptop-code text-2xl text-emerald-300"></i></div>
                                <p class="text-gray-700 font-medium">Tidak ada ujian CBT yang ditemukan</p>
                                <p class="text-gray-500 text-sm mt-1">Ujian sekolah UTS/UAS mapel Anda atau ujian kelas akan muncul di sini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($exams->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/50">{{ $exams->links() }}</div>
        @endif
    </div>
</div>
@endsection
