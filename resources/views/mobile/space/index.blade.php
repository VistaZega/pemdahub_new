@extends('mobile.layouts.app')

@section('title', 'Pembda Space - Class Squads & Wall Mobile')

@section('content')
<div class="space-y-4 font-['Plus_Jakarta_Sans',sans-serif]">
    <!-- Header Title & Create Button -->
    <div class="flex items-center justify-between">
        <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#fde047] border border-[#121316] text-[#121316] text-[10px] font-mono font-black mb-1">
                <span>✱ PEMBDA SPACE</span>
            </div>
            <h2 class="text-xl font-black text-[#121316] tracking-tight">Ruang Komunitas</h2>
            <p class="text-[11px] text-[#555] font-bold">Class Squads & Wall Diskusi Sekolah</p>
        </div>
        <a href="{{ route('mobile.space.create') }}" 
           class="px-4 py-2.5 bg-[#ff3823] text-white border-2 border-[#121316] rounded-xl text-xs font-black flex items-center gap-1.5 shadow-[2px_2px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition">
            <i class="fa-solid fa-plus text-xs"></i> Post Baru
        </a>
    </div>

    <!-- LIVE ACTIVITY TICKER BANNER (Neo-Brutalist) -->
    <div class="p-2.5 bg-[#121316] text-white rounded-2xl shadow-[3px_3px_0px_#121316] border-2 border-[#121316] overflow-hidden relative">
        <div class="flex items-center space-x-2 text-[10px] font-black">
            <span class="px-2 py-0.5 rounded-lg bg-[#ff3823] text-white uppercase text-[8px] font-mono font-black tracking-wider shrink-0 flex items-center gap-1 border border-[#121316]">
                <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span> LIVE
            </span>
            <marquee class="font-mono font-bold text-[#fde047] min-w-0" scrollamount="4">
                ⚡ Rombel X-IPA 1 Aktif Berdiskusi! &nbsp;&bull;&nbsp; 🎉 Modul Baru & Kartu LMS Berhasil Dibagikan di Class Squads! &nbsp;&bull;&nbsp; 🏆 Ahmad Meraih Poin Reputasi Bintang Minggu Ini! &nbsp;&bull;&nbsp; 🧱 Menara Prestasi Aktif Dibangun!
            </marquee>
        </div>
    </div>

    <!-- MAIN TOP NAVIGATION TABS (CLASS SQUADS vs WALL) -->
    <div class="grid grid-cols-2 gap-2 bg-[#faf8f5] p-1.5 rounded-2xl border-2 border-[#121316] shadow-[2px_2px_0px_#121316]">
        <a href="{{ route('mobile.space.index', ['tab' => 'groups']) }}" 
           class="py-2.5 text-center text-xs font-black rounded-xl transition flex items-center justify-center gap-1.5 {{ $tab === 'groups' ? 'bg-[#fde047] text-[#121316] border-2 border-[#121316] shadow-[2px_2px_0px_#121316]' : 'text-[#555] hover:text-[#121316]' }}">
            <span>👥 Class Squads</span>
            <span class="px-1.5 py-0.5 rounded-full bg-[#121316] text-white font-mono text-[10px] font-black">{{ count($groups) }}</span>
        </a>
        <a href="{{ route('mobile.space.index', ['tab' => 'kanal']) }}" 
           class="py-2.5 text-center text-xs font-black rounded-xl transition flex items-center justify-center gap-1.5 {{ $tab === 'kanal' ? 'bg-[#fde047] text-[#121316] border-2 border-[#121316] shadow-[2px_2px_0px_#121316]' : 'text-[#555] hover:text-[#121316]' }}">
            <span>🧱 Wall</span>
            <span class="px-1.5 py-0.5 rounded-full bg-[#121316] text-white font-mono text-[10px] font-black">{{ count($categories) }}</span>
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
                       class="w-full pl-10 pr-4 py-2.5 bg-white border-2 border-[#121316] rounded-2xl text-[#121316] text-xs placeholder-[#777] font-bold focus:outline-none focus:shadow-[2px_2px_0px_#ff3823] shadow-[2px_2px_0px_#121316] transition">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-[#121316] text-xs"></i>
            </div>
        </form>

        <!-- Squad Filter Pills -->
        <div class="flex items-center space-x-2 overflow-x-auto pb-1 no-scrollbar">
            <a href="{{ route('mobile.space.index', ['tab' => 'groups', 'filter' => 'semua']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-black whitespace-nowrap transition border-2 border-[#121316] {{ $groupFilter === 'semua' ? 'bg-[#ff3823] text-white shadow-[2px_2px_0px_#121316]' : 'bg-white text-[#121316] hover:bg-[#faf8f5]' }}">
                🔥 Semua Squad
            </a>
            <a href="{{ route('mobile.space.index', ['tab' => 'groups', 'filter' => 'kelas']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-black whitespace-nowrap transition border-2 border-[#121316] {{ $groupFilter === 'kelas' ? 'bg-[#ff3823] text-white shadow-[2px_2px_0px_#121316]' : 'bg-white text-[#121316] hover:bg-[#faf8f5]' }}">
                🏫 Class Squads Saya
            </a>
            <a href="{{ route('mobile.space.index', ['tab' => 'groups', 'filter' => 'lobi']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-black whitespace-nowrap transition border-2 border-[#121316] {{ $groupFilter === 'lobi' ? 'bg-[#ff3823] text-white shadow-[2px_2px_0px_#121316]' : 'bg-white text-[#121316] hover:bg-[#faf8f5]' }}">
                📢 Lobi & Pengumuman
            </a>
        </div>

        <!-- CLASS SQUADS LIST FEED -->
        <div class="space-y-3">
            <h3 class="text-xs font-mono font-black text-[#121316] uppercase tracking-wider px-1">Daftar Class Squads Aktif</h3>

            @forelse($groups as $grp)
                @php
                    $latest = $grp->latestThread;
                    $memberCount = $grp->calculated_member_count ?? count($grp->members ?? []);
                @endphp
                <a href="{{ route('mobile.space.group.show', $grp->id) }}" 
                   class="p-4 block transition relative space-y-2 bg-white border-2 border-[#121316] rounded-2xl shadow-[3px_3px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 active:shadow-[1px_1px_0px_#121316]">
                    
                    <div class="flex items-center space-x-3">
                        <!-- Squad Icon Avatar -->
                        <div class="w-12 h-12 rounded-2xl bg-[#fde047] flex items-center justify-center text-[#121316] text-xl font-black shadow-[1px_1px_0px_#121316] border-2 border-[#121316] shrink-0">
                            {{ $grp->icon ?? '💬' }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-black text-[#121316] leading-snug truncate">{{ $grp->name }}</h4>
                                <span class="text-[9px] font-mono text-[#666] font-bold shrink-0">
                                    {{ $latest?->created_at ? $latest->created_at->diffForHumans() : $grp->created_at->diffForHumans() }}
                                </span>
                            </div>

                            <p class="text-[11px] text-[#444] truncate font-medium mt-0.5">
                                @if($latest)
                                    <strong class="text-[#2563eb]">{{ $latest->user->name ?? 'User' }}:</strong> {{ strip_tags($latest->content) }}
                                @else
                                    <span class="italic text-[#777]">Belum ada obrolan baru</span>
                                @endif
                            </p>

                            <div class="flex items-center space-x-2 text-[9px] font-mono font-bold text-[#121316] mt-1">
                                <span class="px-2 py-0.5 rounded-full bg-[#faf8f5] text-[#121316] border border-[#121316] font-black">
                                    👥 {{ $memberCount }} Anggota
                                </span>
                                @if($grp->only_admin_can_post)
                                    <span class="px-2 py-0.5 rounded-full bg-[#fde047] text-[#121316] border border-[#121316] font-black">
                                        📢 Pengumuman Guru
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="p-8 text-center text-[#666] text-xs font-bold space-y-2 bg-white border-2 border-[#121316] rounded-2xl shadow-[3px_3px_0px_#121316]">
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
                       class="w-full pl-10 pr-4 py-2.5 bg-white border-2 border-[#121316] rounded-2xl text-[#121316] text-xs placeholder-[#777] font-bold focus:outline-none focus:shadow-[2px_2px_0px_#ff3823] shadow-[2px_2px_0px_#121316] transition">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-[#121316] text-xs"></i>
            </div>
        </form>

        <!-- Category Pills (All Wall Categories) -->
        <div class="flex items-center space-x-2 overflow-x-auto pb-1 no-scrollbar">
            <a href="{{ route('mobile.space.index', ['tab' => 'kanal']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-black whitespace-nowrap transition border-2 border-[#121316] {{ !$category ? 'bg-[#ff3823] text-white shadow-[2px_2px_0px_#121316]' : 'bg-white text-[#121316] hover:bg-[#faf8f5]' }}">
                🧱 Semua Wall
            </a>
            @foreach($categories as $key => $label)
                <a href="{{ route('mobile.space.index', ['tab' => 'kanal', 'category' => $key]) }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-black whitespace-nowrap transition border-2 border-[#121316] {{ $category === $key ? 'bg-[#ff3823] text-white shadow-[2px_2px_0px_#121316]' : 'bg-white text-[#121316] hover:bg-[#faf8f5]' }}">
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
                            $authorPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($authorUser?->name ?? 'User') . '&background=121316&color=fff&bold=true';
                        }
                    }
                @endphp
                <a href="{{ route('mobile.space.show', $thread->id) }}" 
                   class="p-4 block transition relative space-y-2 bg-white border-2 border-[#121316] rounded-2xl shadow-[3px_3px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 active:shadow-[1px_1px_0px_#121316]">
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2.5 min-w-0">
                            <img src="{{ $authorPhoto }}" alt="{{ $authorUser?->name }}"
                                 onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($authorUser?->name ?? 'User') }}&background=121316&color=fff&bold=true';"
                                 class="w-9 h-9 rounded-xl object-cover border-2 border-[#121316] shadow-xs shrink-0">
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <h4 class="text-xs font-black text-[#121316] leading-none truncate max-w-[140px]">{{ $authorUser?->name ?? 'Pengguna' }}</h4>
                                    @if($authorUser?->ekskul_flair)
                                    <span class="text-[8px] font-mono font-black px-1.5 py-0.2 rounded-full border border-[#121316] bg-[#fde047] text-[#121316]" title="{{ $authorUser->ekskul_flair['label'] }}">
                                        {{ $authorUser->ekskul_flair['short_label'] }}
                                    </span>
                                    @endif
                                </div>
                                <span class="text-[9px] font-mono text-[#666] font-bold block mt-0.5">{{ $thread->created_at ? $thread->created_at->diffForHumans() : '' }}</span>
                            </div>
                        </div>

                        <span class="px-2 py-0.5 rounded-md bg-[#faf8f5] text-[#121316] border border-[#121316] text-[9px] font-mono font-black uppercase">
                            {{ \App\Models\ForumThread::CATEGORIES[$thread->category] ?? '💬 Diskusi' }}
                        </span>
                    </div>

                    <h3 class="text-xs font-black text-[#121316] leading-snug pt-0.5">{{ $thread->title }}</h3>
                    <p class="text-[11px] text-[#444] line-clamp-2 leading-relaxed font-medium">
                        {{ strip_tags($thread->content) }}
                    </p>

                    @if($thread->image_path)
                        <div class="h-32 w-full rounded-xl overflow-hidden border-2 border-[#121316] shadow-[1px_1px_0px_#121316]">
                            <img src="{{ asset('storage/' . $thread->image_path) }}" alt="{{ $thread->title }}" class="w-full h-full object-cover">
                        </div>
                    @endif

                    <div class="flex items-center space-x-4 pt-1.5 text-[10px] font-mono font-black text-[#121316] border-t border-[#121316]/10">
                        <span class="flex items-center gap-1 text-[#2563eb]"><i class="fa-regular fa-comment"></i> {{ $thread->replies_count ?? 0 }} Komentar</span>
                        <span class="flex items-center gap-1 text-[#ff3823]"><i class="fa-regular fa-heart"></i> {{ $thread->likes_count ?? 0 }} Suka</span>
                    </div>
                </a>
            @empty
                <div class="p-8 text-center text-[#666] text-xs font-bold space-y-2 bg-white border-2 border-[#121316] rounded-2xl shadow-[3px_3px_0px_#121316]">
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