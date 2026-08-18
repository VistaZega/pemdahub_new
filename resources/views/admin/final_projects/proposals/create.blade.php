@extends('layouts.admin')

@section('title', 'Buat Kelompok Project Akhir - Portal Admin')

@section('content')
<div class="space-y-7 pb-10">

    {{-- Hero Header Card --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 p-7 md:p-8 text-white shadow-xl border border-indigo-700/50">
        <div class="absolute -right-10 -top-10 w-56 h-56 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
            <div class="flex items-start gap-4">
                <div class="w-13 h-13 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 p-0.5 shadow-lg shadow-emerald-500/20 flex-shrink-0 flex items-center justify-center">
                    <div class="w-full h-full bg-indigo-950/40 backdrop-blur-xs rounded-[14px] flex items-center justify-center text-emerald-300 text-2xl">
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>
                <div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-indigo-700/60 border border-indigo-500/50 text-indigo-200 text-xs font-black uppercase tracking-wider mb-1.5">
                        <i class="fas fa-sparkles text-amber-400"></i> Form Pembentukan Kelompok
                    </div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-white">
                        Buat Kelompok Project Akhir
                    </h1>
                    <p class="text-xs md:text-sm text-indigo-100/90 mt-0.5 font-medium">
                        Bentuk kelompok karya akhir siswa kelas XII, tentukan judul penelitian, dan tetapkan guru pembimbing.
                    </p>
                </div>
            </div>
            <div>
                <a href="{{ route('admin.final-projects.proposals.index') }}" 
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white font-bold rounded-2xl text-xs backdrop-blur-xs border border-white/20 transition-all active:scale-95 shadow-sm">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="bg-white rounded-3xl border-2 border-slate-100 shadow-[0_8px_25px_-6px_rgba(0,0,0,0.06)] overflow-hidden">
        
        {{-- Step 1: Pilih Kelas --}}
        <div class="p-6 md:p-7 border-b-2 border-slate-100 bg-gradient-to-r from-slate-50 to-indigo-50/30">
            <form method="GET" action="{{ route('admin.final-projects.proposals.create') }}" class="flex flex-col sm:flex-row gap-4 items-end max-w-xl">
                <div class="w-full flex-1">
                    <label for="classroom_id" class="block text-xs font-black text-slate-800 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <div class="w-5 h-5 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-[10px]">
                            1
                        </div>
                        Pilih Rombel / Kelas Siswa <span class="text-rose-500">*</span>
                    </label>
                    <select name="classroom_id" id="classroom_id" 
                            class="w-full rounded-2xl border-2 border-slate-200 bg-white px-4 py-3 text-xs md:text-sm font-bold text-slate-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all cursor-pointer" 
                            onchange="this.form.submit()">
                        <option value="">-- Pilih Kelas XII --</option>
                        @foreach($classrooms as $c)
                            <option value="{{ $c->id }}" {{ request('classroom_id') == $c->id ? 'selected' : '' }}>
                                🏫 {{ $c->name }} — {{ $c->school->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if(request('classroom_id'))
                    <div class="pb-1 text-xs font-bold text-indigo-700 flex items-center gap-1">
                        <i class="fas fa-check-circle text-emerald-500"></i> Kelas dipilih
                    </div>
                @endif
            </form>
        </div>

        {{-- Step 2: Form Detail Project & Anggota --}}
        @if($selectedClassroom)
            <div class="p-6 md:p-8" x-data="createProjectForm()">
                <form action="{{ route('admin.final-projects.proposals.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="classroom_id" value="{{ $selectedClassroom->id }}">

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        
                        {{-- Kolom Kiri: Informasi Project (7 cols) --}}
                        <div class="lg:col-span-7 space-y-5">
                            <h3 class="text-base font-black text-slate-900 border-b-2 border-slate-100 pb-3 flex items-center gap-2">
                                <div class="w-7 h-7 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs shadow-inner">
                                    <i class="fas fa-file-pen"></i>
                                </div>
                                Informasi Usulan Project
                            </h3>
                            
                            <div>
                                <label for="title" class="block text-xs font-black text-slate-800 uppercase tracking-wider mb-2">
                                    Judul Project Akhir <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="title" id="title" required value="{{ old('title') }}" 
                                       placeholder="Contoh: Rancang Bangun Sistem Smart Garden Berbasis IoT..." 
                                       class="w-full rounded-2xl border-2 border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white text-xs md:text-sm font-bold text-slate-900 px-4 py-3 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all placeholder:text-slate-400">
                                @error('title') <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="abstract" class="block text-xs font-black text-slate-800 uppercase tracking-wider mb-2">
                                    Deskripsi / Abstrak Singkat <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                                </label>
                                <textarea name="abstract" id="abstract" rows="4" 
                                          placeholder="Uraikan secara ringkas latar belakang, tujuan, atau alat yang dikembangkan pada project ini..." 
                                          class="w-full rounded-2xl border-2 border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white text-xs md:text-sm font-medium text-slate-900 p-4 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all placeholder:text-slate-400">{{ old('abstract') }}</textarea>
                                @error('abstract') <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="advisor_id" class="block text-xs font-black text-slate-800 uppercase tracking-wider mb-2">
                                    Guru Pembimbing <span class="text-rose-500">*</span>
                                </label>
                                <select name="advisor_id" id="advisor_id" required 
                                        class="w-full rounded-2xl border-2 border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white text-xs md:text-sm font-bold text-slate-900 px-4 py-3 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all cursor-pointer">
                                    <option value="">-- Pilih Guru Pembimbing --</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" {{ old('advisor_id') == $teacher->id ? 'selected' : '' }}>
                                            👨‍🏫 {{ $teacher->full_name }}
                                            @if($isSA) ({{ $teacher->school->name }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('advisor_id') <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Kolom Kanan: Pemilihan Anggota (5 cols) --}}
                        <div class="lg:col-span-5 space-y-5">
                            <h3 class="text-base font-black text-slate-900 border-b-2 border-slate-100 pb-3 flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs shadow-inner">
                                        <i class="fas fa-users-plus"></i>
                                    </div>
                                    Pilih Anggota Kelompok
                                </span>
                                <span class="text-xs font-black text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-100">
                                    <span x-text="selectedCount"></span> Siswa Terpilih
                                </span>
                            </h3>
                            
                            @if($students->isEmpty())
                                <div class="bg-gradient-to-r from-amber-50 to-orange-50 text-amber-900 p-5 rounded-2xl border-2 border-amber-200 text-xs font-bold space-y-1.5 shadow-sm">
                                    <div class="flex items-center gap-2 text-amber-700 font-black text-sm">
                                        <i class="fas fa-circle-exclamation text-lg"></i> Seluruh Siswa Sudah Terdaftar
                                    </div>
                                    <p>Semua siswa di kelas <strong>{{ $selectedClassroom->name }}</strong> sudah memiliki kelompok Project Akhir masing-masing.</p>
                                </div>
                            @else
                                <div class="bg-slate-50/80 rounded-2xl border-2 border-slate-200 p-4">
                                    <p class="text-xs text-slate-600 font-medium mb-3">
                                        <i class="fas fa-circle-info text-indigo-500 mr-1"></i>
                                        Centang siswa yang tergabung. Siswa yang pertama dicentang otomatis menjadi <strong>Ketua Kelompok</strong>.
                                    </p>
                                    
                                    <div class="space-y-2.5 max-h-[360px] overflow-y-auto pr-1">
                                        @foreach($students as $idx => $student)
                                            <div class="p-3 bg-white border rounded-2xl transition-all"
                                                 :class="selectedMembers.includes({{ $student->id }}) ? 'border-indigo-300 bg-indigo-50/30 shadow-sm' : 'border-slate-200 hover:border-slate-300'">
                                                <div class="flex items-center justify-between gap-2">
                                                    <label class="flex items-center gap-3 cursor-pointer flex-1 select-none">
                                                        <input type="checkbox" 
                                                               name="member_ids[]" 
                                                               value="{{ $student->id }}" 
                                                               @change="toggleMember({{ $student->id }})"
                                                               :checked="selectedMembers.includes({{ $student->id }})"
                                                               class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                                                        <div>
                                                            <span class="block text-xs font-black text-slate-900">{{ $student->full_name }}</span>
                                                            <span class="block text-[11px] text-slate-500 font-bold">NISN: {{ $student->nisn ?? ($student->nis ?? '-') }}</span>
                                                        </div>
                                                    </label>

                                                    <template x-if="selectedMembers.length > 0 && selectedMembers[0] == {{ $student->id }}">
                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-400 text-slate-950 font-black text-[10px] shadow-xs">
                                                            <i class="fas fa-crown text-[9px]"></i> Ketua
                                                        </span>
                                                    </template>
                                                    <template x-if="selectedMembers.includes({{ $student->id }}) && selectedMembers[0] != {{ $student->id }}">
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-slate-100 text-slate-600 font-bold text-[10px]">
                                                            Anggota
                                                        </span>
                                                    </template>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('member_ids') <p class="mt-2 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Action Footer --}}
                    <div class="mt-8 pt-6 border-t-2 border-slate-100 flex items-center justify-between flex-wrap gap-4">
                        <a href="{{ route('admin.final-projects.proposals.index') }}" 
                           class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                            Batal
                        </a>
                        <button type="submit" 
                                class="px-7 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-black text-xs md:text-sm rounded-2xl shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 transition-all transform active:scale-95 flex items-center gap-2" 
                                {{ $students->isEmpty() ? 'disabled' : '' }}>
                            <i class="fas fa-check-circle"></i> Bentuk Kelompok & Tetapkan Judul
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="p-16 text-center">
                <div class="w-18 h-18 bg-indigo-50 text-indigo-500 rounded-3xl flex items-center justify-center mx-auto mb-4 text-3xl border-2 border-indigo-100 shadow-inner">
                    <i class="fas fa-chalkboard-user"></i>
                </div>
                <h3 class="text-base font-black text-slate-800">Silakan Pilih Kelas Terlebih Dahulu</h3>
                <p class="text-slate-500 text-xs md:text-sm max-w-md mx-auto mt-1 font-medium">Pilih kelas XII pada dropdown di atas untuk memuat daftar siswa aktif dan mulai membentuk kelompok project akhir.</p>
            </div>
        @endif
    </div>
</div>

<script>
function createProjectForm() {
    return {
        selectedMembers: @json(old('member_ids', [])),

        get selectedCount() {
            return this.selectedMembers.length;
        },

        toggleMember(id) {
            const idx = this.selectedMembers.indexOf(id);
            if (idx > -1) {
                this.selectedMembers.splice(idx, 1);
            } else {
                this.selectedMembers.push(id);
            }
        }
    }
}
</script>
@endsection
