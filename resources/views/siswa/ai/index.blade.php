@extends('layouts.siswa')

@section('title', 'Pembda AI — Tutor & Konsultasi Belajar')

@section('content')
<!-- Marked.js & KaTeX for Markdown & Mathematical Formula Rendering -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js"></script>

<div class="space-y-6 pb-12" x-data="aiChatApp()">
    <!-- Top Header Banner -->
    <div class="bg-gradient-to-r from-purple-700 via-indigo-600 to-violet-800 rounded-3xl p-6 text-white shadow-xl relative overflow-hidden">
        <div class="absolute right-0 top-0 translate-x-8 -translate-y-8 w-56 h-56 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center text-white text-2xl border border-white/30 shadow-inner shrink-0">
                    <i class="fas fa-robot text-purple-200"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-black tracking-tight">Pembda AI</h1>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-purple-400/30 border border-purple-300/40 text-purple-100">AI Tutor & Konsultasi</span>
                    </div>
                    <p class="text-purple-100 text-sm mt-0.5">Asisten pintar 24/7 untuk bimbingan soal, konsultasi karir/BK, dan rangkuman materi belajar Anda.</p>
                </div>
            </div>

            <!-- Quota Counter Badge -->
            <div class="bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/20 flex items-center gap-3 shrink-0">
                <div class="text-right">
                    <p class="text-[10px] uppercase tracking-wider font-extrabold text-purple-200">Kuota Harian Anda</p>
                    <p class="text-lg font-black text-white">
                        <span x-text="usageInfo.remaining">{{ $usageInfo['remaining'] }}</span> / {{ $usageInfo['limit'] }} <span class="text-xs font-normal">Pesan</span>
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-purple-500/30 flex items-center justify-center text-purple-200 border border-purple-300/30">
                    <i class="fas fa-bolt text-amber-300"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Grid Workspace -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Sidebar Kiri: Riwayat Percakapan & Pilihan Mode (3 cols) -->
        <div class="lg:col-span-4 space-y-4">
            <!-- Modal Button + Mode Cards -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs space-y-3">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">Pilih Mode Pembda AI</h3>
                
                <div class="grid grid-cols-1 gap-2">
                    <button type="button" @click="startNewConversation('tutor')"
                            class="flex items-center gap-3 p-3 rounded-2xl border transition text-left cursor-pointer hover:border-purple-300 hover:bg-purple-50/50"
                            :class="activeMode === 'tutor' ? 'bg-purple-50 border-purple-500 text-purple-900 font-bold' : 'border-slate-200 text-slate-700'">
                        <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center shrink-0">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-extrabold">Tutor Pelajaran (Q&A)</p>
                            <p class="text-[11px] text-slate-500 truncate">Bimbingan soal & konsep materi</p>
                        </div>
                    </button>

                    <button type="button" @click="startNewConversation('bk_consultation')"
                            class="flex items-center gap-3 p-3 rounded-2xl border transition text-left cursor-pointer hover:border-pink-300 hover:bg-pink-50/50"
                            :class="activeMode === 'bk_consultation' ? 'bg-pink-50 border-pink-500 text-pink-900 font-bold' : 'border-slate-200 text-slate-700'">
                        <div class="w-9 h-9 rounded-xl bg-pink-100 text-pink-700 flex items-center justify-center shrink-0">
                            <i class="fas fa-user-nurse"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-extrabold">Konsultasi BK & Karir</p>
                            <p class="text-[11px] text-slate-500 truncate">Kuliah, DUDI, & curhat belajar</p>
                        </div>
                    </button>

                    <button type="button" @click="startNewConversation('lms_assistant')"
                            class="flex items-center gap-3 p-3 rounded-2xl border transition text-left cursor-pointer hover:border-indigo-300 hover:bg-indigo-50/50"
                            :class="activeMode === 'lms_assistant' ? 'bg-indigo-50 border-indigo-500 text-indigo-900 font-bold' : 'border-slate-200 text-slate-700'">
                        <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-extrabold">Asisten Belajar LMS</p>
                            <p class="text-[11px] text-slate-500 truncate">Rangkuman modul & kuis mandiri</p>
                        </div>
                    </button>
                </div>
            </div>

            <!-- List Riwayat Percakapan -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">Riwayat Percakapan</h3>
                    <span class="text-[11px] font-bold text-slate-400">{{ count($conversations) }} Sesi</span>
                </div>

                <div class="space-y-1.5 max-h-[380px] overflow-y-auto pr-1 no-scrollbar">
                    @forelse($conversations as $conv)
                        <div class="group flex items-center justify-between p-2.5 rounded-2xl text-xs transition border {{ $activeConversation && $activeConversation->id == $conv->id ? 'bg-purple-50/80 border-purple-300 text-purple-950 font-bold' : 'border-transparent text-slate-600 hover:bg-slate-50' }}">
                            <a href="{{ route('siswa.ai.index', ['conversation_id' => $conv->id]) }}" class="flex items-center gap-2.5 flex-1 min-w-0 pr-2">
                                <i class="fas {{ $conv->mode == 'bk_consultation' ? 'fa-heart text-pink-500' : ($conv->mode == 'lms_assistant' ? 'fa-book text-indigo-500' : 'fa-robot text-purple-500') }} text-xs shrink-0"></i>
                                <span class="truncate">{{ $conv->title }}</span>
                            </a>
                            <form action="{{ route('siswa.ai.destroy', $conv->id) }}" method="POST" onsubmit="return confirm('Hapus sesi percakapan ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="opacity-0 group-hover:opacity-100 p-1 rounded-lg hover:bg-rose-100 text-rose-600 transition cursor-pointer" title="Hapus">
                                    <i class="fas fa-trash-alt text-[10px]"></i>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400">
                            <i class="fas fa-comments text-2xl mb-2 opacity-50"></i>
                            <p class="text-xs">Belum ada riwayat percakapan.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Chat Workspace Utama (8 cols) -->
        <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200/80 shadow-xs flex flex-col h-[650px] overflow-hidden">
            
            <!-- Chat Header Bar -->
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center text-lg shadow-xs">
                        <i class="fas fa-brain"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-900" x-text="conversationTitle">
                            {{ $activeConversation ? $activeConversation->title : 'Sesi Baru Pembda AI' }}
                        </h2>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                            <span class="text-[11px] font-medium text-slate-500">Pembda AI Siap Membantu</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold px-3 py-1 rounded-full border bg-purple-50 text-purple-700 border-purple-200" x-text="getModeLabel()">
                        {{ $activeConversation ? ($activeConversation->mode == 'bk_consultation' ? 'BK & Karir' : ($activeConversation->mode == 'lms_assistant' ? 'LMS' : 'Tutor Q&A')) : 'Tutor Q&A' }}
                    </span>
                </div>
            </div>

            <!-- Messages Scroll Box -->
            <div class="flex-1 p-6 overflow-y-auto space-y-4 bg-slate-50/30" id="chat-messages-container" x-ref="messagesContainer">
                
                @if(empty($messages) && !$activeConversation)
                    <!-- Initial Welcome State with Prompt Suggestions -->
                    <div class="max-w-md mx-auto my-8 text-center space-y-4">
                        <div class="w-16 h-16 rounded-3xl bg-purple-100 text-purple-600 flex items-center justify-center text-3xl mx-auto shadow-inner">
                            <i class="fas fa-sparkles"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Halo, {{ explode(' ', $student->full_name)[0] }}! 👋</h3>
                            <p class="text-xs text-slate-500 mt-1">Apa yang ingin kamu pelajari atau diskusikan hari ini?</p>
                        </div>

                        <!-- Quick Action Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-left pt-2">
                            <button @click="useQuickPrompt('Jelaskan konsep Matematika/IPA ini dengan cara paling sederhana')" class="p-3 rounded-2xl bg-white border border-slate-200 text-xs hover:border-purple-400 hover:shadow-xs transition text-slate-700 font-medium">
                                💡 Jelaskan konsep materi secara sederhana
                            </button>
                            <button @click="useQuickPrompt('Saya bingung memilih jurusan kuliah atau bidang kerja DUDI, bisa bantu?')" class="p-3 rounded-2xl bg-white border border-slate-200 text-xs hover:border-pink-400 hover:shadow-xs transition text-slate-700 font-medium">
                                🎓 Konsultasi Karir & Minat Bakat
                            </button>
                            <button @click="useQuickPrompt('Berikan tips belajar efektif agar nilai rapor meningkat')" class="p-3 rounded-2xl bg-white border border-slate-200 text-xs hover:border-indigo-400 hover:shadow-xs transition text-slate-700 font-medium">
                                📝 Tips manajemen waktu & belajar
                            </button>
                            <button @click="useQuickPrompt('Buatkan 3 soal kuis latihan beserta pembahasannya')" class="p-3 rounded-2xl bg-white border border-slate-200 text-xs hover:border-purple-400 hover:shadow-xs transition text-slate-700 font-medium">
                                ❓ Kuis latihan mandiri + pembahasan
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Render Existing Messages -->
                <template x-for="(msg, index) in messageList" :key="index">
                    <div :class="msg.sender === 'student' ? 'flex justify-end' : 'flex justify-start'">
                        <div class="flex gap-3 max-w-[88%] md:max-w-[80%]" :class="msg.sender === 'student' ? 'flex-row-reverse' : 'flex-row'">
                            <!-- Avatar -->
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 text-xs text-white shadow-xs font-bold"
                                 :class="msg.sender === 'student' ? 'bg-indigo-600' : 'bg-gradient-to-tr from-purple-600 to-violet-600'">
                                <template x-if="msg.sender === 'student'">
                                    <span>{{ strtoupper(substr($student->full_name, 0, 1)) }}</span>
                                </template>
                                <template x-if="msg.sender === 'ai'">
                                    <i class="fas fa-robot"></i>
                                </template>
                            </div>

                            <!-- Bubble Content -->
                            <div class="p-4 rounded-3xl text-xs sm:text-sm leading-relaxed shadow-xs"
                                 :class="msg.sender === 'student' 
                                    ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-tr-none font-medium' 
                                    : 'bg-white border border-slate-200/90 text-slate-800 rounded-tl-none prose prose-xs max-w-none'">
                                <div class="message-body" x-html="renderMarkdown(msg.message)"></div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Loading Spinner indicator -->
                <div x-show="isSending" class="flex justify-start">
                    <div class="flex items-center gap-3 p-3.5 bg-white border border-purple-200 rounded-2xl rounded-tl-none text-xs text-purple-700 shadow-xs">
                        <i class="fas fa-circle-notch fa-spin text-purple-600"></i>
                        <span class="font-bold">Pembda AI sedang berpikir...</span>
                    </div>
                </div>
            </div>

            <!-- Chat Form Input Bar -->
            <div class="p-4 bg-white border-t border-slate-100 shrink-0">
                <form @submit.prevent="submitMessage()" class="flex items-end gap-2">
                    <div class="flex-1 relative">
                        <textarea x-ref="promptInput" x-model="userInput" @keydown.enter.prevent="if(!$event.shiftKey) submitMessage()"
                                  placeholder="Ketik pertanyaan atau topik konsultasi Anda di sini... (Tekan Enter untuk mengirim)"
                                  rows="2"
                                  class="w-full p-3.5 rounded-2xl border border-slate-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 text-xs sm:text-sm resize-none outline-none transition"
                                  :disabled="isSending || usageInfo.remaining <= 0"></textarea>
                    </div>

                    <button type="submit" 
                            :disabled="isSending || !userInput.trim() || usageInfo.remaining <= 0"
                            class="px-5 py-3.5 rounded-2xl bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-extrabold text-xs shadow-md hover:from-purple-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center gap-2 shrink-0 cursor-pointer">
                        <span>Kirim</span>
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
        usageInfo: {
            remaining: {{ $usageInfo['remaining'] }},
            limit: {{ $usageInfo['limit'] }}
        },
        messageList: [
            @if(!empty($messages))
                @foreach($messages as $m)
                    { sender: '{{ $m->sender }}', message: `{!! addslashes($m->message) !!}` },
                @endforeach
            @endif
        ],

        init() {
            this.scrollToBottom();
            this.renderMathFormulas();
        },

        getModeLabel() {
            if (this.activeMode === 'bk_consultation') return 'BK & Karir';
            if (this.activeMode === 'lms_assistant') return 'Asisten LMS';
            return 'Tutor Q&A';
        },

        startNewConversation(mode) {
            this.activeMode = mode;
            this.activeConversationId = null;
            this.messageList = [];
            this.conversationTitle = (mode === 'bk_consultation') ? 'Konsultasi BK & Karir Baru' : (mode === 'lms_assistant' ? 'Asisten Belajar LMS' : 'Tutor Pelajaran Baru');
            this.$nextTick(() => {
                if (this.$refs.promptInput) this.$refs.promptInput.focus();
            });
        },

        useQuickPrompt(text) {
            this.userInput = text;
            this.submitMessage();
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

            // Push student prompt locally
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
                    this.messageList.push({ sender: 'ai', message: result.ai_message.message });
                    if (result.usage) {
                        this.usageInfo.remaining = result.usage.remaining;
                    }
                    this.renderMathFormulas();
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
