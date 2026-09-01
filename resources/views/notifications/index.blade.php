@extends('layouts.app')

@section('title', 'Pusat Notifikasi')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-3xl p-6 md:p-8 shadow-xl">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center text-2xl">🔔</div>
                <div>
                    <h1 class="text-2xl font-black">Pusat Notifikasi</h1>
                    <p class="text-white/70 text-sm font-bold">
                        @if($unreadCount > 0)
                        {{ $unreadCount }} notifikasi belum dibaca
                        @else
                        Semua notifikasi sudah dibaca ✅
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                    @csrf
                    <button type="submit" class="bg-white/20 hover:bg-white/30 text-white font-bold px-4 py-2 rounded-xl text-xs transition">
                        <i class="fas fa-check-double mr-1"></i> Tandai Semua Dibaca
                    </button>
                </form>
                @endif
                <select onchange="window.location.href='{{ route('notifications.index') }}?type='+this.value" class="bg-white/10 border border-white/20 text-white rounded-xl px-3 py-2 text-xs font-bold">
                    <option value="">Semua Tipe</option>
                    <option value="info" {{ $type == 'info' ? 'selected' : '' }}>Informasi</option>
                    <option value="grade" {{ $type == 'grade' ? 'selected' : '' }}>Nilai</option>
                    <option value="assignment" {{ $type == 'assignment' ? 'selected' : '' }}>Tugas</option>
                    <option value="bill" {{ $type == 'bill' ? 'selected' : '' }}>Tagihan</option>
                    <option value="warning" {{ $type == 'warning' ? 'selected' : '' }}>Peringatan</option>
                    <option value="alert" {{ $type == 'alert' ? 'selected' : '' }}>Penting</option>
                </select>
            </div>
        </div>
    </div>

    @if($notifications->count() > 0)
    <div class="space-y-3">
        @foreach($notifications as $n)
        <div class="bg-white rounded-2xl p-5 border-2 shadow-sm transition hover:shadow-md {{ $n->is_read ? 'border-slate-200' : 'border-indigo-300 bg-indigo-50/30' }}" id="notif-{{ $n->id }}">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3 flex-1 min-w-0">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0
                        {{ $n->type == 'grade' ? 'bg-emerald-100' : ($n->type == 'warning' ? 'bg-amber-100' : ($n->type == 'bill' ? 'bg-purple-100' : ($n->type == 'assignment' ? 'bg-blue-100' : 'bg-slate-100'))) }}">
                        @if($n->type == 'grade') 📝
                        @elseif($n->type == 'warning') ⚠️
                        @elseif($n->type == 'bill') 💰
                        @elseif($n->type == 'assignment') 📋
                        @elseif($n->type == 'alert') 🔴
                        @else ℹ️
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="font-bold text-sm text-slate-800">{{ $n->title }}</h4>
                            @if(!$n->is_read)
                            <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-1">{{ $n->message }}</p>
                        <p class="text-[10px] text-slate-400 font-bold mt-1.5">{{ $n->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @if(!$n->is_read)
                <form method="POST" action="{{ route('notifications.mark-read', $n->id) }}" class="flex-shrink-0">
                    @csrf
                    <button type="submit" class="w-8 h-8 bg-indigo-100 hover:bg-indigo-200 text-indigo-600 rounded-lg flex items-center justify-center text-xs transition" title="Tandai dibaca">
                        <i class="fas fa-check"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
    @else
    <div class="bg-white rounded-3xl p-10 border-2 border-dashed border-slate-300 text-center">
        <div class="text-5xl mb-4">🔔</div>
        <h3 class="font-black text-lg text-slate-700 mb-2">Tidak Ada Notifikasi</h3>
        <p class="text-sm text-slate-500">Belum ada notifikasi untuk Anda.</p>
    </div>
    @endif
</div>
@endsection