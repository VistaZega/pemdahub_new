@extends('layouts.treasurer')

@section('title', 'Pengaturan Gaji - PembdaHUB')

@section('content')
<div class="max-w-5xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="fas fa-sliders-h text-sm"></i>
                </div>
                Pengaturan Gaji
            </h2>
            <p class="text-sm text-gray-500 mt-1">Formula perhitungan gaji, tunjangan, dan honor mengajar</p>
        </div>
    </div>

    {{-- Read-Only Alert --}}
    <div class="p-4 bg-amber-50 border-l-4 border-amber-500 rounded-xl mb-6">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="text-amber-800 font-bold mb-1">Mode Lihat Saja (Read-Only)</p>
                <p class="text-amber-700 text-sm">Formula perhitungan ini bersifat terpusat dan hanya dapat diubah oleh administrator sistem. Bendahara hanya dapat melihat formula aktif.</p>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Section 1: Tunjangan Keluarga & Anak --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6 overflow-hidden">
            <div class="bg-gradient-to-r from-emerald-500 to-green-600 px-6 py-4">
                <h3 class="text-white font-bold flex items-center gap-2">
                    <i class="fas fa-people-roof"></i> Tunjangan Keluarga, Anak & Beras
                </h3>
                <p class="text-emerald-100 text-xs mt-1">Berlaku untuk status kepegawaian: Yayasan (GTY/PTY), sudah menikah</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-heart text-pink-500 mr-1"></i> Tunj. Keluarga
                        </label>
                        <div class="relative">
                            <input type="number" value="{{ $settings['tunjangan_keluarga_persen'] }}" step="0.1" disabled
                                class="w-full border border-gray-200 bg-gray-50 text-gray-500 rounded-xl px-4 py-2.5 pr-10 cursor-not-allowed font-medium">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">%</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">% dari gaji pokok</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-child text-blue-500 mr-1"></i> Tunj. Anak
                        </label>
                        <div class="relative">
                            <input type="number" value="{{ $settings['tunjangan_anak_persen'] }}" step="0.1" disabled
                                class="w-full border border-gray-200 bg-gray-50 text-gray-500 rounded-xl px-4 py-2.5 pr-10 cursor-not-allowed font-medium">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">%</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">% × gaji pokok × jumlah anak</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-hashtag text-indigo-500 mr-1"></i> Maks. Anak
                        </label>
                        <input type="number" value="{{ $settings['tunjangan_anak_max'] }}" disabled
                            class="w-full border border-gray-200 bg-gray-50 text-gray-500 rounded-xl px-4 py-2.5 cursor-not-allowed font-medium">
                        <p class="text-xs text-gray-400 mt-1">Jumlah anak maks yang dihitung</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-wheat-awn text-amber-500 mr-1"></i> Tunj. Beras
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                            <input type="number" value="{{ $settings['tunjangan_beras'] }}" disabled
                                class="w-full border border-gray-200 bg-gray-50 text-gray-500 rounded-xl pl-10 pr-4 py-2.5 cursor-not-allowed font-medium">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Per tanggungan (istri + anak)</p>
                    </div>
                </div>

                {{-- Formula explanation --}}
                <div class="mt-5 bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
                    <i class="fas fa-lightbulb mr-1"></i> <strong>Rumus:</strong>
                    Tunj. Keluarga = <code class="bg-amber-100 px-1 rounded">{{ $settings['tunjangan_keluarga_persen'] }}%</code> × Gaji Pokok |
                    Tunj. Anak = <code class="bg-amber-100 px-1 rounded">{{ $settings['tunjangan_anak_persen'] }}%</code> × Gaji Pokok × Anak (maks <code class="bg-amber-100 px-1 rounded">{{ $settings['tunjangan_anak_max'] }}</code>) |
                    Tunj. Beras = <code class="bg-amber-100 px-1 rounded">Rp {{ number_format($settings['tunjangan_beras'], 0, ',', '.') }}</code> × (1 + Anak)
                </div>
            </div>
        </div>

        {{-- Section 2: Honor Mengajar per Jenjang --}}
        @foreach(['SMP' => ['from-blue-500 to-cyan-600', 'fas fa-school'], 'SMA' => ['from-indigo-500 to-purple-600', 'fas fa-building-columns'], 'SMK' => ['from-violet-500 to-pink-600', 'fas fa-industry']] as $level => $ui)
        @php $lk = strtolower($level); @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6 overflow-hidden">
            <div class="bg-gradient-to-r {{ $ui[0] }} px-6 py-4">
                <h3 class="text-white font-bold flex items-center gap-2">
                    <i class="{{ $ui[1] }}"></i> Honor Mengajar — {{ $level }}
                </h3>
                <p class="text-white/70 text-xs mt-1">Pengaturan jam wajib dan honor per jam untuk jenjang {{ $level }}</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-clock text-teal-500 mr-1"></i> Jam Wajib (Tetap)
                        </label>
                        <div class="relative">
                            <input type="number" value="{{ $settings["jam_wajib_tetap_{$lk}"] }}" disabled
                                class="w-full border border-gray-200 bg-gray-50 text-gray-500 rounded-xl px-4 py-2.5 pr-14 cursor-not-allowed font-medium">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">jam</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">GTY/PTY/PNS</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-clock text-orange-500 mr-1"></i> Jam Wajib (Honor)
                        </label>
                        <div class="relative">
                            <input type="number" value="{{ $settings["jam_wajib_honor_{$lk}"] }}" disabled
                                class="w-full border border-gray-200 bg-gray-50 text-gray-500 rounded-xl px-4 py-2.5 pr-14 cursor-not-allowed font-medium">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">jam</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Guru Honorer</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-money-bill text-green-500 mr-1"></i> Honor/Jam (Tetap)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                            <input type="number" value="{{ $settings["honor_tetap_{$lk}"] }}" disabled
                                class="w-full border border-gray-200 bg-gray-50 text-gray-500 rounded-xl pl-10 pr-4 py-2.5 cursor-not-allowed font-medium">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">GTY/PTY/PNS per jam</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-money-bill-wave text-emerald-500 mr-1"></i> Honor/Jam (Honorer)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                            <input type="number" value="{{ $settings["honor_honorer_{$lk}"] }}" disabled
                                class="w-full border border-gray-200 bg-gray-50 text-gray-500 rounded-xl pl-10 pr-4 py-2.5 cursor-not-allowed font-medium">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Guru Honorer per jam</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-money-bill-wave text-cyan-500 mr-1"></i> Honor/Jam (Percobaan)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                            <input type="number" value="{{ $settings["honor_percobaan_{$lk}"] }}" disabled
                                class="w-full border border-gray-200 bg-gray-50 text-gray-500 rounded-xl pl-10 pr-4 py-2.5 cursor-not-allowed font-medium">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Status Percobaan per jam</p>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        {{-- Section 3: Potongan Wajib --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6 overflow-hidden">
            <div class="bg-gradient-to-r from-red-500 to-rose-600 px-6 py-4">
                <h3 class="text-white font-bold flex items-center gap-2">
                    <i class="fas fa-hand-holding-dollar"></i> Potongan Wajib (Deductions)
                </h3>
                <p class="text-white/70 text-xs mt-1">Potongan otomatis dari gaji pokok (BPJS, PPh 21, dll)</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-hospital text-red-500 mr-1"></i> BPJS Kesehatan
                        </label>
                        <div class="relative">
                            <input type="number" value="{{ $settings['bpjs_kesehatan_persen'] }}" step="0.1" disabled
                                class="w-full border border-gray-200 bg-gray-50 text-gray-500 rounded-xl px-4 py-2.5 pr-10 cursor-not-allowed font-medium">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">%</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Potongan kesehatan dari gaji pokok</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-briefcase text-orange-500 mr-1"></i> BPJS Ketenagakerjaan
                        </label>
                        <div class="relative">
                            <input type="number" value="{{ $settings['bpjs_ketenagakerjaan_persen'] }}" step="0.1" disabled
                                class="w-full border border-gray-200 bg-gray-50 text-gray-500 rounded-xl px-4 py-2.5 pr-10 cursor-not-allowed font-medium">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">%</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Potongan ketenagakerjaan dari gaji pokok</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Back Button --}}
        <div class="flex justify-end mb-8">
            <a href="{{ route('treasurer.dashboard') }}" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition font-medium shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
