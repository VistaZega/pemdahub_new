<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Digital Resmi - {{ $letter->title ?? 'Yayasan Perguruan PEMBDA Nias' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-200 min-h-screen py-6 px-3 md:py-10 md:px-6">
    <div class="max-w-6xl mx-auto space-y-6">

        @if($isValid && $letter)
        {{-- Status Banner Verified --}}
        <div class="bg-gradient-to-r from-emerald-600 to-teal-700 text-white p-5 rounded-2xl shadow-lg flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 bg-white/20 rounded-xl flex items-center justify-center text-2xl flex-shrink-0">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h2 class="text-base font-extrabold uppercase tracking-wide">DOKUMEN RESMI TERVERIFIKASI & SAH</h2>
                    <p class="text-xs text-emerald-100">Surat terdaftar secara otentik pada Server Resmi PembdaHUB</p>
                </div>
            </div>
            <a href="{{ route('public.letters.verify', $letter->signature_hash) }}" class="bg-white/10 hover:bg-white/20 text-white font-mono text-xs px-4 py-2 rounded-xl transition-all border border-white/20 flex items-center gap-2 shadow-sm">
                <i class="fas fa-lock text-emerald-300"></i> SHA-256: {{ substr($letter->signature_hash, 0, 16) }}...
            </a>
        </div>

        {{-- Paper Container (A4: 210mm, Margin Kiri-Kanan 15mm) --}}
        <div class="bg-white rounded-3xl shadow-2xl border border-slate-300/80 p-6 md:p-10 lg:px-[15mm] lg:py-[12mm] max-w-[210mm] w-full mx-auto space-y-8 relative overflow-hidden">
            
            {{-- Kop Surat Resmi Yayasan --}}
            <div class="border-b-4 border-double border-gray-900 pb-6 text-center relative">
                <div class="flex flex-col md:flex-row items-center justify-center gap-4">
                    <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo Yayasan" class="w-16 md:w-20 h-auto max-h-24 object-contain flex-shrink-0">
                    <div class="text-center">
                        <h1 class="text-base md:text-lg font-extrabold uppercase tracking-tight text-gray-900 leading-tight whitespace-nowrap">Yayasan Perguruan Pembangunan Daerah Nias</h1>
                        <h1 class="text-xl md:text-2xl font-black uppercase tracking-widest text-gray-950 my-0.5">( P E M B D A )</h1>
                        <p class="text-xs font-semibold text-gray-800 mt-0.5">Jl. Pelita No.09 Kel. Ilir Kota Gunungsitoli (22815)</p>
                        <p class="text-xs font-medium text-gray-600 mt-0.5">web : perguruanpembda.com | email : perguruanpembdanias@gmail.com</p>
                    </div>
                </div>
            </div>

            {{-- Judul & Nomor Surat --}}
            <div class="text-center">
                <h2 class="text-base font-extrabold uppercase underline tracking-wide text-gray-900">
                    {{ strtoupper($letter->category_label ?? 'SURAT EDARAN YAYASAN') }}
                </h2>
                <p class="text-xs font-mono font-bold text-gray-700 mt-1">Nomor: {{ $letter->letter_number }}</p>
            </div>

            {{-- Meta Penerima --}}
            @php
                $targetLabels = is_array($letter->recipients) && isset($letter->recipients['target_labels']) 
                    ? $letter->recipients['target_labels'] 
                    : [(is_array($letter->recipients) && isset($letter->recipients['target_label']) ? $letter->recipients['target_label'] : 'Kepala Sekolah se-Perguruan Pembda Nias')];
            @endphp
            <div class="text-sm text-gray-800 space-y-2">
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

            {{-- Naskah Isi Surat Lengkap (Rendered Clean HTML) --}}
            <style>
                .letter-content ol { list-style-type: decimal !important; padding-left: 1.75rem !important; margin-top: 0.5rem !important; margin-bottom: 0.75rem !important; }
                .letter-content ol li { margin-bottom: 0.5rem !important; }
                .letter-content ul { list-style-type: disc !important; padding-left: 1.5rem !important; margin-top: 0.25rem !important; margin-bottom: 0.5rem !important; }
                .letter-content ul li { margin-bottom: 0.25rem !important; }
            </style>
            <div class="prose max-w-none text-xs md:text-sm text-gray-800 leading-relaxed font-sans pt-2 border-t border-gray-100 letter-content">
                {!! $letter->content !!}
            </div>

            {{-- Tanda Tangan Digital & Verifikasi Box --}}
            <div class="pt-6 border-t border-gray-200 flex flex-col md:flex-row items-end justify-between gap-6">
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 w-full md:w-auto text-xs space-y-2">
                    <div class="flex items-center gap-2 text-emerald-700 font-bold">
                        <i class="fas fa-shield-alt text-base"></i>
                        <span>VERIFIKASI KEASLIAN KRIPTOGRAFI</span>
                    </div>
                    <div class="font-mono text-[11px] text-gray-600 break-all bg-white p-2.5 rounded-xl border border-gray-200">
                        <span class="text-gray-400 block text-[9px] uppercase font-bold mb-0.5">SHA-256 Signature Hash:</span>
                        {{ $letter->signature_hash }}
                    </div>
                    <div class="text-[11px] text-gray-500 flex justify-between">
                        <span>Ditandatangani secara digital:</span>
                        <span class="font-bold text-gray-700">{{ $letter->signed_at ? $letter->signed_at->format('d/m/Y H:i') : '' }} WIB</span>
                    </div>
                </div>

                <div class="text-center min-w-[240px]">
                    <p class="text-xs text-gray-600">Ditetapkan di : Gunungsitoli</p>
                    <p class="text-xs text-gray-600">Pada tanggal : {{ \Carbon\Carbon::parse($letter->effective_date)->translatedFormat('d F Y') }}</p>
                    
                    <p class="text-xs font-bold text-gray-900 mt-2 uppercase">YAYASAN PERGURUAN PEMBDA NIAS</p>

                    <div class="my-3 flex justify-center">
                        <div class="border-2 border-emerald-500 rounded-xl p-2 bg-emerald-50/50 flex items-center gap-3">
                            <img src="{{ $letter->qr_code_url }}" alt="QR Code" class="w-16 h-16 rounded border bg-white p-1">
                            <div class="text-left text-[10px]">
                                <span class="bg-emerald-600 text-white font-extrabold px-1.5 py-0.5 rounded uppercase block w-max">TTD DIGITAL SAH</span>
                                <span class="font-bold text-gray-900 block mt-1">{{ $letter->signatory_name }}</span>
                                <span class="text-gray-500 block">{{ $letter->signatory_position }}</span>
                            </div>
                        </div>
                    </div>

                    <p class="text-sm font-extrabold text-gray-900 underline">{{ $letter->signatory_name }}</p>
                    <p class="text-xs font-semibold text-gray-700">{{ $letter->signatory_position }}</p>
                </div>
            </div>

        </div>
        @else
        {{-- Invalid / Not Found Card --}}
        <div class="bg-white rounded-3xl shadow-xl border border-rose-200 p-8 text-center space-y-4">
            <div class="w-16 h-16 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center mx-auto text-3xl">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2 class="text-xl font-extrabold text-rose-600">DOKUMEN TIDAK DITEMUK ATAU TIDAK VALID</h2>
            <p class="text-xs text-gray-600 max-w-md mx-auto">
                Tautan atau Kode Verifikasi SHA-256 yang Anda buka tidak terdaftar pada database resmi Yayasan Perguruan PEMBDA Nias.
            </p>
        </div>
        @endif

        {{-- Footer --}}
        <div class="text-center text-xs text-slate-500 py-4">
            <p>© {{ date('Y') }} Yayasan Perguruan PEMBDA Nias • Portal Resmi: <a href="https://perguruanpembda.com" class="text-violet-700 font-bold hover:underline">perguruanpembda.com</a></p>
        </div>

    </div>
</body>
</html>
