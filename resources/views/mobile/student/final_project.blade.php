@extends('mobile.layouts.app')

@php
    $pageTitle = ($schoolType === 'SMK') ? 'Project Akhir Siswa' : 'Penelitian Akhir Siswa';
    $moduleBadge = ($schoolType === 'SMK') ? 'Project Akhir SMK' : 'Penelitian Akhir SMA';
@endphp

@section('title', $pageTitle . ' - PembdaHUB Mobile Pro')

@section('content')
<div class="space-y-4 pt-1">
    <!-- Top Nav Header -->
    <div class="flex items-center justify-between px-1">
        <a href="{{ route('mobile.dashboard') }}" class="w-9 h-9 rounded-2xl bg-white border-2 border-slate-200 shadow-sm flex items-center justify-center text-slate-700 hover:bg-slate-50 transition active:scale-95">
            <i class="fa-solid fa-arrow-left text-xs"></i>
        </a>
        <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">{{ $pageTitle }}</h2>
        <div class="w-9"></div>
    </div>

    @if($project)
        <!-- Project Hero Card (Clay Purple) -->
        <div class="clay-purple p-5 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-wider bg-white/30 px-2.5 py-0.5 rounded-full border border-white/40 shadow-xs">
                    {{ $moduleBadge }}
                </span>
                @php
                    $statusStyles = [
                        'pending' => 'bg-amber-400 text-amber-950',
                        'approved' => 'bg-emerald-400 text-emerald-950',
                        'in_progress' => 'bg-blue-400 text-blue-950',
                        'ready' => 'bg-indigo-300 text-indigo-950 font-black',
                        'scheduled' => 'bg-purple-300 text-purple-950',
                        'passed' => 'bg-emerald-300 text-emerald-950',
                        'failed' => 'bg-rose-400 text-rose-950',
                    ];
                    $statusLabels = [
                        'pending' => 'Menunggu ACC',
                        'approved' => 'Judul Disetujui',
                        'in_progress' => 'Bimbingan Berjalan',
                        'ready' => 'Siap Ujian Sidang',
                        'scheduled' => 'Ujian Terjadwal',
                        'passed' => 'LULUS SIDANG 🎉',
                        'failed' => 'Perlu Revisi',
                    ];
                @endphp
                <span class="text-[10px] font-black px-2.5 py-0.5 rounded-full border border-white/40 {{ $statusStyles[$project->status] ?? 'bg-white/30 text-white' }}">
                    {{ $statusLabels[$project->status] ?? ucfirst($project->status) }}
                </span>
            </div>

            <div>
                <h3 class="text-base font-black text-white leading-snug">
                    {{ $project->title }}
                </h3>
                @if($project->abstract)
                    <p class="text-[11px] text-purple-100 mt-1 font-medium line-clamp-3 leading-relaxed">
                        {{ $project->abstract }}
                    </p>
                @endif
            </div>

            <!-- Advisor & Examiner Info -->
            <div class="pt-2.5 border-t border-white/20 grid grid-cols-2 gap-2 text-[10px] font-bold text-purple-100">
                <div class="flex items-center gap-1.5 truncate">
                    <i class="fa-solid fa-chalkboard-user text-xs shrink-0 text-white"></i>
                    <span class="truncate">Pembimbing: <strong class="text-white">{{ $project->advisor->full_name ?? 'Belum Ditugaskan' }}</strong></span>
                </div>
                <div class="flex items-center gap-1.5 truncate">
                    <i class="fa-solid fa-graduation-cap text-xs shrink-0 text-white"></i>
                    <span class="truncate">Penguji: <strong class="text-white">{{ $project->examiner->full_name ?? 'Belum Dijadwalkan' }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Stage Progress Indicator (Clay Card) -->
        <div class="clay-card p-4 space-y-2.5">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-black text-slate-900 flex items-center gap-1.5">
                    <i class="fa-solid fa-bars-progress text-purple-600"></i> Tahapan Bimbingan
                </h4>
                <span class="text-[10px] font-extrabold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-full border border-purple-200 uppercase">
                    Tahap: {{ $stages[$project->current_stage]['label'] ?? ($stages[$project->current_stage]['name'] ?? ucfirst($project->current_stage ?? 'Tahap Awal')) }}
                </span>
            </div>

            <!-- Stepper Progress Dots -->
            @php
                $stageKeys = array_keys($stages);
                $currentIndex = array_search($project->current_stage, $stageKeys);
                if ($currentIndex === false) $currentIndex = 0;
            @endphp
            <div class="flex items-center justify-between gap-1 pt-1">
                @foreach($stages as $key => $stageInfo)
                    @php $stepIndex = array_search($key, $stageKeys); @endphp
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-7 h-7 rounded-xl flex items-center justify-center text-[10px] font-black border-2 transition
                            {{ $stepIndex <= $currentIndex ? 'bg-purple-600 text-white border-purple-700 shadow-sm' : 'bg-slate-100 text-slate-400 border-slate-200' }}">
                            @if($stepIndex < $currentIndex)
                                <i class="fa-solid fa-check text-[9px]"></i>
                            @else
                                {{ $stepIndex + 1 }}
                            @endif
                        </div>
                        <span class="text-[8px] font-extrabold text-slate-600 text-center mt-1 truncate max-w-[50px] leading-tight">
                            {{ $stageInfo['label'] ?? ($stageInfo['name'] ?? ucfirst($key)) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Team Members List (Clay Card) -->
        <div class="clay-card p-4 space-y-2">
            <h4 class="text-xs font-black text-slate-900 flex items-center gap-1.5">
                <i class="fa-solid fa-users text-blue-600"></i> Anggota Kelompok ({{ $project->members->count() }})
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach($project->members as $member)
                    <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-2xl border border-slate-200">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-7 h-7 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-black text-xs shrink-0">
                                {{ strtoupper(substr($member->student->full_name ?? 'S', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-black text-slate-800 truncate">{{ $member->student->full_name ?? '-' }}</p>
                                <p class="text-[10px] text-slate-500 font-bold">NISN: {{ $member->student->nisn ?? '-' }}</p>
                            </div>
                        </div>
                        <span class="text-[9px] font-black px-2 py-0.5 rounded-full uppercase
                            {{ $member->role === 'leader' ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-slate-200 text-slate-700' }}">
                            {{ $member->role === 'leader' ? 'Ketua' : 'Anggota' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Form Input Jurnal Bimbingan / Logbook (Clay Card) -->
        <div class="clay-card p-5 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-file-pen text-purple-600"></i> Kirim Laporan / Log Bimbingan
                </h3>
                <span class="text-[9px] font-extrabold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full border border-purple-200">Konsultasi Dosen/Guru</span>
            </div>

            <form action="{{ route('mobile.final-project.log') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label for="stage" class="block text-[11px] font-black text-slate-800 mb-1">Tahapan / Bab</label>
                        <select id="stage" name="stage" required
                                class="w-full px-3 py-2 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold focus:outline-hidden focus:border-purple-500">
                            @foreach($stages as $key => $stg)
                                <option value="{{ $key }}" {{ $project->current_stage === $key ? 'selected' : '' }}>{{ $stg['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="log_date" class="block text-[11px] font-black text-slate-800 mb-1">Tanggal Bimbingan</label>
                        <input type="date" id="log_date" name="log_date" value="{{ date('Y-m-d') }}" onclick="try { this.showPicker(); } catch(e) {}" required
                               class="w-full px-3 py-2 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold cursor-pointer focus:outline-hidden focus:border-purple-500">
                    </div>
                </div>

                <div>
                    <label for="activity" class="block text-[11px] font-black text-slate-800 mb-1">Catatan Progres / Hasil Revisi</label>
                    <textarea id="activity" name="activity" rows="3" required
                              placeholder="Tuliskan poin-poin yang sudah dikerjakan, hasil revisi bab, atau pertanyaan bimbingan untuk guru pembimbing..."
                              class="w-full p-3 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold resize-none focus:outline-hidden focus:border-purple-500"></textarea>
                </div>

                <div class="space-y-2">
                    <div>
                        <label for="file_attachment" class="block text-[11px] font-black text-slate-800 mb-1">Upload Berkas Draft / Revisi (Opsional)</label>
                        <input type="file" id="file_attachment" name="file_attachment" accept=".pdf,.doc,.docx,.zip,.rar,.jpg,.png"
                               class="w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
                    </div>

                    <div>
                        <label for="drive_link" class="block text-[11px] font-black text-slate-800 mb-1">Link Google Drive / Repository (Opsional)</label>
                        <input type="url" id="drive_link" name="drive_link" placeholder="https://drive.google.com/..."
                               class="w-full px-3 py-2 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold focus:outline-hidden focus:border-purple-500">
                    </div>
                </div>

                <button type="submit" class="clay-btn w-full py-3 text-white font-black text-xs flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i> Kirim Jurnal Bimbingan
                </button>
            </form>
        </div>

    @else
        <!-- NO PROJECT YET: Form Pengajuan (Khusus SMA) / Notice Panitia (Khusus SMK) -->
        @if($schoolType === 'SMA')
            <!-- Form Pengajuan Proposal Mandiri SMA -->
            <div class="clay-card p-5 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center text-xl shrink-0">
                        🔬
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900">Pengajuan Proposal Penelitian Akhir</h3>
                        <p class="text-[11px] text-slate-500 font-semibold">Tentukan judul penelitian ilmiah dan pilih rekan sekelas Anda.</p>
                    </div>
                </div>

                <form action="{{ route('mobile.final-project.propose') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label for="title" class="block text-xs font-black text-slate-800 mb-1">Rencana Judul Penelitian *</label>
                        <input type="text" id="title" name="title" required placeholder="Contoh: Analisis Kualitas Air Sungai di Sekitar..."
                               class="w-full px-3.5 py-2.5 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold focus:outline-hidden focus:border-purple-500">
                    </div>

                    <div>
                        <label for="abstract" class="block text-xs font-black text-slate-800 mb-1">Ringkasan / Latar Belakang Masalah *</label>
                        <textarea id="abstract" name="abstract" rows="4" required
                                  placeholder="Jelaskan secara singkat fenomena/masalah, tujuan penelitian, dan metode yang akan digunakan..."
                                  class="w-full p-3 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold resize-none focus:outline-hidden focus:border-purple-500"></textarea>
                    </div>

                    @if($classmates->isNotEmpty())
                    <div>
                        <label class="block text-xs font-black text-slate-800 mb-1">Pilih Anggota Kelompok (Opsional)</label>
                        <div class="max-h-40 overflow-y-auto space-y-1.5 p-2 bg-[#f4f7fc] rounded-2xl border-2 border-slate-200">
                            @foreach($classmates as $mate)
                                <label class="flex items-center gap-2 p-1.5 bg-white rounded-xl text-xs font-bold text-slate-800 border border-slate-200 cursor-pointer">
                                    <input type="checkbox" name="member_ids[]" value="{{ $mate->id }}" class="rounded text-purple-600 focus:ring-0">
                                    <span class="truncate">{{ $mate->full_name }} ({{ $mate->nisn ?? '-' }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <button type="submit" class="clay-btn w-full py-3 text-white font-black text-xs flex items-center justify-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i> Ajukan Proposal Penelitian
                    </button>
                </form>
            </div>
        @else
            <!-- SMK Notice: Judul & Kelompok Ditetapkan Panitia -->
            <div class="clay-card p-6 text-center space-y-3">
                <div class="w-14 h-14 rounded-3xl bg-purple-50 border-2 border-purple-200 text-purple-600 text-2xl flex items-center justify-center mx-auto shadow-inner">
                    🚀
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900">Project Akhir SMK Sedang Disiapkan</h3>
                    <p class="text-xs text-slate-500 font-semibold mt-1">Untuk siswa SMK Kelas XII, penentuan judul project, kelompok, dan Guru Pembimbing ditetapkan secara resmi oleh <strong>Panitia Project Akhir SMK</strong>.</p>
                </div>
                <div class="p-3 bg-purple-50 rounded-2xl border border-purple-200 text-[11px] font-bold text-purple-800">
                    Silakan tunggu penetapan kelompok dari Panitia atau konfirmasi ke Guru Produktif Jurusan Anda.
                </div>
            </div>
        @endif
    @endif

    <!-- Panduan & Format Berkas Unduhan -->
    @if($formats->isNotEmpty())
    <div class="clay-card p-4 space-y-2.5">
        <h4 class="text-xs font-black text-slate-900 flex items-center gap-1.5">
            <i class="fa-solid fa-download text-emerald-600"></i> Panduan & Template Format
        </h4>
        <div class="space-y-1.5">
            @foreach($formats as $fmt)
                <a href="{{ route('mobile.final-project.download-format', $fmt->id) }}" class="flex items-center justify-between p-2.5 bg-slate-50 rounded-2xl border border-slate-200 hover:border-purple-300 transition active:scale-98">
                    <div class="flex items-center gap-2 min-w-0">
                        <i class="fa-solid fa-file-pdf text-rose-500 text-base shrink-0"></i>
                        <div class="min-w-0">
                            <p class="text-xs font-black text-slate-800 truncate">{{ $fmt->title }}</p>
                            @if($fmt->description)
                                <p class="text-[10px] text-slate-500 truncate font-semibold">{{ $fmt->description }}</p>
                            @endif
                        </div>
                    </div>
                    <span class="text-[10px] font-black text-purple-600 bg-purple-50 px-2 py-1 rounded-xl border border-purple-200 shrink-0">
                        Unduh <i class="fa-solid fa-arrow-down text-[9px]"></i>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Riwayat Jurnal / Log Bimbingan -->
    @if($project)
    <div class="space-y-2.5">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider">Riwayat Log Bimbingan ({{ $logs->count() }})</h3>
        </div>

        @forelse($logs as $log)
            <div class="clay-card p-4 space-y-2 border-2 {{ $log->status === 'approved' ? 'border-emerald-200' : ($log->status === 'rejected' ? 'border-rose-200' : 'border-amber-200') }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-purple-100 text-purple-800 border border-purple-200">
                            {{ $stages[$log->stage]['label'] ?? ucfirst($log->stage) }}
                        </span>
                        <span class="text-[10px] font-bold text-slate-500">
                            {{ \Carbon\Carbon::parse($log->log_date)->translatedFormat('d M Y') }}
                        </span>
                    </div>

                    @if($log->status === 'approved')
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase clay-green">
                            <i class="fa-solid fa-check text-[8px]"></i> Disetujui (ACC)
                        </span>
                    @elseif($log->status === 'rejected')
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase clay-pink">
                            <i class="fa-solid fa-xmark text-[8px]"></i> Perlu Revisi
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase clay-yellow">
                            <i class="fa-solid fa-hourglass text-[8px]"></i> Menunggu
                        </span>
                    @endif
                </div>

                <p class="text-xs text-slate-700 font-bold leading-relaxed bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                    {{ $log->activity }}
                </p>

                @if($log->advisor_feedback)
                    <div class="p-2.5 rounded-xl bg-purple-50 border border-purple-200 space-y-1">
                        <span class="text-[10px] font-black text-purple-900 flex items-center gap-1">
                            <i class="fa-solid fa-comment-dots text-purple-600"></i> Catatan Evaluasi Guru Pembimbing:
                        </span>
                        <p class="text-[11px] text-purple-950 font-semibold leading-relaxed">{{ $log->advisor_feedback }}</p>
                    </div>
                @endif

                <div class="flex items-center justify-between pt-1 text-[10px] font-extrabold text-slate-500">
                    @if($log->file_attachment)
                        <a href="{{ asset('storage/' . $log->file_attachment) }}" target="_blank" class="text-blue-600 hover:underline flex items-center gap-1">
                            <i class="fa-solid fa-paperclip"></i> Lihat Lampiran
                        </a>
                    @elseif($log->drive_link)
                        <a href="{{ $log->drive_link }}" target="_blank" class="text-blue-600 hover:underline flex items-center gap-1">
                            <i class="fa-solid fa-link"></i> Buka Link Drive
                        </a>
                    @else
                        <span></span>
                    @endif
                </div>
            </div>
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold">
                Belum ada jurnal konsultasi yang dilaporkan. Kirim progres pengerjaan Anda di atas.
            </div>
        @endforelse
    </div>
    @endif
</div>
@endsection
