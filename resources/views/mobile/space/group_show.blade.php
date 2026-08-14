@extends('mobile.layouts.app')

@section('title', $group->name . ' - Pembda Space Groups')

@section('content')
<div class="space-y-4" x-data="{ showMembers: false }">
    <!-- Back Navigation & Group Header Card -->
    <div class="flex items-center justify-between">
        <a href="{{ route('mobile.space.index') }}" class="inline-flex items-center gap-1.5 text-xs font-black text-slate-500 hover:text-slate-900 transition">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Grup
        </a>

        <button @click="showMembers = true" 
                class="px-3 py-1 bg-purple-100 text-purple-900 rounded-xl text-[10px] font-black border border-purple-200 flex items-center gap-1">
            <i class="fa-solid fa-users"></i> {{ count($group->members) }} Anggota
        </button>
    </div>

    <!-- Group Header Card -->
    <div class="clay-card p-5 space-y-3 bg-purple-50 border-2 border-purple-300">
        <div class="flex items-center space-x-3">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center text-white text-xl font-black shadow-md border-2 border-white shrink-0">
                {{ $group->icon ?? '💬' }}
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="text-base font-black text-slate-900 leading-snug truncate">{{ $group->name }}</h2>
                <p class="text-[11px] text-purple-700 font-bold leading-tight">{{ $group->description ?? 'Grup Obrolan Resmi PembdaHUB' }}</p>
            </div>
        </div>

        @if($group->only_admin_can_post)
            <div class="p-2.5 rounded-xl bg-amber-100 text-amber-900 border border-amber-300 text-[10px] font-black flex items-center gap-2">
                <i class="fa-solid fa-bullhorn text-amber-700 text-xs"></i>
                <span>Grup Pengumuman: Hanya Guru / Admin yang dapat mengirim pesan.</span>
            </div>
        @endif
    </div>

    <!-- Messages / Threads Feed -->
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
            <div class="clay-card p-4.5 space-y-2.5 bg-white border-2 border-slate-200">
                <!-- Author Header -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <img src="{{ $authorPhoto }}" alt="{{ $authorUser?->name }}"
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($authorUser?->name ?? 'User') }}&background=7c3aed&color=fff&bold=true';"
                             class="w-9 h-9 rounded-2xl object-cover border border-purple-200 shadow-xs shrink-0">
                        <div>
                            <h4 class="text-xs font-black text-slate-900 leading-none">{{ $authorUser?->name ?? 'Pengguna' }}</h4>
                            <span class="text-[9px] text-slate-400 font-bold block mt-0.5">{{ $thread->created_at ? $thread->created_at->diffForHumans() : '' }}</span>
                        </div>
                    </div>

                    <a href="{{ route('mobile.space.show', $thread->id) }}" class="text-[10px] text-purple-600 font-black hover:underline flex items-center gap-1">
                        Balas Komentar &rarr;
                    </a>
                </div>

                <!-- Content -->
                <div class="text-xs text-slate-800 leading-relaxed font-semibold pl-11 whitespace-pre-line">
                    {!! nl2br(e($thread->content)) !!}
                </div>

                <!-- Footer Stats -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-[10px] text-slate-500 font-black pl-11">
                    <span class="text-blue-600 flex items-center gap-1">
                        <i class="fa-regular fa-comment"></i> {{ $thread->replies_count ?? count($thread->replies ?? []) }} Balasan
                    </span>
                    <span class="text-rose-600 flex items-center gap-1">
                        <i class="fa-regular fa-heart"></i> {{ $thread->likes_count ?? count($thread->likes ?? []) }} Suka
                    </span>
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-3xl">💬</div>
                <p>Belum ada pesan dikirim di grup ini. Mulai obrolan pertama!</p>
            </div>
        @endforelse
    </div>

    <!-- Chat / Message Input Form Card -->
    @if(!$group->only_admin_can_post || ($membership && $membership->role === 'admin'))
        <div class="clay-card p-4 bg-white border-2 border-purple-200 sticky bottom-4 shadow-xl">
            <form action="{{ route('mobile.space.group.post', $group->id) }}" method="POST" class="space-y-2">
                @csrf
                <div class="flex items-center space-x-2">
                    <textarea name="content" rows="2" required
                              placeholder="Tulis pesan ke grup {{ $group->name }}..." 
                              class="w-full p-3 bg-slate-50 border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold placeholder-slate-400 focus:outline-none focus:border-purple-600 transition resize-none"></textarea>
                    
                    <button type="submit" 
                            class="clay-btn px-4 py-3 text-white font-black text-xs shrink-0 flex items-center gap-1">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Member Roster Modal -->
    <div x-show="showMembers" 
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white rounded-3xl p-6 w-full max-w-sm max-h-[80vh] overflow-y-auto shadow-2xl border-4 border-purple-200 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-xs font-black text-slate-900 uppercase">Daftar Anggota Grup ({{ count($group->members) }})</h3>
                <button @click="showMembers = false" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 font-black text-xs">✕</button>
            </div>

            <div class="space-y-2">
                @foreach($group->members as $m)
                    @php
                        $u = $m->user;
                        $mPhoto = $u?->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($u?->name ?? 'User') . '&background=7c3aed&color=fff&bold=true';
                    @endphp
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                        <div class="flex items-center space-x-2">
                            <img src="{{ $mPhoto }}" class="w-7 h-7 rounded-full object-cover border border-purple-200">
                            <span class="font-black text-slate-800">{{ $u?->name ?? 'Pengguna' }}</span>
                        </div>

                        @if($m->role === 'admin')
                            <span class="px-2 py-0.5 rounded-md bg-purple-100 text-purple-900 text-[9px] font-black uppercase border border-purple-200">
                                Admin Grup
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
