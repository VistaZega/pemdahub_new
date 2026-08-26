@extends('layouts.siswa')

@section('title', $material->title . ' - Focus Reader Player')

@push('styles')
<style>
    .focus-mode-active #sidebar-main,
    .focus-mode-active #header-main { display: none !important; }
    .focus-mode-active #player-wrapper { position: fixed; inset: 0; z-index: 99999; border-radius: 0; }
    .font-size-sm { font-size: 0.9rem; line-height: 1.6; }
    .font-size-md { font-size: 1.1rem; line-height: 1.7; }
    .font-size-lg { font-size: 1.3rem; line-height: 1.8; }
</style>
@endpush

@section('content')
<div id="player-wrapper" class="bg-slate-100/70 min-h-screen flex flex-col transition-all duration-300">
    {{-- ═══════════════════════════════════════════════ --}}
    {{-- PLAYER TOOLBAR / HEADER (SESUAI DASHBOARD SISWA) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="px-6 py-4 flex items-center justify-between shadow-md bg-gradient-to-r from-blue-700 via-indigo-700 to-purple-800 text-white sticky top-0 z-40 border-b border-indigo-900/20">
        <div class="flex items-center gap-4 min-w-0">
            <a href="{{ route('siswa.lms.show', $course->id) }}" class="flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 text-white backdrop-blur-md rounded-xl text-xs font-bold transition border border-white/30 shadow-xs">
                <i class="fas fa-arrow-left text-xs"></i> <span class="hidden sm:inline">Kembali ke Ruang Belajar</span>
            </a>
            <div class="hidden sm:block h-6 w-px bg-white/30"></div>
            <div class="min-w-0">
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-amber-300 block truncate">{{ $course->course_name ?? $course->name }}</span>
                <h1 class="text-base sm:text-lg font-extrabold text-white leading-snug truncate tracking-wide drop-shadow-xs">{{ $material->title }}</h1>
            </div>
        </div>

        {{-- Reader Controls --}}
        <div class="flex items-center gap-2">
            {{-- Font Size Toggle --}}
            <div class="hidden md:flex items-center bg-white/20 backdrop-blur-md rounded-xl p-1 border border-white/30">
                <button type="button" onclick="changeFontSize('sm')" class="px-2.5 py-1 text-xs font-bold text-white/80 hover:text-white rounded-lg transition">A-</button>
                <button type="button" onclick="changeFontSize('md')" class="px-2.5 py-1 text-xs font-bold text-slate-900 bg-amber-400 rounded-lg transition shadow-xs">A</button>
                <button type="button" onclick="changeFontSize('lg')" class="px-2.5 py-1 text-xs font-bold text-white/80 hover:text-white rounded-lg transition">A+</button>
            </div>

            {{-- Toggle Focus Mode --}}
            <button type="button" onclick="toggleFocusMode()" class="flex items-center gap-1.5 px-4 py-2 bg-white/20 hover:bg-white/30 text-white backdrop-blur-md rounded-xl text-xs font-bold transition border border-white/30 shadow-xs">
                <i class="fas fa-expand-alt text-amber-300 text-xs"></i> <span class="hidden sm:inline">Focus Mode</span>
            </button>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- PLAYER MAIN CONTENT AREA --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-0 overflow-hidden">
        
        {{-- LEFT / MAIN READER --}}
        <div class="lg:col-span-8 xl:col-span-9 p-6 md:p-8 overflow-y-auto max-h-[calc(100vh-70px)] space-y-6">
            
            {{-- Material Content Player Card --}}
            <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-slate-200/80">
                
                {{-- Video Player --}}
                @if($material->material_type === 'video' || $material->isYouTubeVideo())
                <div class="aspect-video w-full rounded-2xl overflow-hidden bg-slate-950 shadow-md mb-6 border border-slate-200">
                    @if($material->isYouTubeVideo())
                    <iframe class="w-full h-full" src="{{ $material->getVideoEmbedUrl() }}" title="{{ $material->title }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    @elseif($material->isDirectVideo())
                    <video class="w-full h-full" controls controlsList="nodownload">
                        <source src="{{ asset('storage/' . $material->file_path) }}" type="video/mp4">
                        Browser Anda tidak mendukung pemutar video ini.
                    </video>
                    @else
                    <div class="w-full h-full flex items-center justify-center text-white">
                        <i class="fas fa-video-slash text-4xl text-white"></i>
                    </div>
                    @endif
                </div>
                @endif

                {{-- PDF Viewer --}}
                @if($material->material_type === 'pdf' && $material->file_path)
                <div class="w-full h-[650px] rounded-2xl overflow-hidden border border-slate-200 shadow-sm mb-6 bg-white">
                    <iframe src="{{ asset('storage/' . $material->file_path) }}" class="w-full h-full"></iframe>
                </div>
                @endif

                {{-- Text / Article Body --}}
                <div id="reader-body" class="text-slate-800 leading-relaxed space-y-4 prose max-w-none p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                    {!! $material->content ? formatLmsContent($material->content) : '<p class="text-slate-500 italic">Materi ini menggunakan lampiran file atau pemutar video di atas.</p>' !!}
                </div>

                {{-- File Download Attachment --}}
                @if($material->file_path && !in_array($material->material_type, ['video', 'pdf']))
                <div class="mt-8 p-5 border border-amber-200 rounded-2xl flex items-center justify-between shadow-sm bg-gradient-to-r from-amber-50 to-orange-50">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-bold shadow-md bg-gradient-to-br from-amber-500 to-orange-600 text-white shrink-0">
                            <i class="fas fa-paperclip text-xl"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Dokumen Lampiran Materi</h4>
                            <p class="text-xs text-slate-600 font-medium">Klik tombol untuk mengunduh berkas pendukung materi ini</p>
                        </div>
                    </div>
                    <a href="{{ route('siswa.lms.materials.download', $material->id) }}" class="px-5 py-3 text-white rounded-2xl text-xs font-bold shadow-md transition bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700">
                        <i class="fas fa-download mr-1.5"></i> Unduh Berkas
                    </a>
                </div>
                @endif
            </div>

            {{-- Completion & Navigation Footer --}}
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-bold shrink-0 bg-emerald-100 text-emerald-600">
                        <i class="fas fa-check-circle text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Verifikasi & Progress Pembelajaran</h4>
                        <p class="text-xs text-slate-600 font-medium">Tandai materi ini selesai untuk meningkatkan persentase progress kelas Anda</p>
                    </div>
                </div>

                <form action="{{ route('siswa.lms.materials.track', $material->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="px-6 py-3.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-2xl font-bold text-xs uppercase tracking-wider shadow-md shadow-emerald-200 transition flex items-center gap-2">
                        <i class="fas fa-check"></i> Tandai Selesai & Lanjutkan
                    </button>
                </form>
            </div>
        </div>

        {{-- RIGHT SIDEBAR: CATATAN PRIBADI SISWA & NAVIGATION --}}
        <div class="lg:col-span-4 xl:col-span-3 bg-slate-50/50 border-l border-slate-200/80 p-6 overflow-y-auto max-h-[calc(100vh-70px)] space-y-6">
            
            {{-- Personal Notes Panel --}}
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-200/80">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-sticky-note text-amber-500"></i> Catatan Pribadi Siswa
                    </h3>
                    <span id="note-status" class="text-[10px] font-bold text-emerald-700 opacity-0 transition-opacity bg-emerald-100 px-2 py-0.5 rounded-lg border border-emerald-200">Tersimpan ✅</span>
                </div>
                <p class="text-xs text-slate-500 font-medium mb-3">Tuliskan ringkasan materi penting. Catatan ini tersimpan khusus untuk Anda.</p>
                
                <textarea id="student-notes" rows="6" placeholder="Ketik catatan di sini..." class="w-full border border-slate-200 p-3.5 rounded-2xl text-xs focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none text-slate-800 font-medium mb-3 bg-slate-50/40 shadow-inner">{{ $note->notes ?? '' }}</textarea>
                
                <button type="button" onclick="saveStudentNote()" class="w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-2xl font-bold text-xs uppercase tracking-wider shadow-md shadow-indigo-100 transition">
                    <i class="fas fa-save mr-1.5 text-amber-300"></i> Simpan Catatan
                </button>
            </div>

            {{-- Course Outline Navigation --}}
            <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-200/80">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-list-ol text-indigo-600 text-sm"></i> Daftar Modul Ajar
                </h3>

                <div class="space-y-4">
                    @foreach($course->modules as $mod)
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 mb-2 border-b border-slate-100 pb-1.5 flex items-center justify-between">
                            <span>{{ $mod->title }}</span>
                            @if($mod->is_sequential)
                            <span class="text-[9px] font-extrabold bg-amber-100 text-amber-800 px-2 py-0.5 rounded-lg border border-amber-200">Berurutan</span>
                            @endif
                        </h4>
                        <div class="space-y-1.5 pl-1">
                            @foreach($mod->materials as $mat)
                            @php
                                $isCompleted = in_array($mat->id, $completedMaterialIds ?? []);
                                $isCurrent = $mat->id === $material->id;
                            @endphp
                            <a href="{{ route('siswa.lms.materials.player', $mat->id) }}" class="flex items-center justify-between p-2.5 rounded-xl text-xs transition font-semibold {{ $isCurrent ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-sm' : ($isCompleted ? 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-100' : 'bg-slate-50 text-slate-700 hover:bg-indigo-50 hover:text-indigo-700') }}">
                                <span class="truncate max-w-[160px]">{{ $mat->title }}</span>
                                @if($isCompleted)
                                <i class="fas fa-check-circle text-emerald-600 text-sm"></i>
                                @elseif($isCurrent)
                                <i class="fas fa-play-circle text-amber-300 text-sm"></i>
                                @else
                                <i class="far fa-circle text-slate-400 text-xs"></i>
                                @endif
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function changeFontSize(size) {
        const body = document.getElementById('reader-body');
        body.classList.remove('font-size-sm', 'font-size-md', 'font-size-lg');
        body.classList.add('font-size-' + size);
    }

    function toggleFocusMode() {
        document.body.classList.toggle('focus-mode-active');
    }

    function saveStudentNote() {
        const notesText = document.getElementById('student-notes').value;
        const statusEl = document.getElementById('note-status');

        fetch("{{ route('siswa.lms.materials.notes', $material->id) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ notes: notesText })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                statusEl.classList.remove('opacity-0');
                setTimeout(() => { statusEl.classList.add('opacity-0'); }, 2500);
            }
        })
        .catch(err => console.error(err));
    }
</script>
@endpush
