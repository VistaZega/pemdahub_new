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
    .prose p {
        margin-bottom: 0.4rem !important;
        line-height: 1.6 !important;
    }
    .prose table {
        width: 100% !important;
        border-collapse: collapse !important;
        margin: 1rem 0 !important;
        font-size: 0.875rem !important;
    }
    .prose th, .prose td {
        border: 1px solid #cbd5e1 !important;
        padding: 0.5rem 0.75rem !important;
        text-align: left !important;
        vertical-align: top !important;
    }
    .prose th {
        background-color: #f1f5f9 !important;
        font-weight: 800 !important;
        color: #0f172a !important;
    }
    .prose tr:nth-child(even) {
        background-color: #f8fafc;
    }
    .prose ol {
        list-style-type: decimal !important;
        list-style-position: outside !important;
        margin-left: 1.5rem !important;
        padding-left: 0.5rem !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }
    .prose ul {
        list-style-type: disc !important;
        list-style-position: outside !important;
        margin-left: 1.5rem !important;
        padding-left: 0.5rem !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }
    .prose li {
        display: list-item !important;
        margin-bottom: 0.35rem !important;
        padding-left: 0.25rem !important;
    }
    .prose code {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        padding: 0.15rem 0.4rem !important;
        border-radius: 0.375rem !important;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
        font-size: 0.875em !important;
        border: 1px solid #e2e8f0 !important;
    }
</style>
@endpush

