@extends('layouts.admin')

@section('title', 'Tambah Penugasan Mengajar')

@section('content')
<div class="space-y-6" x-data="assignmentForm()">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Tambah Penugasan Mengajar</h1>
                    <p class="text-gray-600 mt-1">Tetapkan guru mengajar mata pelajaran di kelas tertentu</p>
                </div>
            </div>
            <a href="{{ route('admin.assignments.teaching.index') }}" 
                class="flex items-center gap-2 px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 hover:border-gray-400 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </div>
    </div>

    <!-- Alerts -->
    @if($errors->any())
    <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-xl">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <p class="text-red-800 font-bold mb-2">Terjadi Kesalahan!</p>
                <ul class="list-disc list-inside space-y-1 text-red-700 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-xl">
        <div class="flex items-center gap-3">
            <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
            <span class="text-red-700 font-medium">{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <form action="{{ route('admin.assignments.teaching.store') }}" method="POST">
        @csrf

        <!-- Section 1: Informasi Dasar -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gradient-to-r from-emerald-500 to-teal-600">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white font-bold">1</div>
                    <h2 class="text-xl font-bold text-white">Pilih Guru & Periode</h2>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div>
                        <label for="teacher_id" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-chalkboard-teacher mr-1"></i> Guru <span class="text-red-500">*</span>
                        </label>
                        <select name="teacher_id" id="teacher_id" 
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all @error('teacher_id') border-red-500 @enderror" 
                                required>
                            <option value="">-- Pilih Guru --</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('teacher_id', $selectedTeacher->id ?? '') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->teacher_code }} - {{ $teacher->full_name }}
                                    @if($teacher->school) ({{ $teacher->school->name }}) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('teacher_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt mr-1"></i> Tahun Ajaran <span class="text-red-500">*</span>
                        </label>
                        <select name="academic_year_id" 
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all @error('academic_year_id') border-red-500 @enderror" 
                                required>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ old('academic_year_id', $selectedAcademicYearId ?? ($currentYear->id ?? '')) == $year->id ? 'selected' : '' }}>
                                        {{ $year->year ?? $year->name }} @if($year->is_active) (Aktif)@endif
                                    </option>
                                @endforeach
                        </select>
                        @error('academic_year_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-graduation-cap mr-1"></i> Semester <span class="text-red-500">*</span>
                        </label>
                        <select name="semester_id" 
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all @error('semester_id') border-red-500 @enderror" 
                                required>
                                @foreach($semesters as $sem)
                                    <option value="{{ $sem->id }}" {{ old('semester_id', $selectedSemesterId ?? ($activeSemester->id ?? '')) == $sem->id ? 'selected' : '' }}>
                                        {{ $sem->semester_name }} @if($sem->is_active) (Aktif)@endif
                                    </option>
                                @endforeach
                        </select>
                        @error('semester_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Existing Assignments Info -->
        @if($selectedTeacher && $currentAssignments->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-6 border border-blue-200">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-500 to-indigo-600">
                <div class="flex items-center gap-3">
                    <i class="fas fa-list-check text-white text-lg"></i>
                    <h2 class="text-lg font-bold text-white">
                        Penugasan yang Sudah Ada 
                        <span class="ml-2 px-3 py-1 bg-white/20 rounded-full text-sm">
                            {{ $currentAssignments->count() }} penugasan &bullet; {{ $currentAssignments->sum('hours_per_week') }} JP/minggu
                        </span>
                    </h2>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Mata Pelajaran</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Kelas</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">JP/Minggu</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Guru Utama</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($currentAssignments as $existing)
                        <tr class="hover:bg-blue-50/50 transition-colors">
                            <td class="px-6 py-3 font-medium text-gray-800">{{ $existing->subject->subject_name ?? '-' }}</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-sm font-medium bg-emerald-100 text-emerald-800">
                                    {{ $existing->classroom->class_name ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-center font-semibold text-gray-800">{{ $existing->hours_per_week }}</td>
                            <td class="px-6 py-3 text-center">
                                @if($existing->is_main_teacher)
                                    <i class="fas fa-check-circle text-emerald-500"></i>
                                @else
                                    <i class="fas fa-minus-circle text-gray-300"></i>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if($selectedTeacher)
        <div class="bg-amber-50 border-l-4 border-amber-500 p-5 mb-6 rounded-r-2xl shadow-sm">
            <div class="flex items-start gap-3">
                <i class="fas fa-info-circle text-amber-500 text-2xl mt-0.5"></i>
                <div class="text-sm text-amber-900 leading-relaxed">
                    <h4 class="font-bold text-base mb-1">💡 Informasi Pilihan Mata Pelajaran</h4>
                    <p class="mb-2">Daftar Mata Pelajaran yang muncul pada pilihan di bawah ini <b>tersaring secara otomatis</b> berdasarkan <b>Kompetensi / Mapel yang Diampu</b> oleh <b>{{ $selectedTeacher->full_name }}</b>.</p>
                    <p>Apabila mata pelajaran yang ingin Anda tugaskan <b>tidak muncul</b> dalam daftar, silakan tambahkan terlebih dahulu mata pelajaran tersebut ke profil guru yang bersangkutan melalui menu: <a href="{{ route('admin.teachers.competencies', $selectedTeacher->id) }}" target="_blank" class="inline-flex items-center gap-1 font-bold text-amber-900 underline hover:text-amber-700 bg-amber-100 px-2 py-0.5 rounded transition-all">Kompetensi Guru <i class="fas fa-external-link-alt text-xs"></i></a>.</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Section 2: Penugasan Baru -->
        @if(isset($hasContract) && !$hasContract)
        <div class="bg-red-50 border-l-4 border-red-500 p-5 mb-6 rounded-2xl shadow-sm">
            <div class="flex items-start gap-3">
                <i class="fas fa-lock text-red-500 text-2xl mt-0.5"></i>
                <div class="text-sm text-red-900 leading-relaxed">
                    <h4 class="font-bold text-base mb-1">Akses Ditolak: Perjanjian Kinerja Belum Disetujui</h4>
                    <p>Guru atas nama <b>{{ $selectedTeacher->full_name }}</b> belum memiliki Perjanjian Kinerja Mengajar (2A/2B) yang disetujui Yayasan untuk Tahun Pelajaran ini. Fitur Penugasan Mengajar dikunci sementara.</p>
                </div>
            </div>
        </div>
        @else
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gradient-to-r from-purple-500 to-pink-600">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-white font-bold">2</div>
                        <h2 class="text-xl font-bold text-white">Penugasan Baru</h2>
                    </div>
                    <button type="button" @click="addRow()" 
                            class="flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-xl font-medium transition-all duration-200">
                        <i class="fas fa-plus"></i>
                        <span>Tambah Baris</span>
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-4" id="assignmentContainer">
                <template x-for="(row, index) in rows" :key="row.id">
                    <div class="relative p-5 border-2 border-gray-200 rounded-xl bg-gradient-to-r from-gray-50 to-white hover:border-purple-300 transition-all duration-200 group">
                        <!-- Row number badge -->
                        <div class="absolute -top-3 -left-3 w-7 h-7 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center shadow-md">
                            <span class="text-white text-xs font-bold" x-text="index + 1"></span>
                        </div>
                        <!-- Remove button -->
                        <button type="button" x-show="rows.length > 1" @click="removeRow(row.id)"
                                class="absolute -top-3 -right-3 w-7 h-7 rounded-full bg-red-500 hover:bg-red-600 flex items-center justify-center shadow-md opacity-0 group-hover:opacity-100 transition-all duration-200">
                            <i class="fas fa-times text-white text-xs"></i>
                        </button>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                            <div class="md:col-span-3">
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    <i class="fas fa-book mr-1 text-purple-500"></i> Mata Pelajaran <span class="text-red-500">*</span>
                                </label>
                                <select :name="'assignments[' + index + '][subject_id]'" 
                                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all" 
                                        required>
                                    <option value="">-- Pilih Mapel --</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->subject_name }}</option>
                                    @endforeach
                                </select>
                                @if($selectedTeacher)
                                    <p class="mt-1 text-xs text-amber-700">Mapel tidak muncul? <a href="{{ route('admin.teachers.competencies', $selectedTeacher->id) }}" target="_blank" class="underline font-bold">Atur Kompetensi Guru</a></p>
                                @endif
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    <i class="fas fa-door-open mr-1 text-emerald-500"></i> Kelas <span class="text-red-500">*</span>
                                </label>
                                <select :name="'assignments[' + index + '][classroom_id]'" 
                                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all" 
                                        required>
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($classrooms as $classroom)
                                        <option value="{{ $classroom->id }}">{{ $classroom->class_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-1">
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    <i class="fas fa-layer-group mr-1 text-purple-400"></i> Grup
                                </label>
                                <input type="text" :name="'assignments[' + index + '][group_code]'" 
                                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm" 
                                       placeholder="Kode Grup">
                            </div>
                            <div class="md:col-span-1">
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    <i class="fas fa-clock mr-1 text-blue-500"></i> JP
                                </label>
                                <input type="number" :name="'assignments[' + index + '][hours_per_week]'" 
                                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-center font-semibold transition-all" 
                                       min="1" max="40" value="2" required>
                            </div>
                            <div class="md:col-span-2">
                                  <label class="block text-sm font-bold text-gray-700 mb-2">
                                      <i class="fas fa-cubes mr-1 text-orange-500"></i> Tipe Blok
                                  </label>
                                  <select :name="'assignments[' + index + '][block_type]'" 
                                          class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm transition-all">
                                      <option value="none">Reguler (Semua Siswa)</option>
                                      <option value="parallel">Paralel / Agama (Jadwal Bersamaan di Jam yang Sama)</option>
                                      <option value="all">Sistem Blok SMK - Kelompok A</option>
                                      <option value="split">Sistem Blok SMK - Kelompok B</option>
                                  </select>
                            </div>
                            <div class="md:col-span-2 text-center">
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    <i class="fas fa-star mr-1 text-yellow-500"></i> Guru Utama
                                </label>
                                <div class="flex items-center justify-center py-2">
                                    <input type="hidden" :name="'assignments[' + index + '][is_main_teacher]'" value="0">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" :name="'assignments[' + index + '][is_main_teacher]'" value="1" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Empty hint -->
                <div x-show="rows.length === 0" class="text-center py-12">
                    <i class="fas fa-inbox text-5xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">Klik "Tambah Baris" untuk menambahkan penugasan</p>
                </div>
            </div>
        </div>

        <!-- Submit Section -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="p-6 flex items-center justify-between">
                <div class="flex items-center gap-3 text-gray-500">
                    <i class="fas fa-info-circle"></i>
                    <span class="text-sm" x-text="rows.length + ' penugasan akan ditambahkan'"></span>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.assignments.teaching.index') }}" 
                       class="px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 hover:border-gray-400 shadow-md transition-all">
                        Batal
                    </a>
                    <button type="submit" 
                            class="flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-bold hover:from-emerald-600 hover:to-teal-700 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                        <i class="fas fa-save"></i>
                        Simpan Penugasan
                    </button>
                </div>
            </div>
        </div>
        @endif
    </form>
</div>

<script>
function assignmentForm() {
    return {
        nextId: 1,
        rows: [{ id: 0 }],
        addRow() {
            this.rows.push({ id: this.nextId++ });
        },
        removeRow(id) {
            this.rows = this.rows.filter(r => r.id !== id);
        }
    }
}

// School filter — reload page with school_id param
const schoolFilter = document.getElementById('school_filter');
if (schoolFilter) {
    schoolFilter.addEventListener('change', function() {
        const params = new URLSearchParams(window.location.search);
        if (this.value) {
            params.set('school_id', this.value);
        } else {
            params.delete('school_id');
        }
        params.delete('teacher_id');
        window.location.href = '{{ route("admin.assignments.teaching.create") }}?' + params.toString();
    });
}

// Teacher select — reload with teacher_id
document.getElementById('teacher_id').addEventListener('change', function() {
    if (this.value) {
        const params = new URLSearchParams(window.location.search);
        params.set('teacher_id', this.value);
        window.location.href = '{{ route("admin.assignments.teaching.create") }}?' + params.toString();
    }
});

// AJAX: Dynamically update semester dropdown when academic year changes
const yearSelect = document.querySelector('select[name="academic_year_id"]');
const semesterSelect = document.querySelector('select[name="semester_id"]');
if (yearSelect && semesterSelect) {
    yearSelect.addEventListener('change', function() {
        const yearId = this.value;
        if (!yearId) return;
        
        semesterSelect.innerHTML = '<option value="">Memuat...</option>';
        fetch('{{ url("admin/api/semesters-by-year") }}/' + yearId)
            .then(r => r.json())
            .then(data => {
                semesterSelect.innerHTML = '';
                if (data.length === 0) {
                    semesterSelect.innerHTML = '<option value="">-- Tidak ada semester --</option>';
                    return;
                }
                data.forEach(sem => {
                    const opt = document.createElement('option');
                    opt.value = sem.id;
                    opt.textContent = sem.semester_name + (sem.is_active ? ' (Aktif)' : '');
                    if (sem.is_active) opt.selected = true;
                    semesterSelect.appendChild(opt);
                });
            })
            .catch(() => {
                semesterSelect.innerHTML = '<option value="">-- Gagal memuat --</option>';
            });
    });
}
</script>
@endsection
