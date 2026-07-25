@extends('layouts.guru')

@section('title', 'Buat Quiz - LMS')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('guru.lms.show', $course->id) }}?tab=quizzes" class="text-gray-500 hover:text-emerald-600"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Buat Quiz Baru</h2>
            <p class="text-gray-500 text-sm">Course: {{ $course->name }}</p>
        </div>
    </div>

    <form action="{{ route('guru.lms.quizzes.store', $course->id) }}" method="POST"
          class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        @csrf
        <div class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Quiz <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500">
                @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Modul <span class="text-red-500">*</span></label>
                <select name="module_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500">
                    <option value="">— Pilih Modul —</option>
                    @foreach($modules as $module)
                    <option value="{{ $module->id }}" {{ old('module_id') == $module->id ? 'selected' : '' }}>
                        {{ $module->getCode() }} — {{ $module->title }}
                    </option>
                    @endforeach
                </select>
                @error('module_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="bg-purple-50 border border-purple-200 rounded-xl p-4">
                <label class="block text-sm font-bold text-purple-900 mb-1">
                    <i class="fas fa-database text-purple-600 mr-1"></i> Pilih Bank Soal CBT (Opsional)
                </label>
                <p class="text-xs text-purple-700 mb-2">Tautkan kuis ini dengan Bank Soal CBT untuk mengambil pertanyaan otomatis.</p>
                <select name="question_package_id" class="w-full border border-purple-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 bg-white">
                    <option value="">— Buat Soal Manual (Tanpa Bank Soal) —</option>
                    @if(isset($cbtQuestionBanks))
                        @foreach($cbtQuestionBanks as $qb)
                        <option value="{{ $qb->id }}" {{ old('question_package_id') == $qb->id ? 'selected' : '' }}>
                            📦 {{ $qb->bank_name }} ({{ $qb->total_questions ?? 0 }} Soal)
                        </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Batas Waktu (menit)</label>
                    <input type="number" name="time_limit" value="{{ old('time_limit') }}" min="1"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500"
                           placeholder="Contoh: 60">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Skor Minimum Lulus <span class="text-red-500">*</span></label>
                    <input type="number" name="passing_score" value="{{ old('passing_score', 75) }}" min="0" max="100" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Maks Percobaan</label>
                    <input type="number" name="max_attempts" value="{{ old('max_attempts', 1) }}" min="1" max="10"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500">
                    <p class="text-xs text-gray-400 mt-1">Berapa kali siswa boleh mengerjakan</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Mulai</label>
                    <input type="datetime-local" name="start_time" value="{{ old('start_time') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Selesai</label>
                    <input type="datetime-local" name="end_time" value="{{ old('end_time') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500">
                </div>
            </div>

            <div class="flex flex-wrap gap-6 bg-purple-50 rounded-lg p-4">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="shuffle_questions" value="1" {{ old('shuffle_questions') ? 'checked' : '' }}
                           class="rounded text-purple-600 focus:ring-purple-500">
                    <i class="fas fa-random text-purple-500"></i> Acak urutan soal
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="show_result" value="1" {{ old('show_result', true) ? 'checked' : '' }}
                           class="rounded text-purple-600 focus:ring-purple-500">
                    <i class="fas fa-eye text-purple-500"></i> Tampilkan review jawaban setelah selesai
                </label>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="bg-purple-600 text-white px-6 py-2.5 rounded-xl hover:bg-purple-700 transition shadow-md">
                <i class="fas fa-save mr-1"></i> Buat Quiz
            </button>
            <a href="{{ route('guru.lms.show', $course->id) }}?tab=quizzes" class="bg-gray-100 text-gray-700 px-6 py-2.5 rounded-xl hover:bg-gray-200 transition">Batal</a>
        </div>
    </form>
</div>
@endsection
