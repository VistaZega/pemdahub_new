@extends('layouts.siswa')

@section('title', 'Pembda AI — Studio Asisten Belajar & Konsultasi')

@section('content')
<!-- Google Fonts: JetBrains Mono & Plus Jakarta Sans -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700;800&display=swap" rel="stylesheet">
<!-- Marked.js & KaTeX for Markdown & Math -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js"></script>

<style>
    .font-mono-code {
        font-family: 'JetBrains Mono', monospace;
    }

    @keyframes bubbleSlideIn {
        from {
            opacity: 0;
            transform: translateY(8px) scale(0.98);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    /* Tactile Solid Drop-Shadow Buttons */
    .btn-tactile-red {
        background-color: #ff3823 !important;
        color: #ffffff !important;
        border: 2px solid #121316 !important;
        box-shadow: 3.5px 3.5px 0px #121316 !important;
        transition: all 0.15s ease;
    }
    .btn-tactile-red:hover {
        transform: translate(1.5px, 1.5px);
        box-shadow: 2px 2px 0px #121316 !important;
    }

    .btn-tactile-yellow {
        background-color: #fde047 !important;
        color: #121316 !important;
        border: 2px solid #121316 !important;
        box-shadow: 3.5px 3.5px 0px #121316 !important;
        transition: all 0.15s ease;
    }
    .btn-tactile-yellow:hover {
        transform: translate(1.5px, 1.5px);
        box-shadow: 2px 2px 0px #121316 !important;
    }

    .btn-tactile-green {
        background-color: #10b981 !important;
        color: #ffffff !important;
        border: 2px solid #121316 !important;
        box-shadow: 3.5px 3.5px 0px #121316 !important;
        transition: all 0.15s ease;
    }
    .btn-tactile-green:hover {
        transform: translate(1.5px, 1.5px);
        box-shadow: 2px 2px 0px #121316 !important;
    }

    .btn-tactile-white {
        background-color: #ffffff !important;
        color: #121316 !important;
        border: 2px solid #121316 !important;
        box-shadow: 3.5px 3.5px 0px #121316 !important;
        transition: all 0.15s ease;
    }
    .btn-tactile-white:hover {
        transform: translate(1.5px, 1.5px);
        box-shadow: 2px 2px 0px #121316 !important;
    }

    /* Dedicated Mode Icon Box - High Contrast Yellow Icon on Dark Charcoal Box */
    .mode-icon-box {
        background-color: #121316 !important;
        border: 2px solid #121316 !important;
        width: 38px !important;
        height: 38px !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        box-shadow: 2px 2px 0px rgba(0,0,0,0.15) !important;
    }
    .mode-icon-box i {
        color: #fde047 !important;
        font-size: 16px !important;
    }

    /* AI Avatar Box for Chat Stream */
    .ai-avatar-box {
        background-color: #121316 !important;
        border: 2px solid #121316 !important;
        width: 40px !important;
        height: 40px !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        box-shadow: 2px 2px 0px #121316 !important;
    }
    .ai-avatar-box i {
        color: #fde047 !important;
        font-size: 16px !important;
    }

    /* Dotted Graph Paper Texture Background */
    .graph-paper-box {
        background-color: #f6f4ee;
        background-image: radial-gradient(#d1cebe 1.2px, transparent 1.2px);
        background-size: 14px 14px;
    }

    /* Tactile Card Frame */
    .tactile-card {
        background: #ffffff;
        border: 2px solid #121316;
        border-radius: 20px;
        box-shadow: 4px 4px 0px #121316;
    }

    /* Student Question Message Bubble - Sleek Vibrant Indigo/Purple Gradient (NO plain black background or black text) */
    .chat-student-bubble {
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #7c3aed 100%) !important;
        color: #ffffff !important;
        border: 2px solid #121316 !important;
        box-shadow: 3.5px 3.5px 0px #121316 !important;
        animation: bubbleSlideIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    .chat-student-bubble * {
        color: #ffffff !important;
    }
    .chat-student-bubble code {
        background-color: #121316 !important;
        color: #fde047 !important;
        border: 1px solid #121316 !important;
    }

    /* Force High Contrast Readability for AI Message Bubble */
    .chat-ai-bubble {
        background-color: #ffffff !important;
        color: #121316 !important;
        border: 2px solid #121316 !important;
        box-shadow: 4px 4px 0px #121316 !important;
        min-width: 120px !important;
        animation: bubbleSlideIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    .chat-ai-bubble * {
        color: #121316 !important;
    }
    .chat-ai-bubble code {
        background-color: #ede9df !important;
        color: #121316 !important;
        font-weight: 700 !important;
    }
    .chat-ai-bubble pre {
        background-color: #121316 !important;
        color: #fde047 !important;
        padding: 12px !important;
        border-radius: 12px !important;
        border: 1.5px solid #121316 !important;
    }
    .chat-ai-bubble pre * {
        color: #fde047 !important;
    }

    .prose p { margin-bottom: 0.5rem; }
    .prose p:last-child { margin-bottom: 0; }
</style>

<div class="space-y-6 pb-12" x-data="aiChatApp()">
    
    <!-- Top Hero Header (DesainPake AI Tactile Card dengan Corner Framing ⌜ ⌟) -->
    <div class="relative bg-white border-2 border-[#121316] rounded-3xl p-6 shadow-[6px_6px_0px_#121316] overflow-hidden">
        <!-- Corner Crosshairs (Larger & Crisp Black) -->
        <span class="absolute top-2 left-3 text-[#121316] font-mono-code text-base font-black select-none">⌜</span>
        <span class="absolute top-2 right-3 text-[#121316] font-mono-code text-base font-black select-none">⌝</span>
        <span class="absolute bottom-2 left-3 text-[#121316] font-mono-code text-base font-black select-none">⌞</span>
        <span class="absolute bottom-2 right-3 text-[#121316] font-mono-code text-base font-black select-none">⌟</span>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            
            <!-- Left Info (Rapat Kiri Murni) -->
            <div class="flex-1">
                <div class="text-[11px] font-mono-code font-extrabold text-[#ff3823] uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                    <span class="text-base">✱</span>
                    <span>NGODING & ASISTEN BELAJAR AI SISWA</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-[#121316] tracking-tight leading-tight flex items-center gap-2 flex-wrap">
                    <span>Pembda</span>
                    <span class="bg-[#fde047] text-[#121316] px-2.5 py-0.5 rounded-lg border-2 border-[#121316] shadow-[2px_2px_0px_#121316]">AI Studio</span>
                </h1>
                <p class="text-xs sm:text-sm text-[#4b5563] font-semibold mt-1">Tutor Pelajaran, Konsultasi BK/Karir, dan Pendamping LMS Terpadu</p>
            </div>

            <!-- Right Student Profile Photo Badge & Daily Quota -->
            <div class="flex items-center gap-3 bg-[#faf3e0] border-2 border-[#121316] p-3.5 rounded-2xl shadow-[3.5px_3.5px_0px_#121316] shrink-0">
                <!-- Foto Profil Siswa -->
                <div class="w-12 h-12 rounded-xl bg-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316] p-0.5 shrink-0 overflow-hidden">
                    <img src="{{ $student->photo_url }}" 
                         alt="{{ $student->full_name }}" 
                         class="w-full h-full object-cover rounded-lg"
                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($student->full_name) }}&background=121316&color=ffffff&bold=true';">
                </div>

                <div class="text-left font-mono-code">
                    <p class="text-xs font-black text-[#121316] truncate max-w-[150px]">{{ $student->full_name }}</p>
                    <p class="text-[10px] font-extrabold text-[#ff3823] uppercase truncate max-w-[150px]">
                        {{ $student->school->name ?? 'PEMBDA HUB' }}
                    </p>
                    <div class="mt-0.5 inline-block text-[10px] font-extrabold bg-[#121316] text-[#fde047] px-2 py-0.5 rounded-md border border-[#121316]">
                        ⚡ <span x-text="usageInfo.remaining">{{ $usageInfo['remaining'] }}</span>/{{ $usageInfo['limit'] }} Pesan Harian
                    </div>
                </div>
            </div>

        </div>

        <!-- Terminal Prompt Line Bar -->
        <div class="mt-5 bg-[#121316] text-[#fde047] font-mono-code text-xs p-3.5 rounded-xl border-2 border-[#121316] flex items-center justify-between gap-2 shadow-inner">
            <div class="flex items-center gap-2 truncate">
                <span class="text-[#ff3823] font-bold">&rsaquo;</span>
                <span class="truncate text-[#fde047]">pembda_ai_core --mode=<span x-text="activeMode">tutor</span> --student="{{ strtolower(explode(' ', $student->full_name)[0]) }}" --status=ONLINE</span>
            </div>
            <div class="flex items-center gap-1.5 shrink-0 text-[10px]">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="font-bold text-white uppercase">Ready</span>
            </div>
        </div>
    </div>

    <!-- Main Workspace Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Sidebar Kiri: Tactile Mode Selector & Riwayat Sesi (4 cols) -->
        <div class="lg:col-span-4 space-y-4">
            
            <!-- Mode Selector Cards -->
            <div class="tactile-card p-5 space-y-3">
                <div class="flex items-center justify-between font-mono-code">
                    <h3 class="text-xs font-black uppercase text-[#121316] tracking-wider">&bull; MODE INTERAKSI &bull;</h3>
                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded bg-[#fde047] text-[#121316] border border-[#121316]">3 Pilihan</span>
                </div>

                <div class="grid grid-cols-1 gap-2.5">
                    <!-- Mode 1: Tutor Akademik Q&A -->
                    <button type="button" @click="startNewConversation('tutor')"
                            class="flex items-center gap-3.5 p-3.5 rounded-2xl transition-all duration-150 text-left cursor-pointer"
                            :class="activeMode === 'tutor' 
                                ? 'btn-tactile-red font-black' 
                                : 'btn-tactile-white'">
                        <div class="mode-icon-box">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-black uppercase tracking-tight" :class="activeMode === 'tutor' ? 'text-white' : 'text-[#121316]'">TUTOR AKADEMIK Q&A</p>
                            <p class="text-[11px] truncate font-medium" :class="activeMode === 'tutor' ? 'text-white' : 'text-[#4b5563]'">Bimbingan Matematika, IPA & Kejuruan</p>
                        </div>
                        <i class="fas fa-arrow-right text-xs" :class="activeMode === 'tutor' ? 'text-white' : 'text-[#121316]'"></i>
                    </button>

                    <!-- Mode 2: Konsultasi BK & Karir -->
                    <button type="button" @click="startNewConversation('bk_consultation')"
                            class="flex items-center gap-3.5 p-3.5 rounded-2xl transition-all duration-150 text-left cursor-pointer"
                            :class="activeMode === 'bk_consultation' 
                                ? 'btn-tactile-yellow font-black' 
                                : 'btn-tactile-white'">
                        <div class="mode-icon-box">
                            <i class="fas fa-user-nurse"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-black uppercase tracking-tight text-[#121316]">KONSULTASI BK & KARIR</p>
                            <p class="text-[11px] truncate font-medium text-[#121316]">Curhat belajar, Kuliah & Kerja DUDI</p>
                        </div>
                        <i class="fas fa-arrow-right text-xs text-[#121316]"></i>
                    </button>

                    <!-- Mode 3: Asisten Belajar LMS -->
                    <button type="button" @click="startNewConversation('lms_assistant')"
                            class="flex items-center gap-3.5 p-3.5 rounded-2xl transition-all duration-150 text-left cursor-pointer"
                            :class="activeMode === 'lms_assistant' 
                                ? 'btn-tactile-green font-black' 
                                : 'btn-tactile-white'">
                        <div class="mode-icon-box">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-black uppercase tracking-tight" :class="activeMode === 'lms_assistant' ? 'text-white' : 'text-[#121316]'">ASISTEN BELAJAR LMS</p>
                            <p class="text-[11px] truncate font-medium" :class="activeMode === 'lms_assistant' ? 'text-white' : 'text-[#4b5563]'">Rangkuman modul & kuis mandiri</p>
                        </div>
                        <i class="fas fa-arrow-right text-xs" :class="activeMode === 'lms_assistant' ? 'text-white' : 'text-[#121316]'"></i>
                    </button>
                </div>
            </div>

            <!-- List Riwayat Percakapan (Tactile List) -->
            <div class="tactile-card p-5 space-y-3">
                <div class="flex items-center justify-between font-mono-code">
                    <h3 class="text-xs font-black uppercase text-[#121316] tracking-wider">&bull; RIWAYAT SESI &bull;</h3>
                    <button type="button" @click="startNewConversation(activeMode)" class="text-xs font-bold text-[#ff3823] hover:underline flex items-center gap-1 cursor-pointer">
                        <i class="fas fa-plus text-[10px]"></i>
                        <span>Sesi Baru</span>
                    </button>
                </div>

                <div class="space-y-2 max-h-[350px] overflow-y-auto pr-1 no-scrollbar font-mono-code">
                    @forelse($conversations as $conv)
                        <div class="group flex items-center justify-between p-3 rounded-xl text-xs transition border-2 border-[#121316] cursor-pointer {{ $activeConversation && $activeConversation->id == $conv->id ? 'bg-[#faf3e0] shadow-[2px_2px_0px_#121316] font-black' : 'bg-white hover:bg-[#f6f4ee]' }}">
                            <a href="{{ route('siswa.ai.index', ['conversation_id' => $conv->id]) }}" class="flex items-center gap-2.5 flex-1 min-w-0 pr-2">
                                <span class="text-[#ff3823] font-bold">&rsaquo;</span>
                                <span class="truncate text-[#121316]">{{ $conv->title }}</span>
                            </a>
                            <form action="{{ route('siswa.ai.destroy', $conv->id) }}" method="POST" onsubmit="return confirm('Hapus sesi percakapan ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="opacity-0 group-hover:opacity-100 p-1 text-rose-600 hover:text-rose-800 transition cursor-pointer" title="Hapus Sesi">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="py-8 text-center text-[#71717a] font-mono-code">
                            <i class="fas fa-terminal text-2xl mb-2 text-[#121316]"></i>
                            <p class="text-xs font-bold text-[#121316]">Belum ada riwayat percakapan.</p>
                            <p class="text-[10px] mt-0.5 text-[#555]">Pilih mode di atas untuk memulai!</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Chat Console Utama (8 cols) -->
        <div class="lg:col-span-8 tactile-card flex flex-col h-[700px] overflow-hidden relative">
            
            <!-- Console Top Header -->
            <div class="px-6 py-4 border-b-2 border-[#121316] bg-[#faf3e0] flex items-center justify-between shrink-0 font-mono-code">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-[#121316] text-[#fde047] border-2 border-[#121316] flex items-center justify-center text-base font-bold shadow-xs">
                        <i class="fas fa-terminal" style="color: #fde047 !important;"></i>
                    </div>
                    <div>
                        <h2 class="text-xs sm:text-sm font-black text-[#121316] uppercase tracking-tight" x-text="conversationTitle">
                            {{ $activeConversation ? $activeConversation->title : 'SESI BARU PEMBDA AI' }}
                        </h2>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[10px] font-extrabold text-[#121316] uppercase" x-text="getModeLabel()">
                                {{ $activeConversation ? ($activeConversation->mode == 'bk_consultation' ? 'BK & KARIR' : ($activeConversation->mode == 'lms_assistant' ? 'ASISTEN LMS' : 'TUTOR Q&A')) : 'TUTOR Q&A' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Console Action Tools -->
                <div class="flex items-center gap-2">
                    <button type="button" @click="clearScreen()" class="px-3 py-1.5 rounded-full btn-tactile-white text-[11px] font-bold flex items-center gap-1.5 cursor-pointer" title="Bersihkan Layar">
                        <i class="fas fa-broom text-[10px] text-[#121316]"></i>
                        <span class="hidden sm:inline">Bersihkan</span>
                    </button>

                    <button type="button" @click="toggleSpeechVoice()" 
                            class="px-3 py-1.5 rounded-full text-[11px] font-bold flex items-center gap-1.5 cursor-pointer border-2 border-[#121316] transition"
                            :class="autoSpeech ? 'bg-[#fde047] text-[#121316] shadow-[2px_2px_0px_#121316]' : 'bg-white text-[#121316] hover:bg-[#f6f4ee]'">
                        <i class="fas" :class="autoSpeech ? 'fa-volume-high text-[#ff3823]' : 'fa-volume-xmark'"></i>
                        <span class="hidden sm:inline" x-text="autoSpeech ? 'Suara ON' : 'Suara OFF'"></span>
                    </button>
                </div>
            </div>

            <!-- Messages Stream (Graph Paper Background) -->
            <div class="flex-1 p-6 overflow-y-auto space-y-6 graph-paper-box" id="chat-messages-container" x-ref="messagesContainer">
                
                @if(empty($messages) && !$activeConversation)
                    <!-- Welcoming Starter Screen -->
                    <div class="max-w-lg mx-auto my-6 text-center space-y-5">
                        
                        <!-- Poster Greeting Frame -->
                        <div class="bg-white border-2 border-[#121316] rounded-2xl p-6 shadow-[4px_4px_0px_#121316] relative overflow-hidden">
                            <div class="text-[10px] font-mono-code font-extrabold uppercase tracking-widest text-[#ff3823] mb-1">
                                &bull; YAYASAN PERGURUAN PEMBDA NIAS &bull;
                            </div>
                            <h3 class="text-2xl font-black text-[#121316] uppercase tracking-tight flex items-center justify-center gap-2 flex-wrap">
                                <span>HALO,</span>
                                <span class="bg-[#fde047] text-[#121316] px-2 py-0.5 rounded-md border-2 border-[#121316] shadow-[2px_2px_0px_#121316]">{{ explode(' ', $student->full_name)[0] }}!</span>
                                <span>👋</span>
                            </h3>
                            <p class="text-xs text-[#4b5563] font-bold mt-2">Saya Asisten Pintar Pembda AI Studio. Ketik perintah atau pilih kartu pertanyaan di bawah ini:</p>
                        </div>

                        <!-- Tactile Categorized Chips -->
                        <div class="space-y-3 pt-2 text-left font-mono-code">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#121316] text-center">&bull; REKOMENDASI TOPIK POPULER &bull;</p>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                                <button @click="useQuickPrompt('Bagaimana cara menghitung Luas Permukaan Tabung? Jelaskan langkahnya.')" class="p-3.5 rounded-2xl bg-white border-2 border-[#121316] shadow-[3.5px_3.5px_0px_#121316] hover:translate-x-0.5 hover:translate-y-0.5 transition text-left flex items-start gap-2.5 cursor-pointer">
                                    <span class="text-base">🧮</span>
                                    <div>
                                        <p class="font-black text-[#121316] uppercase text-[11px]">Matematika & Sains</p>
                                        <p class="text-[11px] text-[#4b5563] font-medium mt-0.5">Rumus Luas Permukaan Tabung</p>
                                    </div>
                                </button>

                                <button @click="useQuickPrompt('Apa saja 5 jurusan SMK di PembdaHUB dan apa keunggulannya?')" class="p-3.5 rounded-2xl bg-white border-2 border-[#121316] shadow-[3.5px_3.5px_0px_#121316] hover:translate-x-0.5 hover:translate-y-0.5 transition text-left flex items-start gap-2.5 cursor-pointer">
                                    <span class="text-base">🏫</span>
                                    <div>
                                        <p class="font-black text-[#121316] uppercase text-[11px]">Info PembdaHUB</p>
                                        <p class="text-[11px] text-[#4b5563] font-medium mt-0.5">Daftar Jurusan SMK & Fitur Sekolah</p>
                                    </div>
                                </button>

                                <button @click="useQuickPrompt('Saya bingung memilih antara lanjut Kuliah atau kerja di DUDI industri, mohon saran.')" class="p-3.5 rounded-2xl bg-white border-2 border-[#121316] shadow-[3.5px_3.5px_0px_#121316] hover:translate-x-0.5 hover:translate-y-0.5 transition text-left flex items-start gap-2.5 cursor-pointer">
                                    <span class="text-base">🎓</span>
                                    <div>
                                        <p class="font-black text-[#121316] uppercase text-[11px]">Karir & BK</p>
                                        <p class="text-[11px] text-[#4b5563] font-medium mt-0.5">Saran Kuliah vs Kerja DUDI</p>
                                    </div>
                                </button>

                                <button @click="useQuickPrompt('Bagaimana cara melihat nilai rapor dan mengikuti ujian CBT online?')" class="p-3.5 rounded-2xl bg-white border-2 border-[#121316] shadow-[3.5px_3.5px_0px_#121316] hover:translate-x-0.5 hover:translate-y-0.5 transition text-left flex items-start gap-2.5 cursor-pointer">
                                    <span class="text-base">📜</span>
                                    <div>
                                        <p class="font-black text-[#121316] uppercase text-[11px]">Panduan Fitur</p>
                                        <p class="text-[11px] text-[#4b5563] font-medium mt-0.5">Cara Akses Rapor & Ujian CBT</p>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Render Messages Stream -->
                <template x-for="(msg, index) in messageList" :key="index">
                    <div :class="msg.sender === 'student' ? 'flex justify-end' : 'flex justify-start'">
                        <div class="flex gap-3 max-w-[92%] sm:max-w-[85%]" :class="msg.sender === 'student' ? 'flex-row-reverse' : 'flex-row'">
                            
                            <!-- Avatar Frame (Siswa vs AI) -->
                            <template x-if="msg.sender === 'student'">
                                <div class="w-10 h-10 rounded-xl bg-white border-2 border-[#121316] shadow-[2px_2px_0px_#121316] p-0.5 shrink-0 overflow-hidden">
                                    <img src="{{ $student->photo_url }}" 
                                         alt="{{ $student->full_name }}" 
                                         class="w-full h-full object-cover rounded-lg"
                                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($student->full_name) }}&background=121316&color=ffffff&bold=true';">
                                </div>
                            </template>

                            <template x-if="msg.sender === 'ai'">
                                <div class="ai-avatar-box">
                                    <i class="fas fa-robot"></i>
                                </div>
                            </template>

                            <!-- Message Body Frame with Explicit High-Contrast Classes -->
                            <div class="space-y-1.5 flex-1 min-w-0">
                                <div class="p-4 rounded-2xl text-xs sm:text-sm leading-relaxed"
                                     :class="msg.sender === 'student' 
                                        ? 'chat-student-bubble text-white rounded-tr-none font-mono-code font-bold' 
                                        : 'chat-ai-bubble text-[#121316] rounded-tl-none prose prose-xs max-w-none font-medium'">
                                    <div class="message-body" x-html="renderMarkdown(msg.message)"></div>
                                </div>

                                <!-- Action Toolbar for AI Messages -->
                                <template x-if="msg.sender === 'ai'">
                                    <div class="flex items-center gap-3 px-1 text-[10px] font-mono-code font-bold text-[#121316]">
                                        <button type="button" @click="copyToClipboard(msg.message)" class="hover:text-[#ff3823] transition flex items-center gap-1 cursor-pointer" title="Salin Jawaban">
                                            <i class="fas fa-copy text-[10px]"></i>
                                            <span>Salin</span>
                                        </button>

                                        <button type="button" @click="speakText(msg.message)" class="hover:text-[#ff3823] transition flex items-center gap-1 cursor-pointer" title="Dengarkan Suara AI">
                                            <i class="fas fa-volume-high text-[10px]"></i>
                                            <span>Dengarkan</span>
                                        </button>

                                        <div class="flex items-center gap-2 border-l-2 border-[#121316] pl-3">
                                            <button type="button" @click="msg.liked = !msg.liked" :class="msg.liked ? 'text-emerald-600' : 'hover:text-[#ff3823]'" class="transition cursor-pointer">
                                                <i class="fas fa-thumbs-up text-[10px]"></i>
                                            </button>
                                            <button type="button" @click="msg.disliked = !msg.disliked" :class="msg.disliked ? 'text-rose-600' : 'hover:text-[#ff3823]'" class="transition cursor-pointer">
                                                <i class="fas fa-thumbs-down text-[10px]"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                        </div>
                    </div>
                </template>

                <!-- Loading Spinner Box -->
                <div x-show="isSending" class="flex justify-start">
                    <div class="flex items-center gap-3 max-w-[85%]">
                        <div class="ai-avatar-box">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="p-3.5 bg-white border-2 border-[#121316] rounded-2xl rounded-tl-none text-xs text-[#121316] font-mono-code font-bold shadow-[4px_4px_0px_#121316] flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#ff3823] animate-ping"></span>
                            <span>Pembda AI sedang menganalisis jawaban...</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Command Input Form (Tactile Console Input) -->
            <div class="p-4 bg-[#faf3e0] border-t-2 border-[#121316] shrink-0 font-mono-code">
                <form @submit.prevent="submitMessage()" class="flex items-end gap-2.5">
                    
                    <div class="flex-1 relative bg-white rounded-2xl border-2 border-[#121316] shadow-[3.5px_3.5px_0px_#121316] focus-within:ring-2 focus-within:ring-[#ff3823] transition">
                        <textarea x-ref="promptInput" x-model="userInput" 
                                  @keydown.enter.prevent="if(!$event.shiftKey) submitMessage()"
                                  placeholder="› Ketik perintah atau pertanyaan Anda di sini... (Enter untuk Kirim)"
                                  rows="2"
                                  class="w-full p-3.5 pr-10 rounded-2xl bg-transparent text-xs sm:text-sm outline-none resize-none text-[#121316] font-mono-code font-bold placeholder-[#71717a]"
                                  :disabled="isSending || usageInfo.remaining <= 0"></textarea>

                        <!-- Mic Button -->
                        <button type="button" @click="toggleVoiceInput()" 
                                class="absolute right-3 bottom-3 p-1.5 rounded-lg transition cursor-pointer"
                                :class="isListening ? 'bg-[#ff3823] text-white animate-pulse' : 'text-[#121316] hover:text-[#ff3823]'"
                                title="Bicara via Suara (Speech-to-Text)">
                            <i class="fas" :class="isListening ? 'fa-microphone-slash' : 'fa-microphone'"></i>
                        </button>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            :disabled="isSending || !userInput.trim() || usageInfo.remaining <= 0"
                            class="px-6 py-3.5 rounded-2xl btn-tactile-red text-xs font-black uppercase tracking-wider disabled:opacity-40 disabled:cursor-not-allowed transition flex items-center gap-2 shrink-0 cursor-pointer">
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
        conversationTitle: '{{ $activeConversation ? addslashes($activeConversation->title) : "SESI BARU PEMBDA AI" }}',
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
            if (this.activeMode === 'bk_consultation') return 'BK & KARIR';
            if (this.activeMode === 'lms_assistant') return 'ASISTEN LMS';
            return 'TUTOR Q&A';
        },

        startNewConversation(mode) {
            this.activeMode = mode;
            this.activeConversationId = null;
            this.messageList = [];
            this.conversationTitle = (mode === 'bk_consultation') ? 'KONSULTASI BK & KARIR' : (mode === 'lms_assistant' ? 'ASISTEN BELAJAR LMS' : 'TUTOR AKADEMIK Q&A');
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
                return marked.parse(content || '');
            }
            return (content || '').replace(/\n/g, '<br>');
        },

        renderMathFormulas() {
            this.$nextTick(() => {
                try {
                    if (window.renderMathInElement && this.$refs.messagesContainer) {
                        renderMathInElement(this.$refs.messagesContainer, {
                            delimiters: [
                                {left: '$$', right: '$$', display: true},
                                {left: '$', right: '$', display: false},
                                {left: '\\(', right: '\\)', display: false},
                                {left: '\\[', right: '\\]', display: true}
                            ],
                            throwOnError: false
                        });
                    }
                } catch (err) {
                    console.error('KaTeX rendering notice:', err);
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
                    const aiText = result.ai_message.message || 'Pembda AI telah memproses instruksi Anda.';
                    
                    if (result.usage) {
                        this.usageInfo.remaining = result.usage.remaining;
                    }

                    // Instantly push completed AI message with 100% reliability
                    this.messageList.push({
                        sender: 'ai',
                        message: aiText,
                        liked: false,
                        disliked: false
                    });

                    this.renderMathFormulas();

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
