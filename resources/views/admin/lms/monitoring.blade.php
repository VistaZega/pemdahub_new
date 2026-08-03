@extends('layouts.admin')

@section('title', 'Monitoring LMS - PembdaHUB')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Monitoring Keaktifan LMS</h2>
        <p class="text-sm text-gray-500">Pantau penggunaan platform pembelajaran oleh Guru dan Siswa</p>
    </div>
    <div class="flex items-center gap-2">
        <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider">
            Live Monitoring
        </span>
    </div>
</div>

<!-- Stats Overview -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Courses -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
        <div class="relative">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white mb-4 shadow-lg shadow-blue-200">
                <i class="fas fa-book-open text-xl"></i>
            </div>
            <p class="text-gray-500 text-sm font-medium">Total Materi/Kursus</p>
            <h3 class="text-3xl font-bold text-gray-800">{{ number_format($totalCourses) }}</h3>
        </div>
    </div>

    <!-- Total Enrollments -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
        <div class="relative">
            <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center text-white mb-4 shadow-lg shadow-emerald-200">
                <i class="fas fa-user-graduate text-xl"></i>
            </div>
            <p class="text-gray-500 text-sm font-medium">Siswa Terdaftar</p>
            <h3 class="text-3xl font-bold text-gray-800">{{ number_format($totalEnrollments) }}</h3>
        </div>
    </div>

    <!-- Total Submissions -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-orange-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
        <div class="relative">
            <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-amber-600 rounded-2xl flex items-center justify-center text-white mb-4 shadow-lg shadow-orange-200">
                <i class="fas fa-tasks text-xl"></i>
            </div>
            <p class="text-gray-500 text-sm font-medium">Tugas Dikumpul</p>
            <h3 class="text-3xl font-bold text-gray-800">{{ number_format($totalSubmissions) }}</h3>
        </div>
    </div>

    <!-- Total Discussions -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-purple-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
        <div class="relative">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center text-white mb-4 shadow-lg shadow-purple-200">
                <i class="fas fa-comments text-xl"></i>
            </div>
            <p class="text-gray-500 text-sm font-medium">Interaksi Diskusi</p>
            <h3 class="text-3xl font-bold text-gray-800">{{ number_format($totalDiscussions) }}</h3>
        </div>
    </div>
</div>

<!-- Level Summary Cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 border border-emerald-200 rounded-2xl p-5 text-center">
        <div class="w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center text-white mx-auto mb-2 shadow-md">
            <i class="fas fa-fire text-sm"></i>
        </div>
        <div class="text-3xl font-black text-emerald-700">{{ $levelSummary['sangat_aktif'] }}</div>
        <div class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mt-1">Sangat Aktif</div>
    </div>
    <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-2xl p-5 text-center">
        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white mx-auto mb-2 shadow-md">
            <i class="fas fa-check-circle text-sm"></i>
        </div>
        <div class="text-3xl font-black text-blue-700">{{ $levelSummary['aktif'] }}</div>
        <div class="text-xs font-semibold text-blue-600 uppercase tracking-wider mt-1">Aktif</div>
    </div>
    <div class="bg-gradient-to-br from-amber-50 to-amber-100 border border-amber-200 rounded-2xl p-5 text-center">
        <div class="w-10 h-10 bg-amber-500 rounded-full flex items-center justify-center text-white mx-auto mb-2 shadow-md">
            <i class="fas fa-exclamation-triangle text-sm"></i>
        </div>
        <div class="text-3xl font-black text-amber-700">{{ $levelSummary['kurang_aktif'] }}</div>
        <div class="text-xs font-semibold text-amber-600 uppercase tracking-wider mt-1">Kurang Aktif</div>
    </div>
    <div class="bg-gradient-to-br from-red-50 to-red-100 border border-red-200 rounded-2xl p-5 text-center">
        <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center text-white mx-auto mb-2 shadow-md">
            <i class="fas fa-times-circle text-sm"></i>
        </div>
        <div class="text-3xl font-black text-red-700">{{ $levelSummary['belum_aktif'] }}</div>
        <div class="text-xs font-semibold text-red-600 uppercase tracking-wider mt-1">Belum Aktif</div>
    </div>
</div>

