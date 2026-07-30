@extends('layouts.yayasan')

@section('title', 'Detail Surat Digital')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    {{-- Header Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('yayasan.letters.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-violet-700 transition-colors">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Surat
        </a>
        <div class="flex flex-wrap items-center gap-2">
            <button onclick="copyWaContent()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2 rounded-xl shadow transition-all flex items-center gap-2 text-sm" title="Salin seluruh teks surat & link verifikasi untuk dikirim di WhatsApp">
                <i class="fas fa-copy"></i> Salin Teks Surat WA (1-Klik)
            </button>
            <button onclick="copyVerificationLink()" class="bg-teal-600 hover:bg-teal-700 text-white font-bold px-4 py-2 rounded-xl shadow transition-all flex items-center gap-2 text-sm" title="Salin hanya link verifikasi online">
                <i class="fas fa-link"></i> Salin Link Verifikasi
            </button>
            <a href="{{ route('yayasan.letters.edit', $letter->id) }}" class="bg-amber-500 hover:bg-amber-600 text-gray-900 font-bold px-4 py-2 rounded-xl shadow transition-all flex items-center gap-2 text-sm">
                <i class="fas fa-edit"></i> Edit Surat
            </a>
            <a href="{{ route('yayasan.letters.print', $letter->id) }}" target="_blank" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-xl shadow transition-all flex items-center gap-2 text-sm">
                <i class="fas fa-print"></i> Cetak / PDF
            </a>
            <a href="{{ $letter->verification_url }}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 py-2 rounded-xl shadow transition-all flex items-center gap-2 text-sm">
                <i class="fas fa-qrcode"></i> Uji QR
            </a>
            <form action="{{ route('yayasan.letters.destroy', $letter->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus surat digital ini? Dokumen yang dihapus tidak akan dapat diverifikasi lagi.')" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-4 py-2 rounded-xl shadow text-sm transition-all flex items-center gap-2">
                    <i class="fas fa-trash-alt"></i> Hapus Surat
                </button>
            </form>
        </div>
    </div>

    {{-- Session Alert --}}
    @if(session('success'))
    <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-xl shadow-sm text-emerald-800 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700"><i class="fas fa-times"></i></button>
    </div>
    @endif

    {{-- Main Letter Preview Container --}}
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 md:p-12 relative overflow-hidden">
        {{-- Kop Surat Preview --}}
        <div class="border-b-4 border-double border-gray-900 pb-6 mb-8 text-center relative">
            <div class="flex flex-col md:flex-row items-center justify-center gap-5">
                <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo Yayasan" class="w-20 h-auto max-h-24 object-contain flex-shrink-0">
                <div class="text-center">
                    <h2 class="text-lg md:text-xl font-bold text-gray-900 uppercase">Yayasan Perguruan Pembangunan Daerah Nias</h2>
                    <h2 class="text-2xl md:text-3xl font-black text-gray-950 tracking-widest uppercase my-0.5">( P E M B D A )</h2>
                    <p class="text-xs font-semibold text-gray-800 mt-1">Jl. Pelita No.09 Kel. Ilir Kota Gunungsitoli (22815)</p>
                    <p class="text-xs font-medium text-gray-600 mt-0.5">web : perguruanpembda.com | email : perguruanpembdanias@gmail.com</p>
                </div>
            </div>
        </div>

        {{-- Nomor & Perihal --}}
        <div class="mb-8 text-center">
            <h3 class="text-base font-extrabold text-gray-900 uppercase underline tracking-wide">
                {{ $categories[$letter->category] ?? strtoupper($letter->category) }}
            </h3>
            <p class="text-xs font-mono font-bold text-gray-700 mt-1">Nomor: {{ $letter->letter_number }}</p>
        </div>

        {{-- Meta Penerima & Tanggal --}}
        @php
            $targetLabels = is_array($letter->recipients) && isset($letter->recipients['target_labels']) 
                ? $letter->recipients['target_labels'] 
                : [(is_array($letter->recipients) && isset($letter->recipients['target_label']) ? $letter->recipients['target_label'] : 'Kepala Sekolah se-Perguruan Pembda Nias')];
        @endphp
        <div class="mb-8 text-sm text-gray-800 space-y-2">
            <div class="flex">
                <span class="w-28 font-semibold text-gray-600">Perihal</span>
                <span class="w-4 text-center font-bold">:</span>
                <span class="font-bold text-gray-900 flex-1">{{ $letter->title }}</span>
            </div>
            <div class="flex items-start">
                <span class="w-28 font-semibold text-gray-600">Kepada Yth.</span>
                <span class="w-4 text-center font-bold">:</span>
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
                <span class="w-4 text-center font-bold"></span>
                <span class="font-bold text-gray-900 flex-1">Gunungsitoli</span>
            </div>
        </div>

        {{-- Isi Surat --}}
        <div class="prose max-w-none text-sm text-gray-800 leading-relaxed mb-12 space-y-4">
            {!! $letter->content !!}
        </div>

        {{-- Signature Block with QR Code --}}
        <div class="flex justify-between items-end pt-6 border-t border-gray-100">
            {{-- Left side QR Code --}}
            <div class="flex items-center gap-3 bg-gray-50 border border-gray-200 p-3 rounded-xl">
                <img src="{{ $letter->qr_code_url }}" alt="QR Code Keaslian" class="w-20 h-20 bg-white p-1 rounded border border-gray-300">
                <div class="text-[11px] text-gray-600 space-y-0.5">
                    <span class="font-bold text-emerald-700 block text-xs"><i class="fas fa-shield-alt"></i> TTD Digital Terverifikasi</span>
                    <p class="font-mono text-[10px] text-gray-500">Hash: {{ substr($letter->signature_hash, 0, 16) }}...</p>
                    <p class="text-[10px] text-gray-400">Scan QR Code untuk memvalidasi keaslian dokumen resmi di perguruanpembda.com</p>
                </div>
            </div>

            {{-- Right side Signatory --}}
            <div class="text-right">
                <p class="text-xs text-gray-600">Gunungsitoli, {{ \Carbon\Carbon::parse($letter->effective_date)->translatedFormat('d F Y') }}</p>
                <p class="text-xs font-bold text-gray-900 mt-1 uppercase">{{ $letter->signatory_position }}</p>
                <div class="my-3 py-2 flex justify-end">
                    <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-800 border border-emerald-300 rounded text-[10px] font-bold">
                        <i class="fas fa-check-circle"></i> SIGNED DIGITAL
                    </span>
                </div>
                <p class="text-sm font-extrabold text-gray-900 underline">{{ $letter->signatory_name }}</p>
            </div>
        </div>
    </div>

    {{-- Read Receipts Section (Tanda Terima Unit Sekolah) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h4 class="text-base font-bold text-gray-900 mb-2 flex items-center gap-2">
            <i class="fas fa-eye text-violet-600"></i> Status Pembacaan & Tanda Terima Unit Sekolah
        </h4>
        <p class="text-xs text-gray-500 mb-4">Catatan otomatis saat pihak sekolah membuka dan membaca Surat Digital ini melalui PembdaHUB.</p>

        @if($letter->reads->isEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-800 flex items-center gap-3">
            <i class="fas fa-info-circle text-base text-amber-600"></i>
            <span>Belum ada pembacaan dari unit sekolah. Notifikasi edaran ini sudah terbit di Dashboard Unit.</span>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 text-xs font-bold uppercase">
                    <tr>
                        <th class="py-2.5 px-4">Unit Sekolah</th>
                        <th class="py-2.5 px-4">Dibaca Oleh</th>
                        <th class="py-2.5 px-4">Waktu Dibaca</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($letter->reads as $read)
                    <tr>
                        <td class="py-3 px-4 font-bold text-gray-900">
                            {{ $read->school->name ?? 'Unit Sekolah' }}
                        </td>
                        <td class="py-3 px-4 text-xs font-medium text-gray-700">
                            {{ $read->user->name ?? 'User Unit' }} ({{ ucfirst($read->user->role ?? '') }})
                        </td>
                        <td class="py-3 px-4 text-xs text-gray-500 font-mono">
                            {{ \Carbon\Carbon::parse($read->read_at)->translatedFormat('d F Y, H:i') }} WIB
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<script>
function copyWaContent() {
    const text = @json($letter->wa_text_content);
    navigator.clipboard.writeText(text).then(() => {
        alert("✅ Teks Surat & Link Verifikasi Berhasil Disalin ke Clipboard!\n\nSilakan buka WhatsApp (Aplikasi / Desktop) lalu tekan Ctrl + V (Paste) di grup WA.");
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
}

function copyVerificationLink() {
    const url = @json($letter->verification_url);
    navigator.clipboard.writeText(url).then(() => {
        alert("🔗 Link Verifikasi Dokumen Berhasil Disalin!");
    });
}
</script>
@endsection
