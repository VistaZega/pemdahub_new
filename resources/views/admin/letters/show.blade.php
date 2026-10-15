@extends($layout ?? auth()->user()->layout ?? 'layouts.app')

@section('title', $letter->title)

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <a href="{{ route('admin.letters.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-violet-700 transition-colors">
            <i class="fas fa-arrow-left"></i> Kembali ke Inboks Surat Masuk
        </a>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ $letter->wa_share_url }}" target="_blank" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-3.5 py-2 rounded-xl shadow transition-all flex items-center gap-2 text-xs md:text-sm" title="Bagikan Langsung ke WhatsApp">
                <i class="fab fa-whatsapp text-sm md:text-base"></i>
                <span>Bagikan WA</span>
            </a>
            <button type="button" onclick="copyVerificationLink('{{ $letter->verification_url }}')" class="bg-teal-600 hover:bg-teal-700 text-white font-bold px-3.5 py-2 rounded-xl shadow transition-all flex items-center gap-2 text-xs md:text-sm" title="Salin Link Verifikasi Resmi">
                <i class="fas fa-link text-xs"></i>
                <span>Salin Link</span>
            </button>
            <button type="button" onclick="copyLetterText({{ json_encode($letter->wa_text_content) }})" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-3.5 py-2 rounded-xl shadow transition-all flex items-center gap-2 text-xs md:text-sm" title="Salin Teks Lengkap WA">
                <i class="fas fa-copy text-xs"></i>
                <span>Salin Teks WA</span>
            </button>
            <a href="{{ route('admin.letters.print', $letter->id) }}" target="_blank" class="bg-gray-800 hover:bg-gray-900 text-white font-bold px-3.5 py-2 rounded-xl shadow text-xs md:text-sm transition-all flex items-center gap-2">
                <i class="fas fa-print"></i>
                <span>Cetak / PDF</span>
            </a>
        </div>
    </div>

    {{-- Main Letter View Card (A4: 210mm, Margin Kiri-Kanan 15mm) --}}
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-6 md:p-10 lg:px-[15mm] lg:py-[12mm] max-w-[210mm] w-full mx-auto">
        {{-- Kop --}}
        <div class="border-b-4 border-double border-gray-900 pb-6 mb-8 text-center">
            <div class="flex flex-col md:flex-row items-center justify-center gap-4">
                <img src="{{ asset('images/logo_yayasan.png') }}" alt="Logo Yayasan" class="w-16 md:w-20 h-auto max-h-24 object-contain flex-shrink-0">
                <div class="text-center">
                    <h2 class="text-base md:text-lg font-extrabold text-gray-900 uppercase tracking-tight whitespace-nowrap">Yayasan Perguruan Pembangunan Daerah Nias</h2>
                    <h2 class="text-xl md:text-2xl font-black text-gray-950 tracking-widest uppercase my-0.5">( P E M B D A )</h2>
                    <p class="text-xs font-semibold text-gray-800 mt-0.5">Jl. Pelita No.09 Kel. Ilir Kota Gunungsitoli (22815)</p>
                    <p class="text-xs font-medium text-gray-600 mt-0.5">web : perguruanpembda.com | email : perguruanpembdanias@gmail.com</p>
                </div>
            </div>
        </div>

        {{-- Title --}}
        <div class="text-center mb-8">
            <h3 class="text-base font-extrabold text-gray-900 uppercase underline">SURAT EDARAN YAYASAN</h3>
            <p class="text-xs font-mono font-bold text-gray-700 mt-1">Nomor: {{ $letter->letter_number }}</p>
        </div>

        {{-- Meta --}}
        @php
            $targetLabels = is_array($letter->recipients) && isset($letter->recipients['target_labels']) 
                ? $letter->recipients['target_labels'] 
                : [(is_array($letter->recipients) && isset($letter->recipients['target_label']) ? $letter->recipients['target_label'] : 'Kepala Sekolah se-Perguruan Pembda Nias')];
        @endphp
        <div class="mb-8 text-sm space-y-2 text-gray-800">
            <div class="flex">
                <span class="w-28 font-semibold text-gray-600">Perihal</span>
                <span class="w-4 font-bold text-center">:</span>
                <span class="font-bold text-gray-900 flex-1">{{ $letter->title }}</span>
            </div>
            <div class="flex items-start">
                <span class="w-28 font-semibold text-gray-600">Kepada Yth.</span>
                <span class="w-4 font-bold text-center">:</span>
                <div class="font-semibold text-gray-900 flex-1">
                    @if(count($targetLabels) > 1)
                        <ol class="list-decimal list-inside space-y-0.5">
                            @foreach($targetLabels as $targetItem)
                                <li>{{ $targetItem }}</li>
                            @endforeach
                        </ol>
                    @else
                        <span>{{ $targetLabels[0] ?? 'Kepala Sekolah se-Perguruan Pembda Nias' }}</span>
                    @endif
                </div>
            </div>
            <div class="flex">
                <span class="w-28 font-semibold text-gray-600">Di -</span>
                <span class="w-4 font-bold text-center"></span>
                <span class="font-bold text-gray-900 flex-1">Gunungsitoli</span>
            </div>
        </div>

        {{-- Content --}}
        <style>
            .letter-content ol { list-style-type: decimal !important; padding-left: 1.75rem !important; margin-top: 0.5rem !important; margin-bottom: 0.75rem !important; }
            .letter-content ol li { margin-bottom: 0.5rem !important; }
            .letter-content ul { list-style-type: disc !important; padding-left: 1.5rem !important; margin-top: 0.25rem !important; margin-bottom: 0.5rem !important; }
            .letter-content ul li { margin-bottom: 0.25rem !important; }
        </style>
        <div class="prose max-w-none text-sm text-gray-800 leading-relaxed mb-12 space-y-4 font-serif letter-content">
            {!! $letter->content !!}
        </div>

        {{-- TTD Digital --}}
        <div class="flex justify-between items-end pt-6 border-t border-gray-100">
            <div class="flex items-center gap-3 bg-gray-50 border border-gray-200 p-3 rounded-xl">
                <img src="{{ $letter->qr_code_url }}" alt="QR" class="w-16 h-16 bg-white p-1 rounded border border-gray-300">
                <div class="text-[11px] text-gray-600">
                    <span class="font-bold text-emerald-700 block text-xs"><i class="fas fa-shield-alt"></i> TTD Digital Sah</span>
                    <span class="font-mono text-[10px] text-gray-500">{{ substr($letter->signature_hash, 0, 16) }}...</span>
                </div>
            </div>

            <div class="text-right">
                <p class="text-xs text-gray-600">Gunungsitoli, {{ \Carbon\Carbon::parse($letter->effective_date)->translatedFormat('d F Y') }}</p>
                <p class="text-xs font-bold text-gray-900 mt-1 uppercase">{{ $letter->signatory_position }}</p>
                <div class="my-3 py-1 flex justify-end">
                    <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded">✔ SIGNED DIGITAL</span>
                </div>
                <p class="text-sm font-extrabold text-gray-900 underline">{{ $letter->signatory_name }}</p>
            </div>
        </div>
    </div>
