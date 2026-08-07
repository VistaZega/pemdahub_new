@extends('layouts.alumni')

@section('title', 'Kontribusi Alumni & Rekening Yayasan')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    
    <!-- Hero Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-8 sm:p-10 text-white shadow-2xl border border-indigo-900/50">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute left-1/3 -top-10 w-48 h-48 bg-purple-600/15 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-500 via-rose-500 to-indigo-500 shadow-lg shadow-amber-500/20 ring-4 ring-white/10 shrink-0">
                    <i class="fas fa-hand-holding-heart text-white text-3xl"></i>
                </div>
                <div>
                    <span class="inline-block px-3 py-1 bg-amber-500/20 text-amber-200 text-xs font-bold rounded-full border border-amber-400/30 mb-2">
                        <i class="fas fa-heart mr-1 text-rose-400"></i> DUKUNGAN ALMAMATER
                    </span>
                    <h1 class="text-3xl font-extrabold tracking-tight text-white">Wadah Kontribusi Alumni</h1>
                    <p class="text-indigo-200/80 text-sm mt-1 max-w-2xl">
                        Dukung kemajuan pendidikan dan fasilitas belajar adik-adik di Perguruan Pembda Nias melalui kontribusi pemikiran, fasilitas, jaringan karir, maupun donasi pendidikan.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Rekening Resmi & Donasi -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Information Card Rekening Resmi Yayasan -->
            <div class="bg-white rounded-3xl shadow-md border-2 border-slate-900 overflow-hidden">
                <div class="bg-slate-900 p-6 text-white flex items-center justify-between border-b-2 border-slate-900">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-400 flex items-center justify-center text-slate-900 text-lg border border-black font-black">
                            <i class="fas fa-university"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-lg text-white">Rekening Resmi Yayasan</h3>
                            <p class="text-xs text-amber-300 font-bold">Perguruan Pembda Nias - Rekening Sumbangan Pendidikan</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-emerald-400 text-slate-900 text-xs font-black border border-black">
                        <i class="fas fa-shield-alt mr-1"></i> Terverifikasi Resmi
                    </span>
                </div>

                <div class="p-6 sm:p-8 space-y-6">
                    <p class="text-sm text-slate-900 font-bold leading-relaxed">
                        Seluruh kontribusi berupa donasi dana pendidikan, beasiswa siswa kurang mampu, dan pengembangan fasilitas laboratorium/praktik disalurkan langsung melalui rekening resmi pengurus yayasan di bawah ini:
                    </p>

                    <!-- Bank Account Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Account 1: Official Bank Mandiri from PembdaHUB -->
                        <div class="md:col-span-2 bg-gradient-to-r from-blue-900 to-indigo-950 rounded-2xl p-6 border-2 border-slate-900 text-white shadow-md relative overflow-hidden group">
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-3.5 py-1.5 bg-amber-400 text-slate-900 font-black text-xs rounded-xl border border-black shadow-xs">
                                    <i class="fas fa-university mr-1"></i> REKENING UTAMA — BANK MANDIRI
                                </span>
                                <span class="text-[10px] bg-emerald-400 text-slate-900 font-black px-2.5 py-1 rounded-full border border-black">UTAMA / TERVERIFIKASI</span>
                            </div>
                            <p class="text-xs text-indigo-200 font-extrabold uppercase tracking-wider">Atas Nama Rekening Resmi Yayasan:</p>
                            <p class="font-black text-white text-base mb-3">PENGURUS YAYASAN PERGURUAN PEMBDA NIAS</p>
                            
                            <div class="flex items-center justify-between bg-white/15 backdrop-blur-md p-4 rounded-xl border-2 border-white/30">
                                <div>
                                    <span class="text-[10px] text-amber-300 font-bold block uppercase">Nomor Rekening Mandiri:</span>
                                    <span class="font-mono font-black text-amber-300 text-xl tracking-widest" id="rekMandiri">1070010418269</span>
                                </div>
                                <button onclick="copyToClipboard('1070010418269', 'btnMandiri')" id="btnMandiri" class="px-4 py-2 bg-amber-400 hover:bg-amber-500 text-slate-900 font-black text-xs rounded-xl transition border border-black shadow-md flex items-center gap-1.5">
                                    <i class="far fa-copy"></i> Salin No. Rekening
                                </button>
                            </div>
                        </div>

                        <!-- Account 2: Bank Sumut -->
                        <div class="bg-slate-50 rounded-2xl p-5 border-2 border-slate-900 hover:border-indigo-600 transition relative group shadow-sm">
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-3 py-1 bg-blue-700 text-white font-black text-xs rounded-lg border border-black">BANK SUMUT</span>
                                <i class="fas fa-credit-card text-slate-900 text-base"></i>
                            </div>
                            <p class="text-xs text-slate-700 font-extrabold uppercase tracking-wider">Atas Nama Rekening:</p>
                            <p class="font-black text-slate-900 text-sm mb-3">YAYASAN PERGURUAN PEMBDA NIAS</p>
                            
                            <div class="flex items-center justify-between bg-white p-3 rounded-xl border-2 border-slate-900">
                                <span class="font-mono font-black text-indigo-950 text-base tracking-wider" id="rek1">100.02.04.012345-6</span>
                                <button onclick="copyToClipboard('100.02.04.012345-6', 'btn1')" id="btn1" class="px-3 py-1.5 bg-amber-400 hover:bg-amber-500 text-slate-900 font-black text-xs rounded-lg transition border border-black shadow-sm">
                                    <i class="far fa-copy mr-1"></i> Salin
                                </button>
                            </div>
                        </div>

                        <!-- Account 3: Bank BRI -->
                        <div class="bg-slate-50 rounded-2xl p-5 border-2 border-slate-900 hover:border-indigo-600 transition relative group shadow-sm">
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-3 py-1 bg-blue-900 text-white font-black text-xs rounded-lg border border-black">BANK BRI</span>
                                <i class="fas fa-credit-card text-slate-900 text-base"></i>
                            </div>
                            <p class="text-xs text-slate-700 font-extrabold uppercase tracking-wider">Atas Nama Rekening:</p>
                            <p class="font-black text-slate-900 text-sm mb-3">YAYASAN PERGURUAN PEMBDA NIAS</p>
                            
                            <div class="flex items-center justify-between bg-white p-3 rounded-xl border-2 border-slate-900">
                                <span class="font-mono font-black text-indigo-950 text-base tracking-wider" id="rek2">0054-01-002345-53-8</span>
                                <button onclick="copyToClipboard('0054-01-002345-53-8', 'btn2')" id="btn2" class="px-3 py-1.5 bg-amber-400 hover:bg-amber-500 text-slate-900 font-black text-xs rounded-lg transition border border-black shadow-sm">
                                    <i class="far fa-copy mr-1"></i> Salin
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Transparency Note -->
                    <div class="bg-amber-100/90 rounded-2xl p-4 border-2 border-slate-900 flex items-start gap-3.5 shadow-sm">
                        <i class="fas fa-info-circle text-amber-700 text-xl shrink-0 mt-0.5"></i>
                        <p class="text-xs text-slate-900 font-bold leading-relaxed">
                            <strong class="font-black text-indigo-950">Transparansi Penyaluran:</strong> Setiap kontribusi donasi yang masuk dicatat secara resmi oleh pengurus bendahara yayasan dan digunakan khusus untuk alokasi beasiswa siswa berprestasi & perbaikan sarana belajar.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Program Bentuk Kontribusi Lainnya -->
            <div class="bg-white rounded-3xl shadow-md border-2 border-slate-900 p-6 sm:p-8 space-y-6">
                <h3 class="text-xl font-black text-slate-900 flex items-center gap-2">
                    <i class="fas fa-hands-helping text-indigo-700"></i> Bentuk Kontribusi Alumni Lainnya
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Option 1 -->
                    <div class="p-5 rounded-2xl bg-indigo-50/50 border border-indigo-100 space-y-2">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 text-sm">Bagikan Lowongan Kerja & Magang</h4>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Bantu lulusan baru atau adik-adik kelas dengan membagikan info lowongan pekerjaan di instansi Anda atau tempat penerimaan PKL.
                        </p>
                        <a href="{{ route('alumni.jobs.index') }}" class="inline-block text-xs font-bold text-indigo-600 hover:underline pt-1">
                            Buka Papan Loker →
                        </a>
                    </div>

                    <!-- Option 2 -->
                    <div class="p-5 rounded-2xl bg-purple-50/50 border border-purple-100 space-y-2">
                        <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center font-bold">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 text-sm">Alumni Mengajar & Sharing Session</h4>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Bagikan pengalaman karir, tips dunia kerja, atau kuliah melalui sesi webinar/kuliah umum alumni secara offline maupun daring.
                        </p>
                        <a href="{{ route('alumni.forum.index') }}?category=ide_kreatif" class="inline-block text-xs font-bold text-purple-600 hover:underline pt-1">
                            Tulis Ide di Forum →
                        </a>
                    </div>

                    <!-- Option 3 -->
                    <div class="p-5 rounded-2xl bg-rose-50/70 border border-rose-200 space-y-2">
                        <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center font-bold">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <h4 class="font-extrabold text-slate-900 text-sm">Beasiswa & Perangkat Sekolah Siswa</h4>
                        <p class="text-xs text-slate-700 font-medium leading-relaxed">
                            Dukungan Beasiswa Pendidikan serta bantuan <strong>Perangkat Sekolah</strong> bagi adik-adik yang sedang bersekolah (Pakaian seragam, Sepatu, Tas sekolah, & perlengkapan belajar).
                        </p>
                    </div>

                    <!-- Option 4 -->
                    <div class="p-5 rounded-2xl bg-amber-50/50 border border-amber-100 space-y-2">
                        <div class="w-10 h-10 rounded-xl bg-amber-600 text-white flex items-center justify-center font-bold">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 text-sm">Masukan & Gagasan Pengembangan</h4>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Sumbangkan ide-ide inovatif untuk pengembangan kurikulum dan peningkatan mutu sekolah Perguruan Pembda Nias.
                        </p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Quick Contact & Links -->
        <div class="space-y-6">
            
            <!-- Quick Link to Forum -->
            <div class="bg-gradient-to-br from-indigo-900 to-slate-900 rounded-3xl p-6 text-white shadow-lg space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-amber-300 text-xl">
                    <i class="fas fa-comments"></i>
                </div>
                <h4 class="font-extrabold text-lg">Ruang Forum & Diskusi</h4>
                <p class="text-xs text-indigo-200 leading-relaxed">
                    Sampaikan gagasan, berdiskusi per angkatan, atau berkonsultasi mengenai kontribusi alumni langsung di Forum Komunitas.
                </p>
                <a href="{{ route('alumni.forum.index') }}" class="block text-center py-3 bg-white text-indigo-900 hover:bg-indigo-50 font-bold rounded-xl text-xs transition shadow-md">
                    Buka Forum Alumni
                </a>
            </div>

            <!-- Official Foundation Contact Card -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6 space-y-4">
                <h4 class="font-bold text-slate-900 text-sm uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-building text-indigo-600"></i> Pengurus Yayasan
                </h4>
                <div class="space-y-3 text-xs text-slate-600">
                    <p class="flex items-start gap-2">
                        <i class="fas fa-map-marker-alt text-slate-400 mt-0.5"></i>
                        <span>Jl. Perguruan Pembda No. 1, Gunungsitoli, Nias, Sumatera Utara</span>
                    </p>
                    <p class="flex items-center gap-2">
                        <i class="fas fa-phone text-slate-400"></i>
                        <span>(0639) 21234 / Sekretariat IKA</span>
                    </p>
                    <p class="flex items-center gap-2">
                        <i class="fas fa-envelope text-slate-400"></i>
                        <span>yayasan@perguruanpembda.com</span>
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function copyToClipboard(text, btnId) {
    navigator.clipboard.writeText(text).then(function() {
        var btn = document.getElementById(btnId);
        var original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check mr-1"></i> Tersalin!';
        btn.classList.remove('bg-indigo-50', 'text-indigo-600');
        btn.classList.add('bg-emerald-600', 'text-white');
        setTimeout(function() {
            btn.innerHTML = original;
            btn.classList.remove('bg-emerald-600', 'text-white');
            btn.classList.add('bg-indigo-50', 'text-indigo-600');
        }, 2000);
    });
}
</script>
@endsection
