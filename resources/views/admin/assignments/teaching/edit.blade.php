@extends('layouts.admin')

@section('title', 'Edit Penugasan Mengajar - ' . $teacher->full_name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Penugasan Mengajar</h1>
                    <p class="text-gray-600 mt-1">
                        <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-sm font-bold rounded">{{ $teacher->teacher_code }}</span>
                        {{ $teacher->full_name }}
                        <span class="text-gray-400 mx-1">&bullet;</span>
                        {{ $teacher->school->name ?? '-' }}
                    </p>
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
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-xl">
        <div class="flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
            <span class="text-emerald-700 font-medium">{{ session('success') }}</span>
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

    <!-- Filter Period -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
        <div class="p-6">
            <form method="GET" action="{{ route('admin.assignments.teaching.edit', $teacher->id) }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i> Tahun Ajaran
                    </label>
                    <select name="academic_year_id" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $selectedYearId == $year->id ? 'selected' : '' }}>
                                {{ $year->year ?? $year->name }} @if($year->is_active) (Aktif)@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-graduation-cap mr-1"></i> Semester
                    </label>
                    <select name="semester_id" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all">
                        @foreach($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ $selectedSemesterId == $sem->id ? 'selected' : '' }}>
                                {{ $sem->semester_name }} @if($sem->is_active) (Aktif)@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-4">
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl hover:from-purple-700 hover:to-pink-700 transform hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg">
                        <i class="fas fa-search mr-2"></i>Tampilkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-md p-5 border border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-blue-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Jumlah Penugasan</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $assignments->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-md p-5 border border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <i class="fas fa-clock text-emerald-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Total Beban</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $assignments->sum('hours_per_week') }} <span class="text-sm font-medium text-gray-500">JP/Minggu</span></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-md p-5 border border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-star text-purple-600 text-lg"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Guru Utama</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $assignments->where('is_main_teacher', true)->count() }} <span class="text-sm font-medium text-gray-500">kelas</span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignments Table -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
        <div class="px-6 py-4 bg-gradient-to-r from-purple-500 to-pink-600">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fas fa-chalkboard-teacher text-white text-lg"></i>
                    <h2 class="text-xl font-bold text-white">Daftar Penugasan</h2>
                </div>
                <a href="{{ route('admin.assignments.teaching.create', ['teacher_id' => $teacher->id]) }}" 
                   class="flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-xl font-medium transition-all duration-200">
                    <i class="fas fa-plus"></i>
                    <span>Tambah</span>
                </a>
            </div>
        </div>
        
        @if($assignments->isNotEmpty())
        <div class="divide-y divide-gray-100">
            @foreach($assignments as $index => $assignment)
            <div class="p-5 hover:bg-gray-50/50 transition-colors" x-data="{ editing: false }">
                <!-- Display Mode -->
                <div x-show="!editing" class="flex items-center justify-between">
                    <div class="flex items-center gap-6 flex-1">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center shadow-md">
                            <span class="text-white text-sm font-bold">{{ $index + 1 }}</span>
                        </div>
                        <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                            <div>
                                <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Mata Pelajaran</p>
                                <p class="font-semibold text-gray-900">{{ $assignment->subject->subject_name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Kelas</p>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-sm font-medium bg-emerald-100 text-emerald-800">
                                    {{ $assignment->classroom->class_name ?? '-' }}
                                </span>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">JP/Minggu</p>
                                <div class="flex items-center gap-2">
                                    <p class="font-bold text-gray-900 text-lg">{{ $assignment->hours_per_week }}</p>
                                    @if($assignment->group_code)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-700 border border-purple-200" title="Grup: {{ $assignment->group_code }}">
                                            <i class="fas fa-layer-group mr-1"></i> GAB
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div>
                                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Guru Utama</p>
                                    @if($assignment->is_main_teacher)
                                        <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                                    @else
                                        <i class="fas fa-minus-circle text-gray-300 text-lg"></i>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Status</p>
                                    @if($assignment->is_active)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Aktif</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">Nonaktif</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 ml-4">
                        <button @click="editing = true" type="button"
                                class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-amber-100 text-amber-600 hover:bg-amber-200 transition-all duration-200" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="{{ route('admin.assignments.teaching.destroy-single', $assignment->id) }}" method="POST" class="inline"
                              onsubmit="return confirm('Yakin ingin menghapus penugasan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-red-100 text-red-600 hover:bg-red-200 transition-all duration-200" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Edit Mode -->
                <form x-show="editing" x-cloak action="{{ route('admin.assignments.teaching.update', $assignment->id) }}" method="POST"
                      class="bg-gradient-to-r from-amber-50 to-orange-50 -m-5 p-5 border-l-4 border-amber-500">
                    @csrf
                    @method('PUT')
                    <div class="flex items-center gap-2 mb-4">
                        <i class="fas fa-edit text-amber-600"></i>
                        <span class="text-sm font-bold text-amber-800">Mode Edit</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Mata Pelajaran</label>
                            <select name="subject_id" class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all text-sm">
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ $assignment->subject_id == $subject->id ? 'selected' : '' }}>{{ $subject->subject_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Kelas</label>
                            <select name="classroom_id" class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all text-sm">
                                @foreach($classrooms as $classroom)
                                    <option value="{{ $classroom->id }}" {{ $assignment->classroom_id == $classroom->id ? 'selected' : '' }}>{{ $classroom->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-sm font-bold text-gray-700 mb-1">JP</label>
                            <input type="number" name="hours_per_week" value="{{ $assignment->hours_per_week }}" 
                                   class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-center font-semibold text-sm" min="1" max="40">
                        </div>
                        <div class="md:col-span-1 text-center">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Utama</label>
                            <input type="hidden" name="is_main_teacher" value="0">
                            <label class="relative inline-flex items-center cursor-pointer mt-1">
                                <input type="checkbox" name="is_main_teacher" value="1" class="sr-only peer" {{ $assignment->is_main_teacher ? 'checked' : '' }}>
                                <div class="w-10 h-5 bg-gray-300 rounded-full peer peer-checked:bg-amber-500 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all"></div>
                            </label>
                        </div>
                        <div class="md:col-span-1 text-center">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Aktif</label>
                            <input type="hidden" name="is_active" value="0">
                            <label class="relative inline-flex items-center cursor-pointer mt-1">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ $assignment->is_active ? 'checked' : '' }}>
                                <div class="w-10 h-5 bg-gray-300 rounded-full peer peer-checked:bg-emerald-500 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all"></div>
                            </label>
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Grup</label>
                            <input type="text" name="group_code" value="{{ $assignment->group_code }}" 
                                   class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm" placeholder="GRP-001">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Tipe Blok</label>
                            <select name="block_type" class="w-full px-3 py-2.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all text-sm">
                                <option value="none" {{ $assignment->block_type == 'none' ? 'selected' : '' }}>Reguler (Semua Siswa)</option>
                                <option value="parallel" {{ $assignment->block_type == 'parallel' ? 'selected' : '' }}>Paralel / Agama (Jadwal Bersamaan di Jam yang Sama)</option>
                                <option value="all" {{ $assignment->block_type == 'all' ? 'selected' : '' }}>Sistem Blok SMK - Kelompok A</option>
                                <option value="split" {{ $assignment->block_type == 'split' ? 'selected' : '' }}>Sistem Blok SMK - Kelompok B</option>
                            </select>
                        </div>
                        <div class="md:col-span-2 flex items-end gap-2 text-right">
                            <button type="submit" class="flex-1 inline-flex items-center justify-center px-3 py-2.5 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-xl font-medium hover:from-emerald-600 hover:to-emerald-700 transition-all shadow-md text-sm">
                                <i class="fas fa-save mr-1"></i> Simpan
                            </button>
                            <button type="button" @click="editing = false" class="inline-flex items-center justify-center px-3 py-2.5 bg-gray-200 text-gray-700 rounded-xl font-medium hover:bg-gray-300 transition-all text-sm">
                                Batal
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @endforeach
        </div>

        <!-- Footer totals -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <span class="font-bold text-gray-700">Total Beban Mengajar</span>
                <span class="px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-bold rounded-xl shadow-md">
                    {{ $assignments->sum('hours_per_week') }} JP/Minggu
                </span>
            </div>
        </div>
        @else
        <div class="text-center py-16">
            <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-inbox text-4xl text-gray-300"></i>
            </div>
            <p class="text-gray-500 text-lg font-medium">Belum ada penugasan mengajar untuk periode ini</p>
            <a href="{{ route('admin.assignments.teaching.create', ['teacher_id' => $teacher->id]) }}" 
               class="inline-flex items-center gap-2 mt-4 px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-medium hover:from-emerald-600 hover:to-teal-700 shadow-lg transition-all">
                <i class="fas fa-plus"></i> Tambah Penugasan Pertama
            </a>
        </div>
        @endif
    </div>
</div>

<script>
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