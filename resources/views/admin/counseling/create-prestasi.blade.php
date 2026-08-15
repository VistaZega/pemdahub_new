@extends('layouts.admin')
@section('title', 'Input Prestasi & Penghargaan Siswa - PembdaHUB')

@section('content')
<div class="space-y-8 w-full max-w-full px-2 sm:px-6 pb-12">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.counseling.index') }}" class="w-12 h-12 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition shadow-sm">
                <i class="fas fa-arrow-left text-base"></i>
            </a>
            <div>
                <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">🏆 Input Prestasi Siswa</h1>
                <p class="text-base font-bold text-amber-700 mt-1">Apresiasi pencapaian, kejuaraan & kebanggaan sekolah</p>
            </div>
        </div>
        <span class="hidden sm:inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-gradient-to-r from-amber-400 to-yellow-500 text-slate-950 font-black text-sm uppercase tracking-widest shadow-lg border-2 border-amber-500">
            <i class="fas fa-trophy"></i> Mode Prestasi
        </span>
    </div>

    @if($errors->any())
        <div class="bg-rose-50 border-2 border-rose-400 rounded-2xl p-6 shadow-md">
            <div class="flex items-center gap-3 mb-3">
                <i class="fas fa-exclamation-triangle text-rose-600 text-xl"></i>
                <h4 class="font-black text-rose-900 uppercase tracking-wider text-sm">Terdapat Kesalahan</h4>
            </div>
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li class="text-sm font-bold text-rose-800">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.counseling.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="record_type" value="penghargaan">

        <div class="w-full space-y-8">
            {{-- ================================================================== --}}
            {{-- CARD 1: Identitas & Klasifikasi (Blue Theme) --}}
            {{-- ================================================================== --}}
            <div class="bg-white rounded-2xl shadow-xl border-2 border-blue-300 p-8 w-full">
                <div class="flex items-center justify-between pb-5 mb-6 border-b-2 border-blue-200">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xl shadow-md">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-950 uppercase tracking-wider">🏆 1. Identitas Siswa & Klasifikasi Prestasi</h3>
                            <p class="text-sm font-extrabold text-blue-800">Tentukan siswa berprestasi dan parameter pencapaian</p>
                        </div>
                    </div>
                    <span class="px-4 py-1.5 rounded-full bg-blue-100 text-blue-900 font-black text-xs uppercase tracking-widest border border-blue-400 shadow-sm">Langkah 1</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Target Siswa --}}
                    <div class="md:col-span-1">
                        <label class="block text-sm font-black text-slate-950 uppercase tracking-wider mb-3 pl-1">Target Siswa <span class="text-rose-600">*</span></label>
                        <select name="student_id" required id="studentSelect" class="w-full bg-white border-2 border-slate-400 rounded-xl px-5 py-4 text-base sm:text-lg font-black text-slate-950 focus:border-blue-600 outline-none transition-all cursor-pointer shadow-md">
                            <option value="">Cari nama siswa...</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" {{ old('student_id', $preselectedStudent?->id) == $student->id ? 'selected' : '' }}>
                                    {{ $student->full_name }} ({{ $student->currentClassroom->first()?->class_name ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tanggal Perolehan --}}
                    <div>
                        <label class="block text-sm font-black text-slate-950 uppercase tracking-wider mb-3 pl-1">Tanggal Perolehan <span class="text-rose-600">*</span></label>
                        <input type="date" name="incident_date" value="{{ old('incident_date', date('Y-m-d')) }}" required 
                            class="w-full bg-white border-2 border-slate-400 rounded-xl px-5 py-4 text-base sm:text-lg font-black text-slate-950 focus:border-blue-600 outline-none transition-all shadow-md">
                    </div>

                    {{-- Guru Pencatat / Pelapor --}}
                    <div>
                        <label class="block text-sm font-black text-slate-950 uppercase tracking-wider mb-3 pl-1">Guru Pencatat / Pelapor</label>
                        <select name="counselor_id" class="w-full bg-white border-2 border-slate-400 rounded-xl px-5 py-4 text-base sm:text-lg font-black text-slate-950 focus:border-blue-600 outline-none transition-all cursor-pointer shadow-md">
                            <option value="{{ auth()->id() }}">Saya Sendiri</option>
                            @foreach($counselors as $c)
                                <option value="{{ $c->id }}" {{ old('counselor_id') == $c->id ? 'selected' : '' }}>{{ $c->display_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Level Pencapaian --}}
                    <div class="pt-4 border-t border-blue-200">
                        <label class="block text-sm font-black text-slate-950 uppercase tracking-wider mb-3 pl-1">⭐ Level Pencapaian</label>
                        <select name="achievement_level" class="w-full bg-white border-2 border-slate-400 rounded-xl px-5 py-4 text-base sm:text-lg font-black text-slate-950 focus:border-blue-600 outline-none transition-all shadow-md cursor-pointer">
                            <option value="sekolah" {{ old('achievement_level') == 'sekolah' ? 'selected' : '' }}>Tingkat Sekolah (+100 Poin)</option>
                            <option value="kabupaten" {{ old('achievement_level') == 'kabupaten' ? 'selected' : '' }}>Tingkat Kabupaten / Kota (+200 Poin)</option>
                            <option value="propinsi" {{ old('achievement_level') == 'propinsi' ? 'selected' : '' }}>Tingkat Provinsi (+300 Poin)</option>
                            <option value="nasional" {{ old('achievement_level') == 'nasional' ? 'selected' : '' }}>Tingkat Nasional (+400 Poin)</option>
                            <option value="internasional" {{ old('achievement_level') == 'internasional' ? 'selected' : '' }}>Tingkat Internasional (+500 Poin)</option>
                        </select>
                    </div>

                    {{-- Kategori Prestasi --}}
                    <div class="md:col-span-2 pt-4 border-t border-blue-200">
                        <label class="block text-sm font-black text-slate-950 uppercase tracking-wider mb-3 pl-1">🎖️ Kategori Prestasi</label>
                        <select name="category" class="w-full bg-white border-2 border-slate-400 rounded-xl px-5 py-4 text-base sm:text-lg font-black text-slate-950 focus:border-blue-600 outline-none transition-all shadow-md cursor-pointer">
                            <option value="akademik" {{ old('category') == 'akademik' ? 'selected' : '' }}>Akademik & Sains</option>
                            <option value="olahraga" {{ old('category') == 'olahraga' ? 'selected' : '' }}>Olahraga & Fisik</option>
                            <option value="seni" {{ old('category') == 'seni' ? 'selected' : '' }}>Seni, Budaya & Desain</option>
                            <option value="keagamaan" {{ old('category') == 'keagamaan' ? 'selected' : '' }}>Religius (Tahfidz/Kajian)</option>
                            <option value="karir" {{ old('category') == 'karir' ? 'selected' : '' }}>Lomba Kejuruan (LKS)</option>
                            <option value="lainnya" {{ old('category') == 'lainnya' ? 'selected' : '' }}>Penghargaan Lainnya</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- ================================================================== --}}
            {{-- CARD 2: Detail Prestasi (Amber/Gold Accents) --}}
            {{-- ================================================================== --}}
            <div class="bg-white rounded-2xl shadow-xl border-2 border-amber-300 p-8 w-full">
                <div class="flex items-center justify-between pb-5 mb-6 border-b-2 border-amber-200">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-yellow-600 text-white flex items-center justify-center text-xl shadow-md">
                            <i class="fas fa-star"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-950 uppercase tracking-wider">🎖️ 2. Detail Prestasi & Apresiasi Sekolah</h3>
                            <p class="text-sm font-extrabold text-amber-800">Uraikan deskripsi prestasi yang diraih serta bentuk apresiasi sekolah</p>
                        </div>
                    </div>
                    <span class="px-4 py-1.5 rounded-full bg-amber-100 text-amber-900 font-black text-xs uppercase tracking-widest border border-amber-400 shadow-sm">Prestasi</span>
                </div>

                <div class="space-y-6">
                    {{-- Judul Prestasi --}}
                    <div>
                        <label class="block text-sm font-black text-slate-950 uppercase tracking-wider mb-3 pl-1">Judul Prestasi / Kejuaraan <span class="text-rose-600">*</span></label>
                        <input type="text" name="title" required value="{{ old('title') }}" 
                            placeholder="Contoh: Juara 1 Olimpiade Matematika Tingkat Provinsi"
                            class="w-full bg-white border-2 border-slate-400 rounded-xl px-5 py-4 text-base sm:text-lg font-black text-slate-950 focus:border-amber-500 outline-none transition-all shadow-md">
                    </div>

                    {{-- Nama Lomba & Penyelenggara --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-black text-slate-950 uppercase tracking-wider mb-3 pl-1">Nama Lomba / Kompetisi</label>
                            <input type="text" name="competition_name" value="{{ old('competition_name') }}" 
                                placeholder="Contoh: Olimpiade Sains Nasional (OSN)"
                                class="w-full bg-white border-2 border-slate-400 rounded-xl px-5 py-4 text-base sm:text-lg font-black text-slate-950 focus:border-amber-500 outline-none transition-all shadow-md">
                        </div>
                        <div>
                            <label class="block text-sm font-black text-slate-950 uppercase tracking-wider mb-3 pl-1">Penyelenggara</label>
                            <input type="text" name="organizer" value="{{ old('organizer') }}" 
                                placeholder="Contoh: Kementerian Pendidikan dan Kebudayaan"
                                class="w-full bg-white border-2 border-slate-400 rounded-xl px-5 py-4 text-base sm:text-lg font-black text-slate-950 focus:border-amber-500 outline-none transition-all shadow-md">
                        </div>
                    </div>

                    {{-- Peringkat --}}
                    <div>
                        <label class="block text-sm font-black text-slate-950 uppercase tracking-wider mb-3 pl-1">🏅 Peringkat</label>
                        <select name="ranking" class="w-full bg-white border-2 border-slate-400 rounded-xl px-5 py-4 text-base sm:text-lg font-black text-slate-950 focus:border-amber-500 outline-none transition-all shadow-md cursor-pointer">
                            <option value="juara_1" {{ old('ranking') == 'juara_1' ? 'selected' : '' }}>🥇 Juara 1 / Gold</option>
                            <option value="juara_2" {{ old('ranking') == 'juara_2' ? 'selected' : '' }}>🥈 Juara 2 / Silver</option>
                            <option value="juara_3" {{ old('ranking') == 'juara_3' ? 'selected' : '' }}>🥉 Juara 3 / Bronze</option>
                            <option value="harapan_1" {{ old('ranking') == 'harapan_1' ? 'selected' : '' }}>Harapan 1</option>
                            <option value="harapan_2" {{ old('ranking') == 'harapan_2' ? 'selected' : '' }}>Harapan 2</option>
                            <option value="harapan_3" {{ old('ranking') == 'harapan_3' ? 'selected' : '' }}>Harapan 3</option>
                            <option value="finalis" {{ old('ranking') == 'finalis' ? 'selected' : '' }}>Finalis / Top 10</option>
                            <option value="peserta" {{ old('ranking') == 'peserta' ? 'selected' : '' }}>Peserta</option>
                            <option value="best_speaker" {{ old('ranking') == 'best_speaker' ? 'selected' : '' }}>Best Speaker / Best Performer</option>
                            <option value="mvp" {{ old('ranking') == 'mvp' ? 'selected' : '' }}>MVP (Most Valuable Player)</option>
                        </select>
                    </div>

                    {{-- Deskripsi / Kronologi Raihan --}}
                    <div>
                        <label class="block text-sm font-black text-slate-950 uppercase tracking-wider mb-3 pl-1">Deskripsi / Kronologi Raihan <span class="text-rose-600">*</span></label>
                        <textarea name="description" required rows="5" 
                            placeholder="Jelaskan bagaimana prestasi ini diraih, tingkat kejuaraan, dan perjuangan siswa..."
                            class="w-full bg-white border-2 border-slate-400 rounded-xl px-5 py-4 text-base sm:text-lg font-black text-slate-950 focus:border-amber-500 outline-none transition-all shadow-md">{{ old('description') }}</textarea>
                    </div>

                    {{-- Bentuk Apresiasi / Reward Sekolah --}}
                    <div>
                        <label class="block text-sm font-black text-slate-950 uppercase tracking-wider mb-3 pl-1">Bentuk Apresiasi / Reward Sekolah</label>
                        <textarea name="action_taken" rows="3" 
                            placeholder="Contoh: Beasiswa SPP 1 bulan, sertifikat penghargaan sekolah..."
                            class="w-full bg-white border-2 border-slate-400 rounded-xl px-5 py-4 text-base sm:text-lg font-black text-slate-950 focus:border-amber-500 outline-none transition-all shadow-md">{{ old('action_taken') }}</textarea>
                    </div>

                    {{-- Info Box --}}
                    <div class="bg-blue-100 border-2 border-blue-500 rounded-xl p-5 flex items-center gap-4 shadow-sm text-blue-950 font-black text-base">
                        <i class="fas fa-medal text-3xl text-amber-500 shrink-0"></i>
                        <div>
                            <span class="block uppercase tracking-wider text-xs text-blue-800">Hadiah & Reputasi Otomatis</span>
                            Prestasi langsung berstatus <strong>Selesai (Resolved)</strong> dan menambahkan <strong>+50 s/d +250 Poin Reputasi Positif</strong> ke profil siswa!
                        </div>
                    </div>

                    {{-- Parent Notified Checkbox --}}
                    <div class="flex items-center">
                        <label class="flex items-center gap-4 cursor-pointer group p-4 bg-amber-50 border-2 border-amber-300 rounded-xl w-full shadow-sm hover:bg-amber-100 transition">
                            <input type="checkbox" name="parent_notified" value="1" class="w-6 h-6 rounded-lg border-2 border-amber-400 text-amber-600 focus:ring-amber-500 transition-all">
                            <span class="text-base font-black text-slate-950 uppercase tracking-wider">Beritahu Orang Tua (Notifikasi Apresiasi)</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- ================================================================== --}}
            {{-- CARD 3: Tembuskan Prestasi & Lampiran (Purple Theme) --}}
            {{-- ================================================================== --}}
            <div class="bg-white rounded-2xl shadow-xl border-2 border-purple-300 p-8 w-full">
                <div class="flex items-center justify-between pb-5 mb-6 border-b-2 border-purple-200">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-purple-600 text-white flex items-center justify-center text-xl shadow-md">
                            <i class="fas fa-share-nodes"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-950 uppercase tracking-wider">⭐ 3. Tembuskan Prestasi (CC) & Lampiran Piagam</h3>
                            <p class="text-sm font-extrabold text-purple-800">Pilih pihak yang berkaitan dengan prestasi ini serta sertakan bukti piagam/sertifikat</p>
                        </div>
                    </div>
                    <span class="px-4 py-1.5 rounded-full bg-purple-100 text-purple-900 font-black text-xs uppercase tracking-widest border border-purple-400 shadow-sm">Apresiasi</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    {{-- Wali Kelas (Otomatis) --}}
                    <div class="bg-slate-50 p-5 rounded-xl border-2 border-slate-300 flex flex-col justify-between shadow-md">
                        <div>
                            <label class="block text-xs font-black text-slate-950 uppercase tracking-wider mb-3 flex items-center justify-between">
                                <span>Wali Kelas Siswa</span>
                                <i class="fas fa-user-check text-blue-700 text-base"></i>
                            </label>
                            <div class="w-full bg-white border-2 border-slate-400 rounded-xl px-4 py-3 min-h-[56px] flex items-center justify-between shadow-sm">
                                <span class="text-base sm:text-lg font-black text-slate-950 truncate" id="walikelas-name-display">-- Pilih Siswa Di Atas --</span>
                                <span class="px-2.5 py-1 rounded-md bg-emerald-600 text-white text-[11px] font-black uppercase tracking-wider shrink-0 ml-2 shadow"><i class="fas fa-check mr-1"></i> Auto</span>
                            </div>
                            <input type="hidden" name="participants[0][user_id]" id="walikelas-user-id" value="">
                            <input type="hidden" name="participants[0][role]" value="wali_kelas">
                        </div>
                        <p class="text-xs text-emerald-800 mt-3 font-extrabold"><i class="fas fa-magic mr-1"></i> Terdeteksi otomatis tanpa pilih manual.</p>
                    </div>

                    {{-- Tim Kesiswaan / PKS (Otomatis) --}}
                    <div class="bg-slate-50 p-5 rounded-xl border-2 border-slate-300 flex flex-col justify-between shadow-md">
                        <div>
                            <label class="block text-xs font-black text-slate-950 uppercase tracking-wider mb-3 flex items-center justify-between">
                                <span>Tim Kesiswaan / PKS</span>
                                <i class="fas fa-shield-halved text-purple-700 text-base"></i>
                            </label>
                            <div class="w-full bg-white border-2 border-slate-400 rounded-xl px-4 py-3 min-h-[56px] flex items-center justify-between shadow-sm">
                                <span class="text-base sm:text-lg font-black text-slate-950 truncate" id="pks-name-display">Tim PKS / BK Sekolah</span>
                                <span class="px-2.5 py-1 rounded-md bg-purple-600 text-white text-[11px] font-black uppercase tracking-wider shrink-0 ml-2 shadow"><i class="fas fa-check mr-1"></i> Auto</span>
                            </div>
                            <input type="hidden" name="participants[1][user_id]" id="pks-user-id" value="">
                            <input type="hidden" name="participants[1][role]" value="pks">
                        </div>
                        <p class="text-xs text-purple-800 mt-3 font-extrabold"><i class="fas fa-check-circle mr-1"></i> Terhubung otomatis ke Tim BK sekolah.</p>
                    </div>

                    {{-- Guru Pembimbing / Pelatih (Manual Select) --}}
                    <div class="bg-slate-50 p-5 rounded-xl border-2 border-slate-300 flex flex-col justify-between shadow-md">
                        <div>
                            <label class="block text-xs font-black text-slate-950 uppercase tracking-wider mb-3 flex items-center justify-between">
                                <span>Guru Pembimbing / Pelatih</span>
                                <i class="fas fa-trophy text-amber-600 text-base"></i>
                            </label>
                            <select name="participants[2][user_id]" id="select-gurupiket" class="w-full bg-white border-2 border-slate-400 rounded-xl px-4 py-3 text-base sm:text-lg font-black text-slate-950 focus:border-purple-600 outline-none transition-all shadow-sm">
                                <option value="">-- Cari Guru Pembimbing / Pelatih --</option>
                                @foreach($counselors as $c)
                                    <option value="{{ $c->id }}">{{ $c->display_name }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="participants[2][role]" value="guru_mapel">
                            <input type="hidden" name="participants[2][notes]" value="Guru Pembimbing / Pelatih Lomba">
                        </div>
                        <p class="text-xs text-amber-800 mt-3 font-extrabold"><i class="fas fa-medal mr-1"></i> Cari guru yang membimbing atau melatih lomba ini.</p>
                    </div>
                </div>

                {{-- Attachment --}}
                <div class="bg-slate-50 rounded-2xl border-2 border-slate-300 p-6 flex items-center gap-6 shadow-sm">
                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-amber-500 text-2xl border-2 border-amber-300 shadow-md shrink-0">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <div class="flex-1">
                        <label class="block text-base font-black text-slate-950 uppercase tracking-wider mb-2">Lampiran Piagam / Sertifikat / Foto Bukti (Opsional)</label>
                        <input type="file" name="attachment" class="w-full text-base font-black text-slate-950 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-2 file:border-slate-300 file:text-sm file:font-black file:bg-white file:text-slate-950 hover:file:bg-slate-100 cursor-pointer">
                        <p class="text-sm font-extrabold text-blue-900 mt-2">Format yang didukung: PDF, JPG, PNG, DOC (Maksimal 10MB)</p>
                    </div>
                </div>
            </div>

            {{-- ================================================================== --}}
            {{-- Submit Card --}}
            {{-- ================================================================== --}}
            <div class="bg-white rounded-2xl shadow-xl border-2 border-slate-300 p-8 w-full flex flex-col md:flex-row gap-6 items-center justify-between">
                <div>
                    <h4 class="text-lg font-black text-slate-950">🏆 Sudah Memeriksa Seluruh Data Prestasi?</h4>
                    <p class="text-sm font-extrabold text-slate-700">Pastikan detail prestasi, peringkat, dan target siswa telah terisi dengan benar sebelum disimpan.</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto shrink-0">
                    <a href="{{ route('admin.counseling.index') }}" class="py-4 px-8 text-center text-base font-black text-slate-950 uppercase tracking-wider hover:bg-slate-100 transition bg-slate-50 border-2 border-slate-400 rounded-xl shadow-md">Batal & Kembali</a>
                    <button type="submit" class="py-4 px-10 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black text-base sm:text-lg shadow-xl hover:-translate-y-0.5 transition duration-300 uppercase tracking-wider">
                        <i class="fas fa-check-circle mr-2"></i> Simpan Prestasi Sekarang
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 56px !important;
        background-color: #ffffff !important;
        border: 2px solid #cbd5e1 !important;
        border-radius: 0.75rem !important;
        padding: 12px 16px !important;
        display: flex !important;
        align-items: center !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #020617 !important;
        font-weight: 900 !important;
        font-size: 16px !important;
        line-height: normal !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #334155 !important;
        font-weight: 800 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 54px !important;
        right: 12px !important;
    }
    .select2-dropdown {
        background-color: #ffffff !important;
        border: 2px solid #0f172a !important;
        border-radius: 1rem !important;
        color: #020617 !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3) !important;
    }
    .select2-search__field {
        background-color: #f8fafc !important;
        color: #020617 !important;
        font-weight: 800 !important;
        border: 2px solid #334155 !important;
        border-radius: 0.5rem !important;
        padding: 8px 12px !important;
    }
    .select2-results__option {
        color: #020617 !important;
        font-weight: 800 !important;
        padding: 10px 16px !important;
    }
    .select2-results__option--highlighted {
        background-color: #2563eb !important;
        color: #ffffff !important;
    }
    /* Guarantee 100% black text and pure white background on all form controls */
    input, select, textarea, option {
        background-color: #ffffff !important;
        color: #000000 !important;
        font-weight: 800 !important;
    }
    input::placeholder, textarea::placeholder {
        color: #334155 !important;
        opacity: 1 !important;
    }
    input[type="date"] {
        color: #000000 !important;
        background-color: #ffffff !important;
    }
    input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(0) !important;
        opacity: 1 !important;
        cursor: pointer !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 on student select with AJAX
        $('#studentSelect').select2({
            placeholder: 'Ketik nama atau NISN siswa...',
            minimumInputLength: 1,
            ajax: {
                url: '{{ route('admin.counseling.search-students') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        school_id: '{{ request('school_id') }}'
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            }
        });

        // Initialize Select2 on guru pembimbing select
        $('#select-gurupiket').select2({ placeholder: '-- Cari Guru Pembimbing / Pelatih --', allowClear: true });

        // Student select handler — auto-detect wali kelas & BK via AJAX data
        $('#studentSelect').on('select2:select', function(e) {
            const data = e.params.data;
            if (data) {
                // Set Wali Kelas
                if (data.homeroom_id) {
                    $('#walikelas-name-display').text(data.homeroom_name).removeClass('text-slate-900 text-rose-600 text-amber-500').addClass('text-slate-950');
                    $('#walikelas-user-id').val(data.homeroom_id);
                } else {
                    $('#walikelas-name-display').text('Belum Ditentukan').removeClass('text-slate-950 text-slate-900').addClass('text-rose-600');
                    $('#walikelas-user-id').val('');
                }
                
                // Set BK
                if (data.bk_id) {
                    $('#pks-name-display').text(data.bk_name).removeClass('text-slate-900').addClass('text-slate-950');
                    $('#pks-user-id').val(data.bk_id);
                }
            }
        });

        $('#studentSelect').on('select2:clear', function(e) {
            $('#walikelas-name-display').text('-- Pilih Siswa Di Atas --').removeClass('text-slate-950 text-rose-600 text-amber-500').addClass('text-slate-900');
            $('#walikelas-user-id').val('');
            $('#pks-name-display').text('Tim PKS / BK Sekolah').removeClass('text-slate-900').addClass('text-slate-950');
            $('#pks-user-id').val('');
        });

        // Trigger change if student is preselected (e.g. from old() or preselectedStudent)
        if ($('#studentSelect').val()) {
            $('#studentSelect').trigger('change');
        }
    });
</script>
@endpush
