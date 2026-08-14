@extends('mobile.layouts.app')

@section('title', 'Surat Edaran - Informasi Sekolah Mobile')

@section('content')
<div class="space-y-4" x-data="{ activeTab: 'surat' }">
    <!-- Header Title -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-900">Surat Edaran & Informasi 📜</h2>
            <p class="text-[11px] text-slate-500 font-bold">Pengumuman & Dokumen Resmi Sekolah/Yayasan</p>
        </div>
        <div class="w-10 h-10 rounded-2xl clay-purple flex items-center justify-center text-xl font-black text-white shrink-0">
            📜
        </div>
    </div>

    <!-- Category Tabs -->
    <div class="grid grid-cols-2 gap-2 p-1.5 bg-slate-200/70 rounded-2xl border-2 border-slate-300">
        <button @click="activeTab = 'surat'"
                :class="activeTab === 'surat' ? 'bg-purple-600 text-white border-2 border-black shadow-md font-black' : 'text-slate-600 font-extrabold hover:text-slate-900'"
                class="py-2.5 px-3 text-xs transition flex items-center justify-center gap-1.5">
            📜 Surat Edaran ({{ count($foundationLetters) }})
        </button>
        <button @click="activeTab = 'pengumuman'"
                :class="activeTab === 'pengumuman' ? 'bg-blue-600 text-white border-2 border-black shadow-md font-black' : 'text-slate-600 font-extrabold hover:text-slate-900'"
                class="py-2.5 px-3 text-xs transition flex items-center justify-center gap-1.5">
            📢 Pengumuman ({{ count($lmsAnnouncements) + count($newsList) }})
        </button>
    </div>

    <!-- TAB 1: SURAT EDARAN RESMI YAYASAN / SEKOLAH -->
    <div x-show="activeTab === 'surat'" x-transition class="space-y-3">
        @forelse($foundationLetters as $letter)
            <div class="clay-card p-5 space-y-3 bg-white border-2 border-purple-200">
                <!-- Header Category & Date -->
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800 border border-purple-300 text-[10px] font-black uppercase">
                        #{{ $letter->category ?? 'Surat Edaran' }}
                    </span>
                    <span class="text-[10px] text-slate-500 font-bold flex items-center gap-1">
                        <i class="fa-regular fa-calendar text-purple-600"></i>
                        {{ $letter->effective_date ? $letter->effective_date->translatedFormat('d M Y') : ($letter->created_at ? $letter->created_at->translatedFormat('d M Y') : '-') }}
                    </span>
                </div>

                <!-- Letter Number & Title -->
                <div>
                    @if($letter->letter_number)
                        <span class="text-[10px] font-extrabold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-200 block w-max mb-1">
                            No: {{ $letter->letter_number }}
                        </span>
                    @endif
                    <h3 class="text-sm font-black text-slate-900 leading-snug">{{ $letter->title }}</h3>
                </div>

                <!-- Content Preview -->
                @if(!empty($letter->content))
                    <div class="text-[11px] text-slate-600 font-semibold leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-200/80 line-clamp-4">
                        {!! strip_tags($letter->content) !!}
                    </div>
                @endif

                <!-- Footer Signatory & Action -->
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-500 font-bold">
                    @if($letter->signatory_name || $letter->signatory_position)
                        <span class="text-slate-700 font-black flex items-center gap-1">
                            ✍️ {{ $letter->signatory_position ?? 'Ttd' }}: {{ $letter->signatory_name ?? 'Yayasan' }}
                        </span>
                    @else
                        <span>Sekolah Pembda</span>
                    @endif

                    @if($letter->signature_hash || $letter->id)
                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[9px] font-black border border-emerald-300">
                            ✓ Terverifikasi Resmi
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-3xl">📜</div>
                <p>Belum ada Surat Edaran resmi terbit saat ini.</p>
            </div>
        @endforelse
    </div>

    <!-- TAB 2: PENGUMUMAN & BERITA SEKOLAH -->
    <div x-show="activeTab === 'pengumuman'" x-transition class="space-y-3">
        <!-- LMS Announcements -->
        @forelse($lmsAnnouncements as $anc)
            <div class="clay-card p-5 space-y-2 bg-white border-2 border-blue-200">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 border border-blue-300 text-[9px] font-black uppercase">
                        📢 Pengumuman LMS
                    </span>
                    <span class="text-[10px] text-slate-500 font-bold">
                        {{ $anc->created_at ? $anc->created_at->translatedFormat('d M Y') : '-' }}
                    </span>
                </div>

                <h3 class="text-sm font-black text-slate-900 leading-snug">{{ $anc->title }}</h3>

                @if(!empty($anc->content))
                    <p class="text-[11px] text-slate-600 font-semibold leading-relaxed line-clamp-3">
                        {{ strip_tags($anc->content) }}
                    </p>
                @endif

                @if($anc->author)
                    <div class="pt-2 border-t border-slate-100 text-[10px] font-bold text-slate-500">
                        Oleh: <strong class="text-slate-800">{{ $anc->author->name }}</strong>
                    </div>
                @endif
            </div>
        @empty
        @endforelse

        <!-- News / Berita -->
        @forelse($newsList as $news)
            <div class="clay-card p-5 space-y-2 bg-white border-2 border-slate-200">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-300 text-[9px] font-black uppercase">
                        📰 {{ $news->category ?? 'Berita Sekolah' }}
                    </span>
                    <span class="text-[10px] text-slate-500 font-bold">
                        {{ $news->published_at ? $news->published_at->translatedFormat('d M Y') : '-' }}
                    </span>
                </div>

                <h3 class="text-sm font-black text-slate-900 leading-snug">{{ $news->title }}</h3>

                @if(!empty($news->excerpt || $news->content))
                    <p class="text-[11px] text-slate-600 font-semibold leading-relaxed line-clamp-3">
                        {{ strip_tags($news->excerpt ?: $news->content) }}
                    </p>
                @endif
            </div>
        @empty
        @endforelse

        @if(count($lmsAnnouncements) === 0 && count($newsList) === 0)
            <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-3xl">📢</div>
                <p>Belum ada Pengumuman atau Berita Sekolah saat ini.</p>
            </div>
        @endif
    </div>
</div>
@endsection
