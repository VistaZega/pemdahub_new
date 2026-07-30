@extends('layouts.yayasan')

@section('title', 'Buat Surat Digital Baru')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    {{-- Back Link & Header --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('yayasan.letters.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-violet-700 transition-colors">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Surat
        </a>
        <span class="text-xs font-semibold bg-violet-100 text-violet-700 px-3 py-1 rounded-full">
            <i class="fas fa-qrcode mr-1"></i> Form Digital Certificate & QR Generator
        </span>
    </div>

    {{-- Main Form Card --}}
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        {{-- Form Header --}}
        <div class="bg-gradient-to-r from-violet-800 to-purple-800 p-6 text-white">
            <h2 class="text-xl font-extrabold flex items-center gap-2">
                <i class="fas fa-file-signature text-amber-400"></i> Buat & Terbitkan Surat Digital Resmi
            </h2>
            <p class="text-xs text-violet-200 mt-1">Lengkapi atribut surat di bawah ini. Sistem akan otomatis menyematkan Kop Resmi Yayasan dan QR Code Otentikasi Digital.</p>
        </div>

        {{-- Form Body --}}
        <form action="{{ route('yayasan.letters.store') }}" method="POST" class="p-6 md:p-8 space-y-6">
            @csrf

            {{-- Block 1: Identitas Kop Surat & Nomor --}}
            <div class="bg-violet-50/50 border border-violet-100 rounded-xl p-4 md:p-6 space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-violet-800 flex items-center gap-2 border-b border-violet-200/60 pb-2">
                    <i class="fas fa-landmark"></i> 1. Identitas Yayasan & Nomor Surat (Tata Naskah Dinas)
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Identitas Instansi Penerbit</label>
                        <input type="text" value="Yayasan Perguruan PEMBDA Nias" class="w-full bg-gray-100 border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 font-semibold cursor-not-allowed" readonly>
                        <p class="text-[11px] text-gray-500 mt-1"><i class="fas fa-map-marker-alt mr-1"></i>Jl. Pelita No.09, Gunungsitoli, Sumatera Utara (22812)</p>
                    </div>

                    <div>
                        <label for="letter_number" class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            Nomor Surat (Input Manual) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="letter_number" id="letter_number" value="{{ old('letter_number', $defaultNumber) }}" class="w-full border-2 border-violet-200 focus:border-violet-600 rounded-lg px-3 py-2 text-sm font-mono font-bold text-violet-900 focus:ring-0" placeholder="Contoh: ....../SE/YP-PEMBDA/VII/2026" required>
                        <p class="text-[11px] text-gray-500 mt-1">Format penomoran surat fisik/dinas yang berlaku di Yayasan.</p>
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
                            <option value="{{ $key }}" {{ old('category', $defaultCategory) === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="effective_date" class="block text-xs font-bold text-gray-700 uppercase mb-1">
                        Tanggal Ditetapkan <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="effective_date" id="effective_date" value="{{ old('effective_date', $defaultEffectiveDate) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-violet-600 focus:ring-0" required>
                </div>

                <div>
                    <label for="deadline_date" class="block text-xs font-bold text-gray-700 uppercase mb-1">
                        Tenggat Waktu / Tanggal Batas (Opsional)
                    </label>
                    <input type="date" name="deadline_date" id="deadline_date" value="{{ old('deadline_date', $defaultDeadlineDate) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-violet-600 focus:ring-0">
                </div>
            </div>

            {{-- Block 3: Multi-Select Checkbox Tujuan Surat --}}
            <div class="bg-indigo-50/50 border border-indigo-100 rounded-xl p-4 md:p-6 space-y-3">
                <div class="flex items-center justify-between border-b border-indigo-200/60 pb-2">
                    <label class="block text-xs font-bold text-indigo-900 uppercase flex items-center gap-2">
                        <i class="fas fa-users-cog text-violet-700"></i> Tujuan Surat (Dapat Ditandai / Centang Banyak sekaligus) <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[11px] text-indigo-600 font-medium">Pilih 1 atau lebih penerima</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                    @foreach($targetAudiences as $key => $label)
                        @php
                            $isChecked = is_array(old('target_audiences', $defaultTargets)) && in_array($key, old('target_audiences', $defaultTargets));
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
                <input type="text" name="title" id="title" value="{{ old('title', $defaultTitle) }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-semibold text-gray-900 focus:border-violet-600 focus:ring-0" placeholder="Masukkan perihal surat..." required>
            </div>

            {{-- Block 4: Isi Surat Rich Text --}}
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="content" class="block text-xs font-bold text-gray-700 uppercase">
                        Isi Surat Resmi <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-500">Template Cepat:</span>
                        <a href="{{ route('yayasan.letters.create', ['preset' => 'standar_input']) }}" class="text-xs font-semibold text-violet-700 hover:underline bg-violet-50 px-2 py-1 rounded">
                            <i class="fas fa-file-invoice mr-1"></i> Standar Input Data (3 Agt 2026)
                        </a>
                    </div>
                </div>

                <textarea name="content" id="content" rows="14" class="w-full border border-gray-300 rounded-xl p-4 text-sm text-gray-800 focus:border-violet-600 focus:ring-0 font-sans leading-relaxed" placeholder="Tuliskan isi surat resmi di sini..." required>{{ old('content', $defaultContent) }}</textarea>
                <p class="text-[11px] text-gray-500 mt-1">Dapat memuat tag HTML sederhana (&lt;p&gt;, &lt;strong&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;ol&gt;, &lt;br&gt;).</p>
            </div>

            {{-- Block 5: Identitas Penandatangan (Ketua Yayasan) --}}
            <div class="bg-amber-50/60 border border-amber-200/80 rounded-xl p-4 md:p-6 space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-amber-900 flex items-center gap-2 border-b border-amber-200 pb-2">
                    <i class="fas fa-user-check"></i> Identitas Penandatangan & Digital Signature Authorization
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="signatory_name" class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            Nama Penandatangan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="signatory_name" id="signatory_name" value="{{ old('signatory_name', 'Yulianus Zega, S.Kom,M.Pd.T') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold text-gray-900 focus:border-violet-600 focus:ring-0" required>
                    </div>

                    <div>
                        <label for="signatory_position" class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            Jabatan Penandatangan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="signatory_position" id="signatory_position" value="{{ old('signatory_position', 'Ketua Yayasan Perguruan PEMBDA Nias') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold text-gray-900 focus:border-violet-600 focus:ring-0" required>
                    </div>
                </div>

                <div class="flex items-center gap-3 bg-white p-3 rounded-lg border border-amber-200 text-xs text-amber-900">
                    <i class="fas fa-shield-alt text-lg text-emerald-600"></i>
                    <div>
                        <strong class="block font-bold">Ototentikasi Kriptografi Aktif</strong>
                        <span>Dengan menerbitkan surat ini, sistem akan meng-generate QR Code Verifikasi SHA-256 yang menempel pada cetakan dokumen resmi.</span>
                    </div>
                </div>
            </div>

            {{-- Submit Action --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('yayasan.letters.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 text-sm transition-colors">
                    Batal
                </a>
                <button type="submit" class="bg-gradient-to-r from-violet-600 to-purple-700 hover:from-violet-700 hover:to-purple-800 text-white font-bold px-6 py-2.5 rounded-xl shadow-lg shadow-violet-600/30 text-sm transition-all flex items-center gap-2">
                    <i class="fas fa-qrcode"></i>
                    <span>Tandatangani & Terbitkan Surat Digital</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
