@extends('layouts.guru')
@section('title', 'Input Nilai - Portal Guru')

@section('content')
<div class="space-y-6">
    {{-- Header Banner (Neo-Brutalism) --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl p-6 border-2 border-black" style="background: linear-gradient(135deg, #090d16 0%, #065f46 50%, #047857 100%) !important; color: #ffffff !important;">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-black text-white flex items-center gap-3" style="color: #ffffff !important;">
                    <div class="w-10 h-10 rounded-xl bg-amber-400 border-2 border-black flex items-center justify-center text-black shadow-sm text-lg">
                        <i class="fas fa-edit text-black"></i>
                    </div>
                    Input Nilai Siswa
                </h1>
                <p class="text-xs md:text-sm font-bold text-emerald-200 mt-1" style="color: #a7f3d0 !important;">Form pengisian dan pembaruan nilai harian, PTS, & PAS</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('guru.nilai') }}" class="text-xs bg-white hover:bg-slate-100 text-black border-2 border-black font-black px-4 py-2.5 rounded-2xl transition uppercase tracking-wider shadow-md">
                    <i class="fas fa-list mr-1"></i> Lihat Nilai
                </a>
                <a href="{{ route('guru.nilai.summary') }}" class="text-xs bg-amber-400 hover:bg-amber-300 text-black border-2 border-black font-black px-4 py-2.5 rounded-2xl transition uppercase tracking-wider shadow-md">
                    <i class="fas fa-chart-bar mr-1"></i> Rekap Nilai
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-500 mr-3"></i>
            <span class="text-green-700 font-medium">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
            <span class="text-red-700 font-medium">{{ session('error') }}</span>
        </div>
    </div>
    @endif

    {{-- Filter Form --}}
    <form method="GET" action="{{ route('guru.nilai.input') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" data-no-auto-submit="true">
        <div class="bg-gradient-to-r from-emerald-500 to-teal-600 px-5 py-3">
            <h2 class="text-white font-bold flex items-center gap-2">
                <i class="fas fa-filter"></i> Pilih Kelas, Mapel, Jenis & Komponen Nilai
            </h2>
        </div>
        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            {{-- Semester --}}
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5">Semester</label>
                <select name="semester_id" onchange="this.form.submit()" class="w-full text-base font-bold text-gray-800 bg-white border border-gray-300 rounded-xl px-4 py-2.5 shadow-sm focus:ring-2 focus:ring-emerald-300 focus:border-emerald-500 transition">
                    @foreach($semesters as $sem)
                        <option value="{{ $sem->id }}" class="text-gray-850 font-semibold" {{ $selectedSemesterId == $sem->id ? 'selected' : '' }}>
                            {{ $sem->semester_name ?? 'Semester '.$sem->semester_number }} - {{ $sem->academicYear->year ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Kelas --}}
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5">Kelas</label>
                <select name="classroom_id" onchange="this.form.submit()" class="w-full text-base font-bold text-gray-800 bg-white border border-gray-300 rounded-xl px-4 py-2.5 shadow-sm focus:ring-2 focus:ring-emerald-300 focus:border-emerald-500 transition">
                    <option value="" class="text-gray-850">-- Pilih Kelas --</option>
                    @foreach($classrooms as $cr)
                        <option value="{{ $cr->id }}" class="text-gray-850 font-semibold" {{ $selectedClassroomId == $cr->id ? 'selected' : '' }}>
                            {{ $cr->class_name }} ({{ $cr->school->name ?? '' }}){{ $cr->is_homeroom ? ' ★ Wali' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Mata Pelajaran --}}
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5">Mata Pelajaran</label>
                <select name="subject_id" onchange="this.form.submit()" class="w-full text-base font-bold text-gray-800 bg-white border border-gray-300 rounded-xl px-4 py-2.5 shadow-sm focus:ring-2 focus:ring-emerald-300 focus:border-emerald-500 transition" @if(!$selectedClassroomId) disabled @endif>
                    <option value="" class="text-gray-850">-- Pilih Mapel --</option>
                    @foreach($subjects as $subj)
                        <option value="{{ $subj->id }}" class="text-gray-850 font-semibold" {{ $selectedSubjectId == $subj->id ? 'selected' : '' }}>
                            {{ $subj->subject_name ?? $subj->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Jenis Nilai --}}
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5">Jenis Nilai</label>
                <select name="grade_type" onchange="this.form.submit()" class="w-full text-base font-bold text-gray-800 bg-white border border-gray-300 rounded-xl px-4 py-2.5 shadow-sm focus:ring-2 focus:ring-emerald-300 focus:border-emerald-500 transition">
                    <option value="tugas" class="text-gray-850 font-semibold" {{ $selectedGradeType == 'tugas' ? 'selected' : '' }}>Tugas/Harian</option>
                    <option value="uts" class="text-gray-850 font-semibold" {{ $selectedGradeType == 'uts' ? 'selected' : '' }}>PTS (UTS)</option>
                    <option value="uas" class="text-gray-850 font-semibold" {{ $selectedGradeType == 'uas' ? 'selected' : '' }}>PAS (UAS)</option>
                    <option value="sikap" class="text-gray-850 font-semibold" {{ $selectedGradeType == 'sikap' ? 'selected' : '' }}>Sikap</option>
                </select>
            </div>

            {{-- Komponen Nilai --}}
            <div x-data="{ isNew: {{ empty($selectedComponent) || !$existingComponents->contains($selectedComponent) ? 'true' : 'false' }} }">
                <label class="block text-sm font-bold text-gray-700 mb-1.5">Komponen Nilai</label>
                <div class="flex flex-col gap-1.5">
                    <select name="component_select" 
                            x-show="!isNew" 
                            onchange="if(this.value === '__new__') { document.getElementById('component_name_input').value = ''; this.form.submit(); } else { document.getElementById('component_name_input').value = this.value; this.form.submit(); }"
                            class="w-full text-base font-bold text-gray-800 bg-white border border-gray-300 rounded-xl px-4 py-2.5 shadow-sm focus:ring-2 focus:ring-emerald-300 focus:border-emerald-500 transition"
                            @if(!$selectedSubjectId) disabled @endif>
                        @foreach($existingComponents as $comp)
                            <option value="{{ $comp }}" class="text-gray-850 font-semibold" {{ $selectedComponent == $comp ? 'selected' : '' }}>{{ $comp }}</option>
                        @endforeach
                        <option value="__new__" class="text-gray-850 font-bold text-emerald-650" {{ empty($selectedComponent) || !$existingComponents->contains($selectedComponent) ? 'selected' : '' }}>+ Tambah Baru...</option>
                    </select>
                    
                    <div x-show="isNew" class="flex gap-1 animate-fadeIn">
                        <input type="text" 
                               id="component_name_input"
                               name="component_name" 
                               value="{{ $selectedComponent }}"
                               placeholder="Contoh: Tugas 1"
                               class="w-full text-base font-bold text-gray-800 bg-white border border-gray-300 rounded-xl px-4 py-2.5 shadow-sm focus:ring-2 focus:ring-emerald-300 focus:border-emerald-500 transition"
                               @if(!$selectedSubjectId) disabled @endif>
                        @if($existingComponents->isNotEmpty())
                        <button type="button" 
                                @click="isNew = false; document.getElementsByName('component_select')[0].value = '{{ $existingComponents->first() }}'; document.getElementById('component_name_input').value = '{{ $existingComponents->first() }}'; document.forms[0].submit();"
                                class="px-3.5 py-2.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-650 rounded-xl border border-gray-300 transition shadow-sm font-semibold"
                                title="Batal & Pilih Komponen yang Ada">
                            <i class="fas fa-undo"></i>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Grade Weight Info --}}
    @if($gradeWeight)
    <div class="bg-blue-50 rounded-xl border border-blue-200 p-4">
        <div class="flex items-start gap-3">
            <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
            <div class="text-sm text-blue-700">
                <span class="font-semibold">Bobot Nilai Sekolah:</span>
                Tugas <span class="font-bold">{{ number_format($gradeWeight->tugas_weight, 0) }}%</span> |  
                PTS <span class="font-bold">{{ number_format($gradeWeight->pts_weight, 0) }}%</span> |  
                PAS <span class="font-bold">{{ number_format($gradeWeight->pas_weight, 0) }}%</span> |  
                Sikap <span class="font-bold">{{ number_format($gradeWeight->sikap_weight, 0) }}%</span>
                @if($isHomeroom)
                <span class="ml-3 inline-flex items-center gap-1 bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full text-xs font-semibold">
                    <i class="fas fa-star text-amber-500"></i> Anda adalah Wali Kelas — dapat mengakses semua mata pelajaran
                </span>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- LMS Integration Info --}}
    @if($selectedGradeType == 'tugas' && $selectedSubjectId && $lmsGrades->flatten()->count() > 0)
    <div class="bg-purple-50 rounded-xl border border-purple-200 p-4">
        <div class="flex items-start gap-3">
            <i class="fas fa-laptop text-purple-500 mt-0.5"></i>
            <div class="text-sm text-purple-700">
                <span class="font-semibold">Nilai dari LMS Terdeteksi:</span>
                Terdapat <strong>{{ $lmsGrades->flatten()->count() }}</strong> nilai tugas yang otomatis tersinkron dari quiz dan tugas LMS.
                Nilai LMS ditampilkan di kolom terpisah dan <strong>turut dihitung</strong> sebagai komponen rata-rata Tugas.
                <br><span class="text-xs text-purple-500">Kolom "Nilai" untuk input manual, kolom "Nilai LMS" adalah read-only dari sistem LMS.</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Spreadsheet Input --}}
    @if($selectedClassroomId && $selectedSubjectId && $students->count() > 0)
    <form action="{{ route('guru.nilai.store-bulk') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" id="gradeForm" onsubmit="const newNameInput = document.getElementById('component_name_input'); if(newNameInput) { document.getElementById('hidden_component_name').value = newNameInput.value; }">
        @csrf
        <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
        <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
        <input type="hidden" name="grade_type" value="{{ $selectedGradeType }}">
        <input type="hidden" name="semester_id" value="{{ $selectedSemesterId }}">
        <input type="hidden" id="hidden_component_name" name="component_name" value="{{ $selectedComponent }}">        <div class="px-5 py-4 border-b border-gray-150 flex items-center justify-between">
            <h2 class="font-extrabold text-gray-800 text-lg flex items-center gap-2">
                <i class="fas fa-table text-emerald-500"></i>
                Input Nilai {{ ['tugas' => 'Tugas/Harian', 'uts' => 'PTS (UTS)', 'uas' => 'PAS (UAS)', 'sikap' => 'Sikap'][$selectedGradeType] ?? '' }} &mdash; <span class="text-emerald-600 font-bold">{{ $selectedComponent }}</span>
            </h2>
            <span class="text-xs bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full font-bold shadow-sm">{{ $students->count() }} siswa</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-150">
                    <tr>
                        <th class="px-5 py-4 text-left font-bold text-gray-500 text-xs uppercase tracking-wider w-12">#</th>
                        <th class="px-5 py-4 text-left font-bold text-gray-500 text-xs uppercase tracking-wider w-28">NIS</th>
                        <th class="px-5 py-4 text-left font-bold text-gray-500 text-xs uppercase tracking-wider">Nama Siswa</th>
                        <th class="px-5 py-4 text-center font-bold text-gray-500 text-xs uppercase tracking-wider w-36">Nilai (0-100)</th>
                        <th class="px-5 py-4 text-center font-bold text-gray-500 text-xs uppercase tracking-wider w-28">Sumber</th>
                        @if($selectedGradeType == 'tugas')
                        <th class="px-5 py-4 text-center font-bold text-gray-500 text-xs uppercase tracking-wider w-40">Nilai LMS</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($students as $idx => $student)
                        @php
                            $existing = $existingGrades->get($student->id);
                            $currentScore = old("scores.{$student->id}", $existing?->score);
                            $studentLmsGrades = $lmsGrades->get($student->id, collect());
                        @endphp
                        <tr class="hover:bg-gray-50 transition group" data-row="{{ $idx }}">
                            <td class="px-5 py-4 text-gray-400 text-xs font-mono">{{ $idx + 1 }}</td>
                            <td class="px-5 py-4 text-gray-500 text-xs font-mono">{{ $student->nisn ?? $student->nis ?? '-' }}</td>
                            <td class="px-5 py-4 font-bold text-gray-800 text-base">{{ $student->full_name }}</td>
                            <td class="px-5 py-4 text-center">
                                <input type="number" 
                                       name="scores[{{ $student->id }}]" 
                                       value="{{ $currentScore !== null ? ($currentScore == intval($currentScore) ? number_format($currentScore, 0) : number_format($currentScore, 1)) : '' }}"
                                       min="0" max="100" step="any"
                                       class="w-28 text-center border border-gray-300 rounded-xl px-3 py-2 text-base font-bold focus:ring-2 focus:ring-emerald-300 focus:border-emerald-500 score-input shadow-sm {{ $currentScore !== null ? ($currentScore >= 80 ? 'bg-green-50 text-green-700 border-green-300' : ($currentScore >= 60 ? 'bg-yellow-50 text-yellow-700 border-yellow-300' : 'bg-red-50 text-red-700 border-red-300')) : '' }}"
                                       placeholder="-"
                                       data-student-id="{{ $student->id }}"
                                       tabindex="{{ $idx + 1 }}">
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($existing)
                                    <span class="text-xs bg-blue-100 text-blue-750 px-2.5 py-1 rounded-lg border border-blue-200 font-bold shadow-sm">Manual</span>
                                @else
                                    <span class="text-xs bg-gray-150 text-gray-550 px-2.5 py-1 rounded-lg border border-gray-200 font-bold shadow-sm">Belum</span>
                                @endif
                            </td>
                            @if($selectedGradeType == 'tugas')
                            <td class="px-5 py-4 text-center">
                                @if($studentLmsGrades->count() > 0)
                                    <div class="flex flex-wrap justify-center gap-1.5">
                                        @foreach($studentLmsGrades as $lmsGrade)
                                            <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-bold border shadow-sm {{ $lmsGrade->score >= 80 ? 'bg-purple-100 text-purple-700 border-purple-200' : ($lmsGrade->score >= 60 ? 'bg-purple-50 text-purple-600 border-purple-100' : 'bg-red-100 text-red-600 border-red-200') }}"
                                                title="{{ $lmsGrade->notes }}">
                                                <i class="fas fa-laptop text-xs mr-0.5"></i>{{ number_format($lmsGrade->score, 0) }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 font-medium">-</span>
                                @endif
                            </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-5 bg-gray-50 border-t border-gray-150 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-6 text-base text-gray-700 font-semibold">
                <span>Terisi: <strong id="filledCount" class="text-emerald-600 text-lg">{{ $existingGrades->count() }}</strong>/{{ $students->count() }}</span>
                <span>Rata-rata: <strong id="avgScore" class="text-blue-600 text-lg">-</strong></span>
            </div>
            <div class="flex gap-2.5">
                <button type="button" onclick="fillAll()" class="px-5 py-2.5 text-sm bg-gray-200 hover:bg-gray-300 text-gray-750 rounded-xl transition font-bold shadow-sm flex items-center gap-1.5">
                    <i class="fas fa-fill-drip"></i> Isi Semua
                </button>
                <button type="button" onclick="clearAll()" class="px-5 py-2.5 text-sm bg-gray-200 hover:bg-gray-300 text-gray-755 rounded-xl transition font-bold shadow-sm flex items-center gap-1.5">
                    <i class="fas fa-eraser"></i> Kosongkan
                </button>
                <button type="submit" class="px-7 py-2.5 text-sm bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-bold shadow transition flex items-center gap-1.5">
                    <i class="fas fa-save"></i> Simpan Nilai
                </button>
            </div>
        </div>
    </form>
    @elseif($selectedClassroomId && $selectedSubjectId)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
        <i class="fas fa-users-slash text-4xl text-gray-300 mb-3"></i>
        <p class="text-gray-500">Tidak ada siswa aktif di kelas ini.</p>
    </div>
    @elseif(!$selectedClassroomId)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
        <i class="fas fa-hand-pointer text-4xl text-gray-300 mb-3"></i>
        <p class="text-gray-500">Pilih kelas terlebih dahulu untuk mulai input nilai.</p>
    </div>
    @elseif(!$selectedSubjectId)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
        <i class="fas fa-book text-4xl text-gray-300 mb-3"></i>
        <p class="text-gray-500">Pilih mata pelajaran untuk mulai input nilai.</p>
    </div>
    @endif
</div>

@push('scripts')
<script>
    // Auto-color score inputs on change
    document.querySelectorAll('.score-input').forEach(input => {
        input.addEventListener('input', function() {
            const val = parseFloat(this.value);
            this.classList.remove('bg-green-50', 'text-green-700', 'border-green-300',
                                 'bg-yellow-50', 'text-yellow-700', 'border-yellow-300',
                                 'bg-red-50', 'text-red-700', 'border-red-300');
            if (!isNaN(val)) {
                if (val >= 80) {
                    this.classList.add('bg-green-50', 'text-green-700', 'border-green-300');
                } else if (val >= 60) {
                    this.classList.add('bg-yellow-50', 'text-yellow-700', 'border-yellow-300');
                } else {
                    this.classList.add('bg-red-50', 'text-red-700', 'border-red-300');
                }
            }
            updateStats();
        });

        // Auto-advance to next row on Enter
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const row = parseInt(this.closest('tr').dataset.row);
                const nextInput = document.querySelector(`tr[data-row="${row + 1}"] .score-input`);
                if (nextInput) nextInput.focus();
            }
        });
    });

    function updateStats() {
        const inputs = document.querySelectorAll('.score-input');
        let filled = 0, total = 0;
        inputs.forEach(input => {
            const val = parseFloat(input.value);
            if (!isNaN(val)) {
                filled++;
                total += val;
            }
        });
        document.getElementById('filledCount').textContent = filled;
        document.getElementById('avgScore').textContent = filled > 0 ? (total / filled).toFixed(1) : '-';
    }

    function fillAll() {
        const raw = prompt('Isi semua nilai dengan (bisa desimal, misal 75.5):');
        if (raw === null) return;
        const val = parseFloat(raw.replace(',', '.'));
        if (!isNaN(val) && val >= 0 && val <= 100) {
            document.querySelectorAll('.score-input').forEach(input => {
                if (!input.value) {
                    input.value = val;
                    input.dispatchEvent(new Event('input'));
                }
            });
        } else {
            alert('Nilai harus angka antara 0-100.');
        }
    }

    function clearAll() {
        if (confirm('Kosongkan semua nilai yang belum disimpan?')) {
            document.querySelectorAll('.score-input').forEach(input => {
                input.value = '';
                input.dispatchEvent(new Event('input'));
            });
        }
    }

    // Initial stats calculation
    updateStats();
</script>
@endpush
@endsection
