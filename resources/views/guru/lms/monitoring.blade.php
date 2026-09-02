@extends('layouts.guru')

@section('title', 'Pantauan Progres Siswa - LMS Guru: ' . ($course->course_name ?? ''))

@push('styles')
<style>
    .kpi-card {
        animation: fadeUp 0.35s ease both;
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="space-y-6">

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- 1. HERO BANNER GURU MAPEL LMS --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="relative bg-white rounded-3xl p-6 md:p-8 overflow-hidden shadow-xl border-2 border-black">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="px-3 py-1 bg-amber-400 text-black font-black text-[11px] rounded-xl border border-black uppercase tracking-wider shadow-xs">
                        <i class="fas fa-chart-line mr-1"></i> Pantauan Progres Siswa
                    </span>
                    <span class="px-3 py-1 bg-emerald-100 text-emerald-950 font-black text-[11px] rounded-xl border border-black uppercase tracking-wider">
                        {{ $course->subject?->name ?? $course->subject?->subject_name ?? 'Mata Pelajaran' }}
                    </span>
                </div>
                <h1 class="text-2xl md:text-3xl font-black text-black tracking-tight flex items-center gap-2.5">
                    {{ $course->course_name }}
                </h1>
                <p class="text-slate-600 font-bold text-xs md:text-sm mt-1 max-w-2xl">
                    Pantau capaian belajar setiap siswa pada materi, tugas, dan kuis kursus ini. Berikan apresiasi atau pengingat untuk mendorong ketuntasan belajar.
                </p>
            </div>

            {{-- Selector Kursus, Rombel & Export --}}
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                {{-- Selector Kursus --}}
                <form method="GET" action="{{ route('guru.lms.monitoring.index') }}" id="filterCourseForm" class="flex flex-wrap items-center gap-2">
                    <select name="course_id" onchange="document.getElementById('filterCourseForm').submit()" 
                            class="bg-slate-100 text-black font-black text-xs px-3 py-3 rounded-2xl border-2 border-black shadow-sm outline-none cursor-pointer max-w-[220px]">
                        @foreach($myCourses as $mc)
                            <option value="{{ $mc->id }}" {{ $course->id === $mc->id ? 'selected' : '' }}>
                                {{ $mc->course_name }}
                            </option>
                        @endforeach
                    </select>

                    @if($classrooms->count() > 1)
                    <select name="classroom_id" onchange="document.getElementById('filterCourseForm').submit()" 
                            class="bg-slate-100 text-black font-black text-xs px-3 py-3 rounded-2xl border-2 border-black shadow-sm outline-none cursor-pointer">
                        <option value="">-- Semua Kelas --</option>
                        @foreach($classrooms as $cls)
                            <option value="{{ $cls->id }}" {{ $selectedClassroomId == $cls->id ? 'selected' : '' }}>
                                {{ $cls->class_name }}
                            </option>
                        @endforeach
                    </select>
                    @endif
                </form>

                {{-- Tombol Export Excel / CSV --}}
                <a href="{{ route('guru.lms.monitoring.export', ['course_id' => $course->id]) }}" 
                   class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-2xl font-black text-xs uppercase tracking-wider transition shadow-md border-2 border-black">
                    <i class="fas fa-file-excel"></i> Ekspor Excel / CSV
                </a>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- 2. KPI SUMMARY CARDS --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Total Siswa Terdaftar --}}
        <div class="kpi-card bg-white p-5 rounded-3xl border-2 border-black shadow-md relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Siswa Mengikuti</p>
                    <h3 class="text-2xl md:text-3xl font-black text-black mt-0.5">{{ $kpi['total_enrolled_students'] }} <span class="text-sm font-extrabold text-slate-500">Siswa</span></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 border-2 border-black flex items-center justify-center text-blue-600 text-xl font-black shadow-xs shrink-0">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
            <p class="text-[11px] font-bold text-slate-600 mt-2">
                {{ $materials->count() }} Materi • {{ $assignments->count() }} Tugas • {{ $quizzes->count() }} Kuis
            </p>
        </div>

        {{-- Card 2: Rata-rata Penyelesaian Materi --}}
        <div class="kpi-card bg-white p-5 rounded-3xl border-2 border-black shadow-md relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Rata-rata Ketuntasan Materi</p>
                    <h3 class="text-2xl md:text-3xl font-black text-black mt-0.5">{{ $kpi['avg_material_completion'] }}%</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 border-2 border-black flex items-center justify-center text-amber-600 text-xl font-black shadow-xs shrink-0">
                    <i class="fas fa-book-reader"></i>
                </div>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2.5 mt-2 border border-black/20 overflow-hidden">
                <div class="h-full bg-amber-400 rounded-full" style="width: {{ $kpi['avg_material_completion'] }}%"></div>
            </div>
        </div>

        {{-- Card 3: Tugas Terkumpul & Perlu Dinilai --}}
        <div class="kpi-card bg-white p-5 rounded-3xl border-2 border-black shadow-md relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Tugas Terkumpul</p>
                    <h3 class="text-2xl md:text-3xl font-black text-indigo-700 mt-0.5">{{ $kpi['total_submissions_count'] }} <span class="text-sm font-extrabold text-slate-500">Berkas</span></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 border-2 border-black flex items-center justify-center text-indigo-600 text-xl font-black shadow-xs shrink-0">
                    <i class="fas fa-file-signature"></i>
                </div>
            </div>
            <p class="text-[11px] font-bold text-slate-600 mt-2">
                @if($kpi['pending_grading_count'] > 0)
                    <span class="text-rose-600 font-extrabold"><i class="fas fa-clock mr-1"></i>{{ $kpi['pending_grading_count'] }} menunggu dinilai</span>
                @else
                    <span class="text-emerald-700 font-extrabold"><i class="fas fa-check-circle mr-1"></i>Semua sudah dinilai</span>
                @endif
            </p>
        </div>

        {{-- Card 4: Siswa Perlu Perhatian (At-Risk) --}}
        <div class="kpi-card bg-white p-5 rounded-3xl border-2 border-black shadow-md relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Perlu Perhatian (At-Risk)</p>
                    <h3 class="text-2xl md:text-3xl font-black text-rose-600 mt-0.5">{{ $kpi['at_risk_count'] }} <span class="text-sm font-extrabold text-slate-500">Siswa</span></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-50 border-2 border-black flex items-center justify-center text-rose-600 text-xl font-black shadow-xs shrink-0">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
            </div>
            <p class="text-[11px] font-bold text-rose-600 mt-2">
                {{ $kpi['missing_task_count'] }} siswa belum kumpul tugas
            </p>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- 3. FILTER & TABEL MATRIKS PROGRES SISWA --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="bg-white rounded-3xl p-6 border-2 border-black shadow-xl space-y-5">
        
        {{-- Toolbar Filter & Search --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b-2 border-slate-100 pb-5">
            {{-- Tabs --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
                <a href="{{ route('guru.lms.monitoring.index', ['course_id' => $course->id, 'classroom_id' => $selectedClassroomId, 'filter' => 'all', 'search' => $search]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-black transition border border-black whitespace-nowrap {{ $filterTab === 'all' ? 'bg-black text-amber-400 shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Semua Siswa ({{ count($studentList) }})
                </a>
                <a href="{{ route('guru.lms.monitoring.index', ['course_id' => $course->id, 'classroom_id' => $selectedClassroomId, 'filter' => 'at_risk', 'search' => $search]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-black transition border border-black whitespace-nowrap {{ $filterTab === 'at_risk' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 text-rose-800 hover:bg-rose-100' }}">
                    ⚠️ Perlu Perhatian ({{ $kpi['at_risk_count'] }})
                </a>
                <a href="{{ route('guru.lms.monitoring.index', ['course_id' => $course->id, 'classroom_id' => $selectedClassroomId, 'filter' => 'missing_task', 'search' => $search]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-black transition border border-black whitespace-nowrap {{ $filterTab === 'missing_task' ? 'bg-amber-400 text-black shadow-sm' : 'bg-amber-50 text-amber-900 hover:bg-amber-100' }}">
                    📝 Belum Kumpul Tugas ({{ $kpi['missing_task_count'] }})
                </a>
                <a href="{{ route('guru.lms.monitoring.index', ['course_id' => $course->id, 'classroom_id' => $selectedClassroomId, 'filter' => 'completed', 'search' => $search]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-black transition border border-black whitespace-nowrap {{ $filterTab === 'completed' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-900 hover:bg-emerald-100' }}">
                    🌟 Tuntas 100%
                </a>
            </div>

            {{-- Search Bar --}}
            <form method="GET" action="{{ route('guru.lms.monitoring.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="course_id" value="{{ $course->id }}">
                <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
                <input type="hidden" name="filter" value="{{ $filterTab }}">
                <div class="relative w-full md:w-64">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama siswa / NISN..."
                           class="w-full bg-slate-50 border-2 border-black rounded-xl pl-9 pr-3 py-2 text-xs font-bold text-slate-900 outline-none focus:bg-white">
                </div>
                @if(!empty($search))
                <a href="{{ route('guru.lms.monitoring.index', ['course_id' => $course->id, 'classroom_id' => $selectedClassroomId, 'filter' => $filterTab]) }}"
                   class="px-3 py-2 bg-slate-200 hover:bg-slate-300 rounded-xl text-xs font-black text-slate-700 border border-black">✕</a>
                @endif
            </form>
        </div>

        {{-- Tabel Daftar Siswa --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b-2 border-black bg-slate-50 text-[11px] font-black text-slate-700 uppercase tracking-wider">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nama Siswa & Rombel</th>
                        <th class="py-3 px-4 min-w-[160px]">Progres Materi</th>
                        <th class="py-3 px-4 text-center">Tugas & Nilai</th>
                        <th class="py-3 px-4 text-center">Kuis & Nilai</th>
                        <th class="py-3 px-4 text-center">Total Capaian</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center w-36">Tindakan Guru</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-bold text-slate-800">
                    @forelse($filteredStudents as $item)
                    @php
                        $st = $item['student'];
                    @endphp
                    <tr class="hover:bg-amber-50/40 transition {{ $item['is_at_risk'] ? 'bg-rose-50/20' : '' }}">
                        <td class="py-3.5 px-4 text-center font-black text-slate-500">{{ $loop->iteration }}</td>
                        
                        {{-- Siswa & Kelas --}}
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $st->photo ? asset('storage/' . $st->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($st->full_name) . '&background=ea580c&color=fff' }}" 
                                     alt="{{ $st->full_name }}" 
                                     class="w-10 h-10 rounded-xl object-cover border-2 border-black shadow-xs shrink-0">
                                <div>
                                    <div class="font-black text-slate-900 text-xs hover:text-amber-600 transition cursor-pointer"
                                         onclick="openCourseStudentDetail({{ $st->id }}, {{ $course->id }})">
                                        {{ $st->full_name }}
                                    </div>
                                    <p class="text-[10px] text-slate-500 font-bold">
                                        <span class="px-1.5 py-0.2 bg-slate-100 rounded border border-slate-200 mr-1">{{ $item['class_name'] }}</span>
                                        NISN: {{ $st->nisn ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        {{-- Progres Materi --}}
                        <td class="py-3.5 px-4">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-[11px] font-black">
                                    <span>{{ $item['completed_materials'] }} / {{ $item['total_materials'] }} Selesai</span>
                                    <span class="text-slate-600">{{ $item['material_pct'] }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 border border-black/20 overflow-hidden">
                                    <div class="h-full rounded-full {{ $item['material_pct'] >= 80 ? 'bg-emerald-500' : ($item['material_pct'] < 40 ? 'bg-rose-500' : 'bg-amber-400') }}" 
                                         style="width: {{ $item['material_pct'] }}%"></div>
                                </div>
                            </div>
                        </td>

                        {{-- Tugas --}}
                        <td class="py-3.5 px-4 text-center">
                            @if($item['total_assignments'] > 0)
                                <span class="px-2 py-0.5 rounded-lg border font-black {{ $item['submitted_assignments'] < $item['total_assignments'] ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' }}">
                                    {{ $item['submitted_assignments'] }}/{{ $item['total_assignments'] }}
                                </span>
                                @if($item['avg_assignment_grade'])
                                    <p class="text-[10px] text-slate-500 font-bold mt-1">Nilai: <span class="font-black text-slate-800">{{ $item['avg_assignment_grade'] }}</span></p>
                                @endif
                            @else
                                <span class="text-slate-400 text-[10px] font-bold">Tanpa Tugas</span>
                            @endif
                        </td>

                        {{-- Kuis --}}
                        <td class="py-3.5 px-4 text-center">
                            @if($item['total_quizzes'] > 0)
                                <span class="px-2 py-0.5 bg-slate-100 rounded-lg border border-slate-300 text-slate-800 font-black">
                                    {{ $item['completed_quizzes'] }}/{{ $item['total_quizzes'] }}
                                </span>
                                @if($item['avg_quiz_score'])
                                    <p class="text-[10px] text-slate-500 font-bold mt-1">Skor: <span class="font-black text-slate-800">{{ $item['avg_quiz_score'] }}</span></p>
                                @endif
                            @else
                                <span class="text-slate-400 text-[10px] font-bold">Tanpa Kuis</span>
                            @endif
                        </td>

                        {{-- Total Capaian --}}
                        <td class="py-3.5 px-4 text-center">
                            <span class="text-xs font-black px-2.5 py-1 rounded-xl border border-black shadow-xs {{ $item['overall_pct'] >= 80 ? 'bg-emerald-100 text-emerald-950' : ($item['overall_pct'] < 40 ? 'bg-rose-100 text-rose-950' : 'bg-amber-100 text-amber-950') }}">
                                {{ $item['overall_pct'] }}%
                            </span>
                        </td>

                        {{-- Status Badge --}}
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase border border-black shadow-xs whitespace-nowrap {{ $item['status_badge'] === 'Sangat Aktif' ? 'bg-indigo-100 text-indigo-950' : ($item['status_badge'] === 'Perlu Perhatian' ? 'bg-rose-400 text-black' : 'bg-emerald-300 text-emerald-950') }}">
                                {{ $item['status_badge'] }}
                            </span>
                        </td>

                        {{-- Aksi Guru --}}
                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- Tombol Detail --}}
                                <button onclick="openCourseStudentDetail({{ $st->id }}, {{ $course->id }})" 
                                        class="p-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl border border-black shadow-xs transition"
                                        title="Lihat Detail Capaian">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>

                                {{-- Tombol Pengingat / Apresiasi --}}
                                <button onclick="openActionModal({{ $st->id }}, '{{ addslashes($st->full_name) }}', '{{ $item['phone'] }}', {{ $item['material_pct'] }}, {{ $item['submitted_assignments'] }}, {{ $item['total_assignments'] }})"
                                        class="px-2.5 py-1.5 bg-amber-400 hover:bg-amber-300 text-black rounded-xl border border-black font-black text-[11px] shadow-xs transition flex items-center gap-1">
                                    <i class="fas fa-paper-plane text-xs"></i> Aksi
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-10 text-slate-400 font-bold">
                            <i class="fas fa-user-slash text-3xl mb-2 text-slate-300"></i>
                            <p>Tidak ada data siswa yang cocok dengan filter.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- MODAL 1: DETAIL SISWA PADA KURSUS INI --}}
{{-- ═══════════════════════════════════════════════ --}}
<div id="courseStudentModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl border-2 border-black shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden animate-fadeUp">
        <div class="p-5 bg-amber-400 border-b-2 border-black flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <img id="modalCsPhoto" src="" class="w-12 h-12 rounded-2xl object-cover border-2 border-black bg-white shadow-xs">
                <div>
                    <h3 id="modalCsName" class="text-base font-black text-black"></h3>
                    <p id="modalCsSubj" class="text-xs font-bold text-slate-900"></p>
                </div>
            </div>
            <button onclick="closeCourseStudentModal()" class="w-8 h-8 rounded-xl bg-black text-white font-black hover:bg-slate-800 transition flex items-center justify-center">✕</button>
        </div>

        <div id="modalCsContent" class="p-6 overflow-y-auto space-y-4 flex-1">
            <div class="text-center py-10 text-slate-400 font-bold">
                <i class="fas fa-spinner fa-spin text-2xl mb-2 text-slate-600"></i>
                <p>Memuat rincian aktivitas siswa...</p>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- MODAL 2: FORM PENGINGAT / APRESIASI GURU --}}
{{-- ═══════════════════════════════════════════════ --}}
<div id="actionModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl border-2 border-black shadow-2xl w-full max-w-lg overflow-hidden animate-fadeUp">
        <div class="p-5 bg-black text-white border-b-2 border-black flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i class="fas fa-bullhorn text-amber-400 text-lg"></i>
                <div>
                    <h3 class="text-sm font-black uppercase tracking-wider text-amber-400">Pengingat / Apresiasi Guru</h3>
                    <p id="actStudentNameTitle" class="text-xs text-slate-300 font-bold"></p>
                </div>
            </div>
            <button onclick="closeActionModal()" class="w-8 h-8 rounded-xl bg-white/20 text-white font-black hover:bg-white/30 transition flex items-center justify-center">✕</button>
        </div>

        <form id="actionForm" onsubmit="submitAction(event)" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="student_id" id="actStudentId">
            <input type="hidden" name="course_id" value="{{ $course->id }}">

            <div>
                <label class="block text-xs font-black text-slate-700 uppercase mb-1">Tipe Pesan</label>
                <select name="type" id="actType" onchange="updateActionTemplate()" class="w-full bg-slate-50 border-2 border-black rounded-xl p-2.5 text-xs font-bold text-slate-900 outline-none">
                    <option value="tugas">📝 Pengingat Pengumpulan Tugas yang Belum Selesai</option>
                    <option value="peringatan">⚠️ Pengingat Keterlambatan Progres Materi</option>
                    <option value="apresiasi">🌟 Pujian & Apresiasi Ketuntasan Belajar</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-black text-slate-700 uppercase mb-1">Isi Pesan Guru</label>
                <textarea name="message" id="actMessage" rows="4" required class="w-full bg-slate-50 border-2 border-black rounded-xl p-3 text-xs font-bold text-slate-900 outline-none focus:bg-white resize-none"></textarea>
            </div>

            <div>
                <label class="block text-xs font-black text-slate-700 uppercase mb-1">Nomor WhatsApp Siswa / Wali</label>
                <div class="relative">
                    <i class="fab fa-whatsapp absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-600 text-base"></i>
                    <input type="text" name="target_phone" id="actTargetPhone" placeholder="Contoh: 081234567890"
                           class="w-full bg-slate-50 border-2 border-black rounded-xl pl-10 pr-3 py-2.5 text-xs font-bold text-slate-900 outline-none">
                </div>
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="button" onclick="closeActionModal()" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-black text-xs rounded-xl border border-black transition">
                    Batal
                </button>
                <button type="submit" id="btnSubmitAct" class="flex-1 py-3 bg-amber-400 hover:bg-amber-300 text-black font-black text-xs rounded-xl border-2 border-black shadow-md transition flex items-center justify-center gap-1.5">
                    <i class="fas fa-paper-plane"></i> Kirim Notifikasi
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // 1. DETAIL SISWA PADA KURSUS INI
    function openCourseStudentDetail(studentId, courseId) {
        const modal = document.getElementById('courseStudentModal');
        const container = document.getElementById('modalCsContent');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        container.innerHTML = `
            <div class="text-center py-10 text-slate-400 font-bold">
                <i class="fas fa-spinner fa-spin text-2xl mb-2 text-slate-600"></i>
                <p>Memuat aktivitas materi & tugas...</p>
            </div>
        `;

        fetch(`{{ url('guru/lms-monitoring/student') }}/${studentId}/course/${courseId}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('modalCsName').textContent = data.student.name;
                document.getElementById('modalCsSubj').textContent = `NISN: ${data.student.nisn || '-'} • ${data.course.name}`;
                document.getElementById('modalCsPhoto').src = data.student.photo || data.student.avatar;

                let html = `
                    <div class="space-y-4">
                        {{-- Daftar Materi --}}
                        <div class="bg-slate-50 border-2 border-black rounded-2xl p-4 space-y-2">
                            <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center justify-between">
                                <span><i class="fas fa-book-open text-blue-600 mr-1.5"></i>Status Modul & Materi (${data.materials.length})</span>
                            </h4>
                            <div class="space-y-1.5">
                                ${data.materials.map(m => `
                                    <div class="flex items-center justify-between p-2 bg-white rounded-xl border border-slate-200 text-xs">
                                        <span class="font-bold text-slate-800">${m.title}</span>
                                        ${m.is_completed ? '<span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded font-black text-[10px]">✓ Selesai Dibaca</span>' : '<span class="px-2 py-0.5 bg-rose-100 text-rose-800 rounded font-black text-[10px]">✕ Belum Dibaca</span>'}
                                    </div>
                                `).join('')}
                            </div>
                        </div>

                        {{-- Daftar Tugas --}}
                        <div class="bg-slate-50 border-2 border-black rounded-2xl p-4 space-y-2">
                            <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center justify-between">
                                <span><i class="fas fa-file-lines text-amber-600 mr-1.5"></i>Status Pengumpulan Tugas (${data.assignments.length})</span>
                            </h4>
                            <div class="space-y-1.5">
                                ${data.assignments.length > 0 ? data.assignments.map(a => `
                                    <div class="flex items-center justify-between p-2 bg-white rounded-xl border border-slate-200 text-xs">
                                        <div>
                                            <p class="font-bold text-slate-800">${a.title}</p>
                                            <p class="text-[10px] text-slate-400">Deadline: ${a.deadline}</p>
                                        </div>
                                        <div class="text-right">
                                            ${a.is_submitted ? `<span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded font-black text-[10px]">Terkumpul ${a.grade ? '• Nilai: ' + a.grade : ''}</span>` : '<span class="px-2 py-0.5 bg-rose-100 text-rose-800 rounded font-black text-[10px]">Belum Kumpul</span>'}
                                        </div>
                                    </div>
                                `).join('') : '<p class="text-xs text-slate-400 italic">Tidak ada tugas pada kursus ini.</p>'}
                            </div>
                        </div>

                        {{-- Daftar Kuis --}}
                        <div class="bg-slate-50 border-2 border-black rounded-2xl p-4 space-y-2">
                            <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center justify-between">
                                <span><i class="fas fa-circle-question text-purple-600 mr-1.5"></i>Status Kuis (${data.quizzes.length})</span>
                            </h4>
                            <div class="space-y-1.5">
                                ${data.quizzes.length > 0 ? data.quizzes.map(q => `
                                    <div class="flex items-center justify-between p-2 bg-white rounded-xl border border-slate-200 text-xs">
                                        <span class="font-bold text-slate-800">${q.title}</span>
                                        <div>
                                            ${q.attempts_count > 0 ? `<span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded font-black text-[10px]">${q.attempts_count}x Tes • Skor: ${q.highest_score}</span>` : '<span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded font-black text-[10px]">Belum Mengerjakan</span>'}
                                        </div>
                                    </div>
                                `).join('') : '<p class="text-xs text-slate-400 italic">Tidak ada kuis pada kursus ini.</p>'}
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML = html;
            })
            .catch(err => {
                container.innerHTML = `<div class="text-center py-10 text-rose-500 font-bold">Gagal memuat detail data siswa.</div>`;
            });
    }

    function closeCourseStudentModal() {
        const modal = document.getElementById('courseStudentModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // 2. ACTION MODAL
    let currentActData = {};
    function openActionModal(id, name, phone, matPct, sub, tot) {
        currentActData = { id, name, phone, matPct, sub, tot };
        document.getElementById('actStudentId').value = id;
        document.getElementById('actStudentNameTitle').textContent = name;
        document.getElementById('actTargetPhone').value = phone || '';

        updateActionTemplate();

        const modal = document.getElementById('actionModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeActionModal() {
        const modal = document.getElementById('actionModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function updateActionTemplate() {
        const type = document.getElementById('actType').value;
        const name = currentActData.name || 'Ananda';
        const sub = currentActData.sub || 0;
        const tot = currentActData.tot || 0;
        const matPct = currentActData.matPct || 0;

        let msg = '';
        if (type === 'tugas') {
            msg = `Halo ananda ${name}, mohon segera menyelesaikan dan mengunggah tugas LMS yang masih tertunda (${sub}/${tot} tugas terkumpul).`;
        } else if (type === 'peringatan') {
            msg = `Halo ananda ${name}, capaian materi belajarmu saat ini baru mencapai ${matPct}%. Yuk segera luangkan waktu untuk membaca dan mempelajari modul di LMS.`;
        } else {
            msg = `Selamat dan terima kasih kepada ananda ${name} yang sangat aktif dan tuntas menyelesaikan seluruh materi dan tugas LMS dengan sangat baik!`;
        }
        document.getElementById('actMessage').value = msg;
    }

    function submitAction(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitAct');
        btn.disabled = true;
        btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Memproses...`;

        const form = document.getElementById('actionForm');
        const formData = new FormData(form);

        fetch(`{{ route('guru.lms.monitoring.action') }}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = `<i class="fas fa-paper-plane"></i> Kirim Notifikasi`;
            if (data.success) {
                closeActionModal();
                if (data.wa_url) {
                    window.open(data.wa_url, '_blank');
                }
                alert('Aksi pengingat / apresiasi berhasil diproses!');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = `<i class="fas fa-paper-plane"></i> Kirim Notifikasi`;
            alert('Terjadi kesalahan saat memproses aksi.');
        });
    }
</script>
@endpush
@endsection
