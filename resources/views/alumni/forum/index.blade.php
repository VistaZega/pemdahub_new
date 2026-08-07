@extends('layouts.alumni')

@section('title', 'Forum Alumni')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Hero Banner (LMS Solid UI) -->
    <div class="bg-slate-900 rounded-3xl p-6 text-white shadow-xl relative overflow-hidden border-2 border-slate-900">
        <div class="relative z-10 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div>
                <span class="inline-block px-3 py-1 bg-amber-400 text-slate-900 text-xs font-black rounded-full border border-black mb-2">
                    <i class="fas fa-comments mr-1"></i> RUANG REMBUK ALUMNI
                </span>
                <h1 class="text-2xl sm:text-3xl font-black mb-1 text-white">Forum Diskusi Alumni</h1>
                <p class="text-indigo-200 text-xs sm:text-sm font-bold">Ruang obrolan, ide, dan nostalgia eksklusif alumni Perguruan Pembda Nias.</p>
            </div>
            <a href="{{ route('alumni.forum.create') }}" class="bg-amber-400 text-slate-900 hover:bg-amber-500 px-5 py-2.5 rounded-xl font-black text-sm shadow-md transition flex items-center gap-2 border border-black">
                <i class="fas fa-plus"></i> Buat Topik Baru
            </a>
        </div>
    </div>

    <!-- Filter Categories -->
    <div class="flex overflow-x-auto pb-2 gap-2 hide-scrollbar">
        <a href="{{ route('alumni.forum.index') }}" class="px-4 py-2 rounded-xl text-xs font-black whitespace-nowrap transition border-2 border-slate-900 {{ !$category ? 'bg-slate-900 text-amber-300 shadow-md' : 'bg-white text-slate-900 hover:bg-slate-100' }}">
            Semua Topik
        </a>
        @foreach($categories as $key => $label)
            <a href="{{ route('alumni.forum.index', ['category' => $key]) }}" class="px-4 py-2 rounded-xl text-xs font-black whitespace-nowrap transition border-2 border-slate-900 {{ $category === $key ? 'bg-slate-900 text-amber-300 shadow-md' : 'bg-white text-slate-900 hover:bg-slate-100' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <!-- Forum List -->
    <div class="space-y-4">
        @forelse($threads as $thread)
            <a href="{{ route('alumni.forum.show', $thread->id) }}" class="block bg-white border-2 border-slate-900 rounded-2xl p-5 shadow-md hover:shadow-lg transition group">
                <div class="flex items-start gap-4">
                    <img src="{{ $thread->user->photo_url }}" alt="Avatar" class="w-12 h-12 rounded-full object-cover shrink-0 border-2 border-slate-900">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="bg-indigo-900 text-amber-300 text-[10px] font-black px-2.5 py-0.5 rounded-lg border border-black uppercase">{{ $thread->category_label }}</span>
                            <span class="text-xs font-bold text-slate-700">&bull; {{ $thread->created_at->diffForHumans() }}</span>
                        </div>
                        <h3 class="text-lg font-extrabold text-slate-900 mb-1 group-hover:text-indigo-700 transition truncate">{{ $thread->title }}</h3>
                        <p class="text-xs font-semibold text-slate-700 line-clamp-2 leading-relaxed">{{ Str::limit(strip_tags($thread->content), 140) }}</p>
                        
                        <div class="flex items-center gap-4 mt-4 text-xs font-bold text-slate-900">
                            <span class="flex items-center gap-1.5 bg-slate-100 px-2.5 py-1 rounded-md border border-slate-300"><i class="fas fa-user text-indigo-700"></i> {{ $thread->user->name }}</span>
                            <span class="flex items-center gap-1.5 bg-slate-100 px-2.5 py-1 rounded-md border border-slate-300"><i class="fas fa-eye text-teal-700"></i> {{ $thread->views_count }} dilihat</span>
                            <span class="flex items-center gap-1.5 bg-amber-100 text-slate-900 px-2.5 py-1 rounded-md border border-slate-900 font-extrabold"><i class="fas fa-comment text-amber-700"></i> {{ $thread->replies->count() }} balasan</span>
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="bg-white border-2 border-slate-900 rounded-2xl p-10 text-center shadow-md">
                <div class="w-16 h-16 bg-slate-900 text-amber-300 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-black">
                    <i class="fas fa-folder-open text-2xl"></i>
                </div>
                <h3 class="text-slate-900 font-black text-lg mb-1">Belum ada topik diskusi</h3>
                <p class="text-xs font-bold text-slate-700 mb-4">Jadilah yang pertama memulai obrolan di kategori ini.</p>
                <a href="{{ route('alumni.forum.create') }}" class="inline-flex items-center gap-2 bg-slate-900 text-white font-extrabold px-5 py-2.5 rounded-xl text-xs hover:bg-slate-800 transition">
                    <i class="fas fa-plus"></i> Buat Topik Sekarang
                </a>
            </div>
        @endforelse
    </div>
    
    {{ $threads->links() }}
</div>
@endsection
