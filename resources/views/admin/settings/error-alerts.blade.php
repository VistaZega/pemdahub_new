@extends('layouts.admin')

@section('title', 'Pusat Diagnostik & Monitoring Error Sistem - PembdaHUB')

@section('content')
<div class="space-y-6" x-data="{ copyFeedback: false }">
    
    <!-- Top Tactile Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-6 rounded-2xl border-[1.5px] border-slate-900 shadow-[4px_4px_0px_#0f172a]">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-rose-600 border-[1.5px] border-slate-900 shadow-[3px_3px_0px_#0f172a] text-white flex items-center justify-center text-2xl flex-shrink-0">
                <i class="fas fa-shield-virus"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-300">
                        SYSTEM HEALTH & DIAGNOSTICS
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300">
                        LIVE MONITOR
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 mt-1">Pusat Verifikasi Error & Monitoring Sistem</h1>
                <p class="text-xs text-slate-500 font-medium mt-0.5">Pemantau kesehatan kode, diagnosa kendala otomatis, dan verifikasi status perbaikan</p>
            </div>
        </div>

        <!-- Action Links -->
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.settings.whatsapp') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 border-[1.5px] border-slate-900 shadow-[2px_2px_0px_#0f172a]">
                <i class="fab fa-whatsapp"></i> Gateway WA
            </a>
            <a href="{{ route('admin.settings.whatsapp.templates') }}" class="px-4 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 border-[1.5px] border-slate-900 shadow-[2px_2px_0px_#0f172a]">
                <i class="fas fa-comment-dots"></i> Template Pesan
            </a>
            <a href="{{ route('admin.settings.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-2 border border-slate-300">
                <i class="fas fa-arrow-left"></i> Kembali
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

    <!-- PANEL 1: LIVE HEALTH SHIELD & FIXES VERIFICATION (INDIKATOR HIJAU) -->
    <div class="bg-white rounded-2xl border-[1.5px] border-slate-900 shadow-[4px_4px_0px_#0f172a] p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="fas fa-clipboard-check text-emerald-600 text-lg"></i> Status Verifikasi Perbaikan Error Sistem
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Pengujian otomatis komponen codebase dan database untuk memastikan bug yang dilaporkan sudah tuntas teratasi</p>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-mono font-black border {{ $allPassed ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-amber-100 text-amber-800 border-amber-300' }}">
                    {{ $allPassed ? '● ' . count($healthChecks) . '/' . count($healthChecks) . ' PERBAIKAN TERVERIFIKASI LULUS' : '○ SEBAGIAN BUTUH PERHATIAN' }}
                </span>
                <a href="{{ route('admin.settings.error_alerts') }}" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold border border-slate-300 transition flex items-center gap-1.5" title="Muat ulang pengetesan">
                    <i class="fas fa-sync-alt text-[10px]"></i> Verifikasi Ulang
                </a>
            </div>
        </div>

        <!-- 6 Verification Checks Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($healthChecks as $check)
            <div class="p-4 rounded-2xl border-2 transition-all {{ $check['passed'] ? 'bg-emerald-50/50 border-emerald-300 shadow-xs' : 'bg-amber-50/60 border-amber-300' }} flex flex-col justify-between space-y-2.5">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-1.5">
                        <span class="text-xs font-black text-slate-900 flex items-center gap-1.5 truncate">
                            <i class="fas {{ $check['passed'] ? 'fa-check-circle text-emerald-600' : 'fa-exclamation-triangle text-amber-500' }}"></i>
                            {{ $check['title'] }}
                        </span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-mono font-black border whitespace-nowrap {{ $check['passed'] ? 'bg-emerald-200 text-emerald-900 border-emerald-400' : 'bg-amber-200 text-amber-900 border-amber-400' }}">
                            {{ $check['badge'] }}
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-600 leading-relaxed">{{ $check['detail'] }}</p>
                </div>

                <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-[10px] text-slate-400 font-mono">
                    <span>Status: {{ $check['passed'] ? 'RESOLVED' : 'WARNING' }}</span>
                    <span class="text-emerald-700 font-bold">● Terverifikasi</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- PANEL 2: FORM PENGATURAN NOTIFIKASI ERROR (WHATSAPP & TELEGRAM) -->
    <form action="{{ route('admin.settings.error_alerts.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Master Switch & Cooldown Card -->
        <div class="bg-white rounded-2xl border-[1.5px] border-slate-900 shadow-[4px_4px_0px_#0f172a] p-6 space-y-5">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-lg border border-rose-200">
                        <i class="fas fa-power-off"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">Saklar Utama Pemantau Error Realtime</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Kirimkan otomatis laporan diagnostik berbahasa Indonesia saat terjadi kendala pada sistem</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="error_alerts_enabled" value="1" class="sr-only peer" {{ $alertConfig['enabled'] ? 'checked' : '' }}>
                        <div class="w-12 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-600"></div>
                    </label>
                    <span class="text-xs font-black {{ $alertConfig['enabled'] ? 'text-rose-600' : 'text-slate-400' }}">
                        {{ $alertConfig['enabled'] ? 'MONITORING AKTIF' : 'NONAKTIF' }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs">
                <div>
                    <label class="font-bold text-slate-800 flex items-center gap-2 mb-1 text-xs">
                        <i class="fas fa-clock text-amber-500"></i> Jeda Waktu Anti-Spam (Cooldown):
                    </label>
                    <p class="text-slate-500 text-[11px] leading-relaxed">Mencegah banjir pesan saat error yang sama terjadi berulang dalam waktu singkat.</p>
                </div>
                <div class="flex items-center gap-3 justify-start md:justify-end">
                    <input type="number" name="error_alert_cooldown_minutes" value="{{ $alertConfig['cooldown_minutes'] }}" min="1" max="60" class="w-20 px-3 py-2 bg-white rounded-xl border border-slate-300 font-bold text-sm text-center focus:outline-none focus:border-slate-900 shadow-xs">
                    <span class="font-bold text-slate-700">Menit per Error</span>
                </div>
            </div>
        </div>

        <!-- Saluran Pengiriman: WhatsApp Super Admin (Eksklusif) -->
        <div class="bg-white rounded-2xl border-[1.5px] border-slate-900 shadow-[4px_4px_0px_#0f172a] p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-2xl border border-emerald-300">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">Saluran WhatsApp Super Admin (Eksklusif)</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Laporan diagnostik otomatis terkirim langsung ke nomor WhatsApp Super Admin</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="wa_alert_enabled" value="1" class="sr-only peer" {{ $alertConfig['whatsapp']['enabled'] ? 'checked' : '' }}>
                        <div class="w-12 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                    </label>
                    <span class="text-xs font-black {{ $alertConfig['whatsapp']['enabled'] ? 'text-emerald-700' : 'text-slate-400' }}">
                        {{ $alertConfig['whatsapp']['enabled'] ? 'NOTIFIKASI WA AKTIF' : 'NONAKTIF' }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-start">
                <div class="p-4 bg-emerald-50/80 border border-emerald-200 rounded-2xl space-y-2.5 text-xs text-emerald-950">
                    <div class="flex items-center justify-between">
                        <span class="font-black text-emerald-900 flex items-center gap-1.5">
                            <i class="fas fa-user-shield text-emerald-600"></i> Akun Super Admin Penerima:
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-200 text-emerald-900">
                            {{ count($alertConfig['super_admin_recipients'] ?? []) }} Akun Terdeteksi
                        </span>
                    </div>
                    <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                        @forelse($alertConfig['super_admin_recipients'] ?? [] as $sa)
                        <div class="flex items-center justify-between p-2 bg-white rounded-xl border border-emerald-100 text-[11px]">
                            <span class="font-bold text-slate-800">{{ $sa['name'] }}</span>
                            <span class="font-mono text-emerald-700 font-semibold">{{ $sa['phone'] ?? 'Belum ada No HP' }}</span>
                        </div>
                        @empty
                        <p class="text-slate-500 italic">Tidak ada user superadmin terdaftar.</p>
                        @endforelse
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1 flex items-center gap-1.5">
                            <i class="fas fa-phone text-emerald-600"></i> Nomor WhatsApp Tambahan (Opsional):
                        </label>
                        <input type="text" name="wa_alert_phone" value="{{ $alertConfig['whatsapp']['admin_phone'] ?? $alertConfig['whatsapp']['custom_phone'] ?? '' }}" placeholder="08xxxxxxxxxx (kosongkan jika cukup nomor Super Admin)" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-mono focus:outline-none focus:border-slate-900">
                        <p class="text-[10px] text-slate-400 mt-1">Nomor ini juga akan menerima salinan notifikasi error sistem jika diisi.</p>
                    </div>

                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">Gateway Pengirim Aktif:</span>
                        <span class="font-bold text-emerald-800 font-mono">{{ $waService->getProviderLabel() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Simpan Konfigurasi -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2">
            <p class="text-xs text-slate-500 flex items-center gap-2">
                <i class="fas fa-shield-alt text-emerald-600 text-sm"></i> Error 404, validasi form, dan sesi kedaluwarsa <b>otomatis diabaikan</b> agar notifikasi hanya mengirim isu penting.
            </p>
            <button type="submit" class="w-full sm:w-auto px-8 py-3.5 bg-slate-900 hover:bg-black text-white rounded-xl font-black text-sm border-[1.5px] border-slate-900 shadow-[3px_3px_0px_#0f172a] transition-all flex items-center justify-center gap-3">
                <i class="fas fa-save text-base"></i>
                <span>Simpan Konfigurasi Notifikasi Error</span>
            </button>
        </div>
    </form>

    <!-- LIVE TESTING SUITE (WHATSAPP ONLY) -->
    <div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-2xl p-6 text-white space-y-4 border-[1.5px] border-slate-900 shadow-[4px_4px_0px_#0f172a]">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <h3 class="text-base font-black flex items-center gap-2.5 text-white">
                    <i class="fab fa-whatsapp text-emerald-400 text-lg"></i> Uji Coba Pengiriman Laporan WhatsApp
                </h3>
                <p class="text-xs text-slate-300 leading-relaxed">Kirim simulasi laporan error berformat 3 Poin (Masalah → Dampak → Solusi) langsung ke nomor WhatsApp Super Admin</p>
            </div>
            <div>
                <form action="{{ route('admin.settings.error_alerts.test') }}" method="POST" class="inline-block">
                    @csrf
                    <input type="hidden" name="channel" value="whatsapp">
                    <button type="submit" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black transition flex items-center gap-2 shadow-[2px_2px_0px_#047857] cursor-pointer">
                        <i class="fab fa-whatsapp text-sm"></i>
                        <span>Kirim Uji Coba Alert ke WhatsApp</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- PANEL 3: LIVE ERROR LOGS VIEWER DENGAN 1-CLICK COPY TOOLS & STATUS PERBAIKAN -->
    <div class="bg-white rounded-2xl border-[1.5px] border-slate-900 shadow-[4px_4px_0px_#0f172a] p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="fas fa-clipboard-list text-rose-600"></i> Riwayat Log Error & Status Perbaikan (Live Logs)
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Catatan riwayat exception server lengkap dengan diagnosa bahasa Indonesia dan tombol salin 1-klik</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                @if(!empty($recentLogs))
                <button type="button" 
                        onclick="copyAllRecentLogs(this)" 
                        data-all-logs="{{ json_encode($recentLogs) }}"
                        style="padding: 9px 20px !important;"
                        class="bg-slate-900 hover:bg-black text-white rounded-xl text-xs font-bold transition inline-flex items-center gap-2.5 cursor-pointer shadow-xs whitespace-nowrap shrink-0 active:scale-95">
                    <i class="fas fa-copy text-amber-400"></i>
                    <span>Salin Semua Log</span>
                </button>

                <form action="{{ route('admin.settings.error_alerts.clear') }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan riwayat log lama?')">
                    @csrf
                    <button type="submit" 
                            style="padding: 9px 20px !important;"
                            class="bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 rounded-xl text-xs font-bold transition inline-flex items-center gap-2.5 cursor-pointer shadow-xs whitespace-nowrap shrink-0 active:scale-95">
                        <i class="fas fa-trash-alt text-rose-600"></i>
                        <span>Bersihkan Log</span>
                    </button>
                </form>
                @endif

                <span style="padding: 9px 18px !important;"
                      class="bg-slate-100 text-slate-700 rounded-xl text-xs font-mono font-bold border border-slate-200 whitespace-nowrap shrink-0 inline-flex items-center">
                    {{ count($recentLogs) }} Log
                </span>
            </div>
        </div>

        @if(empty($recentLogs))
        <div class="p-8 text-center bg-emerald-50/50 rounded-2xl border-2 border-dashed border-emerald-300 space-y-2">
            <i class="fas fa-shield-check text-emerald-500 text-4xl mb-1"></i>
            <h4 class="text-sm font-black text-emerald-950">Sistem Berjalan 100% Mulus & Sehat</h4>
            <p class="text-xs text-emerald-800">Tidak ada catatan exception kritis baru di server storage PembdaHUB.</p>
        </div>
        @else
        <div class="space-y-3.5 text-xs">
            @foreach($recentLogs as $index => $log)
            @php
                $diag = \App\Services\ErrorDiagnosticService::diagnose($log['full_message']);
                $resolution = \App\Services\ErrorDiagnosticService::isErrorResolved($log['full_message']);
                
                $copyPayload = "📋 [LAPORAN KENDALA PEMBDAHUB - LOG #" . ($index+1) . "]\n" .
                    "━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
                    "⏰ Waktu: " . $log['timestamp'] . " (" . $log['level'] . ")\n" .
                    "🏷️ Kategori: " . strip_tags($diag['type']) . "\n" .
                    "📊 Status: " . strip_tags($resolution['status_badge']) . "\n\n" .
                    "🛑 MASALAH:\n" . strip_tags($diag['problem']) . "\n\n" .
                    "⚠️ DAMPAK:\n" . strip_tags($diag['impact']) . "\n\n" .
                    "🛠️ SOLUSI / CATATAN:\n" . strip_tags($diag['solution']) . "\n\n" .
                    "━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
                    "💻 KODE RAW EXCEPTION:\n" . strip_tags($log['full_message']);
            @endphp

            <div x-data="{ expanded: false }" class="p-4 rounded-2xl border-2 transition-all bg-white hover:border-slate-400 {{ $resolution['resolved'] ? 'border-emerald-300/80 bg-emerald-50/10' : 'border-slate-200' }} shadow-xs space-y-3">
                
                <!-- Card Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span style="padding: 3px 10px !important;" class="rounded-md text-[10px] font-mono font-black border whitespace-nowrap {{ $log['level'] === 'CRITICAL' || $log['level'] === 'EMERGENCY' ? 'bg-rose-100 text-rose-700 border-rose-200' : 'bg-amber-100 text-amber-800 border-amber-200' }}">
                            {{ $log['level'] }}
                        </span>
                        
                        <!-- Status Perbaikan Indikator Hijau -->
                        <span style="padding: 3px 12px !important;" class="rounded-full text-[10px] font-black border whitespace-nowrap {{ $resolution['bg_class'] }}">
                            {{ $resolution['status_badge'] }}
                        </span>

                        <span class="font-mono text-slate-400 text-[11px]">{{ $log['timestamp'] }}</span>
                    </div>

                    <!-- 1-Click Copy Button -->
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <button type="button" 
                                onclick="copyLogCard(this)" 
                                data-copy="{{ $copyPayload }}"
                                style="padding: 7px 16px !important;"
                                class="bg-slate-900 hover:bg-black text-white rounded-xl text-[11px] font-bold transition inline-flex items-center gap-2 shadow-xs cursor-pointer active:scale-95 whitespace-nowrap shrink-0">
                            <i class="fas fa-copy text-amber-400"></i>
                            <span>Salin Kode Error</span>
                        </button>

                        <button type="button" @click="expanded = !expanded" 
                                style="padding: 7px 16px !important;"
                                class="bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-[11px] font-bold transition inline-flex items-center gap-2 whitespace-nowrap shrink-0">
                            <span x-text="expanded ? 'Tutup Trace' : 'Lihat Trace'"></span>
                            <i class="fas" :class="expanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        </button>
                    </div>
                </div>

                <!-- Structured Diagnostic Box -->
                <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200/80 space-y-2 text-[11px] text-slate-700">
                    <div class="flex items-start gap-2">
                        <span class="font-bold text-slate-900 min-w-[70px]">🛑 Masalah:</span>
                        <span class="text-slate-800 leading-relaxed font-medium">{!! $diag['problem'] !!}</span>
                    </div>
                    
                    <div class="flex items-start gap-2">
                        <span class="font-bold text-slate-900 min-w-[70px]">⚠️ Dampak:</span>
                        <span class="leading-relaxed"><span class="font-bold text-emerald-800">{{ $diag['danger_label'] }}</span> — {!! $diag['impact'] !!}</span>
                    </div>

                    <div class="flex items-start gap-2 pt-1 border-t border-slate-200/60">
                        <span class="font-bold text-emerald-900 min-w-[70px]">🛠️ Status:</span>
                        <span class="text-emerald-950 font-semibold leading-relaxed">{{ $resolution['note'] }}</span>
                    </div>
                </div>

                <!-- Raw Error Collapse -->
                <div x-show="expanded" style="display: none;" class="p-3.5 bg-slate-950 text-slate-200 rounded-xl font-mono text-[11px] overflow-x-auto whitespace-pre-wrap leading-relaxed border border-slate-800 shadow-inner">
                    <div class="text-[10px] text-amber-400 font-bold mb-1">// RAW STACKTRACE DARI SERVER:</div>
                    {{ $log['full_message'] }}
                </div>

            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

<!-- 1-Click Copy JS Tools -->
<script>
    function copyLogCard(btn) {
        const text = btn.getAttribute('data-copy');
        executeClipboardCopy(text, btn, '<i class="fas fa-check text-emerald-400"></i> <span class="text-emerald-300">Tersalin!</span>');
    }

    function copyAllRecentLogs(btn) {
        try {
            const logs = JSON.parse(btn.getAttribute('data-all-logs'));
            let combined = "📋 [REKAPITULASI LOG ERROR PEMBDAHUB]\n";
            combined += "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            combined += "Total Log: " + logs.length + " entri\n";
            combined += "Waktu Ekspor: " + new Date().toLocaleString('id-ID') + "\n\n";

            logs.forEach((l, idx) => {
                combined += `--- LOG #${idx+1} [${l.timestamp}] [${l.level}] ---\n`;
                combined += `${l.full_message}\n\n`;
            });

            executeClipboardCopy(combined, btn, '<i class="fas fa-check text-emerald-400"></i> <span class="text-emerald-300">Semua Log Tersalin!</span>');
        } catch(e) {
            alert('Gagal menyalin semua log.');
        }
    }

    function executeClipboardCopy(text, btn, successHtml) {
        const originalHtml = btn.innerHTML;
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                btn.innerHTML = successHtml;
                btn.classList.add('bg-emerald-950');
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('bg-emerald-950');
                }, 2500);
            });
        } else {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            try {
                document.execCommand('copy');
                btn.innerHTML = successHtml;
                btn.classList.add('bg-emerald-950');
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('bg-emerald-950');
                }, 2500);
            } catch (err) {
                alert('Silakan pilih dan salin teks secara manual.');
            }
            document.body.removeChild(ta);
        }
    }
</script>
@endsection
