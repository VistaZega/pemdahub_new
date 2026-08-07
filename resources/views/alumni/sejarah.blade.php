@extends('layouts.alumni')

@section('title', 'Perkembangan & Rekam Jejak Yayasan Perguruan PEMBDA Nias')

@section('content')
<div class="max-w-5xl mx-auto space-y-8 py-4">
    
    <!-- Hero Banner (LMS Neo-Brutalis Style) -->
    <div class="relative bg-slate-900 rounded-3xl p-6 sm:p-10 text-white shadow-xl border-2 border-slate-900 overflow-hidden">
        <div class="relative z-10 space-y-3">
            <span class="inline-block px-3 py-1 bg-amber-400 text-slate-900 text-xs font-black rounded-full border border-black uppercase">
                <i class="fas fa-landmark mr-1"></i> SEJARAH & REKAM JEJAK YAYASAN
            </span>
            <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight leading-tight">
                Perkembangan, Tantangan & Harapan Almamater
            </h1>
            <p class="text-indigo-200 text-xs sm:text-base font-medium max-w-3xl leading-relaxed">
                Kilas balik perjalanan panjang Yayasan Perguruan PEMBDA Nias dalam mencetak generasi unggul di Kepulauan Nias, serta arah perjuangan masa depan bersama alumni.
            </p>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="bg-white rounded-3xl border-2 border-slate-900 shadow-md p-6 sm:p-10 space-y-8">
        
        <!-- Section 1: Awal Pendirian s/d Perkembangan Saat Ini -->
        <div class="space-y-4 border-b-2 border-slate-200 pb-8">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-900 text-amber-300 flex items-center justify-center font-black border border-black shrink-0">
                    <i class="fas fa-history text-lg"></i>
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900">1. Awal Pendirian s/d Perkembangan Saat Ini</h2>
            </div>
            
            <p class="text-sm text-slate-800 font-medium leading-relaxed">
                <strong>Yayasan Perguruan PEMBDA Nias</strong> didirikan dengan semangat mulia untuk menghadirkan akses pendidikan berkualitas dan terjangkau bagi masyarakat di Kepulauan Nias. Dimulai dari satu unit sekolah dasar, tekad pengurus dan dukungan para guru berhasil menumbuhkan yayasan ini hingga menaungi unit pendidikan dari jenjang <strong>SD, SMP, SMA, hingga SMK Swasta Pembda Nias</strong>.
            </p>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div class="bg-slate-50 p-4 rounded-2xl border-2 border-slate-900 text-center">
                    <span class="block text-2xl font-black text-indigo-900">4+ Unit</span>
                    <span class="text-xs font-bold text-slate-700">Jenjang Pendidikan (SD, SMP, SMA, SMK)</span>
                </div>
                <div class="bg-slate-50 p-4 rounded-2xl border-2 border-slate-900 text-center">
                    <span class="block text-2xl font-black text-amber-600">Ribuan</span>
                    <span class="text-xs font-bold text-slate-700">Alumni Tersebar di Indonesia & Mancanegara</span>
                </div>
                <div class="bg-slate-50 p-4 rounded-2xl border-2 border-slate-900 text-center">
                    <span class="block text-2xl font-black text-emerald-600">PembdaHUB</span>
                    <span class="text-xs font-bold text-slate-700">Digitalisasi Terpadu Akademik & Alumni</span>
                </div>
            </div>
        </div>

        <!-- Section 2: Tantangan Pendidikan di Era Modern -->
        <div class="space-y-4 border-b-2 border-slate-200 pb-8">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-rose-600 text-white flex items-center justify-center font-black border border-black shrink-0">
                    <i class="fas fa-exclamation-triangle text-lg"></i>
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900">2. Tantangan Pendidikan yang Dihadapi</h2>
            </div>
            
            <p class="text-sm text-slate-800 font-medium leading-relaxed">
                Di tengah pesatnya perkembangan teknologi dan industri modern, almamater menghadapi tantangan besar untuk terus relevan:
            </p>

            <ul class="space-y-3 text-xs sm:text-sm text-slate-900 font-semibold">
                <li class="flex items-start gap-3 bg-amber-50 p-3.5 rounded-xl border border-slate-900">
                    <i class="fas fa-laptop-code text-indigo-700 text-base shrink-0 mt-0.5"></i>
                    <span><strong>Modernisasi Peralatan Praktik:</strong> Kebutuhan memperbarui laboratorium komputer, fasilitas bengkel praktik kejuruan, dan perpustakaan digital sesuai standar DUDI modern.</span>
                </li>
                <li class="flex items-start gap-3 bg-amber-50 p-3.5 rounded-xl border border-slate-900">
                    <i class="fas fa-user-graduate text-emerald-700 text-base shrink-0 mt-0.5"></i>
                    <span><strong>Dukungan Beasiswa Kurang Mampu:</strong> Memastikan tidak ada siswa berprestasi yang terputus sekolahnya karena kendala ekonomi keluarga.</span>
                </li>
                <li class="flex items-start gap-3 bg-amber-50 p-3.5 rounded-xl border border-slate-900">
                    <i class="fas fa-network-wired text-amber-700 text-base shrink-0 mt-0.5"></i>
                    <span><strong>Kemitraan & Penyerapan Karir:</strong> Membuka akses jaringan kerja yang lebih luas bagi para lulusan baru agar cepat terserap di dunia kerja atau perguruan tinggi ternama.</span>
                </li>
            </ul>
        </div>

        <!-- Section 3: Harapan & Peran Serta Alumni -->
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black border border-black shrink-0">
                    <i class="fas fa-seedling text-lg"></i>
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900">3. Harapan & Peran Serta Alumni (IKA)</h2>
            </div>
            
            <p class="text-sm text-slate-800 font-medium leading-relaxed">
                Pengurus Yayasan meyakini bahwa **kekuatan terbesar sebuah lembaga pendidikan berada pada alumni-alumninya**. Melalui Portal IKA Perguruan Pembda Nias, kami mengharapkan sinergi dari seluruh kawan alumni di manapun berada:
            </p>

            <div class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white p-6 rounded-2xl border-2 border-slate-900 space-y-3">
                <div class="flex items-center gap-2 text-amber-300 font-black text-sm">
                    <i class="fas fa-heart text-pink-400"></i> Mari Bergandengan Tangan Membangun Almamater:
                </div>
                <p class="text-xs sm:text-sm text-indigo-100 font-normal leading-relaxed">
                    Baik dalam bentuk sumbangsih pemikiran di <strong>Ruang Forum Alumni</strong>, berbagi peluang kerja di <strong>Job Board</strong>, memberikan motivasi kepada adik-adik siswa, maupun donasi melalui <strong>Wadah Kontribusi Resmi</strong>. Peran serta Anda akan menjadi obor penerang bagi masa depan generasi muda Kepulauan Nias.
                </p>
            </div>
        </div>

        <div class="pt-4 flex justify-between items-center border-t border-slate-200">
            <a href="{{ route('alumni.dashboard') }}" class="inline-flex items-center gap-2 bg-slate-200 hover:bg-slate-300 text-slate-900 font-black px-5 py-2.5 rounded-xl text-xs transition border border-slate-900">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
            <a href="{{ route('alumni.kontribusi') }}" class="inline-flex items-center gap-2 bg-amber-400 hover:bg-amber-500 text-slate-900 font-black px-5 py-2.5 rounded-xl text-xs transition border border-black shadow-sm">
                <i class="fas fa-hand-holding-heart"></i> Kontribusi untuk Almamater →
            </a>
        </div>
    </div>
</div>
@endsection
