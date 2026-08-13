@extends('mobile.layouts.app')

@section('title', 'Login - PembdaHUB Mobile')

@section('content')
<div class="min-h-[80vh] flex flex-col justify-center py-6">
    <!-- Hero Branding -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-600 text-white text-3xl font-black shadow-xl shadow-indigo-500/30 mb-4">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">PembdaHUB</h2>
        <p class="text-xs text-indigo-600 font-bold mt-1">Perguruan Pembangunan Daerah Nias</p>
    </div>

    <!-- Login Card (Clean Light Theme) -->
    <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-200/80 relative overflow-hidden">
        <h3 class="text-lg font-black text-slate-900 mb-1">Selamat Datang 👋</h3>
        <p class="text-xs text-slate-500 mb-6 font-medium">Masuk dengan akun Siswa, Guru, atau Orang Tua</p>

        @if($errors->any())
            <div class="mb-4 p-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
                <i class="fa-solid fa-circle-exclamation mr-1 text-rose-600"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('mobile.login.post') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Login Input (Email / Username / NIS) -->
            <div>
                <label for="login" class="block text-xs font-bold text-slate-700 mb-1.5">Email / Username / NIS</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                        <i class="fa-regular fa-user"></i>
                    </div>
                    <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                           placeholder="Masukkan Email/NIS..." 
                           class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm placeholder-slate-400 focus:outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 transition font-medium">
                </div>
            </div>

            <!-- Password Input -->
            <div x-data="{ showPass: false }">
                <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <input :type="showPass ? 'text' : 'password'" id="password" name="password" required
                           placeholder="••••••••" 
                           class="w-full pl-10 pr-10 py-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm placeholder-slate-400 focus:outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 transition font-medium">
                    <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                        <i :class="showPass ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Remember me & Forgot password -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-xs text-slate-600 font-medium">Ingat Saya</span>
                </label>
                <a href="{{ url('/forgot-password') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700">Lupa Password?</a>
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    class="w-full py-3.5 px-4 bg-gradient-to-r from-indigo-600 to-indigo-500 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-indigo-500/25 hover:from-indigo-500 hover:to-indigo-400 transition transform active:scale-95">
                Masuk ke Aplikasi
            </button>
        </form>
    </div>

    <div class="text-center mt-6">
        <p class="text-[11px] text-slate-400 font-medium">© {{ date('Y') }} YP PEMBDA Nias. All rights reserved.</p>
    </div>
</div>
@endsection
