@extends('layouts.guru')
@section('title', 'Buat Ujian CBT')
@section('content')
<div class="space-y-8" x-data="examForm({{ $banks->toJson() }}, {{ $subjects->map(fn($s) => ['id' => $s->id, 'name' => $s->subject_name ?? $s->name])->toJson() }})">
    {{-- Hero Header --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700 rounded-2xl p-8 text-white">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        <div class="relative flex items-center gap-5">
            <a href="{{ route('guru.cbt.exams.index') }}" class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center hover:bg-white/25 transition border border-gray-200">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Buat Ujian CBT</h1>
                <p class="text-emerald-50 mt-1 text-base">Atur detail ujian online baru</p>
            </div>
        </div>
    </div>

    <form action="{{ route('guru.cbt.exams.store') }}" method="POST">
        @csrf

        {{-- Info Dasar --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center"><i class="fas fa-info-circle text-emerald-600"></i></div>
                <h2 class="text-lg font-bold text-gray-900">Informasi Dasar</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Judul Ujian <span class="text-red-500">*</span></label>
                    <input type="text" name="exam_title" value="{{ old('exam_title') }}" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-4 py-2.5 text-gray-800" required placeholder="Contoh: UTS Matematika Kelas 10">
                    @error('exam_title') <p class="text-red-500 text-base mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Mata Pelajaran <span class="text-red-500">*</span></label>
                    <select name="subject_id" x-model="selectedSubjectId" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-4 py-2.5 text-gray-800" required>
                        <option value="">Pilih Mata Pelajaran</option>
                        @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->subject_name ?? $subject->name }}</option>
                        @endforeach
                    </select>
                    @error('subject_id') <p class="text-red-500 text-base mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Tipe Ujian <span class="text-red-500">*</span></label>
                    <select name="exam_type" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-4 py-2.5 text-gray-800" required>
                        <option value="">Pilih Tipe</option>
                        @foreach(\App\Models\CbtExam::getClassScopeTypes() as $key => $label)
                        <option value="{{ $key }}" {{ old('exam_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('exam_type') <p class="text-red-500 text-base mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Durasi (menit) <span class="text-red-500">*</span></label>
                    <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 60) }}" min="5" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-4 py-2.5 text-gray-800" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Deskripsi</label>
                    <textarea name="exam_description" rows="2" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-4 py-2.5 text-gray-800" placeholder="Deskripsi ujian (opsional)...">{{ old('exam_description') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Jadwal & Nilai --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center"><i class="fas fa-clock text-blue-600"></i></div>
                <h2 class="text-lg font-bold text-gray-900">Jadwal & Penilaian</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                <div>
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Waktu Mulai</label>
                    <input type="datetime-local" name="start_time" value="{{ old('start_time') }}" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-4 py-2.5 text-gray-800">
                </div>
                <div>
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Waktu Selesai</label>
                    <input type="datetime-local" name="end_time" value="{{ old('end_time') }}" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-4 py-2.5 text-gray-800">
                </div>
                <div>
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-1.5">KKM <span class="text-red-500">*</span></label>
                    <input type="number" name="passing_score" value="{{ old('passing_score', 70) }}" min="0" max="100" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-4 py-2.5 text-gray-800" required>
                </div>
                <div>
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Maks Percobaan <span class="text-red-500">*</span></label>
                    <input type="number" name="max_attempts" value="{{ old('max_attempts', 1) }}" min="1" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-4 py-2.5 text-gray-800" required>
                </div>
            </div>
        </div>

        {{-- Bank Soal --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center"><i class="fas fa-database text-purple-600"></i></div>
                <h2 class="text-lg font-bold text-gray-900">Pilih Bank Soal</h2>
            </div>
            <div class="space-y-4">
                <template x-for="(item, index) in selectedBanks" :key="index">
                    <div class="p-5 bg-gray-50 rounded-2xl border border-gray-200 relative group transition-all"
                         :class="item.bank_id ? 'border-emerald-200 bg-emerald-50/30' : ''">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                            <div class="md:col-span-8">
                                <label class="block text-base font-semibold text-gray-800 uppercase tracking-wider mb-2">Pilih Bank Soal</label>
                                <select :name="'question_banks[' + index + '][bank_id]'" x-model="item.bank_id" 
                                    class="w-full rounded-xl border-gray-200 bg-white focus:ring-2 focus:ring-emerald-500 font-semibold text-base py-3 px-4">
                                    <option value="">-- Pilih Bank Soal --</option>
                                    @foreach($banks as $bank)
                                    <template x-if="isBankVisible({{ $bank->id }})">
                                        <option value="{{ $bank->id }}">
                                            {{ $bank->bank_name }} ({{ $bank->subject?->name ?? 'Mapel' }} &bull; Kls {{ $bank->grade_level }} &bull; {{ $bank->total_questions }} soal)
                                        </option>
                                    </template>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-base font-semibold text-gray-800 uppercase tracking-wider mb-2">Jumlah Soal</label>
                                <div class="relative">
                                    <input type="number" :name="'question_banks[' + index + '][questions_to_pick]'" 
                                        x-model.number="item.questions_to_pick" min="1"
                                        class="w-full rounded-xl border-gray-200 bg-white focus:ring-2 focus:ring-emerald-500 font-bold text-center py-3">
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2 text-base font-semibold text-gray-500 uppercase">Butir</div>
                                </div>
                            </div>
                            <div class="md:col-span-1 flex justify-end pt-5 md:pt-0">
                                <button type="button" @click="selectedBanks.splice(index, 1)" x-show="selectedBanks.length > 1"
                                    class="w-10 h-10 rounded-xl bg-white border border-gray-200 text-red-400 hover:text-red-600 hover:bg-red-50 transition-all shadow-sm">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                <span>* Menampilkan bank soal untuk mata pelajaran yang dipilih dan bank soal bersama sekolah.</span>
                <button type="button" @click="showAllBanks = !showAllBanks" class="text-emerald-600 hover:text-emerald-800 font-semibold underline">
                    <span x-show="!showAllBanks">Tampilkan Semua Bank Soal Sekolah</span>
                    <span x-show="showAllBanks">Hanya Tampilkan Mapel Terpilih</span>
                </button>
            </div>

            <button type="button" @click="selectedBanks.push({ bank_id: '', questions_to_pick: 10 })" 
                class="mt-4 w-full py-3 border-2 border-dashed border-gray-200 rounded-xl text-gray-800 font-semibold text-base uppercase tracking-wider hover:border-emerald-200 hover:text-emerald-500 transition-all flex items-center justify-center gap-2">
                <i class="fas fa-plus-circle"></i> Tambah Koleksi Bank Soal Lainnya
            </button>
            @error('question_banks') <p class="text-red-500 text-base mt-2 font-bold">{{ $message }}</p> @enderror
        </div>

        {{-- Kelas Peserta --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center"><i class="fas fa-users text-teal-600"></i></div>
                <h2 class="text-lg font-bold text-gray-900">Kelas Peserta</h2>
            </div>
            <p class="text-base text-gray-700 mb-4">Pilih kelas yang akan mengikuti ujian di {{ $teacher->school->name ?? 'sekolah Anda' }}</p>
            @foreach($classrooms->groupBy('grade_level') as $grade => $gradeClassrooms)
            <div class="mb-5 p-4 rounded-xl border transition-all duration-300"
                 :class="selectedGradeLevels.length > 0 && !selectedGradeLevels.includes('{{ $grade }}') ? 'bg-gray-50/50 border-gray-200 opacity-50 grayscale' : 'bg-white border-emerald-100 shadow-sm'">
                <div class="flex items-center gap-3 mb-3">
                    <span class="px-3 py-1 rounded-lg text-base font-bold transition-colors"
                          :class="selectedGradeLevels.includes('{{ $grade }}') ? 'bg-emerald-600 text-white' : 'bg-emerald-100 text-emerald-700'">Kelas {{ $grade }}</span>
                    <label class="text-base text-gray-700 cursor-pointer hover:text-emerald-600 flex items-center gap-1">
                        <input type="checkbox" class="rounded text-emerald-600 focus:ring-emerald-500 grade-toggle" data-grade="{{ $grade }}" :disabled="selectedGradeLevels.length > 0 && !selectedGradeLevels.includes('{{ $grade }}')">
                        <span>Pilih semua</span>
                    </label>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2">
                    @foreach($gradeClassrooms as $classroom)
                    <label class="flex items-center gap-2 p-2.5 bg-white rounded-xl border border-gray-200 hover:border-emerald-300 hover:bg-emerald-50 cursor-pointer transition"
                           :class="selectedGradeLevels.length > 0 && !selectedGradeLevels.includes('{{ $grade }}') ? 'opacity-50 pointer-events-none' : ''">
                        <input type="checkbox" name="classrooms[]" value="{{ $classroom->id }}" class="rounded text-emerald-600 focus:ring-emerald-500 classroom-check grade-{{ $grade }}" {{ in_array($classroom->id, old('classrooms', [])) ? 'checked' : '' }} :disabled="selectedGradeLevels.length > 0 && !selectedGradeLevels.includes('{{ $grade }}')">
                        <span class="text-base text-gray-700 font-medium">{{ $classroom->class_name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
            @error('classrooms') <p class="text-red-500 text-base mt-2">{{ $message }}</p> @enderror
        </div>

        {{-- Pengaturan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center"><i class="fas fa-shield-alt text-indigo-600"></i></div>
                <h2 class="text-lg font-bold text-gray-900">Pengaturan</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <h3 class="text-base font-bold text-gray-700 uppercase tracking-wider">Keamanan</h3>
                    @php $securityOptions = [
                        ['randomize_questions', 'Acak urutan soal', true],
                        ['randomize_options', 'Acak urutan pilihan jawaban', true],
                        ['prevent_tab_switch', 'Deteksi pindah tab', true],
                        ['prevent_copy_paste', 'Blokir copy-paste', true],
                    ]; @endphp
                    @foreach($securityOptions as [$name, $label, $default])
                    <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer hover:bg-emerald-50 hover:border-emerald-200 transition">
                        <input type="hidden" name="{{ $name }}" value="0">
                        <input type="checkbox" name="{{ $name }}" value="1" class="rounded text-emerald-600 focus:ring-emerald-500" {{ old($name, $default) ? 'checked' : '' }}>
                        <span class="text-base text-gray-700 font-medium">{{ $label }}</span>
                    </label>
                    @endforeach
                    <div class="mt-4">
                        <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Kode Akses (opsional)</label>
                        <input type="text" name="access_code" value="{{ old('access_code') }}" placeholder="Kosongkan jika tidak perlu" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-4 py-2.5 text-gray-800">
                    </div>
                </div>
                <div class="space-y-4">
                    <h3 class="text-base font-bold text-gray-700 uppercase tracking-wider">Tampilan Hasil</h3>
                    @php $displayOptions = [
                        ['show_result', 'Tampilkan hasil ke siswa', true],
                        ['show_answer_key', 'Tampilkan kunci jawaban', false],
                        ['allow_review', 'Izinkan review jawaban', true],
                        ['auto_sync_grade', 'Sinkron otomatis ke nilai rapor', false],
                    ]; @endphp
                    @foreach($displayOptions as [$name, $label, $default])
                    <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer hover:bg-emerald-50 hover:border-emerald-200 transition">
                        <input type="hidden" name="{{ $name }}" value="0">
                        <input type="checkbox" name="{{ $name }}" value="1" class="rounded text-emerald-600 focus:ring-emerald-500" {{ old($name, $default) ? 'checked' : '' }}>
                        <span class="text-base text-gray-700 font-medium">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('guru.cbt.exams.index') }}" class="px-6 py-3 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition font-medium">Batal</a>
            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl hover:shadow-lg transition font-semibold shadow-sm">
                <i class="fas fa-save mr-2"></i>Simpan Ujian
            </button>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script>
function examForm(banksData, subjectsData) {
    return {
        allBanks: banksData,
        allSubjects: subjectsData || [],
        selectedBanks: [{ bank_id: '', questions_to_pick: 10 }],
        selectedSubjectId: '{{ old('subject_id') }}',
        showAllBanks: false,
        
        isBankVisible(bankId) {
            if (this.showAllBanks || !this.selectedSubjectId) return true;
            const bank = this.allBanks.find(b => b.id == bankId);
            if (!bank) return false;
            // 1. Direct subject ID match
            if (bank.subject_id == this.selectedSubjectId) return true;
            // 2. Name-based match fallback
            const curSubject = this.allSubjects.find(s => s.id == this.selectedSubjectId);
            if (curSubject && bank.subject_name && curSubject.name) {
                const s1 = curSubject.name.toLowerCase().trim();
                const s2 = bank.subject_name.toLowerCase().trim();
                if (s1 === s2 || s1.includes(s2) || s2.includes(s1)) return true;
            }
            return false;
        },

        get selectedGradeLevels() {
            let levels = [];
            this.selectedBanks.forEach(item => {
                if (item.bank_id) {
                    const bank = this.allBanks.find(b => b.id == item.bank_id);
                    if (bank && !levels.includes(bank.grade_level.toString())) {
                        levels.push(bank.grade_level.toString());
                    }
                }
            });
            return levels;
        }
    }
}
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.grade-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const grade = this.dataset.grade;
            document.querySelectorAll('.grade-' + grade).forEach(cb => {
                if (!cb.disabled) cb.checked = this.checked;
            });
        });
    });
});
</script>
@endpush
