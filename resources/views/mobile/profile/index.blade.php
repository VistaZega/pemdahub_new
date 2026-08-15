@extends('mobile.layouts.app')

@section('title', 'Profil & Update Data Lengkap - PembdaHUB Mobile')

@section('content')
<div class="space-y-4" x-data="{ showPasswordFields: false, activeTab: 'dasar' }">
    <!-- Form Upload Foto & Update Data Lengkap -->
    <form action="{{ route('mobile.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <!-- Profile Hero Card with Photo Picker -->
        <div class="clay-blue p-6 text-center text-white relative overflow-hidden space-y-3">
            <div class="inline-block relative mb-1">
                <img id="avatarPreview" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" 
                     class="w-24 h-24 rounded-3xl object-cover border-4 border-white/80 shadow-2xl mx-auto bg-white transition duration-300">
                
                <!-- Camera Upload Badge Button -->
                <label for="profilePhotoInput" 
                       class="absolute -bottom-1 -right-1 w-9 h-9 rounded-2xl bg-white text-blue-700 shadow-lg border-2 border-blue-200 flex items-center justify-center cursor-pointer hover:scale-110 active:scale-95 transition">
                    <i class="fa-solid fa-camera text-sm"></i>
                    <input type="file" id="profilePhotoInput" name="photo" accept="image/*" class="hidden" 
                           onchange="previewAvatar(event)">
                </label>
            </div>

            @php $activeRole = session('active_role', $user->role); @endphp
            <div>
                <h2 class="text-xl font-black text-white leading-tight tracking-tight">{{ $user->name }}</h2>
                <span class="inline-block mt-1 px-3.5 py-0.5 rounded-full bg-white/30 text-white text-[10px] font-black tracking-wide uppercase border border-white/40 shadow-sm backdrop-blur-sm">
                    {{ strtoupper($activeRole) }}
                </span>

                @if($student && $student->school)
                    <p class="text-xs text-blue-100 mt-2 font-extrabold"><i class="fa-solid fa-school mr-1"></i>{{ $student->school->name }}</p>
                @elseif($teacher && $teacher->school)
                    <p class="text-xs text-blue-100 mt-2 font-extrabold"><i class="fa-solid fa-school mr-1"></i>{{ $teacher->school->name }}</p>
                @endif
            </div>

            <p class="text-[10px] text-blue-100 font-bold italic pt-1">
                💡 Klik ikon kamera di foto untuk mengganti foto profil Anda.
            </p>
        </div>

        <!-- TAB NAVIGATION (DATA AKUN vs BIODATA LENGKAP) -->
        <div class="grid grid-cols-2 gap-2 bg-slate-100 p-1.5 rounded-2xl border border-slate-200">
            <button type="button" @click="activeTab = 'dasar'"
                    class="py-2.5 text-center text-xs font-black rounded-xl transition flex items-center justify-center gap-1.5"
                    :class="activeTab === 'dasar' ? 'bg-white text-blue-700 shadow-md border-2 border-blue-200 scale-102' : 'text-slate-500 hover:text-slate-900'">
                <span>👤 Data Akun</span>
            </button>

            <button type="button" @click="activeTab = 'biodata'"
                    class="py-2.5 text-center text-xs font-black rounded-xl transition flex items-center justify-center gap-1.5"
                    :class="activeTab === 'biodata' ? 'bg-white text-blue-700 shadow-md border-2 border-blue-200 scale-102' : 'text-slate-500 hover:text-slate-900'">
                <span>📋 Biodata Lengkap</span>
            </button>
        </div>

        <!-- SECTION 1: DATA UTAMA AKUN -->
        <div x-show="activeTab === 'dasar'" x-transition class="clay-card p-5 space-y-4 bg-white border-2 border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <h3 class="text-xs font-black text-slate-900 flex items-center gap-2 uppercase tracking-wider">
                    <i class="fa-solid fa-user-gear text-blue-600 text-sm"></i> Informasi Dasar Akun
                </h3>
            </div>

            <div class="space-y-3">
                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-[11px] font-black text-slate-700 uppercase tracking-wider mb-1">Nama Lengkap</label>
                    <div class="relative">
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold focus:outline-none focus:border-blue-600 transition">
                        <i class="fa-solid fa-id-card absolute left-3.5 top-3.5 text-slate-400 text-xs"></i>
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-[11px] font-black text-slate-700 uppercase tracking-wider mb-1">Alamat Email</label>
                    <div class="relative">
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold focus:outline-none focus:border-blue-600 transition">
                        <i class="fa-solid fa-envelope absolute left-3.5 top-3.5 text-slate-400 text-xs"></i>
                    </div>
                </div>

                <!-- Nomor HP / WhatsApp -->
                <div>
                    <label class="block text-[11px] font-black text-slate-700 uppercase tracking-wider mb-1">Nomor WhatsApp / Telepon</label>
                    <div class="relative">
                        <input type="text" name="phone" value="{{ old('phone', $user->phone ?? ($student?->phone ?? ($teacher?->phone ?? ''))) }}" placeholder="08xxxxxxxxxx"
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold focus:outline-none focus:border-blue-600 transition">
                        <i class="fa-brands fa-whatsapp absolute left-3.5 top-3.5 text-slate-400 text-xs"></i>
                    </div>
                </div>

                <!-- Opsi Ubah Kata Sandi -->
                <div class="pt-2 border-t border-slate-100">
                    <button type="button" @click="showPasswordFields = !showPasswordFields"
                            class="text-xs font-black text-blue-600 hover:underline flex items-center gap-1.5">
                        <i class="fa-solid fa-lock text-xs"></i>
                        <span>Ubah Kata Sandi (Opsional)</span>
                        <i class="fa-solid fa-chevron-down text-[10px] transition" :class="{ 'rotate-180': showPasswordFields }"></i>
                    </button>

                    <div x-show="showPasswordFields" x-transition class="space-y-3 pt-3">
                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase mb-1">Kata Sandi Saat Ini</label>
                            <input type="password" name="current_password" placeholder="••••••••"
                                   class="w-full px-3.5 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-blue-600 transition">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase mb-1">Kata Sandi Baru</label>
                            <input type="password" name="new_password" placeholder="Minimal 6 karakter"
                                   class="w-full px-3.5 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-blue-600 transition">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase mb-1">Konfirmasi Kata Sandi Baru</label>
                            <input type="password" name="new_password_confirmation" placeholder="Ulangi kata sandi baru"
                                   class="w-full px-3.5 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-blue-600 transition">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: BIODATA LENGKAP (SISWA / GURU / PEGAWAI) -->
        <div x-show="activeTab === 'biodata'" x-transition class="clay-card p-5 space-y-4 bg-white border-2 border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <h3 class="text-xs font-black text-slate-900 flex items-center gap-2 uppercase tracking-wider">
                    <i class="fa-solid fa-address-card text-blue-600 text-sm"></i> 
                    {{ $student ? 'Biodata Lengkap Siswa' : ($teacher ? 'Biodata Lengkap Guru & Pegawai' : 'Biodata Diri Pengguna') }}
                </h3>
            </div>

            <div class="space-y-3">
                @if($student)
                    <!-- FIELD KHUSUS SISWA -->
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase mb-1">NISN</label>
                            <input type="text" name="nisn" value="{{ old('nisn', $student->nisn) }}" placeholder="Nomor NISN"
                                   class="w-full px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase mb-1">NIS Sekolah</label>
                            <input type="text" name="nis" value="{{ old('nis', $student->nis) }}" placeholder="Nomor NIS"
                                   class="w-full px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase mb-1">Tempat Lahir</label>
                            <input type="text" name="birth_place" value="{{ old('birth_place', $student->birth_place) }}" placeholder="Kota Lahir"
                                   class="w-full px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase mb-1">Tanggal Lahir</label>
                            <input type="date" name="birth_date" value="{{ old('birth_date', $student->birth_date?->format('Y-m-d')) }}"
                                   class="w-full px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase mb-1">Jenis Kelamin</label>
                            <select name="gender" class="w-full px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold">
                                <option value="">-- Pilih --</option>
                                <option value="L" {{ old('gender', $student->gender) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('gender', $student->gender) === 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase mb-1">Agama</label>
                            <input type="text" name="religion" value="{{ old('religion', $student->religion) }}" placeholder="Agama"
                                   class="w-full px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-600 uppercase mb-1">Alamat Tempat Tinggal</label>
                        <textarea name="address" rows="2" placeholder="Alamat lengkap siswa..."
                                  class="w-full p-3 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold resize-none">{{ old('address', $student->address) }}</textarea>
                    </div>

                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-2xl space-y-2">
                        <span class="text-[10px] font-black text-blue-900 uppercase block">👨‍👩‍👧 Data Orang Tua / Wali</span>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[9px] font-black text-slate-600 uppercase mb-1">Nama Orang Tua</label>
                                <input type="text" name="parent_name" value="{{ old('parent_name', $student->parent_name) }}" placeholder="Nama Ibu/Ayah"
                                       class="w-full px-2.5 py-1.5 bg-white border border-blue-200 rounded-lg text-slate-900 text-xs font-bold">
                            </div>
                            <div>
                                <label class="block text-[9px] font-black text-slate-600 uppercase mb-1">No HP Orang Tua</label>
                                <input type="text" name="parent_phone" value="{{ old('parent_phone', $student->parent_phone) }}" placeholder="08xxxxxxxxxx"
                                       class="w-full px-2.5 py-1.5 bg-white border border-blue-200 rounded-lg text-slate-900 text-xs font-bold">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-600 uppercase mb-1">Hobi / Minat Bakat</label>
                        <input type="text" name="hobby" value="{{ old('hobby', $student->hobby) }}" placeholder="Contoh: Olahraga, Seni, Coding"
                               class="w-full px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold">
                    </div>
                @elseif($teacher)
                    <!-- FIELD KHUSUS GURU / PEGAWAI -->
                    <div>
                        <label class="block text-[10px] font-black text-slate-600 uppercase mb-1">NIP / NPT</label>
                        <input type="text" name="nip" value="{{ old('nip', $teacher->nip) }}" placeholder="Nomor NIP/NPT Guru"
                               class="w-full px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase mb-1">Tempat Lahir</label>
                            <input type="text" name="birth_place" value="{{ old('birth_place', $teacher->birth_place) }}" placeholder="Kota Lahir"
                                   class="w-full px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase mb-1">Tanggal Lahir</label>
                            <input type="date" name="birth_date" value="{{ old('birth_date', $teacher->birth_date?->format('Y-m-d')) }}"
                                   class="w-full px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase mb-1">Jenis Kelamin</label>
                            <select name="gender" class="w-full px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold">
                                <option value="">-- Pilih --</option>
                                <option value="L" {{ old('gender', $teacher->gender) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('gender', $teacher->gender) === 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase mb-1">Agama</label>
                            <input type="text" name="religion" value="{{ old('religion', $teacher->religion) }}" placeholder="Agama"
                                   class="w-full px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase mb-1">Pendidikan Terakhir</label>
                            <input type="text" name="education_level" value="{{ old('education_level', $teacher->education_level) }}" placeholder="S1 / S2 / S3"
                                   class="w-full px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-600 uppercase mb-1">Jurusan</label>
                            <input type="text" name="major" value="{{ old('major', $teacher->major) }}" placeholder="Jurusan Studi"
                                   class="w-full px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-600 uppercase mb-1">Jabatan / Posisi</label>
                        <input type="text" name="position" value="{{ old('position', $teacher->position) }}" placeholder="Jabatan Mengajar / Staf"
                               class="w-full px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-600 uppercase mb-1">Alamat Tempat Tinggal</label>
                        <textarea name="address" rows="2" placeholder="Alamat lengkap guru..."
                                  class="w-full p-3 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold resize-none">{{ old('address', $teacher->address) }}</textarea>
                    </div>
                @else
                    <div class="p-4 text-center text-slate-500 text-xs font-bold">
                        Profil ini terdaftar sebagai Admin / Pengguna Umum.
                    </div>
                @endif
            </div>
        </div>

        <!-- Tombol Simpan Perubahan Utama -->
        <button type="submit" 
                class="clay-btn w-full py-3.5 text-white text-xs font-black shadow-lg flex items-center justify-center gap-2">
            <i class="fa-solid fa-floppy-disk text-xs"></i> Simpan Seluruh Data Profil
        </button>
    </form>

    <!-- Role Switcher Widget (If Multi-Role) -->
    @if($user->isOwnerOrSuperAdmin() || $user->isGuru() || $user->isAdminSekolah() || $user->isKepalaSekolah())
    <div class="clay-card p-5 space-y-3 border-2 border-blue-200">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-repeat text-blue-600"></i> Fasilitas Beralih Peran (Switch Role)
            </h3>
        </div>
        <p class="text-[11px] text-slate-600 font-bold">Beralih mode tampilan aplikasi sesuai hak akses Anda:</p>

        <form action="{{ route('mobile.switch-role') }}" method="POST" class="grid grid-cols-2 gap-2 pt-1">
            @csrf
            @if($user->isOwnerOrSuperAdmin())
                <button type="submit" name="role" value="superadmin" 
                        class="p-3 rounded-2xl border-2 text-xs font-black transition flex items-center justify-center gap-1.5 {{ $activeRole === 'superadmin' ? 'clay-blue text-white shadow-md' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
                    <span>👑 Admin</span>
                </button>

                <button type="submit" name="role" value="ketua_yayasan" 
                        class="p-3 rounded-2xl border-2 text-xs font-black transition flex items-center justify-center gap-1.5 {{ $activeRole === 'ketua_yayasan' ? 'clay-purple text-white shadow-md' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
                    <span>🏛️ Yayasan</span>
                </button>
            @endif

            @if($user->isOwnerOrSuperAdmin() || $user->isGuru() || $user->isAdminSekolah())
                <button type="submit" name="role" value="guru" 
                        class="p-3 rounded-2xl border-2 text-xs font-black transition flex items-center justify-center gap-1.5 {{ $activeRole === 'guru' ? 'clay-purple text-white shadow-md' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
                    <span>👨‍🏫 Guru</span>
                </button>
            @endif

            @if($user->isOwnerOrSuperAdmin() || $user->hasRole('siswa'))
                <button type="submit" name="role" value="siswa" 
                        class="p-3 rounded-2xl border-2 text-xs font-black transition flex items-center justify-center gap-1.5 {{ $activeRole === 'siswa' ? 'clay-blue text-white shadow-md' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
                    <span>🎓 Siswa</span>
                </button>
            @endif
        </form>
    </div>
    @endif

    <!-- Quick Navigation Links & Logout Section -->
    <div class="clay-card p-2 space-y-1.5 bg-white border-2 border-slate-200">
        <a href="{{ url('/') }}" class="p-3.5 rounded-2xl hover:bg-slate-50 transition flex items-center justify-between text-xs text-slate-800 font-black">
            <div class="flex items-center space-x-3">
                <i class="fa-solid fa-desktop text-blue-600 text-sm"></i>
                <span>Beralih ke Versi Desktop (Web)</span>
            </div>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
        </a>

        <!-- Form Tombol Keluar / Logout -->
        <form action="{{ route('mobile.logout') }}" method="POST" class="pt-1">
            @csrf
            <button type="submit" 
                    onclick="return confirm('Apakah Anda yakin ingin keluar dari akun PembdaHUB Mobile?')"
                    class="w-full p-3.5 rounded-2xl bg-rose-50 border-2 border-rose-200 text-rose-700 hover:bg-rose-600 hover:text-white transition flex items-center justify-center gap-2 text-xs font-black shadow-xs group">
                <i class="fa-solid fa-right-from-bracket text-sm text-rose-600 group-hover:text-white"></i>
                <span>Keluar dari Aplikasi (Logout)</span>
            </button>
        </form>
    </div>
</div>

<script>
    function previewAvatar(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    }
</script>
@endsection
