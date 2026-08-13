@extends('mobile.layouts.app')

@section('title', 'Input Absensi 3D - Guru Mobile')

@section('content')
<div class="space-y-4">
    <div>
        <h2 class="text-xl font-black text-slate-900">Input Absensi Kelas 📋</h2>
        <p class="text-[11px] text-slate-500 font-bold">Presensi Massal Siswa dari HP Guru</p>
    </div>

    <!-- Classroom & Date Select Form (Clay Card) -->
    <div class="clay-card p-5">
        <form action="{{ route('mobile.guru.absensi.input') }}" method="GET" class="space-y-3">
            <div>
                <label for="classroom_id" class="block text-xs font-black text-slate-800 mb-1.5">Pilih Kelas</label>
                <select id="classroom_id" name="classroom_id" onchange="this.form.submit()" required
                        class="w-full px-4 py-3 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($classrooms as $c)
                        <option value="{{ $c->id }}" {{ $selectedClassroomId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="date" class="block text-xs font-black text-slate-800 mb-1.5">Tanggal</label>
                <input type="date" id="date" name="date" value="{{ $date }}" onchange="this.form.submit()" required
                       class="w-full px-4 py-3 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold">
            </div>
        </form>
    </div>

    <!-- Students Attendance Form (Clay Cards) -->
    @if($selectedClassroomId && count($students) > 0)
    <form action="{{ route('mobile.guru.absensi.store') }}" method="POST" class="space-y-3">
        @csrf
        <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
        <input type="hidden" name="date" value="{{ $date }}">

        <h3 class="text-xs font-black text-slate-500 uppercase tracking-wider px-1">Daftar Siswa ({{ count($students) }})</h3>

        <div class="space-y-2.5">
            @foreach($students as $st)
                @php $currentStatus = $existingAttendances[$st->id] ?? 'hadir'; @endphp
                <div class="clay-card p-4 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-xs font-black text-slate-900">{{ $st->full_name }}</h4>
                            <span class="text-[10px] text-slate-500 font-bold">NISN: {{ $st->nisn ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-4 gap-2 pt-1">
                        <label class="cursor-pointer">
                            <input type="radio" name="attendances[{{ $st->id }}]" value="hadir" {{ $currentStatus === 'hadir' ? 'checked' : '' }} class="peer hidden">
                            <div class="py-2 text-center text-[10px] font-black rounded-xl bg-slate-100 text-slate-600 border-2 border-slate-200 peer-checked:clay-green peer-checked:border-white transition">Hadir</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="attendances[{{ $st->id }}]" value="terlambat" {{ $currentStatus === 'terlambat' ? 'checked' : '' }} class="peer hidden">
                            <div class="py-2 text-center text-[10px] font-black rounded-xl bg-slate-100 text-slate-600 border-2 border-slate-200 peer-checked:clay-yellow peer-checked:border-white transition">Late</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="attendances[{{ $st->id }}]" value="sakit" {{ $currentStatus === 'sakit' ? 'checked' : '' }} class="peer hidden">
                            <div class="py-2 text-center text-[10px] font-black rounded-xl bg-slate-100 text-slate-600 border-2 border-slate-200 peer-checked:clay-purple peer-checked:border-white transition">Sakit</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="attendances[{{ $st->id }}]" value="alpha" {{ $currentStatus === 'alpha' ? 'checked' : '' }} class="peer hidden">
                            <div class="py-2 text-center text-[10px] font-black rounded-xl bg-slate-100 text-slate-600 border-2 border-slate-200 peer-checked:clay-pink peer-checked:border-white transition">Alpha</div>
                        </label>
                    </div>
                </div>
            @endforeach
        </div>

        <button type="submit" class="clay-btn w-full py-4 text-white font-black text-xs uppercase tracking-wider">
            <i class="fa-solid fa-save mr-1.5"></i> Simpan Absensi Kelas
        </button>
    </form>
    @endif
</div>
@endsection
