@extends('mobile.layouts.app')

@section('title', 'Pembda Space - Mobile')

@section('content')
<div class="space-y-4">
    <!-- Header Title & Create Button -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-extrabold text-white">Pembda Space</h2>
            <p class="text-[11px] text-slate-400">Forum Diskusi & Kolaborasi Siswa</p>
        </div>
        <a href="{{ route('mobile.space.create') }}" 
           class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 flex items-center gap-1.5 hover:from-indigo-500 hover:to-indigo-400 transition">
            <i class="fa-solid fa-plus"></i> Post Baru
        </a>
    </div>

    <!-- Search Input -->
    <form action="{{ route('mobile.space.index') }}" method="GET" class="relative">
        @if($channel)<input type="hidden" name="channel" value="{{ $channel }}">@endif
        <div class="relative">
            <input type="text" name="search" value="{{ $search ?? '' }}" 
                   placeholder="Cari topik diskusi..." 
                   class="w-full pl-9 pr-4 py-2.5 bg-slate-900/90 border border-slate-800 rounded-xl text-white text-xs placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-500 text-xs"></i>
        </div>
    </form>

    <!-- Channel Filter Pills (Horizontal Scrollable) -->
    <div class="flex items-center space-x-2 overflow-x-auto pb-1 no-scrollbar">
        <a href="{{ route('mobile.space.index') }}" 
           class="px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition {{ !$channel ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-slate-900 text-slate-400 border border-slate-800 hover:text-white' }}">
            🔥 Semua
        </a>
        <a href="{{ route('mobile.space.index', ['channel' => 'diskusi']) }}" 
           class="px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition {{ $channel === 'diskusi' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-slate-900 text-slate-400 border border-slate-800 hover:text-white' }}">
            💬 Obrolan
        </a>
        <a href="{{ route('mobile.space.index', ['channel' => 'tanya_jawab']) }}" 
           class="px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition {{ $channel === 'tanya_jawab' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-slate-900 text-slate-400 border border-slate-800 hover:text-white' }}">
            📚 Akademik
        </a>
        <a href="{{ route('mobile.space.index', ['channel' => 'project_idea']) }}" 
           class="px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition {{ $channel === 'project_idea' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-slate-900 text-slate-400 border border-slate-800 hover:text-white' }}">
            🤝 Kolaborasi
        </a>
        <a href="{{ route('mobile.space.index', ['channel' => 'gaming']) }}" 
           class="px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition {{ $channel === 'gaming' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-slate-900 text-slate-400 border border-slate-800 hover:text-white' }}">
            🎮 Hangout
        </a>
    </div>

    <!-- Threads Feed -->
    <div class="space-y-3">
        @forelse($threads as $thread)
            <div class="glass-card rounded-2xl p-4 transition hover:border-indigo-500/40 relative">
                @if($thread->is_pinned)
                    <div class="absolute top-3 right-3 text-amber-400 text-xs flex items-center gap-1 font-bold">
                        <i class="fa-solid fa-thumbtack"></i> Pinned
                    </div>
                @endif

                <div class="flex items-center space-x-2.5 mb-2.5">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold text-xs">
                        {{ strtoupper(substr($thread->user->name ?? 'A', 0, 1)) }}
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-white leading-none">{{ $thread->user->name ?? 'Pengguna' }}</h4>
                        <span class="text-[10px] text-slate-400">{{ $thread->created_at ? $thread->created_at->diffForHumans() : '' }}</span>
                    </div>
                </div>

                <a href="{{ route('mobile.space.show', $thread->id) }}" class="block">
                    <h3 class="text-sm font-extrabold text-white mb-1 leading-snug hover:text-indigo-300 transition">{{ $thread->title }}</h3>
                    <p class="text-xs text-slate-300 line-clamp-2 leading-relaxed mb-3">{{ strip_tags($thread->content) }}</p>
                </a>

                <!-- Footer Actions -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-800/80 text-xs text-slate-400">
                    <span class="px-2 py-0.5 rounded-md bg-slate-800 text-[10px] text-slate-300 font-medium">
                        #{{ $thread->channel_group ?? 'diskusi' }}
                    </span>

                    <div class="flex items-center space-x-4">
                        <form action="{{ route('mobile.space.like', $thread->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="flex items-center space-x-1 hover:text-rose-400 transition">
                                <i class="fa-regular fa-heart text-sm"></i>
                                <span class="text-xs">{{ $thread->likes_count ?? 0 }}</span>
                            </button>
                        </form>

                        <a href="{{ route('mobile.space.show', $thread->id) }}" class="flex items-center space-x-1 hover:text-indigo-400 transition">
                            <i class="fa-regular fa-comment text-sm"></i>
                            <span class="text-xs">{{ $thread->replies_count ?? 0 }}</span>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="glass-card rounded-2xl p-8 text-center text-slate-500">
                <i class="fa-solid fa-comments text-3xl mb-2 text-slate-600"></i>
                <p class="text-xs font-semibold">Belum ada diskusi di topik ini.</p>
                <a href="{{ route('mobile.space.create') }}" class="inline-block mt-3 px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl shadow-md">Mulai Diskusi Baru</a>
            </div>
        @endforelse

        <div class="pt-2">
            {{ $threads->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
