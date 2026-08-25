@extends('layouts.admin')

@section('title', 'Edit Data Pendaftar PSB')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-700 rounded-2xl p-6 text-white relative overflow-hidden shadow-sm">
        <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4"></div>
        <div class="absolute bottom-0 left-1/3 w-24 h-24 bg-white/5 rounded-full translate-y-1/2"></div>
        <div class="relative">
            <div class="flex items-center text-sm text-white/70 mb-2 gap-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white transition">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.psb.applicants.index') }}" class="hover:text-white transition">PSB</a>
                <span>/</span>
                <a href="{{ route('admin.psb.applicants.show', $applicant) }}" class="hover:text-white transition">{{ $applicant->registration_number }}</a>
                <span>/</span>
                <span class="text-white font-semibold">Edit Data</span>
            </div>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold flex items-center gap-2.5">
                        <i class="fas fa-user-edit"></i> Edit Pendaftar: {{ $applicant->full_name }}
                    </h1>
                    <p class="text-xs text-emerald-100 mt-1 font-mono">
                        No. Registrasi: <b>{{ $applicant->registration_number }}</b> &bull; Unit: <b>{{ $applicant->school->name }}</b>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.psb.applicants.show', $applicant) }}" class="px-5 py-2.5 bg-white/20 hover:bg-white/30 text-white rounded-xl font-semibold transition flex items-center gap-2 text-sm shadow-sm">
                        <i class="fas fa-eye"></i> Lihat Detail
                    </a>
                    <a href="{{ route('admin.psb.applicants.index') }}" class="px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl font-semibold transition flex items-center gap-2 text-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
    <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-4.5 rounded-xl shadow-sm">
        <div class="flex items-center gap-2 font-bold text-sm mb-1">
            <i class="fas fa-exclamation-triangle"></i> Terdapat beberapa kesalahan input:
        </div>
        <ul class="list-disc list-inside text-xs space-y-1">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Form Edit --}}
    <form action="{{ route('admin.psb.applicants.update', $applicant) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Kolom Kiri & Tengah: Data Utama --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- 1. Informasi Unit & Pendaftaran --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-7 space-y-5">
                    <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">
                            <i class="fas fa-school"></i>
                        </span>
                        1. Unit Sekolah & Jalur Pendaftaran
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">Unit Sekolah *</label>
                            <select name="school_id" id="school_id" required class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                @foreach($schools as $sch)
                                <option value="{{ $sch->id }}" {{ old('school_id', $applicant->school_id) == $sch->id ? 'selected' : '' }}>
                                    {{ $sch->name }} ({{ $sch->level ?? strtoupper($sch->code) }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">Tahun Pelajaran *</label>
                            <select name="academic_year_id" required class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ old('academic_year_id', $applicant->academic_year_id) == $ay->id ? 'selected' : '' }}>
                                    {{ $ay->name }} {{ $ay->is_active ? '(Aktif)' : '' }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">Jalur Pendaftaran</label>
                            <select name="admission_path" class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                <option value="reguler" {{ old('admission_path', $applicant->admission_path) == 'reguler' ? 'selected' : '' }}>Reguler / Umum</option>
                                <option value="prestasi" {{ old('admission_path', $applicant->admission_path) == 'prestasi' ? 'selected' : '' }}>Jalur Prestasi (Akademik/Non-Akademik)</option>
                                <option value="afirmasi" {{ old('admission_path', $applicant->admission_path) == 'afirmasi' ? 'selected' : '' }}>Jalur Afirmasi / KIP / Kurang Mampu</option>
                                <option value="zonasi" {{ old('admission_path', $applicant->admission_path) == 'zonasi' ? 'selected' : '' }}>Jalur Domisili / Zonasi</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">Gelombang Pendaftaran</label>
                            <select name="wave_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                <option value="">-- Tanpa Gelombang Khusus --</option>
                                @foreach($waves as $wv)
                                <option value="{{ $wv->id }}" {{ old('wave_id', $applicant->wave_id) == $wv->id ? 'selected' : '' }}>
                                    {{ $wv->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Pilihan Kejuruan (SMK Only) --}}
                    @if(count($programKeahlians) > 0 || str_contains(strtolower($applicant->school->name ?? ''), 'smk'))
                    <div class="pt-3 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">Program Keahlian (SMK)</label>
                            <select name="program_keahlian_id" id="program_keahlian_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                <option value="">-- Pilih Program Keahlian --</option>
                                @foreach($programKeahlians as $pk)
                                <option value="{{ $pk->id }}" {{ old('program_keahlian_id', $applicant->program_keahlian_id) == $pk->id ? 'selected' : '' }}>
                                    {{ $pk->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">Konsentrasi Keahlian (SMK)</label>
                            <select name="konsentrasi_keahlian_id" id="konsentrasi_keahlian_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                <option value="">-- Pilih Konsentrasi Keahlian --</option>
                                @foreach($konsentrasiKeahlians as $kk)
                                <option value="{{ $kk->id }}" {{ old('konsentrasi_keahlian_id', $applicant->konsentrasi_keahlian_id) == $kk->id ? 'selected' : '' }}>
                                    {{ $kk->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- 2. Biodata Calon Siswa --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-7 space-y-5">
                    <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-lg bg-teal-100 text-teal-600 flex items-center justify-center text-xs">
                            <i class="fas fa-user"></i>
                        </span>
                        2. Biodata Pribadi Calon Siswa
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 mb-2">Nama Lengkap Siswa *</label>
                            <input type="text" name="full_name" value="{{ old('full_name', $applicant->full_name) }}" required class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-semibold">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">NISN</label>
                            <input type="text" name="nisn" value="{{ old('nisn', $applicant->nisn) }}" placeholder="Nomor Induk Siswa Nasional (10 digit)" class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">Jenis Kelamin *</label>
                            <select name="gender" required class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                <option value="L" {{ old('gender', $applicant->gender) == 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                                <option value="P" {{ old('gender', $applicant->gender) == 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">Tempat Lahir *</label>
                            <input type="text" name="birth_place" value="{{ old('birth_place', $applicant->birth_place) }}" required class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">Tanggal Lahir *</label>
                            <input type="date" name="birth_date" value="{{ old('birth_date', $applicant->birth_date ? \Carbon\Carbon::parse($applicant->birth_date)->format('Y-m-d') : '') }}" required class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">Agama *</label>
                            <select name="religion" required class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                @foreach(['Kristen Protestan', 'Katolik', 'Islam', 'Hindu', 'Buddha', 'Konghucu'] as $rel)
                                <option value="{{ $rel }}" {{ old('religion', $applicant->religion) == $rel ? 'selected' : '' }}>{{ $rel }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">Asal Sekolah Sebelumnya *</label>
                            <input type="text" name="previous_school" value="{{ old('previous_school', $applicant->previous_school) }}" required placeholder="Contoh: SMPN 1 Gunungsitoli" class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">No. HP / WhatsApp Siswa *</label>
                            <input type="text" name="phone" value="{{ old('phone', $applicant->phone) }}" required class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">Email Siswa (Opsional)</label>
                            <input type="email" name="email" value="{{ old('email', $applicant->email) }}" class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 mb-2">Alamat Domisili Siswa *</label>
                            <textarea name="address" rows="3" required class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">{{ old('address', $applicant->address) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- 3. Data Orang Tua / Wali --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-7 space-y-5">
                    <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-lg bg-cyan-100 text-cyan-600 flex items-center justify-center text-xs">
                            <i class="fas fa-user-friends"></i>
                        </span>
                        3. Data Orang Tua / Wali
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">Nama Ayah</label>
                            <input type="text" name="father_name" value="{{ old('father_name', $applicant->father_name) }}" class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">No. HP / WA Ayah</label>
                            <input type="text" name="father_phone" value="{{ old('father_phone', $applicant->father_phone) }}" class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">Pekerjaan Ayah</label>
                            <input type="text" name="father_occupation" value="{{ old('father_occupation', $applicant->father_occupation) }}" class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">Nama Ibu</label>
                            <input type="text" name="mother_name" value="{{ old('mother_name', $applicant->mother_name) }}" class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">No. HP / WA Ibu</label>
                            <input type="text" name="mother_phone" value="{{ old('mother_phone', $applicant->mother_phone) }}" class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-2">Pekerjaan Ibu</label>
                            <input type="text" name="mother_occupation" value="{{ old('mother_occupation', $applicant->mother_occupation) }}" class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block text-xs font-bold text-gray-700 mb-2">Rentang Penghasilan Orang Tua / Bulan</label>
                            <select name="parent_income" class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                <option value="">-- Pilih Rentang Penghasilan --</option>
                                @foreach(['< Rp 1.000.000', 'Rp 1.000.000 - Rp 2.500.000', 'Rp 2.500.000 - Rp 5.000.000', 'Rp 5.000.000 - Rp 10.000.000', '> Rp 10.000.000'] as $inc)
                                <option value="{{ $inc }}" {{ old('parent_income', $applicant->parent_income) == $inc ? 'selected' : '' }}>{{ $inc }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Kolom Kanan: Foto, Status & Aksi --}}
            <div class="space-y-6">
                
                {{-- Foto Pas --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-7 space-y-4 text-center">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider text-left border-b border-gray-100 pb-3">
                        Foto Calon Siswa
                    </h3>

                    <div class="w-32 h-40 mx-auto rounded-2xl overflow-hidden border-2 border-dashed border-emerald-300 bg-emerald-50/50 flex items-center justify-center relative shadow-sm">
                        @if($applicant->photo_path)
                        <img id="photo-preview" src="{{ asset('storage/' . $applicant->photo_path) }}" alt="{{ $applicant->full_name }}" class="w-full h-full object-cover">
                        @else
                        <div id="photo-placeholder" class="text-emerald-400 text-center p-2">
                            <i class="fas fa-user-circle text-4xl mb-1"></i>
                            <p class="text-[10px] font-semibold">Belum Ada Foto</p>
                        </div>
                        <img id="photo-preview" src="" alt="Preview" class="w-full h-full object-cover hidden">
                        @endif
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-2">Ganti Pas Foto (Max 2MB)</label>
                        <input type="file" name="photo" accept="image/*" onchange="previewImage(this)" class="block w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                    </div>
                </div>

                {{-- Status & Catatan --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-7 space-y-5">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider border-b border-gray-100 pb-3 flex items-center gap-2">
                        <i class="fas fa-tasks text-emerald-600"></i> Status Pendaftaran
                    </h3>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2">Status Saat Ini *</label>
                        <select name="status" required class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm font-bold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="draft" {{ old('status', $applicant->status) == 'draft' ? 'selected' : '' }}>Draft (Belum Lengkap)</option>
                            <option value="submitted" {{ old('status', $applicant->status) == 'submitted' ? 'selected' : '' }}>Submitted (Terkirim)</option>
                            <option value="verified" {{ old('status', $applicant->status) == 'verified' ? 'selected' : '' }}>Verified (Berkas Terverifikasi)</option>
                            <option value="tested" {{ old('status', $applicant->status) == 'tested' ? 'selected' : '' }}>Tested (Sudah Tes Masuk)</option>
                            <option value="accepted" {{ old('status', $applicant->status) == 'accepted' ? 'selected' : '' }}>Accepted (Diterima / Lulus)</option>
                            <option value="rejected" {{ old('status', $applicant->status) == 'rejected' ? 'selected' : '' }}>Rejected (Tidak Diterima)</option>
                            <option value="re-registered" {{ old('status', $applicant->status) == 're-registered' ? 'selected' : '' }}>Re-registered (Sudah Daftar Ulang)</option>
                            <option value="withdrawn" {{ old('status', $applicant->status) == 'withdrawn' ? 'selected' : '' }}>Withdrawn (Mengundurkan Diri)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2">Catatan Panitia PSB (Internal)</label>
                        <textarea name="notes" rows="4" placeholder="Catatan kelengkapan berkas, nomor konfirmasi, rekomendasi, dll..." class="w-full px-4 py-3 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">{{ old('notes', $applicant->notes) }}</textarea>
                    </div>

                    {{-- Tombol Aksi Simpan --}}
                    <div class="pt-4 border-t border-gray-100 space-y-3">
                        <button type="submit" class="w-full px-8 py-3.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-xl font-bold text-sm shadow-md shadow-emerald-500/20 flex items-center justify-center gap-2.5 transition active:scale-[0.98]">
                            <i class="fas fa-save"></i>
                            <span>Simpan Perubahan Pendaftar</span>
                        </button>
                        <a href="{{ route('admin.psb.applicants.show', $applicant) }}" class="w-full px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition">
                            Batal & Kembali
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </form>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('photo-preview');
            const placeholder = document.getElementById('photo-placeholder');
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            if (placeholder) placeholder.classList.add('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
