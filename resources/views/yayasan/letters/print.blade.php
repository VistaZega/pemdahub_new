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
            font-family: 'Times New Roman', Times, serif;
            color: #111827;
            background-color: #f3f4f6;
        }
        .paper {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 10px auto;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        @media print {
            body {
                background: none;
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
            <div class="flex items-center justify-center gap-5">
                <div class="w-20 h-20 bg-violet-900 text-white rounded-full flex items-center justify-center font-extrabold text-4xl border-2 border-amber-400">
                    P
                </div>
                <div class="text-center">
                    <h1 class="text-xl font-bold uppercase tracking-wider text-black leading-tight">YAYASAN PERGURUAN PEMBDA NIAS</h1>
                    <p class="text-xs font-bold uppercase text-gray-800 tracking-normal mt-0.5">SMPS SWASTA PEMBDA 2 • SMA SWASTA PEMBDA 1 • SMKS SWASTA PEMBDA NIAS</p>
                    <p class="text-xs text-gray-800 mt-1">Alamat: Jl. Pelita No.09, Gunungsitoli, Kabupaten Nias, Sumatera Utara (Kode Pos: 22812)</p>
                    <p class="text-[11px] text-gray-700">Website: https://perguruanpembda.com | Email: yayasan@perguruanpembda.com</p>
                </div>
            </div>
        </div>

        {{-- Nomor & Judul Surat --}}
        <div class="text-center mb-6">
            <h2 class="text-base font-bold uppercase underline tracking-wider">
                {{ $categories[$letter->category] ?? strtoupper($letter->category) }}
            </h2>
            <p class="text-xs font-mono font-bold text-gray-900 mt-0.5">Nomor: {{ $letter->letter_number }}</p>
        </div>

        {{-- Meta Penerima --}}
        @php
            $targetLabel = is_array($letter->recipients) && isset($letter->recipients['target_label']) 
                ? $letter->recipients['target_label'] 
                : 'Kepala Sekolah se-Perguruan Pembda Nias';
        @endphp
        <div class="mb-6 text-sm space-y-1">
            <table class="text-sm">
                <tr>
                    <td class="w-28 font-semibold align-top">Perihal</td>
                    <td class="w-4 font-bold align-top">:</td>
                    <td class="font-bold align-top text-gray-900">{{ $letter->title }}</td>
                </tr>
                <tr>
                    <td class="font-semibold align-top">Kepada Yth.</td>
                    <td class="font-bold align-top">:</td>
                    <td class="font-bold align-top text-gray-900">{{ $targetLabel }}</td>
                </tr>
                <tr>
                    <td class="font-semibold align-top">Di Tempat</td>
                    <td class="font-bold align-top">:</td>
                    <td class="align-top text-gray-800">Gunungsitoli</td>
                </tr>
            </table>
        </div>

        {{-- Isi Surat --}}
        <div class="text-sm text-justify leading-relaxed mb-10 space-y-3 font-serif">
            {!! $letter->content !!}
        </div>

        {{-- Tanda Tangan & QR Code Verifikasi --}}
        <div class="flex justify-between items-end pt-4">
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
                <div class="my-4 py-1 flex justify-end">
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
