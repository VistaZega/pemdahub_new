@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Modern Header -->
    <div class="flex items-center gap-4 mb-6">
        <div class="w-16 h-16 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
        </div>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Edit Ruang Kelas</h1>
            <p class="text-gray-600">Perbarui informasi kelas</p>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow">
        <div class="flex items-start">
            <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
            </svg>
            <ul class="list-disc list-inside text-red-700">
                @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <form action="{{ route('admin.classrooms.update', $classroom) }}" method="POST" class="bg-white rounded-2xl shadow-lg overflow-hidden">
        @csrf
        @method('PUT')
        <div class="bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-4">
            <h2 class="text-xl font-bold text-white"><i class="fas fa-clipboard mr-1"></i> Form Ruang Kelas</h2>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-school mr-1"></i> Sekolah</label>
                @if(auth()->user()->isSuperAdmin())
                    <select name="school_id" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition">
                        <option value="">-- Pilih Sekolah --</option>
                        @foreach($schools as $sch)
                        <option value="{{ $sch->id }}" {{ old('school_id', $classroom->school_id) == $sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
                        @endforeach
                    </select>
                @else
                    <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-3">
                        <span class="text-sm text-indigo-600 font-semibold"><i class="fas fa-school mr-1"></i> {{ auth()->user()->school->name }}</span>
                    </div>
                    <input type="hidden" name="school_id" value="{{ old('school_id', $classroom->school_id) }}">
                @endif
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-calendar-alt mr-1"></i> Tahun Ajaran</label>
                @if(auth()->user()->isSuperAdmin())
                    <select name="academic_year_id" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition" id="school-academic-year-select">
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        @foreach($academicYears as $ay)
                        <option value="{{ $ay->id }}" {{ old('academic_year_id', $classroom->academic_year_id) == $ay->id ? 'selected' : '' }}>{{ $ay->year }}</option>
                        @endforeach
                    </select>
                @else
                    <div class="bg-green-50 border border-green-200 rounded-xl p-3">
                        <span class="text-sm text-green-600 font-semibold"><i class="fas fa-calendar-alt mr-1"></i> {{ $classroom->academicYear->year ?? 'Tahun Ajaran' }}</span>
                    </div>
                    <input type="hidden" name="academic_year_id" value="{{ old('academic_year_id', $classroom->academic_year_id) }}">
                @endif
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-tag mr-1"></i> Tipe Kelas</label>
                    <select name="class_type" id="class_type" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition" onchange="toggleGabunganInfo(this.value)">
                        <option value="reguler" {{ old('class_type', $classroom->class_type) == 'reguler' ? 'selected' : '' }}>Reguler</option>
                        <option value="industri" {{ old('class_type', $classroom->class_type) == 'industri' ? 'selected' : '' }}>Kelas Industri</option>
                        <option value="exclusive" {{ old('class_type', $classroom->class_type) == 'exclusive' ? 'selected' : '' }}>Kelas Exclusive</option>
                        <option value="khusus" {{ old('class_type', $classroom->class_type) == 'khusus' ? 'selected' : '' }}>Kelas Khusus</option>
                        <option value="gabungan" {{ old('class_type', $classroom->class_type) == 'gabungan' || $classroom->is_combined ? 'selected' : '' }}>Kelas Gabungan (Multi-Jurusan)</option>
                    </select>
                    <div id="gabungan-info" class="mt-2 p-3 bg-indigo-50 border border-indigo-200 rounded-xl text-xs text-indigo-700 font-medium" style="display: {{ old('class_type', $classroom->class_type) == 'gabungan' || $classroom->is_combined ? 'block' : 'none' }}">
                        <i class="fas fa-info-circle text-indigo-500 mr-1"></i> <strong>Kelas Gabungan:</strong> Program / Konsentrasi Keahlian opsional (boleh dikosongkan). Seluruh Mata Pelajaran Kejuruan dari semua jurusan akan terbuka untuk kelas ini.
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-chart-bar mr-1"></i> Tingkat</label>
                    <select name="grade_level" id="grade-level-select" required class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition font-medium">
                        <option value="">-- Pilih Tingkat --</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-list-ol mr-1"></i> Kode Kelas</label>
                    <input type="text" name="class_code" value="{{ old('class_code', $classroom->class_code) }}" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-edit mr-1"></i> Nama Kelas</label>
                    <input type="text" name="class_name" value="{{ old('class_name', $classroom->class_name) }}" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition">
                </div>
            </div>
            <div id="major-container" style="display:none">
                <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-graduation-cap mr-1"></i> Jurusan</label>
                <select name="major_id" id="major-select" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition">
                    <option value="">-- Pilih Jurusan --</option>
                </select>
            </div>
            <div id="keahlian-container" style="display:none">
                <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-graduation-cap mr-1"></i> Program Keahlian</label>
                <select name="program_keahlian_id" id="program-keahlian-select" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition">
                    <option value="">-- Pilih Program Keahlian --</option>
                </select>
            </div>
            <div id="konsentrasi-container" style="display:none">
                <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-cog mr-1"></i> Konsentrasi Keahlian</label>
                <select name="konsentrasi_keahlian_id" id="konsentrasi-keahlian-select" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition">
                    <option value="">-- Pilih Konsentrasi --</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-users mr-1"></i> Kapasitas</label>
                <input type="number" name="capacity" value="{{ old('capacity', $classroom->capacity) }}" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition">
            </div>

            <!-- Attendance Settings -->
            <div class="bg-gray-50 p-4 rounded-xl border-l-4 border-amber-400">
                <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-clock text-amber-500"></i> Pengaturan Kehadiran (Terlambat)
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Jam Masuk (Format 24 Jam)</label>
                        <input type="text" name="entry_time" placeholder="07:30" value="{{ old('entry_time', $classroom->entry_time ?? '07:30') }}" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition font-mono text-lg">
                        <p class="text-[10px] text-gray-400 mt-1 italic">* Contoh: 07:30 atau 13:00</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Toleransi (Menit)</label>
                        <div class="relative">
                            <input type="number" name="late_tolerance" value="{{ old('late_tolerance', $classroom->late_tolerance ?? 15) }}" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition text-lg">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold uppercase text-[10px]">Menit</span>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1 italic">* Menit setelah jam masuk sebelum dianggap terlambat</p>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-edit mr-1"></i> Catatan</label>
                <textarea name="notes" rows="3" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition">{{ old('notes', $classroom->notes) }}</textarea>
            </div>
            <div class="flex items-center">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $classroom->is_active) ? 'checked' : '' }} class="w-5 h-5 text-cyan-600 border-2 border-gray-300 rounded focus:ring-2 focus:ring-cyan-500 mr-3">
                    <span class="text-sm font-semibold text-gray-700"><i class="fas fa-check-circle text-green-500 mr-1"></i> Aktifkan Kelas</span>
                </label>
            </div>
        </div>

        @push('scripts')
        <script>
            function toggleGabunganInfo(val) {
                const info = document.getElementById('gabungan-info');
                const keahlianContainer = document.getElementById('keahlian-container');
                const konsentrasiContainer = document.getElementById('konsentrasi-container');
                const majorContainer = document.getElementById('major-container');
                const programSelect = document.getElementById('program-keahlian-select');
                const konsentrasiSelect = document.getElementById('konsentrasi-keahlian-select');
                const majorSelect = document.getElementById('major-select');

                if (val === 'gabungan') {
                    if (info) info.style.display = 'block';
                    if (keahlianContainer) keahlianContainer.style.display = 'none';
                    if (konsentrasiContainer) konsentrasiContainer.style.display = 'none';
                    if (majorContainer) majorContainer.style.display = 'none';
                    if (programSelect) programSelect.value = '';
                    if (konsentrasiSelect) konsentrasiSelect.value = '';
                    if (majorSelect) majorSelect.value = '';
                } else {
                    if (info) info.style.display = 'none';
                    const schoolSelect = document.querySelector('select[name="school_id"]');
                    const schoolHidden = document.querySelector('input[name="school_id"][type="hidden"]');
                    const currentSchool = schoolSelect ? schoolSelect.value : (schoolHidden ? schoolHidden.value : null);
                    if (currentSchool && typeof loadKeahlianForSchool === 'function') {
                        loadKeahlianForSchool(currentSchool);
                    }
                }
            }

            async function loadKeahlianForSchool(schoolId, selectedProgramId = null, selectedKonsentrasiId = null, selectedMajorId = null, selectedGradeLevel = null) {
                const keahlianContainer = document.getElementById('keahlian-container');
                const konsentrasiContainer = document.getElementById('konsentrasi-container');
                const majorContainer = document.getElementById('major-container');
                const programSelect = document.getElementById('program-keahlian-select');
                const konsentrasiSelect = document.getElementById('konsentrasi-keahlian-select');
                const majorSelect = document.getElementById('major-select');
                const gradeSelect = document.getElementById('grade-level-select');
                if (!schoolId) {
                    keahlianContainer.style.display = 'none';
                    konsentrasiContainer.style.display = 'none';
                    majorContainer.style.display = 'none';
                    programSelect.innerHTML = '<option value="">-- Pilih Program Keahlian --</option>';
                    konsentrasiSelect.innerHTML = '<option value="">-- Pilih Konsentrasi --</option>';
                    majorSelect.innerHTML = '<option value="">-- Pilih Jurusan --</option>';
                    gradeSelect.innerHTML = '<option value="">-- Pilih Tingkat --</option>';
                    return;
                }
                try {
                    const res = await fetch("{{ url('admin/schools') }}/" + schoolId + "/keahlian");
                    if (!res.ok) throw new Error('Gagal memuat data sekolah');
                    const data = await res.json();
                    
                    // 1. Majors (Jurusan)
                    if (data.majors && data.majors.length) {
                        majorSelect.innerHTML = '<option value="">-- Pilih Jurusan --</option>';
                        data.majors.forEach(m => {
                            const opt = document.createElement('option');
                            opt.value = m.id;
                            opt.textContent = m.major_name;
                            if (String(m.id) === String(selectedMajorId)) opt.selected = true;
                            majorSelect.appendChild(opt);
                        });
                    } else {
                        majorSelect.innerHTML = '<option value="">-- Pilih Jurusan --</option>';
                    }
                    
                    // 2. Grade Levels (Tingkat)
                    gradeSelect.innerHTML = '<option value="">-- Pilih Tingkat --</option>';
                    if (data.grade_levels && data.grade_levels.length) {
                        data.grade_levels.forEach(g => {
                            const opt = document.createElement('option');
                            opt.value = g;
                            opt.textContent = 'Tingkat ' + g;
                            if (String(g) === String(selectedGradeLevel)) opt.selected = true;
                            gradeSelect.appendChild(opt);
                        });
                    }
                    
                    // Dinamis Tampilkan Jurusan untuk SMA hanya jika Kelas XI (11) atau XII (12)
                    const schoolType = (data.type || '').toUpperCase();
                    function updateMajorVisibility() {
                        const selectedGrade = gradeSelect.value;
                        if (schoolType === 'SMA') {
                            if (selectedGrade && (String(selectedGrade) === '11' || String(selectedGrade) === '12')) {
                                if (data.majors && data.majors.length) {
                                    majorContainer.style.display = '';
                                } else {
                                    majorContainer.style.display = 'none';
                                }
                            } else {
                                majorContainer.style.display = 'none';
                                majorSelect.value = '';
                            }
                        } else {
                            if (data.majors && data.majors.length) {
                                majorContainer.style.display = '';
                            } else {
                                majorContainer.style.display = 'none';
                            }
                        }
                    }

                    gradeSelect.onchange = function() {
                        updateMajorVisibility();
                    };

                    updateMajorVisibility();
                    
                    // 3. Program & Konsentrasi (SMK only)
                    if ((data.type || '').toUpperCase() === 'SMK' && data.program_keahlians && data.program_keahlians.length) {
                        programSelect.innerHTML = '<option value="">-- Pilih Program Keahlian --</option>';
                        data.program_keahlians.forEach(p => {
                            const opt = document.createElement('option');
                            opt.value = p.id;
                            opt.textContent = p.nama;
                            if (String(p.id) === String(selectedProgramId)) opt.selected = true;
                            programSelect.appendChild(opt);
                        });
                        keahlianContainer.style.display = '';
                        
                        function renderKonsentrasi() {
                            const pid = programSelect.value;
                            konsentrasiSelect.innerHTML = '<option value="">-- Pilih Konsentrasi --</option>';
                            if (pid && data.konsentrasi_keahlians[pid]) {
                                data.konsentrasi_keahlians[pid].forEach(k => {
                                    const opt = document.createElement('option');
                                    opt.value = k.id;
                                    opt.textContent = k.nama;
                                    if (String(k.id) === String(selectedKonsentrasiId)) opt.selected = true;
                                    konsentrasiSelect.appendChild(opt);
                                });
                                konsentrasiContainer.style.display = '';
                            } else {
                                konsentrasiContainer.style.display = 'none';
                            }
                        }
                        renderKonsentrasi();
                        programSelect.onchange = function() {
                            renderKonsentrasi();
                        };
                    } else {
                        keahlianContainer.style.display = 'none';
                        konsentrasiContainer.style.display = 'none';
                        programSelect.innerHTML = '<option value="">-- Pilih Program Keahlian --</option>';
                        konsentrasiSelect.innerHTML = '<option value="">-- Pilih Konsentrasi --</option>';
                    }

                    const classTypeSelect = document.getElementById('class_type');
                    if (classTypeSelect && classTypeSelect.value === 'gabungan') {
                        toggleGabunganInfo('gabungan');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Gagal memuat data sekolah.');
                }
            }
            document.addEventListener('DOMContentLoaded', function() {
                const schoolSelect = document.querySelector('select[name="school_id"]');
                const schoolHidden = document.querySelector('input[name="school_id"][type="hidden"]');
                const initialSchool = schoolSelect ? schoolSelect.value : (schoolHidden ? schoolHidden.value : null);
                const initialProgram = '{{ old("program_keahlian_id", $classroom->program_keahlian_id ?? "") }}';
                const initialKonsentrasi = '{{ old("konsentrasi_keahlian_id", $classroom->konsentrasi_keahlian_id ?? "") }}';
                const initialMajor = '{{ old("major_id", $classroom->major_id ?? "") }}';
                const initialGradeLevel = '{{ old("grade_level", $classroom->grade_level ?? "") }}';
                if (initialSchool) loadKeahlianForSchool(initialSchool, initialProgram, initialKonsentrasi, initialMajor, initialGradeLevel);
                if (schoolSelect) {
                    schoolSelect.addEventListener('change', function() {
                        loadKeahlianForSchool(this.value);
                    });
                }
            });
        </script>
        @endpush
        <div class="px-6 pb-6 flex gap-3">
            <button type="submit" class="flex-1 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg transition duration-200 transform hover:scale-105">
                <i class="fas fa-save mr-1"></i> Perbarui
            </button>
            <a href="{{ route('admin.classrooms.index') }}" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-semibold transition duration-200">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection