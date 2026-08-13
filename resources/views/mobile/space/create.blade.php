@extends('mobile.layouts.app')

@section('title', 'Buat Postingan Baru - Pembda Space')

@section('content')
<div class="space-y-4">
    <!-- Back Link -->
    <a href="{{ route('mobile.space.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 hover:text-white transition">
        <i class="fa-solid fa-arrow-left"></i> Batal
    </a>

    <!-- Form Container -->
    <div class="glass-card rounded-2xl p-4">
        <h2 class="text-base font-extrabold text-white mb-1">Buat Topik Diskusi Baru</h2>
        <p class="text-xs text-slate-400 mb-4">Bagikan pertanyaan, ide, atau info kepada komunitas PembdaHUB.</p>

        @if($errors->any())
            <div class="mb-4 p-3 rounded-xl bg-rose-500/20 border border-rose-500/30 text-rose-300 text-xs font-medium">
                <i class="fa-solid fa-circle-exclamation mr-1 text-rose-400"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('mobile.space.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Channel Selection -->
            <div>
                <label for="channel_group" class="block text-xs font-semibold text-slate-300 mb-1.5">Pilih Saluran (Channel)</label>
                <select id="channel_group" name="channel_group" required
                        class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-white text-xs focus:outline-none focus:border-indigo-500 transition">
                    <option value="diskusi">💬 Obrolan Umum</option>
                    <option value="tanya_jawab">📚 Tanya Jawab Akademik</option>
                    <option value="project_idea">🤝 Ide Proyek & Kolaborasi</option>
                    <option value="gaming">🎮 Gaming & Hangout</option>
                    <option value="art_gallery">🎨 Galeri Karya & Bakat</option>
                </select>
            </div>

            <!-- Title -->
            <div>
                <label for="title" class="block text-xs font-semibold text-slate-300 mb-1.5">Judul Topik</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required
                       placeholder="Tulis judul yang jelas & singkat..." 
                       class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-white text-xs placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition">
            </div>

            <!-- Content -->
            <div>
                <label for="content" class="block text-xs font-semibold text-slate-300 mb-1.5">Isi Postingan</label>
                <textarea id="content" name="content" rows="6" required
                          placeholder="Jelaskan topik diskusi Anda secara detail..." 
                          class="w-full p-3.5 bg-slate-900 border border-slate-800 rounded-xl text-white text-xs placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition resize-none">{{ old('content') }}</textarea>
            </div>

            <!-- Submit -->
            <button type="submit" 
                    class="w-full py-3 bg-gradient-to-r from-indigo-600 to-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/30 hover:from-indigo-500 hover:to-indigo-400 transition">
                <i class="fa-solid fa-paper-plane mr-1.5"></i> Publikasikan Postingan
            </button>
        </form>
    </div>
</div>
@endsection
