@extends('mobile.layouts.app')

@section('title', 'Login - PembdaHUB Mobile')

@section('content')
<div class="min-h-[80vh] flex flex-col justify-center py-6">
    <!-- Hero Branding -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-500 text-white text-3xl font-black shadow-xl shadow-indigo-500/30 mb-4 animate-bounce duration-1000">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <h2 class="text-2xl font-extrabold text-white tracking-tight">PembdaHUB</h2>
        <p class="text-xs text-indigo-300 font-medium mt-1">Perguruan Pembangunan Daerah Nias</p>
    </div>

    <!-- Glassmorphism Login Card -->
    <div class="glass-card rounded-3xl p-6 shadow-2xl relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl"></div>
        
        <h3 class="text-lg font-bold text-white mb-1">Selamat Datang 👋</h3>
        <p class="text-xs text-slate-400 mb-6">Masuk dengan akun Siswa, Guru, atau Orang Tua</p>

        @if($errors->any())
            <div class="mb-4 p-3 rounded-xl bg-rose-500/20 border border-rose-500/30 text-rose-300 text-xs font-medium">
                <i class="fa-solid fa-circle-exclamation mr-1 text-rose-400"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('mobile.login.post') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Login Input (Email / Username / NIS / NIP) -->
            <div>
                <label for="login" class="block text-xs font-semibold text-slate-300 mb-1.5">Email / Username / NIS</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-sm">
                        <i class="fa-regular fa-user"></i>
                    </div>
                    <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                           placeholder="Masukkan Email/NIS..." 
                           class="w-full pl-10 pr-4 py-3 bg-slate-900/90 border border-slate-700/60 rounded-xl text-white text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                </div>
            </div>

            <!-- Password Input -->
            <div x-data="{ showPass: false }">
                <label for="password" class="block text-xs font-semibold text-slate-300 mb-1.5">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 text-sm">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <input :type="showPass ? 'text' : 'password'" id="password" name="password" required
                           placeholder="••••••••" 
                           class="w-full pl-10 pr-10 py-3 bg-slate-900/90 border border-slate-700/60 rounded-xl text-white text-sm placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                    <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300">
                        <i :class="showPass ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Remember me & Forgot password -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-xs text-slate-400">Ingat Saya</span>
                </label>
                <a href="{{ url('/forgot-password') }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">Lupa Password?</a>
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    class="w-full py-3.5 px-4 bg-gradient-to-r from-indigo-600 to-indigo-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-indigo-600/30 hover:from-indigo-500 hover:to-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-950 transition transform active:scale-95">
                Masuk ke Aplikasi
            </button>
        </form>
    </div>

    <div class="text-center mt-6">
        <p class="text-[11px] text-slate-500">© {{ date('Y') }} YP PEMBDA Nias. All rights reserved.</p>
    </div>
</div>
@endsection
