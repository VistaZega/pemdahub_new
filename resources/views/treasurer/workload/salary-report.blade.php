@extends('layouts.treasurer')
@section('title', 'Laporan Gaji')

@push('styles')
<style>
@page { 
    size: A4 landscape; 
    margin: 5mm 15mm 15mm 15mm; 
}

@media print {
    body { background: white; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    /* Sembunyikan elemen UI yang tidak perlu dicetak */
    aside, header, nav, .no-print { display: none !important; }
    
    /* Reset padding dan margin utama */
    main { padding: 0 !important; margin: 0 !important; margin-left: 0 !important; width: 100% !important; }
    
    /* Tampilkan elemen khusus cetak */
    .print-only { display: block !important; }
    
    /* Hapus bayangan dan sesuaikan border */
    .shadow-sm, .shadow-lg, .shadow-xl { box-shadow: none !important; }
    .border-gray-100 { border-color: #000 !important; }
    
    /* Pastikan teks tabel hitam legam agar jelas saat di-print PDF */
    th, td, p, span, div { color: #000 !important; }
    
    /* Pastikan tabel mengambil seluruh lebar kertas */
    table { width: 100% !important; }
}
.print-only { display: none; }
</style>
@endpush

@section('content')
@php
    $ketuaYayasanUser = \App\Models\User::where('role', 'ketua_yayasan')->first()
        ?? \App\Models\User::where('username', 'yulzega')->first();
    $ketuaYayasanName = $ketuaYayasanUser?->full_name 
        ?? $ketuaYayasanUser?->name 
        ?? \App\Models\Setting::getValue('ketua_yayasan_name') 
        ?? 'YULIANUS ZEGA, S.Kom., M.Pd.T';
@endphp

<div class="space-y-6">
    <div class="mb-8 no-print">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white">
                    <i class="fas fa-chart-bar text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Laporan Gaji</h1>
                    <p class="text-gray-600 mt-1">Rekapitulasi penggajian pegawai per sekolah</p>
                </div>
            </div>
            <div class="flex gap-3 no-print">
                @if($schoolId)
                <button onclick="window.print()"
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl hover:shadow-lg transition">
                    <i class="fas fa-file-pdf mr-2"></i> Cetak PDF
                </button>
                <a href="{{ route('treasurer.workload.salary-report.export', ['school_id' => $schoolId, 'academic_year_id' => $yearId, 'semester_id' => $semesterId]) }}"
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl hover:shadow-lg transition">
                    <i class="fas fa-file-csv mr-2"></i> Export CSV
                </a>
                @endif
                <a href="{{ route('treasurer.workload.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    @if(session('error'))
    <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-xl"><p class="text-red-700">{{ session('error') }}</p></div>
    @endif

    {{-- Filter & Stats --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 no-print">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <form method="GET" class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Sekolah</label>
                    <select name="school_id" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Pilih Sekolah --</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ $schoolId == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Tahun Ajaran</label>
                    <select name="academic_year_id" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ $yearId == $ay->id ? 'selected' : '' }}>{{ $ay->year }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Semester</label>
                    <select name="semester_id" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ $semesterId == $sem->id ? 'selected' : '' }}>{{ $sem->semester_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition text-sm font-semibold shadow-sm">
                        <i class="fas fa-filter mr-1"></i> Tampilkan
                    </button>
                </div>
            </form>

            @if($schoolId)
            <div class="flex items-center gap-6 lg:border-l lg:pl-6 h-full">
                <div class="text-center">
                    <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Pegawai</div>
                    <div class="text-xl font-bold text-gray-900">{{ $employees->count() }}</div>
                </div>
                <div class="text-right">
                    <div class="text-[10px] text-indigo-500 font-bold uppercase tracking-wider">Total THP (Seluruh Unit)</div>
                    <div class="text-2xl font-bold text-green-600">Rp&nbsp;{{ number_format($totalGaji, 0, ',', '.') }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    @if(!$schoolId)
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-12 text-center mt-6">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-school text-blue-500 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-blue-800 mb-1">Pilih Sekolah</h3>
            <p class="text-sm text-blue-600">Pilih sekolah terlebih dahulu untuk menampilkan laporan gaji</p>
        </div>
    @else
    <!-- Report Table -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden print:mt-0 print:border-none print:shadow-none print:rounded-none">
        
        {{-- Print Kop Surat (Logo digeser sedikit ke kanan dengan padding-left) --}}
        <div class="print-only mb-6 pb-4 border-b-[3px] border-black">
            <table class="w-full border-collapse">
                <tr>
                    <td class="text-left align-middle" style="width: 110px; padding-left: 20px;">
                        <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo Yayasan" class="w-20 h-auto" style="max-width: 80px;">
                    </td>
                    <td class="text-center align-middle">
                        <h1 class="text-xl font-bold uppercase tracking-wide" style="font-family: 'Times New Roman', Times, serif; font-size: 16pt; margin: 0;">
                            Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)
                        </h1>
                        <h2 class="text-base font-bold mt-1 tracking-wide" style="font-family: 'Times New Roman', Times, serif; font-size: 13pt; margin: 2px 0 0 0;">
                            Keputusan Yayasan Perguruan Pembda Nias tentang Gaji/Honor Guru/Pegawai<br>
                            Tahun Pelajaran {{ $academicYears->firstWhere('id', $yearId)->year ?? '' }}
                        </h2>
                        <h3 class="text-base font-bold mt-2 uppercase tracking-widest underline" style="font-family: 'Times New Roman', Times, serif; font-size: 13pt; margin: 4px 0 0 0;">
                            {{ $schools->firstWhere('id', $schoolId)->name ?? '' }}
                        </h3>
                    </td>
                    <td style="width: 110px; padding-right: 20px;"></td>
                </tr>
            </table>
        </div>

        @php
            $selectedSchool = $schools->firstWhere('id', $schoolId);
            $isYayasanSchool = $selectedSchool && ($selectedSchool->type === 'yayasan' || $selectedSchool->isYayasan());
        @endphp
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100/70 border-b border-gray-200 text-gray-700 text-xs font-bold uppercase tracking-wider">
                        <th class="px-3 py-4 text-left w-12" rowspan="2">No</th>
                        <th class="px-4 py-4 text-left" rowspan="2">Nama Pegawai / Status</th>
                        <th class="px-3 py-4 text-right" rowspan="2">Gaji Pokok</th>
                        <th class="px-4 py-4 text-left">Tunjangan Jabatan</th>
                        @if(!$isYayasanSchool)
                        <th class="px-3 py-4 text-right">Honor Mengajar</th>
                        @endif
                        <th class="px-5 py-4 text-left">Tunjangan Yayasan</th>
                        <th class="px-4 py-4 text-right" rowspan="2">THP</th>
                    </tr>
                    <tr class="text-gray-500 text-[11px] font-bold uppercase tracking-wider text-center">
                        <th class="px-4 py-2 text-left">Rincian Jabatan</th>
                        @if(!$isYayasanSchool)
                        <th class="px-3 py-2 text-right">Rincian Jam & Tarif</th>
                        @endif
                        <th class="px-5 py-2 text-left">Rincian Tunjangan</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($employees as $idx => $emp)
                    @php $sal = $salaryData[$emp->id] ?? []; @endphp
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td rowspan="2" class="px-3 py-4 border-b border-gray-100 text-xs text-gray-400 text-center font-medium align-top">{{ $idx + 1 }}</td>
                        <td rowspan="2" class="px-4 py-4 border-b border-gray-100 align-top">
                            <div class="font-bold text-gray-900 leading-tight text-[13px]">{{ $emp->full_name }}</div>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-[10px] text-gray-500 font-mono">{{ $emp->employee_code ?? '-' }}</span>
                                <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                @php
                                    $statusClasses = [
                                        'yayasan' => 'text-emerald-600',
                                        'pns' => 'text-blue-600',
                                        'honorer' => 'text-amber-600',
                                        'kontrak' => 'text-purple-600',
                                    ];
                                    $statusColor = $statusClasses[$emp->employment_status ?? ''] ?? 'text-gray-500';
                                @endphp
                                <span class="text-[10px] font-bold uppercase tracking-tighter {{ $statusColor }}">{{ $emp->employment_status ?? '-' }}</span>
                            </div>
                        </td>
                        <td rowspan="2" class="px-3 py-4 border-b border-gray-100 text-right text-xs font-medium text-gray-700 align-top pt-5">Rp&nbsp;{{ number_format($sal['gaji_pokok'] ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 pt-4 pb-1 align-top">
                            <div class="space-y-1">
                                @if(!empty($sal['jabatan_details']))
                                    @foreach($sal['jabatan_details'] as $detail)
                                        <div class="flex justify-between items-start gap-4 text-[10px] leading-tight">
                                            <span class="text-gray-600 font-medium">• {{ $detail['name'] }}</span>
                                            <span class="text-gray-900 font-bold whitespace-nowrap">Rp&nbsp;{{ number_format($detail['amount'], 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-gray-400 text-[10px] italic">-</div>
                                @endif
                            </div>
                        </td>
                        @if(!$isYayasanSchool)
                        <td class="px-3 pt-4 pb-1 align-top">
                            <div class="text-[10px] text-gray-500 font-medium">
                                <div class="flex justify-between text-gray-600 mb-0.5">
                                    <span>Jam</span>
                                    <span>{{ $sal['jam_mengajar'] ?? 0 }} | {{ $sal['jam_wajib'] ?? 0 }} | {{ $sal['jam_honor'] ?? 0 }}</span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>Tarif</span>
                                    <span>Rp&nbsp;{{ number_format($sal['honor_per_jam'] ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </td>
                        @endif
                        <td class="px-5 pt-4 pb-1 align-top">
                            <div class="space-y-0.5">
                                @php $totalYayasan = (($sal['tunjangan_keluarga'] ?? 0) + ($sal['tunjangan_anak'] ?? 0) + ($sal['tunjangan_beras'] ?? 0)); @endphp
                                @if(($sal['tunjangan_keluarga'] ?? 0) > 0) 
                                    <div class="flex justify-between text-[10px] font-medium leading-tight text-pink-600 italic">
                                        <span>Keluarga</span> 
                                        <span>Rp&nbsp;{{ number_format($sal['tunjangan_keluarga'], 0, ',', '.') }}</span>
                                    </div> 
                                @endif
                                @if(($sal['tunjangan_anak'] ?? 0) > 0) 
                                    <div class="flex justify-between text-[10px] font-medium leading-tight text-blue-600 italic">
                                        <span>Anak</span> 
                                        <span>Rp&nbsp;{{ number_format($sal['tunjangan_anak'], 0, ',', '.') }}</span>
                                    </div> 
                                @endif
                                @if(($sal['tunjangan_beras'] ?? 0) > 0) 
                                    <div class="flex justify-between text-[10px] font-medium leading-tight text-amber-600 italic">
                                        <span>Beras</span> 
                                        <span>Rp&nbsp;{{ number_format($sal['tunjangan_beras'], 0, ',', '.') }}</span>
                                    </div> 
                                @endif
                            </div>
                        </td>
                        <td rowspan="2" class="px-4 py-4 border-b border-gray-100 text-right align-top pt-5">
                            <div class="text-[15px] font-bold text-blue-800 tracking-tight">Rp&nbsp;{{ number_format($sal['thp'] ?? 0, 0, ',', '.') }}</div>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-4 pb-4 pt-1 align-bottom border-b border-gray-100">
                            @if(($sal['tunjangan_jabatan'] ?? 0) > 0)
                            <div class="text-right border-t border-gray-200 pt-1 font-bold text-indigo-700 text-[11px]">
                                Rp&nbsp;{{ number_format($sal['tunjangan_jabatan'], 0, ',', '.') }}
                            </div>
                            @else
                            <div class="text-[11px] text-gray-400 italic text-center pt-1">-</div>
                            @endif
                        </td>
                        @if(!$isYayasanSchool)
                        <td class="px-3 pb-4 pt-1 align-bottom border-b border-gray-100">
                            @if(($sal['honor_mengajar'] ?? 0) > 0)
                            <div class="text-right border-t border-gray-200 pt-1 text-xs font-bold text-gray-900 italic">
                                Rp&nbsp;{{ number_format($sal['honor_mengajar'], 0, ',', '.') }}
                            </div>
                            @else
                            <div class="text-[11px] text-gray-400 italic text-center pt-1">-</div>
                            @endif
                        </td>
                        @endif
                        <td class="px-5 pb-4 pt-1 align-bottom border-b border-gray-100">
                            @if($totalYayasan > 0)
                            <div class="text-[11px] font-bold text-emerald-700 border-t border-emerald-100 pt-1 text-right">
                                Rp&nbsp;{{ number_format($totalYayasan, 0, ',', '.') }}
                            </div>
                            @else
                            <div class="text-[11px] text-gray-400 italic text-center pt-1">-</div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="{{ $isYayasanSchool ? 6 : 7 }}" class="px-6 py-12 text-center text-gray-400 italic font-medium">Data gaji belum tersedia untuk unit ini.</td></tr>
                    @endforelse
                @if($employees->count() > 0)
                    <tr class="bg-gray-50 border-t-2 border-gray-100 font-bold text-[12px] uppercase">
                        <td colspan="3" class="px-6 py-4 text-right text-gray-500">TOTAL SELURUH UNIT</td>
                        <td class="px-4 py-4 text-right text-gray-900 tracking-tighter">Rp&nbsp;{{ number_format(collect($salaryData)->sum('tunjangan_jabatan'), 0, ',', '.') }}</td>
                        @if(!$isYayasanSchool)
                        <td class="px-3 py-4 text-right text-gray-900 tracking-tighter">Rp&nbsp;{{ number_format(collect($salaryData)->sum('honor_mengajar'), 0, ',', '.') }}</td>
                        @endif
                        <td class="px-5 py-4 text-right text-emerald-800 tracking-tighter">
                            Rp&nbsp;{{ number_format(collect($salaryData)->sum('tunjangan_keluarga') + collect($salaryData)->sum('tunjangan_anak') + collect($salaryData)->sum('tunjangan_beras'), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-4 text-right text-blue-900 text-lg tracking-tighter">Rp&nbsp;{{ number_format($totalGaji, 0, ',', '.') }}</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>

        {{-- Print Signature --}}
        <div class="print-only mt-10 text-right">
            <div class="inline-block text-center mr-12" style="font-family: 'Times New Roman', Times, serif;">
                <p class="text-md">Gunungsitoli, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p class="text-md font-bold mt-1">Pengurus Yayasan Perguruan Pembangunan Daerah Nias</p>
                <p class="text-md font-bold mt-1">Ketua,</p>
                
                @if(file_exists(public_path('images/ttd-ketua.png')))
                    <div class="relative h-20 mt-2 flex items-center justify-center">
                        <img src="{{ asset('images/ttd-ketua.png') }}" alt="Tanda Tangan" class="h-20 object-contain absolute z-10" style="mix-blend-mode: multiply;">
                    </div>
                @else
                    <div class="h-20"></div>
                @endif
                
                <p class="text-md font-bold underline relative z-20 uppercase">{{ $ketuaYayasanName }}</p>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
