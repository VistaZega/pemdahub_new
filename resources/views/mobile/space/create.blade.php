@extends('mobile.layouts.app')

@section('title', 'Buat Postingan - Pembda Space')

@section('content')
<div class="space-y-4 font-['Plus_Jakarta_Sans',sans-serif]">
    <!-- Back Link -->
    <a href="{{ route('mobile.space.index') }}" class="inline-flex items-center gap-1.5 text-xs font-mono font-black text-[#121316] hover:underline transition">
        <i class="fa-solid fa-arrow-left"></i> Batal
    </a>

    <!-- Form Neo-Brutalist Card -->
    <div class="p-5 space-y-4 bg-white border-2 border-[#121316] rounded-2xl shadow-[4px_4px_0px_#121316]">
        <div class="border-b-2 border-[#121316]/10 pb-3">
            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#fde047] border border-[#121316] text-[10px] font-mono font-black mb-1">
                <span>✱ POSTINGAN BARU</span>
            </div>
            <h2 class="text-base font-black text-[#121316]">Buat Topik Diskusi Baru</h2>
            <p class="text-xs text-[#555] font-semibold">Bagikan pertanyaan, ide, atau info kepada komunitas PembdaHUB.</p>
        </div>

        @if(session('error'))
            <div class="p-3.5 rounded-xl bg-rose-50 border-2 border-rose-600 text-rose-900 text-xs font-black shadow-[2px_2px_0px_#e11d48]">
                <i class="fa-solid fa-circle-exclamation mr-1"></i>
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="p-3.5 rounded-xl bg-emerald-50 border-2 border-emerald-600 text-emerald-900 text-xs font-black shadow-[2px_2px_0px_#059669]">
                <i class="fa-solid fa-circle-check mr-1"></i>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-3.5 rounded-xl bg-rose-50 border-2 border-rose-600 text-rose-900 text-xs font-black shadow-[2px_2px_0px_#e11d48]">
                <i class="fa-solid fa-circle-exclamation mr-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('mobile.space.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4" x-data="{ imagePreview: null }">
            @csrf

            <!-- Channel Selection -->
            <div>
                <label for="category" class="block text-xs font-mono font-black text-[#121316] uppercase tracking-wider mb-1.5">PILIH SALURAN (CHANNEL)</label>
                <select id="category" name="category" required
                        class="w-full px-3.5 py-2.5 bg-[#faf8f5] border-2 border-[#121316] rounded-xl text-[#121316] text-xs font-bold outline-none focus:shadow-[2px_2px_0px_#ff3823] transition">
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
                <label for="title" class="block text-xs font-mono font-black text-[#121316] uppercase tracking-wider mb-1.5">JUDUL TOPIK <span class="text-[10px] text-[#666]">(Opsional)</span></label>
                <input type="text" id="title" name="title" value="{{ old('title') }}"
                       placeholder="Tulis judul (opsional, otomatis dibuat jika kosong)..." 
                       class="w-full px-3.5 py-2.5 bg-[#faf8f5] border-2 border-[#121316] rounded-xl text-[#121316] text-xs font-bold placeholder-[#777] outline-none focus:shadow-[2px_2px_0px_#ff3823] transition">
            </div>

            <!-- Content -->
            <div>
                <label for="content" class="block text-xs font-mono font-black text-[#121316] uppercase tracking-wider mb-1.5">ISI POSTINGAN <span class="text-[#ff3823]">*</span></label>
                <textarea id="content" name="content" rows="4" required minlength="3"
                          placeholder="Jelaskan topik diskusi atau info Anda di sini..." 
                          class="w-full p-3 bg-[#faf8f5] border-2 border-[#121316] rounded-xl text-[#121316] text-xs font-bold placeholder-[#777] outline-none focus:shadow-[2px_2px_0px_#ff3823] transition resize-none">{{ old('content') }}</textarea>
            </div>

            <!-- Image Upload & Preview -->
            <div class="space-y-2">
                <label class="block text-xs font-mono font-black text-[#121316] uppercase tracking-wider">📷 Sisipkan Foto (Opsional)</label>
                <div class="flex items-center gap-3">
                    <label class="px-3.5 py-2 rounded-xl bg-[#faf8f5] border-2 border-[#121316] text-[#121316] text-xs font-black cursor-pointer hover:bg-[#fde047] transition flex items-center gap-2 shadow-[2px_2px_0px_#121316]">
                        <i class="fa-solid fa-camera"></i> Pilih Foto
                        <input type="file" name="image" accept="image/*" class="hidden" 
                               @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => imagePreview = e.target.result; reader.readAsDataURL(file); }">
                    </label>
                    <span class="text-[10px] font-mono text-[#666]">Maks. 10 MB</span>
                </div>
                
                <template x-if="imagePreview">
                    <div class="relative w-28 h-28 rounded-xl overflow-hidden border-2 border-[#121316] shadow-[2px_2px_0px_#121316] mt-2">
                        <img :src="imagePreview" class="w-full h-full object-cover">
                        <button type="button" @click="imagePreview = null" class="absolute top-1 right-1 w-5 h-5 rounded-full bg-[#121316] text-white font-black text-[10px] flex items-center justify-center">✕</button>
                    </div>
                </template>
            </div>

            <!-- Submit -->
            <button type="submit" class="w-full py-3 bg-[#ff3823] text-white border-2 border-[#121316] rounded-xl font-black text-xs shadow-[3px_3px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition flex items-center justify-center gap-1.5">
                <i class="fa-solid fa-paper-plane"></i> Publikasikan Postingan
            </button>
        </form>
    </div>
</div>
@endsection