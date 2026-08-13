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

            <!-- Tema Beranda Hari Besar (Event Themes) -->
            <div x-data="{ selectedTheme: '{{ $settings['homepage_theme'] ?? 'regular' }}' }" class="lg:col-span-2 bg-white rounded-2xl shadow-lg p-6 space-y-5 border-2 border-indigo-100">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-palette text-indigo-600"></i> Tema Beranda Hari Besar &amp; Perayaan (Event Theme)
                    </h3>
                    <span class="text-xs font-semibold px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full border border-indigo-200">
                        Aman &amp; Non-Destruktif (Layout Utuh)
                    </span>
                </div>
                <p class="text-xs text-gray-500 font-medium">
                    Pilih suasana &amp; tema visual untuk halaman depan PembdaHUB pada momen hari besar tertentu. Tampilan beranda akan menyesuaikan banner sambutan, aksen warna, dan perhiasan animasi tanpa merubah struktur layout.
                </p>
                
                <!-- Dropdown Form Selector (Fail-safe) -->
                <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-900">Pilihan Tema Aktif Beranda:</label>
                        <p class="text-xs text-gray-500">Klik salah satu kartu di bawah atau pilih langsung dari menu di sebelah kanan.</p>
                    </div>
                    <select name="homepage_theme" x-model="selectedTheme" class="px-4 py-2.5 border-2 border-indigo-300 rounded-xl font-bold text-sm bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500 shadow-sm cursor-pointer min-w-[240px]">
                        <option value="regular">🌟 Tema Reguler (Default PembdaHUB)</option>
                        <option value="kemerdekaan">🇮🇩 Hari Kemerdekaan RI (17 Agt)</option>
                        <option value="paskah">✝️ Hari Paskah</option>
                        <option value="natal">🎄 Hari Natal &amp; Tahun Baru</option>
                        <option value="pahlawan">🎖️ Hari Pahlawan (10 Nov)</option>
                        <option value="pendidikan">📚 Hari Pendidikan Nasional (2 Mei)</option>
                    </select>
                </div>

                <!-- Visual Interactive Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-2">
                    <!-- Tema Reguler -->
                    <div @click="selectedTheme = 'regular'" 
                         :class="selectedTheme === 'regular' ? 'border-indigo-600 bg-indigo-50/60 ring-2 ring-indigo-500 shadow-md' : 'border-gray-200 hover:border-indigo-300 bg-white'"
                         class="relative flex flex-col p-4 border-2 rounded-2xl cursor-pointer transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-extrabold text-gray-900 flex items-center gap-2">
                                <span>🌟</span> <span>Tema Reguler</span>
                            </span>
                            <span :class="selectedTheme === 'regular' ? 'bg-indigo-600 border-indigo-600 text-white' : 'border-gray-300 bg-white'" class="w-5 h-5 rounded-full border-2 flex items-center justify-center text-[10px] font-bold">
                                <i x-show="selectedTheme === 'regular'" class="fas fa-check"></i>
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mb-3">Tampilan standar PembdaHUB (Modern Indigo Premium).</p>
                        <div class="mt-auto flex items-center gap-1.5">
                            <span class="w-5 h-5 rounded-md bg-indigo-600 inline-block"></span>
                            <span class="w-5 h-5 rounded-md bg-slate-900 inline-block"></span>
                            <span class="w-5 h-5 rounded-md bg-amber-400 inline-block"></span>
                        </div>
                    </div>

                    <!-- Tema Hari Kemerdekaan -->
                    <div @click="selectedTheme = 'kemerdekaan'" 
                         :class="selectedTheme === 'kemerdekaan' ? 'border-red-600 bg-red-50/60 ring-2 ring-red-500 shadow-md' : 'border-gray-200 hover:border-red-300 bg-white'"
                         class="relative flex flex-col p-4 border-2 rounded-2xl cursor-pointer transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-extrabold text-gray-900 flex items-center gap-2">
                                <span>🇮🇩</span> <span>Hari Kemerdekaan RI</span>
                            </span>
                            <span :class="selectedTheme === 'kemerdekaan' ? 'bg-red-600 border-red-600 text-white' : 'border-gray-300 bg-white'" class="w-5 h-5 rounded-full border-2 flex items-center justify-center text-[10px] font-bold">
                                <i x-show="selectedTheme === 'kemerdekaan'" class="fas fa-check"></i>
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mb-3">Nuansa Merah-Putih, Banner Dirgahayu RI &amp; pita melayang.</p>
                        <div class="mt-auto flex items-center gap-1.5">
                            <span class="w-5 h-5 rounded-md bg-red-600 border border-gray-300 inline-block"></span>
                            <span class="w-5 h-5 rounded-md bg-white border border-gray-300 inline-block"></span>
                            <span class="w-5 h-5 rounded-md bg-amber-400 inline-block"></span>
                        </div>
                    </div>

                    <!-- Tema Hari Paskah -->
                    <div @click="selectedTheme = 'paskah'" 
                         :class="selectedTheme === 'paskah' ? 'border-purple-600 bg-purple-50/60 ring-2 ring-purple-500 shadow-md' : 'border-gray-200 hover:border-purple-300 bg-white'"
                         class="relative flex flex-col p-4 border-2 rounded-2xl cursor-pointer transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-extrabold text-gray-900 flex items-center gap-2">
                                <span>✝️</span> <span>Hari Paskah</span>
                            </span>
                            <span :class="selectedTheme === 'paskah' ? 'bg-purple-600 border-purple-600 text-white' : 'border-gray-300 bg-white'" class="w-5 h-5 rounded-full border-2 flex items-center justify-center text-[10px] font-bold">
                                <i x-show="selectedTheme === 'paskah'" class="fas fa-check"></i>
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mb-3">Nuansa Ungu Paskah &amp; Emas Harapan Kebangkitan.</p>
                        <div class="mt-auto flex items-center gap-1.5">
                            <span class="w-5 h-5 rounded-md bg-purple-700 inline-block"></span>
                            <span class="w-5 h-5 rounded-md bg-amber-300 inline-block"></span>
                            <span class="w-5 h-5 rounded-md bg-indigo-900 inline-block"></span>
                        </div>
                    </div>

                    <!-- Tema Hari Natal -->
                    <div @click="selectedTheme = 'natal'" 
                         :class="selectedTheme === 'natal' ? 'border-emerald-600 bg-emerald-50/60 ring-2 ring-emerald-500 shadow-md' : 'border-gray-200 hover:border-emerald-300 bg-white'"
                         class="relative flex flex-col p-4 border-2 rounded-2xl cursor-pointer transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-extrabold text-gray-900 flex items-center gap-2">
                                <span>🎄</span> <span>Hari Natal &amp; Tahun Baru</span>
                            </span>
                            <span :class="selectedTheme === 'natal' ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-gray-300 bg-white'" class="w-5 h-5 rounded-full border-2 flex items-center justify-center text-[10px] font-bold">
                                <i x-show="selectedTheme === 'natal'" class="fas fa-check"></i>
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mb-3">Nuansa Hijau Zamrud, Merah Crimson &amp; Hujan Salju.</p>
                        <div class="mt-auto flex items-center gap-1.5">
                            <span class="w-5 h-5 rounded-md bg-emerald-700 inline-block"></span>
                            <span class="w-5 h-5 rounded-md bg-rose-600 inline-block"></span>
                            <span class="w-5 h-5 rounded-md bg-amber-300 inline-block"></span>
                        </div>
                    </div>

                    <!-- Tema Hari Pahlawan -->
                    <div @click="selectedTheme = 'pahlawan'" 
                         :class="selectedTheme === 'pahlawan' ? 'border-amber-700 bg-amber-50/60 ring-2 ring-amber-600 shadow-md' : 'border-gray-200 hover:border-amber-300 bg-white'"
                         class="relative flex flex-col p-4 border-2 rounded-2xl cursor-pointer transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-extrabold text-gray-900 flex items-center gap-2">
                                <span>🎖️</span> <span>Hari Pahlawan (10 Nov)</span>
                            </span>
                            <span :class="selectedTheme === 'pahlawan' ? 'bg-amber-700 border-amber-700 text-white' : 'border-gray-300 bg-white'" class="w-5 h-5 rounded-full border-2 flex items-center justify-center text-[10px] font-bold">
                                <i x-show="selectedTheme === 'pahlawan'" class="fas fa-check"></i>
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mb-3">Nuansa Merah Marun &amp; Emas Semangat Perjuangan.</p>
                        <div class="mt-auto flex items-center gap-1.5">
                            <span class="w-5 h-5 rounded-md bg-rose-950 inline-block"></span>
                            <span class="w-5 h-5 rounded-md bg-amber-500 inline-block"></span>
                            <span class="w-5 h-5 rounded-md bg-amber-800 inline-block"></span>
                        </div>
                    </div>

                    <!-- Tema Hari Pendidikan -->
                    <div @click="selectedTheme = 'pendidikan'" 
                         :class="selectedTheme === 'pendidikan' ? 'border-sky-600 bg-sky-50/60 ring-2 ring-sky-500 shadow-md' : 'border-gray-200 hover:border-sky-300 bg-white'"
                         class="relative flex flex-col p-4 border-2 rounded-2xl cursor-pointer transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-extrabold text-gray-900 flex items-center gap-2">
                                <span>📚</span> <span>Hari Pendidikan (2 Mei)</span>
                            </span>
                            <span :class="selectedTheme === 'pendidikan' ? 'bg-sky-600 border-sky-600 text-white' : 'border-gray-300 bg-white'" class="w-5 h-5 rounded-full border-2 flex items-center justify-center text-[10px] font-bold">
                                <i x-show="selectedTheme === 'pendidikan'" class="fas fa-check"></i>
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mb-3">Nuansa Biru Tut Wuri Handayani &amp; Emas Akademis.</p>
                        <div class="mt-auto flex items-center gap-1.5">
                            <span class="w-5 h-5 rounded-md bg-sky-700 inline-block"></span>
                            <span class="w-5 h-5 rounded-md bg-amber-400 inline-block"></span>
                            <span class="w-5 h-5 rounded-md bg-blue-950 inline-block"></span>
                        </div>
                    </div>
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
