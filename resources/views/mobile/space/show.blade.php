@extends('mobile.layouts.app')

@section('title', $thread->title . ' - Pembda Space')

@section('content')
<div class="space-y-4">
    <!-- Back Button -->
    <a href="{{ route('mobile.space.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 hover:text-white transition">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Space
    </a>

    <!-- Main Thread Card -->
    <div class="glass-card rounded-2xl p-4 space-y-3">
        <!-- Author Info -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold text-sm shadow">
                    {{ strtoupper(substr($thread->user->name ?? 'A', 0, 1)) }}
                </div>
                <div>
                    <h4 class="text-xs font-bold text-white">{{ $thread->user->name ?? 'Pengguna' }}</h4>
                    <span class="text-[10px] text-slate-400">{{ $thread->created_at ? $thread->created_at->format('d M Y, H:i') : '' }}</span>
                </div>
            </div>
            <span class="px-2 py-0.5 rounded-md bg-indigo-500/20 text-indigo-300 text-[10px] font-bold border border-indigo-500/30">
                #{{ $thread->category_label ?? $thread->category ?? 'diskusi' }}
            </span>
        </div>

        <!-- Thread Title & Body -->
        <h2 class="text-base font-extrabold text-white leading-snug">{{ $thread->title }}</h2>
        <div class="text-xs text-slate-200 space-y-2 leading-relaxed whitespace-pre-line">
            {!! nl2br(e($thread->content)) !!}
        </div>

        <!-- Interaction Bar -->
        <div class="flex items-center justify-between pt-3 border-t border-slate-800/80 text-xs text-slate-400">
            <div class="flex items-center space-x-4">
                <form action="{{ route('mobile.space.like', $thread->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="flex items-center space-x-1.5 {{ $isLiked ? 'text-rose-400 font-bold' : 'text-slate-400 hover:text-rose-400' }} transition">
                        <i class="{{ $isLiked ? 'fa-solid fa-heart' : 'fa-regular fa-heart' }} text-sm"></i>
                        <span>{{ $thread->likes_count ?? 0 }} Suka</span>
                    </button>
                </form>

                <span class="flex items-center space-x-1 text-slate-400">
                    <i class="fa-regular fa-comment text-sm"></i>
                    <span>{{ count($thread->replies) }} Komentar</span>
                </span>
            </div>

            <span class="text-[10px] text-slate-500"><i class="fa-regular fa-eye mr-1"></i>{{ $thread->views_count ?? 0 }} views</span>
        </div>
    </div>

    <!-- Reply Input Box -->
    <div class="glass-card rounded-2xl p-3.5">
        <h4 class="text-xs font-bold text-white mb-2">Tulis Komentar</h4>
        <form action="{{ route('mobile.space.reply', $thread->id) }}" method="POST" class="space-y-2.5">
            @csrf
            <textarea name="content" rows="3" required
                      placeholder="Tulis tanggapan Anda..." 
                      class="w-full p-3 bg-slate-900/90 border border-slate-800 rounded-xl text-white text-xs placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition resize-none"></textarea>
            
            <div class="flex justify-end">
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white font-bold text-xs rounded-xl shadow-md shadow-indigo-600/30 hover:bg-indigo-500 transition">
                    Kirim Komentar
                </button>
            </div>
        </form>
    </div>

    <!-- Replies List -->
    <div class="space-y-2.5">
        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider px-1">Komentar ({{ count($thread->replies) }})</h4>

        @forelse($thread->replies as $reply)
            <div class="glass-card rounded-2xl p-3.5 space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-7 h-7 rounded-full bg-slate-700 flex items-center justify-center text-white font-bold text-[10px]">
                            {{ strtoupper(substr($reply->user->name ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <h5 class="text-xs font-bold text-white leading-none">{{ $reply->user->name ?? 'Pengguna' }}</h5>
                            <span class="text-[9px] text-slate-500">{{ $reply->created_at ? $reply->created_at->diffForHumans() : '' }}</span>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-slate-300 leading-relaxed pl-9">{!! nl2br(e($reply->content)) !!}</p>
            </div>
        @empty
            <div class="glass-card rounded-2xl p-6 text-center text-slate-500 text-xs">
                Belum ada komentar. Jadi yang pertama menanggapi!
            </div>
        @endforelse
    </div>
</div>
@endsection
