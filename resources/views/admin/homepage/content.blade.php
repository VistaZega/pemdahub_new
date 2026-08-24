@extends('layouts.admin')

@section('title', 'Konten Umum Homepage')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg">
                <i class="fas fa-cog text-white text-lg"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Konten Umum Homepage</h1>
                <p class="text-gray-600 mt-1">Kelola statistik, sambutan, dan informasi pendaftaran</p>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 rounded-lg p-4">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-500 mr-3"></i>
            <p class="text-green-800 font-medium">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    <form action="{{ route('admin.homepage-content.update') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Statistik -->
            <div class="bg-white rounded-2xl shadow-lg p-6 space-y-5">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-chart-line text-amber-500"></i> Statistik Yayasan
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun Berdiri</label>
                        <input type="text" name="stat_tahun" value="{{ $settings['stat_tahun'] }}" 
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Total Siswa</label>
                        <input type="text" name="stat_siswa" value="{{ $settings['stat_siswa'] }}" 
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Unit Sekolah</label>
                        <input type="text" name="stat_unit" value="{{ $settings['stat_unit'] }}" 
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Program Keahlian</label>
                        <input type="text" name="stat_program" value="{{ $settings['stat_program'] }}" 
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>
            </div>

            <!-- PSB / Pendaftaran -->
            <div class="bg-white rounded-2xl shadow-lg p-6 space-y-5">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-user-plus text-emerald-500"></i> Info Pendaftaran (PSB)
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun Pelajaran</label>
                        <input type="text" name="psb_tp" value="{{ $settings['psb_tp'] }}" 
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500">
                        <p class="text-[10px] text-gray-400 mt-1">Contoh: 2026/2027</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Periode Pendaftaran</label>
                        <input type="text" name="psb_periode" value="{{ $settings['psb_periode'] }}" 
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500">
                        <p class="text-[10px] text-gray-400 mt-1">Contoh: 1 Feb – 30 Jun 2026</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Label Status</label>
                        <input type="text" name="psb_status" value="{{ $settings['psb_status'] }}" 
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500">
                        <p class="text-[10px] text-gray-400 mt-1">Contoh: Dibuka / Segera Hadir / Ditutup</p>
                    </div>
                </div>
            </div>

            <!-- Global Theme Studio & Design Engine -->
            <div x-data="{ selectedTheme: '{{ $settings['homepage_theme'] ?? 'cerah_ceria' }}' }" class="lg:col-span-2 bg-white rounded-3xl shadow-xl p-6 sm:p-8 space-y-6 border-2 border-indigo-100">
                <div class="flex items-center justify-between flex-wrap gap-3 pb-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-xl font-black text-gray-900 flex items-center gap-2.5">
                            <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white shadow-md shadow-indigo-200">
                                <i class="fas fa-palette text-lg"></i>
                            </span>
                            <span>Global Theme Studio &amp; Design Engine</span>
                        </h3>
                        <p class="text-xs text-gray-500 font-medium mt-1">
                            Pilih tema visual dan suasana antarmuka PembdaHUB. Setiap tema akan mengubah warna, gradasi, bayangan, dan elemen secara instan ke seluruh website.
                        </p>
                    </div>
                    <span class="text-xs font-extrabold px-3.5 py-1.5 bg-emerald-50 text-emerald-700 rounded-full border border-emerald-200 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        1-Klik Ganti Tema Aktif
                    </span>
                </div>
                
                <!-- Hidden Input for Form Submission -->
                <input type="hidden" name="homepage_theme" :value="selectedTheme">

                <!-- Interactive Theme Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($themes as $key => $theme)
                    <div @click="selectedTheme = '{{ $key }}'" 
                         :class="selectedTheme === '{{ $key }}' ? 'border-indigo-600 bg-indigo-50/50 ring-4 ring-indigo-500/20 shadow-lg scale-[1.02]' : 'border-gray-200 hover:border-indigo-300 hover:bg-gray-50/60 bg-white shadow-sm'"
                         class="relative flex flex-col p-5 border-2 rounded-2xl cursor-pointer transition-all duration-300 group">
                        
                        <!-- Header & Active Radio Indicator -->
                        <div class="flex items-start justify-between gap-2 mb-2.5">
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-indigo-600 font-bold group-hover:bg-indigo-100 transition-colors">
                                    <i class="{{ $theme['icon'] }}"></i>
                                </span>
                                <div>
                                    <div class="text-sm font-black text-gray-900 leading-tight">{{ $theme['name'] }}</div>
                                    <span class="inline-block mt-0.5 text-[10px] font-extrabold px-2 py-0.5 rounded-md border {{ $theme['badge_color'] }}">
                                        {{ $theme['badge'] }}
                                    </span>
                                </div>
                            </div>
                            <span :class="selectedTheme === '{{ $key }}' ? 'bg-indigo-600 border-indigo-600 text-white shadow-sm' : 'border-gray-300 bg-white text-transparent'" 
                                  class="w-6 h-6 rounded-full border-2 flex items-center justify-center text-xs font-bold transition-all shrink-0">
                                <i class="fas fa-check"></i>
                            </span>
                        </div>

                        <!-- Description -->
                        <p class="text-xs text-gray-600 line-clamp-2 mb-4 leading-relaxed font-normal">
                            {{ $theme['description'] }}
                        </p>

                        <!-- Color Swatches Bar -->
                        <div class="mt-auto pt-3 border-t border-gray-100/80 flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Palet Warna:</span>
                            <div class="flex items-center gap-1.5">
                                @foreach($theme['swatches'] as $color)
                                <span class="w-5 h-5 rounded-full border border-gray-300 shadow-xs" style="background-color: {{ $color }};" title="{{ $color }}"></span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Sambutan Ketua -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg p-6 space-y-5">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-quote-left text-rose-500"></i> Sambutan Ketua Yayasan
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Ketua</label>
                            <input type="text" name="ketua_nama" value="{{ $settings['ketua_nama'] }}" 
                                class="w-full px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-rose-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Jabatan</label>
                            <input type="text" name="ketua_jabatan" value="{{ $settings['ketua_jabatan'] }}" 
                                class="w-full px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-rose-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Teks Sambutan (Kutipan)</label>
                        <textarea name="ketua_quote" rows="4" 
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-rose-500">{{ $settings['ketua_quote'] }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-gray-800 to-gray-900 text-white rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                <i class="fas fa-save mr-2"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
