@extends('layouts.yayasan')

@section('title', 'Edit Surat Digital')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    {{-- Back Link & Header --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('yayasan.letters.show', $letter->id) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-violet-700 transition-colors">
            <i class="fas fa-arrow-left"></i> Batal & Kembali ke Detail Surat
        </a>
        <span class="text-xs font-semibold bg-amber-100 text-amber-800 px-3 py-1 rounded-full">
            <i class="fas fa-edit mr-1"></i> Edit Dokumen Terbit
        </span>
    </div>

    {{-- Main Form Card --}}
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        {{-- Form Header --}}
        <div class="bg-gradient-to-r from-amber-700 to-orange-800 p-6 text-white">
            <h2 class="text-xl font-extrabold flex items-center gap-2">
                <i class="fas fa-edit text-amber-200"></i> Edit & Perbarui Surat Digital
            </h2>
            <p class="text-xs text-amber-100 mt-1">Ubah isi, perihal, nomor surat, atau atribut lainnya. QR Code Verifikasi tetap berlaku aman.</p>
        </div>

        {{-- Form Body --}}
        <form action="{{ route('yayasan.letters.update', $letter->id) }}" method="POST" class="p-6 md:p-8 space-y-6">
            @csrf
            @method('PUT')

            {{-- Block 1: Identitas Kop Surat & Nomor --}}
            <div class="bg-amber-50/40 border border-amber-200/60 rounded-xl p-4 md:p-6 space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-amber-900 flex items-center gap-2 border-b border-amber-200 pb-2">
                    <i class="fas fa-landmark"></i> 1. Identitas Yayasan & Nomor Surat
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Identitas Instansi Penerbit</label>
                        <input type="text" value="Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)" class="w-full bg-gray-100 border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 font-semibold cursor-not-allowed" readonly>
                        <p class="text-[11px] text-gray-500 mt-1"><i class="fas fa-map-marker-alt mr-1"></i>Jl. Pelita No.09 Kel. Ilir Kota Gunungsitoli (22815)</p>
                    </div>

                    <div>
                        <label for="letter_number" class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            Nomor Surat (Input Manual) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="letter_number" id="letter_number" value="{{ old('letter_number', $letter->letter_number) }}" class="w-full border-2 border-amber-300 focus:border-amber-600 rounded-lg px-3 py-2 text-sm font-mono font-bold text-amber-900 focus:ring-0" required>
                    </div>
                </div>
            </div>

            {{-- Block 2: Jenis Surat & Tanggal --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="category" class="block text-xs font-bold text-gray-700 uppercase mb-1">
                        Jenis / Tipe Surat <span class="text-rose-500">*</span>
                    </label>
                    <select name="category" id="category" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-violet-600 focus:ring-0 font-medium">
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ old('category', $letter->category) === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="effective_date" class="block text-xs font-bold text-gray-700 uppercase mb-1">
                        Tanggal Ditetapkan <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="effective_date" id="effective_date" value="{{ old('effective_date', $letter->effective_date ? $letter->effective_date->format('Y-m-d') : '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-violet-600 focus:ring-0" required>
                </div>

                <div>
                    <label for="deadline_date" class="block text-xs font-bold text-gray-700 uppercase mb-1">
                        Tenggat Waktu / Tanggal Batas (Opsional)
                    </label>
                    <input type="date" name="deadline_date" id="deadline_date" value="{{ old('deadline_date', $letter->deadline_date ? $letter->deadline_date->format('Y-m-d') : '') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-violet-600 focus:ring-0">
                </div>
            </div>

            {{-- Block 3: Multi-Select Checkbox Tujuan Surat --}}
            <div class="bg-indigo-50/50 border border-indigo-100 rounded-xl p-4 md:p-6 space-y-3">
                <div class="flex items-center justify-between border-b border-indigo-200/60 pb-2">
                    <label class="block text-xs font-bold text-indigo-900 uppercase flex items-center gap-2">
                        <i class="fas fa-users-cog text-violet-700"></i> Tujuan Surat (Dapat Ditandai / Centang Banyak sekaligus) <span class="text-rose-500">*</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                    @foreach($targetAudiences as $key => $label)
                        @php
                            $isChecked = is_array(old('target_audiences', $selectedTargets)) && in_array($key, old('target_audiences', $selectedTargets));
                        @endphp
                        <label class="flex items-start gap-3 p-3 bg-white border rounded-xl hover:border-violet-400 cursor-pointer transition-all shadow-sm">
                            <input type="checkbox" name="target_audiences[]" value="{{ $key }}" class="w-4 h-4 mt-0.5 rounded text-violet-600 focus:ring-violet-500 border-gray-300" {{ $isChecked ? 'checked' : '' }}>
                            <span class="text-xs font-semibold text-gray-800 leading-snug">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('target_audiences')
                    <p class="text-xs text-rose-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            {{-- Perihal --}}
            <div>
                <label for="title" class="block text-xs font-bold text-gray-700 uppercase mb-1">
                    Perihal / Judul Surat <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="title" id="title" value="{{ old('title', $letter->title) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-semibold text-gray-900 focus:border-violet-600 focus:ring-0" required>
            </div>

            {{-- Block 4: Isi Surat Rich Text --}}
            <div>
                <label for="content" class="block text-xs font-bold text-gray-700 uppercase mb-1">
                    Isi Surat Resmi <span class="text-rose-500">*</span>
                </label>
                <textarea name="content" id="content" rows="14" class="w-full border border-gray-300 rounded-xl p-4 text-sm text-gray-800 focus:border-violet-600 focus:ring-0 font-sans leading-relaxed" required>{{ old('content', $letter->content) }}</textarea>
            </div>

            {{-- Block 5: Identitas Penandatangan (Ketua Yayasan) --}}
            <div class="bg-amber-50/60 border border-amber-200/80 rounded-xl p-4 md:p-6 space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-amber-900 flex items-center gap-2 border-b border-amber-200 pb-2">
                    <i class="fas fa-user-check"></i> Identitas Penandatangan
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="signatory_name" class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            Nama Penandatangan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="signatory_name" id="signatory_name" value="{{ old('signatory_name', $letter->signatory_name) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold text-gray-900 focus:border-violet-600 focus:ring-0" required>
                    </div>

                    <div>
                        <label for="signatory_position" class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            Jabatan Penandatangan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="signatory_position" id="signatory_position" value="{{ old('signatory_position', $letter->signatory_position) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold text-gray-900 focus:border-violet-600 focus:ring-0" required>
                    </div>
                </div>
            </div>

            {{-- Submit Action --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('yayasan.letters.show', $letter->id) }}" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 text-sm transition-colors">
                    Batal
                </a>
                <button type="submit" class="bg-gradient-to-r from-amber-600 to-orange-700 hover:from-amber-700 hover:to-orange-800 text-white font-bold px-6 py-2.5 rounded-xl shadow-lg text-sm transition-all flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    <span>Simpan Perubahan Surat</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
