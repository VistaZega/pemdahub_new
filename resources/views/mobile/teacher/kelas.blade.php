@extends('mobile.layouts.app')

@section('title', 'My Class - Kelas & Siswa Saya (Guru Mobile)')

@section('content')
<div class="space-y-4" x-data="{ activeTab: 'ajar', search: '' }">
    <!-- Header Title -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-black text-slate-900">Kelas & Siswa Saya 🏫</h2>
                @if(isset($activeAY) && $activeAY)
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black border border-emerald-300">
                        TP {{ $activeAY->name ?? $activeAY->year }}
                    </span>
                @endif
            </div>
            <p class="text-[11px] text-slate-500 font-bold">Daftar Siswa Ajar & Perwalian (Tahun Pelajaran Aktif)</p>
        </div>
        <div class="w-10 h-10 rounded-2xl clay-blue flex items-center justify-center text-xl font-black text-white shrink-0">
            👨‍🏫
        </div>
    </div>

    <!-- Stats Summary Row -->
    <div class="grid grid-cols-2 gap-3">
        <div class="clay-card p-4 bg-gradient-to-br from-blue-600 to-indigo-700 text-white space-y-1">
            <span class="text-[10px] font-black uppercase text-blue-200 block">Siswa Saya Ajar</span>
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-black">{{ count($taughtStudents) }}</h3>
                <span class="text-xl">🎓</span>
            </div>
            <p class="text-[9px] font-bold text-blue-100">{{ count($teachingClasses) }} Rombel Mengajar</p>
        </div>

        <div class="clay-card p-4 bg-gradient-to-br from-emerald-600 to-teal-700 text-white space-y-1">
            <span class="text-[10px] font-black uppercase text-emerald-200 block">Siswa Anak Wali</span>
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-black">{{ count($homeroomStudents) }}</h3>
                <span class="text-xl">⭐</span>
            </div>
            <p class="text-[9px] font-bold text-emerald-100">{{ count($homeroomClasses) }} Rombel Perwalian</p>
        </div>
    </div>

    <!-- Category Tabs -->
    <div class="grid grid-cols-3 gap-1.5 p-1.5 bg-slate-200/70 rounded-2xl border-2 border-slate-300">
        <button @click="activeTab = 'ajar'"
                :class="activeTab === 'ajar' ? 'bg-blue-600 text-white border-2 border-black shadow-md font-black' : 'text-slate-600 font-extrabold hover:text-slate-900'"
                class="py-2.5 px-1 text-[10px] sm:text-xs transition flex items-center justify-center gap-1">
            🎓 Siswa Ajar
        </button>
        <button @click="activeTab = 'wali'"
                :class="activeTab === 'wali' ? 'bg-emerald-600 text-white border-2 border-black shadow-md font-black' : 'text-slate-600 font-extrabold hover:text-slate-900'"
                class="py-2.5 px-1 text-[10px] sm:text-xs transition flex items-center justify-center gap-1">
            ⭐ Anak Wali
        </button>
        <button @click="activeTab = 'rombel'"
                :class="activeTab === 'rombel' ? 'bg-amber-400 text-slate-900 border-2 border-black shadow-md font-black' : 'text-slate-600 font-extrabold hover:text-slate-900'"
                class="py-2.5 px-1 text-[10px] sm:text-xs transition flex items-center justify-center gap-1">
            🏫 Rombel
        </button>
    </div>

    <!-- Search Input -->
    <div x-show="activeTab !== 'rombel'" class="relative">
        <input type="text" x-model="search" placeholder="Cari nama siswa atau NIS..."
               class="w-full pl-9 pr-4 py-2.5 bg-white border-2 border-slate-200 rounded-2xl text-xs font-bold text-slate-900 outline-none focus:border-blue-500 transition">
        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400 text-xs"></i>
    </div>

    <!-- TAB 1: SISWA YANG SAYA AJAR -->
    <div x-show="activeTab === 'ajar'" x-transition class="space-y-3">
        <h3 class="text-xs font-black text-slate-600 uppercase tracking-wider px-1 flex items-center gap-1.5">
            <i class="fa-solid fa-user-graduate text-blue-600"></i> Daftar Siswa Yang Saya Ajar ({{ count($taughtStudents) }})
        </h3>

        @forelse($taughtStudents as $std)
            @php
                $studentPhoto = $std->photo_url ?? null;
                if (!$studentPhoto || str_contains($studentPhoto, 'default-student.jpg') || str_contains($studentPhoto, 'default-avatar')) {
                    if (isset($std->user->avatar_url) && $std->user->avatar_url) {
                        $studentPhoto = $std->user->avatar_url;
                    } else {
                        $studentPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($std->full_name) . '&background=2563eb&color=fff&bold=true';
                    }
                }
            @endphp
            <div x-show="!search || '{{ strtolower($std->full_name) }}'.includes(search.toLowerCase()) || '{{ $std->nis }}'.includes(search)"
                 class="clay-card p-4 flex items-center justify-between bg-white border-2 border-slate-200">
                <div class="flex items-center space-x-3.5">
                    <img src="{{ $studentPhoto }}" alt="{{ $std->full_name }}"
                         onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($std->full_name) }}&background=2563eb&color=fff&bold=true';"
                         class="w-12 h-12 rounded-2xl object-cover border-2 border-blue-200 shadow-md shrink-0">

                    <div>
                        <div class="flex items-center gap-1.5">
                            <h4 class="text-xs font-black text-slate-900 uppercase leading-snug">{{ $std->full_name }}</h4>
                            <span class="text-[9px] font-black px-1.5 py-0.5 rounded-md bg-blue-100 text-blue-800 border border-blue-200">
                                {{ $std->gender === 'P' || $std->gender === 'perempuan' ? '👧' : '👦' }}
                            </span>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500 block mt-0.5">
                            NIS: {{ $std->nis ?? '-' }} &bull; <strong class="text-blue-600">{{ $std->classroom_name ?? 'Kelas' }}</strong>
                        </span>
                    </div>
                </div>

                @if($std->parent_phone || $std->phone)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $std->parent_phone ?: $std->phone) }}" target="_blank"
                       class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center text-sm font-black shadow-xs hover:bg-emerald-100 transition shrink-0">
                        <i class="fa-brands fa-whatsapp"></i>
                    </a>
                @endif
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-3xl">🎓</div>
                <p>Belum ada data siswa yang diajar terdaftar.</p>
            </div>
        @endforelse
    </div>

    <!-- TAB 2: SISWA ANAK WALI (WALI KELAS) -->
    <div x-show="activeTab === 'wali'" x-transition class="space-y-3">
        <h3 class="text-xs font-black text-slate-600 uppercase tracking-wider px-1 flex items-center gap-1.5">
            <i class="fa-solid fa-star text-emerald-600"></i> Daftar Siswa Kelas Perwalian Saya ({{ count($homeroomStudents) }})
        </h3>

        @forelse($homeroomStudents as $std)
            @php
                $studentPhoto = $std->photo_url ?? null;
                if (!$studentPhoto || str_contains($studentPhoto, 'default-student.jpg') || str_contains($studentPhoto, 'default-avatar')) {
                    if (isset($std->user->avatar_url) && $std->user->avatar_url) {
                        $studentPhoto = $std->user->avatar_url;
                    } else {
                        $studentPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($std->full_name) . '&background=059669&color=fff&bold=true';
                    }
                }
            @endphp
            <div x-show="!search || '{{ strtolower($std->full_name) }}'.includes(search.toLowerCase()) || '{{ $std->nis }}'.includes(search)"
                 class="clay-card p-4 flex items-center justify-between bg-white border-2 border-emerald-200">
                <div class="flex items-center space-x-3.5">
                    <img src="{{ $studentPhoto }}" alt="{{ $std->full_name }}"
                         onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($std->full_name) }}&background=059669&color=fff&bold=true';"
                         class="w-12 h-12 rounded-2xl object-cover border-2 border-emerald-300 shadow-md shrink-0">

                    <div>
                        <div class="flex items-center gap-1.5">
                            <h4 class="text-xs font-black text-slate-900 uppercase leading-snug">{{ $std->full_name }}</h4>
                            <span class="text-[9px] font-black px-1.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800 border border-emerald-200">
                                Wali
                            </span>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500 block mt-0.5">
                            NIS: {{ $std->nis ?? '-' }} &bull; <strong class="text-emerald-700">{{ $std->classroom_name ?? 'Wali Kelas' }}</strong>
                        </span>
                    </div>
                </div>

                @if($std->parent_phone || $std->phone)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $std->parent_phone ?: $std->phone) }}" target="_blank"
                       class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center text-sm font-black shadow-xs hover:bg-emerald-100 transition shrink-0">
                        <i class="fa-brands fa-whatsapp"></i>
                    </a>
                @endif
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-3xl">⭐</div>
                <p>Anda belum terdaftar sebagai Wali Kelas pada Rombel aktif.</p>
            </div>
        @endforelse
    </div>

    <!-- TAB 3: DAFTAR ROMBEL / KELAS -->
    <div x-show="activeTab === 'rombel'" x-transition class="space-y-3">
        <h3 class="text-xs font-black text-slate-600 uppercase tracking-wider px-1 flex items-center gap-1.5">
            <i class="fa-solid fa-school text-amber-500"></i> Rombel & Kelas Terkait ({{ count($allClassrooms) }})
        </h3>

        @forelse($allClassrooms as $cls)
            <div class="clay-card p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 rounded-2xl clay-blue flex items-center justify-center text-white text-xl font-black shadow-md">
                            🏫
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 leading-snug">{{ $cls->class_name }}</h3>
                            <span class="text-[10px] font-bold text-slate-500 block">
                                {{ $cls->students_count ?? count($cls->students ?? []) }} Siswa Terdaftar
                            </span>
                        </div>
                    </div>

                    @if($teacher && $cls->homeroom_teacher_id == $teacher->id)
                        <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200 text-[9px] font-black uppercase shadow-xs">
                            Wali Kelas
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 border border-blue-200 text-[9px] font-black uppercase shadow-xs">
                            Pengajar
                        </span>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                    <a href="{{ route('mobile.guru.absensi.input', ['classroom_id' => $cls->id]) }}" 
                       class="py-2.5 px-3 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-black text-center hover:bg-emerald-100 transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-clipboard-user text-xs"></i> Absen Siswa
                    </a>
                    <a href="{{ route('mobile.lms.index') }}" 
                       class="py-2.5 px-3 rounded-xl bg-purple-50 text-purple-700 border border-purple-200 text-[11px] font-black text-center hover:bg-purple-100 transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-book-open text-xs"></i> Modul LMS
                    </a>
                </div>
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-3xl">🏫</div>
                <p>Belum ada rincian kelas mengajar yang terhubung.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
