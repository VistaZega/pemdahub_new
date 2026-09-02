@extends('layouts.guru')

@section('title', 'Pantauan Progres LMS Kelas ' . ($classroom->class_name ?? '') . ' - Wali Kelas')

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
    {{-- 1. HERO BANNER WALI KELAS --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="relative bg-white rounded-3xl p-6 md:p-8 overflow-hidden shadow-xl border-2 border-black">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="px-3 py-1 bg-amber-400 text-black font-black text-[11px] rounded-xl border border-black uppercase tracking-wider shadow-xs">
                        <i class="fas fa-user-shield mr-1"></i> Dashboard Wali Kelas
                    </span>
                    <span class="px-3 py-1 bg-blue-100 text-blue-950 font-black text-[11px] rounded-xl border border-black uppercase tracking-wider">
                        TP. {{ $activeYear->year_name ?? date('Y') }}
                    </span>
                </div>
                <h1 class="text-2xl md:text-3xl font-black text-black tracking-tight flex items-center gap-2.5">
                    Pantauan Progres LMS: <span class="underline decoration-amber-400 decoration-4">{{ $classroom->class_name }}</span>
                </h1>
                <p class="text-slate-600 font-bold text-xs md:text-sm mt-1 max-w-2xl">
                    Pantau capaian belajar seluruh siswa rombel Anda di seluruh mata pelajaran LMS, temukan siswa yang memerlukan bimbingan, dan berikan tindakan motivasi secara proaktif.
                </p>
            </div>

            {{-- Selector Rombel & Cetak --}}
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                @if($homeroomClassrooms->count() > 1)
                <div class="relative">
                    <form method="GET" action="{{ route('guru.walikelas.lms-monitoring') }}" id="classSelectorForm">
                        <select name="classroom_id" onchange="document.getElementById('classSelectorForm').submit()" 
                                class="bg-slate-100 text-black font-black text-xs px-4 py-3 rounded-2xl border-2 border-black shadow-sm outline-none cursor-pointer pr-9">
                            @foreach($homeroomClassrooms as $hc)
                                <option value="{{ $hc->id }}" {{ $classroom->id === $hc->id ? 'selected' : '' }}>
                                    Kelas {{ $hc->class_name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
                @endif

                <a href="{{ route('guru.walikelas.lms-monitoring.print', ['classroom_id' => $classroom->id]) }}" target="_blank"
                   class="inline-flex items-center gap-2 bg-black hover:bg-slate-800 text-white px-5 py-3 rounded-2xl font-black text-xs uppercase tracking-wider transition shadow-md border-2 border-black">
                    <i class="fas fa-print text-amber-400"></i> Cetak Rekap Rombel
                </a>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- 2. KPI SUMMARY CARDS --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Total Siswa & Mapel --}}
        <div class="kpi-card bg-white p-5 rounded-3xl border-2 border-black shadow-md relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Total Siswa / Mapel</p>
                    <h3 class="text-2xl md:text-3xl font-black text-black mt-0.5">{{ $kpi['total_students'] }} <span class="text-sm font-extrabold text-slate-500">/ {{ $kpi['total_courses'] }} Mapel</span></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 border-2 border-black flex items-center justify-center text-blue-600 text-xl font-black shadow-xs shrink-0">
                    <i class="fas fa-users-rectangle"></i>
                </div>
            </div>
            <p class="text-[11px] font-bold text-slate-600 mt-2">
                {{ $kpi['total_materials'] }} Modul, {{ $kpi['total_assignments'] }} Tugas, {{ $kpi['total_quizzes'] }} Kuis
            </p>
        </div>

        {{-- Card 2: Rata-rata Progres Kelas --}}
        <div class="kpi-card bg-white p-5 rounded-3xl border-2 border-black shadow-md relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Rata-rata Progres Rombel</p>
                    <h3 class="text-2xl md:text-3xl font-black text-black mt-0.5">{{ $kpi['avg_class_progress'] }}%</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 border-2 border-black flex items-center justify-center text-amber-600 text-xl font-black shadow-xs shrink-0">
                    <i class="fas fa-chart-pie"></i>
                </div>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2.5 mt-2 border border-black/20 overflow-hidden">
                <div class="h-full bg-amber-400 rounded-full" style="width: {{ $kpi['avg_class_progress'] }}%"></div>
            </div>
        </div>

        {{-- Card 3: Siswa Unggul / On-Track --}}
        <div class="kpi-card bg-white p-5 rounded-3xl border-2 border-black shadow-md relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Sangat Aktif / Unggul</p>
                    <h3 class="text-2xl md:text-3xl font-black text-emerald-700 mt-0.5">{{ $kpi['excellent_count'] }} <span class="text-sm font-extrabold text-slate-500">Siswa</span></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 border-2 border-black flex items-center justify-center text-emerald-600 text-xl font-black shadow-xs shrink-0">
                    <i class="fas fa-award"></i>
                </div>
            </div>
            <p class="text-[11px] font-bold text-emerald-700 mt-2">
                <i class="fas fa-arrow-up mr-1"></i> Progres belajar di atas 85%
            </p>
        </div>

        {{-- Card 4: Siswa Perlu Bimbingan (At-Risk) --}}
        <div class="kpi-card bg-white p-5 rounded-3xl border-2 border-black shadow-md relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-wider">Perlu Bimbingan (At-Risk)</p>
                    <h3 class="text-2xl md:text-3xl font-black text-rose-600 mt-0.5">{{ $kpi['at_risk_count'] }} <span class="text-sm font-extrabold text-slate-500">Siswa</span></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-50 border-2 border-black flex items-center justify-center text-rose-600 text-xl font-black shadow-xs shrink-0">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
            </div>
            <p class="text-[11px] font-bold text-rose-600 mt-2">
                Progres < 40% / tugas menumpuk
            </p>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- 3. FILTER & TABEL MATRIKS PROGRES SISWA --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="bg-white rounded-3xl p-6 border-2 border-black shadow-xl space-y-5">
        
        {{-- Toolbar Filter & Search --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b-2 border-slate-100 pb-5">
            {{-- Status Filter Tabs --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
                <a href="{{ route('guru.walikelas.lms-monitoring', ['classroom_id' => $classroom->id, 'status' => 'all', 'search' => $search]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-black transition border border-black whitespace-nowrap {{ $filterStatus === 'all' ? 'bg-black text-amber-400 shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Semua ({{ count($studentProgressList) }})
                </a>
                <a href="{{ route('guru.walikelas.lms-monitoring', ['classroom_id' => $classroom->id, 'status' => 'at_risk', 'search' => $search]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-black transition border border-black whitespace-nowrap {{ $filterStatus === 'at_risk' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 text-rose-800 hover:bg-rose-100' }}">
                    ⚠️ Perlu Bimbingan ({{ $kpi['at_risk_count'] }})
                </a>
                <a href="{{ route('guru.walikelas.lms-monitoring', ['classroom_id' => $classroom->id, 'status' => 'excellent', 'search' => $search]) }}"
                   class="px-4 py-2 rounded-xl text-xs font-black transition border border-black whitespace-nowrap {{ $filterStatus === 'excellent' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-indigo-50 text-indigo-800 hover:bg-indigo-100' }}">
                    🌟 Sangat Aktif ({{ $kpi['excellent_count'] }})
                </a>
            </div>

            {{-- Search Bar --}}
            <form method="GET" action="{{ route('guru.walikelas.lms-monitoring') }}" class="flex items-center gap-2">
                <input type="hidden" name="classroom_id" value="{{ $classroom->id }}">
                <input type="hidden" name="status" value="{{ $filterStatus }}">
                <div class="relative w-full md:w-64 flex items-center">
                    <i class="fas fa-search absolute left-4 text-slate-400 text-xs pointer-events-none"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama siswa / NISN..."
                           class="w-full bg-slate-50 border-2 border-black rounded-2xl pl-11 pr-4 py-2.5 text-xs font-bold text-slate-900 outline-none focus:bg-white transition shadow-2xs">
                </div>
                @if(!empty($search))
                <a href="{{ route('guru.walikelas.lms-monitoring', ['classroom_id' => $classroom->id, 'status' => $filterStatus]) }}"
                   class="px-3 py-2 bg-slate-200 hover:bg-slate-300 rounded-xl text-xs font-black text-slate-700 border border-black">✕</a>
                @endif
            </form>
        </div>

        {{-- Tabel Matriks Siswa --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b-2 border-black bg-slate-50 text-[11px] font-black text-slate-700 uppercase tracking-wider">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nama Siswa & NISN</th>
                        <th class="py-3 px-4 min-w-[180px]">Progres Seluruh Mapel</th>
                        <th class="py-3 px-4 text-center">Materi Dibaca</th>
                        <th class="py-3 px-4 text-center">Tugas Kumpul</th>
                        <th class="py-3 px-4 text-center">Kuis Selesai</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center w-36">Tindakan Wali Kelas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-bold text-slate-800">
                    @forelse($filteredList as $item)
                    @php
                        $st = $item['student'];
                    @endphp
                    <tr class="hover:bg-amber-50/40 transition {{ $item['is_at_risk'] ? 'bg-rose-50/20' : '' }}">
                        <td class="py-3.5 px-4 text-center font-black text-slate-500">{{ $loop->iteration }}</td>
                        
                        {{-- Nama Siswa & Foto --}}
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $st->photo ? asset('storage/' . $st->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($st->full_name) . '&background=4f46e5&color=fff' }}" 
                                     alt="{{ $st->full_name }}" 
                                     class="w-10 h-10 rounded-xl object-cover border-2 border-black shadow-xs shrink-0">
                                <div>
                                    <div class="font-black text-slate-900 text-xs hover:text-indigo-600 transition cursor-pointer"
                                         onclick="openStudentDetail({{ $st->id }})">
                                        {{ $st->full_name }}
                                    </div>
                                    <p class="text-[10px] text-slate-500 font-bold">
                                        NISN: {{ $st->nisn ?? '-' }} 
                                        @if($item['phone'])
                                            <span class="text-emerald-600 ml-1.5"><i class="fab fa-whatsapp"></i> {{ $item['phone'] }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </td>

                        {{-- Progres Seluruh Mapel --}}
                        <td class="py-3.5 px-4">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-[11px] font-black">
                                    <span class="text-slate-700">{{ $item['overall_progress'] }}%</span>
                                    <span class="text-[10px] text-slate-500 font-extrabold">{{ count($item['courses']) }} Mapel</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2.5 border border-black/20 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500 {{ $item['overall_progress'] >= 80 ? 'bg-emerald-500' : ($item['overall_progress'] < 40 ? 'bg-rose-500' : 'bg-amber-400') }}" 
                                         style="width: {{ $item['overall_progress'] }}%"></div>
                                </div>
                            </div>
                        </td>

                        {{-- Materi --}}
                        <td class="py-3.5 px-4 text-center font-black">
                            <span class="px-2 py-0.5 bg-slate-100 rounded-lg border border-slate-300 text-slate-800">
                                {{ $item['completed_materials'] }} / {{ $kpi['total_materials'] }}
                            </span>
                        </td>

                        {{-- Tugas --}}
                        <td class="py-3.5 px-4 text-center font-black">
                            <span class="px-2 py-0.5 rounded-lg border {{ $item['submitted_tasks'] < $kpi['total_assignments'] ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' }}">
                                {{ $item['submitted_tasks'] }} / {{ $kpi['total_assignments'] }}
                            </span>
                        </td>

                        {{-- Kuis --}}
                        <td class="py-3.5 px-4 text-center font-black">
                            <span class="px-2 py-0.5 bg-slate-100 rounded-lg border border-slate-300 text-slate-800">
                                {{ $item['completed_quizzes'] }} / {{ $kpi['total_quizzes'] }}
                            </span>
                        </td>

                        {{-- Status Badge --}}
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase border border-black shadow-xs whitespace-nowrap {{ $item['status'] === 'excellent' ? 'bg-indigo-100 text-indigo-950' : ($item['status'] === 'at_risk' ? 'bg-rose-400 text-black' : 'bg-emerald-300 text-emerald-950') }}">
                                {{ $item['status_label'] }}
                            </span>
                        </td>

                        {{-- Tombol Aksi Wali Kelas --}}
                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                {{-- Tombol Detail Breakdown Mapel --}}
                                <button onclick="openStudentDetail({{ $st->id }})" 
                                        class="p-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl border border-black shadow-xs transition"
                                        title="Lihat Rincian Seluruh Mapel">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>

                                {{-- Tombol Motivasi / Tindakan Pembinaan --}}
                                <button onclick="openMotivationModal({{ $st->id }}, '{{ addslashes($st->full_name) }}', '{{ $item['phone'] }}', {{ $item['overall_progress'] }}, {{ $item['submitted_tasks'] }}, {{ $kpi['total_assignments'] }})"
                                        class="px-2.5 py-1.5 bg-amber-400 hover:bg-amber-300 text-black rounded-xl border border-black font-black text-[11px] shadow-xs transition flex items-center gap-1">
                                    <i class="fas fa-comment-dots text-xs"></i> Motivasi
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

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- 4. RIWAYAT CATATAN MOTIVASI & PEMBINAAN --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @if(isset($recentMotivations) && $recentMotivations->count() > 0)
    <div class="bg-white rounded-3xl p-6 border-2 border-black shadow-xl space-y-3">
        <h3 class="text-sm font-black text-black uppercase tracking-wider flex items-center gap-2">
            <i class="fas fa-history text-amber-500"></i> Riwayat Pembinaan & Catatan Motivasi Terakhir
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($recentMotivations as $rm)
            <div class="p-4 bg-slate-50 border-2 border-black rounded-2xl shadow-xs space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black text-slate-900">{{ $rm->student->full_name ?? '-' }}</span>
                    <span class="text-[10px] font-bold text-slate-500">{{ $rm->incident_date ? $rm->incident_date->format('d M Y') : '' }}</span>
                </div>
                <p class="text-xs text-slate-700 italic bg-white p-2.5 rounded-xl border border-slate-200">
                    "{{ $rm->description }}"
                </p>
                <div class="flex items-center justify-between text-[10px] font-bold text-slate-500">
                    <span class="text-indigo-600 font-extrabold">{{ $rm->title }}</span>
                    @if($rm->parent_notified)
                        <span class="text-emerald-600"><i class="fab fa-whatsapp mr-1"></i>Terkirim ke Ortu</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- MODAL 1: RINCIAN PROGRES PER MAPEL SISWA --}}
{{-- ═══════════════════════════════════════════════ --}}
<div id="studentDetailModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl border-2 border-black shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden animate-fadeUp">
        {{-- Modal Header --}}
        <div class="p-5 bg-amber-400 border-b-2 border-black flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <img id="modalStudentPhoto" src="https://ui-avatars.com/api/?name=Siswa&background=4f46e5&color=fff" class="w-12 h-12 rounded-2xl object-cover border-2 border-black bg-white shadow-xs">
                <div>
                    <h3 id="modalStudentName" class="text-base font-black text-black">Memuat data siswa...</h3>
                    <p id="modalStudentNisn" class="text-xs font-bold text-slate-900"></p>
                </div>
            </div>
            <button onclick="closeStudentDetail()" class="w-8 h-8 rounded-xl bg-black text-white font-black hover:bg-slate-800 transition flex items-center justify-center">✕</button>
        </div>

        {{-- Modal Body --}}
        <div id="modalCoursesList" class="p-6 overflow-y-auto space-y-4 flex-1">
            <div class="text-center py-10 text-slate-400 font-bold">
                <i class="fas fa-spinner fa-spin text-2xl mb-2 text-slate-600"></i>
                <p>Memuat rincian mata pelajaran siswa...</p>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════ --}}
{{-- MODAL 2: FORM KIRIM MOTIVASI & BIMBINGAN WALI KELAS --}}
{{-- ═══════════════════════════════════════════════ --}}
<div id="motivationModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl border-2 border-black shadow-2xl w-full max-w-lg overflow-hidden animate-fadeUp">
        <div class="p-5 bg-black text-white border-b-2 border-black flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i class="fas fa-paper-plane text-amber-400 text-lg"></i>
                <div>
                    <h3 class="text-sm font-black uppercase tracking-wider text-amber-400">Aksi & Bimbingan Wali Kelas</h3>
                    <p id="motStudentNameTitle" class="text-xs text-slate-300 font-bold"></p>
                </div>
            </div>
            <button onclick="closeMotivationModal()" class="w-8 h-8 rounded-xl bg-white/20 text-white font-black hover:bg-white/30 transition flex items-center justify-center">✕</button>
        </div>

        <form id="motivationForm" onsubmit="submitMotivation(event)" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="student_id" id="motStudentId">

            <div>
                <label class="block text-xs font-black text-slate-700 uppercase mb-1">Jenis Tindakan / Pesan</label>
                <select name="message_type" id="motMessageType" onchange="updateTemplateNote()" class="w-full bg-slate-50 border-2 border-black rounded-xl p-2.5 text-xs font-bold text-slate-900 outline-none">
                    <option value="motivasi">📢 Dorongan & Motivasi Belajar (Umum)</option>
                    <option value="peringatan">⚠️ Peringatan Keterlambatan Tugas / Kurang Aktif</option>
                    <option value="apresiasi">🌟 Pujian & Apresiasi Prestasi Sangat Baik</option>
                    <option value="evaluasi">📋 Catatan Evaluasi & Pembinaan Mandiri</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-black text-slate-700 uppercase mb-1">Isi Catatan / Pesan Pembinaan</label>
                <textarea name="note" id="motNote" rows="4" required class="w-full bg-slate-50 border-2 border-black rounded-xl p-3 text-xs font-bold text-slate-900 outline-none focus:bg-white resize-none" placeholder="Tuliskan pesan motivasi untuk siswa/wali murid..."></textarea>
            </div>

            <div>
                <label class="block text-xs font-black text-slate-700 uppercase mb-1">Nomor WhatsApp Tujuan (Orang Tua / Siswa)</label>
                <div class="relative flex items-center">
                    <i class="fab fa-whatsapp absolute left-4 text-emerald-600 text-base pointer-events-none"></i>
                    <input type="text" name="target_phone" id="motTargetPhone" placeholder="Contoh: 081234567890 (Kosongkan jika hanya rekam sistem)"
                           class="w-full bg-slate-50 border-2 border-black rounded-2xl pl-11 pr-4 py-3 text-xs font-bold text-slate-900 outline-none focus:bg-white transition shadow-2xs">
                </div>
                <p class="text-[10px] text-slate-500 font-bold mt-1">
                    *Jika nomor diisi, sistem akan otomatis membuka chat WhatsApp dengan template pesan rapi.
                </p>
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="button" onclick="closeMotivationModal()" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-black text-xs rounded-xl border border-black transition">
                    Batal
                </button>
                <button type="submit" id="btnSubmitMot" class="flex-1 py-3 bg-amber-400 hover:bg-amber-300 text-black font-black text-xs rounded-xl border-2 border-black shadow-md transition flex items-center justify-center gap-1.5">
                    <i class="fas fa-save"></i> Simpan & Kirim
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // 1. OPEN DETAIL MODAL PER SISWA
    function openStudentDetail(studentId) {
        const modal = document.getElementById('studentDetailModal');
        const container = document.getElementById('modalCoursesList');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        document.getElementById('modalStudentPhoto').src = 'https://ui-avatars.com/api/?name=Siswa&background=4f46e5&color=fff';
        document.getElementById('modalStudentName').textContent = 'Memuat data siswa...';
        document.getElementById('modalStudentNisn').textContent = '';

        container.innerHTML = `
            <div class="text-center py-10 text-slate-400 font-bold">
                <i class="fas fa-spinner fa-spin text-2xl mb-2 text-slate-600"></i>
                <p>Memuat data progres seluruh mapel...</p>
            </div>
        `;

        const detailUrl = `{{ route('guru.walikelas.lms-monitoring.student-detail', ['student' => ':student']) }}`
            .replace(':student', studentId);

        fetch(detailUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(errData => {
                    throw new Error(errData.error || ('HTTP ' + res.status));
                }).catch(() => {
                    throw new Error('HTTP ' + res.status);
                });
            }
            return res.json();
        })
        .then(data => {
            document.getElementById('modalStudentName').textContent = data.student.name;
            document.getElementById('modalStudentNisn').textContent = `NISN: ${data.student.nisn || '-'} • Rombel: ${data.student.classroom || '-'}`;
            document.getElementById('modalStudentPhoto').src = data.student.photo || data.student.avatar;

            if (data.courses.length === 0) {
                container.innerHTML = `<div class="text-center py-10 text-slate-400 font-bold">Belum ada mata pelajaran LMS yang terhubung ke rombel ini.</div>`;
                return;
            }

            let html = `<div class="grid grid-cols-1 md:grid-cols-2 gap-4">`;
            data.courses.forEach(c => {
                const badgeColor = c.overall_pct >= 80 ? 'bg-emerald-100 text-emerald-950 border-emerald-300' : (c.overall_pct < 40 ? 'bg-rose-100 text-rose-950 border-rose-300' : 'bg-amber-100 text-amber-950 border-amber-300');
                html += `
                    <div class="p-4 bg-slate-50 border-2 border-black rounded-2xl space-y-3 shadow-xs">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h4 class="text-xs font-black text-slate-900">${c.name}</h4>
                                <p class="text-[10px] text-slate-500 font-bold"><i class="fas fa-user-tie mr-1"></i>${c.teacher}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-black border ${badgeColor}">
                                ${c.overall_pct}%
                            </span>
                        </div>

                        <div class="w-full bg-slate-200 rounded-full h-2 border border-black/10 overflow-hidden">
                            <div class="h-full rounded-full bg-amber-400" style="width: ${c.overall_pct}%"></div>
                        </div>

                        <div class="grid grid-cols-3 gap-2 text-center text-[10px] font-bold">
                            <div class="bg-white p-1.5 rounded-xl border border-slate-200">
                                <p class="text-slate-400">Materi</p>
                                <p class="font-black text-slate-800">${c.materials_completed}/${c.materials_total}</p>
                            </div>
                            <div class="bg-white p-1.5 rounded-xl border border-slate-200">
                                <p class="text-slate-400">Tugas</p>
                                <p class="font-black text-slate-800">${c.assignments_submitted}/${c.assignments_total}</p>
                            </div>
                            <div class="bg-white p-1.5 rounded-xl border border-slate-200">
                                <p class="text-slate-400">Kuis</p>
                                <p class="font-black text-slate-800">${c.quizzes_completed}/${c.quizzes_total}</p>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += `</div>`;
            container.innerHTML = html;
        })
        .catch(err => {
            container.innerHTML = `<div class="text-center py-10 text-rose-500 font-bold"><i class="fas fa-exclamation-triangle text-2xl mb-2 text-rose-500 block"></i>Gagal memuat data detail siswa (${err.message || 'Terjadi kesalahan sistem'}).</div>`;
        });
    }

    function closeStudentDetail() {
        const modal = document.getElementById('studentDetailModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // 2. OPEN MOTIVATION MODAL
    let currentStudentData = {};
    function openMotivationModal(id, name, phone, progress, submittedTasks, totalTasks) {
        currentStudentData = { id, name, phone, progress, submittedTasks, totalTasks };
        document.getElementById('motStudentId').value = id;
        document.getElementById('motStudentNameTitle').textContent = name;
        document.getElementById('motTargetPhone').value = phone || '';
        
        updateTemplateNote();

        const modal = document.getElementById('motivationModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeMotivationModal() {
        const modal = document.getElementById('motivationModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function updateTemplateNote() {
        const type = document.getElementById('motMessageType').value;
        const name = currentStudentData.name || 'Ananda';
        const progress = currentStudentData.progress || 0;
        const sub = currentStudentData.submittedTasks || 0;
        const tot = currentStudentData.totalTasks || 0;

        let template = '';
        if (type === 'motivasi') {
            template = `Ananda ${name} sudah mencapai progres belajar ${progress}% di LMS. Mari terus tingkatkan semangat dan tuntaskan sisa materi pembelajaran.`;
        } else if (type === 'peringatan') {
            template = `Mohon perhatian, ananda ${name} baru menyelesaikan ${sub} dari total ${tot} tugas LMS (Progres ${progress}%). Diharapkan segera melengkapi tugas yang tertunda.`;
        } else if (type === 'apresiasi') {
            template = `Selamat kepada ananda ${name} atas keaktifan dan ketekunan belajar di LMS dengan capaian optimal ${progress}%. Pertahankan prestasimu!`;
        } else {
            template = `Catatan evaluasi belajar ananda ${name} pada pertengahan semester: Progres LMS ${progress}%.`;
        }
        document.getElementById('motNote').value = template;
    }

    // 3. SUBMIT MOTIVATION
    function submitMotivation(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitMot');
        btn.disabled = true;
        btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Menyimpan...`;

        const form = document.getElementById('motivationForm');
        const formData = new FormData(form);

        fetch(`{{ route('guru.walikelas.lms-monitoring.motivation') }}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = `<i class="fas fa-save"></i> Simpan & Kirim`;
            if (data.success) {
                closeMotivationModal();
                if (data.wa_url) {
                    window.open(data.wa_url, '_blank');
                }
                alert('Catatan motivasi berhasil dicatat dan diproses!');
                location.reload();
            } else {
                alert('Gagal menyimpan catatan.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = `<i class="fas fa-save"></i> Simpan & Kirim`;
            alert('Terjadi kesalahan sistem.');
        });
    }
</script>
@endpush
@endsection
