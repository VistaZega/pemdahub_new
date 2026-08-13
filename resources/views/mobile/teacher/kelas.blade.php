@extends('mobile.layouts.app')

@section('title', 'My Class - Kelas Saya (Guru Mobile)')

@section('content')
<div class="space-y-4" x-data="{ 
    filter: 'all', 
    selectedClassId: null,
    searchStudent: ''
}">
    <!-- Header Title -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-black text-slate-900">Kelas Saya (My Class) 🏫</h2>
                @if(isset($activeAY) && $activeAY)
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black border border-emerald-300">
                        TP {{ $activeAY->name ?? $activeAY->year }}
                    </span>
                @endif
            </div>
            <p class="text-[11px] text-slate-500 font-bold">Daftar Rombel & Siswa yang Saya Ajar / Walikan</p>
        </div>
        <div class="w-10 h-10 rounded-2xl clay-blue flex items-center justify-center text-xl font-black text-white shrink-0">
            🏫
        </div>
    </div>

    <!-- VIEW 1: TAMPILAN UTAMA - CARD DAFTAR KELAS -->
    <div x-show="selectedClassId === null" x-transition class="space-y-4">
        <!-- Filter Tabs -->
        <div class="grid grid-cols-3 gap-1.5 p-1.5 bg-slate-200/70 rounded-2xl border-2 border-slate-300">
            <button @click="filter = 'all'"
                    :class="filter === 'all' ? 'bg-amber-400 text-slate-900 border-2 border-black shadow-md font-black' : 'text-slate-600 font-extrabold hover:text-slate-900'"
                    class="py-2.5 px-1 text-[10px] sm:text-xs transition flex items-center justify-center gap-1">
                🏫 Semua Kelas
            </button>
            <button @click="filter = 'wali'"
                    :class="filter === 'wali' ? 'bg-emerald-600 text-white border-2 border-black shadow-md font-black' : 'text-slate-600 font-extrabold hover:text-slate-900'"
                    class="py-2.5 px-1 text-[10px] sm:text-xs transition flex items-center justify-center gap-1">
                ⭐ Saya Wali Kelas
            </button>
            <button @click="filter = 'ajar'"
                    :class="filter === 'ajar' ? 'bg-blue-600 text-white border-2 border-black shadow-md font-black' : 'text-slate-600 font-extrabold hover:text-slate-900'"
                    class="py-2.5 px-1 text-[10px] sm:text-xs transition flex items-center justify-center gap-1">
                🎓 Saya Mengajar
            </button>
        </div>

        <!-- Cards List -->
        <div class="space-y-3">
            @forelse($classrooms as $cls)
                @php
                    $isWali = $cls->is_homeroom ?? ($teacher && $cls->homeroom_teacher_id == $teacher->id);
                    $isAjar = $cls->is_teaching ?? true;
                @endphp
                <div x-show="(filter === 'all') || (filter === 'wali' && {{ $isWali ? 'true' : 'false' }}) || (filter === 'ajar' && {{ $isAjar ? 'true' : 'false' }})"
                     @click="selectedClassId = {{ $cls->id }}"
                     class="clay-card p-5 cursor-pointer hover:border-blue-400 transition bg-white border-2 border-slate-200 active:scale-[0.98]">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3.5">
                            <div class="w-12 h-12 rounded-2xl {{ $isWali ? 'clay-emerald text-white' : 'clay-blue text-white' }} flex items-center justify-center text-xl font-black shadow-md shrink-0">
                                {{ $isWali ? '⭐' : '🏫' }}
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 leading-snug">{{ $cls->class_name }}</h3>
                                <span class="text-[10px] font-bold text-slate-500 block mt-0.5">
                                    {{ $cls->students_count ?? count($cls->students ?? []) }} Siswa Terdaftar
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-col items-end gap-1.5">
                            @if($isWali)
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 text-[9px] font-black uppercase shadow-xs">
                                    Wali Kelas
                                </span>
                            @endif
                            @if($isAjar && !$isWali)
                                <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 border border-blue-300 text-[9px] font-black uppercase shadow-xs">
                                    Guru Mengajar
                                </span>
                            @endif
                            <span class="text-slate-400 text-xs font-black">
                                Lihat Siswa &rarr;
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                    <div class="text-3xl">🏫</div>
                    <p>Belum ada daftar kelas terdaftar untuk Bapak/Ibu Guru pada Tahun Pelajaran Aktif.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- VIEW 2: DETAIL DAFTAR SISWA KETIKA CARD KELAS DIKLIK -->
    @foreach($classrooms as $cls)
        <div x-show="selectedClassId === {{ $cls->id }}" x-transition class="space-y-4">
            <!-- Back Navigation Header -->
            <div class="flex items-center justify-between bg-slate-100 p-3 rounded-2xl border border-slate-200">
                <button @click="selectedClassId = null; searchStudent = ''" 
                        class="px-3 py-1.5 rounded-xl bg-white border border-slate-300 text-slate-700 text-xs font-black flex items-center gap-1.5 hover:bg-slate-50 shadow-xs">
                    &larr; Kembali ke Daftar Kelas
                </button>
                <span class="text-xs font-black text-slate-900">
                    Kelas: {{ $cls->class_name }}
                </span>
            </div>

            <!-- Student Class Title & Count -->
            <div class="clay-card p-4 bg-gradient-to-br from-blue-600 to-indigo-700 text-white flex items-center justify-between">
                <div>
                    <h3 class="text-base font-black">{{ $cls->class_name }}</h3>
                    <p class="text-[10px] font-bold text-blue-100">
                        {{ $cls->students_count ?? count($cls->students ?? []) }} Siswa Terdaftar
                    </p>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center text-xl font-black">
                    🎓
                </div>
            </div>

            <!-- Student Search Input -->
            <div class="relative">
                <input type="text" x-model="searchStudent" placeholder="Cari nama siswa atau NIS..."
                       class="w-full pl-9 pr-4 py-2.5 bg-white border-2 border-slate-200 rounded-2xl text-xs font-bold text-slate-900 outline-none focus:border-blue-500 transition">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400 text-xs"></i>
            </div>

            <!-- Student Roster List -->
            <div class="space-y-2.5">
                @forelse($cls->students as $index => $std)
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
                    <div x-show="!searchStudent || '{{ strtolower($std->full_name) }}'.includes(searchStudent.toLowerCase()) || '{{ $std->nis }}'.includes(searchStudent)"
                         class="clay-card p-3.5 flex items-center justify-between bg-white border border-slate-200">
                        <div class="flex items-center space-x-3">
                            <!-- Student Avatar -->
                            <img src="{{ $studentPhoto }}" alt="{{ $std->full_name }}"
                                 onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($std->full_name) }}&background=2563eb&color=fff&bold=true';"
                                 class="w-11 h-11 rounded-2xl object-cover border-2 border-blue-200 shadow-sm shrink-0">

                            <div>
                                <h4 class="text-xs font-black text-slate-900 uppercase leading-snug">{{ $std->full_name }}</h4>
                                <span class="text-[10px] font-bold text-slate-500 block mt-0.5">
                                    NIS: {{ $std->nis ?? '-' }}
                                </span>
                            </div>
                        </div>

                        <div class="text-right shrink-0">
                            <span class="text-[10px] font-black px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $std->gender === 'P' || $std->gender === 'perempuan' ? '👧 Perempuan' : '👦 Laki-laki' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                        <div class="text-3xl">🎓</div>
                        <p>Belum ada siswa terdaftar di kelas {{ $cls->class_name }}.</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endforeach
</div>
@endsection
