@extends('layouts.admin')

@section('title', 'Editor Template & Syarat WA')

@section('content')
<div class="space-y-6">
    <!-- Header & Breadcrumb -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/20">
                <i class="fas fa-edit text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Editor Template & Syarat WA</h1>
                <p class="text-sm text-gray-500">Ubah isi teks pesan notifikasi dan atur syarat batasan pengiriman ke wali murid</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.settings.whatsapp') }}" class="px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-xl text-sm font-semibold transition-all flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Pengaturan WA
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

    <form action="{{ route('admin.settings.whatsapp.templates.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- SECTION 1: SYARAT & BATASAN PENGIRIMAN -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
            <div class="border-b border-gray-100 pb-3">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-sliders-h text-emerald-600"></i> Pengaturan Syarat & Batasan Pengiriman
                </h2>
                <p class="text-xs text-gray-500">Tentukan kapan pesan harus dikirim dan siapa target penerima utamanya</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Syarat Absensi -->
                <div class="p-4 bg-gray-50/70 rounded-xl border border-gray-100 space-y-3">
                    <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-user-check text-emerald-600"></i> Syarat Notifikasi Absensi
                    </h3>
                    
                    <div class="space-y-2 text-xs">
                        <label class="block font-semibold text-gray-700">Kirim WA Saat Status Absensi:</label>
                        <div class="flex flex-wrap gap-4">
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" name="wa_cond_notify_present" value="1" {{ !empty($conditions['wa_cond_notify_present']) ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500">
                                <span>[x] Hadir Tepat Waktu</span>
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" name="wa_cond_notify_late" value="1" {{ !empty($conditions['wa_cond_notify_late']) ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500">
                                <span>[x] Terlambat</span>
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" name="wa_cond_notify_absent" value="1" {{ !empty($conditions['wa_cond_notify_absent']) ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500">
                                <span>[x] Alpa / Sakit / Izin</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-gray-200/60">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Toleransi Menit Keterlambatan:</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="wa_cond_late_threshold_minutes" value="{{ $conditions['wa_cond_late_threshold_minutes'] ?? 15 }}" class="w-24 px-3 py-1.5 rounded-lg border border-gray-300 text-xs text-gray-900 focus:outline-none focus:border-emerald-500">
                            <span class="text-xs text-gray-500">Menit (Hanya kirim WA terlambat jika &gt; X menit)</span>
                        </div>
                    </div>
                </div>

                <!-- Syarat SPP & Target Penerima -->
                <div class="p-4 bg-gray-50/70 rounded-xl border border-gray-100 space-y-3">
                    <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-users text-emerald-600"></i> Target Penerima Utama & SPP
                    </h3>

                    <div class="space-y-2 text-xs">
                        <label class="block font-semibold text-gray-700">Kirimkan Notifikasi Kepada:</label>
                        <div class="flex flex-wrap gap-4">
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" name="wa_target_parent" value="1" {{ !empty($conditions['wa_target_parent']) ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500">
                                <span>[x] Wali Murid / Orang Tua</span>
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" name="wa_target_student" value="1" {{ !empty($conditions['wa_target_student']) ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500">
                                <span>[x] Siswa (Jika punya nomor WA)</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-gray-200/60">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Pengingat SPP Dikirim H-Berapa Hari:</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="wa_cond_spp_reminder_days" value="{{ $conditions['wa_cond_spp_reminder_days'] ?? 3 }}" class="w-24 px-3 py-1.5 rounded-lg border border-gray-300 text-xs text-gray-900 focus:outline-none focus:border-emerald-500">
                            <span class="text-xs text-gray-500">Hari sebelum jatuh tempo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: EDITOR TEKS TEMPLATE PESAN -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
            <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-file-signature text-emerald-600"></i> Editor Teks Template Pesan
                    </h2>
                    <p class="text-xs text-gray-500">Kustomisasi isi kata-kata pesan WA untuk masing-masing kejadian di sekolah</p>
                </div>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2">
                    <i class="fas fa-save"></i> Simpan Semua Perubahan
                </button>
            </div>

            <!-- Templates List Cards -->
            <div class="space-y-6">
                @foreach($templates as $key => $tpl)
                @php
                    $settingKey = 'wa_tpl_' . str_replace('.', '_', $key);
                @endphp
                <div class="p-5 bg-gray-50/60 rounded-2xl border border-gray-100 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <span>{{ $tpl['title'] }}</span>
                        </h3>
                        <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-gray-200 text-gray-700">{{ $key }}</span>
                    </div>

                    <!-- Available Variable Badges -->
                    <div class="flex items-center gap-1.5 flex-wrap text-xs text-gray-500">
                        <span class="font-semibold text-emerald-700">Tag Variabel Otomatis:</span>
                        @foreach($tpl['variables'] as $var)
                        <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-mono text-[11px]">
                            {{ $var }}
                        </span>
                        @endforeach
                    </div>

                    <!-- Textarea Editor -->
                    <textarea 
                        name="tpl_{{ $settingKey }}" 
                        rows="6" 
                        class="w-full p-3 rounded-xl border border-gray-200 text-xs font-mono text-gray-900 leading-relaxed focus:outline-none focus:border-emerald-500 bg-white"
                    >{{ $tpl['content'] }}</textarea>
                </div>
                @endforeach
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2">
                    <i class="fas fa-save"></i> Simpan Semua Perubahan
                </button>
            </div>
        </div>

    </form>
</div>
@endsection
