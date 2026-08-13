@extends('mobile.layouts.app')

@section('title', 'Profil Saya 3D - PembdaHUB Mobile')

@section('content')
<div class="space-y-4">
    <!-- Profile Hero Card (Clay Blue Card) -->
    <div class="clay-blue p-6 text-center text-white relative overflow-hidden">
        <div class="inline-block relative mb-3">
            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-3xl object-cover border-4 border-white/60 shadow-xl mx-auto bg-white">
            <span class="absolute bottom-0 right-0 w-4.5 h-4.5 rounded-full bg-emerald-400 border-2 border-blue-700"></span>
        </div>

        @php $activeRole = session('active_role', $user->role); @endphp
        <h2 class="text-xl font-black text-white leading-tight tracking-tight">{{ $user->name }}</h2>
        <span class="inline-block mt-1 px-3.5 py-0.5 rounded-full bg-white/30 text-white text-[10px] font-black tracking-wide uppercase border border-white/40 shadow-sm backdrop-blur-sm">
            {{ strtoupper($activeRole) }}
        </span>

        @if($student && $student->school)
            <p class="text-xs text-blue-100 mt-2 font-extrabold"><i class="fa-solid fa-school mr-1"></i>{{ $student->school->name }}</p>
        @endif
    </div>

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
                <span class="text-slate-500">Email</span>
                <span class="font-black text-slate-900">{{ $user->email }}</span>
            </div>

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

    <!-- Logout Form (3D Pink Clay Button) -->
    <form action="{{ route('mobile.logout') }}" method="POST">
        @csrf
        <button type="submit" 
                onclick="return confirm('Apakah Anda yakin ingin keluar dari aplikasi PembdaHUB Mobile?')"
                class="clay-pink w-full py-4 px-4 font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2">
            <i class="fa-solid fa-right-from-bracket"></i> Keluar dari Akun
        </button>
    </form>
</div>
@endsection
