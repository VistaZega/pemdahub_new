@extends('layouts.guru')
@section('title', $exam->exam_title)

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
@endpush

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js" onload="setTimeout(() => renderMathInElement(document.body, {delimiters: [{left: '$$', right: '$$', display: true}, {left: '\\(', right: '\\)', display: false}, {left: '$', right: '$', display: false}]}), 200)"></script>
@endpush

@section('content')
<div class="space-y-8">
    {{-- Hero Header --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700 rounded-2xl p-8 text-white">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div class="flex items-center gap-5">
                <a href="{{ route('guru.cbt.exams.index') }}" class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center hover:bg-white/25 transition border border-gray-200">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ $exam->exam_title }}</h1>
                    <p class="text-emerald-50 mt-1 text-base">{{ $exam->subject->subject_name ?? $exam->subject->name ?? '-' }} &bull; {{ strtoupper($exam->exam_type) }} &bull; {{ $exam->isSchoolScope() ? 'Ujian Sekolah' : 'Ujian Kelas' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @if($hasEssayQuestions)
                <a href="{{ route('guru.cbt.exams.grade-essays', $exam) }}" class="px-4 py-2.5 bg-amber-400 hover:bg-amber-300 text-black border-2 border-black rounded-xl font-black text-sm shadow-md transition flex items-center gap-2">
                    <i class="fas fa-pen-fancy"></i>Koreksi Esai
                    @if(($pendingEssaysCount ?? 0) > 0)
                        <span class="px-2 py-0.5 bg-red-600 text-white text-xs font-black rounded-full shadow-sm">{{ $pendingEssaysCount }}</span>
                    @endif
                </a>
                @endif
                @php $stMap = match($exam->status) {
                    'active' => ['Aktif', 'bg-white/20  border border-emerald-300'],
                    'completed' => ['Selesai', 'bg-blue-400/30  border border-blue-300'],
                    'published' => ['Diterbitkan', 'bg-amber-400/30  border border-amber-300'],
                    default => ['Draf', 'bg-white/10  border border-gray-200'],
                }; @endphp
                <span class="px-5 py-2 rounded-xl text-base font-bold {{ $stMap[1] }}">{{ strtoupper($stMap[0]) }}</span>
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
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
        @php $showCards = [
            ['Soal', $exam->total_questions_shown, 'fa-list-ol', 'emerald'],
            ['Durasi', $exam->duration_minutes . '′', 'fa-clock', 'blue'],
            ['Peserta', $statistics['total_participants'] ?? 0, 'fa-users', 'purple'],
            ['Selesai', $statistics['completed_count'] ?? 0, 'fa-check-double', 'amber'],
        ]; @endphp
        @foreach($showCards as [$label, $val, $icon, $color])
        <div class="group bg-white rounded-2xl shadow-sm border border-gray-200 p-5 text-center hover:shadow-lg transition-all duration-300">
            <div class="w-11 h-11 rounded-xl bg-{{ $color }}-100 flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                <i class="fas {{ $icon }} text-{{ $color }}-600"></i>
            </div>
            <div class="text-2xl font-bold text-gray-900">{{ $val }}</div>
            <div class="text-base text-gray-700 mt-1">{{ $label }}</div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Detail Info --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center"><i class="fas fa-info-circle text-emerald-600"></i></div>
                    <h2 class="text-lg font-bold text-gray-900">Detail Ujian</h2>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @php 
                    $tuitionInfo = $exam->requires_tuition_payment 
                        ? 'Wajib Lunas (' . $exam->getTargetTuitionPeriod()['month_name'] . ' ' . $exam->getTargetTuitionPeriod()['year'] . ')' 
                        : 'Tidak Bersyarat';
                    $detailItems = [
                        ['Waktu Mulai', $exam->start_time ? $exam->start_time->format('d/m/Y H:i') : '-', 'fa-calendar-check'],
                        ['Waktu Selesai', $exam->end_time ? $exam->end_time->format('d/m/Y H:i') : '-', 'fa-calendar-times'],
                        ['KKM', $exam->passing_score, 'fa-bullseye'],
                        ['Maks Percobaan', $exam->max_attempts . '×', 'fa-redo'],
                        ['Kode Akses', $exam->access_code ?: 'Tidak ada', 'fa-key'],
                        ['Sinkron Nilai', $exam->auto_sync_grade ? 'Ya' : 'Tidak', 'fa-sync'],
                        ['Syarat SPP', $tuitionInfo, 'fa-money-bill-wave'],
                    ]; @endphp
                    @foreach($detailItems as [$label, $val, $icon])
                    <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200">
                        <div class="flex items-center gap-2 mb-1">
                            <i class="fas {{ $icon }} text-gray-800 text-base"></i>
                            <span class="text-base text-gray-700 font-medium">{{ $label }}</span>
                        </div>
                        <span class="text-base font-bold text-gray-900">{{ $val }}</span>
                    </div>
                    @endforeach
                </div>
                @if($exam->exam_description)
                <div class="mt-4 p-4 bg-gray-50 rounded-xl border border-gray-200 text-base text-gray-700">{{ $exam->exam_description }}</div>
                @endif
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

            {{-- Daftar Soal --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center"><i class="fas fa-list text-amber-600"></i></div>
                    <h2 class="text-lg font-bold text-gray-900">Daftar Soal ({{ $exam->examQuestions->count() }})</h2>
                </div>
                <div class="space-y-3 max-h-[500px] overflow-y-auto pr-2">
                    @foreach($exam->examQuestions as $i => $eq)
                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl border border-gray-200 hover:bg-white hover:shadow-sm transition">
                        <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-base font-bold flex-shrink-0">{{ $i + 1 }}</div>
                        <div class="flex-1 min-w-0">
                            <p class="text-base text-gray-800 line-clamp-2">{!! strip_tags($eq->question->question_text) !!}</p>
                            <div class="flex items-center gap-3 mt-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-base font-bold bg-blue-100 text-blue-800 border border-blue-100">{{ strtoupper(str_replace('_', ' ', $eq->question->question_type)) }}</span>
                                <span class="text-base text-gray-800">{{ $eq->getEffectivePoints() }} poin</span>
                                <span class="text-base text-gray-800">{{ ucfirst($eq->question->difficulty) }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Actions --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-base font-bold text-gray-700 uppercase tracking-wider mb-4">Aksi</h3>
                <div class="space-y-2.5">
                    {{-- Tombol Koreksi Esai --}}
                    @if($hasEssayQuestions)
                    <a href="{{ route('guru.cbt.exams.grade-essays', $exam) }}" class="w-full flex items-center justify-between px-4 py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-xl hover:shadow-lg transition font-bold text-base shadow-md">
                        <span class="flex items-center gap-2">
                            <i class="fas fa-pen-fancy"></i>Koreksi Esai
                        </span>
                        @if(($pendingEssaysCount ?? 0) > 0)
                            <span class="px-2.5 py-0.5 bg-white text-amber-900 text-xs font-black rounded-full shadow-sm">{{ $pendingEssaysCount }} Belum Dinilai</span>
                        @else
                            <span class="px-2.5 py-0.5 bg-amber-400 text-amber-950 text-xs font-bold rounded-full">Selesai Dinilai</span>
                        @endif
                    </a>
                    @endif

                    @if($exam->isClassScope())
                        @if($exam->status === 'draft')
                        <a href="{{ route('guru.cbt.exams.edit', $exam) }}" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-white border-2 border-emerald-200 text-emerald-600 rounded-xl hover:bg-emerald-50 transition font-medium text-base">
                            <i class="fas fa-edit"></i>Edit Ujian
                        </a>
                        <form action="{{ route('guru.cbt.exams.publish', $exam) }}" method="POST">@csrf
                            <button class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-xl hover:shadow-lg transition font-medium text-base">
                                <i class="fas fa-paper-plane"></i>Terbitkan Ujian
                            </button>
                        </form>
                        <form action="{{ route('guru.cbt.exams.destroy', $exam) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus ujian ini? Tindakan ini tidak dapat dibatalkan.')">@csrf @method('DELETE')
                            <button class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-white border-2 border-red-200 text-red-600 rounded-xl hover:bg-red-50 transition font-medium text-base">
                                <i class="fas fa-trash"></i>Hapus Ujian
                            </button>
                        </form>
                        @elseif($exam->status === 'published')
                        <form action="{{ route('guru.cbt.exams.activate', $exam) }}" method="POST">@csrf
                            <button class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-emerald-500 to-green-500 text-white rounded-xl hover:shadow-lg transition font-medium text-base">
                                <i class="fas fa-play"></i>Aktifkan Ujian
                            </button>
                        </form>
                        @elseif($exam->status === 'active')
                        @if($exam->is_paused)
                        <form action="{{ route('guru.cbt.exams.resume', $exam) }}" method="POST">@csrf
                            <button class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-xl hover:shadow-lg transition font-medium text-base">
                                <i class="fas fa-play"></i>Lanjutkan Ujian
                            </button>
                        </form>
                        @else
                        <form action="{{ route('guru.cbt.exams.pause', $exam) }}" method="POST" onsubmit="return confirm('Jeda ujian untuk seluruh siswa?')">@csrf
                            <button class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-xl hover:shadow-lg transition font-medium text-base">
                                <i class="fas fa-pause"></i>Jeda Ujian
                            </button>
                        </form>
                        @endif
                        <form action="{{ route('guru.cbt.exams.batch-start', $exam) }}" method="POST" onsubmit="return confirm('Mulai sesi ujian untuk semua siswa sekaligus?')">@csrf
                            <button class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-xl hover:shadow-lg transition font-medium text-base">
                                <i class="fas fa-users"></i>Mulai Serentak
                            </button>
                        </form>
                        <form action="{{ route('guru.cbt.exams.complete', $exam) }}" method="POST" onsubmit="return confirm('Selesaikan ujian ini?')">@csrf
                            <button class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-white border-2 border-red-200 text-red-600 rounded-xl hover:bg-red-50 transition font-medium text-base">
                                <i class="fas fa-stop"></i>Selesaikan Ujian
                            </button>
                        </form>
                        @endif
                    @endif

                    <a href="{{ route('guru.cbt.exams.results', $exam) }}" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-xl hover:shadow-lg transition font-medium text-base">
                        <i class="fas fa-chart-bar"></i>Lihat Hasil
                    </a>

                    @if($exam->status === 'completed')
                    <form action="{{ route('guru.cbt.exams.sync-grades', $exam) }}" method="POST">@csrf
                        <button class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-purple-500 to-violet-500 text-white rounded-xl hover:shadow-lg transition font-medium text-base">
                            <i class="fas fa-sync"></i>Sinkron ke Nilai
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            {{-- Kelas Peserta --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-base font-bold text-gray-700 uppercase tracking-wider mb-4">Kelas Peserta ({{ $exam->participants->count() }})</h3>
                <div class="space-y-2">
                    @foreach($exam->participants as $p)
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200">
                        <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center"><i class="fas fa-chalkboard text-teal-600 text-base"></i></div>
                        <span class="text-base font-medium text-gray-700">{{ $p->classroom->class_name ?? '-' }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Kepatuhan Uang Sekolah & Dispensasi --}}
            @if($exam->requires_tuition_payment)
            <div class="bg-white rounded-2xl shadow-sm border border-amber-200 p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-bold text-amber-900 uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-hand-holding-usd text-amber-600"></i> Kepatuhan SPP
                    </h3>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                        {{ $exam->getTargetTuitionPeriod()['month_name'] }} {{ $exam->getTargetTuitionPeriod()['year'] }}
                    </span>
                </div>
                <p class="text-xs text-gray-600 mb-4 leading-relaxed">
                    Siswa yang belum lunas SPP bulan ini otomatis diblokir saat memulai ujian, kecuali menerima dispensasi dari Wali Kelas.
                </p>
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 mb-4 flex items-center justify-between">
                    <span class="text-xs font-semibold text-amber-900">Dispensasi Diberikan:</span>
                    <span class="text-sm font-black text-amber-950 px-2 py-0.5 bg-white rounded-lg border border-amber-200">
                        {{ $exam->dispensations()->where('status', 'granted')->count() }} Siswa
                    </span>
                </div>
                <a href="{{ route('guru.walikelas.cbt-dispensasi.index', ['cbt_exam_id' => $exam->id]) }}" 
                   class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl transition font-bold text-xs shadow-sm">
                    <i class="fas fa-user-shield"></i> Portal Dispensasi Wali Kelas
                </a>
            </div>
            @endif

            {{-- Statistik --}}
            @if(($statistics['average_score'] ?? 0) > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-base font-bold text-gray-700 uppercase tracking-wider mb-4">Statistik</h3>
                <div class="space-y-3">
                    @php $sItems = [
                        ['Rata-rata', number_format($statistics['average_score'], 1), 'text-gray-900'],
                        ['Tertinggi', number_format($statistics['highest_score'], 1), 'text-emerald-600'],
                        ['Terendah', number_format($statistics['lowest_score'], 1), 'text-red-600'],
                        ['Lulus', ($statistics['passed_count'] ?? 0) . '/' . ($statistics['completed_count'] ?? 0), 'text-gray-900'],
                    ]; @endphp
                    @foreach($sItems as [$label, $val, $color])
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl">
                        <span class="text-base text-gray-700 font-medium">{{ $label }}</span>
                        <span class="text-base font-bold {{ $color }}">{{ $val }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
