@extends('layouts.orangtua')
@section('title', 'Dashboard - Portal Orang Tua')

@section('content')
<div class="space-y-6">
    <div class="bg-gradient-to-r from-teal-600 to-emerald-600 rounded-2xl p-6 text-white shadow-lg">
        <h1 class="text-xl md:text-2xl font-bold">Selamat Datang, {{ Auth::user()->name }}!</h1>
        <p class="text-white/80 text-sm mt-1">Portal monitoring anak Anda di Yayasan Perguruan PEMBDA Nias</p>
    </div>

    @if($childrenData->count() === 0)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
            <i class="fas fa-user-slash text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">Belum ada data anak yang terhubung ke akun Anda.</p>
            <p class="text-xs text-gray-400 mt-1">Silakan hubungi admin sekolah untuk menghubungkan data anak.</p>
        </div>
    @else
        <div class="grid grid-cols-1 {{ $childrenData->count() > 1 ? 'lg:grid-cols-2' : '' }} gap-6">
            @foreach($childrenData as $data)
                @php $s = $data['student']; @endphp
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-teal-50 to-emerald-50 p-5 border-b border-gray-100">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center overflow-hidden flex-shrink-0">
                                <img src="{{ $s->photo_url }}" class="w-full h-full object-cover" alt="{{ $s->full_name }}">
                            </div>
                            <div class="flex-1 min-w-0">
                                <h2 class="font-bold text-gray-800 truncate">{{ $s->full_name }}</h2>
                                <p class="text-xs text-gray-500">
                                    {{ $data['classroom'] ? $data['classroom']->class_name : 'Belum ada kelas' }}
                                    · {{ $s->school->name ?? '' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Stats --}}
                    <div class="grid grid-cols-3 divide-x divide-gray-100 text-center py-4">
                        <div>
                            <p class="text-xl font-bold {{ $data['avg_score'] >= 70 ? 'text-green-600' : 'text-red-600' }}">{{ $data['avg_score'] }}</p>
                            <p class="text-xs text-gray-500">Rata-rata</p>
                        </div>
                        <div>
                            <p class="text-xl font-bold {{ $data['attendance_pct'] >= 80 ? 'text-blue-600' : 'text-orange-600' }}">{{ $data['attendance_pct'] }}%</p>
                            <p class="text-xs text-gray-500">Kehadiran</p>
                        </div>
                        <div>
                            <p class="text-xl font-bold {{ $data['outstanding'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $data['outstanding'] > 0 ? 'Rp '.number_format($data['outstanding'], 0, ',', '.') : '✅ Lunas' }}
                            </p>
                            <p class="text-xs text-gray-500">Tunggakan s.d. Bulan Ini</p>
                        </div>
                    </div>

                    {{-- Quick Links --}}
                    <div class="grid grid-cols-4 border-t border-gray-100">
                        <a href="{{ route('orangtua.anak.nilai', $s->id) }}" class="flex flex-col items-center gap-1 py-3 hover:bg-green-50 transition text-green-600">
                            <i class="fas fa-chart-bar"></i>
                            <span class="text-xs font-medium">Nilai</span>
                        </a>
                        <a href="{{ route('orangtua.anak.tagihan', $s->id) }}" class="flex flex-col items-center gap-1 py-3 hover:bg-red-50 transition text-red-600">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span class="text-xs font-medium">Tagihan</span>
                        </a>
                        <a href="{{ route('orangtua.anak.absensi', $s->id) }}" class="flex flex-col items-center gap-1 py-3 hover:bg-purple-50 transition text-purple-600">
                            <i class="fas fa-clipboard-check"></i>
                            <span class="text-xs font-medium">Absensi</span>
                        </a>
                        <a href="{{ route('orangtua.anak.jadwal', $s->id) }}" class="flex flex-col items-center gap-1 py-3 hover:bg-blue-50 transition text-blue-600">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="text-xs font-medium">Jadwal</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
