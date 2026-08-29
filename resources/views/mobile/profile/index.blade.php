@php 
    $activeRole = session('active_role', $user->role); 
    $isStudent = ($user->role === 'siswa') || ($student && !$user->isGuru() && !$user->isAdminSekolah() && !$user->isOwnerOrSuperAdmin());
@endphp

<div class="space-y-4" x-data="{ showPasswordFields: {{ $isStudent ? 'true' : 'false' }}, activeTab: 'dasar' }">
    <!-- Form Update Profile / Keamanan -->
    <form action="{{ route('mobile.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <!-- Profile Hero Card with Photo -->
        <div class="clay-blue p-6 text-center text-white relative overflow-hidden space-y-3">
            <div class="inline-block relative mb-1">
                <img id="avatarPreview" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" 
                     class="w-24 h-24 rounded-3xl object-cover border-4 border-white/80 shadow-2xl mx-auto bg-white transition duration-300">
                
                @if(!$isStudent)
                    <!-- Camera Upload Badge Button (Khusus Guru / Pegawai / Admin) -->
                    <label for="profilePhotoInput" 
                           class="absolute -bottom-1 -right-1 w-9 h-9 rounded-2xl bg-white text-blue-700 shadow-lg border-2 border-blue-200 flex items-center justify-center cursor-pointer hover:scale-110 active:scale-95 transition">
                        <i class="fa-solid fa-camera text-sm"></i>
                        <input type="file" id="profilePhotoInput" name="photo" accept="image/*" class="hidden" 
                               onchange="previewAvatar(event)">
                    </label>
                @endif
            </div>

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

            @if($isStudent)
                <p class="text-[10px] text-blue-100 font-bold italic pt-1">
                    🛡️ Identitas dan foto profil siswa dikelola terpusat oleh Admin / Operator Sekolah.
                </p>
            @else
                <p class="text-[10px] text-blue-100 font-bold italic pt-1">
                    💡 Klik ikon kamera di foto untuk mengganti foto profil Anda.
                </p>
            @endif
        </div>

        <!-- TAB NAVIGATION -->
        <div class="grid grid-cols-2 gap-2 bg-slate-100 p-1.5 rounded-2xl border border-slate-200">
            <button type="button" @click="activeTab = 'dasar'"
                    class="py-2.5 text-center text-xs font-black rounded-xl transition flex items-center justify-center gap-1.5"
                    :class="activeTab === 'dasar' ? 'bg-white text-blue-700 shadow-md border-2 border-blue-200 scale-102' : 'text-slate-500 hover:text-slate-900'">
                <span>{{ $isStudent ? '🔒 Keamanan Sandi' : '👤 Data Akun' }}</span>
            </button>

            <button type="button" @click="activeTab = 'biodata'"
                    class="py-2.5 text-center text-xs font-black rounded-xl transition flex items-center justify-center gap-1.5"
                    :class="activeTab === 'biodata' ? 'bg-white text-blue-700 shadow-md border-2 border-blue-200 scale-102' : 'text-slate-500 hover:text-slate-900'">
                <span>{{ $isStudent ? '📋 Biodata Siswa' : '📋 Biodata Lengkap' }}</span>
            </button>
        </div>

        @if($isStudent)
            {{-- ================= KHUSUS VIEW SISWA ================= --}}
            <!-- SECTION 1: KEAMANAN KATA SANDI SISWA -->
            <div x-show="activeTab === 'dasar'" x-transition class="clay-card p-5 space-y-4 bg-white border-2 border-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <h3 class="text-xs font-black text-slate-900 flex items-center gap-2 uppercase tracking-wider">
                        <i class="fa-solid fa-shield-halved text-amber-500 text-sm"></i> Keamanan Kata Sandi Akun
                    </h3>
                </div>

                <div class="p-3 bg-amber-50 border border-amber-200 rounded-2xl text-[11px] text-amber-900 leading-relaxed font-semibold">
                    💡 Anda dapat memperbarui kata sandi akun untuk menjaga keamanan akses portal siswa. Kosongkan jika tidak ingin mengubah kata sandi.
                </div>

                <!-- Info Kredensial (Read-Only) -->
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                        <span class="text-[9px] font-black text-slate-400 uppercase block">NISN / Username</span>
                        <span class="text-xs font-bold text-slate-800 font-mono">{{ $student->nisn ?? $user->username }}</span>
                    </div>
                    <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                        <span class="text-[9px] font-black text-slate-400 uppercase block">Email Terdaftar</span>
                        <span class="text-xs font-bold text-slate-800 truncate block">{{ $user->email }}</span>
                    </div>
                </div>

                <!-- Form Ganti Password -->
                <div class="space-y-3 pt-2">
                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase mb-1">Kata Sandi Saat Ini</label>
                        <input type="password" name="current_password" placeholder="••••••••"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-amber-500 transition">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase mb-1">Kata Sandi Baru (Min. 6 Karakter)</label>
                        <input type="password" name="new_password" placeholder="Masukkan kata sandi baru"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-amber-500 transition">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-700 uppercase mb-1">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" name="new_password_confirmation" placeholder="Ulangi kata sandi baru"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-amber-500 transition">
                    </div>
                </div>
            </div>

            <!-- SECTION 2: BIODATA RESMI SISWA (READ ONLY) -->
            <div x-show="activeTab === 'biodata'" x-transition class="clay-card p-5 space-y-4 bg-white border-2 border-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <h3 class="text-xs font-black text-slate-900 flex items-center gap-2 uppercase tracking-wider">
                        <i class="fa-solid fa-id-badge text-blue-600 text-sm"></i> Data Pokok Siswa Terverifikasi
                    </h3>
                    <span class="px-2 py-0.5 rounded-md text-[9px] font-black bg-emerald-100 text-emerald-700 uppercase">Resmi</span>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="grid grid-cols-2 gap-2">
                        <div class="p-2.5 bg-slate-50 border border-slate-100 rounded-xl">
                            <span class="text-[9px] font-black text-slate-400 uppercase block">NISN</span>
                            <span class="font-bold text-slate-800 font-mono">{{ $student->nisn ?? '-' }}</span>
                        </div>
                        <div class="p-2.5 bg-slate-50 border border-slate-100 rounded-xl">
                            <span class="text-[9px] font-black text-slate-400 uppercase block">NIS Sekolah</span>
                            <span class="font-bold text-slate-800 font-mono">{{ $student->nis ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="p-2.5 bg-slate-50 border border-slate-100 rounded-xl">
                        <span class="text-[9px] font-black text-slate-400 uppercase block">Tempat, Tanggal Lahir</span>
                        <span class="font-bold text-slate-800">{{ $student->birth_place ?? '-' }}, {{ $student->birth_date ? $student->birth_date->format('d M Y') : '-' }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="p-2.5 bg-slate-50 border border-slate-100 rounded-xl">
                            <span class="text-[9px] font-black text-slate-400 uppercase block">Jenis Kelamin</span>
                            <span class="font-bold text-slate-800">{{ $student->gender === 'L' ? 'Laki-laki' : ($student->gender === 'P' ? 'Perempuan' : '-') }}</span>
                        </div>
                        <div class="p-2.5 bg-slate-50 border border-slate-100 rounded-xl">
                            <span class="text-[9px] font-black text-slate-400 uppercase block">Agama</span>
                            <span class="font-bold text-slate-800">{{ $student->religion ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="p-2.5 bg-slate-50 border border-slate-100 rounded-xl">
                        <span class="text-[9px] font-black text-slate-400 uppercase block">Alamat Domisili</span>
                        <span class="font-bold text-slate-800">{{ $student->address ?? '-' }}</span>
                    </div>

                    <div class="p-2.5 bg-slate-50 border border-slate-100 rounded-xl">
                        <span class="text-[9px] font-black text-slate-400 uppercase block">Nama Orang Tua / Wali</span>
                        <span class="font-bold text-slate-800">{{ $student->parent_name ?? ($student->guardian_name ?? '-') }}</span>
                    </div>

                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-[10px] text-blue-900 font-semibold leading-relaxed">
                        ℹ️ Jika terdapat kesalahan biodata siswa, silakan melapor ke Guru Wali Kelas atau Petugas Tata Usaha (Admin) Sekolah.
                    </div>
                </div>
            </div>

            <!-- Tombol Simpan Sandi Siswa -->
            <button type="submit" 
                    class="clay-btn w-full py-3.5 text-white text-xs font-black shadow-lg flex items-center justify-center gap-2">
                <i class="fa-solid fa-shield-halved text-xs"></i> Simpan Perubahan Kata Sandi
            </button>

        @else
            {{-- ================= VIEW GURU / PEGAWAI / ADMIN ================= --}}
            <!-- SECTION 1: DATA UTAMA AKUN GURU/ADMIN -->
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
                            <input type="text" name="phone" value="{{ old('phone', $user->phone ?? ($teacher?->phone ?? '')) }}" placeholder="08xxxxxxxxxx"
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

            <!-- SECTION 2: BIODATA LENGKAP GURU / PEGAWAI -->
            <div x-show="activeTab === 'biodata'" x-transition class="clay-card p-5 space-y-4 bg-white border-2 border-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <h3 class="text-xs font-black text-slate-900 flex items-center gap-2 uppercase tracking-wider">
                        <i class="fa-solid fa-address-card text-blue-600 text-sm"></i> 
                        {{ $teacher ? 'Biodata Lengkap Guru & Pegawai' : 'Biodata Diri Pengguna' }}
                    </h3>
                </div>

                <div class="space-y-3">
                    @if($teacher)
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

            <!-- Tombol Simpan Perubahan Guru/Pegawai -->
            <button type="submit" 
                    class="clay-btn w-full py-3.5 text-white text-xs font-black shadow-lg flex items-center justify-center gap-2">
                <i class="fa-solid fa-floppy-disk text-xs"></i> Simpan Seluruh Data Profil
            </button>
        @endif
    </form>

    <!-- Role & Duty Switcher Widget (Hanya jika memiliki >1 Peran atau >1 Jabatan) -->
    @php
        $profRoles = $user->getAvailableMobileRoles();
        $profDuties = $user->getAvailableDuties();
        $currentDuty = session('active_duty', 'pengampu');
    @endphp

    @if(count($profRoles) > 1 || count($profDuties) > 1)
    <div class="clay-card p-4 sm:p-5 space-y-4 border-2 border-blue-200">
        <!-- 1. Peran Akun Utama -->
        @if(count($profRoles) > 1)
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-repeat text-blue-600"></i> Beralih Peran Akun (Role Switcher)
                </h3>
                <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200">{{ count($profRoles) }} Peran</span>
            </div>
            <p class="text-[11px] text-slate-600 font-bold">Beralih mode hak akses akun:</p>

            <form action="{{ route('mobile.switch-role') }}" method="POST" class="grid grid-cols-2 gap-2 pt-0.5">
                @csrf
                @foreach($profRoles as $r)
                    @php $isCurrent = ($activeRole === $r['key']); @endphp
                    <button type="submit" name="role" value="{{ $r['key'] }}" 
                            class="p-2.5 rounded-2xl border-2 text-xs font-black transition flex items-center justify-start gap-2 {{ $isCurrent ? 'bg-blue-600 text-white border-blue-700 shadow-md' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
                        <span class="text-base shrink-0">{{ $r['icon'] }}</span>
                        <div class="min-w-0 text-left">
                            <span class="truncate block leading-tight text-[11px]">{{ $r['label'] }}</span>
                        </div>
                    </button>
                @endforeach
            </form>
        </div>
        @endif

        <!-- 2. Jabatan & Tugas Struktural -->
        @if(count($profDuties) > 1)
        <div class="space-y-2 pt-2 {{ count($profRoles) > 1 ? 'border-t border-slate-200/80' : '' }}">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-briefcase text-purple-600"></i> Beralih Fokus Jabatan (Duty Switcher)
                </h3>
                <span class="text-[10px] font-black text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full border border-purple-200">{{ count($profDuties) }} Jabatan</span>
            </div>
            <p class="text-[11px] text-slate-600 font-bold">Pilih fokus tugas & tanggung jawab aktif:</p>

            <form action="{{ route('mobile.switch-duty') }}" method="POST" class="grid grid-cols-2 gap-2 pt-0.5">
                @csrf
                @foreach($profDuties as $d)
                    @php $isCurrentDuty = ($currentDuty === $d['key']); @endphp
                    <button type="submit" name="duty" value="{{ $d['key'] }}" 
                            class="p-2.5 rounded-2xl border-2 text-xs font-black transition flex items-center justify-start gap-2 {{ $isCurrentDuty ? 'bg-purple-600 text-white border-purple-700 shadow-md' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
                        <span class="text-base shrink-0">{{ $d['icon'] }}</span>
                        <div class="min-w-0 text-left">
                            <span class="truncate block leading-tight text-[11px]">{{ $d['short_label'] ?? $d['label'] }}</span>
                        </div>
                    </button>
                @endforeach
            </form>
        </div>
        @endif
    </div>
    @endif

    <!-- Quick Navigation Links & Logout Section -->
    <div class="clay-card p-2 space-y-1.5 bg-white border-2 border-slate-200">
        <a href="{{ url('/?switch_mode=desktop') }}" class="p-3.5 rounded-2xl hover:bg-slate-50 transition flex items-center justify-between text-xs text-slate-800 font-black">
            <div class="flex items-center space-x-3">
                <i class="fa-solid fa-desktop text-blue-600 text-sm"></i>
                <span>Beralih ke Versi Desktop (Web)</span>
            </div>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
        </a>

        <!-- Petunjuk Install Aplikasi HP -->
        <div x-data="{ openGuide: false }" class="border-t border-slate-100 pt-1">
            <button @click="openGuide = !openGuide" class="w-full p-3.5 rounded-2xl hover:bg-slate-50 transition flex items-center justify-between text-xs text-slate-800 font-black">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-mobile-screen-button text-purple-600 text-sm"></i>
                    <span>Petunjuk Install Aplikasi di HP</span>
                </div>
                <i class="fa-solid text-[10px] text-slate-400" :class="openGuide ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
            <div x-show="openGuide" x-collapse class="p-3 bg-slate-50 rounded-2xl space-y-2 mt-1 text-[11px]" style="display: none;">
                <div class="font-black text-emerald-700 flex items-center gap-1.5">
                    <i class="fa-brands fa-android text-sm"></i> <span>Android (Chrome):</span>
                </div>
                <p class="text-slate-600 pl-4 leading-relaxed">
                    Buka website $\rightarrow$ Tekan titik tiga (⋮) kanan atas $\rightarrow$ Pilih <strong>"Instal aplikasi"</strong> atau <strong>"Tambahkan ke Layar Utama"</strong>.
                </p>
                <div class="font-black text-blue-700 flex items-center gap-1.5 pt-1">
                    <i class="fa-brands fa-apple text-sm"></i> <span>iPhone (Safari):</span>
                </div>
                <p class="text-slate-600 pl-4 leading-relaxed">
                    Buka website $\rightarrow$ Tekan ikon Bagikan (□↑) $\rightarrow$ Pilih <strong>"Tambahkan ke Layar Utama"</strong>.
                </p>
            </div>
        </div>

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
