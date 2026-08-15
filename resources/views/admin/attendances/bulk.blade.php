@extends('layouts.admin')

@section('title', 'Input Absensi Kelas - Admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.attendances.index', ['date' => $selectedDate, 'classroom_id' => $selectedClassroom]) }}"
               class="p-2.5 bg-white border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition-colors shadow-sm"
               title="Kembali ke Daftar Absensi">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center shadow-lg text-white">
                <i class="fas fa-clipboard-check text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Input Absensi Kelas</h1>
                <p class="text-gray-500 text-sm">
                    @if($classroom)
                        Kelas <span class="font-semibold text-gray-700">{{ $classroom->class_name }}</span> &middot; {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }}
                    @else
                        Pilih kelas dan tanggal untuk mengisi absensi massal
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Filter Kelas & Tanggal Form -->
    <form method="GET" action="{{ route('admin.attendances.bulk') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                    <i class="fas fa-calendar-alt text-emerald-600 mr-1"></i> Tanggal Absensi
                </label>
                <input type="date" name="date" 
                       class="w-full border border-gray-200 p-2.5 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-gray-50/50" 
                       value="{{ $selectedDate }}" required onchange="this.form.submit()">
            </div>

            @if($isSuperAdmin)
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                    <i class="fas fa-university text-emerald-600 mr-1"></i> Sekolah
                </label>
                <select name="school_id" class="w-full border border-gray-200 p-2.5 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-gray-50/50" onchange="this.form.submit()">
                    <option value="">Semua Sekolah</option>
                    @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ $selectedSchoolId == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="{{ $isSuperAdmin ? '' : 'md:col-span-2' }}">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                    <i class="fas fa-school text-emerald-600 mr-1"></i> Pilih Kelas
                </label>
                <select name="classroom_id" class="w-full border border-gray-200 p-2.5 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-gray-50/50" required onchange="this.form.submit()">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($classrooms as $c)
                    <option value="{{ $c->id }}" {{ $selectedClassroom == $c->id ? 'selected' : '' }}>
                        {{ $c->class_name }} {{ $isSuperAdmin ? '('. ($c->school->name ?? '-') .')' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    @if(isset($students) && count($students) > 0)
    @php
        $totalStudents = count($students);
        $recordedCount = $existingAttendances->count();
        $unrecordedCount = max(0, $totalStudents - $recordedCount);
        $hadirCount = $existingAttendances->where('status', 'hadir')->count();
        $terlambatCount = $existingAttendances->where('status', 'terlambat')->count();
        $izinCount = $existingAttendances->where('status', 'izin')->count();
        $sakitCount = $existingAttendances->where('status', 'sakit')->count();
        $alphaCount = $existingAttendances->where('status', 'alpha')->count();
    @endphp

    <!-- Summary Stats & Status Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
        <div class="bg-white rounded-xl p-3 border border-gray-100 shadow-sm flex flex-col justify-between">
            <span class="text-xs text-gray-400 font-medium">Total Siswa</span>
            <span class="text-xl font-bold text-gray-800">{{ $totalStudents }}</span>
        </div>
        <div class="bg-white rounded-xl p-3 border border-gray-100 shadow-sm flex flex-col justify-between">
            <span class="text-xs text-emerald-600 font-medium">Tersimpan</span>
            <div class="flex items-baseline gap-1">
                <span class="text-xl font-bold text-emerald-600">{{ $recordedCount }}</span>
                <span class="text-xs text-gray-400">/ {{ $totalStudents }}</span>
            </div>
        </div>
        <div class="bg-white rounded-xl p-3 border border-gray-100 shadow-sm flex flex-col justify-between">
            <span class="text-xs text-green-600 font-medium"><i class="fas fa-check-circle mr-1"></i> Hadir</span>
            <span class="text-xl font-bold text-green-600">{{ $hadirCount }}</span>
        </div>
        <div class="bg-white rounded-xl p-3 border border-gray-100 shadow-sm flex flex-col justify-between">
            <span class="text-xs text-amber-600 font-medium"><i class="fas fa-clock mr-1"></i> Terlambat</span>
            <span class="text-xl font-bold text-amber-600">{{ $terlambatCount }}</span>
        </div>
        <div class="bg-white rounded-xl p-3 border border-gray-100 shadow-sm flex flex-col justify-between">
            <span class="text-xs text-blue-600 font-medium"><i class="fas fa-envelope-open-text mr-1"></i> Izin</span>
            <span class="text-xl font-bold text-blue-600">{{ $izinCount }}</span>
        </div>
        <div class="bg-white rounded-xl p-3 border border-gray-100 shadow-sm flex flex-col justify-between">
            <span class="text-xs text-purple-600 font-medium"><i class="fas fa-head-side-cough mr-1"></i> Sakit</span>
            <span class="text-xl font-bold text-purple-600">{{ $sakitCount }}</span>
        </div>
        <div class="bg-white rounded-xl p-3 border border-gray-100 shadow-sm flex flex-col justify-between">
            <span class="text-xs text-rose-600 font-medium"><i class="fas fa-times-circle mr-1"></i> Alpha</span>
            <span class="text-xl font-bold text-rose-600">{{ $alphaCount }}</span>
        </div>
    </div>

    <!-- Form Absensi -->
    <form action="{{ route('admin.attendances.bulkStore') }}" method="POST" id="bulkAttendanceForm">
        @csrf
        <input type="hidden" name="date" value="{{ $selectedDate }}">
        <input type="hidden" name="classroom_id" value="{{ $selectedClassroom }}">

        <!-- Quick Actions Toolbar -->
        <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 p-4 mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider mr-1">
                    <i class="fas fa-bolt text-amber-500 mr-1"></i> Quick Action:
                </span>
                <button type="button" onclick="setAllStatus('hadir')"
                        class="px-3 py-1.5 bg-green-50 text-green-700 border border-green-200 rounded-lg text-xs font-semibold hover:bg-green-100 transition-all flex items-center gap-1.5 shadow-sm">
                    <i class="fas fa-check-double"></i> Set Semua Hadir
                </button>
                <button type="button" onclick="setUnrecordedToHadir()"
                        class="px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-semibold hover:bg-emerald-100 transition-all flex items-center gap-1.5 shadow-sm"
                        title="Hanya mengisi siswa yang belum memiliki data absensi menjadi Hadir, tanpa mengubah siswa yang sudah Izin/Sakit/Alpha/RFID">
                    <i class="fas fa-user-check"></i> Isi yang Belum Ada &rarr; Hadir
                </button>
                <button type="button" onclick="resetToInitial()"
                        class="px-3 py-1.5 bg-gray-50 text-gray-600 border border-gray-200 rounded-lg text-xs font-semibold hover:bg-gray-100 transition-all flex items-center gap-1.5 shadow-sm">
                    <i class="fas fa-undo"></i> Reset ke Data Awal
                </button>
            </div>
            <div class="text-xs text-gray-400 font-medium">
                {{ $totalStudents }} siswa di kelas ini
            </div>
        </div>

        <!-- Table Siswa -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/80 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider w-12 text-center">No</th>
                            <th class="px-4 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Identitas Siswa</th>
                            <th class="px-4 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider w-40 text-center">Status di Database</th>
                            <th class="px-4 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider w-44 text-center">Pilihan Status</th>
                            <th class="px-4 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Keterangan / Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($students as $index => $student)
                        @php
                            $existing = $existingAttendances->get($student->id);
                            $initialStatus = $existing ? $existing->status : 'hadir';
                            $currentStatus = old("statuses.{$student->id}", $initialStatus);
                            $currentNote = old("notes.{$student->id}", $existing?->notes ?? '');
                            $hasExisting = !is_null($existing);
                            $isRfid = $existing && $existing->recorded_via === 'rfid';
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors student-row {{ $hasExisting ? 'bg-slate-50/30' : '' }}"
                            data-student-id="{{ $student->id }}"
                            data-has-existing="{{ $hasExisting ? '1' : '0' }}"
                            data-initial-status="{{ $initialStatus }}"
                            data-initial-note="{{ $existing?->notes ?? '' }}">
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-7 h-7 bg-gray-100 text-gray-600 rounded-lg text-xs font-semibold">
                                    {{ $index + 1 }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center flex-shrink-0 text-white font-bold text-xs shadow-sm overflow-hidden ring-1 ring-gray-100">
                                        @if($student->photo)
                                            <img src="{{ $student->photo_url }}" alt="{{ $student->full_name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr($student->full_name, 0, 2)) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="font-bold text-gray-800 text-sm truncate">{{ $student->full_name }}</p>
                                            @if($isRfid)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-100 text-indigo-700 border border-indigo-200" title="Absen otomatis via RFID">
                                                    <i class="fas fa-wifi text-[9px] mr-1"></i> RFID
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 text-xs text-gray-400 mt-0.5">
                                            <span>NISN: {{ $student->nisn ?: ($student->nis ?: '-') }}</span>
                                            <span>&middot;</span>
                                            @if($student->gender == 'L')
                                                <span class="text-blue-600 font-medium"><i class="fas fa-mars text-[10px]"></i> L</span>
                                            @else
                                                <span class="text-pink-600 font-medium"><i class="fas fa-venus text-[10px]"></i> P</span>
                                            @endif
                                            @if($existing && ($existing->time_in || $existing->time_out))
                                                <span>&middot;</span>
                                                <span class="text-gray-500"><i class="fas fa-clock text-[10px] mr-0.5"></i> {{ substr($existing->time_in ?? '-', 0, 5) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($existing)
                                    @switch($existing->status)
                                        @case('hadir')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-green-100 text-green-700 border border-green-200">
                                                <i class="fas fa-check-circle mr-1"></i> Hadir
                                            </span>
                                            @break
                                        @case('terlambat')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200">
                                                <i class="fas fa-clock mr-1"></i> Terlambat
                                            </span>
                                            @break
                                        @case('izin')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-200">
                                                <i class="fas fa-envelope-open-text mr-1"></i> Izin
                                            </span>
                                            @break
                                        @case('sakit')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-purple-100 text-purple-700 border border-purple-200">
                                                <i class="fas fa-head-side-cough mr-1"></i> Sakit
                                            </span>
                                            @break
                                        @case('alpha')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-rose-100 text-rose-700 border border-rose-200">
                                                <i class="fas fa-times-circle mr-1"></i> Alpha
                                            </span>
                                            @break
                                        @default
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700">
                                                {{ ucfirst($existing->status) }}
                                            </span>
                                    @endswitch
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-400 border border-gray-200">
                                        <i class="fas fa-minus mr-1"></i> Belum ada
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <select name="statuses[{{ $student->id }}]" 
                                        class="status-select w-full border border-gray-200 p-2.5 rounded-xl text-xs font-bold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition cursor-pointer"
                                        onchange="updateSelectStyle(this)">
                                    <option value="hadir" {{ $currentStatus == 'hadir' ? 'selected' : '' }}>✅ Hadir</option>
                                    <option value="terlambat" {{ $currentStatus == 'terlambat' ? 'selected' : '' }}>⏰ Terlambat</option>
                                    <option value="izin" {{ $currentStatus == 'izin' ? 'selected' : '' }}>📩 Izin</option>
                                    <option value="sakit" {{ $currentStatus == 'sakit' ? 'selected' : '' }}>🏥 Sakit</option>
                                    <option value="alpha" {{ $currentStatus == 'alpha' ? 'selected' : '' }}>❌ Alpha</option>
                                </select>
                            </td>
                            <td class="px-4 py-3">
                                <input type="text" name="notes[{{ $student->id }}]" 
                                       value="{{ $currentNote }}"
                                       class="note-input border border-gray-200 p-2.5 rounded-xl w-full text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-gray-50/50" 
                                       placeholder="Keterangan (opsional, misal: Sakit demam, Izin acara keluarga)">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Footer Action -->
            <div class="p-5 bg-gray-50/80 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-gray-500">
                    <i class="fas fa-info-circle text-emerald-600 mr-1"></i>
                    Pastikan status yang dipilih sudah sesuai sebelum menekan tombol simpan.
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.attendances.index', ['date' => $selectedDate, 'classroom_id' => $selectedClassroom]) }}"
                       class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-xs font-semibold hover:bg-gray-50 transition shadow-sm">
                        Batal
                    </a>
                    <button type="submit" id="submitBtn"
                            class="px-7 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-500/20 transition duration-200 flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Semua Absensi
                    </button>
                </div>
            </div>
        </div>
    </form>
    @elseif(isset($selectedClassroom) && $selectedClassroom)
    <div class="bg-amber-50 border border-amber-200 p-6 rounded-2xl text-center shadow-sm">
        <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-user-graduate text-xl"></i>
        </div>
        <h3 class="text-base font-bold text-amber-800">Tidak ada siswa aktif</h3>
        <p class="text-xs text-amber-600 mt-1">Tidak ditemukan data siswa aktif yang terdaftar di kelas ini pada Tahun Pelajaran aktif.</p>
    </div>
    @endif
</div>

<script>
// Fungsi update warna select sesuai status
function updateSelectStyle(selectElement) {
    const val = selectElement.value;
    selectElement.classList.remove('bg-green-50', 'text-green-800', 'border-green-300',
                                  'bg-amber-50', 'text-amber-800', 'border-amber-300',
                                  'bg-blue-50', 'text-blue-800', 'border-blue-300',
                                  'bg-purple-50', 'text-purple-800', 'border-purple-300',
                                  'bg-rose-50', 'text-rose-800', 'border-rose-300');
    
    if (val === 'hadir') {
        selectElement.classList.add('bg-green-50', 'text-green-800', 'border-green-300');
    } else if (val === 'terlambat') {
        selectElement.classList.add('bg-amber-50', 'text-amber-800', 'border-amber-300');
    } else if (val === 'izin') {
        selectElement.classList.add('bg-blue-50', 'text-blue-800', 'border-blue-300');
    } else if (val === 'sakit') {
        selectElement.classList.add('bg-purple-50', 'text-purple-800', 'border-purple-300');
    } else if (val === 'alpha') {
        selectElement.classList.add('bg-rose-50', 'text-rose-800', 'border-rose-300');
    }
}

// Inisialisasi warna select saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.status-select').forEach(function(sel) {
        updateSelectStyle(sel);
    });
});

// Quick Action: Set Semua Hadir
function setAllStatus(status) {
    document.querySelectorAll('.status-select').forEach(function(sel) {
        sel.value = status;
        updateSelectStyle(sel);
    });
}

// Quick Action: Hanya ubah siswa yang belum ada data absensi di DB menjadi Hadir
function setUnrecordedToHadir() {
    document.querySelectorAll('.student-row').forEach(function(row) {
        const hasExisting = row.getAttribute('data-has-existing') === '1';
        if (!hasExisting) {
            const sel = row.querySelector('.status-select');
            if (sel) {
                sel.value = 'hadir';
                updateSelectStyle(sel);
            }
        }
    });
}

// Quick Action: Reset semua select ke data awal saat halaman dibuka
function resetToInitial() {
    document.querySelectorAll('.student-row').forEach(function(row) {
        const initialStatus = row.getAttribute('data-initial-status') || 'hadir';
        const initialNote = row.getAttribute('data-initial-note') || '';
        
        const sel = row.querySelector('.status-select');
        if (sel) {
            sel.value = initialStatus;
            updateSelectStyle(sel);
        }

        const note = row.querySelector('.note-input');
        if (note) {
            note.value = initialNote;
        }
    });
}

// Form submit loading state
document.getElementById('bulkAttendanceForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan Absensi...';
    }
});
</script>
@endsection