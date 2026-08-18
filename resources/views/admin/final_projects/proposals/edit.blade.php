@extends('layouts.admin')

@section('title', 'Edit Usulan Project Akhir - Portal Admin')

@section('content')
<div class="space-y-8 md:space-y-10 pb-16" x-data="editProjectForm()">

    {{-- Hero Header Card --}}
    <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 p-8 md:p-10 text-white shadow-2xl border-2 border-indigo-700/60">
        <div class="absolute -right-16 -top-16 w-72 h-72 bg-purple-500/25 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
            <div class="flex items-start gap-5">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 p-1 shadow-xl shadow-orange-500/25 flex-shrink-0 flex items-center justify-center">
                    <div class="w-full h-full bg-indigo-950/40 backdrop-blur-xs rounded-[14px] flex items-center justify-center text-amber-300 text-3xl">
                        <i class="fas fa-edit"></i>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-indigo-700/70 border border-indigo-400/40 text-indigo-200 text-xs font-black uppercase tracking-wider">
                            {{ $project->type === 'penelitian_ilmiah' ? 'KTI / Penelitian Ilmiah' : 'Project Akhir SMK' }}
                        </span>
                        @if($currentClassroom)
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white/15 border border-white/25 text-white text-xs font-black">
                                🏫 Kelas: {{ $currentClassroom->class_name }}
                            </span>
                        @endif
                    </div>
                    <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">
                        Edit Usulan Project & Kelompok
                    </h1>
                    <p class="text-xs md:text-sm text-indigo-100/90 font-medium pt-0.5">
                        Ubah judul, ganti guru pembimbing, sesuaikan anggota tim, atau mutakhirkan status bimbingan.
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

    {{-- Main Form Card --}}
    <div class="bg-white rounded-[2rem] border-2 border-slate-150 shadow-[0_10px_30px_-5px_rgba(0,0,0,0.06)] overflow-hidden">
        <form action="{{ route('admin.final-projects.proposals.update', $project->id) }}" method="POST" class="p-7 md:p-10">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">
                
                {{-- Kolom Kiri: Informasi Project (7 cols) --}}
                <div class="lg:col-span-7 space-y-6">
                    <h3 class="text-base md:text-lg font-black text-slate-900 border-b-2 border-slate-150 pb-3.5 flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs shadow-inner">
                            <i class="fas fa-file-signature"></i>
                        </div>
                        Informasi Judul & Pembimbing
                    </h3>
                    
                    <div class="space-y-2">
                        <label for="title" class="block text-xs font-black text-slate-800 uppercase tracking-wider">
                            Judul Project Akhir <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="title" id="title" required value="{{ old('title', $project->title) }}" 
                               placeholder="Masukkan judul project akhir..." 
                               class="w-full rounded-2xl border-2 border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white text-sm font-bold text-slate-900 px-5 py-3.5 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all">
                        @error('title') <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="abstract" class="block text-xs font-black text-slate-800 uppercase tracking-wider">
                            Deskripsi / Abstrak Singkat <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                        </label>
                        <textarea name="abstract" id="abstract" rows="4" 
                                  placeholder="Penjelasan singkat mengenai tujuan atau cakupan project..." 
                                  class="w-full rounded-2xl border-2 border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white text-sm font-medium text-slate-900 p-5 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all">{{ old('abstract', $project->abstract) }}</textarea>
                        @error('abstract') <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label for="advisor_id" class="block text-xs font-black text-slate-800 uppercase tracking-wider">
                                Guru Pembimbing <span class="text-rose-500">*</span>
                            </label>
                            <select name="advisor_id" id="advisor_id" required 
                                    class="w-full rounded-2xl border-2 border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white text-sm font-bold text-slate-900 px-5 py-3.5 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all cursor-pointer">
                                <option value="">-- Pilih Guru Pembimbing --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('advisor_id', $project->advisor_id) == $teacher->id ? 'selected' : '' }}>
                                        👨‍🏫 {{ $teacher->full_name }}
                                        @if($isSA) ({{ $teacher->school->name }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('advisor_id') <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="status" class="block text-xs font-black text-slate-800 uppercase tracking-wider">
                                Status Pengajuan <span class="text-rose-500">*</span>
                            </label>
                            <select name="status" id="status" required 
                                    class="w-full rounded-2xl border-2 border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white text-sm font-bold text-slate-900 px-5 py-3.5 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all cursor-pointer">
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

                    <div class="space-y-2">
                        <label for="current_stage" class="block text-xs font-black text-slate-800 uppercase tracking-wider">
                            Tahapan Bimbingan Saat Ini
                        </label>
                        <select name="current_stage" id="current_stage" 
                                class="w-full rounded-2xl border-2 border-slate-200 bg-slate-50/50 hover:bg-white focus:bg-white text-sm font-bold text-slate-900 px-5 py-3.5 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all cursor-pointer">
                            <option value="proposal" {{ old('current_stage', $project->current_stage) === 'proposal' ? 'selected' : '' }}>📝 Proposal Awal</option>
                            @foreach($stages as $stageKey => $stageInfo)
                                <option value="{{ $stageKey }}" {{ old('current_stage', $project->current_stage) === $stageKey ? 'selected' : '' }}>
                                    📌 {{ $stageInfo['name'] }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500 mt-1 font-bold">Tahapan bimbingan dapat bergerak otomatis sesuai persetujuan bab oleh guru pembimbing atau diatur manual oleh admin.</p>
                    </div>
                </div>

                {{-- Kolom Kanan: Pengaturan Anggota Kelompok (5 cols) --}}
                <div class="lg:col-span-5 space-y-6">
                    <h3 class="text-base md:text-lg font-black text-slate-900 border-b-2 border-slate-150 pb-3.5 flex items-center justify-between">
                        <span class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs shadow-inner">
                                <i class="fas fa-users-gear"></i>
                            </div>
                            Anggota Kelompok
                        </span>
                        <span class="text-xs font-black text-indigo-700 bg-indigo-50 px-3.5 py-1.5 rounded-full border border-indigo-100">
                            <span x-text="selectedMembers.length"></span> Siswa Terpilih
                        </span>
                    </h3>

                    <div class="bg-slate-50 rounded-2xl border-2 border-slate-200 p-5 space-y-3">
                        <p class="text-xs text-slate-600 font-medium">
                            <i class="fas fa-circle-info text-indigo-500 mr-1"></i>
                            Centang untuk menambah/mengurangi anggota. Klik tombol <strong>Ketua</strong> untuk menetapkan pemimpin tim.
                        </p>

                        @if($availableStudents->isEmpty())
                            <div class="bg-amber-50 text-amber-800 p-5 rounded-2xl border-2 border-amber-200 text-xs md:text-sm font-bold">
                                Tidak ada data siswa yang dapat dipilih.
                            </div>
                        @else
                            <div class="space-y-3 max-h-[380px] overflow-y-auto pr-1">
                                @foreach($availableStudents as $st)
                                    <div class="p-3.5 bg-white border-2 rounded-2xl transition-all"
                                         :class="selectedMembers.includes({{ $st->id }}) ? 'border-indigo-400 bg-indigo-50/40 shadow-sm' : 'border-slate-200 hover:border-slate-300'">
                                        <div class="flex items-center justify-between gap-3">
                                            <label class="flex items-center gap-3.5 cursor-pointer flex-1 select-none">
                                                <input type="checkbox" 
                                                       name="member_ids[]" 
                                                       value="{{ $st->id }}" 
                                                       @change="toggleMember({{ $st->id }})"
                                                       :checked="selectedMembers.includes({{ $st->id }})"
                                                       class="w-4.5 h-4.5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                                                <div>
                                                    <span class="block text-xs md:text-sm font-black text-slate-900">{{ $st->full_name }}</span>
                                                    <span class="block text-[11px] text-slate-500 font-bold">NISN: {{ $st->nisn ?? ($st->nis ?? '-') }}</span>
                                                </div>
                                            </label>

                                            {{-- Badge & Radio Pemilihan Ketua --}}
                                            <template x-if="selectedMembers.includes({{ $st->id }})">
                                                <label class="inline-flex items-center gap-1.5 text-xs font-black px-3 py-1.5 rounded-xl cursor-pointer transition select-none"
                                                       :class="leaderId == {{ $st->id }} ? 'bg-amber-400 text-slate-950 shadow-sm border-2 border-amber-300' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 border-2 border-slate-200'">
                                                    <input type="radio" 
                                                           name="leader_id" 
                                                           value="{{ $st->id }}" 
                                                           x-model="leaderId"
                                                           class="hidden">
                                                    <i class="fas fa-crown text-[11px]" :class="leaderId == {{ $st->id }} ? 'text-slate-950' : 'text-slate-400'"></i>
                                                    <span x-text="leaderId == {{ $st->id }} ? 'Ketua' : 'Jadikan Ketua'"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @error('member_ids') <p class="mt-2 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                        @error('leader_id') <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Action Footer --}}
            <div class="mt-10 pt-8 border-t-2 border-slate-150 flex items-center justify-between flex-wrap gap-4">
                <button type="button" 
                        @click="confirmDelete()" 
                        class="px-5 py-3.5 bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white font-black text-xs md:text-sm rounded-2xl border-2 border-rose-200 hover:border-rose-600 transition-all flex items-center gap-2 shadow-sm active:scale-95">
                    <i class="fas fa-trash-alt text-xs"></i> Hapus Usulan Project Ini
                </button>

                <div class="flex items-center gap-3.5">
                    <a href="{{ route('admin.final-projects.proposals.index') }}" 
                       class="px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-black text-xs md:text-sm rounded-2xl transition border border-slate-200">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-black text-xs md:text-sm rounded-2xl shadow-xl shadow-indigo-500/25 hover:shadow-indigo-500/35 transition-all transform active:scale-95 flex items-center gap-2.5 border-2 border-indigo-400">
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
        selectedMembers: @json(old('member_ids', $currentMemberIds)),
        leaderId: '{{ old('leader_id', $project->student_id) }}',

        toggleMember(id) {
            const index = this.selectedMembers.indexOf(id);
            if (index > -1) {
                this.selectedMembers.splice(index, 1);
                // Jika ketua yang di-uncheck, pindahkan kepemimpinan ke anggota pertama yang tersisa
                if (this.leaderId == id) {
                    this.leaderId = this.selectedMembers.length > 0 ? this.selectedMembers[0] : '';
                }
            } else {
                this.selectedMembers.push(id);
                // Jika belum ada ketua yang diset, jadikan anggota baru ini sebagai ketua
                if (!this.leaderId) {
                    this.leaderId = id;
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
