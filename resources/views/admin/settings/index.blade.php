@extends('layouts.admin')

@section('title', 'Pengaturan Sistem')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg">
                    <i class="fas fa-cogs text-white text-3xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Pengaturan Sistem</h1>
                    <p class="text-gray-600 mt-1">Konfigurasi berbagai fitur dan parameter utama aplikasi</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-xl">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <p class="text-green-700 font-medium">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Kartu Pintasan Pengaturan: Biaya Administrasi -->
        <a href="{{ route('admin.settings.late-fees') }}" class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 block border-t-4 border-orange-500">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center text-xl">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <span class="text-gray-400"><i class="fas fa-chevron-right"></i></span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Biaya Administrasi (Late Fees)</h3>
            <p class="text-sm text-gray-600">Konfigurasi denda keterlambatan pembayaran SPP/Tagihan beserta masa tenggang.</p>
        </a>

        <!-- Kartu Pintasan Pengaturan: WhatsApp & Otomatisasi -->
        <a href="{{ route('admin.settings.whatsapp') }}" class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 block border-t-4 border-emerald-500">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <span class="text-gray-400"><i class="fas fa-chevron-right"></i></span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">WhatsApp & Otomatisasi</h3>
            <p class="text-sm text-gray-600">Kelola status gateway dan daftar saklar (On/Off) otomatisasi pengiriman pesan ke wali murid.</p>
        </a>

        <!-- Kartu Pintasan Pengaturan: Konversi Predikat Rapor -->
        <a href="{{ route('admin.settings.report-cards') }}" class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 block border-t-4 border-indigo-600">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <span class="text-gray-400"><i class="fas fa-chevron-right"></i></span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Konversi Predikat Rapor</h3>
            <p class="text-sm text-gray-600">Konfigurasi rumus konversi nilai angka ke predikat (A, B, C, D) per tingkat kelas SMP & SMA/SMK.</p>
        </a>

        <!-- Tempat placeholder untuk pengaturan lain -->
        @foreach($settings as $group => $items)
        <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border-t-4 border-indigo-500">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl">
                    <i class="fas fa-sliders-h"></i>
                </div>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2 capitalize">{{ str_replace('_', ' ', $group) }}</h3>
            <div class="text-sm text-gray-600 space-y-2">
                @foreach($items->take(3) as $item)
                <div class="flex items-center justify-between">
                    <span class="truncate pr-4">{{ $item->key }}</span>
                    <span class="font-semibold text-xs bg-gray-100 px-2 py-1 rounded">{{ Str::limit($item->value, 15) }}</span>
                </div>
                @endforeach
                @if($items->count() > 3)
                <div class="text-xs text-center text-gray-400 mt-2">+ {{ $items->count() - 3 }} pengaturan lainnya</div>
                @endif
            </div>
        </div>
        @endforeach

    </div>
</div>
@endsection
