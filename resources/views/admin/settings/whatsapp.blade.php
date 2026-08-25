@extends('layouts.admin')

@section('title', 'Pengaturan WA & Otomatisasi')

@section('content')
<div class="space-y-6">
    <!-- Header & Breadcrumb -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/20">
                <i class="fab fa-whatsapp text-white text-3xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pengaturan WhatsApp & Otomatisasi</h1>
                <p class="text-sm text-gray-500">Kelola status koneksi gateway dan daftar saklar (On/Off) otomatisasi pengiriman pesan ke wali murid</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.settings.whatsapp.templates') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2">
                <i class="fas fa-edit"></i> Edit Template & Syarat WA
            </a>
            <a href="{{ route('admin.settings.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-xl text-sm font-semibold transition-all flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-xl flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-600 text-lg"></i>
            <p class="text-emerald-800 font-semibold text-sm">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-xl flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
            <i class="fas fa-exclamation-triangle text-rose-600 text-lg"></i>
            <p class="text-rose-800 font-semibold text-sm">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <!-- Health Check & Status Server Widget -->
    <!-- Health Check & Status Server Widget (Dual Provider: Fonnte & Baileys) -->
    @php
        $isConnected = !empty($accountInfo['success']) && (
            ($accountInfo['data']['status'] ?? '') === 'connected' || 
            ($accountInfo['data']['device_status'] ?? '') === 'connect' ||
            ($activeProvider === 'fonnte' && !empty($accountInfo['data']['status']))
        );
        $currentProvider = $activeProvider ?? 'fonnte';
    @endphp
    
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-4">
            <div>
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-server text-emerald-600"></i> Gateway Provider WhatsApp Aktif
                </h3>
                <p class="text-xs text-gray-500">Anda dapat beralih antar provider kapan saja sesuai ketersediaan langganan</p>
            </div>
            <div>
                <span class="px-3.5 py-1.5 rounded-full text-xs font-bold {{ $isConnected ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-amber-100 text-amber-700 border border-amber-200' }}">
                    {{ $isConnected ? '🟢 ONLINE & TERHUBUNG' : '🟡 BELUM TERHUBUNG / STANDBY' }}
                </span>
            </div>
        </div>

        <!-- 2 Provider Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Card 1: Fonnte -->
            @php $isFonnteActive = ($currentProvider === 'fonnte'); @endphp
            <div class="relative rounded-2xl p-5 border-2 transition-all {{ $isFonnteActive ? 'border-emerald-500 bg-emerald-50/30 shadow-md shadow-emerald-500/10' : 'border-gray-200 bg-gray-50/50 opacity-90 hover:opacity-100' }}">
                @if($isFonnteActive)
                <div class="absolute -top-3 right-4 px-3 py-0.5 bg-emerald-600 text-white rounded-full text-[11px] font-bold shadow-sm">
                    ✓ SEDANG DIGUNAKAN
                </div>
                @endif
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl {{ $isFonnteActive ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-600' }} flex items-center justify-center font-bold text-base">
                            <i class="fas fa-cloud"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Fonnte (Cloud API)</h4>
                            <p class="text-[11px] text-gray-500">Pihak Ketiga • Recommended untuk Hosting</p>
                        </div>
                    </div>
                </div>

                <!-- Token Fonnte: Locked & Protected State -->
                @if(!empty($providersInfo['fonnte']['has_token']))
                <div x-data="{ editing: false, showToken: false }" class="mb-4 bg-white/95 rounded-xl p-3 border border-gray-200 shadow-sm space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-[11px] font-bold text-gray-700 flex items-center gap-1.5">
                            <i class="fas fa-key text-amber-500"></i> API Token Fonnte:
                        </label>
                        <span class="text-[10px] text-emerald-700 bg-emerald-100 font-bold px-2 py-0.5 rounded-full flex items-center gap-1 border border-emerald-200">
                            <i class="fas fa-lock text-[9px]"></i> Terkunci & Aman
                        </span>
                    </div>

                    <!-- Locked View (Masked) -->
                    <div x-show="!editing" class="flex items-center justify-between bg-gray-50 border border-gray-200/80 rounded-lg px-3 py-1.5">
                        <div class="flex items-center gap-2 overflow-hidden">
                            <i class="fas fa-shield-alt text-emerald-600 text-xs"></i>
                            <span class="font-mono text-xs text-gray-600 tracking-wider select-none truncate" x-show="!showToken">
                                {{ strlen($providersInfo['fonnte']['api_token']) > 8 ? substr($providersInfo['fonnte']['api_token'], 0, 4) . '••••••••••••' . substr($providersInfo['fonnte']['api_token'], -4) : '••••••••••••••••' }}
                            </span>
                            <span class="font-mono text-xs text-gray-900 select-all truncate" x-show="showToken" style="display: none;">
                                {{ $providersInfo['fonnte']['api_token'] }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5 ml-2">
                            <button type="button" @click="showToken = !showToken" class="p-1.5 px-2 bg-white hover:bg-gray-100 border border-gray-200 rounded text-xs text-gray-600 font-semibold transition" title="Lihat/Sembunyikan">
                                <i class="fas" :class="showToken ? 'fa-eye-slash text-rose-500' : 'fa-eye text-gray-500'"></i>
                            </button>
                            <button type="button" @click="editing = true" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-900 text-white rounded text-[11px] font-bold transition flex items-center gap-1 shadow-sm whitespace-nowrap">
                                <i class="fas fa-lock-open text-[10px]"></i> Buka / Ganti
                            </button>
                        </div>
                    </div>

                    <!-- Form Edit (Muncul jika klik 'Buka / Ganti') -->
                    <form x-show="editing" style="display: none;" action="{{ route('admin.settings.whatsapp.credentials.update') }}" method="POST" class="space-y-2 pt-1">
                        @csrf
                        <input type="hidden" name="provider" value="fonnte">
                        <div class="flex gap-2">
                            <input type="password" name="api_token" placeholder="Paste Token Fonnte Baru..." class="w-full px-3 py-1.5 rounded-lg border border-emerald-400 text-xs font-mono focus:outline-none focus:ring-1 focus:ring-emerald-500 bg-white" required>
                            <button type="submit" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-sm transition whitespace-nowrap">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <button type="button" @click="editing = false" class="px-2.5 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-xs font-bold transition">
                                Batal
                            </button>
                        </div>
                        <p class="text-[10px] text-gray-400">Token baru akan disimpan dan otomatis terkunci kembali.</p>
                    </form>
                </div>
                @else
                <!-- Form Input Pertama Kali (Belum ada token) -->
                <form action="{{ route('admin.settings.whatsapp.credentials.update') }}" method="POST" class="mb-4 bg-white/95 rounded-xl p-3 border border-amber-200 shadow-sm space-y-2">
                    @csrf
                    <input type="hidden" name="provider" value="fonnte">
                    <div class="flex items-center justify-between">
                        <label class="block text-[11px] font-bold text-gray-700 flex items-center gap-1.5">
                            <i class="fas fa-key text-amber-500"></i> API Token Fonnte:
                        </label>
                        <span class="text-[10px] text-amber-700 bg-amber-100 font-bold px-2 py-0.5 rounded-full">
                            ⚠️ Belum Diisi
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <input type="password" name="api_token" placeholder="Paste API Token dari fonnte.com..." class="w-full px-3 py-1.5 rounded-lg border border-gray-300 text-xs font-mono focus:outline-none focus:border-emerald-500 bg-white" required>
                        <button type="submit" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold shadow-sm transition whitespace-nowrap">
                            <i class="fas fa-save"></i> Simpan & Kunci
                        </button>
                    </div>
                    <p class="text-[10px] text-gray-400">Token didapat dari dashboard <a href="https://fonnte.com" target="_blank" class="text-emerald-600 underline font-semibold">fonnte.com</a> setelah scan QR.</p>
                </form>
                @endif

                @if($isFonnteActive && !empty($accountInfo['success']) && !empty($accountInfo['data']['device']))
                <div class="mb-4 bg-emerald-50/90 rounded-xl p-3 border border-emerald-200 space-y-1.5 text-xs text-emerald-900">
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-700 font-medium">Perangkat:</span>
                        <span class="font-bold">{{ $accountInfo['data']['name'] ?? '' }} ({{ $accountInfo['data']['device'] ?? '' }})</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-700 font-medium">Sisa Kuota Pesan:</span>
                        <span class="font-bold bg-emerald-200/70 text-emerald-900 px-2 py-0.5 rounded-md">{{ $accountInfo['data']['quota'] ?? '0' }} Pesan ({{ $accountInfo['data']['package'] ?? 'Paket' }})</span>
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
                        <button type="submit" onclick="return confirm('Beralih ke provider Fonnte?')" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-toggle-on"></i> Aktifkan Fonnte
                        </button>
                    </form>
                    @endif
                    <a href="https://fonnte.com" target="_blank" class="{{ $isFonnteActive ? 'w-full' : 'px-4' }} py-2 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-external-link-alt"></i> Dashboard Fonnte
                    </a>
                </div>
            </div>

            <!-- Card 2: Self-Hosted Baileys -->
            @php $isBaileysActive = ($currentProvider === 'selfhosted'); @endphp
            <div class="relative rounded-2xl p-5 border-2 transition-all {{ $isBaileysActive ? 'border-emerald-500 bg-emerald-50/30 shadow-md shadow-emerald-500/10' : 'border-gray-200 bg-gray-50/50 opacity-90 hover:opacity-100' }}">
                @if($isBaileysActive)
                <div class="absolute -top-3 right-4 px-3 py-0.5 bg-emerald-600 text-white rounded-full text-[11px] font-bold shadow-sm">
                    ✓ SEDANG DIGUNAKAN
                </div>
                @endif
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl {{ $isBaileysActive ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-600' }} flex items-center justify-center font-bold text-base">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Self-Hosted Baileys</h4>
                            <p class="text-[11px] text-gray-500">$0 Cost • Node.js Server Lokal/VPS</p>
                        </div>
                    </div>
                </div>

                <!-- Endpoint Baileys: Protected View -->
                <div x-data="{ editing: false }" class="mb-4 bg-white/95 rounded-xl p-3 border border-gray-200 shadow-sm space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-[11px] font-bold text-gray-700 flex items-center gap-1.5">
                            <i class="fas fa-link text-blue-500"></i> Baileys Server URL:
                        </label>
                        <span class="text-[10px] {{ $isBaileysActive && $isConnected ? 'text-emerald-700 bg-emerald-100 border-emerald-200' : 'text-gray-600 bg-gray-100 border-gray-200' }} font-bold px-2 py-0.5 rounded-full border">
                            {{ $isBaileysActive ? ($isConnected ? '✓ Ready' : '🟡 Standby') : 'Tersimpan' }}
                        </span>
                    </div>

                    <!-- Locked URL Display -->
                    <div x-show="!editing" class="flex items-center justify-between bg-gray-50 border border-gray-200/80 rounded-lg px-3 py-1.5">
                        <div class="flex items-center gap-2 truncate">
                            <i class="fas fa-network-wired text-blue-500 text-xs"></i>
                            <code class="font-mono text-xs text-gray-700 truncate">{{ $providersInfo['selfhosted']['api_url'] ?? 'http://localhost:3000' }}</code>
                        </div>
                        <button type="button" @click="editing = true" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-900 text-white rounded text-[11px] font-bold transition flex items-center gap-1 shadow-sm whitespace-nowrap ml-2">
                            <i class="fas fa-edit text-[10px]"></i> Ubah
                        </button>
                    </div>

                    <!-- Form Edit URL Baileys -->
                    <form x-show="editing" style="display: none;" action="{{ route('admin.settings.whatsapp.credentials.update') }}" method="POST" class="space-y-2 pt-1">
                        @csrf
                        <input type="hidden" name="provider" value="selfhosted">
                        <div class="flex gap-2">
                            <input type="text" name="api_url" value="{{ $providersInfo['selfhosted']['api_url'] ?? 'http://localhost:3000' }}" placeholder="http://localhost:3000" class="w-full px-3 py-1.5 rounded-lg border border-blue-400 text-xs font-mono focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white" required>
                            <button type="submit" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold shadow-sm transition whitespace-nowrap">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <button type="button" @click="editing = false" class="px-2.5 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-xs font-bold transition">
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
                        <button type="submit" onclick="return confirm('Beralih ke provider Self-Hosted Baileys? Pastikan engine Node.js sudah berjalan.')" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-toggle-on"></i> Aktifkan Baileys
                        </button>
                    </form>
                    @endif
                    <a href="{{ $providersInfo['selfhosted']['api_url'] ?? 'http://localhost:3000' }}/qr" target="_blank" class="{{ $isBaileysActive ? 'w-full' : 'px-3' }} py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-qrcode"></i> Scan QR Baileys
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid Container: Automation Toggles & Live Test Form -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Form Saklar Master Otomatisasi (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-toggle-on text-emerald-600"></i> Daftar Otomatisasi Pengiriman Pesan
                    </h2>
                    <p class="text-xs text-gray-500">Aktifkan atau nonaktifkan pengiriman WhatsApp otomatis per modul</p>
                </div>
            </div>

            <form action="{{ route('admin.settings.whatsapp.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="divide-y divide-gray-100 border border-gray-100 rounded-xl overflow-hidden">
                    @foreach($settings as $key => $item)
                    <div class="p-4 flex items-center justify-between hover:bg-gray-50/80 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">
                                @if(str_contains($key, 'attendance'))
                                    <i class="fas fa-user-check"></i>
                                @elseif(str_contains($key, 'payment'))
                                    <i class="fas fa-receipt"></i>
                                @elseif(str_contains($key, 'grade'))
                                    <i class="fas fa-graduation-cap"></i>
                                @elseif(str_contains($key, 'counseling'))
                                    <i class="fas fa-user-shield"></i>
                                @elseif(str_contains($key, 'award'))
                                    <i class="fas fa-trophy"></i>
                                @elseif(str_contains($key, 'psb'))
                                    <i class="fas fa-user-plus"></i>
                                @else
                                    <i class="fas fa-bell"></i>
                                @endif
                            </div>
                            <div>
                                <label for="{{ $key }}" class="text-sm font-semibold text-gray-900 cursor-pointer block">
                                    {{ $item['label'] }}
                                </label>
                                <span class="text-[11px] text-gray-400 font-mono">{{ $key }}</span>
                            </div>
                        </div>

                        <!-- Toggle Switch -->
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="{{ $key }}" name="{{ $key }}" value="1" class="sr-only peer" {{ $item['enabled'] ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>
                    @endforeach
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Pengaturan Otomatisasi
                    </button>
                </div>
            </form>
        </div>

        <!-- Form Live Test Sender (1 Col) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between space-y-4">
            <div>
                <div class="border-b border-gray-100 pb-4 mb-4">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-paper-plane text-emerald-600"></i> Uji Coba Pengiriman WA
                    </h2>
                    <p class="text-xs text-gray-500">Tes pengiriman pesan instan ke nomor WhatsApp penguji</p>
                </div>

                <form action="{{ route('admin.settings.whatsapp.test') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Nomor WhatsApp HP Tujuan:</label>
                        <input type="text" name="phone" required placeholder="081234567890" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-emerald-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Pesan Uji Coba:</label>
                        <textarea name="message" rows="4" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-emerald-500">📢 *UJI COBA GATEWAY WHATSAPP PEMBDAHUB ({{ strtoupper($providerLabel ?? 'GATEWAY') }})*

Halo! Ini adalah pesan tes pengiriman WhatsApp dari Admin Panel PembdaHUB. Gateway terhubung dan bekerja dengan baik!</textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i> Kirim Pesan Tes
                    </button>
                </form>
            </div>

            <!-- Form Uji Coba Laporan Eksekutif -->
            <div class="pt-4 border-t border-gray-100">
                <div class="border-b border-gray-100 pb-3 mb-3">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-user-tie text-emerald-600"></i> Uji Coba Laporan Eksekutif WA
                    </h3>
                    <p class="text-[11px] text-gray-500">Tes pengiriman rekap berita ke Kepsek & Wali Kelas</p>
                </div>

                <form action="{{ route('admin.settings.whatsapp.digest.test') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Pilih Jenis Laporan Eksekutif:</label>
                        <select name="digest_type" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-emerald-500 bg-white">
                            <option value="principal_attendance">📊 Kepsek: Rekap Absensi Harian (07:45 WIB)</option>
                            <option value="homeroom_attendance">👩‍🏫 Wali Kelas: Rekap Absensi Harian Kelas</option>
                            <option value="principal_spp">💰 Kepsek: Rekap SPP & Tunggakan Bulanan</option>
                            <option value="homeroom_spp">💳 Wali Kelas: Rekap SPP Siswa Kelas Binaan</option>
                            <option value="principal_lms">📚 Kepsek: Rekap LMS Guru & Ranking Mingguan</option>
                            <option value="homeroom_lms">✏️ Wali Kelas: Rekap LMS Siswa & Ranking</option>
                            <option value="points_weekly">⭐ Siswa: Rekap Poin Prestasi Mingguan (Setiap Sabtu)</option>
                            <option value="award_sample">🏆 Realtime: Notifikasi Prestasi Siswa</option>
                            <option value="edaran_sample">📜 Realtime: Notifikasi Surat Edaran Yayasan</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-bold text-xs shadow transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-play"></i> Eksekusi Laporan Tes WA
                    </button>
                </form>
            </div>

            <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100 text-xs text-emerald-800 space-y-1">
                <p class="font-bold flex items-center gap-1">
                    <i class="fas fa-shield-alt"></i> Keamanan & Anti-Spam:
                </p>
                <p class="leading-relaxed text-[11px] text-emerald-700">
                    Sistem menggunakan antrean (*Queue*) otomatis untuk memastikan pesan dikirim dengan jeda aman antar nomor.
                </p>
            </div>
        </div>

    </div>
</div>
@endsection
