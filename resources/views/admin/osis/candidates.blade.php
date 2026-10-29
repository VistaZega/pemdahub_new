@extends('layouts.app')
@section('title', 'Kelola Paslon OSIS - ' . $election->title)

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12">
    
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-3xl border-2 border-slate-900 shadow-md">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-500 mb-1">
                <a href="{{ route('admin.osis.index') }}" class="hover:text-emerald-600">E-Voting OSIS</a>
                <span>&rsaquo;</span>
                <span class="text-slate-800">Kelola Paslon</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                Kandidat Paslon: {{ $election->school->name ?? 'OSIS' }}
            </h1>
            <p class="text-xs text-slate-600 mt-0.5">
                {{ $election->title }} &bull; Periode {{ $election->period }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('osis.booth') }}" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-300 text-xs font-bold hover:bg-emerald-100 transition">
                <i class="fas fa-eye mr-1"></i> Lihat Bilik Suara
            </a>
            <a href="{{ route('admin.osis.index') }}" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-100 transition">
                Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border-2 border-emerald-600 text-emerald-900 text-xs font-bold flex items-center gap-3">
            <i class="fas fa-circle-check text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Grid 2 Kolom: Kiri Daftar Paslon, Kanan Form Tambah --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        {{-- DAFTAR PASLON YANG SUDAH DIINPUT (7 COLS) --}}
        <div class="lg:col-span-7 space-y-4">
            <div class="flex items-center justify-between px-1">
                <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider">
                    Daftar Paslon Terdaftar ({{ $election->candidates->count() }})
                </h2>
            </div>

            @forelse($election->candidates as $c)
                <div class="bg-white rounded-3xl border-2 border-slate-900 shadow-md p-5 flex flex-col md:flex-row gap-5 items-start">
                    <img src="{{ $c->photo_url }}" alt="{{ $c->leader_name }}" class="w-24 h-24 rounded-2xl object-cover border-2 border-slate-900 flex-shrink-0 shadow">
                    
                    <div class="flex-1 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-0.5 rounded-lg bg-slate-900 text-white font-black text-xs font-mono">
                                PASLON NO. {{ $c->candidate_number }}
                            </span>
                            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                {{ number_format($c->vote_count) }} Suara Sah
                            </span>
                        </div>

                        <div>
                            <h3 class="font-black text-base text-slate-900">{{ $c->leader_name }} <span class="text-xs font-normal text-slate-500">(Ketua)</span></h3>
                            <div class="text-xs text-slate-600 mt-0.5 space-x-2">
                                <span>Sek: <strong>{{ $c->secretary_name ?? '-' }}</strong></span>
                                &bull;
                                <span>Ben: <strong>{{ $c->treasurer_name ?? '-' }}</strong></span>
                            </div>
                        </div>

                        <p class="text-xs text-slate-500 italic line-clamp-2">
                            &ldquo;{{ $c->quote_slogan }}&rdquo;
                        </p>

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2">
                            <form action="{{ route('admin.osis.destroy-candidate', [$election->id, $c->id]) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus paslon ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 border border-rose-300 text-[11px] font-bold hover:bg-rose-100 transition">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl border-2 border-slate-300 p-8 text-center text-slate-400 font-bold text-xs">
                    Belum ada paslon yang diinput. Gunakan form di sebelah kanan untuk menambahkan paslon.
                </div>
            @endforelse
        </div>

        {{-- FORM TAMBAH PASLON (5 COLS) --}}
        <div class="lg:col-span-5 bg-white rounded-3xl border-2 border-slate-900 shadow-xl p-6">
            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fas fa-user-plus text-emerald-600"></i> Tambah Pasangan Calon
            </h2>

            <form action="{{ route('admin.osis.store-candidate', $election->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">Nomor Urut <span class="text-rose-500">*</span></label>
                        <input type="number" name="candidate_number" value="{{ old('candidate_number', $election->candidates->count() + 1) }}" min="1" class="w-full rounded-xl border-2 border-slate-900 p-2 text-xs font-bold" required>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">Tipe Paslon</label>
                        <select name="candidate_type" class="w-full rounded-xl border-2 border-slate-900 p-2 text-xs font-bold">
                            <option value="presidium_ksb">Ketua, Sekr, Bendahara</option>
                            <option value="pair">Ketua & Wakil</option>
                            <option value="single">Calon Tunggal (Ketua)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">Nama Calon Ketua <span class="text-rose-500">*</span></label>
                    <input type="text" name="leader_name" class="w-full rounded-xl border-2 border-slate-900 p-2 text-xs font-bold" placeholder="Nama lengkap calon ketua" required>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">Nama Sekretaris</label>
                        <input type="text" name="secretary_name" class="w-full rounded-xl border-2 border-slate-900 p-2 text-xs" placeholder="Calon sekretaris">
                    </div>
                    <div>
                        <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">Nama Bendahara</label>
                        <input type="text" name="treasurer_name" class="w-full rounded-xl border-2 border-slate-900 p-2 text-xs" placeholder="Calon bendahara">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">Foto Kartu Paslon (PNG/JPG)</label>
                    <input type="file" name="photo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-2 file:border-slate-900 file:text-xs file:font-bold file:bg-slate-100 hover:file:bg-slate-200">
                </div>

                <div>
                    <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">Flyer Poster HD / Brosur</label>
                    <input type="file" name="poster_flyer" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-2 file:border-slate-900 file:text-xs file:font-bold file:bg-slate-100 hover:file:bg-slate-200">
                </div>

                <div>
                    <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">Kutipan / Slogan Singkat</label>
                    <textarea name="quote_slogan" rows="2" class="w-full rounded-xl border-2 border-slate-900 p-2 text-xs" placeholder="Slogan yang tampil di kartu depan"></textarea>
                </div>

                <div>
                    <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">Visi Lengkap</label>
                    <textarea name="vision" rows="2" class="w-full rounded-xl border-2 border-slate-900 p-2 text-xs" placeholder="Visi paslon"></textarea>
                </div>

                <div>
                    <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">Misi (Satu Misi per Baris)</label>
                    <textarea name="mission" rows="3" class="w-full rounded-xl border-2 border-slate-900 p-2 text-xs font-mono" placeholder="Meningkatkan kedisiplinan siswa&#10;Mendorong kompetisi kejuruan"></textarea>
                </div>

                <div>
                    <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">Program Kerja Unggulan (Satu per Baris)</label>
                    <textarea name="work_programs" rows="3" class="w-full rounded-xl border-2 border-slate-900 p-2 text-xs font-mono" placeholder="Pembda Tech Expo&#10;Kotak Aspirasi Digital"></textarea>
                </div>

                <button type="submit" class="w-full py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md border-2 border-slate-900 transition hover:scale-[1.02]">
                    Simpan Paslon Baru
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
