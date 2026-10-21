@extends('mobile.layouts.app')

@section('title', 'Pembda Space - Class Squads & Wall Mobile')

@section('content')
<div class="space-y-4 font-['Plus_Jakarta_Sans',sans-serif]">
    <!-- Header Title & Create Button -->
    <div class="flex items-center justify-between">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-gradient-to-r from-violet-600 to-indigo-600 text-white text-[10px] font-mono font-black mb-1.5 shadow-xs">
                <i class="fa-solid fa-shapes text-yellow-300 text-[10px]"></i>
                <span>PEMBDA SPACE</span>
            </div>
            <h2 class="text-xl font-black text-[#121316] tracking-tight">Ruang Komunitas</h2>
            <p class="text-[11px] text-slate-600 font-bold">Class Squads & Wall Diskusi Sekolah</p>
        </div>
        <a href="{{ route('mobile.space.create') }}" 
           class="px-4 py-2.5 border-2 border-slate-900" style="background-color: #dc2626 !important; color: #ffffff !important; border-2 border-[#121316] rounded-xl text-xs font-black flex items-center gap-1.5 shadow-[2px_2px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition">
            <i class="fa-solid fa-plus text-xs"></i> Post Baru
        </a>
    </div>

    <!-- LIVE ACTIVITY TICKER BANNER (Youth Vibrant) -->
    <div class="p-2.5 bg-gradient-to-r from-violet-700 via-indigo-700 to-fuchsia-700 text-white rounded-2xl shadow-[3px_3px_0px_#121316] border-2 border-[#121316] overflow-hidden relative">
        <div class="flex items-center space-x-2 text-[10px] font-black">
            <span class="px-2 py-0.5 rounded-lg bg-[#fde047] text-[#121316] uppercase text-[8px] font-mono font-black tracking-wider shrink-0 flex items-center gap-1 border border-[#121316]">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-ping"></span> LIVE
            </span>
            <marquee class="font-mono font-bold text-yellow-200 min-w-0" scrollamount="4">
                ⚡ Rombel X-IPA 1 Aktif Berdiskusi! &nbsp;&bull;&nbsp; 🎉 Modul Baru & Kartu LMS Berhasil Dibagikan di Class Squads! &nbsp;&bull;&nbsp; 🏆 Siswa Meraih Poin Reputasi Bintang Minggu Ini! &nbsp;&bull;&nbsp; 🧱 Menara Prestasi Aktif Dibangun!
            </marquee>
        </div>
    </div>

    <!-- MAIN TOP NAVIGATION TABS (CLASS SQUADS vs WALL) -->
    <div class="grid grid-cols-2 gap-2 bg-[#faf8f5] p-1.5 rounded-2xl border-2 border-[#121316] shadow-[2px_2px_0px_#121316]">
        <a href="{{ route('mobile.space.index', ['tab' => 'groups']) }}" 
           class="py-2.5 text-center text-xs font-black rounded-xl transition flex items-center justify-center gap-1.5 {{ $tab === 'groups' ? 'bg-[#fde047] text-[#121316] border-2 border-[#121316] shadow-[2px_2px_0px_#121316]' : 'text-slate-600 hover:text-[#121316]' }}">
            <i class="fa-solid fa-users-rectangle text-xs"></i>
            <span>Class Squads</span>
            <span class="px-1.5 py-0.5 rounded-full border border-slate-900 font-black" style="background-color: #0f172a !important; color: #fde047 !important; font-mono text-[10px] font-black">{{ count($groups) }}</span>
        </a>
        <a href="{{ route('mobile.space.index', ['tab' => 'kanal']) }}" 
           class="py-2.5 text-center text-xs font-black rounded-xl transition flex items-center justify-center gap-1.5 {{ $tab === 'kanal' ? 'bg-[#fde047] text-[#121316] border-2 border-[#121316] shadow-[2px_2px_0px_#121316]' : 'text-slate-600 hover:text-[#121316]' }}">
            <i class="fa-solid fa-comments text-xs"></i>
            <span>Wall Obrolan</span>
            <span class="px-1.5 py-0.5 rounded-full border border-slate-900 font-black" style="background-color: #0f172a !important; color: #fde047 !important; font-mono text-[10px] font-black">{{ count($categories) }}</span>
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
                       class="w-full pl-10 pr-4 py-2.5 bg-white border-2 border-[#121316] rounded-2xl text-[#121316] text-xs placeholder-slate-500 font-bold focus:outline-none focus:shadow-[2px_2px_0px_#ff3823] shadow-[2px_2px_0px_#121316] transition">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-500 text-xs"></i>
            </div>
        </form>

        <!-- Squad Filter Pills -->
        <div class="flex items-center space-x-2 overflow-x-auto pb-1 no-scrollbar">
            <a href="{{ route('mobile.space.index', ['tab' => 'groups', 'filter' => 'semua']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-black whitespace-nowrap transition border-2 border-[#121316] {{ $groupFilter === 'semua' ? 'border-2 border-slate-900" style="background-color: #dc2626 !important; color: #ffffff !important; shadow-[2px_2px_0px_#121316]' : 'bg-white text-[#121316] hover:bg-[#faf8f5]' }}">
                🔥 Semua Squad
            </a>
            <a href="{{ route('mobile.space.index', ['tab' => 'groups', 'filter' => 'kelas']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-black whitespace-nowrap transition border-2 border-[#121316] {{ $groupFilter === 'kelas' ? 'border-2 border-slate-900" style="background-color: #dc2626 !important; color: #ffffff !important; shadow-[2px_2px_0px_#121316]' : 'bg-white text-[#121316] hover:bg-[#faf8f5]' }}">
                🏫 Class Squads Saya
            </a>
            <a href="{{ route('mobile.space.index', ['tab' => 'groups', 'filter' => 'lobi']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-black whitespace-nowrap transition border-2 border-[#121316] {{ $groupFilter === 'lobi' ? 'border-2 border-slate-900" style="background-color: #dc2626 !important; color: #ffffff !important; shadow-[2px_2px_0px_#121316]' : 'bg-white text-[#121316] hover:bg-[#faf8f5]' }}">
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
                    $grpName = $grp->name ?? 'Class Squad';
                    $cleanGrpName = trim(preg_replace('/^[\p{Emoji_Presentation}\p{Extended_Pictographic}\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\s]+/u', '', $grpName));
                    $squadFa = match(true) {
                        str_contains(strtolower($grpName), 'lobi') => ['fa-solid fa-school', 'bg-purple-100 text-purple-600 border-purple-200'],
                        str_contains(strtolower($grpName), 'guru') => ['fa-solid fa-chalkboard-user', 'bg-emerald-100 text-emerald-600 border-emerald-200'],
                        default => ['fa-solid fa-users', 'bg-sky-100 text-sky-600 border-sky-200']
                    };
                @endphp
                <a href="{{ route('mobile.space.group.show', $grp->id) }}" 
                   class="p-4 block transition relative space-y-2 bg-white border-2 border-[#121316] rounded-2xl shadow-[3px_3px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 active:shadow-[1px_1px_0px_#121316]">
                    
                    <div class="flex items-center space-x-3">
                        <!-- Squad Icon Avatar -->
                        <div class="w-12 h-12 rounded-2xl {{ $squadFa[1] }} flex items-center justify-center text-lg font-black shadow-[1px_1px_0px_#121316] border-2 border-[#121316] shrink-0">
                            <i class="{{ $squadFa[0] }}"></i>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-black text-[#121316] leading-snug truncate">{{ $cleanGrpName }}</h4>
                                <span class="text-[9px] font-mono text-slate-500 font-bold shrink-0">
                                    {{ $latest?->created_at ? $latest->created_at->diffForHumans() : $grp->created_at->diffForHumans() }}
                                </span>
                            </div>

                            <p class="text-[11px] text-slate-600 truncate font-medium mt-0.5">
                                @if($latest)
                                    <strong class="text-indigo-600">{{ $latest->user->name ?? 'User' }}:</strong> {{ strip_tags($latest->content) }}
                                @else
                                    <span class="italic text-slate-400">Belum ada obrolan baru</span>
                                @endif
                            </p>

                            <div class="flex items-center space-x-2 text-[9px] font-mono font-bold text-[#121316] mt-1">
                                <span class="px-2 py-0.5 rounded-full bg-[#faf8f5] text-[#121316] border border-[#121316] font-black">
                                    <i class="fa-solid fa-users text-[8px] text-indigo-600"></i> {{ $memberCount }} Anggota
                                </span>
                                @if($grp->only_admin_can_post)
                                    <span class="px-2 py-0.5 rounded-full bg-[#fde047] text-[#121316] border border-[#121316] font-black">
                                        <i class="fa-solid fa-bullhorn text-[8px] text-rose-600"></i> Pengumuman Guru
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="p-8 text-center text-slate-500 text-xs font-bold space-y-2 bg-white border-2 border-[#121316] rounded-2xl shadow-[3px_3px_0px_#121316]">
                    <div class="text-3xl">👥</div>
                    <p>Belum ada Class Squad terdaftar saat ini.</p>
                </div>
            @endforelse
        </div>
    @endif

    <!-- TAB 2: WALL (CATEGORIES & THREADS FEED) -->
    @if($tab === 'kanal')
        <!-- Search Input -->
        <form action="{{ route('mobile.space.index') }}" method="GET" class="relative">
            <input type="hidden" name="tab" value="kanal">
            @if($category)<input type="hidden" name="category" value="{{ $category }}">@endif
            <div class="relative">
                <input type="text" name="search" value="{{ $search ?? '' }}" 
                       placeholder="Cari ide, diskusi, atau karya di Wall..." 
                       class="w-full pl-10 pr-4 py-2.5 bg-white border-2 border-[#121316] rounded-2xl text-[#121316] text-xs placeholder-slate-500 font-bold focus:outline-none focus:shadow-[2px_2px_0px_#ff3823] shadow-[2px_2px_0px_#121316] transition">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-500 text-xs"></i>
            </div>
        </form>

        <!-- Category Pills (All Wall Categories) -->
        <div class="flex items-center space-x-2 overflow-x-auto pb-1 no-scrollbar">
            <a href="{{ route('mobile.space.index', ['tab' => 'kanal']) }}" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-black whitespace-nowrap transition border-2 border-[#121316] flex items-center gap-1.5 {{ !$category ? 'border-2 border-slate-900" style="background-color: #dc2626 !important; color: #ffffff !important; shadow-[2px_2px_0px_#121316]' : 'bg-white text-[#121316] hover:bg-[#faf8f5]' }}">
                <i class="fa-solid fa-compass text-xs"></i>
                <span>Semua Wall</span>
            </a>
            @foreach($categories as $key => $label)
                @php
                    $cleanLabel = trim(preg_replace('/^[\p{Emoji_Presentation}\p{Extended_Pictographic}\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\s]+/u', '', $label));
                @endphp
                <a href="{{ route('mobile.space.index', ['tab' => 'kanal', 'category' => $key]) }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-black whitespace-nowrap transition border-2 border-[#121316] {{ $category === $key ? 'border-2 border-slate-900" style="background-color: #dc2626 !important; color: #ffffff !important; shadow-[2px_2px_0px_#121316]' : 'bg-white text-[#121316] hover:bg-[#faf8f5]' }}">
                    {{ $cleanLabel }}
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

                    $rawCat = \App\Models\ForumThread::CATEGORIES[$thread->category] ?? 'Diskusi';
                    $cleanCat = trim(preg_replace('/^[\p{Emoji_Presentation}\p{Extended_Pictographic}\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\s]+/u', '', $rawCat));
                @endphp
                <a href="{{ route('mobile.space.show', $thread->id) }}" 
                   class="p-4 block transition relative space-y-2 bg-white border-2 border-[#121316] rounded-2xl shadow-[3px_3px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 active:shadow-[1px_1px_0px_#121316] overflow-hidden">
                    
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
                                <span class="text-[9px] font-mono text-slate-500 font-bold block mt-0.5">{{ $thread->created_at ? $thread->created_at->diffForHumans() : '' }}</span>
                            </div>
                        </div>

                        <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200 text-[9px] font-mono font-black uppercase">
                            {{ $cleanCat }}
                        </span>
                    </div>

                    <h3 class="text-xs font-black text-[#121316] leading-snug pt-0.5">{{ $thread->title }}</h3>
                    <p class="text-[11px] text-slate-600 line-clamp-2 leading-relaxed font-medium">
                        {{ strip_tags($thread->content) }}
                    </p>

                    @if($thread->image_path)
                        <div class="h-32 w-full rounded-xl overflow-hidden border-2 border-[#121316] shadow-[1px_1px_0px_#121316]">
                            <img src="{{ asset('storage/' . $thread->image_path) }}" alt="{{ $thread->title }}" class="w-full h-full object-cover">
                        </div>
                    @endif

                    <div class="flex items-center space-x-4 pt-1.5 text-[10px] font-mono font-black text-[#121316] border-t border-[#121316]/10">
                        <span class="flex items-center gap-1 text-indigo-600"><i class="fa-regular fa-comment"></i> {{ $thread->replies_count ?? 0 }} Komentar</span>
                        <span class="flex items-center gap-1 text-rose-600"><i class="fa-regular fa-heart"></i> {{ $thread->likes_count ?? 0 }} Suka</span>
                    </div>
                </a>
            @empty
                <div class="p-8 text-center text-slate-500 text-xs font-bold space-y-2 bg-white border-2 border-[#121316] rounded-2xl shadow-[3px_3px_0px_#121316]">
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
