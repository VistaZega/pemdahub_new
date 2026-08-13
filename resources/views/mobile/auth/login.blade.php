@extends('mobile.layouts.app')

@section('title', 'Login Playful 3D - PembdaHUB Mobile')

@section('content')
<div class="min-h-[80vh] flex flex-col justify-center py-6">
    <!-- Hero Branding -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-white p-3 shadow-xl shadow-blue-500/20 mb-4 border-4 border-white">
            <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo Pembda" class="w-full h-full object-contain">
        </div>
        <h2 class="text-3xl font-black text-slate-900 tracking-tight">PembdaHUB</h2>
        <p class="text-xs text-blue-600 font-extrabold mt-1 tracking-wide uppercase">Perguruan Pembangunan Daerah Nias</p>
    </div>

    <!-- Login Card (Playful 3D Claymorphism) -->
    <div class="clay-card p-7 relative overflow-hidden bg-white/95 backdrop-blur-md">
        <h3 class="text-xl font-black text-slate-900 mb-1">Selamat Datang! 🚀</h3>
        <p class="text-xs text-slate-500 mb-6 font-bold">Masuk dengan akun Siswa, Guru, atau Orang Tua</p>

        @if($errors->any())
            <div class="mb-5 p-4 rounded-2xl bg-rose-500 text-white text-xs font-black shadow-md border-2 border-white">
                <i class="fa-solid fa-circle-exclamation mr-1.5 text-sm"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('mobile.login.post') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Login Input (Email / Username / NIS) -->
            <div>
                <label for="login" class="block text-xs font-black text-slate-800 mb-1.5">Email / Username / NIS</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 text-sm">
                        <i class="fa-regular fa-user"></i>
                    </div>
                    <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                           placeholder="Masukkan Email/NIS..." 
                           class="w-full pl-11 pr-4 py-3 bg.f4f7fc border-2 border-slate-200/90 rounded-2xl text-slate-900 text-sm placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition font-bold">
                </div>
            </div>

            <!-- Password Input -->
            <div x-data="{ showPass: false }">
                <label for="password" class="block text-xs font-black text-slate-800 mb-1.5">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 text-sm">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <input :type="showPass ? 'text' : 'password'" id="password" name="password" required
                           placeholder="••••••••" 
                           class="w-full pl-11 pr-11 py-3 bg-[#f4f7fc] border-2 border-slate-200/90 rounded-2xl text-slate-900 text-sm placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition font-bold">
                    <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600">
                        <i :class="showPass ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Remember me & Forgot password -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-2 border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-xs text-slate-700 font-bold">Ingat Saya</span>
                </label>
                <a href="{{ url('/forgot-password') }}" class="text-xs font-black text-blue-600 hover:text-blue-700">Lupa Password?</a>
            </div>

            <!-- Submit Button (3D Clay Button) -->
            <button type="submit" 
                    class="clay-btn w-full py-4 px-4 text-white font-black text-sm rounded-2xl uppercase tracking-wider">
                Masuk ke Aplikasi
            </button>
        </form>
    </div>

    <div class="text-center mt-6">
        <p class="text-[11px] text-slate-500 font-bold">© {{ date('Y') }} YP PEMBDA Nias. All rights reserved.</p>
    </div>
</div>
@endsection
