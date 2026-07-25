@extends('layouts.siswa')

@section('title', $material->title . ' - Focus Reader Player')

@push('styles')
<style>
    .focus-mode-active #sidebar-main,
    .focus-mode-active #header-main { display: none !important; }
    .focus-mode-active #player-wrapper { position: fixed; inset: 0; z-index: 99999; border-radius: 0; }
    .font-size-sm { font-size: 0.875rem; line-height: 1.6; }
    .font-size-md { font-size: 1.05rem; line-height: 1.7; }
    .font-size-lg { font-size: 1.25rem; line-height: 1.8; }
</style>
@endpush

@section('content')
<div id="player-wrapper" class="bg-gray-100 min-h-screen flex flex-col transition-all duration-300">
    {{-- ═══════════════════════════════════════════════ --}}
    {{-- PLAYER TOOLBAR / HEADER --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="bg-slate-900 text-white px-6 py-4 flex items-center justify-between shadow-lg border-b border-slate-800 sticky top-0 z-40">
        <div class="flex items-center gap-4 min-w-0">
            <a href="{{ route('siswa.lms.show', $course->id) }}" class="flex items-center gap-2 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-xl text-xs font-bold text-slate-200 transition">
                <i class="fas fa-arrow-left"></i> Kembali ke Kelas
            </a>
            <div class="hidden sm:block h-6 w-px bg-slate-700"></div>
            <div class="min-w-0">
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-cyan-400 block truncate">{{ $course->course_name ?? $course->name }}</span>
                <h1 class="text-base font-bold text-white leading-snug truncate">{{ $material->title }}</h1>
            </div>
        </div>

        {{-- Reader Controls --}}
        <div class="flex items-center gap-2">
            {{-- Font Size Toggle --}}
            <div class="hidden md:flex items-center bg-slate-800 rounded-xl p-1 border border-slate-700">
                <button type="button" onclick="changeFontSize('sm')" class="px-2.5 py-1 text-xs font-bold text-slate-300 hover:text-white rounded-lg transition">A-</button>
                <button type="button" onclick="changeFontSize('md')" class="px-2.5 py-1 text-xs font-bold text-cyan-400 bg-slate-700 rounded-lg transition">A</button>
                <button type="button" onclick="changeFontSize('lg')" class="px-2.5 py-1 text-xs font-bold text-slate-300 hover:text-white rounded-lg transition">A+</button>
            </div>

            {{-- Toggle Focus Mode --}}
            <button type="button" onclick="toggleFocusMode()" class="flex items-center gap-1.5 px-3 py-1.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl text-xs font-bold transition shadow-sm">
                <i class="fas fa-expand-alt"></i> <span class="hidden sm:inline">Focus Mode</span>
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
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-md border border-gray-200">
                
                {{-- Video Player --}}
                @if($material->material_type === 'video' || $material->isYouTubeVideo())
                <div class="aspect-video w-full rounded-2xl overflow-hidden bg-black shadow-inner mb-6">
                    @if($material->isYouTubeVideo())
                    <iframe class="w-full h-full" src="{{ $material->getVideoEmbedUrl() }}" title="{{ $material->title }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    @elseif($material->isDirectVideo())
                    <video class="w-full h-full" controls controlsList="nodownload">
                        <source src="{{ asset('storage/' . $material->file_path) }}" type="video/mp4">
                        Browser Anda tidak mendukung pemutar video ini.
                    </video>
                    @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <i class="fas fa-video-slash text-4xl"></i>
                    </div>
                    @endif
                </div>
                @endif

                {{-- PDF Viewer --}}
                @if($material->material_type === 'pdf' && $material->file_path)
                <div class="w-full h-[600px] rounded-2xl overflow-hidden border border-gray-300 shadow-inner mb-6">
                    <iframe src="{{ asset('storage/' . $material->file_path) }}" class="w-full h-full"></iframe>
                </div>
                @endif

                {{-- Text / Article Body --}}
                <div id="reader-body" class="font-size-md text-slate-900 font-normal leading-relaxed space-y-4 prose max-w-none">
                    {!! $material->content ?: '<p class="text-gray-500 italic">Materi ini menggunakan lampiran file atau video di atas.</p>' !!}
                </div>

                {{-- File Download Attachment --}}
                @if($material->file_path && !in_array($material->material_type, ['video', 'pdf']))
                <div class="mt-8 p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold">
                            <i class="fas fa-paperclip"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Lampiran Materi</h4>
                            <p class="text-xs text-gray-500">Klik untuk mengunduh berkas pendukung</p>
                        </div>
                    </div>
                    <a href="{{ route('siswa.lms.materials.download', $material->id) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                        <i class="fas fa-download mr-1"></i> Unduh Berkas
                    </a>
                </div>
                @endif
            </div>

            {{-- Completion & Navigation Footer --}}
            <div class="bg-white rounded-2xl p-6 shadow-md border border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900">Progres Pembelajaran</h4>
                        <p class="text-xs text-gray-500">Tandai selesai untuk membuka materi berikutnya</p>
                    </div>
                </div>

                <form action="{{ route('siswa.lms.materials.track', $material->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold text-sm shadow-md transition flex items-center gap-2">
                        <i class="fas fa-check"></i> Tandai Selesai & Lanjut
                    </button>
                </form>
            </div>
        </div>

        {{-- RIGHT SIDEBAR: CATATAN PRIBADI SISWA & NAVIGATION --}}
        <div class="lg:col-span-4 xl:col-span-3 bg-slate-50 border-l border-gray-200 p-6 overflow-y-auto max-h-[calc(100vh-70px)] space-y-6">
            
            {{-- Personal Notes Panel --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-sticky-note text-amber-500"></i> Catatan Pribadi
                    </h3>
                    <span id="note-status" class="text-[10px] font-bold text-emerald-600 opacity-0 transition-opacity">Tersimpan ✅</span>
                </div>
                <p class="text-xs text-gray-500 mb-3">Tulis poin penting materi ini. Catatan ini hanya terlihat oleh Anda.</p>
                
                <textarea id="student-notes" rows="6" placeholder="Ketik catatan di sini..." class="w-full border-2 border-gray-200 p-3 rounded-xl text-xs focus:ring-2 focus:ring-amber-500 focus:border-transparent transition text-gray-900 font-medium mb-3">{{ $note->notes ?? '' }}</textarea>
                
                <button type="button" onclick="saveStudentNote()" class="w-full py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-xs shadow-sm transition">
                    <i class="fas fa-save mr-1"></i> Simpan Catatan
                </button>
            </div>

            {{-- Course Outline Navigation --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-200">
                <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-list-ol text-blue-600"></i> Daftar Materi Kursus
                </h3>

                <div class="space-y-4">
                    @foreach($course->modules as $mod)
                    <div>
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-2 border-b border-slate-100 pb-1 flex items-center justify-between">
                            <span>{{ $mod->title }}</span>
                            @if($mod->is_sequential)
                            <span class="text-[9px] font-bold bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded">Berurutan</span>
                            @endif
                        </h4>
                        <div class="space-y-1.5 pl-2">
                            @foreach($mod->materials as $mat)
                            @php
                                $isCompleted = in_array($mat->id, $completedMaterialIds ?? []);
                                $isCurrent = $mat->id === $material->id;
                            @endphp
                            <a href="{{ route('siswa.lms.materials.player', $mat->id) }}" class="flex items-center justify-between p-2 rounded-xl text-xs transition font-semibold {{ $isCurrent ? 'bg-blue-600 text-white shadow-sm' : ($isCompleted ? 'bg-emerald-50 text-emerald-900 hover:bg-emerald-100' : 'bg-gray-50 text-gray-700 hover:bg-gray-100') }}">
                                <span class="truncate max-w-[160px]">{{ $mat->title }}</span>
                                @if($isCompleted)
                                <i class="fas fa-check-circle text-emerald-600 {{ $isCurrent ? 'text-white' : '' }}"></i>
                                @elseif($isCurrent)
                                <i class="fas fa-play-circle text-white"></i>
                                @else
                                <i class="far fa-circle text-gray-400"></i>
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
