@extends('mobile.layouts.app')

@section('title', 'Pembda Space 3D - Mobile Pro')

@section('content')
<div class="space-y-4">
    <!-- Header Title & Create Button -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-900">Pembda Space 💬</h2>
            <p class="text-[11px] text-slate-500 font-bold">Forum Diskusi & Kolaborasi Siswa</p>
        </div>
        <a href="{{ route('mobile.space.create') }}" 
           class="clay-btn px-4 py-2.5 text-white text-xs font-black flex items-center gap-1.5 shadow-md">
            <i class="fa-solid fa-plus text-xs"></i> Post Baru
        </a>
    </div>

    <!-- Search Input (Clay Search Box) -->
    <form action="{{ route('mobile.space.index') }}" method="GET" class="relative">
        @if($channel)<input type="hidden" name="channel" value="{{ $channel }}">@endif
        <div class="relative">
            <input type="text" name="search" value="{{ $search ?? '' }}" 
                   placeholder="Cari topik diskusi..." 
                   class="w-full pl-10 pr-4 py-3 bg-white border-2 border-slate-200/90 rounded-2xl text-slate-900 text-xs placeholder-slate-400 font-bold focus:outline-none focus:border-blue-500 shadow-sm transition">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400 text-xs"></i>
        </div>
    </form>

    <!-- Channel Filter Pills (Clay Pills) -->
    <div class="flex items-center space-x-2 overflow-x-auto pb-1 no-scrollbar">
        <a href="{{ route('mobile.space.index') }}" 
           class="px-4 py-2 rounded-2xl text-xs font-black whitespace-nowrap transition {{ !$channel ? 'clay-purple text-white shadow-md scale-105' : 'bg-white text-slate-600 border-2 border-slate-200 hover:text-slate-900' }}">
            🔥 Semua
        </a>
        <a href="{{ route('mobile.space.index', ['channel' => 'diskusi']) }}" 
           class="px-4 py-2 rounded-2xl text-xs font-black whitespace-nowrap transition {{ $channel === 'diskusi' ? 'clay-purple text-white shadow-md scale-105' : 'bg-white text-slate-600 border-2 border-slate-200 hover:text-slate-900' }}">
            💬 Obrolan
        </a>
        <a href="{{ route('mobile.space.index', ['channel' => 'tanya_jawab']) }}" 
           class="px-4 py-2 rounded-2xl text-xs font-black whitespace-nowrap transition {{ $channel === 'tanya_jawab' ? 'clay-purple text-white shadow-md scale-105' : 'bg-white text-slate-600 border-2 border-slate-200 hover:text-slate-900' }}">
            📚 Akademik
        </a>
        <a href="{{ route('mobile.space.index', ['channel' => 'project_idea']) }}" 
           class="px-4 py-2 rounded-2xl text-xs font-black whitespace-nowrap transition {{ $channel === 'project_idea' ? 'clay-purple text-white shadow-md scale-105' : 'bg-white text-slate-600 border-2 border-slate-200 hover:text-slate-900' }}">
            🤝 Kolaborasi
        </a>
        <a href="{{ route('mobile.space.index', ['channel' => 'gaming']) }}" 
           class="px-4 py-2 rounded-2xl text-xs font-black whitespace-nowrap transition {{ $channel === 'gaming' ? 'clay-purple text-white shadow-md scale-105' : 'bg-white text-slate-600 border-2 border-slate-200 hover:text-slate-900' }}">
            🎮 Hangout
        </a>
    </div>

    <!-- Threads Feed (Clay Cards) -->
    <div class="space-y-3">
        @forelse($threads as $thread)
            <div class="clay-card p-5 transition relative hover:border-purple-300 space-y-3">
                @if($thread->is_pinned)
                    <div class="absolute top-4 right-4 text-amber-900 text-xs flex items-center gap-1 font-black clay-yellow px-2.5 py-0.5 rounded-full border border-white">
                        <i class="fa-solid fa-thumbtack text-[10px]"></i> Pinned
                    </div>
                @endif

                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-purple-500 to-indigo-500 flex items-center justify-center text-white font-black text-xs shadow-md border-2 border-white shrink-0">
                        {{ strtoupper(substr($thread->user->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="text-xs font-black text-slate-900 leading-snug truncate">{{ $thread->user->name ?? 'Pengguna' }}</h4>
                        <span class="text-[10px] text-slate-400 font-bold block">{{ $thread->created_at ? $thread->created_at->diffForHumans() : '' }}</span>
                    </div>
                </div>

                <a href="{{ route('mobile.space.show', $thread->id) }}" class="block space-y-1">
                    <h3 class="text-sm font-black text-slate-900 leading-snug hover:text-purple-600 transition">{{ $thread->title }}</h3>
                    <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed font-semibold">{{ strip_tags($thread->content) }}</p>
                </a>

                <!-- Footer Actions -->
                <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-xs font-black text-slate-500">
                    <span class="px-3 py-1 rounded-full bg-purple-100 text-[10px] text-purple-800 font-black border border-purple-200">
                        #💬 {{ $thread->category_label ?? $thread->category ?? 'Lobi Utama' }}
                    </span>

                    <div class="flex items-center space-x-4">
                        <form action="{{ route('mobile.space.like', $thread->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="flex items-center space-x-1 hover:text-rose-600 transition text-rose-500">
                                <i class="fa-regular fa-heart text-sm"></i>
                                <span class="text-xs font-black">{{ $thread->likes_count ?? 0 }}</span>
                            </button>
                        </form>

                        <a href="{{ route('mobile.space.show', $thread->id) }}" class="flex items-center space-x-1 hover:text-purple-600 transition text-purple-500">
                            <i class="fa-regular fa-comment text-sm"></i>
                            <span class="text-xs font-black">{{ $thread->replies_count ?? 0 }}</span>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 font-bold">
                <i class="fa-solid fa-comments text-4xl mb-2 text-purple-400"></i>
                <p class="text-xs font-black text-slate-700">Belum ada diskusi di topik ini.</p>
                <a href="{{ route('mobile.space.create') }}" class="clay-btn inline-block mt-3 px-5 py-2.5 text-white text-xs font-black shadow-md">Mulai Diskusi Baru</a>
            </div>
        @endforelse

        <div class="pt-2">
            {{ $threads->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
