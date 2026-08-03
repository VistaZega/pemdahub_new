@extends('layouts.admin')

@section('title', 'Penugasan Jabatan')

@section('content')
<div class="space-y-6">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-600 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Penugasan Jabatan</h1>
                    <p class="text-gray-600 mt-1">Kelola penugasan jabatan guru dan pegawai per tahun ajaran</p>
                </div>
            </div>
            <a href="{{ route('admin.assignments.positions.create') }}" 
                class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-700 text-white rounded-xl font-medium hover:from-purple-700 hover:to-pink-800 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Penugasan
            </a>
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
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
        <form method="GET" action="{{ route('admin.assignments.positions.index') }}" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-calendar-alt mr-1"></i> Tahun Ajaran</label>
                <select name="academic_year_id" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500">
                    <option value="">Semua Tahun</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ $selectedYearId == $year->id ? 'selected' : '' }}>
                            {{ $year->year }}{{ $year->is_active ? ' (Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>


            @if(auth()->user()->isSuperAdmin())
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-school mr-1"></i> Sekolah</label>
                <select name="school_id" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500">
                    <option value="">Semua Sekolah</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                            {{ $school->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-users mr-1"></i> Kategori</label>
                <select name="employee_type" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500">
                    <option value="">Semua (Guru & Staff)</option>
                    <option value="guru" {{ request('employee_type') === 'guru' ? 'selected' : '' }}>Khusus Guru</option>
                    <option value="staff" {{ request('employee_type') === 'staff' ? 'selected' : '' }}>Khusus Staff / Pegawai</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2"><i class="fas fa-search mr-1"></i> Cari</label>
                <input type="text" name="search" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500" placeholder="Nama/Kode Guru/Pegawai..." value="{{ request('search') }}">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl font-medium hover:from-purple-700 hover:to-pink-700 shadow-md hover:shadow-lg transition-all">
                    Filter
                </button>
                <a href="{{ route('admin.assignments.positions.index') }}" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-all">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold w-10">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold w-14">Foto</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Nama & Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Sekolah</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Jabatan</th>
                        @if(auth()->user()->isKetuaYayasan() || auth()->user()->hasRole('bendahara'))
                        <th class="px-4 py-3 text-right text-xs font-semibold">Total Tunjangan</th>
                        @endif
                        <th class="px-4 py-3 text-center text-xs font-semibold w-24 whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($employees as $index => $employee)
                    <tr class="hover:bg-purple-50/50 transition-colors">
                        <td class="px-4 py-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-purple-100 text-purple-700 font-bold text-xs">
                                {{ $employees->firstItem() + $index }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($employee->photo)
                            <img src="{{ asset('storage/' . $employee->photo) }}" alt="{{ $employee->full_name }}" 
                                class="w-10 h-10 rounded-xl object-cover border-2 border-purple-200">
                            @else
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center text-white font-bold text-sm">
                                {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">{{ $employee->full_name }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs font-bold rounded">
                                        {{ $employee->employee_code }}
                                    </span>
                                    <span class="px-2 py-0.5 {{ $employee->employee_type === 'guru' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700' }} text-xs font-bold rounded">
                                        {{ $employee->employee_type === 'guru' ? 'Guru' : 'Staff (' . str_replace('_', ' ', strtoupper($employee->employee_type)) . ')' }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{ $employee->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-gray-700">{{ $employee->school->name ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $positions = $employee->employeePositions->sortByDesc('is_primary');
                                $totalAllowance = 0;
                            @endphp
                            @if($positions->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach($positions as $empPos)
                                        @php
                                            $posAllowance = $empPos->position->allowance_amount ?? 0;
                                            $isPkl = str_contains(strtolower($empPos->position->position_code ?? ''), 'pembimbing-pkl') || str_contains(strtolower($empPos->position->position_name ?? ''), 'pembimbing pkl');
                                            if ($isPkl) {
                                                $pklRate = \App\Models\Setting::where('group', 'salary_formula')->where('key', 'pkl_honor_rate')->value('value') ?? 43000;
                                                $posAllowance = ($empPos->pkl_supervisor_hours ?? 0) * $pklRate;
                                            }
                                            $totalAllowance += $posAllowance;
                                        @endphp
                                        <div class="flex items-center gap-2 px-3 py-2 bg-white border-l-4 {{ $empPos->is_primary ? 'border-purple-600 bg-gradient-to-r from-purple-50 to-pink-50' : 'border-gray-300 bg-gray-50' }} rounded-r-lg shadow-sm hover:shadow-md transition-shadow group">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2">
                                                    @if($empPos->is_primary)
                                                    <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                    @endif
                                                    <span class="font-bold text-sm {{ $empPos->is_primary ? 'text-purple-700' : 'text-gray-700' }}">
                                                        {{ $empPos->position->display_name ?? '-' }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center gap-2 mt-1">
                                                    @if($empPos->classroom_id)
                                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded flex items-center gap-1">
                                                        <i class="fas fa-school mr-1"></i> {{ $empPos->classroom->class_name ?? '' }}
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-right flex items-center gap-3">
                                                @if(auth()->user()->isKetuaYayasan() || auth()->user()->hasRole('bendahara'))
                                                <div>
                                                    <div class="text-xs text-gray-500">
                                                        @if($isPkl)
                                                            Honor ({{ $empPos->pkl_supervisor_hours ?? 0 }} JP)
                                                        @else
                                                            Tunjangan
                                                        @endif
                                                    </div>
                                                    <div class="font-bold {{ $posAllowance > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                                        {{ number_format($posAllowance, 0, ',', '.') }}
                                                    </div>
                                                </div>
                                                @endif
                                                <form action="{{ route('admin.assignments.positions.destroy-single', [$employee->id, $empPos->position_id]) }}" 
                                                      method="POST" 
                                                      class="inline-block"
                                                      onsubmit="return confirm('Yakin ingin menghapus KHUSUS jabatan [{{ $empPos->position->display_name ?? '-' }}] dari {{ $employee->full_name }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
                                                    <button type="submit" 
                                                            class="w-6 h-6 bg-red-100 hover:bg-red-200 text-red-600 rounded-md flex items-center justify-center transition-colors shadow-sm" 
                                                            title="Hapus khusus jabatan [{{ $empPos->position->display_name ?? '-' }}] ini">
                                                        <i class="fas fa-times text-xs"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($positions->first()->sk_number)
                                        <div class="flex items-center gap-2 mt-2 px-3 py-1.5 bg-amber-50 border-l-4 border-amber-400 rounded-r-lg">
                                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span class="text-xs text-amber-800 font-semibold">SK: {{ $positions->first()->sk_number }}</span>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg">
                                    <span class="text-gray-400"><i class="fas fa-exclamation-triangle text-yellow-500 mr-1"></i></span>
                                    <span class="text-gray-500 text-sm italic">Belum ada penugasan</span>
                                </div>
                            @endif
                        </td>
                        @if(auth()->user()->isKetuaYayasan() || auth()->user()->hasRole('bendahara'))
                        <td class="px-4 py-3 text-right">
                            <div class="font-bold text-sm {{ $totalAllowance > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                Rp {{ number_format($totalAllowance, 0, ',', '.') }}
                            </div>
                            @if($totalAllowance > 0)
                            <span class="text-xs text-gray-400">/bulan</span>
                            @endif
                        </td>
                        @endif
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('admin.assignments.positions.create', ['employee_id' => $employee->id]) }}" 
                                   class="px-2.5 py-1.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-lg flex items-center gap-1 text-xs font-semibold shadow transition-all" 
                                   title="Tambah Jabatan kepada {{ $employee->full_name }}">
                                    <i class="fas fa-plus text-[10px]"></i> Jabatan
                                </a>
                                <a href="{{ route('admin.assignments.positions.edit', $employee->id) }}" 
                                   class="w-8 h-8 bg-amber-100 hover:bg-amber-200 text-amber-600 rounded-lg flex items-center justify-center transition-colors" 
                                   title="Edit Penugasan">
                                    <i class="fas fa-pencil-alt text-xs"></i>
                                </a>
                                @if($positions->isNotEmpty())
                                    <form action="{{ route('admin.assignments.positions.destroy', $employee->id) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('PERHATIAN! Anda akan menghapus SEMUA jabatan ({{ $positions->count() }} jabatan sekaligus) dari {{ $employee->full_name }} untuk tahun ajaran dan semester ini. Yakin ingin melanjutkan?')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
                                        <button type="submit" 
                                                class="w-8 h-8 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg flex items-center justify-center transition-colors" 
                                                title="Hapus SEMUA Penugasan ({{ $positions->count() }} Jabatan Sekaligus)">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-4">
                                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-purple-100 to-pink-100 flex items-center justify-center">
                                    <svg class="w-10 h-10 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-gray-500 font-semibold text-lg">Belum ada data penugasan</p>
                                    <p class="text-gray-400 text-sm mt-1">Silakan tambahkan penugasan jabatan guru</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($employees->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
