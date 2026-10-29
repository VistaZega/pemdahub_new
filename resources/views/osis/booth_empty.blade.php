@extends(auth()->check() ? auth()->user()->layout : 'layouts.app')
@section('title', 'Pemilihan OSIS - PembdaHUB')

@section('content')
<div class="max-w-2xl mx-auto py-16 text-center">
    <div class="bg-white rounded-3xl p-8 border-2 border-slate-900 shadow-xl">
        <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 text-3xl flex items-center justify-center mx-auto mb-4 border-2 border-emerald-300">
            <i class="fas fa-box-archive"></i>
        </div>
        <h2 class="text-xl font-black text-slate-900">Pemilihan OSIS Belum Tersedia</h2>
        <p class="text-xs text-slate-600 mt-2 max-w-md mx-auto">
            {{ $message ?? 'Belum ada agenda Pemilihan Ketua OSIS yang sedang dibuka untuk unit sekolah Anda saat ini.' }}
        </p>
        <div class="mt-6">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection
