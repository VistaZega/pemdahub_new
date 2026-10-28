@extends('layouts.guru')
@section('title', 'Edit Bank Soal')
@section('content')
<div class="space-y-8" x-data="{
    schoolsData: {{ json_encode($schoolsData) }},
    selectedSchoolId: '{{ old('school_id', $bank->school_id) }}',
    selectedSubjectId: '{{ old('subject_id', $bank->subject_id) }}',
    selectedGradeLevel: '{{ old('grade_level', $bank->grade_level) }}',
    gradeLevels: [],
    subjects: [],
    init() {
        this.updateSchoolData();
    },
    updateSchoolData() {
        const sch = this.schoolsData[this.selectedSchoolId];
        if (sch) {
            this.gradeLevels = sch.grade_levels || [];
            this.subjects = sch.subjects || [];
        }
    }
}">
    {{-- Hero Header --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700 rounded-2xl p-8 text-white">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative flex items-center justify-between gap-4">
            <div class="flex items-center gap-5">
                <a href="{{ route('guru.cbt.banks.show', $bank) }}" class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center hover:bg-white/25 transition border border-gray-200">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Edit Bank Soal</h1>
                    <p class="text-emerald-50 mt-1 text-base">{{ $bank->bank_name }}</p>
                </div>
            </div>
            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white/20 text-white rounded-lg text-xs font-bold border border-white/20">
                <i class="fas fa-school text-amber-300"></i>
                <span x-text="schoolsData[selectedSchoolId]?.name || '{{ $school->name }}'"></span>
            </div>
        </div>
    </div>

    @if(session('error'))
    <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-2xl">
        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0"><i class="fas fa-times text-red-600 text-base"></i></div>
        <p class="text-red-700 text-base font-medium">{{ session('error') }}</p>
    </div>
    @endif

    {{-- Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center"><i class="fas fa-edit text-emerald-600"></i></div>
            <h2 class="text-lg font-bold text-gray-900">Informasi Bank Soal</h2>
        </div>

        {{-- Form DELETE terpisah (di luar form UPDATE untuk menghindari nested form) --}}
        <form id="delete-bank-form" action="{{ route('guru.cbt.banks.destroy', $bank) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus bank soal ini beserta semua soalnya? Tindakan ini tidak dapat dibatalkan.')">
            @csrf @method('DELETE')
        </form>

        <form action="{{ route('guru.cbt.banks.update', $bank) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Nama Bank Soal <span class="text-red-500">*</span></label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $bank->bank_name) }}" required
                        class="w-full rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3 text-gray-800"
                        placeholder="Contoh: UAS Matematika Kelas 10 Semester 1">
                    @error('bank_name')<p class="text-red-500 text-base mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Unit Sekolah --}}
                <div class="md:col-span-2">
                    <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Unit Sekolah <span class="text-red-500">*</span></label>
                    @if(count($availableSchools) > 1)
                    <select name="school_id" x-model="selectedSchoolId" @change="updateSchoolData()" required
                        class="w-full rounded-xl border-2 border-emerald-500/60 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 px-4 py-3 text-gray-900 font-bold text-base bg-emerald-50/50">
                        @foreach($availableSchools as $sch)
                        <option value="{{ $sch->id }}" {{ old('school_id', $bank->school_id) == $sch->id ? 'selected' : '' }}>
                            {{ $sch->name }} ({{ strtoupper($sch->type) }})
                        </option>
                        @endforeach
                    </select>
                    @else
                    <input type="hidden" name="school_id" :value="selectedSchoolId">
                    <div class="flex items-center gap-2.5 p-3.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-800 font-bold text-base">
                        <i class="fas fa-school text-emerald-600"></i>
                        <span>{{ $school->name }} ({{ strtoupper($school->type) }})</span>
                    </div>
                    @endif
                    @error('school_id')<p class="text-red-500 text-base mt-1 font-bold">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Mata Pelajaran <span class="text-red-500">*</span></label>
                    <select name="subject_id" x-model="selectedSubjectId" required class="w-full rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3 text-gray-800">
                        <option value="">-- Pilih Mapel --</option>
                        <template x-for="subj in subjects" :key="subj.id">
                            <option :value="subj.id" x-text="subj.name" :selected="String(subj.id) === String(selectedSubjectId)"></option>
                        </template>
                    </select>
                    @error('subject_id')<p class="text-red-500 text-base mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Tingkat Kelas <span class="text-red-500">*</span></label>
                    <select name="grade_level" x-model="selectedGradeLevel" required class="w-full rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3 text-gray-800">
                        <template x-for="gl in gradeLevels" :key="gl">
                            <option :value="gl" x-text="'Kelas ' + gl" :selected="String(gl) === String(selectedGradeLevel)"></option>
                        </template>
                    </select>
                    @error('grade_level')<p class="text-red-500 text-base mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-base font-bold text-gray-700 uppercase tracking-wider mb-1.5">Deskripsi</label>
                    <textarea name="description" rows="3"
                        class="w-full rounded-xl border-gray-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3 text-gray-800"
                        placeholder="Deskripsi opsional...">{{ old('description', $bank->description) }}</textarea>
                </div>
                <div>
                    <label class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-200 cursor-pointer hover:bg-emerald-50 transition">
                        <input type="checkbox" name="is_shared" value="1" {{ old('is_shared', $bank->is_shared) ? 'checked' : '' }} class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 w-5 h-5">
                        <div>
                            <span class="text-base font-medium text-gray-700">Bagikan ke guru lain</span>
                            <p class="text-base text-gray-800">Guru lain dapat menggunakan soal dari bank ini</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                <button type="submit" form="delete-bank-form" class="px-5 py-2.5 bg-white border border-red-200 text-red-600 rounded-xl hover:bg-red-50 transition text-base font-medium flex items-center gap-2">
                    <i class="fas fa-trash"></i>Hapus Bank Soal
                </button>
                <div class="flex gap-3">
                    <a href="{{ route('guru.cbt.banks.show', $bank) }}" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-800 rounded-xl hover:bg-gray-50 transition text-base font-medium">Batal</a>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl hover:shadow-lg transition text-base font-medium flex items-center gap-2">
                        <i class="fas fa-save"></i>Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
