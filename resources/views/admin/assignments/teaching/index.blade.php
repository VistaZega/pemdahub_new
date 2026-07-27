@extends('layouts.admin')

@section('title', 'Penugasan Mengajar')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Penugasan Mengajar</h2>
            <p class="text-gray-600">Kelola penugasan mengajar guru per tahun ajaran dan semester</p>
        </div>
        <div class="flex gap-3">
            @if($unlinkedScheduleCount > 0)
            <button type="button" onclick="document.getElementById('syncModal').classList.remove('hidden')" 
               class="inline-flex items-center px-5 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white font-semibold rounded-xl hover:from-orange-600 hover:to-orange-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl relative">
                <i class="fas fa-sync-alt mr-2"></i>Sinkronkan dari Jadwal
                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center">{{ $unlinkedScheduleCount }}</span>
            </button>
            @endif
            @if($selectedSemesterId)
            <button type="button" onclick="document.getElementById('copyModal').classList.remove('hidden')" 
               class="inline-flex items-center px-5 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold rounded-xl hover:from-blue-600 hover:to-blue-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
                <i class="fas fa-copy mr-2"></i>Salin ke Semester Lain
            </button>
            @endif
            <a href="{{ route('admin.assignments.teaching.create', array_filter(['academic_year_id' => $selectedYearId, 'semester_id' => $selectedSemesterId, 'school_id' => request('school_id') ?: null])) }}" 
               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold rounded-xl hover:from-emerald-600 hover:to-emerald-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
                <i class="fas fa-plus mr-2"></i>Tambah Penugasan
            </a>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-xl mb-6 flex items-center shadow-sm">
            <i class="fas fa-check-circle text-2xl mr-3"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-xl mb-6 flex items-center shadow-sm">
            <i class="fas fa-exclamation-circle text-2xl mr-3"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Summary Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <!-- Card 1: Total Jam Penugasan (JP) Ter-update -->
        <div class="bg-gradient-to-br from-purple-600 to-indigo-700 rounded-2xl p-5 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-purple-200 uppercase tracking-wider mb-1">Total Jam Penugasan (JP)</p>
                    <h3 class="text-3xl font-extrabold text-white tabular-nums">{{ number_format($totalTeachingHoursAll ?? 0, 0, ',', '.') }} <span class="text-base font-medium text-purple-200">JP/minggu</span></h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur-md flex items-center justify-center text-white text-2xl shadow-inner">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center text-xs text-purple-200">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 mr-2 animate-pulse"></span>
                <span>Ter-update untuk periode & unit aktif</span>
            </div>
        </div>

        <!-- Card 2: Total Penugasan Kelas -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Penugasan Kelas</p>
                    <h3 class="text-3xl font-extrabold text-gray-900 tabular-nums">{{ number_format($totalAssignmentsCount ?? 0, 0, ',', '.') }} <span class="text-base font-medium text-gray-500">Penugasan</span></h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 text-xl">
                    <i class="fas fa-book-open"></i>
                </div>
            </div>
            <div class="mt-3 text-xs text-gray-400">
                Item penugasan terdaftar
            </div>
        </div>

        <!-- Card 3: Total Guru Terdaftar -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Guru Terdaftar</p>
                    <h3 class="text-3xl font-extrabold text-gray-900 tabular-nums">{{ number_format($totalActiveTeachersCount ?? 0, 0, ',', '.') }} <span class="text-base font-medium text-gray-500">Guru</span></h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-xl">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
            </div>
            <div class="mt-3 text-xs text-gray-400">
                Aktif dalam sistem
            </div>
        </div>

        <!-- Card 4: Rata-Rata Beban Jam -->
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Rata-Rata Beban Jam</p>
                    <h3 class="text-3xl font-extrabold text-gray-900 tabular-nums">{{ ($totalActiveTeachersCount ?? 0) > 0 ? number_format(($totalTeachingHoursAll ?? 0) / $totalActiveTeachersCount, 1) : 0 }} <span class="text-base font-medium text-gray-500">JP/guru</span></h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 text-xl">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <div class="mt-3 text-xs text-gray-400">
                Beban rata-rata mengajar
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl shadow-lg mb-6 overflow-hidden border border-gray-100">
        <div class="p-6">
            <form method="GET" action="{{ route('admin.assignments.teaching.index') }}" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun Ajaran</label>
                    <select name="academic_year_id" class="px-4 py-3 rounded-xl border-2 border-gray-200 bg-white text-gray-800 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200">
                        <option value="">Semua Tahun</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $selectedYearId == $year->id ? 'selected' : '' }}>
                                {{ $year->year }} @if($year->is_active) (Aktif)@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Semester</label>
                    <select name="semester_id" class="px-4 py-3 rounded-xl border-2 border-gray-200 bg-white text-gray-800 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200">
                        <option value="" class="text-gray-800 bg-white">Semua</option>
                        @foreach($semesters as $sem)
                            <option value="{{ $sem->id }}" class="text-gray-800 bg-white" {{ $selectedSemesterId == $sem->id ? 'selected' : '' }}>
                                {{ $sem->semester_name }} @if($sem->is_active) (Aktif)@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(auth()->user()->isSuperAdmin())
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Sekolah</label>
                    <select name="school_id" class="px-4 py-3 rounded-xl border-2 border-gray-200 bg-white text-gray-800 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200">
                        <option value="">Semua Sekolah</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Cari Guru</label>
                    <input type="text" name="search" 
                           class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200" 
                           placeholder="Nama/Kode Guru..." 
                           value="{{ request('search') }}">
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl hover:from-purple-700 hover:to-pink-700 transform hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                    <a href="{{ route('admin.assignments.teaching.index') }}" 
                       class="inline-flex items-center px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-300 transition-all duration-200">
                        <i class="fas fa-undo mr-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold">No</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Kode</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Nama Guru</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Sekolah</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold">Jml Penugasan</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold">Beban Mengajar</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($teachers as $index => $teacher)
                        <!-- Main Row -->
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 text-gray-600">{{ $teachers->firstItem() + $index }}</td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-gray-800">{{ $teacher->teacher_code }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-800">{{ $teacher->full_name }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $teacher->school->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($teacher->teachingAssignments->count() > 0)
                                    <button onclick="toggleDetail({{ $teacher->id }})" 
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                                        <i class="fas fa-chevron-down mr-2 detail-icon-{{ $teacher->id }}"></i>
                                        {{ $teacher->teachingAssignments->count() }} penugasan
                                    </button>
                                @else
                                    <span class="text-gray-400 text-sm">0 penugasan</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $teacher->total_teaching_hours >= 24 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $teacher->total_teaching_hours }} JP/minggu
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.assignments.teaching.create', ['teacher_id' => $teacher->id, 'school_id' => $teacher->school_id, 'academic_year_id' => $selectedYearId, 'semester_id' => $selectedSemesterId]) }}" 
                                       class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 hover:bg-emerald-200 transition-all duration-200"
                                       title="Tambah Penugasan">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                    <a href="{{ route('admin.assignments.teaching.edit', $teacher->id) }}" 
                                       class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-amber-100 text-amber-600 hover:bg-amber-200 transition-all duration-200"
                                       title="Edit Penugasan">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($teacher->teachingAssignments->isNotEmpty())
                                        <form action="{{ route('admin.assignments.teaching.bulk-destroy', $teacher->id) }}" 
                                              method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('Yakin ingin menghapus SEMUA penugasan mengajar guru ini untuk periode ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
                                            <input type="hidden" name="semester_id" value="{{ $selectedSemesterId }}">
                                            <button type="submit" 
                                                    class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-red-100 text-red-600 hover:bg-red-200 transition-all duration-200"
                                                    title="Hapus Semua Penugasan">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Detail Penugasan Row (Hidden by default) -->
                        @if($teacher->teachingAssignments->count() > 0)
                        <tr id="assignment-detail-{{ $teacher->id }}" class="hidden bg-gray-50">
                            <td colspan="7" class="px-6 py-6">
                                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                                    <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                        <i class="fas fa-chalkboard-teacher text-purple-600 mr-2"></i>
                                        Penugasan Mengajar - {{ $teacher->full_name }}
                                    </h4>
                                    <div class="overflow-x-auto">
                                        <table class="w-full">
                                            <thead class="bg-gray-50 border-b border-gray-100">
                                                <tr>
                                                    <th class="px-4 py-3 text-left text-sm font-semibold rounded-tl-lg">Mata Pelajaran</th>
                                                    <th class="px-4 py-3 text-left text-sm font-semibold">Kelas</th>
                                                    <th class="px-4 py-3 text-center text-sm font-semibold">JP/Minggu</th>
                                                    <th class="px-4 py-3 text-center text-sm font-semibold">Guru Utama</th>
                                                    <th class="px-4 py-3 text-center text-sm font-semibold">Status</th>
                                                    <th class="px-4 py-3 text-center text-sm font-semibold rounded-tr-lg whitespace-nowrap">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-50">
                                                @foreach($teacher->teachingAssignments as $assignment)
                                                <tr class="hover:bg-purple-50 transition-colors">
                                                    <td class="px-4 py-3">
                                                        <span class="font-medium text-gray-800">{{ $assignment->subject->subject_name ?? '-' }}</span>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-medium bg-emerald-100 text-emerald-800">
                                                            {{ $assignment->classroom->class_name ?? '-' }}
                                                        </span>
                                                        @if($assignment->group_code)
                                                            <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-purple-100 text-purple-700 border border-purple-200" title="Grup Gabungan: {{ $assignment->group_code }}">
                                                                <i class="fas fa-layer-group mr-1"></i> GAB
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <span class="font-semibold text-gray-800">{{ $assignment->hours_per_week }} JP</span>
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        @if($assignment->is_main_teacher)
                                                            <i class="fas fa-check-circle text-emerald-500"></i>
                                                        @else
                                                            <i class="fas fa-minus-circle text-gray-300"></i>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        @if($assignment->is_active)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Aktif</span>
                                                        @else
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">Nonaktif</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        <div class="flex items-center justify-center gap-2">
                                                            <form action="{{ route('admin.assignments.teaching.destroy-single', $assignment->id) }}" 
                                                                  method="POST" 
                                                                  class="inline"
                                                                  onsubmit="return confirm('Yakin ingin menghapus penugasan ini?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" 
                                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-all"
                                                                        title="Hapus">
                                                                    <i class="fas fa-trash text-xs"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="bg-gray-50">
                                                <tr>
                                                    <td class="px-4 py-3 font-semibold text-gray-800" colspan="2">Total</td>
                                                    <td class="px-4 py-3 text-center font-bold text-gray-800">{{ $teacher->total_teaching_hours }} JP</td>
                                                    <td colspan="3"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500 text-lg">Belum ada data guru</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($teachers->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $teachers->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function toggleDetail(teacherId) {
    const detailRow = document.getElementById('assignment-detail-' + teacherId);
    const icon = document.querySelector('.detail-icon-' + teacherId);
    
    if (detailRow.classList.contains('hidden')) {
        detailRow.classList.remove('hidden');
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        detailRow.classList.add('hidden');
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}
</script>

<script>
// AJAX: Dynamically update semester dropdown when academic year changes
const yearSelect = document.querySelector('select[name="academic_year_id"]');
const semesterSelect = document.querySelector('select[name="semester_id"]');
if (yearSelect && semesterSelect) {
    yearSelect.addEventListener('change', function() {
        const yearId = this.value;
        if (!yearId) {
            semesterSelect.innerHTML = '<option value="" class="text-gray-800 bg-white">Semua</option>';
            return;
        }
        
        semesterSelect.innerHTML = '<option value="">Memuat...</option>';
        fetch('{{ url("admin/api/semesters-by-year") }}/' + yearId)
            .then(r => r.json())
            .then(data => {
                semesterSelect.innerHTML = '<option value="" class="text-gray-800 bg-white">Semua</option>';
                data.forEach(sem => {
                    const opt = document.createElement('option');
                    opt.value = sem.id;
                    opt.className = 'text-gray-800 bg-white';
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

{{-- Copy to Semester Modal --}}
@if($selectedSemesterId)
<div id="copyModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="document.getElementById('copyModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 z-10">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white mr-3">
                    <i class="fas fa-copy text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Salin Penugasan</h3>
                    <p class="text-sm text-gray-500">Salin semua penugasan ke semester lain</p>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4">
                <p class="text-sm text-blue-800"><strong>Sumber:</strong>
                    {{ $academicYears->firstWhere('id', $selectedYearId)->year ?? '-' }} — 
                    {{ $semesters->firstWhere('id', $selectedSemesterId)->semester_name ?? '-' }}
                </p>
                <p class="text-xs text-blue-600 mt-1">
                    {{ $teachers->sum(fn($t) => $t->teachingAssignments->count()) }} penugasan akan disalin
                </p>
            </div>

            <form action="{{ route('admin.assignments.teaching.copy-to-semester') }}" method="POST">
                @csrf
                <input type="hidden" name="source_academic_year_id" value="{{ $selectedYearId }}">
                <input type="hidden" name="source_semester_id" value="{{ $selectedSemesterId }}">

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Semester Tujuan</label>
                    <select name="target_semester_id" id="targetSemesterSelect" required
                            class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        <option value="">-- Pilih Semester Tujuan --</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Penugasan yang sudah ada di semester tujuan akan dilewati (tidak duplikat).</p>
                </div>

                <div class="mb-6 bg-gray-50 p-4 rounded-xl border border-gray-200">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="copy_schedules" value="1" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-3">
                            <span class="block text-sm font-bold text-gray-800">Salin Jadwal Pelajaran</span>
                            <span class="block text-xs text-gray-500">Ikut salin plot hari & jam di grid jadwal</span>
                        </span>
                    </label>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('copyModal').classList.add('hidden')" 
                            class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl font-semibold hover:from-blue-600 hover:to-blue-700 transition shadow-md">
                        <i class="fas fa-copy mr-1"></i> Salin Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Populate target semester dropdown from all available semesters (excluding source)
document.addEventListener('DOMContentLoaded', function() {
    const targetSelect = document.getElementById('targetSemesterSelect');
    const sourceId = '{{ $selectedSemesterId }}';
    
    // Fetch all semesters grouped by academic year
    @php
        $allSemesters = \App\Models\Semester::with('academicYear')
            ->orderBy('academic_year_id', 'desc')
            ->orderBy('semester_number')
            ->get();
        $semesterData = $allSemesters->map(function($s) {
            return [
                'id' => $s->id,
                'name' => $s->semester_name,
                'year' => $s->academicYear->year ?? '-',
                'is_active' => $s->is_active,
            ];
        });
    @endphp
    
    const allSemesters = @json($semesterData);
    
    allSemesters.forEach(sem => {
        if (String(sem.id) === sourceId) return; // Skip source
        const opt = document.createElement('option');
        opt.value = sem.id;
        opt.textContent = sem.year + ' — ' + sem.name + (sem.is_active ? ' (Aktif)' : '');
        targetSelect.appendChild(opt);
    });
});
</script>
@endif

{{-- Sync from Schedules Modal --}}
@if($unlinkedScheduleCount > 0)
<div id="syncModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="document.getElementById('syncModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 z-10">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center text-white mr-3">
                    <i class="fas fa-sync-alt text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Sinkronkan dari Jadwal</h3>
                    <p class="text-sm text-gray-500">Generate penugasan mengajar dari data jadwal</p>
                </div>
            </div>

            <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 mb-4">
                <p class="text-sm text-orange-800"><strong>{{ $unlinkedScheduleCount }} jadwal</strong> belum tertautkan ke penugasan mengajar.</p>
                <p class="text-xs text-orange-600 mt-1">
                    Sistem akan membuat penugasan mengajar otomatis berdasarkan data guru, mapel, dan kelas dari jadwal yang sudah ada.
                </p>
            </div>

            <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 mb-4">
                <p class="text-xs text-blue-700">
                    <i class="fas fa-info-circle mr-1"></i>
                    Periode: <strong>{{ $academicYears->firstWhere('id', $selectedYearId)->year ?? '-' }}</strong> — 
                    <strong>{{ $semesters->firstWhere('id', $selectedSemesterId)->semester_name ?? 'Semua Semester' }}</strong>
                </p>
            </div>

            <form action="{{ route('admin.assignments.teaching.sync-from-schedules') }}" method="POST">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
                <input type="hidden" name="semester_id" value="{{ $selectedSemesterId }}">

                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('syncModal').classList.add('hidden')" 
                            class="flex-1 px-4 py-3 bg-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-xl font-semibold hover:from-orange-600 hover:to-orange-700 transition shadow-md">
                        <i class="fas fa-sync-alt mr-1"></i> Sinkronkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
