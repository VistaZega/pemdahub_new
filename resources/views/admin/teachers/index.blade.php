@extends('layouts.admin')

@section('title', 'Data Guru')

@section('content')
<div class="space-y-6">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Data Guru</h1>
                    <p class="text-gray-600 mt-1">Manajemen data guru & tenaga pengajar</p>
                </div>
            </div>
            @if(auth()->user()->canManageEmploymentData())
            <a href="{{ route('admin.teachers.create') }}" 
                class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-xl font-medium hover:from-emerald-700 hover:to-teal-800 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Guru
            </a>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-xl">
        <div class="flex items-center gap-3">
            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <p class="text-green-800 font-medium">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    <!-- Filter Form -->
    <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 p-6 mb-8">
        <form action="{{ route('admin.teachers.index') }}" method="GET" class="flex flex-col lg:flex-row lg:flex-wrap items-end gap-4">
            <div class="w-full lg:flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-1">Cari Guru</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors group-focus-within:text-emerald-500 text-gray-400">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, Kode, atau Telepon..."
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50/50 border-none rounded-xl text-sm focus:ring-2 focus:ring-emerald-500/20 focus:bg-white transition-all">
                </div>
            </div>

            <div class="w-full lg:w-80">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-1">Unit Sekolah</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-500">
                        <i class="fas fa-school text-xs"></i>
                    </div>
                    @if(auth()->user()->isSuperAdmin())
                        <select name="school_id" class="w-full pl-9 pr-4 py-2.5 bg-gray-50/50 border-none rounded-xl text-sm focus:ring-2 focus:ring-emerald-500/20 focus:bg-white transition-all appearance-none">
                            <option value="">Semua Unit</option>
                            @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="w-full pl-9 pr-4 py-2.5 bg-emerald-50/50 border border-emerald-100 rounded-xl text-sm text-emerald-700 font-semibold cursor-not-allowed">
                            {{ auth()->user()->school?->name ?? 'Unit Terpilih' }}
                        </div>
                    @endif
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-chevron-down text-[10px]"></i>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-48">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-1">Status</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-500">
                        <i class="fas fa-user-check text-xs"></i>
                    </div>
                    <select name="is_active" class="w-full pl-9 pr-4 py-2.5 bg-gray-50/50 border-none rounded-xl text-sm focus:ring-2 focus:ring-emerald-500/20 focus:bg-white transition-all appearance-none">
                        <option value="">Semua Status</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-chevron-down text-[10px]"></i>
                    </div>
                </div>
            </div>

            <div class="flex gap-2 w-full lg:w-auto mt-2 lg:mt-0">
                <button type="submit" class="flex-1 lg:flex-none px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-md shadow-emerald-200 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-filter text-xs"></i>
                    <span>Terapkan</span>
                </button>
                @if(request()->anyFilled(['search', 'school_id', 'is_active']))
                <a href="{{ route('admin.teachers.index') }}" class="flex-1 lg:flex-none px-4 py-2.5 bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 rounded-xl text-sm font-bold transition-all flex items-center justify-center">
                    Reset
                </a>
                @endif
            </div>
        </form>

        @if(request('school_id') || !auth()->user()->isSuperAdmin())
        <div class="mt-5 pt-5 border-t border-emerald-100 flex flex-wrap gap-3 justify-end items-center">
            <span class="text-sm text-gray-500 mr-auto font-medium">Aksi Massal:</span>
            @if($isPasswordReset)
                <a href="{{ route('admin.teachers.print-accounts', request()->all()) }}" target="_blank" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2" title="Cetak Daftar Akun">
                    <i class="fas fa-print"></i> Cetak Daftar Akun
                </a>
                <a href="{{ route('admin.teachers.export-accounts', request()->all()) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2" title="Export Excel Akun">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
            @else
                <button type="button" onclick="alert('Silakan klik tombol Reset Password Massal terlebih dahulu (tombol merah) sebelum mencetak/mengexport akun.');" class="px-4 py-2 bg-gray-200 text-gray-500 cursor-not-allowed rounded-xl text-sm font-bold flex items-center justify-center gap-2" title="Reset Password Terlebih Dahulu">
                    <i class="fas fa-print"></i> Cetak Daftar Akun
                </button>
                <button type="button" onclick="alert('Silakan klik tombol Reset Password Massal terlebih dahulu (tombol merah) sebelum mencetak/mengexport akun.');" class="px-4 py-2 bg-gray-200 text-gray-500 cursor-not-allowed rounded-xl text-sm font-bold flex items-center justify-center gap-2" title="Reset Password Terlebih Dahulu">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            @endif
            <button type="button" onclick="if(confirm('PERINGATAN! Semua password guru di unit ini akan direset menjadi pola: Pembda + KodeGuru.\n\nLanjutkan?')) document.getElementById('reset-pwd-form').submit();" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2" title="Reset Password Massal">
                <i class="fas fa-key"></i> Reset Password
            </button>
        </div>
        @endif
    </div>

    @if(request('school_id') || !auth()->user()->isSuperAdmin())
    <form id="reset-pwd-form" action="{{ route('admin.teachers.reset-passwords') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="school_id" value="{{ request('school_id', auth()->user()->school_id) }}">
    </form>
    @endif

    <!-- Teachers Table -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Foto</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nama Lengkap & Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Sekolah</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Kompetensi Mata Pelajaran</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Akun Login</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($teachers as $index => $teacher)
                    <tr class="hover:bg-gradient-to-r hover:from-emerald-50/50 hover:to-teal-50/50 transition-all duration-200">
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-100 to-teal-100 text-emerald-700 font-bold text-xs shadow-sm">
                                {{ $teachers->firstItem() + $index }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-emerald-100 shadow-sm flex-shrink-0">
                                <img src="{{ $teacher->photo_url }}" alt="{{ $teacher->full_name }}" 
                                    class="w-full h-full object-cover">
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col gap-0.5">
                                <div class="font-bold text-gray-900 text-sm md:text-base leading-snug flex items-center flex-wrap gap-1.5">
                                    <span>{{ $teacher->full_name }}</span>
                                    @php
                                        $viewingSchoolId = auth()->user()->isSuperAdmin() ? request('school_id') : auth()->user()->school_id;
                                    @endphp
                                    @if($viewingSchoolId && $teacher->school_id != $viewingSchoolId)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200" title="Guru Lintas Unit (Berasal dari {{ $teacher->school->name ?? 'sekolah lain' }})">
                                            <i class="fas fa-exchange-alt mr-1"></i> Lintas Unit
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="px-2 py-0.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-[10px] font-bold rounded shadow-sm">
                                        {{ $teacher->teacher_code }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{ $teacher->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1.5 text-gray-700">
                                <span class="text-gray-400 text-xs"><i class="fas fa-school"></i></span>
                                <span class="font-medium text-xs md:text-sm">{{ $teacher->school->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if($teacher->competentSubjects && $teacher->competentSubjects->count() > 0)
                                <div class="flex flex-wrap gap-1 max-w-[280px]">
                                    @foreach($teacher->competentSubjects->take(3) as $subject)
                                        <span class="inline-flex items-center px-2 py-0.5 bg-purple-50 text-purple-700 text-xs font-semibold rounded-lg border border-purple-100 shadow-sm" title="{{ $subject->subject_name }} ({{ $subject->subject_code }})">
                                            {{ $subject->subject_name }}
                                        </span>
                                    @endforeach
                                    @if($teacher->competentSubjects->count() > 3)
                                        <span class="inline-flex items-center px-2 py-0.5 bg-gradient-to-r from-purple-100 to-pink-100 text-purple-700 text-xs font-bold rounded-lg shadow-sm">
                                            +{{ $teacher->competentSubjects->count() - 3 }}
                                        </span>
                                    @endif
                                </div>
                            @elseif($teacher->subjects && $teacher->subjects->count() > 0)
                                <div class="flex flex-wrap gap-1 max-w-[280px]">
                                    @foreach($teacher->subjects->take(3) as $subject)
                                        <span class="inline-flex items-center px-2 py-0.5 bg-blue-50 text-blue-700 text-xs font-semibold rounded-lg border border-blue-100 shadow-sm" title="Diajar: {{ $subject->subject_name }} ({{ $subject->subject_code }})">
                                            {{ $subject->subject_name }}
                                        </span>
                                    @endforeach
                                    @if($teacher->subjects->count() > 3)
                                        <span class="inline-flex items-center px-2 py-0.5 bg-gradient-to-r from-blue-100 to-cyan-100 text-blue-700 text-xs font-bold rounded-lg shadow-sm">
                                            +{{ $teacher->subjects->count() - 3 }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                <div class="inline-flex items-center gap-1 px-2 py-0.5 bg-gray-50 border border-gray-200 rounded-lg text-gray-500 text-xs italic">
                                    <i class="fas fa-exclamation-triangle text-yellow-500 text-[10px]"></i>
                                    <span>Belum ada mapel</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($teacher->user)
                                <div class="text-xs font-semibold text-blue-600">{{ $teacher->user->email }}</div>
                                <div class="text-[10px] text-gray-500">Username: {{ $teacher->user->username }}</div>
                            @else
                                <span class="text-xs text-gray-400 italic">Belum punya</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($teacher->is_active)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-gradient-to-r from-green-50 to-green-100 text-green-700 text-xs font-bold rounded-xl border border-green-200 shadow-sm">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse shadow-sm"></span>
                                Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-gradient-to-r from-gray-50 to-gray-100 text-gray-600 text-xs font-bold rounded-xl border border-gray-200 shadow-sm">
                                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                                Non-Aktif
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('admin.teachers.show', $teacher) }}?return_url={{ urlencode(request()->fullUrl()) }}" 
                                    class="group flex items-center justify-center w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg hover:scale-110 hover:rotate-3 transition-all duration-300 shadow-md"
                                    title="Lihat Detail">
                                    <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.teachers.competencies', $teacher) }}?return_url={{ urlencode(request()->fullUrl()) }}" 
                                    class="group flex items-center justify-center w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-500 text-white rounded-lg hover:scale-110 hover:rotate-3 transition-all duration-300 shadow-md"
                                    title="Kelola Kompetensi">
                                    <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </a>
                                @if(auth()->user()->canManageEmploymentData())
                                <a href="{{ route('admin.teachers.edit', $teacher) }}?return_url={{ urlencode(request()->fullUrl()) }}" 
                                    class="group flex items-center justify-center w-8 h-8 bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg hover:scale-110 hover:rotate-3 transition-all duration-300 shadow-md"
                                    title="Edit">
                                    <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endif
                                <button type="button" 
                                    onclick="openQrModal('{{ addslashes($teacher->full_name) }}', '{{ $teacher->teacher_code }}', '{{ $teacher->photo_url }}', 'Guru Pengajar', '{{ addslashes($teacher->school?->name ?? 'Sekolah') }}', 'Guru', '{{ addslashes($teacher->birth_place ?? '-') }}, {{ $teacher->birth_date ? $teacher->birth_date->format('d-m-Y') : '-' }}')"
                                    class="group flex items-center justify-center w-8 h-8 bg-gradient-to-br from-orange-500 to-amber-600 text-white rounded-lg hover:scale-110 hover:rotate-3 transition-all duration-300 shadow-md"
                                    title="Cetak Kartu QR Code">
                                    <i class="fas fa-qrcode text-xs group-hover:scale-110 transition-transform"></i>
                                </button>
                                <button type="button" 
                                    onclick="openRfidModal('{{ addslashes($teacher->full_name) }}', '{{ $teacher->employee?->rfid_uid }}', '{{ route('admin.teachers.update-rfid', $teacher) }}', 'Guru', 'employee', '{{ $teacher->employee_id }}')"
                                    class="group flex items-center justify-center w-8 h-8 bg-gradient-to-br from-purple-500 to-indigo-600 text-white rounded-lg hover:scale-110 hover:rotate-3 transition-all duration-300 shadow-md"
                                    title="Daftarkan RFID">
                                    <i class="fas fa-id-card text-xs group-hover:scale-110 transition-transform"></i>
                                </button>

                                @if(auth()->user()->canManageEmploymentData())
                                <form action="{{ route('admin.teachers.destroy', $teacher) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus guru ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" 
                                        class="group flex items-center justify-center w-8 h-8 bg-gradient-to-br from-red-500 to-red-600 text-white rounded-lg hover:scale-110 hover:rotate-3 transition-all duration-300 shadow-md"
                                        title="Hapus">
                                        <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center bg-gradient-to-br from-gray-50 to-gray-100">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-24 h-24 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full flex items-center justify-center mb-4 animate-pulse">
                                    <svg class="w-12 h-12 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                </div>
                                <p class="text-xl font-bold text-gray-700 mb-2"><i class="fas fa-clipboard mr-1"></i> Tidak ada data guru</p>
                                <p class="text-sm text-gray-500 mb-4">Mulai tambahkan guru untuk sekolah Anda</p>
                                <a href="{{ route('admin.teachers.create') }}" 
                                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-xl font-medium hover:from-emerald-700 hover:to-teal-800 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Tambah Guru Baru
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($teachers->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $teachers->links() }}
        </div>
        @endif
    </div>
</div>
@include('partials.qr-card-modal')
@include('partials.rfid-modal')
@endsection
