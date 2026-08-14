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
    @php
        $isConnected = !empty($accountInfo['success']) && ($accountInfo['data']['status'] ?? '') === 'connected';
    @endphp
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-100 pb-5 mb-5">
            <div class="flex items-center gap-3">
                <div class="w-3 h-3 rounded-full {{ $isConnected ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500' }}"></div>
                <div>
                    <h3 className="text-base font-bold text-gray-900">Status Server Gateway WhatsApp</h3>
                    <p class="text-xs text-gray-500">Provider: <span class="font-semibold text-emerald-600">Self-Hosted Baileys Engine ($0 Cost)</span></p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $isConnected ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $isConnected ? '🟢 TERHUBUNG (Online)' : '🟡 BELUM TERHUBUNG / PERLU SCAN QR' }}
                </span>
                <a href="http://localhost:3000/qr" target="_blank" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2">
                    <i class="fas fa-qrcode"></i> Buka QR Code Scanner
                </a>
            </div>
        </div>

        <p class="text-xs text-gray-500 leading-relaxed">
            <i class="fas fa-info-circle text-emerald-500 mr-1"></i>
            Pesan terkirim secara otomatis melalui nomor WhatsApp sekolah yang terhubung di server lokal port 3000 tanpa biaya langganan Fonnte.
        </p>
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
                        <textarea name="message" rows="4" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-emerald-500">📢 *UJI COBA WHATSAPP ENGINE PEMBDAHUB ($0 COST)*

Halo! Ini adalah pesan tes otomatisasi pengiriman dari menu Settings PembdaHUB. Sistem bekerja dengan lancar!</textarea>
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
