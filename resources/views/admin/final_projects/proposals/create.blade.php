@extends('layouts.admin')

@section('title', 'Buat Kelompok Project Akhir - Portal Admin')

@section('content')
<div class="space-y-8 md:space-y-10 pb-16">

    {{-- Hero Header Card --}}
    <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 p-8 md:p-10 text-white shadow-2xl border-2 border-indigo-700/60">
        <div class="absolute -right-16 -top-16 w-72 h-72 bg-purple-500/25 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
            <div class="flex items-start gap-5">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 p-1 shadow-xl shadow-emerald-500/25 flex-shrink-0 flex items-center justify-center">
                    <div class="w-full h-full bg-indigo-950/40 backdrop-blur-xs rounded-[14px] flex items-center justify-center text-emerald-300 text-3xl">
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-700/70 border border-indigo-400/40 text-indigo-200 text-xs font-black uppercase tracking-wider">
                        <i class="fas fa-sparkles text-amber-400"></i> Form Pembentukan Kelompok
                    </div>
                    <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">
                        Buat Kelompok Project Akhir
                    </h1>
                    <p class="text-xs md:text-sm text-indigo-100/90 font-medium pt-0.5">
                        Bentuk kelompok karya akhir siswa kelas XII, tentukan judul penelitian, dan tetapkan guru pembimbing.
                    </p>
                </div>
            </div>
            <div>
                <a href="{{ route('admin.final-projects.proposals.index') }}" 
                   class="inline-flex items-center justify-center gap-2.5 px-6 py-3.5 bg-white/10 hover:bg-white/20 text-white font-black rounded-2xl text-xs md:text-sm backdrop-blur-xs border-2 border-white/20 transition-all active:scale-95 shadow-md">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="bg-white rounded-[2rem] border-2 border-slate-150 shadow-[0_10px_30px_-5px_rgba(0,0,0,0.06)] overflow-hidden">
        
        {{-- Step 1: Pilih Kelas --}}
        <div class="p-7 md:p-9 border-b-2 border-slate-150 bg-gradient-to-r from-slate-50 to-indigo-50/40">
            <form method="GET" action="{{ route('admin.final-projects.proposals.create') }}" class="flex flex-col sm:flex-row gap-5 items-end max-w-xl">
                <div class="w-full flex-1">
                    <label for="classroom_id" class="block text-xs font-black text-slate-800 uppercase tracking-wider mb-2.5 flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs font-black shadow-xs">
                            1
                        </div>
                        Pilih Rombel / Kelas Siswa <span class="text-rose-500">*</span>
                    </label>
                    <select name="classroom_id" id="classroom_id" 
                            class="w-full rounded-2xl border-2 border-slate-200 bg-white px-5 py-3.5 text-sm font-bold text-slate-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all cursor-pointer" 
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
                    <div class="pb-2 text-xs font-black text-indigo-700 flex items-center gap-1.5">
                        <i class="fas fa-check-circle text-emerald-500 text-sm"></i> Kelas aktif dipilih
                    </div>
                @endif
            </form>
        </div>

        {{-- Step 2: Form Detail Project & Anggota --}}
        @if($selectedClassroom)
            <div class="p-7 md:p-10" x-data="createProjectForm()">
                <form action="{{ route('admin.final-projects.proposals.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="classroom_id" value="{{ $selectedClassroom->id }}">

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">
                        
                        {{-- Kolom Kiri: Informasi Project (7 cols) --}}
                        <div class="lg:col-span-7 space-y-6">
                            <h3 class="text-base md:text-lg font-black text-slate-900 border-b-2 border-slate-150 pb-3.5 flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs shadow-inner">
                                    <i class="fas fa-file-pen"></i>
                                </div>
                                Informasi Usulan Project
                            </h3>
                            
                            <div class="space-y-2">
                                <label for="title" class="block text-xs font-black text-slate-800 uppercase tracking-wider">
                                    Judul Project Akhir <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="title" id="title" required value="{{ old('title') }}" 
                                       placeholder="Contoh: Rancang Bangun Sistem Smart Garden Berbasis IoT..." 
                                       class="w-full rounded-2xl border-2 border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white text-sm font-bold text-slate-900 px-5 py-3.5 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all placeholder:text-slate-400">
                                @error('title') <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="abstract" class="block text-xs font-black text-slate-800 uppercase tracking-wider">
                                    Deskripsi / Abstrak Singkat <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                                </label>
                                <textarea name="abstract" id="abstract" rows="4" 
                                          placeholder="Uraikan secara ringkas latar belakang, tujuan, atau alat yang dikembangkan pada project ini..." 
                                          class="w-full rounded-2xl border-2 border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white text-sm font-medium text-slate-900 p-5 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all placeholder:text-slate-400">{{ old('abstract') }}</textarea>
                                @error('abstract') <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="advisor_id" class="block text-xs font-black text-slate-800 uppercase tracking-wider">
                                    Guru Pembimbing <span class="text-rose-500">*</span>
                                </label>
                                <select name="advisor_id" id="advisor_id" required 
                                        class="w-full rounded-2xl border-2 border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white text-sm font-bold text-slate-900 px-5 py-3.5 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all cursor-pointer">
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
                        <div class="lg:col-span-5 space-y-6">
                            <h3 class="text-base md:text-lg font-black text-slate-900 border-b-2 border-slate-150 pb-3.5 flex items-center justify-between">
                                <span class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs shadow-inner">
                                        <i class="fas fa-users-plus"></i>
                                    </div>
                                    Pilih Anggota Kelompok
                                </span>
                                <span class="text-xs font-black text-indigo-700 bg-indigo-50 px-3.5 py-1.5 rounded-full border border-indigo-100">
                                    <span x-text="selectedCount"></span> Siswa Terpilih
                                </span>
                            </h3>
                            
                            @if($students->isEmpty())
                                <div class="bg-gradient-to-r from-amber-50 to-orange-50 text-amber-900 p-6 rounded-2xl border-2 border-amber-200 text-xs md:text-sm font-bold space-y-2 shadow-sm">
                                    <div class="flex items-center gap-2 text-amber-800 font-black text-sm">
                                        <i class="fas fa-circle-exclamation text-lg"></i> Seluruh Siswa Sudah Terdaftar
                                    </div>
                                    <p>Semua siswa di kelas <strong>{{ $selectedClassroom->name }}</strong> sudah memiliki kelompok Project Akhir masing-masing.</p>
                                </div>
                            @else
                                <div class="bg-slate-50 rounded-2xl border-2 border-slate-200 p-5 space-y-3">
                                    <p class="text-xs text-slate-600 font-medium">
                                        <i class="fas fa-circle-info text-indigo-500 mr-1"></i>
                                        Centang siswa yang tergabung. Siswa yang pertama dicentang otomatis menjadi <strong>Ketua Kelompok</strong>.
                                    </p>
                                    
                                    <div class="space-y-3 max-h-[380px] overflow-y-auto pr-1">
                                        @foreach($students as $idx => $student)
                                            <div class="p-3.5 bg-white border-2 rounded-2xl transition-all"
                                                 :class="selectedMembers.includes({{ $student->id }}) ? 'border-indigo-400 bg-indigo-50/40 shadow-sm' : 'border-slate-200 hover:border-slate-300'">
                                                <div class="flex items-center justify-between gap-3">
                                                    <label class="flex items-center gap-3.5 cursor-pointer flex-1 select-none">
                                                        <input type="checkbox" 
                                                               name="member_ids[]" 
                                                               value="{{ $student->id }}" 
                                                               @change="toggleMember({{ $student->id }})"
                                                               :checked="selectedMembers.includes({{ $student->id }})"
                                                               class="w-4.5 h-4.5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                                                        <div>
                                                            <span class="block text-xs md:text-sm font-black text-slate-900">{{ $student->full_name }}</span>
                                                            <span class="block text-[11px] text-slate-500 font-bold">NISN: {{ $student->nisn ?? ($student->nis ?? '-') }}</span>
                                                        </div>
                                                    </label>

                                                    <template x-if="selectedMembers.length > 0 && selectedMembers[0] == {{ $student->id }}">
                                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-amber-400 text-slate-950 font-black text-xs shadow-xs border border-amber-300">
                                                            <i class="fas fa-crown text-[10px]"></i> Ketua
                                                        </span>
                                                    </template>
                                                    <template x-if="selectedMembers.includes({{ $student->id }}) && selectedMembers[0] != {{ $student->id }}">
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 font-bold text-[11px]">
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
                    <div class="mt-10 pt-8 border-t-2 border-slate-150 flex items-center justify-between flex-wrap gap-4">
                        <a href="{{ route('admin.final-projects.proposals.index') }}" 
                           class="px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-black text-xs md:text-sm rounded-2xl transition border border-slate-200">
                            Batal
                        </a>
                        <button type="submit" 
                                class="px-8 py-4 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-black text-sm md:text-base rounded-2xl shadow-xl shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all transform active:scale-95 flex items-center gap-2.5 border-2 border-emerald-400" 
                                {{ $students->isEmpty() ? 'disabled' : '' }}>
                            <i class="fas fa-check-circle"></i> Bentuk Kelompok & Tetapkan Judul
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="p-20 text-center">
                <div class="w-20 h-20 bg-indigo-50 text-indigo-500 rounded-3xl flex items-center justify-center mx-auto mb-5 text-4xl border-2 border-indigo-100 shadow-inner">
                    <i class="fas fa-chalkboard-user"></i>
                </div>
                <h3 class="text-lg font-black text-slate-800">Silakan Pilih Kelas Terlebih Dahulu</h3>
                <p class="text-slate-500 text-xs md:text-sm max-w-md mx-auto mt-1.5 font-medium leading-relaxed">Pilih kelas XII pada dropdown di atas untuk memuat daftar siswa aktif dan mulai membentuk kelompok project akhir.</p>
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
