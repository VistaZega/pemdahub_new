@extends('mobile.layouts.app')

@section('title', 'Profil Saya - PembdaHUB Mobile')

@section('content')
<div class="space-y-4">
    <!-- Profile Card Header -->
    <div class="glass-card rounded-3xl p-5 text-center relative overflow-hidden bg-gradient-to-br from-indigo-950/80 via-slate-900 to-slate-950 border border-indigo-500/30 shadow-xl">
        <div class="inline-block relative mb-3">
            @if($student && $student->photo_url)
                <img src="{{ $student->photo_url }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-3xl object-cover border-4 border-indigo-500/40 shadow-xl mx-auto">
            @else
                <div class="w-20 h-20 rounded-3xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center text-white font-black text-2xl shadow-xl border-4 border-indigo-500/40 mx-auto">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
            @endif
            <span class="absolute bottom-0 right-0 w-4 h-4 rounded-full bg-emerald-500 border-2 border-slate-950"></span>
        </div>

        <h2 class="text-lg font-extrabold text-white leading-tight">{{ $user->name }}</h2>
        <span class="inline-block mt-1 px-3 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 text-[10px] font-extrabold tracking-wide uppercase border border-indigo-500/30">
            {{ strtoupper($user->role) }}
        </span>

        @if($student && $student->school)
            <p class="text-xs text-slate-400 mt-2"><i class="fa-solid fa-school text-indigo-400 mr-1"></i>{{ $student->school->name }}</p>
        @endif
    </div>

    <!-- Account Detail Group -->
    <div class="glass-card rounded-2xl p-4 space-y-3">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Informasi Akun</h3>

        <div class="space-y-2.5 text-xs">
            <div class="flex items-center justify-between py-1.5 border-b border-slate-800">
                <span class="text-slate-400">Email</span>
                <span class="font-semibold text-white">{{ $user->email }}</span>
            </div>

            <div class="flex items-center justify-between py-1.5 border-b border-slate-800">
                <span class="text-slate-400">Username</span>
                <span class="font-semibold text-white">{{ $user->username ?? '-' }}</span>
            </div>

            @if($student)
            <div class="flex items-center justify-between py-1.5 border-b border-slate-800">
                <span class="text-slate-400">NISN</span>
                <span class="font-semibold text-white">{{ $student->nisn ?? '-' }}</span>
            </div>
            <div class="flex items-center justify-between py-1.5 border-b border-slate-800">
                <span class="text-slate-400">Tahun Angkatan</span>
                <span class="font-semibold text-white">{{ $student->entry_year ?? '-' }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Quick Navigation Links -->
    <div class="glass-card rounded-2xl p-2 space-y-1">
        <a href="{{ url('/') }}" class="p-3 rounded-xl hover:bg-slate-800/80 transition flex items-center justify-between text-xs text-slate-200">
            <div class="flex items-center space-x-3">
                <i class="fa-solid fa-desktop text-indigo-400 text-sm"></i>
                <span class="font-medium">Beralih ke Versi Desktop (Web)</span>
            </div>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-500"></i>
        </a>
    </div>

    <!-- Logout Form -->
    <form action="{{ route('mobile.logout') }}" method="POST">
        @csrf
        <button type="submit" 
                onclick="return confirm('Apakah Anda yakin ingin keluar dari aplikasi PembdaHUB Mobile?')"
                class="w-full py-3.5 px-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 font-bold text-xs rounded-2xl hover:bg-rose-500/20 transition flex items-center justify-center gap-2">
            <i class="fa-solid fa-right-from-bracket"></i> Keluar dari Akun
        </button>
    </form>
</div>
@endsection
