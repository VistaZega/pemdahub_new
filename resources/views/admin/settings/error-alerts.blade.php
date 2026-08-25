@extends('layouts.admin')

@section('title', 'Notifikasi Error & Monitoring Sistem')

@section('content')
<div class="space-y-6">
    <!-- Header & Breadcrumb -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 shadow-lg shadow-rose-500/20">
                <i class="fas fa-bell text-white text-3xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Laporan & Notifikasi Error ke Super Admin</h1>
                <p class="text-sm text-gray-500">Pemantau otomatis kegagalan sistem (500 Error, Query Exception) langsung ke WhatsApp dan Telegram Super Admin</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.settings.whatsapp') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2">
                <i class="fab fa-whatsapp"></i> Gateway WhatsApp
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

    <!-- Form Pengaturan Notifikasi Error -->
    <form action="{{ route('admin.settings.error_alerts.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Master Switch & Cooldown Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-7">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 border-b border-gray-100 pb-6 mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-3.5 h-3.5 rounded-full {{ $alertConfig['enabled'] ? 'bg-rose-500 animate-pulse' : 'bg-gray-400' }}"></div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Saklar Utama Pemantau Error Sistem</h3>
                        <p class="text-xs text-gray-500 mt-1">Jika aktif, setiap terjadi error server kritis 500 akan otomatis dikirimkan laporannya ke saluran yang aktif</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="error_alerts_enabled" value="1" class="sr-only peer" {{ $alertConfig['enabled'] ? 'checked' : '' }}>
                        <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-rose-600"></div>
                    </label>
                    <span class="text-xs font-bold tracking-wide {{ $alertConfig['enabled'] ? 'text-rose-600' : 'text-gray-400' }}">
                        {{ $alertConfig['enabled'] ? 'AKTIF (MONITORING ON)' : 'NONAKTIF' }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-center bg-gray-50/80 p-5 rounded-2xl border border-gray-200/70 text-xs">
                <div>
                    <label class="font-bold text-gray-800 flex items-center gap-2 mb-1.5 text-xs">
                        <i class="fas fa-clock text-amber-500"></i> Jeda Waktu Anti-Spam (Cooldown):
                    </label>
                    <p class="text-gray-500 text-[11px] leading-relaxed">Mencegah banjir notifikasi saat error yang sama terjadi berulang kali dalam waktu singkat.</p>
                </div>
                <div class="flex items-center gap-3 justify-start md:justify-end">
                    <input type="number" name="error_alert_cooldown_minutes" value="{{ $alertConfig['cooldown_minutes'] }}" min="1" max="60" class="w-24 px-4 py-2.5 bg-white rounded-xl border border-gray-300 font-bold text-sm text-center focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 shadow-sm">
                    <span class="font-semibold text-gray-700">Menit per Error Signature</span>
                </div>
            </div>
        </div>

        <!-- 2 Saluran Pengiriman: WhatsApp & Telegram -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Saluran 1: WhatsApp Super Admin -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-7 space-y-6">
                <div class="flex items-center justify-between border-b border-gray-100 pb-5">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-2xl shadow-sm shadow-emerald-500/10">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Saluran WhatsApp Super Admin</h3>
                            <p class="text-[11px] text-gray-500 mt-0.5">Kirim laporan error instan ke nomor WhatsApp Super Admin</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="wa_alert_enabled" value="1" class="sr-only peer" {{ $alertConfig['whatsapp']['enabled'] ? 'checked' : '' }}>
                        <div class="w-12 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                    </label>
                </div>

                <div class="space-y-4">
                    <!-- Deteksi Otomatis Akun Super Admin dari Database -->
                    <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200/80 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-emerald-950 flex items-center gap-2">
                                <i class="fas fa-users-cog text-emerald-600"></i> Akun Super Admin Terdeteksi (Otomatis):
                            </span>
                            <span class="text-[10px] font-bold text-emerald-800 bg-emerald-200/80 px-2.5 py-0.5 rounded-full">
                                {{ count($alertConfig['super_admin_recipients']) }} Akun Aktif
                            </span>
                        </div>

                        <div class="space-y-2">
                            @forelse($alertConfig['super_admin_recipients'] as $sa)
                            <div class="flex items-center justify-between p-2.5 bg-white/95 rounded-xl border border-emerald-100 shadow-sm text-xs">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-xs">
                                        <i class="fas fa-user-shield text-[11px]"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 leading-tight">{{ $sa['name'] }}</p>
                                        <p class="text-[10px] text-gray-400 font-mono">{{ $sa['username'] }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if($sa['has_phone'])
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-lg font-mono font-bold text-[11px]">
                                        <i class="fab fa-whatsapp text-emerald-600"></i> {{ $sa['phone'] }}
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-800 rounded text-[10px] font-semibold">
                                        ⚠️ Belum ada No HP
                                    </span>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <p class="text-xs text-amber-700 italic">Tidak ditemukan akun Super Admin aktif di database.</p>
                            @endforelse
                        </div>

                        <p class="text-[11px] text-emerald-800/80 leading-relaxed">
                            💡 <b>Otomatis:</b> Sistem akan mengirimkan laporan error ke nomor WhatsApp seluruh akun Super Admin di atas tanpa perlu ketik manual.
                        </p>
                    </div>

                    <!-- Input Nomor Tambahan / Custom Override (Opsional) -->
                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-2 flex items-center gap-2">
                            <i class="fas fa-plus-circle text-gray-500"></i> Nomor WhatsApp Tambahan / Tim Luar (Opsional):
                        </label>
                        <input type="text" name="wa_alert_phone" value="{{ $alertConfig['whatsapp']['admin_phone'] }}" placeholder="Opsional: 08... (Isi jika ingin kirim salinan ke nomor tambahan)" class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 font-mono shadow-sm">
                        <p class="text-[11px] text-gray-400 mt-1.5 leading-relaxed">Kosongkan jika hanya ingin mengirimkan laporan ke akun Super Admin yang terdaftar di sistem.</p>
                    </div>

                    <div class="p-4 bg-gray-50/80 rounded-2xl border border-gray-100 text-xs text-gray-600 space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 font-medium">Gateway Pengirim Aktif:</span>
                            <span class="font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-100">{{ $waService->getProviderLabel() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 font-medium">Status Gateway:</span>
                            <span class="{{ $waService->isEnabled() ? 'text-emerald-600 font-bold' : 'text-amber-600 font-bold' }}">
                                {{ $waService->isEnabled() ? '✓ Siap Mengirim' : '⚠️ Gateway Nonaktif' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Saluran 2: Telegram Bot Super Admin -->
            <div x-data="{ showToken: false }" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-7 space-y-6">
                <div class="flex items-center justify-between border-b border-gray-100 pb-5">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-sky-100 text-sky-600 flex items-center justify-center font-bold text-2xl shadow-sm shadow-sky-500/10">
                            <i class="fab fa-telegram-plane"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Saluran Telegram Bot</h3>
                            <p class="text-[11px] text-gray-500 mt-0.5">Kirim laporan error ke Chat ID pribadi / grup teknisi</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="telegram_alert_enabled" value="1" class="sr-only peer" {{ $alertConfig['telegram']['enabled'] ? 'checked' : '' }}>
                        <div class="w-12 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-sky-600"></div>
                    </label>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-2 flex items-center gap-2">
                            <i class="fas fa-robot text-sky-600"></i> Telegram Bot Token:
                        </label>
                        <div class="flex gap-2.5">
                            <input :type="showToken ? 'text' : 'password'" name="telegram_alert_bot_token" value="{{ $alertConfig['telegram']['bot_token'] }}" placeholder="123456789:ABCdefGHIjklMNOpqr..." class="w-full px-4 py-3 rounded-xl border border-gray-300 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 font-mono shadow-sm">
                            <button type="button" @click="showToken = !showToken" class="px-4 py-3 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-xl text-sm text-gray-600 font-semibold transition flex items-center justify-center shadow-sm" title="Lihat/Sembunyikan">
                                <i class="fas" :class="showToken ? 'fa-eye-slash text-rose-500' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-2 flex items-center gap-2">
                            <i class="fas fa-comments text-sky-600"></i> Telegram Chat ID (Pribadi / Grup):
                        </label>
                        <input type="text" name="telegram_alert_chat_id" value="{{ $alertConfig['telegram']['chat_id'] }}" placeholder="Contoh: 123456789 atau -100123456789" class="w-full px-4 py-3 rounded-xl border border-gray-300 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 font-mono shadow-sm">
                        <p class="text-[11px] text-gray-400 mt-1.5">Chat ID didapat dari bot Telegram atau @userinfobot.</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Tombol Simpan Konfigurasi -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-3 border-t border-gray-100">
            <p class="text-xs text-gray-500 flex items-center gap-2">
                <i class="fas fa-shield-alt text-rose-600 text-sm"></i> Error 404, validasi form, dan token CSRF kedaluwarsa <b>otomatis diabaikan</b> agar tidak mengganggu.
            </p>
            <button type="submit" class="w-full sm:w-auto px-8 py-3.5 bg-slate-900 hover:bg-black text-white rounded-xl font-bold text-sm shadow-lg shadow-slate-900/10 hover:shadow-slate-900/20 transition-all flex items-center justify-center gap-3">
                <i class="fas fa-save text-base"></i>
                <span>Simpan Konfigurasi Notifikasi Error</span>
            </button>
        </div>
    </form>

    <!-- Bar Uji Coba Pengiriman Notifikasi (Live Testing) -->
    <div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-2xl p-6 sm:p-7 text-white space-y-5 shadow-lg shadow-slate-900/10">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div class="space-y-1">
                <h3 class="text-base font-bold flex items-center gap-2.5 text-white">
                    <i class="fas fa-vial text-amber-400 text-lg"></i> Uji Coba Pengiriman Laporan Error
                </h3>
                <p class="text-xs text-slate-300 leading-relaxed">Kirim simulasi laporan error secara instan untuk memastikan nomor WA dan Telegram Anda berhasil menerima notifikasi</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <form action="{{ route('admin.settings.error_alerts.test') }}" method="POST" class="inline-block">
                    @csrf
                    <input type="hidden" name="channel" value="whatsapp">
                    <button type="submit" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-2.5 shadow-md shadow-emerald-600/20">
                        <i class="fab fa-whatsapp text-sm"></i>
                        <span>Tes WhatsApp Saja</span>
                    </button>
                </form>

                <form action="{{ route('admin.settings.error_alerts.test') }}" method="POST" class="inline-block">
                    @csrf
                    <input type="hidden" name="channel" value="telegram">
                    <button type="submit" class="px-5 py-3 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-2.5 shadow-md shadow-sky-600/20">
                        <i class="fab fa-telegram-plane text-sm"></i>
                        <span>Tes Telegram Saja</span>
                    </button>
                </form>

                <form action="{{ route('admin.settings.error_alerts.test') }}" method="POST" class="inline-block">
                    @csrf
                    <input type="hidden" name="channel" value="all">
                    <button type="submit" class="px-5 py-3 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-xl text-xs font-bold transition flex items-center gap-2.5 shadow-md shadow-amber-500/20">
                        <i class="fas fa-paper-plane text-sm"></i>
                        <span>Tes Keduanya (Semua Saluran)</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Riwayat Error Terakhir (Live Error Logs Viewer) -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
            <div>
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-clipboard-list text-rose-600"></i> Riwayat Log Error Sistem Terkini (Live Logs)
                </h3>
                <p class="text-xs text-gray-500">Merekam riwayat exception kritis terakhir yang tercatat di server storage PembdaHUB</p>
            </div>
            <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-mono font-bold">
                {{ count($recentLogs) }} Log Terkini
            </span>
        </div>

        @if(empty($recentLogs))
        <div class="p-8 text-center bg-emerald-50/50 rounded-xl border border-emerald-100 space-y-2">
            <i class="fas fa-check-circle text-emerald-500 text-3xl"></i>
            <h4 class="text-sm font-bold text-emerald-900">Sistem Berjalan Mulus Tanpa Error Kritis</h4>
            <p class="text-xs text-emerald-700">Tidak ada catatan exception kritis atau database failure di log server saat ini.</p>
        </div>
        @else
        <div class="divide-y divide-gray-100 border border-gray-100 rounded-xl overflow-hidden text-xs">
            @foreach($recentLogs as $log)
            <div x-data="{ expanded: false }" class="p-4 hover:bg-gray-50 transition">
                <div class="flex items-start justify-between gap-3 cursor-pointer" @click="expanded = !expanded">
                    <div class="space-y-1 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono {{ $log['level'] === 'CRITICAL' || $log['level'] === 'EMERGENCY' ? 'bg-rose-100 text-rose-700 border border-rose-200' : 'bg-amber-100 text-amber-800 border border-amber-200' }}">
                                {{ $log['level'] }}
                            </span>
                            <span class="font-mono text-gray-400 text-[11px]">{{ $log['timestamp'] }}</span>
                            <span class="text-gray-400">•</span>
                            <span class="text-gray-500 font-mono text-[10px] uppercase">Env: {{ $log['environment'] }}</span>
                        </div>
                        <p class="font-mono font-semibold text-gray-900 leading-snug break-all">{{ $log['short_message'] }}</p>
                    </div>
                    <button type="button" class="text-gray-400 hover:text-gray-600 text-xs px-2 py-1 bg-gray-100 rounded">
                        <span x-text="expanded ? 'Tutup Trace' : 'Lihat Detail'"></span>
                    </button>
                </div>
                <div x-show="expanded" style="display: none;" class="mt-3 p-3 bg-slate-900 text-slate-200 rounded-xl font-mono text-[11px] overflow-x-auto whitespace-pre-wrap leading-relaxed">
                    {{ $log['full_message'] }}
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
