@extends('layouts.guru')
@section('title', 'Detail Monitoring PKL - Portal Guru')

@section('content')
<div class="space-y-6">
    {{-- Header Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white rounded-xl shadow-sm border border-gray-100 px-5 py-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('guru.pkl.index') }}" class="w-8 h-8 rounded-lg bg-gray-50 border border-gray-100 hover:bg-gray-100 flex items-center justify-center text-gray-500 transition">
                <i class="fas fa-arrow-left text-xs"></i>
            </a>
            <div>
                <h1 class="text-lg md:text-xl font-bold text-gray-800">Detail Monitoring PKL Siswa</h1>
                <p class="text-xs text-gray-500 mt-0.5">
                    Memantau kegiatan harian dan evaluasi industri siswa bersangkutan
                </p>
            </div>
        </div>
        <div>
            <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-lg text-xs font-semibold">
                Status: {{ $placement->status === 'active' ? 'Magang Aktif' : 'Selesai' }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column: Student & Placement Summary --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Student Profile card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 text-center">
                <div class="w-20 h-20 rounded-2xl overflow-hidden bg-gray-50 border border-gray-200 shadow-md mx-auto mb-4">
                    <img src="{{ $placement->student->photo_url }}" class="w-full h-full object-cover" alt="{{ $placement->student->full_name }}">
                </div>
                <h3 class="font-bold text-gray-800 text-base leading-tight">{{ $placement->student->full_name }}</h3>
                <p class="text-xs text-gray-400 mt-0.5">NISN: {{ $placement->student->nisn }}</p>
                
                <div class="border-t border-gray-50 mt-4 pt-4 grid grid-cols-2 gap-2 text-left text-xs">
                    <div>
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block">Jurusan</span>
                        <span class="font-semibold text-gray-700">{{ $placement->student->major->name ?? 'SMK Swasta Pembda' }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block">Angkatan</span>
                        <span class="font-semibold text-gray-700">{{ $placement->student->entry_year }}</span>
                    </div>
                </div>
            </div>

            {{-- Placement Info --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-xs font-bold text-gray-850 uppercase tracking-wider border-b border-gray-150 pb-3 mb-3 flex items-center gap-2">
                    <i class="fas fa-building text-emerald-500"></i> Instansi & Mitra DUDI
                </h3>
                <div class="space-y-3.5 text-xs text-gray-700">
                    <div>
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block">Nama Instansi</span>
                        <span class="font-bold text-gray-800 leading-snug">{{ $placement->company_name }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block">Alamat Instansi</span>
                        <span class="text-gray-650 leading-relaxed"><i class="fas fa-map-marker-alt text-rose-500 mr-1"></i>{{ $placement->company_address }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4 border-t border-gray-50 pt-2.5">
                        <div>
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block">Mentor Lapangan</span>
                            <span class="font-semibold text-gray-850">{{ $placement->mentor_name }}</span>
                            @if($placement->mentor_phone)
                                <span class="block text-gray-400 mt-0.5"><i class="fab fa-whatsapp text-emerald-500 mr-1"></i>{{ $placement->mentor_phone }}</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block">Periode</span>
                            <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($placement->start_date)->format('d/m/y') }} – {{ \Carbon\Carbon::parse($placement->end_date)->format('d/m/y') }}</span>
                        </div>
                    </div>
                    <div class="border-t border-gray-50 pt-3">
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Tautan Portal Evaluasi Mentor DUDI</span>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <input type="text" readonly id="mentor-link-field" value="{{ route('mentor.pkl.portal', $placement->signed_token) }}" class="bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-1.5 text-[10px] font-mono text-gray-650 flex-1 truncate focus:outline-none">
                            <button onclick="copyToClipboard('{{ route('mentor.pkl.portal', $placement->signed_token) }}', this)" class="bg-gray-55 hover:bg-indigo-50 text-gray-500 hover:text-indigo-600 border border-gray-200 hover:border-indigo-150 p-2 rounded-xl transition shadow-sm" title="Salin Tautan Portal Mentor">
                                <i class="fas fa-link text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Evaluation Grade --}}
            @if($placement->grade)
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl shadow-lg border border-slate-700 p-5">
                    <h3 class="text-xs font-bold border-b border-white/10 pb-3 mb-4 flex items-center gap-2">
                        <i class="fas fa-star text-amber-400"></i> Nilai Penilaian Mentor DUDI
                    </h3>
                    
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-4xl font-black text-white">{{ number_format($placement->grade->score_average, 1) }}</div>
                        <div class="text-right text-xs">
                            <span class="text-slate-400 uppercase tracking-wider block text-[9px]">Selesai Dikirim</span>
                            <span class="font-semibold text-slate-200">{{ \Carbon\Carbon::parse($placement->grade->submitted_at)->translatedFormat('d M Y') }}</span>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between p-2 rounded bg-white/5">
                            <span class="text-slate-300">Kedisiplinan</span>
                            <span class="font-bold text-emerald-450">{{ $placement->grade->score_discipline }}</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded bg-white/5">
                            <span class="text-slate-300">Kerjasama Tim</span>
                            <span class="font-bold text-emerald-450">{{ $placement->grade->score_teamwork }}</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded bg-white/5">
                            <span class="text-slate-300">Kemampuan Teknis</span>
                            <span class="font-bold text-emerald-450">{{ $placement->grade->score_technical }}</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded bg-white/5">
                            <span class="text-slate-300">Keselamatan Kerja</span>
                            <span class="font-bold text-emerald-450">{{ $placement->grade->score_safety }}</span>
                        </div>
                    </div>

                    @if($placement->grade->notes)
                        <div class="mt-4 p-3 bg-white/5 rounded-xl border border-white/5 text-xs">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Catatan Mentor:</span>
                            <p class="text-slate-200 italic">"{{ $placement->grade->notes }}"</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Right Column: Logs Timeline --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-bold text-gray-850 border-b border-gray-100 pb-3 mb-4 flex items-center gap-2">
                <i class="fas fa-history text-indigo-500"></i> Timeline Logbook Siswa
            </h3>

            <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-100">
                @forelse($placement->logs as $log)
                    @php
                        $statusClass = match($log->status) {
                            'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
                            default => 'bg-amber-50 text-amber-700 border-amber-200'
                        };
                        $statusText = match($log->status) {
                            'approved' => 'Disetujui',
                            'rejected' => 'Revisi',
                            default => 'Pending'
                        };
                    @endphp
                    <div class="relative">
                        {{-- Timeline Bullet --}}
                        <div class="absolute -left-[22px] top-1.5 z-10 w-3 h-3 rounded-full border-2 border-white {{ $log->status === 'approved' ? 'bg-emerald-500 shadow-[0_0_0_3px_rgba(16,185,129,0.2)]' : ($log->status === 'rejected' ? 'bg-rose-500 shadow-[0_0_0_3px_rgba(239,68,68,0.2)]' : 'bg-amber-500 shadow-[0_0_0_3px_rgba(245,158,11,0.2)]') }}"></div>

                        {{-- Log Card --}}
                        <div class="border border-gray-100 hover:border-gray-200 rounded-xl p-4 transition hover:bg-gray-50/20">
                            <div class="flex flex-col md:flex-row md:items-start justify-between gap-3 mb-2.5">
                                <div>
                                    <p class="text-xs font-bold text-gray-850">
                                        {{ \Carbon\Carbon::parse($log->log_date)->translatedFormat('l, d F Y') }}
                                    </p>
                                    @if($log->latitude && $log->longitude)
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $log->latitude }},{{ $log->longitude }}" target="_blank" class="inline-flex items-center text-[10px] text-blue-600 hover:underline mt-1">
                                            <i class="fas fa-map-marked-alt mr-1"></i> Lokasi GPS ({{ number_format($log->latitude, 6) }}, {{ number_format($log->longitude, 6) }})
                                        </a>
                                    @else
                                        <span class="text-[10px] text-gray-400 italic mt-1 block">GPS tidak terekam</span>
                                    @endif
                                </div>
                                <div>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[10px] font-bold border {{ $statusClass }}">
                                        {{ $statusText }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="md:col-span-3 text-xs text-gray-750 whitespace-pre-line leading-relaxed">
                                    {{ $log->activity }}
                                </div>
                                @if($log->photo)
                                    <div class="md:col-span-1">
                                        <button type="button" @click="$dispatch('open-preview-modal', { url: '{{ asset('storage/' . $log->photo) }}', name: 'Bukti Kegiatan Logbook PKL ({{ $log->log_date->format('d/m/Y') }})' })" class="block rounded-xl overflow-hidden border-2 border-emerald-400 hover:border-emerald-600 shadow-md hover:shadow-lg max-h-[100px] group relative w-full transition transform active:scale-95">
                                            <img src="{{ asset('storage/' . $log->photo) }}" class="w-full h-[100px] object-cover group-hover:scale-105 transition duration-300" alt="Bukti Foto">
                                            <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-[11px] font-extrabold gap-1">
                                                <i class="fas fa-search-plus"></i> Lihat di Layar
                                            </div>
                                        </button>
                                        <a href="{{ asset('storage/' . $log->photo) }}" target="_blank" download class="mt-1.5 block text-center text-[10px] text-gray-500 hover:text-emerald-700 font-bold hover:underline">
                                            <i class="fas fa-download mr-1"></i> Unduh File Asli
                                        </a>
                                    </div>
                                @endif
                            </div>

                            @if($log->status === 'rejected' && $log->mentor_notes)
                                <div class="mt-3 p-2.5 bg-rose-50/50 border border-rose-100 rounded-xl text-xs text-rose-800">
                                    <span class="font-bold flex items-center gap-1 mb-0.5"><i class="fas fa-exclamation-circle"></i> Catatan Mentor (Alasan Penolakan):</span>
                                    <p class="italic">"{{ $log->mentor_notes }}"</p>
                                </div>
                            @endif

                            @if($log->status === 'submitted')
                                <div class="mt-4 border-t border-gray-100 pt-3 flex justify-end">
                                    <form action="{{ route('guru.pkl.log.approve', [$placement->id, $log->id]) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold px-4 py-2 rounded-xl text-xs transition flex items-center gap-1 border border-indigo-200">
                                            <i class="fas fa-check-circle"></i> Ambil Alih Persetujuan
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-gray-450 italic text-xs">
                        Belum ada entri logbook harian yang dikirim oleh siswa.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    function copyToClipboard(text, button) {
        navigator.clipboard.writeText(text).then(function() {
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check text-emerald-500 text-[10px]"></i>';
            button.classList.add('bg-emerald-50', 'border-emerald-250');
            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.classList.remove('bg-emerald-50', 'border-emerald-250');
            }, 2000);
        }, function(err) {
            console.error('Failed to copy: ', err);
        });
    }
</script>

@include('components.preview-modal')
@endsection