</div>

<script>
function showCopyToast(message) {
    let toast = document.getElementById('letter-copy-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'letter-copy-toast';
        toast.className = 'fixed bottom-5 right-5 z-50 bg-gray-950 text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 border border-emerald-500/60 transition-all duration-300 transform translate-y-10 opacity-0 pointer-events-none text-xs md:text-sm font-semibold';
        document.body.appendChild(toast);
    }
    toast.innerHTML = `<i class="fas fa-check-circle text-emerald-400 text-base"></i> <span>${message}</span>`;
    toast.classList.remove('translate-y-10', 'opacity-0', 'pointer-events-none');
    toast.classList.add('translate-y-0', 'opacity-100');
    setTimeout(() => {
        toast.classList.add('translate-y-10', 'opacity-0', 'pointer-events-none');
        toast.classList.remove('translate-y-0', 'opacity-100');
    }, 2800);
}

function copyVerificationLink(url) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(() => {
            showCopyToast('Link verifikasi surat resmi berhasil disalin!');
        }).catch(() => fallbackCopy(url, 'Link verifikasi surat resmi berhasil disalin!'));
    } else {
        fallbackCopy(url, 'Link verifikasi surat resmi berhasil disalin!');
    }
}

function copyLetterText(text) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            showCopyToast('Teks pengumuman & link WA berhasil disalin!');
        }).catch(() => fallbackCopy(text, 'Teks pengumuman & link WA berhasil disalin!'));
    } else {
        fallbackCopy(text, 'Teks pengumuman & link WA berhasil disalin!');
    }
}

function fallbackCopy(text, msg) {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.left = '-9999px';
    document.body.appendChild(ta);
    ta.focus();
    ta.select();
    try {
        document.execCommand('copy');
        showCopyToast(msg);
    } catch (err) {
        alert(msg + '\n\n' + text);
    }
    document.body.removeChild(ta);
}
</script>
@endsection
