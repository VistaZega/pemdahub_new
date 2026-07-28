@extends('layouts.guru')

@section('title', 'Forum Diskusi - ' . $course->name)

@push('styles')
<style>
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes slideDown {
        from { opacity: 0; max-height: 0; transform: translateY(-10px); }
        to { opacity: 1; max-height: 600px; transform: translateY(0); }
    }
    @keyframes pulse-glow {
        0%, 100% { box-shadow: 0 0 5px rgba(6,182,212,0.3); }
        50% { box-shadow: 0 0 15px rgba(6,182,212,0.6); }
    }
    .fade-up {
        animation: fadeUp 0.5s ease-out forwards;
        opacity: 0;
    }
    .thread-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .thread-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 40px rgba(0,0,0,0.1);
    }
    .gradient-text {
        background: linear-gradient(135deg, #06b6d4, #10b981);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .hero-gradient {
        background: linear-gradient(135deg, #ecfdf5 0%, #f0fdfa 50%, #ecfeff 100%);
    }
    .pill-btn {
        transition: all 0.3s ease;
    }
    .pill-btn.active {
        transform: scale(1.05);
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }
    .input-modern:focus {
        border-color: transparent;
        box-shadow: 0 0 0 2px rgba(6,182,212,0.2), 0 0 0 4px rgba(16,185,129,0.1);
    }
    .pinned-glow {
        animation: pulse-glow 2s infinite;
    }
    .avatar-gradient-blue {
        background: linear-gradient(135deg, #06b6d4, #0891b2);
    }
    .avatar-gradient-orange {
        background: linear-gradient(135deg, #f59e0b, #ef4444);
    }
    .avatar-gradient-red {
        background: linear-gradient(135deg, #ef4444, #ec4899);
    }
    .stat-card {
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
    }
    .toggle-checkbox:checked {
        background: linear-gradient(135deg, #06b6d4, #10b981);
    }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="{
    showForm: false,
    selectedType: 'discussion',
    contentLength: 0,
    maxLength: 2000,
    isPinned: false
}">
    {{-- Premium Hero Header --}}
    <div class="rounded-3xl p-6 md:p-8 shadow-md border-2 border-black" style="background-color: #090d16 !important; color: #ffffff !important;">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('guru.lms.show', $course->id) }}?tab=discussions"
                   class="w-10 h-10 rounded-2xl bg-white border-2 border-black flex items-center justify-center text-black hover:bg-amber-300 transition-all shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="text-2xl font-black text-white tracking-wide">Forum Diskusi Kelas</h2>
                    <p class="text-amber-400 font-bold text-xs mt-0.5">
                        <i class="fas fa-book-open mr-1 text-amber-400"></i>{{ $course->name }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-slate-800 rounded-2xl px-4 py-2.5 border-2 border-slate-700 shadow-sm text-center">
                    <div class="text-[9px] text-amber-300 uppercase tracking-widest font-black">Topik Diskusi</div>
                    <div class="text-lg font-black text-white">{{ $discussions->total() }}</div>
                </div>
                <div class="bg-slate-800 rounded-2xl px-4 py-2.5 border-2 border-slate-700 shadow-sm text-center">
                    <div class="text-[9px] text-amber-300 uppercase tracking-widest font-black">Total Balasan</div>
                    <div class="text-lg font-black text-white">{{ $discussions->sum('replies_count') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Topic Button + Form --}}
    <div>
        <button @click="showForm = !showForm"
                class="inline-flex items-center gap-2.5 bg-black hover:bg-amber-400 hover:text-black text-white border-2 border-black px-6 py-3 rounded-2xl transition-all duration-300 text-xs font-black uppercase tracking-wider shadow-md">
            <i class="fas fa-plus text-amber-400 transition-transform duration-300" :class="showForm ? 'rotate-45' : ''"></i>
            <span x-text="showForm ? 'Tutup Form' : 'Buat Topik Baru'"></span>
        </button>

        <form x-show="showForm"
              x-transition
              action="{{ route('guru.lms.discussions.store', $course->id) }}" method="POST"
              class="bg-white rounded-3xl shadow-md border-2 border-black p-6 mt-4">
            @csrf
            <div class="space-y-5">
                {{-- Type Selector Pills --}}
                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-3">Tipe Topik Diskusi</label>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" @click="selectedType = 'discussion'"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-xs font-black border-2 border-black uppercase tracking-wider transition-all"
                                :class="selectedType === 'discussion' ? 'bg-amber-300 text-black' : 'bg-white text-black hover:bg-slate-100'">
                            <i class="fas fa-comments text-xs"></i> Diskusi
                        </button>
                        <button type="button" @click="selectedType = 'question'"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-xs font-black border-2 border-black uppercase tracking-wider transition-all"
                                :class="selectedType === 'question' ? 'bg-sky-300 text-black' : 'bg-white text-black hover:bg-slate-100'">
                            <i class="fas fa-question-circle text-xs"></i> Pertanyaan
                        </button>
                        <button type="button" @click="selectedType = 'announcement'"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-xs font-black border-2 border-black uppercase tracking-wider transition-all"
                                :class="selectedType === 'announcement' ? 'bg-rose-300 text-black' : 'bg-white text-black hover:bg-slate-100'">
                            <i class="fas fa-bullhorn text-xs"></i> Pengumuman
                        </button>
                    </div>
                    <input type="hidden" name="type" :value="selectedType">
                </div>

                {{-- Title Input --}}
                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Judul Topik</label>
                    <input type="text" name="title" required placeholder="Tulis judul topik diskusi..."
                           class="w-full border-2 border-black rounded-2xl px-4 py-3 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none">
                </div>

                {{-- Content Textarea --}}
                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-2">Isi Diskusi</label>
                    <textarea name="content" required rows="5"
                              placeholder="Tulis detail pembahasan diskusi..."
                              @input="contentLength = $event.target.value.length"
                              :maxlength="maxLength"
                              class="w-full border-2 border-black rounded-2xl p-4 text-sm text-black font-black focus:ring-4 focus:ring-black/20 outline-none resize-none"></textarea>
                    <div class="flex justify-end mt-1">
                        <span class="text-xs font-black text-black">
                            <span x-text="contentLength"></span> / <span x-text="maxLength"></span> Karakter
                        </span>
                    </div>
                </div>

                {{-- Pin + Submit --}}
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <label class="flex items-center gap-3 cursor-pointer group" @click="isPinned = !isPinned">
                        <div class="relative">
                            <input type="checkbox" name="is_pinned" value="1" class="sr-only" :checked="isPinned">
                            <div class="w-10 h-6 rounded-full transition-all duration-300"
                                 :class="isPinned ? 'bg-gradient-to-r from-cyan-500 to-emerald-500' : 'bg-gray-200'">
                            </div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-300"
                                 :class="isPinned ? 'translate-x-4' : ''">
                            </div>
                        </div>
                        <span class="text-sm font-medium" :class="isPinned ? 'text-cyan-700' : 'text-gray-500'">
                            <i class="fas fa-thumbtack mr-1"></i> Sematkan topik
                        </span>
                    </label>
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-gradient-to-r from-cyan-600 to-emerald-600 text-white px-6 py-2.5 rounded-xl hover:from-cyan-700 hover:to-emerald-700 transition-all duration-300 text-sm font-semibold shadow-lg shadow-cyan-500/25 hover:shadow-cyan-500/40 hover:-translate-y-0.5">
                        <i class="fas fa-paper-plane"></i> Posting Topik
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Discussion Thread List --}}
    <div class="space-y-3">
        @forelse($discussions as $index => $discussion)
        <a href="{{ route('guru.lms.discussions.show', [$course->id, $discussion->id]) }}"
           class="thread-card block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden fade-up {{ $discussion->is_pinned ? 'pinned-glow' : '' }}"
           style="animation-delay: {{ 0.15 + ($index * 0.05) }}s">
            <div class="flex">
                {{-- Color-coded Left Border --}}
                <div class="w-1.5 flex-shrink-0 {{ $discussion->type === 'question' ? 'bg-amber-500' : ($discussion->type === 'announcement' ? 'bg-rose-500' : 'bg-cyan-500') }}"></div>

                <div class="flex items-start gap-4 p-5 flex-1">
                    {{-- Avatar --}}
                    <div class="w-11 h-11 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0 shadow-md {{ $discussion->type === 'question' ? 'avatar-gradient-orange' : ($discussion->type === 'announcement' ? 'avatar-gradient-red' : 'avatar-gradient-blue') }}">
                        {{ strtoupper(substr($discussion->author->name ?? 'A', 0, 1)) }}
                    </div>

                    <div class="flex-1 min-w-0">
                        {{-- Title + Status Badges --}}
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            @if($discussion->is_pinned)
                            <span class="inline-flex items-center gap-1 text-xs bg-cyan-50 text-cyan-600 px-2 py-0.5 rounded-full font-medium">
                                <i class="fas fa-thumbtack text-[10px]"></i> Disematkan
                            </span>
                            @endif
                            @if($discussion->is_locked)
                            <span class="inline-flex items-center gap-1 text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-medium">
                                <i class="fas fa-lock text-[10px]"></i> Dikunci
                            </span>
                            @endif
                            @if($discussion->is_resolved)
                            <span class="inline-flex items-center gap-1 text-xs bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full font-medium">
                                <i class="fas fa-check-circle text-[10px]"></i> Terjawab
                            </span>
                            @endif
                        </div>

                        <h4 class="font-bold text-gray-800 text-[15px] leading-snug">{{ $discussion->title }}</h4>
                        <p class="text-gray-500 text-sm mt-1.5 line-clamp-2 leading-relaxed">{{ Str::limit($discussion->content, 120) }}</p>

                        {{-- Meta Info --}}
                        <div class="flex items-center gap-4 mt-3 text-xs text-gray-400 flex-wrap">
                            <span class="inline-flex items-center gap-1.5">
                                <div class="w-4 h-4 rounded-full bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-user text-[8px] text-gray-400"></i>
                                </div>
                                {{ $discussion->author->name ?? 'Anonim' }}
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <i class="fas fa-clock"></i> {{ $discussion->created_at->diffForHumans() }}
                            </span>
                            <span class="inline-flex items-center gap-1 {{ $discussion->replies_count > 0 ? 'text-cyan-500 font-medium' : '' }}">
                                <i class="fas fa-comment-dots"></i> {{ $discussion->replies_count }} balasan
                            </span>
                            @if($discussion->last_reply_at)
                            <span class="inline-flex items-center gap-1">
                                <i class="fas fa-history"></i> Terakhir: {{ $discussion->last_reply_at->diffForHumans() }}
                            </span>
                            @endif
                        </div>
                    </div>

                    {{-- Type Icon (Right side) --}}
                    <div class="hidden sm:flex items-center justify-center w-10 h-10 rounded-xl flex-shrink-0 {{ $discussion->type === 'question' ? 'bg-amber-50 text-amber-500' : ($discussion->type === 'announcement' ? 'bg-rose-50 text-rose-500' : 'bg-cyan-50 text-cyan-500') }}">
                        <i class="fas {{ $discussion->type === 'question' ? 'fa-question' : ($discussion->type === 'announcement' ? 'fa-bullhorn' : 'fa-comments') }}"></i>
                    </div>
                </div>
            </div>
        </a>
        @empty
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center fade-up" style="animation-delay: 0.15s">
            <div class="w-20 h-20 rounded-full bg-cyan-50 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-comments text-3xl text-cyan-300"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-700 mb-1">Belum Ada Diskusi</h3>
            <p class="text-gray-400 text-sm">Mulai topik diskusi baru untuk kelas Anda!</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($discussions->hasPages())
    <div class="mt-6 flex justify-center fade-up" style="animation-delay: 0.3s">
        {{ $discussions->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/alpinejs@3/dist/cdn.min.js" defer></script>
@endpush
