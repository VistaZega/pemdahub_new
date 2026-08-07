<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PembdaHUB</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Modern Typography: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- PWA Manifest & App Shell Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PembdaHUB">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">

    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
          navigator.serviceWorker.register('/sw.js');
        });
      }
    </script>

    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
        .bg-animated {
            background: linear-gradient(-45deg, #4f46e5, #7c3aed, #2563eb, #3b82f6);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        /* Custom styles to ensure colors work even if Tailwind JIT missed them */
        .left-panel-custom {
            background: linear-gradient(135deg, #312e81 0%, #4c1d95 100%); /* indigo-900 to violet-900 */
            color: #ffffff;
        }
        .text-custom-light {
            color: #e0e7ff; /* indigo-100 */
        }
        .text-custom-white {
            color: #ffffff;
        }
        .btn-custom-gradient {
            background: linear-gradient(to right, #4f46e5, #7c3aed); /* indigo-600 to violet-600 */
        }
        .btn-custom-gradient:hover {
            background: linear-gradient(to right, #4338ca, #6d28d9); /* indigo-700 to violet-700 */
        }

        /* Password masking: karakter * dengan ukuran besar */
        #password-display {
            font-size: 1.4rem;
            letter-spacing: 0.25em;
        }
        #password-display::placeholder {
            font-size: 1.4rem;
            letter-spacing: 0.25em;
            color: #b0b8c8;
        }
    </style>
</head>

<body class="bg-animated min-h-screen flex items-center justify-center p-6 text-gray-800">
    
    <div class="w-full max-w-5xl flex flex-col md:flex-row glass-card rounded-3xl overflow-hidden transform transition-all hover:scale-[1.01] duration-500">
        
        @php
            $quotes = [
                [
                    'text' => 'Pendidikan adalah senjata paling ampuh yang bisa Anda gunakan untuk mengubah dunia.',
                    'author' => 'Nelson Mandela'
                ],
                [
                    'text' => 'Ing ngarso sung tulodo, ing madyo mangun karso, tut wuri handayani.',
                    'author' => 'Ki Hajar Dewantara'
                ],
                [
                    'text' => 'Tujuan pendidikan itu untuk mempertajam kecerdasan, memperkukuh kemauan serta memperhalus perasaan.',
                    'author' => 'Tan Malaka'
                ],
                [
                    'text' => 'Pendidikan bukanlah proses mengisi wadah yang kosong, melainkan proses menyalakan api pikiran.',
                    'author' => 'W.B. Yeats'
                ],
                [
                    'text' => 'Barangsiapa tidak mau merasakan pahitnya belajar, ia akan merasakan hinanya kebodohan sepanjang hidupnya.',
                    'author' => 'Imam Syafi\'i'
                ],
                [
                    'text' => 'Anak-anak hidup dan tumbuh sesuai kodratnya sendiri. Pendidik hanya dapat merawat dan menuntun tumbuhnya kodrat itu.',
                    'author' => 'Ki Hajar Dewantara'
                ],
                [
                    'text' => 'Belajar tanpa berpikir tidak ada gunanya, sedangkan berpikir tanpa belajar adalah berbahaya.',
                    'author' => 'Soekarno'
                ],
                [
                    'text' => 'Hanya pendidikan yang bisa menyelamatkan masa depan, tanpa pendidikan Indonesia tak mungkin bertahan.',
                    'author' => 'Najwa Shihab'
                ],
                [
                    'text' => 'Pendidikan adalah tiket ke masa depan. Hari esok dimiliki oleh orang-orang yang mempersiapkan dirinya sejak hari ini.',
                    'author' => 'Malcolm X'
                ],
                [
                    'text' => 'Jangan pernah berhenti belajar, karena hidup tak pernah berhenti mengajarkan.',
                    'author' => 'Kahlil Gibran'
                ],
                [
                    'text' => 'Orang bijak belajar ketika mereka bisa. Orang bodoh belajar ketika mereka terpaksa.',
                    'author' => 'Arthur Wellesley'
                ],
                [
                    'text' => 'Sukses bukanlah kunci kebahagiaan. Kebahagiaanlah kunci kesuksesan. Jika Anda mencintai apa yang Anda kerjakan, Anda akan sukses.',
                    'author' => 'Albert Schweitzer'
                ],
                [
                    'text' => 'Guru yang biasa-biasa saja memberi tahu. Guru yang baik menjelaskan. Guru yang hebat menginspirasi.',
                    'author' => 'William Arthur Ward'
                ],
                [
                    'text' => 'Bukan karena hal itu sulit kita tidak berani, tetapi karena kita tidak berani maka hal itu menjadi sulit.',
                    'author' => 'Seneca'
                ],
                [
                    'text' => 'Masa depan adalah milik mereka yang percaya pada keindahan mimpi-mimpi mereka.',
                    'author' => 'Eleanor Roosevelt'
                ],
                [
                    'text' => 'Investasi dalam pengetahuan selalu membayar bunga yang terbaik.',
                    'author' => 'Benjamin Franklin'
                ],
                [
                    'text' => 'Ilmu itu seperti air. Jika ia tidak bergerak, ia akan menjadi keruh dan tidak sehat.',
                    'author' => 'Imam Syafi\'i'
                ],
                [
                    'text' => 'Sebuah perjalanan ribuan mil dimulai dari satu langkah pertama yang berani.',
                    'author' => 'Lao Tzu'
                ],
                [
                    'text' => 'Satu-satunya kebijaksanaan sejati adalah mengetahui bahwa Anda tidak mengetahui apa-apa.',
                    'author' => 'Socrates'
                ],
                [
                    'text' => 'Pendidikan adalah kemampuan untuk mendengarkan hampir segala hal tanpa kehilangan ketenangan atau rasa percaya dirimu.',
                    'author' => 'Robert Frost'
                ],
                [
                    'text' => 'Bermimpilah setinggi langit. Jika engkau jatuh, engkau akan jatuh di antara bintang-bintang.',
                    'author' => 'Soekarno'
                ],
                [
                    'text' => 'Satu-satunya sumber pengetahuan sejati adalah pengalaman.',
                    'author' => 'Albert Einstein'
                ],
            ];
            $randomQuote = $quotes[array_rand($quotes)];
        @endphp

        <!-- Left Side: Branding & Welcome -->
        <div class="w-full md:w-5/12 left-panel-custom p-12 flex flex-col justify-between relative overflow-hidden hidden md:flex">
            <!-- Decorative shapes -->
            <div class="absolute top-0 right-0 -mt-20 -mr-20 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-80 h-80 bg-blue-400 opacity-20 rounded-full blur-3xl"></div>
            
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-12">
                    <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center shadow-lg p-1.5">
                        <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo Yayasan" class="w-full h-full object-contain">
                    </div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-white">Pembda<span class="text-red-500">HUB</span></h1>
                </div>
                
                <div class="mt-4">
                    <i class="fas fa-quote-left text-indigo-400/50 text-4xl mb-4"></i>
                    <h2 class="text-2xl font-bold mb-4 leading-tight text-white italic">"{{ $randomQuote['text'] }}"</h2>
                    <p class="text-custom-light text-lg font-semibold flex items-center gap-2">
                        <span class="w-6 h-px bg-indigo-300"></span> {{ $randomQuote['author'] }}
                    </p>
                </div>
            </div>
            
            <div class="relative z-10 text-custom-light text-sm font-medium">
                &copy; {{ date('Y') }} Yayasan Perguruan Pembda Nias
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="w-full md:w-7/12 p-8 md:p-14 flex flex-col justify-center bg-white relative">
            
            <!-- Mobile Logo (hidden on desktop) -->
            <div class="flex md:hidden items-center justify-center gap-3 mb-10">
                <div class="w-16 h-16 bg-indigo-100 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-200 p-2">
                    <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo Yayasan" class="w-full h-full object-contain">
                </div>
                <h1 class="text-4xl font-extrabold tracking-tight text-gray-900">Pembda<span class="text-red-600">HUB</span></h1>
            </div>

            <div class="mb-10 text-center md:text-left">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Masuk ke Akun Anda</h2>
                <p class="text-lg text-gray-500 font-medium">Silakan masukkan kredensial Anda untuk melanjutkan</p>
            </div>

            @if ($errors->any())
            <div class="mb-8 p-5 bg-red-50 border-l-4 border-red-500 rounded-r-xl flex items-start gap-4">
                <i class="fas fa-exclamation-circle text-red-500 text-xl mt-0.5"></i>
                <div>
                    <h3 class="text-red-800 font-bold text-lg mb-1">Gagal Masuk</h3>
                    <ul class="text-red-600 text-base font-medium space-y-1">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            @if (session('status'))
            <div class="mb-8 p-5 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl flex items-start gap-4">
                <i class="fas fa-check-circle text-emerald-500 text-xl mt-0.5"></i>
                <p class="text-emerald-700 text-base font-medium">{{ session('status') }}</p>
            </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Username or Email -->
                <div>
                    <label for="login" class="block text-sm font-bold text-gray-700 mb-2">Username atau Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="text" id="login" name="login" value="{{ old('login') }}" required
                               placeholder="Masukkan username atau email"
                               class="block w-full pl-11 pr-4 py-3.5 text-base border-2 border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 transition-all font-semibold text-gray-800">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-bold text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="hidden" id="password" name="password">
                        <input type="text" id="password-display" required
                               placeholder="* * * * * *"
                               autocomplete="off" autocapitalize="off" spellcheck="false"
                               class="block w-full pl-11 pr-4 py-3.5 border-2 border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 transition-all font-semibold text-gray-800">
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between pt-2">
                    <label class="flex items-center cursor-pointer group">
                        <div class="relative flex items-center justify-center">
                            <input type="checkbox" id="remember" name="remember" class="peer sr-only">
                            <div class="w-5 h-5 border-2 border-gray-300 rounded peer-checked:bg-indigo-600 peer-checked:border-indigo-600 transition-all shadow-sm group-hover:border-indigo-500"></div>
                            <i class="fas fa-check absolute text-white text-[10px] opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                        </div>
                        <span class="ml-2.5 text-base font-semibold text-gray-600 group-hover:text-indigo-700 transition-colors">Ingat saya</span>
                    </label>

                    <a href="{{ route('password.request') }}" class="text-base font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                        Lupa password?
                    </a>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3.5 px-6 btn-custom-gradient text-white text-lg font-bold rounded-xl shadow-lg shadow-indigo-600/30 transform transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-indigo-600/40 flex justify-center items-center gap-2 mt-4">
                    <span>Masuk ke Sistem</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <!-- Alumni Link -->
            <div class="mt-6 pt-4 border-t border-gray-100 text-center">
                <p class="text-xs font-semibold text-gray-600 pt-1">
                    Alumni lama yang belum punya akun? 
                    <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-800 font-bold underline transition-colors">
                        <i class="fas fa-user-plus mr-1"></i> Daftar Ikatan Alumni (IKA) di sini →
                    </a>
                </p>
            </div>

            <!-- Mobile Footer -->
            <div class="mt-8 text-center text-gray-400 text-sm font-medium md:hidden">
                &copy; {{ date('Y') }} PembdaHUB.
            </div>

        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const display = document.getElementById('password-display');
    const hidden = document.getElementById('password');
    let realPassword = '';

    display.addEventListener('input', function(e) {
        const pos = this.selectionStart;
        const val = this.value;
        const lenDiff = val.length - realPassword.length;

        if (lenDiff > 0) {
            // Karakter ditambahkan
            const addedCount = lenDiff;
            const addedText = val.substring(pos - addedCount, pos);
            realPassword = realPassword.substring(0, pos - addedCount) + addedText + realPassword.substring(pos - addedCount);
        } else if (lenDiff < 0) {
            // Karakter dihapus
            const removedCount = -lenDiff;
            realPassword = realPassword.substring(0, pos) + realPassword.substring(pos + removedCount);
        } else if (e.data) {
            // Panjang sama = replace (select 1 char, ketik 1 char)
            realPassword = realPassword.substring(0, pos - 1) + e.data + realPassword.substring(pos);
        }

        hidden.value = realPassword;
        this.value = '*'.repeat(realPassword.length);
        this.setSelectionRange(pos, pos);
    });

    // Tangani paste
    display.addEventListener('paste', function(e) {
        e.preventDefault();
        const pastedText = (e.clipboardData || window.clipboardData).getData('text');
        const pos = this.selectionStart;
        const end = this.selectionEnd;

        realPassword = realPassword.substring(0, pos) + pastedText + realPassword.substring(end);
        hidden.value = realPassword;
        this.value = '*'.repeat(realPassword.length);

        const newPos = pos + pastedText.length;
        this.setSelectionRange(newPos, newPos);
    });

    // Blokir copy/cut agar password tidak bocor
    display.addEventListener('copy', function(e) { e.preventDefault(); });
    display.addEventListener('cut', function(e) { e.preventDefault(); });
});
</script>

</body>
</html>