<!-- Teacher Activity Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
    <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-chalkboard-teacher text-indigo-500"></i> Keaktifan Guru di LMS
            </h3>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <!-- Search -->
                <div class="relative">
                    <input type="text" id="searchTeacher" placeholder="Cari nama guru..."
                        class="pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 w-full sm:w-64 transition-all">
                    <i class="fas fa-search absolute left-3.5 top-3 text-gray-400 text-sm"></i>
                </div>
                <!-- Filter Level -->
                <select id="filterLevel" class="border border-gray-200 rounded-xl text-sm py-2.5 px-4 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition-all appearance-none bg-white">
                    <option value="">Semua Level</option>
                    <option value="sangat_aktif">🟢 Sangat Aktif</option>
                    <option value="aktif">🔵 Aktif</option>
                    <option value="kurang_aktif">🟡 Kurang Aktif</option>
                    <option value="belum_aktif">🔴 Belum Aktif</option>
                </select>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full" id="teacherTable">
            <thead>
                <tr class="bg-gray-50/80">
                    <th class="text-left px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider cursor-pointer hover:text-indigo-600 transition-colors" data-sort="name">
                        Guru <i class="fas fa-sort text-gray-300 ml-1"></i>
                    </th>
                    <th class="text-left px-4 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        Mata Pelajaran
                    </th>
                    <th class="text-center px-3 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider cursor-pointer hover:text-indigo-600 transition-colors" data-sort="courses">
                        Course <i class="fas fa-sort text-gray-300 ml-1"></i>
                    </th>
                    <th class="text-center px-3 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider cursor-pointer hover:text-indigo-600 transition-colors" data-sort="modules">
                        Modul <i class="fas fa-sort text-gray-300 ml-1"></i>
                    </th>
                    <th class="text-center px-3 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider cursor-pointer hover:text-indigo-600 transition-colors" data-sort="materials">
                        Materi <i class="fas fa-sort text-gray-300 ml-1"></i>
                    </th>
                    <th class="text-center px-3 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider cursor-pointer hover:text-indigo-600 transition-colors" data-sort="assignments">
                        Tugas <i class="fas fa-sort text-gray-300 ml-1"></i>
                    </th>
                    <th class="text-center px-3 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider cursor-pointer hover:text-indigo-600 transition-colors" data-sort="quizzes">
                        Quiz <i class="fas fa-sort text-gray-300 ml-1"></i>
                    </th>
                    <th class="text-center px-3 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider cursor-pointer hover:text-indigo-600 transition-colors" data-sort="enrollments">
                        Siswa <i class="fas fa-sort text-gray-300 ml-1"></i>
                    </th>
                    <th class="text-center px-4 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        Aktivitas Terakhir
                    </th>
                    <th class="text-center px-4 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider cursor-pointer hover:text-indigo-600 transition-colors" data-sort="level">
                        Level <i class="fas fa-sort text-gray-300 ml-1"></i>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($teacherActivities as $ta)
                <tr class="hover:bg-indigo-50/30 transition-colors teacher-row cursor-pointer"
                    data-name="{{ strtolower($ta->name) }}"
                    data-level="{{ $ta->level }}"
                    data-courses="{{ $ta->courses_count }}"
                    data-modules="{{ $ta->modules_count }}"
                    data-materials="{{ $ta->materials_count }}"
                    data-assignments="{{ $ta->assignments_count }}"
                    data-quizzes="{{ $ta->quizzes_count }}"
                    data-enrollments="{{ $ta->enrollments_count }}"
                    data-level-score="{{ $ta->level_score }}"
                    data-teacher-id="{{ $ta->id }}"
                    onclick="showTeacherDetail({{ $ta->id }})">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ $ta->photo_url }}" alt="{{ $ta->name }}"
                                class="w-9 h-9 rounded-full object-cover border-2 border-gray-100 flex-shrink-0">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-gray-800 truncate max-w-[180px]">{{ $ta->name }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4">
                        <span class="text-xs text-gray-500 truncate block max-w-[150px]" title="{{ $ta->subjects }}">{{ $ta->subjects }}</span>
                    </td>
                    <td class="px-3 py-4 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-bold {{ $ta->courses_count > 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-50 text-gray-400' }}">
                            {{ $ta->courses_count }}
                        </span>
                    </td>
                    <td class="px-3 py-4 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-bold {{ $ta->modules_count > 0 ? 'bg-violet-100 text-violet-700' : 'bg-gray-50 text-gray-400' }}">
                            {{ $ta->modules_count }}
                        </span>
                    </td>
                    <td class="px-3 py-4 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-bold {{ $ta->materials_count > 0 ? 'bg-cyan-100 text-cyan-700' : 'bg-gray-50 text-gray-400' }}">
                            {{ $ta->materials_count }}
                        </span>
                    </td>
                    <td class="px-3 py-4 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-bold {{ $ta->assignments_count > 0 ? 'bg-orange-100 text-orange-700' : 'bg-gray-50 text-gray-400' }}">
                            {{ $ta->assignments_count }}
                        </span>
                    </td>
                    <td class="px-3 py-4 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-bold {{ $ta->quizzes_count > 0 ? 'bg-pink-100 text-pink-700' : 'bg-gray-50 text-gray-400' }}">
                            {{ $ta->quizzes_count }}
                        </span>
                    </td>
                    <td class="px-3 py-4 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-bold {{ $ta->enrollments_count > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-50 text-gray-400' }}">
                            {{ $ta->enrollments_count }}
                        </span>
                    </td>
                    <td class="px-4 py-4 text-center">
                        @if($ta->last_activity)
                            <span class="text-xs text-gray-500">{{ $ta->last_activity->diffForHumans() }}</span>
                        @else
                            <span class="text-xs text-gray-300">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-4 text-center">
                        @if($ta->level === 'sangat_aktif')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span> Sangat Aktif
                            </span>
                        @elseif($ta->level === 'aktif')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200">
                                <span class="w-2 h-2 bg-blue-500 rounded-full"></span> Aktif
                            </span>
                        @elseif($ta->level === 'kurang_aktif')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                <span class="w-2 h-2 bg-amber-500 rounded-full"></span> Kurang Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                                <span class="w-2 h-2 bg-red-500 rounded-full"></span> Belum Aktif
                            </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Empty State for filtered results -->
    <div id="noResults" class="hidden text-center py-12 text-gray-400">
        <i class="fas fa-search text-4xl mb-3 opacity-20"></i>
        <p>Tidak ada guru yang sesuai dengan filter</p>
    </div>

    <!-- Table Footer with count -->
    <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between">
        <span class="text-sm text-gray-500">
            Menampilkan <strong id="visibleCount">{{ $teacherActivities->count() }}</strong> dari <strong>{{ $teacherActivities->count() }}</strong> guru
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Active Courses -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between bg-gradient-to-r from-gray-50 to-white">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-fire text-orange-500"></i> Materi Paling Aktif
            </h3>
            <span class="text-xs text-gray-400">Berdasarkan Tugas Terkumpul</span>
        </div>
        <div class="p-6">
            <div class="space-y-6">
                @foreach($activeCourses as $course)
                <div class="flex items-center gap-4 group">
                    <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-50 transition-colors">
                        <i class="fas fa-graduation-cap text-indigo-500"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-semibold text-gray-800 truncate">{{ $course->course_name }}</h4>
                        <p class="text-xs text-gray-500">{{ optional($course->teacher->user)->name ?? 'Guru Pengampu' }}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-indigo-600">{{ $course->submissions_count }}</div>
                        <div class="text-[10px] text-gray-400 uppercase font-medium">Submissions</div>
                    </div>
                </div>
                @endforeach
                @if($activeCourses->isEmpty())
                <div class="text-center py-8 text-gray-400">
                    <i class="fas fa-inbox text-4xl mb-3 opacity-20"></i>
                    <p>Belum ada aktivitas pembelajaran utama</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Latest Activities -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between bg-gradient-to-r from-gray-50 to-white">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-history text-blue-500"></i> Aktivitas Terkini
            </h3>
            <span class="animate-pulse flex h-2 w-2 rounded-full bg-red-400"></span>
        </div>
        <div class="p-0">
            <div class="divide-y divide-gray-50">
                @foreach($latestSubmissions as $submission)
                <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0">
                            <i class="fas fa-file-upload text-xs"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-800">
                                <span class="font-bold">{{ optional($submission->student->user)->name ?? 'Siswa' }}</span> 
                                mengumpulkan tugas pada materi 
                                <span class="font-semibold text-indigo-600">{{ optional($submission->assignment->course)->course_name ?? 'Materi' }}</span>
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1 uppercase font-medium">
                                <i class="far fa-clock mr-1"></i> {{ $submission->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
                @if($latestSubmissions->isEmpty())
                <div class="text-center py-12 text-gray-400">
                    <i class="fas fa-stream text-4xl mb-3 opacity-20"></i>
                    <p>Belum ada pengumpulan tugas terbaru hari ini</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Teacher Detail Modal -->
<div id="teacherDetailModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4" onclick="if(event.target===this) closeTeacherDetail()">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl max-h-[85vh] overflow-hidden" onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div class="px-8 py-6 bg-gradient-to-r from-indigo-600 to-violet-600 text-white relative">
            <button onclick="closeTeacherDetail()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition-colors">
                <i class="fas fa-times text-sm"></i>
            </button>
            <div class="flex items-center gap-4">
                <img id="modalTeacherPhoto" src="" alt="" class="w-16 h-16 rounded-2xl object-cover border-2 border-white/30 shadow-lg">
                <div>
                    <h3 id="modalTeacherName" class="text-xl font-bold"></h3>
                    <p class="text-indigo-200 text-sm">Detail Konten LMS</p>
                </div>
            </div>
        </div>
        <!-- Modal Body -->
        <div class="px-8 py-6 overflow-y-auto" style="max-height: calc(85vh - 120px);">
            <div id="modalLoading" class="text-center py-12">
                <div class="inline-flex items-center gap-3 text-gray-500">
                    <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memuat data...
                </div>
            </div>
            <div id="modalContent" class="hidden space-y-5"></div>
            <div id="modalEmpty" class="hidden text-center py-12 text-gray-400">
                <i class="fas fa-folder-open text-5xl mb-4 opacity-20"></i>
                <p class="font-medium">Guru ini belum membuat Course apapun di LMS</p>
                <p class="text-sm mt-1">Belum ada konten pembelajaran yang dibuat</p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ===== Search & Filter =====
const searchInput = document.getElementById('searchTeacher');
const filterSelect = document.getElementById('filterLevel');
const rows = document.querySelectorAll('.teacher-row');
const noResults = document.getElementById('noResults');
const visibleCount = document.getElementById('visibleCount');

function applyFilters() {
    const searchTerm = searchInput.value.toLowerCase().trim();
    const levelFilter = filterSelect.value;
    let count = 0;

    rows.forEach(row => {
        const name = row.getAttribute('data-name');
        const level = row.getAttribute('data-level');
        const matchSearch = !searchTerm || name.includes(searchTerm);
        const matchLevel = !levelFilter || level === levelFilter;

        if (matchSearch && matchLevel) {
            row.classList.remove('hidden');
            count++;
        } else {
            row.classList.add('hidden');
        }
    });

    visibleCount.textContent = count;
    noResults.classList.toggle('hidden', count > 0);
}

searchInput.addEventListener('input', applyFilters);
filterSelect.addEventListener('change', applyFilters);

// ===== Column Sorting =====
let sortColumn = null;
let sortAsc = true;

document.querySelectorAll('th[data-sort]').forEach(th => {
    th.addEventListener('click', () => {
        const col = th.getAttribute('data-sort');
        if (sortColumn === col) {
            sortAsc = !sortAsc;
        } else {
            sortColumn = col;
            sortAsc = true;
        }

        const tbody = document.querySelector('#teacherTable tbody');
        const rowsArr = Array.from(rows);

        rowsArr.sort((a, b) => {
            let valA, valB;
            if (col === 'name') {
                valA = a.getAttribute('data-name');
                valB = b.getAttribute('data-name');
                return sortAsc ? valA.localeCompare(valB) : valB.localeCompare(valA);
            } else if (col === 'level') {
                valA = parseInt(a.getAttribute('data-level-score'));
                valB = parseInt(b.getAttribute('data-level-score'));
            } else {
                valA = parseInt(a.getAttribute('data-' + col));
                valB = parseInt(b.getAttribute('data-' + col));
            }
            return sortAsc ? valA - valB : valB - valA;
        });

        rowsArr.forEach(row => tbody.appendChild(row));

        // Update sort icons
        document.querySelectorAll('th[data-sort] i').forEach(icon => {
            icon.className = 'fas fa-sort text-gray-300 ml-1';
        });
        const icon = th.querySelector('i');
        icon.className = sortAsc ? 'fas fa-sort-up text-indigo-500 ml-1' : 'fas fa-sort-down text-indigo-500 ml-1';
    });
});

// ===== Teacher Detail Modal =====
function showTeacherDetail(teacherId) {
    const modal = document.getElementById('teacherDetailModal');
    const loading = document.getElementById('modalLoading');
    const content = document.getElementById('modalContent');
    const empty = document.getElementById('modalEmpty');

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    loading.classList.remove('hidden');
    content.classList.add('hidden');
    empty.classList.add('hidden');

    fetch(`{{ url('admin/lms/monitoring/teacher') }}/${teacherId}`)
        .then(res => res.json())
        .then(data => {
            loading.classList.add('hidden');

            document.getElementById('modalTeacherName').textContent = data.teacher.name;
            document.getElementById('modalTeacherPhoto').src = data.teacher.photo_url;

            if (data.courses.length === 0) {
                empty.classList.remove('hidden');
                return;
            }

            content.innerHTML = '';
            data.courses.forEach(course => {
                const statusBadge = course.is_published
                    ? '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">Published</span>'
                    : '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500">Draft</span>';

                let modulesHtml = '';
                if (course.modules && course.modules.length > 0) {
                    modulesHtml = '<div class="mt-3 space-y-1.5">';
                    course.modules.forEach(mod => {
                        modulesHtml += `
                            <div class="flex items-center justify-between py-1.5 px-3 bg-gray-50 rounded-lg text-xs">
                                <span class="text-gray-700 font-medium truncate mr-3">${mod.title}</span>
                                <div class="flex gap-3 text-gray-500 flex-shrink-0">
                                    <span title="Materi"><i class="fas fa-file-alt text-cyan-400 mr-1"></i>${mod.materials_count}</span>
                                    <span title="Tugas"><i class="fas fa-clipboard-check text-orange-400 mr-1"></i>${mod.assignments_count}</span>
                                    <span title="Quiz"><i class="fas fa-question-circle text-pink-400 mr-1"></i>${mod.quizzes_count}</span>
                                </div>
                            </div>`;
                    });
                    modulesHtml += '</div>';
                }

                content.innerHTML += `
                    <div class="border border-gray-100 rounded-2xl overflow-hidden hover:border-indigo-200 transition-colors">
                        <div class="p-5">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="text-sm font-bold text-gray-800 truncate">${course.course_name}</h4>
                                        ${statusBadge}
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        ${course.subject || '-'} ${course.classroom ? '• ' + course.classroom : ''}
                                    </p>
                                </div>
                                <span class="text-[10px] text-gray-400 flex-shrink-0 ml-3">${course.updated_at}</span>
                            </div>
                            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 mb-2">
                                <div class="text-center p-2 bg-indigo-50 rounded-xl">
                                    <div class="text-lg font-black text-indigo-600">${course.modules_count}</div>
                                    <div class="text-[9px] uppercase font-semibold text-indigo-400">Modul</div>
                                </div>
                                <div class="text-center p-2 bg-cyan-50 rounded-xl">
                                    <div class="text-lg font-black text-cyan-600">${course.materials_count}</div>
                                    <div class="text-[9px] uppercase font-semibold text-cyan-400">Materi</div>
                                </div>
                                <div class="text-center p-2 bg-orange-50 rounded-xl">
                                    <div class="text-lg font-black text-orange-600">${course.assignments_count}</div>
                                    <div class="text-[9px] uppercase font-semibold text-orange-400">Tugas</div>
                                </div>
                                <div class="text-center p-2 bg-pink-50 rounded-xl">
                                    <div class="text-lg font-black text-pink-600">${course.quizzes_count}</div>
                                    <div class="text-[9px] uppercase font-semibold text-pink-400">Quiz</div>
                                </div>
                                <div class="text-center p-2 bg-emerald-50 rounded-xl">
                                    <div class="text-lg font-black text-emerald-600">${course.enrollments_count}</div>
                                    <div class="text-[9px] uppercase font-semibold text-emerald-400">Siswa</div>
                                </div>
                                <div class="text-center p-2 bg-violet-50 rounded-xl">
                                    <div class="text-lg font-black text-violet-600">${course.submissions_count}</div>
                                    <div class="text-[9px] uppercase font-semibold text-violet-400">Submit</div>
                                </div>
                            </div>
                            ${modulesHtml}
                        </div>
                    </div>`;
            });

            content.classList.remove('hidden');
        })
        .catch(err => {
            loading.classList.add('hidden');
            content.innerHTML = '<div class="text-center py-8 text-red-400"><i class="fas fa-exclamation-circle text-3xl mb-2"></i><p>Gagal memuat data. Silakan coba lagi.</p></div>';
            content.classList.remove('hidden');
        });
}

function closeTeacherDetail() {
    const modal = document.getElementById('teacherDetailModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Close on Escape key
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeTeacherDetail();
});
</script>
@endpush
