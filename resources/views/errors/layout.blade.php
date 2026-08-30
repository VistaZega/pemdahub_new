@php
    $diag = null;
    if (isset($exception) && $exception instanceof \Throwable && class_exists('\App\Services\ErrorDiagnosticService')) {
        $diag = \App\Services\ErrorDiagnosticService::diagnose($exception);
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - PembdaHUB</title>
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-slate-100 to-slate-200 min-h-screen flex items-center justify-center px-4 py-8">
    <div class="max-w-xl w-full text-center">
        {{-- Error Icon --}}
        <div class="mb-4">
            @yield('icon')
        </div>

        {{-- Error Code --}}
        <h1 class="text-7xl sm:text-8xl font-black text-transparent bg-clip-text @yield('gradient', 'bg-gradient-to-r from-indigo-500 to-purple-600') mb-2 tracking-tight">
            @yield('code')
        </h1>

        {{-- Error Title --}}
        <h2 class="text-xl sm:text-2xl font-bold text-slate-800 mb-2">
            @yield('heading')
        </h2>

        {{-- Error Message --}}
        <p class="text-slate-600 text-xs sm:text-sm mb-6 leading-relaxed">
            @yield('description')
        </p>

        {{-- Smart Indonesian Diagnostic Card --}}
        @if($diag)
        <div class="mb-6 text-left bg-white border-2 border-slate-300 rounded-2xl p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <span class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                    <i class="fas fa-stethoscope text-emerald-600"></i> Hasil Diagnostik Pintar PembdaHUB
                </span>
                <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">
                    {{ $diag['badge'] }}
                </span>
            </div>
            
            <div class="space-y-2.5 text-xs text-slate-700">
                <div>
                    <span class="font-bold text-slate-900 block mb-0.5">🛑 Masalah (Penyebab):</span>
                    <p class="text-slate-600 leading-relaxed">{!! $diag['problem'] !!}</p>
                </div>
                
                <div>
                    <span class="font-bold text-slate-900 block mb-0.5">⚠️ Dampak / Status Bahaya:</span>
                    <p class="leading-relaxed"><span class="font-semibold">{{ $diag['danger_label'] }}</span> — {!! $diag['impact'] !!}</p>
                </div>
                
                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-950">
                    <span class="font-bold block text-emerald-900 mb-0.5">🛠️ Solusi yang Perlu Dilakukan:</span>
                    <p class="leading-relaxed text-emerald-900">{!! $diag['solution'] !!}</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            @hasSection('actions')
                @yield('actions')
            @else
                <a href="{{ url('/') }}"
                   class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Ke Beranda
                </a>
                <button onclick="history.back()"
                        class="inline-flex items-center justify-center px-6 py-3 bg-white text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors border border-gray-200 shadow-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali
                </button>
            @endif
        </div>

        {{-- Footer --}}
        <p class="mt-12 text-xs text-gray-400">
            &copy; {{ date('Y') }} Pembda<span style="color:#ef4444">HUB</span> — Sistem Manajemen Sekolah Terpadu
        </p>
    </div>
</body>
</html>
