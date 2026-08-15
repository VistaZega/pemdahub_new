@extends('mobile.layouts.app')

@section('title', 'Edit Postingan - Pembda Space')

@section('content')
<div class="space-y-4">
    <!-- Back Link -->
    <a href="{{ route('mobile.space.show', $thread->id) }}" class="inline-flex items-center gap-1.5 text-xs font-black text-slate-500 hover:text-slate-900 transition">
        <i class="fa-solid fa-arrow-left"></i> Batal
    </a>

    <!-- Form Clay Card -->
    <div class="clay-card p-5 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-black text-slate-900">Edit Postingan ✏️</h2>
            @if($thread->group)
                <span class="px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800 text-[10px] font-black border border-purple-200">
                    {{ $thread->group->name }}
                </span>
            @endif
        </div>

        @if($errors->any())
            <div class="p-3.5 rounded-2xl bg-rose-500 text-white text-xs font-black shadow-md border-2 border-white">
                <i class="fa-solid fa-circle-exclamation mr-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('mobile.space.update', $thread->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div>
                <label for="title" class="block text-xs font-black text-slate-800 mb-1.5">Judul Topik</label>
                <input type="text" id="title" name="title" value="{{ old('title', $thread->title) }}" required
                       placeholder="Tulis judul yang jelas & singkat..." 
                       class="w-full px-4 py-3 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold placeholder-slate-400 focus:outline-none focus:border-purple-600 transition">
            </div>

            <!-- Content -->
            <div>
                <label for="content" class="block text-xs font-black text-slate-800 mb-1.5">Isi Postingan</label>
                <textarea id="content" name="content" rows="6" required
                          placeholder="Jelaskan topik diskusi Anda..." 
                          class="w-full p-4 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold placeholder-slate-400 focus:outline-none focus:border-purple-600 transition leading-relaxed">{{ old('content', $thread->content) }}</textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('mobile.space.show', $thread->id) }}" class="px-4 py-3 rounded-2xl bg-slate-100 text-slate-700 font-extrabold text-xs hover:bg-slate-200 transition">
                    Batal
                </a>
                <button type="submit" class="clay-btn px-6 py-3 text-white font-black text-xs flex items-center gap-2">
                    <i class="fa-solid fa-check"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
