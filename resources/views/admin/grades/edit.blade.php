@extends('layouts.admin')

@section('title', 'Edit Nilai Siswa')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white shadow-md">
                <i class="fas fa-edit text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Nilai Siswa</h1>
                <p class="text-xs text-gray-500 mt-0.5">Perbarui nilai komponen akademik siswa</p>
            </div>
        </div>
        <a href="{{ route('admin.grades.index') }}" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-xl font-bold text-xs transition shadow-xs flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    @if($errors->any())
    <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-xl shadow-xs">
        <ul class="list-disc list-inside text-xs text-rose-700 space-y-1">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-7">
        <form method="POST" action="{{ route('admin.grades.update', $grade) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Nama Siswa</label>
                    <select name="student_id" required class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-semibold">
                        @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ old('student_id', $grade->student_id) == $student->id ? 'selected' : '' }}>
                            {{ $student->full_name }} ({{ $student->nisn ?: $student->nis }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Mata Pelajaran</label>
                    <select name="subject_id" required class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-semibold">
                        @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ old('subject_id', $grade->subject_id) == $subject->id ? 'selected' : '' }}>
                            {{ $subject->subject_name }} ({{ $subject->subject_code }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Rombel / Kelas</label>
                    <select name="classroom_id" required class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}" {{ old('classroom_id', $grade->classroom_id) == $cls->id ? 'selected' : '' }}>
                            {{ $cls->class_name ?? $cls->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Tipe Komponen Nilai</label>
                    <select name="grade_type" required class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-bold">
                        <option value="tugas" {{ old('grade_type', $grade->grade_type) == 'tugas' ? 'selected' : '' }}>Tugas / Formatif</option>
                        <option value="uts" {{ old('grade_type', $grade->grade_type) == 'uts' ? 'selected' : '' }}>Penilaian Tengah Semester (PTS/UTS)</option>
                        <option value="uas" {{ old('grade_type', $grade->grade_type) == 'uas' ? 'selected' : '' }}>Penilaian Akhir Semester (PAS/UAS)</option>
                        <option value="sikap" {{ old('grade_type', $grade->grade_type) == 'sikap' ? 'selected' : '' }}>Sikap / Perilaku</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Angka Nilai (0 - 100) *</label>
                    <input type="number" step="0.01" min="0" max="100" name="score" value="{{ old('score', $grade->score) }}" required class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm font-bold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Deskripsi / Catatan Capaian</label>
                    <input type="text" name="notes" value="{{ old('notes', $grade->notes) }}" placeholder="Catatan kemajuan atau feedback guru..." class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.grades.index') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold text-xs transition">
                    Batal
                </a>
                <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl font-bold text-sm shadow-md shadow-indigo-500/20 flex items-center gap-2.5 transition active:scale-95">
                    <i class="fas fa-save"></i>
                    <span>Simpan Perubahan Nilai</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
