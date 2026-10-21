@extends('mobile.layouts.app')

@section('title', $thread->title . ' - PembdaSPACE')

@section('content')
<div class="space-y-4 font-['Plus_Jakarta_Sans',sans-serif]">
    <!-- Back Button -->
    <a href="{{ route('mobile.space.index') }}" class="inline-flex items-center gap-1.5 text-xs font-mono font-black text-[#121316] hover:underline transition">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke PembdaSPACE
    </a>

    <!-- Main Thread Card (Neo-Brutalist) -->
    <div class="p-5 space-y-3 bg-white border-2 border-[#121316] rounded-2xl shadow-[4px_4px_0px_#121316]">
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
            $isOwnerOrAdmin = (Auth::id() === $thread->user_id) || in_array(Auth::user()->role ?? '', ['superadmin', 'admin_sekolah']);
        @endphp

        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3 min-w-0">
                <img src="{{ $authorPhoto }}" alt="{{ $authorUser?->name }}"
                     onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($authorUser?->name ?? 'User') }}&background=121316&color=fff&bold=true';"
                     class="w-10 h-10 rounded-xl object-cover border-2 border-[#121316] shadow-xs shrink-0">
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <h4 class="text-xs font-black text-[#121316] leading-none truncate max-w-[150px]">{{ $thread->user->name ?? 'Pengguna' }}</h4>
                        @if($thread->user?->ekskul_flair)
                        <span class="text-[8px] font-mono font-black px-1.5 py-0.2 rounded-full border border-[#121316] bg-[#fde047] text-[#121316]" title="{{ $thread->user->ekskul_flair['label'] }}">
                            {{ $thread->user->ekskul_flair['short_label'] }}
                        </span>
                        @endif
                    </div>
                    <span class="text-[10px] font-mono text-[#666] font-bold block mt-0.5">{{ $thread->created_at ? $thread->created_at->format('d M Y, H:i') : '' }}</span>
                </div>
            </div>
            
            <div class="flex items-center space-x-1.5">
                <span class="px-2 py-0.5 rounded-md bg-[#faf8f5] text-[#121316] border border-[#121316] text-[10px] font-mono font-black">
                    #{{ $thread->category_label ?? $thread->category ?? 'diskusi' }}
                </span>

                @if($isOwnerOrAdmin)
                <a href="{{ route('mobile.space.edit', $thread->id) }}" class="p-1.5 rounded-lg bg-[#faf8f5] border border-[#121316] text-[#121316] hover:bg-[#fde047] text-xs transition" title="Edit Postingan">
                    <i class="fa-solid fa-pen-to-square"></i>
                </a>
                <form action="{{ route('mobile.space.destroy', $thread->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus postingan ini?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1.5 rounded-lg bg-rose-50 border border-[#121316] text-rose-600 hover:bg-rose-100 text-xs transition" title="Hapus Postingan">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>

        <!-- Thread Title & Body -->
        <h2 class="text-base font-black text-[#121316] leading-snug">{{ $thread->title }}</h2>
        <div class="text-xs text-[#222] space-y-2 leading-relaxed whitespace-pre-line font-medium">
            {!! nl2br(e($thread->content)) !!}
        </div>

        <!-- Image Attachment -->
        @if($thread->image_path)
            <div class="rounded-xl overflow-hidden border-2 border-[#121316] shadow-[2px_2px_0px_#121316] max-w-sm">
                <img src="{{ asset('storage/' . $thread->image_path) }}" alt="{{ $thread->title }}" class="w-full h-auto object-cover">
            </div>
        @endif

        <!-- Document Attachment -->
        @if($thread->attachment_path)
            <div class="p-3 rounded-xl bg-[#faf8f5] border-2 border-[#121316] flex items-center justify-between text-xs max-w-sm shadow-[2px_2px_0px_#121316]">
                <div class="flex items-center space-x-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-[#fde047] border border-[#121316] text-[#121316] flex items-center justify-center font-black text-xs shrink-0">
                        📁
                    </div>
                    <div class="min-w-0">
                        <h5 class="font-black text-[#121316] text-[11px] truncate">{{ $thread->attachment_name ?? 'Dokumen Lampiran' }}</h5>
                        <span class="text-[9px] font-mono text-[#666]">File Lampiran</span>
                    </div>
                </div>
                <a href="{{ asset('storage/' . $thread->attachment_path) }}" target="_blank" 
                   class="px-3 py-1 rounded-lg bg-[#10b981] text-white border border-[#121316] text-[10px] font-mono font-black shadow-xs shrink-0">
                    Unduh
                </a>
            </div>
        @endif

        <!-- Interaction Bar -->
        <div class="flex items-center justify-between pt-3 border-t-2 border-[#121316]/10 text-xs font-mono font-black text-[#121316]">
            <div class="flex items-center space-x-4">
                <form action="{{ route('mobile.space.like', $thread->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="flex items-center space-x-1.5 {{ $isLiked ? 'text-[#ff3823]' : 'text-[#121316] hover:text-[#ff3823]' }} transition">
                        <i class="{{ $isLiked ? 'fa-solid fa-heart text-[#ff3823]' : 'fa-regular fa-heart' }} text-sm"></i>
                        <span>{{ $thread->likes_count ?? 0 }} Suka</span>
                    </button>
                </form>

                <span class="flex items-center space-x-1 text-[#121316]">
                    <i class="fa-regular fa-comment text-sm text-[#2563eb]"></i>
                    <span>{{ count($thread->replies) }} Komentar</span>
                </span>
            </div>

            <span class="text-[10px] text-[#666] font-bold"><i class="fa-regular fa-eye mr-1"></i>{{ $thread->views_count ?? 0 }} views</span>
        </div>
    </div>

    <!-- Reply Input Box (Neo-Brutalist) -->
    <div class="p-4 bg-white border-2 border-[#121316] rounded-2xl shadow-[3px_3px_0px_#121316]">
        <h4 class="text-xs font-mono font-black text-[#121316] uppercase tracking-wider mb-2">Tulis Komentar</h4>
        <form action="{{ route('mobile.space.reply', $thread->id) }}" method="POST" class="space-y-3">
            @csrf
            <textarea name="content" rows="3" required
                      placeholder="Tulis tanggapan Anda secara ramah..." 
                      class="w-full p-3 bg-[#faf8f5] border-2 border-[#121316] rounded-xl text-[#121316] text-xs font-bold placeholder-[#777] focus:outline-none focus:shadow-[2px_2px_0px_#ff3823] transition resize-none"></textarea>
            
            <div class="flex justify-end">
                <button type="submit" 
                        class="px-5 py-2.5 bg-[#ff3823] text-white border-2 border-[#121316] rounded-xl font-black text-xs shadow-[2px_2px_0px_#121316] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition">
                    Kirim Komentar
                </button>
            </div>
        </form>
    </div>

    <!-- Replies List -->
    <div class="space-y-2.5">
        <h4 class="text-xs font-mono font-black text-[#121316] uppercase tracking-wider px-1">Komentar ({{ count($thread->replies) }})</h4>

        @forelse($thread->replies as $reply)
            @php
                $replyUser = $reply->user ?? null;
                $replyPhoto = $replyUser?->avatar_url ?? null;
                if (!$replyPhoto || str_contains($replyPhoto, 'default-avatar') || str_contains($replyPhoto, 'default-student.jpg')) {
                    if ($replyUser?->student?->photo_url) {
                        $replyPhoto = $replyUser->student->photo_url;
                    } elseif ($replyUser?->teacher?->photo_url) {
                        $replyPhoto = $replyUser->teacher->photo_url;
                    } else {
                        $replyPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($replyUser?->name ?? 'User') . '&background=121316&color=fff&bold=true';
                    }
                }
            @endphp
            <div class="p-4 space-y-2 bg-white border-2 border-[#121316] rounded-2xl shadow-[2px_2px_0px_#121316]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2.5 min-w-0">
                        <img src="{{ $replyPhoto }}" alt="{{ $replyUser?->name }}"
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($replyUser?->name ?? 'User') }}&background=121316&color=fff&bold=true';"
                             class="w-8 h-8 rounded-lg object-cover border border-[#121316] shrink-0">
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <h5 class="text-xs font-black text-[#121316] leading-none truncate max-w-[130px]">{{ $reply->user->name ?? 'Pengguna' }}</h5>
                                @if($reply->user?->ekskul_flair)
                                <span class="text-[8px] font-mono font-black px-1.5 py-0.2 rounded-full border border-[#121316] bg-[#fde047] text-[#121316]" title="{{ $reply->user->ekskul_flair['label'] }}">
                                    {{ $reply->user->ekskul_flair['short_label'] }}
                                </span>
                                @endif
                            </div>
                            <span class="text-[9px] font-mono text-[#666] font-bold block mt-0.5">{{ $reply->created_at ? $reply->created_at->diffForHumans() : '' }}</span>
                        </div>
                    </div>
                    @if((Auth::id() === $reply->user_id) || in_array(Auth::user()->role ?? '', ['superadmin', 'admin_sekolah']))
                    <form action="{{ route('mobile.space.reply.destroy', $reply->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus komentar ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-rose-600 hover:text-rose-800 text-[11px] px-2 py-0.5 rounded-lg bg-rose-50 font-bold border border-rose-200 transition">
                            <i class="fa-solid fa-trash-can"></i> Hapus
                        </button>
                    </form>
                    @endif
                </div>

                <p class="text-xs text-[#222] leading-relaxed font-medium pl-10">{!! nl2br(e($reply->content)) !!}</p>
            </div>
        @empty
            <div class="p-6 text-center text-[#666] text-xs font-bold bg-white border-2 border-[#121316] rounded-2xl shadow-[2px_2px_0px_#121316]">
                Belum ada komentar. Jadi yang pertama menanggapi!
            </div>
        @endforelse
    </div>
</div>
@endsection