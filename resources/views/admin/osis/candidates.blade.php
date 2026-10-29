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
        <div class="flex flex-wrap items-center gap-2">
            @if($election->candidates->count() > 0)
                <form action="{{ route('admin.osis.reset-candidates', $election->id) }}" method="POST" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin mengosongkan semua paslon simulasi & suara masuk? Gunakan ini jika ingin mengisi dari nol dengan paslon asli.');">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-amber-50 text-amber-800 border-2 border-amber-300 text-xs font-black hover:bg-amber-100 transition flex items-center gap-1.5 shadow-xs">
                        <i class="fas fa-rotate text-amber-600"></i> Bersihkan Data Simulasi
                    </button>
                </form>
            @endif

            <a href="{{ route('osis.booth') }}" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-50 text-emerald-800 border-2 border-emerald-300 text-xs font-bold hover:bg-emerald-100 transition flex items-center gap-1.5 shadow-xs">
                <i class="fas fa-eye text-emerald-600"></i> Bilik Suara
            </a>
            <a href="{{ route('admin.osis.index') }}" class="px-4 py-2 rounded-xl border-2 border-slate-900 text-xs font-bold text-slate-700 hover:bg-slate-100 transition">
                Kembali
            </a>
        </div>
    </div>

    {{-- Alert Catatan Data Simulasi --}}
    <div class="p-4 rounded-2xl bg-blue-50 border-2 border-blue-400 text-blue-950 text-xs flex items-center gap-3 shadow-xs">
        <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center flex-shrink-0">
            <i class="fas fa-circle-info"></i>
        </div>
        <div class="flex-1 leading-relaxed">
            <strong class="font-black">Catatan Data Simulasi:</strong> Paslon yang tampil saat ini merupakan data contoh/simulasi untuk uji coba tampilan sistem. Anda dapat langsung <strong>mengedit nama & foto paslon</strong> menjadi calon asli, menghapusnya satu per satu, atau mengklik tombol <em>"Bersihkan Data Simulasi"</em> untuk menginput paslon asli dari awal.
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
                    
                    <div class="flex-1 space-y-2 w-full">
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
                            {{-- Tombol Edit Paslon --}}
                            <button type="button" 
                                    onclick="openEditCandidateModal({{ json_encode($c) }})"
                                    class="px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 border border-blue-300 text-[11px] font-bold hover:bg-blue-100 transition flex items-center gap-1">
                                <i class="fas fa-pen"></i> Edit
                            </button>

                            {{-- Tombol Hapus Paslon --}}
                            <form action="{{ route('admin.osis.destroy-candidate', [$election->id, $c->id]) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus paslon ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 border border-rose-300 text-[11px] font-bold hover:bg-rose-100 transition flex items-center gap-1">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl border-2 border-slate-300 p-8 text-center text-slate-400 font-bold text-xs">
                    Belum ada paslon yang diinput. Gunakan form di sebelah kanan untuk menambahkan paslon asli.
                </div>
            @endforelse
        </div>

        {{-- FORM TAMBAH PASLON (5 COLS) --}}
        <div class="lg:col-span-5 bg-white rounded-3xl border-2 border-slate-900 shadow-xl p-6">
            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fas fa-user-plus text-emerald-600"></i> Tambah Pasangan Calon Asli
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

{{-- MODAL EDIT PASLON --}}
<div id="modal-edit-candidate" class="fixed inset-0 z-50 hidden bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl border-2 border-slate-900 shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="text-sm font-black text-slate-900 uppercase">Edit Data Paslon</h3>
            <button type="button" onclick="closeEditCandidateModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        <form id="form-edit-candidate" action="" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">Nomor Urut</label>
                    <input type="number" id="edit-candidate-number" name="candidate_number" min="1" class="w-full rounded-xl border-2 border-slate-900 p-2 text-xs font-bold" required>
                </div>
                <div>
                    <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">Tipe Paslon</label>
                    <select id="edit-candidate-type" name="candidate_type" class="w-full rounded-xl border-2 border-slate-900 p-2 text-xs font-bold">
                        <option value="presidium_ksb">Ketua, Sekr, Bendahara</option>
                        <option value="pair">Ketua & Wakil</option>
                        <option value="single">Calon Tunggal (Ketua)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">Nama Calon Ketua</label>
                <input type="text" id="edit-leader-name" name="leader_name" class="w-full rounded-xl border-2 border-slate-900 p-2 text-xs font-bold" required>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">Nama Sekretaris</label>
                    <input type="text" id="edit-secretary-name" name="secretary_name" class="w-full rounded-xl border-2 border-slate-900 p-2 text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">Nama Bendahara</label>
                    <input type="text" id="edit-treasurer-name" name="treasurer_name" class="w-full rounded-xl border-2 border-slate-900 p-2 text-xs">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">Ganti Foto Kartu Paslon (Opsional)</label>
                <input type="file" name="photo" accept="image/*" class="w-full text-xs text-slate-500">
            </div>

            <div>
                <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">Ganti Flyer Poster HD (Opsional)</label>
                <input type="file" name="poster_flyer" accept="image/*" class="w-full text-xs text-slate-500">
            </div>

            <div>
                <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">Kutipan Slogan</label>
                <textarea id="edit-quote-slogan" name="quote_slogan" rows="2" class="w-full rounded-xl border-2 border-slate-900 p-2 text-xs"></textarea>
            </div>

            <div>
                <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">Visi</label>
                <textarea id="edit-vision" name="vision" rows="2" class="w-full rounded-xl border-2 border-slate-900 p-2 text-xs"></textarea>
            </div>

            <div>
                <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">Misi (Satu per Baris)</label>
                <textarea id="edit-mission" name="mission" rows="3" class="w-full rounded-xl border-2 border-slate-900 p-2 text-xs font-mono"></textarea>
            </div>

            <div>
                <label class="block text-[11px] font-black uppercase text-slate-700 mb-1">Program Kerja (Satu per Baris)</label>
                <textarea id="edit-programs" name="work_programs" rows="3" class="w-full rounded-xl border-2 border-slate-900 p-2 text-xs font-mono"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeEditCandidateModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-700">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 text-white font-black text-xs shadow border-2 border-slate-900">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditCandidateModal(c) {
        const form = document.getElementById('form-edit-candidate');
        form.action = `/admin/osis/{{ $election->id }}/candidates/` + c.id;

        document.getElementById('edit-candidate-number').value = c.candidate_number || '';
        document.getElementById('edit-candidate-type').value = c.candidate_type || 'presidium_ksb';
        document.getElementById('edit-leader-name').value = c.leader_name || '';
        document.getElementById('edit-secretary-name').value = c.secretary_name || '';
        document.getElementById('edit-treasurer-name').value = c.treasurer_name || '';
        document.getElementById('edit-quote-slogan').value = c.quote_slogan || '';
        document.getElementById('edit-vision').value = c.vision || '';

        if (Array.isArray(c.mission)) {
            document.getElementById('edit-mission').value = c.mission.join("\n");
        } else {
            document.getElementById('edit-mission').value = c.mission || '';
        }

        if (Array.isArray(c.work_programs)) {
            document.getElementById('edit-programs').value = c.work_programs.join("\n");
        } else {
            document.getElementById('edit-programs').value = c.work_programs || '';
        }

        document.getElementById('modal-edit-candidate').classList.remove('hidden');
    }

    function closeEditCandidateModal() {
        document.getElementById('modal-edit-candidate').classList.add('hidden');
    }
</script>
@endsection
