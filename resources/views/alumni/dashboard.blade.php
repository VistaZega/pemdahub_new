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
            <h2 class="text-3xl sm:text-4xl font-extrabold mb-3 leading-tight">Selamat Datang Kembali,<br><span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-200 to-amber-400">{{ $alumni->full_name }}!</span></h2>
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
                <a href="{{ route('alumni.forum.index') }}" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition group relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-blue-50 rounded-full group-hover:scale-150 transition duration-500 ease-in-out"></div>
                    <div class="relative z-10">
                        <div class="p-3 bg-indigo-50 rounded-lg text-indigo-600 mb-3 w-fit">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 mb-1">Forum Eksklusif</h3>
                        <p class="text-xs text-gray-500 leading-relaxed">Berkomunikasi bebas dengan alumni lain, berdiskusi proyek, dan nostalgia di forum alumni unit sekolah Anda.</p>
                    </div>
                </a>
                
                <a href="{{ route('alumni.kontribusi') }}" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition group relative overflow-hidden block">
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-rose-50 rounded-full group-hover:scale-150 transition duration-500 ease-in-out"></div>
                    <div class="relative z-10 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                            <i class="fas fa-hand-holding-heart text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 mb-1">Wadah Kontribusi Resmi pada almamater kita</h4>
                            <p class="text-xs text-gray-500 leading-relaxed">Dukung pengembangan almamater melalui donasi pendidikan, rekening resmi yayasan, atau hibah peralatan.</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Job Board Widget -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-briefcase text-amber-500"></i> Lowongan Kerja Terbaru
                    </h3>
                    <a href="{{ route('alumni.jobs.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Lihat Semua →</a>
                </div>
                <div class="p-5">
                    @if($latestJobs->count() > 0)
                        <div class="space-y-4">
                            @foreach($latestJobs as $job)
                            <a href="{{ route('alumni.jobs.index') }}" class="block p-4 border border-gray-100 rounded-xl hover:border-indigo-300 hover:shadow-md transition group">
                                <div class="flex justify-between items-start gap-3">
                                    <div>
                                        <h4 class="font-bold text-gray-900 group-hover:text-indigo-600 transition">{{ $job->title }}</h4>
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-1"><i class="far fa-building mr-1"></i>{{ $job->company_name }}</p>
                                    </div>
                                    @if($job->salary_range)
                                        <span class="shrink-0 bg-emerald-50 text-emerald-700 px-2 py-1 rounded text-[10px] font-bold"><i class="fas fa-coins mr-1"></i>{{ $job->salary_range }}</span>
                                    @endif
                                </div>
                            </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 text-gray-400 italic text-sm">
                            Belum ada lowongan pekerjaan terbaru.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Forum Discussions Widget -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-4 border-b flex justify-between items-center bg-gray-50 rounded-t-2xl">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2 text-sm">
                        <i class="fas fa-users text-indigo-500"></i> Topik Forum Alumni Terkini
                    </h3>
                    <a href="{{ route('alumni.forum.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">Lihat Semua &rarr;</a>
                </div>
                <div class="p-5">
                    @if($latestThreads->count() > 0)
                        <div class="space-y-4">
                            @foreach($latestThreads as $thread)
                            <a href="{{ route('forum.show', $thread->id) }}" class="flex items-start gap-3 p-3 hover:bg-gray-50 rounded-xl transition group">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($thread->user->name) }}&size=40&background=random" class="w-10 h-10 rounded-full border border-gray-200 shrink-0">
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition line-clamp-1">{{ $thread->title }}</h4>
                                    <div class="flex items-center gap-2 mt-1 text-[10px] text-gray-500">
                                        <span class="font-semibold">{{ $thread->user->name }}</span>
                                        <span>&bull;</span>
                                        <span>{{ $thread->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 text-gray-400 italic text-sm">
                            Belum ada diskusi baru di forum.
                        </div>
                    @endif
                </div>
            </div>
            
        </div>

        <!-- Right Sidebar Column -->
        <div class="space-y-6">
            
            <!-- Alumni Identity Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative group">
                <div class="h-20 bg-gradient-to-r from-slate-800 to-indigo-900"></div>
                <div class="px-6 pb-6 relative">
                    <img src="{{ $alumni->photo_url }}" class="w-20 h-20 rounded-xl object-cover border-4 border-white shadow-lg mx-auto -mt-10 mb-4 transform group-hover:scale-105 transition">
                    <div class="text-center">
                        <h3 class="font-bold text-gray-900 text-lg">{{ $alumni->full_name }}</h3>
                        <p class="text-xs font-bold text-indigo-600 tracking-widest uppercase mt-0.5">Angkatan {{ $alumni->graduation_year }}</p>
                        @if($alumni->school)
                            <p class="text-[10px] text-gray-400 mt-1">{{ $alumni->school->name }}</p>
                        @endif
                    </div>
                    
                    <div class="mt-5 space-y-3 bg-gray-50 p-4 rounded-xl">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-500 font-medium"><i class="fas fa-briefcase w-4 text-center mr-1"></i> Pekerjaan</span>
                            <span class="font-bold text-gray-800 text-right">{{ $alumni->occupation ?: '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-500 font-medium"><i class="far fa-building w-4 text-center mr-1"></i> Instansi</span>
                            <span class="font-bold text-gray-800 text-right line-clamp-1">{{ $alumni->company_name ?: '-' }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <a href="{{ route('profile.settings') }}" class="text-xs font-bold text-gray-500 hover:text-indigo-600 transition"><i class="fas fa-user-edit mr-1"></i> Edit Profil</a>
                    </div>
                </div>
            </div>

            <!-- Panduan Penggunaan Widget -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2 text-sm uppercase tracking-wider">
                    <i class="fas fa-compass text-teal-500"></i> Panduan Fitur
                </h3>
                <ul class="space-y-4">
                    <li class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">1</div>
                        <div>
                            <h4 class="text-xs font-bold text-gray-900">Perbarui Data Anda</h4>
                            <p class="text-[10px] text-gray-500 mt-0.5">Isi Tracer Study secara berkala agar sekolah tahu perkembangan karir Anda.</p>
                        </div>
                    </li>
                    <a href="{{ route('alumni.forum.index') }}" class="flex items-center justify-between">
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">2</div>
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm">Masuk Forum Alumni</h4>
                                <p class="text-[10px] text-gray-500 mt-0.5">Berkomunikasi dengan rekan-rekan satu almamater.</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                    </a>
                    <li class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">3</div>
                        <div>
                            <h4 class="text-xs font-bold text-gray-900">Cek Lowongan</h4>
                            <p class="text-[10px] text-gray-500 mt-0.5">Temukan atau bagikan info loker terbaru di fitur Papan Lowongan Kerja.</p>
                        </div>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</div>
@endsection
