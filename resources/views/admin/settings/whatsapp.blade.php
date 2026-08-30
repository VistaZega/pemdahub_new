@extends('layouts.admin')

@section('title', 'Pengaturan WA & Otomatisasi')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: 'all',
    searchQuery: '',
    toggleAllInGroup(groupKey, state) {
        const container = document.getElementById('group-' + groupKey);
        if (container) {
            const checkboxes = container.querySelectorAll('input[type=checkbox]');
            checkboxes.forEach(cb => cb.checked = state);
        }
    },
    matchesSearch(text, key, desc) {
        if (!this.searchQuery.trim()) return true;
        const q = this.searchQuery.toLowerCase();
        return (text && text.toLowerCase().includes(q)) || 
               (key && key.toLowerCase().includes(q)) || 
               (desc && desc.toLowerCase().includes(q));
    }
}">
    <!-- Top Header Banner (Tactile Neo-Brutalist Accent) -->
    <div class="bg-white rounded-2xl border-[1.5px] border-slate-900 shadow-[4px_4px_0px_#0f172a] p-6 sm:p-7 flex flex-col md:flex-row md:items-center justify-between gap-5">
        <div class="flex items-start sm:items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-emerald-500 border-2 border-slate-900 shadow-[3px_3px_0px_#0f172a] flex items-center justify-center text-white text-2xl flex-shrink-0">
                <i class="fab fa-whatsapp"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-mono font-bold border border-emerald-300 uppercase tracking-wider">
                        ⚡ Gateway & Automation Engine
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                    Pengaturan WhatsApp & Otomatisasi
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-0.5">
                    Kelola status koneksi gateway dan konfigurasi saklar (On/Off) rekap presensi, SPP, dan notifikasi sekolah.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.settings.whatsapp.templates') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold border-[1.5px] border-slate-900 shadow-[3px_3px_0px_#0f172a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_0px_#0f172a] transition-all flex items-center gap-2">
                <i class="fas fa-file-signature"></i>
                <span>Editor Template Pesan</span>
            </a>
            <a href="{{ url('/check_phone_data.php?secret=pembda99') }}" target="_blank" class="px-3.5 py-2.5 bg-sky-500 hover:bg-sky-600 text-white rounded-xl text-xs font-bold border-[1.5px] border-slate-900 shadow-[3px_3px_0px_#0f172a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_0px_#0f172a] transition-all flex items-center gap-1.5" title="Diagnostik Nomor HP Kepsek & Wali Kelas">
                <i class="fas fa-clipboard-check"></i>
                <span>Diagnostik No. HP</span>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold border border-slate-300 transition flex items-center gap-1.5">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border-[1.5px] border-emerald-600 rounded-2xl flex items-center justify-between shadow-[3px_3px_0px_#059669]">
        <div class="flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-600 text-lg"></i>
            <p class="text-emerald-900 font-bold text-xs sm:text-sm">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-rose-50 border-[1.5px] border-rose-600 rounded-2xl flex items-center justify-between shadow-[3px_3px_0px_#e11d48]">
        <div class="flex items-center gap-3">
            <i class="fas fa-exclamation-triangle text-rose-600 text-lg"></i>
            <p class="text-rose-900 font-bold text-xs sm:text-sm">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <!-- Dual Gateway Provider Status Widget -->
    @php
        $isConnected = !empty($accountInfo['success']) && (
            ($accountInfo['data']['status'] ?? '') === 'connected' || 
            ($accountInfo['data']['device_status'] ?? '') === 'connect' ||
            ($activeProvider === 'fonnte' && !empty($accountInfo['data']['status']))
        );
        $currentProvider = $activeProvider ?? 'fonnte';
    @endphp

    <div class="bg-white rounded-2xl border-[1.5px] border-slate-200 shadow-sm p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fas fa-server text-emerald-600"></i> Status Gateway Provider WhatsApp
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Pilih gateway aktif yang akan memproses pengiriman notifikasi otomatis</p>
            </div>
            <div>
                <span class="px-3.5 py-1.5 rounded-full text-xs font-mono font-bold border {{ $isConnected ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-amber-100 text-amber-800 border-amber-300' }}">
                    {{ $isConnected ? '● ONLINE & TERHUBUNG' : '○ STANDBY / BELUM SCAN' }}
                </span>
            </div>
        </div>

        <!-- 2 Provider Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Card 1: Fonnte Cloud -->
            @php $isFonnteActive = ($currentProvider === 'fonnte'); @endphp
            <div class="relative rounded-2xl p-5 border-2 transition-all {{ $isFonnteActive ? 'border-emerald-500 bg-emerald-50/20 shadow-md shadow-emerald-500/10' : 'border-slate-200 bg-slate-50/60 opacity-80 hover:opacity-100' }}">
                @if($isFonnteActive)
                <div class="absolute -top-3 right-4 px-3 py-0.5 bg-emerald-600 text-white rounded-full text-[10px] font-mono font-bold shadow-sm">
                    ✓ PROVIDER AKTIF
                </div>
                @endif

                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl {{ $isFonnteActive ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600' }} flex items-center justify-center font-bold text-base shadow-sm">
                            <i class="fas fa-cloud"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Fonnte (Cloud API)</h4>
                            <p class="text-[11px] text-slate-500">Gateway Resmi Berbayar • Direkomendasikan untuk Server Hosting</p>
                        </div>
                    </div>
                </div>

                <!-- Token Fonnte Section -->
                @if(!empty($providersInfo['fonnte']['has_token']))
                <div x-data="{ editing: false, showToken: false }" class="mb-3.5 bg-white rounded-xl p-3 border border-slate-200 shadow-sm space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1.5">
                            <i class="fas fa-key text-amber-500"></i> API Token Fonnte:
                        </label>
                        <span class="text-[10px] text-emerald-700 bg-emerald-100 font-bold px-2 py-0.5 rounded-full flex items-center gap-1 border border-emerald-200">
                            <i class="fas fa-lock text-[9px]"></i> Terkunci & Aman
                        </span>
                    </div>

                    <!-- Locked View (Masked) -->
                    <div x-show="!editing" class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2">
                        <div class="flex items-center gap-2 overflow-hidden">
                            <i class="fas fa-shield-alt text-emerald-600 text-xs"></i>
                            <span class="font-mono text-xs text-slate-600 tracking-wider truncate" x-show="!showToken">
                                {{ strlen($providersInfo['fonnte']['api_token']) > 8 ? substr($providersInfo['fonnte']['api_token'], 0, 4) . '••••••••••••' . substr($providersInfo['fonnte']['api_token'], -4) : '••••••••••••••••' }}
                            </span>
                            <span class="font-mono text-xs text-slate-900 select-all truncate" x-show="showToken" style="display: none;">
                                {{ $providersInfo['fonnte']['api_token'] }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5 ml-2">
                            <button type="button" @click="showToken = !showToken" class="p-1.5 px-2.5 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg text-xs text-slate-600 font-semibold transition" title="Lihat/Sembunyikan">
                                <i class="fas" :class="showToken ? 'fa-eye-slash text-rose-500' : 'fa-eye text-slate-500'"></i>
                            </button>
                            <button type="button" @click="editing = true" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold transition flex items-center gap-1 shadow-sm whitespace-nowrap">
                                <i class="fas fa-lock-open text-[10px]"></i>
                                <span>Ganti</span>
                            </button>
                        </div>
                    </div>

                    <!-- Edit Form -->
                    <form x-show="editing" style="display: none;" action="{{ route('admin.settings.whatsapp.credentials.update') }}" method="POST" class="space-y-2 pt-1">
                        @csrf
                        <input type="hidden" name="provider" value="fonnte">
                        <div class="flex gap-2">
                            <input type="password" name="api_token" placeholder="Paste Token Fonnte Baru..." class="w-full px-3.5 py-2 rounded-xl border border-emerald-400 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white" required>
                            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition whitespace-nowrap">
                                Simpan
                            </button>
                            <button type="button" @click="editing = false" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-bold transition">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
                @else
                <!-- Form Input Token Pertama Kali -->
                <form action="{{ route('admin.settings.whatsapp.credentials.update') }}" method="POST" class="mb-3.5 bg-white rounded-xl p-3.5 border border-amber-200 shadow-sm space-y-2.5">
                    @csrf
                    <input type="hidden" name="provider" value="fonnte">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            <i class="fas fa-key text-amber-500"></i> API Token Fonnte:
                        </label>
                        <span class="text-[10px] text-amber-700 bg-amber-100 font-bold px-2 py-0.5 rounded-full">
                            ⚠️ Belum Terisi
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <input type="password" name="api_token" placeholder="Paste Token dari fonnte.com..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono focus:outline-none focus:border-emerald-500 bg-white" required>
                        <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition whitespace-nowrap">
                            Simpan
                        </button>
                    </div>
                </form>
                @endif

                @if($isFonnteActive && !empty($accountInfo['success']) && !empty($accountInfo['data']['device']))
                <div class="mb-3.5 bg-emerald-50/80 rounded-xl p-3 border border-emerald-200 space-y-1.5 text-xs text-emerald-900">
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-700 font-medium">Perangkat Terhubung:</span>
                        <span class="font-bold font-mono">{{ $accountInfo['data']['name'] ?? '' }} ({{ $accountInfo['data']['device'] ?? '' }})</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-700 font-medium">Sisa Kuota:</span>
                        <span class="font-bold bg-emerald-200/70 text-emerald-900 px-2 py-0.5 rounded text-[11px]">{{ $accountInfo['data']['quota'] ?? '0' }} Pesan ({{ $accountInfo['data']['package'] ?? 'Paket' }})</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-700 font-medium">Masa Berlaku:</span>
                        <span class="font-semibold">{{ $accountInfo['data']['expired'] ?? '-' }}</span>
                    </div>
                </div>
                @endif

                <div class="flex gap-2">
                    @if(!$isFonnteActive)
                    <form action="{{ route('admin.settings.whatsapp.switch_provider') }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="provider" value="fonnte">
                        <button type="submit" onclick="return confirm('Beralih ke provider Fonnte?')" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5">
                            <i class="fas fa-toggle-on"></i>
                            <span>Pilih Fonnte</span>
                        </button>
                    </form>
                    @endif
                    <a href="https://fonnte.com" target="_blank" class="{{ $isFonnteActive ? 'w-full' : 'px-3.5' }} py-2 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5">
                        <i class="fas fa-external-link-alt text-[10px]"></i>
                        <span>Dashboard Fonnte</span>
                    </a>
                </div>
            </div>

            <!-- Card 2: Self-Hosted Baileys -->
            @php $isBaileysActive = ($currentProvider === 'selfhosted'); @endphp
            <div class="relative rounded-2xl p-5 border-2 transition-all {{ $isBaileysActive ? 'border-emerald-500 bg-emerald-50/20 shadow-md shadow-emerald-500/10' : 'border-slate-200 bg-slate-50/60 opacity-80 hover:opacity-100' }}">
                @if($isBaileysActive)
                <div class="absolute -top-3 right-4 px-3 py-0.5 bg-emerald-600 text-white rounded-full text-[10px] font-mono font-bold shadow-sm">
                    ✓ PROVIDER AKTIF
                </div>
                @endif

                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl {{ $isBaileysActive ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600' }} flex items-center justify-center font-bold text-base shadow-sm">
                            <i class="fas fa-server"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Self-Hosted Baileys</h4>
                            <p class="text-[11px] text-slate-500">Gratis ($0) • Server Node.js Internal / VPS Lokal</p>
                        </div>
                    </div>
                </div>

                <!-- Endpoint URL -->
                <div x-data="{ editing: false }" class="mb-3.5 bg-white rounded-xl p-3 border border-slate-200 shadow-sm space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1.5">
                            <i class="fas fa-link text-blue-500"></i> URL Server Baileys:
                        </label>
                        <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-full border {{ $isBaileysActive && $isConnected ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200' }}">
                            {{ $isBaileysActive ? ($isConnected ? '✓ Ready' : 'Standby') : 'Tersimpan' }}
                        </span>
                    </div>

                    <div x-show="!editing" class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2">
                        <code class="font-mono text-xs text-slate-700 truncate">{{ $providersInfo['selfhosted']['api_url'] ?? 'http://localhost:3000' }}</code>
                        <button type="button" @click="editing = true" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold transition flex items-center gap-1 shadow-sm whitespace-nowrap ml-2">
                            <i class="fas fa-edit text-[10px]"></i>
                            <span>Ubah</span>
                        </button>
                    </div>

                    <form x-show="editing" style="display: none;" action="{{ route('admin.settings.whatsapp.credentials.update') }}" method="POST" class="space-y-2 pt-1">
                        @csrf
                        <input type="hidden" name="provider" value="selfhosted">
                        <div class="flex gap-2">
                            <input type="text" name="api_url" value="{{ $providersInfo['selfhosted']['api_url'] ?? 'http://localhost:3000' }}" placeholder="http://localhost:3000" class="w-full px-3.5 py-2 rounded-xl border border-blue-400 text-xs font-mono focus:outline-none bg-white" required>
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition whitespace-nowrap">
                                Simpan
                            </button>
                            <button type="button" @click="editing = false" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-bold transition">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>

                <div class="flex gap-2">
                    @if(!$isBaileysActive)
                    <form action="{{ route('admin.settings.whatsapp.switch_provider') }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="provider" value="selfhosted">
                        <button type="submit" onclick="return confirm('Beralih ke provider Baileys?')" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5">
                            <i class="fas fa-toggle-on"></i>
                            <span>Pilih Baileys</span>
                        </button>
                    </form>
                    @else
                    <span class="w-full py-2 bg-slate-100 text-slate-600 text-center rounded-xl text-xs font-bold">
                        ✓ Sedang Digunakan Sebagai Gateway
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Main Workspace: Automation Switchboard (2 Cols) & Live Test Center (1 Col) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- SAKLAR OTOMATISASI (2 COLS) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border-[1.5px] border-slate-200 shadow-sm p-6 sm:p-7 space-y-6">
            
            <!-- Section Header & Filter Search -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <h2 class="text-lg font-black text-slate-900 flex items-center gap-2.5">
                        <i class="fas fa-toggle-on text-emerald-600"></i> Saklar Otomatisasi Berdasarkan Target
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Aktifkan atau nonaktifkan pengiriman notifikasi otomatis sesuai kebutuhan sekolah</p>
                </div>
                <div class="relative w-full sm:w-64">
                    <i class="fas fa-search absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                    <input type="text" x-model="searchQuery" placeholder="Cari notifikasi / jadwal..." class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-slate-50 shadow-sm">
                </div>
            </div>

            <!-- Target Category Tabs -->
            <div class="flex flex-wrap gap-2 pb-2 border-b border-slate-100">
                <button type="button" @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                    <i class="fas fa-layer-group text-xs"></i>
                    <span>Semua Target</span>
                </button>
                @foreach($groupedSettings as $gKey => $group)
                <button type="button" @click="activeTab = '{{ $gKey }}'" :class="activeTab === '{{ $gKey }}' ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                    <i class="{{ $group['icon'] }} text-xs"></i>
                    <span>{{ $group['badge'] }}</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded font-mono" :class="activeTab === '{{ $gKey }}' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">
                        {{ count($group['items']) }}
                    </span>
                </button>
                @endforeach
            </div>

            <form action="{{ route('admin.settings.whatsapp.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                @foreach($groupedSettings as $gKey => $group)
                <div id="group-{{ $gKey }}" x-show="activeTab === 'all' || activeTab === '{{ $gKey }}'" class="space-y-3 pt-1">
                    <!-- Group Header Card -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/80 p-3.5 rounded-xl border border-slate-200">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-800 text-white flex items-center justify-center text-xs shadow-sm">
                                <i class="{{ $group['icon'] }}"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                    {{ $group['target'] }}
                                    <span class="px-2 py-0.2 bg-slate-200 text-slate-700 text-[10px] font-mono font-bold rounded">
                                        {{ count($group['items']) }} Saklar
                                    </span>
                                </h3>
                                <p class="text-[11px] text-slate-500">{{ $group['description'] }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 self-end sm:self-center">
                            <button type="button" @click="toggleAllInGroup('{{ $gKey }}', true)" class="px-2.5 py-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded border border-emerald-200 transition">
                                Aktifkan Semua
                            </button>
                            <button type="button" @click="toggleAllInGroup('{{ $gKey }}', false)" class="px-2.5 py-1 text-[11px] font-bold text-slate-600 bg-white hover:bg-slate-100 rounded border border-slate-200 transition">
                                Matikan
                            </button>
                        </div>
                    </div>

                    <!-- Switches List -->
                    <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl overflow-hidden bg-white shadow-sm">
                        @foreach($group['items'] as $itemKey => $item)
                        <div x-show="matchesSearch('{{ addslashes($item['label']) }}', '{{ $itemKey }}', '{{ addslashes($item['desc'] ?? '') }}')" class="p-3.5 sm:p-4 flex items-center justify-between hover:bg-slate-50/80 transition-colors">
                            <div class="pr-4 space-y-0.5">
                                <label for="{{ $itemKey }}" class="text-xs font-bold text-slate-900 cursor-pointer block hover:text-emerald-600 transition">
                                    {{ $item['label'] }}
                                </label>
                                @if(!empty($item['desc']))
                                <p class="text-[11px] text-slate-500 leading-relaxed">{{ $item['desc'] }}</p>
                                @endif
                                <span class="text-[10px] text-slate-400 font-mono font-bold">{{ $itemKey }}</span>
                            </div>

                            <!-- Toggle Switch Modern -->
                            <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                                <input type="checkbox" id="{{ $itemKey }}" name="{{ $itemKey }}" value="1" class="sr-only peer" {{ $item['enabled'] ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

                <!-- Submit Button -->
                <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-100">
                    <p class="text-xs text-slate-500 flex items-center gap-1.5">
                        <i class="fas fa-info-circle text-emerald-600"></i> Perubahan saklar akan langsung mempengaruhi jadwal scheduler cron.
                    </p>
                    <button type="submit" class="w-full sm:w-auto px-7 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs border-[1.5px] border-slate-900 shadow-[3px_3px_0px_#0f172a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_0px_#0f172a] transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-save text-sm"></i>
                        <span>Simpan Pengaturan Saklar</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- LIVE TESTING & EXECUTIVE DIGEST TRIGGER (1 COL) -->
        <div class="space-y-6">
            <!-- Box 1: Test Instant Single Message -->
            <div class="bg-white rounded-2xl border-[1.5px] border-slate-200 shadow-sm p-5 sm:p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                        <i class="fas fa-paper-plane text-emerald-600"></i> Uji Coba Kirim Pesan Instan
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Kirim pesan tes ke nomor WhatsApp admin/penguji</p>
                </div>

                <form action="{{ route('admin.settings.whatsapp.test') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Nomor WhatsApp Tujuan:</label>
                        <input type="text" name="phone" required placeholder="081234567890" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-mono focus:outline-none focus:border-emerald-500 bg-slate-50">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Isi Pesan Tes:</label>
                        <textarea name="message" rows="3" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-emerald-500 bg-slate-50 leading-relaxed font-mono">📢 *TEST GATEWAY WHATSAPP PEMBDAHUB*
Koneksi gateway WhatsApp berhasil aktif dan siap digunakan!</textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs border-[1.5px] border-slate-900 shadow-[2px_2px_0px_#0f172a] hover:translate-x-[1px] hover:translate-y-[1px] transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane text-xs"></i>
                        <span>Kirim Pesan Tes</span>
                    </button>
                </form>
            </div>

            <!-- Box 2: Test Executive Digest Trigger -->
            <div class="bg-white rounded-2xl border-[1.5px] border-slate-200 shadow-sm p-5 sm:p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                        <i class="fas fa-user-tie text-blue-600"></i> Uji Coba Laporan Eksekutif
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Eksekusi langsung rekap data asli ke nomor Kepsek / Wali Kelas</p>
                </div>

                <form action="{{ route('admin.settings.whatsapp.digest.test') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Pilih Jenis Laporan:</label>
                        <select name="digest_type" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:border-blue-500 bg-white">
                            <option value="principal_attendance">🏫 Kepsek: Rekap Presensi Sekolah (08:00 WIB)</option>
                            <option value="homeroom_attendance">👩‍🏫 Wali Kelas: Rekap Presensi Kelas Binaan (08:00 WIB)</option>
                            <option value="principal_spp">💰 Kepsek: Rekap Keuangan SPP Bulanan</option>
                            <option value="homeroom_spp">💳 Wali Kelas: Rekap Tunggakan SPP Kelas</option>
                        </select>
                    </div>

                    <button type="submit" onclick="return confirm('Kirim laporan eksekutif sekarang ke nomor pejabat terkait?')" class="w-full py-2.5 bg-slate-900 hover:bg-black text-white rounded-xl font-bold text-xs border-[1.5px] border-slate-900 shadow-[2px_2px_0px_#0f172a] hover:translate-x-[1px] hover:translate-y-[1px] transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-bolt text-amber-400 text-xs"></i>
                        <span>Eksekusi Laporan Sekarang</span>
                    </button>
                </form>
            </div>

            <!-- Box 3: Quick Info -->
            <div class="p-4 bg-slate-900 text-white rounded-2xl border-[1.5px] border-slate-900 shadow-[3px_3px_0px_#0f172a] space-y-2 text-xs">
                <div class="flex items-center gap-2 text-amber-400 font-bold">
                    <i class="fas fa-clock"></i>
                    <span>Jadwal Pengiriman Otomatis:</span>
                </div>
                <p class="text-[11px] text-slate-300 leading-relaxed">
                    Cron Job scheduler berjalan setiap hari <b>Senin s/d Jumat pukul 08:00 WIB</b> untuk mengirim rekapitulasi kehadiran harian ke seluruh Kepala Sekolah dan Wali Kelas aktif.
                </p>
            </div>
        </div>

    </div>
</div>
@endsection
