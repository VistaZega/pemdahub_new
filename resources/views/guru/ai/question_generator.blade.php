@extends('layouts.guru')

@section('title', 'AI CBT Question Generator')

@push('styles')
<style>
    @keyframes pulse-glow {
        0%, 100% { transform: scale(1); filter: drop-shadow(0 0 5px rgba(16, 185, 129, 0.4)); }
        50% { transform: scale(1.05); filter: drop-shadow(0 0 15px rgba(20, 184, 166, 0.6)); }
    }
    .magic-glow {
        animation: pulse-glow 3s infinite ease-in-out;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Header Banner (Neo-Brutalism) --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl p-6 border-2 border-black mb-6" style="background: linear-gradient(135deg, #090d16 0%, #065f46 50%, #0f766e 100%) !important; color: #ffffff !important;">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-amber-300 font-bold mb-2">
                    <a href="{{ route('guru.dashboard') }}" class="hover:underline">Dashboard</a>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                    <span>Asisten AI</span>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                    <span class="text-white font-black">Question Generator</span>
                </div>
                <h1 class="text-xl md:text-2xl font-black text-white flex items-center gap-3" style="color: #ffffff !important;">
                    <div class="w-10 h-10 rounded-xl bg-amber-400 border-2 border-black flex items-center justify-center text-black shadow-sm text-lg">
                        <i class="fas fa-brain text-black"></i>
                    </div>
                    AI CBT Question Generator
                </h1>
                <p class="text-xs md:text-sm font-bold text-teal-200 mt-1" style="color: #99f6e4 !important;">
                    Generate kumpulan soal pilihan ganda secara otomatis dari file PDF atau teks materi ajar.
                </p>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="space-y-6">
        
        <!-- Parameter Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-cog text-emerald-600"></i>
                Pengaturan Bank Soal & Sumber Materi
            </h2>

            <form id="generator-form" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Target Bank Soal -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Pilih Bank Soal</label>
                        <select name="question_bank_id" id="question_bank_id" class="w-full rounded-xl border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition" required>
                            <option value="">-- Pilih Bank Soal --</option>
                            @foreach($questionBanks as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->bank_name }} ({{ $bank->subject->subject_name ?? 'Mapel' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Jumlah Soal -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Jumlah Soal</label>
                        <select name="num_questions" id="num_questions" class="w-full rounded-xl border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition" required>
                            <option value="5">5 Soal</option>
                            <option value="10" selected>10 Soal</option>
                            <option value="15">15 Soal</option>
                            <option value="20">20 Soal</option>
                        </select>
                    </div>

                    <!-- Tingkat Kesulitan -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Tingkat Kesulitan</label>
                        <select name="difficulty" id="difficulty" class="w-full rounded-xl border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition" required>
                            <option value="Easy">Easy (Mudah)</option>
                            <option value="Medium" selected>Medium (Sedang)</option>
                            <option value="Hard">Hard (Sulit)</option>
                        </select>
                    </div>
                </div>

                <!-- Input Type Toggle Tab -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Metode Input Materi</label>
                    <div class="flex gap-2 p-1 bg-gray-50 border border-gray-200 rounded-xl w-fit">
                        <button type="button" id="tab-pdf" class="px-4 py-2 text-xs font-semibold rounded-lg bg-white text-emerald-700 shadow-sm border border-gray-100 transition focus:outline-none">
                            <i class="fas fa-file-pdf mr-1.5 text-rose-500"></i>Unggah File PDF
                        </button>
                        <button type="button" id="tab-text" class="px-4 py-2 text-xs font-semibold rounded-lg text-gray-600 hover:text-gray-800 transition focus:outline-none">
                            <i class="fas fa-align-left mr-1.5 text-blue-500"></i>Salin Teks Materi
                        </button>
                    </div>
                    <input type="hidden" name="content_type" id="content_type" value="pdf">
                </div>

                <!-- Dynamic Input Fields -->
                <div class="border border-gray-150 rounded-2xl p-5 bg-gray-50/50">
                    <!-- PDF Input Area -->
                    <div id="pdf-input-area" class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">Berkas PDF</label>
                        <div class="flex items-center justify-center w-full">
                            <label class="flex flex-col items-center justify-center w-full h-36 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-white hover:bg-gray-50/80 transition">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-2"></i>
                                    <p class="mb-1 text-sm text-gray-500 font-medium"><span class="text-emerald-600 font-semibold">Klik untuk unggah</span> atau drag-and-drop</p>
                                    <p class="text-xs text-gray-400">PDF Materi Pelajaran (Maksimal 5MB)</p>
                                </div>
                                <input id="pdf_file" name="pdf_file" type="file" class="hidden" accept=".pdf" />
                            </label>
                        </div>
                        <div id="pdf-file-name" class="hidden text-xs text-emerald-700 font-semibold bg-emerald-50 py-1.5 px-3 rounded-lg w-fit border border-emerald-200">
                            <!-- File Name Show here -->
                        </div>
                    </div>

                    <!-- Text Input Area -->
                    <div id="text-input-area" class="hidden space-y-2">
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">Salin Teks Ringkasan Materi</label>
                        <textarea name="raw_text" id="raw_text" rows="5" placeholder="Tempelkan teks rangkuman materi di sini (Min. 100 kata)..." class="w-full rounded-xl border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition bg-white"></textarea>
                    </div>
                </div>

                <!-- Action Button -->
                <button type="submit" id="btn-generate" class="bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-medium py-3 px-6 rounded-xl shadow-md hover:shadow-lg focus:outline-none transition-all flex items-center justify-center gap-2 w-full md:w-fit">
                    <i class="fas fa-sparkles"></i>
                    <span>Analisis & Rumuskan Soal Ujian</span>
                </button>
            </form>
        </div>

        <!-- Review Grid Container -->
        <div id="review-container" class="hidden space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-list-check text-emerald-600"></i>
                        Verifikasi & Edit Draf Soal Ujian
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Tinjau, sesuaikan pilihan jawaban, hapus soal yang tidak relevan, sebelum disimpan permanen.</p>
                </div>
                <!-- Final Action -->
                <button type="button" id="btn-save-all" class="bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white font-semibold py-2.5 px-5 rounded-xl shadow-md flex items-center justify-center gap-2 transition-all">
                    <i class="fas fa-cloud-arrow-up"></i>
                    <span>Simpan Ke Bank Soal</span>
                </button>
            </div>

            <!-- List of Draft Questions -->
            <div id="questions-list" class="space-y-6">
                <!-- Javascript will render question cards here -->
            </div>
        </div>

        <!-- Status / States Display -->
        <div class="min-h-[250px] flex items-center justify-center bg-white rounded-2xl border border-gray-100 p-6" id="status-panel">
            <!-- Empty State -->
            <div id="empty-state" class="text-center max-w-sm py-8">
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-500 text-2xl mx-auto mb-4 magic-glow">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <h3 class="text-gray-800 font-bold text-base">Belum Ada Soal Dibuat</h3>
                <p class="text-gray-400 text-xs mt-2">Pilih Bank Soal, unggah berkas materi atau salin teks ringkasan, lalu klik tombol **Analisis & Rumuskan Soal**.</p>
            </div>

            <!-- Loading State -->
            <div id="loading-state" class="hidden text-center py-8">
                <div class="relative w-16 h-16 mx-auto mb-4">
                    <div class="absolute inset-0 rounded-full border-4 border-emerald-100"></div>
                    <div class="absolute inset-0 rounded-full border-4 border-emerald-500 border-t-transparent animate-spin"></div>
                    <div class="absolute inset-0 flex items-center justify-center text-emerald-500 text-lg">
                        <i class="fas fa-sparkles magic-glow"></i>
                    </div>
                </div>
                <h3 class="text-gray-800 font-bold text-base">AI Sedang Merumuskan Soal...</h3>
                <p class="text-gray-400 text-xs mt-2 max-w-xs mx-auto">Kami sedang mengekstrak teks, menganalisis kedalaman topik, dan memformulasikan butir soal pilihan ganda.</p>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabPdf = document.getElementById('tab-pdf');
        const tabText = document.getElementById('tab-text');
        const contentTypeInput = document.getElementById('content_type');
        const pdfInputArea = document.getElementById('pdf-input-area');
        const textInputArea = document.getElementById('text-input-area');
        const pdfFileInput = document.getElementById('pdf_file');
        const pdfFileNameDisplay = document.getElementById('pdf-file-name');

        const generatorForm = document.getElementById('generator-form');
        const btnGenerate = document.getElementById('btn-generate');
        const reviewContainer = document.getElementById('review-container');
        const questionsList = document.getElementById('questions-list');
        const btnSaveAll = document.getElementById('btn-save-all');

        const statusPanel = document.getElementById('status-panel');
        const emptyState = document.getElementById('empty-state');
        const loadingState = document.getElementById('loading-state');

        // Global state of generated questions
        let generatedQuestions = [];

        // Toggle Input Tabs
        tabPdf.addEventListener('click', function() {
            contentTypeInput.value = 'pdf';
            tabPdf.className = 'px-4 py-2 text-xs font-semibold rounded-lg bg-white text-emerald-700 shadow-sm border border-gray-100 transition focus:outline-none';
            tabText.className = 'px-4 py-2 text-xs font-semibold rounded-lg text-gray-600 hover:text-gray-800 transition focus:outline-none';
            pdfInputArea.classList.remove('hidden');
            textInputArea.classList.add('hidden');
        });

        tabText.addEventListener('click', function() {
            contentTypeInput.value = 'text';
            tabText.className = 'px-4 py-2 text-xs font-semibold rounded-lg bg-white text-emerald-700 shadow-sm border border-gray-100 transition focus:outline-none';
            tabPdf.className = 'px-4 py-2 text-xs font-semibold rounded-lg text-gray-600 hover:text-gray-800 transition focus:outline-none';
            textInputArea.classList.remove('hidden');
            pdfInputArea.classList.add('hidden');
        });

        // Show uploaded PDF file name
        pdfFileInput.addEventListener('change', function() {
            if (pdfFileInput.files && pdfFileInput.files[0]) {
                pdfFileNameDisplay.innerHTML = `<i class="fas fa-file-alt mr-1"></i> ${pdfFileInput.files[0].name} (${(pdfFileInput.files[0].size / 1024 / 1024).toFixed(2)} MB)`;
                pdfFileNameDisplay.classList.remove('hidden');
            } else {
                pdfFileNameDisplay.classList.add('hidden');
            }
        });

        // Generate Form Submit
        generatorForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Basic validation
            const bankId = document.getElementById('question_bank_id').value;
            if (!bankId) {
                alert('Pilih Bank Soal tujuan terlebih dahulu.');
                return;
            }

            if (contentTypeInput.value === 'pdf' && (!pdfFileInput.files || !pdfFileInput.files[0])) {
                alert('Pilih berkas PDF materi yang ingin diunggah.');
                return;
            }

            if (contentTypeInput.value === 'text' && !document.getElementById('raw_text').value.trim()) {
                alert('Tempelkan materi teks terlebih dahulu.');
                return;
            }

            // Set states
            statusPanel.classList.remove('hidden');
            emptyState.classList.add('hidden');
            loadingState.classList.remove('hidden');
            reviewContainer.classList.add('hidden');

            btnGenerate.disabled = true;
            btnGenerate.innerHTML = `<i class="fas fa-circle-notch animate-spin mr-1.5"></i> Menganalisis...`;

            const formData = new FormData(generatorForm);

            try {
                const response = await fetch("{{ route('guru.ai.question-generator.generate') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success && data.questions) {
                    generatedQuestions = data.questions;
                    renderQuestionCards();
                    
                    statusPanel.classList.add('hidden');
                    reviewContainer.classList.remove('hidden');
                } else {
                    alert('Gagal membuat soal: ' + (data.message || 'Terjadi kesalahan tidak dikenal.'));
                    loadingState.classList.add('hidden');
                    emptyState.classList.remove('hidden');
                }
            } catch (err) {
                console.error(err);
                alert('Gagal menghubungkan dengan server PembdaHub.');
                loadingState.classList.add('hidden');
                emptyState.classList.remove('hidden');
            } finally {
                btnGenerate.disabled = false;
                btnGenerate.innerHTML = `<i class="fas fa-sparkles mr-1.5"></i> Analisis & Rumuskan Soal Ujian`;
            }
        });

        // Render question cards from generatedQuestions array
        function renderQuestionCards() {
            questionsList.innerHTML = '';
            
            if (generatedQuestions.length === 0) {
                reviewContainer.classList.add('hidden');
                statusPanel.classList.remove('hidden');
                emptyState.classList.remove('hidden');
                return;
            }

            generatedQuestions.forEach((q, qIndex) => {
                const card = document.createElement('div');
                card.className = 'bg-white rounded-2xl shadow-sm border border-gray-150 p-6 space-y-4 relative';
                
                let optionsHtml = '';
                const labels = ['A', 'B', 'C', 'D', 'E'];
                
                labels.forEach(label => {
                    const textOption = q.options[label] || '';
                    const isChecked = q.answer === label ? 'checked' : '';
                    optionsHtml += `
                        <div class="flex items-center gap-3 bg-gray-50/50 p-2.5 rounded-xl border border-gray-100 focus-within:border-emerald-300 transition">
                            <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-800 font-bold flex items-center justify-center text-xs border border-emerald-100">${label}</span>
                            <input type="text" value="${escapeHtml(textOption)}" 
                                class="flex-1 bg-transparent border-none p-0 text-sm focus:ring-0 text-gray-700" 
                                onchange="updateOption(${qIndex}, '${label}', this.value)">
                            <label class="flex items-center gap-1.5 cursor-pointer text-xs font-semibold select-none text-gray-500 hover:text-emerald-700">
                                <input type="radio" name="correct_${qIndex}" value="${label}" ${isChecked} 
                                    class="w-3.5 h-3.5 text-emerald-600 focus:ring-emerald-500 border-gray-300"
                                    onchange="updateCorrectAnswer(${qIndex}, '${label}')">
                                Benar
                            </label>
                        </div>
                    `;
                });

                card.innerHTML = `
                    <div class="flex items-center justify-between gap-4 border-b border-gray-100 pb-3">
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg">Butir Soal #${qIndex + 1}</span>
                        <button type="button" class="text-gray-400 hover:text-rose-500 p-1.5 rounded-lg hover:bg-rose-50 transition" onclick="removeQuestion(${qIndex})" title="Hapus Soal">
                            <i class="fas fa-trash-alt text-sm"></i>
                        </button>
                    </div>

                    <!-- Question Text -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider">Pertanyaan</label>
                        <textarea rows="2" class="w-full rounded-xl border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition"
                            onchange="updateQuestionText(${qIndex}, this.value)">${escapeHtml(q.question)}</textarea>
                    </div>

                    <!-- Choices A-E Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        ${optionsHtml}
                    </div>

                    <!-- Explanation -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider">Pembahasan Jawaban</label>
                        <textarea rows="2" class="w-full rounded-xl border-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition" placeholder="Tulis pembahasan mengapa pilihan tersebut benar..."
                            onchange="updateExplanation(${qIndex}, this.value)">${escapeHtml(q.explanation || '')}</textarea>
                    </div>
                `;
                
                questionsList.appendChild(card);
            });
        }

        // Global function bindings to update generatedQuestions array
        window.updateQuestionText = function(index, value) {
            generatedQuestions[index].question = value;
        };

        window.updateOption = function(qIndex, label, value) {
            generatedQuestions[qIndex].options[label] = value;
        };

        window.updateCorrectAnswer = function(qIndex, label) {
            generatedQuestions[qIndex].answer = label;
        };

        window.updateExplanation = function(index, value) {
            generatedQuestions[index].explanation = value;
        };

        window.removeQuestion = function(index) {
            generatedQuestions.splice(index, 1);
            renderQuestionCards();
        };

        function escapeHtml(text) {
            if (!text) return '';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Save All reviewed questions to database
        btnSaveAll.addEventListener('click', async function() {
            const bankId = document.getElementById('question_bank_id').value;
            if (!bankId) {
                alert('Pilih Bank Soal terlebih dahulu.');
                return;
            }

            if (generatedQuestions.length === 0) {
                alert('Tidak ada draf soal untuk disimpan.');
                return;
            }

            btnSaveAll.disabled = true;
            btnSaveAll.innerHTML = `<i class="fas fa-circle-notch animate-spin"></i> Menyimpan...`;

            try {
                const response = await fetch("{{ route('guru.ai.question-generator.save') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        question_bank_id: bankId,
                        questions: generatedQuestions
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message || 'Soal berhasil disimpan!');
                    // Redirect or Reset View
                    window.location.href = "{{ route('guru.cbt.banks.index') }}";
                } else {
                    alert('Gagal menyimpan soal: ' + (data.message || 'Terjadi kesalahan tidak dikenal.'));
                    btnSaveAll.disabled = false;
                    btnSaveAll.innerHTML = `<i class="fas fa-cloud-arrow-up"></i> Simpan Ke Bank Soal`;
                }
            } catch (err) {
                console.error(err);
                alert('Gagal terhubung dengan server PembdaHub saat menyimpan.');
                btnSaveAll.disabled = false;
                btnSaveAll.innerHTML = `<i class="fas fa-cloud-arrow-up"></i> Simpan Ke Bank Soal`;
            }
        });

    });
</script>
@endpush
