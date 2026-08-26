@extends('layouts.admin')

@section('title', 'Edit Usulan Project Akhir - Portal Admin')

@section('content')
<div class="space-y-7 pb-10" x-data="editProjectForm()">

    {{-- Hero Header Card --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 p-7 md:p-8 text-white shadow-xl border border-indigo-700/50">
        <div class="absolute -right-10 -top-10 w-56 h-56 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
            <div class="flex items-start gap-4">
                <div class="w-13 h-13 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 p-0.5 shadow-lg shadow-orange-500/20 flex-shrink-0 flex items-center justify-center">
                    <div class="w-full h-full bg-indigo-950/40 backdrop-blur-xs rounded-[14px] flex items-center justify-center text-amber-300 text-2xl">
                        <i class="fas fa-edit"></i>
                    </div>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap mb-1.5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-indigo-700/60 border border-indigo-500/50 text-indigo-200 text-xs font-black uppercase tracking-wider">
                            {{ $project->type === 'penelitian_ilmiah' ? 'KTI / Penelitian Ilmiah' : 'Project Akhir SMK' }}
                        </span>
                    </div>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-white">
                        Edit Usulan Project & Kelompok
                    </h1>
                    <p class="text-xs md:text-sm text-indigo-100/90 mt-0.5 font-medium">
                        Ubah judul, ganti guru pembimbing, sesuaikan anggota tim (bisa lintas kelas), atau mutakhirkan status bimbingan.
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

    {{-- Main Form Card --}}
    <div class="bg-white rounded-3xl border-2 border-slate-100 shadow-[0_8px_25px_-6px_rgba(0,0,0,0.06)] overflow-hidden">
        <form action="{{ route('admin.final-projects.proposals.update', $project->id) }}" method="POST" class="p-6 md:p-8">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                {{-- Kolom Kiri: Informasi Project (7 cols) --}}
                <div class="lg:col-span-7 space-y-5">
                    <h3 class="text-base font-black text-slate-900 border-b-2 border-slate-100 pb-3 flex items-center gap-2">
                        <div class="w-7 h-7 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs shadow-inner">
                            <i class="fas fa-file-signature"></i>
                        </div>
                        Informasi Judul & Pembimbing
                    </h3>
                    
                    <div>
                        <label for="title" class="block text-xs font-black text-slate-800 uppercase tracking-wider mb-2">
                            Judul Project Akhir <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="title" id="title" required value="{{ old('title', $project->title) }}" 
                               placeholder="Masukkan judul project akhir..." 
                               class="w-full rounded-2xl border-2 border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white text-xs md:text-sm font-bold text-slate-900 px-4 py-3 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all">
                        @error('title') <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="abstract" class="block text-xs font-black text-slate-800 uppercase tracking-wider mb-2">
                            Deskripsi / Abstrak Singkat <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                        </label>
                        <textarea name="abstract" id="abstract" rows="4" 
                                  placeholder="Penjelasan singkat mengenai tujuan atau cakupan project..." 
                                  class="w-full rounded-2xl border-2 border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white text-xs md:text-sm font-medium text-slate-900 p-4 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all">{{ old('abstract', $project->abstract) }}</textarea>
                        @error('abstract') <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="advisor_id" class="block text-xs font-black text-slate-800 uppercase tracking-wider mb-2">
                                Guru Pembimbing <span class="text-rose-500">*</span>
                            </label>
                            <select name="advisor_id" id="advisor_id" required 
                                    class="w-full rounded-2xl border-2 border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white text-xs md:text-sm font-bold text-slate-900 px-4 py-3 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all cursor-pointer">
                                <option value="">-- Pilih Guru Pembimbing --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('advisor_id', $project->advisor_id) == $teacher->id ? 'selected' : '' }}>
                                        👨‍🏫 {{ $teacher->full_name }}
                                        @if($isSA && $teacher->school) ({{ $teacher->school->name }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('advisor_id') <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-xs font-black text-slate-800 uppercase tracking-wider mb-2">
                                Status Pengajuan <span class="text-rose-500">*</span>
                            </label>
                            <select name="status" id="status" required 
                                    class="w-full rounded-2xl border-2 border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white text-xs md:text-sm font-bold text-slate-900 px-4 py-3 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all cursor-pointer">
                                <option value="pending" {{ old('status', $project->status) === 'pending' ? 'selected' : '' }}>⏳ Pending (Menunggu)</option>
                                <option value="approved" {{ old('status', $project->status) === 'approved' ? 'selected' : '' }}>✅ Disetujui (Approved)</option>
                                <option value="in_progress" {{ old('status', $project->status) === 'in_progress' ? 'selected' : '' }}>📘 Pengerjaan / Bimbingan</option>
                                <option value="ready_for_exam" {{ old('status', $project->status) === 'ready_for_exam' ? 'selected' : '' }}>🎯 Siap Sidang / Layak Ujian</option>
                                <option value="completed" {{ old('status', $project->status) === 'completed' ? 'selected' : '' }}>🎓 Lulus / Selesai</option>
                                <option value="rejected" {{ old('status', $project->status) === 'rejected' ? 'selected' : '' }}>❌ Ditolak</option>
                            </select>
                            @error('status') <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="current_stage" class="block text-xs font-black text-slate-800 uppercase tracking-wider mb-2">
                            Tahapan Bimbingan Saat Ini
                        </label>
                        <select name="current_stage" id="current_stage" 
                                class="w-full rounded-2xl border-2 border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white text-xs md:text-sm font-bold text-slate-900 px-4 py-3 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all cursor-pointer">
                            <option value="proposal" {{ old('current_stage', $project->current_stage) === 'proposal' ? 'selected' : '' }}>📝 Proposal Awal</option>
                            @foreach($stages as $stageKey => $stageInfo)
                                <option value="{{ $stageKey }}" {{ old('current_stage', $project->current_stage) === $stageKey ? 'selected' : '' }}>
                                    📌 {{ $stageInfo['name'] }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-500 mt-1.5 font-bold">Tahapan bimbingan dapat bergerak otomatis sesuai persetujuan bab oleh guru pembimbing atau diatur manual oleh admin.</p>
                    </div>
                </div>

                {{-- Kolom Kanan: Pengaturan Anggota Kelompok (5 cols) --}}
                <div class="lg:col-span-5 space-y-5">
                    <h3 class="text-base font-black text-slate-900 border-b-2 border-slate-100 pb-3 flex items-center justify-between flex-wrap gap-2">
                        <span class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs shadow-inner">
                                <i class="fas fa-users-gear"></i>
                            </div>
                            Anggota Kelompok
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

                    <div class="bg-slate-50/80 rounded-2xl border-2 border-slate-200 p-4 space-y-3">
                        <p class="text-xs text-slate-600 font-medium">
                            <i class="fas fa-circle-info text-indigo-500 mr-1"></i>
                            Centang untuk menambah/mengurangi anggota lintas kelas. Klik tombol <strong>Ketua</strong> untuk menetapkan pemimpin tim.
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

                        @if($availableStudents->isEmpty())
                            <div class="bg-amber-50 text-amber-800 p-4 rounded-xl border border-amber-200 text-xs font-bold">
                                Tidak ada data siswa yang dapat dipilih.
                            </div>
                        @else
                            <div class="space-y-2 max-h-[380px] overflow-y-auto pr-1">
                                <template x-for="st in filteredStudents" :key="st.id">
                                    <div class="p-3 bg-white border rounded-2xl transition-all"
                                         :class="selectedMembers.includes(st.id) ? 'border-indigo-300 bg-indigo-50/30 shadow-sm' : 'border-slate-200 hover:border-slate-300'">
                                        <div class="flex items-start justify-between gap-2">
                                            <label class="flex items-start gap-2.5 cursor-pointer flex-1 select-none">
                                                <input type="checkbox" 
                                                       name="member_ids[]" 
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
                                                <label class="inline-flex items-center gap-1.5 text-[11px] font-black px-2.5 py-1.5 rounded-xl cursor-pointer transition select-none flex-shrink-0"
                                                       :class="leaderId == st.id ? 'bg-amber-400 text-slate-950 shadow-sm border border-amber-300' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200'">
                                                    <input type="radio" 
                                                           name="leader_id" 
                                                           :value="st.id" 
                                                           x-model="leaderId"
                                                           class="hidden">
                                                    <i class="fas fa-crown text-[10px]" :class="leaderId == st.id ? 'text-slate-950' : 'text-slate-400'"></i>
                                                    <span x-text="leaderId == st.id ? 'Ketua' : 'Jadikan Ketua'"></span>
                                                </label>
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
                        @endif

                        @error('member_ids') <p class="mt-2 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                        @error('leader_id') <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Action Footer --}}
            <div class="mt-8 pt-6 border-t-2 border-slate-100 flex items-center justify-between flex-wrap gap-4">
                <button type="button" 
                        @click="confirmDelete()" 
                        class="px-4 py-2.5 bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white font-extrabold text-xs rounded-xl border border-rose-200 hover:border-rose-600 transition-all flex items-center gap-1.5 shadow-sm active:scale-95">
                    <i class="fas fa-trash-alt"></i> Hapus Usulan Project Ini
                </button>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.final-projects.proposals.index') }}" 
                       class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-7 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-black text-xs md:text-sm rounded-xl shadow-md shadow-indigo-500/20 hover:shadow-indigo-500/30 transition-all transform active:scale-95 flex items-center gap-2"
                            :disabled="selectedMembers.length === 0">
                        <i class="fas fa-check"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>

        {{-- Hidden Form Delete --}}
        <form id="delete-project-form" action="{{ route('admin.final-projects.proposals.destroy', $project->id) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

<script>
function editProjectForm() {
    return {
        allStudents: @json($formattedStudents),
        selectedMembers: @json(array_map('intval', old('member_ids', $currentMemberIds))),
        leaderId: '{{ old('leader_id', $project->student_id) }}',
        selectedClassFilter: '',
        searchQuery: '',

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
        },

        confirmDelete() {
            if (confirm('Apakah Anda yakin ingin menghapus usulan Project Akhir "{{ addslashes($project->title) }}" beserta seluruh data kelompoknya? Tindakan ini tidak dapat dibatalkan.')) {
                document.getElementById('delete-project-form').submit();
            }
        }
    }
}
</script>
@endsection
