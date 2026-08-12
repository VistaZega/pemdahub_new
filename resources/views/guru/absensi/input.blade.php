@extends('layouts.guru')
@section('title', 'Input Absensi Kelas - Portal Guru')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-clipboard-check text-purple-500"></i> Input Absensi Kelas
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Catat kehadiran siswa saat kegiatan belajar mengajar (KBM) berlangsung.
                @if($activeYear) · {{ $activeYear->year }} @endif
            </p>
        </div>
        <a href="{{ route('guru.absensi') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-medium transition">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- Select Classroom & Date --}}
    <form method="GET" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-purple-500 to-indigo-600 px-5 py-3 flex justify-between items-center">
            <h2 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fas fa-search"></i> Pilih Kelas & Tanggal
            </h2>
            <span class="text-xs bg-white/20 text-white px-2 py-1 rounded font-medium">Khusus Kehadiran Kelas/Pelajaran</span>
        </div>
        <div class="p-5 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        <i class="fas fa-school mr-1 text-purple-500"></i> Kelas
                    </label>
                    <select name="classroom_id" class="w-full border-2 border-gray-200 p-2.5 rounded-xl text-sm focus:ring-2 focus:ring-purple-300 focus:border-purple-400 transition" required>
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($classrooms as $cr)
                            <option value="{{ $cr->id }}" {{ $selectedClassroomId == $cr->id ? 'selected' : '' }}>
                                {{ $cr->class_name }} ({{ $cr->students_count ?? 0 }} siswa)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        <i class="fas fa-calendar-alt mr-1 text-purple-500"></i> Tanggal
                    </label>
                    <input type="date" name="date" value="{{ $selectedDate }}"
                           class="w-full border-2 border-gray-200 p-2.5 rounded-xl text-sm focus:ring-2 focus:ring-purple-300 focus:border-purple-400 transition" required>
                </div>
            </div>
            <button type="submit" class="w-full bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white px-6 py-2.5 rounded-xl font-semibold shadow-md transition duration-200 text-sm">
                <i class="fas fa-search mr-1"></i> Tampilkan Siswa
            </button>
        </div>
    </form>

    {{-- Student List for Attendance --}}
    @if($selectedClassroom && $students->count() > 0)
        <form action="{{ route('guru.absensi.store') }}" method="POST">
            @csrf
            <input type="hidden" name="date" value="{{ $selectedDate }}">
            <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-500 to-indigo-600 px-5 py-3 flex items-center justify-between">
                    <h2 class="text-base font-bold text-white flex items-center gap-2">
                        <i class="fas fa-users"></i> {{ $selectedClassroom->class_name }} — {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}
                    </h2>
                    <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">
                        {{ $students->count() }} siswa
                    </span>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-500 p-3 m-4 text-sm text-blue-700 rounded-r-lg">
                    <i class="fas fa-info-circle mr-1"></i> <strong>Informasi:</strong> Form ini adalah untuk mencatat <b>Absensi Belajar (KBM)</b>. Data yang Anda simpan di sini berdiri sendiri (terikat pada jadwal Anda) dan <b>tidak akan menimpa</b> data Absensi Hadir Sekolah (dari mesin tap RFID/Gerbang).
                </div>

                {{-- Quick Actions --}}
                <div class="px-5 py-3 bg-purple-50 border-b border-purple-100 flex flex-wrap gap-2">
                    <span class="text-xs text-purple-600 font-semibold mr-2 self-center">Set Semua:</span>
                    <button type="button" onclick="setAll('hadir')" class="px-3 py-1.5 bg-green-100 text-green-700 rounded-lg text-xs font-bold hover:bg-green-200 transition">
                        <i class="fas fa-check-circle mr-1"></i> Hadir
                    </button>
                    <button type="button" onclick="setAll('sakit')" class="px-3 py-1.5 bg-yellow-100 text-yellow-700 rounded-lg text-xs font-bold hover:bg-yellow-200 transition">
                        <i class="fas fa-briefcase-medical mr-1"></i> Sakit
                    </button>
                    <button type="button" onclick="setAll('izin')" class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg text-xs font-bold hover:bg-blue-200 transition">
                        <i class="fas fa-envelope mr-1"></i> Izin
                    </button>
                    <button type="button" onclick="setAll('alpha')" class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg text-xs font-bold hover:bg-red-200 transition">
                        <i class="fas fa-times-circle mr-1"></i> Alpha
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-5 py-4 text-left font-semibold uppercase tracking-wider text-xs w-12">No</th>
                                <th class="px-5 py-4 text-left font-semibold uppercase tracking-wider text-xs">Identitas Siswa</th>
                                <th class="px-5 py-4 text-center font-semibold uppercase tracking-wider text-xs">Status Kehadiran</th>
                                <th class="px-5 py-4 text-left font-semibold uppercase tracking-wider text-xs">Keterangan / Bukti</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($students as $i => $student)
                                @php
                                    $existing = $existingAttendances->get($student->id);
                                    $currentStatus = old("statuses.{$student->id}", $existing?->status ?? 'hadir');
                                    $currentNote = old("notes.{$student->id}", $existing?->notes ?? '');
                                    $isRfid = $existing && $existing->recorded_via === 'rfid';
                                @endphp
                                <tr class="hover:bg-indigo-50/10 transition-all duration-200 attendance-row {{ $isRfid ? 'bg-indigo-50/20' : '' }}">
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-50 text-gray-500 rounded-xl font-bold text-xs ring-1 ring-gray-100">{{ $i + 1 }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-xl flex-shrink-0 overflow-hidden ring-2 ring-white shadow-sm">
                                                <img src="{{ $student->photo_url }}" class="w-full h-full object-cover" alt="{{ $student->full_name }}">
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <p class="font-bold text-gray-800 truncate">{{ $student->full_name }}</p>
                                                    @if($isRfid)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-500 text-white shadow-sm shadow-indigo-200 uppercase tracking-wider animate-pulse">
                                                            <i class="fas fa-wifi mr-1"></i> RFID Tap
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-2 text-xs text-gray-400 font-medium">
                                                    <span>{{ $student->nisn ?: $student->nis ?: 'No ID' }}</span>
                                                    @if($student->gender == 'L')
                                                        <span class="text-blue-500 flex items-center gap-0.5"><i class="fas fa-mars mr-0.5 text-xs"></i> Laki-laki</span>
                                                    @else
                                                        <span class="text-pink-500 flex items-center gap-0.5"><i class="fas fa-venus mr-0.5 text-xs"></i> Perempuan</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <div class="relative inline-block w-full max-w-[140px]">
                                            <select name="statuses[{{ $student->id }}]"
                                                    class="status-select w-full border-gray-200 bg-gray-50 p-2.5 rounded-xl text-xs font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all appearance-none cursor-pointer"
                                                    onchange="updateRowColor(this)">
                                                <option value="hadir" {{ $currentStatus == 'hadir' ? 'selected' : '' }}>✅ HADIR</option>
                                                <option value="izin" {{ $currentStatus == 'izin' ? 'selected' : '' }}>📩 IZIN</option>
                                                <option value="sakit" {{ $currentStatus == 'sakit' ? 'selected' : '' }}>🏥 SAKIT</option>
                                                <option value="alpha" {{ $currentStatus == 'alpha' ? 'selected' : '' }}>❌ ALPHA</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400">
                                                <i class="fas fa-chevron-down text-xs"></i>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-500">
                                                <i class="fas fa-sticky-note text-xs"></i>
                                            </div>
                                            <input type="text" name="notes[{{ $student->id }}]" value="{{ $currentNote }}"
                                                class="w-full pl-9 pr-4 py-2.5 bg-gray-50/50 border-gray-100 rounded-xl text-xs focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all text-gray-600 font-medium"
                                                placeholder="Berikan alasan (opsional)...">
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Summary Counter --}}
                <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
                    <div class="flex flex-wrap gap-4 text-xs font-semibold" id="attendance-summary">
                        <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i> Hadir: <span id="count-hadir">0</span></span>
                        <span class="text-yellow-600"><i class="fas fa-briefcase-medical mr-1"></i> Sakit: <span id="count-sakit">0</span></span>
                        <span class="text-blue-600"><i class="fas fa-envelope mr-1"></i> Izin: <span id="count-izin">0</span></span>
                        <span class="text-red-600"><i class="fas fa-times-circle mr-1"></i> Alpha: <span id="count-alpha">0</span></span>
                    </div>
                </div>

                <div class="p-5 flex justify-end gap-3 border-t border-gray-100">
                    <a href="{{ route('guru.absensi') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-medium transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white rounded-xl text-sm font-semibold shadow-md transition duration-200">
                        <i class="fas fa-save mr-1"></i> Simpan Absensi
                    </button>
                </div>
            </div>
        </form>
    @elseif($selectedClassroom && $students->count() === 0)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
            <i class="fas fa-user-slash text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">Tidak ada siswa aktif di kelas {{ $selectedClassroom->class_name }}.</p>
        </div>
    @elseif($selectedClassroomId && !$selectedClassroom)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
            <i class="fas fa-exclamation-circle text-4xl text-red-300 mb-3"></i>
            <p class="text-gray-500">Kelas tidak ditemukan atau Anda tidak mengajar di kelas ini.</p>
        </div>
    @endif
</div>

@push('scripts')
<script>
    function setAll(status) {
        document.querySelectorAll('.status-select').forEach(select => {
            select.value = status;
            updateRowColor(select);
        });
        updateSummary();
    }

    function updateRowColor(select) {
        const row = select.closest('tr');
        row.className = 'hover:bg-gray-50 transition attendance-row';
        const colors = {
            'hadir': '',
            'sakit': 'bg-yellow-50',
            'izin': 'bg-blue-50',
            'alpha': 'bg-red-50',
        };
        if (colors[select.value]) {
            row.classList.add(colors[select.value]);
        }
        updateSummary();
    }

    function updateSummary() {
        const counts = { hadir: 0, sakit: 0, izin: 0, alpha: 0 };
        document.querySelectorAll('.status-select').forEach(select => {
            counts[select.value]++;
        });
        Object.keys(counts).forEach(key => {
            const el = document.getElementById('count-' + key);
            if (el) el.textContent = counts[key];
        });
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.status-select').forEach(select => updateRowColor(select));
        updateSummary();
    });
</script>
@endpush
@endsection
