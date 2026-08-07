<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Ikatan Alumni (IKA) PEMBDA</title>
    
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
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 10px 40px -10px rgba(49, 46, 129, 0.1);
            border-radius: 24px;
        }
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            transition: all 0.2s;
        }
        .form-input:focus {
            outline: none;
            border-color: #6366f1;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #312e81 100%);
            color: white;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -10px rgba(49, 46, 129, 0.5);
        }
        
        /* Custom Scrollbar for Right Side */
        .custom-scroll::-webkit-scrollbar {
            width: 8px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 20px;
        }
    </style>
</head>
<body class="text-slate-800 antialiased font-sans overflow-x-hidden">

    <!-- Split Layout Container -->
    <div class="min-h-screen flex flex-col lg:flex-row w-full max-w-[1600px] mx-auto relative">
        
        <!-- Left Sidebar: Info & Photos (Sticky on Desktop) -->
        <div class="w-full lg:w-5/12 xl:w-1/2 p-6 md:p-10 lg:sticky lg:top-0 lg:h-screen lg:overflow-y-auto custom-scroll flex flex-col justify-center">
            
            <div class="mb-8">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-indigo-600 font-bold hover:text-indigo-800 transition mb-6 bg-white/50 px-4 py-2 rounded-full shadow-sm border border-indigo-100">
                    <i class="fa-solid fa-arrow-left"></i> Beranda
                </a>
                
                <div class="flex items-center gap-4 mb-6">
                    <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo PEMBDA" class="h-16 w-auto object-contain">
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-extrabold text-indigo-900 tracking-tight leading-tight">
                            Portal & Rembuk Alumni
                        </h1>
                        <p class="text-sm text-gold font-bold uppercase tracking-widest mt-1">Yayasan Perguruan PEMBDA Nias</p>
                    </div>
                </div>

                <!-- Invitation & Detailed Information Banner -->
                <div class="bg-gradient-to-br from-indigo-900 via-slate-900 to-indigo-950 text-white rounded-3xl p-6 shadow-xl mb-6 border border-indigo-800/80">
                    <span class="inline-block px-3 py-1 bg-amber-400/20 text-amber-300 text-xs font-bold rounded-full mb-3 border border-amber-300/30">
                        <i class="fas fa-heart text-pink-400 mr-1"></i> RESMI IKATAN ALUMNI (IKA) PEMBDA
                    </span>
                    <h2 class="text-xl font-bold text-white mb-2 leading-snug">Mari Terhubung Kembali dengan Almamater Tercinta!</h2>
                    <p class="text-xs text-indigo-200 leading-relaxed mb-4">
                        Di manapun Anda berdomisili dan berkarya saat ini, Anda adalah bagian tak terpisahkan dari keluarga besar Perguruan Pembda Nias. Portal ini dihadirkan sebagai **rumah digital resmi** untuk menghimpun seluruh alumni dari semua angkatan di seluruh dunia.
                    </p>
                    <div class="bg-indigo-950/80 p-3.5 rounded-2xl border border-indigo-700/50 text-[11px] text-indigo-100 space-y-1.5">
                        <div class="flex items-center gap-2 text-amber-300 font-bold mb-1">
                            <i class="fas fa-shield-alt"></i> Jaminan Kerahasiaan Data:
                        </div>
                        <p class="text-[11px] text-indigo-200/90 leading-relaxed">
                            Data yang Anda daftarkan dijamin aman dan hanya dipergunakan untuk keperluan silaturahmi IKA, pengembangan karir alumni, dan kemajuan almamater Perguruan Pembda Nias.
                        </p>
                    </div>
                </div>
                
                <!-- Rembuk Alumni Encouraging Paragraph Card -->
                <div class="bg-white/95 backdrop-blur-md rounded-2xl p-5 border border-indigo-100 shadow-sm mb-6 space-y-2.5">
                    <h3 class="text-xs font-extrabold text-indigo-950 uppercase tracking-wider flex items-center gap-2">
                        <i class="fas fa-users text-indigo-600"></i> REMBUK ALUMNI PERGURUAN PEMBDA
                    </h3>
                    <p class="text-xs text-slate-700 leading-relaxed font-normal">
                        Melalui portal resmi ini, mari jalin kembali silaturahmi kawan seangkatan di <strong>Ruang Forum & Cuap-cuap Alumni</strong>, saling membuka peluang karir di <strong>Papan Lowongan Kerja & Karir</strong>, menyalurkan kepedulian bagi almamater melalui <strong>Wadah Kontribusi & Rekening Resmi Yayasan</strong>, serta mendukung kemajuan mutu sekolah melalui <strong>Pendataan Tracer Study</strong>. Kehadiran dan peran serta Anda sangat berharga bagi masa depan Perguruan Pembda Nias.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 mb-6">
                    <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white text-indigo-700 px-6 py-3 rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition-all border border-indigo-100 hover:bg-indigo-50">
                        <i class="fa-solid fa-right-to-bracket"></i> Sudah Punya Akun? Login
                    </a>
                </div>
            </div>

            <!-- Smart Report Widget -->
            <div class="bg-gradient-to-r from-indigo-50 to-white border border-indigo-100 rounded-2xl p-5 shadow-sm mb-8 relative overflow-hidden">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl"></div>
                <h3 class="text-xs font-bold text-indigo-800 uppercase tracking-wider mb-4"><i class="fas fa-chart-pie mr-1 text-indigo-500"></i> Statistik Alumni Terdata</h3>
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-white p-3 rounded-xl shadow-sm border border-slate-100 text-center">
                        <span class="block text-[10px] font-bold text-slate-500 mb-1">Total Terdata</span>
                        <strong class="text-xl md:text-2xl font-extrabold text-indigo-600">{{ $totalRegistered ?? 0 }}</strong>
                    </div>
                    <div class="bg-white p-3 rounded-xl shadow-sm border border-slate-100 text-center">
                        <span class="block text-[10px] font-bold text-slate-500 mb-1">Angkatan Tertua</span>
                        <strong class="text-xl md:text-2xl font-extrabold text-gold">{{ $oldestAlumni ?? '-' }}</strong>
                    </div>
                    <div class="bg-white p-3 rounded-xl shadow-sm border border-slate-100 text-center">
                        <span class="block text-[10px] font-bold text-slate-500 mb-1">Angkatan Termuda</span>
                        <strong class="text-xl md:text-2xl font-extrabold text-emerald-500">{{ $youngestAlumni ?? '-' }}</strong>
                    </div>
                </div>
            </div>

            <!-- Alumni Gallery (Limited Preview) -->
            @if(isset($approvedAlumni) && $approvedAlumni->count() > 0)
            <div>
                <h3 class="text-sm font-bold text-indigo-900 mb-4 flex items-center gap-2">
                    Keluarga yang Telah Bergabung
                    <span class="text-[10px] bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-full">Terbaru</span>
                </h3>
                
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                    @foreach($approvedAlumni as $alumni)
                    <div class="group relative rounded-xl overflow-hidden aspect-[3/4] shadow-sm border border-white/50 bg-white">
                        <img src="{{ $alumni->photo_url }}" class="absolute inset-0 w-full h-full object-cover object-top transition duration-500 group-hover:scale-110" alt="{{ $alumni->full_name }}">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent opacity-80"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-2 text-white text-center">
                            <h4 class="font-bold text-[10px] leading-tight line-clamp-2" title="{{ $alumni->full_name }}">{{ $alumni->alias_name ? $alumni->alias_name : $alumni->full_name }}</h4>
                            <p class="text-[9px] text-gold mt-0.5">'{{ $alumni->graduation_year }}</p>
                        </div>
                        @if($alumni->message)
                        <div class="absolute inset-0 bg-indigo-900/95 p-3 text-white opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-center items-center text-center backdrop-blur-sm cursor-pointer">
                            <i class="fas fa-quote-left text-indigo-400 text-lg mb-1"></i>
                            <p class="text-[9px] italic line-clamp-4 leading-relaxed">{{ $alumni->message }}</p>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @if($totalRegistered > 12)
                <div class="mt-4 text-center">
                    <p class="text-xs font-semibold text-slate-500">... dan {{ $totalRegistered - 12 }} alumni lainnya.</p>
                </div>
                @endif
            </div>
            @endif

        </div>
        
        <!-- Right Content: The Form -->
        <div class="w-full lg:w-7/12 xl:w-1/2 p-4 md:p-8 lg:p-10 flex items-center justify-center">
            
            <div class="w-full glass-card p-6 md:p-10 relative overflow-hidden">
                <!-- Decorative Elements -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-100 rounded-bl-full opacity-50 pointer-events-none"></div>
                
                @if(session('success'))
                    <div class="mb-8 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex gap-3 items-start relative z-10 shadow-sm">
                        <i class="fa-solid fa-circle-check text-xl mt-0.5 text-emerald-500"></i>
                        <div>
                            <h4 class="font-bold text-emerald-900">Pendaftaran Berhasil!</h4>
                            <p class="text-sm mt-1">{!! session('success') !!}</p>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-8 p-4 bg-red-50 border border-red-200 text-red-800 rounded-2xl flex gap-3 items-start relative z-10 shadow-sm">
                        <i class="fa-solid fa-triangle-exclamation text-xl mt-0.5 text-red-500"></i>
                        <div>
                            <h4 class="font-bold text-red-900">Mohon periksa kembali isian Anda:</h4>
                            <ul class="text-sm mt-1 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Step-by-Step Guide Header -->
                <div class="mb-6 bg-gradient-to-r from-indigo-900 via-slate-900 to-indigo-950 text-white p-6 rounded-2xl shadow-sm relative z-10 border border-indigo-800">
                    <h2 class="text-xl font-extrabold text-white flex items-center gap-2 mb-1">
                        <i class="fas fa-user-plus text-amber-400"></i> Formulir Pendaftaran Akun Alumni
                    </h2>
                    <p class="text-xs text-indigo-200 leading-relaxed mb-4">
                        Isi formulir di bawah ini. Akun Anda akan **langsung aktif secara otomatis** setelah pendaftaran selesai!
                    </p>
                    
                    <div class="grid grid-cols-3 gap-2 pt-2 border-t border-indigo-800/80 text-[11px]">
                        <div class="flex items-center gap-1.5 text-indigo-200 font-medium">
                            <span class="w-5 h-5 rounded-full bg-amber-400 text-slate-900 font-extrabold flex items-center justify-center text-[10px]">1</span>
                            <span>Isi Form Data</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-indigo-200 font-medium">
                            <span class="w-5 h-5 rounded-full bg-amber-400 text-slate-900 font-extrabold flex items-center justify-center text-[10px]">2</span>
                            <span>Terima Akun</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-indigo-200 font-medium">
                            <span class="w-5 h-5 rounded-full bg-amber-400 text-slate-900 font-extrabold flex items-center justify-center text-[10px]">3</span>
                            <span>Langsung Login</span>
                        </div>
                    </div>
                </div>

                <form action="{{ route('ika.register.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-8 relative z-10">
                    @csrf

                    <!-- Section: Data Pribadi -->
                    <div>
                        <h3 class="text-sm font-bold text-indigo-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <div class="w-6 h-6 rounded bg-indigo-100 text-indigo-600 flex items-center justify-center">1</div> Identitas Diri
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap (beserta Gelar) <span class="text-red-500">*</span></label>
                                <input type="text" name="full_name" value="{{ old('full_name') }}" required class="form-input" placeholder="Cth: Dr. Budi Santoso, M.Kom">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Panggilan / Alias</label>
                                <input type="text" name="alias_name" value="{{ old('alias_name') }}" class="form-input" placeholder="Cth: Ama Budi / Ina Wati">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
                                <select name="gender" required class="form-input">
                                    <option value="">-- Pilih --</option>
                                    <option value="L" {{ old('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor WhatsApp / HP</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" class="form-input" placeholder="Cth: 08123456789">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Pernikahan</label>
                                <select name="marital_status" class="form-input">
                                    <option value="">-- Pilih --</option>
                                    <option value="Belum Menikah" {{ old('marital_status') == 'Belum Menikah' ? 'selected' : '' }}>Belum Menikah</option>
                                    <option value="Menikah" {{ old('marital_status') == 'Menikah' ? 'selected' : '' }}>Menikah</option>
                                    <option value="Pernah Menikah" {{ old('marital_status') == 'Pernah Menikah' ? 'selected' : '' }}>Pernah Menikah</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Pekerjaan -->
                    <div class="bg-slate-50 border border-slate-100 p-5 rounded-2xl">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4"><i class="fas fa-briefcase mr-1 text-slate-400"></i> Info Karir Saat Ini</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Profesi / Jabatan</label>
                                <input type="text" name="occupation" value="{{ old('occupation') }}" class="form-input bg-white" placeholder="Cth: PNS / Wirausaha / Manajer">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Instansi / Perusahaan</label>
                                <input type="text" name="company_name" value="{{ old('company_name') }}" class="form-input bg-white" placeholder="Cth: Pemda Nias / PT ABC">
                            </div>
                        </div>
                    </div>

                    <!-- Section: Kontak & Alamat -->
                    <div>
                        <h3 class="text-sm font-bold text-indigo-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <div class="w-6 h-6 rounded bg-indigo-100 text-indigo-600 flex items-center justify-center">2</div> Kontak & Lokasi
                        </h3>
                        <div class="grid grid-cols-1 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Email Aktif <span class="text-slate-400 font-normal">(Digunakan untuk Login)</span></label>
                                <input type="email" name="email" value="{{ old('email') }}" class="form-input" placeholder="budi@email.com">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Domisili Sekarang <span class="text-red-500">*</span></label>
                                <textarea name="address" rows="2" required class="form-input" placeholder="Tuliskan alamat lengkap..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Data Akademik -->
                    <div>
                        <h3 class="text-sm font-bold text-indigo-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <div class="w-6 h-6 rounded bg-indigo-100 text-indigo-600 flex items-center justify-center">3</div> Rekam Akademik
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Lulusan Dari <span class="text-red-500">*</span></label>
                                <select id="school-select" name="school_id" required class="form-input">
                                    <option value="">-- Pilih Sekolah --</option>
                                    @foreach($schools as $school)
                                        <option value="{{ $school->id }}" data-type="{{ $school->type }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Tahun Lulus <span class="text-red-500">*</span></label>
                                <select name="graduation_year" required class="form-input">
                                    <option value="">-- Pilih Tahun --</option>
                                    @foreach($years as $year)
                                        <option value="{{ $year }}" {{ old('graduation_year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div id="jurusan-container" style="display: none;" class="col-span-1 md:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Jurusan (Khusus SMK) <span class="text-red-500">*</span></label>
                                <input type="text" id="jurusan-input" name="jurusan" value="{{ old('jurusan') }}" class="form-input" placeholder="Cth: Akuntansi / TKJ / Perkantoran">
                            </div>
                        </div>
                    </div>

                    <!-- Section: Pesan & Foto -->
                    <div>
                        <h3 class="text-sm font-bold text-indigo-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <div class="w-6 h-6 rounded bg-indigo-100 text-indigo-600 flex items-center justify-center">4</div> Personal (Opsional)
                        </h3>
                        
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Pesan, Kesan, atau Ide untuk Almamater</label>
                                <textarea name="message" rows="3" class="form-input" placeholder="Tuliskan pesan indah semasa sekolah, atau motivasi untuk adik kelas..."></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Unggah Foto Profil Terbaru</label>
                                <div class="mt-1 flex flex-col sm:flex-row items-center gap-4">
                                    <!-- Image Preview Area -->
                                    <div id="image-preview-container" class="hidden shrink-0">
                                        <div class="relative w-20 h-20 rounded-full overflow-hidden border-2 border-indigo-200 shadow-sm">
                                            <img id="image-preview" src="#" alt="Preview" class="w-full h-full object-cover">
                                        </div>
                                    </div>
                                    
                                    <div class="flex-1 w-full border-2 border-slate-200 border-dashed rounded-xl bg-slate-50 hover:bg-indigo-50 transition p-4 text-center">
                                        <i class="fa-solid fa-cloud-arrow-up text-xl text-indigo-400 mb-2 block"></i>
                                        <label for="photo" class="cursor-pointer inline-block bg-white rounded-md font-bold text-indigo-600 hover:text-indigo-500 shadow-sm border border-slate-200 px-4 py-1 text-xs">
                                            <span>Pilih File (Maks 4MB)</span>
                                            <input id="photo" name="photo" type="file" class="sr-only" accept="image/jpeg,image/png,image/jpg" onchange="previewImage(event)">
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-6 border-t border-slate-200">
                        <button type="submit" class="w-full btn-primary py-4 px-8 rounded-xl font-bold text-lg shadow-lg flex justify-center items-center gap-2">
                            Daftar Sekarang <i class="fa-solid fa-arrow-right"></i>
                        </button>
                        <p class="text-[10px] text-center text-slate-400 mt-3">Dengan mendaftar, Anda menyetujui kebijakan privasi sekolah untuk mendata profil alumni.</p>
                    </div>
                </form>
            </div>
            
        </div>
    </div>

    <script>
        function previewImage(event) {
            const input = event.target;
            const previewContainer = document.getElementById('image-preview-container');
            const previewImage = document.getElementById('image-preview');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                    previewContainer.classList.add('block');
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                previewContainer.classList.add('hidden');
                previewContainer.classList.remove('block');
                previewImage.src = "#";
            }
        }
        function toggleJurusan() {
            const schoolSelect = document.getElementById('school-select');
            if(!schoolSelect) return;
            
            const selectedOption = schoolSelect.options[schoolSelect.selectedIndex];
            const type = selectedOption ? selectedOption.getAttribute('data-type') : null;
            
            const jurusanContainer = document.getElementById('jurusan-container');
            const jurusanInput = document.getElementById('jurusan-input');
            
            if (type && type.toUpperCase().includes('SMK')) {
                jurusanContainer.style.display = 'block';
                jurusanInput.setAttribute('required', 'required');
            } else {
                jurusanContainer.style.display = 'none';
                jurusanInput.removeAttribute('required');
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const schoolSelect = document.getElementById('school-select');
            if(schoolSelect) {
                schoolSelect.addEventListener('change', toggleJurusan);
                toggleJurusan(); 
            }
        });
    </script>
</body>
</html>
