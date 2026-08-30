@extends('layouts.admin')

@section('title', 'Editor Template & Syarat WA')

@section('content')
<div class="space-y-6" x-data="{
    selectedKey: 'executive.principal_daily_attendance',
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
    formatMessage(text) {
        if (!text) return '';
        let formatted = text;
        // Replace sample variables
        for (const [key, val] of Object.entries(this.previewData)) {
            formatted = formatted.replaceAll(key, val);
        }
        // Bold: *text* -> <b>text</b>
        formatted = formatted.replace(/\*([^\*]+)\*/g, '<b class=\'font-bold\'>$1</b>');
        // Italic: _text_ -> <i>text</i>
        formatted = formatted.replace(/_([^_]+)_/g, '<i class=\'italic text-slate-600\'>$1</i>');
        // Newlines -> <br>
        formatted = formatted.replace(/\n/g, '<br>');
        return formatted;
    },
    insertVariable(varText, fieldId) {
        const textarea = document.getElementById(fieldId);
        if (!textarea) return;
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        textarea.value = text.substring(0, start) + varText + text.substring(end);
        textarea.focus();
        textarea.selectionStart = textarea.selectionEnd = start + varText.length;
        textarea.dispatchEvent(new Event('input'));
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
                    Kustomisasi redaksi pesan notifikasi otomatis & lihat simulasi tampilan di layar smartphone secara realtime.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.settings.whatsapp') }}" class="px-4 py-2.5 bg-slate-900 hover:bg-black text-white rounded-xl text-xs font-bold border-[1.5px] border-slate-900 shadow-[3px_3px_0px_#0f172a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_0px_#0f172a] transition-all flex items-center gap-2">
                <i class="fas fa-toggle-on"></i>
                <span>Pengaturan Saklar WA</span>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold border border-slate-300 transition flex items-center gap-1.5">
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

        <!-- SECTION 2: WORKSPACE TEMPLATE EDITOR (SPLIT: LIST & LIVE PHONE MOCKUP) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- LEFT: TEMPLATES SELECTION ACCORDION (7 COLS) -->
            <div class="lg:col-span-7 bg-white rounded-2xl border-[1.5px] border-slate-200 shadow-sm p-6 space-y-6">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                            <i class="fas fa-edit text-emerald-600"></i> Redaksi Template Pesan
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih template di bawah, edit teksnya, atau klik tag variabel untuk menyisipkannya</p>
                    </div>
                    <span class="text-[11px] font-mono font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-300">
                        {{ count($templates) }} Template
                    </span>
                </div>

                <div class="space-y-5">
                    @foreach($templates as $key => $tpl)
                    @php
                        $settingKey = 'wa_tpl_' . str_replace('.', '_', $key);
                    @endphp
                    <div 
                        class="p-4 sm:p-5 rounded-2xl border transition-all"
                        :class="selectedKey === '{{ $key }}' ? 'border-emerald-500 bg-emerald-50/20 shadow-sm' : 'border-slate-200 bg-white hover:border-slate-300'"
                        @click="selectedKey = '{{ $key }}'"
                    >
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full" :class="selectedKey === '{{ $key }}' ? 'bg-emerald-600' : 'bg-slate-300'"></span>
                                <h3 class="text-xs font-bold text-slate-900">{{ $tpl['title'] }}</h3>
                            </div>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-bold border border-slate-200 self-start sm:self-auto">
                                {{ $key }}
                            </span>
                        </div>

                        <!-- Variable Chips (Click to Insert) -->
                        <div class="mb-3">
                            <p class="text-[10px] text-slate-500 font-semibold mb-1.5 flex items-center gap-1">
                                <i class="fas fa-tags text-slate-400"></i> Tag Variabel (Klik untuk menyisipkan ke kursor):
                            </p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($tpl['variables'] as $var)
                                <button 
                                    type="button" 
                                    @click.stop="insertVariable('{{ $var }}', 'editor_{{ $settingKey }}')"
                                    class="px-2 py-0.5 rounded-md bg-white hover:bg-emerald-100 text-emerald-800 border border-emerald-300 hover:border-emerald-500 font-mono text-[10px] font-bold transition shadow-xs"
                                    title="Klik untuk sisipkan {{ $var }}"
                                >
                                    + {{ $var }}
                                </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Textarea -->
                        <textarea 
                            id="editor_{{ $settingKey }}"
                            name="tpl_{{ $settingKey }}" 
                            rows="6" 
                            x-ref="field_{{ str_replace('.', '_', $key) }}"
                            @focus="selectedKey = '{{ $key }}'"
                            class="w-full p-3 rounded-xl border border-slate-300 text-xs font-mono text-slate-900 leading-relaxed focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white"
                        >{{ $tpl['content'] }}</textarea>
                    </div>
                    @endforeach
                </div>

                <!-- Submit Button -->
                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="w-full sm:w-auto px-7 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs border-[1.5px] border-slate-900 shadow-[3px_3px_0px_#0f172a] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_0px_#0f172a] transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-save text-sm"></i>
                        <span>Simpan Semua Template</span>
                    </button>
                </div>
            </div>

            <!-- RIGHT: LIVE SMARTPHONE WHATSAPP CHAT MOCKUP (5 COLS) -->
            <div class="lg:col-span-5 sticky top-6 space-y-4">
                
                <div class="bg-white rounded-2xl border-[1.5px] border-slate-200 shadow-sm p-4 text-center">
                    <span class="text-xs font-black text-slate-900 flex items-center justify-center gap-1.5">
                        <i class="fas fa-mobile-alt text-emerald-600"></i> Live Preview Layar Smartphone
                    </span>
                    <p class="text-[11px] text-slate-500 mt-0.5">Tampilan simulasi pesan yang akan diterima di WhatsApp penerima</p>
                </div>

                <!-- Smartphone Chassis -->
                <div class="relative mx-auto max-w-[340px] rounded-[36px] bg-slate-900 p-3 shadow-2xl border-4 border-slate-800">
                    <!-- Screen Notch / Camera -->
                    <div class="absolute top-4 left-1/2 -translate-x-1/2 w-20 h-4 bg-slate-800 rounded-full z-20 flex items-center justify-center">
                        <div class="w-2.5 h-2.5 rounded-full bg-slate-950"></div>
                    </div>

                    <!-- Phone Screen Content -->
                    <div class="rounded-[28px] overflow-hidden bg-[#efeae2] flex flex-col h-[520px] text-slate-900 text-xs select-none">
                        
                        <!-- Status Bar Top -->
                        <div class="bg-[#005c4b] text-white px-5 pt-3 pb-1 flex justify-between items-center text-[10px] font-mono">
                            <span>08:00</span>
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-wifi text-[9px]"></i>
                                <i class="fas fa-battery-full text-[10px]"></i>
                            </div>
                        </div>

                        <!-- WhatsApp Header Bar -->
                        <div class="bg-[#005c4b] text-white px-3 py-2 flex items-center justify-between shadow-sm">
                            <div class="flex items-center gap-2.5">
                                <i class="fas fa-arrow-left text-xs"></i>
                                <div class="w-8 h-8 rounded-full bg-white text-emerald-700 flex items-center justify-center font-bold text-xs shadow-inner">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="leading-tight truncate">
                                    <h4 class="font-bold text-xs truncate">PembdaHUB Official</h4>
                                    <p class="text-[10px] text-emerald-200 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span> online
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 text-xs pr-1">
                                <i class="fas fa-video"></i>
                                <i class="fas fa-phone"></i>
                                <i class="fas fa-ellipsis-v"></i>
                            </div>
                        </div>

                        <!-- Chat Messages Canvas -->
                        <div class="flex-1 p-3 overflow-y-auto space-y-3 flex flex-col justify-start bg-[#efeae2]">
                            <!-- Date Badge -->
                            <div class="self-center px-3 py-0.5 rounded-lg bg-white/80 text-slate-600 text-[10px] font-bold shadow-xs uppercase tracking-wider">
                                Hari Ini
                            </div>

                            <!-- WhatsApp Incoming Green Bubble -->
                            <div class="self-start max-w-[92%] bg-[#d9fdd3] text-slate-900 rounded-2xl rounded-tl-none p-3 shadow-sm border border-[#c4eabf] relative space-y-1">
                                <div 
                                    class="text-[11px] leading-relaxed break-words font-sans"
                                    x-html="
                                        @foreach($templates as $key => $tpl)
                                        @php $settingKey = 'wa_tpl_' . str_replace('.', '_', $key); @endphp
                                        (selectedKey === '{{ $key }}') ? formatMessage($refs['field_{{ str_replace('.', '_', $key) }}']?.value || '{{ addslashes($tpl['content']) }}') :
                                        @endforeach
                                        ''
                                    "
                                ></div>

                                <!-- Read Ticks & Timestamp -->
                                <div class="flex items-center justify-end gap-1 text-[9px] text-slate-500 pt-1">
                                    <span>08:00</span>
                                    <span class="text-[#53bdeb] font-bold">✓✓</span>
                                </div>
                            </div>
                        </div>

                        <!-- Chat Input Bar Bottom -->
                        <div class="bg-[#f0f2f5] p-2 flex items-center gap-2 border-t border-slate-200">
                            <div class="flex-1 bg-white rounded-full px-3 py-1.5 text-[11px] text-slate-400 flex items-center justify-between">
                                <span>Ketik pesan...</span>
                                <i class="fas fa-paperclip text-slate-400 text-xs"></i>
                            </div>
                            <div class="w-7 h-7 rounded-full bg-[#005c4b] text-white flex items-center justify-center text-xs">
                                <i class="fas fa-microphone"></i>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Info Box below phone -->
                <div class="p-3.5 bg-slate-100 rounded-xl border border-slate-200 text-[11px] text-slate-600 space-y-1">
                    <p class="font-bold text-slate-800 flex items-center gap-1.5">
                        <i class="fas fa-magic text-emerald-600"></i> Format WhatsApp yang Didukung:
                    </p>
                    <ul class="list-disc list-inside space-y-0.5 text-slate-500 pl-1">
                        <li><code>*teks tebal*</code> $\rightarrow$ <b>teks tebal</b></li>
                        <li><code>_teks miring_</code> $\rightarrow$ <i>teks miring</i></li>
                        <li>Variabel <code>{nama}</code> akan otomatis diisi data riil.</li>
                    </ul>
                </div>

            </div>

        </div>

    </form>
</div>
@endsection
