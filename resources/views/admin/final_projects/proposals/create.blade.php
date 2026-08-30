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
                        <i class="fas fa-sparkles text-amber-400"></i> Form Pembentukan Kelompok Lintas Kelas
                    </div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-white">
                        Buat Kelompok Project Akhir
                    </h1>
                    <p class="text-xs md:text-sm text-indigo-100/90 mt-0.5 font-medium">
                        Bentuk kelompok karya akhir siswa kelas XII (bisa gabungan dari beberapa kelas), tentukan judul, dan tetapkan guru pembimbing.
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
    <div class="bg-white rounded-3xl border-2 border-slate-100 shadow-[0_8px_25px_-6px_rgba(0,0,0,0.06)] overflow-hidden" x-data="createProjectForm()">
        
        {{-- Filter Unit Sekolah (Khusus Super Admin) --}}
        @if($isSA && count($schools) > 1)
            <div class="p-5 md:px-7 md:py-5 border-b-2 border-slate-100 bg-slate-50/50">
                <form method="GET" action="{{ route('admin.final-projects.proposals.create') }}" class="flex items-center gap-3 flex-wrap">
                    <label for="school_id" class="text-xs font-black text-slate-800 uppercase tracking-wider">
                        🏫 Sekolah:
                    </label>
                    <select name="school_id" id="school_id" onchange="this.form.submit()" 
                            class="rounded-xl border-2 border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 cursor-pointer">
                        @foreach($schools as $sch)
                            <option value="{{ $sch->id }}" {{ $selectedSchoolId == $sch->id ? 'selected' : '' }}>
                                {{ $sch->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        @endif

        {{-- Form Detail Project & Anggota --}}
        <form action="{{ route('admin.final-projects.proposals.store') }}" method="POST" class="p-6 md:p-8">
            @csrf
            @if($selectedSchoolId)
                <input type="hidden" name="school_id" value="{{ $selectedSchoolId }}">
            @endif

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
                                    @if($isSA && $teacher->school) ({{ $teacher->school->name }}) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('advisor_id') <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Kolom Kanan: Pemilihan Anggota Lintas Kelas (5 cols) --}}
                <div class="lg:col-span-5 space-y-5">
                    <h3 class="text-base font-black text-slate-900 border-b-2 border-slate-100 pb-3 flex items-center justify-between flex-wrap gap-2">
                        <span class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs shadow-inner">
                                <i class="fas fa-users-plus"></i>
                            </div>
                            Pilih Anggota Kelompok
                        </span>
                        <div class="flex items-center gap-1.5">
                            <template x-if="selectedClassesCount > 1">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-purple-100 text-purple-800 border border-purple-200 text-[10px] font-black">
                                    <i class="fas fa-sparkles text-amber-500"></i> Lintas Kelas (<span x-text="selectedClassesCount"></span> Kelas)
                                </span>
                            </template>
                            <span class="text-xs font-black text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-100">
                                <span x-text="selectedMembers.length"></span> Siswa Terpilih
                            </span>
                        </div>
                    </h3>
                    
                    @if($students->isEmpty())
                        <div class="bg-gradient-to-r from-amber-50 to-orange-50 text-amber-900 p-5 rounded-2xl border-2 border-amber-200 text-xs font-bold space-y-1.5 shadow-sm">
                            <div class="flex items-center gap-2 text-amber-700 font-black text-sm">
                                <i class="fas fa-circle-exclamation text-lg"></i> Seluruh Siswa Sudah Terdaftar
                            </div>
                            <p>Semua siswa kelas XII di sekolah ini sudah memiliki kelompok Project Akhir masing-masing.</p>
                        </div>
                    @else
                        <div class="bg-slate-50/80 rounded-2xl border-2 border-slate-200 p-4 space-y-3">
                            <p class="text-xs text-slate-600 font-medium">
                                <i class="fas fa-circle-info text-indigo-500 mr-1"></i>
                                Anda dapat memilih siswa dari <strong>kelas yang berbeda</strong>. Centang siswa yang tergabung dan pilih 1 siswa sebagai <strong>Ketua</strong>.
                            </p>
                            
                            {{-- Filter & Search Box --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div>
                                    <select x-model="selectedClassFilter" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all cursor-pointer">
                                        <option value="">-- Semua Kelas XII --</option>
                                        @foreach($classrooms as $cls)
                                            <option value="{{ $cls->id }}">🏫 {{ $cls->class_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <input type="text" x-model="searchQuery" placeholder="Cari nama / NISN..." 
                                           class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all">
                                </div>
                            </div>

                            {{-- Student List Container --}}
                            <div class="space-y-2 max-h-[380px] overflow-y-auto pr-1">
                                <template x-for="st in filteredStudents" :key="st.id">
                                    <div class="p-3 bg-white border rounded-2xl transition-all"
                                         :class="selectedMembers.includes(st.id) ? 'border-indigo-300 bg-indigo-50/30 shadow-sm' : 'border-slate-200 hover:border-slate-300'">
                                        <div class="flex items-start justify-between gap-2">
                                            <label class="flex items-start gap-2.5 cursor-pointer flex-1 select-none">
                                                <input type="checkbox" 
                                                       :value="st.id" 
                                                       @change="toggleMember(st.id)"
                                                       :checked="selectedMembers.includes(st.id)"
                                                       class="mt-1 w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                                                <div>
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <span class="text-xs font-black text-slate-900" x-text="st.full_name"></span>
                                                        <span class="px-2 py-0.5 rounded-md bg-indigo-100/70 text-indigo-800 font-extrabold text-[10px]" x-text="'🏫 ' + st.classroom_name"></span>
                                                    </div>
                                                    <span class="block text-[11px] text-slate-500 font-bold mt-0.5" x-text="'NISN: ' + st.nisn"></span>
                                                </div>
                                            </label>

                                            {{-- Badge & Radio Pemilihan Ketua --}}
                                            <template x-if="selectedMembers.includes(st.id)">
                                                <button type="button"
                                                        class="inline-flex items-center gap-1.5 text-[11px] font-black px-2.5 py-1.5 rounded-xl cursor-pointer transition select-none flex-shrink-0"
                                                        :class="leaderId == st.id ? 'bg-amber-400 text-slate-950 shadow-sm border border-amber-300' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200'"
                                                        @click="leaderId = st.id">
                                                    <i class="fas fa-crown text-[10px]" :class="leaderId == st.id ? 'text-slate-950' : 'text-slate-400'"></i>
                                                    <span x-text="leaderId == st.id ? 'Ketua' : 'Jadikan Ketua'"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="filteredStudents.length === 0">
                                    <div class="p-6 text-center text-slate-500 text-xs font-medium bg-white rounded-2xl border border-dashed border-slate-300">
                                        Tidak ada siswa yang cocok dengan kriteria pencarian / filter kelas.
                                    </div>
                                </template>
                            </div>
                            @error('member_ids') <p class="mt-2 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                            @error('leader_id') <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>
            </div>

            {{-- Hidden inputs: memastikan SEMUA member_ids & leader_id selalu terkirim tanpa tergantung filter kelas DOM --}}
            <template x-for="mId in selectedMembers" :key="'hidden-m-' + mId">
                <input type="hidden" name="member_ids[]" :value="mId">
            </template>
            <input type="hidden" name="leader_id" :value="leaderId">

            {{-- Action Footer --}}
            <div class="mt-8 pt-6 border-t-2 border-slate-100 flex items-center justify-between flex-wrap gap-4">
                <a href="{{ route('admin.final-projects.proposals.index') }}" 
                   class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                    Batal
                </a>
                <button type="submit" 
                        class="px-7 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-black text-xs md:text-sm rounded-2xl shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 transition-all transform active:scale-95 flex items-center gap-2" 
                        :disabled="selectedMembers.length === 0">
                    <i class="fas fa-check-circle"></i> Bentuk Kelompok & Tetapkan Judul
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function createProjectForm() {
    return {
        allStudents: @json($formattedStudents),
        selectedMembers: @json(array_map('intval', old('member_ids', []))),
        leaderId: '{{ old('leader_id', '') }}',
        selectedClassFilter: '{{ $selectedClassroomId ?? '' }}',
        searchQuery: '',

        init() {
            if (this.selectedMembers.length > 0 && !this.leaderId) {
                this.leaderId = this.selectedMembers[0];
            }
        },

        get filteredStudents() {
            return this.allStudents.filter(st => {
                const matchClass = !this.selectedClassFilter || st.classroom_id == this.selectedClassFilter;
                const q = this.searchQuery.toLowerCase().trim();
                const matchSearch = !q || st.full_name.toLowerCase().includes(q) || (st.nisn && st.nisn.toLowerCase().includes(q));
                return matchClass && matchSearch;
            });
        },

        get selectedClassesCount() {
            const memberSet = new Set(this.selectedMembers.map(id => String(id)));
            const classes = new Set();
            this.allStudents.forEach(st => {
                if (memberSet.has(String(st.id)) && st.classroom_id) {
                    classes.add(st.classroom_id);
                }
            });
            return classes.size;
        },

        toggleMember(id) {
            const numId = Number(id);
            const idx = this.selectedMembers.indexOf(numId);

            if (idx > -1) {
                this.selectedMembers.splice(idx, 1);
                if (this.leaderId == numId) {
                    this.leaderId = this.selectedMembers.length > 0 ? this.selectedMembers[0] : '';
                }
            } else {
                this.selectedMembers.push(numId);
                if (!this.leaderId) {
                    this.leaderId = numId;
                }
            }
        }
    }
}
</script>
@endsection
