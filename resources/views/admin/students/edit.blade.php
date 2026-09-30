@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Modern Header -->
    <div class="flex items-center gap-4 mb-6">
        <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
        </div>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Edit Data Siswa</h1>
            <p class="text-gray-600">Perbarui informasi lengkap siswa</p>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow">
        <div class="flex items-start">
            <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
            </svg>
            <ul class="list-disc list-inside text-red-700">
                @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <form action="{{ route('admin.students.update', $student) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        @if(request('return_to'))
        <input type="hidden" name="return_to" value="{{ request('return_to') }}">
        @endif

        <!-- Section 1: Data Pribadi -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold">1</span>
                    </div>
                    <h2 class="text-xl font-bold text-white"><i class="fas fa-edit mr-1"></i> Data Pribadi Siswa</h2>
                </div>
            </div>
            <div class="p-6 space-y-4">
                @if(auth()->user()->hasAnyRole(['superadmin', 'admin_sekolah']))
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-school mr-1"></i> Sekolah</label>
                    <select name="school_id" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                        @foreach($schools as $sch)
                        <option value="{{ $sch->id }}" {{ $student->school_id==$sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
                        @endforeach
                    </select>
                </div>
                @else
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-school mr-1"></i> Sekolah</label>
                    <input type="hidden" name="school_id" value="{{ $student->school_id }}">
                    <input type="text" value="{{ $student->school?->name ?? 'Sekolah' }}" disabled class="w-full border-2 border-gray-200 bg-gray-100 p-3 rounded-xl font-bold text-gray-700 cursor-not-allowed">
                </div>
                @endif
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-list-ol mr-1"></i> NISN</label>
                        <input type="text" name="nisn" value="{{ old('nisn', $student->nisn) }}" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-id-badge mr-1"></i> NIS (opsional)</label>
                        <input type="text" name="nis" value="{{ old('nis', $student->nis) }}" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-user mr-1"></i> Nama Lengkap</label>
                    <input type="text" name="full_name" value="{{ old('full_name', $student->full_name) }}" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-venus-mars mr-1"></i> Jenis Kelamin</label>
                        <select name="gender" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                            <option value="L" {{ $student->gender=='L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ $student->gender=='P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-calendar-alt mr-1"></i> Tahun Masuk</label>
                        <input type="number" name="entry_year" value="{{ old('entry_year', $student->entry_year) }}" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-city mr-1"></i> Tempat Lahir</label>
                        <input type="text" name="birth_place" value="{{ old('birth_place', $student->birth_place) }}" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-birthday-cake mr-1"></i> Tanggal Lahir</label>
                        <input type="date" name="birth_date" value="{{ old('birth_date', optional($student->birth_date)->format('Y-m-d')) }}" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-pray mr-1"></i> Agama</label>
                        @php $selReligion = old('religion', $student->religion); @endphp
                        <select name="religion" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                            <option value="" disabled {{ !$selReligion ? 'selected' : '' }}>-- Pilih Agama --</option>
                            <option value="Islam" {{ $selReligion == 'Islam' ? 'selected' : '' }}>Islam</option>
                            <option value="Kristen Protestan" {{ $selReligion == 'Kristen Protestan' ? 'selected' : '' }}>Kristen Protestan</option>
                            <option value="Katolik" {{ $selReligion == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                            <option value="Hindu" {{ $selReligion == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                            <option value="Buddha" {{ $selReligion == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                            <option value="Konghucu" {{ $selReligion == 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                            <option value="Lainnya" {{ $selReligion == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-phone mr-1"></i> Telepon</label>
                        <input type="text" name="phone" value="{{ old('phone', $student->phone) }}" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-home mr-1"></i> Alamat</label>
                    <textarea name="address" rows="3" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">{{ old('address', $student->address) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-school mr-1"></i> Asal Sekolah Sebelumnya</label>
                    <input type="text" name="previous_school" value="{{ old('previous_school', $student->previous_school) }}" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                </div>
            </div>
        </div>

        <!-- Section 2: Data Wali -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold">2</span>
                    </div>
                    <h2 class="text-xl font-bold text-white"><i class="fas fa-users mr-1"></i> Data Wali / Orang Tua</h2>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-user mr-1"></i> Nama Wali</label>
                    <input type="text" name="guardian_name" value="{{ old('guardian_name', $student->guardian_name) }}" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-mobile-alt mr-1"></i> Telepon Wali</label>
                    <input type="text" name="guardian_phone" value="{{ old('guardian_phone', $student->guardian_phone) }}" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-briefcase mr-1"></i> Pekerjaan Wali</label>
                    @php $selOccupation = old('guardian_occupation', $student->guardian_occupation); @endphp
                    <select name="guardian_occupation" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                        <option value="" disabled {{ !$selOccupation ? 'selected' : '' }}>-- Pilih Pekerjaan --</option>
                        <option value="PNS" {{ $selOccupation == 'PNS' ? 'selected' : '' }}>PNS</option>
                        <option value="TNI/Polri" {{ $selOccupation == 'TNI/Polri' ? 'selected' : '' }}>TNI/Polri</option>
                        <option value="Guru/Dosen" {{ $selOccupation == 'Guru/Dosen' ? 'selected' : '' }}>Guru/Dosen</option>
                        <option value="Dokter/Tenaga Medis" {{ $selOccupation == 'Dokter/Tenaga Medis' ? 'selected' : '' }}>Dokter/Tenaga Medis</option>
                        <option value="Pegawai Swasta" {{ $selOccupation == 'Pegawai Swasta' ? 'selected' : '' }}>Pegawai Swasta</option>
                        <option value="Wiraswasta" {{ $selOccupation == 'Wiraswasta' ? 'selected' : '' }}>Wiraswasta</option>
                        <option value="Pedagang" {{ $selOccupation == 'Pedagang' ? 'selected' : '' }}>Pedagang</option>
                        <option value="Petani" {{ $selOccupation == 'Petani' ? 'selected' : '' }}>Petani</option>
                        <option value="Nelayan" {{ $selOccupation == 'Nelayan' ? 'selected' : '' }}>Nelayan</option>
                        <option value="Buruh" {{ $selOccupation == 'Buruh' ? 'selected' : '' }}>Buruh</option>
                        <option value="Pensiunan" {{ $selOccupation == 'Pensiunan' ? 'selected' : '' }}>Pensiunan</option>
                        <option value="Ibu Rumah Tangga" {{ $selOccupation == 'Ibu Rumah Tangga' ? 'selected' : '' }}>Ibu Rumah Tangga</option>
                        <option value="Tidak Bekerja" {{ $selOccupation == 'Tidak Bekerja' ? 'selected' : '' }}>Tidak Bekerja</option>
                        <option value="Lainnya" {{ $selOccupation == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-home mr-1"></i> Alamat Wali</label>
                    <textarea name="guardian_address" rows="3" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition">{{ old('guardian_address', $student->guardian_address) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Section 3: Info Tambahan -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold">3</span>
                    </div>
                    <h2 class="text-xl font-bold text-white"><i class="fas fa-bullseye mr-1"></i> Informasi Tambahan</h2>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-id-card mr-1"></i> RFID UID Card</label>
                    <input type="text" name="rfid_uid" value="{{ old('rfid_uid', $student->rfid_uid) }}" placeholder="Contoh: 04 XX 19 XX" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika siswa belum memiliki kartu ID. Jika punya, tap kartu di alat pembaca ke form ini atau masukkan kode UID-nya.</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-palette mr-1"></i> Hobby</label>
                    <input type="text" name="hobby" value="{{ old('hobby', $student->hobby) }}" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-hospital mr-1"></i> Riwayat Kesehatan</label>
                    <textarea name="health_history" rows="3" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">{{ old('health_history', $student->health_history) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-camera mr-1"></i> Foto (JPG/PNG, max 2MB)</label>
                    <div class="mt-2">
                        <img id="photo-preview" src="{{ $student->photo_url }}" class="w-32 h-32 object-cover mb-3 border-4 border-gray-200 rounded-xl shadow" alt="Preview">
                    </div>
                    <input type="hidden" name="remove_photo" id="remove-photo" value="0">
                    <div class="flex items-center gap-2">
                        <input type="file" name="photo" id="photo-input" accept="image/*" class="flex-1 border-2 border-gray-200 p-2 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                        @if($student->photo)
                        <button type="button" id="remove-photo-btn" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-xl transition duration-200 shadow">Hapus</button>
                        @endif
                    </div>
                    <div id="photo-error" class="text-red-600 text-sm mt-2"></div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-chart-bar mr-1"></i> Status Siswa</label>
                    <select name="status" class="w-full border-2 border-gray-200 p-3 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                        <option value="aktif" {{ $student->status=='aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="lulus" {{ $student->status=='lulus' ? 'selected' : '' }}>Lulus</option>
                        <option value="keluar" {{ $student->status=='keluar' ? 'selected' : '' }}>Keluar</option>
                        <option value="pindah" {{ $student->status=='pindah' ? 'selected' : '' }}>Pindah</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3">
            <button type="submit" class="flex-1 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-6 py-3 rounded-xl font-semibold shadow-lg transition duration-200 transform hover:scale-105">
                <i class="fas fa-save mr-1"></i> Simpan Perubahan
            </button>
            <a href="{{ request('return_to', route('admin.students.index')) }}" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-semibold transition duration-200">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var placeholder = "{{ asset('images/default-student.jpg') }}";

        function showError(msg) {
            var el = document.getElementById('photo-error');
            if (el) el.textContent = msg;
        }

        function clearError() {
            var el = document.getElementById('photo-error');
            if (el) el.textContent = '';
        }

        function validateFile(file) {
            if (!file) return true;
            var allowed = ['image/jpeg', 'image/png', 'image/jpg'];
            if (allowed.indexOf(file.type) === -1) return 'Tipe file harus JPG atau PNG.';
            var max = 2048 * 1024; // 2MB
            if (file.size > max) return 'Ukuran file maksimal 2MB.';
            return true;
        }

        function setupPreview(inputId, previewId) {
            var input = document.getElementById(inputId);
            var preview = document.getElementById(previewId);
            var removeBtn = document.getElementById('remove-photo-btn');
            var removeInput = document.getElementById('remove-photo');
            if (!input || !preview) return;
            input.addEventListener('change', function() {
                clearError();
                if (removeInput) removeInput.value = 0;
                var file = this.files && this.files[0];
                var ok = validateFile(file);
                if (ok !== true) {
                    showError(ok);
                    this.value = '';
                    preview.src = placeholder;
                    return;
                }
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.src = placeholder;
                }
            });
            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    if (!confirm('Hapus foto ini?')) return;
                    if (removeInput) removeInput.value = 1;
                    input.value = '';
                    preview.src = placeholder;
                    clearError();
                });
            }
        }
        setupPreview('photo-input', 'photo-preview');
    });
</script>

@endsection