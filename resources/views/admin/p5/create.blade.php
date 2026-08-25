@extends('layouts.admin')

@section('title', 'Buat Projek P5 Baru')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-violet-600 via-indigo-600 to-purple-700 rounded-2xl p-6 text-white relative overflow-hidden shadow-sm">
        <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4"></div>
        <div class="relative">
            <div class="flex items-center text-sm text-white/70 mb-2 gap-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white transition">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.p5.index') }}" class="hover:text-white transition">Projek P5</a>
                <span>/</span>
                <span class="text-white font-semibold">Buat Baru</span>
            </div>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold flex items-center gap-2.5">
                        <i class="fas fa-plus-circle"></i> Buat Projek P5 Baru
                    </h1>
                    <p class="text-xs text-purple-100 mt-1">
                        Tentukan judul projek, tema, rombel sasaran, dan rumusan target dimensi/sub-elemen
                    </p>
                </div>
                <a href="{{ route('admin.p5.index') }}" class="px-5 py-2.5 bg-white/20 hover:bg-white/30 text-white rounded-xl font-semibold transition flex items-center gap-2 text-sm shadow-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    @if ($errors->any())
    <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-4.5 rounded-xl shadow-sm">
        <div class="flex items-center gap-2 font-bold text-sm mb-1">
            <i class="fas fa-exclamation-triangle"></i> Periksa kembali data yang diinput:
        </div>
        <ul class="list-disc list-inside text-xs space-y-1">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Form Buat Projek P5 --}}
    <form action="{{ route('admin.p5.store') }}" method="POST" class="space-y-6" x-data="p5Form()">
        @csrf

        {{-- 1. Identitas & Sasaran Projek --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-7 space-y-5">
            <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs">
                    <i class="fas fa-info-circle"></i>
                </span>
                1. Informasi Dasar Projek
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Unit Sekolah *</label>
                    <select name="school_id" required class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        @foreach($schools as $sch)
                        <option value="{{ $sch->id }}" {{ old('school_id', auth()->user()->school_id) == $sch->id ? 'selected' : '' }}>
                            {{ $sch->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Tahun Pelajaran *</label>
                    <select name="academic_year_id" required class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        @foreach($academicYears as $ay)
                        <option value="{{ $ay->id }}" {{ (old('academic_year_id') == $ay->id || $ay->is_active) ? 'selected' : '' }}>
                            {{ $ay->name }} {{ $ay->is_active ? '(Aktif)' : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Rombel / Kelas Sasaran *</label>
                    <select name="classroom_id" required class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        <option value="">-- Pilih Rombel --</option>
                        @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}" {{ old('classroom_id') == $cls->id ? 'selected' : '' }}>
                            {{ $cls->name }} ({{ $cls->school->code ?? '' }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-2">Judul / Nama Projek P5 *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="Contoh: Pemanfaatan Sampah Organik Menjadi Pupuk Kompos Berkualitas" class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">Tema Utama P5 *</label>
                    <select name="theme" required class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-semibold text-indigo-950">
                        @foreach($themes as $thm)
                        <option value="{{ $thm }}" {{ old('theme') == $thm ? 'selected' : '' }}>{{ $thm }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-bold text-gray-700 mb-2">Deskripsi Singkat / Tujuan Projek</label>
                    <textarea name="description" rows="3" placeholder="Jelaskan secara singkat latar belakang dan output dari projek ini..." class="w-full px-4 py-3 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        {{-- 2. Target Dimensi & Sub-Elemen --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-7 space-y-5">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-xs">
                        <i class="fas fa-bullseye"></i>
                    </span>
                    2. Target Dimensi & Capaian Sub-Elemen
                </h3>
                <button type="button" @click="addTarget()" class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                    <i class="fas fa-plus"></i> Tambah Dimensi
                </button>
            </div>

            <p class="text-xs text-gray-500 leading-relaxed">
                Pilih dimensi Profil Pelajar Pancasila dan rumuskan indikator/sub-elemen yang akan diobservasi dan dinilai oleh fasilitator/guru kelas.
            </p>

            <div class="space-y-4">
                <template x-for="(target, index) in targets" :key="index">
                    <div class="p-4 bg-gray-50/80 rounded-2xl border border-gray-200/80 space-y-3 relative group">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-indigo-900 flex items-center gap-1.5">
                                <span class="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px]" x-text="index + 1"></span>
                                Target Capaian Dimensi
                            </span>
                            <button type="button" @click="removeTarget(index)" x-show="targets.length > 1" class="text-rose-500 hover:text-rose-700 text-xs font-bold flex items-center gap-1">
                                <i class="fas fa-trash-alt"></i> Hapus
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Dimensi Profil *</label>
                                <select :name="`targets[${index}][dimension]`" x-model="target.dimension" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-semibold">
                                    @foreach($dimensions as $dim)
                                    <option value="{{ $dim }}">{{ $dim }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Rumusan Sub-Elemen / Indikator Penilaian *</label>
                                <input type="text" :name="`targets[${index}][sub_element]`" x-model="target.sub_element" required placeholder="Contoh: Bekerja sama secara aktif dan menghargai pendapat rekan kelompok" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Tombol Aksi Simpan --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.p5.index') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold text-sm transition">
                Batal
            </a>
            <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white rounded-xl font-bold text-sm shadow-md shadow-indigo-500/20 flex items-center gap-2.5 transition active:scale-[0.98]">
                <i class="fas fa-save"></i>
                <span>Simpan Projek P5</span>
            </button>
        </div>
    </form>
</div>

<script>
function p5Form() {
    return {
        targets: [
            { dimension: 'Gotong Royong', sub_element: 'Bekerja sama, berkomunikasi, dan berkoordinasi dalam mencapai target bersama kelompok' },
            { dimension: 'Kreatif', sub_element: 'Menghasilkan gagasan orisinal dan karya inovatif sesuai tema projek' }
        ],
        addTarget() {
            this.targets.push({
                dimension: 'Bernalar Kritis',
                sub_element: ''
            });
        },
        removeTarget(index) {
            if (this.targets.length > 1) {
                this.targets.splice(index, 1);
            }
        }
    }
}
</script>
@endsection
