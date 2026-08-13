@extends('mobile.layouts.app')

@section('title', 'Absensi Kelas 3D - Guru Mobile')

@section('content')
<div class="space-y-4" x-data="{ mode: 'pelajaran', editMode: true }">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-900">Absensi Kelas Siswa 📋</h2>
            <p class="text-[11px] text-slate-500 font-bold">Input & Edit Kehadiran Belajar Siswa</p>
        </div>
    </div>

    <!-- Mode Tab Switcher (Clay Pills) -->
    <div class="flex items-center space-x-2 border-b-2 border-slate-200/80 pb-2">
        <button @click="mode = 'pelajaran'" 
                :class="mode === 'pelajaran' ? 'clay-purple text-white shadow-md scale-105 font-black' : 'bg-white text-slate-600 border-2 border-slate-200 font-bold'"
                class="flex-1 py-2.5 px-3 rounded-2xl text-xs transition text-center flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-chalkboard-user"></i> 1. Absen Belajar (Edit)
        </button>
        <button @click="mode = 'harian'" 
                :class="mode === 'harian' ? 'clay-purple text-white shadow-md scale-105 font-black' : 'bg-white text-slate-600 border-2 border-slate-200 font-bold'"
                class="flex-1 py-2.5 px-3 rounded-2xl text-xs transition text-center flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-school"></i> 2. Absen Sekolah (Harian)
        </button>
    </div>

    <!-- Classroom & Date Select Card (Clay Card) -->
    <div class="clay-card p-5 space-y-3">
        <form action="{{ route('mobile.guru.absensi.input') }}" method="GET" class="space-y-3" id="filterForm">
            <div>
                <label for="classroom_id" class="block text-xs font-black text-slate-800 mb-1">Pilih Kelas Saya</label>
                <select id="classroom_id" name="classroom_id" onchange="this.form.submit()" required
                        class="w-full px-4 py-3 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-black focus:outline-none focus:border-purple-500 transition">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($classrooms as $c)
                        <option value="{{ $c->id }}" {{ $selectedClassroomId == $c->id ? 'selected' : '' }}>{{ $c->class_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="date" class="block text-xs font-black text-slate-800 mb-1">Tanggal Absensi</label>
                <input type="date" id="date" name="date" value="{{ $date }}" onchange="this.form.submit()" required
                       class="w-full px-4 py-3 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-black focus:outline-none focus:border-purple-500 transition">
            </div>
        </form>
    </div>

    <!-- MODE 1: ABSEN KEHADIRAN BELAJAR (INPUT & EDIT) -->
    <div x-show="mode === 'pelajaran'" class="space-y-3">
        @if($selectedClassroomId && count($students) > 0)
        <!-- Toolbar Mode Edit -->
        <div class="clay-card p-4 flex flex-col gap-2.5 bg-purple-50/70 border-2 border-purple-200">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black text-purple-900 flex items-center gap-1.5">
                    <i class="fa-solid fa-pen-to-square text-purple-600"></i> Mode Edit Absen Belajar
                </span>
                <button type="button" @click="editMode = !editMode"
                        :class="editMode ? 'clay-purple text-white' : 'bg-white text-slate-700 border-2 border-slate-200'"
                        class="px-3 py-1 text-[11px] font-black rounded-xl transition shadow-sm">
                    <span x-text="editMode ? '🔓 Edit Aktif' : '🔒 Terkunci'"></span>
                </button>
            </div>

            @if($assignmentRuleInfo)
            <div class="bg-amber-100/90 border border-amber-300 text-amber-900 rounded-xl px-3 py-1.5 text-[11px] font-black flex items-center gap-1.5">
                <i class="fa-solid fa-layer-group text-amber-600"></i>
                <span>Aturan Siswa: <strong>{{ $assignmentRuleInfo }}</strong></span>
            </div>
            @endif

            <div class="flex items-center justify-between pt-1">
                <button type="button" onclick="markAllHadir()" 
                        class="px-3.5 py-2 bg-emerald-500 text-white font-black text-xs rounded-xl shadow-md border-2 border-white flex items-center gap-1 hover:bg-emerald-600 transition">
                    <i class="fa-solid fa-check-double"></i> Hadirkan Semua
                </button>

                <span class="text-[10px] text-purple-900 font-bold bg-white px-2.5 py-1 rounded-lg border border-purple-200">
                    {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d M Y') }}
                </span>
            </div>
        </div>

        <!-- Form Student Attendance Input -->
        <form action="{{ route('mobile.guru.absensi.store') }}" method="POST" class="space-y-3" id="attendanceForm">
            @csrf
            <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
            <input type="hidden" name="date" value="{{ $date }}">

            <div class="flex items-center justify-between px-1">
                <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">Daftar Siswa ({{ count($students) }})</h3>
                <span class="text-[10px] text-slate-500 font-bold">Pilih Status Kehadiran</span>
            </div>

            <div class="space-y-2.5">
                @foreach($students as $st)
                    @php $currentStatus = $existingAttendances[$st->id] ?? 'hadir'; @endphp
                    <div class="clay-card p-4 space-y-2.5" x-data="{ status: '{{ $currentStatus }}' }" @mark-all-hadir.window="status = 'hadir'">
                        <input type="hidden" name="attendances[{{ $st->id }}]" :value="status">
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-xs font-black text-slate-900">{{ $st->full_name }}</h4>
                                <span class="text-[10px] text-slate-500 font-bold">NISN: {{ $st->nisn ?? '-' }}</span>
                            </div>
                            <span class="text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full"
                                  :class="{
                                      'clay-green': status === 'hadir',
                                      'clay-yellow': status === 'terlambat',
                                      'clay-purple': status === 'sakit',
                                      'clay-blue': status === 'izin',
                                      'clay-pink': status === 'alpha'
                                  }" x-text="status">
                            </span>
                        </div>

                        <!-- Status Selection Buttons -->
                        <div class="grid grid-cols-5 gap-1.5 pt-1" x-show="editMode">
                            <button type="button" @click="status = 'hadir'"
                                    :class="status === 'hadir' ? 'clay-green text-white font-black scale-105' : 'bg-slate-100 text-slate-600 border border-slate-200 font-bold'"
                                    class="py-2 text-center text-[10px] rounded-xl transition">
                                Hadir
                            </button>
                            <button type="button" @click="status = 'terlambat'"
                                    :class="status === 'terlambat' ? 'clay-yellow text-white font-black scale-105' : 'bg-slate-100 text-slate-600 border border-slate-200 font-bold'"
                                    class="py-2 text-center text-[10px] rounded-xl transition">
                                Late
                            </button>
                            <button type="button" @click="status = 'sakit'"
                                    :class="status === 'sakit' ? 'clay-purple text-white font-black scale-105' : 'bg-slate-100 text-slate-600 border border-slate-200 font-bold'"
                                    class="py-2 text-center text-[10px] rounded-xl transition">
                                Sakit
                            </button>
                            <button type="button" @click="status = 'izin'"
                                    :class="status === 'izin' ? 'clay-blue text-white font-black scale-105' : 'bg-slate-100 text-slate-600 border border-slate-200 font-bold'"
                                    class="py-2 text-center text-[10px] rounded-xl transition">
                                Izin
                            </button>
                            <button type="button" @click="status = 'alpha'"
                                    :class="status === 'alpha' ? 'clay-pink text-white font-black scale-105' : 'bg-slate-100 text-slate-600 border border-slate-200 font-bold'"
                                    class="py-2 text-center text-[10px] rounded-xl transition">
                                Alpha
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Submit Action -->
            <button type="submit" class="clay-btn w-full py-4 text-white font-black text-xs uppercase tracking-wider shadow-lg">
                <i class="fa-solid fa-floppy-disk mr-1.5"></i> Simpan Kehadiran Belajar
            </button>
        </form>
        @elseif($selectedClassroomId)
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                Tidak ada siswa aktif terdaftar di kelas ini.
            </div>
        @else
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                <i class="fa-solid fa-hand-pointer text-3xl mb-2 text-purple-400"></i>
                <p>Pilih kelas terlebih dahulu dari menu di atas.</p>
            </div>
        @endif
    </div>

    <!-- MODE 2: ABSEN KEHADIRAN HARIAN SEKOLAH -->
    <div x-show="mode === 'harian'" class="space-y-3">
        @if($selectedClassroomId && count($students) > 0)
            <div class="clay-card p-4 space-y-3">
                <h3 class="text-xs font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-school text-purple-600"></i> Rekap Kehadiran Harian Sekolah
                </h3>
                <p class="text-[11px] text-slate-500 font-bold">Status presensi harian siswa yang tercatat di gate / harian sekolah.</p>

                <div class="space-y-2">
                    @foreach($students as $st)
                        @php $stStatus = $existingAttendances[$st->id] ?? 'hadir'; @endphp
                        <div class="p-3 rounded-2xl bg-[#f4f7fc] border-2 border-slate-200/80 flex items-center justify-between text-xs">
                            <div>
                                <h4 class="font-black text-slate-900">{{ $st->full_name }}</h4>
                                <span class="text-[10px] text-slate-500 font-bold">NISN: {{ $st->nisn ?? '-' }}</span>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase
                                {{ $stStatus === 'hadir' ? 'clay-green' : '' }}
                                {{ $stStatus === 'terlambat' ? 'clay-yellow' : '' }}
                                {{ in_array($stStatus, ['sakit', 'izin', 'alpha']) ? 'clay-pink' : '' }}">
                                {{ $stStatus }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                Pilih kelas terlebih dahulu untuk melihat rekap kehadiran harian.
            </div>
        @endif
    </div>
</div>

<script>
function markAllHadir() {
    window.dispatchEvent(new CustomEvent('mark-all-hadir'));
}
</script>
@endsection
