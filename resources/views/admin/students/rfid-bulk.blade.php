@extends('layouts.admin')

@section('title', 'Registrasi RFID Massal')

@section('content')
<div class="min-h-screen bg-gray-50 p-4 md:p-6">

    {{-- HEADER --}}
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-1">
            <a href="{{ route('admin.students.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Registrasi RFID Massal</h1>
        </div>
        <p class="text-gray-500 text-sm ml-8">Hubungkan Arduino Nano Scanner lalu pilih siswa dan tempelkan kartunya.</p>
    </div>

    <div class="flex flex-col xl:flex-row gap-4">

        {{-- KOLOM KIRI: Status Scanner & Filter --}}
        <div class="space-y-4 xl:w-72 shrink-0">

            {{-- PANEL STATUS SCANNER --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
                    Status Scanner Arduino
                </h2>

                {{-- Status Badge --}}
                <div id="scanner-status" class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-200 mb-4">
                    <div id="status-dot" class="w-3 h-3 rounded-full bg-gray-300"></div>
                    <span id="status-text" class="text-sm font-medium text-gray-500">Belum Terhubung</span>
                </div>

                {{-- Tombol Hubungkan --}}
                <button id="btn-connect" onclick="connectScanner()"
                    class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-all flex items-center justify-center gap-2 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    Hubungkan Scanner
                </button>
                <button id="btn-disconnect" onclick="disconnectScanner()"
                    class="w-full py-3 px-4 bg-red-100 hover:bg-red-200 text-red-700 font-semibold rounded-xl transition-all hidden flex items-center justify-center gap-2 mt-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Putuskan Koneksi
                </button>

                {{-- Last UID --}}
                <div class="mt-4 p-3 bg-blue-50 rounded-xl border border-blue-100">
                    <div class="text-xs text-blue-400 font-medium mb-1">UID Terakhir Terbaca</div>
                    <div id="last-uid" class="text-lg font-bold text-blue-700 tracking-widest font-mono">—</div>
                </div>

                {{-- Scan Count --}}
                <div class="mt-3 flex items-center justify-between text-sm text-gray-500">
                    <span>Total Scan Sesi Ini</span>
                    <span id="scan-count" class="font-bold text-gray-800">0</span>
                </div>

                {{-- Browser Info --}}
                <div class="mt-4 p-3 bg-yellow-50 rounded-xl border border-yellow-200 text-xs text-yellow-700">
                    ⚠️ <strong>Web Serial API</strong> hanya berfungsi di browser <strong>Chrome / Edge</strong> versi terbaru. Tidak bisa di Firefox/Safari.
                </div>
            </div>

            {{-- PANEL MODE MANUAL --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h2 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Input Manual
                </h2>
                <p class="text-xs text-gray-400 mb-3">Jika tidak ada Arduino, ketik UID kartu secara manual di sini.</p>
                <div class="flex gap-2">
                    <input type="text" id="manual-uid" placeholder="Ketik UID kartu..." maxlength="20"
                        class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-purple-400">
                    <button onclick="applyManualUid()"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-xl transition-all">
                        Pakai
                    </button>
                </div>
            </div>

            {{-- FILTER SEKOLAH --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h2 class="font-semibold text-gray-700 mb-4">Filter Daftar Siswa</h2>
                <form method="GET" action="{{ route('admin.students.rfid-bulk') }}" class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-500 font-medium mb-1 block">Unit Sekolah</label>
                        <select name="school_id" onchange="this.form.submit()"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="">-- Pilih Sekolah --</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->id }}" {{ $schoolId == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-medium mb-1 block">Tahun Pelajaran</label>
                        <select name="academic_year_id" onchange="this.form.submit()"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $yearId == $year->id ? 'selected' : '' }}>{{ $year->year }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

        </div>

        {{-- KOLOM KANAN: Daftar Siswa --}}
        <div class="flex-1 min-w-0">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                <div class="p-4 border-b border-gray-100">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="font-semibold text-gray-700">
                            Daftar Siswa
                            @if($students->count() > 0)
                                <span class="ml-1 text-sm font-normal text-gray-400">({{ $students->count() }} siswa)</span>
                            @endif
                        </h2>
                        @if($students->count() > 0)
                            <div class="flex items-center gap-4 text-xs">
                                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-400 inline-block"></span> Sudah: <strong id="count-done" class="text-green-700">{{ $students->whereNotNull('rfid_uid')->count() }}</strong></span>
                                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-gray-300 inline-block"></span> Belum: <strong id="count-pending" class="text-gray-600">{{ $students->whereNull('rfid_uid')->count() }}</strong></span>
                            </div>
                        @endif
                    </div>
                </div>

                @if(!$schoolId)
                    <div class="p-12 text-center text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <p class="font-medium">Pilih sekolah terlebih dahulu</p>
                        <p class="text-sm">untuk menampilkan daftar siswa</p>
                    </div>
                @elseif($students->isEmpty())
                    <div class="p-12 text-center text-gray-400">
                        <p class="font-medium">Tidak ada siswa aktif di sekolah ini</p>
                    </div>
                @else
                    {{-- Search siswa --}}
                    <div class="p-4 border-b border-gray-50">
                        <input type="text" id="search-student" placeholder="Cari nama siswa..." onkeyup="filterStudents()"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>

                    {{-- Tabel Siswa --}}
                    <div class="overflow-auto max-h-[calc(100vh-260px)]">
                        <table class="w-full text-sm text-left" id="student-table">
                            <thead class="bg-gray-50 sticky top-0 z-10">
                                <tr>
                                    <th class="px-3 py-3 text-xs font-semibold text-gray-500 w-[5%]">No</th>
                                    <th class="px-3 py-3 text-xs font-semibold text-gray-500 w-[35%]">Nama Siswa</th>
                                    <th class="px-3 py-3 text-xs font-semibold text-gray-500 w-[15%]">Kelas</th>
                                    <th class="px-3 py-3 text-xs font-semibold text-gray-500 w-[25%]">RFID UID</th>
                                    <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 w-[20%]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50" id="student-tbody">
                                @foreach($students as $i => $student)
                                <tr class="hover:bg-blue-50/30 transition-colors student-row" id="row-{{ $student->id }}" data-name="{{ strtolower($student->full_name) }}">
                                    <td class="px-3 py-2.5 text-gray-400 text-xs">{{ $i + 1 }}</td>
                                    <td class="px-3 py-2.5">
                                        <div class="font-medium text-gray-800">{{ $student->full_name }}</div>
                                        <div class="text-xs text-gray-400">{{ $student->nisn }}</div>
                                    </td>
                                    <td class="px-3 py-2.5 text-gray-500 text-xs">
                                        {{ $student->classroom?->name ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2.5" id="uid-cell-{{ $student->id }}">
                                        @if($student->rfid_uid)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-50 text-green-700 rounded-lg text-xs font-mono font-semibold whitespace-nowrap">
                                                <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                {{ $student->rfid_uid }}
                                            </span>
                                        @else
                                            <span class="text-gray-300 italic text-xs">Belum ada</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 text-center">
                                        <button onclick="selectStudent({{ $student->id }}, '{{ addslashes($student->full_name) }}')"
                                            id="btn-select-{{ $student->id }}"
                                            class="px-3 py-1.5 {{ $student->rfid_uid ? 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100 border border-yellow-200' : 'bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200' }} rounded-lg text-xs font-semibold transition-all whitespace-nowrap">
                                            {{ $student->rfid_uid ? '✏️ Ganti' : '📡 Pilih' }}
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- MODAL: Tempelkan Kartu --}}
<div id="scan-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 text-center">
        <div id="modal-waiting" class="">
            {{-- Animasi Pulsing Ring --}}
            <div class="relative w-20 h-20 mx-auto mb-4">
                <div class="absolute inset-0 rounded-full bg-blue-100 animate-ping opacity-60"></div>
                <div class="relative w-20 h-20 rounded-full bg-blue-500 flex items-center justify-center">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                </div>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-1">Tempelkan Kartu Siswa</h3>
            <p class="text-gray-500 text-sm mb-1">Menunggu scan untuk:</p>
            <p id="modal-student-name" class="text-blue-600 font-bold text-base mb-3">—</p>
            {{-- UID yang terbaca --}}
            <div id="modal-uid-box" class="hidden mb-4 p-3 bg-gray-50 rounded-xl border border-gray-200">
                <div class="text-xs text-gray-400 mb-1">UID Terbaca:</div>
                <div id="modal-uid-value" class="text-xl font-bold font-mono text-gray-800 tracking-widest">—</div>
                <div id="modal-uid-checking" class="text-xs text-blue-500 mt-1 animate-pulse">Memeriksa kepemilikan...</div>
            </div>
            <button onclick="closeScanModal()" class="w-full py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold rounded-xl transition-all">Batal</button>
        </div>

        <div id="modal-success" class="hidden">
            <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-green-500 flex items-center justify-center">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-1">Berhasil Disimpan!</h3>
            <div id="modal-success-uid" class="inline-block px-3 py-1 bg-green-50 text-green-700 rounded-lg text-sm font-mono font-bold mb-2">—</div>
            <p id="modal-success-msg" class="text-gray-500 text-sm mb-4">—</p>
            <button onclick="closeScanModal()" class="w-full py-2.5 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-xl transition-all">OK, Lanjut</button>
        </div>

        <div id="modal-error" class="hidden">
            <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-red-500 flex items-center justify-center">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Gagal!</h3>
            <p id="modal-error-msg" class="text-gray-500 text-sm mb-6">—</p>
            <button onclick="goBackToWaiting()" class="w-full py-3 bg-red-100 hover:bg-red-200 text-red-700 font-semibold rounded-xl transition-all mb-2">Coba Lagi</button>
            <button onclick="closeScanModal()" class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold rounded-xl transition-all">Batal</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ================================================================
//  State
// ================================================================
let port = null;
let reader = null;
let readLoopRunning = false;
let selectedStudentId = null;
let selectedStudentName = null;
let waitingForScan = false;
let sessionScanCount = 0;
let serialBuffer = '';

// ================================================================
//  Web Serial API: Hubungkan ke Arduino Nano
// ================================================================
async function connectScanner() {
    if (!('serial' in navigator)) {
        alert('Browser Anda tidak mendukung Web Serial API.\nGunakan Google Chrome atau Microsoft Edge versi terbaru.');
        return;
    }
    try {
        // Lepas port lama jika ada
        if (port) {
            try {
                readLoopRunning = false;
                if (reader) { await reader.cancel(); reader = null; }
                await port.close();
            } catch (e) {}
            port = null;
        }

        port = await navigator.serial.requestPort();
        await port.open({ baudRate: 115200 });

        setStatus('connected', 'Terhubung ke Scanner');
        document.getElementById('btn-connect').classList.add('hidden');
        document.getElementById('btn-disconnect').classList.remove('hidden');

        readLoopRunning = true;
        readLoop();
    } catch (e) {
        if (e.name !== 'NotFoundError') {
            setStatus('error', 'Gagal terhubung: ' + e.message);
        }
    }
}

async function disconnectScanner() {
    readLoopRunning = false;
    try {
        if (reader) { await reader.cancel(); reader = null; }
        if (port) { await port.close(); port = null; }
    } catch(e) {}
    port = null;
    setStatus('disconnected', 'Koneksi Diputus');
    document.getElementById('btn-connect').classList.remove('hidden');
    document.getElementById('btn-disconnect').classList.add('hidden');
}

// ================================================================
//  Loop baca data dari Serial
// ================================================================
async function readLoop() {
    while (port && port.readable && readLoopRunning) {
        reader = port.readable.getReader();
        try {
            while (true) {
                const { value, done } = await reader.read();
                if (done || !readLoopRunning) break;
                const text = new TextDecoder().decode(value);
                serialBuffer += text;

                // Proses setiap baris yang lengkap
                const lines = serialBuffer.split('\n');
                serialBuffer = lines.pop(); // simpan sisa yg belum lengkap
                for (const line of lines) {
                    processSerialLine(line.trim());
                }
            }
        } catch (e) {
            if (readLoopRunning) setStatus('error', 'Koneksi terputus');
        } finally {
            reader.releaseLock();
        }
    }
}

// ================================================================
//  Proses baris data dari Nano
// ================================================================
function processSerialLine(line) {
    if (!line) return;
    console.log('[Serial]', line);

    if (line.startsWith('UID:')) {
        const uid = line.substring(4).trim().toUpperCase();
        document.getElementById('last-uid').textContent = uid;
        sessionScanCount++;
        document.getElementById('scan-count').textContent = sessionScanCount;

        // Jika sedang menunggu scan untuk siswa, langsung assign
        if (waitingForScan && selectedStudentId) {
            assignRfid(selectedStudentId, uid);
        }
    }
}

// ================================================================
//  Pilih siswa (dari tabel)
// ================================================================
function selectStudent(studentId, studentName) {
    selectedStudentId = studentId;
    selectedStudentName = studentName;

    // Buka modal
    document.getElementById('scan-modal').classList.remove('hidden');
    document.getElementById('modal-waiting').classList.remove('hidden');
    document.getElementById('modal-success').classList.add('hidden');
    document.getElementById('modal-error').classList.add('hidden');
    document.getElementById('modal-student-name').textContent = studentName;
    waitingForScan = true;
}

function closeScanModal() {
    waitingForScan = false;
    selectedStudentId = null;
    selectedStudentName = null;
    document.getElementById('scan-modal').classList.add('hidden');
}

function goBackToWaiting() {
    document.getElementById('modal-waiting').classList.remove('hidden');
    document.getElementById('modal-success').classList.add('hidden');
    document.getElementById('modal-error').classList.add('hidden');
    waitingForScan = true;
}

// ================================================================
//  Input Manual
// ================================================================
function applyManualUid() {
    const uid = document.getElementById('manual-uid').value.trim().toUpperCase();
    if (!uid) return;
    document.getElementById('last-uid').textContent = uid;
    if (waitingForScan && selectedStudentId) {
        assignRfid(selectedStudentId, uid);
    } else {
        alert('Pilih siswa terlebih dahulu dari tabel, lalu gunakan input manual ini.');
    }
}

// ================================================================
//  Tampilkan UID di modal saat terbaca
// ================================================================
function showUidInModal(uid) {
    const box = document.getElementById('modal-uid-box');
    const val = document.getElementById('modal-uid-value');
    const chk = document.getElementById('modal-uid-checking');
    box.classList.remove('hidden');
    val.textContent = uid;
    chk.textContent = 'Memeriksa kepemilikan...';
    chk.className = 'text-xs text-blue-500 mt-1 animate-pulse';
}

function setUidCheckResult(ok, msg) {
    const chk = document.getElementById('modal-uid-checking');
    chk.className = ok ? 'text-xs text-green-600 mt-1 font-semibold' : 'text-xs text-red-500 mt-1 font-semibold';
    chk.textContent = msg;
}

// ================================================================
//  Kirim RFID ke server (dengan pre-check kepemilikan)
// ================================================================
async function assignRfid(studentId, uid) {
    waitingForScan = false;

    // Tampilkan UID di modal
    showUidInModal(uid);

    try {
        // ── STEP 1: Pre-check kepemilikan UID ──
        const checkRes = await fetch(`{{ url('/api/rfid/check-uid') }}?uid=${uid}&exclude_type=student&exclude_id=${studentId}`);
        const checkData = await checkRes.json();

        if (checkData.owned) {
            setUidCheckResult(false, `⚠️ UID ini milik: ${checkData.owner_name} (${checkData.owner_type})`);
            // Tunggu 2 detik lalu tampilkan error
            setTimeout(() => {
                showModalError(`UID ${uid} sudah terdaftar atas nama:\n${checkData.owner_name} (${checkData.owner_type})\n\nGunakan kartu yang berbeda.`);
            }, 1500);
            return;
        }

        setUidCheckResult(true, '✓ UID bebas, menyimpan...');
        await new Promise(r => setTimeout(r, 500)); // jeda singkat agar user bisa baca

        // ── STEP 2: Simpan ke database ──
        const response = await fetch('{{ route("admin.students.rfid-bulk-assign") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ student_id: studentId, rfid_uid: uid })
        });

        const data = await response.json();

        if (data.success) {
            updateStudentRow(studentId, uid);
            document.getElementById('modal-waiting').classList.add('hidden');
            document.getElementById('modal-success').classList.remove('hidden');
            document.getElementById('modal-success-uid').textContent = uid;
            document.getElementById('modal-success-msg').textContent = data.message;
            setTimeout(closeScanModal, 2500);
        } else {
            showModalError(data.message || 'Terjadi kesalahan.');
        }
    } catch (e) {
        showModalError('Gagal menghubungi server: ' + e.message);
    }
}

function showModalError(msg) {
    document.getElementById('modal-waiting').classList.add('hidden');
    document.getElementById('modal-error').classList.remove('hidden');
    document.getElementById('modal-error-msg').textContent = msg;
}

// ================================================================
//  Update baris siswa di tabel secara real-time
// ================================================================
function updateStudentRow(studentId, uid) {
    const uidCell = document.getElementById('uid-cell-' + studentId);
    if (uidCell) {
        uidCell.innerHTML = `<span class="inline-flex items-center gap-1 px-2 py-1 bg-green-50 text-green-700 rounded-lg text-xs font-mono font-semibold">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            ${uid}
        </span>`;
    }
    const btn = document.getElementById('btn-select-' + studentId);
    if (btn) {
        btn.textContent = '✏️ Ganti';
        btn.className = 'px-3 py-1.5 bg-yellow-50 text-yellow-700 hover:bg-yellow-100 border border-yellow-200 rounded-lg text-xs font-semibold transition-all whitespace-nowrap';
    }
}

// ================================================================
//  Set status scanner
// ================================================================
function setStatus(type, text) {
    const dot = document.getElementById('status-dot');
    const label = document.getElementById('status-text');
    const colors = {
        connected:    ['bg-green-400', 'text-green-700'],
        disconnected: ['bg-gray-300',  'text-gray-500'],
        error:        ['bg-red-400',   'text-red-600'],
    };
    dot.className = 'w-3 h-3 rounded-full ' + (colors[type]?.[0] ?? 'bg-gray-300');
    label.className = 'text-sm font-medium ' + (colors[type]?.[1] ?? 'text-gray-500');
    label.textContent = text;
}

// ================================================================
//  Filter pencarian siswa
// ================================================================
function filterStudents() {
    const q = document.getElementById('search-student').value.toLowerCase();
    document.querySelectorAll('.student-row').forEach(row => {
        const name = row.getAttribute('data-name') || '';
        row.style.display = name.includes(q) ? '' : 'none';
    });
}
</script>
@endpush
@endsection
