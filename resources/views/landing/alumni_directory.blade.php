<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direktori Ikatan Alumni (IKA) PEMBDA</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        indigo: {
                            500: '#6366f1',
                            600: '#4f46e5',
                            900: '#312e81',
                        },
                        gold: '#f59e0b',
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #f4f3ff;
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,0.03) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,0.03) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,0.03) 0, transparent 50%);
            background-attachment: fixed;
            min-height: 100vh;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 10px 40px -10px rgba(49, 46, 129, 0.08);
            border-radius: 24px;
        }
        .form-input {
            width: 100%;
            padding: 0.65rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            transition: all 0.2s;
        }
        .form-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }
    </style>
</head>
<body class="text-slate-800 antialiased font-sans overflow-x-hidden">

    <!-- Top Navigation Header -->
    <header class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-indigo-100 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo PEMBDA" class="h-12 w-auto">
                    <div>
                        <h1 class="text-lg font-black text-indigo-900 tracking-tight leading-none">IKA PEMBDA NIAS</h1>
                        <p class="text-[10px] text-amber-600 font-extrabold uppercase tracking-widest mt-0.5">Ikatan Alumni Perguruan Pembda</p>
                    </div>
                </a>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('ika.register') }}" class="inline-flex items-center gap-2 bg-amber-400 hover:bg-amber-500 text-slate-900 font-black px-4 py-2 rounded-xl text-xs transition border border-black shadow-sm">
                    <i class="fas fa-user-plus"></i> Form Pendaftaran Alumni
                </a>
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-indigo-900 hover:bg-indigo-950 text-white font-black px-4 py-2 rounded-xl text-xs transition border border-black shadow-sm">
                    <i class="fas fa-sign-in-alt text-amber-300"></i> Login Space
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        <!-- Hero Title Banner -->
        <div class="bg-gradient-to-br from-indigo-950 via-slate-900 to-indigo-900 text-white rounded-3xl p-6 sm:p-10 shadow-xl border border-indigo-800 relative overflow-hidden">
            <div class="relative z-10 space-y-4 max-w-4xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-block px-3 py-1 bg-amber-400 text-slate-900 text-xs font-black rounded-full uppercase border border-black">
                        <i class="fas fa-users-rectangle mr-1"></i> DIREKTORI RESMI IKATAN ALUMNI
                    </span>
                    <span class="inline-block px-3 py-1 bg-white/10 text-amber-300 text-xs font-black rounded-full border border-amber-400/30">
                        <i class="fas fa-landmark mr-1"></i> SEMUA UNIT & SEKOLAH MERGER
                    </span>
                </div>
                
                <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight leading-tight">
                    Direktori Alumni Perguruan Pembda Nias
                </h1>
                
                <p class="text-indigo-200 text-xs sm:text-base font-normal leading-relaxed">
                    Wadah silaturahmi & jejaring alumni dari seluruh unit pendidikan Pembda sejak 1970 — termasuk unit sekolah merger <strong>SMAS Pembda 2 Gunungsitoli</strong> & <strong>SMPS Pembda 1 Gunungsitoli</strong>.
                </p>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 pt-6 border-t border-indigo-800/80">
                <div class="bg-white/10 backdrop-blur-md p-3.5 rounded-2xl border border-white/10 text-center">
                    <span class="block text-2xl font-black text-amber-400">{{ $totalAlumni }}</span>
                    <span class="text-[11px] text-indigo-200 font-bold uppercase tracking-wider">Total Alumni Terdata</span>
                </div>
                <div class="bg-white/10 backdrop-blur-md p-3.5 rounded-2xl border border-white/10 text-center">
                    <span class="block text-2xl font-black text-emerald-400">5 Unit</span>
                    <span class="text-[11px] text-indigo-200 font-bold uppercase tracking-wider">Ikatan Alumni Unit</span>
                </div>
                <div class="bg-white/10 backdrop-blur-md p-3.5 rounded-2xl border border-white/10 text-center">
                    <span class="block text-2xl font-black text-sky-400">50+ Tahun</span>
                    <span class="text-[11px] text-indigo-200 font-bold uppercase tracking-wider">Jejaring Pengabdian</span>
                </div>
                <div class="bg-white/10 backdrop-blur-md p-3.5 rounded-2xl border border-white/10 text-center">
                    <span class="block text-2xl font-black text-purple-400">Lintas Profesi</span>
                    <span class="text-[11px] text-indigo-200 font-bold uppercase tracking-wider">Di Seluruh Indonesia</span>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="glass-card p-6 shadow-sm">
            <form action="{{ route('ika.directory') }}" method="GET" class="space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-4">
                    <h2 class="text-base font-extrabold text-indigo-900 flex items-center gap-2">
                        <i class="fas fa-filter text-amber-500"></i> Filter Ikatan Alumni & Pencarian
                    </h2>
                    @if(request()->anyFilled(['school_id', 'graduation_year', 'search']))
                        <a href="{{ route('ika.directory') }}" class="text-xs text-rose-600 font-bold hover:underline">
                            <i class="fas fa-undo mr-1"></i> Reset Filter
                        </a>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Filter Unit / Ikatan Alumni -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Ikatan Alumni Unit Sekolah</label>
                        <select name="school_id" class="form-input" onchange="this.form.submit()">
                            <option value="">-- Semua Unit / Ikatan Alumni --</option>
                            @foreach($schools as $sch)
                                <option value="{{ $sch->id }}" {{ request('school_id') == $sch->id ? 'selected' : '' }}>
                                    IKA {{ $sch->name }} {{ !$sch->is_active ? '(Unit Merger / Historis)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Tahun Lulus -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tahun Kelulusan</label>
                        <select name="graduation_year" class="form-input" onchange="this.form.submit()">
                            <option value="">-- Semua Angkatan --</option>
                            @foreach($years as $yr)
                                <option value="{{ $yr }}" {{ request('graduation_year') == $yr ? 'selected' : '' }}>Angkatan {{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search Input -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Cari Nama / Profesi / Kota</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik kata kunci..." class="form-input pr-10">
                            <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-indigo-600 hover:text-indigo-900 p-1.5">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Unit Badge Pills Bar -->
                <div class="flex flex-wrap items-center gap-2 pt-2 text-xs">
                    <span class="font-bold text-slate-500 mr-1">Pilih Cepat IKA:</span>
                    <a href="{{ route('ika.directory') }}" class="px-3 py-1 rounded-full border text-xs font-bold transition {{ !request('school_id') ? 'bg-indigo-900 text-white border-indigo-900' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-100' }}">
                        Semua Alumni
                    </a>
                    @foreach($schools as $sch)
                        @php
                            $isMerged = !$sch->is_active;
                        @endphp
                        <a href="{{ route('ika.directory', ['school_id' => $sch->id]) }}" class="px-3 py-1 rounded-full border text-xs font-bold transition {{ request('school_id') == $sch->id ? 'bg-indigo-900 text-white border-indigo-900' : ($isMerged ? 'bg-amber-50 text-amber-800 border-amber-300 hover:bg-amber-100' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-100') }}">
                            IKA {{ $sch->name }} @if($isMerged) <span class="text-[10px] bg-amber-200 text-amber-900 px-1 rounded ml-1">Merger</span> @endif
                        </a>
                    @endforeach
                </div>
            </form>
        </div>

        <!-- Result Status Banner -->
        @if($selectedSchool)
            <div class="bg-indigo-50 border border-indigo-200 p-4 rounded-2xl flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-900 text-amber-400 flex items-center justify-center font-black">
                        <i class="fas fa-school"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-indigo-900 text-sm">Menampilkan Direktori Ikatan Alumni: {{ $selectedSchool->name }}</h3>
                        <p class="text-xs text-indigo-700">
                            @if(!$selectedSchool->is_active)
                                <span class="font-extrabold text-amber-700"><i class="fas fa-info-circle mr-1"></i> Unit Sekolah Merger / Historis</span> — Alumni unit ini memiliki wadah Ikatan Alumni sendiri yang terhubung dalam keluarga besar Pembda.
                            @else
                                <span class="font-extrabold text-emerald-700"><i class="fas fa-check-circle mr-1"></i> Unit Sekolah Aktif</span>
                            @endif
                        </p>
                    </div>
                </div>
                <span class="bg-indigo-900 text-amber-300 px-3 py-1 rounded-full text-xs font-black">
                    {{ $alumnis->total() }} Alumni Terdaftar
                </span>
            </div>
        @endif

        <!-- Alumni Grid Showcase -->
        @if($alumnis->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($alumnis as $alumnus)
                    <div class="bg-white rounded-2xl border-2 border-slate-900 shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col justify-between group">
                        
                        <!-- Top Header Photo & Badge -->
                        <div class="p-5 space-y-4">
                            <div class="flex items-start gap-4">
                                <div class="relative w-16 h-16 rounded-2xl overflow-hidden border-2 border-slate-900 shrink-0 bg-slate-100 shadow-sm">
                                    <img src="{{ $alumnus->photo_url }}" alt="{{ $alumnus->full_name }}" class="w-full h-full object-cover object-top group-hover:scale-105 transition">
                                </div>
                                <div class="space-y-1 min-w-0">
                                    <h4 class="font-black text-slate-900 text-sm leading-tight truncate" title="{{ $alumnus->full_name }}">
                                        {{ $alumnus->full_name }}
                                    </h4>
                                    @if($alumnus->alias_name)
                                        <p class="text-xs text-indigo-600 font-bold italic truncate">"{{ $alumnus->alias_name }}"</p>
                                    @endif
                                    <span class="inline-block px-2 py-0.5 bg-amber-400 text-slate-900 text-[10px] font-black rounded border border-black uppercase">
                                        Lulus {{ $alumnus->graduation_year }}
                                    </span>
                                </div>
                            </div>

                            <!-- Unit School Tag -->
                            <div class="pt-2 border-t border-slate-100">
                                <span class="text-[11px] font-extrabold text-slate-700 block truncate" title="{{ $alumnus->school?->name }}">
                                    <i class="fas fa-graduation-cap text-indigo-500 mr-1"></i>
                                    {{ $alumnus->school?->name ?? 'Pembda Nias' }}
                                </span>
                                @if($alumnus->jurusan)
                                    <span class="text-[10px] text-slate-500 font-medium block">
                                        Jurusan: {{ $alumnus->jurusan }}
                                    </span>
                                @endif
                            </div>

                            <!-- Career / Occupation -->
                            <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs space-y-1">
                                <div class="font-bold text-slate-900 truncate">
                                    <i class="fas fa-briefcase text-slate-400 mr-1"></i>
                                    {{ $alumnus->occupation ?: 'Alumni / Profesional' }}
                                </div>
                                @if($alumnus->company_name)
                                    <div class="text-[11px] text-slate-600 truncate">
                                        <i class="fas fa-building text-slate-400 mr-1"></i>
                                        {{ $alumnus->company_name }}
                                    </div>
                                @endif
                                @if($alumnus->address)
                                    <div class="text-[10px] text-slate-500 truncate">
                                        <i class="fas fa-map-marker-alt text-slate-400 mr-1"></i>
                                        {{ $alumnus->address }}
                                    </div>
                                @endif
                            </div>

                            <!-- Message Quote -->
                            @if($alumnus->message)
                                <div class="bg-indigo-50/70 p-3 rounded-xl border border-indigo-100 text-[11px] text-indigo-900 italic line-clamp-3 leading-relaxed">
                                    <i class="fas fa-quote-left text-indigo-400 mr-1 text-xs"></i>
                                    "{{ $alumnus->message }}"
                                </div>
                            @endif
                        </div>

                        <!-- Card Footer -->
                        <div class="bg-slate-900 text-white px-5 py-2.5 text-[11px] font-bold flex items-center justify-between border-t-2 border-slate-900">
                            <span class="text-amber-300">IKA {{ Str::limit($alumnus->school?->name, 22) }}</span>
                            <span class="text-slate-300"><i class="fas fa-id-badge mr-1"></i> Terverifikasi</span>
                        </div>

                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pt-6">
                {{ $alumnis->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="glass-card p-12 text-center space-y-4 max-w-lg mx-auto">
                <div class="w-16 h-16 rounded-full bg-indigo-50 text-indigo-500 mx-auto flex items-center justify-center text-2xl border-2 border-indigo-200">
                    <i class="fas fa-user-slash"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900">Belum Ada Data Alumni</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Belum ada alumni terdaftar untuk kriteria pencarian ini. Apakah Anda alumni almamater ini? Mari mendaftar untuk mengaktifkan Direktori Ikatan Alumni Anda!
                </p>
                <div class="pt-2">
                    <a href="{{ route('ika.register') }}" class="inline-flex items-center gap-2 bg-amber-400 hover:bg-amber-500 text-slate-900 font-black px-5 py-2.5 rounded-xl text-xs transition border border-black shadow-sm">
                        <i class="fas fa-user-plus"></i> Daftarkan Diri Sekarang
                    </a>
                </div>
            </div>
        @endif

    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-white mt-16 py-8 border-t-2 border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-2">
            <p class="text-xs font-extrabold text-amber-400">YAYASAN PERGURUAN PEMBDA NIAS — SEJAK 1970</p>
            <p class="text-[11px] text-slate-400">"Dari Pembda untuk Nias, Dari Alumni untuk Masa Depan"</p>
        </div>
    </footer>

</body>
</html>
