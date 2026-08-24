<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Siswa Baru - PEMBDA Nias</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        
        body { font-family: 'Inter', sans-serif; }
        
        .inp { 
            width: 100%;
            padding: 1rem 1.25rem;
            background-color: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.2s;
            color: #0f172a;
            box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.02);
            display: block;
        }
        .inp::placeholder { color: #94a3b8; }
        .inp:focus { 
            outline: none !important;
            border-color: #f97316 !important;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1) !important;
        }
        
        .section-card { 
            background-color: #ffffff;
            border-radius: 2rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.04);
            transition: all 0.3s;
            margin-bottom: 2.5rem;
        }
        .section-header { 
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .btn-primary {
            background-color: #f97316;
            color: #ffffff;
            font-weight: 800;
            border-radius: 1rem;
            padding: 1.25rem 2.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 25px -5px rgba(249, 115, 22, 0.3);
        }
        .btn-primary:hover {
            background-color: #fb923c;
            transform: translateY(-2px);
            box-shadow: 0 25px 30px -5px rgba(249, 115, 22, 0.4);
        }

        [x-cloak] { display: none !important; }
        
        /* Modern Scrollbar */
        ::-webkit-scrollbar { width: 10px; }
        ::-webkit-scrollbar-track { background: #f8fafc; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; border: 2px solid #f8fafc; }
        ::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-4px); }
            75% { transform: translateX(4px); }
        }
        .animate-shake { animation: shake 0.3s cubic-bezier(.36,.07,.19,.97) both; }
        
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-[#fcfaf7] min-h-screen text-slate-600">

    {{-- ========== STICKY NAV ========== --}}
    <nav class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-stone-200/50 shadow-sm">
        <div class="max-w-[1440px] mx-auto px-8 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-orange-500 rounded-xl flex items-center justify-center text-white p-2">
                    <img src="{{ asset('images/logo-pembda.png') }}" class="w-full h-full object-contain brightness-0 invert" alt="">
                </div>
                <div class="hidden xs:block leading-tight">
                    <p class="text-base font-black text-slate-900">PEMBDA Nias</p>
                    <p class="text-[10px] text-slate-400 font-bold tracking-[0.2em] uppercase">Penerimaan Siswa Baru</p>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('home') }}" class="hidden md:flex items-center gap-2 px-4 py-2 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-orange-500 transition-colors">
                    Beranda
                </a>
                <a href="{{ route('public.registration.check') }}" class="flex items-center gap-2 px-6 py-2.5 text-xs font-black uppercase tracking-widest text-white bg-slate-800 rounded-xl hover:bg-slate-700 border border-white/10 transition-all">
                    <i class="fas fa-search text-orange-400"></i> Cek Status
                </a>
            </div>
        </div>
    </nav>

    <div class="max-w-[1440px] mx-auto px-10 py-10 space-y-8" x-data="{ infoTab: {{ $schools->first()->id ?? 1 }}, infoOpen: true, showForm: false, schoolLocked: false, isSubmitting: false, selectedSchool: '{{ old('school_id', '') }}' }">
        
        {{-- Custom Session Alert --}}
        @if(session('success'))
            <div class="bg-emerald-500 text-white p-6 rounded-3xl shadow-xl shadow-emerald-500/20 flex items-center gap-4 animate-bounce">
                <i class="fas fa-check-circle text-3xl"></i>
                <div>
                    <p class="font-black uppercase tracking-widest text-xs">Berhasil!</p>
                    <p class="text-sm font-bold">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-500 text-white p-6 rounded-3xl shadow-xl shadow-red-500/20 flex items-center gap-4 animate-shake">
                <i class="fas fa-exclamation-triangle text-3xl"></i>
                <div>
                    <p class="font-black uppercase tracking-widest text-xs">Gagal!</p>
                    <p class="text-sm font-bold">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if($schools->isEmpty())
        {{-- ========== CLOSED / COMPLETED STATE ========== --}}
        <div class="section-card p-12 text-center max-w-2xl mx-auto space-y-6 bg-white rounded-3xl border border-slate-200/80 shadow-xl my-12">
            <div class="w-20 h-20 bg-amber-50 text-amber-500 rounded-3xl flex items-center justify-center mx-auto text-3xl shadow-lg shadow-amber-500/10 border border-amber-200/60">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="space-y-3">
                <span class="inline-block px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest bg-amber-100 text-amber-800">Pendaftaran Selesai</span>
                <h2 class="text-2xl md:text-3xl font-black text-slate-900">Penerimaan Siswa Baru Telah Ditutup</h2>
                <p class="text-sm font-medium text-slate-500 leading-relaxed max-w-lg mx-auto">
                    Saat ini proses pendaftaran siswa baru untuk tahun ajaran aktif telah selesai dan <strong class="text-slate-800">Kegiatan Belajar Mengajar (KBM) Semester Ganjil TP. 2026/2027</strong> sedang berlangsung aktif. Pendaftaran gelombang baru akan dibuka pada periode penerimaan berikutnya.
                </p>
            </div>
            <div class="pt-4 flex flex-wrap gap-4 justify-center">
                <a href="{{ route('public.registration.check') }}" class="btn-primary">
                    <i class="fas fa-search mr-2"></i> Cek Status Pendaftar
                </a>
                <a href="{{ route('home') }}" class="px-8 py-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-2xl transition-all inline-flex items-center">
                    <i class="fas fa-home mr-2"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
        @else
        {{-- ========== HERO COMPACT ========== --}}
        {{-- ========== HERO RE-DESIGN ========== --}}
        <div class="relative overflow-hidden section-card p-8 md:p-12 text-center !bg-transparent !border-0 !shadow-none !mb-0">
            {{-- Decorative Glows --}}
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[400px] h-[400px] bg-orange-500/10 rounded-full blur-[120px] -z-10"></div>
            <div class="absolute top-0 right-0 w-[300px] h-[300px] bg-blue-500/5 rounded-full blur-[100px] -z-10"></div>

            <div class="max-w-4xl mx-auto space-y-6">
                {{-- Logo & Brand --}}
                <div class="flex flex-col items-center gap-3">
                    <div class="w-14 h-14 bg-white/80 backdrop-blur-xl p-2.5 rounded-2xl shadow-lg border border-stone-200/50">
                        <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo Yayasan" class="w-full h-full object-contain opacity-90">
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-orange-500 uppercase tracking-[0.4em]">Yayasan Perguruan</p>
                        <p class="text-base font-black text-slate-900 uppercase tracking-[0.25em] mt-1">PEMBDA NIAS</p>
                    </div>
                </div>

                {{-- Main Content --}}
                <div class="space-y-4">
                    <h1 class="text-4xl md:text-5xl font-black text-slate-900 leading-[1.1] tracking-tight">
                        Pendaftaran <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-amber-600">Online</span>
                    </h1>
                </div>


                {{-- Motto --}}
                <div class="pt-2">
                    <div class="inline-flex items-center gap-3 px-6 py-2.5 bg-white/80 backdrop-blur-md text-slate-800 rounded-xl border border-stone-200/50 shadow-lg">
                        <span class="text-[9px] font-black uppercase tracking-[0.2em] text-orange-500">Motto</span>
                        <div class="w-px h-4 bg-stone-200"></div>
                        <span class="text-xs font-bold italic text-slate-500">"Keep Moving Forward – Maju Terus Pantang Mundur"</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========== INFO SPESIFIK UNIT (Dynamic Tabs) ========== --}}
        <div class="section-card overflow-hidden" id="school_tabs">
            <button @click="infoOpen = !infoOpen" class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-slate-50 transition-all group">
                <div class="flex items-center gap-4">
                    <div class="w-11 h-11 rounded-xl bg-orange-500 text-white flex items-center justify-center shadow-lg shadow-orange-500/20 transition-transform group-hover:scale-110">
                        <i class="fas fa-university text-lg"></i>
                    </div>
                    <div>
                        <span class="text-lg font-black text-slate-900 block tracking-tight">Informasi & Syarat Pendaftaran</span>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Klik detail unit sekolah</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center transition-all group-hover:bg-slate-100 text-slate-400 group-hover:text-slate-600">
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="infoOpen && 'rotate-180'"></i>
                </div>
            </button>
            
            <div x-show="infoOpen" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-4" class="border-t border-slate-100">
                {{-- Horizontal Tabs --}}
                <div class="flex border-b border-slate-100 bg-slate-50 overflow-x-auto no-scrollbar">
                    @foreach($schools as $s)
                    @php
                        $activeClass = match(true) {
                            str_contains($s->name, 'SMPS') => 'bg-blue-50 text-blue-700 border-b-4 border-blue-600 shadow-inner',
                            str_contains($s->name, 'SMAS') => 'bg-emerald-50 text-emerald-700 border-b-4 border-emerald-600 shadow-inner',
                            str_contains($s->name, 'SMKS') => 'bg-orange-50 text-orange-700 border-b-4 border-orange-600 shadow-inner',
                            default => 'bg-white text-orange-600 border-b-4 border-orange-600'
                        };
                    @endphp
                    <button @click="infoTab = {{ $s->id }}" :class="infoTab == {{ $s->id }} ? '{{ $activeClass }}' : 'text-slate-400 hover:text-slate-600 hover:bg-white/50'" class="flex-1 min-w-[130px] px-5 py-4 text-center font-black text-[10px] uppercase tracking-widest transition-all">
                        <i class="fas fa-{{ match(strtoupper($s->type)){'SMP'=>'graduation-cap','SMA'=>'award','SMK'=>'tools',default=>'school'} }} mr-2" :class="infoTab != {{ $s->id }} && 'opacity-50'"></i> {{ $s->name }}
                    </button>
                    @endforeach
                </div>

                <div class="p-8 space-y-8">
                    {{-- Common Steps (Global) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
                        @php
                            $stepColors = [
                                'bg-blue-500 shadow-blue-500/20',
                                'bg-orange-500 shadow-orange-500/20',
                                'bg-emerald-500 shadow-emerald-500/20',
                                'bg-purple-500 shadow-purple-500/20',
                                'bg-indigo-500 shadow-indigo-500/20'
                            ];
                        @endphp
                        @foreach(['Isi Formulir', 'Bayar Biaya', 'Upload Dokumen', 'Ujian Tes', 'Pengumuman'] as $idx => $step)
                        <div class="flex flex-col items-center text-center gap-2">
                            <div class="w-8 h-8 rounded-xl {{ $stepColors[$idx] ?? 'bg-slate-500' }} text-white text-[10px] font-black flex items-center justify-center shadow-lg">
                                {{ $idx + 1 }}
                            </div>
                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-tighter">{{ $step }}</span>
                        </div>
                        @endforeach
                    </div>

                    {{-- Tab Contents --}}
                    @foreach($schools as $s)
                    <div x-show="infoTab == {{ $s->id }}" x-transition.opacity class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Syarat Berkas Section --}}
                            <div class="bg-indigo-50/50 rounded-2xl p-6 border border-indigo-100 shadow-sm">
                                <h5 class="text-[10px] font-black text-indigo-900 uppercase tracking-widest mb-4 flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-lg bg-indigo-500 text-white flex items-center justify-center shadow-md">
                                        <i class="fas fa-file-invoice text-[9px]"></i>
                                    </div>
                                    Syarat Berkas
                                </h5>
                                <ul class="space-y-4">
                                    @php
                                    $docLabels = $s->getAllDocumentTypes();
                                    $reqs = is_array($s->psb_required_documents) ? $s->psb_required_documents : [];
                                @endphp
                                @forelse($reqs as $r)
                                <li class="flex items-center gap-3 group">
                                    <div class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[9px] shadow-sm">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <span class="text-sm font-black text-slate-900">{{ $docLabels[$r] ?? $r }}</span>
                                </li>
                                @empty
                                <li class="text-xs font-bold text-slate-400 italic">Tidak ada dokumen wajib.</li>
                                @endforelse
                            </ul>
                            </div>

                            {{-- Info & Biaya Section --}}
                            <div class="bg-orange-50/50 rounded-2xl p-6 border border-orange-100 shadow-sm">
                                <h5 class="text-[10px] font-black text-orange-950 uppercase tracking-widest mb-4 flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-lg bg-orange-500 text-white flex items-center justify-center shadow-md">
                                        <i class="fas fa-info-circle text-[9px]"></i>
                                    </div>
                                    Info Seleksi & Kontak
                                </h5>
                                <div class="space-y-4">
                                    <div class="bg-white p-5 rounded-2xl border border-orange-100 shadow-sm">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-1">Metode Seleksi</p>
                                        <p class="text-sm font-black text-slate-900">{{ $s->requires_test ? 'Tes Masuk ('.$s->test_type.')' : 'Seleksi Berkas / Tanpa Tes' }}</p>
                                    </div>
                                    <div class="bg-white p-5 rounded-2xl border border-orange-100 shadow-sm">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-1">Operasional Sekretariat</p>
                                        <p class="text-sm font-black text-slate-900 leading-relaxed">{{ $s->psb_opening_hours ?: 'Hubungi HOTLINE WA' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Description Section --}}
                        @if($s->psb_description)
                        <div class="bg-orange-50/30 rounded-3xl p-8 border border-orange-100 shadow-sm">
                             <h5 class="text-[10px] font-black text-orange-600 uppercase tracking-[0.3em] mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                                Penjelasan Tambahan
                             </h5>
                             <div class="text-sm font-black text-slate-900 leading-relaxed">
                                {!! nl2br(e($s->psb_description)) !!}
                             </div>
                        </div>
                        @endif

                        {{-- ========== GELOMBANG & BIAYA ========== --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            {{-- Gelombang --}}
                            <div class="bg-blue-50/50 rounded-2xl p-6 border border-blue-100 flex flex-col h-full shadow-sm">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-9 h-9 rounded-xl bg-blue-500 text-white flex items-center justify-center shadow-lg shadow-blue-500/20">
                                        <i class="fas fa-calendar-alt text-sm"></i>
                                    </div>
                                    <h4 class="text-[11px] font-black text-blue-900 uppercase tracking-widest leading-none">Gelombang Pendaftaran</h4>
                                </div>
                                <div class="space-y-3 flex-1">
                                    @forelse($s->registrationWaves as $wave)
                                    <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-blue-100 hover:bg-blue-50 transition-all group shadow-sm">
                                        <div>
                                            <p class="text-sm font-black text-slate-900 leading-none mb-1 group-hover:text-blue-600 transition-colors">{{ $wave->name }}</p>
                                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">{{ $wave->start_date->format('d M') }} - {{ $wave->end_date->format('d M Y') }}</p>
                                        </div>
                                        <span class="px-3 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest {{ $wave->isOpen() ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/20' : 'bg-slate-100 text-slate-400' }}">
                                            {{ $wave->getStatusLabel() }}
                                        </span>
                                    </div>
                                    @empty
                                    <p class="text-[10px] font-bold text-slate-400 uppercase italic text-center py-4">Belum ada gelombang aktif.</p>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Biaya --}}
                            <div class="bg-emerald-50/50 rounded-3xl p-8 border border-emerald-100 flex flex-col h-full shadow-sm">
                                <div class="flex items-center gap-4 mb-8">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shadow-lg shadow-emerald-500/20">
                                        <i class="fas fa-coins"></i>
                                    </div>
                                    <h4 class="text-xs font-black text-emerald-900 uppercase tracking-[0.2em] leading-none">Biaya Pendaftaran</h4>
                                </div>
                                <div class="space-y-4 flex-1">
                                    @forelse($s->admissionFees as $fee)
                                    <div class="flex items-center justify-between py-3 border-b border-emerald-100 last:border-0 hover:px-2 transition-all group">
                                        <span class="text-sm font-black text-slate-900 group-hover:text-slate-900 transition-colors uppercase tracking-tight">{{ $fee->fee_name }}</span>
                                        <span class="text-sm font-black text-emerald-600">{{ $fee->formatted_amount }}</span>
                                    </div>
                                    @empty
                                    <p class="text-[10px] font-bold text-slate-400 uppercase italic text-center py-4">Hubungi Panitia PSB.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        {{-- Contact Strip --}}
                        <div class="flex flex-wrap items-center justify-center gap-10 py-12 border-t border-slate-100">
                            <div class="flex items-center gap-5 group">
                                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 group-hover:scale-110 transition-transform shadow-sm">
                                    <i class="fab fa-whatsapp text-2xl"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-1">WhatsApp Panitia ({{ $s->type }})</p>
                                    <p class="text-base font-black text-slate-900 group-hover:text-emerald-600 transition-colors">{{ $s->psb_contact_person ?: 'Hotline Panitia' }} · {{ $s->psb_contact_phone ?: '0889 9114 4184' }}</p>
                                </div>
                            </div>
                            @if($s->psb_secretariat)
                            <div class="flex items-center gap-5 group">
                                <div class="w-14 h-14 rounded-2xl bg-slate-50 text-slate-500 flex items-center justify-center border border-slate-200 group-hover:scale-110 transition-transform shadow-sm">
                                    <i class="fas fa-map-marker-alt text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-1">Lokasi Sekretariat</p>
                                    <p class="text-base font-black text-slate-900 max-w-[300px] truncate" title="{{ $s->psb_secretariat }}">{{ $s->psb_secretariat }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                        {{-- ========== ACTION: MENDAFTAR ========== --}}
                        <div class="pt-12 text-center" x-show="!showForm">
                            <button @click="showForm = true; schoolLocked = true; selectedSchool = {{ $s->id }}; $nextTick(() => { document.getElementById('regForm').scrollIntoView({ behavior: 'smooth' }); document.getElementById('school_id').dispatchEvent(new Event('change')); })" 
                                    class="px-12 py-6 bg-gradient-to-r from-orange-500 to-amber-600 text-white rounded-[2rem] text-lg font-black uppercase tracking-widest shadow-xl shadow-orange-500/20 hover:scale-[1.05] active:scale-95 transition-all flex items-center justify-center gap-4 mx-auto group">
                                <i class="fas fa-edit group-hover:rotate-12 transition-transform"></i> Mendaftar Sekarang di {{ $s->name }}
                            </button>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mt-6 italic">*) Klik tombol di atas jika sudah memahami seluruh syarat dan ketentuan</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ======================== FORM PENDAFTARAN (DIPINDAHKAN) ====================== --}}
        <div x-show="showForm" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-20" id="form-container">
            {{-- Tombol Tutup Form --}}
            <div class="flex justify-center mb-8">
                <button @click="showForm = false; $nextTick(() => { document.getElementById('school_tabs').scrollIntoView({ behavior: 'smooth' }); })" 
                        class="px-6 py-3 bg-red-100 text-red-700 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-red-200 transition-all flex items-center gap-2">
                    <i class="fas fa-times"></i> Tutup Formulir & Kembali ke Info
                </button>
            </div>

        {{-- ========== VALIDATION ERRORS ========== --}}
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-[2.5rem] p-8 md:p-12 animate-shake shadow-xl">
                <div class="flex items-center gap-6 mb-10">
                    <div class="w-16 h-16 rounded-2xl bg-red-600 text-white flex items-center justify-center shadow-xl shadow-red-200">
                        <i class="fas fa-exclamation-triangle text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-black text-red-900 leading-tight">Data Belum Lengkap</h3>
                        <p class="text-[10px] text-red-500 font-bold tracking-[0.2em] uppercase mt-1">Mohon perbaiki kesalahan berikut agar dapat melanjutkan</p>
                    </div>
                </div>
                <ul class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($errors->all() as $error)
                        <li class="text-[11px] font-bold text-red-700 flex items-start gap-4 bg-white p-5 rounded-2xl border border-red-100 shadow-sm">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-600 mt-2 flex-shrink-0"></span>
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('public.registration.store') }}" method="POST" enctype="multipart/form-data" id="regForm" class="space-y-6 md:space-y-8 pb-20">
            @csrf

            {{-- ===== SECTION 1: DATA PRIBADI ===== --}}
            <div class="section-card overflow-hidden">
                <div class="section-header !bg-slate-50">
                    <div class="w-12 h-12 rounded-xl bg-orange-500 text-white flex items-center justify-center shadow-lg shadow-orange-500/20">
                        <i class="fas fa-user-graduate text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">Identitas Calon Siswa</h3>
                        <p class="text-[10px] text-slate-400 font-bold tracking-widest uppercase">Lengkapi informasi utama peserta didik baru</p>
                    </div>
                </div>
                <div class="p-8 md:p-12 space-y-6 md:space-y-8">

                    {{-- Row 1: Sekolah Tujuan + Jalur + NISN --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest flex justify-between items-center mb-1">
                                <span>Sekolah Tujuan</span>
                                <template x-if="schoolLocked">
                                    <button type="button" @click="schoolLocked = false" class="text-orange-500 hover:text-orange-600 lowercase font-bold tracking-normal transition-colors">
                                        <i class="fas fa-lock mr-1"></i> Terkunci (Ubah?)
                                    </button>
                                </template>
                                <span class="text-red-500" x-show="!schoolLocked">Wajib</span>
                            </label>
                            <div class="relative">
                                <select name="school_id" 
                                        id="school_id" 
                                        required 
                                        x-model="selectedSchool"
                                        class="inp transition-all" 
                                        :class="schoolLocked ? 'pointer-events-none bg-slate-50 text-slate-500 ring-2 ring-orange-500/20 border-orange-500/30' : ''"
                                        :tabindex="schoolLocked ? '-1' : '0'"
                                        @change="infoTab = $event.target.value; infoOpen = true; showForm = true">
                                    <option value="">Pilih Unit Sekolah</option>
                                    @foreach($schools as $school)
                                        <option value="{{ $school->id }}" data-type="{{ $school->type }}">{{ $school->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest flex justify-between items-center mb-1">
                                <span>Jalur Pendaftaran</span>
                                <span class="text-red-500">Wajib</span>
                            </label>
                            <select name="admission_path" id="admission_path" required class="inp">
                                <option value="">Pilih Jalur</option>
                                <option value="reguler" {{ old('admission_path') == 'reguler' ? 'selected' : '' }}>📝 Reguler</option>
                                <option value="prestasi" {{ old('admission_path') == 'prestasi' ? 'selected' : '' }}>🏆 Prestasi</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest flex justify-between items-center mb-1">
                                <span>Nomor NISN</span>
                                <span class="text-red-500">Wajib</span>
                            </label>
                            <input type="text" name="nisn" required value="{{ old('nisn') }}" class="inp" placeholder="Input 10 digit NISN">
                        </div>
                    </div>

                    {{-- SMK Program & Konsentrasi --}}
                    <div id="smk-program-section" class="hidden">
                        <div class="bg-blue-50/50 rounded-[2.5rem] p-10 border border-blue-100 space-y-8 relative overflow-hidden shadow-sm">
                            <div class="absolute top-0 right-0 w-40 h-40 bg-blue-500/5 rounded-full blur-3xl"></div>
                            <div class="flex items-center gap-5 relative z-10">
                                <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/20">
                                    <i class="fas fa-graduation-cap text-lg"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-black text-slate-900 tracking-tight uppercase">Pilihan Jurusan SMK</h4>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Tentukan program keahlian yang diminati</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 relative z-10">
                                <div class="space-y-3">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block pl-1">Program Keahlian</label>
                                    <select name="program_keahlian_id" id="program_keahlian_id" class="inp">
                                        <option value="">Pilih Program</option>
                                    </select>
                                </div>
                                <div class="space-y-3">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block pl-1">Konsentrasi Keahlian</label>
                                    <select name="konsentrasi_keahlian_id" id="konsentrasi_keahlian_id" class="inp">
                                        <option value="">Pilih Konsentrasi</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Prestasi Section --}}
                    <div id="prestasi-section" class="hidden">
                        <div class="bg-orange-50/50 rounded-[2.5rem] p-10 border border-orange-100 space-y-8 relative overflow-hidden shadow-sm">
                            <div class="absolute top-0 right-0 w-40 h-40 bg-orange-500/5 rounded-full blur-3xl"></div>
                            <div class="flex items-center justify-between relative z-10">
                                <div class="flex items-center gap-5">
                                    <div class="w-12 h-12 rounded-2xl bg-orange-500 text-white flex items-center justify-center shadow-lg shadow-orange-500/20">
                                        <i class="fas fa-medal text-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-black text-slate-900 tracking-tight uppercase">Jalur Prestasi</h4>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Lengkapi data prestasi untuk klaim beasiswa</p>
                                    </div>
                                </div>
                                <span class="hidden sm:inline-block text-[10px] font-black text-white bg-emerald-500 px-6 py-3 rounded-2xl uppercase tracking-widest shadow-lg shadow-emerald-500/20">Bebas Biaya Pendaftaran</span>
                            </div>

                            {{-- Hidden fields --}}
                            <input type="hidden" name="achievement_grade" id="achievement_grade_hidden">
                            <input type="hidden" name="achievement_rank" id="achievement_rank_hidden">
                            <input type="hidden" name="achievement_school" id="achievement_school_final">
                            <input type="hidden" name="achievement_year" id="achievement_year_final">

                            {{-- Prestasi: Tujuan SMA/SMK (asal SMP) --}}
                            <div id="prestasi-form-sma-smk" class="hidden space-y-6 relative z-10">
                                <div class="bg-white p-8 rounded-3xl border border-orange-100 shadow-sm">
                                    <label class="text-[11px] font-black text-slate-400 mb-6 block tracking-[0.2em] uppercase">Status Alumni Terintegrasi</label>
                                    <div class="flex flex-wrap gap-6">
                                        <label class="flex items-center gap-3 cursor-pointer group">
                                            <input type="radio" name="from_smps_pembda" value="ya" class="hidden peer">
                                            <div class="px-8 py-5 rounded-2xl border-2 border-slate-100 bg-slate-50 text-slate-500 font-black text-xs uppercase tracking-widest transition-all peer-checked:border-orange-500 peer-checked:bg-orange-50 peer-checked:text-orange-600 group-hover:border-slate-200">
                                                <i class="fas fa-check-circle mr-2 opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                                                Lulusan SMPS Pembda 2
                                            </div>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer group">
                                            <input type="radio" name="from_smps_pembda" value="tidak" class="hidden peer">
                                            <div class="px-8 py-5 rounded-2xl border-2 border-slate-100 bg-slate-50 text-slate-500 font-black text-xs uppercase tracking-widest transition-all peer-checked:border-slate-400 peer-checked:bg-white peer-checked:text-slate-900 group-hover:border-slate-200">
                                                Lulusan SMP Lain
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                {{-- From SMPS Pembda 2 --}}
                                <div id="from-smps-fields" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-8 p-6 bg-white/[0.03] rounded-2xl border border-white/5">
                                    <div class="space-y-3">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block pl-1">Peringkat Kelas IX</label>
                                        <select id="rank_smps" class="inp">
                                            <option value="">Pilih Peringkat</option>
                                            <option value="1">🏆 Juara 1 (Umum)</option>
                                            <option value="2">🏆 Juara 2 (Umum)</option>
                                            <option value="3">🏆 Juara 3 (Umum)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Tahun Ajaran <span class="text-red-500">*</span></label>
                                        <input type="text" id="year_smps" value="{{ date('Y') }}/{{ date('Y') + 1 }}" class="inp">
                                    </div>
                                </div>
                                {{-- From SMP lain --}}
                                <div id="from-other-smp-fields" class="hidden space-y-6">
                                    <div class="text-[9px] bg-orange-500/10 text-orange-400 border border-orange-500/20 rounded-lg px-4 py-2 font-black uppercase tracking-[0.2em] inline-block">Khusus Juara 1 (Umum) Kelas IX</div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                                        <div class="space-y-3">
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Asal Sekolah <span class="text-red-500">*</span></label>
                                            <input type="text" id="school_other_smp" class="inp" placeholder="SMPN 1 Gunungsitoli">
                                        </div>
                                        <div class="space-y-3">
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Tahun Ajaran <span class="text-red-500">*</span></label>
                                            <input type="text" id="year_other_smp" value="{{ date('Y') }}/{{ date('Y') + 1 }}" class="inp">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Prestasi: Tujuan SMPS (asal SD) --}}
                            <div id="prestasi-form-smps" class="hidden space-y-6 relative z-10">
                                <div class="text-[9px] bg-orange-500/10 text-orange-400 border border-orange-500/20 rounded-lg px-4 py-2 font-black uppercase tracking-[0.2em] inline-block">Khusus Juara 1 (Umum) Kelas 6 SD</div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                                    <div class="space-y-3">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Nama SD Asal <span class="text-red-500">*</span></label>
                                        <input type="text" id="school_sd" class="inp" placeholder="SDN 1 Gunungsitoli">
                                    </div>
                                    <div class="space-y-3">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Tahun Ajaran <span class="text-red-500">*</span></label>
                                        <input type="text" id="year_sd" value="{{ date('Y') }}/{{ date('Y') + 1 }}" class="inp">
                                    </div>
                                </div>
                            </div>

                            {{-- Certificate Upload --}}
                            <div id="prestasi-certificate-upload" class="hidden mt-8 relative z-10">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-4 block pl-1">Dokumen Pendukung Prestasi</label>
                                <div class="relative group">
                                    <input type="file" name="achievement_certificate" id="achievement_certificate_input" 
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" accept="image/*,application/pdf">
                                    <div class="border-2 border-dashed border-orange-200 rounded-[2rem] p-12 text-center transition-all group-hover:border-orange-400 group-hover:bg-orange-50 bg-slate-50 relative">
                                        <div class="w-16 h-16 bg-orange-500 text-white rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform shadow-lg shadow-orange-500/20">
                                            <i class="fas fa-cloud-upload-alt text-2xl"></i>
                                        </div>
                                        <span class="text-sm font-black text-slate-900 block mb-1">Unggah Sertifikat atau Raport</span>
                                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest block">PDF, JPG, PNG (Maks 10MB)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Row 2: Nama + Gender + Agama --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="space-y-3">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest flex justify-between items-center">
                                <span>Nama Lengkap Siswa</span>
                                <span class="text-red-500">Wajib</span>
                            </label>
                            <input type="text" name="full_name" required value="{{ old('full_name') }}" class="inp" placeholder="Nama Sesuai Akta">
                        </div>
                        <div class="space-y-3">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest flex justify-between items-center">
                                <span>Jenis Kelamin</span>
                                <span class="text-red-500">Wajib</span>
                            </label>
                            <select name="gender" required class="inp">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="L" {{ old('gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="space-y-3">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest flex justify-between items-center">
                                <span>Agama</span>
                                <span class="text-red-500">Wajib</span>
                            </label>
                            <select name="religion" required class="inp">
                                <option value="">Pilih Agama</option>
                                <option value="Islam" {{ old('religion') == 'Islam' ? 'selected' : '' }}>Islam</option>
                                <option value="Kristen" {{ old('religion') == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                                <option value="Katolik" {{ old('religion') == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                                <option value="Hindu" {{ old('religion') == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                <option value="Buddha" {{ old('religion') == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                                <option value="Konghucu" {{ old('religion') == 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                            </select>
                        </div>
                    </div>

                    {{-- Row: Tempat, Tgl Lahir, HP, Email --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div class="space-y-3">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest flex justify-between items-center">
                                <span>Tempat Lahir</span>
                                <span class="text-red-500">Wajib</span>
                            </label>
                            <input type="text" name="birth_place" required value="{{ old('birth_place') }}" class="inp" placeholder="Contoh: Gunungsitoli">
                        </div>
                        <div class="space-y-3">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest flex justify-between items-center">
                                <span>Tanggal Lahir</span>
                                <span class="text-red-500">Wajib</span>
                            </label>
                            <input type="date" name="birth_date" required value="{{ old('birth_date') }}" class="inp">
                        </div>
                        <div class="space-y-3">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest flex justify-between items-center">
                                <span>No. HP / WA</span>
                                <span class="text-red-500">WA</span>
                            </label>
                            <input type="text" name="phone" required value="{{ old('phone') }}" class="inp" placeholder="08xx...">
                        </div>
                        <div class="space-y-3">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Alamat Email</label>
                            <input type="email" name="email" required value="{{ old('email') }}" class="inp" placeholder="email@contoh.com">
                        </div>
                    </div>

                    {{-- Alamat --}}
                    <div class="space-y-3">
                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest leading-loose">Alamat Lengkap (Sesuai Kartu Keluarga)</label>
                        <textarea name="address" rows="3" required class="inp resize-none" placeholder="Masukkan Dusun, RT/RW, Desa, dan Kecamatan">{{ old('address') }}</textarea>
                    </div>

                    {{-- Foto --}}
                    <div class="bg-slate-50 rounded-[2.5rem] p-10 border border-slate-100 shadow-sm">
                        <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-8 block text-center sm:text-left">Pas Foto Terbaru (3x4)</label>
                        <div class="flex flex-col sm:flex-row items-center gap-10">
                            <div id="preview-container" class="w-36 h-48 border-4 border-white rounded-3xl flex items-center justify-center bg-white shadow-xl overflow-hidden flex-shrink-0 relative group transition-all ring-1 ring-slate-200">
                                <img id="photo-preview" class="hidden w-full h-full object-cover" />
                                <div id="preview-placeholder" class="text-center">
                                    <i class="fas fa-user-circle text-5xl text-slate-200 block mb-3"></i>
                                    <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest leading-tight block">Pas Foto<br>Calon Siswa</span>
                                </div>
                            </div>
                            <div class="flex-1 w-full space-y-5">
                                <input type="file" name="photo" id="photo-input" accept="image/*" capture="environment" class="hidden">
                                <button type="button" onclick="document.getElementById('photo-input').click()" class="w-full py-6 px-8 bg-white text-slate-900 rounded-2xl font-black text-sm hover:bg-slate-50 transition-all flex items-center justify-center gap-4 border-2 border-slate-200 shadow-sm">
                                    <div class="w-10 h-10 rounded-xl bg-orange-500 text-white flex items-center justify-center shadow-lg shadow-orange-500/20">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                    Ambil atau Pilih Foto Siswa
                                </button>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div class="bg-white p-4 rounded-xl border-2 border-slate-200 flex items-center gap-3">
                                        <i class="fas fa-info-circle text-orange-500 text-sm"></i>
                                        <span class="text-xs font-bold text-slate-500 tracking-tight">Format JPG/PNG, Maks 2MB</span>
                                    </div>
                                    <div class="bg-white p-4 rounded-xl border-2 border-slate-200 flex items-center gap-3">
                                        <i class="fas fa-palette text-orange-500 text-sm"></i>
                                        <span class="text-xs font-bold text-slate-500 tracking-tight">Gunakan latar polos</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== SECTION 2: DATA ORANG TUA ===== --}}
            <div class="section-card overflow-hidden">
                <div class="section-header !bg-slate-50">
                    <div class="w-12 h-12 rounded-xl bg-orange-500 text-white flex items-center justify-center shadow-lg shadow-orange-500/20">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">Data Orang Tua / Wali</h3>
                        <p class="text-[10px] text-slate-400 font-bold tracking-widest uppercase">Lengkapi informasi penanggung jawab calon siswa</p>
                    </div>
                </div>
                <div class="p-8 md:p-12 space-y-16">
                    {{-- Identitas Ayah --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center border border-orange-100 shadow-sm">
                                    <i class="fas fa-male text-lg"></i>
                                </div>
                                <h4 class="font-black text-slate-900 text-base tracking-tight uppercase">Data Ayah</h4>
                            </div>
                            <p class="text-[10px] text-slate-400 leading-relaxed font-bold uppercase tracking-wider pl-1">Informasi ayah kandung sesuai identitas kartu keluarga</p>
                        </div>
                        <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-8">
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest flex justify-between items-center mb-1">
                                    <span>Nama Ayah</span>
                                    <span class="text-red-500">Wajib</span>
                                </label>
                                <input type="text" name="father_name" required value="{{ old('father_name') }}" class="inp" placeholder="Nama Lengkap">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">No. HP Ayah</label>
                                <input type="text" name="father_phone" value="{{ old('father_phone') }}" class="inp" placeholder="08xx...">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Pekerjaan</label>
                                <input type="text" name="father_occupation" value="{{ old('father_occupation') }}" class="inp" placeholder="Contoh: Petani">
                            </div>
                        </div>
                    </div>
                    
                    <div class="h-px bg-white/5"></div>

                    {{-- Identitas Ibu --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center border border-orange-100 shadow-sm">
                                    <i class="fas fa-female text-lg"></i>
                                </div>
                                <h4 class="font-black text-slate-900 text-base tracking-tight uppercase">Data Ibu</h4>
                            </div>
                            <p class="text-[10px] text-slate-400 leading-relaxed font-bold uppercase tracking-wider pl-1">Informasi ibu kandung sesuai identitas kartu keluarga</p>
                        </div>
                        <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-8">
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest flex justify-between items-center mb-1">
                                    <span>Nama Ibu</span>
                                    <span class="text-red-500">Wajib</span>
                                </label>
                                <input type="text" name="mother_name" required value="{{ old('mother_name') }}" class="inp" placeholder="Nama Lengkap">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">No. HP Ibu</label>
                                <input type="text" name="mother_phone" value="{{ old('mother_phone') }}" class="inp" placeholder="08xx...">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Pekerjaan</label>
                                <input type="text" name="mother_occupation" value="{{ old('mother_occupation') }}" class="inp" placeholder="Ibu Rumah Tangga">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== SECTION 3: SEKOLAH ASAL ===== --}}
            <div class="section-card overflow-hidden">
                <div class="section-header !bg-slate-50">
                    <div class="w-12 h-12 rounded-xl bg-orange-500 text-white flex items-center justify-center shadow-lg shadow-orange-500/20">
                        <i class="fas fa-school text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">Data Sekolah Asal</h3>
                        <p class="text-[10px] text-slate-400 font-bold tracking-widest uppercase">Riwayat pendidikan sebelumnya</p>
                    </div>
                </div>
                <div class="p-8 md:p-12">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest flex justify-between items-center">
                                <span>Nama Sekolah Asal</span>
                                <span class="text-red-500">Wajib</span>
                            </label>
                            <input type="text" name="previous_school" required value="{{ old('previous_school') }}" class="inp" placeholder="Contoh: SDN 1 Gunungsitoli">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Alamat / Lokasi Sekolah</label>
                            <input type="text" name="previous_school_address" value="{{ old('previous_school_address') }}" class="inp" placeholder="Kota atau Desa asal">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== SUBMIT ===== --}}
            <div class="text-center space-y-10 py-16">
                <div class="flex flex-col items-center gap-5">
                    <div class="flex items-center gap-3 text-[11px] font-black text-slate-500 tracking-[0.4em] uppercase">
                        <div class="w-2 h-2 rounded-full bg-orange-500 animate-ping"></div>
                        Konfirmasi Keaslian Data
                    </div>
                    <p class="text-[11px] text-slate-500 max-w-sm mx-auto leading-relaxed font-medium">
                        Dengan menekan tombol kirim, Saya menyatakan bahwa seluruh data yang diisi adalah benar dan sesuai dengan dokumen asli yang sah.
                    </p>
                </div>
                
                <button type="submit" 
                        :disabled="isSubmitting"
                        id="submit-btn"
                        class="w-full sm:w-auto px-16 py-7 bg-slate-900 text-white rounded-[2.5rem] font-black text-base hover:bg-orange-600 transition-all shadow-xl shadow-slate-900/20 group disabled:opacity-70 disabled:cursor-not-allowed">
                    <template x-if="!isSubmitting">
                        <span class="flex items-center">
                            KIRIM PENDAFTARAN <i class="fas fa-paper-plane ml-3 text-orange-500 group-hover:text-white group-hover:translate-x-1 group-hover:-translate-y-1 transition-all"></i>
                        </span>
                    </template>
                    <template x-if="isSubmitting">
                        <span class="flex items-center">
                            <i class="fas fa-circle-notch fa-spin mr-3"></i> MENGIRIM...
                        </span>
                    </template>
                </button>
                
                <div class="flex flex-wrap items-center justify-center gap-8 text-[10px] font-black text-slate-600 tracking-[0.25em] uppercase">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-lock text-slate-800"></i> Aman & Terenkripsi
                    </span>
                    <span class="flex items-center gap-2">
                        <i class="fab fa-whatsapp text-slate-800"></i> Notifikasi Otomatis
                    </span>
                </div>
            </div>
        </form>
        @endif
    </div>

    <footer class="mt-16 border-t border-slate-100 bg-white relative overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-px bg-gradient-to-r from-transparent via-orange-500/50 to-transparent"></div>
        <div class="max-w-[1440px] mx-auto px-10 py-12 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-y-12 md:gap-x-12 text-center md:text-left">
                <div class="md:col-span-4 space-y-6">
                    <div class="flex items-center justify-center md:justify-start gap-4">
                        <div class="w-16 h-16 bg-white p-2 rounded-2xl shadow-xl border border-slate-100 flex items-center justify-center">
                            <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo Yayasan" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <span class="text-xl font-black text-slate-900 tracking-tighter block leading-none">YAYASAN PEMBDA NIAS</span>
                            <span class="text-[10px] font-black text-orange-500 uppercase tracking-[0.3em] mt-2 block">Pendaftaran Online</span>
                        </div>
                    </div>
                    <p class="text-sm text-slate-500 leading-relaxed font-bold max-w-md">
                        Membangun Sumber Daya Manusia yang berkualitas dan berkarakter baik melalui pendidikan yang relevan, progresif, dan berorientasi masa depan.
                    </p>
                    <div class="inline-flex items-center gap-2 text-[10px] font-black text-slate-400 uppercase tracking-wider italic">
                        <i class="fas fa-quote-left text-orange-500/50"></i>
                        Keep Moving Forward – Maju Terus Pantang Mundur
                    </div>
                </div>
                <div class="md:col-span-4 space-y-4">
                    <h4 class="text-[11px] font-black text-slate-900 uppercase tracking-[0.3em] mb-6">Lokasi Kampus</h4>
                    <div class="rounded-2xl overflow-hidden border border-slate-100 shadow-sm h-32 md:h-40 w-full mb-4">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.59!2d97.61!3d1.28!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30256!2sJl.%20Pelita%20No.09%2C%20Ilir%2C%20Kec.%20Gunungsitoli%2C%20Kota%20Gunungsitoli%2C%20Sumatera%20Utara%2022812!5e0!3m2!1sid!2sid!4v1709440000000!5m2!1sid!2sid" 
                            width="100%" 
                            height="100%" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                    <p class="text-xs font-medium text-slate-400 flex items-start justify-center md:justify-start gap-3 leading-loose">
                        <i class="fas fa-map-pin text-orange-500 mt-1.5 flex-shrink-0"></i>
                        Jl. Pelita 09, Kel. Ilir, Kec. Gunungsitoli, Kota Gunungsitoli, Sumatera Utara (22812)
                    </p>
                </div>
                <div class="md:col-span-4 space-y-4">
                    <h4 class="text-[11px] font-black text-slate-900 uppercase tracking-[0.3em] mb-6">Hubungi Kami</h4>
                    <div class="space-y-5">
                        <a href="https://wa.me/6288991144184" class="group text-sm font-medium text-slate-500 hover:text-slate-900 transition-all flex items-center justify-center md:justify-start gap-4">
                            <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center text-orange-600 group-hover:bg-orange-500 group-hover:text-white transition-all shadow-sm">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            0889 9114 4184
                        </a>
                        <div class="text-sm font-medium text-slate-500 flex items-center justify-center md:justify-start gap-4">
                            <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center text-orange-600 shadow-sm flex-shrink-0">
                                <i class="far fa-envelope"></i>
                            </div>
                            <span class="truncate" title="perguruanpembdanias@gmail.com">perguruanpembdanias@gmail.com</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="h-px bg-slate-100 mt-20 mb-10"></div>
            
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <p class="text-[10px] font-black text-slate-400 tracking-[0.2em] uppercase">
                    © 2026 Yayasan Perguruan PEMBDA Nias · All Rights Reserved
                </p>
                <div class="flex items-center gap-8">
                    <a href="#" class="text-slate-400 hover:text-slate-900 transition-all"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-slate-400 hover:text-slate-900 transition-all"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-slate-400 hover:text-slate-900 transition-all"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
    </footer>

    {{-- ========== JAVASCRIPT ========== --}}
    <script>
        // Photo preview
        const photoInput = document.getElementById('photo-input');
        if (photoInput) {
            photoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('photo-preview').src = e.target.result;
                        document.getElementById('photo-preview').classList.remove('hidden');
                        document.getElementById('preview-placeholder').classList.add('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // === Elements & Form Listeners ===
        const regForm = document.getElementById('regForm');
        if (regForm) {
            const schoolIdSelect = document.getElementById('school_id');
            const admissionPathSelect = document.getElementById('admission_path');
            const prestasiSection = document.getElementById('prestasi-section');
            const prestasiFormSmaSmk = document.getElementById('prestasi-form-sma-smk');
            const prestasiFormSmps = document.getElementById('prestasi-form-smps');
            const certificateUpload = document.getElementById('prestasi-certificate-upload');
            const fromSmpsRadios = document.querySelectorAll('input[name="from_smps_pembda"]');
            const fromSmpsFields = document.getElementById('from-smps-fields');
            const fromOtherSmpFields = document.getElementById('from-other-smp-fields');
            const smkSection = document.getElementById('smk-program-section');
            const programSelect = document.getElementById('program_keahlian_id');
            const konsentrasiSelect = document.getElementById('konsentrasi_keahlian_id');

            // === Admission path toggle ===
            if (admissionPathSelect) {
                admissionPathSelect.addEventListener('change', function() {
                    if (this.value === 'prestasi') {
                        prestasiSection.classList.remove('hidden');
                        updatePrestasiForm();
                    } else {
                        prestasiSection.classList.add('hidden');
                        resetPrestasiForm();
                    }
                });
            }

        // === School change ===
        schoolIdSelect.addEventListener('change', function() {
            const schoolId = this.value;
            const selectedOption = this.options[this.selectedIndex];
            const schoolType = selectedOption ? selectedOption.getAttribute('data-type') : '';
            const isSMK = (schoolType === 'SMK');

            // SMK section
            if (isSMK && schoolId) {
                smkSection.classList.remove('hidden');
                programSelect.setAttribute('required', 'required');
                fetch(`{{ url('api/program-keahlian') }}/${schoolId}`)
                    .then(r => {
                        if (!r.ok) throw new Error('Network error');
                        return r.json();
                    })
                    .then(programs => {
                        programSelect.innerHTML = '<option value="">Pilih Program</option>';
                        programs.forEach(p => {
                            programSelect.innerHTML += `<option value="${p.id}">${p.kode} - ${p.nama}</option>`;
                        });
                    }).catch(() => {
                        programSelect.innerHTML = '<option value="">Error memuat data</option>';
                    });
                konsentrasiSelect.innerHTML = '<option value="">Pilih Konsentrasi</option>';
            } else {
                smkSection.classList.add('hidden');
                programSelect.value = '';
                konsentrasiSelect.value = '';
                programSelect.removeAttribute('required');
                konsentrasiSelect.removeAttribute('required');
            }

            // Update prestasi if active
            if (admissionPathSelect.value === 'prestasi') {
                updatePrestasiForm();
            }
        });

        // === Program → Konsentrasi ===
        programSelect.addEventListener('change', function() {
            const programId = this.value;
            if (programId) {
                konsentrasiSelect.setAttribute('required', 'required');
                fetch(`{{ url('api/konsentrasi-keahlian') }}/${programId}`)
                    .then(r => {
                        if (!r.ok) throw new Error('Network error');
                        return r.json();
                    })
                    .then(list => {
                        konsentrasiSelect.innerHTML = '<option value="">Pilih Konsentrasi</option>';
                        list.forEach(k => {
                            konsentrasiSelect.innerHTML += `<option value="${k.id}">${k.kode} - ${k.nama}</option>`;
                        });
                    }).catch(() => {
                        konsentrasiSelect.innerHTML = '<option value="">Error memuat data</option>';
                    });
            } else {
                konsentrasiSelect.innerHTML = '<option value="">Pilih Konsentrasi</option>';
                konsentrasiSelect.removeAttribute('required');
            }
        });

        // === Update prestasi form based on target school ===
        function updatePrestasiForm() {
            const targetSchoolId = schoolIdSelect.value;
            prestasiFormSmaSmk.classList.add('hidden');
            prestasiFormSmps.classList.add('hidden');
            certificateUpload.classList.add('hidden');
            fromSmpsFields.classList.add('hidden');
            fromOtherSmpFields.classList.add('hidden');

            if (targetSchoolId == '2' || targetSchoolId == '3') {
                prestasiFormSmaSmk.classList.remove('hidden');
            } else if (targetSchoolId == '1') {
                prestasiFormSmps.classList.remove('hidden');
                certificateUpload.classList.remove('hidden');
            }
        }

        // === Radio: from SMPS Pembda? ===
        fromSmpsRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                fromSmpsFields.classList.add('hidden');
                fromOtherSmpFields.classList.add('hidden');
                if (this.value === 'ya') {
                    fromSmpsFields.classList.remove('hidden');
                } else {
                    fromOtherSmpFields.classList.remove('hidden');
                }
                certificateUpload.classList.remove('hidden');
            });
        });

        // === Reset prestasi ===
        function resetPrestasiForm() {
            fromSmpsRadios.forEach(r => r.checked = false);
            fromSmpsFields.classList.add('hidden');
            fromOtherSmpFields.classList.add('hidden');
            certificateUpload.classList.add('hidden');
            prestasiFormSmaSmk.classList.add('hidden');
            prestasiFormSmps.classList.add('hidden');
            document.getElementById('achievement_rank_hidden').value = '';
            document.getElementById('achievement_grade_hidden').value = '';
            document.getElementById('achievement_school_final').value = '';
            document.getElementById('achievement_year_final').value = '';
            document.getElementById('rank_smps').value = '';
            document.getElementById('year_smps').value = '{{ date("Y") }}/{{ date("Y") + 1 }}';
            document.getElementById('school_other_smp').value = '';
            document.getElementById('year_other_smp').value = '{{ date("Y") }}/{{ date("Y") + 1 }}';
            document.getElementById('school_sd').value = '';
            document.getElementById('year_sd').value = '{{ date("Y") }}/{{ date("Y") + 1 }}';
            document.getElementById('achievement_certificate_input').value = '';
        }

        // === Form submit: consolidate prestasi data ===
        document.getElementById('regForm').addEventListener('submit', function(e) {
            // Get Alpine data to set isSubmitting
            const el = document.querySelector('[x-data]');
            const data = el.__x ? el.__x.$data : (window.Alpine ? window.Alpine.$data(el) : null);

            if (admissionPathSelect.value === 'prestasi') {
                const targetSchoolId = schoolIdSelect.value;

                if (targetSchoolId == '2' || targetSchoolId == '3') {
                    const fromSmpsPembda = document.querySelector('input[name="from_smps_pembda"]:checked')?.value;
                    if (!fromSmpsPembda) {
                        e.preventDefault();
                        alert('Pilih apakah Anda lulusan SMPS Pembda 2!');
                        if (data) data.isSubmitting = false;
                        return;
                    }
                    if (fromSmpsPembda === 'ya') {
                        const rank = document.getElementById('rank_smps').value;
                        const year = document.getElementById('year_smps').value;
                        if (!rank || !year) { e.preventDefault(); alert('Lengkapi data prestasi!'); if (data) data.isSubmitting = false; return; }
                        document.getElementById('achievement_rank_hidden').value = rank;
                        document.getElementById('achievement_grade_hidden').value = '9';
                        document.getElementById('achievement_school_final').value = 'SMPS Pembda 2 Gunungsitoli';
                        document.getElementById('achievement_year_final').value = year;
                    } else {
                        const school = document.getElementById('school_other_smp').value;
                        const year = document.getElementById('year_other_smp').value;
                        if (!school || !year) { e.preventDefault(); alert('Lengkapi data prestasi!'); if (data) data.isSubmitting = false; return; }
                        document.getElementById('achievement_rank_hidden').value = '1';
                        document.getElementById('achievement_grade_hidden').value = '9';
                        document.getElementById('achievement_school_final').value = school;
                        document.getElementById('achievement_year_final').value = year;
                    }
                } else if (targetSchoolId == '1') {
                    const school = document.getElementById('school_sd').value;
                    const year = document.getElementById('year_sd').value;
                    if (!school || !year) { e.preventDefault(); alert('Lengkapi data prestasi!'); if (data) data.isSubmitting = false; return; }
                    document.getElementById('achievement_rank_hidden').value = '1';
                    document.getElementById('achievement_grade_hidden').value = '6';
                    document.getElementById('achievement_school_final').value = school;
                    document.getElementById('achievement_year_final').value = year;
                }

                if (!document.getElementById('achievement_certificate_input').files[0]) {
                    e.preventDefault();
                    alert('Upload bukti raport/piagam!');
                    if (data) data.isSubmitting = false;
                    return;
                }
            }
            
            // If all validations pass, set isSubmitting to true
            if (data) data.isSubmitting = true;
        });
        }
        // === Auto-show form if errors exist ===
        document.addEventListener('DOMContentLoaded', function() {
            @if($errors->any() || session('error') || session('success'))
                // Get Alpine data to show form
                const el = document.querySelector('[x-data]');
                if (el) {
                    const data = el.__x ? el.__x.$data : (window.Alpine ? window.Alpine.$data(el) : null);
                    if (data) {
                       data.showForm = true;
                       data.infoOpen = true;
                    }
                    
                    // Scroll to error/status message
                    setTimeout(() => {
                        const msg = document.querySelector('.animate-shake') || document.getElementById('regForm');
                        if (msg) msg.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 500);
                }
            @endif
        });
    </script>
</body>
</html>

