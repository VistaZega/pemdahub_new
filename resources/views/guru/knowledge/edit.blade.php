@extends('layouts.guru')
@section('title', 'Edit Materi - Pembda Knowledge & Media')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Breadcrumb / Header --}}
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('guru.knowledge.index') }}" class="text-xs font-bold text-teal-700 hover:text-teal-800 flex items-center gap-1.5 mb-1">
                <i class="fas fa-arrow-left"></i> Kembali ke Ruang Publikasi
            </a>
            <h1 class="text-2xl font-black text-slate-900">Edit Publikasi: {{ $knowledge->title }}</h1>
        </div>

        <a href="{{ route('knowledge.download_template') }}" class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs rounded-xl border border-indigo-200 shadow-sm transition-all flex items-center gap-1.5">
            <i class="fas fa-download text-indigo-600"></i> Unduh Template Jurnal (Opsional)
        </a>
    </div>

    {{-- Info Banner Template Opsional --}}
    <div class="bg-gradient-to-r from-teal-50 to-indigo-50 p-4 rounded-2xl border border-teal-200 text-slate-800 space-y-1">
        <h4 class="text-xs font-black uppercase text-teal-800 tracking-wider flex items-center gap-2">
            <i class="fas fa-info-circle text-teal-600"></i> Informasi Format Penulisan & Template Jurnal
        </h4>
        <p class="text-xs text-slate-600 leading-relaxed">
            Perguruan Pembda menyediakan <strong>Template Penulisan Resmi Jurnal & Karya Tulis</strong>. Penggunaan template ini sifatnya <strong>OPSIONAL (Bebas / Tidak Wajib)</strong>. Anda diperbolehkan mengunggah karya dengan format penulisan mandiri.
        </p>
    </div>

    {{-- Form Card --}}
    <div class="bg-white rounded-2xl p-6 md:p-8 border border-slate-200 shadow-sm">
        <form action="{{ route('guru.knowledge.update', $knowledge->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Title --}}
            <div class="space-y-2">
                <label class="block text-sm font-bold text-slate-800">Judul Materi / Karya <span class="text-rose-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $knowledge->title) }}" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">
                @error('title') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            {{-- Category Type & Subject --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-data="{ category: '{{ old('category_type', $knowledge->category_type) }}' }">
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-slate-800">Kategori Publikasi <span class="text-rose-500">*</span></label>
                    <select name="category_type" x-model="category" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">
                        <option value="sekolah">📚 Materi Sekolah (Terikat Mata Pelajaran)</option>
                        <option value="umum">💡 Umum / Hobi / Artikel & Referensi Bebas</option>
                    </select>
                </div>

                <div class="space-y-2" x-show="category === 'sekolah'">
                    <label class="block text-sm font-bold text-slate-800">Mata Pelajaran (Mapel)</label>
                    <select name="subject_id" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach($subjects as $sub)
                            <option value="{{ $sub->id }}" {{ old('subject_id', $knowledge->subject_id) == $sub->id ? 'selected' : '' }}>{{ $sub->subject_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Type & Form Input dynamic --}}
            <div class="space-y-4" x-data="{ type: '{{ old('type', $knowledge->type) }}' }">
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-slate-800">Jenis Format Media <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <label class="flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer transition-all"
                            :class="type === 'document' ? 'border-teal-500 bg-teal-50 text-teal-800 font-bold' : 'border-slate-200 hover:border-slate-300 text-slate-600'">
                            <input type="radio" name="type" value="document" x-model="type" class="sr-only">
                            <i class="fas fa-file-pdf text-2xl mb-1" :class="type === 'document' ? 'text-teal-600' : 'text-slate-400'"></i>
                            <span class="text-xs">Dokumen (PDF/Doc)</span>
                        </label>

                        <label class="flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer transition-all"
                            :class="type === 'video' ? 'border-rose-500 bg-rose-50 text-rose-800 font-bold' : 'border-slate-200 hover:border-slate-300 text-slate-600'">
                            <input type="radio" name="type" value="video" x-model="type" class="sr-only">
                            <i class="fas fa-video text-2xl mb-1" :class="type === 'video' ? 'text-rose-600' : 'text-slate-400'"></i>
                            <span class="text-xs">Video (YouTube/MP4)</span>
                        </label>

                        <label class="flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer transition-all"
                            :class="type === 'audio' ? 'border-amber-500 bg-amber-50 text-amber-800 font-bold' : 'border-slate-200 hover:border-slate-300 text-slate-600'">
                            <input type="radio" name="type" value="audio" x-model="type" class="sr-only">
                            <i class="fas fa-headphones text-2xl mb-1" :class="type === 'audio' ? 'text-amber-600' : 'text-slate-400'"></i>
                            <span class="text-xs">Audio (MP3/Podcast)</span>
                        </label>

                        <label class="flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer transition-all"
                            :class="type === 'link' ? 'border-sky-500 bg-sky-50 text-sky-800 font-bold' : 'border-slate-200 hover:border-slate-300 text-slate-600'">
                            <input type="radio" name="type" value="link" x-model="type" class="sr-only">
                            <i class="fas fa-link text-2xl mb-1" :class="type === 'link' ? 'text-sky-600' : 'text-slate-400'"></i>
                            <span class="text-xs">Link Web / Github</span>
                        </label>
                    </div>
                </div>

                {{-- File Input --}}
                <div class="space-y-2" x-show="type === 'document' || type === 'audio' || type === 'video'">
                    <label class="block text-sm font-bold text-slate-800">Ganti File Media (Biarkan kosong jika tidak diganti)</label>
                    <input type="file" name="file" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700">
                    @if($knowledge->file_path)
                        <p class="text-xs text-teal-600 font-medium">File saat ini: {{ basename($knowledge->file_path) }}</p>
                    @endif
                </div>

                {{-- External URL --}}
                <div class="space-y-2" x-show="type === 'link' || type === 'video'">
                    <label class="block text-sm font-bold text-slate-800">Tautan Eksternal / URL Video (YouTube)</label>
                    <input type="url" name="external_url" value="{{ old('external_url', $knowledge->external_url) }}" placeholder="https://..." class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">
                </div>
            </div>

            {{-- Thumbnail --}}
            <div class="space-y-2">
                <label class="block text-sm font-bold text-slate-800">Ganti Cover / Thumbnail (Opsional)</label>
                <input type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
                <p class="text-[11px] text-slate-500 font-medium"><i class="fas fa-info-circle text-teal-600"></i> Format gambar: JPG, PNG, WEBP. Ukuran file thumbnail tidak boleh lebih dari <strong>10 MB (10.240 Kilobyte)</strong>.</p>
                @error('thumbnail') <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                @if($knowledge->thumbnail_url)
                    <div class="mt-2 w-24 h-24 rounded-xl overflow-hidden border border-slate-200">
                        <img src="{{ $knowledge->thumbnail_url }}" class="w-full h-full object-cover">
                    </div>
                @endif
            </div>

            {{-- Description --}}
            <div class="space-y-2">
                <label class="block text-sm font-bold text-slate-800">Deskripsi / Penjelasan Ringkas</label>
                <textarea name="description" rows="4" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm">{{ old('description', $knowledge->description) }}</textarea>
            </div>

            {{-- Permissions & Access Controls --}}
            <div class="bg-slate-50 p-5 rounded-xl border border-slate-200 space-y-4">
                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-shield-alt text-teal-600"></i> Pengaturan Akses & Proteksi Unduh
                </h3>

                <div class="flex flex-col sm:flex-row gap-6">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_public" value="1" {{ old('is_public', $knowledge->is_public) ? 'checked' : '' }} class="w-5 h-5 text-teal-600 rounded border-slate-300 focus:ring-teal-500">
                        <div>
                            <span class="text-sm font-bold text-slate-800 block">Publikasikan ke Etalase</span>
                            <span class="text-xs text-slate-500">Materi dapat dilihat oleh publik & siswa</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="allow_download" value="1" {{ old('allow_download', $knowledge->allow_download) ? 'checked' : '' }} class="w-5 h-5 text-teal-600 rounded border-slate-300 focus:ring-teal-500">
                        <div>
                            <span class="text-sm font-bold text-slate-800 block">Izinkan Opsi Unduh (Download)</span>
                            <span class="text-xs text-slate-500">Siswa/Pengunjung diperbolehkan mengunduh file</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('guru.knowledge.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-sm">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl shadow-md transition-all text-sm flex items-center gap-2">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
