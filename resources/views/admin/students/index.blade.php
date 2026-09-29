@extends('layouts.admin')

@section('title', 'Daftar Siswa - Admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Daftar Siswa</h1>
                    <p class="text-gray-600 mt-1">Kelola data siswa di semua sekolah</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.students.create') }}"
                    class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-700 text-white rounded-xl font-medium hover:from-indigo-700 hover:to-purple-800 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Tambah Siswa
                </a>
                <a href="{{ route('admin.students.import.form') }}"
                    class="px-4 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-all">
                    Import Excel
                </a>
                <a href="{{ route('admin.students.import.sample') }}"
                    class="px-4 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-all text-sm">
                    Download Template
                </a>
                <a href="{{ route('admin.students.smart-cards-bulk') }}"
                    class="px-4 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold transition-all flex items-center gap-2 text-sm shadow-sm">
                    <i class="fas fa-id-card"></i>
                    Cetak Kartu Pintar Massal
                </a>
                <a href="{{ route('admin.students.rfid-bulk') }}"
                    class="px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition-all flex items-center gap-2 text-sm shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                    Registrasi RFID Massal
                </a>
            </div>
        </div>
    </div>

    <!-- Success Alert -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-xl">
        <div class="flex items-center gap-3">
            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-green-800 font-medium">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    <!-- Filter Form -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
        <form method="GET" class="flex flex-col lg:flex-row lg:flex-wrap items-end gap-4">
            <div class="w-full lg:flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-1">Cari Siswa</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors group-focus-within:text-indigo-500 text-gray-400">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama, NISN, atau NIS..."
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50/50 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all">
                </div>
            </div>

            <div class="w-full lg:w-auto">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-1">Unit Sekolah</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-500">
                        <i class="fas fa-school text-xs"></i>
                    </div>
                    @if(auth()->user()->isSuperAdmin())
                        <select name="school_id" onchange="this.form.submit()" class="w-full pl-9 pr-4 py-2.5 bg-gray-50/50 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all appearance-none">
                            <option value="">Semua Unit</option>
                            @foreach($schools as $sch)
                            <option value="{{ $sch->id }}" {{ request('school_id')==$sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="w-full pl-9 pr-4 py-2.5 bg-indigo-50/50 border border-indigo-100 rounded-xl text-sm text-indigo-700 font-semibold cursor-not-allowed whitespace-nowrap overflow-hidden text-ellipsis">
                            {{ auth()->user()->school?->name ?? 'Unit Terpilih' }}
                        </div>
                    @endif
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-auto">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-1">Tahun Pelajaran</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-500">
                        <i class="fas fa-calendar-alt text-xs"></i>
                    </div>
                    <select name="academic_year_id" onchange="this.form.submit()" class="w-full pl-9 pr-4 py-2.5 bg-gray-50/50 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all appearance-none">
                        <option value="">Semua Tahun</option>
                        @foreach($academicYears as $ay)
                        <option value="{{ $ay->id }}" {{ $selectedAcademicYearId == $ay->id ? 'selected' : '' }}>
                            {{ $ay->year }} {{ $ay->is_active ? '(Aktif)' : '' }}
                        </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-chevron-down text-[10px]"></i>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-auto">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-1">Kelas</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-500">
                        <i class="fas fa-chalkboard-teacher text-xs"></i>
                    </div>
                    <select name="classroom_id" class="w-full pl-9 pr-10 py-2.5 bg-gray-50/50 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all appearance-none">
                        <option value="">Semua Kelas</option>
                        @foreach($classrooms as $cls)
                            <option value="{{ $cls->id }}" {{ request('classroom_id') == $cls->id ? 'selected' : '' }}>
                                {{ $cls->class_name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-chevron-down text-[10px]"></i>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-auto">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-1">Status</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-500">
                        <i class="fas fa-toggle-on text-xs"></i>
                    </div>
                    <select name="status" class="w-full pl-9 pr-4 py-2.5 bg-gray-50/50 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:bg-white transition-all appearance-none">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status')=='aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="lulus" {{ request('status')=='lulus' ? 'selected' : '' }}>Lulus</option>
                        <option value="keluar" {{ request('status')=='keluar' ? 'selected' : '' }}>Keluar</option>
                        <option value="pindah" {{ request('status')=='pindah' ? 'selected' : '' }}>Pindah</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-chevron-down text-[10px]"></i>
                    </div>
                </div>
            </div>

            <div class="flex gap-2 w-full lg:w-auto mt-2 lg:mt-0">
                <button type="submit" class="flex-1 lg:flex-none px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-md shadow-indigo-200 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-filter text-xs"></i>
                    <span>Filter</span>
                </button>
                @if(request()->anyFilled(['q', 'school_id', 'status', 'classroom_id', 'academic_year_id']))
                <a href="{{ route('admin.students.index') }}" class="flex-1 lg:flex-none px-4 py-2.5 bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 rounded-xl text-sm font-semibold transition-all flex items-center justify-center">
                    Reset
                </a>
                @endif
            </div>
        </form>

        @if(request('classroom_id') || request('school_id'))
        <div class="mt-5 pt-5 border-t border-gray-100 flex flex-wrap gap-3 justify-end items-center">
            <span class="text-sm text-gray-500 mr-auto font-medium">Aksi Massal:</span>
            @if($isPasswordReset)
                <a href="{{ route('admin.students.print-accounts', request()->all()) }}" target="_blank" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition-all flex items-center justify-center gap-2" title="Cetak Daftar Akun">
                    <i class="fas fa-print"></i> Cetak Daftar Akun
                </a>
                <a href="{{ route('admin.students.export-accounts', request()->all()) }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-semibold transition-all flex items-center justify-center gap-2" title="Export Excel Akun">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
            @else
                <button type="button" onclick="alert('Silakan klik tombol Reset Password Massal terlebih dahulu (tombol merah) sebelum mencetak/mengexport akun.');" class="px-4 py-2 bg-gray-200 text-gray-500 cursor-not-allowed rounded-xl text-sm font-semibold flex items-center justify-center gap-2" title="Reset Password Terlebih Dahulu">
                    <i class="fas fa-print"></i> Cetak Daftar Akun
                </button>
                <button type="button" onclick="alert('Silakan klik tombol Reset Password Massal terlebih dahulu (tombol merah) sebelum mencetak/mengexport akun.');" class="px-4 py-2 bg-gray-200 text-gray-500 cursor-not-allowed rounded-xl text-sm font-semibold flex items-center justify-center gap-2" title="Reset Password Terlebih Dahulu">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            @endif
            <button type="button" onclick="if(confirm('PERINGATAN! Semua password siswa yang terpilih akan direset menjadi pola: Pembda + NISN.\n\nContoh: Pembda123456789\n\nLanjutkan?')) document.getElementById('reset-pwd-form').submit();" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-semibold transition-all flex items-center justify-center gap-2" title="Reset Password Massal">
                <i class="fas fa-key"></i> Reset Password
            </button>
        </div>
        @endif
    </div>

    @if(request('classroom_id') || request('school_id'))
    <form id="reset-pwd-form" action="{{ route('admin.students.reset-passwords') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="classroom_id" value="{{ request('classroom_id') }}">
        <input type="hidden" name="school_id" value="{{ request('school_id') }}">
    </form>
    @endif

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-16">No</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/4">Siswa</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Identitas</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kelas / Sekolah</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Lahir / Thn Masuk</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    @forelse($students as $s)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 font-semibold text-sm">
                                {{ ($students->currentPage() - 1) * $students->perPage() + $loop->iteration }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full flex-shrink-0 overflow-hidden ring-2 ring-indigo-100 shadow-sm">
                                    <img src="{{ $s->photo_url }}" class="w-full h-full object-cover" alt="{{ $s->full_name }}">
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-gray-900 truncate">{{ $s->full_name }}</div>
                                    <div class="text-xs text-gray-500 font-medium flex items-center gap-1.5">
                                        @if($s->gender == 'L')
                                            <span class="text-blue-500 bg-blue-50 px-1.5 py-0.5 rounded text-xs uppercase font-semibold">Laki-laki</span>
                                        @else
                                            <span class="text-pink-500 bg-pink-50 px-1.5 py-0.5 rounded text-xs uppercase font-semibold">Perempuan</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-gray-700">{{ $s->nisn }}</div>
                            <div class="text-xs text-gray-400 font-mono">{{ $s->nis ?: '- no nis -' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                <span class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-700">
                                    <i class="fas fa-chalkboard-teacher text-xs opacity-50"></i>
                                    {{ $s->currentClassroom->first()->class_name ?? 'Belum ada kelas' }}
                                </span>
                                <span class="text-[11px] text-gray-500 flex items-center gap-1">
                                    <i class="fas fa-school text-[10px] opacity-40"></i>
                                    {{ $s->school?->name }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-medium text-gray-800">
                                    {{ $s->birth_date ? $s->birth_date->format('d M Y') : '-' }}
                                </span>
                                <span class="text-[11px] text-gray-400">Entry: {{ $s->entry_year }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                            $statusColors = [
                            'aktif' => 'bg-green-100 text-green-800',
                            'lulus' => 'bg-blue-100 text-blue-800',
                            'keluar' => 'bg-red-100 text-red-800',
                            'pindah' => 'bg-yellow-100 text-yellow-800',
                            ];
                            $statusColor = $statusColors[$s->status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                                {{ ucfirst($s->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.students.show', $s) }}"
                                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 hover:scale-110 transition-all duration-200 group"
                                    title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.students.edit', $s) }}"
                                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 hover:scale-110 transition-all duration-200 group"
                                    title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <button type="button" 
                                    onclick="openQrModal('{{ addslashes($s->full_name) }}', '{{ $s->nisn ?: $s->nis }}', '{{ $s->photo_url }}', '{{ addslashes($s->currentClassroom->first()->class_name ?? 'Tanpa Kelas') }}', '{{ addslashes($s->school?->name ?? 'Sekolah') }}', 'Siswa', '{{ addslashes($s->birth_place ?? '-') }}, {{ $s->birth_date ? $s->birth_date->format('d-m-Y') : '-' }}')"
                                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-orange-50 text-orange-600 hover:bg-orange-100 hover:scale-110 transition-all duration-200 group"
                                    title="Cetak Kartu QR Code">
                                    <i class="fas fa-qrcode text-sm"></i>
                                </button>
                                <button type="button" 
                                    onclick="openRfidModal('{{ addslashes($s->full_name) }}', '{{ $s->rfid_uid }}', '{{ route('admin.students.update-rfid', $s->id) }}', 'Siswa', 'student', '{{ $s->id }}')"
                                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-purple-50 text-purple-600 hover:bg-purple-100 hover:scale-110 transition-all duration-200 group"
                                    title="Daftarkan RFID">
                                    <i class="fas fa-id-card text-sm"></i>
                                </button>
                                <form action="{{ route('admin.students.destroy', $s) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 hover:scale-110 transition-all duration-200 group"
                                        title="Hapus"
                                        onclick="return confirm('Yakin ingin menghapus siswa {{ addslashes($s->full_name) }}?')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <p class="text-lg font-medium mb-1">Tidak ada data siswa</p>
                            <p class="text-sm">Silakan tambah siswa baru atau ubah filter pencarian</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($students->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $students->links() }}
        </div>
        @endif
    </div>
</div>

@include('partials.qr-card-modal')

@include('partials.rfid-modal')
@endsection