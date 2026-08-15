@extends('mobile.layouts.app')

@section('title', 'LMS Digital PembdaHUB - Kursus & Mata Pelajaran')

@php
    // Helper function to determine icon, gradient, and accents based on subject or course title
    function getSubjectDesign($title, $subjectName = '') {
        $haystack = strtolower($title . ' ' . $subjectName);

        if (preg_match('/(matematika|aljabar|kalkulus|hitung|statistika|math)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-calculator',
                'emoji' => '📐',
                'gradient' => 'from-emerald-600 via-teal-600 to-cyan-700',
                'badgeBg' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'accentColor' => 'emerald',
                'category' => 'Eksak & Hitungan',
            ];
        }

        if (preg_match('/(indonesia|sastra|bahasa id)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-book-open-reader',
                'emoji' => '📖',
                'gradient' => 'from-rose-600 via-red-600 to-pink-700',
                'badgeBg' => 'bg-rose-50 text-rose-800 border-rose-200',
                'accentColor' => 'rose',
                'category' => 'Bahasa & Sastra',
            ];
        }

        if (preg_match('/(inggris|english|toefl|foreign)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-earth-americas',
                'emoji' => '🌐',
                'gradient' => 'from-blue-600 via-indigo-600 to-sky-700',
                'badgeBg' => 'bg-blue-50 text-blue-800 border-blue-200',
                'accentColor' => 'blue',
                'category' => 'Bahasa Asing',
            ];
        }

        if (preg_match('/(fisika|physics)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-atom',
                'emoji' => '⚛️',
                'gradient' => 'from-cyan-600 via-blue-600 to-indigo-800',
                'badgeBg' => 'bg-cyan-50 text-cyan-800 border-cyan-200',
                'accentColor' => 'cyan',
                'category' => 'Sains Fisika',
            ];
        }

        if (preg_match('/(kimia|chemistry)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-flask-vial',
                'emoji' => '🧪',
                'gradient' => 'from-purple-600 via-violet-600 to-fuchsia-800',
                'badgeBg' => 'bg-purple-50 text-purple-800 border-purple-200',
                'accentColor' => 'purple',
                'category' => 'Sains Kimia',
            ];
        }

        if (preg_match('/(biologi|ipa|anatomi|lingkungan|natural)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-dna',
                'emoji' => '🧬',
                'gradient' => 'from-emerald-700 via-green-600 to-teal-800',
                'badgeBg' => 'bg-green-50 text-green-800 border-green-200',
                'accentColor' => 'green',
                'category' => 'Sains & Hayati',
            ];
        }

        if (preg_match('/(informatika|rpl|tkj|komputer|coding|pemrograman|basis data|database|jaringan|network|web|it|software|hardware|multimedia|desain grafis)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-laptop-code',
                'emoji' => '💻',
                'gradient' => 'from-indigo-700 via-slate-800 to-blue-950',
                'badgeBg' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
                'accentColor' => 'indigo',
                'category' => 'Teknologi & IT',
            ];
        }

        if (preg_match('/(sejarah|history|ips|sosiologi|antropologi)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-landmark-dome',
                'emoji' => '🏛️',
                'gradient' => 'from-amber-700 via-amber-600 to-yellow-800',
                'badgeBg' => 'bg-amber-50 text-amber-800 border-amber-200',
                'accentColor' => 'amber',
                'category' => 'Sosial & Humaniora',
            ];
        }

        if (preg_match('/(geografi|bumi|kebumian)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-globe',
                'emoji' => '🌍',
                'gradient' => 'from-teal-700 via-emerald-600 to-sky-800',
                'badgeBg' => 'bg-teal-50 text-teal-800 border-teal-200',
                'accentColor' => 'teal',
                'category' => 'Geografi & Bumi',
            ];
        }

        if (preg_match('/(ekonomi|akuntansi|keuangan|bisnis|manajemen|marketing|pemasaran)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-chart-line',
                'emoji' => '📊',
                'gradient' => 'from-emerald-700 via-teal-600 to-slate-800',
                'badgeBg' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'accentColor' => 'emerald',
                'category' => 'Ekonomi & Bisnis',
            ];
        }

        if (preg_match('/(agama|pak|pab|kristen|katolik|islam|budi pekerti|spiritual)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-hands-praying',
                'emoji' => '🙏',
                'gradient' => 'from-sky-600 via-indigo-600 to-teal-700',
                'badgeBg' => 'bg-sky-50 text-sky-800 border-sky-200',
                'accentColor' => 'sky',
                'category' => 'Pendidikan Keagamaan',
            ];
        }

        if (preg_match('/(ppkn|pkn|pancasila|kewarganegaraan|hukum)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-shield-halved',
                'emoji' => '🇮🇩',
                'gradient' => 'from-red-600 via-rose-700 to-slate-900',
                'badgeBg' => 'bg-red-50 text-red-800 border-red-200',
                'accentColor' => 'red',
                'category' => 'Kewarganegaraan',
            ];
        }

        if (preg_match('/(seni|musik|rupa|tari|teater|budaya|prakarya|kerajinan)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-palette',
                'emoji' => '🎨',
                'gradient' => 'from-pink-600 via-fuchsia-600 to-purple-800',
                'badgeBg' => 'bg-pink-50 text-pink-800 border-pink-200',
                'accentColor' => 'pink',
                'category' => 'Seni & Budaya',
            ];
        }

        if (preg_match('/(pjok|penjas|olahraga|atletik|kebugaran)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-person-running',
                'emoji' => '⚽',
                'gradient' => 'from-orange-600 via-amber-600 to-rose-700',
                'badgeBg' => 'bg-orange-50 text-orange-800 border-orange-200',
                'accentColor' => 'orange',
                'category' => 'Olahraga & Kesehatan',
            ];
        }

        if (preg_match('/(otomotif|tkr|tbsm|sepeda motor|mobil|mesin bubut|bengkel)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-wrench',
                'emoji' => '🔧',
                'gradient' => 'from-slate-700 via-zinc-800 to-neutral-900',
                'badgeBg' => 'bg-slate-100 text-slate-800 border-slate-300',
                'accentColor' => 'slate',
                'category' => 'Teknik Otomotif',
            ];
        }

        if (preg_match('/(listrik|elektronika|mekatronika|kelistrikan|pln)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-bolt-lightning',
                'emoji' => '⚡',
                'gradient' => 'from-amber-600 via-orange-600 to-yellow-700',
                'badgeBg' => 'bg-amber-50 text-amber-800 border-amber-200',
                'accentColor' => 'amber',
                'category' => 'Teknik Elektro',
            ];
        }

        if (preg_match('/(hotel|perhotelan|pariwisata|tata boga|kuliner|food|beverage|restoran)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-bell-concierge',
                'emoji' => '🛎️',
                'gradient' => 'from-rose-600 via-orange-600 to-amber-700',
                'badgeBg' => 'bg-rose-50 text-rose-800 border-rose-200',
                'accentColor' => 'rose',
                'category' => 'Pariwisata & Kuliner',
            ];
        }

        if (preg_match('/(busana|fashion|menjahit|tekstil)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-scissors',
                'emoji' => '👗',
                'gradient' => 'from-fuchsia-600 via-pink-600 to-purple-700',
                'badgeBg' => 'bg-fuchsia-50 text-fuchsia-800 border-fuchsia-200',
                'accentColor' => 'fuchsia',
                'category' => 'Tata Busana',
            ];
        }

        if (preg_match('/(bk|konseling|psikologi|karir)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-heart-pulse',
                'emoji' => '💖',
                'gradient' => 'from-rose-500 via-pink-500 to-purple-600',
                'badgeBg' => 'bg-rose-50 text-rose-800 border-rose-200',
                'accentColor' => 'rose',
                'category' => 'Bimbingan Konseling',
            ];
        }

        if (preg_match('/(ipas|pkk|projek kreatif|kewirausahaan|entrepreneur)/i', $haystack)) {
            return [
                'icon' => 'fa-solid fa-lightbulb',
                'emoji' => '💡',
                'gradient' => 'from-amber-600 via-teal-600 to-indigo-700',
                'badgeBg' => 'bg-amber-50 text-amber-800 border-amber-200',
                'accentColor' => 'amber',
                'category' => 'Projek & Inovasi',
            ];
        }

        // Default Subject Card Theme
        return [
            'icon' => 'fa-solid fa-graduation-cap',
            'emoji' => '🎓',
            'gradient' => 'from-purple-700 via-indigo-600 to-blue-700',
            'badgeBg' => 'bg-purple-50 text-purple-800 border-purple-200',
            'accentColor' => 'purple',
            'category' => 'Mata Pelajaran Umum',
        ];
    }

    $totalMaterials = $enrolledCourses->sum('materials_count');
    $totalAssignments = $enrolledCourses->sum('assignments_count');
    $totalQuizzes = $enrolledCourses->sum('quizzes_count');
