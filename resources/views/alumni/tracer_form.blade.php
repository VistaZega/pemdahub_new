@extends(auth()->user()->layout ?? 'layouts.alumni')
@section('title', 'Tracer Study Alumni')

@section('content')
<div class="space-y-6" x-data="{
    status: '{{ old('employment_status', $tracer->employment_status ?? '') }}',
    editMode: @json(!$tracer)
}">
    {{-- Header Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white rounded-xl shadow-sm border border-gray-100 px-5 py-4">
        <div>
            <h1 class="text-lg md:text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-graduation-cap text-amber-500"></i> Portal Tracer Study Alumni (BMW)
            </h1>
            <p class="text-xs text-gray-500 mt-0.5">
                SMKS Swasta Pembda Nias — Melacak Keterserapan Alumni (Bekerja, Melanjutkan Kuliah, Wirausaha)
            </p>
        </div>
        <div>
            <a href="{{ route('alumni.jobs.index') }}" class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 hover:bg-amber-100 px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                <i class="fas fa-briefcase text-xs"></i> Papan Lowongan Kerja →
            </a>
        </div>
    </div>

    @if($tracer)
        {{-- Filled Tracer Summary Card --}}
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl shadow-lg border border-slate-700 p-6" x-show="!editMode">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/10 pb-4 mb-5">
                <div>
                    <span class="text-[9px] bg-emerald-500/20 text-emerald-400 font-bold px-2.5 py-0.5 rounded-full border border-emerald-500/30">
                        SURVEY SELESAI
                    </span>
                    <h3 class="text-lg font-bold mt-2">Anda Telah Mengisi Tracer Study</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Disubmit pada: {{ \Carbon\Carbon::parse($tracer->survey_date)->translatedFormat('d M Y') }}</p>
                </div>
                <button @click="editMode = true; status = '{{ $tracer->employment_status }}'" class="bg-white/10 hover:bg-white/20 text-white font-bold px-4 py-2 rounded-xl text-xs shadow transition flex items-center gap-1.5">
                    <i class="fas fa-edit text-xs"></i> Update Data Jawaban
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                <div>
                    <span class="text-[10px] text-slate-400 uppercase tracking-wider block">Status Pasca Lulus (BMW)</span>
                    <span class="font-bold text-white text-base mt-0.5 block">
                        @if($tracer->employment_status === 'kerja')
                            Bekerja (Karyawan/Pegawai)
                        @elseif($tracer->employment_status === 'kuliah')
                            Melanjutkan Kuliah (Pendidikan Tinggi)
                        @elseif($tracer->employment_status === 'wirausaha')
                            Wirausaha (Membuka Usaha)
                        @elseif($tracer->employment_status === 'mencari_kerja')
                            Sedang Mencari Pekerjaan
                        @else
                            Lainnya / Belum Bekerja
                        @endif
                    </span>
                </div>

                {{-- Conditional display of survey details --}}
                @if($tracer->employment_status === 'kerja')
                    <div class="space-y-3">
                        <div>
                            <span class="text-[10px] text-slate-400 block">Nama Perusahaan / Instansi</span>
                            <span class="font-semibold text-white">{{ $tracer->company_name }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Posisi / Jabatan</span>
                            <span class="font-semibold text-white">{{ $tracer->job_title }}</span>
                        </div>
                        @if($tracer->salary_range)
                            <div>
                                <span class="text-[10px] text-slate-400 block">Rentang Pendapatan</span>
                                <span class="font-semibold text-white">{{ $tracer->salary_range }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                @if($tracer->employment_status === 'kuliah')
                    <div class="space-y-3">
                        <div>
                            <span class="text-[10px] text-slate-400 block">Nama Perguruan Tinggi / Universitas</span>
                            <span class="font-semibold text-white">{{ $tracer->university_name }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Program Studi / Jurusan</span>
                            <span class="font-semibold text-white">{{ $tracer->major }}</span>
                        </div>
                    </div>
                @endif

                @if($tracer->employment_status === 'wirausaha')
                    <div>
                        <span class="text-[10px] text-slate-400 block">Bidang Usaha</span>
                        <span class="font-semibold text-white">{{ $tracer->wirausaha_field }}</span>
                    </div>
                @endif
            </div>

            @if($tracer->feedback_for_school)
                <div class="mt-5 p-4 bg-white/5 border border-white/5 rounded-xl text-xs">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Kritik & Saran untuk SMKS Swasta Pembda Nias:</span>
                    <p class="text-slate-200 italic leading-relaxed">"{{ $tracer->feedback_for_school }}"</p>
                </div>
            @endif
        </div>
    @endif

    {{-- Form Survey --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 md:p-6" x-show="editMode" x-cloak>
        <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-5">
            <h3 class="text-sm font-bold text-gray-850 flex items-center gap-2">
                <i class="fas fa-edit text-amber-500"></i> Kuesioner Tracer Study Alumni
            </h3>
            @if($tracer)
                <button type="button" @click="editMode = false" class="text-xs text-gray-400 hover:text-gray-600">
                    Batal Edit
                </button>
            @endif
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-xs font-semibold mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('alumni.tracer.submit') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">1. Aktivitas Utama Anda Saat Ini</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <label class="flex items-center gap-3 p-3.5 border border-gray-100 rounded-xl cursor-pointer hover:bg-amber-50/20 hover:border-amber-250 transition" :class="status === 'kerja' ? 'bg-amber-50/30 border-amber-400 ring-1 ring-amber-100' : ''">
                        <input type="radio" name="employment_status" value="kerja" x-model="status" class="accent-amber-500" required>
                        <div>
                            <p class="text-sm font-bold text-gray-800">Bekerja</p>
                            <p class="text-[10px] text-gray-400">Pegawai, Karyawan, TNI/POLRI, ASN</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3.5 border border-gray-100 rounded-xl cursor-pointer hover:bg-amber-50/20 hover:border-amber-250 transition" :class="status === 'kuliah' ? 'bg-amber-50/30 border-amber-400 ring-1 ring-amber-100' : ''">
                        <input type="radio" name="employment_status" value="kuliah" x-model="status" class="accent-amber-500" required>
                        <div>
                            <p class="text-sm font-bold text-gray-800">Melanjutkan Kuliah</p>
                            <p class="text-[10px] text-gray-400">Pendidikan Tinggi, D3, D4, S1</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3.5 border border-gray-100 rounded-xl cursor-pointer hover:bg-amber-50/20 hover:border-amber-250 transition" :class="status === 'wirausaha' ? 'bg-amber-50/30 border-amber-400 ring-1 ring-amber-100' : ''">
                        <input type="radio" name="employment_status" value="wirausaha" x-model="status" class="accent-amber-500" required>
                        <div>
                            <p class="text-sm font-bold text-gray-800">Wirausaha</p>
                            <p class="text-[10px] text-gray-400">Membuka Usaha, UMKM, Freelancer</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3.5 border border-gray-100 rounded-xl cursor-pointer hover:bg-amber-50/20 hover:border-amber-250 transition" :class="status === 'mencari_kerja' ? 'bg-amber-50/30 border-amber-400 ring-1 ring-amber-100' : ''">
                        <input type="radio" name="employment_status" value="mencari_kerja" x-model="status" class="accent-amber-500" required>
                        <div>
                            <p class="text-sm font-bold text-gray-800">Mencari Kerja</p>
                            <p class="text-[10px] text-gray-400">Sedang melamar pekerjaan</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3.5 border border-gray-100 rounded-xl cursor-pointer hover:bg-amber-50/20 hover:border-amber-250 transition" :class="status === 'lainnya' ? 'bg-amber-50/30 border-amber-400 ring-1 ring-amber-100' : ''">
                        <input type="radio" name="employment_status" value="lainnya" x-model="status" class="accent-amber-500" required>
                        <div>
                            <p class="text-sm font-bold text-gray-800">Lainnya</p>
                            <p class="text-[10px] text-gray-400">Ibu rumah tangga, studi lanjut informal, dll</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- 1. Bekerja section --}}
            <div x-show="status === 'kerja'" x-transition class="space-y-4 bg-gray-50/50 border border-gray-100 rounded-2xl p-4">
                <h4 class="text-xs font-bold text-amber-700 uppercase tracking-wider mb-2">Detail Pekerjaan</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-550 mb-1.5">Nama Perusahaan / Instansi</label>
                        <input type="text" name="company_name" value="{{ old('company_name', $tracer->company_name ?? '') }}" :required="status === 'kerja'" placeholder="Contoh: PT. Bank Sumut, Kantor Desa, dll" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-550 mb-1.5">Jabatan / Posisi Kerja</label>
                        <input type="text" name="job_title" value="{{ old('job_title', $tracer->job_title ?? '') }}" :required="status === 'kerja'" placeholder="Contoh: IT Support, Staf Admin, Kasir, dll" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-550 mb-1.5">Rentang Pendapatan Bulanan</label>
                    <select name="salary_range" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition">
                        <option value="">Pilih Rentang Pendapatan...</option>
                        <option value="< Rp 1.500.000" {{ old('salary_range', $tracer->salary_range ?? '') === '< Rp 1.500.000' ? 'selected' : '' }}>< Rp 1.500.000</option>
                        <option value="Rp 1.500.000 - Rp 3.000.000" {{ old('salary_range', $tracer->salary_range ?? '') === 'Rp 1.500.000 - Rp 3.000.000' ? 'selected' : '' }}>Rp 1.500.000 - Rp 3.000.000</option>
                        <option value="Rp 3.000.000 - Rp 5.000.000" {{ old('salary_range', $tracer->salary_range ?? '') === 'Rp 3.000.000 - Rp 5.000.000' ? 'selected' : '' }}>Rp 3.000.000 - Rp 5.000.000</option>
                        <option value="> Rp 5.000.000" {{ old('salary_range', $tracer->salary_range ?? '') === '> Rp 5.000.000' ? 'selected' : '' }}>> Rp 5.000.000</option>
                    </select>
                </div>
            </div>

            {{-- 2. Kuliah section --}}
            <div x-show="status === 'kuliah'" x-transition class="space-y-4 bg-gray-50/50 border border-gray-100 rounded-2xl p-4" x-cloak>
                <h4 class="text-xs font-bold text-amber-700 uppercase tracking-wider mb-2">Detail Perguruan Tinggi</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-550 mb-1.5">Nama Perguruan Tinggi / Universitas</label>
                        <input type="text" name="university_name" value="{{ old('university_name', $tracer->university_name ?? '') }}" :required="status === 'kuliah'" placeholder="Contoh: Universitas Nias, USU, UNIMED, dll" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-550 mb-1.5">Program Studi / Jurusan</label>
                        <input type="text" name="major" value="{{ old('major', $tracer->major ?? '') }}" :required="status === 'kuliah'" placeholder="Contoh: Sistem Informasi, Akuntansi, Teknik Informatika" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition">
                    </div>
                </div>
            </div>

            {{-- 3. Wirausaha section --}}
            <div x-show="status === 'wirausaha'" x-transition class="space-y-4 bg-gray-50/50 border border-gray-100 rounded-2xl p-4" x-cloak>
                <h4 class="text-xs font-bold text-amber-700 uppercase tracking-wider mb-2">Detail Wirausaha</h4>
                <div>
                    <label class="block text-xs font-bold text-gray-550 mb-1.5">Bidang Usaha / Sektor Bisnis</label>
                    <input type="text" name="wirausaha_field" value="{{ old('wirausaha_field', $tracer->wirausaha_field ?? '') }}" :required="status === 'wirausaha'" placeholder="Contoh: Kedai Kopi, Bengkel Motor, Toko Kelontong, Jasa Desain Grafis, dll" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 transition">
                </div>
            </div>

            {{-- Feedback section --}}
            <div class="border-t border-gray-100 pt-4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">2. Kritik, Saran, atau Feedback untuk Kemajuan Sekolah</label>
                <textarea name="feedback_for_school" rows="4" placeholder="Tuliskan masukan Anda mengenai kurikulum, praktek komputer/teknik, atau saran kerja sama DUDI untuk membantu adik kelas Anda..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white transition">{{ old('feedback_for_school', $tracer->feedback_for_school ?? '') }}</textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-6 py-2.5 rounded-xl shadow transition text-sm flex items-center gap-2">
                    <i class="fas fa-paper-plane"></i> Kirim Jawaban Survey
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
