@extends('layouts.guru')
@section('title', 'Edit Ujian CBT')
@section('content')
<div class="space-y-8" x-data="examEditForm()">
    {{-- Hero Header --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700 rounded-2xl p-8 text-white">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        <div class="relative flex items-center gap-5">
            <a href="{{ route('guru.cbt.exams.show', $exam) }}" class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center hover:bg-white/25 transition border border-gray-200">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Edit Ujian CBT</h1>
                <p class="text-emerald-50 mt-1 text-base">{{ $exam->exam_title }}</p>
            </div>
        </div>
    </div>

    <form action="{{ route('guru.cbt.exams.update', $exam) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Info Dasar --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center"><i class="fas fa-info-circle text-emerald-600"></i></div>
                <h2 class="text-lg font-bold text-gray-900">Informasi Dasar</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Judul Ujian <span class="text-red-500">*</span></label>
                    <input type="text" name="exam_title" value="{{ old('exam_title', $exam->exam_title) }}" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-4 py-2.5 text-gray-800" required>
                    @error('exam_title') <p class="text-red-500 text-base mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Mata Pelajaran <span class="text-red-500">*</span></label>
                    <select name="subject_id" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-4 py-2.5 text-gray-800" required>
                        <option value="">Pilih Mata Pelajaran</option>
                        @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ old('subject_id', $exam->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->subject_name ?? $subject->name }}</option>
                        @endforeach
                    </select>
                    @error('subject_id') <p class="text-red-500 text-base mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Tipe Ujian <span class="text-red-500">*</span></label>
                    <select name="exam_type" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-4 py-2.5 text-gray-800" required>
                        <option value="">Pilih Tipe</option>
                        @foreach(\App\Models\CbtExam::getClassScopeTypes() as $key => $label)
                        <option value="{{ $key }}" {{ old('exam_type', $exam->exam_type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('exam_type') <p class="text-red-500 text-base mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Durasi (menit) <span class="text-red-500">*</span></label>
                    <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes) }}" min="5" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-4 py-2.5 text-gray-800" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Deskripsi</label>
                    <textarea name="exam_description" rows="2" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-4 py-2.5 text-gray-800">{{ old('exam_description', $exam->exam_description) }}</textarea>
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
                    <input type="datetime-local" name="start_time" value="{{ old('start_time', $exam->start_time?->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-4 py-2.5 text-gray-800">
                </div>
                <div>
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Waktu Selesai</label>
                    <input type="datetime-local" name="end_time" value="{{ old('end_time', $exam->end_time?->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-4 py-2.5 text-gray-800">
                </div>
                <div>
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-1.5">KKM <span class="text-red-500">*</span></label>
                    <input type="number" name="passing_score" value="{{ old('passing_score', $exam->passing_score) }}" min="0" max="100" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-4 py-2.5 text-gray-800" required>
                </div>
                <div>
                    <label class="block text-base font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Maks Percobaan <span class="text-red-500">*</span></label>
                    <input type="number" name="max_attempts" value="{{ old('max_attempts', $exam->max_attempts) }}" min="1" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-4 py-2.5 text-gray-800" required>
                </div>
            </div>
        </div>

        {{-- Bank Soal --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center"><i class="fas fa-database text-purple-600"></i></div>
                <h2 class="text-lg font-bold text-gray-900">Pilih Bank Soal</h2>
            </div>
            <template x-for="(item, index) in selectedBanks" :key="index">
                <div class="flex items-center gap-4 mb-4 p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <div class="flex-1">
                        <select :name="'question_banks[' + index + '][bank_id]'" x-model="item.bank_id" class="w-full rounded-xl border-gray-200 bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-4 py-2.5 text-gray-800 text-base" required>
                            <option value="">Pilih Bank Soal</option>
                            @foreach($banks as $bank)
                            <option value="{{ $bank->id }}">{{ $bank->bank_name }} ({{ $bank->total_questions }} soal)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-36">
                        <input type="number" :name="'question_banks[' + index + '][questions_to_pick]'" placeholder="Jumlah" min="1" x-model.number="item.questions_to_pick" class="w-full rounded-xl border-gray-200 bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-4 py-2.5 text-gray-800 text-base" required>
                    </div>
                    <button type="button" @click="selectedBanks.splice(index, 1)" class="w-10 h-10 rounded-lg bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-100 transition" x-show="selectedBanks.length > 1">
                        <i class="fas fa-trash text-base"></i>
                    </button>
                </div>
            </template>
            <button type="button" @click="selectedBanks.push({ bank_id: '', questions_to_pick: 10 })" class="inline-flex items-center text-base text-emerald-600 hover:text-emerald-800 font-semibold mt-1">
                <i class="fas fa-plus-circle mr-1.5"></i> Tambah Bank Soal
            </button>
            @error('question_banks') <p class="text-red-500 text-base mt-2">{{ $message }}</p> @enderror
        </div>

        {{-- Kelas Peserta --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center"><i class="fas fa-users text-teal-600"></i></div>
                <h2 class="text-lg font-bold text-gray-900">Kelas Peserta</h2>
            </div>
            <p class="text-base text-gray-700 mb-4">Pilih kelas yang akan mengikuti ujian di {{ $teacher->school->name ?? 'sekolah Anda' }}</p>
            @foreach($classrooms->groupBy('grade_level') as $grade => $gradeClassrooms)
            <div class="mb-5 p-4 bg-gray-50 rounded-xl border border-gray-200">
                <div class="flex items-center gap-3 mb-3">
                    <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-base font-bold">Kelas {{ $grade }}</span>
                    <label class="text-base text-gray-700 cursor-pointer hover:text-emerald-600 flex items-center gap-1">
                        <input type="checkbox" class="rounded text-emerald-600 focus:ring-emerald-500 grade-toggle" data-grade="{{ $grade }}">
                        <span>Pilih semua</span>
                    </label>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2">
                    @foreach($gradeClassrooms as $classroom)
                    <label class="flex items-center gap-2 p-2.5 bg-white rounded-xl border border-gray-200 hover:border-emerald-300 hover:bg-emerald-50 cursor-pointer transition">
                        <input type="checkbox" name="classrooms[]" value="{{ $classroom->id }}" class="rounded text-emerald-600 focus:ring-emerald-500 classroom-check grade-{{ $grade }}" {{ in_array($classroom->id, old('classrooms', $selectedClassrooms)) ? 'checked' : '' }}>
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
                        ['randomize_questions', 'Acak urutan soal', $exam->randomize_questions],
                        ['randomize_options', 'Acak urutan pilihan jawaban', $exam->randomize_options],
                        ['prevent_tab_switch', 'Deteksi pindah tab', $exam->prevent_tab_switch],
                        ['prevent_copy_paste', 'Blokir copy-paste', $exam->prevent_copy_paste],
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
                        <input type="text" name="access_code" value="{{ old('access_code', $exam->access_code) }}" placeholder="Kosongkan jika tidak perlu" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-4 py-2.5 text-gray-800">
                    </div>
                </div>
                <div class="space-y-4">
                    <h3 class="text-base font-bold text-gray-700 uppercase tracking-wider">Tampilan Hasil</h3>
                    @php $displayOptions = [
                        ['show_result', 'Tampilkan hasil ke siswa', $exam->show_result],
                        ['show_answer_key', 'Tampilkan kunci jawaban', $exam->show_answer_key],
                        ['allow_review', 'Izinkan review jawaban', $exam->allow_review],
                        ['auto_sync_grade', 'Sinkron otomatis ke nilai rapor', $exam->auto_sync_grade],
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

        {{-- Persyaratan Uang Sekolah --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6" x-data="{ requiresTuition: {{ old('requires_tuition_payment', $exam->requires_tuition_payment ? 1 : 0) ? 'true' : 'false' }} }">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center"><i class="fas fa-file-invoice-dollar text-amber-600"></i></div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Persyaratan Uang Sekolah (SPP)</h2>
                    <p class="text-xs text-gray-500">Hubungkan hak akses ujian siswa dengan kepatuhan pembayaran uang sekolah</p>
                </div>
            </div>
            
            <div class="space-y-4">
                <label class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl border border-gray-200 cursor-pointer hover:bg-amber-50 hover:border-amber-200 transition">
                    <input type="hidden" name="requires_tuition_payment" value="0">
                    <input type="checkbox" name="requires_tuition_payment" value="1" x-model="requiresTuition" class="rounded text-emerald-600 focus:ring-emerald-500 mt-1" {{ old('requires_tuition_payment', $exam->requires_tuition_payment) ? 'checked' : '' }}>
                    <div>
                        <span class="text-base font-bold text-gray-800">Mensyaratkan Pembayaran Uang Sekolah (SPP)</span>
                        <p class="text-sm text-gray-600 mt-0.5">
                            Jika diaktifkan, hanya siswa yang sudah melunasi SPP pada bulan pelaksanaan ujian (atau memperoleh dispensasi dari Wali Kelas) yang dapat mengerjakan soal ujian.
                        </p>
                    </div>
                </label>

                <div x-show="requiresTuition" x-transition class="p-4 bg-amber-50/60 rounded-xl border border-amber-200 space-y-3">
                    <div class="flex items-center gap-2 text-amber-900 font-bold text-sm">
                        <i class="fas fa-info-circle text-amber-600"></i>
                        <span>Ketentuan Bulan Tagihan</span>
                    </div>
                    <p class="text-xs text-gray-600">
                        Secara standar, sistem akan memeriksa SPP siswa pada <strong>bulan berkenaan tanggal pelaksanaan ujian</strong>. Anda juga dapat menentukan bulan/tahun khusus jika diperlukan.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Override Bulan (Opsional)</label>
                            <select name="tuition_month" class="w-full rounded-xl border-gray-200 bg-white focus:ring-2 focus:ring-emerald-500 text-sm py-2 px-3">
                                <option value="">Otomatis (Sesuai Bulan Pelaksanaan Ujian)</option>
                                @foreach([1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'] as $mNum => $mName)
                                    <option value="{{ $mNum }}" {{ old('tuition_month', $exam->tuition_month) == $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Override Tahun (Opsional)</label>
                            <input type="number" name="tuition_year" value="{{ old('tuition_year', $exam->tuition_year) }}" placeholder="Otomatis dari tanggal ujian" min="2020" max="2099" class="w-full rounded-xl border-gray-200 bg-white focus:ring-2 focus:ring-emerald-500 text-sm py-2 px-3">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('guru.cbt.exams.show', $exam) }}" class="px-6 py-3 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition font-medium">Batal</a>
            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl hover:shadow-lg transition font-semibold shadow-sm">
                <i class="fas fa-save mr-2"></i>Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script>
function examEditForm() {
    return {
        selectedBanks: @json($selectedBanks ?: [['bank_id' => '', 'questions_to_pick' => 10]])
    }
}
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.grade-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const grade = this.dataset.grade;
            document.querySelectorAll('.grade-' + grade).forEach(cb => cb.checked = this.checked);
        });
    });
});
</script>
@endpush
