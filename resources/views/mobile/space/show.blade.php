@extends('mobile.layouts.app')

@section('title', $thread->title . ' - Pembda Space 3D')

@section('content')
<div class="space-y-4">
    <!-- Back Button -->
    <a href="{{ route('mobile.space.index') }}" class="inline-flex items-center gap-1.5 text-xs font-black text-slate-500 hover:text-slate-900 transition">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Space
    </a>

    <!-- Main Thread Card (Clay Card) -->
    <div class="clay-card p-5 space-y-3">
        <!-- Author Info -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-purple-500 to-indigo-500 flex items-center justify-center text-white font-black text-sm shadow-md border border-white">
                    {{ strtoupper(substr($thread->user->name ?? 'A', 0, 1)) }}
                </div>
                <div>
                    <h4 class="text-xs font-black text-slate-900 leading-none">{{ $thread->user->name ?? 'Pengguna' }}</h4>
                    <span class="text-[10px] text-slate-400 font-bold">{{ $thread->created_at ? $thread->created_at->format('d M Y, H:i') : '' }}</span>
                </div>
            </div>
            <span class="px-3 py-0.5 rounded-full bg-purple-100 text-purple-900 text-[10px] font-black border border-purple-200">
                #{{ $thread->category_label ?? $thread->category ?? 'diskusi' }}
            </span>
        </div>

        <!-- Thread Title & Body -->
        <h2 class="text-base font-black text-slate-900 leading-snug">{{ $thread->title }}</h2>
        <div class="text-xs text-slate-700 space-y-2 leading-relaxed whitespace-pre-line font-medium">
            {!! nl2br(e($thread->content)) !!}
        </div>

        <!-- Interaction Bar -->
        <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-xs font-black text-slate-500">
            <div class="flex items-center space-x-4">
                <form action="{{ route('mobile.space.like', $thread->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="flex items-center space-x-1.5 {{ $isLiked ? 'text-rose-600 font-black' : 'text-slate-500 hover:text-rose-600' }} transition">
                        <i class="{{ $isLiked ? 'fa-solid fa-heart' : 'fa-regular fa-heart' }} text-sm"></i>
                        <span>{{ $thread->likes_count ?? 0 }} Suka</span>
                    </button>
                </form>

                <span class="flex items-center space-x-1 text-slate-500">
                    <i class="fa-regular fa-comment text-sm"></i>
                    <span>{{ count($thread->replies) }} Komentar</span>
                </span>
            </div>

            <span class="text-[10px] text-slate-400 font-bold"><i class="fa-regular fa-eye mr-1"></i>{{ $thread->views_count ?? 0 }} views</span>
        </div>
    </div>

    <!-- Reply Input Box (Clay Card) -->
    <div class="clay-card p-4">
        <h4 class="text-xs font-black text-slate-900 mb-2.5">Tulis Komentar</h4>
        <form action="{{ route('mobile.space.reply', $thread->id) }}" method="POST" class="space-y-3">
            @csrf
            <textarea name="content" rows="3" required
                      placeholder="Tulis tanggapan Anda secara ramah..." 
                      class="w-full p-3.5 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold placeholder-slate-400 focus:outline-none focus:border-blue-500 transition resize-none"></textarea>
            
            <div class="flex justify-end">
                <button type="submit" 
                        class="clay-btn px-5 py-2.5 text-white font-black text-xs">
                    Kirim Komentar
                </button>
            </div>
        </form>
    </div>

    <!-- Replies List -->
    <div class="space-y-2.5">
        <h4 class="text-xs font-black text-slate-500 uppercase tracking-wider px-1">Komentar ({{ count($thread->replies) }})</h4>

        @forelse($thread->replies as $reply)
            <div class="clay-card p-4 space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-xl bg-slate-200 flex items-center justify-center text-slate-800 font-black text-xs border border-white">
                            {{ strtoupper(substr($reply->user->name ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <h5 class="text-xs font-black text-slate-900 leading-none">{{ $reply->user->name ?? 'Pengguna' }}</h5>
                            <span class="text-[9px] text-slate-400 font-bold">{{ $reply->created_at ? $reply->created_at->diffForHumans() : '' }}</span>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-slate-700 leading-relaxed font-medium pl-10">{!! nl2br(e($reply->content)) !!}</p>
            </div>
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                Belum ada komentar. Jadi yang pertama menanggapi!
            </div>
        @endforelse
    </div>
</div>
@endsection
