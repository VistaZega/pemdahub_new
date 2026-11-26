@extends('layouts.siswa')

@section('title', 'Pembda AI — Asisten Belajar & Konsultasi Siswa')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js"></script>

<style>
    .ai-shell {
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        display: flex;
        position: relative;
        height: calc(100vh - 150px);
        min-height: 520px;
        max-height: 820px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .ai-sidebar {
        width: 280px;
        min-width: 280px;
        background: #f8fafc;
        border-right: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        transition: all 0.22s ease;
        overflow: hidden;
        z-index: 20;
    }

    .ai-sidebar.is-collapsed {
        width: 0;
        min-width: 0;
        border-right: none;
        opacity: 0;
        pointer-events: none;
    }

    @media (max-width: 1023px) {
        .ai-shell {
            height: calc(100vh - 135px);
        }
        .ai-sidebar {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: 280px;
            min-width: 280px;
            box-shadow: 8px 0 24px rgba(15, 23, 42, 0.14);
            transform: translateX(0);
            opacity: 1;
        }
        .ai-sidebar.is-collapsed {
            width: 280px;
            min-width: 280px;
            transform: translateX(-105%);
            opacity: 0;
            pointer-events: none;
        }
    }

    .ai-main {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        background: #ffffff;
    }

    .ai-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background: #f8fafc;
    }

    .ai-prose p { margin-bottom: 0.6rem; line-height: 1.65; }
    .ai-prose p:last-child { margin-bottom: 0; }
    .ai-prose ul { list-style-type: disc; padding-left: 1.35rem; margin: 0.5rem 0; }
    .ai-prose ol { list-style-type: decimal; padding-left: 1.35rem; margin: 0.5rem 0; }
    .ai-prose li { margin-bottom: 0.25rem; }
    .ai-prose h1, .ai-prose h2, .ai-prose h3 { font-weight: 800; color: #0f172a; margin-top: 0.85rem; margin-bottom: 0.4rem; }
    .ai-prose h2 { font-size: 1.05rem; }
    .ai-prose h3 { font-size: 0.95rem; }
    .ai-prose code {
        font-family: 'JetBrains Mono', monospace;
        background: #e2e8f0;
        color: #0f172a;
        padding: 0.12rem 0.35rem;
        border-radius: 0.375rem;
        font-size: 0.82em;
    }
    .ai-prose pre {
        background: #0f172a;
        color: #f8fafc;
        padding: 0.85rem 1rem;
        border-radius: 0.75rem;
        overflow-x: auto;
        margin: 0.65rem 0;
        font-size: 0.8rem;
    }
    .ai-prose pre code {
        background: transparent;
        color: #fde047;
        padding: 0;
    }
    .ai-prose blockquote {
        border-left: 3px solid #6366f1;
        padding-left: 0.85rem;
        color: #475569;
        margin: 0.5rem 0;
    }
    .ai-prose table {
        width: 100%;
        border-collapse: collapse;
        margin: 0.65rem 0;
        font-size: 0.82rem;
    }
    .ai-prose th, .ai-prose td {
        border: 1px solid #cbd5e1;
        padding: 0.45rem 0.65rem;
        text-align: left;
    }
    .ai-prose th {
        background: #f1f5f9;
        font-weight: 700;
    }
</style>

<div class="ai-shell" x-data="aiChatApp()">

    <!-- Mobile Backdrop di dalam ai-shell -->
    <div x-show="sidebarOpen"
         @click="sidebarOpen = false"
         style="display: none; position: absolute; inset: 0; background: rgba(15, 23, 42, 0.35); z-index: 15;"
         class="lg:hidden"></div>

    <!-- SIDEBAR KIRI PEMBDA AI (Bisa Buka-Tutup di Desktop & Mobile) -->
    <aside class="ai-sidebar" :class="{ 'is-collapsed': !sidebarOpen }">

        <!-- Brand & New Chat -->
        <div class="p-4 border-b border-gray-200 space-y-3 shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-sm">
                        <i class="fas fa-robot text-sm"></i>
                    </div>
                    <div>
                        <div class="text-sm font-extrabold text-gray-900 leading-tight">Pembda AI</div>
                        <div class="flex items-center gap-1.5 text-[11px] font-semibold text-emerald-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Siap Membantu</span>
                        </div>
                    </div>
                </div>
                <button type="button"
                        @click="sidebarOpen = false"
                        class="p-1.5 text-gray-500 hover:text-gray-800 hover:bg-gray-200 rounded-lg cursor-pointer"
                        title="Tutup Panel Kiri">
                    <i class="fas fa-chevron-left text-xs"></i>
                </button>
            </div>

            <button type="button"
                    @click="startNewConversation(activeMode); if(window.innerWidth < 1024) sidebarOpen = false"
                    class="w-full py-2.5 px-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm transition flex items-center justify-center gap-2 cursor-pointer">
                <i class="fas fa-plus"></i>
                <span>Percakapan Baru</span>
            </button>
        </div>

        <!-- Mode Selector -->
        <div class="p-3 border-b border-gray-200 space-y-1.5 shrink-0">
            <div class="px-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">Mode Asisten</div>

            <button type="button"
                    @click="startNewConversation('tutor'); if(window.innerWidth < 1024) sidebarOpen = false"
                    :class="activeMode === 'tutor' ? 'bg-white border-indigo-300 text-indigo-700 shadow-sm' : 'border-transparent text-gray-600 hover:bg-white'"
                    class="w-full p-2.5 rounded-xl border text-left transition flex items-center gap-2.5 cursor-pointer">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                     :class="activeMode === 'tutor' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-200 text-gray-500'">
                    <i class="fas fa-graduation-cap text-xs"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold truncate">Tutor Akademik</div>
                    <div class="text-[11px] text-gray-500 truncate">Soal, tugas &amp; konsep mapel</div>
                </div>
            </button>

            <button type="button"
                    @click="startNewConversation('bk_consultation'); if(window.innerWidth < 1024) sidebarOpen = false"
                    :class="activeMode === 'bk_consultation' ? 'bg-white border-emerald-300 text-emerald-700 shadow-sm' : 'border-transparent text-gray-600 hover:bg-white'"
                    class="w-full p-2.5 rounded-xl border text-left transition flex items-center gap-2.5 cursor-pointer">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                     :class="activeMode === 'bk_consultation' ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-200 text-gray-500'">
                    <i class="fas fa-heart text-xs"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold truncate">Konsultasi BK &amp; Karir</div>
                    <div class="text-[11px] text-gray-500 truncate">Motivasi, kuliah &amp; DUDI</div>
                </div>
            </button>

            <button type="button"
                    @click="startNewConversation('lms_assistant'); if(window.innerWidth < 1024) sidebarOpen = false"
                    :class="activeMode === 'lms_assistant' ? 'bg-white border-amber-300 text-amber-700 shadow-sm' : 'border-transparent text-gray-600 hover:bg-white'"
                    class="w-full p-2.5 rounded-xl border text-left transition flex items-center gap-2.5 cursor-pointer">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                     :class="activeMode === 'lms_assistant' ? 'bg-amber-100 text-amber-600' : 'bg-gray-200 text-gray-500'">
                    <i class="fas fa-book-open text-xs"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold truncate">Asisten Belajar LMS</div>
                    <div class="text-[11px] text-gray-500 truncate">Rangkuman, kuis &amp; tugas</div>
                </div>
            </button>
        </div>

        <!-- Riwayat Sesi -->
        <div class="flex-1 overflow-y-auto p-3 space-y-1">
            <div class="px-1 pb-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">Riwayat Percakapan</div>

            @forelse($conversations as $conv)
                <div class="group flex items-center gap-1 rounded-xl transition {{ $activeConversation && $activeConversation->id == $conv->id && empty($prefillPrompt) ? 'bg-indigo-50 text-indigo-700' : 'hover:bg-gray-200 text-gray-700' }}">
                    <a href="{{ route('siswa.ai.index', ['conversation_id' => $conv->id]) }}"
                       class="flex-1 min-w-0 px-2.5 py-2 text-xs font-semibold truncate flex items-center gap-2">
                        <i class="far fa-comment-dots text-[11px] opacity-60 shrink-0"></i>
                        <span class="truncate">{{ $conv->title }}</span>
                    </a>
                    <form action="{{ route('siswa.ai.destroy', $conv->id) }}" method="POST" onsubmit="return confirm('Hapus percakapan ini?')" class="pr-1.5 opacity-0 group-hover:opacity-100 transition">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1 text-gray-400 hover:text-red-600 rounded cursor-pointer" title="Hapus">
                            <i class="fas fa-trash-alt text-[10px]"></i>
                        </button>
                    </form>
                </div>
            @empty
                <div class="px-2 py-6 text-center text-xs text-gray-400">
                    Belum ada riwayat percakapan.
                </div>
            @endforelse
        </div>

        <!-- Footer Profil & Kuota Harian -->
        <div class="p-3.5 border-t border-gray-200 bg-white shrink-0">
            <div class="flex items-center justify-between text-[11px] font-bold text-gray-700 mb-1.5">
                <span class="truncate">{{ explode(' ', $student->full_name)[0] }} &bull; Kuota Harian</span>
                <span class="text-indigo-600 font-mono">
                    <span x-text="usageInfo.remaining">{{ $usageInfo['remaining'] }}</span>/<span x-text="usageInfo.limit">{{ $usageInfo['limit'] }}</span>
                </span>
            </div>
            <div class="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full bg-indigo-600 rounded-full transition-all duration-300"
                     :style="'width: ' + Math.min(100, Math.round((usageInfo.remaining / Math.max(1, usageInfo.limit)) * 100)) + '%'"></div>
            </div>
        </div>
    </aside>

    <!-- AREA CHAT UTAMA (KANAN) -->
    <main class="ai-main">

        <!-- Top Bar Chat -->
        <header class="px-4 sm:px-5 py-3 border-b border-gray-200 flex items-center justify-between gap-3 bg-white shrink-0">
            <div class="flex items-center gap-2.5 min-w-0">
                <button type="button"
                        @click="sidebarOpen = !sidebarOpen"
                        class="p-2 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl border border-gray-200 transition cursor-pointer shrink-0"
                        :title="sidebarOpen ? 'Tutup Sidebar Riwayat' : 'Buka Sidebar Riwayat'">
                    <i class="fas" :class="sidebarOpen ? 'fa-indent' : 'fa-outdent'"></i>
                </button>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h2 class="text-sm sm:text-base font-extrabold text-gray-900 truncate" x-text="conversationTitle"></h2>
                        <span class="hidden sm:inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-indigo-50 text-indigo-700 border border-indigo-200 shrink-0" x-text="getModeLabel()"></span>
                    </div>
                    <p class="text-[11px] text-gray-500 truncate">Terhubung dengan data akademik, tugas LMS &amp; jadwal CBT Anda</p>
                </div>
            </div>

            <div class="flex items-center gap-1.5 shrink-0">
                <button type="button"
                        @click="toggleSpeechVoice()"
                        :class="autoSpeech ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100'"
                        class="px-2.5 py-1.5 rounded-xl border text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
                        title="Bacakan Jawaban AI Otomatis">
                    <i class="fas" :class="autoSpeech ? 'fa-volume-up' : 'fa-volume-mute'"></i>
                    <span class="hidden sm:inline" x-text="autoSpeech ? 'Suara Aktif' : 'Suara'"></span>
                </button>
                <button type="button"
                        @click="clearScreen()"
                        class="px-2.5 py-1.5 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-600 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
                        title="Bersihkan Layar">
                    <i class="fas fa-eraser"></i>
                    <span class="hidden sm:inline">Bersihkan</span>
                </button>
            </div>
        </header>

        <!-- Messages Stream -->
        <div x-ref="messagesContainer" class="ai-messages space-y-5">

            <!-- Empty State + Smart Prompt Cards -->
            <template x-if="messageList.length === 0">
                <div class="max-w-2xl mx-auto py-6 sm:py-10 space-y-6">
                    <div class="text-center space-y-2">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-2xl shadow-md">
                            <i class="fas fa-robot"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-extrabold text-gray-900">
                            Halo, {{ explode(' ', $student->full_name)[0] }}!
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-500 max-w-md mx-auto">
                            Tanyakan materi pelajaran, cek tugas LMS yang belum dikerjakan, upload foto soal, atau konsultasi karir bersama Pembda AI.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button type="button"
                                @click="useQuickPrompt('Tugas LMS atau kuis apa saja yang belum saya kerjakan saat ini?')"
                                class="p-3.5 rounded-2xl bg-white border border-gray-200 hover:border-indigo-300 hover:shadow-sm text-left transition group cursor-pointer">
                            <div class="flex items-center gap-2 text-xs font-bold text-indigo-700 mb-1">
                                <i class="fas fa-tasks"></i> Cek Tugas &amp; Kuis LMS
                            </div>
                            <p class="text-xs text-gray-600 group-hover:text-gray-900">Lihat daftar tugas &amp; deadline yang belum dikumpulkan.</p>
                        </button>

                        <button type="button"
                                @click="useQuickPrompt('Ada jadwal ujian CBT apa saja yang aktif atau akan datang untuk saya?')"
                                class="p-3.5 rounded-2xl bg-white border border-gray-200 hover:border-amber-300 hover:shadow-sm text-left transition group cursor-pointer">
                            <div class="flex items-center gap-2 text-xs font-bold text-amber-700 mb-1">
                                <i class="fas fa-laptop-code"></i> Cek Jadwal Ujian CBT
                            </div>
                            <p class="text-xs text-gray-600 group-hover:text-gray-900">Pantau jadwal ujian online yang perlu dipersiapkan.</p>
                        </button>

                        <button type="button"
                                @click="useQuickPrompt('Bantu saya memahami langkah-langkah menyelesaikan soal Matematika atau Kejuruan secara bertahap.')"
                                class="p-3.5 rounded-2xl bg-white border border-gray-200 hover:border-purple-300 hover:shadow-sm text-left transition group cursor-pointer">
                            <div class="flex items-center gap-2 text-xs font-bold text-purple-700 mb-1">
                                <i class="fas fa-calculator"></i> Bimbingan Soal Bertahap
                            </div>
                            <p class="text-xs text-gray-600 group-hover:text-gray-900">Ketik soal atau upload foto soal dari buku/LKS.</p>
                        </button>

                        <button type="button"
                                @click="activeMode = 'bk_consultation'; useQuickPrompt('Berikan saran jurusan kuliah atau prospek karir DUDI yang cocok dengan jurusan dan minat saya.')"
                                class="p-3.5 rounded-2xl bg-white border border-gray-200 hover:border-emerald-300 hover:shadow-sm text-left transition group cursor-pointer">
                            <div class="flex items-center gap-2 text-xs font-bold text-emerald-700 mb-1">
                                <i class="fas fa-compass"></i> Konsultasi Karir &amp; DNA
                            </div>
                            <p class="text-xs text-gray-600 group-hover:text-gray-900">Rekomendasi belajar dan karir sesuai profil Anda.</p>
                        </button>
                    </div>
                </div>
            </template>

            <!-- Chat Messages -->
            <template x-for="(msg, index) in messageList" :key="index">
                <div class="max-w-3xl mx-auto flex gap-3" :class="msg.sender === 'student' ? 'justify-end' : 'justify-start'">

                    <!-- AI Avatar -->
                    <template x-if="msg.sender === 'ai'">
                        <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-sm mt-0.5">
                            <i class="fas fa-robot text-xs"></i>
                        </div>
                    </template>

                    <!-- Bubble -->
                    <div class="space-y-1.5 max-w-[88%] sm:max-w-[82%] min-w-0">
                        <div class="px-4 py-3 rounded-2xl text-xs sm:text-sm shadow-sm"
                             :class="msg.sender === 'student'
                                ? 'bg-indigo-600 text-white rounded-br-sm'
                                : 'bg-white text-gray-800 border border-gray-200 rounded-tl-sm ai-prose'">
                            <template x-if="msg.image_url">
                                <div class="mb-2">
                                    <img :src="msg.image_url" alt="Lampiran Soal" class="max-h-56 rounded-xl border border-gray-200 bg-white object-contain">
                                </div>
                            </template>
                            <div x-html="msg.sender === 'ai' ? renderMarkdown(msg.message) : escapeHtml(msg.message)"></div>
                        </div>

                        <!-- AI Action Toolbar -->
                        <template x-if="msg.sender === 'ai' && msg.message">
                            <div class="flex flex-wrap items-center gap-3 px-1 text-[11px] font-semibold text-gray-500">
                                <button type="button" @click="copyToClipboard(msg.message)" class="hover:text-indigo-600 transition flex items-center gap-1 cursor-pointer">
                                    <i class="far fa-copy"></i> <span>Salin</span>
                                </button>
                                <button type="button" @click="speakText(msg.message)" class="hover:text-indigo-600 transition flex items-center gap-1 cursor-pointer">
                                    <i class="fas fa-volume-up"></i> <span>Dengarkan</span>
                                </button>
                                <button type="button" @click="useQuickPrompt('Buatkan 3 soal latihan serupa beserta kunci pembahasan bertahap untuk topik ini.')" class="hover:text-indigo-600 transition flex items-center gap-1 cursor-pointer">
                                    <i class="fas fa-dumbbell"></i> <span>Latihan Serupa</span>
                                </button>
                                <div class="flex items-center gap-2 border-l border-gray-200 pl-3">
                                    <button type="button" @click="msg.liked = !msg.liked; msg.disliked = false" :class="msg.liked ? 'text-emerald-600' : 'hover:text-gray-800'" class="cursor-pointer">
                                        <i class="far fa-thumbs-up"></i>
                                    </button>
                                    <button type="button" @click="msg.disliked = !msg.disliked; msg.liked = false" :class="msg.disliked ? 'text-red-600' : 'hover:text-gray-800'" class="cursor-pointer">
                                        <i class="far fa-thumbs-down"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Typing Indicator -->
            <div x-show="isSending && (!messageList.length || messageList[messageList.length - 1].sender !== 'ai' || !messageList[messageList.length - 1].message)"
                 style="display: none;"
                 class="max-w-3xl mx-auto flex justify-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                    <i class="fas fa-robot text-xs"></i>
                </div>
                <div class="px-4 py-3 bg-white border border-gray-200 rounded-2xl rounded-tl-sm shadow-sm text-xs text-gray-600 font-semibold flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 animate-ping"></span>
                    <span>Pembda AI sedang berpikir...</span>
                </div>
            </div>
        </div>

        <!-- Composer Input Bar -->
        <footer class="p-3 sm:p-4 bg-white border-t border-gray-200 shrink-0">
            <div class="max-w-3xl mx-auto space-y-2">
                <!-- Preview Foto Soal -->
                <div x-show="imagePreviewUrl" style="display: none;" class="flex items-center gap-3 bg-gray-50 p-2 rounded-xl border border-gray-200 w-fit">
                    <img :src="imagePreviewUrl" alt="Preview Soal" class="h-12 w-12 object-cover rounded-lg border border-gray-200">
                    <div class="text-xs">
                        <div class="font-bold text-gray-800">Foto soal siap dikirim</div>
                        <button type="button" @click="clearImage()" class="text-red-600 hover:underline font-semibold cursor-pointer">
                            <i class="fas fa-times-circle"></i> Hapus foto
                        </button>
                    </div>
                </div>

                <form @submit.prevent="submitMessage()" class="flex items-end gap-2 bg-gray-50 border border-gray-300 rounded-2xl p-2 focus-within:bg-white focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-100 transition">
                    <input type="file" x-ref="imageInput" accept="image/jpeg,image/png,image/webp" class="hidden" @change="handleImageSelect($event)">

                    <button type="button"
                            @click="$refs.imageInput.click()"
                            class="p-2.5 rounded-xl text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 transition cursor-pointer shrink-0"
                            title="Upload Foto Soal (JPG/PNG/WEBP, maks 4MB)">
                        <i class="fas fa-camera text-sm"></i>
                    </button>

                    <button type="button"
                            @click="toggleVoiceInput()"
                            class="p-2.5 rounded-xl transition cursor-pointer shrink-0"
                            :class="isListening ? 'bg-red-500 text-white animate-pulse' : 'text-gray-500 hover:text-indigo-600 hover:bg-indigo-50'"
                            title="Bicara via Suara">
                        <i class="fas text-sm" :class="isListening ? 'fa-microphone-slash' : 'fa-microphone'"></i>
                    </button>

                    <textarea x-ref="promptInput"
                              x-model="userInput"
                              @keydown.enter.prevent="if(!$event.shiftKey) submitMessage()"
                              placeholder="Tanya materi, cek tugas LMS, atau upload foto soal..."
                              rows="1"
                              class="flex-1 py-2 px-1 bg-transparent text-xs sm:text-sm text-gray-800 placeholder-gray-400 outline-none resize-none max-h-32"
                              :disabled="isSending || usageInfo.remaining <= 0"></textarea>

                    <button type="submit"
                            :disabled="isSending || (!userInput.trim() && !selectedImageFile) || usageInfo.remaining <= 0"
                            class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 text-white text-xs font-bold transition flex items-center gap-1.5 shrink-0 cursor-pointer disabled:cursor-not-allowed">
                        <span class="hidden sm:inline">Kirim</span>
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </footer>
    </main>
</div>

<script>
function aiChatApp() {
    return {
        sidebarOpen: window.innerWidth >= 1024,
        activeConversationId: {{ $activeConversation ? $activeConversation->id : 'null' }},
        activeMode: '{{ $activeConversation ? $activeConversation->mode : "tutor" }}',
        conversationTitle: @json($activeConversation ? $activeConversation->title : 'Percakapan Baru'),
        userInput: @json($prefillPrompt ?? ''),
        selectedImageFile: null,
        imagePreviewUrl: null,
        isSending: false,
        autoSpeech: false,
        isListening: false,
        recognition: null,
        usageInfo: {
            remaining: {{ $usageInfo['remaining'] }},
            limit: {{ $usageInfo['limit'] }}
        },
        messageList: [
            @if(!empty($messages) && empty($prefillPrompt))
                @foreach($messages as $m)
                    @php $meta = !empty($m->metadata) ? json_decode($m->metadata, true) : []; @endphp
                    { sender: '{{ $m->sender }}', message: @json($m->message), image_url: @json($meta['image_url'] ?? null), liked: false, disliked: false },
                @endforeach
            @endif
        ],

        init() {
            @if(!empty($prefillMode))
                this.activeMode = '{{ $prefillMode }}';
                this.activeConversationId = null;
                this.conversationTitle = (this.activeMode === 'bk_consultation') ? 'Konsultasi BK & Karir' : (this.activeMode === 'lms_assistant' ? 'Asisten Belajar LMS' : 'Tutor Akademik AI');
            @endif
            this.scrollToBottom();
            this.renderMathFormulas();
            this.initSpeechRecognition();
            @if(!empty($prefillPrompt))
                this.$nextTick(() => {
                    if (this.$refs.promptInput) this.$refs.promptInput.focus();
                });
            @endif
        },

        getModeLabel() {
            if (this.activeMode === 'bk_consultation') return 'BK & Karir';
            if (this.activeMode === 'lms_assistant') return 'Asisten LMS';
            return 'Tutor AI';
        },

        startNewConversation(mode) {
            this.activeMode = mode;
            this.activeConversationId = null;
            this.messageList = [];
            this.conversationTitle = (mode === 'bk_consultation') ? 'Konsultasi BK & Karir' : (mode === 'lms_assistant' ? 'Asisten Belajar LMS' : 'Tutor Akademik AI');
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

        handleImageSelect(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (file.size > 4 * 1024 * 1024) {
                alert('Ukuran foto maksimal 4 MB.');
                event.target.value = '';
                return;
            }
            this.selectedImageFile = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.imagePreviewUrl = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        clearImage() {
            this.selectedImageFile = null;
            this.imagePreviewUrl = null;
            if (this.$refs.imageInput) this.$refs.imageInput.value = '';
        },

        toggleSpeechVoice() {
            this.autoSpeech = !this.autoSpeech;
        },

        speakText(text) {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const cleanText = (text || '').replace(/[#*`_$~-]/g, '');
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
                this.recognition.onerror = () => { this.isListening = false; };
                this.recognition.onend = () => { this.isListening = false; };
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
                alert('Jawaban Pembda AI berhasil disalin!');
            }).catch(err => console.error('Gagal menyalin:', err));
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML.replace(/\n/g, '<br>');
        },

        renderMarkdown(content) {
            if (typeof marked !== 'undefined') {
                return marked.parse(content || '');
            }
            return this.escapeHtml(content);
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
                if (el) el.scrollTop = el.scrollHeight;
            });
        },

        async submitMessage() {
            const text = this.userInput.trim();
            const imageFile = this.selectedImageFile;
            const previewUrl = this.imagePreviewUrl;
            if ((!text && !imageFile) || this.isSending) return;

            const displayMsg = text || 'Tolong bantu jelaskan dan selesaikan soal pada gambar ini.';
            this.messageList.push({ sender: 'student', message: displayMsg, image_url: previewUrl });
            this.userInput = '';
            this.clearImage();
            this.isSending = true;
            this.scrollToBottom();

            const formData = new FormData();
            if (this.activeConversationId) {
                formData.append('conversation_id', this.activeConversationId);
            }
            formData.append('mode', this.activeMode);
            formData.append('message', displayMsg);
            if (imageFile) {
                formData.append('image', imageFile);
            }

            try {
                const response = await fetch('{{ route("siswa.ai.stream") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'text/event-stream'
                    },
                    body: formData
                });

                const contentType = response.headers.get('content-type') || '';
                if (!response.ok || !contentType.includes('text/event-stream') || !response.body) {
                    const fallbackRes = await fetch('{{ route("siswa.ai.send") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    const result = await fallbackRes.json();
                    if (result.success) {
                        this.activeConversationId = result.conversation_id;
                        const aiText = result.ai_message.message || 'Pembda AI telah memproses instruksi Anda.';
                        if (result.usage) this.usageInfo.remaining = result.usage.remaining;
                        this.messageList.push({ sender: 'ai', message: aiText, liked: false, disliked: false });
                        this.renderMathFormulas();
                        if (this.autoSpeech) this.speakText(aiText);
                    } else {
                        alert(result.message || 'Terjadi kesalahan saat memproses jawaban AI.');
                    }
                    return;
                }

                const aiMsgIndex = this.messageList.push({
                    sender: 'ai',
                    message: '',
                    liked: false,
                    disliked: false
                }) - 1;

                const reader = response.body.getReader();
                const decoder = new TextDecoder('utf-8');
                let buffer = '';
                let currentEvent = 'message';

                while (true) {
                    const { value, done } = await reader.read();
                    if (done) break;

                    buffer += decoder.decode(value, { stream: true });
                    const lines = buffer.split('\n');
                    buffer = lines.pop() || '';

                    for (const rawLine of lines) {
                        const line = rawLine.trim();
                        if (line.startsWith('event:')) {
                            currentEvent = line.slice(6).trim();
                        } else if (line.startsWith('data:')) {
                            const dataStr = line.slice(5).trim();
                            if (!dataStr) continue;
                            try {
                                const payload = JSON.parse(dataStr);
                                if (currentEvent === 'meta') {
                                    if (payload.conversation_id) this.activeConversationId = payload.conversation_id;
                                    if (payload.conversation_title) this.conversationTitle = payload.conversation_title;
                                } else if (currentEvent === 'chunk' && payload.delta) {
                                    this.messageList[aiMsgIndex].message += payload.delta;
                                    this.scrollToBottom();
                                } else if (currentEvent === 'done') {
                                    if (payload.full_message) this.messageList[aiMsgIndex].message = payload.full_message;
                                    if (payload.usage) this.usageInfo.remaining = payload.usage.remaining;
                                } else if (currentEvent === 'error') {
                                    alert(payload.message || 'Terjadi kesalahan saat memproses jawaban AI.');
                                }
                            } catch (e) {}
                        }
                    }
                }

                this.renderMathFormulas();
                if (this.autoSpeech && this.messageList[aiMsgIndex]?.message) {
                    this.speakText(this.messageList[aiMsgIndex].message);
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
