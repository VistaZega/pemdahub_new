@extends('layouts.admin')

@section('title', 'Cetak Massal Desain Kartu Pintar - Admin')

@section('content')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #print-area, #print-area * {
            visibility: visible;
        }
        #print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            background: white;
            padding: 10px;
        }
        .no-print {
            display: none !important;
        }
        .smart-card-print-grid {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 15px !important;
            page-break-inside: auto;
        }
        .smart-card-item {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
        }
    }
</style>

<div class="space-y-6">
    <!-- Header Page (No Print) -->
    <div class="no-print mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.students.index') }}" class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-id-card text-indigo-600"></i> Cetak Massal Desain Kartu Pintar
                    </h1>
                    <p class="text-xs text-gray-600">Download dan cetak kartu identitas elektronik (Kartu Pintar) per kelas</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.students.rfid-bulk') }}" class="px-4 py-2.5 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold rounded-xl text-xs transition shadow-sm flex items-center gap-2">
                <i class="fas fa-wifi text-indigo-500"></i> Registrasi RFID Massal
            </a>
            @if($students->isNotEmpty())
            <button onclick="window.print()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition shadow-md flex items-center gap-2">
                <i class="fas fa-print"></i> Cetak Seluruh Kartu ({{ $students->count() }} Siswa)
            </button>
            @endif
        </div>
    </div>

    <!-- Filter Form (No Print) -->
    <div class="no-print bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('admin.students.smart-cards-bulk') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Unit Sekolah</label>
                <select name="school_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-semibold focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">-- Pilih Sekolah --</option>
                    @foreach($schools as $sch)
                        <option value="{{ $sch->id }}" {{ $schoolId == $sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Kelas / Rombel</label>
                <select name="classroom_id" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-semibold focus:ring-2 focus:ring-indigo-500 bg-white" {{ empty($schoolId) ? 'disabled' : '' }}>
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($classrooms as $cls)
                        <option value="{{ $cls->id }}" {{ $classroomId == $cls->id ? 'selected' : '' }}>{{ $cls->class_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit" class="w-full bg-slate-900 hover:bg-black text-white font-bold px-5 py-2.5 rounded-xl text-sm transition shadow-sm">
                    <i class="fas fa-filter mr-1.5"></i> Tampilkan Kartu
                </button>
            </div>
        </form>
    </div>

    <!-- Smart Cards Display Area -->
    @if(empty($classroomId))
        <div class="no-print bg-white rounded-2xl border-2 border-dashed border-gray-200 p-12 text-center">
            <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-indigo-600">
                <i class="fas fa-id-card text-2xl"></i>
            </div>
            <h3 class="font-bold text-gray-800 text-base">Silakan Pilih Unit Sekolah & Kelas</h3>
            <p class="text-xs text-gray-500 mt-1 max-w-md mx-auto">Pilih kelas di atas untuk memuat seluruh Desain Kartu Pintar siswa siap cetak.</p>
        </div>
    @elseif($students->isEmpty())
        <div class="no-print bg-white rounded-2xl border-2 border-dashed border-gray-200 p-12 text-center">
            <div class="w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-amber-600">
                <i class="fas fa-user-slash text-2xl"></i>
            </div>
            <h3 class="font-bold text-gray-800 text-base">Tidak Ada Data Siswa Aktif</h3>
            <p class="text-xs text-gray-500 mt-1">Belum ada siswa aktif terdaftar di kelas ini.</p>
        </div>
    @else
        <div id="print-area">
            <div class="no-print mb-4 p-4 bg-indigo-50 border border-indigo-200 rounded-xl flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fas fa-info-circle text-indigo-600 text-lg"></i>
                    <div>
                        <span class="font-bold text-indigo-900 text-sm">Menampilkan {{ $students->count() }} Kartu Pintar Siswa Kelas {{ $selectedClassroom?->class_name }}</span>
                        <p class="text-xs text-indigo-700">Kartu siap dicetak via tombol Cetak (Ctrl+P). Disarankan menggunakan kertas hvs thick / photo paper matte.</p>
                    </div>
                </div>
                <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg transition shadow-sm">
                    <i class="fas fa-print mr-1"></i> Cetak Sekarang
                </button>
            </div>

            <!-- Grid Smart Cards -->
            <div class="smart-card-print-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 justify-items-center">
                @foreach($students as $student)
                @php
                    $code = $student->nisn ?: ($student->nis ?: 'STD-' . $student->id);
                    $photoUrl = $student->photo_url ?: asset('images/default-student.jpg');
                    $schoolName = strtoupper($student->school?->name ?? 'YAYASAN PERGURUAN PEMBDA');
                    $ttl = strtoupper(($student->birth_place ? $student->birth_place . ', ' : '') . ($student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->format('d-m-Y') : '-'));
                @endphp

                <div class="smart-card-item rounded-xl bg-white text-slate-800 shadow-xl relative overflow-hidden flex flex-col tracking-wide font-sans select-none border-2 border-slate-200 shrink-0" style="width: 250px; height: 390px;">
                    <!-- Header Section -->
                    <div class="w-full px-3 py-2.5 flex flex-col items-center justify-center z-10 relative shrink-0" style="background: linear-gradient(to bottom, #1e3a8a, #1e1b4b); border-bottom: 3.5px solid #facc15;">
                        <img src="{{ asset('images/logo-pembda.png') }}" class="object-contain mb-0.5 drop-shadow-md" style="width: 32px; height: 32px;" alt="Logo">
                        <h4 class="font-extrabold uppercase tracking-widest text-white leading-tight text-center drop-shadow-md" style="font-size: 10px;">{{ $schoolName }}</h4>
                        <p class="text-yellow-300 font-bold tracking-widest uppercase mt-0.5 text-center" style="font-size: 7px;">KARTU IDENTITAS ELEKTRONIK</p>
                    </div>

                    <!-- Middle Section: Photo & Details -->
                    <div class="flex flex-col items-center z-10 w-full flex-1 justify-start pt-2.5 pb-2 px-3 relative">
                        <!-- Profile Photo Frame -->
                        <div class="rounded-lg overflow-hidden relative mb-1.5 bg-slate-100" style="width: 80px; height: 100px; border: 2.5px solid white; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); outline: 1px solid #cbd5e1;">
                            <img src="{{ $photoUrl }}" class="object-cover w-full h-full" alt="{{ $student->full_name }}">
                            <div class="absolute inset-x-0 bottom-0 py-0.5 text-center" style="background-color: rgba(30, 58, 138, 0.9); backdrop-filter: blur(2px);">
                                <span class="font-extrabold uppercase tracking-widest text-white" style="font-size: 8px;">SISWA</span>
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="text-center w-full">
                            <h5 class="font-extrabold tracking-wide uppercase leading-tight mb-1" style="font-size: 11px; color: #172554; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 26px;">{{ $student->full_name }}</h5>
                            <div class="mx-auto mb-1" style="width: 36px; height: 2px; background-color: #facc15;"></div>

                            <div class="flex flex-col gap-0.5">
                                <p class="font-semibold text-slate-700" style="font-size: 9px;">Kelas: <span class="font-bold" style="color: #1e3a8a;">{{ $selectedClassroom?->class_name }}</span></p>
                                <p class="font-semibold text-slate-700" style="font-size: 9px;">NIS/NISN: <span class="font-bold" style="color: #1e3a8a;">{{ $code }}</span></p>
                                <p class="font-semibold mt-0.5" style="font-size: 7.5px; color: #475569;">TTL: <span class="font-bold uppercase" style="color: #1e293b;">{{ $ttl }}</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer: QR Code -->
                    <div class="flex flex-col items-center justify-center w-full z-10 mt-auto pb-2">
                        <div class="p-1 bg-white border border-slate-200 rounded-lg shadow-xs flex items-center justify-center mb-0.5">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ $code }}" style="width: 65px; height: 65px;" alt="QR Code">
                        </div>
                        <span class="font-bold uppercase tracking-widest" style="font-size: 6.5px; color: #475569;">Scan QR Untuk Absensi & Verifikasi</span>
                    </div>

                    <!-- Bottom Deco Strip -->
                    <div class="absolute bottom-0 inset-x-0 z-20" style="height: 5px; background-color: #1e3a8a;"></div>
                </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
