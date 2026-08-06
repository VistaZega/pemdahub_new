@extends('layouts.guru')

@section('title', 'Ajukan Cuti Baru - Portal Guru')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    {{-- Header Banner (Neo-Brutalism) --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl p-6 border-2 border-black" style="background: linear-gradient(135deg, #090d16 0%, #065f46 50%, #047857 100%) !important; color: #ffffff !important;">
        <div class="relative z-10 flex items-center gap-4">
            <a href="{{ route('guru.leaves.index') }}" class="w-10 h-10 bg-amber-400 text-black border-2 border-black rounded-xl flex items-center justify-center hover:bg-amber-300 transition-all shadow-sm flex-shrink-0">
                <i class="fas fa-arrow-left text-black"></i>
            </a>
            <div>
                <h1 class="text-xl md:text-2xl font-black text-white" style="color: #ffffff !important;">Ajukan Cuti Baru</h1>
                <p class="text-xs md:text-sm font-bold text-emerald-200 mt-0.5" style="color: #a7f3d0 !important;">Isi formulir di bawah ini untuk mengajukan permohonan cuti atau izin</p>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-xl shadow-sm">
        <div class="flex items-center gap-3 mb-2">
            <i class="fas fa-exclamation-triangle text-rose-600 text-lg"></i>
            <p class="font-bold text-rose-800 text-sm">Terjadi Kesalahan Validasi</p>
        </div>
        <ul class="list-disc list-inside text-rose-700 text-xs space-y-1 pl-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('guru.leaves.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Jenis Cuti --}}
                <div class="sm:col-span-2">
                    <label for="leave_type" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jenis Cuti / Izin <span class="text-rose-500">*</span></label>
                    <select name="leave_type" id="leave_type" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none text-sm transition-all" required>
                        <option value="" disabled selected>-- Pilih Jenis Cuti --</option>
                        @foreach($leaveTypes as $key => $label)
                            <option value="{{ $key }}" {{ old('leave_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tanggal Mulai --}}
                <div>
                    <label for="start_date" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Tanggal Mulai <span class="text-rose-500">*</span></label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none text-sm transition-all" required>
                </div>

                {{-- Tanggal Selesai --}}
                <div>
                    <label for="end_date" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Tanggal Selesai <span class="text-rose-500">*</span></label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none text-sm transition-all" required>
                </div>

                {{-- Alasan Cuti --}}
                <div class="sm:col-span-2">
                    <label for="reason" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Alasan Cuti / Keterangan <span class="text-rose-500">*</span></label>
                    <textarea name="reason" id="reason" rows="4" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none text-sm transition-all" placeholder="Tuliskan alasan pengajuan cuti secara detail..." required>{{ old('reason') }}</textarea>
                </div>

                {{-- Lampiran Dokumen --}}
                <div class="sm:col-span-2">
                    <label for="attachment" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Dokumen Pendukung / Lampiran <span class="text-slate-400 font-medium">(Opsional)</span></label>
                    <input type="file" name="attachment" id="attachment" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-gray-200 rounded-xl p-2.5 focus:outline-none">
                    <p class="text-[10px] text-gray-400 mt-1.5">Format file: PDF, JPG, JPEG, PNG (Maks. 5MB). Lampirkan surat dokter jika sakit, atau dokumen pendukung relevan lainnya.</p>
                </div>

                {{-- Catatan Tambahan --}}
                <div class="sm:col-span-2">
                    <label for="notes" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Catatan Tambahan <span class="text-slate-400 font-medium">(Opsional)</span></label>
                    <textarea name="notes" id="notes" rows="2" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none text-sm transition-all" placeholder="Catatan tambahan untuk kepala sekolah atau yayasan..." >{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-gray-100 pt-6">
                <a href="{{ route('guru.leaves.index') }}" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 shadow-sm flex items-center gap-2 hover:-translate-y-0.5">
                    <i class="fas fa-paper-plane text-xs"></i> Kirim Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
