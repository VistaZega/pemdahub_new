@extends('layouts.guru')
@section('title', 'Tambah Soal - ' . $bank->bank_name)

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    .type-tab { cursor: pointer; transition: all 0.3s ease; border-width: 2px; }
    .type-tab.active { border-color: #059669; background-color: #ecfdf5; color: #065f46; transform: translateY(-2px); }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="questionForm()" x-cloak>
    {{-- simplified Header --}}
    <div class="flex items-center justify-between bg-white rounded-2xl p-4 border border-gray-200 shadow-sm">
        <div class="flex items-center gap-4">
            <a href="{{ route('guru.cbt.banks.show', $bank) }}" class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center hover:bg-emerald-50 text-gray-700 hover:text-emerald-600 transition-all border border-gray-200">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900 leading-none">Tambah Soal Baru</h1>
                <p class="text-base text-gray-800 mt-1 font-medium">{{ $bank->bank_name }} &bull; Kelas {{ $bank->grade_level }}</p>
            </div>
        </div>
        <div class="bg-indigo-50 px-4 py-2 rounded-xl border border-indigo-100">
            <span class="block text-base font-semibold text-indigo-400 uppercase tracking-wider leading-none mb-1">Total Soal</span>
            <span class="text-base font-bold text-indigo-700 leading-none">{{ $bank->total_questions }}</span>
        </div>
    </div>

    <form action="{{ route('guru.cbt.questions.store', $bank) }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="space-y-6">
            {{-- 1. PILIH TIPE SOAL (Very Visual) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <label class="block text-base font-semibold text-gray-800 uppercase tracking-wider mb-4">Pilih Tipe Soal</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <label class="type-tab px-4 py-4 rounded-xl border-gray-200 bg-gray-50 flex flex-col items-center gap-2 text-center" :class="questionType === 'multiple_choice' ? 'active shadow-lg' : 'hover:bg-gray-100'">
                        <input type="radio" name="question_type" value="multiple_choice" x-model="questionType" class="hidden">
                        <i class="fas fa-list-ul text-lg"></i>
                        <span class="text-base font-semibold uppercase">Pilihan Ganda</span>
                    </label>
                    <label class="type-tab px-4 py-4 rounded-xl border-gray-200 bg-gray-50 flex flex-col items-center gap-2 text-center" :class="questionType === 'true_false' ? 'active shadow-lg' : 'hover:bg-gray-100'">
                        <input type="radio" name="question_type" value="true_false" x-model="questionType" class="hidden">
                        <i class="fas fa-check-double text-lg"></i>
                        <span class="text-base font-semibold uppercase">Benar / Salah</span>
                    </label>
                    <label class="type-tab px-4 py-4 rounded-xl border-gray-200 bg-gray-50 flex flex-col items-center gap-2 text-center" :class="questionType === 'essay' ? 'active shadow-lg' : 'hover:bg-gray-100'">
                        <input type="radio" name="question_type" value="essay" x-model="questionType" class="hidden">
                        <i class="fas fa-pen-nib text-lg"></i>
                        <span class="text-base font-semibold uppercase">Essay / Uraian</span>
                    </label>
                    <label class="type-tab px-4 py-4 rounded-xl border-gray-200 bg-gray-50 flex flex-col items-center gap-2 text-center" :class="questionType === 'fill_blank' ? 'active shadow-lg' : 'hover:bg-gray-100'">
                        <input type="radio" name="question_type" value="fill_blank" x-model="questionType" class="hidden">
                        <i class="fas fa-align-left text-lg"></i>
                        <span class="text-base font-semibold uppercase">Isian Singkat</span>
                    </label>
                </div>
            </div>

            {{-- 2. ISI PERTANYAAN --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-200 flex items-center gap-3">
                    <i class="fas fa-pen-fancy text-emerald-600"></i>
                    <h2 class="text-base font-bold text-gray-800 uppercase tracking-wider">Konten Pertanyaan</h2>
                </div>
                <div class="p-6">
                    <textarea name="question_text" rows="5" required
                        class="w-full border-none bg-emerald-50/30 rounded-2xl focus:ring-2 focus:ring-emerald-500/20 p-5 text-lg text-gray-700 placeholder-gray-300 font-medium math-support"
                        placeholder="Ketik pertanyaan di sini...">{{ old('question_text') }}</textarea>
                    
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
                                <span>Rumus Kimia & Video</span>
                            </div>
                            <p class="leading-relaxed text-sm">Anda juga dapat menulis rumus kimia atau menyertakan media lain:</p>
                            <ul class="list-disc pl-5 mt-1 space-y-1 text-sm font-medium">
                                <li><strong>Senyawa:</strong> <code>$H_2O$</code> atau <code>$CO_2$</code></li>
                                <li><strong>Reaksi:</strong> <code>$2H_2 + O_2 \rightarrow 2H_2O$</code></li>
                                <li><strong>Gambar/Audio:</strong> Gunakan form lampiran di bawah</li>
                                <li><strong>Video:</strong> Anda dapat menginput URL video (YouTube) pada form jika tersedia</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 bg-gray-50 rounded-2xl border border-gray-200">
                            <label class="block text-base font-semibold text-gray-800 uppercase tracking-wider mb-2">Lampiran Gambar</label>
                            <input type="file" name="question_image" accept="image/*" class="w-full text-base text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-base file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        </div>
                        <div class="p-4 bg-gray-50 rounded-2xl border border-gray-200">
                            <label class="block text-base font-semibold text-gray-800 uppercase tracking-wider mb-2">Lampiran Audio</label>
                            <input type="file" name="question_audio" accept="audio/*" class="w-full text-base text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-base file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. PILIHAN JAWABAN / KUNCI --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-key text-amber-600"></i>
                        <h2 class="text-base font-bold text-gray-800 uppercase tracking-wider">Jawaban & Kunci</h2>
                    </div>
                </div>
                
                <div class="p-6">
                    {{-- Multiple Choice AREA --}}
                    <div x-show="questionType === 'multiple_choice'" class="space-y-4">
                        <template x-for="(opt, idx) in options" :key="idx">
                            <div class="flex items-center gap-4 group">
                                <label class="flex items-center gap-4 group cursor-pointer">
                                    <div class="flex-shrink-0 relative">
                                        <input type="radio" name="correct_option" :value="idx" @change="setCorrect(idx)" 
                                            :disabled="questionType !== 'multiple_choice'" :checked="opt.is_correct" class="hidden peer">
                                        <div class="w-12 h-12 rounded-xl border-2 flex flex-col items-center justify-center font-bold transition-all peer-checked:bg-emerald-600 peer-checked:border-emerald-600 peer-checked:text-white peer-checked:shadow-lg border-gray-200 text-gray-500 group-hover:border-emerald-200">
                                            <span class="text-lg" x-text="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[idx]"></span>
                                            <span class="text-base mt-1 hidden peer-checked:block uppercase">KUNCI</span>
                                        </div>
                                    </div>
                                    <div class="flex-1 rounded-xl px-5 py-4 border-2 transition-all flex items-center justify-between gap-2"
                                        :class="opt.is_correct ? 'bg-emerald-50 border-emerald-500 shadow-sm' : 'bg-gray-50 border-gray-200 group-hover:border-gray-200'">
                                        <input type="text" :name="'options['+idx+'][text]'" x-model="opt.text"
                                            :disabled="questionType !== 'multiple_choice'"
                                            class="w-full bg-transparent border-none focus:ring-0 p-0 text-base font-bold text-gray-700 placeholder-gray-300 math-support-input"
                                            :placeholder="'Isi jawaban ' + 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[idx] + '...'">
                                        
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            <button type="button" @click.stop.prevent="window.openMathPalette($event.target.closest('.group').querySelector('input[type=text]'))" title="Sisipkan Simbol/Rumus" class="px-2.5 py-1 bg-white hover:bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-lg text-xs font-bold transition-all shadow-2xs flex items-center gap-1">
                                                <i class="fas fa-square-root-variable text-[11px]"></i>
                                                <span class="hidden sm:inline">Simbol</span>
                                            </button>
                                            <div x-show="opt.is_correct" class="text-emerald-600 animate-bounce-short">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" @click="options.splice(idx, 1)" x-show="options.length > 2" class="text-gray-200 hover:text-red-500 transition-colors">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                    <input type="hidden" :name="'options['+idx+'][label]'" :value="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[idx]" :disabled="questionType !== 'multiple_choice'">
                                    <input type="hidden" :name="'options['+idx+'][is_correct]'" :value="opt.is_correct ? 1 : 0" :disabled="questionType !== 'multiple_choice'">
                                </label>
                            </div>
                        </template>
                        <button type="button" @click="addOption()" x-show="options.length < 5" class="w-full py-3 border-2 border-dashed border-gray-200 text-gray-800 rounded-xl hover:border-emerald-200 hover:text-emerald-500 transition-all font-semibold text-base uppercase tracking-wider">
                            <i class="fas fa-plus mr-2"></i> Tambah Pilihan Jawaban
                        </button>
                    </div>

                    {{-- True False AREA --}}
                    <div x-show="questionType === 'true_false'" class="flex gap-4">
                        <label class="flex-1 cursor-pointer group">
                            <input type="radio" name="tf_answer" value="true" x-model="tfAnswer" :disabled="questionType !== 'true_false'" class="hidden peer">
                            <div class="py-8 rounded-2xl border-2 border-gray-50 bg-gray-50 text-center transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:shadow-lg">
                                <i class="fas fa-check-circle text-4xl mb-3" :class="tfAnswer === 'true' ? 'text-emerald-500' : 'text-gray-200'"></i>
                                <div class="font-bold text-gray-700">BENAR</div>
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer group">
                            <input type="radio" name="tf_answer" value="false" x-model="tfAnswer" :disabled="questionType !== 'true_false'" class="hidden peer">
                            <div class="py-8 rounded-2xl border-2 border-gray-50 bg-gray-50 text-center transition-all peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:shadow-lg">
                                <i class="fas fa-times-circle text-4xl mb-3" :class="tfAnswer === 'false' ? 'text-red-500' : 'text-gray-200'"></i>
                                <div class="font-bold text-gray-700">SALAH</div>
                            </div>
                        </label>
                        <input type="hidden" name="tf_options[0][label]" value="T" :disabled="questionType !== 'true_false'">
                        <input type="hidden" name="tf_options[0][text]" value="Benar" :disabled="questionType !== 'true_false'">
                        <input type="hidden" name="tf_options[0][is_correct]" :value="tfAnswer === 'true' ? 1 : 0" :disabled="questionType !== 'true_false'">
                        <input type="hidden" name="tf_options[1][label]" value="F" :disabled="questionType !== 'true_false'">
                        <input type="hidden" name="tf_options[1][text]" value="Salah" :disabled="questionType !== 'true_false'">
                        <input type="hidden" name="tf_options[1][is_correct]" :value="tfAnswer === 'false' ? 1 : 0" :disabled="questionType !== 'true_false'">
                    </div>

                    {{-- Essay / Fill Blank AREA --}}
                    <div x-show="questionType === 'essay' || questionType === 'fill_blank'">
                        <label class="block text-base font-semibold text-gray-800 uppercase tracking-wider mb-3" x-text="questionType === 'essay' ? 'KUNCI JAWABAN / INDIKATOR NILAI' : 'KUNCI JAWABAN SINGKAT'"></label>
                        <textarea name="answer_key" rows="4" :disabled="questionType !== 'essay' && questionType !== 'fill_blank'"
                            class="w-full border-none bg-amber-50/30 rounded-2xl focus:ring-2 focus:ring-amber-500/20 p-5 text-base font-bold text-gray-700"
                            placeholder="Tuliskan jawaban yang benar..."></textarea>
                    </div>
                </div>
            </div>

            {{-- 4. PENGATURAN LAINNYA --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-base font-semibold text-gray-800 uppercase tracking-wider mb-2">Tingkat Kesulitan</label>
                    <select name="difficulty" class="w-full bg-gray-50 border-none rounded-xl text-base font-bold text-gray-700 focus:ring-2 focus:ring-emerald-500/20 py-3">
                        <option value="mudah" {{ old('difficulty') == 'mudah' ? 'selected' : '' }}>Mudah</option>
                        <option value="sedang" {{ old('difficulty') == 'sedang' || !old('difficulty') ? 'selected' : '' }}>Sedang</option>
                        <option value="sulit" {{ old('difficulty') == 'sulit' ? 'selected' : '' }}>Sulit</option>
                    </select>
                </div>
                <div>
                    <label class="block text-base font-semibold text-gray-800 uppercase tracking-wider mb-2">Poin Soal</label>
                    <input type="number" name="points" value="{{ old('points', 1) }}" min="1" class="w-full bg-gray-50 border-none rounded-xl text-base font-bold text-gray-700 focus:ring-2 focus:ring-emerald-500/20 py-3">
                </div>
                <div>
                    <label class="block text-base font-semibold text-gray-800 uppercase tracking-wider mb-2">Pembahasan (Opsional)</label>
                    <input type="text" name="explanation" value="{{ old('explanation') }}" class="w-full bg-gray-50 border-none rounded-xl text-base font-bold text-gray-700 focus:ring-2 focus:ring-emerald-500/20 py-3 px-4" placeholder="Ketik penjelasan...">
                </div>
            </div>

            {{-- 5. ACTIONS --}}
            <div class="flex flex-col md:flex-row gap-4">
                <button type="submit" class="flex-1 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-base shadow-lg shadow-emerald-100 hover:shadow-emerald-200 transition-all flex items-center justify-center gap-3">
                    <i class="fas fa-save"></i> SIMPAN SOAL SEKARANG
                </button>
                <a href="{{ route('guru.cbt.banks.show', $bank) }}" class="md:w-48 py-4 bg-white border-2 border-gray-200 text-gray-800 hover:text-gray-800 hover:border-gray-200 rounded-xl font-bold text-base transition-all flex items-center justify-center">
                    BATAL
                </a>
            </div>
        </div>
    </form>

    <div class="py-10 text-center">
        <p class="text-base font-semibold text-gray-500 uppercase tracking-wider">&copy; 2026 PembdaHUB &bull; Education System</p>
    </div>
</div>
@endsection

@push('scripts')
{{-- Load Alpine.js if not already present in layout --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
function questionForm() {
    return {
        questionType: '{{ old('question_type', 'multiple_choice') }}',
        tfAnswer: '{{ old('tf_answer') }}',
        options: {!! json_encode(old('options') ?: [['text' => '', 'is_correct' => false], ['text' => '', 'is_correct' => false], ['text' => '', 'is_correct' => false], ['text' => '', 'is_correct' => false]]) !!},
        addOption() { 
            if(this.options.length < 5) {
                this.options.push({text: '', is_correct: false});
            }
        },
        setCorrect(idx) { 
            this.options.forEach((o, i) => o.is_correct = (i === idx));
        },
    }
}
</script>
@endpush


