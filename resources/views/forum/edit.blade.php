@extends(auth()->user()->layout)

@section('title', 'Edit Obrolan')

@section('content')
<!-- Dynamic Google Fonts & Phosphor Icons -->
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;650;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>

<!-- PWA Manifest & App Shell Meta Tags -->
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#6366f1">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Pembda Space">
<link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">

<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
      navigator.serviceWorker.register('/sw.js');
    });
  }
</script>

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
    .bg-forum-base   { background-color: #f8fafc !important; }
    .bg-forum-panel  { background-color: #ffffff !important; }
    .bg-forum-card   { background-color: #ffffff !important; }
    .text-forum-title { color: #000000 !important; }
    .text-forum-body  { color: #0f172a !important; }
    .text-forum-muted { color: #0f172a !important; }
    .border-forum       { border-color: #cbd5e1 !important; }
    .border-forum-light { border-color: #cbd5e1 !important; }
    .bg-forum-light-5  { background-color: #ffffff !important; }
    .bg-forum-light-10 { background-color: #f8fafc !important; }
</style>

<!-- App Window Wrapper -->
<div class="w-full bg-forum-base text-forum-title font-['Inter'] rounded-3xl border border-forum mx-auto pt-4 pb-20 px-4 sm:px-6 relative shadow-sm" style="min-height: 85vh;" x-data="editPost()">
    
    <!-- Header -->
    <div class="flex items-center gap-4 bg-white/95 backdrop-blur-xl p-4 rounded-2xl border border-slate-200 mb-6 sticky top-4 z-40 shadow-sm">
        <a href="{{ route('forum.show', $thread) }}" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600 hover:text-slate-900 transition">
            <i class="ph-bold ph-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="forum-hdr text-xl font-bold text-slate-800">Edit Obrolan</h1>
            <div class="text-xs text-amber-600 font-bold uppercase tracking-wider">Perbarui informasi</div>
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

    <form action="{{ route('forum.update', $thread) }}" method="POST" enctype="multipart/form-data" class="space-y-6 max-w-5xl mx-auto">
        @csrf
        @method('PUT')

        <!-- Category Selection -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <label class="block text-xs font-bold text-slate-900 uppercase tracking-widest mb-4">Pilih Saluran <span class="text-rose-600">*</span></label>
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
                            :class="category === '{{ $key }}' ? 'border-{{ $color }}-500 bg-{{ $color }}-50 ring-2 ring-{{ $color }}-400' : 'border-slate-300 bg-slate-50 hover:bg-slate-100 hover:border-slate-400'"
                            class="flex items-center gap-3.5 p-3.5 rounded-xl border transition-all text-left group">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center text-xl flex-shrink-0 transition-colors"
                             :class="category === '{{ $key }}' ? 'bg-{{ $color }}-100 text-{{ $color }}-700 font-bold' : 'bg-slate-200 text-slate-900 group-hover:text-black font-bold'">
                            {{ $emoji }}
                        </div>
                        <div>
                            <div class="font-bold text-sm text-slate-900" :class="category === '{{ $key }}' ? 'text-{{ $color }}-800 font-bold' : ''">{{ $cleanLabel }}</div>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Main Content -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-5">
            <!-- Judul -->
            <div>
                <label class="block text-xs font-bold text-slate-900 uppercase tracking-widest mb-2">Judul Obrolan <span class="text-rose-600">*</span></label>
                <input type="text" name="title" value="{{ old('title', $thread->title) }}" 
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-300 focus:bg-white focus:border-indigo-600 rounded-xl text-slate-900 placeholder-slate-900 font-medium outline-none transition" 
                       placeholder="Judul postingan..." required>
            </div>

            <!-- Konten -->
            <div>
                <label class="block text-xs font-bold text-slate-900 uppercase tracking-widest mb-2">Pesan Utama <span class="text-rose-600">*</span></label>
                <textarea name="content" rows="8" 
                          class="w-full px-4 py-3 bg-slate-50 border border-slate-300 focus:bg-white focus:border-indigo-600 rounded-xl text-slate-900 placeholder-slate-900 font-medium outline-none transition resize-y" 
                          placeholder="Isi konten..." required>{{ old('content', $thread->content) }}</textarea>
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
                        <option value="{{ $badge->id }}" {{ $thread->reference_type === \App\Models\Badge::class && $thread->reference_id == $badge->id ? 'selected' : '' }}>{{ $badge->name }} (Poin: {{ $badge->requirement_value }})</option>
                    @endforeach
                </select>
            </div>

            @if(auth()->user()->isSiswa())
            <div x-show="perfType === 'grade'" class="space-y-2" style="display: none;">
                <select name="reference_id" class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-purple-500 rounded-xl text-sm font-bold text-slate-700 outline-none transition">
                    <option value="">-- Pilih Nilai CBT --</option>
                    @foreach($cbtResults as $result)
                        <option value="{{ $result->id }}" {{ $thread->reference_type === \App\Models\CbtExamResult::class && $thread->reference_id == $result->id ? 'selected' : '' }}>{{ $result->exam->exam_title }} - Nilai: {{ $result->final_score }}</option>
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
                    <div class="text-xs text-blue-600 font-bold uppercase tracking-wider">Buka/Tutup pendaftaran</div>
                </div>
            </div>
            <label class="flex items-center gap-3 cursor-pointer group">
                <div class="relative flex items-center">
                    <input type="checkbox" name="recruitment_enabled" value="1" {{ $thread->status !== 'completed' ? 'checked' : '' }} class="peer sr-only">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </div>
                <span class="text-sm font-bold text-slate-700 group-hover:text-slate-900 transition">Aktifkan pendaftaran anggota baru</span>
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
                        <input type="number" name="charity_target_amount" value="{{ old('charity_target_amount', $thread->charity_target_amount) }}" class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-red-500 rounded-xl text-sm text-slate-800 outline-none" placeholder="Target Rp...">
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
                        <input type="number" name="charity_target_volunteers" value="{{ old('charity_target_volunteers', $thread->charity_target_volunteers) }}" class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-red-500 rounded-xl text-sm text-slate-800 outline-none" placeholder="Jumlah orang...">
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
                        <option value="Mobile Legends" {{ old('game_name', $thread->game_name) === 'Mobile Legends' ? 'selected' : '' }}>🎮 Mobile Legends (MLBB)</option>
                        <option value="Valorant" {{ old('game_name', $thread->game_name) === 'Valorant' ? 'selected' : '' }}>🎯 Valorant</option>
                        <option value="PUBG Mobile" {{ old('game_name', $thread->game_name) === 'PUBG Mobile' ? 'selected' : '' }}>🔫 PUBG Mobile</option>
                        <option value="Roblox" {{ old('game_name', $thread->game_name) === 'Roblox' ? 'selected' : '' }}>🧱 Roblox</option>
                        <option value="Free Fire" {{ old('game_name', $thread->game_name) === 'Free Fire' ? 'selected' : '' }}>🔥 Free Fire</option>
                        <option value="Genshin Impact" {{ old('game_name', $thread->game_name) === 'Genshin Impact' ? 'selected' : '' }}>⚔️ Genshin Impact</option>
                        <option value="Minecraft" {{ old('game_name', $thread->game_name) === 'Minecraft' ? 'selected' : '' }}>📦 Minecraft</option>
                        <option value="Lainnya" {{ old('game_name', $thread->game_name) === 'Lainnya' ? 'selected' : '' }}>🕹️ Game Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Kode Room / Link Party / ID Game</label>
                    <input type="text" name="game_room_code" value="{{ old('game_room_code', $thread->game_room_code) }}" class="w-full px-4 py-3 bg-white border border-slate-200 focus:border-rose-500 rounded-xl text-sm text-slate-800 outline-none" placeholder="Contoh: ID 12345678 / Discord Link / Kode Room">
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
                    <option value="Modul Ajar" {{ old('file_category', $thread->file_category) === 'Modul Ajar' ? 'selected' : '' }}>📘 Modul Ajar & Catatan Pelajaran</option>
                    <option value="Bank Soal" {{ old('file_category', $thread->file_category) === 'Bank Soal' ? 'selected' : '' }}>📝 Bank Soal & Pembahasan Ujian</option>
                    <option value="Rangkuman Materi" {{ old('file_category', $thread->file_category) === 'Rangkuman Materi' ? 'selected' : '' }}>🧠 Rangkuman & Mind Map Materi</option>
                    <option value="Template / Presentasi" {{ old('file_category', $thread->file_category) === 'Template / Presentasi' ? 'selected' : '' }}>📊 Template PPT / Document</option>
                    <option value="Software / Tools" {{ old('file_category', $thread->file_category) === 'Software / Tools' ? 'selected' : '' }}>💻 Application / Software Tool</option>
                    <option value="Lainnya" {{ old('file_category', $thread->file_category) === 'Lainnya' ? 'selected' : '' }}>📁 Berkas Lainnya</option>
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
                        <button type="button" @click="imageUrl = ''; $event.target.closest('.space-y-3').querySelector('input[type=file]').value = ''" 
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
                @if($thread->attachment_path)
                    <div class="text-xs text-indigo-600 mt-2 font-bold"><i class="ph-bold ph-paperclip mr-1"></i> File Saat Ini: {{ $thread->attachment_name }}</div>
                @endif
            </div>
        </div>

        <!-- Submit -->
        <div class="flex gap-4 pt-2">
            <a href="{{ route('forum.show', $thread) }}" class="px-6 py-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition flex items-center justify-center">
                Batal
            </a>
            <button type="submit" class="flex-1 py-3.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white rounded-xl font-bold text-base shadow-md shadow-amber-200 hover:shadow-amber-300 transition-all flex items-center justify-center gap-2">
                <i class="ph-bold ph-floppy-disk"></i> Simpan Perubahan
            </button>
        </div>

    </form>
</div>

<script>
function editPost() {
    return {
        category: '{{ old('category', $thread->category) }}',
        perfType: '{{ $thread->reference_type === \App\Models\CbtExamResult::class ? 'grade' : 'badge' }}',
        hasTargetVolunteers: {{ !empty($thread->charity_target_volunteers) ? 'true' : 'false' }},
        hasTargetDonation: {{ !empty($thread->charity_target_amount) ? 'true' : 'false' }},
        imageUrl: '{{ $thread->image_path ? asset('storage/' . $thread->image_path) : '' }}',
        fileChosen(event) {
            const file = event.target.files[0];
            if (file) {
                this.imageUrl = URL.createObjectURL(file);
            } else {
                this.imageUrl = '{{ $thread->image_path ? asset('storage/' . $thread->image_path) : '' }}';
            }
        }
    }
}
</script>
@endsection