@endphp

<div class="space-y-4 pb-8" x-data="{ 
    showAddCourse: false, 
    searchQuery: '',
    selectedCategory: 'all'
}">
    <!-- Header Navigation -->
    <div class="flex items-center justify-between px-1">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-purple-600 text-white flex items-center justify-center shadow-xs">
                    <i class="fa-solid fa-book-bookmark text-sm"></i>
                </span>
                <div>
                    <h2 class="text-base font-black text-slate-900 leading-tight">LMS Digital</h2>
                    <p class="text-[10px] text-slate-500 font-bold">Ruang Belajar & Materi Interaktif</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($isTeacher)
                <button @click="showAddCourse = !showAddCourse" 
                        class="px-3 py-2 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-xs font-black shadow-md hover:opacity-90 active:scale-95 transition flex items-center gap-1.5">
                    <i class="fa-solid" :class="showAddCourse ? 'fa-xmark' : 'fa-plus'"></i>
                    <span x-text="showAddCourse ? 'Batal' : '+ Kelas'"></span>
                </button>
            @endif
            <a href="{{ route('mobile.lms.catalog') }}" 
               class="px-3 py-2 rounded-xl bg-white border-2 border-purple-200 text-purple-700 text-xs font-black hover:bg-purple-50 active:scale-95 transition flex items-center gap-1.5 shadow-2xs">
                <i class="fa-solid fa-compass text-purple-600"></i>
                <span>Katalog</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Banner Clay Card -->
    <div class="clay-purple p-4.5 space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-black uppercase tracking-wider bg-white/25 px-2.5 py-0.5 rounded-full border border-white/30 text-white">
                {{ $isTeacher ? '👨‍🏫 Panel Pengajar LMS' : '👨‍🎓 Ruang Belajar Siswa' }}
            </span>
            <span class="text-[10px] font-black text-purple-100 bg-white/15 px-2 py-0.5 rounded-full">
                {{ $enrolledCourses->count() }} Kelas Aktif
            </span>
        </div>

        <div class="grid grid-cols-3 gap-2 pt-1 text-center">
            <div class="p-2 rounded-xl bg-white/15 backdrop-blur-xs border border-white/25">
                <span class="text-lg font-black text-white leading-none block">{{ $enrolledCourses->count() }}</span>
                <span class="text-[9px] font-bold text-purple-100 uppercase tracking-tight mt-0.5 block">Mapel</span>
            </div>
            <div class="p-2 rounded-xl bg-white/15 backdrop-blur-xs border border-white/25">
                <span class="text-lg font-black text-white leading-none block">{{ $totalMaterials }}</span>
                <span class="text-[9px] font-bold text-purple-100 uppercase tracking-tight mt-0.5 block">Materi</span>
            </div>
            <div class="p-2 rounded-xl bg-white/15 backdrop-blur-xs border border-white/25">
                <span class="text-lg font-black text-white leading-none block">{{ $totalAssignments + $totalQuizzes }}</span>
                <span class="text-[9px] font-bold text-purple-100 uppercase tracking-tight mt-0.5 block">Tugas & Kuis</span>
            </div>
        </div>
    </div>

    <!-- Teacher: Form Tambah Kelas LMS Baru (Collapsible Slide-Down) -->
    @if($isTeacher)
        <div x-show="showAddCourse" x-collapse class="clay-card p-5 space-y-3.5 bg-gradient-to-b from-purple-50 to-white border-2 border-purple-300 shadow-md">
            <div class="flex items-center justify-between border-b border-purple-100 pb-2">
                <h3 class="text-xs font-black text-purple-950 flex items-center gap-2">
                    <i class="fa-solid fa-square-plus text-purple-600 text-sm"></i> Buat Kursus / Kelas LMS Baru
                </h3>
                <span class="text-[9px] font-extrabold text-purple-700 bg-purple-100 px-2 py-0.5 rounded-full">Khusus Guru</span>
            </div>

            <form action="{{ route('mobile.lms.course.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-[11px] font-black text-slate-800 mb-1">Nama Kelas / Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="course_name" required placeholder="Contoh: Pemrograman Web & Perangkat Bergerak XII RPL" 
                           class="w-full px-3.5 py-2.5 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-hidden focus:border-purple-600 transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[11px] font-black text-slate-800 mb-1">Rombongan Belajar (Rombel)</label>
                        <select name="classroom_id" class="w-full px-3 py-2.5 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-hidden focus:border-purple-600">
                            <option value="">-- Pilih Rombel Mengajar --</option>
                            @foreach($classrooms as $cls)
                                <option value="{{ $cls->id }}">{{ $cls->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-slate-800 mb-1">Mata Pelajaran Induk</label>
                        <select name="subject_id" class="w-full px-3 py-2.5 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-hidden focus:border-purple-600">
                            <option value="">-- Pilih Mapel --</option>
                            @foreach($subjects as $subj)
                                <option value="{{ $subj->id }}">{{ $subj->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-black text-slate-800 mb-1">Kode Kelas (Opsional)</label>
                    <input type="text" name="code" placeholder="Kosongkan untuk generate otomatis (cth: LMS-XXXX)" 
                           class="w-full px-3.5 py-2.5 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-hidden focus:border-purple-600">
                </div>

                <div>
                    <label class="block text-[11px] font-black text-slate-800 mb-1">Deskripsi Ringkas Kelas</label>
                    <textarea name="description" rows="2" placeholder="Tuliskan tujuan pembelajaran, capaian modul, atau instruksi umum kelas..." 
                              class="w-full px-3.5 py-2 bg-white border-2 border-purple-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-hidden focus:border-purple-600 resize-none"></textarea>
                </div>

                <div class="p-3 bg-purple-100/70 rounded-xl border border-purple-300 flex items-start gap-2.5">
                    <input type="checkbox" id="is_sequential" name="is_sequential" value="1" class="mt-0.5 w-4 h-4 text-purple-600 rounded border-purple-400 focus:ring-purple-500 cursor-pointer">
                    <label for="is_sequential" class="text-[11px] font-bold text-purple-950 cursor-pointer select-none">
                        <span class="font-black text-purple-900 flex items-center gap-1">
                            <i class="fa-solid fa-lock text-purple-700"></i> Mode Pembelajaran Bertahap (Sequential Lock)
                        </span>
                        Siswa wajib menyelesaikan materi/modul satu per satu secara berurutan sebelum membuka bab selanjutnya.
                    </label>
                </div>

                <button type="submit" class="w-full py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-black text-xs rounded-xl shadow-md hover:from-purple-700 hover:to-indigo-700 active:scale-98 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan & Publikasikan Kelas LMS
                </button>
            </form>
        </div>
    @endif

    <!-- Search & Filter Controls -->
    <div class="clay-card p-3 space-y-2.5 bg-white border border-slate-200">
        <!-- Live Search Input -->
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input type="text" 
                   x-model="searchQuery" 
                   placeholder="Cari nama mapel, guru pengampu, kode kelas..." 
                   class="w-full pl-9 pr-8 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 placeholder:text-slate-400 focus:outline-hidden focus:border-purple-500 focus:bg-white transition">
            <button type="button" x-show="searchQuery" @click="searchQuery = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>

    <!-- Course Cards Section -->
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                <i class="fa-solid fa-graduation-cap text-purple-600"></i>
                {{ $isTeacher ? 'Mata Pelajaran Ampuan Guru' : 'Daftar Mata Pelajaran Siswa' }}
            </h3>
            <span class="text-[10px] font-extrabold text-slate-500">
                Total: {{ $enrolledCourses->count() }} Kelas
            </span>
        </div>

        <div class="grid grid-cols-1 gap-3.5">
            @forelse($enrolledCourses as $course)
                @php
                    $subjectName = $course->subject->name ?? '';
                    $theme = getSubjectDesign($course->course_name, $subjectName);
                    $progress = $courseProgress[$course->id] ?? 0;
                    $teacherName = $course->teacher->full_name ?? ($course->teacher->user->name ?? 'Guru Pengampu');
                    $className = $course->classroom->class_name ?? 'Semua Kelas';
                @endphp

                <!-- Dynamic Course Card with Search Filter -->
                <div class="clay-card overflow-hidden border-2 border-slate-200/90 hover:border-purple-400 transition-all duration-200 shadow-sm hover:shadow-md group bg-white"
                     x-show="!searchQuery || '{{ strtolower(addslashes($course->course_name . ' ' . $subjectName . ' ' . $teacherName . ' ' . $course->code . ' ' . $className)) }}'.includes(searchQuery.toLowerCase())">
                    
                    <!-- Card Top Thematic Gradient Banner -->
                    <div class="bg-gradient-to-r {{ $theme['gradient'] }} p-4 text-white relative overflow-hidden">
                        <!-- Subtle Background Watermark Graphic -->
                        <div class="absolute -right-3 -bottom-4 text-white/10 text-6xl pointer-events-none transform -rotate-12">
                            <i class="{{ $theme['icon'] }}"></i>
                        </div>

                        <!-- Top Floating Badges -->
                        <div class="flex items-center justify-between gap-2 relative z-10 mb-2.5">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-white/20 backdrop-blur-xs text-white border border-white/30 shadow-2xs">
                                    {{ $course->code }}
                                </span>
                                @if($course->classroom)
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-black/25 backdrop-blur-xs text-white border border-white/20">
                                        <i class="fa-solid fa-users-rectangle text-[8px] mr-0.5"></i> {{ $course->classroom->class_name }}
                                    </span>
                                @endif
                            </div>

                            @if($course->is_sequential)
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-amber-400/90 text-amber-950 border border-amber-300 shadow-2xs flex items-center gap-1 shrink-0">
                                    <i class="fa-solid fa-lock text-[8px]"></i> Bertahap
                                </span>
                            @endif
                        </div>

                        <!-- Main Icon & Course Name Row -->
                        <div class="flex items-start gap-3 relative z-10">
                            <!-- 3D Style Glossy Thematic Icon -->
                            <div class="w-13 h-13 rounded-2xl bg-white/20 backdrop-blur-md border border-white/35 shadow-md flex items-center justify-center text-white text-2xl group-hover:scale-108 transition-transform duration-200 shrink-0">
                                <i class="{{ $theme['icon'] }}"></i>
                            </div>

                            <!-- Course Title & Category -->
                            <div class="min-w-0 flex-1">
                                <span class="text-[9px] font-extrabold uppercase tracking-wide text-white/80 block">
                                    {{ $theme['emoji'] }} {{ $theme['category'] }}
                                </span>
                                <h4 class="text-sm font-black text-white leading-snug drop-shadow-xs line-clamp-2 mt-0.5">
                                    {{ $course->course_name }}
                                </h4>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body Content -->
                    <div class="p-4 space-y-3">
                        <!-- Instructor Info & Description -->
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 font-black text-[10px] flex items-center justify-center border border-purple-200 shrink-0">
                                    {{ strtoupper(substr($teacherName, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[11px] font-black text-slate-800 truncate">{{ $teacherName }}</p>
                                    <p class="text-[9px] font-bold text-slate-400">Guru Pengampu</p>
                                </div>
                            </div>

                            @if($course->subject)
                                <span class="px-2 py-0.5 rounded-lg text-[9px] font-black {{ $theme['badgeBg'] }} border shrink-0">
                                    {{ $course->subject->name }}
                                </span>
                            @endif
                        </div>

                        @if($course->description)
                            <p class="text-[11px] text-slate-600 font-semibold line-clamp-2 leading-relaxed bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                {{ $course->description }}
                            </p>
                        @endif

                        <!-- Key Metrics Grid -->
                        <div class="grid grid-cols-4 gap-1.5 pt-1 text-center">
                            <div class="p-2 rounded-xl bg-slate-50 border border-slate-200/80">
                                <span class="text-xs font-black text-slate-800 block">{{ $course->materials_count }}</span>
                                <span class="text-[8px] font-black text-slate-500 uppercase">Materi</span>
                            </div>
                            <div class="p-2 rounded-xl bg-slate-50 border border-slate-200/80">
                                <span class="text-xs font-black text-slate-800 block">{{ $course->assignments_count }}</span>
                                <span class="text-[8px] font-black text-slate-500 uppercase">Tugas</span>
                            </div>
                            <div class="p-2 rounded-xl bg-slate-50 border border-slate-200/80">
                                <span class="text-xs font-black text-slate-800 block">{{ $course->quizzes_count }}</span>
                                <span class="text-[8px] font-black text-slate-500 uppercase">Kuis</span>
                            </div>
                            <div class="p-2 rounded-xl bg-slate-50 border border-slate-200/80">
                                <span class="text-xs font-black text-slate-800 block">{{ $course->modules->count() }}</span>
                                <span class="text-[8px] font-black text-slate-500 uppercase">Modul</span>
                            </div>
                        </div>

                        <!-- Progress Bar (for Students) -->
                        @if(!$isTeacher)
                            <div class="space-y-1 pt-1">
                                <div class="flex items-center justify-between text-[10px] font-black">
                                    <span class="text-slate-600">Progres Pembelajaran:</span>
                                    <span class="{{ $progress == 100 ? 'text-emerald-600' : ($progress > 0 ? 'text-blue-600' : 'text-slate-400') }}">
                                        {{ $progress }}% Selesai
                                    </span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden border border-slate-200">
                                    <div class="h-full rounded-full transition-all duration-500 {{ $progress == 100 ? 'bg-emerald-500' : ($progress > 40 ? 'bg-blue-500' : 'bg-amber-500') }}"
                                         style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                        @endif

                        <!-- Action Footer -->
                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                            @if($isTeacher)
                                <form action="{{ route('mobile.lms.course.destroy', $course->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus seluruh kelas LMS {{ addslashes($course->course_name) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-2 rounded-xl bg-rose-50 text-rose-700 text-[10px] font-black border border-rose-200 hover:bg-rose-100 active:scale-95 transition flex items-center gap-1">
                                        <i class="fa-solid fa-trash-can"></i> Hapus
                                    </button>
                                </form>
                            @else
                                <span class="text-[10px] font-extrabold text-slate-400">
                                    <i class="fa-solid fa-circle-check text-emerald-500 text-[9px] mr-0.5"></i> Terdaftar
                                </span>
                            @endif

                            <a href="{{ route('mobile.lms.show', $course->id) }}" 
                               class="flex-1 py-2.5 px-4 rounded-xl bg-gradient-to-r {{ $theme['gradient'] }} text-white text-xs font-black text-center shadow-md active:scale-98 hover:opacity-95 transition flex items-center justify-center gap-1.5 group-hover:shadow-lg">
                                <span>Masuk Kelas LMS</span>
                                <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="clay-card p-8 text-center text-slate-500 space-y-3 bg-white">
                    <div class="w-16 h-16 rounded-3xl bg-purple-50 border-2 border-purple-200 text-purple-600 text-3xl flex items-center justify-center mx-auto shadow-inner">
                        🎓
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-800">Belum Ada Kelas LMS</h4>
                        <p class="text-xs text-slate-500 font-semibold mt-1">Anda belum memiliki atau mengikuti kelas LMS pada semester aktif saat ini.</p>
                    </div>
                    @if($isTeacher)
                        <button @click="showAddCourse = true" class="clay-btn inline-flex items-center gap-1.5 px-5 py-2.5 text-white text-xs font-black shadow-md">
                            <i class="fa-solid fa-plus"></i> Buat Kelas Pertama
                        </button>
                    @else
                        <a href="{{ route('mobile.lms.catalog') }}" class="clay-btn inline-flex items-center gap-1.5 px-5 py-2.5 text-white text-xs font-black shadow-md">
                            <i class="fa-solid fa-compass"></i> Jelajahi Katalog Kelas
                        </a>
                    @endif
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection


