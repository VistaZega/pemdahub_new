@extends('layouts.admin')
@section('title', 'Detail Ujian CBT')
@section('content')
<div class="space-y-8">
    {{-- Hero Header --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-violet-600 via-purple-600 to-indigo-700 rounded-2xl p-8 text-white">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div class="flex items-center gap-5">
                <a href="{{ route('admin.cbt.index') }}" class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center hover:bg-white/25 transition border border-gray-200">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ $exam->exam_title }}</h1>
                    <p class="text-violet-50 mt-1 text-base">
                        {{ $exam->subject->subject_name ?? $exam->subject->name ?? '-' }} — {{ $exam->teacher?->full_name ?? 'Ujian Sekolah' }}
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                @if($hasEssayQuestions ?? false)
                <a href="{{ route('admin.cbt.grade-essays', $exam) }}" class="inline-flex items-center px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-semibold transition shadow-lg shadow-amber-900/20 text-sm">
                    <i class="fas fa-pen-nib mr-2"></i>Koreksi Esai
                    @if(($pendingEssaysCount ?? 0) > 0)
                    <span class="ml-2 px-2 py-0.5 text-xs bg-white text-amber-700 font-bold rounded-full">{{ $pendingEssaysCount }}</span>
                    @endif
                </a>
                @endif
                @if(in_array($exam->status, ['draft', 'published', 'active']))
                <a href="{{ route('admin.cbt.exams.edit', $exam) }}" class="inline-flex items-center px-5 py-2.5 bg-white text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition shadow-lg shadow-violet-900/20 text-sm border border-gray-200">
                    <i class="fas fa-edit mr-2"></i>Edit Ujian
                </a>
                @endif
                <a href="{{ route('admin.cbt.results', $exam) }}" class="inline-flex items-center px-5 py-2.5 bg-white text-violet-700 rounded-xl font-semibold hover:bg-violet-50 transition shadow-lg shadow-violet-900/20 text-sm">
                    <i class="fas fa-chart-bar mr-2"></i>Lihat Hasil
                </a>
                <a href="{{ route('admin.cbt.index') }}" class="inline-flex items-center px-5 py-2.5 bg-white/15 text-white rounded-xl hover:bg-white/25 transition border border-gray-200 text-sm">
                    <i class="fas fa-list mr-2"></i>Semua Ujian
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl">
        <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0"><i class="fas fa-check text-emerald-600 text-base"></i></div>
        <p class="text-emerald-700 text-base font-medium">{{ session('success') }}</p>
    </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
        @php $statItems = [
            ['Peserta', $statistics['total_participants'] ?? 0, 'fa-users', 'violet'],
            ['Selesai', $statistics['completed_count'] ?? 0, 'fa-check-double', 'blue'],
            ['Rata-rata', number_format($statistics['average_score'] ?? 0, 1), 'fa-calculator', 'indigo'],
            ['Tertinggi', number_format($statistics['highest_score'] ?? 0, 1), 'fa-arrow-up', 'emerald'],
            ['Terendah', number_format($statistics['lowest_score'] ?? 0, 1), 'fa-arrow-down', 'red'],
            ['Lulus', $statistics['passed_count'] ?? 0, 'fa-trophy', 'green'],
            ['Tidak Lulus', $statistics['failed_count'] ?? 0, 'fa-times-circle', 'rose'],
        ]; @endphp
        @foreach($statItems as [$label, $value, $icon, $color])
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 text-center hover:shadow-md transition-all duration-300">
            <div class="w-10 h-10 rounded-xl bg-{{ $color }}-100 flex items-center justify-center mx-auto mb-2">
                <i class="fas {{ $icon }} text-{{ $color }}-600 text-base"></i>
            </div>
            <div class="text-2xl font-bold text-gray-900">{{ $value }}</div>
            <div class="text-base text-gray-700 mt-1">{{ $label }}</div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Exam Info --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center"><i class="fas fa-info-circle text-violet-600"></i></div>
                    <h2 class="text-lg font-bold text-gray-900">Informasi Ujian</h2>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @php $infoItems = [
                        ['Tipe', strtoupper($exam->exam_type), 'fa-tag'],
                        ['Scope', $exam->exam_scope === 'school' ? 'Ujian Sekolah' : 'Ujian Kelas', 'fa-globe'],
                        ['Guru Pengampu', $exam->teacher?->full_name ?? 'Belum Ditugaskan', 'fa-chalkboard-teacher'],
                        ['Durasi', $exam->duration_minutes . ' menit', 'fa-clock'],
                        ['KKM', $exam->passing_score, 'fa-bullseye'],
                        ['Maks Percobaan', $exam->max_attempts . '×', 'fa-redo'],
                        ['Total Soal', $exam->total_questions_shown, 'fa-list-ol'],
                        ['Mulai', $exam->start_time ? \Carbon\Carbon::parse($exam->start_time)->format('d M Y H:i') : '-', 'fa-calendar-check'],
                        ['Selesai', $exam->end_time ? \Carbon\Carbon::parse($exam->end_time)->format('d M Y H:i') : '-', 'fa-calendar-times'],
                    ]; @endphp
                    @foreach($infoItems as [$label, $val, $icon])
                    <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200">
                        <div class="flex items-center gap-2 mb-1">
                            <i class="fas {{ $icon }} text-gray-800 text-base"></i>
                            <span class="text-base text-gray-700 font-medium">{{ $label }}</span>
                        </div>
                        <span class="text-base font-bold text-gray-900">{{ $val }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Pengaturan --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center"><i class="fas fa-cog text-indigo-600"></i></div>
                    <h2 class="text-lg font-bold text-gray-900">Pengaturan</h2>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    @php $settings = [
                        ['Acak Soal', $exam->randomize_questions],
                        ['Acak Opsi', $exam->randomize_options],
                        ['Deteksi Tab', $exam->prevent_tab_switch],
                        ['Blokir Copy', $exam->prevent_copy_paste ?? false],
                        ['Tampil Hasil', $exam->show_result],
                        ['Tampil Kunci', $exam->show_answer_key],
                        ['Review', $exam->allow_review],
                        ['Sync Nilai', $exam->auto_sync_grade],
                    ]; @endphp
                    @foreach($settings as [$label, $val])
                    <div class="flex items-center gap-2.5 p-3 rounded-xl {{ $val ? 'bg-emerald-50 border border-emerald-100' : 'bg-gray-50 border border-gray-200' }}">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ $val ? 'bg-emerald-100' : 'bg-gray-200' }}">
                            <i class="fas fa-{{ $val ? 'check' : 'times' }} text-base {{ $val ? 'text-emerald-600' : 'text-gray-800' }}"></i>
                        </div>
                        <span class="text-base font-medium {{ $val ? 'text-emerald-700' : 'text-gray-700' }}">{{ $label }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            @if($exam->exam_description)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center"><i class="fas fa-align-left text-amber-600"></i></div>
                    <h2 class="text-lg font-bold text-gray-900">Deskripsi</h2>
                </div>
                <p class="text-base text-gray-700 leading-relaxed">{{ $exam->exam_description }}</p>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Status Badge --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 text-center">
                @php $statusMap = match($exam->status) {
                    'active' => ['Aktif', 'emerald', 'fa-satellite-dish'],
                    'completed' => ['Selesai', 'blue', 'fa-check-double'],
                    'published' => ['Diterbitkan', 'amber', 'fa-bullhorn'],
                    'draft' => ['Draf', 'gray', 'fa-pencil-alt'],
                    default => ['Unknown', 'gray', 'fa-question'],
                }; @endphp
                <div class="w-16 h-16 rounded-2xl bg-{{ $statusMap[1] }}-100 flex items-center justify-center mx-auto mb-3">
                    <i class="fas {{ $statusMap[2] }} text-2xl text-{{ $statusMap[1] }}-600"></i>
                </div>
                <div class="text-lg font-bold text-gray-900">{{ $statusMap[0] }}</div>
                <div class="text-base text-gray-700 mt-1">Status Ujian</div>
            </div>

            {{-- Actions --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-base font-bold text-gray-700 uppercase tracking-wider mb-4">Aksi</h3>
                <div class="space-y-2.5">
                    @if($exam->isSchoolScope())
                        @if(in_array($exam->status, ['draft', 'published', 'active']))
                        <a href="{{ route('admin.cbt.exams.edit', $exam) }}" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-white border-2 border-indigo-200 text-indigo-700 rounded-xl hover:bg-indigo-50 transition font-medium text-base">
                            <i class="fas fa-edit"></i>Edit Ujian
                        </a>
                        @endif
                        @if($exam->status === 'draft')
                        <form action="{{ route('admin.cbt.exams.publish', $exam) }}" method="POST">@csrf
                            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-xl hover:shadow-lg transition font-medium text-base">
                                <i class="fas fa-bullhorn"></i>Terbitkan Ujian
                            </button>
                        </form>
                        @endif
                        @if($exam->status === 'published')
                        <form action="{{ route('admin.cbt.exams.activate', $exam) }}" method="POST">@csrf
                            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-emerald-500 to-green-500 text-white rounded-xl hover:shadow-lg transition font-medium text-base">
                                <i class="fas fa-play"></i>Aktifkan Ujian
                            </button>
                        </form>
                        @endif
                        @if($exam->status === 'active')
                        @if($exam->is_paused)
                        <form action="{{ route('admin.cbt.exams.resume', $exam) }}" method="POST">@csrf
                            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-xl hover:shadow-lg transition font-medium text-base">
                                <i class="fas fa-play"></i>Lanjutkan Ujian
                            </button>
                        </form>
                        @else
                        <form action="{{ route('admin.cbt.exams.pause', $exam) }}" method="POST" onsubmit="return confirm('Jeda ujian untuk seluruh siswa?')">@csrf
                            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-xl hover:shadow-lg transition font-medium text-base">
                                <i class="fas fa-pause"></i>Jeda Ujian
                            </button>
                        </form>
                        @endif
                        <form action="{{ route('admin.cbt.exams.batch-start', $exam) }}" method="POST">@csrf
                            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-xl hover:shadow-lg transition font-medium text-base">
                                <i class="fas fa-users"></i>Mulai Serentak
                            </button>
                        </form>
                        @endif
                    @endif
                    @if($exam->auto_sync_grade && $exam->status === 'completed')
                    <form action="{{ route('admin.cbt.sync-grades', $exam) }}" method="POST">@csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-purple-500 to-violet-500 text-white rounded-xl hover:shadow-lg transition font-medium text-base">
                            <i class="fas fa-sync"></i>Sync Nilai ke Rapor
                        </button>
                    </form>
                    @endif
                    @if(in_array($exam->status, ['active', 'published']))
                    <form action="{{ route('admin.cbt.force-complete', $exam) }}" method="POST" onsubmit="return confirm('Yakin tutup paksa ujian ini?')">@csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-white border-2 border-red-200 text-red-600 rounded-xl hover:bg-red-50 transition font-medium text-base">
                            <i class="fas fa-stop"></i>Tutup Paksa
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            {{-- Guru Pengampu / Penilai --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-gray-700 uppercase tracking-wider">Guru Pengampu</h3>
                    @if($exam->teacher)
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Ditugaskan</span>
                    @else
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">Belum Ada</span>
                    @endif
                </div>

                <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center font-bold">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-gray-900 truncate">
                                {{ $exam->teacher?->full_name ?? 'Belum Ditugaskan' }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $exam->teacher?->teacher_code ?? 'Guru ini berhak mengkoreksi esai ujian' }}
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.cbt.exams.assign-teacher', $exam) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Pilih / Ubah Guru</label>
                        <select name="teacher_id" class="w-full text-sm rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-violet-500 p-2.5 font-medium">
                            <option value="">-- Tanpa Guru Pengampu --</option>
                            @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ $exam->teacher_id == $t->id ? 'selected' : '' }}>
                                {{ $t->full_name }} ({{ $t->teacher_code }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full py-2.5 px-4 bg-violet-600 hover:bg-violet-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition shadow-sm">
                        <i class="fas fa-save mr-1.5"></i> Simpan Penugasan Guru
                    </button>
                </form>
            </div>

            {{-- Kelas Peserta --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-base font-bold text-gray-700 uppercase tracking-wider mb-4">Kelas Peserta ({{ $exam->participants->count() }})</h3>
                <div class="space-y-2">
                    @foreach($exam->participants as $p)
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200">
                        <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center"><i class="fas fa-chalkboard text-violet-600 text-base"></i></div>
                        <span class="text-base font-medium text-gray-700">{{ $p->classroom->class_name ?? '-' }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
