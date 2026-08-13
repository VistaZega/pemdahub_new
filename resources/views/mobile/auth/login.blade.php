@extends('mobile.layouts.app')

@section('title', 'Login Serene - PembdaHUB Mobile')

@section('content')
<div class="min-h-[80vh] flex flex-col justify-center py-6">
    <!-- Hero Branding -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-tr from-purple-300 via-indigo-200 to-rose-200 text-purple-900 text-3xl font-black shadow-lg shadow-purple-200/50 mb-4 border border-white">
            <i class="fa-solid fa-spa"></i>
        </div>
        <h2 class="text-3xl font-serif font-bold text-slate-900 tracking-tight">PembdaHUB</h2>
        <p class="text-xs text-purple-700 font-bold mt-1 tracking-wide">Perguruan Pembangunan Daerah Nias</p>
    </div>

    <!-- Login Card (Serene Soft UI) -->
    <div class="serene-card p-7 shadow-xl border border-white relative overflow-hidden bg-white/90 backdrop-blur-md">
        <h3 class="text-xl font-serif font-bold text-slate-900 mb-1">Selamat Datang 🌿</h3>
        <p class="text-xs text-slate-600 mb-6 font-medium">Masuk dengan akun Siswa, Guru, atau Orang Tua</p>

        @if($errors->any())
            <div class="mb-5 p-4 rounded-2xl bg-rose-100/70 border border-rose-200 text-rose-900 text-xs font-bold">
                <i class="fa-solid fa-circle-exclamation mr-1.5 text-rose-600"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('mobile.login.post') }}" method="POST" class="space-y-4.5">
            @csrf

            <!-- Login Input (Email / Username / NIS) -->
            <div>
                <label for="login" class="block text-xs font-bold text-slate-700 mb-1.5">Email / Username / NIS</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 text-sm">
                        <i class="fa-regular fa-user"></i>
                    </div>
                    <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                           placeholder="Masukkan Email/NIS..." 
                           class="w-full pl-11 pr-4 py-3 bg-[#faf8f5] border border-stone-200/90 rounded-2xl text-slate-900 text-sm placeholder-slate-400 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition font-medium">
                </div>
            </div>

            <!-- Password Input -->
            <div x-data="{ showPass: false }">
                <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 text-sm">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <input :type="showPass ? 'text' : 'password'" id="password" name="password" required
                           placeholder="••••••••" 
                           class="w-full pl-11 pr-11 py-3 bg-[#faf8f5] border border-stone-200/90 rounded-2xl text-slate-900 text-sm placeholder-slate-400 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-100 transition font-medium">
                    <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600">
                        <i :class="showPass ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Remember me & Forgot password -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-stone-300 text-purple-600 focus:ring-purple-500">
                    <span class="text-xs text-slate-600 font-medium">Ingat Saya</span>
                </label>
                <a href="{{ url('/forgot-password') }}" class="text-xs font-bold text-purple-700 hover:text-purple-900">Lupa Password?</a>
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    class="w-full py-3.5 px-4 bg-gradient-to-r from-purple-700 via-indigo-600 to-purple-600 text-white font-extrabold text-sm rounded-2xl shadow-lg shadow-purple-500/25 hover:opacity-95 transition transform active:scale-98">
                Masuk ke Aplikasi
            </button>
        </form>
    </div>

    <div class="text-center mt-6">
        <p class="text-[11px] text-slate-500 font-medium">© {{ date('Y') }} YP PEMBDA Nias. All rights reserved.</p>
    </div>
</div>
@endsection
