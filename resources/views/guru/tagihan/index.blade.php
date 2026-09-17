@extends('layouts.guru')

@section('title', 'Progress Biaya Pendidikan')

@section('content')
<div class="px-6 py-6 pb-20 w-full space-y-6">

    {{-- Header Banner (Neo-Brutalism) --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl p-6 border-2 border-black" style="background: linear-gradient(135deg, #090d16 0%, #065f46 50%, #047857 100%) !important;">
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-xl md:text-2xl font-black text-white flex items-center gap-3" style="color: #ffffff !important;">
                    <div class="w-10 h-10 rounded-xl bg-amber-400 border-2 border-black flex items-center justify-center text-black shadow-sm text-lg">
                        <i class="fas fa-file-invoice-dollar text-black"></i>
                    </div>
                    Progress Biaya Pendidikan
                </h1>
                <p class="text-xs md:text-sm font-bold text-emerald-200 mt-1" style="color: #a7f3d0 !important;">
                    Kelas <span class="font-black text-amber-300">{{ $classroom->class_name }}</span> - Tahun Ajaran {{ $activeYear->year }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('guru.walikelas.cbt-dispensasi.index', ['classroom_id' => $classroom->id]) }}" 
                   class="bg-rose-400 hover:bg-rose-300 text-black border-2 border-black px-4 py-2 rounded-2xl font-black text-xs uppercase tracking-wider flex items-center gap-2 shadow-sm transition">
                    <i class="fas fa-shield-halved text-black"></i> Dispensasi Ujian CBT
                </a>
                <span class="bg-amber-400 text-black border-2 border-black px-4 py-2 rounded-2xl font-black text-xs uppercase tracking-wider flex items-center gap-2 shadow-sm">
                    <i class="fas fa-users text-black"></i> {{ $students->count() }} Siswa
                </span>
            </div>
        </div>
    </div>

    <!-- Summary Bento Boxes -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Lunas -->
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-20"><i class="fas fa-check-circle text-8xl"></i></div>
            <div class="relative z-10">
                <p class="text-emerald-100 text-sm font-medium uppercase tracking-wider mb-1">Item Biaya Lunas</p>
                <h3 class="text-4xl font-bold">{{ $overallStats['lunas_count'] }} <span class="text-lg font-normal">Item</span></h3>
                <div class="mt-4 flex items-center gap-2 text-sm">
                    <span class="bg-white/20 px-2 py-1 rounded-lg"><i class="fas fa-user-check mr-1"></i> {{ $overallStats['student_lunas'] }} Siswa Lunas Total</span>
                </div>
            </div>
        </div>

        <!-- Total Tunggakan -->
        <div class="bg-gradient-to-br from-rose-500 to-red-600 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-20"><i class="fas fa-times-circle text-8xl"></i></div>
            <div class="relative z-10">
                <p class="text-rose-100 text-sm font-medium uppercase tracking-wider mb-1">Item Menunggak</p>
                <h3 class="text-4xl font-bold">{{ $overallStats['belum_bayar_count'] }} <span class="text-lg font-normal">Item</span></h3>
                <div class="mt-4 flex items-center gap-2 text-sm">
                    <span class="bg-white/20 px-2 py-1 rounded-lg"><i class="fas fa-user-times mr-1"></i> {{ $overallStats['student_belum'] }} Siswa Belum Bayar</span>
                </div>
            </div>
        </div>

        <!-- Progress Keseluruhan -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-center">
            @php
                $overallPercentage = $overallStats['total_bills'] > 0 
                    ? round(($overallStats['lunas_count'] / $overallStats['total_bills']) * 100) 
                    : 0;
            @endphp
            <p class="text-gray-500 text-sm font-medium uppercase tracking-wider mb-3">Progress Pelunasan Kelas</p>
            <div class="flex items-end gap-2 mb-2">
                <h3 class="text-4xl font-black text-gray-800">{{ $overallPercentage }}%</h3>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-3">
                <div class="bg-gradient-to-r from-emerald-400 to-teal-500 h-3 rounded-full transition-all duration-1000" style="width: {{ $overallPercentage }}%"></div>
            </div>
            <p class="text-xs text-gray-400 mt-3">Target keseluruhan (termasuk tunggakan): {{ $overallStats['lunas_count'] + $overallStats['belum_bayar_count'] }} Item Biaya Wajib</p>
        </div>
    </div>

    <!-- Legend -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
        <div class="flex flex-wrap items-center gap-6 text-xs font-semibold">
            <span class="text-gray-500 uppercase tracking-wider"><i class="fas fa-info-circle mr-1"></i> Keterangan Warna:</span>
            <span class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-md bg-emerald-500 inline-flex items-center justify-center text-white text-[10px]"><i class="fas fa-check"></i></span>
                <span class="text-gray-700">Lunas</span>
            </span>
            <span class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-md bg-rose-500 inline-flex items-center justify-center text-white text-[10px]"><i class="fas fa-times"></i></span>
                <span class="text-gray-700">Menunggak (Lewat Batas)</span>
            </span>
            <span class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-md bg-amber-400 inline-flex items-center justify-center text-white text-[10px]"><i class="fas fa-clock"></i></span>
                <span class="text-gray-700">Belum Waktunya</span>
            </span>
            <span class="flex items-center gap-2">
                <span class="w-5 h-5 rounded-md bg-gray-200 inline-flex items-center justify-center text-gray-400 text-[10px]"><i class="fas fa-minus"></i></span>
                <span class="text-gray-700">Tidak Ada Tagihan</span>
            </span>
        </div>
    </div>

    <!-- Matrix Heatmap per Recurring Payment Type -->
    @forelse($recurringTypes as $typeId => $typeName)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center justify-between">
            <h2 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                <i class="fas fa-calendar-alt text-emerald-500"></i> {{ $typeName }}
                <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded-full ml-1">Bulanan</span>
            </h2>
            <div class="relative">
                <input type="text" data-search-for="{{ $typeId }}" placeholder="Cari siswa..." class="pl-9 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-xs w-48 transition-all bg-white">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse" id="matrix-{{ $typeId }}">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="py-3 px-4 text-left font-semibold text-gray-600 text-xs uppercase tracking-wider sticky left-0 bg-gray-50 z-10 min-w-[180px]">Nama Siswa</th>
                        @foreach($months as $m)
                            @php
                                $monthLabel = substr(\Carbon\Carbon::create()->month($m)->translatedFormat('F'), 0, 3);
                            @endphp
                            <th class="py-3 px-0.5 text-center font-bold text-gray-500 text-[10px] uppercase tracking-wider w-[42px]">{{ $monthLabel }}</th>
                        @endforeach
                        <th class="py-3 px-3 text-center font-semibold text-gray-600 text-xs uppercase tracking-wider w-[70px]">Progres</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($students as $student)
                    @php
                        $studentBillsForType = collect();
                        $lunasCount = 0;
                        $totalCount = 0;
                        foreach ($months as $m) {
                            $key = $typeId . '_' . $m;
                            if (isset($student->month_map[$key])) {
                                $studentBillsForType->push($student->month_map[$key]);
                                $totalCount++;
                                if ($student->month_map[$key]->status === 'lunas') $lunasCount++;
                            }
                        }
                        $progressPct = $totalCount > 0 ? round(($lunasCount / $totalCount) * 100) : 0;
                    @endphp
                    <tr class="hover:bg-emerald-50/20 transition-colors student-matrix-row" data-name="{{ strtolower($student->full_name) }}">
                        <td class="py-3 px-4 sticky left-0 bg-white z-10 border-r border-gray-100">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ strtoupper(substr($student->full_name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-gray-800 text-xs truncate">{{ $student->full_name }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $student->nisn ?? $student->nis }}</p>
                                </div>
                            </div>
                        </td>
                        @foreach($months as $m)
                            @php
                                $key = $typeId . '_' . $m;
                                $bill = $student->month_map[$key] ?? null;
                                
                                if (!$bill) {
                                    // No bill for this month
                                    $cellBg = 'bg-gray-100';
                                    $cellIcon = '<i class="fas fa-minus text-gray-300"></i>';
                                    $tooltip = 'Tidak ada tagihan';
                                } elseif ($bill->status === 'lunas') {
                                    $cellBg = 'bg-emerald-500';
                                    $cellIcon = '<i class="fas fa-check text-white"></i>';
                                    $tooltip = 'Lunas — Rp ' . number_format($bill->amount, 0, ',', '.');
                                } elseif ($bill->isOverdue()) {
                                    $cellBg = 'bg-rose-500';
                                    $cellIcon = '<i class="fas fa-times text-white"></i>';
                                    $sisa = $bill->amount - $bill->paid_amount;
                                    $tooltip = 'Menunggak — Sisa: Rp ' . number_format($sisa, 0, ',', '.');
                                    if ($bill->paid_amount > 0) {
                                        $tooltip .= ' (Dibayar: Rp ' . number_format($bill->paid_amount, 0, ',', '.') . ')';
                                    }
                                } else {
                                    $cellBg = 'bg-amber-400';
                                    $cellIcon = '<i class="fas fa-clock text-white"></i>';
                                    $tooltip = 'Belum waktunya — Rp ' . number_format($bill->amount, 0, ',', '.');
                                }
                            @endphp
                            <td class="py-1.5 px-0.5 text-center">
                                <div class="w-8 h-8 mx-auto rounded-md {{ $cellBg }} flex items-center justify-center text-xs cursor-default transition-all hover:scale-110 hover:shadow-md" title="{{ $tooltip }}">
                                    {!! $cellIcon !!}
                                </div>
                            </td>
                        @endforeach
                        <td class="py-2 px-3 text-center">
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-xs font-black {{ $progressPct == 100 ? 'text-emerald-600' : ($progressPct > 0 ? 'text-amber-600' : 'text-gray-400') }}">{{ $progressPct }}%</span>
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full transition-all {{ $progressPct == 100 ? 'bg-emerald-500' : 'bg-amber-400' }}" style="width: {{ $progressPct }}%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
        <div class="bg-white rounded-2xl p-12 shadow-sm border border-gray-100 text-center">
            <i class="fas fa-calendar-times text-5xl text-gray-200 mb-4"></i>
            <p class="text-gray-400 text-lg font-medium">Belum ada biaya bulanan yang dibebankan ke kelas ini.</p>
            <p class="text-gray-300 text-sm mt-1">Hubungi Admin untuk meng-generate tagihan biaya pendidikan.</p>
        </div>
    @endforelse

    <!-- Non-Recurring Bills Section -->
    @php
        $hasNonRecurring = $students->contains(fn($s) => $s->non_recurring_bills->isNotEmpty());
    @endphp
    @if($hasNonRecurring)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <h2 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                <i class="fas fa-receipt text-emerald-500"></i> Biaya Non-Bulanan (Sekali Bayar)
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="py-3 px-4 text-left font-semibold text-gray-600 text-xs uppercase tracking-wider min-w-[180px]">Nama Siswa</th>
                        <th class="py-3 px-4 text-left font-semibold text-gray-600 text-xs uppercase tracking-wider">Jenis Biaya</th>
                        <th class="py-3 px-4 text-right font-semibold text-gray-600 text-xs uppercase tracking-wider">Nominal</th>
                        <th class="py-3 px-4 text-right font-semibold text-gray-600 text-xs uppercase tracking-wider">Dibayar</th>
                        <th class="py-3 px-4 text-center font-semibold text-gray-600 text-xs uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($students as $student)
                        @foreach($student->non_recurring_bills as $bill)
                        <tr class="hover:bg-emerald-50/20 transition-colors">
                            <td class="py-3 px-4">
                                <p class="font-bold text-gray-800 text-xs">{{ $student->full_name }}</p>
                            </td>
                            <td class="py-3 px-4 text-xs text-gray-700">{{ $bill->paymentType->type_name ?? '-' }}</td>
                            <td class="py-3 px-4 text-right text-xs font-semibold text-gray-800">Rp {{ number_format($bill->amount, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right text-xs font-semibold text-emerald-600">Rp {{ number_format($bill->paid_amount, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-center">
                                @if($bill->status === 'lunas')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700"><i class="fas fa-check-circle"></i> Lunas</span>
                                @elseif($bill->isOverdue())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700"><i class="fas fa-exclamation-circle"></i> Menunggak</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700"><i class="far fa-clock"></i> Mendatang</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

<script>
    // Search filter for each matrix table
    document.querySelectorAll('[data-search-for]').forEach(input => {
        input.addEventListener('keyup', function() {
            const typeId = this.dataset.searchFor;
            const filter = this.value.toLowerCase();
            const table = document.getElementById('matrix-' + typeId);
            if (!table) return;
            
            table.querySelectorAll('.student-matrix-row').forEach(row => {
                const name = row.dataset.name || '';
                row.style.display = name.includes(filter) ? '' : 'none';
            });
        });
    });
</script>
@endsection
