@php 
    $totalItems = $attendances->total();
    $no = $totalItems - (($attendances->currentPage() - 1) * $attendances->perPage()); 
@endphp
@foreach($attendances as $a)
@php
    // Cek apakah ini baris pertama di halaman pertama (siswa yang terakhir absen) dan bukan absen manual
    $isLastScanned = $loop->first && $attendances->currentPage() == 1 && $a->recorded_via !== 'manual';
@endphp
<tr class="border-b border-gray-100 transition {{ $isLastScanned ? 'bg-gradient-to-r from-green-400 to-emerald-500 text-slate-900 shadow-lg transform scale-[1.01] rounded-xl font-bold' : 'hover:bg-gray-50' }}">
    <td class="p-5">
        @if($isLastScanned)
            <span class="inline-flex items-center justify-center w-10 h-10 bg-black/10 text-slate-900 rounded-xl font-extrabold text-base animate-pulse">
                <i class="fas fa-bullseye text-slate-900 mr-0.5"></i>
            </span>
        @else
            <span class="inline-flex items-center justify-center w-9 h-9 bg-gray-100 text-gray-700 rounded-lg font-bold text-sm">{{ $no }}</span>
        @endif
        @php $no--; @endphp
    </td>
    <td class="p-5 font-bold {{ $isLastScanned ? 'text-slate-900 text-base' : 'text-gray-800 text-sm' }}">
        {{ $a->date ? $a->date->format('d M Y') : '-' }}
    </td>
    <td class="p-5">
        <div class="flex items-center gap-4">
            <div class="relative">
                @if($a->student)
                    <img src="{{ $a->student->photo_url }}" 
                         class="w-12 h-12 rounded-full object-cover border-2 {{ $isLastScanned ? 'border-slate-900' : 'border-green-500' }} shadow-md"
                         alt="{{ $a->student->full_name }}">
                @endif
                <div class="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-green-600 border-2 {{ $isLastScanned ? 'border-green-400' : 'border-white' }} rounded-full animate-ping" style="animation-duration: 2s;"></div>
                <div class="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-green-600 border-2 {{ $isLastScanned ? 'border-green-400' : 'border-white' }} rounded-full"></div>
            </div>
            <div class="flex flex-col">
                <span class="{{ $isLastScanned ? 'font-black text-slate-900 text-lg tracking-wide' : 'font-extrabold text-gray-800 text-base' }}">
                    {{ $a->student->full_name ?? '-' }}
                </span>
                <span class="{{ $isLastScanned ? 'text-[11px] text-slate-900/80 font-bold tracking-wider' : 'text-[10px] text-gray-400 font-medium tracking-wider' }}">
                    NISN: {{ $a->student->nisn ?? '-' }}
                </span>
            </div>
        </div>
    </td>
    <td class="p-5 {{ $isLastScanned ? 'font-black text-slate-900 text-lg' : 'font-bold text-gray-700 text-base' }}">{{ $a->classroom->class_name ?? '-' }}</td>
    <td class="p-5">
        <div class="flex flex-col gap-1.5">
            <span class="text-sm font-extrabold {{ $isLastScanned ? 'text-slate-900' : 'text-green-600' }}">IN: {{ $a->time_in ?? '--:--' }}</span>
            <span class="text-sm font-extrabold {{ $isLastScanned ? 'text-slate-900/85' : 'text-red-500' }}">OUT: {{ $a->time_out ?? '--:--' }}</span>
            
            @if($a->recorded_via == 'rfid')
            <span class="inline-flex items-center gap-1 px-2 py-0.5 {{ $isLastScanned ? 'bg-black/10 text-slate-900 border border-black/20' : 'bg-purple-100 text-purple-700' }} rounded-md text-[10px] font-black w-fit uppercase"><i class="fas fa-id-card"></i> RFID</span>
            @elseif($a->recorded_via == 'qr_gps')
            <span class="inline-flex items-center gap-1 px-2 py-0.5 {{ $isLastScanned ? 'bg-black/10 text-slate-900 border border-black/20' : 'bg-cyan-100 text-cyan-700' }} rounded-md text-[10px] font-black w-fit uppercase"><i class="fas fa-map-marker-alt"></i> GPS</span>
            @elseif($a->recorded_via == 'gps_pkl')
            <span class="inline-flex items-center gap-1 px-2 py-0.5 {{ $isLastScanned ? 'bg-black/10 text-slate-900 border border-black/20' : 'bg-amber-100 text-amber-800' }} rounded-md text-[10px] font-black w-fit uppercase"><i class="fas fa-briefcase"></i> PKL</span>
            @elseif($a->recorded_via == 'pjj_gps')
            <span class="inline-flex items-center gap-1 px-2 py-0.5 {{ $isLastScanned ? 'bg-black/10 text-slate-900 border border-black/20' : 'bg-amber-100 text-amber-900 border border-amber-300' }} rounded-md text-[10px] font-black w-fit uppercase"><i class="fas fa-house-laptop"></i> PJJ GPS</span>
            @elseif($a->recorded_via == 'pjj_lms')
            <span class="inline-flex items-center gap-1 px-2 py-0.5 {{ $isLastScanned ? 'bg-black/10 text-slate-900 border border-black/20' : 'bg-blue-100 text-blue-900 border border-blue-300' }} rounded-md text-[10px] font-black w-fit uppercase"><i class="fas fa-laptop-code"></i> PJJ LMS</span>
            @elseif($a->recorded_via)
            <span class="inline-flex items-center gap-1 px-2 py-0.5 {{ $isLastScanned ? 'bg-black/10 text-slate-900 border border-black/20' : 'bg-gray-100 text-gray-600' }} rounded-md text-[10px] font-black w-fit uppercase"><i class="fas fa-pen"></i> Manual</span>
            @endif
        </div>
    </td>
    <td class="p-5">
        @if($isLastScanned)
            @if($a->status == 'hadir')
            <span class="px-4 py-1.5 rounded-full text-sm font-black shadow-md inline-flex items-center gap-1.5" style="background-color: #0f172a !important; color: #4ade80 !important; border: 1.5px solid rgba(255,255,255,0.15) !important;">
                <i class="fas fa-check-circle" style="color: #4ade80 !important;"></i> HADIR (BARU)
            </span>
            @elseif($a->status == 'terlambat')
            <span class="px-4 py-1.5 rounded-full text-sm font-black shadow-md inline-flex items-center gap-1.5" style="background-color: #0f172a !important; color: #fb923c !important; border: 1.5px solid rgba(255,255,255,0.15) !important;">
                <i class="fas fa-clock" style="color: #fb923c !important;"></i> TERLAMBAT (BARU)
            </span>
            @elseif($a->status == 'izin')
            <span class="px-4 py-1.5 rounded-full text-sm font-black shadow-md inline-flex items-center gap-1.5" style="background-color: #0f172a !important; color: #60a5fa !important; border: 1.5px solid rgba(255,255,255,0.15) !important;">
                <i class="fas fa-edit" style="color: #60a5fa !important;"></i> IZIN (BARU)
            </span>
            @elseif($a->status == 'sakit')
            <span class="px-4 py-1.5 rounded-full text-sm font-black shadow-md inline-flex items-center gap-1.5" style="background-color: #0f172a !important; color: #facc15 !important; border: 1.5px solid rgba(255,255,255,0.15) !important;">
                <i class="fas fa-thermometer" style="color: #facc15 !important;"></i> SAKIT (BARU)
            </span>
            @else
            <span class="px-4 py-1.5 rounded-full text-sm font-black shadow-md inline-flex items-center gap-1.5" style="background-color: #0f172a !important; color: #f87171 !important; border: 1.5px solid rgba(255,255,255,0.15) !important;">
                <i class="fas fa-times-circle" style="color: #f87171 !important;"></i> ALPA (BARU)
            </span>
            @endif
        @else
            @if($a->status == 'hadir')
            <span class="px-3.5 py-1.5 bg-green-100 text-green-800 rounded-full text-sm font-bold border border-green-200"><i class="fas fa-check-circle text-green-500 mr-1"></i> Hadir</span>
            @elseif($a->status == 'terlambat')
            <span class="px-3.5 py-1.5 bg-orange-100 text-orange-800 rounded-full text-sm font-bold border border-orange-200"><i class="fas fa-clock text-orange-500 mr-1"></i> Terlambat</span>
            @elseif($a->status == 'izin')
            <span class="px-3.5 py-1.5 bg-blue-100 text-blue-800 rounded-full text-sm font-bold border border-blue-200"><i class="fas fa-edit mr-1"></i> Izin</span>
            @elseif($a->status == 'sakit')
            <span class="px-3.5 py-1.5 bg-yellow-100 text-yellow-800 rounded-full text-sm font-bold border border-yellow-200"><i class="fas fa-thermometer mr-1"></i> Sakit</span>
            @else
            <span class="px-3.5 py-1.5 bg-red-100 text-red-800 rounded-full text-sm font-bold border border-red-200"><i class="fas fa-times-circle text-red-500 mr-1"></i> Alpa</span>
            @endif
        @endif

        @if($a->attachment)
        <div class="mt-2.5">
            <a href="{{ asset('storage/' . $a->attachment) }}" target="_blank" class="text-[11px] {{ $isLastScanned ? 'text-slate-900 hover:underline' : 'text-blue-600 hover:text-blue-800 hover:underline' }} flex items-center gap-1 font-bold">
                <i class="fas fa-paperclip"></i> Lihat Dokumen
            </a>
        </div>
        @endif
    </td>
    <td class="p-5">
        <div class="flex items-center justify-center gap-2">
            <a href="{{ route('admin.attendances.edit', $a) }}" class="w-10 h-10 {{ $isLastScanned ? 'bg-black/10 hover:bg-black/20 text-slate-900' : 'bg-green-100 hover:bg-green-200 text-green-700' }} rounded-xl flex items-center justify-center transition transform hover:scale-105 shadow-sm" title="Edit">
                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </a>
            <form action="{{ route('admin.attendances.destroy', $a) }}" method="POST" style="display:inline">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('Hapus absensi ini?')" class="w-10 h-10 {{ $isLastScanned ? 'bg-black/10 hover:bg-black/20 text-slate-900' : 'bg-red-100 hover:bg-red-200 text-red-700' }} rounded-xl flex items-center justify-center transition transform hover:scale-105 shadow-sm" title="Hapus">
                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </form>
        </div>
    </td>
</tr>
@endforeach
