@extends('layouts.admin')
@section('title', 'Edit Data Alumni')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.alumni-directory.index') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Data Alumni</h1>
            <p class="text-sm text-gray-500 mt-1">Perbarui data alumni yang sudah terdaftar</p>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative">
        <ul class="list-disc list-inside text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form action="{{ route('admin.alumni-directory.update', $directory) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Data Pribadi -->
                <div class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-2">Identitas Pribadi</h3>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="full_name" value="{{ old('full_name', $directory->full_name) }}" required class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Alias / Panggilan</label>
                        <input type="text" name="alias_name" value="{{ old('alias_name', $directory->alias_name) }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin <span class="text-red-500">*</span></label>
                            <select name="gender" required class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm">
                                <option value="">Pilih</option>
                                <option value="L" {{ old('gender', $directory->gender) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('gender', $directory->gender) == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status Pernikahan</label>
                            <select name="marital_status" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm">
                                <option value="">Pilih</option>
                                <option value="Belum Menikah" {{ old('marital_status', $directory->marital_status) == 'Belum Menikah' ? 'selected' : '' }}>Belum Menikah</option>
                                <option value="Menikah" {{ old('marital_status', $directory->marital_status) == 'Menikah' ? 'selected' : '' }}>Menikah</option>
                                <option value="Pernah Menikah" {{ old('marital_status', $directory->marital_status) == 'Pernah Menikah' ? 'selected' : '' }}>Pernah Menikah</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Anak</label>
                        <input type="number" name="children_count" value="{{ old('children_count', $directory->children_count) }}" min="0" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm">
                    </div>
                </div>

                <!-- Kontak & Pekerjaan -->
                <div class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-2">Kontak & Pekerjaan</h3>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. HP / WhatsApp</label>
                        <input type="text" name="phone" value="{{ old('phone', $directory->phone) }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $directory->email) }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan Saat Ini</label>
                            <input type="text" name="occupation" value="{{ old('occupation', $directory->occupation) }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Perusahaan/Instansi</label>
                            <input type="text" name="company_name" value="{{ old('company_name', $directory->company_name) }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Domisili <span class="text-red-500">*</span></label>
                        <textarea name="address" required rows="2" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm">{{ old('address', $directory->address) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Rekam Akademik & Media -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t">
                <div class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-2">Rekam Akademik</h3>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Sekolah <span class="text-red-500">*</span></label>
                        <select id="school-select" name="school_id" required class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm" onchange="toggleJurusan()">
                            <option value="">-- Pilih Unit Sekolah / IKA --</option>
                            <optgroup label="Unit Sekolah Aktif">
                                @foreach($schools->where('is_active', true) as $school)
                                    <option value="{{ $school->id }}" data-type="{{ $school->type }}" {{ old('school_id', $directory->school_id) == $school->id ? 'selected' : '' }}>IKA {{ $school->name }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Unit Sekolah Merger / Historis">
                                @foreach($schools->where('is_active', false) as $school)
                                    <option value="{{ $school->id }}" data-type="{{ $school->type }}" {{ old('school_id', $directory->school_id) == $school->id ? 'selected' : '' }}>IKA {{ $school->name }} (Sekolah Merger)</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>

                    <div id="jurusan-container" style="display: {{ str_contains(strtolower($directory->school->type ?? ''), 'smk') ? 'block' : 'none' }};">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jurusan (Khusus SMK)</label>
                        <input type="text" id="jurusan-input" name="jurusan" value="{{ old('jurusan', $directory->jurusan) }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Lulus <span class="text-red-500">*</span></label>
                            <select name="graduation_year" required class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm">
                                <option value="">-- Pilih --</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ old('graduation_year', $directory->graduation_year) == $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kelas Terakhir</label>
                            <input type="text" name="last_class" value="{{ old('last_class', $directory->last_class) }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm">
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-900 border-b pb-2">Pesan & Media</h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Testimoni / Pesan (Rembuk Alumni)</label>
                        <textarea name="message" rows="4" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm">{{ old('message', $directory->message) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Foto Profil Baru (Opsional)</label>
                        @if($directory->photo_path)
                        <div class="mb-3">
                            <img src="{{ $directory->photo_url }}" alt="Current Photo" class="w-16 h-16 rounded-full object-cover border">
                        </div>
                        @endif
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/jpg" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition flex items-center gap-2">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleJurusan() {
        const schoolSelect = document.getElementById('school-select');
        if(!schoolSelect) return;
        
        const selectedOption = schoolSelect.options[schoolSelect.selectedIndex];
        const type = selectedOption ? selectedOption.getAttribute('data-type') : null;
        
        const jurusanContainer = document.getElementById('jurusan-container');
        
        if (type && type.toUpperCase().includes('SMK')) {
            jurusanContainer.style.display = 'block';
        } else {
            jurusanContainer.style.display = 'none';
        }
    }
    
    // Check again when navigating back etc
    document.addEventListener('DOMContentLoaded', toggleJurusan);
</script>
@endsection
