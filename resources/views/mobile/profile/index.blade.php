@extends('mobile.layouts.app')

@section('title', 'Profil Saya 3D - PembdaHUB Mobile')

@section('content')
<div class="space-y-4" x-data="{ showPasswordFields: false }">
    <!-- Form Upload Foto & Edit Profil -->
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
                @endif
            </div>

            <p class="text-[10px] text-blue-100 font-bold italic pt-1">
                💡 Klik ikon kamera di atas foto profil untuk mengganti foto Anda.
            </p>
        </div>

        <!-- FORM EDIT DATA PROFIL LENGKAP -->
        <div class="clay-card p-5 space-y-4 bg-white border-2 border-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <h3 class="text-xs font-black text-slate-900 flex items-center gap-2 uppercase tracking-wider">
                    <i class="fa-solid fa-user-pen text-blue-600 text-sm"></i> Edit Profil Lengkap
                </h3>
                <span class="text-[10px] text-slate-400 font-bold">Informasi Diri</span>
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

            <!-- Tombol Simpan Perubahan -->
            <button type="submit" 
                    class="clay-btn w-full py-3 text-white text-xs font-black shadow-lg flex items-center justify-center gap-2">
                <i class="fa-solid fa-floppy-disk text-xs"></i> Simpan Perubahan Profil
            </button>
        </div>
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

    <!-- Account Detail Group (Clay Card) -->
    <div class="clay-card p-5 space-y-3">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-wider">Informasi Akun</h3>

        <div class="space-y-2.5 text-xs font-bold">
            <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                <span class="text-slate-500">Username</span>
                <span class="font-black text-slate-900">{{ $user->username ?? '-' }}</span>
            </div>

            @if($student)
            <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                <span class="text-slate-500">NISN</span>
                <span class="font-black text-slate-900">{{ $student->nisn ?? '-' }}</span>
            </div>
            @endif

            @if($teacher)
            <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                <span class="text-slate-500">NIP / NPT</span>
                <span class="font-black text-slate-900">{{ $teacher->nip ?? '-' }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Quick Navigation Links -->
    <div class="clay-card p-2 space-y-1">
        <a href="{{ url('/') }}" class="p-3.5 rounded-2xl hover:bg-slate-50 transition flex items-center justify-between text-xs text-slate-800 font-black">
            <div class="flex items-center space-x-3">
                <i class="fa-solid fa-desktop text-blue-600 text-sm"></i>
                <span>Beralih ke Versi Desktop (Web)</span>
            </div>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
        </a>
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
