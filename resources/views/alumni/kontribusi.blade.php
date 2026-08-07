@extends('layouts.alumni')

@section('title', 'Kontribusi Alumni & Rekening Resmi Yayasan')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    
    <!-- Hero Banner (Clean Royal Indigo Style) -->
    <div class="relative overflow-hidden rounded-3xl p-8 sm:p-10 text-white shadow-xl border-2 border-slate-900" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%); color: #ffffff;">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-amber-400 text-slate-900 border-2 border-slate-900 shadow-md shrink-0 font-black text-3xl">
                    <i class="fas fa-hand-holding-heart"></i>
                </div>
                <div class="space-y-1">
                    <span class="inline-block px-3 py-1 bg-amber-400 text-slate-900 text-xs font-black rounded-full border border-slate-900 uppercase mb-1">
                        <i class="fas fa-heart text-rose-600 mr-1"></i> DUKUNGAN ALMAMATER
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">Wadah Kontribusi Alumni</h1>
                    <p class="text-xs sm:text-sm font-semibold max-w-2xl leading-relaxed" style="color: #e0e7ff;">
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
                <!-- Card Header -->
                <div class="p-6 text-white flex flex-wrap items-center justify-between gap-4 border-b-2 border-slate-900" style="background-color: #1e1b4b;">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-400 text-slate-900 flex items-center justify-center text-lg border border-slate-900 font-black">
                            <i class="fas fa-university"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-lg text-white">Rekening Resmi Yayasan</h3>
                            <p class="text-xs font-bold" style="color: #fcd34d;">Perguruan Pembda Nias - Rekening Sumbangan Pendidikan</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-emerald-400 text-slate-900 text-xs font-black border border-slate-900">
                        <i class="fas fa-shield-alt mr-1"></i> Terverifikasi Resmi
                    </span>
                </div>

                <div class="p-6 sm:p-8 space-y-6">
                    <p class="text-sm text-slate-900 font-extrabold leading-relaxed">
                        Seluruh kontribusi berupa donasi dana pendidikan, beasiswa siswa kurang mampu, dan pengembangan fasilitas laboratorium/praktik disalurkan langsung melalui rekening resmi pengurus yayasan di bawah ini:
                    </p>

                    <!-- Bank Account Card: Single Official Bank Mandiri -->
                    <div class="rounded-2xl p-6 border-2 border-slate-900 text-white shadow-lg space-y-4" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: #ffffff;">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="px-3.5 py-1.5 bg-amber-400 text-slate-900 font-black text-xs rounded-xl border border-slate-900 shadow-xs">
                                <i class="fas fa-university mr-1"></i> REKENING UTAMA YAYASAN — BANK MANDIRI
                            </span>
                            <span class="text-[10px] bg-emerald-400 text-slate-900 font-black px-2.5 py-1 rounded-full border border-slate-900">UTAMA / TERVERIFIKASI</span>
                        </div>
                        
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider" style="color: #fcd34d;">Atas Nama Rekening Resmi Yayasan:</p>
                            <p class="font-black text-white text-base sm:text-xl tracking-tight" style="color: #ffffff;">PENGURUS YAYASAN PERGURUAN PEMBDA NIAS</p>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl border-2 border-amber-400 shadow-sm" style="background-color: #0f172a;">
                            <div>
                                <span class="text-[11px] font-black uppercase block tracking-wider" style="color: #fcd34d;">Nomor Rekening Mandiri:</span>
                                <span class="font-mono font-black text-2xl sm:text-3xl tracking-widest block py-0.5" id="rekMandiri" style="color: #fde047;">1070010418269</span>
                            </div>
                            <button onclick="copyToClipboard('1070010418269', 'btnMandiri')" id="btnMandiri" class="px-5 py-2.5 bg-amber-400 hover:bg-amber-500 text-slate-900 font-black text-xs rounded-xl transition border-2 border-slate-900 shadow-md flex items-center justify-center gap-2 shrink-0">
                                <i class="far fa-copy"></i> Salin No. Rekening
                            </button>
                        </div>
                    </div>

                    <!-- Transparency Note -->
                    <div class="bg-amber-100 rounded-2xl p-4 border-2 border-slate-900 flex items-start gap-3.5 shadow-sm">
                        <i class="fas fa-info-circle text-slate-900 text-xl shrink-0 mt-0.5"></i>
                        <p class="text-xs text-slate-900 font-bold leading-relaxed">
                            <strong class="font-black text-slate-900">Transparansi Penyaluran:</strong> Setiap kontribusi donasi yang masuk dicatat secara resmi oleh pengurus bendahara yayasan dan digunakan khusus untuk alokasi beasiswa siswa berprestasi & perbaikan sarana belajar.
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
                    <div class="p-5 rounded-2xl bg-indigo-50/70 border-2 border-slate-900 shadow-sm space-y-2">
                        <div class="w-10 h-10 rounded-xl bg-indigo-900 text-amber-300 flex items-center justify-center font-black border border-slate-900">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h4 class="font-black text-slate-900 text-sm">Bagikan Lowongan Kerja & Magang</h4>
                        <p class="text-xs text-slate-800 font-semibold leading-relaxed">
                            Bantu lulusan baru atau adik-adik kelas dengan membagikan info lowongan pekerjaan di instansi Anda atau tempat penerimaan PKL.
                        </p>
                        <a href="{{ route('alumni.jobs.index') }}" class="inline-block text-xs font-black text-indigo-700 hover:underline pt-1">
                            Buka Papan Loker →
                        </a>
                    </div>

                    <!-- Option 2 -->
                    <div class="p-5 rounded-2xl bg-purple-50/70 border-2 border-slate-900 shadow-sm space-y-2">
                        <div class="w-10 h-10 rounded-xl bg-indigo-900 text-amber-300 flex items-center justify-center font-black border border-slate-900">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h4 class="font-black text-slate-900 text-sm">Alumni Mengajar & Sharing Session</h4>
                        <p class="text-xs text-slate-800 font-semibold leading-relaxed">
                            Bagikan pengalaman karir, tips dunia kerja, atau kuliah melalui sesi webinar/kuliah umum alumni secara offline maupun daring.
                        </p>
                        <a href="{{ route('alumni.forum.index') }}?category=ide_kreatif" class="inline-block text-xs font-black text-indigo-700 hover:underline pt-1">
                            Tulis Ide di Forum →
                        </a>
                    </div>

                    <!-- Option 3 -->
                    <div class="p-5 rounded-2xl bg-rose-50/70 border-2 border-slate-900 shadow-sm space-y-2">
                        <div class="w-10 h-10 rounded-xl bg-indigo-900 text-amber-300 flex items-center justify-center font-black border border-slate-900">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <h4 class="font-black text-slate-900 text-sm">Beasiswa & Perangkat Sekolah Siswa</h4>
                        <p class="text-xs text-slate-800 font-semibold leading-relaxed">
                            Dukungan Beasiswa Pendidikan serta bantuan <strong>Perangkat Sekolah</strong> bagi adik-adik yang sedang bersekolah (Pakaian seragam, Sepatu, Tas sekolah, & perlengkapan belajar).
                        </p>
                    </div>

                    <!-- Option 4 -->
                    <div class="p-5 rounded-2xl bg-amber-50/70 border-2 border-slate-900 shadow-sm space-y-2">
                        <div class="w-10 h-10 rounded-xl bg-indigo-900 text-amber-300 flex items-center justify-center font-black border border-slate-900">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h4 class="font-black text-slate-900 text-sm">Masukan & Gagasan Pengembangan</h4>
                        <p class="text-xs text-slate-800 font-semibold leading-relaxed">
                            Sumbangkan ide-ide inovatif untuk pengembangan kurikulum dan peningkatan mutu sekolah Perguruan Pembda Nias.
                        </p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Quick Contact & Links -->
        <div class="space-y-6">
            
            <!-- Quick Link to Forum -->
            <div class="rounded-3xl p-6 text-white shadow-md space-y-4 border-2 border-slate-900" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: #ffffff;">
                <div class="w-12 h-12 rounded-2xl bg-amber-400 text-slate-900 flex items-center justify-center text-xl font-black border border-slate-900">
                    <i class="fas fa-comments"></i>
                </div>
                <h4 class="font-black text-lg text-white">Ruang Forum & Diskusi</h4>
                <p class="text-xs font-semibold leading-relaxed" style="color: #e0e7ff;">
                    Sampaikan gagasan, berdiskusi per angkatan, atau berkonsultasi mengenai kontribusi alumni langsung di Forum Komunitas.
                </p>
                <a href="{{ route('alumni.forum.index') }}" class="block text-center py-3 bg-amber-400 hover:bg-amber-500 text-slate-900 font-black rounded-xl text-xs transition border-2 border-slate-900 shadow-md">
                    Buka Forum Alumni
                </a>
            </div>

            <!-- Official Foundation Contact Card -->
            <div class="bg-white rounded-3xl shadow-md border-2 border-slate-900 p-6 space-y-4">
                <h4 class="font-black text-slate-900 text-sm uppercase tracking-wider flex items-center gap-2 border-b-2 border-slate-200 pb-2">
                    <i class="fas fa-building text-indigo-700"></i> PENGURUS YAYASAN
                </h4>
                <div class="space-y-3 text-xs text-slate-900 font-bold">
                    <p class="flex items-start gap-2.5">
                        <i class="fas fa-map-marker-alt text-indigo-700 mt-0.5 text-sm"></i>
                        <span>Jl. Perguruan Pembda No. 1, Gunungsitoli, Nias, Sumatera Utara</span>
                    </p>
                    <p class="flex items-center gap-2.5">
                        <i class="fas fa-phone text-indigo-700 text-sm"></i>
                        <span>(0639) 21234 / Sekretariat IKA</span>
                    </p>
                    <p class="flex items-center gap-2.5">
                        <i class="fas fa-envelope text-indigo-700 text-sm"></i>
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
        var originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check text-slate-900 mr-1"></i> Tersalin!';
        btn.classList.remove('bg-amber-400');
        btn.classList.add('bg-emerald-400');
        setTimeout(function() {
            btn.innerHTML = originalText;
            btn.classList.remove('bg-emerald-400');
            btn.classList.add('bg-amber-400');
        }, 2000);
    });
}
</script>
@endsection
