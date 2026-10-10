@extends('mobile.layouts.app')

@section('title', 'Buat Postingan 3D - Pembda Space')

@section('content')
<div class="space-y-4">
    <!-- Back Link -->
    <a href="{{ route('mobile.space.index') }}" class="inline-flex items-center gap-1.5 text-xs font-black text-slate-500 hover:text-slate-900 transition">
        <i class="fa-solid fa-arrow-left"></i> Batal
    </a>

    <!-- Form Clay Card -->
    <div class="clay-card p-5 space-y-4">
        <h2 class="text-base font-black text-slate-900 mb-1">Buat Topik Diskusi Baru 💬</h2>
        <p class="text-xs text-slate-500 font-bold mb-4">Bagikan pertanyaan, ide, atau info kepada komunitas PembdaHUB.</p>

        @if(session('error'))
            <div class="mb-4 p-3.5 rounded-2xl bg-rose-500 text-white text-xs font-black shadow-md border-2 border-white flex items-center justify-between">
                <div>
                    <i class="fa-solid fa-circle-exclamation mr-1"></i>
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-4 p-3.5 rounded-2xl bg-emerald-500 text-white text-xs font-black shadow-md border-2 border-white flex items-center justify-between">
                <div>
                    <i class="fa-solid fa-circle-check mr-1"></i>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3.5 rounded-2xl bg-rose-500 text-white text-xs font-black shadow-md border-2 border-white">
                <i class="fa-solid fa-circle-exclamation mr-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('mobile.space.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4" x-data="{ imagePreview: null }">
            @csrf

            <!-- Channel Selection -->
            <div>
                <label for="category" class="block text-xs font-black text-slate-800 mb-1.5">Pilih Saluran (Channel)</label>
                <select id="category" name="category" required
                        class="w-full px-4 py-3 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold focus:outline-none focus:border-blue-500 transition">
                    @foreach(\App\Models\ForumThread::CATEGORIES as $key => $label)
                        @if($key === 'info' && !(auth()->user()->isSuperAdmin() || auth()->user()->isAdminSekolah() || auth()->user()->isGuru()))
                            @continue
                        @endif
                        <option value="{{ $key }}" {{ old('category', 'diskusi') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Title -->
            <div>
                <label for="title" class="block text-xs font-black text-slate-800 mb-1.5">Judul Topik <span class="text-[10px] text-slate-400 font-bold">(Opsional)</span></label>
                <input type="text" id="title" name="title" value="{{ old('title') }}"
                       placeholder="Tulis judul (opsional, otomatis dibuat jika kosong)..." 
                       class="w-full px-4 py-3 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold placeholder-slate-400 focus:outline-none focus:border-blue-500 transition">
            </div>

            <!-- Content -->
            <div>
                <label for="content" class="block text-xs font-black text-slate-800 mb-1.5">Isi Postingan <span class="text-rose-600">*</span></label>
                <textarea id="content" name="content" rows="4" required minlength="3"
                          placeholder="Jelaskan topik diskusi atau info Anda di sini..." 
                          class="w-full p-4 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold placeholder-slate-400 focus:outline-none focus:border-blue-500 transition resize-none">{{ old('content') }}</textarea>
            </div>

            <!-- Image Upload & Preview -->
            <div class="space-y-2">
                <label class="block text-xs font-black text-slate-800">📷 Sisipkan Foto / Gambar (Opsional)</label>
                <div class="flex items-center gap-3">
                    <label class="px-4 py-2.5 rounded-2xl bg-purple-50 border-2 border-purple-200 text-purple-700 text-xs font-black cursor-pointer hover:bg-purple-100 transition flex items-center gap-2">
                        <i class="fa-solid fa-camera"></i> Pilih Foto
                        <input type="file" name="image" accept="image/*" class="hidden" 
                               @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => imagePreview = e.target.result; reader.readAsDataURL(file); }">
                    </label>
                    <span class="text-[10px] text-slate-400 font-bold">Maks. 10 MB (JPG, PNG, WebP)</span>
                </div>
                
                <template x-if="imagePreview">
                    <div class="relative w-32 h-32 rounded-2xl overflow-hidden border-2 border-purple-400 shadow-md">
                        <img :src="imagePreview" class="w-full h-full object-cover">
                        <button type="button" @click="imagePreview = null" class="absolute top-1 right-1 w-6 h-6 rounded-full bg-rose-600 text-white font-black text-xs flex items-center justify-center shadow-md">✕</button>
                    </div>
                </template>
            </div>

            <!-- Submit -->
            <button type="submit" class="clay-btn w-full py-3.5 text-white font-black text-xs">
                <i class="fa-solid fa-paper-plane mr-1.5"></i> Publikasikan Postingan
            </button>
        </form>
    </div>
</div>
@endsection
