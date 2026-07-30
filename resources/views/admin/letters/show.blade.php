@extends('layouts.app')

@section('title', $letter->title)

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.letters.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-violet-700 transition-colors">
            <i class="fas fa-arrow-left"></i> Kembali ke Inboks Surat Masuk
        </a>
        <a href="{{ route('admin.letters.print', $letter->id) }}" target="_blank" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2 rounded-xl shadow text-sm transition-all flex items-center gap-2">
            <i class="fas fa-print"></i> Cetak / Pratinjau Dokumen
        </a>
    </div>

    {{-- Main Letter View Card --}}
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 md:p-12">
        {{-- Kop --}}
        <div class="border-b-4 border-double border-gray-900 pb-6 mb-8 text-center">
            <div class="flex flex-col md:flex-row items-center justify-center gap-5">
                <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo Yayasan" class="w-20 h-auto max-h-24 object-contain flex-shrink-0">
                <div class="text-center">
                    <h2 class="text-xl md:text-2xl font-black text-gray-900 tracking-wide uppercase">Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)</h2>
                    <p class="text-xs font-semibold text-gray-800 mt-1">Jl. Pelita No.09 Kel. Ilir Kota Gunungsitoli (22815)</p>
                    <p class="text-xs font-medium text-gray-600 mt-0.5">web : perguruanpembda.com | email : perguruanpembdanias@gmail.com</p>
                </div>
            </div>
        </div>

        {{-- Title --}}
        <div class="text-center mb-8">
            <h3 class="text-base font-extrabold text-gray-900 uppercase underline">SURAT EDARAN YAYASAN</h3>
            <p class="text-xs font-mono font-bold text-gray-700 mt-1">Nomor: {{ $letter->letter_number }}</p>
        </div>

        {{-- Meta --}}
        @php
            $targetLabel = is_array($letter->recipients) && isset($letter->recipients['target_label']) 
                ? $letter->recipients['target_label'] 
                : 'Kepala Sekolah se-Perguruan Pembda Nias';
        @endphp
        <div class="mb-8 text-sm space-y-1 text-gray-800">
            <div class="flex">
                <span class="w-28 font-semibold text-gray-600">Perihal</span>
                <span class="w-4 font-bold text-center">:</span>
                <span class="font-bold text-gray-900 flex-1">{{ $letter->title }}</span>
            </div>
            <div class="flex">
                <span class="w-28 font-semibold text-gray-600">Kepada Yth.</span>
                <span class="w-4 font-bold text-center">:</span>
                <span class="font-semibold text-gray-900 flex-1">{{ $targetLabel }}</span>
            </div>
        </div>

        {{-- Content --}}
        <div class="prose max-w-none text-sm text-gray-800 leading-relaxed mb-12 space-y-4 font-serif">
            {!! $letter->content !!}
        </div>

        {{-- TTD Digital --}}
        <div class="flex justify-between items-end pt-6 border-t border-gray-100">
            <div class="flex items-center gap-3 bg-gray-50 border border-gray-200 p-3 rounded-xl">
                <img src="{{ $letter->qr_code_url }}" alt="QR" class="w-16 h-16 bg-white p-1 rounded border border-gray-300">
                <div class="text-[11px] text-gray-600">
                    <span class="font-bold text-emerald-700 block text-xs"><i class="fas fa-shield-alt"></i> TTD Digital Sah</span>
                    <span class="font-mono text-[10px] text-gray-500">{{ substr($letter->signature_hash, 0, 16) }}...</span>
                </div>
            </div>

            <div class="text-right">
                <p class="text-xs text-gray-600">Gunungsitoli, {{ \Carbon\Carbon::parse($letter->effective_date)->translatedFormat('d F Y') }}</p>
                <p class="text-xs font-bold text-gray-900 mt-1 uppercase">{{ $letter->signatory_position }}</p>
                <div class="my-3 py-1 flex justify-end">
                    <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded">✔ SIGNED DIGITAL</span>
                </div>
                <p class="text-sm font-extrabold text-gray-900 underline">{{ $letter->signatory_name }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
