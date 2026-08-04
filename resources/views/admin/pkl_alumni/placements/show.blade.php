@extends('layouts.admin')
@section('title', 'Detail Pemantauan PKL - Portal Admin')

@section('content')
<div class="space-y-6">
    {{-- Header Bar --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-indigo-900 via-indigo-800 to-violet-900 rounded-[2rem] shadow-2xl shadow-indigo-200/50 p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-48 h-48 rounded-full bg-indigo-500/20 blur-3xl"></div>
        <div class="relative z-10 flex items-center gap-4">
            <a href="{{ route('admin.pkl-alumni.placements.index') }}" class="group w-12 h-12 rounded-2xl bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 flex items-center justify-center text-indigo-100 hover:text-white transition-all duration-300 shadow-xl hover:-translate-x-1">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-xl md:text-2xl font-black text-white">Detail Pemantauan PKL</h1>
                <p class="text-xs font-medium text-indigo-100/80 mt-1">Monitoring administratif rekap aktivitas logbook dan evaluasi DUDI</p>
            </div>
        </div>
        <div class="relative z-10">
            @php
                $statusClass = match($placement->status) {
                    'active' => 'bg-gradient-to-r from-emerald-400 to-emerald-500 text-white shadow-emerald-500/40 border-emerald-400',
                    'completed' => 'bg-gradient-to-r from-blue-400 to-indigo-500 text-white shadow-blue-500/40 border-blue-400',
                    default => 'bg-gradient-to-r from-slate-400 to-slate-500 text-white shadow-slate-500/40 border-slate-400'
                };
                $statusText = match($placement->status) {
                    'active' => 'Magang Aktif',
                    'completed' => 'Selesai',
                    default => 'Batal'
                };
            @endphp
            <span class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-xs font-black shadow-lg border border-white/20 {{ $statusClass }}">
                @if($placement->status == 'active') <i class="fas fa-circle-notch fa-spin"></i> @endif
                {{ $statusText }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left column: Profile & Summary --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Student Profile Card --}}
            <div class="bg-white/80 backdrop-blur-2xl rounded-[2rem] shadow-2xl shadow-indigo-100/40 border border-indigo-50 p-6 text-center ring-1 ring-slate-900/5 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-br from-indigo-100/50 to-violet-100/50"></div>
                <div class="relative z-10">
                    <div class="w-28 h-28 rounded-[2rem] overflow-hidden bg-white border-4 border-white shadow-xl shadow-indigo-200/50 mx-auto mb-5 rotate-3 hover:rotate-0 transition-transform duration-500">
                        <img src="{{ $placement->student->photo_url }}" class="w-full h-full object-cover" alt="{{ $placement->student->full_name }}">
                    </div>
                    <h3 class="font-black text-slate-800 text-xl leading-tight">{{ $placement->student->full_name }}</h3>
                    <div class="mt-2 flex flex-col items-center gap-1.5">
                        <span class="text-xs font-bold text-slate-400 bg-slate-100 px-3 py-1 rounded-lg">NISN: {{ $placement->student->nisn }}</span>
                        <span class="text-xs font-extrabold text-indigo-700 bg-indigo-50 border border-indigo-100 px-3 py-1 rounded-lg inline-flex items-center gap-1.5 shadow-sm"><i class="fas fa-school text-indigo-400"></i> {{ $placement->student->school->name ?? '' }}</span>
                    </div>
                </div>
            </div>

            {{-- Placement Info Card --}}
            <div class="bg-white/80 backdrop-blur-2xl rounded-[2rem] shadow-2xl shadow-indigo-100/40 border border-indigo-50 p-6 ring-1 ring-slate-900/5">
                <h3 class="text-sm font-black text-indigo-900 uppercase tracking-widest border-b border-indigo-100/50 pb-4 mb-4 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-500 flex items-center justify-center"><i class="fas fa-building text-sm"></i></div>
                    Informasi DUDI
                </h3>
                <div class="space-y-4 text-sm text-slate-700">
                    <div class="bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                        <span class="text-[10px] font-black text-indigo-400 uppercase tracking-widest block mb-1">Instansi / Perusahaan</span>
                        <span class="font-black text-slate-800 text-base">{{ $placement->company_name }}</span>
                        <div class="mt-2 text-xs font-semibold text-slate-500 flex items-start gap-2">
                            <i class="fas fa-map-marker-alt text-rose-400 mt-0.5"></i>
                            <span class="leading-relaxed">{{ $placement->company_address }}</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-indigo-50/50 p-3 rounded-2xl border border-indigo-50/50">
                            <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest block mb-1">Mentor DUDI</span>
                            <span class="font-extrabold text-slate-800 text-xs">{{ $placement->mentor_name }}</span>
                            @if($placement->mentor_phone)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $placement->mentor_phone) }}" target="_blank" class="mt-1 inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 hover:text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">
                                    <i class="fab fa-whatsapp"></i> {{ $placement->mentor_phone }}
                                </a>
                            @endif
                        </div>
                        <div class="bg-emerald-50/50 p-3 rounded-2xl border border-emerald-50/50">
                            <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest block mb-1">Guru Pembimbing</span>
                            <span class="font-extrabold text-slate-800 text-xs">{{ $placement->teacher->user->name ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="bg-slate-50/50 p-3 rounded-2xl border border-slate-100 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white shadow-sm border border-slate-100 flex items-center justify-center text-indigo-400 shrink-0"><i class="far fa-calendar-alt"></i></div>
                        <div>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Durasi PKL</span>
                            <span class="font-bold text-slate-700 text-xs">{{ $placement->start_date->format('d M Y') }} <span class="text-slate-300 mx-1">→</span> {{ $placement->end_date->format('d M Y') }}</span>
                        </div>
                    </div>
                    
                    <div class="pt-2">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Tautan Portal Mentor</span>
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1 group">
                                <input type="text" readonly value="{{ route('mentor.pkl.portal', $placement->signed_token) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-[10px] font-mono text-slate-500 truncate focus:outline-none focus:border-indigo-300 transition-colors cursor-text selection:bg-indigo-100">
                                <div class="absolute inset-y-0 right-0 w-8 bg-gradient-to-l from-slate-50 to-transparent pointer-events-none rounded-r-xl"></div>
                            </div>
                            <button onclick="copyToClipboard('{{ route('mentor.pkl.portal', $placement->signed_token) }}', this)" class="shrink-0 group relative w-9 h-9 rounded-xl bg-white border border-slate-200 hover:border-indigo-300 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 shadow-sm transition-all duration-300 flex items-center justify-center hover:-translate-y-0.5" title="Salin Link">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Grades --}}
            @if($placement->grade)
                <div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 rounded-[2rem] shadow-2xl shadow-indigo-900/40 p-6 ring-1 ring-white/10">
                    <div class="absolute top-0 right-0 -mr-10 -mt-10 w-32 h-32 rounded-full bg-amber-500/10 blur-2xl pointer-events-none"></div>
                    
                    <h3 class="text-sm font-black text-white/90 uppercase tracking-widest border-b border-white/10 pb-4 mb-5 flex items-center gap-3 relative z-10">
                        <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center border border-amber-500/20"><i class="fas fa-star text-sm"></i></div>
                        Nilai DUDI
                    </h3>
                    
                    <div class="flex items-end justify-between mb-6 relative z-10">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Rata-rata</span>
                            <div class="text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-amber-200 to-amber-500 drop-shadow-lg">{{ number_format($placement->grade->score_average, 1) }}</div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-bold text-slate-400 bg-white/5 px-3 py-1.5 rounded-lg border border-white/10 flex items-center gap-1.5">
                                <i class="fas fa-check-circle text-emerald-400"></i>
                                {{ \Carbon\Carbon::parse($placement->grade->submitted_at)->translatedFormat('d M Y') }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2.5 text-xs font-bold relative z-10">
                        <div class="flex items-center justify-between p-3 rounded-xl bg-white/5 border border-white/5 hover:bg-white/10 transition-colors">
                            <span class="text-slate-300 flex items-center gap-2"><i class="fas fa-user-clock text-indigo-400/70 w-4"></i> Kedisiplinan</span>
                            <span class="text-emerald-400">{{ $placement->grade->score_discipline }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-white/5 border border-white/5 hover:bg-white/10 transition-colors">
                            <span class="text-slate-300 flex items-center gap-2"><i class="fas fa-users text-rose-400/70 w-4"></i> Kerjasama Tim</span>
                            <span class="text-emerald-400">{{ $placement->grade->score_teamwork }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-white/5 border border-white/5 hover:bg-white/10 transition-colors">
                            <span class="text-slate-300 flex items-center gap-2"><i class="fas fa-laptop-code text-sky-400/70 w-4"></i> Kemampuan Teknis</span>
                            <span class="text-emerald-400">{{ $placement->grade->score_technical }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-white/5 border border-white/5 hover:bg-white/10 transition-colors">
                            <span class="text-slate-300 flex items-center gap-2"><i class="fas fa-hard-hat text-amber-400/70 w-4"></i> Keselamatan Kerja</span>
                            <span class="text-emerald-400">{{ $placement->grade->score_safety }}</span>
                        </div>
                    </div>

                    @if($placement->grade->notes)
                        <div class="mt-5 p-4 bg-indigo-900/30 rounded-2xl border border-indigo-500/20 text-xs relative z-10 shadow-inner">
                            <span class="text-[9px] font-black text-indigo-300 uppercase tracking-widest block mb-2 flex items-center gap-1.5"><i class="fas fa-quote-left"></i> Catatan Industri:</span>
                            <p class="text-indigo-100/90 italic leading-relaxed">"{{ $placement->grade->notes }}"</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Right column: Timeline --}}
        <div class="lg:col-span-2 bg-white/80 backdrop-blur-2xl rounded-[2rem] shadow-2xl shadow-indigo-100/40 border border-indigo-50 p-6 md:p-8 ring-1 ring-slate-900/5 min-h-[500px]">
            <div class="flex items-center justify-between border-b border-indigo-100/50 pb-5 mb-8">
                <h3 class="text-lg font-black text-indigo-900 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-sm"><i class="fas fa-history"></i></div>
                    Laporan Aktivitas Harian
                </h3>
                <span class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-xl text-xs font-bold border border-indigo-100">{{ $placement->logs->count() }} Laporan</span>
            </div>

            <div class="relative pl-4 md:pl-8 space-y-8 before:absolute before:left-[21px] md:before:left-[37px] before:top-2 before:bottom-2 before:w-[2px] before:bg-gradient-to-b before:from-indigo-200 before:via-indigo-100 before:to-transparent">
                @forelse($placement->logs as $log)
                    @php
                        $statusData = match($log->status) {
                            'approved' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500 shadow-emerald-400/50', 'label' => 'Disetujui', 'icon' => 'fa-check'],
                            'rejected' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'border' => 'border-rose-200', 'dot' => 'bg-rose-500 shadow-rose-400/50', 'label' => 'Revisi', 'icon' => 'fa-times'],
                            default => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-200', 'dot' => 'bg-amber-400 shadow-amber-400/50', 'label' => 'Pending', 'icon' => 'fa-clock']
                        };
                    @endphp
                    <div class="relative group">
                        {{-- Timeline Bullet (Glowing) --}}
                        <div class="absolute -left-[27.5px] md:-left-[27.5px] top-2 z-10 w-4 h-4 rounded-full border-4 border-white shadow-lg {{ $statusData['dot'] }} transition-transform duration-300 group-hover:scale-125"></div>

                        {{-- Log Card --}}
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-indigo-100/50 transition-all duration-300 overflow-hidden group-hover:-translate-y-0.5">
                            {{-- Card Header --}}
                            <div class="bg-slate-50/50 px-5 py-3.5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-500 flex items-center justify-center text-xs shadow-sm font-bold">
                                        {{ \Carbon\Carbon::parse($log->log_date)->format('d') }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-slate-700">
                                            {{ \Carbon\Carbon::parse($log->log_date)->translatedFormat('l, d M Y') }}
                                        </p>
                                        @if($log->latitude && $log->longitude)
                                            <a href="https://www.google.com/maps/search/?api=1&query={{ $log->latitude }},{{ $log->longitude }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-sky-500 hover:text-sky-700 hover:underline mt-0.5 transition-colors">
                                                <i class="fas fa-map-marked-alt"></i> Buka Peta GPS
                                            </a>
                                        @else
                                            <span class="text-[10px] font-semibold text-slate-400 inline-flex items-center gap-1 mt-0.5"><i class="fas fa-map-marker-alt opacity-50"></i> GPS tidak terekam</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider border shadow-sm {{ $statusData['bg'] }} {{ $statusData['text'] }} {{ $statusData['border'] }}">
                                    <i class="fas {{ $statusData['icon'] }}"></i> {{ $statusData['label'] }}
                                </span>
                            </div>

                            {{-- Card Body --}}
                            <div class="p-5">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                    <div class="md:col-span-3">
                                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Deskripsi Kegiatan</h4>
                                        <div class="text-sm font-medium text-slate-700 leading-relaxed whitespace-pre-line bg-slate-50/50 rounded-xl p-4 border border-slate-100">
                                            {{ $log->activity }}
                                        </div>
                                    </div>
                                    @if($log->photo)
                                        <div class="md:col-span-1">
                                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Lampiran</h4>
                                            <a href="{{ asset('storage/' . $log->photo) }}" target="_blank" class="block rounded-xl overflow-hidden border-2 border-slate-100 shadow-md hover:shadow-xl hover:border-indigo-300 transition-all duration-300 group/img relative">
                                                <img src="{{ asset('storage/' . $log->photo) }}" class="w-full h-32 md:h-full min-h-[120px] object-cover transition-transform duration-500 group-hover/img:scale-110" alt="Bukti Foto">
                                                <div class="absolute inset-0 bg-indigo-900/0 group-hover/img:bg-indigo-900/20 transition-colors flex items-center justify-center">
                                                    <i class="fas fa-search-plus text-white opacity-0 group-hover/img:opacity-100 text-xl transform scale-50 group-hover/img:scale-100 transition-all duration-300"></i>
                                                </div>
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                @if($log->status === 'rejected' && $log->mentor_notes)
                                    <div class="mt-5 p-4 bg-rose-50/80 border-l-4 border-rose-500 rounded-r-xl rounded-l-sm text-sm text-rose-900 shadow-sm">
                                        <span class="font-black flex items-center gap-2 mb-1 text-rose-700 text-xs uppercase tracking-wider"><i class="fas fa-exclamation-triangle"></i> Catatan Revisi Mentor:</span>
                                        <p class="font-medium italic leading-relaxed">"{{ $log->mentor_notes }}"</p>
                                    </div>
                                @endif

                                @if($log->status === 'submitted')
                                    <div class="mt-5 border-t border-slate-100 pt-4 flex justify-end">
                                        <form action="{{ route('admin.pkl-alumni.placements.log.approve', [$placement->id, $log->id]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold px-4 py-2 rounded-xl text-xs transition flex items-center gap-2 border border-indigo-200">
                                                <i class="fas fa-check-circle"></i> Ambil Alih Persetujuan
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200">
                        <div class="w-20 h-20 bg-white rounded-full shadow-sm flex items-center justify-center mx-auto mb-4 border border-slate-100">
                            <i class="fas fa-clipboard-list text-3xl text-slate-300"></i>
                        </div>
                        <h4 class="text-lg font-black text-slate-700">Belum Ada Logbook</h4>
                        <p class="text-sm font-medium text-slate-500 mt-1 max-w-sm mx-auto">Siswa ini belum mengirimkan laporan harian apapun ke sistem.</p>
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
@endsection
