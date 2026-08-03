@extends('layouts.admin')

@section('title', 'Pengaturan Gaji - PembdaHUB')

@section('content')
<div class="max-w-5xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="fas fa-sliders text-sm"></i>
                </div>
                Pengaturan Gaji
            </h2>
            <p class="text-sm text-gray-500 mt-1">Kelola formula perhitungan gaji, tunjangan, dan honor mengajar</p>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded-xl mb-6"><p class="text-green-700"><i class="fas fa-check-circle mr-1"></i> {{ session('success') }}</p></div>
    @endif

    @if($errors->any())
    <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-xl mb-6">
        <p class="text-red-700 font-semibold"><i class="fas fa-exclamation-triangle mr-1"></i> Terdapat kesalahan:</p>
        <ul class="list-disc ml-5 mt-1 text-sm text-red-600">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('admin.payroll.settings.update') }}" method="POST" id="settingsForm">
        @csrf
        @method('PUT')

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
                            <input type="number" name="tunjangan_keluarga_persen" value="{{ old('tunjangan_keluarga_persen', $settings['tunjangan_keluarga_persen']) }}" step="0.1" min="0" max="100"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 pr-10 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">%</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">% dari gaji pokok</p>
                        @error('tunjangan_keluarga_persen') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-child text-blue-500 mr-1"></i> Tunj. Anak
                        </label>
                        <div class="relative">
                            <input type="number" name="tunjangan_anak_persen" value="{{ old('tunjangan_anak_persen', $settings['tunjangan_anak_persen']) }}" step="0.1" min="0" max="100"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 pr-10 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">%</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">% × gaji pokok × jumlah anak</p>
                        @error('tunjangan_anak_persen') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-hashtag text-indigo-500 mr-1"></i> Maks. Anak
                        </label>
                        <input type="number" name="tunjangan_anak_max" value="{{ old('tunjangan_anak_max', $settings['tunjangan_anak_max']) }}" min="0" max="10"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        <p class="text-xs text-gray-400 mt-1">Jumlah anak maks yang dihitung</p>
                        @error('tunjangan_anak_max') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-wheat-awn text-amber-500 mr-1"></i> Tunj. Beras
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                            <input type="number" name="tunjangan_beras" value="{{ old('tunjangan_beras', $settings['tunjangan_beras']) }}" min="0"
                                class="w-full border border-gray-300 rounded-xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Per tanggungan (istri + anak)</p>
                        @error('tunjangan_beras') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
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
                            <input type="number" name="jam_wajib_tetap_{{ $lk }}" value="{{ old("jam_wajib_tetap_{$lk}", $settings["jam_wajib_tetap_{$lk}"]) }}" min="0" max="50"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 pr-14 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">jam</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">GTY/PTY/PNS</p>
                        @error("jam_wajib_tetap_{$lk}") <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-clock text-orange-500 mr-1"></i> Jam Wajib (Honor)
                        </label>
                        <div class="relative">
                            <input type="number" name="jam_wajib_honor_{{ $lk }}" value="{{ old("jam_wajib_honor_{$lk}", $settings["jam_wajib_honor_{$lk}"]) }}" min="0" max="50"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 pr-14 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">jam</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Guru Honorer</p>
                        @error("jam_wajib_honor_{$lk}") <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-money-bill text-green-500 mr-1"></i> Honor/Jam (Tetap)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                            <input type="number" name="honor_tetap_{{ $lk }}" value="{{ old("honor_tetap_{$lk}", $settings["honor_tetap_{$lk}"]) }}" min="0"
                                class="w-full border border-gray-300 rounded-xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">GTY/PTY/PNS per jam</p>
                        @error("honor_tetap_{$lk}") <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-money-bill-wave text-emerald-500 mr-1"></i> Honor/Jam (Honorer)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                            <input type="number" name="honor_honorer_{{ $lk }}" value="{{ old("honor_honorer_{$lk}", $settings["honor_honorer_{$lk}"]) }}" min="0"
                                class="w-full border border-gray-300 rounded-xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Guru Honorer per jam</p>
                        @error("honor_honorer_{$lk}") <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-money-bill-wave text-cyan-500 mr-1"></i> Honor/Jam (Percobaan)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                            <input type="number" name="honor_percobaan_{{ $lk }}" value="{{ old("honor_percobaan_{$lk}", $settings["honor_percobaan_{$lk}"]) }}" min="0"
                                class="w-full border border-gray-300 rounded-xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Status Percobaan per jam</p>
                        @error("honor_percobaan_{$lk}") <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
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
                            <input type="number" name="bpjs_kesehatan_persen" value="{{ old('bpjs_kesehatan_persen', $settings['bpjs_kesehatan_persen']) }}" step="0.1" min="0" max="100"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 pr-10 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">%</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Potongan kesehatan dari gaji pokok</p>
                        @error('bpjs_kesehatan_persen') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-briefcase text-orange-500 mr-1"></i> BPJS Ketenagakerjaan
                        </label>
                        <div class="relative">
                            <input type="number" name="bpjs_ketenagakerjaan_persen" value="{{ old('bpjs_ketenagakerjaan_persen', $settings['bpjs_ketenagakerjaan_persen']) }}" step="0.1" min="0" max="100"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 pr-10 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">%</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Potongan ketenagakerjaan (JHT, dll) dari gaji pokok</p>
                        @error('bpjs_ketenagakerjaan_persen') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Save Button --}}
        <div class="flex justify-end gap-3 mb-8">
            <a href="{{ route('admin.dashboard') }}" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition font-medium">
                <i class="fas fa-times mr-1"></i> Batal
            </a>
            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:from-indigo-700 hover:to-purple-700 shadow-lg hover:shadow-xl transition font-semibold">
                <i class="fas fa-save mr-2"></i> Simpan Pengaturan
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('settingsForm').addEventListener('submit', function(e) {
    if (!confirm('Apakah Anda yakin ingin menyimpan pengaturan gaji ini?\n\nPerubahan akan mempengaruhi perhitungan gaji seluruh pegawai.')) {
        e.preventDefault();
    }
});
</script>
@endpush
@endsection
