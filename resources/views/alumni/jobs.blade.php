@extends(auth()->user()->layout)
@section('title', 'Lowongan Kerja (Job Board)')

@section('content')
<div class="space-y-6">
    {{-- Header Bar (LMS Solid UI) --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-900 text-white rounded-2xl shadow-md border-2 border-slate-900 px-6 py-5">
        <div>
            <span class="inline-block px-3 py-1 bg-amber-400 text-slate-900 text-xs font-black rounded-full border border-black mb-1">
                <i class="fas fa-briefcase mr-1"></i> LOWONGAN KERJA & KARIR
            </span>
            <h1 class="text-xl md:text-2xl font-black text-white flex items-center gap-2">
                Papan Lowongan Kerja (*Job Board*)
            </h1>
            <p class="text-xs text-indigo-200 font-bold mt-1">
                Menghubungkan Alumni & Lulusan Perguruan Pembda Nias dengan DUDI Mitra Industri
            </p>
        </div>
        <div>
            <a href="{{ route('alumni.tracer.form') }}" class="inline-flex items-center gap-2 bg-amber-400 hover:bg-amber-500 text-slate-900 px-4 py-2 rounded-xl text-xs font-black transition border border-black shadow-sm">
                <i class="fas fa-graduation-cap"></i> Isi Tracer Study
            </a>
        </div>
    </div>

    {{-- Job Listings --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($jobs as $job)
            <div class="bg-white rounded-2xl shadow-md border-2 border-slate-900 p-5 md:p-6 hover:shadow-lg transition duration-200 flex flex-col justify-between">
                <div>
                    {{-- Company name & salary badge --}}
                    <div class="flex justify-between items-start gap-3 mb-3">
                        <div>
                            <span class="text-[10px] bg-slate-900 text-amber-300 font-black px-2.5 py-1 rounded-lg uppercase tracking-wider border border-black">
                                {{ $job->company_name }}
                            </span>
                            <h3 class="text-lg font-black text-slate-900 mt-2 leading-snug">{{ $job->title }}</h3>
                        </div>
                        @if($job->salary_range)
                            <span class="bg-emerald-100 text-emerald-950 border border-emerald-400 px-2.5 py-1 rounded-lg text-xs font-black whitespace-nowrap">
                                <i class="fas fa-coins text-[10px] mr-1 text-emerald-700"></i>{{ $job->salary_range }}
                            </span>
                        @else
                            <span class="bg-slate-100 text-slate-900 border border-slate-300 px-2 py-0.5 rounded-lg text-[10px] font-bold italic whitespace-nowrap">
                                Gaji Kompetitif
                            </span>
                        @endif
                    </div>

                    {{-- Description --}}
                    <div class="text-xs text-slate-900 space-y-3 mt-4">
                        <div>
                            <p class="font-extrabold text-slate-900 uppercase tracking-wider text-[11px] mb-1">Deskripsi Pekerjaan:</p>
                            <p class="leading-relaxed font-semibold text-slate-800 whitespace-pre-line">{{ $job->description }}</p>
                        </div>

                        {{-- Requirements --}}
                        @if($job->requirements)
                            <div class="border-t-2 border-slate-200 pt-3">
                                <p class="font-extrabold text-slate-900 uppercase tracking-wider text-[11px] mb-1">Persyaratan:</p>
                                <p class="leading-relaxed font-semibold text-slate-800 whitespace-pre-line">{{ $job->requirements }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Footer Contact Info --}}
                <div class="border-t-2 border-slate-900 pt-4 mt-5 flex flex-wrap items-center justify-between gap-3">
                    <div class="text-[11px] font-bold text-slate-700">
                        <i class="far fa-clock mr-1"></i> Ditayangkan: {{ $job->created_at->translatedFormat('d M Y') }}
                    </div>
                    <div class="flex items-center gap-2">
                        @if($job->contact_phone)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $job->contact_phone) }}" target="_blank" class="bg-emerald-600 hover:bg-emerald-700 text-white font-black px-3.5 py-2 rounded-xl text-xs transition flex items-center gap-1.5 border border-black shadow-sm">
                                <i class="fab fa-whatsapp"></i> Hubungi WA
                            </a>
                        @endif
                        @if($job->contact_email)
                            <a href="mailto:{{ $job->contact_email }}?subject=Lamaran Kerja: {{ $job->title }}" class="bg-amber-400 hover:bg-amber-500 text-slate-900 font-black px-3.5 py-2 rounded-xl text-xs shadow transition flex items-center gap-1.5 border border-black">
                                <i class="far fa-envelope"></i> Kirim Email
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="md:col-span-2 bg-white rounded-2xl shadow-md border-2 border-slate-900 py-16 text-center text-slate-900">
                <div class="w-16 h-16 bg-slate-900 text-amber-300 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-black">
                    <i class="fas fa-briefcase text-2xl"></i>
                </div>
                <p class="text-base font-black text-slate-900">Belum ada lowongan pekerjaan aktif saat ini.</p>
                <p class="text-xs font-bold text-slate-700 mt-1">Kami akan mengabari Anda jika mitra DUDI membuka lowongan baru!</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
