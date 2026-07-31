@extends('layouts.guru')
@section('title', 'Profil Saya - Portal Guru')

@section('content')
<div x-data="{ isEditModalOpen: false, photoPreview: null }" class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-user text-emerald-500"></i> Profil Saya
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Informasi data pribadi dan kepegawaian</p>
        </div>
        <div class="flex gap-2">
            <button @click="isEditModalOpen = true" class="bg-emerald-50 text-emerald-700 border border-emerald-100 hover:bg-emerald-100 px-4 py-2 rounded-xl text-sm font-semibold shadow-sm transition-all duration-300 flex items-center gap-2">
                <i class="fas fa-edit"></i> Edit Biodata
            </button>
            <a href="{{ route('profile.settings') }}" class="bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-xl text-sm font-semibold shadow-sm transition-all duration-300 flex items-center gap-2">
                <i class="fas fa-shield-alt"></i> Keamanan Akun
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Photo & Name Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-emerald-600 to-green-600 p-6 text-center text-white">
                <div class="w-24 h-24 mx-auto bg-white/20 rounded-xl flex items-center justify-center text-5xl mb-3">
                    @if($teacher->photo)
                        <img src="{{ asset('storage/'.$teacher->photo) }}" class="w-full h-full object-cover rounded-xl">
                    @else
                        👨‍🏫
                    @endif
                </div>
                <h2 class="text-xl font-bold">{{ $teacher->full_name }}</h2>
                <p class="text-white/80 text-sm">{{ $teacher->teacher_code ?? '-' }}</p>
                @if($teacher->position)
                    <span class="inline-block mt-2 text-xs bg-white/20 px-3 py-1 rounded-full">{{ $teacher->position }}</span>
                @endif
            </div>
            <div class="p-4 text-center">
                <p class="text-sm text-gray-600">{{ $teacher->school->name ?? '-' }}</p>
                <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-bold {{ $teacher->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $teacher->is_active ? 'Aktif' : 'Tidak Aktif' }}
                </span>
            </div>

            {{-- Reputation & Badges --}}
            @if($teacher->user && $teacher->user->reputation)
            <div class="border-t border-gray-100 p-6 bg-slate-50/50">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pembda Elite</h3>
                    <span class="text-xs font-bold {{ $teacher->user->reputation->level_color }} text-white px-2 py-0.5 rounded-full">
                        {{ $teacher->user->reputation->level_name }}
                    </span>
                </div>
                
                <div class="flex items-center gap-3 mb-6">
                    <div class="text-3xl font-bold text-slate-800">{{ number_format($teacher->user->reputation->total_points) }}</div>
                    <div class="text-xs font-bold text-slate-400 uppercase leading-tight">Total<br>Score</div>
                </div>

                <div class="space-y-3">
                    <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Koleksi Lencana</h4>
                    <div class="flex flex-wrap gap-2">
                        @forelse($teacher->user->badges as $badge)
                            <div class="group relative">
                                <div class="w-10 h-10 {{ $badge->color }} text-white rounded-lg flex items-center justify-center shadow-sm cursor-help hover:scale-110 transition-transform">
                                    <i class="fas {{ $badge->icon }} text-base"></i>
                                </div>
                                {{-- Tooltip --}}
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block w-32 bg-slate-900 text-white text-xs p-2 rounded shadow-xl z-20">
                                    <p class="font-bold border-b border-white/10 pb-1 mb-1">{{ $badge->name }}</p>
                                    <p class="text-white/70">{{ $badge->description }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 italic">Belum ada lencana yang didapat</p>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Detail Info --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Data Pribadi --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-bold text-gray-800 flex items-center gap-2"><i class="fas fa-id-card text-emerald-500"></i> Data Pribadi</h2>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @php
                            $fields = [
                                ['Nama Lengkap', $teacher->full_name],
                                ['Kode Guru', $teacher->teacher_code ?? '-'],
                                ['Jenis Kelamin', $teacher->gender === 'L' ? 'Laki-laki' : ($teacher->gender === 'P' ? 'Perempuan' : '-')],
                                ['Tempat Lahir', $teacher->birth_place ?? '-'],
                                ['Tanggal Lahir', $teacher->birth_date ? $teacher->birth_date->translatedFormat('d F Y') : '-'],
                                ['Agama', $teacher->religion ?? '-'],
                                ['No. HP', $teacher->phone ?? '-'],
                                ['Alamat', $teacher->address ?? '-'],
                            ];
                        @endphp
                        @foreach($fields as $f)
                            <div>
                                <p class="text-xs text-gray-500 font-medium mb-1">{{ $f[0] }}</p>
                                <p class="text-sm text-gray-800 font-medium">{{ $f[1] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Data Akademik --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-bold text-gray-800 flex items-center gap-2"><i class="fas fa-graduation-cap text-blue-500"></i> Data Akademik</h2>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @php
                            $academicFields = [
                                ['Pendidikan Terakhir', $teacher->education_level ?? '-'],
                                ['Jurusan', $teacher->major ?? '-'],
                                ['Jabatan', $teacher->position ?? '-'],
                                ['Sekolah', $teacher->school->name ?? '-'],
                            ];
                        @endphp
                        @foreach($academicFields as $f)
                            <div>
                                <p class="text-xs text-gray-500 font-medium mb-1">{{ $f[0] }}</p>
                                <p class="text-sm text-gray-800 font-medium">{{ $f[1] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Data Kepegawaian --}}
            @if($teacher->employee)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-bold text-gray-800 flex items-center gap-2"><i class="fas fa-briefcase text-amber-500"></i> Data Kepegawaian</h2>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @php
                            $empFields = [
                                ['Kode Pegawai', $teacher->employee->employee_code ?? '-'],
                                ['Tipe', ucfirst(str_replace('_', ' ', $teacher->employee->employee_type ?? '-'))],
                                ['Status Kepegawaian', ucfirst($teacher->employee->employment_status ?? '-')],
                                ['TMT', $teacher->employee->tmt_date ? \Carbon\Carbon::parse($teacher->employee->tmt_date)->translatedFormat('d F Y') : '-'],
                            ];

                            if (\App\Models\Setting::getValue('guru_can_see_payroll_details', true)) {
                                $empFields[] = ['Gaji Pokok', $teacher->employee->basic_salary ? 'Rp ' . number_format($teacher->employee->basic_salary, 0, ',', '.') : '-'];
                            }
                        @endphp
                        @foreach($empFields as $f)
                            <div>
                                <p class="text-xs text-gray-500 font-medium mb-1">{{ $f[0] }}</p>
                                <p class="text-sm text-gray-800 font-medium">{{ $f[1] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Modal Edit Biodata --}}
    <div x-show="isEditModalOpen" 
         style="display: none;" 
         class="fixed top-[66px] inset-x-0 bottom-0 z-[99999] overflow-y-auto flex items-start justify-center p-4 sm:p-6 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[82vh] flex flex-col overflow-hidden relative my-2"
             @click.outside="isEditModalOpen = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <form action="{{ route('profile.biodata.update') }}" method="POST" enctype="multipart/form-data" class="flex flex-col h-full overflow-hidden">
                @csrf
                @method('PUT')
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/80 shrink-0">
                    <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                        <i class="fas fa-edit text-emerald-500"></i> Edit Biodata Mandiri
                    </h3>
                    <button type="button" @click="isEditModalOpen = false" class="text-gray-400 hover:text-gray-600 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto flex-1 space-y-4 min-h-0">
                    @php $isBiodataEditable = now()->format('Y-m-d') <= '2026-08-10'; @endphp
                    
                    @if($isBiodataEditable)
                    <div class="bg-emerald-50 border-l-4 border-emerald-500 p-3 rounded-lg text-xs text-emerald-800 mb-4 flex gap-2 items-start">
                        <i class="fas fa-info-circle mt-0.5"></i>
                        <p><strong>Perhatian:</strong> Fitur Edit Biodata Mandiri diperpanjang sampai dengan <strong>10 Agustus 2026</strong>. Pastikan data Anda sudah benar sebelum batas waktu tersebut.</p>
                    </div>
                    @else
                    <div class="bg-amber-50 border-l-4 border-amber-500 p-3 rounded-lg text-xs text-amber-800 mb-4 flex gap-2 items-start">
                        <i class="fas fa-exclamation-triangle mt-0.5"></i>
                        <p>Waktu pembaruan biodata mandiri telah berakhir pada <strong>10 Agustus 2026</strong>. Anda tidak dapat mengubah data ini lagi. Hubungi Admin jika ada kesalahan data.</p>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-500 mb-2 uppercase">Foto Profil</label>
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0 border border-gray-200 flex items-center justify-center text-2xl">
                                    <template x-if="photoPreview">
                                        <img :src="photoPreview" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!photoPreview">
                                        @if($teacher->photo)
                                            <img src="{{ asset('storage/'.$teacher->photo) }}" class="w-full h-full object-cover">
                                        @else
                                            <span>👨‍🏫</span>
                                        @endif
                                    </template>
                                </div>
                                <div class="flex-1">
                                    <input type="file" name="photo" accept="image/jpeg,image/png,image/jpg"
                                        @change="if($event.target.files.length > 0) { photoPreview = URL.createObjectURL($event.target.files[0]) } else { photoPreview = null }"
                                        @if(!$isBiodataEditable) disabled @endif
                                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 disabled:opacity-50">
                                    <p class="text-[10px] text-gray-400 mt-1">Maks 2MB (JPG/PNG). Pilih foto baru untuk melihat pratinjau.</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase">Tempat Lahir</label>
                            <input type="text" name="birth_place" value="{{ old('birth_place', $teacher->birth_place) }}"
                                @if(!$isBiodataEditable) disabled @endif
                                class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 disabled:bg-gray-50 disabled:text-gray-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase">Tanggal Lahir</label>
                            <input type="date" name="birth_date" value="{{ old('birth_date', optional($teacher->birth_date)->format('Y-m-d')) }}"
                                @if(!$isBiodataEditable) disabled @endif
                                class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 disabled:bg-gray-50 disabled:text-gray-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase">Agama</label>
                            <select name="religion" @if(!$isBiodataEditable) disabled @endif class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 disabled:bg-gray-50 disabled:text-gray-500">
                                @foreach(['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $agama)
                                    <option value="{{ $agama }}" {{ old('religion', $teacher->religion) === $agama ? 'selected' : '' }}>{{ $agama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase">Nomor Telepon/HP</label>
                            <input type="text" name="phone" value="{{ old('phone', $teacher->phone) }}"
                                @if(!$isBiodataEditable) disabled @endif
                                class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 disabled:bg-gray-50 disabled:text-gray-500">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase">Alamat Lengkap</label>
                            <textarea name="address" rows="3" @if(!$isBiodataEditable) disabled @endif
                                class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 disabled:bg-gray-50 disabled:text-gray-500">{{ old('address', $teacher->address) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50/80 border-t border-gray-100 flex justify-end gap-3 shrink-0 rounded-b-2xl">
                    <button type="button" @click="isEditModalOpen = false" class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-200 transition">
                        Batal
                    </button>
                    @if($isBiodataEditable)
                    <button type="submit" class="bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white px-6 py-2 rounded-xl text-sm font-semibold transition-all shadow-sm">
                        Simpan Perubahan
                    </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
