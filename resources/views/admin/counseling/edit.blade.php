@extends('layouts.admin')
@section('title', 'Edit Catatan Perkembangan - Pembda Elite')

@section('content')
<div class="space-y-8 w-full max-w-full px-2 sm:px-6 pb-12">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.counseling.show', $record) }}" class="w-12 h-12 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition shadow-sm">
                <i class="fas fa-arrow-left text-base"></i>
            </a>
            <div>
                <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight" id="page-title">Edit Catatan</h1>
                <p class="text-base font-bold text-slate-600 mt-1" id="page-subtitle">Pusat dokumentasi perkembangan dan karakter siswa</p>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.counseling.update', $record) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        
        {{-- Mode Switcher: Premium Segmented Control --}}
        @php $isPrestasi = $record->record_type === 'penghargaan'; @endphp
        <div class="bg-white p-2 rounded-2xl shadow-sm border border-gray-100 flex mb-8">
            <label class="flex-1 cursor-pointer">
                <input type="radio" name="report_mode" value="masalah" class="sr-only peer" {{ !$isPrestasi ? 'checked' : '' }} onchange="toggleMode()">
                <div class="py-3 text-center rounded-xl font-semibold text-xs uppercase tracking-wider transition-all peer-checked:bg-rose-500 peer-checked:text-white text-slate-400 hover:bg-slate-50">
                    <i class="fas fa-shield-halved mr-2"></i> Pembinaan & Kasus
                </div>
            </label>
            <label class="flex-1 cursor-pointer">
                <input type="radio" name="report_mode" value="prestasi" class="sr-only peer" {{ $isPrestasi ? 'checked' : '' }} onchange="toggleMode()">
                <div class="py-3 text-center rounded-xl font-semibold text-xs uppercase tracking-wider transition-all peer-checked:bg-blue-600 peer-checked:text-white text-slate-400 hover:bg-slate-50">
                    <i class="fas fa-trophy mr-2"></i> Prestasi & Penghargaan
                </div>
            </label>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
            {{-- Left Column: Main Data --}}
            <div class="xl:col-span-8 space-y-8">
                {{-- Detail Section --}}
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="bg-slate-50 px-8 py-4 border-b border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Informasi Utama</span>
                        <span id="badge-mode" class="text-xs font-semibold px-2 py-0.5 rounded-full bg-rose-100 text-rose-600 uppercase tracking-wider">Pembinaan</span>
                    </div>
                    <div class="p-8 space-y-6">
                        {{-- Title --}}
                        <div>
                            <label id="label-title" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 pl-1">Judul Kejadian <span class="text-rose-500">*</span></label>
                            <input type="text" name="title" id="input-title" required value="{{ old('title', $record->title) }}" 
                                placeholder="Contoh: Terlambat masuk sekolah"
                                class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl px-5 py-4 text-sm font-semibold focus:border-rose-500 focus:bg-white outline-none transition-all">
                        </div>

                        {{-- Description --}}
                        <div>
                            <label id="label-description" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 pl-1">Kronologi / Detail <span class="text-rose-500">*</span></label>
                            <textarea name="description" id="input-description" required rows="5" 
                                placeholder="Jelaskan detail peristiwa secara lengkap..."
                                class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl px-5 py-4 text-sm font-medium focus:border-rose-500 focus:bg-white outline-none transition-all">{{ old('description', $record->description) }}</textarea>
                        </div>

                        {{-- Action Taken --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 pl-1">Tindakan Lanjut</label>
                            <textarea name="action_taken" rows="3" 
                                placeholder="Apa langkah yang diambil untuk menangani hal ini?"
                                class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl px-5 py-4 text-sm font-medium focus:border-slate-300 focus:bg-white outline-none transition-all">{{ old('action_taken', $record->action_taken) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Step 3: Tembuskan Catatan (CC Partisipan & Petugas Terkait) --}}
                @php
                    $partWk = $record->participants->where('role', 'wali_kelas')->first();
                    $partPks = $record->participants->where('role', 'pks')->first();
                    $partLain = $record->participants->whereIn('role', ['lainnya', 'guru_mapel'])->first();
                @endphp
                <div class="bg-gradient-to-br from-indigo-950 via-slate-900 to-purple-950 rounded-2xl shadow-xl p-8 text-white border border-indigo-500/20 relative overflow-hidden">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-purple-500/10 rounded-full blur-2xl pointer-events-none"></div>
                    
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-white/10">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-purple-500/20 border border-purple-500/30 flex items-center justify-center text-purple-300 shadow-inner">
                                <i class="fas fa-share-nodes text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white">3. Tembuskan Catatan (CC Partisipan)</h3>
                                <p class="text-xs text-indigo-200/70">Pilih pihak terkait agar ikut memantau atau bertindak</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-purple-500/20 text-purple-300 border border-purple-500/30 uppercase tracking-widest">Kolaborasi</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Wali Kelas (Otomatis) --}}
                        @php
                            $wkUser = $partWk?->user ?? $record->student->currentClassroom->first()?->homeroomTeacher?->user;
                            $wkName = $wkUser ? ($wkUser->teacher?->full_name ?? $wkUser->name) : 'Wali Kelas Belum Ditentukan';
                            $wkId = $wkUser?->id ?? '';
                        @endphp
                        <div class="bg-slate-900/80 p-5 rounded-xl border-2 border-indigo-400/50 flex flex-col justify-between shadow-lg">
                            <div>
                                <label class="block text-xs font-black text-white uppercase tracking-wider mb-3 flex items-center justify-between">
                                    <span>Wali Kelas Siswa</span>
                                    <i class="fas fa-user-check text-indigo-400 text-base"></i>
                                </label>
                                <div class="w-full bg-white border-2 border-slate-300 rounded-xl px-4 py-3 min-h-[56px] flex items-center justify-between shadow-md">
                                    <span class="text-base sm:text-lg font-black text-slate-950 truncate">{{ $wkName }}</span>
                                    <span class="px-2.5 py-1 rounded-md bg-emerald-600 text-white text-[11px] font-black uppercase tracking-wider shrink-0 ml-2 shadow"><i class="fas fa-check mr-1"></i> Auto</span>
                                </div>
                                <input type="hidden" name="participants[0][user_id]" value="{{ $wkId }}">
                                <input type="hidden" name="participants[0][role]" value="wali_kelas">
                            </div>
                            <p class="text-xs text-emerald-300 mt-3 font-extrabold"><i class="fas fa-magic mr-1"></i> Terdeteksi otomatis tanpa pilih manual.</p>
                        </div>

                        {{-- Tim PKS / BK (Otomatis) --}}
                        @php
                            $bkUser = $partPks?->user ?? ($counselors->where('school_id', $record->school_id)->filter(function($u) {
                                return $u->role === 'guru_bk' || $u->role === 'pks' || $u->hasSpecialDuty(['BK', 'KESISWAAN', 'PKS', 'BKK']);
                            })->first() ?? $counselors->where('school_id', $record->school_id)->first());
                            $bkName = $bkUser ? $bkUser->display_name : 'Tim PKS / BK Sekolah';
                            $bkId = $bkUser?->id ?? '';
                        @endphp
                        <div class="bg-slate-900/80 p-5 rounded-xl border-2 border-purple-400/50 flex flex-col justify-between shadow-lg">
                            <div>
                                <label class="block text-xs font-black text-white uppercase tracking-wider mb-3 flex items-center justify-between">
                                    <span>Tim PKS / Guru BK</span>
                                    <i class="fas fa-shield-halved text-purple-400 text-base"></i>
                                </label>
                                <div class="w-full bg-white border-2 border-slate-300 rounded-xl px-4 py-3 min-h-[56px] flex items-center justify-between shadow-md">
                                    <span class="text-base sm:text-lg font-black text-slate-950 truncate">{{ $bkName }}</span>
                                    <span class="px-2.5 py-1 rounded-md bg-purple-600 text-white text-[11px] font-black uppercase tracking-wider shrink-0 ml-2 shadow"><i class="fas fa-check mr-1"></i> Auto</span>
                                </div>
                                <input type="hidden" name="participants[1][user_id]" value="{{ $bkId }}">
                                <input type="hidden" name="participants[1][role]" value="pks">
                            </div>
                            <p class="text-xs text-purple-300 mt-3 font-extrabold"><i class="fas fa-check-circle mr-1"></i> Terhubung otomatis ke Tim BK sekolah.</p>
                        </div>

                        {{-- Guru Piket / Petugas Bertugas Hari Ini --}}
                        <div class="bg-slate-900/80 p-5 rounded-xl border-2 {{ $isPrestasi ? 'border-amber-400/50' : 'border-pink-400/50' }} flex flex-col justify-between shadow-lg">
                            <div>
                                <label class="block text-xs font-black text-white uppercase tracking-wider mb-3 flex items-center justify-between">
                                    <span id="label-box3-title">{{ $isPrestasi ? 'Guru Pembimbing / Pelatih' : 'Guru Piket / Lainnya' }}</span>
                                    <i id="icon-box3" class="fas {{ $isPrestasi ? 'fa-trophy text-amber-400' : 'fa-calendar-check text-pink-400' }} text-base"></i>
                                </label>
                                <select name="participants[2][user_id]" id="select-gurupiket" class="w-full bg-white border-2 border-slate-300 rounded-xl px-4 py-3 text-base sm:text-lg font-black text-slate-950 focus:border-purple-500 outline-none transition-all shadow-md">
                                    <option value="">{{ $isPrestasi ? '-- Cari Guru Pembimbing / Pelatih --' : '-- Cari Guru Piket Yang Bertugas --' }}</option>
                                    @foreach($counselors as $c)
                                        <option value="{{ $c->id }}" {{ $partLain?->user_id == $c->id ? 'selected' : '' }}>{{ $c->display_name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="participants[2][role]" id="input-box3-role" value="{{ $isPrestasi ? 'guru_mapel' : 'lainnya' }}">
                                <input type="hidden" name="participants[2][notes]" id="input-box3-notes" value="{{ $isPrestasi ? 'Guru Pembimbing / Pelatih Lomba' : 'Guru Piket / Petugas Harian' }}">
                            </div>
                            <p id="note-box3" class="text-xs {{ $isPrestasi ? 'text-amber-300' : 'text-pink-300' }} mt-3 font-extrabold"><i class="fas {{ $isPrestasi ? 'fa-medal' : 'fa-search' }} mr-1"></i> {{ $isPrestasi ? 'Cari guru yang membimbing atau melatih lomba ini.' : 'Cari siapa guru piket yang bertugas hari ini.' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Attachment --}}
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 flex items-center gap-6">
                    <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400 text-2xl">
                        <i class="fas fa-paperclip"></i>
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Lampiran Dokumen (Opsional)</label>
                        @if($record->attachment)
                            <div class="flex items-center gap-2 mb-2 text-xs font-semibold text-blue-600 italic">
                                <i class="fas fa-check-circle"></i> File terpasang: {{ $record->attachment_name ?? 'Dokumen' }}
                            </div>
                        @endif
                        <input type="file" name="attachment" class="text-sm font-semibold text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-600 hover:file:bg-slate-200 cursor-pointer">
                        <p class="text-xs text-slate-400 mt-2">Format: PDF, JPG, PNG, DOC (Maks 10MB). Upload untuk mengganti.</p>
                    </div>
                </div>
            </div>

            {{-- Right Column: Metadata & Settings --}}
            <div class="xl:col-span-4 space-y-8">
                {{-- Target & Date --}}
                <div class="bg-gradient-to-br from-indigo-950 via-slate-900 to-purple-950 rounded-2xl shadow-xl p-8 text-white space-y-6 border border-indigo-500/20">
                    <div>
                        <label class="block text-xs font-black text-white uppercase tracking-wider mb-3">Siswa</label>
                        <div class="p-4 bg-white rounded-2xl border-2 border-slate-300 shadow-md">
                            <p class="text-base sm:text-lg font-black text-slate-950">{{ $record->student->full_name }}</p>
                            <p class="text-xs font-black text-indigo-600 uppercase tracking-wider mt-1">{{ $record->student->currentClassroom->first()?->class_name ?? 'Tanpa Kelas' }}</p>
                        </div>
                        <input type="hidden" name="student_id" value="{{ $record->student_id }}">
                    </div>

                    <div>
                        <label id="label-date" class="block text-xs font-black text-white uppercase tracking-wider mb-3">Tanggal Kejadian <span class="text-rose-400">*</span></label>
                        <input type="date" name="incident_date" value="{{ old('incident_date', $record->incident_date?->format('Y-m-d')) }}" required 
                            class="w-full bg-white border-2 border-slate-300 rounded-xl px-4 py-3 text-base font-black text-slate-950 focus:border-blue-500 outline-none transition-all shadow-md">
                    </div>

                    <div id="field-status-box">
                        <label class="block text-xs font-black text-white uppercase tracking-wider mb-3">Status Catatan <span class="text-rose-400">*</span></label>
                        <select name="status" id="input-status" class="w-full bg-white border-2 border-slate-300 rounded-xl px-4 py-3 text-base font-black text-slate-950 focus:border-blue-500 outline-none transition-all cursor-pointer shadow-md">
                            <option value="open" {{ old('status', $record->status) === 'open' ? 'selected' : '' }}>Baru / Terbuka</option>
                            <option value="in_progress" {{ old('status', $record->status) === 'in_progress' ? 'selected' : '' }}>Tindak Lanjut</option>
                            <option value="resolved" {{ old('status', $record->status) === 'resolved' ? 'selected' : '' }}>Selesai / Teratasi</option>
                            <option value="closed" {{ old('status', $record->status) === 'closed' ? 'selected' : '' }}>Ditutup</option>
                        </select>
                    </div>
                </div>

                {{-- Classification --}}
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 space-y-6">
                    <div id="field-classification-masalah" class="space-y-6">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Jenis Catatan</label>
                            <select name="record_type" id="input-record-type" class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl px-4 py-3 text-sm font-semibold focus:border-rose-500 outline-none transition-all">
                                <option value="konseling" {{ $record->record_type == 'konseling' ? 'selected' : '' }}>Bimbingan Konseling</option>
                                <option value="pembinaan" {{ $record->record_type == 'pembinaan' ? 'selected' : '' }}>Pembinaan Karakter</option>
                                <option value="pelanggaran" {{ $record->record_type == 'pelanggaran' ? 'selected' : '' }}>Pelanggaran Aturan</option>
                                <option value="home_visit" {{ $record->record_type == 'home_visit' ? 'selected' : '' }}>Kunjungan Rumah (Home Visit)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Tingkat Keseriusan</label>
                            <div class="grid grid-cols-3 gap-3">
                                <label class="cursor-pointer group">
                                    <input type="radio" name="severity" value="ringan" class="sr-only peer" {{ old('severity', $record->severity) == 'ringan' ? 'checked' : '' }}>
                                    <div class="py-3 text-sm font-black text-center border-2 border-emerald-500 bg-emerald-50 text-emerald-950 rounded-xl peer-checked:border-emerald-700 peer-checked:bg-emerald-600 peer-checked:text-white transition-all uppercase shadow-md hover:bg-emerald-100">
                                        Ringan
                                    </div>
                                </label>
                                <label class="cursor-pointer group">
                                    <input type="radio" name="severity" value="sedang" class="sr-only peer" {{ old('severity', $record->severity) == 'sedang' ? 'checked' : '' }}>
                                    <div class="py-3 text-sm font-black text-center border-2 border-amber-500 bg-amber-50 text-amber-950 rounded-xl peer-checked:border-amber-600 peer-checked:bg-amber-400 peer-checked:text-slate-950 transition-all uppercase shadow-md hover:bg-amber-100">
                                        Sedang
                                    </div>
                                </label>
                                <label class="cursor-pointer group">
                                    <input type="radio" name="severity" value="berat" class="sr-only peer" {{ old('severity', $record->severity) == 'berat' ? 'checked' : '' }}>
                                    <div class="py-3 text-sm font-black text-center border-2 border-rose-500 bg-rose-50 text-rose-950 rounded-xl peer-checked:border-rose-700 peer-checked:bg-rose-600 peer-checked:text-white transition-all uppercase shadow-md hover:bg-rose-100">
                                        Berat
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="field-classification-prestasi" style="display:none;" class="space-y-6">
                        <input type="hidden" name="dummy_record_type" value="penghargaan">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Level Pencapaian</label>
                            <select name="achievement_level" class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl px-4 py-3 text-sm font-semibold focus:border-blue-600 outline-none transition-all">
                                <option value="sekolah" {{ $record->achievement_level == 'sekolah' ? 'selected' : '' }}>Tingkat Sekolah (+100 Poin)</option>
                                <option value="kabupaten" {{ $record->achievement_level == 'kabupaten' ? 'selected' : '' }}>Tingkat Kabupaten / Kota (+200 Poin)</option>
                                <option value="propinsi" {{ $record->achievement_level == 'propinsi' ? 'selected' : '' }}>Tingkat Provinsi (+300 Poin)</option>
                                <option value="nasional" {{ $record->achievement_level == 'nasional' ? 'selected' : '' }}>Tingkat Nasional (+400 Poin)</option>
                                <option value="internasional" {{ $record->achievement_level == 'internasional' ? 'selected' : '' }}>Tingkat Internasional (+500 Poin)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Kategori</label>
                        <select name="category" id="input-category" data-selected="{{ $record->category }}" class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl px-4 py-3 text-sm font-semibold focus:border-blue-600 outline-none transition-all">
                            <!-- Populated by JS -->
                        </select>
                    </div>

                    <div class="pt-4 border-t border-slate-50 flex flex-col gap-3">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="is_confidential" value="1" {{ old('is_confidential', $record->is_confidential) ? 'checked' : '' }} class="w-5 h-5 rounded border-2 border-slate-200 text-rose-500 focus:ring-rose-500 transition-all">
                            <span class="text-xs font-bold text-slate-600 group-hover:text-slate-800 transition">Catatan Rahasia</span>
                        </label>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="space-y-3">
                    <button type="submit" id="btn-submit" class="w-full py-3 bg-gradient-to-r from-rose-500 to-rose-600 text-white rounded-xl font-semibold text-sm shadow-lg shadow-rose-200 hover:shadow-rose-300 hover:-translate-y-0.5 transition duration-300">
                        Update Catatan
                    </button>
                    <a href="{{ route('admin.counseling.show', $record) }}" class="block w-full py-4 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-gray-600 transition">Batalkan Perubahan</a>
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
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#select-gurupiket').select2({ placeholder: "{{ $isPrestasi ? '-- Cari Guru Pembimbing / Pelatih --' : '-- Cari Guru Piket Yang Bertugas --' }}", allowClear: true });
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
        const labelDate = document.getElementById('label-date');
        const inputTitle = document.getElementById('input-title');
        const inputDesc = document.getElementById('input-description');
        const fieldMasalah = document.getElementById('field-classification-masalah');
        const fieldPrestasi = document.getElementById('field-classification-prestasi');
        const inputCategory = document.getElementById('input-category');
        const selectedCategory = inputCategory.getAttribute('data-selected');
        const inputRecordType = document.getElementById('input-record-type');
        const btnSubmit = document.getElementById('btn-submit');

        inputCategory.innerHTML = '';

        if (mode === 'prestasi') {
            pageTitle.innerText = 'Edit Prestasi Siswa';
            pageSubtitle.innerText = 'Lakukan penyesuaian pada data penghargaan';
            badgeMode.innerText = 'Prestasi';
            badgeMode.className = 'text-[9px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-600 uppercase tracking-widest';
            
            labelTitle.innerText = 'Nama Kompetisi / Piagam';
            labelDesc.innerText = 'Deskripsi Pencapaian';
            labelDate.innerText = 'Tanggal Perolehan';
            inputTitle.placeholder = 'Contoh: Juara 1 OSN Matematika';
            inputDesc.placeholder = 'Jelaskan bagaimana prestasi ini diraih...';
            
            fieldMasalah.style.display = 'none';
            fieldPrestasi.style.display = 'block';
            
            btnSubmit.className = btnSubmit.className.replace(/rose/g, 'blue');
            
            // Internal record type
            if (!inputRecordType.querySelector('option[value="penghargaan"]')) {
                const opt = document.createElement('option');
                opt.value = 'penghargaan'; opt.text = 'Penghargaan';
                inputRecordType.add(opt);
            }
            inputRecordType.value = 'penghargaan';

            catsPrestasi.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.val; opt.text = c.text;
                if (c.val === selectedCategory) opt.selected = true;
                inputCategory.add(opt);
            });
        } else {
            pageTitle.innerText = 'Edit Catatan Pembinaan';
            pageSubtitle.innerText = 'Perbarui data konseling atau pelanggaran';
            badgeMode.innerText = 'Pembinaan';
            badgeMode.className = 'text-[9px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-600 uppercase tracking-widest';
            
            labelTitle.innerText = 'Judul Peristiwa';
            labelDesc.innerText = 'Kronologi / Detail';
            labelDate.innerText = 'Tanggal Kejadian';
            inputTitle.placeholder = 'Contoh: Merokok di lingkungan sekolah';
            inputDesc.placeholder = 'Jelaskan kronologi kejadian secara objektif...';
            
            fieldMasalah.style.display = 'block';
            fieldPrestasi.style.display = 'none';
            
            btnSubmit.className = btnSubmit.className.replace(/blue/g, 'rose');

            // Set to old record type or default
            if (inputRecordType.value === 'penghargaan') {
                inputRecordType.value = 'konseling';
            }

            catsMasalah.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.val; opt.text = c.text;
                if (c.val === selectedCategory) opt.selected = true;
                inputCategory.add(opt);
            });
        }
    }
</script>
@endpush
