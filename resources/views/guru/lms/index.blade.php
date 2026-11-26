@extends('layouts.guru')

@section('title', 'LMS - Ruang Ajar Digital Guru')

@section('content')
<style>
    .glms-wrap {
        max-width: 1320px;
        margin: 0 auto;
        padding-bottom: 2.5rem;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    .glms-hero {
        background: linear-gradient(135deg, #0f172a 0%, #064e3b 65%, #065f46 100%);
        border: 1px solid #1e293b;
        border-radius: 1.35rem;
        padding: 1.5rem 1.75rem;
        color: #ffffff;
        box-shadow: 0 12px 28px -6px rgba(15, 23, 42, 0.28);
        position: relative;
        overflow: hidden;
    }
    .glms-hero::after {
        content: '';
        position: absolute;
        top: -45px;
        right: -45px;
        width: 210px;
        height: 210px;
        border-radius: 9999px;
        background: radial-gradient(circle, rgba(251, 191, 36, 0.2) 0%, rgba(251, 191, 36, 0) 70%);
        pointer-events: none;
    }
    .glms-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1.25rem;
    }
    @media (max-width: 1180px) {
        .glms-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 700px) {
        .glms-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }
    .glms-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 1.25rem;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .glms-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.1);
    }
</style>

<div class="glms-wrap" x-data="{ search: '' }">

    {{-- 1. HERO HEADER GURU --}}
    <div class="glms-hero">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.25rem; position: relative; z-index: 1;">
            <div style="min-width: 250px; flex: 1;">
                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <span style="padding: 0.22rem 0.65rem; border-radius: 9999px; background: rgba(251, 191, 36, 0.2); border: 1px solid rgba(251, 191, 36, 0.45); color: #fde68a; font-size: 0.80rem; font-weight: 800; text-transform: uppercase;">
                        <i class="fas fa-chalkboard-teacher"></i> LMS Guru PembdaHUB
                    </span>
                    @if($activeSemester)
                    <span style="padding: 0.22rem 0.65rem; border-radius: 9999px; background: rgba(255, 255, 255, 0.14); color: #e2e8f0; font-size: 0.80rem; font-weight: 700;">
                        {{ $activeSemester->semester_name }}
                    </span>
                    @endif
                </div>
                <h1 style="font-size: 1.70rem; font-weight: 900; color: #ffffff; margin: 0; line-height: 1.25;">
                    Selamat Datang, {{ explode(' ', $teacher->user->name ?? 'Guru')[0] }}
                </h1>
                <p style="font-size: 0.90rem; color: #cbd5e1; margin-top: 0.35rem; max-width: 540px; line-height: 1.5;">
                    Kelola materi ajar interaktif, tugas, kuis, kelas tatap muka PembdaTEMU, serta asisten AI untuk penyusunan modul &amp; soal.
                </p>
            </div>

            {{-- Quick Actions --}}
            <div style="display: flex; flex-wrap: wrap; gap: 0.55rem; flex-shrink: 0;">
                <button type="button" onclick="openTemuModal()" style="padding: 0.6rem 1rem; border-radius: 0.8rem; background: #e11d48; color: #ffffff; border: none; font-size: 1.08rem; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 4px 12px rgba(225, 29, 72, 0.35);">
                    <i class="fas fa-video"></i> PembdaTEMU Live
                </button>
                <a href="{{ route('guru.lms.create') }}" style="padding: 0.6rem 1rem; border-radius: 0.8rem; background: #fbbf24; color: #0f172a; font-size: 1.08rem; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <i class="fas fa-plus-circle"></i> Buat Course
                </a>
                <a href="{{ route('guru.ai.lesson-plan') }}" style="padding: 0.6rem 0.9rem; border-radius: 0.8rem; background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.22); color: #ffffff; font-size: 1.08rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <i class="fas fa-magic" style="color: #fde047;"></i> AI RPP
                </a>
                <a href="{{ route('guru.ai.question-generator') }}" style="padding: 0.6rem 0.9rem; border-radius: 0.8rem; background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.22); color: #ffffff; font-size: 1.08rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <i class="fas fa-robot" style="color: #7dd3fc;"></i> AI Soal
                </a>
                <a href="{{ route('guru.cbt.banks.index') }}" style="padding: 0.6rem 0.9rem; border-radius: 0.8rem; background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.22); color: #ffffff; font-size: 1.08rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <i class="fas fa-database"></i> Bank Soal
                </a>
            </div>
        </div>
    </div>

    {{-- 2. FILTER ROMBEL & PENCARIAN --}}
    @if($classrooms->count() > 0)
    <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 1.15rem; padding: 1rem 1.2rem; display: flex; flex-direction: column; gap: 0.85rem; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem;">
            <div>
                <div style="font-size: 1.04rem; font-weight: 800; color: #0f172a;">Filter Rombel &amp; Pencarian Course</div>
                <div style="font-size: 0.84rem; color: #64748b;">Klik rombel untuk memfokuskan materi, tugas, dan kuis kelas tersebut</div>
            </div>
            <div style="position: relative; min-width: 220px; max-width: 300px; width: 100%;">
                <i class="fas fa-search" style="position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.98rem;"></i>
                <input type="text" x-model="search" placeholder="Cari nama course atau mapel..."
                       style="width: 100%; padding: 0.48rem 0.85rem 0.48rem 2.2rem; border-radius: 0.75rem; border: 1px solid #cbd5e1; background: #f8fafc; font-size: 0.98rem; color: #0f172a; outline: none;">
            </div>
        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 0.45rem;">
            <a href="{{ route('guru.lms.index') }}"
               style="padding: 0.45rem 0.9rem; border-radius: 9999px; font-size: 1.08rem; font-weight: 800; text-decoration: none; {{ !$selectedClassroomId ? 'background:#0f172a;color:#fbbf24;' : 'background:#f1f5f9;color:#475569;' }}">
                Semua Rombel
            </a>
            @foreach($classrooms as $classroom)
            <a href="{{ route('guru.lms.index', ['classroom_id' => $classroom->id]) }}"
               style="padding: 0.45rem 0.9rem; border-radius: 9999px; font-size: 1.08rem; font-weight: 800; text-decoration: none; {{ $selectedClassroomId == $classroom->id ? 'background:#0f172a;color:#fbbf24;' : 'background:#f1f5f9;color:#475569;' }}">
                {{ $classroom->class_name }}
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 3. COURSE TABLE (SPACIOUS & EASY TO SCAN) --}}
    @if($courses->count() > 0)
    <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 1.25rem; overflow: hidden; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; min-width: 820px;">
                <thead>
                    <tr style="background: #f1f5f9; border-bottom: 1px solid #cbd5e1; text-align: left;">
                        <th style="padding: 1rem 1.25rem; font-size: 0.82rem; font-weight: 800; color: #334155; text-transform: uppercase; letter-spacing: 0.04em;">Course &amp; Mata Pelajaran</th>
                        <th style="padding: 1rem 1rem; font-size: 0.82rem; font-weight: 800; color: #334155; text-transform: uppercase; letter-spacing: 0.04em;">Rombel / Kelas</th>
                        <th style="padding: 1rem 1rem; font-size: 0.82rem; font-weight: 800; color: #334155; text-transform: uppercase; letter-spacing: 0.04em;">Konten</th>
                        <th style="padding: 1rem 1.25rem; font-size: 0.82rem; font-weight: 800; color: #334155; text-transform: uppercase; letter-spacing: 0.04em; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($courses as $course)
                    @php
                        $headerBg = $course->getActiveThemeHexColor() ?: '#0f172a';
                        $subjectLabel = $course->isExtracurricular()
                            ? ($course->extracurricular?->name ?? 'Ekstrakurikuler')
                            : ($course->subject->subject_name ?? '-');
                        $classNames = $course->classes->pluck('classroom.class_name')->filter()->implode(', ');
                        $searchKey = strtolower($course->name . ' ' . $subjectLabel . ' ' . $classNames);
                    @endphp
                    <tr style="border-bottom: 1px solid #e2e8f0; transition: background 0.15s ease;"
                        onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#ffffff'"
                        x-show="!search.trim() || @js($searchKey).includes(search.toLowerCase().trim())">

                        {{-- Kolom 1: Course --}}
                        <td style="padding: 1.25rem 1.25rem; border-left: 5px solid {{ $headerBg }}; vertical-align: middle;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.25rem;">
                                <span style="background: {{ $headerBg }}; color: #ffffff; padding: 0.15rem 0.55rem; border-radius: 0.45rem; font-size: 0.74rem; font-weight: 800;">{{ $course->getShortCode() }}</span>
                                @if($course->is_published)
                                    <span style="background: #d1fae5; color: #065f46; padding: 0.15rem 0.55rem; border-radius: 9999px; font-size: 0.74rem; font-weight: 800;">PUBLISHED</span>
                                @else
                                    <span style="background: #f1f5f9; color: #475569; padding: 0.15rem 0.55rem; border-radius: 9999px; font-size: 0.74rem; font-weight: 800;">DRAFT</span>
                                @endif
                                @if($course->meeting_active)
                                    <span style="background: #e11d48; color: #ffffff; padding: 0.15rem 0.55rem; border-radius: 9999px; font-size: 0.72rem; font-weight: 800;">LIVE</span>
                                @endif
                            </div>
                            <div style="font-size: 1.04rem; font-weight: 800; color: #0f172a; line-height: 1.3;">{{ $course->name }}</div>
                            <div style="font-size: 0.86rem; color: #475569; font-weight: 600; margin-top: 0.15rem;">{{ $subjectLabel }}</div>
                        </td>

                        {{-- Kolom 2: Rombel --}}
                        <td style="padding: 1.25rem 1rem; vertical-align: middle;">
                            @if($classNames)
                                <span style="display: inline-block; padding: 0.35rem 0.7rem; border-radius: 0.6rem; background: #fffbeb; border: 1px solid #fde68a; font-size: 0.84rem; font-weight: 700; color: #92400e;">
                                    <i class="fas fa-users" style="margin-right: 0.25rem;"></i> {{ $classNames }}
                                </span>
                            @else
                                <span style="font-size: 0.84rem; color: #94a3b8;">Belum ditautkan</span>
                            @endif
                        </td>

                        {{-- Kolom 3: Konten --}}
                        <td style="padding: 1.25rem 1rem; vertical-align: middle;">
                            <div style="display: flex; flex-direction: column; gap: 0.25rem; font-size: 0.86rem; font-weight: 700; color: #334155;">
                                <span><i class="fas fa-file-alt" style="color: #0284c7; width: 16px;"></i> {{ $course->materials_count }} Materi</span>
                                <span><i class="fas fa-tasks" style="color: #d97706; width: 16px;"></i> {{ $course->assignments_count }} Tugas</span>
                                <span><i class="fas fa-question-circle" style="color: #7c3aed; width: 16px;"></i> {{ $course->quizzes_count }} Quiz</span>
                            </div>
                        </td>

                        {{-- Kolom 4: Aksi --}}
                        <td style="padding: 1.25rem 1.25rem; vertical-align: middle; text-align: right;">
                            <div style="display: inline-flex; align-items: center; gap: 0.45rem; flex-wrap: wrap; justify-content: flex-end;">
                                @if($course->meeting_active)
                                    <a href="{{ route('guru.lms.meeting.join', $course->id) }}" style="padding: 0.55rem 0.85rem; border-radius: 0.75rem; background: #e11d48; color: #ffffff; font-size: 0.84rem; font-weight: 800; text-decoration: none; white-space: nowrap;">
                                        <i class="fas fa-video"></i> Masuk Live
                                    </a>
                                @else
                                    <form action="{{ route('guru.lms.meeting.start', $course->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Mulai sesi tatap muka PembdaTEMU untuk kelas \'{{ addslashes($course->name) }}\'?');">
                                        @csrf
                                        <button type="submit" style="padding: 0.55rem 0.85rem; border-radius: 0.75rem; background: #fff1f2; border: 1px solid #fecdd3; color: #be123c; font-size: 0.84rem; font-weight: 800; cursor: pointer; white-space: nowrap;">
                                            <i class="fas fa-video"></i> Temu
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('guru.lms.show', $course->id) }}{{ $selectedClassroomId ? '?classroom_id='.$selectedClassroomId : '' }}"
                                   style="padding: 0.55rem 0.95rem; border-radius: 0.75rem; background: #059669; color: #ffffff; font-size: 0.85rem; font-weight: 800; text-decoration: none; white-space: nowrap;">
                                    <i class="fas fa-door-open"></i> Ruang Ajar
                                </a>

                                <a href="{{ route('guru.lms.edit', $course->id) }}" style="padding: 0.55rem 0.75rem; border-radius: 0.75rem; background: #f8fafc; border: 1px solid #cbd5e1; color: #0f172a; font-size: 0.84rem; font-weight: 700; text-decoration: none;" title="Edit Course">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ route('guru.lms.destroy', $course->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus course \'{{ addslashes($course->name) }}\'?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="padding: 0.55rem 0.75rem; border-radius: 0.75rem; background: #fff1f2; border: 1px solid #fecdd3; color: #be123c; font-size: 0.84rem; font-weight: 700; cursor: pointer;" title="Hapus Course">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $courses->links() }}</div>
    @else
    <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 1.25rem; padding: 3.5rem 1.5rem; text-align: center;">
        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📖</div>
        <h3 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0;">Belum Ada Course di Unit / Rombel Ini</h3>
        <p style="font-size: 0.90rem; color: #64748b; margin-top: 0.3rem; margin-bottom: 1rem;">Mulai buat course baru untuk mengunggah materi ajar dan tugas siswa.</p>
        <a href="{{ route('guru.lms.create') }}" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.65rem 1.25rem; border-radius: 0.8rem; background: #0f172a; color: #fbbf24; font-size: 0.98rem; font-weight: 800; text-decoration: none;">
            <i class="fas fa-plus-circle"></i> Buat Course Baru
        </a>
    </div>
    @endif

    {{-- ORPHAN COURSES --}}
    @if($orphanCourses->count() > 0)
    <div style="background: #fffbeb; border: 1px solid #fcd34d; border-radius: 1.15rem; padding: 1.1rem 1.25rem;">
        <div style="font-size: 0.84rem; font-weight: 800; color: #92400e; margin-bottom: 0.65rem;">
            <i class="fas fa-exclamation-triangle"></i> Course Belum Ditautkan ke Rombel ({{ $orphanCourses->count() }})
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 0.65rem;">
            @foreach($orphanCourses as $course)
            <div style="background: #ffffff; border: 1px solid #fde68a; border-radius: 0.85rem; padding: 0.75rem 0.9rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                <div style="min-width: 0;">
                    <div style="font-size: 0.90rem; font-weight: 800; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $course->name }}</div>
                    <div style="font-size: 1.04rem; color: #b45309;">{{ $course->subject->subject_name ?? 'Tanpa Mapel' }}</div>
                </div>
                <a href="{{ route('guru.lms.edit', $course->id) }}" style="padding: 0.4rem 0.75rem; border-radius: 0.65rem; background: #0f172a; color: #fbbf24; font-size: 1.04rem; font-weight: 800; text-decoration: none; white-space: nowrap;">
                    Tautkan
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- MODAL PEMBDATEMU DESKTOP --}}
    <div id="modal-pembdatemu" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 hidden items-center justify-center p-4">
        <div class="bg-white w-full max-w-xl rounded-2xl p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center text-lg">
                        <i class="fas fa-video"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">PembdaTEMU - Tatap Muka KBM</h3>
                        <p class="text-xs text-slate-500">Pilih mata pelajaran untuk membuka video conference KBM virtual</p>
                    </div>
                </div>
                <button type="button" onclick="closeTemuModal()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-rose-100 hover:text-rose-700 text-slate-600 flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-2.5 max-h-96 overflow-y-auto pr-1">
                @forelse($courses as $c)
                    <div class="p-3.5 rounded-xl border {{ $c->meeting_active ? 'border-rose-300 bg-rose-50/50' : 'border-slate-200 bg-white' }} flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-slate-900 text-sm truncate">{{ $c->name }}</h4>
                                @if($c->meeting_active)
                                    <span class="px-2 py-0.5 rounded-full bg-rose-600 text-white text-[9px] font-extrabold uppercase">LIVE</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 truncate">
                                {{ $c->subject->name ?? ($c->subject->subject_name ?? 'Mapel') }} &bull; {{ $c->classroom->class_name ?? ($c->classes->pluck('classroom.class_name')->implode(', ') ?: 'Semua Rombel') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if($c->meeting_active)
                                <a href="{{ route('guru.lms.meeting.join', $c->id) }}" class="bg-rose-600 hover:bg-rose-700 text-white px-3.5 py-2 rounded-lg text-xs font-bold">
                                    Masuk Temu
                                </a>
                                <form action="{{ route('guru.lms.meeting.stop', $c->id) }}" method="POST" onsubmit="return confirm('Akhiri sesi PembdaTEMU?');">
                                    @csrf
                                    <button type="submit" class="bg-slate-800 hover:bg-rose-600 text-white px-2.5 py-2 rounded-lg text-xs font-bold" title="Akhiri Sesi">
                                        <i class="fas fa-stop"></i>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('guru.lms.meeting.start', $c->id) }}" method="POST" onsubmit="return confirm('Mulai sesi PembdaTEMU untuk kelas \'{{ addslashes($c->name) }}\'?');">
                                    @csrf
                                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-3.5 py-2 rounded-lg text-xs font-bold">
                                        Mulai Temu
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 text-xs">Belum ada course aktif.</div>
                @endforelse
            </div>

            <div class="pt-2 border-t border-slate-200">
                <button type="button" onclick="closeTemuModal()" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openTemuModal() {
        const modal = document.getElementById('modal-pembdatemu');
        if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
    }
    function closeTemuModal() {
        const modal = document.getElementById('modal-pembdatemu');
        if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
    }
</script>
@endpush
@endsection
