@extends('layouts.admin')
@section('title', 'Buat Agenda Pemilihan OSIS - Admin PembdaHUB')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto pb-12">
    <div class="flex items-center justify-between bg-white p-6 rounded-3xl border-2 border-slate-900 shadow-md">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-500 mb-1">
                <a href="{{ route('admin.osis.index') }}" class="hover:text-emerald-600">E-Voting OSIS</a>
                <span>&rsaquo;</span>
                <span class="text-slate-800">Buat Agenda Baru</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                Tambah Agenda Pemilihan OSIS
            </h1>
        </div>
        <a href="{{ route('admin.osis.index') }}" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-100 transition">
            Batal
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border-2 border-rose-500 text-rose-900 text-xs font-bold space-y-1">
            @foreach ($errors->all() as $err)
                <div>&bull; {{ $err }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('admin.osis.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-3xl p-6 md:p-8 border-2 border-slate-900 shadow-xl space-y-6">
        @csrf

        {{-- Unit Sekolah & Tahun Ajaran --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-black uppercase text-slate-700 mb-1.5">Unit Sekolah <span class="text-rose-500">*</span></label>
                <select name="school_id" class="w-full rounded-xl border-2 border-slate-900 p-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500" required>
                    <option value="">-- Pilih Unit Sekolah --</option>
                    @foreach($schools as $sch)
                        <option value="{{ $sch->id }}" {{ old('school_id') == $sch->id ? 'selected' : '' }}>
                            {{ $sch->name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[10px] text-slate-400 mt-1">Hanya 3 unit sekolah aktif (SMK, SMA, SMP).</p>
            </div>

            <div>
                <label class="block text-xs font-black uppercase text-slate-700 mb-1.5">Tahun Ajaran <span class="text-rose-500">*</span></label>
                <select name="academic_year_id" class="w-full rounded-xl border-2 border-slate-900 p-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500">
                    @foreach($academicYears as $ay)
                        <option value="{{ $ay->id }}" {{ (old('academic_year_id', $activeYear?->id) == $ay->id) ? 'selected' : '' }}>
                            {{ $ay->year }} {{ $ay->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Judul & Periode --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="md:col-span-2">
                <label class="block text-xs font-black uppercase text-slate-700 mb-1.5">Judul Agenda Pemilihan <span class="text-rose-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', 'PEMILIHAN OSIS SMK SWASTA PEMDA NIAS') }}" class="w-full rounded-xl border-2 border-slate-900 p-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500" placeholder="Contoh: PEMILIHAN OSIS SMK SWASTA PEMDA NIAS" required>
            </div>

            <div>
                <label class="block text-xs font-black uppercase text-slate-700 mb-1.5">Periode Masa Bakti <span class="text-rose-500">*</span></label>
                <input type="text" name="period" value="{{ old('period', '2026 / 2027') }}" class="w-full rounded-xl border-2 border-slate-900 p-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500" placeholder="Contoh: 2026 / 2027" required>
            </div>
        </div>

        {{-- Tagline & Deskripsi --}}
        <div>
            <label class="block text-xs font-black uppercase text-slate-700 mb-1.5">Slogan / Tema Pemilihan</label>
            <input type="text" name="theme_tagline" value="{{ old('theme_tagline', 'Bersama Membangun Masa Depan yang Lebih Baik') }}" class="w-full rounded-xl border-2 border-slate-900 p-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500" placeholder="Slogan singkat di banner">
        </div>

        <div>
            <label class="block text-xs font-black uppercase text-slate-700 mb-1.5">Deskripsi / Pengantar</label>
            <textarea name="description" rows="3" class="w-full rounded-xl border-2 border-slate-900 p-2.5 text-xs text-slate-800 focus:ring-2 focus:ring-emerald-500" placeholder="Pengantar singkat tentang suksesi kepengurusan OSIS">{{ old('description') }}</textarea>
        </div>

        {{-- Jadwal Mulai & Berakhir --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 p-4 rounded-2xl bg-slate-50 border border-slate-200">
            <div>
                <label class="block text-xs font-black uppercase text-slate-700 mb-1.5">Waktu Mulai Pemungutan Suara <span class="text-rose-500">*</span></label>
                <input type="datetime-local" name="start_time" value="{{ old('start_time', now()->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border-2 border-slate-900 p-2 text-xs font-mono font-bold" required>
            </div>

            <div>
                <label class="block text-xs font-black uppercase text-slate-700 mb-1.5">Waktu Berakhir Pemungutan Suara <span class="text-rose-500">*</span></label>
                <input type="datetime-local" name="end_time" value="{{ old('end_time', now()->addDays(3)->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border-2 border-slate-900 p-2 text-xs font-mono font-bold" required>
            </div>
        </div>

        {{-- Setting Partisipasi & Fitur --}}
        <div class="space-y-3 pt-2">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-500">Pengaturan Hak Pilih & Transparansi</h3>

            <label class="flex items-center gap-3 p-3 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer">
                <input type="checkbox" name="allow_teachers" value="1" {{ old('allow_teachers', '1') ? 'checked' : '' }} class="w-4 h-4 rounded text-emerald-600 border-2 border-slate-900 focus:ring-0">
                <div class="text-xs">
                    <span class="font-black text-slate-900 block">Sertakan Guru sebagai Pemilih</span>
                    <span class="text-slate-500">Bapak/ibu guru unit sekolah ini dapat memberikan 1 suara sah di portal guru.</span>
                </div>
            </label>

            <label class="flex items-center gap-3 p-3 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer">
                <input type="checkbox" name="allow_staff" value="1" {{ old('allow_staff', '1') ? 'checked' : '' }} class="w-4 h-4 rounded text-emerald-600 border-2 border-slate-900 focus:ring-0">
                <div class="text-xs">
                    <span class="font-black text-slate-900 block">Sertakan Pegawai / Tendik sebagai Pemilih</span>
                    <span class="text-slate-500">Tenaga kependidikan dapat memberikan 1 suara sah.</span>
                </div>
            </label>

            <label class="flex items-center gap-3 p-3 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer">
                <input type="checkbox" name="show_realtime_count" value="1" {{ old('show_realtime_count') ? 'checked' : '' }} class="w-4 h-4 rounded text-emerald-600 border-2 border-slate-900 focus:ring-0">
                <div class="text-xs">
                    <span class="font-black text-slate-900 block">Buka Live Real Count untuk Umum Sebelum Selesai</span>
                    <span class="text-slate-500">Jika tidak dicentang (standar pemilu rahasia), perolehan kandidat baru tayang setelah waktu pemungutan suara resmi ditutup.</span>
                </div>
            </label>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <button type="submit" class="px-6 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md border-2 border-slate-900 transition hover:scale-105">
                Simpan & Lanjutkan ke Input Paslon &rarr;
            </button>
        </div>
    </form>
</div>
@endsection
