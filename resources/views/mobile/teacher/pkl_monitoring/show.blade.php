@extends('mobile.layouts.app')

@section('title', 'Lapor Kunjungan DUDI - ' . $dudi->name)

@section('content')
<div class="space-y-4 pt-1">
    <!-- Header Navigation -->
    <div class="flex items-center justify-between px-1">
        <a href="{{ route('mobile.guru.pkl.monitoring') }}" class="w-9 h-9 rounded-2xl bg-white border-2 border-slate-200 shadow-sm flex items-center justify-center text-slate-700 hover:bg-slate-50 transition active:scale-95">
            <i class="fa-solid fa-arrow-left text-xs"></i>
        </a>
        <h2 class="text-sm font-black text-slate-900 uppercase tracking-wide">Laporan Kunjungan DUDI</h2>
        <div class="w-9"></div>
    </div>

    <!-- DUDI Hero Card (Clay Blue) -->
    <div class="clay-blue p-5 space-y-2.5">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-black uppercase tracking-wider bg-white/30 px-2.5 py-0.5 rounded-full border border-white/40">
                {{ $shift ? 'Shift ' . ucfirst($shift) : 'Shift Reguler' }}
            </span>
            <span class="text-[10px] font-black bg-white/20 px-2.5 py-0.5 rounded-full">
                {{ $placements->count() }} Siswa Bimbingan
            </span>
        </div>

        <h3 class="text-base font-black text-white leading-tight">
            {{ $dudi->name }}
        </h3>
        @if($dudi->address)
            <p class="text-xs text-blue-100 font-semibold flex items-center gap-1.5">
                <i class="fa-solid fa-location-dot text-xs shrink-0"></i>
                <span class="truncate">{{ $dudi->address }}</span>
            </p>
        @endif
    </div>

    <!-- Siswa di DUDI ini (Clay Card) -->
    <div class="clay-card p-4 space-y-2">
        <h4 class="text-xs font-black text-slate-800 flex items-center gap-1.5">
            <i class="fa-solid fa-users text-blue-600"></i> Siswa PKL di Lokasi Ini ({{ $placements->count() }})
        </h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
            @foreach($placements as $plc)
                <div class="flex items-center justify-between p-2 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                    <span class="font-black text-slate-800 truncate">{{ $plc->student->full_name ?? '-' }}</span>
                    <a href="{{ route('mobile.guru.pkl.show', $plc->id) }}" class="text-[10px] font-black text-blue-600 hover:underline shrink-0">
                        Logbook <i class="fa-solid fa-chevron-right text-[8px]"></i>
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Form Upload / Update Perangkat PKL (Clay Card) -->
    <div class="clay-card p-4 space-y-3">
        <div class="flex items-center justify-between">
            <h4 class="text-xs font-black text-slate-800 flex items-center gap-1.5">
                <i class="fa-solid fa-folder-open text-amber-500"></i> Berkas Perangkat PKL
            </h4>
            <span class="text-[10px] font-black px-2 py-0.5 rounded-full uppercase
                {{ $isPerangkatReady ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300' }}">
                {{ $isPerangkatReady ? 'Perangkat Siap' : 'Belum Upload' }}
            </span>
        </div>

        @if($perangkatFilePath)
            <div class="p-2.5 bg-emerald-50 rounded-xl border border-emerald-200 text-[11px] font-bold text-emerald-900 flex items-center justify-between">
                <span class="flex items-center gap-1.5"><i class="fa-solid fa-file-pdf text-emerald-600"></i> Dokumen telah diunggah</span>
                <a href="{{ asset('storage/' . $perangkatFilePath) }}" target="_blank" class="text-emerald-700 underline font-black">Lihat Berkas</a>
            </div>
        @endif

        <form action="{{ route('mobile.guru.pkl.monitoring.perangkat', [$dudi->id, $shift ?? 'null']) }}" method="POST" enctype="multipart/form-data" class="space-y-2">
            @csrf
            <div>
                <label for="perangkat_file" class="block text-[11px] font-black text-slate-700 mb-1">Unggah / Perbarui Berkas Perangkat (PDF/ZIP max 10MB)</label>
                <input type="file" id="perangkat_file" name="perangkat_file" required accept=".pdf,.zip,.rar,.jpg,.png"
                       class="w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
            </div>
            <button type="submit" class="w-full py-2 bg-slate-800 text-white font-black text-xs rounded-xl shadow-sm active:scale-95 transition">
                Simpan Perangkat PKL
            </button>
        </form>
    </div>

    <!-- Form Laporan Kunjungan Mingguan DUDI (Clay Card) -->
    <div class="clay-card p-5 space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-calendar-plus text-blue-600"></i> Form Laporan Kunjungan Mingguan
            </h3>
            <span class="text-[9px] font-extrabold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200">1x Setiap Minggu</span>
        </div>

        <form action="{{ route('mobile.guru.pkl.monitoring.store', [$dudi->id, $shift ?? 'null']) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <div>
                <label for="monitoring_date" class="block text-xs font-black text-slate-800 mb-1">Tanggal Kunjungan Monitoring *</label>
                <input type="date" id="monitoring_date" name="monitoring_date" value="{{ date('Y-m-d') }}" onclick="try { this.showPicker(); } catch(e) {}" required
                       class="w-full px-3.5 py-2.5 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold cursor-pointer focus:outline-hidden focus:border-blue-500">
            </div>

            <div>
                <label for="notes" class="block text-xs font-black text-slate-800 mb-1">Catatan Evaluasi / Temuan di DUDI</label>
                <textarea id="notes" name="notes" rows="3"
                          placeholder="Jelaskan kondisi siswa di tempat industri, feedback dari instruktur DUDI, kendala, atau arahan bimbingan..."
                          class="w-full p-3 bg-[#f4f7fc] border-2 border-slate-200 rounded-2xl text-slate-900 text-xs font-bold resize-none focus:outline-hidden focus:border-blue-500"></textarea>
            </div>

            <div class="space-y-2">
                <div>
                    <label for="assignment_letter" class="block text-xs font-black text-slate-800 mb-1">Surat Tugas Kunjungan (PDF/Foto) *</label>
                    <input type="file" id="assignment_letter" name="assignment_letter" required accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                </div>

                <div>
                    <label for="photo" class="block text-xs font-black text-slate-800 mb-1">Foto Bukti Kunjungan di DUDI (Foto Bersama/Tempat) *</label>
                    <input type="file" id="photo" name="photo" required accept="image/*"
                           class="w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                </div>
            </div>

            <button type="submit" class="clay-btn w-full py-3 text-white font-black text-xs flex items-center justify-center gap-2">
                <i class="fa-solid fa-paper-plane"></i> Kirim Laporan Kunjungan Mingguan
            </button>
        </form>
    </div>

    <!-- Riwayat Kunjungan Mingguan -->
    <div class="space-y-2.5">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider">
                Riwayat Kunjungan DUDI ({{ $monitorings->count() }})
            </h3>
        </div>

        @forelse($monitorings as $mon)
            <div class="clay-card p-4 space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black text-slate-900 flex items-center gap-1.5">
                        <i class="fa-regular fa-calendar text-blue-600"></i>
                        {{ \Carbon\Carbon::parse($mon->monitoring_date)->translatedFormat('l, d F Y') }}
                    </span>
                    <span class="text-[10px] font-black text-slate-500">
                        {{ \Carbon\Carbon::parse($mon->created_at)->diffForHumans() }}
                    </span>
                </div>

                @if($mon->notes)
                    <p class="text-xs text-slate-700 font-bold leading-relaxed bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        {{ $mon->notes }}
                    </p>
                @endif

                <!-- Attachment Previews -->
                <div class="grid grid-cols-2 gap-2 pt-1">
                    @if($mon->photo_path)
                        <a href="{{ asset('storage/' . $mon->photo_path) }}" target="_blank" class="block rounded-xl overflow-hidden border border-slate-200 relative group aspect-video">
                            <img src="{{ asset('storage/' . $mon->photo_path) }}" alt="Foto Kunjungan" class="w-full h-full object-cover group-hover:scale-105 transition">
                            <span class="absolute bottom-1 right-1 bg-black/60 text-white text-[8px] font-black px-1.5 py-0.5 rounded-md backdrop-blur-xs">
                                📷 Foto
                            </span>
                        </a>
                    @endif

                    @if($mon->assignment_letter_path)
                        <a href="{{ asset('storage/' . $mon->assignment_letter_path) }}" target="_blank" class="flex flex-col items-center justify-center p-2 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 hover:bg-blue-100 transition">
                            <i class="fa-solid fa-file-lines text-xl mb-0.5"></i>
                            <span class="text-[10px] font-black">Surat Tugas</span>
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="clay-card p-6 text-center text-slate-500 text-xs font-bold space-y-1">
                <p>Belum ada laporan kunjungan mingguan untuk DUDI ini. Laporkan kunjungan pertama Anda pada form di atas.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
