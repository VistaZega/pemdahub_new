@extends('layouts.guru')

@section('title', 'Dispensasi Ujian CBT (Wali Kelas)')

@section('content')
<div class="space-y-6" x-data="dispensationManager()">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-amber-600 via-orange-600 to-rose-600 rounded-3xl p-6 lg:p-8 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-xs font-bold backdrop-blur-xs">
                    <i class="fas fa-shield-halved"></i>
                    <span>Hak Akses Ujian Khusus</span>
                </div>
                <h1 class="text-2xl lg:text-3xl font-black tracking-tight">Otorisasi Dispensasi Ujian Siswa</h1>
                <p class="text-amber-100 text-sm max-w-2xl">
                    Berikan otorisasi bagi siswa bimbingan kelas Anda yang belum menyelesaikan uang sekolah agar dapat mengikuti ujian CBT berdasarkan kesepakatan penyelesaian.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('guru.tagihan-siswa') }}" class="px-4 py-2.5 rounded-xl bg-white text-slate-800 text-xs font-black shadow hover:bg-amber-50 transition flex items-center gap-2">
                    <i class="fas fa-file-invoice-dollar text-amber-600"></i>
                    <span>Cek Biaya Pendidikan</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Filter Selector --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('guru.walikelas.cbt-dispensasi.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            @if($homeroomClassrooms->count() > 1 || $isAdmin)
            <div class="md:col-span-4">
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Pilih Kelas Bimbingan</label>
                <select name="classroom_id" onchange="this.form.submit()" class="w-full rounded-xl border-slate-200 bg-slate-50 text-slate-800 font-bold text-sm py-2.5 px-3 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    @foreach($homeroomClassrooms as $cls)
                        <option value="{{ $cls->id }}" {{ $selectedClassroom?->id == $cls->id ? 'selected' : '' }}>
                            Kelas {{ $cls->class_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @else
                <input type="hidden" name="classroom_id" value="{{ $selectedClassroom?->id }}">
            @endif

            <div class="{{ ($homeroomClassrooms->count() > 1 || $isAdmin) ? 'md:col-span-8' : 'md:col-span-12' }}">
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Pilih Ujian CBT (Mensyaratkan Uang Sekolah)</label>
                <select name="exam_id" onchange="this.form.submit()" class="w-full rounded-xl border-slate-200 bg-slate-50 text-slate-800 font-bold text-sm py-2.5 px-3 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    @forelse($exams as $ex)
                        @php $p = $ex->getTargetTuitionPeriod(); @endphp
                        <option value="{{ $ex->id }}" {{ $selectedExam?->id == $ex->id ? 'selected' : '' }}>
                            {{ $ex->exam_title }} &bull; {{ $ex->subject?->name ?? 'Mapel' }} &bull; (Syarat SPP: {{ $p['label'] }})
                        </option>
                    @empty
                        <option value="">-- Belum ada ujian CBT yang mensyaratkan uang sekolah untuk kelas ini --</option>
                    @endforelse
                </select>
            </div>
        </form>
    </div>

    @if(!$selectedExam)
        <div class="bg-white rounded-3xl p-12 border border-slate-200 text-center space-y-3">
            <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center mx-auto text-2xl">
                <i class="fas fa-calendar-check"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800">Tidak Ada Ujian yang Mensyaratkan Uang Sekolah</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto">
                Saat ini belum ada jadwal ujian CBT aktif untuk kelas ini yang mengaktifkan opsi kepatuhan uang sekolah. Semua siswa dapat mengakses ujian normal.
            </p>
        </div>
    @elseif($overview)
        @php $period = $selectedExam->getTargetTuitionPeriod(); @endphp

        {{-- Highlight Banner Ujian Terpilih --}}
        <div class="bg-amber-50 border-2 border-amber-200 rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg font-black shrink-0">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900">{{ $selectedExam->exam_title }}</h3>
                    <p class="text-xs text-slate-600">
                        {{ $selectedExam->subject?->name ?? 'Mata Pelajaran' }} &bull; Durasi: {{ $selectedExam->duration_minutes }} Menit &bull; 
                        Syarat: Pelunasan SPP <strong>{{ $period['label'] }}</strong>
                    </p>
                </div>
            </div>
            <div class="text-xs text-amber-800 bg-white/80 px-3 py-1.5 rounded-xl border border-amber-300 font-bold shrink-0">
                <i class="fas fa-info-circle mr-1 text-amber-600"></i>
                Kelas: <strong>{{ $selectedClassroom?->class_name }}</strong>
            </div>
        </div>

        {{-- Statistik Kepatuhan Kelas --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-4.5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500">Total Siswa Rombel</span>
                    <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center text-xs">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="text-2xl font-black text-slate-900 mt-2">{{ $overview['total_students'] }}</div>
                <span class="text-[11px] text-slate-500">Terdaftar di rombel</span>
            </div>

            <div class="bg-white p-4.5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-600">Sudah Lunas SPP</span>
                    <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="text-2xl font-black text-emerald-700 mt-2">{{ $overview['paid_count'] }}</div>
                <span class="text-[11px] text-emerald-600 font-semibold">Otomatis diizinkan</span>
            </div>

            <div class="bg-white p-4.5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-blue-600">Dispensasi Aktif</span>
                    <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs">
                        <i class="fas fa-certificate"></i>
                    </div>
                </div>
                <div class="text-2xl font-black text-blue-700 mt-2">{{ $overview['dispensation_count'] }}</div>
                <span class="text-[11px] text-blue-600 font-semibold">Disetujui Wali Kelas</span>
            </div>

            <div class="bg-white p-4.5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-rose-600">Akses Diblokir</span>
                    <div class="w-7 h-7 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center text-xs">
                        <i class="fas fa-lock"></i>
                    </div>
                </div>
                <div class="text-2xl font-black text-rose-700 mt-2">{{ $overview['blocked_count'] }}</div>
                <span class="text-[11px] text-rose-600 font-semibold">Belum bayar & tanpa dispensasi</span>
            </div>
        </div>

        {{-- Tabel Siswa & Aksi Dispensasi --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                <div>
                    <h3 class="text-base font-black text-slate-900">Roster Siswa & Status Izin Masuk Ujian</h3>
                    <p class="text-xs text-slate-500">Klik 'Beri Dispensasi' untuk mengizinkan siswa yang telah bersepakat mengenai penyelesaian SPP</p>
                </div>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
                    <span>Filter Cepat:</span>
                    <button type="button" @click="filterStatus = 'all'" :class="filterStatus === 'all' ? 'bg-slate-800 text-white' : 'bg-white text-slate-700 border border-slate-200'" class="px-2.5 py-1 rounded-lg transition">Semua</button>
                    <button type="button" @click="filterStatus = 'blocked'" :class="filterStatus === 'blocked' ? 'bg-rose-600 text-white' : 'bg-white text-rose-700 border border-slate-200'" class="px-2.5 py-1 rounded-lg transition">Diblokir ({{ $overview['blocked_count'] }})</button>
                    <button type="button" @click="filterStatus = 'dispensation'" :class="filterStatus === 'dispensation' ? 'bg-blue-600 text-white' : 'bg-white text-blue-700 border border-slate-200'" class="px-2.5 py-1 rounded-lg transition">Dispensasi ({{ $overview['dispensation_count'] }})</button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] font-black tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-4 w-12 text-center">No</th>
                            <th class="py-3.5 px-4">Nama Siswa / NIS</th>
                            <th class="py-3.5 px-4">Status SPP ({{ $period['label'] }})</th>
                            <th class="py-3.5 px-4">Status Dispensasi</th>
                            <th class="py-3.5 px-4 text-center">Akses Ujian</th>
                            <th class="py-3.5 px-4 text-right">Aksi Wali Kelas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($overview['students'] as $idx => $item)
                            @php
                                $st = $item['student'];
                                $cp = $item['compliance'];
                                $rowStatus = $cp['has_dispensation'] ? 'dispensation' : ($cp['allowed'] ? 'paid' : 'blocked');
                            @endphp
                            <tr x-show="filterStatus === 'all' || filterStatus === '{{ $rowStatus }}'" class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 text-center text-xs text-slate-400 font-bold">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-amber-100 text-amber-800 font-black flex items-center justify-center text-xs shrink-0">
                                            {{ strtoupper(substr($st->name ?? 'S', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900">{{ $st->name }}</div>
                                            <div class="text-[11px] text-slate-500 font-mono">NIS: {{ $st->nis ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($cp['is_paid'])
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <i class="fas fa-check-circle text-[10px]"></i> Lunas
                                        </span>
                                    @else
                                        <div>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-800 border border-rose-200">
                                                <i class="fas fa-exclamation-circle text-[10px]"></i> Belum Lunas
                                            </span>
                                            @if($cp['unpaid_amount'] > 0)
                                                <div class="text-[11px] text-rose-700 font-bold mt-1">
                                                    Sisa: Rp {{ number_format($cp['unpaid_amount'], 0, ',', '.') }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($cp['has_dispensation'])
                                        <div class="space-y-1">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-black bg-blue-100 text-blue-800 border border-blue-200">
                                                <i class="fas fa-certificate text-[10px]"></i> Otorisasi Aktif
                                            </span>
                                            @if($cp['dispensation']?->reason)
                                                <div class="text-[11px] text-slate-600 italic bg-blue-50/50 p-2 rounded-lg border border-blue-100 max-w-xs">
                                                    "{{ $cp['dispensation']->reason }}"
                                                </div>
                                            @endif
                                            <div class="text-[10px] text-slate-400">
                                                Oleh: {{ $cp['dispensation']?->granter?->name ?? 'Wali Kelas' }} &bull; {{ $cp['dispensation']?->granted_at?->format('d/m/Y H:i') }}
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Tidak ada</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($cp['allowed'])
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-black bg-emerald-500 text-white shadow-xs">
                                            <i class="fas fa-check text-[10px]"></i> Diizinkan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-black bg-rose-500 text-white shadow-xs">
                                            <i class="fas fa-lock text-[10px]"></i> Diblokir
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if(!$cp['has_dispensation'])
                                            <button type="button"
                                                @click="openGrantModal({{ $st->id }}, '{{ addslashes($st->name) }}', '{{ $st->nis ?? '-' }}')"
                                                class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5">
                                                <i class="fas fa-key text-[10px]"></i>
                                                <span>Beri Dispensasi</span>
                                            </button>
                                        @else
                                            <form action="{{ route('guru.walikelas.cbt-dispensasi.revoke', ['exam' => $selectedExam->id, 'student' => $st->id]) }}" method="POST" onsubmit="return confirm('Cabut dispensasi ujian untuk {{ addslashes($st->name) }}?')">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-700 hover:text-rose-700 border border-slate-200 text-xs font-bold transition flex items-center gap-1.5">
                                                    <i class="fas fa-ban text-[10px]"></i>
                                                    <span>Cabut</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                    Tidak ada data siswa pada kelas ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Modal Beri Dispensasi --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="showModal = false" class="bg-white rounded-3xl p-6 lg:p-8 max-w-lg w-full shadow-2xl space-y-5 border border-slate-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center font-black">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">Otorisasi Dispensasi Ujian</h3>
                        <p class="text-xs text-slate-500">Pemberian hak akses pengerjaan CBT</p>
                    </div>
                </div>
                <button type="button" @click="showModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>

            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 text-xs space-y-1">
                <div class="text-slate-500">Nama Siswa:</div>
                <div class="font-black text-slate-900 text-sm" x-text="targetStudentName"></div>
                <div class="text-slate-500">NIS: <span class="font-mono text-slate-800" x-text="targetStudentNis"></span></div>
            </div>

            <form :action="modalActionUrl" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                        Catatan Kesepakatan / Alasan Dispensasi
                    </label>
                    <textarea name="reason" rows="3" required
                        placeholder="Contoh: Orang tua siswa telah berkoordinasi dan berjanji menyelesaikan sisa uang sekolah pada tanggal 25..."
                        class="w-full rounded-2xl border-slate-200 bg-slate-50 p-3 text-xs text-slate-800 font-medium focus:ring-2 focus:ring-amber-500 focus:border-amber-500"></textarea>
                    <p class="text-[11px] text-slate-500 mt-1">
                        Catatan ini akan disimpan secara transparan sebagai bukti kesepakatan antara Wali Kelas dan orang tua/siswa.
                    </p>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-black text-xs shadow-md">
                        <i class="fas fa-check mr-1.5"></i> Setujui Dispensasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function dispensationManager() {
    return {
        filterStatus: 'all',
        showModal: false,
        targetStudentId: null,
        targetStudentName: '',
        targetStudentNis: '',
        examId: '{{ $selectedExam?->id }}',

        get modalActionUrl() {
            return `{{ url('guru/walikelas/cbt-dispensasi/exams') }}/${this.examId}/students/${this.targetStudentId}/grant`;
        },

        openGrantModal(id, name, nis) {
            this.targetStudentId = id;
            this.targetStudentName = name;
            this.targetStudentNis = nis;
            this.showModal = true;
        }
    }
}
</script>
@endpush
