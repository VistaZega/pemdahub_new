@extends('layouts.admin')
@section('title', 'Kelola Tim Lomba — ' . $exam->exam_title)

@section('content')
<div class="space-y-6">

    {{-- Breadcrumb & Header --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-indigo-700 via-blue-700 to-sky-800 rounded-2xl p-6 md:p-8 text-white shadow-xl">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-indigo-200 text-sm mb-1">
                    <a href="{{ route('admin.cbt.index') }}" class="hover:text-white transition">CBT</a>
                    <span>/</span>
                    <a href="{{ route('admin.cbt.show', $exam) }}" class="hover:text-white transition">{{ Str::limit($exam->exam_title, 30) }}</a>
                    <span>/</span>
                    <span class="text-white font-semibold">Kelola Tim Lomba</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-black tracking-tight flex items-center gap-3">
                    <span>🏆</span> Kelola Tim & Peserta Lomba
                </h1>
                <p class="text-indigo-100 text-sm mt-1">
                    {{ $exam->exam_title }} &bull; Mode Penilaian: 
                    <span class="font-bold underline decoration-amber-400">
                        {{ $exam->scoring_mode === 'competition' ? "+{$exam->correct_points} / -{$exam->wrong_penalty} / 0" : 'Standar' }}
                    </span>
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.cbt.competition.livescore', $exam) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-400 hover:bg-amber-300 text-slate-900 font-bold rounded-xl text-sm transition shadow-lg shadow-amber-900/30">
                    <i class="fas fa-tv text-base"></i>
                    <span>Buka Layar Livescore (Proyektor)</span>
                    <i class="fas fa-external-link-alt text-xs opacity-75"></i>
                </a>
                <a href="{{ route('admin.cbt.show', $exam) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-sm font-semibold transition border border-white/20">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm font-medium">
        <i class="fas fa-check-circle text-emerald-600 text-lg"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm font-medium">
        <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm space-y-1">
        @foreach($errors->all() as $err)
        <div>• {{ $err }}</div>
        @endforeach
    </div>
    @endif

    {{-- Grid 2 Kolom: Form Input Tim + Daftar Tim --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Form Tambah Tim (Kolom Kiri / 5 Kolom) --}}
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Daftarkan Tim Baru</h2>
                        <p class="text-xs text-slate-500">Pilih kelas, siswa operator, dan anggota tim</p>
                    </div>
                </div>

                <form action="{{ route('admin.cbt.competition.teams.store', $exam) }}" method="POST" id="teamForm" class="space-y-4">
                    @csrf

                    {{-- Pilih Kelas --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Kelas yang Diwakili <span class="text-red-500">*</span>
                        </label>
                        <select name="classroom_id" id="classroomSelect" required
                                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($availableClassrooms as $cls)
                            <option value="{{ $cls->id }}" {{ old('classroom_id') == $cls->id ? 'selected' : '' }}>
                                {{ $cls->class_name }} ({{ $cls->school->name ?? '' }})
                            </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Satu kelas hanya boleh mengirim 1 tim.</p>
                    </div>

                    {{-- Nama Tim (Opsional) --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nama Tim / Kelompok <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <input type="text" name="team_name" value="{{ old('team_name') }}"
                               placeholder="Contoh: TIGER, FALCON (Kosongkan jika nama kelas)"
                               class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-[11px] text-slate-400 mt-1">Jika dikosongkan, nama tim akan otomatis menggunakan nama kelas.</p>
                    </div>

                    {{-- Siswa Operator --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Siswa Operator (Yang Login Ujian) <span class="text-red-500">*</span>
                        </label>
                        <select name="operator_id" id="operatorSelect" required disabled
                                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white disabled:bg-slate-100 disabled:text-slate-400">
                            <option value="">Pilih kelas terlebih dahulu...</option>
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Siswa ini yang akan login dengan akunnya untuk mengerjakan soal bersama tim.</p>
                    </div>

                    {{-- Anggota Tim Tambahan --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Anggota Tim Lainnya <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <div id="membersContainer" class="max-h-48 overflow-y-auto p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2 text-sm text-slate-500">
                            Pilih kelas terlebih dahulu untuk melihat daftar siswa.
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-sm transition shadow-md shadow-blue-600/30 flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i>
                        <span>Simpan Tim Lomba</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Tabel Daftar Tim (Kolom Kanan / 7 Kolom) --}}
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Tim Terdaftar ({{ $teams->count() }})</h2>
                        <p class="text-xs text-slate-500">Daftar tim yang berhak mengikuti sesi lomba numerasi ini</p>
                    </div>
                    <span class="px-3 py-1 bg-indigo-50 text-indigo-700 font-bold text-xs rounded-full border border-indigo-200">
                        {{ $teams->count() }} Tim
                    </span>
                </div>

                @if($teams->isEmpty())
                <div class="p-12 text-center text-slate-400">
                    <i class="fas fa-users-slash text-4xl mb-3 text-slate-300"></i>
                    <p class="text-sm font-medium">Belum ada tim yang didaftarkan.</p>
                    <p class="text-xs text-slate-400 mt-1">Gunakan formulir di sebelah kiri untuk mendaftarkan tim perwakilan kelas.</p>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider border-b border-slate-100">
                            <tr>
                                <th class="py-3 px-4">No</th>
                                <th class="py-3 px-4">Nama Tim & Kelas</th>
                                <th class="py-3 px-4">Operator (Login)</th>
                                <th class="py-3 px-4">Anggota</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($teams as $idx => $team)
                            @php
                                $session = $team->sessions->first();
                                $operator = $team->members->firstWhere('is_operator', true)?->student;
                                $otherMembers = $team->members->where('is_operator', false);
                            @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-400">{{ $idx + 1 }}</td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800">{{ $team->display_name }}</div>
                                    <div class="text-xs text-slate-500 font-medium">{{ $team->classroom?->class_name ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($operator)
                                    <div class="font-semibold text-slate-800 text-xs">{{ $operator->full_name }}</div>
                                    <div class="text-[11px] text-slate-400">NIS: {{ $operator->nis ?? '-' }}</div>
                                    @else
                                    <span class="text-xs text-red-500 italic">Belum ada operator</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-600">
                                    @if($otherMembers->isNotEmpty())
                                    <ul class="list-disc list-inside space-y-0.5">
                                        @foreach($otherMembers as $m)
                                        <li>{{ $m->student?->full_name }}</li>
                                        @endforeach
                                    </ul>
                                    @else
                                    <span class="text-slate-400 italic">—</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if(!$session)
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-600 text-[11px] font-bold rounded-full">Belum Mulai</span>
                                    @elseif($session->status === 'in_progress')
                                    <span class="px-2.5 py-1 bg-blue-100 text-blue-700 text-[11px] font-bold rounded-full animate-pulse">Sedang Mengerjakan</span>
                                    @else
                                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 text-[11px] font-bold rounded-full">
                                        Selesai ({{ $session->result?->final_score ?? '-' }})
                                    </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <form action="{{ route('admin.cbt.competition.teams.destroy', [$exam, $team]) }}" method="POST"
                                          onsubmit="return confirm('Hapus tim {{ $team->display_name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 flex items-center justify-center transition mx-auto"
                                                title="Hapus Tim">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const classSelect = document.getElementById('classroomSelect');
    const operatorSelect = document.getElementById('operatorSelect');
    const membersContainer = document.getElementById('membersContainer');

    classSelect.addEventListener('change', function() {
        const classroomId = this.value;
        if (!classroomId) {
            operatorSelect.innerHTML = '<option value="">Pilih kelas terlebih dahulu...</option>';
            operatorSelect.disabled = true;
            membersContainer.innerHTML = 'Pilih kelas terlebih dahulu untuk melihat daftar siswa.';
            return;
        }

        operatorSelect.innerHTML = '<option value="">Memuat data siswa...</option>';
        operatorSelect.disabled = true;
        membersContainer.innerHTML = 'Memuat data siswa...';

        fetch(`{{ route('admin.cbt.competition.students-by-class') }}?classroom_id=${classroomId}`)
            .then(res => res.json())
            .then(students => {
                if (students.length === 0) {
                    operatorSelect.innerHTML = '<option value="">Tidak ada siswa aktif di kelas ini</option>';
                    membersContainer.innerHTML = 'Tidak ada siswa aktif di kelas ini.';
                    return;
                }

                // Populate Operator dropdown
                let opHtml = '<option value="">-- Pilih Siswa Operator --</option>';
                let memHtml = '';

                students.forEach(s => {
                    opHtml += `<option value="${s.id}">${s.full_name} (${s.nis || '-'})</option>`;
                    memHtml += `
                        <label class="flex items-center gap-2.5 p-1.5 rounded-lg hover:bg-white transition cursor-pointer">
                            <input type="checkbox" name="member_ids[]" value="${s.id}" class="rounded text-blue-600 focus:ring-blue-500 member-checkbox" data-id="${s.id}">
                            <span class="text-xs text-slate-700">${s.full_name}</span>
                        </label>
                    `;
                });

                operatorSelect.innerHTML = opHtml;
                operatorSelect.disabled = false;
                membersContainer.innerHTML = memHtml;
            })
            .catch(err => {
                operatorSelect.innerHTML = '<option value="">Gagal memuat siswa</option>';
                membersContainer.innerHTML = 'Gagal memuat siswa. Silakan coba lagi.';
                console.error(err);
            });
    });
});
</script>
@endsection
