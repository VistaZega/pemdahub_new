<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PembdaHUB - Shared View')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen text-slate-800 flex flex-col">
    <!-- Clean Minimal Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 py-3">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center text-white shadow-md">
                        <i class="fas fa-chart-line text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 leading-tight">Pembda<span class="text-red-500">HUB</span></h1>
                        <p class="text-xs text-gray-500 font-semibold tracking-wide">SHARED REPORT VIEW</p>
                    </div>
                </div>
                <div>
                    <a href="{{ url('/') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 transition">
                        Ke Beranda Utama
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-sm text-gray-500 font-medium">
                &copy; {{ date('Y') }} Yayasan Perguruan Pembda Nias. All rights reserved.
            </p>
            <p class="text-xs text-gray-400 mt-1">Sistem Manajemen Terpadu PembdaHUB</p>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
