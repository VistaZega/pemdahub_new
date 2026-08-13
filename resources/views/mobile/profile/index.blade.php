@extends('mobile.layouts.app')

@section('title', 'Profil Saya - PembdaHUB Mobile')

@section('content')
<div class="space-y-4">
    <!-- Profile Card Header -->
    <div class="bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-700 rounded-3xl p-5 text-center text-white shadow-xl shadow-indigo-500/20 relative overflow-hidden">
        <div class="inline-block relative mb-3">
            @if($student && $student->photo_url)
                <img src="{{ $student->photo_url }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-3xl object-cover border-4 border-white/40 shadow-xl mx-auto">
            @else
                <div class="w-20 h-20 rounded-3xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white font-black text-2xl shadow-xl border-4 border-white/40 mx-auto">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
            @endif
            <span class="absolute bottom-0 right-0 w-4.5 h-4.5 rounded-full bg-emerald-400 border-2 border-indigo-700"></span>
        </div>

        @php $activeRole = session('active_role', $user->role); @endphp
        <h2 class="text-lg font-extrabold text-white leading-tight">{{ $user->name }}</h2>
        <span class="inline-block mt-1 px-3 py-0.5 rounded-full bg-white/20 text-white text-[10px] font-extrabold tracking-wide uppercase border border-white/30">
            {{ strtoupper($activeRole) }}
        </span>

        @if($student && $student->school)
            <p class="text-xs text-indigo-100/90 mt-2 font-medium"><i class="fa-solid fa-school text-indigo-200 mr-1"></i>{{ $student->school->name }}</p>
        @endif
    </div>

    <!-- Role Switcher Widget (If Multi-Role) -->
    @if($user->isOwnerOrSuperAdmin() || $user->isGuru() || $user->isAdminSekolah() || $user->isKepalaSekolah())
    <div class="glass-card rounded-2xl p-4 space-y-3 border border-indigo-200/80 bg-indigo-50/50">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-extrabold text-indigo-900 flex items-center gap-2">
                <i class="fa-solid fa-repeat text-indigo-600"></i> Fasilitas Beralih Peran (Switch Role)
            </h3>
        </div>
        <p class="text-[11px] text-slate-600 font-medium">Beralih mode tampilan aplikasi sesuai hak akses Anda:</p>

        <form action="{{ route('mobile.switch-role') }}" method="POST" class="grid grid-cols-2 gap-2 pt-1">
            @csrf
            @if($user->isOwnerOrSuperAdmin())
                <button type="submit" name="role" value="superadmin" 
                        class="p-2.5 rounded-xl border text-xs font-bold transition flex items-center justify-center gap-1.5 {{ $activeRole === 'superadmin' ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
                    <span>👑 Admin</span>
                </button>
            @endif

            @if($user->isOwnerOrSuperAdmin() || $user->isGuru() || $user->isAdminSekolah())
                <button type="submit" name="role" value="guru" 
                        class="p-2.5 rounded-xl border text-xs font-bold transition flex items-center justify-center gap-1.5 {{ $activeRole === 'guru' ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
                    <span>👨‍🏫 Guru</span>
                </button>
            @endif

            @if($user->isOwnerOrSuperAdmin() || $user->hasRole('siswa'))
                <button type="submit" name="role" value="siswa" 
                        class="p-2.5 rounded-xl border text-xs font-bold transition flex items-center justify-center gap-1.5 {{ $activeRole === 'siswa' ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50' }}">
                    <span>🎓 Siswa</span>
                </button>
            @endif
        </form>
    </div>
    @endif

    <!-- Account Detail Group -->
    <div class="glass-card rounded-2xl p-4 space-y-3">
        <h3 class="text-xs font-extrabold text-slate-500 uppercase tracking-wider">Informasi Akun</h3>

        <div class="space-y-2.5 text-xs font-medium">
            <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                <span class="text-slate-500">Email</span>
                <span class="font-bold text-slate-900">{{ $user->email }}</span>
            </div>

            <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                <span class="text-slate-500">Username</span>
                <span class="font-bold text-slate-900">{{ $user->username ?? '-' }}</span>
            </div>

            @if($student)
            <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                <span class="text-slate-500">NISN</span>
                <span class="font-bold text-slate-900">{{ $student->nisn ?? '-' }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Quick Navigation Links -->
    <div class="glass-card rounded-2xl p-2 space-y-1">
        <a href="{{ url('/') }}" class="p-3 rounded-xl hover:bg-slate-50 transition flex items-center justify-between text-xs text-slate-700 font-bold">
            <div class="flex items-center space-x-3">
                <i class="fa-solid fa-desktop text-indigo-600 text-sm"></i>
                <span>Beralih ke Versi Desktop (Web)</span>
            </div>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
        </a>
    </div>

    <!-- Logout Form -->
    <form action="{{ route('mobile.logout') }}" method="POST">
        @csrf
        <button type="submit" 
                onclick="return confirm('Apakah Anda yakin ingin keluar dari aplikasi PembdaHUB Mobile?')"
                class="w-full py-3.5 px-4 bg-rose-50 border border-rose-200 text-rose-700 font-extrabold text-xs rounded-2xl hover:bg-rose-100 transition flex items-center justify-center gap-2">
            <i class="fa-solid fa-right-from-bracket"></i> Keluar dari Akun
        </button>
    </form>
</div>
@endsection
