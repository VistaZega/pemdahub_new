@extends('mobile.layouts.app')

@section('title', 'Surat Edaran Resmi - Guru Mobile')

@section('content')
<div class="space-y-4" x-data="{ 
    selectedLetter: null 
}">
    <!-- Header Title -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-900">Surat Edaran Resmi 📜</h2>
            <p class="text-[11px] text-slate-500 font-bold">Dokumen & Instruksi Resmi Yayasan PEMBDA</p>
        </div>
        <div class="w-10 h-10 rounded-2xl clay-purple flex items-center justify-center text-xl font-black text-white shrink-0">
            📜
        </div>
    </div>

    <!-- Surat Edaran List Cards -->
    <div class="space-y-3">
        @forelse($foundationLetters as $letter)
            @php
                $letterData = [
                    'id' => $letter->id,
                    'letter_number' => $letter->letter_number,
                    'title' => $letter->title,
                    'category' => $letter->category ?? 'Surat Edaran',
                    'content' => $letter->content,
                    'effective_date' => $letter->effective_date ? $letter->effective_date->translatedFormat('d F Y') : ($letter->created_at ? $letter->created_at->translatedFormat('d F Y') : '-'),
                    'deadline_date' => $letter->deadline_date ? $letter->deadline_date->translatedFormat('d F Y') : null,
                    'signatory_name' => $letter->signatory_name ?? 'Yayasan Perguruan PEMBDA',
                    'signatory_position' => $letter->signatory_position ?? 'Ketua / Pengurus Yayasan',
                    'signature_hash' => $letter->signature_hash,
                ];
            @endphp
            <div @click="selectedLetter = {{ json_encode($letterData) }}"
                 class="clay-card p-5 space-y-3 bg-white border-2 border-purple-200 cursor-pointer hover:border-purple-400 transition active:scale-[0.98]">
                <!-- Header Category & Date -->
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800 border border-purple-300 text-[10px] font-black uppercase">
                        #{{ $letter->category ?? 'Surat Edaran' }}
                    </span>
                    <span class="text-[10px] text-slate-500 font-bold flex items-center gap-1">
                        <i class="fa-regular fa-calendar text-purple-600"></i>
                        {{ $letterData['effective_date'] }}
                    </span>
                </div>

                <!-- Letter Number & Title -->
                <div>
                    @if($letter->letter_number)
                        <span class="text-[10px] font-extrabold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-200 block w-max mb-1">
                            No: {{ $letter->letter_number }}
                        </span>
                    @endif
                    <h3 class="text-sm font-black text-slate-900 leading-snug hover:text-purple-600 transition">{{ $letter->title }}</h3>
                </div>

                <!-- Short Content Preview -->
                @if(!empty($letter->content))
                    <div class="text-[11px] text-slate-600 font-semibold leading-relaxed line-clamp-2">
                        {!! strip_tags($letter->content) !!}
                    </div>
                @endif

                <!-- Card Footer & Action -->
                <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[10px] font-extrabold text-slate-500">
                        ✍️ {{ $letter->signatory_position ?? 'Penandatangan' }}
                    </span>

                    <button type="button" 
                            class="py-1.5 px-3 rounded-xl bg-purple-600 text-white text-[10px] font-black hover:bg-purple-700 transition flex items-center gap-1 shadow-xs">
                        <i class="fa-solid fa-book-open"></i> Baca / Preview Dokumen &rarr;
                    </button>
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-3xl">📜</div>
                <p>Belum ada Surat Edaran resmi terbit saat ini.</p>
            </div>
        @endforelse
    </div>

    <!-- PREVIEW / BACA SURAT EDARAN MODAL (3D CLAY MODAL) -->
    <div x-show="selectedLetter !== null" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="display: none;">
        
        <div class="bg-white rounded-3xl p-6 w-full max-w-lg max-h-[85vh] overflow-y-auto shadow-2xl border-4 border-purple-200 space-y-4 relative"
             @click.away="selectedLetter = null">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b-2 border-slate-100 pb-3">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-purple-600 text-white flex items-center justify-center font-black text-sm">
                        📜
                    </div>
                    <div>
                        <h3 class="text-xs font-black text-slate-900 uppercase">DOKUMEN RESMI YAYASAN</h3>
                        <span class="text-[9px] font-bold text-slate-400">Yayasan Perguruan PEMBDA</span>
                    </div>
                </div>
                
                <button @click="selectedLetter = null" 
                        class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 flex items-center justify-center font-black text-sm transition">
                    ✕
                </button>
            </div>

            <!-- Modal Content (Reading View) -->
            <template x-if="selectedLetter">
                <div class="space-y-4">
                    <!-- Title & Letter Number Header -->
                    <div class="text-center space-y-1 bg-purple-50 p-4 rounded-2xl border border-purple-200">
                        <span class="px-3 py-0.5 rounded-full bg-purple-600 text-white text-[9px] font-black uppercase tracking-wider inline-block">
                            <span x-text="selectedLetter.category"></span>
                        </span>
                        
                        <h2 class="text-base font-black text-slate-900 leading-snug pt-1" x-text="selectedLetter.title"></h2>
                        
                        <template x-if="selectedLetter.letter_number">
                            <p class="text-xs font-bold text-purple-700">Nomor: <span x-text="selectedLetter.letter_number"></span></p>
                        </template>

                        <div class="flex items-center justify-center gap-4 text-[10px] font-bold text-slate-500 pt-1">
                            <span>📅 Effective: <strong class="text-slate-800" x-text="selectedLetter.effective_date"></strong></span>
                            <template x-if="selectedLetter.deadline_date">
                                <span>⏳ Batas: <strong class="text-rose-600" x-text="selectedLetter.deadline_date"></strong></span>
                            </template>
                        </div>
                    </div>

                    <!-- Full Letter Body -->
                    <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-200 text-xs text-slate-800 leading-relaxed space-y-2 whitespace-pre-line font-medium min-h-[120px]"
                         x-html="selectedLetter.content">
                    </div>

                    <!-- Signatory & Hash Verification Card -->
                    <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-200 flex items-center justify-between text-xs">
                        <div class="space-y-0.5">
                            <span class="text-[10px] font-bold text-emerald-700 uppercase block" x-text="selectedLetter.signatory_position"></span>
                            <h4 class="font-black text-slate-900" x-text="selectedLetter.signatory_name"></h4>
                        </div>

                        <div class="text-right">
                            <span class="px-2.5 py-1 rounded-full bg-emerald-600 text-white text-[9px] font-black uppercase shadow-xs inline-flex items-center gap-1">
                                <i class="fa-solid fa-certificate"></i> Terverifikasi Digital
                            </span>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Close Action Button -->
            <button @click="selectedLetter = null" 
                    class="clay-btn block w-full py-3 text-center text-white font-black text-xs uppercase tracking-wider shadow-md">
                Tutup Dokumen
            </button>
        </div>
    </div>
</div>
@endsection
