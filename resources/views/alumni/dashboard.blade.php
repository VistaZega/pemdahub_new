@extends('layouts.alumni')

@section('title', 'Dashboard Alumni')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-br from-indigo-600 via-blue-700 to-indigo-900 rounded-3xl shadow-2xl p-8 sm:p-10 text-white relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-8">
        <div class="absolute -right-10 -top-10 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl"></div>
        <div class="absolute right-40 -bottom-20 w-48 h-48 bg-cyan-400 opacity-20 rounded-full blur-2xl"></div>
        <div class="absolute left-0 top-0 w-full h-full bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
        
        <div class="relative z-10 md:w-2/3">
            <span class="inline-block px-3 py-1 bg-white/20 rounded-full text-xs font-bold tracking-wider mb-4 border border-white/20 backdrop-blur-sm shadow-sm"><i class="fas fa-sparkles text-amber-300 mr-1"></i> PORTAL ALUMNI PEMBDA</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold mb-3 leading-tight text-white">Selamat Datang Kembali,<br><span class="text-amber-300 font-black tracking-tight drop-shadow-sm">{{ $alumni->full_name }}</span></h2>
            <p class="text-indigo-100 text-sm sm:text-base mb-6 leading-relaxed max-w-2xl">
                Ini adalah rumah digital Anda. Temukan teman seangkatan, kembangkan karir Anda, dan mari bersama-sama berkontribusi untuk almamater tercinta.
            </p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('alumni.forum.index') }}" class="bg-white text-indigo-700 hover:bg-indigo-50 font-bold px-6 py-2.5 rounded-xl shadow-lg transition flex items-center gap-2 transform hover:-translate-y-0.5">
                    <i class="fas fa-comments"></i> Menuju Forum Alumni
                </a>
                <a href="{{ route('alumni.jobs.index') }}" class="bg-indigo-500/30 hover:bg-indigo-500/50 border border-indigo-400/50 text-white font-bold px-6 py-2.5 rounded-xl transition flex items-center gap-2 backdrop-blur-sm">
                    <i class="fas fa-briefcase"></i> Lihat Lowongan Kerja
                </a>
            </div>
        </div>

        <div class="relative z-10 md:w-1/3 flex justify-center">
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-amber-400 to-pink-500 rounded-full blur opacity-70 group-hover:opacity-100 transition duration-500"></div>
                <img src="{{ $alumni->photo_url }}" alt="Profile Photo" class="relative w-32 h-32 md:w-40 md:h-40 rounded-full object-cover border-4 border-white shadow-2xl">
                @if($alumni->is_approved)
                    <div class="absolute bottom-2 right-2 bg-emerald-500 text-white w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center border-2 border-white shadow-lg" title="Verified Alumni">
                        <i class="fas fa-check text-sm md:text-base"></i>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Alert / Reminder for Tracer Study -->
    @if(!$hasFilledTracer)
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-5 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1.5 h-full bg-amber-400"></div>
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-500 shrink-0">
                <i class="fas fa-exclamation-triangle text-xl"></i>
            </div>
            <div>
                <h4 class="font-bold text-amber-900 text-lg">Anda belum mengisi Tracer Study!</h4>
                <p class="text-sm text-amber-700 mt-1">Bantu sekolah mendata alumni dengan mengisi kuesioner singkat mengenai aktivitas Anda saat ini (Kerja/Kuliah/Wirausaha).</p>
            </div>
        </div>
        <a href="{{ route('alumni.tracer.form') }}" class="shrink-0 bg-amber-500 hover:bg-amber-600 text-white font-bold px-6 py-2.5 rounded-xl shadow-md transition flex items-center gap-2 whitespace-nowrap">
            <i class="fas fa-pen"></i> Isi Sekarang
        </a>
    </div>
    @endif
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Main Dashboard Column -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Quick Info Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ route('alumni.forum.index') }}" class="bg-white rounded-2xl p-5 border-2 border-slate-900 shadow-md hover:shadow-lg transition group relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-indigo-100 rounded-full group-hover:scale-150 transition duration-500 ease-in-out"></div>
                    <div class="relative z-10">
                        <div class="p-3 bg-indigo-900 text-amber-300 rounded-xl mb-3 w-fit border border-black shadow-sm">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <h3 class="font-extrabold text-slate-900 text-base mb-1">Forum Eksklusif Alumni</h3>
                        <p class="text-xs text-slate-700 font-semibold leading-relaxed">Berkomunikasi bebas dengan kawan alumni, berdiskusi proyek, dan nostalgia per angkatan.</p>
                    </div>
                </a>
                
                <a href="{{ route('alumni.kontribusi') }}" class="bg-white rounded-2xl p-5 border-2 border-slate-900 shadow-md hover:shadow-lg transition group relative overflow-hidden block">
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-rose-100 rounded-full group-hover:scale-150 transition duration-500 ease-in-out"></div>
                    <div class="relative z-10 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 border border-black shadow-sm">
                            <i class="fas fa-hand-holding-heart text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-slate-900 text-sm mb-1">Wadah Kontribusi Resmi pada almamater kita</h4>
                            <p class="text-xs text-slate-700 font-semibold leading-relaxed">Dukung pengembangan almamater melalui donasi pendidikan, rekening resmi yayasan, atau hibah peralatan.</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Teaser Widget: Perkembangan & Harapan Yayasan (Cuplikan Singkat) -->
            <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white rounded-2xl p-6 border-2 border-slate-900 shadow-md relative overflow-hidden space-y-3">
                <div class="flex items-center justify-between">
                    <span class="inline-block px-3 py-1 bg-amber-400 text-slate-900 text-[10px] font-black rounded-full border border-black uppercase tracking-wider">
                        <i class="fas fa-landmark mr-1"></i> SEJARAH & HARAPAN YAYASAN
                    </span>
                    <span class="text-xs text-indigo-200 font-bold"><i class="fas fa-book-open mr-1"></i> Cuplikan Singkat</span>
                </div>
                
                <h3 class="text-lg font-black text-white leading-snug">
                    Perjalanan Perkembangan, Tantangan & Arah Perjuangan Almamater
                </h3>
                
                <p class="text-xs text-indigo-100 font-medium leading-relaxed">
                    Didirikan untuk menghadirkan akses pendidikan terbaik di Kepulauan Nias, Yayasan Perguruan PEMBDA Nias kini menaungi SD, SMP, SMA, dan SMK Swasta Pembda Nias. Mari bersama-sama menghadapi tantangan modernisasi fasilitas & memperkuat sinergi alumni demi kemajuan generasi almamater...
                </p>

                <div class="pt-1 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <span class="text-[11px] text-amber-300 font-bold">📖 Baca sepintas atau lihat informasi selengkapnya:</span>
                    <a href="{{ route('alumni.sejarah') }}" class="bg-amber-400 hover:bg-amber-500 text-slate-900 font-black px-4 py-2 rounded-xl text-xs transition border border-black shadow-sm flex items-center justify-center gap-1.5 shrink-0">
                        Detail Selengkapnya →
                    </a>
                </div>
            </div>

            <!-- Job Board Widget -->
            <div class="bg-white rounded-2xl border-2 border-slate-900 shadow-md overflow-hidden">
                <div class="p-5 border-b-2 border-slate-900 flex items-center justify-between bg-slate-900 text-white">
                    <h3 class="font-extrabold text-white flex items-center gap-2 text-base">
                        <i class="fas fa-briefcase text-amber-400"></i> Lowongan Kerja Terbaru
                    </h3>
                    <a href="{{ route('alumni.jobs.index') }}" class="text-xs font-black text-amber-300 hover:text-white uppercase tracking-wider">Lihat Semua →</a>
                </div>
                <div class="p-5">
                    @if($latestJobs->count() > 0)
                        <div class="space-y-4">
                            @foreach($latestJobs as $job)
                            <a href="{{ route('alumni.jobs.index') }}" class="block p-4 border border-slate-300 rounded-xl hover:border-slate-900 hover:shadow-md transition group bg-slate-50/50">
                                <div class="flex justify-between items-start gap-3">
                                    <div>
                                        <h4 class="font-extrabold text-slate-900 text-base group-hover:text-indigo-700 transition">{{ $job->title }}</h4>
                                        <p class="text-xs font-bold text-indigo-900 uppercase tracking-wider mt-1"><i class="far fa-building mr-1"></i>{{ $job->company_name }}</p>
                                    </div>
                                    @if($job->salary_range)
                                        <span class="shrink-0 bg-emerald-100 text-emerald-900 border border-emerald-300 px-2.5 py-1 rounded-lg text-xs font-black"><i class="fas fa-coins mr-1 text-emerald-600"></i>{{ $job->salary_range }}</span>
                                    @endif
                                </div>
                            </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 text-slate-700 font-bold italic text-sm">
                            Belum ada lowongan pekerjaan terbaru.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Forum Discussions Widget -->
            <div class="bg-white rounded-2xl border-2 border-slate-900 shadow-md overflow-hidden">
                <div class="p-4 border-b-2 border-slate-900 flex justify-between items-center bg-slate-900 text-white rounded-t-2xl">
                    <h3 class="font-extrabold text-white flex items-center gap-2 text-sm">
                        <i class="fas fa-comments text-amber-400"></i> Topik Forum Alumni Terkini
                    </h3>
                    <a href="{{ route('alumni.forum.index') }}" class="text-xs font-black text-amber-300 hover:text-white uppercase tracking-wider">Lihat Semua &rarr;</a>
                </div>
                <div class="p-5">
                    @if($latestThreads->count() > 0)
                        <div class="space-y-4">
                            @foreach($latestThreads as $thread)
                            <a href="{{ route('alumni.forum.show', $thread->id) }}" class="flex items-start gap-3 p-3 hover:bg-indigo-50/50 rounded-xl transition group border border-slate-200">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($thread->user->name) }}&size=40&background=312e81&color=fff" class="w-10 h-10 rounded-full border-2 border-slate-900 shrink-0">
                                <div>
                                    <h4 class="text-sm font-extrabold text-slate-900 group-hover:text-indigo-700 transition line-clamp-1">{{ $thread->title }}</h4>
                                    <div class="flex items-center gap-2 mt-1 text-xs text-slate-700 font-bold">
                                        <span class="font-black text-indigo-900">{{ $thread->user->name }}</span>
                                        <span>&bull;</span>
                                        <span>{{ $thread->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 text-slate-700 font-bold italic text-sm">
                            Belum ada diskusi baru di forum.
                        </div>
                    @endif
                </div>
            </div>
            
        </div>

        <!-- Right Sidebar Column -->
        <div class="space-y-6">
            
            <!-- Alumni Identity Card -->
            <div class="bg-white rounded-2xl shadow-md border-2 border-slate-900 overflow-hidden relative group">
                <div class="h-20 bg-gradient-to-r from-slate-900 to-indigo-950"></div>
                <div class="px-6 pb-6 relative">
                    <img src="{{ $alumni->photo_url }}" class="w-20 h-20 rounded-2xl object-cover border-4 border-white shadow-lg mx-auto -mt-10 mb-4 transform group-hover:scale-105 transition">
                    <div class="text-center">
                        <h3 class="font-extrabold text-slate-900 text-lg">{{ $alumni->full_name }}</h3>
                        <p class="text-xs font-black text-indigo-900 tracking-widest uppercase mt-0.5">Angkatan {{ $alumni->graduation_year }}</p>
                        @if(isset($alumni->school) && $alumni->school)
                            <p class="text-xs font-bold text-slate-700 mt-1">{{ $alumni->school->name }}</p>
                        @endif
                    </div>
                    
                    <div class="mt-5 space-y-3 bg-slate-100/80 p-4 rounded-xl border border-slate-300">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-700 font-bold"><i class="fas fa-briefcase w-4 text-center mr-1 text-indigo-700"></i> Pekerjaan</span>
                            <span class="font-black text-slate-900 text-right">{{ $alumni->occupation ?: '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-700 font-bold"><i class="far fa-building w-4 text-center mr-1 text-indigo-700"></i> Instansi</span>
                            <span class="font-black text-slate-900 text-right line-clamp-1">{{ $alumni->company_name ?: '-' }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <a href="{{ route('profile.settings') }}" class="text-xs font-bold text-indigo-700 hover:text-indigo-900 transition"><i class="fas fa-user-edit mr-1"></i> Edit Profil Akun</a>
                    </div>
                </div>
            </div>

            <!-- Panduan Penggunaan Widget -->
            <div class="bg-white rounded-2xl shadow-md border-2 border-slate-900 p-5">
                <h3 class="font-extrabold text-slate-900 mb-4 flex items-center gap-2 text-sm uppercase tracking-wider">
                    <i class="fas fa-compass text-teal-600"></i> Panduan Fitur Alumni
                </h3>
                <ul class="space-y-4">
                    <li class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-slate-900 text-amber-300 flex items-center justify-center text-xs font-black shrink-0 mt-0.5">1</div>
                        <div>
                            <h4 class="text-xs font-extrabold text-slate-900">Perbarui Data Anda</h4>
                            <p class="text-xs text-slate-700 font-medium mt-0.5">Isi Tracer Study secara berkala agar sekolah tahu perkembangan karir Anda.</p>
                        </div>
                    </li>
                    <a href="{{ route('alumni.forum.index') }}" class="flex items-center justify-between group">
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-slate-900 text-amber-300 flex items-center justify-center text-xs font-black shrink-0 mt-0.5">2</div>
                            <div>
                                <h4 class="font-extrabold text-slate-900 text-xs group-hover:text-indigo-700 transition">Masuk Forum Alumni</h4>
                                <p class="text-xs text-slate-700 font-medium mt-0.5">Berkomunikasi dengan kawan almamater.</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-slate-700 text-xs"></i>
                    </a>
                    <li class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-slate-900 text-amber-300 flex items-center justify-center text-xs font-black shrink-0 mt-0.5">3</div>
                        <div>
                            <h4 class="text-xs font-extrabold text-slate-900">Cek Lowongan Kerja</h4>
                            <p class="text-xs text-slate-700 font-medium mt-0.5">Temukan atau bagikan info loker terbaru di Papan Lowongan Kerja.</p>
                        </div>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</div>
@endsection
