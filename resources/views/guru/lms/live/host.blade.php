<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Host Live Game - PembdaHUB</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <style>
        body { background-color: #0f172a; color: white; font-family: 'Inter', sans-serif; overflow: hidden; }
        .bg-gradient { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); }
        .pin-box { font-size: 5rem; font-weight: 900; letter-spacing: 10px; color: #fff; text-shadow: 0 4px 20px rgba(255,255,255,0.3); }
        .player-card { animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        @keyframes popIn {
            0% { transform: scale(0.5); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .btn-xl { padding: 1rem 3rem; font-size: 1.5rem; font-weight: 800; border-radius: 1rem; cursor: pointer; transition: all 0.2s; }
        .btn-xl:hover { transform: scale(1.05); }
        .btn-xl:active { transform: scale(0.95); }
    </style>
</head>
<body class="bg-gradient w-screen h-screen flex flex-col" x-data="hostGame()">
    
    <!-- Navbar / Header -->
    <div class="w-full bg-slate-900/80 backdrop-blur border-b border-slate-700/50 p-4 flex justify-between items-center z-50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-cyan-500/20 flex items-center justify-center">
                <i class="fas fa-gamepad text-cyan-400 text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold">{{ $game->title }}</h1>
                <p class="text-xs text-slate-400">PembdaHUB Live</p>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div class="px-4 py-2 bg-slate-800 rounded-lg font-mono text-xl font-bold text-cyan-400">
                PIN: {{ $session->pin_code }}
            </div>
            <button @click="endGame" class="px-4 py-2 bg-red-500/20 text-red-400 hover:bg-red-500 hover:text-white rounded-lg transition font-bold text-sm">
                Akhiri Game
            </button>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="flex-1 w-full relative overflow-hidden flex flex-col items-center justify-center p-8">
        
        <!-- WAITING ROOM -->
        <div x-show="state.status === 'waiting'" class="w-full h-full flex flex-col items-center justify-center" style="display: none;">
            <h2 class="text-3xl font-bold mb-2">Gabung di <span class="text-cyan-400">pembdahub.com/live</span></h2>
            <p class="text-xl text-slate-300 mb-8">Masukkan PIN Game di bawah ini:</p>
            
            <div class="bg-white/10 p-8 rounded-xl backdrop-blur-xl border border-white/20 mb-12 shadow-2xl">
                <div class="pin-box">{{ $session->pin_code }}</div>
            </div>

            <div class="flex gap-4 items-center mb-8">
                <div class="flex items-center gap-2 px-4 py-2 bg-slate-800 rounded-full">
                    <i class="fas fa-users text-slate-400"></i>
                    <span class="font-bold text-xl" x-text="state.players.length"></span>
                </div>
                <button @click="startGame" x-show="state.players.length > 0" class="btn-xl bg-green-500 hover:bg-green-400 text-white shadow-[0_0_20px_rgba(34,197,94,0.4)]">
                    Mulai Sekarang <i class="fas fa-play ml-2"></i>
                </button>
            </div>

            <!-- Player Grid -->
            <div class="w-full max-w-5xl flex flex-wrap justify-center gap-3 h-64 overflow-y-auto content-start">
                <template x-for="player in state.players" :key="player.id">
                    <div class="player-card px-4 py-2 bg-slate-800 border border-slate-600 rounded-lg text-lg font-bold shadow-lg" x-text="player.nickname"></div>
                </template>
            </div>
        </div>

        <!-- QUESTION SCREEN -->
        <div x-show="state.status === 'question'" class="w-full h-full flex flex-col" style="display: none;">
            <div class="w-full text-center mb-4">
                <span class="px-4 py-1 bg-slate-800 rounded-full text-sm font-bold text-slate-300">
                    Pertanyaan <span x-text="state.current_question_index + 1"></span> dari <span x-text="questions.length"></span>
                </span>
            </div>
            
            <div class="bg-white text-slate-900 rounded-xl p-8 mb-8 text-center shadow-2xl relative">
                <h2 class="text-4xl md:text-5xl font-semibold mb-4 leading-tight" x-text="currentQuestion?.questionText"></h2>
                
                <!-- Timer Circle -->
                <div class="absolute -top-8 -right-8 w-24 h-24 bg-purple-600 rounded-full border-4 border-slate-900 flex items-center justify-center shadow-xl">
                    <span class="text-3xl font-semibold text-white" x-text="timeLeft"></span>
                </div>

                <!-- Answers count -->
                <div class="absolute -bottom-6 left-1/2 transform -translate-x-1/2 bg-slate-900 text-white px-6 py-2 rounded-full font-bold shadow-lg border border-slate-700">
                    <span x-text="state.answersCount"></span> / <span x-text="state.players.length"></span> Menjawab
                </div>
            </div>

            <!-- Options (Visual representation of colors) -->
            <div class="flex-1 grid grid-cols-2 gap-4 mt-8" x-show="gameType === 'quiz'">
                <div class="bg-red-500 rounded-xl flex items-center justify-center p-6 shadow-[0_8px_0_#991b1b]">
                    <span class="text-3xl font-bold text-white text-center" x-text="currentQuestion?.options[0]"></span>
                </div>
                <div class="bg-blue-500 rounded-xl flex items-center justify-center p-6 shadow-[0_8px_0_#1e40af]">
                    <span class="text-3xl font-bold text-white text-center" x-text="currentQuestion?.options[1]"></span>
                </div>
                <div class="bg-yellow-500 rounded-xl flex items-center justify-center p-6 shadow-[0_8px_0_#a16207]">
                    <span class="text-3xl font-bold text-white text-center" x-text="currentQuestion?.options[2]"></span>
                </div>
                <div class="bg-green-500 rounded-xl flex items-center justify-center p-6 shadow-[0_8px_0_#166534]">
                    <span class="text-3xl font-bold text-white text-center" x-text="currentQuestion?.options[3]"></span>
                </div>
            </div>
            
            <div class="flex-1 grid grid-cols-2 gap-4 mt-8" x-show="gameType === 'true_false'">
                <div class="bg-blue-500 rounded-xl flex items-center justify-center p-6 shadow-[0_8px_0_#1e40af]">
                    <span class="text-5xl font-semibold text-white">BENAR</span>
                </div>
                <div class="bg-red-500 rounded-xl flex items-center justify-center p-6 shadow-[0_8px_0_#991b1b]">
                    <span class="text-5xl font-semibold text-white">SALAH</span>
                </div>
            </div>
            
            <div class="w-full flex justify-end mt-4">
                <button @click="skipQuestion" class="px-6 py-3 bg-slate-700 hover:bg-slate-600 rounded-xl font-bold transition">
                    Skip / Tampilkan Hasil <i class="fas fa-forward ml-2"></i>
                </button>
            </div>
        </div>

        <!-- LEADERBOARD / RESULT SCREEN -->
        <div x-show="state.status === 'leaderboard'" class="w-full h-full flex flex-col items-center justify-center" style="display: none;">
            <h2 class="text-4xl font-semibold mb-8 text-cyan-400">Papan Peringkat</h2>
            
            <div class="w-full max-w-2xl bg-white/10 p-6 rounded-xl backdrop-blur border border-white/10 mb-8">
                <template x-for="(player, idx) in state.players.slice(0, 5)" :key="player.id">
                    <div class="flex items-center gap-4 p-4 border-b border-white/10 last:border-0">
                        <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center font-semibold text-lg" 
                             :class="{'text-yellow-400': idx===0, 'text-gray-300': idx===1, 'text-orange-400': idx===2}">
                            <span x-text="idx + 1"></span>
                        </div>
                        <div class="flex-1 font-bold text-xl" x-text="player.nickname"></div>
                        <div class="font-semibold text-2xl" x-text="player.score"></div>
                    </div>
                </template>
            </div>
            
            <button @click="nextQuestion" class="btn-xl bg-cyan-600 hover:bg-cyan-500 text-white shadow-[0_0_20px_rgba(8,145,178,0.4)]">
                Lanjut <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>

        <!-- FINISHED / PODIUM -->
        <div x-show="state.status === 'finished'" class="w-full h-full flex flex-col items-center justify-center" style="display: none;">
            <h1 class="text-6xl font-semibold mb-12 text-yellow-400" style="text-shadow: 0 0 30px rgba(250,204,21,0.5);">PODIUM</h1>
            
            <div class="flex items-end justify-center gap-4 h-64 mb-12 w-full max-w-4xl">
                <!-- Juara 2 -->
                <div class="flex flex-col items-center w-1/3" x-show="state.players.length >= 2">
                    <div class="font-bold text-2xl mb-2 truncate w-full text-center" x-text="state.players[1]?.nickname"></div>
                    <div class="font-semibold text-xl text-slate-300 mb-4" x-text="state.players[1]?.score"></div>
                    <div class="w-full bg-slate-300 h-3/4 rounded-t-lg flex justify-center pt-4 shadow-lg">
                        <span class="text-4xl font-semibold text-slate-800">2</span>
                    </div>
                </div>
                <!-- Juara 1 -->
                <div class="flex flex-col items-center w-1/3" x-show="state.players.length >= 1">
                    <i class="fas fa-crown text-5xl text-yellow-400 mb-4"></i>
                    <div class="font-bold text-3xl mb-2 truncate w-full text-center" x-text="state.players[0]?.nickname"></div>
                    <div class="font-semibold text-2xl text-yellow-400 mb-4" x-text="state.players[0]?.score"></div>
                    <div class="w-full bg-yellow-400 h-full rounded-t-lg flex justify-center pt-4 shadow-2xl relative z-10">
                        <span class="text-5xl font-semibold text-yellow-800">1</span>
                    </div>
                </div>
                <!-- Juara 3 -->
                <div class="flex flex-col items-center w-1/3" x-show="state.players.length >= 3">
                    <div class="font-bold text-xl mb-2 truncate w-full text-center" x-text="state.players[2]?.nickname"></div>
                    <div class="font-semibold text-lg text-orange-400 mb-4" x-text="state.players[2]?.score"></div>
                    <div class="w-full bg-orange-400 h-1/2 rounded-t-lg flex justify-center pt-4 shadow-lg">
                        <span class="text-3xl font-semibold text-orange-900">3</span>
                    </div>
                </div>
            </div>

            <a href="{{ route('guru.lms.index') }}" class="px-8 py-4 bg-slate-800 hover:bg-slate-700 rounded-xl font-bold transition text-lg border border-slate-600">
                Kembali ke Dashboard LMS
            </a>
        </div>

    </div>

    <script>
        const gameData = {!! json_encode($game->game_data) !!};
        const sessionId = {{ $session->id }};
        const defaultTimeLimit = {{ $game->time_limit ?? 20 }};
        const csrfToken = '{{ csrf_token() }}';

        document.addEventListener('alpine:init', () => {
            Alpine.data('hostGame', () => ({
                gameType: '{{ $game->game_type }}',
                questions: gameData.questions || [],
                state: {
                    status: '{{ $session->status }}',
                    current_question_index: {{ $session->current_question_index }},
                    players: [],
                    answersCount: 0,
                    distribution: {}
                },
                timeLeft: 0,
                timerInterval: null,
                pollInterval: null,

                get currentQuestion() {
                    return this.questions[this.state.current_question_index];
                },

                init() {
                    this.startPolling();
                },

                startPolling() {
                    this.pollInterval = setInterval(async () => {
                        try {
                            const res = await fetch(`/guru/lms/live/${sessionId}/poll`);
                            const data = await res.json();
                            
                            // If status changed to something else by backend (unlikely for host, but possible)
                            this.state = data;

                            // Auto show leaderboard if all players answered
                            if (this.state.status === 'question' && this.state.answersCount >= this.state.players.length && this.state.players.length > 0) {
                                this.skipQuestion();
                            }
                        } catch(e) { console.error("Polling error", e); }
                    }, 2000);
                },

                async updateState(action) {
                    try {
                        const res = await fetch(`/guru/lms/live/${sessionId}/state`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({ action })
                        });
                        if (res.ok) {
                            if (action === 'start' || action === 'next') {
                                this.state.status = 'question';
                                if (action === 'next') this.state.current_question_index++;
                                this.startQuestionTimer();
                            } else if (action === 'show_leaderboard') {
                                this.state.status = 'leaderboard';
                                clearInterval(this.timerInterval);
                            } else if (action === 'end') {
                                this.state.status = 'finished';
                                clearInterval(this.timerInterval);
                            }
                        }
                    } catch(e) { console.error(e); }
                },

                startGame() {
                    if(this.questions.length === 0) return alert('Kuis tidak memiliki pertanyaan!');
                    this.updateState('start');
                },

                startQuestionTimer() {
                    this.timeLeft = defaultTimeLimit;
                    clearInterval(this.timerInterval);
                    this.timerInterval = setInterval(() => {
                        this.timeLeft--;
                        if (this.timeLeft <= 0) {
                            clearInterval(this.timerInterval);
                            this.skipQuestion(); // Waktu habis
                        }
                    }, 1000);
                },

                skipQuestion() {
                    this.updateState('show_leaderboard');
                },

                nextQuestion() {
                    if (this.state.current_question_index + 1 >= this.questions.length) {
                        this.updateState('end');
                    } else {
                        this.updateState('next');
                    }
                },

                endGame() {
                    if(confirm('Yakin ingin mengakhiri permainan sekarang?')) {
                        this.updateState('end');
                    }
                }
            }));
        });
    </script>
</body>
</html>
