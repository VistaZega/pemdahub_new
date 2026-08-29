@extends('layouts.admin')

@section('title', $news->exists ? 'Edit Berita' : 'Tambah Berita')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.news.index') }}" class="p-2 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                <i class="fas fa-arrow-left text-gray-600"></i>
            </a>
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-rose-500 to-pink-600 shadow-lg">
                    <i class="fas fa-{{ $news->exists ? 'pen' : 'plus' }} text-white"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $news->exists ? 'Edit Berita' : 'Tambah Berita' }}</h1>
                    <p class="text-gray-600 mt-1">{{ $news->exists ? 'Perbarui informasi berita' : 'Buat berita baru untuk homepage' }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-lg p-4">
        <ul class="text-red-700 text-sm list-disc list-inside">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ $news->exists ? route('admin.news.update', $news) : route('admin.news.store') }}"
          method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @if($news->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-lg p-6 space-y-5">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-file-alt text-rose-500"></i> Konten Berita
                    </h3>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Judul Berita <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $news->title) }}" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                            placeholder="Masukkan judul berita...">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Ringkasan</label>
                        <input type="text" name="excerpt" value="{{ old('excerpt', $news->excerpt) }}" maxlength="500"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                            placeholder="Ringkasan singkat berita (muncul di homepage)...">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Konten Lengkap</label>
                        <textarea name="content" rows="8"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                            placeholder="Isi lengkap berita...">{{ old('content', $news->content) }}</textarea>
                    </div>
                </div>

                <!-- Image Upload -->
                <div class="bg-white rounded-2xl shadow-lg p-6 space-y-5">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-image text-rose-500"></i> Gambar
                    </h3>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Upload Gambar</label>
                        @if($news->image)
                        <div class="mb-3">
                            <img src="{{ $news->image_url }}" alt="{{ $news->title }}" class="w-40 h-28 object-cover rounded-xl border">
                            <p class="text-xs text-gray-500 mt-1">Gambar saat ini (upload baru untuk mengganti)</p>
                        </div>
                        @endif
                        <input type="file" name="image" accept="image/*"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-rose-500 focus:border-transparent file:mr-3 file:py-1 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100">
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG, WebP. Maks 10MB.</p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-lg p-6 space-y-5">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-cog text-rose-500"></i> Pengaturan
                    </h3>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori <span class="text-red-500">*</span></label>
                        <select name="category" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                            <option value="kegiatan" {{ old('category', $news->category) == 'kegiatan' ? 'selected' : '' }}>Kegiatan</option>
                            <option value="prestasi" {{ old('category', $news->category) == 'prestasi' ? 'selected' : '' }}>Prestasi</option>
                            <option value="kerjasama" {{ old('category', $news->category) == 'kerjasama' ? 'selected' : '' }}>Kerjasama</option>
                            <option value="pengumuman" {{ old('category', $news->category) == 'pengumuman' ? 'selected' : '' }}>Pengumuman</option>
                        </select>
                    </div>

                    <div>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="is_published" value="0">
                            <input type="checkbox" name="is_published" value="1"
                                {{ old('is_published', $news->is_published) ? 'checked' : '' }}
                                class="w-5 h-5 text-rose-600 border-gray-300 rounded focus:ring-rose-500">
                            <div>
                                <span class="text-sm font-semibold text-gray-700">Publikasikan</span>
                                <p class="text-xs text-gray-500">Berita akan ditampilkan di homepage</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg p-6 space-y-5">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-palette text-rose-500"></i> Tampilan
                    </h3>
                    <p class="text-xs text-gray-500">Jika tidak diisi, warna akan disesuaikan otomatis berdasarkan kategori.</p>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Icon (Font Awesome)</label>
                        <input type="text" name="icon" value="{{ old('icon', $news->icon) }}"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                            placeholder="fa-solid fa-trophy">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Gradient Dari</label>
                            <input type="color" name="gradient_from" value="{{ old('gradient_from', $news->gradient_from ?: '#2563eb') }}"
                                class="w-full h-10 rounded-lg border border-gray-200 cursor-pointer">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Gradient Ke</label>
                            <input type="color" name="gradient_to" value="{{ old('gradient_to', $news->gradient_to ?: '#60a5fa') }}"
                                class="w-full h-10 rounded-lg border border-gray-200 cursor-pointer">
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-rose-600 to-pink-600 text-white rounded-xl font-medium hover:from-rose-700 hover:to-pink-700 shadow-lg transition-all text-center">
                        <i class="fas fa-save mr-2"></i> {{ $news->exists ? 'Simpan Perubahan' : 'Simpan Berita' }}
                    </button>
                    <a href="{{ route('admin.news.index') }}" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition text-center">
                        Batal
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
