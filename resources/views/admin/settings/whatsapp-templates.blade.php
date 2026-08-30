@extends('layouts.admin')

@section('title', 'Editor Template & Syarat WA')

@section('content')
@php
    $templatesPayload = [];
    foreach ($templates as $key => $tpl) {
        $settingKey = 'wa_tpl_' . str_replace('.', '_', $key);
        $templatesPayload[$key] = [
            'key' => $key,
            'settingKey' => $settingKey,
            'title' => $tpl['title'],
            'variables' => $tpl['variables'],
            'content' => $tpl['content'] ?? '',
        ];
    }
@endphp

<div class="space-y-6" x-data="{
    selectedKey: 'executive.principal_daily_attendance',
    templates: {{ Js::from($templatesPayload) }},
    previewData: {
        '{sekolah}': 'SMAS Pembda 1 Gunungsitoli',
        '{nama_kepsek}': 'Agustiani Gea, S.Kom., M.Si',
        '{tanggal}': '{{ date('d F Y') }}',
        '{waktu_rekap}': '08:00 WIB',
        '{total_siswa}': '420',
        '{siswa_hadir}': '395',
        '{siswa_terlambat}': '12',
        '{siswa_sakit}': '5',
        '{siswa_izin}': '3',
        '{siswa_alpha}': '5',
        '{guru_hadir}': '28',
        '{guru_dinas}': '2',
        '{guru_sakit}': '1',
        '{guru_izin}': '0',
        '{guru_alpha}': '0',
        '{pegawai_hadir}': '8',
        '{pegawai_cuti}': '0',
        '{pegawai_sakit}': '0',
        '{pegawai_izin}': '1',
        '{pegawai_alpha}': '0',
        '{kelas}': 'XII ACP',
        '{nama_wali_kelas}': 'Devi Asri M. Halawa, S.Kom., Gr',
        '{hadir}': '29',
        '{terlambat}': '1',
        '{sakit}': '1',
        '{izin}': '1',
        '{alpha}': '0',
        '{daftar_tidak_hadir}': '• Budi Santoso (TERLAMBAT)\n• Siti Rahmawati (SAKIT)\n• Hendrik Zega (IZIN)',
        '{nama}': 'Abraham Yngwie Mozart Zega',
        '{classroom_name}': 'XII Ahmad Yani',
        '{status}': 'HADIR',
        '{waktu}': '07:15',
        '{tipe_absen}': 'Masuk Pagi',
        '{jabatan}': 'Guru Matematika',
        '{transaction_id}': 'TRX-202608-0091',
        '{jumlah}': '150.000',
        '{bulan}': 'Agustus 2026',
        '{periode_bulan}': 'Agustus 2026',
        '{jenis_tagihan}': 'SPP Bulanan',
        '{jatuh_tempo}': '10 September 2026',
        '{bank_name}': 'Bank BNI',
        '{bank_account}': '1234567890',
        '{bank_holder}': 'Yayasan Perguruan Pembda Nias',
        '{total_lunas}': '45.000.000',
        '{total_penunggak}': '14',
        '{total_tunggakan}': '2.100.000',
        '{jumlah_penunggak}': '3',
        '{daftar_penunggak}': '• Antonius Zai (Rp 150.000)\n• Clara Sabrina (Rp 150.000)\n• Daniel Harefa (Rp 150.000)',
        '{title}': 'Juara 1 Lomba Sains Terapan Pelajar',
        '{points}': '50',
        '{reason}': 'Meraih Medali Emas Tingkat Provinsi',
        '{nomor_registrasi}': 'PSB-2026-0812',
        '{biaya}': '250.000',
        '{email}': 'info@perguruanpembda.com',
        '{course_name}': 'Fisika Terapan XII',
        '{due_date}': '05 September 2026',
        '{link}': 'https://perguruanpembda.com/lms',
    },
    get currentText() {
        return this.templates[this.selectedKey]?.content || '';
    },
    get formattedPreview() {
        let text = this.currentText;
        if (!text || text.trim() === '') {
            return '<span class=\'text-slate-400 italic\'>Isi pesan masih kosong...</span>';
        }
        for (const [k, v] of Object.entries(this.previewData)) {
            text = text.replaceAll(k, v);
        }
        // Bold formatting
        text = text.replace(/\*([^\*]+)\*/g, '<b class=\'font-bold text-slate-950\'>$1</b>');
        // Italic formatting
        text = text.replace(/_([^_]+)_/g, '<i class=\'italic text-slate-700\'>$1</i>');
        // Linebreaks
        text = text.replace(/\n/g, '<br>');
        return text;
    },
    insertVariable(v) {
        const textarea = document.getElementById('textarea_' + this.selectedKey);
        if (!textarea) return;
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const oldVal = this.templates[this.selectedKey].content || '';
        this.templates[this.selectedKey].content = oldVal.substring(0, start) + v + oldVal.substring(end);
        this.$nextTick(() => {
            textarea.focus();
            textarea.setSelectionRange(start + v.length, start + v.length);
        });
    }
}">
    <!-- Header Banner (Tactile Neo-Brutalist Accent) -->
    <div class="bg-white rounded-2xl border-[1.5px] border-slate-900 shadow-[4px_4px_0px_#0f172a] p-6 sm:p-7 flex flex-col md:flex-row md:items-center justify-between gap-5">
        <div class="flex items-start sm:items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-teal-500 border-2 border-slate-900 shadow-[3px_3px_0px_#0f172a] flex items-center justify-center text-white text-2xl flex-shrink-0">
                <i class="fas fa-file-signature"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-md bg-teal-100 text-teal-800 text-[10px] font-mono font-bold border border-teal-300 uppercase tracking-wider">
                        📝 Template Studio & Live Preview
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                    Editor Template Pesan WhatsApp
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-0.5">
                    Kustomisasi redaksi pesan notifikasi otomatis & lihat simulasi tampilan di smartphone secara realtime.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.settings.whatsapp') }}" class="px-4 py-2.5 bg-slate-900 hover:bg-black text-white rounded-xl text-xs font-bold border-[1.5px] border-slate-900 shadow-[3px_3px_0px_#0f172a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_0px_#0f172a] transition-all flex items-center gap-2">
                <i class="fas fa-toggle-on"></i>
                <span>Pengaturan Saklar WA</span>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold border border-slate-300 transition flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border-[1.5px] border-emerald-600 rounded-2xl flex items-center justify-between shadow-[3px_3px_0px_#059669]">
        <div class="flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-600 text-lg"></i>
            <p class="text-emerald-900 font-bold text-xs sm:text-sm">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    <form action="{{ route('admin.settings.whatsapp.templates.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- SECTION 1: SYARAT & BATASAN PENGIRIMAN -->
        <div class="bg-white rounded-2xl border-[1.5px] border-slate-200 shadow-sm p-6 space-y-4">
            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                        <i class="fas fa-sliders-h text-emerald-600"></i> Syarat & Batasan Toleransi Pengiriman
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Tentukan pemicu keterlambatan dan target utama penerima pesan notifikasi</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Syarat Absensi -->
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2.5">
                    <h3 class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                        <i class="fas fa-user-check text-emerald-600"></i> Syarat Notifikasi Absensi Siswa:
                    </h3>
                    <div class="flex flex-wrap gap-3 text-xs">
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="wa_cond_notify_present" value="1" {{ !empty($conditions['wa_cond_notify_present']) ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500">
                            <span class="font-medium text-slate-700">Hadir Tepat Waktu</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="wa_cond_notify_late" value="1" {{ !empty($conditions['wa_cond_notify_late']) ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500">
                            <span class="font-medium text-slate-700">Terlambat</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="wa_cond_notify_absent" value="1" {{ !empty($conditions['wa_cond_notify_absent']) ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500">
                            <span class="font-medium text-slate-700">Alpa / Sakit / Izin</span>
                        </label>
                    </div>
                    <div class="pt-2 border-t border-slate-200 flex items-center gap-2">
                        <span class="text-[11px] text-slate-600 font-semibold">Toleransi Terlambat:</span>
                        <input type="number" name="wa_cond_late_threshold_minutes" value="{{ $conditions['wa_cond_late_threshold_minutes'] ?? 15 }}" class="w-16 px-2 py-1 rounded border border-slate-300 text-xs font-mono text-center">
                        <span class="text-[11px] text-slate-500">Menit (Default: 15 Menit / 07:45 WIB)</span>
                    </div>
                </div>

                <!-- Syarat SPP & Target -->
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2.5">
                    <h3 class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                        <i class="fas fa-users text-emerald-600"></i> Target Penerima Utama & Pengingat SPP:
                    </h3>
                    <div class="flex flex-wrap gap-3 text-xs">
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="wa_target_parent" value="1" {{ !empty($conditions['wa_target_parent']) ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500">
                            <span class="font-medium text-slate-700">Wali Murid / Orang Tua</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="wa_target_student" value="1" {{ !empty($conditions['wa_target_student']) ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500">
                            <span class="font-medium text-slate-700">Siswa (Jika Ada No. WA)</span>
                        </label>
                    </div>
                    <div class="pt-2 border-t border-slate-200 flex items-center gap-2">
                        <span class="text-[11px] text-slate-600 font-semibold">Pengingat Tagihan SPP:</span>
                        <input type="number" name="wa_cond_spp_reminder_days" value="{{ $conditions['wa_cond_spp_reminder_days'] ?? 3 }}" class="w-16 px-2 py-1 rounded border border-slate-300 text-xs font-mono text-center">
                        <span class="text-[11px] text-slate-500">Hari sebelum jatuh tempo</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: WORKSPACE STUDIO (TEMPLATE LIST + ACTIVE EDITOR + SMARTPHONE MOCKUP) -->
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">

            <!-- LEFT: TEMPLATES SELECTOR LIST (4 COLS) -->
            <div class="xl:col-span-4 bg-white rounded-2xl border-[1.5px] border-slate-200 shadow-sm p-5 space-y-3">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <h2 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-list-ul text-emerald-600"></i> Daftar Template Pesan
                        </h2>
                    </div>
                    <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-300">
                        {{ count($templates) }} Jenis
                    </span>
                </div>

                <div class="space-y-1.5 max-h-[680px] overflow-y-auto pr-1">
                    @foreach($templates as $key => $tpl)
                    @php $settingKey = 'wa_tpl_' . str_replace('.', '_', $key); @endphp
                    <button 
                        type="button" 
                        @click="selectedKey = '{{ $key }}'"
                        class="w-full text-left p-3 rounded-xl border transition-all flex items-start gap-2.5"
                        :class="selectedKey === '{{ $key }}' ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : 'bg-slate-50 hover:bg-slate-100 text-slate-800 border-slate-200'"
                    >
                        <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0" :class="selectedKey === '{{ $key }}' ? 'bg-emerald-400' : 'bg-slate-400'"></div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-xs font-bold truncate leading-tight" :class="selectedKey === '{{ $key }}' ? 'text-white' : 'text-slate-900'">
                                {{ $tpl['title'] }}
                            </h4>
                            <p class="text-[10px] font-mono mt-0.5 truncate" :class="selectedKey === '{{ $key }}' ? 'text-slate-300' : 'text-slate-500'">
                                {{ $key }}
                            </p>
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- CENTER: ACTIVE TEMPLATE TEXT EDITOR (5 COLS) -->
            <div class="xl:col-span-5 bg-white rounded-2xl border-[1.5px] border-slate-200 shadow-sm p-6 space-y-5">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200 inline-block mb-1" x-text="selectedKey"></span>
                        <h3 class="text-sm font-black text-slate-900" x-text="templates[selectedKey]?.title"></h3>
                    </div>
                </div>

                <!-- Variable Chips -->
                <div class="space-y-1.5 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                    <p class="text-[11px] text-slate-600 font-bold flex items-center gap-1.5">
                        <i class="fas fa-tags text-emerald-600"></i> Tag Variabel Tersedia (Klik untuk Menyisipkan):
                    </p>
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <template x-for="v in (templates[selectedKey]?.variables || [])" :key="v">
                            <button 
                                type="button" 
                                @click="insertVariable(v)"
                                class="px-2.5 py-1 rounded-lg bg-white hover:bg-emerald-100 text-emerald-800 border border-emerald-300 hover:border-emerald-500 font-mono text-[11px] font-bold transition shadow-xs flex items-center gap-1"
                            >
                                <span class="text-emerald-500 font-black">+</span>
                                <span x-text="v"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Textareas per template (All preserved in form so POST saves all) -->
                @foreach($templates as $key => $tpl)
                @php $settingKey = 'wa_tpl_' . str_replace('.', '_', $key); @endphp
                <div x-show="selectedKey === '{{ $key }}'" class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">
                        Isi Teks Pesan Template:
                    </label>
                    <textarea 
                        id="textarea_{{ $key }}"
                        name="tpl_{{ $settingKey }}" 
                        x-model="templates['{{ $key }}'].content"
                        rows="12" 
                        class="w-full p-4 rounded-xl border border-slate-300 text-xs font-mono text-slate-900 leading-relaxed focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white shadow-inner"
                        placeholder="Ketik isi format pesan..."
                    ></textarea>
                </div>
                @endforeach

                <!-- Submit Button with Spacious Padding -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-4">
                    <p class="text-[11px] text-slate-500">
                        Perubahan akan tersimpan ke database.
                    </p>
                    <button type="submit" class="px-8 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm border-[1.5px] border-slate-900 shadow-[3px_3px_0px_#0f172a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_0px_#0f172a] transition-all flex items-center justify-center gap-3 whitespace-nowrap">
                        <i class="fas fa-save text-base"></i>
                        <span>Simpan Semua Template</span>
                    </button>
                </div>
            </div>

            <!-- RIGHT: FIXED WIDTH SMARTPHONE MOCKUP (3 COLS) -->
            <div class="xl:col-span-3 flex flex-col items-center space-y-3 sticky top-6">
                
                <div class="w-full max-w-[320px] bg-white rounded-xl border border-slate-200 p-3 text-center shadow-sm">
                    <span class="text-xs font-black text-slate-900 flex items-center justify-center gap-1.5">
                        <i class="fas fa-mobile-alt text-emerald-600"></i> Live Preview WhatsApp
                    </span>
                    <p class="text-[10px] text-slate-500 mt-0.5">Simulasi pesan nyata di layar smartphone</p>
                </div>

                <!-- Phone Body (Fixed Width: 320px, Height: 550px, Explicit Inline Styles) -->
                <div style="width: 320px; min-width: 320px; max-width: 320px; height: 550px; background-color: #0f172a; border-radius: 38px; padding: 10px; border: 4px solid #1e293b; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3); display: flex; flex-direction: column; position: relative; user-select: none;">
                    
                    <!-- Top Speaker & Camera Notch -->
                    <div style="width: 80px; height: 14px; background-color: #1e293b; border-radius: 9999px; margin: 0 auto 6px auto; display: flex; align-items: center; justify-content: center;">
                        <div style="width: 8px; height: 8px; border-radius: 9999px; background-color: #020617;"></div>
                    </div>

                    <!-- Screen Inner Container (Explicit Light Canvas Background & Dark Text) -->
                    <div style="flex: 1; border-radius: 26px; overflow: hidden; background-color: #efeae2 !important; color: #0f172a !important; display: flex; flex-direction: column; position: relative;">
                        
                        <!-- WhatsApp Status Bar Top -->
                        <div style="background-color: #075e54 !important; color: #ffffff !important; padding: 6px 16px 4px 16px; font-family: monospace; font-size: 10px; display: flex; justify-content: space-between; align-items: center;">
                            <span>08:00</span>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <i class="fas fa-wifi" style="font-size: 9px;"></i>
                                <i class="fas fa-battery-full" style="font-size: 10px;"></i>
                            </div>
                        </div>

                        <!-- WhatsApp Header Bar -->
                        <div style="background-color: #075e54 !important; color: #ffffff !important; padding: 8px 10px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.15);">
                            <div style="display: flex; align-items: center; gap: 8px; min-width: 0;">
                                <i class="fas fa-arrow-left" style="font-size: 11px; cursor: pointer;"></i>
                                <div style="width: 30px; height: 30px; border-radius: 9999px; background-color: #ffffff; color: #075e54; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px; flex-shrink: 0;">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div style="line-height: 1.2; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <h4 style="font-weight: 700; font-size: 11px; margin: 0; color: #ffffff;">PembdaHUB</h4>
                                    <p style="font-size: 9px; color: #a7f3d0; margin: 0; display: flex; align-items: center; gap: 4px;">
                                        <span style="width: 6px; height: 6px; border-radius: 9999px; background-color: #34d399; display: inline-block;"></span> online
                                    </p>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; font-size: 11px; color: #ffffff; padding-right: 4px;">
                                <i class="fas fa-video"></i>
                                <i class="fas fa-phone"></i>
                                <i class="fas fa-ellipsis-v"></i>
                            </div>
                        </div>

                        <!-- Chat Messages Canvas Screen -->
                        <div style="flex: 1; padding: 10px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; background-color: #efeae2 !important;">
                            <!-- Date Badge -->
                            <div style="background-color: #ffffff !important; color: #475569 !important; padding: 2px 10px; border-radius: 6px; font-size: 9px; font-weight: 700; align-self: center; box-shadow: 0 1px 2px rgba(0,0,0,0.06); text-transform: uppercase; letter-spacing: 0.5px;">
                                Hari Ini
                            </div>

                            <!-- WhatsApp Incoming Green Bubble (Explicit Colors) -->
                            <div style="background-color: #d9fdd3 !important; color: #111827 !important; border: 1px solid #c4eabf !important; border-radius: 12px; border-top-left-radius: 0; padding: 10px 12px; width: 100%; max-width: 96%; align-self: flex-start; box-shadow: 0 1px 2px rgba(0,0,0,0.08); font-size: 11px; line-height: 1.55; word-break: break-word;">
                                <div 
                                    style="color: #111827 !important; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;"
                                    x-html="formattedPreview"
                                ></div>

                                <!-- Read Receipt Double Blue Check & Timestamp -->
                                <div style="display: flex; justify-content: flex-end; align-items: center; gap: 4px; font-size: 9px; color: #64748b; margin-top: 4px;">
                                    <span>08:00</span>
                                    <span style="color: #53bdeb; font-weight: bold; letter-spacing: -1px;">✓✓</span>
                                </div>
                            </div>
                        </div>

                        <!-- Chat Input Bar Bottom -->
                        <div style="background-color: #f0f2f5 !important; padding: 8px 10px; display: flex; align-items: center; gap: 8px; border-top: 1px solid #e2e8f0;">
                            <div style="flex: 1; background-color: #ffffff; border-radius: 9999px; padding: 5px 12px; font-size: 10px; color: #94a3b8; display: flex; align-items: center; justify-content: space-between;">
                                <span>Ketik pesan...</span>
                                <i class="fas fa-paperclip" style="color: #94a3b8; font-size: 10px;"></i>
                            </div>
                            <div style="width: 26px; height: 26px; border-radius: 9999px; background-color: #075e54; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 10px;">
                                <i class="fas fa-microphone"></i>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Info Box below phone -->
                <div class="w-full max-w-[320px] p-3 bg-slate-100 rounded-xl border border-slate-200 text-[10px] text-slate-600 space-y-1">
                    <p class="font-bold text-slate-800 flex items-center gap-1">
                        <i class="fas fa-info-circle text-emerald-600"></i> Format WhatsApp:
                    </p>
                    <p>Gunakan <code>*teks*</code> untuk tebal dan <code>_teks_</code> untuk miring.</p>
                </div>

            </div>

        </div>

    </form>
</div>
@endsection
