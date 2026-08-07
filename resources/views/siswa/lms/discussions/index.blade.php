@extends('layouts.siswa')

@section('title', 'Diskusi - ' . $course->name)

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
    @keyframes shimmer {
        0% { background-position: -200% center; }
        100% { background-position: 200% center; }
    }
    @keyframes pulse-glow {
        0%, 100% { box-shadow: 0 0 5px rgba(59,130,246,0.3); }
        50% { box-shadow: 0 0 15px rgba(59,130,246,0.6); }
    }
    .fade-up {
        animation: fadeUp 0.5s ease-out forwards;
    }
    .thread-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .thread-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 40px rgba(0,0,0,0.1);
    }
    .gradient-text {
        background: linear-gradient(135deg, #3b82f6, #6366f1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .hero-gradient {
        background: linear-gradient(135deg, #eff6ff 0%, #eef2ff 50%, #f0f9ff 100%);
    }
    .pill-btn {
        transition: all 0.3s ease;
    }
    .pill-btn.active {
        transform: scale(1.05);
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }
    .gradient-border:focus-within {
        border-image: linear-gradient(135deg, #3b82f6, #6366f1) 1;
        border-image-slice: 1;
    }
    .input-modern:focus {
        border-color: transparent;
        box-shadow: 0 0 0 2px rgba(99,102,241,0.2), 0 0 0 4px rgba(59,130,246,0.1);
    }
    .pinned-glow {
        animation: pulse-glow 2s infinite;
    }
    .avatar-gradient-blue {
        background: linear-gradient(135deg, #3b82f6, #6366f1);
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
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="{
    showForm: false,
    selectedType: 'discussion',
    contentLength: 0,
    maxLength: 2000
}">
    {{-- Premium Hero Header --}}
    <div class="hero-gradient rounded-2xl p-6 border border-blue-100/50 fade-up">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('siswa.lms.show', $course->id) }}?tab=discussions"
                   class="w-10 h-10 rounded-xl bg-white shadow-sm border border-blue-100 flex items-center justify-center text-blue-500 hover:text-blue-700 hover:shadow-md transition-all duration-300 hover:-translate-x-0.5">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="text-2xl font-extrabold gradient-text">Forum Diskusi</h2>
                    <p class="text-gray-500 text-sm mt-0.5">
                        <i class="fas fa-book-open mr-1 text-blue-400"></i>{{ $course->name }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="stat-card bg-white/80 backdrop-blur-sm rounded-xl px-4 py-2.5 border border-blue-100/50 shadow-sm">
                    <div class="text-xs text-gray-400 uppercase tracking-wider font-medium">Topik</div>
                    <div class="text-lg font-bold text-gray-800">{{ $discussions->total() }}</div>
                </div>
                <div class="stat-card bg-white/80 backdrop-blur-sm rounded-xl px-4 py-2.5 border border-blue-100/50 shadow-sm">
                    <div class="text-xs text-gray-400 uppercase tracking-wider font-medium">Balasan</div>
                    <div class="text-lg font-bold text-gray-800">{{ $discussions->sum('replies_count') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Topic Button + Form --}}
    <div class="fade-up" style="animation-delay: 0.1s">
        <button @click="showForm = !showForm"
                class="group inline-flex items-center gap-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-5 py-2.5 rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 text-sm font-semibold shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:-translate-y-0.5">
            <i class="fas fa-plus transition-transform duration-300" :class="showForm ? 'rotate-45' : ''"></i>
            <span x-text="showForm ? 'Tutup Form' : 'Buat Topik Baru'"></span>
        </button>

        <form x-show="showForm"
              x-transition:enter="transition ease-out duration-300"
              x-transition:enter-start="opacity-0 -translate-y-4"
              x-transition:enter-end="opacity-100 translate-y-0"
              x-transition:leave="transition ease-in duration-200"
              x-transition:leave-start="opacity-100 translate-y-0"
              x-transition:leave-end="opacity-0 -translate-y-4"
              action="{{ route('siswa.lms.discussions.store', $course->id) }}" method="POST"
              class="bg-white rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100 p-6 mt-4">
            @csrf
            <div class="space-y-5">
                {{-- Type Selector Pills --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Tipe Topik</label>
                    <div class="flex gap-3">
                        <button type="button" @click="selectedType = 'discussion'"
                                class="pill-btn inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium border-2 transition-all"
                                :class="selectedType === 'discussion' ? 'bg-blue-500 text-white border-blue-500 active shadow-lg shadow-blue-500/30' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300'">
                            <i class="fas fa-comments text-xs"></i> Diskusi
                        </button>
                        <button type="button" @click="selectedType = 'question'"
                                class="pill-btn inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium border-2 transition-all"
                                :class="selectedType === 'question' ? 'bg-amber-500 text-white border-amber-500 active shadow-lg shadow-amber-500/30' : 'bg-white text-gray-600 border-gray-200 hover:border-amber-300'">
                            <i class="fas fa-question-circle text-xs"></i> Pertanyaan
                        </button>
                    </div>
                    <input type="hidden" name="type" :value="selectedType">
                </div>

                {{-- Title Input --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Judul Topik</label>
                    <input type="text" name="title" required placeholder="Tulis judul topik yang menarik..."
                           class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm input-modern focus:outline-none transition-all duration-300 placeholder-gray-300">
                </div>

                {{-- Content Textarea --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Isi Diskusi</label>
                    <textarea name="content" required rows="5"
                              placeholder="Apa yang ingin Anda diskusikan?"
                              @input="contentLength = $event.target.value.length"
                              :maxlength="maxLength"
                              class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm input-modern focus:outline-none transition-all duration-300 resize-none placeholder-gray-300"></textarea>
                    <div class="flex justify-end mt-1">
                        <span class="text-xs" :class="contentLength > maxLength * 0.9 ? 'text-red-500' : 'text-gray-400'">
                            <span x-text="contentLength"></span> / <span x-text="maxLength"></span>
                        </span>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-2.5 rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 text-sm font-semibold shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:-translate-y-0.5">
                        <i class="fas fa-paper-plane"></i> Posting Topik
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Discussion Thread List --}}
    <div class="space-y-3">
        @forelse($discussions as $index => $discussion)
        <a href="{{ route('siswa.lms.discussions.show', [$course->id, $discussion->id]) }}"
           class="thread-card block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden fade-up {{ $discussion->is_pinned ? 'pinned-glow' : '' }}"
           style="animation-delay: {{ 0.15 + ($index * 0.05) }}s">
            <div class="flex">
                {{-- Color-coded Left Border --}}
                <div class="w-1.5 flex-shrink-0 {{ $discussion->type === 'question' ? 'bg-amber-500' : ($discussion->type === 'announcement' ? 'bg-rose-500' : 'bg-blue-500') }}"></div>

                <div class="flex items-start gap-4 p-5 flex-1">
                    {{-- Avatar --}}
                    <div class="w-11 h-11 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0 shadow-md {{ $discussion->type === 'question' ? 'avatar-gradient-orange' : ($discussion->type === 'announcement' ? 'avatar-gradient-red' : 'avatar-gradient-blue') }}">
                        {{ strtoupper(substr($discussion->author->name ?? 'A', 0, 1)) }}
                    </div>

                    <div class="flex-1 min-w-0">
                        {{-- Title + Status Badges --}}
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            @if($discussion->is_pinned)
                            <span class="inline-flex items-center gap-1 text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-medium">
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
                        <div class="flex items-center gap-4 mt-3 text-xs text-gray-400">
                            <span class="inline-flex items-center gap-1.5">
                                <div class="w-4 h-4 rounded-full bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-user text-[8px] text-gray-400"></i>
                                </div>
                                {{ $discussion->author->name ?? 'Anonim' }}
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <i class="fas fa-clock"></i> {{ $discussion->created_at->diffForHumans() }}
                            </span>
                            <span class="inline-flex items-center gap-1 {{ $discussion->replies_count > 0 ? 'text-blue-500 font-medium' : '' }}">
                                <i class="fas fa-comment-dots"></i> {{ $discussion->replies_count }} balasan
                            </span>
                        </div>
                    </div>

                    {{-- Type Icon (Right side) --}}
                    <div class="hidden sm:flex items-center justify-center w-10 h-10 rounded-xl flex-shrink-0 {{ $discussion->type === 'question' ? 'bg-amber-50 text-amber-500' : ($discussion->type === 'announcement' ? 'bg-rose-50 text-rose-500' : 'bg-blue-50 text-blue-500') }}">
                        <i class="fas {{ $discussion->type === 'question' ? 'fa-question' : ($discussion->type === 'announcement' ? 'fa-bullhorn' : 'fa-comments') }}"></i>
                    </div>
                </div>
            </div>
        </a>
        @empty
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center fade-up" style="animation-delay: 0.15s">
            <div class="w-20 h-20 rounded-full bg-blue-50 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-comments text-3xl text-blue-300"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-700 mb-1">Belum Ada Diskusi</h3>
            <p class="text-gray-400 text-sm">Jadilah yang pertama memulai topik diskusi baru!</p>
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
