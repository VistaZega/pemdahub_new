@extends(auth()->user()->layout)

@section('title', 'Buat Postingan Baru')

@section('content')
<!-- Dynamic Google Fonts & Phosphor Icons -->
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;650;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>

<script>
  if (typeof tailwind !== 'undefined') {
    tailwind.config = {
      corePlugins: {
        preflight: false,
      }
    }
  }
</script>
<style>
    .forum-hdr { font-family: 'Space Grotesk', sans-serif; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    
    /* ── LIGHT THEME COLORS ── */
    .bg-forum-base   { background-color: #f1f5f9 !important; }
    .bg-forum-panel  { background-color: #ffffff !important; }
    .bg-forum-card   { background-color: #ffffff !important; }
    .text-forum-title { color: #1e293b !important; }
    .text-forum-body  { color: #475569 !important; }
    .text-forum-muted { color: #94a3b8 !important; }
    .border-forum       { border-color: #e2e8f0 !important; }
    .border-forum-light { border-color: #cbd5e1 !important; }
    .bg-forum-light-5  { background-color: #f8fafc !important; }
    .bg-forum-light-10 { background-color: #f1f5f9 !important; }
</style>

<!-- App Window Wrapper -->
<div class="w-full bg-forum-base text-forum-title font-['Inter'] rounded-3xl border border-forum mx-auto pt-4 pb-20 px-4 sm:px-6 relative shadow-sm" style="min-height: 85vh;" x-data="createPost()">
    
    <!-- Header -->
    <div class="flex items-center gap-4 bg-white/95 backdrop-blur-xl p-4 rounded-2xl border border-slate-200 mb-6 sticky top-4 z-40 shadow-sm">
        <a href="{{ route('forum.index') }}" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600 hover:text-slate-900 transition">
            <i class="ph-bold ph-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="forum-hdr text-xl font-bold text-slate-800">Buat Topik Baru</h1>
            <div class="text-xs text-indigo-600 font-bold uppercase tracking-wider">Mulai Obrolan / Pamerkan Karya</div>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-600">
            <div class="font-bold mb-2 flex items-center gap-2"><i class="ph-bold ph-warning"></i> Ada kesalahan:</div>
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('forum.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 max-w-5xl mx-auto">
        @csrf

        <!-- Category Selection -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">Pilih Saluran <span class="text-rose-500">*</span></label>
            <input type="hidden" name="category" :value="category">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                @foreach(\App\Models\ForumThread::CATEGORIES as $key => $label)
                    @if($key === 'info' && !(auth()->user()->isSuperAdmin() || auth()->user()->isAdminSekolah() || auth()->user()->isGuru()))
                        @continue
                    @endif
                    @php
                        preg_match('/^[\p{Emoji_Presentation}\p{Extended_Pictographic}]/u', $label, $matches);
                        $emoji = $matches[0] ?? '💬';
                        $cleanLabel = trim(str_replace($emoji, '', $label));
                        
                        $color = match($key) {
                            'diskusi' => 'indigo', 'info' => 'amber', 'tanya_jawab' => 'cyan',
                            'sharing' => 'emerald', 'art_gallery' => 'pink', 'talent' => 'violet',
                            'performance' => 'purple', 'gaming' => 'rose', 'trending' => 'orange',
                            'project_idea' => 'blue', 'committee' => 'teal', 'charity' => 'red',
                            default => 'slate'
                        };
                    @endphp
                    
                    <button type="button" @click="category = '{{ $key }}'" 
                            :class="category === '{{ $key }}' ? 'border-{{ $color }}-500 bg-{{ $color }}-50/80 ring-2 ring-{{ $color }}-400/30' : 'border-slate-200 bg-slate-50/60 hover:bg-slate-100 hover:border-slate-300'"
                            class="flex items-center gap-3.5 p-3.5 rounded-xl border transition-all text-left group">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center text-xl flex-shrink-0 transition-colors"
                             :class="category === '{{ $key }}' ? 'bg-{{ $color }}-100 text-{{ $color }}-600' : 'bg-slate-200/60 text-slate-600 group-hover:text-slate-800'">
                            {{ $emoji }}
                        </div>
                        <div>
                            <div class="font-bold text-sm text-slate-700" :class="category === '{{ $key }}' ? 'text-{{ $color }}-700' : ''">{{ $cleanLabel }}</div>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Main Content -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-5">
            <!-- Judul -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Judul Obrolan <span class="text-rose-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" 
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:bg-white focus:border-indigo-500 rounded-xl text-slate-800 placeholder-slate-400 outline-none transition" 
                       placeholder="Contoh: Ada yang tau cara ngerjain soal matdis bab 3?" required>
            </div>

            <!-- Konten -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Pesan Utama <span class="text-rose-500">*</span></label>
                <textarea name="content" rows="6" 
                          class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:bg-white focus:border-indigo-500 rounded-xl text-slate-800 placeholder-slate-400 outline-none transition resize-y" 
                          placeholder="Ceritain detailnya di sini..." required>{{ old('content') }}</textarea>
            </div>
        </div>

        <!-- Performance / Achievements -->
        <div x-show="['performance', 'art_gallery', 'talent', 'portfolio'].includes(category)" style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             class="bg-purple-50/70 border border-purple-200 rounded-2xl p-6 shadow-sm space-y-4 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1.5 h-full bg-purple-500"></div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600"><i class="ph-bold ph-medal text-xl"></i></div>
                <div>
                    <h4 class="forum-hdr text-sm font-bold text-slate-800">Hubungkan Prestasi</h4>
                    <div class="text-xs text-purple-600 font-bold uppercase tracking-wider">Buktikan karya/skor kamu valid</div>
                </div>
            </div>
            
            <div class="flex flex-wrap items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer text-sm font-bold text-slate-700">
                    <input type="radio" name="reference_type" value="badge" x-model="perfType" class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                    <span>🎖️ Lencana Terkunci</span>
                </label>
                @if(auth()->user()->isSiswa())
                <label class="flex items-center gap-2 cursor-pointer text-sm font-bold text-slate-700">
                    <input type="radio" name="reference_type" value="grade" x-model="perfType" class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                    <span>💯 Nilai Ujian CBT</span>
                </label>
                @endif
            </div>

            <!-- Selectors -->
            <div x-show="perfType === 'badge'" class="space-y-2">
                <select name="reference_id" class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-purple-500 rounded-xl text-sm font-bold text-slate-700 outline-none transition">
                    <option value="">-- Pilih Lencana Terhebatmu --</option>
                    @foreach($badges as $badge)
                        <option value="{{ $badge->id }}">{{ $badge->name }} (Poin: {{ $badge->requirement_value }})</option>
                    @endforeach
                </select>
            </div>

            @if(auth()->user()->isSiswa())
            <div x-show="perfType === 'grade'" class="space-y-2" style="display: none;">
                <select name="reference_id" class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-purple-500 rounded-xl text-sm font-bold text-slate-700 outline-none transition">
                    <option value="">-- Pilih Nilai CBT --</option>
                    @foreach($cbtResults as $result)
                        <option value="{{ $result->id }}">{{ $result->exam->exam_title }} - Nilai: {{ $result->final_score }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>

        <!-- Collab -->
        <div x-show="['project_idea', 'committee'].includes(category)" style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             class="bg-blue-50/70 border border-blue-200 rounded-2xl p-6 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1.5 h-full bg-blue-500"></div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600"><i class="ph-bold ph-handshake text-xl"></i></div>
                <div>
                    <h4 class="forum-hdr text-sm font-bold text-slate-800">Rekrutmen Tim</h4>
                    <div class="text-xs text-blue-600 font-bold uppercase tracking-wider">Cari rekan kolaborasi</div>
                </div>
            </div>
            <label class="flex items-center gap-3 cursor-pointer group">
                <div class="relative flex items-center">
                    <input type="checkbox" name="recruitment_enabled" value="1" checked class="peer sr-only">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </div>
                <span class="text-sm font-bold text-slate-700 group-hover:text-slate-900 transition">Buka pendaftaran anggota baru</span>
            </label>
        </div>

        <!-- Charity -->
        <div x-show="category === 'charity'" style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             class="bg-red-50/70 border border-red-200 rounded-2xl p-6 shadow-sm space-y-5 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1.5 h-full bg-red-500"></div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center text-red-600"><i class="ph-bold ph-heart text-xl"></i></div>
                <div>
                    <h4 class="forum-hdr text-sm font-bold text-slate-800">Target Aksi Sosial</h4>
                    <div class="text-xs text-red-600 font-bold uppercase tracking-wider">Tentukan tujuan muliamu</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <div class="relative flex items-center">
                            <input type="checkbox" x-model="hasTargetDonation" class="peer sr-only">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                        </div>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-slate-900 transition">Target Donasi (Uang)</span>
                    </label>
                    <div x-show="hasTargetDonation" style="display:none;">
                        <input type="number" name="charity_target_amount" class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-red-500 rounded-xl text-sm text-slate-800 outline-none" placeholder="Target Rp...">
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <div class="relative flex items-center">
                            <input type="checkbox" x-model="hasTargetVolunteers" class="peer sr-only">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                        </div>
                        <span class="text-sm font-bold text-slate-700 group-hover:text-slate-900 transition">Target Relawan (Orang)</span>
                    </label>
                    <div x-show="hasTargetVolunteers" style="display:none;">
                        <input type="number" name="charity_target_volunteers" class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-red-500 rounded-xl text-sm text-slate-800 outline-none" placeholder="Jumlah orang...">
                    </div>
                </div>
            </div>
        </div>

        <!-- Gaming Mabar Panel -->
        <div x-show="category === 'gaming'" style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             class="bg-rose-50/70 border border-rose-200 rounded-2xl p-6 shadow-sm space-y-4 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1.5 h-full bg-rose-500"></div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600"><i class="ph-bold ph-game-controller text-xl"></i></div>
                <div>
                    <h4 class="forum-hdr text-sm font-bold text-slate-800">Detail Lobi Mabar</h4>
                    <div class="text-xs text-rose-600 font-bold uppercase tracking-wider">Ajak kawan seangkatan mabar game pilihanmu</div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Nama Game</label>
                    <select name="game_name" class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-rose-500 rounded-xl text-sm font-bold text-slate-700 outline-none transition">
                        <option value="Mobile Legends">🎮 Mobile Legends (MLBB)</option>
                        <option value="Valorant">🎯 Valorant</option>
                        <option value="PUBG Mobile">🔫 PUBG Mobile</option>
                        <option value="Roblox">🧱 Roblox</option>
                        <option value="Free Fire">🔥 Free Fire</option>
                        <option value="Genshin Impact">⚔️ Genshin Impact</option>
                        <option value="Minecraft">📦 Minecraft</option>
                        <option value="Lainnya">🕹️ Game Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Kode Room / Link Party / ID Game</label>
                    <input type="text" name="game_room_code" class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-rose-500 rounded-xl text-sm text-slate-800 outline-none" placeholder="Contoh: ID 12345678 / Discord Link / Kode Room">
                </div>
            </div>
        </div>

        <!-- Bank File Panel -->
        <div x-show="category === 'sharing'" style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             class="bg-emerald-50/70 border border-emerald-200 rounded-2xl p-6 shadow-sm space-y-4 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1.5 h-full bg-emerald-500"></div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600"><i class="ph-bold ph-folder-open text-xl"></i></div>
                <div>
                    <h4 class="forum-hdr text-sm font-bold text-slate-800">Kategori Dokumen / File</h4>
                    <div class="text-xs text-emerald-600 font-bold uppercase tracking-wider">Bagikan berkas belajar untuk seluruh kawan</div>
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Jenis Berkas</label>
                <select name="file_category" class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-emerald-500 rounded-xl text-sm font-bold text-slate-700 outline-none transition">
                    <option value="Modul Ajar">📘 Modul Ajar & Catatan Pelajaran</option>
                    <option value="Bank Soal">📝 Bank Soal & Pembahasan Ujian</option>
                    <option value="Rangkuman Materi">🧠 Rangkuman & Mind Map Materi</option>
                    <option value="Template / Presentasi">📊 Template PPT / Document</option>
                    <option value="Software / Tools">💻 Application / Software Tool</option>
                    <option value="Lainnya">📁 Berkas Lainnya</option>
                </select>
            </div>
        </div>

        <!-- Attachments -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Image -->
            <div class="space-y-3">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2"><i class="ph-bold ph-image text-indigo-500 mr-1"></i> Gambar Utama</label>
                <input type="file" name="image" accept="image/*" @change="fileChosen" 
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 focus:border-indigo-500 rounded-xl text-sm text-slate-600 file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-100 file:text-indigo-700 file:font-bold cursor-pointer transition">
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Format JPG/PNG, Maks 5MB</p>
                
                <template x-if="imageUrl">
                    <div class="mt-3 relative inline-block rounded-xl overflow-hidden border border-slate-200 shadow-sm">
                        <img :src="imageUrl" class="h-32 w-auto object-cover">
                        <button type="button" @click="imageUrl = null; $event.target.closest('.space-y-3').querySelector('input[type=file]').value = ''" 
                                class="absolute top-2 right-2 w-7 h-7 bg-slate-800/80 hover:bg-rose-600 text-white rounded-full flex items-center justify-center backdrop-blur-md transition shadow-md">
                            <i class="ph-bold ph-x text-xs"></i>
                        </button>
                    </div>
                </template>
            </div>

            <!-- File -->
            <div class="space-y-3">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2"><i class="ph-bold ph-file-arrow-up text-fuchsia-500 mr-1"></i> Lampiran File</label>
                <input type="file" name="attachment" 
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 focus:border-fuchsia-500 rounded-xl text-sm text-slate-600 file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:bg-fuchsia-100 file:text-fuchsia-700 file:font-bold cursor-pointer transition">
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">PDF/ZIP/DOCS, Maks 10MB</p>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex gap-4 pt-2">
            <a href="{{ route('forum.index') }}" class="px-6 py-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition flex items-center justify-center">
                Batal
            </a>
            <button type="submit" class="flex-1 py-3.5 bg-gradient-to-r from-indigo-500 to-fuchsia-500 hover:from-indigo-600 hover:to-fuchsia-600 text-white rounded-xl font-bold text-base shadow-md shadow-indigo-200 hover:shadow-indigo-300 transition-all flex items-center justify-center gap-2">
                <i class="ph-bold ph-rocket-launch"></i> Posting Sekarang
            </button>
        </div>

    </form>
</div>

<script>
function createPost() {
    return {
        category: 'diskusi',
        perfType: 'badge',
        hasTargetVolunteers: false,
        hasTargetDonation: false,
        imageUrl: null,
        fileChosen(event) {
            const file = event.target.files[0];
            if (file) {
                this.imageUrl = URL.createObjectURL(file);
            } else {
                this.imageUrl = null;
            }
        }
    }
}
</script>
@endsection
