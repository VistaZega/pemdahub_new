@extends('layouts.guru')

@section('title', 'Buat Quiz - LMS Guru')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <div class="flex items-center gap-4">
        <a href="{{ route('guru.lms.show', $course->id) }}?tab=quizzes" class="w-11 h-11 bg-white border-2 border-black rounded-2xl flex items-center justify-center text-black hover:bg-amber-300 transition-all shadow-sm">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h2 class="text-2xl font-black text-black tracking-tight">Buat Kuis Evaluasi Baru</h2>
            <p class="text-black font-bold text-xs">Course: {{ $course->name }}</p>
        </div>
    </div>

    <form action="{{ route('guru.lms.quizzes.store', $course->id) }}" method="POST"
          class="bg-white rounded-3xl shadow-xl border-2 border-black overflow-hidden">
        @csrf
        <div class="px-8 py-6 border-b-2 border-black" style="background-color: #090d16 !important; color: #ffffff !important;">
            <h3 class="text-white font-black tracking-wide flex items-center gap-2 text-base uppercase">
                <i class="fas fa-vial text-amber-400"></i> Detail Informasi Kuis Evaluasi
            </h3>
            <p class="text-amber-300 text-xs font-bold mt-1">Konfigurasi batas waktu, acak soal, dan kriteria kelulusan kuis.</p>
        </div>

        <div class="p-8 space-y-6">
            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Judul Quiz <span class="text-rose-600">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none"
                           placeholder="Contoh: Kuis Evaluasi Bab 1 - Logika Dasar Mikrokontroler">
                    @error('title') <span class="text-rose-600 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Modul Target <span class="text-rose-600">*</span></label>
                    <select name="module_id" required class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none bg-white">
                        <option value="">— Pilih Modul —</option>
                        @foreach($modules as $module)
                        <option value="{{ $module->id }}" {{ old('module_id') == $module->id ? 'selected' : '' }}>
                            {{ $module->getCode() }} — {{ $module->title }}
                        </option>
                        @endforeach
                    </select>
                    @error('module_id') <span class="text-rose-600 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="bg-amber-100 border-2 border-black rounded-2xl p-4 shadow-xs">
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-1">
                        <i class="fas fa-database text-black mr-1"></i> Pilih Bank Soal CBT (Opsional)
                    </label>
                    <p class="text-xs text-black font-bold mb-2">Tautkan kuis ini dengan Bank Soal CBT untuk mengambil pertanyaan otomatis.</p>
                    <select name="question_package_id" class="w-full border-2 border-black rounded-2xl px-4 py-2.5 text-xs text-black font-black focus:ring-4 focus:ring-black/20 bg-white outline-none">
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
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Deskripsi / Petunjuk Pengerjaan</label>
                    <textarea name="description" rows="3"
                              class="w-full border-2 border-black rounded-2xl p-4 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none"
                              placeholder="Tuliskan petunjuk pengerjaan untuk siswa...">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Batas Waktu (Menit)</label>
                        <input type="number" name="time_limit" value="{{ old('time_limit') }}" min="1"
                               class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none"
                               placeholder="Contoh: 60">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Skor Minimum Lulus (KKM) <span class="text-rose-600">*</span></label>
                        <input type="number" name="passing_score" value="{{ old('passing_score', 75) }}" min="0" max="100" required
                               class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Maks Percobaan</label>
                        <input type="number" name="max_attempts" value="{{ old('max_attempts', 1) }}" min="1" max="10"
                               class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Waktu Mulai</label>
                        <input type="datetime-local" name="start_time" value="{{ old('start_time') }}"
                               class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Waktu Selesai</label>
                        <input type="datetime-local" name="end_time" value="{{ old('end_time') }}"
                               class="w-full border-2 border-black rounded-2xl px-5 py-3.5 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none">
                    </div>
                </div>

                <div class="flex flex-wrap gap-6 bg-slate-100 border-2 border-black rounded-2xl p-4">
                    <label class="flex items-center gap-3 text-xs font-black text-black uppercase cursor-pointer">
                        <input type="checkbox" name="shuffle_questions" value="1" {{ old('shuffle_questions') ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-2 border-black text-black focus:ring-0">
                        <i class="fas fa-random text-black"></i> Acak Urutan Soal
                    </label>
                </div>
            </div>

            <div class="pt-6 border-t-2 border-black flex gap-3">
                <a href="{{ route('guru.lms.show', $course->id) }}?tab=quizzes" class="flex-1 bg-slate-200 text-black border-2 border-black px-6 py-3.5 rounded-2xl font-black text-xs uppercase tracking-wider hover:bg-slate-300 transition text-center">Batal</a>
                <button type="submit" class="flex-[2] bg-emerald-600 text-white hover:bg-emerald-700 px-8 py-3.5 rounded-2xl font-black text-xs uppercase tracking-wider transition-all shadow-md border-2 border-black">
                    <i class="fas fa-save mr-1.5 text-amber-400"></i> Simpan Kuis Baru
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
