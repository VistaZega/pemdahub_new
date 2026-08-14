@extends('mobile.layouts.app')

@section('title', $group->name . ' - Pembda Class Squads')

@section('content')
<div class="space-y-4" x-data="{ showMembers: false, showVirtualRoom: false }">
    <!-- Back Navigation & Header Buttons -->
    <div class="flex items-center justify-between">
        <a href="{{ route('mobile.space.index') }}" class="inline-flex items-center gap-1.5 text-xs font-black text-slate-500 hover:text-slate-900 transition">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Class Squads
        </a>

        <div class="flex items-center space-x-2">
            <!-- 1-Click Virtual Room Button -->
            <button @click="showVirtualRoom = true" 
                    class="px-3 py-1.5 bg-gradient-to-r from-rose-500 to-red-600 text-white rounded-xl text-[10px] font-black shadow-md flex items-center gap-1.5 animate-pulse">
                <span class="w-2 h-2 rounded-full bg-white"></span> 🔴 Virtual Room
            </button>

            <!-- Member Count Badge -->
            <button @click="showMembers = true" 
                    class="px-3 py-1.5 bg-purple-100 text-purple-900 rounded-xl text-[10px] font-black border border-purple-200 flex items-center gap-1">
                <i class="fa-solid fa-users"></i> {{ $group->calculated_member_count ?? count($group->members) }} Squad
            </button>
        </div>
    </div>

    <!-- Group Header Card -->
    <div class="clay-card p-5 space-y-3 bg-gradient-to-br from-purple-50 via-indigo-50 to-white border-2 border-purple-300 relative overflow-hidden">
        <div class="flex items-center space-x-3">
            <!-- Squad Avatar with Live Online Dot -->
            <div class="relative">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center text-white text-xl font-black shadow-md border-2 border-white shrink-0">
                    {{ $group->icon ?? '💬' }}
                </div>
                <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-white absolute -bottom-0.5 -right-0.5 shadow-sm" title="Live Online"></span>
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-black text-slate-900 leading-snug truncate">{{ $group->name }}</h2>
                    <span class="text-[9px] font-black text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full border border-emerald-300 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span> Live Active
                    </span>
                </div>
                <p class="text-[11px] text-purple-700 font-bold leading-tight mt-0.5">{{ $group->description ?? 'Class Squad Resmi PembdaHUB' }}</p>
            </div>
        </div>

        @if($group->only_admin_can_post)
            <div class="p-2.5 rounded-xl bg-amber-100 text-amber-900 border border-amber-300 text-[10px] font-black flex items-center gap-2">
                <i class="fa-solid fa-bullhorn text-amber-700 text-xs"></i>
                <span>Squad Pengumuman: Hanya Guru / Admin yang dapat mengirim pesan.</span>
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
                
                $isTeacherOrAdmin = $authorUser?->hasRole('guru') || $authorUser?->hasRole('admin') || $authorUser?->teacher;
                
                // Formating Mentions
                $formattedContent = e($thread->content);
                $formattedContent = preg_replace('/@SemuaSiswa/i', '<span class="px-1.5 py-0.5 bg-purple-600 text-white rounded-md text-[10px] font-black">@SemuaSiswa</span>', $formattedContent);
                $formattedContent = preg_replace('/@WaliKelas/i', '<span class="px-1.5 py-0.5 bg-amber-500 text-white rounded-md text-[10px] font-black">@WaliKelas</span>', $formattedContent);
                $formattedContent = preg_replace('/@Guru/i', '<span class="px-1.5 py-0.5 bg-blue-600 text-white rounded-md text-[10px] font-black">@Guru</span>', $formattedContent);
            @endphp
            <div class="clay-card p-4.5 space-y-3 bg-white border-2 border-slate-200">
                <!-- Author Header -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <!-- 3D Profile Aura Ring for Teachers/Admins -->
                        <div class="relative">
                            <img src="{{ $authorPhoto }}" alt="{{ $authorUser?->name }}"
                                 onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($authorUser?->name ?? 'User') }}&background=7c3aed&color=fff&bold=true';"
                                 class="w-9 h-9 rounded-2xl object-cover border-2 {{ $isTeacherOrAdmin ? 'border-amber-400 ring-2 ring-amber-300 shadow-md' : 'border-purple-200' }} shrink-0">
                            @if($isTeacherOrAdmin)
                                <span class="absolute -top-1 -right-1 text-[10px]">👑</span>
                            @endif
                        </div>

                        <div>
                            <div class="flex items-center gap-1.5">
                                <h4 class="text-xs font-black text-slate-900 leading-none">{{ $authorUser?->name ?? 'Pengguna' }}</h4>
                                @if($isTeacherOrAdmin)
                                    <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-900 text-[8px] font-black uppercase">Guru</span>
                                @endif
                            </div>
                            <span class="text-[9px] text-slate-400 font-bold block mt-0.5">{{ $thread->created_at ? $thread->created_at->diffForHumans() : '' }}</span>
                        </div>
                    </div>

                    <a href="{{ route('mobile.space.show', $thread->id) }}" class="text-[10px] text-purple-600 font-black hover:underline flex items-center gap-1">
                        Balas &rarr;
                    </a>
                </div>

                <!-- Text Content -->
                <div class="text-xs text-slate-800 leading-relaxed font-semibold pl-11 whitespace-pre-line">
                    {!! nl2br($formattedContent) !!}
                </div>

                <!-- SMART ACADEMIC CARD: KARTU TUGAS LMS (Jika postingan mengandung referensi LMS) -->
                @if(str_contains($thread->content, '[TUGAS_LMS]') || $thread->category === 'sharing' || str_contains(strtolower($thread->title), 'tugas'))
                    <div class="ml-11 p-3.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-lg space-y-2 border-2 border-emerald-300">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded-md bg-white/20 text-[9px] font-black uppercase tracking-wider text-white">
                                📚 SMART CARD: TUGAS LMS
                            </span>
                            <span class="text-[10px] font-bold text-emerald-100">Batas Waktu: Hari Ini</span>
                        </div>
                        <h4 class="text-xs font-black leading-snug">{{ $thread->title }}</h4>
                        <div class="flex items-center justify-between pt-1">
                            <span class="text-[10px] text-emerald-100 font-medium">Mata Pelajaran Aktif</span>
                            <a href="{{ route('mobile.lms.index') }}" 
                               class="px-3 py-1 rounded-xl bg-white text-emerald-800 text-[10px] font-black shadow-md hover:bg-emerald-50 transition flex items-center gap-1">
                                ⚡ Kerjakan Tugas LMS &rarr;
                            </a>
                        </div>
                    </div>
                @endif

                <!-- SMART ACADEMIC CARD: KARTU ALERT CBT (Jika postingan mengandung referensi CBT) -->
                @if(str_contains($thread->content, '[ALERT_CBT]') || str_contains(strtolower($thread->title), 'ujian') || str_contains(strtolower($thread->title), 'cbt'))
                    <div class="ml-11 p-3.5 rounded-2xl bg-gradient-to-r from-amber-500 to-rose-600 text-white shadow-lg space-y-2 border-2 border-amber-300">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded-md bg-white/20 text-[9px] font-black uppercase tracking-wider text-white">
                                🎯 SMART CARD: ALERT UJIAN CBT
                            </span>
                            <span class="text-[10px] font-bold text-amber-100">Durasi: 60 Menit</span>
                        </div>
                        <h4 class="text-xs font-black leading-snug">{{ $thread->title }}</h4>
                        <div class="flex items-center justify-between pt-1">
                            <span class="text-[10px] text-amber-100 font-medium">Kode Akses: UJIAN-PEMBDA</span>
                            <a href="/m/siswa/cbt" 
                               class="px-3 py-1 rounded-xl bg-white text-rose-800 text-[10px] font-black shadow-md hover:bg-rose-50 transition flex items-center gap-1">
                                🎯 Masuk Bilik Ujian &rarr;
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Footer Stats & Reactions -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-[10px] text-slate-500 font-black pl-11">
                    <span class="text-blue-600 flex items-center gap-1">
                        <i class="fa-regular fa-comment"></i> {{ $thread->replies_count ?? count($thread->replies ?? []) }} Komentar
                    </span>
                    <form action="{{ route('mobile.space.like', $thread->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="text-rose-600 flex items-center gap-1 hover:scale-110 transition">
                            <i class="fa-solid fa-heart"></i> {{ $thread->likes_count ?? count($thread->likes ?? []) }} Suka
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-3xl">👥</div>
                <p>Belum ada pesan dikirim di Class Squad ini. Mulai obrolan pertama!</p>
            </div>
        @endforelse
    </div>

    <!-- Chat Input Form Card -->
    @if(!$group->only_admin_can_post || ($membership && $membership->role === 'admin'))
        <div class="clay-card p-4 bg-white border-2 border-purple-200 sticky bottom-4 shadow-xl space-y-2">
            <!-- Mention Shortcut Chips -->
            <div class="flex items-center space-x-1.5 overflow-x-auto pb-1 text-[9px] font-black no-scrollbar">
                <span class="text-slate-400 uppercase tracking-wider shrink-0">Tag:</span>
                <button type="button" onclick="document.getElementById('groupChatInput').value += ' @SemuaSiswa '" 
                        class="px-2 py-0.5 rounded-lg bg-purple-100 text-purple-900 border border-purple-200 shrink-0 hover:bg-purple-200">
                    +@SemuaSiswa
                </button>
                <button type="button" onclick="document.getElementById('groupChatInput').value += ' @WaliKelas '" 
                        class="px-2 py-0.5 rounded-lg bg-amber-100 text-amber-900 border border-amber-200 shrink-0 hover:bg-amber-200">
                    +@WaliKelas
                </button>
                <button type="button" onclick="document.getElementById('groupChatInput').value += ' [TUGAS_LMS] '" 
                        class="px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-900 border border-emerald-200 shrink-0 hover:bg-emerald-200">
                    +Kartu LMS
                </button>
            </div>

            <form action="{{ route('mobile.space.group.post', $group->id) }}" method="POST" class="flex items-center space-x-2">
                @csrf
                <textarea id="groupChatInput" name="content" rows="2" required
                          placeholder="Ketik pesan ke {{ $group->name }}... (Gunakan @SemuaSiswa untuk mention)" 
                          class="w-full p-3 bg-slate-50 border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold placeholder-slate-400 focus:outline-none focus:border-purple-600 transition resize-none"></textarea>
                
                <button type="submit" 
                        class="clay-btn px-4 py-3 text-white font-black text-xs shrink-0 flex items-center gap-1">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                </button>
            </form>
        </div>
    @endif

    <!-- Virtual Room Modal (1-Click Google Meet / Zoom) -->
    <div x-show="showVirtualRoom" 
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white rounded-3xl p-6 w-full max-w-sm shadow-2xl border-4 border-rose-200 space-y-4 text-center">
            <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl font-black mx-auto">
                🎥
            </div>
            <div>
                <h3 class="text-sm font-black text-slate-900">Ruang Kelas Virtual Live</h3>
                <p class="text-[11px] text-slate-500 font-bold mt-1">Gabung tatap muka virtual dengan anggota {{ $group->name }}</p>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                <a href="https://meet.google.com/new" target="_blank" 
                   class="w-full py-3 bg-blue-600 text-white rounded-xl text-xs font-black shadow-md flex items-center justify-center gap-2 hover:bg-blue-700 transition">
                    <i class="fa-solid fa-video"></i> Buka Google Meet Live
                </a>
                <a href="https://zoom.us/join" target="_blank" 
                   class="w-full py-3 bg-indigo-600 text-white rounded-xl text-xs font-black shadow-md flex items-center justify-center gap-2 hover:bg-indigo-700 transition">
                    <i class="fa-solid fa-camera"></i> Buka Zoom Virtual
                </a>
            </div>

            <button @click="showVirtualRoom = false" 
                    class="w-full py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-black hover:bg-slate-200 transition">
                Tutup Window
            </button>
        </div>
    </div>

    <!-- Member Roster Modal -->
    <div x-show="showMembers" 
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white rounded-3xl p-6 w-full max-w-sm max-h-[80vh] overflow-y-auto shadow-2xl border-4 border-purple-200 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-xs font-black text-slate-900 uppercase">Anggota {{ $group->name }} ({{ $group->calculated_member_count ?? count($group->members) }})</h3>
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
                                Wali Kelas / Admin
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
