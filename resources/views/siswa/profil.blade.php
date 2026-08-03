@extends('layouts.siswa')
@section('title', 'Profil Saya - Portal Siswa')

@section('content')
<div x-data="profileCropper" class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-user text-amber-500"></i> Profil Saya
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Informasi data pribadi dan orang tua</p>
        </div>
        <div class="flex gap-2">
            <button @click="isEditModalOpen = true" class="bg-amber-50 text-amber-700 border border-amber-100 hover:bg-amber-100 px-4 py-2 rounded-xl text-sm font-semibold shadow-sm transition-all duration-300 flex items-center gap-2">
                <i class="fas fa-edit"></i> Edit Biodata
            </button>
            <a href="{{ route('profile.settings') }}" class="bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-xl text-sm font-semibold shadow-sm transition-all duration-300 flex items-center gap-2">
                <i class="fas fa-shield-alt"></i> Keamanan Akun
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Photo & Identity --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-br from-amber-500 via-orange-500 to-amber-600 p-6 text-center text-white relative overflow-hidden">
                <div class="w-36 h-36 sm:w-40 sm:h-40 mx-auto mb-4 rounded-2xl overflow-hidden shadow-xl ring-4 ring-white/30 border-2 border-white bg-white/20 flex items-center justify-center transition-transform duration-300 hover:scale-105">
                    @if($student->photo)
                        <img src="{{ $student->photo_url }}" class="w-full h-full object-cover rounded-2xl" alt="{{ $student->full_name }}">
                    @else
                        <span class="text-6xl">🎓</span>
                    @endif
                </div>
                <h2 class="text-lg sm:text-xl font-extrabold tracking-tight">{{ $student->full_name }}</h2>
                <div class="flex flex-wrap items-center justify-center gap-2 mt-1.5">
                    @if($student->nisn)
                        <span class="text-amber-100 text-xs font-mono bg-black/20 px-2.5 py-0.5 rounded-full">NISN: {{ $student->nisn }}</span>
                    @endif
                    @if($student->nis)
                        <span class="text-amber-100 text-xs font-mono bg-black/20 px-2.5 py-0.5 rounded-full">NIS: {{ $student->nis }}</span>
                    @endif
                </div>
            </div>
            <div class="p-4 text-center bg-slate-50/50">
                <div class="flex flex-wrap justify-center gap-1.5">
                    @if($classroom)
                        <span class="inline-block bg-amber-100 text-amber-800 border border-amber-200 px-3 py-1 rounded-full text-xs font-bold shadow-sm">{{ $classroom->class_name }}</span>
                    @endif
                    <span class="inline-block bg-blue-100 text-blue-800 border border-blue-200 px-3 py-1 rounded-full text-xs font-bold shadow-sm">{{ $student->school->name ?? '-' }}</span>
                    <span class="inline-block {{ $student->isActive() ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-rose-100 text-rose-800 border border-rose-200' }} px-3 py-1 rounded-full text-xs font-bold shadow-sm">
                        {{ $student->status_label }}
                    </span>
                </div>
            </div>

            {{-- Reputation & Badges --}}
            @if($student->user && $student->user->reputation)
            <div class="border-t border-gray-100 p-6 bg-slate-50/50">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pembda Elite</h3>
                    <span class="text-xs font-bold {{ $student->user->reputation->level_color }} text-white px-2 py-0.5 rounded-full">
                        {{ $student->user->reputation->level_name }}
                    </span>
                </div>
                
                <div class="flex items-center gap-3 mb-6">
                    <div class="text-3xl font-bold text-slate-800">{{ number_format($student->user->reputation->total_points) }}</div>
                    <div class="text-xs font-bold text-slate-400 uppercase leading-tight">Total<br>Score</div>
                </div>

                <div class="space-y-3">
                    <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Koleksi Lencana</h4>
                    <div class="flex flex-wrap gap-2">
                        @forelse($student->user->badges as $badge)
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
                <div class="px-5 py-3 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2"><i class="fas fa-id-card text-amber-500 text-xs"></i> Data Pribadi</h3>
                </div>
                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Nama Lengkap</p>
                        <p class="font-medium text-gray-800">{{ $student->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Jenis Kelamin</p>
                        <p class="font-medium text-gray-800">{{ $student->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Tempat, Tanggal Lahir</p>
                        <p class="font-medium text-gray-800">{{ $student->birth_place }}, {{ $student->birth_date ? $student->birth_date->format('d M Y') : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Agama</p>
                        <p class="font-medium text-gray-800">{{ $student->religion ?? '-' }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-xs text-gray-500 mb-0.5">Alamat</p>
                        <p class="font-medium text-gray-800">{{ $student->address ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">No. HP</p>
                        <p class="font-medium text-gray-800">{{ $student->phone ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Tahun Masuk</p>
                        <p class="font-medium text-gray-800">{{ $student->entry_year ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Data Orang Tua --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2"><i class="fas fa-user-friends text-blue-500 text-xs"></i> Data Orang Tua / Wali</h3>
                </div>
                <div class="p-5">
                    @if($student->parents && $student->parents->count() > 0)
                        <div class="space-y-3">
                            @foreach($student->parents as $parent)
                                <div class="border border-gray-100 rounded-xl p-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $parent->relation_type === 'ayah' ? 'bg-blue-100 text-blue-700' : ($parent->relation_type === 'ibu' ? 'bg-pink-100 text-pink-700' : 'bg-gray-100 text-gray-700') }}">
                                            {{ $parent->getRelationTypeLabel() }}
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                                        <div>
                                            <p class="text-xs text-gray-500">Nama</p>
                                            <p class="font-medium text-gray-800">{{ $parent->full_name }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">No. HP</p>
                                            <p class="font-medium text-gray-800">{{ $parent->phone ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Pekerjaan</p>
                                            <p class="font-medium text-gray-800">{{ $parent->occupation ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Email</p>
                                            <p class="font-medium text-gray-800">{{ $parent->email ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        {{-- Fallback to student's parent_name field --}}
                        <div class="text-sm text-gray-600">
                            <p><span class="text-gray-500">Nama Wali:</span> {{ $student->parent_name ?? '-' }}</p>
                            <p><span class="text-gray-500">No. HP Wali:</span> {{ $student->parent_phone ?? '-' }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Sekolah Asal --}}
            @if($student->previous_school)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-bold text-gray-800 text-sm">🏫 Sekolah Asal</h3>
                </div>
                <div class="p-5 text-sm">
                    <p class="font-medium text-gray-800">{{ $student->previous_school }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Modal Edit Biodata --}}
    <template x-teleport="body">
        <div x-show="isEditModalOpen" 
             style="display: none;" 
             class="fixed inset-0 z-[999999] bg-slate-900/70 backdrop-blur-md flex flex-col items-center justify-start p-3 sm:p-6 pt-[75px] pb-6 overflow-y-auto"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl flex flex-col max-h-[calc(100vh-95px)] my-auto overflow-hidden relative border border-gray-100"
                 @click.outside="isEditModalOpen = false"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <form action="{{ route('profile.biodata.update') }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                    @csrf
                    @method('PUT')
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/90 shrink-0">
                        <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                            <i class="fas fa-edit text-amber-500"></i> Edit Biodata Mandiri
                        </h3>
                        <button type="button" @click="isEditModalOpen = false" class="text-gray-400 hover:text-gray-600 transition">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <input type="hidden" name="cropped_photo" id="croppedPhotoInput">

                    <div class="p-4 sm:p-6 overflow-y-auto flex-1 min-h-0 space-y-4 max-h-[60vh] sm:max-h-[65vh] focus:outline-none">
                        @php $isBiodataEditable = now()->format('Y-m-d') <= '2026-08-10'; @endphp
                        
                        @if($isBiodataEditable)
                        <div class="bg-amber-50 border-l-4 border-amber-500 p-3 rounded-lg text-xs text-amber-800 mb-4 flex gap-2 items-start">
                            <i class="fas fa-info-circle mt-0.5"></i>
                            <p><strong>Perhatian:</strong> Fitur Edit Biodata Mandiri diperpanjang sampai dengan <strong>10 Agustus 2026</strong>. Pastikan data Anda sudah benar sebelum batas waktu tersebut.</p>
                        </div>
                        @else
                        <div class="bg-red-50 border-l-4 border-red-500 p-3 rounded-lg text-xs text-red-800 mb-4 flex gap-2 items-start">
                            <i class="fas fa-exclamation-triangle mt-0.5"></i>
                            <p>Waktu pembaruan biodata mandiri telah berakhir pada <strong>10 Agustus 2026</strong>. Anda tidak dapat mengubah data ini lagi. Hubungi Admin jika ada kesalahan data.</p>
                        </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-gray-500 mb-2 uppercase">Foto Profil</label>
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0 border border-gray-200 flex items-center justify-center">
                                        <template x-if="photoPreview">
                                            <img :src="photoPreview" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!photoPreview">
                                            <img src="{{ $student->photo_url }}" class="w-full h-full object-cover">
                                        </template>
                                    </div>
                                    <div class="flex-1">
                                        <input type="file" name="photo" id="photoInput" accept="image/jpeg,image/png,image/jpg"
                                            @change="initCropper($event)"
                                            @if(!$isBiodataEditable) disabled @endif
                                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 disabled:opacity-50">
                                        <p class="text-[10px] text-gray-400 mt-1">Maks 2MB (JPG/PNG). Pilih foto baru untuk melihat pratinjau.</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase">Tempat Lahir</label>
                                <input type="text" name="birth_place" value="{{ old('birth_place', $student->birth_place) }}"
                                    @if(!$isBiodataEditable) disabled @endif
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 disabled:bg-gray-50 disabled:text-gray-500">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase">Tanggal Lahir</label>
                                <input type="date" name="birth_date" value="{{ old('birth_date', optional($student->birth_date)->format('Y-m-d')) }}"
                                    @if(!$isBiodataEditable) disabled @endif
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 disabled:bg-gray-50 disabled:text-gray-500">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase">Agama</label>
                                <select name="religion" @if(!$isBiodataEditable) disabled @endif class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 disabled:bg-gray-50 disabled:text-gray-500">
                                    @foreach(['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $agama)
                                        <option value="{{ $agama }}" {{ old('religion', $student->religion) === $agama ? 'selected' : '' }}>{{ $agama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase">Jenis Kelamin</label>
                                <select name="gender" @if(!$isBiodataEditable) disabled @endif class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 disabled:bg-gray-50 disabled:text-gray-500">
                                    <option value="L" {{ old('gender', $student->gender) === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                                    <option value="P" {{ old('gender', $student->gender) === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase">Nomor Telepon/HP Siswa</label>
                                <input type="text" name="phone" value="{{ old('phone', $student->phone) }}"
                                    @if(!$isBiodataEditable) disabled @endif
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 disabled:bg-gray-50 disabled:text-gray-500">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase">Alamat Lengkap</label>
                                <textarea name="address" rows="2" @if(!$isBiodataEditable) disabled @endif
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 disabled:bg-gray-50 disabled:text-gray-500">{{ old('address', $student->address) }}</textarea>
                            </div>

                            {{-- Section Data Ortu / Wali --}}
                            <div class="sm:col-span-2 pt-3 border-t border-gray-100">
                                <h4 class="font-extrabold text-amber-700 text-xs uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                    <i class="fas fa-users text-amber-500"></i> Data Orang Tua / Wali Siswa
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase">Nama Ortu / Wali</label>
                                        <input type="text" name="guardian_name" value="{{ old('guardian_name', $student->guardian_name ?? $student->parent_name) }}"
                                            @if(!$isBiodataEditable) disabled @endif
                                            class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 disabled:bg-gray-50 disabled:text-gray-500" placeholder="Nama Ayah/Ibu/Wali">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase">No. HP/Telepon Ortu</label>
                                        <input type="text" name="guardian_phone" value="{{ old('guardian_phone', $student->guardian_phone ?? $student->parent_phone) }}"
                                            @if(!$isBiodataEditable) disabled @endif
                                            class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 disabled:bg-gray-50 disabled:text-gray-500" placeholder="08xxxxxxxxxx">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase">Pekerjaan Ortu/Wali</label>
                                        @php
                                            $occupations = [
                                                'PNS / ASN',
                                                'TNI / Polri',
                                                'Guru / Dosen',
                                                'Pegawai Swasta',
                                                'Wiraswasta / Pedagang',
                                                'Petani / Peternak',
                                                'Nelayan',
                                                'Buruh / Pekerja Lepas',
                                                'Pensiunan',
                                                'Tidak Bekerja / Ibu Rumah Tangga',
                                                'Lainnya'
                                            ];
                                            $currentOcc = old('guardian_occupation', $student->guardian_occupation);
                                        @endphp
                                        <select name="guardian_occupation" @if(!$isBiodataEditable) disabled @endif
                                            class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 disabled:bg-gray-50 disabled:text-gray-500">
                                            <option value="">-- Pilih Pekerjaan --</option>
                                            @foreach($occupations as $occ)
                                                <option value="{{ $occ }}" {{ $currentOcc === $occ ? 'selected' : '' }}>{{ $occ }}</option>
                                            @endforeach
                                            @if($currentOcc && !in_array($currentOcc, $occupations))
                                                <option value="{{ $currentOcc }}" selected>{{ $currentOcc }}</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50/90 border-t border-gray-100 flex justify-end gap-3 shrink-0 rounded-b-2xl">
                        <button type="button" @click="isEditModalOpen = false" class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-200 transition">
                            Batal
                        </button>
                        @if($isBiodataEditable)
                        <button type="submit" class="bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white px-6 py-2 rounded-xl text-sm font-semibold transition-all shadow-sm">
                            Simpan Perubahan
                        </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- Modal Crop Foto --}}
    <template x-teleport="body">
        <div x-show="isCropModalOpen" 
             style="display: none;" 
             class="fixed inset-0 z-[999999] bg-slate-900/90 backdrop-blur-sm flex items-center justify-center p-4">
            
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col" @click.outside="cancelCrop()">
                <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-crop-alt text-amber-500"></i> Sesuaikan Foto Profil
                    </h3>
                    <button type="button" @click="cancelCrop()" class="text-gray-400 hover:text-gray-600 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="p-4 bg-slate-100 flex justify-center items-center">
                    <div class="img-container w-full overflow-hidden rounded-xl shadow-inner bg-slate-200 flex items-center justify-center" style="height: 350px;">
                        <img id="imageToCrop" src="" alt="Picture" class="max-w-full max-h-full hidden">
                    </div>
                </div>
                
                {{-- Cropper Controls --}}
                <div class="px-5 py-3 border-t border-gray-100 flex justify-center gap-3 bg-white">
                    <button type="button" @click="cropperInstance.zoom(0.1)" class="w-10 h-10 flex items-center justify-center bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-xl transition shadow-sm" title="Perbesar">
                        <i class="fas fa-search-plus"></i>
                    </button>
                    <button type="button" @click="cropperInstance.zoom(-0.1)" class="w-10 h-10 flex items-center justify-center bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-xl transition shadow-sm" title="Perkecil">
                        <i class="fas fa-search-minus"></i>
                    </button>
                    <div class="w-px h-8 bg-gray-200 mx-1 self-center"></div>
                    <button type="button" @click="cropperInstance.rotate(-90)" class="w-10 h-10 flex items-center justify-center bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-xl transition shadow-sm" title="Putar Kiri">
                        <i class="fas fa-undo"></i>
                    </button>
                    <button type="button" @click="cropperInstance.rotate(90)" class="w-10 h-10 flex items-center justify-center bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-xl transition shadow-sm" title="Putar Kanan">
                        <i class="fas fa-redo"></i>
                    </button>
                    <div class="w-px h-8 bg-gray-200 mx-1 self-center"></div>
                    <button type="button" @click="cropperInstance.reset()" class="w-10 h-10 flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl transition shadow-sm" title="Reset Ulang">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>

                <div class="px-5 py-4 border-t border-gray-100 flex justify-end gap-3 bg-gray-50">
                    <button type="button" @click="cancelCrop()" class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-200 transition">
                        Batal
                    </button>
                    <button type="button" @click="saveCrop()" class="bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white px-6 py-2 rounded-xl text-sm font-semibold transition shadow-sm flex items-center gap-2">
                        <i class="fas fa-check"></i> Terapkan
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
<style>
    .cropper-view-box,
    .cropper-face {
        border-radius: 50%; /* Make the crop box circular */
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('profileCropper', () => ({
            isEditModalOpen: false,
            photoPreview: null,
            isCropModalOpen: false,
            cropperInstance: null,
            
            initCropper(e) {
                const files = e.target.files;
                if (files && files.length > 0) {
                    const file = files[0];
                    if (!file.type.match(/^image\/(jpeg|png|jpg)$/)) {
                        alert('Hanya format JPG dan PNG yang diperbolehkan.');
                        e.target.value = '';
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const image = document.getElementById('imageToCrop');
                        image.src = event.target.result;
                        image.classList.remove('hidden');
                        
                        this.isCropModalOpen = true;
                        
                        if (this.cropperInstance) {
                            this.cropperInstance.destroy();
                        }
                        
                        // Wait for modal to display before init
                        setTimeout(() => {
                            this.cropperInstance = new Cropper(image, {
                                aspectRatio: 1, // 1:1 for profile picture
                                viewMode: 1,
                                dragMode: 'move',
                                autoCropArea: 0.9,
                                restore: false,
                                guides: false,
                                center: false,
                                highlight: false,
                                cropBoxMovable: true,
                                cropBoxResizable: true,
                                toggleDragModeOnDblclick: false,
                            });
                        }, 150);
                    };
                    reader.readAsDataURL(file);
                }
            },
            
            cancelCrop() {
                this.isCropModalOpen = false;
                if (this.cropperInstance) {
                    this.cropperInstance.destroy();
                    this.cropperInstance = null;
                }
                document.getElementById('photoInput').value = '';
                document.getElementById('croppedPhotoInput').value = '';
                document.getElementById('imageToCrop').classList.add('hidden');
            },
            
            saveCrop() {
                if (!this.cropperInstance) return;
                
                const base64data = this.cropperInstance.getCroppedCanvas({
                    width: 600,
                    height: 600,
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                }).toDataURL('image/jpeg', 0.85);
                
                document.getElementById('croppedPhotoInput').value = base64data;
                document.getElementById('photoInput').value = ''; // clear original file
                this.photoPreview = base64data;
                
                this.isCropModalOpen = false;
                setTimeout(() => {
                    this.cropperInstance.destroy();
                    this.cropperInstance = null;
                    document.getElementById('imageToCrop').classList.add('hidden');
                }, 300);
            }
        }));
    });
</script>
@endpush
