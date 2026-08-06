@extends('layouts.admin')

@section('title', 'Tambah Guru')

@section('content')
<div class="space-y-6">
    <div class="mb-8">
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Tambah Guru</h1>
                <p class="text-gray-600 mt-1">Tambahkan data guru baru</p>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <h3 class="text-red-800 font-semibold mb-2">Terdapat kesalahan pada form:</h3>
                <ul class="list-disc list-inside text-red-700 space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('admin.teachers.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Section 1: Data Pribadi -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-white font-bold text-sm">1</div>
                <h2 class="text-xl font-bold text-gray-900">Data Pribadi</h2>
            </div>

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-school mr-1"></i> Sekolah Utama</label>
                    @if(auth()->user()->isSuperAdmin())
                        <select name="school_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            <option value="">-- Pilih Sekolah --</option>
                            @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="bg-indigo-50 border border-indigo-200 rounded-xl px-4 py-3">
                            <span class="text-sm text-indigo-600 font-semibold"><i class="fas fa-school mr-1"></i> {{ auth()->user()->school->name }}</span>
                        </div>
                        <input type="hidden" name="school_id" value="{{ auth()->user()->school_id }}">
                    @endif
                    @error('school_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                @if(auth()->user()->isSuperAdmin())
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2" title="Guru akan bisa mengakses menu di unit-unit ini tanpa perlu akun ganda"><i class="fas fa-plus-circle mr-1 text-emerald-500"></i> Sekolah Tambahan (Lintas Unit)</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 bg-gray-50 p-4 border border-gray-200 rounded-xl">
                        @foreach($schools as $school)
                            @if($school->type !== 'yayasan')
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="additional_school_ids[]" value="{{ $school->id }}" class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500"
                                {{ is_array(old('additional_school_ids')) && in_array($school->id, old('additional_school_ids')) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">{{ $school->name }}</span>
                            </label>
                            @endif
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Centang sekolah tambahan jika guru ini juga ditugaskan mengajar di unit lain.</p>
                </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-font mr-1"></i> Kode Guru</label>
                        <input type="text" name="teacher_code" value="{{ old('teacher_code') }}" required
                            placeholder="Contoh: GR001"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        @error('teacher_code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-user mr-1"></i> Nama Lengkap</label>
                        <input type="text" name="full_name" value="{{ old('full_name') }}" required
                            placeholder="Nama lengkap guru"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        @error('full_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-users mr-1"></i> Jenis Kelamin</label>
                        <div class="flex gap-6">
                            <label class="inline-flex items-center">
                                <input type="radio" name="gender" value="L" {{ old('gender') == 'L' ? 'checked' : '' }} required class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                                <span class="ml-2">Laki-laki</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="gender" value="P" {{ old('gender') == 'P' ? 'checked' : '' }} required class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                                <span class="ml-2">Perempuan</span>
                            </label>
                        </div>
                        @error('gender')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-graduation-cap mr-1"></i> Pendidikan Terakhir</label>
                        <select name="education_level" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            <option value="">-- Pilih Pendidikan --</option>
                            <option value="SMA/SMK" {{ old('education_level') == 'SMA/SMK' ? 'selected' : '' }}>SMA/SMK</option>
                            <option value="D3" {{ old('education_level') == 'D3' ? 'selected' : '' }}>D3</option>
                            <option value="S1" {{ old('education_level') == 'S1' ? 'selected' : '' }}>S1</option>
                            <option value="S2" {{ old('education_level') == 'S2' ? 'selected' : '' }}>S2</option>
                            <option value="S3" {{ old('education_level') == 'S3' ? 'selected' : '' }}>S3</option>
                        </select>
                        @error('education_level')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-graduation-cap mr-1"></i> Jurusan</label>
                        <input type="text" name="major" value="{{ old('major') }}"
                            placeholder="Contoh: Pendidikan Matematika"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        @error('major')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-birthday-cake mr-1"></i> Tanggal Lahir</label>
                        <input type="date" name="birth_date" value="{{ old('birth_date') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        @error('birth_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-heart mr-1"></i> Status Pernikahan</label>
                        <select name="marital_status" id="marital_status" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            <option value="">-- Pilih Status --</option>
                            <option value="belum_menikah" {{ old('marital_status') == 'belum_menikah' ? 'selected' : '' }}>Belum Menikah</option>
                            <option value="menikah" {{ old('marital_status') == 'menikah' ? 'selected' : '' }}>Sudah Menikah</option>
                        </select>
                        @error('marital_status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-child mr-1"></i> Jumlah Anak</label>
                        <input type="number" name="children_count" id="children_count" value="{{ old('children_count', 0) }}"
                            min="0" max="10"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500"
                            {{ old('marital_status') != 'menikah' ? 'disabled' : '' }}>
                        <p class="mt-1 text-xs text-gray-500">* Diisi jika sudah menikah</p>
                        @error('children_count')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-map-marker-alt mr-1"></i> Tempat Lahir</label>
                        <input type="text" name="birth_place" value="{{ old('birth_place') }}"
                            placeholder="Kota kelahiran"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        @error('birth_place')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-pray mr-1"></i> Agama</label>
                        <select name="religion" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            <option value="">-- Pilih Agama --</option>
                            <option value="Islam" {{ old('religion') == 'Islam' ? 'selected' : '' }}>Islam</option>
                            <option value="Kristen" {{ old('religion') == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                            <option value="Katolik" {{ old('religion') == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                            <option value="Hindu" {{ old('religion') == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                            <option value="Buddha" {{ old('religion') == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                            <option value="Konghucu" {{ old('religion') == 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                        </select>
                        @error('religion')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Kontak & Alamat -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-white font-bold text-sm">2</div>
                <h2 class="text-xl font-bold text-gray-900">Kontak & Alamat</h2>
            </div>

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-phone mr-1"></i> Nomor Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                        placeholder="Contoh: 081234567890"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-home mr-1"></i> Alamat Lengkap</label>
                    <textarea name="address" rows="3"
                        placeholder="Alamat lengkap tempat tinggal"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">{{ old('address') }}</textarea>
                    @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <!-- Section 3: Data Kepegawaian -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-white font-bold text-sm">3</div>
                <h2 class="text-xl font-bold text-gray-900">Data Kepegawaian</h2>
            </div>

            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-clipboard mr-1"></i> Status Kepegawaian</label>
                        <select name="employment_status" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            <option value="">-- Pilih Status --</option>
                            <option value="yayasan" {{ old('employment_status') == 'yayasan' ? 'selected' : '' }}>Yayasan</option>
                            <option value="pns" {{ old('employment_status') == 'pns' ? 'selected' : '' }}>PNS</option>
                            <option value="pppk" {{ old('employment_status') == 'pppk' ? 'selected' : '' }}>PPPK</option>
                            <option value="honorer" {{ old('employment_status') == 'honorer' ? 'selected' : '' }}>Honorer</option>
                            <option value="percobaan" {{ old('employment_status') == 'percobaan' ? 'selected' : '' }}>Percobaan</option>
                            <option value="magang" {{ old('employment_status') == 'magang' ? 'selected' : '' }}>Magang</option>
                            <option value="kontrak" {{ old('employment_status') == 'kontrak' ? 'selected' : '' }}>Kontrak</option>
                        </select>
                        @error('employment_status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-calendar-alt mr-1"></i> TMT (Tanggal Mulai Tugas) <span class="text-red-500">*</span></label>
                        <input type="date" name="tmt_date" value="{{ old('tmt_date') }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        @error('tmt_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    @if(auth()->user()->canManageBasicSalary())
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-money-bill-wave mr-1"></i> Gaji Pokok (Nominal)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">Rp</span>
                            <input type="number" name="basic_salary" value="{{ old('basic_salary') }}"
                                placeholder="3000000"
                                class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent @error('basic_salary') border-red-500 @enderror">
                        </div>
                        @error('basic_salary')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    @endif
                </div>

                <div>
                    <label class="inline-flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                            class="w-5 h-5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                        <span class="text-sm font-medium text-gray-700"><i class="fas fa-check-circle text-green-500 mr-1"></i> Pegawai aktif</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Section 4: Akun & Foto -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-white font-bold text-sm">4</div>
                <h2 class="text-xl font-bold text-gray-900">Akun & Foto</h2>
            </div>

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-envelope mr-1"></i> Email Pegawai</label>
                    <input type="email" name="email_employee" value="{{ old('email_employee') }}"
                        placeholder="email@example.com"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    @error('email_employee')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-gray-500">Email untuk keperluan internal (berbeda dengan email login)</p>
                </div>

                <div>
                    <label class="inline-flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="create_account" value="1" id="createAccountCheck" {{ old('create_account') ? 'checked' : '' }}
                            class="w-5 h-5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500"
                            onchange="toggleAccountFields()">
                        <span class="text-sm font-medium text-gray-700"><i class="fas fa-lock mr-1"></i> Buatkan akun untuk login</span>
                    </label>
                </div>

                <div id="accountFields" class="space-y-5" style="display: {{ old('create_account') ? 'block' : 'none' }}">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-envelope mr-1"></i> Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            placeholder="email@example.com"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-lock mr-1"></i> Password</label>
                        <input type="password" name="password"
                            placeholder="Minimal 6 karakter"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-camera mr-1"></i> Foto Guru</label>
                    <input type="file" name="photo" id="photoInput" accept="image/jpeg,image/jpg,image/png"
                        onchange="validateFile(this)"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG. Maksimal 2MB</p>
                    @error('photo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    
                    <div class="mt-4">
                        <img id="photoPreview" src="#" alt="Preview" class="hidden w-32 h-32 rounded-xl object-cover border-2 border-emerald-300">
                        <div id="photoPlaceholder" class="w-32 h-32 rounded-xl bg-gray-100 flex items-center justify-center border-2 border-dashed border-gray-300">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" 
                class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-xl font-medium hover:from-emerald-700 hover:to-teal-800 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan Data Guru
            </button>
            <a href="{{ route('admin.teachers.index') }}" 
                class="px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-all">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
function toggleAccountFields() {
    const checkbox = document.getElementById('createAccountCheck');
    const fields = document.getElementById('accountFields');
    fields.style.display = checkbox.checked ? 'block' : 'none';
}

function validateFile(input) {
    const file = input.files[0];
    if (!file) return;
    
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    const maxSize = 2 * 1024 * 1024; // 2MB
    
    if (!allowedTypes.includes(file.type)) {
        alert('Format file tidak valid. Gunakan JPG/PNG');
        input.value = '';
        return;
    }
    
    if (file.size > maxSize) {
        alert('Ukuran file maksimal 2MB');
        input.value = '';
        return;
    }
    
    const reader = new FileReader();
    reader.onload = (e) => {
        document.getElementById('photoPreview').src = e.target.result;
        document.getElementById('photoPreview').classList.remove('hidden');
        document.getElementById('photoPlaceholder').classList.add('hidden');
    };
    reader.readAsDataURL(file);
}

const maritalStatus = document.getElementById('marital_status');
const childrenCount = document.getElementById('children_count');

maritalStatus?.addEventListener('change', function() {
    if (this.value === 'menikah') {
        childrenCount.disabled = false;
        childrenCount.classList.remove('bg-gray-100');
    } else {
        childrenCount.disabled = true;
        childrenCount.value = 0;
        childrenCount.classList.add('bg-gray-100');
    }
});
</script>
@endsection