@section('content')
<div id="player-wrapper" class="bg-slate-100 min-h-screen flex flex-col transition-all duration-300">
    {{-- ═══════════════════════════════════════════════ --}}
    {{-- PLAYER TOOLBAR / HEADER (100% SOLID UI UX PRO MAX) --}}
    {{-- ═══════════════════════════════════════════════ --}}
    <div class="px-6 py-4 flex items-center justify-between shadow-lg border-b-2 border-black sticky top-0 z-40" style="background-color: #090d16 !important; color: #ffffff !important;">
        <div class="flex items-center gap-4 min-w-0">
            <a href="{{ route('siswa.lms.show', $course->id) }}" class="flex items-center gap-2 px-4 py-2 bg-black hover:bg-amber-400 hover:text-black rounded-xl text-xs font-black text-white transition border-2 border-black">
                <i class="fas fa-arrow-left text-xs"></i> Kembali ke Ruang Belajar
            </a>
            <div class="hidden sm:block h-6 w-px bg-slate-700"></div>
            <div class="min-w-0">
                <span class="text-[10px] font-black uppercase tracking-widest text-amber-400 block truncate">{{ $course->course_name ?? $course->name }}</span>
                <h1 class="text-base sm:text-lg font-black text-white leading-snug truncate tracking-wide">{{ $material->title }}</h1>
            </div>
        </div>

        {{-- Reader Controls --}}
        <div class="flex items-center gap-2">
            {{-- Font Size Toggle --}}
            <div class="hidden md:flex items-center bg-slate-800 rounded-xl p-1 border border-slate-700">
                <button type="button" onclick="changeFontSize('sm')" class="px-2.5 py-1 text-xs font-black text-slate-300 hover:text-white rounded-lg transition">A-</button>
                <button type="button" onclick="changeFontSize('md')" class="px-2.5 py-1 text-xs font-black text-black bg-amber-400 rounded-lg transition">A</button>
                <button type="button" onclick="changeFontSize('lg')" class="px-2.5 py-1 text-xs font-black text-slate-300 hover:text-white rounded-lg transition">A+</button>
            </div>

            {{-- Toggle Focus Mode --}}
            <button type="button" onclick="toggleFocusMode()" class="flex items-center gap-1.5 px-4 py-2 bg-black hover:bg-emerald-600 text-white rounded-xl text-xs font-black transition border-2 border-black shadow-md">
                <i class="fas fa-expand-alt text-amber-400 text-xs"></i> <span class="hidden sm:inline">Focus Mode</span>
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
            <div class="bg-white rounded-3xl p-6 md:p-8 shadow-md border-2 border-black">
                
                {{-- Video Player --}}
                @if($material->material_type === 'video' || $material->isYouTubeVideo())
                <div class="aspect-video w-full rounded-2xl overflow-hidden bg-black shadow-lg mb-6 border-2 border-black">
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
                    @if($material->fileExists())
                    <div class="mb-6">
                        <div class="w-full h-[680px] rounded-2xl overflow-hidden border-2 border-black shadow-md mb-3 bg-slate-100 relative">
                            <iframe src="{{ route('siswa.lms.materials.view', $material->id) }}" class="w-full h-full" frameborder="0"></iframe>
                        </div>
                        <div class="p-4 rounded-2xl border-2 border-black bg-slate-50 flex flex-wrap items-center justify-between gap-3 shadow-xs">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center font-black border border-black shadow-xs">
                                    <i class="fas fa-file-pdf text-lg"></i>
                                </div>
                                <div>
                                    <p class="font-black text-black text-xs">Dokumen Materi PDF</p>
                                    <p class="text-[11px] text-slate-600 font-bold">Jika loading di layar terasa lambat, gunakan opsi di samping</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('siswa.lms.materials.view', $material->id) }}" target="_blank" class="px-4 py-2 bg-black hover:bg-slate-800 text-white rounded-xl text-xs font-black transition border border-black shadow-xs flex items-center gap-1.5">
                                    <i class="fas fa-external-link-alt text-xs"></i> Buka Tab Baru
                                </a>
                                <a href="{{ route('siswa.lms.materials.download', $material->id) }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black transition border border-black shadow-xs flex items-center gap-1.5">
                                    <i class="fas fa-download text-xs"></i> Unduh PDF
                                </a>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="mb-6 p-5 border-2 border-dashed border-amber-300 rounded-2xl bg-amber-50/70 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-black border border-black shadow-xs">
                                <i class="fas fa-file-pdf text-lg"></i>
                            </div>
                            <div>
                                <p class="font-black text-amber-900 text-xs">Dokumen PDF Terlampir</p>
                                <p class="text-[11px] text-amber-700 font-medium">⚠️ Berkas fisik PDF ini belum tersedia di server. Silakan hubungi guru pengampu untuk mengunggah ulang.</p>
                            </div>
                        </div>
                        <span class="px-3 py-1.5 rounded-xl bg-amber-200/80 text-amber-800 text-xs font-bold border border-amber-300">
                            <i class="fas fa-ban mr-1"></i> Belum Tersedia
                        </span>
                    </div>
                    @endif
                @endif

                {{-- Text / Article Body --}}
                <div id="reader-body" class="text-slate-800 leading-relaxed prose max-w-none p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
                    {!! $material->content ? formatLmsContent($material->content) : '<p class="text-slate-500 italic">Materi ini menggunakan lampiran file atau pemutar video di atas.</p>' !!}
                </div>

                {{-- File Download Attachment --}}
                @if($material->file_path && !in_array($material->material_type, ['video', 'pdf']))
                    @php $hasPhysicalFile = $material->fileExists(); @endphp
                    <div class="mt-8 p-5 border-2 border-black rounded-2xl flex items-center justify-between shadow-md" style="background-color: {{ $hasPhysicalFile ? '#ffedd5' : '#fef3c7' }} !important;">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black shadow-md border-2 border-black shrink-0" style="background-color: {{ $hasPhysicalFile ? '#ea580c' : '#d97706' }} !important; color: #ffffff !important;">
                                <i class="fas {{ $hasPhysicalFile ? 'fa-paperclip' : 'fa-exclamation-triangle' }} text-xl text-white"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-black">Dokumen Lampiran Materi</h4>
                                <p class="text-xs {{ $hasPhysicalFile ? 'text-black font-bold' : 'text-amber-900 font-medium' }}">
                                    {{ $hasPhysicalFile ? 'Klik tombol untuk mengunduh berkas pendukung' : '⚠️ Berkas fisik belum tersedia di server. Silakan hubungi guru pengampu.' }}
                                </p>
                            </div>
                        </div>
                        @if($hasPhysicalFile)
                        <a href="{{ route('siswa.lms.materials.download', $material->id) }}" class="px-5 py-3 text-white rounded-2xl text-xs font-black shadow-md transition border-2 border-black" style="background-color: #ea580c !important;">
                            <i class="fas fa-download mr-1.5 text-white"></i> Unduh Berkas
                        </a>
                        @else
                        <span class="px-4 py-2.5 bg-amber-200 text-amber-900 rounded-2xl text-xs font-black border border-amber-300">
                            <i class="fas fa-ban mr-1"></i> Belum Tersedia
                        </span>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Completion & Navigation Footer --}}
            <div class="bg-white rounded-3xl p-6 shadow-md border-2 border-black flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black border-2 border-black shrink-0" style="background-color: #a7f3d0 !important; color: #000000 !important;">
                        <i class="fas fa-check-circle text-2xl text-black"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-black">Verifikasi & Progress Pembelajaran</h4>
                        <p class="text-xs text-black font-bold">Tandai materi ini selesai untuk meningkatkan persentase progress kelas Anda</p>
                    </div>
                </div>

                <form action="{{ route('siswa.lms.materials.track', $material->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-black text-xs uppercase tracking-wider shadow-md transition flex items-center gap-2 border-2 border-black">
                        <i class="fas fa-check text-white"></i> Tandai Selesai & Lanjutkan
                    </button>
                </form>
            </div>
        </div>

        {{-- RIGHT SIDEBAR: CATATAN PRIBADI SISWA & NAVIGATION --}}
        <div class="lg:col-span-4 xl:col-span-3 bg-slate-50 border-l-2 border-black p-6 overflow-y-auto max-h-[calc(100vh-70px)] space-y-6">
            
            {{-- Personal Notes Panel --}}
            <div class="bg-white rounded-3xl p-5 shadow-md border-2 border-black">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-black text-black flex items-center gap-2">
                        <i class="fas fa-sticky-note text-amber-500"></i> Catatan Pribadi Siswa
                    </h3>
                    <span id="note-status" class="text-[10px] font-black text-emerald-700 opacity-0 transition-opacity bg-emerald-200 px-2 py-0.5 rounded border border-black">Tersimpan ✅</span>
                </div>
                <p class="text-xs text-black font-bold mb-3">Tuliskan ringkasan materi penting. Catatan ini tersimpan khusus untuk Anda.</p>
                
                <textarea id="student-notes" rows="6" placeholder="Ketik catatan di sini..." class="w-full border-2 border-black p-3.5 rounded-2xl text-xs focus:ring-4 focus:ring-black/20 outline-none text-black font-black mb-3 bg-white shadow-inner">{{ $note->notes ?? '' }}</textarea>
                
                <button type="button" onclick="saveStudentNote()" class="w-full py-3 bg-black hover:bg-amber-400 hover:text-black text-white rounded-2xl font-black text-xs uppercase tracking-wider shadow-md transition border-2 border-black">
                    <i class="fas fa-save mr-1.5 text-amber-400"></i> Simpan Catatan
                </button>
            </div>

            {{-- Course Outline Navigation --}}
            <div class="bg-white rounded-3xl p-5 shadow-md border-2 border-black">
                <h3 class="text-xs font-black text-black uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-list-ol text-black text-sm"></i> Daftar Modul Ajar
                </h3>

                <div class="space-y-4">
                    @foreach($course->modules as $mod)
                    <div>
                        <h4 class="text-xs font-black uppercase tracking-wider text-black mb-2 border-b-2 border-black pb-1.5 flex items-center justify-between">
                            <span>{{ $mod->title }}</span>
                            @if($mod->is_sequential)
                            <span class="text-[9px] font-black bg-amber-300 text-black px-2 py-0.5 rounded border border-black">Berurutan</span>
                            @endif
                        </h4>
                        <div class="space-y-1.5 pl-1">
                            @foreach($mod->materials as $mat)
                            @php
                                $isCompleted = in_array($mat->id, $completedMaterialIds ?? []);
                                $isCurrent = $mat->id === $material->id;
                            @endphp
                            <a href="{{ route('siswa.lms.materials.player', $mat->id) }}" class="flex items-center justify-between p-2.5 rounded-xl text-xs transition font-black border border-black {{ $isCurrent ? 'bg-black text-white shadow-md' : ($isCompleted ? 'bg-emerald-200 text-black hover:bg-emerald-300' : 'bg-slate-100 text-black hover:bg-amber-300') }}">
                                <span class="truncate max-w-[160px]">{{ $mat->title }}</span>
                                @if($isCompleted)
                                <i class="fas fa-check-circle text-black text-sm"></i>
                                @elseif($isCurrent)
                                <i class="fas fa-play-circle text-amber-400 text-sm"></i>
                                @else
                                <i class="far fa-circle text-black text-xs"></i>
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
