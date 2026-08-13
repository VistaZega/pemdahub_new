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

        @if($errors->any())
            <div class="mb-4 p-3.5 rounded-2xl bg-rose-500 text-white text-xs font-black shadow-md border-2 border-white">
                <i class="fa-solid fa-circle-exclamation mr-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('mobile.space.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Channel Selection -->
            <div>
                <label for="category" class="block text-xs font-black text-slate-800 mb-1.5">Pilih Saluran (Channel)</label>
                <select id="category" name="category" required
                        class="w-full px-4 py-3 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold focus:outline-none focus:border-blue-500 transition">
                    <option value="diskusi">💬 Obrolan Umum</option>
                    <option value="tanya_jawab">📚 Tanya Jawab Akademik</option>
                    <option value="project_idea">🤝 Ide Proyek & Kolaborasi</option>
                    <option value="gaming">🎮 Gaming & Hangout</option>
                    <option value="art_gallery">🎨 Galeri Karya & Bakat</option>
                </select>
            </div>

            <!-- Title -->
            <div>
                <label for="title" class="block text-xs font-black text-slate-800 mb-1.5">Judul Topik</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required
                       placeholder="Tulis judul yang jelas & singkat..." 
                       class="w-full px-4 py-3 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold placeholder-slate-400 focus:outline-none focus:border-blue-500 transition">
            </div>

            <!-- Content -->
            <div>
                <label for="content" class="block text-xs font-black text-slate-800 mb-1.5">Isi Postingan</label>
                <textarea id="content" name="content" rows="6" required
                          placeholder="Jelaskan topik diskusi Anda secara detail..." 
                          class="w-full p-4 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold placeholder-slate-400 focus:outline-none focus:border-blue-500 transition resize-none">{{ old('content') }}</textarea>
            </div>

            <!-- Submit -->
            <button type="submit" class="clay-btn w-full py-3.5 text-white font-black text-xs">
                <i class="fa-solid fa-paper-plane mr-1.5"></i> Publikasikan Postingan
            </button>
        </form>
    </div>
</div>
@endsection
