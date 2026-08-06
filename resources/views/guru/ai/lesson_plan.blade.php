@extends('layouts.guru')

@section('title', 'AI RPP Generator — Kurikulum Merdeka')

@push('styles')
<style>
    /* Glowing magic icon animation */
    @keyframes pulse-glow {
        0%, 100% { transform: scale(1); filter: drop-shadow(0 0 5px rgba(16, 185, 129, 0.4)); }
        50% { transform: scale(1.08); filter: drop-shadow(0 0 20px rgba(20, 184, 166, 0.8)); }
    }
    .magic-glow {
        animation: pulse-glow 3s infinite ease-in-out;
    }
    /* Simple markdown preview styles */
    .rpp-preview-content h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #064e3b;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
        border-bottom: 2px solid #a7f3d0;
        padding-bottom: 0.25rem;
    }
    .rpp-preview-content h2 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #047857;
        margin-top: 1.25rem;
        margin-bottom: 0.5rem;
    }
    .rpp-preview-content h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #059669;
        margin-top: 1rem;
        margin-bottom: 0.35rem;
    }
    .rpp-preview-content ul {
        list-style-type: disc;
        padding-left: 1.25rem;
        margin-bottom: 1rem;
    }
    .rpp-preview-content li {
        margin-bottom: 0.25rem;
    }
    .rpp-preview-content p {
        margin-bottom: 0.75rem;
        line-height: 1.6;
        color: #374151;
    }
    .rpp-preview-content hr {
        margin: 1.5rem 0;
        border-color: #e5e7eb;
    }
    .rpp-preview-content strong {
        color: #111827;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Header Banner (Neo-Brutalism) --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl p-6 border-2 border-black mb-6" style="background: linear-gradient(135deg, #090d16 0%, #065f46 50%, #0f766e 100%) !important;">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-amber-300 font-bold mb-2">
                    <a href="{{ route('guru.dashboard') }}" class="hover:underline">Dashboard</a>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                    <span>Asisten AI</span>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                    <span class="text-white font-black">RPP Generator</span>
                </div>
                <h1 class="text-xl md:text-2xl font-black text-white flex items-center gap-3" style="color: #ffffff !important;">
                    <div class="w-10 h-10 rounded-xl bg-amber-400 border-2 border-black flex items-center justify-center text-black shadow-sm text-lg">
                        <i class="fas fa-magic text-black"></i>
                    </div>
                    AI RPP & Modul Ajar Generator
                </h1>
                <p class="text-xs md:text-sm font-bold text-teal-200 mt-1" style="color: #99f6e4 !important;">
                    Buat Perencanaan Pembelajaran (Kurikulum Merdeka / 2013) secara instan menggunakan kecerdasan buatan.
                </p>
            </div>
        </div>
    </div>
            <p class="text-gray-500 text-sm mt-1">Susun Rencana Pelaksanaan Pembelajaran Kurikulum Merdeka secara instan menggunakan AI Gemini.</p>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Panel: Input Form -->
        <div class="lg:col-span-5 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-sliders-h text-emerald-600"></i>
                Parameter Pembelajaran
            </h2>
            
            <form id="rpp-form" class="space-y-4">
                @csrf
                <!-- School Type & Grade Level -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Jenjang Sekolah</label>
                        <select name="school_type" id="school_type" class="w-full rounded-xl border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition" required>
                            <option value="SMP">SMP</option>
                            <option value="SMA">SMA</option>
                            <option value="SMK">SMK</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Kelas/Fase</label>
                        <select name="grade_level" id="grade_level" class="w-full rounded-xl border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition" required>
                            <!-- Options populated dynamically or standard -->
                            <option value="Kelas VII (Fase D)">Kelas VII (Fase D)</option>
                            <option value="Kelas VIII (Fase D)">Kelas VIII (Fase D)</option>
                            <option value="Kelas IX (Fase D)">Kelas IX (Fase D)</option>
                            <option value="Kelas X (Fase E)">Kelas X (Fase E)</option>
                            <option value="Kelas XI (Fase F)">Kelas XI (Fase F)</option>
                            <option value="Kelas XII (Fase F)">Kelas XII (Fase F)</option>
                        </select>
                    </div>
                </div>

                <!-- Subject (Mata Pelajaran) -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Mata Pelajaran</label>
                    <input type="text" name="subject" id="subject" placeholder="Contoh: Informatika, Bahasa Indonesia" class="w-full rounded-xl border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition" required>
                </div>

                <!-- Topic (Tema/Materi) -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Tema / Materi Pokok</label>
                    <input type="text" name="topic" id="topic" placeholder="Contoh: Algoritma Pencarian, Puisi Rakyat" class="w-full rounded-xl border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition" required>
                </div>

                <!-- Duration (Alokasi Waktu) -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Alokasi Waktu</label>
                    <input type="text" name="duration" id="duration" placeholder="Contoh: 2 x 45 Menit, 1 Pertemuan" class="w-full rounded-xl border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition" required>
                </div>

                <!-- Objectives (Capaian Pembelajaran) -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Capaian / Tujuan Pembelajaran</label>
                    <textarea name="objectives" id="objectives" rows="4" placeholder="Tuliskan target kompetensi atau capaian pembelajaran yang ingin dicapai siswa..." class="w-full rounded-xl border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition" required></textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="btn-generate" class="w-full bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-medium py-3 px-4 rounded-xl shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 flex items-center justify-center gap-2 transition-all">
                    <i class="fas fa-sparkles"></i>
                    <span>Rumuskan RPP dengan AI</span>
                </button>
            </form>
        </div>

        <!-- Right Panel: Live Preview -->
        <div class="lg:col-span-7 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col min-h-[550px]">
            <!-- Panel Header -->
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-4">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-eye text-teal-650"></i>
                    Pratinjau Modul Ajar
                </h2>
                <!-- Download Button (Hidden by default) -->
                <form id="download-form" action="{{ route('guru.ai.lesson-plan.download') }}" method="POST" class="hidden">
                    @csrf
                    <input type="hidden" name="subject" id="dl-subject">
                    <input type="hidden" name="topic" id="dl-topic">
                    <input type="hidden" name="markdown_content" id="dl-content">
                    <button type="submit" class="bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white text-xs font-semibold py-2 px-4 rounded-xl shadow-sm flex items-center gap-2 transition-all">
                        <i class="fas fa-file-word"></i>
                        Ekspor ke MS Word (.doc)
                    </button>
                </form>
            </div>

            <!-- Panel Content -->
            <div class="flex-1 flex flex-col justify-center items-center" id="preview-area">
                <!-- Empty State -->
                <div id="empty-state" class="text-center py-12 max-w-sm">
                    <div class="w-20 h-20 rounded-3xl bg-emerald-50 flex items-center justify-center text-emerald-500 text-3xl mx-auto mb-6 magic-glow">
                        <i class="fas fa-wand-magic-sparkles"></i>
                    </div>
                    <h3 class="text-gray-800 font-bold text-base">Siap Menghasilkan RPP</h3>
                    <p class="text-gray-400 text-sm mt-2">Isi formulir di sebelah kiri dan tekan tombol **Rumuskan RPP dengan AI** untuk mulai mendesain Rencana Pembelajaran.</p>
                </div>

                <!-- Loading State -->
                <div id="loading-state" class="hidden text-center py-12">
                    <div class="relative w-20 h-20 mx-auto mb-6">
                        <div class="absolute inset-0 rounded-full border-4 border-emerald-100"></div>
                        <div class="absolute inset-0 rounded-full border-4 border-emerald-500 border-t-transparent animate-spin"></div>
                        <div class="absolute inset-0 flex items-center justify-center text-emerald-500 text-xl magic-glow">
                            <i class="fas fa-robot"></i>
                        </div>
                    </div>
                    <h3 class="text-gray-800 font-bold text-base">Sedang Merumuskan RPP...</h3>
                    <p class="text-gray-400 text-sm mt-2 max-w-xs mx-auto">Gemini AI sedang membaca parameter Anda dan mendesain kegiatan belajar berstandar Kurikulum Merdeka.</p>
                </div>

                <!-- Output Preview Container -->
                <div id="output-container" class="hidden w-full text-left">
                    <div class="bg-gray-50 border border-gray-200/60 rounded-2xl p-6 max-h-[500px] overflow-y-auto shadow-inner text-sm rpp-preview-content" id="output-markdown">
                        <!-- Rendered Markdown HTML goes here -->
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<!-- Include marked.js for client-side markdown parsing -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rppForm = document.getElementById('rpp-form');
        const btnGenerate = document.getElementById('btn-generate');
        const emptyState = document.getElementById('empty-state');
        const loadingState = document.getElementById('loading-state');
        const outputContainer = document.getElementById('output-container');
        const outputMarkdown = document.getElementById('output-markdown');
        
        const downloadForm = document.getElementById('download-form');
        const dlSubject = document.getElementById('dl-subject');
        const dlTopic = document.getElementById('dl-topic');
        const dlContent = document.getElementById('dl-content');

        rppForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Validate fields
            const subject = document.getElementById('subject').value.trim();
            const topic = document.getElementById('topic').value.trim();
            const objectives = document.getElementById('objectives').value.trim();

            if (!subject || !topic || !objectives) return;

            // Toggle States to Loading
            emptyState.classList.add('hidden');
            outputContainer.classList.add('hidden');
            downloadForm.classList.add('hidden');
            loadingState.classList.remove('hidden');

            btnGenerate.disabled = true;
            btnGenerate.innerHTML = `<i class="fas fa-circle-notch animate-spin mr-2"></i> Merumuskan...`;

            // Prepare Data
            const formData = new FormData(rppForm);

            try {
                const response = await fetch("{{ route('guru.ai.lesson-plan.generate') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success && data.markdown) {
                    // Render Markdown to HTML
                    const html = marked.parse(data.markdown);
                    outputMarkdown.innerHTML = html;

                    // Configure Word download form
                    dlSubject.value = subject;
                    dlTopic.value = topic;
                    dlContent.value = data.markdown;

                    // Show Results
                    loadingState.classList.add('hidden');
                    outputContainer.classList.remove('hidden');
                    downloadForm.classList.remove('hidden');
                } else {
                    alert('Gagal membuat RPP: ' + (data.message || 'Terjadi kesalahan tidak diketahui.'));
                    loadingState.classList.add('hidden');
                    emptyState.classList.remove('hidden');
                }
            } catch (err) {
                console.error(err);
                alert('Gagal terhubung dengan server PembdaHub.');
                loadingState.classList.add('hidden');
                emptyState.classList.remove('hidden');
            } finally {
                btnGenerate.disabled = false;
                btnGenerate.innerHTML = `<i class="fas fa-sparkles mr-2"></i> Rumuskan RPP dengan AI`;
            }
        });
    });
</script>
@endpush
