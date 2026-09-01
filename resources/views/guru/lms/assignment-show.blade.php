@extends('layouts.guru')

@section('title', $assignment->title . ' - Tugas LMS')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    .stat-grad-blue   { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
    .stat-grad-green  { background: linear-gradient(135deg, #10b981, #047857); }
    .stat-grad-amber  { background: linear-gradient(135deg, #f59e0b, #b45309); }
    .stat-grad-purple { background: linear-gradient(135deg, #8b5cf6, #5b21b6); }
    .avatar-g0 { background: linear-gradient(135deg, #f472b6, #ec4899); }
    .avatar-g1 { background: linear-gradient(135deg, #60a5fa, #3b82f6); }
    .avatar-g2 { background: linear-gradient(135deg, #34d399, #059669); }
    .avatar-g3 { background: linear-gradient(135deg, #fbbf24, #d97706); }
    .avatar-g4 { background: linear-gradient(135deg, #a78bfa, #7c3aed); }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .panel-anim { animation: slideDown 0.3s ease; }

    .score-progress {
        height: 6px;
        background: #e5e7eb;
        border-radius: 999px;
        overflow: hidden;
    }
    .score-progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #10b981, #059669);
        border-radius: 999px;
        transition: width 0.6s ease;
    }
    .prose ol, .prose-invert ol {
        list-style-type: decimal !important;
        list-style-position: outside !important;
        margin-left: 1.5rem !important;
        padding-left: 0.5rem !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }
    .prose ul, .prose-invert ul {
        list-style-type: disc !important;
        list-style-position: outside !important;
        margin-left: 1.5rem !important;
        padding-left: 0.5rem !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }
    .prose li, .prose-invert li {
        display: list-item !important;
        margin-bottom: 0.35rem !important;
        padding-left: 0.25rem !important;
    }
    .prose-invert code {
        background-color: rgba(255, 255, 255, 0.15) !important;
        color: #fde047 !important;
        padding: 0.15rem 0.4rem !important;
        border-radius: 0.375rem !important;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
        font-size: 0.875em !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
    }
    .prose:not(.prose-invert) code {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        padding: 0.15rem 0.4rem !important;
        border-radius: 0.375rem !important;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
        font-size: 0.875em !important;
        border: 1px solid #e2e8f0 !important;
    }
</style>
@endpush

@section('content')
@php
    $avgScore = $assignment->submissions->whereNotNull('score')->avg('score');
    $ungradedCount = $totalSubmissions - $gradedCount;
@endphp

<div class="space-y-6">

{{-- =============================== HEADER =============================== --}}
<div class="rounded-3xl p-6 md:p-8 shadow-xl border-2 border-slate-800 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white relative overflow-hidden space-y-5">

    {{-- Top Bar: Breadcrumb + Action Buttons --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-700/80 pb-4">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs font-bold text-amber-400 tracking-wide flex-wrap">
            <i class="fas fa-graduation-cap"></i>
            <a href="{{ route('guru.lms.show', $course->id) }}?tab=assignments"
               class="hover:underline text-amber-400 font-bold">{{ $course->name }}</a>
            <i class="fas fa-chevron-right text-[10px] opacity-70"></i>
            <span class="text-slate-300">Penugasan Siswa</span>
            <i class="fas fa-chevron-right text-[10px] opacity-70"></i>
            <span class="text-white bg-slate-800/90 px-2.5 py-0.5 rounded-lg border border-slate-700 font-bold">{{ Str::limit($assignment->title, 35) }}</span>
        </nav>

        {{-- Action Buttons (Edit / Delete) --}}
        <div class="flex items-center gap-2.5 shrink-0">
            <a href="{{ route('guru.lms.assignments.edit', $assignment->id) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black tracking-wide shadow-sm transition hover:opacity-90"
               style="background-color: #fbbf24 !important; color: #000000 !important; border: 2px solid #f59e0b !important;">
                <i class="fas fa-edit text-xs" style="color: #000000 !important;"></i>
                <span style="color: #000000 !important;">Edit Tugas</span>
            </a>

            <form action="{{ route('guru.lms.assignments.destroy', $assignment->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus tugas ini beserta semua submisi siswa? Tindakan ini tidak dapat dibatalkan.')">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black tracking-wide shadow-sm transition hover:opacity-90"
                       style="background-color: #fee2e2 !important; color: #dc2626 !important; border: 2px solid #fca5a5 !important;">
                    <i class="fas fa-trash text-xs" style="color: #dc2626 !important;"></i>
                    <span style="color: #dc2626 !important;">Hapus</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Title Section: Full Width --}}
    <div>
        <h1 class="text-2xl lg:text-3xl font-black text-white leading-snug tracking-wide">{{ $assignment->title }}</h1>
    </div>

    {{-- Metadata Badges: Full Width Horizontal Row --}}
    <div class="flex flex-wrap items-center gap-3 pt-1">
        @if($assignment->isGroupAssignment())
        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black tracking-wide shadow-sm"
              style="background-color: #7c3aed !important; color: #ffffff !important; border: 2px solid #a78bfa !important;">
            <i class="fas fa-users text-xs"></i> <span>TUGAS KELOMPOK</span>
        </span>
        @else
        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black tracking-wide shadow-sm"
              style="background-color: #2563eb !important; color: #ffffff !important; border: 2px solid #60a5fa !important;">
            <i class="fas fa-user text-xs"></i> <span>TUGAS INDIVIDU</span>
        </span>
        @endif

        @if($assignment->deadline)
        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black tracking-wide shadow-sm"
              style="{{ $assignment->isOverdue() ? 'background-color: #e11d48 !important; color: #ffffff !important; border: 2px solid #fda4af !important;' : 'background-color: #fef08a !important; color: #000000 !important; border: 2px solid #fde047 !important;' }}">
            <i class="fas fa-clock text-xs"></i>
            <span>@if($assignment->isOverdue()) ⚠ TERLAMBAT — @endif{{ $assignment->deadline->format('d M Y, H:i') }}</span>
        </span>
        @endif

        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black tracking-wide shadow-sm"
              style="background-color: #34d399 !important; color: #000000 !important; border: 2px solid #6ee7b7 !important;">
            <i class="fas fa-star text-xs" style="color: #000000 !important;"></i>
            <span style="color: #000000 !important;">Skor Maks: {{ $assignment->max_score }}</span>
        </span>
    </div>

    {{-- Instruction / Description Card --}}
    @if($assignment->description)
    <div class="relative p-5 bg-slate-950/90 rounded-2xl border-2 border-slate-700 text-slate-100 text-sm font-medium leading-relaxed prose prose-invert max-w-none shadow-md">
        <div class="text-[11px] font-black uppercase text-amber-400 tracking-wider mb-2 flex items-center gap-1.5 not-prose">
            <i class="fas fa-info-circle"></i> Instruksi &amp; Petunjuk Tugas:
        </div>
        {!! balanceHtmlTags($assignment->description) !!}
    </div>
    @endif
</div>

{{-- ============================== 4 STAT CARDS ============================== --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    {{-- Total Dikumpulkan --}}
    <div class="stat-grad-blue rounded-xl p-5 text-white shadow-lg shadow-blue-300/30 relative overflow-hidden group">
        <div class="absolute -bottom-6 -right-6 w-28 h-28 bg-white/10 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
        <div class="w-10 h-10 bg-white/25 rounded-xl flex items-center justify-center mb-3">
            <i class="fas fa-inbox text-lg"></i>
        </div>
        <div class="text-3xl font-semibold">{{ $totalSubmissions }}</div>
        <div class="text-xs text-blue-100 font-semibold uppercase tracking-wide mt-1">Total Dikumpulkan</div>
    </div>

    {{-- Sudah Dinilai --}}
    <div class="stat-grad-green rounded-xl p-5 text-white shadow-lg shadow-emerald-300/30 relative overflow-hidden group">
        <div class="absolute -bottom-6 -right-6 w-28 h-28 bg-white/10 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
        <div class="w-10 h-10 bg-white/25 rounded-xl flex items-center justify-center mb-3">
            <i class="fas fa-check-double text-lg"></i>
        </div>
        <div class="text-3xl font-semibold">{{ $gradedCount }}</div>
        <div class="text-xs text-emerald-100 font-semibold uppercase tracking-wide mt-1">Sudah Dinilai</div>
    </div>

    {{-- Belum Dinilai --}}
    <div class="stat-grad-amber rounded-xl p-5 text-white shadow-lg shadow-amber-300/30 relative overflow-hidden group">
        <div class="absolute -bottom-6 -right-6 w-28 h-28 bg-white/10 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
        <div class="w-10 h-10 bg-white/25 rounded-xl flex items-center justify-center mb-3">
            <i class="fas fa-hourglass-half text-lg"></i>
        </div>
        <div class="text-3xl font-semibold">{{ $ungradedCount }}</div>
        <div class="text-xs text-amber-100 font-semibold uppercase tracking-wide mt-1">Belum Dinilai</div>
    </div>

    {{-- Rata-rata Nilai --}}
    <div class="stat-grad-purple rounded-xl p-5 text-white shadow-lg shadow-purple-300/30 relative overflow-hidden group">
        <div class="absolute -bottom-6 -right-6 w-28 h-28 bg-white/10 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
        <div class="w-10 h-10 bg-white/25 rounded-xl flex items-center justify-center mb-3">
            <i class="fas fa-chart-bar text-lg"></i>
        </div>
        <div class="text-3xl font-semibold">{{ $avgScore ? number_format($avgScore, 1) : '—' }}</div>
        <div class="text-xs text-purple-100 font-semibold uppercase tracking-wide mt-1">Rata-rata Nilai</div>
    </div>
</div>

{{-- Alerts --}}
@if(session('success'))
<div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center gap-3">
    <i class="fas fa-check-circle text-emerald-500"></i>
    <p class="font-medium text-sm">{{ session('success') }}</p>
</div>
@endif
@if(session('error'))
<div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl flex items-center gap-3">
    <i class="fas fa-exclamation-triangle text-rose-500"></i>
    <p class="font-medium text-sm">{{ session('error') }}</p>
</div>
@endif
@if($errors->any())
<div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl">
    <div class="flex items-center gap-3 mb-1">
        <i class="fas fa-exclamation-circle text-rose-500"></i>
        <p class="font-bold text-sm">Gagal Menyimpan Nilai</p>
    </div>
    <ul class="list-disc list-inside text-xs space-y-1 ml-7">
        @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- ============================== ROMBEL FILTER ============================== --}}
@if($classrooms->count() > 1)
<div class="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4">
    <div class="flex items-center gap-3 flex-wrap">
        <div class="flex items-center gap-2 text-xs font-bold text-gray-500 uppercase tracking-wider shrink-0">
            <i class="fas fa-users text-gray-400"></i> Filter Rombel:
        </div>
        <a href="{{ route('guru.lms.assignments.show', $assignment->id) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold border-2 transition
                  {{ !$selectedClassroomId ? 'bg-black text-amber-400 border-black' : 'bg-gray-100 text-gray-700 border-gray-200 hover:bg-amber-300 hover:border-black hover:text-black' }}">
            <i class="fas fa-th-large text-[10px]"></i> Semua Rombel
        </a>
        @foreach($classrooms as $classroom)
        <a href="{{ route('guru.lms.assignments.show', $assignment->id) }}?classroom_id={{ $classroom->id }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold border-2 transition
                  {{ $selectedClassroomId == $classroom->id ? 'bg-black text-amber-400 border-black' : 'bg-gray-100 text-gray-700 border-gray-200 hover:bg-amber-300 hover:border-black hover:text-black' }}">
            <i class="fas fa-users text-[10px]"></i> {{ $classroom->class_name }}
        </a>
        @endforeach
    </div>
    @if($selectedClassroom)
    <div class="mt-2 text-xs text-gray-500 font-semibold flex items-center gap-1.5">
        <i class="fas fa-filter text-amber-500"></i>
        Menampilkan pengumpulan dari: <strong class="text-gray-800">{{ $selectedClassroom->class_name }}</strong>
    </div>
    @endif
</div>
@endif

{{-- ============================== GROUP MANAGEMENT (IF GROUP ASSIGNMENT) ============================== --}}
@if($assignment->isGroupAssignment())
@php
    $groupedStudentIds = $assignment->groups->flatMap(function($grp) {
        return $grp->members->pluck('id')->push($grp->leader_id);
    })->unique()->filter()->toArray();

    $availableStudents = $allEnrolledStudents->reject(fn($s) => in_array($s->id, $groupedStudentIds))->values();
@endphp
<div class="bg-white rounded-2xl border-2 border-purple-200 shadow-md p-6 space-y-6" x-data="{ showAddGroup: false, showAutoGroup: false, activeGroupDetail: null }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-600 text-white rounded-xl flex items-center justify-center shadow-md">
                <i class="fas fa-users text-lg"></i>
            </div>
            <div>
                <h3 class="text-base font-black text-gray-900 flex items-center gap-2 flex-wrap">
                    <span>Manajemen Kelompok Tugas</span>
                    @if($selectedClassroom)
                        <span class="text-xs font-bold text-purple-800 bg-purple-100 border border-purple-300 px-2.5 py-0.5 rounded-lg">
                            <i class="fas fa-chalkboard mr-1"></i> Kelas: {{ $selectedClassroom->class_name }}
                        </span>
                    @else
                        <span class="text-xs font-bold text-gray-700 bg-gray-100 border border-gray-300 px-2.5 py-0.5 rounded-lg">
                            <i class="fas fa-layer-group mr-1"></i> Semua Rombel Kursus
                        </span>
                    @endif
                </h3>
                <p class="text-xs font-bold text-gray-500">
                    {{ $selectedClassroom ? 'Daftar kelompok khusus untuk siswa kelas ' . $selectedClassroom->class_name . '. Hanya ketua kelompok yang akan mengunggah berkas.' : 'Tentukan kelompok, tunjuk ketua kelompok, dan atur anggota. Hanya ketua kelompok yang akan mengunggah berkas.' }}
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @if($course->courseGroups && $course->courseGroups->isNotEmpty() && $assignment->groups->isEmpty())
            <form action="{{ route('guru.lms.assignments.groups.importCourseGroups', $assignment->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-sm"
                        onclick="return confirm('Terapkan {{ $course->courseGroups->count() }} kelompok dari Master Kelompok Kursus ke tugas ini?')">
                    <i class="fas fa-file-import"></i> <span>Gunakan Kelompok Kursus ({{ $course->courseGroups->count() }})</span>
                </button>
            </form>
            @endif
            @if($availableStudents->isNotEmpty())
            <button type="button" @click="showAddGroup = !showAddGroup; showAutoGroup = false"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black bg-purple-600 text-white hover:bg-purple-700 transition shadow-sm">
                <i class="fas fa-plus"></i> <span x-text="showAddGroup ? 'Batal' : 'Tambah Kelompok Manual'"></span>
            </button>
            <button type="button" @click="showAutoGroup = !showAutoGroup; showAddGroup = false"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black bg-amber-400 text-black hover:bg-amber-500 transition shadow-sm">
                <i class="fas fa-magic"></i> <span x-text="showAutoGroup ? 'Batal' : 'Bagi Otomatis'"></span>
            </button>
            @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                <i class="fas fa-check-double text-emerald-600"></i> Seluruh Siswa {{ $selectedClassroom ? 'Kelas ' . $selectedClassroom->class_name : '' }} Sudah Masuk Kelompok
            </span>
            @endif
        </div>
    </div>

    {{-- Form Tambah Kelompok Manual --}}
    <div x-show="showAddGroup" x-cloak class="p-5 rounded-2xl border-2 border-purple-300 bg-purple-50/50 space-y-4">
        <h4 class="text-xs font-black text-purple-900 uppercase tracking-wider flex items-center gap-2">
            <i class="fas fa-user-plus"></i> Buat Kelompok Baru {{ $selectedClassroom ? 'di ' . $selectedClassroom->class_name : '' }} & Tunjuk Ketua
        </h4>
        <form action="{{ route('guru.lms.assignments.groups.store', $assignment->id) }}" method="POST" class="space-y-4">
            @csrf
            @if($selectedClassroomId)
            <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
            @endif
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Kelompok <span class="text-rose-600">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Kelompok 1" value="Kelompok {{ $assignment->groups->count() + 1 }}"
                           class="w-full border-2 border-gray-300 rounded-xl px-4 py-2.5 text-sm font-bold text-gray-900 focus:ring-2 focus:ring-purple-500 outline-none bg-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Pilih Ketua Kelompok <span class="text-rose-600">* (Yang berhak upload berkas)</span></label>
                    <select name="leader_id" required class="w-full border-2 border-gray-300 rounded-xl px-4 py-2.5 text-sm font-bold text-gray-900 focus:ring-2 focus:ring-purple-500 outline-none bg-white">
                        <option value="">— Pilih Ketua (Tersedia: {{ $availableStudents->count() }} Siswa{{ $selectedClassroom ? ' ' . $selectedClassroom->class_name : '' }}) —</option>
                        @forelse($availableStudents as $std)
                        <option value="{{ $std->id }}">{{ $std->user->name ?? $std->full_name }} (NISN: {{ $std->nisn ?? '-' }})</option>
                        @empty
                        <option value="" disabled>Semua siswa sudah terdaftar di kelompok lain</option>
                        @endforelse
                    </select>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-gray-700">Pilih Anggota Kelompok (Centang Siswa):</label>
                    <span class="text-[11px] font-bold text-purple-700">Tersedia: {{ $availableStudents->count() }} Orang dari {{ $allEnrolledStudents->count() }} Siswa{{ $selectedClassroom ? ' ' . $selectedClassroom->class_name : '' }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 max-h-56 overflow-y-auto p-3 bg-white border border-gray-200 rounded-xl">
                    @forelse($availableStudents as $std)
                    <label class="flex items-center gap-2 text-xs font-bold text-gray-700 hover:bg-purple-50 p-1.5 rounded-lg cursor-pointer transition">
                        <input type="checkbox" name="member_ids[]" value="{{ $std->id }}" class="rounded text-purple-600 focus:ring-0">
                        <span class="truncate">{{ $std->user->name ?? $std->full_name }}</span>
                    </label>
                    @empty
                    <div class="col-span-full py-4 text-center text-xs text-emerald-800 font-bold bg-emerald-50 rounded-lg border border-emerald-200">
                        <i class="fas fa-check-circle mr-1 text-emerald-600"></i> Seluruh {{ $allEnrolledStudents->count() }} siswa sudah terbagi ke dalam kelompok.
                    </div>
                    @endforelse
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="showAddGroup = false" class="px-4 py-2 rounded-xl text-xs font-bold bg-gray-200 text-gray-700 hover:bg-gray-300">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-black bg-purple-600 text-white hover:bg-purple-700 shadow-sm">Simpan Kelompok</button>
            </div>
        </form>
    </div>

    {{-- Form Bagi Otomatis --}}
    <div x-show="showAutoGroup" x-cloak class="p-5 rounded-2xl border-2 border-amber-300 bg-amber-50/50 space-y-4">
        <h4 class="text-xs font-black text-amber-900 uppercase tracking-wider flex items-center gap-2">
            <i class="fas fa-magic"></i> Bagi Siswa {{ $selectedClassroom ? 'Kelas ' . $selectedClassroom->class_name : 'Kursus' }} Menjadi N Kelompok Secara Otomatis
        </h4>
        <form action="{{ route('guru.lms.assignments.groups.autoGenerate', $assignment->id) }}" method="POST" class="space-y-4">
            @csrf
            @if($selectedClassroomId)
            <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
            @endif
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Jumlah Kelompok yang Diinginkan <span class="text-rose-600">*</span></label>
                    <input type="number" name="group_count" min="2" max="20" value="4" required
                           class="w-full border-2 border-gray-300 rounded-xl px-4 py-2.5 text-sm font-bold text-gray-900 focus:ring-2 focus:ring-amber-500 outline-none bg-white">
                    <p class="text-[11px] text-gray-500 font-medium mt-1">
                        Sistem akan membagi {{ $availableStudents->count() }} siswa yang belum memiliki kelompok secara merata dan acak.
                    </p>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full px-5 py-2.5 rounded-xl text-xs font-black bg-amber-500 text-black hover:bg-amber-600 shadow-sm">
                        <i class="fas fa-random mr-1"></i> Acak & Bentuk Kelompok
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Daftar Kelompok Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($assignment->groups as $grp)
        @php
            $grpSub = $grp->submission;
            $grpClassroomName = $grp->classroom_name ?? ($selectedClassroom->class_name ?? 'Rombel');
            $grpLeaderName = $grp->leader->user->name ?? $grp->leader->full_name ?? 'Belum Ditunjuk';
            $grpMembersList = $grp->members->map(function($m) use ($grp) {
                return [
                    'id' => $m->id,
                    'name' => $m->user->name ?? $m->full_name ?? '-',
                    'nisn' => $m->nisn ?? '-',
                    'nis' => $m->nis ?? '-',
                    'is_leader' => (int)$m->id === (int)$grp->leader_id,
                ];
            })->values()->all();

            $grpDataJson = [
                'id' => $grp->id,
                'name' => $grp->name,
                'classroom' => $grpClassroomName,
                'leader' => [
                    'name' => $grpLeaderName,
                    'nisn' => $grp->leader->nisn ?? '-',
                    'nis' => $grp->leader->nis ?? '-',
                ],
                'members' => $grpMembersList,
                'submission' => $grpSub ? [
                    'submitted_at' => $grpSub->submitted_at ? $grpSub->submitted_at->format('d M Y, H:i') : '-',
                    'status' => $grpSub->status,
                    'score' => $grpSub->score,
                    'file_url' => $grpSub->file_path ? asset('storage/' . $grpSub->file_path) : null,
                    'file_name' => $grpSub->file_path ? basename($grpSub->file_path) : null,
                    'submission_text' => $grpSub->submission_text,
                    'feedback' => $grpSub->feedback,
                ] : null,
                'max_score' => $assignment->max_score ?? 100,
            ];
        @endphp
        <div class="p-4 rounded-2xl border-2 border-gray-200 bg-white hover:border-purple-300 transition-all shadow-xs flex flex-col justify-between space-y-3">
            <div>
                <div class="flex items-center justify-between gap-2 mb-2">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-xs font-black text-purple-700 uppercase tracking-wider bg-purple-100 px-2.5 py-1 rounded-lg border border-purple-200">
                            {{ $grp->name }}
                        </span>
                        @if($grp->classroom_name)
                        <span class="text-[10px] font-bold text-gray-600 bg-gray-100 px-2 py-0.5 rounded-md border border-gray-200">
                            {{ $grp->classroom_name }}
                        </span>
                        @endif
                    </div>
                    <form action="{{ route('guru.lms.assignments.groups.destroy', [$assignment->id, $grp->id]) }}" method="POST" onsubmit="return confirm('Hapus kelompok {{ $grp->name }}?')">
                        @csrf @method('DELETE')
                        @if($selectedClassroomId)
                        <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
                        @endif
                        <button type="submit" class="text-gray-400 hover:text-rose-600 text-xs p-1" title="Hapus Kelompok">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>

                <div class="space-y-1.5 text-xs">
                    <div class="flex items-center gap-1.5 font-bold text-gray-900">
                        <span class="text-amber-500 shrink-0">👑 Ketua:</span>
                        <span class="truncate">{{ $grpLeaderName }}</span>
                    </div>
                    <div class="text-gray-500 text-[11px] leading-snug">
                        <span class="font-bold text-gray-700">Anggota ({{ $grp->members->count() }}):</span>
                        <p class="text-gray-600 mt-0.5 line-clamp-2">
                            {{ $grp->members->pluck('user.name')->filter()->implode(', ') ?: ($grp->members->pluck('full_name')->filter()->implode(', ') ?: '—') }}
                        </p>
                    </div>

                    {{-- Tombol Klik untuk Melihat Daftar Anggota Lengkap Tanpa Terpotong --}}
                    <button type="button"
                            @click='activeGroupDetail = @json($grpDataJson)'
                            class="mt-2 w-full text-xs font-bold text-purple-700 bg-purple-50 hover:bg-purple-100 hover:border-purple-300 border border-purple-200 rounded-xl py-1.5 px-2.5 flex items-center justify-between transition shadow-2xs cursor-pointer">
                        <span class="flex items-center gap-1.5"><i class="fas fa-users-viewfinder text-purple-600"></i> Detail Anggota ({{ $grp->members->count() }})</span>
                        <span class="text-[10px] font-black text-purple-900 bg-purple-200 px-1.5 py-0.2 rounded-md">Buka &rarr;</span>
                    </button>
                </div>
            </div>

            <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                @if($grpSub && $grpSub->status !== 'draft')
                    <span class="inline-flex items-center gap-1 text-emerald-600 font-bold">
                        <i class="fas fa-check-circle"></i> Sudah Kumpul
                    </span>
                    @if($grpSub->score !== null)
                    <span class="font-black text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md">
                        Nilai: {{ $grpSub->score }}/{{ $assignment->max_score }}
                    </span>
                    @else
                    <span class="text-amber-600 font-bold text-[11px]">Belum Dinilai</span>
                    @endif
                @else
                    <span class="text-gray-400 italic">Belum Mengumpulkan</span>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-full py-8 text-center bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
            <i class="fas fa-users-slash text-3xl text-gray-300 mb-2"></i>
            <p class="text-xs font-bold text-gray-600">Belum ada kelompok yang dibuat{{ $selectedClassroom ? ' untuk kelas ' . $selectedClassroom->class_name : '' }}.</p>
            <p class="text-[11px] text-gray-400">Klik <strong>+ Tambah Kelompok Manual</strong> atau <strong>Bagi Otomatis</strong> di atas untuk membagi siswa.</p>
        </div>
        @endforelse
    </div>

    {{-- ============================== MODAL DETAIL ANGGOTA KELOMPOK INTERAKTIF ============================== --}}
    <div x-show="activeGroupDetail" 
         x-cloak 
         class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-fade-in"
         @click.self="activeGroupDetail = null"
         @keydown.escape.window="activeGroupDetail = null">
        <div class="bg-white rounded-3xl max-w-lg w-full max-h-[90vh] overflow-hidden shadow-2xl border-2 border-purple-300 flex flex-col transform transition-all" @click.stop>
            
            {{-- Modal Header --}}
            <div class="p-5 bg-gradient-to-r from-purple-700 via-purple-800 to-indigo-800 text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30 text-white font-black text-base shadow-inner">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-base font-black tracking-tight text-white" x-text="activeGroupDetail?.name"></h3>
                            <span class="text-[10px] font-mono font-bold bg-white/25 text-white px-2 py-0.5 rounded-md border border-white/30" x-text="activeGroupDetail?.classroom"></span>
                        </div>
                        <p class="text-xs text-purple-100 font-medium mt-0.5">Daftar lengkap anggota untuk penagihan & presensi di kelas</p>
                    </div>
                </div>
                <button type="button" @click="activeGroupDetail = null" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/25 text-white flex items-center justify-center text-xs transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-5 space-y-4 overflow-y-auto max-h-[calc(90vh-140px)] text-gray-800 text-xs">
                
                {{-- Status Pengumpulan Bar --}}
                <div class="p-3.5 rounded-2xl border flex items-center justify-between flex-wrap gap-2"
                     :class="activeGroupDetail?.submission ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200'">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl flex items-center justify-center text-sm font-bold shadow-2xs shrink-0"
                              :class="activeGroupDetail?.submission ? 'bg-emerald-600 text-white' : 'bg-amber-500 text-white'">
                            <i :class="activeGroupDetail?.submission ? 'fas fa-check-circle' : 'fas fa-clock'"></i>
                        </span>
                        <div>
                            <div class="font-black text-xs" :class="activeGroupDetail?.submission ? 'text-emerald-900' : 'text-amber-900'"
                                 x-text="activeGroupDetail?.submission ? 'Sudah Mengumpulkan Tugas' : 'Belum Mengumpulkan Tugas'"></div>
                            <div class="text-[11px] font-medium" :class="activeGroupDetail?.submission ? 'text-emerald-700' : 'text-amber-700'"
                                 x-text="activeGroupDetail?.submission ? `Waktu Kumpul: ${activeGroupDetail.submission.submitted_at}` : 'Menunggu pengunggahan berkas oleh ketua kelompok'"></div>
                        </div>
                    </div>
                    <template x-if="activeGroupDetail?.submission?.score !== null && activeGroupDetail?.submission?.score !== undefined">
                        <span class="px-3 py-1 bg-emerald-600 text-white rounded-xl font-black text-xs shadow-2xs"
                              x-text="`Nilai: ${activeGroupDetail.submission.score}/${activeGroupDetail.max_score}`"></span>
                    </template>
                </div>

                {{-- Ketua Kelompok Card --}}
                <div class="bg-purple-50/70 border border-purple-200 rounded-2xl p-3.5">
                    <div class="text-[10px] font-mono font-bold uppercase text-purple-800 mb-1 flex items-center gap-1.5">
                        <span class="text-amber-500">👑</span> KETUA KELOMPOK (PENANGGUNG JAWAB UPLOAD)
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-purple-600 text-white font-black flex items-center justify-center shadow-xs text-xs shrink-0">
                            <i class="fas fa-crown text-amber-300"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="font-extrabold text-sm text-gray-900 leading-tight break-words" x-text="activeGroupDetail?.leader?.name"></div>
                            <div class="text-[11px] font-mono text-purple-700 mt-0.5" x-text="`NISN: ${activeGroupDetail?.leader?.nisn || '-'}`"></div>
                        </div>
                    </div>
                </div>

                {{-- Daftar Seluruh Anggota Kelompok (Tampil Lengkap Baris per Baris Tanpa Terpotong) --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xs font-black text-gray-900 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fas fa-list-check text-purple-600"></i>
                            Daftar Seluruh Anggota (<span x-text="activeGroupDetail?.members?.length || 0"></span> Siswa):
                        </h4>
                        <span class="text-[10px] font-bold text-gray-500">Nama lengkap & NISN</span>
                    </div>

                    <div class="border border-gray-200 rounded-2xl overflow-hidden divide-y divide-gray-100 bg-white">
                        <template x-for="(member, idx) in activeGroupDetail?.members" :key="member.id">
                            <div class="p-3 flex items-center justify-between gap-3 hover:bg-purple-50/40 transition">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-6 h-6 rounded-lg bg-gray-100 text-gray-700 font-mono font-bold text-[11px] flex items-center justify-center shrink-0"
                                          x-text="idx + 1"></span>
                                    <div class="min-w-0">
                                        <div class="font-bold text-xs text-gray-900 flex items-center gap-2 flex-wrap">
                                            <span x-text="member.name" class="break-words font-extrabold"></span>
                                            <template x-if="member.is_leader">
                                                <span class="text-[9px] font-bold uppercase bg-amber-100 text-amber-800 border border-amber-300 px-1.5 py-0.2 rounded-md shrink-0">👑 Ketua</span>
                                            </template>
                                        </div>
                                        <div class="text-[10px] font-mono text-gray-500 mt-0.5" x-text="`NISN: ${member.nisn || '-'}`"></div>
                                    </div>
                                </div>
                                <div class="shrink-0 flex items-center gap-1.5">
                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md">
                                        <i class="fas fa-check text-emerald-500"></i> Terdaftar
                                    </span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Detail Submission Berkas Jika Ada --}}
                <template x-if="activeGroupDetail?.submission?.file_url">
                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-3.5 space-y-2">
                        <div class="text-[10px] font-mono font-bold uppercase text-gray-600 flex items-center gap-1.5">
                            <i class="fas fa-paperclip text-indigo-600"></i> BERKAS JAWABAN TUGAS:
                        </div>
                        <a :href="activeGroupDetail.submission.file_url" target="_blank"
                           class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-gray-300 hover:border-indigo-500 rounded-xl text-xs font-bold text-indigo-700 hover:text-indigo-900 transition shadow-2xs">
                            <i class="fas fa-file-download text-indigo-500 text-sm"></i>
                            <span x-text="activeGroupDetail.submission.file_name || 'Download Berkas Jawaban'"></span>
                        </a>
                    </div>
                </template>

            </div>

            {{-- Modal Footer --}}
            <div class="p-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between gap-3">
                <span class="text-[11px] text-gray-500 font-medium">Gunakan daftar ini untuk penagihan di kelas</span>
                <button type="button" @click="activeGroupDetail = null"
                        class="px-5 py-2 rounded-xl text-xs font-bold bg-gray-800 text-white hover:bg-black transition shadow-xs">
                    Tutup
                </button>
            </div>

        </div>
    </div>

</div>
@endif

{{-- ============================ SUBMISSIONS TABLE ============================ --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

    {{-- Table Header --}}
    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-sm">
                <i class="fas fa-inbox text-white text-sm"></i>
            </div>
            <div>
                <h2 class="font-bold text-gray-800">{{ $assignment->isGroupAssignment() ? 'Pengumpulan Berkas Kelompok' : 'Pengumpulan Siswa' }}</h2>
                <p class="text-xs text-gray-400">{{ $totalSubmissions }} submission diterima{{ $selectedClassroom ? ' dari ' . $selectedClassroom->class_name : '' }}</p>
            </div>
        </div>
        @if($ungradedCount > 0)
        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-50 text-amber-700 text-xs font-semibold border border-amber-200">
            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse inline-block"></span>
            {{ $ungradedCount }} menunggu penilaian
        </span>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50/70 border-b border-gray-100 text-xs font-semibold text-gray-400 tracking-wide">
                    <th class="text-left px-6 py-3.5">{{ $assignment->isGroupAssignment() ? 'Kelompok & Pengumpul' : 'Siswa' }}</th>
                    <th class="text-left px-4 py-3.5">Waktu Kumpul</th>
                    <th class="text-center px-4 py-3.5">Status</th>
                    <th class="text-center px-4 py-3.5">Nilai</th>
                    <th class="text-center px-4 py-3.5">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignment->submissions as $index => $sub)
                @php
                    $avatarGrads = ['avatar-g0','avatar-g1','avatar-g2','avatar-g3','avatar-g4'];
                    $ag   = $avatarGrads[$index % 5];
                    $name = $sub->student->user->name ?? $sub->student->full_name ?? 'N/A';
                    $parts = array_values(array_filter(explode(' ', trim(preg_replace('/[^a-zA-Z\s]/', '', $name)))));
                    $init = count($parts) >= 2
                        ? strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1))
                        : strtoupper(substr($name, 0, 2));
                    $statusClass = match($sub->status) {
                        'graded'    => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'submitted' => 'bg-blue-100 text-blue-700 border-blue-200',
                        'late'      => 'bg-red-100 text-red-700 border-red-200',
                        default     => 'bg-gray-100 text-gray-500 border-gray-200',
                    };
                    $statusIcon = match($sub->status) {
                        'graded'    => 'fa-check-circle',
                        'submitted' => 'fa-paper-plane',
                        'late'      => 'fa-exclamation-circle',
                        default     => 'fa-file-alt',
                    };
                @endphp

                {{-- Row wrapper with Alpine state --}}
                <tbody x-data="{ open: false }">
                <tr class="border-b border-gray-50 hover:bg-emerald-50/30 transition-colors cursor-default">
                    {{-- Siswa / Kelompok --}}
                    <td class="px-6 py-4">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 {{ $sub->group ? 'bg-purple-600' : $ag }} rounded-full flex items-center justify-center
                                        text-white font-bold text-sm shrink-0 shadow-sm ring-2 ring-white">
                                @if($sub->group)
                                    <i class="fas fa-users text-sm"></i>
                                @else
                                    {{ $init }}
                                @endif
                            </div>
                            <div>
                                @if($sub->group)
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <span class="text-xs font-black text-purple-700 bg-purple-100 px-2 py-0.5 rounded border border-purple-200 uppercase">
                                            {{ $sub->group->name }}
                                        </span>
                                        <span class="text-[10px] text-gray-400 font-bold">({{ $sub->group->members->count() }} Anggota)</span>
                                    </div>
                                    <p class="font-bold text-gray-800 text-xs leading-snug">
                                        <span class="text-amber-500">👑 Pengumpul (Ketua):</span> {{ $name }}
                                    </p>
                                    <p class="text-[10px] text-gray-500 mt-0.5 line-clamp-1">
                                        Anggota: {{ $sub->group->members->pluck('user.name')->filter()->implode(', ') ?: ($sub->group->members->pluck('full_name')->filter()->implode(', ') ?: '—') }}
                                    </p>
                                @else
                                    <p class="font-semibold text-gray-800 leading-snug">{{ $name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">NISN: {{ $sub->student->nisn ?? '—' }}</p>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Waktu Kumpul --}}
                    <td class="px-4 py-4">
                        @if($sub->submitted_at)
                        <p class="font-medium text-gray-700">{{ $sub->submitted_at->diffForHumans() }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $sub->submitted_at->format('d M Y, H:i') }}</p>
                        @else
                        <span class="text-gray-300 italic text-xs">Belum dikumpulkan</span>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td class="px-4 py-4 text-center">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusClass }}">
                            <i class="fas {{ $statusIcon }} text-[10px]"></i>
                            {{ $sub->getStatusLabel() }}
                        </span>
                    </td>

                    {{-- Nilai --}}
                    <td class="px-4 py-4 text-center">
                        @if($sub->score !== null)
                        <div>
                            <span class="text-xl font-semibold {{ $sub->score >= $assignment->max_score * 0.6 ? 'text-emerald-600' : 'text-red-500' }}">
                                {{ $sub->score }}
                            </span>
                            <span class="text-gray-300 text-sm">/{{ $assignment->max_score }}</span>
                        </div>
                        @else
                        <span class="text-gray-300 text-xl">—</span>
                        @endif
                    </td>

                    {{-- Aksi --}}
                    <td class="px-4 py-4 text-center">
                        @if($sub->status !== 'draft')
                        <button @click="open = !open"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all
                                       bg-gradient-to-r from-emerald-500 to-emerald-600 text-white
                                       shadow-sm hover:shadow-md hover:-translate-y-0.5 active:translate-y-0">
                            <i class="fas text-[10px]" :class="open ? 'fa-chevron-up' : 'fa-pen'"></i>
                            <span x-text="open ? 'Tutup' : '{{ $sub->score !== null ? 'Edit Nilai' : 'Beri Nilai' }}'"></span>
                        </button>
                        @else
                        <span class="inline-flex items-center gap-1 text-xs text-gray-400 bg-gray-50 px-2.5 py-1 rounded-full border border-gray-200">
                            <i class="fas fa-file-alt text-[9px]"></i> Draft
                        </span>
                        @endif
                    </td>
                </tr>

                {{-- SLIDE-DOWN GRADING PANEL --}}
                @if($sub->status !== 'draft')
                <tr x-show="open" x-cloak>
                    <td colspan="5" class="px-0 py-0 border-b border-gray-100">
                        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 border-t border-emerald-100 panel-anim">
                            <div class="px-6 py-5">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                                    {{-- Left: Submission Content (In-Browser Viewer) --}}
                                    <div class="space-y-3">
                                        <h4 class="font-semibold text-gray-700 text-sm flex items-center gap-2">
                                            <i class="fas fa-file-alt text-emerald-500"></i>
                                            Pratinjau Jawaban Siswa (In-Browser Viewer)
                                        </h4>

                                        @if($sub->submission_text)
                                        <div class="bg-white rounded-xl border border-emerald-200 p-4 text-sm text-gray-800 leading-relaxed max-h-48 overflow-y-auto shadow-inner font-medium">
                                            {{ $sub->submission_text }}
                                        </div>
                                        @endif

                                        @if($sub->file_path)
                                            @if($sub->isPdf())
                                            <div class="w-full h-80 rounded-xl overflow-hidden border border-emerald-300 shadow-inner bg-slate-900">
                                                <iframe src="{{ Storage::url($sub->file_path) }}" class="w-full h-full"></iframe>
                                            </div>
                                            @elseif($sub->isImage())
                                            <div class="w-full max-h-80 rounded-xl overflow-hidden border border-emerald-300 bg-slate-900 flex items-center justify-center p-2">
                                                <img src="{{ Storage::url($sub->file_path) }}" class="max-h-76 object-contain rounded-lg" alt="Jawaban Gambar">
                                            </div>
                                            @endif

                                            <div class="flex items-center gap-2">
                                                <a href="{{ Storage::url($sub->file_path) }}" target="_blank"
                                                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-emerald-200
                                                          text-emerald-700 text-sm font-semibold hover:bg-emerald-50 transition shadow-sm">
                                                    <i class="fas fa-external-link-alt"></i> Buka Berkas Penuh
                                                </a>
                                                <a href="{{ route('guru.lms.submissions.download', $sub->id) }}"
                                                   class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-emerald-600 border border-emerald-700
                                                          text-white text-xs font-bold hover:bg-emerald-700 transition shadow-sm">
                                                    <i class="fas fa-download"></i> Unduh File Tugas
                                                </a>
                                            </div>
                                        @endif

                                        @if(!$sub->submission_text && !$sub->file_path)
                                        <p class="text-sm text-gray-400 italic">Tidak ada konten submission.</p>
                                        @endif
                                    </div>

                                    {{-- Right: Grading Form & Feedback Presets --}}
                                    <div x-data="{ feedbackText: '{{ addslashes($sub->feedback ?? '') }}' }">
                                        <h4 class="font-semibold text-gray-700 text-sm flex items-center gap-2 mb-3">
                                            <i class="fas fa-star text-amber-500"></i>
                                            Form Penilaian & Umpan Balik Cepat
                                        </h4>
                                        <form action="{{ route('guru.lms.submissions.grade', $sub->id) }}" method="POST"
                                              class="bg-white rounded-xl border border-emerald-200 p-4 shadow-sm space-y-4">
                                            @csrf
                                            <input type="hidden" name="action_type" id="action_type_{{ $sub->id }}" value="grade">

                                            @if($sub->group)
                                            <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl text-xs text-purple-900 font-bold flex items-center gap-2">
                                                <i class="fas fa-users text-purple-600 text-sm flex-shrink-0"></i>
                                                <span>Nilai dan feedback ini otomatis tersinkronisasi ke seluruh anggota kelompok (<strong>{{ $sub->group->name }}</strong>: {{ $sub->group->members->count() }} siswa).</span>
                                            </div>
                                            @endif

                                            {{-- Score Input with Rubrik --}}
                                            @php $rubrik = $assignment->getRubric(); @endphp
                                            <div x-data="{
                                                rubricScores: {{ json_encode(array_fill(0, count($rubrik), 0)) }},
                                                get calculatedScore() {
                                                    let total = 0, bobot = 0;
                                                    {{ $assignment->hasRubric() ? 'const rubrik = '.json_encode($rubrik).';' : 'const rubrik = '.json_encode($assignment->getDefaultRubricTemplate()).';' }}
                                                    rubrik.forEach((r, i) => {
                                                        const nilai = Math.min(parseFloat(this.rubricScores[i] || 0), parseFloat(r.maks || 100));
                                                        const b = parseFloat(r.bobot || 0);
                                                        total += nilai * (b / 100);
                                                        bobot += b;
                                                    });
                                                    return bobot > 0 ? Math.round((total / (bobot / 100)) * 10) / 10 : 0;
                                                },
                                                get displayScore() {
                                                    return this.calculatedScore;
                                                }
                                            }">
                                                {{-- Rubrik Components --}}
                                                <div class="space-y-3 mb-4">
                                                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">
                                                        <i class="fas fa-tasks text-purple-600 mr-1"></i> Penilaian Berdasarkan Rubrik
                                                    </label>
                                                    @foreach($rubrik as $i => $r)
                                                    <div class="flex items-center gap-3">
                                                        <span class="w-28 text-xs font-bold text-gray-700 flex-shrink-0">{{ $r['nama'] }}</span>
                                                        <input type="range" min="0" max="{{ $r['maks'] }}" step="1"
                                                               x-model="rubricScores[{{ $i }}]"
                                                               class="flex-1 h-2 rounded-lg appearance-none cursor-pointer accent-purple-600">
                                                        <input type="number" name="rubric_scores[{{ $i }}]"
                                                               x-model="rubricScores[{{ $i }}]"
                                                               min="0" max="{{ $r['maks'] }}"
                                                               class="w-16 border border-gray-300 rounded-lg px-2 py-1 text-xs font-bold text-center focus:ring-2 focus:ring-purple-500 outline-none">
                                                        <span class="text-[10px] font-bold text-gray-400 w-8">/{{ $r['maks'] }}</span>
                                                    </div>
                                                    @endforeach
                                                </div>

                                                {{-- Calculated Score --}}
                                                <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-xs font-bold text-purple-900">Skor Akhir (Hitung Otomatis)</span>
                                                        <span class="text-lg font-black text-purple-900" x-text="displayScore + ' / {{ $assignment->max_score }}'"></span>
                                                    </div>
                                                    <div class="score-progress mt-2">
                                                        <div class="score-progress-bar" :style="`width: ${Math.min(100, (displayScore / {{ $assignment->max_score ?? 100 }}) * 100)}%`"></div>
                                                    </div>
                                                </div>

                                                <input type="hidden" name="score" :value="displayScore">
                                            </div>

                                            {{-- Feedback Presets Chips --}}
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">
                                                    Preset Umpan Balik Cepat
                                                </label>
                                                <div class="flex flex-wrap gap-1.5 mb-2">
                                                    <button type="button" @click="feedbackText = 'Sangat baik dan rapi! 🌟'" class="px-2.5 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg text-xs font-bold hover:bg-emerald-100 transition">
                                                        🌟 Sangat Baik
                                                    </button>
                                                    <button type="button" @click="feedbackText = 'Tugas lengkap, tingkatkan kerapihan. 📝'" class="px-2.5 py-1 bg-blue-50 text-blue-800 border border-blue-200 rounded-lg text-xs font-bold hover:bg-blue-100 transition">
                                                        📝 Lengkap
                                                    </button>
                                                    <button type="button" @click="feedbackText = 'Perlu perbaikan pada bagian jawaban akhir. ⚠️'" class="px-2.5 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-lg text-xs font-bold hover:bg-amber-100 transition">
                                                        ⚠️ Perlu Perbaikan
                                                    </button>
                                                    <button type="button" @click="feedbackText = 'Mohon revisi dan unggah ulang berkas perbaikan. 🔄'" class="px-2.5 py-1 bg-red-50 text-red-800 border border-red-200 rounded-lg text-xs font-bold hover:bg-red-100 transition">
                                                        🔄 Mohon Revisi
                                                    </button>
                                                </div>

                                                <textarea name="feedback" rows="3" x-model="feedbackText"
                                                          placeholder="Tulis feedback untuk siswa..."
                                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-medium text-gray-900
                                                                 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition resize-none"></textarea>
                                            </div>

                                            <div class="grid grid-cols-2 gap-2">
                                                <button type="submit" onclick="document.getElementById('action_type_{{ $sub->id }}').value='grade'"
                                                        class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white py-2.5 rounded-xl
                                                               text-sm font-bold shadow hover:shadow-md transition-all">
                                                    <i class="fas fa-check-circle mr-1"></i> Simpan Nilai
                                                </button>
                                                <button type="submit" onclick="document.getElementById('action_type_{{ $sub->id }}').value='request_revision'"
                                                        class="bg-gradient-to-r from-amber-500 to-amber-600 text-white py-2.5 rounded-xl
                                                               text-sm font-bold shadow hover:shadow-md transition-all">
                                                    <i class="fas fa-sync-alt mr-1"></i> Minta Revisi
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endif
                </tbody>

                @empty
                @endforelse

                @if($assignment->submissions->isEmpty())
                <tr>
                    <td colspan="5" class="px-6 py-20 text-center">
                        <div class="flex flex-col items-center gap-4">
                            <div class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center">
                                <i class="fas fa-inbox text-3xl text-gray-300"></i>
                            </div>
                            <p class="font-bold text-gray-500 text-lg">Belum ada submission</p>
                            <p class="text-sm text-gray-400">Siswa belum mengumpulkan tugas ini.</p>
                        </div>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush
