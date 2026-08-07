@extends('layouts.alumni')

@section('title', $forum->title)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Tombol Kembali -->
    <a href="{{ route('alumni.forum.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-indigo-600 transition">
        <i class="fas fa-arrow-left"></i> Kembali ke Forum
    </a>

    <!-- Konten Utama Topik -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-6">
                <span class="bg-indigo-50 text-indigo-600 text-xs font-bold px-3 py-1 rounded-full uppercase">{{ $forum->category_label }}</span>
                <span class="text-sm text-gray-400">&bull; {{ $forum->created_at->isoFormat('D MMMM Y - HH:mm') }}</span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6 leading-tight">{{ $forum->title }}</h1>

            <div class="flex items-center gap-4 mb-8 pb-8 border-b border-gray-100">
                <img src="{{ $forum->user->photo_url }}" alt="Avatar" class="w-12 h-12 rounded-full object-cover shadow-sm">
                <div>
                    <h4 class="font-bold text-gray-900">{{ $forum->user->name }}</h4>
                    <p class="text-xs text-gray-500">Pembuat Topik</p>
                </div>
            </div>

            <div class="prose max-w-none text-gray-700 leading-relaxed mb-8">
                {!! nl2br(e($forum->content)) !!}
            </div>

            @if($forum->image_path)
                <div class="mb-8">
                    <img src="{{ Storage::url($forum->image_path) }}" alt="Attachment" class="rounded-2xl max-h-96 w-auto object-cover border border-gray-100 shadow-sm">
                </div>
            @endif
        </div>
    </div>

    <!-- Area Balasan -->
    <div class="space-y-4">
        <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
            <i class="fas fa-comments text-indigo-500"></i> {{ $forum->replies->count() }} Balasan
        </h3>

        <!-- Form Balas -->
        <div class="bg-gray-50 rounded-2xl p-4 sm:p-6 border border-gray-100">
            <form action="{{ route('alumni.forum.reply', $forum->id) }}" method="POST">
                @csrf
                <div class="flex gap-4">
                    <img src="{{ auth()->user()?->photo_url ?? 'https://ui-avatars.com/api/?name=User' }}" class="w-10 h-10 rounded-full object-cover shrink-0 shadow-sm hidden sm:block">
                    <div class="flex-1 space-y-3">
                        <textarea name="content" rows="3" required placeholder="Tulis balasan Anda di sini..." class="w-full border-gray-300 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm p-3"></textarea>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-indigo-600 text-white font-bold px-6 py-2 rounded-xl shadow-sm hover:bg-indigo-700 transition">Kirim Balasan</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Daftar Balasan -->
        <div class="space-y-4">
            @foreach($forum->replies as $reply)
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex gap-4">
                    <img src="{{ $reply->user->photo_url }}" class="w-10 h-10 rounded-full object-cover shrink-0">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h4 class="font-bold text-gray-900 text-sm">{{ $reply->user->name }}</h4>
                            <span class="text-xs text-gray-400">&bull; {{ $reply->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-gray-700 text-sm leading-relaxed">
                            {!! nl2br(e($reply->content)) !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
