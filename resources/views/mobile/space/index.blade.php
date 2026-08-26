@extends('mobile.layouts.app')

@section('title', 'Pembda Space - Class Squads & Wall Mobile')

@section('content')
<div class="space-y-4">
    <!-- Header Title & Create Button -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-900">Pembda Space 💬</h2>
            <p class="text-[11px] text-slate-500 font-bold">Class Squads & Wall Komunitas Sekolah</p>
        </div>
        <a href="{{ route('mobile.space.create') }}" 
           class="clay-btn px-4 py-2.5 text-white text-xs font-black flex items-center gap-1.5 shadow-md">
            <i class="fa-solid fa-plus text-xs"></i> Post Baru
        </a>
    </div>

    <!-- LIVE ACTIVITY TICKER BANNER -->
    <div class="clay-card p-2.5 bg-gradient-to-r from-purple-950 via-indigo-900 to-purple-900 text-white rounded-2xl shadow-md border-2 border-purple-400/40 overflow-hidden relative">
        <div class="flex items-center space-x-2 text-[10px] font-black">
            <span class="px-2 py-0.5 rounded-lg bg-rose-500 text-white uppercase text-[8px] font-black tracking-wider shrink-0 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span> LIVE TICKER
            </span>
            <marquee class="font-bold text-purple-100 min-w-0" scrollamount="4">
                🔥 ⚡ Rombel X-IPA 1 Aktif Berdiskusi! &nbsp;&bull;&nbsp; 🎉 Modul Baru & Kartu LMS Berhasil Dibagikan di Class Squads! &nbsp;&bull;&nbsp; 🏆 Ahmad Meraih Poin Reputasi Bintang Minggu Ini! &nbsp;&bull;&nbsp; 🔴 1-Click Virtual Room Siap Digunakan!
            </marquee>
        </div>
    </div>

    <!-- MAIN TOP NAVIGATION TABS (CLASS SQUADS vs WALL) -->
    <div class="grid grid-cols-2 gap-2 bg-slate-100 p-1.5 rounded-2xl border border-slate-200">
        <a href="{{ route('mobile.space.index', ['tab' => 'groups']) }}" 
           class="py-2.5 text-center text-xs font-black rounded-xl transition flex items-center justify-center gap-1.5 {{ $tab === 'groups' ? 'bg-white text-purple-700 shadow-md border-2 border-purple-200 scale-102' : 'text-slate-500 hover:text-slate-900' }}">
            <span>👥 Class Squads</span>
            <span class="px-1.5 py-0.5 rounded-full bg-purple-100 text-purple-900 text-[10px] font-black">{{ count($groups) }}</span>
        </a>
        <a href="{{ route('mobile.space.index', ['tab' => 'kanal']) }}" 
           class="py-2.5 text-center text-xs font-black rounded-xl transition flex items-center justify-center gap-1.5 {{ $tab === 'kanal' ? 'bg-white text-purple-700 shadow-md border-2 border-purple-200 scale-102' : 'text-slate-500 hover:text-slate-900' }}">
            <span>🧱 Wall</span>
            <span class="px-1.5 py-0.5 rounded-full bg-purple-100 text-purple-900 text-[10px] font-black">{{ count($categories) }}</span>
        </a>
    </div>

    <!-- TAB 1: CLASS SQUADS -->
    @if($tab === 'groups')
        <!-- Search Input -->
        <form action="{{ route('mobile.space.index') }}" method="GET" class="relative">
            <input type="hidden" name="tab" value="groups">
            @if($groupFilter)<input type="hidden" name="filter" value="{{ $groupFilter }}">@endif
            <div class="relative">
                <input type="text" name="search" value="{{ $search ?? '' }}" 
                       placeholder="Cari nama Class Squad..." 
                       class="w-full pl-10 pr-4 py-3 bg-white border-2 border-slate-200/90 rounded-2xl text-slate-900 text-xs placeholder-slate-400 font-bold focus:outline-none focus:border-purple-600 shadow-sm transition">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400 text-xs"></i>
            </div>
        </form>

        <!-- Squad Filter Pills -->
        <div class="flex items-center space-x-2 overflow-x-auto pb-1 no-scrollbar">
            <a href="{{ route('mobile.space.index', ['tab' => 'groups', 'filter' => 'semua']) }}" 
               class="px-4 py-2 rounded-2xl text-xs font-black whitespace-nowrap transition {{ $groupFilter === 'semua' ? 'clay-purple text-white shadow-md scale-105' : 'bg-white text-slate-600 border-2 border-slate-200 hover:text-slate-900' }}">
                🔥 Semua Squad
            </a>
            <a href="{{ route('mobile.space.index', ['tab' => 'groups', 'filter' => 'kelas']) }}" 
               class="px-4 py-2 rounded-2xl text-xs font-black whitespace-nowrap transition {{ $groupFilter === 'kelas' ? 'clay-purple text-white shadow-md scale-105' : 'bg-white text-slate-600 border-2 border-slate-200 hover:text-slate-900' }}">
                🏫 Class Squads Saya
            </a>
            <a href="{{ route('mobile.space.index', ['tab' => 'groups', 'filter' => 'lobi']) }}" 
               class="px-4 py-2 rounded-2xl text-xs font-black whitespace-nowrap transition {{ $groupFilter === 'lobi' ? 'clay-purple text-white shadow-md scale-105' : 'bg-white text-slate-600 border-2 border-slate-200 hover:text-slate-900' }}">
                📢 Lobi & Pengumuman
            </a>
        </div>

        <!-- CLASS SQUADS LIST FEED -->
        <div class="space-y-3">
            <h3 class="text-xs font-black text-slate-500 uppercase tracking-wider px-1">Daftar Class Squads Aktif</h3>

            @forelse($groups as $grp)
                @php
                    $latest = $grp->latestThread;
                    $memberCount = $grp->calculated_member_count ?? count($grp->members ?? []);
                @endphp
                <a href="{{ route('mobile.space.group.show', $grp->id) }}" 
                   class="clay-card p-4.5 block transition relative hover:border-purple-300 space-y-2.5 bg-white border-2 border-slate-200">
                    
                    <div class="flex items-center space-x-3">
                        <!-- Squad Icon Avatar -->
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center text-white text-xl font-black shadow-md border-2 border-white shrink-0">
                            {{ $grp->icon ?? '💬' }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-black text-slate-900 leading-snug truncate">{{ $grp->name }}</h4>
                                <span class="text-[9px] text-slate-400 font-bold shrink-0">
                                    {{ $latest?->created_at ? $latest->created_at->diffForHumans() : $grp->created_at->diffForHumans() }}
                                </span>
                            </div>

                            <p class="text-[11px] text-slate-600 truncate font-medium mt-0.5">
                                @if($latest)
                                    <strong class="text-purple-700">{{ $latest->user->name ?? 'User' }}:</strong> {{ strip_tags($latest->content) }}
                                @else
                                    <span class="italic text-slate-400">Belum ada obrolan baru</span>
                                @endif
                            </p>

                            <div class="flex items-center space-x-2 text-[9px] font-bold text-slate-400 mt-1">
                                <span class="px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 border border-purple-200 font-black">
                                    👥 {{ $memberCount }} Anggota Squad
                                </span>
                                @if($grp->only_admin_can_post)
                                    <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200 font-black">
                                        📢 Pengumuman Guru
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                    <div class="text-3xl">👥</div>
                    <p>Belum ada Class Squad terdaftar saat ini.</p>
                </div>
            @endforelse
        </div>
    @endif

    <!-- TAB 2: WALL (12+ CATEGORIES & THREADS FEED) -->
    @if($tab === 'kanal')
        <!-- Search Input -->
        <form action="{{ route('mobile.space.index') }}" method="GET" class="relative">
            <input type="hidden" name="tab" value="kanal">
            @if($category)<input type="hidden" name="category" value="{{ $category }}">@endif
            <div class="relative">
                <input type="text" name="search" value="{{ $search ?? '' }}" 
                       placeholder="Cari ide atau postingan di Wall..." 
                       class="w-full pl-10 pr-4 py-3 bg-white border-2 border-slate-200/90 rounded-2xl text-slate-900 text-xs placeholder-slate-400 font-bold focus:outline-none focus:border-purple-600 shadow-sm transition">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400 text-xs"></i>
            </div>
        </form>

        <!-- Category Pills (All Wall Categories) -->
        <div class="flex items-center space-x-2 overflow-x-auto pb-1 no-scrollbar">
            <a href="{{ route('mobile.space.index', ['tab' => 'kanal']) }}" 
               class="px-3.5 py-2 rounded-2xl text-xs font-black whitespace-nowrap transition {{ !$category ? 'clay-purple text-white shadow-md scale-105' : 'bg-white text-slate-600 border-2 border-slate-200 hover:text-slate-900' }}">
                🧱 Semua Wall
            </a>
            @foreach($categories as $key => $label)
                <a href="{{ route('mobile.space.index', ['tab' => 'kanal', 'category' => $key]) }}" 
                   class="px-3.5 py-2 rounded-2xl text-xs font-black whitespace-nowrap transition {{ $category === $key ? 'clay-purple text-white shadow-md scale-105' : 'bg-white text-slate-600 border-2 border-slate-200 hover:text-slate-900' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <!-- PUBLIC THREADS WALL FEED -->
        <div class="space-y-3">
            @forelse($threads as $thread)
                @php
                    $authorUser = $thread->user ?? null;
                    $authorPhoto = $authorUser?->avatar_url ?? null;
                    if (!$authorPhoto || str_contains($authorPhoto, 'default-avatar') || str_contains($authorPhoto, 'default-student.jpg')) {
                        if ($authorUser?->student?->photo_url) {
                            $authorPhoto = $authorUser->student->photo_url;
                        } elseif ($authorUser?->teacher?->photo_url) {
                            $authorPhoto = $authorUser->teacher->photo_url;
                        } else {
                            $authorPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($authorUser?->name ?? 'User') . '&background=7c3aed&color=fff&bold=true';
                        }
                    }
                @endphp
                <a href="{{ route('mobile.space.show', $thread->id) }}" 
                   class="clay-card p-4.5 block transition relative hover:border-purple-300 space-y-2 bg-white border-2 border-slate-200">
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2.5 min-w-0">
                            <img src="{{ $authorPhoto }}" alt="{{ $authorUser?->name }}"
                                 onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($authorUser?->name ?? 'User') }}&background=7c3aed&color=fff&bold=true';"
                                 class="w-9 h-9 rounded-2xl object-cover border border-purple-200 shadow-xs shrink-0">
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <h4 class="text-xs font-black text-slate-900 leading-none truncate max-w-[140px]">{{ $authorUser?->name ?? 'Pengguna' }}</h4>
                                    @if($authorUser?->ekskul_flair)
                                    <span class="text-[8px] font-black px-1.5 py-0.2 rounded-full border {{ $authorUser->ekskul_flair['badge_css'] }}" title="{{ $authorUser->ekskul_flair['label'] }}">
                                        {{ $authorUser->ekskul_flair['short_label'] }}
                                    </span>
                                    @endif
                                </div>
                                <span class="text-[9px] text-slate-400 font-bold block mt-0.5">{{ $thread->created_at ? $thread->created_at->diffForHumans() : '' }}</span>
                            </div>
                        </div>

                        <span class="px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 border border-purple-200 text-[9px] font-black uppercase">
                            {{ \App\Models\ForumThread::CATEGORIES[$thread->category] ?? '💬 Diskusi' }}
                        </span>
                    </div>

                    <h3 class="text-xs font-black text-slate-900 leading-snug pt-1">{{ $thread->title }}</h3>
                    <p class="text-[11px] text-slate-600 line-clamp-2 leading-relaxed font-medium">
                        {{ strip_tags($thread->content) }}
                    </p>

                    @if($thread->image_path)
                        <div class="h-32 w-full rounded-2xl overflow-hidden border border-purple-200">
                            <img src="{{ asset('storage/' . $thread->image_path) }}" alt="{{ $thread->title }}" class="w-full h-full object-cover">
                        </div>
                    @endif

                    <div class="flex items-center space-x-4 pt-1.5 text-[10px] font-black text-slate-400 border-t border-slate-100">
                        <span class="flex items-center gap-1 text-purple-600"><i class="fa-regular fa-comment"></i> {{ $thread->replies_count ?? 0 }} Komentar</span>
                        <span class="flex items-center gap-1 text-rose-600"><i class="fa-regular fa-heart"></i> {{ $thread->likes_count ?? 0 }} Suka</span>
                    </div>
                </a>
            @empty
                <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                    <div class="text-3xl">🧱</div>
                    <p>Belum ada postingan di Wall ini.</p>
                </div>
            @endforelse

            <div class="pt-2">
                {{ $threads->appends(request()->query())->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
