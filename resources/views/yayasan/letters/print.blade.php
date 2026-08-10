<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $letter->letter_number }} - {{ $letter->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 20mm 15mm 20mm;
        }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #111827;
            background-color: #f3f4f6;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .paper {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            padding: 15mm 20mm;
            margin: 10px auto;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            box-sizing: border-box;
        }
        @media print {
            body {
                background: none;
                margin: 0;
                padding: 0;
            }
            .paper {
                box-shadow: none;
                padding: 0;
                margin: 0;
                width: 100%;
                min-height: auto;
            }
            .no-print {
                display: none !important;
            }
        }
        .kop-border {
            border-bottom: 4px double #000;
        }
        .letter-content {
            font-size: 0.875rem !important;
            line-height: 1.35 !important;
        }
        .letter-content ol { list-style-type: decimal !important; padding-left: 1.5rem !important; margin-top: 0.35rem !important; margin-bottom: 0.5rem !important; line-height: 1.25 !important; }
        .letter-content ol li { margin-bottom: 0.2rem !important; }
        .letter-content ul { list-style-type: disc !important; padding-left: 1.25rem !important; margin-top: 0.2rem !important; margin-bottom: 0.35rem !important; line-height: 1.25 !important; }
        .letter-content ul li { margin-bottom: 0.15rem !important; }
    </style>
</head>
<body>
    {{-- Print Control Bar --}}
    <div class="no-print bg-gray-900 text-white p-4 sticky top-0 z-50 flex items-center justify-between shadow-lg">
        <div class="flex items-center gap-3">
            <span class="bg-violet-600 px-3 py-1 rounded text-xs font-bold uppercase">Pratinjau Cetak Surat Digital</span>
            <span class="text-sm font-mono text-gray-300">{{ $letter->letter_number }}</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-4 py-2 rounded-lg text-sm transition-all flex items-center gap-2">
                <i class="fas fa-print"></i> Cetak / Simpan PDF
            </button>
            <button onclick="window.close()" class="bg-gray-700 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded-lg text-sm">
                Tutup
            </button>
        </div>
    </div>

    {{-- Main Printable Paper --}}
    <div class="paper">
        {{-- Kop Surat Resmi Yayasan --}}
        <div class="kop-border pb-4 mb-6 text-center">
            <div class="flex items-center justify-center gap-4">
                <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo Yayasan" class="w-16 md:w-20 h-auto max-h-24 object-contain flex-shrink-0">
                <div class="text-center">
                    <h1 class="text-base md:text-lg font-extrabold uppercase tracking-tight text-black leading-tight whitespace-nowrap">Yayasan Perguruan Pembangunan Daerah Nias</h1>
                    <h1 class="text-xl md:text-2xl font-black uppercase tracking-widest text-black my-0.5">( P E M B D A )</h1>
                    <p class="text-xs font-semibold text-gray-900 mt-0.5">Jl. Pelita No.09 Kel. Ilir Kota Gunungsitoli (22815)</p>
                    <p class="text-[11px] font-medium text-gray-800 mt-0.5">web : perguruanpembda.com | email : perguruanpembdanias@gmail.com</p>
                </div>
            </div>
        </div>

        {{-- Nomor & Judul Surat --}}
        <div class="text-center mb-5">
            <h2 class="text-base font-bold uppercase underline tracking-wider">
                {{ $categories[$letter->category] ?? strtoupper($letter->category) }}
            </h2>
            <p class="text-xs font-mono font-bold text-gray-900 mt-0.5">Nomor: {{ $letter->letter_number }}</p>
        </div>

        {{-- Meta Penerima --}}
        @php
            $targetLabels = is_array($letter->recipients) && isset($letter->recipients['target_labels']) 
                ? $letter->recipients['target_labels'] 
                : [(is_array($letter->recipients) && isset($letter->recipients['target_label']) ? $letter->recipients['target_label'] : 'Kepala Sekolah se-Perguruan Pembda Nias')];
        @endphp
        <div class="mb-5 text-sm">
            <table class="text-sm w-full">
                <tr>
                    <td class="w-28 font-semibold align-top">Perihal</td>
                    <td class="w-4 font-bold align-top">:</td>
                    <td class="font-bold align-top text-gray-900">{{ $letter->title }}</td>
                </tr>
                <tr>
                    <td class="font-semibold align-top">Kepada Yth.</td>
                    <td class="font-bold align-top">:</td>
                    <td class="font-bold align-top text-gray-900">
                        @if(count($targetLabels) > 1)
                            <ol class="list-decimal list-inside space-y-0.5 font-bold">
                                @foreach($targetLabels as $targetItem)
                                    <li>{{ $targetItem }}</li>
                                @endforeach
                            </ol>
                        @else
                            <span>{{ $targetLabels[0] ?? 'Kepala Sekolah se-Perguruan Pembda Nias' }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="font-semibold align-top">Di -</td>
                    <td class="font-bold align-top"></td>
                    <td class="font-bold align-top text-gray-900">Gunungsitoli</td>
                </tr>
            </table>
        </div>

        {{-- Isi Surat --}}
        <div class="text-sm text-justify leading-relaxed mb-8 space-y-3 letter-content">
            {!! $letter->content !!}
        </div>

        {{-- Tanda Tangan & QR Code Verifikasi --}}
        <div class="flex justify-between items-end pt-4 border-t border-gray-200">
            {{-- Left QR Box --}}
            <div class="border border-gray-400 p-2 rounded flex items-center gap-3 bg-gray-50 max-w-sm">
                <img src="{{ $letter->qr_code_url }}" alt="QR Code Verification" class="w-20 h-20 bg-white p-0.5 border border-gray-300">
                <div class="text-[10px] text-gray-800 leading-tight">
                    <p class="font-bold text-emerald-800 text-xs"><i class="fas fa-shield-alt"></i> DOKUMEN RESMI TERVERIFIKASI</p>
                    <p class="mt-1 font-mono text-[9px] text-gray-600">SHA-256: {{ substr($letter->signature_hash, 0, 20) }}...</p>
                    <p class="text-[9px] text-gray-500 mt-0.5">Scan QR Code untuk memvalidasi dokumen secara online di perguruanpembda.com</p>
                </div>
            </div>

            {{-- Right Signatory --}}
            <div class="text-right">
                <p class="text-xs">Gunungsitoli, {{ \Carbon\Carbon::parse($letter->effective_date)->translatedFormat('d F Y') }}</p>
                <p class="text-xs font-bold uppercase mt-1">{{ $letter->signatory_position }}</p>
                <div class="my-3 py-1 flex justify-end">
                    <span class="border border-emerald-600 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">
                        ✔ Signed Digital
                    </span>
                </div>
                <p class="text-sm font-bold underline">{{ $letter->signatory_name }}</p>
            </div>
        </div>
    </div>
</body>
</html>
