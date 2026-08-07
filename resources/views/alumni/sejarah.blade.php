@extends('layouts.alumni')

@section('title', 'Pesan Ketua Yayasan & Sejarah Pembda Nias')

@section('content')
<div class="max-w-5xl mx-auto space-y-8 py-4">
    
    <!-- Hero Banner (LMS Neo-Brutalis Style) -->
    <div class="relative bg-slate-900 rounded-3xl p-6 sm:p-10 text-white shadow-xl border-2 border-slate-900 overflow-hidden">
        <div class="relative z-10 space-y-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-block px-3 py-1 bg-amber-400 text-slate-900 text-xs font-black rounded-full border border-black uppercase">
                    <i class="fas fa-landmark mr-1"></i> SEJARAH & PESAN KETUA YAYASAN
                </span>
                <span class="inline-block px-3 py-1 bg-indigo-900 text-amber-300 text-xs font-black rounded-full border border-amber-400/50">
                    <i class="fas fa-history mr-1"></i> SEJAK TAHUN 1970 (50+ TAHUN)
                </span>
            </div>
            <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight leading-tight">
                Dari Pembda untuk Nias,<br>Dari Alumni untuk Masa Depan
            </h1>
            <p class="text-indigo-200 text-xs sm:text-base font-medium max-w-3xl leading-relaxed">
                Sambutan dan pesan resmi Ketua Yayasan Perguruan Pembda Nias mengenai perjalanan panjang almamater, tantangan masa depan, dan ajaran kebersamaan alumni.
            </p>
        </div>
    </div>

    <!-- Live Quick Stats Badge Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border-2 border-slate-900 shadow-md text-center">
            <span class="block text-3xl font-black text-indigo-900">1970</span>
            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Tahun Berdiri</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border-2 border-slate-900 shadow-md text-center">
            <span class="block text-3xl font-black text-amber-600">3 Unit</span>
            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Sekolah Aktif</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border-2 border-slate-900 shadow-md text-center">
            <span class="block text-3xl font-black text-emerald-600">~1.700</span>
            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Peserta Didik</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border-2 border-slate-900 shadow-md text-center">
            <span class="block text-3xl font-black text-purple-600">~130</span>
            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Guru & Pegawai</span>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="bg-white rounded-3xl border-2 border-slate-900 shadow-md p-6 sm:p-10 space-y-8">
        
        <!-- Header Pesan -->
        <div class="border-b-2 border-slate-200 pb-6">
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">PESAN KETUA YAYASAN</h2>
            <p class="text-sm font-extrabold text-amber-600 uppercase tracking-widest mt-1">Yayasan Perguruan Pembda Nias</p>
            <p class="text-xs font-bold text-slate-700 italic mt-2">"Dari Pembda untuk Nias, Dari Alumni untuk Masa Depan"</p>
        </div>

        <!-- Salam & Sejarah Awali -->
        <div class="space-y-4 text-sm sm:text-base text-slate-900 font-medium leading-relaxed">
            <p class="font-extrabold text-slate-900 text-lg">Salam sejahtera untuk kita semua,</p>

            <p>
                Yayasan Perguruan Pembda lahir dari sebuah kerinduan dan cita-cita luhur beberapa keluarga pendiri (<em>founding fathers</em>) pada tahun <strong>1970</strong>, yaitu membuka kesempatan seluas-luasnya bagi masyarakat Nias untuk memperoleh pendidikan yang baik dan bermutu.
            </p>

            <p>
                Perjalanan itu dimulai dengan berdirinya <strong>Sekolah Teknik Menengah (STM)</strong> sebagai unit pendidikan pertama. Seiring meningkatnya kepercayaan masyarakat dan berkembangnya kebutuhan pendidikan, Yayasan Perguruan Pembda kemudian membuka berbagai unit pendidikan, antara lain <strong>SMAS Pembda 1 Gunungsitoli, SMPS Pembda 1 Gunungsitoli (Kelas Siang), SMAS Pembda 2 Gunungsitoli (Kelas Siang), dan SMPS Pembda 2 Gunungsitoli</strong>.
            </p>

            <p>
                Perubahan zaman dan kebijakan pendidikan mendorong kita untuk terus beradaptasi. Sejalan dengan penerapan <strong>lima hari sekolah (Five-Day School)</strong>, secara bertahap beberapa unit sekolah kelas siang kemudian diintegrasikan ke dalam unit sekolah pagi. Saat ini, Yayasan Perguruan Pembda Nias mengelola <strong>tiga unit sekolah aktif</strong>, yaitu:
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 py-2">
                <div class="bg-indigo-50/80 p-4 rounded-2xl border-2 border-slate-900">
                    <div class="w-8 h-8 rounded-xl bg-indigo-900 text-amber-300 flex items-center justify-center font-black text-xs mb-2 border border-black">1</div>
                    <h4 class="font-black text-slate-900 text-sm">SMK Swasta Pembda Nias</h4>
                    <p class="text-xs text-slate-700 font-bold mt-1">Pendidikan Kejuruan & Vokasi Industri</p>
                </div>
                <div class="bg-indigo-50/80 p-4 rounded-2xl border-2 border-slate-900">
                    <div class="w-8 h-8 rounded-xl bg-indigo-900 text-amber-300 flex items-center justify-center font-black text-xs mb-2 border border-black">2</div>
                    <h4 class="font-black text-slate-900 text-sm">SMAS Pembda 1 Gunungsitoli</h4>
                    <p class="text-xs text-slate-700 font-bold mt-1">Pendidikan Menengah Atas Umum</p>
                </div>
                <div class="bg-indigo-50/80 p-4 rounded-2xl border-2 border-slate-900">
                    <div class="w-8 h-8 rounded-xl bg-indigo-900 text-amber-300 flex items-center justify-center font-black text-xs mb-2 border border-black">3</div>
                    <h4 class="font-black text-slate-900 text-sm">SMPS Pembda 2 Gunungsitoli</h4>
                    <p class="text-xs text-slate-700 font-bold mt-1">Pendidikan Menengah Pertama</p>
                </div>
            </div>

            <p>
                Ketiga unit tersebut saat ini melayani sekitar <strong>1.700 peserta didik</strong>, didukung oleh sekitar <strong>130 orang Guru dan Pegawai</strong>.
            </p>
        </div>

        <!-- Section Visi & Motto -->
        <div class="bg-gradient-to-br from-slate-900 to-indigo-950 text-white rounded-3xl p-6 sm:p-8 border-2 border-slate-900 space-y-4 shadow-lg">
            <span class="inline-block px-3 py-1 bg-amber-400 text-slate-900 text-xs font-black rounded-full border border-black uppercase">
                MEMBANGUN MANUSIA, MENJAGA KARAKTER
            </span>
            
            <p class="text-xs sm:text-sm text-indigo-100 font-medium leading-relaxed">
                Lebih dari setengah abad perjalanan pendidikan telah memberikan kepada kita sebuah keyakinan: <strong>pendidikan bukan sekadar membangun kecerdasan, tetapi membentuk manusia yang berkualitas dan berkarakter.</strong>
            </p>

            <div class="bg-white/10 p-5 rounded-2xl border border-white/20 text-center space-y-2">
                <p class="text-xs font-bold text-amber-300 uppercase tracking-widest">VISI KAMI</p>
                <blockquote class="text-base sm:text-xl font-black text-white italic">
                    “Menghasilkan Sumber Daya Manusia yang Berkualitas dan Memiliki Karakter yang Baik.”
                </blockquote>
            </div>

            <p class="text-xs sm:text-sm text-indigo-100 font-medium leading-relaxed">
                Dengan pengalaman lebih dari <strong>50 tahun di dunia pendidikan</strong>, kita terus menyempurnakan strategi, tata kelola, budaya pendidikan, dan model pembelajaran agar Pembda mampu menjawab tuntutan zaman dan kebutuhan dunia kerja serta masyarakat. Kita tidak ingin sekadar mempertahankan apa yang sudah ada. <strong>Kita ingin terus bertumbuh, beradaptasi, berinnovasi, dan menjadi lebih baik.</strong>
            </p>

            <div class="bg-amber-400 text-slate-900 p-5 rounded-2xl border-2 border-black text-center space-y-1 shadow-md">
                <p class="text-xs font-black uppercase tracking-widest text-slate-800">MOTTO PERJUANGAN PEMBDA</p>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-950 tracking-wider">“KEEP MOVING FORWARD”</h3>
                <p class="text-xs font-bold text-slate-900 max-w-xl mx-auto leading-relaxed mt-1">
                    Bagi Pembda, <em>Keep Moving Forward</em> bukan sekadar slogan. Ia adalah semangat untuk tidak menyerah, keberanian untuk berubah, kemauan untuk terus belajar dan berinovasi, serta tekad untuk selalu melangkah lebih maju.
                </p>
            </div>
        </div>

        <!-- Section Alumni Adalah Bagian dari Perjalanan Pembda -->
        <div class="space-y-4 text-sm sm:text-base text-slate-900 font-medium leading-relaxed">
            <h3 class="text-xl sm:text-2xl font-black text-slate-900 border-b-2 border-slate-200 pb-2">
                Alumni adalah Bagian dari Perjalanan Pembda
            </h3>

            <p>
                Selama perjalanan panjang tersebut, Pembda telah melahirkan <strong>puluhan ribu alumni</strong> yang kini hadir di berbagai daerah, bekerja, berkarya, memimpin, berwirausaha, dan mengabdi dalam berbagai bidang serta profesi di seluruh Indonesia. Keberhasilan para alumni merupakan bagian penting dari keberhasilan Pembda.
            </p>

            <p>
                Karena itu, <strong>Portal Alumni Yayasan Perguruan Pembda</strong> bukan sekadar ruang untuk mengenang masa lalu. Portal ini kita bangun sebagai <strong>jembatan antara masa lalu, masa kini, dan masa depan</strong>; sebagai ruang untuk mempererat silaturahmi, berbagi informasi, membangun jejaring, membuka peluang, dan bersama-sama memberikan kontribusi nyata bagi generasi Pembda berikutnya.
            </p>
        </div>

        <!-- Section 3 Tantangan dan Harapan Kita Bersama -->
        <div class="space-y-5">
            <h3 class="text-xl sm:text-2xl font-black text-slate-900 border-b-2 border-slate-200 pb-2">
                Tantangan dan Harapan Kita Bersama
            </h3>

            <p class="text-sm text-slate-800 font-medium leading-relaxed">
                Perjalanan ke depan tentu tidak semakin ringan. Perkembangan teknologi, perubahan dunia kerja, tuntutan kualitas pendidikan, serta kebutuhan masyarakat menghadirkan tantangan baru yang harus kita jawab bersama. Untuk itu, kami membuka ruang seluas-luasnya bagi seluruh alumni untuk berkontribusi melalui:
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white p-5 rounded-2xl border-2 border-slate-900 shadow-md space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-amber-400 flex items-center justify-center font-black text-lg border border-black">1</div>
                    <h4 class="font-black text-slate-900 text-base">Pengembangan Sarana & Prasarana</h4>
                    <p class="text-xs text-slate-700 font-semibold leading-relaxed">
                        Pengembangan gedung, ruang belajar, laboratorium, bengkel, fasilitas teknologi, dan sarana pendidikan untuk memastikan layanan pendidikan yang lebih baik.
                    </p>
                </div>

                <div class="bg-white p-5 rounded-2xl border-2 border-slate-900 shadow-md space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-amber-400 flex items-center justify-center font-black text-lg border border-black">2</div>
                    <h4 class="font-black text-slate-900 text-base">Beasiswa & Perangkat Sekolah</h4>
                    <p class="text-xs text-slate-700 font-semibold leading-relaxed">
                        Bantuan bagi siswa yang sedang bersekolah dalam bentuk <strong>Beasiswa Pendidikan</strong> serta <strong>Perangkat Sekolah</strong> (Pakaian seragam, Sepatu, Tas, perlengkapan belajar, dll).
                    </p>
                </div>

                <div class="bg-white p-5 rounded-2xl border-2 border-slate-900 shadow-md space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-amber-400 flex items-center justify-center font-black text-lg border border-black">3</div>
                    <h4 class="font-black text-slate-900 text-base">Akses Dunia Kerja & Magang</h4>
                    <p class="text-xs text-slate-700 font-semibold leading-relaxed">
                        Informasi lowongan pekerjaan, kesempatan magang, peluang industri, serta jejaring profesi yang membuka jalan bagi lulusan dan calon lulusan Pembda.
                    </p>
                </div>

                <div class="bg-white p-5 rounded-2xl border-2 border-slate-900 shadow-md space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-amber-400 flex items-center justify-center font-black text-lg border border-black">4</div>
                    <h4 class="font-black text-slate-900 text-base">Gagasan, Inovasi & Kolaborasi</h4>
                    <p class="text-xs text-slate-700 font-semibold leading-relaxed">
                        Ide kreatif, pengalaman profesional, teknologi, jejaring industri, maupun kolaborasi lainnya sebagai energi penting pengembangan yayasan.
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer Ajakan & Penutup -->
        <div class="bg-slate-900 text-white rounded-3xl p-6 sm:p-8 border-2 border-slate-900 space-y-4">
            <h4 class="text-lg sm:text-xl font-black text-amber-300">Dari Alumni, Untuk Generasi Berikutnya</h4>
            <p class="text-xs sm:text-sm text-indigo-100 font-medium leading-relaxed">
                Kami percaya, kekuatan terbesar Pembda bukan hanya terletak pada gedung dan fasilitas yang kita miliki, tetapi pada <strong>manusia, jejaring, pengalaman, dan semangat kebersamaan</strong> yang telah dibangun selama lebih dari lima dekade. Mari kita tidak hanya mengenang Pembda sebagai tempat kita pernah belajar, tetapi menjadikannya sebagai rumah besar yang terus kita bangun bersama.
            </p>

            <div class="bg-white/10 p-4 rounded-xl border border-white/20 text-xs text-indigo-100 space-y-1 font-mono">
                <p>• Dari Pembda kita belajar.</p>
                <p>• Dari Pembda kita bertumbuh.</p>
                <p>• Dari alumni kita membangun jejaring.</p>
                <p>• Dan bersama-sama, kita menyiapkan masa depan.</p>
            </div>

            <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-indigo-800/80">
                <div>
                    <strong class="block text-sm font-black text-white">Salam hormat,</strong>
                    <span class="text-xs text-amber-300 font-bold">Ketua Yayasan Perguruan Pembda Nias</span>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('alumni.kontribusi') }}" class="bg-amber-400 hover:bg-amber-500 text-slate-900 font-black px-5 py-2.5 rounded-xl text-xs transition border border-black shadow-sm">
                        <i class="fas fa-hand-holding-heart mr-1"></i> Kontribusi Alumni
                    </a>
                </div>
            </div>
        </div>

        <div class="pt-4 flex justify-between items-center border-t border-slate-200">
            <a href="{{ route('alumni.dashboard') }}" class="inline-flex items-center gap-2 bg-slate-200 hover:bg-slate-300 text-slate-900 font-black px-5 py-2.5 rounded-xl text-xs transition border border-slate-900">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
            <a href="{{ route('alumni.jobs.index') }}" class="inline-flex items-center gap-2 bg-indigo-900 hover:bg-indigo-950 text-amber-300 font-black px-5 py-2.5 rounded-xl text-xs transition border border-black shadow-sm">
                <i class="fas fa-briefcase"></i> Lihat Papan Loker →
            </a>
        </div>
    </div>
</div>
@endsection
