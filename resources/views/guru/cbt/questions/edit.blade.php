@extends('layouts.guru')
@section('title', 'Edit Soal')
@section('content')
<div class="space-y-8">
    {{-- Hero Header --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700 rounded-2xl p-8 text-white">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative flex items-center gap-5">
            <a href="{{ route('guru.cbt.banks.show', $question->question_bank_id) }}" class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center hover:bg-white/25 transition border border-gray-200">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Edit Soal</h1>
                <p class="text-emerald-50 mt-1 text-base">{{ $question->questionBank->bank_name ?? '-' }}</p>
            </div>
        </div>
    </div>

    <form action="{{ route('guru.cbt.questions.update', $question) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf @method('PUT')

        {{-- Pengaturan Soal --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center"><i class="fas fa-sliders-h text-indigo-600"></i></div>
                <h2 class="text-lg font-bold text-gray-900">Pengaturan Soal</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Tipe Soal</label>
                    <div class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-700 text-base font-medium">{{ strtoupper(str_replace('_', ' ', $question->question_type)) }}</div>
                </div>
                <div>
                    <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Tingkat Kesulitan</label>
                    <select name="difficulty" required class="w-full rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3">
                        @foreach(['mudah','sedang','sulit'] as $d)
                        <option value="{{ $d }}" {{ $question->difficulty === $d ? 'selected' : '' }}>{{ ucfirst($d) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Poin</label>
                    <input type="number" name="points" value="{{ $question->points }}" min="1" required class="w-full rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3">
                </div>
            </div>
        </div>

        {{-- Teks Soal --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center"><i class="fas fa-pen-fancy text-emerald-600"></i></div>
                <h2 class="text-lg font-bold text-gray-900">Teks Soal</h2>
            </div>
            <textarea name="question_text" rows="4" required
                class="w-full rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3 math-support">{{ $question->question_text }}</textarea>
            
            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-indigo-50 border border-indigo-100 rounded-xl text-base text-indigo-700">
                    <div class="flex items-center gap-2 font-bold mb-1.5">
                        <i class="fas fa-calculator text-indigo-600"></i>
                        <span>Rumus Matematika & Fisika (LaTeX)</span>
                    </div>
                    <p class="leading-relaxed text-sm">Gunakan format LaTeX (diapit tanda dolar) untuk merender rumus secara otomatis:</p>
                    <ul class="list-disc pl-5 mt-1 space-y-1 text-sm font-medium">
                        <li><strong>Inline:</strong> <code>$x^2 + y^2 = r^2$</code></li>
                        <li><strong>Pecahan:</strong> <code>$\frac{1}{2}$</code> → ½</li>
                        <li><strong>Akar:</strong> <code>$\sqrt{x}$</code> → √x</li>
                        <li><strong>Simbol:</strong> <code>$\pi, \alpha, \beta$</code> → π, α, β</li>
                    </ul>
                </div>
                <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-xl text-base text-emerald-700">
                    <div class="flex items-center gap-2 font-bold mb-1.5">
                        <i class="fas fa-flask text-emerald-600"></i>
                        <span>Rumus Kimia & Media</span>
                    </div>
                    <p class="leading-relaxed text-sm">Anda juga dapat menulis rumus kimia atau menyertakan media lain:</p>
                    <ul class="list-disc pl-5 mt-1 space-y-1 text-sm font-medium">
                        <li><strong>Senyawa:</strong> <code>$H_2O$</code> atau <code>$CO_2$</code></li>
                        <li><strong>Reaksi:</strong> <code>$2H_2 + O_2 \rightarrow 2H_2O$</code></li>
                        <li>Gunakan pengaturan di bawah ini untuk menambah gambar, audio, atau video pada soal.</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Media Soal --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center"><i class="fas fa-photo-video text-blue-600"></i></div>
                <h2 class="text-lg font-bold text-gray-900">Media Pendukung</h2>
                <span class="text-base text-gray-800 font-medium">Opsional</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                {{-- Gambar --}}
                <div class="p-4 bg-green-50 rounded-xl border border-green-100">
                    <label class="block text-base font-bold text-green-700 mb-2"><i class="fas fa-image mr-1"></i>Gambar</label>
                    @if($question->question_image)
                    <div class="mb-2">
                        <img src="{{ $question->question_image_url }}" alt="Preview" class="w-32 h-24 object-cover rounded-xl border shadow-sm">
                        <label class="mt-2 flex items-center gap-1.5 text-base text-red-500 cursor-pointer">
                            <input type="checkbox" name="remove_question_image" value="1" class="rounded text-red-500"> Hapus gambar
                        </label>
                    </div>
                    @endif
                    <input type="file" name="question_image" accept="image/*"
                        class="w-full text-base text-gray-700 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-base file:font-medium file:bg-green-100 file:text-green-700 hover:file:bg-green-200">
                    <p class="text-base text-gray-800 mt-1.5">JPG, PNG, GIF, WebP &bull; Max 2MB</p>
                </div>
                {{-- Audio --}}
                <div class="p-4 bg-purple-50 rounded-xl border border-purple-100">
                    <label class="block text-base font-bold text-purple-700 mb-2"><i class="fas fa-headphones mr-1"></i>Audio</label>
                    @if($question->question_audio)
                    <div class="mb-2">
                        <audio controls class="w-full h-10"><source src="{{ $question->question_audio_url }}"></audio>
                        <label class="mt-2 flex items-center gap-1.5 text-base text-red-500 cursor-pointer">
                            <input type="checkbox" name="remove_question_audio" value="1" class="rounded text-red-500"> Hapus audio
                        </label>
                    </div>
                    @endif
                    <input type="file" name="question_audio" accept="audio/*"
                        class="w-full text-base text-gray-700 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-base file:font-medium file:bg-purple-100 file:text-purple-700 hover:file:bg-purple-200">
                    <p class="text-base text-gray-800 mt-1.5">MP3, WAV, OGG, M4A &bull; Max 10MB</p>
                </div>
                {{-- Video --}}
                <div class="p-4 bg-red-50 rounded-xl border border-red-100">
                    <label class="block text-base font-bold text-red-700 mb-2"><i class="fas fa-video mr-1"></i>Video</label>
                    @if($question->question_video)
                    <div class="mb-2">
                        <video controls class="w-full rounded-xl" style="max-height: 120px"><source src="{{ $question->question_video_url }}"></video>
                        <label class="mt-2 flex items-center gap-1.5 text-base text-red-500 cursor-pointer">
                            <input type="checkbox" name="remove_question_video" value="1" class="rounded text-red-500"> Hapus video
                        </label>
                    </div>
                    @endif
                    <input type="file" name="question_video" accept="video/*"
                        class="w-full text-base text-gray-700 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-base file:font-medium file:bg-red-100 file:text-red-700 hover:file:bg-red-200">
                    <p class="text-base text-gray-800 mt-1.5">MP4, WebM &bull; Max 40MB</p>
                </div>
            </div>
        </div>

        {{-- MC/TF Options --}}
        @if(in_array($question->question_type, ['multiple_choice', 'true_false']))
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center"><i class="fas fa-list-ul text-amber-600"></i></div>
                <h2 class="text-lg font-bold text-gray-900">Pilihan Jawaban</h2>
            </div>
            <div class="space-y-3">
                @foreach($question->options->sortBy('sort_order') as $idx => $opt)
                <div class="p-4 rounded-xl {{ $opt->is_correct ? 'bg-emerald-50 border border-emerald-200' : 'bg-gray-50 border border-gray-200' }}">
                    <div class="flex items-center gap-3">
                        <input type="hidden" name="options[{{ $idx }}][label]" value="{{ $opt->option_label }}">
                        <div class="w-10 h-10 rounded-xl border-2 flex items-center justify-center text-base font-bold flex-shrink-0 {{ $opt->is_correct ? 'bg-emerald-500 text-white border-emerald-600' : 'bg-white text-gray-800 border-gray-300' }}">{{ $opt->option_label }}</div>
                        <input type="text" name="options[{{ $idx }}][text]" id="cbt_edit_opt_{{ $idx }}" value="{{ $opt->option_text }}"
                            class="flex-1 rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-2.5 math-support-input">
                        <button type="button" onclick="window.openMathPalette(document.getElementById('cbt_edit_opt_{{ $idx }}'))" title="Sisipkan Simbol/Rumus" class="px-2.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-xl text-xs font-bold transition-all shadow-2xs flex items-center gap-1">
                            <i class="fas fa-square-root-variable text-[11px]"></i>
                            <span class="hidden sm:inline">Simbol</span>
                        </button>
                        <label class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white border border-gray-200 cursor-pointer hover:bg-emerald-50 hover:border-emerald-200 transition">
                            <input type="radio" name="correct_option" value="{{ $idx }}" {{ $opt->is_correct ? 'checked' : '' }} class="text-emerald-600 focus:ring-emerald-500">
                            <span class="text-base font-bold text-gray-800">Benar</span>
                        </label>
                        <input type="hidden" name="options[{{ $idx }}][is_correct]" value="{{ $opt->is_correct ? '1' : '0' }}">
                    </div>
                    <div class="ml-13 mt-2 flex items-center gap-3">
                        @if($opt->option_image)
                        <img src="{{ $opt->option_image_url }}" alt="Opsi {{ $opt->option_label }}" class="w-16 h-12 object-cover rounded-lg border">
                        @endif
                        <input type="file" name="options[{{ $idx }}][image]" accept="image/*"
                            class="text-base text-gray-800 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-base file:bg-gray-100 file:text-gray-800 hover:file:bg-gray-200">
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Answer Key --}}
        @if(in_array($question->question_type, ['essay', 'fill_blank']))
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center"><i class="fas fa-key text-amber-600"></i></div>
                <h2 class="text-lg font-bold text-gray-900">Kunci Jawaban</h2>
            </div>
            <textarea name="answer_key" rows="2"
                class="w-full rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3">{{ $question->answer_key }}</textarea>
        </div>
        @endif

        {{-- Informasi Tambahan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center"><i class="fas fa-tags text-gray-800"></i></div>
                <h2 class="text-lg font-bold text-gray-900">Informasi Tambahan</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div>
                    <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Topik</label>
                    <input type="text" name="topic" value="{{ $question->topic }}" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3">
                </div>
                <div>
                    <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Kompetensi</label>
                    <input type="text" name="competency" value="{{ $question->competency }}" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3">
                </div>
            </div>
            <div>
                <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Pembahasan</label>
                <textarea name="explanation" rows="3"
                    class="w-full rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3">{{ $question->explanation }}</textarea>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('guru.cbt.banks.show', $question->question_bank_id) }}" class="px-6 py-3 bg-white border border-gray-200 text-gray-800 rounded-xl hover:bg-gray-50 transition text-base font-medium">Batal</a>
            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl hover:shadow-lg transition text-base font-medium flex items-center gap-2">
                <i class="fas fa-save"></i>Update Soal
            </button>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[name="correct_option"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.querySelectorAll('input[name^="options"][name$="[is_correct]"]').forEach(function(h) { h.value = '0'; });
            var idx = this.value;
            var target = document.querySelector('input[name="options[' + idx + '][is_correct]"]');
            if (target) target.value = '1';
        });
    });
});
</script>
@endpush
