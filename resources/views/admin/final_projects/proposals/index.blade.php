@extends('layouts.admin')
@php
    $user = auth()->user();
    $schoolType = $user->school ? strtoupper($user->school->type) : 'ALL';
    $isSMA = $schoolType === 'SMA';
    $isSMK = $schoolType === 'SMK';
    
    $pageTitle = 'Usulan Judul ';
    $entityName = 'tugas akhir';
    if ($isSMA) {
        $pageTitle .= 'Tugas Penelitian Ilmiah';
        $entityName = 'penelitian ilmiah';
    } else if ($isSMK) {
        $pageTitle .= 'Project Akhir SMK';
        $entityName = 'project akhir';
    } else {
        $pageTitle .= 'Penelitian & Project Akhir';
        $entityName = 'tugas akhir / project';
    }

    // Siapkan data guru per sekolah dalam format JSON untuk Alpine.js
    $teachersBySchool = $teachers->groupBy('school_id')->map(function($group) {
        return $group->map(function($t) {
            return [
                'id' => $t->id,
                'name' => $t->full_name,
                'school_id' => $t->school_id,
                'school_name' => $t->school->name ?? 'N/A',
            ];
        })->values();
    });
    $allTeachersJson = $teachers->map(function($t) {
        return [
            'id' => $t->id,
            'name' => $t->full_name,
            'school_id' => $t->school_id,
            'school_name' => $t->school->name ?? 'N/A',
        ];
    })->values();
@endphp
@section('title', $pageTitle . ' - Portal Admin')

