@extends('layouts.admin')

@section('title', 'Absensi Pegawai')

@section('content')
<div class="space-y-6">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-600 shadow-lg">
                    <i class="fas fa-fingerprint text-2xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Absensi Pegawai</h1>
                    <p class="text-gray-600 mt-1">Monitoring kehadiran harian pegawai</p>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.employees.attendance.monitoring') }}"
                    class="flex items-center gap-2 px-5 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                    <i class="fas fa-satellite-dish animate-pulse"></i> Live Monitoring
                </a>
                <a href="{{ route('admin.employees.attendance.rekap') }}"
                    class="flex items-center gap-2 px-5 py-3 bg-white border border-gray-200 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-all">
                    <i class="fas fa-chart-bar"></i> Rekapitulasi
                </a>
                @if($schoolId)
                <a href="{{ route('admin.employees.attendance.bulk', ['date' => $date, 'school_id' => $schoolId]) }}"
                    class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-teal-600 to-emerald-700 text-white rounded-xl font-medium hover:from-teal-700 hover:to-emerald-800 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                    <i class="fas fa-edit"></i> Input Massal
                </a>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded-xl">
        <div class="flex items-center gap-3"><i class="fas fa-check-circle text-green-500"></i><p class="text-green-800 font-medium">{{ session('success') }}</p></div>
    </div>
    @endif

    <!-- Filter -->
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-6">
        <form action="{{ route('admin.employees.attendance.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="w-full md:w-48">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-1">Tanggal</label>
                <input type="date" name="date" value="{{ $date }}"
                    class="w-full px-4 py-2.5 bg-gray-50/50 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:bg-white transition-all">
            </div>
            <div class="w-full md:w-56">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-1">Unit Sekolah</label>
                <select name="school_id" required class="w-full px-4 py-2.5 bg-gray-50/50 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 transition-all appearance-none">
                    <option value="">Pilih Unit Sekolah</option>
                    @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ $schoolId == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-md transition-all"><i class="fas fa-filter text-xs mr-1"></i> Tampilkan</button>
        </form>
    </div>

    @if($schoolId)
    <div id="live_attendance_wrapper">
    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
        @php
            $cardColors = ['hadir' => 'green', 'sakit' => 'yellow', 'izin' => 'blue', 'alpha' => 'red', 'dinas_luar' => 'purple', 'belum' => 'gray'];
            $cardLabels = ['hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alpha' => 'Alpha', 'dinas_luar' => 'Dinas Luar', 'belum' => 'Belum'];
            $cardIcons = ['hadir' => 'check-circle', 'sakit' => 'stethoscope', 'izin' => 'envelope', 'alpha' => 'exclamation-triangle', 'dinas_luar' => 'car', 'belum' => 'question-circle'];
        @endphp
        @foreach(['hadir', 'sakit', 'izin', 'alpha', 'dinas_luar', 'belum'] as $key)
        <div class="bg-white rounded-2xl shadow-sm border border-{{ $cardColors[$key] }}-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase">{{ $cardLabels[$key] }}</p>
                    <p class="text-2xl font-bold text-{{ $cardColors[$key] }}-600 mt-1">{{ $stats[$key] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-{{ $cardColors[$key] }}-100 flex items-center justify-center">
                    <i class="fas fa-{{ $cardIcons[$key] }} text-{{ $cardColors[$key] }}-500"></i>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold">No</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Pegawai</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold">Status</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold">Jam Masuk</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold">Jam Keluar</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold">Via</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Keterangan</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($employees as $index => $emp)
                    @php $att = $attendances->get($emp->id); @endphp
                    <tr class="hover:bg-blue-50 transition-colors {{ $att && $att->isLate() ? 'bg-orange-50' : '' }}">
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-400 to-emerald-500 flex items-center justify-center text-white font-bold text-sm">
                                    {{ strtoupper(substr($emp->full_name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $emp->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $emp->employee_code }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($att)
                            @php $sc = \App\Models\EmployeeAttendance::STATUS_COLORS[$att->status] ?? 'gray'; @endphp
                            <span class="px-3 py-1 bg-{{ $sc }}-100 text-{{ $sc }}-800 text-xs font-semibold rounded-full">{{ $att->status_label }}</span>
                            @else
                            <span class="px-3 py-1 bg-gray-100 text-gray-500 text-xs font-semibold rounded-full">Belum</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center text-sm font-mono {{ $att && $att->isLate() ? 'text-orange-600 font-bold' : 'text-gray-700' }}">
                            {{ $att?->time_in ? substr($att->time_in, 0, 5) : '-' }}
                            @if($att && $att->isLate()) <i class="fas fa-exclamation-circle text-orange-500 ml-1" title="Terlambat"></i> @endif
                        </td>
                        <td class="px-6 py-4 text-center text-sm font-mono text-gray-700">{{ $att?->time_out ? substr($att->time_out, 0, 5) : '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($att)
                                @if($att->recorded_via === 'gps')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-blue-100 text-blue-700 text-xs font-bold rounded-full border border-blue-200">
                                        <i class="fas fa-location-dot text-[10px]"></i> GPS
                                    </span>
                                @elseif($att->recorded_via === 'rfid')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-amber-100 text-amber-800 text-xs font-bold rounded-full border border-amber-200">
                                        <i class="fas fa-id-card text-[10px]"></i> RFID
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-slate-100 text-slate-600 text-xs font-bold rounded-full border border-slate-200">
                                        <i class="fas fa-pen text-[10px]"></i> MANUAL
                                    </span>
                                @endif
                            @else - @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $att?->notes ?? '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($att)
                            <form action="{{ route('admin.employees.attendance.destroy', $att->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data absensi ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                            @else
                            -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-users text-4xl text-gray-300 mb-2"></i>
                            <p>Belum ada data absensi untuk unit ini pada tanggal tersebut.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>
    @else
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-12 text-center">
        <i class="fas fa-school text-5xl text-gray-300 mb-4"></i>
        <p class="text-gray-600 font-medium text-lg">Pilih unit sekolah untuk melihat data absensi</p>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hanya auto-refresh jika melihat data hari ini
    const isToday = '{{ $date }}' === '{{ today()->toDateString() }}';
    
    if (isToday && document.getElementById('live_attendance_wrapper')) {
        setInterval(() => {
            fetch(window.location.href)
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newWrapper = doc.getElementById('live_attendance_wrapper');
                    if (newWrapper) {
                        document.getElementById('live_attendance_wrapper').innerHTML = newWrapper.innerHTML;
                    }
                })
                .catch(err => console.error('Live update failed:', err));
        }, 5000); // Refresh setiap 5 detik
    }
});
</script>
@endpush
