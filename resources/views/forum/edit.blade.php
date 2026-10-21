@extends(auth()->user()->layout)

@section('title', 'Edit Obrolan')

@section('content')
<!-- Dynamic Google Fonts (Plus Jakarta Sans & JetBrains Mono) & Phosphor Icons -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
{{-- Alpine.js dimuat oleh master layout --}}

<!-- PWA Manifest & App Shell Meta Tags -->
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#121316">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="PembdaSPACE">
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
    :root {
        font-family: 'Plus Jakarta Sans', sans-serif;
        --border-ink: #121316;
    }

    .forum-hdr { font-family: 'Plus Jakarta Sans', sans-serif; }
    .font-mono-code { font-family: 'JetBrains Mono', monospace; }
    .no-scrollbar::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; }
    .no-scrollbar { -ms-overflow-style: none !important; scrollbar-width: none !important; }

    /* Tactile Solid Drop-Shadow Buttons */
    .btn-tactile-red {
        background: #ff3823;
        color: #ffffff;
        border: 2px solid #121316;
        box-shadow: 3px 3px 0px #121316;
        transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .btn-tactile-red:hover {
        transform: translate(1px, 1px);
        box-shadow: 2px 2px 0px #121316;
    }
    .btn-tactile-red:active {
        transform: translate(3px, 3px);
        box-shadow: 0px 0px 0px #121316;
    }

    .btn-tactile-white {
        background: #ffffff;
        color: #121316;
        border: 2px solid #121316;
        box-shadow: 3px 3px 0px #121316;
        transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .btn-tactile-white:hover {
        background: #faf8f5;
        transform: translate(1px, 1px);
        box-shadow: 2px 2px 0px #121316;
    }
    .btn-tactile-white:active {
        transform: translate(3px, 3px);
        box-shadow: 0px 0px 0px #121316;
    }

    .btn-tactile-yellow {
        background: #fde047;
        color: #121316;
        border: 2px solid #121316;
        box-shadow: 3px 3px 0px #121316;
        transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* Graph Canvas Background */
    .graph-canvas {
        background-color: #faf7f2;
        background-image: 
            radial-gradient(circle at 10% 15%, rgba(99, 102, 241, 0.08) 0%, transparent 35%),
            radial-gradient(circle at 90% 85%, rgba(236, 72, 153, 0.08) 0%, transparent 35%),
            radial-gradient(circle at 50% 50%, rgba(245, 158, 11, 0.05) 0%, transparent 60%),
            radial-gradient(#d1cfc7 1.2px, transparent 1.2px);
        background-size: auto, auto, auto, 22px 22px;
    }
</style>

<!-- App Window Wrapper -->
<div class="w-full text-[#121316] font-['Plus_Jakarta_Sans',sans-serif] rounded-3xl border-2 border-[#121316] mx-auto pt-4 pb-20 px-4 sm:px-6 relative shadow-[6px_6px_0px_#121316] graph-canvas" style="min-height: 85vh;" x-data="editPost()">
    
    <!-- Header -->
    <div class="flex items-center gap-4 bg-white/95 backdrop-blur-xl p-3.5 sm:p-4 rounded-2xl border-2 border-[#121316] mb-6 sticky top-4 z-40 shadow-[3px_3px_0px_#121316]">
        <a href="{{ route('forum.show', $thread) }}" class="btn-tactile-white w-10 h-10 rounded-xl flex items-center justify-center text-[#121316] transition">
            <i class="fa-solid fa-arrow-left text-lg"></i>
        </a>
        <div>
            <h1 class="forum-hdr text-base sm:text-xl font-black text-[#121316]">Edit Obrolan</h1>
            <div class="text-[10px] sm:text-xs font-mono-code font-bold text-amber-700 uppercase tracking-wider">Perbarui Informasi Post</div>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border-2 border-rose-600 text-rose-900 shadow-[2px_2px_0px_#e11d48]">
            <div class="font-black mb-1.5 flex items-center gap-2"><i class="fa-solid fa-triangle-exclamation"></i> Ada kesalahan:</div>
            <ul class="list-disc list-inside text-xs font-bold space-y-1">
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
        <div class="bg-white border-2 border-[#121316] rounded-2xl p-5 sm:p-6 shadow-[4px_4px_0px_#121316]">
            <label class="block text-xs font-mono-code font-black text-[#121316] uppercase tracking-widest mb-3.5">PILIH SALURAN <span class="text-[#ff3823]">*</span></label>
            <input type="hidden" name="category" :value="category">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2.5">
                @foreach(\App\Models\ForumThread::CATEGORIES as $key => $label)
                    @if($key === 'info' && !(auth()->user()->isSuperAdmin() || auth()->user()->isAdminSekolah() || auth()->user()->isGuru()))
                        @continue
                    @endif
                    @php
                        preg_match('/^[\p{Emoji_Presentation}\p{Extended_Pictographic}]/u', $label, $matches);
                        $emoji = $matches[0] ?? '💬';
                        $cleanLabel = trim(str_replace($emoji, '', $label));
                    @endphp
                    
                    <button type="button" @click="category = '{{ $key }}'" 
                            :class="category === '{{ $key }}' ? 'border-[#121316] bg-[#fde047] shadow-[2px_2px_0px_#121316] translate-x-0.5 translate-y-0.5' : 'border-[#121316] bg-[#faf8f5] hover:bg-white shadow-[1px_1px_0px_#121316]'"
                            class="flex items-center gap-3 p-3 rounded-xl border-2 transition-all text-left group">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center text-lg flex-shrink-0 bg-white border border-[#121316]">
                            {{ $emoji }}
                        </div>
                        <div>
                            <div class="font-extrabold text-xs sm:text-sm text-[#121316]">{{ $cleanLabel }}</div>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Main Content -->
        <div class="bg-white border-2 border-[#121316] rounded-2xl p-5 sm:p-6 shadow-[4px_4px_0px_#121316] space-y-4">
            <!-- Judul -->
            <div>
                <label class="block text-xs font-mono-code font-black text-[#121316] uppercase tracking-widest mb-1.5">Judul Obrolan <span class="text-[#ff3823]">*</span></label>
                <input type="text" name="title" value="{{ old('title', $thread->title) }}" 
                       class="w-full px-4 py-2.5 bg-[#faf8f5] border-2 border-[#121316] focus:bg-white rounded-xl text-xs sm:text-sm text-[#121316] font-bold outline-none focus:shadow-[3px_3px_0px_#ff3823] transition" 
                       placeholder="Judul postingan..." required>
            </div>

            <!-- Konten -->
            <div>
                <label class="block text-xs font-mono-code font-black text-[#121316] uppercase tracking-widest mb-1.5">Pesan Utama <span class="text-[#ff3823]">*</span></label>
                <textarea name="content" rows="8" 
                          class="w-full px-4 py-3 bg-[#faf8f5] border-2 border-[#121316] focus:bg-white rounded-xl text-xs sm:text-sm text-[#121316] font-bold outline-none focus:shadow-[3px_3px_0px_#ff3823] transition resize-y" 
                          placeholder="Isi konten..." required>{{ old('content', $thread->content) }}</textarea>
            </div>
        </div>

        <!-- Performance / Achievements -->
        <div x-show="['performance', 'art_gallery', 'talent', 'portfolio'].includes(category)" style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             class="bg-white border-2 border-[#121316] rounded-2xl p-5 sm:p-6 shadow-[3px_3px_0px_#121316] space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#fde047] border border-[#121316] flex items-center justify-center text-[#121316]"><i class="fa-solid fa-medal text-xl"></i></div>
                <div>
                    <h4 class="forum-hdr text-sm font-black text-[#121316]">Hubungkan Prestasi</h4>
                    <div class="text-xs font-mono-code text-[#2563eb] font-bold uppercase tracking-wider">Buktikan karya/skor kamu valid</div>
                </div>
            </div>
            
            <div class="flex flex-wrap items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer text-xs sm:text-sm font-bold text-[#121316]">
                    <input type="radio" name="reference_type" value="badge" x-model="perfType" class="w-4 h-4 text-[#ff3823]">
                    <span>🎖️ Lencana Terkunci</span>
                </label>
                @if(auth()->user()->isSiswa())
                <label class="flex items-center gap-2 cursor-pointer text-xs sm:text-sm font-bold text-[#121316]">
                    <input type="radio" name="reference_type" value="grade" x-model="perfType" class="w-4 h-4 text-[#ff3823]">
                    <span>💯 Nilai Ujian CBT</span>
                </label>
                @endif
            </div>

            <div x-show="perfType === 'badge'" class="space-y-2">
                <select name="reference_id" class="w-full px-4 py-2.5 bg-[#faf8f5] border-2 border-[#121316] rounded-xl text-xs sm:text-sm font-bold text-[#121316] outline-none">
                    <option value="">-- Pilih Lencana Terhebatmu --</option>
                    @foreach($badges as $badge)
                        <option value="{{ $badge->id }}" {{ $thread->reference_type === \App\Models\Badge::class && $thread->reference_id == $badge->id ? 'selected' : '' }}>{{ $badge->name }} (Poin: {{ $badge->requirement_value }})</option>
                    @endforeach
                </select>
            </div>

            @if(auth()->user()->isSiswa())
            <div x-show="perfType === 'grade'" class="space-y-2" style="display: none;">
                <select name="reference_id" class="w-full px-4 py-2.5 bg-[#faf8f5] border-2 border-[#121316] rounded-xl text-xs sm:text-sm font-bold text-[#121316] outline-none">
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
             class="bg-white border-2 border-[#121316] rounded-2xl p-5 sm:p-6 shadow-[3px_3px_0px_#121316]">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-[#fde047] border border-[#121316] flex items-center justify-center text-[#121316]"><i class="fa-solid fa-handshake-angle text-xl"></i></div>
                <div>
                    <h4 class="forum-hdr text-sm font-black text-[#121316]">Rekrutmen Tim</h4>
                    <div class="text-xs font-mono-code text-[#2563eb] font-bold uppercase tracking-wider">Buka/Tutup pendaftaran</div>
                </div>
            </div>
            <label class="flex items-center gap-3 cursor-pointer group">
                <input type="checkbox" name="recruitment_enabled" value="1" {{ $thread->status !== 'completed' ? 'checked' : '' }} class="w-4 h-4 text-[#ff3823]">
                <span class="text-xs sm:text-sm font-black text-[#121316]">Aktifkan pendaftaran anggota baru</span>
            </label>
        </div>

        <!-- Gaming Mabar Panel -->
        <div x-show="category === 'gaming'" style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             class="bg-white border-2 border-[#121316] rounded-2xl p-5 sm:p-6 shadow-[3px_3px_0px_#121316] space-y-4">
            <div class="flex items-center gap-3 mb-1">
                <div class="w-10 h-10 rounded-xl border border-slate-900 flex items-center justify-center text-white" style="background-color: #dc2626 !important; color: #ffffff !important;"><i class="fa-solid fa-gamepad text-xl"></i></div>
                <div>
                    <h4 class="forum-hdr text-sm font-black text-[#121316]">Detail Lobi Mabar</h4>
                    <div class="text-xs font-mono-code text-[#ff3823] font-bold uppercase tracking-wider">Ajak kawan seangkatan mabar game pilihanmu</div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-mono-code font-black text-[#121316] uppercase tracking-widest mb-1.5">Nama Game</label>
                    <select name="game_name" class="w-full px-4 py-2.5 bg-[#faf8f5] border-2 border-[#121316] rounded-xl text-xs sm:text-sm font-bold text-[#121316] outline-none">
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
                    <label class="block text-xs font-mono-code font-black text-[#121316] uppercase tracking-widest mb-1.5">Kode Room / Link Party / ID Game</label>
                    <input type="text" name="game_room_code" value="{{ old('game_room_code', $thread->game_room_code) }}" class="w-full px-4 py-2.5 bg-[#faf8f5] border-2 border-[#121316] rounded-xl text-xs sm:text-sm font-bold text-[#121316] outline-none" placeholder="Contoh: ID 12345678 / Discord Link / Kode Room">
                </div>
            </div>
        </div>

        <!-- Bank File Panel -->
        <div x-show="category === 'sharing'" style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             class="bg-white border-2 border-[#121316] rounded-2xl p-5 sm:p-6 shadow-[3px_3px_0px_#121316] space-y-4">
            <div class="flex items-center gap-3 mb-1">
                <div class="w-10 h-10 rounded-xl border border-slate-900 flex items-center justify-center text-white" style="background-color: #059669 !important; color: #ffffff !important;"><i class="fa-solid fa-folder-open text-xl"></i></div>
                <div>
                    <h4 class="forum-hdr text-sm font-black text-[#121316]">Kategori Dokumen / File</h4>
                    <div class="text-xs font-mono-code text-[#065f46] font-bold uppercase tracking-wider">Bagikan berkas belajar untuk seluruh kawan</div>
                </div>
            </div>
            <div>
                <label class="block text-xs font-mono-code font-black text-[#121316] uppercase tracking-widest mb-1.5">Jenis Berkas</label>
                <select name="file_category" class="w-full px-4 py-2.5 bg-[#faf8f5] border-2 border-[#121316] rounded-xl text-xs sm:text-sm font-bold text-[#121316] outline-none">
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
        <div class="bg-white border-2 border-[#121316] rounded-2xl p-5 sm:p-6 shadow-[4px_4px_0px_#121316] grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Image -->
            <div class="space-y-2.5">
                <label class="block text-xs font-mono-code font-black text-[#121316] uppercase tracking-widest"><i class="fa-solid fa-image text-[#ff3823] mr-1"></i> Gambar Utama</label>
                <input type="file" name="image" accept="image/*" @change="fileChosen" 
                       class="w-full px-3 py-2 bg-[#faf8f5] border-2 border-[#121316] rounded-xl text-xs font-bold text-[#121316] file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-2 file:border-[#121316] file:bg-[#fde047] file:text-[#121316] file:font-black cursor-pointer">
                <p class="text-[10px] font-mono-code text-[#666]">Format JPG/PNG, Maks 5MB</p>
                
                <template x-if="imageUrl">
                    <div class="mt-2.5 relative inline-block rounded-xl overflow-hidden border-2 border-[#121316] shadow-[2px_2px_0px_#121316]">
                        <img :src="imageUrl" class="h-28 w-auto object-cover">
                        <button type="button" @click="imageUrl = ''; $event.target.closest('.space-y-2.5').querySelector('input[type=file]').value = ''" 
                                class="absolute top-1.5 right-1.5 w-6 h-6 bg-white border border-[#121316] text-[#121316] hover:bg-rose-100 rounded-full flex items-center justify-center font-bold">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>
                </template>
            </div>

            <!-- File -->
            <div class="space-y-2.5">
                <label class="block text-xs font-mono-code font-black text-[#121316] uppercase tracking-widest"><i class="fa-solid fa-cloud-arrow-up text-[#2563eb] mr-1"></i> Lampiran File</label>
                <input type="file" name="attachment" 
                       class="w-full px-3 py-2 bg-[#faf8f5] border-2 border-[#121316] rounded-xl text-xs font-bold text-[#121316] file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-2 file:border-[#121316] file:bg-[#faf8f5] file:text-[#121316] file:font-black cursor-pointer">
                <p class="text-[10px] font-mono-code text-[#666]">PDF/ZIP/DOCS, Maks 10MB</p>
                @if($thread->attachment_path)
                    <div class="text-xs font-mono-code font-black text-[#2563eb] mt-1.5"><i class="fa-solid fa-paperclip mr-1"></i> File Saat Ini: {{ $thread->attachment_name }}</div>
                @endif
            </div>
        </div>

        <!-- Submit -->
        <div class="flex gap-3 pt-2">
            <a href="{{ route('forum.show', $thread) }}" class="btn-tactile-white px-6 py-3 rounded-xl font-black text-xs flex items-center justify-center">
                Batal
            </a>
            <button type="submit" class="btn-tactile-yellow flex-1 py-3 rounded-xl font-black text-xs sm:text-sm flex items-center justify-center gap-2">
                <i class="fa-solid fa-floppy-disk text-base"></i> Simpan Perubahan
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