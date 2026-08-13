@extends('mobile.layouts.app')

@section('title', 'Input Absensi Siswa - Guru Mobile')

@section('content')
<div class="space-y-4">
    <div>
        <h2 class="text-lg font-extrabold text-white">Input Absensi Kelas</h2>
        <p class="text-[11px] text-slate-400">Presensi Massal Siswa dari HP Guru</p>
    </div>

    <!-- Classroom & Date Select Form -->
    <div class="glass-card rounded-2xl p-4">
        <form action="{{ route('mobile.guru.absensi.input') }}" method="GET" class="space-y-3">
            <div>
                <label for="classroom_id" class="block text-xs font-semibold text-slate-300 mb-1">Pilih Kelas</label>
                <select id="classroom_id" name="classroom_id" onchange="this.form.submit()" required
                        class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-white text-xs">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($classrooms as $c)
                        <option value="{{ $c->id }}" {{ $selectedClassroomId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="date" class="block text-xs font-semibold text-slate-300 mb-1">Tanggal</label>
                <input type="date" id="date" name="date" value="{{ $date }}" onchange="this.form.submit()" required
                       class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-white text-xs">
            </div>
        </form>
    </div>

    <!-- Students Attendance Form -->
    @if($selectedClassroomId && count($students) > 0)
    <form action="{{ route('mobile.guru.absensi.store') }}" method="POST" class="space-y-3">
        @csrf
        <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
        <input type="hidden" name="date" value="{{ $date }}">

        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider px-1">Daftar Siswa ({{ count($students) }})</h3>

        <div class="space-y-2.5">
            @foreach($students as $st)
                @php $currentStatus = $existingAttendances[$st->id] ?? 'hadir'; @endphp
                <div class="glass-card rounded-2xl p-3.5 space-y-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-xs font-bold text-white">{{ $st->full_name }}</h4>
                            <span class="text-[10px] text-slate-400">NISN: {{ $st->nisn ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-4 gap-1.5 pt-1">
                        <label class="cursor-pointer">
                            <input type="radio" name="attendances[{{ $st->id }}]" value="hadir" {{ $currentStatus === 'hadir' ? 'checked' : '' }} class="peer hidden">
                            <div class="py-1.5 text-center text-[10px] font-bold rounded-lg bg-slate-900 text-slate-400 border border-slate-800 peer-checked:bg-emerald-500/20 peer-checked:text-emerald-300 peer-checked:border-emerald-500/40">Hadir</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="attendances[{{ $st->id }}]" value="terlambat" {{ $currentStatus === 'terlambat' ? 'checked' : '' }} class="peer hidden">
                            <div class="py-1.5 text-center text-[10px] font-bold rounded-lg bg-slate-900 text-slate-400 border border-slate-800 peer-checked:bg-amber-500/20 peer-checked:text-amber-300 peer-checked:border-amber-500/40">Late</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="attendances[{{ $st->id }}]" value="sakit" {{ $currentStatus === 'sakit' ? 'checked' : '' }} class="peer hidden">
                            <div class="py-1.5 text-center text-[10px] font-bold rounded-lg bg-slate-900 text-slate-400 border border-slate-800 peer-checked:bg-indigo-500/20 peer-checked:text-indigo-300 peer-checked:border-indigo-500/40">Sakit</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="attendances[{{ $st->id }}]" value="alpha" {{ $currentStatus === 'alpha' ? 'checked' : '' }} class="peer hidden">
                            <div class="py-1.5 text-center text-[10px] font-bold rounded-lg bg-slate-900 text-slate-400 border border-slate-800 peer-checked:bg-rose-500/20 peer-checked:text-rose-300 peer-checked:border-rose-500/40">Alpha</div>
                        </label>
                    </div>
                </div>
            @endforeach
        </div>

        <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-indigo-600 to-indigo-500 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-indigo-600/30">
            <i class="fa-solid fa-save mr-1.5"></i> Simpan Absensi Kelas
        </button>
    </form>
    @endif
</div>
@endsection
