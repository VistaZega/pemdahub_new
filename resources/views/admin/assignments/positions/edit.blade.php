@extends('layouts.admin')

@section('title', 'Edit Penugasan Jabatan')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-600 shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Penugasan Jabatan</h1>
                <p class="text-gray-600 mt-1">Perbarui penugasan jabatan untuk {{ $employee->full_name }}</p>
            </div>
        </div>
        <a href="{{ route('admin.assignments.positions.index') }}" 
            class="flex items-center gap-2 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-medium transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>

    @if($errors->any())
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-xl p-4 shadow-sm">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <h3 class="text-red-800 font-semibold mb-2">Terdapat kesalahan pada form:</h3>
                <ul class="list-disc list-inside text-red-700 space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('admin.assignments.positions.update', $employee->id) }}" method="POST" id="assignmentForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="employee_id" value="{{ $employee->id }}">
        
        <!-- Section 1: Informasi Guru / Pegawai & Tahun Ajaran -->
        <div class="bg-white rounded-2xl shadow-lg mb-6 overflow-hidden">
            <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-white bg-opacity-20 text-white font-bold">1</span>
                    Informasi Guru / Pegawai & Tahun Ajaran
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-8">
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-user mr-1"></i> Guru / Pegawai</label>
                        <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl">
                            <span class="px-2 py-0.5 {{ $employee->employee_type === 'guru' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700' }} text-xs font-bold rounded mr-1">{{ $employee->employee_type === 'guru' ? 'Guru' : 'Staff' }}</span>
                            <strong class="text-gray-900">{{ $employee->employee_code }}</strong> - {{ $employee->full_name }}
                            @if($employee->school)
                                <span class="text-sm text-gray-500">({{ $employee->school->school_name }})</span>
                            @endif
                        </div>
                    </div>

                    <div class="md:col-span-4">
                        <label for="academic_year_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt mr-1"></i> Tahun Ajaran <span class="text-red-500">*</span>
                        </label>
                        <select name="academic_year_id" id="academic_year_id" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('academic_year_id') border-red-500 @enderror" 
                            required>
                            <option value="">-- Pilih Tahun --</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id', $currentYear->id ?? '') == $year->id ? 'selected' : '' }}>
                                    {{ $year->year }}{{ $year->is_active ? ' (Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('academic_year_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        <!-- Section 2: Pilih Jabatan -->
        <div class="bg-white rounded-2xl shadow-lg mb-6 overflow-hidden">
            <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-white bg-opacity-20 text-white font-bold">2</span>
                    Pilih Jabatan
                </h2>
            </div>
            <div class="p-6">
                <p class="text-gray-600 mb-4">Pilih satu atau lebih jabatan yang akan ditugaskan:</p>
                
                @if(isset($isSMK) && $isSMK)
                <div class="bg-amber-50 border-l-4 border-amber-500 p-4 mb-6 rounded-r-xl shadow-sm">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-triangle text-amber-500 text-xl mt-0.5"></i>
                        <div class="text-sm text-amber-900">
                            <h4 class="font-bold text-base mb-1">Syarat Penugasan Jabatan (Khusus SMK)</h4>
                            <p>Penugasan jabatan <b>selain</b> Kepala Sekolah, Wakil Kepala Sekolah, PKS, Wali Kelas, KTU, Bendahara, dan Jabatan Support <b>WAJIB</b> memiliki Perjanjian Kinerja Jabatan yang telah disetujui Yayasan. Jabatan yang belum di-ACC akan otomatis digembok.</p>
                        </div>
                    </div>
                </div>
                @endif

                @foreach($positions as $category => $categoryPositions)
                    <div class="mb-6 last:mb-0">
                        <h6 class="text-lg font-bold text-purple-700 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            {{ ucfirst($category) }}
                        </h6>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            @foreach($categoryPositions as $position)
                                @php
                                    $posName = strtolower($position->position_name);
                                    $posCode = strtolower($position->position_code ?? '');
                                    $isExempt = str_contains($posName, 'kepala sekolah') || 
                                                str_contains($posName, 'wakil kepala sekolah') || 
                                                str_contains($posName, 'pks ') || 
                                                $posName == 'pks' ||
                                                str_contains($posName, 'wali kelas') ||
                                                str_contains($posName, 'kepala tata usaha') ||
                                                str_contains($posName, 'ktu') ||
                                                $posCode == 'ktu' ||
                                                str_contains($posName, 'bendahara') ||
                                                str_contains($posCode, 'bendahara') ||
                                                $position->position_category === 'support';
                                    $hasApprovedContract = in_array($position->id, $approvedContractPositionIds ?? []);
                                    $isDisabled = isset($isSMK) && $isSMK && !$isExempt && !$hasApprovedContract;
                                @endphp
                                <label class="flex items-start gap-3 p-4 border-2 {{ $isDisabled ? 'border-gray-200 bg-gray-50 cursor-not-allowed opacity-75' : 'border-gray-200 hover:border-purple-400 hover:bg-purple-50 cursor-pointer group' }} rounded-xl transition-all">
                                    <input class="position-checkbox mt-1 w-5 h-5 text-purple-600 rounded focus:ring-2 focus:ring-purple-500 {{ $isDisabled ? 'cursor-not-allowed' : '' }}" 
                                           type="checkbox" 
                                           name="positions[]" 
                                           value="{{ $position->id }}"
                                           id="position_{{ $position->id }}"
                                           {{ in_array($position->id, old('positions', $currentPositions)) ? 'checked' : '' }}
                                           {{ $isDisabled ? 'disabled' : '' }}>
                                    <div class="flex-1">
                                        <span class="font-semibold {{ $isDisabled ? 'text-gray-500' : 'text-gray-900 group-hover:text-purple-700' }} block">
                                            {{ $position->display_name }}
                                            @if($isDisabled)
                                                <i class="fas fa-lock text-red-400 ml-1 text-xs" title="Belum ACC Yayasan"></i>
                                            @endif
                                        </span>
                                        @if(!auth()->user()->isAdminSekolah())
                                        <span class="text-sm text-green-600 font-medium">
                                            Tunjangan: Rp {{ number_format($position->allowance_amount, 0, ',', '.') }}
                                        </span>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                
                @error('positions')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror

                @if(!auth()->user()->isAdminSekolah())
                <div id="totalAllowanceAlert" class="mt-4 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-xl hidden">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="font-semibold text-blue-900">Total Tunjangan:</p>
                            <p class="text-2xl font-bold text-blue-600" id="totalAllowance">Rp 0</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Section 3: Jabatan Utama & Detail Penugasan -->
        <div class="bg-white rounded-2xl shadow-lg mb-6 overflow-hidden">
            <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-white bg-opacity-20 text-white font-bold">3</span>
                    Jabatan Utama & Detail Penugasan
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="primary_position_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-bullseye mr-1"></i> Jabatan Utama <span class="text-red-500">*</span>
                        </label>
                        <select name="primary_position_id" id="primary_position_id" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('primary_position_id') border-red-500 @enderror" 
                            required>
                            <option value="">-- Pilih Jabatan Utama --</option>
                        </select>
                        <p class="mt-1 text-sm text-gray-500">Pilih satu jabatan sebagai jabatan utama</p>
                        @error('primary_position_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="classroomSection" class="hidden">
                        <label for="classroom_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-school mr-1"></i> Kelas (untuk Wali Kelas)
                        </label>
                        <select name="classroom_id" id="classroom_id" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" {{ (old('classroom_id', $currentClassroom->id ?? '') == $classroom->id) ? 'selected' : '' }}>
                                    {{ $classroom->class_name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-sm text-gray-500">Wajib diisi jika jabatan Wali Kelas dipilih</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="position_start_date" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar mr-1"></i> Tanggal Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="position_start_date" id="position_start_date" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('position_start_date') border-red-500 @enderror" 
                               value="{{ old('position_start_date', isset($currentAssignment) && $currentAssignment->start_date ? \Carbon\Carbon::parse($currentAssignment->start_date)->format('Y-m-d') : '') }}" required>
                        @error('position_start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="sk_number" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-file-alt mr-1"></i> Nomor SK
                        </label>
                        <input type="text" name="sk_number" id="sk_number" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('sk_number') border-red-500 @enderror" 
                               value="{{ old('sk_number', $currentAssignment->sk_number ?? '') }}"
                               placeholder="Contoh: 001/SK/2024">
                        @error('sk_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="sk_date" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt mr-1"></i> Tanggal SK
                        </label>
                        <input type="date" name="sk_date" id="sk_date" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('sk_date') border-red-500 @enderror" 
                               value="{{ old('sk_date', isset($currentAssignment) && $currentAssignment->sk_date ? \Carbon\Carbon::parse($currentAssignment->sk_date)->format('Y-m-d') : '') }}">
                        @error('sk_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex gap-3">
                <button type="submit" 
                    class="flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Perbarui Penugasan
                </button>
                <a href="{{ route('admin.assignments.positions.index') }}" 
                    class="flex items-center gap-2 px-8 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Batal
                </a>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const positionCheckboxes = document.querySelectorAll('.position-checkbox');
    const primaryPositionSelect = document.getElementById('primary_position_id');
    const classroomSection = document.getElementById('classroomSection');
    const classroomSelect = document.getElementById('classroom_id');
    const totalAllowanceAlert = document.getElementById('totalAllowanceAlert');
    const totalAllowanceSpan = document.getElementById('totalAllowance');
    
    // Position allowances (from PHP)
    const positionAllowances = {
        @foreach($positions as $category => $categoryPositions)
            @foreach($categoryPositions as $position)
                '{{ $position->id }}': {{ $position->allowance_amount }},
            @endforeach
        @endforeach
    };
    
    // Initial primary position from server
    const initialPrimaryId = String('{{ old("primary_position_id") ?? (isset($currentAssignment) ? $employee->employeePositions()->where("academic_year_id", $currentYear->id ?? 0)->whereNull("end_date")->where("is_primary", 1)->value("position_id") : "") }}');
    
    function updatePrimaryPositionOptions() {
        // Use live query to always get current state of ALL checkboxes
        const allCheckboxes = document.querySelectorAll('input.position-checkbox[type="checkbox"]');
        
        const checkedPositions = [];
        allCheckboxes.forEach(cb => {
            if (cb.checked) {
                const label = cb.closest('label');
                const nameSpan = label ? label.querySelector('span.font-semibold') : null;
                checkedPositions.push({
                    id: String(cb.value),
                    name: nameSpan ? nameSpan.textContent.trim() : ('Jabatan #' + cb.value)
                });
            }
        });
        
        // Preserve selected option if possible
        const currentPrimaryId = String(primaryPositionSelect.value || initialPrimaryId);

        // Clear and rebuild primary position dropdown
        primaryPositionSelect.innerHTML = '<option value="">-- Pilih Jabatan Utama --</option>';
        checkedPositions.forEach(pos => {
            const option = document.createElement('option');
            option.value = pos.id;
            option.textContent = pos.name;
            if (pos.id === currentPrimaryId) {
                option.selected = true;
            }
            primaryPositionSelect.appendChild(option);
        });
        
        // Show/hide classroom section if "Wali Kelas" is selected
        const waliKelasSelected = checkedPositions.some(pos => 
            pos.name.toLowerCase().includes('wali kelas')
        );
        if (waliKelasSelected) {
            classroomSection.classList.remove('hidden');
        } else {
            classroomSection.classList.add('hidden');
            classroomSelect.value = '';
        }
        
        // Calculate total allowance
        let total = 0;
        checkedPositions.forEach(pos => {
            total += positionAllowances[pos.id] || 0;
        });
        
        if (totalAllowanceAlert && totalAllowanceSpan) {
            if (total > 0) {
                totalAllowanceSpan.textContent = 'Rp ' + total.toLocaleString('id-ID');
                totalAllowanceAlert.classList.remove('hidden');
            } else {
                totalAllowanceAlert.classList.add('hidden');
            }
        }
    }
    
    positionCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updatePrimaryPositionOptions);
    });
    
    // Initial update
    updatePrimaryPositionOptions();
});
</script>
@endpush
@endsection
