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
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <img src="{{ $authorPhoto }}" alt="{{ $authorUser?->name }}"
                     onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($authorUser?->name ?? 'User') }}&background=7c3aed&color=fff&bold=true';"
                     class="w-10 h-10 rounded-2xl object-cover border-2 border-purple-200 shadow-md shrink-0">
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

        <!-- Image Attachment -->
        @if($thread->image_path)
            <div class="rounded-2xl overflow-hidden border-2 border-purple-200 shadow-md max-w-sm">
                <img src="{{ asset('storage/' . $thread->image_path) }}" alt="{{ $thread->title }}" class="w-full h-auto object-cover hover:scale-105 transition duration-300">
            </div>
        @endif

        <!-- Document Attachment -->
        @if($thread->attachment_path)
            <div class="p-3 rounded-2xl bg-slate-50 border-2 border-slate-200 flex items-center justify-between text-xs max-w-sm shadow-xs">
                <div class="flex items-center space-x-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-black text-xs shrink-0">
                        📁
                    </div>
                    <div class="min-w-0">
                        <h5 class="font-black text-slate-800 text-[11px] truncate">{{ $thread->attachment_name ?? 'Dokumen Lampiran' }}</h5>
                        <span class="text-[9px] font-bold text-slate-400">File Lampiran</span>
                    </div>
                </div>
                <a href="{{ asset('storage/' . $thread->attachment_path) }}" target="_blank" 
                   class="px-3 py-1 rounded-xl bg-purple-600 text-white text-[10px] font-black shadow-xs hover:bg-purple-700 transition shrink-0">
                    Unduh File
                </a>
            </div>
        @endif

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
            @php
                $replyUser = $reply->user ?? null;
                $replyPhoto = $replyUser?->avatar_url ?? null;
                if (!$replyPhoto || str_contains($replyPhoto, 'default-avatar') || str_contains($replyPhoto, 'default-student.jpg')) {
                    if ($replyUser?->student?->photo_url) {
                        $replyPhoto = $replyUser->student->photo_url;
                    } elseif ($replyUser?->teacher?->photo_url) {
                        $replyPhoto = $replyUser->teacher->photo_url;
                    } else {
                        $replyPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($replyUser?->name ?? 'User') . '&background=7c3aed&color=fff&bold=true';
                    }
                }
            @endphp
            <div class="clay-card p-4 space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <img src="{{ $replyPhoto }}" alt="{{ $replyUser?->name }}"
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($replyUser?->name ?? 'User') }}&background=7c3aed&color=fff&bold=true';"
                             class="w-8 h-8 rounded-xl object-cover border border-purple-200 shadow-xs shrink-0">
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
