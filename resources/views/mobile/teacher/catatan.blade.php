@extends('mobile.layouts.app')

@section('title', 'Kelola Catatan Perkembangan & Prestasi Siswa - Guru')

@section('content')
<div class="space-y-4 pb-14" x-data="{ 
    activeTab: '{{ request()->query('tab', 'prestasi') }}',
    showForm: null, // 'prestasi', 'pembinaan', 'perkembangan', null
    searchStudent: ''
}">
    <!-- Header Title -->
    <div class="flex items-center justify-between gap-2 px-1 pt-1">
        <div class="flex items-center gap-2.5 min-w-0">
            <a href="{{ route('mobile.dashboard') }}" class="w-9 h-9 rounded-2xl bg-white border-2 border-slate-200 text-slate-700 flex items-center justify-center shadow-xs active:scale-95 transition shrink-0">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            <div class="min-w-0">
                <h2 class="text-base font-black text-slate-900 leading-tight truncate">Catatan Siswa Guru</h2>
                <p class="text-[10px] text-slate-500 font-bold truncate">Prestasi, Pembinaan & Observasi Karakter</p>
            </div>
        </div>

        <a href="{{ route('mobile.guru.hall-of-fame') }}" class="px-3 py-1.5 rounded-xl bg-amber-50 border-2 border-amber-300 text-amber-900 text-xs font-black hover:bg-amber-100 active:scale-95 transition flex items-center gap-1.5 shadow-2xs shrink-0">
            <i class="fa-solid fa-trophy text-amber-500"></i>
            <span>Hall of Fame</span>
        </a>
    </div>

    <!-- Classroom Filter Selector -->
    <div class="clay-card p-3 bg-white border-2 border-slate-200 rounded-3xl shadow-sm space-y-2">
        <label class="block text-[11px] font-black text-slate-700 flex items-center gap-1.5">
            <i class="fa-solid fa-users-rectangle text-purple-600"></i> Pilih Kelas / Rombel Siswa:
        </label>
        <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
            @foreach($allClassrooms as $cls)
                <a href="{{ route('mobile.guru.catatan-siswa', ['classroom_id' => $cls->id]) }}" 
                   class="px-3.5 py-2 rounded-2xl text-xs font-black whitespace-nowrap transition {{ $selectedClassroomId == $cls->id ? 'clay-purple text-white shadow-md scale-105' : 'bg-slate-50 text-slate-700 border-2 border-slate-200 hover:bg-slate-100' }}">
                    {{ $cls->class_name }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Student Selection Slider / Grid -->
    <div class="clay-card p-4 bg-white border-2 border-slate-200 rounded-3xl shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-black text-slate-800 flex items-center gap-1.5">
                <i class="fa-solid fa-user-graduate text-blue-600"></i> Daftar Siswa di Kelas Ini
            </h3>
            <span class="text-[10px] font-black text-purple-700 bg-purple-100 px-2 py-0.5 rounded-full">
                {{ $students->count() }} Siswa
            </span>
        </div>

        <!-- Search Student Bar -->
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input type="text" x-model="searchStudent" placeholder="Cari nama siswa..." 
                   class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-hidden focus:border-purple-500 transition">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto pr-1">
            @forelse($students as $std)
                <a href="{{ route('mobile.guru.catatan-siswa', ['classroom_id' => $selectedClassroomId, 'student_id' => $std->id]) }}"
                   x-show="!searchStudent || '{{ strtolower(addslashes($std->full_name)) }}'.includes(searchStudent.toLowerCase())"
                   class="p-2.5 rounded-2xl border-2 transition flex items-center justify-between {{ $selectedStudent && $selectedStudent->id == $std->id ? 'bg-purple-50 border-purple-500 shadow-xs' : 'bg-slate-50 border-slate-200 hover:bg-slate-100' }}">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-purple-600 text-white font-black text-xs flex items-center justify-center shrink-0">
                            {{ strtoupper(substr($std->full_name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-black text-slate-900 truncate leading-tight">{{ $std->full_name }}</h4>
                            <p class="text-[9px] font-bold text-slate-400">NISN: {{ $std->nisn ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-1 shrink-0 text-[10px] font-extrabold">
                        @if($std->achievements_count > 0)
                            <span class="px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-800" title="{{ $std->achievements_count }} Prestasi">
                                🏆 {{ $std->achievements_count }}
                            </span>
                        @endif
                        @if($std->counseling_records_count > 0)
                            <span class="px-1.5 py-0.5 rounded-md bg-purple-100 text-purple-800" title="{{ $std->counseling_records_count }} Pembinaan">
                                🛡️ {{ $std->counseling_records_count }}
                            </span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="p-4 text-center text-slate-400 text-xs font-bold col-span-2">
                    Tidak ada siswa pada rombel ini.
                </div>
            @endforelse
        </div>
    </div>

    @if($selectedStudent)
        <!-- Active Selected Student Card -->
        <div class="clay-purple p-5 space-y-3 rounded-3xl shadow-md">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md text-white flex items-center justify-center text-xl font-black border border-white/30 shrink-0 shadow-sm">
                        🎓
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-black text-white leading-tight truncate">{{ $selectedStudent->full_name }}</h3>
                        <p class="text-[10px] text-purple-100 font-bold truncate">
                            NISN: {{ $selectedStudent->nisn ?? '-' }} • {{ $selectedStudent->school->name ?? 'Pembda' }}
                        </p>
                    </div>
                </div>
                <span class="text-[10px] font-black text-purple-900 bg-amber-300 px-3 py-1 rounded-full border border-amber-200 shadow-xs shrink-0">
                    ⭐ {{ number_format($selectedStudent->user->reputation->total_points ?? $selectedStudent->reputation_points ?? 0) }} Pts
                </span>
            </div>

            <!-- Add Note Action Buttons -->
            <div class="grid grid-cols-3 gap-2 pt-1">
                <button @click="showForm = (showForm === 'prestasi' ? null : 'prestasi')" 
                        class="py-2.5 px-2 rounded-xl bg-amber-400 text-slate-950 font-black text-[11px] hover:bg-amber-300 active:scale-95 transition shadow-sm flex items-center justify-center gap-1">
                    <i class="fa-solid" :class="showForm === 'prestasi' ? 'fa-xmark' : 'fa-plus'"></i>
                    <span x-text="showForm === 'prestasi' ? 'Batal' : '+ Prestasi'"></span>
                </button>
                <button @click="showForm = (showForm === 'pembinaan' ? null : 'pembinaan')" 
                        class="py-2.5 px-2 rounded-xl bg-purple-900 text-white font-black text-[11px] hover:bg-purple-800 active:scale-95 transition shadow-sm flex items-center justify-center gap-1">
                    <i class="fa-solid" :class="showForm === 'pembinaan' ? 'fa-xmark' : 'fa-plus'"></i>
                    <span x-text="showForm === 'pembinaan' ? 'Batal' : '+ Pembinaan'"></span>
                </button>
                <button @click="showForm = (showForm === 'perkembangan' ? null : 'perkembangan')" 
                        class="py-2.5 px-2 rounded-xl bg-blue-600 text-white font-black text-[11px] hover:bg-blue-500 active:scale-95 transition shadow-sm flex items-center justify-center gap-1">
                    <i class="fa-solid" :class="showForm === 'perkembangan' ? 'fa-xmark' : 'fa-plus'"></i>
                    <span x-text="showForm === 'perkembangan' ? 'Batal' : '+ Observasi'"></span>
                </button>
            </div>
        </div>

        <!-- FORM INPUT 1: CATATAN PRESTASI SISWA -->
        <div x-show="showForm === 'prestasi'" x-collapse class="clay-card p-5 space-y-3 bg-gradient-to-b from-amber-50 to-white border-2 border-amber-300 rounded-3xl shadow-md">
            <div class="flex items-center justify-between border-b border-amber-200 pb-2">
                <h4 class="text-xs font-black text-amber-950 flex items-center gap-2">
                    <i class="fa-solid fa-trophy text-amber-600 text-sm"></i> Tambah Catatan Prestasi Siswa
                </h4>
                <span class="text-[9px] font-black bg-amber-200 text-amber-900 px-2 py-0.5 rounded-full">+ Poin Reputasi</span>
            </div>

            <form action="{{ route('mobile.guru.catatan-siswa.prestasi.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <input type="hidden" name="student_id" value="{{ $selectedStudent->id }}">
                <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">

                <div>
                    <label class="block text-[11px] font-black text-slate-800 mb-1">Nama / Judul Prestasi <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: Juara 1 LKS Web Technologies Tingkat Provinsi" 
                           class="w-full px-3.5 py-2.5 bg-white border-2 border-amber-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-hidden focus:border-amber-500 transition">
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[11px] font-black text-slate-800 mb-1">Bidang</label>
                        <select name="type" class="w-full px-2.5 py-2.5 bg-white border-2 border-amber-200 rounded-xl text-xs font-bold text-slate-900">
                            <option value="academic">Akademik</option>
                            <option value="competition">Kompetisi/LKS</option>
                            <option value="sport">Olahraga</option>
                            <option value="art">Seni & Budaya</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-slate-800 mb-1">Tingkat</label>
                        <select name="level" class="w-full px-2.5 py-2.5 bg-white border-2 border-amber-200 rounded-xl text-xs font-bold text-slate-900">
                            <option value="school">Sekolah</option>
                            <option value="district">Kecamatan</option>
                            <option value="city">Kota/Kabupaten</option>
                            <option value="province">Provinsi</option>
                            <option value="national">Nasional</option>
                            <option value="international">Internasional</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-slate-800 mb-1">Peringkat</label>
                        <select name="rank" class="w-full px-2.5 py-2.5 bg-white border-2 border-amber-200 rounded-xl text-xs font-bold text-slate-900">
                            <option value="winner">Juara 1 🥇</option>
                            <option value="runner_up">Juara 2 🥈</option>
                            <option value="third_place">Juara 3 🥉</option>
                            <option value="participant">Peserta 🎖️</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-black text-slate-800 mb-1">Tanggal Raihan</label>
                        <input type="date" name="achievement_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3 py-2 bg-white border-2 border-amber-200 rounded-xl text-xs font-bold text-slate-900">
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-slate-800 mb-1">Upload Piagam/Foto</label>
                        <input type="file" name="certificate_file" accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full px-2 py-1.5 bg-white border-2 border-amber-200 rounded-xl text-[10px] font-bold text-slate-600">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-black text-slate-800 mb-1">Keterangan / Deskripsi Singkat</label>
                    <textarea name="description" rows="2" placeholder="Tuliskan penyelenggara, nama kegiatan, atau catatan tambahan..."
                              class="w-full px-3 py-2 bg-white border-2 border-amber-200 rounded-xl text-xs font-bold text-slate-900 resize-none"></textarea>
                </div>

                <button type="submit" class="w-full py-3 bg-gradient-to-r from-amber-500 to-yellow-500 text-slate-950 font-black text-xs rounded-xl shadow-md hover:opacity-95 active:scale-98 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Catatan Prestasi & Tambah Reputasi
                </button>
            </form>
        </div>

        <!-- FORM INPUT 2: CATATAN PEMBINAAN & BK -->
        <div x-show="showForm === 'pembinaan'" x-collapse class="clay-card p-5 space-y-3 bg-gradient-to-b from-purple-50 to-white border-2 border-purple-300 rounded-3xl shadow-md">
            <div class="flex items-center justify-between border-b border-purple-200 pb-2">
                <h4 class="text-xs font-black text-purple-950 flex items-center gap-2">
                    <i class="fa-solid fa-hand-holding-heart text-purple-600 text-sm"></i> Catatan Pembinaan & Bimbingan BK
                </h4>
                <span class="text-[9px] font-black bg-purple-200 text-purple-900 px-2 py-0.5 rounded-full">Bimbingan Karakter</span>
            </div>

            <form action="{{ route('mobile.guru.catatan-siswa.pembinaan.store') }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="student_id" value="{{ $selectedStudent->id }}">
                <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">

                <div>
                    <label class="block text-[11px] font-black text-slate-800 mb-1">Topik / Kasus Pembinaan <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: Bimbingan Kedisiplinan Kehadiran & Motivasi Belajar" 
                           class="w-full px-3.5 py-2.5 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-hidden focus:border-purple-600 transition">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-black text-slate-800 mb-1">Jenis Sesi</label>
                        <select name="record_type" class="w-full px-2.5 py-2.5 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900">
                            <option value="pembinaan">Pembinaan Karakter</option>
                            <option value="konseling">Konseling Individual</option>
                            <option value="pelanggaran">Catatan Pelanggaran</option>
                            <option value="home_visit">Kunjungan Rumah</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-slate-800 mb-1">Kategori</label>
                        <select name="category" class="w-full px-2.5 py-2.5 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900">
                            <option value="disiplin">Kedisiplinan & Tata Tertib</option>
                            <option value="kehadiran">Kehadiran / Presensi</option>
                            <option value="akademik">Akademik & Tugas</option>
                            <option value="sosial">Sosial & Pertemanan</option>
                            <option value="moral">Sikap & Moral</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-black text-slate-800 mb-1">Tanggal Sesi</label>
                        <input type="date" name="incident_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3 py-2 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900">
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-slate-800 mb-1">Status Penanganan</label>
                        <select name="status" class="w-full px-2.5 py-2 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900">
                            <option value="resolved">Selesai (Resolved)</option>
                            <option value="in_progress">Dalam Proses (In Progress)</option>
                            <option value="open">Aktif (Open)</option>
                            <option value="closed">Ditutup (Closed)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-black text-slate-800 mb-1">Deskripsi Masalah / Kejadian <span class="text-rose-500">*</span></label>
                    <textarea name="description" rows="2" required placeholder="Jelaskan latar belakang atau kronologi pembinaan..."
                              class="w-full px-3 py-2 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-[11px] font-black text-slate-800 mb-1">Tindakan & Solusi yang Diambil</label>
                    <textarea name="action_taken" rows="2" placeholder="Contoh: Diberikan bimbingan konseling dan komitmen perbaikan kehadiran..."
                              class="w-full px-3 py-2 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-[11px] font-black text-slate-800 mb-1">Rencana Tindak Lanjut</label>
                    <input type="text" name="follow_up" placeholder="Contoh: Evaluasi perkembangan 2 pekan ke depan oleh wali kelas..."
                           class="w-full px-3 py-2 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900">
                </div>

                <div class="flex items-center gap-4 text-xs font-bold text-slate-800 p-2.5 bg-purple-100/60 rounded-xl border border-purple-200">
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="parent_notified" value="1" class="rounded border-purple-400 text-purple-600">
                        <span>Orang Tua Diberitahu</span>
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="is_confidential" value="1" class="rounded border-purple-400 text-purple-600">
                        <span>Rahasia (Hanya Guru/BK)</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-black text-xs rounded-xl shadow-md hover:opacity-95 active:scale-98 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Catatan Pembinaan
                </button>
            </form>
        </div>

        <!-- FORM INPUT 3: CATATAN PERKEMBANGAN & OBSERVASI -->
        <div x-show="showForm === 'perkembangan'" x-collapse class="clay-card p-5 space-y-3 bg-gradient-to-b from-blue-50 to-white border-2 border-blue-300 rounded-3xl shadow-md">
            <div class="flex items-center justify-between border-b border-blue-200 pb-2">
                <h4 class="text-xs font-black text-blue-950 flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-blue-600 text-sm"></i> Catatan Observasi & Perkembangan
                </h4>
                <span class="text-[9px] font-black bg-blue-200 text-blue-900 px-2 py-0.5 rounded-full">Wali Kelas</span>
            </div>

            <form action="{{ route('mobile.guru.catatan-siswa.perkembangan.store') }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="student_id" value="{{ $selectedStudent->id }}">
                <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">

                <div>
                    <label class="block text-[11px] font-black text-slate-800 mb-1">Aspek Perkembangan <span class="text-rose-500">*</span></label>
                    <select name="aspect" class="w-full px-3 py-2.5 bg-white border-2 border-blue-200 rounded-xl text-xs font-bold text-slate-900">
                        <option value="akademik">Akademik & Penguasaan Materi</option>
                        <option value="sikap">Sikap & Perilaku Sehari-hari</option>
                        <option value="keterampilan">Keterampilan Praktik / Softskill</option>
                        <option value="spiritual">Keagamaan & Nilai Spiritual</option>
                        <option value="sosial">Interaksi Sosial & Kerja Sama</option>
                        <option value="fisik">Kebugaran Fisik & Kesehatan</option>
                        <option value="ekstrakurikuler">Keaktifan Ekstrakurikuler</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-black text-slate-800 mb-1">Hasil Observasi / Pengamatan <span class="text-rose-500">*</span></label>
                    <textarea name="observation" rows="2" required placeholder="Tuliskan catatan observasi guru terhadap sikap atau progres siswa..."
                              class="w-full px-3 py-2 bg-white border-2 border-blue-200 rounded-xl text-xs font-bold text-slate-900 resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-[11px] font-black text-slate-800 mb-1">Kemajuan / Progres Positif</label>
                    <textarea name="progress" rows="2" placeholder="Peningkatan yang ditunjukkan siswa..."
                              class="w-full px-3 py-2 bg-white border-2 border-blue-200 rounded-xl text-xs font-bold text-slate-900 resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-[11px] font-black text-slate-800 mb-1">Tantangan / Kendala Belajar</label>
                    <input type="text" name="challenges" placeholder="Hambatan yang masih dihadapi siswa..."
                           class="w-full px-3 py-2 bg-white border-2 border-blue-200 rounded-xl text-xs font-bold text-slate-900">
                </div>

                <div>
                    <label class="block text-[11px] font-black text-slate-800 mb-1">Saran / Rekomendasi Guru</label>
                    <input type="text" name="suggestion" placeholder="Saran perbaikan untuk siswa atau koordinasi dengan orang tua..."
                           class="w-full px-3 py-2 bg-white border-2 border-blue-200 rounded-xl text-xs font-bold text-slate-900">
                </div>

                <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-600 to-teal-600 text-white font-black text-xs rounded-xl shadow-md hover:opacity-95 active:scale-98 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Catatan Perkembangan
                </button>
            </form>
        </div>

        <!-- Detail Tabs & History for Selected Student -->
        <div class="space-y-3">
            <div class="grid grid-cols-3 gap-1.5 p-1 bg-slate-200/80 rounded-2xl border border-slate-300 shadow-inner">
                <button @click="activeTab = 'prestasi'"
                        :class="activeTab === 'prestasi' ? 'bg-amber-400 text-slate-950 font-black shadow-sm' : 'text-slate-600 font-bold hover:text-slate-900'"
                        class="py-2 rounded-xl text-xs transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-award text-xs"></i>
                    <span>Prestasi ({{ $achievements->count() }})</span>
                </button>
                <button @click="activeTab = 'pembinaan'"
                        :class="activeTab === 'pembinaan' ? 'bg-purple-600 text-white font-black shadow-sm' : 'text-slate-600 font-bold hover:text-slate-900'"
                        class="py-2 rounded-xl text-xs transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-hand-holding-heart text-xs"></i>
                    <span>Pembinaan ({{ $counselings->count() }})</span>
                </button>
                <button @click="activeTab = 'perkembangan'"
                        :class="activeTab === 'perkembangan' ? 'bg-blue-600 text-white font-black shadow-sm' : 'text-slate-600 font-bold hover:text-slate-900'"
                        class="py-2 rounded-xl text-xs transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-chart-line text-xs"></i>
                    <span>Observasi ({{ $developmentNotes->count() }})</span>
                </button>
            </div>

            <!-- Tab 1: Prestasi List -->
            <div x-show="activeTab === 'prestasi'" x-transition class="space-y-3">
                @forelse($achievements as $ach)
                    @php
                        $isPending = ($ach->status === 'pending');
                        $isVerified = ($ach->status === 'verified');
                        $isRejected = ($ach->status === 'rejected');
                    @endphp
                    <div class="clay-card p-4 space-y-3 bg-white border-2 {{ $isVerified ? 'border-emerald-300 bg-emerald-50/20' : ($isRejected ? 'border-rose-300 bg-rose-50/20' : 'border-amber-300 bg-amber-50/20') }} rounded-3xl shadow-sm">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-start gap-2.5 min-w-0">
                                <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center text-lg shrink-0">
                                    {{ $ach->rank === 'winner' ? '🥇' : ($ach->rank === 'runner_up' ? '🥈' : ($ach->rank === 'third_place' ? '🥉' : '🎖️')) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-1">
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-amber-100 text-amber-900 border border-amber-300">
                                            Tingkat {{ $ach->level_label }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black {{ $isVerified ? 'bg-emerald-100 text-emerald-800' : ($isRejected ? 'bg-rose-100 text-rose-800' : 'bg-amber-200 text-amber-900') }}">
                                            {{ $ach->status_label }}
                                        </span>
                                    </div>
                                    <h4 class="text-xs font-black text-slate-900 mt-1 leading-snug">{{ $ach->title }}</h4>
                                    <p class="text-[10px] font-bold text-slate-500">
                                        {{ $ach->rank_label }} • {{ $ach->achievement_date ? $ach->achievement_date->translatedFormat('d M Y') : '-' }} • 
                                        <strong class="{{ $isRejected ? 'line-through text-slate-400' : 'text-amber-700' }}">+{{ $ach->points ?? 50 }} Pts</strong>
                                    </p>
                                </div>
                            </div>

                            <form action="{{ route('mobile.guru.catatan-siswa.prestasi.destroy', $ach->id) }}" method="POST" onsubmit="return confirm('Hapus catatan prestasi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 active:scale-95 transition" title="Hapus">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>

                        @if($ach->description)
                            <p class="p-2.5 rounded-xl bg-white text-[11px] text-slate-700 font-semibold border border-slate-200">
                                {{ $ach->description }}
                            </p>
                        @endif

                        @if($ach->verification_notes)
                            <p class="p-2 rounded-xl text-[10px] font-bold {{ $isRejected ? 'bg-rose-100 text-rose-900' : 'bg-emerald-100 text-emerald-900' }}">
                                <strong>Catatan Wali Kelas:</strong> {{ $ach->verification_notes }}
                            </p>
                        @endif

                        <!-- Proof Link & Justification Buttons -->
                        <div class="pt-1 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100">
                            @if($ach->certificate_file)
                                <a href="{{ asset('storage/' . $ach->certificate_file) }}" target="_blank" class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-800 text-[10px] font-black border border-slate-300 flex items-center gap-1 hover:bg-slate-200">
                                    <i class="fa-solid fa-paperclip"></i>
                                    <span>Lihat Bukti</span>
                                </a>
                            @else
                                <span class="text-[10px] text-slate-400 italic">Tanpa dokumen</span>
                            @endif

                            <div class="flex items-center gap-1.5">
                                @if($isPending)
                                    <form action="{{ route('mobile.guru.catatan-siswa.prestasi.justify', $ach->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="notes" value="Diverifikasi dan diakui oleh Wali Kelas di Mobile.">
                                        <button type="submit" class="px-2.5 py-1 rounded-xl bg-emerald-500 text-white font-black text-[10px] shadow-xs active:scale-95 transition">
                                            ✓ Akui
                                        </button>
                                    </form>

                                    <form action="{{ route('mobile.guru.catatan-siswa.prestasi.justify', $ach->id) }}" method="POST" class="inline" onsubmit="var n = prompt('Masukkan alasan penolakan prestasi:'); if(!n) return false; this.notes.value = n;">
                                        @csrf
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="notes" value="">
                                        <button type="submit" class="px-2.5 py-1 rounded-xl bg-rose-500 text-white font-black text-[10px] shadow-xs active:scale-95 transition">
                                            ✕ Tolak & Tarik
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('mobile.guru.catatan-siswa.prestasi.justify', $ach->id) }}" method="POST" class="inline" onsubmit="if('{{ $isVerified }}' === '1') { var n = prompt('Tolak prestasi dan tarik kembali poin? Masukkan alasan:'); if(!n) return false; this.notes.value = n; }">
                                        @csrf
                                        <input type="hidden" name="action" value="{{ $isVerified ? 'reject' : 'approve' }}">
                                        <input type="hidden" name="notes" value="{{ $isVerified ? '' : 'Diverifikasi kembali oleh Wali Kelas.' }}">
                                        <button type="submit" class="px-2.5 py-1 rounded-xl bg-slate-200 text-slate-800 font-bold text-[10px] hover:bg-slate-300">
                                            Ubah Status
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-400 text-xs font-bold clay-card bg-white rounded-3xl">
                        Belum ada catatan prestasi untuk siswa ini.
                    </div>
                @endforelse
            </div>

            <!-- Tab 2: Pembinaan List -->
            <div x-show="activeTab === 'pembinaan'" x-transition class="space-y-3">
                @forelse($counselings as $csl)
                    <div class="clay-card p-4 space-y-2.5 bg-white border-2 border-purple-200 rounded-3xl shadow-sm">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-start gap-2.5 min-w-0">
                                <div class="w-10 h-10 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center text-lg shrink-0">
                                    🛡️
                                </div>
                                <div class="min-w-0">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-purple-100 text-purple-900 border border-purple-300">
                                        {{ ucfirst($csl->record_type) }} • {{ ucfirst($csl->category ?? 'Disiplin') }}
                                    </span>
                                    <h4 class="text-xs font-black text-slate-900 mt-1 leading-snug">{{ $csl->title }}</h4>
                                    <p class="text-[10px] font-bold text-slate-400">{{ $csl->incident_date ? $csl->incident_date->translatedFormat('d M Y') : '-' }}</p>
                                </div>
                            </div>

                            <form action="{{ route('mobile.guru.catatan-siswa.pembinaan.destroy', $csl->id) }}" method="POST" onsubmit="return confirm('Hapus catatan pembinaan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 active:scale-95 transition">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>
                        @if($csl->description)
                            <div class="p-2.5 rounded-xl bg-purple-50 text-[11px] text-slate-700 font-semibold border border-purple-100 space-y-1">
                                <p>{{ $csl->description }}</p>
                                @if($csl->action_taken)
                                    <p class="text-[10px] text-purple-950 font-bold">💡 Tindakan: {{ $csl->action_taken }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-400 text-xs font-bold clay-card bg-white rounded-3xl">
                        Belum ada catatan pembinaan untuk siswa ini.
                    </div>
                @endforelse
            </div>

            <!-- Tab 3: Observasi List -->
            <div x-show="activeTab === 'perkembangan'" x-transition class="space-y-3">
                @forelse($developmentNotes as $note)
                    <div class="clay-card p-4 space-y-2.5 bg-white border-2 border-blue-200 rounded-3xl shadow-sm">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-start gap-2.5 min-w-0">
                                <div class="w-10 h-10 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center text-base shrink-0">
                                    📈
                                </div>
                                <div class="min-w-0">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-blue-100 text-blue-900 border border-blue-300">
                                        Aspek {{ ucfirst($note->aspect) }}
                                    </span>
                                    <p class="text-[10px] font-bold text-slate-400 mt-1">{{ $note->created_at ? $note->created_at->translatedFormat('d M Y') : '-' }}</p>
                                </div>
                            </div>

                            <form action="{{ route('mobile.guru.catatan-siswa.perkembangan.destroy', $note->id) }}" method="POST" onsubmit="return confirm('Hapus catatan perkembangan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 active:scale-95 transition">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>
                        <div class="p-2.5 rounded-xl bg-blue-50 text-[11px] text-slate-700 font-semibold border border-blue-100 space-y-1">
                            <p class="font-bold text-slate-900">🔍 Observasi: {{ $note->observation }}</p>
                            @if($note->progress)
                                <p class="text-emerald-800 text-[10px]">🚀 Progres: {{ $note->progress }}</p>
                            @endif
                            @if($note->suggestion)
                                <p class="text-blue-900 text-[10px]">💡 Saran: {{ $note->suggestion }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-400 text-xs font-bold clay-card bg-white rounded-3xl">
                        Belum ada catatan observasi untuk siswa ini.
                    </div>
                @endforelse
            </div>
        </div>
    @else
        <div class="clay-card p-8 text-center text-slate-500 space-y-3 bg-white rounded-3xl">
            <div class="w-16 h-16 rounded-3xl bg-purple-50 border-2 border-purple-200 text-purple-600 text-3xl flex items-center justify-center mx-auto shadow-inner">
                👆
            </div>
            <div>
                <h4 class="text-sm font-black text-slate-800">Pilih Siswa Terlebih Dahulu</h4>
                <p class="text-xs text-slate-500 font-semibold mt-1">Pilih salah satu siswa dari daftar kelas di atas untuk melihat dan menambahkan catatan prestasi, pembinaan, atau perkembangan.</p>
            </div>
        </div>
    @endif
</div>
@endsection
