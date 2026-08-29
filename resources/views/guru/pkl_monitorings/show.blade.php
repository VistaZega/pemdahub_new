@extends('layouts.guru')

@section('title', 'Detail Monitoring PKL')
@section('page_title', 'Detail Monitoring PKL - ' . ($dudi->name ?? 'DUDI'))

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">{{ $dudi->name ?? 'Unknown DUDI' }}</h2>
            <p class="text-sm text-slate-500 mt-1">Shift: {{ $shift ?: 'Tidak ada shift' }}</p>
        </div>
        <a href="{{ route('guru.pkl_monitorings.index') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-500"></i>
            {{ session('success') }}
        </div>
    @endif
    
    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Siswa & Logbook Preview -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="font-bold text-slate-800"><i class="fas fa-users text-indigo-500 mr-2"></i> Daftar Siswa Bimbingan</h3>
                </div>
                <div class="p-0">
                    @foreach($placements as $placement)
                        <div class="p-5 border-b border-slate-100 last:border-0 hover:bg-slate-50 transition-colors">
                            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full overflow-hidden border border-slate-200 shrink-0">
                                        <img src="{{ $placement->student->photo_url ?? asset('images/default-avatar.png') }}" class="w-full h-full object-cover" alt="Foto Siswa">
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800">{{ $placement->student->full_name ?? 'Nama Siswa' }}</h4>
                                        <p class="text-xs text-slate-500">{{ $placement->student->nisn ?? '-' }} &bull; {{ $placement->student->classroom->class_name ?? '-' }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('guru.pkl.show', $placement->id) }}" class="px-3 py-1.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-lg text-xs font-semibold transition-colors shrink-0" target="_blank">
                                    <i class="fas fa-book-open mr-1"></i> Buka Logbook Lengkap
                                </a>
                            </div>
                            
                            @if($placement->logs->isNotEmpty())
                                <div class="mt-4 p-3 bg-white rounded-xl border border-slate-200">
                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Aktivitas Terakhir:</p>
                                    <ul class="space-y-2">
                                        @foreach($placement->logs as $log)
                                              <li class="flex items-start gap-3 text-sm">
                                                  <i class="fas fa-check-circle text-emerald-500 mt-0.5 text-xs"></i>
                                                  <div class="flex-1">
                                                      <span class="font-medium text-slate-700">{{ $log->log_date->format('d/m/Y') }}:</span>
                                                      <span class="text-slate-600">{{ Str::limit($log->activity, 80) }}</span>
                                                      @if($log->photo)
                                                      <div x-data="{ open: false }" class="mt-2">
                                                          <img @click="open = true" src="{{ Storage::url($log->photo) }}" class="w-16 h-16 object-cover rounded-lg cursor-pointer border border-slate-200 hover:opacity-80 transition-opacity shadow-sm" title="Lihat Foto Kegiatan" alt="Foto">
                                                          
                                                          <div x-show="open" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/80 backdrop-blur-sm p-4" @click="open = false" @keydown.escape.window="open = false">
                                                              <img src="{{ Storage::url($log->photo) }}" class="max-w-full max-h-[90vh] rounded-xl shadow-2xl" @click.stop>
                                                              <button @click="open = false" class="absolute top-6 right-6 text-white hover:text-slate-300">
                                                                  <i class="fas fa-times text-3xl"></i>
                                                              </button>
                                                          </div>
                                                      </div>
                                                      @endif
                                                  </div>
                                              </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <div class="mt-4 p-3 bg-slate-50 rounded-xl border border-slate-200 border-dashed text-center">
                                    <p class="text-xs text-slate-500">Siswa belum mengisi logbook.</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- History Monitoring -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="font-bold text-slate-800"><i class="fas fa-history text-indigo-500 mr-2"></i> Riwayat Laporan Monitoring</h3>
                </div>
                <div class="p-0">
                    @forelse($monitorings as $mon)
                        <div class="p-5 border-b border-slate-100 last:border-0 flex flex-col sm:flex-row justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-slate-800">{{ $mon->monitoring_date->format('d F Y') }}</h4>
                                    <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 text-[10px] font-bold uppercase rounded-md tracking-wider">Periode Ke-{{ $mon->periode_ke }}</span>
                                </div>
                                <div class="text-sm text-slate-600 mt-1.5 whitespace-pre-line leading-relaxed">{{ $mon->notes ?? 'Tidak ada catatan' }}</div>
                            </div>
                              <div class="flex gap-3 shrink-0">
                                  @if($mon->photo_path)
                                      <div x-data="{ open: false }">
                                          <img @click="open = true" src="{{ Storage::url($mon->photo_path) }}" class="w-16 h-16 object-cover rounded-xl cursor-pointer border border-slate-200 hover:opacity-80 transition-opacity shadow-sm" title="Lihat Foto Monitoring" alt="Foto Monitoring">
                                          
                                          <div x-show="open" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/80 backdrop-blur-sm p-4" @click="open = false" @keydown.escape.window="open = false">
                                              <img src="{{ Storage::url($mon->photo_path) }}" class="max-w-full max-h-[90vh] rounded-xl shadow-2xl" @click.stop>
                                              <button @click="open = false" class="absolute top-6 right-6 text-white hover:text-slate-300">
                                                  <i class="fas fa-times text-3xl"></i>
                                              </button>
                                          </div>
                                      </div>
                                  @endif
                                  @if($mon->assignment_letter_path)
                                    <a href="{{ Storage::url($mon->assignment_letter_path) }}" target="_blank" class="w-10 h-10 flex items-center justify-center bg-emerald-50 text-emerald-600 hover:bg-emerald-100 rounded-xl transition-colors" title="Lihat Surat">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-500 text-sm">
                            Belum ada riwayat pelaporan monitoring untuk DUDI ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column: Form Laporan & Perangkat -->
        <div class="space-y-6">
            <!-- Form Perangkat -->
              <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                  <div class="p-5 border-b border-slate-100 bg-slate-50">
                      <h3 class="font-bold text-slate-800"><i class="fas fa-clipboard-check text-indigo-500 mr-2"></i> Dokumen Perangkat PKL</h3>
                  </div>
                  <div class="p-5">
                      @if($perangkatFilePath)
                          <div class="flex items-center justify-between p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                              <div class="flex items-center gap-3">
                                  <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-lg">
                                      <i class="fas fa-check"></i>
                                  </div>
                                  <div>
                                      <p class="font-bold text-emerald-800">Perangkat Sudah Diunggah</p>
                                      <p class="text-xs text-emerald-600">Terima kasih, dokumen panduan dan checklist sudah ditandatangani DUDI.</p>
                                  </div>
                              </div>
                              <a href="{{ Storage::url($perangkatFilePath) }}" target="_blank" class="px-4 py-2 bg-white border border-emerald-200 text-emerald-700 rounded-lg text-sm font-semibold hover:bg-emerald-100 transition-colors shadow-sm">
                                  Lihat Dokumen
                              </a>
                          </div>
                          
                          <div class="mt-4 pt-4 border-t border-slate-100" x-data="{ showUpload: false }">
                              <button @click="showUpload = !showUpload" type="button" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                                  <i class="fas fa-upload mr-1"></i> Unggah ulang dokumen baru
                              </button>
                              
                              <form x-show="showUpload" x-cloak style="display: none;" action="{{ route('guru.pkl_monitorings.update-perangkat', [$dudi->id, $shift ?: 'null']) }}" method="POST" enctype="multipart/form-data" class="mt-3 space-y-3 p-4 bg-slate-50 rounded-xl border border-slate-200">
                                  @csrf
                                  <input type="file" name="perangkat_file" required accept=".pdf,image/*" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                  <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 transition-colors">
                                      Simpan Dokumen Pengganti
                                  </button>
                              </form>
                          </div>
                      @else
                          <form action="{{ route('guru.pkl_monitorings.update-perangkat', [$dudi->id, $shift ?: 'null']) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                              @csrf
                              <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl mb-4">
                                  <h4 class="font-bold text-amber-800 mb-1"><i class="fas fa-exclamation-circle mr-1"></i> Perangkat Belum Diunggah</h4>
                                  <p class="text-xs text-amber-700">Silakan unggah pindaian (scan) atau foto dari dokumen checklist perangkat yang sudah ditandatangani oleh DUDI pada saat penyerahan siswa.</p>
                              </div>
                              
                              <div>
                                  <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Unggah Dokumen Perangkat (PDF/Image) <span class="text-rose-500">*</span></label>
                                  <input type="file" name="perangkat_file" required accept=".pdf,image/*" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                              </div>
                              
                              <button type="submit" class="w-full px-4 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-colors">
                                  Unggah Perangkat PKL
                              </button>
                          </form>
                      @endif
                  </div>
              </div>

            <!-- Form Buat Laporan -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50">
                    <h3 class="font-bold text-slate-800"><i class="fas fa-pen-alt text-indigo-500 mr-2"></i> Buat Laporan Mingguan</h3>
                </div>
                <div class="p-5">
                    <!-- Warning Banner Petunjuk Yayasan -->
                    <div class="p-4 bg-amber-50 border-2 border-amber-300 rounded-2xl mb-5 text-xs space-y-2">
                        <div class="flex items-center gap-2 text-amber-900 font-bold">
                            <i class="fas fa-balance-scale text-amber-600 text-sm"></i>
                            <span class="uppercase tracking-wider">Pemberitahuan Resmi Pembimbing PKL</span>
                        </div>
                        <p class="text-amber-800 leading-relaxed font-medium">
                            Laporan Monitoring PKL merupakan instrumen resmi evaluasi pembimbingan. Guru Pembimbing <strong>WAJIB</strong> menguraikan ketiga poin laporan secara objektif, lengkap, dan faktual sesuai hasil supervisi di tempat industri. <strong>Dilarang mengisi laporan secara asal-asalan atau formalitas.</strong>
                        </p>
                        <div class="text-[11px] font-bold text-amber-700 italic flex items-center gap-1.5 pt-1 border-t border-amber-200">
                            <span>* Di bawah pengawasan dan petunjuk yayasan</span>
                        </div>
                    </div>

                    <form action="{{ route('guru.pkl_monitorings.store', [$dudi->id, $shift ?: 'null']) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Tanggal Monitoring <span class="text-rose-500">*</span></label>
                            <input type="date" id="monitoring_date_input" name="monitoring_date" required value="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                            <p id="period_indicator" class="text-xs text-indigo-600 font-semibold mt-2" style="display: none;">Laporan ini akan masuk sebagai: <span class="bg-indigo-100 px-2 py-0.5 rounded uppercase tracking-wider">Periode Ke-<span id="period_number">1</span></span></p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Upload Surat Monitoring (PDF/IMG) <span class="text-rose-500">*</span></label>
                            <input type="file" name="assignment_letter" required accept=".pdf,image/*" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="text-[11px] text-slate-500 mt-1">Surat yang sudah ditandatangani oleh pihak DUDI.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Foto Bukti Kunjungan di Lokasi DUDI <span class="text-rose-500">*</span></label>
                            <input type="file" name="photo" required accept="image/*" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>

                        <div class="space-y-3 pt-2 border-t border-slate-100">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                    1. Evaluasi Kinerja & Kedisiplinan Siswa <span class="text-rose-500">*</span>
                                </label>
                                <textarea name="evaluation_student" rows="2" minlength="20" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder:text-slate-400" placeholder="Jelaskan kehadiran, kedisiplinan jam kerja, kepatuhan K3, dan etika siswa di tempat industri..."></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                    2. Feedback & Catatan dari Instruktur DUDI <span class="text-rose-500">*</span>
                                </label>
                                <textarea name="feedback_dudi" rows="2" minlength="20" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder:text-slate-400" placeholder="Tuliskan masukan, saran, apresiasi, atau catatan dari instruktur industri terkait penguasaan kompetensi siswa..."></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                    3. Arahan Bimbingan Guru & Solusi Tindak Lanjut <span class="text-rose-500">*</span>
                                </label>
                                <textarea name="guidance_action" rows="2" minlength="20" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder:text-slate-400" placeholder="Jelaskan arahan penguatan teknis/moral yang diberikan guru, penyelesaian kendala, dan target minggu berikutnya..."></textarea>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-md shadow-indigo-200 transition-all">
                            <i class="fas fa-paper-plane mr-2"></i> Kirim Laporan Monitoring Resmi
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('monitoring_date_input');
    const indicator = document.getElementById('period_indicator');
    const periodNumberSpan = document.getElementById('period_number');

    function calculatePeriod(dateString) {
        if (!dateString) return null;
        
        const selectedDate = new Date(dateString);
        // Base date is July 20, 2026
        const baseDate = new Date('2026-07-20T00:00:00');
        
        // Reset time to start of day for accurate day diff
        selectedDate.setHours(0,0,0,0);
        baseDate.setHours(0,0,0,0);
        
        if (selectedDate < baseDate) {
            return 1;
        }
        
        const diffTime = Math.abs(selectedDate - baseDate);
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24)); 
        
        // 13 days per period
        let period = Math.floor(diffDays / 13) + 1;
        return Math.min(8, period);
    }

    function updatePeriodIndicator() {
        const period = calculatePeriod(dateInput.value);
        if (period !== null) {
            periodNumberSpan.textContent = period;
            indicator.style.display = 'block';
        } else {
            indicator.style.display = 'none';
        }
    }

    if (dateInput) {
        dateInput.addEventListener('change', updatePeriodIndicator);
        // Trigger on load
        updatePeriodIndicator();
    }
});
</script>
@endsection
