@extends('layouts.admin')
@section('title', 'Laporan Tracer Study Alumni - Portal Admin')

@section('content')
<div class="space-y-6">
    {{-- Header Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white rounded-xl shadow-sm border border-gray-100 px-5 py-4">
        <div>
            <h1 class="text-lg md:text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-graduation-cap text-indigo-500"></i> Tracer Study Alumni (BMW)
            </h1>
            <p class="text-xs text-gray-500 mt-0.5">Penelusuran keterserapan kerja dan umpan balik lulusan Yayasan Perguruan Pembda Nias</p>
        </div>
    </div>

    <!-- Navigation & Quick Links -->
    <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
        <a href="{{ route('admin.alumni.index') }}" class="px-4 py-2 bg-white text-gray-700 hover:bg-gray-100 border border-gray-200 font-medium rounded-xl text-sm transition">
            <i class="fas fa-list mr-1"></i> Data Alumni Sistem
        </a>
        <a href="{{ route('admin.alumni-directory.index') }}" class="px-4 py-2 bg-white text-gray-700 hover:bg-gray-100 border border-gray-200 font-medium rounded-xl text-sm transition">
            <i class="fas fa-address-book mr-1"></i> Direktori Alumni (IKA)
        </a>
        <a href="{{ route('admin.pkl-alumni.tracer.index') }}" class="px-4 py-2 bg-indigo-600 text-white font-medium rounded-xl text-sm shadow-sm">
            <i class="fas fa-chart-line mr-1"></i> Tracer Study (BMW)
        </a>
    </div>

    {{-- Filter & Search Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <form action="{{ route('admin.pkl-alumni.tracer.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            {{-- Search --}}
            <div class="md:col-span-2 relative">
                <i class="fas fa-search absolute left-3.5 top-3.5 text-gray-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama alumni..." class="w-full bg-gray-50 border border-gray-100 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">
            </div>

            {{-- Status filter --}}
            <div>
                <select name="status" onchange="this.form.submit()" class="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">
                    <option value="">Semua Status BMW...</option>
                    <option value="kerja" {{ request('status') === 'kerja' ? 'selected' : '' }}>Bekerja</option>
                    <option value="kuliah" {{ request('status') === 'kuliah' ? 'selected' : '' }}>Kuliah</option>
                    <option value="wirausaha" {{ request('status') === 'wirausaha' ? 'selected' : '' }}>Wirausaha</option>
                    <option value="mencari_kerja" {{ request('status') === 'mencari_kerja' ? 'selected' : '' }}>Mencari Kerja</option>
                    <option value="lainnya" {{ request('status') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>

            {{-- Graduation Year filter --}}
            <div>
                <input type="number" name="graduation_year" value="{{ request('graduation_year') }}" placeholder="Tahun Lulus (Contoh: 2024)" class="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">
            </div>

            {{-- School filter (Superadmin only) --}}
            @if($isSA)
                <div>
                    <select name="school_id" onchange="this.form.submit()" class="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">
                        <option value="">Semua Sekolah SMK...</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Actions --}}
            <div class="md:col-span-5 flex justify-end gap-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-5 py-2.5 rounded-xl text-xs shadow transition">
                    Terapkan Filter
                </button>
                @if(request()->anyFilled(['search', 'status', 'graduation_year', 'school_id']))
                    <a href="{{ route('admin.pkl-alumni.tracer.index') }}" class="bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold px-5 py-2.5 rounded-xl text-xs transition flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tracer List Table Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider text-left">
                    <tr>
                        <th class="py-3.5 pl-5">Alumni & Sekolah</th>
                        <th class="py-3.5 text-center">Tahun Lulus</th>
                        <th class="py-3.5 text-center">Status</th>
                        <th class="py-3.5">Detail Pekerjaan / Pendidikan</th>
                        <th class="py-3.5">Tanggal Survey</th>
                        <th class="py-3.5 pr-5">Masukan Untuk Sekolah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-xs text-gray-700">
                    @forelse($tracers as $t)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="py-4 pl-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl overflow-hidden bg-gray-100 border border-gray-200 shadow-sm flex-shrink-0">
                                        <img src="{{ $t->alumni->student->photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($t->alumni->full_name) }}" class="w-full h-full object-cover" alt="Foto">
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800 text-sm leading-tight">{{ $t->alumni->full_name }}</p>
                                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $t->alumni->school->name ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 text-center font-bold text-gray-700">
                                {{ $t->alumni->graduation_year }}
                            </td>
                            <td class="py-4 text-center">
                                @php
                                    $statusClass = match($t->employment_status) {
                                        'kerja' => 'bg-emerald-50 text-emerald-700 border-emerald-250',
                                        'kuliah' => 'bg-blue-50 text-blue-700 border-blue-250',
                                        'wirausaha' => 'bg-amber-50 text-amber-700 border-amber-250',
                                        'mencari_kerja' => 'bg-rose-50 text-rose-700 border-rose-250',
                                        default => 'bg-gray-150 text-gray-600 border-gray-300'
                                    };
                                    $statusText = match($t->employment_status) {
                                        'kerja' => 'Bekerja',
                                        'kuliah' => 'Kuliah',
                                        'wirausaha' => 'Wirausaha',
                                        'mencari_kerja' => 'Mencari Kerja',
                                        default => 'Lainnya'
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="py-4">
                                @if($t->employment_status === 'kerja')
                                    <p class="font-bold text-gray-850">{{ $t->job_title }}</p>
                                    <p class="text-[10px] text-gray-500 mt-0.5"><i class="fas fa-building mr-1 text-gray-400"></i>{{ $t->company_name }}</p>
                                    @if($t->salary_range)
                                        <p class="text-[9px] text-emerald-600 font-semibold mt-0.5">{{ $t->salary_range }}</p>
                                    @endif
                                @elseif($t->employment_status === 'kuliah')
                                    <p class="font-bold text-gray-850">{{ $t->major }}</p>
                                    <p class="text-[10px] text-gray-500 mt-0.5"><i class="fas fa-university mr-1 text-gray-400"></i>{{ $t->university_name }}</p>
                                @elseif($t->employment_status === 'wirausaha')
                                    <p class="font-bold text-gray-850">Wirausaha</p>
                                    <p class="text-[10px] text-gray-500 mt-0.5"><i class="fas fa-store mr-1 text-gray-400"></i>Bidang: {{ $t->wirausaha_field }}</p>
                                @else
                                    <span class="text-gray-450 italic text-[10px]">Tidak ada rincian data</span>
                                @endif
                            </td>
                            <td class="py-4 text-gray-600">
                                {{ $t->survey_date->translatedFormat('d M Y') }}
                            </td>
                            <td class="py-4 pr-5 max-w-[250px] truncate" title="{{ $t->feedback_for_school }}">
                                <span class="text-gray-600 italic">"{{ $t->feedback_for_school ?? '-' }}"</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-400 italic">
                                <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-graduation-cap text-lg text-gray-300"></i>
                                </div>
                                Belum ada data respon survey Tracer Study.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tracers->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $tracers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
