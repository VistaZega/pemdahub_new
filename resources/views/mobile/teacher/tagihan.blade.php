@extends('mobile.layouts.app')

@section('title', 'Rekap Tagihan Rombel Wali Kelas - PembdaHUB Mobile')

@section('content')
<div class="space-y-4">
    <!-- Header Title & Class Name -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-black text-slate-900">Rekap Tagihan Rombel 💳</h2>
            <p class="text-[11px] text-slate-500 font-bold">Wali Kelas: {{ $classroom->name ?? 'Rombel Saya' }}</p>
        </div>
        <div class="w-10 h-10 rounded-2xl clay-purple flex items-center justify-center text-xl font-black shadow-md">
            💰
        </div>
    </div>

    <!-- Filter Bulan & Tahun Berkenaan -->
    <form action="{{ route('mobile.guru.tagihan') }}" method="GET" class="clay-card p-4 bg-white border-2 border-slate-200 space-y-2">
        <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider block">📅 Pilih Bulan & Tahun Tagihan:</span>
        <div class="grid grid-cols-2 gap-2">
            <select name="month" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:border-purple-600 outline-none">
                @foreach($monthNames as $num => $name)
                    <option value="{{ $num }}" {{ $selectedMonth == $num ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>

            <select name="year" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 text-xs font-bold focus:border-purple-600 outline-none">
                @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                @endfor
            </select>
        </div>
    </form>

    <!-- Ringkasan Stats Top Card (Bulan Berkenaan) -->
    <div class="grid grid-cols-2 gap-3">
        <!-- Lunas Bulan Ini -->
        <div class="clay-card p-4 bg-emerald-50 border-2 border-emerald-300 space-y-1">
            <div class="flex items-center justify-between text-emerald-800">
                <span class="text-[10px] font-black uppercase">🟢 Lunas {{ $monthNames[$selectedMonth] }}</span>
                <i class="fa-solid fa-circle-check text-xs"></i>
            </div>
            <div class="text-xl font-black text-emerald-900">{{ $stats['lunas_count'] }} <span class="text-xs font-bold text-emerald-700">/ {{ $stats['total_students'] }} Siswa</span></div>
        </div>

        <!-- Belum Lunas Bulan Ini -->
        <div class="clay-card p-4 bg-rose-50 border-2 border-rose-300 space-y-1">
            <div class="flex items-center justify-between text-rose-800">
                <span class="text-[10px] font-black uppercase">🔴 Belum Lunas</span>
                <i class="fa-solid fa-clock text-xs"></i>
            </div>
            <div class="text-xl font-black text-rose-900">{{ $stats['belum_lunas_count'] }} <span class="text-xs font-bold text-rose-700">Siswa</span></div>
        </div>
    </div>

    <!-- Total Tunggakan Rombel Card -->
    <div class="clay-card p-4 bg-gradient-to-r from-purple-900 to-indigo-900 text-white border-2 border-purple-400/40 space-y-1 shadow-md">
        <div class="flex items-center justify-between text-purple-200">
            <span class="text-[10px] font-black uppercase tracking-wider">💰 Akumulasi Tunggakan Rombel {{ $classroom->name ?? '' }}</span>
            <span class="px-2 py-0.5 rounded bg-white/20 text-[9px] font-black">Bulan {{ $monthNames[$selectedMonth] }} {{ $selectedYear }}</span>
        </div>
        <div class="text-lg font-black text-amber-300">
            Rp {{ number_format($stats['total_tunggakan'], 0, ',', '.') }}
        </div>
    </div>

    <!-- Daftar Siswa & Status Pembayaran Rombel -->
    <div class="space-y-3">
        <h3 class="text-xs font-black text-slate-600 uppercase tracking-wider px-1">
            Daftar Rekap Pembayaran Siswa ({{ $stats['total_students'] }})
        </h3>

        @forelse($students as $std)
            @php
                $photo = $std->photo_url ?? null;
                if (!$photo || str_contains($photo, 'default-student.jpg') || str_contains($photo, 'default-avatar')) {
                    if (isset($std->user->avatar_url) && $std->user->avatar_url) {
                        $photo = $std->user->avatar_url;
                    } else {
                        $photo = 'https://ui-avatars.com/api/?name=' . urlencode($std->full_name) . '&background=7c3aed&color=fff&bold=true';
                    }
                }

                $mbill = $std->month_bill;
                $status = $std->status_bulan_ini;
                
                $statusBadgeClass = match($status) {
                    'lunas' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                    'cicilan' => 'bg-amber-100 text-amber-900 border-amber-300',
                    default => 'bg-rose-100 text-rose-900 border-rose-300',
                };

                $statusText = match($status) {
                    'lunas' => '🟢 LUNAS',
                    'cicilan' => '🟡 DIBAYAR SEBAGIAN',
                    default => '🔴 BELUM LUNAS',
                };

                $parentPhone = $std->parent_phone ?? ($std->phone ?? '');
                $cleanPhone = preg_replace('/[^0-9]/', '', $parentPhone);
                if (str_starts_with($cleanPhone, '0')) {
                    $cleanPhone = '62' . substr($cleanPhone, 1);
                }

                $waText = "Halo Bapak/Ibu Wali dari " . $std->full_name . ", menginformasikan rekap pembayaran SPP/Tagihan sekolah bulan " . $monthNames[$selectedMonth] . " " . $selectedYear . " saat ini statusnya: " . ($status === 'lunas' ? 'LUNAS' : 'BELUM LUNAS') . ". Terima kasih. - Wali Kelas " . ($classroom->name ?? '');
            @endphp
            <div class="clay-card p-4 space-y-3 bg-white border-2 border-slate-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <img src="{{ $photo }}" alt="{{ $std->full_name }}" 
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($std->full_name) }}&background=7c3aed&color=fff&bold=true';"
                             class="w-10 h-10 rounded-2xl object-cover border border-purple-200 shadow-xs shrink-0">
                        <div>
                            <h4 class="text-xs font-black text-slate-900 uppercase leading-snug">{{ $std->full_name }}</h4>
                            <span class="text-[10px] font-bold text-slate-400 block">NIS: {{ $std->nis ?? '-' }}</span>
                        </div>
                    </div>

                    <span class="px-2.5 py-1 rounded-xl text-[10px] font-black border {{ $statusBadgeClass }}">
                        {{ $statusText }}
                    </span>
                </div>

                <!-- Detail Tagihan Bulan Berkenaan & Tunggakan -->
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-[11px] font-bold space-y-1">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Tagihan {{ $monthNames[$selectedMonth] }}:</span>
                        <span class="font-black text-slate-900">
                            Rp {{ number_format($mbill?->amount ?? 0, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-slate-600">
                        <span>Sudah Dibayar:</span>
                        <span class="font-black text-emerald-700">
                            Rp {{ number_format($mbill?->paid_amount ?? 0, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between pt-1 border-t border-slate-200 text-rose-700 font-black">
                        <span>Total Akumulasi Tunggakan:</span>
                        <span>Rp {{ number_format($std->total_tunggakan, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Action Button: Ingatkan via WhatsApp -->
                @if($cleanPhone)
                    <div class="pt-1 flex justify-end">
                        <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode($waText) }}" target="_blank"
                           class="px-3 py-1.5 rounded-xl bg-emerald-600 text-white text-[10px] font-black shadow-sm hover:bg-emerald-700 transition flex items-center gap-1.5">
                            <i class="fa-brands fa-whatsapp text-xs"></i> Ingatkan Ortu via WA
                        </a>
                    </div>
                @endif
            </div>
        @empty
            <div class="clay-card p-8 text-center text-slate-500 text-xs font-bold space-y-2">
                <div class="text-3xl">💳</div>
                <p>Belum ada data siswa di Rombel ini.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
