@extends('layouts.guru')
@section('title', $bank->bank_name)
@section('content')
<div class="space-y-8" x-data="{ showModal: false, showLmsModal: false }">
    {{-- Hero Header --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700 rounded-2xl p-8 text-white">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center gap-5">
                <a href="{{ route('guru.cbt.banks.index') }}" class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center hover:bg-white/25 transition border border-gray-200">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ $bank->bank_name }}</h1>
                    <p class="text-emerald-50 mt-1 text-base">{{ $bank->subject->subject_name ?? $bank->subject->name ?? '-' }} &bull; Kelas {{ $bank->grade_level }} &bull; {{ $bank->questions->count() }} soal</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" @click="showLmsModal = true" 
                        class="px-5 py-2.5 bg-amber-400 hover:bg-amber-300 text-slate-900 rounded-xl font-black text-base border-2 border-black transition flex items-center gap-2 shadow-md cursor-pointer">
                    <i class="fas fa-rocket text-black"></i><span>🚀 Jadikan Kuis LMS</span>
                </button>
                <button type="button" @click="showModal = true" 
                        class="px-5 py-2.5 bg-white/15 hover:bg-white/25 text-white rounded-xl font-bold text-base border border-white/20 transition flex items-center gap-2 shadow-sm cursor-pointer">
                    <i class="fas fa-file-import"></i><span>Import Soal</span>
                </button>
                <a href="{{ route('guru.cbt.banks.edit', $bank) }}" class="px-5 py-2.5 bg-white/15 rounded-xl font-bold text-base border border-white/20 hover:bg-white/25 transition flex items-center gap-2">
                    <i class="fas fa-edit"></i><span>Edit</span>
                </a>
                <a href="{{ route('guru.cbt.questions.create', $bank) }}" class="px-6 py-2.5 bg-white text-emerald-800 hover:bg-emerald-50 rounded-xl font-bold text-base shadow-md hover:shadow-lg transition flex items-center gap-2">
                    <i class="fas fa-plus"></i><span>Tambah Soal</span>
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0"><i class="fas fa-check text-emerald-600 text-lg"></i></div>
        <p class="text-emerald-800 text-base font-bold">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-2xl shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation-triangle text-red-600 text-lg"></i></div>
        <p class="text-red-700 text-base font-bold">{{ session('error') }}</p>
    </div>
    @endif

    @if(session('import_warnings') && count(session('import_warnings')) > 0)
    <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl shadow-sm space-y-2">
        <div class="flex items-center gap-2 font-bold text-amber-800 text-base">
            <i class="fas fa-exclamation-circle text-amber-600"></i>
            <span>Beberapa baris dilewati saat import:</span>
        </div>
        <ul class="list-disc list-inside text-sm text-amber-700 space-y-1 pl-2 font-medium">
            @foreach(session('import_warnings') as $warning)
            <li>{{ $warning }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Questions List --}}
    @forelse($bank->questions as $idx => $question)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-base font-bold flex-shrink-0">{{ $idx + 1 }}</div>
                <div class="flex flex-wrap gap-2">
                    <span class="px-2.5 py-0.5 rounded-lg text-base font-bold bg-blue-100 text-blue-800 border border-blue-100">{{ strtoupper(str_replace('_', ' ', $question->question_type)) }}</span>
                    <span class="px-2.5 py-0.5 rounded-lg text-base font-bold bg-gray-50 text-gray-800 border border-gray-200">{{ $question->points }} poin</span>
                    @php $diffColor = match($question->difficulty) {
                        'sulit' => 'bg-red-50 text-red-700 border-red-200',
                        'sedang' => 'bg-amber-100 text-amber-800 border-amber-200',
                        default => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                    }; @endphp
                    <span class="px-2.5 py-0.5 rounded-lg text-base font-bold border {{ $diffColor }}">{{ ucfirst($question->difficulty) }}</span>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('guru.cbt.questions.edit', $question) }}" class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-100 transition border border-amber-100">
                    <i class="fas fa-edit text-base"></i>
                </a>
                <form action="{{ route('guru.cbt.questions.destroy', $question) }}" method="POST" onsubmit="return confirm('Hapus soal ini?')">
                    @csrf @method('DELETE')
                    <button class="w-9 h-9 rounded-xl bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition border border-red-100">
                        <i class="fas fa-trash text-base"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="text-base text-gray-800 mb-4 whitespace-pre-line leading-relaxed">{!! strip_tags($question->question_text) !== $question->question_text ? $question->question_text : nl2br(e($question->question_text)) !!}</div>

        {{-- Media --}}
        @if($question->question_image || $question->question_audio || $question->question_video)
        <div class="flex flex-wrap gap-3 mb-4">
            @if($question->question_image)
            <img src="{{ $question->question_image_url }}" alt="Gambar Soal" class="w-32 h-24 object-cover rounded-xl border border-gray-200 shadow-sm">
            @endif
            @if($question->question_audio)
            <audio controls preload="none" class="h-10"><source src="{{ $question->question_audio_url }}"></audio>
            @endif
            @if($question->question_video)
            <video controls preload="none" class="w-48 rounded-xl border shadow-sm"><source src="{{ $question->question_video_url }}"></video>
            @endif
        </div>
        @endif

        {{-- Options --}}
        @if($question->options->count())
        <div class="space-y-2 ml-2">
            @foreach($question->options->sortBy('sort_order') as $opt)
            <div class="flex items-start gap-2.5 p-2.5 rounded-xl text-base {{ $opt->is_correct ? 'bg-emerald-50 border border-emerald-200' : 'bg-gray-50 border border-gray-200' }}">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-base font-bold flex-shrink-0 {{ $opt->is_correct ? 'bg-emerald-500 text-white' : 'bg-white text-gray-800 border border-gray-200' }}">{{ $opt->option_label }}</div>
                <div class="flex-1">
                    <span class="{{ $opt->is_correct ? 'text-emerald-800 font-medium' : 'text-gray-700' }}">{{ $opt->option_text }}</span>
                    @if($opt->is_correct)<i class="fas fa-check text-emerald-500 ml-1"></i>@endif
                    @if($opt->option_image)
                    <img src="{{ $opt->option_image_url }}" alt="Opsi {{ $opt->option_label }}" class="mt-1.5 w-24 h-18 object-cover rounded-lg border">
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @if($question->answer_key)
        <div class="mt-4 p-3.5 bg-emerald-50 rounded-xl border border-emerald-100 text-base">
            <span class="font-bold text-emerald-700"><i class="fas fa-key mr-1"></i>Kunci:</span> {{ $question->answer_key }}
        </div>
        @endif

        @if($question->explanation)
        <div class="mt-3 p-3.5 bg-blue-50 rounded-xl border border-blue-100 text-base">
            <span class="font-bold text-blue-700"><i class="fas fa-lightbulb mr-1"></i>Pembahasan:</span> {{ $question->explanation }}
        </div>
        @endif
    </div>
    @empty
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4"><i class="fas fa-question-circle text-3xl text-gray-500"></i></div>
        <h3 class="text-lg font-bold text-gray-700 mb-2">Belum Ada Soal</h3>
        <p class="text-gray-700 mb-4 text-base">Tambahkan soal pertama ke bank soal ini.</p>
        <a href="{{ route('guru.cbt.questions.create', $bank) }}" class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl hover:shadow-lg transition text-base font-medium">
            <i class="fas fa-plus mr-2"></i>Tambah Soal
        </a>
    </div>
    @endforelse

    {{-- MODAL IMPORT SOAL KE BANK SOAL INI --}}
    <div x-show="showModal" x-cloak style="display: none;"
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showModal = false"></div>

        {{-- Modal Box --}}
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto z-10 border border-gray-200 text-left"
             @click.stop
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="sticky top-0 z-10 bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-4 text-white flex justify-between items-center">
                <h3 class="text-lg font-bold flex items-center gap-2"><i class="fas fa-file-import"></i> Import Soal ke: {{ $bank->bank_name }}</h3>
                <button type="button" @click="showModal = false" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form action="{{ route('guru.cbt.questions.import', $bank) }}" method="POST" enctype="multipart/form-data"
                  onsubmit="document.getElementById('importQSubmitBtn').innerHTML = '<i class=\'fas fa-spinner fa-spin\'></i> Mengimpor...'; document.getElementById('importQSubmitBtn').classList.add('opacity-75', 'cursor-not-allowed'); return true;">
                @csrf
                <div class="p-6 space-y-4">
                    <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100 flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-emerald-900">Butuh template Excel?</h4>
                            <p class="text-xs text-emerald-700 font-medium">Gunakan format standar agar soal berhasil dibaca sistem.</p>
                        </div>
                        <a href="{{ route('guru.cbt.banks.import-template') }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1 shadow-sm">
                            <i class="fas fa-download"></i> Template
                        </a>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 uppercase mb-2">Pilih File Soal (.XLSX / .ZIP) <span class="text-red-500">*</span></label>
                        <input type="file" name="import_file" accept=".xlsx,.xls,.zip" required
                               class="w-full text-sm text-gray-700 font-medium file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-gray-300 rounded-xl p-1.5 bg-gray-50">
                        <p class="text-xs text-gray-500 mt-2 font-medium">Mendukung file .xlsx (Excel saja) atau .zip (Excel beserta folder images/). Maksimal 50MB.</p>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t border-gray-200">
                    <button type="button" @click="showModal = false" class="px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-gray-700 font-bold hover:bg-gray-100 transition text-sm">Batal</button>
                    <button type="submit" id="importQSubmitBtn" class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-bold hover:shadow-md transition text-sm flex items-center gap-2 cursor-pointer">
                        <i class="fas fa-file-import"></i> Mulai Import
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL TAUTKAN KE KUIS LMS KELAS --}}
    <div x-show="showLmsModal" x-cloak style="display: none;"
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showLmsModal = false"></div>

        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden z-10 border-2 border-black text-left"
             @click.stop>
            <div class="bg-slate-900 px-6 py-4 text-white flex justify-between items-center border-b-2 border-black">
                <h3 class="text-base font-black flex items-center gap-2 text-amber-400">
                    <i class="fas fa-rocket"></i> Tautkan Bank Soal ke Kuis Kelas LMS
                </h3>
                <button type="button" @click="showLmsModal = false" class="text-white hover:text-gray-300">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <form action="{{ route('guru.cbt.banks.assign-to-lms', $bank) }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="p-4 bg-amber-50 rounded-2xl border-2 border-amber-200">
                    <p class="text-xs font-black text-amber-900">📦 Bank Soal Terpilih:</p>
                    <p class="text-sm font-black text-slate-900 mt-0.5">{{ $bank->bank_name }} ({{ $bank->questions->count() }} Soal)</p>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-900 uppercase tracking-wider mb-1">Pilih Kelas / Mata Pelajaran LMS Target <span class="text-rose-600">*</span></label>
                    <select name="course_id" required class="w-full border-2 border-black rounded-2xl p-3 text-xs font-black bg-white outline-none">
                        <option value="">-- Pilih Kelas LMS --</option>
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}">{{ $c->course_name }} ({{ $c->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-900 uppercase tracking-wider mb-1">Judul Kuis LMS Baru <span class="text-rose-600">*</span></label>
                    <input type="text" name="title" value="Kuis - {{ $bank->bank_name }}" required 
                           class="w-full border-2 border-black rounded-2xl p-3 text-xs font-black bg-white outline-none">
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-900 uppercase tracking-wider mb-1">Durasi Pengerjaan (Menit) <span class="text-rose-600">*</span></label>
                    <input type="number" name="time_limit" value="30" min="1" required 
                           class="w-full border-2 border-black rounded-2xl p-3 text-xs font-black bg-white outline-none">
                </div>

                <div class="pt-3 border-t-2 border-black flex gap-3">
                    <button type="button" @click="showLmsModal = false" class="flex-1 py-3 bg-slate-200 text-slate-900 border-2 border-black rounded-2xl font-black text-xs">Batal</button>
                    <button type="submit" class="flex-[2] py-3 bg-emerald-600 text-white border-2 border-black rounded-2xl font-black text-xs shadow-md hover:bg-emerald-700 transition">
                        <i class="fas fa-paper-plane mr-1"></i> Tautkan & Buat Kuis Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
