@extends('layouts.admin')
@section('title', 'Edit Ujian CBT')
@section('content')
<div class="space-y-6" x-data="examEditForm()">
    <div class="mb-8 flex items-center gap-4">
        <a href="{{ route('admin.cbt.show', $exam) }}" class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-800 hover:bg-gray-200"><i class="fas fa-arrow-left"></i></a>
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white"><i class="fas fa-edit text-xl"></i></div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Ujian</h1>
                <p class="text-gray-600 mt-1 text-sm">{{ $exam->exam_title }}</p>
            </div>
        </div>
    </div>

    {{-- Banner Informasi Mode Edit --}}
    @php
        $statusInfo = match($exam->status) {
            'active' => ['bg-emerald-50', 'border-emerald-200', 'bg-emerald-100', 'text-emerald-600', 'text-emerald-800', 'Aktif (Sedang Berlangsung)', 'fa-satellite-dish'],
            'published' => ['bg-amber-50', 'border-amber-200', 'bg-amber-100', 'text-amber-600', 'text-amber-800', 'Diterbitkan (Published)', 'fa-bullhorn'],
            default => ['bg-indigo-50', 'border-indigo-200', 'bg-indigo-100', 'text-indigo-600', 'text-indigo-800', 'Draf', 'fa-pencil-alt'],
        };
    @endphp
    <div class="flex items-start gap-3 p-4 {{ $statusInfo[0] }} border {{ $statusInfo[1] }} rounded-2xl">
        <div class="w-8 h-8 rounded-lg {{ $statusInfo[2] }} flex items-center justify-center flex-shrink-0 mt-0.5">
            <i class="fas {{ $statusInfo[6] }} {{ $statusInfo[3] }} text-sm"></i>
        </div>
        <div>
            <p class="{{ $statusInfo[4] }} font-semibold text-sm">Mode Edit &mdash; Status Saat Ini: {{ $statusInfo[5] }}</p>
            <p class="text-gray-600 text-sm mt-1">Anda dapat memperbarui judul, jadwal, KKM, durasi, kelas peserta, serta pengaturan keamanan &amp; tampilan. Bank soal &amp; butir soal bersifat tetap (read-only).</p>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-xl">
        <div class="flex items-center"><i class="fas fa-exclamation-circle text-red-500 mr-2"></i><span class="text-red-800 font-semibold">Terdapat kesalahan:</span></div>
        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.cbt.exams.update', $exam) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Info Dasar --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 mb-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100">
                    <i class="fas fa-info-circle text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 leading-none">Informasi Dasar</h2>
                    <p class="text-gray-500 text-sm mt-1">Detail utama ujian sekolah</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">Judul Ujian <span class="text-red-500">*</span></label>
                    <input type="text" name="exam_title" value="{{ old('exam_title', $exam->exam_title) }}"
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 px-5 py-4 text-gray-800 font-bold transition-all text-lg"
                        required placeholder="Contoh: UAS Ganjil Matematika Kelas 9">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">Sekolah</label>
                    <div class="w-full rounded-xl border-gray-200 bg-gray-100 px-5 py-3.5 text-gray-700 font-bold border-dashed border-2">
                        {{ $schools->firstWhere('id', $exam->school_id)?->name ?? '-' }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">Mata Pelajaran</label>
                    <div class="w-full rounded-xl border-gray-200 bg-gray-100 px-5 py-3.5 text-gray-700 font-bold border-dashed border-2">
                        {{ $exam->subject?->subject_name ?? '-' }}
                    </div>
                    <p class="text-xs text-gray-400 mt-1"><i class="fas fa-lock mr-1"></i>Mata pelajaran tidak dapat diubah</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">Tipe Ujian <span class="text-red-500">*</span></label>
                    <select name="exam_type" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 px-5 py-3.5 text-gray-800 font-bold transition-all" required>
                        <option value="">Pilih Tipe</option>
                        @foreach($examTypes as $key => $label)
                        <option value="{{ $key }}" {{ old('exam_type', $exam->exam_type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">Status Ujian <span class="text-red-500">*</span></label>
                    <select name="status" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 px-5 py-3.5 text-gray-800 font-bold transition-all" required>
                        <option value="draft" {{ old('status', $exam->status) == 'draft' ? 'selected' : '' }}>Draf (Dalam Persiapan)</option>
                        <option value="published" {{ old('status', $exam->status) == 'published' ? 'selected' : '' }}>Diterbitkan (Terjadwal)</option>
                        <option value="active" {{ old('status', $exam->status) == 'active' ? 'selected' : '' }}>Aktif (Sedang Berlangsung)</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">Durasi (menit) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes) }}"
                            class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 px-5 py-3.5 text-gray-800 font-bold transition-all">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-sm font-bold text-gray-500 uppercase tracking-widest">Menit</div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">Deskripsi</label>
                    <textarea name="exam_description" rows="2" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 px-5 py-4 text-gray-800 font-medium transition-all" placeholder="Deskripsi opsional...">{{ old('exam_description', $exam->exam_description) }}</textarea>
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
                    <p class="text-gray-500 text-sm mt-1">Pengaturan waktu dan ambang batas nilai</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">Waktu Mulai</label>
                    <input type="datetime-local" name="start_time"
                        value="{{ old('start_time', $exam->start_time ? \Carbon\Carbon::parse($exam->start_time)->format('Y-m-d\TH:i') : '') }}"
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 px-5 py-3.5 text-gray-800 font-bold transition-all text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">Waktu Selesai</label>
                    <input type="datetime-local" name="end_time"
                        value="{{ old('end_time', $exam->end_time ? \Carbon\Carbon::parse($exam->end_time)->format('Y-m-d\TH:i') : '') }}"
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 px-5 py-3.5 text-gray-800 font-bold transition-all text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">KKM <span class="text-red-500">*</span></label>
                    <input type="number" name="passing_score" value="{{ old('passing_score', $exam->passing_score) }}" min="0" max="100"
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 px-5 py-3.5 text-gray-800 font-bold transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">Maks Percobaan <span class="text-red-500">*</span></label>
                    <input type="number" name="max_attempts" value="{{ old('max_attempts', $exam->max_attempts) }}" min="1"
                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 px-5 py-3.5 text-gray-800 font-bold transition-all">
                </div>
            </div>
        </div>

        {{-- Bank Soal (Read-only info) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 mb-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-gray-400 shadow-sm border border-gray-100">
                    <i class="fas fa-database text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-500 leading-none">Bank Soal</h2>
                    <p class="text-gray-400 text-sm mt-1">Tidak dapat diubah setelah ujian dibuat</p>
                </div>
                <div class="ml-auto">
                    <span class="px-3 py-1.5 rounded-xl bg-gray-100 text-gray-500 text-xs font-bold uppercase tracking-wider border border-gray-200">
                        <i class="fas fa-lock mr-1"></i>Read-only
                    </span>
                </div>
            </div>
            <div class="space-y-2">
                @foreach($exam->questionBanks as $bank)
                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <div class="w-9 h-9 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-database text-purple-600 text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-gray-700 text-sm">{{ $bank->bank_name }}</div>
                        <div class="text-xs text-gray-500">{{ $bank->subject?->subject_name ?? '-' }} &middot; {{ $bank->total_questions }} soal tersedia</div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <div class="text-sm font-bold text-purple-700">{{ $bank->pivot->questions_to_pick }} soal diambil</div>
                    </div>
                </div>
                @endforeach
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 flex items-center gap-2">
                    <i class="fas fa-info-circle text-amber-500 text-sm flex-shrink-0"></i>
                    <p class="text-xs text-amber-700">Total soal ditampilkan: <strong>{{ $exam->total_questions_shown }} soal</strong>. Untuk mengubah bank soal, silakan buat ujian baru.</p>
                </div>
            </div>
        </div>

        {{-- Kelas Peserta --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 mb-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-sm border border-emerald-100">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 leading-none">Kelas Peserta</h2>
                    <p class="text-gray-500 text-sm mt-1">Pilih rombongan belajar yang mengikuti ujian</p>
                </div>
            </div>

            <div class="space-y-6">
                <template x-for="gradeGroup in groupedClassrooms" :key="gradeGroup.grade">
                    <div class="p-6 rounded-2xl border bg-white border-indigo-100 shadow-sm">
                        <div class="flex items-center gap-4 mb-5">
                            <span class="px-4 py-1.5 rounded-xl text-sm font-bold uppercase tracking-widest bg-indigo-100 text-indigo-700">Kelas <span x-text="gradeGroup.grade"></span></span>
                            <label class="text-sm font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:text-indigo-600 flex items-center gap-1.5">
                                <input type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500" @change="toggleGrade(gradeGroup.grade, $event.target.checked)">
                                <span>Pilih Semua</span>
                            </label>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                            <template x-for="cr in gradeGroup.items" :key="cr.id">
                                <label class="flex items-center gap-3 p-3 bg-white rounded-xl border cursor-pointer transition"
                                       :class="cr.preSelected ? 'border-indigo-400 bg-indigo-50' : 'border-gray-200 hover:border-indigo-300 hover:bg-indigo-50'">
                                    <input type="checkbox" name="classrooms[]" :value="cr.id"
                                        class="rounded text-indigo-600 focus:ring-indigo-500"
                                        :class="'dyn-grade-' + gradeGroup.grade"
                                        :checked="cr.preSelected"
                                        @change="cr.preSelected = $event.target.checked">
                                    <span class="text-sm text-gray-700 font-bold" x-text="cr.name"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
            @error('classrooms') <p class="text-red-500 text-sm mt-2 font-bold">{{ $message }}</p> @enderror
        </div>

        {{-- Pengaturan Keamanan & Tampilan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 mb-8">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600 shadow-sm border border-rose-100">
                    <i class="fas fa-shield-alt text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 leading-none">Keamanan & Tampilan</h2>
                    <p class="text-gray-500 text-sm mt-1">Proteksi anti-curang dan visibilitas hasil</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Fitur Keamanan</h3>
                    @php $securityOptions = [
                        ['randomize_questions', 'Acak urutan soal'],
                        ['randomize_options',   'Acak urutan pilihan jawaban'],
                        ['prevent_tab_switch',  'Deteksi pindah tab'],
                        ['prevent_copy_paste',  'Blokir copy-paste'],
                    ]; @endphp
                    @foreach($securityOptions as [$name, $label])
                    <label class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl border border-gray-200 cursor-pointer hover:bg-indigo-50 hover:border-indigo-200 transition-all group">
                        <input type="hidden" name="{{ $name }}" value="0">
                        <input type="checkbox" name="{{ $name }}" value="1" class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" {{ old($name, $exam->$name) ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700 font-bold group-hover:text-indigo-700 transition-colors">{{ $label }}</span>
                    </label>
                    @endforeach
                    <div class="pt-4">
                        <label class="block text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">Kode Akses Ujian (Opsional)</label>
                        <input type="text" name="access_code" value="{{ old('access_code', $exam->access_code) }}" placeholder="Contoh: TOKEN123"
                            class="w-full rounded-xl border-gray-200 bg-gray-50 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 px-5 py-3.5 text-gray-800 font-bold uppercase placeholder-gray-300">
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Aturan Tampilan</h3>
                    @php $displayOptions = [
                        ['show_result',     'Tampilkan hasil ke siswa'],
                        ['show_answer_key', 'Tampilkan kunci jawaban (setelah selesai)'],
                        ['allow_review',    'Izinkan review jawaban (setelah selesai)'],
                        ['auto_sync_grade', 'Sinkron otomatis ke nilai rapor'],
                    ]; @endphp
                    @foreach($displayOptions as [$name, $label])
                    <label class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl border border-gray-200 cursor-pointer hover:bg-indigo-50 hover:border-indigo-200 transition-all group">
                        <input type="hidden" name="{{ $name }}" value="0">
                        <input type="checkbox" name="{{ $name }}" value="1" class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" {{ old($name, $exam->$name) ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700 font-bold group-hover:text-indigo-700 transition-colors">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-4 p-4 bg-gray-100/50 rounded-2xl">
            <a href="{{ route('admin.cbt.show', $exam) }}" class="px-8 py-4 bg-white border border-gray-200 text-gray-800 rounded-xl hover:bg-gray-50 transition font-bold text-sm uppercase tracking-widest">Batalkan</a>
            <button type="submit" class="px-12 py-4 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl hover:shadow-2xl transition font-bold text-sm uppercase tracking-widest shadow-xl shadow-amber-200">
                <i class="fas fa-save mr-2"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script>
function examEditForm() {
    const allClassrooms = @json($classroomsJson);
    const selectedIds   = @json($selectedClassroomIds);
    const schoolId      = {{ $exam->school_id }};

    // Pre-mark selected classrooms
    allClassrooms.forEach(c => {
        c.preSelected = selectedIds.includes(c.id);
    });

    return {
        get filteredClassrooms() {
            return allClassrooms.filter(c => c.school_id == schoolId);
        },

        get groupedClassrooms() {
            const groups = {};
            this.filteredClassrooms.forEach(c => {
                if (!groups[c.grade]) groups[c.grade] = { grade: c.grade, items: [] };
                groups[c.grade].items.push(c);
            });
            return Object.values(groups).sort((a, b) => a.grade - b.grade);
        },

        toggleGrade(grade, checked) {
            document.querySelectorAll('.dyn-grade-' + grade).forEach(cb => {
                cb.checked = checked;
                const id = parseInt(cb.value);
                const cls = allClassrooms.find(c => c.id === id);
                if (cls) cls.preSelected = checked;
            });
        }
    }
}
</script>
@endpush
