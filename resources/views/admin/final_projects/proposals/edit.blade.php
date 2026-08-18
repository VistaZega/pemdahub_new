@extends('layouts.admin')

@section('title', 'Edit Usulan Project Akhir')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-indigo-100 text-indigo-800 border border-indigo-200 uppercase tracking-wider">
                {{ $project->type === 'penelitian_ilmiah' ? 'KTI / Penelitian Ilmiah' : 'Project Akhir SMK' }}
            </span>
            @if($currentClassroom)
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                    Kelas: {{ $currentClassroom->class_name }}
                </span>
            @endif
        </div>
        <h2 class="text-2xl font-black text-slate-800 tracking-tight">Edit Usulan Project Akhir</h2>
        <p class="text-sm text-slate-500 font-medium">Ubah judul, ganti pembimbing, sesuaikan susunan anggota kelompok, atau ubah status project.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.final-projects.proposals.index') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all shadow-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden" x-data="editProjectForm()">
    <form action="{{ route('admin.final-projects.proposals.update', $project->id) }}" method="POST" class="p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Kolom Kiri: Detail Informasi Project (7 cols) -->
            <div class="lg:col-span-7 space-y-5">
                <h3 class="text-base font-extrabold text-slate-800 border-b border-slate-100 pb-3 flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    Informasi Judul & Pembimbing
                </h3>
                
                <div>
                    <label for="title" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Judul Project Akhir <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" required value="{{ old('title', $project->title) }}" placeholder="Masukkan judul project akhir..." class="w-full rounded-xl border-slate-200 bg-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                    @error('title') <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="abstract" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Deskripsi / Abstrak Singkat <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                    </label>
                    <textarea name="abstract" id="abstract" rows="4" placeholder="Penjelasan singkat mengenai tujuan atau cakupan project..." class="w-full rounded-xl border-slate-200 bg-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-medium">{{ old('abstract', $project->abstract) }}</textarea>
                    @error('abstract') <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="advisor_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Guru Pembimbing <span class="text-rose-500">*</span>
                        </label>
                        <select name="advisor_id" id="advisor_id" required class="w-full rounded-xl border-slate-200 bg-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                            <option value="">-- Pilih Guru Pembimbing --</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('advisor_id', $project->advisor_id) == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->full_name }}
                                    @if($isSA) ({{ $teacher->school->name }}) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('advisor_id') <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Status Pengajuan <span class="text-rose-500">*</span>
                        </label>
                        <select name="status" id="status" required class="w-full rounded-xl border-slate-200 bg-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                            <option value="pending" {{ old('status', $project->status) === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                            <option value="approved" {{ old('status', $project->status) === 'approved' ? 'selected' : '' }}>Disetujui (Approved)</option>
                            <option value="in_progress" {{ old('status', $project->status) === 'in_progress' ? 'selected' : '' }}>Sedang Bimbingan</option>
                            <option value="ready_for_exam" {{ old('status', $project->status) === 'ready_for_exam' ? 'selected' : '' }}>Siap Sidang / Layak Ujian</option>
                            <option value="completed" {{ old('status', $project->status) === 'completed' ? 'selected' : '' }}>Lulus / Selesai</option>
                            <option value="rejected" {{ old('status', $project->status) === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                        @error('status') <p class="mt-1 text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="current_stage" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Tahapan Bimbingan Saat Ini
                    </label>
                    <select name="current_stage" id="current_stage" class="w-full rounded-xl border-slate-200 bg-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                        <option value="proposal" {{ old('current_stage', $project->current_stage) === 'proposal' ? 'selected' : '' }}>Proposal Awal</option>
                        @foreach($stages as $stageKey => $stageInfo)
                            <option value="{{ $stageKey }}" {{ old('current_stage', $project->current_stage) === $stageKey ? 'selected' : '' }}>
                                {{ $stageInfo['name'] }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1 font-medium">Tahapan ini dapat diperbarui otomatis saat bimbingan berlangsung atau diset manual oleh admin.</p>
                </div>
            </div>

            <!-- Kolom Kanan: Pemilihan & Pengaturan Anggota Kelompok (5 cols) -->
            <div class="lg:col-span-5 space-y-5">
                <h3 class="text-base font-extrabold text-slate-800 border-b border-slate-100 pb-3 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                            <i class="fas fa-users-gear"></i>
                        </div>
                        Anggota Kelompok
                    </span>
                    <span class="text-xs font-bold text-slate-500">
                        <span x-text="selectedMembers.length"></span> Siswa Terpilih
                    </span>
                </h3>

                <div class="bg-slate-50/80 rounded-2xl border border-slate-200 p-4">
                    <p class="text-xs text-slate-600 font-medium mb-3">
                        <i class="fas fa-circle-info text-indigo-500 mr-1"></i>
                        Centang siswa untuk menambahkan ke kelompok. Tentukan <strong>1 orang sebagai Ketua Kelompok</strong>.
                    </p>

                    @if($availableStudents->isEmpty())
                        <div class="bg-amber-50 text-amber-800 p-4 rounded-xl border border-amber-200 text-xs font-bold">
                            Tidak ada daftar siswa yang tersedia.
                        </div>
                    @else
                        <div class="space-y-2.5 max-h-[380px] overflow-y-auto pr-1">
                            @foreach($availableStudents as $st)
                                @php
                                    $isCurrentlyInProject = in_array($st->id, $currentMemberIds);
                                    $isLeader = ($st->id == $project->student_id);
                                @endphp
                                <div class="p-3 bg-white border rounded-xl transition-all"
                                     :class="selectedMembers.includes({{ $st->id }}) ? 'border-indigo-300 bg-indigo-50/30 shadow-sm' : 'border-slate-200 hover:border-slate-300'">
                                    <div class="flex items-start justify-between gap-2">
                                        <label class="flex items-start gap-2.5 cursor-pointer flex-1 select-none">
                                            <input type="checkbox" 
                                                   name="member_ids[]" 
                                                   value="{{ $st->id }}" 
                                                   @change="toggleMember({{ $st->id }})"
                                                   :checked="selectedMembers.includes({{ $st->id }})"
                                                   class="mt-1 w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                                            <div>
                                                <span class="block text-xs font-bold text-slate-900">{{ $st->full_name }}</span>
                                                <span class="block text-[11px] text-slate-400 font-medium">NISN: {{ $st->nisn ?? ($st->nis ?? '-') }}</span>
                                            </div>
                                        </label>

                                        <!-- Radio Pilih Ketua -->
                                        <template x-if="selectedMembers.includes({{ $st->id }})">
                                            <label class="inline-flex items-center gap-1 text-[11px] font-extrabold px-2 py-1 rounded-lg cursor-pointer transition select-none"
                                                   :class="leaderId == {{ $st->id }} ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                                                <input type="radio" 
                                                       name="leader_id" 
                                                       value="{{ $st->id }}" 
                                                       x-model="leaderId"
                                                       class="hidden">
                                                <i class="fas fa-crown text-[10px]" :class="leaderId == {{ $st->id }} ? 'text-amber-200' : 'text-slate-400'"></i>
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

        <!-- Footer Tombol -->
        <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-between flex-wrap gap-4">
            <button type="button" 
                    @click="confirmDelete()" 
                    class="px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-xl border border-rose-200 transition-all flex items-center gap-1.5 shadow-sm">
                <i class="fas fa-trash-alt"></i> Hapus Usulan Project Ini
            </button>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.final-projects.proposals.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                    <i class="fas fa-check"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </form>

    <!-- Hidden Form Delete -->
    <form id="delete-project-form" action="{{ route('admin.final-projects.proposals.destroy', $project->id) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
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
                // Jika ketua yang dihapus dari centang, pindahkan ke anggota pertama yang tersisa
                if (this.leaderId == id) {
                    this.leaderId = this.selectedMembers.length > 0 ? this.selectedMembers[0] : '';
                }
            } else {
                this.selectedMembers.push(id);
                // Jika belum ada ketua, jadikan ini sebagai ketua
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
