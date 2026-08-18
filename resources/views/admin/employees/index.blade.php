@extends('layouts.admin')

@section('title', 'Data Pegawai')

@section('content')
<div class="space-y-6">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Data Pegawai</h1>
                    <p class="text-gray-600 mt-1">Manajemen data pegawai non-kependidikan</p>
                </div>
            </div>
            @if(auth()->user()->canManageEmploymentData())
            <a href="{{ route('admin.employees.create') }}" 
                class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-xl font-medium hover:from-blue-700 hover:to-indigo-800 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Pegawai
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

    @if(session('error'))
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl">
        <div class="flex items-center gap-3">
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <p class="text-red-800 font-medium">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <!-- Filter Form -->
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-6 mb-8">
        <form action="{{ route('admin.employees.index') }}" method="GET" class="flex flex-col lg:flex-row items-end gap-4">
            <div class="w-full lg:flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-1">Cari Pegawai</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none transition-colors group-focus-within:text-blue-500 text-gray-400">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, Kode, atau Telepon..."
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50/50 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:bg-white transition-all">
                </div>
            </div>

            <div class="w-full lg:w-80">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-1">Unit Sekolah</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-blue-500">
                        <i class="fas fa-school text-xs"></i>
                    </div>
                    @if(auth()->user()->isSuperAdmin())
                        <select name="school_id" class="w-full pl-9 pr-4 py-2.5 bg-gray-50/50 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:bg-white transition-all appearance-none">
                            <option value="">Semua Unit</option>
                            @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="w-full pl-9 pr-4 py-2.5 bg-blue-50/50 border border-blue-100 rounded-xl text-sm text-blue-700 font-semibold cursor-not-allowed">
                            {{ auth()->user()->school?->name ?? 'Unit Terpilih' }}
                        </div>
                    @endif
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-chevron-down text-[10px]"></i>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-56">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-1">Jenis Pegawai</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-blue-500">
                        <i class="fas fa-user-tie text-xs"></i>
                    </div>
                    <select name="employee_type" class="w-full pl-9 pr-4 py-2.5 bg-gray-50/50 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:bg-white transition-all appearance-none">
                        <option value="">Semua Jenis</option>
                        <option value="staff_tu" {{ request('employee_type') == 'staff_tu' ? 'selected' : '' }}>Staff Tata Usaha</option>
                        <option value="staff_keuangan" {{ request('employee_type') == 'staff_keuangan' ? 'selected' : '' }}>Staff Keuangan</option>
                        <option value="security" {{ request('employee_type') == 'security' ? 'selected' : '' }}>Security</option>
                        <option value="cleaning_service" {{ request('employee_type') == 'cleaning_service' ? 'selected' : '' }}>Cleaning Service</option>
                        <option value="driver" {{ request('employee_type') == 'driver' ? 'selected' : '' }}>Driver</option>
                        <option value="other" {{ request('employee_type') == 'other' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-chevron-down text-[10px]"></i>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-40">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-1">Status</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-blue-500">
                        <i class="fas fa-user-check text-xs"></i>
                    </div>
                    <select name="is_active" class="w-full pl-9 pr-4 py-2.5 bg-gray-50/50 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:bg-white transition-all appearance-none">
                        <option value="">Semua Status</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-chevron-down text-[10px]"></i>
                    </div>
                </div>
            </div>

            <div class="flex gap-2 w-full lg:w-auto">
                <button type="submit" class="flex-1 lg:flex-none px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-md shadow-blue-200 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-filter text-xs"></i>
                    <span>Terapkan</span>
                </button>
                @if(request()->anyFilled(['search', 'school_id', 'employee_type', 'is_active']))
                <a href="{{ route('admin.employees.index') }}" class="flex-1 lg:flex-none px-4 py-2.5 bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-50 rounded-xl text-sm font-bold transition-all flex items-center justify-center">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nama Lengkap</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Jenis Pegawai</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Sekolah</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Telepon</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Akun Login</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($employees as $index => $employee)
                    <tr class="hover:bg-blue-50/50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100 text-blue-700 font-bold text-xs shadow-sm">
                                {{ $employees->firstItem() + $index }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2.5 py-0.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-xs font-bold rounded shadow-sm">
                                {{ $employee->employee_code }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($employee->photo)
                                <div class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-blue-100 shadow-sm flex-shrink-0">
                                    <img src="{{ asset('storage/' . $employee->photo) }}" alt="{{ $employee->full_name }}" class="w-full h-full object-cover">
                                </div>
                                @else
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white font-bold text-base shadow-sm flex-shrink-0">
                                    {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                                </div>
                                @endif
                                <div>
                                    <div class="font-bold text-gray-900 text-sm md:text-base leading-snug">{{ $employee->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $employee->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $types = [
                                    'staff_tu' => ['label' => 'Staff TU', 'color' => 'purple'],
                                    'staff_keuangan' => ['label' => 'Staff Keuangan', 'color' => 'green'],
                                    'security' => ['label' => 'Security', 'color' => 'red'],
                                    'cleaning_service' => ['label' => 'Cleaning Service', 'color' => 'yellow'],
                                    'driver' => ['label' => 'Driver', 'color' => 'blue'],
                                    'other' => ['label' => 'Lainnya', 'color' => 'gray'],
                                ];
                                $type = $types[$employee->employee_type] ?? ['label' => 'Unknown', 'color' => 'gray'];
                            @endphp
                            <span class="inline-block px-2.5 py-0.5 bg-{{ $type['color'] }}-50 text-{{ $type['color'] }}-700 text-xs font-semibold rounded-full border border-{{ $type['color'] }}-100 shadow-sm">
                                {{ $type['label'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1.5 text-gray-700">
                                <span class="text-gray-400 text-xs"><i class="fas fa-school"></i></span>
                                <span class="font-medium text-xs md:text-sm">{{ $employee->school->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-xs md:text-sm text-gray-600 font-medium">{{ $employee->phone ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if($employee->user)
                                <div class="text-xs font-semibold text-blue-600">{{ $employee->user->email }}</div>
                                <div class="text-[10px] text-gray-500">Username: {{ $employee->user->username }}</div>
                            @else
                                <span class="text-xs text-gray-400 italic">Belum punya</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($employee->is_active)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-gradient-to-r from-green-50 to-green-100 text-green-700 text-xs font-bold rounded-xl border border-green-200 shadow-sm">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse shadow-sm"></span>
                                Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-gradient-to-r from-red-50 to-red-100 text-red-700 text-xs font-bold rounded-xl border border-red-200 shadow-sm">
                                <span class="w-1.5 h-1.5 bg-red-400 rounded-full"></span>
                                Tidak Aktif
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('admin.employees.show', $employee) }}" 
                                    class="group flex items-center justify-center w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg hover:scale-110 hover:rotate-3 transition-all duration-300 shadow-md"
                                    title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.employees.profile', $employee) }}" 
                                    class="group flex items-center justify-center w-8 h-8 bg-gradient-to-br from-indigo-500 to-indigo-600 text-white rounded-lg hover:scale-110 hover:rotate-3 transition-all duration-300 shadow-md"
                                    title="Profil Lengkap">
                                    <i class="fas fa-id-card-clip text-xs"></i>
                                </a>
                                @if(auth()->user()->canManageEmploymentData())
                                <a href="{{ route('admin.employees.edit', $employee) }}" 
                                    class="group flex items-center justify-center w-8 h-8 bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg hover:scale-110 hover:rotate-3 transition-all duration-300 shadow-md"
                                    title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endif
                                <button type="button" 
                                    onclick="openQrModal('{{ addslashes($employee->full_name) }}', '{{ $employee->employee_code }}', '{{ $employee->photo_url }}', 'Staff Pegawai', '{{ addslashes($employee->school?->name ?? 'Sekolah') }}', 'Pegawai', '{{ addslashes($employee->birth_place ?? '-') }}, {{ $employee->birth_date ? $employee->birth_date->format('d-m-Y') : '-' }}')"
                                    class="group flex items-center justify-center w-8 h-8 bg-gradient-to-br from-orange-500 to-amber-600 text-white rounded-lg hover:scale-110 hover:rotate-3 transition-all duration-300 shadow-md"
                                    title="Cetak Kartu QR Code">
                                    <i class="fas fa-qrcode text-xs group-hover:scale-110 transition-transform"></i>
                                </button>
                                <button type="button" 
                                    onclick="openRfidModal('{{ addslashes($employee->full_name) }}', '{{ $employee->rfid_uid }}', '{{ route('admin.employees.update-rfid', $employee) }}', 'Pegawai', 'employee', '{{ $employee->id }}')"
                                    class="group flex items-center justify-center w-8 h-8 bg-gradient-to-br from-purple-500 to-indigo-600 text-white rounded-lg hover:scale-110 hover:rotate-3 transition-all duration-300 shadow-md"
                                    title="Daftarkan RFID">
                                    <i class="fas fa-id-card text-xs group-hover:scale-110 transition-transform"></i>
                                </button>

                                @if(auth()->user()->canManageEmploymentData())
                                <form action="{{ route('admin.employees.destroy', $employee) }}" method="POST" 
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus pegawai ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="group flex items-center justify-center w-8 h-8 bg-gradient-to-br from-red-500 to-red-600 text-white rounded-lg hover:scale-110 hover:rotate-3 transition-all duration-300 shadow-md"
                                        title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="text-gray-600 font-medium">Belum ada data pegawai</p>
                                <a href="{{ route('admin.employees.create') }}" class="text-blue-600 hover:text-blue-700 font-medium">
                                    Tambah Pegawai Pertama
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($employees->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $employees->links() }}
        </div>
        @endif
    </div>
</div>
@include('partials.qr-card-modal')
@include('partials.rfid-modal')
@endsection
