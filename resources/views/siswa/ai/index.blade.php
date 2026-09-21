@extends('layouts.siswa')

@section('title', 'Pembda AI — Tutor & Konsultasi Belajar')

@section('content')
<!-- Marked.js, KaTeX & FontAwesome for Markdown, Math & Icons -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js"></script>

<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
    }
    .chat-bubble-ai {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05);
    }
    .chat-bubble-student {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        box-shadow: 0 4px 15px -3px rgba(79, 70, 229, 0.3);
    }
    .prose p { margin-bottom: 0.5rem; }
    .prose p:last-child { margin-bottom: 0; }
    .prose code { background: #f1f5f9; padding: 2px 6px; borderRadius: 6px; color: #475569; font-size: 0.85em; }
    .prose pre { background: #0f172a; color: #f8fafc; padding: 12px; rounded: 12px; overflow-x: auto; margin-top: 8px; margin-bottom: 8px; }
    .animate-bounce-slow { animation: bounce 2s infinite; }
    .pulse-glow { box-shadow: 0 0 20px rgba(124, 58, 237, 0.4); }
</style>

<div class="space-y-6 pb-12" x-data="aiChatApp()">
    
    <!-- Top Header Banner (Futuristic Glassmorphic Theme) -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-purple-950 rounded-3xl p-6 text-white shadow-2xl relative overflow-hidden border border-white/10">
        <!-- Glowing Ambient Lighting Background -->
        <div class="absolute -right-10 -top-10 w-64 h-64 bg-purple-600/30 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-1/3 -bottom-10 w-48 h-48 bg-indigo-500/20 rounded-full blur-2xl pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <!-- AI Avatar Sphere -->
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-purple-500 via-indigo-500 to-pink-500 p-0.5 shadow-lg shrink-0 pulse-glow">
                    <div class="w-full h-full bg-slate-950 rounded-[14px] flex items-center justify-center text-white text-2xl relative">
                        <i class="fas fa-robot text-purple-300"></i>
                        <span class="absolute top-1 right-1 w-3 h-3 bg-emerald-400 border-2 border-slate-950 rounded-full"></span>
                    </div>
                </div>

                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-black tracking-tight text-white">Pembda AI Studio</h1>
                        <span class="px-3 py-0.5 rounded-full text-[11px] font-black bg-purple-500/30 border border-purple-400/40 text-purple-200">
                            v2.0 Interactive
                        </span>
                    </div>
                    <p class="text-indigo-200/90 text-xs sm:text-sm mt-1">Asisten Pembimbing Cerdas 24/7 untuk Perguruan PEMBDA Nias</p>
                </div>
            </div>

            <!-- Student Profile Badge & Quota Counter -->
            <div class="flex items-center gap-3 bg-white/10 backdrop-blur-xl p-3.5 rounded-2xl border border-white/15 shrink-0">
                <!-- Photo Profile Siswa -->
                <div class="relative">
                    <img src="{{ $student->photo_url }}" 
                         alt="{{ $student->full_name }}" 
                         class="w-11 h-11 rounded-xl object-cover border-2 border-purple-300/80 shadow-sm"
                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($student->full_name) }}&background=6366f1&color=ffffff&bold=true';">
                    <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-emerald-500 border-2 border-slate-900 rounded-full" title="Siswa Online"></span>
                </div>

                <div class="text-left">
                    <p class="text-xs font-black text-white truncate max-w-[140px]">{{ $student->full_name }}</p>
                    <p class="text-[10px] font-bold text-purple-200 uppercase tracking-wider">
                        {{ $student->school->name ?? 'Pembda HUB' }}
                    </p>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="text-[10px] font-black text-amber-300">
                            ⚡ <span x-text="usageInfo.remaining">{{ $usageInfo['remaining'] }}</span>/{{ $usageInfo['limit'] }} Pesan
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Workspace Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Sidebar Kiri: Pilihan Mode & Sesi Percakapan (4 cols) -->
        <div class="lg:col-span-4 space-y-4">
            
            <!-- Selector Mode Interaktif (3D Cards) -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200/90 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">Pilih Mode Interaksi</h3>
                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-md bg-purple-100 text-purple-700">3 Mode AI</span>
                </div>
                
                <div class="grid grid-cols-1 gap-2.5">
                    <!-- Mode 1: Tutor Akademik -->
                    <button type="button" @click="startNewConversation('tutor')"
                            class="group relative flex items-center gap-3.5 p-3.5 rounded-2xl border transition-all duration-200 text-left cursor-pointer overflow-hidden"
                            :class="activeMode === 'tutor' 
                                ? 'bg-gradient-to-r from-purple-600 to-indigo-600 text-white border-transparent shadow-md' 
                                : 'border-slate-200/90 hover:border-purple-300 hover:bg-purple-50/50 text-slate-700'">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-transform group-hover:scale-110"
                             :class="activeMode === 'tutor' ? 'bg-white/20 text-white' : 'bg-purple-100 text-purple-700'">
                            <i class="fas fa-graduation-cap text-base"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-black">Tutor Akademik (Q&A)</p>
                            <p class="text-[11px] truncate" :class="activeMode === 'tutor' ? 'text-purple-100' : 'text-slate-500'">Soal, Matematika, IPA & Kejuruan</p>
                        </div>
                        <i class="fas fa-chevron-right text-xs" :class="activeMode === 'tutor' ? 'text-white' : 'text-slate-300'"></i>
                    </button>

                    <!-- Mode 2: Konsultasi BK & Karir -->
                    <button type="button" @click="startNewConversation('bk_consultation')"
                            class="group relative flex items-center gap-3.5 p-3.5 rounded-2xl border transition-all duration-200 text-left cursor-pointer overflow-hidden"
                            :class="activeMode === 'bk_consultation' 
                                ? 'bg-gradient-to-r from-pink-600 to-rose-600 text-white border-transparent shadow-md' 
                                : 'border-slate-200/90 hover:border-pink-300 hover:bg-pink-50/50 text-slate-700'">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-transform group-hover:scale-110"
                             :class="activeMode === 'bk_consultation' ? 'bg-white/20 text-white' : 'bg-pink-100 text-pink-700'">
                            <i class="fas fa-user-nurse text-base"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-black">Konsultasi BK & Karir</p>
                            <p class="text-[11px] truncate" :class="activeMode === 'bk_consultation' ? 'text-pink-100' : 'text-slate-500'">Curhat belajar, Kuliah & DUDI</p>
                        </div>
                        <i class="fas fa-chevron-right text-xs" :class="activeMode === 'bk_consultation' ? 'text-white' : 'text-slate-300'"></i>
                    </button>

                    <!-- Mode 3: Asisten Belajar LMS -->
                    <button type="button" @click="startNewConversation('lms_assistant')"
                            class="group relative flex items-center gap-3.5 p-3.5 rounded-2xl border transition-all duration-200 text-left cursor-pointer overflow-hidden"
                            :class="activeMode === 'lms_assistant' 
                                ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white border-transparent shadow-md' 
                                : 'border-slate-200/90 hover:border-emerald-300 hover:bg-emerald-50/50 text-slate-700'">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-transform group-hover:scale-110"
                             :class="activeMode === 'lms_assistant' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-700'">
                            <i class="fas fa-book-open text-base"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-black">Asisten Belajar LMS</p>
                            <p class="text-[11px] truncate" :class="activeMode === 'lms_assistant' ? 'text-emerald-100' : 'text-slate-500'">Rangkuman modul & kuis mandiri</p>
                        </div>
                        <i class="fas fa-chevron-right text-xs" :class="activeMode === 'lms_assistant' ? 'text-white' : 'text-slate-300'"></i>
                    </button>
                </div>
            </div>

            <!-- List Riwayat Percakapan (Session History) -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200/90 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">Riwayat Percakapan</h3>
                    <button type="button" @click="startNewConversation(activeMode)" class="text-xs font-bold text-purple-600 hover:text-purple-800 transition flex items-center gap-1 cursor-pointer">
                        <i class="fas fa-plus-circle text-xs"></i>
                        <span>Sesi Baru</span>
                    </button>
                </div>

                <div class="space-y-1.5 max-h-[350px] overflow-y-auto pr-1 no-scrollbar">
                    @forelse($conversations as $conv)
                        <div class="group flex items-center justify-between p-3 rounded-2xl text-xs transition border cursor-pointer {{ $activeConversation && $activeConversation->id == $conv->id ? 'bg-purple-50/90 border-purple-300 text-purple-950 font-bold shadow-xs' : 'border-transparent text-slate-600 hover:bg-slate-50' }}">
                            <a href="{{ route('siswa.ai.index', ['conversation_id' => $conv->id]) }}" class="flex items-center gap-3 flex-1 min-w-0 pr-2">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs shrink-0 {{ $conv->mode == 'bk_consultation' ? 'bg-pink-100 text-pink-600' : ($conv->mode == 'lms_assistant' ? 'bg-emerald-100 text-emerald-600' : 'bg-purple-100 text-purple-600') }}">
                                    <i class="fas {{ $conv->mode == 'bk_consultation' ? 'fa-heart' : ($conv->mode == 'lms_assistant' ? 'fa-book' : 'fa-robot') }}"></i>
                                </div>
                                <span class="truncate">{{ $conv->title }}</span>
                            </a>
                            <form action="{{ route('siswa.ai.destroy', $conv->id) }}" method="POST" onsubmit="return confirm('Hapus sesi percakapan ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="opacity-0 group-hover:opacity-100 p-1.5 rounded-lg hover:bg-rose-100 text-rose-600 transition cursor-pointer" title="Hapus">
                                    <i class="fas fa-trash-alt text-[10px]"></i>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400">
                            <i class="fas fa-comments text-3xl mb-2 opacity-40"></i>
                            <p class="text-xs font-semibold">Belum ada riwayat percakapan.</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Pilih salah satu mode di atas untuk memulai!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Chat Canvas Utama (8 cols) -->
        <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200/90 shadow-sm flex flex-col h-[700px] overflow-hidden relative">
            
            <!-- Top Control Bar -->
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 backdrop-blur-md flex items-center justify-between shrink-0 z-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-purple-600 via-indigo-600 to-violet-700 text-white flex items-center justify-center text-lg shadow-sm">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-900" x-text="conversationTitle">
                            {{ $activeConversation ? $activeConversation->title : 'Sesi Baru Pembda AI' }}
                        </h2>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                            <span class="text-[11px] font-semibold text-slate-500" x-text="getModeLabel()">
                                {{ $activeConversation ? ($activeConversation->mode == 'bk_consultation' ? 'Mode BK & Karir' : ($activeConversation->mode == 'lms_assistant' ? 'Mode Asisten LMS' : 'Mode Tutor Q&A')) : 'Mode Tutor Q&A' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons: Clear Screen & Speech Voice Status -->
                <div class="flex items-center gap-2">
                    <button type="button" @click="clearScreen()" class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer" title="Bersihkan Layar">
                        <i class="fas fa-broom text-[11px] text-slate-500"></i>
                        <span class="hidden sm:inline">Bersihkan</span>
                    </button>

                    <button type="button" @click="toggleSpeechVoice()" 
                            class="px-3 py-1.5 rounded-xl border text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
                            :class="autoSpeech ? 'bg-purple-100 border-purple-300 text-purple-700' : 'border-slate-200 text-slate-500 hover:bg-slate-100'">
                        <i class="fas" :class="autoSpeech ? 'fa-volume-high text-purple-600' : 'fa-volume-xmark'"></i>
                        <span class="hidden sm:inline" x-text="autoSpeech ? 'Suara ON' : 'Suara OFF'"></span>
                    </button>
                </div>
            </div>

            <!-- Messages Stream Box -->
            <div class="flex-1 p-6 overflow-y-auto space-y-6 bg-gradient-to-b from-slate-50/50 to-white" id="chat-messages-container" x-ref="messagesContainer">
                
                @if(empty($messages) && !$activeConversation)
                    <!-- Welcoming Screen with Categorized Chips -->
                    <div class="max-w-lg mx-auto my-6 text-center space-y-5">
                        <!-- Big Avatar Sphere -->
                        <div class="relative w-20 h-20 mx-auto">
                            <div class="w-full h-full rounded-3xl bg-gradient-to-tr from-purple-600 via-indigo-600 to-pink-500 p-1 shadow-xl animate-bounce-slow">
                                <div class="w-full h-full bg-white rounded-[22px] flex items-center justify-center text-3xl text-purple-600">
                                    <i class="fas fa-sparkles"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-black text-slate-900">Halo, {{ explode(' ', $student->full_name)[0] }}! 👋</h3>
                            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Saya Asisten Pintar Pembda AI. Pilih kategori pertanyaan atau ketik langsung pertanyaan kamu di bawah ini:</p>
                        </div>

                        <!-- Categorized Prompt Chips -->
                        <div class="space-y-3 pt-2 text-left">
                            <p class="text-[11px] font-black uppercase tracking-wider text-slate-400 text-center">Contoh Pertanyaan Populer</p>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                <button @click="useQuickPrompt('Bagaimana cara menghitung Luas Permukaan Tabung? Jelaskan langkahnya.')" class="p-3 rounded-2xl bg-white border border-purple-200/80 hover:border-purple-500 hover:shadow-md transition text-slate-700 text-left flex items-start gap-2.5 cursor-pointer">
                                    <span class="text-base">🧮</span>
                                    <div>
                                        <p class="font-extrabold text-slate-900">Matematika & Sains</p>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Rumus Luas Permukaan Tabung</p>
                                    </div>
                                </button>

                                <button @click="useQuickPrompt('Apa saja 5 jurusan SMK di PembdaHUB dan apa keunggulannya?')" class="p-3 rounded-2xl bg-white border border-indigo-200/80 hover:border-indigo-500 hover:shadow-md transition text-slate-700 text-left flex items-start gap-2.5 cursor-pointer">
                                    <span class="text-base">🏫</span>
                                    <div>
                                        <p class="font-extrabold text-slate-900">Info PembdaHUB</p>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Daftar Jurusan SMK & Fitur Sekolah</p>
                                    </div>
                                </button>

                                <button @click="useQuickPrompt('Saya bingung memilih antara lanjut Kuliah atau kerja di DUDI industri, mohon saran.')" class="p-3 rounded-2xl bg-white border border-pink-200/80 hover:border-pink-500 hover:shadow-md transition text-slate-700 text-left flex items-start gap-2.5 cursor-pointer">
                                    <span class="text-base">🎓</span>
                                    <div>
                                        <p class="font-extrabold text-slate-900">Karir & BK</p>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Saran Kuliah vs Kerja DUDI</p>
                                    </div>
                                </button>

                                <button @click="useQuickPrompt('Bagaimana cara melihat nilai rapor dan mengikuti ujian CBT online?')" class="p-3 rounded-2xl bg-white border border-emerald-200/80 hover:border-emerald-500 hover:shadow-md transition text-slate-700 text-left flex items-start gap-2.5 cursor-pointer">
                                    <span class="text-base">📜</span>
                                    <div>
                                        <p class="font-extrabold text-slate-900">Panduan Fitur</p>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Cara Akses Rapor & Ujian CBT</p>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Render Dynamic Messages -->
                <template x-for="(msg, index) in messageList" :key="index">
                    <div :class="msg.sender === 'student' ? 'flex justify-end' : 'flex justify-start'">
                        <div class="flex gap-3 max-w-[90%] sm:max-w-[82%]" :class="msg.sender === 'student' ? 'flex-row-reverse' : 'flex-row'">
                            
                            <!-- Avatar Siswa vs AI -->
                            <template x-if="msg.sender === 'student'">
                                <img src="{{ $student->photo_url }}" 
                                     alt="{{ $student->full_name }}" 
                                     class="w-9 h-9 rounded-2xl object-cover border-2 border-indigo-400 shadow-sm shrink-0"
                                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($student->full_name) }}&background=6366f1&color=ffffff&bold=true';">
                            </template>

                            <template x-if="msg.sender === 'ai'">
                                <div class="w-9 h-9 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center text-sm shadow-md shrink-0 font-bold border border-purple-300">
                                    <i class="fas fa-robot"></i>
                                </div>
                            </template>

                            <!-- Message Content & Interactive Toolbars -->
                            <div class="space-y-1.5 flex-1 min-w-0">
                                <!-- Bubble Text -->
                                <div class="p-4 rounded-3xl text-xs sm:text-sm leading-relaxed"
                                     :class="msg.sender === 'student' 
                                        ? 'chat-bubble-student text-white rounded-tr-none font-medium' 
                                        : 'chat-bubble-ai text-slate-800 rounded-tl-none prose prose-xs max-w-none'">
                                    <div class="message-body" x-html="renderMarkdown(msg.message)"></div>
                                </div>

                                <!-- Action Toolbar for AI Messages -->
                                <template x-if="msg.sender === 'ai'">
                                    <div class="flex items-center gap-3 px-1 text-[11px] text-slate-400 font-semibold">
                                        <!-- Copy Button -->
                                        <button type="button" @click="copyToClipboard(msg.message)" class="hover:text-purple-600 transition flex items-center gap-1 cursor-pointer" title="Salin Pesan">
                                            <i class="fas fa-copy text-[10px]"></i>
                                            <span>Salin</span>
                                        </button>

                                        <!-- Speak Button (TTS) -->
                                        <button type="button" @click="speakText(msg.message)" class="hover:text-purple-600 transition flex items-center gap-1 cursor-pointer" title="Dengarkan Suara AI">
                                            <i class="fas fa-volume-high text-[10px]"></i>
                                            <span>Dengarkan</span>
                                        </button>

                                        <!-- Like / Dislike Feedback -->
                                        <div class="flex items-center gap-2 border-l border-slate-200 pl-3">
                                            <button type="button" @click="msg.liked = !msg.liked" :class="msg.liked ? 'text-emerald-600' : 'hover:text-slate-600'" class="transition cursor-pointer">
                                                <i class="fas fa-thumbs-up text-[10px]"></i>
                                            </button>
                                            <button type="button" @click="msg.disliked = !msg.disliked" :class="msg.disliked ? 'text-rose-600' : 'hover:text-slate-600'" class="transition cursor-pointer">
                                                <i class="fas fa-thumbs-down text-[10px]"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                        </div>
                    </div>
                </template>

                <!-- Animated Loading Indicator -->
                <div x-show="isSending" class="flex justify-start">
                    <div class="flex items-center gap-3 p-4 bg-white border border-purple-200 rounded-3xl rounded-tl-none text-xs text-purple-800 shadow-md">
                        <div class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-purple-600 animate-bounce"></span>
                            <span class="w-2 h-2 rounded-full bg-indigo-600 animate-bounce [animation-delay:0.2s]"></span>
                            <span class="w-2 h-2 rounded-full bg-pink-600 animate-bounce [animation-delay:0.4s]"></span>
                        </div>
                        <span class="font-extrabold">Pembda AI sedang menganalisis jawaban...</span>
                    </div>
                </div>
            </div>

            <!-- Chat Bottom Input Form & Speech Recognition -->
            <div class="p-4 bg-white border-t border-slate-100 shrink-0 z-10">
                <form @submit.prevent="submitMessage()" class="flex items-end gap-2.5">
                    
                    <!-- Textarea Box with Mic Button -->
                    <div class="flex-1 relative bg-slate-50 rounded-2xl border border-slate-200 focus-within:border-purple-500 focus-within:ring-2 focus-within:ring-purple-200 transition">
                        <textarea x-ref="promptInput" x-model="userInput" 
                                  @keydown.enter.prevent="if(!$event.shiftKey) submitMessage()"
                                  placeholder="Tanyakan soal, konsep materi, atau curhat belajar... (Enter untuk Kirim)"
                                  rows="2"
                                  class="w-full p-3.5 pr-10 rounded-2xl bg-transparent text-xs sm:text-sm outline-none resize-none text-slate-800"
                                  :disabled="isSending || usageInfo.remaining <= 0"></textarea>

                        <!-- Mic Button for Speech-to-Text -->
                        <button type="button" @click="toggleVoiceInput()" 
                                class="absolute right-3 bottom-3 p-1.5 rounded-xl transition cursor-pointer"
                                :class="isListening ? 'bg-rose-500 text-white animate-pulse' : 'text-slate-400 hover:text-purple-600 hover:bg-purple-100'"
                                title="Bicara via Suara (Speech-to-Text)">
                            <i class="fas" :class="isListening ? 'fa-microphone-slash' : 'fa-microphone'"></i>
                        </button>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            :disabled="isSending || !userInput.trim() || usageInfo.remaining <= 0"
                            class="px-5 py-3.5 rounded-2xl bg-gradient-to-r from-purple-600 via-indigo-600 to-violet-700 text-white font-black text-xs shadow-md hover:from-purple-700 hover:to-violet-800 disabled:opacity-40 disabled:cursor-not-allowed transition flex items-center gap-2 shrink-0 cursor-pointer">
                        <span class="hidden sm:inline">Kirim</span>
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
function aiChatApp() {
    return {
        activeConversationId: {{ $activeConversation ? $activeConversation->id : 'null' }},
        activeMode: '{{ $activeConversation ? $activeConversation->mode : "tutor" }}',
        conversationTitle: '{{ $activeConversation ? addslashes($activeConversation->title) : "Sesi Baru Pembda AI" }}',
        userInput: '',
        isSending: false,
        autoSpeech: false,
        isListening: false,
        recognition: null,
        usageInfo: {
            remaining: {{ $usageInfo['remaining'] }},
            limit: {{ $usageInfo['limit'] }}
        },
        messageList: [
            @if(!empty($messages))
                @foreach($messages as $m)
                    { sender: '{{ $m->sender }}', message: `{!! addslashes($m->message) !!}`, liked: false, disliked: false },
                @endforeach
            @endif
        ],

        init() {
            this.scrollToBottom();
            this.renderMathFormulas();
            this.initSpeechRecognition();
        },

        getModeLabel() {
            if (this.activeMode === 'bk_consultation') return 'Mode BK & Karir';
            if (this.activeMode === 'lms_assistant') return 'Mode Asisten LMS';
            return 'Mode Tutor Q&A';
        },

        startNewConversation(mode) {
            this.activeMode = mode;
            this.activeConversationId = null;
            this.messageList = [];
            this.conversationTitle = (mode === 'bk_consultation') ? 'Konsultasi BK & Karir' : (mode === 'lms_assistant' ? 'Asisten Belajar LMS' : 'Tutor Pelajaran Q&A');
            this.$nextTick(() => {
                if (this.$refs.promptInput) this.$refs.promptInput.focus();
            });
        },

        useQuickPrompt(text) {
            this.userInput = text;
            this.submitMessage();
        },

        clearScreen() {
            if (confirm('Bersihkan percakapan di layar saat ini?')) {
                this.messageList = [];
            }
        },

        toggleSpeechVoice() {
            this.autoSpeech = !this.autoSpeech;
        },

        speakText(text) {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                // Clean markdown tags for TTS
                const cleanText = text.replace(/[#*`_$~-]/g, '');
                const utterance = new SpeechSynthesisUtterance(cleanText);
                utterance.lang = 'id-ID';
                utterance.rate = 1.0;
                window.speechSynthesis.speak(utterance);
            } else {
                alert('Browser Anda belum mendukung fitur pembaca suara (Text-to-Speech).');
            }
        },

        initSpeechRecognition() {
            if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
                const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                this.recognition = new SpeechRecognition();
                this.recognition.lang = 'id-ID';
                this.recognition.interimResults = false;

                this.recognition.onresult = (event) => {
                    const transcript = event.results[0][0].transcript;
                    this.userInput += (this.userInput ? ' ' : '') + transcript;
                    this.isListening = false;
                };

                this.recognition.onerror = () => {
                    this.isListening = false;
                };

                this.recognition.onend = () => {
                    this.isListening = false;
                };
            }
        },

        toggleVoiceInput() {
            if (!this.recognition) {
                alert('Browser Anda belum mendukung pengenalan suara (Speech-to-Text).');
                return;
            }

            if (this.isListening) {
                this.recognition.stop();
                this.isListening = false;
            } else {
                this.recognition.start();
                this.isListening = true;
            }
        },

        copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Jawaban Pembda AI berhasil disalin ke clipboard!');
            }).catch(err => {
                console.error('Gagal menyalin:', err);
            });
        },

        renderMarkdown(content) {
            if (typeof marked !== 'undefined') {
                return marked.parse(content);
            }
            return content.replace(/\n/g, '<br>');
        },

        renderMathFormulas() {
            this.$nextTick(() => {
                if (window.renderMathInElement && this.$refs.messagesContainer) {
                    renderMathInElement(this.$refs.messagesContainer, {
                        delimiters: [
                            {left: '$$', right: '$$', display: true},
                            {left: '$', right: '$', display: false},
                            {left: '\\(', right: '\\)', display: false},
                            {left: '\\[', right: '\\]', display: true}
                        ]
                    });
                }
            });
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.messagesContainer;
                if (el) {
                    el.scrollTop = el.scrollHeight;
                }
            });
        },

        async submitMessage() {
            const text = this.userInput.trim();
            if (!text || this.isSending) return;

            // Append student prompt
            this.messageList.push({ sender: 'student', message: text });
            this.userInput = '';
            this.isSending = true;
            this.scrollToBottom();

            try {
                const response = await fetch('{{ route("siswa.ai.send") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        conversation_id: this.activeConversationId,
                        mode: this.activeMode,
                        message: text
                    })
                });

                const result = await response.json();

                if (result.success) {
                    this.activeConversationId = result.conversation_id;
                    const aiText = result.ai_message.message;
                    this.messageList.push({ sender: 'ai', message: aiText, liked: false, disliked: false });
                    
                    if (result.usage) {
                        this.usageInfo.remaining = result.usage.remaining;
                    }

                    this.renderMathFormulas();

                    // If auto speech is enabled, speak out response
                    if (this.autoSpeech) {
                        this.speakText(aiText);
                    }
                } else {
                    alert(result.message || 'Terjadi kesalahan saat memproses jawaban AI.');
                }
            } catch (err) {
                console.error(err);
                alert('Gagal terhubung ke server Pembda AI. Silakan periksa koneksi internet Anda.');
            } finally {
                this.isSending = false;
                this.scrollToBottom();
            }
        }
    }
}
</script>
@endsection
