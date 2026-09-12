@extends('layouts.admin')
@section('title', 'Buat Ujian Sekolah')
@section('content')
<div class="space-y-6" x-data="examForm()">
    <div class="mb-8 flex items-center gap-4">
        <a href="{{ route('admin.cbt.index') }}" class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-800 hover:bg-gray-200"><i class="fas fa-arrow-left"></i></a>
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white"><i class="fas fa-school text-xl"></i></div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Buat Ujian Sekolah</h1>
                <p class="text-gray-800 mt-1">UAS, UTS, Test Masuk, Ujian Khusus — dikelola oleh Admin</p>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-xl">
        <div class="flex items-center"><i class="fas fa-exclamation-circle text-red-500 mr-2"></i><span class="text-red-800 font-semibold">Terdapat kesalahan:</span></div>
        <ul class="mt-2 text-base text-red-700 list-disc list-inside">
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.cbt.exams.store') }}" method="POST">
        @csrf

        {{-- Info Dasar --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 mb-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100">
                    <i class="fas fa-info-circle text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 leading-none">Informasi Dasar</h2>
                    <p class="text-gray-800 text-base mt-1 uppercase tracking-widest font-bold">Detail utama ujian sekolah</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-2">Judul Ujian <span class="text-red-500">*</span></label>
                    <input type="text" name="exam_title" value="{{ old('exam_title') }}" 
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 px-5 py-4 text-gray-800 font-bold transition-all text-lg" 
                        required placeholder="Contoh: UAS Ganjil Matematika Kelas 9">
                </div>

                <div>
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-2">Sekolah <span class="text-red-500">*</span></label>
                    @if($isSuperAdmin)
                    <select name="school_id" x-model="selectedSchoolId" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 px-5 py-3.5 text-gray-800 font-bold transition-all" required>
                        <option value="">-- Pilih Sekolah --</option>
                        @foreach($schools as $school)
                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                        @endforeach
                    </select>
                    @else
                    <input type="hidden" name="school_id" value="{{ $userSchoolId }}">
                    <div class="w-full rounded-xl border-gray-200 bg-gray-100 px-5 py-3.5 text-gray-700 font-bold border-dashed border-2">{{ $schools->firstWhere('id', $userSchoolId)?->name ?? '-' }}</div>
                    @endif
                </div>

                <div>
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-2">Mata Pelajaran <span class="text-red-500">*</span></label>
                    <select name="subject_id" x-model="selectedSubjectId" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 px-5 py-3.5 text-gray-800 font-bold transition-all" required :disabled="!selectedSchoolId">
                        <option value="">-- Pilih Mapel --</option>
                        <template x-for="subject in filteredSubjects" :key="subject.id">
                            <option :value="subject.id" x-text="subject.name"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-2">Tipe Ujian <span class="text-red-500">*</span></label>
                    <select name="exam_type" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 px-5 py-3.5 text-gray-800 font-bold transition-all" required>
                        <option value="">Pilih Tipe</option>
                        @foreach($examTypes as $key => $label)
                        <option value="{{ $key }}" {{ old('exam_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-2">Durasi (menit) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 90) }}" 
                            class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 px-5 py-3.5 text-gray-800 font-bold transition-all">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-base font-bold text-gray-500 uppercase tracking-widest">Menit</div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-2">Deskripsi</label>
                    <textarea name="exam_description" rows="2" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 px-5 py-4 text-gray-800 font-medium transition-all" placeholder="Deskripsi opsional...">{{ old('exam_description') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Jadwal & Nilai --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 mb-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-sm border border-blue-100">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 leading-none">Jadwal & Penilaian</h2>
                    <p class="text-gray-800 text-base mt-1 uppercase tracking-widest font-bold">Pengaturan waktu dan ambang batas nilai</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-2">Waktu Mulai</label>
                    <input type="datetime-local" name="start_time" value="{{ old('start_time') }}" 
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 px-5 py-3.5 text-gray-800 font-bold transition-all text-base">
                </div>
                <div>
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-2">Waktu Selesai</label>
                    <input type="datetime-local" name="end_time" value="{{ old('end_time') }}" 
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 px-5 py-3.5 text-gray-800 font-bold transition-all text-base">
                </div>
                <div>
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-2">KKM <span class="text-red-500">*</span></label>
                    <input type="number" name="passing_score" value="{{ old('passing_score', 70) }}" min="0" max="100" 
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 px-5 py-3.5 text-gray-800 font-bold transition-all">
                </div>
                <div>
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-2">Maks Percobaan <span class="text-red-500">*</span></label>
                    <input type="number" name="max_attempts" value="{{ old('max_attempts', 1) }}" min="1" 
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 px-5 py-3.5 text-gray-800 font-bold transition-all">
                </div>
            </div>
        </div>

        {{-- Bank Soal --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 mb-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 flex items-center justify-center text-purple-600 shadow-sm border border-purple-100">
                    <i class="fas fa-database text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 leading-none">Pilih Konten Bank Soal</h2>
                    <p class="text-gray-800 text-base mt-1 uppercase tracking-widest font-bold">Ambil butir soal dari database yang tersedia</p>
                </div>
            </div>

            <template x-if="!selectedSchoolId">
                <div class="p-12 text-center border-4 border-dashed border-gray-50 rounded-2xl">
                    <div class="w-16 h-16 rounded-2xl bg-amber-50 flex items-center justify-center mx-auto mb-4 text-amber-400 border border-amber-100 shadow-sm">
                        <i class="fas fa-lock text-2xl"></i>
                    </div>
                    <p class="text-gray-800 font-bold text-base uppercase tracking-widest">Pilih sekolah terlebih dahulu untuk membuka Bank Soal</p>
                </div>
            </template>

            <template x-if="selectedSchoolId">
                <div class="space-y-4">
                    <template x-for="(item, index) in selectedBanks" :key="index">
                        <div class="p-5 bg-gray-50 rounded-2xl border border-gray-200 relative group transition-all"
                             :class="item.bank_id ? 'border-indigo-200 bg-indigo-50/30' : ''">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                <div class="md:col-span-8">
                                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-2">Pilih Bank Soal</label>
                                    <select :name="'question_banks[' + index + '][bank_id]'" x-model="item.bank_id" 
                                        class="w-full rounded-xl border-gray-200 bg-white focus:ring-2 focus:ring-indigo-500 font-bold text-base py-3 px-4">
                                        <option value="">-- Pilih Bank Soal --</option>
                                        <template x-for="bank in filteredBanks" :key="bank.id">
                                            <option :value="bank.id" x-text="bank.name + ' (' + bank.subject_name + ' \u2022 Kls ' + bank.grade + ' \u2022 ' + bank.total + ' soal)'"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-2">Jumlah Soal</label>
                                    <div class="relative">
                                        <input type="number" :name="'question_banks[' + index + '][questions_to_pick]'" 
                                            x-model.number="item.questions_to_pick" min="1"
                                            class="w-full rounded-xl border-gray-200 bg-white focus:ring-2 focus:ring-indigo-500 font-bold text-center py-3">
                                        <div class="absolute right-3 top-1/2 -translate-y-1/2 text-base font-bold text-gray-500 uppercase">Butir</div>
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
                    <button type="button" @click="selectedBanks.push({ bank_id: '', questions_to_pick: 10 })" 
                        class="w-full py-4 border-2 border-dashed border-gray-200 rounded-2xl text-gray-800 font-bold text-base uppercase tracking-widest hover:border-indigo-200 hover:text-indigo-500 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-plus-circle"></i> Tambah Bank Soal Lainnya
                    </button>
                </div>
            </template>
            @error('question_banks') <p class="text-red-500 text-base mt-2 font-bold">{{ $message }}</p> @enderror
        </div>

        {{-- Kelas Peserta --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 mb-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-sm border border-emerald-100">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 leading-none">Kelas Peserta</h2>
                    <p class="text-gray-800 text-base mt-1 uppercase tracking-widest font-bold">Pilih rombongan belajar yang mengikuti ujian</p>
                </div>
            </div>

            <template x-if="!selectedSchoolId">
                <div class="p-8 text-center bg-gray-50 rounded-2xl opacity-50">
                    <p class="text-gray-800 font-bold text-base uppercase tracking-widest">Daftar kelas akan muncul setelah sekolah dipilih</p>
                </div>
            </template>

            <template x-if="selectedSchoolId">
                <div class="space-y-6">
                    <template x-for="gradeGroup in groupedClassrooms" :key="gradeGroup.grade">
                        <div class="p-6 rounded-2xl border transition-all duration-300"
                             :class="selectedGradeLevels.length > 0 && !selectedGradeLevels.includes(gradeGroup.grade.toString()) ? 'bg-gray-50/50 border-gray-200 opacity-50 grayscale' : 'bg-white border-indigo-100 shadow-sm'">
                            <div class="flex items-center gap-4 mb-5">
                                <span class="px-4 py-1.5 rounded-xl text-base font-bold uppercase tracking-widest transition-colors"
                                      :class="selectedGradeLevels.includes(gradeGroup.grade.toString()) ? 'bg-indigo-600 text-white' : 'bg-indigo-100 text-indigo-700'">Kelas <span x-text="gradeGroup.grade"></span></span>
                                <label class="text-base font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:text-indigo-600 flex items-center gap-1.5"
                                       x-show="selectedGradeLevels.length === 0 || selectedGradeLevels.includes(gradeGroup.grade.toString())">
                                    <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500" @change="toggleGrade(gradeGroup.grade, $event.target.checked)">
                                    <span>Pilih Semua</span>
                                </label>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                                <template x-for="cr in gradeGroup.items" :key="cr.id">
                                    <label class="flex items-center gap-3 p-3 bg-white rounded-xl border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 cursor-pointer transition"
                                           :class="selectedGradeLevels.length > 0 && !selectedGradeLevels.includes(gradeGroup.grade.toString()) ? 'opacity-50 pointer-events-none' : ''">
                                        <input type="checkbox" name="classrooms[]" :value="cr.id" class="rounded text-indigo-600 focus:ring-indigo-500" :class="'dyn-grade-' + gradeGroup.grade">
                                        <span class="text-base text-gray-700 font-bold" x-text="cr.name"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
            @error('classrooms') <p class="text-red-500 text-base mt-2 font-bold">{{ $message }}</p> @enderror
        </div>

        {{-- Pengaturan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 mb-8">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600 shadow-sm border border-rose-100">
                    <i class="fas fa-shield-alt text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 leading-none">Keamanan & Tampilan</h2>
                    <p class="text-gray-800 text-base mt-1 uppercase tracking-widest font-bold">Proteksi anti-curang dan visibilitas hasil</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <div class="space-y-4">
                    <h3 class="text-base font-semibold text-gray-700 uppercase tracking-wider mb-4">Fitur Keamanan</h3>
                    @php $securityOptions = [
                        ['randomize_questions', 'Acak urutan soal', true],
                        ['randomize_options', 'Acak urutan pilihan jawaban', true],
                        ['prevent_tab_switch', 'Deteksi pindah tab', true],
                        ['prevent_copy_paste', 'Blokir copy-paste', true],
                    ]; @endphp
                    @foreach($securityOptions as [$name, $label, $default])
                    <label class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl border border-gray-200 cursor-pointer hover:bg-indigo-50 hover:border-indigo-200 transition-all group">
                        <input type="hidden" name="{{ $name }}" value="0">
                        <input type="checkbox" name="{{ $name }}" value="1" class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" {{ old($name, $default) ? 'checked' : '' }}>
                        <span class="text-base text-gray-700 font-bold group-hover:text-indigo-700 transition-colors">{{ $label }}</span>
                    </label>
                    @endforeach
                    <div class="pt-4">
                        <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-2">Kode Akses Ujian (Opsional)</label>
                        <input type="text" name="access_code" value="{{ old('access_code') }}" placeholder="Contoh: TOKEN123" 
                            class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 px-5 py-3.5 text-gray-800 font-bold uppercase placeholder-gray-300">
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-base font-semibold text-gray-700 uppercase tracking-wider mb-4">Aturan Tampilan</h3>
                    @php $displayOptions = [
                        ['show_result', 'Tampilkan hasil ke siswa', true],
                        ['show_answer_key', 'Tampilkan kunci jawaban (setelah selesai)', false],
                        ['allow_review', 'Izinkan review jawaban (setelah selesai)', true],
                        ['auto_sync_grade', 'Sinkron otomatis ke nilai rapor', false],
                    ]; @endphp
                    @foreach($displayOptions as [$name, $label, $default])
                    <label class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl border border-gray-200 cursor-pointer hover:bg-indigo-50 hover:border-indigo-200 transition-all group">
                        <input type="hidden" name="{{ $name }}" value="0">
                        <input type="checkbox" name="{{ $name }}" value="1" class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" {{ old($name, $default) ? 'checked' : '' }}>
                        <span class="text-base text-gray-700 font-bold group-hover:text-indigo-700 transition-colors">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-4 p-4 bg-gray-100/50 rounded-2xl">
            <a href="{{ route('admin.cbt.index') }}" class="px-8 py-4 bg-white border border-gray-200 text-gray-800 rounded-xl hover:bg-gray-50 transition font-bold text-base uppercase tracking-widest">Batalkan</a>
            <button type="submit" class="px-12 py-4 bg-gradient-to-r from-indigo-600 to-purple-700 text-white rounded-xl hover:shadow-2xl transition font-bold text-base uppercase tracking-widest shadow-xl shadow-indigo-200">
                <i class="fas fa-save mr-2"></i> Simpan Ujian Sekolah
            </button>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script>
function examForm() {
    const allSubjects = @json($subjectsJson);
    const allBanks = @json($banksJson);
    const allClassrooms = @json($classroomsJson);

    return {
        selectedBanks: [{ bank_id: '', questions_to_pick: 10 }],
        selectedSchoolId: '{{ old("school_id", $userSchoolId ?? "") }}',
        selectedSubjectId: '{{ old("subject_id", "") }}',

        init() {
            this.$watch('selectedSchoolId', (val) => {
                this.selectedSubjectId = '';
                this.selectedBanks = [{ bank_id: '', questions_to_pick: 10 }];
            });
        },

        get filteredSubjects() {
            if (!this.selectedSchoolId) return [];
            return allSubjects.filter(s => s.school_id == this.selectedSchoolId);
        },

        get filteredBanks() {
            if (!this.selectedSchoolId) return [];
            const bySchool = allBanks.filter(b => b.school_id == this.selectedSchoolId);
            if (this.selectedSubjectId) {
                const bySubject = bySchool.filter(b => b.subject_id == this.selectedSubjectId);
                if (bySubject.length > 0) return bySubject;
            }
            return bySchool;
        },

        get filteredClassrooms() {
            if (!this.selectedSchoolId) return [];
            return allClassrooms.filter(c => c.school_id == this.selectedSchoolId);
        },

        get groupedClassrooms() {
            const groups = {};
            this.filteredClassrooms.forEach(c => {
                if (!groups[c.grade]) groups[c.grade] = { grade: c.grade, items: [] };
                groups[c.grade].items.push(c);
            });
            return Object.values(groups).sort((a, b) => a.grade - b.grade);
        },

        get selectedGradeLevels() {
            let levels = [];
            this.selectedBanks.forEach(item => {
                if (item.bank_id) {
                    const bank = allBanks.find(b => b.id == item.bank_id);
                    if (bank && !levels.includes(bank.grade.toString())) {
                        levels.push(bank.grade.toString());
                    }
                }
            });
            return levels;
        },

        toggleGrade(grade, checked) {
            document.querySelectorAll('.dyn-grade-' + grade).forEach(cb => {
                if (!cb.disabled) cb.checked = checked;
            });
        }
    }
}
</script>
@endpush

