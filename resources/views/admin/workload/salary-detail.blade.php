@extends('layouts.admin')
@section('title', 'Detail Gaji - ' . $employee->full_name)
@section('content')
<div class="space-y-6">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white">
                    <i class="fas fa-file-invoice-dollar text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Detail Komponen Gaji</h1>
                    <p class="text-gray-600 mt-1">{{ $employee->full_name }} — {{ ucfirst($employee->employment_status ?? '-') }}</p>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.workload.salary-slip', ['employee' => $employee, 'academic_year_id' => $year->id, 'semester_id' => $semester->id]) }}" class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded-xl hover:bg-green-600"><i class="fas fa-eye mr-2"></i>Lihat Slip</a>
                <a href="{{ route('admin.workload.salary-slip-pdf', ['employee' => $employee, 'academic_year_id' => $year->id, 'semester_id' => $semester->id]) }}" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 shadow-sm"><i class="fas fa-file-pdf mr-2"></i>Download PDF</a>
                <a href="{{ route('admin.workload.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50"><i class="fas fa-arrow-left mr-2"></i>Kembali</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Employee Info -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pegawai</h2>
            <dl class="grid grid-cols-2 gap-4">
                <div><dt class="text-xs text-gray-500">Nama</dt><dd class="text-sm font-medium">{{ $employee->full_name }}</dd></div>
                <div><dt class="text-xs text-gray-500">Kode Pegawai</dt><dd class="text-sm">{{ $employee->employee_code ?? '-' }}</dd></div>
                <div><dt class="text-xs text-gray-500">Status Kepegawaian</dt><dd class="text-sm">{{ ucfirst($employee->employment_status ?? '-') }}</dd></div>
                <div><dt class="text-xs text-gray-500">Status Nikah</dt><dd class="text-sm">{{ ucfirst($employee->marital_status ?? '-') }}</dd></div>
                <div><dt class="text-xs text-gray-500">Jumlah Anak</dt><dd class="text-sm">{{ $employee->children_count ?? 0 }}</dd></div>
                <div><dt class="text-xs text-gray-500">Sekolah</dt><dd class="text-sm">{{ $employee->school->name ?? '-' }}</dd></div>
            </dl>
        </div>

        <!-- Workload Summary -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Ringkasan Beban Kerja</h2>
            @if($workload)
            <dl class="grid grid-cols-2 gap-4">
                <div><dt class="text-xs text-gray-500">Total Jam Mengajar</dt><dd class="text-sm font-medium">{{ $workload->total_teaching_hours }} jam/minggu</dd></div>
                <div><dt class="text-xs text-gray-500">Total Kelas</dt><dd class="text-sm">{{ $workload->total_teaching_classes ?? 0 }}</dd></div>
                <div><dt class="text-xs text-gray-500">Total Mata Pelajaran</dt><dd class="text-sm">{{ $workload->total_teaching_subjects ?? 0 }}</dd></div>
                <div><dt class="text-xs text-gray-500">Total Jabatan</dt><dd class="text-sm">{{ $workload->total_position_count ?? 0 }}</dd></div>
                <div><dt class="text-xs text-gray-500">Periode</dt><dd class="text-sm">{{ $semester->semester_name ?? '-' }} — {{ $year->year ?? '-' }}</dd></div>

            </dl>
            @else
            <p class="text-gray-500 text-center py-4">Belum dihitung. Silakan hitung beban kerja terlebih dahulu.</p>
            @endif
        </div>
    </div>

    <!-- Salary Components from slipData -->
    @if(!empty($slipData['components']))
    <div class="bg-white rounded-2xl shadow-lg p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-6">Komponen Gaji</h2>
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="border-b-2 border-gray-200">
                    <th class="py-3 text-left text-sm font-semibold text-gray-600">Komponen</th>
                    <th class="py-3 text-right text-sm font-semibold text-gray-600">Jumlah</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($slipData['components'] as $component)
                <tr>
                    <td class="py-3 text-sm text-gray-700">{{ $component['label'] }}</td>
                    <td class="py-3 text-right text-sm font-medium">Rp {{ number_format($component['amount'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="bg-emerald-50 font-bold">
                    <td class="py-3 px-2 text-sm text-emerald-800">TOTAL PENGHASILAN BRUTO</td>
                    <td class="py-3 px-2 text-right text-sm text-emerald-800">Rp {{ number_format($slipData['gross_pay'], 0, ',', '.') }}</td>
                </tr>
                @if(!empty($slipData['deductions']))
                    <tr class="bg-gray-100"><td colspan="2" class="py-1 px-2 text-[10px] uppercase font-bold text-gray-400">Potongan</td></tr>
                    @foreach($slipData['deductions'] as $deduction)
                    <tr class="text-red-600 italic">
                        <td class="py-3 px-2 text-sm">{{ $deduction['label'] }}</td>
                        <td class="py-3 px-2 text-right text-sm">-Rp {{ number_format($deduction['amount'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    <tr class="bg-red-50 font-bold">
                        <td class="py-3 px-2 text-sm text-red-800">TOTAL POTONGAN</td>
                        <td class="py-3 px-2 text-right text-sm text-red-800">-Rp {{ number_format($slipData['total_deductions'], 0, ',', '.') }}</td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-gray-300 bg-indigo-50">
                    <td class="py-4 px-2 text-sm font-bold text-gray-900 uppercase">TAKE HOME PAY (THP Netto)</td>
                    <td class="py-4 px-2 text-right text-lg font-bold text-indigo-700">Rp {{ number_format($slipData['take_home_pay'] ?? 0, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Position Assignments -->
    @php $positions = $employee?->activePositions ?? collect(); @endphp
    @if($positions->count())
    <div class="bg-white rounded-2xl shadow-lg p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Jabatan Aktif</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($positions as $pos)
            <div class="border rounded-xl p-4">
                <div class="text-sm font-medium text-gray-900">{{ $pos->position_name ?? '-' }}</div>
                @php
                    $allowance = $pos->pivot->position_allowance > 0 ? $pos->pivot->position_allowance : ($pos->allowance_amount ?? 0);
                @endphp
                <div class="text-xs text-gray-500 mt-1">Tunjangan: Rp {{ number_format($allowance, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-400">Sejak: {{ $pos->pivot->start_date ? \Carbon\Carbon::parse($pos->pivot->start_date)->format('d M Y') : '-' }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @endif
</div>
@endsection
