@php
    $user = Auth::user();
    $role = session('active_role', $user->role);
    $layout = match($role) {
        'yayasan', 'ketua_yayasan' => 'layouts.yayasan',
        'siswa' => 'layouts.siswa',
        'guru' => 'layouts.guru',
        default => 'layouts.admin',
    };
@endphp

@extends($layout)

@section('title', 'STEAMpreneur SMK 2026 - Hub Inovasi SMKS Pembda Nias')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12">

    <!-- Alert Notifications -->
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm">
        <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
        <div class="font-medium text-sm">{{ session('success') }}</div>
    </div>
    @endif
    @if(session('error'))
    <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm">
        <i class="fas fa-exclamation-circle text-rose-500 text-lg"></i>
        <div class="font-medium text-sm">{{ session('error') }}</div>
    </div>
    @endif

    <!-- Hero Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-900 text-white p-6 sm:p-8 shadow-xl border border-indigo-500/20">
        <div class="absolute -right-10 -top-10 w-72 h-72 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-20 -bottom-10 w-60 h-60 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-amber-500 text-slate-950 uppercase tracking-wider shadow-sm">
                        <i class="fas fa-trophy"></i> STEAMpreneur SMK 2026
                    </span>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-blue-200 backdrop-blur-md border border-white/10">
                        <i class="fas fa-check-double text-emerald-400"></i> Kemendikdasmen RI
                    </span>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        <i class="fas fa-satellite-dish"></i> In-Production (Berjalan Nyata)
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-black text-white leading-tight">
                    {{ $competitionData['title'] }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 max-w-3xl leading-relaxed">
                    Karya mandiri kolaborasi guru teknis dan siswa <strong>SMK Swasta Pembda Nias</strong> di bawah naungan <strong>{{ $competitionData['foundation_name'] }}</strong>. Menghubungkan multi-unit sekolah (SMP, SMA, SMK) dan unit usaha komersial <strong>www.bengkelin.cloud</strong> berbasis Kiosk IoT cerdas.
                </p>
                <div class="text-xs text-slate-400 flex flex-wrap items-center gap-4 pt-1">
                    <span><i class="fas fa-map-marker-alt text-rose-400 mr-1"></i> Kampus &amp; Gerai: <strong>{{ $competitionData['school_address'] }}</strong></span>
                    <span><i class="fas fa-user-tie text-blue-400 mr-1"></i> Guru Pendamping: <strong>{{ $competitionData['mentor_teacher']['name'] }}</strong></span>
                </div>
            </div>

            <!-- Quick Action Print Button -->
            <div class="flex flex-col sm:flex-row md:flex-col gap-2 shrink-0">
                <a href="{{ route('steam.proposal') }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm shadow-lg shadow-blue-600/30 transition transform hover:-translate-y-0.5">
                    <i class="fas fa-print"></i> Cetak Proposal A4 (PDF)
                </a>
                <a href="{{ route('steam.pitch-deck') }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-sm shadow-lg shadow-purple-600/30 transition transform hover:-translate-y-0.5">
                    <i class="fas fa-presentation-screen"></i> Buka Slide Pitch Deck
                </a>
            </div>
        </div>
    </div>

    <!-- 4 Main Navigation Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Proposal -->
        <a href="{{ route('steam.proposal') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-blue-300 transition flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg mb-3 group-hover:scale-110 transition">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-base mb-1 group-hover:text-blue-600 transition">Naskah Proposal</h3>
                <p class="text-xs text-slate-500 leading-relaxed">Format resmi A4, Model Matematika, HPP Kiosk Rp 320rb, &amp; BEP 5 Unit.</p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-blue-600">
                <span>Buka Dokumen</span>
                <i class="fas fa-arrow-right group-hover:translate-x-1 transition"></i>
            </div>
        </a>

        <!-- 2. Pitch Deck -->
        <a href="{{ route('steam.pitch-deck') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-purple-300 transition flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg mb-3 group-hover:scale-110 transition">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-base mb-1 group-hover:text-purple-600 transition">Pitch Deck 12 Slide</h3>
                <p class="text-xs text-slate-500 leading-relaxed">Slide presentasi 16:9 interaktif dengan keyboard panah kiri/kanan.</p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-purple-600">
                <span>Lihat Slide</span>
                <i class="fas fa-arrow-right group-hover:translate-x-1 transition"></i>
            </div>
        </a>

        <!-- 3. Video Script -->
        <a href="{{ route('steam.video-script') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-rose-300 transition flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg mb-3 group-hover:scale-110 transition">
                    <i class="fas fa-video"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-base mb-1 group-hover:text-rose-600 transition">Naskah Video 3-5 Mnt</h3>
                <p class="text-xs text-slate-500 leading-relaxed">Skenario kata demi kata untuk 3 murid presenter (durasi pas 4:15).</p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-rose-600">
                <span>Baca Naskah</span>
                <i class="fas fa-arrow-right group-hover:translate-x-1 transition"></i>
            </div>
        </a>

        <!-- 4. TeFa Live -->
        <a href="https://www.bengkelin.cloud" target="_blank" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-amber-300 transition flex flex-col justify-between">
            <div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg mb-3 group-hover:scale-110 transition">
                    <i class="fas fa-wrench"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-base mb-1 group-hover:text-amber-600 transition">TeFa Bengkelin</h3>
                <p class="text-xs text-slate-500 leading-relaxed">Front-end komersial publik live di <code>www.bengkelin.cloud</code>.</p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-amber-600">
                <span>Kunjungi Web</span>
                <i class="fas fa-external-link-alt group-hover:translate-x-1 transition"></i>
            </div>
        </a>
    </div>

    <!-- Dual Layout: Upload Documents & Team Management -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Uploaded Documents & Upload Form (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Upload Box -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="fas fa-cloud-arrow-up text-blue-600"></i>
                            Pusat Unggah Berkas &amp; Lampiran Lomba
                        </h2>
                        <p class="text-xs text-slate-500">Unggah Surat Pernyataan Bermaterai, Rekomendasi Kepsek, Video MP4, atau Presentasi PPTX</p>
                    </div>
                </div>

                <form action="{{ route('steam.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori / Jenis Berkas <span class="text-rose-500">*</span></label>
                            <select name="doc_type" required class="w-full text-xs rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 py-2.5">
                                <option value="Surat Pernyataan Keaslian (Meterai 10.000)">Surat Pernyataan Keaslian (Meterai 10.000)</option>
                                <option value="Surat Rekomendasi Kepala Sekolah">Surat Rekomendasi Kepala Sekolah</option>
                                <option value="Naskah Proposal Final (PDF)">Naskah Proposal Final (PDF)</option>
                                <option value="Slide Presentasi (PPTX/PDF)">Slide Presentasi (PPTX/PDF)</option>
                                <option value="Video Presentasi MP4">Video Presentasi (MP4)</option>
                                <option value="Dokumentasi Hardware &amp; Kiosk IoT">Dokumentasi Hardware &amp; Kiosk IoT</option>
                                <option value="Berkas Pendukung Lainnya">Berkas Pendukung Lainnya (ZIP/PDF)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan Singkat</label>
                            <input type="text" name="description" placeholder="Contoh: Sudah ditandatangani dan dibubuhi meterai" class="w-full text-xs rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 py-2.5">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih File (Maksimal 50 MB) <span class="text-rose-500">*</span></label>
                        <input type="file" name="document_file" required class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl cursor-pointer">
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center gap-2">
                            <i class="fas fa-upload"></i> Unggah Berkas Sekarang
                        </button>
                    </div>
                </form>
            </div>

            <!-- Uploaded Documents Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-folder-open text-amber-500"></i>
                        Daftar Berkas Terunggah ({{ count($documents) }})
                    </h3>
                </div>

                @if(empty($documents))
                <div class="p-8 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-lg">
                        <i class="fas fa-file-circle-question"></i>
                    </div>
                    <p class="text-xs text-slate-500">Belum ada berkas yang diunggah.</p>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3">Nama Berkas &amp; Jenis</th>
                                <th class="px-4 py-3">Ukuran</th>
                                <th class="px-4 py-3">Diunggah Oleh</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($documents as $doc)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-900 flex items-center gap-2">
                                        <i class="fas {{ in_array($doc['extension'], ['pdf']) ? 'fa-file-pdf text-rose-500' : (in_array($doc['extension'], ['zip', 'rar']) ? 'fa-file-zipper text-amber-500' : 'fa-file text-blue-500') }}"></i>
                                        <span>{{ $doc['name'] }}</span>
                                    </div>
                                    <div class="text-[11px] text-blue-600 font-medium">{{ $doc['type'] }}</div>
                                    @if(!empty($doc['description']) && $doc['description'] !== '-')
                                    <div class="text-[10px] text-slate-400 italic">{{ $doc['description'] }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-500">{{ $doc['size_human'] }}</td>
                                <td class="px-4 py-3 text-slate-500">
                                    <div>{{ $doc['uploaded_by'] }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $doc['uploaded_at'] }}</div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ $doc['url'] }}" download="{{ $doc['name'] }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Unduh">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @if($isPrivileged)
                                        <form action="{{ route('steam.document.delete', $doc['id']) }}" method="POST" onsubmit="return confirm('Hapus berkas ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

        </div>

        <!-- Right: Team Members & Linking Mechanism (1 Col) -->
        <div class="space-y-6">

            <!-- Team Members Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
                <h3 class="text-sm font-extrabold text-slate-900 flex items-center justify-between border-b border-slate-100 pb-3">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-users-gear text-indigo-600"></i>
                        Susunan Tim Inovator
                    </span>
                    @if(empty($competitionData['team_leader']['id']))
                    <span class="text-[10px] font-black bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full">Belum Ditentukan</span>
                    @else
                    <span class="text-[10px] font-black bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">Aktif</span>
                    @endif
                </h3>

                <!-- Guru Pembimbing -->
                <div class="p-3.5 bg-indigo-50/70 rounded-xl border border-indigo-100">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase text-indigo-600 tracking-wider">Guru Pendamping</span>
                        <i class="fas fa-chalkboard-user text-indigo-400"></i>
                    </div>
                    <div class="font-extrabold text-slate-900 text-xs mt-1">{{ $competitionData['mentor_teacher']['name'] }}</div>
                    <div class="text-[11px] text-slate-600 font-medium">{{ $competitionData['mentor_teacher']['nip'] }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">{{ $competitionData['mentor_teacher']['subject'] }}</div>
                </div>

                <!-- Ketua Tim -->
                <div class="p-3.5 bg-amber-50/70 rounded-xl border border-amber-200/80">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase text-amber-700 tracking-wider">Ketua Tim (Siswa)</span>
                        <i class="fas fa-crown text-amber-500"></i>
                    </div>
                    <div class="font-extrabold text-slate-900 text-xs mt-1">{{ $competitionData['team_leader']['name'] }}</div>
                    <div class="text-[11px] text-slate-600 font-medium">NISN: {{ $competitionData['team_leader']['nisn'] }} &bull; {{ $competitionData['team_leader']['class'] }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">{{ $competitionData['team_leader']['role'] }}</div>
                </div>

                <!-- Anggota 1 -->
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase text-slate-600 tracking-wider">Anggota 1 (Siswa)</span>
                        <i class="fas fa-microchip text-slate-400"></i>
                    </div>
                    <div class="font-extrabold text-slate-900 text-xs mt-1">{{ $competitionData['team_member_1']['name'] }}</div>
                    <div class="text-[11px] text-slate-600 font-medium">NISN: {{ $competitionData['team_member_1']['nisn'] }} &bull; {{ $competitionData['team_member_1']['class'] }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">{{ $competitionData['team_member_1']['role'] }}</div>
                </div>

                <!-- Anggota 2 -->
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase text-slate-600 tracking-wider">Anggota 2 (Siswa)</span>
                        <i class="fas fa-chart-line text-slate-400"></i>
                    </div>
                    <div class="font-extrabold text-slate-900 text-xs mt-1">{{ $competitionData['team_member_2']['name'] }}</div>
                    <div class="text-[11px] text-slate-600 font-medium">NISN: {{ $competitionData['team_member_2']['nisn'] }} &bull; {{ $competitionData['team_member_2']['class'] }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">{{ $competitionData['team_member_2']['role'] }}</div>
                </div>
            </div>

            <!-- Form Hubungkan / Tunjuk Siswa Peserta Lomba -->
            @if($isPrivileged)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-user-plus text-blue-600"></i>
                        Pilih &amp; Hubungkan Siswa Peserta
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                        Pilih 3 siswa SMK di bawah. Setelah disimpan, <strong>menu STEAMpreneur otomatis muncul di portal akun masing-masing siswa tersebut</strong>.
                    </p>
                </div>

                <form action="{{ route('steam.settings') }}" method="POST" class="space-y-3.5">
                    @csrf
                    
                    <!-- Guru Pendamping Name -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Guru Pendamping</label>
                        <input type="text" name="mentor_name" value="{{ $competitionData['mentor_teacher']['name'] }}" required class="w-full text-xs rounded-xl border-slate-300 focus:border-blue-500 py-2">
                    </div>

                    <!-- Ketua Tim Dropdown -->
                    <div>
                        <label class="block text-[11px] font-bold text-amber-700 mb-1">
                            🥇 Ketua Tim (Siswa 1)
                        </label>
                        <select name="team_leader_student_id" class="w-full text-xs rounded-xl border-slate-300 focus:border-amber-500 py-2">
                            <option value="">-- Pilih Siswa SMK untuk Ketua Tim --</option>
                            @foreach($smkStudents as $st)
                            @php
                                $clsName = $st->studentClasses()->where('status', 'aktif')->latest('id')->first()?->classroom?->class_name ?? 'SMK';
                            @endphp
                            <option value="{{ $st->id }}" {{ $competitionData['team_leader']['id'] == $st->id ? 'selected' : '' }}>
                                {{ $st->full_name }} (NISN: {{ $st->nisn ?: $st->nis }} - {{ $clsName }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Anggota 1 Dropdown -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                            🥈 Anggota 1 (Siswa 2 - Hardware)
                        </label>
                        <select name="member_1_student_id" class="w-full text-xs rounded-xl border-slate-300 focus:border-blue-500 py-2">
                            <option value="">-- Pilih Siswa SMK untuk Anggota 1 --</option>
                            @foreach($smkStudents as $st)
                            @php
                                $clsName = $st->studentClasses()->where('status', 'aktif')->latest('id')->first()?->classroom?->class_name ?? 'SMK';
                            @endphp
                            <option value="{{ $st->id }}" {{ $competitionData['team_member_1']['id'] == $st->id ? 'selected' : '' }}>
                                {{ $st->full_name }} (NISN: {{ $st->nisn ?: $st->nis }} - {{ $clsName }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Anggota 2 Dropdown -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">
                            🥉 Anggota 2 (Siswa 3 - Bisnis/TeFa)
                        </label>
                        <select name="member_2_student_id" class="w-full text-xs rounded-xl border-slate-300 focus:border-blue-500 py-2">
                            <option value="">-- Pilih Siswa SMK untuk Anggota 2 --</option>
                            @foreach($smkStudents as $st)
                            @php
                                $clsName = $st->studentClasses()->where('status', 'aktif')->latest('id')->first()?->classroom?->class_name ?? 'SMK';
                            @endphp
                            <option value="{{ $st->id }}" {{ $competitionData['team_member_2']['id'] == $st->id ? 'selected' : '' }}>
                                {{ $st->full_name }} (NISN: {{ $st->nisn ?: $st->nis }} - {{ $clsName }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Video YouTube URL -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Tautan Video YouTube Lomba</label>
                        <input type="url" name="youtube_url" value="{{ $competitionData['youtube_video_url'] }}" placeholder="https://youtu.be/..." class="w-full text-xs rounded-xl border-slate-300 focus:border-blue-500 py-2">
                    </div>

                    <div class="pt-2 flex flex-col gap-2">
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center justify-center gap-1.5">
                            <i class="fas fa-save"></i> Hubungkan Tim &amp; Simpan
                        </button>
                    </div>
                </form>
            </div>
            @endif

        </div>

    </div>

</div>
@endsection
