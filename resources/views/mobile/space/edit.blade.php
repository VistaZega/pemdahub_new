@extends('mobile.layouts.app')

@section('title', 'Edit Postingan - Pembda Space')

@section('content')
<div class="space-y-4 font-['Plus_Jakarta_Sans',sans-serif]">
    <!-- Back Link -->
    <a href="{{ route('mobile.space.show', $thread->id) }}" class="inline-flex items-center gap-1.5 text-xs font-mono font-black text-[#121316] hover:underline transition">
        <i class="fa-solid fa-arrow-left"></i> Batal
    </a>

    <!-- Form Neo-Brutalist Card -->
    <div class="p-5 space-y-4 bg-white border-2 border-[#121316] rounded-2xl shadow-[4px_4px_0px_#121316]">
        <div class="flex items-center justify-between border-b-2 border-[#121316]/10 pb-3">
            <div>
                <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#fde047] border border-[#121316] text-[10px] font-mono font-black mb-1">
                    <span>✱ EDIT TOPIK</span>
                </div>
                <h2 class="text-base font-black text-[#121316]">Edit Postingan</h2>
            </div>
            @if($thread->group)
                <span class="px-2.5 py-0.5 rounded-full bg-[#faf8f5] text-[#121316] text-[10px] font-mono font-black border border-[#121316]">
                    {{ $thread->group->name }}
                </span>
            @endif
        </div>

        @if($errors->any())
            <div class="p-3.5 rounded-xl bg-rose-50 border-2 border-rose-600 text-rose-900 text-xs font-black shadow-[2px_2px_0px_#e11d48]">
                <i class="fa-solid fa-circle-exclamation mr-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('mobile.space.update', $thread->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div>
                <label for="title" class="block text-xs font-mono font-black text-[#121316] uppercase tracking-wider mb-1.5">JUDUL TOPIK</label>
                <input type="text" id="title" name="title" value="{{ old('title', $thread->title) }}" required
                       placeholder="Tulis judul yang jelas & singkat..." 
                       class="w-full px-3.5 py-2.5 bg-[#faf8f5] border-2 border-[#121316] rounded-xl text-[#121316] text-xs font-bold placeholder-[#777] focus:outline-none focus:shadow-[2px_2px_0px_#ff3823] transition">
            </div>

            <!-- Content -->
            <div>
                <label for="content" class="block text-xs font-mono font-black text-[#121316] uppercase tracking-wider mb-1.5">ISI POSTINGAN</label>
                <textarea id="content" name="content" rows="6" required
                          placeholder="Jelaskan topik diskusi Anda..." 
                          class="w-full p-3 bg-[#faf8f5] border-2 border-[#121316] rounded-xl text-[#121316] text-xs font-bold placeholder-[#777] focus:outline-none focus:shadow-[2px_2px_0px_#ff3823] transition leading-relaxed">{{ old('content', $thread->content) }}</textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('mobile.space.show', $thread->id) }}" class="px-4 py-2.5 rounded-xl bg-white border-2 border-[#121316] text-[#121316] font-black text-xs hover:bg-[#faf8f5] transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 bg-[#fde047] text-[#121316] border-2 border-[#121316] rounded-xl font-black text-xs shadow-[2px_2px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
