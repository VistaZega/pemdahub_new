@extends('layouts.admin')
@section('title', 'Tambah Catatan Perkembangan - Pembda Elite')

@section('content')
<div class="space-y-8 w-full max-w-full px-2 sm:px-6 pb-12">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.counseling.index') }}" class="w-12 h-12 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition shadow-sm">
                <i class="fas fa-arrow-left text-base"></i>
            </a>
            <div>
                <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight" id="page-title">Tambah Catatan</h1>
                <p class="text-base font-bold text-slate-600 mt-1" id="page-subtitle">Pusat dokumentasi perkembangan dan karakter siswa</p>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.counseling.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        {{-- Mode Switcher: Premium Segmented Control --}}
        <div class="bg-white p-2.5 rounded-2xl shadow-md border-2 border-slate-200 flex mb-8">
            <label class="flex-1 cursor-pointer">
                <input type="radio" name="report_mode" value="masalah" class="sr-only peer" checked onchange="toggleMode()">
                <div class="py-4 text-center rounded-xl font-bold text-sm sm:text-base uppercase tracking-wider transition-all peer-checked:bg-rose-600 peer-checked:text-white text-slate-600 hover:bg-slate-100 shadow-sm">
                    <i class="fas fa-shield-halved mr-2"></i> Pembinaan & Kasus
                </div>
            </label>
            <label class="flex-1 cursor-pointer">
                <input type="radio" name="report_mode" value="prestasi" class="sr-only peer" onchange="toggleMode()">
                <div class="py-4 text-center rounded-xl font-bold text-sm sm:text-base uppercase tracking-wider transition-all peer-checked:bg-blue-600 peer-checked:text-white text-slate-600 hover:bg-slate-100 shadow-sm">
                    <i class="fas fa-trophy mr-2"></i> Prestasi & Penghargaan
                </div>
            </label>
        </div>

        <div class="w-full space-y-8">
            {{-- CARD 1: Identitas & Klasifikasi --}}
            <div class="bg-white rounded-2xl shadow-xl border-2 border-slate-300 p-8 w-full">
                <div class="flex items-center justify-between pb-5 mb-6 border-b-2 border-slate-200">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xl shadow-md">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-950 uppercase tracking-wider">1. Identitas Siswa & Klasifikasi Catatan</h3>
                            <p class="text-sm font-extrabold text-blue-800">Tentukan target siswa dan parameter dasar catatan</p>
                        </div>
                    </div>
                    <span class="px-4 py-1.5 rounded-full bg-blue-100 text-blue-900 font-black text-xs uppercase tracking-widest border border-blue-400 shadow-sm">Langkah 1</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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

                    <div>
                        <label id="label-date" class="block text-sm font-black text-slate-950 uppercase tracking-wider mb-3 pl-1">Tanggal Kejadian <span class="text-rose-600">*</span></label>
                        <input type="date" name="incident_date" value="{{ old('incident_date', date('Y-m-d')) }}" required 
                            class="w-full bg-white border-2 border-slate-400 rounded-xl px-5 py-4 text-base sm:text-lg font-black text-slate-950 focus:border-blue-600 outline-none transition-all shadow-md">
                    </div>

                    <div>
                        <label id="label-counselor" class="block text-sm font-black text-slate-950 uppercase tracking-wider mb-3 pl-1">Petugas Pencatat</label>
                        <select name="counselor_id" class="w-full bg-white border-2 border-slate-400 rounded-xl px-5 py-4 text-base sm:text-lg font-black text-slate-950 focus:border-blue-600 outline-none transition-all cursor-pointer shadow-md">
                            <option value="{{ auth()->id() }}">Saya Sendiri</option>
                            @foreach($counselors as $c)
                                <option value="{{ $c->id }}" {{ old('counselor_id') == $c->id ? 'selected' : '' }}>{{ $c->display_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="field-classification-masalah" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-200">
                        <div>
                            <label class="block text-sm font-black text-slate-950 uppercase tracking-wider mb-3 pl-1">Jenis Catatan</label>
                            <select name="record_type" id="input-record-type" class="w-full bg-white border-2 border-slate-400 rounded-xl px-5 py-4 text-base sm:text-lg font-black text-slate-950 focus:border-blue-600 outline-none transition-all shadow-md cursor-pointer">
                                <option value="konseling">Bimbingan Konseling</option>
                                <option value="pembinaan">Pembinaan Karakter</option>
                                <option value="pelanggaran" selected>Pelanggaran Aturan</option>
                                <option value="home_visit">Kunjungan Rumah (Home Visit)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-black text-slate-950 uppercase tracking-wider mb-3 pl-1">Tingkat Keseriusan</label>
                            <div class="grid grid-cols-3 gap-3">
                                <label class="cursor-pointer group">
                                    <input type="radio" name="severity" value="ringan" class="sr-only peer" checked>
                                    <div class="py-4 text-base font-black text-center border-2 border-emerald-500 bg-emerald-50 text-emerald-950 rounded-xl peer-checked:border-emerald-700 peer-checked:bg-emerald-600 peer-checked:text-white transition-all uppercase shadow-md hover:bg-emerald-100">
                                        Ringan
                                    </div>
                                </label>
                                <label class="cursor-pointer group">
                                    <input type="radio" name="severity" value="sedang" class="sr-only peer">
                                    <div class="py-4 text-base font-black text-center border-2 border-amber-500 bg-amber-50 text-amber-950 rounded-xl peer-checked:border-amber-600 peer-checked:bg-amber-400 peer-checked:text-slate-950 transition-all uppercase shadow-md hover:bg-amber-100">
                                        Sedang
                                    </div>
                                </label>
                                <label class="cursor-pointer group">
                                    <input type="radio" name="severity" value="berat" class="sr-only peer">
                                    <div class="py-4 text-base font-black text-center border-2 border-rose-500 bg-rose-50 text-rose-950 rounded-xl peer-checked:border-rose-700 peer-checked:bg-rose-600 peer-checked:text-white transition-all uppercase shadow-md hover:bg-rose-100">
                                        Berat
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="field-classification-prestasi" style="display:none;" class="md:col-span-2 pt-4 border-t border-slate-200">
                        <input type="hidden" name="dummy_record_type" value="penghargaan">
                        <div>
                            <label class="block text-sm font-black text-slate-950 uppercase tracking-wider mb-3 pl-1">Level Pencapaian</label>
                            <select name="achievement_level" class="w-full bg-white border-2 border-slate-400 rounded-xl px-5 py-4 text-base sm:text-lg font-black text-slate-950 focus:border-blue-600 outline-none transition-all shadow-md cursor-pointer">
                                <option value="sekolah">Tingkat Sekolah (+100 Poin)</option>
                                <option value="kabupaten">Tingkat Kabupaten / Kota (+200 Poin)</option>
                                <option value="propinsi">Tingkat Provinsi (+300 Poin)</option>
                                <option value="nasional">Tingkat Nasional (+400 Poin)</option>
                                <option value="internasional">Tingkat Internasional (+500 Poin)</option>
                            </select>
                        </div>
                    </div>

                    <div class="md:col-span-1 pt-4 border-t border-slate-200">
                        <label class="block text-sm font-black text-slate-950 uppercase tracking-wider mb-3 pl-1">Kategori <span id="category-label-suffix">Masalah</span></label>
                        <select name="category" id="input-category" class="w-full bg-white border-2 border-slate-400 rounded-xl px-5 py-4 text-base sm:text-lg font-black text-slate-950 focus:border-blue-600 outline-none transition-all shadow-md cursor-pointer">
                            <!-- Populated by JS -->
                        </select>
                    </div>
                </div>
            </div>

            {{-- CARD 2: Dokumentasi Detail & Tindakan --}}
            <div class="bg-white rounded-2xl shadow-xl border-2 border-slate-300 p-8 w-full">
                <div class="flex items-center justify-between pb-5 mb-6 border-b-2 border-slate-200">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-rose-600 text-white flex items-center justify-center text-xl shadow-md">
                            <i class="fas fa-file-pen"></i>
                        </div>
                        <div>
                            <h3 id="card2-title" class="text-lg font-black text-slate-950 uppercase tracking-wider">2. Dokumentasi Detail & Tindakan Lanjut</h3>
                            <p id="card2-subtitle" class="text-sm font-extrabold text-rose-800">Uraikan kronologi kejadian dan tindakan yang telah diambil</p>
                        </div>
                    </div>
                    <span id="badge-mode" class="px-4 py-1.5 rounded-full bg-rose-100 text-rose-900 font-black text-xs uppercase tracking-widest border border-rose-400 shadow-sm">Pembinaan</span>
                </div>

                <div class="space-y-6">
                    <div>
                        <label id="label-title" class="block text-sm font-black text-slate-950 uppercase tracking-wider mb-3 pl-1">Judul Kejadian <span class="text-rose-600">*</span></label>
                        <input type="text" name="title" id="input-title" required value="{{ old('title') }}" 
                            placeholder="Contoh: Terlambat masuk sekolah atau Juara 1 Olimpiade Matematika"
                            class="w-full bg-white border-2 border-slate-400 rounded-xl px-5 py-4 text-base sm:text-lg font-black text-slate-950 focus:border-blue-600 outline-none transition-all shadow-md">
                    </div>

                    <div>
                        <label id="label-description" class="block text-sm font-black text-slate-950 uppercase tracking-wider mb-3 pl-1">Kronologi / Detail Lengkap <span class="text-rose-600">*</span></label>
                        <textarea name="description" id="input-description" required rows="5" 
                            placeholder="Jelaskan kronologi, latar belakang, dan fakta peristiwa secara rinci..."
                            class="w-full bg-white border-2 border-slate-400 rounded-xl px-5 py-4 text-base sm:text-lg font-black text-slate-950 focus:border-blue-600 outline-none transition-all shadow-md">{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-200">
                        <div id="field-action-wrapper">
                            <label id="label-action" class="block text-sm font-black text-slate-950 uppercase tracking-wider mb-3 pl-1">Tindakan Lanjut</label>
                            <textarea name="action_taken" id="input-action" rows="3" 
                                placeholder="Apa langkah penanganan atau pembinaan yang telah diberikan?"
                                class="w-full bg-white border-2 border-slate-400 rounded-xl px-5 py-4 text-base sm:text-lg font-black text-slate-950 focus:border-blue-600 outline-none transition-all shadow-md">{{ old('action_taken') }}</textarea>
                        </div>
                        <div id="field-sanction">
                            <label class="block text-sm font-black text-slate-950 uppercase tracking-wider mb-3 pl-1">Sanksi / Tindakan Sementara</label>
                            <textarea name="sanction" rows="3" 
                                placeholder="Contoh: Skorsing 3 hari, pemanggilan orang tua, atau teguran lisan..."
                                class="w-full bg-white border-2 border-slate-400 rounded-xl px-5 py-4 text-base sm:text-lg font-black text-slate-950 focus:border-blue-600 outline-none transition-all shadow-md">{{ old('sanction') }}</textarea>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6 border-t-2 border-slate-200">
                        <div id="field-status-wrapper">
                            <label class="block text-sm font-black text-slate-950 uppercase tracking-wider mb-3 pl-1">Status Setelah Input <span class="text-rose-600">*</span></label>
                            <select name="status" id="input-status" class="w-full bg-white border-2 border-slate-400 rounded-xl px-5 py-4 text-base sm:text-lg font-black text-slate-950 focus:border-blue-600 outline-none transition-all cursor-pointer shadow-md">
                                <option value="open">Open (Baru / Terbuka)</option>
                                <option value="in_progress">Tindak Lanjut</option>
                                <option value="resolved">Resolved (Selesai)</option>
                            </select>
                        </div>
                        <div id="field-prestasi-info" style="display:none;" class="md:col-span-2 bg-blue-100 border-2 border-blue-500 rounded-xl p-5 flex items-center gap-4 shadow-sm text-blue-950 font-black text-base">
                            <i class="fas fa-medal text-3xl text-amber-500 shrink-0"></i>
                            <div>
                                <span class="block uppercase tracking-wider text-xs text-blue-800">Hadiah & Reputasi Otomatis</span>
                                Prestasi akan langsung berstatus <strong>Selesai (Resolved)</strong> dan menambahkan <strong>+50 s/d +250 Poin Reputasi Positif</strong> ke profil siswa!
                            </div>
                        </div>
                        <div id="field-confidential-wrapper" class="flex items-center pt-6">
                            <label class="flex items-center gap-4 cursor-pointer group p-4 bg-slate-50 border-2 border-slate-300 rounded-xl w-full shadow-sm hover:bg-slate-100 transition">
                                <input type="checkbox" name="is_confidential" value="1" class="w-6 h-6 rounded-lg border-2 border-slate-400 text-rose-600 focus:ring-rose-500 transition-all">
                                <span class="text-base font-black text-slate-950 uppercase tracking-wider">Catatan Rahasia (Khusus)</span>
                            </label>
                        </div>
                        <div class="flex items-center pt-6">
                            <label class="flex items-center gap-4 cursor-pointer group p-4 bg-slate-50 border-2 border-slate-300 rounded-xl w-full shadow-sm hover:bg-slate-100 transition">
                                <input type="checkbox" name="parent_notified" value="1" class="w-6 h-6 rounded-lg border-2 border-slate-400 text-emerald-600 focus:ring-emerald-500 transition-all">
                                <span id="label-parent-notified" class="text-base font-black text-slate-950 uppercase tracking-wider">Orang Tua Sudah Tahu</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CARD 3: Tembuskan Catatan & Lampiran --}}
            <div class="bg-white rounded-2xl shadow-xl border-2 border-slate-300 p-8 w-full">
                <div class="flex items-center justify-between pb-5 mb-6 border-b-2 border-slate-200">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-purple-600 text-white flex items-center justify-center text-xl shadow-md">
                            <i class="fas fa-share-nodes"></i>
                        </div>
                        <div>
                            <h3 id="card3-title" class="text-lg font-black text-slate-950 uppercase tracking-wider">3. Tembuskan Catatan (CC Partisipan) & Lampiran</h3>
                            <p id="card3-subtitle" class="text-sm font-extrabold text-purple-800">Pilih pihak terkait agar ikut memantau atau bertindak serta sertakan bukti pendukung</p>
                        </div>
                    </div>
                    <span id="badge-mode-card3" class="px-4 py-1.5 rounded-full bg-purple-100 text-purple-900 font-black text-xs uppercase tracking-widest border border-purple-400 shadow-sm">Kolaborasi</span>
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

                    {{-- Tim PKS / BK (Otomatis) --}}
                    <div class="bg-slate-50 p-5 rounded-xl border-2 border-slate-300 flex flex-col justify-between shadow-md">
                        <div>
                            <label class="block text-xs font-black text-slate-950 uppercase tracking-wider mb-3 flex items-center justify-between">
                                <span id="label-pks-box">Tim PKS / Guru BK</span>
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

                    {{-- Guru Piket / Pembimbing / Lainnya --}}
                    <div class="bg-slate-50 p-5 rounded-xl border-2 border-slate-300 flex flex-col justify-between shadow-md">
                        <div>
                            <label class="block text-xs font-black text-slate-950 uppercase tracking-wider mb-3 flex items-center justify-between">
                                <span id="label-box3-title">Guru Piket / Lainnya</span>
                                <i id="icon-box3" class="fas fa-calendar-check text-pink-700 text-base"></i>
                            </label>
                            <select name="participants[2][user_id]" id="select-gurupiket" class="w-full bg-white border-2 border-slate-400 rounded-xl px-4 py-3 text-base sm:text-lg font-black text-slate-950 focus:border-purple-600 outline-none transition-all shadow-sm">
                                <option value="">-- Cari Guru Piket --</option>
                                @foreach($counselors as $c)
                                    <option value="{{ $c->id }}">{{ $c->display_name }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="participants[2][role]" id="input-box3-role" value="lainnya">
                            <input type="hidden" name="participants[2][notes]" id="input-box3-notes" value="Guru Piket / Petugas Harian">
                        </div>
                        <p id="note-box3" class="text-xs text-pink-800 mt-3 font-extrabold"><i class="fas fa-search mr-1"></i> Cari siapa guru piket yang bertugas hari ini.</p>
                    </div>
                </div>

                {{-- Attachment --}}
                <div class="bg-slate-50 rounded-2xl border-2 border-slate-300 p-6 flex items-center gap-6 shadow-sm">
                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-slate-950 text-2xl border-2 border-slate-300 shadow-md shrink-0">
                        <i class="fas fa-paperclip"></i>
                    </div>
                    <div class="flex-1">
                        <label id="label-attachment" class="block text-base font-black text-slate-950 uppercase tracking-wider mb-2">Lampiran Dokumen Bukti (Opsional)</label>
                        <input type="file" name="attachment" class="w-full text-base font-black text-slate-950 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-2 file:border-slate-300 file:text-sm file:font-black file:bg-white file:text-slate-950 hover:file:bg-slate-100 cursor-pointer">
                        <p class="text-sm font-extrabold text-blue-900 mt-2">Format yang didukung: PDF, JPG, PNG, DOC (Maksimal 10MB)</p>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="bg-white rounded-2xl shadow-xl border-2 border-slate-300 p-8 w-full flex flex-col md:flex-row gap-6 items-center justify-between">
                <div>
                    <h4 class="text-lg font-black text-slate-950">Sudah Memeriksa Seluruh Data?</h4>
                    <p class="text-sm font-extrabold text-slate-700">Pastikan tanggal kejadian, target siswa, dan keterangan telah terisi dengan benar sebelum disimpan.</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto shrink-0">
                    <a href="{{ route('admin.counseling.index') }}" class="py-4 px-8 text-center text-base font-black text-slate-950 uppercase tracking-wider hover:bg-slate-100 transition bg-slate-50 border-2 border-slate-400 rounded-xl shadow-md">Batal & Kembali</a>
                    <button type="submit" id="btn-submit" class="py-4 px-10 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-black text-base sm:text-lg shadow-xl hover:-translate-y-0.5 transition duration-300 uppercase tracking-wider">
                        <i class="fas fa-check-circle mr-2"></i> Simpan Catatan Sekarang
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

        $('#studentSelect').on('select2:select', function(e) {
            const data = e.params.data;
            if (data) {
                if (data.homeroom_id) {
                    $('#walikelas-name-display').text(data.homeroom_name).removeClass('text-slate-900 text-rose-600 text-amber-500').addClass('text-slate-950');
                    $('#walikelas-user-id').val(data.homeroom_id);
                } else {
                    $('#walikelas-name-display').text('Belum Ditentukan').removeClass('text-slate-950 text-slate-900').addClass('text-rose-600');
                    $('#walikelas-user-id').val('');
                }
                
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

        if ($('#studentSelect').val()) {
            $('#studentSelect').trigger('change');
        }

        toggleMode();
    });

    const catsMasalah = [
        {val: 'perilaku', text: 'Karakter & Perilaku'},
        {val: 'kedisiplinan', text: 'Kedisiplinan / Tata Tertib'},
        {val: 'absensi', text: 'Kehadiran / Absensi'},
        {val: 'akademik', text: 'Performa Akademik'},
        {val: 'sosial', text: 'Relasi Sosial'},
        {val: 'pribadi', text: 'Kesehatan Mental/Pribadi'},
        {val: 'lainnya', text: 'Kategori Lainnya'}
    ];
    
    const catsPrestasi = [
        {val: 'akademik', text: 'Akademik & Sains'},
        {val: 'olahraga', text: 'Olahraga & Fisik'},
        {val: 'seni', text: 'Seni, Budaya & Desain'},
        {val: 'keagamaan', text: 'Religius (Tahfidz/Kajian)'},
        {val: 'karir', text: 'Lomba Kejuruan (LKS)'},
        {val: 'lainnya', text: 'Penghargaan Lainnya'}
    ];

    function toggleMode() {
        const mode = document.querySelector('input[name="report_mode"]:checked').value;
        const pageTitle = document.getElementById('page-title');
        const pageSubtitle = document.getElementById('page-subtitle');
        const badgeMode = document.getElementById('badge-mode');
        const labelTitle = document.getElementById('label-title');
        const labelDesc = document.getElementById('label-description');
        const labelAction = document.getElementById('label-action');
        const labelDate = document.getElementById('label-date');
        const inputTitle = document.getElementById('input-title');
        const inputDesc = document.getElementById('input-description');
        const fieldMasalah = document.getElementById('field-classification-masalah');
        const fieldPrestasi = document.getElementById('field-classification-prestasi');
        const inputCategory = document.getElementById('input-category');
        const inputRecordType = document.getElementById('input-record-type');
        const btnSubmit = document.getElementById('btn-submit');
        const catSuffix = document.getElementById('category-label-suffix');

        // New dynamic references
        const labelCounselor = document.getElementById('label-counselor');
        const card2Title = document.getElementById('card2-title');
        const card2Subtitle = document.getElementById('card2-subtitle');
        const fieldSanction = document.getElementById('field-sanction');
        const fieldActionWrapper = document.getElementById('field-action-wrapper');
        const fieldStatusWrapper = document.getElementById('field-status-wrapper');
        const fieldPrestasiInfo = document.getElementById('field-prestasi-info');
        const fieldConfidentialWrapper = document.getElementById('field-confidential-wrapper');
        const labelParentNotified = document.getElementById('label-parent-notified');
        const inputAction = document.getElementById('input-action');
        
        const card3Title = document.getElementById('card3-title');
        const card3Subtitle = document.getElementById('card3-subtitle');
        const badgeModeCard3 = document.getElementById('badge-mode-card3');
        const labelPksBox = document.getElementById('label-pks-box');
        const labelBox3Title = document.getElementById('label-box3-title');
        const iconBox3 = document.getElementById('icon-box3');
        const selectGurupiket = $('#select-gurupiket');
        const inputBox3Role = document.getElementById('input-box3-role');
        const inputBox3Notes = document.getElementById('input-box3-notes');
        const noteBox3 = document.getElementById('note-box3');
        const labelAttachment = document.getElementById('label-attachment');

        inputCategory.innerHTML = '';
        if (catSuffix) catSuffix.innerText = mode === 'prestasi' ? 'Prestasi' : 'Masalah';

        if (mode === 'prestasi') {
            pageTitle.innerText = 'Input Prestasi Siswa';
            pageSubtitle.innerText = 'Apresiasi pencapaian dan kebanggaan sekolah';
            if (badgeMode) {
                badgeMode.innerText = 'Prestasi';
                badgeMode.className = 'text-xs font-black px-3 py-1 rounded-full bg-blue-100 text-blue-900 uppercase tracking-widest border border-blue-400';
            }
            
            if (labelCounselor) labelCounselor.innerText = 'Guru Pencatat / Pelapor';
            if (card2Title) card2Title.innerText = '2. Detail Prestasi & Apresiasi Sekolah';
            if (card2Subtitle) card2Subtitle.innerText = 'Uraikan deskripsi prestasi yang diraih serta bentuk apresiasi / penghargaan yang diberikan sekolah';
            
            labelTitle.innerText = 'Judul Prestasi / Kejuaraan *';
            labelDesc.innerText = 'Deskripsi / Kronologi Raihan *';
            labelAction.innerText = 'Bentuk Apresiasi / Reward Sekolah (Opsional)';
            labelDate.innerText = 'Tanggal Perolehan *';
            inputTitle.placeholder = 'Contoh: Juara 1 Olimpiade Matematika Tingkat Provinsi';
            inputDesc.placeholder = 'Jelaskan bagaimana prestasi ini diraih, tingkat kejuaraan, dan perjuangan siswa...';
            if (inputAction) inputAction.placeholder = 'Contoh: Beasiswa SPP 1 bulan, sertifikat penghargaan sekolah, atau uang pembinaan...';
            
            fieldMasalah.style.display = 'none';
            fieldPrestasi.style.display = 'grid';
            
            if (fieldSanction) fieldSanction.style.display = 'none';
            if (fieldActionWrapper) fieldActionWrapper.className = 'md:col-span-2';
            if (fieldStatusWrapper) fieldStatusWrapper.style.display = 'none';
            if (fieldPrestasiInfo) fieldPrestasiInfo.style.display = 'flex';
            if (fieldConfidentialWrapper) fieldConfidentialWrapper.style.display = 'none';
            if (labelParentNotified) labelParentNotified.innerText = 'Beritahu Orang Tua (Notifikasi Apresiasi)';
            
            if (card3Title) card3Title.innerText = '3. Tembuskan Prestasi (CC) & Lampiran Piagam / Bukti';
            if (card3Subtitle) card3Subtitle.innerText = 'Pilih pihak yang berkaitan dengan prestasi ini serta sertakan bukti piagam/sertifikat';
            if (badgeModeCard3) {
                badgeModeCard3.innerText = 'Apresiasi';
                badgeModeCard3.className = 'px-4 py-1.5 rounded-full bg-blue-100 text-blue-900 font-black text-xs uppercase tracking-widest border border-blue-400 shadow-sm';
            }
            if (labelPksBox) labelPksBox.innerText = 'Tim Kesiswaan / PKS';
            if (labelBox3Title) labelBox3Title.innerText = 'Guru Pembimbing / Pelatih';
            if (iconBox3) iconBox3.className = 'fas fa-trophy text-amber-600 text-base';
            if (selectGurupiket.length) {
                if (selectGurupiket.hasClass('select2-hidden-accessible')) {
                    selectGurupiket.select2('destroy');
                }
                selectGurupiket.find('option:first-child').text('-- Cari Guru Pembimbing / Pelatih --');
                selectGurupiket.select2({ placeholder: '-- Cari Guru Pembimbing / Pelatih --', allowClear: true });
            }
            if (inputBox3Role) inputBox3Role.value = 'guru_mapel';
            if (inputBox3Notes) inputBox3Notes.value = 'Guru Pembimbing / Pelatih Lomba';
            if (noteBox3) noteBox3.innerHTML = '<i class="fas fa-medal mr-1"></i> Cari guru yang membimbing atau melatih lomba ini.';
            if (labelAttachment) labelAttachment.innerText = 'Lampiran Piagam / Sertifikat / Foto Bukti (Opsional)';
            
            btnSubmit.className = btnSubmit.className.replace(/rose/g, 'blue');
            
            // Set internal record type for controller
            const opt = document.createElement('option');
            opt.value = 'penghargaan'; opt.text = 'Penghargaan'; opt.selected = true;
            inputRecordType.add(opt);
            inputRecordType.value = 'penghargaan';

            catsPrestasi.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.val; opt.text = c.text;
                inputCategory.add(opt);
            });
        } else {
            pageTitle.innerText = 'Input Catatan Pembinaan';
            pageSubtitle.innerText = 'Dokumentasi konseling dan pembinaan karakter';
            if (badgeMode) {
                badgeMode.innerText = 'Pembinaan';
                badgeMode.className = 'text-xs font-black px-3 py-1 rounded-full bg-rose-100 text-rose-900 uppercase tracking-widest border border-rose-400';
            }
            
            if (labelCounselor) labelCounselor.innerText = 'Petugas Pencatat';
            if (card2Title) card2Title.innerText = '2. Dokumentasi Detail & Tindakan Lanjut';
            if (card2Subtitle) card2Subtitle.innerText = 'Uraikan kronologi kejadian dan tindakan yang telah diambil';
            
            labelTitle.innerText = 'Judul Kejadian *';
            labelDesc.innerText = 'Kronologi / Detail Lengkap *';
            labelAction.innerText = 'Tindakan Lanjut';
            labelDate.innerText = 'Tanggal Kejadian *';
            inputTitle.placeholder = 'Contoh: Terlambat masuk sekolah atau merokok di lingkungan sekolah';
            inputDesc.placeholder = 'Jelaskan kronologi, latar belakang, dan fakta peristiwa secara rinci...';
            if (inputAction) inputAction.placeholder = 'Apa langkah penanganan atau pembinaan yang telah diberikan?';
            
            fieldMasalah.style.display = 'grid';
            fieldPrestasi.style.display = 'none';
            
            if (fieldSanction) fieldSanction.style.display = 'block';
            if (fieldActionWrapper) fieldActionWrapper.className = '';
            if (fieldStatusWrapper) fieldStatusWrapper.style.display = 'block';
            if (fieldPrestasiInfo) fieldPrestasiInfo.style.display = 'none';
            if (fieldConfidentialWrapper) fieldConfidentialWrapper.style.display = 'flex';
            if (labelParentNotified) labelParentNotified.innerText = 'Orang Tua Sudah Tahu';
            
            if (card3Title) card3Title.innerText = '3. Tembuskan Catatan (CC Partisipan) & Lampiran';
            if (card3Subtitle) card3Subtitle.innerText = 'Pilih pihak terkait agar ikut memantau atau bertindak serta sertakan bukti pendukung';
            if (badgeModeCard3) {
                badgeModeCard3.innerText = 'Kolaborasi';
                badgeModeCard3.className = 'px-4 py-1.5 rounded-full bg-purple-100 text-purple-900 font-black text-xs uppercase tracking-widest border border-purple-400 shadow-sm';
            }
            if (labelPksBox) labelPksBox.innerText = 'Tim PKS / Guru BK';
            if (labelBox3Title) labelBox3Title.innerText = 'Guru Piket / Lainnya';
            if (iconBox3) iconBox3.className = 'fas fa-calendar-check text-pink-700 text-base';
            if (selectGurupiket.length) {
                if (selectGurupiket.hasClass('select2-hidden-accessible')) {
                    selectGurupiket.select2('destroy');
                }
                selectGurupiket.find('option:first-child').text('-- Cari Guru Piket Yang Bertugas --');
                selectGurupiket.select2({ placeholder: '-- Cari Guru Piket Yang Bertugas --', allowClear: true });
            }
            if (inputBox3Role) inputBox3Role.value = 'lainnya';
            if (inputBox3Notes) inputBox3Notes.value = 'Guru Piket / Petugas Harian';
            if (noteBox3) noteBox3.innerHTML = '<i class="fas fa-search mr-1"></i> Cari siapa guru piket yang bertugas hari ini.';
            if (labelAttachment) labelAttachment.innerText = 'Lampiran Dokumen Bukti (Opsional)';
            
            btnSubmit.className = btnSubmit.className.replace(/blue/g, 'rose');

            // Reset record types
            inputRecordType.innerHTML = `
                <option value="konseling">Bimbingan Konseling</option>
                <option value="pembinaan">Pembinaan Karakter</option>
                <option value="pelanggaran" selected>Pelanggaran Aturan</option>
                <option value="home_visit">Kunjungan Rumah (Home Visit)</option>
            `;

            catsMasalah.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.val; opt.text = c.text;
                inputCategory.add(opt);
            });
        }
    }
</script>
@endpush
