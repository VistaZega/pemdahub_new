@extends('layouts.admin')

@section('title', 'Pengaturan AI (ClassAI Router & Gemini) - PembdaHUB')

@section('content')
<div class="space-y-6" x-data="{
    provider: '{{ old('ai_provider', $aiProvider) }}',
    showClassAiKey: false,
    showGeminiKey: false,
    model: '{{ old('classai_model', $classAiModel) }}'
}">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-6 rounded-2xl border-[1.5px] border-slate-900 shadow-[4px_4px_0px_#0f172a]">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-violet-600 border-[1.5px] border-slate-900 shadow-[3px_3px_0px_#0f172a] text-white flex items-center justify-center text-2xl flex-shrink-0">
                <i class="fas fa-robot"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-violet-100 text-violet-800 border border-violet-300">
                        AI ENGINE CONFIGURATION
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ ($aiProvider === 'classai' && !empty($classAiApiKey)) || ($aiProvider === 'gemini' && !empty($geminiApiKey)) ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300' }}">
                        {{ ($aiProvider === 'classai' && !empty($classAiApiKey)) || ($aiProvider === 'gemini' && !empty($geminiApiKey)) ? 'API KEY TERPASANG' : 'MODE FALLBACK LOKAL' }}
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 mt-1">Pengaturan AI (ClassAI Router &amp; Gemini)</h1>
                <p class="text-xs text-slate-500 font-medium mt-0.5">Kelola API Key dan model AI terpusat tanpa perlu mengedit file .env server</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <form action="{{ route('admin.settings.ai.test') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 border-[1.5px] border-slate-900 shadow-[2px_2px_0px_#0f172a] cursor-pointer">
                    <i class="fas fa-vial"></i> Test Koneksi AI
                </button>
            </form>
            <a href="{{ route('admin.settings.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-2 border border-slate-300">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border-[1.5px] border-emerald-600 rounded-2xl flex items-center gap-3 shadow-[3px_3px_0px_#059669]">
        <i class="fas fa-check-circle text-emerald-600 text-lg"></i>
        <p class="text-emerald-900 font-bold text-xs sm:text-sm">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-rose-50 border-[1.5px] border-rose-600 rounded-2xl flex items-center gap-3 shadow-[3px_3px_0px_#e11d48]">
        <i class="fas fa-exclamation-triangle text-rose-600 text-lg"></i>
        <p class="text-rose-900 font-bold text-xs sm:text-sm">{{ session('error') }}</p>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 bg-rose-50 border-[1.5px] border-rose-600 rounded-2xl shadow-[3px_3px_0px_#e11d48]">
        <ul class="list-disc list-inside text-xs sm:text-sm font-bold text-rose-900 space-y-1">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Info Modul AI -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="text-xs font-black text-violet-700 uppercase">1. Asisten AI Siswa</div>
            <p class="text-xs text-slate-600 mt-1">Chatbot tanya-jawab belajar interaktif untuk siswa.</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="text-xs font-black text-violet-700 uppercase">2. Generator RPP</div>
            <p class="text-xs text-slate-600 mt-1">Pembuatan Modul Ajar Kurikulum Merdeka &amp; ekspor Word.</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="text-xs font-black text-violet-700 uppercase">3. Generator Soal CBT</div>
            <p class="text-xs text-slate-600 mt-1">Ekstraksi soal otomatis dari teks maupun file PDF.</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="text-xs font-black text-violet-700 uppercase">4. Materi LMS Guru</div>
            <p class="text-xs text-slate-600 mt-1">Penyusunan materi ajar terstruktur di modul LMS.</p>
        </div>
    </div>

    <!-- Form Pengaturan AI -->
    <form action="{{ route('admin.settings.ai.update') }}" method="POST" class="bg-white rounded-2xl border-[1.5px] border-slate-900 shadow-[4px_4px_0px_#0f172a] p-6 space-y-6">
        @csrf
        @method('PUT')

        <!-- Pilih Provider -->
        <div>
            <label class="block text-sm font-black text-slate-900 mb-3">Provider AI Aktif</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="cursor-pointer rounded-xl border-2 p-4 transition flex items-start gap-3"
                       :class="provider === 'classai' ? 'border-violet-600 bg-violet-50' : 'border-slate-200 hover:border-slate-300'">
                    <input type="radio" name="ai_provider" value="classai" x-model="provider" class="mt-1 text-violet-600 focus:ring-violet-500">
                    <div>
                        <div class="font-black text-slate-900 text-sm">ClassAI Router (Recommended)</div>
                        <p class="text-xs text-slate-600 mt-0.5">Multi-model gateway (DeepSeek, Gemini, Claude, GPT) menggunakan satu API Key ClassAI (`sk_live_...`).</p>
                    </div>
                </label>

                <label class="cursor-pointer rounded-xl border-2 p-4 transition flex items-start gap-3"
                       :class="provider === 'gemini' ? 'border-violet-600 bg-violet-50' : 'border-slate-200 hover:border-slate-300'">
                    <input type="radio" name="ai_provider" value="gemini" x-model="provider" class="mt-1 text-violet-600 focus:ring-violet-500">
                    <div>
                        <div class="font-black text-slate-900 text-sm">Google Gemini Native</div>
                        <p class="text-xs text-slate-600 mt-0.5">Koneksi langsung ke API resmi Google AI Studio (`generativelanguage.googleapis.com`).</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Konfigurasi ClassAI Router -->
        <div class="p-5 rounded-2xl border border-violet-200 bg-violet-50/40 space-y-4" x-show="provider === 'classai'" x-transition>
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-violet-900 flex items-center gap-2">
                    <i class="fas fa-network-wired"></i> Konfigurasi ClassAI Router
                </h3>
                <span class="text-[11px] font-mono text-violet-700 bg-violet-100 px-2.5 py-0.5 rounded-full font-bold">OpenAI-Compatible</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="classai_api_key" class="block text-xs font-bold text-slate-700 mb-1">ClassAI API Key</label>
                    <div class="relative">
                        <input :type="showClassAiKey ? 'text' : 'password'"
                               id="classai_api_key"
                               name="classai_api_key"
                               value="{{ old('classai_api_key', $classAiApiKey) }}"
                               placeholder="sk_live_xxxxxxxxxxxxxxxx"
                               class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 pr-10 text-sm font-mono focus:border-violet-600 focus:ring-violet-600">
                        <button type="button" @click="showClassAiKey = !showClassAiKey" class="absolute inset-y-0 right-0 px-3 text-slate-500 hover:text-slate-800 cursor-pointer">
                            <i class="fas" :class="showClassAiKey ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="classai_model" class="block text-xs font-bold text-slate-700 mb-1">Model AI</label>
                    <input type="text"
                           id="classai_model"
                           name="classai_model"
                           x-model="model"
                           placeholder="deepseek-v4-flash-0731"
                           class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-mono focus:border-violet-600 focus:ring-violet-600">
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        <button type="button" @click="model = 'deepseek-v4-flash-0731'" class="text-[11px] font-mono px-2 py-0.5 rounded bg-white border border-violet-300 text-violet-800 hover:bg-violet-100 cursor-pointer">
                            deepseek-v4-flash-0731
                        </button>
                        <button type="button" @click="model = 'gemini-2.5-flash'" class="text-[11px] font-mono px-2 py-0.5 rounded bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 cursor-pointer">
                            gemini-2.5-flash
                        </button>
                        <button type="button" @click="model = 'claude-sonnet-5.5'" class="text-[11px] font-mono px-2 py-0.5 rounded bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 cursor-pointer">
                            claude-sonnet-5.5
                        </button>
                    </div>
                </div>
            </div>

            <div>
                <label for="classai_base_url" class="block text-xs font-bold text-slate-700 mb-1">Base URL Endpoint</label>
                <input type="url"
                       id="classai_base_url"
                       name="classai_base_url"
                       value="{{ old('classai_base_url', $classAiBaseUrl) }}"
                       placeholder="https://router.classai.id/v1"
                       class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm font-mono focus:border-violet-600 focus:ring-violet-600">
            </div>
        </div>

        <!-- Konfigurasi Google Gemini -->
        <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/70 space-y-4" x-show="provider === 'gemini'" x-transition>
            <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                <i class="fas fa-sparkles text-sky-600"></i> Konfigurasi Google Gemini Native
            </h3>

            <div>
                <label for="gemini_api_key" class="block text-xs font-bold text-slate-700 mb-1">Gemini API Key</label>
                <div class="relative">
                    <input :type="showGeminiKey ? 'text' : 'password'"
                           id="gemini_api_key"
                           name="gemini_api_key"
                           value="{{ old('gemini_api_key', $geminiApiKey) }}"
                           placeholder="AIzaSy..."
                           class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 pr-10 text-sm font-mono focus:border-violet-600 focus:ring-violet-600">
                    <button type="button" @click="showGeminiKey = !showGeminiKey" class="absolute inset-y-0 right-0 px-3 text-slate-500 hover:text-slate-800 cursor-pointer">
                        <i class="fas" :class="showGeminiKey ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2 border-t border-slate-100">
            <p class="text-xs text-slate-500">
                Setelah menyimpan, klik tombol <strong>Test Koneksi AI</strong> di kanan atas untuk memastikan API merespons dengan benar.
            </p>
            <button type="submit" class="px-6 py-2.5 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-sm font-bold transition flex items-center justify-center gap-2 border-[1.5px] border-slate-900 shadow-[3px_3px_0px_#0f172a] cursor-pointer">
                <i class="fas fa-save"></i> Simpan Konfigurasi AI
            </button>
        </div>
    </form>
</div>
@endsection
