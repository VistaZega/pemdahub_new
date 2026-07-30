@extends('layouts.yayasan')

@section('title', 'Surat Digital & Edaran Yayasan')

@section('content')
<div class="space-y-6">
    {{-- Header Banner --}}
    <div class="bg-gradient-to-r from-violet-700 via-purple-700 to-indigo-800 rounded-2xl p-6 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-6 -bottom-10 opacity-10 text-9xl">
            <i class="fas fa-file-signature"></i>
        </div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="bg-white/20 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider">Modul Persuratan Digital</span>
                    <span class="bg-emerald-500/30 text-emerald-200 border border-emerald-400/30 px-2.5 py-0.5 rounded-full text-xs font-medium"><i class="fas fa-qrcode mr-1"></i> QR Code Signature Ready</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Surat Digital & Edaran Yayasan</h1>
                <p class="text-violet-200 text-sm mt-1">Yayasan Perguruan PEMBDA Nias • Pengelolaan & Penerbitan Dokumen Resmi Ber-QR Code Keaslian</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('yayasan.letters.create', ['preset' => 'standar_input']) }}" class="bg-amber-500 hover:bg-amber-600 text-gray-900 font-bold px-4 py-2.5 rounded-xl shadow-lg hover:shadow-amber-500/20 transition-all flex items-center gap-2 text-sm">
                    <i class="fas fa-magic"></i>
                    <span>Preset SE Input Data (3 Agt)</span>
                </a>
                <a href="{{ route('yayasan.letters.create') }}" class="bg-white text-violet-700 hover:bg-violet-50 font-bold px-4 py-2.5 rounded-xl shadow-lg transition-all flex items-center gap-2 text-sm">
                    <i class="fas fa-plus-circle"></i>
                    <span>Buat Surat Digital Baru</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Session Alert --}}
    @if(session('success'))
    <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-xl shadow-sm text-emerald-800 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700"><i class="fas fa-times"></i></button>
    </div>
    @endif

    {{-- Filter & List Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-100">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Arsip Surat Resmi Terbit</h3>
                <p class="text-xs text-gray-500">Daftar dokumen resmi yang telah ditandatangani digital dan dipublikasikan ke unit sekolah</p>
            </div>
            <div class="text-xs text-gray-500 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200">
                Total Terbit: <span class="font-bold text-violet-700">{{ $letters->total() }} Dokumen</span>
            </div>
        </div>

        @if($letters->isEmpty())
        <div class="text-center py-12 border-2 border-dashed border-gray-200 rounded-2xl">
            <div class="w-16 h-16 bg-violet-50 text-violet-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fas fa-folder-open"></i>
            </div>
            <h4 class="text-base font-bold text-gray-800">Belum Ada Surat Digital Terbit</h4>
            <p class="text-sm text-gray-500 max-w-md mx-auto mt-1 mb-6">Buat surat edaran, keputusan, atau pemberitahuan resmi pertama Anda dengan QR Code otentikasi digital.</p>
            <div class="flex justify-center gap-3">
                <a href="{{ route('yayasan.letters.create', ['preset' => 'standar_input']) }}" class="bg-violet-600 hover:bg-violet-700 text-white font-semibold px-4 py-2 rounded-xl text-sm transition-colors inline-flex items-center gap-2">
                    <i class="fas fa-file-alt"></i> Buat Surat Edaran 3 Agustus 2026
                </a>
            </div>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 font-bold uppercase text-xs">
                    <tr>
                        <th class="py-3.5 px-4 rounded-l-xl">No. Surat & Jenis</th>
                        <th class="py-3.5 px-4">Perihal / Judul Surat</th>
                        <th class="py-3.5 px-4">Tujuan Penerima</th>
                        <th class="py-3.5 px-4">Tgl Terbit</th>
                        <th class="py-3.5 px-4">TTD & QR Hash</th>
                        <th class="py-3.5 px-4 text-center">Tanda Terima</th>
                        <th class="py-3.5 px-4 text-right rounded-r-xl">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($letters as $letter)
                    @php
                        $targetLabel = is_array($letter->recipients) && isset($letter->recipients['target_label']) 
                            ? $letter->recipients['target_label'] 
                            : 'Kepala Sekolah se-Perguruan Pembda Nias';
                    @endphp
                    <tr class="hover:bg-violet-50/40 transition-colors">
                        <td class="py-4 px-4">
                            <span class="font-bold text-gray-900 block font-mono text-xs">{{ $letter->letter_number }}</span>
                            <span class="inline-block bg-violet-100 text-violet-700 text-[10px] font-semibold px-2 py-0.5 rounded mt-1">
                                {{ $categories[$letter->category] ?? ucfirst($letter->category) }}
                            </span>
                        </td>
                        <td class="py-4 px-4 max-w-xs">
                            <a href="{{ route('yayasan.letters.show', $letter->id) }}" class="font-bold text-gray-900 hover:text-violet-700 transition-colors line-clamp-2">
                                {{ $letter->title }}
                            </a>
                            @if($letter->deadline_date)
                            <span class="text-[11px] text-amber-600 font-semibold block mt-0.5">
                                <i class="fas fa-clock mr-1"></i>Tenggat: {{ \Carbon\Carbon::parse($letter->deadline_date)->translatedFormat('d F Y') }}
                            </span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-xs font-medium text-gray-700">
                            {{ $targetLabel }}
                        </td>
                        <td class="py-4 px-4 text-xs text-gray-600">
                            {{ \Carbon\Carbon::parse($letter->effective_date)->translatedFormat('d M Y') }}
                        </td>
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-2">
                                <img src="{{ $letter->qr_code_url }}" alt="QR" class="w-9 h-9 rounded border border-gray-200 bg-white p-0.5 shadow-sm">
                                <div>
                                    <span class="text-[10px] text-emerald-600 font-bold block"><i class="fas fa-check-circle"></i> Terverifikasi</span>
                                    <span class="text-[10px] text-gray-400 font-mono">{{ substr($letter->signature_hash, 0, 10) }}...</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-800 text-xs font-bold px-2.5 py-1 rounded-full">
                                <i class="fas fa-eye"></i> {{ $letter->reads_count }} Dibaca
                            </span>
                        </td>
                        <td class="py-4 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('yayasan.letters.show', $letter->id) }}" class="p-2 text-violet-600 hover:bg-violet-100 rounded-lg transition-colors" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('yayasan.letters.edit', $letter->id) }}" class="p-2 text-amber-600 hover:bg-amber-100 rounded-lg transition-colors" title="Edit Surat">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('yayasan.letters.print', $letter->id) }}" target="_blank" class="p-2 text-emerald-600 hover:bg-emerald-100 rounded-lg transition-colors" title="Cetak / Pratinjau PDF">
                                    <i class="fas fa-print"></i>
                                </a>
                                <a href="{{ $letter->verification_url }}" target="_blank" class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors" title="Uji QR Verifikasi">
                                    <i class="fas fa-qrcode"></i>
                                </a>
                                <form action="{{ route('yayasan.letters.destroy', $letter->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus surat digital ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-rose-600 hover:bg-rose-100 rounded-lg transition-colors" title="Hapus Dokumen">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $letters->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
