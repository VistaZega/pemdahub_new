<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Keaslian Surat Digital - Yayasan Perguruan PEMBDA Nias</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-10 px-4">
    <div class="max-w-2xl mx-auto space-y-6">

        {{-- Main Verification Card --}}
        <div class="bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden">
            {{-- Header Band --}}
            <div class="bg-gradient-to-r from-violet-900 via-purple-900 to-indigo-900 p-6 text-white text-center relative overflow-hidden">
                <div class="absolute -left-10 -bottom-10 opacity-10 text-9xl">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="w-16 h-16 bg-white p-2 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-md">
                    <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo Yayasan" class="w-full h-full object-contain">
                </div>
                <h1 class="text-lg md:text-xl font-extrabold tracking-wide uppercase">Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)</h1>
                <p class="text-xs text-violet-200 mt-1">Jl. Pelita No.09 Kel. Ilir Kota Gunungsitoli (22815)</p>
                <p class="text-[11px] text-violet-300">web : perguruanpembda.com | email : perguruanpembdanias@gmail.com</p>
            </div>

            @if($isValid && $letter)
            {{-- Status Banner Valid --}}
            <div class="bg-emerald-500 text-white p-4 text-center flex items-center justify-center gap-3">
                <i class="fas fa-check-circle text-2xl"></i>
                <div class="text-left">
                    <h2 class="text-sm font-extrabold uppercase tracking-wide">DOKUMEN RESMI TERVERIFIKASI & SAH</h2>
                    <p class="text-xs text-emerald-100">Surat ini terdaftar secara sah pada Server Resmi PembdaHUB</p>
                </div>
            </div>

            {{-- Document Details --}}
            <div class="p-6 md:p-8 space-y-6">
                {{-- Metadata Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200 text-xs">
                    <div>
                        <span class="text-slate-400 font-semibold block uppercase">Nomor Surat</span>
                        <span class="font-mono font-bold text-slate-900 text-sm">{{ $letter->letter_number }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-semibold block uppercase">Tanggal Ditetapkan</span>
                        <span class="font-bold text-slate-900">{{ \Carbon\Carbon::parse($letter->effective_date)->translatedFormat('d F Y') }}</span>
                    </div>
                    <div class="md:col-span-2">
                        <span class="text-slate-400 font-semibold block uppercase">Perihal Surat</span>
                        <span class="font-bold text-violet-900 text-sm">{{ $letter->title }}</span>
                    </div>
                    @php
                        $targetLabels = is_array($letter->recipients) && isset($letter->recipients['target_labels']) 
                            ? $letter->recipients['target_labels'] 
                            : [(is_array($letter->recipients) && isset($letter->recipients['target_label']) ? $letter->recipients['target_label'] : 'Kepala Sekolah se-Perguruan Pembda Nias')];
                    @endphp
                    <div class="md:col-span-2">
                        <span class="text-slate-400 font-semibold block uppercase mb-1">Tujuan Penerima</span>
                        @if(count($targetLabels) > 1)
                            <ol class="list-decimal list-inside space-y-0.5 font-semibold text-slate-800">
                                @foreach($targetLabels as $targetItem)
                                    <li>{{ $targetItem }}</li>
                                @endforeach
                            </ol>
                        @else
                            <span class="font-semibold text-slate-800">{{ $targetLabels[0] ?? 'Kepala Sekolah se-Perguruan Pembda Nias' }}</span>
                        @endif
                    </div>
                </div>

                {{-- Signatory Info --}}
                <div class="bg-amber-50/70 border border-amber-200/80 p-4 rounded-2xl flex items-center justify-between gap-4">
                    <div>
                        <span class="text-[10px] font-bold text-amber-800 uppercase tracking-wider block">Penandatangan Digital</span>
                        <h4 class="text-sm font-extrabold text-slate-900">{{ $letter->signatory_name }}</h4>
                        <p class="text-xs text-slate-600 font-medium">{{ $letter->signatory_position }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-800 text-[11px] font-bold px-2.5 py-1 rounded-full border border-emerald-300">
                            <i class="fas fa-certificate text-emerald-600"></i> SIGNED
                        </span>
                        <span class="text-[10px] text-slate-400 block mt-1 font-mono">
                            {{ $letter->signed_at ? $letter->signed_at->format('d/m/Y H:i') : '' }} WIB
                        </span>
                    </div>
                </div>

                {{-- Cryptographic Integrity --}}
                <div class="p-4 bg-slate-900 text-white rounded-2xl space-y-2">
                    <div class="flex items-center justify-between text-xs text-slate-400">
                        <span class="font-bold text-slate-200 flex items-center gap-1.5">
                            <i class="fas fa-lock text-emerald-400"></i> Cryptographic Hash Integrity
                        </span>
                        <span class="font-mono text-[10px] bg-slate-800 px-2 py-0.5 rounded text-emerald-400">SHA-256</span>
                    </div>
                    <div class="bg-slate-950 p-3 rounded-xl font-mono text-[11px] text-slate-300 break-all border border-slate-800">
                        {{ $letter->signature_hash }}
                    </div>
                </div>
            </div>
            @else
            {{-- Invalid / Not Found Banner --}}
            <div class="bg-rose-500 text-white p-4 text-center flex items-center justify-center gap-3">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
                <div class="text-left">
                    <h2 class="text-sm font-extrabold uppercase tracking-wide">DOKUMEN TIDAK DITEMUK ATAU TIDAK VALID</h2>
                    <p class="text-xs text-rose-100">Kode verifikasi QR Code tidak terdaftar pada database resmi PembdaHUB.</p>
                </div>
            </div>
            <div class="p-8 text-center text-slate-600 text-sm">
                <p>Silakan pastikan bahwa QR Code yang Anda pindai berasal dari dokumen Surat Resmi yang diterbitkan oleh <strong>Yayasan Perguruan PEMBDA Nias</strong>.</p>
            </div>
            @endif

            {{-- Footer --}}
            <div class="bg-slate-50 p-4 border-t border-slate-200 text-center text-xs text-slate-500">
                <p>© {{ date('Y') }} Yayasan Perguruan PEMBDA Nias. Official Portal: <a href="https://perguruanpembda.com" class="text-violet-700 font-bold hover:underline">perguruanpembda.com</a></p>
            </div>
        </div>
    </div>
</body>
</html>
