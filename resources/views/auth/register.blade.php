<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - PembdaHUB</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="w-full max-w-md">
            <!-- Logo/Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold mb-2"><span class="text-gray-800">Pembda</span><span class="text-red-600">HUB</span></h1>
                <p class="text-gray-600">Sistem Manajemen Sekolah Terpadu</p>
            </div>

            <!-- Register Form Card -->
            <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                <!-- Warm Invitation Banner -->
                <div class="bg-gradient-to-r from-indigo-900 via-slate-900 to-indigo-950 text-white p-6 rounded-2xl mb-6 shadow-md border border-indigo-800">
                    <span class="inline-block px-3 py-1 bg-amber-400/20 text-amber-300 text-xs font-bold rounded-full mb-2 border border-amber-300/30">
                        <i class="fas fa-heart text-pink-400 mr-1"></i> AJAKAN BERGABUNG ALUMNI
                    </span>
                    <h2 class="text-xl font-bold text-white mb-2">Mari Bergabung di Portal Ikatan Alumni!</h2>
                    <p class="text-xs text-indigo-200 leading-relaxed mb-4">
                        Pembuatan akun ini bertujuan untuk menghubungkan kembali seluruh alumni Perguruan Pembda Nias di mana pun berada.
                    </p>
                    <div class="space-y-1.5 text-xs text-indigo-100">
                        <div class="flex items-center gap-2"><i class="fas fa-check-circle text-amber-400"></i> <span>Akses Forum Obrolan & Nostalgia Per Angkatan</span></div>
                        <div class="flex items-center gap-2"><i class="fas fa-check-circle text-amber-400"></i> <span>Informasi Lowongan Kerja & Karir Alumni</span></div>
                        <div class="flex items-center gap-2"><i class="fas fa-check-circle text-amber-400"></i> <span>Wadah Kontribusi & Rekening Resmi Yayasan</span></div>
                    </div>
                </div>

                <h2 class="text-2xl font-bold text-gray-800 mb-6">Buat Akun Anda</h2>

                @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-600 text-sm font-semibold mb-2">Terjadi kesalahan:</p>
                    <ul class="text-red-600 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if (session('status'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-green-600 text-sm">{{ session('status') }}</p>
                </div>
                @endif

                <form action="{{ route('register.submit') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition"
                            placeholder="Nama Anda">
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition"
                            placeholder="your@email.com">
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Tipe Pengguna</label>
                        <select
                            id="role"
                            name="role"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition">
                            <option value="">Pilih tipe pengguna</option>
                            <option value="siswa" {{ old('role') === 'siswa' ? 'selected' : '' }}>Siswa</option>
                            <option value="orang_tua" {{ old('role') === 'orang_tua' ? 'selected' : '' }}>Orang Tua</option>
                        </select>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition"
                            placeholder="••••••••">
                        <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter, dengan huruf besar, huruf kecil, angka, dan simbol</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition"
                            placeholder="••••••••">
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="flex items-start">
                        <input
                            type="checkbox"
                            id="terms"
                            name="terms"
                            required
                            class="w-4 h-4 mt-1 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="terms" class="ml-2 text-sm text-gray-600">
                            Saya setuju dengan <a href="#" class="text-indigo-600 hover:text-indigo-700">Syarat & Ketentuan</a>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition duration-200">
                        Daftar
                    </button>
                </form>

                <!-- Links -->
                <div class="mt-6 text-center text-sm text-gray-600">
                    <p>
                        Sudah punya akun?
                        <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-700 font-semibold">Masuk di sini</a>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8 text-gray-600 text-sm">
                <p>&copy; 2024 PembdaHUB. Semua hak dilindungi.</p>
            </div>
        </div>
    </div>
</body>

</html>