@extends('layouts.guru')

@section('title', $discussion->title . ' - Diskusi')

@push('styles')
<style>
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulse-gold {
        0%, 100% { box-shadow: 0 0 5px rgba(245,158,11,0.2), 0 0 10px rgba(245,158,11,0.1); }
        50% { box-shadow: 0 0 15px rgba(245,158,11,0.4), 0 0 25px rgba(245,158,11,0.15); }
    }
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-3px); }
    }
    .fade-up {
        animation: fadeUp 0.5s ease-out forwards;
    }
    .gradient-text {
        background: linear-gradient(135deg, #06b6d4, #10b981);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .avatar-ring {
        background: linear-gradient(135deg, #06b6d4, #10b981);
        padding: 3px;
    }
    .best-answer-glow {
        animation: pulse-gold 2s infinite;
        border-color: #f59e0b;
    }
    .reply-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .reply-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.06);
    }
    .input-modern:focus {
        border-color: transparent;
        box-shadow: 0 0 0 2px rgba(6,182,212,0.2), 0 0 0 4px rgba(16,185,129,0.1);
    }
    .hero-gradient {
        background: linear-gradient(135deg, #ecfdf5 0%, #f0fdfa 50%, #ecfeff 100%);
    }
    .nested-connector {
        position: relative;
    }
    .nested-connector::before {
        content: '';
        position: absolute;
        left: -16px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, #99f6e4, #a7f3d0);
        border-radius: 1px;
    }
    .star-badge {
        animation: float 3s ease-in-out infinite;
    }
    .mod-btn {
        transition: all 0.3s ease;
    }
    .mod-btn:hover {
        transform: translateY(-1px);
    }
    .mark-best-btn {
        transition: all 0.3s ease;
    }
    .mark-best-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 15px rgba(16,185,129,0.3);
    }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="{ confirmDelete: false }">
    {{-- Thread Header --}}
    <div class="hero-gradient rounded-xl p-6 border border-emerald-100/50 fade-up">
        <div class="flex items-start gap-4">
            <a href="{{ route('guru.lms.discussions.index', $course->id) }}"
               class="w-10 h-10 rounded-xl bg-white shadow-sm border border-emerald-100 flex items-center justify-center text-emerald-500 hover:text-emerald-700 hover:shadow-md transition-all duration-300 hover:-translate-x-0.5 flex-shrink-0 mt-1">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-2">
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full {{ $discussion->type === 'question' ? 'bg-amber-100 text-amber-700' : ($discussion->type === 'announcement' ? 'bg-rose-100 text-rose-700' : 'bg-cyan-100 text-cyan-700') }}">
                        <i class="fas {{ $discussion->type === 'question' ? 'fa-question-circle' : ($discussion->type === 'announcement' ? 'fa-bullhorn' : 'fa-comments') }}"></i>
                        {{ $discussion->type === 'question' ? 'Pertanyaan' : ($discussion->type === 'announcement' ? 'Pengumuman' : 'Diskusi') }}
                    </span>
                    @if($discussion->is_pinned)
                    <span class="inline-flex items-center gap-1 text-xs bg-cyan-50 text-cyan-600 px-2.5 py-1 rounded-full font-medium">
                        <i class="fas fa-thumbtack text-[10px]"></i> Disematkan
                    </span>
                    @endif
                    @if($discussion->is_locked)
                    <span class="inline-flex items-center gap-1 text-xs bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full font-medium">
                        <i class="fas fa-lock text-[10px]"></i> Dikunci
                    </span>
                    @endif
                    @if($discussion->is_resolved)
                    <span class="inline-flex items-center gap-1 text-xs bg-emerald-50 text-emerald-600 px-2.5 py-1 rounded-full font-medium">
                        <i class="fas fa-check-circle text-[10px]"></i> Terjawab
                    </span>
                    @endif
                </div>
                <h2 class="text-xl md:text-2xl font-extrabold text-gray-800 leading-tight">{{ $discussion->title }}</h2>
                <p class="text-gray-400 text-sm mt-1">
                    <i class="fas fa-book-open mr-1 text-emerald-300"></i>{{ $course->name }} · Forum Diskusi
                </p>
            </div>
        </div>
    </div>

    {{-- Original Post Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden fade-up" style="animation-delay: 0.1s">
        <div class="border-l-4 {{ $discussion->type === 'question' ? 'border-amber-500' : ($discussion->type === 'announcement' ? 'border-rose-500' : 'border-cyan-500') }} p-6">
            <div class="flex items-start gap-4">
                {{-- Large Avatar with Gradient Ring --}}
                <div class="avatar-ring rounded-full flex-shrink-0">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-cyan-500 to-emerald-500 flex items-center justify-center text-white font-bold text-lg border-2 border-white">
                        {{ strtoupper(substr($discussion->author->name ?? 'A', 0, 1)) }}
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-bold text-gray-800 text-base">{{ $discussion->author->name ?? 'Anonim' }}</span>
                        <span class="inline-flex items-center text-[10px] font-bold tracking-wide px-2 py-0.5 rounded bg-emerald-50 text-emerald-600">Guru</span>
                        <span class="text-xs text-gray-400 flex items-center gap-1">
                            <i class="fas fa-calendar-alt"></i> {{ $discussion->created_at->format('d M Y H:i') }}
                        </span>
                    </div>
                    <div class="mt-4 text-gray-700 whitespace-pre-line leading-relaxed prose prose-sm max-w-none">{{ $discussion->content }}</div>
                </div>
            </div>
        </div>

        {{-- Guru Moderation Actions --}}
        <div class="bg-gray-50/50 border-t border-gray-100 px-6 py-3">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs text-gray-400 font-medium tracking-wide mr-2">Moderasi:</span>
                <a href="{{ route('guru.lms.discussions.edit', [$course->id, $discussion->id]) }}" class="mod-btn inline-flex items-center gap-1.5 text-xs font-medium px-3.5 py-1.5 rounded-full border bg-white text-blue-600 border-gray-200 hover:bg-blue-50 hover:border-blue-200 transition-all">
                    <i class="fas fa-edit"></i> Edit Topik
                </a>
                <form action="{{ route('guru.lms.discussions.togglePin', [$course->id, $discussion->id]) }}" method="POST" class="inline">
                    @csrf
                    <button class="mod-btn inline-flex items-center gap-1.5 text-xs font-medium px-3.5 py-1.5 rounded-full border transition-all {{ $discussion->is_pinned ? 'bg-cyan-50 text-cyan-700 border-cyan-200 hover:bg-cyan-100' : 'bg-white text-gray-600 border-gray-200 hover:border-cyan-300 hover:text-cyan-600' }}">
                        <i class="fas fa-thumbtack"></i> {{ $discussion->is_pinned ? 'Lepas Pin' : 'Sematkan' }}
                    </button>
                </form>
                <form action="{{ route('guru.lms.discussions.toggleLock', [$course->id, $discussion->id]) }}" method="POST" class="inline">
                    @csrf
                    <button class="mod-btn inline-flex items-center gap-1.5 text-xs font-medium px-3.5 py-1.5 rounded-full border transition-all {{ $discussion->is_locked ? 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100' : 'bg-white text-gray-600 border-gray-200 hover:border-amber-300 hover:text-amber-600' }}">
                        <i class="fas fa-{{ $discussion->is_locked ? 'unlock' : 'lock' }}"></i> {{ $discussion->is_locked ? 'Buka Kunci' : 'Kunci' }}
                    </button>
                </form>
                <form action="{{ route('guru.lms.discussions.destroy', [$course->id, $discussion->id]) }}" method="POST" class="inline" onsubmit="return confirm('Hapus diskusi ini?')">
                    @csrf @method('DELETE')
                    <button class="mod-btn inline-flex items-center gap-1.5 text-xs font-medium px-3.5 py-1.5 rounded-full border bg-white text-red-600 border-gray-200 hover:bg-red-50 hover:border-red-200 transition-all">
                        <i class="fas fa-trash-alt"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Replies Section --}}
    <div class="space-y-3">
        <div class="flex items-center gap-3 fade-up" style="animation-delay: 0.15s">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-cyan-50 flex items-center justify-center">
                    <i class="fas fa-comments text-cyan-500 text-sm"></i>
                </div>
                <h3 class="font-bold text-gray-800 text-lg">{{ $discussion->replies->count() }} Balasan</h3>
            </div>
            <div class="flex-1 h-px bg-gradient-to-r from-gray-200 to-transparent"></div>
        </div>

        @foreach($discussion->replies as $index => $reply)
        <div class="reply-card {{ $reply->parent_id ? 'ml-8 nested-connector' : '' }} {{ $reply->is_best_answer ? 'best-answer-glow' : '' }} bg-white rounded-xl shadow-sm border {{ $reply->is_best_answer ? 'border-amber-300 bg-amber-50/20' : 'border-gray-100' }} overflow-hidden fade-up"
             style="animation-delay: {{ 0.2 + ($index * 0.05) }}s">
            @if($reply->is_best_answer)
            <div class="bg-gradient-to-r from-amber-50 to-yellow-50 px-5 py-2 border-b border-amber-100 flex items-center gap-2">
                <i class="fas fa-star text-amber-500 star-badge"></i>
                <span class="text-xs font-bold text-amber-700 tracking-wide">Jawaban Terbaik</span>
            </div>
            @endif
            <div class="p-5">
                <div class="flex items-start gap-3">
                    {{-- Reply Avatar --}}
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-sm {{ $reply->is_best_answer ? 'bg-gradient-to-br from-amber-400 to-yellow-500' : 'bg-gradient-to-br from-gray-400 to-gray-500' }}">
                        {{ strtoupper(substr($reply->author->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-semibold text-gray-800 text-sm">{{ $reply->author->name ?? 'Anonim' }}</span>
                            <span class="text-xs text-gray-400 flex items-center gap-1">
                                <i class="fas fa-clock text-[10px]"></i> {{ $reply->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <div class="mt-2 text-gray-700 text-sm whitespace-pre-line leading-relaxed">{{ $reply->content }}</div>

                        {{-- Mark Best Answer Button (guru only, for question type) --}}
                        @if($discussion->type === 'question' && !$reply->is_best_answer)
                        <div class="mt-3 pt-2">
                            <form action="{{ route('guru.lms.discussions.bestAnswer', [$course->id, $discussion->id, $reply->id]) }}" method="POST" class="inline">
                                @csrf
                                <button class="mark-best-btn inline-flex items-center gap-1.5 text-xs font-medium px-3.5 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-all">
                                    <i class="fas fa-check-circle"></i> Tandai Jawaban Terbaik
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        @if($discussion->replies->count() === 0)
        <div class="bg-white rounded-xl border border-gray-100 p-8 text-center fade-up" style="animation-delay: 0.2s">
            <div class="w-14 h-14 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-comment-slash text-xl text-gray-300"></i>
            </div>
            <p class="text-gray-400 text-sm">Belum ada balasan pada diskusi ini.</p>
        </div>
        @endif
    </div>

    {{-- Reply Form --}}
    @if(!$discussion->is_locked)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden fade-up" style="animation-delay: 0.3s">
        <div class="bg-gradient-to-r from-cyan-50 to-emerald-50 px-6 py-3 border-b border-gray-100">
            <h4 class="font-bold text-gray-700 flex items-center gap-2">
                <i class="fas fa-reply text-cyan-500"></i> Tulis Balasan
            </h4>
        </div>
        <form action="{{ route('guru.lms.discussions.reply', [$course->id, $discussion->id]) }}" method="POST" class="p-6">
            @csrf
            <textarea name="content" required rows="4"
                      placeholder="Tulis balasan..."
                      class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm input-modern focus:outline-none transition-all duration-300 resize-none placeholder-gray-300"></textarea>
            <div class="flex justify-end mt-3">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-gradient-to-r from-cyan-600 to-emerald-600 text-white px-5 py-2.5 rounded-xl hover:from-cyan-700 hover:to-emerald-700 transition-all duration-300 text-sm font-semibold shadow-lg shadow-cyan-500/25 hover:shadow-cyan-500/40 hover:-translate-y-0.5">
                    <i class="fas fa-paper-plane"></i> Kirim Balasan
                </button>
            </div>
        </form>
    </div>
    @else
    <div class="bg-gray-50 rounded-xl border border-gray-200 p-6 text-center fade-up" style="animation-delay: 0.3s">
        <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-lock text-xl text-gray-400"></i>
        </div>
        <p class="text-gray-500 font-medium">Diskusi ini dikunci</p>
        <p class="text-gray-400 text-sm mt-1">Tidak bisa membalas diskusi yang sudah dikunci</p>
    </div>
    @endif
</div>
@endsection
