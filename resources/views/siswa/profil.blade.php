@extends('layouts.siswa')
@section('title', 'Profil Saya - Portal Siswa')

@section('content')
<div class="space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-user text-amber-500"></i> Profil Saya
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Informasi data akademik, pribadi, dan orang tua</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('profile.settings') }}" class="bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-sm transition-all duration-300 flex items-center gap-2">
                <i class="fas fa-shield-alt"></i> Keamanan Akun (Ganti Password)
            </a>
        </div>
    </div>

    {{-- Official Data Notice --}}
    <div class="bg-amber-50/80 border border-amber-200 p-4 rounded-2xl flex items-center gap-3 text-xs text-amber-950 shadow-xs">
        <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center flex-shrink-0 text-base">
            <i class="fas fa-id-badge"></i>
        </div>
        <div class="flex-1">
            <p class="font-bold text-amber-900">Kebijakan Identitas & Foto Profil Siswa Terverifikasi</p>
            <p class="text-amber-800/80 text-[11px] mt-0.5 leading-relaxed">
                Seluruh foto profil resmi dan data pokok siswa dikelola terpusat oleh Admin / Operator Sekolah demi ketertiban akademik. Anda dapat memperbarui kata sandi melalui menu <strong>Keamanan Akun</strong>.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Photo & Identity --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative">
            {{-- Cover Photo Banner --}}
            <div class="h-32 sm:h-48 w-full bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 relative overflow-hidden">
                <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 20px 20px;"></div>
            </div>
            
            {{-- Profile Info Section --}}
            <div class="px-6 pb-6 relative text-center sm:text-left sm:flex sm:items-end sm:gap-6">
                {{-- Profile Photo (Read-Only) --}}
                <div class="relative inline-block -mt-16 sm:-mt-20 mb-4 sm:mb-0 z-10">
                    <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-full bg-white p-1.5 shadow-xl mx-auto sm:mx-0 relative">
                        <div class="w-full h-full rounded-full overflow-hidden bg-gray-100 border border-gray-100 flex items-center justify-center text-5xl">
                            @if($student->photo)
                                <img src="{{ $student->photo_url }}" class="w-full h-full object-cover" alt="{{ $student->full_name }}">
                            @else
                                <span class="text-5xl">🎓</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                {{-- Name and Badges --}}
                <div class="flex-1 pb-2">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-gray-800 tracking-tight mt-2 sm:mt-0">{{ $student->full_name }}</h2>
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-2">
                        @if($student->nisn)
                            <span class="text-gray-600 text-xs font-mono bg-gray-100 border border-gray-200 px-3 py-1 rounded-full"><i class="fas fa-id-card text-amber-500 mr-1"></i> NISN: {{ $student->nisn }}</span>
                        @endif
                        @if($student->nis)
                            <span class="text-gray-600 text-xs font-mono bg-gray-100 border border-gray-200 px-3 py-1 rounded-full"><i class="fas fa-id-badge text-amber-500 mr-1"></i> NIS: {{ $student->nis }}</span>
                        @endif
                    </div>
                    <div class="mt-3 flex flex-wrap items-center justify-center sm:justify-start gap-2">
                        @if($classroom)
                            <span class="inline-block bg-amber-50 text-amber-700 border border-amber-200 px-3 py-1 rounded-full text-xs font-bold shadow-sm"><i class="fas fa-chalkboard-teacher mr-1"></i> {{ $classroom->class_name }}</span>
                        @endif
                        <span class="text-xs text-gray-500 font-medium"><i class="fas fa-school mr-1"></i> {{ $student->school->name ?? '-' }}</span>
                        <span class="text-gray-300">•</span>
                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold shadow-sm uppercase {{ $student->isActive() ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            {{ $student->status_label }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Reputation & Badges --}}
            @if($student->user && $student->user->reputation)
            <div class="border-t border-gray-100 p-6 bg-slate-50/50">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pembda Elite</h3>
                    <span class="text-xs font-bold {{ $student->user->reputation->level_color }} text-white px-2 py-0.5 rounded-full">
                        {{ $student->user->reputation->level_name }}
                    </span>
                </div>
                
                <div class="flex items-center gap-3 mb-6">
                    <div class="text-3xl font-bold text-slate-800">{{ number_format($student->user->reputation->total_points) }}</div>
                    <div class="text-xs font-bold text-slate-400 uppercase leading-tight">Total<br>Score</div>
                </div>

                <div class="space-y-3">
                    <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Koleksi Lencana</h4>
                    <div class="flex flex-wrap gap-2">
                        @forelse($student->user->badges as $badge)
                            <div class="group relative">
                                <div class="w-10 h-10 {{ $badge->color }} text-white rounded-lg flex items-center justify-center shadow-sm cursor-help hover:scale-110 transition-transform">
                                    <i class="fas {{ $badge->icon }} text-base"></i>
                                </div>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block w-32 bg-slate-900 text-white text-xs p-2 rounded shadow-xl z-20">
                                    <p class="font-bold border-b border-white/10 pb-1 mb-1">{{ $badge->name }}</p>
                                    <p class="text-white/70">{{ $badge->description }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 italic">Belum ada lencana yang didapat</p>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Detail Info --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Data Pribadi --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2"><i class="fas fa-id-card text-amber-500 text-xs"></i> Data Pribadi</h3>
                </div>
                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Nama Lengkap</p>
                        <p class="font-medium text-gray-800">{{ $student->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Jenis Kelamin</p>
                        <p class="font-medium text-gray-800">{{ $student->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Tempat, Tanggal Lahir</p>
                        <p class="font-medium text-gray-800">{{ $student->birth_place ?? '-' }}, {{ $student->birth_date ? $student->birth_date->format('d M Y') : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Agama</p>
                        <p class="font-medium text-gray-800">{{ $student->religion ?? '-' }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-xs text-gray-500 mb-0.5">Alamat</p>
                        <p class="font-medium text-gray-800">{{ $student->address ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">No. HP</p>
                        <p class="font-medium text-gray-800">{{ $student->phone ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Tahun Masuk</p>
                        <p class="font-medium text-gray-800">{{ $student->entry_year ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Data Orang Tua --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2"><i class="fas fa-user-friends text-blue-500 text-xs"></i> Data Orang Tua / Wali</h3>
                </div>
                <div class="p-5">
                    @php
                        $uniqueParents = $student->parents ? $student->parents->groupBy('relation_type')->map(function($group) {
                            return $group->sortByDesc(function($p) {
                                $score = 0;
                                if (!empty($p->user_id)) $score += 10;
                                if (!empty($p->email) && $p->email !== '-') $score += 5;
                                if (!empty($p->phone) && $p->phone !== '-') $score += 5;
                                if (!empty($p->occupation) && $p->occupation !== '-') $score += 2;
                                return $score;
                            })->first();
                        })->values() : collect();
                    @endphp

                    @if($uniqueParents->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($uniqueParents as $parent)
                                <div class="border border-gray-100 rounded-xl p-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $parent->relation_type === 'ayah' ? 'bg-blue-100 text-blue-700' : ($parent->relation_type === 'ibu' ? 'bg-pink-100 text-pink-700' : 'bg-gray-100 text-gray-700') }}">
                                            {{ $parent->getRelationTypeLabel() }}
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                                        <div>
                                            <p class="text-xs text-gray-500">Nama</p>
                                            <p class="font-medium text-gray-800">{{ $parent->full_name }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">No. HP</p>
                                            <p class="font-medium text-gray-800">{{ $parent->phone ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Pekerjaan</p>
                                            <p class="font-medium text-gray-800">{{ $parent->occupation ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Email</p>
                                            <p class="font-medium text-gray-800">{{ $parent->email ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-sm text-gray-600">
                            <p><span class="text-gray-500">Nama Wali:</span> {{ $student->parent_name ?? '-' }}</p>
                            <p><span class="text-gray-500">No. HP Wali:</span> {{ $student->parent_phone ?? '-' }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Sekolah Asal --}}
            @if($student->previous_school)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-bold text-gray-800 text-sm">🏫 Sekolah Asal</h3>
                </div>
                <div class="p-5 text-sm">
                    <p class="font-medium text-gray-800">{{ $student->previous_school }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