@section('content')
<div x-data="proposalManager()" class="space-y-6">

    {{-- Header Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white rounded-3xl shadow-md border border-gray-250 px-6 py-5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-800 flex items-center justify-center text-lg border border-indigo-300 shadow-sm">
                <i class="fas fa-flask"></i>
            </div>
            <div>
                <h1 class="text-lg md:text-xl font-extrabold text-gray-900 tracking-tight">{{ $pageTitle }}</h1>
                <p class="text-xs text-gray-700 mt-0.5 font-medium">Daftar pengajuan judul bagi siswa kelas XII. Lakukan verifikasi judul, abstrak, dan tentukan guru pembimbing.</p>
            </div>
        </div>
        <div class="flex-shrink-0">
            <a href="{{ route('admin.final-projects.proposals.create') }}" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-sm transition flex items-center justify-center gap-2">
                <i class="fas fa-plus"></i> Buat Kelompok Baru
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="bg-emerald-100 border border-emerald-300 text-emerald-900 px-5 py-4 rounded-2xl text-xs md:text-sm shadow-md flex items-center gap-3">
            <i class="fas fa-circle-check text-emerald-700 text-lg"></i> 
            <span class="font-bold">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-rose-100 border border-rose-300 text-rose-955 px-5 py-4 rounded-2xl text-xs md:text-sm shadow-md flex items-center gap-3">
            <i class="fas fa-circle-exclamation text-rose-700 text-lg"></i> 
            <span class="font-bold">{{ session('error') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="bg-rose-100 border border-rose-300 text-rose-955 px-5 py-4 rounded-2xl text-xs md:text-sm shadow-md space-y-1.5">
            <div class="flex items-center gap-2 font-extrabold">
                <i class="fas fa-circle-exclamation text-rose-700"></i> Terjadi kesalahan validasi:
            </div>
            <ul class="list-disc pl-5 text-[11px] font-black">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Filter & Search Card --}}
    <div class="bg-white rounded-3xl shadow-md border border-gray-250 p-5">
        <form action="{{ route('admin.final-projects.proposals.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
            <div class="md:col-span-2 relative">
                <i class="fas fa-search absolute left-4 top-3.5 text-gray-500 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa atau judul..." class="w-full bg-white border border-gray-300 rounded-2xl pl-11 pr-4 py-2.5 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition font-medium">
            </div>
            <div>
                <select name="status" onchange="this.form.submit()" class="w-full bg-white border border-gray-300 rounded-2xl px-4 py-2.5 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition font-bold">
                    <option value="">Semua Status...</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Pengerjaan</option>
                    <option value="ready_for_exam" {{ request('status') === 'ready_for_exam' ? 'selected' : '' }}>Layak Sidang</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai / Lulus</option>
                </select>
            </div>
            @if($isSA)
                <div>
                    <select name="school_id" onchange="this.form.submit()" class="w-full bg-white border border-gray-300 rounded-2xl px-4 py-2.5 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition font-bold">
                        <option value="">Semua Sekolah...</option>
                        @foreach($schools as $sch)
                            <option value="{{ $sch->id }}" {{ request('school_id') == $sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="flex items-center justify-end">
                <a href="{{ route('admin.final-projects.proposals.index') }}" class="w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-extrabold py-2.5 px-4 rounded-2xl text-xs transition shadow-sm border border-gray-300">
                    <i class="fas fa-arrows-rotate mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Table Card --}}
    <div class="bg-white rounded-3xl shadow-md border border-gray-250 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 border-b border-gray-300 text-xs font-black text-gray-700 uppercase tracking-wider text-left">
                    <tr>
                        <th class="py-4 pl-6">Kelompok / Siswa</th>
                        <th class="py-4">Judul Penelitian</th>
                        <th class="py-4">Jenis</th>
                        <th class="py-4 text-center">Status</th>
                        <th class="py-4 pr-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-250 text-xs text-gray-755">
                    @forelse($projects as $p)
                        <tr class="hover:bg-gray-100 transition">
                            <td class="py-4.5 pl-6">
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-100 to-purple-100 text-indigo-800 flex items-center justify-center flex-shrink-0 text-sm border border-indigo-300 shadow-sm">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-gray-900 text-xs leading-tight truncate">Ketua: {{ $p->student->full_name }}</p>
                                        @if($p->members && $p->members->count() > 1)
                                            <ul class="text-xs text-gray-655 font-bold mt-1 space-y-0.5 border-l-2 border-indigo-500 pl-1.5 leading-normal">
                                                @foreach($p->members->where('role', 'member') as $member)
                                                    <li class="truncate">- {{ $member->student->full_name }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                        <p class="text-xs font-black text-indigo-600 mt-1.5 uppercase tracking-wider">{{ $p->student->school->name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4.5 max-w-[280px]">
                                <p class="font-bold text-gray-900 leading-relaxed truncate" title="{{ $p->title }}">{{ $p->title }}</p>
                                <p class="text-xs text-gray-600 mt-1 font-bold">Pembimbing: {{ $p->advisor->full_name ?? '-' }}</p>
                            </td>
                            <td class="py-4.5">
                                <span class="bg-gray-150 text-gray-800 px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wide border border-gray-300">
                                    {{ $p->type === 'penelitian_ilmiah' ? 'Penelitian' : 'Project Akhir' }}
                                </span>
                            </td>
                            <td class="py-4.5 text-center">
                                @php
                                    $statusClass = match($p->status) {
                                        'pending' => 'bg-amber-100 text-amber-800 border-amber-300',
                                        'approved' => 'bg-blue-100 text-blue-800 border-blue-300',
                                        'rejected' => 'bg-rose-100 text-rose-800 border-rose-300',
                                        'in_progress' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
                                        'ready_for_exam' => 'bg-cyan-100 text-cyan-800 border-cyan-300',
                                        'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                        default => 'bg-gray-150 text-gray-805 border-gray-300'
                                    };
                                    $statusText = match($p->status) {
                                        'pending' => 'Pending',
                                        'approved' => 'Disetujui',
                                        'rejected' => 'Ditolak',
                                        'in_progress' => 'Bimbingan',
                                        'ready_for_exam' => 'Layak Sidang',
                                        'completed' => 'Lulus',
                                        default => $p->status
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black border {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="py-4.5 pr-6 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    @if($p->status === 'pending')
                                        <button @click="openModal({{ $p->id }}, '{{ addslashes($p->student->full_name) }}', '{{ addslashes($p->title) }}', {{ $p->student->school_id }})" class="inline-flex items-center gap-1 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold px-3 py-1.5 rounded-xl text-xs shadow-sm hover:shadow transition-all transform active:scale-95 border border-indigo-700" title="Verifikasi & Tugaskan Pembimbing">
                                            <i class="fas fa-circle-check"></i> Verifikasi
                                        </button>
                                    @endif

                                    <!-- Tombol Edit -->
                                    <a href="{{ route('admin.final-projects.proposals.edit', $p->id) }}" class="inline-flex items-center gap-1 bg-amber-50 hover:bg-amber-100 text-amber-800 font-extrabold px-2.5 py-1.5 rounded-xl text-xs border border-amber-200 shadow-2xs hover:shadow-xs transition-all active:scale-95" title="Edit Judul & Anggota Kelompok">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>

                                    <!-- Tombol Hapus -->
                                    <form action="{{ route('admin.final-projects.proposals.destroy', $p->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus usulan project \'{{ addslashes($p->title) }}\' beserta seluruh data kelompoknya?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center w-7 h-7 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-xl text-xs border border-rose-200 shadow-2xs hover:shadow-xs transition-all active:scale-95" title="Hapus Usulan Project">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-16 text-center text-gray-700 italic font-bold">
                                <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3.5 border border-gray-300">
                                    <i class="fas fa-folder-open text-xl text-gray-500"></i>
                                </div>
                                <p class="text-xs font-semibold text-gray-600">Belum ada pengajuan judul proposal.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($projects->hasPages())
            <div class="px-6 py-4 border-t border-gray-250 bg-gray-50">
                {{ $projects->links() }}
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- MODAL VERIFIKASI & ASSIGN (Alpine.js powered) --}}
    {{-- ══════════════════════════════════════════════ --}}
    <template x-teleport="body">
        <div x-show="showModal" x-cloak
             class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal()"></div>

            {{-- Modal Content --}}
            <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl z-10 border border-gray-250"
                 x-show="showModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 @click.stop>

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200">
                    <h3 class="text-base font-extrabold text-gray-900 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-600 to-purple-700 flex items-center justify-center text-white shadow-md border border-indigo-500">
                            <i class="fas fa-user-check text-xs"></i>
                        </div>
                        Verifikasi Pengajuan Judul
                    </h3>
                    <button @click="closeModal()" class="w-8 h-8 rounded-xl hover:bg-gray-100 flex items-center justify-center text-gray-600 hover:text-gray-800 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Body --}}
                <form :action="formAction" method="POST">
                    @csrf
                    <div class="px-6 py-5 space-y-5 max-h-[65vh] overflow-y-auto">

                        {{-- Info Siswa & Judul --}}
                        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl p-4.5 space-y-2.5 border border-indigo-250">
                            <div>
                                <span class="text-xs text-indigo-805 font-black uppercase tracking-wider">Siswa Pengusul</span>
                                <p class="font-extrabold text-gray-950 text-sm mt-0.5" x-text="studentName"></p>
                            </div>
                            <div class="pt-2 border-t border-indigo-200">
                                <span class="text-xs text-indigo-805 font-black uppercase tracking-wider">Judul Usulan</span>
                                <p class="font-bold text-gray-900 text-xs leading-relaxed text-justify mt-0.5" x-text="projectTitle"></p>
                            </div>
                        </div>

                        {{-- Keputusan --}}
                        <div>
                            <label class="block text-xs font-black text-gray-800 uppercase mb-2 tracking-wider">Keputusan Verifikasi</label>
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" @click="action = 'approve'"
                                        :class="action === 'approve' ? 'bg-emerald-100 border-emerald-400 text-emerald-800 ring-2 ring-emerald-200 font-black' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'"
                                        class="flex items-center justify-center gap-2 px-4 py-3 rounded-2xl border text-xs font-bold transition-all">
                                    <i class="fas fa-check-circle"></i> Setujui Judul
                                </button>
                                <button type="button" @click="action = 'reject'"
                                        :class="action === 'reject' ? 'bg-rose-100 border-rose-400 text-rose-800 ring-2 ring-rose-200 font-black' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'"
                                        class="flex items-center justify-center gap-2 px-4 py-3 rounded-2xl border text-xs font-bold transition-all">
                                    <i class="fas fa-times-circle"></i> Tolak Judul
                                </button>
                            </div>
                            <input type="hidden" name="action" :value="action">
                        </div>

                        {{-- Approve Block: Pilih Pembimbing --}}
                        <div x-show="action === 'approve'" x-transition class="space-y-2 pt-2 border-t border-gray-200">
                            <label class="block text-xs font-black text-gray-800 uppercase tracking-wider mb-1">
                                <i class="fas fa-chalkboard-teacher text-indigo-600 mr-0.5"></i> Pilih Guru Pembimbing
                            </label>
                            <select name="advisor_id" x-ref="advisorSelect"
                                    :required="action === 'approve'"
                                    class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition font-bold text-gray-900">
                                <option value="">— Pilih Guru Pembimbing —</option>
                                <template x-for="teacher in filteredTeachers" :key="teacher.id">
                                    <option :value="teacher.id" x-text="teacher.name + ' — ' + teacher.school_name"></option>
                                </template>
                            </select>
                            <p x-show="filteredTeachers.length === 0" class="text-xs text-amber-700 flex items-center gap-1 mt-1.5 font-bold leading-normal">
                                <i class="fas fa-info-circle"></i> Tidak ada guru yang sesuai dengan asal sekolah siswa ini.
                            </p>
                        </div>

                        {{-- Reject Block: Alasan Penolakan --}}
                        <div x-show="action === 'reject'" x-transition class="space-y-2 pt-2 border-t border-gray-200">
                            <label class="block text-xs font-black text-gray-800 uppercase tracking-wider mb-1">
                                <i class="fas fa-comment-dots text-rose-700 mr-0.5"></i> Alasan Penolakan
                            </label>
                            <textarea name="rejection_reason" rows="3"
                                      :required="action === 'reject'"
                                      placeholder="Tuliskan alasan penolakan agar siswa dapat memahami apa yang perlu direvisi..."
                                      class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-xs focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-rose-400 transition resize-none leading-relaxed text-gray-900 font-medium"></textarea>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-200 bg-gray-100 rounded-b-3xl">
                        <button type="button" @click="closeModal()"
                                class="bg-white hover:bg-gray-50 text-gray-700 font-extrabold px-5 py-2.5 rounded-xl text-xs border border-gray-300 transition-all">
                            Batal
                        </button>
                        <button type="submit"
                                :class="action === 'approve' ? 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-200' : 'bg-rose-600 hover:bg-rose-700 shadow-rose-200'"
                                class="text-white font-extrabold px-5 py-2.5 rounded-xl text-xs shadow-md transition-all inline-flex items-center gap-1.5 transform active:scale-95">
                            <i :class="action === 'approve' ? 'fas fa-check' : 'fas fa-ban'" class="text-[10px]"></i>
                            <span x-text="action === 'approve' ? 'Setujui & Tugaskan' : 'Tolak Pengajuan'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
    function proposalManager() {
        const isSuperAdmin = @json($isSA);
        const allTeachers = @json($allTeachersJson);

        return {
            showModal: false,
            action: 'approve',
            projectId: null,
            studentName: '',
            projectTitle: '',
            studentSchoolId: null,
            formAction: '',

            get filteredTeachers() {
                if (isSuperAdmin && this.studentSchoolId) {
                    return allTeachers.filter(t => t.school_id == this.studentSchoolId);
                }
                return allTeachers;
            },

            openModal(id, student, title, schoolId) {
                this.projectId = id;
                this.studentName = student;
                this.projectTitle = title;
                this.studentSchoolId = schoolId;
                this.formAction = "{{ url('/admin/final-projects/proposals') }}/" + id + "/assign";
                this.action = 'approve';
                this.showModal = true;
            },

            closeModal() {
                this.showModal = false;
            }
        };
    }
</script>
@endpush
